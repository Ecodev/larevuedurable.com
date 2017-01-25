<script>
    $(document).ready(function(){
        $('#relancer').on('click', function(e) {
            $('#relancerConfirm').show();
            e.preventDefault();
        });

        $('#select').on('change', function(){
            $('#selectVal').val($(this).val());
        });

    });

</script>


<form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">
    <fieldset>
        <legend>Général</legend>

        <p>
            Le prochain numéro est le <strong>{$next}</strong> et il paraîtra le <strong>{$nextRelease|date_format:'%d.%m.%Y'}</strong>.<br/>
            Sa date de relance automatique est le <strong>{$nextRemindDate|date_format:'%d.%m.%Y'}</strong>.
        </p>
        <br/>

        <p>Les relances automatiques sont
            <strong>{if $isActive}
                    <span style="color:green">activées</span>
                {else}
                    <span style="color:red">désactivées</span>
                {/if}
            </strong>
            .
        </p>
        <p>
            <input type="hidden" name="action" value="toggleActivation"/>
            <input type="submit" class="button"  value="{if $isActive}Désactiver{else}Activer{/if}"/>
        </p>

    </fieldset>
</form>
<br/>

<fieldset>
    <legend>Relance manuelle</legend>

    <form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">
        <p>
            Numéro à relancer :
            <select name="num" id="select">
                {foreach from=$numbers item=num}
                    <option value="{$num}" {if isset($selected) && $selected==$num}selected="{$selected}"{/if}>{$num}</option>
                {/foreach}
            </select>
        </p>
        <p>
            <input type="hidden" name="action" value="test"/>
            <input type="submit" class="button" value="Prévisualiser"/>
        </p>
    </form>

    <form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">
        <p><input type="button" class="button" value="Relancer" id="relancer"/></p>
        <p style="display:none" id="relancerConfirm">
            <input type="hidden" name="action" value="send"/>
            <input type="hidden" name="num" value="{if $selected}{$selected}{/if}" id="selectVal"/>
            <input type="submit" class="button" value="Confirmer relance (ne peut pas être annulé)"/>
        </p>
    </form>

    {if isset($test) && $test}
        <strong>Concernés par la relance : {$reminds|count} personnes</strong>
        <br/>
        <br/>

        {if isset($reminds) && $reminds|count}
            <table>
                <tbody>
                    {foreach from=$reminds item=customer}
                        <tr>
                            {foreach from=$customer key=k item=v}
                                <td>{$v}</td>
                            {/foreach}
                        </tr>
                    {/foreach}
                </tbody>

            </table>
        {else}
            Personne n'est concerné par la relance
        {/if}
    {/if}

</fieldset>



<br/>

<form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">
    <fieldset>
        <legend>Outils développeur</legend>

        <p>
            <input type="hidden" name="action" value="showMailchimp"/>
            <input type="submit" class="button" value="Campagnes et listes mailchimp"/>
        </p>

        {if isset($mc)}
            <pre>{$mc|print_r}</pre>
        {/if}

    </fieldset>
</form>
<br/>
