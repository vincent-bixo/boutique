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

class AdminManageDonationController extends ModuleAdminController
{
    protected $position_identifier = 'id_donation_info';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'wk_donation_info';
        $this->identifier = 'id_donation_info';
        $this->className = 'WkDonationInfo';
        $this->_defaultOrderBy = 'position';

        parent::__construct();

        $this->_select .= 'dl.name';
        $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'wk_donation_info_lang` dl ON (dl.`id_donation_info` = a.`id_donation_info`)';
        Shop::addTableAssociation('wk_donation_info', ['type' => 'shop', 'primary' => 'id_donation_info']);
        $this->_where = ' AND dl.`id_lang` = ' . (int) $this->context->language->id . " AND a.`is_global` = '0'";
        $this->_group = 'GROUP BY a.id_donation_info';

        $this->toolbar_title = $this->l('Manage Donation');

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected Donations?'),
                'icon' => 'icon-trash',
            ],
        ];

        $priceType = [];
        $priceType[WkDonationInfo::WK_DONATION_PRICE_TYPE_FIXED] = $this->l('Fixed');
        $priceType[WkDonationInfo::WK_DONATION_PRICE_TYPE_CUSTOMER] = $this->l('By customer');

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
            $this->_select .= ', sh.`name` as wk_donation_info_shop_name';
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'wk_donation_info_shop` wdis ON (wdis.`id_donation_info` = a.`id_donation_info`)';
            $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` sh ON (sh.`id_shop` = wdis.`id_shop`)';
        }

        $this->fields_list = [
            'id_donation_info' => [
                'title' => $this->l('Id'),
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => $this->l('Donation name'),
            ],
            'price_type' => [
                'title' => $this->l('Price type'),
                'type' => 'select',
                'hint' => $this->l('\'Fixed\' means donation amount is fixed, \'By customer\' means donation amount can be entered by customer'),
                'list' => $priceType,
                'filter_key' => 'a!price_type',
                'callback' => 'getPriceType',
            ],
            'price' => [
                'title' => $this->l('Price'),
                'type' => 'price',
                'filter_key' => 'a!price',
            ],
            'advertise' => [
                'type' => 'bool',
                'filter_key' => 'a!advertise',
                'title' => $this->l('Advertise'),
                'callback' => 'showAdvertise',
            ],
            'position' => [
                'title' => $this->l('Priority'),
                'hint' => $this->l('Priority define the ordering in which multiple dontions will be shown'),
                'filter_key' => 'a!position',
                'position' => 'position',
            ],
            'active' => [
                'title' => $this->l('Status'),
                'align' => 'center',
                'active' => 'status',
                'type' => 'bool',
                'class' => 'fixed-width-xs',
                'orderby' => false,
            ],
        ];
        // In case of All Shops
        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
            $this->fields_list['wk_donation_info_shop_name'] = [
                'title' => $this->l('Shop'),
                'havingFilter' => true,
                'align' => 'center',
            ];
        }

        $objDonationInfo = new WkDonationInfo();
        $objDonationInfo->updateDonationExpiry();
    }

    public function processFilter()
    {
        Hook::exec('action' . $this->controller_name . 'ListingFieldsModifier', [
            'fields' => &$this->fields_list,
        ]);

        if (!isset($this->list_id)) {
            $this->list_id = $this->table;
        }

        $prefix = $this->getCookieFilterPrefix();

        if (isset($this->list_id)) {
            foreach ($_POST as $key => $value) {
                if ($value === '|') {
                    exit(Tools::displayError($this->l('Search key is not valid')));
                }
                if ($value === '') {
                    unset($this->context->cookie->{$prefix . $key});
                } elseif (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : json_encode($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : json_encode($value);
                }
            }

            foreach ($_GET as $key => $value) {
                if (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : json_encode($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : json_encode($value);
                }
                if (stripos($key, $this->list_id . 'Orderby') === 0 && Validate::isOrderBy($value)) {
                    if ($value === '' || $value == $this->_defaultOrderBy) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key} = $value;
                    }
                } elseif (stripos($key, $this->list_id . 'Orderway') === 0 && Validate::isOrderWay($value)) {
                    if ($value === '' || $value == $this->_defaultOrderWay) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key} = $value;
                    }
                }
            }
        }

        $filters = $this->context->cookie->getFamily($prefix . $this->list_id . 'Filter_');
        $definition = false;
        if (isset($this->className) && $this->className) {
            $definition = ObjectModel::getDefinition($this->className);
        }

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if ($value != null && !strncmp($key, $prefix . $this->list_id . 'Filter_', 7 + Tools::strlen($prefix . $this->list_id))) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix . $this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                    if (($type == 'date' || $type == 'datetime') && is_string($value)) {
                        $value = json_decode($value, true);
                    }
                    $key = isset($tmp_tab[1]) ? $tmp_tab[0] . '.`' . $tmp_tab[1] . '`' : '`' . $tmp_tab[0] . '`';

                    // Assignment by reference
                    if (array_key_exists('tmpTableFilter', $field)) {
                        $sql_filter = &$this->_tmpTableFilter;
                    } elseif (array_key_exists('havingFilter', $field)) {
                        $sql_filter = &$this->_filterHaving;
                    } else {
                        $sql_filter = &$this->_filter;
                    }

                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if (isset($value[0]) && !empty($value[0])) {
                            if (!Validate::isDate($value[0])) {
                                $this->errors[] = $this->trans('The \'From\' date format is invalid (YYYY-MM-DD)', [], 'Admin.Notifications.Error');
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' >= \'' . pSQL(Tools::dateFrom($value[0])) . '\'';
                            }
                        }

                        if (isset($value[1]) && !empty($value[1])) {
                            if (!Validate::isDate($value[1])) {
                                $this->errors[] = $this->trans('The \'To\' date format is invalid (YYYY-MM-DD)', [], 'Admin.Notifications.Error');
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' <= \'' . pSQL(Tools::dateTo($value[1])) . '\'';
                            }
                        }
                    } else {
                        $sql_filter .= ' AND ';
                        $check_key = ($key == $this->identifier || $key == '`' . $this->identifier . '`');
                        $alias = ($definition && !empty($definition['fields'][$filter]['shop'])) ? 'sa' : 'a';

                        if ($type == 'int' || $type == 'bool') {
                            $sql_filter .= (($check_key || $key == '`active`') ? $alias . '.' : '') . pSQL($key) . ' = ' . (int) $value . ' ';
                        } elseif ($type == 'decimal') {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = ' . (float) $value . ' ';
                        } elseif ($type == 'select') {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = \'' . pSQL($value) . '\' ';
                        } elseif ($type == 'price') {
                            $value = (float) str_replace(',', '.', $value);
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = ' . pSQL(trim($value)) . ' ';
                        } else {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' LIKE \'%' . pSQL(trim($value)) . '%\' ';
                        }
                    }
                }
            }
        }
    }

    protected function filterToField($reqKey, $reqFilter)
    {
        $prefix = $this->getCookieFilterPrefix();
        $filters = $this->context->cookie->getFamily($prefix . $this->list_id . 'Filter_');
        if (in_array($reqFilter, ['name'])) {
            foreach ($filters as $key => $value) {
                if ($value != null
                    && !strncmp($key, $prefix . $this->list_id . 'Filter_', 7 + Tools::strlen($prefix . $this->list_id))
                ) {
                    $key = Tools::substr($key, 7 + Tools::strlen($prefix . $this->list_id));
                    /* Table alias could be specified using a ! eg. alias!field */
                    $tmp_tab = explode('!', $key);
                    $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                    if ($filter == $reqFilter) {
                        if ($reqFilter == 'name') {
                            $this->_filter .= ' AND (dl.`name` LIKE "%' . $value . '%")';
                        }

                        return false;
                    }
                }
            }
        } elseif ($reqFilter == 'id_event') {
            return false;
        } else {
            return parent::filterToField($reqKey, $reqFilter);
        }
    }

    public function getPriceType($row)
    {
        if ($row == WkDonationInfo::WK_DONATION_PRICE_TYPE_FIXED) {
            return $this->l('Fixed');
        } elseif ($row == WkDonationInfo::WK_DONATION_PRICE_TYPE_CUSTOMER) {
            return $this->l('By customer');
        }
    }

    public function showAdvertise($row)
    {
        $this->context->smarty->assign('showAdvertise', $row);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name .
            '/views/templates/admin/manage_donation/helpers/_partials/advertise-badge.tpl'
        );
    }

    public function initPageHeaderToolbar()
    {
        if ($this->display != 'add' && $this->display != 'edit') {
            $this->page_header_toolbar_btn['new'] = [
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->l('Add new donation'),
            ];
        }
        parent::initPageHeaderToolbar();
    }

    public function renderForm()
    {
        $smartyVars = [];
        $objDonationInfo = $this->loadObject(true);
        $idShop = Context::getContext()->shop->id;
        if (Validate::isLoadedObject($objDonationInfo)) {
            $smartyVars['donationInfo'] = (array) $objDonationInfo;
            $objImage = new Image();
            $donationImages = $objImage->getImages($this->context->language->id, $objDonationInfo->id_product);
            if ($donationImages) {
                foreach ($donationImages as &$image) {
                    $image['image_link'] = _PS_IMG_
                    . 'p/' . $objImage->getImgFolderStatic($image['id_image'])
                    . $image['id_image']
                    . '.jpg';
                }
                $smartyVars['donationImages'] = $donationImages;
            }
            $smartyVars['imagePath_head_foot'] = $this->getAdvertisementBannerImagePath(
                $objDonationInfo->id . '_' . $idShop . '-head-foot'
            );
            $smartyVars['imagePath_left_right'] = $this->getAdvertisementBannerImagePath(
                $objDonationInfo->id . '_' . $idShop . '-left-right'
            );
            $smartyVars['header_file'] = $objDonationInfo->id . '-head-foot';
            $smartyVars['left_right_file'] = $objDonationInfo->id . '-left-right';
        }

        $smartyVars['errors'] = [
            1 => $this->l('Advertisement title is required'),
            2 => $this->l('Invalid advertisement title for selected language.'),
            3 => $this->l('Advertisement description is required.'),
            4 => $this->l('Invalid advertisement description for selected language'),
            5 => $this->l('Invalid donate button text'),
            6 => $this->l('Advertisement title text color is invalid'),
            7 => $this->l('Advertisement description text color is invalid'),
            8 => $this->l('Donate button text is required'),
            9 => $this->l('Donate button text color is invalid'),
            10 => $this->l('Donate button border color is invalid'),
            11 => $this->l('Select atleast one place for advertisement'),
            12 => $this->l('Background image is required for header/footer advertisement'),
            13 => $this->l('Image format not recognized for header/footer advertisement, allowed formats are: .gif, .jpg, .png, .jpeg'),
            14 => $this->l('Background image is required for left/right advertisement'),
            15 => $this->l('Image format not recognized for left/right advertisement, allowed formats are: .gif, .jpg, .png, .jpeg'),
            16 => $this->l('Image format not recognized for left/right advertisement, allowed formats are: .gif, .jpg, .png, .jpeg'),
            17 => $this->l('Something went wrong'),
            18 => $this->l('Advertisement description use a HTML tag which is not allowed'),
            19 => $this->l('Description contains HTML tag which is not allowed'),
        ];
        $objDisplayPlaces = new WkDonationDisplayPlaces();
        $donationPages = $objDisplayPlaces->getDonationPagesByIdDonation($objDonationInfo->id);
        $donationHooks = [];
        foreach ($donationPages as $page) {
            $donationHooks[$page['id_page']] = $objDisplayPlaces->getDonationHooksByIdPage(
                $objDonationInfo->id,
                $page['id_page'],
                Context::getContext()->shop->id
            );
        }

        $smartyVars['languages'] = Language::getLanguages(false);
        $currentLangId = Configuration::get('PS_LANG_DEFAULT');
        $smartyVars['currentLang'] = Language::getLanguage((int) $currentLangId);

        $this->context->smarty->assign($smartyVars);
        $this->context->smarty->assign(
            [
                'adminManageDonationUrl' => $this->context->link->getAdminLink('AdminManageDonation'),
                'pages' => WkDonationDisplayPlaces::getDonationPages(),
                'hooks' => WkDonationDisplayPlaces::getDonationHooks(),
                'donate_hooks' => $donationHooks,
                'defaultCurrencySign' => $this->context->currency->sign,
                'ps_img_lang_dir' => _PS_IMG_ . 'l/',
                'maxSizeAllowed' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'active_tab' => Tools::getValue('tab'),
            ]
        );
        $this->fields_form = [
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }

    public function processStatus()
    {
        if (Validate::isLoadedObject($objDonationInfo = $this->loadObject())) {
            $today = date('Y-m-d');
            $expiry_date = date('Y-m-d', strtotime($objDonationInfo->expiry_date));
            if ($expiry_date > 0) {
                if ($today > $expiry_date) {
                    $this->errors[] = $this->l('Please update the donation expiry before enabling the donation');
                }
            }
        }
        if (!count($this->errors)) {
            $this->toggleDonationProduct();
        }
    }

    public function toggleDonationProduct()
    {
        if ($id_donation_info = Tools::getValue('id_donation_info')) {
            if ($objDonationInfo = new WkDonationInfo($id_donation_info)) {
                if ($objDonationInfo->active) {
                    $objDonationInfo->active = 0;
                } else {
                    $objDonationInfo->active = 1;
                }
                if ($objDonationInfo->save()) {
                    $objDonationProduct = new Product($objDonationInfo->id_product);
                    if ($objDonationInfo->active) {
                        $objDonationProduct->active = 1;
                    } else {
                        $objDonationProduct->active = 0;
                    }
                    if ($objDonationProduct->save()) {
                        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=5');
                    }
                }
            }
            $this->errors[] = $this->l('There was a problem in changing the  status of the donation, Please try again later');
        }
    }

    public function processSave()
    {
        $active = Tools::getValue('activate_donation');
        $priceType = Tools::getValue('price_type');
        $price = Tools::getValue('price');
        $expiryDate = trim(Tools::getValue('expiry_date'));
        $productVisibility = Tools::getValue('product_visibility');
        $showAtCheckout = Tools::getValue('show_at_checkout');
        $advertise = Tools::getValue('advertise');
        $showDonateButton = Tools::getValue('show_donate_button');
        $advTitleColor = Tools::getValue('adv_title_color');
        $advDescColor = Tools::getValue('adv_desc_color');
        $buttonTextColor = Tools::getValue('button_text_color');
        $buttonBorderColor = Tools::getValue('button_border_color');
        $donationPageHook = Tools::getValue('page_hook');
        $defaultLangId = Configuration::get('PS_LANG_DEFAULT');
        $objDefaultLanguage = Language::getLanguage((int) $defaultLangId);
        $languages = Language::getLanguages(false);

        $id = Tools::getValue('id_donation_info');
        $idShop = Context::getContext()->shop->id;
        $imagePath_head_foot = $this->getAdvertisementBannerImagePath($id . '_' . $idShop . '-head-foot');
        $imagePath_left_right = $this->getAdvertisementBannerImagePath($id . '_' . $idShop . '-left-right');

        // validation
        if (!trim(Tools::getValue('name_' . $defaultLangId))) {
            $this->errors[] = sprintf(
                $this->l('Donation name is required at least in %s'),
                $objDefaultLanguage['name']
            );
        } else {
            foreach ($languages as $language) {
                if (!Validate::isCatalogName(Tools::getValue('name_' . $language['id_lang']))) {
                    $this->errors[] = sprintf(
                        $this->l('Invalid donation name for the language %s'),
                        $language['name']
                    );
                }
            }
        }

        if (!trim(Tools::getValue('description_' . $defaultLangId))) {
            $this->errors[] = sprintf(
                $this->l('Donation description is required at least in %s'),
                $objDefaultLanguage['name']
            );
        } elseif (preg_match('/<\s?[^\>]*\/?\s?>/i', html_entity_decode(strip_tags(Tools::getValue('description_' . $defaultLangId))))) {
            Tools::redirectAdmin(
                self::$currentIndex .
                '&id_donation_info=' . (int) $id .
                '&update' . $this->table .
                '&err=19&tab=' . Tools::getValue('active_tab') .
                '&token=' . $this->token
            );
        } else {
            foreach ($languages as $language) {
                if (!Validate::isCleanHtml(Tools::getValue('description_' . $language['id_lang']))
                ) {
                    $this->errors[] = sprintf(
                        $this->l('Invalid donation description for the language %s'),
                        $language['name']
                    );
                }
            }
        }
        if ($advertise) {
            if (!trim(Tools::getValue('advertisement_title_' . $defaultLangId))) {
                Tools::redirectAdmin(
                    self::$currentIndex .
                    '&id_donation_info=' . (int) $id .
                    '&update' . $this->table .
                    '&err=1&tab=' . Tools::getValue('active_tab') .
                    '&token=' . $this->token
                );
            } else {
                foreach ($languages as $language) {
                    if (!Validate::isCatalogName(Tools::getValue('advertisement_title_' . $language['id_lang']))) {
                        Tools::redirectAdmin(
                            self::$currentIndex .
                            '&id_donation_info=' . (int) $id .
                            '&update' . $this->table .
                            '&err=2&tab=' . Tools::getValue('active_tab') .
                            '&token=' . $this->token
                        );
                    }
                }
            }
            if (!trim(Tools::getValue('advertisement_description_' . $defaultLangId))) {
                Tools::redirectAdmin(
                    self::$currentIndex .
                    '&id_donation_info=' . (int) $id .
                    '&update' . $this->table .
                    '&err=3&tab=' . Tools::getValue('active_tab') .
                    '&token=' . $this->token
                );
            } elseif (preg_match('/<\s?[^\>]*\/?\s?>/i', html_entity_decode(strip_tags(Tools::getValue('advertisement_description_' . $defaultLangId))))) {
                Tools::redirectAdmin(
                    self::$currentIndex .
                    '&id_donation_info=' . (int) $id .
                    '&update' . $this->table .
                    '&err=18&tab=' . Tools::getValue('active_tab') .
                    '&token=' . $this->token
                );
            } else {
                foreach ($languages as $language) {
                    if (!Validate::isCleanHtml(Tools::getValue('advertisement_description_' . $language['id_lang']))
                    ) {
                        Tools::redirectAdmin(
                            self::$currentIndex .
                            '&id_donation_info=' . (int) $id .
                            '&update' . $this->table .
                            '&err=4&tab=' . Tools::getValue('active_tab') .
                            '&token=' . $this->token
                        );
                    }
                }
            }
        }

        foreach ($languages as $language) {
            if (!Validate::isGenericName(Tools::getValue('donate_button_text_' . $language['id_lang']))) {
                Tools::redirectAdmin(
                    self::$currentIndex .
                    '&id_donation_info=' . (int) $id .
                    '&update' . $this->table .
                    '&err=5&tab=' . Tools::getValue('active_tab') .
                    '&token=' . $this->token
                );
            }
        }

        if ((Validate::isUnsignedInt($price) || Validate::isUnsignedFloat($price)) && $price <= 0) {
            $this->errors[] = $this->l('Donation price must be greater than zero');
        } elseif (empty($price)) {
            $this->errors[] = $this->l('Donation price must not be empty');
        } elseif (!Validate::isPrice($price)) {
            $this->errors[] = $this->l('Donation price is invalid');
        }

        if (!empty($expiryDate)) {
            if (!Validate::isDateFormat($expiryDate)) {
                $this->errors[] = $this->l('Expiry date in invalid format');
            } else {
                $currentDate = date('Y-m-d');
                $expiryDate = date('Y-m-d', strtotime($expiryDate));
                if ($currentDate > $expiryDate) {
                    $this->errors[] = $this->l('Expiry date must be greater or equal to current date');
                }
            }
        }
        if (!Validate::isColor($advTitleColor)) {
            Tools::redirectAdmin(
                self::$currentIndex .
                '&id_donation_info=' . (int) $id .
                '&update' . $this->table .
                '&err=6&tab=' . Tools::getValue('active_tab') .
                '&token=' . $this->token
            );
        }
        if (!Validate::isColor($advDescColor)) {
            Tools::redirectAdmin(
                self::$currentIndex .
                '&id_donation_info=' . (int) $id .
                '&update' . $this->table .
                '&err=7&tab=' . Tools::getValue('active_tab') .
                '&token=' . $this->token
            );
        }

        if ($showDonateButton == 1) {
            if (!trim(Tools::getValue('donate_button_text_' . $defaultLangId))) {
                Tools::redirectAdmin(
                    self::$currentIndex .
                    '&id_donation_info=' . (int) $id .
                    '&update' . $this->table .
                    '&err=8&tab=' . Tools::getValue('active_tab') .
                    '&token=' . $this->token
                );
            }
            if (!Validate::isColor($buttonTextColor)) {
                Tools::redirectAdmin(
                    self::$currentIndex .
                    '&id_donation_info=' . (int) $id .
                    '&update' . $this->table .
                    '&err=9&tab=' . Tools::getValue('active_tab') .
                    '&token=' . $this->token
                );
            }
            if (!Validate::isColor($buttonBorderColor)) {
                Tools::redirectAdmin(
                    self::$currentIndex .
                    '&id_donation_info=' . (int) $id .
                    '&update' . $this->table .
                    '&err=10&tab=' . Tools::getValue('active_tab') .
                    '&token=' . $this->token
                );
            }
        }

        if ($advertise) {
            if (empty($donationPageHook)) {
                Tools::redirectAdmin(
                    self::$currentIndex .
                    '&id_donation_info=' . (int) $id .
                    '&update' . $this->table .
                    '&err=11&tab=' . Tools::getValue('active_tab') .
                    '&token=' . $this->token
                );
            } else {
                if (!$imagePath_head_foot) {
                    foreach ($donationPageHook as $donationhook) {
                        if (in_array(WkDonationDisplayPlaces::WK_DONATION_HOOK_HOME, $donationhook)
                            || in_array(WkDonationDisplayPlaces::WK_DONATION_HOOK_FOOTER, $donationhook)) {
                            if ($imgHeadFoot = $_FILES['background_image_head_foot']) {
                                if (empty($imgHeadFoot['name'])) {
                                    if (!$this->getAdvertisementBannerImagePath(
                                        Tools::getValue('id_donation_info') . '-head-foot'
                                    )) {
                                        Tools::redirectAdmin(
                                            self::$currentIndex .
                                            '&id_donation_info=' . (int) $id .
                                            '&update' . $this->table .
                                            '&err=12&tab=' . Tools::getValue('active_tab') .
                                            '&token=' . $this->token
                                        );
                                    }
                                } elseif (!ImageManager::isRealImage($imgHeadFoot['tmp_name'], $imgHeadFoot['type'])
                                    || !ImageManager::isCorrectImageFileExt($imgHeadFoot['name'])
                                ) {
                                    Tools::redirectAdmin(
                                        self::$currentIndex .
                                        '&id_donation_info=' . (int) $id .
                                        '&update' . $this->table .
                                        '&err=13&tab=' . Tools::getValue('active_tab') .
                                        '&token=' . $this->token
                                    );
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
                                Tools::redirectAdmin(
                                    self::$currentIndex .
                                    '&id_donation_info=' . (int) $id .
                                    '&update' . $this->table .
                                    '&err=13&tab=' . Tools::getValue('active_tab') .
                                    '&token=' . $this->token
                                );
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
                                        Tools::getValue('id_donation_info') . '-left-right'
                                    )) {
                                        Tools::redirectAdmin(
                                            self::$currentIndex .
                                            '&id_donation_info=' . (int) $id .
                                            '&update' . $this->table .
                                            '&err=14&tab=' . Tools::getValue('active_tab') .
                                            '&token=' . $this->token
                                        );
                                    }
                                } elseif (!ImageManager::isRealImage($imgLeftRight['tmp_name'], $imgLeftRight['type'])
                                    || !ImageManager::isCorrectImageFileExt($imgLeftRight['name'])
                                ) {
                                    Tools::redirectAdmin(
                                        self::$currentIndex .
                                        '&id_donation_info=' . (int) $id .
                                        '&update' . $this->table .
                                        '&err=15&tab=' . Tools::getValue('active_tab') .
                                        '&token=' . $this->token
                                    );
                                }
                            }
                            break;
                        }
                    }
                } else {
                    if ($imgLeftRight = $_FILES['background_image_left_right']) {
                        if (empty($imgLeftRight['name'])) {
                            if (!ImageManager::isRealImage($imgLeftRight['tmp_name'], $imgLeftRight['type'])
                            || !ImageManager::isCorrectImageFileExt($imgLeftRight['name'])
                            ) {
                                Tools::redirectAdmin(
                                    self::$currentIndex .
                                    '&id_donation_info=' . (int) $id .
                                    '&update' . $this->table .
                                    '&err=15&tab=' . Tools::getValue('active_tab') .
                                    '&token=' . $this->token
                                );
                            }
                        }
                    }
                }
            }
        }

        // if validation passes
        if (!count($this->errors)) {
            if ($id) {
                $objDonationInfo = new WkDonationInfo($id);
            } else {
                $objDonationInfo = new WkDonationInfo();
                $objDonationInfo->id_product = 0;
                $objDonationInfo->position = WkDonationInfo::getHigherPosition();
            }

            $objDonationInfo->active = $active;
            $objDonationInfo->product_visibility = $productVisibility;
            $objDonationInfo->price_type = $priceType;
            $objDonationInfo->price = $price;
            $objDonationInfo->show_at_checkout = $showAtCheckout;
            $objDonationInfo->advertise = $advertise;
            $objDonationInfo->expiry_date = $expiryDate;
            $objDonationInfo->show_donate_button = $showDonateButton;
            $objDonationInfo->adv_title_color = $advTitleColor;
            $objDonationInfo->adv_desc_color = $advDescColor;
            if ($showDonateButton) {
                $objDonationInfo->button_text_color = $buttonTextColor;
                $objDonationInfo->button_border_color = $buttonBorderColor;
            }

            foreach (Language::getLanguages(false) as $language) {
                $objDonationInfo->name[$language['id_lang']] = trim(Tools::getValue(
                    'name_' . $language['id_lang']
                ));

                $objDonationInfo->description[$language['id_lang']] = trim(Tools::getValue(
                    'description_' . $language['id_lang']
                ));
            }
            if ($advertise) {
                foreach (Language::getLanguages(false) as $language) {
                    if (Tools::getValue('advertisement_title_' . $language['id_lang'])) {
                        $objDonationInfo->advertisement_title[$language['id_lang']] = trim(Tools::getValue(
                            'advertisement_title_' . $language['id_lang']
                        ));
                    } else {
                        $objDonationInfo->advertisement_title[$language['id_lang']] =
                        trim(Tools::getValue('advertisement_title_' . $defaultLangId));
                    }
                    if (Tools::getValue('advertisement_description_' . $language['id_lang'])) {
                        $objDonationInfo->advertisement_description[$language['id_lang']] = trim(Tools::getValue(
                            'advertisement_description_' . $language['id_lang']
                        ));
                    } else {
                        $objDonationInfo->advertisement_description[$language['id_lang']] =
                        trim(Tools::getValue('advertisement_description_' . $defaultLangId));
                    }
                    if (Tools::getValue('donate_button_text_' . $language['id_lang'])) {
                        $objDonationInfo->donate_button_text[$language['id_lang']] =
                        trim(Tools::getValue('donate_button_text_' . $language['id_lang']));
                    } else {
                        $objDonationInfo->donate_button_text[$language['id_lang']] =
                        trim(Tools::getValue('donate_button_text_' . $defaultLangId));
                    }
                }
            }
            if ($objDonationInfo->save()) {
                $idDonationInfo = $objDonationInfo->id;
                // add or update donation product to ps
                $idProduct = $objDonationInfo->addDonationProductToPs($idDonationInfo);
                if ($idProduct) {
                    $objDonationInfo->id_product = $idProduct;
                    $objDonationInfo->save();
                }
                if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                    $shops = Shop::getShops(false);
                    if ($shops) {
                        foreach ($shops as $shop) {
                            if ($advertise) {
                                ImageManager::resize(
                                    $_FILES['background_image_head_foot']['tmp_name'],
                                    _PS_MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $idDonationInfo . '_' . $shop['id_shop'] . '-head-foot.jpg'
                                );
                                ImageManager::resize(
                                    $_FILES['background_image_left_right']['tmp_name'],
                                    _PS_MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $idDonationInfo . '_' . $shop['id_shop'] . '-left-right.jpg'
                                );
                            }

                            // delete previous advertisement hooks
                            $objDonationDisplayPlace = new WkDonationDisplayPlaces();
                            $selectedHooks = $objDonationDisplayPlace->getDonationHooksByIdDonation($idDonationInfo, $shop['id_shop']);
                            $hookArray = array_column($selectedHooks, 'id_hook');
                            foreach ($hookArray as $hook) {
                                $objDonationDisplayPlace->deleteDonationHooks($idDonationInfo, $hook, $shop['id_shop']);
                            }
                            // add new advertisement hooks
                            if ($advertise) {
                                foreach ($donationPageHook as $idPage => $pageHooks) {
                                    foreach ($pageHooks as $idHook) {
                                        $objDonationDisplayPlace->insertDonationHooks($idDonationInfo, $idHook, $idPage, $shop['id_shop']);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($advertise) {
                        ImageManager::resize(
                            $_FILES['background_image_head_foot']['tmp_name'],
                            _PS_MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $idDonationInfo . '_' . $idShop . '-head-foot.jpg'
                        );
                        ImageManager::resize(
                            $_FILES['background_image_left_right']['tmp_name'],
                            _PS_MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $idDonationInfo . '_' . $idShop . '-left-right.jpg'
                        );
                    }
                    // delete previous advertisement hooks
                    $objDonationDisplayPlace = new WkDonationDisplayPlaces();
                    $selectedHooks = $objDonationDisplayPlace->getDonationHooksByIdDonation($idDonationInfo, $idShop);
                    $hookArray = array_column($selectedHooks, 'id_hook');
                    foreach ($hookArray as $hook) {
                        $objDonationDisplayPlace->deleteDonationHooks($idDonationInfo, $hook, $idShop);
                    }
                    // add new advertisement hooks
                    if ($advertise) {
                        foreach ($donationPageHook as $idPage => $pageHooks) {
                            foreach ($pageHooks as $idHook) {
                                $objDonationDisplayPlace->insertDonationHooks($idDonationInfo, $idHook, $idPage, $idShop);
                            }
                        }
                    }
                }
                if (Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
                    if ($id) {
                        Tools::redirectAdmin(
                            self::$currentIndex .
                            '&id_donation_info=' . (int) $idDonationInfo .
                            '&update' . $this->table .
                            '&conf=4&tab=' . Tools::getValue('active_tab') .
                            '&token=' . $this->token
                        );
                    } else {
                        Tools::redirectAdmin(
                            self::$currentIndex .
                            '&id_donation_info=' . (int) $idDonationInfo .
                            '&update' . $this->table .
                            '&conf=3&tab=' . Tools::getValue('active_tab') .
                            '&token=' . $this->token
                        );
                    }
                } else {
                    if ($id) {
                        Tools::redirectAdmin(self::$currentIndex . '&conf=4&token=' . $this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex . '&conf=3&token=' . $this->token);
                    }
                }
            } else {
                $this->errors[] = $this->l('Something went wrong. Please try again later !!');
            }
        } else {
            $this->display = 'edit';
        }
    }

    protected function processBulkEnableSelection()
    {
        return $this->processBulkStatusSelection(1);
    }

    protected function processBulkDisableSelection()
    {
        return $this->processBulkStatusSelection(0);
    }

    protected function processBulkStatusSelection($status)
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            $products = Tools::getValue($this->table . 'Box');
            if (is_array($products) && ($count = count($products))) {
                foreach ($products as $id_product) {
                    $product = new WkDonationInfo((int) $id_product);
                    $product->active = $status;
                    if ($product->update()) {
                        $objDonationProduct = new Product($product->id_product);
                        $objDonationProduct->active = $status;
                        if ($objDonationProduct->save()) {
                        }
                    }
                }
                Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token . '&conf=5');
            } else {
                $this->errors[] = Tools::displayError('You must select at least one element to enable/disable.');
            }
        }
    }

    public function processDelete()
    {
        if (Validate::isLoadedObject($objDonationInfo = $this->loadObject())) {
            $imagePath = _PS_MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $objDonationInfo->id;
            if (file_exists($imagePath . '-head-foot.jpg')) {
                unlink($imagePath . '-head-foot.jpg');
            }
            if (file_exists($imagePath . '-left-right.jpg')) {
                unlink($imagePath . '-left-right.jpg');
            }
        }
        parent::processDelete();
    }

    public function getAdvertisementBannerImagePath($imageName)
    {
        $path = _MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $imageName . '.jpg';
        if (file_exists(_PS_MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $imageName . '.jpg')) {
            return $path;
        }

        return false;
    }

    public function ajaxProcessUploadDonationProductImages()
    {
        if ($idDonationInfo = Tools::getValue('id_donation_info')) {
            if (Validate::isLoadedObject($objDonationInfo = new WkDonationInfo($idDonationInfo))) {
                if (!$invalidImg = ImageManager::validateUpload(
                    $_FILES['donation_image'],
                    Tools::getMaxUploadSize()
                )) {
                    $kwargs = [
                        'id_product' => $objDonationInfo->id_product,
                        'donation_image' => $_FILES['donation_image'],
                    ];
                    $imageDetail = $objDonationInfo->uploadDonationProductImages($kwargs);
                    if ($imageDetail) {
                        $this->ajaxDie(json_encode($imageDetail));
                    } else {
                        $this->ajaxDie(json_encode(['hasError' => true]));
                    }
                } else {
                    $this->ajaxDie(
                        json_encode(
                            ['hasError' => true, 'message' => $_FILES['donation_image']['name'] . ': ' . $invalidImg]
                        )
                    );
                }
            }
        }
    }

    public function ajaxProcessChangeDonationCoverImage()
    {
        if ($idDonationInfo = Tools::getValue('id_donation_info')) {
            if (Validate::isLoadedObject($objDonationInfo = new WkDonationInfo($idDonationInfo))) {
                if ($idImage = Tools::getValue('id_image')) {
                    Image::deleteCover((int) $objDonationInfo->id_product);
                    $image = new Image((int) $idImage);
                    $image->cover = 1;

                    // unlink existing cover image in temp folder
                    @unlink(_PS_TMP_IMG_DIR_ . 'product_' . (int) $image->id_product);
                    @unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . (int) $image->id_product . '_' . $this->context->shop->id);

                    if ($image->update()) {
                        $this->ajaxDie(true);
                    } else {
                        $this->ajaxDie(false);
                    }
                } else {
                    $this->ajaxDie(false);
                }
            } else {
                $this->ajaxDie(false);
            }
        }
    }

    public function ajaxProcessDeleteDonationImage()
    {
        if ($idDonationInfo = Tools::getValue('id_donation_info')) {
            if (Validate::isLoadedObject($objDonationInfo = new WkDonationInfo($idDonationInfo))) {
                if ($idImage = Tools::getValue('id_image')) {
                    $image = new Image((int) $idImage);
                    if ($image->delete()) {
                        Product::cleanPositions($idImage);
                        if (!Image::getCover($image->id_product)) {
                            $images = Image::getImages($this->context->language->id, $objDonationInfo->id_product);
                            if ($images) {
                                $objImage = new Image($images[0]['id_image']);
                                $objImage->cover = 1;
                                $objImage->save();
                            }
                        }

                        if (file_exists(_PS_TMP_IMG_DIR_ . 'product_' . $image->id_product . '.jpg')) {
                            @unlink(_PS_TMP_IMG_DIR_ . 'product_' . $image->id_product . '.jpg');
                        }
                        if (file_exists(
                            _PS_TMP_IMG_DIR_ . 'product_mini_' . $image->id_product . '_' . $this->context->shop->id . '.jpg'
                        )) {
                            @unlink(
                                _PS_TMP_IMG_DIR_ . 'product_mini_' . $image->id_product . '_' . $this->context->shop->id . '.jpg'
                            );
                        }
                        if (isset($objImage)) {
                            $this->ajaxDie(json_encode(['idCover' => $objImage->id_image]));
                        }
                        $this->ajaxDie(json_encode(['hasError' => false]));
                    } else {
                        $this->ajaxDie(json_encode(['hasError' => true]));
                    }
                } else {
                    $this->ajaxDie(json_encode(['hasError' => true]));
                }
            } else {
                $this->ajaxDie(json_encode(['hasError' => true]));
            }
        }
    }

    public function ajaxProcessUpdatePositions()
    {
        $way = (int) Tools::getValue('way');
        $idDonationInfo = (int) Tools::getValue('id');
        $positions = Tools::getValue('donation_info');

        foreach ($positions as $position => $value) {
            $pos = explode('_', $value);

            if (isset($pos[2]) && (int) $pos[2] === $idDonationInfo) {
                if ($objDonationInfo = new WkDonationInfo((int) $pos[2])) {
                    if (isset($position)
                        && $objDonationInfo->updatePosition($way, $position)
                    ) {
                        $this->ajaxDie(true);
                    } else {
                        $this->ajaxDie(false);
                    }
                } else {
                    $this->ajaxDie(false);
                }
                break;
            }
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setmedia($isNewTheme);
        $this->addJqueryPlugin('colorpicker');
        $jsVars = [
            'ps_img_lang_dir' => _PS_IMG_ . 'l/',
            'maxSizeAllowed' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
            'adminManageDonationUrl' => $this->context->link->getAdminLink('AdminManageDonation'),
            'filesizeError' => $this->l('File exceeds maximum size'),
            'imgUploadSuccessMsg' => $this->l('Image Successfully Uploaded'),
            'coverImgSuccessMsg' => $this->l('Cover image changed successfully'),
            'coverImgErrorMsg' => $this->l('Error while changing cover image'),
            'deleteImgSuccessMsg' => $this->l('Image deleted successfully'),
            'deleteImgErrorMsg' => $this->l('Something went wrong while deleteing image. Please try again.'),
            'imgUploadErrorMsg' => $this->l('Something went wrong while uploading images. Please try again.'),
            'confirmDelete' => $this->l('Are you sure want to delete image.'),
        ];
        Media::addJsDef($jsVars);
        $this->addJS(_PS_JS_DIR_ . 'tiny_mce/tiny_mce.js');
        if (version_compare(_PS_VERSION_, '1.6.0.11', '>')) {
            $this->addJS(_PS_JS_DIR_ . 'admin/tinymce.inc.js');
        } else {
            $this->addJS(_PS_JS_DIR_ . 'tinymce.inc.js');
        }

        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/admin/wk_manage_donation.css');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/admin/wk_manage_donation.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/admin/wk_donation_images.js');
        $this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/admin/wk_donation_manage_submit.js');
    }

    public function ajaxProcessDeleteImage()
    {
        $file = Tools::getValue('file');
        $idShop = Context::getContext()->shop->id;
        $idCount = strlen(explode('-', $file)[0]);
        $newFileName = substr_replace($file, '_' . $idShop, $idCount, 0);
        $filePath = _PS_MODULE_DIR_ . $this->module->name . '/views/img/banner/' . $newFileName . '.jpg';
        if (file_exists($filePath)) {
            if (@unlink($filePath)) {
                echo json_encode([
                    'response' => true,
                    'title' => $this->l('Success'),
                    'msg' => $this->l('Image deleted successfully.'),
                ]);
                exit;
            }
        }
        echo json_encode([
            'response' => false,
            'title' => $this->l('Error'),
            'msg' => $this->l('Image deletion failed.'),
        ]);
        exit;
    }
}
