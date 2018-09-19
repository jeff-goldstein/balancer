<!--Copyright 2016 Jeff Goldstein

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

File: manager.php
Purpose: UI for Adding/Updating/Deleting IP Warmup Pools and Adding/Update/Deleting long term IP pools

-->
<!DOCTYPE html>
<html>
<head>
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<meta content="width=device-width, initial-scale=1" name="viewport">
<title>IP/ISP/ISP Pool Balancer</title>
<link rel="shotcut icon" type="image/png" href="http://www.geekswithapersonality.com/email.png" />
<link href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css" rel="stylesheet">
<!--<link rel="stylesheet" type="text/css" href="../css/tmCommonFormatting.css">-->
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>

<style>
span {
    width: 100%;
    display: block;
}

th, td {
    padding: 8px;
    /*text-align: left;*/
    border-bottom: 1px solid #ddd;
}

tr:hover {background-color: #f5f5f5;}

input:invalid {
  border: 2px dashed red;
}

input:valid {
  border: 1px solid black;
}

/* For Firefox */
input[type='number'] {
    -moz-appearance:textfield;
}
/* Webkit browsers like Safari and Chrome */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Style the tab */
.tab {
    float: left;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
    width: 30%;
    height: 300px;
}

/* Style the buttons that are used to open the tab content */
.tab button {
    display: block;
    background-color: inherit;
    color: black;
    padding: 22px 16px;
    width: 100%;
    border: none;
    outline: none;
    text-align: left;
    cursor: pointer;
    transition: 0.3s;
}

/* Change background color of buttons on hover */
.tab button:hover {
    background-color: #ddd;
}

/* Create an active/current "tab button" class */
.tab button.active {
    background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
    float: left;
    padding: 0px 12px;
    border: 1px solid #ccc;
    width: 70%;
    border-left: none;
    height: 300px;
}

ul.topnav {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #333;
}

ul.topnav li {float: left;}

ul.topnav li a {
  display: inline-block;
  color: #f2f2f2;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
  transition: 0.3s;
  font-size: 17px;
}

ul.topnav li a:hover {background-color: #555;}

ul.topnav li.icon {display: none;}

@media screen and (max-width:680px) {
  ul.topnav li:not(:first-child) {display: none;}
  ul.topnav li.icon {
    float: right;
    display: inline-block;
  }
}

@media screen and (max-width:680px) {
  ul.topnav.responsive {position: relative;}
  ul.topnav.responsive li.icon {
    position: absolute;
    right: 0;
    top: 0;
  }
  ul.topnav.responsive li {
    float: none;
    display: inline;
  }
  ul.topnav.responsive li a {
    display: block;
    text-align: left;
  }
}
</style>
</head>

<body style="margin-left: 20px; margin-right: 20px" onload="populateWarmup(), populateBalance(), getDown(), getCurrentWarmupSending(), checkESPPoolCombo()"<>
<div class="header" style="width:1600px">
<ul class="topnav" id="generatorTopNav">
    <!--<li><a class="active" href="cgBuildCampaign.php">Generate Campaign</a></li>
    //<li><a class="active" href="cgTemplateManager.php">Template Manager</a></li>
    //<li><a class="active" href="cgEmailTracer.php">Email Tracer</a></li>
    //<li><a class="active" href="graphing/highchart.php">Reporting</a></li>    
    //<li><a href="helpdocs/cgHelp.php" target="_blank">Help</a></li> -->
    <li><a href="https://developers.sparkpost.com/" target="_blank">SparkPost Documentation</a></li>
    <li><a href="mailto:email.goldstein@gmail.com?subject=cgMail">Contact</a></li>
    <li><a class="active" href="../cgKey.php">Logoff</a></li>
    <li class="icon">
        <a href="javascript:void(0);" style="font-size:15px;" onclick="generatorNav()">☰</a>
    </li>
</ul>
</div>
<table style="border:hidden;">
    <tr style="border:hidden; height:0px;">
        <td colspan=2 style="border:hidden; height:0px;"></td>
    </tr>
    <tr style="border:hidden;">
        <td style="border:hidden; height:0px;">
            <iframe src="http://free.timeanddate.com/clock/i5ze60a7/n5446/fs12/tt0/tw0/tm1/ta1/tb2" frameborder="0" width="201" height="16"></iframe>
        </td>
        <td style="text-align:left">
            Systems Currently Down:
            <input name="down" id="down" readonly style="color:red; border:hidden; font-weight:bold; font-size: 100%;" size=100 value=""/>
        </td>
        <td style="width:290px;" align=right >
            <div id="google_translate_element"></div>

<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
}
</script>
        </td>
    </tr>
</table>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<div class="tab" style="width:150px;">
  <button class="tablinks" onclick="openTab(event, 'warmup')">WarmUp</button>
  <button class="tablinks" onclick="openTab(event, 'balancer')">Ongoing Balancer</button>
  <button class="tablinks" onclick="openTab(event, 'status')">Real Time Status For WarmUp</button>
</div>

<div id="warmup" class="tabcontent" style="width:1250px; border:hidden">
    <table id="WarmupTable" style="width:1250px; padding:5px; border:hidden;"></table>
    <br><br>
    <button id="addrow" name="addrow" type="button" title="Add a new row" onclick="addWarmupRow()">Add New Entry</button>
    &nbsp;&nbsp;&nbsp;<button id="refresh" name="refresh" title="Will get latest from the stored table" onclick="populateWarmup()">Refresh Table</button>
    &nbsp;&nbsp;&nbsp;<button type="button" id="deleteRows" name="deleteRows" title="This will only delete selected rows" onclick="deleterows('WarmupTable')">Delete Selected</button>
    &nbsp;&nbsp;&nbsp;<button id="sortASC" name="sortASC" title="Sort by Priority in ASC order" onclick="sortTableInt('WarmupTable', 2)">Sort by Priority</button>
    &nbsp;&nbsp;&nbsp;<button id="push" name="push" title="This sorts, saves and updates all entries." onclick="pushNow('WarmupTable')" style="background-color:#ff9d00; border-radius:2px; color:#000000">Save & Push Now</button>
<br><br>Validation Messages: <input size="200" name="validationTxt" id="validationTxt">
<br><br>
<table style="width:900px;">
    <tr><td colspan=6><h2><center>Legend</h2></center></td></tr>
    <tr>
        <td style='background-color:lightblue;'>Example Line</td>
        <td style='background-color:lightyellow;'>New rows not saved</td>
        <td style='background-color:lightgreen;'>Duplicate ESP/Pool names</td>
        <td style='background-color:thistle;'>Read only fields</td>
        <td style='border-style: dashed; color:red; border-bottom: 2px dashed;'>Field Validation Erros</td>
    </tr>
</table>
<br><br>Notes and Instructions:
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The Warmup/Balancer System is meant to help injectors know which ISP to use for it's next email or set of emails.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This tab UI is to help you set up the warmup process for new IP Pools, ESP's and/or IP addresses.  Since ISP's 
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;are looking for new IP addresses to be warmed up it's important to start low and build up.  A good rule of thumb is
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;to start with a couple of hundred emails the first day, then keep doubling each day until you are at full sending volume
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;for that ESP/Pool/Address.  The Warmup/Balancer has a process that is set up to update the new plan each day that the
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;balancer will use in guiding the email injectors.  You can override the normal update process by pressing the 'Push Now'
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;button; otherwise the system will use your new saved configuration during it's normal update.
<ul>
    <li> The first row in blue is an example line.</li>
    <li> Yellow lines are newlly added line(s) and will remain yellow until saved and reloaded.</li>
    <li> Priority will be used by the system fulfilling in ascending order first.</li>
    <li> ESP Name/Code identifiers are up to you.  Your injector requesting information needs to match these names.</li>
    <li> ISP best practices is to warm-up a pool or IP address by doubling the emails each day.  Faster than that can cause issues.</li>
    <li> Pass thru text is used in case you need to send text/codes that I didn't think of to your injector.  When your injector
        requests guidence to which ESP/Pool to use, the controller will send back four items, the number of emails, the ESP Name/Code, the IP Pool and the Pass thru text. </li>
    <li> Warmup can be broken down into three different intervals; Daily, Hourly or Custom.  Daily, means that the system will use that streams quota as quickly as possible.  
        This gives the system the best chance of using that stream and building up a reputation quickly.  Hourly, will break out the daily warmup quota over 24 hours.  This method
        has been shown to build reputation better than using all of the email quota quickly, but this may slow down how quickly you build up your daily quota if you only send that
        email stream type a few hours of the day.  The Custom interval allows you to set what hours to use your quota during; please use military hours 0-24.  For example, if you want to warm up that email stream
        during 8am-6pm, you will type 8-18 which.  This will spread the daily quota during that 10 hour timeframe.</li>  
    </ul>
</div>

<div id="balancer" hidden class="tabcontent" style="width:1250px; border:hidden">
<table id="BalancerTable" style="width:1250px; padding:5px; border:hidden;"></table>
<br><br><button id="addrow" name="addrow" onclick="addBalancerRow()">Add New Entry</button>
&nbsp;&nbsp;&nbsp;<button id="refresh" name="refresh" onclick="populateBalance()">Refresh Table</button>
&nbsp;&nbsp;&nbsp;<button id="deleteRows" name="deleteRows" onclick="deleterows('BalancerTable')">Delete Selected</button>
&nbsp;&nbsp;&nbsp;<button id="exportBalanceTable" name="exportBalanceTable" onclick="saveTable('BalancerTable', 4)">Save Work</button>
&nbsp;&nbsp;&nbsp;<button id="sortASC" name="sortASC" title="Sort by Stream in ASC order" onclick="sortTableChar('BalancerTable', 4)">Sort by Category</button>
<br><br>***** The first row in blue is an example line
<br><br>Notes and Instructions:
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The Warmup/Balancer System is meant to help injectors know which ISP to use for it's next email or set of emails.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This Balancer tab is to help you balance usage across multiple ESP's or IP Pools over the long run.  One use case
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;for multiple ESP's would be to support business continuity, or different instances of the same on premsises MTA.  The
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Balancer allows you to define streams and to further clarify what percent of emails should go to each instance of each stream.
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;This application will NOT force each stream to have a total percentage of 100 amoung each ESP/IP Pool combo, that will be up
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;to you to verfiy that you have the numbers the way you want them.
<ul>
    <li> The first row in blue is an example line.</li>
    <li> Yellow lines are newlly added line(s) and will remain yellow until saved and reloaded.</li>
    <li> Priority will be used by the system fulfilling in ascending order first.</li>
    <li> ESP Name/Code identifiers are up to you.  Your injector requesting information needs to match these names.</li>
    <li> During requests, the system will skip any ESP's that have been marked <i>down</i> by the injectors.  If there are any ESP's down this system will automatically 
        fill in missing percentages.  For example, if there are four seperate ESP's used for the <i>newletters</i> stream and each is set to 25%; but one ESP is down
        this system will automatically give the last ESP for that stream a 50% sending rate.  Lazy, but easier to implement.  This same technique is used to fill
        any missing amounts that do not add up to 100%.</li>
    <li> Pass thru text is used in case you need to send text/codes that I didn't think of to your injector.  When your injector
        requests guidence to which ESP/Pool to use, the controller will send back four items, the number of emails, the ESP Name/Code, the IP Pool and the Pass thru text. </li>
</ul>
</div>
</div>

<div id="status" hidden class="tabcontent" style="width:1250px; border:hidden">
<table><tr><td ><textarea readonly maxlength="200000" id="liveaction" name="liveaction" rows="50" cols="50" style="resize: both; font-size: 8pt;width:100%;max-width:1200px" wrap="off" class="text" placeholder="Real time statistics Updated every 60 seconds"></textarea>
</td></tr></table>
</div>

<script>

function getRndInteger(min, max) 
{
    // Purpose: Used to create Unique ID for each ESP/Pool combination
    // Called By: AddWarmupRow

    return Math.floor(Math.random() * (max - min)) + min;
}

function addWarmupRow() 
{
    // Purpose: Create a new IP Pool Warmup Row.  Set defaults, field validation rules, etc
    // Called By: On click of button within HTML

    var count = document.getElementById("WarmupTable").rows.length;;
    var table = document.getElementById("WarmupTable");
    var row = table.insertRow(count);
    var index=row.rowIndex-1;

    var cell0 = row.insertCell(0);
    cell0.setAttribute = 'true';
    var t0=document.createElement("input");
    t0.id = "isSelectedw";
    t0.name = "isSelectedw";
    t0.type = 'checkbox';
    cell0.appendChild(t0);

    var cell1 = row.insertCell(1);
    cell1.setAttribute = 'true';
    var t1=document.createElement("input");
    t1.id = "txtUID"+index;
    t1.style.cssText = "background-color:lightyellow";
    t1.style.width = "68px";
    t1.value = getRndInteger(10000000,99999999);
    t1.readOnly = true;
    t1.title = 'Unique ID used by the system';
    cell1.appendChild(t1);

    var cell2 = row.insertCell(2);
    cell2.setAttribute = 'true';
    var t2=document.createElement("input");
    t2.id = "txtPriority"+index;
    t2.style.cssText = "background-color:lightyellow";
    t2.style.width = "68px";
    t2.value = count - 1;
    t2.required = "required";
    t2.title = 'The warmup process will use email pools with the highest priority first (1 being the highest), then move to the next pool once the lower pools are used for the given interval.';
    cell2.appendChild(t2);

    var cell3 = row.insertCell(3);
    cell3.setAttribute = 'true';
    var t3=document.createElement("input");
    t3.id = "txtESP"+index;
    t3.style.cssText = "background-color:lightyellow";
    t3.style.width = "68px";
    t3.value = "sparkpost";
    t3.required = "required";
    t3.addEventListener("change", checkESPPoolCombo);
    cell3.appendChild(t3);
 
    var cell4 = row.insertCell(4);
    cell4.setAttribute = 'true';
    var t4=document.createElement("input");
    t4.id = "txtippool"+index;
    t4.style.cssText = "background-color:lightyellow";
    t4.addEventListener("change", checkESPPoolCombo);
    t4.style.width = "68px";
    cell4.appendChild(t4);

    var cell5 = row.insertCell(5);
    cell5.setAttribute = 'true';
    var t5=document.createElement("input");
    t5.id = "txtCategory"+index;
    t5.style.cssText = "background-color:lightyellow";
    t5.style.width = "68px";
    t5.required = "required";
    cell5.appendChild(t5);
 
    var cell6 = row.insertCell(6);
    cell6.setAttribute = 'true';
    var t6=document.createElement("input");
    t6.id = "txtStartDate"+index;
    t6.style.cssText = "background-color:lightyellow";
    t6.style.width = "98px";
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1;
    var yy = today.getFullYear().toString().substr(-2);
    if (dd<10) { dd = '0'+ dd};
    if (mm<10) { mm = '0' + mm};
    var today = mm + '/' + dd + '/' + yy;
    t6.value = today;
    t6.pattern = "^(1[0-2]|0*[1-9]) *[-\/] *(0[1-9]|[1-2]?[0-9]|3[0-1]) ?[-\/] ?(1[8-9]|2[0-9])";
    t6.required = "required";
    cell6.appendChild(t6);
 
    var cell7 = row.insertCell(7);
    cell7.setAttribute = 'true';
    var t7=document.createElement("input");
    t7.id = "txtTarget"+index;
    t7.style.cssText = "background-color:lightyellow";
    t7.style.width = "98px";
    t7.title='This is the number of emails being sent on a given day where you believe the pool is warmed up and ready to be used on a ongoing basis (see Ongoing Balancer tab).';
    t7.required = "required";
    t7.pattern = "[0-9]*";
    cell7.appendChild(t7);
  
    var cell8 = row.insertCell(8);
    cell8.setAttribute = 'true';
    var t8=document.createElement("input");
    t8.id = "txtStartingPoint"+index;
    t8.style.cssText = "background-color:lightyellow";
    t8.style.width = "98px";
    t8.value = "200";
    t8.required = "required";
    t8.pattern = "[0-9]*";
    t8.title='This the number of emails this pool is allowed to send on the first day.  Daily calculations will continue from there using the previous days actual sending from that pool.';
    cell8.appendChild(t8);

    //Create array of options to be added
    var arrayDisplay = ["Daily","Hourly","Custom Range"];
    var arrayValues = ["daily","hourly","custom"];
    //Create and append select list   
    var cell9 = row.insertCell(9);
    cell9.setAttribute = 'true';
    var t9 = document.createElement("select");
    t9.id = "selectInterval"+index;
    //Create and append the options
    for (var i = 0; i < arrayDisplay.length; i++) 
    {
        var option = document.createElement("option");
        option.value = arrayValues[i];
        option.text = arrayDisplay[i];
        option.id = arrayValues[i]+index;
        t9.appendChild(option);
    }
    t9.selectedIndex = "Daily";
    t9.style.cssText = "background-color:lightyellow";
    t9.style.width = "105px";
    t9.onchange = function () {checkCustom(index);};
    cell9.appendChild(t9);
    document.getElementById("daily"+index).selected = true;

    var cell10 = row.insertCell(10);
    cell10.setAttribute = 'true';
    var t10=document.createElement("input");
    t10.id = "txtCustInterval"+index;
    t10.style.cssText = "background-color:lightyellow";
    t10.style.width = "68px";
    t10.readOnly = true;
    t10.pattern = "^(0[0-9]?|1?[0-9]?|2?[0-4]?) ?[-\/] ?(0[0-9]?|1[0-9]?|2?[0-4]?)";
    cell10.appendChild(t10);

    var cell11 = row.insertCell(11);
    cell11.setAttribute = 'true';
    var t11=document.createElement("input");
    t11.id = "txtTodaysTarget"+index;
    t11.style.cssText = "background-color:lightyellow";
    t11.style.width = "98px";
    t11.readOnly=true;
    t11.title='Read Only.  Calculated amount by multiplying how many emails were sent yesterday and multiplied by the Warm Up Speed.  If the day befores calculation was higher, that number will be used.';
    cell11.appendChild(t11);
 
    var cell12 = row.insertCell(12);
    cell12.setAttribute = 'true';
    var t12=document.createElement("input");
    t12.id = "txtSoFarToday"+index;
    t12.style.cssText = "background-color:lightyellow";
    t12.style.width = "96px";
    t12.readOnly = true;
    t12.title='Read Only.  Number of emails sent through this ESP/Pool combination so far today';
    cell12.appendChild(t12);
 
    var cell13 = row.insertCell(13);
    cell13.setAttribute = 'true';
    var t13=document.createElement("input");
    t13.id = "txtSpeed"+index;
    t13.style.cssText = "background-color:lightyellow";
    t13.style.width = "68px";
    t13.value = "2.0";
    t13.required = "required";
    t13.title='This number is used to increase (by multiplying this number by the actual number of emails send the previous day) the warmup pool available on a daily basis.  A safe number is 2.0 increase each day.';
    cell13.appendChild(t13);

    var cell14 = row.insertCell(14);
    cell14.setAttribute = 'true';
    var t14=document.createElement("input");
    t14.id = "txtPassThru"+index;
    t14.style.cssText = "background-color:lightyellow";
    t14.style.width = "130px";
    cell14.appendChild(t14);
    //document.getElementById("exportWarmupTable").style.backgroundColor = "yellow";

    var cell15 = row.insertCell(15);
    cell15.setAttribute = 'true';
    var t15=document.createElement("input");
    t15.id = "txtNewFlag"+index;
    t15.hidden = true;
    t15.value = "new";
    t15.style.width = "1px";
    cell15.appendChild(t15);
    checkESPPoolCombo();
}

function addBalancerRow() 
{
    // Purpose: Create a new IP Pool Row for older warmed up IP pool.  Set defaults, field validation rules, etc
    // Called By: On click of button within HTML

    var index=1;
    var count = document.getElementById("BalancerTable").rows.length;;
    var table = document.getElementById("BalancerTable");
    var row = table.insertRow(count);

    var cell0 = row.insertCell(0);
    cell0.setAttribute = 'true';
    var t0=document.createElement("input");
    t0.id = "isSelectedb";
    t0.name = "isSelectedb";
    t0.type = 'checkbox';
    cell0.appendChild(t0);
 
    var cell1 = row.insertCell(1);
    cell1.setAttribute = 'true';
    var t1=document.createElement("input");
    t1.id = "txtUID"+index;
    t1.style.cssText = "background-color:lightyellow";
    t1.style.width = "100px";
    t1.value = getRndInteger(10000000,99999999);
    t1.readOnly = true;
    t1.title = 'Unique ID used by the system';
    cell1.appendChild(t1);

    var cell2 = row.insertCell(2);
    cell2.setAttribute = 'true';
    var t2=document.createElement("input");
    t2.id = "txtESP"+index;
    t2.style.cssText = "background-color:lightyellow";
    t2.style.width = "100px";
    cell2.appendChild(t2);

    var cell3 = row.insertCell(3);
    cell3.setAttribute = 'true';
    var t3=document.createElement("input");
    t3.id = "txtippool"+index;
    t3.style.cssText = "background-color:lightyellow";
    t3.style.width = "100px";
    cell3.appendChild(t3);
 
    var cell4 = row.insertCell(4);
    cell4.setAttribute = 'true';
    var t4=document.createElement("input");
    t4.id = "txtStream"+index;
    t4.style.cssText = "background-color:lightyellow";
    t4.style.width = "100px";
    cell4.appendChild(t4);
 
    var cell5 = row.insertCell(5);
    cell5.setAttribute = 'true';
    var t5=document.createElement("input");
    t5.id = "txtPercentage"+index;
    t5.style.cssText = "background-color:lightyellow";
    t5.style.width = "100px";
    cell5.appendChild(t5);

    var cell6 = row.insertCell(6);
    cell6.setAttribute = 'true';
    var t6=document.createElement("input");
    t6.id = "txtStartDate"+index;
    t6.style.cssText = "background-color:lightyellow";
    t6.style.width = "98px";
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1;
    var yy = today.getFullYear().toString().substr(-2);
    if (dd<10) { dd = '0'+ dd};
    if (mm<10) { mm = '0' + mm};
    var today = mm + '/' + dd + '/' + yy;
    t6.value = today;
    t6.pattern = "^(1[0-2]|0*[1-9]) *[-\/] *(0[1-9]|[1-2]?[0-9]|3[0-1]) ?[-\/] ?(1[8-9]|2[0-9])";
    t6.required = "required";
    cell6.appendChild(t6);

    var cell7 = row.insertCell(7);
    cell7.setAttribute = 'true';
    var t7=document.createElement("input");
    t7.id = "txtPassThru"+index;
    t7.style.cssText = "background-color:lightyellow";
    t7.style.width = "160px";
    cell7.appendChild(t7);
}

function checkCustom(fieldindex)
{
    // Purpose: Set the 'Custom Hours' field dependent on the Interval pulldown value
    // Called By: On change method from HTML when Interval field is changed

    var selected = document.getElementById("selectInterval" + fieldindex).value;
    if ((selected == "daily") || (selected == "hourly")) 
    {
        document.getElementById("txtCustInterval" + fieldindex).value = "";
        document.getElementById("txtCustInterval" + fieldindex).readOnly = true;
    }
    else
    {
        document.getElementById("txtCustInterval" + fieldindex).readOnly = false;
    }
}

function getDown()
{
    // Purpose: Obtain all downed ESPs from within the 'down' text file.  This is a list of ESPs that the email services couldn't connect to
    // Called By: Multiple functions throughout the application when updates are made to either Warmup and/or Ongoing tables

    $.ajax({
        url:'getDown.php',
        type: "POST",
        complete: function (response) 
        {
            document.getElementById("down").value = response.responseText;
        },
        error: function () {
            alert("Problem obtaining Down Systems Table");
        }
        });
}

function getCurrentWarmupSending()
{

    // Purpose: Show realtime data from each table keeping track of IP Pool usage.  This data is kept in the third tab, 'Real Time Status for Warmup'
    //          This function is also called at set Intervals in order to continuously update 
    // Called By: On page load AND continuously via 'updategetCurrent' function

    $.ajax({
        url:'getCurrentWarmupSending.php',
        type: "POST",
        complete: function (response) 
        {
            document.getElementById("liveaction").value = response.responseText;
        },
        error: function () {
            alert("Problem obtaining Real time data");
        }
        });
}

function populateWarmup()
{       
    // Purpose: (re)build warmup table with latest information.
    // Called By: On page load, refresh button and when the table is saved or a row is deleted

    $.ajax({
        url:'buildWarmUpTable.php',
        type: "POST",
        complete: function (response) 
        {
            document.getElementById("WarmupTable").innerHTML = response.responseText;
        },
        error: function () {
            alert("Problem obtaining Warmup Table");
        }
        });
    getDown();
}

function populateBalance()
{    
    // Purpose: (re)build ongoing ISP/Pool table with latest information.
    // Called By: On page load, refresh button and when the table is saved

    $.ajax({
        url:'buildBalancerTable.php',
        type: "POST",
        complete: function (response) 
        {
            document.getElementById("BalancerTable").innerHTML = response.responseText;
        },
        error: function () {
            alert("Problem obtaining Balancer Table");
        }
        });
    getDown();
}

function saveTable(tableName, column)
{  
    // Purpose: Save either the Warmup or Long term ISP tables
    // Called By: From HTML buttons

    if (tableName == "WarmupTable") {sortTableInt (tableName, column);} else {sortTableChar (tableName, column);}
    
    var table = document.getElementById(tableName).innerHTML;
    var table = document.getElementById(tableName);
    var stringTable = "";
    for (var i = 0, row; row = table.rows[i]; i++) 
    {
        //iterate through rows
        //rows would be accessed using the "row" variable assigned in the for loop
        var stringRow = "";
        for (var j = 0, col; col = row.cells[j]; j++) 
        {
            var a = row.cells[j];
            var b = a.lastElementChild;
            if (i>1 && j>0 && ((b.localName == "input") || (b.localName == "select"))) //we don't care about the first two rows (header, example) and the checkbox field of each row
            {
                stringRow = stringRow + b.value.trim() + ',';
            }
        }
        if (i>1) 
        {
            stringRow = stringRow.substring(0, stringRow.length-1);
            stringTable = stringTable + stringRow + "\n";
        } 
    }

    $.ajax({
        url:'saveTable.php',
        type: "POST",
        data: {"table" : stringTable, "tableName" : tableName},
        success: function (response) 
        {
            if (tableName == "WarmupTable") populateWarmup(); else populateBalance();
        },
        error: function (response) {
            alert("Problem saving the Warmup Table");
        }
        });
}

function validateRow(row, priority, UID, esp, ippool, stream, startDate, target, starting, interval, custom, speed)
{
    // Purpose: Field Validation for Warmup Row(s) before committing to file
    // Called By: pushNow

    var validationString = document.getElementById("validationTxt").value;
    var errorFound = false;
    var newString = "";

    if (!esp){ errorFound = true; newString += " ESP can not be blank |";}
    if (!stream){ errorFound = true; newString += " Stream can not be blank |";}
    if (!startDate){ errorFound = true; newString += " Start Date can not be blank |";}
    //if (todayC > startDateC) { errorFound = true; newString += " Start Date must be after " + todayS + " |";}
    if (!target){ errorFound = true; newString += " Target can not be blank |";}
    if (!starting){ errorFound = true; newString += " Starting Point can not be blank |";}
    if (parseInt(starting) > parseInt(target)) {errorFound = true; newString += " Starting Point can not be higher than Target |";}
    if ((interval == "custom") && !custom){ errorFound = true; newString += " Custom Hours can not be blank for Custom Intervals |";}
    if (speed < 1){ errorFound = true; newString += " Speed must be greater than 1|";}
    if (errorFound)
    {
        newString = " Row " + row + ' ( ' + UID + ' ) ' + newString;
        validationString += newString;
        document.getElementById("validationTxt").value = validationString;
        return true;
    }
    return false;
}

function addRow(tableID, i, UID, priority, esp, ippool, stream, target, starting, passInterval, speed, passthru, startDate)
{
    // Purpose: Specific function that saves/commits the new row to file
    // Called By: pushNow

    $.ajax({
        url:'addRowToWarmup.php',
        type: "POST",
        data: {"UID" : UID, "priority" : priority, "esp" : esp, "ippool" : ippool, "stream" : stream, "target" : target, "starting" : starting, "passInterval" : passInterval, "speed" : speed, "passthru" : passthru, "startDate" : startDate},
        success: function (response) 
        {
            var a = response; //notice, I'm doing nothing with a
            if (response == "good")
            {
                var table = document.getElementById(tableID).tBodies[0];
                var row = table.rows[i];
                row.cells[15].lastChild.value = "old";
            }
        },
        error: function (response) 
        {
            alert("Problem Adding New Row?" + UID + ", " + priority + ", " + esp + ", " + stream);
            var a = response.responseText; //notice, I'm doing nothing with a
        }
    });
}

function updateRow(tableID, UID, priority, esp, ippool, stream, target, starting, passInterval, speed, passthru, startDate)
{
    
    // Purpose: Calls the service that will take the new data and update each Warmup file.
    // Called By: pushNow

    $.ajax({
        url:'updateWarmupRow.php',
        type: "POST",
        data: {"UID" : UID, "priority" : priority, "esp" : esp, "ippool" : ippool, "stream" : stream, "target" : target, "starting" : starting, "passInterval" : passInterval, "speed" : speed, "passthru" : passthru, "startDate" : startDate},
        success: function (response) 
        {
            var a = response; //notice, I'm doing nothing with a
            if (response == "good")
            {

            }
        },
        error: function (response) 
        {
            alert("Problem Adding New Row?" + UID + ", " + priority + ", " + esp + ", " + stream);
            var a = response.responseText; //notice, I'm doing nothing with a
        }
    });
}

function pushNow(tableID)
{
    // Purpose: Save the Warmup IP table on button 
    // Called By: HTML button

    if (confirm('This will save all changed data and add new entry(s) into the current Warmup Process.  Continue?')) 
    {
        document.getElementById("validationTxt").value = "";
        var foundValidationError = false;
        var table = document.getElementById(tableID).tBodies[0];
        var rowCount = table.rows.length;
        var foundValidationError = false;

        for(var i=1; i<rowCount; i++) 
        {
            // Check each row for errors
            var row = table.rows[i];
            var UID = row.cells[1].lastChild.value;
            var priority = row.cells[2].lastChild.value;
            var esp = row.cells[3].lastChild.value;
            var ippool = row.cells[4].lastChild.value;
            var stream = row.cells[5].lastChild.value;
            var startDate = row.cells[6].lastChild.value;
            var target = row.cells[7].lastChild.value;
            var starting = row.cells[8].lastChild.value;
            var interval = row.cells[9].lastChild.value;
            var custom = row.cells[10].lastChild.value;
            var speed = row.cells[13].lastChild.value;
            var passthru = row.cells[14].lastChild.value;
            var newFlag = row.cells[15].lastElementChild.value;
            validateStatus = validateRow(i, priority, UID, esp, ippool, stream, startDate, target, starting, interval, custom, speed);
            if (validateStatus && !foundValidationError) foundValidationError = true;
        }
        if (foundValidationError)
            alert ("See Validation Messages field before continuing");
        else
        {
            // No errors, now loop through each row and either add or update
            for(var i=1; i<rowCount; i++)
            { 
                var row = table.rows[i];
                var UID = row.cells[1].lastChild.value;
                var priority = row.cells[2].lastChild.value;
                var esp = row.cells[3].lastChild.value;
                var ippool = row.cells[4].lastChild.value;
                var stream = row.cells[5].lastChild.value;
                var startDate = row.cells[6].lastChild.value;
                var target = row.cells[7].lastChild.value;
                var starting = row.cells[11].lastChild.value;
                var interval = row.cells[9].lastChild.value;
                var custom = row.cells[10].lastChild.value;
                //var dailyTarget = table.rows[i].cells[11].lastChild.value;
                var speed = row.cells[13].lastChild.value;
                var passthru = row.cells[14].lastChild.value;
                var newFlag = row.cells[15].lastElementChild.value;
                if (interval == "daily") {var passInterval = 1;} else {if (interval == "hourly") {var passInterval = 24;} else var passInterval = custom;}
                if(newFlag == "new") 
                {     
                    addRow (tableID, i, UID, priority, esp, ippool, stream, target, starting, passInterval, speed, passthru, startDate);
                }
                else
                {
                    updateRow (tableID, UID, priority, esp, ippool, stream, target, starting, passInterval, speed, passthru, startDate);
                }
            }
            saveTable('WarmupTable', 2);
        }
    } 
    else 
    {
        // got nothing, they declined to continue with the save process.  We simply fall back to the UI
    }

}
                
function generatorNav() {
    var x = document.getElementById("generatorTopNav");
    if (x.className === "topnav") {
        x.className += " responsive";
    } else {
        x.className = "topnav";
    }
}

function openTab(evt, content) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the link that opened the tab
    document.getElementById(content).style.display = "block";
    evt.currentTarget.className += " active";
}

