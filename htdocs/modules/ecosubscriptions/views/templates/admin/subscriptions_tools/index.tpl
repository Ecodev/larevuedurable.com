<style>
    table {
        width: 50%;
    }

    thead {
        font-weight: bold;
    }

    td {
        padding: 10px 0;
    }
</style>


<fieldset>
    <legend>Nombre d'abonnements</legend>

    <p>Ces statistiques comptent les abonnements selon leur état actuel.</p>
    <p>Ces données prennent en compte les acheteurs d'abonnements.</p>
    <p>Les personnes au bénéfice d'un abonnement obtenu au travers des prestations de l'abonnement professionnel ne sont pas prises en compte.</p>
    <br/>

    {if isset($computed) && $computed}
        <table>
            <thead>
                <tr>
                    <td>Actifs en ce moment : {$active|count}</td>
                    <td>En tout : {$all|count}</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- En ce moment -->
                    <td>
                        <p>Web : {$archiveOnlyActually|count}</p>
                        <p>Papier : {$paperOnlyActually|count}</p>
                        <p>Web + papier : {$paperAndArchiveActually|count}</p>
                        <br/>
                        <p>Standard : {$standardActually|count}</p>
                        <p>Professionnel : {$proActually|count}</p>
                        <p>Solidarité : {$soliActually|count}</p>
                    </td>

                    <!-- En tout -->
                    <td>
                        <p>Web : {$archiveOnly|count}</p>
                        <p>Papier : {$paperOnly|count}</p>
                        <p>Web + papier : {$paperAndArchive|count}</p>
                        <br/>
                        <p>Standard : {$standard|count}</p>
                        <p>Professionnel : {$pro|count}</p>
                        <p>Solidarité : {$soli|count}</p>
                        <br/>
                        <p>Futurs : {$future|count}</p>
                        <p>Passés : {$past|count}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    {/if}

    <form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">
        <input type="hidden" name="action" value="computeSubscribers"/>
        <input type="submit" class="button" value="Calculer"/>
    </form>
</fieldset>

<br/>
<fieldset>
    <legend>Export TVA</legend>

    <form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">
        <p>
            Exporter du <input type="text" name="exportTVAFrom" value="{$exportTVAFrom}" />
            au <input type="text" name="exportTVATo" value="{$exportTVATo}" />
            <i>(Format des dates : aaaa-mm-jj)</i>
        </p>
        <input type="hidden" name="action" value="exportTVA"/>
        <input type="submit" class="button" value="Exporter"/>
    </form>

</fieldset>



<br/>
<fieldset>
    <legend>Regénérer numéros de page</legend>

    <p>Permet de mettre à jour les numéro de page et les éditions à partir de la référence des revues. </p>
    <p>Ces données sont utilisées à la fois pour trier les listes par numéro, puis par page, mais aussi pour afficher les numéro de page directement</p>
    <br/>
    <form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">
        <input type="hidden" name="action" value="regeneratePagination"/> <input type="submit" class="button" value="Regénérer"/>
    </form>

</fieldset>


<br/>
<fieldset>
    <legend>Statistiques</legend>

    <form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">

        <style>
            #statTable {
                border-top: 1px solid #ccc;
                border-left: 1px solid #ccc;
                width: 100%;
                margin-bottom: 1em
            }

            #statTable td {
                width: 50px;
                border-bottom: 1px solid #ccc;
                border-right: 1px solid #ccc;
                margin: 0;
                padding: 5px;
                text-align: right;
            }
        </style>

        {if isset($statsComputed)}
            <h2>Chiffre d'affaire par édition</h2>
            {include file="./tableCA.tpl" data=$dataByProduct array=[8,31,32,'total'] moy=$moyByProduct}
            {include file="./tableCA.tpl" data=$dataByType array=['w', 'wp', 'p','total'] moy=$moyByType}

            <h2>Nombre d'abonnés par édition</h2>
            {include file="./tableCustomers.tpl" data=$dataByProduct array=[8,31,32,'total']}
            {include file="./tableCustomers.tpl" data=$dataByType array=['w', 'wp', 'p','total']}

            <h2>Chiffre d'affaire par édition et par personne</h2>
            <p>Peut être considéré comme la somme que paie chaque utilisateur en moyenne pour un abonnement</p>
            {include file="./tableCAPerCustomer.tpl" data=$dataByProduct array=[8,31,32,'total']}
            {include file="./tableCAPerCustomer.tpl" data=$dataByType array=['w', 'wp', 'p','total']}

        {/if}

        <input type="hidden" name="action" value="statistics"/>
        <input type="submit" class="button" value="Générer"/>
    </form>

</fieldset>
