<?php 
# AST_team_performance_detail.php
#
# This User-Group based report runs some very intensive SQL queries, so it is
# not recommended to run this on long time periods. This report depends on the
# QC statuses of QCFAIL, QCCANC and sales are defined by the Sale=Y status
# flags being set on those statuses.
#
# Copyright (C) 2012  Joe Johnson, Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGES
#
# 110802-2041 - First build
# 110804-0049 - Added First Call Resolution
# 111104-1259 - Added user_group restrictions for selecting in-groups
# 120224-1424 - Added new colums and PRECAL to System Time
#

require("dbconnect.php");
require("functions.php");

$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
if (isset($_GET["query_date_D"]))			{$query_date_D=$_GET["query_date_D"];}
	elseif (isset($_POST["query_date_D"]))	{$query_date_D=$_POST["query_date_D"];}
if (isset($_GET["end_date_D"]))				{$end_date_D=$_GET["end_date_D"];}
	elseif (isset($_POST["end_date_D"]))	{$end_date_D=$_POST["end_date_D"];}
if (isset($_GET["query_date_T"]))			{$query_date_T=$_GET["query_date_T"];}
	elseif (isset($_POST["query_date_T"]))	{$query_date_T=$_POST["query_date_T"];}
if (isset($_GET["end_date_T"]))				{$end_date_T=$_GET["end_date_T"];}
	elseif (isset($_POST["end_date_T"]))	{$end_date_T=$_POST["end_date_T"];}
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


$report_name = 'Team Performance Detail';
$db_source = 'M';

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

$PHP_AUTH_USER = ereg_replace("[^0-9a-zA-Z]","",$PHP_AUTH_USER);
$PHP_AUTH_PW = ereg_replace("[^0-9a-zA-Z]","",$PHP_AUTH_PW);

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

$stmt="SELECT allowed_campaigns,allowed_reports,admin_viewable_groups,admin_viewable_call_times from vicidial_user_groups where user_group='$LOGuser_group';";
if ($DB) {$HTML_text.="|$stmt|\n";}
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

$LOGallowed_campaignsSQL='';
$whereLOGallowed_campaignsSQL='';
if ( (!eregi("-ALL",$LOGallowed_campaigns)) )
	{
	$rawLOGallowed_campaignsSQL = preg_replace("/ -/",'',$LOGallowed_campaigns);
	$rawLOGallowed_campaignsSQL = preg_replace("/ /","','",$rawLOGallowed_campaignsSQL);
	$LOGallowed_campaignsSQL = "and campaign_id IN('$rawLOGallowed_campaignsSQL')";
	$whereLOGallowed_campaignsSQL = "where campaign_id IN('$rawLOGallowed_campaignsSQL')";
	}
$regexLOGallowed_campaigns = " $LOGallowed_campaigns ";

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

######################################

$MT[0]='';
$NOW_DATE = date("Y-m-d");
$NOW_TIME = date("Y-m-d H:i:s");
$STARTtime = date("U");
if (!isset($group)) {$group = '';}
if (!isset($query_date_D)) {$query_date_D=$NOW_DATE;}
if (!isset($end_date_D)) {$end_date_D=$NOW_DATE;}
if (!isset($query_date_T)) {$query_date_T="00:00:00";}
if (!isset($end_date_T)) {$end_date_T="23:59:59";}


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
	if (ereg("-ALL",$group_string) )
		{$group[$i] = $groups[$i];}
	$i++;
	}

#######################################
for ($i=0; $i<count($user_group); $i++) 
	{
	if (eregi("--ALL--", $user_group[$i])) {$all_user_groups=1; $user_group="";}
	}

