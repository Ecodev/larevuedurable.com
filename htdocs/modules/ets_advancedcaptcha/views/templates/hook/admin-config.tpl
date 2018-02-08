{*
* 2007-20118 PrestaShop
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
{if $ps_version_1_7}
    {if $setting_updated}
        {if $errors == ''}
            <div class="alert alert-success">{l s='Setting updated' mod='ets_advancedcaptcha'}</div>
        {else}
            <div class="alert alert-danger">{$errors|escape:'html':'UTF-8'}</div>
        {/if}
    {/if}
    <form class="defaultForm form-horizontal" enctype="multipart/form-data" method="post" action="{$postUrl|escape:'html':'UTF-8'}">
        <div class="panel">
            <div class="panel-heading"><i class="icon-cogs"></i> {l s='Setting' mod='ets_advancedcaptcha'}</div>
            
            <div class="form-wrapper">
                <div class="form-group">
                    <label class="control-label col-lg-3" for="transition-effect">{l s='Enable captcha for contact form' mod='ets_advancedcaptcha'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
    				        <input type="radio" {if $PA_CAPTCHA_CONTACT}checked="checked"{/if} value="1" id="PA_CAPTCHA_CONTACT_on" name="PA_CAPTCHA_CONTACT"/>
    						<label for="PA_CAPTCHA_CONTACT_on">{l s='Yes' mod='ets_advancedcaptcha'}</label>
    						<input type="radio" {if !$PA_CAPTCHA_CONTACT}checked="checked"{/if} value="0" id="PA_CAPTCHA_CONTACT_off" name="PA_CAPTCHA_CONTACT"/>
    						<label for="PA_CAPTCHA_CONTACT_off">{l s='No' mod='ets_advancedcaptcha'}</label>
    						<a class="slide-button btn"></a>
    					</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3" for="transition-effect">{l s='Enable captcha for registration form' mod='ets_advancedcaptcha'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                	        <input type="radio" {if $PA_CAPTCHA_REGISTRATION}checked="checked"{/if} value="1" id="PA_CAPTCHA_REGISTRATION_on" name="PA_CAPTCHA_REGISTRATION"/>
                			<label for="PA_CAPTCHA_REGISTRATION_on">{l s='Yes' mod='ets_advancedcaptcha'}</label>
                			<input type="radio" {if !$PA_CAPTCHA_REGISTRATION}checked="checked"{/if} value="0" id="PA_CAPTCHA_REGISTRATION_off" name="PA_CAPTCHA_REGISTRATION"/>
                			<label for="PA_CAPTCHA_REGISTRATION_off">{l s='No' mod='ets_advancedcaptcha'}</label>
                			<a class="slide-button btn"></a>
                		</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Captcha type' mod='ets_advancedcaptcha'}</label>
                    <div class="col-lg-9">
                        {foreach from=$captchaTypes item='type'}
                            <input type="radio" {if $PA_CAPTCHA_TYPE==$type.type}checked="checked"{/if} name="PA_CAPTCHA_TYPE" id="PA_CAPTCHA_TYPE_{strtoupper($type.type|escape:'html':'UTF-8')}" value="{$type.type|escape:'html':'UTF-8'}" /> <label style="width: auto;" for="PA_CAPTCHA_TYPE_{strtoupper($type.type|escape:'html':'UTF-8')}"><img style="border: 1px solid #ccc;" title="{$type.name|escape:'html':'UTF-8'}" alt="{$type.name|escape:'html':'UTF-8'}" src="{$pa_img_dir|escape:'html':'UTF-8'}{$type.type|escape:'html':'UTF-8'}.png" /></label> <br />
                        {/foreach}
                        <div class="hidden-google-captcha col-lg-6">
                            <label for="PA_GOOGLE_CAPTCHA_SITE_KEY">{l s='Site key:' mod='ets_advancedcaptcha'}</label>
                            <input type="text" name="PA_GOOGLE_CAPTCHA_SITE_KEY" value="{if isset($PA_GOOGLE_CAPTCHA_SITE_KEY)}{$PA_GOOGLE_CAPTCHA_SITE_KEY|escape:'html':'UTF-8'}{/if}" />
                            <label for="PA_GOOGLE_CAPTCHA_SITE_KEY">{l s='Secret key:' mod='ets_advancedcaptcha'}</label>
                            <input type="text" name="PA_GOOGLE_CAPTCHA_SECRET_KEY" value="{if isset($PA_GOOGLE_CAPTCHA_SECRET_KEY)}{$PA_GOOGLE_CAPTCHA_SECRET_KEY|escape:'html':'UTF-8'}{/if}" />
                        </div> 
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-default pull-right" name="submitUpdate" id="module_form_submit_btn" value="1" type="submit">
        		  <i class="process-icon-save"></i> {l s='Save' mod='ets_advancedcaptcha'}
        	    </button>																								
            </div>
        </div>
    </form>
{elseif $ps_version_1_6}
    {if $setting_updated}
        <div class="alert alert-success">{l s='Setting updated' mod='ets_advancedcaptcha'}</div>
    {/if}
    <form class="defaultForm form-horizontal" enctype="multipart/form-data" method="post" action="{$postUrl|escape:'html':'UTF-8'}">
        <div class="panel">
            <div class="panel-heading"><i class="icon-cogs"></i> {l s='Setting' mod='ets_advancedcaptcha'}</div>
            
            <div class="form-wrapper">
                <div class="form-group">
                    <label class="control-label col-lg-3" for="transition-effect">{l s='Enable captcha for contact form' mod='ets_advancedcaptcha'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
    				        <input type="radio" {if $PA_CAPTCHA_CONTACT}checked="checked"{/if} value="1" id="PA_CAPTCHA_CONTACT_on" name="PA_CAPTCHA_CONTACT"/>
    						<label for="PA_CAPTCHA_CONTACT_on">{l s='Yes' mod='ets_advancedcaptcha'}</label>
    						<input type="radio" {if !$PA_CAPTCHA_CONTACT}checked="checked"{/if} value="0" id="PA_CAPTCHA_CONTACT_off" name="PA_CAPTCHA_CONTACT"/>
    						<label for="PA_CAPTCHA_CONTACT_off">{l s='No' mod='ets_advancedcaptcha'}</label>
    						<a class="slide-button btn"></a>
    					</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3" for="transition-effect">{l s='Enable captcha for registration form' mod='ets_advancedcaptcha'}</label>
                    <div class="col-lg-9">
                        <span class="switch prestashop-switch fixed-width-lg">
                	        <input type="radio" {if $PA_CAPTCHA_REGISTRATION}checked="checked"{/if} value="1" id="PA_CAPTCHA_REGISTRATION_on" name="PA_CAPTCHA_REGISTRATION"/>
                			<label for="PA_CAPTCHA_REGISTRATION_on">{l s='Yes' mod='ets_advancedcaptcha'}</label>
                			<input type="radio" {if !$PA_CAPTCHA_REGISTRATION}checked="checked"{/if} value="0" id="PA_CAPTCHA_REGISTRATION_off" name="PA_CAPTCHA_REGISTRATION"/>
                			<label for="PA_CAPTCHA_REGISTRATION_off">{l s='No' mod='ets_advancedcaptcha'}</label>
                			<a class="slide-button btn"></a>
                		</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">{l s='Captcha type' mod='ets_advancedcaptcha'}</label>
                    <div class="col-lg-9">
                        {foreach from=$captchaTypes item='type'}
                            <input type="radio" {if $PA_CAPTCHA_TYPE==$type.type}checked="checked"{/if} name="PA_CAPTCHA_TYPE" id="PA_CAPTCHA_TYPE_{strtoupper($type.type|escape:'html':'UTF-8')}" value="{$type.type|escape:'html':'UTF-8'}" /> <label style="width: auto;" for="PA_CAPTCHA_TYPE_{strtoupper($type.type|escape:'html':'UTF-8')}"><img style="border: 1px solid #ccc;" title="{$type.name|escape:'html':'UTF-8'}" alt="{$type.name|escape:'html':'UTF-8'}" src="{$pa_img_dir|escape:'html':'UTF-8'}{$type.type|escape:'html':'UTF-8'}.png" /></label> <br />
                        {/foreach}
                        <div class="hidden-google-captcha col-lg-6">
                            <label for="PA_GOOGLE_CAPTCHA_SITE_KEY">{l s='Site key:' mod='ets_advancedcaptcha'}</label>
                            <input type="text" name="PA_GOOGLE_CAPTCHA_SITE_KEY" value="{if isset($PA_GOOGLE_CAPTCHA_SITE_KEY)}{$PA_GOOGLE_CAPTCHA_SITE_KEY|escape:'html':'UTF-8'}{/if}" />
                            <label for="PA_GOOGLE_CAPTCHA_SITE_KEY">{l s='Secret key:' mod='ets_advancedcaptcha'}</label>
                            <input type="text" name="PA_GOOGLE_CAPTCHA_SECRET_KEY" value="{if isset($PA_GOOGLE_CAPTCHA_SECRET_KEY)}{$PA_GOOGLE_CAPTCHA_SECRET_KEY|escape:'html':'UTF-8'}{/if}" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn btn-default pull-right" name="submitUpdate" id="module_form_submit_btn" value="1" type="submit">
        		  <i class="process-icon-save"></i> {l s='Save' mod='ets_advancedcaptcha'}
        	    </button>																								
            </div>
        </div>
    </form>
{else}
    {if $setting_updated}
        <div class="conf">{l s='Setting updated' mod='ets_advancedcaptcha'}</div>
    {/if}
    <form class="defaultForm form-horizontal" enctype="multipart/form-data" method="post" action="{$postUrl|escape:'html':'UTF-8'}">
        <fieldset>
            <legend><img src="../img/t/AdminTools.gif"/> {l s='Settings' mod='ets_advancedcaptcha'}</legend>
            
            <table id="form" width="500" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr>
                        <td width="130" style="height: 35px;"><label>{l s='Enable captcha for contact form' mod='ets_advancedcaptcha'}</label></td>
                        <td>
                            <label class="t" for="PA_CAPTCHA_CONTACT_on"><img title="{l s='Yes' mod='ets_advancedcaptcha'}" alt="Yes" src="../img/admin/enabled.gif"/></label>
                            <input type="radio" {if $PA_CAPTCHA_CONTACT}checked="checked"{/if} value="1" id="PA_CAPTCHA_CONTACT_on" name="PA_CAPTCHA_CONTACT"/>
    						<label class="t" for="PA_CAPTCHA_CONTACT_on">{l s='Yes' mod='ets_advancedcaptcha'}</label>
                            <label for="PA_CAPTCHA_CONTACT_off" class="t"><img style="margin-left: 10px;" title="{l s='No' mod='ets_advancedcaptcha'}" alt="No" src="../img/admin/disabled.gif"/></label>
    						<input type="radio" {if !$PA_CAPTCHA_CONTACT}checked="checked"{/if} value="0" id="PA_CAPTCHA_CONTACT_off" name="PA_CAPTCHA_CONTACT"/>
    						<label class="t" for="PA_CAPTCHA_CONTACT_off">{l s='No' mod='ets_advancedcaptcha'}</label>
                        </td>
                    </tr>
                    <tr>
                        <td width="130" style="height: 35px;"><label>{l s='Enable captcha for registration form' mod='ets_advancedcaptcha'}</label></td>
                        <td>
                            <label class="t" for="PA_CAPTCHA_REGISTRATION_on"><img title="{l s='Yes' mod='ets_advancedcaptcha'}" alt="Yes" src="../img/admin/enabled.gif"/></label>
                            <input type="radio" {if $PA_CAPTCHA_REGISTRATION}checked="checked"{/if} value="1" id="PA_CAPTCHA_REGISTRATION_on" name="PA_CAPTCHA_REGISTRATION"/>
                			<label for="PA_CAPTCHA_REGISTRATION_on" class="t">{l s='Yes' mod='ets_advancedcaptcha'}</label> 
                            <label for="PA_CAPTCHA_REGISTRATION_off" class="t"><img style="margin-left: 10px;" title="{l s='No' mod='ets_advancedcaptcha'}" alt="No" src="../img/admin/disabled.gif"/></label>    						
                			<input type="radio" {if !$PA_CAPTCHA_REGISTRATION}checked="checked"{/if} value="0" id="PA_CAPTCHA_REGISTRATION_off" name="PA_CAPTCHA_REGISTRATION"/>
                			<label for="PA_CAPTCHA_REGISTRATION_off" class="t">{l s='No' mod='ets_advancedcaptcha'}</label>
                        </td>
                    </tr>
                    <tr>
                        <td width="130" style="height: 35px;"><label class="control-label col-lg-3">{l s='Captcha type' mod='ets_advancedcaptcha'}</label></td>
                        <td>
                            {foreach from=$captchaTypes item='type'}
                                <div style="display: inline-block; margin-bottom: 10px;">
                                    <input style="float: left; margin-right: 5px; margin-top: 10px;" type="radio" {if $PA_CAPTCHA_TYPE==$type.type}checked="checked"{/if} name="PA_CAPTCHA_TYPE" id="PA_CAPTCHA_TYPE_{strtoupper($type.type|escape:'html':'UTF-8')}" value="{$type.type|escape:'html':'UTF-8'}" /> <label style="width: auto;" for="PA_CAPTCHA_TYPE_{strtoupper($type.type|escape:'html':'UTF-8')}"><img style="border: 1px solid #ccc;" title="{$type.name|escape:'html':'UTF-8'}" alt="{$type.name|escape:'html':'UTF-8'}" src="{$pa_img_dir|escape:'html':'UTF-8'}{$type.type|escape:'html':'UTF-8'}.png" /></label>
                                </div>
                            {/foreach}
                            <div class="hidden-google-captcha col-lg-6">
                                <label for="PA_GOOGLE_CAPTCHA_SITE_KEY">{l s='Site key:' mod='ets_advancedcaptcha'}</label>
                                <input type="text" name="PA_GOOGLE_CAPTCHA_SITE_KEY" value="{if isset($PA_GOOGLE_CAPTCHA_SITE_KEY)}{$PA_GOOGLE_CAPTCHA_SITE_KEY|escape:'html':'UTF-8'}{/if}" />
                                <label for="PA_GOOGLE_CAPTCHA_SITE_KEY">{l s='Secret key:' mod='ets_advancedcaptcha'}</label>
                                <input type="text" name="PA_GOOGLE_CAPTCHA_SECRET_KEY" value="{if isset($PA_GOOGLE_CAPTCHA_SECRET_KEY)}{$PA_GOOGLE_CAPTCHA_SECRET_KEY|escape:'html':'UTF-8'}{/if}" />
                            </div>                            
                        </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2">
                            <button class="button" name="submitUpdate" id="module_form_submit_btn" value="1" type="submit">{l s='Save' mod='ets_advancedcaptcha'}</button>
                        </td>
                    </tr>
                </tbody>
            </table>            
        </fieldset>
    </form>
{/if}