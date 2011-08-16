<?php 
# AST_campaign_status_list_report.php
#
# This report is designed to show the breakdown by list_id of the calls and 
# their statuses for all lists within a campaign for a set time period
#
# Copyright (C) 2011  Joe Johnson, Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGES
#
# 110815-2138 - First build
#

require("dbconnect.php");
require("functions.php");

$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
if (isset($_GET["query_date_D"]))				{$query_date_D=$_GET["query_date_D"];}
	elseif (isset($_POST["query_date_D"]))	{$query_date_D=$_POST["query_date_D"];}
if (isset($_GET["end_date_D"]))				{$end_date_D=$_GET["end_date_D"];}
	elseif (isset($_POST["end_date_D"]))		{$end_date_D=$_POST["end_date_D"];}
if (isset($_GET["query_date_T"]))				{$query_date_T=$_GET["query_date_T"];}
	elseif (isset($_POST["query_date_T"]))	{$query_date_T=$_POST["query_date_T"];}
if (isset($_GET["end_date_T"]))				{$end_date_T=$_GET["end_date_T"];}
	elseif (isset($_POST["end_date_T"]))		{$end_date_T=$_POST["end_date_T"];}
if (isset($_GET["group"]))					{$group=$_GET["group"];}
	elseif (isset($_POST["group"]))			{$group=$_POST["group"];}
if (isset($_GET["user_group"]))				{$user_group=$_GET["user_group"];}
	elseif (isset($_POST["user_group"]))	{$user_group=$_POST["user_group"];}
if (isset($_GET["file_download"]))			{$file_download=$_GET["file_download"];}
	elseif (isset($_POST["file_download"]))	{$file_download=$_POST["file_download"];}
if (isset($_GET["DB"]))						{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))			{$DB=$_POST["DB"];}
if (isset($_GET["SUBMIT"]))					{$SUBMIT=$_GET["SUBMIT"];}
	elseif (isset($_POST["SUBMIT"]))		{$SUBMIT=$_POST["SUBMIT"];}

$report_name="Campaign Status List Report";
$NOW_DATE = date("Y-m-d");
if (!isset($query_date_D)) {$query_date_D=$NOW_DATE;}
if (!isset($end_date_D)) {$end_date_D=$NOW_DATE;}
if (!isset($query_date_T)) {$query_date_T="00:00:00";}
if (!isset($end_date_T)) {$end_date_T="23:59:59";}

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,outbound_autodial_active,slave_db_server,reports_use_slave_db FROM system_settings;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$HTML_text.="$stmt\n";}
if ($archive_tbl) {$agent_log_table="vicidial_agent_log_archive";} else {$agent_log_table="vicidial_agent_log";}
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

######################################
if ( (strlen($slave_db_server)>5) and (preg_match("/$report_name/",$reports_use_slave_db)) )
	{
	mysql_close($link);
	$use_slave_server=1;
	$db_source = 'S';
	require("dbconnect.php");
	$HTML_text.="<!-- Using slave server $slave_db_server $db_source -->\n";
	}

$PHP_AUTH_USER = preg_replace("/[^0-9a-zA-Z]/","",$PHP_AUTH_USER);
$PHP_AUTH_PW = preg_replace("/[^0-9a-zA-Z]/","",$PHP_AUTH_PW);

$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and pass='$PHP_AUTH_PW' and user_level > 6 and view_reports='1' and active='Y';";
if ($DB) {$HTML_text.="|$stmt|\n";}
if ($non_latin > 0) { $rslt=mysql_query("SET NAMES 'UTF8'");}
$rslt=mysql_query($stmt, $link);
$row=mysql_fetch_row($rslt);
$auth=$row[0];

$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and pass='$PHP_AUTH_PW' and user_level='7' and view_reports='1' and active='Y';";
if ($DB) {$HTML_text.="|$stmt|\n";}
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
if ($DB) {$HTML_text.="|$stmt|\n";}
$rslt=mysql_query($stmt, $link);
$row=mysql_fetch_row($rslt);
$LOGuser_group =			$row[0];

