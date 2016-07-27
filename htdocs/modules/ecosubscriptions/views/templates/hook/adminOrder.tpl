<div class="clear"></div>
<div class="separation"></div>

<script type="text/javascript">

    function changeDate() {
        console.log('change date');
        var order = $('#newOrderDate').data('order');
        var newDate = $('#newOrderDate').val();

        $.ajax({
            type: "POST",
            url: "index.php",
            data: "token={getAdminToken tab='AdminOrders'}&tab=AdminOrders&ajax=1&action=changeDate&order=" + order + "&newDate=" + newDate,
            async: true,
            success : function(r) {
                location.reload();
            }
        });
    }

    function changeCustomer() {
        console.log('change customer');
        var order = $('#newCustomer').data('order');
        var newCustomer = $('#newCustomer').val();
        $.ajax({
            type: "POST",
            url: "index.php",
            data: "token={getAdminToken tab='AdminOrders'}&tab=AdminOrders&ajax=1&action=changeCustomer&order=" + order + "&newCustomer=" + newCustomer,
            async: true,
            success: function(r) {
                location.reload();
            }
        });
    }

    $(document).ready(function(){
        $('#submitDate').on('click', changeDate);
        $('#submitCustomer').on('click', changeCustomer);
    });

</script>

<fieldset>
    <legend ><img src="../img/admin/date.png" /> {l s='Changer la date de la commande'}</legend>
    <div>
        <label for='newOrderDate'>Saisissez la nouvelle date : </label>
        <input type='text' id='newOrderDate' data-order="{$order->id}" value='{$order->date_add}'/>
        <input type="button" class="button" id="submitDate" value="Changer" />
    </div>
</fieldset>

<br/>
<fieldset>
    <legend ><img src="../img/admin/tab-customers.gif" />Changez le propriétaire</legend>
    <div>
        <label for='newOrderDate'>Changez le propriétaire </label>
        <select data-order="{$order->id}" id="newCustomer">
            {foreach from=$customers item=customer}
                <option value="{$customer.id_customer}" {if $order->id_customer == $customer.id_customer}selected{/if}>{$customer.lastname|upper} {$customer.firstname} ({$customer.email})</option>
            {/foreach}
        </select>
        <input type="button" class="button" id="submitCustomer" value="Changer" />
    </div>
</fieldset>


