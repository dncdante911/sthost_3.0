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

// Подключение конфигурации
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

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
if (!preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]\.[a-z]{2,}$|^[a-z0-9]\.[a-z]{2,}$/', $domain)) {
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
            return [
                'status' => 'no_records',
                'message' => 'DNS записи типу ' . $record_type . ' не знайдено для домену ' . $domain
            ];
        }
        
        // Обрабатываем записи в зависимости от типа
        foreach ($records as $record) {
            $formatted_record = formatDNSRecord($record, $record_type);
            if ($formatted_record) {
                $results[] = $formatted_record;
            }
        }
        
        return [
            'status' => 'success',
            'count' => count($results),
            'records' => $results
        ];
        
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
        'host' => $record['host'] ?? null,
        'ttl' => $record['ttl'] ?? null
    ];
    
    switch ($type) {
        case 'A':
            $formatted['ip'] = $record['ip'] ?? null;
            $formatted['ipv4'] = $record['ip'] ?? null;
            break;
            
        case 'AAAA':
            $formatted['ip'] = $record['ipv6'] ?? null;
            $formatted['ipv6'] = $record['ipv6'] ?? null;
            break;
            
        case 'MX':
            $formatted['target'] = $record['target'] ?? null;
            $formatted['priority'] = $record['pri'] ?? null;
            $formatted['mx'] = $record['target'] ?? null;
            break;
            
        case 'CNAME':
            $formatted['target'] = $record['target'] ?? null;
            $formatted['cname'] = $record['target'] ?? null;
            break;
            
        case 'TXT':
            $formatted['text'] = $record['txt'] ?? null;
            $formatted['txt'] = $record['txt'] ?? null;
            break;
            
        case 'NS':
            $formatted['target'] = $record['target'] ?? null;
            $formatted['nameserver'] = $record['target'] ?? null;
            break;
            
        case 'SOA':
            $formatted['mname'] = $record['mname'] ?? null;
            $formatted['rname'] = $record['rname'] ?? null;
            $formatted['serial'] = $record['serial'] ?? null;
            $formatted['refresh'] = $record['refresh'] ?? null;
            $formatted['retry'] = $record['retry'] ?? null;
            $formatted['expire'] = $record['expire'] ?? null;
            $formatted['minimum'] = $record['minimum-ttl'] ?? null;
            break;
            
        case 'PTR':
            $formatted['target'] = $record['target'] ?? null;
            $formatted['ptr'] = $record['target'] ?? null;
            break;
            
        case 'SRV':
            $formatted['target'] = $record['target'] ?? null;
            $formatted['priority'] = $record['pri'] ?? null;
            $formatted['weight'] = $record['weight'] ?? null;
            $formatted['port'] = $record['port'] ?? null;
            break;
    }
    
    return $formatted;
}
