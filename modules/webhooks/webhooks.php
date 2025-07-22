<?php
/**
 * 2007-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2024 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Httpful\Request;

class Webhooks extends Module
{
    protected $form_success = [];
    protected $form_warning = [];
    protected $form_error = [];

    private $hooks = [
        'actionOrderHistoryAddAfter' => 'Order status updated',
        'actionValidateOrder' => 'Order created',
        'actionProductSave' => 'Product saved',
        'actionProductUpdate' => 'Product updated',
        'actionCustomerAccountAdd' => 'Customer created',
        'actionCustomerAccountUpdate' => 'Customer updated',
        'actionObjectAddressUpdateAfter' => 'Customer address updated',
        'actionObjectCustomerMessageAddAfter' => 'Customer message added',
        'actionPasswordRenew' => 'Customer password renewed',
        'actionUpdateQuantity' => 'Product quantity updated',
        'actionObjectStockAvailableUpdateAfter' => 'Product stock updated',
        'actionCronJob' => '',
        'displayBackOfficeHeader' => '',
        'addWebserviceResources' => '',
    ];

    public function __construct()
    {
        $this->name = 'webhooks';
        $this->tab = 'export';
        $this->version = '2.7.1';
        $this->author = 'Wild Fortress Lda';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '32e21ee3b54664a6289ce0db094e6ecd';

        parent::__construct();

        $this->displayName = $this->l('Webhooks');
        $this->description = $this->l('Webhooks integration for Prestashop');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        include dirname(__FILE__) . '/sql/install.php';

        $webhook_secure_key = Configuration::get('WEBHOOKS_SECURE_KEY');
        $cron_secure_key = Configuration::get('WEBHOOKS_CRON_SECURE_KEY');
        if (false === $webhook_secure_key) {
            Configuration::updateValue(
                'WEBHOOKS_SECURE_KEY',
                Tools::strtoupper(Tools::passwdGen(32))
            );
        }
        if (false === $cron_secure_key) {
            Configuration::updateValue(
                'WEBHOOKS_CRON_SECURE_KEY',
                Tools::strtoupper(Tools::passwdGen(32))
            );
        }

        return parent::install() && $this->registerHook(array_keys($this->hooks));
    }

    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';

        Configuration::deleteByName('WEBHOOKS_SECURE_KEY');
        Configuration::deleteByName('WEBHOOKS_CRON_SECURE_KEY');

        foreach ($this->hooks as $hook => $name) {
            $this->unregisterHook($hook);
        }

        return parent::uninstall();
    }

    /** ------------------------------------------------------------------------ */
    /** VIEWS                                                                    */
    /** ------------------------------------------------------------------------ */

    /**
     * @param int $id_queue
     *
     * @return mixed
     */
    protected function renderQueueView($id_queue)
    {
        $queue = WebhookQueueModel::getById((int) $id_queue);
        $this->smarty->assign($queue);

        return $this->display($this->local_path, 'views/templates/admin/queue/view.tpl');
    }

    /**
     * @param int $id_queue
     *
     * @return mixed
     */
    protected function renderLogView($id_queue)
    {
        $log = WebhookLogModel::getById((int) $id_queue);
        $this->smarty->assign($log);

        return $this->display($this->local_path, 'views/templates/admin/log/view.tpl');
    }

    /** ------------------------------------------------------------------------ */
    /** LISTS                                                                    */
    /** ------------------------------------------------------------------------ */

    /**
     * Renders webhooks list
     *
     * @return mixed
     */
    protected function renderWebhooksList()
    {
        $fields_list = [
            'id_webhook' => [
                'title' => $this->l('ID'),
                'search' => false,
            ],
            'hook' => [
                'title' => $this->l('Hook'),
                'search' => false,
            ],
            'url' => [
                'title' => $this->l('URL'),
                'search' => false,
            ],
            'real_time' => [
                'title' => $this->l('Real-time'),
                'search' => false,
                'type' => 'bool',
            ],
            'retries' => [
                'title' => $this->l('Retries'),
                'search' => false,
            ],
            'active' => [
                'title' => $this->l('Status'),
                'search' => false,
                'type' => 'bool',
                'align' => 'center',
                'icon' => [
                    0 => 'disabled.gif',
                    1 => 'enabled.gif',
                    'default' => 'disabled.gif',
                ],
            ],
            'date_add' => [
                'title' => $this->l('Created at'),
                'type' => 'datetime',
                'search' => false,
            ],
        ];

        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            unset($fields_list['shop_name']);
        }

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->l('Webhooks');
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id_webhook';
        $helper_list->table = 'Webhook';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = ['edit', 'toggleStatus', 'delete'];
        $helper_list->shopLinkType = '';

        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $helper_list->listTotal = WebhookModel::getWebhooksTotal();

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;

        $logs = WebhookModel::getWebhooks($page, $pagination);

        return $helper_list->generateList($logs, $fields_list);
    }

    /**
     * Renders webhooks list
     *
     * @return mixed
     */
    protected function renderQueuesList()
    {
        $fields_list = [
            'id_queue' => [
                'title' => $this->l('Queue ID'),
                'search' => false,
            ],
            'id_webhook' => [
                'title' => $this->l('Webhook ID'),
                'search' => false,
            ],
            'url' => [
                'title' => $this->l('URL'),
                'search' => false,
            ],
            'executed' => [
                'title' => $this->l('Executed'),
                'search' => false,
                'type' => 'bool',
                'align' => 'center',
                'icon' => [
                    0 => 'disabled.gif',
                    1 => 'enabled.gif',
                    'default' => 'disabled.gif',
                ],
            ],
            'retry' => [
                'title' => $this->l('Retries'),
                'search' => false,
            ],
            'date_add' => [
                'title' => $this->l('Queued at'),
                'type' => 'datetime',
                'search' => false,
            ],
        ];

        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            unset($fields_list['shop_name']);
        }

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->l('Queued webhooks');
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id_queue';
        $helper_list->table = 'Queue';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = ['view', 'resend', 'delete'];
        $helper_list->shopLinkType = '';

        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $helper_list->listTotal = WebhookQueueModel::getQueuesTotal();

        if ($helper_list->listTotal === 0) {
            return '';
        }

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;

        $logs = WebhookQueueModel::getQueues($page, $pagination);

        return $helper_list->generateList($logs, $fields_list);
    }

    /**
     * Renders the logs list
     *
     * @return mixed
     */
    protected function renderLogsList()
    {
        $fields_list = [
            'id_log' => [
                'title' => $this->l('ID'),
                'search' => false,
            ],
            'id_webhook' => [
                'title' => $this->l('Webhook ID'),
                'search' => false,
            ],
            'real_time' => [
                'title' => $this->l('Real-time'),
                'search' => false,
                'type' => 'bool',
            ],
            'url' => [
                'title' => $this->l('URL'),
                'search' => false,
            ],
            'status_code' => [
                'title' => $this->l('Status'),
                'search' => false,
            ],
            'date_add' => [
                'title' => $this->l('Created at'),
                'type' => 'datetime',
                'search' => false,
            ],
        ];

        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            unset($fields_list['shop_name']);
        }

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->l('Execution Logs');
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id_log';
        $helper_list->table = 'Log';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = ['view', 'delete'];
        $helper_list->shopLinkType = '';

        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $helper_list->listTotal = WebhookLogModel::getLogsTotal();

        if ($helper_list->listTotal === 0) {
            return '';
        }

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;

        $logs = WebhookLogModel::getLogs($page, $pagination);

        return $helper_list->generateList($logs, $fields_list);
    }

    /**
     * Resend link in Queues
     *
     * @param null $token
     * @param int $id
     * @param null $name
     *
     * @return mixed
     */
    public function displayResendLink($token = null, $id = -1, $name = null)
    {
        $query = http_build_query([
            'configure' => $this->name,
            'id_queue' => (int) $id,
            'token' => $token,
            'resendQueue' => true,
        ]);

        $this->context->smarty->assign([
            'location_ok' => $this->context->link->getAdminLink('AdminModules', false) . '&' . $query,
            'location_ko' => 'javascript:void(0)',
            'action' => $this->l('Resend'),
            'confirm' => $this->l('Are you sure you want to re-send this webhook?'),
        ]);

        return $this->display($this->local_path, 'views/templates/admin/actions/resend.tpl');
    }

    /**
     * Toggle status of webhooks
     *
     * @param null $token
     * @param int $id
     * @param null $name
     *
     * @return mixed
     */
    public function displayToggleStatusLink($token = null, $id = -1, $name = null)
    {
        $query = http_build_query([
            'configure' => $this->name,
            'id_webhook' => (int) $id,
            'token' => $token,
            'toggleWebhook' => true,
        ]);

        $this->context->smarty->assign([
            'location_ok' => $this->context->link->getAdminLink('AdminModules', false) . '&' . $query,
            'location_ko' => 'javascript:void(0)',
            'action' => $this->l('Toggle status'),
            'confirm' => $this->l('Are you sure you want to change this webhook status?'),
        ]);

        return $this->display($this->local_path, 'views/templates/admin/actions/toggle.tpl');
    }

    /** ------------------------------------------------------------------------ */
    /** CREATE FORM                                                              */
    /** ------------------------------------------------------------------------ */

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        // Decode messages in the URL
        $messages = Tools::getValue('messages', false);
        if ($messages) {
            $this->unserializeMessages($messages);
        }

        // Create & update Webhook
        if (((bool) Tools::isSubmit('submitWebhooksModule')) == true) {
            $id_webhook = (int) Tools::getValue('id_webhook');

            if ($id_webhook === 0) {
                $this->createNewWebhook();
            } else {
                $this->updateWebhook($id_webhook);
            }
        }

        // Delete Webhook
        if (((bool) Tools::isSubmit('deleteWebhook')) == true) {
            WebhookLogModel::deleteByWebhookId((int) Tools::getValue('id_webhook'));
            WebhookQueueModel::deleteByWebhookId((int) Tools::getValue('id_webhook'));
            WebhookModel::deleteById((int) Tools::getValue('id_webhook'));
            $this->setSuccessMessage($this->l('Webhook was deleted.'));

            return $this->redirectHome();
        }

        // Toggle Webhook
        if (((bool) Tools::isSubmit('toggleWebhook')) == true) {
            WebhookModel::changeWebhookStatus((int) Tools::getValue('id_webhook'));
            $this->setSuccessMessage($this->l('Webhook status was changed.'));

            return $this->redirectHome();
        }

        // Delete Queue
        if (((bool) Tools::isSubmit('deleteQueue')) == true) {
            WebhookQueueModel::deleteById((int) Tools::getValue('id_queue'));
            $this->setSuccessMessage($this->l('Queued webhook was deleted.'));

            return $this->redirectHome();
        }

        // Delete Log
        if (((bool) Tools::isSubmit('deleteLog')) == true) {
            WebhookLogModel::deleteById((int) Tools::getValue('id_log'));
            $this->setSuccessMessage($this->l('Log entry was deleted.'));

            return $this->redirectHome();
        }

        // View Queue
        if (((bool) Tools::isSubmit('viewQueue')) == true) {
            $id_queue = (int) Tools::getValue('id_queue');

            return $this->renderQueueView($id_queue);
        }

        // View Log
        if (((bool) Tools::isSubmit('viewLog')) == true) {
            $id_log = (int) Tools::getValue('id_log');

            return $this->renderLogView($id_log);
        }

        // Resend Queue
        if (((bool) Tools::isSubmit('resendQueue')) == true) {
            return $this->resendQueue();
        }

        $create_webhook_url = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token=' .
            Tools::getAdminTokenLite('AdminModules') . '&createWebhook';

        $cronjob_url = $this->context->link->getModuleLink(
            $this->name,
            'cron',
            ['secure_key' => Configuration::get('WEBHOOKS_CRON_SECURE_KEY')]
        );

        $this->context->smarty->assign('module_dir', $this->_path);

        // Messages
        $this->context->smarty->assign('form_success', $this->form_success);
        $this->context->smarty->assign('form_warning', $this->form_warning);
        $this->context->smarty->assign('form_error', $this->form_error);

        // URLS
        $this->context->smarty->assign('create_webhook_url', $create_webhook_url);
        $this->context->smarty->assign('cronjob_url', $cronjob_url);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/base.tpl');

        if (((bool) Tools::isSubmit('createWebhook')) == true
            || ((bool) Tools::isSubmit('updateWebhook')) == true
            || ((bool) Tools::isSubmit('submitWebhooksModule')) == true
        ) {
            $output .= $this->renderCreateWebhookForm();
        } else {
            $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/panel.tpl');
            $output .= $this->renderWebhooksList();
            $output .= $this->renderQueuesList();
            $output .= $this->renderLogsList();
        }

        return $output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderCreateWebhookForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWebhooksModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $options = [];

        foreach ($this->hooks as $hook => $name) {
            if (!empty($name)) {
                $options[] = [
                    'id_option' => $hook,
                    'name' => $name,
                ];
            }
        }

        $is_update = (int) Tools::getValue('id_webhook', 0);

        $this->context->smarty->assign([
            'webhooks_retries' => (int) Tools::getValue('WEBHOOKS_RETRIES'),
        ]);

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l($is_update ? 'Update Webhook' : 'New Webhook'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_webhook',
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-link"></i>',
                        'desc' => $this->l('Enter a valid URL address to POST the webhook'),
                        'name' => 'WEBHOOKS_URL',
                        'required' => true,
                        'label' => $this->l('URL'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Hook event'),
                        'desc' => $this->l('Choose an event to trigger'),
                        'name' => 'WEBHOOKS_HOOK',
                        'required' => true,
                        'options' => [
                            'query' => $options,
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'col' => 2,
                        'type' => 'html',
                        'desc' => $this->l('Retry this times before stopping (min: 0, max: 99)'),
                        'name' => 'WEBHOOKS_RETRIES',
                        'required' => true,
                        'label' => $this->l('Retries'),
                        'html_content' => $this->display($this->local_path, 'views/templates/admin/inputs/retries_input.tpl'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Real time'),
                        'name' => 'WEBHOOKS_REAL_TIME',
                        'is_bool' => true,
                        'required' => true,
                        'desc' => $this->l('Use this webhook in real time mode'),
                        'value' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l($is_update ? 'Update webhook' : 'Save webhook'),
                ],
                'buttons' => [
                    'go-back' => [
                        'title' => $this->l('Go back'),
                        'name' => 'goBack',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-back',
                    ],
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $webhook = WebhookModel::getById((int) Tools::getValue('id_webhook'));

        if (!$webhook) {
            return [
                'id_webhook' => 0,
                'WEBHOOKS_URL' => Tools::getValue('WEBHOOKS_URL'),
                'WEBHOOKS_HOOK' => Tools::getValue('WEBHOOKS_HOOK'),
                'WEBHOOKS_RETRIES' => (int) Tools::getValue('WEBHOOKS_RETRIES'),
                'WEBHOOKS_REAL_TIME' => (int) Tools::getValue('WEBHOOKS_REAL_TIME'),
            ];
        }

        // Small hack
        $_GET['WEBHOOKS_RETRIES'] = (int) $webhook['retries'];

        return [
            'id_webhook' => $webhook['id_webhook'],
            'WEBHOOKS_URL' => $webhook['url'],
            'WEBHOOKS_HOOK' => $webhook['hook'],
            'WEBHOOKS_REAL_TIME' => (int) $webhook['real_time'],
        ];
    }

    /**
     * Creates a new webhook
     */
    protected function createNewWebhook()
    {
        // Sanitize URL
        $url = filter_var(Tools::getValue('WEBHOOKS_URL'), FILTER_SANITIZE_URL);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->setErrorMessage($this->l('The URL is invalid'));
        } elseif (!Validate::isHookName(Tools::getValue('WEBHOOKS_HOOK'))) {
            $this->setErrorMessage($this->l('Hook name is invalid'));
        } elseif (!Validate::isUnsignedInt(Tools::getValue('WEBHOOKS_RETRIES'))
            || ((int) Tools::getValue('WEBHOOKS_RETRIES')) > 99) {
            $this->setErrorMessage($this->l('Please choose a retry number between 0 and 99'));
        } else {
            WebhookModel::insertWebhook(
                $url,
                Tools::getValue('WEBHOOKS_HOOK'),
                Tools::getValue('WEBHOOKS_REAL_TIME', 0),
                (int) Tools::getValue('WEBHOOKS_RETRIES'),
                1
            );

            $this->setSuccessMessage($this->l('Webhook inserted'));

            return $this->redirectHome();
        }
    }

    /**
     * Updates a webhook
     *
     * @param $id_webhook
     *
     * @return mixed
     */
    protected function updateWebhook($id_webhook)
    {
        // Sanitize URL
        $url = filter_var(Tools::getValue('WEBHOOKS_URL'), FILTER_SANITIZE_URL);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->setErrorMessage($this->l('The URL is invalid'));
        } elseif (!Validate::isHookName(Tools::getValue('WEBHOOKS_HOOK'))) {
            $this->setErrorMessage($this->l('Hook name is invalid'));
        } elseif (!Validate::isUnsignedInt(Tools::getValue('WEBHOOKS_RETRIES'))
            || ((int) Tools::getValue('WEBHOOKS_RETRIES')) > 99) {
            $this->setErrorMessage($this->l('Please choose a retry number between 0 and 99'));
        } else {
            WebhookModel::updateWebhook(
                $id_webhook,
                $url,
                Tools::getValue('WEBHOOKS_HOOK'),
                Tools::getValue('WEBHOOKS_REAL_TIME', 0),
                (int) Tools::getValue('WEBHOOKS_RETRIES')
            );

            $this->setSuccessMessage($this->l('Webhook updated'));

            return $this->redirectHome();
        }
    }

    /**
     * @return mixed
     */
    protected function resendQueue()
    {
        $id_queue = (int) Tools::getValue('id_queue');
        $queue = WebhookQueueModel::getById($id_queue);
        $webhook = WebhookModel::getById((int) $queue['id_webhook']);
        $payload = json_decode($queue['payload']);

        WebhookQueueModel::incrementRetry($id_queue);

        try {
            $this->makeRequest($webhook, $payload);
            $this->setSuccessMessage($this->l('Webhook was executed!'));
        } catch (Exception $e) {
            $this->setErrorMessage($this->l('There was an error executing the Webhook! ' . $e->getMessage()));
        }

        return $this->redirectHome();
    }

    /** ------------------------------------------------------------------------ */
    /** REQUESTS & QUEUEING                                                      */
    /** ------------------------------------------------------------------------ */

    /**
     * Actually makes the webhook request
     *
     * @param WebhookModel $webhook
     * @param mixed $payload
     *
     * @return void
     *
     * @throws Exception
     */
    private function makeRequest($webhook, $payload)
    {
        $response = Request::post($webhook['url'])
            ->withoutStrictSsl()
            ->withXSecureKey(Configuration::get('WEBHOOKS_SECURE_KEY'))
            ->withXHook($webhook['hook'])
            ->withXHookId($webhook['id_webhook'])
            ->sendsJson()
            ->body(json_encode($payload))
            ->send();

        WebhookLogModel::insertLog($webhook, $payload, $response->body, $response->code);

        // 200; 201; 202; 203; 204
        if ($response->code < 200 || $response->code > 204) {
            throw new Exception("Error: expected HTTP 2XX status code response but got {$response->code}.");
        }
    }

    /**
     * Fires the webhook and logs or queue if it fails
     *
     * @param WebhookModel $webhook
     * @param mixed $payload
     *
     * @return void
     *
     * @throws Exception
     */
    private function fireWebhook($webhook, $payload)
    {
        try {
            $this->makeRequest($webhook, $payload);
        } catch (Exception $e) {
            $this->queueWebhook($webhook, $payload);
            // PrestaShopLogger::addLog(var_export($e, true), 1);
        }
    }

    /**
     * Dispatch the execution of the webhook depending on the type of webhook
     *
     * @param WebhookModel $webhook
     * @param mixed $payload
     *
     * @return void
     *
     * @throws Exception
     */
    private function dispatchWebhook($webhook, $payload)
    {
        PrestaShopLogger::addLog(
            'dispatchWebhook ' . $webhook['hook'] . ' with ID ' . $webhook['id_webhook'],
            1
        );

        // PrestaShopLogger::addLog(var_export($payload, true), 1);

        $payload = $this->decoratePayload($webhook['hook'], $payload);

        if ($webhook['real_time']) {
            $this->fireWebhook($webhook, $payload);
        } else {
            $this->queueWebhook($webhook, $payload);
        }
    }

    /**
     * Queues the webhook in the DB for later
     *
     * @param WebhookModel $webhook
     * @param mixed $payload
     *
     * @return void
     */
    private function queueWebhook($webhook, $payload)
    {
        WebhookQueueModel::insertQueue($webhook, $payload);
    }

    /**
     * Decorate payload and transform into stdClass
     *
     * @param string $hook
     * @param mixed $payload
     *
     * @return mixed
     *
     * @throws Exception
     */
    private function decoratePayload($hook, $payload)
    {
        // Transform payload into stdClass
        $payload = json_decode(json_encode($payload));

        if (WebhookDecorator::hasDecorator($hook)) {
            try {
                $decoratorClass = WebhookDecorator::getDecoratorClass($hook);
                $decoratorValue = WebhookDecorator::getDecoratorValue($hook, $payload);

                $enhancer = new $decoratorClass($decoratorValue, $payload);
                $payload = $enhancer->present();

                return $payload;
            } catch (Exception $e) {
                PrestaShopLogger::addLog(var_export($e, true), 1);
            }
        }

        return $payload;
    }

    /**
     * Prestashop own function for cron frequency
     *
     * @return array
     */
    public function getCronFrequency()
    {
        return [
            'hour' => '-1',
            'day' => '-1',
            'month' => '-1',
            'day_of_week' => '-1',
        ];
    }

    /** ------------------------------------------------------------------------ */
    /** ALL HOOKS BELLOW                                                         */
    /** ------------------------------------------------------------------------ */
    public function hookActionValidateOrder($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionValidateOrder');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionOrderHistoryAddAfter($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionOrderHistoryAddAfter');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionProductSave($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionProductSave');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionProductUpdate($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionProductUpdate');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionCustomerAccountAdd');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionCustomerAccountUpdate($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionCustomerAccountUpdate');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookactionObjectAddressUpdateAfter($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionObjectAddressUpdateAfter');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionObjectCustomerMessageAddAfter($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionObjectCustomerMessageAddAfter');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionUpdateQuantity($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionUpdateQuantity');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionObjectStockAvailableUpdateAfter($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionObjectStockAvailableUpdateAfter');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionCronJob()
    {
        if (Module::isInstalled($this->name)) {
            $queued = WebhookQueueModel::getAllActiveAndNonExecuted();

            foreach ($queued as $queue) {
                $webhook = WebhookModel::getById($queue['id_webhook']);
                $payload = json_decode($queue['payload']);
                WebhookQueueModel::incrementRetry($queue['id_queue']);
                try {
                    $this->makeRequest($webhook, $payload);
                    WebhookQueueModel::markExecuted($queue['id_queue']);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog(var_export($e, true), 1);
                }
            }
        }
    }

    public function hookAddWebserviceResources()
    {
        return [
            'webhooks' => [
                'description' => 'Webhooks',
                'class' => 'WebhookModel',
            ],
        ];
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminModules'
            && (Tools::getValue('configure') == $this->name || Tools::getValue('module_name') == $this->name)) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /** ------------------------------------------------------------------------ */
    /** MESSAGES & STUFF                                                         */
    /** ------------------------------------------------------------------------ */

    /**
     * @param $message
     */
    protected function setErrorMessage($message)
    {
        $this->form_error[] = $message;
    }

    /**
     * @param $message
     */
    protected function setSuccessMessage($message)
    {
        $this->form_success[] = $message;
    }

    /**
     * @param $message
     */
    protected function setWarningMessage($message)
    {
        $this->form_warning[] = $message;
    }

    /**
     * Serializer messages for redirect
     *
     * @return string
     */
    protected function getSerializedMessages()
    {
        return urlencode(json_encode([
            'e' => $this->form_error,
            's' => $this->form_success,
            'w' => $this->form_warning,
        ]));
    }

    /**
     * Unserialize Messages from URL to local vars
     *
     * @param $serialized_message
     */
    protected function unserializeMessages($serialized_message)
    {
        $messages = json_decode(urldecode($serialized_message), true);

        $this->form_error = array_merge($this->form_error, $messages['e']);
        $this->form_success = array_merge($this->form_success, $messages['s']);
        $this->form_warning = array_merge($this->form_warning, $messages['w']);
    }

    /**
     * Return home
     */
    protected function redirectHome()
    {
        return Tools::redirectAdmin(
            Context::getContext()->link->getAdminLink('AdminModules') . '&' .
            http_build_query(['configure' => $this->name, 'messages' => $this->getSerializedMessages()])
        );
    }
}
