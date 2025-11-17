/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP TABLE IF EXISTS `cart_domains`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `cart_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) NOT NULL COMMENT 'ID сесії користувача',
  `user_id` int(11) DEFAULT NULL COMMENT 'ID користувача (якщо авторизований)',
  `domain_name` varchar(255) NOT NULL COMMENT 'Повна назва домену',
  `domain_zone` varchar(50) NOT NULL COMMENT 'Доменна зона',
  `registration_period` int(11) NOT NULL DEFAULT 1 COMMENT 'Період реєстрації в роках',
  `price` decimal(10,2) NOT NULL COMMENT 'Ціна за період',
  `whois_privacy` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Захист WHOIS',
  `auto_renewal` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Автопродовження',
  `status` enum('cart','ordered','cancelled') NOT NULL DEFAULT 'cart' COMMENT 'Статус товару',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cart_session` (`session_id`,`status`),
  KEY `idx_cart_user` (`user_id`,`status`),
  KEY `idx_cart_domain` (`domain_name`),
  KEY `idx_cart_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Домени в кошику користувачів';

DROP TABLE IF EXISTS `chat_files`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `chat_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `uploaded_by` varchar(100) DEFAULT 'operator',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `chat_messages`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `sender_type` enum('user','operator','system') NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `message_type` enum('text','file','image','system') DEFAULT 'text',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_messages_session_created` (`session_id`,`created_at`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `chat_notifications`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `chat_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) DEFAULT NULL,
  `type` enum('new_chat','new_message','chat_transfer','urgent') NOT NULL,
  `message` text NOT NULL,
  `data` longtext DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_notifications_operator_read` (`operator_id`,`is_read`,`created_at`),
  CONSTRAINT `chat_notifications_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `support_operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `chat_sessions`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `chat_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `session_key` varchar(64) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `status` enum('waiting','active','closed','transferred','inactive','expired','reset') DEFAULT 'waiting',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `subject` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_key` (`session_key`),
  KEY `user_id` (`user_id`),
  KEY `operator_id` (`operator_id`),
  KEY `idx_chat_sessions_status_priority` (`status`,`priority`,`created_at`),
  CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_sessions_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `support_operators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `chat_settings`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `chat_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `chat_statistics`;

SET @saved_cs_client     = @@character_set_client;

SET character_set_client = utf8mb4;

SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `complaints`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('complaint','suggestion','feedback','question') DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `contact_requests`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `contact_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `form_type` enum('contact','reseller','support') DEFAULT 'contact',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('new','processing','resolved','closed') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `csrf_tokens`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `csrf_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `user_session` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `default_dns_servers`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `default_dns_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `server_address` varchar(255) NOT NULL,
  `priority` int(11) DEFAULT 10,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `domain_check_logs`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `domain_check_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_name` varchar(255) NOT NULL COMMENT 'Назва домену без зони',
  `domain_zone` varchar(50) NOT NULL COMMENT 'Доменна зона (.ua, .com, тощо)',
  `full_domain` varchar(255) NOT NULL COMMENT 'Повна назва домену',
  `is_available` tinyint(1) NOT NULL COMMENT 'Чи доступний домен',
  `check_method` enum('whois','dns','api') NOT NULL DEFAULT 'whois' COMMENT 'Метод перевірки',
  `check_time_ms` int(11) DEFAULT NULL COMMENT 'Час перевірки в мілісекундах',
  `user_ip` varchar(45) DEFAULT NULL COMMENT 'IP користувача',
  `session_id` varchar(128) DEFAULT NULL COMMENT 'ID сесії',
  `user_id` int(11) DEFAULT NULL COMMENT 'ID користувача (якщо авторизований)',
  `whois_response` text DEFAULT NULL COMMENT 'Відповідь WHOIS',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_domain_check_name` (`domain_name`),
  KEY `idx_domain_check_zone` (`domain_zone`),
  KEY `idx_domain_check_full` (`full_domain`),
  KEY `idx_domain_check_date` (`created_at`),
  KEY `idx_domain_check_user` (`user_id`),
  KEY `idx_domain_check_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Логи перевірки доменів на доступність';

