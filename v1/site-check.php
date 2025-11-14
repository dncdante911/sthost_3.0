<?php
/**
 * Site Check API Endpoint
 * POST /v1/site-check
 *
 * Проверка доступности сайтов с различных локацій
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Заголовки CORS и JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.',
        'code' => 405
    ]);
    exit();
}

// Подключение конфигурации
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

/**
 * Проверка API ключа
 */
function verifyApiKey() {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        return false;
    }

    $authHeader = $headers['Authorization'];
    if (!preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
        return false;
    }

    $apiKey = $matches[1];

    // Список валидных API ключей (в продакшене хранить в БД)
    $validKeys = [
        'demo_key_12345',
        'sthost_api_key_2024',
        // Добавить реальные ключи из базы данных
    ];

    return in_array($apiKey, $validKeys);
}

// Проверка авторизации
if (!verifyApiKey()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid or missing API key',
        'code' => 401
    ]);
    exit();
}

// Получение данных из запроса
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON in request body',
        'code' => 400
    ]);
    exit();
}

// Валидация обязательных параметров
if (!isset($input['url']) || empty($input['url'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Missing required parameter: url',
        'code' => 400
    ]);
    exit();
}

// Параметры с значениями по умолчанию
$url = filter_var($input['url'], FILTER_VALIDATE_URL);
if (!$url) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid URL format',
        'code' => 400
    ]);
    exit();
}

// Доступные локации
$availableLocations = [
    'kyiv' => 'Київ, Україна',
    'frankfurt' => 'Франкфурт, Німеччина',
    'london' => 'Лондон, Великобританія',
    'nyc' => 'Нью-Йорк, США',
    'singapore' => 'Сінгапур',
    'tokyo' => 'Токіо, Японія'
];

$locations = isset($input['locations']) && is_array($input['locations'])
    ? $input['locations']
    : ['kyiv'];

// Фильтрация валидных локаций
$locations = array_filter($locations, function($loc) use ($availableLocations) {
    return isset($availableLocations[$loc]);
});

if (empty($locations)) {
    $locations = ['kyiv'];
}

$checkSSL = isset($input['check_ssl']) ? (bool)$input['check_ssl'] : true;
$followRedirects = isset($input['follow_redirects']) ? (bool)$input['follow_redirects'] : true;
$timeout = isset($input['timeout']) ? min(30, max(1, (int)$input['timeout'])) : 10;

/**
 * Выполнение проверки сайта
 */
function checkSite($url, $location, $checkSSL, $followRedirects, $timeout) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_FOLLOWLOCATION => $followRedirects,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => $checkSSL,
        CURLOPT_SSL_VERIFYHOST => $checkSSL ? 2 : 0,
        CURLOPT_USERAGENT => 'StormHosting Site Checker/1.0',
        CURLOPT_CERTINFO => true,
    ]);

    $startTime = microtime(true);
    $response = curl_exec($ch);
    $endTime = microtime(true);

    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);

    curl_close($ch);

    $totalTime = round(($endTime - $startTime) * 1000); // в миллисекундах

    $result = [
        'location' => $location,
        'status' => $errno === 0 ? 'up' : 'down',
        'http_code' => $info['http_code'],
        'response_time' => round($info['total_time'] * 1000),
        'dns_time' => round($info['namelookup_time'] * 1000),
        'connect_time' => round($info['connect_time'] * 1000),
        'total_time' => $totalTime,
    ];

    // SSL информация
    if ($checkSSL && strpos($url, 'https://') === 0) {
        $result['ssl'] = getSSLInfo($url);
    }

    // Если была ошибка
    if ($errno !== 0) {
        $result['error'] = $error;
        $result['error_code'] = $errno;
    }

    return $result;
}

/**
 * Получение информации о SSL сертификате
 */
function getSSLInfo($url) {
    $parsed = parse_url($url);
    $host = $parsed['host'];
    $port = isset($parsed['port']) ? $parsed['port'] : 443;

    $streamContext = stream_context_create([
        'ssl' => [
            'capture_peer_cert' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);

    $client = @stream_socket_client(
        "ssl://{$host}:{$port}",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $streamContext
    );

    if (!$client) {
        return [
            'valid' => false,
            'error' => 'Could not connect to SSL server'
        ];
    }

    $params = stream_context_get_params($client);
    $cert = $params['options']['ssl']['peer_certificate'];

    if (!$cert) {
        fclose($client);
        return [
            'valid' => false,
            'error' => 'Could not retrieve certificate'
        ];
    }

    $certInfo = openssl_x509_parse($cert);
    fclose($client);

    $expiryDate = date('Y-m-d', $certInfo['validTo_time_t']);
    $daysRemaining = ceil(($certInfo['validTo_time_t'] - time()) / 86400);
    $isValid = $certInfo['validTo_time_t'] > time();

    return [
        'valid' => $isValid,
        'issuer' => isset($certInfo['issuer']['CN']) ? $certInfo['issuer']['CN'] : 'Unknown',
        'expires' => $expiryDate,
        'days_remaining' => max(0, $daysRemaining),
        'subject' => isset($certInfo['subject']['CN']) ? $certInfo['subject']['CN'] : 'Unknown',
    ];
}

// Rate limiting (простая проверка)
$clientIp = $_SERVER['REMOTE_ADDR'];
$rateLimitFile = sys_get_temp_dir() . '/api_rate_limit_' . md5($clientIp);
$currentTime = time();

if (file_exists($rateLimitFile)) {
    $requests = json_decode(file_get_contents($rateLimitFile), true);

    // Очистка старых запросов (старше 1 часа)
    $requests = array_filter($requests, function($timestamp) use ($currentTime) {
        return ($currentTime - $timestamp) < 3600;
    });

    // Проверка лимита (1000 запросов в час)
    if (count($requests) >= 1000) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded. Maximum 1000 requests per hour.',
            'code' => 429
        ]);
        exit();
    }

    $requests[] = $currentTime;
} else {
    $requests = [$currentTime];
}

file_put_contents($rateLimitFile, json_encode($requests));

// Выполнение проверок для каждой локации
$results = [];

foreach ($locations as $location) {
    try {
        $results[] = checkSite($url, $location, $checkSSL, $followRedirects, $timeout);
    } catch (Exception $e) {
        $results[] = [
            'location' => $location,
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
}

// Формирование успешного ответа
$response = [
    'success' => true,
    'url' => $url,
    'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
    'results' => $results
];

http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT);
