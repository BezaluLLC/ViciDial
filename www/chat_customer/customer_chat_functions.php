<?php
# customer_chat_functions.php
#
# CHANGES
# 151212-0828 - First Build for customer chat
# 151213-1106 - Added variable filtering
# 151217-1016 - Allow for carrying of group_id through to new chat
# 151219-0850 - Added translation code
# 160108-1710 - Added available_agents variable
# 160120-1943 - Added show_email variable
# 160203-1051 - Added display of chat message after ending it
#

require("dbconnect_mysqli.php");
require("functions.php");

$color_array=array("#FF0000", "#0000FF", "#009900", "#990099", "#009999", "#666600", "#999999");
$style_array=array("", "italics", "bold italics");

if (isset($_GET["action"]))							{$action=$_GET["action"];}
	elseif (isset($_POST["action"]))				{$action=$_POST["action"];}
if (isset($_GET["DB"]))								{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))					{$DB=$_POST["DB"];}
if (isset($_GET["chat_id"]))						{$chat_id=$_GET["chat_id"];}
	elseif (isset($_POST["chat_id"]))				{$chat_id=$_POST["chat_id"];}
if (isset($_GET["chat_level"]))						{$chat_level=$_GET["chat_level"];}
	elseif (isset($_POST["chat_level"]))			{$chat_level=$_POST["chat_level"];}
if (isset($_GET["chat_member_name"]))				{$chat_member_name=$_GET["chat_member_name"];}
	elseif (isset($_POST["chat_member_name"]))		{$chat_member_name=$_POST["chat_member_name"];}
if (isset($_GET["chat_message"]))					{$chat_message=$_GET["chat_message"];}
	elseif (isset($_POST["chat_message"]))			{$chat_message=$_POST["chat_message"];}
if (isset($_GET["lead_id"]))						{$lead_id=$_GET["lead_id"];}
	elseif (isset($_POST["lead_id"]))				{$lead_id=$_POST["lead_id"];}
if (isset($_GET["group_id"]))						{$group_id=$_GET["group_id"];}
	elseif (isset($_POST["group_id"]))				{$group_id=$_POST["group_id"];}
if (isset($_GET["user"]))							{$user=$_GET["user"];}
	elseif (isset($_POST["user"]))					{$user=$_POST["user"];}
if (isset($_GET["user_level"]))						{$user_level=$_GET["user_level"];}
	elseif (isset($_POST["user_level"]))			{$user_level=$_POST["user_level"];}
if (isset($_GET["keepalive"]))						{$keepalive=$_GET["keepalive"];}
	elseif (isset($_POST["keepalive"]))				{$keepalive=$_POST["keepalive"];}
if (isset($_GET["current_message_count"]))			{$current_message_count=$_GET["current_message_count"];}
	elseif (isset($_POST["current_message_count"]))	{$current_message_count=$_POST["current_message_count"];}
if (isset($_GET["language"]))						{$language=$_GET["language"];}
	elseif (isset($_POST["language"]))				{$language=$_POST["language"];}
if (isset($_GET["available_agents"]))				{$available_agents=$_GET["available_agents"];}
	elseif (isset($_POST["available_agents"]))		{$available_agents=$_POST["available_agents"];}
if (isset($_GET["show_email"]))						{$show_email=$_GET["show_email"];}
	elseif (isset($_POST["show_email"]))			{$show_email=$_POST["show_email"];}

$chat_member_name = preg_replace('/[^- \.\,\_0-9a-zA-Z]/',"",$chat_member_name);
if (!$user) {echo "No user, no using."; exit;}

$lead_id = preg_replace("/[^0-9]/","",$lead_id);
$chat_id = preg_replace('/[^- \_\.0-9a-zA-Z]/','',$chat_id);
$chat_level = preg_replace('/[^- \_\.0-9a-zA-Z]/','',$chat_level);
$group_id = preg_replace('/[^- \_0-9a-zA-Z]/','',$group_id);
$language = preg_replace('/[^-\_0-9a-zA-Z]/','',$language);
$chat_member_name = preg_replace("/\'|\"|\\\\|;/","",$chat_member_name);
$available_agents = preg_replace('/[^-\_0-9a-zA-Z]/','',$available_agents);
$show_email = preg_replace('/[^-\_0-9a-zA-Z]/','',$show_email);

if ($non_latin < 1)
	{
	$user = preg_replace('/[^- \'\+\_\.0-9a-zA-Z]/','',$user);
	$phone_number = preg_replace("/[^0-9]/","",$phone_number);
	}
else
	{
	$user = preg_replace("/\'|\"|\\\\|;/","",$user);
	}

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$VUselected_language='';
$stmt = "SELECT use_non_latin,enable_languages,language_method,default_language,chat_url,allow_chats FROM system_settings;";
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
	$chat_url =				$row[4];
	$SSallow_chats =		$row[5];
	}
