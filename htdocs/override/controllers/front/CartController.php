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
}
