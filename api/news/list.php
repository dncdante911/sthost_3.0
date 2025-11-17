<?php
/**
 * StormHosting UA - API для получения списка новостей
 * Файл: /api/news/list.php
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Подключение к БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Получаем параметры
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Ограничиваем максимальное количество
if ($limit > 100) {
    $limit = 100;
}

try {
    // Подготавливаем запрос
    $stmt = $conn->prepare("
        SELECT
            id,
            title_ua as title,
            content_ua as content,
            image,
            created_at,
            is_featured
        FROM news
        WHERE is_published = 1
        ORDER BY is_featured DESC, created_at DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $news = [];
    while ($row = $result->fetch_assoc()) {
        $news[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'image' => $row['image'],
            'created_at' => $row['created_at'],
            'is_featured' => (bool)$row['is_featured']
        ];
    }

    // Получаем общее количество новостей
    $countResult = $conn->query("SELECT COUNT(*) as total FROM news WHERE is_published = 1");
    $total = $countResult->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'news' => $news,
        'total' => (int)$total,
        'limit' => $limit,
        'offset' => $offset
    ], JSON_UNESCAPED_UNICODE);

    $stmt->close();
} catch (Exception $e) {
    error_log('News List API Error: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Помилка завантаження новин',
        'news' => []
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
