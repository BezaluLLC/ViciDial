<?php 
# AST_LISTS_pass_report.php
# 
# Copyright (C) 2014  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# This is a list inventory report, not a calling report. This report will show
# statistics for all of the lists in the selected campaigns
#
# CHANGES
# 140116-0839 - First build based upon AST_LISTS_campaign_stats.php
#

$startMS = microtime();

header ("Content-type: text/html; charset=utf-8");

require("dbconnect_mysqli.php");
require("functions.php");

$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
if (isset($_GET["group"]))				{$group=$_GET["group"];}
	elseif (isset($_POST["group"]))		{$group=$_POST["group"];}
if (isset($_GET["DB"]))					{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))		{$DB=$_POST["DB"];}
if (isset($_GET["submit"]))				{$submit=$_GET["submit"];}
	elseif (isset($_POST["submit"]))	{$submit=$_POST["submit"];}
if (isset($_GET["SUBMIT"]))				{$SUBMIT=$_GET["SUBMIT"];}
	elseif (isset($_POST["SUBMIT"]))	{$SUBMIT=$_POST["SUBMIT"];}
if (isset($_GET["file_download"]))				{$file_download=$_GET["file_download"];}
	elseif (isset($_POST["file_download"]))	{$file_download=$_POST["file_download"];}
if (isset($_GET["report_display_type"]))				{$report_display_type=$_GET["report_display_type"];}
	elseif (isset($_POST["report_display_type"]))	{$report_display_type=$_POST["report_display_type"];}
if (isset($_GET["use_lists"]))			{$use_lists=$_GET["use_lists"];}
	elseif (isset($_POST["use_lists"]))	{$use_lists=$_POST["use_lists"];}


$report_name = 'Lists Pass Report';
$db_source = 'M';
$JS_text="<script language='Javascript'>\n";
$JS_onload="onload = function() {\n";

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,outbound_autodial_active,slave_db_server,reports_use_slave_db FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
if ($DB) {$MAIN.="$stmt\n";}
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =					$row[0];
	$outbound_autodial_active =		$row[1];
	$slave_db_server =				$row[2];
	$reports_use_slave_db =			$row[3];
	}
##### END SETTINGS LOOKUP #####
###########################################

if ($non_latin < 1)
	{
	$PHP_AUTH_USER = preg_replace('/[^-_0-9a-zA-Z]/', '', $PHP_AUTH_USER);
	$PHP_AUTH_PW = preg_replace('/[^-_0-9a-zA-Z]/', '', $PHP_AUTH_PW);
	}
else
	{
	$PHP_AUTH_PW = preg_replace("/'|\"|\\\\|;/","",$PHP_AUTH_PW);
	$PHP_AUTH_USER = preg_replace("/'|\"|\\\\|;/","",$PHP_AUTH_USER);
	}

$auth=0;
$reports_auth=0;
$admin_auth=0;
$auth_message = user_authorization($PHP_AUTH_USER,$PHP_AUTH_PW,'REPORTS',1);
if ($auth_message == 'GOOD')
	{$auth=1;}

