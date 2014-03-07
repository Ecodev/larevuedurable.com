<?php

class OrderController extends OrderControllerCore
{


	/**
	 * Address step
	 */
//	protected function _assignAddress()
//	{
//		parent::_assignAddress();
//
//		if (Tools::getValue('multi-shipping'))
//			$this->context->cart->autosetProductAddress();
//
//		$this->context->smarty->assign('cart', $this->context->cart);
//
//	}
//
//	/**
//	 * Carrier step
//	 */
//	protected function _assignCarrier()
//	{
//		if (!isset($this->context->customer->id))
//			die(Tools::displayError('Fatal error: No customer'));
		// Assign carrier
//		parent::_assignCarrier();
		// Assign wrapping and TOS
//		$this->_assignWrappingAndTOS();
//
//		$this->context->smarty->assign(
//			array(
//				'is_guest' => (isset($this->context->customer->is_guest) ? $this->context->customer->is_guest : 0)
//			));
//	}

	/**
	 * Payment step
	 */
//	protected function _assignPayment()
//	{
//		global $orderTotal;
//
		// Redirect instead of displaying payment modules if any module are grefted on
//		Hook::exec('displayBeforePayment', array('module' => 'order.php?step=3'));
//
//		/* We may need to display an order summary */
//		$this->context->smarty->assign($this->context->cart->getSummaryDetails());
//		$this->context->smarty->assign(array(
//			'total_price' => (float)($orderTotal),
//			'taxes_enabled' => (int)(Configuration::get('PS_TAX'))
//		));
//		$this->context->cart->checkedTOS = '1';
//
//		parent::_assignPayment();
//	}
//	
	



//
//
///* Address step */
//protected function _assignAddress()
//{
//	  global $cookie; // ajout pour la Rustine
//	 
//	  parent::_assignAddress();
//	 
//	  /* ajout pour la Rustine */  
//	   $cart = Db::getInstance()->getRow('
//	   SELECT id_address_delivery
//	   FROM '._DB_PREFIX_.'cart
//	   WHERE id_cart = '.(int)$cookie->id_cart);
//	  
//	   $address=Db::getInstance()->getRow('
//	   SELECT id_country
//	   FROM '._DB_PREFIX_.'address
//	   WHERE id_address = '.(int)$cart['id_address_delivery']);
//	  
//	   switch ($address['id_country']) {  
//	                                case 8: // France
//	                                        $cookie->id_currency = 1;  //assigne la devise correspondant au pays
//	                                        Tools::setCurrency();
//	                                        break;
//	                                case 19: //Suisse
//	                                        $cookie->id_currency = 4; // et ainsi de suite pour chaque pays.
//	                                        Tools::setCurrency();
//	                                        break;                    
//	                        }
//	  /* Fin de l'ajout pour la Rustine */
//	 
//	  self::$smarty->assign('cart', self::$cart);
//	  if (self::$cookie->is_guest)
//	   Tools::redirect('order.php?step=2');
//	}
//	
//	
//	
///* Carrier step */
//protected function _assignCarrier()
//	{
//	 
//	  global $cookie; // ajout pour la Rustine
//	  global $defaultCountry;
//	 
//	  /* ajout pour la Rustine */  
//	   $cart = Db::getInstance()->getRow('
//	   SELECT id_address_delivery
//	   FROM '._DB_PREFIX_.'cart
//	   WHERE id_cart = '.(int)$cookie->id_cart);
//	  
//	   $address=Db::getInstance()->getRow('
//	   SELECT id_country
//	   FROM '._DB_PREFIX_.'address
//	   WHERE id_address = '.(int)$cart['id_address_delivery']);
//	  
//	   switch ($address['id_country']) {  
//	                                case 8: // France
//	                                        $cookie->id_currency = 1;  //assigne la devise correspondant au pays
//	                                        Tools::setCurrency();
//	                                        break;
//	                                case 19: //Suisse
//	                                        $cookie->id_currency = 4; // &#224; r&#233;p&#233;ter pour chaque pays.
//	                                        Tools::setCurrency();
//	                                        break;                    
//	                        }
//	  /* Fin de l'ajout pour la Rustine */
//	 
//	  if (isset(self::$cookie->id_customer))
//	   $customer = new Customer((int)(self::$cookie->id_customer));
//	  else
//	   die(Tools::displayError('Fatal error: No customer'));
	  // Assign carrier
//	  parent::_assignCarrier();
	  // Assign wrapping and TOS
//	  $this->_assignWrappingAndTOS();
//	  self::$smarty->assign('is_guest' ,(isset(self::$cookie->is_guest) ? self::$cookie->is_guest : 0));
//}
//
//





























}

?>