<?php
/**
 * StormHosting UA - Управление доменами
 * Файл: /admin/pages/domains.php
 */

// Подключение к БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Получаем PDO подключение
try {
    $pdo = DatabaseConnection::getSiteConnection();
} catch (Exception $e) {
    die('Помилка підключення до бази даних.');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$domain_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
            case 'update':
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $zone = trim($_POST['zone']);
                $price_registration = (float)$_POST['price_registration'];
                $price_renewal = (float)$_POST['price_renewal'];
                $is_popular = isset($_POST['is_popular']) ? 1 : 0;
                $is_active = isset($_POST['is_active']) ? 1 : 0;

                // Валидация
                if (empty($zone)) {
                    $error_message = "Будь ласка, вкажіть доменну зону";
                } elseif ($price_registration <= 0 || $price_renewal <= 0) {
                    $error_message = "Ціни повинні бути більше 0";
                } else {
                    if ($_POST['action'] === 'create') {
                        $stmt = $pdo->prepare("INSERT INTO domain_zones (zone, price_registration, price_renewal, is_popular, is_active)
                                VALUES (?, ?, ?, ?, ?)");

                        if ($stmt->execute([$zone, $price_registration, $price_renewal, $is_popular, $is_active])) {
                            $success_message = "Доменну зону успішно створено!";
                            $action = 'list';
                        } else {
                            $error_message = "Помилка створення доменної зони";
                        }
                    } else {
                        $stmt = $pdo->prepare("UPDATE domain_zones SET
                                zone = ?,
                                price_registration = ?,
                                price_renewal = ?,
                                is_popular = ?,
                                is_active = ?
                                WHERE id = ?");

                        if ($stmt->execute([$zone, $price_registration, $price_renewal, $is_popular, $is_active, $id])) {
                            $success_message = "Доменну зону успішно оновлено!";
                            $action = 'list';
                        } else {
                            $error_message = "Помилка оновлення доменної зони";
                        }
                    }
                }
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM domain_zones WHERE id = ?");

                if ($stmt->execute([$id])) {
                    $success_message = "Доменну зону успішно видалено!";
                } else {
                    $error_message = "Помилка видалення доменної зони";
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
<!-- Список доменных зон -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-globe2 me-2"></i>Доменні зони</h5>
        <a href="?page=domains&action=create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Додати зону
        </a>
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
                        <th>Активна</th>
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
                            echo '<td><strong>.' . htmlspecialchars($domain['zone']) . '</strong></td>';
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
                            if (isset($domain['is_active']) && $domain['is_active']) {
                                echo '<span class="badge bg-success">Так</span>';
                            } else {
                                echo '<span class="badge bg-secondary">Ні</span>';
                            }
                            echo '</td>';
                            echo '<td>';
                            echo '<div class="btn-group btn-group-sm">';
                            echo '<a href="?page=domains&action=edit&id=' . $domain['id'] . '" class="btn btn-outline-primary">';
                            echo '<i class="bi bi-pencil"></i>';
                            echo '</a>';
                            echo '<button type="button" class="btn btn-outline-danger" onclick="deleteDomain(' . $domain['id'] . ')">';
                            echo '<i class="bi bi-trash"></i>';
                            echo '</button>';
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center text-muted">Немає доменних зон</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Форма удаления (скрытая) -->
<form id="deleteDomainForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteDomainId">
</form>

<script>
function deleteDomain(id) {
    if (confirm('Ви впевнені, що хочете видалити цю доменну зону?')) {
        document.getElementById('deleteDomainId').value = id;
        document.getElementById('deleteDomainForm').submit();
    }
}
</script>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Управління доменними зонами:</strong> Тут ви можете додавати, редагувати та видаляти доменні зони, які доступні для реєстрації.
</div>

<?php elseif ($action === 'create' || $action === 'edit'): ?>
<!-- Форма создания/редактирования -->
<?php
$domain_data = [
    'id' => 0,
    'zone' => '',
    'price_registration' => 0,
    'price_renewal' => 0,
    'is_popular' => 0,
    'is_active' => 1
];

if ($action === 'edit' && $domain_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM domain_zones WHERE id = ?");
    $stmt->execute([$domain_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $domain_data = $result;
    } else {
        echo '<div class="alert alert-danger">Доменну зону не знайдено!</div>';
        $action = 'list';
    }
}
?>

<?php if ($action !== 'list'): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-<?php echo $action === 'create' ? 'plus-circle' : 'pencil'; ?> me-2"></i>
            <?php echo $action === 'create' ? 'Додати доменну зону' : 'Редагувати доменну зону'; ?>
        </h5>
    </div>

    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?php echo $action === 'create' ? 'create' : 'update'; ?>">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $domain_data['id']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="zone" class="form-label">Доменна зона *</label>
                    <div class="input-group">
                        <span class="input-group-text">.</span>
                        <input type="text" class="form-control" id="zone" name="zone"
                               value="<?php echo htmlspecialchars($domain_data['zone']); ?>"
                               placeholder="ua, com, net" required>
                    </div>
                    <small class="form-text text-muted">Без крапки на початку</small>
                </div>

                <div class="col-md-4">
                    <label for="price_registration" class="form-label">Ціна реєстрації (грн) *</label>
                    <input type="number" class="form-control" id="price_registration" name="price_registration"
                           value="<?php echo $domain_data['price_registration']; ?>"
                           step="0.01" min="0" required>
                </div>

                <div class="col-md-4">
                    <label for="price_renewal" class="form-label">Ціна продовження (грн) *</label>
                    <input type="number" class="form-control" id="price_renewal" name="price_renewal"
                           value="<?php echo $domain_data['price_renewal']; ?>"
                           step="0.01" min="0" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular"
                               <?php echo $domain_data['is_popular'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_popular">
                            Популярна зона (показувати першою)
                        </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               <?php echo (isset($domain_data['is_active']) && $domain_data['is_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Активна (доступна для реєстрації)
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>
                    <?php echo $action === 'create' ? 'Створити' : 'Зберегти'; ?>
                </button>
                <a href="?page=domains" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Скасувати
                </a>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info mt-3">
    <i class="bi bi-lightbulb me-2"></i>
    <strong>Підказка:</strong> Популярні доменні зони відображаються першими на сайті.
    Використовуйте цю опцію для найпопулярніших зон, таких як .ua, .com, .net.
</div>
<?php endif; ?>

<?php endif; ?>
