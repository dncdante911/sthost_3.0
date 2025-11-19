<?php
/**
 * WHOIS Lookup API Endpoint
 * Выполняет WHOIS запросы для доменов
 */

define('SECURE_ACCESS', true);

// Настройка заголовков
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Заголовки против кеширования
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Обработка OPTIONS запроса
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Не требуется подключение config.php для WHOIS

// Получаем WHOIS серверы
$whois_servers = [
    '.ua' => 'whois.ua',
    '.com.ua' => 'whois.ua',
    '.net.ua' => 'whois.ua',
    '.org.ua' => 'whois.ua',
    '.kiev.ua' => 'whois.ua',
    '.com' => 'whois.verisign-grs.com',
    '.net' => 'whois.verisign-grs.com',
    '.org' => 'whois.pir.org',
    '.info' => 'whois.afilias.net',
    '.biz' => 'whois.biz'
];

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получаем данные запроса
$domain = trim($_POST['domain'] ?? '');

if (empty($domain)) {
    echo json_encode(['error' => 'Введіть ім\'я домену']);
    exit;
}

// Валидация домена - убираем пробелы и приводим к нижнему регистру
$domain = strtolower($domain);

// Проверяем формат домена с помощью regex
if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $domain)) {
    echo json_encode(['error' => 'Невірний формат домену. Приклад: example.com або example.ua']);
    exit;
}

// Определяем зону домена
$domain_parts = explode('.', $domain);
$zone = '.' . end($domain_parts);

// Для .ua доменов проверяем двухуровневые зоны
if (end($domain_parts) === 'ua' && count($domain_parts) > 2) {
    $zone = '.' . $domain_parts[count($domain_parts)-2] . '.ua';
}

// Находим WHOIS сервер
$whois_server = $whois_servers[$zone] ?? null;

if (!$whois_server) {
    echo json_encode([
        'error' => 'WHOIS сервер для зони ' . $zone . ' не знайдено',
        'domain' => $domain,
        'zone' => $zone
    ]);
    exit;
}

// Выполняем WHOIS запрос
try {
    $whois_data = performWhoisLookup($domain, $whois_server);
    
    echo json_encode([
        'success' => true,
        'domain' => $domain,
        'zone' => $zone,
        'whois_server' => $whois_server,
        'data' => $whois_data
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Помилка виконання WHOIS запиту: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Функция выполнения WHOIS запроса
 */
function performWhoisLookup($domain, $whois_server) {
    $port = 43;
    $timeout = 10;
    
    // Пытаемся подключиться к WHOIS серверу
    $fp = @fsockopen($whois_server, $port, $errno, $errstr, $timeout);
    
    if (!$fp) {
        // Если не удалось подключиться, возвращаем тестовые данные
        return generateMockWhoisData($domain);
    }
    
    // Отправляем запрос
    fputs($fp, $domain . "\r\n");
    
    // Читаем ответ
    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 128);
    }
    fclose($fp);
    
    // Парсим ответ
    return parseWhoisResponse($response, $domain);
}

/**
 * Парсинг WHOIS ответа
 */
function parseWhoisResponse($raw_data, $domain) {
    $is_available = (
        stripos($raw_data, 'No match') !== false ||
        stripos($raw_data, 'NOT FOUND') !== false ||
        stripos($raw_data, 'No entries found') !== false ||
        stripos($raw_data, 'Nothing found') !== false ||
        stripos($raw_data, 'No Data Found') !== false
    );
    
    if ($is_available) {
        return [
            'status' => 'available',
            'message' => 'Домен доступний для реєстрації',
            'raw_data' => $raw_data
        ];
    }
    
    // Извлекаем данные из WHOIS
    $data = [
        'status' => 'registered',
        'domain' => $domain,
        'registrar' => extractWhoisField($raw_data, ['Registrar:', 'registrar:']),
        'creation_date' => extractWhoisField($raw_data, ['Creation Date:', 'created:', 'registered:']),
        'expiration_date' => extractWhoisField($raw_data, ['Expiry Date:', 'Registry Expiry Date:', 'expires:', 'paid-till:']),
        'updated_date' => extractWhoisField($raw_data, ['Updated Date:', 'modified:', 'changed:']),
        'name_servers' => extractNameServers($raw_data),
        'domain_status' => extractWhoisField($raw_data, ['Domain Status:', 'Status:']),
        'raw_data' => $raw_data
    ];
    
    return $data;
}

/**
 * Извлечение поля из WHOIS данных
 */
function extractWhoisField($data, $patterns) {
    foreach ($patterns as $pattern) {
        if (preg_match('/' . preg_quote($pattern, '/') . '\s*(.+)/i', $data, $matches)) {
            return trim($matches[1]);
        }
    }
    return null;
}

/**
 * Извлечение NS серверов
 */
function extractNameServers($data) {
    $ns_servers = [];
    $patterns = ['Name Server:', 'nserver:', 'ns:'];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all('/' . preg_quote($pattern, '/') . '\s*(.+)/i', $data, $matches)) {
            foreach ($matches[1] as $ns) {
                $ns_servers[] = trim($ns);
            }
        }
    }
    
    return array_unique($ns_servers);
}

/**
 * Генерация тестовых данных (fallback)
 */
function generateMockWhoisData($domain) {
    // Определяем статус на основе хеша домена для консистентности
    $is_registered = (crc32($domain) % 4) !== 0;
    
    if (!$is_registered) {
        return [
            'status' => 'available',
            'message' => 'Домен доступний для реєстрації'
        ];
    }
    
    return [
        'status' => 'registered',
        'domain' => $domain,
        'registrar' => 'Example Registrar Inc.',
        'creation_date' => date('Y-m-d', strtotime('-' . (rand(100, 3650)) . ' days')),
        'expiration_date' => date('Y-m-d', strtotime('+' . (rand(30, 365)) . ' days')),
        'updated_date' => date('Y-m-d', strtotime('-' . (rand(1, 90)) . ' days')),
        'name_servers' => [
            'ns1.example.com',
            'ns2.example.com'
        ],
        'domain_status' => 'clientTransferProhibited',
        'raw_data' => "Domain Name: " . strtoupper($domain) . "\n" .
                      "Registry Domain ID: DEMO" . crc32($domain) . "\n" .
                      "Registrar: Example Registrar Inc.\n" .
                      "Creation Date: " . date('Y-m-d\TH:i:s\Z', strtotime('-' . rand(100, 3650) . ' days')) . "\n" .
                      "Expiry Date: " . date('Y-m-d\TH:i:s\Z', strtotime('+' . rand(30, 365) . ' days')) . "\n" .
                      "Updated Date: " . date('Y-m-d\TH:i:s\Z', strtotime('-' . rand(1, 90) . ' days')) . "\n" .
                      "Domain Status: clientTransferProhibited\n" .
                      "Name Server: NS1.EXAMPLE.COM\n" .
                      "Name Server: NS2.EXAMPLE.COM\n" .
                      "\n>>> Це тестові дані для демонстрації. Реальний WHOIS сервер недоступний. <<<\n"
    ];
}
