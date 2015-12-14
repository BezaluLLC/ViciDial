<?php
# agc_agent_manager_chat_interface.php
# 
# Copyright (C) 2015  Joe Johnson, Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# This page is for agents to chat with managers via the agent interface.
#
# changes:
# 141212-2245 - First Build
# 151213-1108 - Added variable filtering
#

$admin_version = '2.12-2';
$build = '151213-1108';

$sh="managerchats"; 

require("dbconnect_mysqli.php");
require("functions.php");

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,enable_languages,language_method FROM system_settings;";
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
        }
##### END SETTINGS LOOKUP #####
###########################################

$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
if (isset($_GET["DB"]))							{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))				{$DB=$_POST["DB"];}
if (isset($_GET["action"]))						{$action=$_GET["action"];}
	elseif (isset($_POST["action"]))			{$action=$_POST["action"];}
if (isset($_GET["SUBMIT"]))						{$SUBMIT=$_GET["SUBMIT"];}
	elseif (isset($_POST["SUBMIT"]))			{$SUBMIT=$_POST["SUBMIT"];}
if (isset($_GET["manager_chat_id"]))			{$manager_chat_id=$_GET["manager_chat_id"];}
	elseif (isset($_POST["manager_chat_id"]))	{$manager_chat_id=$_POST["manager_chat_id"];}
if (isset($_GET["user"]))						{$user=$_GET["user"];}
	elseif (isset($_POST["user"]))				{$user=$_POST["user"];}
if (isset($_GET["pass"]))						{$pass=$_GET["pass"];}
	elseif (isset($_POST["pass"]))				{$pass=$_POST["pass"];}
if (!$user) {echo "Page should only be viewed through the agent interface."; die;}

if ($non_latin < 1)
	{
	$user = preg_replace('/[^-\_0-9a-zA-Z]/','',$user);
	$pass = preg_replace('/[^-\_0-9a-zA-Z]/','',$pass);
	$manager_chat_id = preg_replace('/[^- \_\.0-9a-zA-Z]/','',$user);
	}
else
	{
	$user = preg_replace("/\'|\"|\\\\|;/","",$user);
	$pass=preg_replace("/\'|\"|\\\\|;| /","",$pass);
	$manager_chat_id = preg_replace("/\'|\"|\\\\|;/","",$user);
	}

# Get a count on unread messages where the user is involved but not the chat manager/initiator in order to create the ChatReloadIDNumber variable
$chat_reload_id_number_array=array();
$unread_stmt="select manager_chat_id, manager_chat_subid, sum(if(message_viewed_date is not null, 0, 1)) as unread_count from vicidial_manager_chat_log where vicidial_manager_chat_log.user='$user' group by manager_chat_id, manager_chat_subid order by manager_chat_id, manager_chat_subid";
$unread_rslt=mysqli_query($link, $unread_stmt);
while ($unread_row=mysqli_fetch_array($unread_rslt)) {
	$IDNumber=$unread_row["manager_chat_id"]."-".$unread_row["manager_chat_subid"]."-".$unread_row["unread_count"];
	array_push($chat_reload_id_number_array, "$IDNumber");
}

# Pull the most recently posted-to chat that has not been viewed, then the most recent period, and display that as the default window
$stmt="select distinct vicidial_manager_chat_log.manager_chat_id, vicidial_manager_chat_log.manager_chat_subid, vicidial_users.full_name, vicidial_manager_chats.chat_start_date, sum(if(vicidial_manager_chat_log.message_viewed_date is null, 1, 0)),vicidial_manager_chat_log.user from vicidial_manager_chat_log, vicidial_manager_chats, vicidial_users where vicidial_manager_chat_log.user='$user' and vicidial_manager_chat_log.manager_chat_id=vicidial_manager_chats.manager_chat_id and vicidial_manager_chats.manager=vicidial_users.user group by manager_chat_id, manager_chat_subid order by message_viewed_date asc, message_date desc";
$rslt=mysqli_query($link, $stmt);

