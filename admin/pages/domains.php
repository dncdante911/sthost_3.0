<?php
/**
 * StormHosting UA - Управление доменами
 * Файл: /admin/pages/domains.php
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
        <h5 class="mb-0"><i class="bi bi-globe2 me-2"></i>Доменні зони</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDomainModal">
            <i class="bi bi-plus-circle me-1"></i>Додати зону
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Зона</th>
                        <th>Ціна реєстрації</th>
                        <th>Ціна продовження</th>
                        <th>Популярна</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $pdo->query("SELECT * FROM domain_zones ORDER BY zone ASC");
                    if ($result && $result->rowCount() > 0) {
                        while ($domain = $result->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . $domain['id'] . '</td>';
                            echo '<td><strong>' . htmlspecialchars($domain['zone']) . '</strong></td>';
                            echo '<td>' . number_format($domain['price_registration'], 2) . ' грн</td>';
                            echo '<td>' . number_format($domain['price_renewal'], 2) . ' грн</td>';
                            echo '<td>';
                            if ($domain['is_popular']) {
                                echo '<span class="badge bg-success">Так</span>';
                            } else {
                                echo '<span class="badge bg-secondary">Ні</span>';
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
                        echo '<tr><td colspan="6" class="text-center text-muted">Немає доменних зон</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Управління доменними зонами:</strong> Тут ви можете додавати, редагувати та видаляти доменні зони, які доступні для реєстрації.
</div>
