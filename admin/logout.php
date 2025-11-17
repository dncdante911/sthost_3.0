<?php
/**
 * StormHosting UA - Выход из админ-панели
 * Файл: /admin/logout.php
 */

// Определяем константу для работы с includes
define('SECURE_ACCESS', true);

session_start();

// Удаляем все данные сессии
$_SESSION = array();

// Удаляем cookie сессии
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на страницу входа
header('Location: /admin/login.php');
exit;
?>
