<?php
/**
 * Proxmox VE 9 Manager
 * Класс для управления VPS через Proxmox VE 9 API
 * Файл: /includes/classes/ProxmoxManager.php
 */

// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

class ProxmoxManager {
    private $host;
    private $port;
    private $username;
    private $realm;
    private $password;
    private $token_id;
    private $token_secret;
    private $verify_ssl;
    private $ticket = null;
    private $csrf_token = null;
    private $node = 'pve'; // Default node name

    /**
     * Конструктор
     * @param array $config Конфигурация подключения
     */
    public function __construct($config = []) {
        $this->host = $config['host'] ?? PROXMOX_HOST ?? 'localhost';
        $this->port = $config['port'] ?? PROXMOX_PORT ?? 8006;
        $this->username = $config['username'] ?? PROXMOX_USER ?? 'root';
        $this->realm = $config['realm'] ?? PROXMOX_REALM ?? 'pam';
        $this->password = $config['password'] ?? PROXMOX_PASSWORD ?? '';
        $this->token_id = $config['token_id'] ?? PROXMOX_TOKEN_ID ?? null;
        $this->token_secret = $config['token_secret'] ?? PROXMOX_TOKEN_SECRET ?? null;
        $this->verify_ssl = $config['verify_ssl'] ?? PROXMOX_VERIFY_SSL ?? false;
        $this->node = $config['node'] ?? PROXMOX_NODE ?? 'pve';
    }

