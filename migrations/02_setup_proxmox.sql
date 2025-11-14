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

SELECT '📋 Шаг 1: Настройка таблицы vps_instances для Proxmox...' as status;

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

SELECT '✅ Таблица vps_instances настроена для Proxmox' as status;

-- ==================================================
-- 2. ТАБЛИЦА vps_os_templates - ДОБАВЛЕНИЕ PROXMOX ПОЛЕЙ
-- ==================================================

SELECT '📋 Шаг 2: Настройка таблицы vps_os_templates для Proxmox...' as status;

-- Добавляем поля для Proxmox темплейтов
ALTER TABLE `vps_os_templates`
ADD COLUMN IF NOT EXISTS `proxmox_template_id` INT NULL COMMENT 'Proxmox Template VMID' AFTER `icon`,
ADD COLUMN IF NOT EXISTS `proxmox_storage` VARCHAR(50) DEFAULT 'local-lvm' COMMENT 'Proxmox storage for template' AFTER `proxmox_template_id`,
ADD COLUMN IF NOT EXISTS `proxmox_node` VARCHAR(50) DEFAULT 'pve' COMMENT 'Proxmox node where template is stored' AFTER `proxmox_storage`;

-- Добавляем индексы
ALTER TABLE `vps_os_templates` ADD INDEX IF NOT EXISTS `idx_proxmox_template_id` (`proxmox_template_id`);
ALTER TABLE `vps_os_templates` ADD INDEX IF NOT EXISTS `idx_proxmox_node` (`proxmox_node`);

SELECT '✅ Таблица vps_os_templates настроена для Proxmox' as status;

-- ==================================================
-- 3. ТАБЛИЦА vps_snapshots - ДОБАВЛЕНИЕ PROXMOX ПОЛЕЙ
-- ==================================================

SELECT '📋 Шаг 3: Настройка таблицы vps_snapshots для Proxmox...' as status;

-- Добавляем поле для Proxmox snapshot
ALTER TABLE `vps_snapshots`
ADD COLUMN IF NOT EXISTS `proxmox_snapshot_name` VARCHAR(150) NULL COMMENT 'Proxmox snapshot name' AFTER `name`;

-- Добавляем индекс
ALTER TABLE `vps_snapshots` ADD INDEX IF NOT EXISTS `idx_proxmox_snapshot` (`proxmox_snapshot_name`);

SELECT '✅ Таблица vps_snapshots настроена для Proxmox' as status;

-- ==================================================
-- 4. СОЗДАНИЕ ТАБЛИЦЫ МИГРАЦИИ (опционально)
-- ==================================================

SELECT '📋 Шаг 4: Создание таблицы для отслеживания миграции...' as status;

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

SELECT '✅ Таблица proxmox_migration_log создана' as status;

-- ==================================================
-- 5. ОБНОВЛЕНИЕ СУЩЕСТВУЮЩИХ ДАННЫХ
-- ==================================================

SELECT '📋 Шаг 5: Обновление существующих данных...' as status;

-- Устанавливаем дефолтную ноду для всех существующих VPS
UPDATE `vps_instances` SET `proxmox_node` = 'pve' WHERE `proxmox_node` IS NULL;

-- Устанавливаем дефолтную ноду для всех темплейтов
UPDATE `vps_os_templates` SET `proxmox_node` = 'pve' WHERE `proxmox_node` IS NULL;

-- Устанавливаем дефолтный storage для всех темплейтов
UPDATE `vps_os_templates` SET `proxmox_storage` = 'local-lvm' WHERE `proxmox_storage` IS NULL;

SELECT '✅ Существующие данные обновлены' as status;

-- ==================================================
-- 6. НАСТРОЙКА PROXMOX TEMPLATE ID ДЛЯ СУЩЕСТВУЮЩИХ ОС
-- ==================================================

SELECT '📋 Шаг 6: Настройка Proxmox Template ID для ОС...' as status;

-- ВАЖНО: Замените VMID на реальные ID ваших темплейтов в Proxmox!
-- Узнать VMID можно командой: qm list
-- Или в Proxmox Web UI: Datacenter > [Node] > Virtual Machines

