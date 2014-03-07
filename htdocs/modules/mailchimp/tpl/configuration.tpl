{*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
var id_lang = '{$id_lang}';
var base_uri = '{$BASE_URI}';
var css = '{$PS_CSS}';
var ad = '{$ad}';
var lang_name = '{$lang_name}';
</script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.js" type="text/javascript"></script>
<script src="../js/tiny_mce/tiny_mce.js" type="text/javascript"></script>
<script type="text/javascript" src="../modules/mailchimp/js/myTinyMce.js"></script>
<fieldset id="mailchimp_intro">
  <legend>{l s='MailChimp Module'}</legend>
  <div id="mailchimp_intro_content">
    <p>{l s='In Order to use the MailChimp module, you must first have a MailChimp account and submit the correct API key.' mod='mailchimp'}<br/>{l s='Once the API Key is validated, you will be able to manage your campaign.' mod='mailchimp'}</p><br/>
    {if $api_key == false}
    <p><strong>{l s='Please create a MailChimp account' mod='mailchimp'} <a target="_BLANK" style="color:blue;text-decoration:underline" href="https://mailchimp.com/signup/">{l s='here' mod='mailchimp'}!</a></strong><br/>{l s='If you already have a Mailchimp account, log in and click on "API Keys & Authorized Apps" in your Account Settings.' mod='mailchimp'}</p><br/>
    <img src="../modules/mailchimp/img/warn2.png" /><strong>{l s='Please enter your correct MailChimp Api Key in the "API Key Account" field below.' mod='mailchimp'}</strong><br/>
    {/if}
    {if $lists.data|count == 0 && $api_key != false}
    <img src="../modules/mailchimp/img/warn2.png" /><strong>{l s='Please create a MailChimp list' mod='mailchimp'} <a target="_BLANK" style="color:blue;text-decoration:underline" href="https://{$api_dc}.admin.mailchimp.com/lists/">{l s='here' mod='mailchimp'}</a></strong>
    {/if}
    {if $api_key != false}
    <p></p>
    <!--<div class="hint" style="visibility:hidden">
      <span id="mailchimp_help_list">{l s='List Settings: Manage the Customers in your Mailchimp Lists' mod='mailchimp'}</span>
      <span style="display:none" id="mailchimp_help_campaign">{l s='Campaigns: Delete, Update, Replicate, Schedule or send your Campaign newsletter' mod='mailchimp'}</span>
      <span style="display:none" id="mailchimp_help_create">{l s='Create Campaign: Create a new Campaign and customize your newsletter' mod='mailchimp'}</span>
      <span style="display:none" id="mailchimp_help_stat">{l s='Statistics: Keep track of all your campaign actions, including number of opened e-mails, clickthrough rate and more' mod='mailchimp'}</span>
    </div>-->
    {/if}
  </div>
</fieldset>
<br/><br/>
<ul id="menuTab">
    <li id="menuTab1" class="menuTabButton selected"><img src="../modules/mailchimp/logo.gif" alt="logo"/> {l s='MailChimp' mod='mailchimp'}</li>
  <li id="menuTab2" class="menuTabButton"><img src="../modules/mailchimp/img/key.png" style="width:16px" alt="logo"/> {l s='API Key Account' mod='mailchimp'}</li>
  {if $api_key != false}
  <li id="menuTab3" class="menuTabButton"><img src="../modules/mailchimp/img/list_settings.png" style="width:16px" alt="list_settings"/> {l s='List Settings' mod='mailchimp'}</li>
  <li id="menuTab4" class="menuTabButton"><img src="../modules/mailchimp/img/campaign.png" style="width:16px" alt="campaign"/> {l s='Campaigns' mod='mailchimp'}</li>
  <li id="menuTab5" class="menuTabButton"><img src="../img/t/AdminTools.gif" style="width:16px" alt="campaign"/> {l s='Create Campaign' mod='mailchimp'}</li>
  <li id="menuTab6" class="menuTabButton"><img src="../img/t/AdminStats.gif" style="width:16px" alt="stats"/> {l s='Statistics' mod='mailchimp'}</li>
  <li id="menuTab7" class="menuTabButton"><img src="../modules/mailchimp/img/target.png" style="width:16px" alt="segmentation"/> {l s='Segmentation' mod='mailchimp'}</li>
  {/if}
</ul>
<!-- INTRO -->
<div id="tabList">
  <div id="menuTab1Sheet" class="tabItem selected" style="height:275px">
    <h2>{l s='Easy Email Newsletters' mod='mailchimp'}</h2>
    <input type="hidden" id="mailchimp_configuration_token" value="{$token}"/>
    <input type="hidden" id="mailchimp_campaign_api" value="{$api_key}"/>
    <div style="float:right">
      <img src="../modules/mailchimp/img/chimp.png"/><br/>
      <p style="text-align:right">MailChimp&reg;</p>
    </div>
    <p style="font-size:15px;margin:80px 0 0.5em 100px;text-align:center">{l s='PrestaShop\'s Mailchimp module helps put all your customers into a Mailchimp mailing lists.' mod='mailchimp'}<br/>
      {l s='It will also help you design your email newsletters and track your results.' mod='mailchimp'}</p>
  </div>
<!-- API key form -->
  <div id="menuTab2Sheet" class="tabItem">
    {if isset($mailChimp_validations)}
    <div class="conf">
      {foreach from=$mailChimp_validations item=validate}
      <img src="{$PS_IMG}admin/enabled.gif" alt="nok" />&nbsp;{$validate}</br>
      {/foreach}
    </div>
    {/if}
    {if isset($mailChimp_errors)}
    <div class="error">
      {foreach from=$mailChimp_errors item=error}
      <img src="{$PS_IMG}admin/forbbiden.gif" alt="nok" />&nbsp;{$error}</br>
      {/foreach}
    </div>
    {/if}
    <p>{l s='The MailChimp API key is provided to you by MailChimp' mod='mailChimp'}. {l s='If you have not yet registered, click ' mod='mailchimp'} <a target="_BLANK" style="color:blue;text-decoration:underline" href="https://mailchimp.com/signup/">{l s='here' mod='mailchimp'}</a>.</p>
<p>{l s='Your Mailchimp API key is located in your Mailchimp Account Settings under "API Keys & Authorized Apps"' mod='mailchimp'}.</p>
    <form action="{$mailchimp_configure_url}&action_form=api&id_tab=2" method="POST">
      <fieldset style="border: 0px;">
	<label>{l s='MailChimp\'s API Key' mod='mailchimp'}: </label>
	<div class="margin-form"><input type="text" size="40" name="mailchimp_api_key" value="{$api_key}" /></div>
	<div class="margin-form"><input class="button" name="submitSave" type="submit" value={l s='Save' mod='mailchimp'}></div>
      </fieldset>
    </form>
  </div>
  {if $api_key != false}
<!--List settings -->
  <div id="menuTab3Sheet" class="tabItem">
    {if $lists.data|count == 0}
    <p>{l s='You don\'t have any MailChimp list. Please create a MailChimp list ' mod='mailchimp'}<a target="_BLANK" style="color:blue;text-decoration:underline" href="https://{$api_dc}.admin.mailchimp.com/lists/">{l s='here' mod='mailchimp'}</a>.</p>
    {else}
    <p>{l s='Each time a customer creates an account in your shop, they will automatically receive an e-mail confirming their subscription to one of your MailChimp lists.' mod='mailchimp'}<br/>
      <!--{l s='When your customers update their personal information, it is automatically updated on your MailChimp list' mod='mailchimp'}--></p>
    <fieldset style="border:0">
      <label style="width:400px">{l s='Please select the list for new customers' mod='mailchimp'}</label>
      <div class="margin-form">
	<select id="mailchimp_synchro_list" onchange="listInformationMailChimp()">
	  <option value="">{l s='Select' mod='mailchimp'}</option>
	  {foreach from=$lists.data item=val}
	  <option value="{$val.id}" {if $synchro_list == $val.id}selected='selected'{/if}>{$val.name}</option>
	  {/foreach}
	</select>
      </div>
      <div id="list_info_synch">
	<p id="sync_result"></p>
	<strong>{l s='Please click here to add those customers to your list' mod='mailchimp'}</strong> <input type="button" class="button" onclick="synchronize()" value="{l s='Update' mod='mailchimp'}"/> <img style="display:none" id="mailchimp_synchronize_waiting" src="../img/loader.gif" alt="wait"/>
	<p id="sync_result_error"></p>
      </div>
    </fieldset>
    {/if}
  </div>
<!--Campaigns -->
  <div id="menuTab4Sheet" class="tabItem">
    <p>
      {l s='Here are your created Campaigns. If your campaigns have not been sent, you have the choice to update, delete, send or even schedule them for delivery.' mod='mailchimp'}<br/>
      {l s='If your campaigns have already been sent, you can duplicate or delete them.' mod='mailchimp'}
    </p><br/>
    <div id="mailchimp_campaign_list_info">
    </div>
  </div>
<!-- CREATE UPDATE Campaigns -->
  <div id="menuTab5Sheet" class="tabItem">
    {if $lists.data|count == 0}
    <p>{l s='You don\'t have any MailChimp lists. Please create a MailChimp list ' mod='mailchimp'}<a target="_BLANK" style="color:blue;text-decoration:underline" href="https://{$api_dc}.admin.mailchimp.com/lists/">{l s='here' mod='mailchimp'}</a>.</p>
    {else}
    <link href="../modules/mailchimp/css/step.css" rel="stylesheet" type="text/css"/>
    <h4 id="mailchimp_campaign_update_title" style="display:none;">{l s='Update Campaign' mod='mailchimp'} <span id="mailchimp_campaign_update_title_span"></span><span style="cursor:pointer;color:blue;float:right" onclick="createMode()">{l s='Back to Create Mode' mod='mailchimp'}</span></h4>
    <h4 id="mailchimp_campaign_creation_title">{l s='Create a new Campaign' mod='mailchimp'}</h4>
    <ul class="step">
      <li style="cursor:pointer">{l s='Type' mod='mailchimp'}</li>
      <li style="display:none;">{l s='RSS' mod='mailchimp'}</li>
      <li style="">{l s='Recipients' mod='mailchimp'}</li>
      <li style="display:none;">{l s='Design' mod='mailchimp'}</li>
      <li >{l s='Plain-Text' mod='mailchimp'}</li>
      <li >{l s='Create' mod='mailchimp'}</li>
      <li id="step_end">{l s='Send' mod='mailchimp'}</li>
    </ul>

    <div id="content_campaign">
      <!-- Type -->
      <div style="display:none">
	<p>{l s='Please select the type of newsletter you want to send from the drop-down menu below.' mod='mailchimp'}</p>
	<p><strong>{l s='Regular' mod='mailchimp'}:</strong> {l s='Your newsletter will have some HTML content. This will allow you to improve the readability and appearance of your e-mails.' mod='mailchimp'}<br/>
	<strong>{l s='Plain-text' mod='mailchimp'}:</strong> {l s='Your newsletter will have text, but no images or colors.' mod='mailchimp'}</p><br/>
	<fieldset style="border:0px;padding:0px;margin-top:-5px">
	  <label>{l s='Type' mod='mailchimp'}: </label>
	  <div class="margin-form">
	    <select id="mailchimp_campaign_type" name="mailchimp_campaign_type">
	      <option value="">{l s='Select' mod='mailchimp'}</option>
	      <option value="REGULAR">{l s='Regular (html content)' mod='mailchimp'}</option>
	      <option value="PLAIN">{l s='Plain-text email (no picture or formatting)' mod='mailchimp'}</option>
	      <!--<option value="RSS">{l s='Content from an RSS feed' mod='mailchimp'}</option>-->
	    </select>
	  </div>
	  <div class="margin-form">
	    <input type="button" class="button" id="mailchimp_campaign_step_1" value="{l s='next' mod='mailchimp'}"/>
	    <span style="display:none;color:red">{l s='Please select a type of e-mail from the drop-down menu above' mod='mailchimp'}</span>
	  </div>
	</fieldset>
      </div>

      <!-- RSS -->
      <div style="display:none">
	<fieldset style="border:0px;padding:0px;margin-top:-5px">
	  <label>{l s='RSS link' mod='mailchimp'}: </label>
	  <div class="margin-form">
	    <input typ="text" name="mailchimp_campaign_rss" id="mailchimp_campaign_rss" />
	  </div>
	</fieldset>
      </div>

      <!-- list -->
	<div style="display:none">
	  <p style="margin:auto">{l s='Please select a MailChimp list from the drop-down menu below.' mod='mailchimp'}<br/>{l s='Your campaign will be sent to all the customers who are a part of this MailChimp list.' mod='mailchimp'}</p><br/>
	  <fieldset style="border:0px;padding:0px;margin-top:-5px">
	    <label>{l s='List' mod='mailchimp'}: </label>
	    <div class="margin-form">
	      <select id="mailchimp_campaign_list">
		<option value="">{l s='Select' mod='mailchimp'}</option>
		{foreach from=$lists.data item=val}
		<option value="{$val.id}">{$val.name}</option>
		{/foreach}
	      </select>
	    </div>
	    <div class="margin-form">
	      <input type="button" class="button" id="mailchimp_campaign_step_3" value="{l s='next' mod='mailchimp'}"/>
	      <span style="display:none;color:red">{l s='Please select a MailChimp list from the drop-down menu above' mod='mailchimp'}</span>
	    </div>
	  </fieldset>
	</div>

	<!-- design -->
	<div style="display:none">
	  <p>{l s='Please select an option below.' mod='mailchimp'}<br/><br/>
	    <strong>{l s='Gallery:' mod='mailchimp'}</strong> {l s='Pre-designed templates ready for your content.' mod='mailchimp'}<br/>
	    <strong>{l s='Basic:' mod='mailchimp'}</strong> {l s='Simple email layouts ready for your logo, design and content.' mod='mailchimp'}<br/>
	    <strong>{l s='Custom:' mod='mailchimp'}</strong> {l s='Write your own code to create a truly customized template.' mod='mailchimp'}<br/>
	    <strong>{l s='My Templates:' mod='mailchimp'}</strong> {l s='Templates you\'ve designed and saved on MailChimp for later use.' mod='mailchimp'}<br/>
	  </p><br/>
	  <fieldset style="border:0px;padding:0px;margin-top:-5px">
	    <div style="text-align:center">
	      <input type="radio" name="mailchimp_campaign_design_type" value="gallery"/> : {l s='Gallery' mod='mailchimp'} <input type="radio" name="mailchimp_campaign_design_type" value="base"/> : {l s='Basic' mod='mailchimp'} <input type="radio" name="mailchimp_campaign_design_type" value="own"/> : {l s='Custom' mod='mailchimp'} <input type="radio" name="mailchimp_campaign_design_type" value="user"/> : {l s='My Templates' mod='mailchimp'}
	    </div>
	      </br>
	      <div id="mailchimp_campaign_design_load" style="display:none;text-align:center;background:url('{$PS_IMG}admin/bg_2.png')"><img src="{$PS_IMG}admin/ajax-loader-big.gif" /></div>
	      <div id="mailchimp_campaign_design_list" style="display:none;height:250px;overflow-y:auto;padding-left:70px">
	      </div>
	      <br/>
	      <div id="mailchimp_campaign_design_box" style="display:none">
		<textarea class="rte" id="mailchimp_campaign_design_code"></textarea><br/>
		<input class="button" style="margin-bottom:10px" type="button" value="{l s='Popup Preview' mod='mailchimp'}" onclick="visualizeCampaign()"/>
	      </div>
	      <div id="mailchimp_campaign_design_visualize" style="width:100%" title="{l s='Preview' mod='mailchimp'}"></div>
	      <div style="text-align:right">
		<span style="display:none;color:red">{l s='Please choose a template' mod='mailchimp'}</span> <input type="button" class="button" id="mailchimp_campaign_step_4" value="{l s='next' mod='mailchimp'}"/>
	      </div>
	      <br/>
	  </fieldset>
	</div>

	<div style="display:none">
	  <p>{l s='Please enter your plain-text message below' mod='mailchimp'}</p>
	  <p>{l s='This plain-text email is displayed if recipients can\'t (or won\'t) display your HTML email. Your message might get flagged by spam filters without a plain-text message.' mod='mailchimp'}</p><br/>
	  <fieldset style="border:0px;padding:0px;margin-top:-5px">
	    <textarea id="mailchimp_campaign_plain_text" style="width:890px;height:150px"></textarea>
	    <div style="text-align:right;margin-top:10px">
	      <span style="display:none;color:red">{l s='Please enter your plain-text message above' mod='mailchimp'}</span> <input type="button" class="button" id="mailchimp_campaign_step_5" value="{l s='next' mod='mailchimp'}"/>
	    </div>
	    <br/>
	  </fieldset>
	</div>

	<div style="display:none">
	  <fieldset style="border:0px;padding:0px;margin-top:-5px">
	    <input type="hidden" id="mailchimp_campaign_update_id" value=""/>
	    <label>{l s='Title' mod='mailchimp'}:</label>
	    <div class="margin-form">
	      <input type="text" id="mailchimp_campaign_title" /> <span style="color:red;display:none">{l s='Please enter your campaign title.' mod='mailchimp'}</span>
	    </div>
	    <label>{l s='Subject' mod='mailchimp'}:</label>
	    <div class="margin-form">
	      <input type="text" id="mailchimp_campaign_subject"/> <span style="display:none;color:red">{l s='Please enter an e-mail subject.' mod='mailchimp'}</span>
	    </div>
	    <label>{l s='From e-mail (your e-mail)' mod='mailchimp'}:</label>
	    <div class="margin-form">
	      <input type="text" id="mailchimp_campaign_from_email" value="{$from_email}"/> <span>{l s='Please, be sure to verify this e-mail address' mod='mailchimp'} <a href="https://{$api_dc}.admin.mailchimp.com/account/domains/">{l s='here' mod='mailchimp'}</a></span> <span style="color:red;display:none">{l s='Please enter a "From" e-mail address.' mod='mailchimp'}</span>
	    </div>
	    <label>{l s='From name (your name)' mod='mailchimp'}:</label>
	    <div class="margin-form">
	      <input type="text" id="mailchimp_campaign_from_name"/> <span style="color:red;display:none">{l s='Who will be sending this newsletter?' mod='mailchimp'}</span>
	    </div>
	    <label>{l s='Reply to e-mail' mod='mailchimp'}:</label>
	    <div class="margin-form">
	      <input type="text" id="mailchimp_campaign_reply_to" value="{$from_email}"/> <span>{l s='Please, be sure to verify this e-mail address' mod='mailchimp'} <a href="https://{$api_dc}.admin.mailchimp.com/account/domains/">{l s='here' mod='mailchimp'}</a></span>
	    </div>
	    <div class="margin-form">
	      <input type="button" class="button" id="mailchimp_campaign_create" value="{l s='Create' mod='mailchimp'}"/>  <input type="button" style="display:none" class="button" id="mailchimp_campaign_update" value="{l s='Update' mod='mailchimp'}"/> <img style="display:none" id="mailchimp_create_campaign_waiting" src="../img/loader.gif" alt="wait"/><br/>
	      <span id="mailchimp_campaign_result_create"></span>
	    </div>
	  </fieldset>
	</div>
    </div>
    {/if}
  </div>
  <div id="menuTab6Sheet" class="tabItem">
    <div id="mailchimp_campaign_stat_load" style="display:none;text-align:center;background:url('{$PS_IMG}admin/bg_2.png')"><img src="{$PS_IMG}admin/ajax-loader-big.gif" /></div>
    <div id="mailchimp_campaign_stat_table"></div>
  </div>
  <div id="menuTab7Sheet" class="tabItem">
    <p style="font-size:15px">{l s='Each time a new order is created, all the order information is sent to your MailChimp account. This allows you to send a targeted event invitation to subscribers who have purchased items from your store instead of sending it to your entire list.' mod='mailchimp'}<br/><br/>{l s='For the most part, segmenting your email marketing lists can help improve your open and click rates. This will reduce the number of bounces from each campaign you send.' mod='mailchimp'}</p>
  </div>
  {/if}
</div>
<br clear="left" />
<br />
<style>
  {literal}
  #menuTab { float: left; padding: 0; margin: 0; text-align: left; }
  #menuTab li { text-align: left; float: left; display: inline; padding: 5px; padding-right: 10px; background: #EFEFEF; font-weight: bold; cursor: pointer; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; margin-left:5px; height:14px}
  #menuTab li.menuTabButton.selected { background: #FFFFF0; border-left: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; margin-bottom:-6px;margin-top:-5px;height:20px}
  #tabList { clear: left; }
  .tabItem { display: none; }
  .tabItem.selected { display: block; background: #FFFFF0; border: 1px solid #CCCCCC; padding: 10px; padding-top: 20px;}

legend
{
    text-align:center;
    width:99%;
    margin-bottom:10px;
}
  {/literal}
</style>
<script type="text/javascript">
{literal}
/*$(".menuTabButton").hover(function()
			  {
			      $("#mailchimp_intro_content .hint").show();
			  },
			  function()
			  {
			      $("#mailchimp_intro_content .hint").hide();
			  }
			 );
*/
$(".menuTabButton").click(function () {
$(".menuTabButton.selected").removeClass("selected");
$(this).addClass("selected");
$(".tabItem.selected").removeClass("selected");
$("#" + this.id + "Sheet").addClass("selected");
});
{/literal}
{if (isset($mailchimp_id_tab))}
var id_tab = '{$mailchimp_id_tab}';
{literal}
$(".menuTabButton.selected").removeClass("selected");
$("#menuTab"+id_tab).addClass("selected");
$(".tabItem.selected").removeClass("selected");
$("#menuTab"+id_tab+"Sheet").addClass("selected");
{/literal}
{/if}
</script>

<script type="text/javascript" src="../modules/mailchimp/js/configuration.js"></script>
<script type="text/javascript">
{literal}
function visualizeCampaign()
{
$("#mailchimp_campaign_design_visualize").html(tinyMCE.get('mailchimp_campaign_design_code').getContent()).dialog({modal:true, width:"860px", closeText: ' '});
$(".ui-dialog-titlebar").css("height", "20px");
$(".ui-dialog-titlebar").css("padding", "10px");
$(".ui-icon-closethick").css("float", "right");
}
{/literal}
</script>
