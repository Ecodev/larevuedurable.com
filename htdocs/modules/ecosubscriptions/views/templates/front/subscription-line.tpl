<table id="order-list" class="std {if $sub->is_active}active{else}inactive{/if} {if $sub->is_future}future{else}nofuture{/if}" >
    <thead>
        <tr>
            <th colspan='2' class="first_item">
                {if $sub->is_active}
                    Abonnement actif jusqu'au numéro {$sub->last_edition}
                {elseif $sub->is_future}
                    Abonnement en attente jusqu'au numéro {$sub->first_edition}
                {elseif $sub->is_archive}
                    Abonnement expiré depuis le numéro {$sub->last_edition}
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
                    <strong>{l s='Premier numéro'} : </strong>{$sub->first_edition}<br/>
                    <strong>{l s='Dernier numéro'} : </strong>{$sub->last_edition}<br/>
                </p>
            </td>
        </tr>
        {if $sub->customer->conditions}
            <tr class='last_item'>
                <td colspan='2'>
                    <p>{l s='Cet abonnement est géré par'} : {{$sub->customer->firstname}} {{$sub->customer->lastname}} ({{$sub->customer->email}}) </p>
                </td>
            </tr>
        {/if}
    </tbody>
</table>
