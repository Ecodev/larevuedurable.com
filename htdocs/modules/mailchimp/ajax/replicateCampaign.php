<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * Script that replicate a campaign by its id and return the Id
 * of the new campaign
 * 
 * 
 */
require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/mailchimp/class/MCAPI.class.php");
require_once(_PS_MODULE_DIR_."/mailchimp/mailchimp.php");

$mailchimp = new Mailchimp();
$id_lang = (int)$_GET['lang'];
/*for security reason, if the API key does not correspond to the one stored in Database, we die an error*/
$api = $_GET['api'];
if ($api != Configuration::get('MAILCHIMP_API_KEY'))
  die('<span style="color:red">'.$mailchimp->l('Wrong Api key', false, $id_lang).'</span>');
$id = $_GET['id'];

$mailChimp_api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
$res = $mailChimp_api->campaignReplicate($id);

if ($mailChimp_api->errorCode)
  echo '<span style="color:red">'.$mailChimp_api->errorMessage.'</span>';
else
	echo $mailchimp->l('Your New Campaign Id', false, $id_lang).' is '.$res;
?>