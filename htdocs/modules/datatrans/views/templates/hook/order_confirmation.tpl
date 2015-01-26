{*
** Creator   : WDXperience SARL : YM (121008)
** Copyright : All Right Reserved - Licence available for 1 shop
** Licence   : Prices and Conditions on http://www.wdxperience.ch/shop/
** Compat    : Prestashop v1.5
*}

<div class='mceContentBody'>
        {if $status == 'ok'}

	<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='datatrans'}</p>
	<p>{l s='An e-mail has been sent to you with your order information.' mod='datatrans'}</p>
	<p><strong>{l s='Your order will be sent as soon as possible.'  mod='datatrans'}</strong></p>
	<p>{l s='For any questions or for further information, please contact our' mod='datatrans'}
		<a href="{$link->getPageLink('contact', true)}">{l s='customer support'  mod='datatrans'}</a>.</p>

{else}

	<h3 style="margin-top: 40px; line-height: 1.4em; color:red;">{$shop_name} : {l s='transaction error' mod='datatrans'}</h3>

	<p class="warning">{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='datatrans'}
		<a href="{$link->getPageLink('order', true, NULL, 'step=1')}">{l s='customer support' mod='datatrans'}</a></p>

{/if}
</div>