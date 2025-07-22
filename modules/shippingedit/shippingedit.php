<?php
/**
 *  Shipping Edit
 *
 *  @author    motionSeed <ecommerce@motionseed.com>
 *  @copyright 2017 motionSeed. All rights reserved.
 *  @license   https://www.motionseed.com/en/license-module.html
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!class_exists('MotionSeedModule')) {
    include_once(dirname(__FILE__) . '/helpers/motionseed-module/MotionSeedModule.php');
}

class ShippingEdit extends MotionSeedModule
{

    protected static $carriers = array();

    public function __construct()
    {
        $this->name = 'shippingedit';
        $this->tab = 'shipping_logistics';
        $this->version = '1.7.6';
        $this->author = 'motionSeed';
        $this->need_instance = 0;
        $this->ps_versions_compliancy['min'] = '1.6.0.0';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Edit Shipping Costs');
        $this->description = $this->l('Modify shipping costs of your orders.');

        $this->error = false;
        $this->secure_key = Tools::encrypt($this->name);
        $this->module_key = '2056c754e3ebe60b4d623950804207b7';

        $this->compatible_module = array('orderfees', 'orderfees_payment', 'options', 'orderfees_shipping');
    }

    public function registerHooks()
    {
        return parent::registerHooks()
            && $this->registerHook('actionAdminCartsControllerBefore')
            && $this->registerHook('actionCartGetPackageShippingCost')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayCartRuleAdminOrders')
            && $this->registerHook('displayAdminOrderContentOrder');
    }

    public function hookActionAdminCartsControllerBefore($params)
    {
        if (Tools::getIsset('update_shipping') && $id_cart = Tools::getValue('id_cart')) {
            $controller = $params['controller'];
            $shipping_cost = Tools::getValue('update_shipping');

            $this->context->cookie->{'shipping_cost_' . $id_cart} = $shipping_cost;
            $this->context->cookie->write();

            echo Tools::jsonEncode($controller->ajaxReturnVars());
            exit();
        }
    }

    public function hookActionCartGetPackageShippingCost($params)
    {
        $cookie = $this->context->cookie;
        $id_cart = Tools::getValue('id_cart');

        if ($id_cart && isset($cookie->{'shipping_cost_' . $id_cart})) {
            $params['total'] = $cookie->{'shipping_cost_' . $id_cart};
            $params['return'] = true;

            if (!$params['use_tax']) {
                $cart = $params['object'];
                $product_list = $params['product_list'];

                if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
                    $address_id = (int) $cart->id_address_invoice;
                } elseif (count($product_list)) {
                    $prod = current($product_list);
                    $address_id = (int) $prod['id_address_delivery'];
                } else {
                    $address_id = null;
                }
                if (!Address::addressExists($address_id)) {
                    $address_id = null;
                }

                // Select carrier tax
                if (!Tax::excludeTaxeOption()) {
                    $address = Address::initialize((int) $address_id);

                    if (!Configuration::get('PS_ATCP_SHIPWRAP')) {
                        $id_carrier = $params['id_carrier'];

                        if (is_null($id_carrier) && !empty($cart->id_carrier)) {
                            $id_carrier = (int) $cart->id_carrier;
                        }

                        if (!isset(self::$carriers[$id_carrier])) {
                            self::$carriers[$id_carrier] = new Carrier(
                                (int) $id_carrier,
                                Configuration::get('PS_LANG_DEFAULT')
                            );
                        }

                        $carrier = self::$carriers[$id_carrier];
                        $carrier_tax = $carrier->getTaxesRate($address);

                        $params['total'] /= 1 + ($carrier_tax / 100);
                    }
                }
            }
        }
    }

    public function hookDisplayCartRuleAdminOrders()
    {
        $this->smarty->assign('module_name', $this->name);

        return $this->display(__FILE__, 'admin-order-form.tpl');
    }

    public function hookDisplayAdminOrderContentOrder($params)
    {        
        $this->smarty->assign('order', $params['order']);
        
        if (Tools::version_compare(_PS_VERSION_, '1.6.1')) {
            $this->context->controller->addCSS($this->getPathUri() . 'views/css/popover.css');
        }

        return $this->display(__FILE__, 'admin-order.tpl');
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::isSubmit('submitShippingNumber')) {
            $controller = $this->context->controller;
            
            if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0) {
                $order = new Order(Tools::getValue('id_order'));
                if (!Validate::isLoadedObject($order)) {
                    $controller->errors[] = Tools::displayError('The order cannot be found within your database.');
                }
            }
            
            // Fall-through handling tracking number
            if (Tools::getIsset('update_shipping')) {
                $submit_shipping_number = false;
                
                if (Tools::getIsset('shipping_tracking_number')) {
                    $tracking_number = Tools::getValue('shipping_tracking_number');
                } else {
                    $tracking_number = Tools::getValue('tracking_number');
                }
                
                if ($order->shipping_number != $tracking_number) {
                    $submit_shipping_number = true;
                }
                
                if (Tools::getIsset('shipping_carrier')) {
                    $id_carrier = Tools::getValue('shipping_carrier');
                    
                    if ($order->id_carrier != $id_carrier) {
                        $submit_shipping_number = true;
                    }
                }
                
                if (!$submit_shipping_number) {
                    unset($_POST['submitShippingNumber']);
                }
            }
            
            $shipping_cost = trim(str_replace(',', '.', Tools::getValue('update_shipping')));

            if (isset($order) && $shipping_cost != '') {
                if (!Validate::isPrice($shipping_cost)) {
                    $controller->errors[] = Tools::displayError('The shipping cost is invalid.');
                    
                    $this->smarty->assign('shipping_cost_error', true);
                    
                    return;
                }
                
                $shipping_cost_tax_incl = Tools::ps_round($shipping_cost, 6);
                $shipping_cost_tax_excl = $this->getShippingCostWithoutTax(
                    $shipping_cost_tax_incl,
                    $order->carrier_tax_rate
                );

                $order_carrier = new OrderCarrier(Tools::getValue('id_order_carrier'));

                $order_carrier->shipping_cost_tax_incl = pSQL($shipping_cost_tax_incl);
                $order_carrier->shipping_cost_tax_excl = pSQL($shipping_cost_tax_excl);

                $order->total_paid -= $order->total_shipping_tax_incl;
                $order->total_paid_tax_incl -= $order->total_shipping_tax_incl;
                $order->total_paid_tax_excl -= $order->total_shipping_tax_excl;

                $order->total_shipping = pSQL($shipping_cost_tax_incl);
                $order->total_shipping_tax_incl = pSQL($shipping_cost_tax_incl);
                $order->total_shipping_tax_excl = pSQL($shipping_cost_tax_excl);
                
                $order->total_paid += $order->total_shipping_tax_incl;
                $order->total_paid_tax_incl += $order->total_shipping_tax_incl;
                $order->total_paid_tax_excl += $order->total_shipping_tax_excl;

                if ($order->update() && $order_carrier->update()) {
                    $order_invoice_collection = $order->getInvoicesCollection();
                    
                    foreach ($order_invoice_collection as $order_invoice) {
                        // Update Order Invoice
                        $order_invoice->total_paid_tax_incl -= $order_invoice->total_shipping_tax_incl;
                        $order_invoice->total_paid_tax_excl -= $order_invoice->total_shipping_tax_excl;
                        
                        $order_invoice->total_shipping_tax_incl = pSQL($shipping_cost_tax_incl);
                        $order_invoice->total_shipping_tax_excl = pSQL($shipping_cost_tax_excl);
                        
                        $order_invoice->total_paid_tax_incl += $order_invoice->total_shipping_tax_incl;
                        $order_invoice->total_paid_tax_excl += $order_invoice->total_shipping_tax_excl;
                        
                        $order_invoice->update();
                    }
        
                    if (!property_exists($controller, 'currentIndex')) {
                        $controller::$currentIndex = null;
                    }
                    
                    if (!Tools::getIsset('submitShippingNumber')) {
                        Tools::redirectAdmin(
                            $controller::$currentIndex . '&id_order=' . $order->id
                            . '&vieworder&conf=4&token=' . $controller->token
                        );
                    }
                }
            }
        }
    }

    public function addOverride($classname)
    {
        if ($this->checkCompatibility()) {
            return true;
        }

        return parent::addOverride($classname);
    }

    public function removeOverride($classname)
    {
        if ($this->checkCompatibility()) {
            return true;
        }

        return parent::removeOverride($classname);
    }

    /*public function configureTemplates($install = true)
    {
        if ($this->checkCompatibility()) {
            return true;
        }

        return parent::configureTemplates($install);
    }*/

    public function checkCompatibility()
    {
        foreach ($this->compatible_module as $module) {
            if (self::isInstalled($module)) {
                return true;
            }
        }

        return false;
    }

    public function getShippingCostWithoutTax($shipping_cost, $tax_rate)
    {
        $tax = new Tax();
        $tax->rate = $tax_rate;
        $tax_calculator = new TaxCalculator(array($tax));

        return Tools::ps_round($tax_calculator->removeTaxes($shipping_cost), 6);
    }
}
