<?php
/**
 * StormHosting UA - Утилита управления администраторами
 * Файл: /admin/manage-admins.php
 *
 * ВАЖНО: После создания первого админа - удалите этот файл или защитите его!
 */

define('SECURE_ACCESS', true);
session_start();

// БЕЗОПАСНОСТЬ: Проверка доступа
// Разрешаем доступ только если:
// 1. Нет ни одного админа в системе (первый запуск) ИЛИ
// 2. Пользователь авторизован как admin
$allow_access = false;

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Получаем PDO подключение
try {
    $pdo = DatabaseConnection::getSiteConnection();
} catch (Exception $e) {
    die('Помилка підключення до бази даних. Зверніться до адміністратора.');
}

// Проверяем есть ли хоть один админ
$check_admins = $pdo->query("SELECT COUNT(*) as count FROM admin_users WHERE role = 'admin'");
if ($check_admins) {
    $row = $check_admins->fetch(PDO::FETCH_ASSOC);
    $admin_count = $row['count'];

    if ($admin_count == 0) {
        // Нет админов - разрешаем доступ (первый запуск)
        $allow_access = true;
        $first_run = true;
    } else {
        // Есть админы - требуем авторизацию
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $_SESSION['admin_role'] === 'admin') {
            $allow_access = true;
            $first_run = false;
        }
    }
}

if (!$allow_access) {
    die('
    <!DOCTYPE html>
    <html lang="uk">
    <head>
        <meta charset="UTF-8">
        <title>Доступ заборонено</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
            .error { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>🔒 Доступ заборонено</h1>
            <p>Ця сторінка доступна тільки для адміністраторів.</p>
            <a href="/admin/login.php">Увійти в адмін-панель</a>
        </div>
    </body>
    </html>
    ');
}

$message = '';
$error = '';

// Обработка формы создания администратора
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    // Валидация
    if (empty($username) || empty($password) || empty($email)) {
        $error = 'Всі поля обов\'язкові для заповнення';
    } elseif ($password !== $password_confirm) {
        $error = 'Паролі не співпадають';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль повинен бути мінімум 6 символів';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Невірний формат email';
    } else {
        // Проверяем существует ли уже такой username
        $check_stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
        $check_stmt->execute([$username]);

        if ($check_stmt->rowCount() > 0) {
            $error = 'Користувач з таким логіном вже існує';
        } else {
            // Создаём администратора
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $created_by = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

            $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email, role) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$username, $password_hash, $email, $role])) {
                $message = "✅ Адміністратор <strong>$username</strong> успішно створений!";

                // Очищаем форму
                $_POST = array();
            } else {
                $error = 'Помилка створення адміністратора';
            }
        }
    }
}

// Обработка деактивации/активации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $admin_id = (int)$_POST['admin_id'];
    $new_status = (int)$_POST['new_status'];

    $stmt = $pdo->prepare("UPDATE admin_users SET is_active = ? WHERE id = ?");

    if ($stmt->execute([$new_status, $admin_id])) {
        $message = $new_status ? '✅ Адміністратор активований' : '⚠️ Адміністратор деактивований';
    }
}

// Получаем список всех админов
$admins = $pdo->query("SELECT * FROM admin_users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управління адміністраторами - StormHosting UA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; }
        .card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0 !important; }
        .badge { font-size: 0.85rem; }
        <?php if ($first_run): ?>
        .first-run-alert { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }
        <?php endif; ?>
    </style>
</head>
<body>

<div class="container">
    <?php if ($first_run): ?>
    <div class="alert alert-warning first-run-alert mb-4">
        <h4><i class="bi bi-exclamation-triangle me-2"></i>Перший запуск!</h4>
        <p class="mb-0">Створіть головного адміністратора. <strong>ВАЖЛИВО:</strong> Після створення першого адміна цей файл потрібно захистити або видалити!</p>
    </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Створити нового адміністратора</h4>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Логін *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Пароль * (мін. 6 символів)</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Підтвердження пароля *</label>
                        <input type="password" name="password_confirm" class="form-control" minlength="6" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Роль *</label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Адміністратор (повний доступ)</option>
                            <option value="moderator">Модератор (середній доступ)</option>
                            <option value="publisher">Публікатор (тільки новини)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="create_admin" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Створити адміністратора
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="mb-0"><i class="bi bi-people me-2"></i>Список адміністраторів</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логін</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Статус</th>
                            <th>Останній вхід</th>
                            <th>Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($admins && $admins->rowCount() > 0): ?>
                            <?php while ($admin = $admins->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $admin['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td>
                                    <?php
                                    $role_badges = [
                                        'admin' => 'danger',
                                        'moderator' => 'warning',
                                        'publisher' => 'info'
                                    ];
                                    $badge = $role_badges[$admin['role']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo ucfirst($admin['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($admin['is_active']): ?>
                                        <span class="badge bg-success">Активний</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Заблокований</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $admin['last_login'] ? date('d.m.Y H:i', strtotime($admin['last_login'])) : 'Ніколи'; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                        <input type="hidden" name="new_status" value="<?php echo $admin['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" name="toggle_active" class="btn btn-sm btn-<?php echo $admin['is_active'] ? 'warning' : 'success'; ?>"
                                                onclick="return confirm('Підтвердіть дію')">
                                            <i class="bi bi-<?php echo $admin['is_active'] ? 'lock' : 'unlock'; ?>"></i>
                                            <?php echo $admin['is_active'] ? 'Заблокувати' : 'Активувати'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Немає адміністраторів</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="/admin/index.php" class="btn btn-light">
            <i class="bi bi-arrow-left me-1"></i>Повернутися в адмін-панель
        </a>
    </div>

    <?php if ($first_run): ?>
    <div class="alert alert-danger mt-4">
        <h5><i class="bi bi-shield-exclamation me-2"></i>Безпека</h5>
        <p>Після створення першого адміністратора:</p>
        <ol>
            <li>Видаліть файл <code>/admin/manage-admins.php</code> або</li>
            <li>Додайте додаткову авторизацію через .htpasswd</li>
        </ol>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
