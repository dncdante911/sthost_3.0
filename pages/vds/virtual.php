<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// ==========================================
// WHMCS INTEGRATION CONFIGURATION
// ==========================================
// ВАЖЛИВО: Налаштуйте Product IDs з вашого WHMCS
// Знайти їх можна: WHMCS Admin -> Setup -> Products/Services -> Products/Services
// Формат: 'internal_id' => WHMCS_PRODUCT_ID
//
// 📖 ПОВНА ДОКУМЕНТАЦІЯ:
// - WHMCS_VPS_INTEGRATION.md - настройка Product IDs
// - WHMCS_OS_SELECTION.md - настройка выбора операционных систем
//
// 🎯 ШО ЗРОБЛЕНО:
// ✅ Кнопки "Замовити зараз" ведуть в WHMCS корзину
// ✅ Вибір місячної/річної оплати
// ✅ Кликабельні карточки ОС з індикатором вибору
// ✅ Автоматична передача вибраної ОС в WHMCS (через configoption[1])
//
// 🔧 ШО НАЛАШТУВАТИ:
// 1. Пропишіть ваші Product IDs нижче (дивіться WHMCS_VPS_INTEGRATION.md)
// 2. Створіть Configurable Option для ОС в WHMCS (дивіться WHMCS_OS_SELECTION.md)
// 3. Якщо ID Configurable Option НЕ 1, змініть в строці 868

$whmcs_config = [
    'billing_url' => 'https://bill.sthost.pro', // URL вашого WHMCS біллінгу

    // Маппінг VPS планів на Product IDs з WHMCS
    'product_ids' => [
        1 => 1,  // VPS Start -> WHMCS Product ID (ЗМІНІТЬ НА ВАШІ!)
        2 => 2,  // VPS Basic -> WHMCS Product ID
        3 => 3,  // VPS Pro -> WHMCS Product ID
        4 => 4   // VPS Business -> WHMCS Product ID
    ],

    // Billing cycles для кожного плану (monthly, quarterly, semiannually, annually)
    'default_billing_cycle' => 'monthly',

    // Увімкнути direct checkout (перехід одразу до оформлення)
    'direct_checkout' => false // true = одразу checkout, false = спочатку cart
];

// ІНСТРУКЦІЯ ПО НАЛАШТУВАННЮ:
//
// 1. Створіть продукти VPS в WHMCS:
//    - Зайдіть в WHMCS Admin Panel
//    - Setup -> Products/Services -> Create a New Group (назвіть "VPS Servers")
//    - Setup -> Products/Services -> Create a New Product
//    - Заповніть назву, ціну, характеристики
//    - Збережіть і запам'ятайте Product ID (показується в URL або в списку)
//
// 2. Запишіть Product IDs в масив $whmcs_config['product_ids'] вище
//    Приклад: 1 => 15 означає що VPS Start (ID 1) = WHMCS Product ID 15
//
// 3. Встановіть правильний URL біллінгу в 'billing_url'
//
// 4. Збережіть файл
//
// ==========================================

// Конфігурація сторінки
$page = 'virtual';
$page_title = 'Виділені сервери - StormHosting UA';
$meta_description = 'Потужні виділені сервери в Україні. Intel Xeon, AMD EPYC процесори, до 1TB RAM, NVMe диски. Повний контроль над залізом.';
$meta_keywords = 'виділений сервер, dedicated server, фізичний сервер, bare metal, колокація';

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
//include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Функції-заглушки якщо не визначені
if (!function_exists('formatPrice')) {
    function formatPrice($price, $currency = 'грн') {
        return number_format($price, 0, ',', ' ') . ' ' . $currency;
    }
}

if (!function_exists('t')) {
    function t($key, $def = '') { 
        return $def ?: $key; 
    }
}

