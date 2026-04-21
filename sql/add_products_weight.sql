-- Optional product fields for treasure details + admin. Run once; skip a line if you get "Duplicate column".

ALTER TABLE `products` ADD COLUMN `isbn` varchar(64) DEFAULT NULL;

ALTER TABLE `products` ADD COLUMN `weight` varchar(64) DEFAULT NULL;
