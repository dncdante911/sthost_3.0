-- ============================================================================
-- Database Optimization: Adding Indexes for Better Performance
-- SECURITY AUDIT FIX: Recommendation #17
-- Created: 2025-11-18
-- ============================================================================

-- Проверяйте существующие индексы перед выполнением:
-- SHOW INDEX FROM table_name;

-- ============================================================================
-- News Table Indexes
-- ============================================================================

-- Composite index for published news sorted by date
-- Used in: /api/news/list.php, homepage queries
ALTER TABLE news
ADD INDEX IF NOT EXISTS idx_published_created (is_published, created_at DESC);

-- Index for featured news
ALTER TABLE news
ADD INDEX IF NOT EXISTS idx_featured (is_featured);

-- Index for news by language (if you have multilingual support)
-- ALTER TABLE news
-- ADD INDEX IF NOT EXISTS idx_lang (lang);

-- ============================================================================
-- Domain Zones Table Indexes
-- ============================================================================

-- Composite index for active and popular domains
-- Used in: domain search, pricing pages
ALTER TABLE domain_zones
ADD INDEX IF NOT EXISTS idx_active_popular (is_active, is_popular);

-- Index for zone name lookups
ALTER TABLE domain_zones
ADD INDEX IF NOT EXISTS idx_zone (zone);

-- Index for price queries
ALTER TABLE domain_zones
ADD INDEX IF NOT EXISTS idx_price_registration (price_registration);

-- ============================================================================
-- Hosting Plans Table Indexes
-- ============================================================================

-- Composite index for active and popular hosting plans
-- Used in: hosting pages, plan listings
ALTER TABLE hosting_plans
ADD INDEX IF NOT EXISTS idx_active_popular (is_active, is_popular);

-- Index for price sorting
ALTER TABLE hosting_plans
ADD INDEX IF NOT EXISTS idx_price (price_monthly);

-- ============================================================================
-- VPS Plans Table Indexes
-- ============================================================================

-- Composite index for active and popular VPS plans
-- Used in: VPS pages, plan listings
ALTER TABLE vps_plans
ADD INDEX IF NOT EXISTS idx_active_popular (is_active, is_popular);

-- Index for price sorting
ALTER TABLE vps_plans
ADD INDEX IF NOT EXISTS idx_price (price_monthly);

-- Index for CPU/RAM searches
ALTER TABLE vps_plans
ADD INDEX IF NOT EXISTS idx_cpu_ram (cpu_cores, ram_gb);

-- ============================================================================
-- Admin Users Table Indexes
-- ============================================================================

-- Index for username login lookups
ALTER TABLE admin_users
ADD INDEX IF NOT EXISTS idx_username (username);

-- Index for email lookups
ALTER TABLE admin_users
ADD INDEX IF NOT EXISTS idx_email (email);

-- Index for active status
ALTER TABLE admin_users
ADD INDEX IF NOT EXISTS idx_is_active (is_active);

-- Composite index for active users by role
ALTER TABLE admin_users
ADD INDEX IF NOT EXISTS idx_active_role (is_active, role);

-- ============================================================================
-- Admin Activity Log Table Indexes
-- ============================================================================

-- Composite index for admin activity history
-- Used in: admin dashboard, activity reports
ALTER TABLE admin_activity_log
ADD INDEX IF NOT EXISTS idx_admin_created (admin_id, created_at DESC);

-- Index for action type filtering
ALTER TABLE admin_activity_log
ADD INDEX IF NOT EXISTS idx_action (action);

-- Index for IP address lookups (security monitoring)
ALTER TABLE admin_activity_log
ADD INDEX IF NOT EXISTS idx_ip (ip_address);

-- ============================================================================
-- VPS Instances Table Indexes (if exists)
-- ============================================================================

-- Index for user VPS lookups
-- ALTER TABLE vps_instances
-- ADD INDEX IF NOT EXISTS idx_user_id (user_id);

-- Index for VPS status
-- ALTER TABLE vps_instances
-- ADD INDEX IF NOT EXISTS idx_status (status);

-- Composite index for user's active VPS
-- ALTER TABLE vps_instances
-- ADD INDEX IF NOT EXISTS idx_user_status (user_id, status);

-- ============================================================================
-- Orders Table Indexes (if exists)
-- ============================================================================

-- Index for customer orders
-- ALTER TABLE orders
-- ADD INDEX IF NOT EXISTS idx_customer_id (customer_id);

-- Index for order status
-- ALTER TABLE orders
-- ADD INDEX IF NOT EXISTS idx_status (status);

-- Composite index for customer's active orders
-- ALTER TABLE orders
-- ADD INDEX IF NOT EXISTS idx_customer_status_created (customer_id, status, created_at DESC);

-- ============================================================================
-- Sessions Table Indexes (if you store sessions in DB)
-- ============================================================================

-- Index for session lookups
-- ALTER TABLE sessions
-- ADD INDEX IF NOT EXISTS idx_session_id (session_id);

-- Index for session expiry cleanup
-- ALTER TABLE sessions
-- ADD INDEX IF NOT EXISTS idx_expires_at (expires_at);

-- ============================================================================
-- Verification
-- ============================================================================

-- После выполнения миграции, проверьте что индексы созданы:
-- SHOW INDEX FROM news;
-- SHOW INDEX FROM domain_zones;
-- SHOW INDEX FROM hosting_plans;
-- SHOW INDEX FROM vps_plans;
-- SHOW INDEX FROM admin_users;
-- SHOW INDEX FROM admin_activity_log;

-- Проверьте производительность запросов с EXPLAIN:
-- EXPLAIN SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC LIMIT 50;

-- ============================================================================
-- Notes
-- ============================================================================

/*
ВАЖНО:
1. Создание индексов на больших таблицах может занять время
2. Индексы ускоряют SELECT, но замедляют INSERT/UPDATE/DELETE
3. Не создавайте индексы на колонки, которые редко используются в WHERE/ORDER BY
4. Composite indexes эффективны для запросов с несколькими условиями
5. Регулярно мониторьте производительность с помощью EXPLAIN

Рекомендации по мониторингу:
- Используйте SHOW INDEX для просмотра существующих индексов
- Используйте EXPLAIN для анализа плана выполнения запросов
- Используйте SHOW TABLE STATUS для проверки размера таблиц и индексов
- Периодически запускайте ANALYZE TABLE для обновления статистики

Примеры EXPLAIN:
EXPLAIN SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC LIMIT 50;
-- Должен использовать idx_published_created

EXPLAIN SELECT * FROM domain_zones WHERE is_active = 1 AND is_popular = 1;
-- Должен использовать idx_active_popular
*/