if (!function_exists('escapeOutput')) {
    function escapeOutput($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

// Конфігурація сторінки
$page = 'virtual';
$page_title = 'VPS/VDS сервери - StormHosting UA';
$meta_description = 'Віртуальні приватні сервери VPS/VDS в Україні. KVM віртуалізація, SSD диски, root доступ. Від 299 грн/міс.';
$meta_keywords = 'vps, vds, віртуальний сервер, kvm, ssd vps, root доступ';

// Додаткові CSS та JS файли
//$additional_css = [
//    '/assets/css/pages/vds-virtual.css'
//];
//
//$additional_js = [
//    '/assets/js/pages/vds-virtual.js'
//];

// Fallback дані для VPS планів
$vps_plans = [
    [
        'id' => 1,
        'name' => 'VPS Start',
        'subtitle' => 'Для початківців',
        'cpu_cores' => 1,
        'ram' => 1,
        'storage' => 20,
        'bandwidth' => 1000,
        'price_monthly' => 299,
        'price_yearly' => 2990,
        'is_popular' => 0,
        'ipv4_addresses' => 1
    ],
    [
        'id' => 2,
        'name' => 'VPS Basic',
        'subtitle' => 'Для малого бізнесу',
        'cpu_cores' => 2,
        'ram' => 2,
        'storage' => 40,
        'bandwidth' => 2000,
        'price_monthly' => 499,
        'price_yearly' => 4990,
        'is_popular' => 1,
        'ipv4_addresses' => 1
    ],
    [
        'id' => 3,
        'name' => 'VPS Pro',
        'subtitle' => 'Для проектів що ростуть',
        'cpu_cores' => 4,
        'ram' => 4,
        'storage' => 80,
        'bandwidth' => 4000,
        'price_monthly' => 899,
        'price_yearly' => 8990,
        'is_popular' => 0,
        'ipv4_addresses' => 1
    ],
    [
        'id' => 4,
        'name' => 'VPS Business',
        'subtitle' => 'Для великих проектів',
        'cpu_cores' => 6,
        'ram' => 8,
        'storage' => 160,
        'bandwidth' => 8000,
        'price_monthly' => 1599,
        'price_yearly' => 15990,
        'is_popular' => 0,
        'ipv4_addresses' => 2
    ]
];

$operating_systems = [
    ['id' => 'ubuntu-22', 'name' => 'Ubuntu 22.04 LTS', 'icon' => 'ubuntu.png', 'category' => 'Linux', 'popular' => true],
    ['id' => 'centos-8', 'name' => 'CentOS Stream 8', 'icon' => 'centos.png', 'category' => 'Linux', 'popular' => true],
    ['id' => 'debian-11', 'name' => 'Debian 11', 'icon' => 'debian.png', 'category' => 'Linux', 'popular' => true],
    ['id' => 'almalinux-8', 'name' => 'AlmaLinux 8', 'icon' => 'almalinux.png', 'category' => 'Linux', 'popular' => false],
    ['id' => 'rocky-8', 'name' => 'Rocky Linux 8', 'icon' => 'rocky.png', 'category' => 'Linux', 'popular' => false],
    ['id' => 'freebsd-13', 'name' => 'FreeBSD 13', 'icon' => 'freebsd.png', 'category' => 'BSD', 'popular' => false],
    ['id' => 'windows-2022', 'name' => 'Windows Server 2022', 'icon' => 'windows.png', 'category' => 'Windows', 'popular' => true],
    ['id' => 'windows-2019', 'name' => 'Windows Server 2019', 'icon' => 'windows.png', 'category' => 'Windows', 'popular' => false]
];

// Підключення файлів
try {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/config.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
    }
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
    }
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
} catch (Exception $e) {
    // Ігноруємо помилки включення файлів
}
?>
<link rel="stylesheet" href="/assets/css/pages/vds-virtual.css">
<!-- VPS Hero Section -->
<section class="vps-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-badge mb-4">
                    <i class="bi bi-lightning-charge"></i>
                    <span>KVM Віртуалізація</span>
                </div>
                
                <h1 class="hero-title mb-4">Потужні VPS/VDS сервери</h1>
                <p class="hero-subtitle mb-4">
                    Виділені ресурси, повний root-доступ, KVM віртуалізація. 
                    Ідеальне рішення для веб-додатків, баз даних та проектів що розвиваються.
                </p>
                
                <div class="hero-features">
                    <div class="feature-item">
                        <i class="bi bi-shield-check"></i>
                        <span>DDoS захист</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-hdd"></i>
                        <span>NVMe SSD диски</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-speedometer2"></i>
                        <span>1 Gbps підключення</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-gear"></i>
                        <span>Root доступ</span>
                    </div>
                </div>
                
                <div class="hero-actions mt-4">
                    <a href="#plans" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-down"></i>
                        Переглянути тарифи
                    </a>
                    <a href="#configurator" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-sliders"></i>
                        Конфігуратор
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="vps-illustration">
                    <div class="server-rack">
                        <div class="server-unit" style="--delay: 0">
                            <div class="server-lights">
                                <span class="light power"></span>
                                <span class="light network"></span>
                                <span class="light storage"></span>
                            </div>
                            <div class="server-label">VPS #1</div>
                        </div>
                        <div class="server-unit" style="--delay: 1">
                            <div class="server-lights">
                                <span class="light power"></span>
                                <span class="light network"></span>
                                <span class="light storage"></span>
                            </div>
                            <div class="server-label">VPS #2</div>
                        </div>
                        <div class="server-unit" style="--delay: 2">
                            <div class="server-lights">
                                <span class="light power"></span>
                                <span class="light network"></span>
                                <span class="light storage"></span>
                            </div>
                            <div class="server-label">VPS #3</div>
                        </div>
                        <div class="server-unit" style="--delay: 3">
                            <div class="server-lights">
                                <span class="light power"></span>
                                <span class="light network"></span>
                                <span class="light storage"></span>
                            </div>
                            <div class="server-label">VPS #4</div>
                        </div>
                    </div>
                    
                    <div class="performance-monitor">
                        <div class="monitor-title">Статус серверів</div>
                        <div class="monitor-item">
                            <span class="label">CPU</span>
                            <div class="progress">
                                <div class="progress-bar" data-width="35"></div>
                            </div>
                            <span class="value">35%</span>
                        </div>
                        <div class="monitor-item">
                            <span class="label">RAM</span>
                            <div class="progress">
                                <div class="progress-bar" data-width="60"></div>
                            </div>
                            <span class="value">60%</span>
                        </div>
                        <div class="monitor-item">
                            <span class="label">SSD</span>
                            <div class="progress">
                                <div class="progress-bar" data-width="25"></div>
                            </div>
                            <span class="value">25%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VPS Plans -->
