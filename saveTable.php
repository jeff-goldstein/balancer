<?php
{
/*
File: saveTable.php
Purpose: Save either the warmup or balancer table
Called By: manager.php
*/
	$table = $_POST["table"];
	$tableName = $_POST["tableName"];

	$parametersFile = "parameters.ini";
	$paramonly_array = parse_ini_file( $parametersFile, true );
	$cat = "warmup";
	$WarmupTableName = $paramonly_array[$cat]["filePrefix"];

	$cat = "balancer";
	$BalancerTableName = $paramonly_array[$cat]["filePrefix"];

	$cat = "common";
	$filePostfix = $paramonly_array[$cat]["storagePostfix"];

	$header = NULL;
	switch ($tableName)
	{
		case "WarmupTable" : 
			$fileName = $WarmupTableName . "." . $filePostfix;
			$header = "UID,Priority,ESP Name/Code,IP Pool Name,Stream,Start Date,Warmup Target,Starting Point,Interval,Custom,Todays Target,So Far,Warmup Speed,Pass Thru" . PHP_EOL;
			break;
		case "BalancerTable" :	
			$fileName = $BalancerTableName . "." . $filePostfix;
			$header = "UID,ESP Name/Code,IP Pool Name,Stream,Percentage,Start Date,Pass Thru" . PHP_EOL;
			break;
	}
	$table = $header . $table;
	file_put_contents($fileName, $table);
}
?>

