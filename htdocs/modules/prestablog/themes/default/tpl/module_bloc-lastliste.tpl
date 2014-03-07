{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}

<!-- Module Presta Blog -->
<div class="block">
	<h4 class="title_block">{l s='Last blog articles' mod='prestablog'}</h4>
	<div class="block_content">
		{if $ListeBlocLastNews}
			{foreach from=$ListeBlocLastNews item=Item name=myLoop}
				<p>
					{if isset($Item.link_for_unique)}<a href="{PrestaBlogUrl id=$Item.id_prestablog_news seo=$Item.link_rewrite titre=$Item.title}">{/if}
						{if isset($Item.image_presente)}<img src="{$prestablog_theme_dir}up-img/adminth_{$Item.id_prestablog_news}.jpg?{$md5pic}" alt="{$Item.title}" class="lastlisteimg" />{/if}
						<strong>{$Item.title|truncate:40:"...":true:true}</strong><br />
						<span>{$Item.paragraph_crop}</span>
					{if isset($Item.link_for_unique)}</a>{/if}
				</p>
				{if !$smarty.foreach.myLoop.last}<hr />{/if}
			{/foreach}
		{else}
			<p>{l s='No news' mod='prestablog'}</p>
		{/if}
		{if $link_lastnews_showall}
		<p>
			<a href="{PrestaBlogUrl}" class="button_large">{l s='See all' mod='prestablog'}</a>
		</p>
		{/if}
	</div>
</div>
<!-- /Module Presta Blog -->
