<div id="page" class="clearfix container_9">

    <div id="columns" class="grid_9 alpha omega clearfix">


        <!-- Center -->
        <div id="center_column" class="center grid_7 mceContentBody">




        {capture name=path}{l s='Prepaid account' mod='prepayment'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='prepayment'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='prepayment'}</p>
{else}
    <h3>{l s='Prepaid account' mod='prepayment'}</h3>
    {if $canBuy}
    <form action="{$link->getModuleLink('prepayment', 'validation', [], true)}" method="post">
    {/if}
        <p>
            <img src="{$this_path}prepayment.jpg" alt="{l s='prepayment' mod='prepayment'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
            {l s='You have chosen to pay with your prepaid account.' mod='prepayment'}
            <br/><br />
            {l s='Here is a short summary of your order:' mod='prepayment'}
        </p>
        <p style="margin-top:20px;">
            - {l s='The current balance of your prepaid account is' mod='prepayment'}
            <span id="amount" class="price" style="color:{if $balance == 0}#3B5998{elseif $balance > 0}#5C9939{else}#DA0F00{/if};">{if $balance == 0}{displayPrice price=0}{else}{displayPrice price=$balance}{/if}</span>
        </p>
        <p>
            - {l s='The total amount of your order is' mod='prepayment'}
            <span id="amount" class="price" style="color:#DA0F00">{displayPrice price=$total}</span>
            {if $use_taxes == 1}
                {l s='(tax incl.)' mod='prepayment'}
            {/if}
        </p>
        {if $canBuy}
            <p>
                - {l s='Your finale balance will be' mod='prepayment'}
                <span id="amount" class="price" style="color:{if $balanceEnd == 0}#3B5998{elseif $balanceEnd > 0}#5C9939{else}#DA0F00{/if};">{displayPrice price=$balanceEnd}</span>
            </p>
            <p>
                {*if isset($currencies) && $currencies|@count > 1}
                    {l s='We accept several currencies for prepayment.' mod='prepayment'}
                    <br /><br />
                    {l s='Choose one of the following:' mod='prepayment'}
                    <select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
                    {foreach from=$currencies item=currency}
                        <option value="{$currency.id_currency}" {if isset($currencies) && $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
                    {/foreach}
                    </select>
                {else}
                    {l s='We accept the following currency to be sent by prepayment:' mod='prepayment'}&nbsp;<b>{$currencies.0.name}</b>
                    <input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
                {/if*}
            </p>
            <p><b>{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='prepayment'}</b></p>
            <p class="cart_navigation">
                <input type="submit" name="submit" value="{l s='I confirm my order' mod='prepayment'}" class="exclusive_small" />
                &nbsp;&nbsp;&nbsp;
                <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_small_disabled">{l s='Other payment methods' mod='prepayment'}</a>
            </p>
        {else}
            <p><b>{l s='You don\'t have enough credit on your prepaid account.' mod='prepayment'}</b></p>
            <p class="cart_navigation">
                <a href="{$link->getModuleLink('prepayment', 'account', array(), true)}" class="exclusive_small" />{l s='Recharge my account' mod='prepayment'}</a>
                <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='prepayment'}</a>
            </p>
        {/if}
    {if $canBuy}
    </form>
    {/if}
{/if}
