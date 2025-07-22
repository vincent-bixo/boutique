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

abstract class PaymentModule extends PaymentModuleCore
{
    protected function createOrderFromCart(
        Cart $cart,
        Currency $currency,
        $productList,
        $addressId,
        $context,
        $reference,
        $secure_key,
        $payment_method,
        $name,
        $dont_touch_amount,
        $amount_paid,
        $warehouseId,
        $cart_total_paid,
        $debug,
        $order_status,
        $id_order_state,
        $carrierId = null
    ) {
		$order_object = parent::createOrderFromCart(
			$cart,
			$currency,
			$productList,
			$addressId,
			$context,
			$reference,
			$secure_key,
			$payment_method,
			$name,
			$dont_touch_amount,
			$amount_paid,
			$warehouseId,
			$cart_total_paid,
			$debug,
			$order_status,
			$id_order_state,
			$carrierId
		);

        $order_object['order']->product_list = $productList;
		$total_discounts_tax_excl = (float)abs(
			$cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order_object['order']->product_list, $carrierId)
		);

        if (!version_compare(_PS_VERSION_, '1.7.6.0', '>=') ||
			!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') ||
			$total_discounts_tax_excl == 0) {
            return $order_object;
		}

        require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
		// If no products in cart using ASM, no need to continue
        if (WarehouseStock::getNumberOfAsmProductsInCart($cart->id) <= 0) {
			return $order_object;
		}

		// Calculate the total of discounts applied to the whole order
		$total_discount_whole_order_incl = $total_discount_whole_order_excl = 0;
		$computingPrecision = Context::getContext()->getComputingPrecision();
		foreach ($this->context->cart->getCartRules() as $cart_rule) {
			//if ($cart_rule['product_restriction'] == 0) {
				if ($cart_rule['reduction_product'] == 0) {
					$total_discount_whole_order_incl += $cart_rule['value_real'];
					$total_discount_whole_order_excl += $cart_rule['value_tax_exc'];
				}
            	// *************************************************************************
            	// *************** Discount (%) on the cheapest product ****************
            	// *************************************************************************
				if ($cart_rule['reduction_product'] == -1 && $cart_rule['reduction_percent']) {
					/* Look for the cheapieast product ID in the whole cart */
                	$minPrice = $id_cheapiest_product = false;
        			foreach ($this->context->cart->getProducts() as $product) {
                    	$price = $product['price'];
						if ($price > 0 && ($minPrice === false || $minPrice > $price)) {
							$minPrice = $price;
							$id_cheapiest_product = $product['id_product'];
						}
					}					
					// If id_cheapiest_product not belongs to order products list:
					if ($id_cheapiest_product) {
						$order_products_id = array();
						$minOrderPrice = false;
						//  Look for the cheapest product price in current order
						foreach ($order_object['order']->product_list as $product) {
							$order_products_id[] = (int)$product['id_product'];
							$price = $product['price'];
							if ($price > 0 && ($minOrderPrice === false || $minOrderPrice > $price)) {
								$minOrderPrice = $price;
							}
						}
						if (!in_array($id_cheapiest_product, $order_products_id)) {
							/* Calculate discounts applied to this order to subtruct them later */
							$discount_sheapest_excl = Tools::ps_round(($minOrderPrice * $cart_rule['reduction_percent'] / 100), $computingPrecision);
							$minOrderPrice *= (1 + $this->context->cart->getAverageProductsTaxRate());
							$discount_sheapest_incl = Tools::ps_round(($minOrderPrice * $cart_rule['reduction_percent'] / 100), $computingPrecision);

							// Update the final discounts by removing the discount based on cheapeast product
							$order_object['order']->total_discounts_tax_excl -= $discount_sheapest_excl;
							$order_object['order']->total_discounts_tax_incl -= $discount_sheapest_incl;
							$order_object['order']->total_discounts = $order_object['order']->total_discounts_tax_incl;
							// Restore totals values without discounts
							$order_object['order']->total_paid_tax_excl += $discount_sheapest_excl;
							$order_object['order']->total_paid_tax_incl += $discount_sheapest_incl;
							$order_object['order']->total_paid = $order_object['order']->total_paid_tax_incl;
							$order_object['order']->update();
						}
					}
            	// *************************************************************************
            	// *************************************************************************
				}
			//}
		}
		if ($total_discount_whole_order_excl == 0 || $total_discount_whole_order_incl == 0) {
			return $order_object;
		}

        $list_brothers = array();
        foreach ($order_object['order']->getBrother() as $brother) {
            $list_brothers[] = (int)$brother->id;
        }

