<?php

class ProductController extends ProductControllerCore
{

    public function initContent()
    {
        parent::initContent();

        $customer = new Customer($this->context->customer->id);
        $subs = $customer->manageSubscriptions();

        $this->context->smarty->assign(array(
            '_ABONNEMENT_PARTICULIER_' => _ABONNEMENT_PARTICULIER_,
            '_ABONNEMENT_INSTITUT_'    => _ABONNEMENT_INSTITUT_,
            '_ABONNEMENT_SOLIDARITE_'  => _ABONNEMENT_SOLIDARITE_,
            '_ABONNEMENT_MOOC_'  => _ABONNEMENT_MOOC_,
            'subs'                     => $subs,
            'nbPresentOrFutureActives' => $customer->nbPresentOrFutureActives,
            'current_sub'              => $customer->getCurrentSubscription()
        ));

    }
}