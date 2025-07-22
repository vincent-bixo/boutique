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

function upgrade_module_1_10_6($module)
{
    $result = true;
    
    Db::getInstance()->execute(
        'UPDATE '._DB_PREFIX_.'of_shipping_rule
            SET minimum_amount_restriction = IF(minimum_amount = 0, 0, minimum_amount_restriction + 1),
                maximum_amount_restriction = IF(maximum_amount = 0, 0, maximum_amount_restriction + 1)'
    );
    
    return $result;
}
