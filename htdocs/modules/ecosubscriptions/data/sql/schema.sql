UPDATE `_DB_PREFIX_customer` SET excludeFromRemind = excludeFromFollowUp;
ALTER TABLE `_DB_PREFIX_customer` DROP COLUMN excludeFromFollowUp;
