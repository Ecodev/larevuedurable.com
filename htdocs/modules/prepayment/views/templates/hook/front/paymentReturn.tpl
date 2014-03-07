{if $status == 'ok'}
	<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='prepayment'}
		<br /><br />
		<br /><br /><strong>{l s='Your order is finished and paid.' mod='prepayment'}</strong>
		<br /><br />{l s='An e-mail has been sent to you with this information.' mod='prepayment'}
		<br /><br />{l s='For any questions or for further information, please contact our' mod='prepayment'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='prepayment'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='prepayment'}
		<a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='prepayment'}</a>.
	</p>
{/if}
