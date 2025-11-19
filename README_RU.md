# 🚀 StormHosting UA - Полная документация (RU)

**Версия:** 3.0
**Автор:** StormHosting UA Team
**Дата обновления:** 2025-11-19

---

## 📋 Содержание

1. [Обзор проекта](#обзор-проекта)
2. [Требования](#требования)
3. [БЫСТРАЯ НАСТРОЙКА](#быстрая-настройка)
4. [Настройка Proxmox VE 9](#настройка-proxmox-ve-9)
5. [Настройка WHMCS / FossBilling](#настройка-whmcs--fossbilling)
6. [Настройка доменов](#настройка-доменов)
7. [Настройка ISPmanager](#настройка-ispmanager)
8. [Настройка платежных систем](#настройка-платежных-систем)
9. [Настройка почты](#настройка-почты)
10. [База данных](#база-данных)
11. [Структура проекта](#структура-проекта)
12. [API документация](#api-документация)
13. [Безопасность](#безопасность)
14. [Деплой](#деплой)
15. [Решение проблем](#решение-проблем)

---

## 🎯 Обзор проекта

StormHosting UA - это современная платформа для предоставления хостинговых услуг с интеграцией:

- ✅ **VPS управление** через Proxmox VE 9
- ✅ **Биллинг** через WHMCS / FossBilling
- ✅ **Управление доменами** (DNS, WHOIS, трансфер)
- ✅ **Хостинг** через ISPmanager
- ✅ **Платежные системы** (LiqPay)
- ✅ **Безопасность** (CSRF, XSS, SQL Injection защита)

---

## 💻 Требования

### Сервер
- **OS**: Ubuntu 22.04 LTS / Debian 11+
- **Web Server**: Apache 2.4+ или Nginx 1.18+
- **PHP**: 7.4 или выше (рекомендуется 8.1)
- **MySQL**: 5.7+ или MariaDB 10.3+
- **SSL**: Let's Encrypt или коммерческий сертификат

### PHP Расширения
```bash
sudo apt install php8.1-{mysql,curl,json,mbstring,xml,zip,gd,intl}
```

### Внешние сервисы
- **Proxmox VE 9** - виртуализация VPS
- **WHMCS / FossBilling** - биллинг система
- **ISPmanager** - управление хостингом
- **LiqPay** - платежная система

---

## ⚡ БЫСТРАЯ НАСТРОЙКА

### Шаг 1: Клонируйте проект

```bash
cd /var/www
git clone https://github.com/yourusername/sthost_3.0.git
cd sthost_3.0
```

### Шаг 2: Создайте .env файл

```bash
cp .env.example .env
nano .env
```

**ОБЯЗАТЕЛЬНО заполните .env файл** (см. [Настройка .env](#настройка-env) ниже)

### Шаг 3: Настройте базу данных

```bash
# Создайте БД
mysql -u root -p

CREATE DATABASE sthostsitedb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sthostdb'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON sthostsitedb.* TO 'sthostdb'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Импортируйте схему
mysql -u sthostdb -p sthostsitedb < migrations/001_initial_schema.sql
```

### Шаг 4: Настройте права доступа

```bash
sudo chown -R www-data:www-data /var/www/sthost_3.0
sudo chmod -R 755 /var/www/sthost_3.0
sudo chmod -R 777 /var/www/sthost_3.0/cache
sudo chmod -R 777 /var/www/sthost_3.0/logs
```

### Шаг 5: Настройте виртуальный хост Apache

```bash
sudo nano /etc/apache2/sites-available/sthost.conf
```

```apache
<VirtualHost *:80>
    ServerName sthost.pro
    ServerAlias www.sthost.pro
    DocumentRoot /var/www/sthost_3.0

    <Directory /var/www/sthost_3.0>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sthost_error.log
    CustomLog ${APACHE_LOG_DIR}/sthost_access.log combined
</VirtualHost>
```

```bash
sudo a2ensite sthost.conf
sudo systemctl reload apache2
```

### Шаг 6: Установите SSL

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d sthost.pro -d www.sthost.pro
```

---

## 🔧 Настройка .env

### Файл: `/home/user/sthost_3.0/.env`

Скопируйте `.env.example` и заполните все значения:

```bash
# ========================================
# БАЗА ДАННЫХ САЙТА
# ========================================
DB_HOST=localhost
DB_NAME_SITE=sthostsitedb
DB_USER_SITE=sthostdb
DB_PASSWORD_SITE=ВАШ_ПАРОЛЬ_ЗДЕСЬ

# ========================================
# БАЗА ДАННЫХ WHMCS (если используете)
# ========================================
DB_NAME_WHMCS=whmcs_sthost
DB_USER_WHMCS=whmcs_sthost
DB_PASSWORD_WHMCS=WHMCS_ПАРОЛЬ_ЗДЕСЬ

# ========================================
# PROXMOX VE 9 (VPS УПРАВЛЕНИЕ)
# ========================================
PROXMOX_HOST=192.168.0.4                  # IP вашего Proxmox сервера
PROXMOX_PORT=8006
PROXMOX_USER=root
PROXMOX_REALM=pam
PROXMOX_PASSWORD=                         # Пароль root (или используйте токены)
PROXMOX_NODE=pve                          # Имя ноды (узнайте: pvesh get /nodes)

# PROXMOX API ТОКЕНЫ (РЕКОМЕНДУЕТСЯ!)
PROXMOX_TOKEN_ID=root@pam!sthost-api      # Формат: user@realm!token-name
PROXMOX_TOKEN_SECRET=ВАШ_ТОКЕН_ЗДЕСЬ      # Секрет токена из Proxmox

PROXMOX_VERIFY_SSL=false                  # true если есть валидный SSL
PROXMOX_STORAGE=local-lvm                 # Хранилище для дисков VPS
PROXMOX_BRIDGE=vmbr0                      # Сетевой мост

# ========================================
# WHMCS API (БИЛЛИНГ)
# ========================================
WHMCS_URL=https://bill.sthost.pro         # URL вашего WHMCS
WHMCS_API_URL=https://bill.sthost.pro/includes/api.php
WHMCS_API_IDENTIFIER=                     # API Identifier из WHMCS Admin
WHMCS_API_SECRET=                         # API Secret из WHMCS Admin

# ========================================
# FOSSBILLING (если используете вместо WHMCS)
# ========================================
FOSSBILLING_API_KEY=                      # API ключ из FossBilling Admin

# ========================================
# ISPMANAGER (УПРАВЛЕНИЕ ХОСТИНГОМ)
# ========================================
ISPMANAGER_URL=https://cp.sthost.pro
ISPMANAGER_USER=root
ISPMANAGER_PASS=ПАРОЛЬ_ISPMANAGER

# ========================================
# ПЛАТЕЖНЫЕ СИСТЕМЫ
# ========================================
LIQPAY_PUBLIC_KEY=ВАШ_PUBLIC_KEY
LIQPAY_PRIVATE_KEY=ВАШ_PRIVATE_KEY

# ========================================
# ПОЧТА (SMTP)
# ========================================
SMTP_HOST=mail.sthost.pro
SMTP_PORT=587
SMTP_USERNAME=support@sthost.pro
SMTP_PASSWORD=ПОЧТОВЫЙ_ПАРОЛЬ

# ========================================
# TELEGRAM УВЕДОМЛЕНИЯ (опционально)
# ========================================
TELEGRAM_BOT_TOKEN=                       # Токен вашего бота
TELEGRAM_CHAT_ID=                         # ID чата для уведомлений

# ========================================
# RECAPTCHA (защита от ботов)
# ========================================
RECAPTCHA_SITE_KEY=                       # Site Key из Google reCAPTCHA
RECAPTCHA_SECRET_KEY=                     # Secret Key из Google reCAPTCHA

# ========================================
# API КЛЮЧИ (опционально)
# ========================================
WHOIS_API_KEY=                            # Если используете платный WHOIS API
SITE_CHECK_API_KEY=                       # Для проверки сайтов
```

---

## 🖥️ Настройка Proxmox VE 9

### ГДЕ настраивать Proxmox:

1. **Файл конфигурации**: `/home/user/sthost_3.0/includes/config.php`
2. **Переменные окружения**: `/home/user/sthost_3.0/.env`
3. **Класс управления**: `/home/user/sthost_3.0/includes/classes/ProxmoxManager.php` (УЖЕ настроен!)

### Способ 1: API Токен (РЕКОМЕНДУЕТСЯ!)

#### Создание API токена в Proxmox:

1. Откройте Proxmox Web UI: `https://192.168.0.4:8006`
2. Войдите как root
3. Перейдите: **Datacenter → Permissions → API Tokens**
4. Нажмите **"Add"**
5. Заполните:
   - **User**: `root@pam`
   - **Token ID**: `sthost-api` (можете выбрать любое имя)
   - **Privilege Separation**: ✅ **СНИМИТЕ галочку** (токен будет иметь права root)
6. Нажмите **"Add"**
7. **ВАЖНО**: Скопируйте **Token ID** и **Secret** НЕМЕДЛЕННО! (показывается только один раз)

**Пример вывода:**
```
Token ID: root@pam!sthost-api
Secret: 12345678-abcd-1234-5678-123456789abc
```

#### Добавьте в .env:

```bash
PROXMOX_TOKEN_ID=root@pam!sthost-api
PROXMOX_TOKEN_SECRET=12345678-abcd-1234-5678-123456789abc
```

### Способ 2: Пароль (НЕ рекомендуется!)

```bash
PROXMOX_PASSWORD=ВАШ_ROOT_ПАРОЛЬ
```

### Узнайте имя ноды Proxmox:

```bash
# SSH на Proxmox сервер
ssh root@192.168.0.4

# Выполните команду
pvesh get /nodes

# Скопируйте имя ноды (обычно 'pve')
```

Добавьте в .env:
```bash
PROXMOX_NODE=pve  # или другое имя из вывода pvesh
```

### Где используется Proxmox:

| Файл | Назначение |
|------|-----------|
| `/includes/classes/ProxmoxManager.php` | **Основной класс** - все методы для управления VPS (700+ строк) |
| `/api/vps/control.php` | Управление VPS (start/stop/restart) |
| `/api/vps/delete.php` | Удаление VPS |
| `/api/vps/order.php` | Создание новых VPS |
| `/client/vps.php` | Клиентская панель управления VPS |
| `/pages/vds/virtual.php` | Страница заказа VPS |

### Доступные методы ProxmoxManager:

```php
require_once '/var/www/sthost_3.0/includes/classes/ProxmoxManager.php';

$proxmox = new ProxmoxManager();

// 1. Аутентификация
$proxmox->authenticate();

// 2. Создание VPS
$proxmox->createVPS([
    'name' => 'web-server-01',
    'memory' => 4096,
    'cpu_cores' => 2,
    'disk_size' => 50,
    'os_template' => 'ubuntu'
]);

// 3. Управление VPS
$proxmox->controlVPS($vmid, 'start');   // Запуск
$proxmox->controlVPS($vmid, 'stop');    // Остановка
$proxmox->controlVPS($vmid, 'restart'); // Перезагрузка

// 4. Статус и ресурсы
$status = $proxmox->getVPSStatus($vmid);
$usage = $proxmox->getResourceUsage($vmid);

// 5. VNC консоль
$vnc = $proxmox->getVNCInfo($vmid);

// 6. Снапшоты
$proxmox->createSnapshot($vmid, 'backup-2025-11-19');

// 7. Изменение ресурсов
$proxmox->resizeRAM($vmid, 8192);       // 8 GB
$proxmox->resizeDisk($vmid, 'scsi0', 100); // +100 GB

// 8. Удаление
$proxmox->deleteVPS($vmid);

// 9. Список всех VPS
$all_vps = $proxmox->listAllVPS();

// 10. Получение темплейтов
$templates = $proxmox->getTemplates();

// 11. Переустановка VPS
$proxmox->reinstallVPS($vmid, $template_id);

// 12. Список VPS
$vps_list = $proxmox->listAllVPS();
```

### Тест подключения к Proxmox:

```bash
# Создайте тестовый файл
cat > /tmp/test_proxmox.php << 'EOF'
<?php
define('SECURE_ACCESS', true);
require_once '/var/www/sthost_3.0/includes/config.php';
require_once '/var/www/sthost_3.0/includes/classes/ProxmoxManager.php';

$proxmox = new ProxmoxManager();

if ($proxmox->authenticate()) {
    echo "✅ Proxmox подключен успешно!\n";
    $result = $proxmox->listAllVPS();
    print_r($result);
} else {
    echo "❌ Ошибка подключения к Proxmox\n";
}
EOF

# Запустите тест
php /tmp/test_proxmox.php
```

---

## 💳 Настройка WHMCS / FossBilling

### ГДЕ настраивать WHMCS:

1. **Файл конфигурации**: `/home/user/sthost_3.0/includes/config.php`
2. **Переменные окружения**: `/home/user/sthost_3.0/.env`
3. **Класс WHMCS API**: `/home/user/sthost_3.0/includes/classes/WHMCSAPI.php` (УЖЕ настроен!)

### Шаг 1: Получите API Credentials из WHMCS

1. Войдите в **WHMCS Admin Panel**: `https://bill.sthost.pro/admin`
2. Перейдите: **Setup → Staff Management → Manage API Credentials**
3. Нажмите **"Generate New API Credential"**
4. Заполните:
   - **Credential Name**: `StHost Website API`
   - **Permissions**: Выберите **Admin** (или необходимые права)
5. Нажмите **"Generate"**
6. **ВАЖНО**: Скопируйте **Identifier** и **Secret** НЕМЕДЛЕННО!

**Пример вывода:**
```
Identifier: AbCdEfGh123456
Secret: xYz789AbCdEf123456789...
```

### Шаг 2: Добавьте IP адрес сервера

В той же странице API Credentials:

1. Найдите **"Allowed IP Addresses"**
2. Добавьте IP вашего веб-сервера (например: `192.168.1.100`)
3. Или добавьте `0.0.0.0/0` для тестирования (НЕ для продакшена!)
4. Сохраните

### Шаг 3: Заполните .env

```bash
WHMCS_URL=https://bill.sthost.pro
WHMCS_API_URL=https://bill.sthost.pro/includes/api.php
WHMCS_API_IDENTIFIER=AbCdEfGh123456
WHMCS_API_SECRET=xYz789AbCdEf123456789...
```

### Где используется WHMCS:

| Файл | Назначение |
|------|-----------|
| `/includes/classes/WHMCSAPI.php` | **Основной класс** для работы с WHMCS API |
| `/api/vps/get_list.php` | Получение списка VPS заказов из WHMCS |
| `/includes/whmcs_client.php` | Клиент для WHMCS API запросов |
| `/pages/auth/register.php` | Синхронизация регистрации с WHMCS |
| `/pages/vds/virtual.php` | Создание заказов VPS в WHMCS |

### Тест подключения к WHMCS:

```bash
cat > /tmp/test_whmcs.php << 'EOF'
<?php
define('SECURE_ACCESS', true);
require_once '/var/www/sthost_3.0/includes/config.php';
require_once '/var/www/sthost_3.0/includes/classes/WHMCSAPI.php';

$whmcs = new WHMCSAPI();

// Тест: получить список заказов
$result = $whmcs->call('GetOrders', ['limitnum' => 1]);

if ($result && isset($result['result']) && $result['result'] === 'success') {
    echo "✅ WHMCS подключен успешно!\n";
    print_r($result);
} else {
    echo "❌ Ошибка подключения к WHMCS\n";
    print_r($result);
}
EOF

php /tmp/test_whmcs.php
```

### Альтернатива: FossBilling

Если используете FossBilling вместо WHMCS:

1. Войдите в **FossBilling Admin**: `https://bill.sthost.pro/admin`
2. Перейдите: **System → Settings → API**
3. Сгенерируйте новый API ключ
4. Скопируйте ключ

**Добавьте в .env:**
```bash
FOSSBILLING_API_KEY=YPo9tN0V8Ih0pdHDAKJfBuyNA08CnaHN
```

**Используется в:**
- `/api/vps/get_list.php` (строка 25)

---

## 🌐 Настройка доменов

### ГДЕ настраивать домены:

1. **База данных**: Таблица `domain_zones`
2. **API файлы**: `/home/user/sthost_3.0/api/domains/*.php`
3. **Страницы**: `/home/user/sthost_3.0/pages/domains/*.php`

### Заполнение базы данных доменов

Выполните SQL запрос для добавления доменных зон:

```sql
-- Файл: /var/www/sthost_3.0/migrations/002_domain_zones.sql

USE sthostsitedb;

INSERT INTO domain_zones (zone, description, price_registration, price_transfer, price_renewal, is_active) VALUES
-- Украинские домены
('.ua', 'Украинский домен', 300.00, 300.00, 300.00, 1),
('.com.ua', 'Коммерческий украинский', 120.00, 120.00, 120.00, 1),
('.kiev.ua', 'Киевский региональный', 150.00, 150.00, 150.00, 1),
('.net.ua', 'Сетевой украинский', 120.00, 120.00, 120.00, 1),
('.org.ua', 'Организационный украинский', 120.00, 120.00, 120.00, 1),

-- Международные домены
('.com', 'Коммерческий', 450.00, 400.00, 450.00, 1),
('.net', 'Сетевой', 500.00, 450.00, 500.00, 1),
('.org', 'Организационный', 450.00, 400.00, 450.00, 1),
('.info', 'Информационный', 450.00, 400.00, 450.00, 1),
('.biz', 'Бизнес', 450.00, 400.00, 450.00, 1),

-- Новые популярные зоны
('.club', 'Клуб/Сообщество', 450.00, 400.00, 450.00, 1),
('.pro', 'Профессиональный', 600.00, 550.00, 600.00, 1),
('.online', 'Онлайн бизнес', 350.00, 300.00, 350.00, 1),
('.site', 'Сайт', 350.00, 300.00, 350.00, 1),
('.store', 'Магазин', 500.00, 450.00, 500.00, 1),
('.tech', 'Технологии', 500.00, 450.00, 500.00, 1),
('.space', 'Пространство', 300.00, 250.00, 300.00, 1),
('.xyz', 'Универсальный', 300.00, 250.00, 300.00, 1);
```

Выполните:
```bash
mysql -u sthostdb -p sthostsitedb < /var/www/sthost_3.0/migrations/002_domain_zones.sql
```

### Файлы доменных инструментов:

| Файл | Назначение |
|------|-----------|
| **WHOIS Lookup** | |
| `/pages/domains/whois.php` | Страница WHOIS поиска |
| `/assets/css/pages/whois-lookup.css` | Стили страницы WHOIS |
| `/assets/js/whois-lookup.js` | JavaScript для WHOIS |
| `/api/domains/whois.php` | **API WHOIS запросов** (работает с 50+ TLD) |
| **DNS Lookup** | |
| `/pages/domains/dns.php` | Страница DNS поиска |
| `/assets/css/pages/dns-lookup.css` | Стили страницы DNS |
| `/assets/js/dns-lookup.js` | JavaScript для DNS |
| `/api/domains/dns.php` | **API DNS запросов** (A, AAAA, MX, TXT, NS, SOA, SRV) |
| **Domain Transfer** | |
| `/pages/domains/transfer.php` | Страница трансфера доменов |
| `/assets/css/pages/transfer-form.css` | Стили формы трансфера |
| `/assets/js/transfer-form.js` | JavaScript формы трансфера |
| `/api/domains/transfer.php` | **API трансфера** (сохранение в БД, email уведомления) |

### Важные особенности WHOIS API:

**Файл**: `/home/user/sthost_3.0/api/domains/whois.php` (строки 32-56)

WHOIS API поддерживает **50+ доменных зон**:

```php
$whois_servers = [
    '.ua' => 'whois.ua',
    '.com.ua' => 'whois.ua',
    '.com' => 'whois.verisign-grs.com',
    '.club' => 'whois.nic.club',
    '.pro' => 'whois.afilias.net',
    // ... еще 45+ зон
];

// УНИВЕРСАЛЬНЫЙ FALLBACK для ВСЕХ доменов
if (!$whois_server) {
    $whois_server = 'whois.iana.org'; // Поддерживает ВСЕ домены
}
```

**Критическая логика определения статуса** (строки 188-227):

```php
// 1. Если есть данные регистрации - домен ЗАНЯТ
if (!empty($registrar) || !empty($creation_date) || !empty($name_servers)) {
    return 'registered';
}

// 2. Проверка специфичных паттернов "свободен"
$patterns = [
    '/^no match for/im',
    '/domain not found/im',
    '/status:\s*free/im',
    // ... 12 точных паттернов
];

// 3. Если нет четких маркеров - домен ЗАНЯТ
return 'registered';
```

**Это исправляет баг где занятые домены показывались как свободные!**

---

## 🏢 Настройка ISPmanager

### ГДЕ настраивать ISPmanager:

**Файл**: `/home/user/sthost_3.0/includes/config.php` (строки 89-92)

```php
define('ISPMANAGER_URL', env('ISPMANAGER_URL', 'https://cp.sthost.pro'));
define('ISPMANAGER_USER', env('ISPMANAGER_USER', 'root'));
define('ISPMANAGER_PASS', env('ISPMANAGER_PASS', ''));
```

**В .env файле:**

```bash
ISPMANAGER_URL=https://cp.sthost.pro
ISPMANAGER_USER=root
ISPMANAGER_PASS=ВАШ_ПАРОЛЬ_ISPMANAGER
```

### Где используется:

| Файл | Назначение |
|------|-----------|
| `/client/hosting.php` | Клиентская панель управления хостингом (редирект на ISPmanager) |
| `/client/domains.php` | Управление DNS записями (редирект на ISPmanager) |
| `/pages/hosting/*.php` | Страницы хостинговых тарифов |

**Примечание**: Сейчас ISPmanager используется только для редиректов. Если нужна полная интеграция через API - дополнительная настройка требуется.

---

## 💰 Настройка платежных систем

### LiqPay (Украинская платежная система)

**ГДЕ настраивать**: `/home/user/sthost_3.0/includes/config.php` (строки 85-87)

1. Зарегистрируйтесь на https://www.liqpay.ua
2. Создайте приложение
3. Получите **Public Key** и **Private Key**

**В .env файле:**

```bash
LIQPAY_PUBLIC_KEY=sandbox_i00000000
LIQPAY_PRIVATE_KEY=sandbox_aaaaaaaaaaaaaaaaaaaaa
```

**Где используется**:
- Пока не интегрировано полностью
- Подготовлены константы для будущей интеграции

---

## 📧 Настройка почты (SMTP)

**ГДЕ настраивать**: `/home/user/sthost_3.0/includes/config.php` (строки 129-133)

```bash
SMTP_HOST=mail.sthost.pro
SMTP_PORT=587
SMTP_USERNAME=support@sthost.pro
SMTP_PASSWORD=ВАШ_ПОЧТОВЫЙ_ПАРОЛЬ
```

### Где используется:

| Файл | Назначение |
|------|-----------|
| `/api/domains/transfer.php` | Отправка уведомлений о трансфере доменов |
| `/pages/api/send-contact.php` | Отправка контактных форм |
| `/pages/info/send-complaint.php` | Отправка жалоб |

### Пример использования:

```php
// В /api/domains/transfer.php (строки 80-100)
$to_admin = SITE_EMAIL;
$subject = "Новая заявка на трансфер домена: {$domain}";

mail($to_admin, $subject, $message, $headers);
```

---

## 🗄️ База данных

### Структура БД

Проект использует **2 базы данных**:

1. **sthostsitedb** - основная БД сайта
2. **whmcs_sthost** - БД WHMCS (если используете)

### Главные таблицы:

| Таблица | Назначение |
|---------|-----------|
| `users` | Пользователи сайта |
| `domain_zones` | Доменные зоны и цены |
| `domain_transfer_requests` | Заявки на трансфер доменов |
| `vps_instances` | VPS серверы клиентов |
| `vps_operations_log` | Лог операций с VPS |
| `remember_tokens` | Токены "Запомнить меня" |

### Миграции:

Все миграции находятся в `/home/user/sthost_3.0/migrations/`

```bash
# Применить все миграции
for file in /var/www/sthost_3.0/migrations/*.sql; do
    echo "Applying $file..."
    mysql -u sthostdb -p sthostsitedb < "$file"
done
```

### Важные миграции:

1. **001_initial_schema.sql** - Начальная схема
2. **002_domain_zones.sql** - Доменные зоны
3. **003_vps_tables.sql** - Таблицы VPS

---

## 📂 Структура проекта

```
sthost_3.0/
├── .env                          # ⚙️ ГЛАВНЫЙ ФАЙЛ КОНФИГУРАЦИИ (создайте из .env.example)
├── .env.example                  # Шаблон .env файла
│
├── includes/                     # 🔧 Основные файлы
│   ├── config.php               # ⭐ Главная конфигурация (читает .env)
│   ├── db_connect.php           # Подключение к БД
│   ├── env_loader.php           # Загрузчик .env переменных
│   ├── auth/
│   │   └── check_auth.php       # Аутентификация + CSRF токены
│   └── classes/
│       ├── ProxmoxManager.php   # ⭐ VPS управление (700+ строк)
│       ├── WHMCSAPI.php         # ⭐ WHMCS API клиент
│       └── VPSManager.php       # Дополнительный VPS менеджер
│
├── api/                          # 🚀 REST API
│   ├── domains/
│   │   ├── dns.php              # ⭐ DNS запросы
│   │   ├── whois.php            # ⭐ WHOIS запросы (исправлен баг!)
│   │   └── transfer.php         # ⭐ Трансфер доменов
│   └── vps/
│       ├── get_list.php         # ⭐ Список VPS (из WHMCS/FossBilling)
│       ├── control.php          # ⭐ Управление VPS (start/stop/restart)
│       ├── delete.php           # ⭐ Удаление VPS
│       └── order.php            # Создание VPS
│
├── pages/                        # 📄 Публичные страницы
│   ├── domains/
│   │   ├── whois.php            # ⭐ Страница WHOIS
│   │   ├── dns.php              # ⭐ Страница DNS
│   │   └── transfer.php         # ⭐ Страница трансфера
│   └── vds/
│       └── virtual.php          # Заказ VPS
│
├── client/                       # 👤 Клиентская панель
│   ├── dashboard-new.php        # ⭐ Главная панель
│   ├── vps.php                  # ⭐ Управление VPS
│   ├── domains.php              # ⭐ Управление доменами
│   ├── hosting.php              # Управление хостингом
│   └── settings.php             # Настройки аккаунта
│
├── assets/                       # 🎨 Статические файлы
│   ├── css/pages/
│   │   ├── whois-lookup.css     # ⭐ Стили WHOIS
│   │   ├── dns-lookup.css       # ⭐ Стили DNS
│   │   └── transfer-form.css    # ⭐ Стили трансфера
│   └── js/
│       ├── whois-lookup.js      # ⭐ JS WHOIS
│       ├── dns-lookup.js        # ⭐ JS DNS
│       └── transfer-form.js     # ⭐ JS трансфера
│
└── migrations/                   # 📊 Миграции БД
    ├── 001_initial_schema.sql
    └── 002_domain_zones.sql
```

**⭐ = Критически важные файлы**

---

## 🔐 Безопасность

### Реализованная защита:

1. **SQL Injection**: PDO prepared statements везде
2. **XSS**: `htmlspecialchars()` на всех выводах
3. **CSRF**: Токены на всех формах
4. **Session Hijacking**: Регенерация session ID
5. **Rate Limiting**: 100 запросов/минуту
6. **Secure Cookies**: httponly, secure, samesite

### CSRF Защита

**Файл**: `/home/user/sthost_3.0/includes/auth/check_auth.php` (строки 132-147)

```php
// Генерация CSRF токена
function getCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Проверка CSRF токена
function verifyCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}
```

**Использование в VPS control** (`/client/vps.php`):

```javascript
const CSRF_TOKEN = '<?php echo $_SESSION['csrf_token']; ?>';

fetch('/api/vps/control.php', {
    body: JSON.stringify({
        csrf_token: CSRF_TOKEN
    })
});
```

---

## 🚀 Деплой

### Production Checklist:

- [ ] Заполнить ВСЕ значения в `.env` файле
- [ ] Сменить `PROXMOX_VERIFY_SSL=true`
- [ ] Настроить SSL сертификат
- [ ] Создать резервные копии БД
- [ ] Проверить права доступа (755 для папок, 644 для файлов)
- [ ] Настроить логи: `/var/www/sthost_3.0/logs/`
- [ ] Убрать debug режим в config.php
- [ ] Проверить CSRF токены работают
- [ ] Протестировать все API endpoints
- [ ] Настроить мониторинг (UptimeRobot, New Relic)

---

## 🔧 Решение проблем

### Проблема: VPS не запускается

**Решение**:
1. Проверьте CSRF токен в `/client/vps.php` (строка 590)
2. Проверьте Proxmox credentials в `.env`
3. Проверьте логи: `/var/www/sthost_3.0/logs/error.log`

### Проблема: WHOIS показывает занятые домены как свободные

**Решение**: Эта проблема ИСПРАВЛЕНА! Проверьте файл `/api/domains/whois.php` (строки 188-227)

### Проблема: Цены доменов не загружаются

**Решение**:
1. Проверьте таблицу `domain_zones` заполнена
2. Выполните: `SELECT * FROM domain_zones WHERE is_active = 1;`
3. Проверьте `/pages/domains/transfer.php` (строки 27-50)

### Проблема: База данных не подключается

**Решение**:
1. Проверьте `.env` файл существует
2. Проверьте права: `ls -la /var/www/sthost_3.0/.env`
3. Проверьте MySQL: `mysql -u sthostdb -p`

---

## 📞 Поддержка

**Email**: info@sthost.pro
**Telegram**: @stormhostingua
**GitHub**: https://github.com/stormhostingua/sthost_3.0

---

## 📝 Changelog

### Версия 3.0 (2025-11-19)

#### ✅ Исправлено:
- 🐛 **КРИТИЧНО**: WHOIS занятые домены показывались как свободные
- 🐛 **КРИТИЧНО**: VPS управление не работало (отсутствовал CSRF токен)
- 🐛 Цены трансфера доменов теперь из базы данных
- 🧹 Удалены дубликаты конфигов (config/proxmox.php, config/whmcs.php)

#### ✨ Добавлено:
- ⭐ Полный русский README с точными путями
- ⭐ CSRF защита для всех форм
- ⭐ Универсальная поддержка ВСЕХ доменов в WHOIS (через IANA)
- ⭐ ProxmoxManager класс (700+ строк) с 12 методами

#### 📚 Документация:
- ✅ Точные пути к файлам конфигурации
- ✅ Пошаговые инструкции для Proxmox API токенов
- ✅ Пошаговые инструкции для WHMCS API
- ✅ Полная структура проекта
- ✅ Решение распространенных проблем

---

**Успешного деплоя! 🚀**
