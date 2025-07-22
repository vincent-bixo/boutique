<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Megablock extends Module
{
    public function __construct()
    {
        $this->name = 'megablock';
        $this->tab = 'front_office_features';
        $this->version = '2.0.5';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Mega Block');
        $this->description = $this->l('Add html block on multiple hooks on your website');
        $path = dirname(__FILE__);
        if (strpos(__FILE__, 'Module.php') !== false) {
            $path .= '/../modules/'.$this->name;
        }
        include_once $path.'/MegablockClass.php';

        $this->context->smarty->assign(array(
            'megablock_dir' => $this->_path.'views/img',
        ));
    }

    public function install()
    {
        if (!parent::install()
                || !$this->registerHook('LeftColumn')
                || !$this->registerHook('Header')
                || !$this->registerHook('ExtraLeft')
                || !$this->registerHook('RightColumn')
                || !$this->registerHook('displayFooter')
                || !$this->registerHook('displayBanner')
                || !$this->registerHook('displayFooter')
                || !$this->registerHook('displayTopColumn')
                || !$this->registerHook('displayTop')
                || !$this->registerHook('displayShoppingCartFooter'))
            return false;

        $res = Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'megablock` (
            `id_megablock` int(10) unsigned NOT NULL auto_increment,
            `id_shop` int(10) unsigned NOT NULL ,
            `body_home_logo_link` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id_megablock`))
            ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
        );

        if ($res) {
            $res &= Db::getInstance()->execute(
                'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'megablock_lang` (
                `id_megablock` int(10) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `home_text` text DEFAULT NULL,
                `sidebar_text` text DEFAULT NULL,
                `footer_text` text DEFAULT NULL,
                `product_text` text DEFAULT NULL,
                `cart_text` text DEFAULT NULL,
                `banner_text` text DEFAULT NULL,
                `topcolumn_text` text DEFAULT NULL,
                `top_text` text DEFAULT NULL,
                PRIMARY KEY (`id_megablock`, `id_lang`))
                ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
            );
        }

        if ($res) {
            foreach (Shop::getShops(false) as $shop) {
                $res &= $this->createExampleMegaBlock($shop['id_shop']);
            }
        }

        if (!$res) {
            $res &= $this->uninstall();
        }

        return (bool)$res;
    }

    private function createExampleMegaBlock($id_shop)
    {
        $megablock = new MegablockClass();
        $megablock->id_shop = (int)$id_shop;
        $megablock->body_home_logo_link = 'https://www.team-ever.com';
        foreach (Language::getLanguages(false) as $lang)
        {
            $megablock->home_text[$lang['id_lang']] = '<p>Utilisez l\'éditeur en back-office pour mettre en avant un produit depuis votre page d\'accueil</p>';
            $megablock->sidebar_text[$lang['id_lang']] = '<p>Utilisez l\'éditeur en back-office pour mettre en avant un produit depuis votre sidebar</p>';
            $megablock->footer_text[$lang['id_lang']] = '<p>Utilisez l\'éditeur en back-office pour mettre en avant un produit depuis le pied de page de votre site</p>';
            $megablock->product_text[$lang['id_lang']] = '<p>Utilisez l\'éditeur en back-office pour mettre en avant un produit depuis votre pied de page produit</p>';
            $megablock->topcolumn_text[$lang['id_lang']] = '<p>Utilisez l\'éditeur en back-office pour mettre en avant un produit depuis le top column</p>';
        }

        return $megablock->add();
    }

    public function uninstall()
    {
        $res = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'megablock`');
        $res &= Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'megablock_lang`');

        if ($res == 0 || !parent::uninstall()) {
            return false;
        }

        return true;
    }

    private function initToolbar()
    {
        $this->toolbar_btn['Enregistrer'] = array(
            'href' => '#',
            'desc' => $this->l('Enregistrer')
        );

        return $this->toolbar_btn;
    }

    private function initForm()
    {
        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language)
            $languages[$k]['is_default'] = (int)$language['id_lang'] == Configuration::get('PS_LANG_DEFAULT');

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'megablock';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $languages;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        $helper->toolbar_scroll = true;
        $helper->toolbar_btn = $this->initToolbar();
        $helper->title = $this->displayName;
        $helper->submit_action = 'save';

        //Homepage form
        $this->fields_form[0]['form'] = array(
            'tinymce' => true,
            'description' => $this->l('Utilisez ici l\'éditeur de texte pour mettre en avant vos produits sur la page d\'accueil.').'<br/>',
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Texte pour la page d\'accueil'),
                    'name' => 'home_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
            )
        );

        //Sidebar form
        $this->fields_form[1]['form'] = array(
            'tinymce' => true,
            'description' => $this->l('Utilisez ici l\'éditeur de texte pour mettre en avant vos produits sur la sidebar.').'<br/>',
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Texte pour la sidebar'),
                    'name' => 'sidebar_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
            )
        );

        //Footer form
        $this->fields_form[2]['form'] = array(
            'tinymce' => true,
            'description' => $this->l('Utilisez ici l\'éditeur de texte pour mettre en avant vos produits sur le footer.').'<br/>',
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Texte pour le footer'),
                    'name' => 'footer_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
            )
        );

        //Product form
        $this->fields_form[3]['form'] = array(
            'tinymce' => true,
            'description' => $this->l('Utilisez ici l\'éditeur de texte pour mettre en avant vos produits sur la page produit.').'<br/>',
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Texte pour la page produit'),
                    'name' => 'product_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
            )
        );

        //Cart form
        $this->fields_form[4]['form'] = array(
            'tinymce' => true,
            'description' => $this->l('Utilisez ici l\'éditeur de texte pour mettre en avant vos produits sur la page panier.').'<br/>',
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Texte pour la page panier'),
                    'name' => 'cart_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
            )
        );

        //Cart form
        $this->fields_form[5]['form'] = array(
            'tinymce' => true,
            'description' => $this->l('Utilisez ici l\'éditeur de texte pour mettre en avant vos produits dans la bannière.').'<br/>',
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Texte pour la bannière'),
                    'name' => 'banner_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
            )
        );

        //Top column form
        $this->fields_form[6]['form'] = array(
            'tinymce' => true,
            'description' => $this->l('Utilisez ici l\'éditeur de texte pour mettre en avant vos produits dans le top column.').'<br/>',
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Texte pour le top column'),
                    'name' => 'topcolumn_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
            )
        );

        //Top column form
        $this->fields_form[7]['form'] = array(
            'tinymce' => true,
            'description' => $this->l('Utilisez ici l\'éditeur de texte pour mettre en avant vos produits dans le top.').'<br/>',
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Enregistrer'),
                'class' => 'button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Texte pour le top'),
                    'name' => 'top_text',
                    'lang' => true,
                    'autoload_rte' => true,
                    'cols' => 60,
                    'rows' => 30
                ),
            )
        );

        return $helper;
    }

    public function getContent()
    {
        $this->html = '';
        $this->postProcess();

        $helper = $this->initForm();

        $id_shop = (int)$this->context->shop->id;
        $megablock_fills = MegablockClass::getByIdShop((int)$id_shop);

        if (!$megablock_fills) {
            $this->createExampleMegaBlock($id_shop);
        }

        foreach ($this->fields_form[0]['form']['input'] as $input)
        {
                $helper->fields_value[$input['name']] = $megablock_fills->{$input['name']};
        }

        foreach ($this->fields_form[1]['form']['input'] as $input)
        {
                $helper->fields_value[$input['name']] = $megablock_fills->{$input['name']};
        }

        foreach ($this->fields_form[2]['form']['input'] as $input)
        {
                $helper->fields_value[$input['name']] = $megablock_fills->{$input['name']};
        }

        foreach ($this->fields_form[3]['form']['input'] as $input)
        {
                $helper->fields_value[$input['name']] = $megablock_fills->{$input['name']};
        }

        foreach ($this->fields_form[4]['form']['input'] as $input)
        {
                $helper->fields_value[$input['name']] = $megablock_fills->{$input['name']};
        }

        foreach ($this->fields_form[5]['form']['input'] as $input)
        {
                $helper->fields_value[$input['name']] = $megablock_fills->{$input['name']};
        }

        foreach ($this->fields_form[6]['form']['input'] as $input)
        {
                $helper->fields_value[$input['name']] = $megablock_fills->{$input['name']};
        }

        foreach ($this->fields_form[7]['form']['input'] as $input)
        {
                $helper->fields_value[$input['name']] = $megablock_fills->{$input['name']};
        }

        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/header.tpl');
        $this->html .= $helper->generateForm($this->fields_form);
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/footer.tpl');

        return $this->html;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('save'))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablock_text = MegablockClass::getByIdShop((int)$id_shop);
            $megablock_text->copyFromPost();
            if (empty($megablock_text->id_shop)) {
                $megablock_text->id_shop = (int)$id_shop;
            }
            $megablock_text->save();

            $this->_clearCache('megablock_home.tpl');
            $this->_clearCache('megablock_sidebar.tpl');
            $this->_clearCache('megablock_footer.tpl');
            $this->_clearCache('megablock_product.tpl');
            $this->_clearCache('megablock_cart.tpl');
            $this->_clearCache('megablock_banner.tpl');
            $this->_clearCache('megablock_topcolumn.tpl');
            $this->_clearCache('megablock_top.tpl');
            Tools::redirectAdmin('index.php?tab=AdminModules&configure='.$this->name.'&token='.Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)$this->context->employee->id));
        }

        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    //Hook des sidebars
    public function hookLeftColumn($params)
    {
        return $this->hookRightColumn($params);
    }

    private function hookRightColumn()
    {
        if (!$this->isCached('megablock_sidebar.tpl', $this->getCacheId()))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablocktext = MegablockClass::getByIdShop((int)$id_shop);
            if (!$megablocktext) {
                return;
            }
            $megablocktext = new MegablockClass((int)$megablocktext->id, $this->context->language->id);
            if (!$megablocktext) {
                return;
            }
            $this->smarty->assign(
                array(
                    'megablocktext' => $megablocktext,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                )
            );
        }

        return $this->display(__FILE__, 'megablock_sidebar.tpl', $this->getCacheId());
    }

    //Hook Product Extra Left
    public function hookExtraLeft($params)
    {
        if (!$this->isCached('megablock_product.tpl', $this->getCacheId()))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablocktext = MegablockClass::getByIdShop((int)$id_shop);
            if (!$megablocktext) {
                return;
            }
            $megablocktext = new MegablockClass((int)$megablocktext->id, $this->context->language->id);
            if (!$megablocktext) {
                return;
            }
            $this->smarty->assign(
                array(
                    'megablocktext' => $megablocktext,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                )
            );
        }

        return $this->display(__FILE__, 'megablock_product.tpl', $this->getCacheId());
    }

    public function hookDisplayHome($params)
    {
        if (!$this->isCached('megablock_home.tpl', $this->getCacheId()))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablocktext = MegablockClass::getByIdShop((int)$id_shop);
            if (!$megablocktext) {
                return;
            }
            $megablocktext = new MegablockClass((int)$megablocktext->id, $this->context->language->id);
            if (!$megablocktext) {
                return;
            }
            $this->smarty->assign(
                array(
                    'megablocktext' => $megablocktext,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                )
            );
        }

        return $this->display(__FILE__, 'megablock_home.tpl', $this->getCacheId());
    }

    public function hookDisplayFooter($params)
    {
        if (!$this->isCached('megablock_footer.tpl', $this->getCacheId()))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablocktext = MegablockClass::getByIdShop((int)$id_shop);
            if (!$megablocktext) {
                return;
            }
            $megablocktext = new MegablockClass((int)$megablocktext->id, $this->context->language->id);
            if (!$megablocktext) {
                return;
            }
            $this->smarty->assign(
                array(
                    'megablocktext' => $megablocktext,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                )
            );
        }
        
        return $this->display(__FILE__, 'megablock_footer.tpl', $this->getCacheId());
    }

    public function hookDisplayBanner()
    {
        if (!$this->isCached('megablock_banner.tpl', $this->getCacheId()))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablocktext = MegablockClass::getByIdShop((int)$id_shop);
            if (!$megablocktext) {
                return;
            }
            $megablocktext = new MegablockClass((int)$megablocktext->id, $this->context->language->id);
            if (!$megablocktext) {
                return;
            }
            $this->smarty->assign(
                array(
                    'megablocktext' => $megablocktext,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                )
            );
        }
        
        return $this->display(__FILE__, 'megablock_banner.tpl', $this->getCacheId());
    }

    public function hookDisplayTopColumn()
    {
        if (!$this->isCached('megablock_topcolumn.tpl', $this->getCacheId()))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablocktext = MegablockClass::getByIdShop((int)$id_shop);
            if (!$megablocktext) {
                return;
            }
            $megablocktext = new MegablockClass((int)$megablocktext->id, $this->context->language->id);
            if (!$megablocktext) {
                return;
            }
            $this->smarty->assign(
                array(
                    'megablocktext' => $megablocktext,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                )
            );
        }
        
        return $this->display(__FILE__, 'megablock_topcolumn.tpl', $this->getCacheId());
    }

    public function hookDisplayTop()
    {
        if (!$this->isCached('megablock_top.tpl', $this->getCacheId()))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablocktext = MegablockClass::getByIdShop((int)$id_shop);
            if (!$megablocktext) {
                return;
            }
            $megablocktext = new MegablockClass((int)$megablocktext->id, $this->context->language->id);
            if (!$megablocktext) {
                return;
            }
            $this->smarty->assign(
                array(
                    'megablocktext' => $megablocktext,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                )
            );
        }
        
        return $this->display(__FILE__, 'megablock_top.tpl', $this->getCacheId());
    }

    public function hookDisplayShoppingCartFooter()
    {
        if (!$this->isCached('megablock_cart.tpl', $this->getCacheId()))
        {
            $id_shop = (int)$this->context->shop->id;
            $megablocktext = MegablockClass::getByIdShop((int)$id_shop);
            if (!$megablocktext) {
                return;
            }
            $megablocktext = new MegablockClass((int)$megablocktext->id, $this->context->language->id);
            if (!$megablocktext) {
                return;
            }
            $this->smarty->assign(
                array(
                    'megablocktext' => $megablocktext,
                    'default_lang' => (int)$this->context->language->id,
                    'id_lang' => $this->context->language->id,
                )
            );
        }
        
        return $this->display(__FILE__, 'megablock_cart.tpl', $this->getCacheId());
    }
    
    public function hookHeader()
    {
        $this->context->controller->addCSS(_PS_MODULE_DIR_.'megablock/css/megablock.css', 'all');
    }
}