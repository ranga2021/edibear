-- Run once on your MySQL database for cart weight + configurable shipping.
-- Adds numeric product weight and two rate tables (weight tiers + per-district fees).
-- If `weight_kg` already exists (error 1060), skip the ALTER line below.

ALTER TABLE `products`
  ADD COLUMN `weight_kg` DECIMAL(12,4) NULL DEFAULT NULL COMMENT 'Unit weight in kg for shipping' AFTER `weight`;

CREATE TABLE IF NOT EXISTS `edi_shipping_weight_tiers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `max_weight_kg` DECIMAL(12,4) DEFAULT NULL COMMENT 'Cart total kg up to and including this; NULL = unlimited (catch-all)',
  `fee_lkr` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort_order`),
  KEY `idx_max` (`max_weight_kg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `edi_shipping_districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `fee_lkr` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Added on top of weight-tier fee; match is case-insensitive on name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example tiers (edit in admin → Shipping rates): first matching max wins; last row should have NULL max for heavy carts.
INSERT INTO `edi_shipping_weight_tiers` (`max_weight_kg`, `fee_lkr`, `sort_order`) VALUES
  (1.0000, 300.00, 10),
  (5.0000, 450.00, 20),
  (NULL, 650.00, 90);

-- Example districts (names must match checkout selection for fee to apply)
INSERT IGNORE INTO `edi_shipping_districts` (`name`, `fee_lkr`) VALUES
  ('Colombo', 0.00),
  ('Gampaha', 50.00),
  ('Kandy', 100.00),
  ('Other / not listed', 0.00);
