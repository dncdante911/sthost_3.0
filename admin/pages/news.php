<?php
/**
 * StormHosting UA - Управление новостями
 * Файл: /admin/pages/news.php
 */

// Подключение к БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
            case 'update':
                $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                $title_ua = $conn->real_escape_string($_POST['title_ua']);
                $content_ua = $conn->real_escape_string($_POST['content_ua']);
                $image = isset($_POST['image']) ? $conn->real_escape_string($_POST['image']) : '';
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_published = isset($_POST['is_published']) ? 1 : 0;

                if ($_POST['action'] === 'create') {
                    $sql = "INSERT INTO news (title_ua, content_ua, image, is_featured, is_published, created_at)
                            VALUES ('$title_ua', '$content_ua', '$image', $is_featured, $is_published, NOW())";

                    if ($conn->query($sql)) {
                        $success_message = "Новину успішно створено!";
                        $action = 'list';
                    } else {
                        $error_message = "Помилка створення новини: " . $conn->error;
                    }
                } else {
                    $sql = "UPDATE news SET
                            title_ua = '$title_ua',
                            content_ua = '$content_ua',
                            image = '$image',
                            is_featured = $is_featured,
                            is_published = $is_published,
                            updated_at = NOW()
                            WHERE id = $id";

                    if ($conn->query($sql)) {
                        $success_message = "Новину успішно оновлено!";
                        $action = 'list';
                    } else {
                        $error_message = "Помилка оновлення новини: " . $conn->error;
                    }
                }
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM news WHERE id = $id";

                if ($conn->query($sql)) {
                    $success_message = "Новину успішно видалено!";
                } else {
                    $error_message = "Помилка видалення новини: " . $conn->error;
                }
                $action = 'list';
                break;
        }
    }
}

// Показываем сообщения
if (isset($success_message)) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo '<i class="bi bi-check-circle me-2"></i>' . $success_message;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}

if (isset($error_message)) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    echo '<i class="bi bi-exclamation-triangle me-2"></i>' . $error_message;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}
?>

<?php if ($action === 'list'): ?>
<!-- Список новостей -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-newspaper me-2"></i>Всі новини</h5>
        <a href="?page=news&action=create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Додати новину
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заголовок</th>
                        <th>Зображення</th>
                        <th>Дата створення</th>
                        <th>Статус</th>
                        <th>Особлива</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
                    if ($result && $result->num_rows > 0) {
                        while ($news = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $news['id'] . '</td>';
                            echo '<td><strong>' . htmlspecialchars($news['title_ua']) . '</strong></td>';
                            echo '<td>';
                            if ($news['image']) {
                                echo '<img src="' . htmlspecialchars($news['image']) . '" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">';
                            } else {
                                echo '<span class="text-muted">Немає</span>';
                            }
                            echo '</td>';
                            echo '<td>' . date('d.m.Y H:i', strtotime($news['created_at'])) . '</td>';
                            echo '<td>';
                            if ($news['is_published']) {
                                echo '<span class="badge bg-success">Опубліковано</span>';
                            } else {
                                echo '<span class="badge bg-warning">Чернетка</span>';
                            }
                            echo '</td>';
                            echo '<td>';
                            if ($news['is_featured']) {
                                echo '<i class="bi bi-star-fill text-warning"></i>';
                            } else {
                                echo '<i class="bi bi-star text-muted"></i>';
                            }
                            echo '</td>';
                            echo '<td>';
                            echo '<div class="btn-group btn-group-sm">';
                            echo '<a href="?page=news&action=edit&id=' . $news['id'] . '" class="btn btn-outline-primary">';
                            echo '<i class="bi bi-pencil"></i>';
                            echo '</a>';
                            echo '<button type="button" class="btn btn-outline-danger" onclick="deleteNews(' . $news['id'] . ')">';
                            echo '<i class="bi bi-trash"></i>';
                            echo '</button>';
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center text-muted">Немає новин</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Форма удаления (скрытая) -->
<form id="deleteNewsForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteNewsId">
</form>

<script>
function deleteNews(id) {
    if (confirm('Ви впевнені, що хочете видалити цю новину?')) {
        document.getElementById('deleteNewsId').value = id;
        document.getElementById('deleteNewsForm').submit();
    }
}
</script>

<?php elseif ($action === 'create' || $action === 'edit'): ?>
<!-- Форма создания/редактирования -->
<?php
$news_data = [
    'id' => 0,
    'title_ua' => '',
    'content_ua' => '',
    'image' => '',
    'is_featured' => 0,
    'is_published' => 1
];

if ($action === 'edit' && $news_id > 0) {
    $result = $conn->query("SELECT * FROM news WHERE id = $news_id");
    if ($result && $result->num_rows > 0) {
        $news_data = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-danger">Новину не знайдено!</div>';
        $action = 'list';
    }
}
?>

<?php if ($action !== 'list'): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-<?php echo $action === 'create' ? 'plus-circle' : 'pencil'; ?> me-2"></i>
            <?php echo $action === 'create' ? 'Додати новину' : 'Редагувати новину'; ?>
        </h5>
    </div>

    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action === 'create' ? 'create' : 'update'; ?>">
            <?php if ($action === 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $news_data['id']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="title_ua" class="form-label">Заголовок (UA) *</label>
                    <input type="text" class="form-control" id="title_ua" name="title_ua" value="<?php echo htmlspecialchars($news_data['title_ua']); ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="image" class="form-label">URL зображення</label>
                    <input type="text" class="form-control" id="image" name="image" value="<?php echo htmlspecialchars($news_data['image']); ?>" placeholder="https://...">
                </div>
            </div>

            <div class="mb-3">
                <label for="content_ua" class="form-label">Контент (UA) *</label>
                <textarea class="form-control" id="content_ua" name="content_ua" rows="15" required><?php echo htmlspecialchars($news_data['content_ua']); ?></textarea>
                <small class="form-text text-muted">Можна використовувати HTML теги для форматування</small>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" <?php echo $news_data['is_published'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_published">
                            Опублікувати новину
                        </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" <?php echo $news_data['is_featured'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">
                            Особлива новина (показувати першою)
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>
                    <?php echo $action === 'create' ? 'Створити' : 'Зберегти'; ?>
                </button>
                <a href="?page=news" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Скасувати
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Предпросмотр зображення -->
<script>
document.getElementById('image').addEventListener('input', function() {
    const imageUrl = this.value;
    let preview = document.getElementById('imagePreview');

    if (!preview) {
        preview = document.createElement('div');
        preview.id = 'imagePreview';
        preview.className = 'mt-2';
        this.parentElement.appendChild(preview);
    }

    if (imageUrl) {
        preview.innerHTML = '<img src="' + imageUrl + '" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
    } else {
        preview.innerHTML = '';
    }
});

// Trigger на загрузку для показа превью существующего изображения
if (document.getElementById('image').value) {
    document.getElementById('image').dispatchEvent(new Event('input'));
}
</script>
<?php endif; ?>

<?php endif; ?>

<?php
$conn->close();
?>
