-- Home section background images (explore strip, testimonials, footer) use `carousel` rows
-- with types explore_bg, testimonial_bg, footer_bg and files under img/home/.
-- Widen `type` so these keys fit (was varchar(5), only enough for img/video/main).

ALTER TABLE `carousel` MODIFY `type` VARCHAR(32) NOT NULL COMMENT 'img, video, main, explore_bg, testimonial_bg, footer_bg, ...';
