<?php
class Product extends ProductCore
{

    public function isGift()
    {
        $sql = 'SELECT id_giftcert_product FROM ' . belvg_giftcert::getTableName() . '_product WHERE `id_product` = ' . $this->id . ' AND `id_shop` = 1';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Surcharge pour utilisation dans le module bon cadeau
     */
	public static function getPriceStatic($id_product, $usetax = true, $id_product_attribute = null, $decimals = 6, $divisor = null,
		$only_reduc = false, $usereduc = true, $quantity = 1, $force_associated_tax = false, $id_customer = null, $id_cart = null,
		$id_address = null, &$specific_price_output = null, $with_ecotax = true, $use_group_reduction = true, Context $context = null,
		$use_customer_price = true)
	{
		if (!$context) {
			$context = Context::getContext();
		}

		$module = Module::getInstanceByName('belvg_giftcert');
		$normalPrice = parent::getPriceStatic($id_product, $usetax, $id_product_attribute, $decimals, $divisor,
			$only_reduc, $usereduc, $quantity, $force_associated_tax, $id_customer, $id_cart,
			$id_address, $specific_price_output, $with_ecotax, $use_group_reduction, $context,
			$use_customer_price);

		$cartData = array(
			'id_product' => $id_product,
			'id_product_attribute' => $id_product_attribute,
			'id_cart' => (int)$id_cart,
			'id_shop' => $context->shop->id
		);

		$gift = BelvgGiftcert::getByCartData($cartData);
		if (!$gift->id) {
			return $normalPrice;
		}

		$custom_price = $gift->custom_price;
		if (Validate::isFloat($custom_price)) {
			// Add Tax
			if ($usetax) {
				$id_country = (int)$context->country->id;
				$id_state = 0;
				$zipcode = 0;

				if (!$id_address) {
					$id_address = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
				}

				if ($id_address) {
					$address_infos = Address::getCountryAndState($id_address);
					if ($address_infos['id_country']) {
						$id_country = (int)$address_infos['id_country'];
						$id_state = (int)$address_infos['id_state'];
						$zipcode = $address_infos['postcode'];
					}
				} else if (isset($context->customer->geoloc_id_country)) {
					$id_country = (int)$context->customer->geoloc_id_country;
					$id_state = (int)$context->customer->id_state;
					$zipcode = (int)$context->customer->postcode;
				}

				$address = new Address();
				$address->id_country = $id_country;
				$address->id_state = $id_state;
				$address->postcode = $zipcode;

				$tax_manager = TaxManagerFactory::getManager($address, Product::getIdTaxRulesGroupByIdProduct((int)$id_product, $context));
				$product_tax_calculator = $tax_manager->getTaxCalculator();
				$custom_price = $product_tax_calculator->addTaxes($custom_price);
			}


			$custom_price = Tools::ps_round($custom_price, $decimals);
			if ($custom_price < 0) {
				$custom_price = 0;
			}

			return $custom_price;
		}

		return $normalPrice;
	}


    /*
    * Génère la requête SQL comportant les id de tous les produits identifiés comme étant institutionnels
    */

    // Récupère les accessoires -> dans ce contexte, ce sont les articles liés
    // il trie par référence, c'est la raison de la surcharge.




    /**
     * Retourne la dernère revue publiée à la date de la commande
     * Pour changer la date de référence (de la commande), il faut ajouter dans cette fonction une récupération des statuts de la commande, puis exploiter le statut numéro 15 qui remplacera la date de la commande
     * Si le statut 15 est ajouté, l'action est irreversible, il faut intervenir dans la BD. Ajouter ce repère le même jour, n'aura aucune influence car la journée entière de minuit à minuit est utilisée.
     */






    /*
    * Génère la requête SQL comportant les id de tous les produits identifiés comme étant institutionnels
    */

    // Récupère les accessoires -> dans ce contexte, ce sont les articles liés
    // il trie par référence, c'est la raison de la surcharge.




    /**
     * Retourne la dernère revue publiée à la date de la commande
     * Pour changer la date de référence (de la commande), il faut ajouter dans cette fonction une récupération des statuts de la commande, puis exploiter le statut numéro 15 qui remplacera la date de la commande
     * Si le statut 15 est ajouté, l'action est irreversible, il faut intervenir dans la BD. Ajouter ce repère le même jour, n'aura aucune influence car la journée entière de minuit à minuit est utilisée.
     */






    /*
    * Génère la requête SQL comportant les id de tous les produits identifiés comme étant institutionnels
    */

    // Récupère les accessoires -> dans ce contexte, ce sont les articles liés
    // il trie par référence, c'est la raison de la surcharge.




    /**
     * Retourne la dernère revue publiée à la date de la commande
     * Pour changer la date de référence (de la commande), il faut ajouter dans cette fonction une récupération des statuts de la commande, puis exploiter le statut numéro 15 qui remplacera la date de la commande
     * Si le statut 15 est ajouté, l'action est irreversible, il faut intervenir dans la BD. Ajouter ce repère le même jour, n'aura aucune influence car la journée entière de minuit à minuit est utilisée.
     */




}
