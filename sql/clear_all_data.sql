-- Clear ALL row data; keep tables, columns, indexes, and AUTO_INCREMENT reset.
-- Database name is NOT included here — run against your DB (e.g. edibear).
--
-- After this, re-create at least one admin user (see sql/add_test_admin_user.sql).

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `cart`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `product_subcategories`;
TRUNCATE TABLE `product_categories`;
TRUNCATE TABLE `ad1_descriptions`;
TRUNCATE TABLE `ad1_details`;
TRUNCATE TABLE `ad2_descriptions`;
TRUNCATE TABLE `ad2_details`;
TRUNCATE TABLE `blog_descriptions`;
TRUNCATE TABLE `blog_details`;
TRUNCATE TABLE `books_descriptions`;
TRUNCATE TABLE `books_details`;
TRUNCATE TABLE `homework_descriptions`;
TRUNCATE TABLE `homework_details`;
TRUNCATE TABLE `pdf_descriptions`;
TRUNCATE TABLE `pdf_details`;
TRUNCATE TABLE `carousel`;
TRUNCATE TABLE `grades`;
TRUNCATE TABLE `languages`;
TRUNCATE TABLE `main_category`;
TRUNCATE TABLE `sub_category`;
TRUNCATE TABLE `newsletter`;
TRUNCATE TABLE `testimonials`;
TRUNCATE TABLE `testimonials_images`;
TRUNCATE TABLE `tourists`;
TRUNCATE TABLE `tour_day_details`;
TRUNCATE TABLE `tour_sub_images`;
TRUNCATE TABLE `tour_details`;
TRUNCATE TABLE `braveheart_winners`;
TRUNCATE TABLE `braveheart_events`;
TRUNCATE TABLE `braveheart_categories`;
TRUNCATE TABLE `user_table`;

SET FOREIGN_KEY_CHECKS = 1;
