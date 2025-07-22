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

function upgrade_module_4_15_2($module)
{
    $a_return_valid_states = Db::getInstance()->executeS('
        SELECT `id_nts_config`, `return_valid_states`
        FROM `' . _DB_PREFIX_ . 'nts_config`
    ');

    $result = true;

    foreach ($a_return_valid_states as $a_return_valid_state) {
        if (Validate::isSerializedArray($a_return_valid_state)) {
            $update_table = Db::getInstance()->execute('
                UPDATE `' . _DB_PREFIX_ . 'nts_config` SET `return_valid_states` = "' . pSQL(json_encode(unserialize($a_return_valid_state))) . '";
            ');

            if (!$update_table) {
                $result = false;
                PrestaShopLogger::addLog('Could not update return_valid_states in config table. ' . Db::getInstance()->getMsgError(), 3);
            }
        }
    }

    if (!$result) {
        return false;
    }

    return $module;
}
