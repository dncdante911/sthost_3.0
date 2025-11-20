<?php
/**
 * Server Monitor Main Class
 * Главный класс для управления мониторингом всех серверов
 */

// Определяем путь к классам мониторинга
$monitor_path = dirname(__FILE__);
require_once $monitor_path . '/ISPManagerMonitor.php';
require_once $monitor_path . '/ProxmoxMonitor.php';
require_once $monitor_path . '/HAProxyMonitor.php';
require_once $monitor_path . '/NetworkMonitor.php';
require_once $monitor_path . '/SystemServerMonitor.php';

class ServerMonitor {
    private $config;
    private $cache_ttl;

    public function __construct($config_file = null) {
        // Загружаем конфигурацию
        if ($config_file && file_exists($config_file)) {
            $this->config = require $config_file;
        } else {
            // Определяем корень проекта
            $project_root = $_SERVER['DOCUMENT_ROOT'] ?? '/var/www/www-root/data/www/sthost.pro';

            // Пытаемся загрузить из стандартного места
            $default_config = $project_root . '/config/monitoring.config.php';
            $sthost_config = $project_root . '/config/monitoring.config.sthost.php';
            $example_config = $project_root . '/config/monitoring.config.example.php';

            if (file_exists($default_config)) {
                $this->config = require $default_config;
            } elseif (file_exists($sthost_config)) {
                $this->config = require $sthost_config;
            } elseif (file_exists($example_config)) {
                $this->config = require $example_config;
            } else {
                throw new Exception("Monitoring configuration file not found. Tried: $default_config, $sthost_config, $example_config");
            }
        }

        $this->cache_ttl = $this->config['general']['cache_ttl'] ?? 60;
    }

    /**
     * Получить статус всех серверов
     */
    public function getAllServersStatus() {
        $result = [
            'ispmanager' => [],
            'proxmox' => [],
            'haproxy' => [],
            'network' => [],
            'system_servers' => [],
            'summary' => [
                'total' => 0,
                'online' => 0,
                'offline' => 0,
                'error' => 0,
            ],
            'timestamp' => time(),
        ];

        // ISPManager серверы
        if ($this->config['ispmanager']['enabled'] ?? false) {
            foreach ($this->config['ispmanager']['servers'] as $server) {
                $monitor = new ISPManagerMonitor($server, $this->cache_ttl);
                $status = $monitor->getServerStatus();
                $result['ispmanager'][] = $status;
                $this->updateSummary($result['summary'], $status);
            }
        }

        // Proxmox серверы
        if ($this->config['proxmox']['enabled'] ?? false) {
            foreach ($this->config['proxmox']['servers'] as $server) {
                $monitor = new ProxmoxMonitor($server, $this->cache_ttl);
                $status = $monitor->getServerStatus();
                $result['proxmox'][] = $status;
                $this->updateSummary($result['summary'], $status);
            }
        }

        // HAProxy серверы
        if ($this->config['haproxy']['enabled'] ?? false) {
            foreach ($this->config['haproxy']['servers'] as $server) {
                $monitor = new HAProxyMonitor($server, $this->cache_ttl);
                $status = $monitor->getServerStatus();
                $result['haproxy'][] = $status;
                $this->updateSummary($result['summary'], $status);
            }
        }

        // Сетевые интерфейсы
        if ($this->config['network']['enabled'] ?? false) {
            foreach ($this->config['network']['interfaces'] as $interface) {
                $monitor = new NetworkMonitor($interface, $this->cache_ttl);
                $status = $monitor->getInterfaceStatus();
                $result['network'][] = $status;
                $this->updateSummary($result['summary'], $status);
            }
        }

        // Системные серверы (HTTP/TCP проверки)
        if ($this->config['system_servers']['enabled'] ?? false) {
            foreach ($this->config['system_servers']['servers'] as $server) {
                $monitor = new SystemServerMonitor($server, $this->cache_ttl);
                $status = $monitor->getServerStatus();
                $result['system_servers'][] = $status;
                $this->updateSummary($result['summary'], $status);
            }
        }

        return $result;
    }

