<?php


	/*
	 * Creator   : WDXperience SARL : YM (121008)
	 * Copyright : All Right Reserved - Licence available for 1 shop
	 * Licence   : Prices and Conditions on http://www.wdxperience.ch/shop/
	 * Compat    : Prestashop v1.5
	 */


	class DatatransValidationModuleFrontController extends ModuleFrontController {

		public function postProcess() {


			$id_cart = Tools::getValue('dtscart');
			$cart =  new Cart($id_cart);


			//Check Address and if the module is activated
			if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
				Tools::redirect('index.php?controller=order&step=1');


			//Module is always available ?
			$authorized = false;

			foreach (Module::getPaymentModules() as $module)
				if ($module['name'] == 'datatrans') {
					$authorized = true;
					break;
				}

			if ( !$authorized)
				die($this->module->l('This payment method is not available.', 'validation'));


			//Check the customer
			$customer = new Customer($cart->id_customer);

			if (!Validate::isLoadedObject($customer))
				Tools::redirect('index.php?controller=order&step=1');


			//Goto order confirmation
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&key='.$customer->secure_key);
		}
	}
