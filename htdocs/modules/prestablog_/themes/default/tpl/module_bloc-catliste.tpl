{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}

<!-- Module Presta Blog -->
<div class="block">
	<h4 class="title_block">{l s='Categories blog' mod='prestablog'}</h4>
	<div class="block_content cat_prestablog">
		{if sizeof($ListeBlocCatNews)}
			{foreach from=$ListeBlocCatNews item=Item name=myLoop}
				<p>
					<a href="{PrestaBlogUrl c=$Item.id_prestablog_categorie titre=$Item.title}">{$Item.title|truncate:40:"...":true:true}</a>&nbsp;<span>({$Item.nombre_news})</span>
					{if $RssCatNews}<a target="_blank" href="{PrestaBlogUrl rss=$Item.id_prestablog_categorie}"><img src="{$prestablog_theme_dir}/img/rss.png" alt="Rss feed" align="absmiddle" /></a>{/if}
				</p>
				{if !$smarty.foreach.myLoop.last}<hr />{/if}
			{/foreach}
		{else}
			<p>{l s='No news' mod='prestablog'}</p>
		{/if}
		{if $link_catnews_showall}
		<p>
			<a href="{PrestaBlogUrl}" class="button_large">{l s='See all' mod='prestablog'}</a>
		</p>
		{/if}
	</div>
</div>
<!-- /Module Presta Blog -->
