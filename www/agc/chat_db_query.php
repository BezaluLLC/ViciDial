<?php
# chat_db_query.php
#
# Copyright (C) 2016  Joe Johnson, Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# Called by vdc_chat_display.php and vicidial_chat_agent.js.  This contains all actions taken by the
# agent's interface when chatting with customers, other agents, and managers, through 
# vdc_chat_display.php and vicidial_chat_agent.js.  Sample actions include creating and ending 
# agent-to-agent chats, refreshing the chat window, and sending messages to the database to be 
# displayed.
#
# Builds:
# 150901-2348 - First build
# 151218-1052 - Added missing translation code and user auth
# 151231-0841 - Added agent_allowed_chat_groups setting
# 160107-2318 - Bug fix for agent ending chat with manager
# 160108-2300 - Changed some mysqli_query to mysql_to_mysqli for consistency
#

require("dbconnect_mysqli.php");
require("functions.php");

$NOW_TIME = date("Y-m-d H:i:s");
$color_array=array("#FF0000", "#0000FF", "#009900", "#990099", "#009999", "#666600", "#999999");
$style_array=array("", "italics", "bold italics");

if (isset($_GET["action"]))	{$action=$_GET["action"];}
	elseif (isset($_POST["action"]))	{$action=$_POST["action"];}
if (isset($_GET["DB"]))				{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))	{$DB=$_POST["DB"];}
if (isset($_GET["chat_id"]))	{$chat_id=$_GET["chat_id"];}
	elseif (isset($_POST["chat_id"]))	{$chat_id=$_POST["chat_id"];}
if (isset($_GET["chat_group_id"]))	{$chat_group_id=$_GET["chat_group_id"];}
	elseif (isset($_POST["chat_group_id"]))	{$chat_group_id=$_POST["chat_group_id"];}
if (isset($_GET["chat_level"]))	{$chat_level=$_GET["chat_level"];}
	elseif (isset($_POST["chat_level"]))	{$chat_level=$_POST["chat_level"];}
if (isset($_GET["chat_creator"]))	{$chat_creator=$_GET["chat_creator"];}
	elseif (isset($_POST["chat_creator"]))	{$chat_creator=$_POST["chat_creator"];}
if (isset($_GET["chat_member_name"]))	{$chat_member_name=$_GET["chat_member_name"];}
	elseif (isset($_POST["chat_member_name"]))	{$chat_member_name=$_POST["chat_member_name"];}
if (isset($_GET["chat_message"]))	{$chat_message=$_GET["chat_message"];}
	elseif (isset($_POST["chat_message"]))	{$chat_message=$_POST["chat_message"];}
if (isset($_GET["email"]))	{$email=$_GET["email"];}
	elseif (isset($_POST["email"]))	{$email=$_POST["email"];}
if (isset($_GET["lead_id"]))	{$lead_id=$_GET["lead_id"];}
	elseif (isset($_POST["lead_id"]))	{$lead_id=$_POST["lead_id"];}
if (isset($_GET["user"]))	{$user=$_GET["user"];}
	elseif (isset($_POST["user"]))	{$user=$_POST["user"];}
if (isset($_GET["server_ip"]))	{$server_ip=$_GET["server_ip"];}
	elseif (isset($_POST["server_ip"]))	{$server_ip=$_POST["server_ip"];}
if (isset($_GET["user_level"]))	{$user_level=$_GET["user_level"];}
	elseif (isset($_POST["user_level"]))	{$user_level=$_POST["user_level"];}
if (isset($_GET["pass"]))	{$pass=$_GET["pass"];}
	elseif (isset($_POST["pass"]))	{$pass=$_POST["pass"];}
if (isset($_GET["first_name"]))					{$first_name=$_GET["first_name"];}
	elseif (isset($_POST["first_name"]))			{$first_name=$_POST["first_name"];}
if (isset($_GET["last_name"]))					{$last_name=$_GET["last_name"];}
	elseif (isset($_POST["last_name"]))			{$last_name=$_POST["last_name"];}
if (isset($_GET["group_id"]))					{$group_id=$_GET["group_id"];}
	elseif (isset($_POST["group_id"]))			{$group_id=$_POST["group_id"];}
if (isset($_GET["keepalive"]))					{$keepalive=$_GET["keepalive"];}
	elseif (isset($_POST["keepalive"]))			{$keepalive=$_POST["keepalive"];}
if (isset($_GET["current_message_count"]))					{$current_message_count=$_GET["current_message_count"];}
	elseif (isset($_POST["current_message_count"]))			{$current_message_count=$_POST["current_message_count"];}

if (isset($_GET["manager_chat_id"]))	{$manager_chat_id=$_GET["manager_chat_id"];}
	elseif (isset($_POST["manager_chat_id"]))	{$manager_chat_id=$_POST["manager_chat_id"];}
if (isset($_GET["manager_chat_subid"]))	{$manager_chat_subid=$_GET["manager_chat_subid"];}
	elseif (isset($_POST["manager_chat_subid"]))	{$manager_chat_subid=$_POST["manager_chat_subid"];}
if (isset($_GET["agent_manager"]))					{$agent_manager=$_GET["agent_manager"];}
	elseif (isset($_POST["agent_manager"]))			{$agent_manager=$_POST["agent_manager"];}
if (isset($_GET["agent_user"]))					{$agent_user=$_GET["agent_user"];}
	elseif (isset($_POST["agent_user"]))			{$agent_user=$_POST["agent_user"];}
if (isset($_GET["agent_override"]))					{$agent_override=$_GET["agent_override"];}
	elseif (isset($_POST["agent_override"]))			{$agent_override=$_POST["agent_override"];}
if (isset($_GET["hangup_override"]))					{$hangup_override=$_GET["hangup_override"];}
	elseif (isset($_POST["hangup_override"]))			{$hangup_override=$_POST["hangup_override"];}
if (isset($_GET["manager_message"]))					{$manager_message=$_GET["manager_message"];}
	elseif (isset($_POST["manager_message"]))			{$manager_message=$_POST["manager_message"];}
if (isset($_GET["ChatReloadIDNumber"]))					{$ChatReloadIDNumber=$_GET["ChatReloadIDNumber"];}
	elseif (isset($_POST["ChatReloadIDNumber"]))			{$ChatReloadIDNumber=$_POST["ChatReloadIDNumber"];}

