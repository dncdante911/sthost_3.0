<?php
/**
 * Proxmox Configuration
 * Налаштування інтеграції з Proxmox VE
 */

return [
    // Proxmox API Configuration
    'api' => [
        'enabled' => false, // Увімкнути/вимкнути інтеграцію з Proxmox
        'host' => 'proxmox.sthost.pro', // Хост Proxmox
        'port' => 8006, // Порт API (зазвичай 8006)
        'node' => 'pve', // Назва ноди (за замовчуванням 'pve')
        'realm' => 'pam', // Realm (pam або pve)
        'verify_ssl' => true, // Перевіряти SSL сертифікат
    ],

    // Автентифікація
    'auth' => [
        'method' => 'token', // 'token' або 'password'

        // Для method = 'token' (рекомендовано)
        'token_id' => '', // Наприклад: root@pam!api-token-id
        'token_secret' => '', // Token Secret

        // Для method = 'password' (не рекомендовано для продакшн)
        'username' => 'root@pam',
        'password' => '',
    ],

    // Налаштування VM/CT
    'defaults' => [
        'storage' => 'local-lvm', // Storage для дисків
        'network_bridge' => 'vmbr0', // Network bridge
        'nameserver' => '8.8.8.8 8.8.4.4', // DNS сервери
        'searchdomain' => 'sthost.pro',
        'ostype' => 'l26', // OS type (l26 для Linux 2.6+)
    ],

    // Шаблони
    'templates' => [
        'ubuntu_22_04' => [
            'name' => 'Ubuntu 22.04 LTS',
            'template_id' => 9000,
            'type' => 'lxc',
            'min_disk' => 8,
            'min_ram' => 512,
        ],
        'debian_12' => [
            'name' => 'Debian 12',
            'template_id' => 9001,
            'type' => 'lxc',
            'min_disk' => 8,
            'min_ram' => 512,
        ],
        'centos_stream_9' => [
            'name' => 'CentOS Stream 9',
            'template_id' => 9002,
            'type' => 'lxc',
            'min_disk' => 10,
            'min_ram' => 1024,
        ],
    ],

    // Лімити та квоти
    'limits' => [
        'max_vm_per_user' => 5,
        'max_cpu_cores' => 8,
        'max_ram_mb' => 16384,
        'max_disk_gb' => 500,
    ],

    // Резервне копіювання
    'backup' => [
        'enabled' => true,
        'storage' => 'backup-storage',
        'retention' => 7, // Днів зберігання backup
        'schedule' => '02:00', // Час запуску (HH:MM)
    ],

    // Логування
    'logging' => [
        'enabled' => true,
        'file' => __DIR__ . '/../logs/proxmox.log',
        'level' => 'info', // debug, info, warning, error
    ],
];
