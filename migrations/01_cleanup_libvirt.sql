-- ==================================================
-- ОЧИСТКА ВСЕХ СЛЕДОВ LIBVIRT
-- Файл: migrations/01_cleanup_libvirt.sql
-- ==================================================

-- ВАЖНО: Сделайте резервную копию БД перед выполнением!
-- mysqldump -u sthostdb -p sthostsitedb > backup_before_cleanup_$(date +%Y%m%d_%H%M%S).sql

USE sthostsitedb;

SET FOREIGN_KEY_CHECKS=0;

-- ==================================================
-- 1. ТАБЛИЦА vps_instances - УДАЛЕНИЕ LIBVIRT ПОЛЕЙ
-- ==================================================

SELECT '📋 Шаг 1: Обработка таблицы vps_instances...' as status;

-- Удаляем unique constraint на libvirt_name
ALTER TABLE `vps_instances` DROP INDEX IF EXISTS `unique_libvirt_name`;

-- Удаляем поле libvirt_name (если оно еще существует)
ALTER TABLE `vps_instances` DROP COLUMN IF EXISTS `libvirt_name`;

-- Удаляем vnc_port (Proxmox использует динамические порты)
ALTER TABLE `vps_instances` DROP COLUMN IF EXISTS `vnc_port`;

SELECT '✅ Таблица vps_instances очищена от libvirt полей' as status;

-- ==================================================
-- 2. ТАБЛИЦА vps_os_templates - УДАЛЕНИЕ LIBVIRT ПОЛЕЙ
-- ==================================================

SELECT '📋 Шаг 2: Обработка таблицы vps_os_templates...' as status;

-- Удаляем libvirt поля
ALTER TABLE `vps_os_templates` DROP COLUMN IF EXISTS `libvirt_image_path`;
ALTER TABLE `vps_os_templates` DROP COLUMN IF EXISTS `libvirt_xml_template`;

SELECT '✅ Таблица vps_os_templates очищена от libvirt полей' as status;

-- ==================================================
-- 3. ТАБЛИЦА vps_plans - УДАЛЕНИЕ LIBVIRT ПОЛЕЙ
-- ==================================================

SELECT '📋 Шаг 3: Обработка таблицы vps_plans...' as status;

-- Удаляем libvirt_template
ALTER TABLE `vps_plans` DROP COLUMN IF EXISTS `libvirt_template`;

SELECT '✅ Таблица vps_plans очищена от libvirt полей' as status;

-- ==================================================
-- 4. ТАБЛИЦА vps_snapshots - УДАЛЕНИЕ LIBVIRT ПОЛЕЙ
-- ==================================================

SELECT '📋 Шаг 4: Обработка таблицы vps_snapshots...' as status;

-- Удаляем libvirt_name
ALTER TABLE `vps_snapshots` DROP COLUMN IF EXISTS `libvirt_name`;

SELECT '✅ Таблица vps_snapshots очищена от libvirt полей' as status;

-- ==================================================
-- 5. ОПТИМИЗАЦИЯ ТАБЛИЦ
-- ==================================================

SELECT '📋 Шаг 5: Оптимизация таблиц...' as status;

OPTIMIZE TABLE `vps_instances`;
OPTIMIZE TABLE `vps_os_templates`;
OPTIMIZE TABLE `vps_plans`;
OPTIMIZE TABLE `vps_snapshots`;

SELECT '✅ Таблицы оптимизированы' as status;

SET FOREIGN_KEY_CHECKS=1;

-- ==================================================
-- ГОТОВО! ВСЕ СЛЕДЫ LIBVIRT УДАЛЕНЫ
-- ==================================================

SELECT '
╔════════════════════════════════════════════════════╗
║  ✅ ОЧИСТКА LIBVIRT ЗАВЕРШЕНА УСПЕШНО!            ║
║                                                    ║
║  Теперь выполните: 02_setup_proxmox.sql          ║
╚════════════════════════════════════════════════════╝
' as message;
