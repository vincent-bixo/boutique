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

function upgrade_module_1_84_34($module)
{
    $module->registerHook('displayProductPriceBlock');

	Configuration::updateValue('WKWAREHOUSE_PRODUCT_NAME_SHIPMENT_PART', 0);
	Configuration::updateValue('WKWAREHOUSE_WH_NAME_SHIPMENT_PART', 1);
	Configuration::updateValue('WKWAREHOUSE_PRODUCT_NOT_ASM_GET_BEST_CARRIERS', 1);
	Configuration::updateValue('WKWAREHOUSE_ALLOW_MULTICARRIER_CHOICE', 0);
	Configuration::updateValue('WKWAREHOUSE_MODE_MULTICARRIER_CHOICE', 'carriers-combinations');

    return true;
}
