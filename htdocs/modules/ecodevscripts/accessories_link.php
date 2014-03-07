<?php

require_once(dirname(__FILE__).'/../../config/defines.inc.php');
require_once(dirname(__FILE__).'/../../config/config.inc.php');



$db = DB::getInstance();
$context = Context::getContext();
$context->controller = new MySubscriptionController();

// reset bd
$db->delete('accessory');

// récup des produits par catégorie (tient compte des multiples appartement aux catégories)
$revues = $db->executeS('SELECT p.id_product as id, reference FROM ps_product p
							JOIN ps_category_product cat on cat.id_product = p.id_product
							WHERE cat.`id_category` ='._CATEGORY_FULL_BOOK_);
							
$articles = $db->executeS('SELECT p.id_product as id, reference FROM ps_product p
							JOIN ps_category_product cat on cat.id_product = p.id_product
							WHERE cat.`id_category` ='._CATEGORY_LITTLE_ARTICLES_);



$accessory_revues = array();
$accessory_articles = array();

// prépare les liaisons dans des tableaux annexes pour limiter le nombre de requêtes sql
foreach($revues as $revue)
{
	foreach( $articles as $article)
	{
		if($revue['reference'] == substr($article['reference'], 0, 3))
		{

			if( !isset($accessory_revues[$revue['id']]) || !is_array($accessory_revues[$revue['id']]) ) $accessory_revues[$revue['id']] = array();
			array_push($accessory_revues[$revue['id']], $article);
			
			if(  !isset($accessory_articles[$article['id']]) ||  !is_array($accessory_articles[$article['id']]) ) $accessory_articles[$article['id']] = array();
			array_push($accessory_articles[$article['id']], $revue);
		}		
				
	}

}

// fait les liaisons dans les deux sens
foreach($accessory_revues as $id => $el)
{
	$product = new Product($id);
	$product->setWsAccessories($el);
}

foreach($accessory_articles as $id => $el)
{
	$product = new Product($id);
	$product->setWsAccessories($el);
}


echo 'matching terminé';