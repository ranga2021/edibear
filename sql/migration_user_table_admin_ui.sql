-- Optional: admin list UI (role, city, status toggle, profile pic).
-- Run once; skip individual lines if column already exists.

ALTER TABLE `user_table`
  ADD COLUMN `admin_role` varchar(32) NOT NULL DEFAULT 'administrator' AFTER `mobile_number`;

ALTER TABLE `user_table`
  ADD COLUMN `city_country` varchar(100) NOT NULL DEFAULT '' AFTER `admin_role`;

ALTER TABLE `user_table`
  ADD COLUMN `admin_status` tinyint(1) NOT NULL DEFAULT 1 AFTER `city_country`;

ALTER TABLE `user_table`
  ADD COLUMN `profile_pic` varchar(255) NOT NULL DEFAULT 'default.jpg' AFTER `admin_status`;
