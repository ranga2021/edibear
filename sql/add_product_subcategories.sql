-- Run once against your Edibear database (e.g. traveylo_edibear).
-- Shop subcategories tied to product_categories (no main_category required).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `product_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_category_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_psc_product_category` (`product_category_id`),
  CONSTRAINT `fk_psc_product_categories`
    FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If this errors with "Duplicate column", the column already exists — skip the next two statements.
ALTER TABLE `products`
  ADD COLUMN `product_subcategory_id` int(11) DEFAULT NULL AFTER `sub_category_id`;

ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_product_subcategory`
    FOREIGN KEY (`product_subcategory_id`) REFERENCES `product_subcategories` (`id`) ON DELETE SET NULL;
