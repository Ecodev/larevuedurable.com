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
{if $ps_version_1_7}
    <div class="form-group row required page_{$captcha_page|escape:'html':'UTF-8'} ps_v_17">
		<label class="col-md-3" for="pa_captcha">{if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE != 'google'}{l s='Enter security code' mod='ets_advancedcaptcha'}{else}{l s='Security check' mod='ets_advancedcaptcha'}{/if} {if $captcha_page=='registration'}<sub>*</sub>{/if}</label>
        <div class="col-md-6">
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
        <div class="col-md-3 form-control-comment"></div>
    </div>
{elseif $ps_version_1_6}
    <div class="form-group required page_{$captcha_page|escape:'html':'UTF-8'} ps_v_16">
        <div class="pa-captcha-field-row">
    		<label for="pa_captcha">{if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE != 'google'}{l s='Enter security code' mod='ets_advancedcaptcha'}{else}{l s='Security check' mod='ets_advancedcaptcha'}{/if} {if $captcha_page=='registration'}<sub>*</sub>{/if}</label>
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
{else}
    <p class="text page_{$captcha_page|escape:'html':'UTF-8'} ps_v_15">
        <label for="pa_captcha">{if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE != 'google'}{l s='Enter security code' mod='ets_advancedcaptcha'}{else}{l s='Security check' mod='ets_advancedcaptcha'}{/if} {if $captcha_page=='registration'}<sub>*</sub>{/if}</label>
        <span class="pa-captcha-field-cell">    		
            {if isset($PA_CAPTCHA_TYPE) && $PA_CAPTCHA_TYPE != 'google'}
                <span class="pa-captcha-img">
                    <img class="pa-captcha-img-data" src="{$captcha_image|escape:'html':'UTF-8'}" alt="{l s='Security code' mod='ets_advancedcaptcha'}" title="{l s='Security code' mod='ets_advancedcaptcha'}" />
                    <span id="pa-captcha-refesh" data-rand="{$rand|escape:'html':'UTF-8'}"><img title="{l s='Refresh the code' mod='ets_advancedcaptcha'}" alt="{l s='Refresh the code' mod='ets_advancedcaptcha'}" src="{$modules_dir|escape:'html':'UTF-8'}ets_advancedcaptcha/views/img/refresh.png" /></span>
                </span>
                <input type="text" name="pa_captcha" id="pa_captcha"/>
            {else}
                <div id="g_recaptcha" class="g-recaptcha" ></div>
            {/if}
        </span>    	
    </p>    
{/if}
{if $PA_CAPTCHA_TYPE=='google' && isset($captcha_page) && $captcha_page}
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
{/if}