<?php

/*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class MegablockClass extends ObjectModel
{
	public $home_text;
	public $sidebar_text;
	public $footer_text;
	public $product_text;
	public $cart_text;
	public $banner_text;
	public $topcolumn_text;
	public $top_text;
	public $id_shop;

	public static $definition = array(
		'table' => 'megablock',
		'primary' => 'id_megablock',
		'multilang' => true,
		'fields' => array(
			'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'home_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'sidebar_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'footer_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'product_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'banner_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'topcolumn_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
			'top_text' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
		)
	);

	public static function getByIdShop($id_shop)
	{
		$id = Db::getInstance()->getValue('SELECT `id_megablock` FROM `'._DB_PREFIX_.'megablock` WHERE `id_shop` ='.(int)$id_shop);

		return new MegablockClass($id);
	}

	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST as $key => $value)
		{
			if (key_exists($key, $this) && $key != 'id_'.$this->table)
				$this->{$key} = $value;
		}

		/* Multilingual fields */
		if (count($this->fieldsValidateLang))
		{
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				foreach ($this->fieldsValidateLang as $field => $validation)
				{
					if (Tools::getIsset($field.'_'.(int)$language['id_lang']))
						$this->{$field}[(int)$language['id_lang']] = $_POST[$field.'_'.(int)$language['id_lang']];
				}
			}
		}
	}
}