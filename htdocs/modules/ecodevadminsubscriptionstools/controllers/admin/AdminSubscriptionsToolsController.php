<?php

class AdminSubscriptionsToolsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->className = 'AdminSubscriptions';
        $this->context = Context::getContext();

        parent::__construct();
    }

    public function numberOfWebSubscribers()
    {
        $subscribers = Customer::getAllSubscribers();

        $archiveOnly = [];
        $paperOnly = [];
        $paperAndArchive = [];

        $standard = [];
        $pro = [];
        $soli = [];

        $active = [];
        $future = [];
        $past = [];

        $all = [];

        foreach ($subscribers as $customer) {
            $customer = new Customer($customer['ID']);
            $customer->manageSubscriptions();

            foreach ($customer->user_subscriptions as $sub) {
                if ($sub->is_archive && !$sub->is_paper) {
                    array_push($archiveOnly, $sub);
                }
                if ($sub->is_paper && !$sub->is_archive) {
                    array_push($paperOnly, $sub);
                }
                if ($sub->is_paper && $sub->is_archive) {
                    array_push($paperAndArchive, $sub);
                }

                if ($sub->product_id == _ABONNEMENT_PARTICULIER_) {
                    array_push($standard, $sub);
                }
                if ($sub->product_id == _ABONNEMENT_INSTITUT_) {
                    array_push($pro, $sub);
                }
                if ($sub->product_id == _ABONNEMENT_SOLIDARITE_) {
                    array_push($soli, $sub);
                }

                if ($sub->is_active) {
                    array_push($active, $sub);
                }
                if ($sub->is_future) {
                    array_push($future, $sub);
                }
                if (!$sub->is_active && !$sub->is_future) {
                    array_push($past, $sub);
                }

                array_push($all, $sub);
            }
        }

        $this->context->smarty->assign(array(
            'all' => $all,
            'archiveOnly' => $archiveOnly,
            'paperOnly' => $paperOnly,
            'paperAndArchive' => $paperAndArchive,
            'standard' => $standard,
            'pro' => $pro,
            'soli' => $soli,
            'active' => $active,
            'future' => $future,
            'past' => $past,
        ));

    }

    public function initContent()
    {

        if (isset($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
            $this->$action();
        }

        $this->context->smarty->assign(array(
                'currentTab' => $this,
            ));

        $this->content .= $this->createTemplate('tools.tpl')->fetch();

        parent::initContent();

    }

}
