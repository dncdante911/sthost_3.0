<?php
/**
 * StormHosting UA - Управление хостингом
 * Файл: /admin/pages/hosting.php
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Получаем PDO подключение
try {
    $pdo = DatabaseConnection::getSiteConnection();
} catch (Exception $e) {
    die('Помилка підключення до бази даних.');
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-hdd-stack me-2"></i>Тарифні плани хостингу</h5>
        <button class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Додати план
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Назва</th>
                        <th>Дисковий простір</th>
                        <th>Трафік</th>
                        <th>БД</th>
                        <th>Ціна (міс)</th>
                        <th>Популярний</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $pdo->query("SELECT * FROM hosting_plans ORDER BY price_monthly ASC");
                    if ($result && $result->rowCount() > 0) {
                        while ($plan = $result->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . $plan['id'] . '</td>';
                            echo '<td><strong>' . htmlspecialchars($plan['name_ua']) . '</strong></td>';
                            echo '<td>' . ($plan['disk_space']/1024) . ' GB</td>';
                            echo '<td>' . ($plan['bandwidth'] == 0 ? 'Безліміт' : $plan['bandwidth'] . ' GB') . '</td>';
                            echo '<td>' . $plan['databases'] . '</td>';
                            echo '<td>' . number_format($plan['price_monthly'], 2) . ' грн</td>';
                            echo '<td>';
                            if ($plan['is_popular']) {
                                echo '<span class="badge bg-warning">Популярний</span>';
                            } else {
                                echo '<span class="badge bg-secondary">-</span>';
                            }
                            echo '</td>';
                            echo '<td>';
                            echo '<div class="btn-group btn-group-sm">';
                            echo '<button class="btn btn-outline-primary"><i class="bi bi-pencil"></i></button>';
                            echo '<button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>';
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="8" class="text-center text-muted">Немає планів хостингу</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Управління тарифними планами:</strong> Тут ви можете створювати та редагувати тарифи хостингу для ваших клієнтів.
</div>
