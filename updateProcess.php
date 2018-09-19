<?php
{

/*
File: updateProcess.php

Purpose: The Warmup Tracker tables need to be updated on a continual basis in order to make sure that table's pool limit is updated on both
a daily and possibly on an interval basis.  Each tracker files keeps track of what portion of the pool has been used compared to the interval
limit.  Intervals can be daily or hourly with a twist were the user can set how many hours they expect to send emails during a given day.
For example, a job board may be using a pool for their daily sends that typically happen from 2am-6am.  They may set the Interval to Custom
and the number of hours to 4 (five if they are thinking inclusive).  In order to make sure that to many emails aren't sent through that pool 
during that hour the UpdateProcess still needs to be updated on an hourly basis.
*/

function getCurrentSendingAmounts($WarmupTableName, $WarmupTableNamePostfix, $highperformance, &$totals)
{    
	$rIndex = 1;
    while ($rIndex <= $highperformance)
    {
      $content = file_get_contents ($WarmupTableName . $rIndex . "." . $WarmupTableNamePostfix);
      $matches=explode("\n",$content);
      foreach ($matches as $row)
      {
        $items = explode(",", $row);
        if ($items[0]) // Make sure we don't have an empty row
        {
          $key = $items[2] . $items[3];
          if ($key != "ESP Name/CodeIP Pool Name") $totals[$key] = $totals[$key] + $items[6];
        }
      }
      $rIndex++;
    }       
 }

function buildWarmupTables ($WarmupTableName, $tablePostfix, $highperformance, &$newControlFile, &$newTrackerFile, $totals, $timezone)
{
	date_default_timezone_set($timezone);
    $todaysDate = date("m/d/y");
	$warmup = file_get_contents($WarmupTableName . '.' . $tablePostfix);
	echo "\nOld Control Table\n" . $warmup;
 	$matches=explode("\n",$warmup);
	$newTrackerFile = null;
	$newControlfile = null;
	foreach ($matches as $row)
	{
		if ($rIndex != 0) // Skip the first line, it's just a header;
		{
			list($UID, $priority, $isp, $ippool, $fileStream, $startDate, $target, $start, $sendingInterval, $custom, $currentDailyTarget, $sofar, $warmupSpeed, $passThru) = explode(",", $row);
			if ($isp) // Make sure we don't have an empty row
			{
				$latestSofar = null;
				$newTarget = null;

				switch ($sendingInterval)
				{
					case "daily":
						$hours = 1;
						break;
					case "hourly":
						$hours = 24;
						break;
					case "custom":
						$hours = $custom;
						break; 
				}
				$key = $isp . $ippool;
				$latestSofar = $totals[$key];
				if (!$latestSofar) $latestSofar = 0;
				$newTrackerFile .= $UID . "," . $priority . "," . $isp . "," . $ippool . "," . $fileStream . ",";
				/*
				On the first day, this field is blank.  Lets set it to the the starting amount
				*/
				if (!$currentDailyTarget)  
				{
					$currentDailyTarget = $start;
				}
				$newTarget = round($latestSofar * $warmupSpeed);
				//echo $isp.$ippool.$currentDailyTarget."....".$newTarget;
				/*
					Let's make sure that we don't go backwards in our warmup plan.  If our new target of todays sending time
					the warmup speed is less than our target for the day; let's keep yesterdays target.
				*/
				//if ($newTarget < $currentDailyTarget) $newTarget = $currentDailyTarget;
				$newTarget = max($newTarget, $currentDailyTarget);
				$highperformanceTarget = intval($newTarget / $highperformance);
				//$highperformanceTarget = intval($highperformanceTarget);
				$newTrackerFile .= $highperformanceTarget . ",0," . $hours . ",0," . $warmupSpeed . "," . $passThru . "," . $startDate . PHP_EOL;
				$newTarget = intval($newTarget);
	
				$newControlFile .= $UID . "," . $priority . "," . $isp . "," . $ippool . "," . $fileStream . "," . $startDate . "," . $target . "," . $start . "," . $sendingInterval . "," . $custom . "," . $newTarget . ",0," . $warmupSpeed . "," . $passThru . PHP_EOL;
				$rIndex++;
			}
		}
		else $rIndex++;	
	}
}

function dailyRoutine()
{
	$parametersFile = "parameters.ini";
	$paramonly_array = parse_ini_file( $parametersFile, true );
	$cat = "warmup";
	$WarmupTableName = $paramonly_array[$cat]["filePrefix"];
	$WarmupTableNamePostfix = $paramonly_array[$cat]["filePostfix"];
	$highperformance = $paramonly_array[$cat]["highperformance"];
	if (!$highperformance) $highperformance = 1;  //default to 1 if no entry is found in ini file

	$cat = "common";
	$tablePostfix = $paramonly_array[$cat]["storagePostfix"];
	$timezone = $paramonly_array[$cat]["timezone"];

	$getCurrentSendingAmounts = array();
	$newControlfile = null;

	getCurrentSendingAmounts($WarmupTableName, $WarmupTableNamePostfix, $highperformance, $currentSending);
	//var_dump($currentSending);
	buildWarmupTables($WarmupTableName, $tablePostfix, $highperformance, $newControlFile, $newTrackerFile, $currentSending, $timezone);
	echo "\n New Tracker file:\n" . $newTrackerFile . "\nNew Control file\n" . $newControlFile;
	$createfileIndex = 1;
	while ($createfileIndex <= $highperformance)
	{
		$filename = $WarmupTableName . $createfileIndex . "." . $WarmupTableNamePostfix;
		file_put_contents ($filename, $newTrackerFile, LOCK_EX);
		$createfileIndex++;
	}

	$fileName = $WarmupTableName . "." . $tablePostfix;
	$header = "UID,Priority,ESP Name/Code,IP Pool Name,Stream,Start Date,Warmup Target,Starting Point,Interval,Custom,Todays Target,So Far,Warmup Speed,Pass Thru" . PHP_EOL;
	$table = $header . $newControlFile;
	file_put_contents($fileName, $table);
}

function hourlyRoutine()
{
	$parametersFile = "parameters.ini";
	$paramonly_array = parse_ini_file( $parametersFile, true );
	$cat = "warmup";
	$landscapePrefix = $paramonly_array[$cat]["filePrefix"];
	$landscapePostfix = $paramonly_array[$cat]["filePostfix"];
	$highperformance = $paramonly_array[$cat]["highperformance"];
	if (!$highperformance) $highperformance = 1;  //default to 1 if no entry is found in ini file

	$landscape = file_get_contents($landscapePrefix . '1.' . $landscapePostfix);
	$landscapeRows=explode("\n",$landscape);
	$newTrackerFile = null;
	foreach ($landscapeRows as $row)
	{
		$items = explode(",", $row);
		$itemCount = count($items);
		if ($items[0])
		{
			$items[8] = 0;
			for ($i = 0; $i <= $itemCount-1; $i++)
			{
				if ($i < $itemCount-1) $newTrackerFile .= $items[$i] . ",";
				else $newTrackerFile .= $items[$i] . PHP_EOL;
			}
			$rIndex++;
		}
		else $rIndex++;	
	}

	$createfileIndex = 1;
	while ($createfileIndex <= $highperformance)
	{
		$filename = $landscapePrefix . $createfileIndex . "." . $landscapePostfix;
		file_put_contents ($filename, $newTrackerFile, LOCK_EX);
		$createfileIndex++;
	}
}

/*
Code for directing which type of update will be run
*/
$parametersFile = "parameters.ini";
$paramonly_array = parse_ini_file( $parametersFile, true );
$cat = "common";
$recycleTime = $paramonly_array[$cat]["recycleTime"];
$timezone = $paramonly_array[$cat]["timezone"];
date_default_timezone_set($timezone);
$currentHour = date("H");
if ($currentHour == $recycleTime)
{
 	echo "daily";
 	dailyRoutine();
}
else
{
	echo "hourly";
 	hourlyRoutine();
}
}

?>