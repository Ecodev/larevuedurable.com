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

<div id="social_block">
	<p class="title_block">{l s='Follow us' mod='blocksocial'}</p>
	<ul>
		{if $facebook_url != ''}<li class="facebook"><a href="{$facebook_url|escape:html:'UTF-8'}">{l s='Facebook' mod='blocksocial'}</a></li>{/if}
		{if $twitter_url != ''}<li class="twitter"><a href="{$twitter_url|escape:html:'UTF-8'}">{l s='Twitter' mod='blocksocial'}</a></li>{/if}
		<li class="rss"><a href="/modules/feeder/rss.php?id_category=0&orderby=name&orderway=ASC">{l s='RSS de nos produits' mod='blocksocial'}</a></li>
		<li class="rss"><a href="/modules/prestablog/prestablog-rss.php?rss=2">{l s='RSS de nos brèves' mod='blocksocial'}</a></li>
		<li class="rss"><a href="/modules/prestablog/prestablog-rss.php?rss=3">{l s='RSS de notre agenda' mod='blocksocial'}</a></li>
	</ul>
</div>
