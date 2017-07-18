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
                    {assign var="persons" value=0}
                    {for $i=$min to $max}
                        {if isset($data[$productId][$i])}
                            {assign var="persons" value=$persons+$data[$productId][$i]|@count}
                            <td>{$data[$productId][$i]|@count}</td>
                        {else}
                            <td></td>
                        {/if}
                    {/for}
                    <td>{($persons / ($max - $min + 1))|round}</td>
                </tr>
            {/if}
        {/foreach}
    </tbody>
</table>
