<?php

class EcoHooks extends Module
{

    public function hookDisplayHeader()
    {
        (new LocalSubscriptionsController($this, $this->context))->header();

        $this->smarty->assign([
            'isWebSubscriber' => $this->context->customer->getActiveWebSubscription() !== null
        ]);

        return $this->display($this->name, 'header.tpl');
    }

    public function hookDisplayAdminOrder($params) {

        $customer = null;

        if (isset($params['id_order'])) {
            $order = new Order($params['id_order']);
        } else {
            return '';
        }

        $customers = Customer::getCustomers();
        usort($customers, function($a, $b) {
            return strcmp(trim($a['lastname']), trim($b['lastname']));
        });

        $this->smarty->assign([
            'order' => $order,
            'customers' => $customers,
            'moduleName' => $this->name
        ]);

        return $this->display($this->name, 'adminOrder.tpl');
    }

    public function hookDisplayAdminCustomers($params)
    {
        $customer = null;
        if (isset($params['id_customer'])) {
            $customer = new Customer($params['id_customer']);
        } else {
            return '';
        }

        $customer->manageSubscriptions();

        $this->smarty->assign([
            'customer' => $customer,
            'moduleName' => $this->name
        ]);

        return $this->display($this->name, 'adminCustomer.tpl');
    }

    /**
     * When order status change, update to persist the right customer group
     * @param $params
     */
    public function hookActionOrderStatusPostUpdate($params)
    {
        $customer = new Customer($params['cart']->id_customer);
        $customer->manageSubscriptions();
    }

    /**
     * On product add, update page and edition on database
     * @param $params
     */
    public function hookActionProductAdd($params)
    {
        $createparams['id_product'] = $params['product']->id;
        $this->hookActionProductSave($createparams);
    }

    /**
     * On product update, update page and edition on database
     * @param $params
     */
    public function hookActionProductSave($params)
    {
        $product = new Product($params['id_product']);

        $numero = (int) substr($product->reference, 0, 3);
        $page = (int) substr_replace($product->reference, '', 0, 4);
        $pid = $product->id;

        DB::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'product set page="' . $page . '", numero="' . $numero . '" where id_product=' . $pid);
    }

    /**
     * Override product list to consider LRD sorting -> by edition, then by page number
     * @param $params
     */
    function hookActionProductListOverride($params)
    {
        $id_category = Tools::getValue('id_category');
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $categoryObj = new Category($id_category, $id_lang, $id_shop);

        $n = abs((int) (Tools::getValue('n', ((isset($this->context->cookie->nb_item_per_page) && $this->context->cookie->nb_item_per_page >= 10) ? $this->context->cookie->nb_item_per_page : (int) Configuration::get('PS_PRODUCTS_PER_PAGE')))));
        $p = abs((int) Tools::getValue('p', 1));

        $orderBy = Tools::getValue('orderby', array('numero', 'page'));
        $orderWay = Tools::getValue('orderway', array('desc', 'asc'));

        $params['nbProducts'] = $categoryObj->getProducts(null, null, null, null, null, true);
        $params['catProducts'] = $categoryObj->getProducts($this->context->language->id, (int) $p, (int) $n, $orderBy, $orderWay);
        $params['hookExecuted'] = true;
    }

    /**
     * In the same edition : content of the page
     * @param $params
     * @return mixed
     * @throws PrestaShopDatabaseException
     */
    public function hookDisplayProductTab($params)
    {
        $product = new Product((int) $params['product']->id);
        if (preg_match('/^([0-9]{3})|([0-9]{3}-[0-9]{3})$/', $product->reference)) {

            $revueNumber = substr($product->reference, 0, 3);
            //$sql = "select * from ps_product where reference = '" . $revueNumber . "' OR reference REGEXP '^" . $revueNumber . "-[0-9]{3}$'";

            $products = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT DISTINCT p.id_product, pl.name, pl.link_rewrite, p.reference, cl.link_rewrite category
				FROM ' . _DB_PREFIX_ . 'product p
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (pl.id_product = p.id_product' . Shop::addSqlRestrictionOnLang('pl') . ')
				LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category = product_shop.id_category_default' . Shop::addSqlRestrictionOnLang('cl') . ')
				WHERE pl.id_lang = ' . (int) $this->context->language->id . '
					AND cl.id_lang = ' . (int) $this->context->language->id . '
					AND product_shop.active = 1
					AND (p.reference = "' . $revueNumber . '" OR p.reference REGEXP \'^' . $revueNumber . '-[0-9]{3}$\')
				ORDER BY page asc');

            foreach ($products as $key => $orderProduct) {
                $products[$key]['link'] = $this->context->link->getProductLink((int) $orderProduct['id_product']);
            }

            if (count($products) > 0) {
                $this->smarty->assign('sameNumberProducts', $products);

                return $this->display($this->name, 'samenumbertab.tpl');
            }
        }
    }

    /**
     * In the same edition : content of the tab
     * @return mixed
     */
    public function hookDisplayProductTabContent()
    {
        return $this->display($this->name, 'samenumber.tpl');
    }

    public function hookDisplayUserInfoStart()
    {
        $link = new Link();
        $link = $link->getModuleLink('ecosubscriptions', 'subscriptions');
        return sprintf('<li><a href="%s">mon abonnement</a></li>', $link);
    }

}