    /**
     * Получить статус конкретного сервера
     */
    public function getServerStatus($type, $id) {
        switch ($type) {
            case 'ispmanager':
                if (!($this->config['ispmanager']['enabled'] ?? false)) {
                    return ['error' => 'ISPManager monitoring is disabled'];
                }

                foreach ($this->config['ispmanager']['servers'] as $server) {
                    if ($server['id'] === $id) {
                        $monitor = new ISPManagerMonitor($server, $this->cache_ttl);
                        return $monitor->getServerStatus();
                    }
                }
                break;

            case 'proxmox':
                if (!($this->config['proxmox']['enabled'] ?? false)) {
                    return ['error' => 'Proxmox monitoring is disabled'];
                }

                foreach ($this->config['proxmox']['servers'] as $server) {
                    if ($server['id'] === $id) {
                        $monitor = new ProxmoxMonitor($server, $this->cache_ttl);
                        return $monitor->getServerStatus();
                    }
                }
                break;

            case 'haproxy':
                if (!($this->config['haproxy']['enabled'] ?? false)) {
                    return ['error' => 'HAProxy monitoring is disabled'];
                }

                foreach ($this->config['haproxy']['servers'] as $server) {
                    if ($server['id'] === $id) {
                        $monitor = new HAProxyMonitor($server, $this->cache_ttl);
                        return $monitor->getServerStatus();
                    }
                }
                break;

            case 'network':
                if (!($this->config['network']['enabled'] ?? false)) {
                    return ['error' => 'Network monitoring is disabled'];
                }

                foreach ($this->config['network']['interfaces'] as $interface) {
                    if ($interface['id'] === $id) {
                        $monitor = new NetworkMonitor($interface, $this->cache_ttl);
                        return $monitor->getInterfaceStatus();
                    }
                }
                break;
        }

        return ['error' => 'Server not found'];
    }

    /**
     * Получить упрощенный статус для виджетов
     */
    public function getSimpleStatus() {
        $allStatus = $this->getAllServersStatus();

        $simple = [];

        // ISPManager
        foreach ($allStatus['ispmanager'] as $server) {
            $simple[] = [
                'id' => $server['id'],
                'name' => $server['name'],
                'type' => 'ISPManager',
                'status' => $server['status'],
                'online' => $server['online'],
                'cpu' => $server['metrics']['cpu']['usage'] ?? 0,
                'memory' => $server['metrics']['memory']['usage_percent'] ?? 0,
                'uptime' => $server['metrics']['uptime']['percent'] ?? 0,
            ];
        }

        // Proxmox
        foreach ($allStatus['proxmox'] as $server) {
            $simple[] = [
                'id' => $server['id'],
                'name' => $server['name'],
                'type' => 'Proxmox',
                'status' => $server['status'],
                'online' => $server['online'],
                'cpu' => $server['metrics']['cpu']['usage'] ?? 0,
                'memory' => $server['metrics']['memory']['usage_percent'] ?? 0,
                'uptime' => $server['metrics']['uptime']['percent'] ?? 0,
                'vms_count' => count($server['vms'] ?? []),
            ];
        }

        // HAProxy
        foreach ($allStatus['haproxy'] as $server) {
            $backends_up = 0;
            $backends_total = count($server['backends'] ?? []);

            foreach ($server['backends'] ?? [] as $backend) {
                if (stripos($backend['status'], 'UP') !== false) {
                    $backends_up++;
                }
            }

            $simple[] = [
                'id' => $server['id'],
                'name' => $server['name'],
                'type' => 'HAProxy',
                'status' => $server['status'],
                'online' => $server['online'],
                'backends_up' => $backends_up,
                'backends_total' => $backends_total,
                'sessions' => $server['metrics']['current_sessions'] ?? 0,
            ];
        }

        // Network
        foreach ($allStatus['network'] as $interface) {
            $simple[] = [
                'id' => $interface['id'],
                'name' => $interface['name'],
                'type' => 'Network',
                'status' => $interface['status'],
                'online' => $interface['online'],
                'usage' => $interface['metrics']['usage_percent'] ?? 0,
                'rx_rate' => $interface['metrics']['rx_rate_formatted'] ?? '0 bps',
                'tx_rate' => $interface['metrics']['tx_rate_formatted'] ?? '0 bps',
            ];
        }

        // System Servers (HTTP/TCP)
        foreach ($allStatus['system_servers'] as $server) {
            $simple[] = [
                'id' => $server['id'],
                'name' => $server['name'],
                'type' => 'System',
                'status' => $server['status'],
                'online' => $server['online'],
                'response_time' => $server['metrics']['response_time'] ?? 0,
                'response_time_formatted' => $server['metrics']['response_time_formatted'] ?? 'N/A',
            ];
        }

        return [
            'servers' => $simple,
            'summary' => $allStatus['summary'],
            'timestamp' => $allStatus['timestamp'],
        ];
    }

