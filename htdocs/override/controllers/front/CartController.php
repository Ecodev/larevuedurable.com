<?php
class CartController extends CartControllercore
{

	/** 
	*	Fonction appelée quand un produit est ajouté ou modifié dans le panier
	*
	*	Les abonnements doivent être achetés seuls dans une commande pour plusieurs raisons : 
	*	1) Si on autorise l'achat de plusieurs abonnements, comment gérer les priorités s'ils sont différents ? Quelles sont les dates utilisées ? Et s'ils sont les mêmes, est-ce une erreur ? Et si la personne pensais pouvoir en acheter pour quelqu'un d'autre ?
	*	2) Si on souscrit à un abonnement, par la suite les produits sont gratuits dans le cadre du web alors autant éviter de les faire payer
	*	3) L'état d'un abonement (actif / inactif) est directement lié à l'état de la commande. Si on devait annuler un abonnement, les produits commandés avec, seraient considérés comme non achetés également
	*	4) Un abonnement en peut être lié qu'à un compte, créé avec un e-mail. Par conséquent, un seul abonnement à la fois peut être actif pour un compte
	*	
	*	Dans le cas où la personne voudrait acheter un abonnement papier et commander des petits articles web, ces dernier n'ayant pas de frais de port, il n'y aura aucune majoration pour procéder en deux commandes
	*/
	protected function processChangeProductInCart()
	{
		$cartProducts = $this->context->cart->getProducts();

		// ajout d'un abonnement au panier
		// vérifie si le panier est vide ou plein au moment de l'ajout. Il DOIT être vide pour ajouter l'abonnement.
		if(	isset($this->id_product) && 
				(
				$this->id_product == _ABONNEMENT_PARTICULIER_ && sizeof($cartProducts)>0 ||
				$this->id_product == _ABONNEMENT_INSTITUT_  && sizeof($cartProducts)>0 ||  
				$this->id_product == _ABONNEMENT_MOOC_  && sizeof($cartProducts)>0 ||
				$this->id_product == _ABONNEMENT_SOLIDARITE_  && sizeof($cartProducts)>0
				)
		  )
		{
			$this->errors[] = Tools::displayError("Pour des raisons techniques, chaque abonnement doit être commandé séparément. Si vous souhaitez acheter plusieurs abonnements ou un abonnement et des numéros isolés ou des articles, vous devez passer une commande pour chaque abonnement puis en passer une nouvelle avec les autres articles.", false);
			return;			
		}

		// ajout d'un produit non-abonnement
		// vérifie si le panier comporte un abonnement au moment de l'ajout au panier.
		foreach( $cartProducts as $product)
		{
			if( isset($product['id_product']) &&
					(
					$product['id_product'] == _ABONNEMENT_PARTICULIER_ ||  
					$product['id_product'] == _ABONNEMENT_INSTITUT_ ||  
					$product['id_product'] == _ABONNEMENT_MOOC_ ||
					$product['id_product'] == _ABONNEMENT_SOLIDARITE_
					)
				)
			{
				$this->errors[] = Tools::displayError("Pour des raisons techniques, chaque abonnement doit être commandé séparément. Si vous souhaitez acheter plusieurs abonnements ou un abonnement et des numéros isolés ou des articles, vous devez passer une commande pour chaque abonnement puis en passer une nouvelle avec les autres articles.", false);
				return;	
			}
		}

		parent::processChangeProductInCart();
	}
}
