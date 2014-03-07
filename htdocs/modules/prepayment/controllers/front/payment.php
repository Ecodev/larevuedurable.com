<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PrepaymentPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function checkMedia($type, $filePath)
    {
        if (file_exists(_PS_THEME_DIR_.$type.'/modules/prepayment/'.$filePath))
            return _THEMES_DIR_._THEME_NAME_.'/'.$type.'/modules/prepayment/'.$filePath;
        return _MODULE_DIR_.'prepayment/views/templates/front/'.$filePath;
    }

    public function setMedia()
    {
        parent::setMedia();

        // Custom medias
        $this->addCSS($this->checkMedia('css', 'paymentConfirm.css'));
    }

    public function initContent()
    {
        // Check if there is some amount products in cart
        $products = $this->context->cart->getProducts();
        foreach ($products as $product)
            if (PrepaymentProduct::getByProduct($product['id_product']))
                header('Location: '.Context::getContext()->link->getPageLink('order'));

        $this->display_column_left = false;
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart))
            Tools::redirect('index.php?controller=order');

        $state = PrepaymentState::getByCustomer($this->context->customer->id);

        $balance = ($state && $state->id) ? Tools::convertPrice($state->amount, (int)$this->context->currency->id) : 0;
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $balanceEnd = $balance - $total;

        $canBuy = true;
        if ($balanceEnd < 0)
            $canBuy = Configuration::get('PP_NEGATIVE_ENABLED') ? ($balanceEnd >= (0 - floatval(Configuration::get('PP_NEGATIVE_MAX'))) ? true : false) : false;

        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currentCurrency' => (int)$this->context->currency->id,
            'currencies' => $this->module->getCurrency((int)$this->context->currency->id),
            'state' => $state,
            'balance' => Tools::convertPrice($balance, (int)$this->context->currency->id),
            'total' => $total,
            'balanceEnd' => $balanceEnd,
            'canBuy' => $canBuy,
            'isoCode' => $this->context->language->iso_code,
            'this_path' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
        ));

        $this->setTemplate('paymentConfirm.tpl');
    }
}
