<?php
/*
* 2007-2015 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_Reminder extends Module
{
    private $conf_keys = array();

    public function __construct()
    {
        $this->name = 'ps_reminder';
        $this->tab = 'advertising_marketing';
        $this->version = '2.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->conf_keys = array(
            'PS_FOLLOW_UP_ENABLE_1',
            'PS_FOLLOW_UP_ENABLE_2',
            'PS_FOLLOW_UP_ENABLE_3',
            'PS_FOLLOW_UP_ENABLE_4',
            'PS_FOLLOW_UP_AMOUNT_1',
            'PS_FOLLOW_UP_AMOUNT_2',
            'PS_FOLLOW_UP_AMOUNT_3',
            'PS_FOLLOW_UP_AMOUNT_4',
            'PS_FOLLOW_UP_DAYS_1',
            'PS_FOLLOW_UP_DAYS_2',
            'PS_FOLLOW_UP_DAYS_3',
            'PS_FOLLOW_UP_DAYS_4',
            'PS_FOLLOW_UP_THRESHOLD_3',
            'PS_FOLLOW_UP_DAYS_THRESHOLD_4',
            'PS_FOLLOW_UP_CLEAN_DB'
        );

        $this->bootstrap = true;
        parent::__construct();

        $this->ps_versions_compliancy = array('min' => '1.7.2.0', 'max' => _PS_VERSION_);

        $secure_key = Configuration::get('PS_FOLLOWUP_SECURE_KEY');
        if (false === $secure_key) {
            Configuration::updateValue(
                'PS_FOLLOWUP_SECURE_KEY',
                Tools::strtoupper(Tools::passwdGen(16))
            );
        }

        $this->displayName = $this->trans(
            'Customer follow-up',
            array(),
            'Modules.Reminder.Admin'
        );
        $this->description = $this->trans(
            'Follow-up with your customers by sending abandonment cart emails and other reminders.',
            array(),
            'Modules.Reminder.Admin'
        );
    }

    public function install()
    {
        Db::getInstance()->execute('
		CREATE TABLE '._DB_PREFIX_.'log_email (
		`id_log_email` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`id_email_type` INT UNSIGNED NOT NULL ,
		`id_cart_rule` INT UNSIGNED NOT NULL ,
		`id_customer` INT UNSIGNED NULL ,
		`id_cart` INT UNSIGNED NULL ,
		`date_add` DATETIME NOT NULL,
		 INDEX `date_add`(`date_add`),
		 INDEX `id_cart`(`id_cart`)
		) ENGINE='._MYSQL_ENGINE_);

        foreach ($this->conf_keys as $key) {
            Configuration::updateValue($key, 0);
        }


        return parent::install();
    }

    public function uninstall()
    {
        foreach ($this->conf_keys as $key) {
            Configuration::deleteByName($key);
        }

        Configuration::deleteByName('PS_FOLLOWUP_SECURE_KEY');
        Db::getInstance()->execute('DROP TABLE '._DB_PREFIX_.'log_email');

        return parent::uninstall();
    }

    public function getContent()
    {
        $html = '';
        if (Tools::isSubmit('submitFollowUp')) {
            $ok = true;
            foreach ($this->conf_keys as $c) {
                if (Tools::getValue($c) !== false) {
                    $ok &= Configuration::updateValue(
                        $c,
                        (float)Tools::getValue($c)
                    );
                }
            }
            if ($ok) {
                $html .= $this->displayConfirmation($this->trans(
                    'Settings updated succesfully',
                    array(),
                    'Modules.Reminder.Admin'
                ));
            } else {
                $html .= $this->displayError($this->trans(
                    'Error occurred during settings update',
                    array(),
                    'Modules.Reminder.Admin'
                ));
            }
        }
        $html .= $this->renderForm();
        $html .= $this->renderStats();

        return $html;
    }

    private function logEmail(
        $id_email_type,
        $id_cart_rule,
        $id_customer = null,
        $id_cart = null
    ) {
        $values = array(
            'id_email_type' => (int)$id_email_type,
            'id_cart_rule' => (int)$id_cart_rule,
            'date_add' => date('Y-m-d H:i:s')
        );
        if (!empty($id_cart)) {
            $values['id_cart'] = (int)$id_cart;
        }
        if (!empty($id_customer)) {
            $values['id_customer'] = (int)$id_customer;
        }
        Db::getInstance()->insert('log_email', $values);
    }

    /* Each cart which wasn't transformed into an order */
    private function cancelledCart($count = false)
    {
        $email_logs = $this->getLogsEmail(1);
        $sql = '
		SELECT c.id_cart,
		       c.id_lang,
		       cu.id_customer,
		       c.id_shop,
		       cu.firstname,
		       cu.lastname,
		       cu.email
		FROM '._DB_PREFIX_.'cart c
		LEFT JOIN '._DB_PREFIX_.'orders o
		ON (o.id_cart = c.id_cart)
		RIGHT JOIN '._DB_PREFIX_.'customer cu
		ON (cu.id_customer = c.id_customer)
		WHERE DATE_SUB(CURDATE(),INTERVAL 7 DAY) <= c.date_add
		AND o.id_order IS NULL';

        $sql .= Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c');

        if (!empty($email_logs)) {
            $sql .= ' AND c.id_cart NOT IN ('.join(',', $email_logs).')';
        }

        $sql .= ' GROUP BY cu.id_customer';

        $emails = Db::getInstance()->executeS($sql);

        if ($count || !count($emails)) {
            return count($emails);
        }

        $conf = Configuration::getMultiple(array(
            'PS_FOLLOW_UP_AMOUNT_1',
            'PS_FOLLOW_UP_DAYS_1'
        ));
        foreach ($emails as $email) {
            $voucher = $this->createDiscount(
                1,
                (float)$conf['PS_FOLLOW_UP_AMOUNT_1'],
                (int)$email['id_customer'],
                strftime(
                    '%Y-%m-%d',
                    strtotime('+' . (int)$conf['PS_FOLLOW_UP_DAYS_1'].' day')
                ),
                $this->trans(
                    'Discount for your cancelled cart',
                    array(),
                    'Modules.Reminder.Admin')
            );
            if (false !== $voucher) {
                $template_vars = array(
                    '{email}' => $email['email'],
                    '{lastname}' => $email['lastname'],
                    '{firstname}' => $email['firstname'],
                    '{amount}' => $conf['PS_FOLLOW_UP_AMOUNT_1'],
                    '{days}' => $conf['PS_FOLLOW_UP_DAYS_1'],
                    '{voucher_num}' => $voucher->code
                );
                Mail::Send(
                    (int)$email['id_lang'],
                    'followup_1',
                    Mail::l(
                        'Your cart and your discount',
                        (int)$email['id_lang']
                    ),
                    $template_vars,
                    $email['email'],
                    $email['firstname'] . ' ' . $email['lastname'],
                    null,
                    null,
                    null,
                    null,
                    dirname(__FILE__).'/mails/'
                );
                $this->logEmail(
                    1,
                    (int)$voucher->id,
                    (int)$email['id_customer'],
                    (int)$email['id_cart']
                );
            }
        }
    }

    private function getLogsEmail($email_type)
    {
        static $id_list = array(
            '1' => array(),
            '2' => array(),
            '3' => array(),
            '4' => array(),
        );
        static $executed = false;

        if (!$executed) {
            $query = '
			SELECT id_cart,
			       id_customer,
			       id_email_type
            FROM '._DB_PREFIX_.'log_email
			WHERE id_email_type <> 4
			OR date_add >= DATE_SUB(date_add, INTERVAL ' .
                (int)Configuration::get(
                    'PS_FOLLOW_UP_DAYS_THRESHOLD_4'
                ).' DAY)';
            $results = Db::getInstance()->executeS($query);
            foreach ($results as $line) {
                switch ($line['id_email_type']) {
                    case 1:
                        $id_list['1'][] = $line['id_cart'];
                        break;
                    case 2:
                        $id_list['2'][] = $line['id_cart'];
                        break;
                    case 3:
                        $id_list['3'][] = $line['id_customer'];
                        break;
                    case 4:
                        $id_list['4'][] = $line['id_customer'];
                        break;
                }
            }
            $executed = true;
        }

        return $id_list[$email_type];
    }

    /* For all validated orders, a discount if re-ordering before x days */
    private function reOrder($count = false)
    {
        $email_logs = $this->getLogsEmail(2);
        $sql = '
		SELECT o.id_order,
		       c.id_cart,
		       o.id_lang,
		       o.id_shop,
		       cu.id_customer,
		       cu.firstname,
		       cu.lastname,
		       cu.email
		FROM '._DB_PREFIX_.'orders o
		LEFT JOIN '._DB_PREFIX_.'customer cu
		ON (cu.id_customer = o.id_customer)
		LEFT JOIN '._DB_PREFIX_.'cart c
		ON (c.id_cart = o.id_cart)
		WHERE o.valid = 1
		AND c.date_add >= DATE_SUB(CURDATE(),INTERVAL 7 DAY)
		AND cu.is_guest = 0';

        $sql .= Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'o');

        if (!empty($email_logs)) {
            $sql .= ' AND o.id_cart NOT IN ('.join(',', $email_logs).')';
        }

        $emails = Db::getInstance()->executeS($sql);

        if ($count || !count($emails)) {
            return count($emails);
        }

        $conf = Configuration::getMultiple(array(
            'PS_FOLLOW_UP_AMOUNT_2',
            'PS_FOLLOW_UP_DAYS_2'
        ));
        foreach ($emails as $email) {
            $voucher = $this->createDiscount(
                2,
                (float)$conf['PS_FOLLOW_UP_AMOUNT_2'],
                (int)$email['id_customer'],
                strftime(
                    '%Y-%m-%d',
                    strtotime('+' . (int)$conf['PS_FOLLOW_UP_DAYS_2'].' day')
                ),
                $this->trans(
                    'Thank you for your order.',
                    array(),
                    'Modules.Reminder.Admin'
                )
            );
            if (false !== $voucher) {
                $template_vars = array(
                    '{email}' => $email['email'],
                    '{lastname}' => $email['lastname'],
                    '{firstname}' => $email['firstname'],
                    '{amount}' => $conf['PS_FOLLOW_UP_AMOUNT_2'],
                    '{days}' => $conf['PS_FOLLOW_UP_DAYS_2'],
                    '{voucher_num}' => $voucher->code
                );
                Mail::Send(
                    (int)$email['id_lang'],
                    'followup_2',
                    Mail::l(
                        'Thanks for your order',
                        (int)$email['id_lang']
                    ),
                    $template_vars,
                    $email['email'],
                    $email['firstname'] . ' ' . $email['lastname'],
                    null,
                    null,
                    null,
                    null,
                    dirname(__FILE__).'/mails/'
                );
                $this->logEmail(
                    2,
                    (int)$voucher->id,
                    (int)$email['id_customer'],
                    (int)$email['id_cart']
                );
            }
        }
    }

    /* For all customers with more than x euros in 90 days */
    private function bestCustomer($count = false)
    {
        $email_logs = $this->getLogsEmail(3);

        $sql = '
		SELECT SUM(o.total_paid) total,
		       c.id_cart,
		       o.id_lang,
		       cu.id_customer,
		       cu.id_shop,
		       cu.firstname,
		       cu.lastname,
		       cu.email
		FROM '._DB_PREFIX_.'orders o
		LEFT JOIN '._DB_PREFIX_.'customer cu
		ON (cu.id_customer = o.id_customer)
		LEFT JOIN '._DB_PREFIX_.'cart c
		ON (c.id_cart = o.id_cart)
        WHERE o.valid = 1
        AND DATE_SUB(CURDATE(),INTERVAL 90 DAY) <= o.date_add
        AND cu.is_guest = 0 ';

        $sql .= Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'o');

        if (!empty($email_logs)) {
            $sql .= ' AND cu.id_customer NOT IN ('.join(',', $email_logs).') ';
        }

        $sql .= 'GROUP BY o.id_customer HAVING total >= ' .
            (float)Configuration::get('PS_FOLLOW_UP_THRESHOLD_3');

        $emails = Db::getInstance()->executeS($sql);

        if ($count || !count($emails)) {
            return count($emails);
        }

        $conf = Configuration::getMultiple(array(
            'PS_FOLLOW_UP_AMOUNT_3',
            'PS_FOLLOW_UP_DAYS_3'
        ));
        foreach ($emails as $email) {
            $voucher = $this->createDiscount(
                3,
                (float)$conf['PS_FOLLOW_UP_AMOUNT_3'],
                (int)$email['id_customer'],
                strftime(
                    '%Y-%m-%d',
                    strtotime('+' . (int)$conf['PS_FOLLOW_UP_DAYS_3'] . ' day')
                ),
                $this->trans(
                    'You are one of our best customers!',
                    array(),
                    'Modules.Reminder.Admin'
                )
            );
            if (false !== $voucher) {
                $template_vars = array(
                    '{email}' => $email['email'],
                    '{lastname}' => $email['lastname'],
                    '{firstname}' => $email['firstname'],
                    '{amount}' => $conf['PS_FOLLOW_UP_AMOUNT_3'],
                    '{days}' => $conf['PS_FOLLOW_UP_DAYS_3'],
                    '{voucher_num}' => $voucher->code
                );
                Mail::Send(
                    (int)$email['id_lang'],
                    'followup_3',
                    Mail::l(
                        'You are one of our best customers',
                        (int)$email['id_lang']
                    ),
                    $template_vars,
                    $email['email'],
                    $email['firstname'] . ' ' . $email['lastname'],
                    null,
                    null,
                    null,
                    null,
                    dirname(__FILE__) . '/mails/'
                );
                $this->logEmail(
                    3,
                    (int)$voucher->id,
                    (int)$email['id_customer'],
                    (int)$email['id_cart']
                );
            }
        }
    }

    /* For all customers with no orders since more than x days */

    /**
     * badCustomer send mails to all customers with no orders since more than x days,
     * with at least one valid order in history
     *
     * @param boolean $count if set to true, will return number of customer (default : false, will send mails, no return value)
     *
     * @return void
     */
    private function badCustomer($count = false)
    {
        $email_logs = $this->getLogsEmail(4);
        $sql = '
			SELECT o.id_lang,
			       c.id_cart,
			       cu.id_customer,
			       cu.id_shop,
			       cu.firstname,
			       cu.lastname,
			       cu.email,
			       (SELECT COUNT(o.id_order)
			       FROM '._DB_PREFIX_.'orders o
			       WHERE o.id_customer = cu.id_customer
			       AND o.valid = 1) nb_orders
			FROM '._DB_PREFIX_.'customer cu
			LEFT JOIN '._DB_PREFIX_.'orders o
			ON (o.id_customer = cu.id_customer)
			LEFT JOIN '._DB_PREFIX_.'cart c
			ON (c.id_cart = o.id_cart)
            WHERE cu.id_customer
            NOT IN (SELECT o.id_customer
                    FROM '._DB_PREFIX_.'orders o
                    WHERE DATE_SUB(CURDATE(),INTERVAL ' .
                    (int)Configuration::get('PS_FOLLOW_UP_DAYS_THRESHOLD_4') .
                    ' DAY) <= o.date_add)
            AND cu.is_guest = 0 ';

        $sql .= Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'cu');

        if (!empty($email_logs)) {
            $sql .= ' AND cu.id_customer NOT IN ('.join(',', $email_logs).') ';
        }

        $sql .= ' GROUP BY cu.id_customer HAVING nb_orders >= 1';

        $emails = Db::getInstance()->executeS($sql);

        if ($count || !count($emails)) {
            return count($emails);
        }

        $conf = Configuration::getMultiple(array(
            'PS_FOLLOW_UP_AMOUNT_4',
            'PS_FOLLOW_UP_DAYS_4'
        ));
        foreach ($emails as $email) {
            $voucher = $this->createDiscount(
                4,
                (float)$conf['PS_FOLLOW_UP_AMOUNT_4'],
                (int)$email['id_customer'],
                strftime(
                    '%Y-%m-%d',
                    strtotime('+' . (int)$conf['PS_FOLLOW_UP_DAYS_4'] . ' day')
                ),
                $this->trans(
                    'We miss you!',
                    array(),
                    'Modules.Reminder.Admin'
                )
            );
            if (false !== $voucher) {
                $template_vars = array(
                    '{email}' => $email['email'],
                    '{lastname}' => $email['lastname'],
                    '{firstname}' => $email['firstname'],
                    '{amount}' => $conf['PS_FOLLOW_UP_AMOUNT_4'],
                    '{days}' => $conf['PS_FOLLOW_UP_DAYS_4'],
                    '{days_threshold}' => (int)Configuration::get(
                        'PS_FOLLOW_UP_DAYS_THRESHOLD_4'
                    ),
                    '{voucher_num}' => $voucher->code,
                );
                Mail::Send(
                    (int)$email['id_lang'],
                    'followup_4',
                    Mail::l(
                        'We miss you',
                        (int)$email['id_lang']
                    ),
                    $template_vars,
                    $email['email'],
                    $email['firstname'] . ' ' . $email['lastname'],
                    null,
                    null,
                    null,
                    null,
                    dirname(__FILE__) . '/mails/'
                );
                $this->logEmail(
                    4,
                    (int)$voucher->id,
                    (int)$email['id_customer'],
                    (int)$email['id_cart']
                );
            }
        }
    }

    private function createDiscount(
        $id_email_type,
        $amount,
        $id_customer,
        $date_validity,
        $description
    ) {
        $cart_rule = new CartRule();
        $cart_rule->reduction_percent = (float)$amount;
        $cart_rule->id_customer = (int)$id_customer;
        $cart_rule->date_to = $date_validity;
        $cart_rule->date_from = date('Y-m-d H:i:s');
        $cart_rule->quantity = 1;
        $cart_rule->quantity_per_user = 1;
        $cart_rule->cart_rule_restriction = 1;
        $cart_rule->minimum_amount = 0;

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $cart_rule->name[(int)$language['id_lang']] = $description;
        }

        $code = 'FLW-' . (int)$id_email_type . '-' . Tools::strtoupper(
                Tools::passwdGen(10)
            );
        $cart_rule->code = $code;
        $cart_rule->active = 1;
        if (!$cart_rule->add()) {
            return false;
        }

        return $cart_rule;
    }

    public function cronTask()
    {
        Context::getContext()->link = new Link(); //when this is call by cron context is not init
        $conf = Configuration::getMultiple(array(
            'PS_FOLLOW_UP_ENABLE_1',
            'PS_FOLLOW_UP_ENABLE_2',
            'PS_FOLLOW_UP_ENABLE_3',
            'PS_FOLLOW_UP_ENABLE_4',
            'PS_FOLLOW_UP_CLEAN_DB'
        ));

        if ($conf['PS_FOLLOW_UP_ENABLE_1']) {
            $this->cancelledCart();
        }
        if ($conf['PS_FOLLOW_UP_ENABLE_2']) {
            $this->reOrder();
        }
        if ($conf['PS_FOLLOW_UP_ENABLE_3']) {
            $this->bestCustomer();
        }
        if ($conf['PS_FOLLOW_UP_ENABLE_4']) {
            $this->badCustomer();
        }

        /* Clean-up database by deleting all outdated discounts */
        if (1 == $conf['PS_FOLLOW_UP_CLEAN_DB']) {
            $outdated_discounts = Db::getInstance()->executeS(
                'SELECT id_cart_rule
                 FROM '._DB_PREFIX_.'cart_rule
                 WHERE date_to < NOW()
                 AND code LIKE "FLW-%"');
            foreach ($outdated_discounts as $outdated_discount) {
                $cart_rule = new CartRule(
                    (int)$outdated_discount['id_cart_rule']
                );
                if (Validate::isLoadedObject($cart_rule)) {
                    $cart_rule->delete();
                }
            }
        }
    }

    public function renderStats()
    {
        $subQuery = 'SELECT COUNT(l2.id_cart_rule)
                   FROM '._DB_PREFIX_.'log_email l2
                   LEFT JOIN '._DB_PREFIX_.'order_cart_rule ocr
                   ON (ocr.id_cart_rule = l2.id_cart_rule)
                   LEFT JOIN '._DB_PREFIX_.'orders o ON (o.id_order = ocr.id_order)
                   WHERE l2.id_email_type = l.id_email_type
                   AND date(l2.date_add) = date(l.date_add)
                   AND ocr.id_order IS NOT NULL
                   AND o.valid = 1';

        $query = 'SELECT DATE_FORMAT(l.date_add, \'%Y-%m-%d\') date_stat,
			       l.id_email_type,
			       COUNT(l.id_log_email) nb,
                   (' . $subQuery . ') nb_used
			FROM '._DB_PREFIX_.'log_email l
			WHERE l.date_add >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
			GROUP BY DATE_FORMAT(l.date_add, \'%Y-%m-%d\'), l.id_email_type';

        $stats = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        $stats_array = array();
        foreach ($stats as $stat) {
            $stats_array[$stat['date_stat']][$stat['id_email_type']]['nb'] = (int)$stat['nb'];
            $stats_array[$stat['date_stat']][$stat['id_email_type']]['nb_used'] = (int)$stat['nb_used'];
        }

        foreach ($stats_array as $date_stat => $array) {
            $rates = array();
            for ($i = 1; $i != 5; $i++) {
                if (isset($stats_array[$date_stat][$i]['nb'])
                    && isset($stats_array[$date_stat][$i]['nb_used'])
                    && 0 < $stats_array[$date_stat][$i]['nb_used']) {
                    $rates[$i] = number_format(
                        ($stats_array[$date_stat][$i]['nb_used']
                            / $stats_array[$date_stat][$i]['nb']) * 100,
                        2,
                        '.',
                        ''
                    );
                }
            }
            for ($i = 1; $i != 5; $i++) {
                $stats_array[$date_stat][$i]['nb'] =
                    isset($stats_array[$date_stat][$i]['nb']) ?
                        (int)$stats_array[$date_stat][$i]['nb'] :
                        0;
                $stats_array[$date_stat][$i]['nb_used'] =
                    isset($stats_array[$date_stat][$i]['nb_used']) ?
                        (int)$stats_array[$date_stat][$i]['nb_used'] :
                        0;
                $stats_array[$date_stat][$i]['rate'] =
                    isset($rates[$i]) ?
                        '<b>'.$rates[$i].'</b>' :
                        '0.00';
            }
            ksort($stats_array[$date_stat]);
        }

        $this->context->smarty->assign(array('stats_array' => $stats_array));

        return $this->display(__FILE__, 'stats.tpl');
    }

    public function renderForm()
    {
        $currency = new Currency(
            (int)Configuration::get('PS_CURRENCY_DEFAULT')
        );

        $n1 = $this->cancelledCart(true);
        $n2 = $this->reOrder(true);
        $n3 = $this->bestCustomer(true);
        $n4 = $this->badCustomer(true);

        $cron_info = '';
        if (Shop::getContext() === Shop::CONTEXT_SHOP) {
            $cron_info = $this->trans(
                'Define the settings and paste the following URL in the crontab, or call it manually on a daily basis:',
                    array(),
                    'Modules.Reminder.Admin'
                ).'<br /><b>' . $this->context->shop->getBaseURL() .
                'modules/followup/cron.php?secure_key=' .
                Configuration::get('PS_REMINDER_SECURE_KEY') . '</b></p>';
        }

        $fields_form_1 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans(
                        'Information',
                        array(),
                        'Modules.Reminder.Admin'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'description' => $this->trans(
                    'Four kinds of e-mail alerts are available in order to stay in touch with your customers!',
                        array(),
                        'Modules.Reminder.Admin'
                    ) . '<br />' . $cron_info,
            )
        );

        $fields_form_2 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans(
                        'Cancelled carts',
                        array(),
                        'Modules.Reminder.Admin'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'description' => $this->trans(
                    'For each cancelled cart (with no order), generate a discount and send it to the customer.',
                    array(),
                    'Modules.Reminder.Admin'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->trans(
                            'Enable',
                            array(),
                            'Admin.Actions'
                        ),
                        'name' => 'PS_FOLLOW_UP_ENABLE_1',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans(
                                    'Enabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans(
                                    'Disabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Discount amount',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_AMOUNT_1',
                        'suffix' => '%',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Discount validity',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_DAYS_1',
                        'suffix' => $this->trans(
                            'day(s)',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                    array(
                        'type' => 'desc',
                        'name' => '',
                        'text' => $this->trans(
                            'The next process will send %d e-mail(s).',
                            array('%d' => $n1),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans(
                        'Save',
                        array(),
                        'Admin.Actions'
                    ),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        $fields_form_3 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans(
                        'Re-order',
                        array(),
                        'Modules.Reminder.Admin'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'description' => $this->trans(
                    'For each validated order, generate a discount and send it to the customer.',
                    array(),
                    'Modules.Reminder.Admin'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->trans(
                            'Enable',
                            array(),
                            'Admin.Actions'
                        ),
                        'name' => 'PS_FOLLOW_UP_ENABLE_2',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans(
                                    'Enabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans(
                                    'Disabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Discount amount',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_AMOUNT_2',
                        'suffix' => '%',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Discount validity',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_DAYS_2',
                        'suffix' => $this->trans(
                            'day(s)',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                    array(
                        'type' => 'desc',
                        'name' => '',
                        'text' => $this->trans(
                            'Next process will send: %d e-mail(s)',
                            array('%d' => $n2),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans(
                        'Save',
                        array(),
                        'Admin.Actions'
                    ),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        $fields_form_4 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans(
                        'Best customers',
                        array(),
                        'Modules.Reminder.Admin'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'description' => $this->trans(
                    'For each customer raising a threshold, generate a discount and send it to the customer.',
                    array(),
                    'Modules.Reminder.Admin'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->trans(
                            'Enable',
                            array(),
                            'Admin.Actions'
                        ),
                        'name' => 'PS_FOLLOW_UP_ENABLE_3',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans(
                                    'Enabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans(
                                    'Disabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Discount amount',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_AMOUNT_3',
                        'suffix' => '%',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Threshold',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_THRESHOLD_3',
                        'suffix' => $currency->sign,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Discount validity',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_DAYS_3',
                        'suffix' => $this->trans(
                            'day(s)',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                    array(
                        'type' => 'desc',
                        'name' => '',
                        'text' => $this->trans(
                            'Next process will send: %d e-mail(s)',
                            array('%d' => $n3),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans(
                        'Save',
                        array(),
                        'Admin.Actions'
                    ),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        $fields_form_5 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans(
                        'Bad customers',
                        array(),
                        'Modules.Reminder.Admin'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'description' => $this->trans(
                    'For each customer who has already placed at least one order and with no orders since a given duration, generate a discount and send it to the customer.',
                    array(),
                    'Modules.Reminder.Admin'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->trans(
                            'Enable',
                            array(),
                            'Admin.Actions'
                        ),
                        'name' => 'PS_FOLLOW_UP_ENABLE_4',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans(
                                    'Enabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans(
                                    'Disabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Discount amount',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_AMOUNT_4',
                        'suffix' => '%',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Since x days',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_DAYS_THRESHOLD_4',
                        'suffix' => $this->trans(
                            'day(s)',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans(
                            'Discount validity',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_DAYS_4',
                        'suffix' => $this->trans(
                            'day(s)',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                    array(
                        'type' => 'desc',
                        'name' => '',
                        'text' => $this->trans(
                            'Next process will send: %d e-mail(s)',
                            array('%d' => $n4),
                            'Modules.Reminder.Admin'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans(
                        'Save',
                        array(),
                        'Admin.Actions'
                    ),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        $fields_form_6 = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans(
                        'General',
                        array(),
                        'Modules.Reminder.Admin'
                    ),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'is_bool' => true, //retro-compat
                        'label' => $this->trans(
                            'Delete outdated discounts during each launch to clean database',
                            array(),
                            'Modules.Reminder.Admin'
                        ),
                        'name' => 'PS_FOLLOW_UP_CLEAN_DB',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans(
                                    'Enabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans(
                                    'Disabled',
                                    array(),
                                    'Admin.Global'
                                ),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans(
                        'Save',
                        array(),
                        'Admin.Actions'
                    ),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang =
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
                Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') :
                0;
        $helper->identifier = $this->identifier;
        $helper->override_folder = '/';
        $helper->module = $this;
        $helper->submit_action = 'submitFollowUp';
        $helper->currentIndex = $this->context->link->getAdminLink(
                'AdminModules',
                false) .
            '&configure=' . $this->name .
            '&tab_module=' . $this->tab .
            '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array(
            $fields_form_1,
            $fields_form_2,
            $fields_form_3,
            $fields_form_4,
            $fields_form_5,
            $fields_form_6
        ));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'PS_FOLLOW_UP_ENABLE_1' =>
                Tools::getValue(
                    'PS_FOLLOW_UP_ENABLE_1',
                    Configuration::get('PS_FOLLOW_UP_ENABLE_1')
                ),
            'PS_FOLLOW_UP_DAYS_1' => Tools::getValue(
                'PS_FOLLOW_UP_DAYS_1',
                Configuration::get('PS_FOLLOW_UP_DAYS_1')
            ),
            'PS_FOLLOW_UP_AMOUNT_1' => Tools::getValue(
                'PS_FOLLOW_UP_AMOUNT_1',
                Configuration::get('PS_FOLLOW_UP_AMOUNT_1')
            ),
            'PS_FOLLOW_UP_ENABLE_2' => Tools::getValue(
                'PS_FOLLOW_UP_ENABLE_2',
                Configuration::get('PS_FOLLOW_UP_ENABLE_2')
            ),
            'PS_FOLLOW_UP_DAYS_2' => Tools::getValue(
                'PS_FOLLOW_UP_DAYS_2',
                Configuration::get('PS_FOLLOW_UP_DAYS_2')
            ),
            'PS_FOLLOW_UP_AMOUNT_2' => Tools::getValue(
                'PS_FOLLOW_UP_AMOUNT_2',
                Configuration::get('PS_FOLLOW_UP_AMOUNT_2')
            ),
            'PS_FOLLOW_UP_THRESHOLD_3' => Tools::getValue(
                'PS_FOLLOW_UP_THRESHOLD_3',
                Configuration::get('PS_FOLLOW_UP_THRESHOLD_3')
            ),
            'PS_FOLLOW_UP_DAYS_3' => Tools::getValue(
                'PS_FOLLOW_UP_DAYS_3',
                Configuration::get('PS_FOLLOW_UP_DAYS_3')
            ),
            'PS_FOLLOW_UP_ENABLE_3' => Tools::getValue(
                'PS_FOLLOW_UP_ENABLE_3',
                Configuration::get('PS_FOLLOW_UP_ENABLE_3')
            ),
            'PS_FOLLOW_UP_AMOUNT_3' => Tools::getValue(
                'PS_FOLLOW_UP_AMOUNT_3',
                Configuration::get('PS_FOLLOW_UP_AMOUNT_3')
            ),
            'PS_FOLLOW_UP_AMOUNT_4' => Tools::getValue(
                'PS_FOLLOW_UP_AMOUNT_4',
                Configuration::get('PS_FOLLOW_UP_AMOUNT_4')
            ),
            'PS_FOLLOW_UP_ENABLE_4' => Tools::getValue(
                'PS_FOLLOW_UP_ENABLE_4',
                Configuration::get('PS_FOLLOW_UP_ENABLE_4')
            ),
            'PS_FOLLOW_UP_DAYS_THRESHOLD_4' => Tools::getValue(
                'PS_FOLLOW_UP_DAYS_THRESHOLD_4',
                Configuration::get('PS_FOLLOW_UP_DAYS_THRESHOLD_4')
            ),
            'PS_FOLLOW_UP_DAYS_4' => Tools::getValue(
                'PS_FOLLOW_UP_DAYS_4',
                Configuration::get('PS_FOLLOW_UP_DAYS_4')
            ),
            'PS_FOLLOW_UP_CLEAN_DB' => Tools::getValue(
                'PS_FOLLOW_UP_CLEAN_DB',
                Configuration::get('PS_FOLLOW_UP_CLEAN_DB')
            ),
        );
    }
}
