-- 为找回密码限频增加字段（若已按 schema.sql 重建表可跳过）
-- 执行：mysql -u user -p dbname < add_reset_sent_at.sql
-- 若报错 column already exists，说明已加过，可忽略。

ALTER TABLE `users`
  ADD COLUMN `reset_sent_at` datetime DEFAULT NULL COMMENT '上次发送重置邮件时间（用于限频）' AFTER `reset_expires`;
