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

class AdminGlobalAdvertisementController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'wk_donation_info';
        $this->identifier = 'id_donation_info';
        $this->className = 'WkDonationInfo';
        parent::__construct();
        $this->toolbar_title = $this->l('Global advertisement configuration');
        Shop::addTableAssociation('wk_donation_info', ['type' => 'shop', 'primary' => 'id_donation_info']);
        $this->_where .= ' AND a.`is_global` = true';
        $this->display = 'add';

        $objDonationInfo = new WkDonationInfo();
        $objDonationInfo->updateDonationExpiry();
    }

    public function renderForm()
    {
        $smartyVars = [];
        $objDonationInfo = $this->loadObject(true);

        $idDonationInfo = WkDonationInfo::getIdGlobalDonation();
        if ($idDonationInfo) {
            $objDonationInfo = new WkDonationInfo($idDonationInfo);
            $smartyVars['donationInfo'] = (array) $objDonationInfo;
        }
        $idShop = Context::getContext()->shop->id;
        $objDisplayPlaces = new WkDonationDisplayPlaces();
        $donationPages = $objDisplayPlaces->getDonationPagesByIdDonation($objDonationInfo->id, $idShop);

        $donationHooks = [];
        foreach ($donationPages as $page) {
            $donationHooks[$page['id_page']] = $objDisplayPlaces->getDonationHooksByIdPage(
                $objDonationInfo->id,
                $page['id_page'],
                $idShop
            );
        }
        $smartyVars['languages'] = Language::getLanguages(false);
        $currentLangId = Configuration::get('PS_LANG_DEFAULT');
        $smartyVars['currentLang'] = Language::getLanguage((int) $currentLangId);
        $smartyVars['imagePath_head_foot'] = $this->getAdvertisementBannerImagePath(
            $objDonationInfo->id . '_' . $idShop . '-head-foot'
        );
        $smartyVars['imagePath_left_right'] = $this->getAdvertisementBannerImagePath(
            $objDonationInfo->id . '_' . $idShop . '-left-right'
        );
        $smartyVars['header_file'] = $objDonationInfo->id . '-head-foot';
        $smartyVars['left_right_file'] = $objDonationInfo->id . '-left-right';

        $smartyVars['errors'] = [
            1 => $this->l('Advertisement title is required'),
            2 => $this->l('Length of advertisement title must be less than 128 characters'),
            3 => $this->l('Invalid advertisement title for selected language'),
            4 => $this->l('Advertisement description is required'),
            5 => $this->l('Invalid advertisement description for the selected language'),
            6 => $this->l('Donate button text is required'),
            7 => $this->l('Invalid donate button text for the selected language'),
            8 => $this->l('Donate button text color is invalid'),
            9 => $this->l('Donate button border color is invalid'),
            10 => $this->l('Advertisement title text color is invalid'),
            11 => $this->l('Advertisement description text color is invalid'),
            12 => $this->l('Select at least one place for advertisement'),
            13 => $this->l('Background image is required for header/footer advertisement'),
            14 => $this->l('Image format not recognized for header/footer advertisement, allowed formats are: .gif, .jpg, .png, .jpeg'),
            15 => $this->l('Background image is required for left/right advertisement'),
            16 => $this->l('Image format not recognized for left/right advertisement, allowed formats are: .gif, .jpg, .png, .jpeg'),
            17 => $this->l('Something went wrong'),
            18 => $this->l('Advertisement description use a HTML tag which is not allowed'),
        ];

        $this->context->smarty->assign($smartyVars);
        $this->context->smarty->assign([
            'pages' => WkDonationDisplayPlaces::getDonationPages(),
            'hooks' => WkDonationDisplayPlaces::getDonationHooks(),
            'donate_hooks' => $donationHooks,
            'ps_img_lang_dir' => _PS_IMG_ . 'l/',
            'maxSizeAllowed' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
        ]);

        $this->fields_form = [
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }

    public function getAdvertisementBannerImagePath($imageName)
    {
        $path = _MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $imageName . '.jpg';
        if (file_exists(_PS_MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $imageName . '.jpg')) {
            return $path;
        }

        return false;
    }

    public function processSave()
    {
        $defaultLangId = Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        $objDefaultLanguage = Language::getLanguage((int) $defaultLangId);
        $active = Tools::getValue('activate_global_donation');
        $showDonateButton = Tools::getValue('show_donate_button');
        $advTitleColor = Tools::getValue('adv_title_color');
        $advDescColor = Tools::getValue('adv_desc_color');
        $buttonTextColor = Tools::getValue('button_text_color');
        $buttonBorderColor = Tools::getValue('button_border_color');
        $donationPageHook = Tools::getValue('page_hook');

        $idDonationInfo = WkDonationInfo::getIdGlobalDonation();
        $idShop = Context::getContext()->shop->id;
        $imagePath_head_foot = $this->getAdvertisementBannerImagePath($idDonationInfo . '_' . $idShop . '-head-foot');
        $imagePath_left_right = $this->getAdvertisementBannerImagePath($idDonationInfo . '_' . $idShop . '-left-right');

        if (!trim(Tools::getValue('advertisement_title_' . $defaultLangId))) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=1');
        } elseif (strlen(trim(Tools::getValue('advertisement_title_' . $defaultLangId))) >= 128) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=2');
        } else {
            foreach ($languages as $language) {
                if (!Validate::isCatalogName(Tools::getValue('advertisement_title_' . $language['id_lang']))) {
                    Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=3');
                }
            }
        }
        if (!trim(Tools::getValue('advertisement_description_' . $defaultLangId))) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=4');
        } elseif (preg_match('/<\s?[^\>]*\/?\s?>/i', html_entity_decode(strip_tags(Tools::getValue('advertisement_description_' . $defaultLangId))))) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=18');
        } else {
            foreach ($languages as $language) {
                if (!Validate::isCleanHtml(Tools::getValue('advertisement_description_' . $language['id_lang']))
                ) {
                    Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=5');
                }
            }
        }
        if ($showDonateButton) {
            if (!trim(Tools::getValue('donate_button_text_' . $defaultLangId))) {
                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=6');
            } else {
                foreach ($languages as $language) {
                    if (!Validate::isGenericName(Tools::getValue('donate_button_text_' . $language['id_lang']))) {
                        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=7');
                    }
                }
            }
            if (!Validate::isColor($buttonTextColor)) {
                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=8');
            }
            if (!Validate::isColor($buttonBorderColor)) {
                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=9');
            }
        }
        if (!Validate::isColor($advTitleColor)) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=10');
        }
        if (!Validate::isColor($advDescColor)) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=11');
        }

        if (empty($donationPageHook)) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=12');
        } else {
            if (!$imagePath_head_foot) {
                foreach ($donationPageHook as $donationhook) {
                    if (in_array(WkDonationDisplayPlaces::WK_DONATION_HOOK_HOME, $donationhook)
                        || in_array(WkDonationDisplayPlaces::WK_DONATION_HOOK_FOOTER, $donationhook)) {
                        if ($imgHeadFoot = $_FILES['background_image_head_foot']) {
                            if (empty($imgHeadFoot['name'])) {
                                if (!$this->getAdvertisementBannerImagePath(
                                    WkDonationInfo::getIdGlobalDonation() . '-head-foot'
                                )) {
                                    Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=13');
                                }
                            } elseif (!ImageManager::isRealImage($imgHeadFoot['tmp_name'], $imgHeadFoot['type'])
                                || !ImageManager::isCorrectImageFileExt($imgHeadFoot['name'])
                            ) {
                                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=14');
                            }
                        }
                        break;
                    }
                }
            } else {
                if ($imgHeadFoot = $_FILES['background_image_head_foot']) {
                    if (!empty($imgHeadFoot['name'])) {
                        if (!ImageManager::isRealImage($imgHeadFoot['tmp_name'], $imgHeadFoot['type'])
                        || !ImageManager::isCorrectImageFileExt($imgHeadFoot['name'])
                        ) {
                            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=14');
                        }
                    }
                }
            }
            if (!$imagePath_left_right) {
                foreach ($donationPageHook as $donationhook) {
                    if (in_array(WkDonationDisplayPlaces::WK_DONATION_HOOK_LEFT, $donationhook)
                        || in_array(WkDonationDisplayPlaces::WK_DONATION_HOOK_RIGHT, $donationhook)) {
                        if ($imgLeftRight = $_FILES['background_image_left_right']) {
                            if (empty($imgLeftRight['name'])) {
                                if (!$this->getAdvertisementBannerImagePath(
                                    WkDonationInfo::getIdGlobalDonation() . '-left-right'
                                )) {
                                    Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=15');
                                }
                            } elseif (!ImageManager::isRealImage($imgLeftRight['tmp_name'], $imgLeftRight['type'])
                                    || !ImageManager::isCorrectImageFileExt($imgLeftRight['name'])
                            ) {
                                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=16');
                            }
                        }
                        break;
                    }
                }
            } else {
                if ($imgLeftRight = $_FILES['background_image_left_right']) {
                    if (!empty($imgLeftRight['name'])) {
                        if (!ImageManager::isRealImage($imgLeftRight['tmp_name'], $imgLeftRight['type'])
                        || !ImageManager::isCorrectImageFileExt($imgLeftRight['name'])
                        ) {
                            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&err=16');
                        }
                    }
                }
            }
        }

        if (!count($this->errors)) {
            if ($idDonationInfo) {
                $objDonationInfo = new WkDonationInfo($idDonationInfo);
            } else {
                $objDonationInfo = new WkDonationInfo();
            }
            $objDonationInfo->id_product = 0;
            $objDonationInfo->active = $active;
            $objDonationInfo->product_visibility = 1;
            $objDonationInfo->price_type = 0;
            $objDonationInfo->price = 0;
            $objDonationInfo->show_at_checkout = 0;
            $objDonationInfo->advertise = $active;
            $objDonationInfo->expiry_date = 0000 - 00 - 00;
            $objDonationInfo->show_donate_button = $showDonateButton;
            $objDonationInfo->adv_title_color = $advTitleColor;
            $objDonationInfo->adv_desc_color = $advDescColor;
            if ($showDonateButton) {
                $objDonationInfo->button_text_color = $buttonTextColor;
                $objDonationInfo->button_border_color = $buttonBorderColor;
            }
            $objDonationInfo->is_global = 1;

            foreach ($languages as $language) {
                $objDonationInfo->name[$language['id_lang']] = $this->l('global donation');
                $objDonationInfo->description[$language['id_lang']] = $this->l('global donation');

                if (trim(Tools::getValue('advertisement_title_' . $language['id_lang']))) {
                    $objDonationInfo->advertisement_title[$language['id_lang']] = trim(Tools::getValue(
                        'advertisement_title_' . $language['id_lang']
                    ));
                } else {
                    $objDonationInfo->advertisement_title[$language['id_lang']] =
                        trim(Tools::getValue('advertisement_title_' . $defaultLangId));
                }
                if (trim(Tools::getValue('advertisement_description_' . $language['id_lang']))) {
                    $objDonationInfo->advertisement_description[$language['id_lang']] = Tools::getValue(
                        'advertisement_description_' . $language['id_lang']
                    );
                } else {
                    $objDonationInfo->advertisement_description[$language['id_lang']] =
                        trim(Tools::getValue('advertisement_description_' . $defaultLangId));
                }
                if ($showDonateButton) {
                    if (trim(Tools::getValue('donate_button_text_' . $language['id_lang']))) {
                        $objDonationInfo->donate_button_text[$language['id_lang']] =
                            trim(Tools::getValue('donate_button_text_' . $language['id_lang']));
                    } else {
                        $objDonationInfo->donate_button_text[$language['id_lang']] =
                            trim(Tools::getValue('donate_button_text_' . $defaultLangId));
                    }
                }
            }

            $objDonationInfo->save();
            $idDonationInfo = $objDonationInfo->id;
            // delete previous hooks
            $idShop = $this->context->shop->id;
            $objDonationDisplayPlace = new WkDonationDisplayPlaces();
            // Code added to upload images & hooks shopwise
            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                $shops = Shop::getShops(false);
                if ($shops) {
                    foreach ($shops as $shop) {
                        ImageManager::resize(
                            $_FILES['background_image_head_foot']['tmp_name'],
                            _PS_MODULE_DIR_ . 'wkcharitydonation/views/img/banner/' . $idDonationInfo . '_' . $shop['id_shop'] . '-head-foot.jpg'
                        );
                        ImageManager::resize(
                            $_FILES['background_image_left_right']['tmp_name'],
                            _PS_MODULE_DIR_ . 'wkcharitydonation/views/img/banner/' . $idDonationInfo . '_' . $shop['id_shop'] . '-left-right.jpg'
                        );
                        $selectedHooks = $objDonationDisplayPlace->getDonationHooksByIdDonation($idDonationInfo, $shop['id_shop']);
                        $hookArray = array_column($selectedHooks, 'id_hook');
                        foreach ($hookArray as $hook) {
                            $objDonationDisplayPlace->deleteDonationHooks($idDonationInfo, $hook, $shop['id_shop']);
                        }
                        // add new hooks
                        foreach ($donationPageHook as $idPage => $pageHooks) {
                            foreach ($pageHooks as $idHook) {
                                $objDonationDisplayPlace->insertDonationHooks($idDonationInfo, $idHook, $idPage, $shop['id_shop']);
                            }
                        }
                    }
                }
            } else {
                ImageManager::resize(
                    $_FILES['background_image_head_foot']['tmp_name'],
                    _PS_MODULE_DIR_ . 'wkcharitydonation/views/img/banner/' . $idDonationInfo . '_' . $idShop . '-head-foot.jpg'
                );
                ImageManager::resize(
                    $_FILES['background_image_left_right']['tmp_name'],
                    _PS_MODULE_DIR_ . 'wkcharitydonation/views/img/banner/' . $idDonationInfo . '_' . $idShop . '-left-right.jpg'
                );
                $selectedHooks = $objDonationDisplayPlace->getDonationHooksByIdDonation($idDonationInfo, $idShop);
                $hookArray = array_column($selectedHooks, 'id_hook');
                foreach ($hookArray as $hook) {
                    $objDonationDisplayPlace->deleteDonationHooks($idDonationInfo, $hook, $idShop);
                }
                // add new hooks
                foreach ($donationPageHook as $idPage => $pageHooks) {
                    foreach ($pageHooks as $idHook) {
                        $objDonationDisplayPlace->insertDonationHooks($idDonationInfo, $idHook, $idPage, $idShop);
                    }
                }
            }

            if (!count($this->errors)) {
                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=4');
            }
        } else {
            $this->display = 'add';
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setmedia($isNewTheme);
        // Code added for cross selling banner
        Media::addJsDef([
            'module_dir' => _MODULE_DIR_,
            'wkModuleAddonKey' => $this->module->module_key,
            'wkModuleAddonsId' => '45993',
            'wkModuleTechName' => $this->module->name,
            'wkModuleDoc' => file_exists(_PS_MODULE_DIR_ . $this->module->name . '/docs/doc_en.pdf'),
        ]);
        $this->context->controller->addJs('https://prestashop.webkul.com/crossselling/wkcrossselling.min.js?t=' . time());
        // Code end
        $this->addJqueryPlugin('colorpicker');
        Media::addJsDef([
            'ps_img_lang_dir' => _PS_IMG_ . 'l/',
            'maxSizeAllowed' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
            'filesizeError' => $this->l('File exceeds maximum size.'),
            'adminManageDonationUrl' => $this->context->link->getAdminLink('AdminManageDonation'),
            'confirmDelete' => $this->l('Are you sure want to delete image.'),
        ]);

        $this->addJS(_PS_JS_DIR_ . 'tiny_mce/tiny_mce.js');
        if (version_compare(_PS_VERSION_, '1.6.0.11', '>')) {
            $this->addJS(_PS_JS_DIR_ . 'admin/tinymce.inc.js');
        } else {
            $this->addJS(_PS_JS_DIR_ . 'tinymce.inc.js');
        }
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/admin/wk_global.js');
    }
}
