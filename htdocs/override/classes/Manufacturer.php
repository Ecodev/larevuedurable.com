<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Manufacturer extends ManufacturerCore
{

    public function getProductsLite($id_lang)
    {
        $context = Context::getContext();

        $front = true;
        if (!isset($context->controller) )
            $front = false;
        else if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
            $front = false;

        return Db::getInstance()->executeS('
		SELECT p.`id_product`,  pl.`name`
		FROM `'._DB_PREFIX_.'product` p
		'.Shop::addSqlAssociation('product', 'p').'
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
			p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$id_lang.$context->shop->addSqlRestrictionOnLang('pl').'
		)
		WHERE p.`id_manufacturer` = '.(int)$this->id.
        ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : ''));
    }


}
