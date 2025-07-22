<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Frederic Moreau
 *  @copyright 2016 BeComWeb
 *  @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Addtocartcheckbox extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name       = 'addtocartcheckbox';
        $this->tab        = 'front_office_features';
        $this->version    = '1.3.1';
        $this->author     = 'BeComWeb';
        $this->module_key = '77eda710161a6214dc3bf93503c2e752';
        parent::__construct();
        $this->displayName            = $this->l('Add to Cart Checkbox');
        $this->description            = $this->l('Encourage your customers to add a designated product to their order by simply checking a checkbox.');
        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_
        );
        $this->bootstrap              = true;
        //We check for multishop feature in order to adapt success/warning/error messages
        $this->multishop_is_active    = (bool) Shop::isFeatureActive();
    }
    
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        
        if (!parent::install() || !$this->registerHook('displayShoppingCartFooter') || !$this->registerHook('displayHeader') || !$this->installDb()) {
            return false;
        } else {
            return true;
        }
    }
    
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        } else {
            return $this->uninstallDb();
        }
    }
    
    public function reset()
    {
        if (!$this->uninstall()) {
            return false;
        }
        if (!$this->install()) {
            return false;
        }
        return true;
    }
    
    public function getContent()
    {
        $output = $this->display(__FILE__, 'views/templates/admin/addtocartcheckbox-admin-hint.tpl');
        if (Tools::isSubmit('submit_atccb')) {
            $search_type      = pSQL(Tools::getValue('search_type'));
            $search_value_id  = (int) Tools::getValue('id_product');
            $search_value_ref = pSQL(Tools::getValue('reference'));
            //If Multishop feature is active we provide the option of updating all shops at once
            if ($this->multishop_is_active) {
                $update_all_shops = (bool) Tools::getValue('multishop_update_all');
            }
            if ($search_type === 'by_id') { //Search from ID
                if (!Validate::isUnsignedInt(Tools::getValue('id_product'))) {
                    $error = $this->l('Error : no product id or not numeric value provided');
                } else {
                    $search_atccb_product = $this->searchAtccbProduct($search_type = 'by_id', $data = $search_value_id);
                }
            } elseif ($search_type === 'by_ref') { //Search from Reference
                if (Tools::isEmpty($search_value_ref) || !Validate::isReference($search_value_ref)) {
                    $error = $this->l('Error : no reference or invalid reference provided');
                } else {
                    $search_atccb_product = $this->searchAtccbProduct($search_type = 'by_ref', $data = $search_value_ref);
                }
            }
            if (isset($search_atccb_product) && !$search_atccb_product) {
                if ($this->multishop_is_active) {
                    if ($search_type === 'by_id') {
                        $error = sprintf($this->l('Error : no result found for product id "%s". Are you sure this product exists and is associated to this shop ?'), $search_value_id);
                    } else {
                        $error = sprintf($this->l('Error : no result found for reference "%s". Are you sure this product exists and is associated to this shop ?'), $search_value_ref);
                    }
                } else {
                    if ($search_type === 'by_id') {
                        $error = sprintf($this->l('Error : no result found for product id "%s". Are you sure this product exists ?'), $search_value_id);
                    } else {
                        $error = sprintf($this->l('Error : no result found for reference "%s". Are you sure this product exists ?'), $search_value_ref);
                    }
                }
            } else {
                if (!isset($error)) {
                    //Mass update (all shops)
                    if (isset($update_all_shops) && $update_all_shops) {
                        $ids_shop = Shop::getCompleteListOfShopsID();
                    } else { //Single update (current shop)
                        $ids_shop[] = (int) $this->context->shop->id;
                    }
                    if (!$this->updateAtccbProduct($search_atccb_product['id_product'], $ids_shop)) {
                        $error = $this->l('An error occured while updating, please try again.');
                    }
                }
            }
            if (isset($error)) {
                $output .= $this->displayError($error);
            } else {
                $output .= $this->displayConfirmation($this->l('Product has been updated successfully (see "Product Overview" below)'));
            }
        }
        //Get product informations
        $data = $this->getAtccbProduct((int) $this->context->shop->id, $this->context->language->id);
        if ($data !== false) {
            $this->smarty->assign(array(
                'data' => $data,
                'id_lang' => (int) $this->context->language->id,
                'currency_sign' => $this->context->currency->sign
            ));
        }
        $this->context->controller->addCSS(($this->_path) . 'views/css/addtocartcheckbox.css', 'all');
        $this->context->controller->addJS(($this->_path) . 'views/js/addtocartcheckbox-admin.js');
        $output .= $this->renderForm();
        $output .= $this->display(__FILE__, 'views/templates/admin/addtocartcheckbox-admin-overview.tpl');
        return $output;
    }
    
    public function renderWidget($hookName, array $params)
    {
        $params['data'] = $this->getAtccbProduct((int) $this->context->shop->id, $this->context->cart->id_lang);
        if ($params['data'] !== false) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $params));
            return $this->fetch('module:addtocartcheckbox/views/templates/front/addtocartcheckbox-front.tpl');
        } else {
            return false;
        }
    }
    
    public function getWidgetVariables($hookName, array $params)
    {
        //This product may already be in cart
        $cart              = new Cart((int) $this->context->cart->id);
        $already_in_cart   = $cart->containsProduct($params['data']['atccb_product']->id, $params['data']['atccb_id_product_attribute'], false, 0);
        //Get add/remove links
        $link              = new Link();
        $atccb_add_link    = $link->getUpQuantityCartURL((int) $params['data']['atccb_product']->id, (int) $params['data']['atccb_id_product_attribute'], null);
        $atccb_remove_link = $link->getRemoveFromCartURL((int) $params['data']['atccb_product']->id, (int) $params['data']['atccb_id_product_attribute'], null);
        return array(
            'data' => $params['data'],
            'show_atccb_price_with_taxes' => Product::getTaxCalculationMethod((int) $this->context->customer->id),
            'already_in_cart' => $already_in_cart,
            'atccb_add_link' => $atccb_add_link,
            'atccb_remove_link' => $atccb_remove_link,
            'currency_sign' => $this->context->currency->sign,
            'id_lang' => (int) $this->context->cart->id_lang
        );
    }
    
    public function hookdisplayHeader($params)
    {
        $this->context->controller->registerStylesheet('modules-addtocartcheckbox', 'modules/' . $this->name . '/views/css/addtocartcheckbox.css', array(
            'media' => 'all',
            'priority' => 150
        ));
        $this->context->controller->registerJavascript('modules-addtocartcheckbox-front', 'modules/' . $this->name . '/views/js/addtocartcheckbox.js', array(
            'position' => 'bottom',
            'priority' => 150
        ));
    }
    
    private function searchAtccbProduct($search_type, $data)
    {
        $where_clause = ($search_type === 'by_id') ? "WHERE p.`id_product` = " : "WHERE p.`reference` = ";
        $sql          = 'SELECT * FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (p.`id_product` = ps.`id_product`)
            ' . $where_clause . ' "' . pSQL($data) . '" AND ps.`id_shop` = "' . (int) $this->context->shop->id . '"';
        $search_atccb = Db::getInstance()->getRow($sql);
        return $search_atccb;
    }
    
    //$ids_shop is an array because if multishop feature is active we can choose to mass update (assign smae product to all shops)
    private function updateAtccbProduct($id_product, array $ids_shop)
    {
        foreach ($ids_shop as $id_shop) {
            $update = Db::getInstance()->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'atccb_product` (`id_product`, `id_shop`)
            VALUES("' . (int) $id_product . '", "' . (int) $id_shop . '") ON DUPLICATE KEY UPDATE `id_product` = "' . (int) $id_product . '"
            ');
        }
        return ($update);
    }
    
    //retrieve product in database and then infos (image to display, prices, title and description for this language...)
    private function getAtccbProduct($id_shop, $id_lang)
    {
        $atccb_product_id = (int) Db::getInstance()->getValue('
            SELECT `id_product` FROM `' . _DB_PREFIX_ . 'atccb_product` WHERE `id_shop` = "' . (int) $id_shop . '"
        ');
        if (!$atccb_product_id) {
            return false;
        } else {
            $atccb_product = new Product($atccb_product_id);
            //If product has attributes, we will use default attribute
            if ((int) $atccb_product->hasAttributes() > 0) {
                $atccb_id_product_attribute   = (int) $atccb_product->getDefaultAttribute((int) $atccb_product->id);
                // Multiple images can be associated with one combination, so we cannot use getCombinationsImages() method so we take the 1st one
                $atccb_id_image               = (int) Db::getInstance()->getValue('
                    SELECT `id_image` FROM `' . _DB_PREFIX_ . 'product_attribute_image` WHERE `id_product_attribute` = "' . (int) $atccb_id_product_attribute . '"
                ');
                //Additionnal text
                $atccb_attribute_combinations = $atccb_product->getAttributeCombinationsById($atccb_id_product_attribute, $id_lang);
                $atccb_additional_text        = "";
                $i                            = 1;
                foreach ($atccb_attribute_combinations as $comb) {
                    $atccb_additional_text .= pSQL($comb['attribute_name']);
                    if ($i < count($atccb_attribute_combinations)) {
                        $atccb_additional_text .= ' - ';
                    }
                    $i++;
                }
            } else { //Standard product (without attributes)
                $atccb_id_product_attribute = 0;
                $atccb_image                = $atccb_product->getCover((int) $atccb_product->id, null);
                $atccb_id_image             = (int) $atccb_image['id_image'];
                $atccb_additional_text      = null;
            }
            //Product prices (with and without taxes)
            $atccb_product_price_tax_excl = $atccb_product->getPrice(false, $atccb_id_product_attribute, 6, null, false, true, 1);
            $atccb_product_price_tax_incl = $atccb_product->getPrice(true, $atccb_id_product_attribute, 6, null, false, true, 1);
            //Image and up/down cart qty links
            $link                         = new Link();
            if ($atccb_id_image > 0) {
                $atccb_img_url = Tools::getShopProtocol() . $link->getImageLink($atccb_product->link_rewrite[$id_lang], $atccb_id_image, ImageType::getFormattedName('home'));
            } else {
                $atccb_img_url = Tools::getShopDomain(true, false) . '/img/p/' . Language::getIsoById($id_lang) . '.jpg';
            }
            return (array(
                'atccb_product' => $atccb_product,
                'atccb_id_product_attribute' => $atccb_id_product_attribute,
                'atccb_product_price_tax_excl' => $atccb_product_price_tax_excl,
                'atccb_product_price_tax_incl' => $atccb_product_price_tax_incl,
                'atccb_img_url' => $atccb_img_url,
                'atccb_product_can_be_ordered' => $this->canBeOrdered($atccb_product, $atccb_id_product_attribute),
                'atccb_additional_text' => $atccb_additional_text
            ));
        }
    }
    
    //Choosen product can be ordered if customer has access to its category OR if it is virtual ELSE it might be available for order at least THEN either its stock is positive
    //OR customer can order it if out of stock order is permitted
    private function canBeOrdered(Product $atccb_product, $atccb_id_product_attribute)
    {
        if (!Tools::isEmpty($this->context->cart)) { //we skip this test in back-office as cart details as not loaded
            if (!$atccb_product->checkAccess($this->context->cart->id_customer)) {
                return false;
            }
        }
        if ((bool) $atccb_product->is_virtual) {
            return true;
        }
        if (!(bool) $atccb_product->available_for_order) {
            return false;
        } else {
            $stock = (int) StockAvailable::getQuantityAvailableByProduct($atccb_product->id, $atccb_id_product_attribute, (int) $this->context->shop->id);
            //If stock is positive, product is orderable
            if ($stock > 0) {
                return true;
            }
            //Else we need to check if out of stock order is possible (depending on shop)
            $out_of_stock = (int) StockAvailable::outOfStock((int) $atccb_product->id, (int) $this->context->shop->id);
            $allow_oosp   = (bool) $atccb_product->isAvailableWhenOutOfStock($out_of_stock);
            return ($allow_oosp ? true : false);
        }
    }
    
    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('AddToCartCheckbox module settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Search by :'),
                        'name' => 'search_type',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'by_ref',
                                'value' => 'by_ref',
                                'label' => $this->l('Product Reference')
                            ),
                            array(
                                'id' => 'by_id',
                                'value' => 'by_id',
                                'label' => $this->l('Product ID')
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'reference',
                        'label' => $this->l('Product Reference'),
                        'class' => 'fixed-width-sm atccb_search_field',
                        'suffix' => '<i class="icon-trash"></i>'
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'id_product',
                        'label' => $this->l('Product ID'),
                        'class' => 'fixed-width-sm atccb_search_field',
                        'suffix' => '<i class="icon-trash"></i>'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default'
                )
            )
        );
        if ($this->multishop_is_active) {
            $fields_form['form']['input'][] = array(
                'type' => 'checkbox',
                'label' => $this->l('Multishops update :'),
                'name' => 'multishop',
                'values' => array(
                    'query' => array(
                        array(
                            'id' => 'update_all',
                            'name' => $this->l('Use this product for all my shops'),
                            'val' => '1'
                        )
                    ),
                    'id' => 'id',
                    'name' => 'name'
                )
            );
        }
        
        $helper                           = new HelperForm();
        $helper->name_controller          = 'atccb_admin_form';
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form                = $fields_form;
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submit_atccb';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars                 = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        
        return $helper->generateForm(array(
            $fields_form
        ));
    }
    
    public function getConfigFieldsValues()
    {
        return array(
            'search_type' => Tools::getValue('search_type', 'by_ref'),
            'reference' => Tools::getValue('reference'),
            'id_product' => Tools::getValue('id_product')
        );
    }
    
    public function installDb()
    {
        return (Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'atccb_product` (
            `id_atccb_product` INT(3) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_shop` INT(3) UNSIGNED NOT NULL UNIQUE
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8 COLLATE utf8_general_ci;'));
    }
    
    public function uninstallDb()
    {
        return (Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'atccb_product`'));
    }
}
