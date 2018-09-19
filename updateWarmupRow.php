<?php
/*
File: updateWarmupRow.php
Purpose: After a row is updated in the UI, each Warmup file needs to be updated with the latest changes, but keep it's current sending information
Called by: manager.php
*/
	
$newPriority = $_POST["priority"];
$newUID = $_POST["UID"];
$newEsp = $_POST["esp"];
$newIppool = $_POST["ippool"];
$newStream = $_POST["stream"];
$newTarget = $_POST["target"];
$newStarting = $_POST["starting"];
$newPassInterval = $_POST["passInterval"];
$newSpeed = $_POST["speed"];
$newPassthru = $_POST["passthru"];
$newStartDate = $_POST["startDate"];
	
$parametersFile = "parameters.ini";
$paramonly_array = parse_ini_file( $parametersFile, true );
$cat = "warmup";
$warmupPrefix = $paramonly_array[$cat]["filePrefix"];
$warmupPostfix = $paramonly_array[$cat]["filePostfix"];
$highperformance = $paramonly_array[$cat]["highperformance"];
if (!$highperformance) $highperformance = 1;  //default to 1 if no entry is found in ini file

$fileIndex = 1;
$newWarmupFile = "";
while ($fileIndex <= $highperformance)
{

	$filename = $warmupPrefix . $fileIndex . "." . $warmupPostfix;
	$handle = fopen($filename,"r+");
	//Lock File, error if unable to lock
	if(flock($handle, LOCK_EX)) 
	{

    	// Typically fread's second parameter is a filesize read but
    	// it kept coming back short by 1 character frequently and fucks up the program
    	// My guess is the program may be to fast between grabbing and the system seeing the size, but it's
    	// not reliable enough to use so I'm adding a buffer;
    	$grab = filesize($filename) + 20;
    	$warmup = fread($handle, $grab); 
		$warmupRows=explode("\n",$warmup);
		foreach ($warmupRows as $row)
		{
			$items = explode(",", $row);
			if ($newUID == $items[0])
			{
				$items[1] = $newPriority;
				$items[2] = $newEsp;
				$items[3] = $newIppool;
				$items[4] = $newStream;
				$newTarget = intval($newTarget / $highperformance);
				$newStarting = intval($newStarting / $highperformance);
				if ($newTarget < $items[5]) $items[5] = $newTarget;
				if ($newStarting > $items[5]) $items[5] = $newStarting;
				$items[7] = $newPassInterval;
				$items[9] = $newSpeed;
				$items[10] = $newPassthru;
				$items[11] = $newStartDate;
			}
			$itemCount = count($items);
			if ($items[0])
			{
				for ($i = 0; $i <= $itemCount-1; $i++)
				{
					if ($i < $itemCount-1) $newWarmupFile .= $items[$i] . ",";
					else $newWarmupFile .= $items[$i] . PHP_EOL;
				}
			}
		}
	}
 	ftruncate($handle, 0);    //Truncate the file to 0
    rewind($handle);           //Set write pointer to beginning of file
    fwrite($handle, $newWarmupFile);    //Write the new Hit Count
    flock($handle, LOCK_UN);    //Unlock File
	$fileIndex++;
}
?>