-- Kayamkulam Dars Contribution - SQL setup for cPanel MySQL
-- 1) Create a database from cPanel/MySQL Wizard
-- 2) Update the database name below if needed

CREATE DATABASE IF NOT EXISTS `kayamkulam_dars` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kayamkulam_dars`;

CREATE TABLE IF NOT EXISTS `donations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `organization` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_amount_created` (`amount` DESC, `created_at` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('fundraising_goal', '1000000.00')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('site_logo_url', '/1.jpeg')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- Default admin login: admin / admin123 (change immediately after first login)
INSERT INTO `admin_users` (`username`, `password`)
VALUES ('admin', '$2y$12$OIdriQO2zkHmQQ5j9SU.VeuZRa9zv2KyQPLQu4F1WYhz2XRSCNjEG')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);
