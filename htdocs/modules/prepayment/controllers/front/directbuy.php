<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PrepaymentDirectbuyModuleFrontController extends ModuleFrontController
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

        $redirectAccount = Context::getContext()->link->getModuleLink('prepayment', 'account', array(), true);
        $redirectPayment = Context::getContext()->link->getModuleLink('prepayment', 'payment', array(), true);

        // Check product
        $idProduct = intval(Tools::getValue('id_product'));
        $product = new Product($idProduct);
        if (!$idProduct || !$product || !$product->id)
            header('Location: '.$redirectAccount);

        // Check if product is an amount one
        if (PrepaymentProduct::getByProduct($idProduct))
            header('Location: '.$redirectAccount);

        // Delete all products in cart
        $products = $this->context->cart->getProducts();
        foreach ($products as $product)
            if (!$this->context->cart->deleteProduct($product['id_product']))
                header('Location: '.$redirectAccount);

        // Add product to cart
        $this->context->cart->save();
        $this->context->cart->updateQty(1, $idProduct);
        $this->context->cookie->id_cart = $this->context->cart->id;

        header('Location: '.$redirectPayment);
    }
}