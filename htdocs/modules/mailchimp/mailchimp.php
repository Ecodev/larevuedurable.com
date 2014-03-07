<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

// Security
if (!defined('_PS_VERSION_'))
  exit;

require_once(_PS_MODULE_DIR_."/mailchimp/class/MCAPI.class.php");

class MailChimp extends Module
{
  private $_postErrors = array();
  private $_postValidations = array();
  private $_variables = array();

  public function __construct()
  {
    $this->name = 'mailchimp';
    $this->tab = 'administration';
    $this->version = '1.0';
    $this->author = 'Prestashop';

    parent::__construct();

    $this->displayName = $this->l('MailChimp');
		/*Description that will appear on the page where all the modules are displayed*/
    $this->description = $this->l('MailChimp helps you design email newsletters, share them on social networks, integrate with services you already use, and track your results. It\'s like your own personal publishing platform.');

    /** Backward compatibility 1.4 and 1.5 */
    require(_PS_MODULE_DIR_.'/mailchimp/backward_compatibility/backward.php');
  }

	/** 
	 * register hook createAccount : 
	 * in order to receive all the new customer
	 * and to register them in a mailchimp list
	 * @return 
	 */
  public function install()
  {
    if (!(parent::install() && $this->registerHook('createAccount') && $this->registerHook('newOrder')))
      return false;
    return true;
  }

	/** 
   * Uninstall
	 * we don't need to keep the API key
	 * 
	 * @return 
	 */
  public function uninstall()
  {
    $var_conf = array('MAILCHIMP_API_KEY', 'MAILCHIMP_SYNCHRO_ID_LIST');
    foreach ($var_conf as $key)
		{
			if (!Configuration::deleteByName($key))
				return false;	
		}
    if (!(parent::uninstall() && $this->unregisterHook('createAccount')))
      return false;
    return true;
  }


