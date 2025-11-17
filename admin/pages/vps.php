<?php
/**
 * StormHosting UA - Управление VPS/VDS
 * Файл: /admin/pages/vps.php
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-server me-2"></i>Тарифні плани VPS/VDS</h5>
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
                        <th>CPU</th>
                        <th>RAM</th>
                        <th>Диск</th>
                        <th>Ціна (міс)</th>
                        <th>Популярний</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM vps_plans ORDER BY price_monthly ASC");
                    if ($result && $result->num_rows > 0) {
                        while ($plan = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $plan['id'] . '</td>';
                            echo '<td><strong>' . htmlspecialchars($plan['name_ua']) . '</strong></td>';
                            echo '<td>' . $plan['cpu_cores'] . ' cores</td>';
                            echo '<td>' . $plan['ram_gb'] . ' GB</td>';
                            echo '<td>' . $plan['disk_gb'] . ' GB SSD</td>';
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
                        echo '<tr><td colspan="8" class="text-center text-muted">Немає планів VPS/VDS</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Управління VPS/VDS планами:</strong> Тут ви можете налаштовувати віртуальні та виділені сервери.
</div>

<?php $conn->close(); ?>
