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

        Exporté les abonnés au numéro :
        <input type="number" name="filterNumber" />
        (Si aucun numéro n'est fourni, tous les abonnés seront retournés)

        <input type="hidden" name="action" value="exportForMailChimp" />
        <p>
            <input type="submit" value="Exporter" class="button" />
        </p>
    </fieldset>
</form>
