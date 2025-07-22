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

class AdminManageWarehousesController extends ModuleAdminController
{
    public function __construct()
    {
        require_once(dirname(__FILE__).'/../../classes/WarehouseStock.php');

        $this->bootstrap = true;
        $this->table = 'warehouse';
        $this->className = 'StoreHouse';
        $this->deleted = true;
        $this->lang = true;
        //$this->multishop_context = Shop::CONTEXT_ALL;
        $this->multishop_context = false;

        parent::__construct();

        $this->fields_list = array(
            'id_warehouse' => array(
                'title' => $this->trans('ID', array(), 'Admin.Global'),
                'width' => 50,
            ),
            'reference' => array(
                'title' => $this->trans('Reference', array(), 'Admin.Global'),
            ),
            'warehouse_name' => array(
                'title' => $this->trans('Name', array(), 'Admin.Global'),
                'filter_key' => 'b!name',
            ),
            'delivery_time' => array(
                'title' => $this->l('Delivery time'),
            ),
            /*'management_type' => array(
                'title' => $this->l('Management type'),
            ),*/
            'employee' => array(
                'title' => $this->l('Manager'),
                'filter_key' => 'employee',
                'havingFilter' => true
            ),
            'location' => array(
                'title' => $this->l('Location'),
                'orderby' => false,
                'filter' => false,
                'search' => false,
            ),
            'contact' => array(
                'title' => $this->l('Phone Number'),
                'orderby' => false,
                'filter' => false,
                'search' => false,
            ),
            'active' => array(
                'title' => $this->l('Active aside Frontoffice'),
                'active' => 'status',
                'align' => 'center',
                'filter_key' => 'a!active',
                'type' => 'bool',
                'orderby' => false,
            )
        );
    }

