<?php
/**
 * StormHosting UA - Главная страница админ-панели
 * Файл: /admin/index.php
 */

// Запуск сессии
session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

// Проверка прав доступа (роль должна быть admin, moderator или publisher)
$allowed_roles = ['admin', 'moderator', 'publisher'];
if (!isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], $allowed_roles)) {
    header('Location: /admin/login.php');
    exit;
}

// Определяем константу для защиты от прямого доступа
define('SECURE_ACCESS', true);

$page_title = 'Адмін-панель - StormHosting UA';
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="/admin/assets/css/admin.css">

    <style>
        /* Временные стили пока не создали отдельный файл */
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-bg: #1a1d2e;
            --sidebar-hover: #252942;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f8fafc;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .admin-sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #FFD700, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .admin-sidebar-menu {
            padding: 1rem 0;
        }

        .admin-menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .admin-menu-item:hover {
            background: var(--sidebar-hover);
            color: white;
            border-left-color: var(--primary-color);
        }

        .admin-menu-item.active {
            background: var(--sidebar-hover);
            color: white;
            border-left-color: var(--primary-color);
        }

        .admin-menu-item i {
            width: 24px;
            margin-right: 1rem;
            font-size: 1.1rem;
        }

        /* Main Content */
        .admin-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .admin-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .admin-content {
            padding: 2rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
        }

        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-card-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action-btn {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            text-decoration: none;
            color: #1a1d2e;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .quick-action-btn:hover {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .quick-action-btn i {
            font-size: 2rem;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <div class="admin-sidebar-logo">
            <i class="bi bi-lightning-charge me-2"></i>StormHosting
        </div>
        <small class="text-muted">Адмін-панель</small>
    </div>

    <nav class="admin-sidebar-menu">
        <a href="/admin/index.php?page=dashboard" class="admin-menu-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Дашборд</span>
        </a>

        <a href="/admin/index.php?page=news" class="admin-menu-item <?php echo $current_page === 'news' ? 'active' : ''; ?>">
            <i class="bi bi-newspaper"></i>
            <span>Новини</span>
        </a>

        <a href="/admin/index.php?page=domains" class="admin-menu-item <?php echo $current_page === 'domains' ? 'active' : ''; ?>">
            <i class="bi bi-globe2"></i>
            <span>Домени</span>
        </a>

        <a href="/admin/index.php?page=hosting" class="admin-menu-item <?php echo $current_page === 'hosting' ? 'active' : ''; ?>">
            <i class="bi bi-hdd-stack"></i>
            <span>Хостинг</span>
        </a>

        <a href="/admin/index.php?page=vps" class="admin-menu-item <?php echo $current_page === 'vps' ? 'active' : ''; ?>">
            <i class="bi bi-server"></i>
            <span>VPS/VDS</span>
        </a>

        <a href="/admin/index.php?page=users" class="admin-menu-item <?php echo $current_page === 'users' ? 'active' : ''; ?>">
            <i class="bi bi-people"></i>
            <span>Користувачі</span>
        </a>

        <hr style="border-color: rgba(255,255,255,0.1); margin: 1rem 0;">

        <a href="/admin/support-panel.php" class="admin-menu-item">
            <i class="bi bi-chat-dots"></i>
            <span>Підтримка</span>
        </a>

        <a href="/admin/index.php?page=settings" class="admin-menu-item <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
            <i class="bi bi-gear"></i>
            <span>Налаштування</span>
        </a>
    </nav>
</aside>

<!-- Main Content -->
<main class="admin-main">
    <!-- Header -->
    <header class="admin-header">
        <h1 class="h4 mb-0">
            <?php
            $page_titles = [
                'dashboard' => 'Дашборд',
                'news' => 'Управління новинами',
                'domains' => 'Управління доменами',
                'hosting' => 'Управління хостингом',
                'vps' => 'Управління VPS/VDS',
                'users' => 'Управління користувачами',
                'settings' => 'Налаштування'
            ];
            echo isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Адмін-панель';
            ?>
        </h1>

        <div class="d-flex align-items-center gap-3">
            <a href="/" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i>
                Перегляд сайту
            </a>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                    <span class="badge bg-<?php echo $_SESSION['admin_role'] === 'admin' ? 'danger' : ($_SESSION['admin_role'] === 'moderator' ? 'warning' : 'info'); ?> ms-1">
                        <?php echo ucfirst($_SESSION['admin_role']); ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Роль: <?php echo ucfirst($_SESSION['admin_role']); ?></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/admin/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Вихід</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Content -->
    <div class="admin-content">
        <?php
        // Загружаем соответствующую страницу
        switch ($current_page) {
            case 'dashboard':
                include 'pages/dashboard.php';
                break;
            case 'news':
                include 'pages/news.php';
                break;
            case 'domains':
                include 'pages/domains.php';
                break;
            case 'hosting':
                include 'pages/hosting.php';
                break;
            case 'vps':
                include 'pages/vps.php';
                break;
            case 'users':
                include 'pages/users.php';
                break;
            case 'settings':
                include 'pages/settings.php';
                break;
            default:
                include 'pages/dashboard.php';
        }
        ?>
    </div>
</main>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Admin JS -->
<script src="/admin/assets/js/admin.js"></script>

</body>
</html>
