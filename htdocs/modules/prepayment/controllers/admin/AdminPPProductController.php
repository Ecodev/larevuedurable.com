<?php

/*
* DrÿSs' Agency
* © 2013 All rights reserved
* http://www.dryss.com
* contact@dryss.com
*/

class AdminPPProductController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'pp_product';
        $this->className = 'PrepaymentProduct';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->context = Context::getContext();
        $this->bulk_actions = array('delete' => '');

        $this->_select = '
            pl.`name` AS product,
            sa.`price`,
            (sa.`price` * ((100 + (t.`rate`))/100)) AS price_final';
        $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
                ON (pl.`id_product` = a.`id_product`) AND pl.`id_lang` = '.intval($this->context->language->id).' AND pl.`id_shop` = a.`id_shop`
            LEFT JOIN `'._DB_PREFIX_.'product_shop` sa
                ON (a.`id_product` = sa.`id_product`) AND sa.`id_shop` = a.`id_shop`
            LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr
                ON (sa.`id_tax_rules_group` = tr.`id_tax_rules_group` AND tr.`id_country` = '.intval($this->context->country->id).' AND tr.`id_state` = 0)
            LEFT JOIN `'._DB_PREFIX_.'tax` t
                ON (t.`id_tax` = tr.`id_tax`)';
        $this->_orderBy = 'price_final';
        $this->_orderWay = 'DESC';

        $this->fields_list = array(
            'id_product' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25,
            ),
            'price_final' => array(
                'title' => $this->l('Amount'),
                'align' => 'center',
                'havingFilter' => true,
                'orderby' => false,
                'callback' => 'printAmount',
                'width' => 90,
            ),
            'reduction' => array(
                'title' => $this->l('Offered'),
                'align' => 'center',
                'callback' => 'printReduction',
                'width' => 90,
            ),
            'product' => array(
                'title' => $this->l('Product'),
                'width' => 'auto',
                'filter_key' => 'pl!name',
            ),
        );

        $this->shopLinkType = 'shop';

        parent::__construct();
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        $orderByPriceFinal = (empty($orderBy) ? ($this->context->cookie->__get($this->table.'Orderby') ? $this->context->cookie->__get($this->table.'Orderby') : 'id_'.$this->table) : $orderBy);
        $orderWayPriceFinal = (empty($orderWay) ? ($this->context->cookie->__get($this->table.'Orderway') ? $this->context->cookie->__get($this->table.'Orderby') : 'ASC') : $orderWay);
        if ($orderByPriceFinal == 'price_final')
        {
            $orderBy = 'id_'.$this->table;
            $orderWay = 'ASC';
        }
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);

        /* update product quantity with attributes ...*/
        $nb = count($this->_list);
        if ($this->_list)
        {
            /* update product final price */
            for ($i = 0; $i < $nb; $i++)
            {
                // convert price with the currency from context
                $this->_list[$i]['price'] = Tools::convertPrice($this->_list[$i]['price'], $this->context->currency, true, $this->context);
                $this->_list[$i]['price_tmp'] = Product::getPriceStatic($this->_list[$i]['id_product'], true, null, 2, null, false, true, 1, true);
            }
        }

        if ($orderByPriceFinal == 'price_final')
        {
            if (strtolower($orderWayPriceFinal) == 'desc')
                uasort($this->_list, 'cmpPriceDesc');
            else
                uasort($this->_list, 'cmpPriceAsc');
        }
        for ($i = 0; $this->_list && $i < $nb; $i++)
        {
            $this->_list[$i]['price_final'] = $this->_list[$i]['price_tmp'];
            unset($this->_list[$i]['price_tmp']);
        }
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

        $this->toolbar_btn = array();
        $lists = parent::renderList();
        return $lists;
    }

    public function renderForm()
    {
        $obj = $this->loadObject(true);
        Tools::redirectAdmin('index.php?controller=adminproducts&id_product='.$obj->id_product.'&updateproduct&key_tab=ModulePrepayment&token='.Tools::getAdminTokenLite('AdminProducts'));
    }

    public function ajaxProcessSetProduct()
    {
        $value = intval(Tools::getValue('value'));
        $idProduct = intval(Tools::getValue('id_product'));

        $prepaymentProduct = PrepaymentProduct::getByProduct($idProduct);
        if ($prepaymentProduct === null && $value)
        {
            $prepaymentProduct = new PrepaymentProduct();
            $prepaymentProduct->id_product = $idProduct;
            $prepaymentProduct->active = true;
            $prepaymentProduct->save();
        }
        elseif ($prepaymentProduct && !$value)
            $prepaymentProduct->delete();
        $this->content = Tools::jsonEncode(array());
    }

    public function ajaxProcessSetReduction()
    {
        $reduction = floatval(Tools::getValue('value'));
        $idProduct = intval(Tools::getValue('id_product'));

        $prepaymentProduct = PrepaymentProduct::getByProduct($idProduct);
        if ($prepaymentProduct !== null && $reduction)
        {
            $prepaymentProduct->reduction = $reduction;
            if (!$prepaymentProduct->reduction_type)
                $prepaymentProduct->reduction_type = 'amount';
            $prepaymentProduct->save();
        }
        $this->content = Tools::jsonEncode(array());
    }

    public function ajaxProcessSetReductionType()
    {
        $type = strval(Tools::getValue('value'));
        $idProduct = intval(Tools::getValue('id_product'));

        $prepaymentProduct = PrepaymentProduct::getByProduct($idProduct);
        if ($prepaymentProduct !== null && $type)
        {
            $prepaymentProduct->reduction_type = $type;
            $prepaymentProduct->save();
        }
        $this->content = Tools::jsonEncode(array());
    }

    static public function printAmount($amount)
    {
        if ($amount <= 0)
            $color = '#DA0F00';
        else
            $color = '#5C9939';
        return '<span style="background-color: '.$color.'; color: white; border-radius: 3px 3px 3px 3px; font-size: 11px; padding: 2px 5px;">'.Tools::displayPrice($amount).'</span>';
    }

    static public function printReduction($reduction, $tr)
    {
        if ($reduction > 0)
        {
            $prepaymentProduct = new PrepaymentProduct($tr['id_pp_product']);
            $amount = $prepaymentProduct->getReduction($tr['price_final'], Context::getContext()->currency->id);
            $color = '#3B5998';
            return '<span style="background-color: '.$color.'; color: white; border-radius: 3px 3px 3px 3px; font-size: 11px; padding: 2px 5px;">'.Tools::displayPrice($amount).'</span>';
        }
        return '-';
    }
}