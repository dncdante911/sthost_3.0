<?php
/**
 * StormHosting UA - Страница входа в админ-панель
 * Файл: /admin/login.php
 */

// Определяем константу для работы с includes
define('SECURE_ACCESS', true);

session_start();

// Если уже авторизован - перенаправляем в админку
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/index.php');
    exit;
}

// Подключение к БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

$error_message = '';
$success_message = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = 'Будь ласка, заповніть всі поля';
    } else {
        // Проверяем пользователя в БД
        $stmt = $conn->prepare("SELECT id, username, password, role, is_active FROM admin_users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // Проверяем активность аккаунта
            if (!$admin['is_active']) {
                $error_message = 'Ваш обліковий запис деактивовано';
            } else {
                // Проверяем пароль
                if (password_verify($password, $admin['password'])) {
                    // Успешная авторизация
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'];

                    // Обновляем время последнего входа
                    $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                    $update_stmt->bind_param('i', $admin['id']);
                    $update_stmt->execute();
                    $update_stmt->close();

                    // Перенаправляем в админку
                    header('Location: /admin/index.php');
                    exit;
                } else {
                    $error_message = 'Невірний логін або пароль';
                }
            }
        } else {
            $error_message = 'Невірний логін або пароль';
        }

        $stmt->close();
    }
}

// Создание таблицы admin_users если её нет
$create_table_sql = "
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'moderator', 'publisher') NOT NULL DEFAULT 'publisher',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL,
  KEY `idx_username` (`username`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$conn->query($create_table_sql);

// Проверяем есть ли хоть один админ, если нет - создаём дефолтного
$check_admin = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE role = 'admin'");
$admin_exists = $check_admin->fetch_assoc()['count'] > 0;

if (!$admin_exists) {
    // Создаём дефолтного админа
    // Логин: admin, Пароль: admin123 (ОБЯЗАТЕЛЬНО ИЗМЕНИТЬ ПОСЛЕ ВХОДА!)
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin_users (username, password, email, role) VALUES ('admin', '$default_password', 'admin@stormhosting.ua', 'admin')");

    $success_message = 'Створено обліковий запис за замовчуванням. Логін: <strong>admin</strong>, Пароль: <strong>admin123</strong><br><strong class="text-danger">ОБОВ\'ЯЗКОВО ЗМІНІТЬ ПАРОЛЬ ПІСЛЯ ВХОДУ!</strong>';
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в адмін-панель - StormHosting UA</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 15px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(45deg, #FFD700, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .login-logo p {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .input-group-text {
            background: transparent;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .input-group .form-control:focus {
            border-left: none;
        }

        .back-to-site {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-to-site a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }

        .back-to-site a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <h1><i class="bi bi-lightning-charge"></i> StormHosting UA</h1>
            <p>Адміністративна панель</p>
        </div>

        <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Логін</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Введіть логін" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Пароль</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Введіть пароль" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Увійти
            </button>
        </form>
    </div>

    <div class="back-to-site">
        <a href="/">
            <i class="bi bi-arrow-left me-1"></i>
            Повернутися на сайт
        </a>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
