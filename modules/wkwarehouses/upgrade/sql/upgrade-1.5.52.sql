ALTER TABLE `PREFIX_stock` CHANGE `physical_quantity` `physical_quantity` INT(11) NULL DEFAULT NULL;
ALTER TABLE `PREFIX_stock` CHANGE `usable_quantity` `usable_quantity` INT(11) NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `PREFIX_warehouse_lang` (
  `id_warehouse` int(11) unsigned NOT NULL,
  `id_lang` int(11) unsigned NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id_warehouse`,`id_lang`)
) ENGINE=_SQLENGINE_ DEFAULT CHARSET=utf8;