$chat_member_name = preg_replace('/[^- \.\,\_0-9a-zA-Z]/',"",$chat_member_name);
if (!$user) {echo "No user, no using."; exit;}

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$VUselected_language = '';
$stmt = "SELECT use_non_latin,enable_languages,language_method,default_language,allow_chats FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
        if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'00XXX',$user,$server_ip,$session_name,$one_mysql_log);}
if ($DB) {echo "$stmt\n";}
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =			$row[0];
	$SSenable_languages =	$row[1];
	$SSlanguage_method =	$row[2];
	$SSdefault_language =	$row[3];
	$SSallow_chats =		$row[4];
	}
$VUselected_language = $SSdefault_language;
##### END SETTINGS LOOKUP #####
###########################################

$auth=0;
$auth_message = user_authorization($user,$pass,'',0,0,0);
if ($auth_message == 'GOOD')
	{$auth=1;}

if( (strlen($user)<2) or (strlen($pass)<2) or ($auth==0))
	{
	echo _QXZ("Invalid Username/Password:")." |$user|$pass|$auth_message|chat_db_query|\n";
	exit;
	}

$user_stmt="select full_name,user_level,selected_language from vicidial_users where user='$user'";
$user_level=0;
$user_rslt=mysql_to_mysqli($user_stmt, $link);
if (mysqli_num_rows($user_rslt)>0) 
	{
	$user_row=mysqli_fetch_row($user_rslt);
	$full_name =			$user_row[0];
	$user_level =			$user_row[1];
	$VUselected_language =	$user_row[2];
	}
if ($SSallow_chats < 1)
	{
	header ("Content-type: text/html; charset=utf-8");
	echo _QXZ("Error, chat disabled on this system");
	exit;
	}


###### AGENT/MANAGER CHAT FUNCTIONS #######
if ($action=="CreateAgentToAgentChat" && $agent_manager && $agent_user && $manager_message) {

	# Check that you aren't already chatting with the agent - also checks for chats where the other agent is the manager and you are the requested chat recipient
	$dupe_chat_stmt="select * from vicidial_manager_chats where (manager='$agent_manager' and selected_agents like '%|$agent_user|%') or (manager='$agent_user' and selected_agents like '%|$agent_manager|%')";
	$dupe_chat_rslt=mysql_to_mysqli($dupe_chat_stmt, $link);
	if (mysqli_num_rows($dupe_chat_rslt)>0) {
		echo _QXZ("Error:  You already have an open chat with this agent");
	} else {

		# This is slightly different from the manager-to-agent chat because it's only one agent you're chatting to so for reporting all that agent's specific information is grabbed and used to make the vicidial_manager_chats entry.  Since the query should only return one result there's no while loop to insert subid chat info.

		$stmt="select vicidial_live_agents.user, vicidial_users.full_name, vicidial_users.user_group, vicidial_live_agents.campaign_id from vicidial_live_agents, vicidial_users where vicidial_live_agents.user=vicidial_users.user and vicidial_live_agents.user='$agent_user' and vicidial_users.user!='$agent_manager' order by vicidial_users.full_name asc";
		$rslt=mysql_to_mysqli($stmt, $link);
		if (mysqli_num_rows($rslt)>0) {
			$row=mysqli_fetch_row($rslt);
			$user =			$row[0];
			$user_group =	$row[2];
			$campaign_id =	$row[3];

			$ins_stmt="insert into vicidial_manager_chats(chat_start_date, manager, selected_agents, selected_user_groups, selected_campaigns, allow_replies) VALUES(now(), '$agent_manager', '|$agent_user|', '|$user_group|', '|$campaign_id|', 'Y')";
			$ins_rslt=mysql_to_mysqli($ins_stmt, $link);
			$manager_chat_id=mysqli_insert_id($link);

			$subid=1;

			# $manager_message = preg_replace('/;/i','',$manager_message);
			$manager_message = preg_replace("/\r/i",'',$manager_message);
			$manager_message = preg_replace("/\n/i",' ',$manager_message);
			# $manager_message=addslashes(trim("$manager_message"));
						
			$ins_chat_stmt="insert into vicidial_manager_chat_log(manager_chat_id, manager_chat_subid, manager, user, message, message_date, message_posted_by) VALUES('$manager_chat_id', '$subid', '$agent_manager', '$agent_user', '".mysqli_real_escape_string($link, $manager_message)."', now(), '$agent_manager')";
			$ins_chat_rslt=mysql_to_mysqli($ins_chat_stmt, $link);
			echo "$manager_chat_id|$subid";
		}
	}
}

if ($action=="DisplayMgrAgentChat" && $manager_chat_id && $manager_chat_subid && $user) {
	$stmt="select vm.message_posted_by, vm.message, vm.message_date, vu.full_name, vm.manager, vm.manager_chat_subid, vmc.chat_start_date from vicidial_manager_chats vmc, vicidial_manager_chat_log vm, vicidial_users vu where vmc.manager_chat_id='$manager_chat_id' and vmc.manager_chat_id=vm.manager_chat_id and vm.manager_chat_subid='$manager_chat_subid' and vm.user=vu.user order by vm.manager_chat_subid asc, message_date desc";
	$rslt=mysql_to_mysqli($stmt, $link);
	if ($debug==1) {echo "$stmt<BR>\n";}

	# Mark messages as viewed by user (not manager, even if manager is agent at the time)
	$upd_stmt="update vicidial_manager_chat_log set message_viewed_date=now() where message_viewed_date is null and manager_chat_id='$manager_chat_id' and manager_chat_subid='$manager_chat_subid' and user='$user'";
	$upd_rslt=mysql_to_mysqli($upd_stmt, $link);
	$new_messages=mysqli_affected_rows($link);

	if (mysqli_num_rows($rslt)>0) {
		$prev_chat_subid="";
		$backlog_limit=20;
		$chat_output_text="";
		while($row=mysqli_fetch_row($rslt)) {
			if (!$chat_start_date) {$chat_start_date=$row[6];}
			if (!$manager) {$manager=$row[4];}
			if ($backlog_limit>0) {
				# Current agent is always the blue text
				if ($row[0]!=$user) {$fc="#990000";} else {$fc="#000099";}
				$chat_output_text="<font color='$fc' FACE=\"ARIAL,HELVETICA\" size='1'>$row[1]</font><BR>".$chat_output_text; 
				$backlog_limit--;
			}
		}

		$reply_stmt="select allow_replies from vicidial_manager_chats where manager_chat_id='$manager_chat_id'";
		$reply_rslt=mysql_to_mysqli($reply_stmt, $link);
		$reply_row=mysqli_fetch_row($reply_rslt);
		$allow_replies=$reply_row[0];

		if ($manager==$user) {
			$mgr_stmt="select distinct full_name from vicidial_users vu, vicidial_manager_chat_log vc where manager_chat_id='$manager_chat_id' and manager_chat_subid='$manager_chat_subid' and vc.user=vu.user";
			$mgr_rslt=mysql_to_mysqli($mgr_stmt, $link);
			if (mysqli_num_rows($mgr_rslt)!=1) {
				$display_name="ERROR - $mgr_stmt";
			} else {
				$mgr_row=mysqli_fetch_row($mgr_rslt);
				$display_name=$mgr_row[0];			
			}
		} else {
			$mgr_stmt="select full_name from vicidial_users where user='$manager'";
			$mgr_rslt=mysql_to_mysqli($mgr_stmt, $link);
			$mgr_row=mysqli_fetch_row($mgr_rslt);
			$display_name=$mgr_row[0];
		}

		echo $allow_replies."\n".$chat_output_text."\n".$chat_start_date."\n".$display_name."\n".$new_messages;
	} else {
		echo "N\n"._QXZ("CHAT ENDED")."\n\n";
	}
}

