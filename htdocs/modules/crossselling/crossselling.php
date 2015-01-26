<?php
/*
* 2007-2012 PrestaShop
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
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 7040 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class CrossSelling extends Module
{
	private $_html;

	public function __construct()
	{
		$this->name = 'crossselling';
		$this->tab = 'front_office_features';
		$this->version = 0.1;
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Cross Selling');
		$this->description = $this->l('Customers who bought this product also bought:');

		if (!$this->isRegisteredInHook('header'))
			$this->registerHook('header');
	}

	public function install()
	{
		if (!parent::install() OR
 			!$this->registerHook('displayProductTabContent') OR 
			!$this->registerHook('displayProductTab') ) //OR
			//!$this->registerHook('productFooter') OR
			//!$this->registerHook('header') OR
			//!$this->registerHook('shoppingCart') OR
			//!Configuration::updateValue('CROSSSELLING_DISPLAY_PRICE', 0))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() )//OR
			// !$this->unregisterHook('productFooter') OR
			// !$this->unregisterHook('header') OR
			// !$this->unregisterHook('shoppingCart') OR
			//!Configuration::deleteByName('CROSSSELLING_DISPLAY_PRICE'))
			return false;
		return true;
	}



	public function hookHeader()
	{
		$this->context->controller->addCSS(($this->_path).'crossselling.css', 'all');
	}

	/**
	 * Returns module content
	 */
	public function hookshoppingCart($params)
	{
		if (!$params['products'])
			return;

		$qOrders = 'SELECT o.id_order
		FROM '._DB_PREFIX_.'orders o
		LEFT JOIN '._DB_PREFIX_.'order_detail od ON (od.id_order = o.id_order)
		WHERE o.valid = 1 AND (';
		$nProducts = count($params['products']);
		$i = 1;
		$pIds = array();
		foreach ($params['products'] as $product)
		{
			$qOrders .= 'od.product_id = '.(int)$product['id_product'];
			if ($i < $nProducts)
				$qOrders .= ' OR ';
			++$i;
			$pIds[] = (int)$product['id_product'];
		}
		$qOrders .= ')';
		$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($qOrders);

		if (sizeof($orders))
		{

			$list = '';
			foreach ($orders AS $order)
				$list .= (int)$order['id_order'].',';
			$list = rtrim($list, ',');

			$list_product_ids = join(',', $pIds);
			$orderProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT DISTINCT od.product_id, pl.name, pl.link_rewrite, p.reference, i.id_image, product_shop.show_price, cl.link_rewrite category, p.ean13
				FROM '._DB_PREFIX_.'order_detail od
				LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = od.product_id)
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = od.product_id'.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = product_shop.id_category_default'.Shop::addSqlRestrictionOnLang('cl').')
				LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = od.product_id)
				WHERE od.id_order IN ('.$list.')
					AND pl.id_lang = '.(int)$this->context->language->id.'
					AND cl.id_lang = '.(int)$this->context->language->id.'
					AND od.product_id NOT IN ('.$list_product_ids.')
					AND i.cover = 1
					AND product_shop.active = 1
				ORDER BY RAND()
				LIMIT 10
			');

			$taxCalc = Product::getTaxCalculationMethod();
			foreach ($orderProducts AS &$orderProduct)
			{
				$orderProduct['image'] = $this->context->link->getImageLink($orderProduct['link_rewrite'], (int)$orderProduct['product_id'].'-'.(int)$orderProduct['id_image'], 'medium');
				$orderProduct['link'] = $this->context->link->getProductLink((int)$orderProduct['product_id'], $orderProduct['link_rewrite'], $orderProduct['category'], $orderProduct['ean13']);
				if (Configuration::get('CROSSSELLING_DISPLAY_PRICE') AND ($taxCalc == 0 OR $taxCalc == 2))
					$orderProduct['displayed_price'] = Product::getPriceStatic((int)$orderProduct['product_id'], true, NULL);
				elseif (Configuration::get('CROSSSELLING_DISPLAY_PRICE') AND $taxCalc == 1)
					$orderProduct['displayed_price'] = Product::getPriceStatic((int)$orderProduct['product_id'], false, NULL);
			}

			$this->smarty->assign(array('order' => (count($pIds) > 1 ? true : false), 'orderProducts' => $orderProducts, 'middlePosition_crossselling' => round(sizeof($orderProducts) / 2, 0),
			'crossDisplayPrice' => Configuration::get('CROSSSELLING_DISPLAY_PRICE')));
		}
		return $this->display(__FILE__, 'crossselling.tpl');
	}


	public function hookDisplayProductTab($params)
	{
		
		$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT o.id_order
		FROM '._DB_PREFIX_.'orders o
		LEFT JOIN '._DB_PREFIX_.'order_detail od ON (od.id_order = o.id_order)
		WHERE o.valid = 1 AND od.product_id = '.(int)$params['product']->id);

		if (sizeof($orders))
		{
			$list = '';
			foreach ($orders AS $order)
				$list .= (int)$order['id_order'].',';
			$list = rtrim($list, ',');

			$orderProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT DISTINCT od.product_id, pl.name, pl.link_rewrite, p.reference, i.id_image, product_shop.show_price, cl.link_rewrite category, p.ean13
				FROM '._DB_PREFIX_.'order_detail od
				LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = od.product_id)
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = od.product_id'.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = product_shop.id_category_default'.Shop::addSqlRestrictionOnLang('cl').')
				LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = od.product_id)
				WHERE od.id_order IN ('.$list.')
					AND pl.id_lang = '.(int)$this->context->language->id.'
					AND cl.id_lang = '.(int)$this->context->language->id.'
					AND od.product_id != '.(int)$params['product']->id.'
					AND i.cover = 1
					AND product_shop.active = 1
				ORDER BY RAND()
				LIMIT 10
			');

			$taxCalc = Product::getTaxCalculationMethod();
			foreach ($orderProducts AS &$orderProduct)
			{
				$orderProduct['image'] = $this->context->link->getImageLink($orderProduct['link_rewrite'], (int)$orderProduct['product_id'].'-'.(int)$orderProduct['id_image'], 'medium');
				$orderProduct['link'] = $this->context->link->getProductLink((int)$orderProduct['product_id'], $orderProduct['link_rewrite'], $orderProduct['category'], $orderProduct['ean13']);
				if (Configuration::get('CROSSSELLING_DISPLAY_PRICE') AND ($taxCalc == 0 OR $taxCalc == 2))
					$orderProduct['displayed_price'] = Product::getPriceStatic((int)$orderProduct['product_id'], true, NULL);
				elseif (Configuration::get('CROSSSELLING_DISPLAY_PRICE') AND $taxCalc == 1)
					$orderProduct['displayed_price'] = Product::getPriceStatic((int)$orderProduct['product_id'], false, NULL);
			}

			$this->smarty->assign(array('order' => false, 'orderProducts' => $orderProducts, 'middlePosition_crossselling' => round(sizeof($orderProducts) / 2, 0),
			'crossDisplayPrice' => Configuration::get('CROSSSELLING_DISPLAY_PRICE')));
		}
		
		return $this->display(__FILE__, 'crosstab.tpl');
	}



	/**
	* Returns module content for left column
	*/
	public function hookDisplayProductTabContent($params)
	{
		return $this->display(__FILE__, 'crossselling.tpl');
	}
}
