<?php
//echo "CONFIG LOADED<br>";
//exit;
// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Load environment variables
require_once __DIR__ . '/env_loader.php';

// Основные настройки сайта
define('SITE_NAME', 'StormHosting UA');
define('SITE_URL', 'https://sthost.pro');
define('SITE_EMAIL', 'info@sthost.pro');
define('TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', 'YOUR_TELEGRAM_BOT_TOKEN'));
define('TELEGRAM_CHAT_ID', env('TELEGRAM_CHAT_ID', 'YOUR_TELEGRAM_CHAT_ID'));

// Конфігурація підключення до БД сайту
$host = env('DB_HOST', 'localhost');
$dbname_site = env('DB_NAME_SITE', 'sthostsitedb');
$db_userconnect_site = env('DB_USER_SITE', 'sthostdb');
$db_passwd_site = env('DB_PASSWORD_SITE', '');

// Конфігурація підключення до БД WHMCS
$dbname_whmcs = env('DB_NAME_WHMCS', 'whmcs_sthost');
$db_userconnect_whmcs = env('DB_USER_WHMCS', 'whmcs_sthost');
$db_passwd_whmcs = env('DB_PASSWORD_WHMCS', '');

// Старая конфигурация FOSSBilling (DEPRECATED - больше не используется)
// $dbname_fossbill = 'admin_fossbill';
// $db_userconnect_fossbill = 'admin_fossbill';
// $db_passwd_fossbill = '0607Dm$157';

// Настройки безопасности
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600); // 1 час
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 минут

// Функция генерации CSRF токена
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Функция проверки CSRF токена
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Функция очистки ввода
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function t($key, $default = '') {
    global $translations;
    return $translations[$key] ?? $default;
}

// Функция валидации email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Функция защиты от XSS
function escapeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Языковые настройки
define('DEFAULT_LANG', 'ua');
$available_languages = ['ua', 'en', 'ru'];

// API настройки
define('WHOIS_API_KEY', env('WHOIS_API_KEY', 'YOUR_WHOIS_API_KEY'));
define('SITE_CHECK_API_KEY', env('SITE_CHECK_API_KEY', 'YOUR_SITE_CHECK_API_KEY'));

// Платежные системы
define('LIQPAY_PUBLIC_KEY', env('LIQPAY_PUBLIC_KEY', 'YOUR_LIQPAY_PUBLIC_KEY'));
define('LIQPAY_PRIVATE_KEY', env('LIQPAY_PRIVATE_KEY', 'YOUR_LIQPAY_PRIVATE_KEY'));

// ispmanager настройки
define('ISPMANAGER_URL', env('ISPMANAGER_URL', 'https://cp.sthost.pro'));
define('ISPMANAGER_USER', env('ISPMANAGER_USER', 'root'));
define('ISPMANAGER_PASS', env('ISPMANAGER_PASS', ''));

// Proxmox VE 9 настройки
define('PROXMOX_HOST', env('PROXMOX_HOST', '192.168.0.4')); // IP или hostname вашего Proxmox сервера
define('PROXMOX_PORT', env('PROXMOX_PORT', 8006)); // Порт Proxmox API (по умолчанию 8006)
define('PROXMOX_USER', env('PROXMOX_USER', 'root')); // Пользователь Proxmox
define('PROXMOX_REALM', env('PROXMOX_REALM', 'pam')); // Realm: pam или pve
define('PROXMOX_PASSWORD', env('PROXMOX_PASSWORD', '')); // !!!ВАЖНО: Заполните пароль или используйте API токены!!!
define('PROXMOX_NODE', env('PROXMOX_NODE', 'pve')); // Имя ноды Proxmox (узнайте через: pvesh get /nodes)

// Proxmox API Token (РЕКОМЕНДУЕТСЯ вместо пароля!)
// Создайте токен: Datacenter > Permissions > API Tokens
define('PROXMOX_TOKEN_ID', env('PROXMOX_TOKEN_ID', '')); // Например: 'root@pam!mytoken'
define('PROXMOX_TOKEN_SECRET', env('PROXMOX_TOKEN_SECRET', '')); // Секретный ключ токена
define('PROXMOX_VERIFY_SSL', env('PROXMOX_VERIFY_SSL', false)); // Проверка SSL сертификата (false для самоподписанных)

// VPS настройки для Proxmox
define('PROXMOX_STORAGE', env('PROXMOX_STORAGE', 'local-lvm')); // Хранилище для дисков VPS
define('PROXMOX_BRIDGE', env('PROXMOX_BRIDGE', 'vmbr0')); // Сетевой мост

// WHMCS настройки
define('WHMCS_URL', env('WHMCS_URL', 'https://bil.sthost.pro')); // URL вашего WHMCS (измените на актуальный!)
define('WHMCS_API_URL', env('WHMCS_API_URL', 'https://bill.sthost.pro/includes/api.php')); // URL API WHMCS
define('WHMCS_API_IDENTIFIER', env('WHMCS_API_IDENTIFIER', '')); // API Identifier из WHMCS Admin
define('WHMCS_API_SECRET', env('WHMCS_API_SECRET', '')); // API Secret из WHMCS Admin

// ВАЖНО: Создайте API Credentials в WHMCS:
// 1. Зайдите в WHMCS Admin Panel
// 2. Setup > Staff Management > Manage API Credentials
// 3. Создайте новые credentials с Admin правами
// 4. Скопируйте Identifier и Secret сюда
// 5. Добавьте IP вашего сервера в Allowed IP Addresses

// Старые настройки FOSSBilling (DEPRECATED - больше не используются)
// define('FOSSBILLING_URL', 'https://bill.sthost.pro');
// define('FOSSBILLING_API_TOKEN', 'YPo9tN0V8Ih0pdHDAKJfBuyNA08CnaHN');

// Настройки почты
define('SMTP_HOST', env('SMTP_HOST', 'mail.sthost.pro'));
define('SMTP_PORT', env('SMTP_PORT', 587));
define('SMTP_USERNAME', env('SMTP_USERNAME', 'support@sthost.pro'));
define('SMTP_PASSWORD', env('SMTP_PASSWORD', ''));

// Настройки капчи
define('RECAPTCHA_SITE_KEY', env('RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY'));
define('RECAPTCHA_SECRET_KEY', env('RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY'));

// Временная зона
date_default_timezone_set('Europe/Kyiv');

// Настройки сессий с защитой
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

// Запуск сессий
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Регенерация ID сессии для защиты от фиксации
//if (!isset($_SESSION['initiated'])) {
//    session_regenerate_id(true);
//    $_SESSION['initiated'] = true;
//}

// Установка языка по умолчанию
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

// Защита от DDoS - простая проверка частоты запросов
function checkRateLimit() {
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $current_time = time();
    
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    if (!isset($_SESSION['rate_limit'][$client_ip])) {
        $_SESSION['rate_limit'][$client_ip] = ['count' => 1, 'time' => $current_time];
        return true;
    }
    
    $rate_data = $_SESSION['rate_limit'][$client_ip];
    
    // Сброс счетчика каждую минуту
    if ($current_time - $rate_data['time'] > 60) {
        $_SESSION['rate_limit'][$client_ip] = ['count' => 1, 'time' => $current_time];
        return true;
    }
    
    // Максимум 100 запросов в минуту
    if ($rate_data['count'] > 100) {
        http_response_code(429);
        die('Too many requests');
    }
    
    $_SESSION['rate_limit'][$client_ip]['count']++;
    return true;
}

checkRateLimit();
?>