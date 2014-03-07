
<div id="page" class="clearfix container_9">
	<div id="columns" class="grid_9 alpha omega clearfix">
		<!-- Center -->
		<div id="center_column" class="center grid_7">


{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Mon abonnement'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
{include file="$tpl_dir./errors.tpl"}

<div class='mceContentBody'>


{*if $customer->is_tierce}
	<div id='tierceSubsActive'>
		<p>{l s='Vous bénéficiez d\'un abonnement gratuitement'}
	</div>
{/if*}


<h1>{l s='Vos abonnements'}</h1>
<p>{l s='Sur cette page vous pouvez consulter tous les abonnements auxquels vous avez souscrit.'}</p>

<div class="block-center" id="block-history">
	{if isset($subs) && count($subs)}
	
		{foreach from=$subs item=sub name=myLoop}
            <table id="order-list" class="std {if $sub->is_active}active{else}inactive{/if} {if $is_conflict && $sub->type == 'user'}is_conflict{/if} {if $sub->is_future}future{else}nofuture{/if}" >
			<thead>
				<tr>
					<th colspan='2' class="first_item">
						{if $sub->is_active}
							{if $sub->type == 'tierce' && $sub->order->id_customer != $customer->id}
                                {l s='Abonnement professionnel dont vous bénéficiez'}
                            {elseif $sub->type == 'tierce' && $sub->order->id_customer == $customer->id}
                                {l s='Abonnement professionnel pour lequel vous êtes compte de référence'}
                            {elseif $sub->type == 'user' && $customer->is_tierce}
                                {l s='Abonnement en veille'}
                            {elseif $sub->type == 'user' && !$customer->is_tierce}
                                {l s='Abonnement actif'}
                            {/if}
						{elseif $sub->is_future}
                            {l s='Abonnement en attente. Débute avec le numéro '}{$sub->first_edition}
                        {elseif $sub->is_archive}
                            {l s='Abonnement expiré'}
						{/if}
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan='2'>
						<p><strong>{l s='Description'} : </strong>{$sub->product_attributes_name}</p>
					</td>
				</tr>
				<tr {if !$customer->conditions}class='last_item'{/if}>
					<td style='width:60%'>
						<p>
							<strong>{l s='Réf de la commande'} : </strong>{$sub->order->reference}<br/>
							<strong>{l s='Souscrit le '} : </strong>{$sub->order->date_add|date_format:"%A %e %B %Y"}<br/>

						</p>
					</td>
					<td style='width:40%'>
						<p>
							<strong>{l s='Dernier numéro'} : </strong>{$sub->last_edition}<br/>
							<strong>{l s='Premier numéro'} : </strong>{$sub->first_edition}<br/>
						</p>
					</td>
				</tr>
				{if $sub->customer->conditions}
				<tr class='last_item'>
					<td colspan='2'>
						<p>{l s='Cet abonnement est accessible par les comptes suivants'} : </p>
						<ul>

						{foreach from=$sub->customer->conditions item=condition}
							<li>{$condition}</li>
						{/foreach}
						</ul>
					</td>
				</tr>
				{/if}
			</tbody>
		</table>
		
	{/foreach}
	<div id="block-order-detail" class="hidden">&nbsp;</div>
	{else}
		<p class="warning">{l s='Vous n\'avez aucun abonnement actif ou ayant été actif'}</p>
	{/if}
</div>
</div>

<ul class="footer_links clearfix">
	<li><a href="{$link->getPageLink('my-account', true)}"><img src="{$img_dir}icon/my-account.gif" alt="" class="icon" /> {l s='Back to Your Account'}</a></li>
	<li class="f_right"><a href="{$base_dir}"><img src="{$img_dir}icon/home.gif" alt="" class="icon" /> {l s='Home'}</a></li>
</ul>
