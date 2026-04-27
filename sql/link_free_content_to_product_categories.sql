-- One-time: link free resources (coloring / books / homework) to product_categories
-- and product_subcategories. If a column already exists, remove that line and re-run.
-- Requires tables product_categories and product_subcategories (see sql/add_product_subcategories.sql).

ALTER TABLE `pdf_details`
  ADD COLUMN `product_category_id` int(11) DEFAULT NULL COMMENT 'shop category' AFTER `sub_cat_id`,
  ADD COLUMN `product_subcategory_id` int(11) DEFAULT NULL COMMENT 'shop subcategory' AFTER `product_category_id`;

ALTER TABLE `books_details`
  ADD COLUMN `product_category_id` int(11) DEFAULT NULL COMMENT 'shop category' AFTER `sub_cat_id`,
  ADD COLUMN `product_subcategory_id` int(11) DEFAULT NULL COMMENT 'shop subcategory' AFTER `product_category_id`;

ALTER TABLE `homework_details`
  ADD COLUMN `product_category_id` int(11) DEFAULT NULL COMMENT 'shop category' AFTER `sub_cat_id`,
  ADD COLUMN `product_subcategory_id` int(11) DEFAULT NULL COMMENT 'shop subcategory' AFTER `product_category_id`;

-- Optional foreign keys (comment out if your server rejects them):
-- ALTER TABLE `pdf_details` ADD CONSTRAINT `fk_pdf_product_category` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `pdf_details` ADD CONSTRAINT `fk_pdf_product_subcategory` FOREIGN KEY (`product_subcategory_id`) REFERENCES `product_subcategories` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `books_details` ADD CONSTRAINT `fk_books_product_category` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `books_details` ADD CONSTRAINT `fk_books_product_subcategory` FOREIGN KEY (`product_subcategory_id`) REFERENCES `product_subcategories` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `homework_details` ADD CONSTRAINT `fk_homework_product_category` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL;
-- ALTER TABLE `homework_details` ADD CONSTRAINT `fk_homework_product_subcategory` FOREIGN KEY (`product_subcategory_id`) REFERENCES `product_subcategories` (`id`) ON DELETE SET NULL;
