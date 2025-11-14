<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'register';
$page_title = 'Реєстрація доменів - StormHosting UA | Купити домен .ua, .com.ua, .kiev.ua';
$meta_description = 'Реєстрація доменів .ua, .com.ua, .kiev.ua, .pp.ua та інших. Найкращі ціни на домени в Україні. Миттєва активація, безкоштовне керування DNS.';
$meta_keywords = 'реєстрація доменів .ua, домен .com.ua, домен .kiev.ua, домен .pp.ua, дешеві домени україна, купити домен';

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// ========================================
// WHMCS INTEGRATION CONFIGURATION
// ========================================
$whmcs_config = [
    'billing_url' => 'https://bill.sthost.pro',

    // Прямий перехід до оформлення (true) або через кошик (false)
    'direct_checkout' => false,

    // Шаблон кошика WHMCS
    'cart_template' => 'standard'
];

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/domains-register-2.css'
];

$additional_js = [
    '/assets/js/domains-register-2.js'
];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Функції-заглушки якщо не визначені
if (!function_exists('t')) {
    function t($key, $def = '') { 
        $translations = [
            'domains_register' => 'Реєстрація доменів',
            'domain_search_button' => 'Перевірити',
            'site_name' => 'StormHosting UA'
        ];
        return $translations[$key] ?? $def ?: $key; 
    }
}

if (!function_exists('escapeOutput')) {
    function escapeOutput($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price) { return number_format($price, 0, ',', ' ') . ' грн'; }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken() { 
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token']; 
    }
}

if (!function_exists('validateCSRFToken')) {
    function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return trim(strip_tags($input));
    }
}

