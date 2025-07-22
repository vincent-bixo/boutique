<?php
/**
 * 2020 Wild Fortress, Lda
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 *  @author    Hélder Duarte <cossou@gmail.com>
 *  @copyright 2020 Wild Fortress, Lda
 *  @license   Proprietary and confidential
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class WebhookCustomer
{
    private $params;
    private $id_customer;

    /**
     * WebhookCustomer constructor.
     *
     * @param int $id_customer
     * @param array $params
     *
     * @throws Exception
     */
    public function __construct($id_customer, $params = [])
    {
        $this->id_customer = $id_customer;
        $this->params = $params;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function present()
    {
        if (!is_int($this->id_customer)) {
            return $this->params;
        }

        $customer = new Customer($this->id_customer);

        if (!is_a($customer, 'Customer')) {
            return $this->params;
        }

        $referrers = [];
        // Referrer class was removed in PrestaShop 8
        if (version_compare(_PS_VERSION_, '8.0.0.0', '<')) {
            $referrers = Referrer::getReferrers($customer->id);
        }

        return [
            'customer' => $this->getDetails($customer),
            'addresses' => $customer->getAddresses($customer->id_lang),
            'stats' => $customer->getStats(),
            'groups' => $customer->getGroups(),
            'last_emails' => $customer->getLastEmails(),
            'last_connections' => $customer->getLastConnections(),
            'referrers' => $referrers,
        ];
    }

    /**
     * @param Customer $customer
     *
     * @return array
     */
    private function getDetails(Customer $customer)
    {
        $customerArray = [];
        $gender = new Gender((int) $customer->id_gender, $customer->id_lang);

        $customerArray['id'] = $customer->id;
        $customerArray['id_shop'] = $customer->id_shop;
        $customerArray['id_shop_group'] = $customer->id_shop_group;
        $customerArray['note'] = $customer->note;
        $customerArray['id_gender'] = $customer->id_gender;
        $customerArray['id_default_group'] = $customer->id_default_group;
        $customerArray['id_lang'] = $customer->id_lang;
        $customerArray['lastname'] = $customer->lastname;
        $customerArray['firstname'] = $customer->firstname;
        $customerArray['email'] = $customer->email;
        $customerArray['passwd'] = $customer->passwd;
        $customerArray['last_passwd_gen'] = $customer->last_passwd_gen;
        $customerArray['secure_key'] = $customer->secure_key;
        $customerArray['birthday'] = $customer->birthday;
        $customerArray['newsletter'] = $customer->newsletter;
        $customerArray['ip_registration_newsletter'] = $customer->ip_registration_newsletter;
        $customerArray['newsletter_date_add'] = $customer->newsletter_date_add;
        $customerArray['optin'] = $customer->optin;
        $customerArray['website'] = $customer->website;
        $customerArray['company'] = $customer->company;
        $customerArray['siret'] = $customer->siret;
        $customerArray['ape'] = $customer->ape;
        $customerArray['outstanding_allow_amount'] = $customer->outstanding_allow_amount;
        $customerArray['show_public_prices'] = $customer->show_public_prices;
        $customerArray['id_risk'] = $customer->id_risk;
        $customerArray['max_payment_days'] = $customer->max_payment_days;
        $customerArray['active'] = $customer->active;
        $customerArray['is_guest'] = $customer->is_guest;
        $customerArray['deleted'] = $customer->deleted;
        $customerArray['date_add'] = $customer->date_add;
        $customerArray['date_upd'] = $customer->date_upd;
        $customerArray['years'] = $customer->years;
        $customerArray['days'] = $customer->days;
        $customerArray['months'] = $customer->months;
        $customerArray['geoloc_id_country'] = $customer->geoloc_id_country;
        $customerArray['geoloc_id_state'] = $customer->geoloc_id_state;
        $customerArray['geoloc_postcode'] = $customer->geoloc_postcode;
        $customerArray['logged'] = $customer->logged;
        $customerArray['id_guest'] = $customer->id_guest;
        $customerArray['groupBox'] = $customer->groupBox;
        $customerArray['id_shop_list'] = $customer->id_shop_list;
        $customerArray['force_id'] = $customer->force_id;

        $customerArray['gender'] = [
            'id' => $gender->id,
            'id_gender' => $gender->id_gender,
            'type' => $gender->type,
            'name' => $gender->name,
        ];

        return $customerArray;
    }
}
