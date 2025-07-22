<?php
/**
 * Project : EverPsCaptcha
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverPsCaptcha extends Module
{
    private $postErrors = array();
    private $html = '';

    public function __construct()
    {
        $this->name = 'everpscaptcha';
        $this->tab = 'front_office_features';
        $this->version = '2.0.1';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Ever Ps Captcha');
        $this->description = $this->l('Protect your shop from spams with Google ReCaptcha v3 !');

        $this->isSeven = version_compare(_PS_VERSION_, '1.7', '>=');
        $this->module_key = '1ebfe89ffc0cea27d7bd0f1231f0c296';
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && Configuration::updateValue('EVERPSCAPTCHA_SITE_KEY', '')
            && Configuration::updateValue('EVERPSCAPTCHA_SECRET_KEY', '');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('EVERPSCAPTCHA_SITE_KEY')
            && Configuration::deleteByName('EVERPSCAPTCHA_SECRET_KEY');
    }

    public function hookHeader()
    {
        return $this->hookDisplayHeader();
    }

    public function hookDisplayHeader()
    {
        if (!Configuration::get('EVERPSCAPTCHA_SITE_KEY') || !Configuration::get('EVERPSCAPTCHA_SECRET_KEY')) {
            return;
        }

        $secret = Configuration::get('EVERPSCAPTCHA_SECRET_KEY');

        if (Tools::getIsset('g-recaptcha-response')) {
            if (Tools::getValue('g-recaptcha-response')) {
                $verifyResponse = Tools::file_get_contents(
                    'https://www.google.com/recaptcha/api/siteverify?secret='
                    .$secret
                    .'&response='
                    .Tools::getValue('g-recaptcha-response')
                );
                $responseData = json_decode($verifyResponse);
                if (!$responseData->success) {
                    sleep(25);
                    die('no valid Google recaptcha key');
                }
            } else {
                sleep(25);
                die('not human');
            }
        }

        if ($this->isSeven) {
            $this->context->controller->addJquery();
            $this->context->controller->registerJavascript(
                'remote-google-recaptcha',
                '//www.google.com/recaptcha/api.js?render='
                .Configuration::get('EVERPSCAPTCHA_SITE_KEY'),
                array(
                    'server' => 'remote',
                    'position' => 'bottom',
                    'priority' => 20
                )
            );
        } else {
            $this->context->controller->addJs(
                '//www.google.com/recaptcha/api.js?render='
                .Configuration::get('EVERPSCAPTCHA_SITE_KEY')
            );
        }

        $this->context->controller->addJs(
            $this->_path.'views/js/ever-ps-captcha.js'
        );

        $this->context->smarty->assign(array(
            'ever_ps_captcha_site_key' => Configuration::get('EVERPSCAPTCHA_SITE_KEY'),
        ));

        return $this->display(__FILE__, 'ever-ps-captcha.tpl');
    }

    private function postValidation()
    {
        if (Tools::isSubmit('submitSave')) {
            if (!Tools::getValue('EVERPSCAPTCHA_SITE_KEY')) {
                $this->postErrors[] = $this->displayError($this->l('The field "Google Site Key" is required.'));
            }
            if (!Tools::getValue('EVERPSCAPTCHA_SECRET_KEY')) {
                $this->postErrors[] = $this->displayError($this->l('The field "Google Secret Key" is required.'));
            }
        }
    }

    private function postProcess()
    {
        if (Tools::isSubmit('submitSave')) {
            Configuration::updateValue('EVERPSCAPTCHA_SITE_KEY', Tools::getValue('EVERPSCAPTCHA_SITE_KEY'));
            Configuration::updateValue('EVERPSCAPTCHA_SECRET_KEY', Tools::getValue('EVERPSCAPTCHA_SECRET_KEY'));
        }

        $this->html .= $this->displayConfirmation($this->l('Setting updated'));
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitSave')) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $error) {
                    $this->html .= $error;
                }
            }
        }

        $this->context->smarty->assign(array(
            'everpscaptcha_dir' => $this->_path
        ));

        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/header.tpl');
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/footer.tpl');

        return $this->html;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Ever Ps Captcha Configuration'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Google Site Key'),
                        'name' => 'EVERPSCAPTCHA_SITE_KEY',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Google Secret Key'),
                        'name' => 'EVERPSCAPTCHA_SECRET_KEY',
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSave';

        $helper->currentIndex =
            $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name
            .'&tab_module='.$this->tab
            .'&module_name='.$this->name;

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'EVERPSCAPTCHA_SITE_KEY' => Tools::getValue(
                'EVERPSCAPTCHA_SITE_KEY',
                Configuration::get('EVERPSCAPTCHA_SITE_KEY')
            ),
            'EVERPSCAPTCHA_SECRET_KEY' => Tools::getValue(
                'EVERPSCAPTCHA_SECRET_KEY',
                Configuration::get('EVERPSCAPTCHA_SECRET_KEY')
            ),
        );
    }
}
