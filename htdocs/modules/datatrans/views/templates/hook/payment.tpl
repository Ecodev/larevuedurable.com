{*
** Creator   : WDXperience SARL : YM (121008)
** Copyright : All Right Reserved - Licence available for 1 shop
** Licence   : Prices and Conditions on http://www.wdxperience.ch/shop/
** Compat    : Prestashop v1.5
*}

<p class="payment_module">
	<a href="javascript:javascript:document.datatransform.submit();" style="overflow: auto;">
		<span>{l s='Cartes de crédit via Datatrans' mod='datatrans'}
		{if isset($usealias)}<br />{$usealias}{/if}</span>
	</a>
</p>

<form method="post" action="{$psp_url}" id="datatransform"  name="datatransform">
<p>
{foreach from=$parametres item=parametre}
	<input type="hidden" name="{$parametre.name}" value="{$parametre.value}" />
{/foreach}
</p>
</form>
