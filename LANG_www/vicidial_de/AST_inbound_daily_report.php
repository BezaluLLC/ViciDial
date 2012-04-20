<?php 
# AST_inbound_daily_report.php
# 
# Copyright (C) 2012  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGES
#
# 111119-1234 - First build
# 120118-2116 - Changed headers on CSV download
# 120224-0910 - Added HTML display option with bar graphs
#

require("dbconnect.php");

$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
if (isset($_GET["group"]))				{$group=$_GET["group"];}
	elseif (isset($_POST["group"]))		{$group=$_POST["group"];}
if (isset($_GET["query_date"]))				{$query_date=$_GET["query_date"];}
	elseif (isset($_POST["query_date"]))	{$query_date=$_POST["query_date"];}
if (isset($_GET["end_date"]))				{$end_date=$_GET["end_date"];}
	elseif (isset($_POST["end_date"]))		{$end_date=$_POST["end_date"];}
if (isset($_GET["shift"]))				{$shift=$_GET["shift"];}
	elseif (isset($_POST["shift"]))		{$shift=$_POST["shift"];}
if (isset($_GET["file_download"]))			{$file_download=$_GET["file_download"];}
	elseif (isset($_POST["file_download"]))	{$file_download=$_POST["file_download"];}
if (isset($_GET["hourly_breakdown"]))			{$hourly_breakdown=$_GET["hourly_breakdown"];}
	elseif (isset($_POST["hourly_breakdown"]))	{$hourly_breakdown=$_POST["hourly_breakdown"];}
if (isset($_GET["submit"]))				{$submit=$_GET["submit"];}
	elseif (isset($_POST["submit"]))	{$submit=$_POST["submit"];}
if (isset($_GET["SUBMIT"]))				{$SUBMIT=$_GET["SUBMIT"];}
	elseif (isset($_POST["SUBMIT"]))	{$SUBMIT=$_POST["SUBMIT"];}
if (isset($_GET["DB"]))				{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))	{$DB=$_POST["DB"];}
if (isset($_GET["report_display_type"]))				{$report_display_type=$_GET["report_display_type"];}
	elseif (isset($_POST["report_display_type"]))	{$report_display_type=$_POST["report_display_type"];}

$PHP_AUTH_USER = ereg_replace("[^0-9a-zA-Z]","",$PHP_AUTH_USER);
$PHP_AUTH_PW = ereg_replace("[^0-9a-zA-Z]","",$PHP_AUTH_PW);

if (strlen($shift)<2) {$shift='ALL';}

$report_name = 'Inbound Daily Report';
$db_source = 'M';

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,outbound_autodial_active,slave_db_server,reports_use_slave_db FROM system_settings;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$MAIN.="$stmt\n";}
$qm_conf_ct = mysql_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysql_fetch_row($rslt);
	$non_latin =					$row[0];
	$outbound_autodial_active =		$row[1];
	$slave_db_server =				$row[2];
	$reports_use_slave_db =			$row[3];
	}
##### END SETTINGS LOOKUP #####
###########################################

if ( (strlen($slave_db_server)>5) and (preg_match("/$report_name/",$reports_use_slave_db)) )
	{
	mysql_close($link);
	$use_slave_server=1;
	$db_source = 'S';
	require("dbconnect.php");
	$MAIN.="<!-- Using slave server $slave_db_server $db_source -->\n";
	}

$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and pass='$PHP_AUTH_PW' and user_level >= 7 and view_reports='1' and active='Y';";
if ($DB) {$MAIN.="|$stmt|\n";}
if ($non_latin > 0) {$rslt=mysql_query("SET NAMES 'UTF8'");}
$rslt=mysql_query($stmt, $link);
$row=mysql_fetch_row($rslt);
$auth=$row[0];

$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and pass='$PHP_AUTH_PW' and user_level='7' and view_reports='1' and active='Y';";
if ($DB) {$MAIN.="|$stmt|\n";}
$rslt=mysql_query($stmt, $link);
$row=mysql_fetch_row($rslt);
$reports_only_user=$row[0];

