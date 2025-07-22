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
 *  @copyright 2020 BeComWeb
 *  @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class ChangeProductCreationDate extends Module
{
    
    public function __construct()
    {
        $this->name                   = 'changeproductcreationdate';
        $this->tab                    = 'administration';
        $this->version                = '1.2.0';
        $this->author                 = 'BeComWeb';
        $this->need_instance          = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_
        );
        $this->bootstrap              = true;
        
        parent::__construct();
        
        $this->displayName = $this->l('Product creation date');
        $this->description = $this->l('Change product creation date from product add/edit form');
        
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');
    }
    
    //Tab content
    public function hookDisplayAdminProductsOptionsStepTop($params)
    {
        //Instantiate current product
        $product = new Product((int)$params['id_product']);
        $this->smarty->assign(array(
            'product_id' => $product->id,
            'product_creation_date' => Tools::substr($this->getProductCreationDate($product), 0, -9),
            'module_token' => Tools::getAdminToken('AdminChangeproductcreationdate'),
            'ajax_error_text' => $this->l('Technical error ! Unable to contact update controller. If this problem persists please reset this module.'),
        ));
        return $this->display(__FILE__, 'views/templates/admin/changeproductcreationdate.tpl');
    }
 
    //Product creation date to display is different if we are in multistore context or not
    private function getProductCreationDate(Product $product)
    {
        $multishop_is_active = (bool) Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');
        //Returns date_add property in single store context
        if (!$multishop_is_active || ($multishop_is_active && (Shop::getContext() != Shop::CONTEXT_SHOP))) {
            return Db::getInstance()->getValue('
                SELECT `date_add` FROM ' . _DB_PREFIX_ . 'product
                WHERE `id_product` = "' . (int) $product->id . '" 
            ');
        } else {
            return Db::getInstance()->getValue('
                SELECT `date_add` FROM ' . _DB_PREFIX_ . 'product_shop
                WHERE `id_product` = "' . (int) $product->id . '" AND `id_shop` = "' . (int) $this->context->shop->id . '" 
            ');
        }
    }
    
    //For multistore context, we dont only use the Product object update method but also update 'product_shop' table with SQL queries
    //For ajax controller
    public function updateProductCreationDateForShop($new_product_date, $id_product, $id_shop)
    {
        return Db::getInstance()->update(
            'product_shop', //table
            array('date_add' => $new_product_date), //value
            'id_product = "' . (int) $id_product . '" AND id_shop = "' . (int) $id_shop . '"' //where clauses
        );
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (isset($this->context->controller->controller_name) && $this->context->controller->controller_name == 'AdminProducts') {
            $this->context->controller->addJS($this->_path.'views/js/changeproductcreationdate.js');
        }
    }
    
    //Install/uninstall
    
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (!parent::install() || !$this->registerHook('displayAdminProductsOptionsStepTop') || !$this->registerHook('actionAdminControllerSetMedia') || !$this->installAdminController()) {
            return false;
        } else {
            return true;
        }
    }

    public function installAdminController()
    {
        $tab = new Tab();
        $tab->active = 1;
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'ChangeproductcreationdateController';
        }
        $tab->class_name = 'AdminChangeproductcreationdate';
        $tab->module = $this->name;
        $tab->id_parent = - 1;
        if ($tab->add()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallAdminController()) {
            return false;
        }
        return true;
    }

    public function uninstallAdminController()
    {
        $tab = Tab::getInstanceFromClassName('AdminChangeproductcreationdate');
        if (Validate::isLoadedObject($tab)) {
            $tab->delete();
        }
        return true;
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
}
