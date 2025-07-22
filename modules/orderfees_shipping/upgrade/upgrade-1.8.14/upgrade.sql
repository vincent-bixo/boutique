# Table : of_shipping_rule
ALTER TABLE `_DB_PREFIX_of_shipping_rule` ADD COLUMN `state_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `_DB_PREFIX_of_shipping_rule` ADD COLUMN `gender_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

# Table : of_shipping_rule_state
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_state` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_state` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_state`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;

# Table : of_shipping_rule_gender
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_gender` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_gender` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_gender`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;