    /**
     * Аутентификация в Proxmox
     */
    public function authenticate() {
        try {
            // Если используются API токены, не требуется аутентификация
            if ($this->token_id && $this->token_secret) {
                return true;
            }

            $url = "https://{$this->host}:{$this->port}/api2/json/access/ticket";
            $data = [
                'username' => "{$this->username}@{$this->realm}",
                'password' => $this->password
            ];

            $response = $this->makeRequest('POST', $url, $data, false);

            if (isset($response['data']['ticket']) && isset($response['data']['CSRFPreventionToken'])) {
                $this->ticket = $response['data']['ticket'];
                $this->csrf_token = $response['data']['CSRFPreventionToken'];
                return true;
            }

            throw new Exception('Authentication failed: Invalid credentials');

        } catch (Exception $e) {
            error_log("Proxmox authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Выполнение HTTP запроса к Proxmox API
     */
    private function makeRequest($method, $url, $data = null, $auth_required = true) {
        $ch = curl_init();

        $headers = ['Content-Type: application/json'];

        // Добавляем заголовки аутентификации
        if ($auth_required) {
            if ($this->token_id && $this->token_secret) {
                // API Token authentication
                $headers[] = "Authorization: PVEAPIToken={$this->username}@{$this->realm}!{$this->token_id}={$this->token_secret}";
            } elseif ($this->ticket) {
                // Ticket authentication
                $headers[] = "Cookie: PVEAuthCookie={$this->ticket}";
                if ($method !== 'GET' && $this->csrf_token) {
                    $headers[] = "CSRFPreventionToken: {$this->csrf_token}";
                }
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verify_ssl ? 2 : 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL error: $error");
        }

        if ($http_code >= 400) {
            throw new Exception("HTTP error $http_code: $response");
        }

        return json_decode($response, true);
    }

    /**
     * Создание виртуальной машины
     */
    public function createVPS($config) {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            // Получаем следующий свободный VMID
            $vmid = $config['vmid'] ?? $this->getNextVMID();

            // Подготавливаем данные для создания VM
            $vm_data = [
                'vmid' => $vmid,
                'name' => escapeshellarg($config['name']),
                'memory' => intval($config['memory']),
                'cores' => intval($config['cpu_cores']),
                'sockets' => 1,
                'ostype' => $this->getOSType($config['os_template']),
                'net0' => "virtio,bridge=vmbr0",
                'scsi0' => "local-lvm:" . intval($config['disk_size']),
                'boot' => 'order=scsi0',
                'agent' => 1
            ];

            // Если указан IP адрес
            if (isset($config['ip_address'])) {
                $vm_data['ipconfig0'] = "ip={$config['ip_address']}/24,gw={$config['gateway']}";
            }

            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu";
            $response = $this->makeRequest('POST', $url, $vm_data);

            if (isset($response['data'])) {
                // Клонируем из темплейта если указан
                if (isset($config['template_id'])) {
                    $this->cloneFromTemplate($vmid, $config['template_id'], $config['name']);
                }

                return [
                    'success' => true,
                    'vmid' => $vmid,
                    'domain_name' => $config['name'],
                    'ip_address' => $config['ip_address'] ?? null
                ];
            }

            return ['success' => false, 'error' => 'Failed to create VM'];

        } catch (Exception $e) {
            error_log("Proxmox create VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Управление состоянием VPS
     */
    public function controlVPS($vmid, $action) {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $vmid = intval($vmid);
            $action_map = [
                'start' => 'start',
                'stop' => 'shutdown',
                'force_stop' => 'stop',
                'restart' => 'reboot',
                'suspend' => 'suspend',
                'resume' => 'resume',
                'reset' => 'reset'
            ];

            if (!isset($action_map[$action])) {
                return ['success' => false, 'error' => 'Unknown action'];
            }

            $proxmox_action = $action_map[$action];
            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}/status/{$proxmox_action}";

            $response = $this->makeRequest('POST', $url);

            if (isset($response['data'])) {
                return ['success' => true, 'action' => $action, 'task' => $response['data']];
            }

            return ['success' => false, 'error' => 'Action failed'];

        } catch (Exception $e) {
            error_log("Proxmox control VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получение статуса VPS
     */
    public function getVPSStatus($vmid) {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $vmid = intval($vmid);
            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}/status/current";

            $response = $this->makeRequest('GET', $url);

            if (isset($response['data'])) {
                $data = $response['data'];

                // Маппинг статусов Proxmox на наши статусы
                $state_map = [
                    'running' => 'active',
                    'stopped' => 'stopped',
                    'paused' => 'suspended',
                ];

                return [
                    'success' => true,
                    'state' => $state_map[$data['status']] ?? $data['status'],
                    'status' => $data['status'],
                    'uptime' => $data['uptime'] ?? 0,
                    'max_memory' => ($data['maxmem'] ?? 0) / 1024 / 1024, // Bytes to MB
                    'memory' => ($data['mem'] ?? 0) / 1024 / 1024, // Bytes to MB
                    'cpu_count' => $data['cpus'] ?? 0,
                    'cpu_usage' => ($data['cpu'] ?? 0) * 100, // Percentage
                    'disk_used' => ($data['disk'] ?? 0) / 1024 / 1024 / 1024, // Bytes to GB
                    'disk_max' => ($data['maxdisk'] ?? 0) / 1024 / 1024 / 1024 // Bytes to GB
                ];
            }

            return ['success' => false, 'error' => 'VM not found'];

        } catch (Exception $e) {
            error_log("Proxmox get VPS status error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получение VNC информации
     */
    public function getVNCInfo($vmid) {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $vmid = intval($vmid);
            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}/vncproxy";

            $response = $this->makeRequest('POST', $url, ['websocket' => 1]);

            if (isset($response['data'])) {
                $data = $response['data'];
                return [
                    'success' => true,
                    'port' => $data['port'],
                    'ticket' => $data['ticket'],
                    'host' => $this->host,
                    'type' => 'vnc',
                    'upid' => $data['upid'] ?? null
                ];
            }

            return ['success' => false, 'error' => 'Failed to get VNC info'];

        } catch (Exception $e) {
            error_log("Proxmox get VNC info error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Переустановка VPS
     */
    public function reinstallVPS($vmid, $template_id) {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $vmid = intval($vmid);
            $template_id = intval($template_id);

            // Останавливаем VM
            $this->controlVPS($vmid, 'force_stop');
            sleep(3);

            // Удаляем диски
            $config = $this->getVMConfig($vmid);
            if (isset($config['scsi0'])) {
                $this->deleteDisk($vmid, 'scsi0');
            }

            // Клонируем из темплейта
            $result = $this->cloneFromTemplate($vmid, $template_id, "reinstalled-vm-{$vmid}");

            if ($result['success']) {
                // Запускаем VM
                $this->controlVPS($vmid, 'start');
                return ['success' => true, 'message' => 'VPS reinstalled successfully'];
            }

            return $result;

        } catch (Exception $e) {
            error_log("Proxmox reinstall VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Удаление VPS
     */
    public function deleteVPS($vmid) {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $vmid = intval($vmid);

            // Останавливаем VM если запущен
            $status = $this->getVPSStatus($vmid);
            if ($status['success'] && $status['status'] === 'running') {
                $this->controlVPS($vmid, 'force_stop');
                sleep(3);
            }

            // Удаляем VM
            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}";
            $response = $this->makeRequest('DELETE', $url);

            if (isset($response['data'])) {
                return ['success' => true, 'message' => 'VPS deleted successfully'];
            }

            return ['success' => false, 'error' => 'Failed to delete VM'];

        } catch (Exception $e) {
            error_log("Proxmox delete VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Создание снимка (snapshot)
     */
    public function createSnapshot($vmid, $snapshot_name, $description = '') {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $vmid = intval($vmid);
            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}/snapshot";

            $data = [
                'snapname' => escapeshellarg($snapshot_name),
                'description' => $description ?: "Backup snapshot created at " . date('Y-m-d H:i:s')
            ];

            $response = $this->makeRequest('POST', $url, $data);

            if (isset($response['data'])) {
                return ['success' => true, 'snapshot_name' => $snapshot_name];
            }

            return ['success' => false, 'error' => 'Failed to create snapshot'];

        } catch (Exception $e) {
            error_log("Proxmox create snapshot error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получение статистики использования ресурсов
     */
    public function getResourceUsage($vmid) {
        try {
            $status = $this->getVPSStatus($vmid);

            if (!$status['success']) {
                return $status;
            }

            $memory_usage = ($status['memory'] / $status['max_memory']) * 100;

            return [
                'success' => true,
                'cpu_usage' => round($status['cpu_usage'], 2),
                'memory_usage' => round($memory_usage, 2),
                'memory_used_mb' => round($status['memory'], 2),
                'memory_total_mb' => round($status['max_memory'], 2),
                'disk_usage_gb' => round($status['disk_used'], 2),
                'disk_total_gb' => round($status['disk_max'], 2),
                'uptime' => $status['uptime']
            ];

        } catch (Exception $e) {
            error_log("Proxmox get resource usage error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Изменение размера RAM
     */
    public function resizeRAM($vmid, $new_memory_mb) {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $vmid = intval($vmid);
            $new_memory_mb = intval($new_memory_mb);

            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}/config";
            $data = ['memory' => $new_memory_mb];

            $response = $this->makeRequest('PUT', $url, $data);

            if (isset($response['data'])) {
                return ['success' => true, 'new_memory_mb' => $new_memory_mb];
            }

            return ['success' => false, 'error' => 'Failed to resize RAM'];

        } catch (Exception $e) {
            error_log("Proxmox resize RAM error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получение списка всех VPS
     */
    public function listAllVPS() {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu";
            $response = $this->makeRequest('GET', $url);

            if (isset($response['data'])) {
                $vps_list = [];

                foreach ($response['data'] as $vm) {
                    $state_map = [
                        'running' => 'active',
                        'stopped' => 'stopped',
                        'paused' => 'suspended'
                    ];

                    $vps_list[] = [
                        'vmid' => $vm['vmid'],
                        'name' => $vm['name'],
                        'state' => $state_map[$vm['status']] ?? $vm['status'],
                        'status' => $vm['status'],
                        'memory_mb' => ($vm['maxmem'] ?? 0) / 1024 / 1024,
                        'cpu_cores' => $vm['cpus'] ?? 0,
                        'uptime' => $vm['uptime'] ?? 0
                    ];
                }

                return ['success' => true, 'vps_list' => $vps_list];
            }

            return ['success' => false, 'error' => 'Failed to get VM list'];

        } catch (Exception $e) {
            error_log("Proxmox list VPS error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========== ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ==========

    /**
     * Получение следующего свободного VMID
     */
    private function getNextVMID() {
        try {
            $url = "https://{$this->host}:{$this->port}/api2/json/cluster/nextid";
            $response = $this->makeRequest('GET', $url);

            if (isset($response['data'])) {
                return intval($response['data']);
            }

            return 100; // Fallback

        } catch (Exception $e) {
            error_log("Get next VMID error: " . $e->getMessage());
            return 100;
        }
    }

    /**
     * Определение типа ОС для Proxmox
     */
    private function getOSType($os_template) {
        $os_map = [
            'ubuntu' => 'l26',
            'debian' => 'l26',
            'centos' => 'l26',
            'rocky' => 'l26',
            'alma' => 'l26',
            'windows' => 'win11',
            'windows-2019' => 'win10',
            'windows-2022' => 'win11'
        ];

        foreach ($os_map as $key => $type) {
            if (stripos($os_template, $key) !== false) {
                return $type;
            }
        }

        return 'l26'; // Default Linux
    }

    /**
     * Клонирование из темплейта
     */
    private function cloneFromTemplate($new_vmid, $template_id, $name) {
        try {
            $template_id = intval($template_id);
            $new_vmid = intval($new_vmid);

            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$template_id}/clone";
            $data = [
                'newid' => $new_vmid,
                'name' => escapeshellarg($name),
                'full' => 1
            ];

            $response = $this->makeRequest('POST', $url, $data);

            if (isset($response['data'])) {
                return ['success' => true, 'vmid' => $new_vmid];
            }

            return ['success' => false, 'error' => 'Failed to clone template'];

        } catch (Exception $e) {
            error_log("Clone template error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получение конфигурации VM
     */
    private function getVMConfig($vmid) {
        try {
            $vmid = intval($vmid);
            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}/config";
            $response = $this->makeRequest('GET', $url);

            return $response['data'] ?? [];

        } catch (Exception $e) {
            error_log("Get VM config error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Удаление диска
     */
    private function deleteDisk($vmid, $disk_name) {
        try {
            $vmid = intval($vmid);
            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}/config";
            $data = [
                'delete' => $disk_name
            ];

            $response = $this->makeRequest('PUT', $url, $data);
            return isset($response['data']);

        } catch (Exception $e) {
            error_log("Delete disk error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получение списка темплейтов
     */
    public function getTemplates() {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu";
            $response = $this->makeRequest('GET', $url);

            if (isset($response['data'])) {
                $templates = [];

                foreach ($response['data'] as $vm) {
                    // Проверяем, является ли VM темплейтом
                    if (isset($vm['template']) && $vm['template'] == 1) {
                        $templates[] = [
                            'vmid' => $vm['vmid'],
                            'name' => $vm['name'],
                            'memory_mb' => ($vm['maxmem'] ?? 0) / 1024 / 1024,
                            'disk_gb' => ($vm['maxdisk'] ?? 0) / 1024 / 1024 / 1024
                        ];
                    }
                }

                return ['success' => true, 'templates' => $templates];
            }

            return ['success' => false, 'error' => 'Failed to get templates'];

        } catch (Exception $e) {
            error_log("Get templates error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Изменение размера диска
     */
    public function resizeDisk($vmid, $disk, $size_gb) {
        try {
            if (!$this->ticket && !$this->token_id && !$this->authenticate()) {
                return ['success' => false, 'error' => 'Authentication failed'];
            }

            $vmid = intval($vmid);
            $size_gb = intval($size_gb);

            $url = "https://{$this->host}:{$this->port}/api2/json/nodes/{$this->node}/qemu/{$vmid}/resize";
            $data = [
                'disk' => $disk,
                'size' => "+{$size_gb}G" // Увеличение на указанный размер
            ];

            $response = $this->makeRequest('PUT', $url, $data);

            if (isset($response['data'])) {
                return ['success' => true, 'new_size_gb' => $size_gb];
            }

            return ['success' => false, 'error' => 'Failed to resize disk'];

        } catch (Exception $e) {
            error_log("Resize disk error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
