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

class WkDonationInfo extends ObjectModel
{
    public const WK_DONATION_PRICE_TYPE_FIXED = 1;
    public const WK_DONATION_PRICE_TYPE_CUSTOMER = 2;

    public $id_donation_info;
    public $id_product;
    public $price_type;
    public $price;
    public $product_visibility;
    public $show_at_checkout;
    public $advertise;
    public $expiry_date;
    public $show_donate_button;
    public $adv_title_color;
    public $adv_desc_color;
    public $button_text_color;
    public $button_border_color;
    public $position;
    public $is_global;
    public $active;
    public $date_add;
    public $date_upd;
    /* multilang fields */
    public $name;
    public $description;
    public $advertisement_title;
    public $advertisement_description;
    public $donate_button_text;

    public static $definition = [
        'table' => 'wk_donation_info',
        'primary' => 'id_donation_info',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            'id_product' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'price_type' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'price' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
            'product_visibility' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'show_at_checkout' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'advertise' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'expiry_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
            'show_donate_button' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'adv_title_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor'],
            'adv_desc_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor'],
            'button_text_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor'],
            'button_border_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor'],
            'is_global' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'position' => ['type' => self::TYPE_STRING, 'validate' => 'isInt'],
            'active' => ['type' => self::TYPE_INT, 'validate' => 'isbool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false],
            /* Lang fields */
            'name' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'required' => true,
                'size' => 128,
            ],
            'description' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
                'required' => true,
            ],
            'advertisement_title' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isCatalogName',
                'size' => 128,
            ],
            'advertisement_description' => [
                'type' => self::TYPE_HTML,
                'lang' => true,
                'validate' => 'isCleanHtml',
            ],
            'donate_button_text' => [
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isGenericName',
                'required' => false,
                'size' => 128,
            ],
        ],
    ];

    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('wk_donation_info', ['type' => 'shop', 'primary' => 'id_donation_info']);
        Shop::addTableAssociation('wk_donation_info_lang', ['type' => 'fk_shop']);
    }

    public function delete()
    {
        if (!parent::delete()
            || $this->deletehooks($this->id_donation_info)
            || $this->deleteDonationFromPs($this->id_product)
        ) {
            return false;
        }
        /* Reinitializing position */
        WkDonationInfo::cleanPositions();

        return true;
    }

    public function deletehooks($id_donation_info)
    {
        !Db::getInstance()->delete('wk_donation_display_places', 'id_donation_info = ' . (int) $id_donation_info);
    }

    public function deleteDonationFromPs($id_product)
    {
        $objProduct = new Product($id_product);
        $objProduct->delete();
    }

    public function addDonationProductToPs($idDonationInfo)
    {
        $objDonationInfo = new self($idDonationInfo);
        $donationInfo = (array) $objDonationInfo;
        $objProduct = new Product($donationInfo['id_product']);

        $objProduct->name = [];
        $objProduct->description = [];

        foreach (Language::getLanguages(false) as $lang) {
            $objProduct->name[$lang['id_lang']] = $donationInfo['name'][$lang['id_lang']];
            $objProduct->description[$lang['id_lang']] = $donationInfo['description'][$lang['id_lang']];
            $objProduct->link_rewrite[$lang['id_lang']] = Tools::link_rewrite($donationInfo['name'][$lang['id_lang']]);
        }
        $objProduct->active = $donationInfo['active'];
        $objProduct->price = $donationInfo['price'];

        if ($donationInfo['product_visibility'] == 1) {
            $objProduct->visibility = 'both';
        } else {
            $objProduct->visibility = 'none';
        }

        $objProduct->id_category_default = Configuration::get('WK_DONATION_ID_CATEGORY');
        $objProduct->id_shop = Configuration::get('PS_SHOP_DEFAULT');
        $objProduct->id_tax_rules_group = 0;
        $objProduct->is_virtual = 1;
        $objProduct->indexed = 1;
        $objProduct->redirect_type = '302-category';
        if ($objProduct->save()) {
            if ($donationInfo['id_product']) {
                StockAvailable::setQuantity($objProduct->id, null, 999999);
            } else {
                StockAvailable::updateQuantity($objProduct->id, null, 999999);
            }
        }
        $idDoantionProduct = $objProduct->id;
        foreach (Language::getLanguages(false) as $lang) {
            Search::indexation($donationInfo['name'][$lang['id_lang']], $idDoantionProduct);
        }
        $objProduct->updateCategories((array) Configuration::get('WK_DONATION_ID_CATEGORY'));

        return $idDoantionProduct;
    }

    public function createCategory()
    {
        $groupId = [
            Configuration::get('PS_UNIDENTIFIED_GROUP'),
            Configuration::get('PS_GUEST_GROUP'),
            Configuration::get('PS_CUSTOMER_GROUP'),
        ];
        $objCategory = new Category();

        foreach (Language::getLanguages(true) as $lang) {
            $objCategory->name[$lang['id_lang']] = 'Donations';
            $objCategory->description[$lang['id_lang']] = 'Charity donation category';
            $objCategory->link_rewrite[$lang['id_lang']] = Tools::link_rewrite('Donations');
        }
        $objCategory->id_parent = Configuration::get('PS_HOME_CATEGORY');
        $objCategory->groupBox = $groupId;
        $objCategory->save();
        Configuration::updateValue('WK_DONATION_ID_CATEGORY', $objCategory->id);

        return true;
    }

    public function disableProducts()
    {
        $donationProducts = $this->getAllDonationProducts();
        if ($donationProducts) {
            foreach ($donationProducts as $donationProduct) {
                $objProduct = new Product(
                    $donationProduct['id_product'],
                    false,
                    null,
                    Configuration::get('PS_SHOP_DEFAULT')
                );
                $objProduct->active = 0;
                $objProduct->price = 0;
                $objProduct->save();
            }
        }

        return true;
    }

    public function deleteAdvertisementImages()
    {
        $idDonationsInfo = $this->getAllIdDonation();
        $idShop = Context::getContext()->shop->id;
        foreach ($idDonationsInfo as $idDonationInfo) {
            $imagePath = _PS_MODULE_DIR_ . 'wkcharitydonation/views/img/banner/' . $idDonationInfo['id_donation_info'];
            if (file_exists($imagePath . '-head-foot.jpg')) {
                unlink($imagePath . $idShop . '-head-foot.jpg');
            }
            if (file_exists($imagePath . '-left-right.jpg')) {
                unlink($imagePath . $idShop . '-left-right.jpg');
            }
        }

        return true;
    }

    public static function cleanPositions()
    {
        Db::getInstance()->execute('SET @i = -1', false);

        return (bool) Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'wk_donation_info` SET `position` = @i:=@i+1
            WHERE `is_global` = 0 ORDER BY `position` ASC'
        );
    }

    public function updatePosition($way, $position)
    {
        if (!$res = Db::getInstance()->executeS(
            'SELECT wkd.`id_donation_info`, wkd.`position` FROM `' . _DB_PREFIX_ .
            'wk_donation_info` wkd WHERE wkd.`id_donation_info` = ' .
            (int) $this->id . ' AND wkd.`is_global` = 0 ORDER BY `position` ASC'
        )
        ) {
            return false;
        }

        $moved_donation_info = false;
        foreach ($res as $donation_info) {
            if ((int) $donation_info['id_donation_info'] == (int) $this->id) {
                $moved_donation_info = $donation_info;
            }
        }

        if ($moved_donation_info === false) {
            return false;
        }

        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'wk_donation_info` SET `position`= `position` ' . ((int) $way ? '- 1' : '+ 1') .
            ' WHERE `position`' . ((int) $way ? '> ' .
            (int) $moved_donation_info['position'] . ' AND `position` <= ' . (int) $position : '< '
            . (int) $moved_donation_info['position'] . ' AND `position` >= ' . (int) $position) . ' AND `is_global` = 0'
        ) && Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'wk_donation_info`
            SET `position` = ' . (int) $position . '
            WHERE `id_donation_info`=' . (int) $moved_donation_info['id_donation_info'] . ' AND `is_global` = 0'
        );
    }

    public static function getHigherPosition()
    {
        $position = Db::getInstance()->getValue(
            'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'wk_donation_info` WHERE `is_global` = 0'
        );
        $result = (is_numeric($position)) ? (int) $position : -1;

        return $result + 1;
    }

    public function isDonationProduct($idProduct)
    {
        return Db::getInstance()->getValue(
            'SELECT `id_donation_info` FROM `' . _DB_PREFIX_ . 'wk_donation_info` di
            WHERE di.`id_product` = ' . (int) $idProduct
        );
    }

    public function getPriceInfo($idProduct)
    {
        return Db::getInstance()->getRow(
            'SELECT `id_donation_info`, `price_type`, `price` FROM `' . _DB_PREFIX_ . 'wk_donation_info`
            where id_product = ' . (int) $idProduct
        );
    }

    public function getDonationsByHook($idHook, $idPage, $idLang)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'wk_donation_info` di
            JOIN `' . _DB_PREFIX_ . 'wk_donation_display_places` dp
            ON di.`id_donation_info` = dp.`id_donation_info`
            JOIN `' . _DB_PREFIX_ . 'wk_donation_info_lang` dl
            ON dl.`id_donation_info` = di.`id_donation_info`
            WHERE dp.`id_page` = ' . (int) $idPage . ' AND dp.`id_hook` = ' . (int) $idHook . '
            AND dl.`id_lang` = ' . (int) $idLang . ' AND di.`advertise` = 1 AND di.`active` = 1
            AND dp.`id_shop` = ' . Context::getContext()->shop->id . ' ORDER BY di.`position` ASC'
        );
    }

    public function getCheckoutDonations($idShop = false)
    {
        return Db::getInstance()->executeS(
            'SELECT `id_donation_info` FROM `' . _DB_PREFIX_ . 'wk_donation_info` di
            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa
            ON di.`id_product` = sa.`id_product`
            WHERE di.`show_at_checkout` = 1 AND di.`active` = 1  AND sa.`id_shop` = ' . (int) $idShop . '
            AND (sa.`quantity` > 0 OR sa.`out_of_stock` = 1)
            ORDER BY di.`position` ASC'
        );
    }

    public static function getIdGlobalDonation()
    {
        $sql = 'SELECT di.`id_donation_info` FROM `' . _DB_PREFIX_ . 'wk_donation_info` di ' .
        WkDonationInfo::addSqlAssociationCustom('wk_donation_info', 'di') . ' WHERE di.`is_global` = 1';

        return Db::getInstance()->getValue($sql);
    }

    public function getAllIdDonation()
    {
        return Db::getInstance()->executeS('SELECT `id_donation_info` FROM `' . _DB_PREFIX_ . 'wk_donation_info` di');
    }

    public function getAllDonationProducts()
    {
        return Db::getInstance()->executeS(
            'SELECT `id_product` FROM `' . _DB_PREFIX_ . 'wk_donation_info` di
            WHERE di.`is_global` = 0'
        );
    }

    public function setSpecificPrice($idProduct, $price)
    {
        if (!$idCustomer = Context::getContext()->customer->id) {
            $idCustomer = 0;
        }
        $idCart = Context::getContext()->cart->id;
        $price = Tools::ps_round($price, 6);
        if ($idSpecificPrice = $this->checkExistingSpecificPrice($idProduct, $idCustomer, $idCart)) {
            $objSpecificPrice = new SpecificPrice($idSpecificPrice);
            $price += $objSpecificPrice->price;
        } else {
            $objSpecificPrice = new SpecificPrice();
        }

        $objSpecificPrice->id_shop = 0;
        $objSpecificPrice->id_cart = $idCart;
        $objSpecificPrice->id_product = $idProduct;
        $objSpecificPrice->id_customer = $idCustomer;
        $objSpecificPrice->id_currency = 0;
        $objSpecificPrice->price = $price;
        $objSpecificPrice->id_country = 0;
        $objSpecificPrice->id_group = 0;
        $objSpecificPrice->from_quantity = 1;
        $objSpecificPrice->reduction = 0;
        $objSpecificPrice->reduction_type = 0;
        $objSpecificPrice->from = '0000-00-00 00:00:00';
        $objSpecificPrice->to = '0000-00-00 00:00:00';

        $objSpecificPrice->save();

        return $objSpecificPrice->id;
    }

    public function checkExistingSpecificPrice($idProduct, $idCustomer, $idCart)
    {
        return Db::getInstance()->getValue(
            'SELECT `id_specific_price` FROM `' . _DB_PREFIX_ . 'specific_price` sp WHERE sp.id_product = '
            . (int) $idProduct . ' AND sp.id_customer = ' . (int) $idCustomer . ' AND sp.id_cart = ' . (int) $idCart
        );
    }

    public function uploadDonationProductImages($imageDetail)
    {
        $context = Context::getContext();
        if ($idProduct = $imageDetail['id_product']) {
            $objProduct = new Product($idProduct, false, $context->language->id);
            $maxImgPosition = Image::getHighestPosition($idProduct);
            $coverImgExist = Image::getCover($idProduct);
            $imagesTypes = ImageType::getImagesTypes('products');
            $donationProductImage = $imageDetail['donation_image'];

            $objImage = new Image();
            $objImage->id_product = $idProduct;
            ++$maxImgPosition;
            $objImage->position = $maxImgPosition;

            if (!$coverImgExist) {
                $coverImgExist = 1;
                $objImage->cover = 1;
            } else {
                $objImage->cover = 0;
            }
            $objImage->add();

            $imageId = $objImage->id;
            $newPath = $objImage->getPathForCreation();

            if ($imagesTypes) {
                foreach ($imagesTypes as $imageType) {
                    ImageManager::resize(
                        $donationProductImage['tmp_name'],
                        $newPath . '-' . Tools::stripslashes($imageType['name']) . '.' . $objImage->image_format,
                        $imageType['width'],
                        $imageType['height'],
                        $objImage->image_format
                    );
                }
            }
            ImageManager::resize($donationProductImage['tmp_name'], $newPath . '.' . $objImage->image_format);

            // Associate image to shop from context
            $shops = Shop::getContextListShopID();
            $objImage->associateTo($shops);

            $addedImage = [
                'id_image' => $imageId,
                'is_cover' => $objImage->cover ? 1 : 0,
                'position' => $objImage->position,
                'image_url' => $context->link->getImageLink($objProduct->link_rewrite, $idProduct . '-' . $imageId),
            ];

            return $addedImage;
        }

        return false;
    }

    public function updateDonationExpiry()
    {
        if ($idAllDonations = Db::getInstance()->executeS(
            'SELECT `id_donation_info` FROM `' . _DB_PREFIX_ . 'wk_donation_info` di WHERE di.`is_global` = 0'
        )) {
            $today = date('Y-m-d');
            foreach ($idAllDonations as $idDonationInfo) {
                $objDonationInfo = new WkDonationInfo((int) $idDonationInfo['id_donation_info']);
                if (Validate::isLoadedObject($objDonationInfo)) {
                    if (Validate::isDate($objDonationInfo->expiry_date)) {
                        $expiry_date = date('Y-m-d', strtotime($objDonationInfo->expiry_date));
                        if ($expiry_date > 0) {
                            if ($today > $expiry_date) {
                                $objDonationInfo->active = 0;
                                if ($objDonationInfo->save()) {
                                    $objDonationInfo->addDonationProductToPs((int) $idDonationInfo['id_donation_info']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function getTotalDonationCount($idDonationInfo = null)
    {
        if ($idDonationInfo) {
            $sql = 'SELECT COUNT(DISTINCT ds.`id_order`) FROM `' . _DB_PREFIX_ . 'wk_donation_stats` ds
            WHERE `id_donation_info` =' . (int) $idDonationInfo;
            if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL) {
                $sql .= ' AND ds.id_shop = ' . (int) Context::getContext()->shop->id;
            }

            return Db::getInstance()->getValue($sql);
        } else {
            $sql = 'SELECT COUNT(DISTINCT ds.`id_order`) FROM `' . _DB_PREFIX_ . 'wk_donation_stats` ds';
            if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL) {
                $sql .= ' WHERE ds.id_shop = ' . (int) Context::getContext()->shop->id;
            }

            return Db::getInstance()->getValue($sql);
        }
    }

    public function getTotalCustomerCount($idDonationInfo = null)
    {
        if ($idDonationInfo) {
            $sql = 'SELECT COUNT(DISTINCT ds.`id_customer`) FROM `' . _DB_PREFIX_ . 'wk_donation_stats` ds
            WHERE `id_donation_info` =' . (int) $idDonationInfo;
            if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL) {
                $sql .= ' AND ds.id_shop = ' . (int) Context::getContext()->shop->id;
            }

            return Db::getInstance()->getValue($sql);
        } else {
            $sql = 'SELECT COUNT(DISTINCT ds.`id_customer`) FROM `' . _DB_PREFIX_ . 'wk_donation_stats` ds';
            if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL) {
                $sql .= ' WHERE ds.id_shop = ' . (int) Context::getContext()->shop->id;
            }

            return Db::getInstance()->getValue($sql);
        }
    }

    public function getTotalDonationAmount($idDonationInfo = null)
    {
        if ($idDonationInfo) {
            $sql = 'SELECT od.`id_currency` , od.`total_paid_tax_incl` , ord.`product_id` , ord.`total_price_tax_incl`
            FROM `' . _DB_PREFIX_ . 'wk_donation_stats` ds
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` od ON od.`id_order` = ds.`id_order`
            LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` ord
            ON CONCAT(ord.`id_order`, ord.`product_id`) = CONCAT(ds.`id_order`, ds.`id_product`)
            WHERE `id_donation_info` =' . (int) $idDonationInfo;
            if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL) {
                $sql .= ' AND ds.id_shop = ' . (int) Context::getContext()->shop->id;
            }
            $total = Db::getInstance()->executeS($sql);
        } else {
            $sql = 'SELECT od.`id_currency` , od.`total_paid_tax_incl`, ord.`product_id` , ord.`total_price_tax_incl`
            FROM `' . _DB_PREFIX_ . 'wk_donation_stats` ds
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` od ON od.`id_order` = ds.`id_order`
            LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` ord
            ON CONCAT(ord.`id_order`, ord.`product_id`) = CONCAT(ds.`id_order`, ds.`id_product`)';
            if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL) {
                $sql .= ' WHERE ds.id_shop = ' . (int) Context::getContext()->shop->id;
            }
            $total = Db::getInstance()->executeS($sql);
        }
        if ($total) {
            $totalAmount = 0;
            foreach ($total as $amount) {
                if ($productCategory = Product::getProductCategories($amount['product_id'])) {
                    if ($productCategory[0] == Configuration::get('WK_DONATION_ID_CATEGORY')) {
                        $objCurrency = new Currency((int) $amount['id_currency']);
                        $totalAmount += Tools::convertPriceFull($amount['total_price_tax_incl'], $objCurrency);
                    }
                }
            }

            return (float) $totalAmount;
        }
    }

    public function getDonationNameFromStats($idDonationInfo)
    {
        return Db::getInstance()->getValue(
            'SELECT `name` FROM `' . _DB_PREFIX_ . 'wk_donation_stats` ds
            WHERE `id_donation_info` =' . (int) $idDonationInfo . '
            ORDER BY `id_order` DESC'
        );
    }

    public static function addSqlAssociationCustom(
        $table,
        $alias,
        $inner_join = true,
        $on = null,
        $force_not_default = false,
        $identifier = 'id_donation_info'
    ) {
        $table_alias = $table . '_shop';
        if (strpos($table, '.') !== false) {
            list($table_alias, $table) = explode('.', $table);
        }

        $asso_table = Shop::getAssoTable($table);
        if ($asso_table === false || $asso_table['type'] != 'shop') {
            return;
        }
        $sql = (($inner_join) ? ' INNER' : ' LEFT') . ' JOIN ' . _DB_PREFIX_ . $table . '_shop ' . $table_alias . '
        ON (' . $table_alias . '.' . $identifier . ' = ' . $alias . '.' . $identifier;
        if ((int) Shop::getContextShopID()) {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . (int) Shop::getContextShopID();
        } elseif (Shop::checkIdShopDefault($table) && !$force_not_default) {
            $sql .= ' AND ' . $table_alias . '.id_shop = ' . $alias . '.id_shop_default';
        } else {
            $sql .= ' AND ' . $table_alias . '.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')';
        }
        $sql .= (($on) ? ' AND ' . $on : '') . ')';

        return $sql;
    }

    public static function displayPrice($price, $currency = null)
    {
        if (!is_numeric($price)) {
            return $price;
        }

        $context = Context::getContext();
        $currency = $currency ?: $context->currency;

        if (is_int($currency)) {
            $currency = Currency::getCurrencyInstance($currency);
        }

        $locale = Tools::getContextLocale($context);
        $currencyCode = is_array($currency) ? $currency['iso_code'] : $currency->iso_code;

        return $locale->formatPrice($price, $currencyCode);
    }
}
