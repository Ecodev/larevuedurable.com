<?php


require_once(dirname(__FILE__) . '/../../config/defines.inc.php');
require_once(dirname(__FILE__) . '/../../config/config.inc.php');


/* @var $order8 OrderCore */
/* @var $order6 OrderCore */
/* @var $cart11 CartCore */
/* @var $cart14 CartCore */


$order6 = new Order(6);
$cart11 = new Cart(11);

$order8 = new Order(8);
$cart14 = new Cart(14);
$cart = new Cart(15);

echo '<pre>';

echo 'First order<br/>';
echo $order6->getTotalProductsWithTaxes();
echo '<br/>';
echo $order6->getTotalProductsWithTaxes();
echo '<br/>';
echo $order6->getTotalProductsWithoutTaxes();
echo '<br/>';
echo $cart->getTotalCart(true).'-'.$cart11->getTotalCart(true);


echo '<br/>Second order<br/>';









var_dump($order6);
//var_dump($order8);

