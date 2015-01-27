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
* ****************************************************
* @category   Belvg
* @package    belvg_giftcert
* @author     Dzianis Yurevich (dzianis.yurevich@gmail.com)
* @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
* @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'belvg_giftcert/classes/BelvgGiftcert.php';
require_once _PS_MODULE_DIR_ . 'belvg_giftcert/classes/BelvgGiftcertProduct.php';

class belvg_giftcert extends Module
{
	const PREFIX = 'belvg_GC';
	const TABLE_NAME = 'belvg_giftcert';

    protected $_hooks = array(
        'displayAdminProductsExtra',
        'actionProductUpdate',
        'displayFooterProduct',
        'actionProductDelete',
        'actionCartSave',
        'actionOrderStatusUpdate',
        'actionValidateOrder',
        'displayAdminOrder');
    
    public $_configs = array(
    	'order_state');

    public function __construct()
    {
        $this->name = 'belvg_giftcert';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'BelVG';
        $this->need_instance = 0;
        $this->module_key = '6c96478a80d9e1d8d9b522f3b4afc4e6';

        parent::__construct();

        $this->displayName = $this->l('BelVG Gift Certificates');
        $this->description = $this->l('BelVG Gift Certificates');
    }

	public function getParam($name, $value = NULL)
	{
		if (in_array($name, $this->_configs)) {
			$_name = self::PREFIX . $name;
			if (!is_null($value)) {
				Configuration::updateValue($_name, $value);
			}
			
			return Configuration::get($_name);
		}
		
		return FALSE;
	}

    public static function getTableName($add = '')
    {
	    return _DB_PREFIX_ . self::TABLE_NAME . (empty($add) ? '' : ('_' . $add));
    }