<section id="plans" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Тарифні плани VPS</h2>
            <p class="section-subtitle">Оберіть конфігурацію що підходить для ваших завдань</p>
            
            <!-- Billing Toggle -->
            <div class="billing-toggle mt-4">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="vps-billing" id="vps-monthly" checked>
                    <label class="btn btn-outline-primary" for="vps-monthly">Щомісячно</label>
                    
                    <input type="radio" class="btn-check" name="vps-billing" id="vps-yearly">
                    <label class="btn btn-outline-primary" for="vps-yearly">
                        Щорічно <span class="badge bg-success ms-1">-20%</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($vps_plans as $plan): ?>
            <div class="col-lg-3 col-md-6">
                <div class="vps-plan <?php echo $plan['is_popular'] ? 'popular' : ''; ?>">
                    <?php if ($plan['is_popular']): ?>
                    <div class="popular-badge">Найпопулярніший</div>
                    <?php endif; ?>
                    
                    <div class="plan-header">
                        <h3 class="plan-name"><?php echo escapeOutput($plan['name']); ?></h3>
                        <p class="plan-subtitle"><?php echo escapeOutput($plan['subtitle']); ?></p>
                        
                        <div class="plan-price">
                            <div class="price monthly-price">
                                <span class="currency">від</span>
                                <span class="amount"><?php echo $plan['price_monthly']; ?></span>
                                <span class="period">грн/міс</span>
                            </div>
                            <div class="price yearly-price d-none">
                                <span class="currency">від</span>
                                <span class="amount"><?php echo round($plan['price_yearly']/12); ?></span>
                                <span class="period">грн/міс</span>
                                <div class="savings">Економія <?php echo formatPrice($plan['price_monthly'] * 12 - $plan['price_yearly']); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="plan-specs">
                        <div class="spec-item">
                            <i class="bi bi-cpu"></i>
                            <div>
                                <strong><?php echo $plan['cpu_cores']; ?> vCPU</strong>
                                <span>Intel Xeon</span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <i class="bi bi-memory"></i>
                            <div>
                                <strong><?php echo $plan['ram']; ?> GB RAM</strong>
                                <span>DDR4 ECC</span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <i class="bi bi-hdd"></i>
                            <div>
                                <strong><?php echo $plan['storage']; ?> GB SSD</strong>
                                <span>NVMe диски</span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <i class="bi bi-speedometer2"></i>
                            <div>
                                <strong><?php echo number_format($plan['bandwidth']/1000, 1); ?> TB</strong>
                                <span>Трафік/місяць</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="plan-features">
                        <ul>
                            <li><i class="bi bi-check-circle"></i> KVM віртуалізація</li>
                            <li><i class="bi bi-check-circle"></i> Root доступ</li>
                            <li><i class="bi bi-check-circle"></i> <?php echo $plan['ipv4_addresses']; ?> IPv4 адреса</li>
                            <li><i class="bi bi-check-circle"></i> 1 Gbps порт</li>
                            <li><i class="bi bi-check-circle"></i> DDoS захист</li>
                            <li><i class="bi bi-check-circle"></i> Щотижневі бекапи</li>
                            <li><i class="bi bi-check-circle"></i> VNC консоль</li>
                            <li><i class="bi bi-check-circle"></i> API управління</li>
                        </ul>
                    </div>
                    
                    <div class="plan-footer">
                        <?php
                        // Отримуємо WHMCS Product ID для цього плану
                        $whmcs_pid = $whmcs_config['product_ids'][$plan['id']] ?? $plan['id'];
                        $billing_url = $whmcs_config['billing_url'];

                        // Формуємо URLs для обох billing cycles
                        if ($whmcs_config['direct_checkout']) {
                            $order_url_monthly = "{$billing_url}/cart.php?a=add&pid={$whmcs_pid}&billingcycle=monthly&carttpl=standard";
                            $order_url_yearly = "{$billing_url}/cart.php?a=add&pid={$whmcs_pid}&billingcycle=annually&carttpl=standard";
                        } else {
                            $order_url_monthly = "{$billing_url}/cart.php?a=add&pid={$whmcs_pid}&billingcycle=monthly";
                            $order_url_yearly = "{$billing_url}/cart.php?a=add&pid={$whmcs_pid}&billingcycle=annually";
                        }
                        ?>
                        <a href="<?php echo htmlspecialchars($order_url_monthly); ?>"
                           class="btn btn-primary w-100 btn-order-vps"
                           data-url-monthly="<?php echo htmlspecialchars($order_url_monthly); ?>"
                           data-url-yearly="<?php echo htmlspecialchars($order_url_yearly); ?>"
                           target="_blank"
                           rel="noopener noreferrer"
                           title="Замовити <?php echo escapeOutput($plan['name']); ?>">
                            <i class="bi bi-rocket-takeoff"></i>
                            Замовити зараз
                        </a>
                        
                        <div class="guarantee-text">
                            <i class="bi bi-shield-check"></i>
                            <span>14 днів тест-драйв</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <div class="alert alert-info d-inline-block">
                <i class="bi bi-info-circle"></i>
                Потрібна індивідуальна конфігурація? <a href="/pages/contacts.php" class="alert-link">Зв'яжіться з нами</a>
            </div>
        </div>
    </div>
