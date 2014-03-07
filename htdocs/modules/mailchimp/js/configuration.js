function listInformationMailChimp(){
    var list;
    if ($("#mailchimp_synchro_list").val() != '')
    {
	list = $("#mailchimp_synchro_list").val();
	$("#sync_result").load("../modules/mailchimp/ajax/listInformation.php?lang="+id_lang+"&list="+list);
	$("#list_info_synch").slideDown();
    }
    else
    {
	$("#list_info_synch").slideUp();
    }
}

function synchronize(){
    var list = $("#mailchimp_synchro_list").val();
    $("#mailchimp_synchronize_waiting").show();
    $.get("../modules/mailchimp/ajax/synchronize.php?lang="+id_lang+"&list="+list, function(result){
	$("#sync_result_error").html(result);
	$("#mailchimp_synchronize_waiting").hide();
	listInformationMailChimp();
    });
}

$(document).ready(function(){
    campaignInformation();
    listInformationMailChimp();

    campaignStat();
    setCurrentStep(1);
    tinyMCE.execCommand('mceAddControl', true, 'mailchimp_campaign_design_code');

    /*$("#mailchimp_intro").click(function(){
	$("#mailchimp_intro_content").slideToggle();
    });*/

    $("#mailchimp_campaign_creation_title").next().children('li:nth-child(1)').click(function(){
	setCurrentStep(1);
    });

    $("#mailchimp_campaign_creation_title").next().children('li:nth-child(3)').click(function(){
	if (getCurrentStep() >= 3)
	    setCurrentStep(3);
    });

    $("#mailchimp_campaign_creation_title").next().children('li:nth-child(4)').click(function(){
	if (getCurrentStep() >= 4)
	    setCurrentStep(4);
    });
    $("#mailchimp_campaign_creation_title").next().children('li:nth-child(5)').click(function(){
	if (getCurrentStep() >= 5)
	    setCurrentStep(5);
    });


    $("#mailchimp_campaign_type").change(function(){
	if ($(this).val() == "RSS"){
	    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(2)").slideDown();
	    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(4)").slideDown();
	}
	else if ($(this).val() == "PLAIN"){
	    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(4)").slideUp();
	    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(2)").slideUp();

	}
	else if ($(this).val() == "REGULAR"){
	    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(4)").slideDown();
	    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(2)").slideUp();
	}
	else
	{
	    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(4)").slideUp();
	    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(2)").slideUp();
	}
    });

    $("#mailchimp_campaign_step_1").click(function(){
	if ($("#mailchimp_campaign_type").val() != '')
	    changeStateStep(1);
	else
	    $(this).next().show();
    });

    $("#mailchimp_campaign_step_3").click(function(){
	if ($("#mailchimp_campaign_list").val() != '')
	    changeStateStep(3);
	else
	    $(this).next().show();
    });

    $("#mailchimp_campaign_step_4").click(function(){
	if (tinyMCE.get('mailchimp_campaign_design_code').getContent() != '')
	{
	    changeStateStep(4);
	}
	else
	    $(this).prev().show();
    });

    $("#mailchimp_campaign_step_5").click(function(){
	if ($("#mailchimp_campaign_plain_text").val() != '')
	    changeStateStep(5);
	else
	    $(this).prev().show();
    });

    $("#mailchimp_campaign_create, #mailchimp_campaign_update").each(function(){
	$(this).click(function()
		      {
			  var api = $("#mailchimp_campaign_api").val();
			  var type = $("#mailchimp_campaign_type").val();
			  var list = $("#mailchimp_campaign_list").val();
			  var title = $("#mailchimp_campaign_title").val();
			  var design = '';
			  var plain = $('#mailchimp_campaign_plain_text').val();
			  if (type == 'REGULAR')
			      design = tinyMCE.get('mailchimp_campaign_design_code').getContent();
			  var campany = $("#mailchimp_campaign_campany").val();
			  var subject = $("#mailchimp_campaign_subject").val();
			  var from_email = $("#mailchimp_campaign_from_email").val();
			  var from_name = $("#mailchimp_campaign_from_name").val();
			  var reply = $("#mailchimp_campaign_reply_to").val();

			  if (title == '')
			  {
			      $("#mailchimp_campaign_title").next().show();
			      return false;
			  }
			  else
			      $("#mailchimp_campaign_title").next().hide();
			  if (subject == '')
			  {
			      $("#mailchimp_campaign_subject").next().show();
			      return false;
			  }
			  else
			      $("#mailchimp_campaign_subject").next().hide();
			  if (from_email == '')
			  {
			      $("#mailchimp_campaign_from_email").next().next().show();
			      return false;
			  }
			  else
			      $("#mailchimp_campaign_from_email").next().hide();
			  if (from_name == '')
			  {
			      $("#mailchimp_campaign_from_name").next().show();
			      return false;
			  }
			  else
			      $("#mailchimp_campaign_from_name").next().hide();

			  var get = '?lang='+id_lang;
			  var action = '';
			  if ($(this).attr('id') == 'mailchimp_campaign_create')
			      action = 'create';
			  else if ($(this).attr('id') == 'mailchimp_campaign_update')
			  {
			      action = 'update';
			      get += '&id='+$("#mailchimp_campaign_update_id").val();
			  }
			  $("#mailchimp_create_campaign_waiting").show();
			  $.post('../modules/mailchimp/ajax/'+action+'Campaign.php'+get, {api : api, type : type, list : list, design : design, plain : plain, title : title, subject : subject, from_email : from_email, from_name : from_name, reply : reply},
				 function(result){
				     $("#mailchimp_campaign_result_create").html(result);
				     $("#mailchimp_create_campaign_waiting").hide();
				     campaignInformation();
				 });
		      });
    });

    $("#mailchimp_campaign_send_schedule").click(function(){
	$("#mailchimp_campaign_send_now").slideUp();
	$("#mailchimp_campaign_schedule_date").slideDown();
	$("#mailchimp_campaign_cancel_schedule").slideDown();
    });

    $("#mailchimp_campaign_cancel_schedule").click(function(){
	$("#mailchimp_campaign_send_now").slideDown();
	$("#mailchimp_campaign_schedule_date").slideUp();
	$("#mailchimp_campaign_cancel_schedule").slideUp();
    });

    $("[name='mailchimp_campaign_design_type']").click(function(){
	var value = $("[name='mailchimp_campaign_design_type']:checked").val();
	if (value != 'own')
	{
	    $("#mailchimp_campaign_design_list").html("");
	    $("#mailchimp_campaign_design_load").slideDown();
	    $("#mailchimp_campaign_design_list").load("../modules/mailchimp/ajax/template.php?lang="+id_lang+"&type="+value, function(result){
		$("#mailchimp_campaign_design_load").slideUp();
		$("#mailchimp_campaign_design_list").slideDown();
	    });
	}
	else
	{
	    $("#mailchimp_campaign_design_list").slideUp();
	    $("#mailchimp_campaign_design_box").slideDown();
	    tinyMCE.execCommand('mceFocus', true, 'mailchimp_campaign_design_code');
	    tinyMCE.execCommand('mceSetContent', true, '');
	}
    });

    $(".campaign_stat_tr").live('click', function(){
	$(this).parent().parent().next().slideToggle('fast');
    });

});

