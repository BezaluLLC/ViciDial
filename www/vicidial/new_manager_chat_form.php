<?php
require("dbconnect_mysqli.php");
require("functions.php");

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,allow_chats FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
if ($DB) {echo "$stmt\n";}
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =							$row[0];
	$SSallow_chats =						$row[1];
	}
##### END SETTINGS LOOKUP #####
###########################################

$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];
$PHP_SELF=$_SERVER['PHP_SELF'];
if ($non_latin < 1)
	{
	$PHP_AUTH_USER = preg_replace("/[^-_0-9a-zA-Z]/", "",$PHP_AUTH_USER);
	$PHP_AUTH_PW = preg_replace("/[^-_0-9a-zA-Z]/", "",$PHP_AUTH_PW);
	}	# end of non_latin
else
	{
	$PHP_AUTH_USER = preg_replace("/'|\"|\\\\|;/","",$PHP_AUTH_USER);
	$PHP_AUTH_PW = preg_replace("/'|\"|\\\\|;/","",$PHP_AUTH_PW);
	}
$auth=0;
$auth_message = user_authorization($PHP_AUTH_USER,$PHP_AUTH_PW,'',1);
if ($auth_message == 'GOOD')
	{$auth=1;}

if ($auth < 1)
	{
	$VDdisplayMESSAGE = _QXZ("Login incorrect, please try again");
	if ($auth_message == 'LOCK')
		{
		$VDdisplayMESSAGE = _QXZ("Too many login attempts, try again in 15 minutes");
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
		exit;
		}
	Header("WWW-Authenticate: Basic realm=\"CONTACT-CENTER-ADMIN\"");
	Header("HTTP/1.0 401 Unauthorized");
	echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$PHP_AUTH_PW|$auth_message|\n";
	exit;
	}

$stmt="select vla.user, vu.full_name, vu.user_group, vla.campaign_id, vc.campaign_name, vug.group_name from vicidial_users vu, vicidial_live_agents vla, vicidial_campaigns vc, vicidial_user_groups vug where vla.user=vu.user and vla.campaign_id=vc.campaign_id and vu.user_group=vug.user_group";
$rslt=mysql_to_mysqli($stmt, $link);
$user_array=array();
$user_group_array=array();
$campaign_id_array=array();
while ($row=mysqli_fetch_row($rslt)) {
	if (!in_array("$row[0]", $user_array)) {$user_array[$row[0]]="$row[1]";}
	if (!in_array("$row[2]", $user_group_array)) {$user_group_array["$row[2]"]="$row[5]";}
	if (!in_array("$row[3]", $campaign_id_array)) {$campaign_id_array["$row[3]"]="$row[4]";}
}
asort($user_array);
asort($user_group_array);
asort($campaign_id_array);

echo "<form action='/vicidial/manager_chat_interface.php' method='GET'>";
echo "<FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2><BR>";
if ($message) {echo "<B>$message</B><BR>";}
# echo "<br>"._QXZ("VICIDIAL Manager Chat Interface").":\n";
echo "<span id='ManagerChatAvailabilityDisplay'><TABLE width=750 cellspacing=1 cellpadding=1>\n";
echo "<TR><TD align='left' class='arial'>"._QXZ("VICIDIAL Manager Chat Interface").":</TD><TD align='right' class='arial_bold'><a link='#FFFF00' vlink='#FFFF00' href='manager_chat_interface.php'>["._QXZ("RELOAD")."]</a></TD></TR>";
echo "<TR BGCOLOR=BLACK>\n";
echo "<TD><font size=1 color=white width='50%'>"._QXZ("CURRENT LIVE AGENTS")."</TD>\n";
echo "<TD><font size=1 color=white width='50%'>"._QXZ("CURRENT LIVE CAMPAIGNS")."</TD></tr>\n";
echo "<TR BGCOLOR='#E6E6E6'>";
echo "<TD rowspan=3 valign='top'>";
echo "<select name='available_chat_agents[]' multiple size='12' style=\"width:350px\">\n";
if (count($user_array)==0) {echo "<option value=''>---- "._QXZ("NO LIVE AGENTS")." ----</option>";}
while (list($user, $full_name) = each($user_array)) {
	echo "<option value='$user'>$user - $full_name</option>\n";
}
echo "</select>\n";
echo "</TD>";
echo "<TD valign='top'>";
echo "<select name='available_chat_campaigns[]' multiple size='5' style=\"width:350px\">\n";
if (count($campaign_id_array)==0) {echo "<option value=''>---- "._QXZ("NO LIVE CAMPAIGNS")." ----</option>";}
while (list($campaign_id, $campaign_name) = each($campaign_id_array)) {
	echo "<option value='$campaign_id'>$campaign_id - $campaign_name</option>\n";
}
echo "</select>\n";
echo "</TD>";
echo "<TR BGCOLOR=BLACK>";
echo "<TD><font size=1 color=white>"._QXZ("CURRENT LIVE USER GROUPS")."</TD>\n";
echo "</TR>";
echo "<TR BGCOLOR='#E6E6E6'>";
echo "<TD valign='top'>";
echo "<select name='available_chat_groups[]' multiple size='5' style=\"width:350px\">\n";
if (count($user_group_array)==0) {echo "<option value=''>---- "._QXZ("NO LIVE USER GROUPS")." ----</option>";}
while (list($user_group, $group_name) = each($user_group_array)) {
	echo "<option value='$user_group'>$user_group - $group_name</option>\n";
}
echo "</select>\n";
echo "</TD>";
echo "</TR>";
echo "</table></span>";

# echo "<TR BGCOLOR='#E6E6E6'>";
# echo "<TD align='center' colspan='2'><input type='button' name='submit_chat' style='width: 150px' value='"._QXZ("CLEAR SELECTIONS")."'></TD>";
# echo "</TR>";

echo "<TABLE width=750 cellspacing=1 cellpadding=1>\n";
echo "<TR BGCOLOR=BLACK>\n";
echo "<TD align='left'><font size=1 color=white>"._QXZ("MESSAGE").":</font></td>";
echo "<TD align='left'><font size=1 color=white>"._QXZ("SEND TO").":</font></td>";
echo "</TR>";
echo "<TR BGCOLOR='#E6E6E6'>\n";
echo "<TD align='left'>";
echo "<textarea rows='5' cols='50' name='manager_message' id='manager_message'></textarea><BR><BR>";
echo "<input type='checkbox' name='allow_replies' id='allow_replies' value='Y' checked><font size='1'>"._QXZ("Allow agent replies")."</font>";
echo "</TD>";
echo "<td align='center' valign='middle'>";
echo "<input type='submit' name='submit_chat' style='width: 150px' value='"._QXZ("SELECTED AGENTS")."'><BR><BR><BR>";
echo "<input type='submit' name='submit_chat' style='width: 150px' value='"._QXZ("ALL LIVE AGENTS")."'>";
echo "</td>";
echo "</TR>";
echo "</table>";
echo "</form>";
echo "<BR>";

$reload_function="setInterval(\"RefreshChatDisplay()\", 30000);\n";
echo "<script language=\"JavaScript\">\n";
echo "$reload_function";
echo "</script>\n";
?>