// Отримуємо доменні зони з БД
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sthostsitedb;charset=utf8mb4", "sthostdb", "3344Frz@q0607Dm$157");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Популярні домени (перші 8)
    $stmt = $pdo->prepare("
        SELECT dz.zone, dz.description, dz.price_registration, dz.price_renewal, dz.price_transfer,
               CASE
                   WHEN dz.zone LIKE '%.ua' THEN 'Український домен'
                   WHEN dz.zone IN ('.com', '.net', '.org') THEN 'Міжнародний домен'
                   ELSE 'Спеціальний домен'
               END as domain_type,
               CASE
                   WHEN dz.price_registration <= 150 THEN 'Економ'
                   WHEN dz.price_registration <= 250 THEN 'Стандарт'
                   ELSE 'Преміум'
               END as price_category
        FROM domain_zones dz
        WHERE dz.is_active = 1 AND dz.is_popular = 1
        ORDER BY dz.zone LIKE '%.ua' DESC, dz.price_registration ASC
        LIMIT 8
    ");
    $stmt->execute();
    $popular_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Всі активні зони для пошуку
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer,
               CASE WHEN zone LIKE '%.ua' THEN 1 ELSE 0 END as is_ua_domain
        FROM domain_zones
        WHERE is_active = 1
        ORDER BY is_ua_domain DESC, price_registration ASC
    ");
    $stmt->execute();
    $all_zones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Категоризовані домени - Українські (.ua)
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer
        FROM domain_zones
        WHERE is_active = 1 AND zone LIKE '%.ua'
        ORDER BY price_registration ASC, zone ASC
    ");
    $stmt->execute();
    $ukrainian_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Категоризовані домени - Міжнародні класичні (.com, .net, .org, etc)
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer
        FROM domain_zones
        WHERE is_active = 1 AND zone IN ('.com', '.net', '.org', '.info', '.biz', '.name', '.pro', '.eu')
        ORDER BY FIELD(zone, '.com', '.net', '.org', '.info', '.biz', '.name', '.pro', '.eu')
    ");
    $stmt->execute();
    $international_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Категоризовані домени - Бізнес і Комерція
    $business_zones = ['.shop', '.store', '.market', '.sale', '.deals', '.shopping', '.buy', '.trade', '.business', '.company', '.corporation', '.ltd', '.llc', '.inc', '.gmbh', '.ventures'];
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer
        FROM domain_zones
        WHERE is_active = 1 AND zone IN (" . implode(',', array_fill(0, count($business_zones), '?')) . ")
        ORDER BY price_registration ASC
    ");
    $stmt->execute($business_zones);
    $business_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Категоризовані домени - Технології та IT
    $tech_zones = ['.tech', '.io', '.dev', '.app', '.ai', '.cloud', '.digital', '.software', '.systems', '.technology', '.computer', '.network', '.online', '.site', '.web', '.website', '.codes', '.domains'];
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer
        FROM domain_zones
        WHERE is_active = 1 AND zone IN (" . implode(',', array_fill(0, count($tech_zones), '?')) . ")
        ORDER BY price_registration ASC
    ");
    $stmt->execute($tech_zones);
    $tech_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Категоризовані домени - Креатив та Медіа
    $creative_zones = ['.design', '.art', '.studio', '.photography', '.photo', '.media', '.video', '.film', '.music', '.gallery', '.graphics', '.agency', '.production', '.creative'];
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer
        FROM domain_zones
        WHERE is_active = 1 AND zone IN (" . implode(',', array_fill(0, count($creative_zones), '?')) . ")
        ORDER BY price_registration ASC
    ");
    $stmt->execute($creative_zones);
    $creative_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Категоризовані домени - Lifestyle та Спільноти
    $lifestyle_zones = ['.club', '.blog', '.community', '.social', '.life', '.style', '.fashion', '.beauty', '.fit', '.health', '.wellness'];
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer
        FROM domain_zones
        WHERE is_active = 1 AND zone IN (" . implode(',', array_fill(0, count($lifestyle_zones), '?')) . ")
        ORDER BY price_registration ASC
    ");
    $stmt->execute($lifestyle_zones);
    $lifestyle_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Категоризовані домени - Освіта та Наука
    $education_zones = ['.education', '.academy', '.school', '.university', '.college', '.institute', '.training', '.courses'];
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer
        FROM domain_zones
        WHERE is_active = 1 AND zone IN (" . implode(',', array_fill(0, count($education_zones), '?')) . ")
        ORDER BY price_registration ASC
    ");
    $stmt->execute($education_zones);
    $education_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Категоризовані домени - Туризм та Подорожі
    $tourism_zones = ['.travel', '.tours', '.voyage', '.vacations', '.holiday', '.hotel', '.booking'];
    $stmt = $pdo->prepare("
        SELECT zone, description, price_registration, price_renewal, price_transfer
        FROM domain_zones
        WHERE is_active = 1 AND zone IN (" . implode(',', array_fill(0, count($tourism_zones), '?')) . ")
        ORDER BY price_registration ASC
    ");
    $stmt->execute($tourism_zones);
    $tourism_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Статистика по доменам
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_zones,
            COUNT(CASE WHEN zone LIKE '%.ua' THEN 1 END) as ua_zones,
            MIN(price_registration) as min_price,
            MAX(price_registration) as max_price
        FROM domain_zones WHERE is_active = 1
    ");
    $stmt->execute();
    $domain_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Fallback дані у випадку помилки БД
    $popular_domains = [
        ['zone' => '.ua', 'price_registration' => 200, 'price_renewal' => 200, 'price_transfer' => 180, 'domain_type' => 'Український домен', 'price_category' => 'Стандарт'],
        ['zone' => '.com.ua', 'price_registration' => 150, 'price_renewal' => 160, 'price_transfer' => 130, 'domain_type' => 'Український домен', 'price_category' => 'Економ'],
        ['zone' => '.pp.ua', 'price_registration' => 160, 'price_renewal' => 160, 'price_transfer' => 160, 'domain_type' => 'Український домен', 'price_category' => 'Економ'],
        ['zone' => '.kiev.ua', 'price_registration' => 180, 'price_renewal' => 180, 'price_transfer' => 160, 'domain_type' => 'Український домен', 'price_category' => 'Стандарт'],
        ['zone' => '.net.ua', 'price_registration' => 180, 'price_renewal' => 180, 'price_transfer' => 160, 'domain_type' => 'Український домен', 'price_category' => 'Стандарт'],
        ['zone' => '.org.ua', 'price_registration' => 180, 'price_renewal' => 180, 'price_transfer' => 160, 'domain_type' => 'Український домен', 'price_category' => 'Стандарт'],
        ['zone' => '.com', 'price_registration' => 350, 'price_renewal' => 400, 'price_transfer' => 350, 'domain_type' => 'Міжнародний домен', 'price_category' => 'Преміум'],
        ['zone' => '.net', 'price_registration' => 450, 'price_renewal' => 500, 'price_transfer' => 450, 'domain_type' => 'Міжнародний домен', 'price_category' => 'Преміум']
    ];
    
    $all_zones = $popular_domains;
    $domain_stats = ['total_zones' => count($popular_domains), 'ua_zones' => 6, 'min_price' => 120, 'max_price' => 450];
}

// Обробка AJAX запитів для перевірки доменів
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_POST['action'] === 'check_domain') {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['error' => 'Недійсний токен безпеки']);
            exit;
        }
        
        $domain = sanitizeInput($_POST['domain'] ?? '');
        $zone = sanitizeInput($_POST['zone'] ?? '');
        
        if (empty($domain)) {
            echo json_encode(['error' => 'Введіть ім\'я домену']);
            exit;
        }
        
        // Перевірка формату домену
        if (!preg_match('/^[a-zA-Z0-9-]+$/', $domain) || strlen($domain) < 2 || strlen($domain) > 63) {
            echo json_encode(['error' => 'Недопустимі символи в імені домену або неправильна довжина (2-63 символи)']);
            exit;
        }
        
        // Перевірка що домен не починається та не закінчується дефісом
        if (strpos($domain, '-') === 0 || strrpos($domain, '-') === strlen($domain) - 1) {
            echo json_encode(['error' => 'Домен не може починатися або закінчуватися дефісом']);
            exit;
        }
        
        $full_domain = $domain . $zone;
        
        // Тут буде реальна перевірка через WHOIS API
        // Поки що робимо псевдо-випадкову перевірку
        $hash = crc32($full_domain);
        $is_available = ($hash % 4) !== 0; // ~75% доменів доступні
        
        // Отримуємо ціну для зони
        $zone_info = null;
        foreach ($all_zones as $z) {
            if ($z['zone'] === $zone) {
                $zone_info = $z;
                break;
            }
        }
        
        if (!$zone_info) {
            echo json_encode(['error' => 'Доменна зона не підтримується']);
            exit;
        }
        
        echo json_encode([
            'domain' => $full_domain,
            'available' => $is_available,
            'price' => $zone_info['price_registration'],
            'renewal_price' => $zone_info['price_renewal'],
            'currency' => 'грн',
            'message' => $is_available ? 'Домен доступний для реєстрації!' : 'Домен уже зареєстрований',
            'zone_info' => $zone_info
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'bulk_check') {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['error' => 'Недійсний токен безпеки']);
            exit;
        }
        
        $domain = sanitizeInput($_POST['domain'] ?? '');
        $zones = $_POST['zones'] ?? [];
        
        if (empty($domain)) {
            echo json_encode(['error' => 'Введіть ім\'я домену']);
            exit;
        }
        
        $results = [];
        foreach ($zones as $zone) {
            $zone = sanitizeInput($zone);
            $full_domain = $domain . $zone;
            $hash = crc32($full_domain);
            $is_available = ($hash % 4) !== 0;
            
            $zone_info = null;
            foreach ($all_zones as $z) {
                if ($z['zone'] === $zone) {
                    $zone_info = $z;
                    break;
                }
            }
            
            if ($zone_info) {
                $results[] = [
                    'domain' => $full_domain,
                    'zone' => $zone,
                    'available' => $is_available,
                    'price' => $zone_info['price_registration'],
                    'renewal_price' => $zone_info['price_renewal']
                ];
            }
        }
        
        echo json_encode(['results' => $results]);
        exit;
    }
}

