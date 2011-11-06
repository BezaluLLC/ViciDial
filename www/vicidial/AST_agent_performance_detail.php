<?php 
# AST_agent_performance_detail.php
# 
# Copyright (C) 2011  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGES
#
# 71119-2359 - First build
# 71121-0144 - Replace existing AST_agent_performance_detail.php script with this one
#            - Fixed zero division bug
# 71218-1155 - added end_date for multi-day reports
# 80428-0144 - UTF8 cleanup
# 80712-1007 - tally bug fixes and time display change
# 81030-0346 - Added pause code stats
# 81030-1924 - Added total non-pause and total logged-in time to pause code section
# 81108-0716 - fixed user same-name bug
# 81110-0056 - fixed pause code display bug
# 90310-2039 - Admin header
# 90508-0644 - Changed to PHP long tags
# 90523-0935 - Rewrite of seconds to minutes and hours conversion
# 90717-1500 - Changed to be multi-campaign, multi-user-group select
# 90908-1058 - Added DEAD time statistics
# 100203-1131 - Added CUSTOMER time statistics
# 100214-1421 - Sort menu alphabetically
# 100216-0042 - Added popup date selector
# 100712-1324 - Added system setting slave server option
# 100802-2347 - Added User Group Allowed Reports option validation and allowed campaigns restrictions
# 100914-1326 - Added lookup for user_level 7 users to set to reports only which will remove other admin links
# 110703-1739 - Added file download option
# 111007-0709 - Changed user and fullname to use non-truncated for output file
# 111104-1256 - Added user_group restrictions for selecting in-groups
#

require("dbconnect.php");
require("functions.php");

$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
if (isset($_GET["query_date"]))				{$query_date=$_GET["query_date"];}
	elseif (isset($_POST["query_date"]))	{$query_date=$_POST["query_date"];}
if (isset($_GET["end_date"]))				{$end_date=$_GET["end_date"];}
	elseif (isset($_POST["end_date"]))		{$end_date=$_POST["end_date"];}
if (isset($_GET["group"]))					{$group=$_GET["group"];}
	elseif (isset($_POST["group"]))			{$group=$_POST["group"];}
if (isset($_GET["user_group"]))				{$user_group=$_GET["user_group"];}
	elseif (isset($_POST["user_group"]))	{$user_group=$_POST["user_group"];}
if (isset($_GET["shift"]))					{$shift=$_GET["shift"];}
	elseif (isset($_POST["shift"]))			{$shift=$_POST["shift"];}
if (isset($_GET["stage"]))					{$stage=$_GET["stage"];}
	elseif (isset($_POST["stage"]))			{$stage=$_POST["stage"];}
if (isset($_GET["DB"]))						{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))			{$DB=$_POST["DB"];}
if (isset($_GET["submit"]))					{$submit=$_GET["submit"];}
	elseif (isset($_POST["submit"]))		{$submit=$_POST["submit"];}
if (isset($_GET["SUBMIT"]))					{$SUBMIT=$_GET["SUBMIT"];}
	elseif (isset($_POST["SUBMIT"]))		{$SUBMIT=$_POST["SUBMIT"];}
if (isset($_GET["file_download"]))				{$file_download=$_GET["file_download"];}
	elseif (isset($_POST["file_download"]))	{$file_download=$_POST["file_download"];}


if (strlen($shift)<2) {$shift='ALL';}

$report_name = 'Agent Performance Detail';
$db_source = 'M';

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,outbound_autodial_active,slave_db_server,reports_use_slave_db FROM system_settings;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$HTML_text.="$stmt\n";}
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

