<?php
/**
* 2007-2017 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Tm_NewProducts extends Module implements WidgetInterface
{
    private $templateFile;

    public function __construct()
    {
        $this->name = 'tm_newproducts';
        $this->author = 'PixoThemes';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->ps_versions_compliancy = array(
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        );

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('PX - New products', array(), 'Modules.Newproducts');
        $this->description = $this->trans('Displays new products as Slider or Grid in the central column of your homepage.', array(), 'Modules.Newproducts');

        $this->templateFile = 'module:tm_newproducts/views/templates/hook/tm_newproducts.tpl';
    }

    public function install()
    {
        $this->_clearCache('*');

        return parent::install()
            && Configuration::updateValue('TMNEW_PRODUCTS_NBR', 8)
			&& Configuration::updateValue('TMNEW_PRODUCTS_SLIDER', 0)
            && $this->registerHook('actionProductAdd')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('actionProductDelete')
            && $this->registerHook('displayTmNew')
			&& $this->registerHook('displayHome')
        ;
    }

    public function uninstall()
    {
        $this->_clearCache('*');

        if (!parent::uninstall() ||
            !Configuration::deleteByName('TMNEW_PRODUCTS_NBR')) {
            return false;
        }

        return true;
    }

    public function hookActionProductAdd($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionProductUpdate($params)
    {
        $this->_clearCache('*');
    }

    public function hookActionProductDelete($params)
    {
        $this->_clearCache('*');
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitBlockTmNewProducts')) {

            $productNbr = Tools::getValue('TMNEW_PRODUCTS_NBR');

            if (!$productNbr || empty($productNbr)) {
                $output .= $this->displayError(
                    $this->trans('Please complete the "products to display" field.', array(), 'Modules.Newproducts.Admin' )
                );
            } elseif (0 === (int)$productNbr) {
                $output .= $this->displayError(
                    $this->trans('Invalid number.', array(), 'Modules.Newproducts.Admin' )
                );
            } else {
                Configuration::updateValue('PS_NB_DAYS_NEW_PRODUCT', (int)Tools::getValue('PS_NB_DAYS_NEW_PRODUCT'));
                Configuration::updateValue('TMNEW_PRODUCTS_NBR', (int)$productNbr);
				Configuration::updateValue('TMNEW_PRODUCTS_SLIDER', (int)Tools::getValue('TMNEW_PRODUCTS_SLIDER'));

                $this->_clearCache('*');

                $output .= $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
            }
        }
        return $output.$this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Products to display', array(), 'Modules.Newproducts.Admin'),
                        'name' => 'TMNEW_PRODUCTS_NBR',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Define the number of products to be displayed in this block.', array(), 'Modules.Newproducts.Admin'),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->trans('Number of days for which the product is considered \'new\'', array(), 'Modules.Newproducts.Admin'),
                        'name'  => 'PS_NB_DAYS_NEW_PRODUCT',
                        'class' => 'fixed-width-xs',
                    ),
					 array(
                        'type' => 'switch',
                        'label' => $this->trans('Display New Product as Slider', array(), 'Modules.Newproducts'),
                        'name' => 'TMNEW_PRODUCTS_SLIDER',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->trans('Display Slider or Grid.(Note:Slider is working if "Number of product" is set more than 4).', array(), 'Modules.Newproducts'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', array(), 'Admin.Global'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', array(), 'Admin.Global'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions')
                ),
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlockTmNewProducts';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name .
            '&tab_module=' . $this->tab .
            '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'PS_NB_DAYS_NEW_PRODUCT' => Tools::getValue('PS_NB_DAYS_NEW_PRODUCT', (int) Configuration::get('PS_NB_DAYS_NEW_PRODUCT')),
            'TMNEW_PRODUCTS_NBR' => Tools::getValue('TMNEW_PRODUCTS_NBR', (int) Configuration::get('TMNEW_PRODUCTS_NBR')),
			'TMNEW_PRODUCTS_SLIDER' => Tools::getValue('TMNEW_PRODUCTS_SLIDER',(int)  Configuration::get('TMNEW_PRODUCTS_SLIDER')),
        );
    }

    public function renderWidget($hookName, array $configuration)
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('tm_newproducts'))) {
            $variables = $this->getWidgetVariables($hookName, $configuration);

            if (empty($variables)) {
                return false;
            }

            $this->smarty->assign($variables);
        }

        return $this->fetch($this->templateFile, $this->getCacheId('tm_newproducts'));
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $products = $this->getNewProducts();

        if (!empty($products)) {
            return array(
                'products' => $products,
                'allNewProductsLink' => Context::getContext()->link->getPageLink('new-products'),
				'no_prod' => (int) Configuration::get('TMNEW_PRODUCTS_NBR'),
				'slider' => (int) Configuration::get('TMNEW_PRODUCTS_SLIDER'),
            );
        }
        return false;
    }

    protected function getNewProducts()
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            //return false;
        }

        $newProducts = false;

        if (Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) {
            $newProducts = Product::getNewProducts(
                (int)$this->context->language->id,
                0,
                (int)Configuration::get('TMNEW_PRODUCTS_NBR')
            );
        }

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products_for_template = array();

        if (is_array($newProducts)) {
            foreach ($newProducts as $rawProduct) {
                $products_for_template[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $this->context->language
                );
            }
        }

        return $products_for_template;
    }
}
