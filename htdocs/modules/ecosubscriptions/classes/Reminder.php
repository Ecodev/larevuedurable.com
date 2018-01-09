<?php

class Reminder
{
    public $api;
    public $execution_date;

    public function __construct()
    {
        $this->execution_date = new DateTime();
        $this->api = new MCAPI(_MAILCHIMP_API_KEY_);
    }

    public function send($num = null)
    {
        $this->log('Execution du script de relance');

        $module = Module::getInstanceByName('ecosubscriptions');
        $isActivated = (bool) Configuration::get($module->prefixConfiguration . AdminSubscriptionsReminderController::$configActivate);

        // Execute only if is activated, or if a number is provided (that means a manual sending)
        if ($isActivated || $num) {

            $this->videListe();
            $relances = $this->getSubscribersToRemind($num);

            $this->log(count($relances) . " abonnés à relancer");

            if (count($relances)) {
                $this->api->listBatchSubscribe(_MC_SUBSCRIBERS_LIST_, $relances, false, true, false);
                $campagne = $this->dupliqueCampagne();
                $this->envoiCampagne($campagne, $relances);
            }
        } else {
            $this->log('Désactivé : envoi annulé');
        }
    }

    /**
     * Liste les campagnes et les listes telles que vues par l'API afin de pouvoir récupérer manuellement l'ID de ces objets dans l'API.
     * Ces Ids ne sont pas accessibles via le GUI de mailchimp.
     */
    public function echoData($print = false)
    {
        $campaigns = $this->api->campaigns([], 0, 1000);
        if ($print) p($campaigns);

        $lists = $this->api->lists([], 0, 100);
        if($print) d($lists);

        return [
            'campaigns' => $campaigns,
            'lists' => $lists
        ];
    }

    private function videListe()
    {
        $subscribed = $this->api->listMembers(_MC_SUBSCRIBERS_LIST_, 'subscribed', null, 0, 15000);
        $subscribed = is_array($subscribed['data']) ? $subscribed['data'] : [];

        $unsubscribed = $this->api->listMembers(_MC_SUBSCRIBERS_LIST_, 'unsubscribed', null, 0, 15000);
        $unsubscribed = is_array($unsubscribed['data']) ? $unsubscribed['data'] : [];

        $email_list = array();
        foreach ($subscribed as $sub) {
            array_push($email_list, $sub['email']);
        }

        foreach ($unsubscribed as $sub) {
            array_push($email_list, $sub['email']);
        }

        if (sizeof($email_list)) {
            $this->api->listBatchUnsubscribe(_MC_SUBSCRIBERS_LIST_, $email_list, true, false, false);
        }
    }

    public function getSubscribersToRemind($num)
    {
        $array_newsletter = Customer::getAllSubscribersForRemind();

        $relances = [];

        foreach ($array_newsletter as $key => $user) {
            $customer = new Customer($user['ID']);

            // ajout de la date d'expiration
            $customer->manageSubscriptions();
            $current_subscription = $customer->getLastSubscription();

            if ($current_subscription != null && (!$num || $num && $num == $current_subscription->last_edition)) {
                $date_dernier_numero = Product::getParutionDateByRef($current_subscription->last_edition);
                if ($date_dernier_numero) {
                    $date_dernier_numero = new DateTime($date_dernier_numero);
                    $date_relance = $date_dernier_numero->modify('-10 day');
                    if ($num || $this->execution_date->format(_DATE_FORMAT_SHORT_) == $date_relance->format(_DATE_FORMAT_SHORT_)) {
                        $user['ECHEANCE'] = $current_subscription->last_edition;

                        // ajout de l'adresse
                        $adresses = $customer->getAddresses(1);
                        if (count($adresses)) {
                            $user['NPA'] = $adresses[0]['postcode'];
                        }

                        $relances[] = $user;
                    }
                }
            }
        }

        return $relances;
    }


    private function dupliqueCampagne()
    {
        $campaignId = $this->api->campaignReplicate(_MC_RELANCE_CAMPAIGN_);

        if ($this->api->errorCode) {
            $message = "Unable to Replicate Campaign - Code = " . $this->api->errorCode . " - Msg = " . $this->api->errorMessage . "\n";
            $this->log($message);
            exit();

        } else {
            if ($this->api->campaignUpdate($campaignId, 'title', 'Relance du ' . $this->execution_date->format(_DATE_FORMAT_))) {
                Configuration::updateValue('SUBSCRIPTION_LAST_CAMPAIGN', $campaignId);

                return $campaignId;
            } else {
                $this->log("La campagne a été dupliquée mais n'a pas pu être mise à jour.");
                exit();
            }

        }
    }

    private function envoiCampagne($campagne, $relances)
    {
        $relances = array_map(function ($relance) {
            return $relance['ECHEANCE'] . ' - ' . $relance['ID'] . ' - ' . $relance['EMAIL'];
        }, $relances);

        $msg = "Campagne envoyée avec succès : \n";
        $msg .= implode("\n\t", $relances);

        if (!_PS_MODE_DEV_) {
            $this->api->campaignSendNow($campagne);

            if ($this->api->errorCode) {
                $message = "Unable to send Campaign - Code = " . $this->api->errorCode . " - Msg = " . $this->api->errorMessage . "\n";
                $this->log($message);
                exit();
            } else {
                $this->log($msg);
            }

        } else {
            $this->log('DEV : ' . $msg);
        }
    }

    private function log($msg)
    {
        $msg = date(_DATE_FORMAT_) . ' - RELANCES -  ' . $msg . "\r\n";
        error_log($msg, 3, _PS_ROOT_DIR_ . '/../logs/cron_log.txt');
    }

}
