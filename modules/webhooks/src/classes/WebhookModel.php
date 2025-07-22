<?php
/**
 * 2020 Wild Fortress, Lda
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author    Hélder Duarte <cossou@gmail.com>
 * @copyright 2020 Wild Fortress, Lda
 * @license   Proprietary and confidential
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class WebhookModel extends ObjectModel
{
    public $id_webhook;
    public $url;
    public $hook;
    public $real_time;
    public $retries;
    public $active;
    public $date_add;

    protected $webserviceParameters = [
        'objectMethods' => ['add' => 'addWs'],
        'objectsNodeName' => 'webhooks',
        'objectNodeName' => 'webhook',
        'hidden_fields' => ['id_webhook'],
        'fields' => [
            'id' => [],
            'url' => [],
            'hook' => [],
            'real_time' => [],
            'retries' => [],
            'active' => [],
            'date_add' => [],
        ],
    ];

    public static $definition = [
        'table' => 'webhooks',
        'primary' => 'id_webhook',
        'multilang' => false,
        'fields' => [
            'id_webhook' => [
                'type' => self::TYPE_INT,
            ],
            'url' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isUrl',
                'required' => true,
            ],
            'hook' => [
                'type' => self::TYPE_STRING,
                'required' => true,
            ],
            'real_time' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'retries' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    public function addWs($autodate = true, $null_values = false)
    {
        $hooks = [
            'actionOrderHistoryAddAfter',
            'actionValidateOrder',
            'actionProductSave',
            'actionProductUpdate',
            'actionCustomerAccountAdd',
            'actionCustomerAccountUpdate',
            'actionObjectCustomerMessageAddAfter',
            'actionObjectAddressUpdateAfter',
            'actionPasswordRenew',
            'actionUpdateQuantity',
            'actionObjectStockAvailableUpdateAfter',
        ];

        $validHook = in_array($this->hook, $hooks);

        if (!$validHook) {
            WebserviceRequest::getInstance()->setError(
                400,
                $this->trans(
                    "Hook \"{$this->hook}\" is not valid. Valid hooks are " . join(', ', $hooks),
                    [],
                    'Admin.Notifications.Error'
                ),
                1
            );

            return false;
        }

        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            WebserviceRequest::getInstance()->setError(
                400,
                $this->trans(
                    "URL \"{$this->url}\" is not valid.",
                    [],
                    'Admin.Notifications.Error'
                ),
                2
            );

            return false;
        }

        return $this->add();
    }

    /**
     * @param int $id_webhook
     *
     * @return mixed
     */
    public static function getById($id_webhook)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . self::$definition['table'] . "`
            WHERE id_webhook = '" . (int) $id_webhook . "'";

        return Db::getInstance()->getRow($sql);
    }

    /**
     * @param $hook_name
     *
     * @return mixed
     */
    public static function getWebhooksByHook($hook_name)
    {
        $sql = 'SELECT `' . implode('`,`', array_keys(self::$definition['fields'])) . '`
            FROM `' . _DB_PREFIX_ . self::$definition['table'] . "`
            WHERE `hook` = '" . pSQL($hook_name) . "'";

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $webhook_id
     *
     * @return mixed
     */
    public static function changeWebhookStatus($webhook_id)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . "`
            SET active = 1 - active
            WHERE id_webhook = '" . (int) $webhook_id . "'";

        return Db::getInstance()->execute($sql);
    }

    /**
     * @param string $url
     * @param string $hook
     * @param int $real_time
     * @param int $retries
     * @param int $active
     *
     * @return mixed
     */
    public static function insertWebhook($url, $hook, $real_time, $retries, $active = 1)
    {
        return Db::getInstance()->insert(self::$definition['table'], [
            'url' => pSQL($url),
            'hook' => pSQL($hook),
            'real_time' => (int) $real_time,
            'retries' => (int) $retries,
            'active' => (int) $active,
            'date_add' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return int
     */
    public static function getWebhooksTotal()
    {
        $query = new DbQuery();
        $query->select('COUNT(*)')
            ->from(self::$definition['table']);

        return Db::getInstance()->getValue($query);
    }

    /**
     * @return mixed
     */
    public static function getWebhooks($page = 1, $pagination = 50)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::$definition['table'])
            ->orderBy(self::$definition['primary'] . ' DESC')
            ->limit($pagination, ($page - 1) * $pagination);

        return Db::getInstance()->ExecuteS($query);
    }

    /**
     * @param int $id_webhook
     */
    public static function deleteById($id_webhook)
    {
        Db::getInstance()->delete(
            self::$definition['table'],
            self::$definition['primary'] . ' = ' . (int) $id_webhook,
            1
        );
    }

    /**
     * @param $id_webhook
     * @param $url
     * @param $hook
     * @param $real_time
     * @param $retries
     *
     * @return mixed
     */
    public static function updateWebhook($id_webhook, $url, $hook, $real_time, $retries)
    {
        return Db::getInstance()->update(
            self::$definition['table'],
            [
                'url' => $url,
                'hook' => $hook,
                'real_time' => $real_time,
                'retries' => $retries,
            ],
            self::$definition['primary'] . ' = ' . (int) $id_webhook,
            1
        );
    }
}
