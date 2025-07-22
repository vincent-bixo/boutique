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

class OrderController extends OrderControllerCore
{
    public function initContent()
    {
        /* opartquantitylimit */
        $limitQtyObj = null;
        if (Module::isEnabled('opartlimitquantity')) {
            $limitQtyObj = Module::getInstanceByName('opartlimitquantity');
        }

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            if ($limitQtyObj) {
                if ($qtyErrors = $limitQtyObj->checkCartQuantities()) {
                    $this->errors = array_merge($this->errors, $qtyErrors);
                }

                if (count($this->errors)) {
                    Tools::redirect($this->context->link->getPageLink('cart') . '?action=show');
                    die;
                }
            }

            parent::initContent();
        } else {
            if ($limitQtyObj) {
                if ($qtyErrors = $limitQtyObj->checkCartQuantities()) {
                    $this->errors = array_merge($this->errors, $qtyErrors);
                    $this->step = 0;
                }
            }

            parent::initContent();
        }
    }
}