$active_chats_array=array();
$chat_subid_array=array();
$unread_chats_array=array();
$chat_managers_array=array();
$chat_start_date_array=array();
$agents_managers_array=array(); // for override
$priority_chat="";
$priority_chat_subid="";
while ($row=mysqli_fetch_row($rslt)) {
	if ($row[0]!="") {
		if (!$priority_chat) {$priority_chat=$row[0];} # The priority_chat is the most recent chat that has not been viewed.
		if (!$priority_chat_subid) {$priority_chat_subid=$row[1];} # The priority_chat is the most recent chat that has not been viewed.
		if (!$agent_manager_override) {$agent_manager_override="0";} # The priority_chat is the most recent chat that has not been viewed.
		array_push($active_chats_array, "$row[0]");
		$chat_subid_array[$row[0]]="$row[1]";
		$chat_managers_array[$row[0]]="$row[2]";
		$chat_start_date_array[$row[0]]="$row[3]";
		if ($row[4]>0) {array_push($unread_chats_array, $row[0]);} # Store any chat with unread messages.
		$agents_managers_array[$row[0]]="0";  // not a chat where the agent is a manager
	}
}

	# Get a count on unread messages where the user is the chat manager/initiator in order to create the ChatReloadIDNumber variable
	$unread_stmt="select manager_chat_id, manager_chat_subid, sum(if(message_viewed_date is not null, 0, 1)) as unread_count from vicidial_manager_chat_log where vicidial_manager_chat_log.manager='$user' group by manager_chat_id, manager_chat_subid order by manager_chat_id, manager_chat_subid";
	$unread_rslt=mysqli_query($link, $unread_stmt);
	while ($unread_row=mysqli_fetch_array($unread_rslt)) {
		$IDNumber=$unread_row["manager_chat_id"]."-".$unread_row["manager_chat_subid"]."-".$unread_row["unread_count"];
		array_push($chat_reload_id_number_array, "$IDNumber");
	}


	### This was added for agent to agent chats since there needs to be a list of open chats where the agent viewing this is also the manager,
	### which will now happen because agents can now start their own chats.  Added vicidial_manager_chat_log.user to this query on 2/4/15 for
	### manager override
	$stmt="select distinct vicidial_manager_chat_log.manager_chat_id, vicidial_manager_chat_log.manager_chat_subid, vicidial_users.full_name, vicidial_manager_chats.chat_start_date, sum(if(vicidial_manager_chat_log.message_viewed_date is null, 1, 0)),vicidial_manager_chat_log.user from vicidial_manager_chat_log, vicidial_manager_chats, vicidial_users where vicidial_manager_chat_log.manager='$user' and vicidial_manager_chat_log.manager_chat_id=vicidial_manager_chats.manager_chat_id and vicidial_manager_chat_log.user=vicidial_users.user group by manager_chat_id, manager_chat_subid order by message_viewed_date asc, message_date desc";
	$rslt=mysqli_query($link, $stmt);
	while ($row=mysqli_fetch_row($rslt)) {
		if ($row[0]!="") {
			if (!$priority_chat) {$priority_chat=$row[0];} # The priority_chat is the most recent chat that has not been viewed.
			if (!$priority_chat_subid) {$priority_chat_subid=$row[1];} # The priority_chat is the most recent chat that has not been viewed.
			if (!$agent_manager_override) {$agent_manager_override=$row[5];} # The priority_chat is the most recent chat that has not been viewed.
			array_push($active_chats_array, "$row[0]");
			$chat_subid_array[$row[0]]="$row[1]"; // Added back in on 3/3 - why was this removed?
			$chat_managers_array[$row[0]]="$row[2]";
			$chat_start_date_array[$row[0]]="$row[3]";
			if ($row[4]>0) {array_push($unread_chats_array, $row[0]);} # Store any chat with unread messages.
			$agents_managers_array[$row[0]]="$row[5]";  // IS a chat where the agent is a manager
		}
	}
	#########
asort($active_chats_array);
asort($chat_managers_array);

