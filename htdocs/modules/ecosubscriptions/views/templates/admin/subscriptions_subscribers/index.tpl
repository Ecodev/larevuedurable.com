{assign "numWidth" "50"}
{assign "chronologyAlert" 0}
{assign "subsWidth" $numWidth * ($max-$min+1)}

<div class="adminsubs">
   <div class="legend">

      <div class="element header">
         Lignes
      </div>
      <div class="element">
         <div class="reference tierces "></div> Reçus
      </div>
      <div class="element">
         <div class="reference ignored "></div> Ignorés
      </div>


      <div class="element header">
         Abonnements
      </div>
      <div class="element">
         <div class="reference subType"></div> Spécial
      </div>
      <div class="element">
         <div class="reference subType8"></div> Standard
      </div>
      <div class="element">
         <div class="reference subType31"></div> Solidarité
      </div>
      <div class="element">
         <div class="reference subType32"></div> Institutionnel
      </div>

      <div class="element sep"></div>
      <div class="element">
         <div class="reference paper underscored"></div> Papier
      </div>
      <div class="element">
         <div class="reference web underscored"></div> Web
      </div>
      <div class="element">
         <div class="reference web paper underscored"></div> Web + Papier
      </div>
      <div class="element">
         <div class="reference imported"></div> Importé
      </div>


   </div>

   <div class="legend">

      <div class="element header">
         Relances
      </div>
      <div class="element">
         <strong>{$nextRemindDate|date_format:'d.m.Y'}</strong>
      </div>
      <div class="element">
         <div class="reference remind"></div> Concerné par la relance
      </div>
      <div class="element">
         <div class="reference remind excluded"></div> Exclu de la relance
      </div>
      <div class="elementNotStyled">
         <a href class="button" id="seeRemind">Voir les concernés par la relance</a>
      </div>
      <div class="elementNotStyled">
         <a href class="button" id="seeAll">Voir tous</a>
      </div>

   </div>

   <br/>

   <div style="width:{$subsWidth + 410}px">

      {capture name = "years"}
            <div class="leftheader">&nbsp;</div>

            {foreach $magazines as $mag}
               {if $mag.reference > $max}{break}{/if}

               {if isset($lastMagazine) && ($mag.reference != ($lastMagazine.reference + 1) )}
                  {assign "chronologyAlert" 1}
               {/if}

               {assign "lastMagazine" $mag}

               {if !isset($biggerMagazine) || $mag.reference > $biggerMagazine.reference}
                  {assign "biggerMagazine" $mag}
               {/if}

               <a href="index.php?controller=AdminProducts&amp;id_product={$mag.id_product}&amp;updateproduct&amp;token={Tools::getAdminTokenLite('AdminProducts')}"
                  class="num{if $mag.reference < $actual} archive{/if}{if $mag.reference == $actual} active{/if}{if $mag.reference > $actual} next{/if}{if $mag.active == 0} unpublished{/if}{if $mag.visibility == 'none'} invisible{/if}{if $chronologyAlert==1} chronologyAlert{/if}">
                  {$mag.reference}
                  <div class="infobox">
                     Parution : {$mag.date_parution}
                     {if $chronologyAlert == 1}, problème de chronologie{/if}
                     {if $mag.active == 0}, inactif{/if}
                     {if $mag.visibility == 'none'}, invisible{/if}
                  </div>
               </a>

            {/foreach}

            {for $num=($biggerMagazine.reference + 1) to $max}
               <div class="num next nonexistent">
                  {$num}
                  <div class="infobox">
                     Ce numéro n'existe pas
                  </div>
               </div>
            {/for}
         {/capture}

      <div class="row years main"  style="width:{$subsWidth + 410}px">
         {$smarty.capture.years}
      </div>

      {foreach $customers as $customer}
         {assign "customerRemindDate" $customer->getNextRemindDate()}
         <div class="row customer {if $customerRemindDate == $nextRemindDate} remind{/if}{if $customer->excludeFromRemind} excluded{/if}">
            <div class="leftheader">
               <a href="index.php?controller=AdminCustomers&amp;id_customer={$customer->id}&amp;viewcustomer&amp;token={Tools::getAdminTokenLite('AdminCustomers')}" class="edit" title="Modifier">
                  <img src="../img/admin/edit.gif" alt="Modifier">
               </a>
               {$customer->id}. {$customer->email}

               <span style="font-size:11px;font-weight:normal">
               {if !$customer->excludeFromRemind}
                  {$customerRemindDate|date_format:'d.m.Y'}
               {else}
                  relance manuellement
               {/if}
               </span>
            </div><!--

            --><div class="subscriptionsContainer" style="width:{$subsWidth}px;">
               {if !empty($customer->user_subscriptions)}
                  <div class="subscriptions">
                     <div class="actualMarker" style="left:{($actual - $min) * $numWidth + 1}px;"></div>

                     {foreach $customer->user_subscriptions as $sub}
                        <div class="sub sub{$sub->number_of_editions/4} subType{$sub->product->id}{if $sub->is_active} active{/if}{if $sub->is_future} future{/if}{if $sub->last_edition < $actual} archive{/if}{if $sub->order_history->id_order_state == $imported_order_state} imported{/if}{if $sub->is_archive} web{/if}{if $sub->is_paper} paper{/if}"
                             style="left:{($sub->first_edition - $min) * $numWidth + 1}px;right:{($max - $sub->last_edition) * $numWidth}px">

                           <a href="index.php?controller=AdminOrders&id_order={$sub->order->id}&vieworder&token={Tools::getAdminTokenLite('AdminOrders')}" class="edit" title="Modifier">
                              <img src="../img/admin/edit.gif" alt="Modifier">
                           </a>

                           {$sub->order->reference}, {$sub->order->date_add|date_format:"d.m.Y"}

                        </div>
                     {/foreach}
                  </div>
               {/if}
               {if !empty($customer->user_subscriptions) && $customer->tierce_subscription}
                  <div class="subSeparator"></div>
               {/if}
               {if $customer->tierce_subscription}
                  <div class="subscriptions tierces">
                     <div class="actualMarker" style="left:{($actual - $min) * $numWidth + 1}px;"></div>

                     {assign var="sub" value=$customer->tierce_subscription}
                     <div class="sub tierce sub{$sub->number_of_editions/4}  subType{$sub->product->id}{if $sub->is_active} active{/if}{if $sub->is_future} future{/if}{if $sub->last_edition < $actual} archive{/if}{if $sub->order_history->id_order_state == $imported_order_state} imported{/if}{if $sub->is_archive} web{/if}{if $sub->is_paper} paper{/if}"
                          style="left:{($sub->first_edition - $min) * $numWidth + 1}px;right:{($max - $sub->last_edition) * $numWidth}px">

                        <a href="index.php?controller=AdminOrders&id_order={$sub->order->id}&vieworder&token={Tools::getAdminTokenLite('AdminOrders')}" class="edit" title="Modifier">
                           <img src="../img/admin/edit.gif" alt="Modifier">
                        </a>

                        {$sub->order->reference}

                        <a href="index.php?controller=AdminCustomers&amp;id_customer={$sub->customer->id}&amp;viewcustomer&amp;token={Tools::getAdminTokenLite('AdminCustomers')}" class="edit owner" target="_blank" title="Modifier">
                           {$sub->customer->email}
                        </a>
                     </div>
               </div>
               {/if}

               {if !empty($customer->user_ignored_subscriptions)}
                  {foreach $customer->user_ignored_subscriptions as $sub}
                     <div class="subSeparator"></div>
                     <div class="subscriptions ignored">
                        <div class="actualMarker" style="left:{($actual - $min) * $numWidth + 1}px;"></div>

                           <div class="sub ignored sub{$sub->number_of_editions/4} subType{$sub->product->id}{if $sub->is_active} active{/if}{if $sub->is_future} future{/if}{if $sub->last_edition < $actual} archive{/if}{if $sub->order_history->id_order_state == $imported_order_state} imported{/if}{if $sub->is_archive} web{/if}{if $sub->is_paper} paper{/if}"
                                style="left:{($sub->first_edition - $min) * $numWidth + 1}px;right:{($max - $sub->last_edition) * $numWidth}px">

                              <a href="index.php?controller=AdminOrders&id_order={$sub->order->id}&vieworder&token={Tools::getAdminTokenLite('AdminOrders')}" class="edit" title="Modifier">
                                 <img src="../img/admin/edit.gif" alt="Modifier">
                              </a>

                              {$sub->order->reference}, {$sub->order->date_add|date_format:"d.m.Y"}

                           </div>
                     </div>
                  {/foreach}
               {/if}
            </div>
            <div class="clearfix"></div>
         </div>

         <div class="row years mini"></div>

      {/foreach}

   </div>
</div>
