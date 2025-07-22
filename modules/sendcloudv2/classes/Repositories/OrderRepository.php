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

namespace Sendcloud\PrestaShop\Classes\Repositories;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class OrderRepository
 *
 * @package Sendcloud\PrestaShop\Classes\Repositories
 */
class OrderRepository
{
    const TABLE_NAME = 'orders';

    /**
     * Retrieve cart id based on order id.
     *
     * @param $orderId
     *
     * @return string|null
     */
    public function getCartByOrderId($orderId)
    {
        $query = new \DbQuery();
        $query->select('id_cart');
        $query->from(self::TABLE_NAME);
        $query->where('id_order = ' . (int)$orderId);

        return \Db::getInstance()->getValue($query);
    }
}
