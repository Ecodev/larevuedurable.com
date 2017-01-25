<tr>
    <td class="center">
        {if $sub->is_paper}
            <input type="checkbox" value="{$sub->order->id}" {if $sub->is_ignored}checked{/if}>
        {/if}
    </td>
    <td>{$sub->first_edition}</td>
    <td>{$sub->last_edition}</td>
    <td>
        <a href="{$link->getAdminLink('AdminOrders')|escape:'htmlall':'UTF-8'}&id_order={$sub->order->id}&vieworder" >
            {$sub->order->reference}
        </a>

        {if isset($showCustomer) && $showCustomer}
            <a href="{$link->getAdminLink('AdminCustomers')|escape:'htmlall':'UTF-8'}&id_customer={$sub->customer->id}&viewcustomer" >
                ({$sub->customer->email})
            </a>
        {/if}

    </td>
    <td>{$sub->order->date_add|date_format:"%A %e %B %Y"}</td>
    <td>{$sub->product_attributes_name}</td>
    <td>
        {foreach from=$sub->customer->conditions item=condition}
            {$condition}
        {/foreach}
    </td>
</tr>
