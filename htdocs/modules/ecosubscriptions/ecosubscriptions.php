<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/autoload.php');

class EcoSubscriptions extends EcoHooks
{
    public function __construct()
    {
        $this->name = 'ecosubscriptions';
        $this->tab = 'ecodev';
        $this->version = '1.1';

        $this->_errors = array();

        parent::__construct();

        $this->prefixConfiguration = 'ECO_LRD_';
        $this->displayName = $this->l("Abonnements");
        $this->description = $this->l("Permet de gérer les abonnements au travers d'outils dédiés.");
    }

    public function install()
    {

        $ecoInstaller = new EcoInstaller($this);

        if (!parent::install()) {
            $this->context->controller->errors[] = "Prestashop n'a pas pu installer le module correctement";
            $this->context->controller->errors = array_merge($this->context->controller->errors, $ecoInstaller->errors);

            // $this->_errors[] = $msg; // ps 1.6 ?

            return false;
        }

        if (!$ecoInstaller->install()) {
            $this->context->controller->errors[] = "EcoInstaller n'a pas pu installer le module correctement";
            $this->context->controller->errors = array_merge($this->context->controller->errors, $ecoInstaller->errors);

            // $this->_errors[] = $msg; // ps 1.6 ?

            $this->uninstall();

            return false;
        }

        // Uninstall modules that are replaced by this new one
        $modules = ['ecodevadminsubscriptions', 'ecodevadminsubscriptionstools', 'ecodevproductsorting', 'ecodevsamenumber', 'ecodevsubscription', 'ecodevscripts'];
        foreach($modules as $module) {
            /** @var $module ModuleCore */
            $module = Module::getInstanceByName($module);

            if ($module && $module->active) {
                $module->uninstall();
            }
        }

        return true;
    }

    public function uninstall()
    {
        $ecoInstaller = new EcoInstaller($this);

        $customers = Customer::getCustomers();
        foreach($customers as $customer) {
            $customer = new Customer($customer['id_customer']);
            $customer->unsubscribe();
        }

        if (!$ecoInstaller->uninstall()) {
            $this->_errors[] = "EcoInstaller n'a pas pu désinstaller le module correctement";
            $this->context->controller->errors = array_merge($this->context->controller->errors, $ecoInstaller->errors);

            return false;
        }

        if (!parent::uninstall()) {
            $this->_errors[] = "Prestashop n'a pas pu désinstaller le module correctement";
            $this->context->controller->errors = array_merge($this->context->controller->errors, $ecoInstaller->errors);

            return false;
        }

        return true;
    }

}
