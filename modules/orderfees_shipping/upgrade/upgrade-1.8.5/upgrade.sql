# Table : of_shipping_rule
ALTER TABLE `_DB_PREFIX_of_shipping_rule` MODIFY COLUMN `formula` VARCHAR(255) NULL DEFAULT '';
ALTER TABLE `_DB_PREFIX_of_shipping_rule` MODIFY COLUMN `date_from` DATETIME NULL;
ALTER TABLE `_DB_PREFIX_of_shipping_rule` MODIFY COLUMN `date_to` DATETIME NULL;

ALTER TABLE `_DB_PREFIX_of_shipping_rule` DROP INDEX id_customer;
ALTER TABLE `_DB_PREFIX_of_shipping_rule` DROP INDEX group_restriction;