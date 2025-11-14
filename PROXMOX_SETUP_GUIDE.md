# 🚀 Руководство по настройке Proxmox VE 9 для StormHosting UA

## 📋 Содержание
1. [Обзор изменений](#обзор-изменений)
2. [Требования](#требования)
3. [Настройка Proxmox VE 9](#настройка-proxmox-ve-9)
4. [Настройка сайта](#настройка-сайта)
5. [Миграция базы данных](#миграция-базы-данных)
6. [Создание темплейтов](#создание-темплейтов)
7. [Тестирование](#тестирование)
8. [Устранение неполадок](#устранение-неполадок)
9. [Отчет по безопасности](#отчет-по-безопасности)

---

## 🔄 Обзор изменений

### Что было изменено:

✅ **Удалено:**
- `LibvirtManager.php` - класс для работы с libvirt
- Все упоминания libvirt в коде
- Зависимости от PHP-libvirt расширения

✅ **Добавлено:**
- `ProxmoxManager.php` - новый класс для работы с Proxmox VE 9 API
- Поддержка Proxmox API токенов (более безопасно)
- Новые поля в базе данных для Proxmox

✅ **Обновлено:**
- `VPSManager.php` - интегрирован ProxmoxManager
- `config.php` - добавлены настройки Proxmox
- SQL схема - миграция с libvirt на Proxmox

---

## 📋 Требования

### Серверные требования:
- **Proxmox VE 9.x** (или 8.x, совместимо)
- PHP 8.1+ с расширениями: curl, json, mbstring
- MariaDB 10.11+ / MySQL 8.0+
- SSL сертификат для Proxmox (или отключение проверки SSL)

### Права доступа Proxmox:
- **Пользователь:** root@pam или созданный пользователь
- **Права:** VM.Allocate, VM.Config.*, Datastore.AllocateSpace, SDN.Use

---

## ⚙️ Настройка Proxmox VE 9

### Шаг 1: Создание API токена (РЕКОМЕНДУЕТСЯ)

API токены безопаснее, чем использование пароля. Создайте токен:

```bash
# Войдите в Proxmox Web UI
# Перейдите в: Datacenter > Permissions > API Tokens

# Или через CLI:
pveum user token add root@pam mytoken --privsep 0
# Сохраните полученный секрет! Он показывается только ОДИН раз!
```

**Результат:**
```
Token ID: root@pam!mytoken
Secret: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

### Шаг 2: Узнайте имя вашей ноды

```bash
pvesh get /nodes
# Результат:
# ┌──────┬────────┐
# │ node │ status │
# ├──────┼────────┤
# │ pve  │ online │
# └──────┴────────┘
```

Имя ноды (в данном случае `pve`) понадобится в конфигурации.

### Шаг 3: Проверьте доступные хранилища

```bash
pvesh get /storage
# Найдите хранилище для дисков VPS (обычно local-lvm или local-zfs)
```

### Шаг 4: Проверьте сетевые мосты

```bash
ip a | grep vmbr
# Обычно используется vmbr0
```

---

## 🔧 Настройка сайта

### Шаг 1: Обновите config.php

Откройте `/home/user/sthost_3.0/includes/config.php` и заполните:

```php
// Proxmox VE 9 настройки
define('PROXMOX_HOST', '192.168.0.4'); // ← Ваш IP Proxmox
define('PROXMOX_PORT', 8006);
define('PROXMOX_USER', 'root');
define('PROXMOX_REALM', 'pam');
define('PROXMOX_PASSWORD', ''); // Оставьте пустым если используете токен!
define('PROXMOX_NODE', 'pve'); // ← Имя вашей ноды

// Proxmox API Token (РЕКОМЕНДУЕТСЯ!)
define('PROXMOX_TOKEN_ID', 'root@pam!mytoken'); // ← Ваш Token ID
define('PROXMOX_TOKEN_SECRET', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'); // ← Ваш Secret
define('PROXMOX_VERIFY_SSL', false); // true если используете валидный SSL

// VPS настройки для Proxmox
define('PROXMOX_STORAGE', 'local-lvm'); // ← Ваше хранилище
define('PROXMOX_BRIDGE', 'vmbr0'); // ← Ваш сетевой мост
```

### Шаг 2: Тестирование подключения

Создайте тестовый скрипт `/home/user/sthost_3.0/test_proxmox.php`:

```php
<?php
define('SECURE_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/classes/ProxmoxManager.php';

$proxmox = new ProxmoxManager();

echo "🔌 Тестирование подключения к Proxmox...\n";

if ($proxmox->authenticate()) {
    echo "✅ Успешное подключение!\n\n";

    // Получаем список VPS
    $result = $proxmox->listAllVPS();
    if ($result['success']) {
        echo "📊 Найдено VPS: " . count($result['vps_list']) . "\n";
        print_r($result['vps_list']);
    }

    // Получаем список темплейтов
    $templates = $proxmox->getTemplates();
    if ($templates['success']) {
        echo "\n📦 Найдено темплейтов: " . count($templates['templates']) . "\n";
        print_r($templates['templates']);
    }
} else {
    echo "❌ Ошибка подключения!\n";
    echo "Проверьте настройки в config.php\n";
}
?>
```

Запустите:
```bash
php /home/user/sthost_3.0/test_proxmox.php
```

---

## 🗄️ Миграция базы данных

### Шаг 1: Создайте резервную копию

```bash
mysqldump -u sthostdb -p'3344Frz@q0607Dm$157' sthostsitedb > backup_before_proxmox_$(date +%Y%m%d).sql
```

### Шаг 2: Выполните миграцию

```bash
mysql -u sthostdb -p'3344Frz@q0607Dm$157' sthostsitedb < /home/user/sthost_3.0/migrations/migrate_to_proxmox.sql
```

### Шаг 3: Проверьте результат

```bash
mysql -u sthostdb -p'3344Frz@q0607Dm$157' sthostsitedb -e "DESCRIBE vps_instances;"
```

Вы должны увидеть новые поля:
- `proxmox_vmid`
- `proxmox_node`
- `legacy_libvirt_name` (старое поле)

---

## 📦 Создание темплейтов

### Метод 1: Конвертация существующих образов

Если у вас есть qcow2 образы из libvirt:

```bash
# 1. Создайте новую VM в Proxmox
qm create 9000 --name ubuntu-22.04-template --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0

# 2. Импортируйте диск
qm importdisk 9000 /var/lib/libvirt/images/ubuntu22.qcow2 local-lvm

# 3. Подключите диск
qm set 9000 --scsi0 local-lvm:vm-9000-disk-0

# 4. Настройте загрузку
qm set 9000 --boot order=scsi0

# 5. Конвертируйте в темплейт
qm template 9000
```

### Метод 2: Создание через Cloud-Init

```bash
# Ubuntu 22.04
wget https://cloud-images.ubuntu.com/jammy/current/jammy-server-cloudimg-amd64.img

# Создайте VM
qm create 9000 --name ubuntu-22.04-template --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0

# Импортируйте образ
qm importdisk 9000 jammy-server-cloudimg-amd64.img local-lvm

# Настройте VM
qm set 9000 --scsi0 local-lvm:vm-9000-disk-0
qm set 9000 --boot order=scsi0
qm set 9000 --serial0 socket --vga serial0
qm set 9000 --agent enabled=1

# Cloud-Init
qm set 9000 --ide2 local-lvm:cloudinit
qm set 9000 --ipconfig0 ip=dhcp

# Конвертируйте в темплейт
qm template 9000
```

### Шаг 3: Обновите базу данных

```sql
-- Обновите таблицу vps_os_templates с реальными VMID
UPDATE vps_os_templates SET proxmox_template_id = 9000 WHERE name = 'ubuntu-22.04';
UPDATE vps_os_templates SET proxmox_template_id = 9001 WHERE name = 'ubuntu-24.04';
UPDATE vps_os_templates SET proxmox_template_id = 9002 WHERE name = 'centos-8';
-- И т.д.
```

---

## 🧪 Тестирование

### Тест 1: Создание VPS

```php
<?php
define('SECURE_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/classes/ProxmoxManager.php';

$proxmox = new ProxmoxManager();
$proxmox->authenticate();

$config = [
    'name' => 'test-vps',
    'memory' => 1024,
    'cpu_cores' => 1,
    'disk_size' => 10,
    'template_id' => 9000, // Ubuntu 22.04
    'ip_address' => '192.168.1.100',
    'gateway' => '192.168.1.1'
];

$result = $proxmox->createVPS($config);
print_r($result);
?>
```

### Тест 2: Управление VPS

```php
<?php
$vmid = 100; // ID созданного VPS

// Запуск
$result = $proxmox->controlVPS($vmid, 'start');
echo "Start: "; print_r($result);

// Статус
$status = $proxmox->getVPSStatus($vmid);
echo "Status: "; print_r($status);

// Остановка
$result = $proxmox->controlVPS($vmid, 'stop');
echo "Stop: "; print_r($result);
?>
```

---

## 🔍 Устранение неполадок

### Проблема: "Authentication failed"

**Решение:**
1. Проверьте правильность токена/пароля
2. Убедитесь, что токен не истек
3. Проверьте права доступа пользователя

```bash
# Проверка токена
pveum user token list root@pam
```

### Проблема: "SSL certificate verification failed"

**Решение:**
```php
define('PROXMOX_VERIFY_SSL', false);
```

Или установите валидный SSL сертификат на Proxmox.

### Проблема: "VM not found"

**Решение:**
1. Проверьте правильность VMID
2. Убедитесь, что VM создан на правильной ноде
3. Проверьте:
```bash
qm list
```

### Проблема: "No available IP addresses"

**Решение:**
Добавьте IP адреса в пул:

```sql
INSERT INTO vps_ip_pool (ip_address, gateway, netmask, is_reserved) VALUES
('192.168.1.100', '192.168.1.1', '255.255.255.0', 0),
('192.168.1.101', '192.168.1.1', '255.255.255.0', 0);
```

---

## 🛡️ Отчет по безопасности

### ⚠️ КРИТИЧЕСКИЕ УЯЗВИМОСТИ (исправьте немедленно!)

#### 1. Пароли в открытом виде в config.php

**Файл:** `includes/config.php` (строки 20, 25, 89, 103-104, 121)

**Проблема:** Все пароли хранятся в открытом виде в исходном коде.

**Решение:**
Создайте файл `.env`:
```bash
cat > /home/user/sthost_3.0/.env << 'EOF'
DB_SITE_PASSWORD="3344Frz@q0607Dm$157"
DB_WHMCS_PASSWORD="3344Frz@q0607"
ISPMANAGER_PASS="0607Dm$157"
WHMCS_API_IDENTIFIER="cGvOmXc9V8vxV8ABNqfZ3GOkMwuCIFB5"
WHMCS_API_SECRET="U0aRUDUgCNaQC7CZDbfYiA0a7tGfmab6"
SMTP_PASSWORD="0607Dm$157"
PROXMOX_PASSWORD="ваш_пароль_proxmox"
PROXMOX_TOKEN_SECRET="ваш_секрет_токена"
EOF

chmod 600 /home/user/sthost_3.0/.env
```

Добавьте в `.gitignore`:
```bash
echo ".env" >> /home/user/sthost_3.0/.gitignore
```

Установите библиотеку для работы с .env:
```bash
composer require vlucas/phpdotenv
```

Обновите config.php:
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$db_passwd_site = $_ENV['DB_SITE_PASSWORD'];
$db_passwd_whmcs = $_ENV['DB_WHMCS_PASSWORD'];
// и т.д.
```

#### 2. Отсутствие CSRF защиты в API

**Файлы:** `api/auth/login.php`, `api/auth/register.php`, `api/user/*.php`

**Решение:**
Добавьте в начало каждого API файла:

```php
// Проверка CSRF токена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? $input['csrf_token'] ?? '';
    if (!validateCSRFToken($csrf_token)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
    }
}
```

#### 3. Command Injection в LibvirtManager.php

**ВАЖНО:** Хотя LibvirtManager больше не используется, если вы храните его для истории, добавьте защиту:

```php
$cmd = sprintf(
    "qemu-img create -f qcow2 -b %s %s %sG",
    escapeshellarg($template_path),
    escapeshellarg($disk_path),
    escapeshellarg($config['disk_size'])
);
```

### ⚠️ СРЕДНИЕ УЯЗВИМОСТИ

#### 4. Закомментирована регенерация session ID

**Файл:** `includes/config.php` (строки 142-145)

**Решение:**
```php
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}
```

#### 5. Отсутствует .htaccess в /uploads/

**Решение:**
Создайте файл `/home/user/sthost_3.0/uploads/.htaccess`:
```apache
# Запрет выполнения PHP файлов
<FilesMatch "\.ph(p[3-7]?|tml|ar)$">
    Deny from all
</FilesMatch>

# Разрешить только определенные типы файлов
<FilesMatch "\.(jpg|jpeg|png|gif|webp|pdf|txt)$">
    Allow from all
</FilesMatch>

Order Deny,Allow
Deny from all
```

---

## ✅ Проверочный список после миграции

- [ ] Proxmox API токен создан и настроен
- [ ] config.php обновлен с правильными настройками
- [ ] SQL миграция выполнена успешно
- [ ] Темплейты созданы и добавлены в базу
- [ ] Тестовый VPS создан успешно
- [ ] Управление VPS (старт/стоп/перезагрузка) работает
- [ ] VNC/Console доступен
- [ ] Snapshot создание работает
- [ ] Удаление VPS работает
- [ ] Пароли перенесены в .env файл
- [ ] CSRF защита добавлена в API
- [ ] .htaccess создан в /uploads/
- [ ] Регенерация session ID раскомментирована

---

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи Proxmox: `/var/log/pve/tasks/`
2. Проверьте логи PHP: `/var/log/php/error.log`
3. Проверьте логи веб-сервера: `/var/log/nginx/error.log`

---

## 🎉 Готово!

Ваш сайт теперь использует Proxmox VE 9 вместо libvirt!

**Следующие шаги:**
1. Протестируйте все функции VPS
2. Создайте несколько тестовых VPS
3. Обновите документацию для пользователей
4. Удалите старые libvirt файлы (если не нужны)

---

*Документация обновлена: 2025-11-14*
*Версия: 1.0*
