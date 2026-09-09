CREATE DATABASE IF NOT EXISTS `freelance_platform`
    DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `freelance_platform`;

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('client', 'freelancer', 'admin') NOT NULL DEFAULT 'client',
    `skills`     VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `services` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `freelancer_id` INT NOT NULL,
    `title`         VARCHAR(150) NOT NULL,
    `category`      VARCHAR(80) NOT NULL,
    `description`   TEXT NOT NULL,
    `price`         DECIMAL(10, 2) NOT NULL,
    `delivery_days` INT NOT NULL DEFAULT 3,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_services_user` FOREIGN KEY (`freelancer_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `bookings` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `service_id` INT NOT NULL,
    `client_id`  INT NOT NULL,
    `message`    TEXT,
    `engagement_type` ENUM('hourly', 'project') NOT NULL DEFAULT 'project',
    `budget`     DECIMAL(10, 2) DEFAULT NULL,
    `status`     ENUM('pending', 'accepted', 'declined', 'completed') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`)
        REFERENCES `services` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookings_client` FOREIGN KEY (`client_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
