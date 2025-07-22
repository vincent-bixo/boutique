<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_73_83()
{
	Configuration::updateValue('WKWAREHOUSE_WAY_FIX_QUANTITIES', 'alignQtiesToPrestashop');
	Configuration::updateValue('WKWAREHOUSE_SECURE_KEY', md5(_COOKIE_KEY_.time()));
	Configuration::updateValue('WKWAREHOUSE_PAGINATION_USE', 0);
	Configuration::updateValue('WKWAREHOUSE_PAGINATION_LIMIT', 20);
	Configuration::updateValue('WKWAREHOUSE_PAGINATION_NUMBER_LINKS', 10);

    return true;
}