if( (strlen($PHP_AUTH_USER)<2) or (strlen($PHP_AUTH_PW)<2) or (!$auth))
	{
    Header("WWW-Authenticate: Basic realm=\"VICI-PROJECTS\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo "Unzulässiges Username/Kennwort:|$PHP_AUTH_USER|$PHP_AUTH_PW|\n";
    exit;
	}

$stmt="SELECT user_group from vicidial_users where user='$PHP_AUTH_USER' and pass='$PHP_AUTH_PW' and user_level > 6 and view_reports='1' and active='Y';";
if ($DB) {$MAIN.="|$stmt|\n";}
$rslt=mysql_query($stmt, $link);
$row=mysql_fetch_row($rslt);
$LOGuser_group =			$row[0];

$stmt="SELECT allowed_campaigns,allowed_reports,admin_viewable_groups,admin_viewable_call_times from vicidial_user_groups where user_group='$LOGuser_group';";
if ($DB) {$MAIN.="|$stmt|\n";}
$rslt=mysql_query($stmt, $link);
$row=mysql_fetch_row($rslt);
$LOGallowed_campaigns =			$row[0];
$LOGallowed_reports =			$row[1];
$LOGadmin_viewable_groups =		$row[2];
$LOGadmin_viewable_call_times =	$row[3];

if ( (!preg_match("/$report_name/",$LOGallowed_reports)) and (!preg_match("/ALL REPORTS/",$LOGallowed_reports)) )
	{
    Header("WWW-Authenticate: Basic realm=\"VICI-PROJECTS\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo "Sie sind nicht berechtigt, diesen Bericht zu sehen: |$PHP_AUTH_USER|$report_name|\n";
    exit;
	}

$LOGadmin_viewable_groupsSQL='';
$whereLOGadmin_viewable_groupsSQL='';
if ( (!eregi("--ALL--",$LOGadmin_viewable_groups)) and (strlen($LOGadmin_viewable_groups) > 3) )
	{
	$rawLOGadmin_viewable_groupsSQL = preg_replace("/ -/",'',$LOGadmin_viewable_groups);
	$rawLOGadmin_viewable_groupsSQL = preg_replace("/ /","','",$rawLOGadmin_viewable_groupsSQL);
	$LOGadmin_viewable_groupsSQL = "and user_group IN('---ALL---','$rawLOGadmin_viewable_groupsSQL')";
	$whereLOGadmin_viewable_groupsSQL = "where user_group IN('---ALL---','$rawLOGadmin_viewable_groupsSQL')";
	}

$LOGadmin_viewable_call_timesSQL='';
$whereLOGadmin_viewable_call_timesSQL='';
if ( (!eregi("--ALL--",$LOGadmin_viewable_call_times)) and (strlen($LOGadmin_viewable_call_times) > 3) )
	{
	$rawLOGadmin_viewable_call_timesSQL = preg_replace("/ -/",'',$LOGadmin_viewable_call_times);
	$rawLOGadmin_viewable_call_timesSQL = preg_replace("/ /","','",$rawLOGadmin_viewable_call_timesSQL);
	$LOGadmin_viewable_call_timesSQL = "and call_time_id IN('---ALL---','$rawLOGadmin_viewable_call_timesSQL')";
	$whereLOGadmin_viewable_call_timesSQL = "where call_time_id IN('---ALL---','$rawLOGadmin_viewable_call_timesSQL')";
	}

$NOW_DATE = date("Y-m-d");
$NOW_TIME = date("Y-m-d H:i:s");
$STARTtime = date("U");
if (!isset($group)) {$group = '';}
if (!isset($query_date)) {$query_date = $NOW_DATE;}
if (!isset($end_date)) {$end_date = $NOW_DATE;}

$stmt="select group_id,group_name from vicidial_inbound_groups $whereLOGadmin_viewable_groupsSQL order by group_id;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$MAIN.="$stmt\n";}
$groups_to_print = mysql_num_rows($rslt);
$i=0;
$groups_string='|';
while ($i < $groups_to_print)
	{
	$row=mysql_fetch_row($rslt);
	$groups[$i] =		$row[0];
	$group_names[$i] =	$row[1];
	$groups_string .= "$groups[$i]|";
	$i++;
	}

$HEADER.="<HTML>\n";
$HEADER.="<HEAD>\n";
$HEADER.="<STYLE type=\"text/css\">\n";
$HEADER.="<!--\n";
$HEADER.="   .green {color: black; background-color: #99FF99}\n";
$HEADER.="   .red {color: black; background-color: #FF9999}\n";
$HEADER.="   .orange {color: black; background-color: #FFCC99}\n";
$HEADER.="-->\n";
$HEADER.=" </STYLE>\n";

if (!preg_match("/\|$group\|/i",$groups_string))
	{
	$HEADER.="<!-- group not found: $group  $groups_string -->\n";
	$group='';
	}

$HEADER.="<script language=\"JavaScript\" src=\"calendar_db.js\"></script>\n";
$HEADER.="<link rel=\"stylesheet\" href=\"calendar.css\">\n";
$HEADER.="<link rel=\"stylesheet\" href=\"horizontalbargraph.css\">\n";

$HEADER.="<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">\n";
$HEADER.="<TITLE>$report_name</TITLE></HEAD><BODY BGCOLOR=WHITE marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>\n";

$short_header=1;

# require("admin_header.php");

$MAIN.="<TABLE CELLPADDING=4 CELLSPACING=0><TR><TD>";

$MAIN.="<FORM ACTION=\"$PHP_SELF\" METHOD=GET name=vicidial_report id=vicidial_report>\n";
$MAIN.="<INPUT TYPE=TEXT NAME=query_date SIZE=10 MAXLENGTH=10 VALUE=\"$query_date\">";

$MAIN.="<script language=\"JavaScript\">\n";
$MAIN.="var o_cal = new tcal ({\n";
$MAIN.="	// form name\n";
$MAIN.="	'formname': 'vicidial_report',\n";
$MAIN.="	// input name\n";
$MAIN.="	'controlname': 'query_date'\n";
$MAIN.="});\n";
$MAIN.="o_cal.a_tpl.yearscroll = false;\n";
$MAIN.="// o_cal.a_tpl.weekstart = 1; // Montag week start\n";
$MAIN.="</script>\n";

$MAIN.=" to <INPUT TYPE=TEXT NAME=end_date SIZE=10 MAXLENGTH=10 VALUE=\"$end_date\">";

$MAIN.="<script language=\"JavaScript\">\n";
$MAIN.="var o_cal = new tcal ({\n";
$MAIN.="	// form name\n";
$MAIN.="	'formname': 'vicidial_report',\n";
$MAIN.="	// input name\n";
$MAIN.="	'controlname': 'end_date'\n";
$MAIN.="});\n";
$MAIN.="o_cal.a_tpl.yearscroll = false;\n";
$MAIN.="// o_cal.a_tpl.weekstart = 1; // Montag week start\n";
$MAIN.="</script>\n";

$MAIN.="<SELECT SIZE=1 NAME=group>\n";
	$o=0;
while ($groups_to_print > $o)
	{
	if ($groups[$o] == $group) {$MAIN.="<option selected value=\"$groups[$o]\">$groups[$o] - $group_names[$o]</option>\n";}
	else {$MAIN.="<option value=\"$groups[$o]\">$groups[$o] - $group_names[$o]</option>\n";}
	$o++;
	}
$MAIN.="</SELECT>\n";
$MAIN.=" &nbsp;";
$MAIN.="<select name='report_display_type'>";
if ($report_display_type) {$MAIN.="<option value='$report_display_type' selected>$report_display_type</option>";}
$MAIN.="<option value='TEXT'>TEXT</option><option value='HTML'>HTML</option></select>&nbsp; ";
$MAIN.="<SELECT SIZE=1 NAME=shift>\n";
$MAIN.="<option selected value=\"$shift\">$shift</option>\n";
$MAIN.="<option value=\"\">--</option>\n";
$MAIN.="<option value=\"AM\">AM</option>\n";
$MAIN.="<option value=\"PM\">PM</option>\n";
$MAIN.="<option value=\"ALL\">ALL</option>\n";
$MAIN.="<option value=\"DAYTIME\">DAYTIME</option>\n";
$MAIN.="<option value=\"10AM-6PM\">10AM-6PM</option>\n";
$MAIN.="<option value=\"9AM-1AM\">9AM-1AM</option>\n";
$MAIN.="<option value=\"845-1745\">845-1745</option>\n";
$MAIN.="<option value=\"1745-100\">1745-100</option>\n";
$MAIN.="</SELECT>\n";
$MAIN.="<INPUT TYPE=submit NAME=SUBMIT VALUE=SUBMIT>\n";
$MAIN.="<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href=\"$PHP_SELF?DB=$DB&query_date=$query_date&end_date=$end_date&group=$group&shift=$shift&hourly_breakdown=$hourly_breakdown&SUBMIT=$SUBMIT&file_download=1\">DOWNLOAD</a> | <a href=\"./admin.php?ADD=3111&group_id=$group\">ÄNDERN Sie</a> | <a href=\"./admin.php?ADD=999999\">REPORTS</a><BR><INPUT TYPE=checkbox NAME=hourly_breakdown VALUE='checked' $hourly_breakdown>Show hourly results</FONT>\n";
$MAIN.="</FORM>\n\n";

$MAIN.="<PRE><FONT SIZE=2>\n\n";


if (!$group)
	{
	$MAIN.="\n\n";
	$MAIN.="Wählen Sie eine IM-Gruppe und Zeitraum oberhalb und klicken Sie auf Senden\n";
	echo "$HEADER";
	require("admin_header.php");
	echo "$MAIN";
	}

else
	{
	### FOR SHIFTS IT IS BEST TO STICK TO 15-MINUTE INCREMENTS FOR START TIMES ###

	if ($shift == 'AM') 
		{
		if (strlen($time_BEGIN) < 6) {$time_BEGIN = "00:00:00";}   
		if (strlen($time_END) < 6) {$time_END = "11:59:59";}
		}
	if ($shift == 'PM') 
		{
		if (strlen($time_BEGIN) < 6) {$time_BEGIN = "12:00:00";}
		if (strlen($time_END) < 6) {$time_END = "23:59:59";}
		}
	if ($shift == 'ALL') 
		{
		if (strlen($time_BEGIN) < 6) {$time_BEGIN = "00:00:00";}
		if (strlen($time_END) < 6) {$time_END = "23:59:59";}
		}
	if ($shift == 'DAYTIME') 
		{
		if (strlen($time_BEGIN) < 6) {$time_BEGIN = "08:45:00";}
		if (strlen($time_END) < 6) {$time_END = "00:59:59";}
		}
	if ($shift == '10AM-6PM') 
		{
		if (strlen($time_BEGIN) < 6) {$time_BEGIN = "10:00:00";}
		if (strlen($time_END) < 6) {$time_END = "17:59:59";}
		}
	if ($shift == '9AM-1AM') 
		{
		if (strlen($time_BEGIN) < 6) {$time_BEGIN = "09:00:00";}
		if (strlen($time_END) < 6) {$time_END = "00:59:59";}
		}
	if ($shift == '845-1745') 
		{
		if (strlen($time_BEGIN) < 6) {$time_BEGIN = "08:45:00";}
		if (strlen($time_END) < 6) {$time_END = "17:44:59";}
		}
	if ($shift == '1745-100') 
		{
		if (strlen($time_BEGIN) < 6) {$time_BEGIN = "17:45:00";}
		if (strlen($time_END) < 6) {$time_END = "00:59:59";}
		}

	$time1 = strtotime($time_BEGIN);
	$time2 = strtotime($time_END)+1;

	$hpd = ceil(($time2 - $time1) / 3600);
	if ($hpd<0) {$hpd+=24;}


	$query_date_BEGIN = "$query_date $time_BEGIN";   
	$query_date_END = "$end_date $time_END";

	$SQdate_ARY =	explode(' ',$query_date_BEGIN);
	$SQday_ARY =	explode('-',$SQdate_ARY[0]);
	$SQtime_ARY =	explode(':',$SQdate_ARY[1]);
	$EQdate_ARY =	explode(' ',$query_date_END);
	$EQday_ARY =	explode('-',$EQdate_ARY[0]);
	$EQtime_ARY =	explode(':',$EQdate_ARY[1]);

	$SQepochDAY = mktime(0, 0, 0, $SQday_ARY[1], $SQday_ARY[2], $SQday_ARY[0]);
	$SQepoch = mktime($SQtime_ARY[0], $SQtime_ARY[1], $SQtime_ARY[2], $SQday_ARY[1], $SQday_ARY[2], $SQday_ARY[0]);
	$EQepoch = mktime($EQtime_ARY[0], $EQtime_ARY[1], $EQtime_ARY[2], $EQday_ARY[1], $EQday_ARY[2], $EQday_ARY[0]);

	$SQsec = ( ($SQtime_ARY[0] * 3600) + ($SQtime_ARY[1] * 60) + ($SQtime_ARY[2] * 1) );
	$EQsec = ( ($EQtime_ARY[0] * 3600) + ($EQtime_ARY[1] * 60) + ($EQtime_ARY[2] * 1) );

	$DURATIONsec = ($EQepoch - $SQepoch);
	$DURATIONday = intval( ($DURATIONsec / 86400) + 1 );

	if ( ($EQsec < $SQsec) and ($DURATIONday < 1) )
		{
		$EQepoch = ($SQepochDAY + ($EQsec + 86400) );
		$query_date_END = date("Y-m-d H:i:s", $EQepoch);
		$DURATIONday++;
		}

	$MAIN.="Inbound Daily Report                      $NOW_TIME\n";
	$MAIN.="Selected in-group: $group\n";
	$MAIN.="Time range $DURATIONday days: $query_date_BEGIN to $query_date_END for $shift shift\n\n";
	#echo "Time range day sec: $SQsec - $EQsec   Day range in epoch: $SQepoch - $EQepoch   Start: $SQepochDAY\n";
	$CSV_text.="\"Inbound Daily Report\",\"$NOW_TIME\"\n";
	$CSV_text.="Selected in-group: $group\n";
	$CSV_text.="\"Time range $DURATIONday days:\",\"$query_date_BEGIN to $query_date_END for $shift shift\"\n\n";

	$d=0; $q=0; $hr=0;
	while ($d < $DURATIONday)
		{
		$dSQepoch = ($SQepoch + ($d * 86400) + ($hr * 3600) );

		if ($hourly_breakdown) 
			{
			$dEQepoch = $dSQepoch+3599;
			}
			else
			{
			$dEQepoch = ($SQepochDAY + ($EQsec + ($d * 86400) + ($hr * 3600) ) );
			if ($EQsec < $SQsec)
				{
				$dEQepoch = ($dEQepoch + 86400);
				}
			}

		$daySTART[$q] = date("Y-m-d H:i:s", $dSQepoch);
		$dayEND[$q] = date("Y-m-d H:i:s", $dEQepoch);

	#  || $time_END<=date("H:i:s", $dEQepoch)
		if ($hr>=($hpd-1) || !$hourly_breakdown) 
			{
			$d++;
			$hr=0;
			if (date("H:i:s", $dEQepoch)>$time_END) 
				{
				$dayEND[$q] = date("Y-m-d ", $dEQepoch).$time_END;
				}
			}
			else
			{
			$hr++;
			}
		#$MAIN.="$daySTART[$q] - $dayEND[$q] | $SQepochDAY,".date("Y-m-d H:i:s",$SQepochDAY)."\n";
		$q++;

		}
	$prev_week=$daySTART[0];
	$prev_month=$daySTART[0];
	$prev_qtr=$daySTART[0];
	##########################################################################
	#########  CALCULATE ALL OF THE 15-MINUTE PERIODS NEEDED FOR ALL DAYS ####

	### BAU HOUR:MIN DISPLAY ARRAY ###
	$i=0;
	$h=4;
	$j=0;
	$Zhour=1;
	$active_time=0;
	$hour =		($SQtime_ARY[0] - 1);
	$startSEC = ($SQsec - 900);
	$endSEC =	($SQsec - 1);
	if ($SQtime_ARY[1] > 14) 
		{
		$h=1;
		$hour++;
		if ($hour < 10) {$hour = "0$hour";}
		}
	if ($SQtime_ARY[1] > 29) {$h=2;}
	if ($SQtime_ARY[1] > 44) {$h=3;}
	while ($i < 96)
		{
		$startSEC = ($startSEC + 900);
		$endSEC = ($endSEC + 900);
		$time = '      ';
		if ($h >= 4)
			{
			$hour++;
			if ($Zhour == '00') 
				{
				$startSEC=0;
				$endSEC=899;
				}
			$h=0;
			if ($hour < 10) {$hour = "0$hour";}
			$Stime="$hour:00";
			$Etime="$hour:15";
			$time = "+$Stime-$Etime+";
			}
		if ($h == 1)
			{
			$Stime="$hour:15";
			$Etime="$hour:30";
			$time = " $Stime-$Etime ";
			}
		if ($h == 2)
			{
			$Stime="$hour:30";
			$Etime="$hour:45";
			$time = " $Stime-$Etime ";
			}
		if ($h == 3)
			{
			$Zhour=$hour;
			$Zhour++;
			if ($Zhour < 10) {$Zhour = "0$Zhour";}
			if ($Zhour == 24) {$Zhour = "00";}
			$Stime="$hour:45";
			$Etime="$Zhour:00";
			$time = " $Stime-$Etime ";
			if ($Zhour == '00') 
				{$hour = ($Zhour - 1);}
			}

		if ( ( ($startSEC >= $SQsec) and ($endSEC <= $EQsec) and ($EQsec > $SQsec) ) or 
			( ($startSEC >= $SQsec) and ($EQsec < $SQsec) ) or 
			( ($endSEC <= $EQsec) and ($EQsec < $SQsec) ) )
			{
			$HMdisplay[$j] =	$time;
			$HMstart[$j] =		$Stime;
			$HMend[$j] =		$Etime;
			$HMSepoch[$j] =		$startSEC;
			$HMEepoch[$j] =		$endSEC;

			$j++;
			}

		$h++;
		$i++;
		}

	$TOTintervals = $q;


	### GRAB ALL RECORDS WITHIN RANGE FROM THE DATABASE ###
	$stmt="select queue_seconds,UNIX_TIMESTAMP(call_date),length_in_sec,status,term_reason,call_date from vicidial_closer_log where call_date >= '$query_date_BEGIN' and call_date <= '$query_date_END' and  campaign_id='" . mysql_real_escape_string($group) . "';";
	$rslt=mysql_query($stmt, $link);
	if ($DB) {$ASCII_text.="$stmt\n";}
	$records_to_grab = mysql_num_rows($rslt);
	$i=0;
	if($hourly_breakdown) {$epoch_interval=3600;} else {$epoch_interval=86400;}
	while ($i < $records_to_grab)
		{
		$row=mysql_fetch_row($rslt);
		$qs[$i] = $row[0];
		$dt[$i] = 0;
		$ut[$i] = ($row[1] - $SQepochDAY);
		while($ut[$i] >= $epoch_interval) 
			{
			$ut[$i] = ($ut[$i] - $epoch_interval);
			$dt[$i]++;
			}
		if ( ($ut[$i] <= $EQsec) and ($EQsec < $SQsec) )
			{
			$dt[$i] = ($dt[$i] - 1);
			}
		$ls[$i] = $row[2];
		$st[$i] = $row[3];
		$tr[$i] = $row[4];
		$at[$i] = $row[5]; # Actual time

		# $ASCII_text.= "$qs[$i] | $dt[$i] - $row[1] | $ut[$i] | $ls[$i] | $st[$i] | $tr[$i] | $at[$i]\n";

		$i++;
		}

	### PARSE THROUGH ALL RECORDS AND GENERATE STATS ###
	$MT[0]='0';
	$totCALLS=0;
	$totDROPS=0;
	$totQUEUE=0;
	$totCALLSsec=0;
	$totDROPSsec=0;
	$totQUEUEsec=0;
	$totCALLSmax=0;
	$totDROPSmax=0;
	$totQUEUEmax=0;
	$totCALLSdate=$MT;
	$totDROPSdate=$MT;
	$totQUEUEdate=$MT;
	$qrtCALLS=$MT;
	$qrtDROPS=$MT;
	$qrtQUEUE=$MT;
	$qrtCALLSsec=$MT;
	$qrtDROPSsec=$MT;
	$qrtQUEUEsec=$MT;
	$qrtCALLSavg=$MT;
	$qrtDROPSavg=$MT;
	$qrtQUEUEavg=$MT;
	$qrtCALLSmax=$MT;
	$qrtDROPSmax=$MT;
	$qrtQUEUEmax=$MT;

	$totABANDONSdate=$MT;
	$totANTWORTSdate=$MT;

	$totANTWORTS=0;
	$totABANDONS=0;
	$totANTWORTSsec=0;
	$totABANDONSsec=0;
	$totANTWORTSspeed=0;

	$FtotANTWORTS=0;
	$FtotABANDONS=0;
	$FtotANTWORTSsec=0;
	$FtotABANDONSsec=0;
	$FtotANTWORTSspeed=0;

	$j=0;
	while ($j < $TOTintervals)
		{
	#	$jd__0[$j]=0; $jd_20[$j]=0; $jd_40[$j]=0; $jd_60[$j]=0; $jd_80[$j]=0; $jd100[$j]=0; $jd120[$j]=0; $jd121[$j]=0;
	#	$Phd__0[$j]=0; $Phd_20[$j]=0; $Phd_40[$j]=0; $Phd_60[$j]=0; $Phd_80[$j]=0; $Phd100[$j]=0; $Phd120[$j]=0; $Phd121[$j]=0;
	#	$qrtCALLS[$j]=0; $qrtCALLSsec[$j]=0; $qrtCALLSmax[$j]=0;
	#	$qrtDROPS[$j]=0; $qrtDROPSsec[$j]=0; $qrtDROPSmax[$j]=0;
	#	$qrtQUEUE[$j]=0; $qrtQUEUEsec[$j]=0; $qrtQUEUEmax[$j]=0;
		$totABANDONSdate[$j]=0;
		$totABANDONSsecdate[$j]=0;
		$totANTWORTSdate[$j]=0;
		$totANTWORTSsecdate[$j]=0;
		$totANTWORTSspeeddate[$j]=0;
		$i=0;
		while ($i < $records_to_grab)
			{
			if ( ($at[$i] >= $daySTART[$j]) and ($at[$i] <= $dayEND[$j]) )
				{
				$totCALLS++;
				$totCALLSsec = ($totCALLSsec + $ls[$i]);
				$totCALLSsecDATE[$j] = ($totCALLSsecDATE[$j] + $ls[$i]);
	#			$qrtCALLS[$j]++;
	#			$qrtCALLSsec[$j] = ($qrtCALLSsec[$j] + $ls[$i]);
	#			$dtt = $dt[$i];
				$totCALLSdate[$j]++;
				if ($totCALLSmax < $ls[$i]) {$totCALLSmax = $ls[$i];}
				if ($qrtCALLSmax[$j] < $ls[$i]) {$qrtCALLSmax[$j] = $ls[$i];}
				if (ereg('ABANDON|NOAGENT|QUEUETIMEOUT|AFTERHOURS|MAXCALLS', $tr[$i])) 
					{
					$totABANDONSdate[$j]++;
					$totABANDONSsecdate[$j]+=$ls[$i];
					$FtotABANDONS++;
					$FtotABANDONSsec+=$ls[$i];
					}
					else 
					{
					$totANTWORTSdate[$j]++;
					$totANTWORTSsecdate[$j]+=($ls[$i]-$qs[$i]-15);
					$totANTWORTSspeeddate[$j]+=$qs[$i];
					$FtotANTWORTS++;
					$FtotANTWORTSsec+=($ls[$i]-$qs[$i]-15);
					$FtotANTWORTSspeeddate+=$qs[$i];
					}
				if (ereg('DROP',$st[$i])) 
					{
					$totDROPS++;
					$totDROPSsec = ($totDROPSsec + $ls[$i]);
					$totDROPSsecDATE[$j] = ($totDROPSsecDATE[$j] + $ls[$i]);
	#				$qrtDROPS[$j]++;
	#				$qrtDROPSsec[$j] = ($qrtDROPSsec[$j] + $ls[$i]);
					$totDROPSdate[$j]++;
	#				if ($totDROPSmax < $ls[$i]) {$totDROPSmax = $ls[$i];}
	#				if ($qrtDROPSmax[$j] < $ls[$i]) {$qrtDROPSmax[$j] = $ls[$i];}
					}
				if ($qs[$i] > 0) 
					{
					$totQUEUE++;
					$totQUEUEsec = ($totQUEUEsec + $qs[$i]);
					$totQUEUEsecDATE[$j] = ($totQUEUEsecDATE[$j] + $qs[$i]);
	#				$qrtQUEUE[$j]++;
	#				$qrtQUEUEsec[$j] = ($qrtQUEUEsec[$j] + $qs[$i]);
					$totQUEUEdate[$j]++;
	#				if ($totQUEUEmax < $qs[$i]) {$totQUEUEmax = $qs[$i];}
	#				if ($qrtQUEUEmax[$j] < $qs[$i]) {$qrtQUEUEmax[$j] = $qs[$i];}
					}
	/*
				if ($qs[$i] == 0) {$hd__0[$j]++;}
				if ( ($qs[$i] > 0) and ($qs[$i] <= 20) ) {$hd_20[$j]++;}
				if ( ($qs[$i] > 20) and ($qs[$i] <= 40) ) {$hd_40[$j]++;}
				if ( ($qs[$i] > 40) and ($qs[$i] <= 60) ) {$hd_60[$j]++;}
				if ( ($qs[$i] > 60) and ($qs[$i] <= 80) ) {$hd_80[$j]++;}
				if ( ($qs[$i] > 80) and ($qs[$i] <= 100) ) {$hd100[$j]++;}
				if ( ($qs[$i] > 100) and ($qs[$i] <= 120) ) {$hd120[$j]++;}
				if ($qs[$i] > 120) {$hd121[$j]++;}
	*/
				}
			
			$i++;
			}

		$j++;
		}


	###################################################
	### TOTALS SUMMARY SECTION ###
	$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
	$ASCII_text.="|                                           | TOTAL   | TOTAL    | TOTAL     | TOTAL   | AVG     | AVG    | AVG    | TOTAL      | TOTAL      | TOTAL      |\n";
	$ASCII_text.="| SHIFT                                     | CALLS   | CALLS    | CALLS     | ABANDON | ABANDON | ANTWORT | TALK   | TALK       | WRAP       | CALL       |\n";
	$ASCII_text.="| DATE-TIME RANGE                           | OFFERED | ANTWORTED | ABANDONED | PERCENT | TIME    | SPEED  | TIME   | TIME       | TIME       | TIME       |\n";
	$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
	$CSV_text.="\"\",\"TOTAL\",\"TOTAL\",\"TOTAL\",\"TOTAL\",\"AVG\",\"AVG\",\"AVG\",\"TOTAL\",\"TOTAL\",\"TOTAL\"\n";
	$CSV_text.="\"\",\"CALLS\",\"CALLS\",\"CALLS\",\"ABANDON\",\"ABANDON\",\"ANTWORT\",\"TALK\",\"TALK\",\"WRAP\",\"CALL\"\n";
	$CSV_text.="\"SHIFT DATE-TIME RANGE\",\"OFFERED\",\"ANTWORTED\",\"ABANDONED\",\"PERCENT\",\"TIME\",\"SPEED\",\"TIME\",\"TIME\",\"TIME\",\"TIME\"\n";

	##########################
	$JS_text="<script language='Javascript'>\n";
	$JS_onload="onload = function() {\n";
	$graph_stats=array();
	$mtd_graph_stats=array();
	$wtd_graph_stats=array();
	$qtd_graph_stats=array();
	$da=0; $wa=0; $ma=0; $qa=0;
	$max_offered=1;
	$max_answered=1;
	$max_abandoned=1;
	$max_abandonpct=1;
	$max_avgabandontime=1;
	$max_avganswerspeed=1;
	$max_avgtalktime=1;
	$max_totaltalktime=1;
	$max_totalwraptime=1;
	$max_totalcalltime=1;
	$max_wtd_offered=1;
	$max_wtd_answered=1;
	$max_wtd_abandoned=1;
	$max_wtd_abandonpct=1;
	$max_wtd_avgabandontime=1;
	$max_wtd_avganswerspeed=1;
	$max_wtd_avgtalktime=1;
	$max_wtd_totaltalktime=1;
	$max_wtd_totalwraptime=1;
	$max_wtd_totalcalltime=1;
	$max_mtd_offered=1;
	$max_mtd_answered=1;
	$max_mtd_abandoned=1;
	$max_mtd_abandonpct=1;
	$max_mtd_avgabandontime=1;
	$max_mtd_avganswerspeed=1;
	$max_mtd_avgtalktime=1;
	$max_mtd_totaltalktime=1;
	$max_mtd_totalwraptime=1;
	$max_mtd_totalcalltime=1;
	$max_qtd_offered=1;
	$max_qtd_answered=1;
	$max_qtd_abandoned=1;
	$max_qtd_abandonpct=1;
	$max_qtd_avgabandontime=1;
	$max_qtd_avganswerspeed=1;
	$max_qtd_avgtalktime=1;
	$max_qtd_totaltalktime=1;
	$max_qtd_totalwraptime=1;
	$max_qtd_totalcalltime=1;
	$GRAPH="<a name='multigroup_graph'/><table border='0' cellpadding='0' cellspacing='2' width='800'><tr><th width='10%' class='grey_graph_cell' id='multigroup_graph1'><a href='#' onClick=\"DrawGraph('OFFERED', '1'); return false;\">GESAMTANRUFEOFFERED</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph2'><a href='#' onClick=\"DrawGraph('ANTWORTED', '2'); return false;\">GESAMTANRUFEANTWORTED</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph3'><a href='#' onClick=\"DrawGraph('ABANDONED', '3'); return false;\">GESAMTANRUFEABANDONED</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph4'><a href='#' onClick=\"DrawGraph('ABANDONPCT', '4'); return false;\">TOTAL ABANDON PERCENT</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph5'><a href='#' onClick=\"DrawGraph('AVGABANDONTIME', '5'); return false;\">AVG ABANDON TIME</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph6'><a href='#' onClick=\"DrawGraph('AVGANTWORTSPEED', '6'); return false;\">AVG ANTWORT SPEED</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph7'><a href='#' onClick=\"DrawGraph('AVGTALKTIME', '7'); return false;\">AVG TALK TIME</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph8'><a href='#' onClick=\"DrawGraph('TOTALTALKTIME', '8'); return false;\">TOTAL TALK TIME</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph9'><a href='#' onClick=\"DrawGraph('TOTALWRAPTIME', '9'); return false;\">TOTAL WRAP TIME</a></th><th width='10%' class='grey_graph_cell' id='multigroup_graph10'><a href='#' onClick=\"DrawGraph('TOTALCALLTIME', '10'); return false;\">TOTAL CALL TIME</a></th></tr><tr><td colspan='10' class='graph_span_cell' align='center'><span id='stats_graph'><BR>&nbsp;<BR></span></td></tr></table><BR><BR>";
	$MTD_GRAPH="<BR><BR><a name='MTD_graph'/><table border='0' cellpadding='0' cellspacing='2' width='800'><tr><th width='10%' class='grey_graph_cell' id='MTD_graph1'><a href='#' onClick=\"DrawMTDGraph('OFFERED', '1'); return false;\">GESAMTANRUFEOFFERED</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph2'><a href='#' onClick=\"DrawMTDGraph('ANTWORTED', '2'); return false;\">GESAMTANRUFEANTWORTED</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph3'><a href='#' onClick=\"DrawMTDGraph('ABANDONED', '3'); return false;\">GESAMTANRUFEABANDONED</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph4'><a href='#' onClick=\"DrawMTDGraph('ABANDONPCT', '4'); return false;\">TOTAL ABANDON PERCENT</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph5'><a href='#' onClick=\"DrawMTDGraph('AVGABANDONTIME', '5'); return false;\">AVG ABANDON TIME</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph6'><a href='#' onClick=\"DrawMTDGraph('AVGANTWORTSPEED', '6'); return false;\">AVG ANTWORT SPEED</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph7'><a href='#' onClick=\"DrawMTDGraph('AVGTALKTIME', '7'); return false;\">AVG TALK TIME</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph8'><a href='#' onClick=\"DrawMTDGraph('TOTALTALKTIME', '8'); return false;\">TOTAL TALK TIME</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph9'><a href='#' onClick=\"DrawMTDGraph('TOTALWRAPTIME', '9'); return false;\">TOTAL WRAP TIME</a></th><th width='10%' class='grey_graph_cell' id='MTD_graph10'><a href='#' onClick=\"DrawMTDGraph('TOTALCALLTIME', '10'); return false;\">TOTAL CALL TIME</a></th></tr><tr><td colspan='10' class='graph_span_cell' align='center'><span id='MTD_stats_graph'><BR>&nbsp;<BR></span></td></tr></table><BR><BR>";
	$WTD_GRAPH="<BR><BR><a name='WTD_graph'/><table border='0' cellpadding='0' cellspacing='2' width='800'><tr><th width='10%' class='grey_graph_cell' id='WTD_graph1'><a href='#' onClick=\"DrawWTDGraph('OFFERED', '1'); return false;\">GESAMTANRUFEOFFERED</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph2'><a href='#' onClick=\"DrawWTDGraph('ANTWORTED', '2'); return false;\">GESAMTANRUFEANTWORTED</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph3'><a href='#' onClick=\"DrawWTDGraph('ABANDONED', '3'); return false;\">GESAMTANRUFEABANDONED</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph4'><a href='#' onClick=\"DrawWTDGraph('ABANDONPCT', '4'); return false;\">TOTAL ABANDON PERCENT</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph5'><a href='#' onClick=\"DrawWTDGraph('AVGABANDONTIME', '5'); return false;\">AVG ABANDON TIME</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph6'><a href='#' onClick=\"DrawWTDGraph('AVGANTWORTSPEED', '6'); return false;\">AVG ANTWORT SPEED</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph7'><a href='#' onClick=\"DrawWTDGraph('AVGTALKTIME', '7'); return false;\">AVG TALK TIME</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph8'><a href='#' onClick=\"DrawWTDGraph('TOTALTALKTIME', '8'); return false;\">TOTAL TALK TIME</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph9'><a href='#' onClick=\"DrawWTDGraph('TOTALWRAPTIME', '9'); return false;\">TOTAL WRAP TIME</a></th><th width='10%' class='grey_graph_cell' id='WTD_graph10'><a href='#' onClick=\"DrawWTDGraph('TOTALCALLTIME', '10'); return false;\">TOTAL CALL TIME</a></th></tr><tr><td colspan='10' class='graph_span_cell' align='center'><span id='WTD_stats_graph'><BR>&nbsp;<BR></span></td></tr></table><BR><BR>";
	$QTD_GRAPH="<BR><BR><a name='QTD_graph'/><table border='0' cellpadding='0' cellspacing='2' width='800'><tr><th width='10%' class='grey_graph_cell' id='QTD_graph1'><a href='#' onClick=\"DrawQTDGraph('OFFERED', '1'); return false;\">GESAMTANRUFEOFFERED</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph2'><a href='#' onClick=\"DrawQTDGraph('ANTWORTED', '2'); return false;\">GESAMTANRUFEANTWORTED</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph3'><a href='#' onClick=\"DrawQTDGraph('ABANDONED', '3'); return false;\">GESAMTANRUFEABANDONED</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph4'><a href='#' onClick=\"DrawQTDGraph('ABANDONPCT', '4'); return false;\">TOTAL ABANDON PERCENT</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph5'><a href='#' onClick=\"DrawQTDGraph('AVGABANDONTIME', '5'); return false;\">AVG ABANDON TIME</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph6'><a href='#' onClick=\"DrawQTDGraph('AVGANTWORTSPEED', '6'); return false;\">AVG ANTWORT SPEED</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph7'><a href='#' onClick=\"DrawQTDGraph('AVGTALKTIME', '7'); return false;\">AVG TALK TIME</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph8'><a href='#' onClick=\"DrawQTDGraph('TOTALTALKTIME', '8'); return false;\">TOTAL TALK TIME</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph9'><a href='#' onClick=\"DrawQTDGraph('TOTALWRAPTIME', '9'); return false;\">TOTAL WRAP TIME</a></th><th width='10%' class='grey_graph_cell' id='QTD_graph10'><a href='#' onClick=\"DrawQTDGraph('TOTALCALLTIME', '10'); return false;\">TOTAL CALL TIME</a></th></tr><tr><td colspan='10' class='graph_span_cell' align='center'><span id='QTD_stats_graph'><BR>&nbsp;<BR></span></td></tr></table><BR><BR>";
	
	$graph_header="<table cellspacing='0' cellpadding='0' class='horizontalgraph'><caption align='top'>DAILY RPT - $query_date_BEGIN to $query_date_END</caption><tr><th class='thgraph' scope='col'>DATE/TIME RANGE</th>";
	$OFFERED_graph=$graph_header."<th class='thgraph' scope='col'>GESAMTANRUFEOFFERED</th></tr>";
	$ANTWORTED_graph=$graph_header."<th class='thgraph' scope='col'>GESAMTANRUFEANTWORTED </th></tr>";
	$ABANDONED_graph=$graph_header."<th class='thgraph' scope='col'>GESAMTANRUFEABANDONED</th></tr>";
	$ABANDONPCT_graph=$graph_header."<th class='thgraph' scope='col'>TOTAL ABANDON PERCENT</th></tr>";
	$AVGABANDONTIME_graph=$graph_header."<th class='thgraph' scope='col'>AVG ABANDON TIME</th></tr>";
	$AVGANTWORTSPEED_graph=$graph_header."<th class='thgraph' scope='col'>AVG ANTWORT SPEED</th></tr>";
	$AVGTALKTIME_graph=$graph_header."<th class='thgraph' scope='col'>AVG TALK TIME</th></tr>";
	$TOTALTALKTIME_graph=$graph_header."<th class='thgraph' scope='col'>TOTAL TALK TIME</th></tr>";
	$TOTALWRAPTIME_graph=$graph_header."<th class='thgraph' scope='col'>TOTAL WRAP TIME</th></tr>";
	$TOTALCALLTIME_graph=$graph_header."<th class='thgraph' scope='col'>TOTAL CALL TIME</th></tr>";
	##########################

	$totCALLSwtd=0;
	$totANTWORTSwtd=0;
	$totANTWORTSsecwtd=0;
	$totANTWORTSspeedwtd=0;
	$totABANDONSwtd=0;
	$totABANDONSsecwtd=0;

	$totCALLSmtd=0;
	$totANTWORTSmtd=0;
	$totANTWORTSsecmtd=0;
	$totANTWORTSspeedmtd=0;
	$totABANDONSmtd=0;
	$totABANDONSsecmtd=0;

	$totCALLSqtd=0;
	$totANTWORTSqtd=0;
	$totANTWORTSsecqtd=0;
	$totANTWORTSspeedqtd=0;
	$totABANDONSqtd=0;
	$totABANDONSsecqtd=0;

	$d=0;
	while ($d < $TOTintervals)
		{
		if ($totDROPSdate[$d] < 1) {$totDROPSdate[$d]=0;}
		if ($totQUEUEdate[$d] < 1) {$totQUEUEdate[$d]=0;}
		if ($totCALLSdate[$d] < 1) {$totCALLSdate[$d]=0;}

		if ($totDROPSdate[$d] > 0)
			{$totDROPSpctDATE[$d] = ( ($totDROPSdate[$d] / $totCALLSdate[$d]) * 100);}
		else {$totDROPSpctDATE[$d] = 0;}
		$totDROPSpctDATE[$d] = round($totDROPSpctDATE[$d], 2);
		if ($totQUEUEdate[$d] > 0)
			{$totQUEUEpctDATE[$d] = ( ($totQUEUEdate[$d] / $totCALLSdate[$d]) * 100);}
		else {$totQUEUEpctDATE[$d] = 0;}
		$totQUEUEpctDATE[$d] = round($totQUEUEpctDATE[$d], 2);

		if ($totDROPSsecDATE[$d] > 0)
			{$totDROPSavgDATE[$d] = ($totDROPSsecDATE[$d] / $totDROPSdate[$d]);}
		else {$totDROPSavgDATE[$d] = 0;}
		if ($totQUEUEsecDATE[$d] > 0)
			{$totQUEUEavgDATE[$d] = ($totQUEUEsecDATE[$d] / $totQUEUEdate[$d]);}
		else {$totQUEUEavgDATE[$d] = 0;}
		if ($totQUEUEsecDATE[$d] > 0)
			{$totQUEUEtotDATE[$d] = ($totQUEUEsecDATE[$d] / $totCALLSdate[$d]);}
		else {$totQUEUEtotDATE[$d] = 0;}

		if ($totCALLSsecDATE[$d] > 0)
			{
			$totCALLSavgDATE[$d] = ($totCALLSsecDATE[$d] / $totCALLSdate[$d]);

			$totTIME_M = ($totCALLSsecDATE[$d] / 60);
			$totTIME_M_int = round($totTIME_M, 2);
			$totTIME_M_int = intval("$totTIME_M");
			$totTIME_S = ($totTIME_M - $totTIME_M_int);
			$totTIME_S = ($totTIME_S * 60);
			$totTIME_S = round($totTIME_S, 0);
			if ($totTIME_S < 10) {$totTIME_S = "0$totTIME_S";}
			$totTIME_MS = "$totTIME_M_int:$totTIME_S";
			$totTIME_MS =		sprintf("%8s", $totTIME_MS);
			}
		else 
			{
			$totCALLSavgDATE[$d] = 0;
			$totTIME_MS='        ';
			}
	/*
		$totCALLSavgDATE[$d] =	sprintf("%6.0f", $totCALLSavgDATE[$d]);
		$totDROPSavgDATE[$d] =	sprintf("%7.2f", $totDROPSavgDATE[$d]);
		$totQUEUEavgDATE[$d] =	sprintf("%7.2f", $totQUEUEavgDATE[$d]);
		$totQUEUEtotDATE[$d] =	sprintf("%7.2f", $totQUEUEtotDATE[$d]);
		$totDROPSpctDATE[$d] =	sprintf("%6.2f", $totDROPSpctDATE[$d]);
		$totQUEUEpctDATE[$d] =	sprintf("%6.2f", $totQUEUEpctDATE[$d]);
		$totDROPSdate[$d] =	sprintf("%6s", $totDROPSdate[$d]);
		$totQUEUEdate[$d] =	sprintf("%6s", $totQUEUEdate[$d]);
	*/	$totCALLSdate[$d] =	sprintf("%7s", $totCALLSdate[$d]);

		if ($totCALLSdate[$d]>0)
			{
			$totABANDONSpctDATE[$d] =	sprintf("%7.2f", (100*$totABANDONSdate[$d]/$totCALLSdate[$d]));
			}
		else
			{
			$totCALLSdate[$d]="      0";
			$totABANDONSpctDATE[$d] = "    0.0";
			}
		if ($totABANDONSdate[$d]>0)
			{
			$totABANDONSavgTIME[$d] =	sprintf("%7s", date("i:s", mktime(0, 0, round($totABANDONSsecdate[$d]/$totABANDONSdate[$d]))));
			if (round($totABANDONSsecdate[$d]/$totABANDONSdate[$d])>$max_avgabandontime) {$max_avgabandontime=round($totABANDONSsecdate[$d]/$totABANDONSdate[$d]);}
			$graph_stats[$d][11]=round($totABANDONSsecdate[$d]/$totABANDONSdate[$d]);
			}
		else
			{
			$totABANDONSdate[$d]="0";
			$totABANDONSavgTIME[$d] = "  00:00";
			$graph_stats[$d][11]=0;
			}
		if ($totANTWORTSdate[$d]>0)
			{
			$totANTWORTSavgspeedTIME[$d] =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSspeeddate[$d]/$totANTWORTSdate[$d]))));
			$totANTWORTSavgTIME[$d] =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSsecdate[$d]/$totANTWORTSdate[$d]))));
			if (round($totANTWORTSspeeddate[$d]/$totANTWORTSdate[$d])>$max_avganswerspeed) {$max_avganswerspeed=round($totANTWORTSspeeddate[$d]/$totANTWORTSdate[$d]);}
			$graph_stats[$d][12]=round($totANTWORTSspeeddate[$d]/$totANTWORTSdate[$d]);
			$graph_stats[$d][16]=round($totANTWORTSsecdate[$d]/$totANTWORTSdate[$d]);
			}
		else
			{
			$totANTWORTSdate[$d]="0";
			$totANTWORTSavgspeedTIME[$d] = " 00:00";
			$totANTWORTSavgTIME[$d] = " 00:00";
			$graph_stats[$d][12]=0;
			$graph_stats[$d][16]=0;
			}
		$totANTWORTStalkTIME[$d] =	sprintf("%10s", floor($totANTWORTSsecdate[$d]/3600).date(":i:s", mktime(0, 0, $totANTWORTSsecdate[$d])));
		$totANTWORTSwrapTIME[$d] =	sprintf("%10s", floor(($totANTWORTSdate[$d]*15)/3600).date(":i:s", mktime(0, 0, ($totANTWORTSdate[$d]*15))));
		if (($totANTWORTSdate[$d]*15)>$max_totalwraptime) {$max_totalwraptime=($totANTWORTSdate[$d]*15);}
		$graph_stats[$d][13]=($totANTWORTSdate[$d]*15);
		$graph_stats[$d][14]=($totANTWORTSsecdate[$d]+($totANTWORTSdate[$d]*15));
		$graph_stats[$d][15]=$totANTWORTSsecdate[$d];

		$totANTWORTStotTIME[$d] =	sprintf("%10s", floor(($totANTWORTSsecdate[$d]+($totANTWORTSdate[$d]*15))/3600).date(":i:s", mktime(0, 0, ($totANTWORTSsecdate[$d]+($totANTWORTSdate[$d]*15)))));
		$totANTWORTSdate[$d] =	sprintf("%8s", $totANTWORTSdate[$d]);
		$totABANDONSdate[$d] =	sprintf("%9s", $totABANDONSdate[$d]);

		if (date("w", strtotime($daySTART[$d]))==0 && date("w", strtotime($daySTART[$d-1]))!=0 && $d>0) 
			{  # 2nd date/"w" check is for DST
			if ($totCALLSwtd>0)
				{
				$totABANDONSpctwtd =	sprintf("%7.2f", (100*$totABANDONSwtd/$totCALLSwtd));
				}
			else
				{
				$totABANDONSpctwtd = "    0.0";
				}
			if ($totABANDONSwtd>0)
				{
				$totABANDONSavgTIMEwtd =	sprintf("%7s", date("i:s", mktime(0, 0, round($totABANDONSsecwtd/$totABANDONSwtd))));
				if (round($totABANDONSsecwtd/$totABANDONSwtd)>$max_wtd_avgabandontime) {$max_wtd_avgabandontime=round($totABANDONSsecwtd/$totABANDONSwtd);}
				$wtd_graph_stats[$wa][11]=round($totABANDONSsecwtd/$totABANDONSwtd);
				}
			else
				{
				$totABANDONSavgTIMEwtd = "  00:00";
				$wtd_graph_stats[$wa][11]=0;
				}
			if ($totANTWORTSwtd>0)
				{
				$totANTWORTSavgspeedTIMEwtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSspeedwtd/$totANTWORTSwtd))));
				$totANTWORTSavgTIMEwtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSsecwtd/$totANTWORTSwtd))));
				if (round($totANTWORTSspeedwtd/$totANTWORTSwtd)>$max_wtd_avganswerspeed) {$max_wtd_avganswerspeed=round($totANTWORTSspeedwtd/$totANTWORTSwtd);}
				$wtd_graph_stats[$wa][12]=round($totANTWORTSspeedwtd/$totANTWORTSwtd);
				$wtd_graph_stats[$wa][16]=round($totANTWORTSsecwtd/$totANTWORTSwtd);
				}
			else
				{
				$totANTWORTSavgspeedTIMEwtd = " 00:00";
				$totANTWORTSavgTIMEwtd = " 00:00";
				$wtd_graph_stats[$wa][12]=0;
				$wtd_graph_stats[$wa][16]=0;
				}
			$totANTWORTStalkTIMEwtd =	sprintf("%10s", floor($totANTWORTSsecwtd/3600).date(":i:s", mktime(0, 0, $totANTWORTSsecwtd)));
			$totANTWORTSwrapTIMEwtd =	sprintf("%10s", floor(($totANTWORTSwtd*15)/3600).date(":i:s", mktime(0, 0, ($totANTWORTSwtd*15))));
			if (($totANTWORTSwtd*15)>$max_wtd_totalwraptime) {$max_wtd_totalwraptime=($totANTWORTSwtd*15);}
			$wtd_graph_stats[$wa][13]=($totANTWORTSwtd*15);
			$wtd_graph_stats[$wa][14]=($totANTWORTSsecwtd+($totANTWORTSwtd*15));
			$wtd_graph_stats[$wa][15]=$totANTWORTSsecwtd;
			$totANTWORTStotTIMEwtd =	sprintf("%10s", floor(($totANTWORTSsecwtd+($totANTWORTSwtd*15))/3600).date(":i:s", mktime(0, 0, ($totANTWORTSsecwtd+($totANTWORTSwtd*15)))));
			$totANTWORTSwtd =	sprintf("%8s", $totANTWORTSwtd);
			$totABANDONSwtd =	sprintf("%9s", $totABANDONSwtd);
			$totCALLSwtd =	sprintf("%7s", $totCALLSwtd);		

			if (trim($totCALLSwtd)>$max_wtd_offered) {$max_wtd_offered=trim($totCALLSwtd);}
			if (trim($totANTWORTSwtd)>$max_wtd_answered) {$max_wtd_answered=trim($totANTWORTSwtd);}
			if (trim($totABANDONSwtd)>$max_wtd_abandoned) {$max_wtd_abandoned=trim($totABANDONSwtd);}
			if (trim($totABANDONSpctwtd)>$max_wtd_abandonpct) {$max_wtd_abandonpct=trim($totABANDONSpctwtd);}

			if (round($totANTWORTSsecwtd/$totANTWORTSwtd)>$max_wtd_avgtalktime) {$max_wtd_avgtalktime=round($totANTWORTSsecwtd/$totANTWORTSwtd);}
			if (trim($totANTWORTSsecwtd)>$max_wtd_totaltalktime) {$max_wtd_totaltalktime=trim($totANTWORTSsecwtd);}
			if (trim($totANTWORTSsecwtd+($totANTWORTSwtd*15))>$max_wtd_totalcalltime) {$max_wtd_totalcalltime=trim($totANTWORTSsecwtd+($totANTWORTSwtd*15));}
			$week=date("W", strtotime($dayEND[$d-1]));
			$year=substr($dayEND[$d-1],0,4);
			$wtd_graph_stats[$wa][0]="Week $week, $year";
			$wtd_graph_stats[$wa][1]=trim($totCALLSwtd);
			$wtd_graph_stats[$wa][2]=trim($totANTWORTSwtd);
			$wtd_graph_stats[$wa][3]=trim($totABANDONSwtd);
			$wtd_graph_stats[$wa][4]=trim($totABANDONSpctwtd);
			$wtd_graph_stats[$wa][5]=trim($totABANDONSavgTIMEwtd);
			$wtd_graph_stats[$wa][6]=trim($totANTWORTSavgspeedTIMEwtd);
			$wtd_graph_stats[$wa][7]=trim($totANTWORTSavgTIMEwtd);
			$wtd_graph_stats[$wa][8]=trim($totANTWORTStalkTIMEwtd);
			$wtd_graph_stats[$wa][9]=trim($totANTWORTSwrapTIMEwtd);
			$wtd_graph_stats[$wa][10]=trim($totANTWORTStotTIMEwtd);
			$wa++;

			$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$ASCII_text.="|                                       WTD | $totCALLSwtd | $totANTWORTSwtd | $totABANDONSwtd | $totABANDONSpctwtd%| $totABANDONSavgTIMEwtd | $totANTWORTSavgspeedTIMEwtd | $totANTWORTSavgTIMEwtd | $totANTWORTStalkTIMEwtd | $totANTWORTSwrapTIMEwtd | $totANTWORTStotTIMEwtd |\n";
			$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$CSV_text.="\"WTD\",\"$totCALLSwtd\",\"$totANTWORTSwtd\",\"$totABANDONSwtd\",\"$totABANDONSpctwtd%\",\"$totABANDONSavgTIMEwtd\",\"$totANTWORTSavgspeedTIMEwtd\",\"$totANTWORTSavgTIMEwtd\",\"$totANTWORTStalkTIMEwtd\",\"$totANTWORTSwrapTIMEwtd\",\"$totANTWORTStotTIMEwtd\"\n";
			$totCALLSwtd=0;
			$totANTWORTSwtd=0;
			$totANTWORTSsecwtd=0;
			$totANTWORTSspeedwtd=0;
			$totABANDONSwtd=0;
			$totABANDONSsecwtd=0;
		}

		if (date("d", strtotime($daySTART[$d]))==1 && $d>0 && date("d", strtotime($daySTART[$d-1]))!=1) {
			if ($totCALLSmtd>0)
				{
				$totABANDONSpctmtd =	sprintf("%7.2f", (100*$totABANDONSmtd/$totCALLSmtd));
				}
			else
				{
				$totABANDONSpctmtd = "    0.0";
				}
			if ($totABANDONSmtd>0)
				{
				$totABANDONSavgTIMEmtd =	sprintf("%7s", date("i:s", mktime(0, 0, round($totABANDONSsecmtd/$totABANDONSmtd))));
				if (round($totABANDONSsecmtd/$totABANDONSmtd)>$max_mtd_avgabandontime) {$max_mtd_avgabandontime=round($totABANDONSsecmtd/$totABANDONSmtd);}
				$mtd_graph_stats[$ma][11]=round($totABANDONSsecmtd/$totABANDONSmtd);
				}
			else
				{
				$totABANDONSavgTIMEmtd = "  00:00";
				$mtd_graph_stats[$ma][11]=0;
				}
			if ($totANTWORTSmtd>0)
				{
				$totANTWORTSavgspeedTIMEmtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSspeedmtd/$totANTWORTSmtd))));
				$totANTWORTSavgTIMEmtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSsecmtd/$totANTWORTSmtd))));
				if (round($totANTWORTSspeedmtd/$totANTWORTSmtd)>$max_mtd_avganswerspeed) {$max_mtd_avganswerspeed=round($totANTWORTSspeedmtd/$totANTWORTSmtd);}
				$mtd_graph_stats[$ma][12]=round($totANTWORTSspeedmtd/$totANTWORTSmtd);
				$mtd_graph_stats[$ma][16]=round($totANTWORTSsecmtd/$totANTWORTSmtd);
				}
			else
				{
				$totANTWORTSavgspeedTIMEmtd = " 00:00";
				$totANTWORTSavgTIMEmtd = " 00:00";
				$mtd_graph_stats[$ma][12]=0;
				$mtd_graph_stats[$ma][16]=0;
				}
			$totANTWORTStalkTIMEmtd =	sprintf("%10s", floor($totANTWORTSsecmtd/3600).date(":i:s", mktime(0, 0, $totANTWORTSsecmtd)));
			$totANTWORTSwrapTIMEmtd =	sprintf("%10s", floor(($totANTWORTSmtd*15)/3600).date(":i:s", mktime(0, 0, ($totANTWORTSmtd*15))));
			if (($totANTWORTSmtd*15)>$max_mtd_totalwraptime) {$max_mtd_totalwraptime=($totANTWORTSmtd*15);}
			$mtd_graph_stats[$ma][13]=($totANTWORTSmtd*15);
			$mtd_graph_stats[$ma][14]=($totANTWORTSsecmtd+($totANTWORTSmtd*15));
			$mtd_graph_stats[$ma][15]=$totANTWORTSsecmtd;
			$totANTWORTStotTIMEmtd =	sprintf("%10s", floor(($totANTWORTSsecmtd+($totANTWORTSmtd*15))/3600).date(":i:s", mktime(0, 0, ($totANTWORTSsecmtd+($totANTWORTSmtd*15)))));
			$totANTWORTSmtd =	sprintf("%8s", $totANTWORTSmtd);
			$totABANDONSmtd =	sprintf("%9s", $totABANDONSmtd);
			$totCALLSmtd =	sprintf("%7s", $totCALLSmtd);		

			if (trim($totCALLSmtd)>$max_mtd_offered) {$max_mtd_offered=trim($totCALLSmtd);}
			if (trim($totANTWORTSmtd)>$max_mtd_answered) {$max_mtd_answered=trim($totANTWORTSmtd);}
			if (trim($totABANDONSmtd)>$max_mtd_abandoned) {$max_mtd_abandoned=trim($totABANDONSmtd);}
			if (trim($totABANDONSpctmtd)>$max_mtd_abandonpct) {$max_mtd_abandonpct=trim($totABANDONSpctmtd);}
			if (round($totANTWORTSsecmtd/$totANTWORTSmtd)>$max_mtd_avgtalktime) {$max_mtd_avgtalktime=round($totANTWORTSsecmtd/$totANTWORTSmtd);}
			if (trim($totANTWORTSsecmtd)>$max_mtd_totaltalktime) {$max_mtd_totaltalktime=trim($totANTWORTSsecmtd);}
			if (trim($totANTWORTSsecmtd+($totANTWORTSmtd*15))>$max_mtd_totalcalltime) {$max_mtd_totalcalltime=trim($totANTWORTSsecmtd+($totANTWORTSmtd*15));}
			$month=date("F", strtotime($dayEND[$d-1]));
			$year=substr($dayEND[$d-1], 0, 4);
			$mtd_graph_stats[$ma][0]="$month $year";
			$mtd_graph_stats[$ma][1]=trim($totCALLSmtd);
			$mtd_graph_stats[$ma][2]=trim($totANTWORTSmtd);
			$mtd_graph_stats[$ma][3]=trim($totABANDONSmtd);
			$mtd_graph_stats[$ma][4]=trim($totABANDONSpctmtd);
			$mtd_graph_stats[$ma][5]=trim($totABANDONSavgTIMEmtd);
			$mtd_graph_stats[$ma][6]=trim($totANTWORTSavgspeedTIMEmtd);
			$mtd_graph_stats[$ma][7]=trim($totANTWORTSavgTIMEmtd);
			$mtd_graph_stats[$ma][8]=trim($totANTWORTStalkTIMEmtd);
			$mtd_graph_stats[$ma][9]=trim($totANTWORTSwrapTIMEmtd);
			$mtd_graph_stats[$ma][10]=trim($totANTWORTStotTIMEmtd);
			$ma++;

			$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$ASCII_text.="|                                       MTD | $totCALLSmtd | $totANTWORTSmtd | $totABANDONSmtd | $totABANDONSpctmtd%| $totABANDONSavgTIMEmtd | $totANTWORTSavgspeedTIMEmtd | $totANTWORTSavgTIMEmtd | $totANTWORTStalkTIMEmtd | $totANTWORTSwrapTIMEmtd | $totANTWORTStotTIMEmtd |\n";
			$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$CSV_text.="\"MTD\",\"$totCALLSmtd\",\"$totANTWORTSmtd\",\"$totABANDONSmtd\",\"$totABANDONSpctmtd%\",\"$totABANDONSavgTIMEmtd\",\"$totANTWORTSavgspeedTIMEmtd\",\"$totANTWORTSavgTIMEmtd\",\"$totANTWORTStalkTIMEmtd\",\"$totANTWORTSwrapTIMEmtd\",\"$totANTWORTStotTIMEmtd\"\n";
			$totCALLSmtd=0;
			$totANTWORTSmtd=0;
			$totANTWORTSsecmtd=0;
			$totANTWORTSspeedmtd=0;
			$totABANDONSmtd=0;
			$totABANDONSsecmtd=0;

			if (date("m", strtotime($daySTART[$d]))==1 || date("m", strtotime($daySTART[$d]))==4 || date("m", strtotime($daySTART[$d]))==7 || date("m", strtotime($daySTART[$d]))==10) # Quarterly line
				{
				if ($totCALLSqtd>0)
					{
					$totABANDONSpctqtd =	sprintf("%7.2f", (100*$totABANDONSqtd/$totCALLSqtd));
					}
				else
					{
					$totABANDONSpctqtd = "    0.0";
					}
				if ($totABANDONSqtd>0)
					{
					$totABANDONSavgTIMEqtd =	sprintf("%7s", date("i:s", mktime(0, 0, round($totABANDONSsecqtd/$totABANDONSqtd))));
					if (round($totABANDONSsecqtd/$totABANDONSqtd)>$max_qtd_avgabandontime) {$max_qtd_avgabandontime=round($totABANDONSsecqtd/$totABANDONSqtd);}
					$qtd_graph_stats[$qa][11]=round($totABANDONSsecqtd/$totABANDONSqtd);
					}
				else
					{
					$totABANDONSavgTIMEqtd = "  00:00";
					$qtd_graph_stats[$qa][11]=0;
					}
				if ($totANTWORTSqtd>0)
					{
					$totANTWORTSavgspeedTIMEqtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSspeedqtd/$totANTWORTSqtd))));
					$totANTWORTSavgTIMEqtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSsecqtd/$totANTWORTSqtd))));
					if (round($totANTWORTSspeedqtd/$totANTWORTSqtd)>$max_qtd_avganswerspeed) {$max_qtd_avganswerspeed=round($totANTWORTSspeedqtd/$totANTWORTSqtd);}
					$qtd_graph_stats[$qa][12]=round($totANTWORTSspeedqtd/$totANTWORTSqtd);
					$qtd_graph_stats[$qa][16]=round($totANTWORTSsecqtd/$totANTWORTSqtd);
					}
				else
					{
					$totANTWORTSavgspeedTIMEqtd = " 00:00";
					$totANTWORTSavgTIMEqtd = " 00:00";
					$qtd_graph_stats[$qa][12]=0;
					$qtd_graph_stats[$qa][16]=0;
					}
				$totANTWORTStalkTIMEqtd =	sprintf("%10s", floor($totANTWORTSsecqtd/3600).date(":i:s", mktime(0, 0, $totANTWORTSsecqtd)));
				$totANTWORTSwrapTIMEqtd =	sprintf("%10s", floor(($totANTWORTSqtd*15)/3600).date(":i:s", mktime(0, 0, ($totANTWORTSqtd*15))));
				if (($totANTWORTSqtd*15)>$max_qtd_totalwraptime) {$max_qtd_totalwraptime=($totANTWORTSqtd*15);}
				$qtd_graph_stats[$qa][13]=($totANTWORTSqtd*15);
				$qtd_graph_stats[$qa][14]=($totANTWORTSsecqtd+($totANTWORTSqtd*15));
				$qtd_graph_stats[$qa][15]=$totANTWORTSsecqtd;
				$totANTWORTStotTIMEqtd =	sprintf("%10s", floor(($totANTWORTSsecqtd+($totANTWORTSqtd*15))/3600).date(":i:s", mktime(0, 0, ($totANTWORTSsecqtd+($totANTWORTSqtd*15)))));
				$totANTWORTSqtd =	sprintf("%8s", $totANTWORTSqtd);
				$totABANDONSqtd =	sprintf("%9s", $totABANDONSqtd);
				$totCALLSqtd =	sprintf("%7s", $totCALLSqtd);		

				if (trim($totCALLSqtd)>$max_qtd_offered) {$max_qtd_offered=trim($totCALLSqtd);}
				if (trim($totANTWORTSqtd)>$max_qtd_answered) {$max_qtd_answered=trim($totANTWORTSqtd);}
				if (trim($totABANDONSqtd)>$max_qtd_abandoned) {$max_qtd_abandoned=trim($totABANDONSqtd);}
				if (trim($totABANDONSpctqtd)>$max_qtd_abandonpct) {$max_qtd_abandonpct=trim($totABANDONSpctqtd);}
				if (round($totANTWORTSsecqtd/$totANTWORTSqtd)>$max_qtd_avgtalktime) {$max_qtd_avgtalktime=round($totANTWORTSsecqtd/$totANTWORTSqtd);}
				if (trim($totANTWORTSsecqtd)>$max_qtd_totaltalktime) {$max_qtd_totaltalktime=trim($totANTWORTSsecqtd);}
				if (trim($totANTWORTSsecqtd+($totANTWORTSqtd*15))>$max_qtd_totalcalltime) {$max_qtd_totalcalltime=trim($totANTWORTSsecqtd+($totANTWORTSqtd*15));}
				$month=date("m", strtotime($dayEND[$d]));
				$year=substr($dayEND[$d], 0, 4);
				$qtr4=array(01,02,03);
				$qtr1=array(04,05,06);
				$qtr2=array(07,08,09);
				$qtr3=array(10,11,12);
				if(in_array($month,$qtr1)) {
					$qtr="1st";
				} else if(in_array($month,$qtr2)) {
					$qtr="2nd";
				}  else if(in_array($month,$qtr3)) {
					$qtr="3rd";
				}  else if(in_array($month,$qtr4)) {
					$qtr="4th";
				}
				$qtd_graph_stats[$qa][0]="$qtr quarter, $year";
				$qtd_graph_stats[$qa][1]=trim($totCALLSqtd);
				$qtd_graph_stats[$qa][2]=trim($totANTWORTSqtd);
				$qtd_graph_stats[$qa][3]=trim($totABANDONSqtd);
				$qtd_graph_stats[$qa][4]=trim($totABANDONSpctqtd);
				$qtd_graph_stats[$qa][5]=trim($totABANDONSavgTIMEqtd);
				$qtd_graph_stats[$qa][6]=trim($totANTWORTSavgspeedTIMEqtd);
				$qtd_graph_stats[$qa][7]=trim($totANTWORTSavgTIMEqtd);
				$qtd_graph_stats[$qa][8]=trim($totANTWORTStalkTIMEqtd);
				$qtd_graph_stats[$qa][9]=trim($totANTWORTSwrapTIMEqtd);
				$qtd_graph_stats[$qa][10]=trim($totANTWORTStotTIMEqtd);
				$qa++;

				$ASCII_text.="|                                       QTD | $totCALLSqtd | $totANTWORTSqtd | $totABANDONSqtd | $totABANDONSpctqtd%| $totABANDONSavgTIMEqtd | $totANTWORTSavgspeedTIMEqtd | $totANTWORTSavgTIMEqtd | $totANTWORTStalkTIMEqtd | $totANTWORTSwrapTIMEqtd | $totANTWORTStotTIMEqtd |\n";
				$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
				$CSV_text.="\"QTD\",\"$totCALLSqtd\",\"$totANTWORTSqtd\",\"$totABANDONSqtd\",\"$totABANDONSpctqtd%\",\"$totABANDONSavgTIMEqtd\",\"$totANTWORTSavgspeedTIMEqtd\",\"$totANTWORTSavgTIMEqtd\",\"$totANTWORTStalkTIMEqtd\",\"$totANTWORTSwrapTIMEqtd\",\"$totANTWORTStotTIMEqtd\"\n";
				$totCALLSqtd=0;
				$totANTWORTSqtd=0;
				$totANTWORTSsecqtd=0;
				$totANTWORTSspeedqtd=0;
				$totABANDONSqtd=0;
				$totABANDONSsecqtd=0;
				}
		}

		$totCALLSwtd+=$totCALLSdate[$d];
		$totANTWORTSwtd+=$totANTWORTSdate[$d];
		$totANTWORTSsecwtd+=$totANTWORTSsecdate[$d];
		$totANTWORTSspeedwtd+=$totANTWORTSspeeddate[$d];
		$totABANDONSwtd+=$totABANDONSdate[$d];
		$totABANDONSsecwtd+=$totABANDONSsecdate[$d];
		$totCALLSmtd+=$totCALLSdate[$d];
		$totANTWORTSmtd+=$totANTWORTSdate[$d];
		$totANTWORTSsecmtd+=$totANTWORTSsecdate[$d];
		$totANTWORTSspeedmtd+=$totANTWORTSspeeddate[$d];
		$totABANDONSmtd+=$totABANDONSdate[$d];
		$totABANDONSsecmtd+=$totABANDONSsecdate[$d];
		$totCALLSqtd+=$totCALLSdate[$d];
		$totANTWORTSqtd+=$totANTWORTSdate[$d];
		$totANTWORTSsecqtd+=$totANTWORTSsecdate[$d];
		$totANTWORTSspeedqtd+=$totANTWORTSspeeddate[$d];
		$totABANDONSqtd+=$totABANDONSdate[$d];
		$totABANDONSsecqtd+=$totABANDONSsecdate[$d];

		if (trim($totCALLSdate[$d])>$max_offered) {$max_offered=trim($totCALLSdate[$d]);}
		if (trim($totANTWORTSdate[$d])>$max_answered) {$max_answered=trim($totANTWORTSdate[$d]);}
		if (trim($totABANDONSdate[$d])>$max_abandoned) {$max_abandoned=trim($totABANDONSdate[$d]);}
		if (trim($totABANDONSpctDATE[$d])>$max_abandonpct) {$max_abandonpct=trim($totABANDONSpctDATE[$d]);}

		if (round($totANTWORTSsecdate[$d]/$totANTWORTSdate[$d])>$max_avgtalktime) {$max_avgtalktime=round($totANTWORTSsecdate[$d]/$totANTWORTSdate[$d]);}
		if (trim($totANTWORTSsecdate[$d])>$max_totaltalktime) {$max_totaltalktime=trim($totANTWORTSsecdate[$d]);}
		if (trim($totANTWORTSsecdate[$d]+($totANTWORTSdate[$d]*15))>$max_totalcalltime) {$max_totalcalltime=trim($totANTWORTSsecdate[$d]+($totANTWORTSdate[$d]*15));}
		$graph_stats[$d][0]="$daySTART[$d] - $dayEND[$d]";
		$graph_stats[$d][1]=trim($totCALLSdate[$d]);
		$graph_stats[$d][2]=trim($totANTWORTSdate[$d]);
		$graph_stats[$d][3]=trim($totABANDONSdate[$d]);
		$graph_stats[$d][4]=trim($totABANDONSpctDATE[$d]);
		$graph_stats[$d][5]=trim($totABANDONSavgTIME[$d]);
		$graph_stats[$d][6]=trim($totANTWORTSavgspeedTIME[$d]);
		$graph_stats[$d][7]=trim($totANTWORTSavgTIME[$d]);
		$graph_stats[$d][8]=trim($totANTWORTStalkTIME[$d]);
		$graph_stats[$d][9]=trim($totANTWORTSwrapTIME[$d]);
		$graph_stats[$d][10]=trim($totANTWORTStotTIME[$d]);

		$ASCII_text.="| $daySTART[$d] - $dayEND[$d] | $totCALLSdate[$d] | $totANTWORTSdate[$d] | $totABANDONSdate[$d] | $totABANDONSpctDATE[$d]%| $totABANDONSavgTIME[$d] | $totANTWORTSavgspeedTIME[$d] | $totANTWORTSavgTIME[$d] | $totANTWORTStalkTIME[$d] | $totANTWORTSwrapTIME[$d] | $totANTWORTStotTIME[$d] |\n";
		$CSV_text.="\"$daySTART[$d] - $dayEND[$d]\",\"$totCALLSdate[$d]\",\"$totANTWORTSdate[$d]\",\"$totABANDONSdate[$d]\",\"$totABANDONSpctDATE[$d]%\",\"$totABANDONSavgTIME[$d]\",\"$totANTWORTSavgspeedTIME[$d]\",\"$totANTWORTSavgTIME[$d]\",\"$totANTWORTStalkTIME[$d]\",\"$totANTWORTSwrapTIME[$d]\",\"$totANTWORTStotTIME[$d]\"\n";

		$d++;
		}

	if ($totDROPS > 0)
		{$totDROPSpct = ( ($totDROPS / $totCALLS) * 100);}
	else {$totDROPSpct = 0;}
	$totDROPSpct = round($totDROPSpct, 2);
	if ($totQUEUE > 0)
		{$totQUEUEpct = ( ($totQUEUE / $totCALLS) * 100);}
	else {$totQUEUEpct = 0;}
	$totQUEUEpct = round($totQUEUEpct, 2);

	if ($totDROPSsec > 0)
		{$totDROPSavg = ($totDROPSsec / $totDROPS);}
	else {$totDROPSavg = 0;}
	if ($totQUEUEsec > 0)
		{$totQUEUEavg = ($totQUEUEsec / $totQUEUE);}
	else {$totQUEUEavg = 0;}
	if ($totQUEUEsec > 0)
		{$totQUEUEtot = ($totQUEUEsec / $totCALLS);}
	else {$totQUEUEtot = 0;}

	if ($totCALLSsec > 0)
		{
		$totCALLSavg = ($totCALLSsec / $totCALLS);

		$totTIME_M = ($totCALLSsec / 60);
		$totTIME_M_int = round($totTIME_M, 2);
		$totTIME_M_int = intval("$totTIME_M");
		$totTIME_S = ($totTIME_M - $totTIME_M_int);
		$totTIME_S = ($totTIME_S * 60);
		$totTIME_S = round($totTIME_S, 0);
		if ($totTIME_S < 10) {$totTIME_S = "0$totTIME_S";}
		$totTIME_MS = "$totTIME_M_int:$totTIME_S";
		$totTIME_MS =		sprintf("%9s", $totTIME_MS);
		}
	else 
		{
		$totCALLSavg = 0;
		$totTIME_MS='         ';
		}


		$FtotCALLSavg =	sprintf("%6.0f", $totCALLSavg);
		$FtotDROPSavg =	sprintf("%7.2f", $totDROPSavg);
		$FtotQUEUEavg =	sprintf("%7.2f", $totQUEUEavg);
		$FtotQUEUEtot =	sprintf("%7.2f", $totQUEUEtot);
		$FtotDROPSpct =	sprintf("%6.2f", $totDROPSpct);
		$FtotQUEUEpct =	sprintf("%6.2f", $totQUEUEpct);
		$FtotDROPS =	sprintf("%6s", $totDROPS);
		$FtotQUEUE =	sprintf("%6s", $totQUEUE);
		$FtotCALLS =	sprintf("%7s", $totCALLS);

		if ($FtotCALLS>0) 
			{
			$FtotABANDONSpct =	sprintf("%7.2f", (100*$FtotABANDONS/$FtotCALLS));
			}
		else
			{
			$FtotABANDONSpct =	"    0.0";
			}
		if ($FtotABANDONS>0) 
			{
			$FtotABANDONSavgTIME =	sprintf("%7s", date("i:s", mktime(0, 0, round($FtotABANDONSsec/$FtotABANDONS))));
			}
		else 
			{
			$FtotABANDONSavgTIME =	sprintf("%7s", "00:00");
			}
		if ($FtotANTWORTS>0) 
			{
			$FtotANTWORTSavgspeedTIME =	sprintf("%6s", date("i:s", mktime(0, 0, round($FtotANTWORTSspeed/$FtotANTWORTS))));
			$FtotANTWORTSavgTIME =	sprintf("%6s", date("i:s", mktime(0, 0, round($FtotANTWORTSsec/$FtotANTWORTS))));
			}
		else 
			{
			$FtotANTWORTSavgspeedTIME =	sprintf("%6s", "00:00");
			$FtotANTWORTSavgTIME =	sprintf("%6s", "00:00");
			}
		$FtotANTWORTStalkTIME =	sprintf("%10s", floor($FtotANTWORTSsec/3600).date(":i:s", mktime(0, 0, $FtotANTWORTSsec)));
		$FtotANTWORTSwrapTIME =	sprintf("%10s", floor(($FtotANTWORTS*15)/3600).date(":i:s", mktime(0, 0, ($FtotANTWORTS*15))));
		$FtotANTWORTStotTIME =	sprintf("%10s", floor(($FtotANTWORTSsec+($FtotANTWORTS*15))/3600).date(":i:s", mktime(0, 0, ($FtotANTWORTSsec+($FtotANTWORTS*15)))));
		$FtotANTWORTS =	sprintf("%8s", $FtotANTWORTS);
		$FtotABANDONS =	sprintf("%9s", $FtotABANDONS);

		if (date("w", strtotime($daySTART[$d]))>0) 
			{
			if ($totCALLSwtd>0)
				{
				$totABANDONSpctwtd =	sprintf("%7.2f", (100*$totABANDONSwtd/$totCALLSwtd));
				}
			else
				{
				$totABANDONSpctwtd = "    0.0";
				}
			if ($totABANDONSwtd>0)
				{
				$totABANDONSavgTIMEwtd =	sprintf("%7s", date("i:s", mktime(0, 0, round($totABANDONSsecwtd/$totABANDONSwtd))));
				if (round($totABANDONSsecwtd/$totABANDONSwtd)>$max_wtd_avgabandontime) {$max_wtd_avgabandontime=round($totABANDONSsecwtd/$totABANDONSwtd);}
				$wtd_graph_stats[$wa][11]=round($totABANDONSsecwtd/$totABANDONSwtd);
				}
			else
				{
				$totABANDONSavgTIMEwtd = "  00:00";
				$wtd_graph_stats[$wa][11]=0;
				}
			if ($totANTWORTSwtd>0)
				{
				$totANTWORTSavgspeedTIMEwtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSspeedwtd/$totANTWORTSwtd))));
				$totANTWORTSavgTIMEwtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSsecwtd/$totANTWORTSwtd))));
				if (round($totANTWORTSspeedwtd/$totANTWORTSwtd)>$max_wtd_avganswerspeed) {$max_wtd_avganswerspeed=round($totANTWORTSspeedwtd/$totANTWORTSwtd);}
				$wtd_graph_stats[$wa][12]=round($totANTWORTSspeedwtd/$totANTWORTSwtd);
				$wtd_graph_stats[$wa][16]=round($totANTWORTSsecwtd/$totANTWORTSwtd);
				}
			else
				{
				$totANTWORTSavgspeedTIMEwtd = " 00:00";
				$totANTWORTSavgTIMEwtd = " 00:00";
				$wtd_graph_stats[$wa][12]=0;
				$wtd_graph_stats[$wa][16]=0;
				}
			$totANTWORTStalkTIMEwtd =	sprintf("%10s", floor($totANTWORTSsecwtd/3600).date(":i:s", mktime(0, 0, $totANTWORTSsecwtd)));
			$totANTWORTSwrapTIMEwtd =	sprintf("%10s", floor(($totANTWORTSwtd*15)/3600).date(":i:s", mktime(0, 0, ($totANTWORTSwtd*15))));
			if (($totANTWORTSwtd*15)>$max_wtd_totalwraptime) {$max_wtd_totalwraptime=($totANTWORTSwtd*15);}
			$wtd_graph_stats[$wa][13]=($totANTWORTSwtd*15);
			$wtd_graph_stats[$wa][14]=($totANTWORTSsecwtd+($totANTWORTSwtd*15));
			$wtd_graph_stats[$wa][15]=$totANTWORTSsecwtd;
			$totANTWORTStotTIMEwtd =	sprintf("%10s", floor(($totANTWORTSsecwtd+($totANTWORTSwtd*15))/3600).date(":i:s", mktime(0, 0, ($totANTWORTSsecwtd+($totANTWORTSwtd*15)))));
			$totANTWORTSwtd =	sprintf("%8s", $totANTWORTSwtd);
			$totABANDONSwtd =	sprintf("%9s", $totABANDONSwtd);
			$totCALLSwtd =	sprintf("%7s", $totCALLSwtd);		

			if (trim($totCALLSwtd)>$max_wtd_offered) {$max_wtd_offered=trim($totCALLSwtd);}
			if (trim($totANTWORTSwtd)>$max_wtd_answered) {$max_wtd_answered=trim($totANTWORTSwtd);}
			if (trim($totABANDONSwtd)>$max_wtd_abandoned) {$max_wtd_abandoned=trim($totABANDONSwtd);}
			if (trim($totABANDONSpctwtd)>$max_wtd_abandonpct) {$max_wtd_abandonpct=trim($totABANDONSpctwtd);}

			if (trim($totANTWORTSavgTIMEwtd)>$max_wtd_avgtalktime) {$max_wtd_avgtalktime=trim($totANTWORTSavgTIMEwtd);}
			if (trim($totANTWORTSsecwtd)>$max_wtd_totaltalktime) {$max_wtd_totaltalktime=trim($totANTWORTSsecwtd);}
			if (trim($totANTWORTSsecwtd+($totANTWORTSwtd*15))>$max_wtd_totalcalltime) {$max_wtd_totalcalltime=trim($totANTWORTSsecwtd+($totANTWORTSwtd*15));}

			$week=date("W", strtotime($dayEND[$d-1]));
			$year=substr($dayEND[$d-1], 0, 4);
			$wtd_graph_stats[$wa][0]="Week $week, $year";
			$wtd_graph_stats[$wa][1]=trim($totCALLSwtd);
			$wtd_graph_stats[$wa][2]=trim($totANTWORTSwtd);
			$wtd_graph_stats[$wa][3]=trim($totABANDONSwtd);
			$wtd_graph_stats[$wa][4]=trim($totABANDONSpctwtd);
			$wtd_graph_stats[$wa][5]=trim($totABANDONSavgTIMEwtd);
			$wtd_graph_stats[$wa][6]=trim($totANTWORTSavgspeedTIMEwtd);
			$wtd_graph_stats[$wa][7]=trim($totANTWORTSavgTIMEwtd);
			$wtd_graph_stats[$wa][8]=trim($totANTWORTStalkTIMEwtd);
			$wtd_graph_stats[$wa][9]=trim($totANTWORTSwrapTIMEwtd);
			$wtd_graph_stats[$wa][10]=trim($totANTWORTStotTIMEwtd);
			$wtd_OFFERED_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE', $OFFERED_graph);
			$wtd_ANTWORTED_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$ANTWORTED_graph);
			$wtd_ABANDONED_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$ABANDONED_graph);
			$wtd_ABANDONPCT_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$ABANDONPCT_graph);
			$wtd_AVGABANDONTIME_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$AVGABANDONTIME_graph);
			$wtd_AVGANTWORTSPEED_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$AVGANTWORTSPEED_graph);
			$wtd_AVGTALKTIME_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$AVGTALKTIME_graph);
			$wtd_TOTALTALKTIME_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$TOTALTALKTIME_graph);
			$wtd_TOTALWRAPTIME_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$TOTALWRAPTIME_graph);
			$wtd_TOTALCALLTIME_graph=preg_replace('/DAILY/', 'WEEK-TO-DATE',$TOTALCALLTIME_graph);
			for ($q=0; $q<count($wtd_graph_stats); $q++) {
				if ($q==0) {$class=" first";} else if (($q+1)==count($wtd_graph_stats)) {$class=" last";} else {$class="";}
				$wtd_OFFERED_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][1]/$max_wtd_offered)."' height='16' />".$wtd_graph_stats[$q][1]."</td></tr>";
				$wtd_ANTWORTED_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][2]/$max_wtd_answered)."' height='16' />".$wtd_graph_stats[$q][2]."</td></tr>";
				$wtd_ABANDONED_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][3]/$max_wtd_abandoned)."' height='16' />".$wtd_graph_stats[$q][3]."</td></tr>";
				$wtd_ABANDONPCT_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][4]/$max_wtd_abandonpct)."' height='16' />".$wtd_graph_stats[$q][4]."% </td></tr>";
				$wtd_AVGABANDONTIME_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][11]/$max_wtd_avgabandontime)."' height='16' />".$wtd_graph_stats[$q][5]."</td></tr>";
				$wtd_AVGANTWORTSPEED_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][12]/$max_wtd_avganswerspeed)."' height='16' />".$wtd_graph_stats[$q][6]."</td></tr>";
				$wtd_AVGTALKTIME_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][16]/$max_wtd_avgtalktime)."' height='16' />".$wtd_graph_stats[$q][7]."</td></tr>";
				$wtd_TOTALTALKTIME_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][15]/$max_wtd_totaltalktime)."' height='16' />".$wtd_graph_stats[$q][8]."</td></tr>";
				$wtd_TOTALWRAPTIME_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][13]/$max_wtd_totalwraptime)."' height='16' />".$wtd_graph_stats[$q][9]."</td></tr>";
				$wtd_TOTALCALLTIME_graph.="  <tr><td class='chart_td$class'>".$wtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$wtd_graph_stats[$q][14]/$max_wtd_totalcalltime)."' height='16' />".$wtd_graph_stats[$q][10]."</td></tr>";
			}

			$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$ASCII_text.="|                                       WTD | $totCALLSwtd | $totANTWORTSwtd | $totABANDONSwtd | $totABANDONSpctwtd%| $totABANDONSavgTIMEwtd | $totANTWORTSavgspeedTIMEwtd | $totANTWORTSavgTIMEwtd | $totANTWORTStalkTIMEwtd | $totANTWORTSwrapTIMEwtd | $totANTWORTStotTIMEwtd |\n";
			$CSV_text.="\"WTD\",\"$totCALLSwtd\",\"$totANTWORTSwtd\",\"$totABANDONSwtd\",\"$totABANDONSpctwtd%\",\"$totABANDONSavgTIMEwtd\",\"$totANTWORTSavgspeedTIMEwtd\",\"$totANTWORTSavgTIMEwtd\",\"$totANTWORTStalkTIMEwtd\",\"$totANTWORTSwrapTIMEwtd\",\"$totANTWORTStotTIMEwtd\"\n";
			$totCALLSwtd=0;
			$totANTWORTSwtd=0;
			$totANTWORTSsecwtd=0;
			$totANTWORTSspeedwtd=0;
			$totABANDONSwtd=0;
			$totABANDONSsecwtd=0;
			}

		if (date("d", strtotime($daySTART[$d]))!=1) 
			{
			if ($totCALLSmtd>0)
				{
				$totABANDONSpctmtd =	sprintf("%7.2f", (100*$totABANDONSmtd/$totCALLSmtd));
				}
			else
				{
				$totABANDONSpctmtd = "    0.0";
				}
			if ($totABANDONSmtd>0)
				{
				$totABANDONSavgTIMEmtd =	sprintf("%7s", date("i:s", mktime(0, 0, round($totABANDONSsecmtd/$totABANDONSmtd))));
				if (round($totABANDONSsecmtd/$totABANDONSmtd)>$max_mtd_avgabandontime) {$max_mtd_avgabandontime=round($totABANDONSsecmtd/$totABANDONSmtd);}
				$mtd_graph_stats[$ma][11]=round($totABANDONSsecmtd/$totABANDONSmtd);
				}
			else
				{
				$totABANDONSavgTIMEmtd = "  00:00";
				$mtd_graph_stats[$ma][11]=0;
				}
			if ($totANTWORTSmtd>0)
				{
				$totANTWORTSavgspeedTIMEmtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSspeedmtd/$totANTWORTSmtd))));
				$totANTWORTSavgTIMEmtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSsecmtd/$totANTWORTSmtd))));
				if (round($totANTWORTSspeedmtd/$totANTWORTSmtd)>$max_mtd_avganswerspeed) {$max_mtd_avganswerspeed=round($totANTWORTSspeedmtd/$totANTWORTSmtd);}
				$mtd_graph_stats[$ma][12]=round($totANTWORTSspeedmtd/$totANTWORTSmtd);
				$mtd_graph_stats[$ma][16]=round($totANTWORTSsecmtd/$totANTWORTSmtd);
				}
			else
				{
				$totANTWORTSavgspeedTIMEmtd = " 00:00";
				$totANTWORTSavgTIMEmtd = " 00:00";
				$mtd_graph_stats[$ma][12]=0;
				$mtd_graph_stats[$ma][16]=0;
				}
			$totANTWORTStalkTIMEmtd =	sprintf("%10s", floor($totANTWORTSsecmtd/3600).date(":i:s", mktime(0, 0, $totANTWORTSsecmtd)));
			$totANTWORTSwrapTIMEmtd =	sprintf("%10s", floor(($totANTWORTSmtd*15)/3600).date(":i:s", mktime(0, 0, ($totANTWORTSmtd*15))));
			if (($totANTWORTSmtd*15)>$max_mtd_totalwraptime) {$max_mtd_totalwraptime=($totANTWORTSmtd*15);}
			$mtd_graph_stats[$ma][13]=($totANTWORTSmtd*15);
			$mtd_graph_stats[$ma][14]=($totANTWORTSsecmtd+($totANTWORTSmtd*15));
			$mtd_graph_stats[$ma][15]=$totANTWORTSsecmtd;
			$totANTWORTStotTIMEmtd =	sprintf("%10s", floor(($totANTWORTSsecmtd+($totANTWORTSmtd*15))/3600).date(":i:s", mktime(0, 0, ($totANTWORTSsecmtd+($totANTWORTSmtd*15)))));
			$totANTWORTSmtd =	sprintf("%8s", $totANTWORTSmtd);
			$totABANDONSmtd =	sprintf("%9s", $totABANDONSmtd);
			$totCALLSmtd =	sprintf("%7s", $totCALLSmtd);		

			if (trim($totCALLSmtd)>$max_mtd_offered) {$max_mtd_offered=trim($totCALLSmtd);}
			if (trim($totANTWORTSmtd)>$max_mtd_answered) {$max_mtd_answered=trim($totANTWORTSmtd);}
			if (trim($totABANDONSmtd)>$max_mtd_abandoned) {$max_mtd_abandoned=trim($totABANDONSmtd);}
			if (trim($totABANDONSpctmtd)>$max_mtd_abandonpct) {$max_mtd_abandonpct=trim($totABANDONSpctmtd);}

			if (round($totANTWORTSsecmtd/$totANTWORTSmtd)>$max_mtd_avgtalktime) {$max_mtd_avgtalktime=round($totANTWORTSsecmtd/$totANTWORTSmtd);}
			if (trim($totANTWORTSsecmtd)>$max_mtd_totaltalktime) {$max_mtd_totaltalktime=trim($totANTWORTSsecmtd);}
			if (trim($totANTWORTSsecmtd+($totANTWORTSmtd*15))>$max_mtd_totalcalltime) {$max_mtd_totalcalltime=trim($totANTWORTSsecmtd+($totANTWORTSmtd*15));}

			$month=date("F", strtotime($dayEND[$d-1]));
			$year=substr($dayEND[$d-1], 0, 4);
			$mtd_graph_stats[$ma][0]="$month $year";
			$mtd_graph_stats[$ma][1]=trim($totCALLSmtd);
			$mtd_graph_stats[$ma][2]=trim($totANTWORTSmtd);
			$mtd_graph_stats[$ma][3]=trim($totABANDONSmtd);
			$mtd_graph_stats[$ma][4]=trim($totABANDONSpctmtd);
			$mtd_graph_stats[$ma][5]=trim($totABANDONSavgTIMEmtd);
			$mtd_graph_stats[$ma][6]=trim($totANTWORTSavgspeedTIMEmtd);
			$mtd_graph_stats[$ma][7]=trim($totANTWORTSavgTIMEmtd);
			$mtd_graph_stats[$ma][8]=trim($totANTWORTStalkTIMEmtd);
			$mtd_graph_stats[$ma][9]=trim($totANTWORTSwrapTIMEmtd);
			$mtd_graph_stats[$ma][10]=trim($totANTWORTStotTIMEmtd);
			$mtd_OFFERED_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$OFFERED_graph);
			$mtd_ANTWORTED_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$ANTWORTED_graph);
			$mtd_ABANDONED_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$ABANDONED_graph);
			$mtd_ABANDONPCT_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$ABANDONPCT_graph);
			$mtd_AVGABANDONTIME_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$AVGABANDONTIME_graph);
			$mtd_AVGANTWORTSPEED_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$AVGANTWORTSPEED_graph);
			$mtd_AVGTALKTIME_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$AVGTALKTIME_graph);
			$mtd_TOTALTALKTIME_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$TOTALTALKTIME_graph);
			$mtd_TOTALWRAPTIME_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$TOTALWRAPTIME_graph);
			$mtd_TOTALCALLTIME_graph=preg_replace('/DAILY/', 'MONTH-TO-DATE',$TOTALCALLTIME_graph);
			for ($q=0; $q<count($mtd_graph_stats); $q++) {
				if ($q==0) {$class=" first";} else if (($q+1)==count($mtd_graph_stats)) {$class=" last";} else {$class="";}
				$mtd_OFFERED_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][1]/$max_mtd_offered)."' height='16' />".$mtd_graph_stats[$q][1]."</td></tr>";
				$mtd_ANTWORTED_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][2]/$max_mtd_answered)."' height='16' />".$mtd_graph_stats[$q][2]."</td></tr>";
				$mtd_ABANDONED_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][3]/$max_mtd_abandoned)."' height='16' />".$mtd_graph_stats[$q][3]."</td></tr>";
				$mtd_ABANDONPCT_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][4]/$max_mtd_abandonpct)."' height='16' />".$mtd_graph_stats[$q][4]."%</td></tr>";
				$mtd_AVGABANDONTIME_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][11]/$max_mtd_avgabandontime)."' height='16' />".$mtd_graph_stats[$q][5]."</td></tr>";
				$mtd_AVGANTWORTSPEED_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][12]/$max_mtd_avganswerspeed)."' height='16' />".$mtd_graph_stats[$q][6]."</td></tr>";
				$mtd_AVGTALKTIME_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][16]/$max_mtd_avgtalktime)."' height='16' />".$mtd_graph_stats[$q][7]."</td></tr>";
				$mtd_TOTALTALKTIME_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][15]/$max_mtd_totaltalktime)."' height='16' />".$mtd_graph_stats[$q][8]."</td></tr>";
				$mtd_TOTALWRAPTIME_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][13]/$max_mtd_totalwraptime)."' height='16' />".$mtd_graph_stats[$q][9]."</td></tr>";
				$mtd_TOTALCALLTIME_graph.="  <tr><td class='chart_td$class'>".$mtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$mtd_graph_stats[$q][14]/$max_mtd_totalcalltime)."' height='16' />".$mtd_graph_stats[$q][10]."</td></tr>";
				$graph_totCALLSmtd+=$mtd_graph_stats[$q][1];
				$graph_totANTWORTSmtd+=$mtd_graph_stats[$q][2];
				$graph_totABANDONSmtd+=$mtd_graph_stats[$q][3];
				$graph_totABANDONSpctmtd+=$mtd_graph_stats[$q][4];
				$graph_totABANDONSavgTIMEmtd+=$mtd_graph_stats[$q][5];
				$graph_totANTWORTSavgspeedTIMEmtd+=$mtd_graph_stats[$q][6];
				$graph_totANTWORTSavgTIMEmtd+=$mtd_graph_stats[$q][7];
				$graph_totANTWORTStalkTIMEmtd+=$mtd_graph_stats[$q][8];
				$graph_totANTWORTSwrapTIMEmtd+=$mtd_graph_stats[$q][9];
				$graph_totANTWORTStotTIMEmtd+=$mtd_graph_stats[$q][10];
			}			
			
			$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$ASCII_text.="|                                       MTD | $totCALLSmtd | $totANTWORTSmtd | $totABANDONSmtd | $totABANDONSpctmtd%| $totABANDONSavgTIMEmtd | $totANTWORTSavgspeedTIMEmtd | $totANTWORTSavgTIMEmtd | $totANTWORTStalkTIMEmtd | $totANTWORTSwrapTIMEmtd | $totANTWORTStotTIMEmtd |\n";
			$CSV_text.="\"MTD\",\"$totCALLSmtd\",\"$totANTWORTSmtd\",\"$totABANDONSmtd\",\"$totABANDONSpctmtd%\",\"$totABANDONSavgTIMEmtd\",\"$totANTWORTSavgspeedTIMEmtd\",\"$totANTWORTSavgTIMEmtd\",\"$totANTWORTStalkTIMEmtd\",\"$totANTWORTSwrapTIMEmtd\",\"$totANTWORTStotTIMEmtd\"\n";
			$totCALLSmtd=0;
			$totANTWORTSmtd=0;
			$totANTWORTSsecmtd=0;
			$totANTWORTSspeedmtd=0;
			$totABANDONSmtd=0;
			$totABANDONSsecmtd=0;

	#		if (date("m", strtotime($daySTART[$d]))==1 || date("m", strtotime($daySTART[$d]))==4 || date("m", strtotime($daySTART[$d]))==7 || date("m", strtotime($daySTART[$d]))==10) # Quarterly line
	#			{
				if ($totCALLSqtd>0)
					{
					$totABANDONSpctqtd =	sprintf("%7.2f", (100*$totABANDONSqtd/$totCALLSqtd));
					}
				else
					{
					$totABANDONSpctqtd = "    0.0";
					}
				if ($totABANDONSqtd>0)
					{
					$totABANDONSavgTIMEqtd =	sprintf("%7s", date("i:s", mktime(0, 0, round($totABANDONSsecqtd/$totABANDONSqtd))));
					if (round($totABANDONSsecqtd/$totABANDONSqtd)>$max_qtd_avgabandontime) {$max_qtd_avgabandontime=round($totABANDONSsecqtd/$totABANDONSqtd);}
					$qtd_graph_stats[$qa][11]=round($totABANDONSsecqtd/$totABANDONSqtd);
					}
				else
					{
					$totABANDONSavgTIMEqtd = "  00:00";
					$qtd_graph_stats[$qa][11]=0;
					}
				if ($totANTWORTSqtd>0)
					{
					$totANTWORTSavgspeedTIMEqtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSspeedqtd/$totANTWORTSqtd))));
					$totANTWORTSavgTIMEqtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANTWORTSsecqtd/$totANTWORTSqtd))));
					if (round($totANTWORTSspeedqtd/$totANTWORTSqtd)>$max_qtd_avganswerspeed) {$max_qtd_avganswerspeed=round($totANTWORTSspeedqtd/$totANTWORTSqtd);}
					$qtd_graph_stats[$qa][12]=round($totANTWORTSspeedqtd/$totANTWORTSqtd);
					$qtd_graph_stats[$qa][16]=round($totANTWORTSsecqtd/$totANTWORTSqtd);
				}
				else
					{
					$totANTWORTSavgspeedTIMEqtd = " 00:00";
					$totANTWORTSavgTIMEqtd = " 00:00";
					$qtd_graph_stats[$qa][12]=0;
					$qtd_graph_stats[$qa][16]=0;
					}
				$totANTWORTStalkTIMEqtd =	sprintf("%10s", floor($totANTWORTSsecqtd/3600).date(":i:s", mktime(0, 0, $totANTWORTSsecqtd)));
				$totANTWORTSwrapTIMEqtd =	sprintf("%10s", floor(($totANTWORTSqtd*15)/3600).date(":i:s", mktime(0, 0, ($totANTWORTSqtd*15))));
				if (($totANTWORTSqtd*15)>$max_qtd_totalwraptime) {$max_qtd_totalwraptime=($totANTWORTSqtd*15);}
				$qtd_graph_stats[$qa][13]=($totANTWORTSqtd*15);
				$qtd_graph_stats[$qa][14]=($totANTWORTSsecqtd+($totANTWORTSqtd*15));
				$qtd_graph_stats[$qa][15]=$totANTWORTSsecqtd;
				$totANTWORTStotTIMEqtd =	sprintf("%10s", floor(($totANTWORTSsecqtd+($totANTWORTSqtd*15))/3600).date(":i:s", mktime(0, 0, ($totANTWORTSsecqtd+($totANTWORTSqtd*15)))));
				$totANTWORTSqtd =	sprintf("%8s", $totANTWORTSqtd);
				$totABANDONSqtd =	sprintf("%9s", $totABANDONSqtd);
				$totCALLSqtd =	sprintf("%7s", $totCALLSqtd);		

				if (trim($totCALLSqtd)>$max_qtd_offered) {$max_qtd_offered=trim($totCALLSqtd);}
				if (trim($totANTWORTSqtd)>$max_qtd_answered) {$max_qtd_answered=trim($totANTWORTSqtd);}
				if (trim($totABANDONSqtd)>$max_qtd_abandoned) {$max_qtd_abandoned=trim($totABANDONSqtd);}
				if (trim($totABANDONSpctqtd)>$max_qtd_abandonpct) {$max_qtd_abandonpct=trim($totABANDONSpctqtd);}

				if (round($totANTWORTSsecqtd/$totANTWORTSqtd)>$max_qtd_avgtalktime) {$max_qtd_avgtalktime=round($totANTWORTSsecqtd/$totANTWORTSqtd);}
				if (trim($totANTWORTSsecqtd)>$max_qtd_totaltalktime) {$max_qtd_totaltalktime=trim($totANTWORTSsecqtd);}
				if (trim($totANTWORTSsecqtd+($totANTWORTSqtd*15))>$max_qtd_totalcalltime) {$max_qtd_totalcalltime=trim($totANTWORTSsecqtd+($totANTWORTSqtd*15));}

				$month=date("m", strtotime($dayEND[$d-1]));
				$year=substr($dayEND[$d-1], 0, 4);
				$qtr1=array(01,02,03);
				$qtr2=array(04,05,06);
				$qtr3=array(07,08,09);
				$qtr4=array(10,11,12);
				if(in_array($month,$qtr1)) {
					$qtr="1st";
				} else if(in_array($month,$qtr2)) {
					$qtr="2nd";
				}  else if(in_array($month,$qtr3)) {
					$qtr="3rd";
				}  else if(in_array($month,$qtr4)) {
					$qtr="4th";
				}
				$qtd_graph_stats[$qa][0]="$qtr quarter, $year";
				$qtd_graph_stats[$qa][1]=trim($totCALLSqtd);
				$qtd_graph_stats[$qa][2]=trim($totANTWORTSqtd);
				$qtd_graph_stats[$qa][3]=trim($totABANDONSqtd);
				$qtd_graph_stats[$qa][4]=trim($totABANDONSpctqtd);
				$qtd_graph_stats[$qa][5]=trim($totABANDONSavgTIMEqtd);
				$qtd_graph_stats[$qa][6]=trim($totANTWORTSavgspeedTIMEqtd);
				$qtd_graph_stats[$qa][7]=trim($totANTWORTSavgTIMEqtd);
				$qtd_graph_stats[$qa][8]=trim($totANTWORTStalkTIMEqtd);
				$qtd_graph_stats[$qa][9]=trim($totANTWORTSwrapTIMEqtd);
				$qtd_graph_stats[$qa][10]=trim($totANTWORTStotTIMEqtd);
				$qtd_OFFERED_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$OFFERED_graph);
				$qtd_ANTWORTED_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$ANTWORTED_graph);
				$qtd_ABANDONED_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$ABANDONED_graph);
				$qtd_ABANDONPCT_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$ABANDONPCT_graph);
				$qtd_AVGABANDONTIME_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$AVGABANDONTIME_graph);
				$qtd_AVGANTWORTSPEED_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$AVGANTWORTSPEED_graph);
				$qtd_AVGTALKTIME_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$AVGTALKTIME_graph);
				$qtd_TOTALTALKTIME_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$TOTALTALKTIME_graph);
				$qtd_TOTALWRAPTIME_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$TOTALWRAPTIME_graph);
				$qtd_TOTALCALLTIME_graph=preg_replace('/DAILY/', 'QUARTER-TO-DATE',$TOTALCALLTIME_graph);
				for ($q=0; $q<count($qtd_graph_stats); $q++) {
					if ($q==0) {$class=" first";} else if (($q+1)==count($qtd_graph_stats)) {$class=" last";} else {$class="";}
					$qtd_OFFERED_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][1]/$max_qtd_offered)."' height='16' />".$qtd_graph_stats[$q][1]."</td></tr>";
					$qtd_ANTWORTED_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][2]/$max_qtd_answered)."' height='16' />".$qtd_graph_stats[$q][2]."</td></tr>";
					$qtd_ABANDONED_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][3]/$max_qtd_abandoned)."' height='16' />".$qtd_graph_stats[$q][3]."</td></tr>";
					$qtd_ABANDONPCT_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][4]/$max_qtd_abandonpct)."' height='16' />".$qtd_graph_stats[$q][4]."%</td></tr>";
					$qtd_AVGABANDONTIME_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][11]/$max_qtd_avgabandontime)."' height='16' />".$qtd_graph_stats[$q][5]."</td></tr>";
					$qtd_AVGANTWORTSPEED_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][12]/$max_qtd_avganswerspeed)."' height='16' />".$qtd_graph_stats[$q][6]."</td></tr>";
					$qtd_AVGTALKTIME_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][16]/$max_qtd_avgtalktime)."' height='16' />".$qtd_graph_stats[$q][7]."</td></tr>";
					$qtd_TOTALTALKTIME_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][15]/$max_qtd_totaltalktime)."' height='16' />".$qtd_graph_stats[$q][8]."</td></tr>";
					$qtd_TOTALWRAPTIME_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][13]/$max_qtd_totalwraptime)."' height='16' />".$qtd_graph_stats[$q][9]."</td></tr>";
					$qtd_TOTALCALLTIME_graph.="  <tr><td class='chart_td$class'>".$qtd_graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$qtd_graph_stats[$q][14]/$max_qtd_totalcalltime)."' height='16' />".$qtd_graph_stats[$q][10]."</td></tr>";
					$graph_totCALLSqtd+=$qtd_graph_stats[$q][1];
					$graph_totANTWORTSqtd+=$qtd_graph_stats[$q][2];
					$graph_totABANDONSqtd+=$qtd_graph_stats[$q][3];
					$graph_totABANDONSpctqtd+=$qtd_graph_stats[$q][4];
					$graph_totABANDONSavgTIMEqtd+=$qtd_graph_stats[$q][5];
					$graph_totANTWORTSavgspeedTIMEqtd+=$qtd_graph_stats[$q][6];
					$graph_totANTWORTSavgTIMEqtd+=$qtd_graph_stats[$q][7];
					$graph_totANTWORTStalkTIMEqtd+=$qtd_graph_stats[$q][8];
					$graph_totANTWORTSwrapTIMEqtd+=$qtd_graph_stats[$q][9];
					$graph_totANTWORTStotTIMEqtd+=$qtd_graph_stats[$q][10];
				}

				$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
				$ASCII_text.="|                                       QTD | $totCALLSqtd | $totANTWORTSqtd | $totABANDONSqtd | $totABANDONSpctqtd%| $totABANDONSavgTIMEqtd | $totANTWORTSavgspeedTIMEqtd | $totANTWORTSavgTIMEqtd | $totANTWORTStalkTIMEqtd | $totANTWORTSwrapTIMEqtd | $totANTWORTStotTIMEqtd |\n";
				$CSV_text.="\"QTD\",\"$totCALLSqtd\",\"$totANTWORTSqtd\",\"$totABANDONSqtd\",\"$totABANDONSpctqtd%\",\"$totABANDONSavgTIMEqtd\",\"$totANTWORTSavgspeedTIMEqtd\",\"$totANTWORTSavgTIMEqtd\",\"$totANTWORTStalkTIMEqtd\",\"$totANTWORTSwrapTIMEqtd\",\"$totANTWORTStotTIMEqtd\"\n";
				$totCALLSqtd=0;
				$totANTWORTSqtd=0;
				$totANTWORTSsecqtd=0;
				$totANTWORTSspeedqtd=0;
				$totABANDONSqtd=0;
				$totABANDONSsecqtd=0;
	#			}
		}

			for ($q=0; $q<count($graph_stats); $q++) {
				if ($q==0) {$class=" first";} else if (($q+1)==count($graph_stats)) {$class=" last";} else {$class="";}
				$OFFERED_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][1]/$max_offered)."' height='16' />".$graph_stats[$q][1]."</td></tr>";
				$ANTWORTED_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][2]/$max_answered)."' height='16' />".$graph_stats[$q][2]."</td></tr>";
				$ABANDONED_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][3]/$max_abandoned)."' height='16' />".$graph_stats[$q][3]."</td></tr>";
				$ABANDONPCT_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][4]/$max_abandonpct)."' height='16' />".$graph_stats[$q][4]."%</td></tr>";
				$AVGABANDONTIME_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][11]/$max_avgabandontime)."' height='16' />".$graph_stats[$q][5]."</td></tr>";
				$AVGANTWORTSPEED_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][12]/$max_avganswerspeed)."' height='16' />".$graph_stats[$q][6]."</td></tr>";
				$AVGTALKTIME_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][16]/$max_avgtalktime)."' height='16' />".$graph_stats[$q][7]."</td></tr>";
				$TOTALTALKTIME_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][15]/$max_totaltalktime)."' height='16' />".$graph_stats[$q][8]."</td></tr>";
				$TOTALWRAPTIME_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][13]/$max_totalwraptime)."' height='16' />".$graph_stats[$q][9]."</td></tr>";
				$TOTALCALLTIME_graph.="  <tr><td class='chart_td$class'>".$graph_stats[$q][0]."</td><td nowrap class='chart_td value$class'><img src='../vicidial/images/bar.png' alt='' width='".round(400*$graph_stats[$q][14]/$max_totalcalltime)."' height='16' />".$graph_stats[$q][10]."</td></tr>";
			}
			$OFFERED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotCALLS)."</th></tr></table>";
			$ANTWORTED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTS)."</th></tr></table>";
			$ABANDONED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONS)."</th></tr></table>";
			$ABANDONPCT_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONSpct)."%</th></tr></table>";
			$AVGABANDONTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONSavgTIME)."</th></tr></table>";
			$AVGANTWORTSPEED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSavgspeedTIME)."</th></tr></table>";
			$AVGTALKTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSavgTIME)."</th></tr></table>";
			$TOTALTALKTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTStalkTIME)."</th></tr></table>";
			$TOTALWRAPTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSwrapTIME)."</th></tr></table>";
			$TOTALCALLTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTStotTIME)."</th></tr></table>";
			$JS_onload.="\tDrawGraph('OFFERED', '1');\n"; 
			$JS_text.="function DrawGraph(graph, th_id) {\n";
			$JS_text.="	var OFFERED_graph=\"$OFFERED_graph\";\n";
			$JS_text.="	var ANTWORTED_graph=\"$ANTWORTED_graph\";\n";
			$JS_text.="	var ABANDONED_graph=\"$ABANDONED_graph\";\n";
			$JS_text.="	var ABANDONPCT_graph=\"$ABANDONPCT_graph\";\n";
			$JS_text.="	var AVGABANDONTIME_graph=\"$AVGABANDONTIME_graph\";\n";
			$JS_text.="	var AVGANTWORTSPEED_graph=\"$AVGANTWORTSPEED_graph\";\n";
			$JS_text.="	var AVGTALKTIME_graph=\"$AVGTALKTIME_graph\";\n";
			$JS_text.="	var TOTALTALKTIME_graph=\"$TOTALTALKTIME_graph\";\n";
			$JS_text.="	var TOTALWRAPTIME_graph=\"$TOTALWRAPTIME_graph\";\n";
			$JS_text.="	var TOTALCALLTIME_graph=\"$TOTALCALLTIME_graph\";\n";
			$JS_text.="\n";
			$JS_text.="	for (var i=1; i<=10; i++) {\n";
			$JS_text.="		var cellID=\"multigroup_graph\"+i;\n";
			$JS_text.="		document.getElementById(cellID).style.backgroundColor='#DDDDDD';\n";
			$JS_text.="	}\n";
			$JS_text.="	var cellID=\"multigroup_graph\"+th_id;\n";
			$JS_text.="	document.getElementById(cellID).style.backgroundColor='#999999';\n";
			$JS_text.="	var graph_to_display=eval(graph+\"_graph\");\n";
			$JS_text.="	document.getElementById('stats_graph').innerHTML=graph_to_display;\n";
			$JS_text.="}\n";
			$wtd_OFFERED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotCALLS)."</th></tr></table>";
			$wtd_ANTWORTED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTS)."</th></tr></table>";
			$wtd_ABANDONED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONS)."</th></tr></table>";
			$wtd_ABANDONPCT_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONSpct)."%</th></tr></table>";
			$wtd_AVGABANDONTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONSavgTIME)."</th></tr></table>";
			$wtd_AVGANTWORTSPEED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSavgspeedTIME)."</th></tr></table>";
			$wtd_AVGTALKTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSavgTIME)."</th></tr></table>";
			$wtd_TOTALTALKTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTStalkTIME)."</th></tr></table>";
			$wtd_TOTALWRAPTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSwrapTIME)."</th></tr></table>";
			$wtd_TOTALCALLTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTStotTIME)."</th></tr></table>";
			$JS_onload.="\tDrawWTDGraph('OFFERED', '1');\n"; 
			$JS_text.="function DrawWTDGraph(graph, th_id) {\n";
			$JS_text.="	var OFFERED_graph=\"$wtd_OFFERED_graph\";\n";
			$JS_text.="	var ANTWORTED_graph=\"$wtd_ANTWORTED_graph\";\n";
			$JS_text.="	var ABANDONED_graph=\"$wtd_ABANDONED_graph\";\n";
			$JS_text.="	var ABANDONPCT_graph=\"$wtd_ABANDONPCT_graph\";\n";
			$JS_text.="	var AVGABANDONTIME_graph=\"$wtd_AVGABANDONTIME_graph\";\n";
			$JS_text.="	var AVGANTWORTSPEED_graph=\"$wtd_AVGANTWORTSPEED_graph\";\n";
			$JS_text.="	var AVGTALKTIME_graph=\"$wtd_AVGTALKTIME_graph\";\n";
			$JS_text.="	var TOTALTALKTIME_graph=\"$wtd_TOTALTALKTIME_graph\";\n";
			$JS_text.="	var TOTALWRAPTIME_graph=\"$wtd_TOTALWRAPTIME_graph\";\n";
			$JS_text.="	var TOTALCALLTIME_graph=\"$wtd_TOTALCALLTIME_graph\";\n";
			$JS_text.="\n";
			$JS_text.="	for (var i=1; i<=10; i++) {\n";
			$JS_text.="		var cellID=\"WTD_graph\"+i;\n";
			$JS_text.="		document.getElementById(cellID).style.backgroundColor='#DDDDDD';\n";
			$JS_text.="	}\n";
			$JS_text.="	var cellID=\"WTD_graph\"+th_id;\n";
			$JS_text.="	document.getElementById(cellID).style.backgroundColor='#999999';\n";
			$JS_text.="	var graph_to_display=eval(graph+\"_graph\");\n";
			$JS_text.="	document.getElementById('WTD_stats_graph').innerHTML=graph_to_display;\n";
			$JS_text.="}\n";
			$mtd_OFFERED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotCALLS)."</th></tr></table>";
			$mtd_ANTWORTED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTS)."</th></tr></table>";
			$mtd_ABANDONED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONS)."</th></tr></table>";
			$mtd_ABANDONPCT_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONSpct)."%</th></tr></table>";
			$mtd_AVGABANDONTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONSavgTIME)."</th></tr></table>";
			$mtd_AVGANTWORTSPEED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSavgspeedTIME)."</th></tr></table>";
			$mtd_AVGTALKTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSavgTIME)."</th></tr></table>";
			$mtd_TOTALTALKTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTStalkTIME)."</th></tr></table>";
			$mtd_TOTALWRAPTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSwrapTIME)."</th></tr></table>";
			$mtd_TOTALCALLTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTStotTIME)."</th></tr></table>";
			$JS_onload.="\tDrawMTDGraph('OFFERED', '1');\n"; 
			$JS_text.="function DrawMTDGraph(graph, th_id) {\n";
			$JS_text.="	var OFFERED_graph=\"$mtd_OFFERED_graph\";\n";
			$JS_text.="	var ANTWORTED_graph=\"$mtd_ANTWORTED_graph\";\n";
			$JS_text.="	var ABANDONED_graph=\"$mtd_ABANDONED_graph\";\n";
			$JS_text.="	var ABANDONPCT_graph=\"$mtd_ABANDONPCT_graph\";\n";
			$JS_text.="	var AVGABANDONTIME_graph=\"$mtd_AVGABANDONTIME_graph\";\n";
			$JS_text.="	var AVGANTWORTSPEED_graph=\"$mtd_AVGANTWORTSPEED_graph\";\n";
			$JS_text.="	var AVGTALKTIME_graph=\"$mtd_AVGTALKTIME_graph\";\n";
			$JS_text.="	var TOTALTALKTIME_graph=\"$mtd_TOTALTALKTIME_graph\";\n";
			$JS_text.="	var TOTALWRAPTIME_graph=\"$mtd_TOTALWRAPTIME_graph\";\n";
			$JS_text.="	var TOTALCALLTIME_graph=\"$mtd_TOTALCALLTIME_graph\";\n";
			$JS_text.="\n";
			$JS_text.="	for (var i=1; i<=10; i++) {\n";
			$JS_text.="		var cellID=\"MTD_graph\"+i;\n";
			$JS_text.="		document.getElementById(cellID).style.backgroundColor='#DDDDDD';\n";
			$JS_text.="	}\n";
			$JS_text.="	var cellID=\"MTD_graph\"+th_id;\n";
			$JS_text.="	document.getElementById(cellID).style.backgroundColor='#999999';\n";
			$JS_text.="	var graph_to_display=eval(graph+\"_graph\");\n";
			$JS_text.="	document.getElementById('MTD_stats_graph').innerHTML=graph_to_display;\n";
			$JS_text.="}\n";
			$qtd_OFFERED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotCALLS)."</th></tr></table>";
			$qtd_ANTWORTED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTS)."</th></tr></table>";
			$qtd_ABANDONED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONS)."</th></tr></table>";
			$qtd_ABANDONPCT_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONSpct)."%</th></tr></table>";
			$qtd_AVGABANDONTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotABANDONSavgTIME)."</th></tr></table>";
			$qtd_AVGANTWORTSPEED_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSavgspeedTIME)."</th></tr></table>";
			$qtd_AVGTALKTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSavgTIME)."</th></tr></table>";
			$qtd_TOTALTALKTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTStalkTIME)."</th></tr></table>";
			$qtd_TOTALWRAPTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTSwrapTIME)."</th></tr></table>";
			$qtd_TOTALCALLTIME_graph.="<tr><th class='thgraph' scope='col'>TOTAL:</th><th class='thgraph' scope='col'>".trim($FtotANTWORTStotTIME)."</th></tr></table>";
			$JS_onload.="\tDrawQTDGraph('OFFERED', '1');\n"; 
			$JS_text.="function DrawQTDGraph(graph, th_id) {\n";
			$JS_text.="	var OFFERED_graph=\"$qtd_OFFERED_graph\";\n";
			$JS_text.="	var ANTWORTED_graph=\"$qtd_ANTWORTED_graph\";\n";
			$JS_text.="	var ABANDONED_graph=\"$qtd_ABANDONED_graph\";\n";
			$JS_text.="	var ABANDONPCT_graph=\"$qtd_ABANDONPCT_graph\";\n";
			$JS_text.="	var AVGABANDONTIME_graph=\"$qtd_AVGABANDONTIME_graph\";\n";
			$JS_text.="	var AVGANTWORTSPEED_graph=\"$qtd_AVGANTWORTSPEED_graph\";\n";
			$JS_text.="	var AVGTALKTIME_graph=\"$qtd_AVGTALKTIME_graph\";\n";
			$JS_text.="	var TOTALTALKTIME_graph=\"$qtd_TOTALTALKTIME_graph\";\n";
			$JS_text.="	var TOTALWRAPTIME_graph=\"$qtd_TOTALWRAPTIME_graph\";\n";
			$JS_text.="	var TOTALCALLTIME_graph=\"$qtd_TOTALCALLTIME_graph\";\n";
			$JS_text.="\n";
			$JS_text.="	for (var i=1; i<=10; i++) {\n";
			$JS_text.="		var cellID=\"QTD_graph\"+i;\n";
			$JS_text.="		document.getElementById(cellID).style.backgroundColor='#DDDDDD';\n";
			$JS_text.="	}\n";
			$JS_text.="	var cellID=\"QTD_graph\"+th_id;\n";
			$JS_text.="	document.getElementById(cellID).style.backgroundColor='#999999';\n";
			$JS_text.="	var graph_to_display=eval(graph+\"_graph\");\n";
			$JS_text.="	document.getElementById('QTD_stats_graph').innerHTML=graph_to_display;\n";
			$JS_text.="}\n";


	$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
	$ASCII_text.="|                                    TOTALS | $FtotCALLS | $FtotANTWORTS | $FtotABANDONS | $FtotABANDONSpct%| $FtotABANDONSavgTIME | $FtotANTWORTSavgspeedTIME | $FtotANTWORTSavgTIME | $FtotANTWORTStalkTIME | $FtotANTWORTSwrapTIME | $FtotANTWORTStotTIME |\n";
	$ASCII_text.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
	$CSV_text.="\"TOTALS\",\"$FtotCALLS\",\"$FtotANTWORTS\",\"$FtotABANDONS\",\"$FtotABANDONSpct%\",\"$FtotABANDONSavgTIME\",\"$FtotANTWORTSavgspeedTIME\",\"$FtotANTWORTSavgTIME\",\"$FtotANTWORTStalkTIME\",\"$FtotANTWORTSwrapTIME\",\"$FtotANTWORTStotTIME\"\n";

	## FORMAT OUTPUT ##
	$i=0;
	$hi_hour_count=0;
	$hi_hold_count=0;

	while ($i < $TOTintervals)
		{
		if ($qrtCALLS[$i] > 0)
			{$qrtCALLSavg[$i] = ($qrtCALLSsec[$i] / $qrtCALLS[$i]);}
		else {$qrtCALLSavg[$i] = 0;}
		if ($qrtDROPS[$i] > 0)
			{$qrtDROPSavg[$i] = ($qrtDROPSsec[$i] / $qrtDROPS[$i]);}
		else {$qrtDROPSavg[$i] = 0;}
		if ($qrtQUEUE[$i] > 0)
			{$qrtQUEUEavg[$i] = ($qrtQUEUEsec[$i] / $qrtQUEUE[$i]);}
		else {$qrtQUEUEavg[$i] = 0;}

		if ($qrtCALLS[$i] > $hi_hour_count) {$hi_hour_count = $qrtCALLS[$i];}
		if ($qrtQUEUEavg[$i] > $hi_hold_count) {$hi_hold_count = $qrtQUEUEavg[$i];}

		$qrtQUEUEavg[$i] = round($qrtQUEUEavg[$i], 0);
		if (strlen($qrtQUEUEavg[$i])<1) {$qrtQUEUEavg[$i]=0;}
		$qrtQUEUEmax[$i] = round($qrtQUEUEmax[$i], 0);
		if (strlen($qrtQUEUEmax[$i])<1) {$qrtQUEUEmax[$i]=0;}

		$i++;
		}

	$JS_onload.="}\n";
	$JS_text.=$JS_onload;
	$JS_text.="</script>\n";

	if ($report_display_type=="HTML") 
		{
		$MAIN.=$JS_text.$GRAPH.$WTD_GRAPH.$MTD_GRAPH.$QTD_GRAPH;
		}
	else
		{
		$MAIN.=$ASCII_text;
		}

	if ($hi_hour_count < 1)
		{$hour_multiplier = 0;}
	else
		{$hour_multiplier = (20 / $hi_hour_count);}
	if ($hi_hold_count < 1)
		{$hold_multiplier = 0;}
	else
		{$hold_multiplier = (20 / $hi_hold_count);}


	$ENDtime = date("U");
	$RUNtime = ($ENDtime - $STARTtime);
	$MAIN.="\nRun Time: $RUNtime seconds|$db_source\n";
	$MAIN.="</PRE>\n";
	$MAIN.="</TD></TR></TABLE>\n";
	$MAIN.="</BODY></HTML>\n";

	if ($file_download > 0)
		{
		$FILE_TIME = date("Ymd-His");
		$CSVfilename = "Inbound_Daily_Report_$US$FILE_TIME.csv";
		$CSV_text=preg_replace('/ +\"/', '"', $CSV_text);
		$CSV_text=preg_replace('/\" +/', '"', $CSV_text);
		// We'll be outputting a TXT file
		header('Content-type: application/octet-stream');

		// It will be called LIST_101_20090209-121212.txt
		header("Content-Disposition: attachment; filename=\"$CSVfilename\"");
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		ob_clean();
		flush();

		echo "$CSV_text";

		exit;
		}
	else 
		{

		echo "$HEADER";
		require("admin_header.php");
		echo "$MAIN";
		}
	}



?>