$stmt="select user_group from vicidial_user_groups $whereLOGadmin_viewable_groupsSQL order by user_group;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$HTML_text.="$stmt\n";}
$user_groups_to_print = mysql_num_rows($rslt);
$i=0;
while ($i < $user_groups_to_print)
	{
	$row=mysql_fetch_row($rslt);
	$user_groups[$i] =$row[0];
	if ($all_user_groups) {$user_group[$i]=$row[0];}
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

if ( (ereg("--ALL--",$group_string) ) or ($group_ct < 1) )
	{$group_SQL = "";}
else
	{
	$group_SQL = eregi_replace(",$",'',$group_SQL);
	$group_SQL_str=$group_SQL;
	$group_SQL = "and campaign_id IN($group_SQL)";
	}

$i=0;
$user_group_string='|';
$user_group_ct = count($user_group);
while($i < $user_group_ct)
	{
	$user_group_string .= "$user_group[$i]|";
	$user_group_SQL .= "'$user_group[$i]',";
	$user_groupQS .= "&user_group[]=$user_group[$i]";
	$i++;
	}

if ( (ereg("--ALL--",$user_group_string) ) or ($user_group_ct < 1) )
	{$user_group_SQL = "";}
else
	{
	$user_group_SQL = eregi_replace(",$",'',$user_group_SQL);
	$user_group_SQL = "and vicidial_agent_log.user_group IN($user_group_SQL)";
	}
######################################
if ($DB) {$HTML_text.="$user_group_string|$user_group_ct|$user_groupQS|$i<BR>";}


###########################

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

$query_date="$query_date_D $query_date_T";
$end_date="$end_date_D $end_date_T";

$HTML_head.="<script language=\"JavaScript\" src=\"calendar_db.js\"></script>\n";
$HTML_head.="<link rel=\"stylesheet\" href=\"calendar.css\">\n";

$HTML_head.="<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=utf-8\">\n";
$HTML_head.="<TITLE>$report_name</TITLE></HEAD><BODY BGCOLOR=WHITE marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>$group_S\n";

$HTML_text.="<TABLE CELLPADDING=4 CELLSPACING=0><TR><TD>";

$HTML_text.="<FORM ACTION=\"$PHP_SELF\" METHOD=GET name=vicidial_report id=vicidial_report>\n";
$HTML_text.="<TABLE CELLSPACING=3><TR><TD VALIGN=TOP> Dates:<BR>";
$HTML_text.="<INPUT TYPE=HIDDEN NAME=DB VALUE=\"$DB\">\n";
$HTML_text.="<INPUT TYPE=HIDDEN NAME=type VALUE=\"$type\">\n";
$HTML_text.="Date Range:<BR>\n";

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
if  (eregi("--ALL--",$group_string))
	{$HTML_text.="<option value=\"--ALL--\" selected>-- ALL CAMPAIGNS --</option>\n";}
else
	{$HTML_text.="<option value=\"--ALL--\">-- ALL CAMPAIGNS --</option>\n";}
$o=0;
while ($campaigns_to_print > $o)
	{
	if (eregi("$groups[$o]\|",$group_string)) 
		{$HTML_text.="<option selected value=\"$groups[$o]\">$groups[$o]</option>\n";}
	else 
		{$HTML_text.="<option value=\"$groups[$o]\">$groups[$o]</option>\n";}
	$o++;
	}
$HTML_text.="</SELECT>\n";

$HTML_text.="</TD><TD VALIGN=TOP>Teams/User Groups:<BR>";
$HTML_text.="<SELECT SIZE=5 NAME=user_group[] multiple>\n";

if  (eregi("--ALL--",$user_group_string))
	{$HTML_text.="<option value=\"--ALL--\" selected>-- ALL USER GROUPS --</option>\n";}
else
	{$HTML_text.="<option value=\"--ALL--\">-- ALL USER GROUPS --</option>\n";}
$o=0;
while ($user_groups_to_print > $o)
	{
	if  (eregi("\|$user_groups[$o]\|",$user_group_string)) 
		{$HTML_text.="<option selected value=\"$user_groups[$o]\">$user_groups[$o]</option>\n";}
	else 
		{$HTML_text.="<option value=\"$user_groups[$o]\">$user_groups[$o]</option>\n";}
	$o++;
	}
$HTML_text.="</SELECT>\n";
$HTML_text.="</TD><TD VALIGN=TOP>\n";
$HTML_text.="<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=SUBMIT>\n";
$HTML_text.="</TD><TD VALIGN=TOP> &nbsp; &nbsp; &nbsp; &nbsp; ";

$HTML_text.="<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;\n";
$HTML_text.="<a href=\"$PHP_SELF?DB=$DB&query_date=$query_date&end_date=$end_date&query_date_D=$query_date_D&query_date_T=$query_date_T&end_date_D=$end_date_D&end_date_T=$end_date_T$groupQS$user_groupQS&file_download=1&SUBMIT=$SUBMIT\">DOWNLOAD</a> |";
$HTML_text.=" <a href=\"./admin.php?ADD=999999\">REPORTS</a> </FONT>\n";
$HTML_text.="</FONT>\n";
$HTML_text.="</TD></TR></TABLE>";
$HTML_text.="</FORM>\n\n";

if ($SUBMIT=="SUBMIT") 
	{
	# Sale counts per rep 
	$stmt="select max(event_time), vicidial_agent_log.user, vicidial_agent_log.lead_id, vicidial_list.status as current_status from vicidial_agent_log, vicidial_list where event_time>='$query_date' and event_time<='$end_date' $group_SQL and vicidial_agent_log.status in (select status from vicidial_campaign_statuses where sale='Y' $group_SQL UNION select status from vicidial_statuses where sale='Y') and vicidial_agent_log.lead_id=vicidial_list.lead_id group by vicidial_agent_log.user, vicidial_agent_log.lead_id";
	if ($DB) {$HTML_text.="$stmt\n";}
	$rslt=mysql_query($stmt, $link);
	while ($row=mysql_fetch_array($rslt)) 
		{
		$lead_id=$row["lead_id"];
		$user=$row["user"];
		$current_status=$row["current_status"];
		if (eregi("QCCANC", $current_status)) 
			{
			$cancel_array[$row["user"]]++;
			} 
		else if (eregi("QCFAIL", $current_status)) 
			{
			$incomplete_array[$row["user"]]++;
			} 
		else 
			{
			$sale_array[$row["user"]]++;

			# Get actual talk time for all calls made by the user for this particular lead. If cancelled and incomplete sales are to have their times 
			# counted towards sales talk time, move the below lines OUTSIDE the curly bracket below, so the query runs regardless of what "type" of 
			# sale it is.
			$sale_time_stmt="select sum(talk_sec)-sum(dead_sec) from vicidial_agent_log where user='$user' and lead_id='$lead_id' $group_SQL";
			if ($DB) {$HTML_text.="$sale_time_stmt\n";}
			$sale_time_rslt=mysql_query($sale_time_stmt, $link);
			$sale_time_row=mysql_fetch_row($sale_time_rslt);
			$sales_talk_time_array[$row["user"]]+=$sale_time_row[0];
			}
		}

	$HTML_text.="<PRE><FONT SIZE=2>";
	$total_average_sale_time=0;
	$total_average_contact_time=0;
	$total_talk_time=0; 
	$total_system_time=0; 
	$total_calls=0;	
	$total_leads=0;
	$total_contacts=0;
	$total_sales=0;
	$total_inc_sales=0;
	$total_cnc_sales=0;
	$total_callbacks=0;
	$total_stcall=0;

	for($i=0; $i<$user_group_ct; $i++) 
		{
		$group_average_sale_time=0;
		$group_average_contact_time=0;
		$group_talk_time=0; 
		$group_system_time=0; 
		$group_nonpause_time=0;
		$group_calls=0;	
		$group_leads=0;
		$group_contacts=0;
		$group_sales=0;
		$group_inc_sales=0;
		$group_cnc_sales=0;
		$group_callbacks=0;
		$group_stcall=0;
		$name_stmt="select group_name from vicidial_user_groups where user_group='$user_group[$i]'";
		$name_rslt=mysql_query($name_stmt, $link);
		$name_row=mysql_fetch_row($name_rslt);
		$group_name=$name_row[0];

		$HTML_text.="--- <B>TEAM: $user_group[$i] - $group_name</B>\n";
		$CSV_text.="\"\",\"TEAM: $user_group[$i] - $group_name\"\n";

		#### USER COUNTS
		$user_stmt="select distinct vicidial_users.full_name, vicidial_users.user from vicidial_users, vicidial_agent_log where vicidial_users.user_group='$user_group[$i]' and vicidial_users.user=vicidial_agent_log.user and vicidial_agent_log.user_group='$user_group[$i]'  and vicidial_agent_log.event_time>='$query_date' and vicidial_agent_log.event_time<='$end_date' and vicidial_agent_log.campaign_id in ($group_SQL_str) order by full_name, user";
		if ($DB) {$HTML_text.="$user_stmt\n";}
		$user_rslt=mysql_query($user_stmt, $link);
		if (mysql_num_rows($user_rslt)>0) 
			{
			$j=0;
			$HTML_text.="+------------------------------------------+------------+-------+-------+----------+---------------+---------------+-------------+-----------+-------+------------------------+----------------------+-------------------------+----------------+------------------+-----------------+-----------+-----------------------+-------------------+----------------------+\n";
			$HTML_text.="| Agent Name                               | Agent ID   | Calls | Leads | Contacts | Contact Ratio | Nonpause Time | System Time | Talk Time | Sales | Sales per Working Hour | Sales to Leads Ratio | Sales to Contacts Ratio | Sales Per Hour | Incomplete Sales | Cancelled Sales | Callbacks | First Call Resolution | Average Sale Time | Average Contact Time |\n";
			$HTML_text.="+------------------------------------------+------------+-------+-------+----------+---------------+---------------+-------------+-----------+-------+------------------------+----------------------+-------------------------+----------------+------------------+-----------------+-----------+-----------------------+-------------------+----------------------+\n";
			$CSV_text.="\"\",\"Agent Name\",\"Agent ID\",\"Calls\",\"Leads\",\"Contacts\",\"Contact Ratio\",\"Nonpause Time\",\"System Time\",\"Talk Time\",\"Sales\",\"Sales per Working Hour\",\"Sales to Leads Ratio\",\"Sales to Contacts Ratio\",\"Sales Per Hour\",\"Incomplete Sales\",\"Cancelled Sales\",\"Callbacks\",\"First Call Resolution\",\"Average Sale Time\",\"Average Contact Time\"\n";
			while ($user_row=mysql_fetch_array($user_rslt)) 
				{
				$j++;
				$contacts=0;
				$callbacks=0;
				$stcall=0;
				$calls=0;
				$leads=0;
				$system_time=0;
				$talk_time=0;
				$nonpause_time=0;
				# For each user
				$user=$user_row["user"];
				$sale_array[$user]+=0;  # For agents with no sales logged
				$incomplete_array[$user]+=0;  # For agents with no QCFAIL logged
				$cancel_array[$user]+=0;  # For agents with no QCCANC logged

				# Leads 
				$lead_stmt="select count(distinct lead_id) from vicidial_agent_log where lead_id is not null and event_time>='$query_date' and event_time<='$end_date' $group_SQL and user='$user' and user_group='$user_group[$i]'";
				if ($DB) {$HTML_text.="$lead_stmt\n";}
				$lead_rslt=mysql_query($lead_stmt, $link);
				$lead_row=mysql_fetch_row($lead_rslt);
				$leads=$lead_row[0];

				# Callbacks 
				$callback_stmt="select count(*) from vicidial_callbacks where status in ('ACTIVE', 'LIVE') $group_SQL and user='$user' and user_group='$user_group[$i]'";
				if ($DB) {$HTML_text.="$callback_stmt\n";}
				$callback_rslt=mysql_query($callback_stmt, $link);
				$callback_row=mysql_fetch_row($callback_rslt);
				$callbacks=$callback_row[0];

				$stat_stmt="select val.status, val.sub_status, vs.customer_contact, sum(val.talk_sec), sum(val.pause_sec), sum(val.wait_sec), sum(val.dispo_sec), sum(val.dead_sec), count(*) from vicidial_agent_log val, vicidial_statuses vs where val.user='$user' and val.user_group='$user_group[$i]' and val.event_time>='$query_date' and val.event_time<='$end_date' and val.status=vs.status and vs.status in (select status from vicidial_statuses) and val.campaign_id in ($group_SQL_str) group by status, customer_contact UNION select val.status, val.sub_status, vs.customer_contact, sum(val.talk_sec), sum(val.pause_sec), sum(val.wait_sec), sum(val.dispo_sec), sum(val.dead_sec), count(*) from vicidial_agent_log val, vicidial_campaign_statuses vs where val.campaign_id in ($group_SQL_str) and val.user='$user' and val.user_group='$user_group[$i]' and val.event_time>='$query_date' and val.event_time<='$end_date' and val.status=vs.status and val.campaign_id=vs.campaign_id and vs.status in (select distinct status from vicidial_campaign_statuses where ".substr($group_SQL, 4).") group by status, customer_contact";
				if ($DB) {$HTML_text.="$stat_stmt\n";}
				$stat_rslt=mysql_query($stat_stmt, $link);
				while ($stat_row=mysql_fetch_row($stat_rslt)) 
					{
					if ($stat_row[2]=="Y") 
						{
						$contacts+=$stat_row[8]; 
						$contact_talk_time+=($stat_row[3]-$stat_row[7]);

						$group_contact_talk_time+=($stat_row[3]-$stat_row[7]);
						}
					# if ($stat_row[2]=="Y") {$callbacks+=$stat_row[8];}
					$calls+=$stat_row[8];
					$talk_time+=($stat_row[3]-$stat_row[7]);
					$system_time+=($stat_row[3]+$stat_row[5]+$stat_row[6]);
					$nonpause_time+=($stat_row[3]+$stat_row[5]+$stat_row[6]);
					if ($stat_row[1]=="PRECAL") 
						{
						$nonpause_time+=$stat_row[4];
						}
					}
				$user_talk_time =		sec_convert($talk_time,'H'); 
				$group_talk_time+=$talk_time;
				$user_system_time =		sec_convert($system_time,'H'); 
				$talk_hours=$talk_time/3600;
				$group_system_time+=$system_time;
				$user_nonpause_time =		sec_convert($nonpause_time,'H'); 
				$group_nonpause_time+=$nonpause_time;

				if ($sale_array[$user]>0) {$average_sale_time=sec_convert(round($sales_talk_time_array[$user]/$sale_array[$user]), 'H');} else {$average_sale_time="00:00";}
				$group_sales_talk_time+=$sales_talk_time_array[$user];
				if ($contacts>0) {$average_contact_time=sec_convert(round($contact_talk_time/$contacts), 'H');} else {$average_contact_time="00:00";}

				$HTML_text.="| ".sprintf("%-40s", $user_row["full_name"]);
				$HTML_text.=" | <a href='user_stats.php?user=$user&begin_date=$query_date_D&end_date=$end_date_D'>".sprintf("%10s", "$user")."</a>";
				$HTML_text.=" | ".sprintf("%5s", $calls);	$group_calls+=$calls;
				$HTML_text.=" | ".sprintf("%5s", $leads);	$group_leads+=$leads;
				$HTML_text.=" | ".sprintf("%8s", $contacts);  $group_contacts+=$contacts;
				if ($leads>0) 
					{
					$contact_ratio=sprintf("%.2f", (100*$contacts/$leads));
					}
				else 
					{
					$contact_ratio="0.00";
					}
				$HTML_text.=" | ".sprintf("%12s", $contact_ratio)."%";
				$HTML_text.=" | ".sprintf("%13s", $user_nonpause_time);
				$HTML_text.=" | ".sprintf("%11s", $user_system_time);
				$HTML_text.=" | ".sprintf("%9s", $user_talk_time);
				$HTML_text.=" | ".sprintf("%5s", $sale_array[$user]);	$group_sales+=$sale_array[$user];
				if ($nonpause_time>0) 
					{
					$sales_per_working_hours=sprintf("%.2f", ($sale_array[$user]/($nonpause_time/3600)));
					}
				else
					{
					$sales_per_working_hours="0.00";
					}
				$HTML_text.=" | ".sprintf("%22s", $sales_per_working_hours);
				if ($leads>0) 
					{
					$sales_ratio=sprintf("%.2f", (100*$sale_array[$user]/$leads));
					}
				else 
					{
					$sales_ratio="0.00";
					}
				$HTML_text.=" | ".sprintf("%19s", $sales_ratio)."%";
				if ($contacts>0) 
					{
					$sale_contact_ratio=sprintf("%.2f", (100*$sale_array[$user]/$contacts));
					}
				else 
					{
					$sale_contact_ratio=0;
					}
				$HTML_text.=" | ".sprintf("%22s", $sale_contact_ratio)."%";
				if ($talk_hours>0) 
					{
					$sales_per_hour=sprintf("%.2f", ($sale_array[$user]/$talk_hours));
					}
				else 
					{
					$sales_per_hour="0.00";
					}
				if ( ($calls>0) and ($leads>0) )
					{
					$stcall=sprintf("%.2f", ($calls/$leads));
					}
				else 
					{
					$stcall="0.00";
					}
				$HTML_text.=" | ".sprintf("%14s", $sales_per_hour);
				$HTML_text.=" | ".sprintf("%16s", $incomplete_array[$user]);  $group_inc_sales+=$incomplete_array[$user];
				$HTML_text.=" | ".sprintf("%15s", $cancel_array[$user]);  $group_cnc_sales+=$cancel_array[$user];
				$HTML_text.=" | ".sprintf("%9s", $callbacks);  $group_callbacks+=$callbacks;
				$HTML_text.=" | ".sprintf("%21s", $stcall);	# first call resolution
				$HTML_text.=" | ".sprintf("%17s", $average_sale_time);
				$HTML_text.=" | ".sprintf("%20s", $average_contact_time)." |\n";
				$CSV_text.="\"$j\",\"$user_row[full_name]\",\"$user\",\"$calls\",\"$leads\",\"$contacts\",\"$contact_ratio %\",\"$user_nonpause_time\",\"$user_system_time\",\"$user_talk_time\",\"$sale_array[$user]\",\"$sales_per_working_hours\",\"$sales_ratio\",\"$sale_contact_ratio\",\"$sales_per_hour\",\"$incomplete_array[$user]\",\"$cancel_array[$user]\",\"$callbacks\",\"$stcall\",\"$average_sale_time\",\"$average_contact_time\"\n";
				}

			##### GROUP TOTALS #############
			if ($group_sales>0) 
				{
				$group_average_sale_time=sec_convert(round($group_sales_talk_time/$group_sales), 'H');
				} 
			else 
				{
				$group_average_sale_time="00:00:00";
				}
			if ($group_contacts>0) 
				{
				$group_average_contact_time=sec_convert(round($group_contact_talk_time/$group_contacts), 'H');
				} 
			else 
				{
				$group_average_contact_time="00:00:00";
				}
			$group_talk_hours=$group_talk_time/3600;

			$GROUP_text.="| ".sprintf("%40s", "$group_name");
			$GROUP_text.=" | ".sprintf("%10s", "$user_group[$i]");

			$HTML_text.="+------------------------------------------+------------+-------+-------+----------+---------------+---------------+-------------+-----------+-------+------------------------+----------------------+-------------------------+----------------+------------------+-----------------+-----------+-----------------------+-------------------+----------------------+\n";
			$HTML_text.="| ".sprintf("%40s", "");
			$HTML_text.=" | ".sprintf("%10s", "TOTALS:");

			$TOTAL_text=" | ".sprintf("%5s", $group_calls);	
			$TOTAL_text.=" | ".sprintf("%5s", $group_leads);
			$TOTAL_text.=" | ".sprintf("%8s", $group_contacts);
			if ($group_leads>0) 
				{
				$group_contact_ratio=sprintf("%.2f", (100*$group_contacts/$group_leads));
				} 
			else 
				{
				$group_contact_ratio="0.00";
				}
			$TOTAL_text.=" | ".sprintf("%12s", $group_contact_ratio)."%";
			$TOTAL_text.=" | ".sprintf("%13s", sec_convert($group_nonpause_time,'H'));
			$TOTAL_text.=" | ".sprintf("%11s", sec_convert($group_system_time,'H'));
			$TOTAL_text.=" | ".sprintf("%9s", sec_convert($group_talk_time,'H'));
			$TOTAL_text.=" | ".sprintf("%5s", $group_sales);
			if ($group_nonpause_time>0) 
				{
				$sales_per_working_hours=sprintf("%.2f", ($group_sales/($group_nonpause_time/3600)));
				}
			else
				{
				$sales_per_working_hours="0.00";
				}
			$TOTAL_text.=" | ".sprintf("%22s", $sales_per_working_hours);
			if ($group_leads>0) 
				{
				$group_sales_ratio=sprintf("%.2f", (100*$group_sales/$group_leads));
				} 
			else 
				{
				$group_sales_ratio="0.00";
				}	
			$TOTAL_text.=" | ".sprintf("%19s", $group_sales_ratio)."%";
			if ($group_contacts>0) 
				{
				$group_sale_contact_ratio=sprintf("%.2f", (100*$group_sales/$group_contacts));
				} 
			else 
				{
				$group_sale_contact_ratio=0;
				}
			$TOTAL_text.=" | ".sprintf("%22s", $group_sale_contact_ratio)."%";
			if ($group_talk_hours>0) 
				{
				$group_sales_per_hour=sprintf("%.2f", ($group_sales/$group_talk_hours));
				} 
			else 
				{
				$group_sales_per_hour="0.00";
				}
			if ( ($group_calls>0) and ($group_leads>0) )
				{
				$group_stcall=sprintf("%.2f", ($group_calls/$group_leads));
				} 
			else 
				{
				$group_stcall="0.00";
				}
			$TOTAL_text.=" | ".sprintf("%14s", $group_sales_per_hour);
			$TOTAL_text.=" | ".sprintf("%16s", $group_inc_sales);
			$TOTAL_text.=" | ".sprintf("%15s", $group_cnc_sales);
			$TOTAL_text.=" | ".sprintf("%9s", $group_callbacks);
			$TOTAL_text.=" | ".sprintf("%21s", $group_stcall); 	# first call resolution
			$TOTAL_text.=" | ".sprintf("%17s", $group_average_sale_time);
			$TOTAL_text.=" | ".sprintf("%20s", $group_average_contact_time)." |\n";

			$HTML_text.=$TOTAL_text;
			$GROUP_text.=$TOTAL_text;

			$HTML_text.="+------------------------------------------+------------+-------+-------+----------+---------------+---------------+-------------+-----------+-------+------------------------+----------------------+-------------------------+----------------+------------------+-----------------+-----------+-----------------------+-------------------+----------------------+\n";
			$HTML_text.="\n\n";

			$CSV_text.="\"\",\"\",\"TOTALS:\",\"$group_calls\",\"$group_leads\",\"$group_contacts\",\"$group_contact_ratio %\",\"".sec_convert($group_nonpause_time,'H')."\",\"".sec_convert($group_system_time,'H')."\",\"".sec_convert($group_talk_time,'H')."\",\"$group_sales\",\"$sales_per_working_hours\",\"$group_sales_ratio\",\"$group_sale_contact_ratio\",\"$group_sales_per_hour\",\"$group_inc_sales\",\"$group_cnc_sales\",\"$group_callbacks\",\"$group_stcall\",\"$group_average_sale_time\",\"$group_average_contact_time\"\n";
			$GROUP_CSV_text.="\"$i\",\"$group_name\",\"$user_group[$i]\",\"$group_calls\",\"$group_leads\",\"$group_contacts\",\"$group_contact_ratio %\",\"".sec_convert($group_nonpause_time,'H')."\",\"".sec_convert($group_system_time,'H')."\",\"".sec_convert($group_talk_time,'H')."\",\"$group_sales\",\"$sales_per_working_hours\",\"$group_sales_ratio\",\"$group_sale_contact_ratio\",\"$group_sales_per_hour\",\"$group_inc_sales\",\"$group_cnc_sales\",\"$group_callbacks\",\"$group_stcall\",\"$group_average_sale_time\",\"$group_average_contact_time\"\n";
			$CSV_text.="\n\n";

			$total_calls+=$group_calls;
			$total_leads+=$group_leads;
			$total_contacts+=$group_contacts;
			$total_system_time+=$group_system_time;
			$total_nonpause_time+=$group_nonpause_time;
			$total_talk_time+=$group_talk_time;
			$total_sales+=$group_sales;
			$total_inc_sales+=$group_inc_sales;
			$total_cnc_sales+=$group_cnc_sales;
			$total_callbacks+=$group_callbacks;
			$total_stcall+=$group_stcall; 	# first call resolution
			$total_sales_talk_time+=$group_sales_talk_time;
			$total_contact_talk_time+=$group_contact_talk_time;

			#flush();
			} 
		else 
			{
			$HTML_text.="    **** NO AGENTS FOUND UNDER THESE REPORT PARAMETERS ****\n\n";
			$CSV_text.="\"\",\"**** NO AGENTS FOUND UNDER THESE REPORT PARAMETERS ****\"\n\n";
			}
		}

	$HTML_text.="--- <B>CALL CENTER TOTAL</B>\n";
	$HTML_text.="+------------------------------------------+------------+-------+-------+----------+---------------+---------------+-------------+-----------+-------+------------------------+----------------------+-------------------------+----------------+------------------+-----------------+-----------+-----------------------+-------------------+----------------------+\n";
	$HTML_text.="| Team Name                                | Team ID    | Calls | Leads | Contacts | Contact Ratio | Nonpause Time | System Time | Talk Time | Sales | Sales per Working Hour | Sales to Leads Ratio | Sales to Contacts Ratio | Sales Per Hour | Incomplete Sales | Cancelled Sales | Callbacks | First Call Resolution | Average Sale Time | Average Contact Time |\n";
	$HTML_text.="+------------------------------------------+------------+-------+-------+----------+---------------+---------------+-------------+-----------+-------+------------------------+----------------------+-------------------------+----------------+------------------+-----------------+-----------+-----------------------+-------------------+----------------------+\n";
	$HTML_text.=$GROUP_text;
	$HTML_text.="+------------------------------------------+------------+-------+-------+----------+---------------+---------------+-------------+-----------+-------+------------------------+----------------------+-------------------------+----------------+------------------+-----------------+-----------+-----------------------+-------------------+----------------------+\n";

	if ($total_sales>0) 
		{
		$total_average_sale_time=sec_convert(round($total_sales_talk_time/$total_sales), 'H');
		} 
	else 
		{
		$total_average_sale_time="00:00:00";
		}
	if ($total_contacts>0) 
		{
		$total_average_contact_time=sec_convert(round($total_contact_talk_time/$total_contacts), 'H');
		} 
	else 
		{
		$total_average_contact_time="00:00:00";
		}
	$total_talk_hours=$total_talk_time/3600;

	$HTML_text.="| ".sprintf("%40s", "");
	$HTML_text.=" | ".sprintf("%10s", "TOTALS:");
	$HTML_text.=" | ".sprintf("%5s", $total_calls);	
	$HTML_text.=" | ".sprintf("%5s", $total_leads);
	$HTML_text.=" | ".sprintf("%8s", $total_contacts);
	if ($total_leads>0) 
		{
		$total_contact_ratio=sprintf("%.2f", (100*$total_contacts/$total_leads));
		} 
	else 
		{
		$total_contact_ratio="0.00";
		}
	$HTML_text.=" | ".sprintf("%12s", $total_contact_ratio)."%";
	$HTML_text.=" | ".sprintf("%13s", sec_convert($total_nonpause_time,'H'));
	$HTML_text.=" | ".sprintf("%11s", sec_convert($total_system_time,'H'));
	$HTML_text.=" | ".sprintf("%9s", sec_convert($total_talk_time,'H'));
	$HTML_text.=" | ".sprintf("%5s", $total_sales);
	if ($total_nonpause_time>0) 
		{
		$sales_per_working_hours=sprintf("%.2f", ($total_sales/($total_nonpause_time/3600)));
		}
	else
		{
		$sales_per_working_hours="0.00";
		}
	$HTML_text.=" | ".sprintf("%22s", $sales_per_working_hours);
	if ($total_leads>0) 
		{
		$total_sales_ratio=sprintf("%.2f", (100*$total_sales/$total_leads));
		} 
	else 
		{
		$total_sales_ratio="0.00";
		}	
	$HTML_text.=" | ".sprintf("%19s", $total_sales_ratio)."%";
	if ($total_contacts>0) 
		{
		$total_sale_contact_ratio=sprintf("%.2f", (100*$total_sales/$total_contacts));
		} 
	else 
		{
		$total_sale_contact_ratio=0;
		}
	$HTML_text.=" | ".sprintf("%22s", $total_sale_contact_ratio)."%";
	if ($total_talk_hours>0) 
		{
		$total_sales_per_hour=sprintf("%.2f", ($total_sales/$total_talk_hours));
		} 
	else 
		{
		$total_sales_per_hour="0.00";
		}
	if ( ($total_calls>0) and ($total_leads>0) )
		{
		$total_stcall=sprintf("%.2f", ($total_calls/$total_leads));
		} 
	else 
		{
		$total_stcall="0.00";
		}
	$HTML_text.=" | ".sprintf("%14s", $total_sales_per_hour);
	$HTML_text.=" | ".sprintf("%16s", $total_inc_sales);
	$HTML_text.=" | ".sprintf("%15s", $total_cnc_sales);
	$HTML_text.=" | ".sprintf("%9s", $total_callbacks);
	$HTML_text.=" | ".sprintf("%21s", $total_stcall); 	# first call resolution
	$HTML_text.=" | ".sprintf("%17s", $total_average_sale_time);
	$HTML_text.=" | ".sprintf("%20s", $total_average_contact_time)." |\n";
	$HTML_text.="+------------------------------------------+------------+-------+-------+----------+---------------+---------------+-------------+-----------+-------+------------------------+----------------------+-------------------------+----------------+------------------+-----------------+-----------+-----------------------+-------------------+----------------------+\n";
	$HTML_text.="</FONT></PRE>";
	$HTML_text.="</BODY>\n";
	$HTML_text.="</HTML>\n";

	$CSV_text.="\"\",\"CALL CENTER TOTAL\"\n";
	$CSV_text.="\"\",\"Team Name\",\"Team ID\",\"Calls\",\"Leads\",\"Contacts\",\"Contact Ratio\",\"Nonpause Time\",\"System Time\",\"Talk Time\",\"Sales\",\"Sales per Working Hour\",\"Sales to Leads Ratio\",\"Sales to Contacts Ratio\",\"Sales Per Hour\",\"Incomplete Sales\",\"Cancelled Sales\",\"Callbacks\",\"First Call Resolution\",\"Average Sale Time\",\"Average Contact Time\"\n";
	$CSV_text.=$GROUP_CSV_text;
	$CSV_text.="\"\",\"\",\"TOTALS:\",\"$total_calls\",\"$total_leads\",\"$total_contacts\",\"$total_contact_ratio %\",\"".sec_convert($total_nonpause_time,'H')."\",\"".sec_convert($total_system_time,'H')."\",\"".sec_convert($total_talk_time,'H')."\",\"$total_sales\",\"$sales_per_working_hours\",\"$total_sales_ratio\",\"$total_sale_contact_ratio\",\"$total_sales_per_hour\",\"$total_inc_sales\",\"$total_cnc_sales\",\"$total_callbacks\",\"$total_stcall\",\"$total_average_sale_time\",\"$total_average_contact_time\"\n";
	}

if ($file_download>0) 
	{
	$FILE_TIME = date("Ymd-His");
	$CSVfilename = "AST_team_performance_detail_$US$FILE_TIME.csv";
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
	$short_header=1;
	require("admin_header.php");
	echo $HTML_text;
	flush();
	}
?>
