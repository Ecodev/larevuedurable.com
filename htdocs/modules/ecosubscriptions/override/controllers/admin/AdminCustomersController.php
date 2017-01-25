<?php

/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminCustomersController extends AdminCustomersControllerCore
{

    public function renderView()
    {
        if (!($customer = $this->loadObject())) {
            return;
        }

        $customer->manageSubscriptions();

        return parent::renderView();
    }

    /**
     * Update the customer exclusion from follow up
     * @return void
     */
    public function ajaxProcessUpdateCustomerExclusionFromRemind()
    {
        if ($this->tabAccess['edit'] === '1') {
            $val = (int) Tools::getValue('excludeFromRemind');

            $customer = new Customer((int) Tools::getValue('id_customer'));

            if (!Validate::isLoadedObject($customer)) {
                die ('error:update');
            }
            if ($val !== 0 && $val !== 1) {
                die ('error:validation');
            }

            $customer->excludeFromRemind = $val;

            if (!$customer->update()) {
                die ('error:update');
            }

            die('ok');
        }
    }

    public function ajaxProcessIgnoreSubscription() {
        $ignore = (bool) Tools::getValue('ignore');
        $order = new Order((int) Tools::getValue('order'));
        $order->ignore_sub = $ignore;
        $result = $order->save();

        if (!$result) {
            die ('ko');
        }

        die('ok');
    }

}
