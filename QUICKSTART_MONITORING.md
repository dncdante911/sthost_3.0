# ⚡ БЫСТРЫЙ СТАРТ - Мониторинг StormHosting

## 🚀 Запуск за 5 минут

### 1️⃣ Автоматическая установка

```bash
cd /home/user/sthost_3.0
sudo ./scripts/setup-monitoring.sh
```

Скрипт автоматически:
- ✅ Проверит PHP и расширения
- ✅ Установит недостающие компоненты
- ✅ Создаст конфигурацию
- ✅ Протестирует подключения

---

### 2️⃣ Заполните пароли

Откройте конфиг:
```bash
nano config/monitoring.config.php
```

Замените **YOUR_PASSWORD** на реальные пароли:

#### ISPmanager (192.168.0.250)
```php
'password' => 'ваш_пароль_ispmanager',  // Логин: admin
```

**Где взять:**
```bash
ssh -p 224 root@192.168.0.250
# Если забыли пароль:
/usr/local/mgr5/sbin/mgrctl -m ispmgr user.passwd.set username=admin password=НОВЫЙ_ПАРОЛЬ
```

#### Proxmox (192.168.0.4)
```php
'password' => 'ваш_root_пароль',        // Логин: root@pam
```

**Где взять:** Пароль root сервера Proxmox

#### HAProxy (192.168.0.10)
```php
'stats_password' => 'ваш_stats_пароль',  // Логин: admin
```

**Где взять:** Настроить stats в HAProxy:
```bash
ssh root@192.168.0.10
nano /etc/haproxy/haproxy.cfg

# Добавить в конец:
listen stats
    bind *:8080
    mode http
    stats enable
    stats uri /stats
    stats auth admin:ваш_пароль

# Перезапустить:
systemctl restart haproxy
```

#### SNMP (для мониторинга каналов)
```php
'community' => 'public',  // SNMP community string
```

**Где настроить:** На HAProxy сервере:
```bash
ssh root@192.168.0.10
apt-get install snmpd
nano /etc/snmp/snmpd.conf

# Добавить:
rocommunity public localhost
agentAddress udp:161

systemctl restart snmpd
```

---

### 3️⃣ Проверьте имя ноды Proxmox

```bash
ssh -p 225 root@192.168.0.4
hostname
# Вывод: pve (или другое)

# В конфиге укажите:
'node' => 'pve',  // То что вывела команда hostname
```

---

### 4️⃣ Узнайте имена сетевых интерфейсов

```bash
ssh root@192.168.0.10
snmpwalk -v2c -c public localhost IF-MIB::ifDescr

# Вывод покажет:
# IF-MIB::ifDescr.2 = STRING: eth0
# IF-MIB::ifDescr.3 = STRING: eth1

# В конфиге укажите правильные интерфейсы для каждого канала
```

---

### 5️⃣ Откройте страницу мониторинга

```
http://sthost.pro/server-status
```

Вы должны увидеть:
- 📊 Статус всех 4 серверов
- 💻 Загрузка CPU, RAM, дисков
- 🌐 Трафик по каналам ns1 и ns2
- ⚖️ Статус HAProxy балансировщика

---

## 🧪 Проверка работы

### Тест API:
```bash
curl http://localhost/api/monitoring/status.php?action=all | jq
```

### Очистка кеша (если что-то не работает):
```bash
rm /tmp/ispmanager_*.cache
rm /tmp/proxmox_*.cache
rm /tmp/haproxy_*.cache
rm /tmp/network_*.cache
```

---

## 📚 Полная документация

Детальные инструкции с командами для каждой системы:
```bash
cat docs/MONITORING_SETUP_STHOST.md
```

---

## 🆘 Быстрая помощь

**API не отвечает?**
```bash
tail -f /var/log/apache2/error.log
```

**Сервер не отображается?**
```bash
# Проверьте подключение:
ping 192.168.0.250  # ISPmanager
ping 192.168.0.4    # Proxmox
ping 192.168.0.10   # HAProxy

# Проверьте порты:
nc -zv 192.168.0.250 1500  # ISPmanager
nc -zv 192.168.0.4 8006    # Proxmox
nc -zv 192.168.0.10 8080   # HAProxy stats
```

**SNMP не работает?**
```bash
# Проверьте на HAProxy:
ssh root@192.168.0.10
snmpget -v2c -c public localhost SNMPv2-MIB::sysDescr.0
```

---

## 📞 Контакты

- **Email:** support@sthost.pro
- **Telegram:** @stormhosting_ua

---

**Версия:** 1.0.0
**Дата:** 19.11.2024
