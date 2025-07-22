<?php
/**
 * Utility class for SendCloud module.
 *
 * PHP version 7.4
 *
 * @author    SendCloud Global B.V. <contact@sendcloud.eu>
 * @copyright 2023 SendCloud Global B.V.
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 * @category  Shipping
 *
 * @see      https://sendcloud.eu
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Class Product
 *
 * @package Sendcloud\PrestaShop\Classes\Models
 */
class Product extends ProductCore
{
    /*
    * module: sendcloudv2
    * date: 2025-07-09 01:21:30
    * version: 2.0.16
    */
    const TABLE_NAME = 'product';
    /*
    * module: sendcloudv2
    * date: 2025-07-09 01:21:30
    * version: 2.0.16
    */
    public $sc_hs_code;
    /*
    * module: sendcloudv2
    * date: 2025-07-09 01:21:30
    * version: 2.0.16
    */
    public $sc_country_of_origin;
    /*
    * module: sendcloudv2
    * date: 2025-07-09 01:21:30
    * version: 2.0.16
    */
    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, \Context $context = null)
    {
        self::$definition['fields']['sc_hs_code'] = ['type' => self::TYPE_INT, 'validate' => 'isInt', 'size' => 10];
        self::$definition['fields']['sc_country_of_origin'] = ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 3];
        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
    }
}
