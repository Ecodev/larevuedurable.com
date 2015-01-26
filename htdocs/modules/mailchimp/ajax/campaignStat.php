<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

/** 
 * script that return an html code of the stat of all the campaigns
 * 
 * 
 * 
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
  return ($myDateTime->format('Y-m-d H:i:s'));
}

/*for security reason, if the API key does not correspond to the one stored in Database, we die an error*/
$api = $_GET['api'];
if ($api != Configuration::get('MAILCHIMP_API_KEY'))
  die('<span style="color:red">'.$mailchimp->l('Wrong Api key', 'campaignStat', $id_lang).'</span>');

$mailChimp_api = new MCAPI(Configuration::get('MAILCHIMP_API_KEY'));
$res = $mailChimp_api->campaigns(array('status' => 'sent'));

echo '<div style="overflow:auto">';


if ($mailChimp_api->errorCode)
  echo '<span style="color:red">'.$mailChimp_api->errorMessage.'</span>';
else if ($res['total'] > 0)
{
	echo '<style>
    .list_campaign_stat li
    {
list-style-type:none;
font-size:15px;
height:17px;
    }
    .list_campaign_stat
{
margin-left:0px;
margin-right:25px;
}
</style>';
	echo '<table class="table" style="width:100%">';
	echo '<tr><th><input type="button" class="button" value="'.$mailchimp->l('Reload', 'campaignStat', $id_lang).'" onclick="campaignStat();" /></th>
<th>'.$mailchimp->l('Title', 'campaignStat', $id_lang).'</th>
<th>'.$mailchimp->l('Send Time', 'campaignStat', $id_lang).'</th>
<th>'.$mailchimp->l('Bounces', 'campaignStat', $id_lang).'</th>
<th>'.$mailchimp->l('Opens', 'campaignStat', $id_lang).'</th>
<th>'.$mailchimp->l('E-mails', 'campaignStat', $id_lang).'</th>
</tr>';
	$i = 0;
	foreach ($res['data'] as $val)
	{
		$stat = $mailChimp_api->campaignStats($val['id']);
		if ($mailChimp_api->errorCode)
			echo '<tr><td><span style="color:red">'.$mailChimp_api->errorMessage.'</span></td></tr>';
		else
		{
			echo '<tr '.($i % 2 == 0 ? 'class="alt_row"': '').'>';
			echo '<td><input type="button" class="button campaign_stat_tr" value="'.$mailchimp->l('View', 'campaignStat', $id_lang).'" /></td>
<td>'.Tools::safeOutput($val['title']).'</td>
<td>'.convertGmtToTimeZone($val['send_time']).'</td>
<td>'.((int)$stat['hard_bounces'] + (int)$stat['soft_bounces']).'</td>
<td>'.(int)$stat['opens'].'</td>
<td>'.$stat['emails_sent'].'</td>';
			echo '</tr>
<tr style="display:none" '.($i % 2 == 0 ? 'class="alt_row"': '').'>
<td colspan="6">
<div style="float:left">
<img src="../modules/mailchimp/img/email_error.png" alt="invalid E-mail" style="width:35px; float:left;" />
<ul class="list_campaign_stat">
<li>'.$mailchimp->l('Invalid E-mail', 'campaignStat', $id_lang).': '.(int)$stat['syntax_errors'].'</li>
<li>'.$mailchimp->l('Bounced', 'campaignStat', $id_lang).' : '.((int)$stat['hard_bounces'] + (int)$stat['soft_bounces']).'</li>
<li>'.$mailchimp->l('Unsubscribers', 'campaignStat', $id_lang).': '.(int)$stat['unsubscribes'].'</li>
<li>'.$mailchimp->l('Abuse reports', 'campaignStat', $id_lang).': '.(int)$stat['abuse_reports'].'</li>
</ul>
</div>
<div style="float:left">
<img src="../modules/mailchimp/img/email_open.png" alt="E-mail open" style="width:35px; float:left" />
<ul class="list_campaign_stat">
<li>Your newsletter was forwarded '.(int)$stat['forwards'].' time'.((int)$stat['forwards'] > 1 || (int)$stat['forwards'] == 0 ? 's' : '').' and '.(int)$stat['forwards_opens'].' link'.((int)$stat['forwards'] > 1 || (int)$stat['forwards'] == 0 ? 's' : '').' '.((int)$stat['forwards_opens'] > 1 || (int)$stat['forwards'] == 0 ? 'were' : 'was').' opened.</li>
<li>'.(convertGmtToTimeZone($stat['last_open'])  == '' ? 'Today' : convertGmtToTimeZone($stat['last_open'])).', your Newsletter was opened '.(int)$stat['opens'].' time'.((int)$stat['opens'] > 1 ? 's' : '').' by '.(int)$stat['unique_opens'].' '.((int)$stat['unique_opens'] > 1 || (int)$stat['forwards'] == 0 ? 'people' : 'person').'.</li>
<li>'.(convertGmtToTimeZone($stat['last_click']) == '' ? 'Today' : convertGmtToTimeZone($stat['last_click'])).', '.(int)$stat['clicks'].' link'.((int)$stat['forwards'] > 1 || (int)$stat['forwards'] == 0 ? 's' : '').' '.((int)$stat['forwards_opens'] > 1 || (int)$stat['forwards'] == 0 ? 'were' : 'was').' clicked by '.(int)$stat['users_who_clicked'].' '.((int)$stat['users_who_clicked'] > 1 || (int)$stat['forwards'] == 0? 'people' : 'person').'.</li>
</ul>
</div>
<div style="float:left">
<img src="../modules/mailchimp/img/facebook.png" alt="facebook" style="width:35px; float:left; margin-right:5px" />
<ul class="list_campaign_stat">
<li>'.$mailchimp->l('Facebook likes', 'campaignStat', $id_lang).' : '.(int)$stat['facebook_likes'].'</li>
</ul>
</div>
</td>
</tr>';
			$i++;
		}
	}
	echo'</table>';
}
else
{
	echo $mailchimp->l('You don\'t have any Campaigns sent.', 'campaignStat', $id_lang);
}
echo '</div>';
?>