<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'dns';
$page_title = 'DNS Lookup - Перевірка DNS записів | StormHosting UA';
$meta_description = 'Безкоштовна перевірка DNS записів доменів. A, MX, CNAME, TXT, NS, SOA та інші типи записів.';
$meta_keywords = 'dns lookup, перевірка dns, dns записи, mx записи, a записи';

// Додаткові CSS та JS файли
$additional_css = [
    '/assets/css/pages/dns-lookup.css'
];

$additional_js = [
    '/assets/js/dns-lookup.js'
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// DNS record types
$dns_types = [
    'A' => ['name' => 'A Record', 'desc' => 'IPv4 адреса', 'icon' => 'hdd-network'],
    'AAAA' => ['name' => 'AAAA Record', 'desc' => 'IPv6 адреса', 'icon' => 'hdd-network-fill'],
    'MX' => ['name' => 'MX Record', 'desc' => 'Поштові сервери', 'icon' => 'envelope-at'],
    'CNAME' => ['name' => 'CNAME Record', 'desc' => 'Канонічне ім\'я', 'icon' => 'arrow-right-circle'],
    'TXT' => ['name' => 'TXT Record', 'desc' => 'Текстові записи', 'icon' => 'file-text'],
    'NS' => ['name' => 'NS Record', 'desc' => 'DNS сервери', 'icon' => 'diagram-3'],
    'SOA' => ['name' => 'SOA Record', 'desc' => 'Авторитетність зони', 'icon' => 'shield-check'],
    'SRV' => ['name' => 'SRV Record', 'desc' => 'Сервісні записи', 'icon' => 'server']
];
?>

<!-- DNS Hero Section -->
<section class="dns-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="bi bi-diagram-3"></i>
                <span>DNS Lookup</span>
            </div>
            <h1 class="hero-title">Перевірка DNS записів</h1>
            <p class="hero-subtitle">Дізнайтесь IP адреси, поштові сервери, DNS сервери<br>та іншу технічну інформацію про домен</p>
        </div>

        <!-- Search Form -->
        <div class="dns-search-card">
            <form id="dnsForm" class="dns-search-form">
                <div class="search-row">
                    <div class="domain-input-group">
                        <i class="bi bi-globe search-icon"></i>
                        <input
                            type="text"
                            id="domainInput"
                            name="domain"
                            class="search-input"
                            placeholder="example.com"
                            autocomplete="off"
                            required>
                    </div>

                    <select id="recordType" name="record_type" class="record-select">
                        <?php foreach ($dns_types as $type => $info): ?>
                        <option value="<?php echo $type; ?>">
                            <?php echo $type; ?> - <?php echo $info['desc']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="search-btn" id="searchBtn">
                        <i class="bi bi-search"></i>
                        <span>Перевірити</span>
                    </button>
                </div>

                <!-- Quick Buttons -->
                <div class="quick-types">
                    <span class="quick-label">Швидкий вибір:</span>
                    <button type="button" class="quick-btn" data-type="A">A</button>
                    <button type="button" class="quick-btn" data-type="MX">MX</button>
                    <button type="button" class="quick-btn" data-type="NS">NS</button>
                    <button type="button" class="quick-btn" data-type="CNAME">CNAME</button>
                    <button type="button" class="quick-btn" data-type="TXT">TXT</button>
                </div>
            </form>
        </div>

        <!-- Results Container -->
        <div id="dnsResults"></div>
    </div>
</section>

<!-- Record Types Section -->
<section class="record-types-section">
    <div class="container">
        <h2 class="section-title">Типи DNS записів</h2>
        <div class="types-grid">
            <?php foreach ($dns_types as $type => $info): ?>
            <div class="type-card">
                <div class="type-icon">
                    <i class="bi bi-<?php echo $info['icon']; ?>"></i>
                </div>
                <h3><?php echo $type; ?></h3>
                <p class="type-name"><?php echo $info['name']; ?></p>
                <p class="type-desc"><?php echo $info['desc']; ?></p>
                <button class="test-btn" data-type="<?php echo $type; ?>">
                    <i class="bi bi-play-circle"></i>
                    Тестувати
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-content">
            <div class="features-text">
                <h2>Для чого потрібні DNS записи?</h2>
                <p class="lead">DNS (Domain Name System) - це система, яка перетворює доменні імена на IP адреси та зберігає іншу важливу інформацію.</p>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-number">01</div>
                        <div class="feature-content">
                            <h4>Направлення трафіку</h4>
                            <p>A та AAAA записи вказують, на який сервер направляти відвідувачів вашого сайту</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-number">02</div>
                        <div class="feature-content">
                            <h4>Налаштування пошти</h4>
                            <p>MX записи визначають, куди доставляти email для вашого домену</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-number">03</div>
                        <div class="feature-content">
                            <h4>Перевірка автентичності</h4>
                            <p>TXT записи використовуються для SPF, DKIM та інших систем захисту</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-number">04</div>
                        <div class="feature-content">
                            <h4>Делегування доменів</h4>
                            <p>NS записи вказують, які DNS сервери авторитетні для вашого домену</p>
                        </div>
                    </div>
                </div>

                <a href="/client/domains" class="btn-primary-large">
                    <i class="bi bi-gear"></i>
                    Керувати DNS записами
                </a>
            </div>

            <div class="features-visual">
                <div class="dns-flow-diagram">
                    <div class="flow-step">
                        <div class="step-icon"><i class="bi bi-person"></i></div>
                        <div class="step-label">Користувач</div>
                        <div class="step-desc">example.com</div>
                    </div>
                    <div class="flow-arrow"><i class="bi bi-arrow-down"></i></div>
                    <div class="flow-step active">
                        <div class="step-icon"><i class="bi bi-hdd-network"></i></div>
                        <div class="step-label">DNS Сервер</div>
                        <div class="step-desc">Пошук записів</div>
                    </div>
                    <div class="flow-arrow"><i class="bi bi-arrow-down"></i></div>
                    <div class="flow-step">
                        <div class="step-icon"><i class="bi bi-server"></i></div>
                        <div class="step-label">Веб-сервер</div>
                        <div class="step-desc">185.25.118.10</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our DNS Servers Section -->
<section class="our-servers-section">
    <div class="container">
        <div class="servers-content">
            <div class="servers-header">
                <h2>Наші DNS сервери</h2>
                <p>Використовуйте швидкі та надійні DNS сервери StormHosting UA</p>
            </div>

            <div class="servers-grid">
                <div class="server-card">
                    <div class="server-icon">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <div class="server-name">ns1.sthost.pro</div>
                    <div class="server-ip">195.22.131.11</div>
                    <div class="server-status online">
                        <i class="bi bi-check-circle-fill"></i>
                        Online
                    </div>
                </div>

                <div class="server-card">
                    <div class="server-icon">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                    <div class="server-name">ns2.sthost.pro</div>
                    <div class="server-ip">46.232.232.38</div>
                    <div class="server-status online">
                        <i class="bi bi-check-circle-fill"></i>
                        Online
                    </div>
                </div>
            </div>

            <div class="servers-stats">
                <div class="stat">
                    <div class="stat-value">99.9%</div>
                    <div class="stat-label">Аптайм</div>
                </div>
                <div class="stat">
                    <div class="stat-value">&lt;5мс</div>
                    <div class="stat-label">Відгук</div>
                </div>
                <div class="stat">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Моніторинг</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Tools Section -->
<section class="tools-section">
    <div class="container">
        <h2 class="section-title">Інші інструменти</h2>
        <div class="tools-grid">
            <a href="/pages/domains/whois.php" class="tool-card">
                <div class="tool-icon">
                    <i class="bi bi-search"></i>
                </div>
                <h3>WHOIS Lookup</h3>
                <p>Інформація про власника домену</p>
                <span class="tool-link">Перевірити <i class="bi bi-arrow-right"></i></span>
            </a>

            <a href="/pages/domains/register.php" class="tool-card">
                <div class="tool-icon">
                    <i class="bi bi-plus-circle"></i>
                </div>
                <h3>Реєстрація</h3>
                <p>Зареєструйте новий домен</p>
                <span class="tool-link">Зареєструвати <i class="bi bi-arrow-right"></i></span>
            </a>

            <a href="/pages/domains/transfer.php" class="tool-card">
                <div class="tool-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
                <h3>Трансфер</h3>
                <p>Перенесіть домен до нас</p>
                <span class="tool-link">Перенести <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
