<?php
/**
 * CSRF Protection Class
 * Защита от Cross-Site Request Forgery атак
 */

class CSRF {
    /**
     * Генерация CSRF токена
     * @return string
     */
    public static function generateToken() {
        // Стартуем сессию если еще не запущена
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Генерируем новый токен если его нет
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        } else {
            // Проверяем срок действия токена (1 час)
            $lifetime = $_ENV['CSRF_TOKEN_LIFETIME'] ?? 3600;
            if ((time() - $_SESSION['csrf_token_time']) > $lifetime) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['csrf_token_time'] = time();
            }
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Валидация CSRF токена
     * @param string $token
     * @return bool
     */
    public static function validateToken($token) {
        // Стартуем сессию если еще не запущена
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Проверяем наличие токена в сессии
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Проверяем срок действия токена
        if (isset($_SESSION['csrf_token_time'])) {
            $lifetime = $_ENV['CSRF_TOKEN_LIFETIME'] ?? 3600;
            if ((time() - $_SESSION['csrf_token_time']) > $lifetime) {
                return false;
            }
        }

        // Используем hash_equals для защиты от timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Получение HTML кода для скрытого поля с токеном
     * @return string
     */
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Проверка токена из запроса
     * @param string $method Request method (POST, GET, etc.)
     * @return bool
     */
    public static function verifyRequest($method = 'POST') {
        $token = '';

        if ($method === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
        } elseif ($method === 'GET') {
            $token = $_GET['csrf_token'] ?? '';
        }

        // Проверяем также заголовки для AJAX запросов
        if (empty($token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        return self::validateToken($token);
    }

    /**
     * Middleware для автоматической проверки CSRF токена
     */
    public static function protect($method = 'POST') {
        if (!self::verifyRequest($method)) {
            http_response_code(403);
            die(json_encode([
                'success' => false,
                'error' => 'CSRF token validation failed',
                'message' => 'Недійсний або застарілий токен безпеки.'
            ]));
        }
    }

    public static function clearToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }

    public static function refreshToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
        return $_SESSION['csrf_token'];
    }
}

// Helper функции
function csrf_token() {
    return CSRF::generateToken();
}

function csrf_field() {
    return CSRF::getTokenField();
}

function verify_csrf($method = 'POST') {
    return CSRF::verifyRequest($method);
}
