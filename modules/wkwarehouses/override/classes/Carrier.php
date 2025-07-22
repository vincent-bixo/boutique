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

class Carrier extends CarrierCore
{
    /**
     * Get available Carriers for Order.
     *
     * @param int $id_zone Zone ID
     * @param array $groups Group of the Customer
     * @param Cart|null $cart Optional Cart object
     * @param array &$error Contains an error message if an error occurs
     *
     * @return array Carriers for the order
     */
    public static function getCarriersForOrder($id_zone, $groups = null, $cart = null, &$error = [])
    {
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return parent::getCarriersForOrder($id_zone, $groups, $cart, $error);
        }
        $context = Context::getContext();
        $id_lang = $context->language->id;
        if (null === $cart) {
            $cart = $context->cart;
        }

		/*
		* Added by K.W
		*/
		// Description: to fix the total weight or total price (cause if cart is empty: returns wrong weight or price)
		$nbProducts = 0;
        if (Validate::isLoadedObject($cart)) {
			$nbProducts = $cart->nbProducts();
		}
		if (!class_exists('WarehouseStock')) {
			require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
		}
        if ($nbProducts && !WarehouseStock::getNumberOfAsmProductsInCart($cart->id)) {
            return parent::getCarriersForOrder($id_zone, $groups, $cart, $error);
        }
		// ------------------------------------------------------------------------------------------------------------------------

        if (isset($context->currency)) {
            $id_currency = $context->currency->id;
        }

        if (is_array($groups) && !empty($groups)) {
            $result = Carrier::getCarriers(
				$id_lang,
				true,
				false,
				(int)$id_zone,
				$groups,
				self::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
			);
        } else {
            $result = Carrier::getCarriers(
				$id_lang,
				true,
				false,
				(int)$id_zone,
				[Configuration::get('PS_UNIDENTIFIED_GROUP')],
				self::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
			);
        }
        $results_array = array();

        foreach ($result as $k => $row) {
            $carrier = new Carrier((int) $row['id_carrier']);
            $shipping_method = $carrier->getShippingMethod();
            if ($shipping_method != Carrier::SHIPPING_METHOD_FREE) {
                // Get only carriers that are compliant with shipping method
                if (($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)) {
                    $error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
                    unset($result[$k]);
                    continue;
                }
                if (($shipping_method == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice($id_zone) === false)) {
                    $error[$carrier->id] = Carrier::SHIPPING_PRICE_EXCEPTION;
                    unset($result[$k]);
                    continue;
                }

                // If out-of-range behavior carrier is set to "Deactivate carrier"
                if ($row['range_behavior']) {
                    // Get id zone
                    if (!$id_zone) {
                        $id_zone = (int)Country::getIdZone(Country::getDefaultCountryId());
                    }
                    // Get only carriers that have a range compatible with cart
                    /*if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT && 
						$nbProducts > 0 &&// Added by K.W
                        (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $cart->getTotalWeight(), $id_zone))) {
                        $error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
                        unset($result[$k]);

                        continue;
                    }
                    if ($shipping_method == Carrier::SHIPPING_METHOD_PRICE &&
						$nbProducts > 0 &&// Added by K.W
						(!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $id_currency))) {
                        $error[$carrier->id] = Carrier::SHIPPING_PRICE_EXCEPTION;
                        unset($result[$k]);

                        continue;
                    }*/
                }
            }

            $row['name'] = ((string)($row['name']) != '0' ? $row['name'] : Carrier::getCarrierNameFromShopName());
            $row['price'] = (($shipping_method == Carrier::SHIPPING_METHOD_FREE) ? 0 : $cart->getPackageShippingCost((int) $row['id_carrier'], true, null, null, $id_zone));
            $row['price_tax_exc'] = (($shipping_method == Carrier::SHIPPING_METHOD_FREE) ? 0 : $cart->getPackageShippingCost((int) $row['id_carrier'], false, null, null, $id_zone));
            $row['img'] = file_exists(_PS_SHIP_IMG_DIR_.(int)$row['id_carrier'] . '.jpg') ? _THEME_SHIP_DIR_.(int)$row['id_carrier'].'.jpg' : '';

            // If price is false, then the carrier is unavailable (carrier module)
            if ($row['price'] === false) {
                unset($result[$k]);
                continue;
            }
            $results_array[] = $row;
        }

        // if we have to sort carriers by price
        $prices = array();
        if (Configuration::get('PS_CARRIER_DEFAULT_SORT') == Carrier::SORT_BY_PRICE) {
            foreach ($results_array as $r) {
                $prices[] = $r['price'];
            }
            if (Configuration::get('PS_CARRIER_DEFAULT_ORDER') == Carrier::SORT_BY_ASC) {
                array_multisort($prices, SORT_ASC, SORT_NUMERIC, $results_array);
            } else {
                array_multisort($prices, SORT_DESC, SORT_NUMERIC, $results_array);
            }
        }

        return $results_array;
    }
}
