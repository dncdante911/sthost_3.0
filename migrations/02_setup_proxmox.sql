-- ==================================================
-- ПОЛНАЯ НАСТРОЙКА PROXMOX VE 9
-- Файл: migrations/02_setup_proxmox.sql
-- ==================================================

-- ВАЖНО: Сначала выполните 01_cleanup_libvirt.sql!

USE sthostsitedb;

SET FOREIGN_KEY_CHECKS=0;

-- ==================================================
-- 1. ТАБЛИЦА vps_instances - ДОБАВЛЕНИЕ PROXMOX ПОЛЕЙ
-- ==================================================

-- Добавляем поля Proxmox
ALTER TABLE `vps_instances`
ADD COLUMN IF NOT EXISTS `proxmox_vmid` INT NULL COMMENT 'Proxmox VM ID' AFTER `hostname`,
ADD COLUMN IF NOT EXISTS `proxmox_node` VARCHAR(50) DEFAULT 'pve' COMMENT 'Proxmox node name' AFTER `proxmox_vmid`;

-- Добавляем индексы
ALTER TABLE `vps_instances` ADD INDEX IF NOT EXISTS `idx_proxmox_vmid` (`proxmox_vmid`);
ALTER TABLE `vps_instances` ADD INDEX IF NOT EXISTS `idx_proxmox_node` (`proxmox_node`);

-- Убираем unique constraint на hostname (могут быть дубликаты при миграции)
ALTER TABLE `vps_instances` DROP INDEX IF EXISTS `unique_hostname`;
ALTER TABLE `vps_instances` ADD INDEX IF NOT EXISTS `idx_hostname` (`hostname`);

-- ==================================================
-- 2. ТАБЛИЦА vps_os_templates - ДОБАВЛЕНИЕ PROXMOX ПОЛЕЙ
-- ==================================================

-- Добавляем поля для Proxmox темплейтов
ALTER TABLE `vps_os_templates`
ADD COLUMN IF NOT EXISTS `proxmox_template_id` INT NULL COMMENT 'Proxmox Template VMID' AFTER `icon`,
ADD COLUMN IF NOT EXISTS `proxmox_storage` VARCHAR(50) DEFAULT 'local-lvm' COMMENT 'Proxmox storage for template' AFTER `proxmox_template_id`,
ADD COLUMN IF NOT EXISTS `proxmox_node` VARCHAR(50) DEFAULT 'pve' COMMENT 'Proxmox node where template is stored' AFTER `proxmox_storage`;

-- Добавляем индексы
ALTER TABLE `vps_os_templates` ADD INDEX IF NOT EXISTS `idx_proxmox_template_id` (`proxmox_template_id`);
ALTER TABLE `vps_os_templates` ADD INDEX IF NOT EXISTS `idx_proxmox_node` (`proxmox_node`);

-- ==================================================
-- 3. ТАБЛИЦА vps_snapshots - ДОБАВЛЕНИЕ PROXMOX ПОЛЕЙ
-- ==================================================

-- Добавляем поле для Proxmox snapshot
ALTER TABLE `vps_snapshots`
ADD COLUMN IF NOT EXISTS `proxmox_snapshot_name` VARCHAR(150) NULL COMMENT 'Proxmox snapshot name' AFTER `name`;

-- Добавляем индекс
ALTER TABLE `vps_snapshots` ADD INDEX IF NOT EXISTS `idx_proxmox_snapshot` (`proxmox_snapshot_name`);

-- ==================================================
-- 4. СОЗДАНИЕ ТАБЛИЦЫ МИГРАЦИИ
-- ==================================================

CREATE TABLE IF NOT EXISTS `proxmox_migration_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `vps_id` INT(11) NULL,
  `old_identifier` VARCHAR(100) NULL COMMENT 'Старый libvirt_name если был',
  `new_proxmox_vmid` INT NULL,
  `migration_status` ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
  `migration_date` TIMESTAMP NULL,
  `error_message` TEXT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vps_id` (`vps_id`),
  KEY `idx_migration_status` (`migration_status`),
  KEY `idx_migration_date` (`migration_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог миграции на Proxmox VE 9';

-- ==================================================
-- 5. ОБНОВЛЕНИЕ СУЩЕСТВУЮЩИХ ДАННЫХ
-- ==================================================

-- Устанавливаем дефолтную ноду для всех существующих VPS
UPDATE `vps_instances` SET `proxmox_node` = 'pve' WHERE `proxmox_node` IS NULL;

-- Устанавливаем дефолтную ноду для всех темплейтов
UPDATE `vps_os_templates` SET `proxmox_node` = 'pve' WHERE `proxmox_node` IS NULL;

-- Устанавливаем дефолтный storage для всех темплейтов
UPDATE `vps_os_templates` SET `proxmox_storage` = 'local-lvm' WHERE `proxmox_storage` IS NULL;

-- ==================================================
-- 6. ОПТИМИЗАЦИЯ ТАБЛИЦ
-- ==================================================

OPTIMIZE TABLE `vps_instances`;
OPTIMIZE TABLE `vps_os_templates`;
OPTIMIZE TABLE `vps_plans`;
OPTIMIZE TABLE `vps_snapshots`;
OPTIMIZE TABLE `proxmox_migration_log`;

SET FOREIGN_KEY_CHECKS=1;

-- ==================================================
-- ПРОВЕРКА РЕЗУЛЬТАТОВ
-- ==================================================

-- Проверяем vps_instances
SELECT 'Проверка vps_instances:' as info;
DESCRIBE vps_instances;

-- Статистика VPS
SELECT
    COUNT(*) as total_vps,
    SUM(CASE WHEN proxmox_vmid IS NOT NULL THEN 1 ELSE 0 END) as with_proxmox_vmid,
    SUM(CASE WHEN proxmox_vmid IS NULL THEN 1 ELSE 0 END) as needs_vmid
FROM vps_instances;

-- Проверяем vps_os_templates
SELECT 'Проверка vps_os_templates:' as info;
DESCRIBE vps_os_templates;

-- Статистика темплейтов
SELECT
    COUNT(*) as total_templates,
    SUM(CASE WHEN proxmox_template_id IS NOT NULL THEN 1 ELSE 0 END) as configured_for_proxmox,
    SUM(CASE WHEN proxmox_template_id IS NULL THEN 1 ELSE 0 END) as needs_configuration
FROM vps_os_templates;

-- Список всех темплейтов с их статусом
SELECT
    id,
    name,
    display_name,
    version,
    type,
    proxmox_template_id,
    proxmox_node,
    proxmox_storage,
    is_active,
    CASE
        WHEN proxmox_template_id IS NOT NULL THEN 'Ready'
        ELSE 'Needs Config'
    END as status
FROM vps_os_templates
ORDER BY sort_order, name;

-- Готово!
-- Следующие шаги:
-- 1. Создайте темплейты в Proxmox
-- 2. Узнайте их VMID (qm list)
-- 3. Обновите proxmox_template_id в таблице vps_os_templates
-- 4. Настройте config.php с учетными данными
-- 5. Протестируйте создание VPS
