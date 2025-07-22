<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.txt
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to a newer
 * versions in the future. If you wish to customize this module for your needs
 * please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright Since 2010 Webkul
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/WkcharitydonationRequiredClasses.php';
class WkCharityDonation extends Module
{
    public function __construct()
    {
        $this->name = 'wkcharitydonation';
        $this->tab = 'others';
        $this->version = '4.1.1';
        $this->module_key = 'f231284b731d86901af1dd0e6d4192dc';
        $this->author = 'Webkul';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Donate To Charity');
        $this->description = $this->l('Using this module you can create and get donations for charities.');
        $this->confirmUninstall = $this->l('Are you sure?');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $objDonationDb = new WkCharityDonationDb();
        $objDonationInfo = new WkDonationInfo();
        if (!parent::install()
            || !$this->callInstallTabs()
            || !$objDonationDb->createTables()
            || !$objDonationInfo->createCategory()
            || !$this->registerHooks()
        ) {
            return false;
        }

        return true;
    }

    public function callInstallTabs()
    {
        $this->installTab('AdminCharityDonation', 'Charity Donation');
        $this->installTab('AdminCharityDonationManagement', 'Charity Donation', 'AdminCharityDonation');
        $this->installTab(
            'AdminGlobalAdvertisement',
            'Global Advertisement Settings',
            'AdminCharityDonationManagement'
        );
        $this->installTab('AdminManageDonation', 'Manage Donations', 'AdminCharityDonationManagement');
        $this->installTab('AdminDonationStats', 'Donations Stats', 'AdminCharityDonationManagement');

        return true;
    }

