<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PrepaymentValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
            Tools::redirect('index.php?controller=order&step=1');

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
        if ($module['name'] == 'prepayment')
        {
            $authorized = true;
            break;
        }
        if (!$authorized)
            die($this->module->l('This payment method is not available.', 'validation'));

        // Check customer
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        // Check if there is some amount products in cart
        $products = $this->context->cart->getProducts();
        foreach ($products as $product)
            if (PrepaymentProduct::getByProduct($product['id_product']))
                header('Location: '.Context::getContext()->link->getPageLink('order'));

        // Balance calculation
        $state = PrepaymentState::getByCustomer($this->context->customer->id);
        $balance = ($state && $state->id) ? Tools::convertPrice($state->amount, (int)$this->context->currency->id) : 0;
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $balanceEnd = $balance - $total;

        // Check if enough money
        $canBuy = true;
        if ($balanceEnd < 0)
            $canBuy = Configuration::get('PP_NEGATIVE_ENABLED') ? ($balanceEnd >= floatval(Configuration::get('PP_NEGATIVE_MAX')) ? true : false) : false;
        if (!$canBuy)
            header('Location: '.Context::getContext()->link->getPageLink('order'));

        // Validate order
        $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, NULL, array(), (int)$this->context->currency->id, false, $customer->secure_key);

        // Add prepayment history (auto update balance)
        $history = new PrepaymentHistory();
        $history->id_customer = (int)$customer->id;
        $history->id_currency = (int)$this->context->currency->id;
        $history->id_order = (int)$this->module->currentOrder;
        $history->date = date('Y-m-d H:i:s', time());
        $history->amount = (float)(0 - $total);
        $history->save();

        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
    }
}
