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

<!-- Module Editorial -->
<div id="editorial_block_center" class="grid_3b alpha editorial_block">
	<div class='padding'>
	{if $editorial->body_home_logo_link}<a href="{$editorial->body_home_logo_link|escape:'htmlall':'UTF-8'}" title="{$editorial->body_title|escape:'htmlall':'UTF-8'|stripslashes}">{/if}
	{if $editorial->body_home_logo_link}</a>{/if}
	{if $editorial->body_title}<h1>{$editorial->body_title|stripslashes}</h1>{/if}
	{if $editorial->body_subheading}<h2>{$editorial->body_subheading|stripslashes}</h2>{/if}
	{if $editorial->body_paragraph}<div class="rte mceContentBody">{$editorial->body_paragraph|stripslashes}</div>{/if}
	</div>
</div>
<!-- /Module Editorial -->
