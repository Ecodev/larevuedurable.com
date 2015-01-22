/*
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

/* Add id_shop field */
ALTER TABLE `_DB_PREFIX_history`
    ADD `id_shop` int(11) NOT NULL AFTER `id_pp_history`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id_pp_history`, `id_shop`, `id_customer`);

/* Add id_shop field */
ALTER TABLE `_DB_PREFIX_product`
    ADD `id_shop` int(11) NOT NULL AFTER `id_pp_product`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id_pp_product`, `id_shop`, `id_product`);

/* Add id_shop field */
ALTER TABLE `_DB_PREFIX_state`
    ADD `id_shop` int(11) NOT NULL AFTER `id_pp_state`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id_pp_state`, `id_shop`, `id_customer`);

/* END */