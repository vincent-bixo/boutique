<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class Warehouse extends WarehouseCore
{
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        self::$definition['multilang'] = true;
        self::$definition['fields']['name'] = array(
            'type' => self::TYPE_STRING,
            'lang' => true,
            'validate' => 'isGenericName',
            'required' => true,
            'size' => 255
        );
        self::$definition['fields']['active'] = array(
            'type' => self::TYPE_BOOL,
        );
        if (is_null($idLang)) {
            $idLang = Context::getContext()->language->id;
        }
        parent::__construct($id, $idLang, $idShop);
    }
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public static function getProductWarehouseList($id_product, $id_product_attribute = 0, $id_shop = null)
    {
        $query = new DbQuery();
        $query->select('wpl.id_warehouse, CONCAT(w.reference, " - ", wl.name) as name');
        $query->from('warehouse_product_location', 'wpl');
        $query->innerJoin(
            'warehouse_shop',
            'ws',
            'ws.id_warehouse = wpl.id_warehouse'
        );
        $query->innerJoin('warehouse', 'w', 'ws.id_warehouse = w.id_warehouse');
        $query->leftJoin(
            'warehouse_lang',
            'wl',
            'w.`id_warehouse` = wl.`id_warehouse` AND `id_lang` = '.(int)Context::getContext()->language->id
        );
        $query->where('id_product = ' . (int) $id_product);
        $query->where('id_product_attribute = ' . (int) $id_product_attribute);
        $query->where('w.deleted = 0');
        if (!defined('_PS_ADMIN_DIR_')) {
        	$query->where('w.active = 1');
		}
        $query->groupBy('wpl.id_warehouse');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
		if (version_compare(_PS_VERSION_, '1.7.7', '<') && $result &&
			Configuration::get('WKWAREHOUSE_STOCKSINFOS_ORDER_PAGE')) {
			if (!class_exists('StoreHouse')) {
				require_once(dirname(__FILE__).'/../../../modules/wkwarehouses/classes/WarehouseStock.php');
			}
            foreach ($result as &$row) {
				$available_quantity = WarehouseStock::getAvailableQuantityByWarehouse(
					$id_product,
					$id_product_attribute,
					$row['id_warehouse']
				);
                $row['name'] = $row['name'].' | Qty: '.$available_quantity;
            }
		}
        return $result;
    }
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public static function getWarehouses($ignore_shop = false, $id_shop = null)
    {
        if (!$ignore_shop) {
            if (null === $id_shop) {
                $id_shop = Context::getContext()->shop->id;
            }
        }
        $query = new DbQuery();
        $query->select('w.id_warehouse, CONCAT(reference, \' - \', wl.name) as name');
        $query->from('warehouse', 'w');
        $query->leftJoin(
            'warehouse_lang',
            'wl',
            'w.`id_warehouse` = wl.`id_warehouse` AND `id_lang` = '.(int)Context::getContext()->language->id
        );
        $query->where('deleted = 0');
        if (!defined('_PS_ADMIN_DIR_')) {
            $query->where('active', '=', 1);
        }
        $query->orderBy('reference ASC');
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public static function getWarehousesByProductId($id_product, $id_product_attribute = 0)
    {
        if (!$id_product && !$id_product_attribute) {
            return array();
        }
        $query = new DbQuery();
        $query->select('DISTINCT w.id_warehouse, CONCAT(w.reference, " - ", wl.name) as name');
        $query->from('warehouse', 'w');
        $query->leftJoin(
            'warehouse_lang',
            'wl',
            'w.`id_warehouse` = wl.`id_warehouse` AND `id_lang` = '.(int)Context::getContext()->language->id
        );
        $query->leftJoin('warehouse_product_location', 'wpl', 'wpl.id_warehouse = w.id_warehouse');
        if ($id_product) {
            $query->where('wpl.id_product = ' . (int) $id_product);
        }
        if ($id_product_attribute) {
            $query->where('wpl.id_product_attribute = ' . (int) $id_product_attribute);
        }
        $query->orderBy('w.reference ASC');
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }
}
