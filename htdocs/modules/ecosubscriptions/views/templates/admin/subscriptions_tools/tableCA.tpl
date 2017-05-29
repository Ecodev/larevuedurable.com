<table id="statTable" cellspacing="0">
    <thead>
        <tr>
            <td></td>
            {for $editionNumber=$min to $max}
                <td>{$editionNumber}</td>
            {/for}
            <td>moy.</td>
        </tr>
    </thead>

    <tbody>
        {foreach from=$array item=productId}
            {if isset($data[$productId])}
                <tr>
                    <td>{$productId}</td>
                    {for $i=$min to $max}
                        {if isset($data[$productId][$i])}
                            <td>{$data[$productId][$i]|@array_sum}</td>
                        {else}
                            <td></td>
                        {/if}
                    {/for}
                    <td>{($moy[$productId]|@array_sum / ($max - $min + 1))|round}</td>
                </tr>
            {/if}
        {/foreach}
    </tbody>
</table>
