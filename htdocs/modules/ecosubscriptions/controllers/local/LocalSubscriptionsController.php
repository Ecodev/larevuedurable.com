<?php

require_once(dirname(__FILE__) . '/../../autoload.php');

class LocalSubscriptionsController
{
    protected $ctrl = null;

    protected $context = null;

    protected $excludedModules = [
        'DatatransValidationModuleFrontController',
        'BankwireValidationModuleFrontController',
        'BankwireBVRValidationModuleFrontController',
        'ChequeValidationModuleFrontController'
    ];

    public function __construct($ctrl, $context)
    {
        $this->ctrl = $ctrl;
        $this->context = $context;
    }

    /**
     * Avoids subscriptions management on paiement controllers.
     *
     * The lack of this test result in a customer assigned to another
     * customer group and then paiement modules are no more listed
     */
    public function header()
    {
        if (!in_array(get_class($this->ctrl), $this->excludedModules) && method_exists($this->context->customer, 'manageSubscriptions')) {
            $this->context->customer->manageSubscriptions();
        }
    }
}
