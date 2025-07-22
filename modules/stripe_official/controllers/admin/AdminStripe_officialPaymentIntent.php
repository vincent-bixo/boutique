<?php
/**
 * Copyright (c) since 2010 Stripe, Inc. (https://stripe.com)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Stripe <https://support.stripe.com/contact/email>
 * @copyright Since 2010 Stripe, Inc.
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminStripe_officialPaymentIntentController extends ModuleAdminController
{
    /** @var bool Active bootstrap for Prestashop 1.6 */
    public $bootstrap = true;

    /** @var Module Instance of your module automatically set by ModuleAdminController */
    public $module;

    /** @var string Associated object class name */
    public $className = StripeEvent::class;

    /** @var string Associated table name */
    public $table = 'stripe_event';

    /** @var string|false Object identifier inside the associated table */
    public $identifier = 'id_payment_intent';

    /** @var string Default ORDER BY clause when is not defined */
    protected $_defaultOrderBy = 'id_stripe_event';

    /** @var bool List content lines are clickable if true */
    protected $list_no_link = true;

    public $multishop_context = 1;

    protected $actions = ['details'];
    const MODULE_CLASS = 'Modules.Stripeofficial.AdminStripe_officialPaymentIntent';

    /**
     * @var \StripeOfficial\Classes\services\PrestashopTranslationService
     */
    protected $translationService;

    /**
     * @see AdminController::__construct()
     */
    public function __construct()
    {
        parent::__construct();
        $this->translationService = new \StripeOfficial\Classes\services\PrestashopTranslationService($this->module, self::MODULE_CLASS, 'AdminStripe_officialPaymentIntentController');

        $this->_select = 'o.id_order, sp.id_cart, sp.id_payment_intent, sp.type, spi.status, o.reference';
        $this->_join =
            'INNER JOIN `' . _DB_PREFIX_ . 'stripe_payment` sp ON (a.id_payment_intent = sp.id_payment_intent AND sp.result > 0)
            INNER JOIN `' . _DB_PREFIX_ . 'stripe_payment_intent` spi ON (sp.id_payment_intent = spi.id_payment_intent)
            INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON (sp.id_cart = o.id_cart)';

        $this->explicitSelect = true;

        $this->fields_list = [
            'id_order' => [
                'title' => $this->translationService->translate('Order ID'),
                'filter_key' => 'o!id_order',
                'orderby' => false,
            ],
            'id_cart' => [
                'title' => $this->translationService->translate('Cart ID'),
                'filter_key' => 'sp!id_cart',
                'orderby' => false,
            ],
            'id_payment_intent' => [
                'title' => $this->translationService->translate('Payment Intent'),
                'filter_key' => 'sp!id_payment_intent',
                'orderby' => false,
            ],
            'type' => [
                'title' => $this->translationService->translate('Payment Method'),
                'orderby' => false,
            ],
            'status' => [
                'title' => $this->translationService->translate('Charge Status'),
                'filter_key' => 'spi!status',
                'orderby' => false,
            ],
            'reference' => [
                'title' => $this->translationService->translate('Order Reference'),
                'orderby' => false,
            ],
        ];
    }

    /**
     * @see AdminController::initToolbar()
     */
    public function initToolbar()
    {
        parent::initToolbar();
        // Remove the add new item button
        unset($this->toolbar_btn['new']);
        unset($this->toolbar_btn['delete']);
    }

    /**
     * @throws PrestaShopException
     *
     * @see AdminController::initToolbar()
     */
    public function renderDetails()
    {
        $this->_select = null;
        $this->_join = null;
        $this->_group = null;
        $this->_filter = null;
        $this->_where = ' AND a.id_payment_intent = "' . pSQL(Tools::getValue('id_payment_intent')) . '"';
        $this->_orderBy = 'date_add';

        $this->actions = [];

        $this->list_simple_header = true;
        $this->explicitSelect = false;

        $this->fields_list = [
            'id_payment_intent' => [
                'title' => $this->translationService->translate('Payment Intent'),
            ],
            'status' => [
                'title' => $this->translationService->translate('Event Status'),
            ],
            'is_processed' => [
                'title' => $this->translationService->translate('Processed'),
            ],
            'date_add' => [
                'title' => $this->translationService->translate('Saving date'),
                'align' => 'right',
                'class' => 'fixed-width-xs',
            ],
            'flow_type' => [
                'title' => $this->translationService->translate('Flow Type'),
            ],
        ];

        return $this->renderList();
    }
}
