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

class NtsConfigProfilCountries extends ObjectModel
{
    /** @var int id_nts_config */
    public $id_nts_config;

    /** @var int id_profil */
    public $id_profil;

    /** @var string id_countries */
    public $id_countries;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'nts_config_profil_countries',
        'primary' => 'id_nts_config_profil_countries',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'id_nts_config' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'id_profil' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_countries' => [
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
     * Get the config profils countries limit for the given config
     *
     * @param int $id_nts_config The config you want the infos for
     * @param int $id_lang The profil name lang
     *
     * @return array The infos
     */
    public static function getConfigByIdConfig($id_nts_config, $id_lang)
    {
        $infos = Db::getInstance()->executeS('
            SELECT pl.`name`, pl.`id_profile`, IFNULL(ntcpc.`id_countries`, "") AS id_countries
            FROM `' . _DB_PREFIX_ . 'profile_lang` pl
            LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_profil_countries` ntcpc ON ntcpc.`id_profil` = pl.`id_profile` AND ntcpc.`id_nts_config` = ' . (int) $id_nts_config . '
            WHERE pl.`id_lang` = ' . (int) $id_lang . '
            AND pl.`id_profile` <> ' . (int) _PS_ADMIN_PROFILE_ . '
            GROUP BY pl.`id_profile`
            ORDER BY pl.`name`
        ');

        if (!is_array($infos)) {
            return [];
        }

        $data = [];

        foreach ($infos as $info) {
            if ($info['id_countries']) {
                $info['id_countries'] = json_decode($info['id_countries']);
            } else {
                $info['id_countries'] = [];
            }

            $data[$info['id_profile']] = $info;
        }

        return $data;
    }

    /**
     * Get the config profils countries limit for the given config
     *
     * @param int $id_nts_config The config you want the profils countries limit for
     *
     * @return array The config profils countries limit
     */
    public static function getProfilsCountriesLimitByIdConfig($id_nts_config)
    {
        $profils_countries_limit = Db::getInstance()->executeS('
            SELECT pc.`id_profil`, IFNULL(pc.`id_countries`, "") AS id_countries
            FROM `' . _DB_PREFIX_ . 'nts_config_profil_countries` pc
            WHERE `id_nts_config` = ' . (int) $id_nts_config . '
            ORDER BY pc.`id_profil`, id_countries
        ');

        if (!is_array($profils_countries_limit)) {
            return [];
        }

        return $profils_countries_limit;
    }

    /**
     * Get informations for the given profil and config
     *
     * @param int $id_profil The profil you want the infos of
     * @param int $id_nts_config The config you want the infos for
     *
     * @return array The countries limit infos
     */
    public static function getByIdProfil($id_profil, $id_nts_config)
    {
        $data = Db::getInstance()->getRow('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'nts_config_profil_countries`
            WHERE `id_profil` = ' . (int) $id_profil . '
            AND `id_nts_config` = ' . (int) $id_nts_config . '
        ');

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * Empty the table
     *
     * @return bool The result of the deletion
     */
    public static function deleteAll()
    {
        return Db::getInstance()->execute('
            TRUNCATE `' . _DB_PREFIX_ . 'nts_config_profil_countries`
        ');
    }
}