$VUselected_language = $SSdefault_language;
##### END SETTINGS LOOKUP #####
###########################################

if (strlen($language) > 1)
	{
	$stmt = "SELECT language_code,language_description FROM vicidial_languages where language_id='$language' and active='Y';";
	$rslt=mysql_to_mysqli($stmt, $link);
			if ($mel > 0) {mysql_error_logging($NOW_TIME,$link,$mel,$stmt,'00XXX',$user,$server_ip,$session_name,$one_mysql_log);}
	if ($DB) {echo "$stmt\n";}
	$lang_good_ct = mysqli_num_rows($rslt);
	if ($lang_good_ct > 0)
		{
		$row=mysqli_fetch_row($rslt);
		$language_code =		$row[0];
		$language_description =	$row[1];
		$VUselected_language = $language;
		}
	}
if ($SSallow_chats < 1)
	{
	header ("Content-type: text/html; charset=utf-8");
	echo _QXZ("Error, chat disabled on this system");
	exit;
	}


if ($action=="send_message" && $chat_id) {
	$live_stmt="SELECT status from vicidial_live_chats where chat_id='$chat_id'";
	$live_rslt=mysql_to_mysqli($live_stmt, $link);

	if ($user && $chat_message && $chat_id && mysqli_num_rows($live_rslt)>0) {
		$live_row=mysqli_fetch_row($live_rslt);
		$status=$live_row[0];
		if ($status=="WAITING") {
			echo _QXZ("Chat is waiting for an agent").": $chat_id";
		} else {
			if ($status=="LIVE") {
				# Check that the customer is still in the chat.
				$active_stmt="SELECT * from vicidial_chat_participants where chat_id='$chat_id' and chat_member='$user'";
				$active_rslt=mysql_to_mysqli($active_stmt, $link);
				if (mysqli_num_rows($active_rslt)>0) {
					$ins_stmt="INSERT IGNORE INTO vicidial_chat_log(chat_id, message, poster, chat_member_name, chat_level) VALUES('$chat_id', '".mysqli_real_escape_string($link, $chat_message)."', '$user', '".mysqli_real_escape_string($link, $chat_member_name)."', '$chat_level')";
					$ins_rslt=mysql_to_mysqli($ins_stmt, $link);
					if (mysqli_affected_rows($link)<1) {
						echo "<font class='chat_title alert'>"._QXZ("SYSTEM ERROR")."</font><BR/>\n";
					}
				}
			} else {
				echo "<font class='chat_title alert'>"._QXZ("SYSTEM ERROR")."</font><BR/>\n";
			}
		}
	} else if (mysqli_num_rows($rslt)==0) {
		echo _QXZ("Chat has been closed").": $chat_id";
	}
}

if ($action=="leave_chat" && $user && $chat_id) { 
	if (!$chat_member_name) {$chat_member_name="Customer";}
	$del_stmt2="DELETE from vicidial_chat_participants where chat_id='$chat_id' and chat_member='$user'";
	$del_rslt2=mysql_to_mysqli($del_stmt2, $link);
	$deleted_participants=mysqli_affected_rows($link);
	if ($deleted_participants>0) {
		# ERASE THE CHAT IF THE PERSON NEVER GOT PICKED UP - I DON'T REMEMBER WHY I MOVED customer_leave_chat TO leave_chat
		$stmt="SELECT lead_id, status, chat_creator from vicidial_live_chats where chat_id='$chat_id'"; # and status='WAITING' and chat_creator='NONE'
		$rslt=mysql_to_mysqli($stmt, $link);
		if (mysqli_num_rows($rslt)>0) {
			$row=mysqli_fetch_row($rslt);
			$lead_id=$row[0];
			$chat_status=$row[1];
			$chat_creator=$row[2];

			if ($chat_status=="WAITING" && $chat_creator=="NONE") {
				# USE SPECIAL DROP STATUS 'CDROP' FOR DROPPED CHATS WHERE NO AGENT RESPONDED TO CHAT
				$upd_stmt="UPDATE vicidial_list set status='CDROP' where lead_id='$lead_id'";
				$upd_rslt=mysql_to_mysqli($upd_stmt, $link);
			
				$ins_stmt="INSERT IGNORE INTO vicidial_chat_archive SELECT chat_id, chat_start_time, 'DROP', chat_creator, group_id, lead_id From vicidial_live_chats where chat_id='$chat_id'";
				$ins_rslt=mysql_to_mysqli($ins_stmt, $link);

				$del_stmt="DELETE from vicidial_live_chats where chat_id='$chat_id'";
				$del_rslt=mysql_to_mysqli($del_stmt, $link);
			} else {
				# CHAT IS LIVE/AGENT NEEDS NOTIFICATION
				$ins_alert_stmt="INSERT INTO vicidial_chat_log(poster, chat_member_name, message_time, message, chat_id, chat_level) SELECT '$chat_creator', full_name, now(), '$chat_member_name has left chat', '$chat_id', '1' from vicidial_users where user='$chat_creator'";
				$ins_alert_rslt=mysql_to_mysqli($ins_alert_stmt, $link);
			}
		} 
	}
}

