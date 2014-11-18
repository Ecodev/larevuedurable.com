<div style="display:none" id="gift_container">
	<div class="gift_inner">
		<b>{l s='Send this gift certificate(s):' mod='belvg_customprice'}</b>
		<br><br>
			<input type="radio" class="gift_radio" name="belvg_send_gift" value="myself" checked="checked"> {l s='To Myself' mod='belvg_customprice'}
			<input type="radio" class="gift_radio" name="belvg_send_gift" value="friend"> {l s='To Friend' mod='belvg_customprice'}
		<div class="gift_inner_hider">
			<br>
				{l s='Recipient\'s name:' mod='belvg_customprice'}<br>
				<input class="gift_input recipient_name" type="text" name="belvg_recipient_name" value="">
			<br>
				{l s='Recipient\'s email:' mod='belvg_customprice'}<br>
				<input class="gift_input recipient_email" type="text" name="belvg_recipient_email" value="">
			{if !$belvg_product->is_virtual}
				<br>
					{l s='Recipient\'s postal address:' mod='belvg_customprice'}<br>
					<textarea class="gift_input recipient_address" name="belvg_recipient_address"></textarea>
			{/if}
			<br>
				{l s='Your message (optional):' mod='belvg_customprice'}<br>
				<textarea class="gift_input recipient_message" name="belvg_recipient_message"></textarea>
			<br>
			<br>
		</div>
	</div>
</div>
<script>
	var giftType = '{$belvg_gift->price_type}';
	var giftPrice = {$belvg_gift->getPriceValue()};
	var price_label = "{l s='Select amount:' mod='belvg_customprice' js=1}";
	var custom_price_label = "{l s='Your price:' mod='belvg_customprice' js=1}";
</script>