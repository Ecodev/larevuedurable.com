(function($){
	$(document).ready(function(){

		var isClicked = false;
		var priceValidate = true;
		var giftValidate = true;
		$('#add_to_cart input').mousedown(function(event){
			//if (!isClicked) {
				$("#add_to_cart input").unbind('click');
				$("#add_to_cart input").click(function(event){
					event.stopImmediatePropagation();
					if (priceValidate && giftValidate) {
						ajaxCart.add( $('#product_page_product_id').val(), $('#idCombination').val(), true, null, $('#quantity_wanted').val(), null);
					}

					return false;
				});
			//}
			
			isClicked = true;
		});
		
		function isEmail(email) {
			var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			return regex.test(email);
		}

		var addToCartEvent = true;
		var price_elem = $('#our_price_display');
		if (price_elem.length) {

			price_elem.attr('id', 'our_price_display_old');

			var additionalQuery = "&belvg_giftcert='+encodeURIComponent($('#belvg_price').val())+'";
			additionalQuery += "&belvg_send_gift='+encodeURIComponent($('.gift_radio:checked').val())+'";
			additionalQuery += "&belvg_recipient_message='+encodeURIComponent($('.recipient_message').val())+'";
			additionalQuery += "&belvg_recipient_name='+encodeURIComponent($('.recipient_name').val())+'";
			additionalQuery += "&belvg_recipient_email='+encodeURIComponent($('.recipient_email').val())+'";
			additionalQuery += "&belvg_recipient_address='+encodeURIComponent($('.recipient_address').val())+'";
			var addToCartRewrite = ajaxCart.add.toString()
				.replace('function ', 'function addToCartFunc')
				.replace("controller=cart&add=1", "controller=cart&add=1"+additionalQuery);

			eval(addToCartRewrite);
			ajaxCart.add = addToCartFunc;

			if (giftType == 'dropdown') {
				price_elem.hide().after(
					$('<select>').attr({
						'name': 'belvg_giftcert',
						'id': 'belvg_price' 
					})				
				);
				
				for (var price in giftPrice) {
					$('#belvg_price').append('<option value="'+giftPrice[price]+'">'+formatCurrency(giftPrice[price], currencyFormat, currencySign, currencyBlank)+'</option>');
				}
				
				$('#belvg_price').before('<span class="belvg_custom_price">'+price_label+'</span>');
			} else if (giftType == 'fixed') {

                price_elem.text(formatCurrency(giftPrice[0], currencyFormat, currencySign, currencyBlank));
				price_elem.after(
					$('<input>').attr({
						'name': 'belvg_giftcert',
						'id': 'belvg_price',
						'type': 'hidden',
						'value': giftPrice[0]
					})
				);
			} else {
				price_elem.hide().after(
					$('<input>').attr({
						'name': 'belvg_giftcert',
						'id': 'belvg_price' 
					}).css({
						'width': productPrice.length * 15 + 'px',
						'text-align': 'center',
					})					
				);

				$('#belvg_price').keydown(function(e){
		            var key = e.charCode || e.keyCode || 0;
		            return (
		                key == 8 || 
		                key == 9 ||
		                key == 46 ||
		                key == 110 ||
		                key == 190 ||
		                (key >= 35 && key <= 40) ||
		                (key >= 48 && key <= 57) ||
		                (key >= 96 && key <= 105)
		            );
				});
				
				$('#belvg_price').before('<span class="belvg_custom_price">'+price_label+'</span>');
				
				
				$('#belvg_price').after('<div class="belvg_amount">From '+formatCurrency(giftPrice[0], currencyFormat, currencySign, currencyBlank)+' to '+formatCurrency(giftPrice[1], currencyFormat, currencySign, currencyBlank)+'</div>');
				
				$('#add_to_cart input').mousedown(function(){
					priceValidate = true;
					var val = parseFloat($('#belvg_price').val());
					if (isNaN(val)) {
						//alert('Invalid amount!');
						$('#belvg_price').focus();
						priceValidate = false;
						return false;
					}
					
					if (giftType == 'range') {
						if (val < giftPrice[0]) {
							//alert('Amount must be larger then ' + formatCurrency(giftPrice[0], currencyFormat, currencySign, currencyBlank));
							$('#belvg_price').focus();
							priceValidate = false;
							return false;
						}
						
						if (val > giftPrice[1]) {
							//alert('Amount must be less then ' + formatCurrency(giftPrice[1], currencyFormat, currencySign, currencyBlank));
							$('#belvg_price').focus();
							priceValidate = false;
							return false;
						}
					}
				});
			}

            console.log('asdf');
            console.log($('#buy_block .price'));
            console.log($('#gift_container'));

            $('#buy_block .price').after($('#gift_container').html());
			$('#gift_container').remove();
			
			$('#old_price, #reduction_percent, #reduction_amount').hide();
			
			$('#add_to_cart').mousedown(function(event){
				giftValidate = true;
				if ($('input[name="belvg_send_gift"]:checked').val() == 'friend' && addToCartEvent) {			
					var valid = true;
					$('.recipient_email, .recipient_name, .recipient_address').each(function(){
						if ($.trim($(this).val()) == '') {
							$(this).focus();
							valid = false;
							return false;
						}
					});
					
					if (!valid) {
						//alert('Please fill recipient\'s data');
						giftValidate = false;
						return false;
					}
					
					if (!isEmail($('.recipient_email').val())) {
						//alert('Email is invalid!');
						$('.recipient_email').focus();
						giftValidate = false;
						return false;
					}
				}
			});
			
			$('.gift_inner .gift_radio').click(function(){
				$('.gift_inner_hider').hide();
				if ($(this).val() == 'friend') {
					$('.gift_inner_hider').show();
				}
			});
		}
	});
})(jQuery);