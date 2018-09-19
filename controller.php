<?php
/*
File: controller.php

This is the main library that will be consumed into any email service that needs to decide which ESP (ESP/IPPool) to use.
These functions do NOT send emails, they simply guide the service to which ESP/IPPool to use.  
	o getESPTargetWarmup is used to decide which warmup pool to use
	o getESPTargetBalance is used to decide which ESP to use that has already been warmed up and sending email on a normal basis 
	  without worrying about limiting the number of emails through that Pool.

If getESPTargetWarmup does NOT find a matching ESP due to all of the pools for that email stream exhausting its allotment or maybe 
that appropriate target ESP is currently down; getESPTargeWarmup will call getESPTargetBalance looking for an older warmup up
pool that can be used.

If the email service knows that it wants to use a ESP/Pool that is already warmed up, it can call the getESPTargetBalance function
directly.

Called by: any email service that needs to know which ESP to use.
*/

function getESPTargetWarmup ($stream, &$amount, &$ESP, &$ipPool, &$variableInput)
{
	$parametersFile = "parameters.ini";
	$paramonly_array = parse_ini_file( $parametersFile, true );
	$cat = "warmup";
	$WarmupTableName = $paramonly_array[$cat]["filePrefix"];
	$WarmupTableNamePostfix = $paramonly_array[$cat]["filePostfix"];
	$highperformance = $paramonly_array[$cat]["highperformance"];
	$cat = "down";
	$downFileName = $paramonly_array[$cat]["file"];
	$cat = "common";
	$timezone = $paramonly_array[$cat]["timezone"];
	date_default_timezone_set($timezone);
	$today = date("m/d/y");	
	$ESP = NULL; $ipPool = NULL; $fileContent = NULL;
	$downedESPs = file_get_contents($downFileName);
	$matches=explode("\n",$downedESPs);
    $downedESPIndex = 0; $itemCount = 0;
    foreach ($matches as $row)
    {
        $items = explode(",", $row);
        if ($items[0]) // Make sure we don't have an empty row
        {
            $downedArray[$downedESPIndex] = trim($items[0]) . trim($items[1]);
        }
        $downedESPIndex++;
    }
	/*
	Open the File Stream & decide which file to use if using concurrent number of files.  This must match the number of files being
	updated by the manager processes.
	
	Notice: The function is only checking one file out of a possible 'x' number of files.  This means that I may point the user to
	the ongoing pools when this file say's the warmup pools for that stream are full, BUT there is some room left from other files.
	Since they all tend to fill up in a fairly uniform fashion, I'm willing to simply default to the ongoing pools knowing the other
	files will fill up their allotment fairly soon anyway.  This also limits processing speed by not looping through multiple files
	and locking them looking for any allotment available. 
	*/ 
	$whichfile = rand (1,$highperformance);
	$thisfile .= $WarmupTableName . $whichfile . "." . $WarmupTableNamePostfix;
	$handle = fopen($thisfile,"r+");
	//Lock File, error if unable to lock
	if(flock($handle, LOCK_EX)) 
	{
    	// Typically fread's second parameter is a filesize read but
    	// it kept coming back short by 1 character frequently and fucks up the program
    	// My guess is the program may be to fast between grabbing and the system seeing the size, but it's
    	// not reliable enough to use so I'm adding a buffer;
    	$grab = filesize($thisfile) + 20;
    	$warmuptable = fread($handle, 2096); 
    	$matches=explode("\n", $warmuptable);
    	$itemCount = count(explode(",",$matches[0]));
		$rIndex = 0;
		foreach ($matches as $row)
		{
			$items = explode(",", $row);
			$rowDate = $items[11];
			$rowDate = date("m/d/y", strtotime($rowDate));
			if ($items[0]) // Make sure we don't have an empty row
			{
				 //we really only need to do this once for the loop below, but this is the only good location for the call
				$espPoolPair = $items[2] . $items[3];
				$pos = array_search($espPoolPair, $downedArray);
				if (($pos !== false) || ($items[4] != $stream || $rowDate > $today))  
				{
					// Add skip to the row instead of simply not adding this row into the array
					// because we need this row in the array to write the stats back to the file when done 
					//echo $pos . " " . $items[4] . " " . $rowDate . " " . $today . "\n";  //debugging
					$items[12] = "skip";
				}
				$fArray[$rIndex] = $items;
				$rIndex++;
			}
		}
		$current = 0; $looking = TRUE;
		while (($current < $rIndex) && $looking)
		{
			if (($fArray[$current][1] != "") && ($fArray[$current][12] != "skip")) // check against empty rows
			{
				$maxToday = $fArray[$current][5];
				$sentSoFarToday = $fArray[$current][6];
				$sentSoFarThisInterval = $fArray[$current][8];
				$maxSendThisHour = round($fArray[$current][5] / $fArray[$current][7]);
				if ($sentSoFarToday < $maxToday)
				{
					$amountLeftToday = $maxToday - $sentSoFarToday;
					// We still have some left in the pool for the day
					if ($maxSendThisHour > $sentSoFarThisInterval)
					{
						// We have not used up the pool for this Interval either
						// We can at least fulfill part of the order.
						$amountLeftInterval = $maxSendThisHour - $sentSoFarThisInterval;
						$maxLeft = min($amountLeftToday, $amountLeftInterval);
						if ($maxLeft <= $amount)
						{
							// We are only able to partially fill the request
							// we will tell the calling process of this new amount and let them
							// rerequest the missing amount.  During the rerequest, the order will
							// either be filled by another warmup pool or moved to the Ongoing pools
							$amount = $maxLeft;
						}
						$fArray[$current][8] = $fArray[$current][8] + $amount;
						$fArray[$current][6] = $fArray[$current][6] + $amount;
						
						$looking = FALSE;
						$ESP = $fArray[$current][2];
						$ipPool = $fArray[$current][3];
						$stream = $fArray[$current][4];
						$variableInput = $fArray[$current][10];
					}
				}
			}
			$current++;
		}
		if (!$ESP)
		{
			getESPTargetBalance ($stream, $ESP, $ipPool, $variableInput);
		}
		$current = 0; $looking = TRUE; 
		while (($current < $rIndex))
		{
			if ($fArray[$current][0] != "")
			{
				// I could loop this through each item after counting the number of items.  Hmmmmm.  This should be faster though.  More upkeep but just a smidge faster.
				//$fileContent .= $fArray[$current][0] . "," . $fArray[$current][1] . "," . $fArray[$current][2] . "," . $fArray[$current][3] . "," . $fArray[$current][4] .  "," . $fArray[$current][5] .  "," . $fArray[$current][6] . "," . $fArray[$current][7] .  "," . $fArray[$current][8] . "," . $fArray[$current][9] . "," . $fArray[$current][10] . "," . $fArray[$current][11] . "\n";
				for ($i = 0; $i <= $itemCount-1; $i++)
				{
					if ($i < $itemCount-1) $fileContent .= $fArray[$current][$i] . ",";
					else $fileContent .= $fArray[$current][$i] . PHP_EOL;
				}
			}
			$current++;
		}
    	ftruncate($handle, 0);    //Truncate the file to 0
    	rewind($handle);           //Set write pointer to beginning of file
    	fwrite($handle, $fileContent);    //Write the new Hit Count
    	flock($handle, LOCK_UN);    //Unlock File
	} 
	else 
	{
    	echo "Could not Lock File!";
	}
	//Close Stream
	fclose($handle);
}

