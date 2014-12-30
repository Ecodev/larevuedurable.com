<?php

class Relance
{
    public $api;
    public $execution_date;

    public function __construct()
    {
        $this->execution_date = new DateTime();
        $this->api = new MCAPI(_MAILCHIMP_API_KEY_);

        $context = Context::getContext();
        $context->controller = new MySubscriptionController();
    }

    public function relancer($num = null)
    {
        $this->videListe();
        $relances = $this->importeAbonnes($num);
        $campagne = $this->dupliqueCampagne();
        $this->envoiCampagne($campagne, $relances);
    }

    /**
     * Liste les campagnes et les listes telles que vues par l'API afin de pouvoir récupérer manuellement l'ID de ces objets dans l'API.
     * Ces Ids ne sont pas accessibles via le GUI de maichimp.
     */
    public function echoData()
    {
        $campaigns = $this->api->campaigns();
        p($campaigns);

        $lists = $this->api->lists();
        d($lists);
    }

    private function videListe()
    {
        $subscribed = $this->api->listMembers(_MC_SUBSCRIBERS_LIST_, 'subscribed');
        $unsubscribed = $this->api->listMembers(_MC_SUBSCRIBERS_LIST_, 'unsubscribed');

        $email_list = array();
        foreach ($subscribed['data'] as $sub) {
            array_push($email_list, $sub['email']);
        }

        foreach ($unsubscribed['data'] as $sub) {
            array_push($email_list, $sub['email']);
        }

        if (sizeof($email_list)) {
            $this->api->listBatchUnsubscribe(_MC_SUBSCRIBERS_LIST_, $email_list, true, false, false);
        }

    }

    private function importeAbonnes($num = null)
    {
        $array_newsletter = Customer::getAllSubscribers();
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

        if (count($relances) === 0) {
            $this->reporteErreur('Aucun utilisateur à relancer', 1);
        } else {
            $this->api->listBatchSubscribe(_MC_SUBSCRIBERS_LIST_, $relances, false, true, false);

            return $relances;
        }
    }

    private function dupliqueCampagne()
    {
        $campaign = $this->api->campaignReplicate(_MC_RELANCE_CAMPAIGN_);

        if ($this->api->errorCode) {
            $message = '';
            $message .= "Unable to Replicate Campaign!";
            $message .= "\n\tCode=" . $this->api->errorCode;
            $message .= "\n\tMsg=" . $this->api->errorMessage . "\n";
            $this->reporteErreur($message, 1);

        } else {
            $campagne = $this->api->campaignUpdate($campaign, 'title', 'Relance du ' . $this->execution_date->format(_DATE_FORMAT_));
            Configuration::updateValue('SUBSCRIPTION_LAST_CAMPAIGN', $campagne);

            return $campagne;
        }
    }

    private function envoiCampagne($campagne, $relances)
    {
        $nbRelances = count($relances);
        $relances = array_map(function ($relance) {
            return $relance['ECHEANCE'] . ' - ' . $relance['ID'] . ' - ' . $relance['EMAIL'];
        }, $relances);

        $msg = "Campagne envoyée avec succès à $nbRelances personnes : \n";
        $msg .= implode("\n\t", $relances);

        if (!_PS_MODE_DEV_) {
            $this->api->campaignSendNow($campagne);

            if ($this->api->errorCode) {
                $message = '';
                $message .= "Unable to send Campaign!";
                $message .= "\n\tCode=" . $this->api->errorCode;
                $message .= "\n\tMsg=" . $this->api->errorMessage . "\n";
                $this->reporteErreur($message, 1);
            } else {
                $this->reporteErreur($msg);
            }

        } else {
            $this->reporteErreur('DEV : ' . $msg);
        }
    }

    private function reporteErreur($msg, $exit = false)
    {
        error_log(date(_DATE_FORMAT_) . ' - ' . $msg . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/log/_cron_relances_log.txt');
        echo $msg;

        if ($exit) {
            exit();
        }
    }

}