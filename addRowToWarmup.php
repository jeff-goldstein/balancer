<?php
/*
File: addRowToWarmup.php
Purpose: A new row/entry is added into the UI table.  This information is added to each WarmupTable
Called by: manager.php
*/

$priority = $_POST["priority"];
$UID = $_POST["UID"];
$esp = $_POST["esp"];
$ippool = $_POST["ippool"];
$stream = $_POST["stream"];
$target = $_POST["target"];
$starting = $_POST["starting"];
$passInterval = $_POST["passInterval"];
$speed = $_POST["speed"];
$passthru = $_POST["passthru"];
$startDate = $_POST["startDate"];
$tableID = $_POST["tableID"];

$parametersFile = "parameters.ini";
$paramonly_array = parse_ini_file( $parametersFile, true );
$cat = "warmup";
$warmupPrefix = $paramonly_array[$cat]["filePrefix"];
$warmupPostfix = $paramonly_array[$cat]["filePostfix"];
$highperformance = $paramonly_array[$cat]["highperformance"];
if (!$highperformance) $highperformance = 1;  //default to 1 if no entry is found in ini file

$fileIndex = 1;
while ($fileIndex <= $highperformance)
{
	$added = false;
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
		$newTrackerFile = null;
		foreach ($warmupRows as $row)
		{
			//echo $row;
			$items = explode(",", $row);
			if ($items[0]) //skip empty rows
			{	
				if ((intval($priority) <= intval($items[1])) && !$added)  //keep adding while priority of new row is higher than what is already in the file
				{
					$newTrackerFile .= $UID . "," . $priority . "," . $esp . "," . $ippool . "," . $stream . "," . $starting . ",0," . $passInterval . ",0," . $speed . "," . $passthru . "," . $startDate . PHP_EOL;
					$added = true;		
				}
				$itemCount = count($items);
				for ($i = 0; $i <= $itemCount-1; $i++)
				{
					if ($i < $itemCount-1) $newTrackerFile .= $items[$i] . ",";
					else $newTrackerFile .= $items[$i] . PHP_EOL;
				}
			}
		}
		if (!$added)  //This priority is a higher number than any priority already in the file; so add it to the end of the file
		{
			$newTrackerFile .= $UID . "," . $priority . "," . $esp . "," . $ippool . "," . $stream . "," . $starting . ",0," . $passInterval . ",0," . $speed . "," . $passthru . "," . $startDate . PHP_EOL;
		}
		ftruncate($handle, 0);    //Truncate the file to 0
    	rewind($handle);           //Set write pointer to beginning of file
    	fwrite($handle, $newTrackerFile);    //Write the new Hit Count
    	flock($handle, LOCK_UN);    //Unlock File
		//file_put_contents ($filename, $newTrackerFile, LOCK_EX);
		$fileIndex++;
		echo "good";  // This is used to tell the manager.php process to update the UI row with an 'old' flag so the data is treated as an old entry, not a new entry
	}
	else
	{
		echo "bad";
	}
}
?>