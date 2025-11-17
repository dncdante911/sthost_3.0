<?php
/**
 * StormHosting UA - API для получения одной новости
 * Файл: /api/news/get.php
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Определяем константу для работы с includes
define('SECURE_ACCESS', true);

// Подключение к БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Получаем ID новости
$newsId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($newsId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Невірний ID новини'
    ], JSON_UNESCAPED_UNICODE);
    exit;
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
        WHERE id = ? AND is_published = 1
    ");

    $stmt->execute([$newsId]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($news) {
        echo json_encode([
            'success' => true,
            'news' => [
                'id' => $news['id'],
                'title' => $news['title'],
                'content' => $news['content'],
                'image' => $news['image'],
                'created_at' => $news['created_at'],
                'is_featured' => (bool)$news['is_featured']
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Новину не знайдено'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    error_log('News API Error: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Помилка завантаження новини'
    ], JSON_UNESCAPED_UNICODE);
}
