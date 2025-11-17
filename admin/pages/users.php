<?php
/**
 * StormHosting UA - Управление пользователями
 * Файл: /admin/pages/users.php
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Получаем PDO подключение
try {
    $pdo = DatabaseConnection::getSiteConnection();
} catch (Exception $e) {
    die('Помилка підключення до бази даних.');
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Користувачі системи</h5>
    </div>

    <div class="card-body">
        <!-- Фильтры и поиск -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Пошук по email або імені...">
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option value="">Всі ролі</option>
                    <option value="user">Користувач</option>
                    <option value="admin">Адміністратор</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option value="">Всі статуси</option>
                    <option value="active">Активний</option>
                    <option value="blocked">Заблокований</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Ім'я</th>
                        <th>Дата реєстрації</th>
                        <th>Статус</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 50");
                    if ($result && $result->rowCount() > 0) {
                        while ($user = $result->fetch(PDO::FETCH_ASSOC)) {
                            echo '<tr>';
                            echo '<td>' . $user['id'] . '</td>';
                            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['name'] ?? '-') . '</td>';
                            echo '<td>' . date('d.m.Y H:i', strtotime($user['created_at'])) . '</td>';
                            echo '<td><span class="badge bg-success">Активний</span></td>';
                            echo '<td>';
                            echo '<div class="btn-group btn-group-sm">';
                            echo '<button class="btn btn-outline-primary" title="Переглянути"><i class="bi bi-eye"></i></button>';
                            echo '<button class="btn btn-outline-warning" title="Редагувати"><i class="bi bi-pencil"></i></button>';
                            echo '<button class="btn btn-outline-danger" title="Заблокувати"><i class="bi bi-lock"></i></button>';
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center text-muted">Немає користувачів</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#">Попередня</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Наступна</a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Управління користувачами:</strong> Тут ви можете переглядати та керувати обліковими записами користувачів.
</div>
