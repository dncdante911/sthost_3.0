<?php
define('SECURE_ACCESS', true);

// Захист від прямого доступу
if (!defined('SECURE_ACCESS')) {
    die('Прямий доступ заборонено');
}

// Захист від XSS
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// CSRF токен
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Мета-дані сторінки
$page_title = "Контакти - StormHosting UA | Зв'яжіться з нами";
$page_description = "Контактна інформація StormHosting UA: телефони, адреса офісу в Дніпрі, email, форма зворотного зв'язку. Працюємо 24/7 для вашої зручності.";
$page_keywords = "контакти, телефон, адреса, офіс дніпро, зворотний зв'язок, підтримка";
$canonical_url = "https://sthost.pro/pages/contacts";

// Додаткові CSS та JS файли для цієї сторінки
//$additional_css = [
//    '/assets/css/pages/contacts.css?v=' . filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/pages/contacts.css')
//];
//
//$additional_js = [
//    '/assets/js/contacts.js'
//];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Контактна інформація
$contact_info = [
    'company' => [
        'name' => 'StormHosting UA',
        'legal_name' => 'ФОП Кошевой Максим Ігоревич',
        'address' => 'пл. Академіка Стародубова 1, 203',
        'city' => 'Дніпро',
        'postal_code' => '49000',
        'country' => 'Україна'
    ],
    'phones' => [
        'main' => '+380 (99) 623-96-37',
        'support' => '+380 (93) 025-39-41',
        'sales' => '+380 (97) 714-19-80'
    ],
    'emails' => [
        'main' => 'info@sthost.pro',
        'support' => 'support@sthost.pro',
        'sales' => 'sales@sthost.pro',
        'billing' => 'billing@sthost.pro'
    ],
    'schedule' => [
        'weekdays' => 'Пн-Пт: 09:00 - 18:00',
        'weekend' => 'Сб-Нд: 10:00 - 16:00',
        'support' => '24/7 онлайн підтримка'
    ],
    'social' => [
        'telegram' => 'https://t.me/stormhosting_ua',
        'viber' => 'viber://chat?number=380671234567',
        'facebook' => 'https://facebook.com/stormhosting.ua',
        'instagram' => 'https://instagram.com/stormhosting_ua'
    ]
];

// Статус серверів - отримуємо з системи мониторинга
$server_status = [];

try {
    // Визначаємо шлях до файлу мониторинга відносно поточного файлу
    $monitor_file = dirname(__DIR__) . '/includes/monitoring/ServerMonitor.php';

    if (!file_exists($monitor_file)) {
        throw new Exception("ServerMonitor.php not found at: $monitor_file");
    }

    require_once $monitor_file;

    $monitor = new ServerMonitor();
    $monitoring_data = $monitor->getSimpleStatus();

    // Конвертуємо дані мониторинга в формат для відображення
    if (isset($monitoring_data['servers']) && is_array($monitoring_data['servers'])) {
        foreach ($monitoring_data['servers'] as $server) {
            $status = 'offline';
            if (isset($server['online']) && $server['online']) {
                $status = (isset($server['status']) && $server['status'] === 'online') ? 'online' : 'maintenance';
            }

            // Визначаємо метрики залежно від типу сервера
            $response_time = 'N/A';
            $load = 'N/A';
            $uptime = 'N/A';
            $server_type = $server['type'] ?? 'Unknown';

            if ($server_type === 'ISPManager' || $server_type === 'Proxmox') {
                $load = isset($server['cpu']) ? round($server['cpu']) . '%' : 'N/A';
                $uptime = isset($server['uptime']) ? $server['uptime'] . '%' : 'N/A';
                $response_time = '<5ms';
            } elseif ($server_type === 'HAProxy') {
                $load = isset($server['sessions']) ? $server['sessions'] . ' sess' : 'N/A';
                $uptime = isset($server['backends_up'], $server['backends_total'])
                    ? $server['backends_up'] . '/' . $server['backends_total'] . ' BE'
                    : 'N/A';
                $response_time = '<2ms';
            } elseif ($server_type === 'Network') {
                $load = isset($server['usage']) ? round($server['usage']) . '%' : 'N/A';
                $uptime = isset($server['rx_rate']) ? $server['rx_rate'] : 'N/A';
                $response_time = isset($server['tx_rate']) ? $server['tx_rate'] : 'N/A';
            } elseif ($server_type === 'System') {
                $load = 'HTTP';
                $uptime = 'Check';
                $response_time = isset($server['response_time_formatted']) ? $server['response_time_formatted'] : 'N/A';
            }

            $server_status[$server['id'] ?? 'unknown'] = [
                'name' => $server['name'] ?? 'Unknown Server',
                'status' => $status,
                'uptime' => $uptime,
                'response_time' => $response_time,
                'load' => $load,
                'type' => $server_type
            ];
        }
    }
} catch (Throwable $e) {
    // Ловимо всі помилки (Exception та Error в PHP 7+)
    error_log("Monitoring error on contacts page: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    $server_status = [
        'placeholder' => [
            'name' => 'Мониторинг налаштовується',
            'status' => 'maintenance',
            'uptime' => 'N/A',
            'response_time' => 'N/A',
            'load' => 'N/A'
        ]
    ];
}

