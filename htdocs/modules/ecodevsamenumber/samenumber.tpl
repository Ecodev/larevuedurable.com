{if isset($sameNumberProducts) && count($sameNumberProducts) > 0}
<div id="idTab101" >
	<ul class="bullet">
		<div class='mceContentBody'></div>
		{foreach from=$sameNumberProducts item='orderProduct' name=orderProduct}
		<li>
			<a href="{$orderProduct.link}" title="{$orderProduct.name|htmlspecialchars}">N° {$orderProduct.reference|regex_replace:"/[_-]/":" p."} : {$orderProduct.name|escape:'htmlall':'UTF-8'}</a>
		</li>
		{/foreach}
	</ul>
</div>
{/if}
