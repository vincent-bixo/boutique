<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminWkwarehousestaskrunController extends ModuleAdminController
{
    public function __construct()
    {
        // Check security by token
        if (Tools::getValue('secure_key') != Configuration::get('WKWAREHOUSE_SECURE_KEY') ||
            !Module::isInstalled('wkwarehouses')) {
            exit;
        }
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', "0");

        require_once(dirname(__FILE__).'/../../classes/Warehouse.php');
        require_once(dirname(__FILE__).'/../../classes/WarehouseProductLocation.php');
        require_once(dirname(__FILE__).'/../../classes/WarehouseStock.php');
        require_once(dirname(__FILE__).'/../../classes/WorkshopAsm.php');

		if (!defined('_PS_ADMIN_DIR_')) {
			define('_PS_ADMIN_DIR_', _PS_ROOT_DIR_.'/admin/'); //getcwd()
		}
        parent::__construct();

        $this->postProcessGaps();
    }

    public function postProcessGaps()
    {
		$debug_mode = false;
		if ($debug_mode) {
			ini_set('display_errors', 'on');
			error_reporting(E_ALL | E_STRICT);
		}

		$connected = false;
		if (!WorkshopAsm::isAdminAuth()) {
			$super_admins = WorkshopAsm::getSuperAdminEmployeeByProfile(_PS_ADMIN_PROFILE_, true);
			if ($super_admins) {
				$admin_email = $super_admins[0]['email'];

				$this->context->employee = new Employee();
				$is_employee_loaded = $this->context->employee->getByEmail($admin_email);

				if ($is_employee_loaded) {
					$this->context->employee->remote_addr = ip2long(Tools::getRemoteAddr());
					// Update cookie
					$cookie = $this->context->cookie;
					$cookie->id_employee = $this->context->employee->id;
					$cookie->email = $this->context->employee->email;
					$cookie->profile = $this->context->employee->id_profile;
					$cookie->passwd = $this->context->employee->passwd;
					$cookie->remote_addr = $this->context->employee->remote_addr;
					$connected = true;
				}
			}
		} else {
			$connected = true;
		}

		// If Authentication Success
		if ($connected) {
			/*
			* Use fastcgi_finish_request function (in sendCallback function) that can be used instead of asynchrone queuing to do "background job"
			* This function flushes all response data to the client and finishes the request.
			* This allows for time consuming tasks to be performed without leaving the connection to the client open.
			*/
			if (!$debug_mode) {
				ignore_user_abort(true);
				set_time_limit(0);
				ob_start();
				echo 'Tasks run';
				header('Connection: close');
				header('Content-Length: '.ob_get_length());
				ob_end_flush();
				//ob_flush();
				flush();
				if (function_exists('fastcgi_finish_request')) {
					fastcgi_finish_request();
				}
			}
			/********************************************************************************************************/

			// Begin processing
			if (!$debug_mode) {
				ob_start();
			}
			if (Configuration::get('WKWAREHOUSE_WAY_FIX_QUANTITIES')) {
				WorkshopAsm::alignQuantities(false, false, false, Configuration::get('WKWAREHOUSE_WAY_FIX_QUANTITIES'));
			}
			if (!$debug_mode) {
				ob_end_clean();
			}
		}
        die();
    }
}
