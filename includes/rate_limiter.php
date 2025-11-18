<?php
/**
 * Rate Limiter Class
 * SECURITY AUDIT FIX: Implements rate limiting for API endpoints and forms
 * Created: 2025-11-18
 *
 * Usage:
 * $limiter = new RateLimiter();
 * $limiter->checkLimit($identifier, $maxAttempts, $windowSeconds);
 */

class RateLimiter {
    private $storage;
    private $storageType;

    /**
     * Constructor - попытка использовать Redis, fallback to session storage
     */
    public function __construct() {
        // Попытка подключиться к Redis
        if (class_exists('Redis')) {
            try {
                $this->storage = new Redis();
                $this->storage->connect('127.0.0.1', 6379);
                $this->storage->ping();
                $this->storageType = 'redis';
            } catch (Exception $e) {
                error_log('Redis unavailable, falling back to session storage: ' . $e->getMessage());
                $this->storage = null;
                $this->storageType = 'session';
            }
        } else {
            $this->storage = null;
            $this->storageType = 'session';
        }

        // Убедимся что сессия запущена для fallback storage
        if ($this->storageType === 'session' && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Проверка лимита запросов
     *
     * @param string $identifier Уникальный идентификатор (IP, user_id, email и т.д.)
     * @param int $maxAttempts Максимальное количество попыток
     * @param int $windowSeconds Временное окно в секундах
     * @return bool True если лимит не превышен
     * @throws Exception если лимит превышен
     */
    public function checkLimit($identifier, $maxAttempts = 60, $windowSeconds = 60) {
        $key = "rate_limit:{$identifier}";

        if ($this->storageType === 'redis') {
            return $this->checkLimitRedis($key, $maxAttempts, $windowSeconds);
        } else {
            return $this->checkLimitSession($key, $maxAttempts, $windowSeconds);
        }
    }

    /**
     * Redis implementation
     */
    private function checkLimitRedis($key, $maxAttempts, $windowSeconds) {
        $current = $this->storage->incr($key);

        // Установить TTL при первом запросе
        if ($current === 1) {
            $this->storage->expire($key, $windowSeconds);
        }

        if ($current > $maxAttempts) {
            $ttl = $this->storage->ttl($key);
            $this->sendRateLimitResponse($ttl);
            return false;
        }

        return true;
    }

    /**
     * Session-based implementation (fallback)
     */
    private function checkLimitSession($key, $maxAttempts, $windowSeconds) {
        $currentTime = time();

        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }

        // Очистка старых записей
        $_SESSION['rate_limits'] = array_filter($_SESSION['rate_limits'], function($data) use ($currentTime) {
            return ($currentTime - $data['time']) < 3600; // Храним 1 час
        });

        if (!isset($_SESSION['rate_limits'][$key])) {
            $_SESSION['rate_limits'][$key] = [
                'count' => 1,
                'time' => $currentTime
            ];
            return true;
        }

        $rateData = $_SESSION['rate_limits'][$key];

        // Сброс если прошло больше времени чем window
        if ($currentTime - $rateData['time'] > $windowSeconds) {
            $_SESSION['rate_limits'][$key] = [
                'count' => 1,
                'time' => $currentTime
            ];
            return true;
        }

        // Проверка лимита
        if ($rateData['count'] >= $maxAttempts) {
            $remainingTime = $windowSeconds - ($currentTime - $rateData['time']);
            $this->sendRateLimitResponse($remainingTime);
            return false;
        }

        // Увеличить счетчик
        $_SESSION['rate_limits'][$key]['count']++;
        return true;
    }

    /**
     * Отправка ответа при превышении лимита
     */
    private function sendRateLimitResponse($retryAfter) {
        http_response_code(429);
        header('Retry-After: ' . $retryAfter);
        header('X-RateLimit-Limit: exceeded');
        header('X-RateLimit-Retry-After: ' . $retryAfter);

        // Проверка на AJAX запрос
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if ($isAjax || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)) {
            header('Content-Type: application/json');
            die(json_encode([
                'success' => false,
                'error' => 'rate_limit_exceeded',
                'message' => 'Too many requests. Please try again in ' . $retryAfter . ' seconds.',
                'retry_after' => $retryAfter
            ]));
        } else {
            die('Too many requests. Please try again in ' . $retryAfter . ' seconds.');
        }
    }

    /**
     * Получить текущий счетчик попыток
     *
     * @param string $identifier Идентификатор
     * @return int Количество попыток
     */
    public function getAttempts($identifier) {
        $key = "rate_limit:{$identifier}";

        if ($this->storageType === 'redis') {
            $count = $this->storage->get($key);
            return $count !== false ? (int)$count : 0;
        } else {
            if (!isset($_SESSION['rate_limits'][$key])) {
                return 0;
            }
            return (int)$_SESSION['rate_limits'][$key]['count'];
        }
    }

    /**
     * Сбросить счетчик попыток
     *
     * @param string $identifier Идентификатор
     * @return bool
     */
    public function resetAttempts($identifier) {
        $key = "rate_limit:{$identifier}";

        if ($this->storageType === 'redis') {
            return $this->storage->del($key) > 0;
        } else {
            if (isset($_SESSION['rate_limits'][$key])) {
                unset($_SESSION['rate_limits'][$key]);
                return true;
            }
            return false;
        }
    }

    /**
     * Проверка лимита без выброса исключения (для мягкой проверки)
     *
     * @param string $identifier Идентификатор
     * @param int $maxAttempts Максимальное количество попыток
     * @param int $windowSeconds Временное окно
     * @return bool True если лимит не превышен
     */
    public function isAllowed($identifier, $maxAttempts = 60, $windowSeconds = 60) {
        try {
            return $this->checkLimit($identifier, $maxAttempts, $windowSeconds);
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * Helper функция для быстрого использования
 *
 * @param string $identifier Идентификатор (IP, email и т.д.)
 * @param int $maxAttempts Максимум попыток
 * @param int $windowSeconds Окно времени в секундах
 * @return bool True если разрешено
 */
function checkRateLimit($identifier, $maxAttempts = 60, $windowSeconds = 60) {
    static $limiter = null;
    if ($limiter === null) {
        $limiter = new RateLimiter();
    }
    return $limiter->checkLimit($identifier, $maxAttempts, $windowSeconds);
}
?>