    public function install()
    {
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . self::getTableName() . '` (
              `id_giftcert` int(10) unsigned NOT NULL auto_increment,
              `id_cart_rule` int(10) unsigned NOT NULL,
              `id_order` int(10) unsigned NOT NULL,
        	  `id_cart` int(10) unsigned NOT NULL,
              `id_product` int(10) unsigned NOT NULL,
              `id_product_attribute` int(10) unsigned NOT NULL,
              `id_shop` int(10) unsigned NOT NULL,
              `custom_price` decimal(20,6) NOT NULL,
              `recipient_email` varchar(255) NOT NULL,
              `recipient_name` varchar(255) NOT NULL,
              `recipient_address` text NOT NULL,
              `message` text NOT NULL,
              PRIMARY KEY  (`id_giftcert`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . self::getTableName('product') . '` (
              `id_giftcert_product` int(10) unsigned NOT NULL auto_increment,
              `id_product` int(11) unsigned NOT NULL,
              `id_shop` int(11) unsigned NOT NULL,
              `is_enabled` int(1) unsigned NOT NULL,
              `price_type` enum("dropdown", "fixed", "range", "custom") NOT NULL,
              `price_value` text NOT NULL,
              PRIMARY KEY  (`id_giftcert_product`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        foreach ($sql as $_sql) {
            Db::getInstance()->Execute($_sql);
        }

        $flagInstall = parent::install();
        foreach ($this->_hooks as $hook) {
            $this->registerHook($hook);
        }

        $this->getParam('order_state', 2);

        return $flagInstall;
    }

    public function uninstall()
    {
        $sql = array();
        $sql[] = 'DROP TABLE IF EXISTS `' . self::getTableName() . '`';
        $sql[] = 'DROP TABLE IF EXISTS `' . self::getTableName('product') . '`';

        foreach ($sql as $_sql) {
            Db::getInstance()->Execute($_sql);
        }

        foreach ($this->_hooks as $hook) {
            $this->unregisterHook($hook);
        }

        return parent::uninstall();
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = (int)Tools::getValue('id_product');
        if ($id_product) {
            $_storeId = $this->getIdShop();
            if (!$_storeId) {
                return $this->l('Please select the shop!');
            }

            $this->smarty->assign(array(
                $this->name => BelvgGiftcertProduct::getByProduct($id_product, $_storeId),
                'id_shop' => $_storeId,
                'belvg_product' => new Product($id_product, TRUE, $_storeId)));
            $html = $this->display(__file__, 'productAdminTab.tpl');
            return $html;
        } else {
            return $this->l('Please save this product!');
        }
    }

    public function hookActionProductUpdate($params)
    {
        if (Tools::isSubmit($this->name)) {
        	Context::getContext()->cookie->belvg_error = FALSE;
            $id_product = (int)Tools::getValue('id_product');
            $data = Tools::getValue($this->name);
            if ($id_product) {
            	$gift = BelvgGiftcertProduct::getByProduct($id_product, $this->getIdShop());
            	$gift->is_enabled = $data['is_enabled'];
            	$gift->id_product = $id_product;
            	$gift->id_shop = $data['id_shop'];
            	$gift->price_type = $data['price_type'];
            	$gift->price_value = $data['price_value'];
            	try {
            		$gift->save();
            	} catch (Exception $e) {
	            	Context::getContext()->cookie->belvg_error = $e->getMessage();
            	}
            }
        }
    }

    public function hookDisplayFooterProduct($params)
    {
        $id_product = (int)Tools::getValue('id_product');
        $gift = BelvgGiftcertProduct::getByProduct($id_product, $this->getIdShop());
        if ($gift->id && $gift->is_enabled) {
        	$this->context->controller->addCss($this->_path . 'css/front.css', 'all');
			$this->context->controller->addJS($this->_path . 'js/front.js');
			$this->context->smarty->assign('belvg_gift', $gift);
			$this->context->smarty->assign('belvg_product', new Product($id_product));
			return $this->display(__file__, 'productFooter.tpl');
        }

        return NULL;
    }

    public function hookActionProductDelete($params)
    {
        if ($id = (int)$params['product']->id) {
            $sql = array();
            $sql[] = 'DELETE FROM `' . self::getTableName() . '` WHERE `id_product` = ' . $id . ';';

            foreach ($sql as $_sql) {
                Db::getInstance()->Execute($_sql);
            }
        }
    }

	public function hookActionCartSave($params)
	{
		if (Tools::getIsset('add') && Tools::getIsset($this->name)) {
			$id_product = (int)Tools::getValue('id_product');
			$gift = BelvgGiftcertProduct::getByProduct($id_product, $this->getIdShop());
			if ($gift->id && $gift->is_enabled) {
				$custom_price = abs((float)Tools::getValue('belvg_giftcert'));
				if (!$custom_price) {
					$custom_price = Product::getPriceStatic($id_product, TRUE, (int)Tools::getValue('ipa'));
				}

				$data = array(
					'custom_price' => abs((float)$custom_price),
					'id_product' => $id_product,
					'id_product_attribute' => (int)Tools::getValue('ipa'),
					'id_cart' => $this->context->cart->id,
					'id_shop' => $this->getIdShop(),
					'recipient_email' => Tools::htmlentitiesUTF8(Tools::getValue('belvg_recipient_email')),
					'recipient_name' => Tools::htmlentitiesUTF8(Tools::getValue('belvg_recipient_name')),
					'recipient_address' => Tools::htmlentitiesUTF8(Tools::getValue('belvg_recipient_address')),
					'message' => Tools::htmlentitiesUTF8(Tools::getValue('belvg_recipient_message')),
				);
				
				$errors = array();

				if (Tools::getValue('belvg_send_gift') == 'myself') {
					$data['recipient_email'] = 'myself';
					$data['recipient_name'] = '';
					$data['recipient_address'] = '';
					$data['message'] = '';
				} else {
					if (!Validate::isEmail($data['recipient_email'])) {
						$errors[] = $this->l("Invalid recipient's email!");
					}
					
					if (empty($data['recipient_name'])) {
						$errors[] = $this->l("Recipient's name is empty!");
					}
				}

				if (count($errors)) {
					unset($_POST['add'], $_GET['add']);
					$_POST['delete'] = true;
					$this->context->controller->postProcess();

					header('HTTP/1.1 400 BAD REQUEST');
					echo chr(10);
					echo implode(chr(10), $errors); 
					die;
				}

				BelvgGiftcert::saveFromPost($data);
			}
		}
	}

    public function hookActionOrderStatusUpdate($params)
    {
        if ($params['newOrderStatus']->id == $this->getParam('order_state')) {
            $_gifts = BelvgGiftcert::loadOrderItems($params['id_order']);
            foreach ($_gifts as $gift) {
            	if (!$gift->id_cart_rule) {
            		$_cart = new Cart($gift->id_cart);
            		$_row = $_cart->containsProduct($gift->id_product, $gift->id_product_attribute);
            		$qty = (int)$_row['quantity'];
            		$rule = new CartRule;
	            	$rule->name[Configuration::get('PS_LANG_DEFAULT')] =
	            		$this->l('Gift Certificate. Order #') . $params['id_order'];
	            	
	            	$rule->date_from = date('Y-m-d', time());
	            	$rule->date_to = date('Y-m-d', strtotime('+1 year', time()));
	            	$rule->quantity = $qty;
	            	$rule->quantity_per_user = $qty;
	            	$rule->partial_use = TRUE;
	            	$rule->code = Tools::passwdGen();
	            	$rule->reduction_amount = $gift->custom_price;
	            	$rule->active = TRUE;
	            	$rule->minimum_amount_currency = 1;
	            	$rule->reduction_currency = 1;
	            	$rule->reduction_tax = 1;
	            	$rule->save();

	            	$gift->id_cart_rule = $rule->id;
	            	$gift->save();
	            	
	            	$id_lang = $this->context->language->id;
	            	$_productName = Product::getProductName($gift->id_product, $gift->id_product_attribute, $id_lang);
	            	$emailSubject = $_productName;

	            	$_order = new Order($params['id_order']);
	            	$_customer = new Customer($_order->id_customer, $id_lang);

	            	if ($gift->recipient_email == 'myself') {
		            	$toEmail = $_customer->email;
						$toName = $_customer->firstname . ' ' . $_customer->lastname;
						$message = '';
						$fromName = Configuration::get('PS_SHOP_NAME');
	            	} else {
		            	$toName = $gift->recipient_name;
		            	$toEmail = $gift->recipient_email;
                        $messageTitle = "<strong>Voici son message à votre intention : </strong>";
		            	$message = $gift->message ? $messageTitle . $gift->message : '';
		            	$fromName = $_customer->firstname . ' ' . $_customer->lastname;
	            	}

	            	$fromEmail = Configuration::get('PS_SHOP_EMAIL');

                    $templateVars['{toName}'] = $toName;
                    $templateVars['{product_name}'] = $_productName;
                    $templateVars['{qty}'] = $qty;
                    $templateVars['{message}'] = $message;
                    $templateVars['{code}'] = $rule->code;
                    $templateVars['{amount}'] = Tools::ps_round($gift->custom_price, 2);
                    $templateVars['{currency}'] = Context::getContext()->currency->iso_code;
                    $templateVars['{bought_by}'] = $_customer->firstname . ' ' . $_customer->lastname;
                    $templateVars['{shop_url}'] = _PS_BASE_URL_;

                    Mail::Send($id_lang, 'gift', $emailSubject, $templateVars, $toEmail,
                    	$toName, $fromEmail, $fromName, NULL, NULL, $this->local_path . 'mails/');
            	}
            }
        }
    }

    /**
     * Versionned changes correct a bug where the gift certificate is still linked to cart even when you remove it in the summary
     * There is no "removed from cart" hook, so we use "validateOrder" hook to check if the gift is in the cart before creating the order
     * If the gift is in the cart, this function update database with order id
     * If the gift is not in the cart, this function updates database by unsetting order_id on gift.
     */
    public function hookActionValidateOrder($params)
    {
        $_gifts = BelvgGiftcert::loadCartItems($params['cart']->id);
        $cartProducts = $params['cart']->getProducts();

        foreach ($_gifts as $gift) {
            $found = false;
            foreach($cartProducts as $product) {

                if ($gift->id_product == $product['id_product']) {
                    $found = true;
                }
            }

            if ($found) {
                $gift->id_order = $params['order']->id;
                $gift->save();
            } else {
                $gift->id_cart = 0;
                $gift->save();
            }
        }
    }

    public static function getIdShop()
    {
        $shop_id = 1;
        if (!is_null(Shop::getContextShopID())) {
            $shop_id = Shop::getContextShopID();
        }

        return $shop_id;
    }
    
    protected function postProcess()
    {
	    $output = FALSE;
	    if (Tools::getIsset($this->name)) {
		    $this->getParam('order_state', Tools::getValue('order_state'));
		    $output = TRUE;
	    }
	    
	    return $output;
    }
    
    public function getContent()
    {
    	$this->bootstrap = true;
    	$belvg_output = $this->postProcess();
    	$this->context->smarty->assign(array(
    		$this->name => $this,
    		'belvg_output' => $belvg_output
    	));
	    return $this->display(__file__, 'config.tpl');
    }

    public function hookDisplayAdminOrder($params)
    {
    	$_gifts = BelvgGiftcert::loadOrderItems($params['id_order']);
    	if (count($_gifts)) {
	    	$this->context->smarty->assign($this->name, $_gifts);
	    	return $this->display(__FILE__, 'adminOrder.tpl');
    	}
    	
    	return NULL;
    }
}
