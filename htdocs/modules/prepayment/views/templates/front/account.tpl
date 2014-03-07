
<div id="page" class="clearfix container_9">
	<div id="columns" class="grid_9 alpha omega clearfix">
		<!-- Center -->
		<div id="center_column" class="center grid_7">


	{capture name=path}<a href="{$link->getPageLink('my-account', true)}">{l s='My account' mod='prepayment'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='My prepaid account' mod='prepayment'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
	{include file="$tpl_dir./errors.tpl"}
	
<div class='mceContentBody'>
	<h1>{l s='My prepaid account' mod='prepayment'}</h1>
	<p>{l s='Your prepaid account is the easiest way to shop!' mod='prepayment'}<br />
	{l s='Just recharge and use it to shop anything you want.' mod='prepayment'}</p>
	
	<div id="balance">
	    {l s='Current balance:' mod='prepayment'}&nbsp;<span class="price bold" style="color:{if $balance == 0}#3B5998{elseif $balance > 0}#5C9939{else}#DA0F00{/if};">{if $balance == 0}{displayPrice price=0}{else}{displayPrice price=$balance}{/if}</span>
	</div>
	<div id="recharge">
	    <form method="post" action="{$link->getModuleLink('prepayment', 'recharge', array(), true)}">
	        <p>
	        {if count($amount)}
	            <select name="amount" id="amount">
	                {foreach from=$amount item=amt name=myLoop}
	                    {assign var="price" value=Product::getPriceStatic($amt->id_product)}
	                    {if $amt->reduction > 0}
	                        {assign var="priceExtra" value=$amt->getReduction($price, $currency->id)}
	                    {/if}
	                    <option value="{$amt->id}">{displayPrice price=$price}{if $amt->reduction > 0} + {displayPrice price=$priceExtra} {l s='offered' mod='prepayment'} {/if}</option>
	                {/foreach}
	            </select>
	            <input type="submit" class="exclusive_large" name="load" value="{l s='Recharge my account' mod='prepayment'}" />
	        {else}
	            &nbsp;
	        {/if}
	        </p>
	    </form>
	</div>
	<div class="clear"></div>
	
	<div class="block-center" id="block-history">
	    {if $history && count($history)}
	    <table id="order-list" class="std">
	        <thead>
	            <tr>
	                <th class="first_item">{l s='Order Reference' mod='prepayment'}</th>
	                <th class="columnDate">{l s='Date' mod='prepayment'}</th>
	                <th>{l s='Amount' mod='prepayment'}</th>
	                <th class="item">{l s='Payment' mod='prepayment'}</th>
	                <th class="item">{l s='Status' mod='prepayment'}</th>
	                <th class="last_item">{l s='Invoice' mod='prepayment'}</th>
	            </tr>
	        </thead>
	        <tbody>
	        {foreach from=$history item=hist name=myLoop}
	            {assign var="order" value=$hist->getOrder()}
	            <tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
	                <td class="history_link bold">{if $order.id_order}{Order::getUniqReferenceOf($order.id_order)}{else}-{/if}</td>
	                <td class="history_date bold">{dateFormat date=$hist->date full=0}</td>
	                <td class="history_price bold"><span class="price" style="color:{if $hist->amount > 0}#5C9939{else}#DA0F00{/if};">{displayPrice price=$hist->amount currency=$hist->id_currency}</span></td>
	                <td class="history_method">{if $order.payment}{$order.payment|escape:'htmlall':'UTF-8'}{else}-{/if}</td>
	                <td class="history_state">{if isset($order.order_state)}{$order.order_state|escape:'htmlall':'UTF-8'}{else}-{/if}</td>
	                <td class="history_detail">
	                {if (isset($order.invoice) && $order.invoice && isset($order.invoice_number) && $order.invoice_number) && isset($invoiceAllowed) && $invoiceAllowed == true}
	                    <a href="{$link->getPageLink('pdf-invoice', true, NULL, "id_order={$order.id_order}")}" title="{l s='Invoice' mod='prepayment'}" target="_blank"><img src="{$img_dir}icon/pdf.gif" alt="{l s='Invoice' mod='prepayment'}" class="icon" /></a>
	                    <a href="{$link->getPageLink('pdf-invoice', true, NULL, "id_order={$order.id_order}")}" title="{l s='Invoice' mod='prepayment'}" target="_blank">{l s='PDF' mod='prepayment'}</a>
	                {else}-{/if}
	                </td>
	            </tr>
	        {/foreach}
	        </tbody>
	    </table>
	    <div id="block-order-detail" class="hidden">&nbsp;</div>
	    {else}
	        <p class="warning">{l s='You have no prepaid history.' mod='prepayment'}</p>
	    {/if}
	</div>
</div>
<ul class="footer_links clearfix">
    <li><a href="{$link->getPageLink('my-account', true)}"><img src="{$img_dir}icon/my-account.gif" alt="" class="icon" /> {l s='Back to Your Account' mod='prepayment'}</a></li>
    <li class="f_right"><a href="{$base_dir}"><img src="{$img_dir}icon/home.gif" alt="" class="icon" /> {l s='Home' mod='prepayment'}</a></li>
</ul>

