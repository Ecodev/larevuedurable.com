<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * 
 * Script that return all the state of all the campaigns
 * the customer will be able to delete, upload, schedule, send or replicate
 * a campaign.
 */

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/mailchimp/class/MCAPI.class.php");
require_once(_PS_MODULE_DIR_."/mailchimp/mailchimp.php");

$mailchimp = new Mailchimp();
$id_lang = (int)$_GET['lang'];

/** 
 * convertGmtToTimeZone
 * 
 * @param time 
 * the time that will be converted
 * @return the converted time in the correct time Zone
 */
function convertGmtToTimeZone($time)
{
  if (!$time)
    return '';
  $myDateTime = new DateTime($time, new DateTimeZone('GMT'));
  $myDateTime->setTimezone(new DateTimeZone(Configuration::get('PS_TIMEZONE')));
  return ($myDateTime->format('r'));
}

/*for security reason, if the API key does not correspond to the one stored in Database, we die an error*/
$api = $_GET['api'];
if ($api != Configuration::get('MAILCHIMP_API_KEY'))
  die('<span style="color:red">'.$mailchimp->l('Wrong Api key', 'campaignInfo', $id_lang).'</span>');

$mailChimp_api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
$res = $mailChimp_api->campaigns();

if ($mailChimp_api->errorCode)
  echo '<span style="color:red">'.$mailChimp_api->errorMessage.'</span>';
else if ($res['total'] > 0)
  {
    echo "<table class='table' style='width:100%'>";
    echo '<tr>
<th>'.$mailchimp->l('TITLE', 'campaignInfo', $id_lang).'</th>
<th>'.$mailchimp->l('SUBJECT', 'campaignInfo', $id_lang).'</th>
<th>'.$mailchimp->l('TYPE', 'campaignInfo', $id_lang).'</th>
<th>'.$mailchimp->l('STATUS', 'campaignInfo', $id_lang).'</th>
<th>'.$mailchimp->l('CREATED', 'campaignInfo', $id_lang).'</th>
<th></th>
</tr>';

    $i = 0;
    foreach ($res['data'] as $key => $value)
      {
	echo '<tr '.($i % 2 == 0 ? 'class="alt_row"': '').' id="tr_campaign_'.$value['id'].'">
<td>'.$value['title'].'</td>
<td>'.$value['subject'].'</td>
<td>'.$value['type'].'</td>
<td>'.$value['status'].'</td>
<td>'.convertGmtToTimeZone($value['create_time']).'</td>
<td><input type="button" class="button" value="'.$mailchimp->l('Delete', 'campaignInfo', $id_lang).'" onclick="deleteCampaign(\''.$value['id'].'\')"/> 
'.($value['status'] != 'schedule' && $value['status'] != 'sending' && $value['status'] != 'sent' ?
	'<input type="button" class="button" value="'.$mailchimp->l('Update', 'campaignInfo', $id_lang).'" onclick="updateCampaign(\''.$value['id'].'\')"/> <input type="button" class="button" value="'.$mailchimp->l('Send Now', 'campaignInfo', $id_lang).'" id="mailchimp_campaign_send_button_'.$value['id'].'" 
onclick="sendCampaign(\''.$value['id'].'\')"/> <input type="button" class="button" value="'.$mailchimp->l('Schedule', 'campaignInfo', $id_lang).'" onclick="scheduleCampaign(\''.$value['id'].'\')"/>': '').($value['status'] == 'schedule' ? '<input type="button" class="button" value="'.$mailchimp->l('Unschedule', 'campaignInfo', $id_lang).'" onclick="unscheduleCampaign(\''.$value['id'].'\')"/> <input type="button" class="button" value="'.$mailchimp->l('Update', 'campaignInfo', $id_lang).'" onclick="updateCampaign(\''.$value['id'].'\')"/>' : '').($value['status'] == 'sent' ? '<input type="button" class="button" value="'.$mailchimp->l('Replicate', 'campaignInfo', $id_lang).'" onclick="replicateCampaign(\''.$value['id'].'\')"/>': '').'</td>
</tr>
<tr '.($i % 2 == 0 ? 'class="alt_row"': '').' style="display:none"><td colspan="6">
<label>'.$mailchimp->l('Delivery date and time:', 'campaignInfo', $id_lang).'</label>
<div class="margin-form">
<input type="text" size="2" value="MM" id="mailchimp_campaign_schedule_month_'.$value['id'].'"/> / <input type="text" size="2" value="DD" id="mailchimp_campaign_schedule_day_'.$value['id'].'"/> / <input type="text" size="4" value="YYYY" id="mailchimp_campaign_schedule_year_'.$value['id'].'"/> <input type="text" size="2" value="HH" id="mailchimp_campaign_schedule_hour_'.$value['id'].'"/>:<input type="text" size="2" value="mm" id="mailchimp_campaign_schedule_min_'.$value['id'].'"/> <select id="mailchimp_campaign_schedule_period_'.$value['id'].'"><option value="AM">AM</option><option value="PM">PM</option></select>
</div>
<div class="margin-form">
<input type="button" class="button" value="'.$mailchimp->l('Cancel', 'campaignInfo', $id_lang).'" onclick="cancelScheduleCampaign(\''.$value['id'].'\')"/> <input type="button" class="button" value="'.$mailchimp->l('Send', 'campaignInfo', $id_lang).'" onclick="sendScheduleCampaign(\''.$value['id'].'\')"/>
</div></td></tr>';
	$i++;
      }
    echo '</table>';
  }
else
  {
    echo ''.$mailchimp->l('You don\'t have any Campaign. Please create a campaign', 'campaignInfo', $id_lang).' <a style="color:blue;text-decoration:underline" href="./index.php?tab=AdminModules&configure=mailchimp&token='.Tools::safeOutput($_GET['token']).'&tab_module=adminModules&module_name=mailchimp&id_tab=5">'.$mailchimp->l('here', 'campaignInfo', $id_lang).'</a>';
  }

?>