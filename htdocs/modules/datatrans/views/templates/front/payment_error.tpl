{*
** Creator   : WDXperience SARL : YM (121008)
** Copyright : All Right Reserved - Licence available for 1 shop
** Licence   : Prices and Conditions on http://www.wdxperience.ch/shop/
** Compat    : Prestashop v1.5
*}

<div id="page" class="clearfix container_9">

    <div id="columns" class="grid_9 alpha omega clearfix mceContentBody">

        <!-- Center -->
        <div id="center_column" class="center grid_7">

        <h1 style="margin-top: 40px; line-height: 1.4em; color:red;">{$shop_name} - {$title} : {l s='transaction declined' mod='datatrans'}</h1>
        <p>{l s='Permission was refused by the financial institution. Please choose another payment method.' mod='datatrans'}</p>
        <p><a href="{$link->getPageLink('order', true, NULL, NULL)}" class="button_large">&laquo; {l s='Another payment method' mod='datatrans'}</a></p>