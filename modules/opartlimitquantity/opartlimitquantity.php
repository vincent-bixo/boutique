<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Opartlimitquantity extends Module
{
    protected $post_error = array();
    protected $post_conf = array();

    public function __construct()
    {
        $this->name = 'opartlimitquantity';
        $this->tab = 'front_office_features';
        $this->version = '1.4.10';

        $this->author = '"Réussir mon ecommerce';
        $this->need_instance = 0;
        $this->module_key = "86b20b1f806a37c8f97ac1e9bb7aba01";
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Advanced quantity management');
        $this->description = $this->l('Limit quantity by product');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');
    }

    public function install()
    {
        if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
            return false;
        }

        // Install Module
        if (parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductActions')
            && $this->registerHook('actionFrontControllerSetMedia')
        ) {
            // Install SQL
            $sql = include(dirname(__FILE__) . '/sql/install.php');
            foreach ($sql as $s) {
                if (!Db::getInstance()->execute($s)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function uninstall()
    {
        // Uninstall SQL
        $sql = include(dirname(__FILE__) . '/sql/uninstall.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->execute($s)) {
                return false;
            }
        }

        Configuration::deleteByName('OPARTLIMITQUANTITY_CHECK_DIF');
        Configuration::deleteByName('OPARTLIMITQUANTITY_MAX');
        Configuration::deleteByName('OPARTLIMITQUANTITY_MIN');


        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $this->_postProcess();

        $output = '';
        if (count($this->post_error) > 0) {
            foreach ($this->post_error as $err) {
                $output .= $this->displayError($err);
            }
        } elseif (count($this->post_conf) > 0) {
            foreach ($this->post_conf as $conf) {
                $output .= $this->displayConfirmation($conf);
            }
        }

        $this->context->smarty->assign([
            'moduledir' => $this->_path,
            'doc_lang' => $this->context->language->iso_code == 'fr' ? 'fr' : 'en',
        ]);
        $output .= $this->renderForm();
        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/footer.tpl');

        return $output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitOpartLimitQuantity';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General cart settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6') < 0) ? 'radio' : 'switch',
                        'label' => $this->l('check different products only'),
                        'name' => 'OPARTLIMITQUANTITY_CHECK_DIF',
                        'class' => 't',
                        'is_bool' => true,
                        'desc' => $this->l('If you turn on this feature, quantity will be ignored and only different products will be cumulated'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'OPARTLIMITQUANTITY_MAX',
                        'label' => $this->l('Maximum number of products in the cart'),
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'name' => 'OPARTLIMITQUANTITY_MIN',
                        'label' => $this->l('Minimum number of products in the cart'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'OPARTLIMITQUANTITY_CHECK_DIF' => Configuration::get('OPARTLIMITQUANTITY_CHECK_DIF'),
            'OPARTLIMITQUANTITY_MAX' => Configuration::get('OPARTLIMITQUANTITY_MAX', null, null, null,0),
            'OPARTLIMITQUANTITY_MIN' => Configuration::get('OPARTLIMITQUANTITY_MIN', null, null, null,0),
        ];
    }

    /**
     * Save form data.
     */
    protected function _postProcess()
    {
        if (Tools::isSubmit('submitOpartLimitQuantity')) {
            $form_values = $this->getConfigFormValues();

            foreach (array_keys($form_values) as $key) {
                if (Validate::isInt(Tools::getValue($key)) || Tools::getValue($key) == '') {
                    Configuration::updateValue($key, Tools::getValue($key));
                } else {
                    $this->post_error[] = $this->l('Quantities need to be numeric values');
                }
            }

            $this->post_conf[] = $this->l('The settings have been updated.');
        }
    }

    public function getErrorText($txt_id, $value, $productName = null)
    {
        switch ($txt_id) {
            case 1:
                return sprintf($this->l('You can not add more than %d different products in your cart'), $value);
                break;
            case 2:
                return sprintf($this->l('You can not add more than %d products in your cart'), $value);
                break;
            case 3:
                return sprintf($this->l('You must add at least %d different products in your cart'), $value);
                break;
            case 4:
                return sprintf($this->l('You must add at least %d products in your cart'), $value);
                break;
            case 5:
                if ($productName) {
                    return sprintf($this->l('The maximum quantity permitted for %s is %d.'), $productName, $value);
                } else {
                    return sprintf($this->l('The maximum quantity permitted for this product is %d.'), $value);
                }
                break;
            case 6:
                if ($productName) {
                    return sprintf($this->l('The minimum quantity permitted for %s is %d'), $productName, $value);
                } else {
                    return sprintf($this->l('The minimum quantity permitted for this product is %d'), $value);
                }
                break;
            case 7:
                if ($productName) {
                    return sprintf($this->l('You can only buy the product %s in batches of %s'), $productName, $value);
                } else {
                    return sprintf($this->l('You can only buy this product in batches of %s'), $value);
                }
                break;
        }
    }

    public function hookDisplayHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->context->controller->registerJavascript('opartlimitquantity', 'modules/' . $this->name . '/views/js/cart.js', ['priority' => 9999]);
        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if (Tools::getIsset('id_product')) {
            $id_product = (int)Tools::getValue('id_product');
        } else {
            $id_product = (int)$params['id_product'];
        }

        $hideBottomSaveButton = false;
        if (version_compare(_PS_VERSION_, '1.6', '<')
            || version_compare(_PS_VERSION_, '1.7', '>=')
        ) {
            $hideBottomSaveButton = true;
        }

        $product = new Product($id_product);
        $declinations = $product->getAttributesResume($this->context->language->id);
        $this->smarty->assign([
            'hideBottomSaveButton' => $hideBottomSaveButton,
            'product' => $product,
            'declinations' => $declinations,
            'batches' => $this->getBatches($product->id),
        ]);

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $this->display(__FILE__, 'views/templates/admin/tab-body_17.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/admin/tab-body.tpl');
        }
    }

    public function hookActionProductUpdate($params)
    {
        // get all languages
        // for each of them, store the new field

        $id_product = (int)Tools::getValue('id_product');
        $opartsingleproduct = (!Tools::getIsset('opartsingleproduct') || (int)Tools::getValue('opartsingleproduct'));
        if ($opartsingleproduct) {
            $max = (int)Tools::getValue('opartMaxQuantity');
            $min = (int)Tools::getValue('opartMinQuantity');
        } else {
            $max = '';
            $min = '';
        }
        /* Db::getInstance()->update(
            'product',
            [
                'opart_max_qty' => $max,
                'opart_min_qty' => $min,
            ],
            'id_product=' . $id_product,
            0,
            true
        ); */
        Db::getInstance()->update(
            'product',
            [
                'opart_max_qty' => $max,
                'opart_min_qty' => $min,
            ],
            'id_product=' . (int)$id_product,
            0,
            true
        );

        if (Tools::getIsset('opartMaxQuantity_attr') && Tools::getIsset('opartMinQuantity_attr')) {
            $productAttributesQty = [];
            foreach (Tools::getValue('opartMaxQuantity_attr') as $id_product_attribute => $maxQty) {
                $productAttributesQty[$id_product_attribute] = ['opart_max_qty' => (int)$maxQty];
            }
            foreach (Tools::getValue('opartMinQuantity_attr') as $id_product_attribute => $minQty) {
                $productAttributesQty[$id_product_attribute]['opart_min_qty'] = (int)$minQty;
            }
            if (count($productAttributesQty)) {
                foreach ($productAttributesQty as $id_product_attribute => $qtys) {
                    /* Db::getInstance()->update(
                        'product_attribute',
                        [
                            'opart_max_qty' => $qtys['opart_max_qty'],
                            'opart_min_qty' => $qtys['opart_min_qty'],
                        ],
                        'id_product = ' . $id_product . ' AND id_product_attribute = ' . (int)$id_product_attribute
                    ); */
                    Db::getInstance()->update(
                        'product_attribute',
                        [
                            'opart_max_qty' => (int)$qtys['opart_max_qty'],
                            'opart_min_qty' => (int)$qtys['opart_min_qty'],
                        ],
                        'id_product = ' . (int)$id_product . ' AND id_product_attribute = ' . (int)$id_product_attribute
                    );
                }
            }
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            /* Db::getInstance()->update('product', ['minimal_quantity' => 1], 'id_product=' . $id_product);
            Db::getInstance()->update('product_shop', ['minimal_quantity' => 1], 'id_product=' . $id_product);
            Db::getInstance()->update('product_attribute', ['minimal_quantity' => 1], 'id_product=' . $id_product);
            Db::getInstance()->update('product_attribute_shop', ['minimal_quantity' => 1], 'id_product=' . $id_product); */
            $db = Db::getInstance();
            $db->update('product', ['minimal_quantity' => 1], 'id_product=' . (int)$id_product);
            $db->update('product_shop', ['minimal_quantity' => 1], 'id_product=' . (int)$id_product);
            $db->update('product_attribute', ['minimal_quantity' => 1], 'id_product=' . (int)$id_product);
            $db->update('product_attribute_shop', ['minimal_quantity' => 1], 'id_product=' . (int)$id_product);
        }

        if (Tools::getIsset('batches')) {
            $batches = Tools::getValue('batches');
            $productBatches = [];
            $productAttributeBatches = [];
            foreach ($batches as $id_product_attribute => $quantities) {
                foreach ($quantities['quantity'] as $key => $quantity) {
                    if ((int)$quantity) {
                        if ($id_product_attribute) {
                            $productAttributeBatches[] = [
                                'id_product' => (int)$id_product,
                                'id_product_attribute' => (int)$id_product_attribute,
                                'batch_type' => pSQL($quantities['type'][$key]),
                                'quantity' => (int)$quantity,
                            ];
                        } else {
                            $productBatches[] = [
                                'id_product' => (int)$id_product,
                                'batch_type' => pSQL($quantities['type'][$key]),
                                'quantity' => (int)$quantity,
                            ];
                        }
                    }
                }
            }
            Db::getInstance()->delete('opartlimitquantity_product_attribute_batch', '`id_product` = ' . (int)$id_product);
            if (count($productAttributeBatches)) {
                Db::getInstance()->insert('opartlimitquantity_product_attribute_batch', $productAttributeBatches, false, true, Db::INSERT_IGNORE);
            }
            Db::getInstance()->delete('opartlimitquantity_product_batch', '`id_product` = ' . (int)$id_product);
            if (count($productBatches)) {
                Db::getInstance()->insert('opartlimitquantity_product_batch', $productBatches, false, true, Db::INSERT_IGNORE);
            }
        }
        else {

                        Db::getInstance()->delete('opartlimitquantity_product_attribute_batch', '`id_product` = ' . (int)$id_product);           

                        Db::getInstance()->delete('opartlimitquantity_product_batch', '`id_product` = ' . (int)$id_product);

          }
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $file = Tools::getValue('controller');
        if (!in_array($file, array('AdminProducts'))) {
            return;
        }

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $this->context->controller->addJquery();
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/opartlimitquantity.css');
            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/opartlimitquantity16.js');
            Media::addJsDef(['multiple_of' => $this->l('Multiple of')]);
            Media::addJsDef(['fixed_quantity' => $this->l('Fixed quantity')]);
            Media::addJsDef(['defaultQuantityDesactivatedMessage' => sprintf($this->l('This quantity will not be used because the minimum quantity is managed by the "% s" module.'), $this->l('Advanced quantity management'))]);
            Media::addJsDef(['defaultQuantityConfigureMessage' => sprintf($this->l('Click here to manage your quantities using the "%s" module.'), $this->l('Advanced quantity management'))]);
        }
    }

    public function hookActionFrontControllerSetMedia($params)
    {


        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/opartlimitquantityfront.css');
        }
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $file = Tools::getValue('controller');
        if (!in_array($file, array('AdminProducts'))) {
            return;
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/opartlimitquantity.css');
            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/opartlimitquantity.js');
            Media::addJsDef(['multiple_of' => $this->l('Multiple of')]);
            Media::addJsDef(['fixed_quantity' => $this->l('Fixed quantity')]);
            Media::addJsDef(['defaultQuantityDesactivatedMessage' => sprintf($this->l('This quantity will not be used because the minimum quantity is managed by the "% s" module.'), $this->l('Advanced quantity management'))]);
            Media::addJsDef(['defaultQuantityConfigureMessage' => sprintf($this->l('Click here to manage your quantities using the "%s" module.'), $this->l('Advanced quantity management'))]);
        }
    }

    public function checkCartQuantities()
    {
        $errors = [];

        if (!Tools::getIsset('add')) {
            $opartquantitylimitMax = Configuration::get('OPARTLIMITQUANTITY_MAX');
            $opartquantitylimitMin = Configuration::get('OPARTLIMITQUANTITY_MIN');

            if ($opartquantitylimitMax > 0) {
                if (Configuration::get('OPARTLIMITQUANTITY_CHECK_DIF') == 1) {
                    if (count($this->context->cart->getProducts()) > $opartquantitylimitMax) {
                        $errors[] = $this->getErrorText(1, $opartquantitylimitMax);
                    }
                } else {
                    if ($this->context->cart->nbProducts() > $opartquantitylimitMax) {
                        $errors[] = $this->getErrorText(2, $opartquantitylimitMax);
                    }
                }
            }
            if ($opartquantitylimitMin > 0) {
                if (Configuration::get('OPARTLIMITQUANTITY_CHECK_DIF') == 1) {
                    if (count($this->context->cart->getProducts()) < $opartquantitylimitMin) {
                        $errors[] = $this->getErrorText(3, $opartquantitylimitMin);
                    }
                } else {
                    if ($this->context->cart->nbProducts() < $opartquantitylimitMin) {
                        $errors[] = $this->getErrorText(4, $opartquantitylimitMin);
                    }
                }
            }

            if (count($errors)) {
                return $errors;
            }
        }

        return false;
    }

        public function checkProductQty($product, $id_product_attribute, $qty_to_check)
    {

        $access = $this->getAuthorizedModules($this->context->customer->id_default_group);
        $found = false;
        foreach ($access as $module) {
            if ($module['id_module'] == $this->id) {
                $found = true;
                break;
            }
        }
      
      if($found){

        $errors = [];
        if ($id_product_attribute && $product->opart_max_qty === null && $product->opart_min_qty === null) {
            $declinationsQties = Db::getInstance()->getRow('SELECT opart_max_qty, opart_min_qty FROM `' . _DB_PREFIX_ . 'product_attribute` WHERE `id_product_attribute` = ' . (int)$id_product_attribute);
            $productName = Product::getProductName($product->id, $id_product_attribute, $this->context->language->id);
            if ($declinationsQties['opart_max_qty'] > 0 && $declinationsQties['opart_max_qty'] < $qty_to_check) {
                $errors[] = $this->getErrorText(5, $declinationsQties['opart_max_qty'], $productName);
            }
            if ($declinationsQties['opart_min_qty'] > 0 && $declinationsQties['opart_min_qty'] > $qty_to_check) {
                $errors[] = $this->getErrorText(6, $declinationsQties['opart_min_qty'], $productName);
            }

            /** Batches */
            if ($batchError = $this->checkBatches($product, $id_product_attribute, $qty_to_check, $productName)) {
                $errors[] = $batchError;
            }
        } else {
            if ($id_product_attribute) {
                $productQuantities = 0;
                $cartProducts = $this->context->cart->getProducts();
                foreach ($cartProducts as $cartProduct) {
                    if ($cartProduct['id_product'] == $product->id && $cartProduct['id_product_attribute'] != $id_product_attribute) {
                        $productQuantities += $cartProduct['cart_quantity'];
                    }
                }
                $qty_to_check += $productQuantities;
            }
            $productName = Product::getProductName($product->id, $id_product_attribute, $this->context->language->id);

            if ($product->opart_max_qty > 0 && $product->opart_max_qty < $qty_to_check) {
                $errors[] = $this->getErrorText(5, $product->opart_max_qty, $productName);
            }
            if ($product->opart_min_qty > 0 && $product->opart_min_qty > $qty_to_check) {
                $errors[] = $this->getErrorText(6, $product->opart_min_qty, $productName);
            }

            /** Batches */
            if ($batchError = $this->checkBatches($product, 0, $qty_to_check, $productName)) {
                $errors[] = $batchError;
            }
        }

        if (count($errors)) {
            if (!Tools::getIsset('op')) {
                $productQuantities = 0;
                $cartProducts = $this->context->cart->getProducts();
                foreach ($cartProducts as $cartProduct) {
                    if ($id_product_attribute) {
                        if ($id_product_attribute == $cartProduct['id_product_attribute']) {
                            $productQuantities += $cartProduct['cart_quantity'];
                        }
                    } elseif ($cartProduct['id_product'] == $product->id) {
                        $productQuantities += $cartProduct['cart_quantity'];
                    }
                }
                if ($productQuantities) {
                    $errors[] = sprintf($this->l('You already have the product %s %s times in your cart'), $productName, $productQuantities);
                }
            }

            return $errors;
        }

        return false;

      }


        
    }

    private function getBatches($id_product)
    {
        $batches = [];
        $batches[0] = [];
        /* $rows = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'opartlimitquantity_product_batch` WHERE `id_product` = ' . $id_product . ' ORDER BY `quantity`'); */
        $rows = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'opartlimitquantity_product_batch` WHERE `id_product` = ' . (int)$id_product . ' ORDER BY `quantity`');
        foreach ($rows as $row) {
            $batches[0][] = [
                'batch_type' => $row['batch_type'],
                'quantity' => $row['quantity'],
            ];
        }
        /* $rows = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'opartlimitquantity_product_attribute_batch` WHERE `id_product` = ' . $id_product . ' ORDER BY `quantity`'); */
        $rows = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'opartlimitquantity_product_attribute_batch` WHERE `id_product` = ' . (int)$id_product . ' ORDER BY `quantity`');
        foreach ($rows as $row) {
            if (!isset($batches[$row['id_product_attribute']])) {
                $batches[$row['id_product_attribute']] = [];
            }
            $batches[$row['id_product_attribute']][] = [
                'batch_type' => $row['batch_type'],
                'quantity' => $row['quantity'],
            ];
        }

        return $batches;
    }

    private function checkBatches($product, $id_product_attribute, $qty_to_check, $productName)
    {
        $batches = $this->getBatches($product->id);
        if (isset($batches[$id_product_attribute]) && count($batches[$id_product_attribute])) {
            $isCorrectBatch = false;
            $values = [
                'multiple' => [],
                'fixed' => [],
            ];
            foreach ($batches[$id_product_attribute] as $batch) {
                switch ($batch['batch_type']) {
                    case 'multiple':
                        $values['multiple'][] = $batch['quantity'];
                        if ($qty_to_check % $batch['quantity'] == 0) {
                            $isCorrectBatch = true;
                        }
                        break;
                    case 'fixed':
                        $values['fixed'][] = $batch['quantity'];
                        if ($qty_to_check == $batch['quantity']) {
                            $isCorrectBatch = true;
                        }
                        break;
                }
            }

            if (!$isCorrectBatch) {
                $isCorrectBatch = $this->checkMultipleBatches($batches, $id_product_attribute, $qty_to_check);
            }

            if (!$isCorrectBatch) {
                if (count($values['multiple']) && count($values['fixed'])) {
                    return sprintf($this->l('You can only buy the product %s in multiple of %s or quantities of %s'), $productName, implode(' ' . $this->l('or of') . ' ', $values['multiple']), implode(' ' . $this->l('or of') . ' ', $values['fixed']));
                } elseif (count($values['fixed'])) {
                    return sprintf($this->l('You can only buy the product %s in quantities of %s'), $productName, implode(' ' . $this->l('or of') . ' ', $values['fixed']));
                } elseif (count($values['multiple'])) {
                    return sprintf($this->l('You can only buy the product %s in multiple of %s'), $productName, implode(' ' . $this->l('or of') . ' ', $values['multiple']));
                }
            }
        }

        return false;
    }

    private function checkMultipleBatches($batches, $id_product_attribute, $qty_to_check)
    {
        return false;

        $multiples = [];
        $fixed = [];
        foreach ($batches[$id_product_attribute] as $batch) {
            switch ($batch['batch_type']) {
                case 'multiple':
                    $multiples[] = $batch['quantity'];
                    break;
                case 'fixed':
                    $fixed[] = $batch['quantity'];
                    break;
            }
        }

        foreach ($multiples as $multiple) {
            $modulo = $qty_to_check % $multiple;
            if ($modulo) {
                foreach ($multiples as $multiple2) {
                    if ($multiple2 != $multiple) {
                        $modulo2 = $modulo % $multiple2;
                        if ($modulo2 == 0) {
                            return true;
                        }
                    }
                }
            }
        }

        foreach ($multiples as $multiple) {
            $modulo = $qty_to_check - $multiple;
            if ($modulo > 0) {
                foreach ($multiples as $multiple2) {
                    $modulo2 = $modulo - $multiple2;
                    if ($modulo2 > 0) {
                        foreach ($multiples as $multiple3) {
                            $modulo3 = $modulo2 - $multiple3;
                            if ($modulo3 > 0) {
                                foreach ($multiples as $multiple4) {
                                    $modulo4 = $modulo3 - $multiple4;
                                    if ($modulo4 > 0) {
                                        foreach ($multiples as $multiple5) {
                                            $modulo5 = $modulo4 - $multiple5;
                                            if ($modulo5 > 0) {
                                                foreach ($multiples as $multiple6) {
                                                    $modulo6 = $modulo5 - $multiple6;
                                                    if ($modulo6 === 0) {
                                                        return true;
                                                    }
                                                }
                                            } elseif ($modulo5 === 0) {
                                                return true;
                                            }
                                        }
                                    } elseif ($modulo4 === 0) {
                                        return true;
                                    }
                                }
                            } elseif ($modulo3 === 0) {
                                return true;
                            }
                        }
                    } elseif ($modulo2 === 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function hookDisplayProductActions($product){

        $id_product = $product['product']['id_product'];
        $id_product_attribute = $product['product']['id_product_attribute'];

        if($product['product']['id_product_attribute'] == 0){

            /* $values = Db::getInstance()->getRow('SELECT quantity,batch_type FROM '._DB_PREFIX_.'opartlimitquantity_product_batch WHERE id_product = '.$id_product); */
            $values = Db::getInstance()->getRow('SELECT quantity,batch_type FROM '._DB_PREFIX_.'opartlimitquantity_product_batch WHERE id_product = '.(int)$id_product);
            /* if($values){
                $this->context->smarty->assign('values', $values);
            } */
        }
        else{
            $values = Db::getInstance()->getRow('SELECT quantity,batch_type FROM '._DB_PREFIX_.'opartlimitquantity_product_attribute_batch WHERE id_product = '.(int)$id_product.' AND id_product_attribute = '.(int)$id_product_attribute);
            /* if($values){
                $this->context->smarty->assign('values', $values);
            }
            else{
               $values = Db::getInstance()->getRow('SELECT quantity,batch_type FROM '._DB_PREFIX_.'opartlimitquantity_product_batch WHERE id_product = '.(int)$id_product);
                if($values){
                    $this->context->smarty->assign('values', $values);
                } 
            } */
            if($values == false) {
                $values = Db::getInstance()->getRow('SELECT quantity,batch_type FROM '._DB_PREFIX_.'opartlimitquantity_product_batch WHERE id_product = '.(int)$id_product);
            }
        }

        $product = new Product(Tools::getValue('id_product'),true,$this->context->language->id,$this->context->shop->id);

        if($product->opart_min_qty == 0){
            $quantity_minimal = 1;
        }
        else{
             $quantity_minimal = $product->opart_min_qty;
        }

        $this->context->smarty->assign(array(
            'quantity_minimal' => $quantity_minimal,
            'values' => $values
        ));
       
        return $this->display(__FILE__, 'message.tpl');        
    }
}
