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

class StoreHouse extends ObjectModel
{
    /** @var int identifier of the warehouse */
    public $id;

    /** @var int Id of the address associated to the warehouse */
    public $id_address;

    /** @var string Reference of the warehouse */
    public $reference;

    /** @var int Id of the employee who manages the warehouse */
    public $id_employee;

    /** @var int Id of the valuation currency of the warehouse */
    public $id_currency;

    /** @var bool True if warehouse has been deleted (hence, no deletion in DB) */
    public $deleted = 0;

    public $active = 1;

    /**
     * Describes the way a Warehouse is managed
     *
     * @var string enum WA|LIFO|FIFO
     */
    public $management_type;

    /** @var string Name of the warehouse */
    public $name;

    /** @var string delivery time of warehouse */
    public $delivery_time;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'warehouse',
        'primary' => 'id_warehouse',
        'multilang' => true,
        'fields' => array(
            'id_address' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'reference' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 45),
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'management_type' => array('type' => self::TYPE_STRING, 'validate' => 'isStockManagement'),
            'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'deleted' => array('type' => self::TYPE_BOOL),
            'active' => array('type' => self::TYPE_BOOL),
            /* Lang fields */
            'name' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 255
            ),
            'delivery_time' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'size' => 255
            ),
        ),
    );

    /**
     * @see ObjectModel::$webserviceParameters
     */
    protected $webserviceParameters = array(
        'fields' => array(
            'id_address' => array('xlink_resource' => 'addresses'),
            'id_employee' => array('xlink_resource' => 'employees'),
            'id_currency' => array('xlink_resource' => 'currencies'),
            'valuation' => array('getter' => 'getWsStockValue', 'setter' => false),
            'deleted' => array(),
        ),
        'associations' => array(
            'stocks' => array(
                'resource' => 'stock',
                'fields' => array(
                    'id' => array(),
                ),
            ),
            'carriers' => array(
                'resource' => 'carrier',
                'fields' => array(
                    'id' => array(),
                ),
            ),
            'shops' => array(
                'resource' => 'shop',
                'fields' => array(
                    'id' => array(),
                    'name' => array(),
                ),
            ),
        ),
    );

    /**
     * WarehouseCore constructor.
     *
     * @param int|null $id warehouse ID
     * @param int|null $idLang Language ID
     * @param int|null $idShop Shop ID
     */
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
    }

    /**
     * Gets the carriers associated to the current warehouse
     *
     * @return array Ids of the associated carriers
     */
    public function getCarriers($return_reference = false)
    {
        $ids_carrier = array();

        $query = new DbQuery();
        if ($return_reference) {
            $query->select('wc.id_carrier');
        } else {
            $query->select('c.id_carrier');
        }
        $query->from('warehouse_carrier', 'wc');
        $query->innerJoin('carrier', 'c', 'c.id_reference = wc.id_carrier');
        $query->where('id_warehouse = '.(int)$this->id);
        $query->where('c.deleted = 0');
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!is_array($res)) {
            return $ids_carrier;
        }

        foreach ($res as $carriers) {
            foreach ($carriers as $carrier) {
                $ids_carrier[$carrier] = $carrier;
            }
        }
        return $ids_carrier;
    }

    /**
     * Sets the carriers associated to the current warehouse
     *
     * @param array $ids_carriers
     */
    public function setCarriers($ids_carriers)
    {
        if (!is_array($ids_carriers)) {
            $ids_carriers = array();
        }

        $row_to_insert = array();
        foreach ($ids_carriers as $id_carrier) {
            $row_to_insert[] = array(
                'id_warehouse' => (int)$this->id,
                'id_carrier' => (int)$id_carrier
            );
        }

        Db::getInstance()->execute(
            'DELETE FROM '._DB_PREFIX_.'warehouse_carrier
             WHERE id_warehouse = '.(int)$this->id
        );

        $unique_array = array();
        foreach ($row_to_insert as $sub_array) {
            if (!in_array($sub_array, $unique_array)) {
                $unique_array[] = $sub_array;
            }
        }
        if (count($unique_array)) {
            Db::getInstance()->insert('warehouse_carrier', $unique_array);
        }
    }

    /**
     * For a given carrier, removes it from the warehouse/carrier association
     * If $id_warehouse is set, it only removes the carrier for this warehouse
     *
     * @param int $id_carrier Id of the carrier to remove
     * @param int $id_warehouse optional Id of the warehouse to filter
     */
    public static function removeCarrier($id_carrier, $id_warehouse = null)
    {
        Db::getInstance()->execute(
            'DELETE FROM '._DB_PREFIX_.'warehouse_carrier
             WHERE id_carrier = '.(int)$id_carrier.
            ($id_warehouse ? ' AND id_warehouse = '.(int)$id_warehouse : '')
        );
    }

    /**
     * Resets all product locations for this warehouse
     */
    public function resetProductsLocations()
    {
        Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'warehouse_product_location`
             WHERE `id_warehouse` = '.(int)$this->id
        );
    }

    /**
     * For a given {product, product attribute} gets its location in the given warehouse
     *
     * @param int $id_product ID of the product
     * @param int $id_product_attribute Use 0 if this product does not have attributes
     * @param int $id_warehouse ID of the warehouse
     * @return string Location of the product
     */
    public static function getProductLocation($id_product, $id_product_attribute, $id_warehouse)
    {
        $query = new DbQuery();
        $query->select('location');
        $query->from('warehouse_product_location');
        $query->where('id_warehouse = '.(int)$id_warehouse);
        $query->where('id_product = '.(int)$id_product);
        $query->where('id_product_attribute = '.(int)$id_product_attribute);
        return (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query));
    }

    /**
     * Checks if the given warehouse exists
     *
     * @param int $id_warehouse
     * @return bool Exists/Does not exist
	 * DO NOT REMOVE - USED BY ANOTHER MODULE
     */
    public static function exists($id_warehouse)
    {
        $query = new DbQuery();
        $query->select('id_warehouse');
        $query->from('warehouse');
        $query->where('id_warehouse = '.(int)$id_warehouse);
        $query->where('deleted = 0');
        return (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query));
    }

    /**
     * For a given {product, product attribute} gets warehouse list
     *
     * @return warehouses array
     */
    public static function getProductWarehouseList(
        $id_product,
        $id_product_attribute = 0,
        $with_stock = false
    ) {
        $query = new DbQuery();
        $query->select('wpl.id_warehouse, wl.name, wl.delivery_time, location, w.id_address');
        $query->from('warehouse_product_location', 'wpl');
        $query->innerJoin(
            'warehouse_shop',
            'ws',
            'ws.id_warehouse = wpl.id_warehouse'
        );
        $query->innerJoin('warehouse', 'w', 'ws.id_warehouse = w.'.self::$definition['primary']);
        if (!defined('_PS_ADMIN_DIR_')) {
            $query->where('w.`active` = 1');
        }
        $query->leftJoin(
            'warehouse_lang',
            'wl',
            'w.`id_warehouse` = wl.`id_warehouse` AND `id_lang` = '.(int)Context::getContext()->language->id
        );
        if ($with_stock) {
            $query->innerJoin(
                'stock',
                's',
                's.`id_warehouse` = wpl.`id_warehouse` AND
                 s.`id_product` = wpl.`id_product` AND
                 s.`id_product_attribute` = wpl.`id_product_attribute`'
            );
        }
        $query->where('wpl.id_product = '.(int)$id_product);
        $query->where('wpl.id_product_attribute = '.(int)$id_product_attribute);
        $query->where('w.deleted = 0');
        $query->groupBy('wpl.id_warehouse');
        //var_dump($query->build());
        //exit();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        // Have country name of each warehouse
        foreach ($result as &$data) {
            $country_address = Address::getCountryAndState($data['id_address']);
            $data['country'] = (new Country($country_address['id_country'], (int)Context::getContext()->language->id))->name;
        }
        return $result;
    }

    /**
     * Gets available warehouses
     * @return array Warehouses (ID, reference/name concatenated)
     */
    public static function getWarehouses($ids_filter = null, $concat = true)
    {
        $query = new DbQuery();
        $query->select(
            'w.`'.self::$definition['primary'].'`,
            '.($concat ? 'CONCAT(`reference`, \' - \', wl.`name`)' : 'wl.`name`').' as name,
            `reference`'
        );
        $query->from('warehouse', 'w');
        $query->leftJoin(
            'warehouse_lang',
            'wl',
            'w.`id_warehouse` = wl.`id_warehouse` AND `id_lang` = '.(int)Context::getContext()->language->id
        );
        $query->where('`deleted` = 0');
        if (!defined('_PS_ADMIN_DIR_')) {
            $query->where('active', '=', 1);
        }
        if (!empty($ids_filter) && is_array($ids_filter)) {
            $query->where('w.`id_warehouse` IN ('.pSQL(implode(',', array_map('intval', $ids_filter))).')');
        }
        $query->orderBy('reference ASC');
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    /**
     * Gets the number of products in the current warehouse
     *
     * @return int Number of different id_stock
     */
    public function getNumberOfProducts()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(t.id_stock)
             FROM (
                SELECT s.id_stock
                FROM '._DB_PREFIX_.'stock s
                INNER JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON (
                    wpl.id_product = s.id_product AND 
                    wpl.id_product_attribute = s.id_product_attribute AND 
                    wpl.id_warehouse = s.id_warehouse
                )
                WHERE s.id_warehouse = '.(int)$this->id.'
                GROUP BY s.id_product, s.id_product_attribute
            ) as t'
        );
    }

    /**
     * Gets the number of quantities - for all products - in the current warehouse
     *
     * @return int Total Quantity
     */
    public function getQuantitiesOfProducts()
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT SUM(s.physical_quantity)
             FROM '._DB_PREFIX_.'stock s
             INNER JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON (
                wpl.id_product = s.id_product AND 
                wpl.id_product_attribute = s.id_product_attribute AND 
                wpl.id_warehouse = s.id_warehouse
             )
             WHERE s.id_warehouse = '.(int)$this->id
        );
        return ($res ? $res : 0);
    }

    public static function hasHiddenWarehouses()
    {
        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(*)
             FROM '._DB_PREFIX_.self::$definition['table'].' 
             WHERE `active` = 0 AND `deleted` = 0'
        );
    }

    /**
     * Gets the value of the stock in the current warehouse
     *
     * @return int Value of the stock
     */
    public function getStockValue()
    {
        $query = new DbQuery();
        $query->select('SUM(s.`price_te` * s.`physical_quantity`)');
        $query->from('stock', 's');
        $query->where('s.`id_warehouse` = '.(int)$this->id);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * For a given employee, gets the warehouse(s) he/she manages
     *
     * @param int $id_employee Manager ID
     * @return array ids_warehouse Ids of the warehouses
     */
    public static function getWarehousesByEmployee($id_employee)
    {
        $query = new DbQuery();
        $query->select('w.'.self::$definition['primary']);
        $query->from('warehouse', 'w');
        $query->where('w.id_employee = '.(int)$id_employee);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    /**
     * For a given product, return the warehouses it is stored in
     *
     * @param int $id_product Product Id
     * @param int $id_product_attribute Optional, Product Attribute Id - 0 by default (no attribues)
     * @return array Warehouses Ids and names
     */
    public static function getWarehousesByProductId($id_product, $id_product_attribute = 0)
    {
        if (!$id_product && !$id_product_attribute) {
            return array();
        }

        $query = new DbQuery();
        $query->select('DISTINCT w.'.self::$definition['primary'].', CONCAT(w.reference, " - ", wl.name) as name');
        $query->from('warehouse', 'w');
        $query->leftJoin(
            'warehouse_lang',
            'wl',
            'w.`id_warehouse` = wl.`id_warehouse` AND `id_lang` = '.(int)Context::getContext()->language->id
        );
        $query->leftJoin('warehouse_product_location', 'wpl', 'wpl.id_warehouse = w.'.self::$definition['primary']);
        if ($id_product) {
            $query->where('wpl.id_product = '.(int)$id_product);
        }
        if ($id_product_attribute) {
            $query->where('wpl.id_product_attribute = '.(int)$id_product_attribute);
        }
        $query->orderBy('w.reference ASC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    /**
     * For a given $id_warehouse, returns its name
     *
     * @param int $id_warehouse Warehouse Id
     * @return string Name
     */
    public static function getWarehouseNameById($id_warehouse)
    {
        $query = new DbQuery();
        $query->select('wl.`name`');
        $query->from('warehouse', 'w');
        $query->leftJoin(
            'warehouse_lang',
            'wl',
            'w.`id_warehouse` = wl.`id_warehouse` AND `id_lang` = '.(int)Context::getContext()->language->id
        );
        $query->where('w.'.self::$definition['primary'].' = '.(int)$id_warehouse);
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * Webservice : gets the value of the warehouse
     * @return int
     */
    public function getWsStockValue()
    {
        return $this->getStockValue();
    }

    /**
     * Webservice : gets the ids stock associated to this warehouse
     * @return array
     */
    public function getWsStocks()
    {
        $query = new DbQuery();
        $query->select('s.id_stock as id');
        $query->from('stock', 's');
        $query->where('s.id_warehouse ='.(int)$this->id);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    /**
     * Webservice : gets the ids carriers associated to this warehouse
     * @return array
     */
    public function getWsCarriers()
    {
        $ids_carrier = array();

        $query = new DbQuery();
        $query->select('wc.id_carrier as id');
        $query->from('warehouse_carrier', 'wc');
        $query->where('id_warehouse = '.(int)$this->id);
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (!is_array($res)) {
            return $ids_carrier;
        }

        foreach ($res as $carriers) {
            foreach ($carriers as $carrier) {
                $ids_carrier[] = $carrier;
            }
        }
        return $ids_carrier;
    }

    public static function exportWarehousesLanguages()
    {
        // Backup warehouses names before
        $query = new DbQuery();
        $query->select('`id_warehouse`, `name`');
        $query->from('warehouse');
        $query->where('`deleted` = 0');
        $warehouses = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        // Restore warehouses names for each language in language table
        $result = true;
        foreach ($warehouses as $warehouse) {
            foreach (Language::getLanguages(false) as $lang) {
                $sql = 'SELECT * FROM `'._DB_PREFIX_.'warehouse_lang` 
                            WHERE `id_warehouse` = '.(int)$warehouse['id_warehouse'].' AND 
                            `id_lang` = '.(int)$lang['id_lang'];
                if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
                    $result &= Db::getInstance()->insert(
                        'warehouse_lang',
                        array(
                            'id_warehouse' => (int)$warehouse['id_warehouse'],
                            'id_lang' => (int)$lang['id_lang'],
                            'name' => pSQL($warehouse['name']),
                        )
                    );
                }
            }
        }
        return $result;
    }
}
