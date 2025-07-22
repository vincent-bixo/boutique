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

class StorehouseProductLocation extends ObjectModel
{
    public $id_product;
    public $id_product_attribute;
    public $id_warehouse;
    public $location;

    public static $definition = array(
        'table' => 'warehouse_product_location',
        'primary' => 'id_warehouse_product_location',
        'fields' => array(
            'location' => array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 64),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_warehouse' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
        ),
    );

    protected $webserviceParameters = array(
        'fields' => array(
            'id_product' => array('xlink_resource' => 'products'),
            'id_product_attribute' => array('xlink_resource' => 'combinations'),
            'id_warehouse' => array('xlink_resource' => 'warehouses'),
        ),
        'hidden_fields' => array(
        ),
    );

    public function add($autodate = true, $null_values = false)
    {
        $res = parent::add($autodate, $null_values);
        if ($res) {
            // Add stock trace automatically
            (new WorkshopAsm())->addProduct(
                $this->id_product,
                $this->id_product_attribute,
                (new StoreHouse($this->id_warehouse)),
                0
            );
            // Align automatically warehouse quantities to Prestashop quantity
            (new WorkshopAsm())->synchronize(
                $this->id_product,
                $this->id_product_attribute
            );
        }
        return $res;
    }

    public function delete()
    {
        if (parent::delete()) {
            // Remove also stock
            WorkshopAsm::removeStock($this->id_product, $this->id_product_attribute, $this->id_warehouse);
            return true;
        }
        return false;
    }

    /**
     * For a given product and warehouse, gets the location
     *
     * @param int $id_product product ID
     * @param int $id_product_attribute product attribute ID
     * @param int $id_warehouse warehouse ID
     * @return string $location Location of the product
     */
    public static function getProductLocation($id_product, $id_product_attribute, $id_warehouse)
    {
        // build query
        $query = new DbQuery();
        $query->select('wpl.location');
        $query->from('warehouse_product_location', 'wpl');
        $query->where(
            'wpl.id_product = '.(int)$id_product.' AND 
             wpl.id_product_attribute = '.(int)$id_product_attribute.' AND 
             wpl.id_warehouse = '.(int)$id_warehouse
        );
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * For a given product and warehouse, gets the StorehouseProductLocation corresponding ID
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_warehouse
     * @return int $id_warehouse_product_location ID of the StorehouseProductLocation
     */
    public static function getIdByProductAndWarehouse($id_product, $id_product_attribute, $id_warehouse)
    {
        // build query
        $query = new DbQuery();
        $query->select('wpl.id_warehouse_product_location');
        $query->from('warehouse_product_location', 'wpl');
        $query->where(
            'wpl.id_product = '.(int)$id_product.' AND 
             wpl.id_product_attribute = '.(int)$id_product_attribute.' AND 
             wpl.id_warehouse = '.(int)$id_warehouse
        );
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    /**
     * For a given product, gets its warehouses
     * @return PrestaShopCollection The type of the collection is StorehouseProductLocation
     */
    public static function getCollection(
        $id_product = null,
        $id_product_attribute = null,
        $id_warehouse = null,
        $order_by = false
    ) {
        // Get valid warehouses
        $warehouseList = array();
        foreach (StoreHouse::getWarehouses(null, false) as $row) {
            $warehouseList[] = (int)$row['id_warehouse'];
        }

        $collection = new PrestaShopCollection('StorehouseProductLocation');
        if (!is_null($id_product)) {
            $collection->where('id_product', '=', (int)$id_product);
        }
        if (!is_null($id_product_attribute)) {
            $collection->where('id_product_attribute', '=', (int)$id_product_attribute);
        }
        if (!empty($id_warehouse)) {
            $collection->where('id_warehouse', '=', (int)$id_warehouse);
        }
        if (count($warehouseList)) {
            $collection->where('id_warehouse', 'IN', $warehouseList);
        } else {
			if (!defined('_PS_ADMIN_DIR_')) {// it means all warehouses are disabled
				return null;
			}
		}
        if ($order_by) {
            $collection->orderBy('id_warehouse');
        }
        return $collection;
    }

    public static function getProducts($id_warehouse, $asm_select = false)
    {
        return Db::getInstance()->executeS(
            'SELECT DISTINCT id_product 
             FROM '._DB_PREFIX_.'warehouse_product_location 
             WHERE id_warehouse = '.(int)$id_warehouse
             .($asm_select ? ' AND id_product IN (
                 SELECT p.id_product
                 FROM '._DB_PREFIX_.'product p 
                 '.Shop::addSqlAssociation('product', 'p').'
                 WHERE p.advanced_stock_management = 1
              )' : '')
        );
    }
}
