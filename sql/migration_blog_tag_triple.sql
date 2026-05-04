-- Widen blog tag so Language ||| Grade ||| Category can be stored (admin add-blog mock).
-- Skip if error 1060 / already applied.

ALTER TABLE `blog_details`
  MODIFY COLUMN `tag` VARCHAR(255) NOT NULL;
