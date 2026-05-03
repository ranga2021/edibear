-- Run once. Adds optional gallery (JSON array of up to 3 filenames) and dynamic option rows (JSON).
-- If a column already exists (MySQL error 1060), skip that ALTER line.

ALTER TABLE `products`
  ADD COLUMN `gallery_images` TEXT NULL DEFAULT NULL COMMENT 'JSON: up to 3 filenames in img/products/' AFTER `image`;

ALTER TABLE `products`
  ADD COLUMN `options_extra` TEXT NULL DEFAULT NULL COMMENT 'JSON array of {k,v} from admin extra option rows' AFTER `description`;
