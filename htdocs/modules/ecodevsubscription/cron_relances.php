<?php

require_once(dirname(__FILE__) . '/../../config/defines.inc.php');
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(_PS_MODULE_DIR_ . "/mailchimp/mailchimp.php");

$execution_date = date(_DATE_FORMAT_);
$mailchimp = new Mailchimp();
$api = new MCAPI(_MAILCHIMP_API_KEY_);


$context = Context::getContext();
$context->controller = new MySubscriptionController();


/******************************************************************************************************
Listing de l'existant pour retrouver les ids -> ils ne figurent pas dans l'interface de maichimp

L'identifiant des campagnes et des listes se fait par un chiffre qu'on ne retrouve pas dans l'interface de mailchimp
Ces lignes permettent de lister les campagnes et listes existantes afin d'en récupérer le champ ['id']
 *******************************************************************************************************/

//$campaigns = $api->campaigns();
//$lists = $api->lists();
//
//echo '<pre>';
//print_r( $campaigns );
//echo '/////////////////////////////////////////';
//print_r( $lists);
//echo '</pre>';
//
//exit();



/*************************************************************************
Nettoie la liste des clients avant d'envoyer une nouvelle campagne
 **************************************************************************/

$subscribed = $api->listMembers(_MC_SUBSCRIBERS_LIST_, 'subscribed');
$unsubscribed = $api->listMembers(_MC_SUBSCRIBERS_LIST_, 'unsubscribed');

$email_list = array();
foreach ($subscribed['data'] as $sub) {
    array_push($email_list, $sub['email']);
}

foreach ($unsubscribed['data'] as $sub) {
    array_push($email_list, $sub['email']);
}


if (sizeof($email_list)) {
    $vals = $api->listBatchUnsubscribe(_MC_SUBSCRIBERS_LIST_, $email_list, true, false, false);
}


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


/****************************************************************
Importe les clients (seulement ceux qui ont un abonnement)
 *****************************************************************/

$array_newsletter = Customer::getAllSubscribers();
$email_list = array();
foreach ($array_newsletter as $key => $user) {
    $customer = new Customer($user['ID']);
    array_push($email_list, $user['EMAIL']);
    // ajout de l'adresse
    $adresses = $customer->getAddresses(1);
    $array_newsletter[$key]['NPA'] = $adresses[0]['postcode'];

    // ajout de la date d'expiration
    $subscriptions = $customer->manageSubscriptions();
    $current_subscription = $customer->getLastSubscription();

    if ($current_subscription != null) {
        $date = new DateTime($current_subscription->getEndDate());
        $date_relance = $date->modify('-1 month');
        $array_newsletter[$key]['RELANCE'] = $date_relance->format(_DATE_FORMAT_SHORT_); // format utilisé sur LRD techniquement pour gérer les relances
        $end_date = new DateTime($current_subscription->getEndDate());
        $array_newsletter[$key]['ECHEANCE'] = $end_date->format('d.m.Y'); // format plus conventionnel pour la lecture humaine
    } else {
        $array_newsletter[$key]['RELANCE'] = '';
        $array_newsletter[$key]['ECHEANCE'] = '';
    }
}

$res = $api->listBatchSubscribe(_MC_SUBSCRIBERS_LIST_, $array_newsletter, false, true, false);

/****************************************************************
Réplique la campagne
 *****************************************************************/

$newCampaign = $api->campaignReplicate(_MC_RELANCE_CAMPAIGN_);

if ($api->errorCode) {
    $message = '';
    $message .= "Unable to Replicate Campaign!";
    $message .= "\n\tCode=" . $api->errorCode;
    $message .= "\n\tMsg=" . $api->errorMessage . "\n";

    echo $message;
    error_log(date(_DATE_FORMAT_) . chr(10) . $message . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/_module_ecodevsubscriptions_cron_relances_log.txt');
    exit();
}

Configuration::updateValue('SUBSCRIPTION_LAST_CAMPAIGN', $newCampaign);


/****************************************************************
Met à jour la segmentation
 *****************************************************************/

$segmentation_cond = array(
    'match'      => 'all',
    'conditions' => array(
        array(
            'field' => 'RELANCE',
            'op'    => 'eq',
            'value' => $date_now->format(_DATE_FORMAT_SHORT_)
            // '2014-05-28'
        )
    )
);

$campaign = $api->campaignUpdate($newCampaign, 'title', 'Relance du ' . $execution_date);
//$campaign = $api->campaignUpdate($newCampaign, 'subject', 'Relance du '.$execution_date); // change le sujet pour les phases de test
$segmentation = $api->campaignSegmentTest(_MC_SUBSCRIBERS_LIST_, $segmentation_cond);
$campaign = $api->campaignUpdate($newCampaign, 'segment_opts', $segmentation_cond);


if ($api->errorCode) {
    $message = '';
    $message .= "Unable to update Campaign!";
    $message .= "\n\tCode=" . $api->errorCode;
    $message .= "\n\tMsg=" . $api->errorMessage . "\n";

    echo $message;
    error_log(date(_DATE_FORMAT_) . chr(10) . $message . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/_module_ecodevsubscriptions_cron_relances_log.txt');
    exit();
}

/****************************************************************
Envoi la campagne
 *****************************************************************/


$res = $api->campaignSendNow($newCampaign);

if ($api->errorCode) {
    $message = '';
    $message .= "Unable to send Campaign!";
    $message .= "\n\tCode=" . $api->errorCode;
    $message .= "\n\tMsg=" . $api->errorMessage . "\n";

    echo $message;
    error_log(date(_DATE_FORMAT_) . chr(10) . $message . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/_module_ecodevsubscriptions_cron_relances_log.txt');
    exit();

}

$message = 'Campagne envoyée avec succès à ' . $segmentation . ' personnes.';
echo $message;
error_log(date(_DATE_FORMAT_) . ' - ' . $message . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/_module_ecodevsubscriptions_cron_relances_log.txt');
