</section>

<!-- Operating Systems -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Операційні системи</h2>
            <p class="section-subtitle">Встановлюйте будь-яку ОС за кілька хвилин</p>
        </div>
        
        <div class="os-categories">
            <div class="row g-4">
                <!-- Linux Category -->
                <div class="col-lg-6">
                    <div class="os-category">
                        <h4 class="category-title">
                            <i class="bi bi-ubuntu text-orange"></i>
                            Linux дистрибутиви
                        </h4>
                        <div class="os-grid">
                            <?php foreach ($operating_systems as $os): ?>
                                <?php if ($os['category'] === 'Linux'): ?>
                                <div class="os-item <?php echo $os['popular'] ? 'popular' : ''; ?>"
                                     data-os-id="<?php echo $os['id']; ?>"
                                     data-os-name="<?php echo escapeOutput($os['name']); ?>"
                                     onclick="selectOS('<?php echo $os['id']; ?>', '<?php echo escapeOutput($os['name']); ?>')">
                                    <div class="os-icon">
                                        <img src="/assets/images/os/<?php echo $os['icon']; ?>" alt="<?php echo escapeOutput($os['name']); ?>">
                                    </div>
                                    <div class="os-name"><?php echo escapeOutput($os['name']); ?></div>
                                    <?php if ($os['popular']): ?>
                                    <span class="os-badge">Популярна</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Windows Category -->
                <div class="col-lg-6">
                    <div class="os-category">
                        <h4 class="category-title">
                            <i class="bi bi-windows text-primary"></i>
                            Windows Server
                        </h4>
                        <div class="os-grid">
                            <?php foreach ($operating_systems as $os): ?>
                                <?php if ($os['category'] === 'Windows'): ?>
                                <div class="os-item <?php echo $os['popular'] ? 'popular' : ''; ?>"
                                     data-os-id="<?php echo $os['id']; ?>"
                                     data-os-name="<?php echo escapeOutput($os['name']); ?>"
                                     onclick="selectOS('<?php echo $os['id']; ?>', '<?php echo escapeOutput($os['name']); ?>')">
                                    <div class="os-icon">
                                        <img src="/assets/images/os/<?php echo $os['icon']; ?>" alt="<?php echo escapeOutput($os['name']); ?>">
                                    </div>
                                    <div class="os-name"><?php echo escapeOutput($os['name']); ?></div>
                                    <?php if ($os['popular']): ?>
                                    <span class="os-badge">Популярна</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-info-circle"></i>
                            <small>Windows Server ліцензії оплачуються окремо</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VPS Features -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Переваги наших VPS</h2>
            <p class="section-subtitle">Чому понад 500+ клієнтів обирають наші VPS сервери</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h5>KVM віртуалізація</h5>
                    <p class="text-muted">
                        Повна ізоляція ресурсів та гарантовані характеристики. 
                        Ваш VPS працює як фізичний сервер.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-hdd-fill"></i>
                    </div>
                    <h5>NVMe SSD диски</h5>
                    <p class="text-muted">
                        Швидкість читання до 3500 МБ/с. 
                        Enterprise диски з підтримкою RAID для надійності.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-shaded"></i>
                    </div>
                    <h5>DDoS захист</h5>
                    <p class="text-muted">
                        Багаторівневий захист від DDoS атак до 500 Gbps. 
                        Автоматична фільтрація трафіку.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h5>Автобекапи</h5>
                    <p class="text-muted">
                        Щотижневі снапшоти з можливістю відновлення. 
                        Зберігання бекапів протягом 30 днів.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h5>Швидка мережа</h5>
                    <p class="text-muted">
                        1 Gbps підключення для кожного VPS. 
                        Прямі канали до IX Ukraine та основних провайдерів.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <h5>Панель управління</h5>
                    <p class="text-muted">
                        Зручна веб-панель для управління VPS. 
                        VNC консоль, reinstall OS, snapshots.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Часті питання</h2>
            <p class="section-subtitle">Відповіді на найпопулярніші запитання про VPS сервери</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="vpsFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Яка різниця між VPS та VDS?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#vpsFAQ">
                            <div class="accordion-body">
                                VPS (Virtual Private Server) та VDS (Virtual Dedicated Server) - це практично одне і те ж. Обидва терміни означають віртуальний сервер з виділеними ресурсами. Ми використовуємо KVM віртуалізацію для повної ізоляції.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Чи можна збільшити ресурси сервера?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#vpsFAQ">
                            <div class="accordion-body">
                                Так, ви можете в будь-який момент збільшити кількість RAM, CPU або дискового простору. Зміна конфігурації відбувається протягом 15-30 хвилин з коротким перезавантаженням.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Як швидко активується VPS?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#vpsFAQ">
                            <div class="accordion-body">
                                VPS активується автоматично протягом 5-15 хвилин після підтвердження оплати. Дані для доступу надсилаються на електронну пошту.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Чи надаєте ви технічну підтримку?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#vpsFAQ">
                            <div class="accordion-body">
                                Так, ми надаємо технічну підтримку 24/7 з питань роботи серверного обладнання, мережі та панелі управління. Підтримка по налаштуванню програмного забезпечення надається на платній основі.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Чи можна встановити будь-яку ОС?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#vpsFAQ">
                            <div class="accordion-body">
                                Ми надаємо готові образи популярних ОС. Також ви можете завантажити власний ISO образ та встановити будь-яку 64-бітну операційну систему через VNC консоль.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="cta-content">
                    <h2 class="mb-4">Готові запустити свій VPS?</h2>
                    <p class="lead mb-4">
                        Приєднуйтесь до понад 500+ клієнтів які довіряють нам свої проекти. 
                        Миттєва активація, надійність 99.9% та професійна підтримка.
                    </p>
                    
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="#plans" class="btn btn-primary btn-lg">
                            <i class="bi bi-rocket-takeoff"></i>
                            Обрати VPS
                        </a>
                        <a href="/pages/contacts.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-chat-dots"></i>
                            Отримати консультацію
                        </a>
                    </div>
                    
                    <div class="trust-indicators mt-4">
                        <div class="row g-3 align-items-center justify-content-center">
                            <div class="col-auto">
                                <i class="bi bi-shield-check"></i>
                                <span>99.9% Uptime</span>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-lightning"></i>
                                <span>Активація за 5 хв</span>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people"></i>
                                <span>500+ клієнтів</span>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-headset"></i>
                                <span>Підтримка 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Подключение Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Подключение пользовательского JS -->
