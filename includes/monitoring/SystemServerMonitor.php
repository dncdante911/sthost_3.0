<?php
/**
 * System Server Monitor
 * Простой мониторинг серверов через HTTP/TCP проверки
 */

class SystemServerMonitor {
    private $config;
    private $cache_ttl;

    public function __construct($config, $cache_ttl = 60) {
        $this->config = $config;
        $this->cache_ttl = $cache_ttl;
    }

    /**
     * Получить статус сервера
     */
    public function getServerStatus() {
        $result = [
            'id' => $this->config['id'] ?? 'unknown',
            'name' => $this->config['name'] ?? 'Unknown Server',
            'host' => $this->config['host'] ?? '',
            'status' => 'offline',
            'online' => false,
            'metrics' => [
                'response_time' => 0,
                'check_method' => $this->config['check_method'] ?? 'tcp',
            ],
            'error' => null,
        ];

        try {
            $check_method = $this->config['check_method'] ?? 'tcp';

            if ($check_method === 'http') {
                $this->checkHttp($result);
            } else {
                $this->checkTcp($result);
            }

        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
            $result['status'] = 'error';
        }

        return $result;
    }

    /**
     * HTTP проверка
     */
    private function checkHttp(&$result) {
        $url = $this->config['check_url'] ?? 'http://' . $this->config['host'];
        $timeout = $this->config['timeout'] ?? 5;

        $start_time = microtime(true);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_NOBODY => true, // HEAD request
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $response_time = round((microtime(true) - $start_time) * 1000, 2);
        $result['metrics']['response_time'] = $response_time;
        $result['metrics']['response_time_formatted'] = $response_time . 'ms';
        $result['metrics']['http_code'] = $http_code;

        if ($http_code >= 200 && $http_code < 400) {
            $result['status'] = 'online';
            $result['online'] = true;
        } elseif ($http_code > 0) {
            $result['status'] = 'warning';
            $result['online'] = true;
            $result['error'] = "HTTP $http_code";
        } else {
            $result['status'] = 'offline';
            $result['online'] = false;
            $result['error'] = $error ?: 'Connection failed';
        }
    }

    /**
     * TCP проверка порта
     */
    private function checkTcp(&$result) {
        $host = $this->config['host'];
        $port = $this->config['check_port'] ?? 80;
        $timeout = $this->config['timeout'] ?? 5;

        $start_time = microtime(true);

        $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

        $response_time = round((microtime(true) - $start_time) * 1000, 2);
        $result['metrics']['response_time'] = $response_time;
        $result['metrics']['response_time_formatted'] = $response_time . 'ms';
        $result['metrics']['port'] = $port;

        if ($connection) {
            fclose($connection);
            $result['status'] = 'online';
            $result['online'] = true;
        } else {
            $result['status'] = 'offline';
            $result['online'] = false;
            $result['error'] = "$errstr ($errno)";
        }
    }
}
