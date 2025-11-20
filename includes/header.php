<?php
// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Проверяем авторизацию пользователя
$user_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

// Определяем текущую страницу для активных состояний
$current_page = $_SERVER['REQUEST_URI'];
$page_parts = explode('/', trim($current_page, '/'));
$page = $page_parts[1] ?? '';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang ?? 'uk'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'StormHosting UA'; ?></title>
    <meta name="description" content="<?php echo isset($meta_description) ? htmlspecialchars($meta_description) : 'Надійний хостинг для вашого бізнесу'; ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Modal Auth CSS -->
    <link rel="stylesheet" href="/assets/css/pages/modal-auth.css">

    <!-- Premium Design Enhancements -->
    <link rel="stylesheet" href="/assets/css/premium-enhancements.css">

    <!-- Additional CSS for specific pages -->
    <?php if (isset($additional_css) && is_array($additional_css)): ?>
        <?php foreach ($additional_css as $css_file): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css_file); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

<style>
/* ===== WOW HEADER STYLES ===== */
:root {
    --header-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --header-glass: rgba(255, 255, 255, 0.1);
    --header-text: #ffffff;
    --header-hover: rgba(255, 255, 255, 0.15);
    --accent-color: #f59e0b;
    --transition-fast: 0.2s ease;
    --transition-normal: 0.3s ease;
}

/* Main Header */
.wow-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    transition: all var(--transition-normal);
}

.wow-header .header-bg {
    background: var(--header-gradient);
    transition: all var(--transition-normal);
}

.wow-header.scrolled .header-bg {
    background: rgba(102, 126, 234, 0.95);
    backdrop-filter: blur(20px);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.15);
}

.header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 70px;
}

/* Logo */
.header-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--header-text);
    font-weight: 700;
    font-size: 1.4rem;
    transition: transform var(--transition-fast);
}

.header-logo:hover {
    transform: scale(1.05);
    color: var(--header-text);
}

.header-logo img {
    height: 38px;
    filter: brightness(0) invert(1);
}

.logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}

.logo-text .main {
    font-size: 1.2rem;
}

.logo-text .sub {
    font-size: 0.65rem;
    opacity: 0.8;
    font-weight: 400;
    letter-spacing: 1px;
}

/* Navigation */
.header-nav {
    display: flex;
    align-items: center;
    gap: 5px;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    color: var(--header-text);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    border-radius: 10px;
    transition: all var(--transition-fast);
    position: relative;
}

.nav-link:hover {
    background: var(--header-hover);
    color: var(--header-text);
}

.nav-link i.arrow {
    font-size: 0.7rem;
    transition: transform var(--transition-fast);
}

.nav-item:hover .nav-link i.arrow {
    transform: rotate(180deg);
}

/* Mega Dropdown */
.mega-dropdown {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(10px);
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    opacity: 0;
    visibility: hidden;
    transition: all var(--transition-normal);
    min-width: 280px;
    padding: 12px;
    pointer-events: none;
}

.nav-item:hover .mega-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
    pointer-events: auto;
}

.mega-dropdown::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    border: 8px solid transparent;
    border-bottom-color: white;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #374151;
    text-decoration: none;
    border-radius: 10px;
    transition: all var(--transition-fast);
}

.dropdown-item:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
    transform: translateX(5px);
}

.dropdown-item i {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-radius: 8px;
    color: #667eea;
    font-size: 1rem;
}

.dropdown-item:hover i {
    background: var(--header-gradient);
    color: white;
}

.dropdown-content {
    flex: 1;
}

.dropdown-title {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 2px;
}

.dropdown-desc {
    font-size: 0.75rem;
    color: #6b7280;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 8px 0;
}

/* Wide Dropdown for Services */
.mega-dropdown.wide {
    min-width: 600px;
    padding: 20px;
}

.dropdown-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.dropdown-header {
    grid-column: 1 / -1;
    padding: 8px 16px;
    font-weight: 600;
    color: #1f2937;
    font-size: 0.85rem;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 8px;
}

