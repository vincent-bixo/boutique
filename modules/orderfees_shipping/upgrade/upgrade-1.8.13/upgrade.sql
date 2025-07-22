# Table : of_shipping_rule
ALTER TABLE `_DB_PREFIX_of_shipping_rule` CHANGE COLUMN `minimum_amount_shipping` `minimum_amount_restriction` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_of_shipping_rule` CHANGE COLUMN `maximum_amount_shipping` `maximum_amount_restriction` INT(10) NOT NULL DEFAULT '0';