<?php if (isset($additional_js) && is_array($additional_js)): ?>
    <?php foreach ($additional_js as $js_file): ?>
        <script src="<?php echo htmlspecialchars($js_file); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<script src="assets/js/vds-virtual.js"></script>

<!-- Enhanced OS Selection & VPS Billing Cycle Switcher -->
<style>
/* OS Selection Styles */
.os-item {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    padding: 15px;
    border-radius: 12px;
    background: #fff;
}

.os-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
    border-color: #667eea;
}

.os-item.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    position: relative;
}

.os-item.selected::after {
    content: '✓';
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

#selectedOSIndicator {
    position: sticky;
    top: 80px;
    z-index: 100;
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
</style>

<script>
// Глобальные переменные для выбранной ОС
let selectedOS = null;
let currentBillingCycle = 'monthly';

// Функция выбора ОС
function selectOS(osId, osName) {
    selectedOS = { id: osId, name: osName };

    // Убираем выделение со всех карточек
    document.querySelectorAll('.os-item').forEach(item => {
        item.classList.remove('selected');
    });

    // Выделяем выбранную
    document.querySelectorAll(`.os-item[data-os-id="${osId}"]`).forEach(item => {
        item.classList.add('selected');
    });

    // Показываем уведомление
    showOSIndicator(osName);

    // Обновляем ссылки на кнопках заказа
    updateOrderButtonsWithOS();

    // Скроллим к тарифам
    setTimeout(() => {
        document.getElementById('plans')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 500);
}

// Показать индикатор выбранной ОС
function showOSIndicator(osName) {
    let indicator = document.getElementById('selectedOSIndicator');

    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'selectedOSIndicator';
        indicator.className = 'alert alert-success';
        indicator.style.cssText = 'margin-bottom: 0;';
        document.querySelector('#plans .container').prepend(indicator);
    }

    indicator.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Обрана ОС:</strong> ${osName}
            </div>
            <button class="btn btn-sm btn-outline-success" onclick="clearOSSelection()">
                <i class="bi bi-x-circle"></i> Змінити
            </button>
        </div>
    `;
}

// Очистить выбор ОС
function clearOSSelection() {
    selectedOS = null;
    document.querySelectorAll('.os-item').forEach(item => {
        item.classList.remove('selected');
    });
    const indicator = document.getElementById('selectedOSIndicator');
    if (indicator) indicator.remove();
    updateOrderButtonsWithOS();
}

// Обновить ссылки на кнопках с учетом выбранной ОС
function updateOrderButtonsWithOS() {
    const orderButtons = document.querySelectorAll('.btn-order-vps');

    orderButtons.forEach(button => {
        let baseUrl = button.getAttribute(`data-url-${currentBillingCycle}`);

        if (selectedOS && baseUrl) {
            // Добавляем configoption для ОС
            // ВАЖНО: Настройте в WHMCS Configurable Option ID для выбора ОС
            // Формат: &configoption[X]=value где X - ID вашего config option в WHMCS
            baseUrl += `&configoption[1]=${encodeURIComponent(selectedOS.id)}`;
        }

        button.href = baseUrl;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Элементы переключателя биллинга
    const monthlyToggle = document.getElementById('vps-monthly');
    const yearlyToggle = document.getElementById('vps-yearly');
    const orderButtons = document.querySelectorAll('.btn-order-vps');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const yearlyPrices = document.querySelectorAll('.yearly-price');

    // Функция переключения billing cycle
    function updateBillingCycle(cycle) {
        currentBillingCycle = cycle;

        orderButtons.forEach(button => {
            button.href = button.getAttribute(`data-url-${cycle}`);
        });

        // Переключение отображения цен
        if (cycle === 'monthly') {
            monthlyPrices.forEach(price => price.classList.remove('d-none'));
            yearlyPrices.forEach(price => price.classList.add('d-none'));
        } else {
            monthlyPrices.forEach(price => price.classList.add('d-none'));
            yearlyPrices.forEach(price => price.classList.remove('d-none'));
        }

        // Обновляем ссылки с учетом выбранной ОС
        updateOrderButtonsWithOS();
    }

    // Обработчики событий
    if (monthlyToggle) {
        monthlyToggle.addEventListener('change', function() {
            if (this.checked) {
                updateBillingCycle('monthly');
            }
        });
    }

    if (yearlyToggle) {
        yearlyToggle.addEventListener('change', function() {
            if (this.checked) {
                updateBillingCycle('yearly');
            }
        });
    }

    // Инициализация с monthly по умолчанию
    updateBillingCycle('monthly');
});
</script>

<?php
// Підключення footer якщо файл існує
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php')) {
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
}
?>