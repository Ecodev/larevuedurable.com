<form action="{Tools::safeOutput($smarty.server.REQUEST_URI)}" method="post" class="panel">
	<h2>{$belvg_giftcert->displayName}</h2>
	{if $belvg_output eq true}
		<div class="alert alert-success">
			{l s='Update successful' mod='belvg_giftcert'}
		</div>
	{/if}
	<fieldset>
		<legend><img src="../modules/belvg_giftcert/logo.png" /></legend>
			<label>{l s='Order status to create gift certificate after purchase:' mod='belvg_giftcert'}</label>
			<div class="margin-form">
				<select name="order_state">
					{foreach from=OrderState::getOrderStates(Context::getContext()->language->id) item=state}
						<option {if $belvg_giftcert->getParam('order_state') == $state.id_order_state}selected{/if} value="{$state.id_order_state}">{$state.name}</option>
					{/foreach}
				</select>
			</div>
			<div class="clear"></div>
	<div class="panel-footer">
		<button type="submit" name="belvg_giftcert" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='belvg_giftcert'}</button>
	</div>	
	</fieldset>
	<br>
	

</form>