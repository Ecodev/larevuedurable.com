<?php 

class Combination extends CombinationCore
{

	
	public function getAttributesAndGroupName($id_lang)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT al.*, agl.public_name
				FROM ps_product_attribute_combination pac
				JOIN ps_attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang='.$id_lang.')
				JOIN ps_attribute a ON pac.id_attribute = a.id_attribute
				JOIN ps_attribute_group_lang agl ON a.id_attribute_group = agl.id_attribute_group
				WHERE pac.id_product_attribute='.(int)$this->id.' and agl.id_lang = '.$id_lang);
	}


} 
?>