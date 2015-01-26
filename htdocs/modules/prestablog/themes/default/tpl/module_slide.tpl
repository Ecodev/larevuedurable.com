{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}
<!-- Module Presta Blog -->
<div id="prestablog_slide_home">
	{foreach from=$ListeBlogNews item=slide name=slides}
	<div class="prestablog_visuels">
		{if isset($slide.image_presente)}
			<img src="{$prestablog_theme_dir}up-img/slide_{$slide.id_prestablog_news}.jpg?{$md5pic}" class="visu" alt="{$slide.title}" width="{$prestablog_config.images.slide.width}" />
		{/if}
		<p class="date-slide">{$slide.date|date_format:"%e %b"}</p>
		<div class="resume_blog">
		{if $slide.title!=''}
			<h2>{if isset($slide.link_for_unique)}
				<a href="{PrestaBlogUrl id=$slide.id_prestablog_news
					seo=$slide.link_rewrite titre=$slide.title}" title="{$slide.title}">
				{/if}
				{$slide.title}
				{if isset($slide.link_for_unique)}</a>{/if}</h2>
		{/if}
		{if $slide.paragraph_crop!=''}
			<p>{$slide.paragraph_crop}
			{if isset($slide.link_for_unique)}
				<a href="{PrestaBlogUrl id=$slide.id_prestablog_news seo=$slide.link_rewrite titre=$slide.title}">{l s='Read more' mod='prestablog'}</a>
			{/if}
			</p>
		{/if}
		</div>
	 </div>
	{/foreach}
</div>
<div id="prestablog_nav_slide"></div>
<script type="text/javascript">
	{literal}
	$(document).ready(function() {
		$('#prestablog_slide_home').cycle({
			fx: '{/literal}{$prestablog_config.slide_effect}{literal}',
			speed:  {/literal}{$prestablog_config.slide_speed}{literal},
			timeout: {/literal}{$prestablog_config.slide_timeout}{literal},
			pager:  '#prestablog_nav_slide',
			slideExpr: 'div.prestablog_visuels'
		});
	});
	{/literal}
</script>
<div class="clearfix"></div>
<!-- /Module Presta Blog -->

