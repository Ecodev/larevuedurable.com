{if isset($orderProducts) && count($orderProducts) > 0}
<div id="idTab50" >
	<ul class="bullet">
		<div class='mceContentBody'><p>{l s='Les personnes qui ont acheté ce produit, ont également acheté ces produits : ' mod='crossselling'}</p></div>
		{foreach from=$orderProducts item='orderProduct' name=orderProduct}
		<li>
			<a href="{$orderProduct.link}" title="{$orderProduct.name|htmlspecialchars}">N° {$orderProduct.reference|regex_replace:"/[_-]/":" p."} : {$orderProduct.name|escape:'htmlall':'UTF-8'}</a>
		</li>
		{/foreach}
	</ul>
</div>
{/if}
