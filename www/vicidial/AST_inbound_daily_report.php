<?php 
# AST_inbound_daily_report.php
# 
# Copyright (C) 2011  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGES
#
# 111119-1234 - First build
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
    echo "Invalid Username/Password: |$PHP_AUTH_USER|$PHP_AUTH_PW|\n";
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
    echo "You are not allowed to view this report: |$PHP_AUTH_USER|$report_name|\n";
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
$MAIN.="// o_cal.a_tpl.weekstart = 1; // Monday week start\n";
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
$MAIN.="// o_cal.a_tpl.weekstart = 1; // Monday week start\n";
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
$MAIN.="<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href=\"$PHP_SELF?DB=$DB&query_date=$query_date&end_date=$end_date&group=$group&shift=$shift&hourly_breakdown=$hourly_breakdown&SUBMIT=$SUBMIT&file_download=1\">DOWNLOAD</a> | <a href=\"./admin.php?ADD=3111&group_id=$group\">MODIFY</a> | <a href=\"./admin.php?ADD=999999\">REPORTS</a><BR><INPUT TYPE=checkbox NAME=hourly_breakdown VALUE='checked' $hourly_breakdown>Show hourly results</FONT>\n";
$MAIN.="</FORM>\n\n";

$MAIN.="<PRE><FONT SIZE=2>\n\n";


