<?php
/**
 * Domain Transfer API Endpoint
 * Обрабатывает заявки на трансфер доменов
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
    echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
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
    echo json_encode(['error' => 'Введіть домен для трансферу'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($contact_email)) {
    echo json_encode(['error' => 'Введіть email для зв\'язку'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Валидация email
if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Невірний формат email адреси'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Валидация домена
$domain = strtolower($domain);
if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $domain)) {
    echo json_encode(['error' => 'Невірний формат домену. Приклад: example.com'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Определяем зону домена
$domain_parts = explode('.', $domain);
$zone = '.' . end($domain_parts);

// Для многоуровневых зон (.com.ua, .kiev.ua и т.д.)
if (count($domain_parts) > 2 && in_array(end($domain_parts), ['ua'])) {
    $zone = '.' . $domain_parts[count($domain_parts)-2] . '.ua';
}

// Получаем цену из БД
$price = 400; // Default fallback

try {
    $db_path = $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
    if (file_exists($db_path)) {
        require_once $db_path;

        if (isset($pdo)) {
            $stmt = $pdo->prepare("
                SELECT price_transfer
                FROM domain_zones
                WHERE zone = ? AND is_active = 1 AND price_transfer > 0
                LIMIT 1
            ");
            $stmt->execute([$zone]);
            $zone_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($zone_data) {
                $price = $zone_data['price_transfer'];
            }
        }
    }
} catch (Exception $e) {
    error_log('Transfer price DB error: ' . $e->getMessage());

    // Fallback prices if DB is not available
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
}

// Сохраняем заявку в базу данных (если доступно)
$request_id = null;
try {
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
    error_log('Transfer DB error: ' . $e->getMessage());
}

// Отправка email уведомления администратору
$admin_email = 'domains@sthost.pro';
$subject = '🌐 Нова заявка на трансфер домену: ' . $domain;

$email_body = "
═══════════════════════════════════════════════
  НОВА ЗАЯВКА НА ТРАНСФЕР ДОМЕНУ
═══════════════════════════════════════════════

📌 ІНФОРМАЦІЯ ПРО ДОМЕН:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Домен:          $domain
Зона:           $zone
Ціна трансферу: $price грн (включає +1 рік)

🔑 КОД АВТОРИЗАЦІЇ:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
" . ($auth_code ? "EPP код:       $auth_code" : "EPP код:       НЕ ВКАЗАНО (клієнт надасть пізніше)") . "

👤 КОНТАКТНІ ДАНІ:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Email:          $contact_email
Телефон:        " . ($phone ?: 'не вказано') . "

📝 ПРИМІТКИ:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
" . ($notes ?: 'немає') . "

🔍 ТЕХНІЧНА ІНФОРМАЦІЯ:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
IP адреса:      " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "
User Agent:     " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "
Дата заявки:    " . date('d.m.Y H:i:s') . "
ID заявки:      " . ($request_id ?: 'N/A') . "

═══════════════════════════════════════════════
  Автоматичне повідомлення від StormHosting UA
═══════════════════════════════════════════════
";

$email_headers = "From: StormHosting Transfer <noreply@sthost.pro>\r\n";
$email_headers .= "Reply-To: $contact_email\r\n";
$email_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$email_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

@mail($admin_email, $subject, $email_body, $email_headers);

// Отправка подтверждения клиенту
$client_subject = "✅ Заявка на трансфер домену $domain прийнята";
$client_body = "
Вітаємо!

Ваша заявка на трансфер домену $domain успішно прийнята!

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
ДЕТАЛІ ЗАЯВКИ:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Домен:          $domain
Вартість:       $price грн (включає продовження на 1 рік)
Дата заявки:    " . date('d.m.Y H:i:s') . "

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
НАСТУПНІ КРОКИ:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Перевірте email для підтвердження трансферу
2. Наш менеджер зв'яжеться з вами протягом 24 годин
3. Приготуйте EPP/Auth код від поточного реєстратора
4. Після підтвердження трансфер займе 3-7 днів

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Якщо у вас виникнуть питання, звертайтесь:
📧 Email: domains@sthost.pro
💬 Telegram: @sthost_support

З повагою,
Команда StormHosting UA
https://sthost.pro
";

$client_headers = "From: StormHosting UA <noreply@sthost.pro>\r\n";
$client_headers .= "Reply-To: domains@sthost.pro\r\n";
$client_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

@mail($contact_email, $client_subject, $client_body, $client_headers);

// Возвращаем успешный ответ
echo json_encode([
    'success' => true,
    'message' => 'Дякуємо! Ваша заявка на трансфер успішно подана.',
    'domain' => $domain,
    'zone' => $zone,
    'price' => $price,
    'request_id' => $request_id,
    'next_steps' => [
        'Перевірте вашу поштову скриньку ' . $contact_email,
        'Очікуйте дзвінок від нашого менеджера протягом 24 годин',
        'Приготуйте EPP/Auth код від поточного реєстратора',
        'Підтвердіть трансфер через email',
        'Процес трансферу займе від 3 до 7 днів'
    ]
], JSON_UNESCAPED_UNICODE);
