<?php

require_once(dirname(__FILE__).'/../../config/defines.inc.php');
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/PSWebServiceLibrary.php');

$db = DB::getInstance();
$context = Context::getContext();
$context->controller = new MySubscriptionController();

$siteurl = 'http://'.Configuration::get('PS_SHOP_DOMAIN');
$apikey = 'OX7P1KT26VMCR2N6FPRA3NB1JZKXRSAF';
$debug = 0;




try
{
	$webService = new PrestaShopWebservice($siteurl, $apikey, $debug);
	
 	$xml = $webService->get(array('url' => $siteurl.'/api/carts?schema=synopsis'));

 	

	$xml->cart->associations->cart_rows->cart_row->id_product_attribute = 109;	

 
 	$xml->cart->id_currency = 1;
 	$xml->cart->id_customer = 7;
 	$xml->cart->id_guest = 0;
 	$xml->cart->id_lang = 1;
 	$xml->cart->date_add = '2013-01-01 00:00:00';
 	$xml->cart->date_upd = '2013-01-01 00:00:01';
	$xml->cart->associations->cart_rows->cart_row->id_product = 8;
 	$xml->cart->associations->cart_rows->cart_row->quantity = 1;

 	$opt = array(
 		'resource' => 'carts',
 		'postXml' => $xml->asXML()
 		);
 	$cart = $webService->add($opt);
	$cart_id = $cart->cart->cart->id;
	
	$xml->cart->date_add = $start_date->format(_DATE_FORMAT_);
	$xml->cart->date_upd = $start_date->format(_DATE_FORMAT_);//date(_DATE_FORMAT_);
	$opt['postXml'] = $xml->asXML();
	$cart = $this->webService->edit($opt);
	 	
	echo "Cart successfully added : ".$cart_id."<br/>";   



		
}
catch (PrestaShopWebserviceException $ex) 
{
    // Shows a message related to the error
    echo 'Erreur web service : <br />' ;
    echo '<pre>';
    print_r( $ex->getMessage() );
    echo '</pre>'; 
}
catch( Exception $ex)
{
	echo 'Erreur générale : <br/>';
	echo '<pre>';
	print_r( $ex->getMessage() );
	echo '</pre>'; 
}








