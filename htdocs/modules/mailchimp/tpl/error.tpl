{*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="error">
  {foreach from=$mailChimp_errors item=error}
     <img src="{$PS_IMG}admin/forbbiden.gif" alt="nok" />&nbsp;{$error}</br>
  {/foreach}
</div>
