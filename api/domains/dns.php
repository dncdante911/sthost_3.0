<?php
/**
 * DNS Lookup API Endpoint
 * Выполняет DNS запросы для доменов
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

// Типы DNS записей
$dns_record_types = [
    'A' => DNS_A,
    'AAAA' => DNS_AAAA,
    'MX' => DNS_MX,
    'CNAME' => DNS_CNAME,
    'TXT' => DNS_TXT,
    'NS' => DNS_NS,
    'SOA' => DNS_SOA,
    'PTR' => DNS_PTR,
    'SRV' => DNS_SRV
];

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получаем данные запроса
$domain = trim($_POST['domain'] ?? '');
$record_type = strtoupper(trim($_POST['record_type'] ?? 'A'));

if (empty($domain)) {
    echo json_encode(['error' => 'Введіть ім\'я домену']);
    exit;
}

// Валидация домена
$domain = strtolower($domain);
if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $domain)) {
    echo json_encode(['error' => 'Невірний формат домену']);
    exit;
}

// Валидация типа записи
if (!array_key_exists($record_type, $dns_record_types)) {
    echo json_encode([
        'error' => 'Невідомий тип DNS запису',
        'supported_types' => array_keys($dns_record_types)
    ]);
    exit;
}

// Выполняем DNS lookup
try {
    $dns_results = performDNSLookup($domain, $record_type, $dns_record_types[$record_type]);

    echo json_encode([
        'success' => true,
        'domain' => $domain,
        'record_type' => $record_type,
        'results' => $dns_results,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Помилка виконання DNS запиту: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Функция выполнения DNS lookup
 */
function performDNSLookup($domain, $record_type, $dns_type) {
    $results = [];

    try {
        // Получаем DNS записи
        $records = @dns_get_record($domain, $dns_type);

        if ($records === false || empty($records)) {
            return [];
        }

        // Обрабатываем записи в зависимости от типа
        foreach ($records as $record) {
            $formatted_record = formatDNSRecord($record, $record_type);
            if ($formatted_record) {
                $results[] = $formatted_record;
            }
        }

        return $results;

    } catch (Exception $e) {
        throw new Exception('DNS lookup failed: ' . $e->getMessage());
    }
}

/**
 * Форматирование DNS записи
 */
function formatDNSRecord($record, $type) {
    $formatted = [
        'type' => $type,
        'host' => $record['host'] ?? '',
        'ttl' => $record['ttl'] ?? 0
    ];

    switch ($type) {
        case 'A':
            $formatted['ip'] = $record['ip'] ?? '';
            break;

        case 'AAAA':
            $formatted['ipv6'] = $record['ipv6'] ?? '';
            break;

        case 'MX':
            $formatted['target'] = $record['target'] ?? '';
            $formatted['pri'] = $record['pri'] ?? 0;
            break;

        case 'CNAME':
            $formatted['target'] = $record['target'] ?? '';
            break;

        case 'TXT':
            $formatted['txt'] = $record['txt'] ?? '';
            break;

        case 'NS':
            $formatted['target'] = $record['target'] ?? '';
            break;

        case 'SOA':
            $formatted['mname'] = $record['mname'] ?? '';
            $formatted['rname'] = $record['rname'] ?? '';
            $formatted['serial'] = $record['serial'] ?? 0;
            $formatted['refresh'] = $record['refresh'] ?? 0;
            $formatted['retry'] = $record['retry'] ?? 0;
            $formatted['expire'] = $record['expire'] ?? 0;
            $formatted['minimum'] = $record['minimum-ttl'] ?? 0;
            break;

        case 'PTR':
            $formatted['target'] = $record['target'] ?? '';
            break;

        case 'SRV':
            $formatted['target'] = $record['target'] ?? '';
            $formatted['pri'] = $record['pri'] ?? 0;
            $formatted['weight'] = $record['weight'] ?? 0;
            $formatted['port'] = $record['port'] ?? 0;
            break;
    }

    return $formatted;
}
