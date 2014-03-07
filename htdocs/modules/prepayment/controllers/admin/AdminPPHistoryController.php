<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class AdminPPHistoryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'pp_history';
        $this->className = 'PrepaymentHistory';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->context = Context::getContext();
        $this->bulk_actions = array('delete' => '');

        $this->_select = '
            c.`id_gender` AS id_gender,
            c.`lastname` AS lastname,
            c.`firstname` AS firstname,
            c.`email` AS email';
        $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'customer` c
                ON (c.`id_customer` = a.`id_customer`) AND c.id_shop = a.`id_shop`';
        $this->_orderBy = 'date';
        $this->_orderWay = 'DESC';

        $genders = array();
        $genders_icon = array('default' => 'unknown.gif');
        foreach (Gender::getGenders() as $gender)
        {
            $gender_file = 'genders/'.$gender->id.'.jpg';
            if (file_exists(_PS_IMG_DIR_.$gender_file))
                $genders_icon[$gender->id] = '../'.$gender_file;
            else
                $genders_icon[$gender->id] = $gender->name;
            $genders[$gender->id] = $gender->name;
        }

        $this->fields_list = array(
            'id_pp_history' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 20,
            ),
            'date' => array(
                'title' => $this->l('Date'),
                'width' => 150,
                'type' => 'datetime',
                'align' => 'center',
            ),
            'amount' => array(
                'title' => $this->l('Amount'),
                'align' => 'center',
                'callback' => 'printAmount',
                'width' => 90,
            ),
            'id_gender' => array(
                'title' => $this->l('Titles'),
                'width' => 70,
                'align' => 'center',
                'icon' => $genders_icon,
                'orderby' => false,
                'type' => 'select',
                'list' => $genders,
                'filter_key' => 'a!id_gender',
            ),
            'lastname' => array(
                'title' => $this->l('Last Name'),
                'width' => 'auto',
            ),
            'firstname' => array(
                'title' => $this->l('First name'),
                'width' => 'auto',
            ),
            'email' => array(
                'title' => $this->l('E-mail address'),
                'width' => 'auto',
            ),
        );

        $this->shopLinkType = 'shop';

        parent::__construct();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
                )
            );

        $lists = parent::renderList();
        return $lists;
    }

    public function initToolbar()
    {
        $obj = $this->loadObject(true);

        if ($this->tabAccess['edit'] && ($this->display == 'edit' || $this->display == 'add'))
        {
            if ($this->display == 'edit' && $obj->id)
            {
                if ($this->tabAccess['delete'])
                    $this->toolbar_btn['delete'] = array(
                        'short' => 'Delete',
                        'href' => $this->context->link->getAdminLink('AdminPPHistory').'&amp;id_pp_history='.(int)$obj->id.'&amp;deletepp_history',
                        'desc' => $this->l('Delete this history'),
                        'confirm' => 1,
                        'js' => 'if (confirm(\''.$this->l('Delete history?').'\')){return true;}else{event.preventDefault();}'
                    );
            }

            $this->toolbar_btn['save'] = array(
                'short' => 'Save',
                'href' => '#',
                'desc' => $this->l('Save'),
            );

            $this->toolbar_btn['save-and-stay'] = array(
                'short' => 'SaveAndStay',
                'href' => '#',
                'desc' => $this->l('Save and stay'),
            );
        }

        parent::initToolbar();
    }

    public function renderForm()
    {
        parent::renderForm();

        $idLang = (int)$this->context->language->id;

        // Object
        $obj = $this->loadObject(true);
        if (!$obj->id)
            $obj->active = true;

        $imgPath = 'prepayment/medias/img/';
        $templatePath = 'prepayment/views/templates/admin/pp_history/';

        // CSS/JS
        $this->context->controller->addCSS(_MODULE_DIR_.$templatePath.'form.css');
        $this->context->controller->addJS(_MODULE_DIR_.$templatePath.'form.js');
        $this->addJqueryPlugin(array('typewatch'));
        $this->addJqueryUI('ui.datepicker');

        $this->context->smarty->assign(
            array(
                'imgPath' => _MODULE_DIR_.$imgPath,
                'show_toolbar' => true,
                'toolbar_btn' => $this->toolbar_btn,
                'toolbar_scroll' => $this->toolbar_scroll,
                'title' => array(
                    $this->l('Prepayment'),
                    $this->l('History'),
                    (!$obj->id ? $this->l('Create') : $this->l('Edit').'&nbsp;(#'.$obj->id.')')
                ),
                'id_lang' => $idLang,
                'currentTab' => $this,
                'currentToken' => $this->token,
                'currentObject' => $obj,
                'currency' => ($obj->id ? Currency::getCurrencyInstance((int)$obj->id_currency) : $this->context->currency),
                'customer' => new Customer(intval(Tools::getValue('id_customer', $obj->id_customer))),
                'back_customer' => intval(Tools::getValue('back_customer')),
            )
        );
        $this->context->smarty->addTemplateDir(_PS_MODULE_DIR_.$templatePath);
        $this->content .= $this->createTemplate('form.tpl')->fetch();
    }

    public function processAdd()
    {
        $_POST['date'] = date('Y-m-d H:i:s', time());
        $_POST['id_currency'] = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
        if (Tools::getValue('amount') == 0)
            unset($_POST['amount']);
        $res = parent::processAdd();
        if ($res !== false && Tools::getValue('back_customer') && !Tools::isSubmit('submitAdd'.$this->table.'AndStay'))
        {
            $obj = new PrepaymentHistory(intval(Tools::getValue('id_pp_history')));
            $this->redirect_after = '?controller=AdminCustomers&viewcustomer&id_customer='.$obj->id_customer.'&conf=3&token='.Tools::getAdminTokenLite('AdminCustomers');
        }
        return $res;
    }

    public function processUpdate()
    {
        $obj = new PrepaymentHistory(intval(Tools::getValue('id_pp_history')));
        $_POST['id_customer'] = $obj->id_customer;
        $_POST['id_currency'] = $obj->id_currency;
        $_POST['id_order'] = $obj->id_order;
        $_POST['date'] = $obj->date;
        $res = parent::processUpdate();
        if ($res !== false && Tools::getValue('back_customer') && !Tools::isSubmit('submitAdd'.$this->table.'AndStay'))
            $this->redirect_after = '?controller=AdminCustomers&viewcustomer&id_customer='.$obj->id_customer.'&conf=4&&token='.Tools::getAdminTokenLite('AdminCustomers');
        return $res;
    }

    public function processDelete()
    {
        if (Tools::getValue('back_customer'))
        {
            $obj = new PrepaymentHistory(intval(Tools::getValue('id_pp_history')));
            $redirect_after = '?controller=AdminCustomers&viewcustomer&id_customer='.$obj->id_customer.'&conf=1&token='.Tools::getAdminTokenLite('AdminCustomers');
        }
        $res = parent::processDelete();
        if (isset($redirect_after) && $res !== false)
            $this->redirect_after = $redirect_after;
        return $res;
    }

    public function ajaxProcessSearchCustomers()
    {
        if ($customers = Customer::searchByName(pSQL(Tools::getValue('customer_search'))))
        {
            $to_return = array(
                'customers' => $customers,
                'found' => true
            );
        }
        else
            $to_return = array('found' => false);

        $this->content = Tools::jsonEncode($to_return);
    }

    static public function printAmount($amount, $tr)
    {
        $currencyId = intval($tr['id_currency']);
        if ($amount == 0)
            $color = '#3B5998';
        elseif ($amount <= 0)
            $color = '#DA0F00';
        else
            $color = '#5C9939';
        return '<span style="background-color: '.$color.'; color: white; border-radius: 3px 3px 3px 3px; font-size: 11px; padding: 2px 5px;">'.Tools::displayPrice($amount, $currencyId).'</span>';
    }
}