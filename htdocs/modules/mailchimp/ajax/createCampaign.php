<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * Script that create a campaign
 * be carefull, campaignCreate doesn't send the campaign
 * it only save the campaign in order to update, schedule or eventually send it
 * @return
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
$type = ($_POST['type'] == 'REGULAR' ? 'regular': 'plaintext');
$list = Tools::safeOutput($_POST['list']);
$title = Tools::safeOutput($_POST['title']);
$design = $_POST['design'];
$plain = $_POST['plain'];
$subject = Tools::safeOutput($_POST['subject']);
$from_name = Tools::safeOutput($_POST['from_name']);
$from_email = Tools::safeOutput($_POST['from_email']);
$reply = Tools::safeOutput($_POST['reply']);

$mailChimp_api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));

$res = $mailChimp_api->campaignCreate($type, array(
				 'list_id' => $list,
				 'title' => $title,
				 'subject' => $subject,
				 'from_email' => $from_email,
				 'from_name' => $from_name,
				 'to_name' => $reply
			 ),
			 array(
				 'html' => $design,
				 'text' => $plain
			 ));

if ($mailChimp_api->errorCode)
  echo '<span style="color:red">'.$mailChimp_api->errorMessage.'</span>';
else
  echo '<span style="color:green">'.$mailchimp->l('The ID for the created campaign is', false, $id_lang).' '.$res.'</span>';

?>