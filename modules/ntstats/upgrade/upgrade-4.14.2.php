<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_14_2($module)
{
    $module_id = Configuration::get('NTSTATS_ID');

    // Check module ID exists
    if (!$module_id || $module_id == '') {
        // Unique value for all shop that should not be remove or modify
        if (!Configuration::updateGlobalValue('NTSTATS_ID', $module->createModuleId())) {
            PrestaShopLogger::addLog('Module ID cannot be created.', 3);

            return false;
        }
    }

    $module->setOperation(NtStats::OP_UPGRADE);

    return $module;
}
