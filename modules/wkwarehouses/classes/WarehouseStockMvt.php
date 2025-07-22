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

class WarehouseStockMvt extends ObjectModel
{
    public $id;

    /**
     * @var string The creation date of the movement
     */
    public $date_add;

    /**
     * @var int The employee id, responsible of the movement
     */
    public $id_employee;

    /**
     * @var string The first name of the employee responsible of the movement
     */
    public $employee_firstname;

    /**
     * @var string The last name of the employee responsible of the movement
     */
    public $employee_lastname;

    /**
     * @var int The stock id on wtich the movement is applied
     */
    public $id_stock;

    /**
     * @var int the quantity of product with is moved
     */
    public $physical_quantity;

    /**
     * @var int id of the movement reason assoiated to the movement
     */
    public $id_stock_mvt_reason;

    /**
     * @var int Used when the movement is due to a customer order
     */
    public $id_order = null;

    /**
     * @var int determine if the movement is a positive or negative operation
     */
    public $sign;

    /**
     * @var int Used when the movement is due to a supplier order
     */
    public $id_supply_order = null;

    /**
     * @var float Last value of the weighted-average method
     */
    public $last_wa = null;

    /**
     * @var float Current value of the weighted-average method
     */
    public $current_wa = null;

    /**
     * @var float The unit price without tax of the product associated to the movement
     */
    public $price_te;

    /**
     * @var int Refers to an other id_stock_mvt : used for LIFO/FIFO implementation in StockManager
     */
    public $referer;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'warehouse_stock_mvt',
        'primary' => 'id_stock_mvt',
        'fields' => [
            'id_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'employee_firstname' => ['type' => self::TYPE_STRING, 'validate' => 'isName'],
            'employee_lastname' => ['type' => self::TYPE_STRING, 'validate' => 'isName'],
            'id_stock' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'physical_quantity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_stock_mvt_reason' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_supply_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'sign' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'last_wa' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'current_wa' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'price_te' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'referer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true],
        ],
    ];

    protected $webserviceParameters = [
        'objectsNodeName' => 'stock_movements',
        'objectNodeName' => 'stock_movement',
        'fields' => [
            'id_employee' => ['xlink_resource' => 'employees'],
            'id_stock' => ['xlink_resource' => 'stock'],
            'id_stock_mvt_reason' => ['xlink_resource' => 'stock_movement_reasons'],
            'id_order' => ['xlink_resource' => 'orders'],
            'id_supply_order' => ['xlink_resource' => 'supply_order'],
        ],
    ];

    public static function createWarehouseStockMvtsTable()
    {
        Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::$definition['table'].'` (
              `id_stock_mvt` bigint(20) NOT NULL AUTO_INCREMENT,
              `id_stock` int(11) NOT NULL,
              `id_order` int(11) NULL DEFAULT NULL,
              `id_supply_order` int(11) NULL DEFAULT NULL,
              `id_stock_mvt_reason` int(11) NOT NULL,
              `id_employee` int(11) NOT NULL,
              `employee_lastname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
              `employee_firstname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
              `physical_quantity` int(11) NOT NULL,
              `date_add` datetime NOT NULL,
              `sign` smallint(6) NOT NULL DEFAULT 1,
              `price_te` decimal(20, 6) NULL DEFAULT 0.000000,
              `last_wa` decimal(20, 6) NULL DEFAULT 0.000000,
              `current_wa` decimal(20, 6) NULL DEFAULT 0.000000,
              `referer` bigint(20) NULL DEFAULT NULL,
              PRIMARY KEY (`id_stock_mvt`) USING BTREE,
              INDEX `id_stock`(`id_stock`) USING BTREE,
              INDEX `id_stock_mvt_reason`(`id_stock_mvt_reason`) USING BTREE
            ) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic'
        );
    }

    /**
     * Gets the negative (decrements the stock) stock mvts that correspond to the given order, for :
     * the given product, in the given quantity.
     *
     * @param int $id_order
     * @param int $id_product
     * @param int $id_product_attribute Use 0 if the product does not have attributes
     * @param int $quantity
     * @param int $id_warehouse Optional
     *
     * @return array mvts
     */
    public static function getNegativeStockMvts(
        $id_order,
        $id_product,
        $id_product_attribute,
        $quantity,
        $id_warehouse = null
    ) {
        $movements = [];
        $quantity_total = 0;

        // prepare query
        $query = new DbQuery();
        $query->select('sm.*, s.id_warehouse');
        $query->from(self::$definition['table'], 'sm');
        $query->innerJoin('stock', 's', 's.id_stock = sm.id_stock');
        $query->where('sm.sign = -1');
        $query->where('sm.id_order = '.(int)$id_order);
        $query->where('s.id_product = '.(int)$id_product.' AND s.id_product_attribute = '.(int)$id_product_attribute);
        // if filer by warehouse
        if (null !== $id_warehouse) {
            $query->where('s.id_warehouse = '.(int)$id_warehouse);
        }
        // order movements by date
        $query->orderBy('id_stock_mvt DESC, date_add DESC');

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query, false);

        // fill the movements array
        while ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->nextRow($res)) {
            if ($quantity_total >= $quantity) {
                break;
            }
            $quantity_total += (int)$row['physical_quantity'];
            $movements[] = $row;
        }
        return $movements;
    }

    /**
     * For a given product, get the last positive stock mvt.
     *
     * @return bool|array
     */
    public static function getLastPositiveStockMvt($id_product, $id_product_attribute, $id_warehouse = null)
    {
        $query = new DbQuery();
        $query->select('sm.*, w.id_currency, (s.usable_quantity = sm.physical_quantity) as is_usable');
        $query->from(self::$definition['table'], 'sm');
        $query->innerJoin('stock', 's', 's.id_stock = sm.id_stock');
        $query->innerJoin('warehouse', 'w', 'w.id_warehouse = s.id_warehouse');
        $query->where('sm.sign = 1');
        if (!is_null($id_warehouse)) {
            $query->where('w.id_warehouse = '.$id_warehouse);
        }
        if ($id_product_attribute) {
            $query->where(
                's.id_product = '.(int)$id_product.' AND s.id_product_attribute = '.(int)$id_product_attribute
            );
        } else {
            $query->where('s.id_product = '.(int)$id_product);
        }
        $query->orderBy('id_stock_mvt DESC, date_add DESC');

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($res != false) {
            return $res['0'];
        }

        return false;
    }

    public static function getLastProductUnitPrice($id_product, $id_product_attribute, $id_warehouse = null)
    {
        $last_sm = self::getLastPositiveStockMvt($id_product, $id_product_attribute, $id_warehouse);
        // if there is a stock mvt
        if ($last_sm != false) {
            $price_te = $last_sm['price_te'];
        } else {
            $price_te = WorkshopAsm::getWholesalePrice($id_product, $id_product_attribute);
        }
        return $price_te ? Tools::ps_round((float)$price_te, 2) : 0;
    }

    public static function deleteStockMvt($id_stock)
    {
        Db::getInstance()->execute(
            'DELETE FROM '._DB_PREFIX_.self::$definition['table'].' WHERE `id_stock` = '.(int)$id_stock
        );
    }
}
