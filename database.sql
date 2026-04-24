-- ============================================================
--  FASHIONISTA UNIVERSITY PLATFORM — Database Schema
--  Compatible: MySQL 5.7+ / MariaDB 10.3+
--  Import via phpMyAdmin or: mysql -u root -p < database.sql
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- -----------------------------------------------
-- Create & select database
-- -----------------------------------------------
CREATE DATABASE IF NOT EXISTS `fashionista_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `fashionista_db`;

-- -----------------------------------------------
-- Table: users
-- -----------------------------------------------
CREATE TABLE `users` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(50)  NOT NULL UNIQUE,
  `full_name`    VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) NOT NULL UNIQUE,
  `password`     VARCHAR(255) NOT NULL,
  `bio`          TEXT,
  `avatar`       VARCHAR(255) DEFAULT 'default_avatar.png',
  `role`         ENUM('student','admin') DEFAULT 'student',
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: posts
-- -----------------------------------------------
CREATE TABLE `posts` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)      NOT NULL,
  `title`        VARCHAR(200) NOT NULL,
  `description`  TEXT,
  `image`        VARCHAR(255) NOT NULL,
  `category`     ENUM('robe','costume','accessoire','streetwear','haute_couture','autre') DEFAULT 'autre',
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_post_user` (`user_id`),
  CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: comments
-- -----------------------------------------------
CREATE TABLE `comments` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `post_id`      INT(11)      NOT NULL,
  `user_id`      INT(11)      NOT NULL,
  `content`      TEXT         NOT NULL,
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comment_post` (`post_id`),
  KEY `fk_comment_user` (`user_id`),
  CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: likes
-- -----------------------------------------------
CREATE TABLE `likes` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `post_id`      INT(11)      NOT NULL,
  `user_id`      INT(11)      NOT NULL,
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`post_id`, `user_id`),
  KEY `fk_like_user` (`user_id`),
  CONSTRAINT `fk_like_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_like_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: events
-- -----------------------------------------------
CREATE TABLE `events` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(200) NOT NULL,
  `description`  TEXT,
  `image`        VARCHAR(255) DEFAULT 'default_event.png',
  `location`     VARCHAR(255),
  `event_date`   DATETIME     NOT NULL,
  `type`         ENUM('défilé','concours','atelier','exposition','autre') DEFAULT 'autre',
  `max_participants` INT(11)  DEFAULT NULL,
  `created_by`   INT(11)      NOT NULL,
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_event_creator` (`created_by`),
  CONSTRAINT `fk_event_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: event_participants
-- -----------------------------------------------
CREATE TABLE `event_participants` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `event_id`     INT(11)      NOT NULL,
  `user_id`      INT(11)      NOT NULL,
  `registered_at` DATETIME    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_participation` (`event_id`, `user_id`),
  KEY `fk_ep_user` (`user_id`),
  CONSTRAINT `fk_ep_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ep_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Table: notifications
-- -----------------------------------------------
CREATE TABLE `notifications` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)      NOT NULL,   -- recipient
  `from_user_id` INT(11)      NOT NULL,   -- sender
  `post_id`      INT(11)      DEFAULT NULL,
  `type`         ENUM('like','comment','event') NOT NULL,
  `message`      VARCHAR(255) NOT NULL,
  `is_read`      TINYINT(1)   DEFAULT 0,
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notif_user` (`user_id`),
  KEY `fk_notif_from` (`from_user_id`),
  CONSTRAINT `fk_notif_user`     FOREIGN KEY (`user_id`)      REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_from`     FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notif_post`     FOREIGN KEY (`post_id`)      REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- Demo data — Admin user (password: Admin@1234)
-- -----------------------------------------------
-- -----------------------------------------------
-- Table: password_resets
-- -----------------------------------------------
CREATE TABLE `password_resets` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11)      NOT NULL,
  `token_hash`   CHAR(64)     NOT NULL,
  `expires_at`   DATETIME     NOT NULL,
  `used_at`      DATETIME     DEFAULT NULL,
  `created_at`   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pr_user` (`user_id`),
  KEY `idx_pr_token` (`token_hash`),
  CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`username`, `full_name`, `email`, `password`, `bio`, `role`) VALUES
('admin', 'Administrateur', 'admin@fashionista.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Responsable de la plateforme Fashionista University.', 'admin'),
('sofia_design', 'Sofia Benali', 'sofia@fashionista.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Étudiante en 3ème année, passionnée de haute couture et de streetwear.', 'student'),
('lucas_mode', 'Lucas Fontaine', 'lucas@fashionista.edu', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Créateur de costumes et accessoires, inspiré par la culture afro-urbaine.', 'student');

-- Demo posts
INSERT INTO `posts` (`user_id`, `title`, `description`, `image`, `category`) VALUES
(2, 'Robe de soirée plissée', 'Création réalisée en soie naturelle avec des plissés inspirés du Japon traditionnel.', 'demo_post1.jpg', 'robe'),
(3, 'Veste streetwear oversize', 'Veste réalisée à partir de matières recyclées, collection capsule urbaine.', 'demo_post2.jpg', 'streetwear'),
(2, 'Accessoire bijou col', 'Col ornementé réalisé en fils dorés et perles de verre soufflé.', 'demo_post3.jpg', 'accessoire');

-- Demo events
INSERT INTO `events` (`title`, `description`, `location`, `event_date`, `type`, `max_participants`, `created_by`) VALUES
('Grand Défilé de Printemps 2025', 'Le défilé annuel de l''université réunissant les meilleures créations des étudiants de toutes les années.', 'Amphithéâtre Principal — Campus Central', '2025-04-20 18:00:00', 'défilé', 200, 1),
('Concours Jeune Créateur', 'Concours ouvert à tous les étudiants pour présenter une pièce originale. Prix à gagner !', 'Salle des Arts — Bâtiment B', '2025-05-10 14:00:00', 'concours', 50, 1),
('Atelier Couture Durable', 'Atelier pratique pour apprendre les techniques de mode éco-responsable et upcycling.', 'Atelier Textile — Bâtiment C', '2025-04-30 10:00:00', 'atelier', 20, 1);

COMMIT;