function getESPTargetBalance ($stream, &$ESP, &$ipPool, &$variableInput)
{
//Open the File Stream
	$alldown = FALSE;
	$parametersFile = "parameters.ini";
	$paramonly_array = parse_ini_file( $parametersFile, true );
	$cat = "balancer";
	$BalancerTableName = $paramonly_array[$cat]["filePrefix"];
	$cat = "down";
	$downFileName = $paramonly_array[$cat]["file"];
	$cat = "common";
	$BalancerTableNamePostfix = $paramonly_array[$cat]["storagePostfix"];
	$timezone = $paramonly_array[$cat]["timezone"];
	date_default_timezone_set($timezone);
	$today = date("m/d/y");	

	$downedESPs = file_get_contents($downFileName);
	$matches=explode("\n",$downedESPs);
    $downedESPIndex = 0;
    foreach ($matches as $row)
    {
        $items = explode(",", $row);
        if ($items[0]) // Make sure we don't have an empty row
        {
            $downedArray[$downedESPIndex] = trim($items[0]) . trim($items[1]);
        }
        $downedESPIndex++;
    }

	$balancer = file_get_contents($BalancerTableName . "." . $BalancerTableNamePostfix);
    $matches=explode("\n",$balancer);
	$rIndex = 0;
	$percent = 0; $addThisPercent = 0;
	/*
	This foreach block is creating an array for each ESP that is currently not marked as 'down'
	Each good ESP entry will be given an upper and lower number that will be used for the
	Random number generator in order to balance the load as indicated in the Balancer.csv file
	*/
	foreach ($matches as $row)
	{
		if ($rIndex != 0) // Skip the first line, it's just a header;
		{
			list($UID, $esp, $ippool, $fileStream, $rowPercent, $startDate, $variableInput) = explode(",", $row);
			//echo $UID . "-". $esp. "-".  $ippool. "-".  $fileStream. "-".  $rowPercent. "-".  $startDate. "-".  $variableInput . "\n";
			if ($esp) // Make sure we don't have an empty row
			{
				$find = $esp . $ippool;
				$pos = array_search($find, $downedArray);
				if (($pos !== false) || ($fileStream != $stream) || ($startDate > $today))
				{
					// We either found a match for a downed ESP or it's not the right stream or it's not targetted to start yet
				}
				else
				{
					$lower = $percent +1; // This is the lower bound of the percent range
					$percent = $percent + $rowPercent; // Track the percentage range; this is our upper bound for the current row
					$upper = $percent;  // This is the upper bound of the percent range for the ESP entry
					$fArray[$rIndex] = [$esp, $ippool, $stream, $rowPercent, $lower, $upper, $variableInput];
					$rIndex++;
				}
			}
		}
		else $rIndex++;	
	}
	/* 
	Check to see if all the ESP's are down
 	
	The Random generator that decides which stream to use will be set to the total of all percentages for that stream.  If there is only
	one category/stream then what every percentage entered in fact will be used 100% of the time.  For example, if a stream has only one entry and the
	percentage is set to 50, the random generator will only pick numbers between 1 and 50.  In essence, that one row will always be picked.
	*/
	if ($rIndex == 0) $alldown = TRUE;
	$current = 0; 
	$looking = TRUE;
	$random = rand(1,$percent);
	/*
	Now find the ESP where the random number is between the lower/upper bounds
	*/
	while (($current < $rIndex) & $looking & !$alldown)
	{
		if ($fArray[$current][1] != "")
		{
			if ($random >= $fArray[$current][4] && $random <= $fArray[$current][5])
			{
				$looking = FALSE;
				$ESP = $fArray[$current][0];
				$ipPool = $fArray[$current][1];
				$variableInput = $fArray[$current][6];
			}
		}
		$current++;
	}
	if (!$ESP)
	{
		$ESP = "alldown";
		$amount = 0;
	}
	$current = 0; $looking = TRUE;
} 
?>