if ($action=="EndAgentToAgentChat" && $manager_chat_id && $user) {
	$subid_lookup="select distinct manager_chat_subid from vicidial_manager_chat_log where manager_chat_id='$manager_chat_id' and user='$user'";
	$subid_rslt=mysql_to_mysqli($subid_lookup, $link);
	# should only be one, but making array just in case
	$manager_chat_subid_array=array();
	while ($subid_row=mysqli_fetch_row($subid_rslt)) {
		$manager_chat_subid=$subid_row[0];
		array_push($manager_chat_subid_array, "$subid_row[0]");
	}


	$archive_stmt="insert ignore into vicidial_manager_chat_log_archive select * from vicidial_manager_chat_log where manager_chat_id='$manager_chat_id' and manager_chat_subid in ('".implode("','",$manager_chat_subid_array)."')";
	$archive_rslt=mysql_to_mysqli($archive_stmt, $link);

	# JCJ 1/7/16 - do not do this, as it will end the chat for all agents.
	# $archive_stmt="insert ignore into vicidial_manager_chats_archive select * from vicidial_manager_chats where manager_chat_id='$manager_chat_id'";
	# $archive_rslt=mysqli_query($link, $archive_stmt);

	$delete_stmt="delete from vicidial_manager_chat_log where manager_chat_id='$manager_chat_id' and manager_chat_subid in ('".implode("','",$manager_chat_subid_array)."')";
	$delete_rslt=mysql_to_mysqli($delete_stmt, $link);

	#if (mysqli_affected_rows($link)>0) {
	#	$archive_stmt="delete from vicidial_manager_chats where manager_chat_id='$manager_chat_id'";
	#	$archive_rslt=mysqli_query($link, $archive_stmt);
	#}
	echo mysqli_affected_rows($link);
}

if ($action=="RefreshActiveChatView" && $user) {
	# Get a count on unread messages where the user is involved but not the chat manager/initiator in order to create the ChatReloadIDNumber variable
	$chat_reload_id_number_array=array();
	$unread_stmt="select manager_chat_id, manager_chat_subid, sum(if(message_viewed_date is not null, 0, 1)) as unread_count from vicidial_manager_chat_log where vicidial_manager_chat_log.user='$user' group by manager_chat_id, manager_chat_subid order by manager_chat_id, manager_chat_subid";
	$unread_rslt=mysql_to_mysqli($unread_stmt, $link);
	while ($unread_row=mysqli_fetch_array($unread_rslt)) {
		$IDNumber=$unread_row["manager_chat_id"]."-".$unread_row["manager_chat_subid"]."-".$unread_row["unread_count"];
		array_push($chat_reload_id_number_array, "$IDNumber");
	}

	# Pull the most recently posted-to chat that has not been viewed, then the most recent period, and display that as the default window
	$stmt="select distinct vicidial_manager_chat_log.manager_chat_id, vicidial_manager_chat_log.manager_chat_subid, vicidial_users.full_name, vicidial_manager_chats.chat_start_date, sum(if(vicidial_manager_chat_log.message_viewed_date is null, 1, 0)) from vicidial_manager_chat_log, vicidial_manager_chats, vicidial_users where vicidial_manager_chat_log.user='$user' and vicidial_manager_chat_log.manager_chat_id=vicidial_manager_chats.manager_chat_id and vicidial_manager_chats.manager=vicidial_users.user group by manager_chat_id, manager_chat_subid order by message_viewed_date asc, message_date desc";
	$rslt=mysql_to_mysqli($stmt, $link);
	
	$active_chats_array=array();
	$chat_subid_array=array();
	$unread_chats_array=array();
	$chat_managers_array=array();
	$chat_start_date_array=array();
	while ($row=mysqli_fetch_row($rslt)) {
		if ($row[0]!="") {
			array_push($active_chats_array, "$row[0]");
			$chat_subid_array[$row[0]]="$row[1]";
			$chat_managers_array[$row[0]]="$row[2]";
			$chat_start_date_array[$row[0]]="$row[3]";
			if ($row[4]>0) {array_push($unread_chats_array, $row[0]);} # Store any chat with unread messages.
			$agents_managers_array[$row[0]]="0";
		}
	}

	# Get a count on unread messages where the user is the chat manager/initiator in order to create the ChatReloadIDNumber variable
	$unread_stmt="select manager_chat_id, manager_chat_subid, sum(if(message_viewed_date is not null, 0, 1)) as unread_count from vicidial_manager_chat_log where vicidial_manager_chat_log.manager='$user' group by manager_chat_id, manager_chat_subid order by manager_chat_id, manager_chat_subid";
	$unread_rslt=mysql_to_mysqli($unread_stmt, $link);
	while ($unread_row=mysqli_fetch_array($unread_rslt)) {
		$IDNumber=$unread_row["manager_chat_id"]."-".$unread_row["manager_chat_subid"]."-".$unread_row["unread_count"];
		array_push($chat_reload_id_number_array, "$IDNumber");
	}

	### This was added for agent to agent chats since there needs to be a list of open chats where the agent viewing this is also the manager,
	### which will now happen because agents can now start their own chats.  Added vicidial_manager_chat_log.user to this query on 2/4/15 for
	### manager override
	$stmt="select distinct vicidial_manager_chat_log.manager_chat_id, vicidial_manager_chat_log.manager_chat_subid, vicidial_users.full_name, vicidial_manager_chats.chat_start_date, sum(if(vicidial_manager_chat_log.message_viewed_date is null, 1, 0)),vicidial_manager_chat_log.user from vicidial_manager_chat_log, vicidial_manager_chats, vicidial_users where vicidial_manager_chat_log.manager='$user' and vicidial_manager_chat_log.manager_chat_id=vicidial_manager_chats.manager_chat_id and vicidial_manager_chat_log.user=vicidial_users.user group by manager_chat_id, manager_chat_subid order by message_viewed_date asc, message_date desc";
	$rslt=mysql_to_mysqli($stmt, $link);
	while ($row=mysqli_fetch_row($rslt)) {
		if ($row[0]!="") {
			array_push($active_chats_array, "$row[0]");
			$chat_subid_array[$row[0]]="$row[1]";
			$chat_managers_array[$row[0]]="$row[2]";
			$chat_start_date_array[$row[0]]="$row[3]";
			if ($row[4]>0) {array_push($unread_chats_array, $row[0]);} # Store any chat with unread messages.
			$agents_managers_array[$row[0]]="$row[5]";  // IS a chat where the agent is a manager
		}
	}
	#########
	asort($chat_reload_id_number_array);
	$new_ChatReloadIDNumber="";
	while (list($key, $id_number) = each($chat_reload_id_number_array)) {
		$new_ChatReloadIDNumber.="$id_number.";
	}
	$new_ChatReloadIDNumber=substr($new_ChatReloadIDNumber,0,-1);
	
	sort($active_chats_array);
	asort($chat_managers_array);

	if ($new_ChatReloadIDNumber!=$ChatReloadIDNumber) {
		echo "$new_ChatReloadIDNumber|";
		echo "<ul class='chatview'>";
		if (empty($chat_managers_array)) {
			echo "\t<li class='arial_bold'>"._QXZ("NO OPEN CHATS")."</li>\n";
		} else {
			while (list($manager_chat_id, $text) = each($chat_managers_array)) {
				$manager_chat_subid=$chat_subid_array[$manager_chat_id];
				if (!empty($unread_chats_array) && in_array($manager_chat_id, $unread_chats_array)) {$cclass="unreadchat";} else {$cclass="viewedchat";}
				echo "\t<li class='".$cclass."'><a onClick=\"document.getElementById('CurrentActiveChat').value='$manager_chat_id'; document.getElementById('CurrentActiveChatSubID').value='$manager_chat_subid'; document.getElementById('AgentManagerOverride').value='".$agents_managers_array[$manager_chat_id]."'; \">".$chat_managers_array[$manager_chat_id]."</a></li>\n";
			}
		}
		echo "</ul>\n";
	}
}

