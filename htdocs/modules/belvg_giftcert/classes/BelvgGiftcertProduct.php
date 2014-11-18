<?php

/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*         DISCLAIMER   *
* *************************************** */
/* Do not edit or add to this file if you wish to upgrade Prestashop to newer
* versions in the future.
* *****************************************************
* @category   Belvg
* @package    BelvgGiftcertProduct
* @author     Dzianis Yurevich (dzianis.yurevich@gmail.com)
* @site       http://module-presta.com
* @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
* @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
*/

class BelvgGiftcertProduct extends ObjectModel
{
    public $id;
    public $id_giftcert_product;
    public $is_enabled = 1;
    public $id_product;
    public $id_shop;
    public $price_type;
    public $price_value;
    
    public static $price_types = array('dropdown', 'fixed', 'range', 'custom');

    public static $definition = array(
        'table' => "belvg_giftcert_product",
        'primary' => 'id_giftcert_product',
        'multilang' => FALSE,
        'fields' => array(
            'is_enabled' => array('type' => self::TYPE_INT),
            'id_product' => array('type' => self::TYPE_INT),
            'id_shop' => array('type' => self::TYPE_INT),
            'price_type' => array('type' => self::TYPE_STRING),
            'price_value' => array('type' => self::TYPE_STRING)
            )
        );

    /*public function __construct($id = NULL, $id_lang = NULL)
    {
        self::$definition['table'] = belvg_giftcert::getTableName('product');
        parent::__construct($id, $id_lang);
    }*/
    
    public static function getByProduct($id_product, $id_shop = 1)
    {
	    $id = (int)Db::getInstance()->getValue('
	    	SELECT `id_giftcert_product` 
	    	FROM `' . belvg_giftcert::getTableName('product') . '`
	    	WHERE `id_product` = ' . (int)$id_product . '
	    	AND `id_shop` = ' . (int)$id_shop
	    );
	    
	    if ($id) {
		    return new self($id);
	    }
	    
	    return new self();
    }
    
    public function save($null_values = false, $autodate = true)
    {
	    if (!in_array($this->price_type, self::$price_types)) {
		    throw new Exception('Invalid Price Type!');
	    }
	    
	    $value = '';
	    if ($this->price_type == 'dropdown') {
		    $_values = explode(';', $this->price_value);
		    foreach ($_values as $val) {
			    if (!Validate::isFloat($val)) {
				    throw new Exception('Invalid Price Value!');
			    }
			    
			    $value .= (float)$val . ';';
		    }
		    
		    $value = rtrim($value, ';');
	    }
	    
	    if ($this->price_type == 'fixed') {
		    if (!Validate::isFloat($this->price_value)) {
			    throw new Exception('Invalid Price Value!');
		    }
		    
		    $value = (float)$this->price_value;
	    }
	    
	    if ($this->price_type == 'range') {
		    $_values = explode('-', $this->price_value);
			if (count($_values) != 2) {
				throw new Exception('Invalid Price Value!');
			}

		    foreach ($_values as $val) {
			    if (!Validate::isFloat($val)) {
				    throw new Exception('Invalid Price Value!');
			    }
			    
			    $value .= (float)$val . '-';
		    }
		    
		    $value = rtrim($value, '-');
	    }

		$this->price_value = $value;

	    parent::save($null_values, $autodate);
    }
    
    public function getPriceValue($toJson = TRUE)
    {
	    $value = $this->price_value;
	    if ($this->price_type == 'dropdown') {
		    return json_encode(array_map('floatval', explode(';', $value)));
	    }

	    if ($this->price_type == 'range') {
		    return json_encode(array_map('floatval', explode('-', $value)));
	    }

	    return json_encode(array((float)$value));
    }
}
