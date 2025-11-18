<?php
/**
 * StormHosting UA - Управление хостингом
 * Файл: /admin/pages/hosting.php
 */

// Подключение к БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf.php';

// Получаем PDO подключение
try {
    $pdo = DatabaseConnection::getSiteConnection();
} catch (Exception $e) {
    die('Помилка підключення до бази даних.');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateOrDie();
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
            case 'update':
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $name_ua = trim($_POST['name_ua']);
                $disk_space = (int)$_POST['disk_space']; // в MB
                $bandwidth = (int)$_POST['bandwidth']; // в GB, 0 = безлимит
                $domains = (int)$_POST['domains'];
                $databases = (int)$_POST['databases'];
                $price_monthly = (float)$_POST['price_monthly'];
                $is_popular = isset($_POST['is_popular']) ? 1 : 0;
                $is_active = isset($_POST['is_active']) ? 1 : 0;

                // Валидация
                if (empty($name_ua)) {
                    $error_message = "Будь ласка, вкажіть назву тарифного плану";
                } elseif ($disk_space <= 0) {
                    $error_message = "Дисковий простір повинен бути більше 0";
                } elseif ($price_monthly <= 0) {
                    $error_message = "Ціна повинна бути більше 0";
                } else {
                    if ($_POST['action'] === 'create') {
                        $stmt = $pdo->prepare("INSERT INTO hosting_plans
                                (name_ua, disk_space, bandwidth, domains, databases, price_monthly, is_popular, is_active)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                        if ($stmt->execute([$name_ua, $disk_space, $bandwidth, $domains, $databases, $price_monthly, $is_popular, $is_active])) {
                            $success_message = "Тарифний план успішно створено!";
                            $action = 'list';
                        } else {
                            $error_message = "Помилка створення тарифного плану";
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE hosting_plans SET
                                name_ua = ?,
                                disk_space = ?,
                                bandwidth = ?,
                                domains = ?,
                                databases = ?,
                                price_monthly = ?,
                                is_popular = ?,
                                is_active = ?
                                WHERE id = ?");

                        if ($stmt->execute([$name_ua, $disk_space, $bandwidth, $domains, $databases, $price_monthly, $is_popular, $is_active, $id])) {
                            $success_message = "Тарифний план успішно оновлено!";
                            $action = 'list';
                        } else {
                            $error_message = "Помилка оновлення тарифного плану";
                        }
                    }
                }
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM hosting_plans WHERE id = ?");

                if ($stmt->execute([$id])) {
                    $success_message = "Тарифний план успішно видалено!";
                } else {
                    $error_message = "Помилка видалення тарифного плану";
                }
                $action = 'list';
                break;
        }
    }
}

// Показываем сообщения
if (isset($success_message)) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo '<i class="bi bi-check-circle me-2"></i>' . $success_message;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}

if (isset($error_message)) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo '<i class="bi bi-exclamation-triangle me-2"></i>' . $error_message;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}
?>

<?php if ($action === 'list'): ?>
<!-- Список тарифных планов хостинга -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-hdd-stack me-2"></i>Тарифні плани хостингу</h5>
        <a href="?page=hosting&action=create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Додати план
        </a>
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
                        <th>Домени</th>
                        <th>БД</th>
                        <th>Ціна (міс)</th>
                        <th>Популярний</th>
                        <th>Активний</th>
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
                            echo '<td>' . ($plan['domains'] == 0 ? 'Безліміт' : $plan['domains']) . '</td>';
                            echo '<td>' . ($plan['databases'] == 0 ? 'Безліміт' : $plan['databases']) . '</td>';
                            echo '<td>' . number_format($plan['price_monthly'], 2) . ' грн</td>';
                            echo '<td>';
                            if ($plan['is_popular']) {
                                echo '<span class="badge bg-warning">Популярний</span>';
                            } else {
                                echo '<span class="badge bg-secondary">-</span>';
                            }
                            echo '</td>';
                            echo '<td>';
                            if (isset($plan['is_active']) && $plan['is_active']) {
                                echo '<span class="badge bg-success">Так</span>';
                            } else {
                                echo '<span class="badge bg-secondary">Ні</span>';
                            }
                            echo '</td>';
                            echo '<td>';
                            echo '<div class="btn-group btn-group-sm">';
                            echo '<a href="?page=hosting&action=edit&id=' . $plan['id'] . '" class="btn btn-outline-primary">';
                            echo '<i class="bi bi-pencil"></i>';
                            echo '</a>';
                            echo '<button type="button" class="btn btn-outline-danger" onclick="deleteHostingPlan(' . $plan['id'] . ')">';
                            echo '<i class="bi bi-trash"></i>';
                            echo '</button>';
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="10" class="text-center text-muted">Немає планів хостингу</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Форма удаления (скрытая) -->
<form id="deleteHostingPlanForm" method="POST" style="display: none;">
    <?php echo CSRF::tokenField(); ?>
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteHostingPlanId">
</form>