if ($action=="ReloadAgentNewChatSpan" && $user) {
	echo "<table width='600' border='0' cellpadding='5' cellspacing='0'>\n";
	echo "<TR BGCOLOR='#E6E6E6' valign='top'>\n";
	echo "<td width='*'><font class='arial'>"._QXZ("Select a live agent").":</font><BR>\n";

	$stmt="SELECT user_group from vicidial_users where user='$user';";
	if ($non_latin > 0) {$rslt=mysql_to_mysqli("SET NAMES 'UTF8'", $link);}
	$rslt=mysql_to_mysqli($stmt, $link);
		if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'00573',$user,$server_ip,$session_name,$one_mysql_log);}
	$row=mysqli_fetch_row($rslt);
	$VU_user_group =	$row[0];

	$stmt="SELECT campaign_id from vicidial_live_agents where user='$user';";
	if ($non_latin > 0) {$rslt=mysql_to_mysqli("SET NAMES 'UTF8'", $link);}
	$rslt=mysql_to_mysqli($stmt, $link);
		if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'00XXX',$user,$server_ip,$session_name,$one_mysql_log);}
	$row=mysqli_fetch_row($rslt);
	$campaign_id =	$row[0];

	$agent_allowed_chat_groupsSQL='';
	### Gather timeclock and shift enforcement restriction settings
	$stmt="SELECT agent_status_viewable_groups,agent_status_view_time,agent_allowed_chat_groups from vicidial_user_groups where user_group='$VU_user_group';";
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$agent_allowed_chat_groups = $row[2];
	$agent_allowed_chat_groupsSQL = preg_replace('/\s\s/i','',$agent_allowed_chat_groups);
	$agent_allowed_chat_groupsSQL = preg_replace('/\s/i',"','",$agent_allowed_chat_groupsSQL);
	$agent_allowed_chat_groupsSQL = "user_group IN('$agent_allowed_chat_groupsSQL')";
	$agent_status_view = 0;
	if (strlen($agent_allowed_chat_groups) > 2)
		{$agent_status_view = 1;}
	$agent_status_view_time=0;
	if ($row[1] == 'Y')
		{$agent_status_view_time=1;}
	$andSQL='';
	if (preg_match("/ALL-GROUPS/",$agent_allowed_chat_groups))
		{$AGENTviewSQL = "";}
	else
		{
		$AGENTviewSQL = "($agent_allowed_chat_groupsSQL)";

		if (preg_match("/CAMPAIGN-AGENTS/",$agent_allowed_chat_groups))
			{$AGENTviewSQL = "($AGENTviewSQL or (campaign_id='$campaign_id'))";}
		$AGENTviewSQL = "and $AGENTviewSQL";
		}

	$stmt="SELECT vla.user,vu.full_name from vicidial_live_agents vla,vicidial_users vu where vla.user=vu.user and vu.user!='$user' $AGENTviewSQL order by vu.full_name;";
	$rslt=mysql_to_mysqli($stmt, $link);
	if ($rslt) {$agents_count = mysqli_num_rows($rslt);}
	$loop_count=0;
	echo "<select name='agent' id='agent'>\n";
	echo "<option value=''>"._QXZ("Available agents")."</option>\n";
	while ($agents_count > $loop_count)
		{
		$row=mysqli_fetch_row($rslt);
		echo "<option value='$row[0]'>$row[1]</option>\n";
		$loop_count++;
		}
	echo "</select>";

	echo "</td>\n";
	echo "<td width='200'><font class='arial'>"._QXZ("Message").":</font><BR>\n";
	echo "<textarea class='small_arial' rows='5' cols='36' name='agent_message' id='agent_message'></textarea>";
	echo "</td></TR>\n";

	echo "<TR BGCOLOR='#E6E6E6'>\n";
	echo "<td><BR><input class='red_btn' type='button' style='width:200px' value='"._QXZ("BACK TO CHAT SCREEN")."' onClick=\"document.getElementById('AgentChatSpan').style.display='block'; document.getElementById('AgentNewChatSpan').style.display='none';\"></td>\n";
	echo "<td align='center'><BR><input class='green_btn' type='button' style='width:200px' value='"._QXZ("START CHAT")."' onClick=\"CreateAgentToAgentChat()\">\n</td></TR>\n";
	echo "</table>";

}

