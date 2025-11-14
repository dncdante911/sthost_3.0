# 🚀 Миграция с Libvirt на Proxmox VE 9

## 📋 Обзор

Эта директория содержит SQL скрипты для полной миграции вашего сайта с Libvirt на Proxmox VE 9.

## 📂 Файлы миграции

1. **01_cleanup_libvirt.sql** - Удаление всех следов libvirt из БД
2. **02_setup_proxmox.sql** - Настройка структуры БД для Proxmox
3. **03_update_templates_example.sql** - Примеры обновления темплейтов (настройте под свои VMID!)

## ⚠️ ВАЖНО! ПЕРЕД НАЧАЛОМ

### 1. Сделайте резервную копию БД:

```bash
mysqldump -u sthostdb -p sthostsitedb > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Проверьте учетные данные:

Убедитесь, что у вас есть:
- Пароль от MySQL: `sthostdb`
- База данных: `sthostsitedb`

## 🔧 Процесс миграции

### Шаг 1: Очистка libvirt

```bash
mysql -u sthostdb -p sthostsitedb < migrations/01_cleanup_libvirt.sql
```

Этот скрипт:
- ✅ Удаляет все поля связанные с libvirt
- ✅ Удаляет unique constraints
- ✅ Оптимизирует таблицы

### Шаг 2: Настройка Proxmox

```bash
mysql -u sthostdb -p sthostsitedb < migrations/02_setup_proxmox.sql
```

Этот скрипт:
- ✅ Добавляет поля `proxmox_vmid` и `proxmox_node`
- ✅ Настраивает таблицу `vps_os_templates` для Proxmox
- ✅ Создает таблицу `proxmox_migration_log`
- ✅ Добавляет необходимые индексы
- ✅ Показывает статистику по темплейтам

### Шаг 3: Создание темплейтов в Proxmox

**На сервере Proxmox выполните:**

#### Ubuntu 22.04:
```bash
wget https://cloud-images.ubuntu.com/jammy/current/jammy-server-cloudimg-amd64.img
qm create 9000 --name ubuntu-22.04-template --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0
qm importdisk 9000 jammy-server-cloudimg-amd64.img local-lvm
qm set 9000 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9000-disk-0
qm set 9000 --boot order=scsi0
qm set 9000 --serial0 socket --vga serial0
qm set 9000 --agent enabled=1
qm set 9000 --ide2 local-lvm:cloudinit
qm template 9000
```

#### Ubuntu 24.04:
```bash
wget https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
qm create 9001 --name ubuntu-24.04-template --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0
qm importdisk 9001 noble-server-cloudimg-amd64.img local-lvm
qm set 9001 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9001-disk-0
qm set 9001 --boot order=scsi0
qm set 9001 --serial0 socket --vga serial0
qm set 9001 --agent enabled=1
qm set 9001 --ide2 local-lvm:cloudinit
qm template 9001
```

#### CentOS Stream 8:
```bash
wget https://cloud.centos.org/centos/8-stream/x86_64/images/CentOS-Stream-GenericCloud-8-latest.x86_64.qcow2
qm create 9002 --name centos-8-template --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0
qm importdisk 9002 CentOS-Stream-GenericCloud-8-latest.x86_64.qcow2 local-lvm
qm set 9002 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9002-disk-0
qm set 9002 --boot order=scsi0
qm set 9002 --serial0 socket --vga serial0
qm set 9002 --agent enabled=1
qm set 9002 --ide2 local-lvm:cloudinit
qm template 9002
```

#### Проверка созданных темплейтов:
```bash
qm list
```

### Шаг 4: Узнайте VMID темплейтов

```bash
qm list | grep template
```

Вы должны увидеть что-то вроде:
```
9000   ubuntu-22.04-template    running    2048    local-lvm
9001   ubuntu-24.04-template    running    2048    local-lvm
9002   centos-8-template        running    2048    local-lvm
```

### Шаг 5: Обновление таблицы vps_os_templates

**Отредактируйте файл** `03_update_templates_example.sql`:
- Замените VMID (9000, 9001, 9002...) на **ваши реальные VMID**
- Если используете другую ноду - измените `pve` на имя вашей ноды
- Если используете другое хранилище - измените `local-lvm`

**Выполните:**
```bash
mysql -u sthostdb -p sthostsitedb < migrations/03_update_templates_example.sql
```

## ✅ Проверка результатов

### Проверка структуры таблиц:

```sql
USE sthostsitedb;

-- Проверка vps_instances
DESCRIBE vps_instances;

