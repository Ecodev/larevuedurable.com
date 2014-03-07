<?php

class CombinationProduct{

    /*
     * Récupére les combinaisons d'un produit
     * @param   object ($Objet)
     * @return  array
     */
    static function get($Product){
        global $cookie;
        $combinaisons = $Product->getAttributeCombinaisons($cookie->id_lang);
        if(is_array($combinaisons)){
            foreach($combinaisons AS $k => $combinaison){
                $combArray[$combinaison['id_product_attribute']]['reference'] = $combinaison['reference'];
                $combArray[$combinaison['id_product_attribute']]['id_product_attribute'] = $combinaison['id_product_attribute'];
                $combArray[$combinaison['id_product_attribute']]['price'] = $combinaison['price'];
                $combArray[$combinaison['id_product_attribute']]['weight'] = $combinaison['weight'];
                $combArray[$combinaison['id_product_attribute']]['quantity'] = $combinaison['quantity'];
                $combArray[$combinaison['id_product_attribute']]['supplier_reference'] = $combinaison['supplier_reference'];
                $combArray[$combinaison['id_product_attribute']]['ean13'] = $combinaison['ean13'];
                $combArray[$combinaison['id_product_attribute']]['id_image'] = isset($combinationImages[$combinaison['id_product_attribute']][0]['id_image']) ? $combinationImages[$combinaison['id_product_attribute']][0]['id_image'] : 0;
                $combArray[$combinaison['id_product_attribute']]['ecotax'] = $combinaison['ecotax'];
                $combArray[$combinaison['id_product_attribute']]['price'] = $combinaison['price'];
                $combArray[$combinaison['id_product_attribute']]['attributes'][] = array($combinaison['group_name'], $combinaison['attribute_name'], $combinaison['id_attribute']);
            }
        }
        return $combArray;
    }
    
}

?>
