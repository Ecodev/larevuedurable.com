{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}
<!-- Module Presta Blog -->
<!-- Block categories module -->
<div id="categories_block_left" class="block">
	<!--<p class="title_block">{l s='Categories' mod='blockcategories'}</p>-->
	<div class="block_content">
		<ul class="tree {if isset($isDhtml) && $isDhtml}dhtml{/if}">

			{if sizeof($ListeCatNews)}
				{foreach from=$ListeCatNews item=Item name=myLoop}
					<li {if isset($smarty.foreach.myLoop.last) && $smarty.foreach.myLoop.last == 'true'}class="last"{/if}>
						<a {if isset($Category) && $Item.id_prestablog_categorie == $Category}class="selected"{/if} href="{PrestaBlogUrl c=$Item.id_prestablog_categorie titre=$Item.title}">{$Item.title|truncate:40:"...":true:true}</a><!--&nbsp;<span>({$Item.nombre_news})</span>-->
						{if $RssMenuCat}<a target="_blank" href="{PrestaBlogUrl rss=$Item.id_prestablog_categorie}"><img src="{$prestablog_theme_dir}/img/rss.png" alt="Rss feed" align="absmiddle" /></a>{/if}
					</li>
				{/foreach}
			{/if}
		</ul>
		{* Javascript moved here to fix bug #PSCFI-151 *}
		<script type="text/javascript">
		// <![CDATA[
			// we hide the tree only if JavaScript is activated
			$('div#categories_block_left ul.dhtml').hide();
		// ]]>
		</script>
	</div>
</div>
<!-- Module Presta Blog -->

