{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
<div class="leadin">{block name="leadin"}{/block}</div>

<form action="{$currentIndex|escape}&amp;token={$currentToken|escape}&amp;addPPHistory" id="pp_history_form" method="post">
    {if $back_customer}<input type="hidden" name="back_customer" value="{$back_customer|intval}" />{/if}
    <input id="id_pp_history" type="hidden" value="{$currentObject->id}" name="id_pp_history">
    <fieldset id="fieldset_0">
        <legend>
            <img alt="source" src="{$imgPath}history.png">{l s='Prepayment history' mod='prepayment'}
        </legend>
        <label>{l s='Customer' mod='prepayment'}</label>
        {if !$currentObject->id}
            <input type="hidden" name="customer_default_id" id="customer_default_id" value="{$customer->id}" />
            <input type="hidden" name="customer_default_name" id="customer_default_name" value="{$customer->firstname} {$customer->lastname}" />
            <div class="margin-form">
                <select id="id_customer" name="id_customer">
                {if $customer->id}
                    <option value="{$customer->id}">{$customer->firstname} {$customer->lastname}</option>
                {/if}
                </select>
                <sup>*</sup>
            </div>
            <label>{l s='Search a customer' mod='prepayment'}</label>
            <div class="margin-form">
                <input type="text" id="customer" name="customer" />
                <p class="preference_description">{l s='Search a customer by tapping the first letters of his name.' mod='prepayment'}</p>
            </div>
        {else}
            <div class="margin-form">
                {$customer->firstname} {$customer->lastname}
                <a href="index.php?tab=AdminCustomers&id_customer={$currentObject->id_customer}&viewcustomer&token={getAdminToken tab='AdminCustomers'}"><img src="../img/admin/details.gif" /></a>
            </div>
        {/if}
        {if $currentObject->id}
            {if $currentObject->id_order}
                <label>{l s='Order' mod='prepayment'}</label>
                <div class="margin-form">
                    #{$currentObject->id_order}
                    <a href="index.php?tab=AdminOrders&id_order={$currentObject->id_order}&vieworder&token={getAdminToken tab='AdminOrders'}"><img src="../img/admin/details.gif" /></a>
                </div>
            {/if}
            <label>{l s='Date' mod='prepayment'}</label>
            <div class="margin-form">{dateFormat date=$currentObject->date full=1}</div>
        {/if}
        <div class="separation"></div>
        <label>{l s='Amount' mod='prepayment'}</label>
        <div class="margin-form">
            {$currency->prefix}
            <input type="text" id="amount" name="amount" value="{toolsConvertPrice price={$currentTab->getFieldValue($currentObject, 'amount')}|string_format:'%.2f'}" />
            {$currency->suffix}
            <sup>*</sup>
        </div>
        <div style="text-align:center">
            <input type="submit" value="{l s='Save' mod='prepayment'}" class="button" name="submitAddpp_history" id="{$table|escape}_form_submit_btn" />
            <!--<input type="submit" value="{l s='Save and stay' mod='prepayment'}" class="button" name="submitAddpp_historyAndStay" id="" />-->
        </div>
    </fieldset>
</form>
<script type="text/javascript">
    var currentToken = '{$currentToken|escape:'quotes'}';
    var adminUrl = '{$link->getAdminLink("AdminProductSubs")}';
    var noMatchFound = "{l s='No match found' mod='prepayment'}";
    var tooMuchResult = "{l s='Too much results...' mod='prepayment'}";
</script>

