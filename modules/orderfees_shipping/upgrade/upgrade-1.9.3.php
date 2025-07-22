<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2020 motionSeed. All rights reserved.
 *  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_9_3($module)
{
    $module->upgradeVersion('1.9.3');
    
    $result = true;
    
    // Replace Cart.php override
    $result &= $module->upgradeOverride('Cart');
    
    // Register actionCartGetTotalWeight hook
    $module->registerHook('actionCartGetTotalWeight');

    return $result;
}
