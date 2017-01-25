<?php

class AbstractAdminSubscriptionsController extends ModuleAdminController
{

    public $name = 'ecosubscriptions';

    public function __construct()
    {
        $this->context = Context::getContext();

        parent::__construct();
    }

    public function initContent()
    {
        if (isset($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
            $this->$action();
        }

        $this->context->smarty->assign('currentTab', $this);
        $this->content .= $this->createTemplate('index.tpl')->fetch();

        parent::initContent();
    }

}
