<?php
/**
 * CSRF Token API Endpoint
 * Генерирует и возвращает CSRF токен
 */

// Подключаем CSRF класс
require_once __DIR__ . '/../includes/csrf.php';

// Устанавливаем заголовки
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Генерируем токен
    $token = CSRF::generateToken();
    
    // Возвращаем успешный ответ
    echo json_encode([
        'success' => true,
        'token' => $token,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to generate CSRF token',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