if ($action=="update_chat_window" && $chat_id) {
	$status_stmt="SELECT status, chat_creator from vicidial_live_chats where chat_id='$chat_id'";
	$status_rslt=mysql_to_mysqli($status_stmt, $link);
	if (mysqli_num_rows($status_rslt)==0) {
		# JCJ - comment out to display in Javascript function that calls this in parent page.
		# echo "<font class='chat_title alert'>"._QXZ("Chat does not exist or has been closed").": $chat_id</font><BR/><BR/><font class='chat_title'><a href='".$chat_url."?group_id=$group_id&language=$language&available_agents=$available_agents&show_email=$show_email'>"._QXZ("GO BACK TO CHAT FORM")."</a></font>\n";
		echo "Error|";

		# Create color-coding for archived chat
		$stmt="SELECT * from vicidial_chat_log_archive where chat_id='$chat_id' order by message_time asc";
		$rslt=mysql_to_mysqli($stmt, $link);
		$chat_members=array();
		while ($row=mysqli_fetch_row($rslt)) {
			if (!in_array("$row[4]", $chat_members)) {
				array_push($chat_members, "$row[4]");
			}
		}

		## GRAB ARCHIVED CHAT MESSAGES AND DISPLAY THEM
		if (!$user_level || $user_level==0) {$user_level_clause=" and chat_level='0' ";} else {$user_level_clause="";}
		$stmt="SELECT * from vicidial_chat_log_archive where chat_id='$chat_id' $user_level_clause order by message_time asc";
		$rslt=mysql_to_mysqli($stmt, $link);
		while ($row=mysqli_fetch_row($rslt)) {
			$chat_color_key=array_search("$row[4]", $chat_members);
			$row[2]=preg_replace('/\n/', '<BR/>', $row[2]);	
			echo "<li><font color='$color_array[$chat_color_key]' class='chat_message bold'>$row[5]</font> <font class='chat_timestamp bold'>($row[3])</font> - <font class='chat_message ".$style_array[$row[6]]."'>$row[2]</font></li>\n";
		}

	} else {
		$status_row=mysqli_fetch_row($status_rslt);
		
		## Modify user's ping date to verify they are still participating
		if ($user && $keepalive) {
			$upd_stmt="UPDATE vicidial_chat_participants set ping_date=now() where chat_member='$user' and chat_id='$chat_id'";
			$upd_rslt=mysql_to_mysqli($upd_stmt, $link);
		}

		## CHECK IF CHAT IS ACTIVE, IF SO GRAB DISTINCT USERS IN ORDER OF POST TO ASSIGN COLORS
		if ($status_row[0]=="LIVE") {
			$live_stmt="SELECT * from vicidial_live_chats vlc, vicidial_chat_participants vcp where vlc.chat_id='$chat_id' and status='LIVE' and vlc.chat_id=vcp.chat_id and vcp.chat_member='$user'";
			$live_rslt=mysql_to_mysqli($live_stmt, $link);
			if (mysqli_num_rows($live_rslt)>0) {
				echo "<font class='chat_title bold'>"._QXZ("Current chat").": $chat_id</font><BR/>\n";

				# Create color-coding for chat
				$stmt="SELECT * from vicidial_chat_log where chat_id='$chat_id' order by message_time asc";

				$rslt=mysql_to_mysqli($stmt, $link);
				$chat_members=array();
				while ($row=mysqli_fetch_row($rslt)) {
					if (!in_array("$row[4]", $chat_members)) {
						array_push($chat_members, "$row[4]");
					}
				}

				## GRAB CHAT MESSAGES AND DISPLAY THEM
				if (!$user_level || $user_level==0) {$user_level_clause=" and chat_level='0' ";} else {$user_level_clause="";}

				$stmt="SELECT * from vicidial_chat_log where chat_id='$chat_id' $user_level_clause order by message_time asc";

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
				echo "<font class='chat_title bold'>"._QXZ("You have left chat").": $chat_id.</font><BR/><BR/><font class='chat_title'><a href='".$chat_url."?group_id=$group_id&language=$language&available_agents=$available_agents&show_email=$show_email'>"._QXZ("GO BACK TO CHAT FORM")."</a></font>\n";
			}
		} else {
			if ($status_row[1]=="NONE") {
				echo "<font class='chat_title bold'>"._QXZ("Waiting for next available agent...")."</font><BR/>\n";
			} else {
				echo "<font class='chat_title alert'>"._QXZ("SYSTEM ERROR")."</font><BR/>\n";
			}
		}
	}
}

?>