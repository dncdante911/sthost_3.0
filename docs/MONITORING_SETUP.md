# 📊 Настройка системы мониторинга серверов

Полное руководство по настройке мониторинга ISPmanager, Proxmox, HAProxy и сетевых каналов.

## 📋 Содержание

1. [Требования](#требования)
2. [Установка](#установка)
3. [Настройка ISPmanager](#настройка-ispmanager)
4. [Настройка Proxmox VE](#настройка-proxmox-ve)
5. [Настройка HAProxy](#настройка-haproxy)
6. [Настройка SNMP для мониторинга сети](#настройка-snmp)
7. [Настройка алертов](#настройка-алертов)
8. [Тестирование](#тестирование)
9. [Решение проблем](#решение-проблем)

---

## 🔧 Требования

### Серверные требования

- PHP 7.4 или выше
- PHP расширения:
  - `curl`
  - `json`
  - `simplexml`
  - `snmp` (для мониторинга сети)
- Доступ к API серверов

### Клиентские требования

- Современный браузер (Chrome, Firefox, Safari, Edge)
- JavaScript включен

---

## 📦 Установка

### 1. Копирование конфигурационного файла

```bash
cd /home/user/sthost_3.0
cp config/monitoring.config.example.php config/monitoring.config.php
```

### 2. Установка SNMP (если нужен мониторинг сети)

#### Ubuntu/Debian:
```bash
sudo apt-get update
sudo apt-get install php-snmp snmp snmp-mibs-downloader
sudo systemctl restart apache2  # или php-fpm
```

#### CentOS/RHEL:
```bash
sudo yum install php-snmp net-snmp net-snmp-utils
sudo systemctl restart httpd  # или php-fpm
```

### 3. Проверка установки

```bash
php -m | grep snmp    # Должно показать "snmp"
```

---

## 🖥️ Настройка ISPmanager

### 1. Создание API пользователя

1. Войдите в ISPmanager как администратор
2. Перейдите в **Настройки** → **Пользователи**
3. Создайте нового пользователя или используйте существующего
4. Запишите имя пользователя и пароль

### 2. Настройка в конфиге

Откройте `config/monitoring.config.php` и заполните секцию `ispmanager`:

```php
'ispmanager' => [
    'enabled' => true,
    'servers' => [
        [
            'id' => 'isp_main',  // Уникальный ID
            'name' => 'ISPmanager Main Server',  // Отображаемое имя
            'host' => 'your-server.com',  // Хост или IP
            'port' => 1500,  // Порт (обычно 1500)
            'username' => 'admin',  // Имя пользователя
            'password' => 'your-password',  // Пароль
            'ssl' => true,  // Использовать HTTPS
        ],
        // Добавьте больше серверов при необходимости
    ],
],
```

### 3. Тестирование подключения

```bash
curl -k -u admin:password https://your-server.com:1500/ispmgr?out=xml&func=stat
```

Если получили XML ответ - подключение работает!

---

## 🖥️ Настройка Proxmox VE

### 1. Создание API токена (рекомендуется)

```bash
# На сервере Proxmox выполните:
pveum user add monitoring@pve --comment "Monitoring User"
pveum aclmod / -user monitoring@pve -role PVEAuditor
pveum user token add monitoring@pve monitoring-token --privsep 0
```

Сохраните сгенерированный токен!

### 2. Или используйте пароль пользователя

Можно использовать существующего пользователя (например, root@pam)

### 3. Настройка в конфиге

```php
'proxmox' => [
    'enabled' => true,
    'servers' => [
        [
            'id' => 'pve_main',
            'name' => 'Proxmox Main Node',
            'host' => 'proxmox.yourserver.com',
            'port' => 8006,
            'node' => 'pve',  // Имя ноды (проверьте в Proxmox: pvesh get /nodes)
            'username' => 'root@pam',  // Или monitoring@pve
            'password' => 'your-password',  // Или токен
            'realm' => 'pam',  // pam или pve
            'ssl_verify' => false,  // true для проверки SSL сертификата
        ],
    ],
],
```

### 4. Получение имени ноды

```bash
# На сервере Proxmox:
hostname
# или
pvesh get /nodes
```

### 5. Тестирование

```bash
curl -k -u root@pam:password https://proxmox.yourserver.com:8006/api2/json/version
```

---

## ⚖️ Настройка HAProxy

### 1. Включение Stats в HAProxy

Отредактируйте `/etc/haproxy/haproxy.cfg`:

```haproxy
# Добавьте секцию stats
listen stats
    bind *:8080
    mode http
    stats enable
    stats uri /stats
    stats refresh 30s
    stats auth admin:your-password  # Логин и пароль для stats
    stats admin if TRUE
```

### 2. Перезапустите HAProxy

```bash
sudo systemctl restart haproxy
```

### 3. Проверка доступа

Откройте в браузере: `http://your-haproxy:8080/stats`

### 4. Настройка в конфиге

```php
'haproxy' => [
    'enabled' => true,
    'servers' => [
        [
            'id' => 'haproxy_main',
            'name' => 'HAProxy Load Balancer',
            'stats_url' => 'http://your-haproxy:8080/stats',
            'stats_user' => 'admin',
            'stats_password' => 'your-password',
            'stats_format' => 'csv',  // csv или json
        ],
    ],
],
```

---

## 🌐 Настройка SNMP

### 1. Установка и настройка SNMP на роутере/коммутаторе

#### Для устройств Cisco:
```
snmp-server community public RO
```

#### Для Linux-серверов:
```bash
sudo apt-get install snmpd
sudo nano /etc/snmp/snmpd.conf
```

Добавьте или раскомментируйте:
```
rocommunity public localhost
agentAddress udp:161
```

Перезапустите:
```bash
sudo systemctl restart snmpd
```

### 2. Определение имени интерфейса

```bash
snmpwalk -v2c -c public localhost IF-MIB::ifDescr
```

Вывод покажет интерфейсы, например:
```
IF-MIB::ifDescr.1 = STRING: lo
IF-MIB::ifDescr.2 = STRING: eth0
IF-MIB::ifDescr.3 = STRING: eth1
```

### 3. Настройка в конфиге

```php
'network' => [
    'enabled' => true,
    'interfaces' => [
        [
            'id' => 'wan_main',
            'name' => 'Main WAN Channel',
            'host' => 'your-router-ip',  // IP роутера/сервера
            'snmp_version' => '2c',  // версия SNMP
            'community' => 'public',  // community string
            'interface' => 'eth0',  // Имя интерфейса из snmpwalk
            'bandwidth' => 1000,  // Пропускная способность в Мбит/с
        ],
    ],
],
```

### 4. Тестирование SNMP

```bash
# Проверка доступности
snmpget -v2c -c public your-router-ip SNMPv2-MIB::sysDescr.0

# Проверка интерфейса
snmpget -v2c -c public your-router-ip IF-MIB::ifInOctets.2
```

---

## 🔔 Настройка алертов

### 1. Настройка порогов

```php
'alerts' => [
    'enabled' => true,
    'thresholds' => [
        'cpu' => 80,      // % загрузки CPU
        'memory' => 85,   // % использования RAM
        'disk' => 90,     // % использования диска
        'network' => 80,  // % использования канала
    ],
],
```

### 2. Уведомления в Telegram (опционально)

#### Создайте бота:
1. Найдите @BotFather в Telegram
2. Отправьте `/newbot`
3. Следуйте инструкциям
4. Сохраните токен

#### Получите Chat ID:
1. Напишите боту любое сообщение
2. Откройте: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
3. Найдите `"chat":{"id":123456789}`

#### Настройте в конфиге:
```php
'notifications' => [
    'telegram' => [
        'enabled' => true,
        'bot_token' => 'your-bot-token',
        'chat_id' => 'your-chat-id',
    ],
],
```

---

## ✅ Тестирование

### 1. Проверка API endpoint

```bash
curl http://your-site.com/api/monitoring/status.php?action=all
```

Должен вернуть JSON с данными серверов.

### 2. Проверка страницы статуса

Откройте в браузере: `http://your-site.com/server-status`

### 3. Очистка кеша

```bash
curl http://your-site.com/api/monitoring/status.php?action=clear-cache
```

---

## 🐛 Решение проблем

### Ошибка: "SNMP extension not installed"

**Решение:**
```bash
sudo apt-get install php-snmp
sudo systemctl restart apache2
```

### Ошибка: "CURL Error: SSL certificate problem"

**Решение:** Отключите проверку SSL в конфиге (только для тестирования!):
```php
'ssl_verify' => false,
```

### Ошибка: "Authentication failed" для Proxmox

**Решение:**
1. Проверьте правильность username (должен быть с @pam или @pve)
2. Убедитесь что пароль правильный
3. Проверьте права пользователя

### Данные не обновляются

**Решение:**
1. Проверьте права на /tmp директорию
2. Очистите кеш вручную:
```bash
rm /tmp/ispmanager_*.cache
rm /tmp/proxmox_*.cache
rm /tmp/haproxy_*.cache
rm /tmp/network_*.cache
```

### Медленная загрузка страницы

**Решение:**
1. Увеличьте время кеширования в конфиге:
```php
'general' => [
    'cache_ttl' => 120,  // 2 минуты вместо 60 секунд
],
```
2. Отключите неиспользуемые типы мониторинга

---

## 📚 Дополнительные ресурсы

- [ISPmanager API Documentation](https://docs.ispsystem.com/ispmanager-6-lite/api)
- [Proxmox VE API Viewer](https://pve.proxmox.com/pve-docs/api-viewer/)
- [HAProxy Stats Documentation](https://www.haproxy.org/download/2.4/doc/management.txt)
- [Net-SNMP Documentation](http://www.net-snmp.org/docs/)

---

## 🆘 Поддержка

Если у вас возникли проблемы:

1. Проверьте логи PHP: `/var/log/apache2/error.log` или `/var/log/php-fpm/error.log`
2. Проверьте права доступа к файлам
3. Убедитесь что все зависимости установлены
4. Свяжитесь с технической поддержкой: support@sthost.pro

---

**Последнее обновление:** 19.11.2024
**Версия:** 1.0.0
