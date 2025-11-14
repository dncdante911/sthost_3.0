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

SELECT 'Текущее состояние темплейтов:' as info;

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

-- ВАЖНО: Замените VMID (9000, 9001, 9002...) на ваши реальные!

-- Ubuntu 22.04 LTS
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9000,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'ubuntu-22.04';

-- Ubuntu 24.04 LTS
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9001,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'ubuntu-24.04';

-- CentOS Stream 8
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9002,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'centos-8';

-- Windows 10 Professional
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9003,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'windows-10';

-- Windows 11 Professional
UPDATE `vps_os_templates`
SET
    `proxmox_template_id` = 9004,
    `proxmox_node` = 'pve',
    `proxmox_storage` = 'local-lvm'
WHERE `name` = 'windows-11';

-- ==================================================
-- ПРОВЕРКА РЕЗУЛЬТАТОВ
-- ==================================================

SELECT 'Результат обновления:' as info;

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
        WHEN proxmox_template_id IS NOT NULL THEN 'Configured'
        ELSE 'Not Configured'
    END as status
FROM vps_os_templates
ORDER BY id;

-- Готово!
-- Все темплейты обновлены с Proxmox VMID
