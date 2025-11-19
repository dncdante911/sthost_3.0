<?php
/**
 * WHOIS Lookup API Endpoint
 * Performs WHOIS queries for domains
 */

define('SECURE_ACCESS', true);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// WHOIS servers configuration (популярні зони для швидкості)
$whois_servers = [
    '.ua' => 'whois.ua',
    '.com.ua' => 'whois.ua',
    '.kiev.ua' => 'whois.ua',
    '.net.ua' => 'whois.ua',
    '.org.ua' => 'whois.ua',
    '.com' => 'whois.verisign-grs.com',
    '.net' => 'whois.verisign-grs.com',
    '.org' => 'whois.pir.org',
    '.info' => 'whois.afilias.net',
    '.biz' => 'whois.biz',
    '.club' => 'whois.nic.club',
    '.pro' => 'whois.afilias.net',
    '.eu' => 'whois.eu',
    '.de' => 'whois.denic.de',
    '.pl' => 'whois.dns.pl',
    '.ru' => 'whois.tcinet.ru',
    '.su' => 'whois.tcinet.ru',
    '.xyz' => 'whois.nic.xyz',
    '.online' => 'whois.nic.online',
    '.site' => 'whois.nic.site',
    '.store' => 'whois.nic.store',
    '.tech' => 'whois.nic.tech',
    '.space' => 'whois.nic.space'
];

// Get domain
$domain = trim($_POST['domain'] ?? '');

if (empty($domain)) {
    echo json_encode(['error' => 'Введіть ім\'я домену'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate domain
$domain = strtolower($domain);
if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $domain)) {
    echo json_encode(['error' => 'Невірний формат домену'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Determine WHOIS server
$domain_parts = explode('.', $domain);
$tld = '.' . end($domain_parts);

// For multi-level TLDs like .com.ua
if (count($domain_parts) > 2 && in_array(end($domain_parts), ['ua'])) {
    $tld = '.' . $domain_parts[count($domain_parts)-2] . '.ua';
}

$whois_server = $whois_servers[$tld] ?? null;

// Якщо сервер не знайдено - використовуємо IANA для автоматичного визначення
if (!$whois_server) {
    $whois_server = 'whois.iana.org';
}

// Perform WHOIS query
try {
    $whois_data = performWhoisQuery($domain, $whois_server);
    $parsed_data = parseWhoisData($whois_data, $tld);

    echo json_encode([
        'success' => true,
        'domain' => $domain,
        'whois_server' => $whois_server,
        'data' => $parsed_data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Помилка виконання WHOIS запиту: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Perform WHOIS query via socket
 */
function performWhoisQuery($domain, $server, $port = 43) {
    $fp = @fsockopen($server, $port, $errno, $errstr, 10);

    if (!$fp) {
        throw new Exception("Не вдалося підключитись до WHOIS сервера: $errstr ($errno)");
    }

    // Send query
    fputs($fp, $domain . "\r\n");

    // Read response
    $response = '';
    while (!feof($fp)) {
        $response .= fgets($fp, 128);
    }

    fclose($fp);

    if (empty($response)) {
        throw new Exception('Отримано порожню відповідь від WHOIS сервера');
    }

    return $response;
}

/**
 * Parse WHOIS data
 */
function parseWhoisData($raw_data, $tld) {
    $data = [
        'status' => 'registered',
        'raw_data' => $raw_data,
        'creation_date' => null,
        'expiration_date' => null,
        'updated_date' => null,
        'registrar' => null,
        'registrar_url' => null,
        'status_list' => [],
        'name_servers' => []
    ];

    // Parse dates FIRST (before checking availability)
    if (preg_match('/(?:creation date|created|registered):\s*([^\r\n]+)/i', $raw_data, $matches)) {
        $data['creation_date'] = trim($matches[1]);
    }

    if (preg_match('/(?:expir|registry expiry date|renewal date).*?:\s*([^\r\n]+)/i', $raw_data, $matches)) {
        $data['expiration_date'] = trim($matches[1]);
    }

    if (preg_match('/(?:updated|modified|last updated|changed).*?:\s*([^\r\n]+)/i', $raw_data, $matches)) {
        $data['updated_date'] = trim($matches[1]);
    }

    // Parse registrar
    if (preg_match('/registrar:\s*([^\r\n]+)/i', $raw_data, $matches)) {
        $data['registrar'] = trim($matches[1]);
    }

    if (preg_match('/registrar url:\s*([^\r\n]+)/i', $raw_data, $matches)) {
        $data['registrar_url'] = trim($matches[1]);
    }

    // Parse status
    if (preg_match_all('/(?:domain )?status:\s*([^\r\n]+)/i', $raw_data, $matches)) {
        $data['status_list'] = array_map('trim', $matches[1]);
        $data['status_list'] = array_unique($data['status_list']);
    }

    // Parse name servers
    if (preg_match_all('/(?:name server|nserver|ns):\s*([^\r\n\s]+)/i', $raw_data, $matches)) {
        $data['name_servers'] = array_map('strtolower', array_map('trim', $matches[1]));
        $data['name_servers'] = array_unique($data['name_servers']);
        $data['name_servers'] = array_values($data['name_servers']);
    }

    // Check if domain is REGISTERED (if we have registrar OR creation date OR nameservers)
    // This is the CORRECT way - if domain has registration info, it's registered!
    if (!empty($data['registrar']) || !empty($data['creation_date']) || !empty($data['name_servers'])) {
        $data['status'] = 'registered';
        return $data;
    }

    // IMPROVED: Check if domain is available with MORE SPECIFIC patterns
    // These patterns must be at the BEGINNING of a line or after specific keywords
    $availability_patterns = [
        '/^no match for/im',                    // "No match for domain..."
        '/^not found:/im',                      // "Not found: domain..."
        '/domain not found/im',                 // "Domain not found"
        '/no entries found/im',                 // "No entries found"
        '/no data found/im',                    // "No data found"
        '/status:\s*free/im',                   // "Status: free"
        '/status:\s*available/im',              // "Status: available"
        '/is available for/im',                 // "...is available for registration"
        '/NOT FOUND/m',                         // Some servers return all caps
        '/no match$/im',                        // "No match" at end of line
        '/вільний/iu',                          // "вільний" (Ukrainian: free)
        '/домен вільний/iu',                    // "домен вільний"
    ];

    foreach ($availability_patterns as $pattern) {
        if (preg_match($pattern, $raw_data)) {
            $data['status'] = 'available';
            return $data;
        }
    }

    // If we got here, domain is registered (we have WHOIS data but no "available" markers)
    $data['status'] = 'registered';
    return $data;
}
