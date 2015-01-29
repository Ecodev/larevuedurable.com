{assign "numWidth" "50"}
{assign "chronologyAlert" 0}

<div class="legend">

   <div class="element header">
      Légende :
   </div>

   <div class="element">
      <div class="reference tierce"></div> Hérité d'un tiers
   </div>

   <div class="element">
      <div class="reference imported"></div> Importé
   </div>

   <div class="element">
      <div class="reference archive"></div> Echu
   </div>

   <div class="element">
      <div class="reference active"></div> Actif
   </div>

   <div class="element">
      <div class="reference future"></div> Futur
   </div>

   <div class="element">
      <div class="reference nonexistent"></div> N'existe pas
   </div>

   <div class="element">
      <div class="reference unpublished"></div> Non publié
   </div>

   <div class="element">
      <div class="reference future invisible"></div><div class="reference unpublished invisible"></div><div class="reference nonexistent invisible"></div> Invisible
   </div>

   <div class="element">
      <div class="reference future alertchronology">X</div> <div class="reference unpublished alertchronology">X</div> Problème de chronologie
   </div>

</div>

<div class="adminsubs" style="width:{($max-$min+1) * $numWidth + 410}px">



      {capture name="years"}
         {foreach $magazines as $mag}

            {if isset($lastMagazine) && ($mag.reference != ($lastMagazine.reference + 1) )}
               {assign "chronologyAlert" 1}
            {/if}

            {assign "lastMagazine" $mag}

            {if !isset($biggerMagazine) || $mag.reference > $biggerMagazine.reference}
               {assign "biggerMagazine" $mag}
            {/if}

            <a href="index.php?controller=AdminProducts&amp;id_product={$mag.id_product}&amp;updateproduct&amp;token={Tools::getAdminTokenLite('AdminProducts')}" class="num{if $mag.reference < $actual} archive{/if}{if $mag.reference == $actual} active{/if}{if $mag.reference > $actual} future{/if}{if $mag.active == 0} unpublished{/if}{if $mag.visibility == 'none'} invisible{/if}{if $chronologyAlert==1} chronologyAlert{/if}" title="Modifier">
               {$mag.reference}
               <div class="infobox">
                  Parution : {$mag.date_parution}
                  {if $chronologyAlert==1}, problème de chronologie{/if}
                  {if $mag.active == 0}, inactif{/if}
                  {if $mag.visibility == 'none'}, invisible{/if}
               </div>
            </a>
         {/foreach}

         {for $num=($biggerMagazine.reference + 1) to $max}
            <div class="num nonexistent">
               {$num}
               <div class="infobox">
                  Ce numéro n'existe pas
               </div>
            </div>
         {/for}
      {/capture}

   <div class="row years">
      {$smarty.capture.years}
   </div>


   {foreach $customers as $customer}
      <div class="row">
         <div class="leftheader">
            <a href="index.php?controller=AdminCustomers&amp;id_customer={$customer->id}&amp;viewcustomer&amp;token={Tools::getAdminTokenLite('AdminCustomers')}" class="edit" title="Modifier">
               <img src="../img/admin/edit.gif" alt="Modifier">
            </a>
            {$customer->id}. {$customer->email}
         </div>

         <div class="subscriptionsContainer">
            {if !empty($customer->user_subscriptions)}
               <div class="subscriptions">
                  {foreach $customer->user_subscriptions as $sub}
                     <div class="sub sub{$sub->number_of_editions/4}{if $sub->is_active} active{/if}{if $sub->is_future} future{/if}{if $sub->last_edition < $actual} archive{/if}{if $sub->order_history->id_order_state == $imported_order_state} imported{/if}"
                          style="left:{($sub->first_edition - $min) * $numWidth}px">

                        <a href="index.php?controller=AdminOrders&id_order={$sub->order->id}&vieworder&token={Tools::getAdminTokenLite('AdminOrders')}" class="edit" title="Modifier">
                           <img src="../img/admin/edit.gif" alt="Modifier">
                        </a>

                        {$sub->order->reference}, {$sub->order->date_add|date_format:"d.m.Y"}, {$sub->number_of_editions/4} an(s)

                     </div>
                  {/foreach}
               </div>
            {/if}
            {if !empty($customer->tierce_subscriptions)}
               <div class="subscriptions tierce">
                  {foreach $customer->tierce_subscriptions as $sub}
                     <div class="sub sub{$sub->number_of_editions/4}{if $sub->is_active} active{/if}{if $sub->is_future} future{/if}{if $sub->last_edition < $actual} archive{/if}{if $sub->order_history->id_order_state == $imported_order_state} imported{/if}"
                          style="left:{($sub->first_edition - $min) * $numWidth}px">

                        <a href="index.php?controller=AdminOrders&id_order={$sub->order->id}&vieworder&token={Tools::getAdminTokenLite('AdminOrders')}" class="edit" title="Modifier">
                           <img src="../img/admin/edit.gif" alt="Modifier">
                        </a>

                        {$sub->order->reference}

                        (<a href="index.php?controller=AdminCustomers&amp;id_customer={$sub->customer->id}&amp;viewcustomer&amp;token={Tools::getAdminTokenLite('AdminCustomers')}" class="edit" title="Modifier">
                           {$sub->customer->email} <img src="../img/admin/edit.gif" alt="Modifier">
                        </a>)

                     </div>
                  {/foreach}
               </div>
            {/if}
         </div>
         <div class="clearfix"></div>
      </div>

      <div class="row years mini">{$smarty.capture.years}</div>

   {/foreach}

</div>