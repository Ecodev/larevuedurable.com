<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * Script that return all the informations
 * needed about a specific campaign
 * in JSON
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
  die('<span style="color:red;">'.$mailchimp->l('Wrong Api key', 'campaignContent', $id_lang).'</span>');

$id = Tools::safeOutput($_GET['id']);

if (isset($_GET['campaign']))
{
	$mailChimp_campaign = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
	$campaign = $mailChimp_campaign->campaigns(array('campaign_id' => $id));

	$json = '{';

	if ($mailChimp_campaign->errorCode)
		$json .= ', "error_campaign" : "'.$mailChimp_campaign->errorMessage.'"';
	else
	{
		$json .= '"list_id" : "'.$campaign['data'][0]['list_id'].'",
 "title" : "'.$campaign['data'][0]['title'].'",
 "subject" : "'.$campaign['data'][0]['subject'].'",
 "from_name" : "'.$campaign['data'][0]['from_name'].'",
 "from_email" : "'.$campaign['data'][0]['from_email'].'",
 "to_name" : "'.$campaign['data'][0]['to_name'].'",
 "type" : "'.$campaign['data'][0]['type'].'"';
	}
	$json .= '}';

	echo $json;
}
else if (isset($_GET['content']) || isset($_GET['text']))
{
	$mailChimp_content = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
	$content = $mailChimp_content->campaignContent($id);

	if ($mailChimp_content->errorCode)
		echo 'error_content: '.$mailChimp_content->errorMessage;
	else
	{
		if (isset($_GET['content']))
			echo $content['html'];
		else
			echo $content['text'];
	}
}

?>