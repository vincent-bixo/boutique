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

function upgrade_module_1_56_10($module)
{
    // Install new parameters
    Configuration::updateValue(
        'WKWAREHOUSE_STOCKPRIORITY_INC',
        Configuration::get('WKWAREHOUSE_USE_STOCK_PRIORITY')
    );
    Configuration::updateValue('WKWAREHOUSE_PRIORITY_DECREASE', '');
    Configuration::updateValue('WKWAREHOUSE_STOCKPRIORITY_DEC', 1);
    Configuration::updateValue('WKWAREHOUSE_WAREHOUSEINFOS_POSITION', 'afterCart');
    Configuration::updateValue('WKWAREHOUSE_DISPLAY_DELIVERIES_TIME', 0);
    Configuration::updateValue('WKWAREHOUSE_ALLOWSET_WAREHOUSE', 0);
    Configuration::updateValue('WKWAREHOUSE_DISPLAY_SELECTED_WAREHOUSE', 0);
    Configuration::updateValue('WKWAREHOUSE_DISPLAY_SELECTED_LOCATION', 0);
    Configuration::updateValue('WKWAREHOUSE_DISPLAY_WAREHOUSE_NAME', 1);
    Configuration::updateValue('WKWAREHOUSE_DISPLAY_SELECTED_STOCK', 1);
    Configuration::updateValue('WKWAREHOUSE_DISPLAY_DELIVERYTIME', 0);
    Configuration::updateValue('WKWAREHOUSE_ENABLE_INCART', 0);
    Configuration::updateValue('WKWAREHOUSE_WAREHOUSES_INCART', 0);
    Configuration::updateValue('WKWAREHOUSE_QUANTITIES_INCART', 0);
    Configuration::updateValue('WKWAREHOUSE_LOCATIONS_INCART', 0);
    Configuration::updateValue('WKWAREHOUSE_DELIVERYTIMES_INCART', 0);
    Configuration::updateValue('WKWAREHOUSE_POSITION_INCART', 'belowProductName');
    Configuration::updateValue('WKWAREHOUSE_ALLOW_MULTICARRIER_CART', 1);
    Configuration::updateValue('WKWAREHOUSE_ALLOW_MULTIWH_CART', 1);
    Configuration::updateValue('WKWAREHOUSE_STOCKPRIORITY_DEC', 1);
    Configuration::updateValue('WKWAREHOUSE_ENABLE_FONTAWESOME', 1);
    Configuration::deleteByName('WKWAREHOUSE_NO_SPLIT_ORDERS');
    Configuration::updateValue('WKWAREHOUSE_DISPLAY_COUNTRIES', 1);
    Configuration::updateValue('WKWAREHOUSE_DISPLAY_COUNTRY', 0);
    Configuration::updateValue('WKWAREHOUSE_COUNTRIES_INCART', 0);

    // Install new hooks
    $module->registerHook('displayProductExtraContent');
    $module->registerHook('actionCartSave');
    $module->registerHook('actionSetInvoice');
    $module->registerHook('actionObjectDeleteAfter');
    $module->registerHook('actionCartUpdateQuantityBefore');
    $module->registerHook('actionGetProductPropertiesAfter');
    $module->registerHook('actionObjectProductInCartDeleteAfter');
    $module->fixActionCartUpdateQuantityBeforeHook();

    // Upgrade mysql
    $module->loadSQLFile(dirname(__FILE__).'/sql/upgrade-cart-table.sql');

    // Remove useless override
    $payment_override = _PS_MODULE_DIR_.$module->name.'/override/classes/PaymentModule.php';
    if (file_exists($payment_override)) {
        @unlink($payment_override);
    }

    // Install / Upgrade overrides
    $module->uninstallOverrides();
    try {
        $module->installOverrides();
    } catch (Exception $e) {
    }

    return true;
}
