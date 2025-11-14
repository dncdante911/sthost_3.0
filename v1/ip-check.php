<?php
/**
 * IP Check API Endpoint
 * POST /v1/ip-check
 *
 * Детальная проверка IP адресов: геолокация, blacklist, ASN, угрозы
 */

// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Заголовки JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed. Use POST.',
        'code' => 405
    ]);
    exit();
}

// Получение данных из FormData
$ip = isset($_POST['ip']) ? trim($_POST['ip']) : '';
$options = isset($_POST['options']) ? json_decode($_POST['options'], true) : [];
$userLocation = isset($_POST['user_location']) ? json_decode($_POST['user_location'], true) : null;

// Валидация IP адреса
if (empty($ip)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required parameter: ip',
        'code' => 400
    ]);
    exit();
}

// Проверка формата IP
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid IP address format',
        'code' => 400
    ]);
    exit();
}

// Определение типа IP
$ipVersion = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 'IPv6' : 'IPv4';
$ipType = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
    ? 'Публічна'
    : 'Приватна/Зарезервована';

/**
 * Получение геолокации IP через ipapi.co
 */
function getIPLocation($ip) {
    try {
        $url = "https://ipapi.co/{$ip}/json/";
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'StormHosting IP Checker/1.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);

            if (isset($data['error'])) {
                return null;
            }

            return [
                'country' => $data['country_name'] ?? 'Невідомо',
                'country_code' => $data['country_code'] ?? '',
                'region' => $data['region'] ?? 'Невідомо',
                'city' => $data['city'] ?? 'Невідомо',
                'postal' => $data['postal'] ?? '',
                'latitude' => $data['latitude'] ?? 0,
                'longitude' => $data['longitude'] ?? 0,
                'timezone' => $data['timezone'] ?? 'UTC',
                'continent' => $data['continent_code'] ?? '',
            ];
        }
    } catch (Exception $e) {
        error_log("IP Location Error: " . $e->getMessage());
    }

    return null;
}

/**
 * Получение сетевой информации
 */
function getNetworkInfo($ip) {
    try {
        $url = "https://ipapi.co/{$ip}/json/";
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'StormHosting IP Checker/1.0',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);

            return [
                'isp' => $data['org'] ?? 'Невідомо',
                'org' => $data['org'] ?? 'Невідомо',
                'asn' => $data['asn'] ?? 'N/A',
                'connection_type' => $data['connection_type'] ?? 'Невідомо',
                'usage_type' => $data['network'] ?? 'Невідомо',
                'is_proxy' => isset($data['in_eu']) ? false : false, // ipapi.co doesn't provide this directly
            ];
        }
    } catch (Exception $e) {
        error_log("Network Info Error: " . $e->getMessage());
    }

    return [
        'isp' => 'Невідомо',
        'org' => 'Невідомо',
        'asn' => 'N/A',
        'connection_type' => 'Невідомо',
        'usage_type' => 'Невідомо',
        'is_proxy' => false,
    ];
}

/**
 * Проверка черных списков (DNSBL)
 */
function checkBlacklists($ip) {
    // Только для IPv4
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return [];
    }

    // Реверсируем IP для DNSBL запросов
    $reversedIP = implode('.', array_reverse(explode('.', $ip)));

    $blacklists = [
        'zen.spamhaus.org' => 'Spamhaus ZEN',
        'bl.spamcop.net' => 'SpamCop',
        'dnsbl.sorbs.net' => 'SORBS',
        'b.barracudacentral.org' => 'Barracuda',
        'dnsbl-1.uceprotect.net' => 'UCEPROTECT L1',
        'multi.surbl.org' => 'SURBL',
        'cbl.abuseat.org' => 'CBL',
        'psbl.surriel.com' => 'PSBL',
    ];

    $results = [];

    foreach ($blacklists as $dnsbl => $name) {
        $query = "{$reversedIP}.{$dnsbl}";
        $listed = (bool)gethostbyname($query) !== $query;

        $results[] = [
            'name' => $name,
            'listed' => $listed,
            'checked' => true,
        ];
    }

    return $results;
}

/**
 * Анализ угроз (упрощенный)
 */
function checkThreats($ip) {
    // Простая проверка: приватные IP безопасны
    $isPrivate = !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

    if ($isPrivate) {
        return [
            'risk_level' => 'Низький',
            'confidence' => 0,
            'last_seen' => 'Н/Д',
            'categories' => [],
        ];
    }

    // Для публичных IP можно добавить интеграцию с API угроз
    // Например: AbuseIPDB, VirusTotal, etc.

    return [
        'risk_level' => 'Низький',
        'confidence' => 5,
        'last_seen' => 'Невідомо',
        'categories' => [],
    ];
}

