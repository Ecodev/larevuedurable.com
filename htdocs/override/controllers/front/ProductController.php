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
            '_PAPIER_' => _PAPIER_,
            '_WEB_' => _WEB_,
            '_PAPIER_ET_WEB_' => _PAPIER_ET_WEB_,
            '_ATTRIBUTE_VERSION_' => _ATTRIBUTE_VERSION_,
            'subs'                     => $subs,
            'nbPresentOrFutureActives' => $customer->nbPresentOrFutureActives,
            'current_sub'              => $customer->getCurrentSubscription()
        ));

    }
}