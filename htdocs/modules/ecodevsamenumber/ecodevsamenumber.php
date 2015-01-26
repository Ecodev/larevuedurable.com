<?php

class EcodevSameNumber extends Module
{


    public function __construct()
    {
        $this->name = 'ecodevsamenumber';
        $this->tab = 'Ecodev';
        $this->version = '1.0';

        $this->_errors = array();

        parent::__construct();

        $this->displayName = $this->l("Au sommaire de ce numéro");
        $this->description = $this->l("");
    }

    public function install()
    {
        if (!parent::install() OR
            !$this->registerHook('displayProductTabContent') OR
            !$this->registerHook('displayProductTab')
        ) {
            return false;
        }

        return true;
    }


    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function hookDisplayProductTab($params)
    {
        $product = new Product((int) $params['product']->id);
        if (preg_match('/^([0-9]{3})|([0-9]{3}-[0-9]{3})$/', $product->reference)) {

            $revueNumber = substr($product->reference, 0, 3);
            //$sql = "select * from ps_product where reference = '" . $revueNumber . "' OR reference REGEXP '^" . $revueNumber . "-[0-9]{3}$'";

            $products = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT DISTINCT p.id_product, pl.name, pl.link_rewrite, p.reference, cl.link_rewrite category
				FROM '._DB_PREFIX_.'product p
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product'.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = product_shop.id_category_default'.Shop::addSqlRestrictionOnLang('cl').')
				WHERE pl.id_lang = '.(int)$this->context->language->id.'
					AND cl.id_lang = '.(int)$this->context->language->id.'
					AND product_shop.active = 1
					AND (p.reference = "'.$revueNumber.'" OR p.reference REGEXP \'^' . $revueNumber . '-[0-9]{3}$\')
				ORDER BY page asc');

            foreach($products as $key => $orderProduct){
                $products[$key]['link'] = $this->context->link->getProductLink((int)$orderProduct['id_product']);
            }

            if(count($products)>0){
                $this->smarty->assign('sameNumberProducts', $products);
                return $this->display(__FILE__, 'samenumbertab.tpl');
            }
        }
    }

    public function hookDisplayProductTabContent($params)
    {
        return $this->display(__FILE__, 'samenumber.tpl');
    }

}