function getCurrentStep()
{
    for (var i = 1; i <= 6; i++)
    {
	if ($("#content_campaign").children("div:nth-child("+i+")").is(':visible'))
	    return (i);
    }
}

function setCurrentStep(s)
{
    for (var i = 1; i < s; i++)
    {
	$("#content_campaign").children("div:nth-child("+i+")").slideUp();
	$("#mailchimp_campaign_creation_title").next().children('li:nth-child('+i+')').attr('class', 'step_done');
	$("#mailchimp_campaign_creation_title").next().children('li:nth-child('+i+')').css('text-decoration', 'underline');
	$("#mailchimp_campaign_creation_title").next().children('li:nth-child('+i+')').css('cursor', 'pointer');
    }
    $("#content_campaign").children("div:nth-child("+s+")").slideDown();
    $("#mailchimp_campaign_creation_title").next().children('li:nth-child('+s+')').attr('class', 'step_current')
    $("#mailchimp_campaign_creation_title").next().children('li:nth-child('+s+')').css('text-decoration', 'none');
    for (var j = s + 1; j <= 6; j++)
    {
	$("#content_campaign").children("div:nth-child("+j+")").slideUp();
	$("#mailchimp_campaign_creation_title").next().children('li:nth-child('+j+')').attr('class', 'step_todo');
	$("#mailchimp_campaign_creation_title").next().children('li:nth-child('+j+')').css('text-decoration', 'none');
	$("#mailchimp_campaign_creation_title").next().children('li:nth-child('+j+')').css('cursor', 'auto');

    }
    if (s == 4)
	tinyMCE.execCommand('mceFocus', true, 'mailchimp_campaign_design_code');
}

