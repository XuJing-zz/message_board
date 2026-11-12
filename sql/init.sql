-- ============================================================
-- 内容后台（文章 + 点赞 + 留言）数据库初始化
-- 用法：phpMyAdmin → SQL → 整段执行
-- ============================================================

CREATE DATABASE IF NOT EXISTS `message_board`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `message_board`;

-- 用户表（role: admin 管理员 / user 普通用户）
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)  NOT NULL,
  `password`   VARCHAR(255) NOT NULL COMMENT 'password_hash 生成',
  `role`       VARCHAR(20)  NOT NULL DEFAULT 'user' COMMENT 'admin / user',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 文章表
CREATE TABLE IF NOT EXISTS `articles` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `author_id`  INT UNSIGNED NOT NULL,
  `title`      VARCHAR(200) NOT NULL,
  `cover`      VARCHAR(255) NOT NULL DEFAULT '',
  `content`    MEDIUMTEXT   NOT NULL,
  `status`     ENUM('published','draft') NOT NULL DEFAULT 'draft',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_articles_status` (`status`),
  KEY `idx_articles_author` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 文章点赞表（同一用户对同一篇只能赞一次）
CREATE TABLE IF NOT EXISTS `article_likes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_article_user` (`article_id`, `user_id`),
  KEY `idx_al_article` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 文章留言表
CREATE TABLE IF NOT EXISTS `article_comments` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `content`    TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ac_article` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 默认管理员：admin / 123456
INSERT INTO `users` (`username`, `password`, `role`)
SELECT 'admin', '$2y$10$uS6N36wx90WxElagDM/4t.K5FOf93uJ/M9S8LvVg60v/jsceiLvp2', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `username` = 'admin');