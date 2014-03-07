<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class PrepaymentAccountModuleFrontController extends ModuleFrontController
{
    public $auth = true;
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

        // PrestaShop default medias
        $this->addCSS(_THEME_CSS_DIR_.'history.css');
        $this->addCSS(_THEME_CSS_DIR_.'addresses.css');
        $this->addJqueryPlugin('scrollTo');
        $this->addJS(array(
            _THEME_JS_DIR_.'history.js',
            _THEME_JS_DIR_.'tools.js')
        );

        // Custom medias
        $this->addCSS($this->checkMedia('css', 'account.css'));
    }

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $state = PrepaymentState::getByCustomer($this->context->customer->id);
        $balance = ($state && $state->id) ? $state->amount : 0;
        $this->context->smarty->assign(
            array(
                'idLang' => (int)$this->context->language->id,
                'currency' => $this->context->currency,
                'balance' => Tools::convertPrice($balance, (int)$this->context->currency->id),
                'amount' => PrepaymentProduct::getAll(),
                'history' => PrepaymentHistory::getByCustomer($this->context->customer->id),
                'invoiceAllowed' => (int)(Configuration::get('PS_INVOICE')),
            )
        );
        $this->setTemplate('account.tpl');
    }
}
