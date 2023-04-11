-- -------------------------------------------------------------
-- TablePlus 5.3.5(494)
--
-- https://tableplus.com/
--
-- Database: trackandtrace
-- Generation Time: 2023-04-11 11:29:28.2170
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `book_types`;
CREATE TABLE `book_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `distribution_steps`;
CREATE TABLE `distribution_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `distribution_id` bigint unsigned NOT NULL,
  `route_id` bigint unsigned NOT NULL,
  `step` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `distributions`;
CREATE TABLE `distributions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `distributions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grades`;
CREATE TABLE `grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grades_subject_id_foreign` (`subject_id`),
  CONSTRAINT `grades_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `kebeles`;
CREATE TABLE `kebeles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region_id` bigint unsigned NOT NULL DEFAULT '1',
  `country_id` bigint unsigned DEFAULT NULL,
  `zone_id` bigint unsigned DEFAULT NULL,
  `woreda_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kebeles_name_region_id_country_id_zone_id_woreda_id_unique` (`name`,`region_id`,`country_id`,`zone_id`,`woreda_id`),
  KEY `kebeles_region_id_foreign` (`region_id`),
  KEY `kebeles_country_id_foreign` (`country_id`),
  KEY `kebeles_zone_id_foreign` (`zone_id`),
  KEY `kebeles_woreda_id_foreign` (`woreda_id`),
  CONSTRAINT `kebeles_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `kebeles_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `kebeles_woreda_id_foreign` FOREIGN KEY (`woreda_id`) REFERENCES `woredas` (`id`),
  CONSTRAINT `kebeles_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `organization_types`;
CREATE TABLE `organization_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization_types_name_unique` (`name`),
  UNIQUE KEY `organization_types_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_user_id` bigint unsigned NOT NULL,
  `country_id` bigint unsigned NOT NULL,
  `region_id` bigint unsigned NOT NULL,
  `zone_id` bigint unsigned DEFAULT NULL,
  `woreda_id` bigint unsigned DEFAULT NULL,
  `kebele_id` bigint unsigned DEFAULT NULL,
  `organization_type_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organizations_name_unique` (`name`),
  UNIQUE KEY `organizations_email_unique` (`email`),
  KEY `organizations_country_id_foreign` (`country_id`),
  KEY `organizations_region_id_foreign` (`region_id`),
  KEY `organizations_zone_id_foreign` (`zone_id`),
  KEY `organizations_woreda_id_foreign` (`woreda_id`),
  KEY `organizations_kebele_id_foreign` (`kebele_id`),
  KEY `organizations_organization_type_id_foreign` (`organization_type_id`),
  CONSTRAINT `organizations_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `organizations_kebele_id_foreign` FOREIGN KEY (`kebele_id`) REFERENCES `kebeles` (`id`),
  CONSTRAINT `organizations_organization_type_id_foreign` FOREIGN KEY (`organization_type_id`) REFERENCES `organization_types` (`id`),
  CONSTRAINT `organizations_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `organizations_woreda_id_foreign` FOREIGN KEY (`woreda_id`) REFERENCES `woredas` (`id`),
  CONSTRAINT `organizations_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `print_orders`;
CREATE TABLE `print_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `regions`;
CREATE TABLE `regions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_city` tinyint(1) NOT NULL DEFAULT '0',
  `country_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `regions_name_country_id_unique` (`name`,`country_id`),
  KEY `regions_country_id_foreign` (`country_id`),
  CONSTRAINT `regions_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `routes`;
CREATE TABLE `routes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `stores`;
CREATE TABLE `stores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `assigned_user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_name_unique` (`name`),
  UNIQUE KEY `subjects_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `traces`;
