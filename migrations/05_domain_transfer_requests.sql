-- Migration: Domain Transfer Requests Table
-- Created: 2025-11-19
-- Purpose: Store domain transfer requests from the transfer form

CREATE TABLE IF NOT EXISTS `domain_transfer_requests` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain` VARCHAR(255) NOT NULL,
  `zone` VARCHAR(50) NOT NULL,
  `auth_code` VARCHAR(255) DEFAULT NULL COMMENT 'EPP/Auth code (encrypted)',
  `contact_email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'cancelled', 'failed') NOT NULL DEFAULT 'pending',
  `processed_by` INT(11) DEFAULT NULL COMMENT 'Admin user ID who processed the request',
  `processed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_domain` (`domain`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_contact_email` (`contact_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Domain transfer requests';
