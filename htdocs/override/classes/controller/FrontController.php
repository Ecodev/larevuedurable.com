<?php

class FrontController extends FrontControllerCore
{

    public function initContent($display = true)
    {
        $this->context->smarty->assign(array(
            'currentController' => get_class($this),
            'current_year'      => date('Y')
        ));
        parent::initContent();
        $this->context->smarty->assign(array(
            'HOOK_MENU'         => Hook::exec('displayMenu'),
            'HOOK_TOP_TOP'      => Hook::exec('displayTopTop'),
            'HOOK_HOME_COL1'    => Hook::exec('displayHomeCol1'),
            'HOOK_HOME_COL2'    => Hook::exec('displayHomeCol2'),
            'HOOK_HOME_COL3'    => Hook::exec('displayHomeCol3'),
            'HOOK_TOP_CATEGORY' => Hook::exec('displayTopCategory'),
            'HOOK_CMS_NAV'      => Hook::exec('displayCMSNav'),
            // 'HOOK_HEADER_BOTTOM_RIGHT' => Hook::exec('displayHeaderBottomRight'),
            // 'HOOK_LEFT_ACCOUNT_COLUMN' => Hook::exec('displayLeftAccountNav'),
            // 'HOOK_LEFT_COLUMN_CMS' => Hook::exec('displayLeftColumnCMS')
        ));
    }


    public function init()
    {
        parent::init();

        // si l'utilisateur est connecté, on force les CHF pour les suisses et l'EUR pour tous les autres
        if (isset($this->context->customer->id)) {
            if ($this->context->country->id == 19) // le 19 est l'id de la suisse
            {
                // le 1 est l'id de la devise CHF
                $this->context->smarty->assign('forced_currency', 1); // assignation à smarty pour le module bloccurrencies
                $this->context->currency = new Currency(1);
                $this->context->cart->id_currency = 1;
                $this->context->cookie->id_currency = 1;
            } else {
                // le 2 est l'id de la devise EUR
                $this->context->smarty->assign('forced_currency', 2);
                $this->context->currency = new Currency(2);
                $this->context->cart->id_currency = 2;
                $this->context->cookie->id_currency = 2;

            }
        }
        
        // évite la gestion des abonnements quand il s'agit d'un abonnement de module de paiement qui est actif
        // l'absence de ce contrôle assigne l'utilisateur à un autre groupe et il en résulte qu'aucun module de paiement n'est disponible pour cette commande
        if(
        	get_class($this) != 'DatatransValidationModuleFrontController' && 
	        get_class($this) != 'BankwireValidationModuleFrontController' && 
	        get_class($this) != 'BankwireBVRValidationModuleFrontController' && 
	        get_class($this) != 'ChequeValidationModuleFrontController'
        ){
	        $this->context->customer->manageSubscriptions();
	    }
    }


    public function setMedia()
    {
        parent::setMedia();

        $this->addCSS(_THEME_CSS_DIR_ . 'editor.css');
        $this->addCSS(_THEME_CSS_DIR_ . 'print.css', 'print');
        $this->addJS(_THEME_JS_DIR_.'default.js');
    }

}

