<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * Script that return the code source of a mailchimp template
 * 
 * 
 * 
 */

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/mailchimp/class/MCAPI.class.php");

$mailChimp_api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));

if (isset($_GET['id']))
  $res = $mailChimp_api->templateInfo($_GET['id'], $_GET['type']);

if ($mailChimp_api->errorCode)
  echo $mailChimp_api->errorMessage;
else
    echo $res['source'];
?>