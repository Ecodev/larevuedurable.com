{*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="conf">
  {foreach from=$mailChimp_validations item=validate}
     <img src="{$PS_IMG}admin/enabled.gif" alt="nok" />&nbsp;{$validate}</br>
  {/foreach}
</div>
