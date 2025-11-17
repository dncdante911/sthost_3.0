-- ============================================================================
-- StormHosting UA - Миграция для создания таблицы admin_users
-- Файл: /table/migration_admin_users.sql
-- ============================================================================

-- Создание таблицы admin_users
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL COMMENT 'Хэш пароля (password_hash)',
  `email` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'moderator', 'publisher') NOT NULL DEFAULT 'publisher' COMMENT 'admin=полный доступ, moderator=средний, publisher=только публикация',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=активен, 0=заблокирован',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL COMMENT 'Время последнего входа',
  `created_by` INT UNSIGNED NULL COMMENT 'ID админа который создал',
  `notes` TEXT NULL COMMENT 'Заметки о пользователе',

  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Таблица администраторов системы';

-- Создание дефолтного администратора
-- Пароль: admin123 (ОБЯЗАТЕЛЬНО ИЗМЕНИТЬ ПОСЛЕ ВХОДА!)
INSERT INTO `admin_users` (`username`, `password`, `email`, `role`, `notes`)
VALUES (
  'admin',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
  'admin@stormhosting.ua',
  'admin',
  'Дефолтный администратор. ОБЯЗАТЕЛЬНО измените пароль!'
) ON DUPLICATE KEY UPDATE
  username = username; -- Не перезаписываем если уже существует

-- ============================================================================
-- Создание таблицы логов действий админов (опционально)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `admin_activity_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'Тип действия: login, create_news, edit_domain и т.д.',
  `entity_type` VARCHAR(50) NULL COMMENT 'Тип сущности: news, domain, user и т.д.',
  `entity_id` INT UNSIGNED NULL COMMENT 'ID сущности',
  `details` TEXT NULL COMMENT 'Детали в JSON формате',
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`admin_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Лог действий администраторов';

-- ============================================================================
-- Примеры использования
-- ============================================================================

-- Создание нового администратора
-- INSERT INTO admin_users (username, password, email, role)
-- VALUES ('newadmin', PASSWORD_HASH_HERE, 'newadmin@example.com', 'admin');

-- Создание модератора
-- INSERT INTO admin_users (username, password, email, role)
-- VALUES ('moderator', PASSWORD_HASH_HERE, 'moderator@example.com', 'moderator');

-- Создание публикатора
-- INSERT INTO admin_users (username, password, email, role)
-- VALUES ('publisher', PASSWORD_HASH_HERE, 'publisher@example.com', 'publisher');

-- Деактивация пользователя
-- UPDATE admin_users SET is_active = 0 WHERE username = 'username';

-- Активация пользователя
-- UPDATE admin_users SET is_active = 1 WHERE username = 'username';

-- Изменение роли
-- UPDATE admin_users SET role = 'moderator' WHERE username = 'username';

-- Просмотр всех администраторов
-- SELECT id, username, email, role, is_active, created_at, last_login FROM admin_users ORDER BY created_at DESC;
