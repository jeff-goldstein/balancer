<?php
/*
File: getDown.php
Usage:  This file used in order to obtain which ESP's are down at the current time.
Called by: manager.php.  This process is called in a loop via a setInterval function in order to keep the list up to date.
Reliant on: parameters.ini
			down
*/
$items = null;
$parametersFile = "parameters.ini";
$paramonly_array = parse_ini_file( $parametersFile, true );
$cat = "down";
$downFileName = $paramonly_array[$cat]["file"];

$down = file_get_contents($downFileName);
$matches=explode("\n",$down);
foreach ($matches as $row)
{
	$items =explode(",",$row);
	if ($items[0])  //skip blank lines
	{ 
		if ($items[1]) $list .= trim($items[0]) . "(" . trim($items[1]) . "), "; 
		else $list .= trim($items[0]) . "(no pool), ";
	}
}
$list = rtrim($list, ", ");
echo $list;
?>