CREATE TABLE `traces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `is_firtst_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `organization_id` bigint unsigned DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ware_houses`;
CREATE TABLE `ware_houses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `assigned_user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `woredas`;
CREATE TABLE `woredas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region_id` bigint unsigned NOT NULL DEFAULT '1',
  `country_id` bigint unsigned DEFAULT NULL,
  `zone_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `woredas_name_region_id_country_id_zone_id_unique` (`name`,`region_id`,`country_id`,`zone_id`),
  KEY `woredas_region_id_foreign` (`region_id`),
  KEY `woredas_country_id_foreign` (`country_id`),
  KEY `woredas_zone_id_foreign` (`zone_id`),
  CONSTRAINT `woredas_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `woredas_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `woredas_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `zones`;
CREATE TABLE `zones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_subcity` tinyint(1) NOT NULL DEFAULT '0',
  `region_id` bigint unsigned DEFAULT NULL,
  `country_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `zones_name_region_id_country_id_unique` (`name`,`region_id`,`country_id`),
  KEY `zones_region_id_foreign` (`region_id`),
  KEY `zones_country_id_foreign` (`country_id`),
  CONSTRAINT `zones_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `zones_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `countries` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Ethiopia', 'ET', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(2, 'Afghanistan', 'AF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(3, 'Albania', 'AL', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(4, 'Algeria', 'DZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(5, 'Andorra', 'AD', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(6, 'Angola', 'AO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(7, 'Antigua and Barbuda', 'AG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(8, 'Argentina', 'AR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(9, 'Armenia', 'AM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(10, 'Australia', 'AU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(11, 'Austria', 'AT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(12, 'Azerbaijan', 'AZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(13, 'Bahamas', 'BS', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(14, 'Bahrain', 'BH', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(15, 'Bangladesh', 'BD', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(16, 'Barbados', 'BB', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(17, 'Belarus', 'BY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(18, 'Belgium', 'BE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(19, 'Belize', 'BZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(20, 'Benin', 'BJ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(21, 'Bhutan', 'BT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(22, 'Bolivia', 'BO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(23, 'Bosnia and Herzegovina', 'BA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(24, 'Botswana', 'BW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(25, 'Brazil', 'BR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(26, 'Brunei', 'BN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(27, 'Bulgaria', 'BG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(28, 'Burkina Faso', 'BF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(29, 'Burundi', 'BI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(30, 'Cambodia', 'KH', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(31, 'Cameroon', 'CM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(32, 'Canada', 'CA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(33, 'Cape Verde', 'CV', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(34, 'Central African Republic', 'CF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(35, 'Chad', 'TD', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(36, 'Chile', 'CL', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(37, 'People\'s Republic of China', 'CN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(38, 'Colombia', 'CO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(39, 'Comoros', 'KM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(40, 'Congo - Kinshasa', 'CD', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(41, 'Congo - Brazzaville', 'CG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(42, 'Costa Rica', 'CR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(43, 'Cote d\'Ivoire (The Ivory Coast)', 'CI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(44, 'Croatia', 'HR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(45, 'Cuba', 'CU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(46, 'Cyprus', 'CY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(47, 'Czech Republic', 'CZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(48, 'Denmark', 'DK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(49, 'Djibouti', 'DJ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(50, 'Dominica', 'DM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(51, 'Dominican Republic', 'DO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(52, 'Ecuador', 'EC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(53, 'Egypt', 'EG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(54, 'El Salvador', 'SV', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(55, 'Equatorial Guinea', 'GQ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(56, 'Eritrea', 'ER', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(57, 'Estonia', 'EE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(58, 'Fiji', 'FJ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(59, 'Finland', 'FI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(60, 'France', 'FR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(61, 'Gabon', 'GA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(62, 'Gambia', 'GM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(63, 'Georgia', 'GE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(64, 'Germany', 'DE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(65, 'Ghana', 'GH', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(66, 'Greece', 'GR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(67, 'Grenada', 'GD', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(68, 'Guatemala', 'GT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(69, 'Guinea', 'GN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(70, 'Guinea-Bissau', 'GW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(71, 'Guyana', 'GY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(72, 'Haiti', 'HT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(73, 'Honduras', 'HN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(74, 'Hungary', 'HU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(75, 'Iceland', 'IS', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(76, 'India', 'IN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(77, 'Indonesia', 'ID', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(78, 'Iran', 'IR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(79, 'Iraq', 'IQ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(80, 'Ireland', 'IE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(81, 'Israel', 'IL', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(82, 'Italy', 'IT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(83, 'Jamaica', 'JM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(84, 'Japan', 'JP', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(85, 'Jordan', 'JO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(86, 'Kazakhstan', 'KZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(87, 'Kenya', 'KE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(88, 'Kiribati', 'KI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(89, 'North Korea', 'KP', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(90, 'South Korea', 'KR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(91, 'Kuwait', 'KW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(92, 'Kyrgyzstan', 'KG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(93, 'Laos', 'LA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(94, 'Latvia', 'LV', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(95, 'Lebanon', 'LB', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(96, 'Lesotho', 'LS', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(97, 'Liberia', 'LR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(98, 'Libya', 'LY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(99, 'Liechtenstein', 'LI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(100, 'Lithuania', 'LT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(101, 'Luxembourg', 'LU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(102, 'Macedonia', 'MK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(103, 'Madagascar', 'MG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(104, 'Malawi', 'MW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(105, 'Malaysia', 'MY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(106, 'Maldives', 'MV', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(107, 'Mali', 'ML', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(108, 'Malta', 'MT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(109, 'Marshall Islands', 'MH', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(110, 'Mauritania', 'MR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(111, 'Mauritius', 'MU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(112, 'Mexico', 'MX', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(113, 'Micronesia', 'FM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(114, 'Moldova', 'MD', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(115, 'Monaco', 'MC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(116, 'Mongolia', 'MN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(117, 'Montenegro', 'ME', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(118, 'Morocco', 'MA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(119, 'Mozambique', 'MZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(120, 'Myanmar (Burma)', 'MM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(121, 'Namibia', 'NA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(122, 'Nauru', 'NR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(123, 'Nepal', 'NP', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(124, 'Netherlands', 'NL', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(125, 'New Zealand', 'NZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(126, 'Nicaragua', 'NI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(127, 'Niger', 'NE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(128, 'Nigeria', 'NG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(129, 'Norway', 'NO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(130, 'Oman', 'OM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(131, 'Pakistan', 'PK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(132, 'Palau', 'PW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(133, 'Panama', 'PA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(134, 'Papua New Guinea', 'PG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(135, 'Paraguay', 'PY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(136, 'Peru', 'PE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(137, 'Philippines', 'PH', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(138, 'Poland', 'PL', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(139, 'Portugal', 'PT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(140, 'Qatar', 'QA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(141, 'Romania', 'RO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(142, 'Russia', 'RU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(143, 'Rwanda', 'RW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(144, 'Saint Kitts and Nevis', 'KN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(145, 'Saint Lucia', 'LC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(146, 'Saint Vincent and the Grenadines', 'VC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(147, 'Samoa', 'WS', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(148, 'San Marino', 'SM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(149, 'Sao Tome and Principe', 'ST', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(150, 'Saudi Arabia', 'SA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(151, 'Senegal', 'SN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(152, 'Serbia', 'RS', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(153, 'Seychelles', 'SC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(154, 'Sierra Leone', 'SL', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(155, 'Singapore', 'SG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(156, 'Slovakia', 'SK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(157, 'Slovenia', 'SI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(158, 'Solomon Islands', 'SB', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(159, 'Somalia', 'SO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(160, 'South Africa', 'ZA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(161, 'Spain', 'ES', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(162, 'Sri Lanka', 'LK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(163, 'Sudan', 'SD', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(164, 'Suriname', 'SR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(165, 'Swaziland', 'SZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(166, 'Sweden', 'SE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(167, 'Switzerland', 'CH', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(168, 'Syria', 'SY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(169, 'Tajikistan', 'TJ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(170, 'Tanzania', 'TZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(171, 'Thailand', 'TH', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(172, 'Timor-Leste (East Timor)', 'TL', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(173, 'Togo', 'TG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(174, 'Tonga', 'TO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(175, 'Trinidad and Tobago', 'TT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(176, 'Tunisia', 'TN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(177, 'Turkey', 'TR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(178, 'Turkmenistan', 'TM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(179, 'Tuvalu', 'TV', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(180, 'Uganda', 'UG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(181, 'Ukraine', 'UA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(182, 'United Arab Emirates', 'AE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(183, 'United Kingdom', 'GB', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(184, 'United States', 'US', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(185, 'Uruguay', 'UY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(186, 'Uzbekistan', 'UZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(187, 'Vanuatu', 'VU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(188, 'Vatican City', 'VA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(189, 'Venezuela', 'VE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(190, 'Vietnam', 'VN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(191, 'Yemen', 'YE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(192, 'Zambia', 'ZM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(193, 'Zimbabwe', 'ZW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(194, 'Abkhazia', 'GE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(195, 'Taiwan', 'TW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(196, 'Nagorno-Karabakh', 'AZ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(197, 'Northern Cyprus', 'CY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(198, 'Pridnestrovie (Transnistria)', 'MD', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(199, 'Somaliland', 'SO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(200, 'South Ossetia', 'GE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(201, 'Ashmore and Cartier Islands', 'AU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(202, 'Christmas Island', 'CX', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(203, 'Cocos (Keeling) Islands', 'CC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(204, 'Coral Sea Islands', 'AU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(205, 'Heard Island and McDonald Islands', 'HM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(206, 'Norfolk Island', 'NF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(207, 'New Caledonia', 'NC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(208, 'French Polynesia', 'PF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(209, 'Mayotte', 'YT', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(210, 'Saint Barthelemy', 'GP', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(211, 'Saint Martin', 'GP', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(212, 'Saint Pierre and Miquelon', 'PM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(213, 'Wallis and Futuna', 'WF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(214, 'French Southern and Antarctic Lands', 'TF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(215, 'Clipperton Island', 'PF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(216, 'Bouvet Island', 'BV', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(217, 'Cook Islands', 'CK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(218, 'Niue', 'NU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(219, 'Tokelau', 'TK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(220, 'Guernsey', 'GG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(221, 'Isle of Man', 'IM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(222, 'Jersey', 'JE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(223, 'Anguilla', 'AI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(224, 'Bermuda', 'BM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(225, 'British Indian Ocean Territory', 'IO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(226, 'British Sovereign Base Areas', '', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(227, 'British Virgin Islands', 'VG', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(228, 'Cayman Islands', 'KY', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(229, 'Falkland Islands (Islas Malvinas)', 'FK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(230, 'Gibraltar', 'GI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(231, 'Montserrat', 'MS', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(232, 'Pitcairn Islands', 'PN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(233, 'Saint Helena', 'SH', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(234, 'South Georgia & South Sandwich Islands', 'GS', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(235, 'Turks and Caicos Islands', 'TC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(236, 'Northern Mariana Islands', 'MP', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(237, 'Puerto Rico', 'PR', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(238, 'American Samoa', 'AS', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(239, 'Baker Island', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(240, 'Guam', 'GU', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(241, 'Howland Island', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(242, 'Jarvis Island', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(243, 'Johnston Atoll', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(244, 'Kingman Reef', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(245, 'Midway Islands', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(246, 'Navassa Island', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(247, 'Palmyra Atoll', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(248, 'U.S. Virgin Islands', 'VI', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(249, 'Wake Island', 'UM', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(250, 'Hong Kong', 'HK', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(251, 'Macau', 'MO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(252, 'Faroe Islands', 'FO', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(253, 'Greenland', 'GL', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(254, 'French Guiana', 'GF', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(255, 'Guadeloupe', 'GP', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(256, 'Martinique', 'MQ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(257, 'Reunion', 'RE', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(258, 'Aland', 'AX', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(259, 'Aruba', 'AW', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(260, 'Netherlands Antilles', 'AN', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(261, 'Svalbard', 'SJ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(262, 'Ascension', 'AC', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(263, 'Tristan da Cunha', 'TA', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(264, 'Australian Antarctic Territory', 'AQ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(265, 'Ross Dependency', 'AQ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(266, 'Peter I Island', 'AQ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(267, 'Queen Maud Land', 'AQ', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(268, 'British Antarctic Territory', 'AQ', '2022-12-13 20:25:43', '2022-12-13 20:25:43');

INSERT INTO `grades` (`id`, `name`, `code`, `description`, `subject_id`, `created_at`, `updated_at`) VALUES
(1, 'Grade 1', 'G-01', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(2, 'Grade 1', 'G-01', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(3, 'Grade 1', 'G-01', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(4, 'Grade 1', 'G-01', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(5, 'Grade 2', 'G-02', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(6, 'Grade 2', 'G-02', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(7, 'Grade 2', 'G-02', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(8, 'Grade 2', 'G-02', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(9, 'Grade 3', 'G-03', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(10, 'Grade 3', 'G-03', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(11, 'Grade 3', 'G-03', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(12, 'Grade 3', 'G-03', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(13, 'Grade 4', 'G-04', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(14, 'Grade 4', 'G-04', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(15, 'Grade 4', 'G-04', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(16, 'Grade 4', 'G-04', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(17, 'Grade 5', 'G-05', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(18, 'Grade 5', 'G-05', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(19, 'Grade 5', 'G-05', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(20, 'Grade 5', 'G-05', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(21, 'Grade 6', 'G-06', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(22, 'Grade 6', 'G-06', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(23, 'Grade 6', 'G-06', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(24, 'Grade 6', 'G-06', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(25, 'Grade 7', 'G-07', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(26, 'Grade 7', 'G-07', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(27, 'Grade 7', 'G-07', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(28, 'Grade 7', 'G-07', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(29, 'Grade 8', 'G-08', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(30, 'Grade 8', 'G-08', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(31, 'Grade 8', 'G-08', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(32, 'Grade 8', 'G-08', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(33, 'Grade 9', 'G-09', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(34, 'Grade 9', 'G-09', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(35, 'Grade 9', 'G-09', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(36, 'Grade 9', 'G-09', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(37, 'Grade 10', 'G-10', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(38, 'Grade 10', 'G-10', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(39, 'Grade 10', 'G-10', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(40, 'Grade 10', 'G-10', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(41, 'Grade 11', 'G-11', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(42, 'Grade 11', 'G-11', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(43, 'Grade 11', 'G-11', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(44, 'Grade 11', 'G-11', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(45, 'Grade 12', 'G-12', NULL, 1, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(46, 'Grade 12', 'G-12', NULL, 2, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(47, 'Grade 12', 'G-12', NULL, 3, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(48, 'Grade 12', 'G-12', NULL, 4, '2022-12-13 20:25:43', '2022-12-13 20:25:43');

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(2, '2022_06_12_000000_create_users_table', 1),
(3, '2022_06_12_100000_create_password_resets_table', 1),
(4, '2022_06_12_200000_add_two_factor_columns_to_users_table', 1),
(5, '2022_06_19_000000_create_failed_jobs_table', 1),
(6, '2022_07_06_183313_create_sessions_table', 1),
(7, '2022_07_08_062121_create_countries_table', 1),
(8, '2022_07_08_062121_create_regions_table', 1),
(9, '2022_07_08_062121_create_zones_table', 1),
(10, '2022_07_08_062122_create_woredas_table', 1),
(11, '2022_07_08_062245_create_organization_types_table', 1),
(12, '2022_07_16_110107_create_subjects_table', 1),
(13, '2022_07_16_110108_create_grades_table', 1),
(14, '2022_07_16_110147_create_book_types_table', 1),
(15, '2022_07_16_110247_create_books_table', 1),
(16, '2022_07_16_110449_create_ware_houses_table', 1),
(17, '2022_07_16_110506_create_print_orders_table', 1),
(18, '2022_07_16_110522_create_routes_table', 1),
(19, '2022_07_16_110540_create_distributions_table', 1),
(20, '2022_07_16_110556_create_traces_table', 1),
(21, '2022_07_27_085501_create_kebeles_table', 1),
(22, '2022_07_28_062256_create_organizations_table', 1),
(23, '2022_08_15_134557_create_permission_tables', 1),
(24, '2022_09_08_135750_create_distribution_steps_table', 1),
(25, '2022_09_20_121851_create_stores_table', 1);

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 2),
(1, 'App\\Models\\User', 3),
(1, 'App\\Models\\User', 4),
(1, 'App\\Models\\User', 5),
(1, 'App\\Models\\User', 6),
(2, 'App\\Models\\User', 3),
(2, 'App\\Models\\User', 7),
(3, 'App\\Models\\User', 1),
(4, 'App\\Models\\User', 3),
(5, 'App\\Models\\User', 3),
(5, 'App\\Models\\User', 6),
(6, 'App\\Models\\User', 3),
(6, 'App\\Models\\User', 5),
(6, 'App\\Models\\User', 6),
(7, 'App\\Models\\User', 3),
(8, 'App\\Models\\User', 3),
(9, 'App\\Models\\User', 3),
(9, 'App\\Models\\User', 6),
(9, 'App\\Models\\User', 7);

INSERT INTO `organization_types` (`id`, `name`, `code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'school', 'SH01', 'In this section we will see how to use fake data in blade file without creating factory file. You can create fake data using fake() helper method. It is helpful to create quick prototyping a design and fake data.', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(2, 'campany', 'CAM01', 'In this section we will see how to use fake data in blade file without creating factory file. You can create fake data using fake() helper method. It is helpful to create quick prototyping a design and fake data.', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(3, 'organization', 'ORG01', 'In this section we will see how to use fake data in blade file without creating factory file. You can create fake data using fake() helper method. It is helpful to create quick prototyping a design and fake data.', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(4, 'Printer', 'P01', 'In this section we will see how to use fake data in blade file without creating factory file. You can create fake data using fake() helper method. It is helpful to create quick prototyping a design and fake data.', '2022-12-13 20:25:43', '2022-12-13 20:25:43');

INSERT INTO `organizations` (`id`, `name`, `website`, `email`, `logo`, `telephone`, `mobile`, `assigned_user_id`, `country_id`, `region_id`, `zone_id`, `woreda_id`, `kebele_id`, `organization_type_id`, `created_at`, `updated_at`) VALUES
(1, 'In inventore ipsa e', 'https://www.kun.in', 'rakufy@mailinator.com', 'https://ui-avatars.com/api/?name=I+i+i+e&color=7F9CF5&background=EBF4FF', '84', '99', 2, 1, 1, NULL, NULL, NULL, 3, '2022-12-13 21:06:16', '2022-12-13 21:06:16');

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'user-list', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(2, 'user-create', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(3, 'user-edit', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(4, 'user-delete', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(5, 'org-list', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(6, 'org-create', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(7, 'org-edit', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(8, 'org-update', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(9, 'org-delete', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(10, 'org-publish', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(11, 'org-unpublish', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(12, 'role-list', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(13, 'role-create', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(14, 'role-edit', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(15, 'role-delete', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(16, 'location-list', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(17, 'location-create', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(18, 'location-edit', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(19, 'location-delete', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(20, 'book-list', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(21, 'book-show', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(22, 'book-edit', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(23, 'book-update', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(24, 'book-delete', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(25, 'view-logs', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43');

INSERT INTO `regions` (`id`, `name`, `code`, `is_city`, `country_id`, `created_at`, `updated_at`) VALUES
(1, 'Addis Abeba', NULL, 1, 1, '2022-12-13 21:06:03', '2022-12-13 21:06:03');

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(7, 1),
(8, 1),
(12, 2),
(12, 5),
(13, 2),
(14, 2),
(15, 2),
(16, 4),
(16, 5),
(17, 4),
(18, 4),
(18, 5),
(18, 7),
(19, 4),
(19, 7),
(20, 1),
(20, 8),
(21, 1),
(21, 5),
(21, 8),
(22, 6),
(22, 8),
(23, 6),
(23, 8),
(24, 6),
(24, 8),
(25, 9);

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Org-Manager', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(2, 'Admin', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(3, 'Super-Admin', 'web', '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(4, 'Location Manager', 'web', '2023-01-26 08:23:34', '2023-01-26 08:23:34'),
(5, 'Print Order Manager', 'web', '2023-01-26 08:23:59', '2023-01-26 08:26:04'),
(6, 'Packages Manager', 'web', '2023-01-26 08:24:32', '2023-01-26 08:24:32'),
(7, 'Warehouse Manager', 'web', '2023-01-26 08:25:01', '2023-01-26 08:25:01'),
(8, 'Book Manager', 'web', '2023-01-26 08:25:28', '2023-01-26 08:25:28'),
(9, 'Route Manager', 'web', '2023-01-26 08:25:44', '2023-01-26 08:25:44');

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('FyupeAc4KNut7v8YQ4OHcvUH1mU69CMqku1OnjBh', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36 Edg/112.0.1722.34', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWDJScGtVV0ZDU2x1TDVBV3lxa3hvS3VUcTlSWUIyU2wyazVmTndYdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ib29rLXBhY2thZ2VzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjIxOiJwYXNzd29yZF9oYXNoX3NhbmN0dW0iO3M6NjA6IiQyeSQxMCRVVW9TS2FETWRzR1k0cWMvSlQwMWYuRGxpVi4ueTJGQmx5S2JoTUw2c3lvdWlNcmV0YU81QyI7fQ==', 1681201388);

INSERT INTO `subjects` (`id`, `name`, `code`, `description`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Amharic', 'C-01', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(2, 'English', 'C-02', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(3, 'Mathematics', 'C-03', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(4, 'Physics', 'C-04', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(5, 'Chemistry', 'C-05', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(6, 'Biology', 'C-06', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(7, 'History', 'C-07', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(8, 'Geography', 'C-08', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(9, 'Civics', 'C-09', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(10, 'PHE', 'C-10', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(11, 'Environmental Sience', 'C-11', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(12, 'Technical Drawing', 'C-12', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(13, 'Social Studies', 'C-13', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(14, 'Sign Language', 'C-14', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(15, 'ICT', 'C-15', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(16, 'General Business', 'C-16', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43');

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `is_firtst_time`, `phone`, `position`, `organization_id`, `remember_token`, `current_team_id`, `profile_photo_path`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@gmail.com', '2022-12-13 20:25:43', '$2y$10$UUoSKaDMdsGY4qc/JT01f.DliV..y2FBlyKbhML6syouiMretaO5C', NULL, NULL, NULL, '0', NULL, '0', NULL, 'nbc7ZfZrZLr9nmSlYk8IfDJQwQ0QDaEamOhZA8uqonHlPw2DkATgY66XO6MZ', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(2, 'Organization Manager', 'test@gmail.com', '2022-12-13 20:25:43', '$2y$10$NLyxCNTdjCkreS5m1UsFQ.fcW81Z0d5jikcxlKhtFoBbJydj0Kblm', NULL, NULL, NULL, '0', NULL, '0', NULL, 'knZhyXjowJ', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(3, 'Admin User', 'admin@gmail.com', '2022-12-13 20:25:43', '$2y$10$n89bWLEgL/EowVeBBs4z8uCg6dwEYsp7FEVOpqHUQcVllFSI80h.e', NULL, NULL, NULL, '0', NULL, '0', NULL, 'g43Oajj9aH', NULL, NULL, '2022-12-13 20:25:43', '2022-12-13 20:25:43'),
(4, 'Jason Duncan', 'jesek@mailinator.com', NULL, '$2y$10$JEkzhNknQ.cATv2oh41cX.yGts0pPf2kb2sUA3yJxiokJYGeSXD5C', NULL, NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2023-01-26 08:22:42', '2023-01-26 08:22:42'),
(5, 'Taylor Barr', 'zagez@mailinator.com', NULL, '$2y$10$5NuyY9sgqzCfsUX5kwtlNuKfpdvrnU.8fzktBRAkEf1bYD47XsfXa', NULL, NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2023-01-26 08:22:55', '2023-01-26 08:22:55'),
(6, 'Daniel Quinn', 'zonagexyt@mailinator.com', NULL, '$2y$10$zQftbD.ciDBWRhAM//YbgeYRqpaWidSsauJOTnItcT9y0H.M2zLWu', NULL, NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2023-01-26 08:26:27', '2023-01-26 08:26:27'),
(7, 'Doris Mann', 'kodyd@mailinator.com', NULL, '$2y$10$lC5LNtuCGSJZcw1PVBvzPepFyjZEA9.ApfiCwlDEDeb7cXF7cyu4q', NULL, NULL, NULL, '0', NULL, '0', NULL, NULL, NULL, NULL, '2023-01-26 08:26:35', '2023-01-26 08:26:35');

INSERT INTO `woredas` (`id`, `name`, `code`, `region_id`, `country_id`, `zone_id`, `created_at`, `updated_at`) VALUES
(1, 'woreda 6', NULL, 1, 1, 1, '2023-01-26 09:01:19', '2023-01-26 09:01:19');

INSERT INTO `zones` (`id`, `name`, `code`, `is_subcity`, `region_id`, `country_id`, `created_at`, `updated_at`) VALUES
(1, 'Bole', NULL, 1, 1, 1, '2023-01-26 08:59:53', '2023-01-26 08:59:53');



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;