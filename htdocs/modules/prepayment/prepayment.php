<?php

/*
* Prepayment module
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*
* This module is for PrestaShop (http://www.prestashop.com), free open-source e-commerce application.
*
* NOTICE OF LICENSE
*
* This source file is subject of commerciale license.
* The Prepayment module is a one-use license.
*
* Reproduction in whole or part of this module, of one or more of its components,
* using any process, and without our express permission, is forbidden.
*
* If you did not receive this module from us, please contact us.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Prepayment to newer versions in the future.
* If you wish to customize Prepayment for your needs please contact us.
*
* INFORMATION
*
* Please referrer to user guide about installation/configuration/help use (PDF file).
* For any bugs, please contact us, we make free patch.
* We often provide update for this module, with brand new features, please contact us for more informations.
*
* @author DrÿSs' Agency <contact@dryss.com>
* @copyright 2013 DrÿSs' Agency
* @version 0.7
* International Registered Trademark & Property of DrÿSs' Agency
*/

// Security
if (!defined('_PS_VERSION_'))
	exit;

// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
	define('_MYSQL_ENGINE_', 'MyISAM');

// Include auto-loader
include_once(dirname(__FILE__).'/models/PPAutoLoad.php');
spl_autoload_register(array(PPAutoLoad::getInstance(), 'load'));

class Prepayment extends PPHooks
{
    protected static $instance = null;

    public function __construct()
    {
        $this->name = 'prepayment';
        $this->version = '0.7';
        $this->tab = 'payments_gateways';
        $this->author = "DrÿSs' Agency";
        $this->url = "http://www.dryss.com";
        $this->email = "contact@dryss.com";
        $this->year = '2013';
        $this->module_key = '63d25a7458a9f299857d3f0ad8affcd2';
        $this->ps_versions_compliancy['min'] = '1.5.0.1';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = $this->l('Prepayment');
        $this->description = $this->l('Prepayment solution - Prepaid account paiement');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        if (!count(Currency::checkPaymentCurrencies($this->id)))
            $this->warning = $this->l('No currency set for this module');

        $this->prefixConfiguration = 'PP_';
        $this->file = __FILE__;
    }

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new Prepayment();
        return self::$instance;
    }

    ################
    # INSTALLATION #
    ################

    public function install()
    {
        if (!parent::install() || !PPInstaller::getInstance($this)->install())
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !PPInstaller::getInstance($this)->uninstall())
            return false;
        return true;
    }

    ##########
    # COMMON #
    ##########

    public function checkCurrency($cart)
    {
        $currency_order = Currency::getCurrencyInstance((int)$cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;
        return false;
    }
}
