<?php

/* 

File name: emailInjector.php
Purpose: Sample test email injector.  It is hardcoded to only send emails when the picked ESP is SparkPost

*/


ini_set("auto_detect_line_endings", true);
include "controller.php";

function confirmedSend(&$responseText, &$serverResponse)
{
	$payload = buildPayload();
	$serverResponse .= "<br>" . sendScheduleCampaign($payload);

}

function buildPayload()
{
	global $key, $template, $recipients, $now, $date, $hour, $minutes, $tz, $campaign, $returnpath, $open, $click, $email; 
	global $jsonLoad, $globalsub, $recipientCount;
	global $recDisplay, $ipPool;

	//
	//Build the payload for the Transmission API call
	//
	$transmissionLoad = '{"options": { "open_tracking" :';
	if ($open == "T") $transmissionLoad .= 'true, "click_tracking" : '; else $transmissionLoad .= 'false, "click_tracking" : ';
	if ($click == "T") $transmissionLoad .= 'true, "start_time" : '; else $transmissionLoad .= 'false, "start_time" : ';
	//if ($click == "T") $transmissionLoad .= 'true, "smartsend" : '; else $transmissionLoad .= 'false, "smartsend" : ';
	//if ($smartsent == "T") $transmissionLoad .= 'true, "start_time" : '; else $transmissionLoad .= 'false, "start_time" : ';
	if (!empty($date)) $transmissionLoad .= '"' . $date . 'T' . $hour . ':' . $minutes . ':00' . $tz . '" '; else $transmissionLoad .= '"now" ';
	if (!empty($ipPool)) $transmissionLoad .= ', "ip_pool" : "' . $ipPool . '"},'; else $transmissionLoad .= '},';
	$transmissionLoad .= '"content" : {"template_id" : "' . $template . '","use_draft_template" : false  },';
	$transmissionLoad .= '"campaign_id" : "' . $campaign . '", ';
	if ($returnpath != "") $transmissionLoad .= '"return_path" : "' . $returnpath . '", ';
	$transmissionLoad .= '"description" : "' . $recDisplay . 'controller",';
	$transmissionLoad .= $jsonLoad;
	$transmissionLoad .= "}";
	//echo $transmissionLoad;
	return $transmissionLoad;
	
}

function sendScheduleCampaign($transmissionLoad)
{
	//
	// Schedule/Send the campaign
	//
	global $apikey, $apiroot;

	$curl = curl_init();
	$url = $apiroot . "/transmissions";
	curl_setopt_array($curl, array(
  	CURLOPT_URL => $url,
  	CURLOPT_RETURNTRANSFER => true,
  	CURLOPT_ENCODING => "",
  	CURLOPT_MAXREDIRS => 10,
  	CURLOPT_TIMEOUT => 30,
  	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  	CURLOPT_CUSTOMREQUEST => "POST",
  	CURLOPT_POSTFIELDS => "$transmissionLoad",
  	CURLOPT_HTTPHEADER => array(
    "authorization: $apikey",
    "cache-control: no-cache",
    "content-type: application/json",
  	),));

	$response = curl_exec($curl);
	$err      = curl_error($curl);
	curl_close($curl);

	if ($err) 
	{
  		echo "cURL Error #:" . $err;
	} 
	else 
	{
  		// nada
	}
	return $response;

}
//
// Get values entered by user
//
$runner = 0;
while ($runner < 1000)
{
	$amount = rand (1,100);
	$ESP = NULL;
	$useIPPool = NULL;
	$variableInput = NULL;
	$categories = ["alerts", "newsletters", "oldstuff", "open", "passwords"];
	$randomCategory = rand (0,4);  //randomly pick a category/segment

	$branch = rand (1,100);  // Randomly pick if we test looking for IP Pools being warmed up or go directly to warmed pools
	if ($branch < 50)
	{
		echo "\nRequested: " . $amount . " for category, " . $categories[$randomCategory] . " from warmup and was alloted: ";
		getESPTargetWarmup ($categories[$randomCategory], $amount, $ESP, $useIPPool, $variableInput);
		echo $amount . " using " . $ESP . " " . $useIPPool . " " . $variableInput . "\n";
	}
	else
	{
		echo "\nRequested: " . $amount . " for category, " . $categories[$randomCategory] . " from balance and was told to send through: ";
		getESPTargetBalance ($categories[$randomCategory], $ESP, $useIPPool, $variableInput);
		echo $ESP . " " . $useIPPool . " " . $variableInput . "\n";
	}

	if ($ESP == "sparkpost")
	{
		$runner++;
		$amount = 1;
		$apikey = "<apikey>";
		$apiroot = "https://demo.api.e.sparkpost.com/api/v1";
		$template = "welcome-letter";
		$recipients = "jeff.goldstein@sparkpost.com.sink.sparkpostmail.com";
		$now = true;
		$campaign = "sink";
		$returnpath = "noreply@burningbarns.com";
		$open = true;
		$click = true;
		$smartsend = true;
		$email = "noreply@mail.geekwithapersonality.com";
		if ($useIPPool == 'x') $ipPool = ""; else $ipPool = $useIPPool;
		$jsonLoad  = '"recipients" : [{"address" : {"email" : "jeff.goldstein@sparkpost.com.sink.sparkpostmail.com"},"substitution_data":{"firstname" : "jeff"}}]';
		$globalsub  = "";
		$responseText = NULL;
		$serverResponse = NULL;
		$recDisplay = $recipientCount;

		confirmedSend($responseText, $serverResponse);
	}
}
?>
