<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * Script that return a preview picture of a Maichimp template
 * 
 * 
 * 
 */

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/mailchimp/class/MCAPI.class.php");
require_once(_PS_MODULE_DIR_."/mailchimp/mailchimp.php");

$mailchimp = new Mailchimp();
$id_lang = (int)$_GET['lang'];

$mailChimp_api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
if (isset($_GET['type']) && $_GET['type'] == 'user')
  $res = $mailChimp_api->templates(array('user' => true, 'gallery' => false, 'base' => false));
else if (isset($_GET['type']) && $_GET['type'] == 'gallery')
  $res = $mailChimp_api->templates(array('user' => false, 'gallery' => true, 'base' => false));
else if (isset($_GET['type']) && $_GET['type'] == 'base')
  $res = $mailChimp_api->templates(array('user' => false, 'gallery' => false, 'base' => true));
else
  $res = $mailChimp_api->templates(array('user' => true, 'gallery' => true, 'base' => true));

if ($mailChimp_api->errorCode)
  echo $mailChimp_api->errorMessage;
else
  {
    echo '<p>'.$mailchimp->l('Please select a template below, customize it and click on the "next" button at the bottom of the page', 'template', $id_lang).'</p><br/>';
    foreach ($res as $key => $value)
      {
	echo '<div style="clear:both"><h4>'.$key.'</h4>';
	$i = 1;
	foreach ($value as $k => $v)
	  {
	    echo '<div style="width:200px;height:200px;'.($i % 5 == 0 ? 'clear:boh': 'float:left').'">';
	    echo $v['name']."<br/><img onclick='getCodeTemplate(\"".$v['id']."\", \"".$key."\")' style='max-width:150px;max-height:150px;cursor:pointer' src='".$v['preview_image']."'/>";
	    echo "</div>";
	    $i++;
	  }
	echo '</div>';
      }
  }

?>