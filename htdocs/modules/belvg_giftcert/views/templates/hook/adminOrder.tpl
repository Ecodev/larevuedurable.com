<br/>
<fieldset>
	<legend><img src="../img/os/10.gif"> {l s='BelVG Gift Certificates' mod='belvg_giftcert'}</legend>
	{foreach from=$belvg_giftcert item=gift key=index}
		<span style="font-weight: bold; font-size: 14px;">
			{$index+1}.
			{if $gift->getCartRule() eq false}
				{Product::getProductName($gift->id_product, $gift->id_product_attribute, Context::getContext()->language->id)}
			{else}
				<a href="{$link->getAdminLink('AdminCartRules')}&id_cart_rule={$gift->id_cart_rule}&updatecart_rule">
					{$gift->getCartRule()->name}
				</a>
			{/if}
		</span>
		<div style="padding: 15px;">
			{l s='This certificate was bought to' mod='belvg_giftcert'} {$gift->recipient_email}.
			{if $gift->recipient_email neq 'myself'}
				<br><br>
				<b>{l s='Recipient name:' mod='belvg_giftcert'}</b> <small>{$gift->recipient_name}</small><br/>
				{if !$gift->getProduct()->is_virtual}
					<b>{l s='Recipient address:' mod='belvg_giftcert'}</b> <small>{$gift->recipient_address}</small><br/>
				{/if}
				<b>{l s='Unique message:' mod='belvg_giftcert'}</b> <small>{$gift->message}</small>
			{/if}
		</div>
	{/foreach}
</fieldset>