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

function upgrade_module_1_73_87($module)
{
	if (Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT')) {
		Configuration::updateValue(
			'WKWAREHOUSE_DEFAULT_NEW_PRODUCT',
			Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT')
		);
	}
    $module->uninstallTabs();
    $module->installTabs();
    return true;
}
