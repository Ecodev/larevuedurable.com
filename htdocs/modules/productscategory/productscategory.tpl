
{if isset($categoryProducts) && count($categoryProducts) > 0 && $categoryProducts !== false}
	<ul id="idTab20" class="bullet">
		{foreach from=$categoryProducts item='categoryProduct'}
			{if ($categoryProduct.allow_oosp || $categoryProduct.quantity_all_versions > 0 || $categoryProduct.quantity > 0) AND $categoryProduct.available_for_order AND !isset($restricted_country_mode)}
				{assign var='accessoryLink' value=$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category)}
				<li class="ajax_block_product item product_categoryProduct_description">
						<a href="{$accessoryLink|escape:'htmlall':'UTF-8'}">N° {$categoryProduct.reference|regex_replace:"/[_-]/":" p."} : {$categoryProduct.name|escape:'htmlall':'UTF-8'}</a>
				</li>
			{/if}
		{/foreach}
	</ul>
{/if}


<!-- <li>
	<a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" class="lnk_img" title="{$categoryProduct.name|htmlspecialchars}"><img src="{$link->getImageLink($categoryProduct.link_rewrite, $categoryProduct.id_image, 'medium')}" alt="{$categoryProduct.name|htmlspecialchars}" /></a>
	<p class="product_name">
		<a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" title="{$categoryProduct.name|htmlspecialchars}">{$categoryProduct.name|truncate:15:'...'|escape:'htmlall':'UTF-8'}</a>
	</p>
	{if $ProdDisplayPrice AND $categoryProduct.show_price == 1 AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
	<p class="price_display">
		<span class="price">{convertPrice price=$categoryProduct.displayed_price}</span>
	</p>
	{else}
	<br />
	{/if}
</li> -->