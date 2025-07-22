<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class CartController extends CartControllerCore
{
    public function initContent()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            if (Module::isEnabled('opartlimitquantity')) {
                $limitQtyObj = Module::getInstanceByName('opartlimitquantity');
                if ($qtyErrors = $limitQtyObj->checkCartQuantities()) {
                    $this->errors = array_merge($this->errors, $qtyErrors);
                }
            }
        }
        parent::initContent();
    }

    protected function processChangeProductInCart()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $mode = (Tools::getIsset('update') && $this->id_product) ? 'update' : 'add';
            $ErrorKey = ('update' === $mode) ? 'updateOperationError' : 'errors';
            if (Tools::getIsset('group')) {
                $this->id_product_attribute = (int)Product::getIdProductAttributeByIdAttributes(
                    $this->id_product,
                    Tools::getValue('group')
                );
            }
            if ($this->qty == 0) {
                $this->{$ErrorKey}[] = $this->trans(
                    'Null quantity.',
                    [],
                    'Shop.Notifications.Error'
                );
            } elseif (!$this->id_product) {
                $this->{$ErrorKey}[] = $this->trans(
                    'Product not found',
                    [],
                    'Shop.Notifications.Error'
                );
            }
            $product = new Product($this->id_product, true, $this->context->language->id);
            if (!$product->id || !$product->active || !$product->checkAccess($this->context->cart->id_customer)) {
                $this->{$ErrorKey}[] = $this->trans(
                    'This product (%product%) is no longer available.',
                    ['%product%' => $product->name],
                    'Shop.Notifications.Error'
                );
                return;
            }
            if (!$this->id_product_attribute && $product->hasAttributes()) {
                $minimum_quantity = ($product->out_of_stock == 2)
                    ? !Configuration::get('PS_ORDER_OUT_OF_STOCK')
                    : !$product->out_of_stock;
                $this->id_product_attribute = Product::getDefaultAttribute($product->id, $minimum_quantity);
                if (!$this->id_product_attribute) {
                    Tools::redirectAdmin($this->context->link->getProductLink($product));
                }
            }
            $qty_to_check = $this->qty;
            $cart_products = $this->context->cart->getProducts();
            if (is_array($cart_products)) {
                foreach ($cart_products as $cart_product) {
                    if ($this->productInCartMatchesCriteria($cart_product)) {
                        $qty_to_check = $cart_product['cart_quantity'];
                        if (Tools::getValue('op', 'up') == 'down') {
                            $qty_to_check -= $this->qty;
                        } else {
                            $qty_to_check += $this->qty;
                        }
                        break;
                    }
                }
            }
            if ('update' !== $mode && $this->shouldAvailabilityErrorBeRaised($product, $qty_to_check)) {
                $this->{$ErrorKey}[] = $this->trans(
                    'The product is no longer available in this quantity.',
                    [],
                    'Shop.Notifications.Error'
                );
            }
            if (!$this->id_product_attribute) {
                if ($qty_to_check < $product->minimal_quantity) {
                    die('dead');
                    $this->errors[] = $this->trans(
                        'The minimum purchase order quantity for the product %product% is %quantity%.',
                        ['%product%' => $product->name, '%quantity%' => $product->minimal_quantity],
                        'Shop.Notifications.Error'
                    );
                    return;
                }
            } else {
                $combination = new Combination($this->id_product_attribute);
                if ($qty_to_check < $combination->minimal_quantity) {
                    $this->errors[] = $this->trans(
                        'The minimum purchase order quantity for the product %product% is %quantity%.',
                        ['%product%' => $product->name, '%quantity%' => $combination->minimal_quantity],
                        'Shop.Notifications.Error'
                    );
                    return;
                }
            }

            $limitQtyObj = Module::getInstanceByName('opartlimitquantity');
            if ($qtyErrors = $limitQtyObj->checkProductQty($product, $this->id_product_attribute, $qty_to_check)) {
                $this->errors = array_merge($this->errors, $qtyErrors);
            }

            if (!$this->errors) {
                if (!$this->context->cart->id) {
                    if (Context::getContext()->cookie->id_guest) {
                        $guest = new Guest(Context::getContext()->cookie->id_guest);
                        $this->context->cart->mobile_theme = $guest->mobile_theme;
                    }
                    $this->context->cart->add();
                    if ($this->context->cart->id) {
                        $this->context->cookie->id_cart = (int)$this->context->cart->id;
                    }
                }
                if (!$product->hasAllRequiredCustomizableFields() && !$this->customization_id) {
                    $this->{$ErrorKey}[] = $this->trans(
                        'Please fill in all of the required fields, and then save your customizations.',
                        [],
                        'Shop.Notifications.Error'
                    );
                }
                if (!$this->errors) {
                    $cart_rules = $this->context->cart->getCartRules();
                    $available_cart_rules = CartRule::getCustomerCartRules(
                        $this->context->language->id,
                        (isset($this->context->customer->id) ? $this->context->customer->id : 0),
                        true,
                        true,
                        true,
                        $this->context->cart,
                        false,
                        true
                    );
                    $update_quantity = $this->context->cart->updateQty(
                        $this->qty,
                        $this->id_product,
                        $this->id_product_attribute,
                        $this->customization_id,
                        Tools::getValue('op', 'up'),
                        $this->id_address_delivery,
                        null,
                        true,
                        true
                    );
                    if ($update_quantity < 0) {
                        $minimal_quantity = ($this->id_product_attribute)
                            ? Attribute::getAttributeMinimalQty($this->id_product_attribute)
                            : $product->minimal_quantity;
                        $this->{$ErrorKey}[] = $this->trans(
                            'You must add %quantity% minimum quantity',
                            ['%quantity%' => $minimal_quantity],
                            'Shop.Notifications.Error'
                        );
                    } elseif (!$update_quantity) {
                        $this->errors[] = $this->trans(
                            'You already have the maximum quantity available for this product.',
                            [],
                            'Shop.Notifications.Error'
                        );
                    } elseif ($this->shouldAvailabilityErrorBeRaised($product, $qty_to_check)) {
                        $this->{$ErrorKey}[] = $this->trans(
                            'The product is no longer available in this quantity.',
                            [],
                            'Shop.Notifications.Error'
                        );
                    }
                }
            }
            $removed = CartRule::autoRemoveFromCart();
            CartRule::autoAddToCart();
        } else {
            $mode = (Tools::getIsset('update') && $this->id_product) ? 'update' : 'add';
            if ($this->qty == 0) {
                $this->errors[] = Tools::displayError('Null quantity.', !Tools::getValue('ajax'));
            } elseif (!$this->id_product) {
                $this->errors[] = Tools::displayError('Product not found', !Tools::getValue('ajax'));
            }
            $product = new Product($this->id_product, true, $this->context->language->id);
            if (!$product->id || !$product->active) {
                $this->errors[] = Tools::displayError('This product is no longer available.', !Tools::getValue('ajax'));
                return;
            }
            $qty_to_check = $this->qty;
            $cart_products = $this->context->cart->getProducts();
            if (is_array($cart_products)) {
                foreach ($cart_products as $cart_product) {
                    if ((!isset($this->id_product_attribute) || $cart_product['id_product_attribute'] == $this->id_product_attribute) &&
                        (isset($this->id_product) && $cart_product['id_product'] == $this->id_product)) {
                        $qty_to_check = $cart_product['cart_quantity'];
                        if (Tools::getValue('op', 'up') == 'down') {
                            $qty_to_check -= $this->qty;
                        } else {
                            $qty_to_check += $this->qty;
                        }
                        break;
                    }
                }
            }
            if ($this->id_product_attribute) {
                if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($this->id_product_attribute, $qty_to_check)) {
                    $this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));
                }
            } elseif ($product->hasAttributes()) {
                $minimumQuantity = ($product->out_of_stock == 2) ? !Configuration::get('PS_ORDER_OUT_OF_STOCK') : !$product->out_of_stock;
                $this->id_product_attribute = Product::getDefaultAttribute($product->id, $minimumQuantity);
                if (!$this->id_product_attribute) {
                    Tools::redirectAdmin($this->context->link->getProductLink($product));
                } elseif (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty($this->id_product_attribute, $qty_to_check)) {
                    $this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));
                }
            } elseif (!$product->checkQty($qty_to_check)) {
                $this->errors[] = Tools::displayError('There isn\'t enough product in stock.', !Tools::getValue('ajax'));
            }

            $limitQtyObj = Module::getInstanceByName('opartlimitquantity');
            if ($qtyErrors = $limitQtyObj->checkProductQty($product, $this->id_product_attribute, $qty_to_check)) {
                $this->errors = array_merge($this->errors, $qtyErrors);
            }

            if (!$this->errors && $mode == 'add') {
                if (!$this->context->cart->id) {
                    if (Context::getContext()->cookie->id_guest) {
                        $guest = new Guest(Context::getContext()->cookie->id_guest);
                        $this->context->cart->mobile_theme = $guest->mobile_theme;
                    }
                    $this->context->cart->add();
                    if ($this->context->cart->id) {
                        $this->context->cookie->id_cart = (int)$this->context->cart->id;
                    }
                }
                if (!$product->hasAllRequiredCustomizableFields() && !$this->customization_id) {
                    $this->errors[] = Tools::displayError('Please fill in all of the required fields, and then save your customizations.', !Tools::getValue('ajax'));
                }
                if (!$this->errors) {
                    $cart_rules = $this->context->cart->getCartRules();
                    $update_quantity = $this->context->cart->updateQty($this->qty, $this->id_product, $this->id_product_attribute, $this->customization_id, Tools::getValue('op', 'up'), $this->id_address_delivery);
                    if ($update_quantity < 0) {
                        $minimal_quantity = ($this->id_product_attribute) ? Attribute::getAttributeMinimalQty($this->id_product_attribute) : $product->minimal_quantity;
                        $this->errors[] = sprintf(Tools::displayError('You must add %d minimum quantity', !Tools::getValue('ajax')), $minimal_quantity);
                    } elseif (!$update_quantity) {
                        $this->errors[] = Tools::displayError('You already have the maximum quantity available for this product.', !Tools::getValue('ajax'));
                    } elseif ((int)Tools::getValue('allow_refresh')) {
                        $cart_rules2 = $this->context->cart->getCartRules();
                        if (count($cart_rules2) != count($cart_rules)) {
                            $this->ajax_refresh = true;
                        } else {
                            $rule_list = array();
                            foreach ($cart_rules2 as $rule) {
                                $rule_list[] = $rule['id_cart_rule'];
                            }
                            foreach ($cart_rules as $rule) {
                                if (!in_array($rule['id_cart_rule'], $rule_list)) {
                                    $this->ajax_refresh = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if ($limitQtyObj->checkCartQuantities() && Tools::getValue('allow_refresh')) {
                $this->ajax_refresh = true;
            }

            $removed = CartRule::autoRemoveFromCart();
            CartRule::autoAddToCart();
            if (count($removed) && (int)Tools::getValue('allow_refresh')) {
                $this->ajax_refresh = true;
            }
        }
    }
}