    /**
     * Проверить алерты
     */
    public function checkAlerts() {
        if (!($this->config['alerts']['enabled'] ?? false)) {
            return [];
        }

        $alerts = [];
        $thresholds = $this->config['alerts']['thresholds'];
        $allStatus = $this->getAllServersStatus();

        // Проверяем ISPManager
        foreach ($allStatus['ispmanager'] as $server) {
            if (!$server['online']) {
                $alerts[] = [
                    'severity' => 'critical',
                    'type' => 'server_offline',
                    'server' => $server['name'],
                    'message' => "Server {$server['name']} is offline",
                ];
                continue;
            }

            $cpu = $server['metrics']['cpu']['usage'] ?? 0;
            $memory = $server['metrics']['memory']['usage_percent'] ?? 0;
            $disk = $server['metrics']['disk']['usage_percent'] ?? 0;

            if ($cpu > $thresholds['cpu']) {
                $alerts[] = [
                    'severity' => 'warning',
                    'type' => 'high_cpu',
                    'server' => $server['name'],
                    'value' => $cpu,
                    'threshold' => $thresholds['cpu'],
                    'message' => "High CPU usage on {$server['name']}: {$cpu}%",
                ];
            }

            if ($memory > $thresholds['memory']) {
                $alerts[] = [
                    'severity' => 'warning',
                    'type' => 'high_memory',
                    'server' => $server['name'],
                    'value' => $memory,
                    'threshold' => $thresholds['memory'],
                    'message' => "High memory usage on {$server['name']}: {$memory}%",
                ];
            }

            if ($disk > $thresholds['disk']) {
                $alerts[] = [
                    'severity' => 'warning',
                    'type' => 'high_disk',
                    'server' => $server['name'],
                    'value' => $disk,
                    'threshold' => $thresholds['disk'],
                    'message' => "High disk usage on {$server['name']}: {$disk}%",
                ];
            }
        }

        // Проверяем Network
        foreach ($allStatus['network'] as $interface) {
            $usage = $interface['metrics']['usage_percent'] ?? 0;

            if ($usage > $thresholds['network']) {
                $alerts[] = [
                    'severity' => 'warning',
                    'type' => 'high_network',
                    'interface' => $interface['name'],
                    'value' => $usage,
                    'threshold' => $thresholds['network'],
                    'message' => "High network usage on {$interface['name']}: {$usage}%",
                ];
            }
        }

        return $alerts;
    }

    /**
     * Обновить сводку
     */
    private function updateSummary(&$summary, $status) {
        $summary['total']++;

        if ($status['online']) {
            $summary['online']++;
        } elseif ($status['status'] === 'error') {
            $summary['error']++;
        } else {
            $summary['offline']++;
        }
    }

    /**
     * Очистить весь кеш
     */
    public function clearCache() {
        $cache_dir = sys_get_temp_dir();
        $patterns = ['ispmanager_*.cache', 'proxmox_*.cache', 'haproxy_*.cache', 'network_*.cache'];

        foreach ($patterns as $pattern) {
            $files = glob($cache_dir . '/' . $pattern);
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}
