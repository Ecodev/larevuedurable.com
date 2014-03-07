/*
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

/* Add reduction fields to table pp_product */
ALTER TABLE `_DB_PREFIX_product`
    ADD `reduction` decimal(20,6) NOT NULL,
    ADD `reduction_type` enum('amount', 'percentage') NOT NULL DEFAULT 'amount';

/* END */