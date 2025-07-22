# Table : of_shipping_rule
ALTER TABLE `_DB_PREFIX_of_shipping_rule` ADD COLUMN `zone_restriction` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

# Table : of_shipping_rule_zone
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_of_shipping_rule_zone` (
  `id_of_shipping_rule` INT(10) UNSIGNED NOT NULL,
  `id_zone` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_of_shipping_rule`,`id_zone`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=UTF8;