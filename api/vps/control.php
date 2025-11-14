<?php
/**
 * API для керування VPS (start/stop/restart)
 * Використовує Proxmox VE 9
 * Файл: /api/vps/control.php
 */

define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth/check_auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/classes/ProxmoxManager.php';

header('Content-Type: application/json; charset=utf-8');

// Перевірка авторізації
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

// Тільки POST запити
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Тільки POST запити']);
    exit;
}

$user_id = getUserId();

try {
    // Отримуємо JSON дані
    $input = json_decode(file_get_contents('php://input'), true);

    // CSRF Protection
    $csrf_token = $input['csrf_token'] ?? '';
    if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Невірний CSRF токен']);
        exit;
    }

    $server_id = intval($input['server_id'] ?? 0);
    $action = $input['action'] ?? '';

    if (!$server_id || !in_array($action, ['start', 'stop', 'restart', 'reboot', 'shutdown'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Невірні параметри']);
        exit;
    }

    // Перевіряємо чи користувач має доступ до цього VPS
    try {
        $pdo = DatabaseConnection::getSiteConnection();
        $stmt = $pdo->prepare("
            SELECT id, user_id, proxmox_vmid, proxmox_node, hostname, status
            FROM vps_instances
            WHERE id = ? AND user_id = ? AND status != 'deleted'
        ");
        $stmt->execute([$server_id, $user_id]);
        $vps = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vps) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Немає доступу до цього VPS або VPS не знайдено']);
            exit;
        }

        $proxmox_vmid = $vps['proxmox_vmid'];
        $proxmox_node = $vps['proxmox_node'];

        if (empty($proxmox_vmid)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'VPS не має Proxmox VMID']);
            exit;
        }

    } catch (Exception $e) {
        error_log('VPS access check error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Помилка перевірки доступу']);
        exit;
    }

    // Виконуємо дію через Proxmox
    try {
        $proxmox = new ProxmoxManager();

        if (!$proxmox->authenticate()) {
            throw new Exception('Не вдалося підключитися до Proxmox');
        }

        $result = $proxmox->controlVPS($proxmox_vmid, $action, $proxmox_node);

        if ($result['success']) {
            // Логуємо операцію
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO vps_operations_log
                    (user_id, vps_id, operation, status, details, created_at)
                    VALUES (?, ?, ?, 'success', ?, NOW())
                ");
                $stmt->execute([
                    $user_id,
                    $server_id,
                    $action,
                    $result['message'] ?? 'Операцію виконано'
                ]);
            } catch (Exception $e) {
                error_log('VPS log error: ' . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => $result['message'] ?? "Команда {$action} виконана успішно",
                'data' => $result['data'] ?? null
            ]);
        } else {
            // Логуємо помилку
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO vps_operations_log
                    (user_id, vps_id, operation, status, details, created_at)
                    VALUES (?, ?, ?, 'failed', ?, NOW())
                ");
                $stmt->execute([
                    $user_id,
                    $server_id,
                    $action,
                    $result['message'] ?? 'Невідома помилка'
                ]);
            } catch (Exception $e) {
                error_log('VPS log error: ' . $e->getMessage());
            }

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $result['message'] ?? 'Не вдалося виконати операцію'
            ]);
        }

    } catch (Exception $e) {
        error_log('Proxmox error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Помилка Proxmox: ' . $e->getMessage()
        ]);
    }

} catch (Exception $e) {
    error_log('VPS control error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Помилка виконання команди'
    ]);
}

/**
 * СТРУКТУРА ТАБЛИЦІ vps_operations_log:
 *
 * CREATE TABLE IF NOT EXISTS vps_operations_log (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     user_id INT NOT NULL,
 *     vps_id INT NOT NULL,
 *     operation VARCHAR(50) NOT NULL,
 *     status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
 *     details TEXT,
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     INDEX(user_id),
 *     INDEX(vps_id),
 *     INDEX(created_at)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 */
?>