/**
 * Расчет расстояния между двумя координатами (формула Haversine)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // км

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;

    return [
        'km' => round($distance, 2),
        'miles' => round($distance * 0.621371, 2),
        'flight_time' => estimateFlightTime($distance),
    ];
}

/**
 * Приблизительное время полета
 */
function estimateFlightTime($km) {
    $avgSpeed = 800; // км/ч для коммерческого рейса
    $hours = $km / $avgSpeed;

    if ($hours < 1) {
        return round($hours * 60) . ' хв';
    } elseif ($hours < 24) {
        return round($hours, 1) . ' год';
    } else {
        return round($hours / 24, 1) . ' днів';
    }
}

/**
 * Получение погоды для локации
 */
function getWeather($lat, $lon) {
    // Используем бесплатный API Open-Meteo
    try {
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current_weather=true";
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $data = json_decode($response, true);

            if (isset($data['current_weather'])) {
                $current = $data['current_weather'];

                return [
                    'temperature' => round($current['temperature']),
                    'condition' => getWeatherCondition($current['weathercode']),
                    'description' => getWeatherDescription($current['weathercode']),
                    'humidity' => 'Н/Д', // Open-Meteo doesn't provide humidity in free tier
                    'wind_speed' => round($current['windspeed'] / 3.6, 1), // конвертируем km/h в m/s
                    'visibility' => 'Н/Д',
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Weather Error: " . $e->getMessage());
    }

    return null;
}

function getWeatherCondition($code) {
    $conditions = [
        0 => 'clear',
        1 => 'clear',
        2 => 'cloudy',
        3 => 'cloudy',
        45 => 'cloudy',
        48 => 'cloudy',
        51 => 'rain',
        53 => 'rain',
        55 => 'rain',
        61 => 'rain',
        63 => 'rain',
        65 => 'rain',
        71 => 'snow',
        73 => 'snow',
        75 => 'snow',
        95 => 'storm',
        96 => 'storm',
        99 => 'storm',
    ];

    return $conditions[$code] ?? 'cloudy';
}

function getWeatherDescription($code) {
    $descriptions = [
        0 => 'Ясно',
        1 => 'Переважно ясно',
        2 => 'Частково хмарно',
        3 => 'Хмарно',
        45 => 'Туман',
        48 => 'Туман з ожеледицею',
        51 => 'Легка мряка',
        53 => 'Мряка',
        55 => 'Сильна мряка',
        61 => 'Легкий дощ',
        63 => 'Дощ',
        65 => 'Сильний дощ',
        71 => 'Легкий сніг',
        73 => 'Сніг',
        75 => 'Сильний сніг',
        95 => 'Гроза',
        96 => 'Гроза з градом',
        99 => 'Сильна гроза з градом',
    ];

    return $descriptions[$code] ?? 'Хмарно';
}

// Собираем данные
$response = [
    'general' => [
        'ip' => $ip,
        'ip_type' => $ipType,
        'is_valid' => true,
        'check_time' => date('c'),
    ],
];

// Геолокация
$location = getIPLocation($ip);
if ($location) {
    $response['location'] = $location;
}

// Сетевая информация
$response['network'] = getNetworkInfo($ip);

// Черные списки (только если опция включена)
if (isset($options['checkBlacklists']) && $options['checkBlacklists']) {
    $response['blacklists'] = checkBlacklists($ip);
}

// Анализ угроз (только если опция включена)
if (isset($options['checkThreatIntel']) && $options['checkThreatIntel']) {
    $response['threats'] = checkThreats($ip);
}

// Расчет расстояния (если есть локация пользователя и опция включена)
if (isset($options['checkDistance']) && $options['checkDistance'] && $userLocation && $location) {
    $response['distance'] = calculateDistance(
        $userLocation['lat'],
        $userLocation['lng'],
        $location['latitude'],
        $location['longitude']
    );
}

// Погода в регионе IP
if ($location && isset($location['latitude']) && isset($location['longitude'])) {
    $weather = getWeather($location['latitude'], $location['longitude']);
    if ($weather) {
        $response['weather'] = $weather;
    }
}

// Возвращаем результат
http_response_code(200);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
