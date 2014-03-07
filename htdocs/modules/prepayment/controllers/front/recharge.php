<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PrepaymentRechargeModuleFrontController extends ModuleFrontController
{
    public $auth = true;
    public $ssl = true;

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $redirect = Context::getContext()->link->getModuleLink('prepayment', 'account', array(), true);

        // Check prepayment product
        $idPPProduct = intval(Tools::getValue('amount'));
        $ppp = new PrepaymentProduct($idPPProduct);
        if (!$idPPProduct || !$ppp || !$ppp->id || !$ppp->id_product)
            header('Location: '.$redirect);

        // Add product to cart
        $this->context->cart->save();
        $this->context->cart->updateQty(1, $ppp->id_product);
        $this->context->cookie->id_cart = $this->context->cart->id;

        header('Location: '.Context::getContext()->link->getPageLink('order'));
    }
}
