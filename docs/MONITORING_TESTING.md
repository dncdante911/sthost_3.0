# 🧪 Тестирование системы мониторинга

## ✅ Быстрая проверка работоспособности

### 1️⃣ Проверка что конфиг найден

```bash
# На сервере выполните:
php -r "
require '/var/www/www-root/data/www/sthost.pro/includes/monitoring/ServerMonitor.php';
try {
    \$monitor = new ServerMonitor();
    echo '✅ Конфиг загружен успешно!\n';
} catch (Exception \$e) {
    echo '❌ Ошибка: ' . \$e->getMessage() . '\n';
}
"
```

**Должно вывести:** `✅ Конфиг загружен успешно!`

---

### 2️⃣ Проверка API endpoint

```bash
# Тест API (должен вернуть JSON)
curl http://sthost.pro/api/monitoring/status.php?action=all

# С красивым форматированием (если установлен jq)
curl -s http://sthost.pro/api/monitoring/status.php?action=all | jq

# Простой формат
curl -s http://sthost.pro/api/monitoring/status.php?action=all&format=simple | jq
```

**Должно вернуть:**
```json
{
  "success": true,
  "data": {
    "servers": [...],
    "summary": {
      "total": 4,
      "online": 4,
      "offline": 0
    }
  }
}
```

---

### 3️⃣ Проверка страницы Контакты

Откройте в браузере:
```
http://sthost.pro/contacts
```

**Что должно быть:**
- ✅ Секция "Статус серверів" с реальными данными
- ✅ Карточки серверов (ISPmanager, Proxmox, HAProxy, сетевые каналы)
- ✅ Цветные индикаторы статуса (зеленый = онлайн)
- ✅ Автообновление каждые 30 секунд

---

### 4️⃣ Проверка логов

```bash
# Логи PHP
tail -f /var/log/apache2/error.log | grep -i monitoring

# Или для Nginx
tail -f /var/log/nginx/error.log | grep -i monitoring
```

**Не должно быть ошибок типа:**
- ❌ "Configuration file not found"
- ❌ "Failed to connect"
- ❌ "Class not found"

---

## 🔍 Детальное тестирование

### Проверка ISPmanager

```bash
# Тест подключения
curl -k -u admin:ВАШ_ПАРОЛЬ https://192.168.0.250:1500/ispmgr?out=xml&func=stat

# Через API мониторинга
curl -s 'http://sthost.pro/api/monitoring/status.php?action=server&type=ispmanager&id=isp_main' | jq
```

**Должно вернуть данные сервера с метриками CPU, RAM, Disk**

---

### Проверка Proxmox

```bash
# Проверка имени ноды
ssh -p 225 root@192.168.0.4 hostname
# Вывод должен совпадать с 'node' в конфиге

# Тест API Proxmox
curl -k -u root@pam:ВАШ_ПАРОЛЬ https://192.168.0.4:8006/api2/json/version

# Через API мониторинга
curl -s 'http://sthost.pro/api/monitoring/status.php?action=server&type=proxmox&id=pve_main' | jq
```

**Должно вернуть список VM и метрики ноды**

---

### Проверка HAProxy

```bash
# Проверка stats через браузер
# Откройте: http://192.168.0.10:8080/stats
# Введите логин/пароль

# Тест через curl
curl -u admin:ВАШ_ПАРОЛЬ http://192.168.0.10:8080/stats

# Через API мониторинга
curl -s 'http://sthost.pro/api/monitoring/status.php?action=server&type=haproxy&id=haproxy_main' | jq
```

**Должно вернуть статус frontend/backend**

---

### Проверка SNMP (сетевые каналы)

```bash
# На HAProxy сервере
ssh root@192.168.0.10

# Проверка SNMP
snmpget -v2c -c public localhost SNMPv2-MIB::sysDescr.0

# Проверка интерфейсов
snmpwalk -v2c -c public localhost IF-MIB::ifDescr

# Проверка трафика (замените .2 на номер вашего интерфейса)
snmpget -v2c -c public localhost IF-MIB::ifInOctets.2
snmpget -v2c -c public localhost IF-MIB::ifOutOctets.2
```

**Через API мониторинга:**
```bash
curl -s 'http://sthost.pro/api/monitoring/status.php?action=server&type=network&id=wan_ns1' | jq
curl -s 'http://sthost.pro/api/monitoring/status.php?action=server&type=network&id=wan_ns2' | jq
```

**Должно вернуть RX/TX скорости**

---

## 🐛 Типичные ошибки и решения

### Ошибка: "Configuration file not found"

**Причина:** Файл конфига не найден