DROP TABLE IF EXISTS `domain_page_settings`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `domain_page_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Чи можна показувати на фронті',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`),
  KEY `idx_setting_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Налаштування сторінки реєстрації доменів';

DROP TABLE IF EXISTS `domain_registrars`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `domain_registrars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `api_endpoint` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `supported_zones` text DEFAULT NULL,
  `commission_percent` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `priority` int(11) DEFAULT 1 COMMENT 'Приоритет использования',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `domain_search_statistics`;

SET @saved_cs_client     = @@character_set_client;

SET character_set_client = utf8mb4;

SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `domain_search_trends`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `domain_search_trends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search_term` varchar(255) NOT NULL COMMENT 'Пошуковий запит',
  `search_count` int(11) NOT NULL DEFAULT 1 COMMENT 'Кількість пошуків',
  `last_searched` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_search_term` (`search_term`),
  KEY `idx_search_trends_count` (`search_count` DESC),
  KEY `idx_search_trends_date` (`last_searched`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Тренди пошуку доменів';

DROP TABLE IF EXISTS `domain_whois_servers`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `domain_whois_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` varchar(20) NOT NULL,
  `whois_server` varchar(255) NOT NULL,
  `port` int(11) DEFAULT 43,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_zone` (`zone`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `domain_zones`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `domain_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` varchar(20) NOT NULL,
  `description` text DEFAULT NULL COMMENT 'Опис доменної зони',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Особливості доменної зони' CHECK (json_valid(`features`)),
  `min_registration_period` int(11) NOT NULL DEFAULT 1 COMMENT 'Мінімальний період реєстрації в роках',
  `price_registration` decimal(10,2) NOT NULL,
  `price_renewal` decimal(10,2) NOT NULL,
  `price_transfer` decimal(10,2) NOT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `max_registration_period` int(11) DEFAULT 10 COMMENT 'Максимальний період реєстрації',
  `grace_period_days` int(11) DEFAULT 30 COMMENT 'Період відновлення після закінчення',
  `whois_privacy_available` tinyint(1) DEFAULT 1 COMMENT 'Доступність приховування WHOIS',
  `auto_renewal_available` tinyint(1) DEFAULT 1 COMMENT 'Доступність автопродовження',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `zone` (`zone`),
  KEY `idx_zone` (`zone`),
  KEY `idx_popular` (`is_popular`),
  KEY `idx_active` (`is_active`),
  KEY `idx_domain_zones_popular` (`is_popular`,`is_active`),
  KEY `idx_domain_zones_type` (`zone`(10),`is_active`),
  KEY `idx_domain_zones_price` (`price_registration`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `email_verifications`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `hosting_plans`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `hosting_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ua` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `name_ru` varchar(100) DEFAULT NULL,
  `type` enum('shared','cloud','reseller') NOT NULL,
  `disk_space` int(11) NOT NULL,
  `bandwidth` int(11) NOT NULL,
  `databases` int(11) DEFAULT 0,
  `email_accounts` int(11) DEFAULT 0,
  `domains` int(11) DEFAULT 1,
  `price_monthly` decimal(10,2) NOT NULL,
  `price_yearly` decimal(10,2) NOT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `features_ua` text DEFAULT NULL,
  `features_en` text DEFAULT NULL,
  `features_ru` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_active` (`is_active`),
  KEY `idx_popular` (`is_popular`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `ip_blacklist_cache`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `ip_blacklist_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `rbl_name` varchar(100) NOT NULL,
  `is_listed` tinyint(1) NOT NULL,
  `response_code` varchar(20) DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip_rbl` (`ip_address`,`rbl_name`),
  KEY `idx_ip_rbl` (`ip_address`,`rbl_name`),
  KEY `idx_checked` (`checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ip_check_logs`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `ip_check_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checked_ip` varchar(45) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `results_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`results_json`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`,`created_at`),
  KEY `idx_checked_ip` (`checked_ip`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ip_check_stats`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `ip_check_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_checked` date NOT NULL,
  `total_checks` int(11) DEFAULT 0,
  `unique_ips` int(11) DEFAULT 0,
  `blacklisted_count` int(11) DEFAULT 0,
  `threats_detected` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`date_checked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ip_geolocation_cache`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `ip_geolocation_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `location_stats`;

SET @saved_cs_client     = @@character_set_client;

SET character_set_client = utf8mb4;

SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `login_attempts`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `attempts` int(11) DEFAULT 1,
  `last_attempt` timestamp NULL DEFAULT current_timestamp(),
  `locked_until` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_email` (`email`),
  KEY `idx_locked` (`locked_until`),
  KEY `idx_login_attempts_ip_time` (`ip_address`,`last_attempt`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `news`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_ua` varchar(255) NOT NULL,
  `content_ua` text NOT NULL,
  `content_en` text DEFAULT NULL,
  `content_ru` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_published` (`is_published`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `newsletter_stats`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `newsletter_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('sent','delivered','opened','clicked','bounced','unsubscribed') NOT NULL,
  `opened_at` datetime DEFAULT NULL,
  `clicked_at` datetime DEFAULT NULL,
  `bounce_reason` text DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `subscriber_id` (`subscriber_id`),
  KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `newsletter_subscribers`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `newsletter_templates`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `newsletter_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject_template` varchar(255) DEFAULT NULL,
  `html_content` longtext NOT NULL,
  `text_content` longtext DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `operator_actions`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `operator_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL,
  `action_type` enum('login','logout','take_session','close_session','transfer_session','send_message') NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operator_actions_operator` (`operator_id`,`created_at`),
  KEY `idx_operator_actions_session` (`session_id`),
  KEY `idx_operator_actions_type` (`action_type`,`created_at`),
  CONSTRAINT `operator_actions_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `support_operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `operator_performance`;

SET @saved_cs_client     = @@character_set_client;

SET character_set_client = utf8mb4;

SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `operator_status`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `operator_status` (
  `operator_id` int(11) NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `status_message` varchar(255) DEFAULT 'Доступний',
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `current_sessions` int(11) DEFAULT 0,
  `max_sessions` int(11) DEFAULT 5,
  PRIMARY KEY (`operator_id`),
  KEY `idx_operator_status_online` (`is_online`,`last_activity`),
  CONSTRAINT `operator_status_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `support_operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `password_resets`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_email` (`email`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `popular_checked_sites`;

SET @saved_cs_client     = @@character_set_client;

SET character_set_client = utf8mb4;

SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `popular_domains_view`;

SET @saved_cs_client     = @@character_set_client;

SET character_set_client = utf8mb4;

SET character_set_client = @saved_cs_client;

DROP TABLE IF EXISTS `proxmox_migration_log`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `proxmox_migration_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) DEFAULT NULL,
  `old_identifier` varchar(100) DEFAULT NULL COMMENT 'Старый libvirt_name если был',
  `new_proxmox_vmid` int(11) DEFAULT NULL,
  `migration_status` enum('pending','in_progress','completed','failed') DEFAULT 'pending',
  `migration_date` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vps_id` (`vps_id`),
  KEY `idx_migration_status` (`migration_status`),
  KEY `idx_migration_date` (`migration_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лог миграции на Proxmox VE 9';

DROP TABLE IF EXISTS `remember_tokens`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `security_logs`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4233 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `site_alerts`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `site_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monitor_id` int(11) NOT NULL COMMENT 'ID записи мониторинга',
  `alert_type` enum('down','slow','ssl_expiring','ssl_expired') NOT NULL COMMENT 'Тип алерта',
  `message` text NOT NULL COMMENT 'Сообщение алерта',
  `is_resolved` tinyint(1) DEFAULT 0 COMMENT 'Решен ли алерт',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL COMMENT 'Время решения алерта',
  PRIMARY KEY (`id`),
  KEY `idx_monitor_type` (`monitor_id`,`alert_type`),
  KEY `idx_unresolved` (`is_resolved`,`created_at`),
  CONSTRAINT `site_alerts_ibfk_1` FOREIGN KEY (`monitor_id`) REFERENCES `site_monitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Алерты и уведомления';

DROP TABLE IF EXISTS `site_check_logs`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `site_check_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(512) NOT NULL COMMENT 'URL проверяемого сайта',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP адрес пользователя',
  `user_agent` text DEFAULT NULL COMMENT 'User Agent браузера',
  `results_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Результаты проверки в JSON формате' CHECK (json_valid(`results_json`)),
  `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Время создания записи',
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`,`created_at`) COMMENT 'Индекс для rate limiting',
  KEY `idx_url` (`url`(100)) COMMENT 'Индекс для поиска по URL',
  KEY `idx_created` (`created_at`) COMMENT 'Индекс для сортировки по времени'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Логи проверки доступности сайтов';

DROP TABLE IF EXISTS `site_monitor_results`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `site_monitor_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monitor_id` int(11) NOT NULL COMMENT 'ID записи мониторинга',
  `location` varchar(50) NOT NULL COMMENT 'Локация проверки',
  `status_code` int(11) DEFAULT NULL COMMENT 'HTTP статус код',
  `response_time` int(11) DEFAULT NULL COMMENT 'Время ответа в миллисекундах',
  `error_message` text DEFAULT NULL COMMENT 'Сообщение об ошибке если есть',
  `is_up` tinyint(1) NOT NULL COMMENT 'Доступен ли сайт',
  `checked_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Время проверки',
  PRIMARY KEY (`id`),
  KEY `idx_monitor_time` (`monitor_id`,`checked_at`),
  KEY `idx_location` (`location`),
  KEY `idx_status` (`is_up`,`checked_at`),
  CONSTRAINT `site_monitor_results_ibfk_1` FOREIGN KEY (`monitor_id`) REFERENCES `site_monitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Результаты мониторинга сайтов';

DROP TABLE IF EXISTS `site_monitors`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `site_monitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID пользователя (NULL для анонимных)',
  `url` varchar(512) NOT NULL COMMENT 'URL для мониторинга',
  `check_interval` int(11) DEFAULT 300 COMMENT 'Интервал проверки в секундах',
  `locations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Массив локаций для проверки' CHECK (json_valid(`locations`)),
  `email_notifications` tinyint(1) DEFAULT 0 COMMENT 'Включены ли email уведомления',
  `webhook_url` varchar(512) DEFAULT NULL COMMENT 'URL для webhook уведомлений',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Активен ли мониторинг',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_next_check` (`created_at`,`check_interval`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Настройки мониторинга сайтов';

DROP TABLE IF EXISTS `site_settings`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `is_public` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_key` (`setting_key`),
  KEY `idx_public` (`is_public`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `sms_codes`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `sms_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `action` varchar(50) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_action` (`user_id`,`action`),
  CONSTRAINT `sms_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `support_operators`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `support_operators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('operator','supervisor','admin') DEFAULT 'operator',
  `department` varchar(100) DEFAULT 'general',
  `is_online` tinyint(1) DEFAULT 0,
  `last_activity` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `support_operators_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `translations`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translation_key` varchar(255) NOT NULL,
  `language` enum('ua','en','ru') NOT NULL,
  `translation_value` text NOT NULL,
  `section` varchar(100) DEFAULT 'general',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_translation` (`translation_key`,`language`),
  KEY `idx_key` (`translation_key`),
  KEY `idx_lang` (`language`),
  KEY `idx_section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_activity`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_sessions`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `whmcs_client_id` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `language` enum('ua','en','ru') DEFAULT 'ua',
  `registration_date` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `marketing_emails` tinyint(1) DEFAULT 0,
  `fossbilling_client_id` int(11) DEFAULT NULL,
  `ispmanager_username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_active` (`is_active`),
  KEY `idx_users_email_active` (`email`,`is_active`),
  KEY `idx_users_registration_date` (`registration_date`),
  KEY `idx_whmcs_client_id` (`whmcs_client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vds_plans`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vds_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ua` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `name_ru` varchar(100) DEFAULT NULL,
  `type` enum('virtual','dedicated') NOT NULL,
  `cpu_cores` int(11) NOT NULL,
  `ram_mb` int(11) NOT NULL,
  `disk_gb` int(11) NOT NULL,
  `bandwidth_gb` int(11) NOT NULL,
  `price_monthly` decimal(10,2) NOT NULL,
  `price_yearly` decimal(10,2) NOT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `features_ua` text DEFAULT NULL,
  `features_en` text DEFAULT NULL,
  `features_ru` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_actions`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('create','start','stop','restart','reinstall','suspend','unsuspend','terminate','backup','restore','change_password','resize') NOT NULL,
  `status` enum('pending','running','completed','failed','cancelled') DEFAULT 'pending',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `error_message` text DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vps_id` (`vps_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_status` (`status`),
  KEY `idx_started` (`started_at`),
  CONSTRAINT `vps_actions_ibfk_1` FOREIGN KEY (`vps_id`) REFERENCES `vps_instances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_backups`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `backup_type` enum('manual','automatic','before_reinstall') DEFAULT 'manual',
  `status` enum('creating','completed','failed','deleted') DEFAULT 'creating',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_vps_id` (`vps_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `vps_backups_ibfk_1` FOREIGN KEY (`vps_id`) REFERENCES `vps_instances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_instances`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `whmcs_service_id` int(11) DEFAULT NULL,
  `hostname` varchar(255) NOT NULL,
  `proxmox_vmid` int(11) DEFAULT NULL COMMENT 'Proxmox VM ID',
  `proxmox_node` varchar(50) DEFAULT 'pve' COMMENT 'Proxmox node name',
  `domain_name` varchar(255) DEFAULT NULL,
  `legacy_libvirt_name` varchar(100) DEFAULT NULL COMMENT 'Legacy: старое имя в Libvirt',
  `ip_address` varchar(45) DEFAULT NULL,
  `ip_gateway` varchar(45) DEFAULT '192.168.0.10',
  `ip_netmask` varchar(45) DEFAULT '255.255.255.0',
  `dns_servers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dns_servers`)),
  `os_template` varchar(100) DEFAULT NULL,
  `root_password` varchar(255) DEFAULT NULL,
  `vnc_password` varchar(255) DEFAULT NULL,
  `status` enum('pending','creating','active','stopped','suspended','terminated','error') DEFAULT 'pending',
  `cpu_cores` int(11) NOT NULL,
  `ram_mb` int(11) NOT NULL,
  `disk_gb` int(11) NOT NULL,
  `bandwidth_gb` int(11) NOT NULL,
  `bandwidth_used` bigint(20) DEFAULT 0,
  `last_bandwidth_reset` timestamp NULL DEFAULT current_timestamp(),
  `suspend_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip` (`ip_address`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_status` (`status`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_proxmox_vmid` (`proxmox_vmid`),
  KEY `idx_proxmox_node` (`proxmox_node`),
  KEY `idx_hostname` (`hostname`),
  CONSTRAINT `vps_instances_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vps_instances_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `vps_plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_ip_pool`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_ip_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `gateway` varchar(45) DEFAULT '192.168.0.10',
  `netmask` varchar(45) DEFAULT '255.255.255.0',
  `dns_servers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dns_servers`)),
  `vps_id` int(11) DEFAULT NULL,
  `is_reserved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `assigned_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip` (`ip_address`),
  KEY `idx_vps_id` (`vps_id`),
  KEY `idx_available` (`vps_id`,`is_reserved`),
  CONSTRAINT `vps_ip_pool_ibfk_1` FOREIGN KEY (`vps_id`) REFERENCES `vps_instances` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_operations_log`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_operations_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `operation_type` varchar(50) NOT NULL,
  `status` enum('started','running','completed','failed') DEFAULT 'started',
  `started_at` timestamp NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `result_message` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  PRIMARY KEY (`id`),
  KEY `vps_id` (`vps_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `vps_operations_log_ibfk_1` FOREIGN KEY (`vps_id`) REFERENCES `vps_instances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vps_operations_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_os_templates`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_os_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `version` varchar(50) DEFAULT NULL,
  `architecture` enum('x64','x86','arm64') DEFAULT 'x64',
  `type` enum('linux','windows','bsd','other') DEFAULT 'linux',
  `icon` varchar(255) DEFAULT NULL,
  `proxmox_template_id` int(11) DEFAULT NULL COMMENT 'Proxmox Template VMID',
  `proxmox_storage` varchar(50) DEFAULT 'local-lvm' COMMENT 'Proxmox storage for template',
  `proxmox_node` varchar(50) DEFAULT 'pve' COMMENT 'Proxmox node where template is stored',
  `default_username` varchar(50) DEFAULT 'root',
  `min_ram_mb` int(11) DEFAULT 512,
  `min_disk_gb` int(11) DEFAULT 10,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name_version` (`name`,`version`),
  KEY `idx_type` (`type`),
  KEY `idx_active` (`is_active`),
  KEY `idx_sort` (`sort_order`),
  KEY `idx_proxmox_template_id` (`proxmox_template_id`),
  KEY `idx_proxmox_node` (`proxmox_node`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_plans`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `whmcs_product_id` int(11) DEFAULT NULL,
  `name_ua` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `name_ru` varchar(100) DEFAULT NULL,
  `description_ua` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_ru` text DEFAULT NULL,
  `cpu_cores` int(11) NOT NULL DEFAULT 1,
  `ram_mb` int(11) NOT NULL DEFAULT 512,
  `disk_gb` int(11) NOT NULL DEFAULT 10,
  `bandwidth_gb` int(11) NOT NULL DEFAULT 100,
  `price_monthly` decimal(10,2) NOT NULL,
  `price_yearly` decimal(10,2) NOT NULL,
  `setup_fee` decimal(10,2) DEFAULT 0.00,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `features_ua` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_ua`)),
  `features_en` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_en`)),
  `features_ru` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_ru`)),
  `os_templates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`os_templates`)),
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_popular` (`is_popular`),
  KEY `idx_sort` (`sort_order`),
  KEY `idx_whmcs_product_id` (`whmcs_product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_snapshot_log`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_snapshot_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `snapshot_id` int(11) NOT NULL,
  `action` enum('create','restore','delete') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `vps_id` (`vps_id`),
  KEY `snapshot_id` (`snapshot_id`),
  CONSTRAINT `vps_snapshot_log_ibfk_1` FOREIGN KEY (`vps_id`) REFERENCES `vps_instances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vps_snapshot_log_ibfk_2` FOREIGN KEY (`snapshot_id`) REFERENCES `vps_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_snapshots`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_snapshots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `proxmox_snapshot_name` varchar(150) DEFAULT NULL COMMENT 'Proxmox snapshot name',
  `description` text DEFAULT NULL,
  `status` enum('active','deleted') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vps_snapshot` (`vps_id`,`name`),
  KEY `idx_proxmox_snapshot` (`proxmox_snapshot_name`),
  CONSTRAINT `vps_snapshots_ibfk_1` FOREIGN KEY (`vps_id`) REFERENCES `vps_instances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `vps_statistics`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

CREATE TABLE `vps_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `cpu_usage` decimal(5,2) DEFAULT NULL,
  `ram_usage_mb` int(11) DEFAULT NULL,
  `disk_usage_gb` decimal(10,2) DEFAULT NULL,
  `network_rx_bytes` bigint(20) DEFAULT 0,
  `network_tx_bytes` bigint(20) DEFAULT 0,
  `recorded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vps_id` (`vps_id`),
  KEY `idx_recorded` (`recorded_at`),
  CONSTRAINT `vps_statistics_ibfk_1` FOREIGN KEY (`vps_id`) REFERENCES `vps_instances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