if ($action=="SendMgrChatMessage" && $manager_chat_id && $manager_chat_subid) {

	$chat_message = preg_replace("/\r/i",'',$chat_message);
	$chat_message = preg_replace("/\n/i",' ',$chat_message);

	$mgr_stmt="select manager from vicidial_manager_chats where manager_chat_id='$manager_chat_id'";
	$mgr_rslt=mysql_to_mysqli($mgr_stmt, $link);
	$mgr_row=mysqli_fetch_row($mgr_rslt);
	$manager=$mgr_row[0];

	$message_posted_by=$user;
	if ($agent_override && $agent_override!="0") {$user=$agent_override;} // Added for agent-to-agent chatting so the manager ID and user ID don't get mixed up
	
	if ($manager) {
		$ins_chat_stmt="insert into vicidial_manager_chat_log(manager_chat_id, manager_chat_subid, manager, user, message, message_date, message_posted_by) VALUES('$manager_chat_id', '$manager_chat_subid', '$manager', '$user', '".mysqli_real_escape_string($link, $chat_message)."', now(), '$message_posted_by')";
		$ins_chat_rslt=mysql_to_mysqli($ins_chat_stmt, $link);
		if (mysqli_insert_id($link)>0) {
			$reload_chat_span=1;
		} else {
			echo _QXZ("Error sending message.");
		}
	} else {
		echo _QXZ("Error sending message.");
	}
}
######################################


/* JOEJ - removed since this is for customer requests only and is now done on a single page
if ($action=="request_chat" && $user && $chat_member_name) { # For people requesting a chat with an agent; consider using a special user variable name for this
	$ip_address=$_SERVER['REMOTE_ADDR'];

	# SEARCH FOR CUSTOMER IN DATABASE - IF NOT FOUND BY PHONE OR IP ADDRESS MAKE A NEW USER
	if ($phone_number) {
		$stmt="select lead_id from vicidial_list where phone_number='$phone_number' order by entry_date desc limit 1";
		$rslt=mysql_to_mysqli($stmt, $link);
	} else {
		$stmt="select lead_id from vicidial_list where email like '%".$ip_address."%' order by entry_date desc limit 1";
		$rslt=mysql_to_mysqli($stmt, $link);
	}

	if (mysqli_num_rows($rslt)>0) {
		$row=mysqli_fetch_row($rslt);
		$lead_id=$row[0];
	} else {
		# Create lead in vicidial_list table
		$ins_stmt="insert into vicidial_list(first_name, last_name, email, list_id, phone_number, security_phrase) VALUES('$first_name', '$last_name', '$ip_address', '$default_list_id', '$phone_number', '$group_id')";
		$ins_rslt=mysql_to_mysqli($ins_stmt, $link);
		$lead_id=mysqli_insert_id($link);
	}

	if (!$lead_id || $lead_id==0) {
		echo "0|Could not find or create dialer entry - $ins_stmt";
		exit;
	}

	$ins_stmt="insert into vicidial_live_chats(status, chat_creator, group_id, lead_id, chat_start_time, customer_ping_date) VALUES('WAITING', 'NONE', '$group_id', '$lead_id', now(), now())";
	$ins_rslt=mysql_to_mysqli($ins_stmt, $link);
	$chat_id=mysqli_insert_id($link);	

	if ($chat_id>0) {

		$ins_stmt="insert into vicidial_chat_participants(chat_id, chat_member, chat_member_name) VALUES('$chat_id', '$user', '$chat_member_name')";
		$ins_rslt=mysql_to_mysqli($ins_stmt, $link);
		if (mysqli_affected_rows($link)==0) {
			$del_stmt="delete from vicidial_live_chats where chat_id='$chat_id'";
			$del_rslt=mysql_to_mysqli($del_stmt, $link);
			echo "0|Chat started, failure to join";
		} else {
			echo "1|$chat_id|$lead_id";
		}
	} else {
		echo "0|Chat not started - $ins_stmt";
	}
}
*/

if ($action=="assign_chat" && $chat_id) { # Assign available vicidial_agent to chat
}

###### CUSTOMER-AGENT CHAT FUNCTIONS ######
if ($action=="start_chat" && $user && $server_ip) {
	if (!$chat_group_id) {
		echo "NO_GROUP";
	} else {
		$user_stmt="select full_name from vicidial_users where user='$user'";
		$user_rslt=mysql_to_mysqli($user_stmt, $link);
		if (mysqli_num_rows($user_rslt)>0) {
			$live_agent_stmt="select * from vicidial_live_agents where user='$user' and status='PAUSED'";
			$live_agent_rslt=mysql_to_mysqli($live_agent_stmt, $link);
			if (mysqli_num_rows($live_agent_rslt)>0) {
				$user_row=mysqli_fetch_row($user_rslt);
				$user_name="$user_row[0]";

				$upd_stmt="UPDATE vicidial_live_agents set status='INCALL',comments='CHAT',last_call_time='$NOW_TIME',external_hangup=0,external_status='',external_pause='',external_dial='',last_state_change='$NOW_TIME',pause_code='' where user='$user' and server_ip='$server_ip';";
				$upd_rslt=mysql_to_mysqli($upd_stmt, $link);
				if (mysqli_affected_rows($link)>0) {				
					$ins_stmt="insert into vicidial_live_chats(chat_creator, chat_start_time, status, group_id) values('$user', now(), 'WAITING', '$chat_group_id')";
					$ins_rslt=mysql_to_mysqli($ins_stmt, $link);

					$chat_id=mysqli_insert_id($link);	
					if ($chat_id) {
						$ins_stmt2="insert into vicidial_chat_participants(chat_id, chat_member, chat_member_name, vd_agent) values('$chat_id', '$user', '$user_name', 'Y')";
						$ins_rslt2=mysql_to_mysqli($ins_stmt2, $link);
						echo $chat_id;
					} else {
						echo "0";
					}
				} else {
					echo "FAILED_LIVE_STATUS";
				}
			} else {
				echo "NOT_PAUSED";
			}
		} else {
			echo "0";
		}
	}
}

