<?php

/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*         DISCLAIMER   *
* *************************************** */
/* Do not edit or add to this file if you wish to upgrade Prestashop to newer
* versions in the future.
* ****************************************************
* @category   Belvg
* @package    belvg_giftcert
* @author     Dzianis Yurevich (dzianis.yurevich@gmail.com)
* @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
* @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
*/

if (!defined('_PS_VERSION_'))
{
    exit;
}

//require_once _PS_MODULE_DIR_ . 'ecodevadminsubscriptions/classes/AdminSubscriptions.php';

class EcodevAdminSubscriptionsTools extends Module
{

    public function __construct()
    {
        $this->name = 'ecodevadminsubscriptionstools';
        $this->tab = 'ecodev';
        $this->version = '1.0.0';
        $this->module_key = 'b86c292c60b0f3fe8c3d4ab03881f271';

        parent::__construct();

        $this->displayName = 'Outils abonnements';
    }

    public function install()
    {

        $adminTab = Tab::getIdFromClassName('AdminSubscriptions');

        $tab = new Tab();
        $tab->name[1] = 'Outils';
        $tab->class_name = 'AdminSubscriptionsTools';
        $tab->id_parent = $adminTab;
        $tab->module = $this->name;
        $tab->add();

        return parent::install();
    }


}