// Якщо немає серверів - показуємо повідомлення
if (empty($server_status)) {
    $server_status = [
        'info' => [
            'name' => 'Система мониторинга',
            'status' => 'maintenance',
            'uptime' => 'Налаштування',
            'response_time' => 'N/A',
            'load' => 'N/A'
        ]
    ];
}

// Обробка форми зворотного зв'язку
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Захист від CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF токен невірний');
    }
    
    if (isset($_POST['submit_contact'])) {
        $contact_data = [
            'name' => clean_input($_POST['name'] ?? ''),
            'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'phone' => clean_input($_POST['phone'] ?? ''),
            'subject' => clean_input($_POST['subject'] ?? ''),
            'message' => clean_input($_POST['message'] ?? ''),
            'department' => clean_input($_POST['department'] ?? 'general')
        ];
        
        // Валідація
        if (empty($contact_data['name']) || !$contact_data['email'] || 
            empty($contact_data['subject']) || empty($contact_data['message'])) {
            $error_message = "Будь ласка, заповніть всі обов'язкові поля.";
        } else {
            try {
                // Збереження в БД
                $stmt = $conn->prepare("
                    INSERT INTO contact_messages (
                        name, email, phone, subject, message, department,
                        created_at, ip_address
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
                ");
                
                $stmt->execute([
                    $contact_data['name'],
                    $contact_data['email'],
                    $contact_data['phone'],
                    $contact_data['subject'],
                    $contact_data['message'],
                    $contact_data['department'],
                    $_SERVER['REMOTE_ADDR']
                ]);
                
                $message_id = $conn->lastInsertId();
                
                // Відправка email (опціонально)
                send_contact_notification($contact_data, $message_id);
                
                $success_message = "Дякуємо за звернення! Ми відповімо вам найближчим часом.";
                $_POST = []; // Очищуємо форму
                
            } catch (Exception $e) {
                error_log("Contact form error: " . $e->getMessage());
                $error_message = "Виникла помилка при відправці повідомлення. Спробуйте ще раз.";
            }
        }
    }
}

function send_contact_notification($data, $id) {
    // Логіка відправки email
}

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/pages/contacts.css">

<main class="main-content">
    <!-- Hero Section -->
    <section class="contacts-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="icon-map-pin"></i>
                    <span>Ми завжди на зв'язку</span>
                </div>
                
                <h1 class="hero-title">Контакти</h1>
                <p class="hero-subtitle">
                    Зв'яжіться з нами будь-яким зручним способом. Наша команда готова допомогти вам 
                    24 години на добу, 7 днів на тиждень.
                </p>
                
                <div class="hero-quick-contacts">
                    <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', $contact_info['phones']['main']); ?>" 
                       class="quick-contact-item">
                        <i class="icon-phone"></i>
                        <span><?php echo $contact_info['phones']['main']; ?></span>
                    </a>
                    <a href="mailto:<?php echo $contact_info['emails']['main']; ?>" 
                       class="quick-contact-item">
                        <i class="icon-mail"></i>
                        <span><?php echo $contact_info['emails']['main']; ?></span>
                    </a>
                    <div class="quick-contact-item">
                        <i class="icon-clock"></i>
                        <span>24/7 підтримка</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Methods -->
    <section class="contact-methods">
        <div class="container">
            <div class="methods-grid">
                <!-- Телефони -->
                <div class="method-card">
                    <div class="method-icon phone-icon">
                        <i class="icon-phone"></i>
                    </div>
                    <h3 class="method-title">Телефонний зв'язок</h3>
                    <div class="method-content">
                        <div class="contact-item">
                            <div class="contact-label">Головний номер</div>
                            <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', $contact_info['phones']['main']); ?>" 
                               class="contact-value"><?php echo $contact_info['phones']['main']; ?></a>
                        </div>
                        <div class="contact-item">
                            <div class="contact-label">Технічна підтримка</div>
                            <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', $contact_info['phones']['support']); ?>" 
                               class="contact-value"><?php echo $contact_info['phones']['support']; ?></a>
                        </div>
                        <div class="contact-item">
                            <div class="contact-label">Відділ продажів</div>
                            <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', $contact_info['phones']['sales']); ?>" 
                               class="contact-value"><?php echo $contact_info['phones']['sales']; ?></a>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="method-card">
                    <div class="method-icon email-icon">
                        <i class="icon-mail"></i>
                    </div>
                    <h3 class="method-title">Електронна пошта</h3>
                    <div class="method-content">
                        <div class="contact-item">
                            <div class="contact-label">Загальні питання</div>
                            <a href="mailto:<?php echo $contact_info['emails']['main']; ?>" 
                               class="contact-value"><?php echo $contact_info['emails']['main']; ?></a>
                        </div>
                        <div class="contact-item">
                            <div class="contact-label">Технічна підтримка</div>
                            <a href="mailto:<?php echo $contact_info['emails']['support']; ?>" 
                               class="contact-value"><?php echo $contact_info['emails']['support']; ?></a>
                        </div>
                        <div class="contact-item">
                            <div class="contact-label">Біллінг</div>
                            <a href="mailto:<?php echo $contact_info['emails']['billing']; ?>" 
                               class="contact-value"><?php echo $contact_info['emails']['billing']; ?></a>
                        </div>
                    </div>
                </div>

                <!-- Месенджери -->
                <div class="method-card">
                    <div class="method-icon messengers-icon">
                        <i class="icon-message-circle"></i>
                    </div>
                    <h3 class="method-title">Месенджери</h3>
                    <div class="method-content">
                        <div class="messenger-links">
                            <a href="<?php echo $contact_info['social']['telegram']; ?>" 
                               class="messenger-link telegram" target="_blank">
                                <i class="icon-telegram"></i>
                                <span>Telegram</span>
                            </a>
                            <a href="<?php echo $contact_info['social']['viber']; ?>" 
                               class="messenger-link viber">
                                <i class="icon-viber"></i>
                                <span>Viber</span>
                            </a>
                            <button class="messenger-link whatsapp" onclick="openWhatsApp()">
                                <i class="icon-whatsapp"></i>
                                <span>WhatsApp</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Адреса -->
                <div class="method-card">
                    <div class="method-icon address-icon">
                        <i class="icon-map-pin"></i>
                    </div>
                    <h3 class="method-title">Наша адреса</h3>
                    <div class="method-content">
                        <div class="address-info">
                            <div class="address-line">
                                <strong><?php echo $contact_info['company']['name']; ?></strong>
                            </div>
                            <div class="address-line">
                                <?php echo $contact_info['company']['address']; ?>
                            </div>
                            <div class="address-line">
                                <?php echo $contact_info['company']['city']; ?>, 
                                <?php echo $contact_info['company']['postal_code']; ?>
                            </div>
                            <div class="address-line">
                                <?php echo $contact_info['company']['country']; ?>
                            </div>
                        </div>
                        <button class="btn btn-outline btn-small" onclick="openMap()">
                            <i class="icon-navigation"></i>
                            Відкрити на карті
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form & Map -->
    <section class="contact-form-section">
        <div class="container">
            <div class="form-map-grid">
                <!-- Форма зворотного зв'язку -->
                <div class="contact-form-wrapper">
                    <div class="form-header">
                        <h2 class="form-title">Форма зворотного зв'язку</h2>
                        <p class="form-subtitle">
                            Залишіть повідомлення і ми зв'яжемося з вами найближчим часом
                        </p>
                    </div>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="icon-check-circle"></i>
                            <span><?php echo htmlspecialchars($success_message); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="icon-alert-circle"></i>
                            <span><?php echo htmlspecialchars($error_message); ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="contact-form" id="contactForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="submit_contact" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    Ваше ім'я <span class="required">*</span>
                                </label>
                                <input type="text" name="name" id="name" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                       placeholder="Введіть ваше ім'я" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    Email <span class="required">*</span>
                                </label>
                                <input type="email" name="email" id="email" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       placeholder="your@email.com" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" name="phone" id="phone" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       placeholder="+38 (XXX) XXX-XX-XX">
                            </div>
                            
                            <div class="form-group">
                                <label for="department" class="form-label">Відділ</label>
                                <select name="department" id="department" class="form-select">
                                    <option value="general" <?php echo (($_POST['department'] ?? '') === 'general') ? 'selected' : ''; ?>>
                                        Загальні питання
                                    </option>
                                    <option value="support" <?php echo (($_POST['department'] ?? '') === 'support') ? 'selected' : ''; ?>>
                                        Технічна підтримка
                                    </option>
                                    <option value="sales" <?php echo (($_POST['department'] ?? '') === 'sales') ? 'selected' : ''; ?>>
                                        Відділ продажів
                                    </option>
                                    <option value="billing" <?php echo (($_POST['department'] ?? '') === 'billing') ? 'selected' : ''; ?>>
                                        Біллінг
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject" class="form-label">
                                Тема повідомлення <span class="required">*</span>
                            </label>
                            <input type="text" name="subject" id="subject" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                                   placeholder="Коротко опишіть тему вашого звернення" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label">
                                Повідомлення <span class="required">*</span>
                            </label>
                            <textarea name="message" id="message" class="form-textarea" rows="6" 
                                      placeholder="Детально опишіть ваше питання або проблему..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-large">
                                <i class="icon-send"></i>
                                Відправити повідомлення
                            </button>
                        </div>
                        
                        <div class="form-note">
                            Натиснувши кнопку "Відправити", ви погоджуєтеся з 
                            <a href="/pages/info/legal" target="_blank">політикою конфіденційності</a>
                        </div>
                    </form>
                </div>

                <!-- Карта -->
                <div class="map-wrapper">
                    <div class="map-header">
                        <h3 class="map-title">Ми на карті</h3>
                        <p class="map-subtitle">Наш офіс у центрі Дніпра</p>
                    </div>
                    
                    <div class="map-container" id="mapContainer">
                        <div class="map-placeholder">
                            <div class="map-placeholder-content">
                                <i class="icon-map"></i>
                                <h4>Інтерактивна карта</h4>
                                <p>Клікніть для завантаження карти</p>
                                <button class="btn btn-primary" onclick="loadMap()">
                                    <i class="icon-navigation"></i>
                                    Завантажити карту
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="map-info">
                        <div class="map-info-item">
                            <i class="icon-navigation"></i>
                            <div>
                                <strong>Як дістатися:</strong><br>
                                м. Дніпро, пл. Академіка Стародубова 1<br>
                            </div>
                        </div>
                        <div class="map-info-item">
                            <i class="icon-car"></i>
                            <div>
                                <strong>Парковка:</strong><br>
                                Безкоштовна парковка для клієнтів<br>
                                Охоронювана територія
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Server Status -->
    <section class="server-status">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Статус серверів</h2>
                <p class="section-subtitle">
                    Моніторинг роботи наших серверів в реальному часі
                </p>
            </div>
            
            <div class="status-grid">
                <?php foreach ($server_status as $server_id => $server): ?>
                    <div class="status-card" data-server="<?php echo $server_id; ?>">
                        <div class="status-header">
                            <div class="status-info">
                                <h3 class="server-name"><?php echo $server['name']; ?></h3>
                                <div class="status-indicator status-<?php echo $server['status']; ?>">
                                    <span class="status-dot"></span>
                                    <span class="status-text">
                                        <?php 
                                        echo $server['status'] === 'online' ? 'Онлайн' : 
                                            ($server['status'] === 'maintenance' ? 'Обслуговування' : 'Офлайн');
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="status-uptime">
                                <div class="uptime-value"><?php echo $server['uptime']; ?></div>
                                <div class="uptime-label">Uptime</div>
                            </div>
                        </div>
                        
                        <div class="status-metrics">
                            <div class="metric">
                                <div class="metric-label">Відгук</div>
                                <div class="metric-value"><?php echo $server['response_time']; ?></div>
                            </div>
                            <div class="metric">
                                <div class="metric-label">Навантаження</div>
                                <div class="metric-value"><?php echo $server['load']; ?></div>
                            </div>
                        </div>
                        
                        <div class="status-actions">
                            <button class="btn btn-small btn-outline" onclick="refreshServerStatus('<?php echo $server_id; ?>')">
                                <i class="icon-refresh-cw"></i>
                                Оновити
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="status-legend">
                <div class="legend-item">
                    <span class="legend-dot status-online"></span>
                    <span>Сервер працює нормально</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot status-maintenance"></span>
                    <span>Планове обслуговування</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot status-offline"></span>
                    <span>Сервер недоступний</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Working Hours -->
    <section class="working-hours">
        <div class="container">
            <div class="hours-grid">
                <div class="hours-info">
                    <h2 class="hours-title">Графік роботи</h2>
                    <p class="hours-description">
                        Наш офіс працює за зручним графіком, а онлайн підтримка доступна цілодобово
                    </p>
                    
                    <div class="schedule-list">
                        <div class="schedule-item">
                            <div class="schedule-days">Понеділок - П'ятниця</div>
                            <div class="schedule-time">09:00 - 18:00</div>
                        </div>
                        <div class="schedule-item">
                            <div class="schedule-days">Субота - Неділя</div>
                            <div class="schedule-time">10:00 - 16:00</div>
                        </div>
                        <div class="schedule-item highlight">
                            <div class="schedule-days">Онлайн підтримка</div>
                            <div class="schedule-time">24/7</div>
                        </div>
                    </div>
                </div>
                
                <div class="current-time-widget">
                    <div class="widget-header">
                        <h3>Зараз у нас</h3>
                        <div class="timezone">UTC+2 (Київ)</div>
                    </div>
                    
                    <div class="time-display">
                        <div class="current-time" id="currentTime">
                            <span class="time"></span>
                            <span class="date"></span>
                        </div>
                        
                        <div class="office-status" id="officeStatus">
                            <span class="status-indicator"></span>
                            <span class="status-text"></span>
                        </div>
                    </div>
                    
                    <div class="quick-actions">
                        <button class="btn btn-primary btn-small" onclick="startLiveChat()">
                            <i class="icon-message-circle"></i>
                            Онлайн чат
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Скрипт автообновления статуса серверов -->
<script>
// Автообновление статуса серверов каждые 30 секунд
function refreshServerStatus(serverId = null) {
    fetch('/api/monitoring/status.php?action=all&format=simple')
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.data.servers) {
                console.warn('Failed to load server status');
                return;
            }

            data.data.servers.forEach(server => {
                const card = document.querySelector(`.status-card[data-server="${server.id}"]`);
                if (!card) return;

                // Обновляем статус
                const statusIndicator = card.querySelector('.status-indicator');
                const statusText = card.querySelector('.status-text');
                const uptimeValue = card.querySelector('.uptime-value');
                const metrics = card.querySelectorAll('.metric-value');

                const status = server.online && server.status === 'online' ? 'online' :
                              (server.online ? 'maintenance' : 'offline');

                if (statusIndicator) {
                    statusIndicator.className = `status-indicator status-${status}`;
                }

                if (statusText) {
                    const statusNames = {
                        'online': 'Онлайн',
                        'offline': 'Офлайн',
                        'maintenance': 'Обслуговування'
                    };
                    statusText.textContent = statusNames[status] || status;
                }

                // Обновляем метрики
                if (server.type === 'ISPManager' || server.type === 'Proxmox') {
                    if (uptimeValue) {
                        uptimeValue.textContent = server.uptime ? server.uptime + '%' : 'N/A';
                    }
                    if (metrics[0]) {
                        metrics[0].textContent = '<5ms';
                    }
                    if (metrics[1]) {
                        metrics[1].textContent = server.cpu ? Math.round(server.cpu) + '%' : 'N/A';
                    }
                } else if (server.type === 'HAProxy') {
                    if (uptimeValue) {
                        uptimeValue.textContent = server.backends_up && server.backends_total ?
                            `${server.backends_up}/${server.backends_total} BE` : 'N/A';
                    }
                    if (metrics[0]) {
                        metrics[0].textContent = '<2ms';
                    }
                    if (metrics[1]) {
                        metrics[1].textContent = server.sessions ? server.sessions + ' sess' : 'N/A';
                    }
                } else if (server.type === 'Network') {
                    if (uptimeValue) {
                        uptimeValue.textContent = server.rx_rate || 'N/A';
                    }
                    if (metrics[0]) {
                        metrics[0].textContent = server.tx_rate || 'N/A';
                    }
                    if (metrics[1]) {
                        metrics[1].textContent = server.usage ? Math.round(server.usage) + '%' : 'N/A';
                    }
                }
            });

            console.log('Server status updated at ' + new Date().toLocaleTimeString());
        })
        .catch(error => {
            console.error('Error refreshing server status:', error);
        });
}

// Запускаем автообновление при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Обновляем каждые 30 секунд
    setInterval(refreshServerStatus, 30000);

    // Кнопка ручного обновления
    window.refreshServerStatus = refreshServerStatus;
});
</script>

<script src="/assets/js/contacts.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>