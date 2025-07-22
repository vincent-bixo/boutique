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

class AdminWkwarehousesdashController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';
        $this->toolbar_title = $this->l('Dashboard');
        parent::__construct();
    }

    /*
     * Render general view
    */
    public function renderView()
    {
        array_shift($this->module->my_tabs);
        array_shift($this->module->my_tabs);

        $url = Context::getContext()->link->getAdminLink('AdminModules')
        .'&configure='.$this->module->name
        .'&tab_module='.$this->module->tab
        .'&module_name='.$this->module->name;

        $this->tpl_view_vars = array(
            'url_config' => $url,
            'module_folder' => _MODULE_DIR_.$this->module->name,
            'module_tabs' => $this->module->my_tabs,
        );
        $this->base_tpl_view = 'dashboard.tpl';

        return parent::renderView();
    }

    /*
    * Method Translation Override For PS 1.7
    */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
   			$translated = $this->translator->trans($string, [], 'Modules.Wkwarehouses.Adminwkwarehousesdashcontroller');
            if ($translated !== $string) {
                return $translated;
            }
        }
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            $class = Tools::substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
