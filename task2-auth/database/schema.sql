-- CTLP 用户认证模块 - 数据库结构
-- 执行前请创建数据库，并配置 .env 中的 DB_* 变量

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 用户表
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL COMMENT '登录邮箱',
  `password` varchar(255) NOT NULL COMMENT '密码哈希 (bcrypt/argon2)',
  `reset_token` varchar(255) DEFAULT NULL COMMENT '找回密码令牌',
  `reset_expires` datetime DEFAULT NULL COMMENT '令牌过期时间',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_reset_token` (`reset_token`),
  KEY `idx_reset_expires` (`reset_expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

SET FOREIGN_KEY_CHECKS = 1;