/* Customers have separate function  file - customer_chat_functions.php */
if ($action=="agent_send_message" && $chat_id) {
	$live_stmt="select status from vicidial_live_chats where chat_id='$chat_id'";
	$live_rslt=mysql_to_mysqli($live_stmt, $link);

	if ($user && $chat_message && $chat_id && mysqli_num_rows($live_rslt)>0) {
		$live_row=mysqli_fetch_row($live_rslt);
		$status=$live_row[0];
		if ($status=="WAITING") {
			echo _QXZ("Chat is waiting for an agent").": $chat_id";
		} else {
			if ($status=="LIVE") {
				$ins_stmt="insert ignore into vicidial_chat_log(chat_id, message, poster, chat_member_name, chat_level) VALUES('$chat_id', '".mysqli_real_escape_string($link, $chat_message)."', '$user', '".mysqli_real_escape_string($link, $chat_member_name)."', '$chat_level')";
				$ins_rslt=mysql_to_mysqli($ins_stmt, $link);
				if (mysqli_affected_rows($link)<1) {
					echo "<font class='chat_title alert'>"._QXZ("SYSTEM ERROR")."</font><BR/>\n";
				}
			} else {
				echo "<font class='chat_title alert'>"._QXZ("SYSTEM ERROR")."</font><BR/>\n";
			}
		}
	} else if (mysqli_num_rows($rslt)==0) {
		echo _QXZ("Chat has been closed").": $chat_id";
	}
}

if ($action=="agent_leave_chat" && $user && $chat_id) { 
	$del_stmt2="delete from vicidial_chat_participants where chat_id='$chat_id' and chat_member='$user'";
	$del_rslt2=mysql_to_mysqli($del_stmt2, $link);
	$deleted_participants=mysqli_affected_rows($link);
	if ($deleted_participants>0) {
		# ERASE THE CHAT IF THE PERSON NEVER GOT PICKED UP - I DON'T REMEMBER WHY I MOVED customer_leave_chat TO leave_chat
		$stmt="select lead_id from vicidial_live_chats where chat_id='$chat_id' and status='WAITING' and chat_creator='NONE'";
		$rslt=mysql_to_mysqli($stmt, $link);
		if (mysqli_num_rows($rslt)>0) {
			$row=mysqli_fetch_row($rslt);
			$lead_id=$row[0];
			# CHECK IF SHOULD USE SPECIAL DROP STATUS 'CDROP' FOR DROPPED CHATS
			$upd_stmt="update vicidial_list set status='CDROP' where lead_id='$lead_id'";
			$upd_rslt=mysql_to_mysqli($upd_stmt, $link);
			
			$ins_stmt="insert ignore into vicidial_chat_archive select * From vicidial_live_chats where chat_id='$chat_id'";
			$ins_rslt=mysql_to_mysqli($ins_stmt, $link);

			$del_stmt="delete from vicidial_live_chats where chat_id='$chat_id' and status='WAITING' and chat_creator='NONE'";
			$del_rslt=mysql_to_mysqli($del_stmt, $link);
		}
	}
}

if ($action=="update_agent_chat_window" && $chat_id) {
	$status_stmt="select status, chat_creator from vicidial_live_chats where chat_id='$chat_id'";
	$status_rslt=mysql_to_mysqli($status_stmt, $link);
	if (mysqli_num_rows($status_rslt)==0) {
		echo "<font class='chat_title alert'>"._QXZ("Chat does not exist or has been closed").": $chat_id</font><BR/>\n";
	} else {
		$status_row=mysqli_fetch_row($status_rslt);
		
		## Modify user's ping date to verify they are still participating
		if ($user && $keepalive) {
			$upd_stmt="update vicidial_chat_participants set ping_date=now() where chat_member='$user' and chat_id='$chat_id'";
			$upd_rslt=mysql_to_mysqli($upd_stmt, $link);
		}

		## CHECK IF CHAT IS ACTIVE, IF SO GRAB DISTINCT USERS IN ORDER OF POST TO ASSIGN COLORS
		if ($status_row[0]=="LIVE") {
			$live_stmt="select * from vicidial_live_chats vlc, vicidial_chat_participants vcp where vlc.chat_id='$chat_id' and status='LIVE' and vlc.chat_id=vcp.chat_id and vcp.chat_member='$user'";
			$live_rslt=mysql_to_mysqli($live_stmt, $link);
			if (mysqli_num_rows($live_rslt)>0) {
				echo "<font class='chat_title bold'>"._QXZ("Current chat").": $chat_id</font><BR/>\n";

				# Create color-coding for chat
				$stmt="select * from vicidial_chat_log where chat_id='$chat_id' order by message_time asc";

				$rslt=mysql_to_mysqli($stmt, $link);
				$chat_members=array();
				while ($row=mysqli_fetch_row($rslt)) {
					if (!in_array("$row[4]", $chat_members)) {
						array_push($chat_members, "$row[4]");
					}
				}

				## GRAB CHAT MESSAGES AND DISPLAY THEM
				if (!$user_level || $user_level==0) {$user_level_clause=" and chat_level='0' ";} else {$user_level_clause="";}

				$stmt="select * from vicidial_chat_log where chat_id='$chat_id' $user_level_clause order by message_time asc";

				$rslt=mysql_to_mysqli($stmt, $link);
				while ($row=mysqli_fetch_row($rslt)) {
					$chat_color_key=array_search("$row[4]", $chat_members);
					$row[2]=preg_replace('/\n/', '<BR/>', $row[2]);	
					echo "<li><font color='$color_array[$chat_color_key]' class='chat_message bold'>$row[5]</font> <font class='chat_timestamp bold'>($row[3])</font> - <font class='chat_message ".$style_array[$row[6]]."'>$row[2]</font></li>\n";
				}

				## PLAY AUDIO FILE IF THERE ARE NEW MESSAGES
				$current_messages=mysqli_num_rows($rslt);
				echo "<input type='hidden' id='current_message_count' name='current_message_count' value='$current_messages'>\n";
			} else {	
				echo "<font class='chat_title bold'>"._QXZ("Click on a live chat at right to join it.")."</font><BR/>\n";
			}
		} else {
			if ($status_row[1]=="NONE") {
				echo "<font class='chat_title bold'>"._QXZ("Waiting for next available agent...")."</font><BR/>\n";
			# 8/28 - Added in case agent starts chat but hasn't invited someone or they haven't shown up yet
			} else if ($status_row[1]==$user || $status_row[0]=="WAITING") {
				echo "<font class='chat_title bold'>"._QXZ("Please send an invite to the customer to begin...")."</font><BR/>\n";
			} else {
				echo "<font class='chat_title alert'>"._QXZ("SYSTEM ERROR")."</font><BR/>\n";
			}
		}
	}
}

