<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class AdminPPStateController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'pp_state';
        $this->className = 'PrepaymentState';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->context = Context::getContext();
        $this->bulk_actions = array();

        $this->_select = '
            c.`id_gender` AS id_gender,
            c.`date_add` AS date_add,
            c.`lastname` AS lastname,
            c.`firstname` AS firstname,
            c.`email` AS email,
            (
                SELECT c.`date_add` FROM '._DB_PREFIX_.'guest g
                LEFT JOIN '._DB_PREFIX_.'connections c ON c.`id_guest` = g.`id_guest`
                WHERE g.`id_customer` = c.`id_customer`
                ORDER BY c.`date_add` DESC
                LIMIT 1
            ) as `connect`';
        $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'customer` c
                ON (c.`id_customer` = a.`id_customer`) AND c.`id_shop` = c.`id_shop` = a.`id_shop`';
        $this->_orderBy = 'id_pp_state';
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
            'id_customer' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 20,
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
            'date_add' => array(
                'title' => $this->l('Registration'),
                'width' => 150,
                'type' => 'date',
                'align' => 'center',
            ),
            'connect' => array(
                'title' => $this->l('Last visit'),
                'width' => 150,
                'type' => 'datetime',
                'search' => false,
                'havingFilter' => true,
                'align' => 'center',
            ),
        );

        $this->shopLinkType = 'shop';

        parent::__construct();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->toolbar_btn = array();
        $lists = parent::renderList();
        return $lists;
    }

    public function renderForm()
    {
        $obj = $this->loadObject(true);
        Tools::redirectAdmin('index.php?controller=admincustomers&id_customer='.$obj->id_customer.'&viewcustomer&token='.Tools::getAdminTokenLite('AdminCustomers'));
    }

    static public function printAmount($_ /* amount */, $tr)
    {
        $state = new PrepaymentState(intval($tr['id_pp_state'])); /* used to refresh state currency */
        $amount = $state->amount;

        if ($amount == 0)
        {
            $color = '#3B5998';
            $amount = 0;
        }
        elseif ($amount <= 0)
            $color = '#DA0F00';
        else
            $color = '#5C9939';
        return '<span style="background-color: '.$color.'; color: white; border-radius: 3px 3px 3px 3px; font-size: 11px; padding: 2px 5px;">'.Tools::displayPrice($amount).'</span>';
    }
}