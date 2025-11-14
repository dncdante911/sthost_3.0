<?php
// Защита от прямого доступа
define('SECURE_ACCESS', true);

// Получить код ошибки
$errorCode = isset($_GET['code']) ? (int)$_GET['code'] : 404;

// Информация об ошибках
$errors = [
    400 => [
        'title' => 'Неправильний запит',
        'message' => 'Ваш браузер надіслав запит, який сервер не може зрозуміти.',
        'icon' => 'bi-exclamation-triangle'
    ],
    401 => [
        'title' => 'Необхідна авторизація',
        'message' => 'Для доступу до цієї сторінки необхідно авторизуватися.',
        'icon' => 'bi-lock'
    ],
    403 => [
        'title' => 'Доступ заборонено',
        'message' => 'У вас немає прав доступу до цієї сторінки.',
        'icon' => 'bi-shield-x'
    ],
    404 => [
        'title' => 'Сторінка не знайдена',
        'message' => 'На жаль, запитувана сторінка не існує або була переміщена.',
        'icon' => 'bi-file-earmark-x'
    ],
    500 => [
        'title' => 'Внутрішня помилка сервера',
        'message' => 'Виникла помилка на сервері. Ми вже працюємо над її усуненням.',
        'icon' => 'bi-exclamation-octagon'
    ],
    502 => [
        'title' => 'Помилка шлюзу',
        'message' => 'Сервер отримав неправильну відповідь від вищестоящого сервера.',
        'icon' => 'bi-server'
    ],
    503 => [
        'title' => 'Сервіс недоступний',
        'message' => 'Сервер тимчасово недоступний через технічне обслуговування або перевантаження.',
        'icon' => 'bi-hourglass-split'
    ]
];

$error = isset($errors[$errorCode]) ? $errors[$errorCode] : $errors[404];

// Конфигурация страницы
$page = 'error-' . $errorCode;
$page_title = 'Помилка ' . $errorCode . ' - ' . $error['title'];
$meta_description = $error['message'];

// Подключение конфигурации и БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Подключение header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Установить правильный HTTP код
http_response_code($errorCode);
?>

<style>
.error-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 6rem 0;
    min-height: 60vh;
    display: flex;
    align-items: center;
}

.error-code {
    font-size: 8rem;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.3);
    line-height: 1;
    margin-bottom: 1rem;
}

.error-icon {
    font-size: 4rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 2rem;
}

.error-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    padding: 3rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: 0 auto;
}

.error-title {
    color: #343a40;
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 1rem;
}

.error-message {
    color: #6c757d;
    font-size: 1.125rem;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.error-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

.btn-home {
    background: #007bff;
    color: white;
    padding: 0.875rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-home:hover {
    background: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: white;
}

.btn-back {
    background: transparent;
    color: #007bff;
    padding: 0.875rem 2rem;
    border: 2px solid #007bff;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: #007bff;
    color: white;
    text-decoration: none;
}

@media (max-width: 768px) {
    .error-code {
        font-size: 5rem;
    }

    .error-icon {
        font-size: 3rem;
    }

    .error-card {
        padding: 2rem 1.5rem;
    }

    .error-title {
        font-size: 1.5rem;
    }

    .error-message {
        font-size: 1rem;
    }

    .error-actions {
        flex-direction: column;
    }

    .btn-home,
    .btn-back {
        width: 100%;
        justify-content: center;
    }
}
</style>

<section class="error-hero">
    <div class="container">
        <div class="text-center">
            <div class="error-code"><?= $errorCode ?></div>
            <div class="error-card">
                <i class="<?= $error['icon'] ?> error-icon"></i>
                <h1 class="error-title"><?= $error['title'] ?></h1>
                <p class="error-message"><?= $error['message'] ?></p>

                <div class="error-actions">
                    <a href="/" class="btn-home">
                        <i class="bi bi-house"></i>
                        Головна сторінка
                    </a>
                    <a href="javascript:history.back()" class="btn-back">
                        <i class="bi bi-arrow-left"></i>
                        Повернутися назад
                    </a>
                </div>

                <?php if ($errorCode == 404): ?>
                <div class="mt-4">
                    <p class="text-muted mb-2" style="font-size: 0.9rem;">
                        Можливо, вас зацікавить:
                    </p>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="/hosting/vps" class="btn btn-sm btn-outline-primary">VPS хостинг</a>
                        <a href="/hosting/cloud" class="btn btn-sm btn-outline-primary">Cloud хостинг</a>
                        <a href="/hosting/domains" class="btn btn-sm btn-outline-primary">Домени</a>
                        <a href="/contacts" class="btn btn-sm btn-outline-primary">Контакти</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
