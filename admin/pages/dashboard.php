<?php
/**
 * StormHosting UA - Дашборд админ-панели
 * Файл: /admin/pages/dashboard.php
 */

// Подключение к БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Получаем статистику
$stats = [
    'users' => 0,
    'news' => 0,
    'domains' => 0,
    'hosting_plans' => 0,
    'vps_plans' => 0
];

try {
    // Количество пользователей
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $stats['users'] = $result->fetch_assoc()['count'];
    }

    // Количество новостей
    $result = $conn->query("SELECT COUNT(*) as count FROM news");
    if ($result) {
        $stats['news'] = $result->fetch_assoc()['count'];
    }

    // Количество доменных зон
    $result = $conn->query("SELECT COUNT(*) as count FROM domain_zones");
    if ($result) {
        $stats['domains'] = $result->fetch_assoc()['count'];
    }

    // Количество планов хостинга
    $result = $conn->query("SELECT COUNT(*) as count FROM hosting_plans");
    if ($result) {
        $stats['hosting_plans'] = $result->fetch_assoc()['count'];
    }

    // Количество VPS планов
    $result = $conn->query("SELECT COUNT(*) as count FROM vps_plans");
    if ($result) {
        $stats['vps_plans'] = $result->fetch_assoc()['count'];
    }
} catch (Exception $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
}
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white;">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['users']); ?></div>
        <div class="stat-card-label">Користувачів</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(45deg, #f093fb, #f5576c); color: white;">
            <i class="bi bi-newspaper"></i>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['news']); ?></div>
        <div class="stat-card-label">Новин</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(45deg, #4facfe, #00f2fe); color: white;">
            <i class="bi bi-globe2"></i>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['domains']); ?></div>
        <div class="stat-card-label">Доменних зон</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(45deg, #43e97b, #38f9d7); color: white;">
            <i class="bi bi-hdd-stack"></i>
        </div>
        <div class="stat-card-value"><?php echo number_format($stats['hosting_plans']); ?></div>
        <div class="stat-card-label">Планів хостингу</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Швидкі дії</h5>
    </div>
    <div class="card-body">
        <div class="quick-actions">
            <a href="/admin/index.php?page=news&action=create" class="quick-action-btn">
                <i class="bi bi-plus-circle"></i>
                <span>Додати новину</span>
            </a>

            <a href="/admin/index.php?page=domains&action=create" class="quick-action-btn">
                <i class="bi bi-plus-circle"></i>
                <span>Додати домен</span>
            </a>

            <a href="/admin/index.php?page=hosting&action=create" class="quick-action-btn">
                <i class="bi bi-plus-circle"></i>
                <span>Додати план хостингу</span>
            </a>

            <a href="/admin/index.php?page=users" class="quick-action-btn">
                <i class="bi bi-people"></i>
                <span>Переглянути користувачів</span>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Останні новини</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $result = $conn->query("SELECT id, title_ua, created_at, is_published FROM news ORDER BY created_at DESC LIMIT 5");
                    if ($result && $result->num_rows > 0) {
                        echo '<div class="list-group list-group-flush">';
                        while ($news = $result->fetch_assoc()) {
                            $badge = $news['is_published'] ? '<span class="badge bg-success">Опубліковано</span>' : '<span class="badge bg-warning">Чернетка</span>';
                            $date = date('d.m.Y H:i', strtotime($news['created_at']));
                            echo '<div class="list-group-item d-flex justify-content-between align-items-start">';
                            echo '<div class="ms-2 me-auto">';
                            echo '<div class="fw-bold">' . htmlspecialchars($news['title_ua']) . '</div>';
                            echo '<small class="text-muted">' . $date . '</small>';
                            echo '</div>';
                            echo $badge;
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p class="text-muted mb-0">Немає новин</p>';
                    }
                } catch (Exception $e) {
                    echo '<p class="text-danger">Помилка завантаження новин</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Нові користувачі</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $result = $conn->query("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
                    if ($result && $result->num_rows > 0) {
                        echo '<div class="list-group list-group-flush">';
                        while ($user = $result->fetch_assoc()) {
                            $date = date('d.m.Y H:i', strtotime($user['created_at']));
                            echo '<div class="list-group-item">';
                            echo '<div class="d-flex w-100 justify-content-between">';
                            echo '<h6 class="mb-1">' . htmlspecialchars($user['name'] ?? $user['email']) . '</h6>';
                            echo '<small class="text-muted">' . $date . '</small>';
                            echo '</div>';
                            echo '<small class="text-muted">' . htmlspecialchars($user['email']) . '</small>';
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p class="text-muted mb-0">Немає користувачів</p>';
                    }
                } catch (Exception $e) {
                    echo '<p class="text-danger">Помилка завантаження користувачів</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
?>
