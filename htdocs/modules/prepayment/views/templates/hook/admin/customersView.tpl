<div class="separation"></div>
<h2 class="media_title">{l s='Prepayment account' mod='prepayment'}</h2>
<span class="media_add"><a class="button" href="?controller=adminpphistory&amp;id_customer={$id_customer}&amp;addpp_history&amp;token={$ppHistoryToken}&amp;back_customer=1" title="{l s='Recharge account' mod='prepayment'}"><img src="../img/admin/add.gif">{l s='Recharge account' mod='prepayment'}</a></span>
<div class="clear"></div>
<h3 style="color:green;font-weight:700">{l s='Account balance:' mod='prepayment'}&nbsp;<span class="bold" style="color:{if $balance == 0}#3B5998{elseif $balance > 0}#5C9939{else}#DA0F00{/if};">{if $balance == 0}{displayPrice price=0}{else}{displayPrice price=$balance}{/if}</span></h3>
<table class="table media_list" cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <th class="right" height="39px" width="15px">{l s='ID' mod='prepayment'}</th>
            <th width="150px">{l s='Date' mod='prepayment'}</th>
            <th>{l s='Amount' mod='prepayment'}</th>
            <th class="center" width="70px">{l s='Actions' mod='prepayment'}</th>
        </tr>
        {foreach from=$history item=hist key=k}
            <tr class="row_hover" onclick="document.location = '?controller=adminpphistory&amp;id_pp_history={$hist->id}&amp;updatepp_history&amp;token={$ppHistoryToken}&amp;back_customer=1'" style="cursor: pointer">
                <td class="center">{$hist->id}</td>
                <td>{dateFormat date=$hist->date full=1}</td>
                <td><span class="price" style="background-color: {if $hist->amount > 0}#5C9939{else}#DA0F00{/if};">{displayPrice price=$hist->amount currency=$hist->id_currency no_utf8=false convert=false}</span></td>
                <td align="center">
                    <a title="{l s='Edit' mod='prepayment'}" href="?controller=adminpphistory&amp;id_pp_history={$hist->id}&amp;updatepp_history&amp;token={$ppHistoryToken}&amp;back_customer=1"><img src="../img/admin/edit.gif"></a>
                    <a title="{l s='Delete' mod='prepayment'}"
                    onclick="if (confirm('{l s='Delete this item?' mod='prepayment'}\n\n{l s='History' mod='prepayment'} #{$hist->id}')){ return true; }else{ event.stopPropagation(); event.preventDefault();};"
                    href="?controller=adminpphistory&amp;id_pp_history={$hist->id}&amp;deletepp_history&amp;token={$ppHistoryToken}&amp;back_customer=1"><img src="../img/admin/delete.gif"></a>
                </td>
            </tr>
        {/foreach}
        {if !count($history)}
            <tr>
                <td align="center" colspan="4">{l s='No history found for this customer.' mod='prepayment'}</td>
            </tr>
        {/if}
    </tbody>
</table>
<div class="clear"></div>
<div class="separation"></div>