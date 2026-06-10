-- Voucher / coupon system
-- Run this migration on your database to create the vouchers table.

CREATE TABLE IF NOT EXISTS `edi_vouchers` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code`            VARCHAR(50)  NOT NULL,
  `description`     VARCHAR(255) NOT NULL DEFAULT '',
  `discount_type`   ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `min_order_total`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_uses`        INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
  `used_count`      INT UNSIGNED NOT NULL DEFAULT 0,
  `status`          TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive',
  `starts_at`       DATE DEFAULT NULL,
  `expires_at`      DATE DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_voucher_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add voucher tracking columns to orders table
-- These use a procedure to safely skip if columns already exist
DELIMITER $$
DROP PROCEDURE IF EXISTS _edi_add_voucher_cols$$
CREATE PROCEDURE _edi_add_voucher_cols()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'voucher_code') THEN
    ALTER TABLE `orders` ADD COLUMN `voucher_code` VARCHAR(50) DEFAULT NULL;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'voucher_discount') THEN
    ALTER TABLE `orders` ADD COLUMN `voucher_discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
  END IF;
END$$
DELIMITER ;
CALL _edi_add_voucher_cols();
DROP PROCEDURE IF EXISTS _edi_add_voucher_cols;
