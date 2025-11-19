<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'dns';
$page_title = 'DNS Lookup - StormHosting UA';
$meta_description = 'DNS lookup сервіс для перевірки DNS записів доменів. Перевіряйте A, AAAA, MX, CNAME, TXT та інші DNS записи безкоштовно.';
$meta_keywords = 'dns lookup, перевірка dns, dns записи, mx записи, а записи, cname записи';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/domains2.css'
];

$additional_js = [
    '/assets/js/domains.js'
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Типы DNS записей
$dns_record_types = [
    'A' => 'IPv4 адреса',
    'AAAA' => 'IPv6 адреса',
    'MX' => 'Поштові сервери',
    'CNAME' => 'Канонічне ім\'я',
    'TXT' => 'Текстові записи',
    'NS' => 'DNS сервери',
    'SOA' => 'Авторитетність зони',
    'PTR' => 'Зворотний DNS',
    'SRV' => 'Сервісні записи'
];

?>

<!-- DNS Hero -->
<section class="dns-hero py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-5 fw-bold mb-4">DNS Lookup</h1>
                <p class="lead mb-5">Перевірте DNS записи будь-якого домену. Дізнайтесь IP адреси, поштові сервери, DNS сервери та іншу технічну інформацію.</p>

                <!-- DNS Search Form -->
                <div class="dns-search-form">
                    <form id="dnsForm" class="row g-3 justify-content-center">
                        <input type="hidden" id="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="col-md-6">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="bi bi-globe"></i>
                                </span>
                                <input type="text"
                                       id="dnsDomain"
                                       class="form-control"
                                       placeholder="example.com"
                                       pattern="[a-zA-Z0-9.-]+"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <select id="recordType" class="form-select form-select-lg">
                                <?php foreach ($dns_record_types as $type => $description): ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?> - <?php echo $description; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-search"></i>
                                Перевірити
                            </button>
                        </div>
                    </form>

                    <!-- Quick Type Buttons -->
                    <div class="quick-types mt-3">
                        <small class="text-muted">Швидкий вибір: </small>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="A">A</button>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="MX">MX</button>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="NS">NS</button>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="CNAME">CNAME</button>
                        <button class="btn btn-sm btn-outline-secondary quick-type-btn" data-type="TXT">TXT</button>
                    </div>

                    <!-- Search Results -->
                    <div id="dnsResults" class="mt-5"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DNS Record Types -->
<section class="dns-types py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Типи DNS записів</h2>
                <p class="section-subtitle">Розуміння різних типів DNS записів допоможе вам краще керувати доменом</p>
            </div>
        </div>

        <div class="row g-4">
            <?php
            $type_descriptions = [
                'A' => ['icon' => 'bi-hdd-network', 'desc' => 'Вказує IPv4 адресу сервера, на якому розміщений сайт'],
                'AAAA' => ['icon' => 'bi-hdd-network-fill', 'desc' => 'Вказує IPv6 адресу сервера для сучасних мереж'],
                'MX' => ['icon' => 'bi-envelope-at', 'desc' => 'Визначає поштові сервери для доставки електронної пошти'],
                'CNAME' => ['icon' => 'bi-arrow-right-circle', 'desc' => 'Створює псевдонім для іншого доменного імені'],
                'TXT' => ['icon' => 'bi-file-text', 'desc' => 'Містить текстову інформацію, SPF, DKIM записи'],
                'NS' => ['icon' => 'bi-dns', 'desc' => 'Вказує авторитетні DNS сервери для домену']
            ];
            ?>

            <?php foreach ($type_descriptions as $type => $info): ?>
            <div class="col-lg-4 col-md-6">
                <div class="dns-type-card h-100">
                    <div class="dns-type-icon">
                        <i class="bi <?php echo $info['icon']; ?>"></i>
                    </div>
                    <h4><?php echo $type; ?> - <?php echo $dns_record_types[$type]; ?></h4>
                    <p><?php echo $info['desc']; ?></p>
                    <button class="btn btn-outline-primary btn-sm test-type-btn" data-type="<?php echo $type; ?>">
                        Тестувати <?php echo $type; ?> запис
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- DNS Management -->
<section class="dns-management py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold">Керування DNS записами</h2>
                <p class="lead">Потрібно налаштувати DNS для вашого домену?</p>

                <div class="dns-features">
                    <div class="feature-item">
                        <i class="bi bi-gear text-primary"></i>
                        <div>
                            <h5>Зручний редактор</h5>
                            <p>Інтуїтивний інтерфейс для редагування DNS записів</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <i class="bi bi-lightning text-primary"></i>
                        <div>
                            <h5>Швидке поширення</h5>
                            <p>Зміни DNS записів поширюються протягом кількох хвилин</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <i class="bi bi-shield-check text-primary"></i>
                        <div>
                            <h5>Безпека та надійність</h5>
                            <p>Захищені DNS сервери з 99.9% аптаймом</p>
                        </div>
                    </div>
                </div>

                <a href="/client/domains" class="btn btn-primary btn-lg">
                    <i class="bi bi-gear"></i>
                    Керувати DNS
                </a>
            </div>

            <div class="col-lg-6">
                <div class="dns-editor-preview">
                    <div class="editor-header">
                        <div class="window-controls">
                            <span class="control close"></span>
                            <span class="control minimize"></span>
                            <span class="control maximize"></span>
                        </div>
                        <div class="window-title">DNS Manager - example.com</div>
                    </div>

                    <div class="editor-content">
                        <div class="dns-record">
                            <span class="record-type">A</span>
                            <span class="record-name">@</span>
                            <span class="record-value">185.25.118.10</span>
                            <span class="record-ttl">3600</span>
                        </div>
                        <div class="dns-record">
                            <span class="record-type">A</span>
                            <span class="record-name">www</span>
                            <span class="record-value">185.25.118.10</span>
                            <span class="record-ttl">3600</span>
                        </div>
                        <div class="dns-record">
                            <span class="record-type">MX</span>
                            <span class="record-name">@</span>
                            <span class="record-value">mail.example.com</span>
                            <span class="record-ttl">10</span>
                        </div>
                        <div class="dns-record">
                            <span class="record-type">CNAME</span>
                            <span class="record-name">mail</span>
                            <span class="record-value">ghs.google.com</span>
                            <span class="record-ttl">3600</span>
                        </div>
                        <div class="dns-record new">
                            <span class="record-type">TXT</span>
                            <span class="record-name">@</span>
                            <span class="record-value">v=spf1 include:_spf.google.com ~all</span>
                            <span class="record-ttl">3600</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DNS Our Servers -->
<section class="our-dns-servers py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold">Наші DNS сервери</h2>
                <p class="lead">Використовуйте наші швидкі та надійні DNS сервери для ваших доменів</p>

                <div class="dns-servers">
                    <div class="server-item">
                        <i class="bi bi-hdd-network"></i>
                        <span class="server-name">ns1.sthost.pro</span>
                        <span class="server-ip">195.22.131.11</span>
                    </div>
                    <div class="server-item">
                        <i class="bi bi-hdd-network"></i>
                        <span class="server-name">ns2.sthost.pro</span>
                        <span class="server-ip">46.232.232.38</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 text-lg-end">
                <div class="dns-stats">
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Аптайм</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">&lt;5мс</div>
                        <div class="stat-label">Відгук</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Моніторинг</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
