# Table : of_shipping_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `id_customer` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `date_from` DATETIME NULL,
  `date_to` DATETIME NULL,
  `time_from` TIME NULL,
  `time_to` TIME NULL,
  `priority` INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `minimum_amount` DECIMAL(20, 10) NOT NULL DEFAULT '0.00',
  `minimum_amount_tax` TINYINT(1) NOT NULL DEFAULT '0',
  `minimum_amount_currency` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `minimum_amount_restriction` INT(10) NOT NULL DEFAULT '0',
  `maximum_amount` DECIMAL(20, 10) NOT NULL DEFAULT '0.00',
  `maximum_amount_tax` TINYINT(1) NOT NULL DEFAULT '0',
  `maximum_amount_currency` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `maximum_amount_restriction` INT(10) NOT NULL DEFAULT '0',
  `country_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `zone_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `state_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `city_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `carrier_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `group_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `of_shipping_rule_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `product_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `shop_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `dimension_restriction` TINYINT(1) NOT NULL DEFAULT '0',
  `zipcode_restriction` TINYINT(1) NOT NULL DEFAULT '0',
  `package_restriction` TINYINT(1) NOT NULL DEFAULT '0',
  `gender_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `nb_supplier_min` INT(10) UNSIGNED NULL,
  `nb_supplier_max` INT(10) UNSIGNED NULL,
  `type` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `percent` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
  `amount` DECIMAL(20, 10) NOT NULL DEFAULT '0.00',
  `formula` VARCHAR(255) NULL DEFAULT '',
  `currency` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `tax_rules_group` INT(10) NOT NULL DEFAULT '0',
  `product` INT(10) NOT NULL DEFAULT '0',
  `quantity_per_product` TINYINT(1) NOT NULL DEFAULT '0',
  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_carrier
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_carrier` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_carrier` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_carrier`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_combination
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_combination` (
  `id_of_shipping_rule_1` INT(10) UNSIGNED NOT NULL,
  `id_of_shipping_rule_2` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule_1`,`id_of_shipping_rule_2`),
  KEY `id_of_shipping_rule_1` (`id_of_shipping_rule_1`),
  KEY `id_of_shipping_rule_2` (`id_of_shipping_rule_2`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_country
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_country` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_country` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_country`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_zone
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_zone` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_zone` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_zone`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_state
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_state` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_state` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_state`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_city
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_city` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`name`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_group` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_group` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_product_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_product_rule` (
  `id_product_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_product_rule_group` INT(10) UNSIGNED NOT NULL,
  `type` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`id_product_rule`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_product_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_product_rule_group` (
  `id_product_rule_group` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `quantity` INT(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_product_rule_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_product_rule_value
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_product_rule_value` (
  `id_product_rule` INT(10) UNSIGNED NOT NULL,
  `id_item` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_product_rule`,`id_item`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_shop
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_shop` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_shop` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_shop`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_dimension_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_dimension_rule_group` (
    `id_dimension_rule_group` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
    `base` VARCHAR(32) NOT NULL,
    PRIMARY KEY (`id_dimension_rule_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_dimension_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_dimension_rule` (
    `id_dimension_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_dimension_rule_group` INT(10) UNSIGNED NOT NULL,
    `type` VARCHAR(32) NOT NULL,
    `operator` CHAR(5) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`id_dimension_rule`)
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

# Table : of_shipping_rule_zipcode_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_zipcode_rule_group` (
    `id_zipcode_rule_group` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_zipcode_rule_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_zipcode_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_zipcode_rule` (
    `id_zipcode_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_zipcode_rule_group` INT(10) UNSIGNED NOT NULL,
    `type` VARCHAR(32) NOT NULL,
    `operator` CHAR(5) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`id_zipcode_rule`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_package_rule_group
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_package_rule_group` (
    `id_package_rule_group` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
    `unit` VARCHAR(32) NOT NULL,
    `unit_weight` VARCHAR(32) NOT NULL,
    `ratio` VARCHAR(32) NOT NULL,
    PRIMARY KEY (`id_package_rule_group`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_package_rule
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_package_rule` (
    `id_package_rule` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_package_rule_group` INT(10) UNSIGNED NOT NULL,
    `range_start` DECIMAL(10,2) NOT NULL,
    `range_end` DECIMAL(10,2) NOT NULL,
    `round` DECIMAL(10,2) NOT NULL DEFAULT '1',
    `divider` DECIMAL(10,2) NOT NULL DEFAULT '1',
    `currency` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `tax` TINYINT(1) NOT NULL DEFAULT '0',
    `value` DECIMAL(20, 10) NOT NULL DEFAULT '0.00',
    PRIMARY KEY (`id_package_rule`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_gender
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_gender` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_gender` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_gender`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;