if (!$group)
	{
	$MAIN.="\n\n";
	$MAIN.="PLEASE SELECT AN IN-GROUP AND DATE RANGE ABOVE AND CLICK SUBMIT\n";
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

	##########################################################################
	#########  CALCULATE ALL OF THE 15-MINUTE PERIODS NEEDED FOR ALL DAYS ####

	### BUILD HOUR:MIN DISPLAY ARRAY ###
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
	if ($DB) {$MAIN.="$stmt\n";}
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

		# $MAIN.= "$qs[$i] | $dt[$i] - $row[1] | $ut[$i] | $ls[$i] | $st[$i] | $tr[$i] | $at[$i]\n";

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
	$totANSWERSdate=$MT;

	$totANSWERS=0;
	$totABANDONS=0;
	$totANSWERSsec=0;
	$totABANDONSsec=0;
	$totANSWERSspeed=0;

	$FtotANSWERS=0;
	$FtotABANDONS=0;
	$FtotANSWERSsec=0;
	$FtotABANDONSsec=0;
	$FtotANSWERSspeed=0;

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
		$totANSWERSdate[$j]=0;
		$totANSWERSsecdate[$j]=0;
		$totANSWERSspeeddate[$j]=0;
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
					$totANSWERSdate[$j]++;
					$totANSWERSsecdate[$j]+=($ls[$i]-$qs[$i]-15);
					$totANSWERSspeeddate[$j]+=$qs[$i];
					$FtotANSWERS++;
					$FtotANSWERSsec+=($ls[$i]-$qs[$i]-15);
					$FtotANSWERSspeeddate+=$qs[$i];
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
	$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
	$MAIN.="|                                           | TOTAL   | TOTAL    | TOTAL     | TOTAL   | AVG     | AVG    | AVG    | TOTAL      | TOTAL      | TOTAL      |\n";
	$MAIN.="| SHIFT                                     | CALLS   | CALLS    | CALLS     | ABANDON | ABANDON | ANSWER | TALK   | TALK       | WRAP       | CALL       |\n";
	$MAIN.="| DATE-TIME RANGE                           | OFFERED | ANSWERED | ABANDONED | PERCENT | TIME    | SPEED  | TIME   | TIME       | TIME       | TIME       |\n";
	$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
	$CSV_text.="\"SHIFT DATE-TIME RANGE\",\"CALLS\",\"ANSWERED\",\"ABANDONED\",\"ABN %\",\"AVG ABANDON TIME\",\"AVG ANSWER SPEED\",\"AVG TALK TIME\",\"TOTAL TALK TIME\",\"WRAP TIME MIN:SEC\",\"TOTAL MINS\"\n";


	$totCALLSwtd=0;
	$totANSWERSwtd=0;
	$totANSWERSsecwtd=0;
	$totANSWERSspeedwtd=0;
	$totABANDONSwtd=0;
	$totABANDONSsecwtd=0;

	$totCALLSmtd=0;
	$totANSWERSmtd=0;
	$totANSWERSsecmtd=0;
	$totANSWERSspeedmtd=0;
	$totABANDONSmtd=0;
	$totABANDONSsecmtd=0;

	$totCALLSqtd=0;
	$totANSWERSqtd=0;
	$totANSWERSsecqtd=0;
	$totANSWERSspeedqtd=0;
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
			}
		else
			{
			$totABANDONSdate[$d]="0";
			$totABANDONSavgTIME[$d] = "  00:00";
			}
		if ($totANSWERSdate[$d]>0)
			{
			$totANSWERSavgspeedTIME[$d] =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSspeeddate[$d]/$totANSWERSdate[$d]))));
			$totANSWERSavgTIME[$d] =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSsecdate[$d]/$totANSWERSdate[$d]))));
			}
		else
			{
			$totANSWERSdate[$d]="0";
			$totANSWERSavgspeedTIME[$d] = " 00:00";
			$totANSWERSavgTIME[$d] = " 00:00";
			}
		$totANSWERStalkTIME[$d] =	sprintf("%10s", floor($totANSWERSsecdate[$d]/3600).date(":i:s", mktime(0, 0, $totANSWERSsecdate[$d])));
		$totANSWERSwrapTIME[$d] =	sprintf("%10s", floor(($totANSWERSdate[$d]*15)/3600).date(":i:s", mktime(0, 0, ($totANSWERSdate[$d]*15))));
		$totANSWERStotTIME[$d] =	sprintf("%10s", floor(($totANSWERSsecdate[$d]+($totANSWERSdate[$d]*15))/3600).date(":i:s", mktime(0, 0, ($totANSWERSsecdate[$d]+($totANSWERSdate[$d]*15)))));
		$totANSWERSdate[$d] =	sprintf("%8s", $totANSWERSdate[$d]);
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
				}
			else
				{
				$totABANDONSavgTIMEwtd = "  00:00";
				}
			if ($totANSWERSwtd>0)
				{
				$totANSWERSavgspeedTIMEwtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSspeedwtd/$totANSWERSwtd))));
				$totANSWERSavgTIMEwtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSsecwtd/$totANSWERSwtd))));
				}
			else
				{
				$totANSWERSavgspeedTIMEwtd = " 00:00";
				$totANSWERSavgTIMEwtd = " 00:00";
				}
			$totANSWERStalkTIMEwtd =	sprintf("%10s", floor($totANSWERSsecwtd/3600).date(":i:s", mktime(0, 0, $totANSWERSsecwtd)));
			$totANSWERSwrapTIMEwtd =	sprintf("%10s", floor(($totANSWERSwtd*15)/3600).date(":i:s", mktime(0, 0, ($totANSWERSwtd*15))));
			$totANSWERStotTIMEwtd =	sprintf("%10s", floor(($totANSWERSsecwtd+($totANSWERSwtd*15))/3600).date(":i:s", mktime(0, 0, ($totANSWERSsecwtd+($totANSWERSwtd*15)))));
			$totANSWERSwtd =	sprintf("%8s", $totANSWERSwtd);
			$totABANDONSwtd =	sprintf("%9s", $totABANDONSwtd);
			$totCALLSwtd =	sprintf("%7s", $totCALLSwtd);		

			$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$MAIN.="|                                       WTD | $totCALLSwtd | $totANSWERSwtd | $totABANDONSwtd | $totABANDONSpctwtd%| $totABANDONSavgTIMEwtd | $totANSWERSavgspeedTIMEwtd | $totANSWERSavgTIMEwtd | $totANSWERStalkTIMEwtd | $totANSWERSwrapTIMEwtd | $totANSWERStotTIMEwtd |\n";
			$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$CSV_text.="\"WTD\",\"$totCALLSwtd\",\"$totANSWERSwtd\",\"$totABANDONSwtd\",\"$totABANDONSpctwtd%\",\"$totABANDONSavgTIMEwtd\",\"$totANSWERSavgspeedTIMEwtd\",\"$totANSWERSavgTIMEwtd\",\"$totANSWERStalkTIMEwtd\",\"$totANSWERSwrapTIMEwtd\",\"$totANSWERStotTIMEwtd\"\n";
			$totCALLSwtd=0;
			$totANSWERSwtd=0;
			$totANSWERSsecwtd=0;
			$totANSWERSspeedwtd=0;
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
				}
			else
				{
				$totABANDONSavgTIMEmtd = "  00:00";
				}
			if ($totANSWERSmtd>0)
				{
				$totANSWERSavgspeedTIMEmtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSspeedmtd/$totANSWERSmtd))));
				$totANSWERSavgTIMEmtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSsecmtd/$totANSWERSmtd))));
				}
			else
				{
				$totANSWERSavgspeedTIMEmtd = " 00:00";
				$totANSWERSavgTIMEmtd = " 00:00";
				}
			$totANSWERStalkTIMEmtd =	sprintf("%10s", floor($totANSWERSsecmtd/3600).date(":i:s", mktime(0, 0, $totANSWERSsecmtd)));
			$totANSWERSwrapTIMEmtd =	sprintf("%10s", floor(($totANSWERSmtd*15)/3600).date(":i:s", mktime(0, 0, ($totANSWERSmtd*15))));
			$totANSWERStotTIMEmtd =	sprintf("%10s", floor(($totANSWERSsecmtd+($totANSWERSmtd*15))/3600).date(":i:s", mktime(0, 0, ($totANSWERSsecmtd+($totANSWERSmtd*15)))));
			$totANSWERSmtd =	sprintf("%8s", $totANSWERSmtd);
			$totABANDONSmtd =	sprintf("%9s", $totABANDONSmtd);
			$totCALLSmtd =	sprintf("%7s", $totCALLSmtd);		

			$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$MAIN.="|                                       MTD | $totCALLSmtd | $totANSWERSmtd | $totABANDONSmtd | $totABANDONSpctmtd%| $totABANDONSavgTIMEmtd | $totANSWERSavgspeedTIMEmtd | $totANSWERSavgTIMEmtd | $totANSWERStalkTIMEmtd | $totANSWERSwrapTIMEmtd | $totANSWERStotTIMEmtd |\n";
			$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$CSV_text.="\"MTD\",\"$totCALLSmtd\",\"$totANSWERSmtd\",\"$totABANDONSmtd\",\"$totABANDONSpctmtd%\",\"$totABANDONSavgTIMEmtd\",\"$totANSWERSavgspeedTIMEmtd\",\"$totANSWERSavgTIMEmtd\",\"$totANSWERStalkTIMEmtd\",\"$totANSWERSwrapTIMEmtd\",\"$totANSWERStotTIMEmtd\"\n";
			$totCALLSmtd=0;
			$totANSWERSmtd=0;
			$totANSWERSsecmtd=0;
			$totANSWERSspeedmtd=0;
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
					}
				else
					{
					$totABANDONSavgTIMEqtd = "  00:00";
					}
				if ($totANSWERSqtd>0)
					{
					$totANSWERSavgspeedTIMEqtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSspeedqtd/$totANSWERSqtd))));
					$totANSWERSavgTIMEqtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSsecqtd/$totANSWERSqtd))));
					}
				else
					{
					$totANSWERSavgspeedTIMEqtd = " 00:00";
					$totANSWERSavgTIMEqtd = " 00:00";
					}
				$totANSWERStalkTIMEqtd =	sprintf("%10s", floor($totANSWERSsecqtd/3600).date(":i:s", mktime(0, 0, $totANSWERSsecqtd)));
				$totANSWERSwrapTIMEqtd =	sprintf("%10s", floor(($totANSWERSqtd*15)/3600).date(":i:s", mktime(0, 0, ($totANSWERSqtd*15))));
				$totANSWERStotTIMEqtd =	sprintf("%10s", floor(($totANSWERSsecqtd+($totANSWERSqtd*15))/3600).date(":i:s", mktime(0, 0, ($totANSWERSsecqtd+($totANSWERSqtd*15)))));
				$totANSWERSqtd =	sprintf("%8s", $totANSWERSqtd);
				$totABANDONSqtd =	sprintf("%9s", $totABANDONSqtd);
				$totCALLSqtd =	sprintf("%7s", $totCALLSqtd);		

				$MAIN.="|                                       QTD | $totCALLSqtd | $totANSWERSqtd | $totABANDONSqtd | $totABANDONSpctqtd%| $totABANDONSavgTIMEqtd | $totANSWERSavgspeedTIMEqtd | $totANSWERSavgTIMEqtd | $totANSWERStalkTIMEqtd | $totANSWERSwrapTIMEqtd | $totANSWERStotTIMEqtd |\n";
				$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
				$CSV_text.="\"QTD\",\"$totCALLSqtd\",\"$totANSWERSqtd\",\"$totABANDONSqtd\",\"$totABANDONSpctqtd%\",\"$totABANDONSavgTIMEqtd\",\"$totANSWERSavgspeedTIMEqtd\",\"$totANSWERSavgTIMEqtd\",\"$totANSWERStalkTIMEqtd\",\"$totANSWERSwrapTIMEqtd\",\"$totANSWERStotTIMEqtd\"\n";
				$totCALLSqtd=0;
				$totANSWERSqtd=0;
				$totANSWERSsecqtd=0;
				$totANSWERSspeedqtd=0;
				$totABANDONSqtd=0;
				$totABANDONSsecqtd=0;
				}
		}

		$totCALLSwtd+=$totCALLSdate[$d];
		$totANSWERSwtd+=$totANSWERSdate[$d];
		$totANSWERSsecwtd+=$totANSWERSsecdate[$d];
		$totANSWERSspeedwtd+=$totANSWERSspeeddate[$d];
		$totABANDONSwtd+=$totABANDONSdate[$d];
		$totABANDONSsecwtd+=$totABANDONSsecdate[$d];
		$totCALLSmtd+=$totCALLSdate[$d];
		$totANSWERSmtd+=$totANSWERSdate[$d];
		$totANSWERSsecmtd+=$totANSWERSsecdate[$d];
		$totANSWERSspeedmtd+=$totANSWERSspeeddate[$d];
		$totABANDONSmtd+=$totABANDONSdate[$d];
		$totABANDONSsecmtd+=$totABANDONSsecdate[$d];
		$totCALLSqtd+=$totCALLSdate[$d];
		$totANSWERSqtd+=$totANSWERSdate[$d];
		$totANSWERSsecqtd+=$totANSWERSsecdate[$d];
		$totANSWERSspeedqtd+=$totANSWERSspeeddate[$d];
		$totABANDONSqtd+=$totABANDONSdate[$d];
		$totABANDONSsecqtd+=$totABANDONSsecdate[$d];


		$MAIN.="| $daySTART[$d] - $dayEND[$d] | $totCALLSdate[$d] | $totANSWERSdate[$d] | $totABANDONSdate[$d] | $totABANDONSpctDATE[$d]%| $totABANDONSavgTIME[$d] | $totANSWERSavgspeedTIME[$d] | $totANSWERSavgTIME[$d] | $totANSWERStalkTIME[$d] | $totANSWERSwrapTIME[$d] | $totANSWERStotTIME[$d] |\n";
		$CSV_text.="\"$daySTART[$d] - $dayEND[$d]\",\"$totCALLSdate[$d]\",\"$totANSWERSdate[$d]\",\"$totABANDONSdate[$d]\",\"$totABANDONSpctDATE[$d]%\",\"$totABANDONSavgTIME[$d]\",\"$totANSWERSavgspeedTIME[$d]\",\"$totANSWERSavgTIME[$d]\",\"$totANSWERStalkTIME[$d]\",\"$totANSWERSwrapTIME[$d]\",\"$totANSWERStotTIME[$d]\"\n";

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
		if ($FtotANSWERS>0) 
			{
			$FtotANSWERSavgspeedTIME =	sprintf("%6s", date("i:s", mktime(0, 0, round($FtotANSWERSspeed/$FtotANSWERS))));
			$FtotANSWERSavgTIME =	sprintf("%6s", date("i:s", mktime(0, 0, round($FtotANSWERSsec/$FtotANSWERS))));
			}
		else 
			{
			$FtotANSWERSavgspeedTIME =	sprintf("%6s", "00:00");
			$FtotANSWERSavgTIME =	sprintf("%6s", "00:00");
			}
		$FtotANSWERStalkTIME =	sprintf("%10s", floor($FtotANSWERSsec/3600).date(":i:s", mktime(0, 0, $FtotANSWERSsec)));
		$FtotANSWERSwrapTIME =	sprintf("%10s", floor(($FtotANSWERS*15)/3600).date(":i:s", mktime(0, 0, ($FtotANSWERS*15))));
		$FtotANSWERStotTIME =	sprintf("%10s", floor(($FtotANSWERSsec+($FtotANSWERS*15))/3600).date(":i:s", mktime(0, 0, ($FtotANSWERSsec+($FtotANSWERS*15)))));
		$FtotANSWERS =	sprintf("%8s", $FtotANSWERS);
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
				}
			else
				{
				$totABANDONSavgTIMEwtd = "  00:00";
				}
			if ($totANSWERSwtd>0)
				{
				$totANSWERSavgspeedTIMEwtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSspeedwtd/$totANSWERSwtd))));
				$totANSWERSavgTIMEwtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSsecwtd/$totANSWERSwtd))));
				}
			else
				{
				$totANSWERSavgspeedTIMEwtd = " 00:00";
				$totANSWERSavgTIMEwtd = " 00:00";
				}
			$totANSWERStalkTIMEwtd =	sprintf("%10s", floor($totANSWERSsecwtd/3600).date(":i:s", mktime(0, 0, $totANSWERSsecwtd)));
			$totANSWERSwrapTIMEwtd =	sprintf("%10s", floor(($totANSWERSwtd*15)/3600).date(":i:s", mktime(0, 0, ($totANSWERSwtd*15))));
			$totANSWERStotTIMEwtd =	sprintf("%10s", floor(($totANSWERSsecwtd+($totANSWERSwtd*15))/3600).date(":i:s", mktime(0, 0, ($totANSWERSsecwtd+($totANSWERSwtd*15)))));
			$totANSWERSwtd =	sprintf("%8s", $totANSWERSwtd);
			$totABANDONSwtd =	sprintf("%9s", $totABANDONSwtd);
			$totCALLSwtd =	sprintf("%7s", $totCALLSwtd);		

			$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$MAIN.="|                                       WTD | $totCALLSwtd | $totANSWERSwtd | $totABANDONSwtd | $totABANDONSpctwtd%| $totABANDONSavgTIMEwtd | $totANSWERSavgspeedTIMEwtd | $totANSWERSavgTIMEwtd | $totANSWERStalkTIMEwtd | $totANSWERSwrapTIMEwtd | $totANSWERStotTIMEwtd |\n";
			$CSV_text.="\"WTD\",\"$totCALLSwtd\",\"$totANSWERSwtd\",\"$totABANDONSwtd\",\"$totABANDONSpctwtd%\",\"$totABANDONSavgTIMEwtd\",\"$totANSWERSavgspeedTIMEwtd\",\"$totANSWERSavgTIMEwtd\",\"$totANSWERStalkTIMEwtd\",\"$totANSWERSwrapTIMEwtd\",\"$totANSWERStotTIMEwtd\"\n";
			$totCALLSwtd=0;
			$totANSWERSwtd=0;
			$totANSWERSsecwtd=0;
			$totANSWERSspeedwtd=0;
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
				}
			else
				{
				$totABANDONSavgTIMEmtd = "  00:00";
				}
			if ($totANSWERSmtd>0)
				{
				$totANSWERSavgspeedTIMEmtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSspeedmtd/$totANSWERSmtd))));
				$totANSWERSavgTIMEmtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSsecmtd/$totANSWERSmtd))));
				}
			else
				{
				$totANSWERSavgspeedTIMEmtd = " 00:00";
				$totANSWERSavgTIMEmtd = " 00:00";
				}
			$totANSWERStalkTIMEmtd =	sprintf("%10s", floor($totANSWERSsecmtd/3600).date(":i:s", mktime(0, 0, $totANSWERSsecmtd)));
			$totANSWERSwrapTIMEmtd =	sprintf("%10s", floor(($totANSWERSmtd*15)/3600).date(":i:s", mktime(0, 0, ($totANSWERSmtd*15))));
			$totANSWERStotTIMEmtd =	sprintf("%10s", floor(($totANSWERSsecmtd+($totANSWERSmtd*15))/3600).date(":i:s", mktime(0, 0, ($totANSWERSsecmtd+($totANSWERSmtd*15)))));
			$totANSWERSmtd =	sprintf("%8s", $totANSWERSmtd);
			$totABANDONSmtd =	sprintf("%9s", $totABANDONSmtd);
			$totCALLSmtd =	sprintf("%7s", $totCALLSmtd);		

			$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
			$MAIN.="|                                       MTD | $totCALLSmtd | $totANSWERSmtd | $totABANDONSmtd | $totABANDONSpctmtd%| $totABANDONSavgTIMEmtd | $totANSWERSavgspeedTIMEmtd | $totANSWERSavgTIMEmtd | $totANSWERStalkTIMEmtd | $totANSWERSwrapTIMEmtd | $totANSWERStotTIMEmtd |\n";
			$CSV_text.="\"MTD\",\"$totCALLSmtd\",\"$totANSWERSmtd\",\"$totABANDONSmtd\",\"$totABANDONSpctmtd%\",\"$totABANDONSavgTIMEmtd\",\"$totANSWERSavgspeedTIMEmtd\",\"$totANSWERSavgTIMEmtd\",\"$totANSWERStalkTIMEmtd\",\"$totANSWERSwrapTIMEmtd\",\"$totANSWERStotTIMEmtd\"\n";
			$totCALLSmtd=0;
			$totANSWERSmtd=0;
			$totANSWERSsecmtd=0;
			$totANSWERSspeedmtd=0;
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
					}
				else
					{
					$totABANDONSavgTIMEqtd = "  00:00";
					}
				if ($totANSWERSqtd>0)
					{
					$totANSWERSavgspeedTIMEqtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSspeedqtd/$totANSWERSqtd))));
					$totANSWERSavgTIMEqtd =	sprintf("%6s", date("i:s", mktime(0, 0, round($totANSWERSsecqtd/$totANSWERSqtd))));
					}
				else
					{
					$totANSWERSavgspeedTIMEqtd = " 00:00";
					$totANSWERSavgTIMEqtd = " 00:00";
					}
				$totANSWERStalkTIMEqtd =	sprintf("%10s", floor($totANSWERSsecqtd/3600).date(":i:s", mktime(0, 0, $totANSWERSsecqtd)));
				$totANSWERSwrapTIMEqtd =	sprintf("%10s", floor(($totANSWERSqtd*15)/3600).date(":i:s", mktime(0, 0, ($totANSWERSqtd*15))));
				$totANSWERStotTIMEqtd =	sprintf("%10s", floor(($totANSWERSsecqtd+($totANSWERSqtd*15))/3600).date(":i:s", mktime(0, 0, ($totANSWERSsecqtd+($totANSWERSqtd*15)))));
				$totANSWERSqtd =	sprintf("%8s", $totANSWERSqtd);
				$totABANDONSqtd =	sprintf("%9s", $totABANDONSqtd);
				$totCALLSqtd =	sprintf("%7s", $totCALLSqtd);		

				$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
				$MAIN.="|                                       QTD | $totCALLSqtd | $totANSWERSqtd | $totABANDONSqtd | $totABANDONSpctqtd%| $totABANDONSavgTIMEqtd | $totANSWERSavgspeedTIMEqtd | $totANSWERSavgTIMEqtd | $totANSWERStalkTIMEqtd | $totANSWERSwrapTIMEqtd | $totANSWERStotTIMEqtd |\n";
				$CSV_text.="\"QTD\",\"$totCALLSqtd\",\"$totANSWERSqtd\",\"$totABANDONSqtd\",\"$totABANDONSpctqtd%\",\"$totABANDONSavgTIMEqtd\",\"$totANSWERSavgspeedTIMEqtd\",\"$totANSWERSavgTIMEqtd\",\"$totANSWERStalkTIMEqtd\",\"$totANSWERSwrapTIMEqtd\",\"$totANSWERStotTIMEqtd\"\n";
				$totCALLSqtd=0;
				$totANSWERSqtd=0;
				$totANSWERSsecqtd=0;
				$totANSWERSspeedqtd=0;
				$totABANDONSqtd=0;
				$totABANDONSsecqtd=0;
	#			}
		}

	$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
	$MAIN.="|                                    TOTALS | $FtotCALLS | $FtotANSWERS | $FtotABANDONS | $FtotABANDONSpct%| $FtotABANDONSavgTIME | $FtotANSWERSavgspeedTIME | $FtotANSWERSavgTIME | $FtotANSWERStalkTIME | $FtotANSWERSwrapTIME | $FtotANSWERStotTIME |\n";
	$MAIN.="+-------------------------------------------+---------+----------+-----------+---------+---------+--------+--------+------------+------------+------------+\n";
	$CSV_text.="\"TOTALS\",\"$FtotCALLS\",\"$FtotANSWERS\",\"$FtotABANDONS\",\"$FtotABANDONSpct%\",\"$FtotABANDONSavgTIME\",\"$FtotANSWERSavgspeedTIME\",\"$FtotANSWERSavgTIME\",\"$FtotANSWERStalkTIME\",\"$FtotANSWERSwrapTIME\",\"$FtotANSWERStotTIME\"\n";

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
