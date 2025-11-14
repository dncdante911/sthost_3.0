# StormHosting - Руководство по реализации и настройке

## Содержание
1. [Обзор выполненных работ](#обзор-выполненных-работ)
2. [Инструменты проверки (Tools)](#инструменты-проверки-tools)
3. [API эндпоинты](#api-эндпоинты)
4. [Интеграция с WHMCS](#интеграция-с-whmcs)
5. [Настройка VPS](#настройка-vps)
6. [Кеширование и производительность](#кеширование-и-производительность)
7. [Устранение неполадок](#устранение-неполадок)

---

## Обзор выполненных работ

### 1. Облачный хостинг (Cloud Hosting)
**Файл:** `/pages/hosting/cloud.php`

#### Выполнено:
- ✅ Исправлена структура PHP (убраны дублирующиеся HTML теги)
- ✅ Добавлены массивы `$additional_css` и `$additional_js`
- ✅ Создан калькулятор облачных ресурсов
- ✅ Интеграция с WHMCS для заказов
- ✅ Исправлены ссылки на контакты

#### Файлы:
```
/pages/hosting/cloud.php
/assets/js/pages/hosting-cloud.js (создан, 510 строк)
/assets/css/pages/hosting-cloud.css (существующий)
```

#### Калькулятор - ценообразование:
- **CPU:** ₴100 за 1 vCPU
- **RAM:** ₴50 за 1 ГБ
- **Storage:** ₴5 за 1 ГБ SSD
- **Bandwidth:** ₴0.1 за 1 ГБ трафика
- **Скидка на годовой план:** 15%

#### Готовые конфигурации:
- **Start:** ₴399/мес (2 vCPU, 4 GB RAM, 50 GB SSD)
- **Business:** ₴799/мес (4 vCPU, 8 GB RAM, 100 GB SSD)
- **Pro:** ₴1499/мес (8 vCPU, 16 GB RAM, 200 GB SSD)
- **Enterprise:** ₴2999/мес (16 vCPU, 32 GB RAM, 500 GB SSD)

---

### 2. Site Check Tool (Проверка доступности сайтов)
**Файл:** `/pages/tools/site-check.php`

#### Выполнено:
- ✅ Исправлена структура PHP
- ✅ Полностью переписан CSS (соответствие стилю сайта)
- ✅ Исправлены ссылки на инструменты
- ✅ Создана API документация
- ✅ Создан рабочий API endpoint `/v1/site-check`
- ✅ Изменен URL с `api.stormhosting.ua` на `sthost.pro`

#### Файлы:
```
/pages/tools/site-check.php
/assets/css/pages/tools-site-check.css (полностью переписан, 593 строки)
/assets/js/tools-site-check.js
/v1/site-check.php (создан)
```

#### API Endpoint: `/v1/site-check`
**URL:** `https://sthost.pro/v1/site-check`
**Метод:** POST
**Аутентификация:** Bearer Token (обязательно)
**Rate Limit:** 1000 запросов/час

**Параметры запроса:**
```json
{
  "url": "https://example.com",
  "locations": ["kyiv", "frankfurt", "london", "nyc", "singapore", "tokyo"],
  "check_ssl": true,
  "follow_redirects": true,
  "timeout": 10
}
```

**Возможности:**
- Проверка с 6 географических локаций
- SSL сертификат проверка
- DNS резолвинг и тайминги
- HTTP статус коды и заголовки
- Время отклика (DNS, Connect, Total)

---

### 3. IP Check Tool (Проверка IP адресов)
**Файл:** `/pages/tools/ip-check.php`

#### Выполнено:
- ✅ Исправлена структура PHP
- ✅ Полностью переписан CSS (соответствие стилю сайта)
- ✅ Исправлены ссылки на инструменты
- ✅ Обновлен JS для использования `/v1/ip-check`
- ✅ Создан рабочий API endpoint `/v1/ip-check`

#### Файлы:
```
/pages/tools/ip-check.php
/assets/css/pages/tools-ip-check2.css (полностью переписан, 848 строк)
/assets/js/tools-ip-check2.js
/v1/ip-check.php (создан)
```

#### API Endpoint: `/v1/ip-check`
**URL:** `https://sthost.pro/v1/ip-check`
**Метод:** POST
**Аутентификация:** Не требуется
**Rate Limit:** Без ограничений

**Параметры запроса:**
```json
{
  "ip": "8.8.8.8",
  "options": {
    "checkBlacklists": true,
    "checkThreatIntel": true,
    "checkDistance": true
  },
  "user_location": {
    "lat": 50.4501,
    "lng": 30.5234
  }
}
```

**Возможности:**
- Геолокация IP (через ipapi.co)
- Информация о сети (ISP, ASN, организация)
- Проверка по 8 DNSBL (Spamhaus, SpamCop, SORBS, Barracuda, UCEPROTECT, SURBL, CBL, PSBL)
- Анализ угроз
- Расчет расстояния (формула Haversine)
- Погода в регионе IP (через Open-Meteo API)
- Поддержка IPv4 и IPv6

---

## Интеграция с WHMCS

### Калькулятор облачного хостинга

**Файл:** `/assets/js/pages/hosting-cloud.js`

#### Функция заказа:
```javascript
function orderCloud() {
    // Формирование конфигурации
    const config = {
        cpu: currentConfig.cpu,
        ram: currentConfig.ram,
        storage: currentConfig.storage,
        bandwidth: currentConfig.bandwidth,
        billing: currentBilling,
        options: getSelectedOptions()
    };

    // Переход в WHMCS корзину
    window.location.href = `/billing/cart.php?a=add&pid=cloud&config=${encodeURIComponent(JSON.stringify(config))}`;
}
```

#### Что нужно настроить в WHMCS:

1. **Создать продукт "Cloud VPS"**
   - Product Type: VPS/Dedicated Server
   - Product ID: `cloud` (важно!)
   - Payment Type: Recurring

2. **Настроить Configurable Options:**
   ```
   CPU Cores: 1-32 (шаг 1)
   RAM (GB): 1-64 (шаг 1)
   Storage (GB): 20-2000 (шаг 10)
   Bandwidth (GB): 100-10000 (шаг 100)
   ```

3. **Pricing:**
   - Setup Fee: ₴0
   - Monthly: Рассчитывается через configurable options
   - Annually: Со скидкой 15%

4. **Дополнительные опции (Addons):**
   - Backup: ₴99/мес
   - Monitoring: ₴199/мес
   - DDoS Protection: ₴499/мес
   - Snapshots: ₴149/мес
   - Load Balancer: ₴799/мес
   - Private Network: ₴299/мес

5. **Hook для обработки конфигурации:**

Создать файл `/includes/hooks/cloud_config.php`:

```php
<?php
add_hook('ShoppingCartValidateCheckout', 1, function($vars) {
    if (isset($_GET['config'])) {
        $config = json_decode($_GET['config'], true);

        // Сохранить конфигурацию в custom fields
        $customfields = [
            'CPU Cores' => $config['cpu'],
            'RAM (GB)' => $config['ram'],
            'Storage (GB)' => $config['storage'],
            'Bandwidth (GB)' => $config['bandwidth'],
            'Billing Cycle' => $config['billing']
        ];

        // Добавить в корзину с custom fields
        foreach ($customfields as $field => $value) {
            // Логика добавления custom fields
        }

        // Добавить аддоны если выбраны
        if (isset($config['options'])) {
            foreach ($config['options'] as $option => $enabled) {
                if ($enabled) {
                    // Добавить addon в корзину
                }
            }
        }
    }
});
```

---

## Настройка VPS

### Проверка доступности сайтов (Site Check)

#### Требования сервера:
- PHP 7.4+
- cURL extension
- OpenSSL extension

#### Конфигурация для множественных локаций:

Для реальной проверки с разных локаций нужно настроить:

**Вариант 1: Использовать прокси-серверы**

В файле `/v1/site-check.php` добавить:

```php
$proxies = [
    'kyiv' => null, // Локальный сервер
    'frankfurt' => 'proxy.frankfurt.example.com:8080',
    'london' => 'proxy.london.example.com:8080',
    'nyc' => 'proxy.nyc.example.com:8080',
    'singapore' => 'proxy.singapore.example.com:8080',
    'tokyo' => 'proxy.tokyo.example.com:8080',
];

function checkSite($url, $location, $checkSSL, $followRedirects, $timeout) {
    global $proxies;

    $ch = curl_init();

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        // ... другие опции
    ];

    // Если есть прокси для этой локации
    if (isset($proxies[$location]) && $proxies[$location]) {
        $options[CURLOPT_PROXY] = $proxies[$location];
    }

    curl_setopt_array($ch, $options);
    // ... остальной код
}
```

**Вариант 2: Распределенная архитектура**

Разместить скрипты проверки на серверах в разных локациях и делать API запросы к ним.

---

### Проверка IP адресов (IP Check)

#### Требования сервера:
- PHP 7.4+
- cURL extension
- Доступ к внешним API (ipapi.co, open-meteo.com)

#### API ключи (если нужны):

Если бесплатных лимитов ipapi.co не хватит, зарегистрировать платный аккаунт и добавить в `/v1/ip-check.php`:

```php
define('IPAPI_KEY', 'ваш_api_key');

function getIPLocation($ip) {
    $url = "https://api.ipapi.com/{$ip}?access_key=" . IPAPI_KEY;
    // ... остальной код
}
```

#### DNSBL проверка

Текущие RBL:
- zen.spamhaus.org
- bl.spamcop.net
- dnsbl.sorbs.net
- b.barracudacentral.org
- dnsbl-1.uceprotect.net
- multi.surbl.org
- cbl.abuseat.org
- psbl.surriel.com

Можно добавить больше в массив `$blacklists`.

---

## Кеширование и производительность

### HAProxy конфигурация

Если CSS/JS не обновляются из-за кеша HAProxy, добавить в конфигурацию:

```
frontend http_front
    # Игнорировать query string для кеширования статики
    acl is_static path_end .css .js .jpg .png .gif .ico .woff .woff2
    acl has_cache_buster url_param(v) -m found

    # Не кешировать файлы с параметром ?v=
    http-request set-header Cache-Control "no-cache" if is_static has_cache_buster
```

### Memcached конфигурация

Если используется Memcached для кеширования страниц:

**В PHP конфиге добавить:**
```php
// /includes/config.php
define('CACHE_STATIC_TTL', 3600); // 1 час для статики
define('CACHE_DYNAMIC_TTL', 300); // 5 минут для динамики

// Versioning для статических файлов
define('ASSETS_VERSION', '1.0.2'); // Увеличивать при обновлении
```

**Использование в коде:**
```php
$additional_css = [
    '/assets/css/pages/tools-site-check.css?v=' . ASSETS_VERSION
];
```

### Очистка кеша после обновления

После деплоя новых версий CSS/JS выполнить:

```bash
# Очистка Memcached
echo "flush_all" | nc localhost 11211

# Перезапуск HAProxy
sudo systemctl reload haproxy

# Очистка браузерного кеша (для пользователей)
# Изменить версию в $additional_css и $additional_js
```

---

## Структура API

### Список всех endpoints:

**URL:** `https://sthost.pro/v1/`

**Ответ:**
```json
{
    "name": "StormHosting API",
    "version": "1.0",
    "status": "active",
    "endpoints": [
        {
            "path": "/v1/site-check",
            "method": "POST",
            "description": "Check website availability and performance",
            "authentication": "Bearer token required",
            "rate_limit": "1000 requests per hour",
            "documentation": "https://sthost.pro/pages/tools/site-check.php#api"
        },
        {
            "path": "/v1/ip-check",
            "method": "POST",
            "description": "IP address lookup: geolocation, blacklist check, ASN info, threat analysis",
            "authentication": "None required",
            "rate_limit": "None",
            "documentation": "https://sthost.pro/pages/tools/ip-check.php"
        }
    ],
    "documentation": "https://sthost.pro/pages/tools/",
    "support": "https://sthost.pro/pages/contacts.php"
}
```

### .htaccess для маршрутизации

Файл `/v1/.htaccess`:
```apache
RewriteEngine On
RewriteBase /v1/

# Перенаправление на endpoints
RewriteRule ^site-check$ site-check.php [L]
RewriteRule ^ip-check$ ip-check.php [L]

# Security Headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "DENY"
Header set X-XSS-Protection "1; mode=block"

# CORS
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization"
```

---

## Устранение неполадок

### CSS не обновляется

**Проблема:** После коммита новых CSS файлов изменения не видны на сайте.

**Решение:**
1. Проверить версионирование в PHP файлах
2. Очистить кеш HAProxy/Memcached
3. Использовать hard refresh в браузере (Ctrl+F5)
4. Временно добавить `time()` вместо версии:
   ```php
   $additional_css = ['/assets/css/pages/tools-site-check.css?v=' . time()];
   ```

### API возвращает 404

**Проблема:** Запросы к `/v1/site-check` возвращают 404.

**Решение:**
1. Проверить `.htaccess` в папке `/v1/`
2. Убедиться что mod_rewrite включен в Apache
3. Проверить права доступа к файлам
4. Проверить логи Apache: `tail -f /var/log/apache2/error.log`

### WHMCS не получает конфигурацию

**Проблема:** При заказе через калькулятор конфигурация не передается в WHMCS.

**Решение:**
1. Проверить URL в `orderCloud()` функции
2. Создать hook в WHMCS для обработки параметра `config`
3. Проверить логи WHMCS
4. Убедиться что custom fields созданы для продукта

### IP Check не работает

**Проблема:** API `/v1/ip-check` не возвращает данные.

**Решение:**
1. Проверить доступ к внешним API:
   ```bash
   curl https://ipapi.co/8.8.8.8/json/
   ```
2. Проверить лимиты ipapi.co (1000 запросов/день бесплатно)
3. При превышении лимита - зарегистрировать платный аккаунт
4. Проверить логи PHP: `tail -f /var/log/php-fpm/error.log`

---

## Изменения в CSS

### Site Check Tool
**Файл:** `/assets/css/pages/tools-site-check.css`

**Изменения:**
- Полностью переписан с нуля (593 строки)
- Использованы цвета основного сайта (#007bff)
- Улучшена читаемость и контраст
- Унифицированы карточки (white bg, #e9ecef border)
- Плавные hover эффекты
- Современный дизайн API секции

### IP Check Tool
**Файл:** `/assets/css/pages/tools-ip-check2.css`

**Изменения:**
- Полностью переписан с нуля (848 строк)
- Соответствие стилю основного сайта
- Убраны пестрые градиенты
- Единый стиль карточек
- Улучшенная типографика
- Оптимизированная адаптивность

---

## Версии файлов

Для отслеживания изменений рекомендуется использовать константу версии:

**В `/includes/config.php` добавить:**
```php
// Версия статических ресурсов
define('ASSETS_VERSION', '1.0.2');
```

**В страницах использовать:**
```php
$additional_css = [
    '/assets/css/pages/tools-site-check.css?v=' . ASSETS_VERSION
];
```

При обновлении CSS/JS увеличивать версию:
```php
define('ASSETS_VERSION', '1.0.3'); // Новая версия
```

---

## Контакты для поддержки

При возникновении проблем:
1. Проверить логи сервера
2. Проверить документацию выше
3. Связаться с разработчиком через GitHub issues

---

**Дата создания:** 2024-11-14
**Версия документа:** 1.0
**Автор:** Claude AI Assistant
