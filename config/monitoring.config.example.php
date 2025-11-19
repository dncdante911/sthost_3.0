<?php
/**
 * Конфигурация системы мониторинга серверов
 *
 * ВАЖНО: Скопируйте этот файл как monitoring.config.php и заполните реальными данными
 * НЕ коммитьте monitoring.config.php в Git!
 */

return [
    // Общие настройки
    'general' => [
        'cache_ttl' => 60, // Время кеширования в секундах
        'timeout' => 10,   // Таймаут для API запросов
        'enabled' => true, // Включить/выключить мониторинг
    ],

    // ISPmanager серверы
    'ispmanager' => [
        'enabled' => true,
        'servers' => [
            [
                'id' => 'isp_main',
                'name' => 'ISPmanager Main',
                'host' => 'your-ispmanager-host.com',
                'port' => 1500,
                'username' => 'admin',
                'password' => 'your-password-here',
                'ssl' => true,
            ],
            // Добавьте больше серверов по необходимости
        ],
    ],

    // Proxmox VE серверы
    'proxmox' => [
        'enabled' => true,
        'servers' => [
            [
                'id' => 'pve_main',
                'name' => 'Proxmox Main Node',
                'host' => 'your-proxmox-host.com',
                'port' => 8006,
                'node' => 'pve', // Имя ноды Proxmox
                'username' => 'root@pam',
                'password' => 'your-password-here',
                'realm' => 'pam',
                'ssl_verify' => false, // Проверка SSL сертификата
            ],
            [
                'id' => 'pve_backup',
                'name' => 'Proxmox Backup Node',
                'host' => 'your-proxmox-backup.com',
                'port' => 8006,
                'node' => 'pve-backup',
                'username' => 'root@pam',
                'password' => 'your-password-here',
                'realm' => 'pam',
                'ssl_verify' => false,
            ],
        ],
    ],

    // HAProxy серверы
    'haproxy' => [
        'enabled' => true,
        'servers' => [
            [
                'id' => 'haproxy_main',
                'name' => 'HAProxy Load Balancer',
                'stats_url' => 'http://your-haproxy-host.com:8080/stats',
                'stats_user' => 'admin',
                'stats_password' => 'your-password-here',
                'stats_format' => 'csv', // csv или json
            ],
        ],
    ],

    // Мониторинг сетевых каналов (через SNMP)
    'network' => [
        'enabled' => true,
        'interfaces' => [
            [
                'id' => 'wan_main',
                'name' => 'Main WAN Channel',
                'host' => 'your-router-or-switch.com',
                'snmp_version' => '2c',
                'community' => 'public',
                'interface' => 'eth0', // Интерфейс для мониторинга
                'bandwidth' => 1000, // Мбит/с для расчета процента использования
            ],
            [
                'id' => 'wan_backup',
                'name' => 'Backup WAN Channel',
                'host' => 'your-backup-router.com',
                'snmp_version' => '2c',
                'community' => 'public',
                'interface' => 'eth1',
                'bandwidth' => 500, // Мбит/с
            ],
        ],
    ],

    // Простой мониторинг серверов (через системные команды)
    'system_servers' => [
        'enabled' => true,
        'servers' => [
            [
                'id' => 'web_server',
                'name' => 'Web Server',
                'host' => 'localhost',
                'check_url' => 'http://localhost',
                'check_port' => 80,
                'check_method' => 'http', // http, ping, tcp
            ],
            [
                'id' => 'db_server',
                'name' => 'Database Server',
                'host' => 'localhost',
                'check_port' => 3306,
                'check_method' => 'tcp',
            ],
        ],
    ],

    // Алерты и уведомления
    'alerts' => [
        'enabled' => true,
        'thresholds' => [
            'cpu' => 80,      // Процент загрузки CPU
            'memory' => 85,   // Процент использования памяти
            'disk' => 90,     // Процент использования диска
            'network' => 80,  // Процент использования канала
        ],
        'notifications' => [
            'telegram' => [
                'enabled' => false,
                'bot_token' => 'your-telegram-bot-token',
                'chat_id' => 'your-telegram-chat-id',
            ],
            'email' => [
                'enabled' => false,
                'to' => 'alerts@sthost.pro',
            ],
        ],
    ],
];
