<?php


	/*
	 * Creator   : WDXperience SARL : YM (130616)
	 * Copyright : All Right Reserved - Licence available for 1 shop
	 * Licence   : Prices and Conditions on http://www.wdxperience.ch/shop/
	 * Compat    : Prestashop v1.5
	 */


	if (!defined('_CAN_LOAD_FILES_'))
		exit;

	class datatrans extends PaymentModule {

		private $_html = '';
		private $_postErrors = array();

		public $merchantId;
		public $testOnly;
		public $sign;
		public $sign2;
		public $postback;
		public $usealias = 0;
		public $limit = 0;

		public $post_production_url = 'https://payment.datatrans.biz/upp/jsp/upStart.jsp';
		public $post_test_url = 'https://pilot.datatrans.biz/upp/jsp/upStart.jsp';


		function __construct() {

			$this->name = 'datatrans';
			$this->tab = 'payments_gateways';
			$this->author = 'WDXperience';
			$this->version = "1.5.4";
			$this->release = "130616";
			$this->module_key = "797765edba98c94a8407b94944881517";

			$config = Configuration::getMultiple(array('DATATRANS_PSP_ID', 'DATATRANS_PSP_TEST',
													   'DATATRANS_PSP_SIGN', 'DATATRANS_PSP_SIGN2',
													   'DATATRANS_PSP_USEALIAS', 'DATATRANS_PSP_LIMIT'));

			if (isset($config['DATATRANS_PSP_ID'])) $this->merchantId = $config['DATATRANS_PSP_ID'];
			if (isset($config['DATATRANS_PSP_TEST'])) $this->testOnly = $config['DATATRANS_PSP_TEST'];
			if (isset($config['DATATRANS_PSP_SIGN'])) $this->sign = $config['DATATRANS_PSP_SIGN'];
			if (isset($config['DATATRANS_PSP_SIGN2'])) $this->sign2 = $config['DATATRANS_PSP_SIGN2'];

			$this->usealias = isset($config['DATATRANS_PSP_USEALIAS']) ? $config['DATATRANS_PSP_USEALIAS'] : 0;
			$this->limit = isset($config['DATATRANS_PSP_LIMIT']) ? (float)$config['DATATRANS_PSP_LIMIT'] : 0;


			/* The parent construct is required for translations */
			parent::__construct();

			$this->displayName = $this->l('Datatrans');
			$this->description = $this->l('Accept payments through Datatrans Payment Service Provider.');
			$this->mypath =  (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/';
			$this->postback = $this->mypath.'postsale.php';

			$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
			if (empty($this->merchantId) OR empty($this->sign) OR empty($this->sign2))
				$this->warning = $this->l('Account owner and details must be configured in order to use this module correctly');

			//when this is call by cron or post-sale context is not init
			if (empty(Context::getContext()->link))
				Context::getContext()->link = new Link();
		}


		public function path(){
			return $this->_path;
		}


		public function install() {

			if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('orderConfirmation')) {
				return false;
			}

			//Create alias table
			if (!Db::getInstance()->Execute('
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'datatrans_alias` (
				  `id_alias` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_customer` int(10) unsigned NOT NULL,
				  `alias` varchar(50) NOT NULL,
				  PRIMARY KEY (`id_alias`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')){
		 		return false;
			}

			if (Shop::isFeatureActive())
				Shop::setContext(Shop::CONTEXT_ALL);

			return true;
		}


		public function uninstall() {

			/**
			 * When the module is uninstalled, I keep Alias table.
			 */
			if (Configuration::deleteByName('DATATRANS_PSP_ID') &&
				Configuration::deleteByName('DATATRANS_PSP_TEST') &&
				Configuration::deleteByName('DATATRANS_PSP_SIGN') &&
				Configuration::deleteByName('DATATRANS_PSP_SIGN2') &&
				Configuration::deleteByName('DATATRANS_PSP_USEALIAS') &&
				Configuration::deleteByName('DATATRANS_PSP_LIMIT') &&
				parent::uninstall()){
				return true;
			}
			return false;
		}


		private function _postValidation() {
			if (isset($_POST['btnSubmit'])) {

				if (empty($_POST['execenv'])) $this->_postErrors[] = $this->l('Execution Environment is required.');
				elseif (empty($_POST['merchantId'])) $this->_postErrors[] = $this->l('Merchand ID is required.');
				elseif (empty($_POST['sign'])) $this->_postErrors[] = $this->l('HMAC Key (sign) is required.');
				elseif (empty($_POST['sign2'])) $this->_postErrors[] = $this->l('HMAC Key (sign2) is required.');

			}
			return true;
		}


		private function _postProcess() {
			if (isset($_POST['btnSubmit'])) {

				Configuration::updateValue('DATATRANS_PSP_ID', $_POST['merchantId']);
				Configuration::updateValue('DATATRANS_PSP_TEST', $_POST['execenv']);
				Configuration::updateValue('DATATRANS_PSP_SIGN', $_POST['sign']);
				Configuration::updateValue('DATATRANS_PSP_SIGN2', $_POST['sign2']);
				Configuration::updateValue('DATATRANS_PSP_USEALIAS', (empty($_POST['usealias']) ? '0' : '1'));
				Configuration::updateValue('DATATRANS_PSP_LIMIT', (float)$_POST['limit']);

				$this->_html .= '<div class="conf confirm">'.$this->l('Settings updated').'</div>';
			}
		}


		private function _displayDatatransPSP() {

			if (strpos(_PS_VERSION_, "1.5") === false) {
				$this->_html .= '<h1 style="color: red; padding-bottom: 20px;">'.$this->l('This module is only compatible with Prestashop v1.5').'</h1>';
			}

			$this->_html .= '<img src="'.$this->mypath.'img/logo_dts.gif" style="float:right; margin-left:15px;">'.
			'<span style="font-weight:bold;">'.$this->l('This module allows you to accept payments through Datatrans Payment Service Provider.').'</span><br />'.
			'&raquo; '.$this->l('Backoffice Datatrans').' : <a href="https://payment.datatrans.biz">https://payment.datatrans.biz</a><br />'.
			'<a href="http://wdxperience.ch/wiki/" target="_blank">&raquo; '.$this->l('Howto install and configure this module?').'</a><br /><br /><br />';

		}


		private function _displayForm() {
			$this->_html .=
			'
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset style="margin-bottom: 10px;">
				<legend><img src="../img/t/AdminManufacturers.gif" alt="" />'.$this->l('System Parameters').'</legend>
					<table border="0" cellpadding="0" cellspacing="0" id="form">
						<tr>
							<td style="height: 30px;">'.$this->l('Execution Environment').'</td>
							<td>
								<input type="radio" value="test" name="execenv"'.(Tools::getValue('execenv', $this->testOnly) == "test" ? ' checked="checked"' : '').' /> test
								<input type="radio" value="prod" name="execenv"'.(Tools::getValue('execenv', $this->testOnly) == "prod" ? ' checked="checked"' : '').' /> production
							</td>
						</tr>
						<tr>
							<td style="width: 200px; height: 30px;">'.$this->l('Merchand ID').'</td>
							<td><input type="text" name="merchantId" value="'.htmlentities(Tools::getValue('merchantId', $this->merchantId), ENT_COMPAT, 'UTF-8').'" style="width: 140px;" maxlength="20" /></td>
						</tr>
						<tr>
							<td style="height: 30px;">'.$this->l('HMAC Key (sign)').'</td>
							<td><input type="text" name="sign" value="'.htmlentities(Tools::getValue('sign', $this->sign), ENT_COMPAT, 'UTF-8').'" style="width: 650px;" /></td>
						</tr>
						<tr>
							<td style="height: 30px;">'.$this->l('HMAC Key (sign2)').'</td>
							<td><input type="text" name="sign2" value="'.htmlentities(Tools::getValue('sign2', $this->sign2), ENT_COMPAT, 'UTF-8').'" style="width: 650px;" /></td>
						</tr>
						<tr>
							<td style="height: 30px;">'.$this->l('Use ALIAS option').'</td>
							<td><input type="checkbox" name="usealias" value="1" '.(Configuration::get('DATATRANS_PSP_USEALIAS') == '1' ? ' checked="checked"' : '').' />
							<span style="color: #888;">'.$this->l('Before use this option, please, contact Datatrans support to activate it.').'</span></td>
						</tr>
						<tr>
							<td style="height: 30px;">'.$this->l('Payment Limit').'</td>
							<td><input type="text" name="limit" value="'.(float)Tools::getValue('limit', $this->limit).'" style="width: 100px;" />
							<span style="color: #888;">'.$this->l('0 = unlimited').'</span>
							</td>
						</tr>
						<tr>
							<td style="height: 30px;">'.$this->l('URL Post').'</td>
							<td><input type="text" name="posturl" value="'.$this->postback.'" style="width: 650px;" readonly="readonly" /></td>
						</tr>
						<tr>
							<td style="height: 55px;"></td>
							<td><input class="button" name="btnSubmit" value="'.$this->l('Update settings').'" type="submit" /></td>
						</tr>
					</table>
				</fieldset>
			</form>
			<p style="color: #555;">
				Developped by <a href="http://www.wdxperience.ch" target="_blank">WDXperience Sàrl</a> (1 licence = 1 shop) - version '.$this->version.'
				('.$this->release.') - compatible Prestashop v1.5
			</p>';
		}


		public function getContent() {

			$this->_html = '<h2>'.$this->displayName.'</h2>';

			if (!empty($_POST)) {
				$this->_postValidation();
				if (!sizeof($this->_postErrors)) {
					$this->_postProcess();
				}else{
					foreach ($this->_postErrors AS $err) {
						$this->_html .= '<div class="alert error">'. $err .'</div>';
					}
				}
			}else{
				$this->_html .= '<br />';
			}

			$this->_displayDatatransPSP();
			$this->_displayForm();

			return $this->_html;
		}


		public function hookPayment($params) {

			if ( !$this->active)
				return ;


			/* Check amount limit */
			$cartamount = $params['cart']->getOrderTotal(true, Cart::BOTH);
			if ($this->limit > 0 && $this->limit < $cartamount)
				return;


			$address = new Address((int)$params['cart']->id_address_invoice);
			$customer = new Customer((int)$params['cart']->id_customer);
			$cart_currency = new Currency((int)$params['cart']->id_currency);
			$lang =  new Language((int)$params['cart']->id_lang);


			/*AMOUNT and CURRENCY*/
			$amount = number_format($params['cart']->getOrderTotal(true, Cart::BOTH), 2, ".", "") * 100;


			/*LANGUAGE*/
			$autlang = array("de", "en", "fr", "it", "es", "el", "no", "da");
			$lng = in_array($lang->iso_code, $autlang) ? $lang->iso_code : 'en';


			/*INSERT VALUES IN PARAMETERS ARRAY*/
			$ps_params['merchantId'] = $this->merchantId;
			$ps_params['refno'] = date("dHis").(int)$params['cart']->id;
			$ps_params['dtscart'] = (int)$params['cart']->id;
			$ps_params['dtssecu'] = $params['cart']->secure_key;
			$ps_params['dtsshop'] = (int)$params['cart']->id_shop;
			$ps_params['amount'] = $amount;
			$ps_params['currency'] = $cart_currency->iso_code;
			$ps_params['language'] = $lng;

			$ps_params['uppCustomerDetails'] = "yes";
			$cn = trim(substr($customer->firstname." ".$customer->lastname, 0, 40));
			if (! empty($cn)){
				$ps_params['uppCustomerName'] = $cn;
			}
			$cn = trim(substr($customer->firstname, 0, 40));
			if (! empty($cn)){
				$ps_params['uppCustomerFirstName'] = $cn;
			}
			$cn = trim(substr($customer->lastname, 0, 40));
			if (! empty($cn)){
				$ps_params['uppCustomerLastName'] = $cn;
			}

			$email = trim(substr($customer->email, 0, 40));
			if (! empty($email)){
				$ps_params['uppCustomerEmail'] = $email;
			}
			$ownerzip = trim(substr($address->postcode, 0, 10));
			if (! empty($ownerzip)){
				$ps_params['uppCustomerZipCode'] = $ownerzip;
			}
			$owneradd = trim(substr($address->address1, 0, 40));
			if (! empty($owneradd)){
				$ps_params['uppCustomerStreet'] = $owneradd;
			}
			$ownercity = trim(substr($address->city, 0, 40));
			if (! empty($ownercity)){
				$ps_params['uppCustomerCity'] = $ownercity;
			}

			$ps_params['successUrl'] = Context::getContext()->link->getModuleLink('datatrans', 'validation');
			$ps_params['cancelUrl'] = Context::getContext()->link->getModuleLink('datatrans', 'cancel');
			$ps_params['errorUrl'] = Context::getContext()->link->getModuleLink('datatrans', 'error');

			$ps_params['reqtype'] = "CAA";


			/*SIGN*/
			$passphrase = trim(Configuration::get('DATATRANS_PSP_SIGN'));
			$ps_params['sign'] = $this->signValue($passphrase, $ps_params['merchantId'], $ps_params['amount'], $ps_params['currency'], $ps_params['refno']);


			/*ALIAS - don't work with integrated mode*/
			if ($this->usealias == '1') {

				//Get the stored alias
				$alias = '';
				$sql = "SELECT alias FROM "._DB_PREFIX_."datatrans_alias WHERE id_customer = ".(int)$params['cart']->id_customer;
				$lines = Db::getInstance()->ExecuteS($sql);
				foreach ($lines as $line) {
					$alias = $line['alias'];
				}
				if ( ! empty($alias)) {
					$ps_params['aliasCC'] = $alias;
				}
				$ps_params['useAlias'] = "yes";
			}


			/*TEST ENVIRONMENT*/
			if ($this->testOnly == 'test') {
				$this->context->smarty->assign("psp_url", $this->post_test_url);
				$ps_params['testOnly'] = "yes";
			}else{
				$this->context->smarty->assign("psp_url", $this->post_production_url);
				$ps_params['testOnly'] = "no";
			}


			/*BUILD FORM PARAMETERS*/
			$form_params = array();
			$i = 0;
			foreach ($ps_params as $name => $value) {
				if (! empty($value)){
					$form_params[$i]['name'] = $name;
					$form_params[$i]['value'] = $value;
					$i++;
				}
			}


			/**
			 * Text to define ALIAS option usage
			 */
			if ($this->usealias == '1') {
				$this->context->smarty->assign("usealias", $this->l('ALIAS option is activated.'));
			}

			$this->context->smarty->assign("parametres", $form_params);
			$this->context->smarty->assign("this_path",  $this->_path);

			return $this->display(__FILE__, 'payment.tpl');
		}


		public function hookOrderConfirmation($params) {

			if ( !$this->active)
				return;

			if ($params['objOrder']->module != $this->name)
				return;


			if ($params['objOrder']->valid){
				$this->smarty->assign(array(
						'status' => 'ok',
						'title', $this->displayName
				));
			}else{
				$this->smarty->assign('status', 'failed');
			}

			return $this->display(__FILE__, 'order_confirmation.tpl');
		}


		public function validate($id_cart, $id_order_state, $amount, $message = '', $secure_key, $shop_id) {

			if (isset($this->pcc)){

				$this->pcc->transaction_id = Tools::getValue('PAYID');
				$this->pcc->card_number = Tools::getValue('CARDNO');
				$this->pcc->card_brand = Tools::getValue('BRAND');
				$this->pcc->card_expiration = Tools::getValue('ED');
				$this->pcc->card_holder = Tools::getValue('CN');
			}

			$this->validateOrder($id_cart, $id_order_state,  $amount, $this->displayName, $message, NULL, NULL, true, pSQL($secure_key), new Shop((int)$shop_id));
		}


		function signValue($key, $merchId, $amount, $ccy, $idno) {
			$str = $merchId.$amount.$ccy.$idno;
			$key2 = $this->hexstr($key);
			return $this->hmac($key2, $str);
		}


		function hexstr($hex) {
			//translate byte array to hex string
			$string = "";
			for ($i = 0 ; $i < strlen($hex)-1 ; $i += 2)
				$string .= chr(hexdec($hex[$i].$hex[$i+1]));
			return $string;
		}


		function hmac($key, $data) {

			// RFC 2104 HMAC implementation for php.
			// Creates an md5 HMAC.
			// Eliminates the need to install mhash to compute a HMAC

			$b = 64; // byte length for md5
			if (strlen($key) > $b) {
				$key = pack("H*",md5($key));
			}
			$key  = str_pad($key, $b, chr(0x00));
			$ipad = str_pad('', $b, chr(0x36));
			$opad = str_pad('', $b, chr(0x5c));
			$k_ipad = $key ^ $ipad ;
			$k_opad = $key ^ $opad;

			return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
		}
	}
?>