		if (count($list_brothers)) {
			$saved_discount_incl = $order_object['order']->total_discounts_tax_incl;
			$saved_discount_excl = $order_object['order']->total_discounts_tax_excl;
			if ($saved_discount_incl >= $total_discount_whole_order_incl) {
				// Update the real discounts
				$order_object['order']->total_discounts_tax_excl -= Tools::ps_round($total_discount_whole_order_excl, $computingPrecision);
				$order_object['order']->total_discounts_tax_incl -= Tools::ps_round($total_discount_whole_order_incl, $computingPrecision);
				$order_object['order']->total_discounts = $order_object['order']->total_discounts_tax_incl;
				// Restore real totals values without discounts
				$order_object['order']->total_paid_tax_excl = ($order_object['order']->total_paid_tax_excl + $saved_discount_excl) - $order_object['order']->total_discounts_tax_excl;
				$order_object['order']->total_paid_tax_incl = ($order_object['order']->total_paid_tax_incl + $saved_discount_incl) - $order_object['order']->total_discounts_tax_incl;
				$order_object['order']->total_paid = $order_object['order']->total_paid_tax_incl;
				$order_object['order']->update();
			}
		}

		return $order_object;
    }

    protected function createOrderCartRules(
        Order $order,
        Cart $cart,
        $order_list,
        $total_reduction_value_ti,
        $total_reduction_value_tex,
        $id_order_state
    ) {
		$cart_rules_list = parent::createOrderCartRules(
			$order,
			$cart,
			$order_list,
			$total_reduction_value_ti,
			$total_reduction_value_tex,
			$id_order_state
		);

		$total_discounts_tax_excl = (float)abs(
			$cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $order->id_carrier)
		);

        if (!version_compare(_PS_VERSION_, '1.7.6.0', '>=') ||
			!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') ||
			$total_discounts_tax_excl == 0) {
            return $cart_rules_list;
		}

        require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
		// If no products in cart using ASM, no need to continue
        if (WarehouseStock::getNumberOfAsmProductsInCart($cart->id) <= 0) {
			return $cart_rules_list;
		}
		// Get the first sub-order
        $first_order = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			'SELECT MIN(`id_order`)
             FROM `' . _DB_PREFIX_ . 'orders`
             WHERE `reference` = \''.pSQL($order->reference).'\''
		);

		// *************************************************************************
		// *************************************************************************
		// If cart rule applied to cheapest product but not the concerned order,  remove it
		// 1- Look for the cheapieast product ID in the whole cart
		$minPrice = $id_cheapiest_product = false;
		foreach ($this->context->cart->getProducts() as $product) {
			$price = $product['price'];
			if ($price > 0 && ($minPrice === false || $minPrice > $price)) {
				$minPrice = $price;
				$id_cheapiest_product = $product['id_product'];
			}
		}					
		foreach ($this->context->cart->getCartRules() as $cart_rule) {
			if ($cart_rule['reduction_product'] == -1 && $id_cheapiest_product) {
				$order_products_id = array();
				foreach ($order->product_list as $product) {
					$order_products_id[] = (int)$product['id_product'];
				}
				//  2- If cheapest product don't belongs to the current order
				if (!in_array($id_cheapiest_product, $order_products_id)) {
					// Remove cart rule
					Db::getInstance()->delete(
						'order_cart_rule',
						'`id_order` = '.(int)$order->id.' AND id_cart_rule = '.(int)$cart_rule['obj']->id
					);
					// 3- Update cart rules list (fix email sending)
					foreach ($cart_rules_list as $k => $rule) {
						if ($rule['voucher_name'] ==  $cart_rule['obj']->name) {
							unset($cart_rules_list[$k]);
						}
					}
				}
			}
		}
		// *************************************************************************
		// *************************************************************************

		// Fix discount problem, need to be applied only for brothers orders
		if ($order->id != $first_order) {
			foreach ($this->context->cart->getCartRules() as $cart_rule) {
				// If cart rule applied to the whole order > remove it (because has been applied for the first order)
				if ($cart_rule['reduction_product'] == 0) {
					// Remove cart rule
					Db::getInstance()->delete(
						'order_cart_rule',
						'`id_order` = '.(int)$order->id.' AND id_cart_rule = '.(int)$cart_rule['obj']->id
					);
					// Update cart rules list (fix email sending)
					foreach ($cart_rules_list as $k => $rule) {
						if ($rule['voucher_name'] ==  $cart_rule['obj']->name) {
							unset($cart_rules_list[$k]);
						}
					}
				}
			}
		}

		return $cart_rules_list;
	}
}
