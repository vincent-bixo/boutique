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

class NtsConfigPaymentMethod extends ObjectModel
{
    /** @var int id_nts_config */
    public $id_nts_config;

    /** @var string payment_method */
    public $payment_method;

    /** @var string display_name */
    public $display_name;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'nts_config_payment_method',
        'primary' => 'id_nts_config_payment_method',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'id_nts_config' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'payment_method' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'display_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    /**
     * Get the config payment informations for the given config
     *
     * @param int $id_nts_config The config you want the payment infos for
     *
     * @return array The config payment infos
     */
    public static function getConfigByIdConfig($id_nts_config)
    {
        $o_config = new NtsConfig($id_nts_config);
        $shop_context = Shop::getContext();
        $req_shop = '';

        if ($o_config->id_shop === null || !is_int($o_config->id_shop)) {
            $o_config->id_shop = Context::getContext()->shop->id;
        }

        if ($o_config->id_shop_group === null || !is_int($o_config->id_shop_group)) {
            $o_config->id_shop_group = Context::getContext()->shop->id_shop_group;
        }

        if ($shop_context == Shop::CONTEXT_SHOP) {
            $req_shop = ' AND o.`id_shop` = ' . (int) $o_config->id_shop;
        } elseif ($shop_context == Shop::CONTEXT_GROUP) {
            $req_shop = ' AND o.`id_shop_group` = ' . (int) $o_config->id_shop_group;
        }

        $payment_infos = Db::getInstance()->executeS('
            SELECT op.`payment_method`, IFNULL(ntcpm.`display_name`, "") AS display_name
            FROM `' . _DB_PREFIX_ . 'order_payment` op
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`reference` = op.`order_reference`
            LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm ON ntcpm.`payment_method` = op.`payment_method` AND ntcpm.`id_nts_config` = ' . (int) $id_nts_config . '
            WHERE 1 = 1
            ' . $req_shop . '
            GROUP BY op.`payment_method`
            ORDER BY op.`payment_method`, display_name
        ');

        if (!is_array($payment_infos)) {
            return [];
        }

        return $payment_infos;
    }

    /**
     * Get the payment informations for the given config
     *
     * @param int $id_nts_config The config you want the payment infos for
     *
     * @return array The payment infos
     */
    public static function getPaymentMethodByIdConfig($id_nts_config)
    {
        $o_config = new NtsConfig($id_nts_config);
        $shop_context = Shop::getContext();
        $req_shop = '';

        if ($shop_context == Shop::CONTEXT_SHOP) {
            $req_shop = ' AND o.`id_shop` = ' . (int) $o_config->id_shop;
        } elseif ($shop_context == Shop::CONTEXT_GROUP) {
            $req_shop = ' AND o.`id_shop_group` = ' . (int) $o_config->id_shop_group;
        }

        $payment_infos = Db::getInstance()->executeS('
            SELECT ntcpm.`display_name` AS payment_method
            FROM `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm
            WHERE ntcpm.`id_nts_config` = ' . (int) $id_nts_config . '

            UNION

            SELECT op.`payment_method` AS payment_method
            FROM `' . _DB_PREFIX_ . 'order_payment` op
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`reference` = op.`order_reference`
            WHERE op.`payment_method` NOT IN (
                SELECT ntcpm2.`payment_method`
                FROM `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm2
            )
            ' . $req_shop . '
            GROUP BY payment_method
            ORDER BY payment_method
        ');

        if (!is_array($payment_infos)) {
            return [];
        }

        return $payment_infos;
    }

    /**
     * Get informations for the given payment method
     *
     * @param string $payment_method The payment method you want the infos of
     * @param int $id_nts_config The config you want the infos for
     *
     * @return array The payment infos
     */
    public static function getByPaymentMethod($payment_method, $id_nts_config)
    {
        $data = Db::getInstance()->getRow('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'nts_config_payment_method`
            WHERE `payment_method` = "' . pSQL($payment_method) . '"
            AND `id_nts_config` = ' . (int) $id_nts_config . '
        ');

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }
}
