<?php
/**
 * Proxmox VE Monitor Class
 * Класс для мониторинга серверов Proxmox VE
 */

class ProxmoxMonitor {
    private $config;
    private $cache_file;
    private $cache_ttl;
    private $ticket;
    private $csrf_token;

    public function __construct($server_config, $cache_ttl = 60) {
        $this->config = $server_config;
        $this->cache_ttl = $cache_ttl;
        $this->cache_file = sys_get_temp_dir() . '/proxmox_' . $this->config['id'] . '.cache';
    }

    /**
     * Получить статус сервера
     */
    public function getServerStatus() {
        // Проверяем кеш
        $cached = $this->getCache();
        if ($cached !== null) {
            return $cached;
        }

        try {
            $status = [
                'id' => $this->config['id'],
                'name' => $this->config['name'],
                'type' => 'proxmox',
                'status' => 'unknown',
                'online' => false,
                'metrics' => [],
                'vms' => [],
                'timestamp' => time(),
            ];

            // Авторизуемся
            if (!$this->authenticate()) {
                $status['status'] = 'auth_failed';
                $this->saveCache($status);
                return $status;
            }

            // Проверяем доступность
            if (!$this->checkConnection()) {
                $status['status'] = 'offline';
                $this->saveCache($status);
                return $status;
            }

            // Получаем метрики
            $status['online'] = true;
            $status['status'] = 'online';
            $status['metrics'] = $this->getMetrics();
            $status['vms'] = $this->getVMsList();

            $this->saveCache($status);
            return $status;

        } catch (Exception $e) {
            error_log("Proxmox Monitor Error: " . $e->getMessage());
            return [
                'id' => $this->config['id'],
                'name' => $this->config['name'],
                'type' => 'proxmox',
                'status' => 'error',
                'online' => false,
                'error' => $e->getMessage(),
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Авторизация в Proxmox API
     */
    private function authenticate() {
        $url = $this->buildUrl('/api2/json/access/ticket');

        $post_data = [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'realm' => $this->config['realm'] ?? 'pam',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config['ssl_verify'] ?? false);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            return false;
        }

        $data = json_decode($response, true);

        if (isset($data['data']['ticket']) && isset($data['data']['CSRFPreventionToken'])) {
            $this->ticket = $data['data']['ticket'];
            $this->csrf_token = $data['data']['CSRFPreventionToken'];
            return true;
        }

        return false;
    }

    /**
     * Проверка подключения
     */
    private function checkConnection() {
        try {
            $data = $this->apiRequest('/api2/json/version');
            return isset($data['data']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Получить метрики ноды
     */
    private function getMetrics() {
        try {
            $node = $this->config['node'];
            $data = $this->apiRequest("/api2/json/nodes/$node/status");

            if (!isset($data['data'])) {
                return [];
            }

            $status = $data['data'];

            $cpu_usage = isset($status['cpu']) ? round($status['cpu'] * 100, 2) : 0;
            $mem_total = $status['memory']['total'] ?? 1;
            $mem_used = $status['memory']['used'] ?? 0;
            $mem_usage = $mem_total > 0 ? round(($mem_used / $mem_total) * 100, 2) : 0;

            $disk_total = $status['rootfs']['total'] ?? 1;
            $disk_used = $status['rootfs']['used'] ?? 0;
            $disk_usage = $disk_total > 0 ? round(($disk_used / $disk_total) * 100, 2) : 0;

            return [
                'cpu' => [
                    'usage' => $cpu_usage,
                    'cores' => $status['cpuinfo']['cpus'] ?? 0,
                    'model' => $status['cpuinfo']['model'] ?? 'Unknown',
                ],
                'memory' => [
                    'total' => $this->formatBytes($mem_total),
                    'used' => $this->formatBytes($mem_used),
                    'free' => $this->formatBytes($mem_total - $mem_used),
                    'usage_percent' => $mem_usage,
                ],
                'disk' => [
                    'total' => $this->formatBytes($disk_total),
                    'used' => $this->formatBytes($disk_used),
                    'free' => $this->formatBytes($disk_total - $disk_used),
                    'usage_percent' => $disk_usage,
                ],
                'uptime' => [
                    'seconds' => $status['uptime'] ?? 0,
                    'formatted' => $this->formatUptime($status['uptime'] ?? 0),
                    'percent' => $this->calculateUptimePercent($status['uptime'] ?? 0),
                ],
                'kernel' => $status['kversion'] ?? 'Unknown',
                'pve_version' => $status['pveversion'] ?? 'Unknown',
            ];

        } catch (Exception $e) {
            error_log("Proxmox getMetrics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Получить список виртуальных машин
     */
    private function getVMsList() {
        try {
            $node = $this->config['node'];
            $data = $this->apiRequest("/api2/json/nodes/$node/qemu");

            if (!isset($data['data'])) {
                return [];
            }

            $vms = [];
            foreach ($data['data'] as $vm) {
                $vms[] = [
                    'vmid' => $vm['vmid'],
                    'name' => $vm['name'],
                    'status' => $vm['status'],
                    'cpu' => isset($vm['cpu']) ? round($vm['cpu'] * 100, 2) : 0,
                    'mem' => isset($vm['mem'], $vm['maxmem']) && $vm['maxmem'] > 0
                        ? round(($vm['mem'] / $vm['maxmem']) * 100, 2)
                        : 0,
                    'uptime' => isset($vm['uptime']) ? $this->formatUptime($vm['uptime']) : 'N/A',
                ];
            }

            return $vms;

        } catch (Exception $e) {
            error_log("Proxmox getVMsList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Выполнить API запрос
     */
    private function apiRequest($endpoint, $method = 'GET', $data = []) {
        $url = $this->buildUrl($endpoint);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config['ssl_verify'] ?? false);

        // Добавляем cookie с ticket для авторизации
        if ($this->ticket) {
            curl_setopt($ch, CURLOPT_COOKIE, "PVEAuthCookie={$this->ticket}");
        }

        // Добавляем CSRF токен для POST/PUT/DELETE
        $headers = [];
        if (in_array($method, ['POST', 'PUT', 'DELETE']) && $this->csrf_token) {
            $headers[] = "CSRFPreventionToken: {$this->csrf_token}";
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("CURL Error: $error");
        }

        if ($http_code !== 200) {
            throw new Exception("HTTP Error: $http_code");
        }

        return json_decode($response, true);
    }

    /**
     * Построить URL
     */
    private function buildUrl($endpoint) {
        $protocol = 'https';
        return "{$protocol}://{$this->config['host']}:{$this->config['port']}{$endpoint}";
    }

    /**
     * Форматировать байты
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }

    /**
     * Форматировать uptime
     */
    private function formatUptime($seconds) {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return "{$days}d {$hours}h {$minutes}m";
    }

    /**
     * Рассчитать процент uptime
     */
    private function calculateUptimePercent($uptime_seconds) {
        $month_seconds = 30 * 24 * 60 * 60;
        if ($uptime_seconds >= $month_seconds) {
            return 99.9;
        }
        return round(($uptime_seconds / $month_seconds) * 100, 2);
    }

    /**
     * Получить кеш
     */
    private function getCache() {
        if (!file_exists($this->cache_file)) {
            return null;
        }

        $cache_time = filemtime($this->cache_file);
        if (time() - $cache_time > $this->cache_ttl) {
            return null;
        }

        $data = file_get_contents($this->cache_file);
        return json_decode($data, true);
    }

    /**
     * Сохранить кеш
     */
    private function saveCache($data) {
        file_put_contents($this->cache_file, json_encode($data));
    }
}
