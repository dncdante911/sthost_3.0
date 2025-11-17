<?php
/**
 * StormHosting UA - API для получения списка новостей
 * Файл: /api/news/list.php
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Определяем константу для работы с includes
define('SECURE_ACCESS', true);

// Подключение к БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Получаем параметры
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Ограничиваем максимальное количество
if ($limit > 100) {
    $limit = 100;
}

try {
    // Получаем PDO подключение
    $pdo = DatabaseConnection::getSiteConnection();

    // Подготавливаем запрос
    $stmt = $pdo->prepare("
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
        LIMIT :limit OFFSET :offset
    ");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $news = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM news WHERE is_published = 1");
    $countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
    $total = $countRow['total'];

    echo json_encode([
        'success' => true,
        'news' => $news,
        'total' => (int)$total,
        'limit' => $limit,
        'offset' => $offset
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('News List API Error: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Помилка завантаження новин',
        'news' => []
    ], JSON_UNESCAPED_UNICODE);
}