### CURRENTLY DEFUNCT
/*
if ($action=="customer_leave_chat" && $chat_id) {
	$del_stmt2="delete from vicidial_chat_participants where chat_id='$chat_id' and chat_member='$user'";
	$del_rslt2=mysql_to_mysqli($del_stmt2, $link);
	$deleted_participants=mysqli_affected_rows($link);

	$archive_stmt="insert ignore into vicidial_chat_archive select chat_id, chat_start_time, 'DROP' as status, chat_creator, group_id, lead_id from vicidial_live_chats where chat_id='$chat_id'  and status='WAITING' and chat_creator='NONE'";
	$archive_rslt=mysql_to_mysqli($archive_stmt, $link);
	if (mysqli_affected_rows($link)>0) {
		$del_stmt="delete from vicidial_live_chats where chat_id='$chat_id' and status='WAITING' and chat_creator='NONE'";
		$del_rslt=mysql_to_mysqli($del_stmt, $link);
	}
}
*/
#####################################

if ($action=="send_invite" && $chat_id && $email && $chat_group_id) {
	$chat_stmt="select chat_url from system_settings";
	$chat_rslt=mysql_to_mysqli($chat_stmt, $link);
	$chat_row=mysqli_fetch_row($chat_rslt);
	$chat_url=$chat_row[0];

	## Check if person being emailed exists as a lead
	$email_stmt="select lead_id from vicidial_list where email='$email' order by lead_id desc limit 1";
	$email_rslt=mysql_to_mysqli($email_stmt, $link);
	if (mysqli_num_rows($email_rslt)>0) {
		$email_row=mysqli_fetch_row($email_rslt);
		$lead_id=$email_row[0];
	} else {
			$ins_stmt="insert into vicidial_list(status, email, list_id, security_phrase) VALUES('NEW', '$email', '$default_list_id', '$chat_group_id')";
			$ins_rslt=mysql_to_mysqli($ins_stmt, $link);
			$lead_id=mysqli_insert_id($link);
	}

	if ($lead_id) {
		$to = "$email";
		$from = 'vicidial_chat_bot@vicidial.com'; 
		$subject ="VICIDIAL CHAT INVITE - DO NOT REPLY TO THIS EMAIL"; 

		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "To: $email\r\n";
		$headers .= "From: $from\r\n";
	 
		$unique_userID=time().".".rand(10000, 99999);

		$user_stmt="select full_name from vicidial_users where user='$user'";
		$user_rslt=mysql_to_mysqli($user_stmt, $link);
		$user_row=mysqli_fetch_row($user_rslt);
		$user_name=$user_row[0];

		$message = "<html>
		<head>
		  <title>Invitation to ViciDIAL chat</title>
		</head>
		<body>
		  <p>$user_name has invited you to participate in a Vicidial Chat<BR>

		  <p><a href='".$chat_url."?unique_userID=".urlencode($unique_userID)."&lead_id=$lead_id&chat_group_id=".urlencode($chat_group_id)."&chat_id=$chat_id&email=".urlencode($email)."'>JOIN CHAT</A><BR>

		  <p>Enter your name, which will allow you to start chatting with the Vicidial agent.<BR>
		</body>
		</html>";

	#	  <table>
	#		<tr>
	#		  <th>PUT FORM HERE LATER</th>
	#		</tr>
	#	  </table>


		$sendmail = @mail($to, $subject, $message, $headers); 
	}
	
	if ($sendmail) 
		{
		$upd_stmt="update vicidial_live_chats set lead_id='$lead_id' where chat_id='$chat_id'";
		$upd_rslt=mysql_to_mysqli($upd_stmt, $link);
		if (mysqli_affected_rows($link)>0) 
			{
			echo "$lead_id";
			}
		else
			{
			echo "0";
			}
		} 
	else
		{
		echo "0";
		}
}

