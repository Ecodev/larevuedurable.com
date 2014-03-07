<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * Script that return in html format the number of customer in the database
 * and also the number of customers who are not part
 * of the specifed mailchimp list
 * 
 */

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/mailchimp/class/MCAPI.class.php");
require_once(_PS_MODULE_DIR_."/mailchimp/mailchimp.php");

$mailchimp = new Mailchimp();
$idLang = (int)$_GET['lang'];

$listId = $_GET['list'];

Configuration::updateValue('MAILCHIMP_SYNCHRO_ID_LIST', pSQL($listId));

$api  = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));



$array_newsletter = Db::getInstance()->ExecuteS('
		SELECT email
		FROM '._DB_PREFIX_.'customer
		WHERE newsletter = 1
		AND active = 1');

foreach ($array_newsletter as $k => $v)
  {
    $array_customer[] = $v['email'];
  }

/*the method listMemberInfo is limited to 50 emails address*/
$array_limited = array_chunk($array_customer, 50);
$unsubscribe = 0;

foreach ($array_limited as $k => $v)
  {
    $res = $api->listMemberInfo(Tools::safeOutput($listId), $v);
    $unsubscribe += $res['errors'];
  }

$plural = array('there_be_cust' => (count($array_newsletter) > 1 ? $mailchimp->l('There are', 'listInformation', $idLang) : $mailchimp->l('There is', 'listInformation', $idLang)),
					'be_sub_cust' => (count($array_newsletter) > 1 ? $mailchimp->l('are subscribed', 'listInformation', $idLang) : $mailchimp->l('is subscribed', 'listInformation', $idLang)),
					'be_not_sub_cust' => (count($array_newsletter) > 1 ? $mailchimp->l('are not subscribed', 'listInformation', $idLang) : $mailchimp->l('is not subscribed', 'listInformation', $idLang)),
					's' => (count($array_newsletter) > 1 ? 's' : ''));

$plural = array(
	'there_be_cust' => array(true => $mailchimp->l('There are', 'listInformation', $idLang),
									 false => $mailchimp->l('There is', 'listInformation', $idLang)),
	'be_sub_cust' => array(true => $mailchimp->l('have subscribed', 'listInformation', $idLang),
								 false => $mailchimp->l('subscribed', 'listInformation', $idLang)),
	'be_not_sub_cust' => array(true => $mailchimp->l('have not subscribed', 'listInformation', $idLang),
										 false => $mailchimp->l('has not subscribed', 'listInformation', $idLang)),
	's' => array(true => 's',
			 false => '')
);

$on = (count($array_newsletter) > 1 ? true : false);

echo '<p>'.$plural['there_be_cust'][$on].' '.count($array_newsletter).' '.$mailchimp->l('customer', 'listInformation', $idLang).''.$plural['s'][$on].' '.$mailchimp->l('who', 'listInformation', $idLang).' '.$plural['be_sub_cust'][$on].' '.$mailchimp->l('to this newsletter in your database', 'listInformation', $idLang).'<br/>
'.$unsubscribe.' '.$mailchimp->l('of them', 'listInformation', $idLang).' '.$plural['be_not_sub_cust'][$on].' '.$mailchimp->l('to your list', 'listInformation', $idLang).'.';

?>