$stmt="SELECT allowed_campaigns,allowed_reports from vicidial_user_groups where user_group='$LOGuser_group';";
if ($DB) {$HTML_text.="|$stmt|\n";}
$rslt=mysql_query($stmt, $link);
$row=mysql_fetch_row($rslt);
$LOGallowed_campaigns = $row[0];
$LOGallowed_reports =	$row[1];

if ( (!preg_match("/$report_name/",$LOGallowed_reports)) and (!preg_match("/ALL REPORTS/",$LOGallowed_reports)) )
	{
    Header("WWW-Authenticate: Basic realm=\"VICI-PROJECTS\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo "You are not allowed to view this report: |$PHP_AUTH_USER|$report_name|\n";
    exit;
	}

$LOGallowed_campaignsSQL='';
$whereLOGallowed_campaignsSQL='';
if ( (!preg_match("/-ALL/",$LOGallowed_campaigns)) )
	{
	$rawLOGallowed_campaignsSQL = preg_replace("/ -/",'',$LOGallowed_campaigns);
	$rawLOGallowed_campaignsSQL = preg_replace("/ /","','",$rawLOGallowed_campaignsSQL);
	$LOGallowed_campaignsSQL = "and campaign_id IN('$rawLOGallowed_campaignsSQL')";
	$whereLOGallowed_campaignsSQL = "where campaign_id IN('$rawLOGallowed_campaignsSQL')";
	}
$regexLOGallowed_campaigns = " $LOGallowed_campaigns ";

$i=0;
$group_string='|';
$group_ct = count($group);
while($i < $group_ct)
	{
	$group_string .= "$group[$i]|";
	$i++;
	}

$stmt="select campaign_id from vicidial_campaigns $whereLOGallowed_campaignsSQL order by campaign_id;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$HTML_text.="$stmt\n";}
$campaigns_to_print = mysql_num_rows($rslt);
$i=0;
while ($i < $campaigns_to_print)
	{
	$row=mysql_fetch_row($rslt);
	$groups[$i] =$row[0];
	if (preg_match("/-ALL/",$group_string) )
		{$group[$i] = $groups[$i];}
	$i++;
	}

$i=0;
$group_string='|';
$group_ct = count($group);
while($i < $group_ct)
	{
	if ( (preg_match("/ $group[$i] /",$regexLOGallowed_campaigns)) or (preg_match("/-ALL/",$LOGallowed_campaigns)) )
		{
		$group_string .= "$group[$i]|";
		$group_SQL .= "'$group[$i]',";
		$groupQS .= "&group[]=$group[$i]";
		}
	$i++;
	}

if ( (preg_match("/--ALL--/",$group_string) ) or ($group_ct < 1) )
	{$group_SQL = "";}
else
	{
	$group_SQL = preg_replace("/,\$/",'',$group_SQL);
	$group_SQL_str=$group_SQL;
	$group_SQL = "and campaign_id IN($group_SQL)";
	}

$query_date="$query_date_D $query_date_T";
$end_date="$end_date_D $end_date_T";

$HTML_head.="<HTML>\n";
$HTML_head.="<HEAD>\n";
$HTML_head.="<STYLE type=\"text/css\">\n";
$HTML_head.="<!--\n";
$HTML_head.="   .green {color: white; background-color: green}\n";
$HTML_head.="   .red {color: white; background-color: red}\n";
$HTML_head.="   .blue {color: white; background-color: blue}\n";
$HTML_head.="   .purple {color: white; background-color: purple}\n";
$HTML_head.="-->\n";
$HTML_head.=" </STYLE>\n";

$HTML_head.="<script language=\"JavaScript\" src=\"calendar_db.js\"></script>\n";
$HTML_head.="<link rel=\"stylesheet\" href=\"calendar.css\">\n";

$HTML_head.="<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">\n";
$HTML_head.="<TITLE>$report_name</TITLE></HEAD><BODY BGCOLOR=WHITE marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>$group_S\n";
$short_header=1;

#	require("admin_header.php");

$HTML_text.="<TABLE CELLPADDING=4 CELLSPACING=0><TR><TD>";

$HTML_text.="<FORM ACTION=\"$PHP_SELF\" METHOD=GET name=vicidial_report id=vicidial_report>\n";
$HTML_text.="<TABLE CELLSPACING=3><TR><TD VALIGN=TOP> Dates:<BR>";
$HTML_text.="<INPUT TYPE=hidden NAME=DB VALUE=\"$DB\">\n";
$HTML_text.="<INPUT TYPE=hidden NAME=query_date ID=query_date VALUE=\"$query_date\">\n";
$HTML_text.="<INPUT TYPE=hidden NAME=end_date ID=end_date VALUE=\"$end_date\">\n";
$HTML_text.="<INPUT TYPE=TEXT NAME=query_date_D SIZE=11 MAXLENGTH=10 VALUE=\"$query_date_D\">";

$HTML_text.="<script language=\"JavaScript\">\n";
$HTML_text.="var o_cal = new tcal ({\n";
$HTML_text.="	// form name\n";
$HTML_text.="	'formname': 'vicidial_report',\n";
$HTML_text.="	// input name\n";
$HTML_text.="	'controlname': 'query_date_D'\n";
$HTML_text.="});\n";
$HTML_text.="o_cal.a_tpl.yearscroll = false;\n";
$HTML_text.="// o_cal.a_tpl.weekstart = 1; // Monday week start\n";
$HTML_text.="</script>\n";

$HTML_text.=" &nbsp; <INPUT TYPE=TEXT NAME=query_date_T SIZE=9 MAXLENGTH=8 VALUE=\"$query_date_T\">";

$HTML_text.="<BR> to <BR><INPUT TYPE=TEXT NAME=end_date_D SIZE=11 MAXLENGTH=10 VALUE=\"$end_date_D\">";

$HTML_text.="<script language=\"JavaScript\">\n";
$HTML_text.="var o_cal = new tcal ({\n";
$HTML_text.="	// form name\n";
$HTML_text.="	'formname': 'vicidial_report',\n";
$HTML_text.="	// input name\n";
$HTML_text.="	'controlname': 'end_date_D'\n";
$HTML_text.="});\n";
$HTML_text.="o_cal.a_tpl.yearscroll = false;\n";
$HTML_text.="// o_cal.a_tpl.weekstart = 1; // Monday week start\n";
$HTML_text.="</script>\n";

$HTML_text.=" &nbsp; <INPUT TYPE=TEXT NAME=end_date_T SIZE=9 MAXLENGTH=8 VALUE=\"$end_date_T\">";

$HTML_text.="</TD><TD VALIGN=TOP> Campaigns:<BR>";
$HTML_text.="<SELECT SIZE=5 NAME=group[] multiple>\n";
if  (preg_match("/--ALL--/",$group_string))
	{$HTML_text.="<option value=\"--ALL--\" selected>-- ALL CAMPAIGNS --</option>\n";}
else
	{$HTML_text.="<option value=\"--ALL--\">-- ALL CAMPAIGNS --</option>\n";}
$o=0;
while ($campaigns_to_print > $o)
	{
	if (preg_match("/$groups[$o]\|/",$group_string)) {$HTML_text.="<option selected value=\"$groups[$o]\">$groups[$o]</option>\n";}
	else {$HTML_text.="<option value=\"$groups[$o]\">$groups[$o]</option>\n";}
	$o++;
	}
$HTML_text.="</SELECT>\n";

$HTML_text.="</TD><TD VALIGN=TOP>&nbsp;\n";
$HTML_text.="</TD><TD VALIGN=TOP>\n";
$HTML_text.="<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=SUBMIT>\n";
$HTML_text.="</TD><TD VALIGN=TOP> &nbsp; &nbsp; &nbsp; &nbsp; ";

$HTML_text.="<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;\n";
$HTML_text.="<a href=\"$PHP_SELF?DB=$DB&query_date=$query_date&end_date=$end_date&query_date_D=$query_date_D&query_date_T=$query_date_T&end_date_D=$end_date_D&end_date_T=$end_date_T$groupQS&file_download=1&SUBMIT=$SUBMIT\">DOWNLOAD</a> |";
$HTML_text.=" <a href=\"./admin.php?ADD=999999\">REPORTS</a> </FONT>\n";
$HTML_text.="</FONT>\n";
$HTML_text.="</TD></TR></TABLE>";
$HTML_text.="</FORM>\n\n";

$HTML_text.="<PRE>";
$i=0;
$group_string='|';
$group_ct = count($group);
while($i < $group_ct)
	{
	$stmt="select distinct status, status_name from vicidial_campaign_statuses where campaign_id='$group[$i]' UNION select distinct status, status_name from vicidial_statuses order by status, status_name";
	$rslt=mysql_query($stmt, $link);
	while ($row=mysql_fetch_row($rslt)) 
		{
		$status_ary[$row[0]]=" - $row[1]";
		}
	$HTML_text.="<B>CAMPAIGN: $group[$i]</B>\n";
	$CSV_text.="\"CAMPAIGN: $group[$i]\"\n";

	$stmt="select closer_campaigns from vicidial_campaigns where campaign_id='$group[$i]'";
	$rslt=mysql_query($stmt, $link);
	if (mysql_num_rows($rslt)>0) 
		{
		$row=mysql_fetch_row($rslt);
		$inbound_groups=preg_replace('/ -$/', '', trim($row[0]));
		if (strlen($inbound_groups)>0) 
			{
			$inbound_groups=preg_replace("/\s/", "', '", $inbound_groups);
			$inbound_SQL="and vicidial_closer_log.campaign_id in ('$inbound_groups')";
			} 
		else 
			{
			$inbound_SQL="";
			}
		}

	$stmt="select distinct list_id, list_name from vicidial_lists where campaign_id='$group[$i]' order by list_id, list_name asc";
	$rslt=mysql_query($stmt, $link);
	while ($row=mysql_fetch_row($rslt)) 
		{
		$list_id=$row[0]; $list_name=$row[1];
		$dispo_ary="";
		$HTML_text.="<FONT SIZE=2><B>List ID #$list_id: $list_name</B>\n";
		$CSV_text.="\"List ID #$list_id: $list_name\"\n";
		# 			$stat_stmt="select vs.status_name, count(*), sum(pause_sec), sum(wait_sec), sum(talk_sec), sum(dispo_sec), sum(dead_sec) from vicidial_agent_log val, vicidial_list vl, vicidial_statuses vs where val.event_time>='$query_date' and val.event_time<='$end_date' and val.campaign_id='$group[$i]' and val.lead_id=vl.list_id and vl.list_id='$list_id' and val.status=vs.status group by vs.status_name order by vs.status_name";
		#$stat_stmt="select val.status, count(*), sum(pause_sec), sum(wait_sec), sum(talk_sec), sum(dispo_sec), sum(dead_sec) from vicidial_agent_log val, vicidial_list vl where val.event_time>='$query_date' and val.event_time<='$end_date' and val.campaign_id='$group[$i]' and val.lead_id=vl.lead_id and vl.list_id='$list_id' group by val.status order by val.status";
		$stat_stmt="select vicidial_log.status, vicidial_log.uniqueid, vicidial_log.length_in_sec as duration, (vicidial_agent_log.talk_sec-vicidial_agent_log.dead_sec) as handle_time from vicidial_log LEFT OUTER JOIN vicidial_agent_log on vicidial_log.lead_id=vicidial_agent_log.lead_id and vicidial_log.uniqueid=vicidial_agent_log.uniqueid where vicidial_log.call_date>='$query_date' and vicidial_log.call_date<='$end_date' and vicidial_log.list_id='$list_id' UNION select vicidial_closer_log.status, vicidial_closer_log.uniqueid, vicidial_closer_log.length_in_sec as duration, (vicidial_agent_log.talk_sec-vicidial_agent_log.dead_sec) as handle_time from vicidial_closer_log LEFT OUTER JOIN vicidial_agent_log on vicidial_closer_log.lead_id=vicidial_agent_log.lead_id and vicidial_closer_log.uniqueid=vicidial_agent_log.uniqueid where call_date>='$query_date' and call_date<='$end_date' and list_id='$list_id' order by status";
		# $HTML_text.=$stat_stmt."\n";
		$stat_rslt=mysql_query($stat_stmt, $link);
		if (mysql_num_rows($stat_rslt)>0) 
			{
			$total_calls=0; $total_handle_time=0; $total_duration=0;
			$HTML_text.="+-------------------------------------+-------+-----------+-------------+\n";
			$HTML_text.="| DISPOSITION                         | CALLS | DURATION  | HANDLE TIME |\n";
			$HTML_text.="+-------------------------------------+-------+-----------+-------------+\n";
			$CSV_text.="\"DISPOSITION\",\"CALLS\",\"DURATION\",\"HANDLE TIME\"\n";
			while ($stat_row=mysql_fetch_row($stat_rslt)) 
				{
				#if ($stat_row[0]=="") {$stat_row[0]="(no dispo)";}
				#$handle_time=sec_convert(($stat_row[4]-$stat_row[6]), 'H');
				#$duration=sec_convert(($stat_row[3]+$stat_row[4]+$stat_row[5]), 'H');
				#$total_handle_time+=($stat_row[4]-$stat_row[6]);
				#$total_duration+=($stat_row[3]+$stat_row[4]+$stat_row[5]);
				$dispo_ary[$stat_row[0]][0]++;
				$dispo_ary[$stat_row[0]][1]+=$stat_row[2];
				$dispo_ary[$stat_row[0]][2]+=$stat_row[3];
				$total_calls++;
				$total_duration+=$stat_row[2];
				$total_handle_time+=$stat_row[3];
				}

			while (list($key, $val)=each($dispo_ary)) 
				{
				$HTML_text.="| ".sprintf("%-35s", $key.$status_ary[$key]);
				$HTML_text.=" | ".sprintf("%5s", $val[0]);
				$HTML_text.=" | ".sprintf("%9s", sec_convert($val[1], 'H'));
				$HTML_text.=" | ".sprintf("%11s", sec_convert($val[2], 'H'))." |\n";
				$CSV_text.="\"".$key.$status_ary[$key]."\",\"$val[0]\",\"".sec_convert($val[1], 'H')."\",\"".sec_convert($val[2], 'H')."\"\n";
				}
			$HTML_text.="+-------------------------------------+-------+-----------+-------------+\n";
			$HTML_text.="|                             TOTALS:";
			$HTML_text.=" | ".sprintf("%5s", $total_calls);
			$HTML_text.=" | ".sprintf("%9s", sec_convert($total_duration, 'H'));
			$HTML_text.=" | ".sprintf("%11s", sec_convert($total_handle_time, 'H'))." |\n";
			$HTML_text.="+-------------------------------------+-------+-----------+-------------+\n";
			$CSV_text.="\"TOTALS:\",\"$total_calls\",\"".sec_convert($total_duration, 'H')."\",\"".sec_convert($total_handle_time, 'H')."\"\n\n";
			}
		else 
			{
			$HTML_text.="<B>***NO CALLS FOUND FROM $query_date TO $end_date***</B>\n";
			$CSV_text.="\"***NO CALLS FOUND FROM $query_date TO $end_date***\"\n\n";
			}
		$HTML_text.="</FONT>\n";
		}
	$i++;
	$HTML_text.="\n\n";
	$CSV_text.="\n\n";
	}
$HTML_text.="</PRE></BODY></HTML>";

if ($file_download>0) 
	{
	$FILE_TIME = date("Ymd-His");
	$CSVfilename = "AST_campaign_status_$US$FILE_TIME.csv";
	$CSV_text=preg_replace('/\n +,/', ',', $CSV_text);
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
	header("Content-type: text/html; charset=utf-8");

	echo $HTML_head;
	require("admin_header.php");
	echo $HTML_text;
	flush();
	}
?>
