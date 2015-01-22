<?php

class EcodevProductSorting extends Module
{

    public function __construct()
    {
        $this->name = 'ecodevproductsorting';
        $this->tab = 'Ecodev';
        $this->version = '1.0';

        $this->_errors = array();

        parent::__construct();

        $this->displayName = $this->l("Assistant au tri des produits");
        $this->description = $this->l("");
    }

    public function install()
    {
        if (!parent::install() OR !$this->installDB() OR !$this->registerHook('actionProductUpdate') OR !$this->registerHook('actionProductAdd') OR !$this->registerHook('actionProductSave') or !$this->registerHook('actionProductListOverride') ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if ( !parent::uninstall() || !$this->uninstallDB() ) {
            return false;
        }

        return true;
    }


    private function installDB()
    {

        $results = DB::getInstance()->executeS("   SELECT NULL
                                                    FROM INFORMATION_SCHEMA.COLUMNS
                                                    WHERE table_name = '"._DB_PREFIX_."product'
                                                    AND table_schema = '"._DB_NAME_."'
                                                    AND column_name = 'numero'");

        if(count($results)==0)
            DB::getInstance()->execute("ALTER TABLE "._DB_PREFIX_."product ADD COLUMN numero SMALLINT");

        $results = DB::getInstance()->executeS("   SELECT NULL
                                                    FROM INFORMATION_SCHEMA.COLUMNS
                                                    WHERE table_name = '"._DB_PREFIX_."product'
                                                    AND table_schema = '"._DB_NAME_."'
                                                    AND column_name = 'page'");

        if(count($results)==0)
            DB::getInstance()->execute("ALTER TABLE "._DB_PREFIX_."product ADD COLUMN page SMALLINT");

        $results = DB::getInstance()->executeS('SELECT id_product, reference FROM '._DB_PREFIX_.'product where reference REGEXP "^[0-9]{3}" ');
        $db = DB::getInstance();
        foreach ($results as $p) {
            $page = substr_replace($p['reference'], '', 0, 4);
            $numero = substr($p['reference'], 0, 3);
            if (!$page) {
                $page = 0;
            }

            $sql = 'UPDATE '._DB_PREFIX_.'product set page="'.$page.'", numero="'.$numero.'" where id_product='.$p['id_product'];

            $db->execute($sql);
        }
        return true;
    }

    private function uninstallDB()
    {
        DB::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'product DROP COLUMN page');
        DB::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'product DROP COLUMN numero');

        return true;
    }


    public function hookActionProductAdd($params)
    {
        $createparams['id_product']=$params['product']->id;
        $this->hookActionProductSave($createparams);
    }

    public function hookActionProductSave($params)
    {
        $product = new Product($params['id_product']);

        $numero = (int)substr($product->reference, 0, 3);;
        $page = (int)substr_replace($product->reference, '', 0, 4);
        $pid = $product->id;

        DB::getInstance()->execute('UPDATE '._DB_PREFIX_.'product set page="'.$page.'", numero="'.$numero.'" where id_product='.$pid);
    }

    function hookActionProductListOverride($params)
    {
        $id_category = Tools::getValue('id_category');
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $categoryObj = new Category($id_category, $id_lang, $id_shop);

        $n = abs((int)(Tools::getValue('n', ((isset($this->context->cookie->nb_item_per_page) && $this->context->cookie->nb_item_per_page >= 10) ? $this->context->cookie->nb_item_per_page : (int)Configuration::get('PS_PRODUCTS_PER_PAGE')))));
        $p = abs((int)Tools::getValue('p', 1));

        $orderBy = Tools::getValue('orderby', array('numero','page'));
        $orderWay = Tools::getValue('orderway',  array('desc','asc'));

        $params['nbProducts'] = $categoryObj->getProducts(null, null, null, null, null, true);
        $params['catProducts'] = $categoryObj->getProducts($this->context->language->id, (int) $p, (int) $n, $orderBy, $orderWay);
        $params['hookExecuted'] = TRUE;
    }


}