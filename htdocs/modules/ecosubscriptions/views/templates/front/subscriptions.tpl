
<div id="page" class="clearfix container_9">
	<div id="columns" class="grid_9 alpha omega clearfix">
		<!-- Center -->
		<div id="center_column" class="center grid_7">


{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Mon abonnement'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
{include file="$tpl_dir./errors.tpl"}

<div class='mceContentBody'>

	<h1>{l s='Abonnements souscrits'}</h1>
	<p>{l s='Sur cette page vous pouvez consulter tous les abonnements auxquels vous avez souscrit.'}</p>

	<div class="block-center" id="block-history">

		{if $customer->tierce_subscription}
			<h1>Abonnement professionnel dont vous bénéficiez</h1>
			{include file='./subscription-line.tpl' sub=$customer->tierce_subscription}
		{/if}

		{if $customer->user_subscriptions|count}
			<h1>Vos abonnements</h1>
			{foreach from=$customer->user_subscriptions|@array_reverse item=sub name=myLoop}
				{include file='./subscription-line.tpl' sub=$sub}
			{/foreach}
		{/if}

		{if $customer->user_ignored_subscriptions|count}
			<h1>Autres abonnements souscrits</h1>
			{foreach from=$customer->user_ignored_subscriptions item=sub name=myLoop}
				{include file='./subscription-line.tpl' sub=$sub}
			{/foreach}
		{/if}

	</div>
</div>

<ul class="footer_links clearfix">
	<li><a href="{$link->getPageLink('my-account', true)}"><img src="{$img_dir}icon/my-account.gif" alt="" class="icon" /> {l s='Back to Your Account'}</a></li>
	<li class="f_right"><a href="{$base_dir}"><img src="{$img_dir}icon/home.gif" alt="" class="icon" /> {l s='Home'}</a></li>
</ul>
