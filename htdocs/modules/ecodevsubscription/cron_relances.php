#!/usr/bin/php
<?php
if (php_sapi_name() == 'cli')
{
    define('_PS_ADMIN_DIR_', getcwd());
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__FILE__)));
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . "/class/MCAPI.class.php");
require_once(dirname(__FILE__) . "/class/Relance.php");

$script = new Relance();

$num = isset($_REQUEST['num']) ? (int) $_REQUEST['num'] : null;

$script->relancer($num);

/*************************************************************************
Supprime la dernière campagne de relance pour éviter de polluer chaque jour
 **************************************************************************/

// désormais les campagnes ne peuvent pas etre supprimées dans les 7 jours qui suivent leur envoi.
// ce système devient donc obsolète,
// prendre le temps de coder un système qui supprime seulement les campagnes issues des relances automatiques après 7 jours.

//$oldCampaingID = Configuration::get('SUBSCRIPTION_LAST_CAMPAIGN');
//
//if ($oldCampaingID) {
//    $retval = $api->campaignDelete($oldCampaingID);
//}
