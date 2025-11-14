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

-- Удаляем unique constraint на libvirt_name
ALTER TABLE `vps_instances` DROP INDEX IF EXISTS `unique_libvirt_name`;

-- Удаляем поле libvirt_name (если оно еще существует)
ALTER TABLE `vps_instances` DROP COLUMN IF EXISTS `libvirt_name`;

-- Удаляем vnc_port (Proxmox использует динамические порты)
ALTER TABLE `vps_instances` DROP COLUMN IF EXISTS `vnc_port`;

-- ==================================================
-- 2. ТАБЛИЦА vps_os_templates - УДАЛЕНИЕ LIBVIRT ПОЛЕЙ
-- ==================================================

-- Удаляем libvirt поля
ALTER TABLE `vps_os_templates` DROP COLUMN IF EXISTS `libvirt_image_path`;
ALTER TABLE `vps_os_templates` DROP COLUMN IF EXISTS `libvirt_xml_template`;

-- ==================================================
-- 3. ТАБЛИЦА vps_plans - УДАЛЕНИЕ LIBVIRT ПОЛЕЙ
-- ==================================================

-- Удаляем libvirt_template
ALTER TABLE `vps_plans` DROP COLUMN IF EXISTS `libvirt_template`;

-- ==================================================
-- 4. ТАБЛИЦА vps_snapshots - УДАЛЕНИЕ LIBVIRT ПОЛЕЙ
-- ==================================================

-- Удаляем libvirt_name
ALTER TABLE `vps_snapshots` DROP COLUMN IF EXISTS `libvirt_name`;

-- ==================================================
-- 5. ОПТИМИЗАЦИЯ ТАБЛИЦ
-- ==================================================

OPTIMIZE TABLE `vps_instances`;
OPTIMIZE TABLE `vps_os_templates`;
OPTIMIZE TABLE `vps_plans`;
OPTIMIZE TABLE `vps_snapshots`;

SET FOREIGN_KEY_CHECKS=1;

-- Готово! Все следы libvirt удалены.
-- Теперь выполните: 02_setup_proxmox.sql
