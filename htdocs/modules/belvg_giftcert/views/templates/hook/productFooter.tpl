<div id="gift_container">
	<div class="gift_inner">
		<b>Envoyer ce bon cadeau :</b>
			<input type="radio" class="gift_radio" name="belvg_send_gift" value="myself" checked="checked"> A moi même
			<input type="radio" class="gift_radio" name="belvg_send_gift" value="friend"> A un ami
		<div class="gift_inner_hider">
			<br>
				Nom du destinataire <br/>
				<input class="gift_input recipient_name" type="text" name="belvg_recipient_name" value="">
			<br>
				Email du destinataire <br/>
				<input class="gift_input recipient_email" type="text" name="belvg_recipient_email" value="">
			{if !$belvg_product->is_virtual}
				<br>
					Adresse du destinataire<br>
					<textarea class="gift_input recipient_address" name="belvg_recipient_address"></textarea>
			{/if}
			<br>
                Votre message (optionel)<br/>
                <textarea class="gift_input recipient_message" name="belvg_recipient_message"></textarea>
			<br>
		</div>
	</div>
</div>
<script>
	var giftType = '{$belvg_gift->price_type}';
	var giftPrice = {$belvg_gift->getPriceValue()};
	var price_label = "Saisissez le montant :";
	var custom_price_label = "Votre prix :";
</script>