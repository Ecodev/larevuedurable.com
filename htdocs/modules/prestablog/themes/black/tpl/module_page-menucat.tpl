{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}
<!-- Module Presta Blog -->
<ul id="prestablog_menu_cat">
	{if sizeof($ListeCatNews)}
		{foreach from=$ListeCatNews item=Item name=myLoop}
			<li>
				<a href="{PrestaBlogUrl c=$Item.id_prestablog_categorie titre=$Item.title}">{$Item.title|truncate:40:"...":true:true}</a>&nbsp;<span>({$Item.nombre_news})</span>
				{if $RssMenuCat}<a target="_blank" href="{PrestaBlogUrl rss=$Item.id_prestablog_categorie}"><img src="{$prestablog_theme_dir}/img/rss.png" alt="Rss feed" align="absmiddle" /></a>{/if}
			</li>
		{/foreach}
	{/if}
</ul>
<!-- Module Presta Blog -->
