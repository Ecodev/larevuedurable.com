<div id="prepaid_block" class="block">
    <h4>{l s='Prepaid account' mod='prepayment'}</h4>
    <div class="block_content clearfix">
            <p>{l s='Your prepaid account is the easiest way to shop!' mod='prepayment'}</p>
            <div class="center">
                <p class="bold">{l s='Current balance:' mod='prepayment'}<br />
                    <span class="price" style="color:{if $balance == 0}#3B5998{elseif $balance > 0}#5C9939{else}#DA0F00{/if};">{if $balance == 0}{displayPrice price=0}{else}{displayPrice price=$balance}{/if}</span>
                </p>
                <p><a href="{$link->getModuleLink('prepayment', 'account', array(), true)}" class="exclusive_large" />{l s='Recharge my account' mod='prepayment'}</a></p>
            </div>
    </div>
</div>
