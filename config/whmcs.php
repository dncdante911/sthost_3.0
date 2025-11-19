<?php
/**
 * WHMCS Configuration
 * Налаштування інтеграції з WHMCS
 */

return [
    // WHMCS API Configuration
    'api' => [
        'enabled' => false, // Увімкнути/вимкнути інтеграцію з WHMCS
        'url' => 'https://bill.sthost.pro', // URL вашого WHMCS
        'identifier' => '', // API Identifier (отримати в WHMCS: Setup > Staff Management > API Credentials)
        'secret' => '', // API Secret
        'access_key' => '', // Access Key (опціонально, для додаткової безпеки)
    ],

    // WHMCS Product IDs для доменів
    'products' => [
        'domain_registration' => 1, // ID продукту для реєстрації доменів
        'domain_transfer' => 2,     // ID продукту для трансферу доменів
        'domain_renewal' => 3,      // ID продукту для продовження доменів
    ],

    // Автоматичне перенаправлення
    'redirect' => [
        'enabled' => true, // Увімкнути автоматичне перенаправлення до WHMCS
        'cart_url' => 'https://bill.sthost.pro/cart.php', // URL кошика WHMCS
        'domain_transfer_action' => 'a=add&domain=transfer', // Дія для трансферу
    ],

    // Синхронізація цін
    'prices' => [
        'sync_enabled' => false, // Автоматична синхронізація цін з WHMCS
        'sync_interval' => 3600, // Інтервал синхронізації (секунди)
        'cache_file' => __DIR__ . '/../cache/whmcs_prices.json',
    ],

    // Webhook налаштування
    'webhooks' => [
        'enabled' => false,
        'secret' => '', // Секретний ключ для перевірки webhooks
        'events' => [
            'DomainTransferCompleted',
            'DomainTransferFailed',
            'DomainRegistered',
        ],
    ],

    // Логування
    'logging' => [
        'enabled' => true,
        'file' => __DIR__ . '/../logs/whmcs.log',
        'level' => 'info', // debug, info, warning, error
    ],
];
