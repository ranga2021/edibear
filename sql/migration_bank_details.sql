CREATE TABLE IF NOT EXISTS `edi_bank_details` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `account_number` VARCHAR(50)  NOT NULL DEFAULT '',
    `account_name`   VARCHAR(150) NOT NULL DEFAULT '',
    `bank_name`      VARCHAR(150) NOT NULL DEFAULT '',
    `branch_name`    VARCHAR(150) NOT NULL DEFAULT '',
    `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `edi_bank_details` (`account_number`, `account_name`, `bank_name`, `branch_name`)
SELECT '1000400531', 'EDIBEAR (PRIVATE) LIMITED', 'COMMERCIAL BANK', 'GAMPAHA BRANCH'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `edi_bank_details` LIMIT 1);