    public function installTab($className, $tabName, $tabParentName = false)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }

        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = 0;
        }

        if ($className == 'AdminCharityDonationManagement') {
            $tab->icon = 'thumb_up';
        }

        $tab->module = $this->name;

        return $tab->add();
    }

    public function registerHooks()
    {
        return $this->registerHook(
            [
                'displayContentWrapperTop',
                'displayContentWrapperBottom',
                'displayLeftColumn',
                'displayLeftColumnProduct',
                'displayRightColumn',
                'displayRightColumnProduct',
                'displayProductAdditionalInfo',
                'displayBackOfficeHeader',
                'displayOverrideTemplate',
                'displayHeader',
                'actionValidateOrder',
                'actionFrontControllerSetMedia',
                'actionObjectProductInCartDeleteBefore',
                'actionObjectProductDeleteBefore',
            ]
        );
    }

    public function uninstall()
    {
        $objDonationDb = new WkCharityDonationDb();
        $objDonationInfo = new WkDonationInfo();
        if (!parent::uninstall()
            || !$this->uninstallTabs()
            || !$objDonationInfo->disableProducts()
            || !$objDonationInfo->deleteAdvertisementImages()
            || !$objDonationDb->deleteTables()
            || !$this->deleteCategory()
            || !$this->removeUplaodedFiles()
            || !Configuration::deleteByName('WK_DONATION_ID_CATEGORY')
        ) {
            return false;
        }

        return true;
    }

    public function deleteCategory()
    {
        $idCat = Configuration::get('WK_DONATION_ID_CATEGORY');
        $objCat = new Category((int) $idCat);
        $objCat->delete();

        return true;
    }

    public function removeUplaodedFiles()
    {
        $folder_path = _PS_MODULE_DIR_ . $this->name . '/views/img/banner';
        $fileIndex = _PS_MODULE_DIR_ . $this->name . '/views/img/banner/index.php';
        $files = glob($folder_path . '/*');
        if (count($files) > 1) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    if ($file != $fileIndex) {
                        @unlink($file);
                    }
                }
            }
        }

        return true;
    }

    public function uninstallTabs()
    {
        if ($moduleTabs = Tab::getCollectionFromModule($this->name)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }

        return true;
    }

    public function getContent()
    {
        Tools::redirect($this->context->link->getAdminLink('AdminGlobalAdvertisement'));
    }

    public function hookDisplayContentWrapperTop()
    {
        $objDisplayPlaces = new WkDonationDisplayPlaces();
        if ($donationAds = $objDisplayPlaces->displayDonationsAdvertisement(
            WkDonationDisplayPlaces::WK_DONATION_HOOK_HOME
        )) {
            $shopWiseDonationAds = [];
            foreach ($donationAds as $value) {
                if ($value['id_shop'] == $this->context->shop->id) {
                    $shopWiseDonationAds[] = $value;
                }
            }
            $this->context->smarty->assign('donationAds', $shopWiseDonationAds);

            return $this->fetch('module:' . $this->name . '/views/templates/hook/advertisement-top-bottom.tpl');
        }
    }

    public function hookDisplayContentWrapperBottom()
    {
        $objDisplayPlaces = new WkDonationDisplayPlaces();
        if ($donationAds = $objDisplayPlaces->displayDonationsAdvertisement(
            WkDonationDisplayPlaces::WK_DONATION_HOOK_FOOTER
        )) {
            $shopWiseDonationAds = [];
            foreach ($donationAds as $value) {
                if ($value['id_shop'] == $this->context->shop->id) {
                    $shopWiseDonationAds[] = $value;
                }
            }
            $this->context->smarty->assign('donationAds', $shopWiseDonationAds);

            return $this->fetch('module:' . $this->name . '/views/templates/hook/advertisement-top-bottom.tpl');
        }
    }

    public function hookDisplayLeftColumnProduct()
    {
        $objDisplayPlaces = new WkDonationDisplayPlaces();
        if ($donationAds = $objDisplayPlaces->displayDonationsAdvertisement(
            WkDonationDisplayPlaces::WK_DONATION_HOOK_LEFT
        )) {
            $shopWiseDonationAds = [];
            foreach ($donationAds as $value) {
                if ($value['id_shop'] == $this->context->shop->id) {
                    $shopWiseDonationAds[] = $value;
                }
            }
            $this->context->smarty->assign('donationAds', $shopWiseDonationAds);

            return $this->fetch('module:' . $this->name . '/views/templates/hook/advertisement-left-right.tpl');
        }
    }

    public function hookDisplayLeftColumn()
    {
        $objDisplayPlaces = new WkDonationDisplayPlaces();
        if ($donationAds = $objDisplayPlaces->displayDonationsAdvertisement(
            WkDonationDisplayPlaces::WK_DONATION_HOOK_LEFT
        )) {
            $shopWiseDonationAds = [];
            foreach ($donationAds as $value) {
                if ($value['id_shop'] == $this->context->shop->id) {
                    $shopWiseDonationAds[] = $value;
                }
            }
            $this->context->smarty->assign('donationAds', $shopWiseDonationAds);

            return $this->fetch('module:' . $this->name . '/views/templates/hook/advertisement-left-right.tpl');
        }
    }

    public function hookDisplayRightColumnProduct()
    {
        $objDisplayPlaces = new WkDonationDisplayPlaces();
        if ($donationAds = $objDisplayPlaces->displayDonationsAdvertisement(
            WkDonationDisplayPlaces::WK_DONATION_HOOK_RIGHT
        )) {
            $shopWiseDonationAds = [];
            foreach ($donationAds as $value) {
                if ($value['id_shop'] == $this->context->shop->id) {
                    $shopWiseDonationAds[] = $value;
                }
            }
            $this->context->smarty->assign('donationAds', $shopWiseDonationAds);

            return $this->fetch('module:' . $this->name . '/views/templates/hook/advertisement-left-right.tpl');
        }
    }

    public function hookDisplayRightColumn()
    {
        $objDisplayPlaces = new WkDonationDisplayPlaces();
        if ($donationAds = $objDisplayPlaces->displayDonationsAdvertisement(
            WkDonationDisplayPlaces::WK_DONATION_HOOK_RIGHT
        )) {
            $shopWiseDonationAds = [];
            foreach ($donationAds as $value) {
                if ($value['id_shop'] == $this->context->shop->id) {
                    $shopWiseDonationAds[] = $value;
                }
            }
            $this->context->smarty->assign('donationAds', $shopWiseDonationAds);

            return $this->fetch('module:' . $this->name . '/views/templates/hook/advertisement-left-right.tpl');
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if ($idProduct = Tools::getValue('id_product')) {
            $objDonationInfo = new WkDonationInfo();
            if ($priceInfo = $objDonationInfo->getPriceInfo($idProduct)) {
                $this->context->smarty->assign([
                    'currency_sign' => $this->context->currency->sign,
                    'id_donation_info' => $priceInfo['id_donation_info'],
                    'price_type' => $priceInfo['price_type'],
                    'price_by_customer' => WkDonationInfo::WK_DONATION_PRICE_TYPE_CUSTOMER,
                    'minimum_price' => Tools::ps_round(
                        Tools::convertPrice($priceInfo['price']),
                        Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                    ),
                ]);

                return $this->fetch('module:' . $this->name . '/views/templates/hook/product-price-block.tpl');
            }
        }
    }

    public function hookActionObjectProductInCartDeleteBefore($params)
    {
        $idProduct = $params['id_product'];
        $objDonationInfo = new WkDonationInfo();
        if ($idSpecificPrice = $objDonationInfo->checkExistingSpecificPrice(
            $idProduct,
            $this->context->customer->id,
            $this->context->cart->id
        )) {
            $objSpecificPrice = new SpecificPrice($idSpecificPrice);
            $objSpecificPrice->delete();
        }
    }

    public function hookActionObjectProductDeleteBefore($params)
    {
        if ($idProduct = $params['object']->id) {
            $objDonationInfo = new WkDonationInfo();
            if ($objDonationInfo->isDonationProduct($idProduct)) {
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminProducts', true, ['notdeleted' => 1])
                );
            }
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if ('AdminProducts' == $this->context->controller->php_self) {
            if (Tools::getValue('notdeleted')) {
                $this->context->controller->errors[] = $this->l('The product you are deleting is a donation product, it can only be deleted from \'manage donation\' tab in Charity donation');
            }
        }
    }

    public function hookDisplayOverrideTemplate($params)
    {
        if ('checkout/cart' === $params['template_file']) {
            $objDonationInfo = new WkDonationInfo();
            if ($idDonationInfo = $objDonationInfo->getCheckoutDonations($this->context->shop->id)) {
                $checkoutDonations = [];
                foreach ($idDonationInfo as $idCheckoutDonation) {
                    $objCheckoutdonation = new WkDonationInfo($idCheckoutDonation['id_donation_info']);
                    $objCheckoutdonation->price = Tools::ps_round(
                        Tools::convertPrice($objCheckoutdonation->price),
                        Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                    );
                    $objCheckoutdonation->link = $this->context->link->getProductLink($objCheckoutdonation->id_product);
                    $objCheckoutdonation->displayPrice = WkDonationInfo::displayprice($objCheckoutdonation->price);
                    $checkoutDonations[] = (array) $objCheckoutdonation;
                }
                $columns = '0';
                if ('layout-full-width' == $this->context->shop->theme->getLayoutNameForPage('cart')) {
                    $columns = '1';
                }
                $this->context->smarty->assign([
                    'checkoutDonations' => $checkoutDonations,
                    'id_current_lang' => $this->context->language->id,
                    'currency_sign' => $this->context->currency->sign,
                    'cart_url' => $this->context->link->getPageLink('cart') . '?action=show',
                    'columnLayout' => $columns,
                ]);

                return dirname(__FILE__) . '/views/templates/front/checkout-donation.tpl';
            }
        }
        if ('catalog/_partials/quickview' == $params['template_file']) {
            if ($idProduct = Tools::getValue('id_product')) {
                $objDonationInfo = new WkDonationInfo();
                if ($priceInfo = $objDonationInfo->getPriceInfo($idProduct)) {
                    $this->context->smarty->assign([
                        'currency_sign' => $this->context->currency->sign,
                        'id_donation_info' => $priceInfo['id_donation_info'],
                        'price_type' => $priceInfo['price_type'],
                        'price_by_customer' => WkDonationInfo::WK_DONATION_PRICE_TYPE_CUSTOMER,
                        'minimum_price' => Tools::ps_round(
                            Tools::convertPrice($priceInfo['price']),
                            Configuration::get('PS_PRICE_DISPLAY_PRECISION')
                        ),
                    ]);
                }

                if ($objDonationInfo->isDonationProduct($idProduct)) {
                    $this->context->smarty->assign([
                        'isDonationProduct' => 1,
                    ]);

                    return dirname(__FILE__) . '/views/templates/front/product-donation-add-to-cart-override.tpl';
                }
            }
        }
    }

    public function hookDisplayHeader()
    {
        // following code is used to update dontion product expiry so is called on every page.
        $objDonationInfo = new WkDonationInfo();
        $objDonationInfo->updateDonationExpiry();
    }

    public function hookActionValidateOrder($params)
    {
        $objDonationInfo = new WkDonationInfo();
        $data = [];
        foreach ($params['order']->product_list as $key => $orderProduct) {
            if ($idDonationInfo = $objDonationInfo->isDonationProduct($orderProduct['id_product'])) {
                if (Validate::isLoadedObject($objDonationInfo = new WkDonationInfo($idDonationInfo))) {
                    if ($objDonationInfo->active) {
                        $data[$key]['id_order'] = (int) $params['order']->id;
                        $data[$key]['id_product'] = (int) $orderProduct['id_product'];
                        $data[$key]['id_donation_info'] = (int) $idDonationInfo;
                        $data[$key]['id_customer'] = (int) $params['customer']->id;
                        $data[$key]['name'] = pSQL($orderProduct['name']);
                        $data[$key]['id_shop'] = $orderProduct['id_shop'];
                        $data[$key]['date_add'] = date('Y-m-d h:i:s');
                    }
                }
            }
        }

        return Db::getInstance()->insert('wk_donation_stats', $data);
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ('index' == $this->context->controller->php_self
            || 'category' == $this->context->controller->php_self
            || 'product' == $this->context->controller->php_self
            || 'cart' == $this->context->controller->php_self
        ) {
            $this->context->controller->registerStylesheet(
                'wk-charitydonation-css',
                'modules/' . $this->name . '/views/css/front/wk-donation-banner.css'
            );
            $this->context->controller->registerJavascript(
                'wk-charitydonation-js',
                'modules/' . $this->name . '/views/js/front/wk-donation.js'
            );
            Media::addJsDef([
                'readMoreTxt' => $this->l('read more'),
                'readLessTxt' => $this->l('read less'),
                'ajaxToken' => Tools::getToken(false),
                'addDonationControllerlink' => $this->context->link->getModuleLink(
                    'wkcharitydonation',
                    'validatedonation'
                ),
            ]);
        }
        if ('cart' == $this->context->controller->php_self) {
            $this->context->controller->registerStylesheet(
                'wk-charitydonations-css',
                'modules/' . $this->name . '/views/css/front/wk-checkout-donation.css'
            );
        }
        if ('product' == $this->context->controller->php_self) {
            $objDonationInfo = new WkDonationInfo();
            if ($objDonationInfo->isDonationProduct(Tools::getValue('id_product'))) {
                $this->context->controller->registerStylesheet(
                    'wk-charitydonations-css',
                    'modules/' . $this->name . '/views/css/front/wk-product-price-block.css'
                );
            }
        }
    }
}
