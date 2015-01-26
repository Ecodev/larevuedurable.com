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
* @package    BelvgGiftcert
* @author     Dzianis Yurevich (dzianis.yurevich@gmail.com)
* @site       http://module-presta.com
* @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
* @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
*/

class BelvgGiftcert extends ObjectModel
{
    public $id;
    public $id_giftcert;
    public $id_cart_rule;
    public $id_order;
    public $id_cart;
    public $id_product;
    public $id_product_attribute;
    public $id_shop;
    public $custom_price;
    public $recipient_name;
    public $recipient_email;
    public $recipient_address;
    public $message;
    
    protected $_cart_rule = NULL;
    protected $_product = NULL;

    public static $definition = array(
        'table' => "belvg_giftcert",
        'primary' => 'id_giftcert',
        'multilang' => FALSE,
        'fields' => array(
            'id_cart_rule' => array('type' => self::TYPE_INT),
            'id_order' => array('type' => self::TYPE_INT),
            'id_cart' => array('type' => self::TYPE_INT),
            'id_product' => array('type' => self::TYPE_INT),
            'id_product_attribute' => array('type' => self::TYPE_INT),
            'id_shop' => array('type' => self::TYPE_INT),
            'custom_price' => array('type' => self::TYPE_FLOAT),
            'recipient_name' => array('type' => self::TYPE_STRING),
            'recipient_email' => array('type' => self::TYPE_STRING),
            'recipient_address' => array('type' => self::TYPE_STRING),
            'message' => array('type' => self::TYPE_STRING)
            )
        );

	public function getCartRule()
	{
		if (!$this->id_cart_rule) {
			return FALSE;
		}

		if (is_null($this->_cart_rule)) {
			$this->_cart_rule = new CartRule($this->id_cart_rule, Context::getContext()->language->id);
		}

		return $this->_cart_rule;
	}

	public function getProduct()
	{
		if (is_null($this->_product)) {
			$this->_product = new Product($this->id_product, FALSE, Context::getContext()->language->id);
		}

		return $this->_product;
	}

    /*public function __construct($id = NULL, $id_lang = NULL)
    {
        self::$definition['table'] = belvg_giftcert::getTableName();
        parent::__construct($id, $id_lang);
    }*/

	public static function getByCartData($data)
	{
	    $id = (int)Db::getInstance()->getValue('
	        SELECT `id_giftcert` FROM `' . belvg_giftcert::getTableName() . '`
	        WHERE   `id_product` = ' . (int)$data['id_product'] . '
		        AND `id_product_attribute` = ' . (int)$data['id_product_attribute'] . '
		        AND `id_cart` = ' . (int)$data['id_cart'] . '
		        AND `id_shop` = ' . (int)$data['id_shop'] . ';
        ');
        
        if (!$id) {
	        $id = NULL;
        }
        
        $cert = new self($id);
        return $cert;
	}
    
    public static function saveFromPost($data)
    {
		$cert = self::getByCartData($data);
        foreach ($data as $key => $val) {
	        $cert->$key = $val;
        }
        
        return $cert->save();
    }

	public static function loadOrderItems($id_order, $id_shop = NULL)
	{
		$_items = array();
		if (is_null($id_shop)) {
			$id_shop = belvg_giftcert::getIdShop();
		}

		$query = Db::getInstance()->ExecuteS('
	        SELECT `id_giftcert` FROM `' . belvg_giftcert::getTableName() . '`
	        WHERE  `id_order` = ' . (int)$id_order . '
		    AND `id_shop` = ' . (int)$id_shop . ';
        ');

		foreach ($query as $item) {
			$_items[] = new self($item['id_giftcert']);
		}
		
		return $_items;
	}

	public static function loadCartItems($id_cart, $id_shop = NULL)
	{
		$_items = array();
		if (is_null($id_shop)) {
			$id_shop = belvg_giftcert::getIdShop();
		}

		$query = Db::getInstance()->ExecuteS('
	        SELECT `id_giftcert` FROM `' . belvg_giftcert::getTableName() . '`
	        WHERE  `id_cart` = ' . (int)$id_cart . '
		    AND `id_shop` = ' . (int)$id_shop . ';
        ');

		foreach ($query as $item) {
			$_items[] = new self($item['id_giftcert']);
		}
		
		return $_items;
	}
}
