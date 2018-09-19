<?php
/*
File: deleteEntry.php
Purpose:  Believe it or not, sometimes people remove data from the tables.  This process can delete data from either the warmup or the
balancer table(s).  When a row is being removed from the warmup table, the system will first attempt to remove the file from the Warmup CSV
file that is used by the UI.  If that works, then the program will cycle through each warmup tracker file.
Called by: manager.php
*/

function deleteRow($file, $UID)
{
/* Purpose: Delete the row with the matching $UID sent from manager.php.  I took this approach for simplicity instead of looking for each
row that had a checkmark.  If I took the approach of scanning the full UI table for checked rows, I would have either send the whole table
from manager.php to deleteEntry.php or create an array of all rows to be deleted.

Since manager.php was doing the loop through each row looking for checked rows for deletion, we can simply call this
application for each row. This simplifies the interaction between the two processes and removes the need to convert the html table down to
a structure that can be passed and consumed by this application.

Since speed isn't that big of an issue her and there probably won't be a lot of rows being deleted; calling this
program for each deletion didn't seem to be that big of a time waste.
*/

	$handle = fopen($file,"r+");
	//Lock File, error if unable to lock
	if(flock($handle, LOCK_EX)) 
	{
		$newFileString = "";
    	// Typically fread's second parameter is a filesize read but
    	// it kept coming back short by 1 character frequently and fucks up the program
    	// My guess is the program may be to fast between grabbing and the system seeing the size, but it's
    	// not reliable enough to use so I'm adding a buffer;
    	$grab = filesize($file) + 20;
    	$fullfile = fread($handle, $grab); 
    	$matches=explode("\n",$fullfile);
		$rIndex = 0;
		foreach ($matches as $row)
		{
			$rowString = "";
			$items = explode(",", $row);
			if (($items[0] != $UID) && ($items[0] != ""))
			{
				for ($i=0; $i<count($items); $i++)
				{
					$rowString .= $items[$i] . ",";
				}
				$rowString = rtrim($rowString,",") . PHP_EOL;
				$newFileString .= $rowString;
			}
		}

		ftruncate($handle, 0);    //Truncate the file to 0
    	rewind($handle);           //Set write pointer to beginning of file
    	fwrite($handle, $newFileString);    //Write the new Hit Count
    	flock($handle, LOCK_UN);    //Unlock File
    	return true;
    }
    else return false;
}

$tableID = $_POST["tableID"];
$UID = $_POST["UID"];  

if ($tableID == "WarmupTable") 
{
	$parametersFile = "parameters.ini";
	$paramonly_array = parse_ini_file( $parametersFile, true );
	$cat = "warmup";
	$warmupTableName = $paramonly_array[$cat]["filePrefix"];
	$warmupTableNamePostfix = $paramonly_array[$cat]["filePostfix"];
	$highperformance = $paramonly_array[$cat]["highperformance"];
	$cat = "common";
	$controlPostfix = $paramonly_array[$cat]["storagePostfix"];
	$file = $warmupTableName . '.' . $controlPostfix;
	$deleteControlRowFlag = deleteRow($file, $UID);

	if ($deleteControlRowFlag)
	{
		$deletetrackingRowFlag = true;
		for ($i=1; $i<=$highperformance; $i++)
		{
			if ($deletetrackingRowFlag)
			{
				$file = $warmupTableName . $i . '.' . $warmupTableNamePostfix;
				$deleteControlRowFlag = deleteRow($file, $UID);
			}
		}
	}
}

if ($tableID == "BalancerTable") 
{
	$parametersFile = "parameters.ini";
	$paramonly_array = parse_ini_file( $parametersFile, true );
	$cat = "balancer";
	$balancerTableName = $paramonly_array[$cat]["filePrefix"];
	$cat = "common";
	$controlPostfix = $paramonly_array[$cat]["storagePostfix"];
	$file = $balancerTableName . '.' . $controlPostfix;
	$deleteControlRowFlag = deleteRow($file, $UID);  
}
?>
