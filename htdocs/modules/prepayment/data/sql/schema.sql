/*
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

/* Create table history */
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_history` (
    `id_pp_history` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(11) NOT NULL,
    `id_customer` int(10) unsigned NOT NULL,
    `id_currency` int(10) unsigned NOT NULL,
    `id_order` int(10) unsigned NOT NULL DEFAULT '0',
    `date` datetime NOT NULL,
    `amount` decimal(20,6) DEFAULT '0.000000',
    PRIMARY KEY (`id_pp_history`, `id_shop`, `id_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Create table product */
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_product` (
    `id_pp_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(11) NOT NULL,
    `id_product` int(10) unsigned NOT NULL,
    `reduction` decimal(20,6) NOT NULL,
    `reduction_type` enum('amount', 'percentage') NOT NULL DEFAULT 'amount',
    PRIMARY KEY (`id_pp_product`, `id_shop`, `id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* Create table state */
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_state` (
    `id_pp_state` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_shop` int(11) NOT NULL,
    `id_customer` int(10) unsigned NOT NULL,
    `id_currency` int(10) unsigned NOT NULL,
    `amount` decimal(20,6) DEFAULT '0.000000',
    PRIMARY KEY (`id_pp_state`, `id_shop`, `id_customer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* END */