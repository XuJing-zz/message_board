-- ============================================================
-- 留言板数据库初始化脚本
-- 用法：在 phpMyAdmin 的 SQL 页整段执行，或命令行 source 本文件
-- ============================================================

CREATE DATABASE IF NOT EXISTS `message_board`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `message_board`;

-- ------------------------------------------------------------
-- 用户表
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username`   VARCHAR(50)  NOT NULL                COMMENT '用户名',
  `password`   VARCHAR(255) NOT NULL                COMMENT '密码哈希（password_hash）',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- ------------------------------------------------------------
-- 留言表
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `message` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '留言ID',
  `user_id`    INT UNSIGNED NOT NULL                COMMENT '发留言的用户',
  `content`    TEXT         NOT NULL                COMMENT '留言内容',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_message_user` (`user_id`),
  KEY `idx_message_created` (`created_at`),
  CONSTRAINT `fk_message_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='留言表';

-- ------------------------------------------------------------
-- 点赞表（同一用户对同一条留言只能赞一次）
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `likes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `message_id` INT UNSIGNED NOT NULL,
  `user_id`    INT UNSIGNED NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_likes_msg_user` (`message_id`, `user_id`),
  CONSTRAINT `fk_likes_message`
    FOREIGN KEY (`message_id`) REFERENCES `message` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_likes_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='点赞表';