function changeall(tableID)
{
    var tableName = 'mastercheck' + tableID;
    var checked = document.getElementById(tableName).checked;
    if (checked) checkall(tableID); else uncheckall(tableID);     
}

function uncheckall(tableID) 
{    
    var checks = document.getElementsByName('isSelected'+tableID);
    for(var i = 0; i < checks.length; ++i)
    {
        checks[i].checked = false;
    }
}

function checkall(tableID) 
{        
    var checks = document.getElementsByName('isSelected'+tableID);
    for(var i = 0; i < checks.length; ++i)
    {
        checks[i].checked = true;
    }
}

/*function updateDailyTarget(fieldindex)
{
    document.getElementById("txtTodaysTarget" + fieldindex).value = document.getElementById("txtStartingPoint" + fieldindex).value;
}*/


function deleterows(tableID)  
{
    // Purpose: Delete selected rows from either table both in the UI and physical file.  This function will remove the row from the UI.
    // In deleteEntry.php, the first step is to remove the rows from the UI.
    // The second step will call the 'deleteEntry.php' service that will delete the entries from the warmup tracking files.  
    // Called By: HTML button

    if (confirm('This will delete all selected rows; any entries that are already being used for email delivery will be immediately remove from the system and will no longer be used for sending.  Continue?')) 
    {
        var table = document.getElementById(tableID).tBodies[0];
        var rowCount = table.rows.length;

        for(var i=1; i<rowCount; i++) 
        {
            var row = table.rows[i];
            var UID = row.cells[1].lastElementChild.value;
            var chkbox = row.cells[0].getElementsByTagName('input')[0];
            if(null != chkbox && true == chkbox.checked) 
            {
                table.deleteRow(i);
                rowCount--;
                i--;

                $.ajax({
                    url:'deleteEntry.php',
                    type: "POST",
                    data: {"tableID" : tableID, "UID" : UID},
                    success: function (response) 
                    {
                        var a = response; //notice, I'm doing nothing with a
                    },
                    error: function (response) 
                    {
                        alert("Problem Deleting Row? " + UID);
                    }
                });
            }
        }
        populateWarmup();  //Hopefully this is a useless call.  This call will be useful to bring back any rows that were deleted from the HTML row, BUT failed to delete from the underlining tables.
    }
}

