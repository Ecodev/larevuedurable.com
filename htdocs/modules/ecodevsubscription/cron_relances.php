<?php

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
$usersToFollowUp = [];

foreach ($array_newsletter as $key => $user) {
    $customer = new Customer($user['ID']);

    // ajout de la date d'expiration
    $subscriptions = $customer->manageSubscriptions();
    $current_subscription = $customer->getLastSubscription();

    if ($current_subscription != null) {
        $date_dernier_numero = Product::getParutionDateByRef($current_subscription->last_edition);
        if ($date_dernier_numero) {
            $date_dernier_numero = new DateTime($date_dernier_numero);
            $date_relance = $date_dernier_numero->modify('-10 day');
            if ($date_now == $date_relance) {
                $user['ECHEANCE'] = $current_subscription->last_edition;

                // ajout de l'adresse
                $adresses = $customer->getAddresses(1);
                $user['NPA'] = $adresses[0]['postcode'];

                $usersToFollowUp[] = $user;
            }
        }
    }
}

if(count($usersToFollowUp) === 0) {
    exit('No users to follow up at this date ' . $date_now->format(_DATE_FORMAT_SHORT_));
}

$res = $api->listBatchSubscribe(_MC_SUBSCRIBERS_LIST_, $usersToFollowUp, false, true, false);

/****************************************************************
Réplique la campagne
 *****************************************************************/

$campaign = $api->campaignReplicate(_MC_RELANCE_CAMPAIGN_);

if ($api->errorCode) {
    $message = '';
    $message .= "Unable to Replicate Campaign!";
    $message .= "\n\tCode=" . $api->errorCode;
    $message .= "\n\tMsg=" . $api->errorMessage . "\n";

    echo $message;
    error_log(date(_DATE_FORMAT_) . chr(10) . $message . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/_module_ecodevsubscriptions_cron_relances_log.txt');
    exit();
}

Configuration::updateValue('SUBSCRIPTION_LAST_CAMPAIGN', $campaign);

/****************************************************************
Envoi la campagne
 *****************************************************************/

$res = $api->campaignSendNow($campaign);

if ($api->errorCode) {
    $message = '';
    $message .= "Unable to send Campaign!";
    $message .= "\n\tCode=" . $api->errorCode;
    $message .= "\n\tMsg=" . $api->errorMessage . "\n";

    echo $message;
    error_log(date(_DATE_FORMAT_) . chr(10) . $message . chr(10) . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/_module_ecodevsubscriptions_cron_relances_log.txt');
    exit();
}

$message = 'Campagne envoyée avec succès à ' . count($usersToFollowUp) . ' personnes.';
echo $message;
error_log(date(_DATE_FORMAT_) . ' - ' . $message . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/_module_ecodevsubscriptions_cron_relances_log.txt');
