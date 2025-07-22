<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Frederic Moreau
 *  @copyright 2020 BeComWeb
 *  @license   LICENSE.txt
 */

class AdminChangeproductcreationdateController extends ModuleAdminController
{
    public $ssl = true;

    public function __construct()
    {
        parent::__construct();
        $this->token = Tools::getAdminToken('AdminChangeproductcreationdate');
    }

    public function ajaxProcessUpdateCreationDate()
    {
        //Check if product id is valid
        $id_product = (int)Tools::getValue('id_product');
        if (Tools::isEmpty((int) $id_product)) {
            die(Tools::jsonEncode(array(
                'result' => false,
                'msg' => $this->module->l('An error occured while retrieving product. Please refresh this page and try again.', 'AdminChangeproductcreationdateController')
            )));
        }
        //Then check if date is valid
        $new_product_date = Tools::getValue('product_creation_date');
        if (!Validate::isDate($new_product_date)) {
            die(Tools::jsonEncode(array(
                'result' => false,
                'msg' => $this->module->l('Invalid date. Please correct it then submit it again', 'AdminChangeproductcreationdateController')
            )));
        }
        if (Shop::isFeatureActive() && (Shop::getContext() != Shop::CONTEXT_SHOP)) {
            foreach (Shop::getContextListShopID(true) as $id_shop) {
                $product = new Product($id_product, false, null, (int)$id_shop);
                if (Validate::isLoadedObject($product)) {
                    $product->date_add = $new_product_date;
                    $product_update = $product->update();
                    if (!$product_update) {
                        break;
                    }
                }
            }
            $update_msg = $this->module->l('Creation date has been updated successfully for all your shops.', 'AdminChangeproductcreationdateController');
        } else {
            $product = new Product($id_product, false, null, (int)$this->context->shop->id);
            $product->date_add = $new_product_date;
            $product_update = $product->update();
            $update_msg = $this->module->l('Creation date has been updated successfully for the current shop.', 'AdminChangeproductcreationdateController');
        }
        if ($product_update) {
            die(Tools::jsonEncode(array(
                'result' => true,
                'msg' => $update_msg
            )));
        } else {
            die(Tools::jsonEncode(array(
                'result' => false,
                'msg' => $this->module->l('An error occured while updating creation date. At least one of your shop has not been updated. Please try again.', 'AdminChangeproductcreationdateController')
            )));
        }
    }
}