function sortTableInt(tableID, column)
{
    // Purpose: Used to sort the Warmup table by priority
    // Called By: Sort button

/* W3School example with changes */
var table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementById(tableID);
  switching = true;
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.getElementsByTagName("TR");
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 2; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("input")[column];
      y = rows[i + 1].getElementsByTagName("input")[column];
      // Check if the two rows should switch place:
      xInt = parseInt(x.value);
      yInt = parseInt(y.value);
      if (xInt > yInt) {
        // If so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}

function sortTableChar(tableID, column)
{
    // Purpose: Used to sort the long term balancer table by category
    // Called by: Sort button

/* W3School example with changes */
var table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementById(tableID);
  switching = true;
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.getElementsByTagName("TR");
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 2; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("input")[column];
      y = rows[i + 1].getElementsByTagName("input")[column];
      // Check if the two rows should switch place:
      x = x.value;
      y = y.value;
      if (x > y) {
        // If so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}

$(document).bind('input').change(function()
{
  document.getElementById("push").style.backgroundColor = "yellow";
});


function checkESPPoolCombo()  
{
    // Purpose: Validate against any duplicate ESP and Pool combinations.  Set a warning and color code appropriately
    // Called By: Onload, when either field is changed, and when a new row is added

    /* stole from brendan (stackoverflow) */
    var table = document.getElementById('WarmupTable');
    for (var r = 0, n = table.rows.length; r < n; r++) 
    {
        for (var c = 0, m = table.rows[r].cells.length; c < m; c++) 
        {
            if (table.rows[r].cells[c].lastElementChild.style.backgroundColor == "lightgreen")
            {
                table.rows[r].cells[c].lastElementChild.style.backgroundColor = table.rows[r].cells[1].lastElementChild.style.backgroundColor;
                table.rows[r].cells[c+1].lastElementChild.style.backgroundColor = table.rows[r].cells[1].lastElementChild.style.backgroundColor;
            }
        }
    }

    //document.getElementById("exportWarmupTable").disabled = false;
    document.getElementById("push").disabled = false;
    var iIndexOutter = 2;
    var matchFound = null;
    var count = document.getElementById("WarmupTable").rows.length;
    var table = document.getElementById("WarmupTable");
    while ((iIndexOutter < count) && !matchFound)
    {
        var outterrow = table.rows[iIndexOutter];
        var ESP = outterrow.cells[3].lastElementChild.value;
        var pool = outterrow.cells[4].lastElementChild.value;
        var iIndexInner = 2;
        //while ((iIndexInner < count) && !matchFound)
        while (iIndexInner < count)
        {
            if (iIndexInner != iIndexOutter)
            {
                var innerrow = table.rows[iIndexInner];
                var curESP = innerrow.cells[3].lastElementChild.value;
                var curPool = innerrow.cells[4].lastElementChild.value;
                if ((curESP == ESP) && (curPool == pool)) 
                {
                    matchFound = true;
                    innerrow.cells[3].lastElementChild.style.backgroundColor = "lightgreen";
                    innerrow.cells[4].lastElementChild.style.backgroundColor = "lightgreen";
                    outterrow.cells[3].lastElementChild.style.backgroundColor = "lightgreen";
                    outterrow.cells[4].lastElementChild.style.backgroundColor = "lightgreen";
                }
            }
            iIndexInner++;
        }
        iIndexOutter++;
    }
    if (matchFound)
    {
        //document.getElementById("exportWarmupTable").disabled = true;
        document.getElementById("push").disabled = true;
        alert ("ESP/Pool combo's must be unique; match(s) found.  'Save Work and Push Now' buttons are disabled until green highlighted fields are fixed.");
    }  
}


/*function validateMyInt(s) {
  var reg = /^(\d+)\s*-\s*(\d+)$/;
  var match = reg.exec(s);
  if(match) {
    var a = parseInt(match[1],10), b = parseInt(match[2],10);
    return a < b;
  }
  return false;
}*/

function updategetDown() {
    getDown()
}
var i = setInterval(function() { updategetDown(); }, 2000);

function updategetCurrent() {
    getCurrentWarmupSending()
}
var i = setInterval(function() { updategetCurrent(); }, 2000);

</script>
</body>
</html>