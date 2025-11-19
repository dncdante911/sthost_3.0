<?php
/**
 * ISPManager Monitor Class
 * Класс для мониторинга серверов ISPManager
 */

class ISPManagerMonitor {
    private $config;
    private $cache_file;
    private $cache_ttl;

    public function __construct($server_config, $cache_ttl = 60) {
        $this->config = $server_config;
        $this->cache_ttl = $cache_ttl;
        $this->cache_file = sys_get_temp_dir() . '/ispmanager_' . $this->config['id'] . '.cache';
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
                'type' => 'ispmanager',
                'status' => 'unknown',
                'online' => false,
                'metrics' => [],
                'timestamp' => time(),
            ];

            // Проверяем доступность сервера
            if (!$this->checkConnection()) {
                $status['status'] = 'offline';
                $this->saveCache($status);
                return $status;
            }

            // Получаем метрики
            $status['online'] = true;
            $status['status'] = 'online';
            $status['metrics'] = $this->getMetrics();

            $this->saveCache($status);
            return $status;

        } catch (Exception $e) {
            error_log("ISPManager Monitor Error: " . $e->getMessage());
            return [
                'id' => $this->config['id'],
                'name' => $this->config['name'],
                'type' => 'ispmanager',
                'status' => 'error',
                'online' => false,
                'error' => $e->getMessage(),
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Проверка подключения к ISPManager
     */
    private function checkConnection() {
        $url = $this->buildUrl('');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config['ssl'] ?? false);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['username'] . ':' . $this->config['password']);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code == 200 || $http_code == 401; // 401 тоже означает что сервер доступен
    }

    /**
     * Получить метрики сервера
     */
    private function getMetrics() {
        $metrics = [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'network' => $this->getNetworkUsage(),
            'uptime' => $this->getUptime(),
        ];

        return $metrics;
    }

    /**
     * Получить загрузку CPU
     */
    private function getCpuUsage() {
        try {
            $data = $this->apiRequest('stat');

            if (isset($data['cpu'])) {
                return [
                    'usage' => round($data['cpu']['usage'] ?? 0, 2),
                    'load_avg' => $data['cpu']['load_avg'] ?? [0, 0, 0],
                ];
            }

            return ['usage' => 0, 'load_avg' => [0, 0, 0]];
        } catch (Exception $e) {
            return ['usage' => 0, 'load_avg' => [0, 0, 0], 'error' => $e->getMessage()];
        }
    }

    /**
     * Получить использование памяти
     */
    private function getMemoryUsage() {
        try {
            $data = $this->apiRequest('stat');

            if (isset($data['memory'])) {
                $total = $data['memory']['total'] ?? 1;
                $used = $data['memory']['used'] ?? 0;
                $free = $data['memory']['free'] ?? 0;

                return [
                    'total' => $this->formatBytes($total),
                    'used' => $this->formatBytes($used),
                    'free' => $this->formatBytes($free),
                    'usage_percent' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
                ];
            }

            return ['total' => 0, 'used' => 0, 'free' => 0, 'usage_percent' => 0];
        } catch (Exception $e) {
            return ['total' => 0, 'used' => 0, 'free' => 0, 'usage_percent' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получить использование диска
     */
    private function getDiskUsage() {
        try {
            $data = $this->apiRequest('stat.disk');

            if (isset($data['disk'])) {
                $total = $data['disk']['total'] ?? 1;
                $used = $data['disk']['used'] ?? 0;
                $free = $data['disk']['free'] ?? 0;

                return [
                    'total' => $this->formatBytes($total),
                    'used' => $this->formatBytes($used),
                    'free' => $this->formatBytes($free),
                    'usage_percent' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
                ];
            }

            return ['total' => 0, 'used' => 0, 'free' => 0, 'usage_percent' => 0];
        } catch (Exception $e) {
            return ['total' => 0, 'used' => 0, 'free' => 0, 'usage_percent' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получить использование сети
     */
    private function getNetworkUsage() {
        try {
            $data = $this->apiRequest('stat.network');

            if (isset($data['network'])) {
                return [
                    'rx_bytes' => $this->formatBytes($data['network']['rx_bytes'] ?? 0),
                    'tx_bytes' => $this->formatBytes($data['network']['tx_bytes'] ?? 0),
                    'rx_rate' => $this->formatBytes(($data['network']['rx_rate'] ?? 0) * 8) . '/s',
                    'tx_rate' => $this->formatBytes(($data['network']['tx_rate'] ?? 0) * 8) . '/s',
                ];
            }

            return ['rx_bytes' => 0, 'tx_bytes' => 0, 'rx_rate' => 0, 'tx_rate' => 0];
        } catch (Exception $e) {
            return ['rx_bytes' => 0, 'tx_bytes' => 0, 'rx_rate' => 0, 'tx_rate' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Получить uptime сервера
     */
    private function getUptime() {
        try {
            $data = $this->apiRequest('stat');

            if (isset($data['uptime'])) {
                return [
                    'seconds' => $data['uptime'],
                    'formatted' => $this->formatUptime($data['uptime']),
                    'percent' => $this->calculateUptimePercent($data['uptime']),
                ];
            }

            return ['seconds' => 0, 'formatted' => '0d 0h 0m', 'percent' => 0];
        } catch (Exception $e) {
            return ['seconds' => 0, 'formatted' => '0d 0h 0m', 'percent' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Выполнить API запрос к ISPManager
     */
    private function apiRequest($func, $params = []) {
        $url = $this->buildUrl($func, $params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config['ssl'] ?? false);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['username'] . ':' . $this->config['password']);

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

        // Парсим XML ответ от ISPManager
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            throw new Exception("Invalid XML response");
        }

        return $this->xmlToArray($xml);
    }

    /**
     * Построить URL для API запроса
     */
    private function buildUrl($func, $params = []) {
        $protocol = ($this->config['ssl'] ?? true) ? 'https' : 'http';
        $base = "{$protocol}://{$this->config['host']}:{$this->config['port']}/ispmgr";

        $query = array_merge([
            'out' => 'xml',
            'func' => $func,
        ], $params);

        return $base . '?' . http_build_query($query);
    }

    /**
     * Конвертировать XML в массив
     */
    private function xmlToArray($xml) {
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    /**
     * Форматировать байты в читаемый вид
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
     * Рассчитать процент uptime за 30 дней
     */
    private function calculateUptimePercent($uptime_seconds) {
        $month_seconds = 30 * 24 * 60 * 60; // 30 дней
        if ($uptime_seconds >= $month_seconds) {
            return 99.9;
        }
        return round(($uptime_seconds / $month_seconds) * 100, 2);
    }

    /**
     * Получить данные из кеша
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
     * Сохранить данные в кеш
     */
    private function saveCache($data) {
        file_put_contents($this->cache_file, json_encode($data));
    }
}
