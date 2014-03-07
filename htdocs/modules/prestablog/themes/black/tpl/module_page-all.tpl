{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}

<!-- Module Presta Blog -->
{capture name=path}<a href="{PrestaBlogUrl}" >{l s='Blog' mod='prestablog'}</a>{if $SecteurName}&nbsp;>&nbsp;{$SecteurName}{/if}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2><span>{$NbNews} {if $NbNews <> 1}{l s='articles' mod='prestablog'}{else}{l s='article' mod='prestablog'}{/if}</span></h2>

{if sizeof($News)}
	{include file="$tpl_dir./../../modules/prestablog/themes/$prestablog_theme/tpl/module_page-pagination.tpl"}
	<ul id="blog_list">
	{foreach from=$News item=news name=NewsName}
		<li>
			<div class="center_block">
				<h3>
					{if isset($news.link_for_unique)}<a href="{PrestaBlogUrl id=$news.id_prestablog_news seo=$news.link_rewrite titre=$news.title}" title="{$news.title}">{/if}{$news.title|truncate:35:'...'|escape:'htmlall':'UTF-8'}{if isset($news.link_for_unique)}</a>{/if}
				<br /><span class="date_blog-cat">{l s='Published :' mod='prestablog'}
						{dateFormat date=$news.date full=1}
						{if sizeof($news.categories)} | {l s='Categories :' mod='prestablog'}{foreach from=$news.categories item=categorie key=key name=current}
						<a href="{PrestaBlogUrl c=$key titre=$categorie}" class="categorie_blog">{$categorie}</a>
						{if !$smarty.foreach.current.last},{/if}
					{/foreach}{/if}</span>
				</h3>
				<hr />
				<p class="blog_desc">
					{if $news.paragraph_crop!=''}
						{$news.paragraph_crop|strip_tags:'UTF-8'}
					{/if}
				</p>
				{if isset($news.link_for_unique)}
					<p>
						<a href="{PrestaBlogUrl id=$news.id_prestablog_news seo=$news.link_rewrite titre=$news.title}" class="blog_link">{l s='Read more' mod='prestablog'}</a>
					</p>
				{/if}
			</div>
			{if isset($news.image_presente)}
			<div class="left_block">
				{if isset($news.link_for_unique)}<a href="{PrestaBlogUrl id=$news.id_prestablog_news seo=$news.link_rewrite titre=$news.title}" class="product_img_link" title="{$news.title}">{/if}
					<img src="{$base_dir_ssl}modules/prestablog/themes/{$prestablog_theme}/up-img/thumb_{$news.id_prestablog_news}.jpg?{$md5pic}" width="129" height="129" alt="{$news.title}" />
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
