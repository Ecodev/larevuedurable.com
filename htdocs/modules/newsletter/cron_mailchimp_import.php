<?php

require_once(dirname(__FILE__).'/../../config/defines.inc.php');
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(_PS_MODULE_DIR_."/mailchimp/mailchimp.php");

$execution_date = date( _DATE_FORMAT_);
$date_now = $date_now->format(_DATE_FORMAT_SHORT_);
$mailchimp = new Mailchimp();
$api  = new MCAPI(_MAILCHIMP_API_KEY_);



// inscriptions
$newsletterSub = Customer::getNewsletterSubscribers();
$vals = $api->listBatchSubscribe(_MC_NEWSLETTER_LIST_, $newsletterSub, false,true , false);	
if ($api->errorCode)
{
	$message = '';
	$message.= "Unable to Batch subscribe List!\n";
	$message.= "\n\tCode=".$api->errorCode;
	$message.= "\n\tMsg=".$api->errorMessage;
	
	echo $message;
	error_log($message." ".date(_DATE_FORMAT_).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/log/_module_newsletter_cron_mailchimp_import_log.txt');
}
else
{
	$message = '';
	$message.= "Success Batch subscribe List!";	
	echo $message;
	error_log($message." ".date(_DATE_FORMAT_).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/log/_module_newsletter_cron_mailchimp_import_log.txt');

}




// désinscriptions
$newsletterUnsub = Customer::getNewsletterUnsubscribed();
$unsub = array();
foreach($newsletterUnsub as $s)
{
	array_push($unsub, $s['EMAIL']);
}

if( sizeof($unsub) )
{

	$vals = $api->listBatchUnsubscribe(_MC_NEWSLETTER_LIST_, $unsub, true, false, false);

	if ($api->errorCode)
	{
		$message = '';
		$message.= "Unable to Batch unsubscribe List!\n";
		$message.= "\n\tCode=".$api->errorCode;
		$message.= "\n\tMsg=".$api->errorMessage;
		
		echo $message;
		error_log($message." ".date(_DATE_FORMAT_).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/log/_module_newsletter_cron_mailchimp_import_log.txt');
	
	}
	else
	{
		$message = '';
		$message.= "Success Batch unsubscribe List!";
		echo $message;
		error_log($message." ".date(_DATE_FORMAT_).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/log/_module_newsletter_cron_mailchimp_import_log.txt');
	
	}

}






?>