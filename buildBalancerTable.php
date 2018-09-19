<?php
/*
File: buildBalancerTable.php
Purpose in life: To take the data from the Balancer Table and create an HTML table for the UI.
*/
{
  $count=0;
  echo "<tbody><tr>";
  echo "<th style='width:15px'><center><input unchecked type='checkbox' id='mastercheckb' name='mastercheckb' onchange='changeall(\"b\")'/></center></th>";
  echo "<th align=left>Unique Row ID</th>";
  echo "<th align=left>ESP Name/Code</th>";
  echo "<th align=left>IP Pool Name (optional)</th>";
  echo "<th align=left>Category</th>";
  echo "<th align=left>Percentage</th>";
  echo "<th align=left>Start Date</th>";
  echo "<th align=left>Pass Thru Text</th>";
  echo "</tr><tr>";
  echo "<td style='width:15px'><input type='checkbox' readonly style='background-color: #7FDBFF;' unchecked hidden></input></td>";
  echo "<td style='width:300px'><input readonly size=15 style='background-color: #7FDBFF;' value=00384462></input></td>";
  echo "<td style='width:300px'><input size=15 readonly style='background-color: #7FDBFF;' value = 'sparkpost'></input></td>";
  echo "<td style='width:300px'><input size=15 readonly style='background-color: #7FDBFF;' value = 'newsletters'></input></td>";
  echo "<td style='width:300px'><input size=15 readonly style='background-color: #7FDBFF;' value = 'newsletters'></input></td>";
  echo "<td style='width:300px'><input size=15 readonly style='background-color: #7FDBFF;' value = '4'></input></td>";
  $newdate = date("m/d/y", strtotime("-4 days"));
  echo "<td><input readonly style='width:98px; background-color: #7FDBFF;' value = '" . $newdate . "'></td>";
  echo "<td style='width:300px'><input size=25 readonly style='background-color: #7FDBFF;' value = 'Pass xyz and abc'></input></td>";

  $balancer = file_get_contents("BalancerTable.csv");
  $matches=explode("\n",$balancer);
  foreach ($matches as $row)
  {
    $items = explode(",", $row);
    if ($items[0] && $count > 0) // Make sure we don't have an empty row
    {
      $count++;
      $row =  "<tr>";
      $row .= "<td style='width:15px'><input type='checkbox' name='isSelectedb'></input></td>";
      $row .= "<td style='width:300px'><input size=15 style='color:black;background-color: lightgray;' value='" . $items[0] . "''></input></td>";
      $row .= "<td style='width:300px'><input size=15 style='color:black;background-color: lightgray;' value='" . $items[1] . "''></input></td>";
      $row .= "<td style='width:300px'><input size=15 style='color:black;background-color: lightgray;' value='" . $items[2] . "''></input></td>";
      $row .= "<td style='width:300px'><input size=15 style='color:black;background-color: lightgray;' value='" . $items[3] . "''></input></td>";
      $row .= "<td style='width:300px'><input size=15 style='color:black;background-color: lightgray;' value='" . $items[4] . "''></input></td>";
      $row .= "<td style='width:300px'><input size=15 id='txtStartDate" . $count . "'required pattern='^(1[0-2]|0*[1-9]) ?[-\/] ?(0[1-9]|[1-2]?[0-9]|3[0-1]) ?[-\/] ?(1[8-9]|2[0-9])' style='width:98px; color:black;background-color: lightgray;' value='" . $items[5] . "'></td>";
      $row .= "<td style='width:300px'><input size=25 style='color:black;background-color: lightgray;' value='" . $items[6] . "''></input></td>";      
      $row .= "</tr>";
      echo $row;
    }
    elseif ($items[0]) $count++; // This was the header row; need to add one to the count.
	}
  if ($count < 2) 
  {
    $row .= "<tr><td colspan=7 border='0'><br><br><center><h3>No Entries, New system?</h3></center></td></tr>";
    echo $row;
  }
  echo "</tbody>";
}	
?>