function changeStateStep(s)
{
    var n = s + 1;
    if (s == 1)
	n = 3;
    if (s == 3 && $("#mailchimp_campaign_type").val() == 'PLAIN')
	n = 5;
    else if (s == 3 && $("#mailchimp_campaign_type").val() == 'REGULAR')
	tinyMCE.execCommand('mceFocus', true, 'mailchimp_campaign_design_code');
    else if (n == 6)
    	$("#mailchimp_campaign_result_create").html('');

    $("#content_campaign").children("div:nth-child("+s+")").slideUp();
    $("#content_campaign").children("div:nth-child("+n+")").slideDown();

    $("#mailchimp_campaign_creation_title").next().children('li:nth-child('+s+')').attr('class', 'step_done');
    $("#mailchimp_campaign_creation_title").next().children('li:nth-child('+s+')').css('text-decoration', 'underline');
    $("#mailchimp_campaign_creation_title").next().children('li:nth-child('+s+')').css('cursor', 'pointer');
    $("#mailchimp_campaign_creation_title").next().children('li:nth-child('+n+')').attr('class', 'step_current');

}

function getCodeTemplate(id, type)
{
    $.get("../modules/mailchimp/ajax/templateInfo.php?lang="+id_lang+"&id="+id+"&type="+type, function(result){
	tinyMCE.execCommand('mceFocus', true, 'mailchimp_campaign_design_code');
	tinyMCE.execCommand('mceSetContent', true, '');
	tinyMCE.execCommand('mceInsertContent', true, result);
	$("#mailchimp_campaign_design_box").slideDown();
    });
}


function campaignInformation()
{
    var api = $("#mailchimp_campaign_api").val();
    var token = $("#mailchimp_configuration_token").val();
    $("#mailchimp_campaign_list_info").load("../modules/mailchimp/ajax/campaignInfo.php?lang="+id_lang+"&api="+api+"&token="+token);
}

function deleteCampaign(id)
{
    if (confirm("Are you sure you want to delete your campaign ?"))
    {
	var api = $("#mailchimp_campaign_api").val();
	$.get("../modules/mailchimp/ajax/deleteCampaign.php?lang="+id_lang+"&api="+api+"&id="+id);
	campaignInformation();
	$("#tr_campaign_"+id).hide();
    }
}

function sendCampaign(id)
{
    if (confirm("Are you sure you want to send your campaign now ?"))
    {
	var api = $("#mailchimp_campaign_api").val();
	$.get("../modules/mailchimp/ajax/sendCampaign.php?lang="+id_lang+"&api="+api+"&id="+id);
	window.setTimeout(
	    function()
	    {
		campaignInformation();
	    },
	    1000
	);
    }
}

function replicateCampaign(id)
{
    var api = $("#mailchimp_campaign_api").val();
    $.get("../modules/mailchimp/ajax/replicateCampaign.php?lang="+id_lang+"&api="+api+"&id="+id);
    window.setTimeout(
	function()
	{
	    campaignInformation();
	},
	1000
    );
}

function scheduleCampaign(id)
{
    $("#mailchimp_campaign_send_button_"+id).hide();
    $("#tr_campaign_"+id).next().show();
}

function unscheduleCampaign(id)
{
    var api = $("#mailchimp_campaign_api").val();
    $.get("../modules/mailchimp/ajax/unscheduleCampaign.php?lang="+id_lang+"&api="+api+"&id="+id);
    campaignInformation();
}

function cancelScheduleCampaign(id)
{
    $("#mailchimp_campaign_send_button_"+id).show();
    $("#tr_campaign_"+id).next().hide();
}

function sendScheduleCampaign(id)
{
    var api = $("#mailchimp_campaign_api").val();

    var day = $("#mailchimp_campaign_schedule_day_"+id).val();
    var month = $("#mailchimp_campaign_schedule_month_"+id).val();
    var year = $("#mailchimp_campaign_schedule_year_"+id).val();

    var period = $("#mailchimp_campaign_schedule_period_"+id).val();
    var hour = $("#mailchimp_campaign_schedule_hour_"+id).val();
    var min = $("#mailchimp_campaign_schedule_min_"+id).val();

    if (period == 'PM')
	hour = parseInt(hour) + 12;

    var schedule = year+'-'+month+'-'+day+' '+hour+':'+min+':00';

    $.get("../modules/mailchimp/ajax/scheduleCampaign.php?lang="+id_lang+"&api="+api+"&id="+id+"&schedule="+schedule);
    window.setTimeout(
	function()
	{
	    campaignInformation();
	},
	1000
    );
}

