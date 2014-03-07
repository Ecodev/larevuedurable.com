{if !$product->id}
    <div class="warn" style="display:block">
        {l s='You need to save this product first before to be able to add prepayment.' mod='prepayment'}
    </div>
{else}
    <h4 class="tab">{l s='Prepayment' mod='prepayment'}</h4>
    <h4>{l s='Prepayment settings' mod='prepayment'}</h4>
    <div class="separation"></div>
    <fieldset style="border:0px">
        <label>{l s='Prepayment amount' mod='prepayment'}</label>
        <div class="margin-form">
            &nbsp;&nbsp;
            <input type="radio" name="ppproduct" id="ppproduct_on" value="1"{if $prepaymentProduct} checked="checked"{/if} onclick="setPrepayment();" />
            <label class="t" for="ppproduct_on"><img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='prepayment'}" title="{l s='Enabled' mod='prepayment'}" style="cursor:pointer" /></label>
            &nbsp;&nbsp;
            <input type="radio" name="ppproduct" id="ppproduct_off" value="0"{if !$prepaymentProduct} checked="checked"{/if} onclick="setPrepayment();" />
            <label class="t" for="ppproduct_off"><img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='prepayment'}" title="{l s='Disabled' mod='prepayment'}" style="cursor:pointer" /></label>
            <p class="preference_description">{l s='Use this product as prepayment amount to reload prepayment account.' mod='prepayment'}</p>
        </div>
        <div id="pp_reduction"{if !$prepaymentProduct} style="display:none;"{/if}>
            <label>{l s='Extra amount' mod='prepayment'}</label>
            <div class="margin-form">
                <input type="text" id="pp_reduction_amount" name="pp_reduction_amount" value="{if $prepaymentProduct}{$prepaymentProduct->reduction}{else}0{/if}" onkeyup="setReduction()"/>&nbsp;
                <select id="pp_reduction_type" name="pp_reduction_type" onchange="setReductionType()">
                    <option value="amount">{l s='Amount' mod='prepayment'} ({$currency->sign})</option>
                    <option{if $prepaymentProduct && $prepaymentProduct->reduction_type == 'percentage'} selected="selected"{/if} value="percentage">{l s='Percentage' mod='prepayment'} (%)</option>
                </select>
                <p class="preference_description">{l s='Define an extra amount that will be offered for each recharge.' mod='prepayment'}</p>
            </div>
            </div>
    </fieldset>

    <script type="text/javascript" src="{$js_file}"></script>
    <link type="text/css" href="{$css_file}" rel="stylesheet" media="all" />
    <script type="text/javascript">
        var ppproductToken = '{$ppproductToken|escape:'quotes'}';
        var ppproductUrl = '{$link->getAdminLink("AdminPPProduct")}';
        var productId = {$product->id};
    </script>
{/if}
