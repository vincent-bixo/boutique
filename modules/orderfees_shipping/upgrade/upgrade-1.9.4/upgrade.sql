# Table : of_shipping_rule
ALTER TABLE `_DB_PREFIX_of_shipping_rule` ADD COLUMN `city_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

# Table : of_shipping_rule_product_rule
ALTER TABLE `_DB_PREFIX_of_shipping_rule_product_rule` MODIFY COLUMN `type` VARCHAR(32) NOT NULL;

# Table : of_shipping_rule_city
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_city` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`name`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_city_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_city_rule_group` (
    `id_city_rule_group` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_city_rule_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_city_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_city_rule` (
    `id_city_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_city_rule_group` INT(10) UNSIGNED NOT NULL,
    `type` VARCHAR(32) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`id_city_rule`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;