asort($chat_reload_id_number_array);
$ChatReloadIDNumber="";
while (list($key, $id_number) = each($chat_reload_id_number_array)) {
	$ChatReloadIDNumber.="$id_number.";
}
$ChatReloadIDNumber=substr($ChatReloadIDNumber,0,-1);
?>
<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
</head>
<link rel="stylesheet" href="vicidial_stylesheet.css">
<link rel="stylesheet" href="css/simpletree.css">
<script language="JavaScript" src="vicidial_chat_agent.js"></script>
<title><?php echo _QXZ("ADMINISTRATION: Agent/Manager Chat Interface"); ?></title>
<body onLoad="MgrAgentAutoRefresh();" onUnLoad="clearInterval(rInt);"><!-- DisplayMgrAgentChat(<?php echo $priority_chat; ?>); //-->
<span id="AgentChatSpan" name="AgentChatSpan" style="display: block;">
<?php
echo "<form name='agent_manager_chat_form' id='agent_manager_chat_form'>";
echo "<table width='620' border='0' cellpadding='5' cellspacing='0'>";
echo "<TR BGCOLOR='#E6E6E6'>\n";
echo "<td align='left' width='190' valign='top'><font class='arial'>Chatting with: </font><BR><span class='arial_bold' id='ActiveChatManager'>".$chat_managers_array[$priority_chat]."</span></td>";
echo "<td align='right' width='190' valign='top'><font class='arial'>Chat started: </font><BR><span class='arial_bold' id='ActiveChatStartDate'>".$chat_start_date_array[$priority_chat]."</span></td>";
echo "<td align='left' width='*' valign='bottom'><font class='arial'>Your active chats:</font></td>";
echo "</TR>";

echo "<TR BGCOLOR='#E6E6E6'>\n";
echo "<TD align='left' colspan='2' valign='top' width='380'>\n";
echo "\t<div class='scrolling_transcript' id='ActiveManagerChatTranscript'></div><BR>\n";
echo "\t<div id='AllowAgentReplies' align='center' style='display:none;'>\n";
echo "\t<textarea class='small_arial' rows='2' cols='65' name='manager_message' id='manager_message' onkeypress='if (event.keyCode == 13) {SendMgrChatMessage();}'></textarea><BR><input class='blue_btn' type='button' style='width:200px' value='SEND MESSAGE' onClick=\"SendMgrChatMessage()\">\n";
echo "\t</div>\n";
echo "</TD>\n";
echo "<TD align='left' rowspan='2' valign='top' width='210'>\n";
echo "<div class='scrolling_chat_display' id='AllActiveChats'>\n";
	echo "<ul class='chatview'>";
	if (empty($chat_managers_array)) {
		echo "\t<li class='arial_bold'>NO OPEN CHATS</li>\n";
	} else {
		while (list($manager_chat_id, $text) = each($chat_managers_array)) {
			$manager_chat_subid=$chat_subid_array[$manager_chat_id];
			if (!empty($unread_chats_array) && in_array($manager_chat_id, $unread_chats_array)) {$cclass="unreadchat";} else {$cclass="viewedchat";}
			echo "\t<li class='".$cclass."'><a onClick=\"document.getElementById('CurrentActiveChat').value='$manager_chat_id'; document.getElementById('CurrentActiveChatSubID').value='$manager_chat_subid'; document.getElementById('AgentManagerOverride').value='".$agents_managers_array[$manager_chat_id]."'; \">".$chat_managers_array[$manager_chat_id]."</a></li>\n";

			$sid++;
		}
	}
	echo "</ul>\n";
echo "\t</div>\n";
echo "<font class='small_arial_bold'>(bolded chats = unread messages)<BR><input type='checkbox' id='MuteChatAlert' name='MuteChatAlert'>Mute alert sound</font>\n";
echo "\t<BR><BR><input class='green_btn' type='button' style='width:200px' value='CHAT WITH LIVE AGENT' onClick=\"document.getElementById('AgentChatSpan').style.display='none'; document.getElementById('AgentNewChatSpan').style.display='block'; ReloadAgentNewChatSpan('$user');\">\n";
echo "\t<BR><BR><span id='AgentEndChatSpan' style='display: none;'><div align='left'><input class='red_btn' type='button' style='width:200px' value='END CHAT' onClick='EndAgentToAgentChat()'></div></span>";
echo "</TD>\n";
echo "</TR>\n";