    public function renderList()
    {
        // removes links on rows
        $this->list_no_link = true;
        // adds actions on rows
        $this->addRowAction('edit');
        $this->addRowAction('view');
        $this->addRowAction('delete');

        // query: select
        $this->_select = '
            reference,
            b.name as warehouse_name,
            management_type,
            CONCAT(e.lastname, \' \', e.firstname) as employee,
            ad.phone as contact,
            CONCAT(ad.city, \' - \', c.iso_code) as location';

        // query: join
        $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'employee` e ON (e.id_employee = a.id_employee)
            LEFT JOIN `'._DB_PREFIX_.'address` ad ON (ad.id_address = a.id_address)
            LEFT JOIN `'._DB_PREFIX_.'country` c ON (c.id_country = ad.id_country)';
        $this->_use_found_rows = false;

        // display help informations
        $this->displayInformation('- '.$this->l('This interface allows you to manage your warehouses.'));
        $this->displayInformation('- '.$this->l('You have to check its associated carriers.'));
        $this->displayInformation(
			'- '.$this->l('Before adding stock in your warehouses, you should assign products to their warehouses.')
		);
        $this->displayInformation(
			'- '.$this->l('Be careful! Products from different warehouses will need to be shipped in different packages.')
		);
        $this->displayInformation(
			'- '.$this->l('Click on view button to see detailed informations for each warehouse, such as the number of products, the quantities stored, etc.')
		);
        $this->displayInformation(
			'- '.$this->l('By disabling a warehouse, it will simply be deactivated on the Frontoffice side but the functionalities on the backoffice side will remain active, so you can manage it internally.')
		);
        $this->displayInformation(
			'- '.$this->l('Be aware: if all warehouses are disabled, products using A.S.M will not be available for order.')
		);

        return parent::renderList();
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
        /*// foreach item in the list to render
        $nb_items = count($this->_list);
        for ($i = 0; $i < $nb_items; ++$i) {
            // depending on the management type, translates the management type
            $item = &$this->_list[$i];
            switch ($item['management_type']) {// management type can be either WA/FIFO/LIFO
                case 'WA':
                    $item['management_type'] = $this->l('WA: Weighted Average');
                    break;

                case 'FIFO':
                    $item['management_type'] = $this->l('FIFO: First In, First Out');
                    break;

                case 'LIFO':
                    $item['management_type'] = $this->l('LIFO: Last In, First Out');
                    break;
            }
        }*/
    }

    public function getRequiredFieldsAddress()
    {
        $class_name = version_compare(_PS_VERSION_, '1.7.3', '>=') === true ? new CustomerAddress() : new Address();
        $tmp_addr = new $class_name();
        return $tmp_addr->getFieldsRequiredDatabase();
    }

    public function renderForm()
    {
        // Load current warehouse
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        // Gets the manager of the warehouse
        $query = new DbQuery();
        $query->select('id_employee, CONCAT(lastname," ",firstname) as name');
        $query->from('employee');
        $query->where('active = 1');
        $employees_array = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        // Sets the title of the toolbar
        if (Tools::isSubmit('add'.$this->table)) {
            $this->toolbar_title = $this->l('Create a warehouse');
        } else {
            $this->toolbar_title = $this->l('Warehouse management');
        }

        $res = $this->getRequiredFieldsAddress();
        $required_fields = array();
        foreach ($res as $row) {
            $required_fields[(int)$row['id_required_field']] = $row['field_name'];
        }

        // Sets the fields of the form
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Warehouse information'),
                'icon' => 'icon-pencil'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'id_address',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Reference', array(), 'Admin.Global'),
                    'name' => 'reference',
                    'maxlength' => 32,
                    'required' => true,
                    'hint' => $this->l('Reference for this warehouse.'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Name', array(), 'Admin.Global'),
                    'name' => 'name',
                    'lang' => true,
                    'maxlength' => 45,
                    'required' => true,
                    'hint' => array(
                        $this->l('Name of this warehouse.'),
                        $this->l('Invalid characters:').' !&lt;&gt;,;?=+()@#"�{}_$%:',
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Delivery time'),
                    'name' => 'delivery_time',
                    'lang' => true,
                    'maxlength' => 200,
                    'hint' => array(
                        $this->l('Delivery time of this warehouse.'),
                        $this->l('Invalid characters:').' !&lt;&gt;,;?=+()@#"�{}_$%:',
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Phone'),
                    'name' => 'phone',
                    'maxlength' => 16,
                    'hint' => $this->l('Phone number for this warehouse.'),
                    'required' => in_array('phone', $required_fields)
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Mobile phone'),
                    'name' => 'phone_mobile',
                    'required' => in_array('phone_mobile', $required_fields),
                    'maxlength' => 16,
                    'hint' => $this->l('Mobile phone number for this supplier.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Address'),
                    'name' => 'address',
                    'maxlength' => 128,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Address').' (2)',
                    'name' => 'address2',
                    'maxlength' => 128,
                    'hint' => $this->l('Complementary address (optional).'),
                    'required' => in_array('address2', $required_fields)
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Zip/postal code'),
                    'name' => 'postcode',
                    'maxlength' => 12,
                    'required' => in_array('postcode', $required_fields)
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('City', array(), 'Admin.Global'),
                    'name' => 'city',
                    'maxlength' => 32,
                    'required' => true,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->trans('Country', array(), 'Admin.Global'),
                    'name' => 'id_country',
                    'required' => true,
                    'default_value' => (int)$this->context->country->id,
                    'options' => array(
                        'query' => Country::getCountries($this->context->language->id, false),
                        'id' => 'id_country',
                        'name' => 'name',
                    ),
                    'hint' => $this->l('Country of location of the warehouse.')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('State'),
                    'name' => 'id_state',
                    'required' => true,
                    'options' => array(
                        'query' => array(),
                        'id' => 'id_state',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Manager'),
                    'name' => 'id_employee',
                    'required' => true,
                    'options' => array(
                        'query' => $employees_array,
                        'id' => 'id_employee',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'swap',
                    'label' => $this->l('Carriers'),
                    'name' => 'ids_carriers',
                    'required' => false,
                    'multiple' => true,
                    'options' => array(
                        'query' => Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::ALL_CARRIERS),
                        'id' => 'id_reference',
                        'name' => 'name'
                    ),
                    'hint' => array(
                        $this->l('Associated carriers.'),
                        $this->l('You can choose which carriers can ship orders from particular warehouses.'),
                        $this->l('If you do not select any carrier, all the carriers will be able to ship from this warehouse.'),
                    ),
                    'desc' => $this->l('If no carrier is selected, all the carriers will be allowed to ship from this warehouse. Use CTRL+Click to select more than one carrier.'),
                ),
            ),
        );

        /*// Shop Association
        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
                'disable_shared' => Shop::SHARE_STOCK
            );
        }

        // if it is still possible to change currency valuation and management type
        if (Tools::isSubmit('addwarehouse') || Tools::isSubmit('submitAddwarehouse')) {
            // adds input management type
            $this->fields_form['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Management type'),
                'hint' => $this->l('Inventory valuation method. Be careful! You won\'t be able to change this value later!'),
                'name' => 'management_type',
                'required' => true,
                'options' => array(
                    'query' => array(
                        array(
                            'id' => 'WA',
                            'name' => $this->l('Weighted Average')
                        ),
                        array(
                            'id' => 'FIFO',
                            'name' => $this->l('First In, First Out')
                        ),
                        array(
                            'id' => 'LIFO',
                            'name' => $this->l('Last In, First Out')
                        ),
                    ),
                    'id' => 'id',
                    'name' => 'name'
                ),
            );

            // adds input valuation currency
            $this->fields_form['input'][] = array(
                'type' => 'select',
                'label' => $this->l('Stock valuation currency'),
                'hint' => $this->l('Be careful! You won\'t be able to change this value later!'),
                'name' => 'id_currency',
                'required' => true,
                'options' => array(
                    'query' => Currency::getCurrencies(),
                    'id' => 'id_currency',
                    'name' => 'name'
                )
            );
        } else {
            // else hide input
            $this->fields_form['input'][] = array(
                'type' => 'hidden',
                'name' => 'management_type'
            );

            $this->fields_form['input'][] = array(
                'type' => 'hidden',
                'name' => 'id_currency'
            );
        }*/

        $this->fields_form['input'][] = array(
            'type' => 'hidden',
            'name' => 'management_type'
        );
        $this->fields_form['input'][] = array(
            'type' => 'hidden',
            'name' => 'id_currency'
        );

        $this->fields_form['submit'] = array(
            'title' => $this->trans('Save', array(), 'Admin.Actions'),
        );

        $address = null;
        // Load current address for this warehouse - if possible
        if ($obj->id_address > 0) {
            $address = new Address($obj->id_address);
        }

        // Load current carriers associated with this warehouse
        $carriers = $obj->getCarriers(true);

        // if an address is available : force specific fields values
        if ($address != null) {
            $this->fields_value = array(
                'id_address' => $address->id,
                'phone' => $address->phone,
                'phone_mobile' => $address->phone_mobile,
                'address' => $address->address1,
                'address2' => $address->address2,
                'postcode' => $address->postcode,
                'city' => $address->city,
                'id_country' => $address->id_country,
                'id_state' => $address->id_state,
            );
        } else {// Load default country
            $this->fields_value = array(
                'id_address' => 0,
                'id_country' => Configuration::get('PS_COUNTRY_DEFAULT')
            );
        }

        // Load carriers
        $this->fields_value['ids_carriers'] = $carriers;
        $this->fields_value['management_type'] = 'WA';

        if (!Validate::isLoadedObject($obj)) {
            $this->fields_value['id_currency'] = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        }

        return parent::renderForm();
    }

    public function renderView()
    {
        // get necessary objects
        $id_warehouse = (int)Tools::getValue('id_warehouse');
        $warehouse = new StoreHouse($id_warehouse, $this->context->employee->id_lang);
        $employee = new Employee($warehouse->id_employee);
        $currency = new Currency($warehouse->id_currency);
        $address = new Address($warehouse->id_address);

        $this->toolbar_title = $warehouse->name;

        // check objects
        if (!Validate::isLoadedObject($warehouse) ||
            !Validate::isLoadedObject($employee) ||
            !Validate::isLoadedObject($currency) ||
            !Validate::isLoadedObject($address)) {
            return parent::renderView();
        }

        // assign to our view
        $this->tpl_view_vars = array(
            'warehouse' => $warehouse,
            'employee' => $employee,
            'currency' => $currency,
            'address' => $address,
            // Gets the number of products in the current warehouse
            'warehouse_num_products' => $warehouse->getNumberOfProducts(),
            'warehouse_quantities' => $warehouse->getQuantitiesofProducts(),
            //'warehouse_value' => Tools::displayPrice(Tools::ps_round($warehouse->getStockValue(), 2), $currency),
        );
        $this->base_tpl_view = 'view.tpl';

        return parent::renderView();
    }

    /**
     * Called once $object is set.
     * Used to process the associations with address/carriers
     * @see AdminController::afterAdd()
     *
     * @param Warehouse $object
     *
     * @return bool
     */
    protected function afterAdd($object)
    {
        // Process Warehouses Priorities INCREASE
        $warehouses_priority = Configuration::get('WKWAREHOUSE_PRIORITY');
        if (!empty($warehouses_priority)) {
            $warehouses_priority .= ',';
        }
        $warehouses_priority .= (int)$object->id;
        Configuration::updateValue('WKWAREHOUSE_PRIORITY', $warehouses_priority);

        // Process Warehouses Priorities DECREASE
        $warehouses_priority = Configuration::get('WKWAREHOUSE_PRIORITY_DECREASE');
        if (!empty($warehouses_priority)) {
            $warehouses_priority .= ',';
        }
        $warehouses_priority .= (int)$object->id;
        Configuration::updateValue('WKWAREHOUSE_PRIORITY_DECREASE', $warehouses_priority);

        // Handles address association
        $address = new Address($object->id_address);
        if (Validate::isLoadedObject($address)) {
            $address->id_warehouse = (int)$object->id;
            $address->save();
        }

        // Handles carriers associations
        $ids_carriers_selected = Tools::getValue('ids_carriers_selected');
        if (Tools::isSubmit('ids_carriers_selected') && !empty($ids_carriers_selected)) {
            $object->setCarriers($ids_carriers_selected);
        } else {
            $object->setCarriers(Tools::getValue('ids_carriers_available'));
        }

        return true;
    }

    protected function afterUpdate($object)
    {
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            if (!$this->loadObject(true)) {
                return;
            }
			$id_address = (int)Tools::getValue('id_address');
			$address = new Address($id_address);
			if (Validate::isLoadedObject($address)) {
				// handles address association
				$address = new Address($object->id_address);
				if (Validate::isLoadedObject($address)) {
					$address->id_warehouse = (int)$object->id;
					$address->save();
				}
			}
		}
		return true;
    }

    public function processUpdate()
    {
        // Load object
        if (!($object = $this->loadObject(true))) {
            return;
        }

        $this->updateAddress();
        // Handle carriers associations
        $ids_carriers_selected = Tools::getValue('ids_carriers_selected');
        if (Tools::isSubmit('ids_carriers_selected') && !empty($ids_carriers_selected)) {
            $object->setCarriers($ids_carriers_selected);
        } else {
            $object->setCarriers(Tools::getValue('ids_carriers_available'));
        }

        return parent::processUpdate();
    }

    public function processAdd()
    {
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            if (!$this->loadObject(true)) {
                return;
            }
            $this->updateAddress();
            // hack for enable the possibility to update a warehouse without recreate new id
            $this->deleted = false;

            return parent::processAdd();
        }
    }

    protected function updateAddress()
    {
		$create_address = false;
        // Update / Create address if it does not exist
        if (Tools::isSubmit('id_address') && (int)Tools::getValue('id_address') > 0) {
            $address = new Address((int)Tools::getValue('id_address')); // update address
			if (!Validate::isLoadedObject($address)) {
				$address = new Address(); // create address
				$create_address = true;
			}
        } else {
            $address = new Address(); // create address
			$create_address = true;
        }
        // Set the address
        $address->alias = pSQL(Tools::getValue('reference', null));
        $address->lastname = 'warehouse'; // skip problem with numeric characters in warehouse name
        $address->firstname = 'warehouse'; // skip problem with numeric characters in warehouse name
        $address->address1 = pSQL(Tools::getValue('address', null));
        $address->address2 = pSQL(Tools::getValue('address2', null));
        $address->postcode = pSQL(Tools::getValue('postcode', null));
        $address->phone = pSQL(Tools::getValue('phone', null));
        $address->id_country = (int)Tools::getValue('id_country', null);
        $address->id_state = (int)Tools::getValue('id_state', null);
        $address->city = pSQL(Tools::getValue('city', null));

        // To avoid errors validation of required fields
        $res = $this->getRequiredFieldsAddress();
        if (count($res)) {
            foreach ($res as $required_field) {
                if ($required_field['field_name'] == 'dni') {
                    $address->dni = '12345678Z';
                } else {
                    $address->{$required_field['field_name']} = 'warehouse';
                }
            }
        }
        if (Country::isNeedDniByCountryId($address->id_country) && !Tools::getValue('dni')) {
            $address->dni = '12345678Z';
        }

        if (!($country = new Country($address->id_country, Configuration::get('PS_LANG_DEFAULT'))) || !Validate::isLoadedObject($country)) {
            $this->errors[] = $this->l('Country is invalid');
        }

        $contains_state = isset($country) && is_object($country) ? (int)$country->contains_states: 0;
        $id_state = isset($address) && is_object($address) ? (int)$address->id_state: 0;
        if ($contains_state && !$id_state) {
            $this->errors[] = $this->l('This country requires you to choose a State.');
        }

        // Validate address
        $validation = $address->validateController();

        // Check address validity
        if (count($validation) > 0) {
            // if not valid
            foreach ($validation as $item) {
                $this->errors[] = $item;
            }
            $this->errors[] = $this->l('The address is not correct. Please make sure all of the required fields are completed.');
        } else {
            // valid
            if (!$create_address) {
                $address->update();
            } else {
                $address->save();
                $_POST['id_address'] = $address->id;
            }
        }
    }

    public function processDelete()
    {
        if (Tools::isSubmit('delete'.$this->table)) {
            // check if the warehouse exists and can be deleted
            if (!($obj = $this->loadObject(true))) {
                return;
            } elseif ($obj->getQuantitiesOfProducts() > 0) { // not possible : products
                $this->errors[] = $this->l('It is not possible to delete a warehouse when there are products in it.');
            /*} elseif (Delivery::warehouseHasPendingOrders($obj->id) && class_exists('Delivery')) { // not possible : supply orders
                $this->errors[] = $this->l('It is not possible to delete a Warehouse if it has pending supply orders.');*/
            } else {// else, it can be deleted
                // set the address of the warehouse as deleted
                $address = new Address($obj->id_address);
                $address->deleted = 1;
                $address->save();

                // remove associations with carriers/products location
                $obj->setCarriers(array());
                $obj->resetProductsLocations();
                // remove stocks traces
                WarehouseStock::deleteWarehouseStock(null, null, $obj->id);

                // Warehouses increase priority
                $ids_warehouses = explode(',', Configuration::get('WKWAREHOUSE_PRIORITY'));
                if (in_array($obj->id, $ids_warehouses)) {
                    unset($ids_warehouses[array_search($obj->id, $ids_warehouses)]);
                }
                Configuration::updateValue('WKWAREHOUSE_PRIORITY', implode(',', $ids_warehouses));
                // Warehouses decrease priority
                $ids_warehouses = explode(',', Configuration::get('WKWAREHOUSE_PRIORITY_DECREASE'));
                if (in_array($obj->id, $ids_warehouses)) {
                    unset($ids_warehouses[array_search($obj->id, $ids_warehouses)]);
                }
                Configuration::updateValue('WKWAREHOUSE_PRIORITY_DECREASE', implode(',', $ids_warehouses));

                return parent::processDelete();
            }
        }
    }

    protected function updateAssoShop($id_object)
    {
        parent::updateAssoShop($id_object);
        if (!$this->loadObject(true)) {
            return;
        }
    }

    public function initContent()
    {
        if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management before using this feature.');
            return false;
        }
        $this->tpl_list_vars['has_bulk_actions'] = false;
        parent::initContent();
    }

