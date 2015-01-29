<?php

class Ecodevganalytics extends Module
{	
	function __construct()
	{
	 	$this->name = 'ecodevganalytics';
	 	$this->tab = 'ecodev';
	 	$this->version = '1';
        $this->displayName = 'Google Analytics';
		
	 	parent::__construct();
		
		if (!Configuration::get('GANALYTICS_ID'))
			$this->warning = $this->l('You have not yet set your Google Analytics ID');
        $this->description = $this->l('Integrate the Google Analytics script into your shop');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
	}
	
    function install()
    {
        if (!parent::install() OR !$this->registerHook('header') OR !$this->registerHook('orderConfirmation'))
			return false;
		return true;
    }
	
	function uninstall()
	{
		if (!Configuration::deleteByName('GANALYTICS_ID') OR !parent::uninstall())
			return false;
		return true;
	}
	
	public function getContent()
	{
		$output = '<h2>Google Analytics</h2>';
		if (Tools::isSubmit('submitGAnalytics') AND ($gai = Tools::getValue('ganalytics_id')) AND ($gadc = Tools::getValue('ganalytics_domain_cookie')))
		{
			Configuration::updateValue('NL_GANALYTICS_ID', $gai);
			Configuration::updateValue('NL_DOMAIN_COOKIE', $gadc);
			$output .= '
			<div class="conf confirm">
				<img src="../img/admin/ok.gif" alt="" title="" />
				'.$this->l('Settings updated').'
			</div>';
		}
		
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$output = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset class="width2">
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Your username').'</label>
				<div class="margin-form">
					<input type="text" name="ganalytics_id" value="'.Tools::getValue('ganalytics_id', Configuration::get('NL_GANALYTICS_ID')).'" />
					<p class="clear">'.$this->l('Example:').' UA-1234567-1</p>
				</div>
				<div class="margin-form">
					<input type="text" name="ganalytics_domain_cookie" value="'.Tools::getValue('ganalytics_domain_cookie', Configuration::get('NL_DOMAIN_COOKIE')).'" />
					<p class="clear">'.$this->l('Example:').' .net-lead.ch</p>
				</div>
				<center><input type="submit" name="submitGAnalytics" value="'.$this->l('Update').'" class="button" /></center>
			</fieldset>
		</form>';
		
		$output .= '
		<fieldset class="space">
			<legend><img src="../img/admin/unknown.gif" alt="" class="middle" />'.$this->l('Help').'</legend>
			 <h3>'.$this->l('The first step of tracking e-commerce transactions is to enable e-commerce reporting for your website\'s profile.').'</h3>
			 '.$this->l('To enable e-Commerce reporting, please follow these steps:').'
			 <ol>
			 	<li>'.$this->l('Log in to your account').'</li>
			 	<li>'.$this->l('Click Edit next to the profile you\'d like to enable').'</li>
			 	<li>'.$this->l('On the Profile Settings page, click edit next to Main Website Profile Information').'</li>
			 	<li>'.$this->l('Change the e-Commerce Website radio button from No to Yes').'</li>
			</ol>
			<h3>'.$this->l('To set up your goals, enter Goal Information:').'</h3>
			<ol>
				<li>'.$this->l('Return to Your Account main page').'</li>
				<li>'.$this->l('Find the profile for which you will be creating goals, then click Edit').'</li>
				<li>'.$this->l('Select one of the 4 goal slots available for that profile, then click Edit').'</li>
				<li>'.$this->l('Enter the Goal URL. Reaching this page marks a successful conversion').'</li>
				<li>'.$this->l('Enter the Goal name as it should appear in your Google Analytics account').'</li>
				<li>'.$this->l('Turn the Goal on').'</li>
			</ol>
			<h3>'.$this->l('Then, define a funnel by following these steps:').'</h3>
			<ol>
				<li>'.$this->l('Enter the URL of the first page of your conversion funnel. This page should be a page that is common to all users working their way towards your Goal.').'</li>
				<li>'.$this->l('Enter a Name for this step.').'</li>
				<li>'.$this->l('If this step is a Required step in the conversion process, mark the checkbox to the right of the step.').'</li>
				<li>'.$this->l('Continue entering goal steps until your funnel has been completely defined. You may enter up to 10 steps, or as few as a single step.').'</li>
			</ol>
			'.$this->l('Finally, configure Additional settings by following the steps below:').'
			<ol>
				<li>'.$this->l('If the URLs entered above are Case sensitive, mark the checkbox.').'</li>
				<li>'.$this->l('Select the appropriate goal Match Type. (').'<a href="http://www.google.com/support/analytics/bin/answer.py?answer=72285">'.$this->l('Learn more').'</a> '.$this->l('about Match Types and how to choose the appropriate goal Match Type for your goal.)').'</li>
				<li>'.$this->l('Enter a Goal value. This is the value used in Google Analytics\' ROI calculations.').'</li>
				<li>'.$this->l('Click Save Changes to create this Goal and funnel, or Cancel to exit without saving.').'</li>
			</ol>
			<h3>'.$this->l('Demonstration: The order process').'</h3>
			<ol>
				<li>'.$this->l('After having enabled your e-commerce reports and selected the respective profile enter \'order-confirmation.php\' as the targeted page URL').'</li>
				<li>'.$this->l('Name this goal (for example \'Order process\')').'</li>
				<li>'.$this->l('Activate the goal').'</li>
				<li>'.$this->l('Add \'product.php\' as the first page of your conversion funnel').'</li>
				<li>'.$this->l('Give it a name (for example, \'Product page\')').'</li>
				<li>'.$this->l('Do not mark \'required\' checkbox because the customer could be visiting directly from an \'adding to cart\' button such as in the homefeatured block on the homepage').'</li>
				<li>'.$this->l('Continue by entering the following URLs as goal steps:').'
					<ul>
						<li>order/step0.html '.$this->l('(required)').'</li>
						<li>authentication.php '.$this->l('(required)').'</li>
						<li>order/step1.html '.$this->l('(required)').'</li>
						<li>order/step2.html '.$this->l('(required)').'</li>
						<li>order/step3.html '.$this->l('(required)').'</li>
					</ul>
				</li>
				<li>'.$this->l('Check the \'Case sensitive\' option').'</li>
				<li>'.$this->l('Save this new goal').'</li>
			</ol>
		</fieldset>';
		
		return $output;
	}
	
	function hookHeader($params)
	{
		global $smarty, $step, $protocol_content;
		

		
		//$step = (strpos($_SERVER['REQUEST_URI'], __PS_BASE_URI__.'order.php') === 0 ? intval($step) : '');
		if(strpos($_SERVER['REQUEST_URI'], __PS_BASE_URI__.'order.php') === 0){
			$step = intval($step);
		}else if(isset($_GET['fromCart']) && $_GET['fromCart']==1) 
			$step .= 'login';
		
		$smarty->assign(array(
			'ganalyticsID'	=> Configuration::get('NL_GANALYTICS_ID'),
			'ganalitics_domaine_cookie' => Configuration::get('NL_DOMAIN_COOKIE'),
		));

		//if( $step!='' || $step==0 ) $smarty->assign('step', $step);
		if( isset($step) || (isset($step) && $step==0) ) $smarty->assign('step', $step);
		
		return $this->display(__FILE__, 'toutes_les_pages.tpl');
	}
	
	function hookOrderConfirmation($params)
	{
		global $smarty, $protocol_content;
		
		$order = $params['objOrder'];
		if (Validate::isLoadedObject($order))
		{
			$deliveryAddress = new Address(intval($order->id_address_delivery));
		
			$conversion_rate = 1;
			if ($order->id_currency != Configuration::get('PS_CURRENCY_DEFAULT'))
			{
				$currency = new Currency(intval($order->id_currency));
				$conversion_rate = floatval($currency->conversion_rate);
			}
			
			$products = $order->getProducts();
			foreach ($products AS $key => $product)
			{
				$product['product_price_wt'] = Tools::ps_round(floatval($product['product_price_wt']) / floatval($conversion_rate), 2);
				$p = new Product(	$product['product_id'] );
				$products[$key]['product_name'] = $p->name[2];
			}
			

			$smarty->assign(array(
				'gaorder'			=> $order,
				'gatotal'			=> Tools::ps_round(floatval($order->total_paid) / floatval($conversion_rate), 2),
				'gashipping'		=> Tools::ps_round(floatval($order->total_shipping) / floatval($conversion_rate), 2),
				'gadeliveryAddress' => $deliveryAddress,
				'gaproducts'		=> $products
			));
			
			return $this->display(__FILE__, 'confirmation_commande.tpl');
		}
	}
}
