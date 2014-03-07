<?php

	/*
	 * Creator   : WDXperience SARL : YM (121008)
	 * Copyright : All Right Reserved - Licence available for 1 shop
	 * Licence   : Prices and Conditions on http://www.wdxperience.ch/shop/
	 * Compat    : Prestashop v1.5
	 */

	include_once(dirname(__FILE__).'/../../config/config.inc.php');
	include_once(dirname(__FILE__).'/datatrans.php');


	//default check values
	$mycart_amount = 0;
	$debug_mode = false;
	$debug_message = "";
	$comment_status = "";
	$errors = "";
	$dts_transid = "";
	$moddts = new datatrans();


	//for support only : check existance of file
	if (! isset($_POST['uppTransactionId'])){
		printf("WDXPAY DTS | %s | %s | %s | ", $moddts->version, $moddts->release, _PS_VERSION_);
		exit;
	}

	switch ($_POST['status']) {
		case "success":

			$debug_message .= "\nStatus code is 'success'";

			$dts_merchand = $_POST['merchantId'];
			$dts_transid = $_POST['uppTransactionId'];
			$dts_acceptance = $_POST['acqAuthorizationCode'];
			$dts_message = $_POST['responseMessage'];
			$dts_orderID = $_POST['refno'];
			$dts_amount = $_POST['amount'];						// ! price * 100
			$dts_currency = $_POST['currency'];
			$dts_pm = $_POST['pmethod'];
			$dts_status = $_POST['status'];
			$dts_shasign = $_POST['sign2'];

			/*$dts_splitted = explode("_", $_POST['plus']);
			$dts_cart_id = $dts_splitted[0];
			$dts_secure_key = $dts_splitted[1];
			$dts_shop_id = $dts_splitted[2];*/

			$dts_cart_id = $_POST['dtscart'];
			$dts_secure_key = $_POST['dtssecu'];
			$dts_shop_id = $_POST['dtsshop'];

			$dts_respcode = $_POST['responseCode'];


			//check order_id
			$mycart = new Cart($dts_cart_id);
			if (!$mycart->id){
				$errors .= $moddts->l('Cart not exists').': cartid='.$dts_cart_id.'\n';
			}elseif (Order::getOrderByCartId(intval($dts_cart_id))){
				$errors .= $moddts->l('Order not exists').'\n';
			}


			//check sign2
			if (empty($errors)){

				$passphrase = trim(Configuration::get('DATATRANS_PSP_SIGN2'));
				$signed = $moddts->signValue($passphrase, $dts_merchand, $dts_amount, $dts_currency, $dts_transid);
				$debug_message .= "\nmySign2=$signed";

				if ($dts_shasign <> $signed){
                    $errors .= 'SHA Key error  (computed = '.$signed.' | received = '.$dts_shasign.')\n';
				}
			}


			//build transaction comment
			$amount = $dts_amount / 100;
			$comment_status .= "Datatrans: $dts_message \n\ntrxID: $dts_transid \nstatus: $dts_status \nauth: $dts_acceptance";
			$comment_status .= " \nrefno: $dts_orderID \npm: $dts_pm \nrespcode: $dts_respcode \npayed: $dts_currency $amount";

			$payment_method = $moddts->displayName;

			//validate order
			if (empty($errors)){

				/*ALIAS*/
				if ($moddts->usealias == '1') {

					//store received alias
					$alias = isset($_POST['aliasCC']) && ! empty($_POST['aliasCC']) ? $_POST['aliasCC'] : '';
					if ( ! empty($alias)){
						$sql = "DELETE FROM "._DB_PREFIX_."datatrans_alias WHERE id_customer = '".(int)$mycart->id_customer."'";
						Db::getInstance()->Execute($sql);
						$sql = "INSERT INTO "._DB_PREFIX_."datatrans_alias (id_customer, alias) VALUES ('".(int)$mycart->id_customer."', '$alias')";
						Db::getInstance()->Execute($sql);
					}
				}

				//all conditions are satisfied to validate the Transaction
				$debug_message .= "\ntransaction ready for validation";
				$moddts->validate((int)$dts_cart_id,  Configuration::get('PS_OS_PAYMENT'), (float)$amount, $comment_status, $dts_secure_key, $dts_shop_id);
			}else{
				$debug_message .= "\ntransaction NOT ready for validation";
				if (strlen($comment_status) >0) $errors .= $comment_status;
				if ($dts_cart_id > 0) $moddts->validate((int)$dts_cart_id, Configuration::get('PS_OS_ERROR'), 0, $errors, $dts_secure_key, $dts_shop_id);
			}
			break;


		case "error":
			$debug_message .= "An error occurs during POST validation of transaction $dts_transid";
			$errors = "Reported if debug mode is on.";
			break;


		default:
			$debug_message .= "Transaction $dts_transid was canceled";
			$errors = "Reported if debug mode is on.";
			break;
	}


	/* ONLY FOR DEBUG MODE       */
	/* DO NOT CHANGE THESE LINES */
	/*
	logerr("debug");
	function logerr($add=""){
		global $errors;
		$params = "\n";
		foreach ($_POST AS $key => $value) {
			$params .= "\n".$key.' = '.urlencode(stripslashes($value));
		}
		$params .= "\n";
		mail("support@wdxperience.ch", "DATATRANS TEST", $add."\n".$params."\n".$errors);
	}
	/**/

?>