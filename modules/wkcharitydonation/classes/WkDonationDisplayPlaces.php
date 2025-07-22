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

class WkDonationDisplayPlaces extends ObjectModel
{
    public const WK_DONATION_HOOK_HOME = 1;
    public const WK_DONATION_HOOK_FOOTER = 3;
    public const WK_DONATION_HOOK_LEFT = 4;
    public const WK_DONATION_HOOK_RIGHT = 5;

    public const WK_DONATION_PRODUCT_PAGE = 1;
    public const WK_DONATION_HOME_PAGE = 2;
    public const WK_DONATION_CATEGORY_PAGE = 3;
    public const WK_DONATION_CART_PAGE = 4;

    public $donationDisplayPages = [];

    public function __construct()
    {
        $this->donationDisplayPages = [
            'product' => self::WK_DONATION_PRODUCT_PAGE,
            'index' => self::WK_DONATION_HOME_PAGE,
            'category' => self::WK_DONATION_CATEGORY_PAGE,
            'cart' => self::WK_DONATION_CART_PAGE,
        ];
    }

    public static function getDonationPages()
    {
        $moduleInstance = new WkCharityDonation();

        $pages = [
            'homepage' => [
                'id_page' => WkDonationDisplayPlaces::WK_DONATION_HOME_PAGE,
                'name' => $moduleInstance->l('Home page', 'WkDonationDisplayPlaces'),
            ],
            'product' => [
                'id_page' => WkDonationDisplayPlaces::WK_DONATION_PRODUCT_PAGE,
                'name' => $moduleInstance->l('Product page', 'WkDonationDisplayPlaces'),
            ],
            'category_page' => [
                'id_page' => WkDonationDisplayPlaces::WK_DONATION_CATEGORY_PAGE,
                'name' => $moduleInstance->l('Category page', 'WkDonationDisplayPlaces'),
            ],
            'cart_page' => [
                'id_page' => WkDonationDisplayPlaces::WK_DONATION_CART_PAGE,
                'name' => $moduleInstance->l('Cart page', 'WkDonationDisplayPlaces'),
            ],
        ];

        return $pages;
    }

    public static function getDonationHooks()
    {
        $moduleInstance = new WkCharityDonation();
        $hooks = [
            'home_hook' => [
                'id_hook' => WkDonationDisplayPlaces::WK_DONATION_HOOK_HOME,
                'name' => $moduleInstance->l('Header', 'WkDonationDisplayPlaces'),
            ],
            'footer_hook' => [
                'id_hook' => WkDonationDisplayPlaces::WK_DONATION_HOOK_FOOTER,
                'name' => $moduleInstance->l('Footer', 'WkDonationDisplayPlaces'),
            ],
            'left_hook' => [
                'id_hook' => WkDonationDisplayPlaces::WK_DONATION_HOOK_LEFT,
                'name' => $moduleInstance->l('Left', 'WkDonationDisplayPlaces'),
            ],
            'right_hook' => [
                'id_hook' => WkDonationDisplayPlaces::WK_DONATION_HOOK_RIGHT,
                'name' => $moduleInstance->l('Right', 'WkDonationDisplayPlaces'),
            ],
        ];

        return $hooks;
    }

    public function getDonationPagesByIdDonation($donationInfoId)
    {
        return Db::getInstance()->executeS(
            'SELECT DISTINCT `id_page` FROM `' . _DB_PREFIX_ . 'wk_donation_display_places`
            WHERE `id_donation_info` = ' . (int) $donationInfoId
        );
    }

    public function getDonationHooksByIdPage($donationInfoId, $idPage, $idShop = false)
    {
        $context = Context::getContext();
        $result = Db::getInstance()->executeS(
            'SELECT `id_hook` FROM `' . _DB_PREFIX_ . 'wk_donation_display_places` WHERE `id_page` = ' .
            (int) $idPage . ' AND `id_donation_info` = ' . (int) $donationInfoId . ' AND `id_shop` = ' . (int) $idShop
        );

        return array_column($result, 'id_hook');
    }

    public function getDonationHooksByIdDonation($idDonationInfo, $idShop = false)
    {
        return Db::getInstance()->executeS(
            'SELECT `id_hook` FROM `' . _DB_PREFIX_ . 'wk_donation_display_places`
            WHERE `id_donation_info` = ' . (int) $idDonationInfo . ' AND `id_shop` = ' . (int) $idShop
        );
    }

    public function deleteDonationHooks($idDonationInfo, $idHook, $idShop = false)
    {
        return Db::getInstance()->delete(
            'wk_donation_display_places',
            'id_donation_info = ' . (int) $idDonationInfo . ' AND `id_hook` = ' . (int) $idHook . ' AND `id_shop` = ' . (int) $idShop
        );
    }

    public function insertDonationHooks($idDonationInfo, $idHook, $idPage, $idShop = false)
    {
        return Db::getInstance()->insert(
            'wk_donation_display_places',
            [
                'id_donation_info' => (int) $idDonationInfo,
                'id_page' => (int) $idPage,
                'id_hook' => (int) $idHook,
                'id_shop' => (int) $idShop,
                'date_add' => pSQL(date('Y-m-d')),
            ]
        );
    }

    public function displayDonationsAdvertisement($hookId)
    {
        $currentPage = Tools::getValue('controller');
        if (isset($this->donationDisplayPages[$currentPage])) {
            $currentPageId = $this->donationDisplayPages[$currentPage];
            $context = Context::getContext();
            $objDonationInfo = new WkDonationInfo();
            $advertisementDonationInfo = $objDonationInfo->getDonationsByHook(
                $hookId,
                $currentPageId,
                Context::getContext()->language->id,
                Context::getContext()->shop->id
            );
            if ($advertisementDonationInfo) {
                $idShop = Context::getContext()->shop->id;
                foreach ($advertisementDonationInfo as &$donationInfo) {
                    if ($donationInfo['product_visibility']) {
                        if (!$donationInfo['is_global']) {
                            $donationInfo['button_link'] = $context->link->getProductLink(
                                $donationInfo['id_product']
                            );
                        } else {
                            $donationInfo['button_link'] = $context->link->getCategoryLink(
                                Configuration::get('WK_DONATION_ID_CATEGORY')
                            );
                        }
                    }
                    if ($hookId == self::WK_DONATION_HOOK_FOOTER
                        || $hookId == self::WK_DONATION_HOOK_HOME
                    ) {
                        $donationInfo['image_path'] = _MODULE_DIR_ . 'wkcharitydonation/views/img/banner/' .
                        $donationInfo['id_donation_info'] . '_' . $idShop . '-head-foot.jpg';
                    } elseif ($hookId == self::WK_DONATION_HOOK_LEFT
                        || $hookId == self::WK_DONATION_HOOK_RIGHT
                    ) {
                        $donationInfo['image_path'] = _MODULE_DIR_ . 'wkcharitydonation/views/img/banner/' .
                        $donationInfo['id_donation_info'] . '_' . $idShop . '-left-right.jpg';
                    }
                }

                return $advertisementDonationInfo;
            }
        }

        return false;
    }
}
