<?php
/**
 * API для получения статуса серверов
 * Endpoint: /api/monitoring/status.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Подключаем главный класс мониторинга
require_once __DIR__ . '/../../includes/monitoring/ServerMonitor.php';

try {
    // Получаем параметры запроса
    $action = $_GET['action'] ?? 'all';
    $type = $_GET['type'] ?? null;
    $id = $_GET['id'] ?? null;
    $format = $_GET['format'] ?? 'full'; // full, simple

    // Создаем экземпляр монитора
    $monitor = new ServerMonitor();

    $response = ['success' => true];

    switch ($action) {
        case 'all':
            // Получить статус всех серверов
            if ($format === 'simple') {
                $response['data'] = $monitor->getSimpleStatus();
            } else {
                $response['data'] = $monitor->getAllServersStatus();
            }
            break;

        case 'server':
            // Получить статус конкретного сервера
            if (!$type || !$id) {
                throw new Exception('Type and ID parameters are required');
            }

            $response['data'] = $monitor->getServerStatus($type, $id);
            break;

        case 'alerts':
            // Получить алерты
            $response['data'] = $monitor->checkAlerts();
            break;

        case 'clear-cache':
            // Очистить кеш (только для админов)
            $monitor->clearCache();
            $response['message'] = 'Cache cleared successfully';
            break;

        default:
            throw new Exception('Invalid action');
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ], JSON_PRETTY_PRINT);
}
