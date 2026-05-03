-- Optional: extra images / YouTube-style videos for a single blog post (blog.php + admin add-blog).
-- Run once on your database if the table is not present.

CREATE TABLE IF NOT EXISTS `blog_extra_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `media_type` varchar(20) NOT NULL DEFAULT 'image',
  `path` varchar(512) NOT NULL DEFAULT '',
  `caption` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_blog_extra_media_blog` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
