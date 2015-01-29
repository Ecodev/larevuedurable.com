<?php

class EcodevSubscription extends Module
{
    public function __construct()
    {
        $this->name = 'ecodevsubscription';
        $this->tab = 'ecodev';
        $this->version = '1.0';

        $this->_errors = array();

        parent::__construct();

        $this->displayName = $this->l("Abonnements");
        $this->description = $this->l("");
    }

    public function install()
    {
        if (!parent::install() OR
            !$this->registerHook('header')  OR
            !$this->registerHook('top') OR
            !$this->registerHook('actionOrderStatusPostUpdate')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }


    /**
     *    Quand le statut d'une commande est changé (p.ex "paiement accepté" ou "livré")
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        $customer = new Customer($params['cart']->id_customer);
        // récupère les infos, gère les conflits de date, abonne et désabonne automatiquement l'utilisateur au groupe abonnés.
        $subs = $customer->manageSubscriptions(true);
    }


}