/* Auth Buttons */
.header-auth {
    display: flex;
    align-items: center;
    gap: 10px;
}

.auth-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 25px;
    font-weight: 500;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all var(--transition-fast);
    border: 2px solid transparent;
}

.auth-btn.login {
    color: var(--header-text);
    border-color: rgba(255, 255, 255, 0.3);
    background: transparent;
}

.auth-btn.login:hover {
    background: white;
    color: #667eea;
    border-color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.auth-btn.register {
    background: white;
    color: #667eea;
    border-color: white;
}

.auth-btn.register:hover {
    background: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

/* User Menu */
.user-menu {
    position: relative;
}

.user-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px 6px 6px;
    background: var(--header-glass);
    border-radius: 30px;
    cursor: pointer;
    transition: all var(--transition-fast);
    border: none;
    color: white;
}

.user-toggle:hover {
    background: var(--header-hover);
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.85rem;
}

.user-name {
    font-weight: 500;
    font-size: 0.85rem;
}

.user-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    min-width: 220px;
    padding: 8px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all var(--transition-normal);
}

.user-menu:hover .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-dropdown-header {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 8px;
}

.user-dropdown-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9rem;
}

.user-dropdown-email {
    font-size: 0.75rem;
    color: #6b7280;
}

.user-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: #374151;
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.85rem;
    transition: all var(--transition-fast);
}

.user-dropdown-item:hover {
    background: #f3f4f6;
    color: #667eea;
}

.user-dropdown-item i {
    width: 20px;
    text-align: center;
    opacity: 0.7;
}

.user-dropdown-item.logout {
    color: #ef4444;
    margin-top: 8px;
    border-top: 1px solid #e5e7eb;
    padding-top: 18px;
}

.user-dropdown-item.logout:hover {
    background: #fef2f2;
    color: #dc2626;
}

/* Mobile Toggle */
.mobile-toggle {
    display: none;
    background: var(--header-glass);
    border: none;
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.mobile-toggle:hover {
    background: var(--header-hover);
}

.mobile-toggle span {
    display: block;
    width: 20px;
    height: 2px;
    background: white;
    margin: 4px auto;
    transition: all var(--transition-fast);
    border-radius: 1px;
}

/* Mobile Menu */
.mobile-menu {
    position: fixed;
    top: 0;
    right: -100%;
    width: 100%;
    max-width: 400px;
    height: 100vh;
    background: white;
    z-index: 9999;
    transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto;
}

.mobile-menu.open {
    right: 0;
}

.mobile-menu-header {
    background: var(--header-gradient);
    padding: 20px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mobile-menu-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.mobile-menu-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.mobile-menu-content {
    padding: 20px;
}

.mobile-nav-section {
    margin-bottom: 20px;
}

.mobile-nav-title {
    font-weight: 600;
    color: #1f2937;
    padding: 10px 0;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.mobile-nav-title i.arrow {
    margin-left: auto;
    transition: transform var(--transition-fast);
}

.mobile-nav-title.open i.arrow {
    transform: rotate(180deg);
}

.mobile-nav-items {
    display: none;
    padding-left: 10px;
}

.mobile-nav-items.show {
    display: block;
}

.mobile-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: #4b5563;
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.85rem;
    transition: all var(--transition-fast);
}

.mobile-nav-item:hover {
    background: #f3f4f6;
    color: #667eea;
}

.mobile-nav-item i {
    width: 20px;
    text-align: center;
    opacity: 0.7;
}

.mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 9998;
    opacity: 0;
    visibility: hidden;
    transition: all var(--transition-normal);
}

.mobile-overlay.show {
    opacity: 1;
    visibility: visible;
}

/* Body padding for fixed header */
body {
    padding-top: 70px;
}

/* Responsive */
@media (max-width: 1100px) {
    .header-nav {
        display: none;
    }

    .mobile-toggle {
        display: block;
    }
}

@media (max-width: 576px) {
    .header-logo .logo-text {
        display: none;
    }

    .header-auth .auth-btn span {
        display: none;
    }

    .header-auth .auth-btn {
        padding: 8px 12px;
    }
}

/* CTA Badge */
.nav-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: var(--accent-color);
    color: white;
    font-size: 0.6rem;
    padding: 2px 5px;
    border-radius: 4px;
    font-weight: 600;
}
</style>

