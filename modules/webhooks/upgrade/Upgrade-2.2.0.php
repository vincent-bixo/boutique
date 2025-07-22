<?php
/**
 * 2022 Wild Fortress, Lda
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author    Hélder Duarte <cossou@gmail.com>
 * @copyright 2022 Wild Fortress, Lda
 * @license   Proprietary and confidential
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/*
 * File: /upgrade/Upgrade-2.2.0.php
 */
function upgrade_module_2_2_0($module)
{
    return $module->registerHook('actionPasswordRenew');
}