$MT[0]='';
$NOW_DATE = date("Y-m-d");
$NOW_TIME = date("Y-m-d H:i:s");
$STARTtime = date("U");
if (!isset($group)) {$group = '';}
if (!isset($query_date)) {$query_date = $NOW_DATE;}
if (!isset($end_date)) {$end_date = $NOW_DATE;}


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
$stmt="select user_group from vicidial_user_groups $whereLOGadmin_viewable_groupsSQL order by user_group;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$HTML_text.="$stmt\n";}
$user_groups_to_print = mysql_num_rows($rslt);
$i=0;
while ($i < $user_groups_to_print)
	{
	$row=mysql_fetch_row($rslt);
	$user_groups[$i] =$row[0];
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

if ($DB) {$HTML_text.="$user_group_string|$user_group_ct|$user_groupQS|$i<BR>";}

$LINKbase = "$PHP_SELF?query_date=$query_date&end_date=$end_date$groupQS$user_groupQS&shift=$shift&DB=$DB";


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
$HTML_head.="<TITLE>$report_name</TITLE></HEAD><BODY BGCOLOR=WHITE marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>\n";

	$short_header=1;

#	require("admin_header.php");

$HTML_text.="<TABLE CELLPADDING=4 CELLSPACING=0><TR><TD>";

$HTML_text.="<FORM ACTION=\"$PHP_SELF\" METHOD=GET name=vicidial_report id=vicidial_report>\n";
$HTML_text.="<TABLE CELLSPACING=3><TR><TD VALIGN=TOP> Dates:<BR>";
$HTML_text.="<INPUT TYPE=hidden NAME=DB VALUE=\"$DB\">\n";
$HTML_text.="<INPUT TYPE=TEXT NAME=query_date SIZE=10 MAXLENGTH=10 VALUE=\"$query_date\">";

$HTML_text.="<script language=\"JavaScript\">\n";
$HTML_text.="var o_cal = new tcal ({\n";
$HTML_text.="	// form name\n";
$HTML_text.="	'formname': 'vicidial_report',\n";
$HTML_text.="	// input name\n";
$HTML_text.="	'controlname': 'query_date'\n";
$HTML_text.="});\n";
$HTML_text.="o_cal.a_tpl.yearscroll = false;\n";
$HTML_text.="// o_cal.a_tpl.weekstart = 1; // Monday week start\n";
$HTML_text.="</script>\n";

$HTML_text.="<BR> to <BR><INPUT TYPE=TEXT NAME=end_date SIZE=10 MAXLENGTH=10 VALUE=\"$end_date\">";

$HTML_text.="<script language=\"JavaScript\">\n";
$HTML_text.="var o_cal = new tcal ({\n";
$HTML_text.="	// form name\n";
$HTML_text.="	'formname': 'vicidial_report',\n";
$HTML_text.="	// input name\n";
$HTML_text.="	'controlname': 'end_date'\n";
$HTML_text.="});\n";
$HTML_text.="o_cal.a_tpl.yearscroll = false;\n";
$HTML_text.="// o_cal.a_tpl.weekstart = 1; // Monday week start\n";
$HTML_text.="</script>\n";

$HTML_text.="</TD><TD VALIGN=TOP> Campaigns:<BR>";
$HTML_text.="<SELECT SIZE=5 NAME=group[] multiple>\n";
if  (eregi("--ALL--",$group_string))
	{$HTML_text.="<option value=\"--ALL--\" selected>-- ALL CAMPAIGNS --</option>\n";}
else
	{$HTML_text.="<option value=\"--ALL--\">-- ALL CAMPAIGNS --</option>\n";}
$o=0;
while ($campaigns_to_print > $o)
{
	if (eregi("$groups[$o]\|",$group_string)) {$HTML_text.="<option selected value=\"$groups[$o]\">$groups[$o]</option>\n";}
	  else {$HTML_text.="<option value=\"$groups[$o]\">$groups[$o]</option>\n";}
	$o++;
}
$HTML_text.="</SELECT>\n";
$HTML_text.="</TD><TD VALIGN=TOP>User Groups:<BR>";
$HTML_text.="<SELECT SIZE=5 NAME=user_group[] multiple>\n";

if  (eregi("--ALL--",$user_group_string))
	{$HTML_text.="<option value=\"--ALL--\" selected>-- ALL USER GROUPS --</option>\n";}
else
	{$HTML_text.="<option value=\"--ALL--\">-- ALL USER GROUPS --</option>\n";}
$o=0;
while ($user_groups_to_print > $o)
	{
	if  (eregi("$user_groups[$o]\|",$user_group_string)) {$HTML_text.="<option selected value=\"$user_groups[$o]\">$user_groups[$o]</option>\n";}
	  else {$HTML_text.="<option value=\"$user_groups[$o]\">$user_groups[$o]</option>\n";}
	$o++;
	}
$HTML_text.="</SELECT>\n";
$HTML_text.="</TD><TD VALIGN=TOP>Shift:<BR>";
$HTML_text.="<SELECT SIZE=1 NAME=shift>\n";
$HTML_text.="<option selected value=\"$shift\">$shift</option>\n";
$HTML_text.="<option value=\"\">--</option>\n";
$HTML_text.="<option value=\"AM\">AM</option>\n";
$HTML_text.="<option value=\"PM\">PM</option>\n";
$HTML_text.="<option value=\"ALL\">ALL</option>\n";
$HTML_text.="</SELECT><BR><BR>\n";
$HTML_text.="<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE=SUBMIT>\n";
$HTML_text.="</TD><TD VALIGN=TOP> &nbsp; &nbsp; &nbsp; &nbsp; ";

$HTML_text.="<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;\n";
$HTML_text.=" <a href=\"./admin.php?ADD=999999\">REPORTS</a> </FONT>\n";
$HTML_text.="</FONT>\n";
$HTML_text.="</TD></TR></TABLE>";

$HTML_text.="</FORM>\n\n";


$HTML_text.="<PRE><FONT SIZE=2>\n";


if (!$group)
{
$HTML_text.="\n";
$HTML_text.="PLEASE SELECT A CAMPAIGN AND DATE-TIME ABOVE AND CLICK SUBMIT\n";
$HTML_text.=" NOTE: stats taken from shift specified\n";
}

else
{
if ($shift == 'AM') 
	{
	$time_BEGIN=$AM_shift_BEGIN;
	$time_END=$AM_shift_END;
	if (strlen($time_BEGIN) < 6) {$time_BEGIN = "03:45:00";}   
	if (strlen($time_END) < 6) {$time_END = "15:15:00";}
	}
if ($shift == 'PM') 
	{
	$time_BEGIN=$PM_shift_BEGIN;
	$time_END=$PM_shift_END;
	if (strlen($time_BEGIN) < 6) {$time_BEGIN = "15:15:00";}
	if (strlen($time_END) < 6) {$time_END = "23:15:00";}
	}
if ($shift == 'ALL') 
	{
	if (strlen($time_BEGIN) < 6) {$time_BEGIN = "00:00:00";}
	if (strlen($time_END) < 6) {$time_END = "23:59:59";}
	}
$query_date_BEGIN = "$query_date $time_BEGIN";   
$query_date_END = "$end_date $time_END";

if (strlen($user_group)>0) {$ugSQL="and vicidial_agent_log.user_group='$user_group'";}
else {$ugSQL='';}

$HTML_text.="Agent Performance Detail                        $NOW_TIME\n";

$HTML_text.="Time range: $query_date_BEGIN to $query_date_END\n\n";
$HTML_text.="---------- AGENTS Details -------------\n\n";





$statuses='-';
$statusesTXT='';
$statusesHEAD='';

$CSV_header="\"Agent Performance Detail                        $NOW_TIME\"\n";
$CSV_header.="\"Time range: $query_date_BEGIN to $query_date_END\"\n\n";
$CSV_header.="\"---------- AGENTS Details -------------\"\n";
$CSV_header.='"USER NAME","ID","CALLS","TIME","PAUSE","PAUSAVG","WAIT","WAITAVG","TALK","TALKAVG","DISPO","DISPAVG","DEAD","DEADAVG","CUSTOMER","CUSTAVG"';

$statusesHTML='';
$statusesARY[0]='';
$j=0;
$users='-';
$usersARY[0]='';
$user_namesARY[0]='';
$k=0;

$stmt="select count(*) as calls,sum(talk_sec) as talk,full_name,vicidial_users.user,sum(pause_sec),sum(wait_sec),sum(dispo_sec),status,sum(dead_sec) from vicidial_users,vicidial_agent_log where event_time <= '$query_date_END' and event_time >= '$query_date_BEGIN' and vicidial_users.user=vicidial_agent_log.user and pause_sec<65000 and wait_sec<65000 and talk_sec<65000 and dispo_sec<65000 $group_SQL $user_group_SQL group by user,full_name,status order by full_name,user,status desc limit 500000;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$HTML_text.="$stmt\n";}
$rows_to_print = mysql_num_rows($rslt);
$i=0;
while ($i < $rows_to_print)
	{
	$row=mysql_fetch_row($rslt);
#	$row[0] = ($row[0] - 1);	# subtract 1 for login/logout event compensation
	
	$calls[$i] =		$row[0];
	$talk_sec[$i] =		$row[1];
	$full_name[$i] =	$row[2];
	$user[$i] =			$row[3];
	$pause_sec[$i] =	$row[4];
	$wait_sec[$i] =		$row[5];
	$dispo_sec[$i] =	$row[6];
	$status[$i] =		$row[7];
	$dead_sec[$i] =		$row[8];
	$customer_sec[$i] =	($talk_sec[$i] - $dead_sec[$i]);
	if ($customer_sec[$i] < 1)
		{$customer_sec[$i]=0;}
	if ( (!eregi("-$status[$i]-", $statuses)) and (strlen($status[$i])>0) )
		{
		$statusesTXT = sprintf("%8s", $status[$i]);
		$statusesHEAD .= "----------+";
		$statusesHTML .= " $statusesTXT |";

		$CSV_header.=",\"$status[$i]\"";

		$statuses .= "$status[$i]-";
		$statusesARY[$j] = $status[$i];
		$j++;
		}
	if (!eregi("-$user[$i]-", $users))
		{
		$users .= "$user[$i]-";
		$usersARY[$k] = $user[$i];
		$user_namesARY[$k] = $full_name[$i];
		$k++;
		}

	$i++;
	}

$CSV_header.="\n";
$CSV_lines='';

$HTML_text.="CALL STATS BREAKDOWN: (Statistics related to handling of calls only)     <a href=\"$LINKbase&stage=$stage&file_download=1\">[DOWNLOAD]</a>\n";
$HTML_text.="+-----------------+----------+--------+-----------+----------+--------+----------+--------+----------+--------+----------+--------+----------+--------+----------+--------+$statusesHEAD\n";
$HTML_text.="| <a href=\"$LINKbase\">USER NAME</a>       | <a href=\"$LINKbase&stage=ID\">ID</a>       | <a href=\"$LINKbase&stage=LEADS\">CALLS</a>  | <a href=\"$LINKbase&stage=TIME\">TIME</a>      | PAUSE    |PAUSAVG | WAIT     |WAITAVG | TALK     |TALKAVG | DISPO    |DISPAVG | DEAD     |DEADAVG | CUSTOMER |CUSTAVG |$statusesHTML\n";
$HTML_text.="+-----------------+----------+--------+-----------+----------+--------+----------+--------+----------+--------+----------+--------+----------+--------+----------+--------+$statusesHEAD\n";


### BEGIN loop through each user ###
$m=0;
while ($m < $k)
	{
	$Suser=$usersARY[$m];
	$Sfull_name=$user_namesARY[$m];
	$Stime=0;
	$Scalls=0;
	$Stalk_sec=0;
	$Spause_sec=0;
	$Swait_sec=0;
	$Sdispo_sec=0;
	$Sdead_sec=0;
	$Scustomer_sec=0;
	$SstatusesHTML='';
	$CSVstatuses='';

	### BEGIN loop through each status ###
	$n=0;
	while ($n < $j)
		{
		$Sstatus=$statusesARY[$n];
		$SstatusTXT='';
		### BEGIN loop through each stat line ###
		$i=0; $status_found=0;
		while ($i < $rows_to_print)
			{
			if ( ($Suser=="$user[$i]") and ($Sstatus=="$status[$i]") )
				{
				$Scalls =		($Scalls + $calls[$i]);
				$Stalk_sec =	($Stalk_sec + $talk_sec[$i]);
				$Spause_sec =	($Spause_sec + $pause_sec[$i]);
				$Swait_sec =	($Swait_sec + $wait_sec[$i]);
				$Sdispo_sec =	($Sdispo_sec + $dispo_sec[$i]);
				$Sdead_sec =	($Sdead_sec + $dead_sec[$i]);
				$Scustomer_sec =	($Scustomer_sec + $customer_sec[$i]);
				$SstatusTXT = sprintf("%8s", $calls[$i]);
				$SstatusesHTML .= " $SstatusTXT |";

				$CSVstatuses.=",\"$calls[$i]\"";

				$status_found++;
				}
			$i++;
			}
		if ($status_found < 1)
			{
			$SstatusesHTML .= "        0 |";
			$CSVstatuses.=",\"0\"";
			}
		### END loop through each stat line ###
		$n++;
		}
	### END loop through each status ###
	$Stime = ($Stalk_sec + $Spause_sec + $Swait_sec + $Sdispo_sec);
	$TOTcalls=($TOTcalls + $Scalls);
	$TOTtime=($TOTtime + $Stime);
	$TOTtotTALK=($TOTtotTALK + $Stalk_sec);
	$TOTtotWAIT=($TOTtotWAIT + $Swait_sec);
	$TOTtotPAUSE=($TOTtotPAUSE + $Spause_sec);
	$TOTtotDISPO=($TOTtotDISPO + $Sdispo_sec);
	$TOTtotDEAD=($TOTtotDEAD + $Sdead_sec);
	$TOTtotCUSTOMER=($TOTtotCUSTOMER + $Scustomer_sec);
	$Stime = ($Stalk_sec + $Spause_sec + $Swait_sec + $Sdispo_sec);
	if ( ($Scalls > 0) and ($Stalk_sec > 0) ) {$Stalk_avg = ($Stalk_sec/$Scalls);}
		else {$Stalk_avg=0;}
	if ( ($Scalls > 0) and ($Spause_sec > 0) ) {$Spause_avg = ($Spause_sec/$Scalls);}
		else {$Spause_avg=0;}
	if ( ($Scalls > 0) and ($Swait_sec > 0) ) {$Swait_avg = ($Swait_sec/$Scalls);}
		else {$Swait_avg=0;}
	if ( ($Scalls > 0) and ($Sdispo_sec > 0) ) {$Sdispo_avg = ($Sdispo_sec/$Scalls);}
		else {$Sdispo_avg=0;}
	if ( ($Scalls > 0) and ($Sdead_sec > 0) ) {$Sdead_avg = ($Sdead_sec/$Scalls);}
		else {$Sdead_avg=0;}
	if ( ($Scalls > 0) and ($Scustomer_sec > 0) ) {$Scustomer_avg = ($Scustomer_sec/$Scalls);}
		else {$Scustomer_avg=0;}

	$RAWuser = $Suser;
	$RAWcalls = $Scalls;
	$Scalls =	sprintf("%6s", $Scalls);
	$Sfull_nameRAW = $Sfull_name;
	$SuserRAW = $Suser;

	if ($non_latin < 1)
		{
		$Sfull_name=	sprintf("%-15s", $Sfull_name); 
		while(strlen($Sfull_name)>15) {$Sfull_name = substr("$Sfull_name", 0, -1);}
		$Suser =		sprintf("%-8s", $Suser);
		while(strlen($Suser)>8) {$Suser = substr("$Suser", 0, -1);}
		}
	else
		{	
		$Sfull_name=	sprintf("%-45s", $Sfull_name); 
		while(mb_strlen($Sfull_name,'utf-8')>15) {$Sfull_name = mb_substr("$Sfull_name", 0, -1,'utf-8');}
		$Suser =	sprintf("%-24s", $Suser);
		while(mb_strlen($Suser,'utf-8')>8) {$Suser = mb_substr("$Suser", 0, -1,'utf-8');}
		}

	$pfUSERtime_MS =		sec_convert($Stime,'H'); 
	$pfUSERtotTALK_MS =		sec_convert($Stalk_sec,'H'); 
	$pfUSERavgTALK_MS =		sec_convert($Stalk_avg,'M'); 
	$USERtotPAUSE_MS =		sec_convert($Spause_sec,'H'); 
	$USERavgPAUSE_MS =		sec_convert($Spause_avg,'M'); 
	$USERtotWAIT_MS =		sec_convert($Swait_sec,'H'); 
	$USERavgWAIT_MS =		sec_convert($Swait_avg,'M'); 
	$USERtotDISPO_MS =		sec_convert($Sdispo_sec,'H'); 
	$USERavgDISPO_MS =		sec_convert($Sdispo_avg,'M'); 
	$USERtotDEAD_MS =		sec_convert($Sdead_sec,'H'); 
	$USERavgDEAD_MS =		sec_convert($Sdead_avg,'M'); 
	$USERtotCUSTOMER_MS =	sec_convert($Scustomer_sec,'H'); 
	$USERavgCUSTOMER_MS =	sec_convert($Scustomer_avg,'M'); 

	$pfUSERtime_MS =		sprintf("%9s", $pfUSERtime_MS);
	$pfUSERtotTALK_MS =		sprintf("%8s", $pfUSERtotTALK_MS);
	$pfUSERavgTALK_MS =		sprintf("%6s", $pfUSERavgTALK_MS);
	$pfUSERtotPAUSE_MS =	sprintf("%8s", $USERtotPAUSE_MS);
	$pfUSERavgPAUSE_MS =	sprintf("%6s", $USERavgPAUSE_MS);
	$pfUSERtotWAIT_MS =		sprintf("%8s", $USERtotWAIT_MS);
	$pfUSERavgWAIT_MS =		sprintf("%6s", $USERavgWAIT_MS);
	$pfUSERtotDISPO_MS =	sprintf("%8s", $USERtotDISPO_MS);
	$pfUSERavgDISPO_MS =	sprintf("%6s", $USERavgDISPO_MS);
	$pfUSERtotDEAD_MS =		sprintf("%8s", $USERtotDEAD_MS);
	$pfUSERavgDEAD_MS =		sprintf("%6s", $USERavgDEAD_MS);
	$pfUSERtotCUSTOMER_MS =	sprintf("%8s", $USERtotCUSTOMER_MS);
	$pfUSERavgCUSTOMER_MS =	sprintf("%6s", $USERavgCUSTOMER_MS);
	$PAUSEtotal[$m] = $pfUSERtotPAUSE_MS;

	$Toutput = "| $Sfull_name | <a href=\"./user_stats.php?user=$RAWuser\">$Suser</a> | $Scalls | $pfUSERtime_MS | $pfUSERtotPAUSE_MS | $pfUSERavgPAUSE_MS | $pfUSERtotWAIT_MS | $pfUSERavgWAIT_MS | $pfUSERtotTALK_MS | $pfUSERavgTALK_MS | $pfUSERtotDISPO_MS | $pfUSERavgDISPO_MS | $pfUSERtotDEAD_MS | $pfUSERavgDEAD_MS | $pfUSERtotCUSTOMER_MS | $pfUSERavgCUSTOMER_MS |$SstatusesHTML\n";


	$CSV_lines.="\"$Sfull_nameRAW\",";
	$CSV_lines.=eregi_replace(" ", "", "\"$SuserRAW\",\"$Scalls\",\"$pfUSERtime_MS\",\"$pfUSERtotPAUSE_MS\",\"$pfUSERavgPAUSE_MS\",\"$pfUSERtotWAIT_MS\",\"$pfUSERavgWAIT_MS\",\"$pfUSERtotTALK_MS\",\"$pfUSERavgTALK_MS\",\"$pfUSERtotDISPO_MS\",\"$pfUSERavgDISPO_MS\",\"$pfUSERtotDEAD_MS\",\"$pfUSERavgDEAD_MS\",\"$pfUSERtotCUSTOMER_MS\",\"$pfUSERavgCUSTOMER_MS\"$CSVstatuses\n");

	$TOPsorted_output[$m] = $Toutput;

	if ($stage == 'ID')
		{$TOPsort[$m] =	'' . sprintf("%08s", $RAWuser) . '-----' . $m . '-----' . sprintf("%020s", $RAWuser);}
	if ($stage == 'LEADS')
		{$TOPsort[$m] =	'' . sprintf("%08s", $RAWcalls) . '-----' . $m . '-----' . sprintf("%020s", $RAWuser);}
	if ($stage == 'TIME')
		{$TOPsort[$m] =	'' . sprintf("%08s", $Stime) . '-----' . $m . '-----' . sprintf("%020s", $RAWuser);}
	if (!ereg("ID|TIME|LEADS",$stage))
		{$HTML_text.="$Toutput";}

	$m++;
	}
### END loop through each user ###



### BEGIN sort through output to display properly ###
if (ereg("ID|TIME|LEADS",$stage))
	{
	if (ereg("ID",$stage))
		{sort($TOPsort, SORT_NUMERIC);}
	if (ereg("TIME|LEADS",$stage))
		{rsort($TOPsort, SORT_NUMERIC);}

	$m=0;
	while ($m < $k)
		{
		$sort_split = explode("-----",$TOPsort[$m]);
		$i = $sort_split[1];
		$sort_order[$m] = "$i";
		$HTML_text.="$TOPsorted_output[$i]";
		$m++;
		}
	}
### END sort through output to display properly ###



###### LAST LINE FORMATTING ##########
### BEGIN loop through each status ###
$SUMstatusesHTML='';
$CSVSUMstatuses='';
$n=0;
while ($n < $j)
	{
	$Scalls=0;
	$Sstatus=$statusesARY[$n];
	$SUMstatusTXT='';
	### BEGIN loop through each stat line ###
	$i=0; $status_found=0;
	while ($i < $rows_to_print)
		{
		if ($Sstatus=="$status[$i]")
			{
			$Scalls =		($Scalls + $calls[$i]);
			$status_found++;
			}
		$i++;
		}
	### END loop through each stat line ###
	if ($status_found < 1)
		{
		$SUMstatusesHTML .= "        0 |";
		}
	else
		{
		$SUMstatusTXT = sprintf("%8s", $Scalls);
		$SUMstatusesHTML .= " $SUMstatusTXT |";
		$CSVSUMstatuses.=",\"$Scalls\"";
		}
	$n++;
	}
### END loop through each status ###

$TOTcalls =	sprintf("%7s", $TOTcalls);
$TOT_AGENTS = sprintf("%-4s", $m);

if ($TOTtotTALK < 1) {$TOTavgTALK = '0';}
else {$TOTavgTALK = ($TOTtotTALK / $TOTcalls);}
if ($TOTtotDISPO < 1) {$TOTavgDISPO = '0';}
else {$TOTavgDISPO = ($TOTtotDISPO / $TOTcalls);}
if ($TOTtotDEAD < 1) {$TOTavgDEAD = '0';}
else {$TOTavgDEAD = ($TOTtotDEAD / $TOTcalls);}
if ($TOTtotPAUSE < 1) {$TOTavgPAUSE = '0';}
else {$TOTavgPAUSE = ($TOTtotPAUSE / $TOTcalls);}
if ($TOTtotWAIT < 1) {$TOTavgWAIT = '0';}
else {$TOTavgWAIT = ($TOTtotWAIT / $TOTcalls);}
if ($TOTtotCUSTOMER < 1) {$TOTavgCUSTOMER = '0';}
else {$TOTavgCUSTOMER = ($TOTtotCUSTOMER / $TOTcalls);}

$TOTtime_MS =		sec_convert($TOTtime,'H'); 
$TOTtotTALK_MS =	sec_convert($TOTtotTALK,'H'); 
$TOTtotDISPO_MS =	sec_convert($TOTtotDISPO,'H'); 
$TOTtotDEAD_MS =	sec_convert($TOTtotDEAD,'H'); 
$TOTtotPAUSE_MS =	sec_convert($TOTtotPAUSE,'H'); 
$TOTtotWAIT_MS =	sec_convert($TOTtotWAIT,'H'); 
$TOTtotCUSTOMER_MS =	sec_convert($TOTtotCUSTOMER,'H'); 
$TOTavgTALK_MS =	sec_convert($TOTavgTALK,'M'); 
$TOTavgDISPO_MS =	sec_convert($TOTavgDISPO,'H'); 
$TOTavgDEAD_MS =	sec_convert($TOTavgDEAD,'H'); 
$TOTavgPAUSE_MS =	sec_convert($TOTavgPAUSE,'H'); 
$TOTavgWAIT_MS =	sec_convert($TOTavgWAIT,'H'); 
$TOTavgCUSTOMER_MS =	sec_convert($TOTavgCUSTOMER,'H'); 

$TOTtime_MS =		sprintf("%10s", $TOTtime_MS);
$TOTtotTALK_MS =	sprintf("%10s", $TOTtotTALK_MS);
$TOTtotDISPO_MS =	sprintf("%10s", $TOTtotDISPO_MS);
$TOTtotDEAD_MS =	sprintf("%10s", $TOTtotDEAD_MS);
$TOTtotPAUSE_MS =	sprintf("%10s", $TOTtotPAUSE_MS);
$TOTtotWAIT_MS =	sprintf("%10s", $TOTtotWAIT_MS);
$TOTtotCUSTOMER_MS =	sprintf("%10s", $TOTtotCUSTOMER_MS);
$TOTavgTALK_MS =	sprintf("%6s", $TOTavgTALK_MS);
$TOTavgDISPO_MS =	sprintf("%6s", $TOTavgDISPO_MS);
$TOTavgDEAD_MS =	sprintf("%6s", $TOTavgDEAD_MS);
$TOTavgPAUSE_MS =	sprintf("%6s", $TOTavgPAUSE_MS);
$TOTavgWAIT_MS =	sprintf("%6s", $TOTavgWAIT_MS);
$TOTavgCUSTOMER_MS =	sprintf("%6s", $TOTavgCUSTOMER_MS);

while(strlen($TOTtime_MS)>10) {$TOTtime_MS = substr("$TOTtime_MS", 0, -1);}
while(strlen($TOTtotTALK_MS)>10) {$TOTtotTALK_MS = substr("$TOTtotTALK_MS", 0, -1);}
while(strlen($TOTtotDISPO_MS)>10) {$TOTtotDISPO_MS = substr("$TOTtotDISPO_MS", 0, -1);}
while(strlen($TOTtotDEAD_MS)>10) {$TOTtotDEAD_MS = substr("$TOTtotDEAD_MS", 0, -1);}
while(strlen($TOTtotPAUSE_MS)>10) {$TOTtotPAUSE_MS = substr("$TOTtotPAUSE_MS", 0, -1);}
while(strlen($TOTtotWAIT_MS)>10) {$TOTtotWAIT_MS = substr("$TOTtotWAIT_MS", 0, -1);}
while(strlen($TOTtotCUSTOMER_MS)>10) {$TOTtotCUSTOMER_MS = substr("$TOTtotCUSTOMER_MS", 0, -1);}
while(strlen($TOTavgTALK_MS)>6) {$TOTavgTALK_MS = substr("$TOTavgTALK_MS", 0, -1);}
while(strlen($TOTavgDISPO_MS)>6) {$TOTavgDISPO_MS = substr("$TOTavgDISPO_MS", 0, -1);}
while(strlen($TOTavgDEAD_MS)>6) {$TOTavgDEAD_MS = substr("$TOTavgDEAD_MS", 0, -1);}
while(strlen($TOTavgPAUSE_MS)>6) {$TOTavgPAUSE_MS = substr("$TOTavgPAUSE_MS", 0, -1);}
while(strlen($TOTavgWAIT_MS)>6) {$TOTavgWAIT_MS = substr("$TOTavgWAIT_MS", 0, -1);}
while(strlen($TOTavgCUSTOMER_MS)>6) {$TOTavgCUSTOMER_MS = substr("$TOTavgCUSTOMER_MS", 0, -1);}


$HTML_text.="+-----------------+----------+--------+-----------+----------+--------+----------+--------+----------+--------+----------+--------+----------+--------+----------+--------+$statusesHEAD\n";
$HTML_text.="|  TOTALS        AGENTS:$TOT_AGENTS | $TOTcalls| $TOTtime_MS|$TOTtotPAUSE_MS| $TOTavgPAUSE_MS |$TOTtotWAIT_MS| $TOTavgWAIT_MS |$TOTtotTALK_MS| $TOTavgTALK_MS |$TOTtotDISPO_MS| $TOTavgDISPO_MS |$TOTtotDEAD_MS| $TOTavgDEAD_MS |$TOTtotCUSTOMER_MS| $TOTavgCUSTOMER_MS |$SUMstatusesHTML\n";
$HTML_text.="+-----------------+----------+--------+-----------+----------+--------+----------+--------+----------+--------+----------+--------+----------+--------+----------+--------+$statusesHEAD\n";

$CSV_total=eregi_replace(" ", "", "\"TOTALS\",\"AGENTS:$TOT_AGENTS\",\"$TOTcalls\",\"$TOTtime_MS\",\"$TOTtotPAUSE_MS\",\"$TOTavgPAUSE_MS\",\"$TOTtotWAIT_MS\",\"$TOTavgWAIT_MS\",\"$TOTtotTALK_MS\",\"$TOTavgTALK_MS\",\"$TOTtotDISPO_MS\",\"$TOTavgDISPO_MS\",\"$TOTtotDEAD_MS\",\"$TOTavgDEAD_MS\",\"$TOTtotCUSTOMER_MS\",\"$TOTavgCUSTOMER_MS\"$CSVSUMstatuses");

if ($file_download == 1)
	{
	$FILE_TIME = date("Ymd-His");
	$CSVfilename = "AGENT_PERFORMACE_DETAIL$US$FILE_TIME.csv";

	// We'll be outputting a TXT file
	header('Content-type: application/octet-stream');

	// It will be called LIST_101_20090209-121212.txt
	header("Content-Disposition: attachment; filename=\"$CSVfilename\"");
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	ob_clean();
	flush();

	echo "$CSV_header$CSV_lines$CSV_total";

	exit;
	}

$CSV_report=fopen("AST_agent_performance_detail.csv", "w");
fwrite($CSV_report, $CSV_header);
fwrite($CSV_report, $CSV_lines);
fwrite($CSV_report, $CSV_total);


$HTML_text.="\n\n";















$sub_statuses='-';
$sub_statusesTXT='';
$sub_statusesHEAD='';
$sub_statusesHTML='';
$CSV_statuses='';
$sub_statusesARY=$MT;
$j=0;
$PCusers='-';
$PCusersARY=$MT;
$PCuser_namesARY=$MT;
$k=0;
$stmt="select full_name,vicidial_users.user,sum(pause_sec),sub_status,sum(wait_sec + talk_sec + dispo_sec) from vicidial_users,vicidial_agent_log where event_time <= '$query_date_END' and event_time >= '$query_date_BEGIN' and vicidial_users.user=vicidial_agent_log.user and pause_sec<65000 $group_SQL $user_group_SQL group by user,full_name,sub_status order by user,full_name,sub_status desc limit 100000;";
$rslt=mysql_query($stmt, $link);
if ($DB) {$HTML_text.="$stmt\n";}
$subs_to_print = mysql_num_rows($rslt);
$i=0;
while ($i < $subs_to_print)
	{
	$row=mysql_fetch_row($rslt);
	$PCfull_name[$i] =	$row[0];
	$PCuser[$i] =		$row[1];
	$PCpause_sec[$i] =	$row[2];
	$sub_status[$i] =	$row[3];
	$PCnon_pause_sec[$i] =	$row[4];

#	echo "$sub_status[$i]|$PCpause_sec[$i]\n";
#	if ( (!eregi("-$sub_status[$i]-", $sub_statuses)) and (strlen($sub_status[$i])>0) )
	if (!eregi("-$sub_status[$i]-", $sub_statuses))
		{
		$sub_statusesTXT = sprintf("%8s", $sub_status[$i]);
		$sub_statusesHEAD .= "----------+";
		$sub_statusesHTML .= " $sub_statusesTXT |";
		$sub_statuses .= "$sub_status[$i]-";
		$sub_statusesARY[$j] = $sub_status[$i];
		$CSV_statuses.=",\"$sub_status[$i]\"";
		$j++;
		}
	if (!eregi("-$PCuser[$i]-", $PCusers))
		{
		$PCusers .= "$PCuser[$i]-";
		$PCusersARY[$k] = $PCuser[$i];
		$PCuser_namesARY[$k] = $PCfull_name[$i];
		$k++;
		}

	$i++;
	}

$HTML_text.="PAUSE CODE BREAKDOWN:     <a href=\"$LINKbase&stage=$stage&file_download=2\">[DOWNLOAD]</a>\n\n";
$HTML_text.="+-----------------+----------+----------+----------+----------+  +$sub_statusesHEAD\n";
$HTML_text.="| USER NAME       | ID       | TOTAL    | NONPAUSE | PAUSE    |  |$sub_statusesHTML\n";
$HTML_text.="+-----------------+----------+----------+----------+----------+  +$sub_statusesHEAD\n";

$CSV_header="\"Agent Performance Detail                        $NOW_TIME\"\n";
$CSV_header.="\"Time range: $query_date_BEGIN to $query_date_END\"\n\n";
$CSV_header.="\"PAUSE CODE BREAKDOWN:\"\n";
$CSV_header.="\"USER NAME\",\"ID\",\"TOTAL\",\"NONPAUSE\",\"PAUSE\",$CSV_statuses\n";

### BEGIN loop through each user ###
$m=0;
$Suser_ct = count($usersARY);
$TOTtotNONPAUSE = 0;
$TOTtotTOTAL = 0;
$CSV_lines="";

while ($m < $k)
	{
	$d=0;
	while ($d < $Suser_ct)
		{
		if ($usersARY[$d] === "$PCusersARY[$m]")
			{$pcPAUSEtotal = $PAUSEtotal[$d];}
		$d++;
		}
	$Suser=$PCusersARY[$m];
	$Sfull_name=$PCuser_namesARY[$m];
	$Spause_sec=0;
	$Snon_pause_sec=0;
	$Stotal_sec=0;
	$SstatusesHTML='';
	$CSV_statuses="";

	### BEGIN loop through each status ###
	$n=0;
	while ($n < $j)
		{
		$Sstatus=$sub_statusesARY[$n];
		$SstatusTXT='';
		### BEGIN loop through each stat line ###
		$i=0; $status_found=0;
		while ($i < $subs_to_print)
			{
			if ( ($Suser=="$PCuser[$i]") and ($Sstatus=="$sub_status[$i]") )
				{
				$Spause_sec =	($Spause_sec + $PCpause_sec[$i]);
				$Snon_pause_sec =	($Snon_pause_sec + $PCnon_pause_sec[$i]);
				$Stotal_sec =	($Stotal_sec + $PCnon_pause_sec[$i] + $PCpause_sec[$i]);

				$USERcodePAUSE_MS =		sec_convert($PCpause_sec[$i],'H'); 
				$pfUSERcodePAUSE_MS =	sprintf("%6s", $USERcodePAUSE_MS);

				$SstatusTXT = sprintf("%8s", $pfUSERcodePAUSE_MS);
				$SstatusesHTML .= " $SstatusTXT |";
				$CSV_statuses.=",\"$USERcodePAUSE_MS\"";
				$status_found++;
				}
			$i++;
			}
		if ($status_found < 1)
			{
			$SstatusesHTML .= "        0 |";
			$CSV_statuses.=",\"0\"";
			}
		### END loop through each stat line ###
		$n++;
		}
	### END loop through each status ###
	$TOTtotPAUSE=($TOTtotPAUSE + $Spause_sec);
	$Sfull_nameRAW = $Sfull_name;
	$SuserRAW = $Suser;

	if ($non_latin < 1)
		{
		$Sfull_name=	sprintf("%-15s", $Sfull_name); 
		while(strlen($Sfull_name)>15) {$Sfull_name = substr("$Sfull_name", 0, -1);}
		$Suser =		sprintf("%-8s", $Suser);
		while(strlen($Suser)>8) {$Suser = substr("$Suser", 0, -1);}
		}
	else
		{
		$Sfull_name=	sprintf("%-45s", $Sfull_name); 
		while(mb_strlen($Sfull_name,'utf-8')>15) {$Sfull_name = mb_substr("$Sfull_name", 0, -1,'utf-8');}
		$Suser =	sprintf("%-24s", $Suser);
		while(mb_strlen($Suser,'utf-8')>8) {$Suser = mb_substr("$Suser", 0, -1,'utf-8');}
		}

	$TOTtotNONPAUSE = ($TOTtotNONPAUSE + $Snon_pause_sec);
	$TOTtotTOTAL = ($TOTtotTOTAL + $Stotal_sec);

	$USERtotPAUSE_MS =		sec_convert($Spause_sec,'H'); 
	$USERtotNONPAUSE_MS =	sec_convert($Snon_pause_sec,'H'); 
	$USERtotTOTAL_MS =		sec_convert($Stotal_sec,'H'); 

	$pfUSERtotPAUSE_MS =		sprintf("%8s", $USERtotPAUSE_MS);
	$pfUSERtotNONPAUSE_MS =		sprintf("%8s", $USERtotNONPAUSE_MS);
	$pfUSERtotTOTAL_MS =		sprintf("%8s", $USERtotTOTAL_MS);

	$BOTTOMoutput = "| $Sfull_name | $Suser | $pfUSERtotTOTAL_MS | $pfUSERtotNONPAUSE_MS | $pfUSERtotPAUSE_MS |  |$SstatusesHTML\n";

	$BOTTOMsorted_output[$m] = $BOTTOMoutput;

	$HTML_text.="$BOTTOMoutput";
	$CSV_lines.="\"$Sfull_nameRAW\"".eregi_replace(" ", "", ",\"$SuserRAW\",\"$pfUSERtotTOTAL_MS\",\"$pfUSERtotNONPAUSE_MS\",\"$pfUSERtotPAUSE_MS\",$CSV_statuses\n");
	$m++;
	}
### END loop through each user ###



### BEGIN sort through output to display properly ###
#if (ereg("ID|TIME|LEADS",$stage))
#	{
#	$n=0;
#	while ($n <= $m)
#		{
#		$i = $sort_order[$m];
#		echo "$BOTTOMsorted_output[$i]";
#		$m--;
#		}
#	}
### END sort through output to display properly ###



###### LAST LINE FORMATTING ##########
### BEGIN loop through each status ###
$SUMstatusesHTML='';
$CSVSUMstatuses='';
$TOTtotPAUSE=0;
$n=0;
while ($n < $j)
	{
	$Scalls=0;
	$Sstatus=$sub_statusesARY[$n];
	$SUMstatusTXT='';
	### BEGIN loop through each stat line ###
	$i=0; $status_found=0;
	while ($i < $subs_to_print)
		{
		if ($Sstatus=="$sub_status[$i]")
			{
			$Scalls =		($Scalls + $PCpause_sec[$i]);
			$status_found++;
			}
		$i++;
		}
	### END loop through each stat line ###
	if ($status_found < 1)
		{
		$SUMstatusesHTML .= "        0 |";
		}
	else
		{
		$TOTtotPAUSE = ($TOTtotPAUSE + $Scalls);

		$USERsumstatPAUSE_MS =		sec_convert($Scalls,'H'); 
		$pfUSERsumstatPAUSE_MS =	sprintf("%8s", $USERsumstatPAUSE_MS);

		$SUMstatusTXT = sprintf("%8s", $pfUSERsumstatPAUSE_MS);
		$SUMstatusesHTML .= " $SUMstatusTXT |";
		$CSVSUMstatuses.=",\"$USERsumstatPAUSE_MS\"";
		}
	$n++;
	}
### END loop through each status ###

	$TOT_AGENTS = sprintf("%-4s", $m);

	$TOTtotPAUSE_MS =		sec_convert($TOTtotPAUSE,'H'); 
	$TOTtotNONPAUSE_MS =	sec_convert($TOTtotNONPAUSE,'H'); 
	$TOTtotTOTAL_MS =		sec_convert($TOTtotTOTAL,'H'); 

	$TOTtotPAUSE_MS =		sprintf("%10s", $TOTtotPAUSE_MS);
	$TOTtotNONPAUSE_MS =	sprintf("%10s", $TOTtotNONPAUSE_MS);
	$TOTtotTOTAL_MS =		sprintf("%10s", $TOTtotTOTAL_MS);

	while(strlen($TOTtotPAUSE_MS)>10) {$TOTtotPAUSE_MS = substr("$TOTtotPAUSE_MS", 0, -1);}
	while(strlen($TOTtotNONPAUSE_MS)>10) {$TOTtotNONPAUSE_MS = substr("$TOTtotNONPAUSE_MS", 0, -1);}
	while(strlen($TOTtotTOTAL_MS)>10) {$TOTtotTOTAL_MS = substr("$TOTtotTOTAL_MS", 0, -1);}


$HTML_text.="+-----------------+----------+----------+----------+----------+  +$sub_statusesHEAD\n";
$HTML_text.="|  TOTALS        AGENTS:$TOT_AGENTS |$TOTtotTOTAL_MS|$TOTtotNONPAUSE_MS|$TOTtotPAUSE_MS|  |$SUMstatusesHTML\n";
$HTML_text.="+----------------------------+----------+----------+----------+  +$sub_statusesHEAD\n";

$CSV_total=eregi_replace(" ", "", "\"TOTALS\",\"AGENTS:$TOT_AGENTS\",\"$TOTtotTOTAL_MS\",\"$TOTtotNONPAUSE_MS\",\"$TOTtotPAUSE_MS\",$CSVSUMstatuses");

if ($file_download == 2)
	{
	$FILE_TIME = date("Ymd-His");
	$CSVfilename = "AST_PAUSE_CODE_BREAKDOWN$US$FILE_TIME.csv";

	// We'll be outputting a TXT file
	header('Content-type: application/octet-stream');

	// It will be called LIST_101_20090209-121212.txt
	header("Content-Disposition: attachment; filename=\"$CSVfilename\"");
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	ob_clean();
	flush();

	echo "$CSV_header$CSV_lines$CSV_total";

	exit;
	}
$CSV_report=fopen("AST_pause_code_breakdown.csv", "w");
fwrite($CSV_report, $CSV_header);
fwrite($CSV_report, $CSV_lines);
fwrite($CSV_report, $CSV_total);


$HTML_text.="\n\n<BR>$db_source";
$HTML_text.="</TD></TR></TABLE>";

$HTML_text.="</BODY></HTML>";


}
if ($file_download == 0 || !$file_download) {
	echo $HTML_head;
	require("admin_header.php");
	echo $HTML_text;
}


?>