function campaignStat()
{
    var api = $("#mailchimp_campaign_api").val();
    var token = $("#mailchimp_configuration_token").val();
    $("#mailchimp_campaign_stat_load").show();
    $("#mailchimp_campaign_stat_table").load("../modules/mailchimp/ajax/campaignStat.php?lang="+id_lang+"&api="+api+"&stat&token="+token, function(result)
					     {
						 $("#mailchimp_campaign_stat_load").hide();
					     });
}


function updateCampaign(id)
{
    var api = $("#mailchimp_campaign_api").val();
    $.get("../modules/mailchimp/ajax/campaignContent.php?lang="+id_lang+"&campaign&api="+api+"&id="+id, function(result){
	obj = $.parseJSON(result);

	if (obj.error_campaign != 'undefined')
	{
	    if (obj.type == 'plaintext')
	    {
		$("#mailchimp_campaign_type").val('PLAIN');
		$("#mailchimp_campaign_creation_title").next().children("li:nth-child(4)").hide();
		$("#mailchimp_campaign_creation_title").next().children("li:nth-child(2)").hide();
	    }
	    else
	    {
		$("#mailchimp_campaign_creation_title").next().children("li:nth-child(4)").show();
		$("#mailchimp_campaign_creation_title").next().children("li:nth-child(2)").hide();
		$("#mailchimp_campaign_type").val('REGULAR');
	    }
	    $("#mailchimp_campaign_update_title_span").html(obj.title);
	    $("#mailchimp_campaign_title").val(obj.title);
	    $("#mailchimp_campaign_list").val(obj.list_id);
	    $("#mailchimp_campaign_subject").val(obj.subject);
	    $("#mailchimp_campaign_from_email").val(obj.from_email);
	    $("#mailchimp_campaign_from_name").val(obj.from_name);
	    $("#mailchimp_campaign_reply_to").val(obj.to_name);
	}
    });
    $.get("../modules/mailchimp/ajax/campaignContent.php?lang="+id_lang+"&content&api="+api+"&id="+id, function(result){
	tinyMCE.execCommand('mceFocus', true, 'mailchimp_campaign_design_code');
	tinyMCE.execCommand('mceSetContent', true, '');
	tinyMCE.execCommand('mceInsertContent', true, result);
	$("#mailchimp_campaign_design_box").slideDown();
    });

    $.get("../modules/mailchimp/ajax/campaignContent.php?lang="+id_lang+"&text&api="+api+"&id="+id, function(result){
	$('#mailchimp_campaign_plain_text').val(result);
    });

    $("#mailchimp_campaign_update_id").val(id);
    $("#mailchimp_campaign_update").show();
    $("#mailchimp_campaign_create").hide();

    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(1)").hide();

    $(".menuTabButton.selected").removeClass("selected");
    $("#menuTab5").addClass("selected");
    $(".tabItem.selected").removeClass("selected");
    $("#menuTab5Sheet").addClass("selected");

    $("#mailchimp_campaign_creation_title").hide();
    $("#mailchimp_campaign_update_title").show();

    setCurrentStep(3);
}

function createMode()
{
    $("#mailchimp_campaign_creation_title").show();
    $("#mailchimp_campaign_update_title").hide();

    $("#mailchimp_campaign_creation_title").next().children("li:nth-child(1)").show();
    $("#mailchimp_campaign_update").hide();
    $("#mailchimp_campaign_create").show();

    $("#mailchimp_campaign_title").val("");
    $("#mailchimp_campaign_list").val("");
    $("#mailchimp_campaign_subject").val("");
    $("#mailchimp_campaign_from_email").val("");
    $("#mailchimp_campaign_from_name").val("");
    $("#mailchimp_campaign_reply_to").val("");
    $('#mailchimp_campaign_plain_text').val("");
    $('#mailchimp_campaign_type').val("");
    tinyMCE.execCommand('mceFocus', true, 'mailchimp_campaign_design_code');
    tinyMCE.execCommand('mceSetContent', true, '');
    $("#mailchimp_campaign_design_box").hide();

    setCurrentStep(1);
}
