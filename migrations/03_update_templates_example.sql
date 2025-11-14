-- ==================================================
-- ОБНОВЛЕНИЕ PROXMOX TEMPLATE ID ДЛЯ СУЩЕСТВУЮЩИХ ОС
-- Файл: migrations/03_update_templates_example.sql
-- ==================================================

-- ВАЖНО: Этот файл содержит ПРИМЕРЫ!
-- Перед выполнением:
-- 1. Создайте темплейты в Proxmox
-- 2. Узнайте их VMID командой: qm list
-- 3. Замените VMID ниже на ваши реальные ID
-- 4. Выполните этот SQL

USE sthostsitedb;

-- ==================================================
-- ПРОВЕРКА ТЕКУЩЕГО СОСТОЯНИЯ
-- ==================================================

SELECT '📋 Текущее состояние темплейтов:' as info;

SELECT
    id,
    name,
    display_name,
    version,
    type,
    proxmox_template_id,
    proxmox_node,
    proxmox_storage,
    is_active
FROM vps_os_templates
ORDER BY id;

-- ==================================================
-- ОБНОВЛЕНИЕ PROXMOX TEMPLATE ID
-- ==================================================

SELECT '' as separator;
SELECT '🔄 Обновление Proxmox Template ID...' as status;

-- Ubuntu 22.04 LTS
-- Замените 9000 на реальный VMID вашего темплейта!
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9000,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'ubuntu-22.04';

-- Ubuntu 24.04 LTS
-- Замените 9001 на реальный VMID вашего темплейта!
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9001,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'ubuntu-24.04';

-- CentOS Stream 8
-- Замените 9002 на реальный VMID вашего темплейта!
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9002,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'centos-8';

-- Windows 10 Professional
-- Замените 9003 на реальный VMID вашего темплейта!
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9003,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'windows-10';

-- Windows 11 Professional
-- Замените 9004 на реальный VMID вашего темплейта!
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9004,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'windows-11';

SELECT '✅ Proxmox Template ID обновлены!' as status;

-- ==================================================
-- ПРОВЕРКА РЕЗУЛЬТАТОВ
-- ==================================================

SELECT '' as separator;
SELECT '📊 Результат обновления:' as info;

SELECT
    id,
    name,
    display_name,
    version,
    type,
    proxmox_template_id,
    proxmox_node,
    proxmox_storage,
    CASE
        WHEN proxmox_template_id IS NOT NULL THEN '✅ Настроен'
        ELSE '❌ Не настроен'
    END as status
FROM vps_os_templates
ORDER BY id;

-- ==================================================
-- КОМАНДЫ ДЛЯ СОЗДАНИЯ ТЕМПЛЕЙТОВ В PROXMOX
-- ==================================================

SELECT '
╔════════════════════════════════════════════════════════════════╗
║  📦 КОМАНДЫ ДЛЯ СОЗДАНИЯ ТЕМПЛЕЙТОВ В PROXMOX                 ║
╚════════════════════════════════════════════════════════════════╝

Выполните на сервере Proxmox:

# ========== Ubuntu 22.04 LTS ==========
wget https://cloud-images.ubuntu.com/jammy/current/jammy-server-cloudimg-amd64.img
qm create 9000 --name ubuntu-22.04-template --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0
qm importdisk 9000 jammy-server-cloudimg-amd64.img local-lvm
qm set 9000 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9000-disk-0
qm set 9000 --boot order=scsi0
qm set 9000 --serial0 socket --vga serial0
qm set 9000 --agent enabled=1
qm set 9000 --ide2 local-lvm:cloudinit
qm set 9000 --ipconfig0 ip=dhcp
qm template 9000

# ========== Ubuntu 24.04 LTS ==========
wget https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
qm create 9001 --name ubuntu-24.04-template --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0
qm importdisk 9001 noble-server-cloudimg-amd64.img local-lvm
qm set 9001 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9001-disk-0
qm set 9001 --boot order=scsi0
qm set 9001 --serial0 socket --vga serial0
qm set 9001 --agent enabled=1
qm set 9001 --ide2 local-lvm:cloudinit
qm set 9001 --ipconfig0 ip=dhcp
qm template 9001

# ========== CentOS Stream 8 ==========
wget https://cloud.centos.org/centos/8-stream/x86_64/images/CentOS-Stream-GenericCloud-8-latest.x86_64.qcow2
qm create 9002 --name centos-8-template --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0
qm importdisk 9002 CentOS-Stream-GenericCloud-8-latest.x86_64.qcow2 local-lvm
qm set 9002 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9002-disk-0
qm set 9002 --boot order=scsi0
qm set 9002 --serial0 socket --vga serial0
qm set 9002 --agent enabled=1
qm set 9002 --ide2 local-lvm:cloudinit
qm set 9002 --ipconfig0 ip=dhcp
qm template 9002

# ========== Windows (требует ISO) ==========
# 1. Загрузите ISO Windows 10/11 в Proxmox Storage
# 2. Создайте VM через Web UI
# 3. Установите Windows + VirtIO драйверы
# 4. Установите QEMU Guest Agent
# 5. Очистите систему (Sysprep)
# 6. Конвертируйте в темплейт: qm template <VMID>

# Проверка созданных темплейтов:
qm list | grep template

' as commands;
