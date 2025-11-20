<?php
/**
 * API моніторингу серверів
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Завантаження конфігу
$config_file = __DIR__ . '/../../config/monitoring.config.php';
if (!file_exists($config_file)) {
    echo json_encode(['success' => false, 'error' => 'Config not found']);
    exit;
}
$config = require $config_file;

// Кешування
$cache_file = sys_get_temp_dir() . '/sthost_monitor_v3.json';
$cache_ttl = $config['cache_ttl'] ?? 60;
$force_refresh = isset($_GET['refresh']);

// Перевірка кешу
if (!$force_refresh && file_exists($cache_file)) {
    $cache = json_decode(file_get_contents($cache_file), true);
    if ($cache && (time() - $cache['time']) < $cache_ttl) {
        echo json_encode(['success' => true, 'cached' => true, 'data' => $cache['data']]);
        exit;
    }
}

/**
 * Перевірка ISPmanager API
 */
function check_ispmanager($server) {
    $url = ($server['ssl'] ? 'https' : 'http') . "://{$server['host']}:{$server['port']}/ispmgr";
    $auth = '?authinfo=' . urlencode($server['username']) . ':' . urlencode($server['password']) . '&func=dashboard&out=json';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url . $auth,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $start = microtime(true);
    $response = curl_exec($ch);
    $response_time = round((microtime(true) - $start) * 1000);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $online = ($http_code >= 200 && $http_code < 400);
    $cpu = 0;
    $memory = 0;

    if ($online && $response) {
        $data = json_decode($response, true);
        if ($data && isset($data['doc']['sysinfo'])) {
            $cpu = $data['doc']['sysinfo']['cpu']['used'] ?? 0;
            $memory = $data['doc']['sysinfo']['mem']['used'] ?? 0;
        }
    }

    return [
        'online' => $online,
        'response_time' => $response_time,
        'cpu' => round($cpu, 1),
        'memory' => round($memory, 1)
    ];
}

/**
 * Перевірка Proxmox API
 */
function check_proxmox($server) {
    $auth_url = "https://{$server['host']}:{$server['port']}/api2/json/access/ticket";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $auth_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'username' => $server['username'],
            'password' => $server['password']
        ]),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $start = microtime(true);
    $response = curl_exec($ch);
    $response_time = round((microtime(true) - $start) * 1000);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $online = ($http_code == 200);
    $cpu = 0;
    $memory = 0;

    if ($online) {
        $auth_data = json_decode($response, true);
        $ticket = $auth_data['data']['ticket'] ?? '';
        $csrf = $auth_data['data']['CSRFPreventionToken'] ?? '';

        if ($ticket) {
            $node = $server['node'] ?? 'pve';
            $status_url = "https://{$server['host']}:{$server['port']}/api2/json/nodes/{$node}/status";

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $status_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Cookie: PVEAuthCookie={$ticket}",
                    "CSRFPreventionToken: {$csrf}"
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $status_response = curl_exec($ch);
            curl_close($ch);

            $status_data = json_decode($status_response, true);
            if ($status_data && isset($status_data['data'])) {
                $cpu = ($status_data['data']['cpu'] ?? 0) * 100;
                $mem_used = $status_data['data']['memory']['used'] ?? 0;
                $mem_total = $status_data['data']['memory']['total'] ?? 1;
                $memory = ($mem_used / $mem_total) * 100;
            }
        }
    }

    return [
        'online' => $online,
        'response_time' => $response_time,
        'cpu' => round($cpu, 1),
        'memory' => round($memory, 1)
    ];
}

/**
 * Перевірка HAProxy stats
 */
function check_haproxy($server) {
    $url = $server['stats_url'] . ';csv';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $start = microtime(true);
    $response = curl_exec($ch);
    $response_time = round((microtime(true) - $start) * 1000);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $online = ($http_code >= 200 && $http_code < 400);
    $sessions = 0;
    $backends = 0;

    if ($online && $response) {
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            $parts = str_getcsv($line);
            if (count($parts) > 4 && ($parts[1] ?? '') === 'BACKEND') {
                $backends++;
            }
        }
    }

    return [
        'online' => $online,
        'response_time' => $response_time,
        'sessions' => $sessions,
        'backends' => $backends
    ];
}

/**
 * Проста HTTP перевірка
 */
function check_http($server) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $server['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_NOBODY => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $start = microtime(true);
    curl_exec($ch);
    $response_time = round((microtime(true) - $start) * 1000);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'online' => ($http_code >= 200 && $http_code < 400),
        'response_time' => $response_time
    ];
}

// Перевіряємо всі сервери
$results = [];

foreach ($config['servers'] as $server) {
    $result = [
        'id' => $server['id'],
        'name' => $server['name'],
        'color' => $server['color'],
        'online' => false,
        'response_time' => 0,
        'metrics' => []
    ];

    try {
        switch ($server['type']) {
            case 'ispmanager':
                $check = check_ispmanager($server);
                $result['online'] = $check['online'];
                $result['response_time'] = $check['response_time'];
                $result['metrics'] = ['cpu' => $check['cpu'], 'memory' => $check['memory']];
                break;

            case 'proxmox':
                $check = check_proxmox($server);
                $result['online'] = $check['online'];
                $result['response_time'] = $check['response_time'];
                $result['metrics'] = ['cpu' => $check['cpu'], 'memory' => $check['memory']];
                break;

            case 'haproxy':
                $check = check_haproxy($server);
                $result['online'] = $check['online'];
                $result['response_time'] = $check['response_time'];
                $result['metrics'] = ['sessions' => $check['sessions'], 'backends' => $check['backends']];
                break;

            case 'http':
                $check = check_http($server);
                $result['online'] = $check['online'];
                $result['response_time'] = $check['response_time'];
                break;
        }
    } catch (Exception $e) {
        error_log("Monitor error for {$server['id']}: " . $e->getMessage());
    }

    $results[] = $result;
}

// Зберігаємо в кеш
file_put_contents($cache_file, json_encode([
    'time' => time(),
    'data' => $results
]));

echo json_encode(['success' => true, 'cached' => false, 'data' => $results]);
