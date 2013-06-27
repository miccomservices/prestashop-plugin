<?php
	
	include(dirname(__FILE__).'/../../config/config.inc.php');
	include(dirname(__FILE__).'/bitpay.php');
		
	$handle = fopen('php://input','r');
	$jsonInput = fgets($handle);
	bplog($jsonInput);
	$decoded = json_decode($jsonInput, true);
	fclose($handle);

	$posData = json_decode($decoded['posData']);
	bplog("inside ipn.php");
	if ($posData->hash == crypt($posData->cart_id, Configuration::get('bitpay_APIKEY')))
	{	
		$bitpay = new bitpay();		
		
		if (in_array($decoded['status'], array('confirmed', 'complete')))
		{
			if (empty(Context::getContext()->link))
				Context::getContext()->link = new Link(); // workaround a prestashop bug so email is sent 
			bplog("posData");
			bplog($posData);
			bplog("secure key");
			bplog($customer->secure_key);
			$bitpay->validateOrder($posData->cart_id, Configuration::get('PS_OS_PAYMENT'), $decoded['price'], $bitpay->displayName, null, array(), null, false, $customer->secure_key);
		}
		$bitpay->writeDetails($bitpay->currentOrder, $posData->cart_id, $decoded['id'], $decoded['status']);
		bplog("reached write");
	}
	else 
	{
		bplog('Hash does not match');
		bplog($posData);
	}