<script>
function deleteHostingPlan(id) {
    if (confirm('Ви впевнені, що хочете видалити цей тарифний план?')) {
        document.getElementById('deleteHostingPlanId').value = id;
        document.getElementById('deleteHostingPlanForm').submit();
    }
}
</script>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Управління тарифними планами:</strong> Тут ви можете створювати та редагувати тарифи хостингу для ваших клієнтів.
</div>

<?php elseif ($action === 'create' || $action === 'edit'): ?>
<!-- Форма создания/редактирования -->
<?php
$plan_data = [
    'id' => 0,
    'name_ua' => '',
    'disk_space' => 0,
    'bandwidth' => 0,
    'domains' => 1,
    'databases' => 1,
    'price_monthly' => 0,
    'is_popular' => 0,
    'is_active' => 1
];

if ($action === 'edit' && $plan_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM hosting_plans WHERE id = ?");
    $stmt->execute([$plan_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $plan_data = $result;
    } else {
        echo '<div class="alert alert-danger">Тарифний план не знайдено!</div>';
        $action = 'list';
    }
}
?>

<?php if ($action !== 'list'): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-<?php echo $action === 'create' ? 'plus-circle' : 'pencil'; ?> me-2"></i>
            <?php echo $action === 'create' ? 'Додати тарифний план' : 'Редагувати тарифний план'; ?>
        </h5>
    </div>

    <div class="card-body">
        <form method="POST">
            <?php echo CSRF::tokenField(); ?>
            <input type="hidden" name="action" value="<?php echo $action === 'create' ? 'create' : 'update'; ?>">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $plan_data['id']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name_ua" class="form-label">Назва тарифного плану *</label>
                    <input type="text" class="form-control" id="name_ua" name="name_ua"
                           value="<?php echo htmlspecialchars($plan_data['name_ua']); ?>"
                           placeholder="Наприклад: Starter, Business, Premium" required>
                </div>

                <div class="col-md-6">
                    <label for="price_monthly" class="form-label">Ціна за місяць (грн) *</label>
                    <input type="number" class="form-control" id="price_monthly" name="price_monthly"
                           value="<?php echo $plan_data['price_monthly']; ?>"
                           step="0.01" min="0" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="disk_space" class="form-label">Дисковий простір (MB) *</label>
                    <input type="number" class="form-control" id="disk_space" name="disk_space"
                           value="<?php echo $plan_data['disk_space']; ?>"
                           min="0" required>
                    <small class="form-text text-muted">1 GB = 1024 MB</small>
                </div>

                <div class="col-md-4">
                    <label for="bandwidth" class="form-label">Трафік (GB)</label>
                    <input type="number" class="form-control" id="bandwidth" name="bandwidth"
                           value="<?php echo $plan_data['bandwidth']; ?>"
                           min="0">
                    <small class="form-text text-muted">0 = Безліміт</small>
                </div>

                <div class="col-md-4">
                    <label for="domains" class="form-label">Кількість доменів</label>
                    <input type="number" class="form-control" id="domains" name="domains"
                           value="<?php echo $plan_data['domains']; ?>"
                           min="0">
                    <small class="form-text text-muted">0 = Безліміт</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="databases" class="form-label">Кількість баз даних</label>
                    <input type="number" class="form-control" id="databases" name="databases"
                           value="<?php echo $plan_data['databases']; ?>"
                           min="0">
                    <small class="form-text text-muted">0 = Безліміт</small>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular"
                               <?php echo $plan_data['is_popular'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_popular">
                            Популярний план
                        </label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               <?php echo (isset($plan_data['is_active']) && $plan_data['is_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Активний (доступний для покупки)
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>
                    <?php echo $action === 'create' ? 'Створити' : 'Зберегти'; ?>
                </button>
                <a href="?page=hosting" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Скасувати
                </a>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info mt-3">
    <i class="bi bi-lightbulb me-2"></i>
    <strong>Підказка:</strong> Популярний план буде виділений на сайті та відображатиметься першим у списку.
    Для необмеженого значення вкажіть 0.
</div>
<?php endif; ?>

<?php endif; ?>
