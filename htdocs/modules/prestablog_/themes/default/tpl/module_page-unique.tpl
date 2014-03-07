{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}
<!-- Module Presta Blog -->



{if isset($News)}
<div class='mceContentBody'>

	{capture name=path}<a href="{PrestaBlogUrl}" >{l s='Blog' mod='prestablog'}</a> <span class="navigation-pipe">{$navigationPipe}</span> {$News->title}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}

	<a name="article"></a>
	<h1>{$News->title}</h1>
	
	<p class="date">
		{if $isEvent}
			{l s='Date de l\'événement :' mod='prestablog'}
		{else}
			{l s='Published :' mod='prestablog'} 
		{/if}

		{$News->date|date_format:"%e %B %Y"}
	</p>

	{if isset($News_Image)}<img src="{$prestablog_theme_dir}up-img/thumb_{$News->id}.jpg?{$md5pic}" class="news" alt="{$News->title}" style="float:left;margin:10px;"/>{/if}
	{$News->content}
	<div class="clear"></div>
	{if (sizeof($News->products_liaison))}
		<h3>{l s='Products link' mod='prestablog'}</h3>
		<ul id="productslinkscarousel" class="jcarousel-skin-tango">
		{foreach from=$News->products_liaison item=article key=key name=current}
			<li>
				<a href="{$article.link}">
					{$article.thumb}<br/>
					{$article.name}
				</a>
			</li>
		{/foreach}
		</ul>
		<script type="text/javascript">
			{literal}jQuery(document).ready(function() {
				jQuery('#productslinkscarousel').jcarousel({
					visible: {/literal}{$News->nb_products_row}{literal}
				});
			});{/literal}
		</script>
	{/if}
	


	{*if sizeof($News->categories)}
		<br />
		{l s='Categories :' mod='prestablog'} 
		{foreach from=$News->categories item=categorie key=key name=current}<a href="{PrestaBlogUrl c=$key titre=$categorie}">{$categorie}</a>
		{if $RssCategories}<sup><a target="_blank" href="{PrestaBlogUrl rss=$key}"><img src="{$prestablog_theme_dir}/img/rss.png" alt="Rss feed" align="absmiddle" /></a></sup>{/if}
		{if !$smarty.foreach.current.last},{/if}
		{/foreach}
	{/if*}

	
	
	{if $Socials || (sizeof($News->categories) && $RssCategories)}
		{if $Socials}<h3>{l s='Share this content' mod='prestablog'}</h3>{/if}
		<div class="sexy-bookmarks sexy-bookmarks-expand sexy-bookmarks-center sexy-bookmarks-bg-sexy">
			<ul class="socials">
				{if $Socials}
				<li class="shr-twitter"><a target="_blank" href="http://twitter.com/share?url={$prestablog_current_url}" rel="nofollow" class="external" title="{l s='Tweet This!' mod='prestablog'}">{l s='Tweet This!' mod='prestablog'}</a></li>
				<li class="shr-facebook"><a target="_blank" href="https://www.facebook.com/sharer.php?u={$prestablog_current_url}&t={$News->title}" rel="nofollow" class="external" title="{l s='Share this on Facebook' mod='prestablog'}">{l s='Share this on Facebook' mod='prestablog'}</a></li>
				<li class="shr-googlebookmarks"><a target="_blank" href="http://www.google.com/bookmarks/mark?op=edit&bkmk={$prestablog_current_url}&title={$News->title}" rel="nofollow" class="external" title="{l s='Add this to Google Bookmarks' mod='prestablog'}">{l s='Add this to Google Bookmarks' mod='prestablog'}</a></li>
				<li class="shr-googlereader"><a target="_blank" href="https://plusone.google.com/_/+1/confirm?hl={$lang_iso}&url={$prestablog_current_url}&title={$News->title}" rel="nofollow" class="external" title="{l s='Add this to Google +' mod='prestablog'}">{l s='Add this to Google +' mod='prestablog'}</a></li>
				{/if}
				{if sizeof($News->categories) && $RssCategories}
					{foreach from=$News->categories item=categorie key=key name=current}
						<li class="shr-comfeed"><a target="_blank" href="{PrestaBlogUrl rss=$key}" class="external" title="{l s='Get the Rss feed of category :' mod='prestablog'} {$categorie}">{l s='Get the Rss feed of category :' mod='prestablog'} {$categorie}</a></li>
					{/foreach}
				{/if}
			</ul>
		</div>
	{/if}
	</div>
{/if}
<!-- /Module Presta Blog -->
