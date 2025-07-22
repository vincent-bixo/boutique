<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class DeliveryOptionsFinder extends DeliveryOptionsFinderCore
{
	
	
	
	
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function getDeliveryOptions()
    {
		$this->context = Context::getContext();
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') ||
			!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES') ||
			!class_exists('PrestaShop\PrestaShop\Adapter\Product\PriceFormatter') ||
			!$this->context->cart->isMultiAddressDelivery()) {
            return parent::getDeliveryOptions();
        }
        if (version_compare(_PS_VERSION_, '1.7.4', '<=')) {
			if (!class_exists('PrestaShop\PrestaShop\Adapter\ObjectPresenter')) {
				return parent::getDeliveryOptions();
			} else {
				$this->objectPresenter = new PrestaShop\PrestaShop\Adapter\ObjectPresenter();
			}
		} else {
			if (!class_exists('PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter')) {
				return parent::getDeliveryOptions();
			} else {
				$this->objectPresenter = new PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter();
			}
		}
		$this->priceFormatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
        $delivery_addresses = $this->context->cart->getAddressCollection();
        $delivery_option_list = $this->context->cart->getDeliveryOptionList();
        $include_taxes = !Product::getTaxCalculationMethod((int)$this->context->cart->id_customer) && (int)Configuration::get('PS_TAX');
        $display_taxes_label = (Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC'));
        $carriers_available = [];
		foreach ($delivery_addresses as $id_address_delivery => $address) {
			if (isset($delivery_option_list[$id_address_delivery])) {
				foreach ($delivery_option_list[$id_address_delivery] as $id_carriers_list => $carriers_list) {
					foreach ($carriers_list as $carriers) {
						if (is_array($carriers)) {
							foreach ($carriers as $carrier) {
								$carrier = array_merge($carrier, $this->objectPresenter->present($carrier['instance']));
								$delay = $carrier['delay'][$this->context->language->id];
								unset($carrier['instance'], $carrier['delay']);
								$carrier['delay'] = $delay;
								
								$free_shipping = false;
								if ($carriers_list['is_free']) {
									$free_shipping = true;
								} else {
									foreach ($this->context->cart->getCartRules() as $rule) {
										if ($rule['free_shipping'] && !$rule['carrier_restriction']) {
											$free_shipping = true;
											break;
										}
									}
								}
								
								if ($free_shipping) {
									$carrier['price'] = ($this->context->getTranslator())->trans(
										'Free',
										[],
										'Shop.Theme.Checkout'
									);
								} else {
									if ($include_taxes) {
										$carrier['price'] = $this->priceFormatter->format($carriers_list['total_price_with_tax']);
										if ($display_taxes_label) {
											$carrier['price'] = ($this->context->getTranslator())->trans(
												'%price% tax incl.',
												['%price%' => $carrier['price']],
												'Shop.Theme.Checkout'
											);
										}
									} else {
										$carrier['price'] = $this->priceFormatter->format($carriers_list['total_price_without_tax']);
										if ($display_taxes_label) {
											$carrier['price'] = ($this->context->getTranslator())->trans(
												'%price% tax excl.',
												['%price%' => $carrier['price']],
												'Shop.Theme.Checkout'
											);
										}
									}
								}
	
								if (count($carriers) > 1) {
									$carrier['label'] = $carrier['price'];
								} else {
									$carrier['label'] = $carrier['name'] . ' - ' . $carrier['delay'] . ' - ' . $carrier['price'];
								}
								$carrier['extraContent'] = '';
								if ($carrier['is_module']) {
									if ($moduleId = Module::getModuleIdByName($carrier['external_module_name'])) {
										$carrier['extraContent'] = Hook::exec('displayCarrierExtraContent', ['carrier' => $carrier], $moduleId);
									}
								}
	
								$carriers_available[$id_carriers_list] = $carrier;
							}
						}
					}
				}
			}
		}
        return $carriers_available;
    }
}
