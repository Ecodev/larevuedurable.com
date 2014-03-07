<?php
class Product extends ProductCore
{

    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        $this->date_parution = null;
        $this->numero = null;
        $this->page = null;
        self::$definition['fields']['date_parution'] = array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat');
        self::$definition['fields']['numero'] = array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt');
        self::$definition['fields']['page'] = array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt');
        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
    }


    /*
    * Génère la requête SQL comportant les id de tous les produits identifiés comme étant institutionnels
    */
    public static function getInstituteProductsAsSql()
    {
        // les abonnements "instituts" sont identifiés par le fabriquant "Institut"
        $manufacturer = new Manufacturer(_MANUFACTURE_INSTITUTE_);

        // récupère les produits qui sont des abonnements instituts
        $products = $manufacturer->getProductsLite(Context::getContext()->language->id);

        $return = '';
        foreach ($products as $product) {
            $return .= ' OR od.product_id=' . $product['id_product'];
        }

        return $return;
    }


    // Récupère les accessoires -> dans ce contexte, ce sont les articles liés
    // il trie par référence, c'est la raison de la surcharge.
    public function getAccessories($id_lang, $active = true, Context $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }

        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`link_rewrite`,
					pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`reference`,
					MAX(image_shop.`id_image`) id_image, il.`legend`, m.`name` as manufacturer_name, cl.`name` AS category_default,
					DATEDIFF(
						p.`date_add`,
						DATE_SUB(
							NOW(),
							INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY
						)
					) > 0 AS new
				FROM `' . _DB_PREFIX_ . 'accessory`
				LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = `id_product_2`
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (
					p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . '
				)
				LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (
					product_shop.`id_category_default` = cl.`id_category`
					AND cl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . '
				)
				LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`)' . Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (p.`id_manufacturer`= m.`id_manufacturer`)
				' . Product::sqlStock('p', 0) . '
				WHERE `id_product_1` = ' . (int) $this->id . ($active ? ' AND product_shop.`active` = 1' : '') . '
				GROUP BY product_shop.id_product
				order by reference asc';

        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return false;
        }
        foreach ($result as &$row) {
            $row['id_product_attribute'] = Product::getDefaultAttribute((int) $row['id_product']);
        }

        return $this->getProductsProperties($id_lang, $result);
    }


    /**
     * Retourne la dernère revue publiée à la date de la commande
     * Pour changer la date de référence (de la commande), il faut ajouter dans cette fonction une récupération des statuts de la commande, puis exploiter le statut numéro 15 qui remplacera la date de la commande
     * Si le statut 15 est ajouté, l'action est irreversible, il faut intervenir dans la BD. Ajouter ce repère le même jour, n'aura aucune influence car la journée entière de minuit à minuit est utilisée.
     */
    public static function getLastMagazineReleased($order)
    {
        global $date_now;

        if ($order === null) {
            $reference_date = $date_now;
        } else {
            $reference_date = new DateTime($order->date_add);
        }
        $sql = 'select id_product, reference from ps_product where
            date_parution <= "'.$reference_date->format(_DATE_FORMAT_SHORT_).'" and active = 1  and
            reference REGEXP "^[0-9]{3}$" order by date_parution desc';


        $product = DB::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $product;
    }


    public static function getLastMagazineUntil($startNUmber)
    {
        $sql = 'select id_product, reference from ps_product
                where
                    active = 1 and
                    reference REGEXP "^[0-9]{3}$"
                order by date_parution desc';
        $products = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($products as $product){
            if ((int) $product['reference'] <= $startNUmber) {
                return $product;
            }
        }
        return 0;
    }


    public static function getParutionDateByRef($ref)
    {
        if (strlen($ref) == 2) {
            $ref = '0' . $ref;
        }

        $sql = 'SELECT date_parution from ps_product where reference ="' . $ref . '"';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if (!$result || sizeof($result) == 0) {
            return null;
        }

        return $result['date_parution'];

    }


}