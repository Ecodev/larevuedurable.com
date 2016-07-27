<?php

class EcosubscriptionsSubscriptionsModuleFrontController extends ModuleFrontController
{
    public $auth = true;
    public $ssl = true;

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $customer = new Customer($this->context->customer->id);
        $customer->manageSubscriptions();

        $this->context->smarty->assign(array(
            'customer' => $customer,
        ));

        $this->setTemplate('subscriptions.tpl');
    }
}