	/** 
	 * getContent
	 * we need this function to help the customer to configure the module
	 * 
	 * @return 
	 */
  public function getContent()
  {
    $html = '';
    $this->context->smarty->assign(array(
				'mailchimp_configure_url' => './index.php?tab=AdminModules
&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'
&tab_module='.$this->tab.'&module_name='.$this->name,
				'PS_IMG' => _PS_IMG_,
				'BASE_URI' => __PS_BASE_URI__,
				'ad' => dirname($_SERVER['PHP_SELF']),
				'PS_CSS' => _THEME_CSS_DIR_.'global.css',
				'token' => Tools::getAdminTokenLite('AdminModules'),
				'id_lang' => $this->context->language->id,
				'lang_name' => $this->context->language->iso_code
			));

    if (!empty($_POST) && Tools::isSubmit('submitSave'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
			{
				$this->_postProcess();
				$html .= $this->_displayValidation();
			}
			else
				$html .= $this->_displayError();
		}

    if (isset($_GET['id_tab']))
      $this->context->smarty->assign('mailchimp_id_tab', (int)$_GET['id_tab']);

    $html .= $this->_displayConfiguration();
    return $html;
  }

  private function _displayConfiguration()
  {
    /*api key form*/
    $this->context->smarty->assign('api_key',
			Configuration::get('MAILCHIMP_API_KEY'));
    $this->context->smarty->assign('api_dc',
			Configuration::get('MAILCHIMP_API_DC'));
    $this->context->smarty->assign('campany_name',
			Configuration::get('PS_SHOP_NAME'));
    $this->context->smarty->assign('from_email',
			Configuration::get('PS_SHOP_EMAIL'));

    /*get info on the merchant's list*/
    $mailChimp_api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
    $this->context->smarty->assign('lists', $mailChimp_api->lists());

    /*synchronize data*/
    $this->context->smarty->assign('synchro_list',
			Configuration::get('MAILCHIMP_SYNCHRO_ID_LIST'));

    return $this->display(__FILE__, 'tpl/configuration.tpl');
  }

	/** 
	 * postValidation
	 * this methode is called to validate POST values
	 * 
	 * @return 
	 */
  private function _postValidation()
  {
    if (Tools::getValue('action_form') == 'api')
      $this->_postApiValidation();
  }

  private function _postApiValidation()
  {
    if (Tools::getValue('mailchimp_api_key') == '')
      $this->_postErrors[] = $this->l('Your MailChimp Api key is required.');
    else
		{
			$api_key = Tools::safeOutput(Tools::getValue('mailchimp_api_key'));
			$mailChimp_api = new MCAPI($api_key);
			if ($mailChimp_api->ping() != "Everything's Chimpy!")
				$this->_postErrors[] = $this->l('Invalid Mailchimp API Key: ').$api_key;
			else
				$this->_postValidation[] = $this->l(
					'Your MailChimp API Key has been registered.'
				);
		}
  }

	/** 
	 * postProcess
	 * method called to update the database
	 * 
	 * @return 
	 */
  private function _postProcess()
  {
    if (Tools::getValue('action_form') == 'api')
      $this->_postApiProcess();
  }

  private function _postApiProcess()
  {
    Configuration::updateValue('MAILCHIMP_API_KEY',
			Tools::safeOutput(Tools::getValue('mailchimp_api_key')));
    Configuration::updateValue('MAILCHIMP_API_DC',
			Tools::safeOutput(substr(Tools::getValue('mailchimp_api_key'),
					strpos(Tools::getValue('mailchimp_api_key'), '-') + 1)));
  }

	/** 
	 * displayError & displayValidation
	 * display what postValidation return
	 * 
	 * @return 
	 */
  private function _displayError()
  {
    $this->context->smarty->assign('mailChimp_errors', $this->_postErrors);
  }

  private function _displayValidation()
  {
    $this->context->smarty->assign('mailChimp_validations',
			$this->_postValidation);
  }

	/** 
	 * hookCreateAccount
	 * each time a new customer has registered, 
	 it will automatically be registered to a Mailchimp List
	 * @param params 
	 * 
	 * @return 
	 */
  public function hookCreateAccount($params)
  {
    if (!$this->active || !Configuration::get('MAILCHIMP_SYNCHRO_ID_LIST'))
      return ;

    $newCustomer = $params['newCustomer'];
    if (!Validate::isLoadedObject($newCustomer))
      return false;
    $postVars = $params['_POST'];

    if (empty($postVars)
			|| !isset($postVars['newsletter'])
			|| empty($postVars['newsletter'])
			|| !$postVars['newsletter'])
      return false;

    $api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
    $merge = array('FNAME' => $newCustomer->firstname,
						 'LNAME' => $newCustomer->lastname,
						 'OPTIN_IP' => $newCustomer->ip_registration_newsletter,
						 'OPTIN_TIME' => $newCustomer->newsletter_date_add,
		);

    $retval = $api->listSubscribe(
			Configuration::get('MAILCHIMP_SYNCHRO_ID_LIST'),
			$newCustomer->email,
			$merge,
			'html',
			false,
			true,
			false,
			true);

    return $retval;
  }

	/** 
	 * Each a new order is created, we import the order informations to Mailchimp
	 *  in order to be used for segmentation.
	 * @param params 
	 * 
	 * @return 
	 */
	public function hookNewOrder($params)
	{
		if (!$this->active || !Configuration::get('MAILCHIMP_API_KEY'))
      return ;

		$order = $params['order'];
		$customer = $params['customer'];

		$cart = $params['cart'];

		$products = $cart->getProducts();
		$i = 0;
		foreach ($products as $key => $value)
		{
			$product[$i]['product_id'] = $value['id_product'];
			$product[$i]['product_name'] = $value['name'];
			$product[$i]['category_id'] = $value['id_category_default'];
			$product[$i]['category_name'] = $value['category'];
			$product[$i]['qty'] = $value['cart_quantity'];
			$product[$i]['cost'] = $value['price'];
			$i++;
		}

		$api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
		$res = $api->ecommOrderAdd(array(
						 'id' => $order->id,
						 'email' => $customer->email,
						 'total' => $order->total_paid,
						 'order_date' => $order->date_add,
						 'shipping' => $order->total_shipping,
						 'tax' => $order->total_products_wt,
						 'store_id' => 1,
						 'store_name' => Configuration::get('PS_SHOP_NAME'),
						 'items' => $product
					 )
		);
		if ($api->errorCode)
			echo $api->errorMessage;
	}

  public function hookUpdateCustomer($params)
  {
    if (!$this->active
			|| !Configuration::get('MAILCHIMP_SYNCHRO_ID_LIST'))
      return ;

    $customer = $params['object'];
    if (!Validate::isLoadedObject($customer))
      return false;
    if (!isset($customer->newsletter))
      return false;

    $api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
    if ($customer->newsletter == 0)
			$retval= $api->listUnsubscribe(
				Configuration::get('MAILCHIMP_SYNCHRO_ID_LIST'),
				$customer->email
			);
    else
		{
			$merge = array('FNAME' => $customer->firstname,
							 'LNAME' => $customer->lastname,
							 'OPTIN_IP' => $customer->ip_registration_newsletter,
							 'OPTIN_TIME' => $customer->newsletter_date_add,
			);
			$retval	= $api->listSubscribe(
				Configuration::get('MAILCHIMP_SYNCHRO_ID_LIST'),
				$customer->email,
				$merge,
				'html',
				false,
				true,
				false,
				true);
		}
    return $retval;
  }
}
?>