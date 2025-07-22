<?php
/**
 * Project : everpsshoppayment3
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link http://team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Everpsshoppayment3ValidationModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;

        parent::init();
    }

    public function initContent()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0
            || (int)$cart->id_address_delivery == 0
            || (int)$cart->id_address_invoice == 0
            || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        if ((int)$cart->id_carrier != (int)Configuration::get('EVERPSSHOPPAY_ID_CARRIER3')
            && (int)Configuration::get('EVERPSSHOPPAY_BLOCK_CARRIER3')) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'everpsshoppayment3') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->trans('This payment method is not available.', array(), 'Modules.Everpsshoppayment3.Shop'));
        }
        parent::initContent();

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        $this->module->validateOrder(
            (int)$cart->id,
            Configuration::get('PS_OS_EVERPSSHOPPAYMENT3'),
            $total,
            $this->module->displayName,
            null,
            null,
            (int)$currency->id,
            false,
            $customer->secure_key
        );
        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='
            .(int)$cart->id
            .'&id_module='
            .(int)$this->module->id
            .'&id_order='
            .$this->module->currentOrder
            .'&key='.$customer->secure_key
        );
    }
}
