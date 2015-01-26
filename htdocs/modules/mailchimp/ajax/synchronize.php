<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * Script that register a list of email Addresses in a mailchimp list
 * The script return an html format of all the email addresses that have been
 * updated correctly as those that have been failed
 * 
 */

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/mailchimp/class/MCAPI.class.php");
require_once(_PS_MODULE_DIR_."/mailchimp/mailchimp.php");

$mailchimp = new Mailchimp();
$id_lang = (int)$_GET['lang'];

$list = $_GET['list'];

$api  = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));

$array_newsletter = Db::getInstance()->ExecuteS('
		SELECT id_customer as ID, `email` as EMAIL , firstname as FNAME, lastname as LNAME
		FROM `'._DB_PREFIX_.'customer`
		WHERE `newsletter` = 1
		AND `active` = 1');


// added by sam
// completion des infos
foreach( $array_newsletter as $key=> $user)
{		
	$customer = new Customer($user['ID']);

	// ajout de l'adresse
	$adresses = $customer->getAddresses(1);
	$array_newsletter[$key]['NPA'] = $adresses[0]['postcode'];

	// ajout de la date d'expiration
	$subscriptions = $customer->getSubscriptions(false);
	$current_subscription = $customer->getLastSubscription();
	if( $current_subscription != null )
	{
		$date = clone $current_subscription->end_date;
		$date_relance = $date->modify('-1 month');
		$array_newsletter[$key]['RELANCE'] = $date_relance->format(_DATE_FORMAT_SHORT_);
	}
	else 
	{
		$array_newsletter[$key]['RELANCE'] = '';
	}
}
// fin de completion des infos

$res = $api->listBatchSubscribe($list, $array_newsletter, false, true, false);

if ($api->errorCode)
  echo '<span style="color:red">API error : '.$api->errorCode.'</span>';
else
  {
    echo '<p>'.$res['add_count'].' '.$mailchimp->l('e-mail addresses were succesfully added', 'synchronize', $id_lang).'<br/>
'.$res['update_count'].' '.$mailchimp->l('e-mail addresses were succesfully updated', 'synchronize', $id_lang).'<br/>
'.$res['error_count'].' '.$mailchimp->l('e-mail addresses failed', 'synchronize', $id_lang).'</p>
<ul>';
    foreach ($res['errors'] as $k => $v)
      echo '<li>'.$v['message'].'</li>';
    echo '</ul>';
  }
?>