<form method="post" action="{$currentIndex|escape}&amp;token={$currentTab->token|escape}&amp;">

    <fieldset>
        <legend>Nombre d'abonnements</legend>

        <p>
            Ces statistiques comptent les abonnements selon leur état actuel.
        </p>
        <p>
            Ces données prennent en compte les acheteurs d'abonnements.
        </p>
        <p>
            Les personnes au bénéfice d'un abonnement obtenu au travers des prestations de l'abonnement professionnel ne sont pas prises en compte.
        </p>
        <br/>

        {if isset($all)}
            <p><strong>Tous :</strong> {$all|count}</p>
            <br/>

        {/if}


        {if isset($archiveOnly)}
            <p><strong>Web :</strong> {$archiveOnly|count}</p>
        {/if}
        {if isset($paperOnly)}
            <p><strong>Papier :</strong> {$paperOnly|count}</p>
        {/if}
        {if isset($paperAndArchive)}
            <p><strong>Web + papier :</strong> {$paperAndArchive|count}</p>
            <br/>
        {/if}


        {if isset($standard)}
            <p><strong>Standard :</strong> {$standard|count}</p>
        {/if}
        {if isset($pro)}
            <p><strong>Professionnel :</strong> {$pro|count}</p>
        {/if}
        {if isset($soli)}
            <p><strong>Solidarité :</strong> {$soli|count}</p>
            <br/>
        {/if}


        {if isset($active)}
            <p><strong>Actifs :</strong> {$active|count}</p>
        {/if}
        {if isset($future)}
            <p><strong>Futurs :</strong> {$future|count}</p>
        {/if}
        {if isset($past)}
            <p><strong>Passés :</strong> {$past|count}</p>
            <br/>
        {/if}


        <input type="hidden" name="action" value="numberOfWebSubscribers"/>
        <input type="submit" value="Calculer"/>
    </fieldset>

</form>