-- Пример настройки (ЗАМЕНИТЕ НА СВОИ VMID!):
-- UPDATE `vps_os_templates` SET `proxmox_template_id` = 9000 WHERE `name` = 'ubuntu-22.04';
-- UPDATE `vps_os_templates` SET `proxmox_template_id` = 9001 WHERE `name` = 'ubuntu-24.04';
-- UPDATE `vps_os_templates` SET `proxmox_template_id` = 9002 WHERE `name` = 'centos-8';
-- UPDATE `vps_os_templates` SET `proxmox_template_id` = 9003 WHERE `name` = 'windows-10';
-- UPDATE `vps_os_templates` SET `proxmox_template_id` = 9004 WHERE `name` = 'windows-11';

SELECT '⚠️  ВНИМАНИЕ: Обновите proxmox_template_id вручную!' as warning;
SELECT '   Раскомментируйте и выполните UPDATE запросы выше' as hint;

-- ==================================================
-- 7. ОПТИМИЗАЦИЯ И ФИНАЛИЗАЦИЯ
-- ==================================================

SELECT '📋 Шаг 7: Оптимизация таблиц...' as status;

OPTIMIZE TABLE `vps_instances`;
OPTIMIZE TABLE `vps_os_templates`;
OPTIMIZE TABLE `vps_plans`;
OPTIMIZE TABLE `vps_snapshots`;
OPTIMIZE TABLE `proxmox_migration_log`;

SELECT '✅ Таблицы оптимизированы' as status;

-- ==================================================
-- 8. ПРОВЕРКА РЕЗУЛЬТАТОВ
-- ==================================================

SELECT '
╔════════════════════════════════════════════════════╗
║  📊 ПРОВЕРКА СТРУКТУРЫ ТАБЛИЦ                     ║
╚════════════════════════════════════════════════════╝
' as message;

-- Проверяем vps_instances
SELECT 'vps_instances: Структура' as info;
DESCRIBE vps_instances;

-- Статистика
SELECT
    '📊 СТАТИСТИКА vps_instances:' as info,
    COUNT(*) as total_vps,
    SUM(CASE WHEN proxmox_vmid IS NOT NULL THEN 1 ELSE 0 END) as with_proxmox_vmid,
    SUM(CASE WHEN proxmox_vmid IS NULL THEN 1 ELSE 0 END) as needs_vmid
FROM vps_instances;

-- Проверяем vps_os_templates
SELECT '' as separator;
SELECT 'vps_os_templates: Структура' as info;
DESCRIBE vps_os_templates;

-- Статистика темплейтов
SELECT
    '📊 СТАТИСТИКА vps_os_templates:' as info,
    COUNT(*) as total_templates,
    SUM(CASE WHEN proxmox_template_id IS NOT NULL THEN 1 ELSE 0 END) as configured_for_proxmox,
    SUM(CASE WHEN proxmox_template_id IS NULL THEN 1 ELSE 0 END) as needs_configuration
FROM vps_os_templates;

-- Список всех темплейтов с их статусом
SELECT '' as separator;
SELECT '📦 СПИСОК ТЕМПЛЕЙТОВ:' as info;
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
        WHEN proxmox_template_id IS NOT NULL THEN '✅ Готов'
        ELSE '❌ Нужна настройка'
    END as status
FROM vps_os_templates
ORDER BY sort_order, name;

SET FOREIGN_KEY_CHECKS=1;

-- ==================================================
-- ГОТОВО!
-- ==================================================

SELECT '
╔════════════════════════════════════════════════════╗
║  ✅ НАСТРОЙКА PROXMOX ЗАВЕРШЕНА!                  ║
║                                                    ║
║  📝 СЛЕДУЮЩИЕ ШАГИ:                               ║
║  1. Создайте темплейты в Proxmox                 ║
║  2. Узнайте их VMID (qm list)                    ║
║  3. Обновите proxmox_template_id в таблице       ║
║     vps_os_templates                              ║
║  4. Настройте config.php с учетными данными      ║
║  5. Протестируйте создание VPS                   ║
║                                                    ║
║  📖 Подробнее: PROXMOX_SETUP_GUIDE.md            ║
╚════════════════════════════════════════════════════╝
' as message;
