<?php
/**
 *  Order Fees Shipping
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_PS_PRICE_COMPUTE_PRECISION_')) {
    define('_PS_PRICE_COMPUTE_PRECISION_', _PS_PRICE_DISPLAY_PRECISION_);
}

class ShippingRule extends ObjectModel
{
    public $id;
    public $name;
    public $id_customer;
    public $date_from;
    public $date_to;
    public $time_from;
    public $time_to;
    public $priority = 1;
    public $minimum_amount;
    public $minimum_amount_tax;
    public $minimum_amount_currency;
    public $minimum_amount_restriction;
    public $maximum_amount;
    public $maximum_amount_tax;
    public $maximum_amount_currency;
    public $maximum_amount_restriction;
    public $country_restriction;
    public $zone_restriction;
    public $state_restriction;
    public $city_restriction;
    public $carrier_restriction;
    public $group_restriction;
    public $of_shipping_rule_restriction;
    public $product_restriction;
    public $shop_restriction;
    public $dimension_restriction;
    public $zipcode_restriction;
    public $package_restriction;
    public $gender_restriction;
    public $nb_supplier_min;
    public $nb_supplier_max;
    public $type;
    public $percent;
    public $amount;
    public $formula;
    public $currency;
    public $tax_rules_group = 0;
    public $product;
    public $quantity_per_product = 0;
    public $active = 1;
    public $date_add;
    public $date_upd;
    
    public $cart = null;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'of_shipping_rule',
        'primary' => 'id_of_shipping_rule',
        'fields' => array(
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_from' => array('type' => self::TYPE_STRING, 'validate' => 'isDate', 'allow_null' => true),
            'date_to' => array('type' => self::TYPE_STRING, 'validate' => 'isDate', 'allow_null' => true),
            'time_from' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => true),
            'time_to' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'allow_null' => true),
            'priority' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'minimum_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'minimum_amount_tax' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'minimum_amount_currency' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'minimum_amount_restriction' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'maximum_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'maximum_amount_tax' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'maximum_amount_currency' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'maximum_amount_restriction' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'country_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'zone_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'state_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'city_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'carrier_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'group_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'of_shipping_rule_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'product_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'shop_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'dimension_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'zipcode_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'package_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'gender_restriction' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'nb_supplier_min' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' => true),
            'nb_supplier_max' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' => true),
            'type' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'percent' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'formula' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255),
            'tax_rules_group' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'currency' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'product' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'quantity_per_product' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        ),
    );
    
    const IS_NONE = 0;
    const IS_FREE_SHIPPING = 1;
    const IS_PERCENT = 2;
    const IS_AMOUNT = 4;
    const IS_FORMULA = 8;
    const IS_CARRIER = 16;
    const IS_WEIGHT = 1024;
    
    const ORDER = 0;
    const PRODUCTS = 1;
    
    const PERCENT_PRODUCTS = -2;
    
    const BASIC_SHIPPING_MERGE = 32;
    const BASIC_SHIPPING_RULE = 64;
    const BASIC_SHIPPING_BASE = 128;
    
    const APPLY_IF_LEAST = 256;
    const APPLY_IF_ALL = 512;
    
    protected static $math_parser = null;

    /**
     * @see ObjectModel::update()
     */
    public function add($autodate = true, $null_values = false)
    {
        (bool)$null_values;
        
        return parent::add($autodate, true);
    }
    
    /**
     * @see ObjectModel::update()
     */
    public function update($null_values = false)
    {
        (bool)$null_values;
        
        return parent::update(true);
    }

    /**
     * @see ObjectModel::delete()
     */
    public function delete()
    {
        if (!parent::delete()) {
            return false;
        }
        
        // Country restriction
        $r = Db::getInstance()->delete(
            'of_shipping_rule_country',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        
        // Zone restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_zone',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        
        // State restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_state',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );

        // Carrier restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_carrier',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        
        // Groups restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_group',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        
        // Shop restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_shop',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        
        // Combination restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_combination',
            '`id_of_shipping_rule_1` = ' . (int)$this->id . ' OR `id_of_shipping_rule_2` = ' . (int)$this->id
        );
        
        // Products restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_product_rule_group',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_product_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule`.`id_product_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule_group`.`id_product_rule_group`)'
        );
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_product_rule_value',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule_value`.`id_product_rule`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule`.`id_product_rule`)'
        );

        // Dimension restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_dimension_rule_group',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_dimension_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_dimension_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_dimension_rule`.`id_dimension_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_dimension_rule_group`.`id_dimension_rule_group`)'
        );
        
        // City restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_city_rule_group',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_city_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_city_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_city_rule`.`id_city_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_city_rule_group`.`id_city_rule_group`)'
        );

        // Zipcode restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_zipcode_rule_group',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_zipcode_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_zipcode_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_zipcode_rule`.`id_zipcode_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_zipcode_rule_group`.`id_zipcode_rule_group`)'
        );

        // Package restriction
        Db::getInstance()->delete(
            'of_shipping_rule_package_rule_group',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        Db::getInstance()->delete(
            'of_shipping_rule_package_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_package_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_package_rule`.`id_package_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_package_rule_group`.`id_package_rule_group`)'
        );
        
        // Title restriction
        $r &= Db::getInstance()->delete(
            'of_shipping_rule_gender',
            '`id_of_shipping_rule` = ' . (int)$this->id
        );
        
        return $r;
    }

    public static function copyConditions($id_shipping_rule_source, $id_shipping_rule_destination)
    {
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_shop` (`id_of_shipping_rule`, `id_shop`)
                (SELECT '.(int)$id_shipping_rule_destination.', id_shop
                    FROM `'._DB_PREFIX_.'of_shipping_rule_shop`
                    WHERE `id_of_shipping_rule` = '.(int)$id_shipping_rule_source.'
                )'
        );
        
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_carrier` (`id_of_shipping_rule`, `id_carrier`)
                (SELECT '.(int)$id_shipping_rule_destination.', id_carrier
                    FROM `'._DB_PREFIX_.'of_shipping_rule_carrier`
                    WHERE `id_of_shipping_rule` = '.(int)$id_shipping_rule_source.'
                )'
        );
        
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_group` (`id_of_shipping_rule`, `id_group`)
                (SELECT '.(int)$id_shipping_rule_destination.', id_group
                    FROM `'._DB_PREFIX_.'of_shipping_rule_group`
                    WHERE `id_of_shipping_rule` = '.(int)$id_shipping_rule_source.'
                )'
        );
        
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_country` (`id_of_shipping_rule`, `id_country`)
            (SELECT '.(int)$id_shipping_rule_destination.', id_country
                FROM `'._DB_PREFIX_.'of_shipping_rule_country`
                WHERE `id_of_shipping_rule` = '.(int)$id_shipping_rule_source.'
            )'
        );
        
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_zone` (`id_of_shipping_rule`, `id_zone`)
            (SELECT '.(int)$id_shipping_rule_destination.', id_zone
                FROM `'._DB_PREFIX_.'of_shipping_rule_zone`
                WHERE `id_of_shipping_rule` = '.(int)$id_shipping_rule_source.'
            )'
        );
        
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_state` (`id_of_shipping_rule`, `id_state`)
            (SELECT '.(int)$id_shipping_rule_destination.', id_state
                FROM `'._DB_PREFIX_.'of_shipping_rule_state`
                WHERE `id_of_shipping_rule` = '.(int)$id_shipping_rule_source.'
            )'
        );
        
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_combination`
                (`id_of_shipping_rule_1`, `id_of_shipping_rule_2`)
                (SELECT '.(int)$id_shipping_rule_destination.',
                    IF(id_of_shipping_rule_1 != '.(int)$id_shipping_rule_source.',
                    id_of_shipping_rule_1, id_of_shipping_rule_2)
                    FROM `'._DB_PREFIX_.'of_shipping_rule_combination`
                    WHERE `id_of_shipping_rule_1` = '.(int)$id_shipping_rule_source.'
                        OR `id_of_shipping_rule_2` = '.(int)$id_shipping_rule_source.'
                )'
        );

        $products_rules_group_source = Db::getInstance()->ExecuteS(
            'SELECT id_product_rule_group,quantity
                FROM `'._DB_PREFIX_.'of_shipping_rule_product_rule_group`
                WHERE `id_of_shipping_rule` = '.(int)$id_shipping_rule_source
        );

        foreach ($products_rules_group_source as $product_rule_group_source) {
            Db::getInstance()->execute(
                'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_product_rule_group` (`id_of_shipping_rule`, `quantity`)
                VALUES ('.(int)$id_shipping_rule_destination.','.(int)$product_rule_group_source['quantity'].')'
            );
            
            $id_product_rule_group_destination = Db::getInstance()->Insert_ID();

            $products_rules_source = Db::getInstance()->ExecuteS(
                'SELECT id_product_rule,type
                    FROM `'._DB_PREFIX_.'of_shipping_rule_product_rule`
                    WHERE `id_product_rule_group` = '.(int)$product_rule_group_source['id_product_rule_group'].' '
            );

            foreach ($products_rules_source as $product_rule_source) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_product_rule` (`id_product_rule_group`, `type`)
                    VALUES ('.(int)$id_product_rule_group_destination.',"'.pSQL($product_rule_source['type']).'")'
                );
                
                $id_product_rule_destination = Db::getInstance()->Insert_ID();

                $products_rules_values_source = Db::getInstance()->ExecuteS(
                    'SELECT id_item
                        FROM `'._DB_PREFIX_.'of_shipping_rule_product_rule_value`
                        WHERE `id_product_rule` = '.(int)$product_rule_source['id_product_rule']
                );

                foreach ($products_rules_values_source as $product_rule_value_source) {
                    Db::getInstance()->execute(
                        'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_product_rule_value` (`id_product_rule`, `id_item`)
                        VALUES ('.(int)$id_product_rule_destination.','.(int)$product_rule_value_source['id_item'].')'
                    );
                }
            }
        }
        
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_gender` (`id_of_shipping_rule`, `id_gender`)
            (SELECT '.(int)$id_shipping_rule_destination.', id_gender
                FROM `'._DB_PREFIX_.'of_shipping_rule_gender`
                WHERE `id_of_shipping_rule` = '.(int)$id_shipping_rule_source.'
            )'
        );
    }

    public function getProductRuleGroups()
    {
        if (!Validate::isLoadedObject($this) || $this->product_restriction == 0) {
            return array();
        }

        $productRuleGroups = array();
        
        $result = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_product_rule_group
                WHERE id_of_shipping_rule = ' . (int)$this->id
        );
        
        foreach ($result as $row) {
            if (!isset($productRuleGroups[$row['id_product_rule_group']])) {
                $productRuleGroups[$row['id_product_rule_group']] = array(
                    'id_product_rule_group' => $row['id_product_rule_group'],
                    'quantity' => $row['quantity']
                );
            }
            
            $productRuleGroups[$row['id_product_rule_group']]['product_rules'] = $this->getProductRules(
                $row['id_product_rule_group']
            );
        }
        
        return $productRuleGroups;
    }

    public function getProductRules($id_product_rule_group)
    {
        if (!Validate::isLoadedObject($this) || $this->product_restriction == 0) {
            return array();
        }

        $productRules = array();
        
        $results = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_product_rule pr
                LEFT JOIN '._DB_PREFIX_.'of_shipping_rule_product_rule_value prv
                    ON pr.id_product_rule = prv.id_product_rule
                WHERE pr.id_product_rule_group = ' . (int)$id_product_rule_group
        );
        
        foreach ($results as $row) {
            if (!isset($productRules[$row['id_product_rule']])) {
                $productRules[$row['id_product_rule']] = array(
                    'type' => $row['type'],
                    'values' => array()
                );
            }
            
            $productRules[$row['id_product_rule']]['values'][] = $row['id_item'];
        }
        
        return $productRules;
    }
    
    public function getDimensionRuleGroups()
    {
        if (!Validate::isLoadedObject($this) || $this->dimension_restriction == 0) {
            return array();
        }

        $dimensionRuleGroups = array();
        
        $result = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_dimension_rule_group
                WHERE id_of_shipping_rule = ' . (int)$this->id
        );
        
        foreach ($result as $row) {
            if (!isset($dimensionRuleGroups[$row['id_dimension_rule_group']])) {
                $dimensionRuleGroups[$row['id_dimension_rule_group']] = array(
                    'id_dimension_rule_group' => $row['id_dimension_rule_group'],
                    'base' => $row['base']
                );
            }
            
            $dimensionRuleGroups[$row['id_dimension_rule_group']]['dimension_rules'] = $this->getDimensionRules(
                $row['id_dimension_rule_group']
            );
        }
        
        return $dimensionRuleGroups;
    }
    
    public function getDimensionRules($id_dimension_rule_group)
    {
        if (!Validate::isLoadedObject($this) || $this->dimension_restriction == 0) {
            return array();
        }

        $dimensionRules = array();
        
        $results = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_dimension_rule pr
                WHERE pr.id_dimension_rule_group = ' . (int)$id_dimension_rule_group
        );
        
        foreach ($results as $row) {
            $dimensionRules[$row['id_dimension_rule']] = array(
                'type' => $row['type'],
                'operator' => $row['operator'],
                'value' => $row['value']
            );
        }
        
        return $dimensionRules;
    }
    
    public function getCityRuleGroups()
    {
        if (!Validate::isLoadedObject($this) || $this->city_restriction == 0) {
            return array();
        }

        $cityRuleGroups = array();
        
        $result = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_city_rule_group
                WHERE id_of_shipping_rule = ' . (int)$this->id
        );
        
        foreach ($result as $row) {
            if (!isset($cityRuleGroups[$row['id_city_rule_group']])) {
                $cityRuleGroups[$row['id_city_rule_group']] = array(
                    'id_city_rule_group' => $row['id_city_rule_group']
                );
            }
            
            $cityRuleGroups[$row['id_city_rule_group']]['city_rules'] = $this->getCityRules(
                $row['id_city_rule_group']
            );
        }
        
        return $cityRuleGroups;
    }
    
    public function getCityRules($id_city_rule_group)
    {
        if (!Validate::isLoadedObject($this) || $this->city_restriction == 0) {
            return array();
        }

        $cityRules = array();
        
        $results = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_city_rule pr
                WHERE pr.id_city_rule_group = ' . (int)$id_city_rule_group
        );
        
        foreach ($results as $row) {
            $cityRules[$row['id_city_rule']] = array(
                'type' => $row['type'],
                'value' => $row['value']
            );
        }
        
        return $cityRules;
    }
    
    public function getZipcodeRuleGroups()
    {
        if (!Validate::isLoadedObject($this) || $this->zipcode_restriction == 0) {
            return array();
        }

        $zipcodeRuleGroups = array();
        
        $result = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_zipcode_rule_group
                WHERE id_of_shipping_rule = ' . (int)$this->id
        );
        
        foreach ($result as $row) {
            if (!isset($zipcodeRuleGroups[$row['id_zipcode_rule_group']])) {
                $zipcodeRuleGroups[$row['id_zipcode_rule_group']] = array(
                    'id_zipcode_rule_group' => $row['id_zipcode_rule_group']
                );
            }
            
            $zipcodeRuleGroups[$row['id_zipcode_rule_group']]['zipcode_rules'] = $this->getZipcodeRules(
                $row['id_zipcode_rule_group']
            );
        }
        
        return $zipcodeRuleGroups;
    }
    
    public function getZipcodeRules($id_zipcode_rule_group)
    {
        if (!Validate::isLoadedObject($this) || $this->zipcode_restriction == 0) {
            return array();
        }

        $zipcodeRules = array();
        
        $results = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_zipcode_rule pr
                WHERE pr.id_zipcode_rule_group = ' . (int)$id_zipcode_rule_group
        );
        
        foreach ($results as $row) {
            $zipcodeRules[$row['id_zipcode_rule']] = array(
                'type' => $row['type'],
                'operator' => $row['operator'],
                'value' => $row['value']
            );
        }
        
        return $zipcodeRules;
    }
    
    public function getPackageRuleGroups()
    {
        if (!Validate::isLoadedObject($this) || $this->package_restriction == 0) {
            return array();
        }

        $packageRuleGroups = array();
        
        $result = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_package_rule_group
                WHERE id_of_shipping_rule = ' . (int)$this->id
        );
        
        foreach ($result as $row) {
            if (!isset($packageRuleGroups[$row['id_package_rule_group']])) {
                $packageRuleGroups[$row['id_package_rule_group']] = array(
                    'id_package_rule_group' => $row['id_package_rule_group'],
                    'unit' => $row['unit'],
                    'unit_weight' => $row['unit_weight'],
                    'ratio' => $row['ratio']
                );
            }
            
            $packageRuleGroups[$row['id_package_rule_group']]['package_rules'] = $this->getPackageRules(
                $row['id_package_rule_group']
            );
        }
        
        return $packageRuleGroups;
    }

    public function getPackageRules($id_package_rule_group)
    {
        if (!Validate::isLoadedObject($this) || $this->package_restriction == 0) {
            return array();
        }

        $packageRules = array();
        
        $results = Db::getInstance()->executeS(
            'SELECT *
                FROM '._DB_PREFIX_.'of_shipping_rule_package_rule pr
                WHERE pr.id_package_rule_group = ' . (int)$id_package_rule_group . '
                ORDER BY pr.range_start ASC'
        );
        
        foreach ($results as $row) {
            $packageRules[$row['id_package_rule']] = array(
                'range_start' => $row['range_start'],
                'range_end' => $row['range_end'],
                'range_start' => $row['range_start'],
                'round' => $row['round'],
                'divider' => $row['divider'],
                'currency' => $row['currency'],
                'tax' => $row['tax'],
                'value' => $row['value']
            );
        }
        
        return $packageRules;
    }

    public function check()
    {
        $context = Context::getContext();
        $cart = $this->cart;
        $currency = Currency::getCurrencyInstance((int)$cart->id_currency);
        
        // Main
        if (!$this->active
            || ($this->date_from && strtotime($this->date_from) > time())
            || ($this->date_to && strtotime($this->date_to) < time())
            || ($this->id_customer && $cart->id_customer != $this->id_customer)
            || (($this->time_from && date('H:i:s') < $this->time_from)
            || ($this->time_to && date('H:i:s') > $this->time_to))
        ) {
            return false;
        }
        
        $results = Hook::exec('actionShippingRuleCheck', array(
            'object' => &$this,
            'context' => &$context,
            'cart' => &$cart
        ), null, true);
        
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null && !$result) {
                    return false;
                }
            }
        }
        
        // Minimum amount
        if ((int)$this->minimum_amount) {
            $minimum_amount = $this->minimum_amount;
            
            if ($this->minimum_amount_currency != $currency->id) {
                $minimum_amount = Tools::convertPriceFull(
                    $minimum_amount,
                    new Currency($this->minimum_amount_currency),
                    $currency
                );
            }
            
            $cart_total = 0;
            
            if ($this->minimum_amount_restriction == self::PRODUCTS) {
                foreach ($this->getProducts() as $product) {
                    $cart_total += Tools::ps_round(
                        $product[$this->minimum_amount_tax ? 'total_wt' : 'total'],
                        (int) $currency->decimals * _PS_PRICE_COMPUTE_PRECISION_
                    );
                }
            } else {
                $cart_total = $cart->getOrderTotal($this->minimum_amount_tax, Cart::BOTH_WITHOUT_SHIPPING);
                
                if (version_compare(_PS_VERSION_, '1.7.6.0', '<')) {
                    $cart_total -= $cart->getOrderTotal($this->minimum_amount_tax, Cart::ONLY_DISCOUNTS);
                }
            }

            if ($cart_total < $minimum_amount) {
                return false;
            }
        }

        // Maximum amount
        if ((int)$this->maximum_amount) {
            $maximum_amount = $this->maximum_amount;
            
            if ($this->maximum_amount_currency != $currency->id) {
                $maximum_amount = Tools::convertPriceFull(
                    $maximum_amount,
                    new Currency($this->maximum_amount_currency),
                    $currency
                );
            }
            
            $cart_total = 0;
            
            if ($this->maximum_amount_restriction == self::PRODUCTS) {
                foreach ($this->getProducts() as $product) {
                    $cart_total += Tools::ps_round(
                        $product[$this->maximum_amount_tax ? 'total_wt' : 'total'],
                        (int) $currency->decimals * _PS_PRICE_COMPUTE_PRECISION_
                    );
                }
            } else {
                $cart_total = $cart->getOrderTotal($this->maximum_amount_tax, Cart::BOTH_WITHOUT_SHIPPING);
                
                if (version_compare(_PS_VERSION_, '1.7.6.0', '<')) {
                    $cart_total -= $cart->getOrderTotal($this->maximum_amount_tax, Cart::ONLY_DISCOUNTS);
                }
            }

            if ($cart_total >= $maximum_amount) {
                return false;
            }
        }
        
        // City restriction
        if ($this->city_restriction) {
            if (!$cart->id_address_delivery) {
                return false;
            }
            
            if (Module::isEnabled('cityselect') && !count($this->getCityRuleGroups())) {
                $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                    'SELECT src.id_of_shipping_rule
                        FROM ' . _DB_PREFIX_ . 'of_shipping_rule_city src
                        WHERE src.id_of_shipping_rule = ' . (int) $this->id . '
                            AND src.name = (
                                SELECT a.city
                                FROM ' . _DB_PREFIX_ . 'address a
                                WHERE a.id_address = ' . (int) $cart->id_address_delivery . '
                                LIMIT 1
                            )'
                );

                if (!$id_of_shipping_rule) {
                    return false;
                }
            } else {
                $address = Db::getInstance()->getRow(
                    'SELECT a.id_country, a.city
                        FROM '._DB_PREFIX_.'address a
                        WHERE a.id_address = ' . (int)$cart->id_address_delivery
                );

                $id_country = $address['id_country'];
                $city = $this->formatCity($address['city']);

                $city_rule_groups = $this->getCityRuleGroups();

                foreach (array_keys($city_rule_groups) as $id_city_rule_group) {
                    $city_rules = $this->getCityRules($id_city_rule_group);

                    foreach ($city_rules as $city_rule) {
                        if ($city_rule['type'] != '' && $city_rule['type'] != $id_country) {
                            continue;
                        }

                        $values = array_map(array($this, 'formatCity'), explode(',', $city_rule['value']));

                        if (in_array($city, $values)) {
                            continue 2;
                        }
                    }

                    return false;
                }
            }
        }

        // Zipcode restriction
        if ($this->zipcode_restriction) {
            $id_country = 0;
            $postcode = '';

            if ($cart->id_address_delivery) {
                $address = Db::getInstance()->getRow(
                    'SELECT a.id_country, a.postcode
                        FROM '._DB_PREFIX_.'address a
                        WHERE a.id_address = ' . (int)$cart->id_address_delivery
                );

                $id_country = $address['id_country'];
                $postcode = $address['postcode'];
            } elseif (isset($context->customer->postcode)) {
                $id_country = $context->customer->geoloc_id_country;
                $postcode = $context->customer->postcode;
            }

            $postcode = preg_replace('/([\s-]+)/', '', Tools::strtolower($postcode));

            if (!$postcode) {
                return false;
            }

            $zipcode_rule_groups = $this->getZipcodeRuleGroups();

            foreach (array_keys($zipcode_rule_groups) as $id_zipcode_rule_group) {
                $zipcode_rules = $this->getZipcodeRules($id_zipcode_rule_group);

                foreach ($zipcode_rules as $zipcode_rule) {
                    if ($zipcode_rule['type'] != '' && $zipcode_rule['type'] != $id_country) {
                        continue;
                    }

                    $operator = $zipcode_rule['operator'];
                    $values = explode(',', preg_replace('/([\s-]+|,+$)/', '', Tools::strtolower($zipcode_rule['value'])));

                    if ($operator == '!=') {
                        if (!in_array($postcode, $values)) {
                            continue 2;
                        }
                    }

                    foreach ($values as $value) {
                        if ($operator == 'begin') {
                            if (strpos($postcode, $value) === 0) {
                                continue 3;
                            }
                        } elseif ($operator == 'end') {
                            if (strrpos($postcode, $value) + Tools::strlen($value) === Tools::strlen($postcode)) {
                                continue 3;
                            }
                        } else {
                            $cmp = ($postcode > $value ? 1 : ($postcode === $value ? 0 : - 1));

                            if ($operator == '=' && $cmp == 0) {
                                continue 3;
                            } elseif ($operator == '>' && $cmp > 0) {
                                continue 3;
                            } elseif ($operator == '<' && $cmp < 0) {
                                continue 3;
                            } elseif ($operator == '>=' && $cmp >= 0) {
                                continue 3;
                            } elseif ($operator == '<=' && $cmp <= 0) {
                                continue 3;
                            }
                        }
                    }
                }

                return false;
            }
        }

        // Dimension restriction
        if ($this->dimension_restriction) {
            $dimensions_available = array('width', 'height', 'depth', 'weight', 'volume');
            $dimensions_products = array(
                'product' => array(),
                'product_quantity' => array(),
                'all' => array()
            );

            $products = $this->getProducts();

            foreach ($products as $product) {
                foreach ($dimensions_available as $dim) {
                    if (isset($product[$dim])) {
                        $dimensions_products['product'][$dim][] = $product[$dim];

                        if (!isset($dimensions_products['all'][$dim])) {
                            $dimensions_products['all'][$dim][0] = 0;
                            $dimensions_products['product_quantity'][$dim][0] = 0;
                        }

                        $dimensions_products['all'][$dim][0] += ($product[$dim] * $product['cart_quantity']);
                        $dimensions_products['product_quantity'][$dim][0] = max($dimensions_products['product_quantity'][$dim][0], $product[$dim] * $product['cart_quantity']);
                    }
                }

                // Volume
                $volume = $product['height'] * $product['width'] * $product['depth'];

                $dimensions_products['product']['volume'][] = $volume;

                if (!isset($dimensions_products['all']['volume'])) {
                    $dimensions_products['all']['volume'][0] = 0;
                    $dimensions_products['product_quantity']['volume'][0] = 0;
                }

                $dimensions_products['all']['volume'][0] += ($volume * $product['cart_quantity']);
                $dimensions_products['product_quantity']['volume'][0] = max($dimensions_products['product_quantity']['volume'][0], $volume * $product['cart_quantity']);
                
                // Combined (L + W + H)
                $combined = $product['height'] + $product['width'] + $product['depth'];

                $dimensions_products['product']['combined'][] = $combined;

                if (!isset($dimensions_products['all']['combined'])) {
                    $dimensions_products['all']['combined'][0] = 0;
                    $dimensions_products['product_quantity']['combined'][0] = 0;
                }

                $dimensions_products['all']['combined'][0] += ($combined * $product['cart_quantity']);
                $dimensions_products['product_quantity']['combined'][0] = max($dimensions_products['product_quantity']['combined'][0], $combined * $product['cart_quantity']);
                
                // Combined (L + 2W + 2H)
                $combined_girth = (2 * $product['height']) + (2 * $product['width']) + $product['depth'];

                $dimensions_products['product']['combined_girth'][] = $combined_girth;

                if (!isset($dimensions_products['all']['combined_girth'])) {
                    $dimensions_products['all']['combined_girth'][0] = 0;
                    $dimensions_products['product_quantity']['combined_girth'][0] = 0;
                }

                $dimensions_products['all']['combined_girth'][0] += ($combined_girth * $product['cart_quantity']);
                $dimensions_products['product_quantity']['combined_girth'][0] = max($dimensions_products['product_quantity']['combined_girth'][0], $combined_girth * $product['cart_quantity']);
            }

            if (!empty($dimensions_products['product'])) {
                $dimension_rule_groups = $this->getDimensionRuleGroups();

                foreach ($dimension_rule_groups as $id_dimension_rule_group => $dimension_rule_group) {
                    $base = $dimension_rule_group['base'];
                    $dimension_rules = $this->getDimensionRules($id_dimension_rule_group);

                    foreach ($dimension_rules as $dimension_rule) {
                        $type = $dimension_rule['type'];
                        $operator = $dimension_rule['operator'];
                        $values = explode(',', preg_replace('/(\s+)/', '', Tools::strtolower($dimension_rule['value'])));

                        foreach ($values as $value) {
                            $dimensions = $dimensions_products[$base][$type];
                            $count_matching = 0;

                            foreach ($dimensions as $dimension) {
                                $cmp = ($dimension > $value ? 1 : ($dimension == $value ? 0 : - 1));

                                if ($operator == '=' && $cmp == 0) {
                                    $count_matching++;
                                } elseif ($operator == '>' && $cmp > 0) {
                                    $count_matching++;
                                } elseif ($operator == '<' && $cmp < 0) {
                                    $count_matching++;
                                } elseif ($operator == '>=' && $cmp >= 0) {
                                    $count_matching++;
                                } elseif ($operator == '<=' && $cmp <= 0) {
                                    $count_matching++;
                                } elseif ($operator == '!=' && $cmp != 0) {
                                    $count_matching++;
                                }
                            }
                            
                            if ($this->type & self::APPLY_IF_ALL) {
                                if ($count_matching == count($dimensions)) {
                                    continue 3;
                                }
                            } elseif ($count_matching > 0) {
                                continue 3;
                            }
                        }
                    }

                    return false;
                }
            }
        }

        // Product
        if (!$this->restrictionsProducts()) {
            return false;
        }

        // Group
        if ($this->group_restriction) {
            $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                'SELECT srg.id_of_shipping_rule
                    FROM ' . _DB_PREFIX_ . 'of_shipping_rule_group srg
                    WHERE srg.id_of_shipping_rule = ' . (int) $this->id . '
                        AND srg.id_group ' . (
                            $cart->id_customer ? 'IN (
                                SELECT cg.id_group
                                FROM ' . _DB_PREFIX_ . 'customer_group cg
                                WHERE cg.id_customer = ' . (int) $cart->id_customer . '
                            )' : '= ' . (int) Configuration::get('PS_UNIDENTIFIED_GROUP')
                        )
            );
            
            if (!$id_of_shipping_rule) {
                return false;
            }
        }

        // Country
        if ($this->country_restriction) {
            if (!$cart->id_address_delivery) {
                if (empty($context->country->id)) {
                    return false;
                }
                
                $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                    'SELECT src.id_of_shipping_rule
                        FROM ' . _DB_PREFIX_ . 'of_shipping_rule_country src
                        WHERE src.id_of_shipping_rule = ' . (int) $this->id . '
                            AND src.id_country = ' . (int)$context->country->id
                );
            } else {
                $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                    'SELECT src.id_of_shipping_rule
                        FROM ' . _DB_PREFIX_ . 'of_shipping_rule_country src
                        WHERE src.id_of_shipping_rule = ' . (int) $this->id . '
                            AND src.id_country = (
                                SELECT a.id_country
                                FROM ' . _DB_PREFIX_ . 'address a
                                WHERE a.id_address = ' . (int) $cart->id_address_delivery . '
                                LIMIT 1
                            )'
                );
            }
            
            if (!$id_of_shipping_rule) {
                return false;
            }
        }
        
        // Zone
        if ($this->zone_restriction) {
            if (!$cart->id_address_delivery) {
                if (empty($context->country->id_zone)) {
                    return false;
                }
                
                $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                    'SELECT srz.id_of_shipping_rule
                        FROM ' . _DB_PREFIX_ . 'of_shipping_rule_zone srz
                        WHERE srz.id_of_shipping_rule = ' . (int) $this->id . '
                            AND srz.id_zone = ' . (int)$context->country->id_zone
                );
            } else {
                $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                    'SELECT srz.id_of_shipping_rule
                        FROM ' . _DB_PREFIX_ . 'of_shipping_rule_zone srz
                        INNER JOIN ' . _DB_PREFIX_ . 'address a
                                    ON a.id_address = ' . (int) $cart->id_address_delivery . '
                                AND srz.id_of_shipping_rule = ' . (int) $this->id . '
                        LEFT JOIN ' . _DB_PREFIX_ . 'country c
                            ON c.id_country = a.id_country
                                AND c.id_zone = srz.id_zone
                        LEFT JOIN ' . _DB_PREFIX_ . 'state s
                            ON s.id_state = a.id_state
                                AND s.id_zone = srz.id_zone
                        WHERE c.id_zone IS NOT NULL OR s.id_zone IS NOT NULL'
                );
            }
            
            if (!$id_of_shipping_rule) {
                return false;
            }
        }
        
        // State
        if ($this->state_restriction) {
            if (!$cart->id_address_delivery) {
                return false;
            }
            
            $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                'SELECT srs.id_of_shipping_rule
                    FROM ' . _DB_PREFIX_ . 'of_shipping_rule_state srs
                    WHERE srs.id_of_shipping_rule = ' . (int) $this->id . '
                        AND srs.id_state = (
                            SELECT a.id_state
                            FROM ' . _DB_PREFIX_ . 'address a
                            WHERE a.id_address = ' . (int) $cart->id_address_delivery . '
                            LIMIT 1
                        )'
            );
            
            if (!$id_of_shipping_rule) {
                return false;
            }
        }

        // Shop
        if ($this->shop_restriction && $cart->id_shop && Shop::isFeatureActive()) {
            $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                'SELECT srs.id_of_shipping_rule
                    FROM ' . _DB_PREFIX_ . 'of_shipping_rule_shop srs
                    WHERE srs.id_of_shipping_rule = ' . (int) $this->id . '
                        AND srs.id_shop = ' . (int) $cart->id_shop
            );
            
            if (!$id_of_shipping_rule) {
                return false;
            }
        }
        
        // Title
        if ($this->gender_restriction) {
            $id_of_shipping_rule = (int) Db::getInstance()->getValue(
                'SELECT srg.id_of_shipping_rule
                    FROM ' . _DB_PREFIX_ . 'of_shipping_rule_gender srg
                    WHERE srg.id_of_shipping_rule = ' . (int) $this->id . '
                        AND srg.id_gender = (
                            SELECT c.id_gender
                            FROM ' . _DB_PREFIX_ . 'customer c
                            WHERE c.id_customer = ' . (int) $cart->id_customer . '
                            LIMIT 1
                        )'
            );
            
            if (!$id_of_shipping_rule) {
                return false;
            }
        }
        
        // Nb Suppliers
        if ($this->nb_supplier_max) {
            $nb_suppliers = (int) Db::getInstance()->getValue(
                'SELECT COUNT(DISTINCT ps.id_supplier)
                    FROM ' . _DB_PREFIX_ . 'cart_product cp
                    INNER JOIN ' . _DB_PREFIX_ . 'product_supplier ps 
                        ON cp.id_cart = ' . (int)$cart->id . ' AND ps.id_product = cp.id_product'
            );
            
            if ($nb_suppliers < $this->nb_supplier_min || $nb_suppliers > $this->nb_supplier_max) {
                return false;
            }
        }
        
        return true;
    }

    public function value($params)
    {
        $cart = $this->cart;
        $products = $cart->getProducts();
        $value = 0;
        $use_tax = $params['use_tax'];
        
        $cache_id = sprintf('orderfees_shipping_%d_%d_%d_%d', (int)$this->id, (int)$params['id_carrier'], (int)$use_tax, (int)$cart->id);
        
        if (Cache::isStored($cache_id)) {
            return Cache::retrieve($cache_id);
        }
        
        Hook::exec('actionShippingRuleValue', array(
            'object' => &$this,
            'cart' => &$cart,
            'value' => &$value
        ));
        
        if ($this->type & self::IS_PERCENT) {
            // % on the whole order
            if ($this->percent && $this->product == 0) {
                $value += $cart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING) * $this->percent / 100;
            }

            // % on a specific product
            if ($this->percent && $this->product > 0) {
                foreach ($products as $product) {
                    if ($product['id_product'] == $this->product) {
                        $value += ($product['total_wt'] * $this->percent / 100);
                    }
                }
            }

            // % on the selection of products
            if ($this->percent && $this->product == self::PERCENT_PRODUCTS) {
                $selected_products_total = 0;
                $selected_products = $this->restrictionsProducts(true);

                if (is_array($selected_products)) {
                    foreach ($cart->getProducts() as $product) {
                        if (in_array($product['id_product'].'-'.$product['id_product_attribute'], $selected_products)
                            || in_array($product['id_product'].'-0', $selected_products)
                        ) {
                            $selected_products_total += $product['total_wt'];
                        }
                    }
                }

                $value += $selected_products_total * $this->percent / 100;
            }
        }

        // Amount
        if ($this->type & self::IS_AMOUNT) {
            $amount = $this->amount;
            $cart_currency = Currency::getCurrencyInstance((int)$cart->id_currency);
            
            if ($this->currency != $cart_currency->id) {
                $currency = new Currency($this->currency);

                if ($currency->conversion_rate == 0) {
                    $amount = 0;
                } else {
                    $amount /= $currency->conversion_rate;
                }

                $amount *= $cart_currency->conversion_rate;
                $amount = Tools::ps_round($amount, _PS_PRICE_COMPUTE_PRECISION_);
            }
            
            $value += $amount;
        }
        
        // Formula
        if (($this->type & self::IS_FORMULA) && $this->formula) {
            $data = array(
                'total' => $cart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING),
                'shipping' => $params['total'],
                'total_quantity' => 0,
                'quantity' => 0,
                'total_exclude_promo' => 0
            );
            
            $selected_products = $this->restrictionsProducts(true);
        
            if (is_array($selected_products)) {
                $dimensions_available = array('width', 'height', 'depth', 'weight', 'volume');
                $all_products = (count($selected_products) == 0);
                $quantity = 0;
                
                foreach ($cart->getProducts() as $product) {
                    $key = (int)$product['id_product'].'-'.(int)$product['id_product_attribute'];

                    if ($all_products || in_array($key, $selected_products)) {
                        $data['quantity'] += $product['cart_quantity'];
                    }
                    
                    foreach ($dimensions_available as $dim) {
                        if (!isset($data[$dim])) {
                            $data[$dim] = 0;
                        }
                        
                        if (!isset($data[$dim . '_products'])) {
                            $data[$dim . '_products'] = 0;
                        }
                        
                        if (isset($product[$dim])) {
                            $data[$dim] += ($product[$dim] * $product['cart_quantity']);
                            
                            if ($all_products || in_array($key, $selected_products)) {
                                $data[$dim . '_products'] += ($product[$dim] * $product['cart_quantity']);
                            }
                        }
                    }
                    
                    // Volume
                    $volume = $product['height'] * $product['width'] * $product['depth'];
                    
                    $data['volume'] += ($volume * $product['cart_quantity']);
                    
                    if ($all_products || in_array($key, $selected_products)) {
                        $data['volume_products'] += ($volume * $product['cart_quantity']);
                    }
                    
                    // Total
                    $data['total_quantity'] += $product['cart_quantity'];
                    
                    // Exclude special promo
                    if (!$product['reduction_applies']) {
                        $data['total_exclude_promo'] += $product['total'];
                    }
                }
                
                if ($all_products) {
                    $data['weight'] = $cart->getTotalWeight();
                }
            }
            
            $result = self::parse($this->formula, $data);
            
            if (is_numeric($result)) {
                $value = $result;
            }
        }
        
        if ($use_tax) {
            $tax_address_type = Configuration::get('PS_TAX_ADDRESS_TYPE');
            $address = null;

            if ($cart->{$tax_address_type} != null) {
                $address = $cart->{$tax_address_type};
            }

            $value *= (1 + ($this->getTaxesRate($this->tax_rules_group, new Address($address)) / 100));
        }
        
        if ($this->quantity_per_product) {
            $selected_products = $this->restrictionsProducts(true);
        
            if (is_array($selected_products)) {
                $all_products = (count($selected_products) == 0);
                $quantity = 0;
                
                foreach ($cart->getProducts() as $product) {
                    $key = (int)$product['id_product'].'-'.(int)$product['id_product_attribute'];

                    if ($all_products || in_array($key, $selected_products)) {
                        $quantity += $product['cart_quantity'];
                    }
                }

                $value *= $quantity;
            }
        }

        Cache::store($cache_id, $value);
        
        return $value;
    }

    protected function getShippingRuleCombinations()
    {
        $array = array();

        $array['selected'] = Db::getInstance()->executeS(
            'SELECT sr.*, 1 as selected
                FROM '._DB_PREFIX_.'of_shipping_rule sr    
                WHERE sr.id_of_shipping_rule != '.(int)$this->id.'
                AND (
                    sr.of_shipping_rule_restriction = 0
                    OR EXISTS (
                        SELECT 1
                        FROM '._DB_PREFIX_.'of_shipping_rule_combination
                        WHERE sr.id_of_shipping_rule = '._DB_PREFIX_.'of_shipping_rule_combination.id_of_shipping_rule_1
                            AND '.(int)$this->id.' = id_of_shipping_rule_2
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM '._DB_PREFIX_.'of_shipping_rule_combination
                        WHERE sr.id_of_shipping_rule = '._DB_PREFIX_.'of_shipping_rule_combination.id_of_shipping_rule_2
                            AND '.(int)$this->id.' = id_of_shipping_rule_1
                    )
                )
                ORDER BY sr.id_of_shipping_rule'
        );

        $array['unselected'] = Db::getInstance()->executeS(
            'SELECT sr.*, 1 as selected
                FROM '._DB_PREFIX_.'of_shipping_rule sr
                LEFT JOIN '._DB_PREFIX_.'of_shipping_rule_combination src1
                    ON (sr.id_of_shipping_rule = src1.id_of_shipping_rule_1
                        AND src1.id_of_shipping_rule_2 = '.(int)$this->id.')
                LEFT JOIN '._DB_PREFIX_.'of_shipping_rule_combination src2
                    ON (sr.id_of_shipping_rule = src2.id_of_shipping_rule_2
                        AND src2.id_of_shipping_rule_1 = '.(int)$this->id.')
                WHERE sr.of_shipping_rule_restriction = 1
                    AND sr.id_of_shipping_rule != '.(int)$this->id.'
                    AND src1.id_of_shipping_rule_1 IS NULL
                    AND src2.id_of_shipping_rule_1 IS NULL  ORDER BY sr.id_of_shipping_rule'
        );
        
        return $array;
    }
    
    public function isSupportedType($type)
    {
        static $cache_types = array();
        
        if (!isset($cache_types[$type])) {
            $cache_types[$type] = in_array($type, explode(',', Configuration::get('MS_ORDERFEES_SHIPPING_TYPES')));
        }
        
        return $cache_types[$type];
    }
    
    public function restrictions($type, $active = true, $i18n = false)
    {
        $array = array('selected' => array(), 'unselected' => array());

        if (!$this->isSupportedType($type)) {
            return $array;
        }
        
        if ($type == 'city') {
            return $this->restrictionsCity($active);
        }

        $context = Context::getContext();
        $shop_list = '';
        $id_lang = $context->language->id;
        
        if ($type == 'shop') {
            $shops = $context->employee->getAssociatedShops();
            
            if (count($shops)) {
                $shop_list = ' AND t.id_shop IN ('.implode(',', array_map('intval', $shops)).') ';
            }
        }

        if (!Validate::isLoadedObject($this) || $this->{$type.'_restriction'} == 0) {
            $array['selected'] = Db::getInstance()->executeS(
                'SELECT t.*'.($i18n ? ', tl.*' : '').', 1 as selected
                    FROM `'._DB_PREFIX_.pSQL($type).'` t '
                .($i18n ? 'LEFT JOIN `'._DB_PREFIX_.pSQL($type).'_lang` tl
                    ON (t.id_'.pSQL($type).' = tl.id_'.pSQL($type).' AND tl.id_lang = '.(int)$id_lang.')' : '').'
                WHERE 1 '.($active ? 'AND t.active = 1' : '').'
                '.(in_array($type, array('carrier', 'shop')) ? ' AND t.deleted = 0' : '').'
                '.($type == 'of_shipping_rule' ? 'AND t.id_of_shipping_rule != '.(int)$this->id : '').$shop_list
                .(in_array($type, array('carrier', 'shop')) ? ' GROUP BY t.id_'.pSQL($type).'
                    ORDER BY t.name ASC ' : '')
                .(in_array(
                    $type,
                    array('country', 'group', 'of_shipping_rule')
                ) && $i18n ? ' ORDER BY tl.name ASC ' : '')
            );
        } else {
            if ($type == 'of_shipping_rule') {
                $array = $this->getShippingRuleCombinations();
            } else {
                $resource = Db::getInstance()->query(
                    'SELECT t.*'.($i18n ? ', tl.*' : '').', IF(crt.id_'.pSQL($type).' IS NULL, 0, 1) as selected
                        FROM `'._DB_PREFIX_.pSQL($type).'` t '
                    .($i18n ? 'LEFT JOIN `'._DB_PREFIX_.pSQL($type).'_lang` tl
                        ON (t.id_'.pSQL($type).' = tl.id_'.pSQL($type).' AND tl.id_lang = '.(int)$id_lang.')' : '').'
                    LEFT JOIN (
                        SELECT id_'.pSQL($type).'
                            FROM `'._DB_PREFIX_.'of_shipping_rule_'.pSQL($type).'`
                            WHERE id_of_shipping_rule = '.(int)$this->id.'
                    ) crt ON t.id_'.($type == 'carrier' ? 'reference' : pSQL($type)).' = crt.id_'.pSQL($type).'
                    WHERE 1 '.($active ? ' AND t.active = 1' : '').
                    $shop_list
                    .(in_array($type, array('carrier', 'shop')) ? ' AND t.deleted = 0' : '')
                    .(in_array($type, array('carrier', 'shop')) ? ' GROUP BY t.id_'.pSQL($type).'
                        ORDER BY t.name ASC ' : '')
                    .(in_array($type, array('country', 'group', 'of_shipping_rule')) && $i18n ? '
                        ORDER BY tl.name ASC ' : ''),
                    false
                );
                
                while ($row = Db::getInstance()->nextRow($resource)) {
                    if ($row['selected'] || $this->{$type.'_restriction'} == 0) {
                        $array['selected'][] = $row;
                    } else {
                        $array['unselected'][] = $row;
                    }
                }
            }
        }
        
        return $array;
    }
    
    public function restrictionsCity($active = true)
    {
        $array = array('selected' => array(), 'unselected' => array());
        
        if (!Module::isEnabled('cityselect') || !Configuration::get('MS_ORDERFEES_SHIPPING_CITYSELECT')) {
            return $array;
        }
        
        $cities = Db::getInstance()->executeS(
            'SELECT c.name
                FROM `'._DB_PREFIX_.'cityselect_city` c 
            ' . ($active ? 'WHERE c.active = 1' : '') . '
            ORDER BY c.name ASC'
        );
        
        foreach ($cities as $city) {
            $array['unselected'][$city['name']] = array(
                'name' => $city['name']
            );
        }
        
        if (!Validate::isLoadedObject($this) || $this->city_restriction == 0) {
            list($array['selected'], $array['unselected']) = array($array['unselected'], $array['selected']);
        } else {
            $resource = Db::getInstance()->query(
                'SELECT name
                FROM `'._DB_PREFIX_.'of_shipping_rule_city`
                WHERE `id_of_shipping_rule` = ' . (int)$this->id,
                false
            );
            
            while ($row = Db::getInstance()->nextRow($resource)) {
                $name = $row['name'];
                
                if (isset($array['unselected'][$name])) {
                    $array['selected'][$name] = $array['unselected'][$name];
                    
                    unset($array['unselected'][$name]);
                }
            }
        }
        
        return $array;
    }
    
    public function restrictionsProducts($return_products = false)
    {
        if (!$this->product_restriction) {
            return $return_products ? array() : true;
        }

        $cart = $this->cart;
        
        $products_list = array();
        $selected_products = array();
        
        foreach ($cart->getProducts() as $product) {
            $products_list[] = (int)$product['id_product'].'-'.(int)$product['id_product_attribute'];
        }

        if (!count($products_list)) {
            return false;
        }
        
        $product_rule_groups = $this->getProductRuleGroups();

        foreach ($product_rule_groups as $id_product_rule_group => $product_rule_group) {
            $products = $products_list;
            $product_rules = $this->getProductRules($id_product_rule_group);

            $countRulesProduct = count($product_rules);
            $condition = 0;

            foreach ($product_rules as $product_rule) {
                $count_matching_products = 0;
                $matching_products_list = array();
                        
                switch ($product_rule['type']) {
                    case 'features':
                        $cart_features = Db::getInstance()->executeS('
                        SELECT cp.quantity, cp.`id_product`, fp.`id_feature_value`
                        FROM `'._DB_PREFIX_.'cart_product` cp
                        LEFT JOIN `'._DB_PREFIX_.'feature_product` fp ON cp.id_product = fp.id_product
                        WHERE cp.`id_cart` = '.(int)$cart->id.'
                        AND cp.`id_product` IN ('.implode(',', array_map('intval', $products)).')');

                        foreach ($cart_features as $cart_feature) {
                            if (in_array($cart_feature['id_feature_value'], $product_rule['values'])) {
                                $count_matching_products += $cart_feature['quantity'];
                                $matching_products_list[] = $cart_feature['id_product'].'-0';
                            }
                        }

                        break;
                    case 'attributes':
                        $cart_attributes = Db::getInstance()->executeS('
                        SELECT cp.*, pac.`id_attribute`
                        FROM `'._DB_PREFIX_.'cart_product` cp
                        LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac
                            ON cp.id_product_attribute = pac.id_product_attribute
                        WHERE cp.`id_cart` = '.(int)$cart->id.'
                        AND cp.`id_product` IN ('.implode(',', array_map('intval', $products)).')
                        AND cp.id_product_attribute > 0');

                        foreach ($cart_attributes as $ca) {
                            if (!empty($ca['instructions_id'])) {
                                if (!count(array_intersect(explode(',', $ca['instructions_id']), $product_rule['values']))) {
                                    continue;
                                }
                            } elseif (!in_array($ca['id_attribute'], $product_rule['values'])) {
                                continue;
                            }
                            
                            $count_matching_products += $ca['quantity'];
                            $matching_products_list[] = $ca['id_product'].'-'.$ca['id_product_attribute'];
                        }

                        break;
                    case 'products':
                        $cart_products = Db::getInstance()->executeS('
                        SELECT cp.quantity, cp.`id_product`
                        FROM `'._DB_PREFIX_.'cart_product` cp
                        WHERE cp.`id_cart` = '.(int)$cart->id.'
                        AND cp.`id_product` IN ('.implode(',', array_map('intval', $products)).')');
                        
                        foreach ($cart_products as $cart_product) {
                            if (in_array($cart_product['id_product'], $product_rule['values'])) {
                                $count_matching_products += $cart_product['quantity'];
                                $matching_products_list[] = $cart_product['id_product'].'-0';
                            }
                        }
                        
                        break;
                    case 'categories':
                        $cart_categories = Db::getInstance()->executeS('
                        SELECT cp.quantity, cp.`id_product`, cp.`id_product_attribute`, catp.`id_category`
                        FROM `'._DB_PREFIX_.'cart_product` cp
                        LEFT JOIN `'._DB_PREFIX_.'category_product` catp ON cp.id_product = catp.id_product
                        WHERE cp.`id_cart` = '.(int)$cart->id.'
                        AND cp.`id_product` IN ('.implode(',', array_map('intval', $products)).')');

                        foreach ($cart_categories as $cc) {
                            if (in_array($cc['id_category'], $product_rule['values'])
                                && !in_array($cc['id_product'].'-'.$cc['id_product_attribute'], $matching_products_list)
                            ) {
                                $count_matching_products += $cc['quantity'];
                                $matching_products_list[] = $cc['id_product'].'-0';
                            }
                        }
                        
                        break;
                    case 'manufacturers':
                        $cart_manufacturers = Db::getInstance()->executeS('
                        SELECT cp.quantity, cp.`id_product`, p.`id_manufacturer`
                        FROM `'._DB_PREFIX_.'cart_product` cp
                        LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.id_product = p.id_product
                        WHERE cp.`id_cart` = '.(int)$cart->id.'
                        AND cp.`id_product` IN ('.implode(',', array_map('intval', $products)).')');

                        foreach ($cart_manufacturers as $cart_manufacturer) {
                            if (in_array($cart_manufacturer['id_manufacturer'], $product_rule['values'])) {
                                $count_matching_products += $cart_manufacturer['quantity'];
                                $matching_products_list[] = $cart_manufacturer['id_product'].'-0';
                            }
                        }

                        break;
                    case 'suppliers':
                        $cart_suppliers = Db::getInstance()->executeS('
                        SELECT cp.quantity, cp.`id_product`, p.`id_supplier`
                        FROM `'._DB_PREFIX_.'cart_product` cp
                        LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.id_product = p.id_product
                        WHERE cp.`id_cart` = '.(int)$cart->id.'
                        AND cp.`id_product` IN ('.implode(',', array_map('intval', $products)).')');

                        foreach ($cart_suppliers as $cart_supplier) {
                            if (in_array($cart_supplier['id_supplier'], $product_rule['values'])) {
                                $count_matching_products += $cart_supplier['quantity'];
                                $matching_products_list[] = $cart_supplier['id_product'].'-0';
                            }
                        }

                        break;
                    case 'main_categories':
                        $cart_categories = Db::getInstance()->executeS('
                        SELECT cp.quantity, cp.`id_product`, p.`id_category_default`
                        FROM `'._DB_PREFIX_.'cart_product` cp
                        LEFT JOIN `'._DB_PREFIX_.'product` p ON cp.id_product = p.id_product
                        WHERE cp.`id_cart` = '.(int)$cart->id.'
                        AND cp.`id_product` IN ('.implode(',', array_map('intval', $products)).')');

                        foreach ($cart_categories as $cart_category) {
                            if (in_array($cart_category['id_category_default'], $product_rule['values'])) {
                                $count_matching_products += $cart_category['quantity'];
                                $matching_products_list[] = $cart_category['id_product'].'-0';
                            }
                        }

                        break;
                    case 'outofstock':
                        $count_matching_products = $product_rule_group['quantity'];
                        
                        foreach ($products as $product) {
                            list($id_product, $id_product_attribute) = explode('-', $product);
                            
                            if (!StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute, $cart->id_shop)) {
                                $matching_products_list[] = $product;
                            }
                        }

                        break;
                }
                
                if ($count_matching_products < $product_rule_group['quantity']) {
                    if ($countRulesProduct === 1) {
                        return false;
                    } else {
                        $condition++;
                    }
                } else {
                    if (($this->type & self::APPLY_IF_ALL) && ($products != self::arrayUintersect($products, $matching_products_list))) {
                        return false;
                    }

                    $products = self::arrayUintersect($products, $matching_products_list);
                }

                if (!count($products)) {
                    if ($countRulesProduct === 1) {
                        return false;
                    }
                }
            }

            if ($countRulesProduct !== 1 && $condition == $countRulesProduct) {
                return false;
            }
            
            $selected_products = array_unique(array_merge($selected_products, $products));
        }
        
        if ($return_products) {
            return $selected_products;
        }
        
        return true;
    }
    
    public function getProducts()
    {
        static $products = [];
        
        if (isset($products[$this->id])) {
            return $products[$this->id];
        }

        $cart = $this->cart;
        
        if (!$cart->id) {
            return [];
        }
        
        $selected_products = $this->restrictionsProducts(true);
        $products[$this->id] = [];
        
        if (is_array($selected_products)) {
            $all_products = (count($selected_products) == 0);

            foreach ($cart->getProducts() as $product) {
                $key = (int)$product['id_product'].'-'.(int)$product['id_product_attribute'];

                if ($all_products || in_array($key, $selected_products)) {
                    $products[$this->id][] = $product;
                }
            }
            
            Hook::exec('actionShippingRuleGetProducts', [
                'object' => &$this,
                'products' => &$products[$this->id]
            ]);
        }
        
        return $products[$this->id];
    }
    
    public static function getShippingCostByProduct($id_product, $id_product_attribute = 0, $use_tax = null)
    {
        $values = array('price_tax_incl' => 0, 'price_tax_excl' => 0);
        
        $items = Db::getInstance()->executeS(
            'SELECT sr.id_of_shipping_rule
            FROM '._DB_PREFIX_.'of_shipping_rule sr
            WHERE sr.active = 1 AND sr.product_restriction = 1
            GROUP BY sr.id_of_shipping_rule
            ORDER BY sr.priority ASC'
        );

        if (empty($items)) {
            return $values;
        }
        
        $product = (int)$id_product.'-'.(int)$id_product_attribute;
        $items_disabled = array();

        foreach ($items as &$item) {
            if (in_array($item['id_of_shipping_rule'], $items_disabled)) {
                continue;
            }
            
            $shipping_rule = ShippingRule::factory($item['id_of_shipping_rule'], Context::getContext()->cart);
                
            if (!$shipping_rule->check()) {
                continue;
            }
            
            if ($shipping_rule->of_shipping_rule_restriction) {
                $other_items = $items;

                foreach ($other_items as $other_item) {
                    if ($shipping_rule->id == $other_item['id_of_shipping_rule']) {
                        continue;
                    }

                    $combinable = Db::getInstance()->getValue(
                        'SELECT id_of_shipping_rule_1
                        FROM '._DB_PREFIX_.'of_shipping_rule_combination
                        WHERE (
                            id_of_shipping_rule_1 = ' . (int) $shipping_rule->id . '
                            AND id_of_shipping_rule_2 = ' . (int) $other_item['id_of_shipping_rule'] . '
                        ) OR (
                            id_of_shipping_rule_2 = ' . (int) $shipping_rule->id . '
                            AND id_of_shipping_rule_1 = ' . (int) $other_item['id_of_shipping_rule'] . '
                        )'
                    );

                    if (!$combinable) {
                        $items_disabled[] = $other_item['id_of_shipping_rule'];
                    }
                }
            }
            
            if (in_array($product, $shipping_rule->restrictionsProducts(true))) {
                $values['price_tax_incl'] += $shipping_rule->value(true);
                $values['price_tax_excl'] += $shipping_rule->value(false);
            }
        }
        
        return ($use_tax === null ? $values : $values[$use_tax ? 'price_tax_incl' : 'price_tax_excl']);
    }
    
    public function formatCity($city)
    {
        return Tools::strtolower(preg_replace('/[^\pL]/u', '', $city));
    }
    
    public static function factory($id_of_shipping_rule, $cart)
    {
        $shipping_rule = new self($id_of_shipping_rule);
        
        $shipping_rule->cart = $cart;
        
        return $shipping_rule;
    }
    
    protected static function arrayUintersect($array1, $array2)
    {
        $intersection = array();
        foreach ($array1 as $value1) {
            foreach ($array2 as $value2) {
                if (self::arrayUintersectCompare($value1, $value2) == 0) {
                    $intersection[] = $value1;
                    break 1;
                }
            }
        }
        return $intersection;
    }

    protected static function arrayUintersectCompare($a, $b)
    {
        if ($a == $b) {
            return 0;
        }

        $asplit = explode('-', $a);
        $bsplit = explode('-', $b);
        if ($asplit[0] == $bsplit[0] && (!(int)$asplit[1] || !(int)$bsplit[1])) {
            return 0;
        }

        return 1;
    }
    
    /**
     * Returns tax rate.
     *
     * @param Address|null $address
     * @return float The total taxes rate applied to the product
     */
    public function getTaxesRate($tax_rules_group, Address $address = null)
    {
        if (!$address || !$address->id_country) {
            $address = Address::initialize();
        }

        $tax_manager = TaxManagerFactory::getManager($address, $tax_rules_group);
        $tax_calculator = $tax_manager->getTaxCalculator();

        return $tax_calculator->getTotalRate();
    }
    
    public static function parse($formula, $data)
    {
        if (is_numeric($formula)) {
            return (float)$formula;
        }
        
        foreach ($data as $name => $value) {
            self::math()->registerVariable($name, $value);
        }
        
        try {
            return self::math()->evaluate($formula);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public static function math()
    {
        if (is_null(self::$math_parser)) {
            self::$math_parser = new \PHPMathParser\Math();
        }
 
        return self::$math_parser;
    }
}
