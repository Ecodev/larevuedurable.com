<?php

class AdminSubscriptionsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->className = 'AdminSubscriptions';
        $this->context = Context::getContext();

        parent::__construct();
    }

    public function initContent()
    {
        $templatePath = 'ecodevadminsubscriptions/views/templates/admin/subscriptions/';
        $this->context->controller->addCSS(_MODULE_DIR_.$templatePath.'subscribers.css');
        // $this->context->controller->addJS(_MODULE_DIR_.$templatePath.'form.js');

        if (isset($_REQUEST['customer'])) {
            $customers = [array('ID' => $_REQUEST['customer'])];
        } else {
            $customers = Customer::getAllSubscribers();
        }

        /** @var Customer $customer */
        $min = null;
        $max = null;
        foreach($customers as &$customer) {
            $customer = new Customer($customer['ID']);
            $customer->manageSubscriptions();

            foreach(array_merge($customer->tierce_subscriptions, $customer->user_subscriptions) as $sub) {
                if (is_null($min) || $sub->first_edition < $min) {
                    $min = $sub->first_edition;
                }
                if (is_null($max) || $sub->last_edition > $max) {
                    $max = $sub->last_edition;
                }
            }
        }

        $magazine = Product::getLastMagazineReleased(null);

        $this->context->smarty->assign(
            array(
                'imported_order_state' => _IMPORTED_ORDER_STATE_,
                'actual' => (int) $magazine['reference'],
                'min' => $min,
                'max' => $max,
                'customers' => $customers
            )
        );

        $this->context->smarty->addTemplateDir(_PS_MODULE_DIR_.$templatePath);
        $this->content .= $this->createTemplate('subscribers.tpl')->fetch();

        parent::initContent();

    }

}
