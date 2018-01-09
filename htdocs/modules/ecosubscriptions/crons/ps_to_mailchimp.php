#!/usr/bin/php
<?php

require_once(__DIR__ . '/../../../config/config.inc.php');
require_once(__DIR__ . "/../autoload.php");
require_once(_PS_MODULE_DIR_ . "/mailchimp/mailchimp.php");

$date = date(_DATE_FORMAT_);
$mailchimp = new Mailchimp();
$api  = new MCAPI(_MAILCHIMP_API_KEY_);

// inscriptions
$newsletterSub = Customer::getNewsletterSubscribers();
$vals = $api->listBatchSubscribe(_MC_NEWSLETTER_LIST_, $newsletterSub, false,true , false);
if ($api->errorCode)
{
	$message = $date . ' - MC IMPORT -  ';
	$message.= "Unable to Batch subscribe List! - Code = " . $api->errorCode . " - Msg = ".$api->errorMessage;

	echo $message . "\r\n";
	error_log($message, 3, $_SERVER['DOCUMENT_ROOT'] . '/../../logs/cron_log.txt');
}
else
{
	$message = $date . ' - MC IMPORT - Success Batch subscribe List!';
	echo $message . "\r\n";
	error_log($message . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/../../logs/cron_log.txt');
}

// désinscriptions
$newsletterUnsub = Customer::getNewsletterUnsubscribed();
$unsub = array();
foreach($newsletterUnsub as $s)
{
	array_push($unsub, $s['EMAIL']);
}

if( count($unsub) )
{
	$vals = $api->listBatchUnsubscribe(_MC_NEWSLETTER_LIST_, $unsub, true, false, false);

	if ($api->errorCode)
	{
		$message = $date . ' - MC IMPORT -  ';
		$message.= "Unable to Batch unsubscribe List! - Code = " . $api->errorCode . " - Msg = ".$api->errorMessage;

		echo $message . "\r\n";
		error_log($message . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/../../logs/cron_log.txt');
	}
	else
	{
		$message = $date . " - MC IMPORT -  Success Batch unsubscribe List!";

		echo $message . "\r\n";
		error_log($message . chr(10), 3, $_SERVER['DOCUMENT_ROOT'] . '/../../logs/cron_log.txt');
	}
}
