<div class='homeblock' >

	<div class='homeTitle'>
		<a href="{$nextEditoProduct->getLink()|escape:'htmlall':'UTF-8'}">
			{l s='Dernier éditorial' mod='ecodevnextconf'}
		</a>
	</div>

	{if $nextEditoImageBool}
	<a id='editoPict' href="{$nextEditoProduct->getLink()|escape:'htmlall':'UTF-8'}">
		<img id='nextEditoImg' src="{$link->getImageLink($nextEditoProduct->link_rewrite, $nextEditoImage, 'home_portrait')}" />
	</a>
    {/if}
	<span id='prochaineConferenceDetailTitre' class='mceContentBody'>{$nextEditoProduct->description_short}</span>
	<a class='finalLink' href="{$nextEditoProduct->getLink()|escape:'htmlall':'UTF-8'}">{l s='Voir l\'éditorial' mod='ecodevnextconf'}</a>
</div>




<div class='homeblock' >

	<div class='homeTitle'>
		<a href="{$nextIndicatorProduct->getLink()|escape:'htmlall':'UTF-8'}">
			{l s='Indicateurs' mod='ecodevnextconf'}
		</a>
	</div>

	{if $nextIndicatorImageBool}
    <a id='indicatorPict' href="{$nextIndicatorProduct->getLink()|escape:'htmlall':'UTF-8'}">
        <img id='nextEditoImg' src="{$link->getImageLink($nextIndicatorProduct->link_rewrite, $nextIndicatorImage, 'home_pleine_largeur_grand')}" />
    </a>
    {/if}
    <span id='prochaineConferenceDetailTitre' class='mceContentBody'>{$nextIndicatorProduct->description_short}</span>
	<a class='finalLink' href="{$nextIndicatorProduct->getLink()|escape:'htmlall':'UTF-8'}">{l s='Voir les indicateurs' mod='ecodevnextconf'}</a>
</div>
