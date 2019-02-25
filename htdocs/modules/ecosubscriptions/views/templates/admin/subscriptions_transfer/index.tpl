<form action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Importer dans prestashop</legend>
        <table>
            <tr>
                <td width="50%">
                    <p>
                        <input type="file" name="fichierCresus" />
                    </p>
                </td>
                <td width="50%">
                    <input type="hidden" name="action" value="import" />
                    <p>
                        <input type="submit" value="Lancer l'importation" class="button" />
                    </p>
                </td>
            </tr>
        </table>
    </fieldset>
</form>

<br />

<form action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Exporter vers crésus</legend>

        <p>L'export le plus récent ayant été fait jusqu'au :<strong> {$lastExportDate} </strong>. Nous sommes le <strong>{$dateNow}</strong>.
        </p>
        <p>
            Exporter du
            <input type="text" name="exportFrom" value="{$exportFrom}" />
            à 00h00:00 au
            <input type="text" name="exportTo" value="{$exportTo}" />
            à 23h59:59. &nbsp;&nbsp;&nbsp;<i>(Format des dates : aaaa-mm-jj)</i>
        </p>
        <input type="hidden" name="action" value="export" />
        <p>
            <input type="submit" value="Lancer l'exportation" class="button" />
        </p>
    </fieldset>
</form>

<br />

<form action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Exporter abonnés pour mailchimp</legend>

        Exporter les abonnés au numéro :
        <input type="number" name="filterNumber" />
        (Si aucun numéro n'est fourni, tous les abonnés seront retournés)

        <input type="hidden" name="action" value="exportForMailChimp" />
        <p>
            <input type="submit" value="Exporter" class="button" />
        </p>
    </fieldset>
</form>

<br/>
<form action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Exporter les anciens abonnés pour mailchimp</legend>

        Exporter les anciens abonnés dont le dernier numéro de leur abonnement peut être :
        <input type="text" name="lastSubsNumbers" placeholder="53,54,55,56,57,58" />

        <input type="hidden" name="action" value="exportUnsubscribedForMailChimp" />
        <p>
            <input type="submit" value="Exporter" class="button" />
        </p>
    </fieldset>
</form>

<br/>
<form action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>Exporter les abonnés actuels et leur historique</legend>

        <input type="number" min="0" name="requiredNumber" placeholder="Numéro abonnné requis"/>
        <i>Si ce numéro d'édition est fourni, seuls les utilisateurs y ayant été (ou y étant encore) abonnés seront considérés.</i>

        <input type="hidden" name="action" value="exportSubscribersWithHistory" />
        <p>
            <input type="submit" value="Exporter" class="button" />
        </p>
    </fieldset>
</form>
