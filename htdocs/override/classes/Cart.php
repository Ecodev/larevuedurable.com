<?php

class Cart extends CartCore
{

    public function getProducts($refresh = false, $id_product = false, $id_country = null)
    {
        $products = parent::getProducts($refresh, $id_product, $id_country);
        $products = Tools::addIsGiftProperty($products);

        return $products;
    }
}
