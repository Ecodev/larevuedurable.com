{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}



<!-- Module Presta Blog -->
{capture name=path}<a href="{PrestaBlogUrl}" >{l s='Blog' mod='prestablog'}</a>{if $SecteurName} <span class="navigation-pipe">{$navigationPipe}</span> {$SecteurName}{/if}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h1>{$prestablog_categorie_name}</h1>
{if sizeof($News)}

	{include file="$tpl_dir./../../modules/prestablog/themes/$prestablog_theme/tpl/module_page-pagination.tpl"}
	<ul id="product_list" class="articles news">
	{foreach from=$News item=news name=NewsName}
		<li class="blog ajax_block_product {if $smarty.foreach.NewsName.first}first_item{elseif $smarty.foreach.NewsName.last}last_item{/if} {if $smarty.foreach.NewsName.index % 2}alternate_item{else}item{/if} clearfix">
		
			<div class="center_block mceContentBody">
				<h3>
					{if isset($news.link_for_unique)}<a href="{PrestaBlogUrl id=$news.id_prestablog_news seo=$news.link_rewrite titre=$news.title}" title="{$news.title}">{/if}{$news.title|truncate:35:'...'|escape:'htmlall':'UTF-8'}{if isset($news.link_for_unique)}</a>{/if}
				</h3>
				
				<div class="date">
					{if $currentCategoryID=='3'}
					{l s='Date de l\'événement :' mod='prestablog'}
					{else}
						{l s='Published :' mod='prestablog'}
					{/if}
					{$news.date|date_format:"%e %B"}
				</div>


				<p class="product_desc">
					{if $news.paragraph_crop!=''}
						{$news.paragraph_crop|strip_tags:'UTF-8'}
					{/if}
				</p>
				{if isset($news.link_for_unique)}
					<a class='button_mini' href="{PrestaBlogUrl id=$news.id_prestablog_news seo=$news.link_rewrite titre=$news.title}">{l s='Read more' mod='prestablog'}</a>
				{/if}
			</div>
			
			{if isset($news.image_presente)}
			<div class="right_block">
				{if isset($news.link_for_unique)}<a href="{PrestaBlogUrl id=$news.id_prestablog_news seo=$news.link_rewrite titre=$news.title}" class="product_img_link" title="{$news.title}">{/if}
					<img src="{$base_dir_ssl}modules/prestablog/themes/{$prestablog_theme}/up-img/thumb_{$news.id_prestablog_news}.jpg?{$md5pic}" width="129"  alt="{$news.title}" />
				{if isset($news.link_for_unique)}</a>{/if}
			</div>
			{/if}
			
			
		</li>
	{/foreach}
	</ul>
	{include file="$tpl_dir./../../modules/prestablog/themes/$prestablog_theme/tpl/module_page-pagination.tpl"}
{else}
	<p class="warning">{l s='Empty' mod='prestablog'}</p>
{/if}

<!-- /Module Presta Blog -->
