<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PPHooks extends PaymentModule
{
    public function checkMedia($src, $type, $filePath)
    {
        if (file_exists(_PS_THEME_DIR_.$type.'/modules/prepayment/'.$filePath))
            return _THEMES_DIR_._THEME_NAME_.'/'.$type.'/modules/prepayment/'.$filePath;
        return _MODULE_DIR_.'prepayment/views/templates/hook/'.$src.'/'.$filePath;
    }

    public function checkTemplate($src, $template)
    {
        if (file_exists(_PS_THEME_DIR_.'modules/prepayment/'.$template))
            return $template;
        return $src.'/'.$template;
    }

    ######
    # FO #
    ######

     public function hookDisplayCustomerAccount($_ /* params */)
    {
        $this->smarty->assign(array(
            'imgPath' => _MODULE_DIR_.'prepayment/medias/img/',
        ));
        return $this->display($this->file, $this->checkTemplate('front', 'customerAccount.tpl'));
    }

     public function hookMyAccountBlock($_ /* params */)
    {
        return $this->display($this->file, $this->checkTemplate('front', 'myAccountBlock.tpl'));
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->checkMedia('front', 'css', 'header.css'));
    }

    public function displayColumn()
    {
        if (!$this->context->customer->isLogged())
            return ;
        $state = PrepaymentState::getByCustomer($this->context->customer->id);
        $balance = ($state && $state->id) ? $state->amount : 0;

        $this->smarty->assign(array(
            'imgPath' => _MODULE_DIR_.'prepayment/medias/img/',
            'balance' => Tools::convertPrice($balance),
        ));
        return $this->display($this->file, $this->checkTemplate('front', 'columnBlock.tpl'));
    }

    public function hookDisplayRightColumn()
    {
        if ((int)Configuration::get('PP_BLOCK_DISPLAY') !== 3)
            return ;
        return $this->displayColumn();
    }

    public function hookDisplayLeftColumn()
    {
        if ((int)Configuration::get('PP_BLOCK_DISPLAY') !== 2)
            return ;
        return $this->displayColumn();
    }

//    public function hookDisplayProductButtons($params)
//    {
//        if (!$this->context->customer->isLogged())
//            return ;
//        $cart = $params['cart'];
//        $idProduct = intval(Tools::getValue('id_product'));
//        $idCustomer = intval((int)$params['cookie']->id_customer);
//        $idLang = (int)$this->context->language->id;
//
//        // Check if there is some amount products in cart
//        $products = $cart->getProducts();
//        foreach ($products as $product)
//            if (PrepaymentProduct::getByProduct($product['id_product']))
//                return ;
//
//        // Check if product is an amount one
//        if (PrepaymentProduct::getByProduct($idProduct))
//            return ;
//
//        $this->smarty->assign(array(
//            'productParams' => array('id_product' => $idProduct)
//        ));
//        return $this->display($this->file, $this->checkTemplate('front', 'productButtons.tpl'));
//    }

    ######
    # BO #
    ######

    public function hookDisplayAdminProductsExtra($_ /* params */)
    {
    
        $idProduct = (int)Tools::getValue('id_product');

        // Prepayment duration
        $prepaymentProduct = PrepaymentProduct::getByProduct($idProduct);

        // CSS/JS
        $js =  Media::getJSPath($this->checkMedia('admin', 'js', 'productsExtra.js'));
        $css = array_keys(Media::getCSSPath($this->checkMedia('admin', 'css', 'productsExtra.css')));

        $this->smarty->assign(array(
            'ppproductToken' => Tools::getAdminTokenLite('AdminPPProduct'),
            'imgPath' => _MODULE_DIR_.'prepayment/medias/img/',
            'css_file' => $css[0],
            'js_file' => $js,
            'prepaymentProduct' => $prepaymentProduct,
            'product' => new Product($idProduct),
            'currency' => $this->context->currency,
        ));
        return $this->display($this->file, $this->checkTemplate('admin', 'productsExtra.tpl'));
    }

    public function hookDisplayAdminCustomers($_ /* params */)
    {
        $idCustomer = (int)Tools::getValue('id_customer');

        // CSS/JS
        $this->context->controller->addCSS($this->checkMedia('admin', 'css', 'customersView.css'));
        $this->context->controller->addJS($this->checkMedia('admin', 'js', 'customersView.js'));

        $state = PrepaymentState::getByCustomer($this->context->customer->id);
        $balance = ($state && $state->id) ? $state->amount : 0;

        $this->smarty->assign(array(
            'ppHistoryToken' => Tools::getAdminTokenLite('AdminPPHistory'),
            'imgPath' => _MODULE_DIR_.'prepayment/medias/img/',
            'id_customer' => $idCustomer,
            'idCurrency' => (int)$this->context->currency->id,
            'balance' => Tools::convertPrice($balance, (int)$this->context->currency->id),
            'history' => PrepaymentHistory::getByCustomer($this->context->customer->id),
        ));
        return $this->display($this->file, $this->checkTemplate('admin', 'customersView.tpl'));
    }

    ##########
    # ACTION #
    ##########

    public function hookActionPaymentConfirmation($params)
    {
    
    	$message = 'asdf';
    	error_log($message.chr(10).__LINE__.", ".__FILE__.chr(10).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/logs/_debug_error_log.txt');
        $order = new Order(intval($params['id_order']));
        $products = $order->getProducts();

        foreach ($products as $product)
        {
            // Credit prepaypement account if product has amount prepayment
            $ppp = PrepaymentProduct::getByProduct($product['product_id']);
            if ($ppp && $ppp->id_product)
            {
                $history = new PrepaymentHistory();
                $history->amount = $product['total_price_tax_incl'];
                if ($ppp->reduction > 0)
                    $history->amount += $ppp->getReduction($history->amount, $order->id_currency);
                $history->id_order = $order->id;
                $history->id_customer = $order->id_customer;
                $history->id_currency = $order->id_currency;
                $history->date = date('Y-m-d H:i:s', time());
                $history->add();
            }
        }
        return true;
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $order = new Order($params['id_order']);
        $previousState = new OrderState($order->getCurrentState());
        $newState = $params['newOrderStatus'];
        if ($previousState->logable && !$newState->logable)
        {
            // Delete linked Prepayment history
            $history = PrepaymentHistory::getByOrder($params['id_order']);
            foreach ($history as $hist)
                $hist->delete();
        }
    }

    ###########
    # PAYMENT #
    ###########

    public function hookPayment($params)
    {
        if (!$this->active)
            return;
        if (!$this->checkCurrency($params['cart']))
            return;

        // Check if there is some amount products in cart
        $products = $this->context->cart->getProducts();
        foreach ($products as $product)
            if (PrepaymentProduct::getByProduct($product['id_product']))
                return ;

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        return $this->display($this->file, $this->checkTemplate('front', 'payment.tpl'));
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;

        $state = $params['objOrder']->getCurrentState();
        if ($state == Configuration::get('PS_OS_PAYMENT'))
            $this->smarty->assign('status', 'ok');
        else
            $this->smarty->assign('status', 'failed');
        return $this->display($this->file, $this->checkTemplate('front', 'paymentReturn.tpl'));
    }
}
