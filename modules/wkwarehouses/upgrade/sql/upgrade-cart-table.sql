ALTER TABLE `PREFIX_warehouse_lang` ADD `delivery_time` VARCHAR(255) NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `PREFIX_warehouse_cart_product` (
  `id_cart` int(10) unsigned NOT NULL,
  `id_product` int(10) unsigned NOT NULL,
  `id_product_attribute` int(10) unsigned NOT NULL DEFAULT '0',
  `id_warehouse` int(10) unsigned NOT NULL,
  `date_add` datetime NOT NULL,
  PRIMARY KEY (`id_cart`,`id_product`,`id_product_attribute`),
  KEY `id_product_attribute` (`id_product_attribute`),
  KEY `id_cart_order` (`id_cart`,`id_product`,`id_product_attribute`) USING BTREE
) ENGINE=_SQLENGINE_ DEFAULT CHARSET=utf8;
