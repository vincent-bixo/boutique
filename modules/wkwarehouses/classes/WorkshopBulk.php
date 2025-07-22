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

class WorkshopBulk
{
    public $options = array();
    /*
     * Advanced Search for Products
    */
    public static function advSearchProducts(
        $id_cat = false,
        $ids = false
    ) {
        $id_lang = Context::getContext()->language->id;
        $shop = Context::getContext()->shop;
        $only_active = false;
        $isCombFeatureActive = (Combination::isFeatureActive() ? true : false);

        // Filters used to search products
        $id_supplier = Tools::getValue('id_supplier');
        $id_manufacturer = Tools::getValue('id_manufacturer');
        $attributes_ids = Tools::getValue('attributesSearch');
        $id_warehouse = Tools::getValue('id_warehouse');
        $search_is_active = Tools::getValue('search_is_active');
        if (empty($search_is_active)) {
            $search_is_active = 'both';
        }
		if (Tools::getIsset('id_brand') && Tools::getValue('id_brand')) {
        	$id_manufacturer = Tools::getValue('id_brand');
		}

        $sql = 'SELECT SQL_CALC_FOUND_ROWS
					p.*, pl.name, IF(
					   p.advanced_stock_management = 1 AND depends_on_stock = 0,
					   (
							SELECT SUM(s.physical_quantity) FROM '._DB_PREFIX_.'stock s WHERE p.id_product = s.id_product
						),
					   stock.physical_quantity
					) as quantity,
					image_shop.`id_image` id_image
					FROM `'._DB_PREFIX_.'product` p
					'.Shop::addSqlAssociation('product', 'p').'
					LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
						p.`id_product` = pl.`id_product` AND pl.`id_shop` = '.(int)$shop->id.'
					)
					'.($isCombFeatureActive ? '
						LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
						'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop = '.(int)$shop->id) : '')
					.Product::sqlStock('p', 0)
					.($id_cat != false && $id_cat > 0 ? '
					  LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = p.`id_product`)
					  ' : '')
					// Product attributes associations
					.(!empty($attributes_ids) && $isCombFeatureActive ? ' 
					 LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` ppac ON (
						pa.`id_product_attribute` = ppac.`id_product_attribute`
					 ) ' : '');

		if ($ids != false) {
			$id_warehouse = $ids;
		}
		// by warehouse(s) ID
		if (!empty($id_warehouse) && (is_array($id_warehouse) || is_numeric($id_warehouse))) {
			$sql .= ' INNER JOIN `'._DB_PREFIX_.'warehouse_product_location` wpl ON (
				wpl.id_product = p.id_product AND 
				wpl.id_warehouse IN ('.(is_array($id_warehouse) ? implode(',', $id_warehouse) : (int)$id_warehouse).')
			)';
		}
        // By supplier
        if (!empty($id_supplier)) {
            $sql .= ' INNER JOIN `'._DB_PREFIX_.'product_supplier` ps ON (
                ps.id_product = p.id_product AND 
                ps.id_supplier IN ('.(is_array($id_supplier) ? implode(',', $id_supplier) : (int)$id_supplier).')
            )';
        }
		if (!version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
			$sql .= ' LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (
				image_shop.`id_product` = p.`id_product` AND image_shop.cover = 1 AND 
				image_shop.id_shop = '.(int)$shop->id.'
			)';
		} else {
			$sql .= ' LEFT JOIN `'._DB_PREFIX_.'image` i ON (
				i.`id_product` = p.`id_product` AND i.`cover` = 1
			)'.Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover = 1');
		}

        $sql .= ' WHERE pl.`id_lang` = '.(int)$id_lang;

        if ($id_cat != false && $id_cat > 0) {
            $sql .= ' AND c.`id_category` = '.(int)$id_cat;
        }
		if (Tools::getIsset('use_asm') && Tools::getValue('use_asm')) {// Filter from right panel (use only ASM?)
			$use_asm = 1;
		}
		if (!is_array($id_warehouse) || isset($use_asm)) {
			if ($id_warehouse == 'depends_on_stock' || (isset($use_asm) && $use_asm)) {
				$sql .= ' AND p.advanced_stock_management = 1 ';
			} elseif ($id_warehouse == 'normal_stock') {
				$sql .= ' AND p.advanced_stock_management = 0 ';
			} elseif ($id_warehouse == 'without_warehouses') {
				$sql .= ' AND p.id_product NOT IN (
					SELECT DISTINCT(id_product) FROM '._DB_PREFIX_.'warehouse_product_location
				) ';
			}
		}
        // By Attributes
        $sub_queries = array();
        if (!empty($attributes_ids)) {
            $attributes_ids = explode(',', $attributes_ids);
            if (sizeof($attributes_ids) && Tools::getIsset('attributesSearch')) {
                foreach ($attributes_ids as $attribute) {
                    $sub_queries[] = 'ppac.`id_attribute` = '.(int)$attribute;
                }
                $sql .= ' AND ('.implode(' OR ', $sub_queries).') ';
            }
        }

        $sql .= ' '. ($only_active ? ' AND p.`active` = 1' : '').' ';

        if (!empty($id_manufacturer)) {
            $sql .= ' AND p.`id_manufacturer` = '.(int)$id_manufacturer;
        }
        // By Product Status
        if ($search_is_active == 'active') {
            $sql .= ' AND product_shop.`active` = 1';
        }
        if ($search_is_active == 'disable') {
            $sql .= ' AND product_shop.`active` = 0';
        }

        // Where (filter by input field)
        $columns = array(
            'p.reference',
            'pl.name',
            'p.ean13',
            'p.upc',
            'p.supplier_reference',
            'pl.description',
        );
        if ($isCombFeatureActive) {
            array_push($columns, 'pa.reference', 'pa.ean13', 'pa.upc');
        }
        $wheres_input_filter = self::fnFilterQuery($columns);
        if ($wheres_input_filter) {
            $sql .= ' AND '.$wheres_input_filter;
        }
        $sql .= ' GROUP BY p.id_product ';

        /* Ordering */
        $columns = array(
            1 => 'id_product',
            3 => 'name',
            4 => 'quantity',
            6 => 'advanced_stock_management',
        );
        $sql .= ' ORDER BY '.self::fnColumnToField($columns);

        /* Paging */
        if (Tools::getIsset('start') && Tools::getValue('length') != -1) {
            $sql .= ' LIMIT '.(int)Tools::getValue('start').', '.(int)Tools::getValue('length');
        }
        //echo '<pre>'.$sql;
        $result = Db::getInstance()->executeS($sql);

        // Count all rows
        $iFilteredTotal = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()', false);

        $sOutput = array(
            'draw' => Tools::getIsset('draw') ? (int)Tools::getValue('draw') : 0,
            'recordsTotal' => (int)$iFilteredTotal,
            'recordsFiltered' => (int)$iFilteredTotal,
            'data' => array()
        );
        foreach ($result as $product) {
            // Prepare Datatables Rows
            $row = array();
            // Add the row ID and class to the object
            $row['DT_RowId'] = $product['id_product'];
            $row[] = '';
            $row[] = $product['id_product'];
            $row[] = self::printProductImage($product);
            $row[] = $product['name'];
            $row[] = (int)$product['quantity'];
            $row[] = self::getWarehousesCount($product['id_product']);
            $row[] = (int)$product['advanced_stock_management'];
            $row[] = self::productLink($product['id_product'], 'Warehouses');
            $sOutput['data'][] = $row;
        }
        return $sOutput;
    }

    public static function printProductImage($product)
    {
        $image = Product::getCover($product['id_product']);
        if (!empty($image['id_image'])) {
            return ImageManager::thumbnail(
                _PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($image['id_image']) . $image['id_image'] . '.jpg',
                'product_mini_' . $product['id_product'] . '_' . $image['id_image'] . '.jpg',
                45
            );
        }
        return '';
    }

    /*
     * Products to use for ajax!
    */
    public static function getProductsFromDatabase($products_ids, $offset, $limit)
    {
        return Db::getInstance()->executeS(
            'SELECT p.`id_product`
             FROM `'._DB_PREFIX_.'product` p 
             '.Shop::addSqlAssociation('product', 'p').'
             WHERE p.`id_product` IN ('.implode(',', $products_ids).')'.
             ($offset !== false && $limit ? ' LIMIT '.$offset.','.$limit : '')
        );
    }

    /**
    * Count product warehouses
    */
    public static function getWarehousesCount($id_product)
    {
        return (int)Db::getInstance()->getValue(
            'SELECT COUNT(DISTINCT(`id_warehouse`))
             FROM `'._DB_PREFIX_.'warehouse_product_location`
             WHERE id_product = '.(int)$id_product
        );
    }

    /**
     * Datatables : Searching / Filtering
     *
     * Construct the WHERE clause for server-side processing SQL query.
     * @return string SQL where clause
     */
    public static function fnFilterQuery($columns)
    {
        $globalSearch = array();
        $where = '';
        $boolOperator = Tools::getValue('searchMode') ? Tools::getValue('searchMode') : 'OR';

        if (Tools::getIsset('search')) {
            $search_data = Tools::getValue('search');
            $str = Tools::strtolower(trim($search_data['value']));
            if (!empty($str)) {
                $columns_post = Tools::getValue('columns');
                if ($boolOperator != 'EXACT') {
                    $words = array_filter(explode(' ', addslashes($str)));
                }

                for ($j = 0, $len = count($columns); $j < $len; $j++) {
                    $found = false;
                    $column_name = $columns[$j];
                    for ($i = 0, $ien = count($columns_post); $i < $ien; $i++) {
                        $requestColumn = $columns_post[$i];

                        if (isset($requestColumn['name']) && $requestColumn['name'] == $column_name) {
                            if ($requestColumn['searchable'] == 'true') {
                                if ($boolOperator == 'EXACT') {
                                    // Search the exact word(s)
                                    $globalSearch[] = "LOWER(".pSQL($column_name).") REGEXP '[[:<:]]".pSQL($str)."[[:>:]]'";
                                } else {
                                    $wheres = array();
                                    foreach ($words as $word) {
                                        $wheres2   = array();
                                        $wheres2[] = 'LOWER('.pSQL($column_name).') LIKE LOWER("%'.pSQL($word).'%")';
                                        $wheres[]  = implode(' OR ', $wheres2);
                                    }
                                    $globalSearch[] = '('.implode(($boolOperator == 'AND' ? ') AND (' : ') OR ('), $wheres).')';
                                }
                            }
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $globalSearch[] = 'LOWER('.pSQL($column_name).') LIKE "%'.pSQL($str).'%"';
                    }
                }
            }
        }
        // Combine the filters into a single string
        if (count($globalSearch)) {
            $where = '('.implode(' OR ', $globalSearch).')';
        }
        return $where;
    }

    /**
     * Datatable > Ordering
     *
     * Construct the ORDER BY clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL order by clause
     */
    public static function fnColumnToField($columns)
    {
        $order = '';
        if (Tools::getIsset('order')) {
            $order_request = Tools::getValue('order');
            if (count($order_request)) {
                $orderBy = array();
                for ($i = 0, $ien = count($order_request); $i < $ien; $i++) {
                    // Convert the column index into the column data property
                    $columnIdx = (int)$order_request[$i]['column'];
                    $columns_post = Tools::getValue('columns');
                    $requestColumn = $columns_post[$columnIdx];

                    if (isset($columns[(int)$requestColumn['data']])) {
                        $column_name = $columns[(int)$requestColumn['data']];

                        if ($requestColumn['orderable'] == 'true') {
                            $dir = $order_request[$i]['dir'] === 'asc' ? 'ASC' : 'DESC';
                            $orderBy[] = '`'.$column_name.'` '.$dir;
                        }
                    }
                }
                if (count($orderBy)) {
                    $order = implode(', ', $orderBy);
                }
            }
        }
        return $order;
    }

    public function recurseWkcatProds($categories, $current, $id_category = 1, $id_selected = 1)
    {
        $nb_products = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(DISTINCT cp.id_product) AS nb_products
             FROM `'._DB_PREFIX_.'category_product` cp
             INNER JOIN '._DB_PREFIX_.'product p ON p.id_product = cp.id_product
             WHERE id_category = '.(int)$id_category
        );
        $product_option = $nb_products > 0 ? ' ('.$nb_products.')' : '';

        $tmp = array();
        $tmp['name'] = str_repeat('-', $current['infos']['level_depth'] * 5)
        .Tools::stripslashes($current['infos']['name']).$product_option;
        $tmp['id_category'] = $current['infos']['id_category'];
        array_push($this->options, $tmp);

        if (isset($categories[$id_category])) {
            foreach ($categories[$id_category] as $key => $row) {
                unset($row);
                $this->recurseWkcatProds($categories, $categories[$id_category][$key], $key, $id_selected);
            }
        }
    }

    public static function productLink($id_product, $tab)
    {
        $link = self::classLink();
        $vars_queries = array(
            'id_product' => $id_product,
            'updateproduct' => 1,
        );
        if (!version_compare(_PS_VERSION_, '1.7', '>=')) {
            $vars_queries['key_tab'] = $tab;
        }
        return (
            !version_compare(_PS_VERSION_, '1.7', '>=') ?
            $link->getAdminLink('AdminProducts').'&'.self::implodeKey('&', $vars_queries) :
            $link->getAdminLink('AdminProducts', true, $vars_queries)
        );
    }

    public static function implodeKey($glue = '&', $pieces = array())
    {
        $attributes_str = array();
        foreach ($pieces as $attribute => $value) {
            $attributes_str[] = $attribute.'='.$value;
        }
        return implode($glue, $attributes_str);
    }

    public static function classLink()
    {
        $link = Context::getContext()->link;
        if (empty($link)) {
            $link = new Link();
        }
        return $link;
    }

    public static function getProductAttributes($id_product)
    {
        $pa = Db::getInstance()->executeS(
            'SELECT `id_product_attribute`
             FROM `'._DB_PREFIX_.'product_attribute`
             WHERE `id_product` = '.(int)$id_product
        );
        $ids = array();
        if (!$pa) {
            return $ids;
        }
        foreach ($pa as $product_attribute) {
            $ids[] = (int)$product_attribute['id_product_attribute'];
        }
        return $ids;
    }

    public static function castArray($array)
    {
        return array_map('intval', $array);
    }

    /**
     * Get all attributes groups for a given language order by position
     *
     * @return array Attributes groups
     */
    public static function getAttributesGroups()
    {
        if (!Combination::isFeatureActive()) {
            return array();
        }
        return Db::getInstance()->executeS(
            'SELECT DISTINCT agl.`name`, ag.*, agl.*
             FROM `'._DB_PREFIX_.'attribute_group` ag
             '.Shop::addSqlAssociation('attribute_group', 'ag').'
             LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (
                ag.`id_attribute_group` = agl.`id_attribute_group` AND 
                `id_lang` = '.(int)Context::getContext()->language->id.'
             )
             ORDER BY `position` ASC'
        );
    }

    public static function getAttributesNames($id_product, $id_product_attribute)
    {
        $product = new Product($id_product, false);
        $attributes = $product->getAttributeCombinationsById(
            $id_product_attribute,
            Context::getContext()->language->id
        );
        $combination_name = '';
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $combination_name .= ' '.$attribute['group_name'].' : '.$attribute['attribute_name'].', ';
            }
            $combination_name = rtrim($combination_name, ', ');
        }
        return trim($combination_name);
    }

    public static function deleteAsmStock($id_product, $id_product_attribute = null, $ids_warehouse = null)
    {
        if (!$id_product) {
            return false;
        }
        return Db::getInstance()->execute(
            'DELETE FROM '._DB_PREFIX_.'stock 
             WHERE `id_product` = '.(int)$id_product.
             (!is_null($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').
             (!empty($ids_warehouse) && count($ids_warehouse) ? ' AND `id_warehouse` IN ('.implode(',', $ids_warehouse).')' : '')
        );
    }

    /**
     * For a given product and a given warehouses ID, gets associations
     */
    public static function getCollectionByWarehouses($id_product, $id_product_attribute = null, $ids_warehouse = null)
    {
		$collection = new PrestaShopCollection('StorehouseProductLocation');
        $collection->where('id_product', '=', (int)$id_product);
        if (!is_null($id_product_attribute)) {
            $collection->where('id_product_attribute', '=', (int)$id_product_attribute);
        }
        if (!empty($ids_warehouse)) {
            $collection->where('id_warehouse', 'in', $ids_warehouse);
        }
        return $collection;
    }

    /*
    * Function to be accessed only if "Wk warehouse management" Module is installed & enabled
    */
    public static function deleteWarehouseQtyByProduct(
		$id_product,
		$id_product_attribute = null,
		$id_warehouse = null
	) {
        if (class_exists('WorkshopAsm')) {
            $products_in_stock = self::productsInStock($id_product, $id_product_attribute, $id_warehouse);
            if ($products_in_stock) {
                // Instanciate stock manager
                foreach ($products_in_stock as $stock) {
                    $qty = (int)$stock['physical_quantity'];
                    // Delete stock trace
                    if (self::deleteAsmStock($id_product, $id_product_attribute, array($id_warehouse))) {
                        // Remove quantity also from shop stock available table
                        $qty *= -1;
                        // Use my own function instead of Prestashop! we don't need accessing
                        // the actionUpdateQuantity hook to sync warehouses quantities because we know the warehouse
                        self::updateQuantity(
                            $id_product,
                            $id_product_attribute,
                            $qty,
                            Context::getContext()->shop->id,
                            true// Add movement
                        );
                    }
                }
                self::updatePhysicalProductAvailableQuantity($id_product);
            }
        }
    }

    public static function updatePhysicalProductAvailableQuantity($id_product, $id_shop = null)
    {
        if (empty($id_shop)) {
            $id_shop = Context::getContext()->shop->id;
        }
        if (class_exists('PrestaShop\PrestaShop\Adapter\StockManager')) {
            (new PrestaShop\PrestaShop\Adapter\StockManager())->updatePhysicalProductQuantity(
                (int)$id_shop,
                (int)Configuration::get('PS_OS_ERROR'),
                (int)Configuration::get('PS_OS_CANCELED'),
                (int)$id_product
            );
        }
    }

    public static function productsInStock($id_product, $id_product_attribute = null, $id_warehouse = null)
    {
        if (!(int)$id_product) {
            return false;
        }
        $result = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'stock
             WHERE `id_product` = '.(int)$id_product.
             (!is_null($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').
             (!is_null($id_warehouse) ? ' AND `id_warehouse` = '.(int)$id_warehouse : '')
        );
        return (is_array($result) && !empty($result) ? $result : false);
    }

    /**
     * For a given id_product and id_product_attribute sets the quantity available
     * This function is the same as copied from \src\Core\Stock\StockManager.php
     * We copied and execute this function from here to avoid executing the "actionUpdateQuantity" hook
     * @return bool
     */
    public static function updateQuantity(
        $id_product,
        $id_product_attribute,
        $delta_quantity,
        $id_shop = null,
        $add_movement = true,
        $params = array()
    ) {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }
        $product = new Product((int)$id_product);
        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        // @TODO We should call the needed classes with the Symfony dependency injection instead of the Homemade Service Locator
        $serviceLocator = new PrestaShop\PrestaShop\Adapter\ServiceLocator();
        $stockManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Stock\\StockManager');
        $packItemsManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\Product\\PackItemsManager');
        $cacheManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\CacheManager');

        $availableStockManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\StockManager');
        $stockAvailable = $availableStockManager->getStockAvailableByProduct($product, $id_product_attribute, $id_shop);

        // Update quantity of the pack products
        if ($packItemsManager->isPack($product)) {
            // The product is a pack
            $stockManager->updatePackQuantity($product, $stockAvailable, $delta_quantity, $id_shop);
        } else {
            // The product is not a pack
            $stockAvailable->quantity = $stockAvailable->quantity + $delta_quantity;
            $stockAvailable->update();

            // Decrease case only: the stock of linked packs should be decreased too.
            if ($delta_quantity < 0) {
                // The product is not a pack, but the product combination is part of a pack (use of isPacked, not isPack)
                if ($packItemsManager->isPacked($product, $id_product_attribute)) {
                    $stockManager->updatePacksQuantityContainingProduct($product, $id_product_attribute, $stockAvailable, $id_shop);
                }
            }
        }

        // Prepare movement and save it
        if (true === $add_movement && 0 != $delta_quantity) {
            $stockManager->saveMovement($product->id, $id_product_attribute, $delta_quantity, $params);
        }
        $cacheManager->clean('StockAvailable::getQuantityAvailableByProduct_'.(int)$product->id.'*');
    }

    public static function removeFromProductLocation(
        $id_product,
        $id_product_attribute,
        $id_warehouse_from,
        $remove_stock = true
    ) {
        Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'warehouse_product_location`
             WHERE `id_product` = '.(int)$id_product.'
             '.(!empty($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').' 
             AND `id_warehouse` = '.(int)$id_warehouse_from
        );
        if ($remove_stock) {
            self::removeStock($id_product, $id_product_attribute, $id_warehouse_from);
        }
    }

    public static function removeStock($id_product, $id_product_attribute, $id_warehouse = null)
    {
        return Db::getInstance()->execute(
            'DELETE FROM '._DB_PREFIX_.'stock 
             WHERE `id_product` = '.(int)$id_product.' 
             '.(!empty($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').' 
             '.(!empty($id_warehouse) ? ' AND id_warehouse = '.(int)$id_warehouse : '')
        );
    }

    public static function getInfosProductStock($id_product, $id_product_attribute, $id_warehouse = null)
    {
        $query = new DbQuery();
        $query->select('s.physical_quantity as quantity, s.price_te');
        $query->from('stock', 's');
        $query->where('s.id_product = '.(int)$id_product);
        $query->where('s.id_product_attribute = '.(int)$id_product_attribute);
        if (!empty($id_warehouse)) {
            $query->where('s.id_warehouse = '.(int)$id_warehouse);
        }
        return Db::getInstance()->getRow($query);
    }

    public static function transferBetweenWarehouses(
        $id_product,
        $id_product_attribute,
        $id_warehouse_from,
        $id_warehouse_to
    ) {
        if ($id_warehouse_from == $id_warehouse_to) {
            return false;
        }
        // Check if the given warehouses are available
        $warehouse_from = new StoreHouse($id_warehouse_from);
        $warehouse_to = new StoreHouse($id_warehouse_to);
        if (!Validate::isLoadedObject($warehouse_from) || !Validate::isLoadedObject($warehouse_to)) {
            return false;
        }
        // Get quantity and price
        $stock_from = self::getInfosProductStock($id_product, $id_product_attribute, $id_warehouse_from);
        if (!$stock_from) {
            return false;
        }

        $stock_manager = new WorkshopAsm();

        // Remove warehouse_from
        $stock_manager->removeProduct(
            $id_product,
            $id_product_attribute,
            $warehouse_from,
            $stock_from['quantity'],
            true
        );

        // Add in warehouse_to
        $price = $stock_from['price_te'];
        if ($price == 0.000000) {
            $price = Product::getPriceStatic(
                $id_product,
                false,
                $id_product_attribute,
                6,
                null,
                false,
                false
            );
        }
        $price = str_replace(',', '.', $price);
        if (!Validate::isPrice($price)) {
            $price = 0.000001;
        }

        // convert product price to destination warehouse currency if needed
        if ($warehouse_from->id_currency != $warehouse_to->id_currency) {
            // First convert price to the default currency
            $price_converted_to_default_currency = Tools::convertPrice($price, $warehouse_from->id_currency, false);
            // Convert the new price from default currency to needed currency
            $price = Tools::convertPrice($price_converted_to_default_currency, $warehouse_to->id_currency, true);
        }

		$stock_manager->addProduct(
			$id_product,
			$id_product_attribute,
			$warehouse_to,
			$stock_from['quantity'],
			$price
		);
        return true;
    }
}
