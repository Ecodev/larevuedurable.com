<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * Script that update a specific campaign
 * 
 * 
 * 
 */
require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/mailchimp/class/MCAPI.class.php");
require_once(_PS_MODULE_DIR_."/mailchimp/mailchimp.php");

$mailchimp = new Mailchimp();
$id_lang = (int)$_GET['lang'];

/*for security reason, if the API key does not correspond to the one stored in Database, we die an error*/
$api = $_POST['api'];
if ($api != Configuration::get('MAILCHIMP_API_KEY'))
  die('<span style="color:red">'.$mailchimp->l('Wrong Api key', false, $id_lang).'</span>');

$id = Tools::safeOutput($_GET['id']);

$updateVar = array(
	'list_id' => Tools::safeOutput($_POST['list']),
	'title' => Tools::safeOutput($_POST['title']),
	'subject' => Tools::safeOutput($_POST['subject']),
	'from_email' => Tools::safeOutput($_POST['from_email']),
	'from_name' => Tools::safeOutput($_POST['from_name']),
	'to_name' => Tools::safeOutput($_POST['reply']),
	'content' => array(
		'html' => $_POST['design'],
		'text' => $_POST['plain']
	)
);

$mailChimp_api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
foreach ($updateVar as $k => $v)
{
	$res = $mailChimp_api->campaignUpdate($id, $k, $v);
	if ($mailChimp_api->errorCode)
		echo '<span style="color:red">'.$mailChimp_api->errorMessage.'</span><br/>';
	else
		echo '<span style="color:green">'.$k.' '.$mailchimp->l('has been succesfully updated', false, $id_lang).'.</span><br/>';
}

?>