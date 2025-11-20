<?php
/**
 * API моніторингу серверів
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Підключаємо функції моніторингу
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/monitoring.php';

// Отримуємо статус
$data = get_server_status();

// Відповідь
echo json_encode([
    'success' => true,
    'cached' => false,
    'data' => $data
]);
