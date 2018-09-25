<?php
{
/*
File: buildWarmUpTable.php
Purpose in life: To take the data from the Warmup Table and create an HTML table for the UI.  Unlike the Balancer table, this table need
input from other content; in this case, each of the live Warmup tables that are keeping track of how much email has been sent from each
warmup pool.  That information is merged with each ESP/IPPool combination in order to make the UI table.  The calling function from manager.php
will save the UI content down to the physical file.
Called By: manager.php
*/
  function getSendCountYesterday(&$totals, &$todaysTarget)
  {
    /* This approach sums up data from all Warmup files that can be found and have changed since yesterday.  This has an issue if the email injectors
    do not send on a daily basis.
    */

    //
    // This function is currently obsoleted but I decided to keep it around in case I (or someonelse) chooses this approach
    //

    date_default_timezone_set('America/Los_Angeles');
    $yesterday = date("F j, Y", time() - 60 * 60 * 24);
    $yesterday = strtotime($yesterday);
    $files = array();
    if ($handle = opendir('.')) 
    {
      while (false !== ($file = readdir($handle))) 
      {
        if ((false !== strpos($file, "WarmupTable")) && (false !== strpos($file, ".txt")))
        {  
          $lastModified = date('F j, Y, H:i:s',filemtime($file));
          $lastModified = strtotime($lastModified);
          if ($lastModified > $yesterday)
          {
            $content = file_get_contents ($file);
            $currentFileCount++;
            $matches=explode("\n",$content);
            $rIndex = 0;
            foreach ($matches as $row)
            {
              $items = explode(",", $row);
              if ($items[0]) // Make sure we don't have an empty row
              {
                $key = $items[2] . $items[3];
                if ($key != "ESP Name/CodeIP Pool Name") 
                {
                  $totals[$key] = $totals[$key] + $items[6];
                  $todaysTarget[$key] = $items[5];
                }
              }
              $rIndex++;
            }
          }        
        }
      }
    }
    closedir($handle);
  }

  function getSendCountParam(&$totals, &$todaysTarget)
  {
    /*
    Purpose: This approach uses the parameters file to know how many Warmup files to read.  This approach can show a low amount if the parameters file was changed
    from a lower number than before.  For example, if 9 files were used to keep track to guide the injectors for a very high performance send, but then
    the number was dropped down to 4; this approach will only read the first four files and not look at files 5-9.
    
    Called By: Main
    */
    $parametersFile = "parameters.ini";
    $paramonly_array = parse_ini_file( $parametersFile, true );
    $cat = "warmup";
    $WarmupTableName = $paramonly_array[$cat]["filePrefix"];
    $WarmupTableNamePostfix = $paramonly_array[$cat]["filePostfix"];
    $highperformance = $paramonly_array[$cat]["highperformance"];
    
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
          if ($key != "ESP Name/CodeIP Pool Name") 
          {
            $totals[$key] = $totals[$key] + $items[6];
            $todaysTarget[$key] = $items[5];
          }
        }
      }
      $rIndex++;
    }        
  }

  $totals = array();
  $todaysTargets = array();
  $count=0; $currentFileCount = 0;
  getSendCountParam($totals, $todaysTargets);

  echo "<tbody><tr>";
  echo "<th style='width:15px'><center><input unchecked type='checkbox' id='mastercheckw' name='mastercheckw' onchange='changeall(\"w\")'/></center></th>";
  echo "<th><center>Unique Row ID</center></th>";
  echo "<th><center>Priority*</center></th>";
  echo "<th><center>ESP Name/Code*</center></th>";
  echo "<th><center>IP Pool Name</center></th>";
  echo "<th><center>Stream*</center></th>";
  echo "<th><center>Start Date* (mm/dd/yy)</center></th>";
  echo "<th><center>Target*<br>(# Emails/Day)</center></th>";
  echo "<th><center>Starting Point* (Number of emails)</center></th>";
  echo "<th><center>Interval*</center></th>";
  echo "<th><center>Custom Hours<br>(0-24)</center></th>";
  echo "<th><center>Current Daily Target</center></th>";
  echo "<th><center>Amount Sent Today</sup</center></th>";
  echo "<th><center>Warmup Speed*<br>1.00 - 5.00</center></th>";
  echo "<th><center>Pass Thru Text</center></th>";
  echo "</tr><tr>";
  echo "<td style='width:15px'><input type='checkbox' readonly style='background-color: #7FDBFF;' unchecked hidden></input></td>";
  echo "<td><input readonly style='width:68px; background-color: #7FDBFF;' value=18299372></input></td>";
  echo "<td><input readonly style='width:68px; background-color: #7FDBFF;' value=1></input></td>";
  echo "<td><input readonly style='width:68px; background-color: #7FDBFF;' value = 'sparkpost'></td>";
  echo "<td><input readonly style='width:68px; background-color: #7FDBFF;' value = 'newsletters'></td>";
  echo "<td><input readonly style='width:68px; background-color: #7FDBFF;' value = 'newsletters'></td>";
  $newdate = date("m/d/y", strtotime("-4 days"));
  echo "<td><input readonly style='width:98px; background-color: #7FDBFF;' value = '" . $newdate . "'></td>";
  echo "<td><input readonly style='width:98px; background-color: #7FDBFF;' value = '6000000'></td>";
  echo "<td><input readonly style='width:98px; background-color: #7FDBFF;' value = '1600'></td>";
  echo "<td><select disabled style='background-color: #7FDBFF;'><option value='daily'>Daily</option><option value='hourly'>Hourly</option><option selected value='custom'>Custom Range</option></select></td>";
  echo "<td><input readonly style='width:68px; background-color: #7FDBFF;' value = '7'></td>";
  echo "<td><input readonly style='width:98px; background-color: #7FDBFF;' value = '25600'></td>";
  echo "<td><input readonly style='width:98px; background-color: #7FDBFF;' value = '23444'></td>";
  echo "<td><input readonly style='width:68px; background-color: #7FDBFF;' value = '2.0'></td>";
  echo "<td><input readonly style='width:130px; background-color: #7FDBFF;' value = 'Pass this text to injector'></td>";
  echo "<td hidden><input readonly style='width:1px; color:black;background-color: lightgray;' hidden value='old'></td>";
  $warmup = file_get_contents("WarmupTable.csv");
  $matches=explode("\n",$warmup);
  foreach ($matches as $row)
  {
    $items = explode(",", $row);
    if ($items[0] && $count > 0) // Make sure we don't have an empty row and we are not using the header row
    {
      $key = $items[2] . $items[3];
      $row =  "<tr>";
      $row .= "<td style='width:15px'><input type='checkbox' name='isSelectedw'></input></td>";
      $row .= "<td><input id='UID" . $count . "'readonly type='number' style='width:68px; color:black;background-color: lightgray;' title='This is a system generated number used by the system.' value=" . $items[0] . "></td>";
      $row .= "<td><input type=text id='txtPriority" . $count . "'required type='number' style='width:68px; color:black;background-color: lightgray;' title='The warmup process will use email pools with the highest priority first (1 being the highest), then move to the next pool once the lower pools are used for the given interval.' value=" . $items[1] . "></td>";
      $row .= "<td><input id='txtESP" . $count . "' name='txtESP' required onchange='checkESPPoolCombo()' style='width:68px; color:black;background-color: lightgray;' value='" . $items[2] . "'></td>";
      $row .= "<td><input id='txtippool" . $count . "' name='ippool' onchange='checkESPPoolCombo()' style='width:68px; color:black;background-color: lightgray;' value='" . $items[3] . "'></td>";
      $row .= "<td><input id='txtStream" . $count . "'required style='width:68px; color:black;background-color: lightgray;' value='" . $items[4] . "'></td>";
      $row .= "<td><input id='txtStartDate" . $count . "'required pattern='^(1[0-2]|0*[1-9]) ?[-\/] ?(0[1-9]|[1-2]?[0-9]|3[0-1]) ?[-\/] ?(1[8-9]|2[0-9])' style='width:98px; color:black;background-color: lightgray;' value='" . $items[5] . "'></td>";
      $row .= "<td><input id='txtTarget" . $count . "'required pattern='[0-9]*' style='width:98px; color:black;background-color: lightgray;' title='This is the number of emails being sent on a given day where you believe the pool is warmed up and ready to be used on a ongoing basis (see Ongoing Balancer tab).' value='" . $items[6] . "'></td>";
      $row .= "<td><input id='txtStartingPoint" . $count . "'required pattern='[0-9]*' style='width:98px; color:black;background-color: lightgray;' onchange='updateDailyTarget(" . $count . ")' title='This the number of emails this pool is allowed to send on the first day.  Daily calculations will continue from there using the previous days actual sending from that pool.' value='" . $items[7] . "'></td>";
      switch ($items[8]) 
      {
        case 'daily':
          $row .= "<td><select id='selectInterval" . $count . "'style='background-color: lightgray;' onchange='checkCustom(" . $count . ")'><option selected value='daily'>Daily</option><option value='hourly'>Hourly</option><option value='custom'>Custom Range</option></select></td>";
          $row .= "<td><input readonly id='txtCustInterval" . $count . "'pattern='^(0[0-9]?|1?[0-9]?|2?[0-4]?) ?[-\/] ?(0[0-9]?|1[0-9]?|2?[0-4]?)' style='width:68px; background-color: lightgray;' value ='" . $items[9] . "'></td>";
          break;
        case 'hourly':
          $row .= "<td><select id='selectInterval" . $count . "'style='background-color: lightgray;' onchange='checkCustom(" . $count . ")'><option value='daily'>Daily</option><option selected value='hourly'>Hourly</option><option value='custom'>Custom Range</option></select></td>";
          $row .= "<td><input readonly id='txtCustInterval" . $count . "'pattern='^(0[0-9]?|1?[0-9]?|2?[0-4]?) ?[-\/] ?(0[0-9]?|1[0-9]?|2?[0-4]?)' style='width:68px; background-color: lightgray;' value ='" . $items[9] . "'></td>";
          break;
        case 'custom':
          $row .= "<td><select id='selectInterval" . $count . "'style='background-color: lightgray;' onchange='checkCustom(" . $count . ")'><option value='daily'>Daily</option><option value='hourly'>Hourly</option><option selected value='custom'>Custom Range</option></select></td>";
          $row .= "<td><input id='txtCustInterval" . $count . "'pattern='^(2[0-4]|1[0-9]|0?[1-9])' style='width:68px; background-color: lightgray;' value ='" . $items[9] . "'></td>";
          break;
      }

      $row .= "<td><input readonly id='txtTodaysTarget" . $count . "'style='width:98px; color:black;background-color: thistle;' value='" . $todaysTargets[$key] . "' title='Read Only.  Calculated amount by multiplying how many emails were sent yesterday and multiplied by the Warm Up Speed.  If the day befores calculation was higher, that number will be used.'></td>";
      $row .= "<td><input readonly id='SoFarToday" . $count . "'style='width:98px; color:black;background-color: thistle;' value='" . $totals[$key] . "' title='Read Only.  Number of emails sent through this ESP/Pool combination so far today.'></td>";
      $row .= "<td><input id='txtSpeed" . $count . "'required type='number' step='.0001' min='1.0000' max='5.9999' size=10 style='width:68px; color:black;background-color: lightgray;' value='" . $items[12] . "' title='This number is used to increase (by multiplying this number by the actual number of emails send the previous day) the warmup pool available on a daily basis.  A safe number is 2.0 increase each day.'></td>";
      $row .= "<td><input id='txtPassThru" . $count . "'style='width:130px; color:black;background-color: lightgray;' value='" . $items[13] . "' title='This text will be passed back to the calling application (injector) to be used however that application needs the information.'></td>";
      $row .= "<td hidden><input id='txtNewFlag" . $count . "'style='width:1px; color:black;background-color: lightgray;' hidden value='old'></td>";
      $row .= "</tr>";
      echo $row;
      $count++;
    }
    elseif ($items[0]) $count++; // This was the header row; need to add one to the count.
	}
  // if ($count < 2) 
  // {
  //   $row .= "<tr><td colspan=11 border='0'><br><br><center><h3>No Entries, New system?</h3></center></td></tr>";
  //   echo $row;
  // }
  echo "</tbody>";
}	
?>