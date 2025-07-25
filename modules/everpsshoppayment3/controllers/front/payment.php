<?php
/**
 * Project : everpsshoppayment3
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link http://team-ever.com
 */

/**
 * @since 1.5.0
 */
class Everpsshoppayment3PaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('payment.tpl');
    }
}
