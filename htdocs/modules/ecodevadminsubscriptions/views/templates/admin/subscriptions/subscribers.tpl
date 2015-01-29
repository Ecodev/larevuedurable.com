{assign "numWidth" "50"}
<div class="adminsubs" style="width:{($max-$min+1) * $numWidth + 410}px">


   <div class="row years">
      {for $num=$min to $max}
         <div class="num{if $num < $actual} archive{/if}{if $num == $actual} active{/if}{if $num > $actual} future{/if}">
            {$num}
         </div>
      {/for}
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

      <div class="row years mini">
         {for $num=$min to $max}
            <div class="num{if $num < $actual} archive{/if}{if $num == $actual} active{/if}{if $num > $actual} future{/if}"></div>
         {/for}
      </div>
   {/foreach}

</div>