</head>
<body>
    <!-- WOW Header -->
    <header class="wow-header" id="wowHeader">
        <div class="header-bg">
            <div class="header-container">
                <div class="header-content">
                    <!-- Logo -->
                    <a href="/" class="header-logo">
                        <img src="/assets/img/logo.svg" alt="StormHosting">
                        <div class="logo-text">
                            <span class="main">StormHosting</span>
                            <span class="sub">ХОСТИНГ ПРОВАЙДЕР</span>
                        </div>
                    </a>

                    <!-- Desktop Navigation -->
                    <nav class="header-nav">
                        <!-- Domains -->
                        <div class="nav-item">
                            <a href="/pages/domains/domains.php" class="nav-link">
                                <span>Домени</span>
                                <i class="bi bi-chevron-down arrow"></i>
                            </a>
                            <div class="mega-dropdown">
                                <a href="/pages/domains/register.php" class="dropdown-item">
                                    <i class="bi bi-plus-circle"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Реєстрація</div>
                                        <div class="dropdown-desc">Зареєструйте новий домен</div>
                                    </div>
                                </a>
                                <a href="/pages/domains/transfer.php" class="dropdown-item">
                                    <i class="bi bi-arrow-left-right"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Трансфер</div>
                                        <div class="dropdown-desc">Перенесіть домен до нас</div>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="/pages/domains/whois.php" class="dropdown-item">
                                    <i class="bi bi-search"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">WHOIS</div>
                                        <div class="dropdown-desc">Перевірка власника</div>
                                    </div>
                                </a>
                                <a href="/pages/domains/dns.php" class="dropdown-item">
                                    <i class="bi bi-diagram-3"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">DNS перевірка</div>
                                        <div class="dropdown-desc">Діагностика записів</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Hosting -->
                        <div class="nav-item">
                            <a href="/pages/hosting/hosting.php" class="nav-link">
                                <span>Хостинг</span>
                                <i class="bi bi-chevron-down arrow"></i>
                            </a>
                            <div class="mega-dropdown wide">
                                <div class="dropdown-grid">
                                    <a href="/pages/hosting/shared.php" class="dropdown-item">
                                        <i class="bi bi-hdd-stack"></i>
                                        <div class="dropdown-content">
                                            <div class="dropdown-title">Спільний хостинг</div>
                                            <div class="dropdown-desc">Ідеально для сайтів</div>
                                        </div>
                                    </a>
                                    <a href="/pages/hosting/reseller.php" class="dropdown-item">
                                        <i class="bi bi-people"></i>
                                        <div class="dropdown-content">
                                            <div class="dropdown-title">Реселер</div>
                                            <div class="dropdown-desc">Продавайте хостинг</div>
                                        </div>
                                    </a>
                                    <a href="/pages/hosting/cloud.php" class="dropdown-item">
                                        <i class="bi bi-cloud"></i>
                                        <div class="dropdown-content">
                                            <div class="dropdown-title">Cloud сховище</div>
                                            <div class="dropdown-desc">Файли у хмарі</div>
                                        </div>
                                    </a>
                                    <a href="/pages/info/ssl.php" class="dropdown-item">
                                        <i class="bi bi-shield-lock"></i>
                                        <div class="dropdown-content">
                                            <div class="dropdown-title">SSL сертифікати</div>
                                            <div class="dropdown-desc">Захист з'єднання</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- VDS/VPS -->
                        <div class="nav-item">
                            <a href="/pages/vds/virtual.php" class="nav-link">
                                <span>VDS/VPS</span>
                                <i class="bi bi-chevron-down arrow"></i>
                            </a>
                            <div class="mega-dropdown">
                                <a href="/pages/vds/virtual.php" class="dropdown-item">
                                    <i class="bi bi-cpu"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Віртуальні сервери</div>
                                        <div class="dropdown-desc">KVM VPS</div>
                                    </div>
                                </a>
                                <a href="/pages/vds/dedicated.php" class="dropdown-item">
                                    <i class="bi bi-pc-display-horizontal"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Виділені сервери</div>
                                        <div class="dropdown-desc">Фізичні сервери</div>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="/pages/vds/vds-calc.php" class="dropdown-item">
                                    <i class="bi bi-calculator"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Калькулятор</div>
                                        <div class="dropdown-desc">Розрахунок вартості</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Tools -->
                        <div class="nav-item">
                            <a href="#" class="nav-link">
                                <span>Інструменти</span>
                                <i class="bi bi-chevron-down arrow"></i>
                            </a>
                            <div class="mega-dropdown">
                                <a href="/pages/tools/site-check.php" class="dropdown-item">
                                    <i class="bi bi-globe2"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Перевірка сайту</div>
                                        <div class="dropdown-desc">Доступність online</div>
                                    </div>
                                </a>
                                <a href="/pages/tools/ip-check.php" class="dropdown-item">
                                    <i class="bi bi-router"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Перевірка IP</div>
                                        <div class="dropdown-desc">Геолокація та інфо</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="nav-item">
                            <a href="/pages/info/about.php" class="nav-link">
                                <span>Інфо</span>
                                <i class="bi bi-chevron-down arrow"></i>
                            </a>
                            <div class="mega-dropdown">
                                <a href="/pages/info/about.php" class="dropdown-item">
                                    <i class="bi bi-building"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Про компанію</div>
                                        <div class="dropdown-desc">Наша історія</div>
                                    </div>
                                </a>
                                <a href="/pages/info/quality.php" class="dropdown-item">
                                    <i class="bi bi-shield-check"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Гарантія якості</div>
                                        <div class="dropdown-desc">SLA та стандарти</div>
                                    </div>
                                </a>
                                <a href="/pages/info/faq.php" class="dropdown-item">
                                    <i class="bi bi-question-circle"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">FAQ</div>
                                        <div class="dropdown-desc">Часті питання</div>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="/pages/contacts.php" class="dropdown-item">
                                    <i class="bi bi-telephone"></i>
                                    <div class="dropdown-content">
                                        <div class="dropdown-title">Контакти</div>
                                        <div class="dropdown-desc">Зв'яжіться з нами</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </nav>

                    <!-- Auth / User -->
                    <div class="header-auth">
                        <?php if ($user_logged_in): ?>
                            <div class="user-menu">
                                <button class="user-toggle">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                                    </div>
                                    <span class="user-name d-none d-sm-inline"><?php echo htmlspecialchars($user_name); ?></span>
                                    <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                                </button>
                                <div class="user-dropdown">
                                    <div class="user-dropdown-header">
                                        <div class="user-dropdown-name"><?php echo htmlspecialchars($user_name); ?></div>
                                        <div class="user-dropdown-email"><?php echo htmlspecialchars($user_email); ?></div>
                                    </div>
                                    <a href="/client/dashboard-new.php" class="user-dropdown-item">
                                        <i class="bi bi-speedometer2"></i>
                                        <span>Панель управління</span>
                                    </a>
                                    <a href="/client/profile.php" class="user-dropdown-item">
                                        <i class="bi bi-person-gear"></i>
                                        <span>Налаштування</span>
                                    </a>
                                    <a href="/auth/logout.php" class="user-dropdown-item logout" onclick="return confirm('Вийти з системи?')">
                                        <i class="bi bi-box-arrow-right"></i>
                                        <span>Вийти</span>
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="#" class="auth-btn login" data-open-login>
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>Вхід</span>
                            </a>
                            <a href="#" class="auth-btn register" data-open-register>
                                <i class="bi bi-person-plus"></i>
                                <span>Реєстрація</span>
                            </a>
                        <?php endif; ?>

                        <!-- Mobile Toggle -->
                        <button class="mobile-toggle" id="mobileToggle">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <span style="font-weight: 600; font-size: 1.1rem;">Меню</span>
            <button class="mobile-menu-close" id="mobileClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="mobile-menu-content">
            <?php if ($user_logged_in): ?>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="bi bi-person-circle"></i>
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                    </div>
                    <div class="mobile-nav-items show">
                        <a href="/client/dashboard-new.php" class="mobile-nav-item">
                            <i class="bi bi-speedometer2"></i>
                            <span>Панель управління</span>
                        </a>
                        <a href="/client/profile.php" class="mobile-nav-item">
                            <i class="bi bi-person-gear"></i>
                            <span>Налаштування</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Domains -->
            <div class="mobile-nav-section">
                <div class="mobile-nav-title" onclick="toggleMobileNav(this)">
                    <i class="bi bi-globe"></i>
                    <span>Домени</span>
                    <i class="bi bi-chevron-down arrow"></i>
                </div>
                <div class="mobile-nav-items">
                    <a href="/pages/domains/register.php" class="mobile-nav-item">
                        <i class="bi bi-plus-circle"></i>
                        <span>Реєстрація</span>
                    </a>
                    <a href="/pages/domains/transfer.php" class="mobile-nav-item">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Трансфер</span>
                    </a>
                    <a href="/pages/domains/whois.php" class="mobile-nav-item">
                        <i class="bi bi-search"></i>
                        <span>WHOIS</span>
                    </a>
                    <a href="/pages/domains/dns.php" class="mobile-nav-item">
                        <i class="bi bi-diagram-3"></i>
                        <span>DNS перевірка</span>
                    </a>
                </div>
            </div>

            <!-- Hosting -->
            <div class="mobile-nav-section">
                <div class="mobile-nav-title" onclick="toggleMobileNav(this)">
                    <i class="bi bi-server"></i>
                    <span>Хостинг</span>
                    <i class="bi bi-chevron-down arrow"></i>
                </div>
                <div class="mobile-nav-items">
                    <a href="/pages/hosting/shared.php" class="mobile-nav-item">
                        <i class="bi bi-hdd-stack"></i>
                        <span>Спільний хостинг</span>
                    </a>
                    <a href="/pages/hosting/reseller.php" class="mobile-nav-item">
                        <i class="bi bi-people"></i>
                        <span>Реселер</span>
                    </a>
                    <a href="/pages/hosting/cloud.php" class="mobile-nav-item">
                        <i class="bi bi-cloud"></i>
                        <span>Cloud сховище</span>
                    </a>
                    <a href="/pages/info/ssl.php" class="mobile-nav-item">
                        <i class="bi bi-shield-lock"></i>
                        <span>SSL сертифікати</span>
                    </a>
                </div>
            </div>

            <!-- VDS -->
            <div class="mobile-nav-section">
                <div class="mobile-nav-title" onclick="toggleMobileNav(this)">
                    <i class="bi bi-cpu"></i>
                    <span>VDS/VPS</span>
                    <i class="bi bi-chevron-down arrow"></i>
                </div>
                <div class="mobile-nav-items">
                    <a href="/pages/vds/virtual.php" class="mobile-nav-item">
                        <i class="bi bi-cpu"></i>
                        <span>Віртуальні сервери</span>
                    </a>
                    <a href="/pages/vds/dedicated.php" class="mobile-nav-item">
                        <i class="bi bi-pc-display-horizontal"></i>
                        <span>Виділені сервери</span>
                    </a>
                    <a href="/pages/vds/vds-calc.php" class="mobile-nav-item">
                        <i class="bi bi-calculator"></i>
                        <span>Калькулятор</span>
                    </a>
                </div>
            </div>

            <!-- Tools -->
            <div class="mobile-nav-section">
                <div class="mobile-nav-title" onclick="toggleMobileNav(this)">
                    <i class="bi bi-tools"></i>
                    <span>Інструменти</span>
                    <i class="bi bi-chevron-down arrow"></i>
                </div>
                <div class="mobile-nav-items">
                    <a href="/pages/tools/site-check.php" class="mobile-nav-item">
                        <i class="bi bi-globe2"></i>
                        <span>Перевірка сайту</span>
                    </a>
                    <a href="/pages/tools/ip-check.php" class="mobile-nav-item">
                        <i class="bi bi-router"></i>
                        <span>Перевірка IP</span>
                    </a>
                </div>
            </div>

            <!-- Info -->
            <div class="mobile-nav-section">
                <div class="mobile-nav-title" onclick="toggleMobileNav(this)">
                    <i class="bi bi-info-circle"></i>
                    <span>Інформація</span>
                    <i class="bi bi-chevron-down arrow"></i>
                </div>
                <div class="mobile-nav-items">
                    <a href="/pages/info/about.php" class="mobile-nav-item">
                        <i class="bi bi-building"></i>
                        <span>Про компанію</span>
                    </a>
                    <a href="/pages/info/quality.php" class="mobile-nav-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Гарантія якості</span>
                    </a>
                    <a href="/pages/info/faq.php" class="mobile-nav-item">
                        <i class="bi bi-question-circle"></i>
                        <span>FAQ</span>
                    </a>
                    <a href="/pages/info/rules.php" class="mobile-nav-item">
                        <i class="bi bi-file-text"></i>
                        <span>Правила</span>
                    </a>
                    <a href="/pages/info/legal.php" class="mobile-nav-item">
                        <i class="bi bi-briefcase"></i>
                        <span>Юридична інфо</span>
                    </a>
                    <a href="/pages/contacts.php" class="mobile-nav-item">
                        <i class="bi bi-telephone"></i>
                        <span>Контакти</span>
                    </a>
                </div>
            </div>

            <?php if ($user_logged_in): ?>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                    <a href="/auth/logout.php" class="mobile-nav-item" style="color: #ef4444;" onclick="return confirm('Вийти?')">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Вийти з системи</span>
                    </a>
                </div>
            <?php else: ?>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; display: flex; gap: 10px;">
                    <a href="#" class="btn btn-outline-primary flex-fill" data-open-login>Вхід</a>
                    <a href="#" class="btn btn-primary flex-fill" data-open-register>Реєстрація</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <!-- Modal Auth JS -->
    <?php if (!$user_logged_in): ?>
        <script src="/assets/js/modal-auth.js"></script>
    <?php endif; ?>

    <!-- Header JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('wowHeader');
            const mobileToggle = document.getElementById('mobileToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const mobileClose = document.getElementById('mobileClose');

            // Scroll effect
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // Mobile menu
            function openMobile() {
                mobileMenu.classList.add('open');
                mobileOverlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeMobile() {
                mobileMenu.classList.remove('open');
                mobileOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }

            mobileToggle.addEventListener('click', openMobile);
            mobileClose.addEventListener('click', closeMobile);
            mobileOverlay.addEventListener('click', closeMobile);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeMobile();
            });

            // Init auth modal
            <?php if (!$user_logged_in): ?>
                if (typeof AuthModal !== 'undefined') {
                    window.authModal = new AuthModal();
                }
            <?php endif; ?>
        });

        // Toggle mobile nav sections
        function toggleMobileNav(element) {
            const items = element.nextElementSibling;
            const isOpen = items.classList.contains('show');

            // Close all
            document.querySelectorAll('.mobile-nav-items').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.mobile-nav-title').forEach(el => el.classList.remove('open'));

            // Open current if was closed
            if (!isOpen) {
                items.classList.add('show');
                element.classList.add('open');
            }
        }
    </script>

    <?php if (isset($additional_js) && is_array($additional_js)): ?>
        <?php foreach ($additional_js as $js_file): ?>
            <script src="<?php echo htmlspecialchars($js_file); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
