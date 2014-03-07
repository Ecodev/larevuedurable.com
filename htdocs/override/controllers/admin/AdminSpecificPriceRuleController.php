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

class AdminSpecificPriceRuleController extends AdminSpecificPriceRuleControllerCore
{
	public $list_reduction_type;

	public function __construct()
	{
	
		parent::__construct();		
		$this->fields_list = array(
			'id_specific_price_rule' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'name' => array(
				'title' => $this->l('Name'),
				'filter_key' => 'a!name',
				'width' => 'auto'
			),
//			'shop_name' => array(
//				'title' => $this->l('Shop'),
//				'filter_key' => 's!name'
//			),
			'currency_name' => array(
				'title' => $this->l('Currency'),
				'align' => 'center',
				'filter_key' => 'cu!name'
			),
//			'country_name' => array(
//				'title' => $this->l('Country'),
//				'align' => 'center',
//				'filter_key' => 'cl!name'
//			),
			'group_name' => array(
				'title' => $this->l('Group'),
				'align' => 'center',
				'filter_key' => 'gl!name'
			),
//			'from_quantity' => array(
//				'title' => $this->l('From quantity'),
//				'align' => 'center',
//			),
			'reduction_type' => array(
				'title' => $this->l('Reduction type'),
				'align' => 'center',
				'type' => 'select',
				'filter_key' => 'a!reduction_type',
				'list' => $this->list_reduction_type,
			),
			'reduction' => array(
				'title' => $this->l('Reduction'),
				'align' => 'center',
				'type' => 'decimal'
			),
//			'from' => array(
//				'title' => $this->l('Beginning'),
//				'align' => 'right',
//				'type' => 'date',
//			),
//			'to' => array(
//				'title' => $this->l('End'),
//				'align' => 'right',
//				'type' => 'date'
//			),
		);


	}
}
