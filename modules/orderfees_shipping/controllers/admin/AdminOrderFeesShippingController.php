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

class AdminOrderFeesShippingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        
        $this->table = 'of_shipping_rule';
        $this->className = 'ShippingRule';
        $this->_orderWay = 'DESC';
        
        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');
        
        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );

        $this->fields_list = array(
            'id_of_shipping_rule' => array('title' => $this->l('ID'), 'align' => 'center', 'class' => 'fixed-width-xs'),
            'name' => array('title' => $this->l('Name')),
            'priority' => array('title' => $this->l('Priority'), 'align' => 'center', 'class' => 'fixed-width-xs'),
            'active' => array(
                'title' => $this->l('Status'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'orderby' => false
            )
        );
        
        $this->override_folder = 'orderfees_shipping/';
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        
        if (in_array($this->display, array('add', 'edit'))) {
            $this->addJqueryPlugin(array('typewatch', 'fancybox', 'autocomplete'));

            $this->addCSS($this->module->getPathUri() . 'views/css/bootstrap-datetimepicker.min.css');
            $this->addJS($this->module->getPathUri() . 'views/js/moment.js');
            $this->addJS($this->module->getPathUri() . 'views/js/bootstrap-datetimepicker.js');
        }
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_shipping_rule'] = array(
                'href' => self::$currentIndex.'&addof_shipping_rule&token='.$this->token,
                'desc' => $this->l('Add new rule', null, null, false),
                'icon' => 'process-icon-new'
            );
        }
        
        if (in_array($this->display, array('add', 'edit'))) {
            $this->toolbar_btn['save-and-stay'] = array(
                'href' => '#',
                'desc' => $this->l('Save and Stay')
            );
        }

        if ((bool) !Module::isEnabled($this->module->name)) {
            $this->warnings[] = $this->l('"Advanced Shipping cost Plus" module is not enabled. Please enable it from "Modules" menu.');
        }
        
        if ((bool) Configuration::get('PS_DISABLE_OVERRIDES')) {
            $this->warnings[] = $this->l('Overrides are disabled on your store. In "Performance" menu, the "Disable all overrides" setting must be set to "No". Then clear the cache.');
        }

        parent::initPageHeaderToolbar();
    }
    
    public function initProcess()
    {
        parent::initProcess();
        
        if (Tools::isSubmit('submitAddof_shipping_rule') || Tools::isSubmit('submitAddof_shipping_ruleAndStay')) {
            $this->display = 'edit';
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAddof_shipping_rule') || Tools::isSubmit('submitAddof_shipping_ruleAndStay')) {
            Hook::exec('actionAdminOrderFeesShippingValidateBefore', array(
                'controller' => &$this,
                'errors' => &$this->errors
            ));
            
            if (!Tools::getValue('name')) {
                $this->errors[] = $this->l('The rule name is empty');
            }
            
            if (!Validate::isUnsignedInt(Tools::getValue('priority'))) {
                $this->errors[] = $this->l('The priority must be a number');
            }
            
            $post = &$_POST;
            $type = (int) Tools::getValue('type');
            
            if (Tools::getValue('apply_to') == 'selection') {
                $post['product'] = ShippingRule::PERCENT_PRODUCTS;
            } elseif ((int)Tools::getValue('product')
                && Tools::getValue('apply_to') == 'specific'
                && ($type & (ShippingRule::IS_PERCENT + ShippingRule::IS_AMOUNT))
            ) {
                $product = (int)Tools::getValue('product');

                $already_restricted = false;
                
                if (is_array($rule_group_array = Tools::getValue('product_rule_group'))
                    && count($rule_group_array)
                    && Tools::getValue('product_restriction')
                ) {
                    foreach ($rule_group_array as $rule_group_id) {
                        if (is_array($rule_array = Tools::getValue('product_rule_' . (int)$rule_group_id))
                            && count($rule_array)
                        ) {
                            foreach ($rule_array as $rule_id) {
                                $product_group_rule_id = 'product_rule_'.(int)$rule_group_id.'_'.(int)$rule_id;
                                
                                if (Tools::getValue($product_group_rule_id.'_type') == 'products'
                                    && in_array(
                                        $product,
                                        Tools::getValue('product_rule_select_'.(int)$rule_group_id.'_'.(int)$rule_id)
                                    )
                                ) {
                                    $already_restricted = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                
                if ($already_restricted == false) {
                    $post['product_restriction'] = 1;

                    $rule_group_id = 1;
                    
                    if (is_array($rule_group_array)) {
                        $post['product_rule_group'][] = $rule_group_id;
                    } else {
                        $post['product_rule_group'] = array($rule_group_id);
                    }

                    $post['product_rule_group_'.(int)$rule_group_id.'_quantity'] = 1;
                    $post['product_rule_'.(int)$rule_group_id] = array(1);
                    $post['product_rule_'.(int)$rule_group_id.'_1_type'] = 'products';
                    $post['product_rule_select_'.(int)$rule_group_id.'_1'] = array($product);
                }
            } else {
                $post['product'] = 0;
            }
            
            $restrictions = array(
                'country',
                'zone',
                'state',
                'city',
                'carrier',
                'group',
                'shipping_rule',
                'shop',
                'product',
                'dimension',
                'zipcode',
                'package',
                'gender'
            );

            foreach ($restrictions as $restriction) {
                if (!Tools::getValue($restriction . '_restriction')) {
                    $post[$restriction . '_restriction'] = 0;
                }
            }
            
            if (Tools::getValue('date_restriction')) {
                if (Tools::getValue('date_from')
                    && Tools::getValue('date_to')
                    && strtotime(Tools::getValue('date_from')) > strtotime(Tools::getValue('date_to'))
                ) {
                    $this->errors[] = $this->l('The rule cannot end before it begins.');
                }

                if (!Tools::getValue('date_from')) {
                    $post['date_from'] = null;
                }

                if (!Tools::getValue('date_to')) {
                    $post['date_to'] = null;
                }
            } else {
                $post['date_from'] = null;
                $post['date_to'] = null;
            }
            
            if (Tools::getValue('time_restriction')) {
                if (!Tools::getValue('time_from') || !Tools::getValue('time_to')) {
                    $this->errors[] = $this->l('Please fill the start and end time for this rule');
                }
            } else {
                $post['time_from'] = null;
                $post['time_to'] = null;
            }
            
            if (Tools::getValue('nb_supplier_restriction')) {
                if (!Tools::getValue('nb_supplier_min')) {
                    $post['nb_supplier_min'] = 0;
                }
                
                if (!Validate::isUnsignedInt(Tools::getValue('nb_supplier_min'))
                    || !Validate::isUnsignedInt(Tools::getValue('nb_supplier_max'))
                    || !Tools::getValue('nb_supplier_max')
                ) {
                    $this->errors[] = $this->l('Please check "Min" and "Max" for the number of suppliers.');
                }
            } else {
                $post['nb_supplier_min'] = null;
                $post['nb_supplier_max'] = null;
            }
            
            if (!Validate::isUnsignedFloat(Tools::getValue('minimum_amount'))) {
                $this->errors[] = $this->l('The minimum amount cannot be lower than zero.');
            }
            
            if (!Validate::isUnsignedFloat(Tools::getValue('maximum_amount'))) {
                $this->errors[] = $this->l('The maximum amount cannot be lower than zero.');
            }
            
            if ($type & ShippingRule::IS_PERCENT) {
                $value = Tools::getValue('percent');
                
                if (!(Validate::isFloat($value) && $value >= -100 && $value <= 100)) {
                    $this->errors[] = $this->l('Percentage is invalid');
                }
            } else {
                $post['percent'] = 0;
            }
            
            if ($type & ShippingRule::IS_AMOUNT) {
                if (!Validate::isFloat(Tools::getValue('amount'))) {
                    $this->errors[] = $this->l('Amount is invalid');
                }
            } else {
                $post['amount'] = 0;
            }
            
            if ($type & (ShippingRule::IS_FORMULA + ShippingRule::IS_WEIGHT)) {
                $formula = trim(Tools::getValue('formula'));
                
                if ($formula) {
                    $test_data = array(
                        'quantity' => 5,
                        'total_quantity' => 5,
                        'total' => 100,
                        'width' => 100,
                        'height' => 100,
                        'depth' => 100,
                        'weight' => 100,
                        'volume' => 100,
                        'width_products' => 100,
                        'height_products' => 100,
                        'depth_products' => 100,
                        'weight_products' => 100,
                        'volume_products' => 100,
                        'shipping' => 100,
                        'total_exclude_promo' => 100
                    );

                    $result = ShippingRule::parse($formula, $test_data);

                    if (is_string($result)) {
                        $this->errors[] = sprintf($this->l('The formula is invalid : %s'), $result);
                    }
                } else {
                    $this->errors[] = $this->l('Formula is invalid');
                }
            } else {
                $post['formula'] = '';
            }
            
            if (!$type) {
                $post['tax_rules_group'] = 0;
                $post['quantity_per_product'] = 0;
            }
            
            if ($type & (ShippingRule::IS_FREE_SHIPPING + ShippingRule::IS_PERCENT + ShippingRule::IS_AMOUNT + ShippingRule::IS_FORMULA + ShippingRule::IS_CARRIER)) {
                $post['type'] |= (int)Tools::getValue('apply_if');
            }
            
            if ($type & (ShippingRule::IS_PERCENT + ShippingRule::IS_AMOUNT + ShippingRule::IS_FORMULA)) {
                $post['type'] |= (int)Tools::getValue('basic_shipping');
            }
            
            if (Tools::getValue('package_restriction')) {
                if (is_array($rule_group_array = Tools::getValue('package_rule_group')) && count($rule_group_array)) {
                    foreach ($rule_group_array as $rule_group_id) {
                        if (!(int)Tools::getValue('package_rule_group_ratio_' . (int)$rule_group_id)) {
                            $ratio = (float)$post['package_rule_group_unit_predefined_' . (int)$rule_group_id];

                            $post['package_rule_group_ratio_' . (int)$rule_group_id] = $ratio;
                        }
                    }
                }
            }
            
            Hook::exec('actionAdminOrderFeesShippingValidateAfter', array(
                'controller' => &$this,
                'errors' => &$this->errors
            ));
        } elseif (Tools::getIsset('duplicate' . $this->table)) {
            $this->action = 'duplicate';
        }
        
        return parent::postProcess();
    }
    
    public function validateRules($class_name = false)
    {
        if (empty($this->errors)) {
            return parent::validateRules($class_name);
        }
        
        return false;
    }
    
    public function processDuplicate()
    {
        if (Validate::isLoadedObject($shipping_rule = new ShippingRule((int)Tools::getValue('id_of_shipping_rule')))) {
            $id_of_shipping_rule = $shipping_rule->id;
            
            unset($shipping_rule->id);
            
            if (Tools::getValue('name')) {
                $shipping_rule->name = Tools::htmlentitiesUTF8(Tools::getValue('name'));
            }

            if ($shipping_rule->add()) {
                $tables = array(
                    'of_shipping_rule_carrier' => array(
                        'column' => 'id_of_shipping_rule'
                    ),
                    'of_shipping_rule_combination' => array(
                        'column' => 'id_of_shipping_rule_1'
                    ),
                    'of_shipping_rule_country' => array(
                        'column' => 'id_of_shipping_rule'
                    ),
                    'of_shipping_rule_zone' => array(
                        'column' => 'id_of_shipping_rule'
                    ),
                    'of_shipping_rule_state' => array(
                        'column' => 'id_of_shipping_rule'
                    ),
                    'of_shipping_rule_city' => array(
                        'column' => 'id_of_shipping_rule'
                    ),
                    'of_shipping_rule_group' => array(
                        'column' => 'id_of_shipping_rule'
                    ),
                    'of_shipping_rule_shop' => array(
                        'column' => 'id_of_shipping_rule'
                    ),
                    'of_shipping_rule_product_rule_group' => array(
                        'column' => 'id_of_shipping_rule',
                        'column_ignore' => array('id_product_rule_group'),
                        'link_tables' => array(
                            'of_shipping_rule_product_rule' => array(
                                'column' => 'id_product_rule_group',
                                'column_ignore' => array('id_product_rule'),
                                'link_tables' => array(
                                    'of_shipping_rule_product_rule_value' => array(
                                        'column' => 'id_product_rule'
                                    )
                                )
                            )
                        )
                    ),
                    'of_shipping_rule_city_rule_group' => array(
                        'column' => 'id_of_shipping_rule',
                        'column_ignore' => array('id_city_rule_group'),
                        'link_tables' => array(
                            'of_shipping_rule_city_rule' => array(
                                'column' => 'id_city_rule_group',
                                'column_ignore' => array('id_city_rule')
                            )
                        )
                    ),
                    'of_shipping_rule_zipcode_rule_group' => array(
                        'column' => 'id_of_shipping_rule',
                        'column_ignore' => array('id_zipcode_rule_group'),
                        'link_tables' => array(
                            'of_shipping_rule_zipcode_rule' => array(
                                'column' => 'id_zipcode_rule_group',
                                'column_ignore' => array('id_zipcode_rule')
                            )
                        )
                    ),
                    'of_shipping_rule_dimension_rule_group' => array(
                        'column' => 'id_of_shipping_rule',
                        'column_ignore' => array('id_dimension_rule_group'),
                        'link_tables' => array(
                            'of_shipping_rule_dimension_rule' => array(
                                'column' => 'id_dimension_rule_group',
                                'column_ignore' => array('id_dimension_rule')
                            )
                        )
                    ),
                    'of_shipping_rule_package_rule_group' => array(
                        'column' => 'id_of_shipping_rule',
                        'column_ignore' => array('id_package_rule_group'),
                        'link_tables' => array(
                            'of_shipping_rule_package_rule' => array(
                                'column' => 'id_package_rule_group',
                                'column_ignore' => array('id_package_rule')
                            )
                        )
                    ),
                    'of_shipping_rule_gender' => array(
                        'column' => 'id_of_shipping_rule'
                    )
                );
                
                $this->module->duplicateTables($tables, $id_of_shipping_rule, $shipping_rule->id);
                
                Hook::exec(
                    'actionShippingRuleDuplicate',
                    array(
                        'id_of_shipping_rule_duplicated' => (int)$id_of_shipping_rule,
                        'shipping_rule' => &$shipping_rule
                    )
                );
                    
                $this->redirect_after = self::$currentIndex.'&conf=19&token='.$this->token;
            } else {
                $this->errors[] = $this->l('An error occurred while creating an object.');
            }
        }
    }
    
    public function processAdd()
    {
        if ($shipping_rule = parent::processAdd()) {
            $this->context->smarty->assign('new_shipping_rule', $shipping_rule);
        }
        
        if (Tools::getValue('submitFormAjax')) {
            $this->redirect_after = false;
        }

        return $shipping_rule;
    }

    public function processDelete()
    {
        $r = parent::processDelete();
        
        if (Tools::isSubmit('delete' . $this->table)) {
            $back = urldecode(Tools::getValue('back', ''));
            
            if (!empty($back)) {
                $this->redirect_after = $back;
            }
        }
        
        return $r;
    }
    
    protected function afterUpdate($rule)
    {
        $id_of_shipping_rule = Tools::getValue('id_of_shipping_rule');

        // Restrictions
        $restrictions_types = array(
            'country',
            'zone',
            'state',
            'city',
            'carrier',
            'group',
            'product_rule_group',
            'shop',
            'gender'
        );
        
        foreach ($restrictions_types as $type) {
            Db::getInstance()->delete(
                'of_shipping_rule_' . pSQL($type),
                '`id_of_shipping_rule` = ' . (int)$id_of_shipping_rule
            );
        }

        // Product restriction
        Db::getInstance()->delete(
            'of_shipping_rule_product_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule`.`id_product_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule_group`.`id_product_rule_group`)'
        );
        Db::getInstance()->delete(
            'of_shipping_rule_product_rule_value',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule_value`.`id_product_rule`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_product_rule`.`id_product_rule`)'
        );
        Db::getInstance()->delete(
            'of_shipping_rule_combination',
            '`id_of_shipping_rule_1` = ' . (int)$id_of_shipping_rule . '
                OR `id_of_shipping_rule_2` = ' . (int)$id_of_shipping_rule
        );
        
        // Dimension restriction
        Db::getInstance()->delete(
            'of_shipping_rule_dimension_rule_group',
            '`id_of_shipping_rule` = ' . (int)$id_of_shipping_rule
        );
        Db::getInstance()->delete(
            'of_shipping_rule_dimension_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_dimension_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_dimension_rule`.`id_dimension_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_dimension_rule_group`.`id_dimension_rule_group`)'
        );
        
        // City restriction
        Db::getInstance()->delete(
            'of_shipping_rule_city_rule_group',
            '`id_of_shipping_rule` = ' . (int)$id_of_shipping_rule
        );
        Db::getInstance()->delete(
            'of_shipping_rule_city_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_city_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_city_rule`.`id_city_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_city_rule_group`.`id_city_rule_group`)'
        );
        
        // Zipcode restriction
        Db::getInstance()->delete(
            'of_shipping_rule_zipcode_rule_group',
            '`id_of_shipping_rule` = ' . (int)$id_of_shipping_rule
        );
        Db::getInstance()->delete(
            'of_shipping_rule_zipcode_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_zipcode_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_zipcode_rule`.`id_zipcode_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_zipcode_rule_group`.`id_zipcode_rule_group`)'
        );
        
        // Package restriction
        Db::getInstance()->delete(
            'of_shipping_rule_package_rule_group',
            '`id_of_shipping_rule` = ' . (int)$id_of_shipping_rule
        );
        Db::getInstance()->delete(
            'of_shipping_rule_package_rule',
            'NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'of_shipping_rule_package_rule_group`
                WHERE `' . _DB_PREFIX_ . 'of_shipping_rule_package_rule`.`id_package_rule_group`
                    = `' . _DB_PREFIX_ . 'of_shipping_rule_package_rule_group`.`id_package_rule_group`)'
        );
        
        $this->afterAdd($rule);
    }
    
    protected function afterAdd($rule)
    {
        // Add restrictions
        foreach (array('country', 'zone', 'state', 'carrier', 'group', 'shop', 'gender') as $type) {
            if (Tools::getValue($type.'_restriction')
                && is_array($array = Tools::getValue($type.'_select'))
                && count($array)
            ) {
                $values = array();
                
                foreach ($array as $id) {
                    $values[] = '('.(int)$rule->id.','.(int)$id.')';
                }
                
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_'.pSQL($type).'`
                        (`id_of_shipping_rule`, `id_'.pSQL($type).'`)
                        VALUES '.implode(',', $values)
                );
            }
        }
        
        // City
        if (Tools::getValue('city_restriction')
            && is_array($array = Tools::getValue('city_select'))
            && count($array)
        ) {
            $values = array();

            foreach ($array as $name) {
                $values[] = '('.(int)$rule->id.', "'.pSQL($name).'")';
            }

            Db::getInstance()->execute(
                'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_city`
                    (`id_of_shipping_rule`, `name`)
                    VALUES '.implode(',', $values)
            );
        }
        
        // Add combinable rule restrictions
        if (Tools::getValue('of_shipping_rule_restriction')
            && is_array($array = Tools::getValue('of_shipping_rule_select'))
            && count($array)
        ) {
            $values = array();
            
            foreach ($array as $id) {
                $values[] = '('.(int)$rule->id.','.(int)$id.')';
            }
            
            Db::getInstance()->execute(
                'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_combination`
                (`id_of_shipping_rule_1`, `id_of_shipping_rule_2`)
                    VALUES '.implode(',', $values)
            );
        }
        
        // Add product rule restrictions
        if (Tools::getValue('product_restriction')
            && is_array($ruleGroupArray = Tools::getValue('product_rule_group'))
            && count($ruleGroupArray)
        ) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                $quantity = (int)Tools::getValue('product_rule_group_'.(int)$ruleGroupId.'_quantity');
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_product_rule_group`
                    (`id_of_shipping_rule`, `quantity`)
                        VALUES ('.(int)$rule->id.', '.(int)$quantity.')'
                );
                
                $id_product_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('product_rule_'.(int)$ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        $type = Tools::getValue('product_rule_'.(int)$ruleGroupId.'_'.(int)$ruleId.'_type');
                        
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_product_rule`
                            (`id_product_rule_group`, `type`)
                            VALUES ('.(int)$id_product_rule_group.',"'.pSQL($type).'")'
                        );
                        
                        $id_product_rule = Db::getInstance()->Insert_ID();
                        
                        if (!Tools::getIsset('product_rule_select_'.(int)$ruleGroupId.'_'.(int)$ruleId)) {
                            continue;
                        }

                        $values = array();
                        
                        foreach (Tools::getValue('product_rule_select_'.(int)$ruleGroupId.'_'.(int)$ruleId) as $id) {
                            $values[] = '('.(int)$id_product_rule.','.(int)$id.')';
                        }
                        
                        $values = array_unique($values);
                        
                        if (count($values)) {
                            Db::getInstance()->execute(
                                'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_product_rule_value`
                                (`id_product_rule`, `id_item`)
                                VALUES '.implode(',', $values)
                            );
                        }
                    }
                }
            }
        }

        if (!Tools::getValue('of_shipping_rule_restriction')) {
            Db::getInstance()->execute(
                'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_combination`
                (`id_of_shipping_rule_1`, `id_of_shipping_rule_2`) (
                    SELECT id_of_shipping_rule, '.(int)$rule->id.'
                        FROM `'._DB_PREFIX_.'of_shipping_rule`
                        WHERE of_shipping_rule_restriction = 1
                )'
            );
        } else {
            $ruleCombinations = Db::getInstance()->executeS(
                'SELECT sr.id_of_shipping_rule
                    FROM '._DB_PREFIX_.'of_shipping_rule sr
                    WHERE sr.id_of_shipping_rule != '.(int)$rule->id.'
                        AND sr.of_shipping_rule_restriction = 0
                        AND NOT EXISTS (
                            SELECT 1
                                FROM '._DB_PREFIX_.'of_shipping_rule_combination c1
                                WHERE sr.id_of_shipping_rule = c1.id_of_shipping_rule_2
                                    AND '.(int)$rule->id.' = c1.id_of_shipping_rule_1
                        )
                        AND NOT EXISTS (
                            SELECT 1
                                FROM '._DB_PREFIX_.'of_shipping_rule_combination c2
                                WHERE sr.id_of_shipping_rule = c2.id_of_shipping_rule_1
                                    AND '.(int)$rule->id.' = c2.id_of_shipping_rule_2
                        )'
            );
            
            foreach ($ruleCombinations as $incompatibleRule) {
                Db::getInstance()->execute(
                    'UPDATE `'._DB_PREFIX_.'of_shipping_rule` SET of_shipping_rule_restriction = 1
                        WHERE id_of_shipping_rule = '.(int)$incompatibleRule['id_of_shipping_rule'].'
                        LIMIT 1'
                );
                
                Db::getInstance()->execute(
                    'INSERT IGNORE INTO `'._DB_PREFIX_.'of_shipping_rule_combination`
                        (`id_of_shipping_rule_1`, `id_of_shipping_rule_2`) (
                            SELECT id_of_shipping_rule, '.(int)$incompatibleRule['id_of_shipping_rule'].'
                                FROM `'._DB_PREFIX_.'of_shipping_rule`
                                WHERE active = 1
                                    AND id_of_shipping_rule != '.(int)$rule->id.'
                                    AND id_of_shipping_rule != '.(int)$incompatibleRule['id_of_shipping_rule'].'
                        )'
                );
            }
        }
        
        // Add dimension rule restrictions
        if (Tools::getValue('dimension_restriction')
            && is_array($ruleGroupArray = Tools::getValue('dimension_rule_group'))
            && count($ruleGroupArray)
        ) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_dimension_rule_group` (`id_of_shipping_rule`, `base`)
                        VALUES ('.(int)$rule->id.',
                            "'.pSQL(Tools::getValue('dimension_rule_group_base_'.(int)$ruleGroupId)).'")'
                );
                
                $id_dimension_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('dimension_rule_'.(int)$ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        $dimension_group_rule_id = 'dimension_rule_'.(int)$ruleGroupId.'_'.(int)$ruleId;
                        
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_dimension_rule`
                            (`id_dimension_rule_group`, `type`, `operator`, `value`)
                            VALUES ('.(int)$id_dimension_rule_group.',
                            "'.pSQL(Tools::getValue($dimension_group_rule_id.'_type'), true).'",
                            "'.pSQL(Tools::getValue($dimension_group_rule_id.'_operator'), true).'",
                            "'.pSQL(Tools::getValue($dimension_group_rule_id.'_value'), true).'")'
                        );
                    }
                }
            }
        }
        
        // Add city rule restrictions
        if (Tools::getValue('city_restriction')
            && is_array($ruleGroupArray = Tools::getValue('city_rule_group'))
            && count($ruleGroupArray)
        ) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_city_rule_group` (`id_of_shipping_rule`)
                        VALUES ('.(int)$rule->id.')'
                );
                
                $id_city_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('city_rule_'.(int)$ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        $city_group_rule_id = 'city_rule_'.(int)$ruleGroupId.'_'.(int)$ruleId;
                        
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_city_rule`
                            (`id_city_rule_group`, `type`, `value`)
                            VALUES ('.(int)$id_city_rule_group.',
                            "'.pSQL(Tools::getValue($city_group_rule_id.'_type'), true).'",
                            "'.pSQL(Tools::getValue($city_group_rule_id.'_value'), true).'")'
                        );
                    }
                }
            }
        }
        
        // Add zipcode rule restrictions
        if (Tools::getValue('zipcode_restriction')
            && is_array($ruleGroupArray = Tools::getValue('zipcode_rule_group'))
            && count($ruleGroupArray)
        ) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_zipcode_rule_group` (`id_of_shipping_rule`)
                        VALUES ('.(int)$rule->id.')'
                );
                
                $id_zipcode_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('zipcode_rule_'.(int)$ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        $zipcode_group_rule_id = 'zipcode_rule_'.(int)$ruleGroupId.'_'.(int)$ruleId;
                        
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_zipcode_rule`
                            (`id_zipcode_rule_group`, `type`, `operator`, `value`)
                            VALUES ('.(int)$id_zipcode_rule_group.',
                            "'.pSQL(Tools::getValue($zipcode_group_rule_id.'_type'), true).'",
                            "'.pSQL(Tools::getValue($zipcode_group_rule_id.'_operator'), true).'",
                            "'.pSQL(Tools::getValue($zipcode_group_rule_id.'_value'), true).'")'
                        );
                    }
                }
            }
        }
        
        // Add package rule restrictions
        if (Tools::getValue('package_restriction')
            && is_array($ruleGroupArray = Tools::getValue('package_rule_group'))
            && count($ruleGroupArray)
        ) {
            foreach ($ruleGroupArray as $ruleGroupId) {
                Db::getInstance()->execute(
                    'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_package_rule_group`
                        (`id_of_shipping_rule`, `unit`, `unit_weight`, `ratio`)
                        VALUES ('.(int)$rule->id.',
                            "'.pSQL(Tools::getValue('package_rule_group_unit_'.(int)$ruleGroupId)).'",
                            "'.pSQL(Tools::getValue('package_rule_group_unit_weight_'.(int)$ruleGroupId)).'",
                            "'.pSQL(Tools::getValue('package_rule_group_ratio_'.(int)$ruleGroupId)).'")'
                );
                
                $id_package_rule_group = Db::getInstance()->Insert_ID();

                if (is_array($ruleArray = Tools::getValue('package_rule_'.(int)$ruleGroupId)) && count($ruleArray)) {
                    foreach ($ruleArray as $ruleId) {
                        $package_group_rule_id = 'package_rule_'.(int)$ruleGroupId.'_'.(int)$ruleId;
                        
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'of_shipping_rule_package_rule`
                            (`id_package_rule_group`, `range_start`, `range_end`, `round`, `divider`,
                            `currency`, `tax`, `value`)
                            VALUES ('.(int)$id_package_rule_group.',
                            "'.pSQL(Tools::getValue($package_group_rule_id.'_range_start')).'",
                            "'.pSQL(Tools::getValue($package_group_rule_id.'_range_end')).'",
                            "'.pSQL(Tools::getValue($package_group_rule_id.'_round')).'",
                            "'.pSQL(Tools::getValue($package_group_rule_id.'_divider')).'",
                            "'.pSQL(Tools::getValue($package_group_rule_id.'_currency')).'",
                            "'.pSQL(Tools::getValue($package_group_rule_id.'_tax')).'",
                            "'.pSQL(Tools::getValue($package_group_rule_id.'_value')).'")'
                        );
                    }
                }
            }
        }
    }

    public function getProductRuleGroupsDisplay($shipping_rule)
    {
        $productRuleGroupsArray = array();
        if (Tools::getValue('product_restriction')
            && is_array($array = Tools::getValue('product_rule_group'))
            && count($array)
        ) {
            $i = 1;
            foreach ($array as $ruleGroupId) {
                $productRulesArray = array();
                if (is_array($array = Tools::getValue('product_rule_'.(int)$ruleGroupId)) && count($array)) {
                    foreach ($array as $ruleId) {
                        $productRulesArray[] = $this->getProductRuleDisplay(
                            $ruleGroupId,
                            $ruleId,
                            Tools::getValue('product_rule_'.(int)$ruleGroupId.'_'.(int)$ruleId.'_type'),
                            Tools::getValue('product_rule_select_'.(int)$ruleGroupId.'_'.(int)$ruleId)
                        );
                    }
                }

                $productRuleGroupsArray[] = $this->getProductRuleGroupDisplay(
                    $i++,
                    (int)Tools::getValue('product_rule_group_'.(int)$ruleGroupId.'_quantity'),
                    $productRulesArray
                );
            }
        } else {
            $i = 1;
            foreach ($shipping_rule->getProductRuleGroups() as $productRuleGroup) {
                $j = 1;
                $productRulesDisplay = array();
                foreach ($productRuleGroup['product_rules'] as $productRule) {
                    $productRulesDisplay[] = $this->getProductRuleDisplay(
                        $i,
                        $j++,
                        $productRule['type'],
                        $productRule['values']
                    );
                }
                $productRuleGroupsArray[] = $this->getProductRuleGroupDisplay(
                    $i++,
                    $productRuleGroup['quantity'],
                    $productRulesDisplay
                );
            }
        }
        
        return $productRuleGroupsArray;
    }

    public function getProductRuleGroupDisplay($group_id, $quantity = 1, $product_rules = array())
    {
        Context::getContext()->smarty->assign('product_rule_group_id', $group_id);
        Context::getContext()->smarty->assign('product_rule_group_quantity', $quantity);
        Context::getContext()->smarty->assign('product_rules', $product_rules);

        return $this->createTemplate('product_rule_group.tpl')->fetch();
    }

    public function ajaxProcess()
    {
        if (Tools::isSubmit('newDimensionRule')) {
            die($this->getDimensionRuleDisplay(
                Tools::getValue('dimension_rule_group_id'),
                Tools::getValue('dimension_rule_id'),
                Tools::getValue('dimension_rule_type')
            ));
        }
        
        if (Tools::isSubmit('newDimensionRuleGroup')
            && $dimension_rule_group_id = Tools::getValue('dimension_rule_group_id')
        ) {
            die($this->getDimensionRuleGroupDisplay(
                $dimension_rule_group_id,
                Tools::getValue('dimension_rule_group_base_'.(int)$dimension_rule_group_id, 'product')
            ));
        }
        
        if (Tools::isSubmit('newCityRule')) {
            die($this->getCityRuleDisplay(
                Tools::getValue('city_rule_group_id'),
                Tools::getValue('city_rule_id'),
                Tools::getValue('city_rule_type')
            ));
        }
        
        if (Tools::isSubmit('newCityRuleGroup')
            && $city_rule_group_id = Tools::getValue('city_rule_group_id')
        ) {
            die($this->getCityRuleGroupDisplay($city_rule_group_id));
        }
        
        if (Tools::isSubmit('newCityRule')) {
            die($this->getCityRuleDisplay(
                Tools::getValue('zipcode_rule_group_id'),
                Tools::getValue('zipcode_rule_id'),
                Tools::getValue('zipcode_rule_type')
            ));
        }
        
        if (Tools::isSubmit('newZipcodeRuleGroup')
            && $zipcode_rule_group_id = Tools::getValue('zipcode_rule_group_id')
        ) {
            die($this->getZipcodeRuleGroupDisplay($zipcode_rule_group_id));
        }
        
        if (Tools::isSubmit('newZipcodeRule')) {
            die($this->getZipcodeRuleDisplay(
                Tools::getValue('zipcode_rule_group_id'),
                Tools::getValue('zipcode_rule_id'),
                Tools::getValue('zipcode_rule_type')
            ));
        }
        
        if (Tools::isSubmit('newPackageRule')) {
            die($this->getPackageRuleDisplay(
                Tools::getValue('package_rule_group_id'),
                Tools::getValue('package_rule_id'),
                Tools::getValue('package_rule_unit_weight'),
                Tools::getValue('package_rule_range_start'),
                Tools::getValue('package_rule_range_end'),
                Tools::getValue('package_rule_round', 1),
                Tools::getValue('package_rule_divider', 1),
                Tools::getValue('package_rule_currency', ''),
                Tools::getValue('package_rule_tax', true),
                Tools::getValue('package_rule_value', '')
            ));
        }
        
        if (Tools::isSubmit('newPackageRuleGroup')
            && $package_rule_group_id = Tools::getValue('package_rule_group_id')
        ) {
            die($this->getPackageRuleGroupDisplay(
                $package_rule_group_id
            ));
        }

        if (Tools::isSubmit('newProductRule')) {
            die($this->getProductRuleDisplay(
                Tools::getValue('product_rule_group_id'),
                Tools::getValue('product_rule_id'),
                Tools::getValue('product_rule_type')
            ));
        }
        
        if (Tools::isSubmit('newProductRuleGroup')
            && $product_rule_group_id = Tools::getValue('product_rule_group_id')
        ) {
            die($this->getProductRuleGroupDisplay(
                $product_rule_group_id,
                Tools::getValue('product_rule_group_' . (int)$product_rule_group_id . '_quantity', 1)
            ));
        }

        if (Tools::isSubmit('customerFilter')) {
            $search_query = trim(Tools::getValue('q'));
            
            $customers = Db::getInstance()->executeS(
                'SELECT `id_customer`, `email`, CONCAT(`firstname`, \' \', `lastname`) as cname
                    FROM `'._DB_PREFIX_.'customer`
                    WHERE `deleted` = 0 AND is_guest = 0 AND active = 1
                    AND (
                        `id_customer` = ' . (int) $search_query . '
                        OR `email` LIKE "%' . pSQL($search_query) . '%"
                        OR `firstname` LIKE "%' . pSQL($search_query) . '%"
                        OR `lastname` LIKE "%' . pSQL($search_query) . '%"
                    )
                    ORDER BY `firstname`, `lastname` ASC
                LIMIT 50'
            );
            
            die(json_encode($customers));
        }

        if (Tools::isSubmit('productFilter')) {
            $products = Product::searchByName((int)$this->context->language->id, trim(Tools::getValue('q')));
            
            die(json_encode($products));
        }
        
        Hook::exec('actionAdminOrderFeesShippingAjaxProcess', array(
            'controller' => &$this
        ));
    }

    protected function searchProducts($search)
    {
        $id_lang = (int) $this->context->language->id;
        
        if ($products = Product::searchByName($id_lang, $search)) {
            foreach ($products as &$product) {
                $combinations = array();
                $productObj = new Product((int)$product['id_product'], false, $id_lang);
                $attributes = $productObj->getAttributesGroups($id_lang);
                
                $product['formatted_price'] = Tools::displayPrice(
                    Tools::convertPrice($product['price_tax_incl'], $this->context->currency),
                    $this->context->currency
                );

                foreach ($attributes as $attribute) {
                    $id_attribute = $attribute['id_product_attribute'];
                    
                    if (!isset($combinations[$id_attribute]['attributes'])) {
                        $combinations[$id_attribute]['attributes'] = '';
                    }
                    
                    $combinations[$id_attribute]['attributes'] .= $attribute['attribute_name'].' - ';
                    $combinations[$id_attribute]['id_product_attribute'] = $id_attribute;
                    $combinations[$id_attribute]['default_on'] = $attribute['default_on'];
                    
                    if (!isset($combinations[$id_attribute]['price'])) {
                        $price_tax_incl = Product::getPriceStatic((int)$product['id_product'], true, $id_attribute);
                        
                        $combinations[$id_attribute]['formatted_price'] = Tools::displayPrice(
                            Tools::convertPrice($price_tax_incl, $this->context->currency),
                            $this->context->currency
                        );
                    }
                }

                foreach ($combinations as &$combination) {
                    $combination['attributes'] = rtrim($combination['attributes'], ' - ');
                }
                
                $product['combinations'] = $combinations;
            }
            return array(
                'products' => $products,
                'found' => true
            );
        } else {
            return array('found' => false, 'notfound' => Tools::displayError('No product has been found.'));
        }
    }

    public function ajaxProcessSearchProducts()
    {
        $array = $this->searchProducts(Tools::getValue('product_search'));
        
        $this->content = trim(json_encode($array));
    }

    public function renderForm()
    {
        parent::renderForm();
        
        $rule = $this->loadObject(true);
        
        $customer_filter = '';
        
        if (Validate::isUnsignedId($rule->id_customer)
            && ($customer = new Customer($rule->id_customer))
            && Validate::isLoadedObject($customer)
        ) {
            $customer_filter = sprintf('%s %s (%s)', $customer->firstname, $customer->lastname, $customer->email);
        }
        
        $product_filter = '';
        
        if (Validate::isUnsignedId($rule->product)
            && ($product = new Product($rule->product, false, $this->context->language->id))
            && Validate::isLoadedObject($product)
        ) {
            $product_filter = (!empty($product->reference) ? $product->reference : $product->name);
        }
        
        $id_lang = $this->context->language->id;
        
        $this->context->smarty->assign(
            array(
                'title' => array($this->l('Advanced Shipping cost'), $this->l('Rule')),
                'rule' => $rule,
                'show_toolbar' => true,
                'toolbar_btn' => $this->toolbar_btn,
                'toolbar_scroll' => $this->toolbar_scroll,
                'customerFilter' => $customer_filter,
                'productFilter' => $product_filter,
                'defaultCurrency' => Configuration::get('PS_CURRENCY_DEFAULT'),
                'currencies' => Currency::getCurrencies(false, true, true),
                'countries' => $rule->restrictions('country', true, true),
                'zones' => $rule->restrictions('zone', true, false),
                'states' => $rule->restrictions('state', true, false),
                'cities' => $rule->restrictions('city', true, false),
                'carriers' => $rule->restrictions('carrier', true, true),
                'groups' => $rule->restrictions('group', false, true),
                'shops' => $rule->restrictions('shop', false, false),
                'shipping_rules' => $rule->restrictions('of_shipping_rule', false, false),
                'product_rule_groups' => $this->getProductRuleGroupsDisplay($rule),
                'attribute_groups' => AttributeGroup::getAttributesGroups($id_lang),
                'dimension_rule_groups' => $this->getDimensionRuleGroupsDisplay($rule),
                'countries_nb' => count(Country::getCountries($id_lang, true, false, false)),
                'city_rule_groups' => $this->getCityRuleGroupsDisplay($rule),
                'zipcode_rule_groups' => $this->getZipcodeRuleGroupsDisplay($rule),
                'package_rule_groups' => $this->getPackageRuleGroupsDisplay($rule),
                'titles' => $rule->restrictions('gender', false, true),
                'currentIndex' => self::$currentIndex,
                'currentToken' => $this->token,
                'controller' => $this,
                'module' => $this->module,
                'tax_rules_groups' => TaxRulesGroup::getTaxRulesGroups(true),
                'tax_exclude_taxe_option' => Tax::excludeTaxeOption(),
                'ps_weight_unit' => Configuration::get('PS_WEIGHT_UNIT')
            )
        );
        
        Hook::exec('actionAdminOrderFeesShippingRenderForm', array(
            'controller' => &$this,
            'object' => &$rule
        ));

        $this->content = $this->createTemplate('form.tpl')->fetch();
    }
    
    public function getDimensionRuleGroupDisplay(
        $dimension_rule_group_id,
        $dimension_rule_group_base = 'product',
        $dimension_rules = array()
    ) {
        Context::getContext()->smarty->assign('dimension_rule_group_id', $dimension_rule_group_id);
        Context::getContext()->smarty->assign('dimension_rule_group_base', $dimension_rule_group_base);
        Context::getContext()->smarty->assign('dimension_rules', $dimension_rules);
        
        return $this->createTemplate('dimension_rule_group.tpl')->fetch();
    }
    
    public function getDimensionRuleGroupsDisplay($shipping_rule)
    {
        $dimensionRuleGroupsArray = array();
        
        if (Tools::getValue('dimension_restriction')
            && is_array($array = Tools::getValue('dimension_rule_group'))
            && count($array)
        ) {
            $i = 1;
            
            foreach ($array as $ruleGroupId) {
                $dimensionRulesArray = array();
                if (is_array($array = Tools::getValue('dimension_rule_'.(int)$ruleGroupId)) && count($array)) {
                    foreach ($array as $ruleId) {
                        $dimensionRulesArray[] = $this->getDimensionRuleDisplay(
                            $ruleGroupId,
                            $ruleId,
                            Tools::getValue('dimension_rule_'.(int)$ruleGroupId.'_'.(int)$ruleId.'_type'),
                            Tools::getValue('dimension_rule_'.(int)$ruleGroupId.'_'.(int)$ruleId)
                        );
                    }
                }

                $dimensionRuleGroupsArray[] = $this->getDimensionRuleGroupDisplay(
                    $i++,
                    Tools::getValue('dimension_rule_group_base_'.$ruleGroupId),
                    $dimensionRulesArray
                );
            }
        } else {
            $i = 1;
            foreach ($shipping_rule->getDimensionRuleGroups() as $dimensionRuleGroup) {
                $j = 1;
                $dimensionRulesDisplay = array();
                
                foreach ($dimensionRuleGroup['dimension_rules'] as $dimensionRule) {
                    $dimensionRulesDisplay[] = $this->getDimensionRuleDisplay(
                        $i,
                        $j++,
                        $dimensionRule['type'],
                        $dimensionRule['operator'],
                        $dimensionRule['value']
                    );
                }
                
                $dimensionRuleGroupsArray[] = $this->getDimensionRuleGroupDisplay(
                    $i++,
                    $dimensionRuleGroup['base'],
                    $dimensionRulesDisplay
                );
            }
        }
        return $dimensionRuleGroupsArray;
    }
    
    public function getDimensionRuleDisplay(
        $dimension_rule_group_id,
        $dimension_rule_id,
        $dimension_rule_type,
        $dimension_rule_operator = '=',
        $dimension_rule_value = ''
    ) {
        $this->context->smarty->assign(
            array(
                'dimension_rule_group_id' => (int)$dimension_rule_group_id,
                'dimension_rule_id' => (int)$dimension_rule_id,
                'dimension_rule_type' => $dimension_rule_type,
                'id_lang' => (int)$this->context->language->id,
                'operator' => $dimension_rule_operator,
                'value' => $dimension_rule_value,
                'ps_dimension_unit' => Configuration::get('PS_DIMENSION_UNIT'),
                'ps_weight_unit' => Configuration::get('PS_WEIGHT_UNIT')
            )
        );

        if (Tools::getValue('dimension_restriction')) {
            $this->context->smarty->assign(
                array(
                    'operator' => Tools::getValue(
                        'dimension_rule_'.$dimension_rule_group_id.'_'.$dimension_rule_id.'_operator'
                    ),
                    'value' => Tools::getValue(
                        'dimension_rule_'.$dimension_rule_group_id.'_'.$dimension_rule_id.'_value'
                    )
                )
            );
        }

        return $this->createTemplate('dimension_rule.tpl')->fetch();
    }
    
    public function getCityRuleGroupDisplay(
        $city_rule_group_id,
        $city_rules = array()
    ) {
        Context::getContext()->smarty->assign('city_rule_group_id', $city_rule_group_id);
        Context::getContext()->smarty->assign(
            'city_countries',
            Country::getCountries($this->context->language->id, true, false, false)
        );
        Context::getContext()->smarty->assign('city_rules', $city_rules);
        
        return $this->createTemplate('city_rule_group.tpl')->fetch();
    }
    
    public function getCityRuleGroupsDisplay($shipping_rule)
    {
        $cityRuleGroupsArray = array();
        
        if (Tools::getValue('city_restriction')
            && is_array($array = Tools::getValue('city_rule_group'))
            && count($array)
        ) {
            $i = 1;
            
            foreach ($array as $ruleGroupId) {
                $cityRulesArray = array();
                if (is_array($array = Tools::getValue('city_rule_'.$ruleGroupId)) && count($array)) {
                    foreach ($array as $ruleId) {
                        $cityRulesArray[] = $this->getCityRuleDisplay(
                            $ruleGroupId,
                            $ruleId,
                            Tools::getValue('city_rule_'.$ruleGroupId.'_'.$ruleId.'_type'),
                            Tools::getValue('city_rule_'.$ruleGroupId.'_'.$ruleId)
                        );
                    }
                }

                $cityRuleGroupsArray[] = $this->getCityRuleGroupDisplay(
                    $i++,
                    $cityRulesArray
                );
            }
        } else {
            $i = 1;
            foreach ($shipping_rule->getCityRuleGroups() as $cityRuleGroup) {
                $j = 1;
                $cityRulesDisplay = array();
                
                foreach ($cityRuleGroup['city_rules'] as $cityRule) {
                    $cityRulesDisplay[] = $this->getCityRuleDisplay(
                        $i,
                        $j++,
                        $cityRule['type'],
                        $cityRule['value']
                    );
                }
                
                $cityRuleGroupsArray[] = $this->getCityRuleGroupDisplay(
                    $i++,
                    $cityRulesDisplay
                );
            }
        }
        return $cityRuleGroupsArray;
    }
    
    public function getCityRuleDisplay(
        $city_rule_group_id,
        $city_rule_id,
        $city_rule_type,
        $city_rule_value = ''
    ) {
        $this->context->smarty->assign(
            array(
                'city_rule_group_id' => (int)$city_rule_group_id,
                'city_rule_id' => (int)$city_rule_id,
                'city_rule_type' => $city_rule_type,
                'id_lang' => (int)$this->context->language->id,
                'value' => $city_rule_value
            )
        );

        if (Tools::getValue('city_restriction')) {
            $this->context->smarty->assign(
                array(
                    'value' => Tools::getValue(
                        'city_rule_'.$city_rule_group_id.'_'.$city_rule_id.'_value'
                    )
                )
            );
        }

        return $this->createTemplate('city_rule.tpl')->fetch();
    }
    
    public function getZipcodeRuleGroupDisplay(
        $zipcode_rule_group_id,
        $zipcode_rules = array()
    ) {
        Context::getContext()->smarty->assign('zipcode_rule_group_id', $zipcode_rule_group_id);
        Context::getContext()->smarty->assign(
            'zipcode_countries',
            Country::getCountries($this->context->language->id, true, false, false)
        );
        Context::getContext()->smarty->assign('zipcode_rules', $zipcode_rules);
        
        return $this->createTemplate('zipcode_rule_group.tpl')->fetch();
    }
    
    public function getZipcodeRuleGroupsDisplay($shipping_rule)
    {
        $zipcodeRuleGroupsArray = array();
        
        if (Tools::getValue('zipcode_restriction')
            && is_array($array = Tools::getValue('zipcode_rule_group'))
            && count($array)
        ) {
            $i = 1;
            
            foreach ($array as $ruleGroupId) {
                $zipcodeRulesArray = array();
                if (is_array($array = Tools::getValue('zipcode_rule_'.$ruleGroupId)) && count($array)) {
                    foreach ($array as $ruleId) {
                        $zipcodeRulesArray[] = $this->getZipcodeRuleDisplay(
                            $ruleGroupId,
                            $ruleId,
                            Tools::getValue('zipcode_rule_'.$ruleGroupId.'_'.$ruleId.'_type'),
                            Tools::getValue('zipcode_rule_'.$ruleGroupId.'_'.$ruleId)
                        );
                    }
                }

                $zipcodeRuleGroupsArray[] = $this->getZipcodeRuleGroupDisplay(
                    $i++,
                    $zipcodeRulesArray
                );
            }
        } else {
            $i = 1;
            foreach ($shipping_rule->getZipcodeRuleGroups() as $zipcodeRuleGroup) {
                $j = 1;
                $zipcodeRulesDisplay = array();
                
                foreach ($zipcodeRuleGroup['zipcode_rules'] as $zipcodeRule) {
                    $zipcodeRulesDisplay[] = $this->getZipcodeRuleDisplay(
                        $i,
                        $j++,
                        $zipcodeRule['type'],
                        $zipcodeRule['operator'],
                        $zipcodeRule['value']
                    );
                }
                
                $zipcodeRuleGroupsArray[] = $this->getZipcodeRuleGroupDisplay(
                    $i++,
                    $zipcodeRulesDisplay
                );
            }
        }
        return $zipcodeRuleGroupsArray;
    }
    
    public function getZipcodeRuleDisplay(
        $zipcode_rule_group_id,
        $zipcode_rule_id,
        $zipcode_rule_type,
        $zipcode_rule_operator = '=',
        $zipcode_rule_value = ''
    ) {
        $this->context->smarty->assign(
            array(
                'zipcode_rule_group_id' => (int)$zipcode_rule_group_id,
                'zipcode_rule_id' => (int)$zipcode_rule_id,
                'zipcode_rule_type' => $zipcode_rule_type,
                'id_lang' => (int)$this->context->language->id,
                'operator' => $zipcode_rule_operator,
                'value' => $zipcode_rule_value
            )
        );

        if (Tools::getValue('zipcode_restriction')) {
            $this->context->smarty->assign(
                array(
                    'operator' => Tools::getValue(
                        'zipcode_rule_'.$zipcode_rule_group_id.'_'.$zipcode_rule_id.'_operator'
                    ),
                    'value' => Tools::getValue(
                        'zipcode_rule_'.$zipcode_rule_group_id.'_'.$zipcode_rule_id.'_value'
                    )
                )
            );
        }

        return $this->createTemplate('zipcode_rule.tpl')->fetch();
    }
    
    public function getPackageRuleGroupDisplay(
        $package_rule_group_id,
        $package_rule_group_unit = 'kg/m3',
        $package_rule_group_unit_weight = 'kg',
        $package_rule_group_ratio = '',
        $package_rules = array()
    ) {
        $this->context->smarty->assign('package_rule_group_id', $package_rule_group_id);
        $this->context->smarty->assign('package_rule_group_unit', $package_rule_group_unit);
        $this->context->smarty->assign('package_rule_group_unit_weight', $package_rule_group_unit_weight);
        $this->context->smarty->assign('package_rule_group_ratio', $package_rule_group_ratio);
        $this->context->smarty->assign('package_rules', $package_rules);
        
        return $this->createTemplate('package_rule_group.tpl')->fetch();
    }
    
    public function getPackageRuleGroupsDisplay($shipping_rule)
    {
        $packageRuleGroupsArray = array();
        
        if (Tools::getValue('package_restriction')
            && is_array($array = Tools::getValue('package_rule_group'))
            && count($array)
        ) {
            $i = 1;
            
            foreach ($array as $ruleGroupId) {
                $packageRulesArray = array();
                if (is_array($array = Tools::getValue('package_rule_'.$ruleGroupId)) && count($array)) {
                    foreach ($array as $ruleId) {
                        $packageRulesArray[] = $this->getPackageRuleDisplay(
                            $ruleGroupId,
                            $ruleId,
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_unit_weight'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_range_start'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_range_end'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_round'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_divider'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_currency'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_tax'),
                            Tools::getValue('package_rule_'.$ruleGroupId.'_'.$ruleId.'_value')
                        );
                    }
                }

                $packageRuleGroupsArray[] = $this->getPackageRuleGroupDisplay(
                    $i++,
                    Tools::getValue('package_rule_group_unit_'.$ruleGroupId),
                    Tools::getValue('package_rule_group_unit_weight_'.$ruleGroupId),
                    Tools::getValue('package_rule_group_ratio_'.$ruleGroupId),
                    $packageRulesArray
                );
            }
        } else {
            $i = 1;
            foreach ($shipping_rule->getPackageRuleGroups() as $packageRuleGroup) {
                $j = 1;
                $packageRulesDisplay = array();
                
                foreach ($packageRuleGroup['package_rules'] as $packageRule) {
                    $packageRulesDisplay[] = $this->getPackageRuleDisplay(
                        $i,
                        $j++,
                        $packageRuleGroup['unit_weight'],
                        $packageRule['range_start'],
                        $packageRule['range_end'],
                        $packageRule['round'],
                        $packageRule['divider'],
                        $packageRule['currency'],
                        $packageRule['tax'],
                        $packageRule['value']
                    );
                }
                
                $packageRuleGroupsArray[] = $this->getPackageRuleGroupDisplay(
                    $i++,
                    $packageRuleGroup['unit'],
                    $packageRuleGroup['unit_weight'],
                    $packageRuleGroup['ratio'],
                    $packageRulesDisplay
                );
            }
        }
        return $packageRuleGroupsArray;
    }
    
    public function getPackageRuleDisplay(
        $package_rule_group_id,
        $package_rule_id,
        $package_rule_unit_weight,
        $package_rule_range_start,
        $package_rule_range_end,
        $package_rule_round = 1,
        $package_rule_divider = 1,
        $package_rule_currency = '',
        $package_rule_tax = true,
        $package_rule_value = ''
    ) {
        (bool)$package_rule_currency;
        
        $this->context->smarty->assign(
            array(
                'package_rule_group_id' => (int)$package_rule_group_id,
                'package_rule_id' => (int)$package_rule_id,
                'unit_weight' => $package_rule_unit_weight,
                'range_start' => $package_rule_range_start,
                'range_end' => $package_rule_range_end,
                'round' => $package_rule_round,
                'divider' => $package_rule_divider,
                'id_lang' => (int)$this->context->language->id,
                'selected_currency' => $this->module->getActualCurrency(),
                'tax' => $package_rule_tax,
                'value' => $package_rule_value
            )
        );

        if (Tools::getValue('package_restriction')) {
            $this->context->smarty->assign(
                array(
                    'round' => Tools::getValue(
                        'package_rule_'.(int)$package_rule_group_id.'_'.(int)$package_rule_id.'_round'
                    ),
                    'divided' => Tools::getValue(
                        'package_rule_'.(int)$package_rule_group_id.'_'.(int)$package_rule_id.'_divider'
                    ),
                    'currency' => Tools::getValue(
                        'package_rule_'.(int)$package_rule_group_id.'_'.(int)$package_rule_id.'_currency'
                    ),
                    'tax' => Tools::getValue(
                        'package_rule_'.(int)$package_rule_group_id.'_'.(int)$package_rule_id.'_tax'
                    ),
                    'value' => Tools::getValue(
                        'package_rule_'.(int)$package_rule_group_id.'_'.(int)$package_rule_id.'_value'
                    )
                )
            );
        }
        
        return $this->createTemplate('package_rule.tpl')->fetch();
    }
    
    public function getProductRuleDisplay(
        $product_rule_group_id,
        $product_rule_id,
        $product_rule_type,
        $selected = array()
    ) {
        Context::getContext()->smarty->assign(
            array(
                'product_rule_group_id' => (int)$product_rule_group_id,
                'product_rule_id' => (int)$product_rule_id,
                'product_rule_type' => $product_rule_type,
            )
        );
        
        $id_lang = (int) Context::getContext()->language->id;

        switch ($product_rule_type) {
            case 'features':
                $attributes = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT CONCAT(fl.name, " - ", fvl.value) as name, fv.id_feature_value as id
				FROM '._DB_PREFIX_.'feature_value_lang fvl
				LEFT JOIN '._DB_PREFIX_.'feature_value fv ON fv.id_feature_value = fvl.id_feature_value
				LEFT JOIN '._DB_PREFIX_.'feature_lang fl
                    ON (fv.id_feature = fl.id_feature AND fl.id_lang = ' . (int)$id_lang . ')
				WHERE fvl.id_lang = ' . (int)$id_lang . '
				ORDER BY fl.name, fvl.value');
                
                foreach ($results as $row) {
                    $attributes[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $attributes);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'attributes':
                $attributes = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT CONCAT(agl.name, " - ", al.name) as name, a.id_attribute as id
				FROM '._DB_PREFIX_.'attribute_group_lang agl
				LEFT JOIN '._DB_PREFIX_.'attribute a ON a.id_attribute_group = agl.id_attribute_group
				LEFT JOIN '._DB_PREFIX_.'attribute_lang al
                    ON (a.id_attribute = al.id_attribute AND al.id_lang = ' . (int)$id_lang . ')
				WHERE agl.id_lang = ' . (int)$id_lang . '
				ORDER BY agl.name, al.name');
                
                foreach ($results as $row) {
                    $attributes[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $attributes);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'products':
                $display_sku = (bool)Configuration::get('MS_ORDERFEES_SHIPPING_CONDITIONS_DISPLAY_SKU');
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT DISTINCT ' . ($display_sku ? 'CONCAT(p.reference, " - ", name) AS name' : 'name') . ',
                    p.id_product as id
				FROM '._DB_PREFIX_.'product p
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON (p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl').')
				'.Shop::addSqlAssociation('product', 'p').'
				WHERE id_lang = ' . (int)$id_lang . '
				ORDER BY name');
                
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $products);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'manufacturers':
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT name, id_manufacturer as id
				FROM '._DB_PREFIX_.'manufacturer
				ORDER BY name');
                
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $products);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'suppliers':
                $products = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT name, id_supplier as id
				FROM '._DB_PREFIX_.'supplier
				ORDER BY name');
                
                foreach ($results as $row) {
                    $products[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $products);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            case 'categories':
            case 'main_categories':
                $categories = array('selected' => array(), 'unselected' => array());
                $results = Db::getInstance()->executeS('
				SELECT DISTINCT CONCAT(REPEAT("- ", c.level_depth), cl.name) AS name, c.id_category as id
				FROM '._DB_PREFIX_.'category c
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
					ON (c.`id_category` = cl.`id_category`
					AND cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl').')
				'.Shop::addSqlAssociation('category', 'c').'
				WHERE cl.id_lang = ' . (int)$id_lang . '
				ORDER BY c.nleft');
                
                foreach ($results as $row) {
                    $categories[in_array($row['id'], $selected) ? 'selected' : 'unselected'][] = $row;
                }
                
                Context::getContext()->smarty->assign('product_rule_itemlist', $categories);
                $choose_content = $this->createTemplate('product_rule_itemlist.tpl')->fetch();
                Context::getContext()->smarty->assign('product_rule_choose_content', $choose_content);
                
                break;
            default:
                Context::getContext()->smarty->assign(
                    'product_rule_itemlist',
                    array('selected' => array(), 'unselected' => array())
                );
                
                Context::getContext()->smarty->assign('product_rule_choose_content', '');
        }

        return $this->createTemplate('product_rule.tpl')->fetch();
    }
    
    public function displayDuplicateLink($token = null, $id = null, $name = null)
    {
        (bool)$name;
        
        $tpl = $this->createTemplate('list_action_duplicate.tpl');
        
        if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
            self::$cache_lang['Duplicate'] = $this->l('Duplicate', 'Helper');
        }

        $duplicate = self::$currentIndex.'&'.$this->identifier.'='.$id.'&duplicate'.$this->table;

        $tpl->assign(array(
            'href' => self::$currentIndex.'&'.$this->identifier.'='.$id
                    .'&view'.$this->table.'&token='.($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Duplicate'],
            'location_ok' => $duplicate.'&token='.($token != null ? $token : $this->token)
        ));

        return $tpl->fetch();
    }
}
