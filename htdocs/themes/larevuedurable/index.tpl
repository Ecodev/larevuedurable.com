{*
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
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


<div id="page" class="clearfix container_9">

	<div id="columns" class="grid_9 alpha omega clearfix">

		<!-- Center -->
		<div id="center_column" class="alpha omega grid_9">


		{$HOOK_HOME}
		
		<div style='clear:both'></div>
			
		<div class='homeCol alpha grid_3b'>
			{if isset($HOOK_HOME_COL1)}{$HOOK_HOME_COL1}{/if}
		</div>
		
		<div class='homeCol  grid_3b'>
			{if isset($HOOK_HOME_COL2)}{$HOOK_HOME_COL2}{/if}
		</div>
		
		<div class='homeCol  omega grid_2b'>
			{if isset($HOOK_HOME_COL3)}{$HOOK_HOME_COL3}{/if}
		</div>