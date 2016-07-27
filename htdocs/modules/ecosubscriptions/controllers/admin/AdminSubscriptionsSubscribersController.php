<?php

require_once(dirname(__FILE__) . '/../../autoload.php');

class AdminSubscriptionsSubscribersController extends AbstractAdminSubscriptionsController
{
    public function __construct()
    {
        $this->className = 'AdminSubscriptionsSubscribers';
        parent::__construct();
    }

    public function initContent()
    {
        $templatePath = $this->name . '/views/templates/admin/subscriptions_subscribers/';
        $this->context->controller->addCSS(_MODULE_DIR_ . $templatePath . 'subscribers.css');
        $this->context->controller->addCSS(_MODULE_DIR_ . $templatePath . 'colors.css');
        $this->context->controller->addJS(_MODULE_DIR_ . $templatePath . 'default.js');

        if (isset($_REQUEST['customer'])) {
            $ids = explode(',', $_REQUEST['customer']);
            $customers = array_map(function ($id) {
                return array('ID' => $id);
            }, $ids);
        } else {
            $customers = Customer::getAllSubscribers();
        }

        /** @var Customer $customer */
        $min = null;
        $max = null;
        foreach ($customers as &$customer) {
            $customer = new Customer($customer['ID']);
            $customer->manageSubscriptions();

            $subs1 = $customer->tierce_subscription ? [$customer->tierce_subscription] : [];
            $subs2 = !empty($customer->user_subscriptions) ? $customer->user_subscriptions : [];
            $subs3 = !empty($customer->user_ignored_subscriptions) ? $customer->user_ignored_subscriptions : [];

            foreach (array_merge($subs1, $subs2, $subs3) as $sub) {
                if (is_null($min) || $sub->first_edition < $min) {
                    $min = $sub->first_edition;
                }
                if (is_null($max) || $sub->last_edition > $max) {
                    $max = $sub->last_edition;
                }
            }
        }

        $allMagazines = Product::getAllRegisteredMagazines();
        $magazinesAfterFirstSubscription = array();

        foreach ($allMagazines as $mag) {
            if ($mag['reference'] >= $min) {
                array_push($magazinesAfterFirstSubscription, $mag);
            }
        }

        $magazinesAfterFirstSubscription = array_map(function ($mag) {
            $mag['reference'] = (int) $mag['reference'];

            return $mag;
        }, $magazinesAfterFirstSubscription);

        $magazine = Product::getLastMagazineReleased();

        $this->context->smarty->assign(array(
                'imported_order_state' => _IMPORTED_ORDER_STATE_,
                'magazines' => $magazinesAfterFirstSubscription,
                'actual' => (int) $magazine['reference'],
                'nextRemindDate' => Subscription::getNextRemindDate(),
                'min' => $min,
                'max' => $max,
                'customers' => $customers
            ));

        $this->context->smarty->addTemplateDir(_PS_MODULE_DIR_ . $templatePath);
        $this->content .= $this->createTemplate('index.tpl')->fetch();

        ModuleAdminController::initContent();

    }

}
