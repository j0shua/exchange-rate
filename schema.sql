CREATE TABLE IF NOT EXISTS `exchange_rate` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `currency` CHAR(3) NOT NULL,
    `conversion_rate` DECIMAL(12,6),
    `as_of` DATETIME,
    `deleted` tinyint(1) default 0,
    `last_modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB, DEFAULT CHARSET=utf8;


INSERT INTO `exchange_rate` (`id`, `currency`, `conversion_rate`, `as_of`) VALUES (null, 'EUR', '1.20324', NOW());
INSERT INTO `exchange_rate` (`id`, `currency`, `conversion_rate`, `as_of`) VALUES (null, 'JPY', '3.2321', NOW());
INSERT INTO `exchange_rate` (`id`, `currency`, `conversion_rate`, `as_of`) VALUES (null, 'CAN', '1.089', NOW());