if ($action=="end_chat" && $chat_id && $chat_creator && $user && $server_ip) {
	if ($user!=$chat_creator) {
		echo _QXZ("You cannot end this chat unless you are the one who started it.");
		exit;
	}

	$archive_stmt="insert ignore into vicidial_chat_archive select * from vicidial_live_chats where chat_id='$chat_id'";
	$archive_rslt=mysql_to_mysqli($archive_stmt, $link);

	$archive_log_stmt="insert ignore into vicidial_chat_log_archive select * from vicidial_chat_log where chat_id='$chat_id'";
	$archive_log_rslt=mysql_to_mysqli($archive_log_stmt, $link);

	$del_stmt="delete from vicidial_live_chats where chat_id='$chat_id' and chat_creator='$chat_creator'";
	$del_rslt=mysql_to_mysqli($del_stmt, $link);
	if (mysqli_affected_rows($link)==0) {
		echo _QXZ("Warning: chat ID not found. Deleting any remaining participants...")."<BR><BR>";
	} else {
		echo _QXZ("Chat ended")."<BR><BR>";
	}
	$del_log_stmt="delete from vicidial_chat_log where chat_id='$chat_id'";
	$del_log_rslt=mysql_to_mysqli($del_log_stmt, $link);

	$del_stmt2="delete from vicidial_chat_participants where chat_id='$chat_id'";
	$del_rslt2=mysql_to_mysqli($del_stmt2, $link);
	$deleted_participants=mysqli_affected_rows($link);

	echo "$deleted_participants "._QXZ("removed from chat.");

	# HTML to display after ending chat - will only display again if no lead_id, i.e. no customer involved and no dispositioning needed.
	echo "|";
	if (!$lead_id) {
		$upd_stmt="UPDATE vicidial_live_agents set status='PAUSED',comments='',external_hangup=0,external_status='',external_pause='',external_dial='',last_state_change='$NOW_TIME',pause_code='' where user='$user' and server_ip='$server_ip' and status='INCALL' and comments='CHAT'";
		$upd_rslt=mysql_to_mysqli($upd_stmt, $link);

		$vla_stmt="select closer_campaigns from vicidial_live_agents where user='$user'";
		$vla_rslt=mysql_to_mysqli($vla_stmt, $link);
		echo "<BR/><BR/><input class='green_btn' type='button' style=\"width:150px\" value=\""._QXZ("START CHAT")."\" onClick=\"StartChat()\">";
		echo "<BR/><BR/><select name='startchat_group_id' id='startchat_group_id' class='chat_window' onChange=\"document.getElementById('chat_group_id').value=this.value\">"; 
		echo "<option value='' selected>--"._QXZ("SELECT A CHAT GROUP")."--</option>";
		if (mysqli_num_rows($vla_rslt)>0) {
			$vla_row=mysqli_fetch_row($vla_rslt);
			$closer_campaigns=trim($vla_row[0]);
			$closer_campaigns=preg_replace('/\s/', '\',\'', $closer_campaigns);
			$closer_campaigns_SQL="'".$closer_campaigns."'";

			$group_stmt="select group_id, group_name from vicidial_inbound_groups where group_handling='CHAT' and group_id in ($closer_campaigns_SQL) order by group_name asc";
			$group_rslt=mysql_to_mysqli($group_stmt, $link);
			while ($group_row=mysqli_fetch_row($group_rslt)) {
				echo "<option value='".$group_row[0]."'>".$group_row[1]."</option>";
			}
		}
		echo "</select>";
		echo "|TOGGLE_DIAL_CONTROL";
	}
}

if ($action=="join_chat" && $user && $chat_id && $chat_member_name) {
	# REMOVE AGENT FROM OTHER CHATS, JUST IN CASE
	$del_stmt="delete from vicidial_chat_participants where chat_member='$user'";
	$del_rslt=mysql_to_mysqli($del_stmt, $link);

	$ins_stmt="insert into vicidial_chat_participants(chat_id, chat_member, chat_member_name, vd_agent) values('$chat_id', '$user', '".mysqli_real_escape_string($link, $chat_member_name)."', 'Y')";
	$ins_rslt=mysql_to_mysqli($ins_stmt, $link);
	if (mysqli_affected_rows($link)==0) {
		echo "ERROR|"._QXZ("Chat was not joined.");
	} else {
		$creator_stmt="select chat_creator from vicidial_live_chats where chat_id='$chat_id'";
		$creator_rslt=mysql_to_mysqli($creator_stmt, $link);
		if (mysqli_num_rows($creator_rslt)>0) {
			$crow=mysqli_fetch_row($creator_rslt);
			echo "$crow[0]";
		} else {
			echo "ERROR|"._QXZ("You have joined a non-existent chat.  Trippy.")."\n";
		}
	}
}

if ($action=="show_live_chats" && $user) {
	echo "<ul id=\"treemenu1\" class=\"treeview\">\n";
	if ($user && $pass) {
		$user_stmt="select full_name from vicidial_users where user='$user' and pass='$pass'";
		$user_rslt=mysql_to_mysqli($user_stmt, $link);
		$vicidial_user=mysqli_num_rows($user_rslt);
		$vicidial_user_row=mysqli_fetch_row($user_rslt);
		$full_name=$vicidial_user_row[0];
	}

	$active_chats=array();
	$agents_in_chat=array();
	$absent_agents=array();
	if ($vicidial_user) {	# If you aren't a vicidial_user you aren't going to see the chat window.
		$stmt="select vlc.chat_id, vcp.chat_member, vcp.chat_member_name, vlc.chat_creator, vcp.vd_agent from vicidial_live_chats vlc, vicidial_chat_participants vcp where vlc.chat_id=vcp.chat_id order by vlc.chat_id asc, vcp.chat_member_name asc";

		$rslt=mysql_to_mysqli($stmt, $link);
		while($row=mysqli_fetch_row($rslt)) {
			$chat_id=$row[0];
			$chat_member=$row[1];
			$chat_member_name=$row[2];
			$chat_creator=$row[3];
			$vd_agent=$row[4];
			$active_chats[$chat_id][]=array("$chat_member", "$chat_member_name", "in chat");
			if ($vd_agent=="Y") {
				array_push($agents_in_chat, "$chat_member");
			}
		}

		# If an agent is the owner of a live chat but not a participant, they need their name displayed there.
		$empty_live_chat_stmt="select chat_id, '$user' as chat_member, '$full_name' as chat_member, chat_creator from vicidial_live_chats where status='LIVE' and chat_creator='$user'";
		$empty_live_chat_rslt=mysql_to_mysqli($empty_live_chat_stmt, $link);
		while($row=mysqli_fetch_row($empty_live_chat_rslt)) {
			$chat_id=$row[0];
			$chat_member=$row[1];
			$chat_member_name=$row[2];
			if (!$active_chats[$chat_id]) {
				$active_chats[$chat_id][]=array("$chat_member", "$chat_member_name", "absent");
			}
		}
	}
	
	if ($active_chats) {
		echo "<font class='chat_title bold'>"._QXZ("Available Chats").":</font><BR><BR>";
		while (list($key, $val)=each($active_chats)) {
			echo "<li class='submenu' onClick=\"JoinChat('$key', '$chat_creator');\"><font class='chat_message bold'>"._QXZ("Chat ID")." #$key</font>\n";
			echo "\t<ul id=\"chat_members_$key\">\n";
			while (list($subkey, $subval)=each($val)) {
				if ($subval[2]=="absent") {
					$font_color=" absent_agent";
				} else if (in_array($subval[0], $agents_in_chat)) {
					$font_color=" vd_agent";
				} else {
					$font_color="";
				}
				echo "\t\t<li><font class='chat_timestamp bold$font_color'>$subval[1]</font></li>\n";
			}
			echo "\t</ul>\n";
			echo "</li>\n";
		}
		echo "</ul>";
	}
	
	if ($rslt && mysqli_num_rows($rslt)>0) {
		echo "<input type='button' style='width:180px' value='"._QXZ("CLICK CHAT TO JOIN")."' disabled>";
	}
}

?>
