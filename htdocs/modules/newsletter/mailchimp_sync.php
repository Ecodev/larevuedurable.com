<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');

$message = date(_DATE_FORMAT_) . ' - MC Sync - ';
error_log($message.chr(10).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/logs/cron_log.txt');

$message = Tools::arrayToString($_POST);
error_log($message.chr(10).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/logs/cron_log.txt');

if (isset($_POST) && isset($_POST['type'])) {
	$type = $_POST['type'];

	// if subscribe
		//"type": "subscribe", 
		//"fired_at": "2009-03-26 21:35:57", 
		//"data[id]": "8a25ff1d98", 
		//"data[list_id]": "a6b5da1054",
		//"data[email]": "api@mailchimp.com", 
		//"data[email_type]": "html", 
		//"data[merges][EMAIL]": "api@mailchimp.com", 
		//"data[merges][FNAME]": "MailChimp", 
		//"data[merges][LNAME]": "API", 
		//"data[merges][INTERESTS]": "Group1,Group2", 
		//"data[ip_opt]": "10.20.10.30", 
		//"data[ip_signup]": "10.20.10.30"

	if ($type == 'subscribe') {
		$success = DB::getInstance()->update(
			'customer',
			array( 'newsletter' => 1 ),
			"email='".$_POST['data']['email']."'"
		);
		$success2 = DB::getInstance()->update(
			'newsletter',
			array('active' => 1),
			"email='".$_POST['data']['email']."'"
		);

		if (!$success || !$success2) {
			$message = 'subscribe fail for ' . $_POST['data']['email'];
		} else {
			$message = 'subscribe success for ' . $_POST['data']['email'];
		}
		
		error_log($message.chr(10).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/logs/cron_log.txt');
		exit();			
	}

	// if unsubscribe or cleaned
		//	"type": "unsubscribe", 
		//	"fired_at": "2009-03-26 21:40:57",  
		//	"data[action]": "unsub",
		//	"data[reason]": "manual", 
		//	"data[id]": "8a25ff1d98", 
		//	"data[list_id]": "a6b5da1054",
		//	"data[email]": "api+unsub@mailchimp.com", 
		//	"data[email_type]": "html", 
		//	"data[merges][EMAIL]": "api+unsub@mailchimp.com", 
		//	"data[merges][FNAME]": "MailChimp", 
		//	"data[merges][LNAME]": "API", 
		//	"data[merges][INTERESTS]": "Group1,Group2", 
		//	"data[ip_opt]": "10.20.10.30",
		//	"data[campaign_id]": "cb398d21d2",
		//	"data[reason]": "hard"

		//"type": "cleaned", 
		//"fired_at": "2009-03-26 22:01:00", 
		//"data[list_id]": "a6b5da1054",
		//"data[campaign_id]": "4fjk2ma9xd",
		//"data[reason]": "hard",
		//"data[email]": "api+cleaned@mailchimp.com"

	if ($type == 'unsubscribe' || $type = 'cleaned')
	{
		$success = DB::getInstance()->update(
			'customer',
			array('newsletter' => 0),
			"email='".$_POST['data']['email']."'"
		);
			
		$success2 = DB::getInstance()->update(
			'newsletter',
			array('active' => 0),
			"email='".$_POST['data']['email']."'"
		);

		if (!$success || !$success2) {
			$message = 'unsubscribe fail for ' . $_POST['data']['email'];
		} else {
			$message = 'unsubscribe success for ' . $_POST['data']['email'];
		}
		
		error_log($message.chr(10).chr(10), 3, $_SERVER['DOCUMENT_ROOT'].'/logs/cron_log.txt');
		exit();
	}
}
