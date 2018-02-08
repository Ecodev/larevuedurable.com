{*
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2018 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<section class="contact-form">
  <form action="{$urls.pages.contact|escape:'html':'UTF-8'}" method="post" {if $contact.allow_file_upload}enctype="multipart/form-data"{/if}>

    {if $notifications}
      <div class="col-xs-12 alert {if $notifications.nw_error}alert-danger{else}alert-success{/if}">
        <ul>
          {foreach $notifications.messages as $notif}
            <li>{$notif|escape:'html':'UTF-8'}</li>
          {/foreach}
        </ul>
      </div>
    {/if}

    <section class="form-fields">

      <div class="form-group row">
        <div class="col-md-9 col-md-offset-3">
          <h3>{l s='Contact us' mod='ets_advancedcaptcha'}</h3>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-md-3 form-control-label">{l s='Subject' mod='ets_advancedcaptcha'}</label>
        <div class="col-md-6">
          <select name="id_contact" class="form-control form-control-select">
            {foreach from=$contact.contacts item=contact_elt}
              <option value="{$contact_elt.id_contact|intval}">{$contact_elt.name|escape:'html':'UTF-8'}</option>
            {/foreach}
          </select>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-md-3 form-control-label">{l s='Email address' mod='ets_advancedcaptcha'}</label>
        <div class="col-md-6">
          <input
            class="form-control"
            name="from"
            type="email"
            value="{$contact.email|escape:'html':'UTF-8'}"
            placeholder="{l s='your@email.com' mod='ets_advancedcaptcha'}"
          >
        </div>
      </div>

      {if $contact.orders}
        <div class="form-group row">
          <label class="col-md-3 form-control-label">{l s='Order reference' mod='ets_advancedcaptcha'}</label>
          <div class="col-md-6">
            <select name="id_order" class="form-control form-control-select">
              <option value="">{l s='Select reference' mod='ets_advancedcaptcha'}</option>
              {foreach from=$contact.orders item=order}
                <option value="{$order.id_order|intval}">{$order.reference|escape:'html':'UTF-8'}</option>
              {/foreach}
            </select>
          </div>
          <span class="col-md-3 form-control-comment">
            {l s='optional' mod='ets_advancedcaptcha'}
          </span>
        </div>
      {/if}

      {if $contact.allow_file_upload}
        <div class="form-group row">
          <label class="col-md-3 form-control-label">{l s='Attachment' mod='ets_advancedcaptcha'}</label>
          <div class="col-md-6">
            <input type="file" name="fileUpload" class="filestyle">
          </div>
          <span class="col-md-3 form-control-comment">
            {l s='optional' mod='ets_advancedcaptcha'}
          </span>
        </div>
      {/if}

      <div class="form-group row">
        <label class="col-md-3 form-control-label">{l s='Message' mod='ets_advancedcaptcha'}</label>
        <div class="col-md-9">
          <textarea
            class="form-control"
            name="message"
            placeholder="{l s='How can we help?' mod='ets_advancedcaptcha'}"
            rows="3"
          >{if $contact.message}{$contact.message|escape:'html':'UTF-8'}{/if}</textarea>
        </div>
      </div>
      {if $PA_CAPTCHA_CONTACT}
          <div class="form-group row">
            <label class="col-md-3 form-control-label">{if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE != 'google'}{l s='Enter security code' mod='ets_advancedcaptcha'}{else}{l s='Security check' mod='ets_advancedcaptcha'}{/if}<sub>*</sub></label>
            <div class="col-md-9">
                {if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE != 'google'}
                    <span class="pa-captcha-img">
                        <img class="pa-captcha-img-data" src="{$captcha_image|escape:'html':'UTF-8'}" alt="{l s='Security code' mod='ets_advancedcaptcha'}" title="{l s='Security code' mod='ets_advancedcaptcha'}" />
                        <span id="pa-captcha-refesh" data-rand="{$rand|escape:'html':'UTF-8'}"><img title="{l s='Refresh the code' mod='ets_advancedcaptcha'}" alt="{l s='Refresh the code' mod='ets_advancedcaptcha'}" src="{$modules_dir|escape:'html':'UTF-8'}ets_advancedcaptcha/views/img/refresh.png" /></span>
                    </span>
                    <input type="text" name="pa_captcha" id="pa_captcha" class="form-control grey"/>
                {else}
                    <div id="g_recaptcha" class="g-recaptcha" ></div>
                {/if}
            </div>
          </div>
      {/if}
    </section>

    <footer class="form-footer text-xs-right">
      <input class="btn btn-primary" type="submit" name="submitMessage" value="{l s='Send' mod='ets_advancedcaptcha'}">
    </footer>

  </form>
</section>
{if $PA_CAPTCHA_TYPE=='google'}
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
{/if}