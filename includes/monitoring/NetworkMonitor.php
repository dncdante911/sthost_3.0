<?php
/**
 * Network Monitor Class
 * Класс для мониторинга сетевых каналов через SNMP
 */

class NetworkMonitor {
    private $config;
    private $cache_file;
    private $cache_ttl;

    public function __construct($interface_config, $cache_ttl = 60) {
        $this->config = $interface_config;
        $this->cache_ttl = $cache_ttl;
        $this->cache_file = sys_get_temp_dir() . '/network_' . $this->config['id'] . '.cache';
    }

    /**
     * Получить статус сетевого интерфейса
     */
    public function getInterfaceStatus() {
        // Проверяем кеш
        $cached = $this->getCache();
        if ($cached !== null) {
            return $cached;
        }

        try {
            $status = [
                'id' => $this->config['id'],
                'name' => $this->config['name'],
                'type' => 'network',
                'status' => 'unknown',
                'online' => false,
                'metrics' => [],
                'timestamp' => time(),
            ];

            // Проверяем доступность SNMP
            if (!$this->checkSNMP()) {
                $status['status'] = 'snmp_unavailable';
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
            error_log("Network Monitor Error: " . $e->getMessage());
            return [
                'id' => $this->config['id'],
                'name' => $this->config['name'],
                'type' => 'network',
                'status' => 'error',
                'online' => false,
                'error' => $e->getMessage(),
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Проверка доступности SNMP
     */
    private function checkSNMP() {
        if (!function_exists('snmpget')) {
            error_log("SNMP extension not installed");
            return false;
        }

        try {
            $result = @snmpget(
                $this->config['host'],
                $this->config['community'],
                'SNMPv2-MIB::sysDescr.0',
                1000000,
                1
            );

            return $result !== false;
        } catch (Exception $e) {
            error_log("SNMP check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получить метрики интерфейса
     */
    private function getMetrics() {
        $interface = $this->config['interface'];
        $bandwidth = $this->config['bandwidth'] ?? 1000; // Мбит/с

        // Получаем индекс интерфейса
        $ifIndex = $this->getInterfaceIndex($interface);

        if (!$ifIndex) {
            return [
                'error' => 'Interface not found',
                'rx_bytes' => 0,
                'tx_bytes' => 0,
                'rx_rate' => 0,
                'tx_rate' => 0,
                'usage_percent' => 0,
            ];
        }

        // Получаем счетчики трафика
        $inOctets = $this->getSNMPValue("IF-MIB::ifInOctets.$ifIndex");
        $outOctets = $this->getSNMPValue("IF-MIB::ifOutOctets.$ifIndex");
        $ifSpeed = $this->getSNMPValue("IF-MIB::ifSpeed.$ifIndex");
        $ifOperStatus = $this->getSNMPValue("IF-MIB::ifOperStatus.$ifIndex");

        // Получаем предыдущие значения для расчета скорости
        $prevData = $this->getPreviousData();
        $currentTime = time();

        $metrics = [
            'rx_bytes' => intval($inOctets),
            'tx_bytes' => intval($outOctets),
            'rx_bytes_formatted' => $this->formatBytes($inOctets),
            'tx_bytes_formatted' => $this->formatBytes($outOctets),
            'interface_speed' => intval($ifSpeed),
            'interface_speed_formatted' => $this->formatBps($ifSpeed),
            'operational_status' => $this->parseOperStatus($ifOperStatus),
            'rx_rate' => 0,
            'tx_rate' => 0,
            'rx_rate_formatted' => '0 bps',
            'tx_rate_formatted' => '0 bps',
            'usage_percent' => 0,
        ];

        // Рассчитываем скорость, если есть предыдущие данные
        if ($prevData && isset($prevData['rx_bytes'], $prevData['tx_bytes'], $prevData['timestamp'])) {
            $timeDiff = $currentTime - $prevData['timestamp'];

            if ($timeDiff > 0) {
                // Вычисляем разницу с учетом возможного переполнения счетчика (32-бит)
                $rxDiff = $this->calculateDiff($inOctets, $prevData['rx_bytes']);
                $txDiff = $this->calculateDiff($outOctets, $prevData['tx_bytes']);

                // Скорость в битах в секунду
                $rxRate = ($rxDiff * 8) / $timeDiff;
                $txRate = ($txDiff * 8) / $timeDiff;

                $metrics['rx_rate'] = round($rxRate, 2);
                $metrics['tx_rate'] = round($txRate, 2);
                $metrics['rx_rate_formatted'] = $this->formatBps($rxRate);
                $metrics['tx_rate_formatted'] = $this->formatBps($txRate);

                // Рассчитываем процент использования канала
                $bandwidthBps = $bandwidth * 1000000; // Мбит/с в бит/с
                $totalRate = max($rxRate, $txRate); // Берем максимальное значение
                $metrics['usage_percent'] = $bandwidthBps > 0
                    ? round(($totalRate / $bandwidthBps) * 100, 2)
                    : 0;
            }
        }

        // Сохраняем текущие данные для следующего расчета
        $this->savePreviousData([
            'rx_bytes' => $metrics['rx_bytes'],
            'tx_bytes' => $metrics['tx_bytes'],
            'timestamp' => $currentTime,
        ]);

        return $metrics;
    }

    /**
     * Получить индекс интерфейса по имени
     */
    private function getInterfaceIndex($interfaceName) {
        // Получаем список всех интерфейсов
        $ifDescr = @snmpwalk(
            $this->config['host'],
            $this->config['community'],
            'IF-MIB::ifDescr',
            1000000,
            1
        );

        if (!$ifDescr) {
            return null;
        }

        foreach ($ifDescr as $oid => $value) {
            $value = trim($value, '"');
            if ($value === $interfaceName) {
                // Извлекаем индекс из OID
                if (preg_match('/\.(\d+)$/', $oid, $matches)) {
                    return $matches[1];
                }
            }
        }

        return null;
    }

    /**
     * Получить SNMP значение
     */
    private function getSNMPValue($oid) {
        $result = @snmpget(
            $this->config['host'],
            $this->config['community'],
            $oid,
            1000000,
            1
        );

        if ($result === false) {
            return 0;
        }

        // Очищаем значение от префиксов типа "INTEGER: " или "Counter32: "
        $result = preg_replace('/^[A-Z\-]+:\s*/', '', $result);
        return trim($result, '"');
    }

    /**
     * Парсинг операционного статуса интерфейса
     */
    private function parseOperStatus($status) {
        $status = strtolower(trim($status));

        $statusMap = [
            '1' => 'up',
            'up' => 'up',
            '2' => 'down',
            'down' => 'down',
            '3' => 'testing',
            'testing' => 'testing',
        ];

        return $statusMap[$status] ?? 'unknown';
    }

    /**
     * Рассчитать разницу с учетом переполнения счетчика
     */
    private function calculateDiff($current, $previous) {
        if ($current >= $previous) {
            return $current - $previous;
        } else {
            // Переполнение 32-битного счетчика
            return (4294967296 - $previous) + $current;
        }
    }

    /**
     * Получить предыдущие данные
     */
    private function getPreviousData() {
        $file = $this->cache_file . '.prev';

        if (!file_exists($file)) {
            return null;
        }

        $data = file_get_contents($file);
        return json_decode($data, true);
    }

    /**
     * Сохранить предыдущие данные
     */
    private function savePreviousData($data) {
        $file = $this->cache_file . '.prev';
        file_put_contents($file, json_encode($data));
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
     * Форматировать биты в секунду
     */
    private function formatBps($bps) {
        $units = ['bps', 'Kbps', 'Mbps', 'Gbps', 'Tbps'];
        $bps = max($bps, 0);
        $pow = floor(($bps ? log($bps) : 0) / log(1000));
        $pow = min($pow, count($units) - 1);

        return round($bps / (1000 ** $pow), 2) . ' ' . $units[$pow];
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
