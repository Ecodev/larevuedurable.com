<?php

require_once(dirname(__FILE__) . '/../../autoload.php');

class AdminSubscriptionsReminderController extends AbstractAdminSubscriptionsController
{

    public static $configActivate = 'REMINDER_ACTIVE';

    public function __construct()
    {
        $this->className = 'AdminSubscriptionsReminder';

        parent::__construct();
    }

    public function initContent()
    {
        $all = array_map(function ($p) {
            return (int) $p['reference'];
        }, Product::getAllRegisteredMagazines());

        $next = ((int) Product::getLastMagazineReleased()['reference']) + 1;
        $selected = (int) Tools::getValue('num', $next);

        $this->context->smarty->assign([
            'numbers' => $all,
            'isActive' => $this->isActive(),
            'selected' => $selected,
            'nextRemindDate' => Subscription::getNextRemindDate(),
            'next' => (int) $next,
            'nextRelease' => Product::getParutionDateByRef(strlen($next) == 2 ? '0' . $next : $next)
        ]);

        parent::initContent();
    }

    public function test()
    {
        $reminder = new Reminder();
        $reminds = $reminder->getSubscribersToRemind((int) Tools::getValue('num'));

        $this->context->smarty->assign([
            'reminds' => $reminds,
            'test' => true
        ]);
    }

    public function send()
    {
        $reminder = new Reminder();
        $reminder->send((int) Tools::getValue('num'));

        $this->confirmations[] = 'Campagne envoyée';
    }


    private function isActive()
    {
        return (bool) Configuration::get($this->context->controller->module->prefixConfiguration . self::$configActivate);
    }

    public function toggleActivation()
    {
        Configuration::updateValue($this->context->controller->module->prefixConfiguration . self::$configActivate, !$this->isActive());
        $this->context->smarty->assign([
            'isActive' => $this->isActive(),
        ]);
    }

    public function showMailchimp() {
        $reminder = new Reminder();
        $this->context->smarty->assign('mc', $reminder->echoData());
    }

}