**Решение:**
```bash
# Проверьте наличие конфига
ls -la /var/www/www-root/data/www/sthost.pro/config/monitoring.config.php

# Если нет - скопируйте из шаблона
cd /var/www/www-root/data/www/sthost.pro
cp config/monitoring.config.sthost.php config/monitoring.config.php

# Заполните пароли
nano config/monitoring.config.php
```

---

### Ошибка: "Failed to connect to 192.168.0.X"

**Причина:** Сервер недоступен или закрыт порт

**Решение:**
```bash
# Проверьте доступность
ping 192.168.0.250  # ISPmanager
ping 192.168.0.4    # Proxmox
ping 192.168.0.10   # HAProxy

# Проверьте порты
nc -zv 192.168.0.250 1500  # ISPmanager
nc -zv 192.168.0.4 8006    # Proxmox
nc -zv 192.168.0.10 8080   # HAProxy stats

# Проверьте файрвол на серверах
ssh -p 224 root@192.168.0.250 'iptables -L | grep 1500'
ssh -p 225 root@192.168.0.4 'iptables -L | grep 8006'
ssh root@192.168.0.10 'iptables -L | grep 8080'
```

---

### Ошибка: "Authentication failed"

**Причина:** Неправильный логин или пароль

**Решение:**
```bash
# Проверьте учетные данные в конфиге
cat /var/www/www-root/data/www/sthost.pro/config/monitoring.config.php | grep password

# Для ISPmanager - проверьте пароль
ssh -p 224 root@192.168.0.250
/usr/local/mgr5/sbin/mgrctl -m ispmgr user.list

# Для Proxmox - проверьте пользователя
ssh -p 225 root@192.168.0.4
pveum user list
```

---

### Ошибка: "SNMP extension not installed"

**Причина:** PHP расширение SNMP не установлено

**Решение:**
```bash
# Установите расширение
apt-get install php-snmp

# Перезапустите веб-сервер
systemctl restart apache2
# или
systemctl restart nginx && systemctl restart php-fpm

# Проверьте
php -m | grep snmp
```

---

### Страница показывает "Мониторинг налаштовується"

**Причина:** Ошибка при получении данных

**Решение:**
```bash
# Проверьте логи
tail -50 /var/log/apache2/error.log | grep monitoring

# Попробуйте API напрямую
curl http://sthost.pro/api/monitoring/status.php?action=all

# Очистите кеш
rm /tmp/ispmanager_*.cache
rm /tmp/proxmox_*.cache
rm /tmp/haproxy_*.cache
rm /tmp/network_*.cache

# Перезагрузите страницу
```

---

### Данные не обновляются автоматически

**Причина:** JavaScript не выполняется

**Решение:**
1. Откройте консоль браузера (F12)
2. Перейдите на вкладку Console
3. Ищите ошибки JavaScript
4. Проверьте что нет блокировки в браузере

---

## 📊 Проверка метрик

### Какие данные должны отображаться:

**ISPmanager (192.168.0.250):**
- ✅ Статус: Онлайн/Офлайн
- ✅ CPU: 0-100%
- ✅ Uptime: 99.X%
- ✅ Відгук: <5ms
- ✅ Навантаження: CPU в %

**Proxmox VE (192.168.0.4):**
- ✅ Статус: Онлайн/Офлайн
- ✅ CPU: 0-100%
- ✅ Uptime: 99.X%
- ✅ Відгук: <5ms
- ✅ Навантаження: CPU в %
- ✅ Кількість VM

**HAProxy (192.168.0.10):**
- ✅ Статус: Онлайн/Офлайн
- ✅ Backends: X/Y BE
- ✅ Відгук: <2ms
- ✅ Навантаження: X sess

**Сетевые каналы:**
- ✅ ns1.sthost.pro: RX/TX скорость
- ✅ ns2.sthost.pro: RX/TX скорость
- ✅ Використання каналу: 0-100%

---

## 🆘 Помощь

Если ничего не помогло:

1. **Соберите информацию:**
```bash
# Создайте файл с информацией о проблеме
cat > /tmp/monitoring_debug.txt <<EOF
=== КОНФИГ ===
$(ls -la /var/www/www-root/data/www/sthost.pro/config/monitoring.config.php)

=== PHP ВЕРСИЯ ===
$(php -v)

=== PHP РАСШИРЕНИЯ ===
$(php -m | grep -E 'curl|json|snmp|xml')

=== API ТЕСТ ===
$(curl -s http://sthost.pro/api/monitoring/status.php?action=all)

=== ЛОГИ (последние 20 строк) ===
$(tail -20 /var/log/apache2/error.log | grep -i monitoring)
EOF

cat /tmp/monitoring_debug.txt
```

2. **Отправьте на:** support@sthost.pro

3. **Или свяжитесь:** @stormhosting_ua в Telegram

---

**Версия:** 1.0.0
**Дата:** 19.11.2024
