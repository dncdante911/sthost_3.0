<?php
/**
 * HAProxy Monitor Class
 * Класс для мониторинга HAProxy серверов
 */

class HAProxyMonitor {
    private $config;
    private $cache_file;
    private $cache_ttl;

    public function __construct($server_config, $cache_ttl = 60) {
        $this->config = $server_config;
        $this->cache_ttl = $cache_ttl;
        $this->cache_file = sys_get_temp_dir() . '/haproxy_' . $this->config['id'] . '.cache';
    }

    /**
     * Получить статус HAProxy
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
                'type' => 'haproxy',
                'status' => 'unknown',
                'online' => false,
                'metrics' => [],
                'frontends' => [],
                'backends' => [],
                'servers' => [],
                'timestamp' => time(),
            ];

            // Получаем данные из stats
            $stats = $this->getStats();

            if (!$stats) {
                $status['status'] = 'offline';
                $this->saveCache($status);
                return $status;
            }

            $status['online'] = true;
            $status['status'] = 'online';
            $status['metrics'] = $this->parseGlobalMetrics($stats);
            $status['frontends'] = $this->parseFrontends($stats);
            $status['backends'] = $this->parseBackends($stats);
            $status['servers'] = $this->parseServers($stats);

            $this->saveCache($status);
            return $status;

        } catch (Exception $e) {
            error_log("HAProxy Monitor Error: " . $e->getMessage());
            return [
                'id' => $this->config['id'],
                'name' => $this->config['name'],
                'type' => 'haproxy',
                'status' => 'error',
                'online' => false,
                'error' => $e->getMessage(),
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Получить статистику от HAProxy
     */
    private function getStats() {
        $url = $this->config['stats_url'];
        $format = $this->config['stats_format'] ?? 'csv';

        // Добавляем формат к URL
        if ($format === 'csv') {
            $url .= ';csv';
        } elseif ($format === 'json') {
            $url .= ';json';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['stats_user'] . ':' . $this->config['stats_password']);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $http_code !== 200) {
            return false;
        }

        if ($format === 'csv') {
            return $this->parseCSVStats($response);
        } elseif ($format === 'json') {
            return json_decode($response, true);
        }

        return false;
    }

    /**
     * Парсинг CSV статистики
     */
    private function parseCSVStats($csv) {
        $lines = explode("\n", trim($csv));
        $headers = null;
        $data = [];

        foreach ($lines as $line) {
            if (empty($line) || $line[0] === '#') {
                // Пропускаем комментарии и заголовки
                if ($line[0] === '#' && strpos($line, '# ') === 0) {
                    $headers = str_getcsv(substr($line, 2));
                }
                continue;
            }

            $row = str_getcsv($line);
            if ($headers && count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }

        return $data;
    }

    /**
     * Парсинг глобальных метрик
     */
    private function parseGlobalMetrics($stats) {
        $metrics = [
            'total_sessions' => 0,
            'current_sessions' => 0,
            'max_sessions' => 0,
            'total_requests' => 0,
            'bytes_in' => 0,
            'bytes_out' => 0,
            'uptime' => 0,
        ];

        foreach ($stats as $item) {
            if (!isset($item['pxname']) || !isset($item['svname'])) {
                continue;
            }

            // Собираем общие метрики
            if (isset($item['scur'])) {
                $metrics['current_sessions'] += intval($item['scur']);
            }
            if (isset($item['smax'])) {
                $metrics['max_sessions'] = max($metrics['max_sessions'], intval($item['smax']));
            }
            if (isset($item['stot'])) {
                $metrics['total_sessions'] += intval($item['stot']);
            }
            if (isset($item['bin'])) {
                $metrics['bytes_in'] += intval($item['bin']);
            }
            if (isset($item['bout'])) {
                $metrics['bytes_out'] += intval($item['bout']);
            }
        }

        $metrics['bytes_in_formatted'] = $this->formatBytes($metrics['bytes_in']);
        $metrics['bytes_out_formatted'] = $this->formatBytes($metrics['bytes_out']);

        return $metrics;
    }

    /**
     * Парсинг frontends
     */
    private function parseFrontends($stats) {
        $frontends = [];

        foreach ($stats as $item) {
            if (!isset($item['pxname']) || !isset($item['svname']) || $item['svname'] !== 'FRONTEND') {
                continue;
            }

            $frontends[] = [
                'name' => $item['pxname'],
                'status' => $item['status'] ?? 'UNKNOWN',
                'sessions_current' => intval($item['scur'] ?? 0),
                'sessions_max' => intval($item['smax'] ?? 0),
                'sessions_total' => intval($item['stot'] ?? 0),
                'bytes_in' => $this->formatBytes(intval($item['bin'] ?? 0)),
                'bytes_out' => $this->formatBytes(intval($item['bout'] ?? 0)),
                'requests_total' => intval($item['req_tot'] ?? 0),
                'request_rate' => intval($item['req_rate'] ?? 0),
            ];
        }

        return $frontends;
    }

    /**
     * Парсинг backends
     */
    private function parseBackends($stats) {
        $backends = [];

        foreach ($stats as $item) {
            if (!isset($item['pxname']) || !isset($item['svname']) || $item['svname'] !== 'BACKEND') {
                continue;
            }

            $backends[] = [
                'name' => $item['pxname'],
                'status' => $item['status'] ?? 'UNKNOWN',
                'sessions_current' => intval($item['scur'] ?? 0),
                'sessions_max' => intval($item['smax'] ?? 0),
                'queue_current' => intval($item['qcur'] ?? 0),
                'queue_max' => intval($item['qmax'] ?? 0),
                'active_servers' => intval($item['act'] ?? 0),
                'backup_servers' => intval($item['bck'] ?? 0),
                'downtime' => intval($item['downtime'] ?? 0),
            ];
        }

        return $backends;
    }

    /**
     * Парсинг серверов
     */
    private function parseServers($stats) {
        $servers = [];

        foreach ($stats as $item) {
            if (!isset($item['pxname']) || !isset($item['svname'])) {
                continue;
            }

            // Пропускаем FRONTEND и BACKEND
            if (in_array($item['svname'], ['FRONTEND', 'BACKEND'])) {
                continue;
            }

            $status = $item['status'] ?? 'UNKNOWN';
            $check_status = $item['check_status'] ?? '';

            // Определяем состояние сервера
            $server_status = 'unknown';
            if (strpos($status, 'UP') !== false) {
                $server_status = 'up';
            } elseif (strpos($status, 'DOWN') !== false) {
                $server_status = 'down';
            } elseif (strpos($status, 'MAINT') !== false) {
                $server_status = 'maintenance';
            }

            $servers[] = [
                'backend' => $item['pxname'],
                'name' => $item['svname'],
                'status' => $server_status,
                'status_raw' => $status,
                'check_status' => $check_status,
                'sessions_current' => intval($item['scur'] ?? 0),
                'sessions_max' => intval($item['smax'] ?? 0),
                'queue_current' => intval($item['qcur'] ?? 0),
                'response_time' => intval($item['rtime'] ?? 0),
                'downtime' => intval($item['downtime'] ?? 0),
                'weight' => intval($item['weight'] ?? 0),
            ];
        }

        return $servers;
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