#echo "<TR BGCOLOR='#E6E6E6'>\n";
#echo "<TD align='center' colspan='2'>&nbsp;\n";
#echo "</TD>\n";
#echo "</TR>\n";
echo "</table>\n";
echo "<input type='hidden' name='CurrentActiveChat' id='CurrentActiveChat' value='$priority_chat'>\n";
echo "<input type='hidden' name='InternalMessageCount' id='InternalMessageCount' value='0'>\n";
echo "<input type='hidden' name='CurrentActiveChatSubID' id='CurrentActiveChatSubID' value='$priority_chat_subid'>\n";
echo "<input type='hidden' name='AgentManagerOverride' id='AgentManagerOverride' value='$agent_manager_override'>\n";
echo "<input type='hidden' name='user' id='user' value='$user'>\n";
echo "<input type='hidden' size='50' name='ChatReloadIDNumber' id='ChatReloadIDNumber' value='$ChatReloadIDNumber'>\n";
echo "</form>";
?>
</span>
<span id='AgentNewChatSpan' name='AgentNewChatSpan' style='display: none;'>
<?php
echo "<table width='600' border='0' cellpadding='5' cellspacing='0'>\n";
echo "<TR BGCOLOR='#E6E6E6' valign='top'>\n";
echo "<td width='*'><font class='arial'>Select a live agent:</font><BR>\n";

	$stmt="SELECT user_group from vicidial_users where user='$user';";
	if ($non_latin > 0) {$rslt=mysql_to_mysqli("SET NAMES 'UTF8'", $link);}
	$rslt=mysql_to_mysqli($stmt, $link);
		if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'00573',$user,$server_ip,$session_name,$one_mysql_log);}
	$row=mysqli_fetch_row($rslt);
	$VU_user_group =	$row[0];

	$agent_status_viewable_groupsSQL='';
	### Gather timeclock and shift enforcement restriction settings
	$stmt="SELECT agent_status_viewable_groups,agent_status_view_time from vicidial_user_groups where user_group='$VU_user_group';";
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$agent_status_viewable_groups = $row[0];
	$agent_status_viewable_groupsSQL = preg_replace('/\s\s/i','',$agent_status_viewable_groups);
	$agent_status_viewable_groupsSQL = preg_replace('/\s/i',"','",$agent_status_viewable_groupsSQL);
	$agent_status_viewable_groupsSQL = "user_group IN('$agent_status_viewable_groupsSQL')";
	$agent_status_view = 0;
	if (strlen($agent_status_viewable_groups) > 2)
		{$agent_status_view = 1;}
	$agent_status_view_time=0;
	if ($row[1] == 'Y')
		{$agent_status_view_time=1;}
	$andSQL='';
	if (preg_match("/ALL-GROUPS/",$agent_status_viewable_groups))
		{$AGENTviewSQL = "";}
	else
		{
		$AGENTviewSQL = "($agent_status_viewable_groupsSQL)";

		if (preg_match("/CAMPAIGN-AGENTS/",$agent_status_viewable_groups))
			{$AGENTviewSQL = "($AGENTviewSQL or (campaign_id='$campaign'))";}
		$AGENTviewSQL = "and $AGENTviewSQL";
		}

	$stmt="SELECT vla.user,vu.full_name from vicidial_live_agents vla,vicidial_users vu where vla.user=vu.user and vu.user!='$user' $AGENTviewSQL order by vu.full_name;";
	$rslt=mysql_to_mysqli($stmt, $link);
	if ($rslt) {$agents_count = mysqli_num_rows($rslt);}
	$loop_count=0;
	echo "<select name='agent' id='agent'>\n";
	echo "<option value=''>Available agents</option>\n";
	while ($agents_count > $loop_count)
		{
		$row=mysqli_fetch_row($rslt);
		echo "<option value='$row[0]'>$row[1]</option>\n";
		$loop_count++;
		}
	echo "</select>";

echo "</td>\n";
echo "<td width='200'><font class='arial'>Message:</font><BR>\n";
echo "<textarea class='small_arial' rows='5' style='width:200px; name='agent_message' id='agent_message'></textarea>";
echo "</td></TR>\n";

echo "<TR BGCOLOR='#E6E6E6'>\n";
echo "<td><BR><input class='red_btn' type='button' style='width:200px' value='BACK TO CHAT SCREEN' onClick=\"document.getElementById('AgentChatSpan').style.display='block'; document.getElementById('AgentNewChatSpan').style.display='none';\"></td>\n";
echo "<td align='center'><BR><input class='green_btn' type='button' style='width:200px' value='START CHAT' onClick=\"CreateAgentToAgentChat()\">\n</td></TR>\n";
echo "</table>";
?>
</span>
</body>
</html>
