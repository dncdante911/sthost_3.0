<?php
/**
 * Domain Transfer API Endpoint
 * Обработка запросов на трансфер доменов
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

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получаем данные запроса
$domain = trim($_POST['domain'] ?? '');
$auth_code = trim($_POST['auth_code'] ?? '');
$contact_email = trim($_POST['contact_email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// Валидация обязательных полей
if (empty($domain)) {
    echo json_encode(['error' => 'Введіть домен для трансферу']);
    exit;
}

if (empty($contact_email)) {
    echo json_encode(['error' => 'Введіть email для зв\'язку']);
    exit;
}

// Валидация email
if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Невірний формат email']);
    exit;
}

// Валидация домена
$domain = strtolower($domain);
if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $domain)) {
    echo json_encode(['error' => 'Невірний формат домену. Приклад: example.com']);
    exit;
}

// Определяем зону домена и цену
$domain_parts = explode('.', $domain);
$zone = '.' . end($domain_parts);
if (count($domain_parts) > 2 && in_array(end($domain_parts), ['ua'])) {
    $zone = '.' . $domain_parts[count($domain_parts)-2] . '.ua';
}

// Таблица цен на трансфер
$transfer_prices = [
    '.ua' => 180,
    '.com.ua' => 130,
    '.kiev.ua' => 160,
    '.net.ua' => 160,
    '.org.ua' => 160,
    '.com' => 300,
    '.net' => 400,
    '.org' => 350,
    '.info' => 300,
    '.biz' => 300
];

$price = $transfer_prices[$zone] ?? 400;

// Сохраняем заявку в БД (опционально)
try {
    // Подключение к БД если доступно
    $db_path = $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
    if (file_exists($db_path)) {
        require_once $db_path;
        
        if (isset($pdo)) {
            $stmt = $pdo->prepare("
                INSERT INTO domain_transfer_requests 
                (domain, zone, auth_code, contact_email, phone, notes, price, ip_address, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $domain,
                $zone,
                $auth_code,
                $contact_email,
                $phone,
                $notes,
                $price,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            $request_id = $pdo->lastInsertId();
        }
    }
} catch (Exception $e) {
    // Логируем ошибку, но продолжаем
    error_log('Transfer request DB error: ' . $e->getMessage());
}

// Отправка email уведомления (опционально)
$admin_email = 'domains@sthost.pro';
$subject = 'Нова заявка на трансфер домену: ' . $domain;
$message = "
Отримано нову заявку на трансфер домену

Домен: $domain
Зона: $zone
Ціна трансферу: $price грн

Контактні дані:
Email: $contact_email
Телефон: $phone

Код авторизації: " . ($auth_code ? '****' . substr($auth_code, -4) : 'не вказано') . "

Примітки: $notes

IP адреса: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "
Дата: " . date('Y-m-d H:i:s') . "
";

@mail($admin_email, $subject, $message, "From: noreply@sthost.pro\r\nReply-To: $contact_email");

// Возвращаем успешный ответ
echo json_encode([
    'success' => true,
    'message' => 'Заявка на трансфер успішно подана!',
    'domain' => $domain,
    'zone' => $zone,
    'price' => $price,
    'request_id' => $request_id ?? null,
    'next_steps' => [
        'Перевірте email для підтвердження',
        'Очікуйте на зв\'язок від нашої команди протягом 24 годин',
        'Приготуйте код авторизації від поточного реєстратора'
    ]
], JSON_UNESCAPED_UNICODE);
