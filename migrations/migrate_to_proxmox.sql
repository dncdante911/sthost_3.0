-- ==================================================
-- МИГРАЦИЯ: Переход с Libvirt на Proxmox VE 9
-- Файл: migrations/migrate_to_proxmox.sql
-- Дата: 2025-11-14
-- ==================================================

-- ВАЖНО: Сделайте резервную копию базы данных перед выполнением!
-- mysqldump -u sthostdb -p sthostsitedb > backup_before_proxmox_migration.sql

USE sthostsitedb;

-- ==================================================
-- 1. ОБНОВЛЕНИЕ ТАБЛИЦЫ vps_instances
-- ==================================================

-- Добавляем новые поля для Proxmox
ALTER TABLE `vps_instances`
ADD COLUMN `proxmox_vmid` INT NULL COMMENT 'Proxmox VM ID' AFTER `hostname`,
ADD COLUMN `proxmox_node` VARCHAR(50) DEFAULT 'pve' COMMENT 'Proxmox node name' AFTER `proxmox_vmid`;

-- Переименовываем libvirt_name в старое поле (для истории)
ALTER TABLE `vps_instances`
CHANGE COLUMN `libvirt_name` `legacy_libvirt_name` VARCHAR(100) NULL COMMENT 'Legacy: старое имя в Libvirt';

-- Добавляем индексы для оптимизации
ALTER TABLE `vps_instances`
ADD INDEX `idx_proxmox_vmid` (`proxmox_vmid`),
ADD INDEX `idx_proxmox_node` (`proxmox_node`);

-- Убираем уникальность старого поля libvirt_name (теперь не используется)
ALTER TABLE `vps_instances`
DROP INDEX IF EXISTS `unique_libvirt_name`;

-- ==================================================
-- 2. ОБНОВЛЕНИЕ ТАБЛИЦЫ vps_os_templates
-- ==================================================

-- Добавляем поля для Proxmox темплейтов
ALTER TABLE `vps_os_templates`
ADD COLUMN `proxmox_template_id` INT NULL COMMENT 'Proxmox Template VMID' AFTER `icon_url`,
ADD COLUMN `proxmox_storage` VARCHAR(50) DEFAULT 'local-lvm' COMMENT 'Proxmox storage for template' AFTER `proxmox_template_id`;

-- Переименовываем libvirt поля в legacy
ALTER TABLE `vps_os_templates`
CHANGE COLUMN `libvirt_image_path` `legacy_libvirt_image_path` VARCHAR(255) NULL COMMENT 'Legacy: путь к образу в Libvirt',
CHANGE COLUMN `libvirt_xml_template` `legacy_libvirt_xml_template` TEXT NULL COMMENT 'Legacy: XML template для Libvirt';

-- Обновляем существующие темплейты (ПРИМЕРЫ - НАСТРОЙТЕ ПОД ВАШИ VMID!)
-- ВАЖНО: Замените VMID на реальные ID ваших темплейтов в Proxmox!
UPDATE `vps_os_templates` SET `proxmox_template_id` = 9000 WHERE `name` = 'ubuntu-22.04';
UPDATE `vps_os_templates` SET `proxmox_template_id` = 9001 WHERE `name` = 'ubuntu-24.04';
UPDATE `vps_os_templates` SET `proxmox_template_id` = 9002 WHERE `name` = 'centos-8';
UPDATE `vps_os_templates` SET `proxmox_template_id` = 9003 WHERE `name` = 'windows-10';
UPDATE `vps_os_templates` SET `proxmox_template_id` = 9004 WHERE `name` = 'windows-11';

-- ==================================================
-- 3. ОБНОВЛЕНИЕ ТАБЛИЦЫ vps_plans
-- ==================================================

-- Убираем libvirt_template поле
ALTER TABLE `vps_plans`
CHANGE COLUMN `libvirt_template` `legacy_libvirt_template` VARCHAR(100) NULL COMMENT 'Legacy: имя template в Libvirt';

-- ==================================================
-- 4. ОБНОВЛЕНИЕ ТАБЛИЦЫ vps_snapshots
-- ==================================================

-- Переименовываем libvirt_name в legacy
ALTER TABLE `vps_snapshots`
CHANGE COLUMN `libvirt_name` `legacy_snapshot_name` VARCHAR(150) NULL COMMENT 'Legacy: имя snapshot в Libvirt';

-- Добавляем поле для Proxmox snapshot
ALTER TABLE `vps_snapshots`
ADD COLUMN `proxmox_snapshot_name` VARCHAR(150) NULL COMMENT 'Proxmox snapshot name' AFTER `name`;

-- ==================================================
-- 5. СОЗДАНИЕ ТАБЛИЦЫ ДЛЯ МИГРАЦИИ (опционально)
-- ==================================================

-- Таблица для отслеживания процесса миграции
CREATE TABLE IF NOT EXISTS `migration_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `vps_id` INT(11) NOT NULL,
  `old_libvirt_name` VARCHAR(100) NULL,
  `new_proxmox_vmid` INT NULL,
  `migration_status` ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
  `migration_date` TIMESTAMP NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vps_id` (`vps_id`),
  KEY `idx_migration_status` (`migration_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог миграции с Libvirt на Proxmox';

-- ==================================================
-- 6. ОЧИСТКА И ОПТИМИЗАЦИЯ
-- ==================================================

-- Удаляем старые неиспользуемые записи (опционально)
-- DELETE FROM vps_instances WHERE status = 'terminated' AND updated_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Оптимизируем таблицы после изменений
OPTIMIZE TABLE `vps_instances`;
OPTIMIZE TABLE `vps_os_templates`;
OPTIMIZE TABLE `vps_plans`;
OPTIMIZE TABLE `vps_snapshots`;

-- ==================================================
-- 7. ВЕРИФИКАЦИЯ МИГРАЦИИ
-- ==================================================

-- Проверяем структуру таблиц
SELECT 'Структура vps_instances:' as info;
DESCRIBE vps_instances;

SELECT 'Структура vps_os_templates:' as info;
DESCRIBE vps_os_templates;

-- Проверяем данные
SELECT
    'VPS instances статистика:' as info,
    COUNT(*) as total_vps,
    SUM(CASE WHEN proxmox_vmid IS NOT NULL THEN 1 ELSE 0 END) as migrated_to_proxmox,
    SUM(CASE WHEN legacy_libvirt_name IS NOT NULL AND proxmox_vmid IS NULL THEN 1 ELSE 0 END) as needs_migration
FROM vps_instances;

SELECT
    'OS Templates статистика:' as info,
    COUNT(*) as total_templates,
    SUM(CASE WHEN proxmox_template_id IS NOT NULL THEN 1 ELSE 0 END) as configured_for_proxmox
FROM vps_os_templates;

-- ==================================================
-- ГОТОВО!
-- ==================================================
-- После выполнения миграции:
-- 1. Проверьте, что все VPS корректно отображаются
-- 2. Протестируйте создание нового VPS
-- 3. Протестируйте управление существующими VPS
-- 4. Удалите файлы includes/classes/LibvirtManager.php если больше не нужны
-- ==================================================

SELECT '✅ Миграция на Proxmox VE 9 завершена!' as status;