// Підключення header
//include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/pages/domains-register-2.css">
<!-- Domain Search Hero -->
<section class="domain-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div class="hero-badge mb-4">
                    <i class="bi bi-globe"></i>
                    <span>Реєстрація доменів в Україні</span>
                </div>
                
                <h1 class="hero-title mb-4">Знайдіть ідеальний домен для вашого проекту</h1>
                <p class="hero-subtitle mb-5">
                    Підтримуємо всі популярні українські та міжнародні доменні зони. 
                    Миттєва активація, безкоштовне керування DNS та професійна підтримка 24/7.
                </p>
                
                <!-- Domain Search Form -->
                <div class="domain-search-wrapper">
                    <form id="domainSearchForm" class="domain-search-form">
                        <input type="hidden" id="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="search-input-group">
                            <div class="input-wrapper">
                                <i class="bi bi-globe input-icon"></i>
                                <input type="text" 
                                       id="domainName" 
                                       class="domain-input" 
                                       placeholder="назва-вашого-сайту"
                                       autocomplete="off"
                                       maxlength="63"
                                       required>
                            </div>
                            
                            <div class="zone-selector">
                                <select id="domainZone" class="zone-select">
                                    <?php foreach ($popular_domains as $domain): ?>
                                    <option value="<?php echo escapeOutput($domain['zone']); ?>" 
                                            data-price="<?php echo $domain['price_registration']; ?>"
                                            data-renewal="<?php echo $domain['price_renewal']; ?>">
                                        <?php echo escapeOutput($domain['zone']); ?> 
                                        (<?php echo formatPrice($domain['price_registration']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="search-btn">
                                <i class="bi bi-search"></i>
                                <span>Перевірити</span>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Search Results -->
                    <div id="searchResults" class="search-results"></div>
                    
                    <!-- Bulk Search -->
                    <div class="bulk-search-toggle">
                        <button type="button" id="toggleBulkSearch" class="btn-link">
                            <i class="bi bi-list-check"></i>
                            Перевірити у всіх популярних зонах
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Background Elements -->
    <div class="hero-bg-elements">
        <div class="floating-element element-1">
            <i class="bi bi-globe"></i>
        </div>
        <div class="floating-element element-2">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="floating-element element-3">
            <i class="bi bi-lightning"></i>
        </div>
    </div>
</section>

<!-- Domain Statistics -->
<section class="domain-stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-collection"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $domain_stats['total_zones']; ?>+</div>
                    <div class="stat-label">Доменних зон</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-flag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $domain_stats['ua_zones']; ?></div>
                    <div class="stat-label">Українських зон</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">від <?php echo formatPrice($domain_stats['min_price']); ?></div>
                    <div class="stat-label">Мінімальна ціна</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-headphones"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Підтримка</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Domains -->
<section class="popular-domains">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Популярні доменні зони</h2>
            <p class="section-subtitle">Оберіть найкращий домен для вашого проекту з найвигіднішими цінами</p>
        </div>
        
        <div class="domains-grid">
            <?php foreach ($popular_domains as $index => $domain): ?>
            <div class="domain-card" data-zone="<?php echo escapeOutput($domain['zone']); ?>" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <div class="domain-card-header">
                    <div class="domain-zone"><?php echo escapeOutput($domain['zone']); ?></div>
                    <div class="domain-type"><?php echo escapeOutput($domain['domain_type']); ?></div>
                </div>
                
                <div class="domain-card-body">
                    <div class="price-section">
                        <div class="main-price">
                            <span class="price-amount"><?php echo formatPrice($domain['price_registration']); ?></span>
                            <span class="price-period">/ рік</span>
                        </div>
                        <div class="renewal-price">
                            Продовження: <?php echo formatPrice($domain['price_renewal']); ?>
                        </div>
                    </div>
                    
                    <div class="price-badge badge-<?php echo strtolower($domain['price_category']); ?>">
                        <?php echo escapeOutput($domain['price_category']); ?>
                    </div>
                    
                    <ul class="domain-features">
                        <li><i class="bi bi-check"></i> Безкоштовне керування DNS</li>
                        <li><i class="bi bi-check"></i> Захист конфіденційності WHOIS</li>
                        <li><i class="bi bi-check"></i> Автопродовження</li>
                        <li><i class="bi bi-check"></i> Підтримка 24/7</li>
                    </ul>
                </div>
                
                <div class="domain-card-footer">
                    <button class="btn-check-domain" data-zone="<?php echo escapeOutput($domain['zone']); ?>">
                        <i class="bi bi-search"></i>
                        Перевірити доступність
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- All Domain Zones by Category -->
<section class="all-domains-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Всі доменні зони</h2>
            <p class="section-subtitle">Оберіть одну або декілька доменних зон для перевірки доступності</p>
        </div>

        <!-- Shopping Cart Summary (Sticky) -->
        <div id="domainCart" class="domain-cart-summary" style="display: none;">
            <div class="cart-content">
                <div class="cart-info">
                    <i class="bi bi-cart3"></i>
                    <span id="cartCount">0</span> доменів обрано
                    <span class="cart-total">Загалом: <strong id="cartTotalPrice">0 грн</strong></span>
                </div>
                <div class="cart-actions">
                    <button type="button" class="btn-cart-clear" onclick="clearCart()">
                        <i class="bi bi-trash"></i> Очистити
                    </button>
                    <button type="button" class="btn-cart-order" onclick="orderSelectedDomains()">
                        <i class="bi bi-bag-check"></i> Замовити
                    </button>
                </div>
            </div>
        </div>

        <!-- Domain Categories Tabs -->
        <ul class="nav nav-tabs domain-category-tabs justify-content-center" id="domainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ukrainian-tab" data-bs-toggle="tab" data-bs-target="#ukrainian" type="button" role="tab">
                    <i class="bi bi-flag"></i> Українські (<?php echo count($ukrainian_domains); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="international-tab" data-bs-toggle="tab" data-bs-target="#international" type="button" role="tab">
                    <i class="bi bi-globe2"></i> Міжнародні (<?php echo count($international_domains); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="business-tab" data-bs-toggle="tab" data-bs-target="#business" type="button" role="tab">
                    <i class="bi bi-briefcase"></i> Бізнес (<?php echo count($business_domains); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tech-tab" data-bs-toggle="tab" data-bs-target="#tech" type="button" role="tab">
                    <i class="bi bi-cpu"></i> Технології (<?php echo count($tech_domains); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="creative-tab" data-bs-toggle="tab" data-bs-target="#creative" type="button" role="tab">
                    <i class="bi bi-palette"></i> Креатив (<?php echo count($creative_domains); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="lifestyle-tab" data-bs-toggle="tab" data-bs-target="#lifestyle" type="button" role="tab">
                    <i class="bi bi-heart"></i> Lifestyle (<?php echo count($lifestyle_domains); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="education-tab" data-bs-toggle="tab" data-bs-target="#education" type="button" role="tab">
                    <i class="bi bi-book"></i> Освіта (<?php echo count($education_domains); ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tourism-tab" data-bs-toggle="tab" data-bs-target="#tourism" type="button" role="tab">
                    <i class="bi bi-airplane"></i> Туризм (<?php echo count($tourism_domains); ?>)
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content domain-tab-content" id="domainTabsContent">
            <!-- Ukrainian Domains -->
            <div class="tab-pane fade show active" id="ukrainian" role="tabpanel">
                <div class="domains-list-grid">
                    <?php foreach ($ukrainian_domains as $domain): ?>
                    <div class="domain-list-item">
                        <label class="domain-checkbox-label">
                            <input type="checkbox"
                                   class="domain-checkbox"
                                   data-zone="<?php echo escapeOutput($domain['zone']); ?>"
                                   data-price="<?php echo $domain['price_registration']; ?>"
                                   data-renewal="<?php echo $domain['price_renewal']; ?>"
                                   onchange="updateCart()">
                            <div class="domain-info">
                                <span class="domain-zone-name"><?php echo escapeOutput($domain['zone']); ?></span>
                                <span class="domain-description"><?php echo escapeOutput($domain['description']); ?></span>
                            </div>
                            <div class="domain-pricing">
                                <span class="price-value"><?php echo formatPrice($domain['price_registration']); ?></span>
                                <span class="price-label">/ рік</span>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- International Domains -->
            <div class="tab-pane fade" id="international" role="tabpanel">
                <div class="domains-list-grid">
                    <?php foreach ($international_domains as $domain): ?>
                    <div class="domain-list-item">
                        <label class="domain-checkbox-label">
                            <input type="checkbox"
                                   class="domain-checkbox"
                                   data-zone="<?php echo escapeOutput($domain['zone']); ?>"
                                   data-price="<?php echo $domain['price_registration']; ?>"
                                   data-renewal="<?php echo $domain['price_renewal']; ?>"
                                   onchange="updateCart()">
                            <div class="domain-info">
                                <span class="domain-zone-name"><?php echo escapeOutput($domain['zone']); ?></span>
                                <span class="domain-description"><?php echo escapeOutput($domain['description']); ?></span>
                            </div>
                            <div class="domain-pricing">
                                <span class="price-value"><?php echo formatPrice($domain['price_registration']); ?></span>
                                <span class="price-label">/ рік</span>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Business Domains -->
            <div class="tab-pane fade" id="business" role="tabpanel">
                <div class="domains-list-grid">
                    <?php foreach ($business_domains as $domain): ?>
                    <div class="domain-list-item">
                        <label class="domain-checkbox-label">
                            <input type="checkbox"
                                   class="domain-checkbox"
                                   data-zone="<?php echo escapeOutput($domain['zone']); ?>"
                                   data-price="<?php echo $domain['price_registration']; ?>"
                                   data-renewal="<?php echo $domain['price_renewal']; ?>"
                                   onchange="updateCart()">
                            <div class="domain-info">
                                <span class="domain-zone-name"><?php echo escapeOutput($domain['zone']); ?></span>
                                <span class="domain-description"><?php echo escapeOutput($domain['description']); ?></span>
                            </div>
                            <div class="domain-pricing">
                                <span class="price-value"><?php echo formatPrice($domain['price_registration']); ?></span>
                                <span class="price-label">/ рік</span>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tech Domains -->
            <div class="tab-pane fade" id="tech" role="tabpanel">
                <div class="domains-list-grid">
                    <?php foreach ($tech_domains as $domain): ?>
                    <div class="domain-list-item">
                        <label class="domain-checkbox-label">
                            <input type="checkbox"
                                   class="domain-checkbox"
                                   data-zone="<?php echo escapeOutput($domain['zone']); ?>"
                                   data-price="<?php echo $domain['price_registration']; ?>"
                                   data-renewal="<?php echo $domain['price_renewal']; ?>"
                                   onchange="updateCart()">
                            <div class="domain-info">
                                <span class="domain-zone-name"><?php echo escapeOutput($domain['zone']); ?></span>
                                <span class="domain-description"><?php echo escapeOutput($domain['description']); ?></span>
                            </div>
                            <div class="domain-pricing">
                                <span class="price-value"><?php echo formatPrice($domain['price_registration']); ?></span>
                                <span class="price-label">/ рік</span>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Creative Domains -->
            <div class="tab-pane fade" id="creative" role="tabpanel">
                <div class="domains-list-grid">
                    <?php foreach ($creative_domains as $domain): ?>
                    <div class="domain-list-item">
                        <label class="domain-checkbox-label">
                            <input type="checkbox"
                                   class="domain-checkbox"
                                   data-zone="<?php echo escapeOutput($domain['zone']); ?>"
                                   data-price="<?php echo $domain['price_registration']; ?>"
                                   data-renewal="<?php echo $domain['price_renewal']; ?>"
                                   onchange="updateCart()">
                            <div class="domain-info">
                                <span class="domain-zone-name"><?php echo escapeOutput($domain['zone']); ?></span>
                                <span class="domain-description"><?php echo escapeOutput($domain['description']); ?></span>
                            </div>
                            <div class="domain-pricing">
                                <span class="price-value"><?php echo formatPrice($domain['price_registration']); ?></span>
                                <span class="price-label">/ рік</span>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Lifestyle Domains -->
            <div class="tab-pane fade" id="lifestyle" role="tabpanel">
                <div class="domains-list-grid">
                    <?php foreach ($lifestyle_domains as $domain): ?>
                    <div class="domain-list-item">
                        <label class="domain-checkbox-label">
                            <input type="checkbox"
                                   class="domain-checkbox"
                                   data-zone="<?php echo escapeOutput($domain['zone']); ?>"
                                   data-price="<?php echo $domain['price_registration']; ?>"
                                   data-renewal="<?php echo $domain['price_renewal']; ?>"
                                   onchange="updateCart()">
                            <div class="domain-info">
                                <span class="domain-zone-name"><?php echo escapeOutput($domain['zone']); ?></span>
                                <span class="domain-description"><?php echo escapeOutput($domain['description']); ?></span>
                            </div>
                            <div class="domain-pricing">
                                <span class="price-value"><?php echo formatPrice($domain['price_registration']); ?></span>
                                <span class="price-label">/ рік</span>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Education Domains -->
            <div class="tab-pane fade" id="education" role="tabpanel">
                <div class="domains-list-grid">
                    <?php foreach ($education_domains as $domain): ?>
                    <div class="domain-list-item">
                        <label class="domain-checkbox-label">
                            <input type="checkbox"
                                   class="domain-checkbox"
                                   data-zone="<?php echo escapeOutput($domain['zone']); ?>"
                                   data-price="<?php echo $domain['price_registration']; ?>"
                                   data-renewal="<?php echo $domain['price_renewal']; ?>"
                                   onchange="updateCart()">
                            <div class="domain-info">
                                <span class="domain-zone-name"><?php echo escapeOutput($domain['zone']); ?></span>
                                <span class="domain-description"><?php echo escapeOutput($domain['description']); ?></span>
                            </div>
                            <div class="domain-pricing">
                                <span class="price-value"><?php echo formatPrice($domain['price_registration']); ?></span>
                                <span class="price-label">/ рік</span>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tourism Domains -->
            <div class="tab-pane fade" id="tourism" role="tabpanel">
                <div class="domains-list-grid">
                    <?php foreach ($tourism_domains as $domain): ?>
                    <div class="domain-list-item">
                        <label class="domain-checkbox-label">
                            <input type="checkbox"
                                   class="domain-checkbox"
                                   data-zone="<?php echo escapeOutput($domain['zone']); ?>"
                                   data-price="<?php echo $domain['price_registration']; ?>"
                                   data-renewal="<?php echo $domain['price_renewal']; ?>"
                                   onchange="updateCart()">
                            <div class="domain-info">
                                <span class="domain-zone-name"><?php echo escapeOutput($domain['zone']); ?></span>
                                <span class="domain-description"><?php echo escapeOutput($domain['description']); ?></span>
                            </div>
                            <div class="domain-pricing">
                                <span class="price-value"><?php echo formatPrice($domain['price_registration']); ?></span>
                                <span class="price-label">/ рік</span>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Bulk Check Selected Domains -->
        <div class="text-center mt-5">
            <div class="bulk-check-wrapper">
                <input type="text"
                       id="bulkDomainName"
                       class="bulk-domain-input"
                       placeholder="Введіть назву домену (наприклад: mycompany)"
                       maxlength="63">
                <button type="button" class="btn-bulk-check" onclick="bulkCheckSelected()">
                    <i class="bi bi-search"></i>
                    Перевірити обрані зони
                </button>
            </div>
            <p class="bulk-help-text">Оберіть зони вище та введіть назву для масової перевірки доступності</p>
        </div>

        <!-- Bulk Check Results -->
        <div id="bulkCheckResults" class="bulk-check-results mt-4"></div>
    </div>
</section>

<!-- Domain Features -->
<section class="domain-features">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Переваги реєстрації доменів у нас</h2>
            <p class="section-subtitle">Ми пропонуємо найкращі умови для реєстрації та управління доменами</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up" data-aos-delay="0">
                <div class="feature-icon">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Миттєва активація</h4>
                    <p class="feature-description">Домен активується автоматично протягом декількох хвилин після оплати</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Захист приватності</h4>
                    <p class="feature-description">Безкоштовний захист персональних даних в WHOIS базі</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon">
                    <i class="bi bi-gear"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Повне керування</h4>
                    <p class="feature-description">Зручна панель управління доменом з усіма необхідними функціями</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Безкоштовне перенесення</h4>
                    <p class="feature-description">Перенесіть свій домен від іншого реєстратора абсолютно безкоштовно</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon">
                    <i class="bi bi-dns"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Керування DNS</h4>
                    <p class="feature-description">Повний контроль над DNS записами через зручний інтерфейс</p>
                </div>
            </div>
            
            <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-icon">
                    <i class="bi bi-headphones"></i>
                </div>
                <div class="feature-content">
                    <h4 class="feature-title">Підтримка 24/7</h4>
                    <p class="feature-description">Кваліфікована технічна підтримка доступна цілодобово</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Domain Transfer CTA -->
<section class="domain-transfer-cta">
    <div class="container">
        <div class="transfer-card">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="transfer-content">
                        <h3 class="transfer-title">Маєте домен у іншого реєстратора?</h3>
                        <p class="transfer-subtitle">Перенесіть його до нас безкоштовно та отримайте кращі умови обслуговування</p>
                        
                        <ul class="transfer-benefits">
                            <li><i class="bi bi-check-circle"></i> Безкоштовне перенесення</li>
                            <li><i class="bi bi-check-circle"></i> Продовження на 1 рік</li>
                            <li><i class="bi bi-check-circle"></i> Кращі ціни на продовження</li>
                            <li><i class="bi bi-check-circle"></i> Професійна підтримка</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-4 text-lg-end">
                    <a href="/pages/domains/transfer.php" class="btn btn-transfer">
                        <i class="bi bi-arrow-right-circle"></i>
                        Перенести домен
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="domain-faq">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Часті питання</h2>
            <p class="section-subtitle">Відповіді на найпоширеніші питання про реєстрацію доменів</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="domainFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Як довго займає реєстрація домену?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#domainFAQ">
                            <div class="accordion-body">
                                Реєстрація домену відбувається миттєво після підтвердження оплати. Зазвичай це займає від 5 до 15 хвилин. Для українських доменів (.ua, .com.ua) процес може зайняти до 24 годин через додаткові перевірки.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Чи можу я зареєструвати домен без хостингу?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#domainFAQ">
                            <div class="accordion-body">
                                Так, ви можете зареєструвати домен окремо від хостингу. Домен можна використовувати для електронної пошти, перенаправлення або підключити до хостингу пізніше.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Що включено в ціну домену?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#domainFAQ">
                            <div class="accordion-body">
                                У ціну включено: реєстрацію на 1 рік, безкоштовне керування DNS, захист приватності WHOIS, автопродовження (опціонально) та технічну підтримку 24/7.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Як змінити DNS сервери?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#domainFAQ">
                            <div class="accordion-body">
                                Змінити DNS сервери можна в панелі управління доменом. Зміни вступають в силу протягом 24-48 годин через особливості поширення DNS записів.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Конфігурація для JavaScript
window.domainConfig = {
    searchUrl: '?ajax=1',
    csrfToken: '<?php echo generateCSRFToken(); ?>',
    zones: <?php echo json_encode($all_zones); ?>,
    whmcs: {
        billingUrl: '<?php echo $whmcs_config['billing_url']; ?>',
        directCheckout: <?php echo $whmcs_config['direct_checkout'] ? 'true' : 'false'; ?>,
        cartTemplate: '<?php echo $whmcs_config['cart_template']; ?>'
    },
    translations: {
        searching: 'Перевіряємо доступність...',
        available: 'Домен доступний!',
        unavailable: 'Домен зайнятий',
        error: 'Помилка перевірки'
    }
};

// ========================================
// Shopping Cart Management
// ========================================
let selectedDomains = [];

function updateCart() {
    selectedDomains = [];
    let totalPrice = 0;

    // Gather all checked domains
    document.querySelectorAll('.domain-checkbox:checked').forEach(checkbox => {
        const zone = checkbox.dataset.zone;
        const price = parseFloat(checkbox.dataset.price);
        const renewal = parseFloat(checkbox.dataset.renewal);

        selectedDomains.push({
            zone: zone,
            price: price,
            renewal: renewal
        });

        totalPrice += price;
    });

    // Update cart UI
    const cartElement = document.getElementById('domainCart');
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotalPrice');

    if (selectedDomains.length > 0) {
        cartElement.style.display = 'block';
        cartCount.textContent = selectedDomains.length;
        cartTotal.textContent = formatPrice(totalPrice);
    } else {
        cartElement.style.display = 'none';
    }
}

function clearCart() {
    document.querySelectorAll('.domain-checkbox:checked').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateCart();
}

function formatPrice(price) {
    return new Intl.NumberFormat('uk-UA', {
        style: 'currency',
        currency: 'UAH',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price).replace('UAH', 'грн');
}

// ========================================
// WHMCS Domain Ordering
// ========================================
function orderSelectedDomains() {
    if (selectedDomains.length === 0) {
        alert('⚠️ Будь ласка, оберіть хоча б одну доменну зону');
        return;
    }

    const domainName = prompt('Введіть назву домену для реєстрації (без зони, наприклад: mycompany):');

    if (!domainName || domainName.trim() === '') {
        return;
    }

    // Validate domain name
    const cleanDomain = domainName.trim().toLowerCase();
    if (!validateDomainName(cleanDomain)) {
        alert('⚠️ Недопустимі символи в назві домену. Використовуйте лише літери, цифри та дефіси.');
        return;
    }

    // Build WHMCS cart URL with all selected domains
    const billingUrl = window.domainConfig.whmcs.billingUrl;
    let cartUrl = `${billingUrl}/cart.php?a=add&domain=register`;

    // Add all selected domains to cart
    selectedDomains.forEach((domain, index) => {
        const fullDomain = cleanDomain + domain.zone;
        cartUrl += `&query=${encodeURIComponent(fullDomain)}`;
    });

    // Redirect to WHMCS
    window.open(cartUrl, '_blank');
}

function validateDomainName(domain) {
    // Allow letters, numbers, and hyphens
    // No hyphens at start or end
    const pattern = /^[a-z0-9][a-z0-9-]*[a-z0-9]$|^[a-z0-9]$/;
    return pattern.test(domain) && domain.length >= 1 && domain.length <= 63;
}

// ========================================
// Bulk Domain Checking
// ========================================
function bulkCheckSelected() {
    const domainName = document.getElementById('bulkDomainName').value.trim().toLowerCase();

    if (!domainName) {
        alert('⚠️ Введіть назву домену');
        document.getElementById('bulkDomainName').focus();
        return;
    }

    if (!validateDomainName(domainName)) {
        alert('⚠️ Недопустимі символи в назві домену. Використовуйте лише літери, цифри та дефіси.');
        document.getElementById('bulkDomainName').focus();
        return;
    }

    if (selectedDomains.length === 0) {
        alert('⚠️ Оберіть хоча б одну доменну зону для перевірки');
        return;
    }

    // Show loading
    const resultsDiv = document.getElementById('bulkCheckResults');
    resultsDiv.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Завантаження...</span>
            </div>
            <p class="mt-3">Перевіряємо доступність ${selectedDomains.length} доменів...</p>
        </div>
    `;

    // Collect selected zones
    const zones = selectedDomains.map(d => d.zone);

    // Make AJAX request
    fetch(window.domainConfig.searchUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'bulk_check',
            domain: domainName,
            zones: zones,
            csrf_token: window.domainConfig.csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            resultsDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            return;
        }

        displayBulkResults(data.results, domainName);
    })
    .catch(error => {
        resultsDiv.innerHTML = `<div class="alert alert-danger">Помилка перевірки доменів</div>`;
        console.error('Error:', error);
    });
}

function displayBulkResults(results, domainName) {
    const resultsDiv = document.getElementById('bulkCheckResults');

    if (!results || results.length === 0) {
        resultsDiv.innerHTML = '<div class="alert alert-warning">Немає результатів</div>';
        return;
    }

    let html = '<div class="bulk-results-grid">';

    results.forEach(result => {
        const statusClass = result.available ? 'available' : 'unavailable';
        const statusIcon = result.available ? 'check-circle' : 'x-circle';
        const statusText = result.available ? 'Доступний' : 'Зайнятий';
        const actionButton = result.available ?
            `<button class="btn-order-domain" onclick="orderSingleDomain('${result.domain}')">
                <i class="bi bi-cart-plus"></i> Замовити
            </button>` :
            '<span class="text-muted">Недоступний</span>';

        html += `
            <div class="bulk-result-item ${statusClass}">
                <div class="result-domain">
                    <i class="bi bi-${statusIcon}"></i>
                    <strong>${escapeHtml(result.domain)}</strong>
                </div>
                <div class="result-status">
                    <span class="status-badge">${statusText}</span>
                </div>
                <div class="result-price">
                    ${formatPrice(result.price)} / рік
                </div>
                <div class="result-action">
                    ${actionButton}
                </div>
            </div>
        `;
    });

    html += '</div>';
    resultsDiv.innerHTML = html;

    // Scroll to results
    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function orderSingleDomain(fullDomain) {
    const billingUrl = window.domainConfig.whmcs.billingUrl;
    const cartUrl = `${billingUrl}/cart.php?a=add&domain=register&query=${encodeURIComponent(fullDomain)}`;
    window.open(cartUrl, '_blank');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========================================
// Initialize on page load
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Make cart sticky on scroll
    const cart = document.getElementById('domainCart');
    if (cart) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                cart.classList.add('sticky');
            } else {
                cart.classList.remove('sticky');
            }
        });
    }

    // Auto-check bulk domain name when typing in search
    const mainSearch = document.getElementById('domainName');
    const bulkInput = document.getElementById('bulkDomainName');
    if (mainSearch && bulkInput) {
        mainSearch.addEventListener('input', function() {
            if (this.value.trim()) {
                bulkInput.value = this.value.trim();
            }
        });
    }
});
</script>

<style>
/* ========================================
   Domain Categories Styling
   ======================================== */
.all-domains-section {
    padding: 80px 0;
    background: linear-gradient(to bottom, #f8f9ff 0%, #ffffff 100%);
}

.domain-category-tabs {
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.domain-category-tabs .nav-link {
    border: none;
    color: #666;
    padding: 15px 25px;
    font-weight: 600;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.domain-category-tabs .nav-link:hover {
    color: var(--premium-primary, #667eea);
    border-bottom-color: var(--premium-primary, #667eea);
}

.domain-category-tabs .nav-link.active {
    color: var(--premium-primary, #667eea);
    border-bottom-color: var(--premium-primary, #667eea);
    background: none;
}

.domain-category-tabs .nav-link i {
    margin-right: 8px;
}

/* Domains List Grid */
.domains-list-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 15px;
    margin-top: 30px;
}

.domain-list-item {
    background: white;
    border-radius: 12px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
}

.domain-list-item:hover {
    border-color: var(--premium-primary, #667eea);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.domain-checkbox-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    cursor: pointer;
    margin: 0;
}

.domain-checkbox-label input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin-right: 15px;
    cursor: pointer;
    flex-shrink: 0;
}

.domain-checkbox-label input[type="checkbox"]:checked {
    accent-color: var(--premium-primary, #667eea);
}

.domain-info {
    flex: 1;
    min-width: 0;
}

.domain-zone-name {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 4px;
}

.domain-description {
    display: block;
    font-size: 13px;
    color: #666;
}

.domain-pricing {
    text-align: right;
    flex-shrink: 0;
    margin-left: 15px;
}

.price-value {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: var(--premium-primary, #667eea);
}

.price-label {
    font-size: 12px;
    color: #999;
}

/* Shopping Cart Summary */
.domain-cart-summary {
    position: sticky;
    top: 100px;
    z-index: 100;
    margin-bottom: 30px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.cart-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 30px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.cart-info {
    display: flex;
    align-items: center;
    gap: 20px;
    font-size: 16px;
}

.cart-info i {
    font-size: 24px;
}

.cart-total {
    margin-left: 20px;
}

.cart-total strong {
    font-size: 24px;
}

.cart-actions {
    display: flex;
    gap: 10px;
}

.btn-cart-clear,
.btn-cart-order {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-cart-clear {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.btn-cart-clear:hover {
    background: rgba(255, 255, 255, 0.3);
}

.btn-cart-order {
    background: white;
    color: var(--premium-primary, #667eea);
}

.btn-cart-order:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Bulk Check Section */
.bulk-check-wrapper {
    display: flex;
    gap: 15px;
    justify-content: center;
    max-width: 600px;
    margin: 0 auto;
}

.bulk-domain-input {
    flex: 1;
    padding: 15px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.bulk-domain-input:focus {
    outline: none;
    border-color: var(--premium-primary, #667eea);
}

.btn-bulk-check {
    padding: 15px 30px;
    background: var(--premium-gradient-main, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-bulk-check:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
}

.bulk-help-text {
    margin-top: 15px;
    color: #666;
    font-size: 14px;
}

/* Bulk Results */
.bulk-results-grid {
    display: grid;
    gap: 15px;
    max-width: 900px;
    margin: 0 auto;
}

.bulk-result-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    align-items: center;
    gap: 20px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s ease;
}

.bulk-result-item.available {
    border-color: #28a745;
    background: linear-gradient(to right, rgba(40, 167, 69, 0.05), white);
}

.bulk-result-item.unavailable {
    border-color: #dc3545;
    background: linear-gradient(to right, rgba(220, 53, 69, 0.05), white);
}

.result-domain {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
}

.result-domain i {
    font-size: 24px;
}

.bulk-result-item.available .result-domain i {
    color: #28a745;
}

.bulk-result-item.unavailable .result-domain i {
    color: #dc3545;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.bulk-result-item.available .status-badge {
    background: #28a745;
    color: white;
}

.bulk-result-item.unavailable .status-badge {
    background: #dc3545;
    color: white;
}

.result-price {
    font-weight: 700;
    color: var(--premium-primary, #667eea);
}

.btn-order-domain {
    padding: 8px 16px;
    background: var(--premium-gradient-main, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-order-domain:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .domains-list-grid {
        grid-template-columns: 1fr;
    }

    .domain-category-tabs .nav-link {
        font-size: 12px;
        padding: 10px 12px;
    }

    .cart-content {
        flex-direction: column;
        gap: 15px;
    }

    .cart-info {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .bulk-check-wrapper {
        flex-direction: column;
    }

    .bulk-result-item {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .result-domain {
        justify-content: center;
    }
}
</style>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>