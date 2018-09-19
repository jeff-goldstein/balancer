<?php
{
/*
File: getCurrentWarmupSending.php
Purpose: This process simply gathers all the live data from each Warmup table and places them into a field that is then displayed in the
third tab of the main UI.  This process will be called when the UI is reloaded, then every couple of seconds through an Interval process.
Called by: manager.php
*/

	$WarmupTableName = "WarmupTable";
	$WarmupTableNamePostfix = ".txt";
	$currentFileCount = 0;
	date_default_timezone_set('America/Los_Angeles');
	$readableyesterday = date("F j, Y", time() - 60 * 60 * 24);
	$yesterday = strtotime($readableyesterday);
	$files = array(); $arrayOf = array();
	$rawcontent = NULL;
	if ($handle = opendir('.')) 
	{
		while (false !== ($file = readdir($handle))) 
		{
			if ((false !== strpos($file, $WarmupTableName)) && (false !== strpos($file, $WarmupTableNamePostfix)))
			{  
				$readableLastModified = date('F j, Y, H:i:s',filemtime($file));
				$lastModified = strtotime($readableLastModified);
				if ($lastModified > $yesterday)
				{
					$pos = strpos(".txt", $file);
					$filenum = (int)substr($file, 11, (strlen($file)-15));
					$arrayOf[] = [$filenum, $file, $readableLastModified, file_get_contents ($file)];
					$currentFileCount++;
				}        
			}
		}
	}
	sort($arrayOf);
	$index = 0;
	while ($index < $currentFileCount)
	{
		$sortedcontent .= "\n" . $arrayOf[$index][1] . ", Last Updated: " . $arrayOf[$index][2] . "\nUID,Priority,ESP,Pool,Max Daily Send,Sent Today,Custom Sending Rate,Sent This Period,Warmup Rate,Variable Thru Text, Start Date\n" . $arrayOf[$index][3] . "\n\n";    	
		$index++;
	}
	closedir($handle);
	echo $sortedcontent;
}
?>