if ($auth > 0)
	{
	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 7 and view_reports > 0;";
	if ($DB) {echo "|$stmt|\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$admin_auth=$row[0];

	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 6 and view_reports > 0;";
	if ($DB) {echo "|$stmt|\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$reports_auth=$row[0];

	if ($reports_auth < 1)
		{
		$VDdisplayMESSAGE = "You are not allowed to view reports";
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
		exit;
		}
	if ( ($reports_auth > 0) and ($admin_auth < 1) )
		{
		$ADD=999999;
		$reports_only_user=1;
		}
	}
else
	{
	$VDdisplayMESSAGE = "Login incorrect, please try again";
	if ($auth_message == 'LOCK')
		{
		$VDdisplayMESSAGE = "Too many login attempts, try again in 15 minutes";
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
		exit;
		}
	Header("WWW-Authenticate: Basic realm=\"CONTACT-CENTER-ADMIN\"");
	Header("HTTP/1.0 401 Unauthorized");
	echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$PHP_AUTH_PW|$auth_message|\n";
	exit;
	}


##### BEGIN log visit to the vicidial_report_log table #####
$LOGip = getenv("REMOTE_ADDR");
$LOGbrowser = getenv("HTTP_USER_AGENT");
$LOGscript_name = getenv("SCRIPT_NAME");
$LOGserver_name = getenv("SERVER_NAME");
$LOGserver_port = getenv("SERVER_PORT");
$LOGrequest_uri = getenv("REQUEST_URI");
$LOGhttp_referer = getenv("HTTP_REFERER");
if (preg_match("/443/i",$LOGserver_port)) {$HTTPprotocol = 'https://';}
  else {$HTTPprotocol = 'http://';}
if (($LOGserver_port == '80') or ($LOGserver_port == '443') ) {$LOGserver_port='';}
else {$LOGserver_port = ":$LOGserver_port";}
$LOGfull_url = "$HTTPprotocol$LOGserver_name$LOGserver_port$LOGrequest_uri";

$LOGhostname = php_uname('n');
if (strlen($LOGhostname)<1) {$LOGhostname='X';}
if (strlen($LOGserver_name)<1) {$LOGserver_name='X';}

$stmt="SELECT webserver_id FROM vicidial_webservers where webserver='$LOGserver_name' and hostname='$LOGhostname' LIMIT 1;";
$rslt=mysql_to_mysqli($stmt, $link);
if ($DB) {echo "$stmt\n";}
$webserver_id_ct = mysqli_num_rows($rslt);
if ($webserver_id_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$webserver_id = $row[0];
	}
else
	{
	##### insert webserver entry
	$stmt="INSERT INTO vicidial_webservers (webserver,hostname) values('$LOGserver_name','$LOGhostname');";
	if ($DB) {echo "$stmt\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$affected_rows = mysqli_affected_rows($link);
	$webserver_id = mysqli_insert_id($link);
	}

$stmt="INSERT INTO vicidial_report_log set event_date=NOW(), user='$PHP_AUTH_USER', ip_address='$LOGip', report_name='$report_name', browser='$LOGbrowser', referer='$LOGhttp_referer', notes='$LOGserver_name:$LOGserver_port $LOGscript_name |$group[0], $query_date, $end_date, $shift, $file_download, $report_display_type|', url='$LOGfull_url', webserver='$webserver_id';";
if ($DB) {echo "|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$report_log_id = mysqli_insert_id($link);
##### END log visit to the vicidial_report_log table #####

if ( (strlen($slave_db_server)>5) and (preg_match("/$report_name/",$reports_use_slave_db)) )
	{
	mysqli_close($link);
	$use_slave_server=1;
	$db_source = 'S';
	require("dbconnect_mysqli.php");
	$MAIN.="<!-- Using slave server $slave_db_server $db_source -->\n";
	}

$stmt="SELECT user_group from vicidial_users where user='$PHP_AUTH_USER';";
if ($DB) {$MAIN.="|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$LOGuser_group =			$row[0];

$stmt="SELECT allowed_campaigns,allowed_reports from vicidial_user_groups where user_group='$LOGuser_group';";
if ($DB) {$MAIN.="|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$LOGallowed_campaigns = $row[0];
$LOGallowed_reports =	$row[1];

if ( (!preg_match("/$report_name/",$LOGallowed_reports)) and (!preg_match("/ALL REPORTS/",$LOGallowed_reports)) )
	{
    Header("WWW-Authenticate: Basic realm=\"CONTACT-CENTER-ADMIN\"");
    Header("HTTP/1.0 401 Unauthorized");
    echo "You are not allowed to view this report: |$PHP_AUTH_USER|$report_name|\n";
    exit;
	}

$NOW_DATE = date("Y-m-d");
$NOW_TIME = date("Y-m-d H:i:s");
$STARTtime = date("U");
if (!isset($group)) {$group = '';}

$i=0;
$group_string='|';
$group_ct = count($group);
while($i < $group_ct)
	{
	$group_string .= "$group[$i]|";
	$i++;
	}

$LOGallowed_campaignsSQL='';
$whereLOGallowed_campaignsSQL='';
if ( (!preg_match('/\-ALL/i', $LOGallowed_campaigns)) )
	{
	$rawLOGallowed_campaignsSQL = preg_replace("/ -/",'',$LOGallowed_campaigns);
	$rawLOGallowed_campaignsSQL = preg_replace("/ /","','",$rawLOGallowed_campaignsSQL);
	$LOGallowed_campaignsSQL = "and campaign_id IN('$rawLOGallowed_campaignsSQL')";
	$whereLOGallowed_campaignsSQL = "where campaign_id IN('$rawLOGallowed_campaignsSQL')";
	}
$regexLOGallowed_campaigns = " $LOGallowed_campaigns ";

if ($use_lists < 1)
	{
	$stmt="select campaign_id,campaign_name from vicidial_campaigns $whereLOGallowed_campaignsSQL order by campaign_id;";
	$rslt=mysql_to_mysqli($stmt, $link);
	if ($DB) {$MAIN.="$stmt\n";}
	$campaigns_to_print = mysqli_num_rows($rslt);
	$i=0;
	while ($i < $campaigns_to_print)
		{
		$row=mysqli_fetch_row($rslt);
		$groups[$i] =		$row[0];
		$group_names[$i] =	$row[1];
		if (preg_match('/\-ALL/',$group_string) )
			{$group[$i] = $groups[$i];}
		$i++;
		}
	}
else
	{
	$stmt="select list_id,list_name from vicidial_lists $whereLOGallowed_campaignsSQL order by list_id;";
	$rslt=mysql_to_mysqli($stmt, $link);
	if ($DB) {$MAIN.="$stmt\n";}
	$campaigns_to_print = mysqli_num_rows($rslt);
	$i=0;
	while ($i < $campaigns_to_print)
		{
		$row=mysqli_fetch_row($rslt);
		$groups[$i] =		$row[0];
		$group_names[$i] =	$row[1];
		if (preg_match('/\-ALL/',$group_string) )
			{$group[$i] = $groups[$i];}
		$i++;
		}
	}

$rollover_groups_count=0;
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

if ($use_lists < 1)
	{
	if ( (preg_match('/\-\-ALL\-\-/',$group_string) ) or ($group_ct < 1) or (strlen($group_string) < 2) )
		{
		$group_SQL = "$LOGallowed_campaignsSQL";
		}
	else
		{
		$group_SQL = preg_replace('/,$/i', '',$group_SQL);
		$group_SQLand = "and campaign_id IN($group_SQL)";
		$group_SQL = "where campaign_id IN($group_SQL)";
		}
	}
else
	{
	if ( (preg_match('/\-\-ALL\-\-/',$group_string) ) or ($group_ct < 1) or (strlen($group_string) < 2) )
		{
		$group_SQL = "where list_id IN($group_SQL)";
		}
	else
		{
		$group_SQL = preg_replace('/,$/i', '',$group_SQL);
		$group_SQLand = "and list_id IN($group_SQL)";
		$group_SQL = "where list_id IN($group_SQL)";
		}

	}

# Get lists to query to avoid using a nested query
$lists_id_str="";
$list_stmt="SELECT list_id from vicidial_lists where active IN('Y','N') $group_SQLand";
$list_rslt=mysql_to_mysqli($list_stmt, $link);
while ($lrow=mysqli_fetch_row($list_rslt)) 
	{
	$lists_id_str.="'$lrow[0]',";
	}
$lists_id_str=substr($lists_id_str,0,-1);

$stmt="select vsc_id,vsc_name from vicidial_status_categories;";
$rslt=mysql_to_mysqli($stmt, $link);
if ($DB) {echo "$stmt\n";}
$statcats_to_print = mysqli_num_rows($rslt);
$i=0;
while ($i < $statcats_to_print)
	{
	$row=mysqli_fetch_row($rslt);
	$vsc_id[$i] =	$row[0];
	$vsc_name[$i] =	$row[1];

	$category_statuses="";
	$status_stmt="select distinct status from vicidial_statuses where category='$row[0]' UNION select distinct status from vicidial_campaign_statuses where category='$row[0]' $group_SQLand";
	if ($DB) {echo "$status_stmt\n";}
	$status_rslt=mysql_to_mysqli($status_stmt, $link);
	while ($status_row=mysqli_fetch_row($status_rslt)) 
		{
		$category_statuses.="'$status_row[0]',";
        }
	$category_statuses=substr($category_statuses, 0, -1);

	$category_stmt="select count(*) from vicidial_list where status in ($category_statuses) and list_id IN($lists_id_str)";
	if ($DB) {echo "$category_stmt\n";}
	$category_rslt=mysql_to_mysqli($category_stmt, $link);
	$category_row=mysqli_fetch_row($category_rslt);
	$vsc_count[$i] = $category_row[0];
	$i++;
	}


### BEGIN gather all statuses that are in status flags  ###
$human_answered_statuses='';
$sale_statuses='';
$dnc_statuses='';
$customer_contact_statuses='';
$not_interested_statuses='';
$unworkable_statuses='';
$stmt="select status,human_answered,sale,dnc,customer_contact,not_interested,unworkable,scheduled_callback,completed,status_name from vicidial_statuses;";
$rslt=mysql_to_mysqli($stmt, $link);
if ($DB) {$MAIN.="$stmt\n";}
$statha_to_print = mysqli_num_rows($rslt);
$i=0;
while ($i < $statha_to_print)
	{
	$row=mysqli_fetch_row($rslt);
	$temp_status = $row[0];
	$statname_list["$temp_status"] = "$row[9]";
	if ($row[1]=='Y') {$human_answered_statuses .= "'$temp_status',";}
	if ($row[2]=='Y') {$sale_statuses .= "'$temp_status',";}
	if ($row[3]=='Y') {$dnc_statuses .= "'$temp_status',";}
	if ($row[4]=='Y') {$customer_contact_statuses .= "'$temp_status',";}
	if ($row[5]=='Y') {$not_interested_statuses .= "'$temp_status',";}
	if ($row[6]=='Y') {$unworkable_statuses .= "'$temp_status',";}
	if ($row[7]=='Y') {$scheduled_callback_statuses .= "'$temp_status',";}
	if ($row[8]=='Y') {$completed_statuses .= "'$temp_status',";}
	$i++;
	}
$stmt="select status,human_answered,sale,dnc,customer_contact,not_interested,unworkable,scheduled_callback,completed,status_name from vicidial_campaign_statuses where selectable IN('Y','N') $group_SQLand;";
$rslt=mysql_to_mysqli($stmt, $link);
if ($DB) {$MAIN.="$stmt\n";}
$statha_to_print = mysqli_num_rows($rslt);
$i=0;
while ($i < $statha_to_print)
	{
	$row=mysqli_fetch_row($rslt);
	$temp_status = $row[0];
	$statname_list["$temp_status"] = "$row[9]";
	if ( ($row[1]=='Y') and (!preg_match("/'$temp_status'/",$human_answered_statuses)) ) {$human_answered_statuses .= "'$temp_status',";}
	if ($row[2]=='Y') {$sale_statuses .= "'$temp_status',";}
	if ($row[3]=='Y') {$dnc_statuses .= "'$temp_status',";}
	if ($row[4]=='Y') {$customer_contact_statuses .= "'$temp_status',";}
	if ($row[5]=='Y') {$not_interested_statuses .= "'$temp_status',";}
	if ($row[6]=='Y') {$unworkable_statuses .= "'$temp_status',";}
	if ($row[7]=='Y') {$scheduled_callback_statuses .= "'$temp_status',";}
	if ($row[8]=='Y') {$completed_statuses .= "'$temp_status',";}
	$i++;
	}
if (strlen($human_answered_statuses)>2)		{$human_answered_statuses = substr("$human_answered_statuses", 0, -1);}
else {$human_answered_statuses="''";}
if (strlen($sale_statuses)>2)				{$sale_statuses = substr("$sale_statuses", 0, -1);}
else {$sale_statuses="''";}
if (strlen($dnc_statuses)>2)				{$dnc_statuses = substr("$dnc_statuses", 0, -1);}
else {$dnc_statuses="''";}
if (strlen($customer_contact_statuses)>2)	{$customer_contact_statuses = substr("$customer_contact_statuses", 0, -1);}
else {$customer_contact_statuses="''";}
if (strlen($not_interested_statuses)>2)		{$not_interested_statuses = substr("$not_interested_statuses", 0, -1);}
else {$not_interested_statuses="''";}
if (strlen($unworkable_statuses)>2)			{$unworkable_statuses = substr("$unworkable_statuses", 0, -1);}
else {$unworkable_statuses="''";}
if (strlen($scheduled_callback_statuses)>2)			{$scheduled_callback_statuses = substr("$scheduled_callback_statuses", 0, -1);}
else {$scheduled_callback_statuses="''";}
if (strlen($completed_statuses)>2)			{$completed_statuses = substr("$completed_statuses", 0, -1);}
else {$completed_statuses="''";}














































$HEADER.="<HTML>\n";
$HEADER.="<HEAD>\n";
$HEADER.="<STYLE type=\"text/css\">\n";
$HEADER.="<!--\n";
$HEADER.="   .green {color: white; background-color: green}\n";
$HEADER.="   .red {color: white; background-color: red}\n";
$HEADER.="   .blue {color: white; background-color: blue}\n";
$HEADER.="   .purple {color: white; background-color: purple}\n";
$HEADER.="-->\n";
$HEADER.=" </STYLE>\n";
$HEADER.="<link rel=\"stylesheet\" href=\"horizontalbargraph.css\">\n";

$HEADER.="<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">\n";
$HEADER.="<TITLE>$report_name</TITLE></HEAD><BODY BGCOLOR=WHITE marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>\n";

$short_header=1;

$MAIN.="<TABLE CELLPADDING=4 CELLSPACING=0><TR><TD>";

$MAIN.="<FORM ACTION=\"$PHP_SELF\" METHOD=GET name=vicidial_report id=vicidial_report>\n";
$MAIN.="<TABLE CELLSPACING=3><TR><TD VALIGN=TOP>";
$MAIN.="<INPUT TYPE=HIDDEN NAME=DB VALUE=\"$DB\">\n";
$MAIN.="<INPUT TYPE=HIDDEN NAME=use_lists VALUE=\"$use_lists\">\n";

if ($use_lists > 0)
	{
	$MAIN.="</TD><TD VALIGN=TOP> Lists:<BR>";
	$MAIN.="<SELECT SIZE=5 NAME=group[] multiple>\n";
	if  (preg_match('/\-\-ALL\-\-/',$group_string))
		{$MAIN.="<option value=\"--ALL--\" selected>-- ALL LISTS --</option>\n";}
	else
		{$MAIN.="<option value=\"--ALL--\">-- ALL LISTS --</option>\n";}
	$o=0;
	while ($campaigns_to_print > $o)
		{
		if (preg_match("/$groups[$o]\|/i",$group_string)) {$MAIN.="<option selected value=\"$groups[$o]\">$groups[$o] - $group_names[$o]</option>\n";}
		  else {$MAIN.="<option value=\"$groups[$o]\">$groups[$o] - $group_names[$o]</option>\n";}
		$o++;
		}
	$MAIN.="</SELECT>\n<BR>\n";
	$MAIN.="<a href=\"$PHP_SELF?use_lists=0&DB=$DB\">SWITCH TO CAMPAIGNS</a>";
	}
else
	{
	$MAIN.="</TD><TD VALIGN=TOP> Campaigns:<BR>";
	$MAIN.="<SELECT SIZE=5 NAME=group[] multiple>\n";
	if  (preg_match('/\-\-ALL\-\-/',$group_string))
		{$MAIN.="<option value=\"--ALL--\" selected>-- ALL CAMPAIGNS --</option>\n";}
	else
		{$MAIN.="<option value=\"--ALL--\">-- ALL CAMPAIGNS --</option>\n";}
	$o=0;
	while ($campaigns_to_print > $o)
		{
		if (preg_match("/$groups[$o]\|/i",$group_string)) {$MAIN.="<option selected value=\"$groups[$o]\">$groups[$o] - $group_names[$o]</option>\n";}
		  else {$MAIN.="<option value=\"$groups[$o]\">$groups[$o] - $group_names[$o]</option>\n";}
		$o++;
		}
	$MAIN.="</SELECT>\n<BR>\n";
	$MAIN.="<a href=\"$PHP_SELF?use_lists=1&DB=$DB\">SWITCH TO LISTS</a>";
	}
$MAIN.="</TD><TD VALIGN=TOP>";
$MAIN.="Display as:<BR/>";
$MAIN.="<select name='report_display_type'>";
if ($report_display_type) {$MAIN.="<option value='$report_display_type' selected>$report_display_type</option>";}
$MAIN.="<option value='TEXT'>TEXT</option><option value='HTML'>HTML</option></select>&nbsp; ";
$MAIN.="<BR><BR>\n";
$MAIN.="<INPUT type=submit NAME=SUBMIT VALUE=SUBMIT>\n";
$MAIN.="</TD><TD VALIGN=TOP> &nbsp; &nbsp; &nbsp; &nbsp; ";
$MAIN.="<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2>";
if (strlen($group[0]) > 1)
	{
	$MAIN.=" <a href=\"./admin.php?ADD=34&campaign_id=$group[0]\">MODIFY</a> | \n";
	$MAIN.=" <a href=\"./admin.php?ADD=999999\">REPORTS</a> </FONT>\n";
	}
else
	{
	$MAIN.=" <a href=\"./admin.php?ADD=10\">CAMPAIGNS</a> | \n";
	$MAIN.=" <a href=\"./admin.php?ADD=999999\">REPORTS</a> </FONT>\n";
	}
$MAIN.="</TD></TR></TABLE>";
$MAIN.="</FORM>\n\n";

$MAIN.="<PRE><FONT SIZE=2>\n\n";


if (strlen($group[0]) < 1)
	{
	$MAIN.="\n\n";
	$MAIN.="PLEASE SELECT A CAMPAIGN AND DATE ABOVE AND CLICK SUBMIT\n";
	}

else
	{
	$OUToutput = '';
	$OUToutput .= "Lists Pass Report                             $NOW_TIME\n";

	$OUToutput .= "\n";

	##############################
	#########  LIST ID BREAKDOWN STATS

	$TOTALleads = 0;

	$OUToutput .= "\n";
	$OUToutput .= "---------- LIST ID SUMMARY     <a href=\"$PHP_SELF?DB=$DB$groupQS&SUBMIT=$SUBMIT&file_download=1\">DOWNLOAD</a>\n";

	$OUToutput .= "+------------+------------------------------------------+----------+------------+----------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "\n";

	$OUToutput .= "|   FIRST    |                                          |          | LEAD       |          |";
	$OUToutput .= " CONTACTS| CONTACTS| CONTACTS| CONTACTS| CONTACTS|";
	$OUToutput .= " CNT RATE| CNT RATE| CNT RATE| CNT RATE| CNT RATE|";
	$OUToutput .= "   SALES |   SALES |   SALES |   SALES |   SALES |";
	$OUToutput .= "CONV RATE|CONV RATE|CONV RATE|CONV RATE|CONV RATE|";
	$OUToutput .= "   DNC   |   DNC   |   DNC   |   DNC   |   DNC   |";
	$OUToutput .= " DNC RATE| DNC RATE| DNC RATE| DNC RATE| DNC RATE|";
	$OUToutput .= "CUST CONT|CUST CONT|CUST CONT|CUST CONT|CUST CONT|";
	$OUToutput .= "CUCT RATE|CUCT RATE|CUCT RATE|CUCT RATE|CUCT RATE|";
	$OUToutput .= "UNWORKABL|UNWORKABL|UNWORKABL|UNWORKABL|UNWORKABL|";
	$OUToutput .= "UNWK RATE|UNWK RATE|UNWK RATE|UNWK RATE|UNWK RATE|";
	$OUToutput .= "SCHEDL CB|SCHEDL CB|SCHEDL CB|SCHEDL CB|SCHEDL CB|";
	$OUToutput .= "SHCB RATE|SHCB RATE|SHCB RATE|SHCB RATE|SHCB RATE|";
	$OUToutput .= "COMPLETED|COMPLETED|COMPLETED|COMPLETED|COMPLETED|";
	$OUToutput .= "COMP RATE|COMP RATE|COMP RATE|COMP RATE|COMP RATE|";
	$OUToutput .= "\n";

	$OUToutput .= "|  LOAD DATE | LIST ID and NAME                         | CAMPAIGN | COUNT      | ACTIVE   |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= " 1st PASS| 2nd PASS| 3rd PASS| 3 Total |    LIFE |";
	$OUToutput .= "\n";

	$OUToutput .= "+------------+------------------------------------------+----------+------------+----------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "\n";


	$CSV_text1.="\"LIST ID SUMMARY\"\n";
	$CSV_text1.="\"FIRST LOAD DATE\",\"LIST\",\"CAMPAIGN\",\"LEADS\",\"ACTIVE\"";
	$CSV_text1.=",\"CONTACTS 1st PASS\",\"CONTACTS 2nd PASS\",\"CONTACTS 3rd PASS\",\"CONTACTS 3 Total\",\"CONTACTS LIFE\"";
	$CSV_text1.=",\"CNT RATE 1st PASS\",\"CNT RATE 2nd PASS\",\"CNT RATE 3rd PASS\",\"CNT RATE 3 Total\",\"CNT RATE LIFE\"";
	$CSV_text1.=",\"SALES 1st PASS\",\"SALES 2nd PASS\",\"SALES 3rd PASS\",\"SALES 3 Total\",\"SALES LIFE\"";
	$CSV_text1.=",\"CONV RATE 1st PASS\",\"CONV RATE 2nd PASS\",\"CONV RATE 3rd PASS\",\"CONV RATE 3 Total\",\"CONV RATE LIFE\"";
	$CSV_text1.=",\"DNC 1st PASS\",\"DNC 2nd PASS\",\"DNC 3rd PASS\",\"DNC 3 Total\",\"DNC LIFE\"";
	$CSV_text1.=",\"DNC RATE 1st PASS\",\"DNC RATE 2nd PASS\",\"DNC RATE 3rd PASS\",\"DNC RATE 3 Total\",\"DNC RATE LIFE\"";
	$CSV_text1.=",\"CUSTOMER CONTACT 1st PASS\",\"CUSTOMER CONTACT 2nd PASS\",\"CUSTOMER CONTACT 3rd PASS\",\"CUSTOMER CONTACT 3 Total\",\"CUSTOMER CONTACT LIFE\"";
	$CSV_text1.=",\"CUSTOMER CONTACT RATE 1st PASS\",\"CUSTOMER CONTACT RATE 2nd PASS\",\"CUSTOMER CONTACT RATE 3rd PASS\",\"CUSTOMER CONTACT RATE 3 Total\",\"CUSTOMER CONTACT RATE LIFE\"";
	$CSV_text1.=",\"UNWORKABLE 1st PASS\",\"UNWORKABLE 2nd PASS\",\"UNWORKABLE 3rd PASS\",\"UNWORKABLE 3 Total\",\"UNWORKABLE LIFE\"";
	$CSV_text1.=",\"UNWORKABLE RATE 1st PASS\",\"UNWORKABLE RATE 2nd PASS\",\"UNWORKABLE RATE 3rd PASS\",\"UNWORKABLE RATE 3 Total\",\"UNWORKABLE RATE LIFE\"";
	$CSV_text1.=",\"SCHEDULED CALLBACK 1st PASS\",\"SCHEDULED CALLBACK 2nd PASS\",\"SCHEDULED CALLBACK 3rd PASS\",\"SCHEDULED CALLBACK 3 Total\",\"SCHEDULED CALLBACK LIFE\"";
	$CSV_text1.=",\"SCHEDULED CALLBACK RATE 1st PASS\",\"SCHEDULED CALLBACK RATE 2nd PASS\",\"SCHEDULED CALLBACK RATE 3rd PASS\",\"SCHEDULED CALLBACK RATE 3 Total\",\"SCHEDULED CALLBACK RATE LIFE\"";
	$CSV_text1.=",\"COMPLETED 1st PASS\",\"COMPLETED 2nd PASS\",\"COMPLETED 3rd PASS\",\"COMPLETED 3 Total\",\"COMPLETED LIFE\"";
	$CSV_text1.=",\"COMPLETED RATE 1st PASS\",\"COMPLETED RATE 2nd PASS\",\"COMPLETED RATE 3rd PASS\",\"COMPLETED RATE 3 Total\",\"COMPLETED RATE LIFE\"";
	$CSV_text1.="\n";

	$max_calls=1; $graph_stats=array();

	$lists_id_str="";
	$list_stmt="SELECT list_id from vicidial_lists where active IN('Y','N') $group_SQLand";
	$list_rslt=mysql_to_mysqli($list_stmt, $link);
	while ($lrow=mysqli_fetch_row($list_rslt)) 
		{
		$lists_id_str.="'$lrow[0]',";
		}
	$lists_id_str=substr($lists_id_str,0,-1);

	$stmt="select count(*),list_id from vicidial_list where list_id IN($lists_id_str) group by list_id;";
	$rslt=mysql_to_mysqli($stmt, $link);
	if ($DB) {$MAIN.="$stmt\n";}
	$listids_to_print = mysqli_num_rows($rslt);
	$i=0;
	while ($i < $listids_to_print)
		{
		$row=mysqli_fetch_row($rslt);
		$LISTIDcalls[$i] =	$row[0];
		$LISTIDlists[$i] =	$row[1];
		$list_id_SQL .=		"'$row[1]',";
		if ($row[0]>$max_calls) {$max_calls=$row[0];}
		$graph_stats[$i][0]=$row[0];
		$graph_stats[$i][1]=$row[1];
		$i++;
		}
	if (strlen($list_id_SQL)>2)		{$list_id_SQL = substr("$list_id_SQL", 0, -1);}
	else {$list_id_SQL="''";}

	$i=0;
	while ($i < $listids_to_print)
		{
		$stmt="select list_name,active,campaign_id from vicidial_lists where list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$list_name_to_print = mysqli_num_rows($rslt);
		if ($list_name_to_print > 0)
			{
			$row=mysqli_fetch_row($rslt);
			$LISTIDlist_names[$i] =	$row[0];
			$LISTIDcampaign[$i] =	$row[2];
			$graph_stats[$i][1].=" - $row[0]";
			if ($row[1]=='Y')
				{$LISTIDlist_active[$i] = 'ACTIVE  '; $graph_stats[$i][1].=" (ACTIVE)";}
			else
				{$LISTIDlist_active[$i] = 'INACTIVE'; $graph_stats[$i][1].=" (INACTIVE)";}
			}

		$LISTIDentry_date[$i]='';
		$stmt="select entry_date from vicidial_list where list_id='$LISTIDlists[$i]' order by entry_date limit 1;";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$list_name_to_print = mysqli_num_rows($rslt);
		if ($list_name_to_print > 0)
			{
			$row=mysqli_fetch_row($rslt);
			$LISTIDentry_date[$i] =	$row[0];
			}

		$TOTALleads = ($TOTALleads + $LISTIDcalls[$i]);
		$LISTIDentry_dateS =	sprintf("%10s", $LISTIDentry_date[$i]); while(strlen($LISTIDentry_dateS)>10) {$LISTIDentry_dateS = substr("$LISTIDentry_dateS", 0, -1);}
		$LISTIDcampaignS =	sprintf("%8s", $LISTIDcampaign[$i]); while(strlen($LISTIDcampaignS)>8) {$LISTIDcampaignS = substr("$LISTIDcampaignS", 0, -1);}
		$LISTIDname =	sprintf("%-40s", "$LISTIDlists[$i] - $LISTIDlist_names[$i]"); while(strlen($LISTIDname)>40) {$LISTIDname = substr("$LISTIDname", 0, -1);}
		$LISTIDcount =	sprintf("%10s", $LISTIDcalls[$i]); while(strlen($LISTIDcount)>10) {$LISTIDcount = substr("$LISTIDcount", 0, -1);}



		########################################################
		########## BEGIN CONTACTS (Human-Answer flag) ##########

		$HA_count=0; $HA_one_count=0; $HA_two_count=0; $HA_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($human_answered_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$HA_results = mysqli_num_rows($rslt);
		if ($HA_results > 0)
			{$row=mysqli_fetch_row($rslt); $HA_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($human_answered_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$HA_one_results = mysqli_num_rows($rslt);
		if ($HA_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $HA_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($human_answered_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$HA_two_results = mysqli_num_rows($rslt);
		if ($HA_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $HA_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($human_answered_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$HA_three_results = mysqli_num_rows($rslt);
		if ($HA_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $HA_three_count = $row[0];}
		$HA_threeT_count = ($HA_one_count + $HA_two_count + $HA_three_count);

		$HA_countS =	sprintf("%7s", $HA_count); while(strlen($HA_countS)>7) {$HA_countS = substr("$HA_countS", 0, -1);}
		$HA_one_countS =	sprintf("%7s", $HA_one_count); while(strlen($HA_one_countS)>7) {$HA_one_countS = substr("$HA_one_countS", 0, -1);}
		$HA_two_countS =	sprintf("%7s", $HA_two_count); while(strlen($HA_two_countS)>7) {$HA_two_countS = substr("$HA_two_countS", 0, -1);}
		$HA_three_countS =	sprintf("%7s", $HA_three_count); while(strlen($HA_three_countS)>7) {$HA_three_countS = substr("$HA_three_countS", 0, -1);}
		$HA_threeT_countS =	sprintf("%7s", $HA_threeT_count); while(strlen($HA_threeT_countS)>7) {$HA_threeT_countS = substr("$HA_threeT_countS", 0, -1);}

		$HA_count_tot =	($HA_count + $HA_count_tot);
		$HA_one_count_tot =	($HA_one_count + $HA_one_count_tot);
		$HA_two_count_tot =	($HA_two_count + $HA_two_count_tot);
		$HA_three_count_tot =	($HA_three_count + $HA_three_count_tot);
		$HA_threeT_count_tot =	($HA_threeT_count + $HA_threeT_count_tot);

		########## END CONTACTS (Human-Answer flag) ##########
		########################################################


		########################################################
		########## BEGIN CONTACT RATIO (Human-Answer flag out of total leads percentage) ##########

		$HR_count=0; $HR_one_count=0; $HR_two_count=0; $HR_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($human_answered_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$HR_results = mysqli_num_rows($rslt);
		if ($HR_results > 0)
			{$row=mysqli_fetch_row($rslt); $HR_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($human_answered_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$HR_one_results = mysqli_num_rows($rslt);
		if ($HR_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $HR_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($human_answered_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$HR_two_results = mysqli_num_rows($rslt);
		if ($HR_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $HR_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($human_answered_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$HR_three_results = mysqli_num_rows($rslt);
		if ($HR_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $HR_three_count = $row[0];}
		$HR_threeT_count = ($HR_one_count + $HR_two_count + $HR_three_count);

		$HR_count_pct=0;
		$HR_one_count_pct=0;
		$HR_two_count_pct=0;
		$HR_three_count_pct=0;
		$HR_threeT_count_pct=0;
		if ( ($HR_count > 0) and ($LISTIDcalls[$i] > 0) )   {$HR_count_pct = (($HR_count / $LISTIDcalls[$i]) * 100);}
		if ( ($HR_one_count > 0) and ($LISTIDcalls[$i] > 0) )   {$HR_one_count_pct = (($HR_one_count / $LISTIDcalls[$i]) * 100);}
		if ( ($HR_two_count > 0) and ($LISTIDcalls[$i] > 0) )   {$HR_two_count_pct = (($HR_two_count / $LISTIDcalls[$i]) * 100);}
		if ( ($HR_three_count > 0) and ($LISTIDcalls[$i] > 0) )   {$HR_three_count_pct = (($HR_three_count / $LISTIDcalls[$i]) * 100);}
		if ( ($HR_threeT_count > 0) and ($LISTIDcalls[$i] > 0) )   {$HR_threeT_count_pct = (($HR_threeT_count / $LISTIDcalls[$i]) * 100);}

		$HR_countS =	sprintf("%6.2f", $HR_count_pct); while(strlen($HR_countS)>7) {$HR_countS = substr("$HR_countS", 0, -1);}
		$HR_one_countS =	sprintf("%6.2f", $HR_one_count_pct); while(strlen($HR_one_countS)>7) {$HR_one_countS = substr("$HR_one_countS", 0, -1);}
		$HR_two_countS =	sprintf("%6.2f", $HR_two_count_pct); while(strlen($HR_two_countS)>7) {$HR_two_countS = substr("$HR_two_countS", 0, -1);}
		$HR_three_countS =	sprintf("%6.2f", $HR_three_count_pct); while(strlen($HR_three_countS)>7) {$HR_three_countS = substr("$HR_three_countS", 0, -1);}
		$HR_threeT_countS =	sprintf("%6.2f", $HR_threeT_count_pct); while(strlen($HR_threeT_countS)>7) {$HR_threeT_countS = substr("$HR_threeT_countS", 0, -1);}

		$HR_count_tot =	($HR_count + $HR_count_tot);
		$HR_one_count_tot =	($HR_one_count + $HR_one_count_tot);
		$HR_two_count_tot =	($HR_two_count + $HR_two_count_tot);
		$HR_three_count_tot =	($HR_three_count + $HR_three_count_tot);
		$HR_threeT_count_tot =	($HR_threeT_count + $HR_threeT_count_tot);

		########## END  CONTACT RATIO (Human-Answer flag out of total leads percentage) ##########
		########################################################


		########################################################
		########## BEGIN SALES (Sales flag) ##########

		$SA_count=0; $SA_one_count=0; $SA_two_count=0; $SA_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($sale_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$SA_results = mysqli_num_rows($rslt);
		if ($SA_results > 0)
			{$row=mysqli_fetch_row($rslt); $SA_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($sale_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$SA_one_results = mysqli_num_rows($rslt);
		if ($SA_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $SA_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($sale_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$SA_two_results = mysqli_num_rows($rslt);
		if ($SA_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $SA_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($sale_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$SA_three_results = mysqli_num_rows($rslt);
		if ($SA_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $SA_three_count = $row[0];}
		$SA_threeT_count = ($SA_one_count + $SA_two_count + $SA_three_count);

		$SA_countS =	sprintf("%7s", $SA_count); while(strlen($SA_countS)>7) {$SA_countS = substr("$SA_countS", 0, -1);}
		$SA_one_countS =	sprintf("%7s", $SA_one_count); while(strlen($SA_one_countS)>7) {$SA_one_countS = substr("$SA_one_countS", 0, -1);}
		$SA_two_countS =	sprintf("%7s", $SA_two_count); while(strlen($SA_two_countS)>7) {$SA_two_countS = substr("$SA_two_countS", 0, -1);}
		$SA_three_countS =	sprintf("%7s", $SA_three_count); while(strlen($SA_three_countS)>7) {$SA_three_countS = substr("$SA_three_countS", 0, -1);}
		$SA_threeT_countS =	sprintf("%7s", $SA_threeT_count); while(strlen($SA_threeT_countS)>7) {$SA_threeT_countS = substr("$SA_threeT_countS", 0, -1);}

		$SA_count_tot =	($SA_count + $SA_count_tot);
		$SA_one_count_tot =	($SA_one_count + $SA_one_count_tot);
		$SA_two_count_tot =	($SA_two_count + $SA_two_count_tot);
		$SA_three_count_tot =	($SA_three_count + $SA_three_count_tot);
		$SA_threeT_count_tot =	($SA_threeT_count + $SA_threeT_count_tot);

		########## END SALES (Sales flag) ##########
		########################################################


		########################################################
		########## BEGIN CONV SALES RATIO (Sales flag out of total leads percentage) ##########

		$SR_count=0; $SR_one_count=0; $SR_two_count=0; $SR_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($sale_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$SR_results = mysqli_num_rows($rslt);
		if ($SR_results > 0)
			{$row=mysqli_fetch_row($rslt); $SR_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($sale_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$SR_one_results = mysqli_num_rows($rslt);
		if ($SR_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $SR_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($sale_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$SR_two_results = mysqli_num_rows($rslt);
		if ($SR_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $SR_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($sale_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$SR_three_results = mysqli_num_rows($rslt);
		if ($SR_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $SR_three_count = $row[0];}
		$SR_threeT_count = ($SR_one_count + $SR_two_count + $SR_three_count);

		$SR_count_pct=0;
		$SR_one_count_pct=0;
		$SR_two_count_pct=0;
		$SR_three_count_pct=0;
		$SR_threeT_count_pct=0;
		if ( ($SR_count > 0) and ($LISTIDcalls[$i] > 0) )   {$SR_count_pct = (($SR_count / $LISTIDcalls[$i]) * 100);}
		if ( ($SR_one_count > 0) and ($LISTIDcalls[$i] > 0) )   {$SR_one_count_pct = (($SR_one_count / $LISTIDcalls[$i]) * 100);}
		if ( ($SR_two_count > 0) and ($LISTIDcalls[$i] > 0) )   {$SR_two_count_pct = (($SR_two_count / $LISTIDcalls[$i]) * 100);}
		if ( ($SR_three_count > 0) and ($LISTIDcalls[$i] > 0) )   {$SR_three_count_pct = (($SR_three_count / $LISTIDcalls[$i]) * 100);}
		if ( ($SR_threeT_count > 0) and ($LISTIDcalls[$i] > 0) )   {$SR_threeT_count_pct = (($SR_threeT_count / $LISTIDcalls[$i]) * 100);}

		$SR_countS =	sprintf("%6.2f", $SR_count_pct); while(strlen($SR_countS)>7) {$SR_countS = substr("$SR_countS", 0, -1);}
		$SR_one_countS =	sprintf("%6.2f", $SR_one_count_pct); while(strlen($SR_one_countS)>7) {$SR_one_countS = substr("$SR_one_countS", 0, -1);}
		$SR_two_countS =	sprintf("%6.2f", $SR_two_count_pct); while(strlen($SR_two_countS)>7) {$SR_two_countS = substr("$SR_two_countS", 0, -1);}
		$SR_three_countS =	sprintf("%6.2f", $SR_three_count_pct); while(strlen($SR_three_countS)>7) {$SR_three_countS = substr("$SR_three_countS", 0, -1);}
		$SR_threeT_countS =	sprintf("%6.2f", $SR_threeT_count_pct); while(strlen($SR_threeT_countS)>7) {$SR_threeT_countS = substr("$SR_threeT_countS", 0, -1);}

		$SR_count_tot =	($SR_count + $SR_count_tot);
		$SR_one_count_tot =	($SR_one_count + $SR_one_count_tot);
		$SR_two_count_tot =	($SR_two_count + $SR_two_count_tot);
		$SR_three_count_tot =	($SR_three_count + $SR_three_count_tot);
		$SR_threeT_count_tot =	($SR_threeT_count + $SR_threeT_count_tot);

		########## END   CONV SALES RATIO (Sales flag out of total leads percentage) ##########
		########################################################


		########################################################
		########## BEGIN DNC (DNC flag) ##########

		$DN_count=0; $DN_one_count=0; $DN_two_count=0; $DN_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($dnc_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$DN_results = mysqli_num_rows($rslt);
		if ($DN_results > 0)
			{$row=mysqli_fetch_row($rslt); $DN_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($dnc_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$DN_one_results = mysqli_num_rows($rslt);
		if ($DN_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $DN_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($dnc_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$DN_two_results = mysqli_num_rows($rslt);
		if ($DN_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $DN_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($dnc_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$DN_three_results = mysqli_num_rows($rslt);
		if ($DN_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $DN_three_count = $row[0];}
		$DN_threeT_count = ($DN_one_count + $DN_two_count + $DN_three_count);

		$DN_countS =	sprintf("%7s", $DN_count); while(strlen($DN_countS)>7) {$DN_countS = substr("$DN_countS", 0, -1);}
		$DN_one_countS =	sprintf("%7s", $DN_one_count); while(strlen($DN_one_countS)>7) {$DN_one_countS = substr("$DN_one_countS", 0, -1);}
		$DN_two_countS =	sprintf("%7s", $DN_two_count); while(strlen($DN_two_countS)>7) {$DN_two_countS = substr("$DN_two_countS", 0, -1);}
		$DN_three_countS =	sprintf("%7s", $DN_three_count); while(strlen($DN_three_countS)>7) {$DN_three_countS = substr("$DN_three_countS", 0, -1);}
		$DN_threeT_countS =	sprintf("%7s", $DN_threeT_count); while(strlen($DN_threeT_countS)>7) {$DN_threeT_countS = substr("$DN_threeT_countS", 0, -1);}

		$DN_count_tot =	($DN_count + $DN_count_tot);
		$DN_one_count_tot =	($DN_one_count + $DN_one_count_tot);
		$DN_two_count_tot =	($DN_two_count + $DN_two_count_tot);
		$DN_three_count_tot =	($DN_three_count + $DN_three_count_tot);
		$DN_threeT_count_tot =	($DN_threeT_count + $DN_threeT_count_tot);

		########## END DNC (DNC flag) ##########
		########################################################


		########################################################
		########## BEGIN CONV DNC RATIO (DNC flag out of total leads percentage) ##########

		$DR_count=0; $DR_one_count=0; $DR_two_count=0; $DR_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($dnc_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$DR_results = mysqli_num_rows($rslt);
		if ($DR_results > 0)
			{$row=mysqli_fetch_row($rslt); $DR_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($dnc_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$DR_one_results = mysqli_num_rows($rslt);
		if ($DR_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $DR_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($dnc_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$DR_two_results = mysqli_num_rows($rslt);
		if ($DR_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $DR_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($dnc_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$DR_three_results = mysqli_num_rows($rslt);
		if ($DR_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $DR_three_count = $row[0];}
		$DR_threeT_count = ($DR_one_count + $DR_two_count + $DR_three_count);

		$DR_count_pct=0;
		$DR_one_count_pct=0;
		$DR_two_count_pct=0;
		$DR_three_count_pct=0;
		$DR_threeT_count_pct=0;
		if ( ($DR_count > 0) and ($LISTIDcalls[$i] > 0) )   {$DR_count_pct = (($DR_count / $LISTIDcalls[$i]) * 100);}
		if ( ($DR_one_count > 0) and ($LISTIDcalls[$i] > 0) )   {$DR_one_count_pct = (($DR_one_count / $LISTIDcalls[$i]) * 100);}
		if ( ($DR_two_count > 0) and ($LISTIDcalls[$i] > 0) )   {$DR_two_count_pct = (($DR_two_count / $LISTIDcalls[$i]) * 100);}
		if ( ($DR_three_count > 0) and ($LISTIDcalls[$i] > 0) )   {$DR_three_count_pct = (($DR_three_count / $LISTIDcalls[$i]) * 100);}
		if ( ($DR_threeT_count > 0) and ($LISTIDcalls[$i] > 0) )   {$DR_threeT_count_pct = (($DR_threeT_count / $LISTIDcalls[$i]) * 100);}

		$DR_countS =	sprintf("%6.2f", $DR_count_pct); while(strlen($DR_countS)>7) {$DR_countS = substr("$DR_countS", 0, -1);}
		$DR_one_countS =	sprintf("%6.2f", $DR_one_count_pct); while(strlen($DR_one_countS)>7) {$DR_one_countS = substr("$DR_one_countS", 0, -1);}
		$DR_two_countS =	sprintf("%6.2f", $DR_two_count_pct); while(strlen($DR_two_countS)>7) {$DR_two_countS = substr("$DR_two_countS", 0, -1);}
		$DR_three_countS =	sprintf("%6.2f", $DR_three_count_pct); while(strlen($DR_three_countS)>7) {$DR_three_countS = substr("$DR_three_countS", 0, -1);}
		$DR_threeT_countS =	sprintf("%6.2f", $DR_threeT_count_pct); while(strlen($DR_threeT_countS)>7) {$DR_threeT_countS = substr("$DR_threeT_countS", 0, -1);}

		$DR_count_tot =	($DR_count + $DR_count_tot);
		$DR_one_count_tot =	($DR_one_count + $DR_one_count_tot);
		$DR_two_count_tot =	($DR_two_count + $DR_two_count_tot);
		$DR_three_count_tot =	($DR_three_count + $DR_three_count_tot);
		$DR_threeT_count_tot =	($DR_threeT_count + $DR_threeT_count_tot);

		########## END   CONV DNC RATIO (DNC flag out of total leads percentage) ##########
		########################################################


		########################################################
		########## BEGIN CUSTOMER CONTACT (Customer Contact flag) ##########

		$CC_count=0; $CC_one_count=0; $CC_two_count=0; $CC_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($customer_contact_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$CC_results = mysqli_num_rows($rslt);
		if ($CC_results > 0)
			{$row=mysqli_fetch_row($rslt); $CC_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($customer_contact_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$CC_one_results = mysqli_num_rows($rslt);
		if ($CC_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $CC_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($customer_contact_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$CC_two_results = mysqli_num_rows($rslt);
		if ($CC_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $CC_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($customer_contact_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$CC_three_results = mysqli_num_rows($rslt);
		if ($CC_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $CC_three_count = $row[0];}
		$CC_threeT_count = ($CC_one_count + $CC_two_count + $CC_three_count);

		$CC_countS =	sprintf("%7s", $CC_count); while(strlen($CC_countS)>7) {$CC_countS = substr("$CC_countS", 0, -1);}
		$CC_one_countS =	sprintf("%7s", $CC_one_count); while(strlen($CC_one_countS)>7) {$CC_one_countS = substr("$CC_one_countS", 0, -1);}
		$CC_two_countS =	sprintf("%7s", $CC_two_count); while(strlen($CC_two_countS)>7) {$CC_two_countS = substr("$CC_two_countS", 0, -1);}
		$CC_three_countS =	sprintf("%7s", $CC_three_count); while(strlen($CC_three_countS)>7) {$CC_three_countS = substr("$CC_three_countS", 0, -1);}
		$CC_threeT_countS =	sprintf("%7s", $CC_threeT_count); while(strlen($CC_threeT_countS)>7) {$CC_threeT_countS = substr("$CC_threeT_countS", 0, -1);}

		$CC_count_tot =	($CC_count + $CC_count_tot);
		$CC_one_count_tot =	($CC_one_count + $CC_one_count_tot);
		$CC_two_count_tot =	($CC_two_count + $CC_two_count_tot);
		$CC_three_count_tot =	($CC_three_count + $CC_three_count_tot);
		$CC_threeT_count_tot =	($CC_threeT_count + $CC_threeT_count_tot);

		########## END CUSTOMER CONTACT (Customer Contact flag) ##########
		########################################################


		########################################################
		########## BEGIN CUSTOMER CONTACT RATIO (Customer Contact flag out of total leads percentage) ##########

		$CR_count=0; $CR_one_count=0; $CR_two_count=0; $CR_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($customer_contact_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$CR_results = mysqli_num_rows($rslt);
		if ($CR_results > 0)
			{$row=mysqli_fetch_row($rslt); $CR_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($customer_contact_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$CR_one_results = mysqli_num_rows($rslt);
		if ($CR_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $CR_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($customer_contact_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$CR_two_results = mysqli_num_rows($rslt);
		if ($CR_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $CR_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($customer_contact_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$CR_three_results = mysqli_num_rows($rslt);
		if ($CR_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $CR_three_count = $row[0];}
		$CR_threeT_count = ($CR_one_count + $CR_two_count + $CR_three_count);

		$CR_count_pct=0;
		$CR_one_count_pct=0;
		$CR_two_count_pct=0;
		$CR_three_count_pct=0;
		$CR_threeT_count_pct=0;
		if ( ($CR_count > 0) and ($LISTIDcalls[$i] > 0) )   {$CR_count_pct = (($CR_count / $LISTIDcalls[$i]) * 100);}
		if ( ($CR_one_count > 0) and ($LISTIDcalls[$i] > 0) )   {$CR_one_count_pct = (($CR_one_count / $LISTIDcalls[$i]) * 100);}
		if ( ($CR_two_count > 0) and ($LISTIDcalls[$i] > 0) )   {$CR_two_count_pct = (($CR_two_count / $LISTIDcalls[$i]) * 100);}
		if ( ($CR_three_count > 0) and ($LISTIDcalls[$i] > 0) )   {$CR_three_count_pct = (($CR_three_count / $LISTIDcalls[$i]) * 100);}
		if ( ($CR_threeT_count > 0) and ($LISTIDcalls[$i] > 0) )   {$CR_threeT_count_pct = (($CR_threeT_count / $LISTIDcalls[$i]) * 100);}

		$CR_countS =	sprintf("%6.2f", $CR_count_pct); while(strlen($CR_countS)>7) {$CR_countS = substr("$CR_countS", 0, -1);}
		$CR_one_countS =	sprintf("%6.2f", $CR_one_count_pct); while(strlen($CR_one_countS)>7) {$CR_one_countS = substr("$CR_one_countS", 0, -1);}
		$CR_two_countS =	sprintf("%6.2f", $CR_two_count_pct); while(strlen($CR_two_countS)>7) {$CR_two_countS = substr("$CR_two_countS", 0, -1);}
		$CR_three_countS =	sprintf("%6.2f", $CR_three_count_pct); while(strlen($CR_three_countS)>7) {$CR_three_countS = substr("$CR_three_countS", 0, -1);}
		$CR_threeT_countS =	sprintf("%6.2f", $CR_threeT_count_pct); while(strlen($CR_threeT_countS)>7) {$CR_threeT_countS = substr("$CR_threeT_countS", 0, -1);}

		$CR_count_tot =	($CR_count + $CR_count_tot);
		$CR_one_count_tot =	($CR_one_count + $CR_one_count_tot);
		$CR_two_count_tot =	($CR_two_count + $CR_two_count_tot);
		$CR_three_count_tot =	($CR_three_count + $CR_three_count_tot);
		$CR_threeT_count_tot =	($CR_threeT_count + $CR_threeT_count_tot);

		########## END   CUSTOMER CONTACT RATIO (Customer Contact flag out of total leads percentage) ##########
		########################################################


		########################################################
		########## BEGIN UNWORKABLE (Unworkable flag) ##########

		$UW_count=0; $UW_one_count=0; $UW_two_count=0; $UW_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($unworkable_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$UW_results = mysqli_num_rows($rslt);
		if ($UW_results > 0)
			{$row=mysqli_fetch_row($rslt); $UW_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($unworkable_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$UW_one_results = mysqli_num_rows($rslt);
		if ($UW_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $UW_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($unworkable_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$UW_two_results = mysqli_num_rows($rslt);
		if ($UW_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $UW_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($unworkable_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$UW_three_results = mysqli_num_rows($rslt);
		if ($UW_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $UW_three_count = $row[0];}
		$UW_threeT_count = ($UW_one_count + $UW_two_count + $UW_three_count);

		$UW_countS =	sprintf("%7s", $UW_count); while(strlen($UW_countS)>7) {$UW_countS = substr("$UW_countS", 0, -1);}
		$UW_one_countS =	sprintf("%7s", $UW_one_count); while(strlen($UW_one_countS)>7) {$UW_one_countS = substr("$UW_one_countS", 0, -1);}
		$UW_two_countS =	sprintf("%7s", $UW_two_count); while(strlen($UW_two_countS)>7) {$UW_two_countS = substr("$UW_two_countS", 0, -1);}
		$UW_three_countS =	sprintf("%7s", $UW_three_count); while(strlen($UW_three_countS)>7) {$UW_three_countS = substr("$UW_three_countS", 0, -1);}
		$UW_threeT_countS =	sprintf("%7s", $UW_threeT_count); while(strlen($UW_threeT_countS)>7) {$UW_threeT_countS = substr("$UW_threeT_countS", 0, -1);}

		$UW_count_tot =	($UW_count + $UW_count_tot);
		$UW_one_count_tot =	($UW_one_count + $UW_one_count_tot);
		$UW_two_count_tot =	($UW_two_count + $UW_two_count_tot);
		$UW_three_count_tot =	($UW_three_count + $UW_three_count_tot);
		$UW_threeT_count_tot =	($UW_threeT_count + $UW_threeT_count_tot);

		########## END UNWORKABLE (Unworkable flag) ##########
		########################################################


		########################################################
		########## BEGIN UNWORKABLE RATIO (Unworkable flag out of total leads percentage) ##########

		$UR_count=0; $UR_one_count=0; $UR_two_count=0; $UR_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($unworkable_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$UR_results = mysqli_num_rows($rslt);
		if ($UR_results > 0)
			{$row=mysqli_fetch_row($rslt); $UR_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($unworkable_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$UR_one_results = mysqli_num_rows($rslt);
		if ($UR_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $UR_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($unworkable_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$UR_two_results = mysqli_num_rows($rslt);
		if ($UR_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $UR_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($unworkable_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$UR_three_results = mysqli_num_rows($rslt);
		if ($UR_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $UR_three_count = $row[0];}
		$UR_threeT_count = ($UR_one_count + $UR_two_count + $UR_three_count);

		$UR_count_pct=0;
		$UR_one_count_pct=0;
		$UR_two_count_pct=0;
		$UR_three_count_pct=0;
		$UR_threeT_count_pct=0;
		if ( ($UR_count > 0) and ($LISTIDcalls[$i] > 0) )   {$UR_count_pct = (($UR_count / $LISTIDcalls[$i]) * 100);}
		if ( ($UR_one_count > 0) and ($LISTIDcalls[$i] > 0) )   {$UR_one_count_pct = (($UR_one_count / $LISTIDcalls[$i]) * 100);}
		if ( ($UR_two_count > 0) and ($LISTIDcalls[$i] > 0) )   {$UR_two_count_pct = (($UR_two_count / $LISTIDcalls[$i]) * 100);}
		if ( ($UR_three_count > 0) and ($LISTIDcalls[$i] > 0) )   {$UR_three_count_pct = (($UR_three_count / $LISTIDcalls[$i]) * 100);}
		if ( ($UR_threeT_count > 0) and ($LISTIDcalls[$i] > 0) )   {$UR_threeT_count_pct = (($UR_threeT_count / $LISTIDcalls[$i]) * 100);}

		$UR_countS =	sprintf("%6.2f", $UR_count_pct); while(strlen($UR_countS)>7) {$UR_countS = substr("$UR_countS", 0, -1);}
		$UR_one_countS =	sprintf("%6.2f", $UR_one_count_pct); while(strlen($UR_one_countS)>7) {$UR_one_countS = substr("$UR_one_countS", 0, -1);}
		$UR_two_countS =	sprintf("%6.2f", $UR_two_count_pct); while(strlen($UR_two_countS)>7) {$UR_two_countS = substr("$UR_two_countS", 0, -1);}
		$UR_three_countS =	sprintf("%6.2f", $UR_three_count_pct); while(strlen($UR_three_countS)>7) {$UR_three_countS = substr("$UR_three_countS", 0, -1);}
		$UR_threeT_countS =	sprintf("%6.2f", $UR_threeT_count_pct); while(strlen($UR_threeT_countS)>7) {$UR_threeT_countS = substr("$UR_threeT_countS", 0, -1);}

		$UR_count_tot =	($UR_count + $UR_count_tot);
		$UR_one_count_tot =	($UR_one_count + $UR_one_count_tot);
		$UR_two_count_tot =	($UR_two_count + $UR_two_count_tot);
		$UR_three_count_tot =	($UR_three_count + $UR_three_count_tot);
		$UR_threeT_count_tot =	($UR_threeT_count + $UR_threeT_count_tot);

		########## END   UNWORKABLE RATIO (Unworkable flag out of total leads percentage) ##########
		########################################################


		########################################################
		########## BEGIN SCHEDULED CALLBACK (Scheduled Callback flag) ##########

		$BA_count=0; $BA_one_count=0; $BA_two_count=0; $BA_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($scheduled_callback_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$BA_results = mysqli_num_rows($rslt);
		if ($BA_results > 0)
			{$row=mysqli_fetch_row($rslt); $BA_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($scheduled_callback_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$BA_one_results = mysqli_num_rows($rslt);
		if ($BA_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $BA_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($scheduled_callback_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$BA_two_results = mysqli_num_rows($rslt);
		if ($BA_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $BA_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($scheduled_callback_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$BA_three_results = mysqli_num_rows($rslt);
		if ($BA_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $BA_three_count = $row[0];}
		$BA_threeT_count = ($BA_one_count + $BA_two_count + $BA_three_count);

		$BA_countS =	sprintf("%7s", $BA_count); while(strlen($BA_countS)>7) {$BA_countS = substr("$BA_countS", 0, -1);}
		$BA_one_countS =	sprintf("%7s", $BA_one_count); while(strlen($BA_one_countS)>7) {$BA_one_countS = substr("$BA_one_countS", 0, -1);}
		$BA_two_countS =	sprintf("%7s", $BA_two_count); while(strlen($BA_two_countS)>7) {$BA_two_countS = substr("$BA_two_countS", 0, -1);}
		$BA_three_countS =	sprintf("%7s", $BA_three_count); while(strlen($BA_three_countS)>7) {$BA_three_countS = substr("$BA_three_countS", 0, -1);}
		$BA_threeT_countS =	sprintf("%7s", $BA_threeT_count); while(strlen($BA_threeT_countS)>7) {$BA_threeT_countS = substr("$BA_threeT_countS", 0, -1);}

		$BA_count_tot =	($BA_count + $BA_count_tot);
		$BA_one_count_tot =	($BA_one_count + $BA_one_count_tot);
		$BA_two_count_tot =	($BA_two_count + $BA_two_count_tot);
		$BA_three_count_tot =	($BA_three_count + $BA_three_count_tot);
		$BA_threeT_count_tot =	($BA_threeT_count + $BA_threeT_count_tot);

		########## END SCHEDULED CALLBACK (Scheduled Callback flag) ##########
		########################################################


		########################################################
		########## BEGIN SCHEDULED CALLBACK RATIO (Scheduled Callback flag out of total leads percentage) ##########

		$BR_count=0; $BR_one_count=0; $BR_two_count=0; $BR_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($scheduled_callback_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$BR_results = mysqli_num_rows($rslt);
		if ($BR_results > 0)
			{$row=mysqli_fetch_row($rslt); $BR_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($scheduled_callback_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$BR_one_results = mysqli_num_rows($rslt);
		if ($BR_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $BR_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($scheduled_callback_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$BR_two_results = mysqli_num_rows($rslt);
		if ($BR_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $BR_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($scheduled_callback_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$BR_three_results = mysqli_num_rows($rslt);
		if ($BR_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $BR_three_count = $row[0];}
		$BR_threeT_count = ($BR_one_count + $BR_two_count + $BR_three_count);

		$BR_count_pct=0;
		$BR_one_count_pct=0;
		$BR_two_count_pct=0;
		$BR_three_count_pct=0;
		$BR_threeT_count_pct=0;
		if ( ($BR_count > 0) and ($LISTIDcalls[$i] > 0) )   {$BR_count_pct = (($BR_count / $LISTIDcalls[$i]) * 100);}
		if ( ($BR_one_count > 0) and ($LISTIDcalls[$i] > 0) )   {$BR_one_count_pct = (($BR_one_count / $LISTIDcalls[$i]) * 100);}
		if ( ($BR_two_count > 0) and ($LISTIDcalls[$i] > 0) )   {$BR_two_count_pct = (($BR_two_count / $LISTIDcalls[$i]) * 100);}
		if ( ($BR_three_count > 0) and ($LISTIDcalls[$i] > 0) )   {$BR_three_count_pct = (($BR_three_count / $LISTIDcalls[$i]) * 100);}
		if ( ($BR_threeT_count > 0) and ($LISTIDcalls[$i] > 0) )   {$BR_threeT_count_pct = (($BR_threeT_count / $LISTIDcalls[$i]) * 100);}

		$BR_countS =	sprintf("%6.2f", $BR_count_pct); while(strlen($BR_countS)>7) {$BR_countS = substr("$BR_countS", 0, -1);}
		$BR_one_countS =	sprintf("%6.2f", $BR_one_count_pct); while(strlen($BR_one_countS)>7) {$BR_one_countS = substr("$BR_one_countS", 0, -1);}
		$BR_two_countS =	sprintf("%6.2f", $BR_two_count_pct); while(strlen($BR_two_countS)>7) {$BR_two_countS = substr("$BR_two_countS", 0, -1);}
		$BR_three_countS =	sprintf("%6.2f", $BR_three_count_pct); while(strlen($BR_three_countS)>7) {$BR_three_countS = substr("$BR_three_countS", 0, -1);}
		$BR_threeT_countS =	sprintf("%6.2f", $BR_threeT_count_pct); while(strlen($BR_threeT_countS)>7) {$BR_threeT_countS = substr("$BR_threeT_countS", 0, -1);}

		$BR_count_tot =	($BR_count + $BR_count_tot);
		$BR_one_count_tot =	($BR_one_count + $BR_one_count_tot);
		$BR_two_count_tot =	($BR_two_count + $BR_two_count_tot);
		$BR_three_count_tot =	($BR_three_count + $BR_three_count_tot);
		$BR_threeT_count_tot =	($BR_threeT_count + $BR_threeT_count_tot);

		########## END   SCHEDULED CALLBACK RATIO (Scheduled Callback flag out of total leads percentage) ##########
		########################################################


		########################################################
		########## BEGIN COMPLETED (Completed flag) ##########

		$MP_count=0; $MP_one_count=0; $MP_two_count=0; $MP_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($completed_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$MP_results = mysqli_num_rows($rslt);
		if ($MP_results > 0)
			{$row=mysqli_fetch_row($rslt); $MP_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($completed_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$MP_one_results = mysqli_num_rows($rslt);
		if ($MP_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $MP_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($completed_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$MP_two_results = mysqli_num_rows($rslt);
		if ($MP_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $MP_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($completed_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$MP_three_results = mysqli_num_rows($rslt);
		if ($MP_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $MP_three_count = $row[0];}
		$MP_threeT_count = ($MP_one_count + $MP_two_count + $MP_three_count);

		$MP_countS =	sprintf("%7s", $MP_count); while(strlen($MP_countS)>7) {$MP_countS = substr("$MP_countS", 0, -1);}
		$MP_one_countS =	sprintf("%7s", $MP_one_count); while(strlen($MP_one_countS)>7) {$MP_one_countS = substr("$MP_one_countS", 0, -1);}
		$MP_two_countS =	sprintf("%7s", $MP_two_count); while(strlen($MP_two_countS)>7) {$MP_two_countS = substr("$MP_two_countS", 0, -1);}
		$MP_three_countS =	sprintf("%7s", $MP_three_count); while(strlen($MP_three_countS)>7) {$MP_three_countS = substr("$MP_three_countS", 0, -1);}
		$MP_threeT_countS =	sprintf("%7s", $MP_threeT_count); while(strlen($MP_threeT_countS)>7) {$MP_threeT_countS = substr("$MP_threeT_countS", 0, -1);}

		$MP_count_tot =	($MP_count + $MP_count_tot);
		$MP_one_count_tot =	($MP_one_count + $MP_one_count_tot);
		$MP_two_count_tot =	($MP_two_count + $MP_two_count_tot);
		$MP_three_count_tot =	($MP_three_count + $MP_three_count_tot);
		$MP_threeT_count_tot =	($MP_threeT_count + $MP_threeT_count_tot);

		########## END COMPLETED (Completed Callback flag) ##########
		########################################################


		########################################################
		########## BEGIN COMPLETED RATIO (Completed flag out of total leads percentage) ##########

		$MR_count=0; $MR_one_count=0; $MR_two_count=0; $MR_three_count=0;

		$stmt="select count(*) from vicidial_list where status IN($completed_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$MR_results = mysqli_num_rows($rslt);
		if ($MR_results > 0)
			{$row=mysqli_fetch_row($rslt); $MR_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=1 and status IN($completed_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$MR_one_results = mysqli_num_rows($rslt);
		if ($MR_one_results > 0)
			{$row=mysqli_fetch_row($rslt); $MR_one_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=2 and status IN($completed_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$MR_two_results = mysqli_num_rows($rslt);
		if ($MR_two_results > 0)
			{$row=mysqli_fetch_row($rslt); $MR_two_count = $row[0];}
		$stmt="select count(*) from vicidial_log where called_count=3 and status IN($completed_statuses) and list_id='$LISTIDlists[$i]';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {$MAIN.="$stmt\n";}
		$MR_three_results = mysqli_num_rows($rslt);
		if ($MR_three_results > 0)
			{$row=mysqli_fetch_row($rslt); $MR_three_count = $row[0];}
		$MR_threeT_count = ($MR_one_count + $MR_two_count + $MR_three_count);

		$MR_count_pct=0;
		$MR_one_count_pct=0;
		$MR_two_count_pct=0;
		$MR_three_count_pct=0;
		$MR_threeT_count_pct=0;
		if ( ($MR_count > 0) and ($LISTIDcalls[$i] > 0) )   {$MR_count_pct = (($MR_count / $LISTIDcalls[$i]) * 100);}
		if ( ($MR_one_count > 0) and ($LISTIDcalls[$i] > 0) )   {$MR_one_count_pct = (($MR_one_count / $LISTIDcalls[$i]) * 100);}
		if ( ($MR_two_count > 0) and ($LISTIDcalls[$i] > 0) )   {$MR_two_count_pct = (($MR_two_count / $LISTIDcalls[$i]) * 100);}
		if ( ($MR_three_count > 0) and ($LISTIDcalls[$i] > 0) )   {$MR_three_count_pct = (($MR_three_count / $LISTIDcalls[$i]) * 100);}
		if ( ($MR_threeT_count > 0) and ($LISTIDcalls[$i] > 0) )   {$MR_threeT_count_pct = (($MR_threeT_count / $LISTIDcalls[$i]) * 100);}

		$MR_countS =	sprintf("%6.2f", $MR_count_pct); while(strlen($MR_countS)>7) {$MR_countS = substr("$MR_countS", 0, -1);}
		$MR_one_countS =	sprintf("%6.2f", $MR_one_count_pct); while(strlen($MR_one_countS)>7) {$MR_one_countS = substr("$MR_one_countS", 0, -1);}
		$MR_two_countS =	sprintf("%6.2f", $MR_two_count_pct); while(strlen($MR_two_countS)>7) {$MR_two_countS = substr("$MR_two_countS", 0, -1);}
		$MR_three_countS =	sprintf("%6.2f", $MR_three_count_pct); while(strlen($MR_three_countS)>7) {$MR_three_countS = substr("$MR_three_countS", 0, -1);}
		$MR_threeT_countS =	sprintf("%6.2f", $MR_threeT_count_pct); while(strlen($MR_threeT_countS)>7) {$MR_threeT_countS = substr("$MR_threeT_countS", 0, -1);}

		$MR_count_tot =	($MR_count + $MR_count_tot);
		$MR_one_count_tot =	($MR_one_count + $MR_one_count_tot);
		$MR_two_count_tot =	($MR_two_count + $MR_two_count_tot);
		$MR_three_count_tot =	($MR_three_count + $MR_three_count_tot);
		$MR_threeT_count_tot =	($MR_threeT_count + $MR_threeT_count_tot);

		########## END   COMPLETED RATIO (Completed flag out of total leads percentage) ##########
		########################################################



		$OUToutput .= "| $LISTIDentry_dateS | $LISTIDname | $LISTIDcampaignS | $LISTIDcount | $LISTIDlist_active[$i] ";
		$OUToutput .= "| $HA_one_countS | $HA_two_countS | $HA_three_countS | $HA_threeT_countS | $HA_countS ";
		$OUToutput .= "| $HR_one_countS% | $HR_two_countS% | $HR_three_countS% | $HR_threeT_countS% | $HR_countS% ";
		$OUToutput .= "| $SA_one_countS | $SA_two_countS | $SA_three_countS | $SA_threeT_countS | $SA_countS ";
		$OUToutput .= "| $SR_one_countS% | $SR_two_countS% | $SR_three_countS% | $SR_threeT_countS% | $SR_countS% ";
		$OUToutput .= "| $DN_one_countS | $DN_two_countS | $DN_three_countS | $DN_threeT_countS | $DN_countS ";
		$OUToutput .= "| $DR_one_countS% | $DR_two_countS% | $DR_three_countS% | $DR_threeT_countS% | $DR_countS% ";
		$OUToutput .= "| $CC_one_countS | $CC_two_countS | $CC_three_countS | $CC_threeT_countS | $CC_countS ";
		$OUToutput .= "| $CR_one_countS% | $CR_two_countS% | $CR_three_countS% | $CR_threeT_countS% | $CR_countS% ";
		$OUToutput .= "| $UW_one_countS | $UW_two_countS | $UW_three_countS | $UW_threeT_countS | $UW_countS ";
		$OUToutput .= "| $UR_one_countS% | $UR_two_countS% | $UR_three_countS% | $UR_threeT_countS% | $UR_countS% ";
		$OUToutput .= "| $BA_one_countS | $BA_two_countS | $BA_three_countS | $BA_threeT_countS | $BA_countS ";
		$OUToutput .= "| $BR_one_countS% | $BR_two_countS% | $BR_three_countS% | $BR_threeT_countS% | $BR_countS% ";
		$OUToutput .= "| $MP_one_countS | $MP_two_countS | $MP_three_countS | $MP_threeT_countS | $MP_countS ";
		$OUToutput .= "| $MR_one_countS% | $MR_two_countS% | $MR_three_countS% | $MR_threeT_countS% | $MR_countS% ";
		$OUToutput .= "|\n";

		$CSV_text1.="\"$LISTIDentry_dateS\",\"$LISTIDname\",\"$LISTIDcampaignS\",\"$LISTIDcount\",\"$LISTIDlist_active[$i]\"";
		$CSV_text1.=",\"$HA_one_countS\",\"$HA_two_countS\",\"$HA_three_countS\",\"$HA_threeT_countS\",\"$HA_countS\"";
		$CSV_text1.=",\"$HR_one_countS%\",\"$HR_two_countS%\",\"$HR_three_countS%\",\"$HR_threeT_countS%\",\"$HR_countS%\"";
		$CSV_text1.=",\"$SA_one_countS\",\"$SA_two_countS\",\"$SA_three_countS\",\"$SA_threeT_countS\",\"$SA_countS\"";
		$CSV_text1.=",\"$SR_one_countS%\",\"$SR_two_countS%\",\"$SR_three_countS%\",\"$SR_threeT_countS%\",\"$SR_countS%\"";
		$CSV_text1.=",\"$DN_one_countS\",\"$DN_two_countS\",\"$DN_three_countS\",\"$DN_threeT_countS\",\"$DN_countS\"";
		$CSV_text1.=",\"$DR_one_countS%\",\"$DR_two_countS%\",\"$DR_three_countS%\",\"$DR_threeT_countS%\",\"$DR_countS%\"";
		$CSV_text1.=",\"$CC_one_countS\",\"$CC_two_countS\",\"$CC_three_countS\",\"$CC_threeT_countS\",\"$CC_countS\"";
		$CSV_text1.=",\"$CR_one_countS%\",\"$CR_two_countS%\",\"$CR_three_countS%\",\"$CR_threeT_countS%\",\"$CR_countS%\"";
		$CSV_text1.=",\"$UW_one_countS\",\"$UW_two_countS\",\"$UW_three_countS\",\"$UW_threeT_countS\",\"$UW_countS\"";
		$CSV_text1.=",\"$UR_one_countS%\",\"$UR_two_countS%\",\"$UR_three_countS%\",\"$UR_threeT_countS%\",\"$UR_countS%\"";
		$CSV_text1.=",\"$BA_one_countS\",\"$BA_two_countS\",\"$BA_three_countS\",\"$BA_threeT_countS\",\"$BA_countS\"";
		$CSV_text1.=",\"$BR_one_countS%\",\"$BR_two_countS%\",\"$BR_three_countS%\",\"$BR_threeT_countS%\",\"$BR_countS%\"";
		$CSV_text1.=",\"$MP_one_countS\",\"$MP_two_countS\",\"$MP_three_countS\",\"$MP_threeT_countS\",\"$MP_countS\"";
		$CSV_text1.=",\"$MR_one_countS%\",\"$MR_two_countS%\",\"$MR_three_countS%\",\"$MR_threeT_countS%\",\"$MR_countS%\"";
		$CSV_text1.="\n";

		$i++;
		}

	$HA_count_totS =	sprintf("%7s", $HA_count_tot); while(strlen($HA_count_totS)>7) {$HA_count_totS = substr("$HA_count_totS", 0, -1);}
	$HA_one_count_totS =	sprintf("%7s", $HA_one_count_tot); while(strlen($HA_one_count_totS)>7) {$HA_one_count_totS = substr("$HA_one_count_totS", 0, -1);}
	$HA_two_count_totS =	sprintf("%7s", $HA_two_count_tot); while(strlen($HA_two_count_totS)>7) {$HA_two_count_totS = substr("$HA_two_count_totS", 0, -1);}
	$HA_three_count_totS =	sprintf("%7s", $HA_three_count_tot); while(strlen($HA_three_count_totS)>7) {$HA_three_count_totS = substr("$HA_three_count_totS", 0, -1);}
	$HA_threeT_count_totS =	sprintf("%7s", $HA_threeT_count_tot); while(strlen($HA_threeT_count_totS)>7) {$HA_threeT_count_totS = substr("$HA_threeT_count_totS", 0, -1);}

	$SA_count_totS =	sprintf("%7s", $SA_count_tot); while(strlen($SA_count_totS)>7) {$SA_count_totS = substr("$SA_count_totS", 0, -1);}
	$SA_one_count_totS =	sprintf("%7s", $SA_one_count_tot); while(strlen($SA_one_count_totS)>7) {$SA_one_count_totS = substr("$SA_one_count_totS", 0, -1);}
	$SA_two_count_totS =	sprintf("%7s", $SA_two_count_tot); while(strlen($SA_two_count_totS)>7) {$SA_two_count_totS = substr("$SA_two_count_totS", 0, -1);}
	$SA_three_count_totS =	sprintf("%7s", $SA_three_count_tot); while(strlen($SA_three_count_totS)>7) {$SA_three_count_totS = substr("$SA_three_count_totS", 0, -1);}
	$SA_threeT_count_totS =	sprintf("%7s", $SA_threeT_count_tot); while(strlen($SA_threeT_count_totS)>7) {$SA_threeT_count_totS = substr("$SA_threeT_count_totS", 0, -1);}

	$DN_count_totS =	sprintf("%7s", $DN_count_tot); while(strlen($DN_count_totS)>7) {$DN_count_totS = substr("$DN_count_totS", 0, -1);}
	$DN_one_count_totS =	sprintf("%7s", $DN_one_count_tot); while(strlen($DN_one_count_totS)>7) {$DN_one_count_totS = substr("$DN_one_count_totS", 0, -1);}
	$DN_two_count_totS =	sprintf("%7s", $DN_two_count_tot); while(strlen($DN_two_count_totS)>7) {$DN_two_count_totS = substr("$DN_two_count_totS", 0, -1);}
	$DN_three_count_totS =	sprintf("%7s", $DN_three_count_tot); while(strlen($DN_three_count_totS)>7) {$DN_three_count_totS = substr("$DN_three_count_totS", 0, -1);}
	$DN_threeT_count_totS =	sprintf("%7s", $DN_threeT_count_tot); while(strlen($DN_threeT_count_totS)>7) {$DN_threeT_count_totS = substr("$DN_threeT_count_totS", 0, -1);}

	$CC_count_totS =	sprintf("%7s", $CC_count_tot); while(strlen($CC_count_totS)>7) {$CC_count_totS = substr("$CC_count_totS", 0, -1);}
	$CC_one_count_totS =	sprintf("%7s", $CC_one_count_tot); while(strlen($CC_one_count_totS)>7) {$CC_one_count_totS = substr("$CC_one_count_totS", 0, -1);}
	$CC_two_count_totS =	sprintf("%7s", $CC_two_count_tot); while(strlen($CC_two_count_totS)>7) {$CC_two_count_totS = substr("$CC_two_count_totS", 0, -1);}
	$CC_three_count_totS =	sprintf("%7s", $CC_three_count_tot); while(strlen($CC_three_count_totS)>7) {$CC_three_count_totS = substr("$CC_three_count_totS", 0, -1);}
	$CC_threeT_count_totS =	sprintf("%7s", $CC_threeT_count_tot); while(strlen($CC_threeT_count_totS)>7) {$CC_threeT_count_totS = substr("$CC_threeT_count_totS", 0, -1);}

	$UW_count_totS =	sprintf("%7s", $UW_count_tot); while(strlen($UW_count_totS)>7) {$UW_count_totS = substr("$UW_count_totS", 0, -1);}
	$UW_one_count_totS =	sprintf("%7s", $UW_one_count_tot); while(strlen($UW_one_count_totS)>7) {$UW_one_count_totS = substr("$UW_one_count_totS", 0, -1);}
	$UW_two_count_totS =	sprintf("%7s", $UW_two_count_tot); while(strlen($UW_two_count_totS)>7) {$UW_two_count_totS = substr("$UW_two_count_totS", 0, -1);}
	$UW_three_count_totS =	sprintf("%7s", $UW_three_count_tot); while(strlen($UW_three_count_totS)>7) {$UW_three_count_totS = substr("$UW_three_count_totS", 0, -1);}
	$UW_threeT_count_totS =	sprintf("%7s", $UW_threeT_count_tot); while(strlen($UW_threeT_count_totS)>7) {$UW_threeT_count_totS = substr("$UW_threeT_count_totS", 0, -1);}

	$BA_count_totS =	sprintf("%7s", $BA_count_tot); while(strlen($BA_count_totS)>7) {$BA_count_totS = substr("$BA_count_totS", 0, -1);}
	$BA_one_count_totS =	sprintf("%7s", $BA_one_count_tot); while(strlen($BA_one_count_totS)>7) {$BA_one_count_totS = substr("$BA_one_count_totS", 0, -1);}
	$BA_two_count_totS =	sprintf("%7s", $BA_two_count_tot); while(strlen($BA_two_count_totS)>7) {$BA_two_count_totS = substr("$BA_two_count_totS", 0, -1);}
	$BA_three_count_totS =	sprintf("%7s", $BA_three_count_tot); while(strlen($BA_three_count_totS)>7) {$BA_three_count_totS = substr("$BA_three_count_totS", 0, -1);}
	$BA_threeT_count_totS =	sprintf("%7s", $BA_threeT_count_tot); while(strlen($BA_threeT_count_totS)>7) {$BA_threeT_count_totS = substr("$BA_threeT_count_totS", 0, -1);}

	$MP_count_totS =	sprintf("%7s", $MP_count_tot); while(strlen($MP_count_totS)>7) {$MP_count_totS = substr("$MP_count_totS", 0, -1);}
	$MP_one_count_totS =	sprintf("%7s", $MP_one_count_tot); while(strlen($MP_one_count_totS)>7) {$MP_one_count_totS = substr("$MP_one_count_totS", 0, -1);}
	$MP_two_count_totS =	sprintf("%7s", $MP_two_count_tot); while(strlen($MP_two_count_totS)>7) {$MP_two_count_totS = substr("$MP_two_count_totS", 0, -1);}
	$MP_three_count_totS =	sprintf("%7s", $MP_three_count_tot); while(strlen($MP_three_count_totS)>7) {$MP_three_count_totS = substr("$MP_three_count_totS", 0, -1);}
	$MP_threeT_count_totS =	sprintf("%7s", $MP_threeT_count_tot); while(strlen($MP_threeT_count_totS)>7) {$MP_threeT_count_totS = substr("$MP_threeT_count_totS", 0, -1);}

	$HR_count_Tpc=0;
	$HR_one_count_Tpc=0;
	$HR_two_count_Tpc=0;
	$HR_three_count_Tpc=0;
	$HR_threeT_count_Tpc=0;
	if ( ($HR_count_tot > 0) and ($TOTALleads > 0) )   {$HR_count_Tpc = (($HR_count_tot / $TOTALleads) * 100);}
	if ( ($HR_one_count_tot > 0) and ($TOTALleads > 0) )   {$HR_one_count_Tpc = (($HR_one_count_tot / $TOTALleads) * 100);}
	if ( ($HR_two_count_tot > 0) and ($TOTALleads > 0) )   {$HR_two_count_Tpc = (($HR_two_count_tot / $TOTALleads) * 100);}
	if ( ($HR_three_count_tot > 0) and ($TOTALleads > 0) )   {$HR_three_count_Tpc = (($HR_three_count_tot / $TOTALleads) * 100);}
	if ( ($HR_threeT_count_tot > 0) and ($TOTALleads > 0) )   {$HR_threeT_count_Tpc = (($HR_threeT_count_tot / $TOTALleads) * 100);}

	$HR_count_totS =	sprintf("%6.2f", $HR_count_Tpc); while(strlen($HR_count_totS)>7) {$HR_count_totS = substr("$HR_count_totS", 0, -1);}
	$HR_one_count_totS =	sprintf("%6.2f", $HR_one_count_Tpc); while(strlen($HR_one_count_totS)>7) {$HR_one_count_totS = substr("$HR_one_count_totS", 0, -1);}
	$HR_two_count_totS =	sprintf("%6.2f", $HR_two_count_Tpc); while(strlen($HR_two_count_totS)>7) {$HR_two_count_totS = substr("$HR_two_count_totS", 0, -1);}
	$HR_three_count_totS =	sprintf("%6.2f", $HR_three_count_Tpc); while(strlen($HR_three_count_totS)>7) {$HR_three_count_totS = substr("$HR_three_count_totS", 0, -1);}
	$HR_threeT_count_totS =	sprintf("%6.2f", $HR_threeT_count_Tpc); while(strlen($HR_threeT_count_totS)>7) {$HR_threeT_count_totS = substr("$HR_threeT_count_totS", 0, -1);}

	$SR_count_Tpc=0;
	$SR_one_count_Tpc=0;
	$SR_two_count_Tpc=0;
	$SR_three_count_Tpc=0;
	$SR_threeT_count_Tpc=0;
	if ( ($SR_count_tot > 0) and ($TOTALleads > 0) )   {$SR_count_Tpc = (($SR_count_tot / $TOTALleads) * 100);}
	if ( ($SR_one_count_tot > 0) and ($TOTALleads > 0) )   {$SR_one_count_Tpc = (($SR_one_count_tot / $TOTALleads) * 100);}
	if ( ($SR_two_count_tot > 0) and ($TOTALleads > 0) )   {$SR_two_count_Tpc = (($SR_two_count_tot / $TOTALleads) * 100);}
	if ( ($SR_three_count_tot > 0) and ($TOTALleads > 0) )   {$SR_three_count_Tpc = (($SR_three_count_tot / $TOTALleads) * 100);}
	if ( ($SR_threeT_count_tot > 0) and ($TOTALleads > 0) )   {$SR_threeT_count_Tpc = (($SR_threeT_count_tot / $TOTALleads) * 100);}

	$SR_count_totS =	sprintf("%6.2f", $SR_count_Tpc); while(strlen($SR_count_totS)>7) {$SR_count_totS = substr("$SR_count_totS", 0, -1);}
	$SR_one_count_totS =	sprintf("%6.2f", $SR_one_count_Tpc); while(strlen($SR_one_count_totS)>7) {$SR_one_count_totS = substr("$SR_one_count_totS", 0, -1);}
	$SR_two_count_totS =	sprintf("%6.2f", $SR_two_count_Tpc); while(strlen($SR_two_count_totS)>7) {$SR_two_count_totS = substr("$SR_two_count_totS", 0, -1);}
	$SR_three_count_totS =	sprintf("%6.2f", $SR_three_count_Tpc); while(strlen($SR_three_count_totS)>7) {$SR_three_count_totS = substr("$SR_three_count_totS", 0, -1);}
	$SR_threeT_count_totS =	sprintf("%6.2f", $SR_threeT_count_Tpc); while(strlen($SR_threeT_count_totS)>7) {$SR_threeT_count_totS = substr("$SR_threeT_count_totS", 0, -1);}

	$DR_count_Tpc=0;
	$DR_one_count_Tpc=0;
	$DR_two_count_Tpc=0;
	$DR_three_count_Tpc=0;
	$DR_threeT_count_Tpc=0;
	if ( ($DR_count_tot > 0) and ($TOTALleads > 0) )   {$DR_count_Tpc = (($DR_count_tot / $TOTALleads) * 100);}
	if ( ($DR_one_count_tot > 0) and ($TOTALleads > 0) )   {$DR_one_count_Tpc = (($DR_one_count_tot / $TOTALleads) * 100);}
	if ( ($DR_two_count_tot > 0) and ($TOTALleads > 0) )   {$DR_two_count_Tpc = (($DR_two_count_tot / $TOTALleads) * 100);}
	if ( ($DR_three_count_tot > 0) and ($TOTALleads > 0) )   {$DR_three_count_Tpc = (($DR_three_count_tot / $TOTALleads) * 100);}
	if ( ($DR_threeT_count_tot > 0) and ($TOTALleads > 0) )   {$DR_threeT_count_Tpc = (($DR_threeT_count_tot / $TOTALleads) * 100);}

	$DR_count_totS =	sprintf("%6.2f", $DR_count_Tpc); while(strlen($DR_count_totS)>7) {$DR_count_totS = substr("$DR_count_totS", 0, -1);}
	$DR_one_count_totS =	sprintf("%6.2f", $DR_one_count_Tpc); while(strlen($DR_one_count_totS)>7) {$DR_one_count_totS = substr("$DR_one_count_totS", 0, -1);}
	$DR_two_count_totS =	sprintf("%6.2f", $DR_two_count_Tpc); while(strlen($DR_two_count_totS)>7) {$DR_two_count_totS = substr("$DR_two_count_totS", 0, -1);}
	$DR_three_count_totS =	sprintf("%6.2f", $DR_three_count_Tpc); while(strlen($DR_three_count_totS)>7) {$DR_three_count_totS = substr("$DR_three_count_totS", 0, -1);}
	$DR_threeT_count_totS =	sprintf("%6.2f", $DR_threeT_count_Tpc); while(strlen($DR_threeT_count_totS)>7) {$DR_threeT_count_totS = substr("$DR_threeT_count_totS", 0, -1);}

	$CR_count_Tpc=0;
	$CR_one_count_Tpc=0;
	$CR_two_count_Tpc=0;
	$CR_three_count_Tpc=0;
	$CR_threeT_count_Tpc=0;
	if ( ($CR_count_tot > 0) and ($TOTALleads > 0) )   {$CR_count_Tpc = (($CR_count_tot / $TOTALleads) * 100);}
	if ( ($CR_one_count_tot > 0) and ($TOTALleads > 0) )   {$CR_one_count_Tpc = (($CR_one_count_tot / $TOTALleads) * 100);}
	if ( ($CR_two_count_tot > 0) and ($TOTALleads > 0) )   {$CR_two_count_Tpc = (($CR_two_count_tot / $TOTALleads) * 100);}
	if ( ($CR_three_count_tot > 0) and ($TOTALleads > 0) )   {$CR_three_count_Tpc = (($CR_three_count_tot / $TOTALleads) * 100);}
	if ( ($CR_threeT_count_tot > 0) and ($TOTALleads > 0) )   {$CR_threeT_count_Tpc = (($CR_threeT_count_tot / $TOTALleads) * 100);}

	$CR_count_totS =	sprintf("%6.2f", $CR_count_Tpc); while(strlen($CR_count_totS)>7) {$CR_count_totS = substr("$CR_count_totS", 0, -1);}
	$CR_one_count_totS =	sprintf("%6.2f", $CR_one_count_Tpc); while(strlen($CR_one_count_totS)>7) {$CR_one_count_totS = substr("$CR_one_count_totS", 0, -1);}
	$CR_two_count_totS =	sprintf("%6.2f", $CR_two_count_Tpc); while(strlen($CR_two_count_totS)>7) {$CR_two_count_totS = substr("$CR_two_count_totS", 0, -1);}
	$CR_three_count_totS =	sprintf("%6.2f", $CR_three_count_Tpc); while(strlen($CR_three_count_totS)>7) {$CR_three_count_totS = substr("$CR_three_count_totS", 0, -1);}
	$CR_threeT_count_totS =	sprintf("%6.2f", $CR_threeT_count_Tpc); while(strlen($CR_threeT_count_totS)>7) {$CR_threeT_count_totS = substr("$CR_threeT_count_totS", 0, -1);}

	$UR_count_Tpc=0;
	$UR_one_count_Tpc=0;
	$UR_two_count_Tpc=0;
	$UR_three_count_Tpc=0;
	$UR_threeT_count_Tpc=0;
	if ( ($UR_count_tot > 0) and ($TOTALleads > 0) )   {$UR_count_Tpc = (($UR_count_tot / $TOTALleads) * 100);}
	if ( ($UR_one_count_tot > 0) and ($TOTALleads > 0) )   {$UR_one_count_Tpc = (($UR_one_count_tot / $TOTALleads) * 100);}
	if ( ($UR_two_count_tot > 0) and ($TOTALleads > 0) )   {$UR_two_count_Tpc = (($UR_two_count_tot / $TOTALleads) * 100);}
	if ( ($UR_three_count_tot > 0) and ($TOTALleads > 0) )   {$UR_three_count_Tpc = (($UR_three_count_tot / $TOTALleads) * 100);}
	if ( ($UR_threeT_count_tot > 0) and ($TOTALleads > 0) )   {$UR_threeT_count_Tpc = (($UR_threeT_count_tot / $TOTALleads) * 100);}

	$UR_count_totS =	sprintf("%6.2f", $UR_count_Tpc); while(strlen($UR_count_totS)>7) {$UR_count_totS = substr("$UR_count_totS", 0, -1);}
	$UR_one_count_totS =	sprintf("%6.2f", $UR_one_count_Tpc); while(strlen($UR_one_count_totS)>7) {$UR_one_count_totS = substr("$UR_one_count_totS", 0, -1);}
	$UR_two_count_totS =	sprintf("%6.2f", $UR_two_count_Tpc); while(strlen($UR_two_count_totS)>7) {$UR_two_count_totS = substr("$UR_two_count_totS", 0, -1);}
	$UR_three_count_totS =	sprintf("%6.2f", $UR_three_count_Tpc); while(strlen($UR_three_count_totS)>7) {$UR_three_count_totS = substr("$UR_three_count_totS", 0, -1);}
	$UR_threeT_count_totS =	sprintf("%6.2f", $UR_threeT_count_Tpc); while(strlen($UR_threeT_count_totS)>7) {$UR_threeT_count_totS = substr("$UR_threeT_count_totS", 0, -1);}

	$BR_count_Tpc=0;
	$BR_one_count_Tpc=0;
	$BR_two_count_Tpc=0;
	$BR_three_count_Tpc=0;
	$BR_threeT_count_Tpc=0;
	if ( ($BR_count_tot > 0) and ($TOTALleads > 0) )   {$BR_count_Tpc = (($BR_count_tot / $TOTALleads) * 100);}
	if ( ($BR_one_count_tot > 0) and ($TOTALleads > 0) )   {$BR_one_count_Tpc = (($BR_one_count_tot / $TOTALleads) * 100);}
	if ( ($BR_two_count_tot > 0) and ($TOTALleads > 0) )   {$BR_two_count_Tpc = (($BR_two_count_tot / $TOTALleads) * 100);}
	if ( ($BR_three_count_tot > 0) and ($TOTALleads > 0) )   {$BR_three_count_Tpc = (($BR_three_count_tot / $TOTALleads) * 100);}
	if ( ($BR_threeT_count_tot > 0) and ($TOTALleads > 0) )   {$BR_threeT_count_Tpc = (($BR_threeT_count_tot / $TOTALleads) * 100);}

	$BR_count_totS =	sprintf("%6.2f", $BR_count_Tpc); while(strlen($BR_count_totS)>7) {$BR_count_totS = substr("$BR_count_totS", 0, -1);}
	$BR_one_count_totS =	sprintf("%6.2f", $BR_one_count_Tpc); while(strlen($BR_one_count_totS)>7) {$BR_one_count_totS = substr("$BR_one_count_totS", 0, -1);}
	$BR_two_count_totS =	sprintf("%6.2f", $BR_two_count_Tpc); while(strlen($BR_two_count_totS)>7) {$BR_two_count_totS = substr("$BR_two_count_totS", 0, -1);}
	$BR_three_count_totS =	sprintf("%6.2f", $BR_three_count_Tpc); while(strlen($BR_three_count_totS)>7) {$BR_three_count_totS = substr("$BR_three_count_totS", 0, -1);}
	$BR_threeT_count_totS =	sprintf("%6.2f", $BR_threeT_count_Tpc); while(strlen($BR_threeT_count_totS)>7) {$BR_threeT_count_totS = substr("$BR_threeT_count_totS", 0, -1);}

	$MR_count_Tpc=0;
	$MR_one_count_Tpc=0;
	$MR_two_count_Tpc=0;
	$MR_three_count_Tpc=0;
	$MR_threeT_count_Tpc=0;
	if ( ($MR_count_tot > 0) and ($TOTALleads > 0) )   {$MR_count_Tpc = (($MR_count_tot / $TOTALleads) * 100);}
	if ( ($MR_one_count_tot > 0) and ($TOTALleads > 0) )   {$MR_one_count_Tpc = (($MR_one_count_tot / $TOTALleads) * 100);}
	if ( ($MR_two_count_tot > 0) and ($TOTALleads > 0) )   {$MR_two_count_Tpc = (($MR_two_count_tot / $TOTALleads) * 100);}
	if ( ($MR_three_count_tot > 0) and ($TOTALleads > 0) )   {$MR_three_count_Tpc = (($MR_three_count_tot / $TOTALleads) * 100);}
	if ( ($MR_threeT_count_tot > 0) and ($TOTALleads > 0) )   {$MR_threeT_count_Tpc = (($MR_threeT_count_tot / $TOTALleads) * 100);}

	$MR_count_totS =	sprintf("%6.2f", $MR_count_Tpc); while(strlen($MR_count_totS)>7) {$MR_count_totS = substr("$MR_count_totS", 0, -1);}
	$MR_one_count_totS =	sprintf("%6.2f", $MR_one_count_Tpc); while(strlen($MR_one_count_totS)>7) {$MR_one_count_totS = substr("$MR_one_count_totS", 0, -1);}
	$MR_two_count_totS =	sprintf("%6.2f", $MR_two_count_Tpc); while(strlen($MR_two_count_totS)>7) {$MR_two_count_totS = substr("$MR_two_count_totS", 0, -1);}
	$MR_three_count_totS =	sprintf("%6.2f", $MR_three_count_Tpc); while(strlen($MR_three_count_totS)>7) {$MR_three_count_totS = substr("$MR_three_count_totS", 0, -1);}
	$MR_threeT_count_totS =	sprintf("%6.2f", $MR_threeT_count_Tpc); while(strlen($MR_threeT_count_totS)>7) {$MR_threeT_count_totS = substr("$MR_threeT_count_totS", 0, -1);}


	$TOTALleads =		sprintf("%10s", $TOTALleads);

	$OUToutput .= "+------------+------------------------------------------+----------+------------+----------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "\n";

	$OUToutput .= "             |            TOTALS:                                  | $TOTALleads |          |";
	$OUToutput .= " $HA_one_count_totS | $HA_two_count_totS | $HA_three_count_totS | $HA_threeT_count_totS | $HA_count_totS |";
	$OUToutput .= " $HR_one_count_totS% | $HR_two_count_totS% | $HR_three_count_totS% | $HR_threeT_count_totS% | $HR_count_totS% |";
	$OUToutput .= " $SA_one_count_totS | $SA_two_count_totS | $SA_three_count_totS | $SA_threeT_count_totS | $SA_count_totS |";
	$OUToutput .= " $SR_one_count_totS% | $SR_two_count_totS% | $SR_three_count_totS% | $SR_threeT_count_totS% | $SR_count_totS% |";
	$OUToutput .= " $DN_one_count_totS | $DN_two_count_totS | $DN_three_count_totS | $DN_threeT_count_totS | $DN_count_totS |";
	$OUToutput .= " $DR_one_count_totS% | $DR_two_count_totS% | $DR_three_count_totS% | $DR_threeT_count_totS% | $DR_count_totS% |";
	$OUToutput .= " $CC_one_count_totS | $CC_two_count_totS | $CC_three_count_totS | $CC_threeT_count_totS | $CC_count_totS |";
	$OUToutput .= " $CR_one_count_totS% | $CR_two_count_totS% | $CR_three_count_totS% | $CR_threeT_count_totS% | $CR_count_totS% |";
	$OUToutput .= " $UW_one_count_totS | $UW_two_count_totS | $UW_three_count_totS | $UW_threeT_count_totS | $UW_count_totS |";
	$OUToutput .= " $UR_one_count_totS% | $UR_two_count_totS% | $UR_three_count_totS% | $UR_threeT_count_totS% | $UR_count_totS% |";
	$OUToutput .= " $BA_one_count_totS | $BA_two_count_totS | $BA_three_count_totS | $BA_threeT_count_totS | $BA_count_totS |";
	$OUToutput .= " $BR_one_count_totS% | $BR_two_count_totS% | $BR_three_count_totS% | $BR_threeT_count_totS% | $BR_count_totS% |";
	$OUToutput .= " $MP_one_count_totS | $MP_two_count_totS | $MP_three_count_totS | $MP_threeT_count_totS | $MP_count_totS |";
	$OUToutput .= " $MR_one_count_totS% | $MR_two_count_totS% | $MR_three_count_totS% | $MR_threeT_count_totS% | $MR_count_totS% |";
	$OUToutput .= "\n";

	$OUToutput .= "             +------------------------------------------+----------+------------+          +";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "---------+---------+---------+---------+---------+";
	$OUToutput .= "\n";

	$CSV_text1.="\"\",\"\",\"TOTAL\",\"$TOTALleads\",\"\"";
	$CSV_text1.=",\"$HA_one_count_totS\",\"$HA_two_count_totS\",\"$HA_three_count_totS\",\"$HA_threeT_count_totS\",\"$HA_count_totS\"";
	$CSV_text1.=",\"$HR_one_count_totS%\",\"$HR_two_count_totS%\",\"$HR_three_count_totS%\",\"$HR_threeT_count_totS%\",\"$HR_count_totS%\"";
	$CSV_text1.=",\"$SA_one_count_totS\",\"$SA_two_count_totS\",\"$SA_three_count_totS\",\"$SA_threeT_count_totS\",\"$SA_count_totS\"";
	$CSV_text1.=",\"$SR_one_count_totS%\",\"$SR_two_count_totS%\",\"$SR_three_count_totS%\",\"$SR_threeT_count_totS%\",\"$SR_count_totS%\"";
	$CSV_text1.=",\"$DN_one_count_totS\",\"$DN_two_count_totS\",\"$DN_three_count_totS\",\"$DN_threeT_count_totS\",\"$DN_count_totS\"";
	$CSV_text1.=",\"$DR_one_count_totS%\",\"$DR_two_count_totS%\",\"$DR_three_count_totS%\",\"$DR_threeT_count_totS%\",\"$DR_count_totS%\"";
	$CSV_text1.=",\"$CC_one_count_totS\",\"$CC_two_count_totS\",\"$CC_three_count_totS\",\"$CC_threeT_count_totS\",\"$CC_count_totS\"";
	$CSV_text1.=",\"$CR_one_count_totS%\",\"$CR_two_count_totS%\",\"$CR_three_count_totS%\",\"$CR_threeT_count_totS%\",\"$CR_count_totS%\"";
	$CSV_text1.=",\"$UW_one_count_totS\",\"$UW_two_count_totS\",\"$UW_three_count_totS\",\"$UW_threeT_count_totS\",\"$UW_count_totS\"";
	$CSV_text1.=",\"$UR_one_count_totS%\",\"$UR_two_count_totS%\",\"$UR_three_count_totS%\",\"$UR_threeT_count_totS%\",\"$UR_count_totS%\"";
	$CSV_text1.=",\"$BA_one_count_totS\",\"$BA_two_count_totS\",\"$BA_three_count_totS\",\"$BA_threeT_count_totS\",\"$BA_count_totS\"";
	$CSV_text1.=",\"$BR_one_count_totS%\",\"$BR_two_count_totS%\",\"$BR_three_count_totS%\",\"$BR_threeT_count_totS%\",\"$BR_count_totS%\"";
	$CSV_text1.=",\"$MP_one_count_totS\",\"$MP_two_count_totS\",\"$MP_three_count_totS\",\"$MP_threeT_count_totS\",\"$MP_count_totS\"";
	$CSV_text1.=",\"$MR_one_count_totS%\",\"$MR_two_count_totS%\",\"$MR_three_count_totS%\",\"$MR_threeT_count_totS%\",\"$MR_count_totS%\"";
	$CSV_text1.="\n";



	if ($report_display_type=="HTML")
		{
		$MAIN.=$GRAPH;
		}
	else
		{
		$MAIN.="$OUToutput";
		}



	$ENDtime = date("U");
	$RUNtime = ($ENDtime - $STARTtime);
	$MAIN.="\nRun Time: $RUNtime seconds|$db_source\n";
	$MAIN.="</PRE>\n";
	$MAIN.="</TD></TR></TABLE>\n";
	$MAIN.="</BODY></HTML>\n";

	}

	if ($file_download>0) {
		$FILE_TIME = date("Ymd-His");
		$CSVfilename = "AST_LISTS_campaign_stats_$US$FILE_TIME.csv";
		$CSV_var="CSV_text".$file_download;
		$CSV_text=preg_replace('/^ +/', '', $$CSV_var);
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

	} else {
		$JS_onload.="}\n";
		$JS_text.=$JS_onload;
		$JS_text.="</script>\n";

		echo $HEADER;
		echo $JS_text;
		require("admin_header.php");
		echo $MAIN;
	}


if ($db_source == 'S')
	{
	mysqli_close($link);
	$use_slave_server=0;
	$db_source = 'M';
	require("dbconnect_mysqli.php");
	}

$endMS = microtime();
$startMSary = explode(" ",$startMS);
$endMSary = explode(" ",$endMS);
$runS = ($endMSary[0] - $startMSary[0]);
$runM = ($endMSary[1] - $startMSary[1]);
$TOTALrun = ($runS + $runM);

$stmt="UPDATE vicidial_report_log set run_time='$TOTALrun' where report_log_id='$report_log_id';";
if ($DB) {echo "|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);

exit;

?>