    public function initProcess()
    {
        if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management before using this feature.');
            return false;
        }
        parent::initProcess();
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_warehouse'] = array(
                'href' => self::$currentIndex.'&addwarehouse&token='.$this->token,
                'desc' => $this->l('Add', null, null, false),
                'icon' => 'process-icon-new'
            );
        } else {
            $this->page_header_toolbar_btn['back_to_list'] = array(
                'href' => $this->context->link->getAdminLink('AdminManageWarehouses'),
                'desc' => $this->l('Back to list', null, null, false),
                'icon' => 'process-icon-back'
            );
        }
        $name = $this->module->name;
        $url_config = $this->context->link->getAdminLink('AdminModules').'&configure='.$name
        .'&tab_module='.$this->module->tab.'&module_name='.$name;
        $this->page_header_toolbar_btn['config_url'] = array(
            'href' => $url_config,
            'desc' => $this->l('Configuration', null, null, false),
            'icon' => 'process-icon-configure'
        );
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminWkwarehousesdash'),
            'desc' => $this->l('Dashboard', null, null, false),
            'icon' => 'process-icon-back'
        );
        parent::initPageHeaderToolbar();
    }

    /*
    * Method Translation Override For PS 1.7
    */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
   			$translated = $this->translator->trans($string, [], 'Modules.Wkwarehouses.Adminmanagewarehousescontroller');
            if ($translated !== $string) {
                return $translated;
            }
        }
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            $class = Tools::substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
