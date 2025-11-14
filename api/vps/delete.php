<?php
/**
 * API для удаления VPS
 * Файл: /api/vps/delete.php
 */

define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth/check_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/classes/ProxmoxManager.php';

header('Content-Type: application/json; charset=utf-8');

// Проверка авторизации
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

// Только POST запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Тільки POST запити']);
    exit;
}

$user_id = getUserId();

try {
    // Получаем JSON данные
    $input = json_decode(file_get_contents('php://input'), true);

    $vps_id = intval($input['vps_id'] ?? 0);

    if (!$vps_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Невірний ID VPS']);
        exit;
    }

    // Проверяем, принадлежит ли VPS пользователю
    try {
        $pdo = DatabaseConnection::getSiteConnection();
        $stmt = $pdo->prepare("
            SELECT id, user_id, proxmox_vmid, proxmox_node, hostname
            FROM vps_instances
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$vps_id, $user_id]);
        $vps = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vps) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'VPS не знайдено або немає доступу']);
            exit;
        }

        $proxmox_vmid = $vps['proxmox_vmid'];
        $proxmox_node = $vps['proxmox_node'];

        // Удаляем из Proxmox, если есть VMID
        if (!empty($proxmox_vmid)) {
            try {
                $proxmox = new ProxmoxManager();

                if ($proxmox->authenticate()) {
                    // Удаляем VM из Proxmox (метод deleteVPS внутри сам останавливает VM)
                    $result = $proxmox->deleteVPS($proxmox_vmid);

                    if (!$result['success']) {
                        error_log("Proxmox delete warning: " . ($result['message'] ?? 'Unknown error'));
                        // Продолжаем даже если не удалось удалить из Proxmox
                    }
                }
            } catch (Exception $e) {
                error_log('Proxmox delete error: ' . $e->getMessage());
                // Продолжаем удаление из БД даже при ошибке Proxmox
            }
        }

        // Удаляем из базы данных
        $stmt = $pdo->prepare("
            UPDATE vps_instances
            SET status = 'deleted',
                deleted_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$vps_id, $user_id]);

        // Логируем операцию
        try {
            $stmt = $pdo->prepare("
                INSERT INTO vps_operations_log
                (user_id, vps_id, operation, status, details, created_at)
                VALUES (?, ?, 'delete', 'success', 'VPS видалено користувачем', NOW())
            ");
            $stmt->execute([$user_id, $vps_id]);
        } catch (Exception $e) {
            error_log('VPS log error: ' . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'message' => 'VPS успішно видалено'
        ]);

    } catch (Exception $e) {
        error_log('VPS delete error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Помилка при видаленні VPS']);
    }

} catch (Exception $e) {
    error_log('VPS delete error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Помилка виконання запиту'
    ]);
}
?>
