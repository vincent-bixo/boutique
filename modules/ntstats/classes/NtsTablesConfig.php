<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class NtsTablesConfig extends ObjectModel
{
    /** @var string name */
    public $name;

    /** @var string config */
    public $config;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'nts_tables_config',
        'primary' => 'id_nts_tables_config',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'config' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    /**
     * Get table config infos
     *
     * @param string $name The table name
     *
     * @return array The table config infos
     */
    public static function getByName($name)
    {
        $data = Db::getInstance()->getRow('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'nts_tables_config`
            WHERE `name` = "' . pSQL($name) . '"
        ');

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }
}
