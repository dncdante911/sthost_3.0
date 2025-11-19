<?php
/**
 * Конфигурация мониторинга для StormHosting UA
 *
 * ВАЖНО:
 * 1. Скопируйте этот файл как monitoring.config.php
 * 2. Заполните пароли и API ключи
 * 3. НЕ коммитьте monitoring.config.php в Git!
 *
 * Команда для копирования:
 * cp config/monitoring.config.sthost.php config/monitoring.config.php
 */

return [
    // Общие настройки
    'general' => [
        'cache_ttl' => 60,      // Кеш на 60 секунд
        'timeout' => 10,        // Таймаут API запросов
        'enabled' => true,      // Включить мониторинг
    ],

    // ========================================
    // ISPmanager - 192.168.0.250 (SSH: 224)
    // ========================================
    'ispmanager' => [
        'enabled' => true,
        'servers' => [
            [
                'id' => 'isp_main',
                'name' => 'ISPmanager Main (192.168.0.250)',
                'host' => '192.168.0.250',
                'port' => 1500,                    // Стандартный порт ISPmanager
                'username' => 'admin',             // ⚠️ ЗАПОЛНИТЕ: логин админа ISPmanager
                'password' => 'YOUR_PASSWORD',     // ⚠️ ЗАПОЛНИТЕ: пароль ISPmanager
                'ssl' => true,                     // HTTPS подключение
            ],
        ],
    ],

    // ========================================
    // Proxmox VE - 192.168.0.4 (SSH: 225)
    // ========================================
    'proxmox' => [
        'enabled' => true,
        'servers' => [
            [
                'id' => 'pve_main',
                'name' => 'Proxmox VE Main (192.168.0.4)',
                'host' => '192.168.0.4',
                'port' => 8006,                         // Стандартный порт Proxmox Web UI
                'node' => 'pve',                        // ⚠️ ПРОВЕРЬТЕ: имя ноды (команда: hostname)
                'username' => 'root@pam',               // Пользователь Proxmox
                'password' => 'YOUR_PASSWORD',          // ⚠️ ЗАПОЛНИТЕ: пароль root
                'realm' => 'pam',                       // Realm аутентификации
                'ssl_verify' => false,                  // Не проверять SSL (самоподписанный сертификат)
            ],
        ],
    ],

    // ========================================
    // HAProxy - 192.168.0.10 (SSH: 22)
    // ========================================
    'haproxy' => [
        'enabled' => true,
        'servers' => [
            [
                'id' => 'haproxy_main',
                'name' => 'HAProxy Load Balancer (192.168.0.10)',
                'stats_url' => 'http://192.168.0.10:8080/stats',  // ⚠️ ПРОВЕРЬТЕ: порт stats
                'stats_user' => 'admin',                           // ⚠️ ЗАПОЛНИТЕ: логин для stats
                'stats_password' => 'YOUR_PASSWORD',               // ⚠️ ЗАПОЛНИТЕ: пароль stats
                'stats_format' => 'csv',                           // Формат вывода
            ],
        ],
    ],

    // ========================================
    // Мониторинг сетевых каналов (SNMP)
    // ========================================
    'network' => [
        'enabled' => true,
        'interfaces' => [
            // Канал 1: ns1.sthost.pro (195.22.131.11)
            [
                'id' => 'wan_ns1',
                'name' => 'WAN: ns1.sthost.pro (195.22.131.11)',
                'host' => '192.168.0.10',           // HAProxy как точка мониторинга
                'snmp_version' => '2c',
                'community' => 'public',            // ⚠️ ЗАПОЛНИТЕ: SNMP community string
                'interface' => 'eth0',              // ⚠️ ПРОВЕРЬТЕ: имя интерфейса (snmpwalk)
                'bandwidth' => 1000,                // Пропускная способность в Мбит/с
            ],

            // Канал 2: ns2.sthost.pro (46.232.232.38)
            [
                'id' => 'wan_ns2',
                'name' => 'WAN: ns2.sthost.pro (46.232.232.38)',
                'host' => '192.168.0.10',           // HAProxy как точка мониторинга
                'snmp_version' => '2c',
                'community' => 'public',            // ⚠️ ЗАПОЛНИТЕ: SNMP community string
                'interface' => 'eth1',              // ⚠️ ПРОВЕРЬТЕ: имя интерфейса (snmpwalk)
                'bandwidth' => 1000,                // Пропускная способность в Мбит/с
            ],
        ],
    ],

    // ========================================
    // Простой мониторинг через HTTP/TCP
    // ========================================
    'system_servers' => [
        'enabled' => true,
        'servers' => [
            [
                'id' => 'ns1_http',
                'name' => 'ns1.sthost.pro HTTP',
                'host' => '195.22.131.11',
                'check_url' => 'http://195.22.131.11',
                'check_port' => 80,
                'check_method' => 'http',
            ],
            [
                'id' => 'ns2_http',
                'name' => 'ns2.sthost.pro HTTP',
                'host' => '46.232.232.38',
                'check_url' => 'http://46.232.232.38',
                'check_port' => 80,
                'check_method' => 'http',
            ],
        ],
    ],

    // ========================================
    // Алерты и уведомления
    // ========================================
    'alerts' => [
        'enabled' => true,
        'thresholds' => [
            'cpu' => 80,        // % загрузки CPU
            'memory' => 85,     // % использования RAM
            'disk' => 90,       // % использования диска
            'network' => 80,    // % использования канала
        ],
        'notifications' => [
            'telegram' => [
                'enabled' => false,                     // ⚠️ Включите после настройки
                'bot_token' => 'YOUR_BOT_TOKEN',        // ⚠️ ЗАПОЛНИТЕ: токен бота
                'chat_id' => 'YOUR_CHAT_ID',            // ⚠️ ЗАПОЛНИТЕ: ID чата
            ],
            'email' => [
                'enabled' => false,
                'to' => 'support@sthost.pro',
            ],
        ],
    ],
];
