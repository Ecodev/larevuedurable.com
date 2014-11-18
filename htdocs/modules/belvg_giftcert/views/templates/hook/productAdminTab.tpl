{if isset(Context::getContext()->cookie->belvg_error) && Context::getContext()->cookie->belvg_error != false}
	<div class="alert alert-danger">{Context::getContext()->cookie->belvg_error}</div>
{/if}
<div id="belvg_giftcert" class="panel">
	<input type="hidden" name="belvg_giftcert[id_product]" value="{$belvg_product->id}" />
	<input type="hidden" name="belvg_giftcert[id_shop]" value="{$id_shop}" />
	<h3>{l s='BelVG Gift Certificates' mod='belvg_giftcert'}</h3>
	<div class="separation"></div>
	<table>
		<tbody>
			<tr>
				<td class="col-left" style="padding-right: 20px;">
					<label>{l s='Use this product as gift certificate:' mod='belvg_giftcert'}</label>
				</td>
				<td style="padding-bottom:15px;">
					<div>
						<label class="t" for="belvg_giftcert_enabled">
							<img src="../img/admin/enabled.gif">
						</label>
						<input type="radio" name="belvg_giftcert[is_enabled]" id="belvg_giftcert_enabled" value="1"{if $belvg_giftcert->is_enabled eq 1} checked="checked"{/if}>
						<label class="t" for="belvg_giftcert_enabled"> {l s='Yes' mod='belvg_giftcert'}</label>
						<label class="t" for="belvg_giftcert_disabled">
							<img src="../img/admin/disabled.gif" style="margin-left: 10px;">
						</label>
						<input type="radio" name="belvg_giftcert[is_enabled]" id="belvg_giftcert_disabled" value="0"{if $belvg_giftcert->is_enabled neq 1} checked="checked"{/if}>
						<label class="t" for="belvg_giftcert_disabled"> {l s='No' mod='belvg_giftcert'}</label>
					</div>
				</td>
			</tr>
			<tr>
				<td class="col-left">
					<label>{l s='Price type:' mod='belvg_giftcert'}</label>
				</td>
				<td style="padding-bottom:15px;">
					<div>
						<input class="price_type" type="radio" name="belvg_giftcert[price_type]" id="belvg_giftcert_dropdown" value="dropdown">
						<label class="t" for="belvg_giftcert_dropdown"> {l s='Dropdown' mod='belvg_giftcert'}</label>

						<input class="price_type" type="radio" name="belvg_giftcert[price_type]" id="belvg_giftcert_fixed" value="fixed">
						<label class="t" for="belvg_giftcert_fixed"> {l s='Fixed' mod='belvg_giftcert'}</label>

						<input class="price_type" type="radio" name="belvg_giftcert[price_type]" id="belvg_giftcert_range" value="range">
						<label class="t" for="belvg_giftcert_range"> {l s='Range' mod='belvg_giftcert'}</label>

						<input class="price_type" type="radio" name="belvg_giftcert[price_type]" id="belvg_giftcert_custom" value="custom">
						<label class="t" for="belvg_giftcert_custom"> {l s='Custom price' mod='belvg_giftcert'}</label>
					</div>
				</td>
			</tr>
			<tr>
				<td class="col-left">
					<label>{l s='Price values:' mod='belvg_giftcert'}</label>
				</td>
				<td style="padding-bottom:15px;">
					<div>
						<input type="text" name="belvg_giftcert[price_value]" id="belvg_giftcert_price_value" value="{$belvg_giftcert->price_value}" style="width: 290px;">
						<p style="display:none" class="preference_description price-dropdown">{l s='Example: 10;50;100;200' mod='belvg_giftcert'}</p>
						<p style="display:none" class="preference_description price-fixed">{l s='Example: 100' mod='belvg_giftcert'}</p>
						<p style="display:none" class="preference_description price-range">{l s='Example: 50-300' mod='belvg_giftcert'}</p>
						<p style="display:none" class="preference_description price-custom">{l s='Leave empty for CUSTOM.' mod='belvg_giftcert'}</p>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="panel-footer">
		<a href="{$link->getAdminLink('AdminProducts')}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel'}</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save'}</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay'}</button>
	</div>
</div>
<script>
	(function($){
		$('#belvg_giftcert input.price_type').click(function(){
			$('#belvg_giftcert_price_value').show();
			$('#belvg_giftcert .preference_description').hide();
			$('#belvg_giftcert .preference_description.price-'+$(this).val()).show();
			if ($(this).val() == 'custom') {
				$('#belvg_giftcert_price_value').hide();
			}
		});
		
		{if $belvg_giftcert->price_type}
			$('#belvg_giftcert input.price_type[value="{$belvg_giftcert->price_type}"]').click();
		{else}
			$('#belvg_giftcert input.price_type[value="dropdown"]').click();
		{/if}
		
		//$('#belvg_giftcert').append('<div class="panel-footer">'+$('.product-tab-content .panel-footer:first').html()+'</div>');
	})(jQuery);
</script>