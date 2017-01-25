<div class="clear"></div>
<div class="separation"></div>

<script type="text/javascript">
    function saveCustomerNote() {
        $('#note_feedback').html('<img src="../img/loader.gif" alt="" />').show();
        var noteContent = $('#noteContent').val();

        $.ajax({
            type: "POST",
            url: "index.php",
            data: "token={getAdminToken tab='AdminCustomers'}&tab=AdminCustomers&ajax=1&action=updateCustomerNote&id_customer={$customer->id}&note=" + noteContent,
            async: true,
            success: function(r) {
                $('#note_feedback').html('').hide();
                if (r == 'ok') {
                    $('#note_feedback').html("<b style='color:green'>{l s='Your note has been saved.'}</b>").fadeIn(400);
                    $('#submitCustomerNote').attr('disabled', true);
                } else if (r == 'error:validation')
                    $('#note_feedback').html("<b style='color:red'>({l s='Error: Your note is not valid.'}</b>").fadeIn(400); else if (r == 'error:update')
                    $('#note_feedback').html("<b style='color:red'>{l s='Error: Your note cannot be saved.'}</b>").fadeIn(400);
                $('#note_feedback').fadeOut(3000);
            }
        });
    }

    function saveExcludeFromRemind() {
        var el = $('#excludeFromRemind');
        var val = el.is(':checked');
        el.attr('disabled', true);
        $('#excludeFromRemindStage').hide();

        $.ajax({
            type: "POST",
            url: "index.php",
            data: "token={getAdminToken tab='AdminCustomers'}&tab=AdminCustomers&ajax=1&action=updateCustomerExclusionFromRemind&id_customer={$customer->id}&excludeFromRemind=" + (val ? 1 : 0),
            async: true,
            success: function(r) {
                el.removeAttr('disabled');
                if (r != 'ok') {
                    $('#excludeFromRemindStage').show();
                }
            }
        });
    }

    function ignoreSubscription() {
        var orderId = $(this).val();

        $.ajax({
            type: "POST",
            url: "index.php",
            data: "token={getAdminToken tab='AdminCustomers'}&tab=AdminCustomers&ajax=1&action=ignoreSubscription&order=" + orderId + "&ignore=" + ($(this).is(':checked') ? 1 : 0) ,
            async: true,
            success : function() {
                location.reload();
            }
        });
    }

    $(document).ready(function(){
        $('.subscriptions input').on('change', ignoreSubscription);
    });

</script>


<style>
    .table.subscriptions td,
    .table.subscriptions th {
        padding:10px 5px;
    }

    .subscrptionsZone .buttons {
        padding:20px 0 10px
    }

    .subscrptionsZone .button {
        margin-right:10px;
        font-weight:normal;
    }

    .table.subscriptions .center {
        text-align:center
    }
</style>


<div class='subscrptionsZone' style="padding:20px;">

    <div style="display:flex">

        <!-- Logo -->
        <div style="flex-basis: 50px;">
            <img src="/modules/{$moduleName}/logo.gif">
        </div>

        <!-- Content -->
        <div style="flex:1">

            <!-- Title -->
            <h2>{l s='Abonnements'}</h2>

            <!-- Buttons -->
            <div class="buttons">
                <a class="button" href="index.php?controller=AdminSubscriptionsSubscribers&customer={$customer->id}&token={Tools::getAdminTokenLite('AdminSubscriptionsSubscribers')}">Voir graphiquement</a>
                <label class="button" style="float:none;">
                    <input type="checkbox" id="excludeFromRemind" name="excludeFromRemind" onchange="saveExcludeFromRemind()" {if $customer->excludeFromRemind}checked{/if}/>
                    Exclure de la relance automatique
                </label>
                <span>Crésus : {$customer->cresus_id}, {$customer->cresus_source}</span>
                <span id="excludeFromRemindStage" style="color:red;display:none">Une erreur s'est produite lors de la sauvegarde, contactez l'administrateur technique.</span>

                {if $customer->getNextRemindDate()}
                    <span class="button">Relance le {$customer->getNextRemindDate()|date_format:'%e %B %Y'}</span>
                {/if}
            </div>


            <table class="table media_list subscriptions" cellspacing="0" cellpadding="0">
                <tr>
                    <th class="center">Ignoré</th>
                    <th>{l s='Du'}</th>
                    <th>{l s='Au'}</th>
                    <th>{l s='Réf de la commande'}</th>
                    <th>{l s='Date de commande'}</th>
                    <th>{l s='Description'}</th>
                    <th>{l s='Autorisations'}</th>
                </tr>

                {foreach from=$customer->user_subscriptions|@array_reverse item=sub}
                    {if !$sub->is_ignored}
                        {include file='./subscription-line.tpl' sub=$sub}
                    {/if}
                {/foreach}


                {if $customer->tierce_subscription}
                    <tr>
                        <td colspan="7">
                            <strong>Abonnement hérité (actuellement actif) :</strong>
                        </td>
                    </tr>
                    {include file='./subscription-line.tpl' sub=$customer->tierce_subscription showCustomer=true}
                {/if}

                {if $customer->user_ignored_subscriptions|count}
                <tr>
                    <td colspan="7">
                        <strong>Abonnements ignorés :</strong>
                    </td>
                </tr>
                {/if}

                {foreach from=$customer->user_ignored_subscriptions item=sub}
                        {include file='./subscription-line.tpl' sub=$sub}
                {/foreach}
            </table>

        </div>
    </div>
</div>
