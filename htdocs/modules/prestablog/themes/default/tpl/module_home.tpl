{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}


<!-- Module Presta Blog -->
<!-- section actus -->
{if isset($ListeBlogNews.actus) && $ListeBlogNews.actus|@count > 0}
	{assign "actu" $ListeBlogNews.actus[0]}	
	<div class='homeblock'>
			{if $actu.title!=''}
				<h2 class='homeTitle'>{if isset($actu.link_for_unique)}
					<a href="{PrestaBlogUrl id=$actu.id_prestablog_news seo=$actu.link_rewrite titre=$actu.title}" title="{$actu.title}">
					{/if}
					{$actu.title}
					{if isset($actu.link_for_unique)}</a>{/if}
				</h2>
			{/if}
		
			{if isset($actu.image_presente)}
				<a class='visu' href="{PrestaBlogUrl id=$actu.id_prestablog_news seo=$actu.link_rewrite titre=$actu.title}" title="{$actu.title}">
					<img src="{$prestablog_theme_dir}up-img/slide_{$actu.id_prestablog_news}.jpg?{$md5pic}" alt="{$actu.title}" width="{$prestablog_config.images.slide.width}" />
				</a>
			{/if}
			<div class='mceContentBody'>
			{if $actu.paragraph_crop!=''}
				<p>{$actu.paragraph_crop}</p>
			{/if}
			</div>
		<div class='date'>{l s='Posté le' mod='prestablog'} : {$actu.date|date_format:"%e %B"}</div>
		<a class='finalLink' href='/fr/prestablog-breves-c2/default'>{l s='Voir toutes les actualités' mod='prestablog'}</a>
	</div>
	
	
	<!-- affiche une 2e news s'il n'y a aucune actu -->
	{if (!isset($ListeBlogNews.events) || $ListeBlogNews.events|@count == 0) && $ListeBlogNews.actus|@count > 1 }
	{assign "actu" $ListeBlogNews.actus[1]}	
	<div class='homeblock'>
			{if $actu.title!=''}
				<h2 class='homeTitle'>{if isset($actu.link_for_unique)}
					<a href="{PrestaBlogUrl id=$actu.id_prestablog_news seo=$actu.link_rewrite titre=$actu.title}" title="{$actu.title}">
					{/if}
					{$actu.title}
					{if isset($actu.link_for_unique)}</a>{/if}
				</h2>
			{/if}
		
			{if isset($actu.image_presente)}
				<a class='visu' href="{PrestaBlogUrl id=$actu.id_prestablog_news seo=$actu.link_rewrite titre=$actu.title}" title="{$actu.title}">
					<img src="{$prestablog_theme_dir}up-img/slide_{$actu.id_prestablog_news}.jpg?{$md5pic}" alt="{$actu.title}" width="{$prestablog_config.images.slide.width}" />
				</a>
			{/if}
			
			<div class='mceContentBody'>
			{if $actu.paragraph_crop!=''}
				<p>{$actu.paragraph_crop}</p>
			{/if}
			</div>		
		<div class='date'>{l s='Posté le' mod='prestablog'} : {$actu.date|date_format:"%e %B"}</div>
		<a class='finalLink' href='/fr/prestablog-breves-c2/default'>{l s='Voir toutes les actualités' mod='prestablog'}</a>
	</div>
	{/if}	
{/if}








<!-- section agenda -->
{if isset($ListeBlogNews.events) && $ListeBlogNews.events|@count > 0}
	{assign "event" $ListeBlogNews.events[0]}	
	<div  class='homeblock'>
			{if $event.title!=''}
				<h2 class='homeTitle'>{if isset($event.link_for_unique)}
					<a href="{PrestaBlogUrl id=$event.id_prestablog_news
						seo=$event.link_rewrite titre=$event.title}" title="{$event.title}">
					{/if}
					{$event.title}
					{if isset($event.link_for_unique)}</a>{/if}
				</h2>
			{/if}
		
		
			{if isset($event.image_presente)}
				<a class='visu' href="{PrestaBlogUrl id=$event.id_prestablog_news seo=$event.link_rewrite titre=$event.title}" title="{$event.title}">
					<img src="{$prestablog_theme_dir}up-img/slide_{$event.id_prestablog_news}.jpg?{$md5pic}" alt="{$event.title}" width="{$prestablog_config.images.slide.width}" />
				</a>
			{/if}
		
			<div class='mceContentBody'>
			{if $event.paragraph_crop!=''}
				<p>{$event.paragraph_crop}</p>
			{/if}
			</div>
		<div class='date'>{l s='Date de l\'événement' mod='prestablog'} : {$event.date|date_format:"%e %B"}</div>
		<a class='finalLink' href='/fr/prestablog-agenda-c3/default'>{l s='Voir touts les événements' mod='prestablog'}</a>
	</div>
	
	
	<!-- affiche un 2e events s'il n'y a aucune actu -->
	{if (!isset($ListeBlogNews.actus) || $ListeBlogNews.actus|@count == 0) && $ListeBlogNews.events|@count > 1}
		{assign "event" $ListeBlogNews.events[1]}	
		<div  class='homeblock '>
				{if $event.title!=''}
					<h2 class='homeTitle'>{if isset($event.link_for_unique)}
						<a href="{PrestaBlogUrl id=$event.id_prestablog_news
							seo=$event.link_rewrite titre=$event.title}" title="{$event.title}">
						{/if}
						{$event.title}
						{if isset($event.link_for_unique)}</a>{/if}
					</h2>
				{/if}


				{if isset($event.image_presente)}
					<a class='visu' href="{PrestaBlogUrl id=$event.id_prestablog_news seo=$event.link_rewrite titre=$event.title}" title="{$event.title}">
						<img src="{$prestablog_theme_dir}up-img/slide_{$event.id_prestablog_news}.jpg?{$md5pic}" alt="{$event.title}" width="{$prestablog_config.images.slide.width}" />
					</a>
				{/if}

				<div class='mceContentBody'>
				{if $event.paragraph_crop!=''}
					<p>{$event.paragraph_crop}</p>
				{/if}
				</div>
			<div class='date'>{l s='Date de l\'événement' mod='prestablog'} : {$event.date|date_format:"%e %B"}</div>
			<a class='finalLink' href='/fr/prestablog-agenda-c3/default'>{l s='Voir touts les événements' mod='prestablog'}</a>
		</div>
	{/if}
	
	
{/if}
<!-- /Module Presta Blog -->