-- Проверка vps_os_templates
DESCRIBE vps_os_templates;

-- Статистика темплейтов
SELECT
    id,
    name,
    display_name,
    proxmox_template_id,
    proxmox_node,
    proxmox_storage,
    CASE
        WHEN proxmox_template_id IS NOT NULL THEN '✅ Готов'
        ELSE '❌ Нужна настройка'
    END as status
FROM vps_os_templates
ORDER BY id;
```

### Проверка существующих VPS:

```sql
SELECT
    id,
    hostname,
    proxmox_vmid,
    proxmox_node,
    status,
    os_template
FROM vps_instances;
```

## 🔄 Миграция существующих VPS

Если у вас уже есть работающие VPS на libvirt:

1. Экспортируйте диски VPS из libvirt
2. Импортируйте в Proxmox
3. Обновите `proxmox_vmid` в таблице `vps_instances`

**Пример:**
```sql
UPDATE vps_instances
SET proxmox_vmid = 100, proxmox_node = 'pve'
WHERE id = 1;
```

## 🔧 Настройка config.php

После миграции БД обновите `/home/user/sthost_3.0/includes/config.php`:

```php
// Proxmox VE 9 настройки
define('PROXMOX_HOST', '192.168.0.4');        // ← ВАШ IP
define('PROXMOX_PORT', 8006);
define('PROXMOX_USER', 'root');
define('PROXMOX_REALM', 'pam');
define('PROXMOX_PASSWORD', '');               // ← ИЛИ ПАРОЛЬ
define('PROXMOX_NODE', 'pve');                // ← ВАШ NODE

// API Token (рекомендуется!)
define('PROXMOX_TOKEN_ID', 'root@pam!mytoken');     // ← ВАШ TOKEN
define('PROXMOX_TOKEN_SECRET', 'xxxxxxxx-xxxx');    // ← ВАШ SECRET

// Storage и Network
define('PROXMOX_STORAGE', 'local-lvm');       // ← ВАШ STORAGE
define('PROXMOX_BRIDGE', 'vmbr0');            // ← ВАШ BRIDGE
```

## 🧪 Тестирование

Создайте тестовый файл `test_proxmox.php`:

```php
<?php
define('SECURE_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/classes/ProxmoxManager.php';

$proxmox = new ProxmoxManager();

if ($proxmox->authenticate()) {
    echo "✅ Подключение успешно!\n";

    $result = $proxmox->listAllVPS();
    print_r($result);

    $templates = $proxmox->getTemplates();
    print_r($templates);
} else {
    echo "❌ Ошибка подключения!\n";
}
?>
```

Запустите:
```bash
php test_proxmox.php
```

## 📊 Статистика миграции

После выполнения всех шагов:

```sql
SELECT '📊 ИТОГОВАЯ СТАТИСТИКА' as info;

SELECT 'VPS Instances:' as table_name,
    COUNT(*) as total,
    SUM(CASE WHEN proxmox_vmid IS NOT NULL THEN 1 ELSE 0 END) as migrated
FROM vps_instances
UNION ALL
SELECT 'OS Templates:',
    COUNT(*),
    SUM(CASE WHEN proxmox_template_id IS NOT NULL THEN 1 ELSE 0 END)
FROM vps_os_templates;
```

## 🆘 Проблемы и решения

### Ошибка: Unknown column 'icon_url'
**Решение:** Используйте новые скрипты миграции из этой директории.

### Ошибка: Duplicate column name
**Решение:** Скрипты проверяют существование столбцов. Запустите заново.

### Темплейты не работают
**Решение:**
1. Проверьте `proxmox_template_id` в БД
2. Проверьте существование темплейтов: `qm list`
3. Проверьте права доступа в Proxmox

## 📚 Дополнительная документация

См. полное руководство: `PROXMOX_SETUP_GUIDE.md`

## ✅ Checklist

- [ ] Сделана резервная копия БД
- [ ] Выполнен `01_cleanup_libvirt.sql`
- [ ] Выполнен `02_setup_proxmox.sql`
- [ ] Созданы темплейты в Proxmox
- [ ] Узнаны VMID темплейтов
- [ ] Отредактирован `03_update_templates_example.sql`
- [ ] Выполнен `03_update_templates_example.sql`
- [ ] Настроен `config.php`
- [ ] Создан API токен в Proxmox
- [ ] Протестировано подключение
- [ ] Создан тестовый VPS

---

**Автор:** Claude AI
**Дата:** 2025-11-14
**Версия:** 2.0 (исправленная)
