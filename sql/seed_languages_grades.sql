-- Languages + grades for homepage explorer and product admin selects.
-- Run once (or re-run): updates titles when IDs already exist.

SET NAMES utf8mb4;

INSERT INTO `languages` (`id`, `title`) VALUES
  (1, 'Sinhala'),
  (2, 'English'),
  (3, 'Tamil')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `grades` (`id`, `title`) VALUES
  (1, 'Pre School'),
  (2, 'Grade 1'),
  (3, 'Grade 2'),
  (4, 'Grade 3'),
  (5, 'Grade 4'),
  (6, 'Grade 5'),
  (7, 'Grade 6'),
  (8, 'Grade 7'),
  (9, 'Grade 8'),
  (10, 'Grade 9'),
  (11, 'Grade 10'),
  (12, 'Grade 11'),
  (13, 'Grade 12'),
  (14, 'Grade 13')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);
