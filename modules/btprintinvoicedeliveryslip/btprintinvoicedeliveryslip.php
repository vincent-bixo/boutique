<?php
/**
 * Module Print Invoice Deliveryslip - Main file
 *
 *  @author    MyAddons <support@myaddons.io>
 *  @copyright 2017 My Addons
 *  @license   myaddons.io
 *  @version   1.0.6
 *  @since     File available since Release 1.0
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class BtPrintInvoiceDeliverySlip extends Module
{
    public function __construct()
    {
        $this->author = 'MyAddons';
        $this->tab = 'quick_bulk_update';
        $this->need_instance = 0;
        $this->module_key = "51bbcf5d79afee3bfdc12116e13d42d5";
        $this->name = 'btprintinvoicedeliveryslip';
        $this->author_address = '0xE4a7B4d225E2D9ACDf6d9e579EA04925912d32A0';
        $this->version = '1.0.7';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Print invoices and delivery slip in mass');
        $this->description = $this->l('This module allows you to print invoices and delivery slip in mass on orders list view.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this module ?');

        /* Retrocompatibility */
        $this->initContext();

        $this->css_path = $this->_path.'views/css/';
        $this->js_path = $this->_path.'views/js/';
    }

    /* Retrocompatibility 1.4/1.5 */
    private function initContext()
    {
        $this->context = Context::getContext();
        if (!$this->context->shop->id) {
            $this->context->shop->id = 1;
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install()
                || !$this->registerHook('displayBackOfficeFooter')
                || !$this->registerHook('displayBackOfficeHeader')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /*************************************************************************************************
    DISPLAY BACK OFFICE MANAGEMENT
    **************************************************************************************************/
    /**
     * Loads asset resources.
     */
    public function loadAsset()
    {
        $css_compatibility = $js_compatibility = array();

        /* Load CSS */
        $css = array(
                $this->css_path.'bootstrap-select.min.css',
                $this->css_path.'DT_bootstrap.css',
                $this->css_path.'fix.css',
                );

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $css_compatibility = array(
                            $this->css_path.'bootstrap.css',
                            $this->css_path.'bootstrap.extend.css',
                            $this->css_path.'bootstrap-responsive.min.css',
                            $this->css_path.'font-awesome.min.css',
                            $this->css_path.'back.1.5.css',
                            );
            $css = array_merge($css_compatibility, $css);
        }
        $this->context->controller->addCSS($css, 'all');

        unset($css, $css_compatibility);

        $this->context->controller->addJquery();

        /* Load JS */
        $js = array(
                        $this->js_path.'bootstrap-select.min.js',
                        $this->js_path.'bootstrap-dialog.js',
                        $this->js_path.'jquery.autosize.min.js',
                        $this->js_path.'jquery.dataTables.js',
                        $this->js_path.'DT_bootstrap.js',
                        $this->js_path.'dynamic_table_init.js',
                        $this->js_path.'jscolor.js',
                        $this->js_path.'module.js',
                        );

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $js_compatibility = array(
                            $this->js_path.'bootstrap.min.js',
                            );
            $js = array_merge($js_compatibility, $js);
        }
        $this->context->controller->addJS($js);

        /* Clean memory */
        unset($js, $js_compatibility);
    }

    public function getContent()
    {
        $this->loadAsset();

        /**
         * If values have been submitted in the form, process.
         */
        $smarty = $this->context->smarty;
        $smarty->assign('module_dir', $this->_path);
        $smarty->assign('version', $this->version);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (!$this->active) {
            return;
        }


        if ((isset(Context::getContext()->controller->controller_name)
            && preg_match('/AdminOrders/i', Context::getContext()->controller->controller_name))) {
                $this->context->controller->addJquery();
                $this->context->controller->addCSS($this->css_path.'admin.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeFooter()
    {
        if (!$this->active) {
            return;
        }

        if ((isset(Context::getContext()->controller->controller_name) && preg_match('/AdminOrders/i', Context::getContext()->controller->controller_name))) {
            //if (!Tools::isSubmit('submitBulkupdateOrderStatusorder') && !Tools::getValue('order_pagination')) {
              if (Tools::isSubmit('submitBulkupdateOrderPrintInvoices')) {
                    if (Tools::getValue('orderBox')) {
                        //Generate invoice
                        $order_invoice_collection = array();

                        $order_invoice_list = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                                            SELECT oi.*
                                            FROM `'._DB_PREFIX_.'order_invoice` oi
                                            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oi.`id_order`)
                                            WHERE oi.number > 0
                                            AND oi.`id_order` IN ('.implode(',', array_map('intval', Tools::getValue('orderBox'))).')
                                            ORDER BY oi.`date_add` ASC
                                            ');

                        $order_invoice_collection = ObjectModel::hydrateCollection('OrderInvoice', $order_invoice_list);

                        if (count($order_invoice_collection)) {
                            $pdf_invoice = new PDF($order_invoice_collection, PDF::TEMPLATE_INVOICE, Context::getContext()->smarty);

                            if (ob_get_level() && ob_get_length() > 0) {
                                ob_clean();
                            }

                            die($pdf_invoice->render());
                            unset($pdf_invoice);
                        } else {
                            $error = $this->l('Selected orders have no associated invoices.');
                        }
                    } else {
                        $error = $this->l('Please, select at least one order. Thanks!');
                    }
                }

                if (Tools::isSubmit('submitBulkupdateOrderPrintDeliverySlip')) {
                    if (Tools::getValue('orderBox')) {
                        //Generate delivery
                        $order_delivery_collection = array();

                        $order_delivery_list = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                                            SELECT oi.*
                                            FROM `'._DB_PREFIX_.'order_invoice` oi
                                            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oi.`id_order`)
                                            WHERE oi.delivery_date > 0
                                            AND oi.`id_order` IN ('.implode(',', array_map('intval', Tools::getValue('orderBox'))).')
                                            ORDER BY oi.`date_add` ASC
                                            ');

                        $order_delivery_collection = ObjectModel::hydrateCollection('OrderInvoice', $order_delivery_list);

                        if (count($order_delivery_collection)) {
                            $pdf_delivery = new PDF($order_delivery_collection, PDF::TEMPLATE_DELIVERY_SLIP, Context::getContext()->smarty);

                            if (ob_get_level() && ob_get_length() > 0) {
                                ob_clean();
                            }

                            die($pdf_delivery->render());
                            unset($pdf_delivery);
                        } else {
                            $error = $this->l('Selected orders have no associated delivery slip.');
                        }
                    } else {
                        $error = $this->l('Please, select at least one order. Thanks!');
                    }
                }

                if (Tools::isSubmit('submitBulkupdateOrderPrintAll')) {
                    if (Tools::getValue('orderBox')) {
                        $render = false;
                        $pdf_renderer = new PDFGenerator((bool)Configuration::get('PS_PDF_USE_CACHE'), 'P');
                        $pdf_renderer->setFontForLang(Context::getContext()->language->iso_code);
                        $filename = false;
                        foreach (Tools::getValue('orderBox') as $id_order) {
                            //Generate invoice
                            $id_order_invoice = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                                                SELECT oi.id_order_invoice
                                                FROM `'._DB_PREFIX_.'order_invoice` oi
                                                LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oi.`id_order`)
                                                WHERE oi.number > 0
                                                AND oi.`id_order` = '.(int) $id_order.'
                                                ');

                            if ($id_order_invoice) {
                                $order_invoice = new OrderInvoice((int) $id_order_invoice);

                                if (Validate::isLoadedObject($order_invoice)) {
                                    $pdf_renderer->startPageGroup();
                                    $template = $this->getTemplateObject($order_invoice, PDF::TEMPLATE_INVOICE);

                                    if (!$template) {
                                        continue;
                                    }

                                    if (!$filename) {
                                        $filename = 'invoice-delivery.pdf';
                                    }

                                    $template->assignHookData($order_invoice);

                                    $pdf_renderer->createHeader($template->getHeader());
                                    $pdf_renderer->createFooter($template->getFooter());
                                    $pdf_renderer->createPagination($template->getPagination());
                                    $pdf_renderer->createContent($template->getContent());
                                    $pdf_renderer->writePage();
                                    $render = true;
                                    unset($template);
                                }

                                unset($order_invoice);
                            }

                            //Generate delivery
                            $id_order_delivery = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                                                SELECT oi.id_order_invoice
                                                FROM `'._DB_PREFIX_.'order_invoice` oi
                                                LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oi.`id_order`)
                                                WHERE oi.delivery_date > 0
                                                AND oi.`id_order` = '.(int) $id_order.'
                                                ');

                            if ($id_order_delivery) {
                                $order_invoice_delivery = new OrderInvoice((int) $id_order_delivery);

                                if (Validate::isLoadedObject($order_invoice_delivery)) {
                                    $pdf_renderer->startPageGroup();
                                    $template = $this->getTemplateObject($order_invoice_delivery, PDF::TEMPLATE_DELIVERY_SLIP);

                                    if (!$template) {
                                        continue;
                                    }

                                    if (!$filename) {
                                        $filename = 'invoice-delivery.pdf';
                                    }

                                    $template->assignHookData($order_invoice_delivery);

                                    $pdf_renderer->createHeader($template->getHeader());
                                    $pdf_renderer->createFooter($template->getFooter());
                                    $pdf_renderer->createPagination($template->getPagination());
                                    $pdf_renderer->createContent($template->getContent());
                                    $pdf_renderer->writePage();
                                    $render = true;
                                    unset($template);
                                }
                            }
                        }

                        if ($render) {
                            if (ob_get_level() && ob_get_length() > 0) {
                                ob_clean();
                            }

                            die($pdf_renderer->render($filename, true));
                        } else {
                            $error = $this->l('Selected orders have no associated delivery slip and no associated invoices.');
                        }
                    } else {
                        $error = $this->l('Please, select at least one order. Thanks!');
                    }
                }

                if (isset($error)) {
                    $this->context->smarty->assign('errorPrint', $error);
                    $this->context->smarty->assign('version', $this->version);
                }

                return $this->display(__FILE__, Tools::substr(_PS_VERSION_, 0, 3).'/admin_order.tpl');
            //}
        }
    }

    public function getTemplateObject($object, $template)
    {
        $class = false;
        $class_name = 'HTMLTemplate'.$template;

        if (class_exists($class_name)) {
            // Some HTMLTemplateXYZ implementations won't use the third param but this is not a problem (no warning in PHP),
            // the third param is then ignored if not added to the method signature.
            $class = new $class_name($object, Context::getContext()->smarty, true);

            if (!($class instanceof HTMLTemplate)) {
                throw new PrestaShopException('Invalid class. It should be an instance of HTMLTemplate');
            }
        }

        return $class;
    }
}
