<?php
# help.php - VICIDIAL administration page
#
# Copyright (C) 2014  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
# 

# CHANGELOG:
# 131019-0848 - Moved help from admin.php
# 131029-2058 - Added auto-restart asterisk help
# 131208-1635 - Added help for max dead, dispo and pause time campaign options
# 140126-1023 - Added VMAIL_NO_INST options
# 140126-2254 - Added voicemail_instructions option for phones
# 140404-1104 - Added new DID filter options
# 140418-0915 - Added users and campaigns max_inbound_calls
# 140423-1637 - Added manual_dial_search_checkbox and hide_call_log_info
# 140425-0912 - Added modify_custom_dialplans
# 140425-1306 - Added queuemetrics_pause_type
# 140509-2211 - Added frozen_server_call_clear
# 140521-2020 - Changed alt_number_dialing and added timer_alt_seconds
#


require("dbconnect_mysqli.php");
require("functions.php");

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,enable_queuemetrics_logging,enable_vtiger_integration,qc_features_active,outbound_autodial_active,sounds_central_control_active,enable_second_webform,user_territories_active,custom_fields_enabled,admin_web_directory,webphone_url,first_login_trigger,hosted_settings,default_phone_registration_password,default_phone_login_password,default_server_password,test_campaign_calls,active_voicemail_server,voicemail_timezones,default_voicemail_timezone,default_local_gmt,campaign_cid_areacodes_enabled,pllb_grouping_limit,did_ra_extensions_enabled,expanded_list_stats,contacts_enabled,alt_log_server_ip,alt_log_dbname,alt_log_login,alt_log_pass,tables_use_alt_log_db,call_menu_qualify_enabled,admin_list_counts,allow_voicemail_greeting,svn_revision,allow_emails,level_8_disable_add,pass_key,pass_hash_enabled,disable_auto_dial,country_code_list_stats FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
if ($DB) {echo "$stmt\n";}
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =							$row[0];
	$SSenable_queuemetrics_logging =		$row[1];
	$SSenable_vtiger_integration =			$row[2];
	$SSqc_features_active =					$row[3];
	$SSoutbound_autodial_active =			$row[4];
	$SSsounds_central_control_active =		$row[5];
	$SSenable_second_webform =				$row[6];
	$SSuser_territories_active =			$row[7];
	$SScustom_fields_enabled =				$row[8];
	$SSadmin_web_directory =				$row[9];
	$SSwebphone_url =						$row[10];
	$SSfirst_login_trigger =				$row[11];
	$SShosted_settings =					$row[12];
	$SSdefault_phone_registration_password =$row[13];
	$SSdefault_phone_login_password =		$row[14];
	$SSdefault_server_password =			$row[15];
	$SStest_campaign_calls =				$row[16];
	$SSactive_voicemail_server =			$row[17];
	$SSvoicemail_timezones =				$row[18];
	$SSdefault_voicemail_timezone =			$row[19];
	$SSdefault_local_gmt =					$row[20];
	$SScampaign_cid_areacodes_enabled =		$row[21];
	$SSpllb_grouping_limit =				$row[22];
	$SSdid_ra_extensions_enabled =			$row[23];
	$SSexpanded_list_stats =				$row[24];
	$SScontacts_enabled =					$row[25];
	$SSalt_log_server_ip =					$row[26];
	$SSalt_log_dbname =						$row[27];
	$SSalt_log_login =						$row[28];
	$SSalt_log_pass =						$row[29];
	$SStables_use_alt_log_db =				$row[30];
	$SScall_menu_qualify_enabled =			$row[31];
	$SSadmin_list_counts =					$row[32];
	$SSallow_voicemail_greeting =			$row[33];
	$SSsvn_revision =						$row[34];
	$SSallow_emails =						$row[35];
	$SSlevel_8_disable_add =				$row[36];
	$SSpass_key =							$row[37];
	$SSpass_hash_enabled =					$row[38];
	$SSdisable_auto_dial =					$row[39];
	$SScountry_code_list_stats =			$row[40];
	}
##### END SETTINGS LOOKUP #####
###########################################

$PHP_AUTH_USER=$_SERVER['PHP_AUTH_USER'];
$PHP_AUTH_PW=$_SERVER['PHP_AUTH_PW'];

$PHP_AUTH_PW = preg_replace('/\'|\"|\\\\|;/', '',$PHP_AUTH_PW);
$PHP_AUTH_USER = preg_replace('/\'|\"|\\\\|;/', '',$PHP_AUTH_USER);

$user_auth=0;
$auth=0;
$reports_auth=0;
$qc_auth=0;
$auth_message = user_authorization($PHP_AUTH_USER,$PHP_AUTH_PW,'QC',1);
if ($auth_message == 'GOOD')
	{$user_auth=1;}

if ($user_auth > 0)
	{
	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 7;";
	if ($DB) {echo "|$stmt|\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$auth=$row[0];

	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 6 and view_reports > 0;";
	if ($DB) {echo "|$stmt|\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$reports_auth=$row[0];

	$stmt="SELECT count(*) from vicidial_users where user='$PHP_AUTH_USER' and user_level > 1 and qc_enabled > 0;";
	if ($DB) {echo "|$stmt|\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$row=mysqli_fetch_row($rslt);
	$qc_auth=$row[0];

	$reports_only_user=0;
	$qc_only_user=0;
	if ( ($reports_auth > 0) and ($auth < 1) )
		{
		$ADD=999999;
		$reports_only_user=1;
		}
	if ( ($qc_auth > 0) and ($reports_auth < 1) and ($auth < 1) )
		{
		if ( ($ADD != '881') and ($ADD != '100000000000000') )
			{
            $ADD=100000000000000;
			}
		$qc_only_user=1;
		}
	if ( ($qc_auth < 1) and ($reports_auth < 1) and ($auth < 1) )
		{
		$VDdisplayMESSAGE = "You do not have permission to be here";
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
		exit;
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



######################
# display the HELP SCREENS
######################

header ("Content-type: text/html; charset=utf-8");
echo "</title>\n";
echo "</head>\n";
echo "<BODY BGCOLOR=white marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>\n";
echo "<CENTER>\n";
echo "<TABLE WIDTH=98% BGCOLOR=#E6E6E6 cellpadding=2 cellspacing=0><TR><TD ALIGN=LEFT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=4><B>ADMINISTRATION: HILFE<BR></B></FONT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2><BR><BR>\n";

?>
<B><FONT SIZE=3>Benutzer-Tabelle</FONT></B><BR><BR>
<A NAME="users-user">
<BR>
<B>User ID -</B> Dieses Feld ist, wo Sie die Benutzer-ID-Nummer setzen, können bis zu 8 Stellen lang sein, muss mindestens zwei Zeichen lang sein.

<BR>
<A NAME="users-pass">
<BR>
<B>Password -</B> Dieses Feld ist, wo Sie die Benutzer Passwort. Muss mindestens 2 Zeichen lang sein. Eine starke Benutzer-Passwort sollte mindestens 8 Zeichen lang sein und Groß-und Kleinbuchstaben sowie mindestens eine Zahl.

<BR>
<A NAME="users-force_change_password">
<BR>
<B>Zwingen Sie Passwort ändern -</B> Wenn diese Option gesetzt, um dann Y der Benutzer aufgefordert, ihr Passwort ändern sie das nächste Mal in der Verwaltung Webseite anzumelden. Standard ist N.

<BR>
<A NAME="users-last_login_date">
<BR>
<B>Letzte Anmeldung Info -</B> Dies zeigt die letzten Anmeldeversuch Datum und Zeit, und wenn es hat eine jüngste fehlgeschlagenen Anmeldeversuch. Wenn dies ändern Benutzerformular eingereicht wird, dann werden die fehlgeschlagenen Anmeldeversuch Zähler zurückgesetzt werden und der Agent kann sofort versuchen, sich erneut anzumelden. Wenn ein Agent hat 10 fehlgeschlagenen Anmeldeversuchen in Folge dann werden sie nicht versuchen, kann sich erneut anmelden für mindestens 15 Minuten, es sei denn, ihr Konto manuell zurückgesetzt.

<BR>
<A NAME="users-full_name">
<BR>
<B>Voller Name -</B> Dieses Feld ist, wo Sie den Benutzern vollen Namen setzen. Muss mindestens 2 Zeichen lang.

<BR>
<A NAME="users-user_level">
<BR>
<B>Benutzer-Niveau -</B> Dieses Menü ist, wo Sie die Benutzer auf Benutzerebene zu wählen. Muss ein Niveau von 1, in dem Agenten-Bildschirm anmelden können, muss eben größer als 2 sein, so loggen Sie sich ein genauerer, müssen Benutzer Stufe 8 oder höher sein, um in Web-Admin-Abschnitt erhalten.

<BR>
<A NAME="users-user_group">
<BR>
<B>Benutzer-Gruppe -</B> Dieses Menü ist, wo Sie die Benutzergruppe, die diese Benutzer angehören wählen. Es gibt mehrere Mittel-Screen-Funktionen, die über die Benutzergruppeneinstellungen gesteuert werden können. Wenn dieses Feld leer ist, dann kann der Benutzer sich nicht bei dem Agenten-Bildschirm.

<BR>
<A NAME="users-phone_login">
<BR>
<B>Telefon-LOGON -</B> Hier ist, wo können Sie eine Standardtelefon Login Wert gesetzt, wenn sich der Benutzer in den Agenten-Bildschirm. Dieser Wert wird den phone_login automatisch zu füllen, wenn sich der Benutzer mit ihrem Benutzerpass-Kampagne in der Agenten-Login-Bildschirm.

<BR>
<A NAME="users-phone_pass">
<BR>
<B>Telefon-Durchlauf -</B> Hier ist, wo können Sie eine Standardtelefon Pass Wert gesetzt, wenn sich der Benutzer in den Agenten-Bildschirm. Dieser Wert wird den phone_pass automatisch zu füllen, wenn sich der Benutzer mit ihrem Benutzerpass-Kampagne in der Agenten-Login-Bildschirm.

<BR>
<A NAME="users-active">
<BR>
<B>Aktive -</B> Dieses Feld legt fest, ob der Benutzer im System aktiv und kann als Agent oder Manager anmelden. Standardwert ist Y.

<BR>
<A NAME="users-voicemail_id">
<BR>
<B>Voicemail-ID -</B> Dies ist die Voicemail-Box, die Anrufe in einer in-group AGENTDIRECT wird am Tropfen Zeit gerichtet, wenn die in-group hat die Drop-Methode gesetzt, um Sprachnachrichten und Voicemail-Feld auf AGENTVMAIL.

<BR>
<A NAME="users-optional">
<BR>
<B>E-Mail, User-Code und Territorium -</B> Dies sind optionale Felder.

<BR>
<A NAME="users-hotkeys_active">
<BR>
<B>Hot Keys aktiv -</B> Diese Wahl, wenn Satz bis 1 dem Benutzer erlaubt,die Hot Keys schnelle-dispositioning Funktion innen zu verwenden the agent screen.

<BR>
<A NAME="users-agent_choose_ingroups">
<BR>
<B>Mittel wählen Ingroups -</B> Diese Wahl, wenn Satz bis 1 dem Benutzererlaubt, die ingroups zu wählen, denen sie Anrufe von wenn sie LOGONzu einer GENAUEREN oder INBOUND Kampagne empfangen. Andernfalls mußder Manager dieses auf ihrem Benutzersonderkommandoschirm der adminSeite einstellen.

<BR>
<A NAME="users-agent_choose_blended">
<BR>
<B>Wählen Sie Blended-Agent -</B> Diese Option, wenn dieser Wert auf 1 kann der Anwender wählen, ob der Agent hat ihre Kampagne zu Blended-Set oder nicht, und wenn nicht, dann wird die Standard-Einstellung wird gemischt verwendet werden. Standard ist 1 für aktivierte.

<BR>
<A NAME="users-agent_choose_territories">
<BR>
<B>Wählen Sie Agent Territories -</B> Diese Option, wenn auf 1 gesetzt, kann der Anwender für die Gebiete wählen, dass sie, wenn sie Anrufe aus dem Login erhalten zu einem manuellen oder INBOUND_MAN Kampagne. Andernfalls wird der Benutzer eingestellt werden, dass alle Gebiete, die sie gesetzt sind, um in der User-Gebieten gehören Verwaltungs-Abschnitt verwenden.

<BR>
<A NAME="users-scheduled_callbacks">
<BR>
<B>Scheduled Callbacks -</B> Diese Option ermöglicht es einem Agenten um einen Anruf zu Verfügung als CALLBK und wählen Sie das Datum und die Uhrzeit, zu der die Führung wieder aktiviert werden.

<BR>
<A NAME="users-agentonly_callbacks">
<BR>
<B>Mittel-Nur Wiederholungsbesuche -</B> Diese Wahl erlaubt einemVertreter, einen Wiederholungsbesuch einzustellen, damit sie daseinzige Mittel sind, das die Kunde Rückseite benennen kann. Dieseserlaubt auch dem Vertreter, ihre Wiederholungsbesuch Auflistungen zusehen und sie zurück zu benennen, immer wenn sie zu wünschen.

<BR>
<A NAME="users-agentcall_manual">
<BR>
<B>Vertreter-Anruf-Handbuch -</B> Diese Option ermöglicht es ein Mittel, um eine neue Führung manuell eingeben in das System und sie nennen. Dies ermöglicht auch die Berufung eines jeden Telefonnummer von ihr Agent Bildschirm und Puts, die in ihrer Sitzung aufrufen. Verwenden Sie diese Option mit Vorsicht.

<BR>
<A NAME="users-agentcall_email">
<BR>
<B>Agent Call Email -</B> This option is disabled.

<BR>
<A NAME="users-agent_recording">
<BR>
<B>Agent-Recording -</B> Diese Option kann einen Agenten zu tun, keine Aufnahmen zu verhindern, nachdem sie sich bei dem Agenten-Bildschirm. Diese Option muss für den Agenten-Bildschirm sein, um die Aufnahmeeinstellungen Kampagne folgen.

<BR>
<A NAME="users-agent_transfers">
<BR>
<B>Agent-Transfers -</B> Konferenzsitzung in der Agenten-Bildschirm - Mit dieser Option können einen Agenten Öffnen der Transfer von zu verhindern. Wenn diese deaktiviert ist, kann der Agent nicht Dritten Anruf oder Blindtransfer Anrufe.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-closer_default_blended">
	<BR>
	<B>Genauere Rückstellung gemischt -</B> Diese Wahl fällt einfach dasgemischte checkbox auf einem GENAUEREN LOGON-Schirm zurück.
	<?php
	}
?>

<BR>
<A NAME="users-agent_recording_override">
<BR>
<B>Agent-Recording Override -</B> Diese Option wird überschreiben, was auch immer die Option in der Kampagne für die Aufnahme. BEHINDERTE die Kampagne Aufnahmeeinstellung nicht überschreiben. NIEMALS wird die Aufnahme auf dem Client zu deaktivieren. ONDEMAND ist der Standard und ermöglicht den Agenten zu starten und stoppen Sie die Aufnahme, wie gebraucht. ALLCALLS wird die Aufnahme auf dem Client zu starten, wenn ein Anruf an einen Agenten geschickt. ALLFORCE wird die Aufzeichnung auf dem Client zu starten, wenn ein Anruf an einen Agenten gibt dem Agenten keine Option, um die Aufnahme zu stoppen gesendet. Für ALLCALLS und ALLFORCE gibt es eine Option, um die Aufnahmeverzögerung zu verwenden, um auf sehr kurzen Aufnahmen senken und reduzieren die Systemlast.

<BR>
<A NAME="users-agent_shift_enforcement_override">
<BR>
<B>Agent Shift Vollstreckung Override -</B> Diese Einstellung überschreibt, was die Benutzer Benutzer-Gruppe hat für die Vollstreckung Shift. DEAKTIVIERT wird die Benutzergruppe Einstellung. AUS wird nicht in allen Schichten durchzusetzen. START wird nur die Durchsetzung der Login-Zeit, sondern wirkt sich nicht auf einen Vertreter, der mit über die Verlagerung, wenn sie bereits eingeloggt ALL wird durchzusetzen Verlagerung starten und meldet einen Agenten aus, nachdem sie über das Ende ihrer Schicht Zeit. Option ist standardmäßig deaktiviert.

<BR>
<A NAME="users-agent_call_log_view_override">
<BR>
<B>Agent-Call Log anzeigen Override -</B> Diese Einstellung hat Vorrang vor was auch immer der Benutzer Benutzer-Gruppe hat für Agent Call Log Ansicht eingestellt. Deaktiviert wird, verwenden Sie die Benutzergruppe Einstellung. N wird nicht zulassen, welche die Benutzer Anrufliste. Y ermöglicht, welche die Benutzer Anrufliste. Der Standardwert ist DISABLED.

<BR>
<A NAME="users-agent_lead_search_override">
<BR>
<B>Agent-Lead-Suche Override -</B> Diese Einstellung hat Vorrang, was die Kampagne für Agent Lead-Suche eingestellt. NOT_ACTIVE verwenden die Kampagne Einstellung. ENABLED ermöglicht Blei Suche und DEAKTIVIERT werden nicht zulassen, Blei Suche. Die Standardeinstellung ist NOT_ACTIVE. LIVE_CALL_INBOUND wird Suche nach einer Führung während eines eingehenden Anrufs nur zu ermöglichen. LIVE_CALL_INBOUND_AND_MANUAL wird Suche nach einer Führung während eines eingehenden Anrufs oder während der Pause zu ermöglichen. Als Lead-Suche auf einem Live eingehenden Anruf verwendet wird, wird die Führung des Gesprächs, wenn es um den Agenten gingen zu einem Status LSMERG geändert werden, und die Protokolle für den Anruf wird modifiziert, um dem Agenten eine Verknüpfung ausgewählt Blei stattdessen.

<BR>
<A NAME="users-alert_enabled">
<BR>
<B>Alarm aktiv -</B> Dieses Feld zeigt an, ob der Agent Web-Browser Warnung bei Anrufen in ihr Agent screen-Session gekommen aktiviert. Standard ist 0 für NEIN.

<BR>
<A NAME="users-allow_alerts">
<BR>
<B>Lassen Sie Alerts -</B> Dieses Feld gibt Ihnen die Fähigkeit, damit Agent Browser-Warnmeldungen, die von den Agenten für Anrufe, wenn in ihr Agent screen-Session gekommen aktiviert sein. Standard ist 0 für NEIN.

<BR>
<A NAME="users-preset_contact_search">
<BR>
<B>Preset Kontakt Suche -</B> Wenn der Benutzer in eine Kampagne, die Transfer-Presets eingestellt hat KONTAKTE angemeldet ist, dann kann diese Einstellung deaktivieren Kontakt Suche nur für diesen Benutzer. Standard ist die NOT_ACTIVE verwenden, was auch immer die Kampagne Einstellung wird.

<BR>
<A NAME="users-max_inbound_calls">
<BR>
<B>Max eingehende Anrufe -</B> Wenn diese Einstellung auf eine Zahl größer als 0 gesetzt wird, dann wird es die maximale Anzahl der eingehenden Anrufe, die ein Agent für alle eingehenden Gruppen an einem Tag zu bewältigen. Wenn der Agent die maximale Anzahl der eingehenden Anrufe erreicht, dann werden sie nicht in der Lage, eingehende Gruppen auswählen, um Anrufe von bis zum nächsten Tag zu nehmen. Diese Einstellung zeigt das Kampagnen-Einstellung mit dem gleichen Namen überschreiben. Standard ist 0 für Behinderte.

<BR>
<A NAME="users-campaign_ranks">
<BR>
<B>Kampagne ordnet - in diesem Abschnitt können Sie den Rank definieren,den ein Mittel für jede Kampagne hat. Diese Rank können gewohntSEIN,bevorzugte Anrufwegewahl zuzulassen, wenn folgender Vertreter-Anrufauf campaign_rank eingestellt wird. Auch in diesem Abschnitt sind die WEB VARs für jede Kampagne. Diese ermöglichen es jedem Agenten zu haben, eine andere Variable Zeichenfolge, die hinzugefügt werden können, um die Web-Formular oder SCRIPT Registerkarte URLs, indem Sie einfach setzen - A - web_vars - B - wie Sie würde jedem anderen Bereich. Ein weiterer Bereich, in diesem Abschnitt ist GRADE, das es ermöglicht, den nächsten Agenten campaign_grade_random Rufen Einstellung verwendet werden. Diese Sorte Einstellung erhöht oder verringert sich die Wahrscheinlichkeit, dass der Agent den Anruf erhalten.

<BR>
<A NAME="users-closer_campaigns">
<BR>
<B>Inbound Gruppen -</B> Ist hier, wo Sie die inbound Gruppen vorwählen,die Sie Anrufe von empfangen möchten, wenn Sie die GENAUERE Kampagnevorgewählt haben. SieSIND auch in der Lage, den Rank oder Fähigkeit Niveaueinzustellen, in diesem Abschnitt für jede der inbound Gruppen sowieIn der LageSEIN, die Zahl den Anrufen zu sehen, die von jeder inboundGruppe für dieses spezifische Mittel empfangen werden. Auch in diesem Abschnitt ist die Fähigkeit, dem Mittel einen Rankfür jede inbound Gruppe zu geben. Diese Rank können für bevorzugteAnrufwegewahl verwendet werden, wenn diese Wahl auf dem IngruppeSchirm vorgewählt wird. Auch in diesem Abschnitt sind die WEB VARs für jede Kampagne. Diese ermöglichen es jedem Agenten zu haben, eine andere Variable Zeichenfolge, die hinzugefügt werden können, um die Web-Formular oder SCRIPT Registerkarte URLs, indem Sie einfach setzen - A - web_vars - B - wie Sie würde jedem anderen Bereich.

<BR>
<A NAME="users-alter_custdata_override">
<BR>
<B>Mittel ändern Kunde Daten-Übersteuerung -</B> diese Wahl läuft über,was auch immer die Wahl in der Kampagne für das Ändern von KundeDaten ist. NOT_ACTIVE verwendet, was auch immer Einstellung für dieKampagne anwesend ist. ALLOW_ALTER darf immer, damit das Mittel dieKunde Daten ändert, egal was die Kampagne Einstellung ist.Rückstellung ist NOT_ACTIVE.

<BR>
<A NAME="users-alter_custphone_override">
<BR>
<B>Agent Alter Kunden Telefon Override - </B> Mit dieser Option werden die unabhängig von der Option ist in der Kampagne für die Änderung der Kunde die Rufnummer ein. NOT_ACTIVE wird unabhängig Einstellung ist für die Kampagne. ALLOW_ALTER wird immer für den Agenten, um die Kunden-Nummer, egal, was die Kampagne Einstellung. Der Standardwert ist NOT_ACTIVE.

<BR>
<A NAME="users-custom_one">
<BR>
<B>Benutzerdefinierte Felder -</B> Diese fünf Felder können für verschiedene Zwecke verwendet werden, und sie können in das Web-Formular Adressen und Skripte als user_custom_one und so weiter gefüllt werden. Die Custom 5-Feld als Feld genutzt werden kann, um einen Dialplan Zahl definieren, um einen Anruf zu senden aus einer in-group AGENTDIRECT, wenn der Benutzer nicht verfügbar ist, brauchen Sie nur AGENTEXT in der Nachricht oder Erweiterungskörper setzen in der AGENTDIRECT in-group und das System sucht diesen Brauch fünf Feld und schickt den Anruf an diesem Dialplan Zahl.

<BR>
<A NAME="users-alter_agent_interface_options">
<BR>
<B>Ändern Sie Mittel-Schnittstelle Wahlen -</B> diese Wahl, wenn Satzbis 1 dem administrativen Benutzer erlaubt, die MittelschnittstelleWahlen in admin.php zu ändern.

<BR>
<A NAME="users-delete_users">
<BR>
<B>Löschung-Benutzer -</B> Diese Wahl, wenn Satz bis 1 dem Benutzererlaubt, andere Benutzer des Gleichgestellten oder wenigenBenutzerniveaus aus dem System zu löschen.

<BR>
<A NAME="users-delete_user_groups">
<BR>
<B>Löschung-Benutzer-Gruppen -</B> Diese Wahl, wenn Satz bis 1 demBenutzer erlaubt, Benutzergruppen aus dem System zu löschen.

<BR>
<A NAME="users-delete_lists">
<BR>
<B>Löschung-Listen -</B> Diese Option, wenn auf 1 gesetzt, kann der Benutzer Listen aus dem System löschen.

<BR>
<A NAME="users-delete_campaigns">
<BR>
<B>Löschung-Kampagnen -</B> Diese Option, wenn auf 1 gesetzt, kann der Benutzer Kampagnen aus dem System löschen.

<BR>
<A NAME="users-delete_ingroups">
<BR>
<B>Löschung In-Gruppen -</B> Diese Option, wenn auf 1 gesetzt, kann der Benutzer aus dem System löschen Inbound Gruppen.

<BR>
<A NAME="users-modify_custom_dialplans">
<BR>
<B>Ändern Benutzerdefinierte DialPlans -</B> Diese Option, wenn auf 1 gesetzt, kann der Anwender benutzerdefinierte Dialplan Einträge, die verfügbar sind in der Anruf Menü, Systemeinstellungen und Server Modifikation Bildschirme anzeigen und ändern.

<BR>
<A NAME="users-delete_remote_agents">
<BR>
<B>Löschung-Direktübertragung Mittel -</B> Diese Option, wenn auf 1 gesetzt, kann der Benutzer Remote-Agenten aus dem System löschen.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-load_leads">
	<BR>
	<B>Last Leitungen -</B> Diese Option, wenn auf 1 gesetzt, kann der Benutzer führen Listen über die Web-basierte Führung Lader in der Liste Tabelle laden.
	<?php
	}
if ($SScustom_fields_enabled > 0)
	{
	?>
	<BR>
	<A NAME="users-custom_fields_modify">
	<BR>
	<B>Benutzerdefinierte Felder bearbeiten -</B> Diese Option, wenn auf 1 gesetzt, kann der Anwender benutzerdefinierte Liste Felder ändern.
	<?php
	}
?>

<BR>
<A NAME="users-campaign_detail">
<BR>
<B>Kampagne Detail -</B> Diese Wahl, wenn Satz bis 1 dem Benutzererlaubt, die Kampagne Detail-Schirmelemente anzusehen und zu ändern.

<BR>
<A NAME="users-ast_admin_access">
<BR>
<B>AGC Admin Zugang -</B> Diese Wahl, wenn Satz bis 1 den Benutzer zumLOGON zu den astGUIclient admin Seiten erlaubt.

<BR>
<A NAME="users-ast_delete_phones">
<BR>
<B>AGC Löschung-Telefone -</B> Diese Wahl, wenn Satz bis 1 dem Benutzererlaubt, Telefoneintragungen in den astGUIclient admin Seiten zulöschen.

<BR>
<A NAME="users-delete_scripts">
<BR>
<B>Löschung-Indexe -</B> Diese Wahl, wenn Satz bis 1 dem Benutzererlaubt, Kampagne Indexe auf dem Indexänderung Schirm zu löschen.

<BR>
<A NAME="users-modify_leads">
<BR>
<B>Ändern Sie Leitungen -</B> Diese Wahl, wenn Satz bis 1 dem Benutzererlaubt, Leitungen in der admin Abschnittleitung Suchresultate Seitezu ändern.

<?php
if ($SSallow_emails>0)
	{
?>
<BR>
<A NAME="users-modify_email_accounts">
<BR>
<B>Ändern E-Mail-Konten -</B> Diese Option, wenn auf 1 gesetzt dem Benutzer, E-Mail-Konten in der E-Mail-Account-Management-Seite ändern können.
<?php
	}
?>

<BR>
<A NAME="users-change_agent_campaign">
<BR>
<B>Ändern Sie Vertreter-Kampagne -</B> Diese Wahl, wenn Satz bis 1 demBenutzer erlaubt, die Kampagne zu ändern, daß ein Mittel in geloggtwird, während sie in es geloggt werden.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-delete_filters">
	<BR>
	<B>Löschung-Filter -</B> Diese Option ermöglicht es dem Benutzer, um Blei-Filter aus dem System zu löschen.
	<?php
	}
?>

<BR>
<A NAME="users-delete_call_times">
<BR>
<B>Lösche Anrufzeits -</B> Diese Option ermöglicht es dem Benutzer, um Anrufzeiten Aufzeichnungen und Zustand Anrufzeiten Datensätze aus dem System zu löschen sein.

<BR>
<A NAME="users-modify_call_times">
<BR>
<B>Ändere Anrufzeiten -</B> Diese Option erlaubt dem Nutzer das Ansehen und Ändern der Anrufzeiten und Status Anrufzeiten Aufzeichnungen. Ein Nutzer braucht diese Option nicht aktiviert, wenn er nur die Anrufzeit Option auf der Kampagnen-Sicht ändern können muss.

<BR>
<A NAME="users-modify_sections">
<BR>
<B>Ändern Sie Abschnitte -</B> diese Wahlen erlauben dem Benutzer, jedesanzusehen und zu ändern Abschnittaufzeichnungen. Wenn Satz bis 0, derBenutzer in der LageIST, die Abschnittliste, aber nicht das Detailoder den Änderung Schirm einer Aufzeichnung in diesem Abschnitt zusehen.

<BR>
<A NAME="users-view_reports">
<BR>
<B>View Berichte -</B> Diese Option ermöglicht es dem Benutzer, die Web-System Berichte anzeigen.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="users-qc_enabled">
	<BR>
	<B>QC aktiv - </B> Diese Option ermöglicht es dem Benutzer, um sich in der Qualitätskontrolle Agent-Bildschirm.

	<BR>
	<A NAME="users-qc_user_level">
	<BR>
	<B>QC-Benutzer-Niveau - </B> Diese Einstellung legt fest, was die Qualität der User-Agent ist. Dies wird diktieren der Ebene der Funktionalität für den Agenten in der QC-Abschnitt:<BR>
	1 - Nichts ändern<BR>
	2 - Ändern Sie nichts außer Status<BR>
	3 - Ändern Sie alle Felder<BR>
	4 - Überprüfen ersten QC<BR>
	5 - QC Statistiken anzeigen<BR>
	6 - Möglichkeit, Aufzeichnungen FINISHED<BR>
	7 - Manager Level<BR>

	<BR>
	<A NAME="users-qc_pass">
	<BR>
	<B>QC-Datensatz Pass - </B> Diese Option ermöglicht es dem Agenten, um anzugeben, dass ein Datensatz hat die erste Runde der QC nach Überprüfung der Aufnahme.

	<BR>
	<A NAME="users-qc_finish">
	<BR>
	<B>QC-Datensatz Finish - </B> Diese Option ermöglicht es dem Agenten, um anzugeben, dass eine Aufzeichnung beendet ist die zweite Runde der QC nach Überprüfung durch die Aufnahme.

	<BR>
	<A NAME="users-qc_commit">
	<BR>
	<B>QC-Datensatz Commit - </B> Diese Option ermöglicht es dem Agenten festlegen, dass ein Datensatz wurde, die in QC. Es kann nicht mehr geändert werden, indem man.
	<?php
	}
?>

<BR>
<A NAME="users-add_timeclock_log">
<BR>
<B>AddTimeclock Log Record - </B> Diese Option ermöglicht es dem Benutzer, um an das timeclock Log.

<BR>
<A NAME="users-modify_timeclock_log">
<BR>
<B>ÄndernTimeclock Log Record - </B> Diese Option ermöglicht es dem Benutzer zu ändern, Einträge in der Log-timeclock.

<BR>
<A NAME="users-delete_timeclock_log">
<BR>
<B>Löschen der RegistrierungTimeclock Record - </B> Diese Option ermöglicht es dem Benutzer zu löschen, Einträge in der Log-timeclock.

<BR>
<A NAME="users-vdc_agent_api_access">
<BR>
<B>Agent API Access -</B> Diese Option ermöglicht das Konto, mit dem Agenten und Nicht-Agenten-API-Befehle verwendet werden.

<BR>
<A NAME="users-manager_shift_enforcement_override">
<BR>
<B>Shift Manager Vollstreckung Override -</B> Diese Einstellung, wenn auf 1 gesetzt wird ein Manager, um ihre Benutzer und Kennwort für ein Agent-Bildschirm, um die Verlagerung Beschränkungen auf einen Agenten Sitzung, wenn der Agent versucht, sich außerhalb ihrer Schicht. Der Standardwert ist 0.

<BR>
<A NAME="users-download_lists">
<BR>
<B>Download Listen -</B> Diese Einstellung, wenn auf 1 gesetzt wird ein Manager zu klicken Sie auf den Download-Link-Liste am unteren Rand einer Liste Änderung Bildschirm zu exportieren den gesamten Inhalt eines Verzeichnisses auf eine flache Datei. Der Standardwert ist 0.

<BR>
<A NAME="users-export_reports">
<BR>
<B>Exportieren von Berichten -</B> Diese Einstellung, wenn es auf 1 gesetzt wird es ein Manager, um den Export Call-und Blei-Berichte über den Bildschirm Berichte zugreifen. Standard ist 0. Für den Export Calls und Leads Berichte wurde die folgende Reihenfolge der Felder für den Export verwendet: <BR>call_date, phone_number_dialed, status, user, full_name, campaign_id/in-group, vendor_lead_code, source_id, list_id, gmt_offset_now, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, length_in_sec, user_group, alt_dial/queue_seconds, rank, owner

<BR>
<A NAME="users-delete_from_dnc">
<BR>
<B>Löschen von DNC-Listen -</B> Diese Einstellung, wenn auf 1 gesetzt, wird es ein Manager, um Telefonnummern aus den DNC-Listen in dem System zu entfernen.

<BR>
<A NAME="users-realtime_block_user_info">
<BR>
<B>Realtime Block User Info -</B> Diese Einstellung, wenn es auf 1 gesetzt werden Benutzer und Informationen vor dem Bahnhof in der Real-time-Bericht angezeigt zu blockieren. Standard ist 0 für Behinderte.

<BR>
<A NAME="users-modify_same_user_level">
<BR>
<B>Ändern Gleiche Benutzer-Niveau -</B> Diese Einstellung gilt nur bis 9 Benutzern zu nivellieren. Wenn diese Option aktiviert es ermöglicht der Stufe 9 Benutzer, ihre eigenen Einstellungen sowie andere Ebene 9 Benutzer ändern. Standard ist 1 für aktivierte.

<BR>
<A NAME="users-admin_hide_lead_data">
<BR>
<B>Admin ausblenden Lead-Daten -</B> Diese Einstellung gilt nur bis Level 7, 8 und 9 Benutzer. Wenn diese Option aktiviert es ersetzt oder des Konsumenten führt Daten in den vielen Berichten und Bildschirmen im System mit Xs. Standard ist 0 für Behinderte.

<BR>
<A NAME="users-admin_hide_phone_data">
<BR>
<B>Admin ausblenden Phone Data -</B> Diese Einstellung gilt nur bis Level 7, 8 und 9 Benutzer. Wenn diese Option aktiviert es ersetzt die Kunden Rufnummern in den vielen Berichten und Bildschirmen im System mit Xs. Die DIGITS Einstellungen zeigt nur die letzten Ziffern der X Telefonnummer. Standard ist 0 für Behinderte.






<BR><BR><BR><BR>

<B><FONT SIZE=3>KAMPAGNENTABELLE</FONT></B><BR><BR>
<A NAME="campaigns-campaign_id">
<BR>
<B>Kampagne Identifikation -</B> Dieses ist der kurze Name der Kampagne,es ist nicht editable nach Ausgangsunterordnung, kann nicht Räumeenthalten und muß zwischen 2 und 8 Buchstaben im lengt seinh.

<BR>
<A NAME="campaigns-campaign_name">
<BR>
<B>Kampagne Name -</B> Dieses ist die Beschreibung der Kampagne, muß eszwischen 6 und 40 Buchstaben lang sein.

<BR>
<A NAME="campaigns-campaign_description">
<BR>
<B>Kampagne Beschreibung -</B> dieses ist ein Protokoll auffangenfür die Kampagne, ist es wahlweise freigestellt und kann ein Maximumvon 255 Buchstaben lang sein.

<BR>
<A NAME="campaigns-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist der Benutzer mit Administratorrechten für diese Kampagne, ermöglicht diese Admin Betrachten dieser Kampagne sowie den Listen zugewiesen zu dieser Kampagne, um nach Benutzergruppen eingeschränkt werden. Standard ist - ALLE -, die jeder Benutzer admin mit Benutzergruppe Kampagne Rechte, um diese Kampagne sehen können.

<BR>
<A NAME="campaigns-campaign_changedate">
<BR>
<B>Kampagne Änderung Datum -</B> dieses ist das letzte Mal, daß dieEinstellungen für diese Kampagne in jeder Hinsicht geändert wurden.

<BR>
<A NAME="campaigns-campaign_logindate">
<BR>
<B>Letztes Kampagne LOGON-Datum -</B> dieses ist das letzte Mal, daß einMittel in diese Kampagne geloggt wurde.

<BR>
<A NAME="campaigns-campaign_calldate">
<BR>
<B>Letzte Kampagne Call Date -</B> Dies ist das letzte Mal, dass ein Anruf wurde durch einen Vertreter in diese Kampagne angemeldet behandelt.

<BR>
<A NAME="campaigns-max_stats">
<BR>
<B>Tägliches Maximum Stats -</B> Das sind Statistiken, die vom System generiert werden, jeden Tag den ganzen Tag, bis die Zeitschaltuhr am Ende des Tages, als es in den Systemeinstellungen eingestellt ist. Diese Zahlen sind aus den Protokollen innerhalb des Systems erzeugt werden, um für viel schneller Anzeige zu ermöglichen. Die Statistiken enthalten sind - Summe der Anrufe, Maximale Bevollmächtigte, Maximale eingehende Anrufe, ausgehende Anrufe Maximale.

<BR>
<A NAME="campaigns-campaign_stats_refresh">
<BR>
<B>Kampagne Stats Refresh -</B> Diese Option ermöglicht es Ihnen, eine Kampagne zu zwingen Aufruf Statistiken zu aktualisieren, selbst wenn die Kampagne nicht aktiv.

<BR>
<A NAME="campaigns-realtime_agent_time_stats">
<BR>
<B>Echtzeit-Agent Zeitstatistik -</B> Wenn Sie dieses auf alles andere als BEHINDERTE ermöglicht das Sammeln von Agent Zeit Stats for heute für diese Kampagne, die sichtbar sind durch den Echtzeit-Bericht. Ferngespräche sind die durchschnittlichen ruft behandelt pro Agent, ist WAIT die durchschnittliche Wartezeit Agent, CUST der durchschnittliche Kunde Sprechzeit ist, der Durchschnitt liegt bei ACW Anrufnachbearbeitung Zeit, ist die durchschnittliche PAUSE Pausenzeit. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-active">
<BR>
<B>Aktiv -</B> Dieses ist, wohin Sie die Kampagne auf aktives oderunaktiviertes einstellen. Wenn unaktiviert, kann noone in es loggen.

<BR>
<A NAME="campaigns-park_ext">
<BR>
<B>Park Music-on-Hold -</B> Hier können Sie die Wartemusik Kontext für diese Kampagne definieren können. Wählen Sie unbedingt eine gültige Wartemusik Kontext von der Select-Liste und dass der Kontext, den Sie ausgewählt haben, hat gültige Dateien in sich. Die Standardeinstellung ist Standard.

<BR>
<A NAME="campaigns-park_file_name">
<BR>
<B>Park File Name -</B> NOT USED.

<BR>
<A NAME="campaigns-web_form_address">
<BR>
<B>Netz-Form -</B> Dieses ist, wo Sie die kundenspezifische Webseiteeinstellen können, die geöffnet ist, wenn der Benutzer an dieNETZ-FORM-Taste klickt. So passen Sie die Abfragezeichenfolge nach dem Web-Formular, einfach beginnen, die Web-Formular mit VAR und dann die URL, die Sie verwenden möchten, ersetzt die Variablen mit den Variablen-Namen, die Sie verwenden möchten - A - phone_number - B -- genau wie in der Registerkarte INDEXE Abschnitt. Wenn Sie benutzerdefinierte Felder in einem Web-Formular-Adresse verwenden möchten, müssen Sie hinzufügen &CF_uses_custom_fields=Y als Teil der URL.

<BR>
<A NAME="campaigns-web_form_target">
<BR>
<B>Netz-Form-Target-</B> Hier können Sie die eigene Web-Seite, dass der Rahmen Web-Formular wird geöffnet, wenn der Nutzer auf die Netz-Form-Taste. Der Standardwert ist _blank.

<BR>
<A NAME="campaigns-allow_closers">
<BR>
<B>Erlauben Sie Closers -</B> Dieses ist, wo Sie einstellen können, obdie Benutzer dieser Kampagne die Wahl haben, zum des Anrufs zu einemgenauerem zu schicken.

<?php
if ($SSallow_emails > 0) 
		{
?>
	<BR>
	<A NAME="campaigns-allow_emails">
	<BR>
	<B>Erlauben Sie E-Mails -</B> Dies ist, wo Sie festlegen können, ob die Benutzer dieser Kampagne in der Lage, eingehende E-Mails zusätzlich zu Telefon Anrufe empfangen.
<?php
		}
?>
<BR>
<A NAME="campaigns-default_xfer_group">
<BR>
<B>Rückstellung Übergangsgruppe - dieses fangen ist dieRückstellung In-Gruppe auf, die automatisch vorgewählt wird, wenndas Mittel zum Bringenkonferenz Rahmen in ihrer Mittelschnittstellegeht.

<BR>
<A NAME="campaigns-xfer_groups">
<BR>
<B>Gewährte Übergangsgruppen - mit diesen checkbox Auflistungen könnenSie die Gruppen vorwählen, denen Mittel in dieser Kampagne bringenkönnen Anrufe zu. Erlauben Sie Closers muß ermöglicht werden, damitdiese Wahl oben darstellt.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-campaign_allow_inbound">
	<BR>
	<B>Erlauben Sie Inbound und gemischt - dieses ist, wo Sie einstellenkönnen, ob die Benutzer dieser Kampagne die Wahl haben, zum von voninbound Anrufen mit dieser Kampagne zu nehmen. Wenn Sie gemischtesinbound und outbound tun möchten dann, muß dieses auf Y eingestelltwerden. Wenn Sie nur das outbound Wählen auf dieser Kampagne tunmöchten einstellten dieses auf N. Default sind N.

	<BR>
	<A NAME="campaigns-dial_status">
	<BR>
	<B>Vorwahlknopf-Status -</B> Dieses ist, wohin Sie die Status einstellen,die Sie an innerhalb der Listen wählen wünschen, die für dieKampagne unten aktiv sind. Um einen anderen Status Vorwahlknopf hinzuzufügen, wählen Sie ihnvon der Drop-Down-Liste vor und Klicken FÜGT hinzu. Um einen derVorwahlknopfstatus zu entfernen, klicken Sie an dieENTFERNENVERBINDUNG nahe bei dem Status, den Sie entfernen möchten.

	<BR>
	<A NAME="campaigns-lead_order">
	<BR>
	<B>Liste Auftrag -</B> Dieses Menü ist, wo Sie vorwählen, wie dieLeitungen, die die Status zusammenbringen, die oben vorgewähltwerden, in den Leitung Zufuhrbehälter eingesetzt werden:
	 <BR> &nbsp; - DOWN: wählen Sie die erste führt in die Liste Tabelle geladen
	 <BR> &nbsp; - UP: Wählen Sie die letzte führt in der Liste Tabelle geladen
	 <BR> &nbsp; - UP PHONE: wählen Sie die höchste Telefonnummer und die Arbeiten seine Weiseunten vor
	 <BR> &nbsp; - DOWN PHONE: wählen Sie die niedrigste Telefonnummer und die Arbeiten seine Weiseoben vor
	 <BR> &nbsp; - UP LAST NAME: Anfänge mit letzten Namen beginnend mit Z und Arbeiten seine Weiseunten
	 <BR> &nbsp; - DOWN LAST NAME: Anfänge mit letzten Namen beginnend mit A und Arbeiten seine Weiseoben
	 <BR> &nbsp; - UP COUNT: Anfänge mit benannten Leitungen und Arbeiten seine Weise unten
	 <BR> &nbsp; - DOWN COUNT: Anfänge mit wenigen benannten Leitungen und Arbeiten seine Weiseoben
	 <BR> &nbsp; - DOWN COUNT 2nd NEW: Anfänge mit wenigen benannten Leitungen und Arbeiten seine Weise, dieoben eine NEUE Leitung in jeder anderen Leitung einsetzt - dürfenNICHT NEUES haben vorgewählt in den Vorwahlknopfstatus
	 <BR> &nbsp; - DOWN COUNT 3nd NEW: Anfänge mit wenigen benannten Leitungen und Arbeiten seine Weise, dieoben eine NEUE Leitung in jeder dritten Leitung einsetzt - dürfenNICHT NEUES haben vorgewählt in den Vorwahlknopfstatus
	 <BR> &nbsp; - DOWN COUNT 4th NEW: Anfänge mit wenigen benannten Leitungen und Arbeiten seine Weise, dieoben eine NEUE Leitung in jedem einsetzt weiter, führen Sie - darfNICHT NEUES haben vorgewählt in den Vorwahlknopfstatus
	 <BR> &nbsp; - RANDOM: Zufällig schnappt führen innerhalb der definierten Status und Listen
	 <BR> &nbsp; - UP LAST CALL TIME: Sortiert nach den neuesten Ortsgespräch Zeit für die Leitungen
	 <BR> &nbsp; - DOWN LAST CALL TIME: Sortiert nach den ältesten Ortsgespräch Zeit für die Leitungen
	 <BR> &nbsp; - UP RANK: Startet mit dem höchsten Rang und arbeitet sich nach unten
	 <BR> &nbsp; - DOWN RANK: Startet mit dem niedrigsten Rang und arbeitet dem Weg nach oben
	 <BR> &nbsp; - UP OWNER: Beginnt mit den Eigentümern beginnend mit Z und arbeitet sich nach unten
	 <BR> &nbsp; - DOWN OWNER: Beginnt mit den Eigentümern mit A beginnt und arbeitet dem Weg nach oben
	 <BR> &nbsp; - UP TIMEZONE: Startet mit Ost-West Zeitzonen und arbeitet
	 <BR> &nbsp; - DOWN TIMEZONE: Startet mit westlichen Zeitzonen und arbeitet Osten

	<BR>
	<A NAME="campaigns-lead_order_randomize">
	<BR>
	<B>Liste Auftrag Randomize -</B> Diese Option ermöglicht es Ihnen, die Reihenfolge der Leitungen in den Trichter Last innerhalb der Ergebnisse von den obengenannten Kriterien definiert randomisieren. Zum Beispiel wird die Reihenfolge der Liste erhalten bleiben, aber die Ergebnisse werden innerhalb dieser Sortierung randomisiert. Wenn diese Option auf Y wird diese Funktion aktivieren. Standard ist für behinderte N. Hinweis, wenn Sie eine große Zahl von Kaufinteressenten haben diese Option kann verlangsamen die Geschwindigkeit des Trichters Laden Skript.

	<BR>
	<A NAME="campaigns-lead_order_secondary">
	<BR>
	<B>Liste Auftrag Sekundäre -</B> Diese Option ermöglicht es Ihnen, die zweite Sortierreihenfolge der Hauptrollen in dem Trichter nach dem Last-Liste Reihenfolge und innerhalb der Ergebnisse von den obengenannten Kriterien definiert auszuwählen. Zum Beispiel wird die Reihenfolge der Liste für die erste Art der Leitungen verwendet werden, aber die Ergebnisse werden zum zweiten Mal innerhalb dieser Sortierung über den gleichen Satz von der ersten Liste sortiert werden. Zum Beispiel, wenn Sie Auftrag Liste gesetzt zu COUNT DOWN-und haben Sie nur Leitungen, die 1 und 2 mal versucht haben, dann, wenn Sie die Reihenfolge der Liste sekundäre Gruppe zu allen Versuch 1 führt LEAD_ASCEND haben wird von den ältesten Leitungen sortiert werden erste und wird in den Trichter so gesetzt werden. Standard ist LEAD_ASCEND. Bedenken Sie, dass wenn Sie eine große Anzahl von Leitungen mit einer der Optionen CALLTIME verlangsamen die Geschwindigkeit des Trichters Laden Skript. Wenn Liste Auftrag Randomize aktiviert ist, wird diese Option ignoriert.

	<BR>
	<A NAME="campaigns-hopper_level">
	<BR>
	<B>Minimale Stufe Hopper -</B> Dies ist die minimale Anzahl von Leitungen der Trichter Lade Skript versucht, in den Trichter Tabelle für diese Kampagne zu halten. Wenn Laufen VDhopper Skript jede Minute, machen diese etwas größer als die Anzahl der Leads Sie durch in einer Minute.

	<BR>
	<A NAME="campaigns-use_auto_hopper">
	<BR>
	<B>Automatische Hopper Stufe -</B> Wird dies auf Y wird es dem System, um die Trichter auf Basis von den Einstellungen, die Sie in Ihrer Kampagne haben automatisch einzustellen. Die Formel verwendet es, um dies zu tun ist:<BR>Number of AktivBevollmächtigte * Selbstvorwahlknopf-Niveau * ( 60 seconds / Vorwahlknopf-Abschaltung ) * Auto Hopper Multiplier<BR>Default is Y.

	<BR>
	<A NAME="campaigns-auto_hopper_multi">
	<BR>
	<B>Automatische Hopper Multiplier -</B> Dies ist Multiplikator für die Auto-Hopper. Wenn Sie diese weniger als 1 bewirkt, dass der Auto-Hopper-Algorithmus, um weniger Leitungen als normalerweise zu laden. Wenn Sie diesen Wert größer als 1 bewirkt, dass der Auto-Hopper-Algorithmus, um mehr Leads als es normalerweise tun würde zu laden. Standard ist 1.

	<BR>
	<A NAME="campaigns-auto_trim_hopper">
	<BR>
	<B>Auto Trim Hopper -</B> Wird dies auf Y wird es dem System, um überschüssige führt automatisch zu entfernen aus dem Trichter. Standardwert ist Y.

	<BR>
	<A NAME="campaigns-hopper_vlc_dup_check">
	<BR>
	<B>Hopper VLC Dup Prüfen -</B> Wenn Sie dieses auf Y wird in jedem führen, dass in den Trichter durch vendor_lead_code überprüft, um sicherzustellen, gibt es kein Duplikat eingefügt führen Leitungen mit der gleichen vendor_lead_code eingefügt. Dies ist besonders nützlich, wenn Auto-Alt-Dialing mit MULTI_LEAD. Standard ist N.

	<BR>
	<A NAME="campaigns-lead_filter_id">
	<BR>
	<B>Leitung Filter -</B> Dieses ist eine Methode der Entstörung IhrerLeitungen mit einem Fragment einer SQL Frage. Benutzen Sie dieseFunktion mit Vorsicht, es ist einfach mit, der geringfügigstenÄnderung zur SQL Aussage versehentlich zu wählen zu stoppen.Rückstellung ist KEINE.

	<BR>
	<A NAME="campaigns-call_count_limit">
	<BR>
	<B>Anzahl der Anrufe-Limit -</B> Dies erzwingt eine Grenze für die Anzahl der Anrufversuche für die Leitungen in dieser Kampagne gewählt. Ein Lead kann sich über diese Grenze gehen, wenn etwas Blei-Recycling oder Auto-Alt-Dialing aktiviert ist. Standard ist 0 für kein Limit.

	<BR>
	<A NAME="campaigns-call_count_target">
	<BR>
	<B>Anzahl der Anrufe Ziel -</B> Diese Option wird nur für Zwecke der Berichterstattung verwendet werden und hat keinen Einfluss auf den Leitungen gewählt. Standard ist 3.

	<BR>
	<A NAME="campaigns-drop_lockout_time">
	<BR>
	<B>Drop Lockout Time -</B> Dies ist eine Zahl der Stunden, die DROP aufzugeben Anrufe verhindert werden, daß gewählt, zu deaktivieren, auf 0 gesetzt. Diese Einstellung ist sehr nützlich, in Ländern wie dem Vereinigten Königreich, wo gibt es Vorschriften, die verhindern versucht Berufung des Kunden innerhalb von 72 Stunden ein Abandon oder DROP. Der Standardwert ist 0.

	<BR>
	<A NAME="campaigns-force_reset_hopper">
	<BR>
	<B>Kraft-Zurückstellen des Zufuhrbehälters -</B> erlaubt dieses Ihnen,aus dem Zufuhrbehälterinhalt nach Formunterordnung abzuwischen. Essollte wieder gefüllt werden, wenn der VDhopper Index läuft.

	<BR>
	<A NAME="campaigns-dial_method">
	<BR>
	<B>Vorwahlknopf-Methode -</B> Dieses fangen ist die Weise, wie auf das Wählen zu definieren, stattfinden soll. Wenn HANDBUCH dann das auto_dial_level bei 0 verschlossen ist, es sei denn Vorwahlknopf-Methode geändert wird. Wenn VERHÄLTNIS dann der Normal, der eine Zeilenzahl für aktive Mittel wählt. ADAPT_HARD_LIMIT wählt predictively bis zum fallengelassenen Prozentsatz und dann erlaubt nicht das konkurrenzfähige Wählen, sobald die Tropfenbegrenzung erreicht wird, bis der Prozentsatz unten wieder geht. ADAPT_TAPERED läßt laufenden Überschuß den fallengelassenen Prozentsatz zur Hälfte erste der Verschiebung zu - wie durch das call_time definiert, das für Kampagne vorgewählt wird und erhält strenger, während die Verschiebung weitergeht. ADAPT_AVERAGE versucht, einen Durchschnitt oder den fallengelassenen Prozentsatz beizubehalten so, die konkurrenzfähig harte Begrenzungen nicht wie die anderen zwei Methoden auferlegen. Sie können nicht das Selbstvorwahlknopf-Niveau ändern, wenn Sie in irgendwelchen der ANPASSENVORWAHLKNOPFMETHODEN sind. Nur der Dialer kann das Vorwahlknopfniveau ändern wenn im vorbestimmten wählenden Modus. INBOUND_MAN ermöglicht es dem Agenten, um manuelle Einwahl Anrufe von einer Kampagne Liste, während in der Lage, die eingehenden Anrufe zwischen manuelle Einwahl fordert.

	<BR>
	<A NAME="campaigns-auto_dial_level">
	<BR>
	<B>Selbstvorwahlknopf-Niveau -</B> Hier können Sie einstellen, wie viele Zeilen sollte das System pro Wirkstoff zu verwenden. Null bedeutet 0 Autowahl ist aus und klicken Sie auf die Agenten, um jede Nummer zu wählen. Andernfalls wird das System der Wahl zu halten Linien gleich Wirkstoffe, multipliziert mit der Wahlebene an, wie viele Zeilen diese Kampagne auf jedem Server sollte es ankommen. Das Kontrollkästchen ADAPT RIDE ermöglicht es Ihnen, eine neue Wahl Ebene zwingen, obwohl das Wahlverfahren ist in einem ADAPT-Modus. Dies ist nützlich, wenn es eine dramatische Verschiebung in der Qualität der Leads und Sie die dial_level manuell drastisch ändern wollen.

	<BR>
	<A NAME="campaigns-dial_level_threshold">
	<BR>
	<B>Auto Dial Schwellwert -</B> Diese Einstellung funktioniert nur mit einem ADAPT oder RATIO Wählmethode, und es muss auf etwas anderes als BEHINDERTE und die Anzahl der Agenten Einstellung muss über 0 eingestellt werden. Diese Funktion erlaubt Ihnen, eine Mindestanzahl Agenten eingestellt, dass prädiktive Algorithmus wird bei der Arbeit. Wenn die Anzahl der Agenten unterschreitet die Nummer, die Sie eingestellt haben, dann wird die DFÜ-Ebene wird auf 1,0 gehen, bis mehr Agenten melden Sie sich an oder gehen in den ausgewählten Zustand. Angemeldete IN_AGENTS wird alle Mittel in die Kampagne angemeldet zu zählen, wird NON-PAUSED_AGENTS zählen nur Agenten, die darauf warten oder sich unterhalten, und WAITING_AGENTS wird nur zählen, dass Agenten auf einen Anruf warten. Der Standardwert ist DISABLED.

	<BR>
	<A NAME="campaigns-available_only_ratio_tally">
	<BR>
	<B>Vorhandenes nur Tally -</B> dieses fangen auf, wenn Satz zu YINCALL und WARTESCHLANGE Statusmittel ausläßt, wenn er die ZahlAnrufen zum Vorwahlknopf wenn nicht in MANUELLEN Vorwahlknopfmoduserrechnet. Rückstellung ist N.

	<BR>
	<A NAME="campaigns-available_only_tally_threshold">
	<BR>
	<B>Erhältlich Nur Tally Threshold -</B> Diese Einstellung funktioniert nur mit einem ADAPT oder RATIO Wählmethode, erhältlich nur Tally auf N gesetzt werden muss, muss diese Einstellung auf etwas anderes als DISABLED gesetzt werden und die Anzahl der Agenten Einstellung muss über 0 sein. Diese Funktion ermöglicht es Ihnen, die Anzahl der Agenten, unterhalb derer Erhältlich Nur Tally aktiviert sein wird eingestellt. Wenn die Anzahl der Agenten unterschreitet die Nummer, die Sie eingestellt haben, dann ist die Verfügbar Nur Tally Einstellung mit zu gehen, um vorübergehend y bis mehreren Mitteln, sich anzumelden oder gehen in den ausgewählten Zustand. Angemeldete IN_AGENTS wird alle Mittel in die Kampagne angemeldet zu zählen, wird NON-PAUSED_AGENTS zählen nur Agenten, die darauf warten oder sich unterhalten, und WAITING_AGENTS wird nur zählen, dass Agenten auf einen Anruf warten. Der Standardwert ist DISABLED.

	<BR>
	<A NAME="campaigns-adaptive_dropped_percentage">
	<BR>
	<B>Tropfen-Prozentsatz-Begrenzung -</B> dieses fangen ist auf, wohinSie die Begrenzung auf den Prozentsatz der fallengelassenen Anrufe,die einstellten Sie beim Verwenden eineranpassungsfähig-vorbestimmten Vorwahlknopfmethode möchten, nichtMANUELL oder des VERHÄLTNISSES.

	<BR>
	<A NAME="campaigns-adaptive_maximum_level">
	<BR>
	<B>Maximum paßt den waagerecht ausgerichteten Vorwahlknopf an -</B> diesesfangen ist auf, wohin Sie die Begrenzung auf die Begrenzungauf das numbr der Linien, die einstellten Sie gewählt pro Mittel beimVerwenden einer anpassungsfähig-vorbestimmten Vorwahlknopfmethodemöchten, nicht MANUELL oder des VERHÄLTNISSES. Diese Zahl kann alsdas Selbstvorwahlknopf-Niveau höher sein, wenn Ihre Kleinteile esstützen. Wert muß eine positive Nr. grösser als eine sein und kannDezimalstellen Rückstellung 3.0 haben.

	<BR>
	<A NAME="campaigns-adaptive_latest_server_time">
	<BR>
	<B>Neueste Bediener-Zeit -</B> dieses fangen wird verwendet nurdurch die ADAPT_TAPERED Vorwahlknopfmethode auf. Sie sollten in dieStunde hereinkommen und Minute, um der Sie stoppen, diese Kampagne zuersuchen, 2100 würde bedeuten, daß Sie stoppen, diese Kampagne bei9PM Bedienerzeit zu wählen. Dieses läßt den sich verjüngendenAlgorithmus entscheiden wie konkurrenzfähig zum Vorwahlknopf durch,wie lang Sie haben, bis Sie fertiges Benennen sind.

	<BR>
	<A NAME="campaigns-adaptive_intensity">
	<BR>
	<B>Passen Sie Intensität Modifizierfaktor an -</B> dieses fangenwird verwendet, die vorbestimmte höhere oder niedrigere Intensitätzu justieren entweder auf. Das höhere eine positive Zahl, die Sievorwählen, grösser der Dialer den schreitenen Anruf erhöht, wenn esgeht oben und langsam der Dialer den schreitenen Anruf verringert,wenn es geht unten. Das niedriger die negative Zahl, Sie vorwählenhier, langsam erhöht der Dialer den schreitenen Anruf und schnellersenkt der Dialer den schreitenen Anruf, wenn es geht unten.Rückstellung ist 0. Dieses fangen wird verwendet nicht durchden HANDBUCH- oder VERHÄLTNIS-Vorwahlknopf Methoden auf.

	<BR>
	<A NAME="campaigns-adaptive_dl_diff_target">
	<BR>
	<B>Vorwahlknopf-Waagerecht ausgerichtetes Unterschied-Ziel -</B> Dieses Feld wird festgelegt, ob Sie gezielt mit einer bestimmten Anzahl von Agenten warten auf Anrufe oder Anrufe in der Warteschlange für die Agenten wollen. Zum Beispiel, wenn Sie möchte immer im Durchschnitt ein Agent frei zu nehmen haben, ruft sofort würden Sie diese auf -1 gesetzt, wenn Sie möchten, zu zielen immer mit einem Anruf zu halten für einen Agenten warten Sie dies auf 1 gesetzt würde. Standard ist 0. Dieses Feld wird nicht durch das Handbuch oder INBOUND_MAN Einwahl verwendeten Methoden.

	<BR>
	<A NAME="campaigns-dl_diff_target_method">
	<BR>
	<B>Wählen Level Difference Zielmethode -</B> Mit dieser Option können Sie festlegen, ob die Einwahl Pegeldifferenz Zielsetzung nur auf die Berechnung des Zifferblattes Ebene oder auch auf die tatsächliche Wahl auf jeder Einwahl-Server angewendet wird. Wenn Sie mit einer kleinen Kampagne mit angemeldeten Agenten in auf vielen Servern Sie die ADAPT_CALC_ONLY Option verwenden können, weil die CALLS_PLACED Option in weniger Anrufe werden als Moeglichkeiten nach platziert führen kann. Diese Option ist nur aktiv, wenn Dial Level Difference Ziel auf etwas anderes als 0 gesetzt ist. Die Standardeinstellung ist ADAPT_CALC_ONLY.

	<BR>
	<A NAME="campaigns-concurrent_transfers">
	<BR>
	<B>Gleichzeitige Übertragungen -</B> diese Einstellung wird benutzt, um dieZahl Anrufen zu definieren, die zu den Mitteln gleichzeitig geschicktwerden können. Es wird empfohlen, daß diese Einstellung am AUTOMOBILgelassen wird. Dieses fangen wird verwendet nicht durch dieMANUELLE Vorwahlknopfmethode auf.

	<BR>
	<A NAME="campaigns-queue_priority">
	<BR>
	<B>Queue-Priorität - </B> Diese Einstellung wird verwendet, um die Reihenfolge, in der die Anrufe von diesem ausgehende Kampagne zu beantworten in Bezug auf die eingehenden Anrufe, wenn diese Kampagne ist in Blended-Modus. Wir empfehlen nicht, die Einstellung eingehende Anrufe an eine höhere Priorität als die Outbound Kampagnen, weil die Leute rufen in zu warten, erwarten, und die Menschen aufgerufen erwarten, für jemanden da zu sein auf dem Handy.

	<BR>
	<A NAME="campaigns-drop_rate_group">
	<BR>
	<B>Mehrere Kampagne Drop Rate Group -</B> Diese Funktion erlaubt Ihnen, eine Kampagne als Mitglied einer Kampagne Drop-Rate-Fraktion, oder eine Gruppe von Kampagnen Menge, deren Human Beantwortete Anrufe und Drop fordert für alle Kampagnen in der Gruppe werden in einen gemeinsamen Drop Prozentsatz, oder aufgeben und abstimmen kombiniert werden. Dies ermöglicht es Ihnen, mehrere Kampagnen gleichzeitig und leichter Ablaufsteuerung Ihre Drop-Rate. Dies ist besonders nützlich in Großbritannien, wo die Vorschriften dieser Drop-Rate Berechnungsmethode mit Kampagnen-Gruppierung in der gleichen Firma Bewilligung, auch wenn es mehrere Kampagnen, die Firma läuft am selben Tag. Um dies für eine Kampagne zu aktivieren, wählen Sie einfach eine Gruppe aus der Liste. Es gibt 10 Gruppen in das System standardmäßig definiert, können Sie Ihren Systemadministrator wenden, um mehr hinzuzufügen. Option ist standardmäßig deaktiviert.

	<BR>
	<A NAME="campaigns-inbound_queue_no_dial">
	<BR>
	<B>Eingangswarteschlange No Dial -</B> Diese Funktion, wenn auf ENABLED einstellen können Sie ausgehende automatische Einwahl dieser Kampagne zu verhindern, wenn es irgendwelche eingehende Anrufe in der Warteschlange, die Teil der zulässigen eingehenden in dieser Kampagne festgelegt sind. Wenn Sie dieses auf ALL_SERVERS ändert den Algorithmus, um alle eingehenden Anrufe als aktive Anrufe auf diesem Server zu berechnen, auch wenn sie auf einem anderen Server, die die Chance auf unnötigen ausgehenden Anrufen reduziert werden, wenn Sie Anrufe kommen in auf einem anderen Server haben sollen. Der Standardwert ist DISABLED.

	<BR>
	<A NAME="campaigns-auto_alt_dial">
	<BR>
	<B>Wählende SelbstAlt-zahl -</B> diese Einstellung wird benutzt, umwechselnde Nummer automatisch zu wählen auffängt, beimWählen im VERHÄLTNIS und ANPASST Vorwahlknopfmethoden, wenn eskeinen Kontakt an der Haupttelefonnummer für eine Leitung, die Na, B,DC und N Status gibt. Diese Einstellung wird nicht durch die MANUELLEVorwahlknopfmethode benutzt. EXTENDED stellvertretenden Zahlen sind die Zahlen in das System außerhalb der Standard-Bildschirm führen. Mit Extended können Sie Hunderte von Telefonnummern für einen einzigen Kunden aufnehmen. Mit MULTI_LEAD erlaubt Ihnen, eine separate Leitung für jede Nummer auf einem Kundenkonto, dass alle eine gemeinsame vendor_lead_code verwenden, und die Art der Rufnummer jeweils mit einem Etikett im Feld Besitzer definiert. Diese Option wird deaktiviert einige Optionen auf dem Bildschirm ändern Kampagne und zeigen einen Link zu dem Multi-Alt-Einstellungen Seite, die Sie an, welche Telefonnummer Typen, definiert durch die Beschriftung der einzelnen Blei festlegen können, wird gewählt und in welcher Reihenfolge. Dies wird eine spezielle Blei-Filter und wird das Feld Rang aller Leads innerhalb der Listen gesetzt, um diese Kampagne zu ändern, um sie in der von Ihnen angegebenen Reihenfolge nennen.

	<BR>
	<A NAME="campaigns-dial_timeout">
	<BR>
	<B>Vorwahlknopf-Abschaltung -</B> Wenn Sie, Anrufe, die normalerweiseHängezustand wurden, nachdem die Abschaltung, die in extensions.confanstatt Abschaltung an dieser Menge Sekunden definiert wurde wurde,wenn sie kleiner ist, als die extensions.conf Abschaltung definiertwerden. Dieses läßt schnell ändernde Vorwahlknopfabschaltungen vomBediener zum Bediener und zum Begrenzen der Effekte zu einer einzelnenKampagne zu. Wenn Sie eine Menge antwortende Maschine oder VoicemailAnrufe haben, können Sie diesen Wert, bis zwischen 21-26 zu ändernversuchen und sehen wünschen, wenn Resultate verbessern.

	<BR>
	<A NAME="campaigns-campaign_vdad_exten">
	<BR>
	<B>Routing-Erweiterung -</B> In diesem Feld können für eine benutzerdefinierte Outbound-Routing-Erweiterung. Dies ermöglicht Ihnen, verschiedene Methoden Anrufbearbeitung je nachdem, wie Sie Anrufe über Ihren Outbound-Kampagnen nutzen. Früher nannte Kampagne VDAD Erweiterung. 
	<BR>- 8364 - wie 8368
	<BR>- 8365 - Wird der Anruf nur an einen Agenten auf dem gleichen Server zu senden, als der Anruf in Verkehr gebracht wird
	<BR>- 8366 - Wird für Presse-1-, Broadcast-und Umfrage-Kampagnen
	<BR>- 8367 - Werden versuchen, senden Sie zunächst den Anruf an einen Agenten auf dem lokalen Server, dann wird es auf anderen Servern suchen
	<BR>- 8368 - DEFAULT - wird der Anruf zur nächsten freien Agenten zu senden, egal welchem ​​Server sie sind auf
	<BR>- 8369 - Wird für Anrufbeantworter-Erkennung nach, dass das gleiche Verhalten wie 8368
	<BR>- 8373 - Wird für Anrufbeantworter-Erkennung nach dieser gleiche Verhalten wie 8366
	<BR>- 8374 - Wird für Presse-1-, Broadcast-und Umfrage-Kampagnen mit Cepstral Text-to-Speech
	<BR>- 8375 - Wird für Anrufbeantworter-Erkennung und drücken Sie dann-1-, Broadcast-und Umfrage-Kampagnen mit Cepstral Text-to-Speech

	<BR>
	<A NAME="campaigns-am_message_exten">
	<BR>
	<B>Antwortende Maschine Anzeige -</B> Dieses Feld ist für die Eingabe von der Eingabeaufforderung zu spielen, wenn der Agent einen Anrufbeantworter und Klicks wird auf dem Anrufbeantworter Mitteilung Button in der Übertragung Konferenz Rahmen. Sie müssen diese entweder auf eine Audio-Datei in das Audio-Speicher oder ein TTS set prompt TTS, wenn auf Ihrem System aktiviert ist.

	<BR>
	<A NAME="campaigns-waitforsilence_options">
	<BR>
	<B>WaitForSilence Optionen -</B> Wenn Wait For Silence ist auf Anrufe, die als Anrufbeantworter dann festgestellt werden diesem Bereich hat die gewünschten Optionen. Es gibt zwei Einstellungen durch ein Komma getrennt, ist die erste Option, wie lange Schweigen in Millisekunden erkennen und die zweite Option ist, wie oft erkennen, dass vor dem Abspielen der Nachricht. Der Standardwert ist LEER für Behinderte. Ein Standard-Wert für dieses wäre für 2 Sekunden Stille warten, zweimal: 2000,2

	<BR>
	<A NAME="campaigns-amd_send_to_vmx">
	<BR>
	<B>AMD to Action senden -</B> Mit dieser Option können Sie festlegen, ob ein Aufruf der AMD Aktion wird gesendet, wenn ein Anrufbeantworter erkannt wird. Wenn diese auf N gesetzt ist, wird der Anruf, sobald festgestellt wird, um mit einem Anrufbeantworter aufgehängt. Der Standardwert ist N.

	<BR>
	<A NAME="campaigns-cpd_amd_action">
	<BR>
	<B>CPD AMD Action - </B> Wenn Sie die Sangoma ParaXip Rufen Fortschritte Detection-Software, dann werden Sie zum Aktivieren dieser Einstellung, entweder sie DISPO, die Disposition der Anruf als AA und hängen Sie es, wenn der Anruf wird bearbeitet und wurde nicht gesendet, um noch ein Agent oder eine Nachricht zu senden, die den Aufruf der Anrufbeantworter die Nachricht für diese Kampagne. Standardwert ist deaktiviert. Wird dies auf ingroup wird einen Anrufbeantworter an einer eingehenden Gruppe senden. Wird dies auf CALLMENU wird einen Anrufbeantworter an einem Aufruf Menü Nachricht im System.

	<BR>
	<A NAME="campaigns-amd_inbound_group">
	<BR>
	<B>AMD Inbound-Gruppe -</B> Wenn CPD AMD Aktion ingroup gesetzt ist, dann ist dies der Inbound-Gruppe, dass der Aufruf gesendet werden, wenn ein Anrufbeantworter erkannt.

	<BR>
	<A NAME="campaigns-amd_callmenu">
	<BR>
	<B>AMD Aufruf Menü -</B> Wenn CPD AMD Aktion CALLMENU gesetzt ist, dann ist dies der Ruf-Menü, dass der Aufruf gesendet werden, wenn ein Anrufbeantworter erkannt.

	<BR>
	<A NAME="campaigns-alt_number_dialing">
	<BR>
	<B>Manuelle Alt Num Dialing -</B> Diese Option ermöglicht ein Agent manuell wählen die alternative Telefonnummer oder Address3 Feld nach die Hauptnummer aufgerufen wurde. Wenn die Option in ihr AUSGEWÄHLTE dann wird das Kontrollkästchen Alt Wahl für jeden Anruf automatisch überprüft werden. Wenn die Option TIMER hat es dann in die Alt-Phone oder Address3 Feld wird automatisch nach Timer Alt Sekunden gewählt werden. Standardwert ist N für Behinderte.

	<BR>
	<A NAME="campaigns-timer_alt_seconds">
	<BR>
	<B>Timer Alt Sekunden -</B> Wenn der Hand Alt Num Dialing Einstellung hat in TIMER dann die Alt-Phone oder Address3 Feld wird automatisch nach dieser Anzahl von Sekunden gewählt werden. Standard ist 0 für Behinderte .

	<BR>
	<A NAME="campaigns-drop_call_seconds">
	<BR>
	<B>Sekunden zur Verbindung -</B> Anzahl der Sekunden vom Abnehmen des Kunden bis zur Erkennung als Verbindung, betrifft nur abgehende Verbindungen.

	<BR>
	<A NAME="campaigns-drop_action">
	<BR>
	<B>Drop-Aktion - </B> Mit diesem Menü können Sie wählen, was passiert, wenn er einen Anruf wartet seit mehr als das, was ist in der Drop-Call Sekunden ein. HANGUP einfach auflegen, das Gespräch, MESSAGE wird die Forderung der Drop-Erweiterung, die Sie definiert haben, unten, Voicemail wird der Anruf an die Voicemail-Box, die Sie definiert haben, und unter IN_GROUP wird der Aufruf an die Inbound-Gruppe, die weiter unten definiert ist. VMAIL_NO_INST wird der Anruf an die Voicemail-Box, die Sie unten definiert haben, senden und keine Aufträge nach der Voicemail-Nachricht zu spielen.

	<BR>
	<A NAME="campaigns-safe_harbor_exten">
	<BR>
	<B>Sicherheitsnebenstelle -</B> Dies ist die Wählplan Nebenstelle, wo sich die gewünschte Sicherheits-Audiodatei auf Ihrem Server befindet.

	<BR>
	<A NAME="campaigns-safe_harbor_audio">
	<BR>
	<B>Safe-Harbor-Audio -</B> Dies ist der Audio-Datei, die prompt gespielt, wenn die Drop-Aktion zu AUDIO eingestellt ist. Die Standardeinstellung ist Buzz.

	<BR>
	<A NAME="campaigns-safe_harbor_audio_field">
	<BR>
	<B>Safe-Harbor-Audio-Bereich -</B> Diese optionale Einstellung ermöglicht es Ihnen, ein Feld in der Liste zu definieren, dass das System als Audio-Dateinamen für jede Ableitung anstelle der Safe-Harbor-Audio-Datei verwenden. Wenn dies auf Disabled gestellt ist die Safe-Harbor-Audio-Datei wird immer verwendet werden. Das System wird nichts aus, um sicherzustellen, dass die Audio-Datei existiert als andere zu machen, dass der Wert des Feldes ist mindestens ein Zeichen, wenn Sie also einen Vorsprung, um die Standard-Safe harbor Audio verwenden möchten, dann setzen Sie einfach den Wert des Feldes in der Zuleitung zu entleeren. Sie können mit dem Pipe-Zeichen, um mehrere Audio-Dateien miteinander zu verknüpfen in dem Feld Wert für jedes Kabel. Ist standardmäßig deaktiviert. Hier ist die Liste der Felder, die für diese Einstellung verwendet werden können: vendor_lead_code, source_id, list_id, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, alt_phone, email, security_phrase, comments, rank, owner, entry_list_id

	<BR>
	<A NAME="campaigns-safe_harbor_menu_id">
	<BR>
	<B>Safe-Harbor-Call-Menü -</B> Dies ist der Anruf-Menü, dass ein Aufruf gesendet, wenn die Drop-Aktion ist auf CALLMENU.

	<BR>
	<A NAME="campaigns-voicemail_ext">
	<BR>
	<B>Voicemail -</B> Wenn sie definiert werden, würden Anrufe, dienormalerweise FALLEN würden, anstatt auf diesen voicemail Kastenverwiesen, um eine Anzeige zu hören und zu lassen.

	<BR>
	<A NAME="campaigns-drop_inbound_group">
	<BR>
	<B>Drop-Transfer-Fraktion - </B> Wenn Drop-Aktion ist auf IN_GROUP, wird der Anruf wird an die Inbound-Gruppe, wenn sie erreicht Drop Call Sekunden.

	<BR>
	<A NAME="campaigns-no_hopper_leads_logins">
	<BR>
	<B>Gewähren Sie Kein-Zufuhrbehälter-Führt LOGON -</B> wenn Satz zu Y,Mittel zum LOGON zur Kampagne erlaubt, selbst wenn es keine Leitungengibt, die in den Zufuhrbehälter für diese Kampagne geladen werden.Diese Funktion wird nicht in den CLOSER-type Kampagnen benötigt.Rückstellung ist N.

	<BR>
	<A NAME="campaigns-no_hopper_dialing">
	<BR>
	<B>Nr. Hopper Dialing -</B> Wenn diese aktiviert ist, wird der Trichter nicht ausgeführt werden für diese Kampagne. Diese Option ist nur verfügbar, wenn die DFÜ-Methode, mit manuellen oder INBOUND_MAN gesetzt ist. Es wird empfohlen, dass Sie diese Option nicht aktivieren, wenn Sie eine sehr große Datenbank haben, führen über 100.000 führt. With No Hopper Wählen Sie die folgenden Funktionen nicht:-Recycling, Auto-alt-Dialing, Verzeichnis-Mix, Liste Bestellung mit X. NEW. Wenn Sie Besitzer verwenden Nur Wählen Sie müssen keine Hopper Dialing aktiviert. Der Standardwert ist N für Behinderte.

	<BR>
	<A NAME="campaigns-agent_dial_owner_only">
	<BR>
	<B>Eigentümer nur Dialing -</B> Wenn diese aktiviert ist, wird der Agent führt nur zu erhalten, dass sie in das Eigentum Parameter sind. Ist dies der USER dann die Vertreter muß der Benutzer in der Datenbank als Besitzer dies dazu führen, definiert. Ist dies das Hoheitsgebiet dann die Eigentümer der führen muss das Gebiet, in dem User Änderung Bildschirm für dieses Anbieters angegeben haben. Ist dies zu USER_GROUP dann die Eigentümer der führen muss der Benutzer die Gruppe, dass der Agent ist ein Mitglied der übereinstimmen. Für diese Funktion, um die DFÜ-Methode arbeiten müssen, um MANUAL oder INBOUND_MAN und Nr. Hopper Dialing eingestellt werden muss aktiviert sein. Der Standardwert ist KEINE für Behinderte. Wenn die Option BLANK hat am Ende, dann Benutzer können Leads ohne Besitzer neben Eigentümer definiert führt wählen.

	<BR>
	<A NAME="campaigns-owner_populate">
	<BR>
	<B>Besitzer Auffüllen -</B> Wenn diese Option aktiviert ist und der Besitzer des Feldes führen leer ist, wird der Besitzer Feld für die Leitung mit der Benutzer-ID des Agenten, der den Anruf übernimmt erste bevölkern. Standardmäßig deaktiviert.

	<?php
	if ($SSuser_territories_active > 0)
		{
		?>
		<BR>
		<A NAME="campaigns-agent_select_territories">
		<BR>
		<B>Wählen Sie Agent Territories -</B> Wenn diese Option aktiviert ist und der Agent gehört mindestens ein Gebiet, wird der Agent die Möglichkeit haben, die Auswahl Gebiete führt vom Zifferblatt. Der Agent wird eine Liste der verfügbaren Gebiete bei der Anmeldung zu sehen und sie wird die Fähigkeit, um wieder auf diesem Gebiet Liste haben, wenn angehalten, ihre Gebiete zu ändern. Für diese Funktion für die Arbeit der Eigentümer nur Dialing-Option zu GEBIET und User Gebiete gesetzt werden muss in den Einstellungen aktiviert werden.
		<?php
		}
	?>

	<BR>
	<A NAME="campaigns-list_order_mix">
	<BR>
	<B>Liste Auftrag Mischung - läuft den Leitung Auftrag über undVorwahlknopf-Status fängt auf. Verwendet die Liste undStatusparameter für die vorgewählte Liste Mischung Eintragung imListe Mischung Unterseebootabschnitt anstatt. Rückstellung istUNTAUGLICH.

	<BR>
	<A NAME="campaigns-vcl_id">
	<BR>
	<B>Liste Mischung Identifikation - Identifikation der Liste Mischung.Muß von 2-20 Buchstaben lang ohne Räume oder andere spezielleInterpunktion sein.

	<BR>
	<A NAME="campaigns-vcl_name">
	<BR>
	<B>Liste Mischung Name - beschreibender Name der Liste Mischung. Muß von2-50 Buchstaben lang sein.

	<BR>
	<A NAME="campaigns-list_mix_container">
	<BR>
	<B>Liste Mischung Detail - der Aufbau der Liste Mischung Eintragung.Enthält die Liste Identifikation, Mischung Auftrag, Prozentsätze undStatus, die diese Liste Mischung bilden. Die Prozentsätze müssen bis100 immer addieren, und alle Listen müssen aktiv und eingestellt aufdie Kampagne sein, damit die Auftrag Mischung Eintragung aktiviertwerden kann.

	<BR>
	<A NAME="campaigns-mix_method">
	<BR>
	<B>Liste Mischung Methode - die Methode von alle Teile des Liste MischungDetails zusammen mischen. EVEN_MIX mischt Leitungen von jedem Teil,das mit den anderen Teilen, wie diese 1.2.3.1.2.3.1.2.3 durchgeschobenwird. IN_ORDER setzt die Leitungen in den Auftrag ein, in dem sie aufdem Liste Mischung Detailschirm 1.1.1.2.2.2.3.3.3 verzeichnet werden.GELEGENTLICHER Wille setzte sie in GELEGENTLICHEN Auftrag1.3.2.1.1.3.2.1.3 ein. Rückstellung ist IN_ORDER.

	<BR>
	<A NAME="campaigns-agent_extended_alt_dial">
	<BR>
	<B>Agent Screen Extended Alt Dial -</B> Mit dieser Funktion können für Agenten auf erweiterten alternative Rufnummern für Leitungen über den Standard Alt Telefon und Address3 Felder, die in der Agenten-Bildschirm für Telefonnummern über die Hauptnummer verwendet werden kann zugreifen. Die Erweiterte Telefonnummern können automatisch mit der Auto-Alt-Dial-Funktion in den Kampagneneinstellungen gewählt werden, aber die Aktivierung dieser Agent-Screen-Funktion wird auch für den Agenten zu ermöglichen, um diese Zahlen von ihren Agenten-Bildschirm aufrufen und bearbeiten ihre Informationen. Diese Funktion ist in der Entwicklung und ist derzeit nicht verfügbar.

	<BR>
	<A NAME="campaigns-survey_first_audio_file">
	<BR>
	<B>Umfrage Erste Audio-Datei - </B> Dies ist der Audio-Datei, die gespielt wird, sobald der Kunde nimmt das Telefon, wenn Sie eine Umfrage Kampagne.

	<BR>
	<A NAME="campaigns-survey_dtmf_digits">
	<BR>
	<B>Umfrage DTMF Digits - </B> Dieses Feld ist, in dem Sie die Zahlen, dass ein Kunde kann Presse als eine Option auf eine Umfrage-Kampagne. gültig von DTMF-Ziffern sind0123456789*#. Alle Optionen mit Ausnahme der nicht interessiert, dritte und vierte Stelle Optionen werden nun zu den Umfrage-Methode nennen Weg.

	<BR>
	<A NAME="campaigns-survey_wait_sec">
	<BR>
	<B>Umfrage Warten Sekunden -</B> Dies ist die Anzahl der Sekunden, wenn in Umfrage-Modus wird das System für die Eingabe von der Person genannt, bis die Umfrage oder Drop-Aktion ausgelöst wird, warten. Wird nicht angewendet, wenn die Erhebungsmethode ist HANGUP. Standardwert ist 10 Sekunden.

	<BR>
	<A NAME="campaigns-survey_ni_digit">
	<BR>
	<B>Umfrage nicht interessiert Digit - </B> Dieses Feld ist, wo Sie den Kunden Ziffer gedrückt wird, dass sie nicht daran interessiert sind.

	<BR>
	<A NAME="campaigns-survey_ni_status">
	<BR>
	<B>Umfrage kein Interesse Status -</B> Dieses Feld ist, wo Sie den Status auswählen, die für Not Interested verwendet werden. Wenn DNC verwendet wird und die Kampagne die DNC dann wird die Telefonnummer automatisch in die interne Liste DNC und möglicherweise der kampagnenspezifischen DNC-Liste hinzugefügt werden, wenn diese sich in der Kampagne aktiviert.

	<BR>
	<A NAME="campaigns-survey_opt_in_audio_file">
	<BR>
	<B>Umfrage Opt-in Audio-Datei - </B> Dies ist der Audio-Datei, die gespielt wird, wenn der Kunde hat sich bei der Erhebung, sich nicht-oder nicht reagiert, wenn die Nicht-Antwort-Aktion ist auf OptOut. Nach dieser Audio-Datei gespielt wird, die Umfrage-Methode Maßnahmen ergriffen werden.

	<BR>
	<A NAME="campaigns-survey_ni_audio_file">
	<BR>
	<B>Umfrage nicht interessiert Audio Datei - </B> Dies ist der Audio-Datei, die gespielt wird, wenn der Kunde hat sich aus der Umfrage, nicht Opt-in ist oder nicht reagiert, wenn die Nicht-Antwort-Aktion ist auf OptIn. Nach dieser Audio-Datei gespielt wird, wird der Anruf wird dann.

	<BR>
	<A NAME="campaigns-survey_method">
	<BR>
	<B>Umfrage-Methode - </B> Diese Option legt fest, was geschieht, auf eine Ausschreibung nach den Kunden hat sich in. AGENT_XFER wird der Anruf an den nächsten verfügbaren Agenten. VOICEMAIL wird der Anruf an die Voicemail-Box, die in den Bereich Voicemail. AUSDEHNUNG wird der Kunde auf die Ausdehnung der Definition in der Umfrage XFER Erweiterung Bereich. HANGUP hängt die Kunden. CAMPREC_60_WAV wird der Kunde auf eine Aufnahme, die mit ihrer Antwort, diese Aufnahme wird in einem Ordner mit dem Namen, wie sich die Kampagne innerhalb der Umfrage Kampagne Aufnahme Directory. CALLMENU wird den Kunden mit dem Call-Menü in der Auswahlliste unten definiert schicken. VMAIL_NO_INST wird der Anruf an die Voicemail-Box, die Sie unten definiert haben, senden und keine Aufträge nach der Voicemail-Nachricht zu spielen.

	<BR>
	<A NAME="campaigns-survey_no_response_action">
	<BR>
	<B>Umfrage Nr.-Response Action - </B> Hier wird definiert, was passieren wird, wenn es keine Reaktion auf die Umfrage Frage. OptIn wird nur dann den Aufruf an die an der Umfrage-Methode, wenn der Kunde Pressen eine DTMF-Ziffern. OptOut wird der Kunde auf die Umfrage-Methode, auch wenn sie nicht drücken Sie eine DTMF-Ziffern. DROP sinkt das Gespräch mit der Kampagne Drop-Methode, aber immer noch einloggen den Anruf als PM gespielt Meldungszustand.

	<BR>
	<A NAME="campaigns-survey_response_digit_map">
	<BR>
	<B>Antworten Digit Karte - </B> Das ist der Abschnitt, in dem Sie eine Beschreibung zu jedem DTMF-Ziffern Option, dass der Kunde auswählen kann.

	<BR>
	<A NAME="campaigns-survey_xfer_exten">
	<BR>
	<B>Umfrage XFER Extension - </B>, wenn die Methode der Erhebung AUSDEHNUNG ausgewählt ist dann der Kunde Aufruf richten Sie bitte an dieser dialplan Erweiterung.

	<BR>
	<A NAME="campaigns-survey_camp_record_dir">
	<BR>
	<B>Umfrage Kampagne Aufnahme der Dienststellen - </B>, wenn die Methode der Erhebung CAMPREC_60_WAV ausgewählt ist dann der Kunde Reaktion wird erfasst und in ein Verzeichnis mit dem Namen nach der Kampagne innerhalb dieses Verzeichnisses.

	<BR>
	<A NAME="campaigns-survey_third_digit">
	<BR>
	<B>Umfrage dritte Ziffer -</B> Dies ermöglicht einen dritten Anruf weg, wenn die dritte Stelle in diesem Bereich definiert ist, gedrückt durch den Kunden.

	<BR>
	<A NAME="campaigns-survey_fourth_digit">
	<BR>
	<B>Umfrage vierte Stelle -</B> Dies ermöglicht ein Viertel nennen Weg, wenn die vierte Stelle in diesem Bereich definiert ist, gedrückt durch den Kunden.

	<BR>
	<A NAME="campaigns-survey_third_audio_file">
	<BR>
	<B>Umfrage Dritten Audio-Datei -</B> Dies ist der dritte Audiodatei auf die Auswahl des Kunden von der dritten Stelle Option gespielt werden.

	<BR>
	<A NAME="campaigns-survey_third_status">
	<BR>
	<B>Umfrage Dritten Status -</B> Dies ist der dritte Status für den Anruf auf die Auswahl des Kunden von der dritten Stelle Option verwendet.

	<BR>
	<A NAME="campaigns-survey_third_exten">
	<BR>
	<B>Umfrage dritte Erweiterung -</B> Dies ist die dritte Erweiterung für den Anruf auf die Auswahl des Kunden von der dritten Stelle Option verwendet. Der Standardwert ist 8300, das hängt unmittelbar den Ruf nach der Audio-Datei Meldung abgespielt ist.

	<BR>
	<A NAME="campaigns-agent_display_dialable_leads">
	<BR>
	<B>Agent Anzeige dialable Leads -</B> Diese Option aktiviert wird, wenn die Anzahl der wählbaren führt in die Kampagne in der Agenten-Bildschirm zeigen. Diese Zahl wird in das System einmal pro Minute aktualisiert und wird auf dem Bildschirm des Agenten aktualisiert werden, alle paar Sekunden.

	<BR>
	<A NAME="campaigns-survey_menu_id">
	<BR>
	<B>Umfrage Call-Menü -</B> Wenn die Methode zu CALLMENU gesetzt wird, ist dies das Menü Anruf, dass der Kunde wird gesendet, wenn sie opt-in.

	<BR>
	<A NAME="campaigns-survey_recording">
	<BR>
	<B>Umfrage Recording -</B> Wenn aktiviert, wird diese Aufnahme starten, wenn der Anruf beantwortet wird. Nur empfohlen, wenn das Verfahren nicht eingestellt ist, um an einen Agenten zu übertragen. Standard ist für behinderte N. Wenn gesetzt, um auch Anrufbeantworter Y_WITH_AMD erkannt Nachricht Anrufe werden aufgezeichnet.

	<?php
	}
?>

<BR>
<A NAME="campaigns-next_agent_call">
<BR>
<B>Folgender Vertreter-Anruf -</B> Dieses stellt fest, welches Mittel denfolgenden Anruf empfängt, der vorhanden ist:
 <BR> &nbsp; - random: Bestellungen durch den Zufalls Update-Wert in der Tabelle live_agents
 <BR> &nbsp; - oldest_call_start: Aufträge bis zum dem letzten Mal ein Mittel wurden einem Anrufgeschickt. Resultate in den Mitteln, die insgesamt ungefähr gleicheZahl von Anrufen empfangen.
 <BR> &nbsp; - oldest_call_finish: Aufträge bis zum dem letzten Mal ein Mittel beendeten einen Anruf.Das AKA Mittel, das am längsten wartet, empfängt ersten Anruf.
 <BR> &nbsp; - overall_user_level: Bestellungen des user_level des Mittels wie in der Benutzer-Tabelle definiert eine höhere user_level mehr Anrufe empfangen.
 <BR> &nbsp; - campaign_rank: Aufträge durch den Rank gegeben zum Mittel für dieKampagne. Stark zu am niedrigsten.
 <BR> &nbsp; - campaign_grade_random: ergibt eine höhere Wahrscheinlichkeit, einen Aufruf an die höherwertige Agenten.
 <BR> &nbsp; - fewest_calls: Aufträge durch die Zahl den Anrufen empfangen durch einMittel für diese spezifische inbound Gruppe. Wenige Anrufe zuerst.
 <BR> &nbsp; - longest_wait_time: Bestellungen durch die Zeit, die Agenten aktiv gewartet auf einen Anruf.

<BR>
<A NAME="campaigns-local_call_time">
<BR>
<B>Ortsgespräch-Zeit -</B> Dieses ist, wohin Sie während welcherStunden Sie wählen möchten, wie bis zum der lokalen Zeit infestgestellt sind einstellten in, welchem Sie benennen. Dieses wirddurch Ortsnetzkennzahl gesteuert und wird auf Tageslicht-SparungenZeit eingestellt, wenn anwendbar. Allgemeine Richtlinien in den USAfür Geschäft zum Geschäft ist 9am bis 5pm und Geschäft zu denVerbraucheranrufen ist 9am bis 9pm.

<BR>
<A NAME="campaigns-dial_prefix">
<BR>
<B>Vorwahlknopf-Präfix -</B> Dieses fangen zuläßt einen Wegdes Wählens leicht ändern auf, zum durch eine andere Methode zuerlöschen, ohne ein Umladen im Sternchen zu tun. Rückstellung ist 9gegründet nach einem 91NXXNXXXXXX im dial plan - extensions.conf.

<BR>
<A NAME="campaigns-manual_dial_prefix">
<BR>
<B>Manuelle Vorwahl -</B> Dieses optionale Feld können Sie die Vorwahl nur verwendet werden, bei der Platzierung manuelle Einwahl Anrufe aus dem Agenten-Interface, wie zum Beispiel mit dem Manual Dial Feature-Set, oder Wählen Sie die nächste Nummer, wenn in der MANUAL Zifferblatt Methode oder manuelle alt Nummernwahl, oder geplant vom Benutzer nur Rückrufe. Standard ist leer für Behinderte, die die Vorwahl im Feld oben definiert nutzen werden. Diese Option funktioniert nicht mit dem 3-fach Vorwahl Option stören.

<BR>
<A NAME="campaigns-omit_phone_code">
<BR>
<B>Lassen Sie Telefon-Code Aus -</B> Dieses Feld ermöglicht Ihnen, lassen Sie das Feld phone_code während der Wahl innerhalb des Systems. Zum Beispiel, wenn Sie in Großbritannien wählt aus dem Vereinigten Königreich würden Sie als Ihre 44 in phone_code Feld für alle Leitungen haben, aber Sie wollen einfach nur zu 10 Stellen in Ihrem Rufnummernplan extensions.conf wählen, Anrufe statt 44 dann 10 Stellen zu platzieren. Standardwert ist N.

<BR>
<A NAME="campaigns-campaign_cid">
<BR>
<B>Kampagne CallerID -</B> Dieses Feld ermöglicht das Senden einer benutzerdefinierten callerid Nummer auf den ausgehenden Anrufen. Dies ist die Nummer, die zeigen, auf dem callerid der Person, die Sie anrufen. Der Standardwert ist UNKNOWN. Wenn Sie mit T1-oder E1-Leitungen nach draußen wählen diese Option ist nur verfügbar, wenn Sie PRIs werden - ISDN oder T1-E1 - das ist die individuelle callerid Funktion eingeschaltet haben, wird dies nicht mit beraubt-Bit-Service-RBS-Schaltungen funktionieren. Dies wird auch durch die meisten VoIP-SIP-oder IAX-Trunks-Anbieter, die dynamische Outbound callerID erlauben zu arbeiten. Der Brauch callerID gilt nur für Anrufe für die Kampagne direkt gestellt wird, werden alle Anrufe oder 3rd-Party-Transfers die benutzerdefinierte callerID nicht senden. HINWEIS: Manchmal setzen UNKNOWN oder PRIVATE im Feld ergeben die Absendung Ihrer Standard callerID Nummer von Ihrem Träger mit den Anrufen. Sie können dies zu testen und legte 0000000000 in der callerid Feld statt, wenn Sie nicht möchten, dass Sie CallerID senden.

<BR>
<A NAME="campaigns-use_custom_cid">
<BR>
<B>Benutzerdefinierte CallerID -</B> Wenn auf Y gesetzt, ermöglicht Ihnen diese Option, um die security_phrase Feld in der Liste Tabelle verwenden als CallerID zu senden, bei der Platzierung für die jeweilige Führung. Wenn dieses Feld hat keine CID in der Kampagne dann CallerID oben definiert werden stattdessen verwendet werden. Diese Option wird die Liste CallerID Override deaktivieren, wenn es in der security_phrase Feld ein Geschenk CID. Der Standardwert ist N. Wenn auf area Sie die Möglichkeit, in die AC-CID Untermenü zu gehen und zu definieren, um mehrere callerids pro area verwendet werden müssen.

<BR>
<A NAME="campaigns-campaign_rec_exten">
<BR>
<B>Kampagne Rec extension -</B> Dieses Feld ermöglicht eine individuelle Aufzeichnung Erweiterung mit dem System verwendet werden. Dies ermöglicht Ihnen, verschiedene Erweiterungen je nachdem, wie lange Sie ermöglichen eine maximale Aufnahme und welche Art von Codecs, die Sie aufnehmen in. Die Standardumfang wollen, ist 8309 verwenden möchten, die, wenn Sie folgen Sie den SCRATCH_INSTALL Beispiele werden im WAV-Format für bis zu notieren eine Stunde. Eine weitere Option in den Beispielen enthalten sind, die im GSM-8310-Format für bis zu einer Stunde aufgezeichnet werden. Die Aufnahmezeit kann durch die Erhöhung der Einstellung in der Server-Modification-Bildschirm im Verwaltungsabschnitt verlängert werden.

<BR>
<A NAME="campaigns-campaign_recording">
<BR>
<B>Kampagne Aufnahme -</B> Dieses Menü erlaubt Ihnen, zu wählen,welches Niveau der Aufnahme auf dieser Kampagne erlaubt wird. NIEsperrt Aufnahme auf dem Klienten. ONDEMAND ist die Rückstellung underlaubt dem Vertreter zu notieren zu beginnen und, zu stoppen, wiegebraucht. ALLCALLS beginnt Aufnahme auf dem Klienten, wann immer einAnruf zu einem Mittel geschickt wird. ALLFORCE will start recording on the client whenever a call is sent to an agent giving the agent no option to stop recording. For ALLCALLS and ALLFORCE there is an option to use the Das Notieren Verzögert to cut down on very short recordings and reduce system load.

<BR>
<A NAME="campaigns-campaign_rec_filename">
<BR>
<B>Kampagne Rec Dateiname -</B> In diesem Feld können Sie den Namen der Aufnahme anpassen, wenn Kampagne Aufnahme ist ONDEMAND oder ALLCALLS. Die Variablen sind erlaubt CAMPAIGN INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH MITTELVENDORLEADCODE LEADID CALLID. Der Standardwert ist FULLDATE_MITTELund möchte dieses 20051020-103108_6666 aussehen. Ein weiteres Beispiel ist die CAMPAIGN_TINYDATE_CUSTPHONE wie dieser TESTCAMP_51020103108_3125551212 aussehen würde. Te resultierende Dateiname muss weniger als 90 Zeichen lang sein.

<BR>
<A NAME="campaigns-allcalls_delay">
<BR>
<B>Das Notieren verzögert -</B> für ALLCALLS und ALLFORCE nur Aufnahme.Dieser Einstellung Wille verzögert das Beginnen der Aufnahme beiallen Anrufen für die Zahl den Sekunden, die diesbezüglichspezifiziert werden, auffangen. Rückstellung ist 0.

<BR>
<A NAME="campaigns-per_call_notes">
<BR>
<B>Rufen Sie Noten pro Anruf -</B> Wenn Sie diese Option auf Enabled ermöglicht es Agenten in Notes für jeden Anruf sie im Agent-Interface handhaben eingeben. Die Noten Eingabefeld unter dem Feld Bemerkungen im Agent-Interface erscheinen. Auch, wenn der Agent Benutzer-Gruppe ist berechtigt, Anzeigen von Anruflisten dann der Agent in der Lage, Vergangenheit Anrufanmerkungen für eine Führung jederzeit einsehen. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-hide_call_log_info">
<BR>
<B>Anrufliste ausblenden Info -</B> Die Aktivierung dieser Option wird jede Anrufprotokoll verbergen oder rufen Zählung Informationen, wenn Blei Informationen auf der Agenten-Bildschirm angezeigt. Standardwert ist N.

<BR>
<A NAME="campaigns-agent_lead_search">
<BR>
<B>Agent-Lead-Suche -</B> Wenn diese Option auf ENABLED ermöglicht Agenten für Leads durchsuchen und Artikel lesen Blei Informationen während der Pause im Agent-Interface. Auch, wenn der Agent Benutzer-Gruppe ist berechtigt, Anzeigen von Anruflisten dann der Agent in der Lage, Vergangenheit Anrufanmerkungen für jeden Lead dass die dort verfügbaren Informationen zu sehen. Der Standardwert ist DISABLED. LIVE_CALL_INBOUND wird Suche nach einer Führung während eines eingehenden Anrufs nur zu ermöglichen. LIVE_CALL_INBOUND_AND_MANUAL wird Suche nach einer Führung während eines eingehenden Anrufs oder während der Pause zu ermöglichen. Als Lead-Suche auf einem Live eingehenden Anruf verwendet wird, wird die Führung des Gesprächs, wenn es um den Agenten gingen zu einem Status LSMERG geändert werden, und die Protokolle für den Anruf wird modifiziert, um dem Agenten eine Verknüpfung ausgewählt Blei stattdessen.

<BR>
<A NAME="campaigns-agent_lead_search_method">
<BR>
<B>Agent-Lead-Suchmethode -</B> Wenn Agent Lead-Suche aktiviert ist, legt diese Einstellung in der der Agent erlaubt sein wird, für Leads zu suchen. SYSTEM wird das gesamte System zu suchen, wird im Inneren CAMPAIGNLISTS alle aktiven Listen innerhalb der Kampagne zu suchen, wird im Inneren CAMPLISTS_ALL alle aktiven und inaktiven Listen innerhalb der Kampagne suchen, listet nur suchen im Manual Dial-ID-Liste, wie in der Kampagne definiert . Die Standardeinstellung ist CAMPLISTS_ALL. Eine dieser Optionen mit USER_ vor werden nur innerhalb von Leads, die das Feld Besitzer Anpassung der Benutzer-ID des Agenten suchen haben, werden die Optionen mit GROUP_ vor nur innerhalb von Leads, die das Feld Besitzer Anpassung der Benutzer-Gruppe ist, dass der Benutzer zu suchen, Mitglied, mit den Optionen TERRITORY_ vor werden nur innerhalb von Leads, die das Feld Besitzer passend zu den Gebieten, die der Agent ausgewählt haben, suchen.

<BR>
<A NAME="campaigns-campaign_script">
<BR>
<B>Kampagne Index -</B> Dieses Menü erlaubt Ihnen, den Index zu wählen,der auf dem Mittelschirm für diese Kampagne erscheint. Wählen SieKEINE vor, keinen Index für diese Kampagne zu zeigen.

<BR>
<A NAME="campaigns-get_call_launch">
<BR>
<B>Erhalten Sie Anruf-Produkteinführung -</B> Dieses Menü erlaubt Ihnenzu wählen, ob Sie Automobil-ausstoßen die Netz-Form Seite in einemunterschiedlichen Fenster, Auto-switch zum INDEX-Vorsprung oder tunnichts wünschen, wenn ein Anruf zum Mittel für diese Kampagnegeschickt wird. Wenn benutzerdefinierte Liste Felder auf Ihrem System aktiviert sind, bilden die Registerkarte Formular bei der Verbindung eines Anrufs öffnen, um einen Agenten.

<BR>
<A NAME="campaigns-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> In diesen Feldern können für Sie zwei Sätze von Über Konferenz-und DTMF-Presets zu haben. Wenn der Anruf oder eine Kampagne eingelegt ist, wird der Agent Bildschirm zwei Tasten auf dem Transfer-Konferenz Rahmen und zeigen automatischen Auffüllen der Nummer-zu-Zifferblatt und die Send-dtmf Felder, wenn gedrückt. Wenn Sie Beratende Transfers erlauben wollen, ein Fronter auf eine engere, haben die Mittel zu verwenden der Beratenden Kontrollkästchen, die nicht für Dritte nicht-Agent Beratungsgespräche nicht funktioniert. Für diejenigen, die einfach nur der Agent auf die DFÜ mit Kunden-Taste. Dann kann der Agent nur LASSEN-3 POL-CALL und gehen Sie zum nächsten Anruf. Wenn Sie Blind Transfers von Kunden zu einem AGI-Script für Protokollierung oder ein IVR erlauben wollen, dann legen Sie AXFER der Zahl-to-Dial-Feld. Sie können auch eine benutzerdefinierte Erweiterung nach dem AXFER, zum Beispiel, wenn Sie einen Anruf zu einem speziellen IVR Sie Verlängerung 83900 eingestellt haben Sie AXFER83900 in der Feldnummer-zu-Wahl setzen wollen.

<BR>
<A NAME="campaigns-quick_transfer_button">
<BR>
<B>Quick Transfer Button -</B> Diese Option wird ein Quick Transfer-Taste, um den Agenten Bildschirm unter dem Transfer-Conf Schaltfläche -Hinzufügen- klicken, dass man blind Weiterleitung der Gespräche, damit werden die ausgewählten In-Group oder einer Nummer. IN_GROUP werden Anrufe an die Standard-Xfer für diese Kampagne, oder In-Group, wenn es einen eingehenden Anruf. Die Voreinstellungen werden die Aufrufe an die gewählte Voreinstellung. Der Standardwert ist N für Behinderte. Die LOCKED Optionen werden verwendet, um den Wert auf die Schnelle Transfer-Taste sperren, auch wenn der Agent verwendet die Übertragung Konferenz-Funktionen während eines Gesprächs vor der Verwendung des Quick-Transfer-Taste.

<BR>
<A NAME="campaigns-custom_3way_button_transfer">
<BR>
<B>Custom 3-Wege-Taste Transfer- -</B> Diese Option wird eine benutzerdefinierte Schaltfläche Übertragung an den Agenten-Bildschirm unterhalb der Transfer-Conf-Taste, die es einem Klick Drei-Wege-Anrufe über das ausgewählte Preset oder ein Feld hinzufügen. Die PRESET_ Optionen findet Anrufe mit der Preset-Wert definiert. Die field_groupj Optionen findet Anrufe mit der Nummer in das ausgewählte Feld aus der Führung. BEHINDERTE nicht zeigen Sie die Taste auf dem Agenten-Bildschirm. Die PARK_ Optionen wird den Kunden vor dem Wählen zu parken. Der Standardwert ist DISABLED. Die VIEW_PRESET Option öffnen Sie einfach die Übertragung Rahmen und die voreingestellte Frame. Die VIEW_CONTACTS Option wird eine Suche Kontakte-Fenster zu öffnen, wird dies nur funktionieren, wenn Presets zu aktivieren KONTAKTE eingestellt ist.

<BR>
<A NAME="campaigns-prepopulate_transfer_preset">
<BR>
<B>Vorab gefüllt Transfer Preset -</B> Diese Option wird in der Anzahl zu Dial-Feld füllen Sie das Transfer Conference Rahmen des Bildschirms, wenn der Agent definiert. Der Standardwert ist N für Behinderte.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Aktivieren Transfer-Presets -</B> Diese Option ermöglicht es den Presets Untermenü, an der Spitze der Kampagne Modifikation Seite erscheinen, und auch Sie werden die Fähigkeit zur Preset-Rufnummern für Bevollmächtigte, um in der Transfer-Konferenz Rahmen des Agent-Interface verwenden, genau angeben. Der Standardwert ist DISABLED. Kontakte ist ein Option nur, wenn contact_information auf Ihrem System aktiviert ist, das ist eine benutzerdefinierte Funktion.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Aktivieren Transfer-Presets -</B> Diese Option ermöglicht es den Presets Untermenü, an der Spitze der Kampagne Modifikation Seite erscheinen, und auch Sie werden die Fähigkeit zur Preset-Rufnummern für Bevollmächtigte, um in der Transfer-Konferenz Rahmen des Agent-Interface verwenden, genau angeben. Der Standardwert ist DISABLED. Kontakte ist ein Option nur, wenn contact_information auf Ihrem System aktiviert ist, das ist eine benutzerdefinierte Funktion.

<BR>
<A NAME="campaigns-hide_xfer_number_to_dial">
<BR>
<B>Übertragung ausblenden Zu wählende Nummer -</B> Diese Option blendet die Nummer Feld in der Transfer-Konferenz Rahmen des Agent-Interfaces Dial. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call">
<BR>
<B>Anruf parken IVR -</B> Diese Option ermöglicht ein Agent einen Anruf mit einem separaten IVR-PARK Taste CALL auf ihren Agenten-Interface parken, wenn diese Option aktiviert ist oder ENABLED_PARK_ONLY. Die ENABLED_PARK_ONLY Option ermöglicht es dem Agenten, um den Anruf zu parken, aber nicht senden klicken, um das Gespräch wie die Option Aktiviert abzurufen. Die ENABLED_BUTTON_HIDDEN Option ermöglicht die Funktion nur über die API. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call_agi">
<BR>
<B>Anruf parken IVR AGI -</B> Wenn der Anruf parken IVR-Bereich nicht deaktiviert ist, dann wird dieses Feld als String AGI Anwendung, dass der Kunde gesendet wird verwendet. Dies ist eine Einstellung, die vom Administrator festgelegt werden sollte, wenn möglich.

<BR>
<A NAME="campaigns-timer_action">
<BR>
<B>Timer-Aktion -</B> Mit dieser Funktion können Sie die Aktionen nach einer bestimmten Zeit auslösen. D1 und D2 DIAL Optionen werden einen Aufruf der Transfer Conference Anzahl Presets starten und senden sie an die Agenten-Sitzung wird dies in der Regel für einfache IVR Validierung AGI-Anwendungen oder nur eine vorher aufgenommene Nachricht zu spielen verwendet. WebForm öffnet sich das Web-Formular-Adresse. MESSAGE_ONLY wird einfach Anzeige die Meldung, dass unten in das Feld ist. Niemand wird diese Funktion zu deaktivieren und ist der Standard -. HANGUP hängen bleiben wird das Gespräch, wenn der Timer ausgelöst wird, wird CALLMENU den Anruf an den Anruf-Timer-Menü im Feld Ziel angegeben Aktion zu senden, EXTENSION, wird der Anruf an die Erweiterung, die in der Timer-Aktion Feld Ziel angegeben wird, zu senden, wird IN_GROUP senden der Aufruf an die In-Group in den Timer-Aktion Feld Ziel angegeben.

<BR>
<A NAME="campaigns-timer_action_message">
<BR>
<B>Timer-Aktion Message -</B> Dies ist die Botschaft, die auf dem Bildschirm erscheint Agenten zum Zeitpunkt der Timer-Aktion ausgelöst wird.

<BR>
<A NAME="campaigns-timer_action_seconds">
<BR>
<B>Timer-Aktion Sekunden -</B> Dies ist die Zeit nach dem Anruf des Kunden, dass die Timer-Aktion wird ausgelöst, verbunden. Der Standardwert ist -1, die auch inaktiv.

<BR>
<A NAME="campaigns-timer_action_destination">
<BR>
<B>Timer-Aktion Reiseziel -</B> Dieses Feld ist, wo Sie das Call-Menü, Erweiterung oder In-Group angeben, dass Sie den Anruf an, wenn die Zeit Aktion zu CALLMENU, Erweiterung oder IN_GROUP eingestellt werden soll. Standard ist leer.

<BR>
<A NAME="campaigns-scheduled_callbacks">
<BR>
<B>Zeitlich geplante Wiederholungsbesuche -</B> Diese Wahl erlaubt einemMittel zur Einteilung einen Anruf als CALLBK und wählt die Daten unddie Zeit, an denen die Leitung reaktiviert wird.

<BR>
<A NAME="campaigns-scheduled_callbacks_alert">
<BR>
<B>Geplante Rückrufe Alarm -</B> Diese Option ermöglicht die Rückrufe Statuszeile im Agent-Interface, rot zu sein, blinken oder blinkt rot, wenn es sich AGENTONLY Rückrufe, die ihre Zeit und das Datum Trigger getroffen haben soll. Der Standardwert ist NONE für Standard-Status-Zeile. Die DEFER Optionen auf zu blinken und die Anzeige-oder rot auf, wenn Sie die Rückrufe zu überprüfen, bis die Anzahl der Rückrufe Änderungen.

<BR>
<A NAME="campaigns-scheduled_callbacks_count">
<BR>
<B>Geplante Rückrufe Count -</B> These options allows you to limit the viewable callbacks in the agent callback alert section on the agent screen, to only LIVE callbacks.  LIVE callbacks are user-only scheduled callbacks that have hit their trigger date and time. ACTIVE call backs are user-only callbacks that are active in the system but have not yet triggered.  You can view both ACTIVE and LIVE callbacks by selecting ALL_ACTIVE.  Default is ALL_ACTIVE.

<BR>
<A NAME="campaigns-callback_days_limit">
<BR>
<B>Geplante Rückrufe Tage zu begrenzen -</B> Diese Option ermöglicht Ihnen, den Agenten zu reduzieren geplant Rückrufe Kalender, um eine wählbare Anzahl von Tagen ab heute, die volle 12-monatigen Kalender wird immer noch angezeigt, aber nur die festgelegte Anzahl von Tagen wird auswählbar sein. Standard ist 0 für unbegrenzt.

<BR>
<A NAME="campaigns-callback_hours_block">
<BR>
<B>Geplante Rückrufe Stunden-Block -</B> Diese Option ermöglicht es Ihnen, eine USERONLY geplanten Rückruf von auf dem Agent Rückrufliste bis X Stunden angezeigt, nachdem diese festgelegt wurde zu beschränken. Standard ist 0 für kein Block.

<BR>
<A NAME="campaigns-callback_list_calltime">
<BR>
<B>Geplante Rückrufe Calltime-Block -</B> Diese Option aktiviert, wenn das geplante Rückruf in der Agenten-Callback-Liste aus, die gewählt zu verhindern, wenn es außerhalb des planmäßigen calltime für die Kampagne ist. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-my_callback_option">
<BR>
<B>Meine Rückrufe Checkbox Standard -</B> Diese Option ermöglicht es Ihnen, Voreinstellung der Eig.Rückruf Checkbox auf der Callback-Agent geplant Bildschirm. Überprüft wird das Kontrollkästchen automatisch, ob für jeden Anruf. Die Standardeinstellung ist UNCHECKED.

<BR>
<A NAME="campaigns-wrapup_seconds">
<BR>
<B>Sekunden Nachbereitung -</B> Anzahl der Sekunden, die ein Agent bis zum nächsten erhaltenen Anruf oder Wählen eines anderen Anrufs warten muss. Die Zeit beginnt, sobald ein Agent bei seinem Kunden aufgelegt hat - oder im Fall der alternativen Nummernwahl, wenn ein Agent das Telefonat beendet - Standard ist 0 Sekunden. Wenn die Zeit abgelaufen ist bevor der Agent den Anruf eingeordnet hat, wird der Agent dennoch nicht zum nächsten Anruf kommen, bevor er eine Einteilung gewählt hat.

<BR>
<A NAME="campaigns-wrapup_message">
<BR>
<B>Nachbereitungsnachricht -</B> Dies ist eine Kampagnen-spezifische Nachricht, die bei gesetzten Sekunden Nachbereitung auf dem Nachbereitungsbildschirm angezeigt wird.

<BR>
<A NAME="campaigns-disable_dispo_screen">
<BR>
<B>Deaktivieren Sie Dispo-Screen -</B> Diese Option ermöglicht es Ihnen, die Disposition Bildschirm im Agent-Interface zu deaktivieren. Das Deaktivieren der Dispo-Status Feld darunter muss für diese Option funktioniert gefüllt werden. Die Standardeinstellung ist DISPO_ENABLED. Die DISPO_SELECT_DISABLED Option wird nicht deaktivieren Sie die Dispo-Bildschirm vollständig, sondern wird das Dispo-Bildschirm ohne jegliche Verfügungen anzeigen zu lassen, sollte diese Option nur verwenden, wenn Sie zu zwingen, Ihren Agenten zu Ihrem CRM-Software, die den Status an das System sendet über die API nutzen möchten werden.

<BR>
<A NAME="campaigns-disable_dispo_status">
<BR>
<B>Deaktivieren Sie Dispo-Status -</B> Wenn der Dispo-Screen-Option deaktivieren, um DISPO_DISABLED gesetzt ist, dann muss dieses Feld ausgefüllt Du jede Verfügung, die Sie für dieses Feld verwenden können, solange sie von 1 bis 6 Zeichen lang mit nur Buchstaben und Zahlen ist zu.

<BR>
<A NAME="campaigns-dead_max">
<BR>
<B>Tote Anruf Max Sekunden -</B> Wenn diese größer als 0 gesetzt wird, nachdem ein Kunde legt auf und der Agent nicht auf dem Auflegen Kunden Schaltfläche in dieser Anzahl von Sekunden angeklickt wird, wird der Anruf automatisch aufgehängt werden, wird der Status unten eingestellt werden und der Agent wird angehalten. Standard ist 0 für Behinderte.

<BR>
<A NAME="campaigns-dead_max_dispo">
<BR>
<B>Tote Anruf Max-Status -</B> Wenn Tote Anruf Max Sekunden aktiviert ist, ist dies der Status für den Verbindungsaufbau, wenn der Agent tot Gespräch nicht über die Anzahl der Sekunden zuvor benannten aufgehängt. Standard ist DCMX.

<BR>
<A NAME="campaigns-dispo_max">
<BR>
<B>Dispo Anruf Max Sekunden -</B> Wenn diese größer als 0 gesetzt ist, und der Agent nicht in dieser Anzahl von Sekunden eine Disposition Status ausgewählt, wird der Anruf automatisch in den Status unten eingestellt werden und der Agent wird angehalten. Standard ist 0 für Behinderte.

<BR>
<A NAME="campaigns-dispo_max_dispo">
<BR>
<B>Dispo Anruf Max-Status -</B> Wenn Dispo Anruf Max Sekunden aktiviert ist, ist dies der Status für den Verbindungsaufbau, wenn der Agent hat nicht einen Status über die Anzahl der Sekunden zuvor benannten ausgewählt. Standard ist DISMX.

<BR>
<A NAME="campaigns-pause_max">
<BR>
<B>Pause-Agent Max Sekunden -</B> Wenn diese größer als 0 in dieser Anzahl von Sekunden eingestellt, und der Agent hat nicht aus der PAUSE-Status gegangen ist, wird der Agent automatisch aus dem Agenten-Bildschirm eingeloggt sein. Standard ist 0 für Behinderte.

<BR>
<A NAME="campaigns-screen_labels">
<BR>
<B>Agent-Bildschirm Labels -</B> Sie können eine Reihe von Agenten-Bildschirm Etiketten mit dieser Option zu nutzen. Standard ist - SYSTEM-EINSTELLUNGEN - für die Standard-Etiketten.

<BR>
<A NAME="campaigns-status_display_fields">
<BR>
<B>Status Anzeige-Felder -</B> Sie können auswählen, welche Variablen für Anrufe in der Statuszeile des Agenten-Bildschirm wird angezeigt. CALLID wird die 20-stelligen eindeutigen Anruf-ID anzuzeigen, LEADID wird das System Blei-ID anzuzeigen, wird die Liste ListId ID. Die Standardeinstellung ist CALLID.

<BR>
<A NAME="campaigns-use_internal_dnc">
<BR>
<B>Interne DNC Liste des Gebrauch-</B>- definiert dieses, ob dieseKampagne Leitungen gegen die interne DNC Liste filtern soll. Wenn esauf Y eingestellt wird, sucht der Zufuhrbehälter nach jederTelefonnummer in der DNC Liste, bevor er sie in den Zufuhrbehälterlegt. Wenn er in der DNC Liste ist, dann, das sie dieses führt Statuszu DNCL also ändert, kann sie nicht gewählt werden. Rückstellungist N. Die AREACODE Option ist ebenso wie die Y-Option, nur dass diese Filter auch einen ganzen Vorwahl in Nordamerika dazu verwendet werden gewählt, in diesem Fall mit dem 201XXXXXXX Eintrag in der Liste DNC würde alle Anrufe in den 201 Ortsvorwahl zu blockieren, wenn aktiviert.

<BR>
<A NAME="campaigns-use_campaign_dnc">
<BR>
<B>Verwenden Sie Kampagne DNC List - </B> Diese legt fest, ob dieser Kampagne ist es, Filter führt gegen eine DNC-Liste, die sich speziell auf die Kampagne nur. Wenn es auf Y, der Trichter wird für jede Telefonnummer in der Kampagne-spezifische DNC-Liste, bevor sie in den Trichter. Wenn es in der Kampagne-spezifische DNC Liste, dann wird sich ändern, die Status DNCC, so kann er nicht gewählt werden. Der Standardwert ist N. Die AREACODE Option ist ebenso wie die Y-Option, nur dass diese Filter auch einen ganzen Vorwahl in Nordamerika dazu verwendet werden gewählt, in diesem Fall mit dem 201XXXXXXX Eintrag in der Liste DNC würde alle Anrufe in den 201 Ortsvorwahl zu blockieren, wenn aktiviert.

<BR>
<A NAME="campaigns-use_other_campaign_dnc">
<BR>
<B>Andere Kampagne DNC -</B> Wenn die Option Use Kampagne DNC Liste aktiviert ist, kann diese Option können Sie eine andere Kampagne DNC Liste verwenden, einfach die Kampagnen-ID der anderen Kampagne in diesem Bereich. Wenn Sie diese Option verwenden, wird die ursprüngliche Kampagne DNC Liste nicht mehr überprüft werden, nur die Andere Kampagne DNC Liste verwendet werden soll. Dies hat keinen Einfluss auf die Verwendung der internen Systemuhr DNC Liste. Standardwert ist Empty.

<BR>
<A NAME="campaigns-closer_campaigns">
<BR>
<B>Gewährte Inbound Gruppen -</B> For CLOSER campaigns only. Here is where you select the inbound groups you want agents in this CLOSER campaign to be able to take calls from. It is important for BLENDED inbound-outbound campaigns only to select the inbound groups that are used for agents in this campaign. The calls coming into the inbound groups selected here will be counted as active calls for a blended campaign even if all agents in the campaign are not logged in to receive calls from all of those selected inbound groups.

<BR>
<A NAME="campaigns-agent_pause_codes_active">
<BR>
<B>AgentPause CodesAktive -</B> Ermöglicht Agenten, um eine Pause Code auswählen, wenn sie auf der PAUSE-Taste in der Agenten-Bildschirm klicken.Pause Codessind einstellbar pro Kampagne an der Unterseite der Kampagne Ansicht Detailbild und sind in der agent_log Tabelle gespeichert. Der Standardwert ist N. FORCE wird die Agenten zu zwingen, eine PAUSE-Code wählen, ob sie auf der PAUSE-Taste klicken.

<BR>
<A NAME="campaigns-auto_pause_precall">
<BR>
<B>Auto Pause Pre-Call Work -</B> Im Auto-Dial-Modus, wenn diese Einstellung aktiviert wird gesetzt, um den Agenten automatisch angehalten, wenn der Agent klickt auf eine der folgenden Funktionen, die ihnen inne-Manual Dial, Fast Dial, Lead Suche, Call Log View benötigt, überprüfen Sie Rückrufe, Geben Sie Pause Code. Standard ist für inaktive N.

<BR>
<A NAME="campaigns-auto_resume_precall">
<BR>
<B>Auto-Resume-Pre-Call Work -</B> Im Auto-Dial-Modus, wenn diese Einstellung aktiviert ist wird der Agent an aktiven automatisch eingestellt, wenn der Agent klickt, die aus oder Stempeln, auf eine der folgenden Funktionen, die ihnen inne-Manual Dial, Fast Dial, Lead Suche sein muss, Call Melden Sie sehen, Rückrufe, Enter Pause Code. Standard ist für inaktive N.

<BR>
<A NAME="campaigns-auto_pause_precall_code">
<BR>
<B>Auto Pause Pre-Call-Vorwahl -</B> Wenn das Auto Pause Pre-Call Work-Funktion über aktiv ist, und AgentPause Codesaktiv ist, wird diese Einstellung die Pause-Code, der verwendet wird, wenn der Agent für diese Aktivitäten angehalten wird. Die Standardeinstellung ist PRECAL.

<BR>
<A NAME="campaigns-disable_alter_custdata">
<BR>
<B>Sperren Sie ändern Kunde Daten -</B> wenn Satz zu Y, nicht irgendwelchedes Kunde Datensatzes wenn Einteilungen eines Mittels der Anrufändert. Rückstellung ist N.

<BR>
<A NAME="campaigns-disable_alter_custphone">
<BR>
<B>Deaktivieren Alter Customer Telefon - </B> Bei der Einstellung Y, ändert nichts an den Kunden die Telefonnummer, wenn ein Agent Verfügungen den Anruf zu tätigen. Der Standardwert ist Y. Verwenden Sie die Verberge-Option zum vollständigen Entfernen der Kunde die Telefonnummer aus der Agenten-Anzeige.

<BR>
<A NAME="campaigns-display_queue_count">
<BR>
<B>Maklers Queue Count - </B> Wenn Sie auf Y, wenn ein Kunde wartet auf einen Agenten, der Queue fordert Anzeige oben auf der Agenten-Bildschirm wird rot und zeigen die Anzahl der wartenden Anrufe. Der Standardwert ist Y.

<BR>
<A NAME="campaigns-manual_dial_override">
<BR>
<B>Manuelle Wahl Override -</B> Die Einstellung kann der Benutzer überschreiben die Einstellung für die manuelle Einwahl Möglichkeit für Agenten, wenn sie in dieser Kampagne werden protokolliert. NONE wird die Benutzer-Einstellung folgen, ALLOW_ALL wird jedes Mittel in dieser Kampagne auf manuelle Einwahl Anrufe getätigt werden protokolliert zulassen, wird DISABLE_ALL niemandem erlauben, in diese Kampagne angemeldet, um manuelle Einwahl von Anrufen. Der Standardwert ist NONE.

<BR>
<A NAME="campaigns-manual_dial_list_id">
<BR>
<B>Manual Dial Liste Identifikation -</B> Der Standard LIST_ID verwendet werden, wenn ein Agent legt einen Handmelder und eine neue Leaddatensatz wird in der Liste Tabelle erstellt. Der Standardwert ist 999. Dieses Feld kann nur Ziffern enthalten.

<BR>
<A NAME="campaigns-manual_dial_filter">
<BR>
<B>Manuelle Wahl Filter - </B> Auf diese Weise können Sie zum Filtern der Anrufe, die Agenten sind im manuellen Modus wählen Sie für diese Kampagne durch eine Kombination der folgenden: DNC - die Kick Out, CAMPAIGNLISTS - die Zahl muss innerhalb der Listen für die Kampagne KEINE - kein Filter auf manuelle Einwahl oder schnelle Einwahl Listen. CAMPLISTS_ALL - wird inaktiv Listen bei der Suche nach der Zahl enthalten.

<BR>
<A NAME="campaigns-manual_dial_search_checkbox">
<BR>
<B>Manuelle Wahl suchen Checkbox -</B> Damit können Sie definieren, wenn Sie wollen, dass die manuelle Einwahl Suche Checkbox standardmäßig oder nicht gewählt werden. Wenn eine Option mit RESET gewählt wird, dann das Kontrollkästchen wird nach jedem Anruf zurückgesetzt werden. Standardwert wird ausgewählt.

<BR>
<A NAME="campaigns-manual_preview_dial">
<BR>
<B>Manuelle Vorschau Dial -</B> Dadurch kann der Agent im manuellen Modus Zifferblatt, die Führung zu Informationen sehen, wenn sie nächstes klicken Sie auf DFÜ-Nummer wählen, bevor sie aktiv den Anruf. Es ist ein optionaler Link, um das Blei überspringen und zum nächsten, wenn ausgewählt. Die Standardeinstellung ist PREVIEW_AND_SKIP.

<BR>
<A NAME="campaigns-manual_dial_lead_id">
<BR>
<B>Manuelle Wahl von Lead-ID -</B> Dies ermöglicht es dem Agenten im manuellen Modus, um einen Wahlaufruf von lead_id statt einer Telefonnummer zu platzieren. Standardwert ist N für Behinderte.

<BR>
<A NAME="campaigns-api_manual_dial">
<BR>
<B>Manuelle Wahl API -</B> Diese Option ermöglicht es Ihnen, die Agent-API gesetzt, um entweder ein Anruf zu einem Zeitpunkt, STANDARD, oder die Fähigkeit, Schlange stehen manuelle Einwahl Anrufe zu tätigen und haben sie automatisch zu wählen, sobald der Agent geht auf Pause oder ist verfügbar, um ihre nächsten Aufruf der Rücknahme Option, um die automatische Wahl der Anrufe, Warteschlange oder QUEUE_AND_AUTOCALL die die gleiche wie Warteschlange, aber ohne die Möglichkeit, die automatische Anwahl dieser Anrufe zu deaktivieren ist deaktiviert. Wenn ein Agent hat mehr als einen Aufruf für sie in der Warteschlange werden sie die zählen, wie viele manuelle Einwahl Anrufe in der Warteschlange direkt unterhalb der Pause-Taste zu sehen, oder wählen Sie Nächste Nummer drücken. Wir schlagen vor, dass, wenn Warteschlange verwendet wird, dass Sie API-Aktionen mit der Vorschaufunktion = YES Option, damit Sie nicht wiederholt werden Tätigen von Anrufen für den Agenten ohne vorherige Ankündigung zu senden. Auch, wenn mit Queue und stark durch manuelle Einwahl Anrufe in einer nicht MANUAL Zifferblatt Methode, würden wir empfehlen, die Agent-Pause nach jedem Call-Option auf Y Voreingestellt ist standard.

<BR>
<A NAME="campaigns-manual_dial_call_time_check">
<BR>
<B>Manuell überprüfen Sie Anrufdauer -</B> Wenn diese Option aktiviert ist, wird es prüfen alle Anrufe auf manuelle Einwahl sicherzustellen, dass sie innerhalb der Zeit während des Bereitschaftsdienstes Einstellungen für die Kampagne eingestellt. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-manual_dial_cid">
<BR>
<B>Manuelle Wahl CID -</B> Dieser legt fest, ob ein Agent macht manuelle Einwahl Anrufe haben die Kampagne callerID Einstellungen verwendet, oder ihrer Bevollmächtigten Telefon callerID Einstellungen verwendet. Die Standardeinstellung ist KAMPAGNE. Wenn die Use Custom CID Kampagne Option aktiviert ist oder die Liste Kampagne CID Override Einstellung wird verwendet, wird diese Einstellung ignoriert werden.

<BR>
<A NAME="campaigns-post_phone_time_diff_alert">
<BR>
<B>Telefon Post Time Difference-Alert -</B> Dieses Handbuch-Einwahl nur Funktion, falls aktiviert, wird eine Warnung anzeigen, wenn die Zeitzone für die Führung Postleitzahl oder eine Postleitzahl ein, unterscheidet sich von der Zeitzone von der Vorwahl der Rufnummer für die Führung. Die OUTSIDE_CALLTIME_ONLY Option wird nur die Warnung, wenn die beiden Zeitzonen unterschiedlich sind und eine der Zeitzonen liegt außerhalb des Bereitschaftsdienstes für die Kampagne ausgewählt. OUTSIDE_CALLTIME_PHONE prüfen lediglich die Zeitzone der Telefonnummer des Blei-und alarmieren, wenn es außerhalb des lokalen Zeit während des Bereitschaftsdienstes ist. OUTSIDE_CALLTIME_POSTAL prüfen lediglich die Zeitzone der Postleitzahl des Blei-und alarmieren, wenn es außerhalb des lokalen Zeit während des Bereitschaftsdienstes ist. OUTSIDE_CALLTIME_BOTH wird die Postleitzahl und die Telefonnummer für Sein im Ortsgespräch Zeit zu überprüfen, auch wenn sie in der gleichen Zeitzone befinden. Diese Warnungen werden in der Anrufliste Info, Rückrufe List-Info, Suchergebnisse Info, wenn eine Leitung gewählt wird und wenn ein Interessent eine Vorschau zu zeigen. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-in_group_dial">
<BR>
<B>In-Gruppe Manuelle Wahl -</B> Diese Funktion ermöglicht es Ihnen, die Möglichkeit für Agenten auf manuelle Einwahl ausgehende Anrufe, die als in-group ruft zugeordnet zu einem bestimmten in-group angemeldet sind platzieren können. Die MANUAL_DIAL Option ermöglicht die Platzierung von Telefonaten aus durch eine In-Gruppe an den Agenten, der den Anruf. Die NO_DIAL Option ermöglicht es dem Agenten zu Zeit auf einen Anruf, der nicht existiert einzuloggen, als ob es ein echtes Gespräch waren, wird dies oft für die Anmeldung per E-Mail oder per Fax Zeit verwendet. Die beiden Option erlaubt sowohl Call-keine-Call-in-Gruppenwahl. Die Standardeinstellung ist deaktiviert.

<BR>
<A NAME="campaigns-in_group_dial_select">
<BR>
<B>In-Gruppe Manuelle Wahl Select -</B> Diese Option ist nur dann aktiv, wenn die oben genannten In-Gruppe Handbuch Dial-Funktion nicht deaktiviert ist. Diese Option schränkt die wählbare In-Gruppen, dass der Agent in-group Manuelle Wahl ruft durch platzieren. CAMPAIGN_SELECTED zeigt nur die in-Gruppen, die die Kampagne als zulässig in-Gruppen gesetzt. ALL_USER_GROUP zeigt alle in-Gruppen, die sichtbar an die Mitglieder der Benutzergruppe sind, dass der Agent gehört.

<BR>
<A NAME="campaigns-agent_clipboard_copy">
<BR>
<B>Agent Screen Zwischenablage kopieren - </B> Diese Funktion ist derzeit nur in die Lage für den Internet Explorer. Diese Funktion ermöglicht es Ihnen, wählen Sie einen Bereich, der kopiert werden an den Computer Zwischenablage des Maklers Computer auf einen Anruf wird an einen Agenten. Häufig verwendet für diese sind, damit für die einfache Einfügen von Kontonummern oder Telefonnummern in Legacy-Anwendungen auf dem Client-Agent-Computer.

<BR>
<A NAME="campaigns-three_way_call_cid">
<BR>
<B>3-Way Call Outbound CallerID -</B> Diese wird definiert, was als die ausgehende callerID Zahl von 3-Wege-Anrufe vom Agenten versandt, verwendet KAMPAGNE die benutzerdefinierte Kampagne callerID, verwendet der Kunde die Nummer des Kunden, die aktiv ist auf die Mittel-Bildschirm und verwendet die AGENT_PHONE callerID für das Telefon dass der Agent angemeldet ist. AGENT_CHOOSE kann der Agent zu wählen, welche callerID für 3-Wege-Anrufe aus einer Auswahlliste verwenden. CUSTOM_CID wird das Custom CID, die in der security_phrase Feld der Listentabelle für die Führung definiert ist verwenden.

<BR>
<A NAME="campaigns-three_way_dial_prefix">
<BR>
<B>3-Wege-Call-Vorwahl - </B> Diese legt fest, was als Vorwahl für 3-Wege-Anrufe, standardmäßig ist leer, so dass die Kampagne Vorwahl verwendet wird, so können Sie passthru Klingelton hören ist 88.

<BR>
<A NAME="campaigns-customer_3way_hangup_logging">
<BR>
<B>Kunde 3-Wege-Hangup Logging -</B> Wenn diese Option aktiviert die user_call_log wird protokolliert, wenn ein Kunde Hangup-up, wenn sie während einer 3-Wege-Anruf zu hängen. Außerdem kann dieser für den Kunden erlauben 3-Wege-Hangup Aktion, wenn man unten defineed wird. Standardeinstellung lautet AKTIVIERT.

<BR>
<A NAME="campaigns-customer_3way_hangup_seconds">
<BR>
<B>Kunde 3-Wege-Hangup Sekunden -</B> Wenn der Kunde 3-Wege-Protokollierung aktiviert ist, können Sie mit dieser Option die Anzahl der Sekunden definieren, nachdem der Kunde hangup erkannt wird, bevor es tatsächlich angemeldet ist und der Kunde optional 3-Wege-Hangup-Aktion ausgeführt wird. Die Standardeinstellung ist 5 Sekunden.

<BR>
<A NAME="campaigns-customer_3way_hangup_action">
<BR>
<B>Kunde 3-Wege-Hangup Aktion -</B> Wenn der Kunde 3-Wege-Protokollierung aktiviert ist, können Sie mit dieser Option haben die Agenten-Bildschirm automatisch auflegen auf den Anruf und rufen Sie die DISPO Bildschirm, wenn diese Option, um DISPO eingestellt ist. Der Standardwert ist NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-qc_enabled">
	<BR>
	<B>QC aktiv - </B> Wenn Sie diesen Bereich auf Y-Agent ermöglicht die Qualitätskontrolle Funktionen zur Verfügung stehen. Der Standardwert ist N.

	<BR>
	<A NAME="campaigns-qc_statuses">
	<BR>
	<B>QC-Status - </B> Dieser Bereich ist, in dem Sie auswählen, welche Zustände führt sollte weg von der QC-System. Aktivieren Sie das Kontrollkästchen neben den Status, die Sie wollen, QC, zu überprüfen. 

	<BR>
	<A NAME="campaigns-qc_shift_id">
	<BR>
	<B>QC-Shift - </B> Das ist die Verlagerung Zeitrahmen für die Pull-QC-Datensätze für eine Kampagne. Die Tage der Woche sind nicht für diese Funktionen.

	<BR>
	<A NAME="campaigns-qc_get_record_launch">
	<BR>
	<B>QC Get Record Launch-</B> Auf diese Weise können Sie eine der folgenden Aktionen ausgelöst werden dürfte, auf einem QC Agenten erhalten einen neuen Rekord.

	<BR>
	<A NAME="campaigns-qc_show_recording">
	<BR>
	<B>QC Show-Aufnahme - </B> Auf diese Weise können für eine Aufnahme, die im Zusammenhang mit der QC-Datensatz werden in der QC-Agent-Bildschirm.

	<BR>
	<A NAME="campaigns-qc_web_form_address">
	<BR>
	<B>QC WebForm Adresse - </B> Dies ist die Website-Adresse, dass ein QC Agenten können sich an, wenn Sie auf den Link WebForm in der QC-Bildschirm.

	<BR>
	<A NAME="campaigns-qc_script">
	<BR>
	<B>QC-Script - </B> Das ist das Skript, das verwendet werden kann, indem QC Agenten in den SCRIPT Registerkarte in der QC-Bildschirm.
	<?php
	}
?>

<BR>
<A NAME="campaigns-vtiger_search_category">
<BR>
<B>Vtiger Search Category -</B> Wenn Vtiger Integration in den Systemeinstellungen aktiviert ist, dann wird diese Einstellung definieren, wo die vtiger_search.php Seite wird nach der Telefonnummer zu suchen, die eingegeben wurde. Es gibt 4 Optionen, die in diesem Bereich verwendet werden können: BLEI Diese Option wird durch das Vtiger führt nur wird KONTO-Diese Option durch die Vtiger-Konten und alle Kontakte und Sub-Kontakte für die Telefonnummer, hersteller Diese Option suchen, suchen wird nur durch die Vtiger-Anbieter zu suchen, ACCTID-Diese Option funktioniert nur für Konten, und es wird die vendor_lead_code Feldliste nehmen und versuchen, für die Vtiger Account ID suchen. Wenn legen ist, werden alle anderen aufgelistet, die Sie ausgewählt haben Methoden versuchen. Mehrere Optionen können für jeden Such verwendet werden, aber auf diese großen Datenbanken wird nicht empfohlen. Standard ist LEAD. UNIFIED_CONTACT-Diese Option wird die Beta Vtiger 5.1.0 Funktion verwenden, um nach der Telefonnummer suchen und bringen eine Suchseite in Vtiger.

<BR>
<A NAME="campaigns-vtiger_search_dead">
<BR>
<B>Vtiger Suche Dead-Konten -</B> Wenn Vtiger Integration ist in den Systemeinstellungen aktivieren, dann wird diese Einstellung festlegen, ob Gelöschte Konten durchsucht werden, wenn der Agent WEB FORM Mausklicks genügen, um in der vtiger-System sucht. BEHINDERTE-deleted führt nicht durchsucht werden, ASK-führt wird gelöscht hast und der vtiger-Suche Web-Seite wird der Agent fragen, ob sie zu machen, der vtiger-Konto aktiv wollen, RESURRECT-automatisch machen das gelöschte Konto wieder aktiv und würde die Inanspruchnahme Agenten auf das Konto Bildschirm unverzüglich nach Klick auf Netz-Form. Option ist standardmäßig deaktiviert.

<BR>
<A NAME="campaigns-vtiger_create_call_record">
<BR>
<B>Vtiger Erstellen Call Record - </B> Wenn vtiger Integration aktiviert ist in den System-Einstellungen, dann wird diese Einstellung festlegen, ob eine neue vtiger Aktivität wird für den Anruf, wenn der Agent geht an die vtiger_search Seite. Der Standardwert ist Y. Die DISPO Option wird eine Anrufe aufzeichnen der vtiger-Konto erstellen, ohne dass der Agent zu müssen, um der vtiger-Suchseite gehen über den Netz-Form.

<BR>
<A NAME="campaigns-vtiger_create_lead_record">
<BR>
<B>Vtiger Erstellen Lead Record - </B> Wenn vtiger Integration aktiviert ist in den System-Einstellungen und vtiger Suche Kategorie umfasst BLEI, dann wird diese Einstellung festlegen, ob eine neue vtiger führen wird erstellt, wenn der Agent geht an die vtiger_search Seite und wird nicht festgestellt, dass die Call-Telefonnummer. Der Standardwert ist Y.

<BR>
<A NAME="campaigns-vtiger_screen_login">
<BR>
<B>Vtiger Screen Login -</B> Wenn Vtiger Integration in den Systemeinstellungen aktiviert ist, dann wird diese Einstellung festlegen, ob der Benutzer automatisch in das Vtiger-Schnittstelle protokolliert, wenn sie zu dem Agenten-Bildschirm anmelden. Der Standardwert ist Y. Die Option NEW_WINDOW wird ein neues Fenster mit dem Login zu dem Agenten-Bildschirm zu öffnen.

<BR>
<A NAME="campaigns-vtiger_status_call">
<BR>
<B>Vtiger Status Call -</B> Wenn Vtiger Integration in den Systemeinstellungen aktiviert ist, dann wird diese Einstellung festlegen, ob der Status der Vtiger Konto mit dem Status des Anrufs aktualisiert, nachdem es richtig verteilt wurde. Standardwert ist N.

<BR>
<A NAME="campaigns-queuemetrics_callstatus">
<BR>
<B>QM CallStatus Override -</B> Wenn QueueMetrics Integration in den Systemeinstellungen aktiviert ist, wird diese Einstellung erlauben das vorrangige der System-Einstellungen die Einstellung für CallStatus queue_log Einträge. Der Standardwert ist DISABLED, welche den System-Einstellung verwenden.

<BR>
<A NAME="campaigns-queuemetrics_phone_environment">
<BR>
<B>QM-Telefon Umwelt -</B> Wenn QueueMetrics Integration in den Systemeinstellungen aktiviert ist, wird diese Einstellung erlauben das Einfügen von Daten dieser Wert im Feld der data4 queue_log für Agententätigkeit Datensätze. Standard ist leer für Behinderte.

<BR>
<A NAME="campaigns-extension_appended_cidname">
<BR>
<B>Erweiterung anhängen CID -</B> Wenn diese Option aktiviert, werden die Anrufe von dieser Kampagne platziert haben einen Raum und die Durchwahl des Agenten an das Ende des CallerID Name für den Anruf, bevor es an den Agenten gesendet wird. Standard ist für behinderte N.

<BR>
<A NAME="campaigns-pllb_grouping">
<BR>
<B>PLLB Gruppierung -</B> Telefon Einloggen Load Balancing Gruppierung, nur zulässig, wenn es mehrere Agent-Server sind und Telefon-Aliase sind auf dem System vorhanden. Bei der Einstellung ONE_SERVER_ONLY wird es erzwingen, dass alle Agenten für diese Kampagne, um auf den gleichen Server einloggen. Wenn in Cascading es in Agenten auf dem gleichen Server werden protokolliert Gruppe bis PLLB Gruppierung Anzahl der Agenten-Limit erreicht gesetzt sind, dann ist der nächste Agent wird an den nächsten Server mit der geringsten Anzahl von Agenten anmelden. Wenn der Maßstab Telefon Aliases Verhalten jedes Agenten Finden der Server mit der geringsten Anzahl von Nicht-Remote-Agenten hinein protokolliert Deaktiviert, um genutzt werden. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-pllb_grouping_limit">
<BR>
<B>PLLB Limit-Gruppierung -</B> Telefon Einloggen Load Balancing Verbund Limit. Wenn PLLB Gruppierung zu CASCADING eingestellt ist, dann wird diese Einstellung bestimmt die Anzahl der Agenten akzeptabel in jedem Server für diese Kampagne. Standard ist 50.

<BR>
<A NAME="campaigns-crm_popup_login">
<BR>
<B>CRM Popup Login -</B> Wenn auf Y gesetzt, so wird das CRM-Popup-Adresse verwendet, um ein neues Fenster auf Anbieters anmelden, um diese Kampagne zu öffnen. Der Standardwert ist N.

<BR>
<A NAME="campaigns-crm_login_address">
<BR>
<B>CRM Popup-Adresse -</B> Die Internet-Adresse eines CRM-Login-Seite, kann es Variablen, wie das Web-Formular Adresse bevölkert haben, mit der VAR in der Front und mit - A - user_custom_one - B - Variablen definieren.

<BR>
<A NAME="campaigns-start_call_url">
<BR>
<B>Start Call URL -</B> Diese Web-URL-Adresse wird nicht von den Agenten zu sehen, aber es ist, wann immer ein Anruf an einen Agenten gesendet werden, wenn es gefüllt wird. Verwendet die gleichen Variablen wie die Web-Formularfelder und Skripte. Diese URL kann nicht ein relativer Pfad. Die Start-URL funktioniert nicht für die manuelle Einwahl fordert. Der Standardwert ist leer.

<BR>
<A NAME="campaigns-dispo_call_url">
<BR>
<B>Dispo Call URL -</B> Diese Web-URL-Adresse wird nicht von den Agenten zu sehen, aber es ist, wann immer ein Anruf von einem Agenten dispositioned, wenn sie gefüllt wird. Verwendet die gleichen Variablen wie die Web-Formularfelder und Skripte. dispo und talk_time sind die Variablen, die Sie verwenden können, um den Agenten abrufen definierten Disposition für den Anruf und die tatsächliche Sprechzeit in Sekunden des Anrufs. Diese URL kann nicht ein relativer Pfad. Der Standardwert ist leer.

<BR>
<A NAME="campaigns-na_call_url">
<BR>
<B>Kein Anruf-Agent URL -</B> Diese Web-URL-Adresse wird nicht durch den Agenten zu sehen, aber wenn es gefüllt wird aufgerufen, wenn ein Ruf, der nicht von einem Agenten gehandhabt wird nach oben oder aufgehängt übertragen. Verwendet die gleichen Variablen wie die Web-Formular und Indexe verwenden. dispo kann verwendet werden, um die vom System vorgegebene Disposition für den Anruf abzurufen. Diese URL kann kein relativer Pfad sein. Standardeinstellung ist leer.

<BR>
<A NAME="campaigns-agent_allow_group_alias">
<BR>
<B>Alias-Gruppe erlaubt - </B> Wenn Sie möchten, dass Ihre Agenten zu verwenden Gruppe Aliase dann müssen Sie diese Gruppe Y. Aliases werden mehr in der Admin-Bereich, sie zuzulassen, um verschiedene callerIDs Handbuch für ausgehende Anrufe dass sie Platz. Der Standardwert ist N.

<BR>
<A NAME="campaigns-default_group_alias">
<BR>
<B>Standard-Fraktion Alias - </B> Wenn Sie erlaubt Gruppe Aliases, dann ist dies die Gruppe Alias, die zuerst ausgewählten standardmäßig aktiviert, wenn der Agent sich für eine Gruppe Alias für einen ausgehenden Anruf Handbuch. Der Standardwert ist Keine oder leer.

<BR>
<A NAME="campaigns-view_calls_in_queue">
<BR>
<B>Agent Profil Anrufe in der Warteschlange -</B> Bei anderen aber none gesetzt ist, werden Vertreter der Lage sein, Informationen über Anrufe, die in der Warteschlange in ihrer Bevollmächtigten Bildschirm zu sehen sind. Wenn auf einen Zahlenwert setzen, die Anrufe angezeigt werden, um die Anzahl begrenzt werden ausgewählt. Der Standardwert ist KEINE.

<BR>
<A NAME="campaigns-view_calls_in_queue_launch">
<BR>
<B>Profil Anrufe in der Warteschlange Launch -</B> Diese Einstellung, wenn auf AUTO eingestellt wird die Anrufe in der Warteschlange Frame angezeigt werden bei der Anmeldung durch den Agenten in der Agent dem Bildschirm haben. Standard ist -Manuell-.

<BR>
<A NAME="campaigns-grab_calls_in_queue">
<BR>
<B>Agent Grab Anrufe in der Warteschlange -</B> Diese Option, wenn auf Y wird es den Agenten, um den Anruf, dass sie wählen wollen, aus der Anrufe in der Warteschlange anzeigen, indem Sie auf es, während der Pause. Bevollmächtigte werden nur in der Lage, eingehende Anrufe oder übertragen werden ersucht, nicht ausgehende Anrufe zu greifen. Der Standardwert ist N.

<BR>
<A NAME="campaigns-call_requeue_button">
<BR>
<B>Call Agent Re-Queue Button -</B> Diese Option, wenn auf Y fügt ein Re-Queue Customer-Taste, um den Agenten Bildschirm, so dass der Agent, um den Anruf zu einem AGENTDIRECT Warteschlange, die für den Agenten nur reserviert ist, zu senden. Der Standardwert ist N.

<BR>
<A NAME="campaigns-pause_after_each_call">
<BR>
<B>Agent Pause nach jedem Anruf -</B> Diese Option, wenn auf Y wird der Agent Pause nach jedem Anruf automatisch. Der Standardwert ist N.

<BR>
<A NAME="campaigns-pause_after_next_call">
<BR>
<B>Agenten Pause After Next Link Anruf -</B> Diese Option wird angezeigt, wenn aktiviert einen Link auf der Agenten-Bildschirm, damit der Agent auf Pause automatisch gehen, nachdem sie ihre nächsten Anruf hängen wird. Standardmäßig deaktiviert.

<BR>
<A NAME="campaigns-blind_monitor_warning">
<BR>
<B>Blinden-Monitor Warnung -</B> Diese Option aktiviert wird, wenn wir das Mittel in verschiedene optionale Möglichkeiten wissen, ob sie blind zu sein von jemandem überwacht werden. DISABLED bedeutet diese Funktion nicht aktiv ist, wird nur eingeblendet ALERT eine Warnung auf dem Bildschirm Agent, NOTICE wird eine Notiz, die sich bleibt auf dem Agent-Bildschirm, solange Theyâ Re überwachten veröffentlichen, wird der Ton spielen Sie den Dateinamen unten definiert, wenn ein Agent beginnt, überwacht werden und die anderen Optionen sind combibnations der oben genannten Optionen. Der Standardwert ist DISABLED.

<BR>
<A NAME="campaigns-blind_monitor_message">
<BR>
<B>Blinden-Monitor und Datenschutz -</B> Das ist die Botschaft, die auf dem Bildschirm zeigen Agent wird, während sie überwacht wird, wenn die Bekanntmachung Option ausgewählt ist. Die Standardeinstellung ist -Someone is blind monitoring your session-.

<BR>
<A NAME="campaigns-blind_monitor_filename">
<BR>
<B>Blinden-Monitor Dateiname -</B> Dies ist der Audio-Datei, in der Agenten-Session zu Beginn der Überwachung jemand blinde ihnen zu spielen. Diese Aufforderung wird für jeden in der Sitzung mit dem Kunden, wenn eine vorhanden ist, gespielt werden. Standard ist leer.

<BR>
<A NAME="campaigns-max_inbound_calls">
<BR>
<B>Max eingehende Anrufe -</B> Wenn diese Einstellung auf eine Zahl größer als 0 gesetzt wird, dann wird es die maximale Anzahl der eingehenden Anrufe, die ein Agent für alle eingehenden Gruppen an einem Tag zu bewältigen. Wenn der Agent die maximale Anzahl der eingehenden Anrufe erreicht, dann werden sie nicht in der Lage, eingehende Gruppen auswählen, um Anrufe von bis zum nächsten Tag zu nehmen. Diese Einstellung kann durch die Benutzer-Einstellung mit dem gleichen Namen werden überschrieben. Standard ist 0 für Behinderte.






<BR><BR><BR><BR>

<B><FONT SIZE=3>Tabelle sind</FONT></B><BR><BR>
<A NAME="lists-list_id">
<BR>
<B>Liste Identifikation -</B> Dieses ist der numerische Name der Liste,es ist nicht editable nach Ausgangsunterordnung, muß nur Zahlenenthalten und muß zwischen 2 und 8 Buchstaben lang sein. Must be a number greater than 100.

<BR>
<A NAME="lists-list_name">
<BR>
<B>Liste Name -</B> This is the description of the list, it must be between 2 and 20 characters in length.

<BR>
<A NAME="lists-list_description">
<BR>
<B>Liste Beschreibung -</B> dieses ist das Protokoll auffangen fürdie Liste, es ist wahlweise freigestellt.

<BR>
<A NAME="lists-list_changedate">
<BR>
<B>Liste Änderung Datum -</B> dieses ist das letzte Mal, daß dieEinstellungen für diese Liste in jeder Hinsicht geändert wurden.

<BR>
<A NAME="lists-list_lastcalldate">
<BR>
<B>Liste Letzt-Anruf-Datum -</B> dieses ist das letzte Mal, das wurdegewählt von dieser Liste führen.

<BR>
<A NAME="lists-campaign_id">
<BR>
<B>Kampagne -</B> This is the campaign that this list belongs to. A list can only be dialed on a single campaign at one time.

<BR>
<A NAME="lists-active">
<BR>
<B>Aktiv -</B> Dieses definiert, ob die Liste an oder nicht gewähltwerden soll.

<BR>
<A NAME="lists-reset_list">
<BR>
<B>Stellen Sie Führen-Benennen-Status für diese Liste -</B> zurückstellt dieses alle Leitungen in dieser Liste zu N für das benannte\"not da letztes Zurückstellen \" zurück und bedeutet, daß jedemögliche Leitung jetzt benannt werden kann, wenn es der rechte Statusist, wie auf dem Kampagne Schirm definiert.

<BR>
<A NAME="lists-reset_time">
<BR>
<B>Reset Times -</B> In diesem Feld können Sie zu setzen in Zeiten, durch einen Bindestrich getrennt, dass diese Liste automatisch zurückgesetzt werden durch das System. Die Zeiten im 24-Stunden-Format ohne Interpunktion werden, wäre zum Beispiel 0800-1700 die Liste 8 zurückgesetzt und 5pm jeden Tag. Der Standardwert ist leer.

<BR>
<A NAME="lists-expiration_date">
<BR>
<B>Fälligkeitsdatum -</B> Diese Option ermöglicht es Ihnen, das Datum, nach dem führt in dieser Liste nicht erlaubt sein wird, werden automatisch gewählt oder durch das System-Handbuch-list-gewählt werden gesetzt. Standard ist 2099-12-31.

<BR>
<A NAME="lists-audit_comments">
<BR>
<B>Audit Kommentare -</B> Diese Option ermöglicht es, Kommentare zu einer Audit-Tabelle verschoben werden. Nicht mehr bearbeitet werden, aber sichtbar zusammen mit dem Datum-Zeit-creator jeder Kommentar. Der Standardwert ist N. Dies ist ein Teil der Qualitätskontrolle Add-On.

<BR>
<A NAME="lists-agent_script_override">
<BR>
<B>Agent Script Override -</B> Ist dieses Feld gesetzt ist, wird dies das Skript, dass der Agent sieht auf dem Bildschirm statt der Kampagne führen, wenn das Skript aus dieser Liste ist. Der Standardwert ist nicht gesetzt.

<BR>
<A NAME="lists-campaign_cid_override">
<BR>
<B>Kampagne CID Override -</B> Ist dieses Feld gesetzt ist, wird diese die Kampagne CallerID, dass für Anrufe, die gestellt werden außer Kraft gesetzt ist, führt in dieser Liste. Der Standardwert ist nicht gesetzt.

<BR>
<A NAME="lists-am_message_exten_override">
<BR>
<B>Anrufbeantworter Nachricht Override -</B> Ist dieses Feld gesetzt ist, wird diese Vorrang vor den Anrufbeantworter Message in der Kampagne für die Kunden in dieser Liste gesetzt. Der Standardwert ist nicht gesetzt. 
<BR>
<A NAME="lists-drop_inbound_group_override">
<BR>
<B>Drop Inbound-Fraktion Override -</B> Ist dieses Feld gesetzt ist, in dieser Gruppe wird für ausgehende Anrufe, in dieser Liste verwendet werden, die Tropfen aus dem Outbound-Kampagne statt der Drop-in in der Kampagne Detailbild gesetzt. Der Standardwert ist nicht gesetzt.

<BR>
<A NAME="lists-web_form_address">
<BR>
<B>Netz-Form Override -</B> Dies ist die Adresse, die kunden Sie auf den WEB FORM-Taste in der Agenten-Bildschirm wird Sie für Anrufe, die auf dieser Liste zu nehmen. Wenn Sie benutzerdefinierte Felder in einem Web-Formular-Adresse verwenden möchten, um als Teil der URL hinzufügen und CF_uses_custom_fields = Y müssen Sie.

<BR>
<A NAME="lists-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf-Nr Override -</B> Diese fünf Felder ermöglichen Sie den Transfer Conference Anzahl Presets zu überschreiben, wenn das Blei aus dieser Liste. Der Standardwert ist leer.

<BR>
<A NAME="lists-inventory_report">
<BR>
<B>Inventory Report -</B> Wenn das Inventory Report auf Ihrem System aktiviert ist, wird diese Option entscheiden, ob diese Liste in den Bericht aufgenommen wird oder nicht. Die Standardeinstellung ist J für Ja.

<BR>
<A NAME="lists-time_zone_setting">
<BR>
<B>Zeitzonen-Einstellung -</B> Diese Option ermöglicht es Ihnen, die Methode der Aufrechterhaltung der aktuellen Zeitzone Nachschlag für die Leitungen innerhalb dieser Liste gesetzt. Dieser Prozess wird nur in der Nacht geschehen ist werden die Änderungen nicht in der unmittelbaren. COUNTRY_AND_AREA_CODE ist die Standardeinstellung und wird die Landes-und Ortsvorwahl der Telefonnummer verwenden, um die Zeitzone des Bleis bestimmen. POSTAL_CODE verwenden die Postleitzahl falls vorhanden die Zeitzone des Bleis zu bestimmen. NANPA_PREFIX funktioniert nur in den USA und wird die Vorwahl und die Vorwahl der Telefonnummer verwenden, um die Zeitzone des Bleis bestimmen, aber dies ist nicht standardmäßig im System aktiviert, so dass Sie sicher, Sie haben die NANPA Präfix Daten verladen Ihr System, bevor Sie diese Option auswählen. OWNER_TIME_ZONE_CODE wird die Standard-Zeitzone abbraviation in das Feld Besitzer des Blei beladen benutzen, um die Zeitzone zu bestimmen, in den USA Beispiele sind AST, EST, CST, MST, PST, AKST, HST. Dieses Leistungsmerkmal muss von Ihrem Systemadministrator aktiviert werden, um in Kraft treten.


<BR>
<A NAME="internal_list-dnc">
<BR>
<B>Interne DNC Liste -</B> This Do Not Call list contains every lead that has been set to a status of DNC in the system. Through the LISTS - FÜGEN SIE ZAHL DNC HINZU page you are able to manually add numbers to this list so that they will not be called by campaigns that use the internal DNC list. Es besteht auch die Option, mit der Sie führt zu der Kampagne DNC-spezifische Listen für die Kampagnen, die sie. Wenn Sie die aktive DNC-Option auf -AREACODE dann können Sie auch die Vorwahl Wildcard-Einträge wie 201XXXXXXX, dass alle Anrufe auf die 201 Ortsvorwahl, wenn aktiviert-Block.



<BR><BR><BR><BR>

<B><FONT SIZE=3>INBOUND_GROUPSTABELLE</FONT></B><BR><BR>
<A NAME="inbound_groups-group_id">
<BR>
<B>Gruppe Identifikation -</B> Dieses ist der kurze Name der inboundGruppe, es ist nicht editable nach Ausgangsunterordnung, darf keineRäume enthalten und muß zwischen 2 und 20 Buchstaben lang sein.

<BR>
<A NAME="inbound_groups-group_name">
<BR>
<B>Gruppe Name -</B> Dieses ist die Beschreibung der Gruppe, muß eszwischen 2 und 30 Buchstaben lang sein. Kann nicht Schläge, plussesoder Räume einschließen .

<BR>
<A NAME="inbound_groups-group_color">
<BR>
<B>Gruppe Farbe -</B> Dies ist die Farbe, in der Agenten-Client-Anwendung angezeigt wird, wenn ein Anruf auf diese Gruppe. Es muss zwischen 2 und 7 Zeichen lang sein. Wenn dies ein Hex-Farbdefinition müssen Sie daran denken, ein # am Anfang der Zeichenfolge oder dem Agenten-Bildschirm setzen funktioniert nicht richtig,.

<BR>
<A NAME="inbound_groups-active">
<BR>
<B>Aktive -</B> Diese bestimmt, ob diese eingehende Gruppe ist, Anrufe anzunehmen. Wenn dies so eingestellt ist, dann inaktiv die After Hours Aktion wird auf alle Anrufe, die in sie verwendet werden.

<BR>
<A NAME="inbound_groups-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Inbound-Gruppe, ermöglicht diese Admin Betrachten dieser in-group von Benutzergruppe beschränkt. Standard ist - ALLE -, die jeder Benutzer admin, dies in-group sehen können.

<BR>
<A NAME="inbound_groups-group_calldate">
<BR>
<B>In-Group Calldate -</B> Dies ist das letzte Datum und die Zeit, dass ein Anruf auf diese Gruppe gerichtet war Inbound.

<BR>
<A NAME="inbound_groups-web_form_address">
<BR>
<B>Netz-Form -</B> Dies ist die Adresse, die kunden Sie auf den WEB FORM-Taste in der Agenten-Bildschirm wird Sie für Anrufe, die auf diese Gruppe zu nehmen. Wenn Sie benutzerdefinierte Felder in einem Web-Formular-Adresse verwenden möchten, um als Teil der URL hinzufügen und CF_uses_custom_fields = Y müssen Sie.

<BR>
<A NAME="inbound_groups-next_agent_call">
<BR>
<B>Folgender Vertreter-Anruf -</B> Dieses stellt fest, welches Mittel denfolgenden Anruf empfängt, der vorhanden ist:
 <BR> &nbsp; - random: Bestellungen durch den Zufalls Update-Wert in der Tabelle live_agents
 <BR> &nbsp; - oldest_call_start: Aufträge bis zum dem letzten Mal ein Mittel wurden einem Anrufgeschickt. Resultate in den Mitteln, die insgesamt ungefähr gleicheZahl von Anrufen empfangen.
 <BR> &nbsp; - oldest_call_finish: Aufträge bis zum dem letzten Mal ein Mittel beendeten einen Anruf.Das AKA Mittel, das am längsten wartet, empfängt ersten Anruf.
 <BR> &nbsp; - oldest_inbound_call_start: Aufträge von der letzten Zeit wurde ein Agent einen eingehenden Anruf gesendet. Ergebnisse in Agenten erhalten etwa die gleiche Anzahl der Anrufe insgesamt.
 <BR> &nbsp; - oldest_inbound_call_finish: Bestellungen durch das letzte Mal ein Agent beendet einen eingehenden Anruf. AKA-Agent wartet am längsten erhält ersten nennen.
 <BR> &nbsp; - overall_user_level: Bestellungen des user_level des Mittels wie in der Benutzer-Tabelle definiert eine höhere user_level mehr Anrufe empfangen.
 <BR> &nbsp; - inbound_group_rank: Aufträge durch den Rank gegeben zum Mittel fürdie spezifische inbound Gruppe. Stark zu am niedrigsten.
 <BR> &nbsp; - campaign_rank: Aufträge durch den Rank gegeben zum Mittel für dieKampagne. Stark zu am niedrigsten.
 <BR> &nbsp; - ingroup_grade_random: ergibt eine höhere Wahrscheinlichkeit, einen Aufruf an die höherwertige Agenten durch in-group.
 <BR> &nbsp; - campaign_grade_random: ergibt eine höhere Wahrscheinlichkeit, einen Aufruf an die höherwertige Agenten von Kampagne.
 <BR> &nbsp; - fewest_calls: Aufträge durch die Zahl den Anrufen empfangen durch einMittel für diese spezifische inbound Gruppe. Wenige Anrufe zuerst.
 <BR> &nbsp; - fewest_calls_campaign: orders by the number of calls received by an agent for the campaign. Least calls first.
 <BR> &nbsp; - longest_wait_time: Bestellungen durch die Zeit, die Agenten aktiv gewartet auf einen Anruf.
 <BR> &nbsp; - ring_all: rings all available agents until one picks up the phone.
 <BR> HINWEISE: Für ring_all, die Agenten, die mit Handys, die über Hook-Agent aktiviert haben, werden ihre Telefone klingeln und der erste, der auf die Forderung und die Informationen auf dem Bildschirm Agenten erhalten haben. Seit ring_all ignoriert Agent Wartezeit und das Ranking und wird alle Mittel, die für die Warteschlange ist anzurufen, empfehlen wir nicht mit dieser Methode für große Warteschlangen. Bei der Verwendung von ring_all werden Agenten mit Telefonen, die das Aufgelegt-Agent deaktiviert angemeldet haben müssen, um die Anrufe in Warteschlange Panel verwenden und klicken Sie auf den Link zum Gespräch annehmen Anrufe in der Warteschlange zu nehmen. Die Zeit, die Agenten-Telefon für ring_all klingelt an die On-Hakenring Zeiteinstellung oder der kürzesten Zeit Ring der Telefone, die aufgerufen wird eingestellt. Wir empfehlen nicht die Verwendung ring_all auf hohem Anrufvolumen Warteschlangen oder Warteschlangen mit vielen Agenten. Die ring_all Methode soll mit nur wenigen Mitteln und auf niedrigem Gesprächsaufkommen in-Gruppen verwendet werden.
<BR>

<BR>
<A NAME="inbound_groups-on_hook_ring_time">
<BR>
<B>On-Hakenring Zeit -</B> Diese Option wird nur für Agenten, die in Handys, die mit den Agenten-On-Hook-Funktion aktiviert haben, werden protokolliert verwendet. Dies ist die Anzahl der Sekunden, dass jeder Anrufversuch an den Agent wird für das System wird bis 1 Sekunde warten und klingeln die Telefone verfügbaren Agenten erneut ertönen. Dieses Feld kann überschrieben werden, wenn die Agenten-Telefone zu einem unteren Ring der Zeit, die notwendig sein, um Anrufe von Telefonen gesendet werden ein Voicemail verhindern kann eingestellt werden. Die Standardeinstellung ist 15.

<BR>
<A NAME="inbound_groups-on_hook_cid">
<BR>
<B>On-Haken CID -</B> Diese Option wird nur für Agenten, die in Handys, die mit den Agenten-On-Hook-Funktion aktiviert haben, werden protokolliert verwendet. Dies ist die Anrufer-ID zeigen sich nicht auf ihren Agenten Handys, wenn die Anrufe klingeln. GENERIC ist eine generische RINGAGENT00000000001 Art der Benachrichtigung. Eigengruppe wird zeigen, nur die in-group der Anruf kam. CUSTOMER_PHONE zeigt nur den Kunden Telefonnummer. CUSTOMER_PHONE_RINGMITTELwird RINGAGENT_3125551212 mit dem RINGMITTELals Teil des CID mit dem Kunden Rufnummer zu erhalten. CUSTOMER_PHONE_INGROUP zeigt die ersten 10 Zeichen des In-Group durch den Kunden Telefonnummer gefolgt. Default is GENERIC.

<BR>
<A NAME="inbound_groups-queue_priority">
<BR>
<B>Queue Priority -</B> This setting is used to define the order in which the calls from this inbound group should be answered in relation to calls from other inbound groups.

<A NAME="inbound_groups-fronter_display">
<BR>
<B>Fronter Anzeige -</B> Dieses Feld bestimmt, ob die eingehenden Mittel würde die Fronter Namen haben - wenn es einen gibt - im Feld Status angezeigt, wenn der Anruf an den Agenten kommt.

<BR>
<A NAME="inbound_groups-ingroup_script">
<BR>
<B>Kampagne Index -</B> Dieses Menü erlaubt Ihnen, den Index zu wählen,der auf dem Mittelschirm für diese Kampagne erscheint. Wählen SieKEINE vor, keinen Index für diese Kampagne zu zeigen.

<BR>
<A NAME="inbound_groups-ignore_list_script_override">
<BR>
<B>Ignorieren-Liste Script Override-</B> Diese Option ermöglicht es Ihnen, die Liste ID Script Override-Option für Anrufe kommen in dieser In-Group zu ignorieren. Wenn Sie dieses auf Y ignoriert dann sämtliche Listen-ID Script-Einstellungen. Standard ist N.

<BR>
<A NAME="inbound_groups-get_call_launch">
<BR>
<B>Erhalten Sie Anruf-Produkteinführung -</B> Dieses Menü erlaubt Ihnenzu wählen, ob Sie Automobil-ausstoßen die Netz-Form Seite in einemunterschiedlichen Fenster, Auto-switch zum INDEX-Vorsprung oder tunnichts wünschen, wenn ein Anruf zum Mittel für diese Kampagnegeschickt wird. Wenn benutzerdefinierte Liste Felder auf Ihrem System aktiviert sind, bilden die Registerkarte Formular bei der Verbindung eines Anrufs öffnen, um einen Agenten.

<BR>
<A NAME="inbound_groups-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Diese vier Felder können für Sie zwei Sätze von Über Konferenz-und DTMF-Presets haben. Wenn der Anruf oder eine Kampagne eingelegt ist, wird der Agent Bildschirm zwei Tasten auf dem Transfer-Konferenz Rahmen und zeigen automatischen Auffüllen der Nummer-zu-Zifferblatt und die Send-dtmf Felder, wenn gedrückt. Wenn Sie zulassen möchten Beratenden Transfers, ein Frontmann, um eine engere, haben die Mittel zu verwenden der Beratenden Kontrollkästchen, die nicht für Dritte Mittel Bildschirm Beratungsgespräche nicht funktioniert. Für diejenigen, die einfach nur der Agent auf die DFÜ mit Kunden-Taste. Dann kann der Agent nur LASSEN-3 POL-CALL und gehen Sie zum nächsten Anruf. Wenn Sie Blind Transfers von Kunden zu einem AGI-Script für Protokollierung oder ein IVR erlauben wollen, dann legen Sie AXFER der Zahl-to-Dial-Feld. Sie können auch eine benutzerdefinierte Erweiterung nach dem AXFER, zum Beispiel, wenn Sie einen Anruf zu einem speziellen IVR Sie Verlängerung 83900 eingestellt haben Sie AXFER83900 in der Feldnummer-zu-Wahl setzen wollen.

<BR>
<A NAME="inbound_groups-timer_action">
<BR>
<B>Timer-Aktion -</B> Mit dieser Funktion können Sie die Aktionen nach einer bestimmten Zeit auslösen. D1 und D2 DIAL Optionen werden einen Aufruf der Transfer Conference Anzahl Presets starten und senden sie an die Agenten-Sitzung wird dies in der Regel für einfache IVR Validierung AGI-Anwendungen oder nur eine vorher aufgenommene Nachricht zu spielen verwendet. WebForm öffnet sich das Web-Formular-Adresse. MESSAGE_ONLY wird einfach Anzeige die Meldung, dass unten in das Feld ist. Niemand wird diese Funktion zu deaktivieren und ist der Standard -. Diese Einstellung überschreibt die Kampagne Einstellungen. HANGUP hängen bleiben wird das Gespräch, wenn der Timer ausgelöst wird, wird CALLMENU den Anruf an den Anruf-Timer-Menü im Feld Ziel angegeben Aktion zu senden, EXTENSION, wird der Anruf an die Erweiterung, die in der Timer-Aktion Feld Ziel angegeben wird, zu senden, wird IN_GROUP senden der Aufruf an die In-Group in den Timer-Aktion Feld Ziel angegeben.

<BR>
<A NAME="inbound_groups-timer_action_message">
<BR>
<B>Timer-Aktion Message -</B> Dies ist die Botschaft, die auf dem Bildschirm erscheint Agenten zum Zeitpunkt der Timer-Aktion ausgelöst wird.

<BR>
<A NAME="inbound_groups-timer_action_seconds">
<BR>
<B>Timer-Aktion Sekunden -</B> Dies ist die Zeit nach dem Anruf des Kunden, dass die Timer-Aktion wird ausgelöst, verbunden. Der Standardwert ist -1, die auch inaktiv.

<BR>
<A NAME="inbound_groups-timer_action_destination">
<BR>
<B>Timer-Aktion Reiseziel -</B> Dieses Feld ist, wo Sie das Call-Menü, Erweiterung oder In-Group angeben, dass Sie den Anruf an, wenn die Zeit Aktion zu CALLMENU, Erweiterung oder IN_GROUP eingestellt werden soll. Standard ist leer.

<BR>
<A NAME="inbound_groups-drop_call_seconds">
<BR>
<B>Drop Call Sekunden - </B> Die Anzahl der Sekunden eines Gesprächs bleibt in der Warteschlange, bevor sie als DROP.

<BR>
<A NAME="inbound_groups-drop_action">
<BR>
<B>Drop-Aktion - </B> Mit diesem Menü können Sie wählen, was passiert, wenn er einen Anruf wartet seit mehr als das, was ist in der Drop-Call Sekunden ein. HANGUP einfach auflegen, das Gespräch, MESSAGE wird die Forderung der Drop-Erweiterung, die Sie definiert haben, unten, Voicemail wird der Anruf an die Voicemail-Box, die Sie definiert haben, und unter IN_GROUP wird der Aufruf an die Inbound-Gruppe, die weiter unten definiert ist. VMAIL_NO_INST wird der Anruf an die Voicemail-Box, die Sie unten definiert haben, senden und keine Aufträge nach der Voicemail-Nachricht zu spielen.

<BR>
<A NAME="inbound_groups-drop_exten">
<BR>
<B>Drop-Erweiterung - </B> Wenn Drop-Aktion ist auf MESSAGE Dies ist der Plan wählen, dass sich die Aufforderung an, wenn sie erreicht Drop Call Sekunden. Für in-Gruppen AGENTDIRECT, wenn der Benutzer nicht verfügbar ist, können Sie AGENTEXT in diesem Bereich setzen und das System wird nach dem Benutzernamen fünf benutzerdefinierte Feld und schicken Sie den Anruf an dieses Dialplan Zahl.

<BR>
<A NAME="inbound_groups-voicemail_ext">
<BR>
<B>Voicemail -</B> If Drop Action is set to VOICEMAIL, the call DROP would instead be directed to this voicemail box to hear and leave a message. In einem in-group AGENTDIRECT hat die Einstellung dieses AGENTVMAIL wählt der Benutzer Voicemail-ID zu benutzen.

<BR>
<A NAME="inbound_groups-drop_inbound_group">
<BR>
<B>Drop-Transfer-Fraktion - </B> Wenn Drop-Aktion ist auf IN_GROUP, wird der Anruf wird an die Inbound-Gruppe, wenn sie erreicht Drop Call Sekunden.

<BR>
<A NAME="inbound_groups-drop_callmenu">
<BR>
<B>Drop-Call-Menü -</B> Wenn Drop-Aktion zu CALLMENU gesetzt ist, wird der Anruf mit dieser Aufforderung Menü geschickt werden, wenn sie auf Anruf beenden Sekunden erreicht.

<BR>
<A NAME="inbound_groups-action_xfer_cid">
<BR>
<B>Aktion Transfer-CID -</B> Wird für Tropfen, Afterhours und No-Agent-no-Warteschlange Aktionen. Dies ist die Anrufer-ID-Nummer, die für den Anruf, bevor es an Erweiterungen, Nachrichten, Voicemail-oder Call-Menüs übertragen. Sie können in diesem Feld KUNDEN verwenden, um die Kundentelefonnummer oder Kampagne zu verwenden, um die erste Kampagne erlaubt Anrufer-ID-Nummer. Standard ist KUNDEN. Wenn dies ein Aufruf, zu einem Call-Menü zurück zu gehen und dann eine in-group, empfehlen wir Ihnen CUSTOMERCLOSER in diesem Bereich zu verwenden, und auch Sie, um die In-Gruppe Handle-Methode in der Call-Menü näher festlegen müssen.

<BR>
<A NAME="inbound_groups-call_time_id">
<BR>
<B>Anruf-Zeit - dieses ist der für diese inbound Gruppe zu verwendenAnrufzeitentwurf. Halten Sie im Verstand, daß die Zeit auf derBedienerzeit basiert. Rückstellung ist 24hours.

<BR>
<A NAME="inbound_groups-after_hours_action">
<BR>
<B>After Hours Aktion - </B> Die Aktion durchführen, wenn sie nach Stunden wie in der Ausschreibung für diesen eingehenden Gruppe. HANGUP sofort hangup den Anruf, MESSASGE wird die Datei im After Hours Nachricht Filenam Bereich, AUSDEHNUNG wird die Aufforderung an die After Hours-Erweiterung in der dialplan und Voicemail wird der Anruf an die Voicemail-Box, die in den Bereich After Hours Voicemail , IN_GROUP wird die Aufforderung an die Inbound-Gruppe in der After Hours Transfer-Gruppe Wählen Sie Liste. Der Standardwert ist MESSAGE. VMAIL_NO_INST wird der Anruf an die Voicemail-Box, die Sie unten definiert haben, senden und keine Aufträge nach der Voicemail-Nachricht zu spielen.

<BR>
<A NAME="inbound_groups-after_hours_message_filename">
<BR>
<B>Nach Stunden Anzeige Dateinamen - die Audioakte gelegen auf demgespielt zu werden Bediener, wenn die Tätigkeit auf ANZEIGEeingestellt wird. Rückstellung ist VM-Auf Wiedersehen

<BR>
<A NAME="inbound_groups-after_hours_exten">
<BR>
<B>Nach Stunden Verlängerung - die dialplan Verlängerung, zum desAnrufs zu senden, wenn die Tätigkeit auf VERLÄNGERUNG eingestelltwird. Rückstellung ist 8300. Für in-Gruppen AGENTDIRECT, können Sie AGENTEXT in diesem Bereich setzen und das System wird nach dem Benutzernamen fünf benutzerdefinierte Feld und schicken Sie den Anruf an dieses Dialplan Zahl.

<BR>
<A NAME="inbound_groups-after_hours_voicemail">
<BR>
<B>Nach Stunden Voicemail - der voicemail Kasten, zum des Anrufs zusenden, wenn die Tätigkeit auf VOICEMAIL eingestellt wird. In einem in-group AGENTDIRECT hat die Einstellung dieses AGENTVMAIL wählt der Benutzer Voicemail-ID zu benutzen.

<BR>
<A NAME="inbound_groups-afterhours_xfer_group">
<BR>
<B>After Hours Transfer Group - </B>, wenn After Hours Aktion ist auf IN_GROUP, wird der Anruf wird an die Inbound-Gruppe, wenn sie die in der Gruppe außerhalb der Aufforderung zur Einreichung von Zeitplanung für die in-group.

<BR>
<A NAME="inbound_groups-after_hours_callmenu">
<BR>
<B>After Hours Call-Menü -</B> Wenn After Hours Aktion zu CALLMENU gesetzt ist, wird der Anruf an diesem Call-Menü geschickt werden, wenn sie die in-group außerhalb des Bereitschaftsdienstes Schema für die in-group definiert betritt.

<BR>
<A NAME="inbound_groups-no_agent_no_queue">
<BR>
<B>Nr. Bevollmächtigte Nr. Queueing -</B> Ist dieses Feld auf Y oder NO_PAUSED dann keine Anrufe in die Warteschlange für diese in-Gruppe gesetzt werden, wenn es keine Agenten und die Forderungen angemeldet gehen an die Nr. Agent Nr. Queue Aktion sind. Die NO_PAUSED Option wird auch das Senden der Anrufer in der Warteschlange, wenn es nur angehalten Agenten in der in-group sind. Der Standardwert ist N. In einem in-group AGENTDIRECT hat die Einstellung dieses AGENTVMAIL wählt der Benutzer Voicemail-ID zu benutzen. Sie können auch AGENTEXT in diesem Bereich, wenn es um Infusionsleitung ist und das System wird nach dem Benutzernamen fünf benutzerdefinierte Feld und schicken Sie den Anruf an dieses Dialplan Zahl. Wenn auf N gesetzt, werden die Anrufe anstehen, auch wenn es keine Agenten angemeldet und eingestellt, um Anrufe von dieser nehmen in-group.

<BR>
<A NAME="inbound_groups-no_agent_action">
<BR>
<B>Kein Agent Nr. Queue Aktion -</B> Wenn kein Anwalt keine Warteschlange aktiviert ist, wird dieses Feld definiert, wo die anrufen, wenn es keine Mittel sind, gehen in die In-Group. Der Standardwert ist MESSAGE, spielt dieser die Sound-Dateien in das Action Feld Wert und legt dann auf.

<BR>
<A NAME="inbound_groups-no_agent_action_value">
<BR>
<B>Kein Agent Nr. Queue Preis-Aktion -</B> Dies ist der Wert für die Aktion vor. Der Standardwert ist nbdy-avail-to-take-call|vm-goodbye.

<BR>
<A NAME="inbound_groups-max_calls_method">
<BR>
<B>Max ruft die Methode -</B> Diese Option kann damit die maximale gleichzeitige Anrufe für diese Funktion in-group. Bei der Einstellung TOTAL, dann ist die Gesamtzahl der Anrufe von Agenten in der Warteschlange und in dieser in-group gehandhabt wird nicht gestattet, das Max-Calls Graf Anzahl der Zeilen wie unten definiert überschreiten werden. Wenn zu IN_QUEUE gesetzt ist, dann, wenn die Anzahl der Anrufe in der Warteschlange für die Agenten nicht erlaubt wird, den Max überschreiten werden Calls Graf, egal wie viele Anrufe sind mit Agenten für diese in-group. Der Standardwert ist DISABLED.

<BR>
<A NAME="inbound_groups-max_calls_count">
<BR>
<B>Max ruft Graf -</B> Diese Option muss gesetzt höher als 0 sein, wenn Sie mit der MAX-Funktion ruft die Methode wollen. Standard ist 0.

<BR>
<A NAME="inbound_groups-max_calls_action">
<BR>
<B>Max ruft Aktion -</B> Dies ist der zu treffenden Maßnahme, wenn der Max ruft die Methode aktiviert ist und die Anzahl der Anrufe übersteigt, was oben in der Max-Calls gesetzt Count-Einstellung sein. Die Anrufe über diesen Betrag wird entweder mit dem Drop-Aktion, die Afterhours Aktion oder der NO_AGENT_NO_QUEUE Aktion geschickt werden und werden als MAXCAL Status mit einem MaxCalls hangup Grund angemeldet sein. Die Standardeinstellung ist NO_AGENT_NO_QUEUE.

<BR>
<A NAME="inbound_groups-welcome_message_filename">
<BR>
<B>Willkommener Anzeige Dateiname - die Audioakte gelegen auf demgespielt zu werden Bediener, wenn der Anruf hereinkommt. Wenn Satz zu---NONE --- dann wird keine Anzeige gespielt. Rückstellung ist---NONE ---. Dieses Feld wie bei den anderen Audio-Felder im In-Gruppen, mit Ausnahme der Agent Alert Dateiname, können mehrere Audio-Dateien abgespielt, wenn Sie eine Pipe getrennte Liste von Audio-Dateien in den Bereich.

<BR>
<A NAME="inbound_groups-play_welcome_message">
<BR>
<B>Jetzt Grußwort - </B> Diese Einstellungen wählen Sie, wann Sie spielen die definierten Willkommensnachricht, immer spielen sie jedes Mal, nie nie spielen, IF_WAIT_ONLY nur spielen die Willkommen-Nachricht, wenn die Forderung nicht sofort zu einem Agenten , und YES_UNLESS_NODELAY immer spielen die Willkommen-Nachricht, es sei denn, die NO_DELAY Einstellung aktiviert ist. Der Standardwert ist IMMER.

<BR>
<A NAME="inbound_groups-moh_context">
<BR>
<B>Musik auf Einfluß-Kontext - die Musik auf dem Einflußkontext, zum zuverwenden, wann der Kunde auf Einfluß gesetzt wird. Rückstellung istRückstellung.

<BR>
<A NAME="inbound_groups-onhold_prompt_filename">
<BR>
<B>Auf Einfluß-Aufforderung Dateinamen - die Audioakte gelegen auf demin einem regelmäßigen Abstand gespielt zu werden Bediener, wenn derKunde auf Einfluß ist. Rückstellung ist generic_hold. DieseAudioakte MUSS 9 Sekunden oder kleiner in der Länge sein.

<BR>
<A NAME="inbound_groups-prompt_interval">
<BR>
<B>Auf Einfluß-Aufforderung Abstand - die Zeitspanne in den Sekunden, zuwarten, bevor an die haltenaufforderung gespielt wird. Rückstellungist 60. So deaktivieren Sie die On-Hold-Prompt, das Intervall auf 0.

<BR>
<A NAME="inbound_groups-onhold_prompt_no_block">
<BR>
<B>Hold On Prompt keine Block -</B> Wenn diese Option auf Y ermöglicht Anrufe in der Schlange hinter einem Anruf in die Warteschleife, wo die Eingabeaufforderung zu spielen, um an einen Agenten gehen, wenn man zur Verfügung steht, während die Nachricht abgespielt wird. Während die On Hold Prompt Filename Nachricht an einen Kunden spielt sie können nicht an einen Agenten geschickt werden. Standard ist N.

<BR>
<A NAME="inbound_groups-onhold_prompt_seconds">
<BR>
<B>Hold On Prompt Sekunden -</B> Dieses Feld muss auf die Anzahl der Sekunden, die das On Hold Prompt Dateiname spielt für eingestellt werden. Standard ist 10.

<BR>
<A NAME="inbound_groups-play_place_in_line">
<BR>
<B>Jetzt in den Line - </B> Diese legt fest, ob die Anrufer hören, ihren Platz in der Zeile, wenn sie in der Warteschlange als auch, wenn sie hören, die announcemend. Der Standardwert ist N.

<BR>
<A NAME="inbound_groups-play_estimate_hold_time">
<BR>
<B>Jetzt Geschätzte Hold Time - </B> Diese legt fest, ob die Anrufer hören, halten Sie die geschätzte Zeit, bevor sie übertragen werden, zu einem Makler. Der Standardwert ist N. Wenn der Kunde gehalten wird, und hört diese geschätzte hold time Nachricht, die Mindestzeit, die abgespielt werden soll 15 Sekunden.

<BR>
<A NAME="inbound_groups-calculate_estimated_hold_seconds">
<BR>
<B>Berechnen Sie Geschätzte Hold-Sekunden -</B> Dies definiert die Anzahl der Sekunden in der Warteschlange, die der Kunde wartet vor der voraussichtlichen Hold Time wird berechnet und optional gespielt. Minimum beträgt 3 Sekunden, auch wenn weniger als 3 gesetzt. Standard ist 0.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_filename">
<BR>
<B>Geschätzte Mindest-Haltezeit Dateiname -</B> Wenn der geschätzte Hold Time ist aktiv und es wird berechnet, um am oder unter dem Minimum von 15 Sekunden sein, dann ist dies prompt Datei wird anstelle des Standard-Ankündigung abgespielt. Standardwert ist Empty für inaktive.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_no_block">
<BR>
<B>Geschätzte Mindest-Haltezeit Prompt keine Block -</B> Wenn Geschätzte Hold Time aktiv ist und die geschätzte Hold Time Minimum Feld Dateiname oben ist ausgefüllt, dann ist diese Option für Anrufe in der Schlange hinter einem Anruf, wo die Eingabeaufforderung zu spielen, um an einen Agenten gehen, wenn man zur Verfügung stehen wird, während die Nachricht abgespielt wird erlauben . Während die Eingabeaufforderung an einen Kunden spielt sie nicht an einen Agenten gesendet werden. Standard ist N.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_seconds">
<BR>
<B>Geschätzte Mindest-Haltezeit Prompt Sekunden -</B> Dieses Feld muss auf die Anzahl der Sekunden, dass die geschätzte Hold Time Minimum Dateiname Eingabeaufforderung für spielt eingestellt werden. Standard ist 10.

<BR>
<A NAME="inbound_groups-wait_time_option">
<BR>
<B>Wait Time Option -</B> Dies ermöglicht Ihnen, den Kunden Möglichkeiten, um die Warteschlange zu verlassen, wenn ihre Wartezeit ist über die Anzahl der Sekunden unten angegeben. Standardwert ist NONE. Wenn einer der PRESS_ Optionen gewählt wird, wird es spielen die Presse Dateiname unten definiert und geben dem Kunden die Möglichkeit, ein auf ihrem Handy drücken, um die Warteschlange zu verlassen, und führen Sie die gewählte Option. Die PRESS_STAY Option wird den Kunden zurück in die Warteschlange zu senden, ohne dabei ihren Platz in der Linie.

<BR>
<A NAME="inbound_groups-wait_time_second_option">
<BR>
<B>Wartezeit Zweite Option -</B> Gleiche wie die erste Option Wait Time Feld oben, außer diesem einen wird für den Kunden Drücken der Taste 2 zu prüfen. Standardwert ist NONE. Wenn keine Wartezeit erste Option gewählt dann ist diese Option nicht angeboten werden.

<BR>
<A NAME="inbound_groups-wait_time_third_option">
<BR>
<B>Wartezeit Third Option -</B> Gleiche wie die erste Option Wait Time Feld oben, außer diesem einen wird für den Kunden Drücken der Taste 3 zu überprüfen. Standardwert ist NONE. Wenn keine zweite Wartezeit Option ausgewählt wird dann diese Option nicht angeboten werden.

<BR>
<A NAME="inbound_groups-wait_time_option_seconds">
<BR>
<B>Wait Time Option Sekunden -</B> Wenn Wartezeit Option, um alles andere als NONE gesetzt ist, ist dies die Anzahl der Sekunden, dass der Kunde in der Warteschleife gewartet, dass die Wartezeit Optionen auslösen wird. Standardwert ist 120 Sekunden.

<BR>
<A NAME="inbound_groups-wait_time_option_exten">
<BR>
<B>Wait Time Option Erweiterung -</B> Wenn Wartezeit Option auf PRESS_EXTEN, ist dies der Dialplan-Erweiterung, dass der Aufruf gesendet werden, wenn der Kunde die Option-Taste drückt, wenn mit der Option vorgestellt. Für in-Gruppen AGENTDIRECT, können Sie AGENTEXT in diesem Bereich setzen und das System wird nach dem Benutzernamen fünf benutzerdefinierte Feld und schicken Sie den Anruf an dieses Dialplan Zahl.

<BR>
<A NAME="inbound_groups-wait_time_option_callmenu">
<BR>
<B>Wait Time Option Callmenu -</B> Wenn Wartezeit Option zum PRESS_CALLMENU gesetzt wird, ist dies das Menü Anruf, dass der Aufruf gesendet werden, wenn der Kunde die Option-Taste drückt, wenn mit der Option vorgestellt.

<BR>
<A NAME="inbound_groups-wait_time_option_voicemail">
<BR>
<B>Wait Time Option Voicemail -</B> Wenn Wartezeit Option auf PRESS_VMAIL, ist dies der Voicemail-Box, dass der Aufruf, wenn der Kunde die Option-Taste drückt, wenn mit der Option vorgestellt wird gesendet. In einem in-group AGENTDIRECT hat die Einstellung dieses AGENTVMAIL wählt der Benutzer Voicemail-ID zu benutzen.

<BR>
<A NAME="inbound_groups-wait_time_option_xfer_group">
<BR>
<B>Wait Time Option Transfer-In-Group -</B> Wenn Wartezeit Option zum PRESS_INGROUP gesetzt wird, ist dies die Gruppe, dass der eingehende Anruf wird weiterverbunden, wenn der Kunde die Option-Taste drückt, wenn mit der Option vorgestellt.

<BR>
<A NAME="inbound_groups-wait_time_option_press_filename">
<BR>
<B>Wait Time Option Drücken Dateiname -</B> Wenn Wartezeit Option zu einem der PRESS_ Optionen eingestellt ist, ist dies der Dateiname Eingabeaufforderung, die gespielt wird, wenn der Kunde Wartezeit überschreitet die Wartezeit Option Seconds to geben dem Kunden die Option auf 1, 2 oder 3 auf ihrem Handy drücken, um laufen die ausgewählten Wartezeit Drücken Sie Optionen. Es ist sehr wichtig, dass Sie Optionen in der Audiodatei für alle Ihre ausgewählten Wait Time-Optionen, und dass die Audiodatei in Sekunden Länge ist richtig in das Feld Dateiname Sekunden unten definiert oder wird es Probleme geben sind. Standard ist zu-sein-als-back.

<BR>
<A NAME="inbound_groups-wait_time_option_no_block">
<BR>
<B>Wait Time Option Presse keine Block -</B> Wenn diese Option auf Y ermöglicht Anrufe in der Schlange hinter einem Anruf, wo die Wartezeit Option Dateiname Presse prompt zu spielen, um an einen Agenten zu gehen, wenn einer verfügbar wird, während die Nachricht abgespielt wird. Während die Wartezeit Option Drücken Dateiname Nachricht an einen Kunden spielt sie nicht an einen Agenten gesendet werden. Standard ist N.

<BR>
<A NAME="inbound_groups-wait_time_option_prompt_seconds">
<BR>
<B>Wait Time Option Drücken Dateiname Sekunden -</B> Dieses Feld muss auf die Anzahl der Sekunden, die Wartezeit Option Dateiname spielt für Presse eingestellt werden. Standard ist 10.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_filename">
<BR>
<B>Wait Time Option Nach dem Drücken Dateiname -</B> Wenn Wartezeit Option zu einem der PRESS_ Optionen eingestellt ist, ist dies der Dateiname Eingabeaufforderung, die spielten, nachdem der Kunde 1, 2 oder 3 gedrückt hat, wird.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_list_id">
<BR>
<B>Wait Time Option Rückruf-Liste-ID -</B> Wenn Wartezeit Option zum PRESS_CID_CALLBACK gesetzt wird, ist dies die ID-Liste der Aufruf wird als neuer Lead hinzugefügt, wenn der Kunde die Option-Taste drückt, wenn mit der Option vorgestellt.

<BR>
<A NAME="inbound_groups-wait_hold_option_priority">
<BR>
<B>Warten Halten Sie die Wahltaste Priorität -</B> Wenn beide Optionen Geschätzte Hold Time und Wartezeit Optionen aktiv sind, wird diese Einstellung festlegen, ob eine, das andere oder beide dieser Funktionen aktiv sind. Zum Beispiel, wenn der geschätzte Hold Time Option auf 360 gesetzt ist, die Wartezeit Option auf 120 gesetzt und der Kunde hat für 120 Sekunden gewartet und es gibt noch schätzungsweise 400 Sekunden Haltezeit, dann sind sie beide zur gleichen Zeit aktiv sein und diese Einstellung wird überprüft, um zu sehen, welche Möglichkeiten angeboten werden soll. Standard ist nur WAIT.

<BR>
<A NAME="inbound_groups-hold_time_option">
<BR>
<B>Geschätzte Hold Time Option - </B> Auf diese Weise können Sie die Weiterleitung von den Anruf halten, wenn die geschätzte Zeit über dem Betrag von Sekunden aus. Der Standardwert ist KEINE. Wenn einer der PRESS_ Optionen gewählt wird, wird es spielen die Presse Dateiname unten definiert und geben dem Kunden die Möglichkeit, ein auf ihrem Handy drücken, um die Warteschlange zu verlassen, und führen Sie die gewählte Option.

<BR>
<A NAME="inbound_groups-hold_time_second_option">
<BR>
<B>Hold Time Zweite Option -</B> Gleiche wie die erste Option Hold Time Feld oben, außer diesem einen wird für den Kunden Drücken der Taste 2 zu prüfen. Standardwert ist NONE. Wenn kein erster Hold Time Option gewählt dann ist diese Option nicht angeboten werden.

<BR>
<A NAME="inbound_groups-hold_time_third_option">
<BR>
<B>Hold Time Third Option -</B> Gleiche wie die erste Option Hold Time Feld oben, außer diesem einen wird für den Kunden Drücken der Taste 3 zu überprüfen. Standardwert ist NONE. Wenn kein Sekunden Haltezeit Option gewählt dann ist diese Option nicht angeboten werden.

<BR>
<A NAME="inbound_groups-hold_time_option_seconds">
<BR>
<B>Hold Time Option Sekunden - </B> Wenn Hold Time-Option ist gesetzt, was aber keiner, das ist die Anzahl der Sekunden des geschätzten halten auslösen wird Zeit, dass die Hold-Zeit-Option. Der Standardwert ist 360 Sekunden.

<BR>
<A NAME="inbound_groups-hold_time_option_minimum">
<BR>
<B>Hold Time Option in einer Mindestharmonisierung -</B> Wenn Hold Time Option aktiviert, ist dies die minimale Anzahl von Sekunden der Anruf warten muss, bevor es mit der Haltezeit Option vorgestellt werden. Die Haltezeit Option wird sofort zu diesem Zeitpunkt vorgelegt werden, wenn der geschätzte Haltezeit ist größer als die Haltezeit Option Sekunden-Wert. Standardwert ist 0 Sekunden.

<BR>
<A NAME="inbound_groups-hold_time_option_exten">
<BR>
<B>Hold Time Option Extension - </B> Wenn Hold Time-Option auf Verlängerung, ist dies die dialplan, dass sich die Aufforderung an, wenn der geschätzte halten länger als die Hold Time Option Sekunden. Für in-Gruppen AGENTDIRECT, können Sie AGENTEXT in diesem Bereich setzen und das System wird nach dem Benutzernamen fünf benutzerdefinierte Feld und schicken Sie den Anruf an dieses Dialplan Zahl.

<BR>
<A NAME="inbound_groups-hold_time_option_callmenu">
<BR>
<B>Hold Time Option Callmenu -</B> Wenn Hold Time Option zum CALL_MENU gesetzt wird, ist dies das Menü Anruf, dass der Aufruf gesendet werden, wenn der geschätzte Haltezeit übersteigt die Haltezeit Option Sekunden.

<BR>
<A NAME="inbound_groups-hold_time_option_voicemail">
<BR>
<B>Hold Time Option Voicemail - </B> Wenn Hold Time auf Voicemail, das ist die Voice-Mailbox, dass der Anruf an, wenn der geschätzte halten länger als die Hold Time Option Sekunden. In einem in-group AGENTDIRECT hat die Einstellung dieses AGENTVMAIL wählt der Benutzer Voicemail-ID zu benutzen.

<BR>
<A NAME="inbound_groups-hold_time_option_xfer_group">
<BR>
<B>Hold Time Option Transfer In-Group - </B> Wenn Hold Time-Option ist auf IN_GROUP, das ist die Gruppe, dass die eingehenden Anruf werden, wenn der geschätzte halten länger als die Hold Time Option Sekunden.

<BR>
<A NAME="inbound_groups-hold_time_option_press_filename">
<BR>
<B>Hold Time Option Drücken Dateiname -</B> Wenn Hold Time Option zu einem der PRESS_ Optionen eingestellt ist, ist dies der Dateiname Eingabeaufforderung, die gespielt wird, wenn der geschätzte Haltezeit übersteigt die Haltezeit Option Sekunden, um den Kunden die Möglichkeit, ein auf ihrem Handy drücken, um das ausgewählte Hold Time laufen Drücken Sie Option. Es ist sehr wichtig, dass diese Audio-Datei 10 Sekunden oder weniger ist oder wird es Probleme geben. Standard ist zu-sein-als-back.

<BR>
<A NAME="inbound_groups-hold_time_option_no_block">
<BR>
<B>Hold Time Option Presse keine Block -</B> Wenn diese Option auf Y ermöglicht Anrufe in der Schlange hinter einem Anruf, wo die Hold Time Option Dateiname Presse prompt zu spielen, um an einen Agenten zu gehen, wenn einer verfügbar wird, während die Nachricht abgespielt wird. Während die Hold Time Option Drücken Filename Nachricht an einen Kunden spielt sie können nicht an einen Agenten geschickt werden. Standard ist N.

<BR>
<A NAME="inbound_groups-hold_time_option_prompt_seconds">
<BR>
<B>Hold Time Option Drücken Dateiname Sekunden -</B> Dieses Feld muss auf die Anzahl der Sekunden, die Hold Time Option Dateiname spielt für Presse eingestellt werden. Standard ist 10.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_filename">
<BR>
<B>Hold Time Option Nach dem Drücken Dateiname -</B> Wenn Hold Time Option auf eine der Optionen oder PRESS_ CALLERID_CALLBACK gesetzt wird, ist dies der Dateiname Eingabeaufforderung, die spielten, nachdem der Kunde 1 gedrückt wurde oder wird der Anruf an die Callback-Liste hinzugefügt.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_list_id">
<BR>
<B>Hold Time Option Liste Rückruf-ID - </B> Wenn Hold Time Option ist auf CALLERID_CALLBACK, Dies ist die Liste der Anruf-ID wird als neues führen, wenn der geschätzte halten länger als die Hold Time Option Sekunden.

<BR>
<A NAME="inbound_groups-agent_alert_exten">
<BR>
<B>Agent Alert Dateiname -</B> Die Audio-Datei zu spielen, einen Agenten zu verkünden, dass ein Anruf ankommt, um den Agenten. Um diese Funktion nicht nutzen setzen Sie ihn auf X. Der Standardwert ist ding.

<BR>
<A NAME="inbound_groups-agent_alert_delay">
<BR>
<B>Mittel-Alarm verzögert - die Zeitspanne in den Millisekunden, zuwarten, bevor er den Anruf zum Mittel schickt, nachdem er an dieMittel-Alarmverlängerung gespielt hat. Rückstellung ist 1000.

<BR>
<A NAME="inbound_groups-default_xfer_group">
<BR>
<B>Rückstellung Übergangsgruppe - dieses fangen ist dieRückstellung In-Gruppe auf, die automatisch vorgewählt wird, wenndas Mittel zum Bringenkonferenz Rahmen in ihrer Mittelschnittstellegeht.

<BR>
<A NAME="inbound_groups-ingroup_recording_override">
<BR>
<B>In-Group Recording Override -</B> Dieses Feld ermöglicht die übergeordnete der Kampagne Call Recording-Einstellung. Diese Einstellung kann vom Benutzer geänderten Einstellung Aufzeichnung überschrieben werden. BEHINDERTE die Kampagne Aufnahmeeinstellung nicht überschreiben. NIEMALS wird die Aufnahme auf dem Client zu deaktivieren. ONDEMAND ist der Standard und ermöglicht den Agenten zu starten und stoppen Sie die Aufnahme, wie gebraucht. ALLCALLS wird die Aufnahme auf dem Client zu starten, wenn ein Anruf an einen Agenten geschickt. ALLFORCE wird die Aufnahme auf dem Client zu starten, wenn ein Anruf an einen Agenten geschickt was dem Agenten keine Option, um die Aufnahme zu stoppen.

<BR>
<A NAME="inbound_groups-ingroup_rec_filename">
<BR>
<B>In-Group Recording Filename -</B> Dieses Feld überschreibt die Kampagne Recording Dateibenennung Schema, es sei denn auf NONE gesetzt ist. Die Variablen sind erlaubt CAMPAIGN INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH MITTELVENDORLEADCODE LEADID CALLID. Der Standardwert ist FULLDATE_MITTELund möchte dieses 20051020-103108_6666 aussehen. Ein weiteres Beispiel ist die CAMPAIGN_TINYDATE_CUSTPHONE wie dieser TESTCAMP_51020103108_3125551212 aussehen würde. Te resultierende Dateiname muss weniger als 90 Zeichen lang sein. Default is NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="inbound_groups-qc_enabled">
	<BR>
	<B>QC aktiv - </B> Wenn Sie diesen Bereich auf Y-Agent ermöglicht die Qualitätskontrolle Funktionen zur Verfügung stehen. Der Standardwert ist N.

	<BR>
	<A NAME="inbound_groups-qc_statuses">
	<BR>
	<B>QC-Status - </B> Dieser Bereich ist, in dem Sie auswählen, welche Zustände führt sollte weg von der QC-System. Aktivieren Sie das Kontrollkästchen neben den Status, die Sie wollen, QC, zu überprüfen. 

	<BR>
	<A NAME="inbound_groups-qc_shift_id">
	<BR>
	<B>QC-Shift - </B> Das ist die Verlagerung Zeitrahmen für die Pull-QC-Datensätze für eine inbound_group. Die Tage der Woche sind nicht für diese Funktionen.

	<BR>
	<A NAME="inbound_groups-qc_get_record_launch">
	<BR>
	<B>QC Get Record Launch-</B> Auf diese Weise können Sie eine der folgenden Aktionen ausgelöst werden dürfte, auf einem QC Agenten erhalten einen neuen Rekord.

	<BR>
	<A NAME="inbound_groups-qc_show_recording">
	<BR>
	<B>QC Show-Aufnahme - </B> Auf diese Weise können für eine Aufnahme, die im Zusammenhang mit der QC-Datensatz werden in der QC-Agent-Bildschirm.

	<BR>
	<A NAME="inbound_groups-qc_web_form_address">
	<BR>
	<B>QC WebForm Adresse - </B> Dies ist die Website-Adresse, dass ein QC Agenten können sich an, wenn Sie auf den Link WebForm in der QC-Bildschirm.

	<BR>
	<A NAME="inbound_groups-qc_script">
	<BR>
	<B>QC-Script - </B> Das ist das Skript, das verwendet werden kann, indem QC Agenten in den SCRIPT Registerkarte in der QC-Bildschirm.
	<?php
	}
?>

<BR>
<A NAME="inbound_groups-hold_recall_xfer_group">
<BR>
<B>Hold Recall Transfer In-Group - </B> Wenn ein Kunde, um zu dieser Gruppe mehr als einmal, und das ist nicht auf None gesetzt wird, dann wird der Anruf automatisch an das In-Gruppe in diesem Bereich ausgewählt . Der Standardwert ist KEINE.

<BR>
<A NAME="inbound_groups-no_delay_call_route">
<BR>
<B>Nr. Delay Call Route - </B> Wenn Sie diesen auf Y werden alle Wartezeiten und akustische und versuchen, den Anruf zu einem Agenten. Nicht überschreiben Willkommensnachricht oder halten Prompt Einstellungen. Der Standardwert ist N.

<BR>
<A NAME="inbound_groups-answer_sec_pct_rt_stat_one">
<BR>
<B>Statistiken Prozent der Anrufe innerhalb von X Sekunden - </B> In diesem Feld können Sie die Anzahl der Sekunden, halten Sie die Echtzeit-Statistiken angezeigt wird zur Berechnung der Anteil der Anrufe, die beantwortet wurden beantwortet X Anzahl von Sekunden zu halten.

<BR>
<A NAME="inbound_groups-start_call_url">
<BR>
<B>Start Call URL -</B> This web URL address is not seen by the agent, but it is called every time a call is sent to an agent if it is populated. Uses the same variables as the web form fields and scripts. Default is blank.

<BR>
<A NAME="inbound_groups-dispo_call_url">
<BR>
<B>Dispo Call URL -</B> This web URL address is not seen by the agent, but it is called every time a call is dispositioned by an agent if it is populated. Uses the same variables as the web form fields and scripts. dispo and talk_time are the variables you can use to retrieve the agent-defined disposition for the call and the actual talk time in Sekunden of the call. Default is blank.

<BR>
<A NAME="inbound_groups-add_lead_url">
<BR>
<B>Fügen Sie führen zur URL -</B> Diese Web-URL-Adresse wird nicht durch den Agenten gesehen, aber es wird immer dann aufgerufen, eine Leitung mit dem System über den eingehenden zugegeben wird. Standardeinstellung ist leer. Sie müssen diese URL mit VAR beginnen, wenn Sie Variablen verwenden möchten, und natürlich - A - und - B - rund um die eigentliche Variable in der URL, wo Sie sie benutzen wollen. Hier ist die Liste der Variablen, die verfügbar sind für diese Funktion. lead_id, vendor_lead_code, list_id, phone_number, phone_code, did_id, did_extension, did_pattern, did_description, uniqueid

<BR>
<A NAME="inbound_groups-na_call_url">
<BR>
<B>Kein Anruf-Agent URL -</B> Diese Web-URL-Adresse wird nicht durch den Agenten zu sehen, aber wenn es gefüllt wird aufgerufen, wenn ein Ruf, der nicht von einem Agenten gehandhabt wird nach oben oder aufgehängt übertragen. Verwendet die gleichen Variablen wie die Web-Formular und Indexe verwenden. dispo kann verwendet werden, um die vom System vorgegebene Disposition für den Anruf abzurufen. Diese URL kann kein relativer Pfad sein. Standardeinstellung ist leer.

<BR>
<A NAME="inbound_groups-default_group_alias">
<BR>
<B>Standard-Fraktion Alias - </B> Wenn Sie erlaubt Gruppe Aliases für die Kampagne, dass der Agent angemeldet ist, dann ist dies in der Gruppe ist, dass Alias zuerst ausgewählten standardmäßig auf einen Anruf, die von dieser Gruppe, wenn die Inbound-Agent dafür, Verwendung eines Alias-Fraktion für einen ausgehenden Anruf Handbuch. Der Standardwert ist Keine oder leer.

<BR>
<A NAME="inbound_groups-dial_ingroup_cid">
<BR>
<B>Dial-In-Gruppe CID -</B> Wenn der Agent Kampagne ermöglicht die manuelle In-Gruppenwahl, wird dieser Anrufer-ID-Nummer als ausgehende CID des Anrufs gesendet werden, wenn sie gefüllt wird, überschreibt die Kampagne Einstellungen und Liste CID Override-Einstellung. Der Standardwert ist leer.

<BR>
<A NAME="inbound_groups-extension_appended_cidname">
<BR>
<B>Erweiterung anhängen CID -</B> Wenn aktiviert, kommen die Anrufe in ins In-Gruppe wird eine Raum und die Durchwahl des Mittels an das Ende des CallerID Namen für den Anruf, bevor es an den Agenten gesendet wird. Standard ist für behinderte N.

<BR>
<A NAME="inbound_groups-uniqueid_status_display">
<BR>
<B>UniqueId Statusanzeige -</B> Wenn aktiviert, wenn ein Agent einen Anruf erhält durch diese in-group werden sie sehen den uniqueid der Aufruf der Statuszeile in ihrer Agenten-Interface hinzugefügt. Die Option Präfix wird das Präfix, unten definiert, auf den Anfang des uniqueid in der Anzeige hinzufügen. Standardeinstellung ist deaktiviert. Wenn es bereits eine UNIQUEID auf einen Anruf Betreten dieser in-group definiert, wird die ursprüngliche uniqueid wird angezeigt. Wenn die Domäne Option wird verwendet, und der Anruf wird an einen zweiten Agenten gesendet wird, die uniqueid und Präfix zu dem ersten Agenten angezeigt, auch mit dem zweiten Agenten angezeigt werden.

<BR>
<A NAME="inbound_groups-uniqueid_status_prefix">
<BR>
<B>UniqueId Status-Prefix -</B> Wenn PREFIX Option ausgewählt wird dann oben das ist der Wert dieses Präfix. Standard ist leer.




<BR><BR><BR><BR>

<B><FONT SIZE=3>INBOUND_DIDSTABELLE</FONT></B><BR><BR>
<BR>
<A NAME="inbound_dids-did_pattern">
<BR>
<B>DID-Erweiterung - </B> Das ist die Zahl, die Erweiterung oder das getan hat, löst diesen Eintrag, und dass Sie Route innerhalb des Systems mit dieser Funktion. Es ist ein Standard DID vorbehalten, die Sie verwenden können, die nur das Wort-default-ohne Bindestriche, Allos, die Sie senden jede Aufforderung, die nicht mit einer anderen bestehenden Muster auf den Standard DID.

<BR>
<A NAME="inbound_dids-did_description">
<BR>
<B>DID-Beschreibung - </B> Das ist die Beschreibung von der DID-Routing-Eintrag.

<BR>
<A NAME="inbound_dids-did_active">
<BR>
<B>DID-Aktive - </B> Das ist der Bereich, in dem Sie die DID Eintrag aktiv oder nicht. Der Standardwert ist Y.

<BR>
<A NAME="inbound_dids-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das hat, ermöglicht diese Betrachtung von Admin dies durch Nutzergruppe hat beschränkt. Standard ist - ALLE -, die jeder Benutzer admin an, um dieses tat erlaubt.

<BR>
<A NAME="inbound_dids-did_route">
<BR>
<B>DID Route -</B> Dies ist die Art der Route, die Sie setzen die DID zu bedienen. EXTEN Anrufe mit der Erweiterung unten eingegebenen senden, Voicemail Anrufe direkt an die Voicemail-Box senden unten eingegeben, wird MANAGER Anrufe an einen Agenten zu schicken, wenn sie eingeloggt sind, wird PHONE den Anruf zu einem Eintrag unten ausgewählten Handys zu senden, wird IN_GROUP senden Anrufe direkt an die angegebene Inbound-Gruppe. Standard ist EXTEN. CALLMENU wird der Anruf an die definierte Call-Menü senden. VMAIL_NO_INST wird der Anruf an die Voicemail-Box, die Sie unten definiert haben, senden und keine Aufträge nach der Voicemail-Nachricht zu spielen.

<BR>
<A NAME="inbound_dids-record_call">
<BR>
<B>Anruf aufzeichnen -</B> Diese Option ermöglicht es Ihnen, die Anrufe kommen in diese DID aufgezeichnet werden eingestellt. Y wird die gesamte Anruf aufzeichnen, wird Y_QUEUESTOP das Gespräch mitschneiden, bis der Anruf ist hungup oder tritt in eine Warteschlange in-group, N nimmt nicht auf den Anruf. Die Standardeinstellung ist N.

<BR>
<A NAME="inbound_dids-extension">
<BR>
<B>Extension - </B>, wenn die Erweiterung ist als der DID-Route, dann ist dies die Erweiterung dialplan, dass Anrufe werden an. Der Standardwert ist 9998811112, no-Service.

<BR>
<A NAME="inbound_dids-exten_context">
<BR>
<B>Erweiterung Kontext - </B>, wenn die Erweiterung ist als der DID-Route, dann ist dies die dialplan Zusammenhang, dass Anrufe werden an. Die Voreinstellung ist Standard.

<BR>
<A NAME="inbound_dids-voicemail_ext">
<BR>
<B>Voicemail-Box - </B> Wenn Voicemail ist als der DID-Route, dann ist dies die Voicemail-Box, die Anrufe werden an. Der Standardwert ist leer.

<BR>
<A NAME="inbound_dids-phone">
<BR>
<B>Telefon Extension - </B> Wenn PHONE ist als der DID-Route, dann ist das Telefon-Erweiterung, wird der Aufruf an.

<BR>
<A NAME="inbound_dids-server_ip">
<BR>
<B>Server-IP-Telefon - </B> Wenn PHONE ist als der DID-Route, dann ist dies der Server-IP-Telefon für die Erweiterung, die Anrufe werden an.

<BR>
<A NAME="inbound_dids-menu_id">
<BR>
<B>Call-Menü -</B> Wenn CALLMENU ist wie die DID-Route ausgewählt, dann ist dies der Ruf-Menü, dass Anrufe gesendet werden, werden.

<BR>
<A NAME="inbound_dids-user">
<BR>
<B>User Agent -</B> Wenn MANAGER als DID-Route ausgewählt, dann ist dies der Agent, wird auf Anrufe gesendet werden.

<BR>
<A NAME="inbound_dids-user_unavailable_action">
<BR>
<B>User-Aktion nicht verfügbar - </B> Wenn agent ist als der DID-Route, und der Benutzer ist nicht angemeldet oder verfügbar sind, dann ist dies der Weg, den die Anrufe wird.

<BR>
<A NAME="inbound_dids-user_route_settings_ingroup">
<BR>
<B>User-Einstellungen Route In-Group - </B> Wenn agent ist als der DID-Route, dann ist dies die In-Group, die verwendet werden für die Queue-Einstellungen wie der Anrufer wartet, die an den Agenten. Der Standardwert ist AGENTDIRECT.

<BR>
<A NAME="inbound_dids-group_id">
<BR>
<B>In-Group-ID - </B> Wenn IN_GROUP ist als der DID-Route, dann ist dies die In-Group, die Anrufe werden an.

<BR>
<A NAME="inbound_dids-call_handle_method">
<BR>
<B>In-Group Call Handle-Methodee -</B> Wenn IN_GROUP als DID-Route ausgewählt, dann ist dies der Call-Handling-Methode für diese Anrufe verwendet. CID wird einen neuen Leaddatensatz mit jedem Anruf mit der CallerID als der Telefonnummer hinzufügen, wird CIDLOOKUP versuchen, die Telefonnummer von der CallerID im gesamten System-Lookup wird CIDLOOKUPRL versuchen, die Telefonnummer von der CallerID nur in einer festgelegten Liste nachschlagen , CIDLOOKUPRC wird versuchen, die Telefonnummer von der CallerID in allen Listen, die der angegebenen Kampagne NÄHER für Closer Anrufe angegeben gehören Lookup wird ANI einen neuen Lead-Datensatz mit jedem Anruf mit der ANI wie die Telefonnummer, ANILOOKUP hinzufügen versuchen werden, um die Telefonnummer von der ANI im gesamten System-Lookup wird ANILOOKUPRL versuchen, die Telefonnummer von der ANI in nur einer bestimmten Liste nachschlagen, werden XDIGITID den Anrufer für einen X-stelligen Code vor dem Aufruf aufgefordert wird, in die genommen werden Warteschlange wird VIDPROMPT den Anrufer für ihre ID-Nummer aufgefordert und wird eine neue Leitung Datensatz mit der CallerID wie die Telefonnummer und die ID als die Hersteller-ID erstellen, wird VIDPROMPTLOOKUP versuchen, die ID im gesamten System-Lookup wird VIDPROMPTLOOKUPRL versuchen, Nachschlag die Hersteller-ID durch die ID nur in einer bestimmten Liste, wird VIDPROMPTLOOKUPRC versuchen, die Hersteller-ID durch die ID in allen Listen, die der angegebenen Kampagne gehören Nachschlag. Standard ist CID. Wenn ein CIDLOOKUP Methode wird mit ALT verwendet, wird es die alt_phone Feld für die Telefonnummer suchen, wenn keine Spiele sind für die Haupttelefonnummer gefunden. Wenn ein CIDLOOKUP Methode wird mit ADDR3 verwendet, wird es die address3 Feld für die Telefonnummer suchen, wenn keine Spiele sind für die Haupttelefonnummer gefunden und gegebenenfalls das Feld alt_phone.

<BR>
<A NAME="inbound_dids-agent_search_method">
<BR>
<B>In-Group-Agent Suche Methode - </B> Wenn IN_GROUP ist als der DID-Route, dann ist dies der Agent Suche Methode für die Inbound-Gruppe, LO ist Load-Balanced-Overflow und versuchen um den Ruf zu senden einem Agenten auf dem lokalen Server, bevor Sie versuchen, senden Sie sie an einen Agenten auf einem anderen Server, Load-LB ist ausgewogen und wird versuchen, um den Ruf zu senden, um zur nächsten Agent-Server, egal was sie sind, so ist nur Server-und nur versuchen, die Forderungen an Agenten auf dem Server, dass der Anruf kam auf. Der Standardwert ist LB.

<BR>
<A NAME="inbound_dids-list_id">
<BR>
<B>In-Group-ID-Liste - </B> Wenn IN_GROUP ist als der DID-Route, dann ist dies die ID-Liste führt, dass kann man suchen, durch und führt werden, wenn notwendig, in.

<BR>
<A NAME="inbound_dids-campaign_id">
<BR>
<B>In-Group-ID-Kampagne - </B> Wenn IN_GROUP ist als der DID-Route, dann ist dies die Kampagnen-ID, was kann man suchen, wenn das Gespräch mit Methode ist CIDLOOKUPRC.

<BR>
<A NAME="inbound_dids-phone_code">
<BR>
<B>In-Group Telefon Code - </B> Wenn IN_GROUP ist als der DID-Route, dann ist das Telefon-Code verwendet, wenn eine neue Führung wird erstellt.

<BR>
<A NAME="inbound_dids-filter_clean_cid_number">
<BR>
<B>Reinigen Sie CID-Nummer -</B> In diesem Feld können Sie eine Anzahl von Ziffern angeben, um den eingehenden Anrufer-ID-Nummer zu beschränken, indem sie ein R vor der Anzahl der Stellen, zum Beispiel um an die richtigen Stellen Sie in 10 R10 geben würde, zu beschränken. Sie können diese Funktion auch verwenden, um nur eine führende Ziffer oder Ziffern, indem sie ein L vor den spezifischen Ziffern, die Sie entfernen möchten, zum Beispiel, um eine 1 als erste Ziffer Sie in L1 geben würde entfernen zu entfernen. Standard ist leer. Wenn mehr als eine Regel angegeben wird sicherstellen, dass Sie trennen Sie diese mit einem Leerzeichen und dem R wird vor der Ausführung L.

<BR>
<A NAME="inbound_dids-filter_inbound_number">
<BR>
<B>Filter Inbound-Nummer -</B> Diese Option, falls aktiviert können Sie Anrufe kommen in diese DID und schicken sie auf eine alternative Handlung, wenn sie eine Telefonnummer, die im Filter Phone Group oder einer URL-Antwort ist, wenn man ein so konfiguriert haben, passen zu filtern. Der Standardwert ist DISABLED. Gruppe wird in einem Filter-Phone-Gruppe zu finden. URL senden Sie eine URL und treffen zu, wenn eine 1 zurück gesendet wird. DNC_INTERNAL wird durch die interne DNC Liste suchen. DNC_CAMPAIGN wird von einem bestimmten Kampagne DNC Liste suchen.

<BR>
<A NAME="inbound_dids-filter_phone_group_id">
<BR>
<B>Filter Phone Gruppe Identifikation -</B> Wenn der Filter Inbound Feld Nummer GRUPPE gesetzt ist, dann ist dies die ID des Filter-Phone Group, die haben ihre Zahlen gesucht auf der Suche nach einer Übereinstimmung mit dem Anrufer-ID-Nummer des eingehenden Anrufs wird.

<BR>
<A NAME="inbound_dids-filter_url">
<BR>
<B>URL-Filter -</B> Wenn der Filter Inbound-Nummer Feld URL gesetzt ist, dann ist dies die Web-Adresse von einem Skript, das eine Remote-System zu suchen und zurück wird eine 1 für ein Spiel und eine 0 für kein Spiel. Nur zwei Variablen sind in der Adresse zur Verfügung, wenn Sie die VAR-Präfix wie mit Webformular-Adressen in Kampagnen zu nutzen, - A - phone_number - B - und - A - did_pattern - B - können in der URL verwendet werden auf die Anrufer-ID des Anrufers und zeigt die DID, dass der Kunde in aufgerufen.

<BR>
<A NAME="inbound_dids-filter_url_did_redirect">
<BR>
<B>URL-Filter DID-Redirect -</B> Wenn die Anzahl Inbound Feld Filter auf URL gesetzt ist, dann ermöglicht diese Einstellung die URL Reaktion auf ein System DID, um den Anruf zu insead der Verwendung der Standard-Aktion umleiten angeben. Wenn eine 0 wird dann wieder die Standard-Aktion verwendet wird. Wenn etwas anderes als eine 0 zurückgegeben, dann wird der Anruf an die resultierende URL Antwortwert umgeleitet werden.

<BR>
<A NAME="inbound_dids-filter_dnc_campaign">
<BR>
<B>Filter DNC-Kampagne -</B> Wenn die Anzahl Inbound Feld Filter so eingestellt ist, DNC_CAMPAIGN dann ist dies die spezifische Kampagnen-ID, die die Kampagne DNC-Liste gehört.



<BR>
<A NAME="inbound_dids-filter_action">
<BR>
<B>Filteraktion -</B> Wenn Filter Inbound-Nummer wird aktiviert und eine Übereinstimmung gefunden wird, dann ist dies die Aktion, die zu nehmen ist. Dies ist die gleiche wie die Route, die Sie wählen für eine DID, und die folgenden Einstellungen funktionieren genau wie sie es tun für eine Standard-Routing.

<BR>
<A NAME="inbound_dids-custom_one">
<BR>
<B>Benutzerdefinierte Felder DID - </ B> Diese fünf Felder können für verschiedene Zwecke verwendet werden, vor allem in Bezug auf kundenspezifische Programmierung und Berichte.




<BR>
<A NAME="did_ra_extensions">
<BR>
<B>DID Remote Agent Erweiterung Overrides -</B><BR>Dieser Abschnitt erlaubt Ihnen, zu ermöglichen DIDs Erweiterung Überschreibungen für Remote-Agenten geroutet haben ruft durch in-Gruppen. Der Nutzer muss eine gültige Start-Remote-Agent User Anfang sein, oder wenn Sie die Erweiterung Überschreiben Eintrag für alle Anrufe zu arbeiten wollen, dann können Sie --- Alle --- in der User Feld Start. Wenn es mehrere Einträge für denselben DID und Benutzer starten dann die aktiven Einträge in einem Round-Robin-Verfahren verwendet werden.




<BR><BR><BR><BR>

<B><FONT SIZE=3>CALL MENU, Tabelle</FONT></B><BR><BR>
<A NAME="call_menu-menu_id">
<BR>
<B>Menü-ID -</B> Dies ist die ID für diesen Schritt der Aufforderung Menü. Dies wird auch angezeigt, wie der Kontext, die in der dialplan für diesen Aufruf ist im Menü. Hier ist eine Liste der reservierten Sätze, die nicht als Menü-IDs verwendet werden kann: vicidial, vicidial-auto, general, globals, default, trunkinbound, loopback-no-log, monitor_exit, monitor.

<BR>
<A NAME="call_menu-menu_name">
<BR>
<B>Menu Name -</B> Dieses Feld ist die Bezeichnung für den Aufruf im Menü.

<BR>
<A NAME="call_menu-menu_prompt">
<BR>
<B>Prompt Menu -</B> Dieses Feld enthält den Dateinamen der Audio-Eingabeaufforderung zu Beginn dieses Menüs spielen. Sie können mehrere propmts in dieses Feld eingeben und den anderen Bereichen, prompt, indem Sie diese mit einem Pipe-Zeichen. Sie können NOINT direkt vor einer Audio-Datei, um ihn zu machen, so dass die Wiedergabe kann nicht mit einem Tastendruck vom Anrufer unterbrochen werden hinzufügen, desto NOINT sollte nicht ein Teil des Dateinamens sein, ist es eine besondere Flagge für das System. Sie können auch spezielle Zwecke. Agi Skripte in diesem Bereich als auch wie die cm_date.agi Skript, mit Ihren Administrator für weitere Details zu besprechen.

<BR>
<A NAME="call_menu-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="call_menu-menu_timeout">
<BR>
<B>Menü-Timeout -</B> Dieses Feld ist, wo Sie den Timeout in Sekunden einstellen, dass das Menü wird für den Anrufer in einer DTMF-Wahl geben, warten. Festlegen dieses Feld auf Null 0 bedeutet, dass es keine Wartezeit nach der Eingabeaufforderung geben wird gespielt.

<BR>
<A NAME="call_menu-menu_timeout_prompt">
<BR>
<B>Menü-Timeout Prompt -</B> Dieses Feld enthält den Dateinamen der Audio-Eingabeaufforderung zu spielen, wenn das Zeitlimit erreicht ist. Default ist keiner, der nicht bei Audio-Timeout spielen.

<BR>
<A NAME="call_menu-menu_invalid_prompt">
<BR>
<B>Prompt Menu Ungültige -</B> Dieses Feld enthält den Dateinamen der Audio-Eingabeaufforderung zu spielen, wenn der Anrufer eine ungültige Option ausgewählt hat. Default ist keiner, der keine Audio-Wiedergabe auf ungültige.

<BR>
<A NAME="call_menu-menu_repeat">
<BR>
<B>Menü wiederholen -</B> Dieses Feld ist, wo Sie die Anzahl der Male, dass das Menü nach dem ersten Mal, wenn keine gültige Wahl ist vom Anrufer aus spielen wird, zu definieren. Der Standardwert ist 1, um das Menü einmal wiederholen.

<BR>
<A NAME="call_menu-menu_time_check">
<BR>
<B>Menü Time Check -</B> Dieses Feld ist, wo Sie wählen, ob der Ruf-Menü Zugriff auf die spezifischen Stunden in der ausgewählten Call Time eingerichtet eingeschränkt werden kann. Wenn der Call Time leer ist, wird diese Einstellung ignoriert. Der Standardwert ist 0 für Behinderte.

<BR>
<A NAME="call_menu-call_time_id">
<BR>
<B>Anrufzeit ID -</B> Dies ist die Call Time-ID, die verwendet werden, zu berufen, mal zu beschränken, wenn die Menü-Time Check Option aktiviert ist.

<BR>
<A NAME="call_menu-track_in_vdac">
<BR>
<B>Track fordert in Real-Time-Report -</B> Dieses Feld ist, wo Sie wählen, ob Sie den Anruf wollen, in das Echtzeit-Bildschirm als einen eingehenden IVR Call verfolgt werden können. Der Standardwert ist 1 für aktive.

<BR>
<A NAME="call_menu-tracking_group">
<BR>
<B>Tracking-Fraktion -</B> Dies ist die ID, die Sie für Anrufe an dieser Aufforderung Menü Länge, wenn man die IVR-Bericht verwenden können. Die Liste enthält CALLMENU als Standard-sowie alle In-Gruppen.

<BR>
<A NAME="call_menu-dtmf_log">
<BR>
<B>LOG-Taste drücken -</B> Diese Option wird aktiviert, wenn die DTMF-Taste drücken durch den Anrufer in dieser Aufforderung Menü anmelden. Standard ist 0 für Behinderte.

<BR>
<A NAME="call_menu-dtmf_field">
<BR>
<B>Protokollfeld -</B> Wenn die LOG-Taste drücken Option aktiviert ist, kann diese optionale Einstellung ermöglicht die Reaktion auf auch in dieser Liste Feld gespeichert werden. vendor_lead_code, source_id, phone_code, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, alt_phone, email, security_phrase, comments, rank, owner, status, user. Der Standardwert ist NONE für Behinderte.

<BR>
<A NAME="call_menu-option_value">
<BR>
<B>Optionswert -</B> Dieses Feld ist, wo Sie den Menüpunkt definieren, Wahlmöglichkeiten: 0,1,2,3,4,5,6,7,8,9,*,#,A,B,C,D,TIMECHECK. Die besondere TIMECHECK Option kann nur verwendet werden, wenn Sie Menü Time Check aktiviert und es gibt einen Call Time definiert für die Menü -. Um eine Option zu löschen, einfach die Route zu entfernen und die Option wird gelöscht, wenn Sie auf den Submit-Button. TIMEOUT können Sie festlegen, was mit dem Anruf, wenn es mal aus, ohne Eingaben des Anrufers. INVALID können Sie festlegen, was passiert, wenn der Anrufer eine ungültige Option. INVALID_2ND und 3. kann nur dann aktiv, wenn ungültige nicht verwendet wird, wird es erst in der zweiten oder dritten ungültigen Eintrag durch den Anrufer warten, bevor es die Option ausführt.

<BR>
<A NAME="call_menu-option_description">
<BR>
<B>Option Beschreibung -</B> Dieses Feld ist, wo Sie die Möglichkeit, beschreiben kann, wird diese Beschreibung in den dialplan als Kommentar über die Möglichkeit genommen werden.

<BR>
<A NAME="call_menu-option_route">
<BR>
<B>Option Route -</B> Dieses Menü enthält die Optionen für die, wo der Anruf gesendet wird, falls diese Option ausgewählt ist: CALLMENU, Eigen-, DID, HANGUP, ERWEITERUNG, TEL. Für CALLMENU, sollte die Route Wert auf die Menü-ID des Call-Menü sein, dass Sie den Anruf an. Für die Eigen-, In-Gruppe, die Sie wollen, dass die Aufforderung an die Bedürfnisse geschickt werden, um ausgewählt werden, sowie die anderen 5 Optionen, die Notwendigkeit, ordnungsgemäß Route einen Anruf an eine Inbound-Gruppe eingestellt werden. Für DID, muss der Wert für die Route der DID-Muster, das Sie wollen, um den Anruf zu senden. Für HANGUP kann die Route Wert ist der Name einer Audio-Datei vor dem Aufhängen der Aufforderung zu spielen. DIE ERWEITERUNG, muss der Wert für die Route der dialplan Erweiterung, die Sie möchten den Anruf zu senden, und die Route Preis-Kontext ist der Kontext, dass die Verlängerung in der Lage ist, wenn leer gelassen Rahmen wird standardmäßig auf Standard. Für Telefon, muss die Route Preis zu sein Telefon anmelden Wert für die Handys Eintrag, den Sie möchten den Anruf zu senden. Für Voicemail, muss die Route Wert für die Voice-Mailbox-Nummer, die nicht verfügbar mesage wird abgespielt. Für AGI, muss die Route Wert für die AGI-Skript und alle Werte, die an sie übergeben werden. VMAIL_NO_INST wird der Anruf an die Voicemail-Box, die Sie unten definiert haben, senden und keine Aufträge nach der Voicemail-Nachricht zu spielen.

<BR>
<A NAME="call_menu-option_route_value">
<BR>
<B>Option Route Preis -</B> Dieses Feld ist, wo Sie den Wert, der festlegt, wo in der gewählten Route Option, dass der Anruf eingeben, um die Anweisung bekommen,.

<BR>
<A NAME="call_menu-option_route_value_context">
<BR>
<B>Option Route Preis Kontext -</B> Dieses Feld ist optional und nur für die Extension Option Routen eingesetzt.

<BR>
<A NAME="call_menu-ingroup_settings">
<BR>
<B>Rufen Sie Menü In-Group-Einstellungen -</B> Wenn die Fahrtroute eingestellt ist, um Eigengruppe dann gibt es viele Optionen, die Sie setzen, um festzulegen, wie der Aufruf wird in die Warteschlange gesendet werden können. In-Group ist die Inbound-Gruppe, die Sie den Anruf an gehen wollen. Handle-Methode ist, wie Sie wollen den Anruf zu handhabenden, <a href="#inbound_dids-call_handle_method">Klicken Sie hier, um eine Liste der verfügbaren Methoden siehe Griff</a>. Suchmethode definiert, wie die Warteschlange wird beim nächsten Agenten zu finden, empfehlen lassen Sie dieses auf LB. ID-Liste ist die Liste, dass die neue Führung in eingesetzt wird, auch wenn die Methode nicht um eine Lookup-Methode und das Blei wird nicht gefunden. Kampagnen-ID ist die Kampagne, um Listen durch, wenn eine der RC-Verfahren verwendet wird, zu suchen. Telefon Code ist die phone_code Feld Eintrag für die Führung, die mit eingefügt wird. VID eingeben, wenn die Methode zu einem der VIDPROMPT Methoden gesetzt wird Dateiname verwendet wird, ist es die Audio-Aufforderung gespielt, um den Kunden zu bitten, ihre ID eingeben. VID-ID-Nummer Dateiname verwendet wird, wenn die Methode zu einem der VIDPROMPT Methoden gesetzt wird, ist es die Audio-Aufforderung spielte nach Kunde gibt seine ID, so etwas wie Sie eingegeben haben. VID bestätigen Dateiname verwendet wird, wenn die Methode zu einem der VIDPROMPT Methoden gesetzt wird, ist es die Audio-Aufforderung gespielt, um ihre Identifikation zu bestätigen, so etwas wie PRESS 1 bestätigen und 2, um erneut einzugeben. VID Digits wird verwendet, wenn die Methode zu einem der VIDPROMPT Methoden gesetzt wird, wenn es zu einer Reihe ist es die Anzahl der Stellen, die vom Kunden eingegeben werden, wenn für ihre ID aufgefordert werden müssen, wenn sie leer oder X dann das gesetzt wird Kunde muss Nummernzeichen drücken auf ihre Aufnahme ihrer ID beenden.

<BR>
<A NAME="call_menu-custom_dialplan_entry">
<BR>
<B>Benutzerdefinierte Dialplan Eintrag -</B> Dieses Feld ermöglicht es Ihnen, in jedem dialplan Elemente, die Sie für die Call-Menü eingeben wollen.

<BR>
<A NAME="call_menu-qualify_sql">
<BR>
<B>Qualifizieren SQL -</B> Dieses Feld ermöglicht es Ihnen, SQL - Structured Query Language - Datenbank-Fragmente, wie mit Filtern, um festzustellen, ob dieser Aufruf Menü sollte für den Anrufer oder nicht spielen. Diese Funktion funktioniert nur, wenn der Anruf hat die CallerIDName Satz, bevor sie an diesem Aufruf Menü gesendet, entweder als Outbound-Umfrage zu übertragen oder durch den Einsatz von einem Tropfen Anruf-Menü für eine In-Gruppe anrufen. Wenn es eine Übereinstimmung gibt, wird der Anruf normal weitergehen. Wenn es keine Übereinstimmung gibt, wird der Anruf an die D-Option oder der ungültigen Option zu gehen, wenn kein D-Option eingestellt ist. Sie können keine einfachen Anführungszeichen in diesem Bereich, nur doppelte Anführungszeichen, wenn sie gebraucht werden. Der Standardwert ist leer für Behinderte.






<BR><BR><BR><BR>

<BR>
<A NAME="filter_phone_groups-filter_phone_group_id">
<BR>
<B>Filter Phone Gruppe Identifikation -</B> Dies ist die ID des Filter-Phone Group, die die Container für eine Gruppe von Telefonnummern, die Sie automatisch durchsucht haben, wenn ein Anruf in ein DID und senden Sie eine alternative Route, wenn es eine Übereinstimmung gibt. Dieses Feld sollte zwischen 2 und 20 Zeichen lang sein und haben keine Satzzeichen außer Unterstrich.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_name">
<BR>
<B>Filter Phone Gruppe Name -</B> This is the name of the Filter Phone Group and is displayed with the ID in select lists where this feature is used. This field should be between 2 and 40 characters.

<BR>
<A NAME="filter_phone_groups-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_description">
<BR>
<B>Filter Phone Group Beschreibung -</B> Dies ist die Beschreibung des Filter Phone Group, ist es rein für die Notation Zwecken und ist nicht ein erforderliches Feld.




<BR><BR><BR><BR>

<B><FONT SIZE=3>REMOTE_AGENTSTABELLE</FONT></B><BR><BR>
<A NAME="remote_agents-user_start">
<BR>
<B>Benutzernummer Anfang -</B> Dies ist die Ausgangs Benutzer-ID, die verwendet wird, wenn die Remote-Agent-Einträge werden in das System eingesetzt. Wenn die Anzahl der Zeilen größer als 1 gesetzt ist, wird diese Zahl um eins erhöht, bis jede Zeile hat einen Eintrag. Stellen Sie sicher, dass Sie ein neues Benutzerkonto mit einem Benutzerebene von 4 oder große erstellen, wenn Sie wollen, dass sie in der Lage, die vdremote.php Seite für Remote-Web-Zugriff auf dieses Konto verwenden.

<BR>
<A NAME="remote_agents-number_of_lines">
<BR>
<B>Zeilenzahl -</B> Dieses definiert, wievieles Remotemittel Eintragungendas System verursacht, und stellt fest, wieviele Linien es denkt, daßes zur Zahl unter sicher senden kann.

<BR>
<A NAME="remote_agents-server_ip">
<BR>
<B>Bediener IP -</B> Eine Remotemitteleintragung ist für einenspezifischen Bediener, ist hier nur gut, wo Sie vorwählen, dasBediener Sie wünschen.

<BR>
<A NAME="remote_agents-conf_exten">
<BR>
<B>Externe Verlängerung -</B> Dieses ist die Zahl, daß Sie die Anrufewünschen, die zu nachgeschickt werden. Überprüfen Sie, ob es einevolle dial plan Zahl ist und daß, wenn Sie 9 am Anfang benötigen, Sieihn innen hier setzen. Prüfen Sie, indem Sie diese Nummer von einemTelefon auf dem System wählen.

<BR>
<A NAME="remote_agents-extension_group">
<BR>
<B>Erweiterungs-Gruppe -</B> Wenn etwas anderes als NONE oder leer dies wird die externe Feld Erweiterung außer Kraft setzen und nutzen den Extension-Gruppe Einträge, die die gleiche Ausdehnung Gruppen-ID eingestellt haben. Der Standardwert ist NONE deaktiviert.

<BR>
<A NAME="remote_agents-status">
<BR>
<B>Status -</B> Ist hier, wo Sie das Remotemittel an und abstellen.Sobald das Mittel aktiv ist, nimmt das System an, daß es Anrufe zuihm schicken kann. Es kann bis 30 Sekunden dauern, sobald Sie denStatus zu unaktiviertem ändern, um Anrufe, zu empfangen zu stoppen.

<BR>
<A NAME="remote_agents-campaign_id">
<BR>
<B>Kampagne -</B> Ist hier, wo Sie die Kampagne vorwählen, daß dieseRemotemittel in geloggt werden. Inbound Notwendigkeiten, die GENAUEREKampagne zu verwenden und die inbound Kampagnen unter dervorzuwählen, die Sie Anrufe von empfangen möchten.

<BR>
<A NAME="remote_agents-on_hook_agent">
<BR>
<B>Auf-Hook-Agent -</B> Diese Option ist nur für eingehende Anrufe gehen auf diese Remote-Agent verwendet. Diese Funktion ruft die Remote-Agenten und wird nicht sendet dem Kunden an den Remote-Agent, bis die Leitung beantwortet wird. Standard ist für behinderte N.

<BR>
<A NAME="remote_agents-on_hook_ring_time">
<BR>
<B>On-Hakenring Zeit -</B> Diese Option wird nur verwendet, wenn der On-Hook-Agent-Feld oben auf Y gesetzt und dann auch nur für eingehende Anrufe kommen auf diese Remote-Agent. Dies ist die Anzahl der Sekunden, dass jeder Anrufversuch wird, klingeln, um zu versuchen, eine Antwort zu bekommen. Es wird empfohlen, dass Sie diese Einstellung auf ein paar Sekunden weniger, als es für einen Aufruf an die Voicemail gesendet werden braucht. Die Standardeinstellung ist 15.

<BR>
<A NAME="remote_agents-closer_campaigns">
<BR>
<B>Inbound Gruppen -</B> Ist hier, wo Sie die inbound Gruppen vorwählen,die Sie Anrufe von empfangen möchten, wenn Sie die GENAUERE Kampagnevorgewählt haben.


<BR><BR><BR><BR>

<B><FONT SIZE=3>EXTENSION_GROUPSTABELLE</FONT></B><BR><BR>
<A NAME="extension_groups-extension_group_id">
<BR>
<B>Erweiterungs-Gruppe -</B> Dieses erforderliche Feld ist, wo Sie die Gruppen-ID, die Sie wollen, dass diese Erweiterung in Kraft gesetzt werden. Keine Leerzeichen oder Sonderzeichen außer Unterstrich Buchstaben und Zahlen.

<BR>
<A NAME="extension_groups-extension">
<BR>
<B>Extension -</B> Dieses Feld ist erforderlich, wo Sie die Dialplan-Erweiterung stellen, dass Sie wollen, dass der Remote-Agent ruft an, um für diese Nebenstelle Gruppe Eintrag gesendet werden.

<BR>
<A NAME="extension_groups-rank">
<BR>
<B>Rank -</B> In diesem Feld können Sie die Einträge, die Verlängerung Gruppe die gleiche Ausdehnung Gruppe teilen Rang. Standard ist 0.

<BR>
<A NAME="extension_groups-campaign_groups">
<BR>
<B>Kampagnen Gruppen -</B> In diesem Feld können Sie legte eine Liste von Kampagnen-IDs und Gruppen-IDs oder Inbound, dass Sie die Nutzung der Erweiterung für die Gruppe zu beschränken. Liste werden getrennt durch Rohrleitungen und eine Leitung am Anfang und Ende des Strings.


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN_LISTS</FONT></B><BR><BR>
<A NAME="campaign_lists">
<BR>
<B>Die Listen innerhalb dieser Kampagne werden hier verzeichnet, ob siewerden bezeichnet durch das Y aktiv sind, oder N und Sie zum ListeSchirm gehen können, indem sie auf der Liste Identifikation in derersten Spalte klicken.</B>


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN_STATUSESTABELLE</FONT></B><BR><BR>
<A NAME="campaign_statuses">
<BR>
<B>Durch die Verwendung von benutzerdefinierten Kampagne Zustände, können Sie Zustände, die nur für eine bestimmte Kampagne existieren. Die Status muss 1-8 Zeichen lang sein, muss die Beschreibung 2-30 Zeichen lang sein und Wählbare definiert, ob es zeigt sich in dem System als Disposition. Die human_answered Feld wird bei der Berechnung die Drop Prozentsatz oder Abbruchrate. Die Einstellung auf Y human_answered diesen Status zu verwenden, wenn das Zählen der menschlichen angenommene Anrufe. Die Option Kategorie können Sie mehrere Status-Gruppe in eine catogy, die für die statistische Analyse verwendet werden können.</B>



<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>CAMPAIGN_HOTKEYSTABELLE</FONT></B><BR><BR>
	<A NAME="campaign_hotkeys">
	<BR>
	<B>Durch die Verwendung von benutzerdefinierten Kampagne Hotkeys, Agenten, der Agent Web-Client verwenden, hängen kann und Disposition ruft einfach durch Drücken einer einzigen Taste auf Ihrer Tastatur.</B> Es gibt two special HotKey options that you can use in conjunction with Alternate Phone number dialing, ALTPH2 - Alternate Phone Hot Dial and ADDR3-----Address3 Hot Dial allow an agent to use a hotkey to hang up their call, stay on the same lead, and dial another contact number from that lead. Sie können auch LTMG oder XFTAMM als Zustände, eine automatische Übertragung der Leave-Voicemail-Option auslösen.





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_RECYCLETABELLE</FONT></B><BR><BR>
	<A NAME="lead_recycle">
	<BR>
	<B>Through the use of lead recycling, you can call specific statuses of leads again at a specified interval without resetting the entire list. Lead recycling is campaign-specific and does not have to be a selected dialable status in your campaign. The attempt delay field is the number of seconds until the lead can be placed back in the hopper, this number must be at least 120 seconds. The attempt maximum field is the maximum number of times that a lead of this status can be attempted before the list needs to be reset, this number can be from 1 to 10. You can activate and deactivate a lead recycle entry with the provided links.</B>





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>AUTO ALT DIAL Status</FONT></B><BR><BR>
	<A NAME="auto_alt_dial_statuses">
	<BR>
	<B>Wenn das SelbstAlt-zahl Wählen wird eingestellt auffangen,dann die Leitungen, die sind, unter diesen Vorwahlknopfstatus desAutomobils Alt haben ihr alt_phone dispositioned, und-oder address3gewählt, nachdem irgendwelche von diesen Status kein-antworten,werden eingestellt auffängt.</B>

	<?php
	}
?>



<BR><BR><BR><BR>

<B><FONT SIZE=3>MITTEL-PAUSE CODES</FONT></B><BR><BR>
<A NAME="pause_codes">
<BR>
<B>Wenn der AgentPause CodesAktive Feld aktiv gesetzt wird dann die Agenten in der Lage, aus diesenPause Codesauswählen, wenn sie auf der PAUSE-Taste am Bildschirm klicken. Diese Daten werden dann in dem Mittel Protokoll gespeichert. Die Pause-Code muss nur Buchstaben und Zahlen enthalten und weniger als 7 Zeichen. Die Pause Codename kann nicht länger als 30 Zeichen sein.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN PRESETS</FONT></B><BR><BR>
<A NAME="xfer_presets">
<BR>
<B>Wenn die Kampagne Einstellung für Presets auf Aktiviert eingestellt ist, dann haben die Fähigkeit, Transfer-Presets, die Konferenz zur Verfügung stehen, um dem Agenten wird es ihnen ermöglicht, 3-Wege nennen diese Presets oder blind Anrufe an diese vorgegebenen Zahlen zu definieren. Diese Voreinstellungen haben auch eine Option, um die Zahl mit jedem Preset aus dem Agenten verbunden zu verbergen.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN CID AREACODES</FONT></B><BR><BR>
<A NAME="campaign_cid_areacodes">
<BR>
<B>Wenn die Einstellung SYSTEM bei Vorwahl CIDs aktiviert ist und die Kampagne für die Einstellung Use Benutzerdefinierte CallerID soll AREACODE dann haben Sie die Möglichkeit, Vorwahl CIDs, die verwendet werden, wenn ausgehende Anrufe, um Leads in diesem speziellen Kampagne zu definieren. Sie können mehrere callerIDs pro Ortsvorwahl und Sie können aktivieren und deaktivieren jeweils in Echtzeit. Wenn mehr als ein callerID ist für eine bestimmte Ortsvorwahl aktiv dann wird das System mit der CallerID, die die geringste Anzahl von Zeiten heute verwendet wurde. Wenn keine callerIDs für die Vorwahl der Region aktiv sind, dann ist die Kampagne CallerID oder Override-Liste CallerID verwendet werden.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>USER_GROUPSTABELLE</FONT></B><BR><BR>
<A NAME="user_groups-user_group">
<BR>
<B>Benutzer-Gruppe -</B> Dies ist der Kurzname einer Benutzergruppe, versuchen, keine Leerzeichen oder Satzzeichen für dieses Feld zu verwenden. max 20 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="user_groups-group_name">
<BR>
<B>Gruppe Name -</B> Dies ist die Beschreibung der Benutzergruppe maximal 40 Zeichen.

<BR>
<A NAME="user_groups-forced_timeclock_login">
<BR>
<B>ForceTimeclock Login -</B> Diese Option ermöglicht Ihnen, nicht zulassen, ein Agent einloggen, um das Agenten-Interface, wenn sie nicht in die Zeitschaltuhr eingeloggt. Der Standardwert ist N. Es gibt eine Option, um Admin-Benutzer befreien, Stufen 8 und 9.

<BR>
<A NAME="user_groups-shift_enforcement">
<BR>
<B>Shift Vollstreckung -</B> Diese Einstellung ermöglicht es Ihnen, Agenten-Logins auf der Grundlage der Veränderungen, die ausgewählt werden. AUS wird nicht in allen Schichten durchzusetzen. START wird nur die Durchsetzung der Login-Zeit, sondern wirkt sich nicht auf einen Vertreter, der mit über die Verlagerung, wenn sie bereits eingeloggt ALL wird durchzusetzen Verlagerung starten und meldet einen Agenten aus, nachdem sie über das Ende ihrer Schicht Zeit. Standardeinstellung ist Aus.

<BR>
<A NAME="user_groups-group_shifts">
<BR>
<B>Gruppe Verschiebungen - </B> Dies ist eine Liste der wählbaren Schichten, die sich die Agenten anmelden, Zeit auf dem System.

<BR>
<A NAME="user_groups-allowed_campaigns">
<BR>
<B>Erlaubte Kampagnen -</B> dieses ist eine auswählbare Liste der Kampagnen,zu denen Mitglieder dieser Benutzergruppe innen protokollieren könnenin. Die ALL-CAMPAIGNS Wahl erlaubt den Benutzern in dieser Gruppe, injeder möglicher Kampagne auf dem System innen zu sehen und zuprotokollieren.

<BR>
<A NAME="user_groups-agent_status_viewable_groups">
<BR>
<B>Agent Status Sichtbare Gruppen -</B> Dies ist eine Liste der wählbaren Benutzer-Gruppen und User-Funktionen, auf die Mitglieder dieser Benutzergruppe den Status sowie die Übertragung Anrufe innerhalb des Agent-Bildschirm anzeigen können. Der ALL-GRUPPEN Option ermöglicht es dem Benutzer in dieser Gruppe zu sehen und Anrufe für jeden Benutzer auf dem System. Die Kampagnen-AGENTUREN Option können Benutzer in dieser Gruppe zu sehen und Anrufe für jeden Benutzer in der Kampagne, dass sie angemeldet sind, in. Die NICHT-logged-in-Agenten Option können alle Benutzer im System angezeigt werden soll, auch wenn sie nicht eingeloggt sind in curently.

<BR>
<A NAME="user_groups-agent_status_view_time">
<BR>
<B>Agent Status View Time -</B> Diese Option legt fest, ob der Agent die Höhe der Zeit, dass die Nutzer in ihrer Bevollmächtigten Seitenleiste in ihren aktuellen Status zu sehen gewesen sein wird. Der Standardwert ist N nicht oder deaktiviert.

<BR>
<A NAME="user_groups-agent_call_log_view">
<BR>
<B>Agent-Call Log anzeigen -</B> Diese Option definiert, ob Agenten in der Lage, ihre Anrufprotokoll für Anrufe über den Agenten abgewickelt Bildschirm zu sehen. Standard ist N für keine oder deaktiviert.

<BR>
<A NAME="user_groups-agent_xfer_options">
<BR>
<B>Agent-Transfer-Optionen -</B> Mit diesen Optionen können für die Sperrung von bestimmten Tasten in der Transfer Conference Abschnitt des Agent-Schnittstelle. Die Standardeinstellung ist J für Ja oder aktiviert.

<BR>
<A NAME="user_groups-agent_fullscreen">
<BR>
<B>Agent-Vollbild -</B> Diese Option, wenn auf Y wird die Höhe und Breite des Mittels Bildschirm, um die Größe des Browser-Fensters ohne Berücksichtigung der Bevollmächtigte anzeigen gesetzt, Anrufe in der Warteschlange anzeigen oder Anrufe in der Session-Ansicht. Standard ist N für keine oder deaktiviert.

<BR>
<A NAME="user_groups-allowed_reports">
<BR>
<B>Erlaubt Berichte -</B> Wenn ein Benutzer in dieser Gruppe auf Benutzer-Ebene 7 oder höher festgelegt ist, dann ist dieses Feature kann verwendet werden, um die Berichte, dass die Benutzer sehen können eingeschränkt werden. Der Standardwert ist ALL. Wenn Sie mehr als einen Bericht auswählen möchten und drücken Sie die Strg-Taste auf Ihrer Tastatur, wenn Sie die Berichte auswählen.

<BR>
<A NAME="user_groups-admin_viewable_groups">
<BR>
<B>Erlaubte Benutzer-Gruppen -</B> Dies ist eine Liste der wählbaren Benutzergruppen, denen die Mitglieder dieser Benutzergruppe ansehen und ggf. bearbeiten. Benutzer-Gruppen können den Zugriff auf nahezu alle Aspekte des Systems, vom Wareneingang bis DIDs Telefone an die Voicemail-Boxen. Die - ALLE - Option erlaubt den Benutzern in dieser Gruppe zu sehen und melden Sie sich an, um jeden Datensatz auf dem System, wenn ihre Benutzerberechtigungen für sie erlauben.

<BR>
<A NAME="user_groups-admin_viewable_call_times">
<BR>
<B>Anruf erlaubt Zeiten -</B> Dies ist eine Liste der wählbaren Zeiten anrufen, an die Mitglieder dieser Benutzergruppe in Kampagnen verwenden können, in Gruppen-und Call-Menüs. Die - ALLE - Option erlaubt den Benutzern in dieser Gruppe, um alle Call-Zeiten im System verwenden.


<?php
if (strlen($SSwebphone_url) > 5)
	{
	?>
	<BR>
	<A NAME="user_groups-webphone_url_override">
	<BR>
	<B>Webphone URL Override -</B> Diese Einstellung ermöglicht es Ihnen, eine alternative URL webphone nur für die Mitglieder einer Benutzergruppe festzulegen. Standard ist leer.

	<BR>
	<A NAME="user_groups-webphone_systemkey_override">
	<BR>
	<B>Webphone System Key Override -</B> Diese Einstellung ermöglicht es Ihnen, einen alternativen webphone System Key nur für die Mitglieder einer Benutzergruppe festzulegen. Standard ist leer.

	<BR>
	<A NAME="user_groups-webphone_dialpad_override">
	<BR>
	<B>Webphone Dialpad Override -</B> Diese Einstellung können Sie aktivieren oder deaktivieren Sie das Tastenfeld auf der webphone nur für die Mitglieder einer Benutzergruppe. Der Standardwert ist DISABLED. ROCKER kann der Benutzer zum Anzeigen und Ausblenden der Wähltasten durch Anklicken eines Links. TOGGLE_OFF ausfällt, um nicht das Tastenfeld beim ersten Laden, sondern ermöglicht es dem Benutzer, die Wähltasten, indem Sie zeigen, auf dem Tastenfeld Link.

	<?php
	}

if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="user_groups-qc_allowed_campaigns">
	<BR>
	<B>QC erlaubt Kampagnen - </B> Dies ist eine Liste der wählbaren Kampagnen, die Mitglieder dieser Gruppe in der Lage, QC. Die ALL-KAMPAGNEN Option ermöglicht es dem Benutzer in dieser Gruppe, QC einer Kampagne auf dem System.

	<BR>
	<A NAME="user_groups-qc_allowed_inbound_groups">
	<BR>
	<B>QC erlaubt Inbound Gruppen - </B> Dies ist eine Liste der wählbaren Eingang Gruppen, die Mitglieder dieser Gruppe in der Lage, QC. Die ALL-GRUPPEN Option ermöglicht es dem Benutzer in dieser Benutzergruppe zu QC jede eingehende Gruppe über das System.
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>INDEXETABELLE</FONT></B><BR><BR>
<A NAME="scripts-script_id">
<BR>
<B>Index Identifikation -</B> Dies ist der Kurzname eines Script. Dies muss eine eindeutige Kennung darstellen. Versuchen Sie nicht, alle Leerzeichen oder Satzzeichen für dieses Feld zu verwenden. max 10 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="scripts-script_name">
<B>Index-Name -</B> Dies ist der Titel einer Schrift. Dies ist eine kurze Zusammenfassung des Skripts. max 50 Zeichen, mindestens 2 Zeichen. Es sollte keine Leerzeichen oder Satzzeichen jeglicher Art in Theis Feld.

<BR>
<A NAME="scripts-script_comments">
<B>Index-Anmerkungen -</B> Dies ist, wo Sie können Kommentare für einen Agenten wie Bildschirm Skript zum kostenlosen Upgrade auf 23. September, wie-verändert legen -. max 255 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="scripts-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="scripts-script_text">
<B>Index-Text -</B> Hier können Sie den Inhalt einer Script-Agent-Bildschirm zu platzieren. Mindestens 2 Zeichen. Sie können mit Kundeninformationen haben werden in diesem Skript automatisch ausgefüllt "--A--field--B--" Feld ist, wo Sie eine der folgenden Feldnamen: vendor_lead_code, source_id, list_id, gmt_offset_now, called_since_last_reset, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, lead_id, campaign, phone_login, group, channel_group, SQLdate, epoch, uniqueid, customer_zap_channel, server_ip, SIPexten, session_id, dialed_number, dialed_label, rank, owner, camp_script, in_script, script_width, script_height, recording_filename, recording_id, user_custom_one, user_custom_two, user_custom_three, user_custom_four, user_custom_five, preset_number_a, preset_number_b, preset_number_c, preset_number_d, preset_number_e, preset_number_f, preset_dtmf_a, preset_dtmf_b, did_id, did_extension, did_pattern, did_description, closecallid, xfercallid, agent_log_id, entry_list_id, call_id, user_group, called_count,TABELLEper_call_notes. For example, this sentence would print the persons name in it----<BR><BR>  Hello, can I speak with --A--first_name--B-- --A--last_name--B-- please? Well hello --A--title--B-- --A--last_name--B-- how are you today?<BR><BR> This would read----<BR><BR>Hello, can I speak with John Doe please? Well hello Mr. Doe how are you today?<BR><BR> Sie können auch ein iframe verwenden, um ein separates Fenster in der Registerkarte Skript laden, hier ist ein Beispiel mit Vorkonfektionierte Variablen:

<DIV style="height:200px;width:400px;background:white;overflow:scroll;font-size:12px;font-family:sans-serif;" id=iframe_example>
&#60;iframe src="http://www.sample.net/test_output.php?lead_id=--A--lead_id--B--&#38;vendor_id=--A--vendor_lead_code--B--&#38;list_id=--A--list_id--B--&#38;gmt_offset_now=--A--gmt_offset_now--B--&#38;phone_code=--A--phone_code--B--&#38;phone_number=--A--phone_number--B--&#38;title=--A--title--B--&#38;first_name=--A--first_name--B--&#38;middle_initial=--A--middle_initial--B--&#38;last_name=--A--last_name--B--&#38;address1=--A--address1--B--&#38;address2=--A--address2--B--&#38;address3=--A--address3--B--&#38;city=--A--city--B--&#38;state=--A--state--B--&#38;province=--A--province--B--&#38;postal_code=--A--postal_code--B--&#38;country_code=--A--country_code--B--&#38;gender=--A--gender--B--&#38;date_of_birth=--A--date_of_birth--B--&#38;alt_phone=--A--alt_phone--B--&#38;email=--A--email--B--&#38;security_phrase=--A--security_phrase--B--&#38;comments=--A--comments--B--&#38;user=--A--user--B--&#38;campaign=--A--campaign--B--&#38;phone_login=--A--phone_login--B--&#38;fronter=--A--fronter--B--&#38;closer=--A--user--B--&#38;group=--A--group--B--&#38;channel_group=--A--group--B--&#38;SQLdate=--A--SQLdate--B--&#38;epoch=--A--epoch--B--&#38;uniqueid=--A--uniqueid--B--&#38;customer_zap_channel=--A--customer_zap_channel--B--&#38;server_ip=--A--server_ip--B--&#38;SIPexten=--A--SIPexten--B--&#38;session_id=--A--session_id--B--&#38;dialed_number=--A--dialed_number--B--&#38;dialed_label=--A--dialed_label--B--&#38;rank=--A--rank--B--&#38;owner=--A--owner--B--&#38;phone=--A--phone--B--&#38;camp_script=--A--camp_script--B--&#38;in_script=--A--in_script--B--&#38;script_width=--A--script_width--B--&#38;script_height=--A--script_height--B--&#38;recording_filename=--A--recording_filename--B--&#38;recording_id=--A--recording_id--B--&#38;user_custom_one=--A--user_custom_one--B--&#38;user_custom_two=--A--user_custom_two--B--&#38;user_custom_three=--A--user_custom_three--B--&#38;user_custom_four=--A--user_custom_four--B--&#38;user_custom_five=--A--user_custom_five--B--&#38;preset_number_a=--A--preset_number_a--B--&#38;preset_number_b=--A--preset_number_b--B--&#38;preset_number_c=--A--preset_number_c--B--&#38;preset_number_d=--A--preset_number_d--B--&#38;preset_number_e=--A--preset_number_e--B--&#38;preset_number_f=--A--preset_number_f--B--&#38;preset_dtmf_a=--A--preset_dtmf_a--B--&#38;preset_dtmf_b=--A--preset_dtmf_b--B--&#38;did_id=--A--did_id--B--&#38;did_extension=--A--did_extension--B--&#38;did_pattern=--A--did_pattern--B--&#38;did_description=--A--did_description--B--&#38;closecallid=--A--closecallid--B--&#38;xfercallid=--A--xfercallid--B--&#38;agent_log_id=--A--agent_log_id--B--&#38;entry_list_id=--A--entry_list_id--B--&#38;call_id=--A--call_id--B--&&#38;user_group=--A--user_group--B--&&#38;" style="width:580;height:290;background-color:transparent;" scrolling="auto" frameborder="0" allowtransparency="true" id="popupFrame" name="popupFrame" width="460" height="290" STYLE="z-index:17"&#62;
&#60;/iframe&#62;
</DIV>
<BR>
Sie können auch eine spezielle Variable IGNORENOSCROLL zu Bildlaufleisten auf der Registerkarte Skript zwingen, selbst wenn Sie mit einem iFrame sind darin.

<BR>
<A NAME="scripts-active">
<BR>
<B>Aktiv -</B> Dieses stellt fest, ob dieser Index vorgewählt werdenkann, durch eine Kampagne verwendet zu werden.





<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_FILTERTABELLE</FONT></B><BR><BR>
	<A NAME="lead_filters-lead_filter_id">
	<BR>
	<B>Filter ID -</B> Dies ist der Kurzname eines Lead-Filter. Dies muss eine eindeutige Kennung darstellen. Verwenden Sie keine Leerzeichen oder Satzzeichen für dieses Feld. max 10 Zeichen, mindestens 2 Zeichen.

	<BR>
	<A NAME="lead_filters-lead_filter_name">
	<B>Filter-Name -</B> Dieses ist ein beschreibenderer Name des Filter.Dieses ist eine kurze Zusammenfassung der Filtermaximum 30 Buchstaben,Minimum von 2 Buchstaben.

	<BR>
	<A NAME="lead_filters-lead_filter_comments">
	<B>Filter Anmerkungen -</B> Dies ist, wo Sie können Kommentare für einen Filter wie-alle Anrufe California-führt. max 255 Zeichen, mindestens 2 Zeichen.

	<BR>
	<A NAME="lead_filters-user_group">
	<BR>
	<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

	<BR>
	<A NAME="lead_filters-lead_filter_sql">
	<B>Filter SQL -</B> Dieses ist, wohin Sie das SQL Frage Fragment setzen,das Sie vorbei filtern möchten anfangen nicht, oder Ende mit UND, dasdurch den Zufuhrbehälter cron Index automatisch hinzugefügt wird.eine Beispiel SQL Frage, die hier arbeiten würde, ist called_count \4 und called_count \.
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>AnrufzeitenTABELLE</FONT></B><BR><BR>
<A NAME="call_times-call_time_id">
<BR>
<B>Anrufzeit ID -</B> Dies ist der Kurzname eines Anrufzeit Definition. Dies muss eine eindeutige Kennung darstellen. Verwenden Sie keine Leerzeichen oder Satzzeichen für dieses Feld. max 10 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="call_times-call_time_name">
<B>Anrufzeit Name -</B> Dies ist ein besser beschreibender Name für die Anrufzeit Definition. Es ist eine kurze Zusammenfassung der Anrufzeit Definition. Maximal 30 Zeichen, mindestens 2.

<BR>
<A NAME="call_times-call_time_comments">
<B>Anrufzeit Kommentare -</B> Dies ist, wo Sie können Kommentare für Call Time Definition wie-von 10.00 bis 04.00 Uhr mit extra Anruf-Zustand zu versetzen Einschränkungen. max 255 Zeichen.

<BR>
<A NAME="call_times-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="call_times-ct_default_start">
<B>Standard Start and Stop Zeiten -</B> Dies ist die Standardzeit, zu der Anrufe innerhalb der Anrufzeitdefinition gestartet oder gestoppt werden dürfen, wenn die Wochentag Startzeit nicht definiert ist. 0 ist Mitternacht. Um Anrufe komplett zu verhindern den Wert auf 2400 und die Standard Stopzeit ebenfalls auf 2400 setzen. Um Anrufe für 24 Stunden am Tag zu erlauben die Startzeit auf 0 und die Stopzeit auf 2400 setzen. Für nur eingehend, können Sie auch den Anschlag Anrufzeit höher als 2400, wenn Sie die Forderung der Zeit, über Mitternacht gehen wollen. Also, wenn Sie möchten Ihren Anruf Zeit von 6 Uhr morgens bis 2 Uhr am nächsten Tag, würden Sie 0600 als Startzeit und 2600 gelegt, als der Stoppzeit.

<BR>
<A NAME="call_times-ct_sunday_start">
<B>Wochentag Start and Stop Zeit -</B> Dies sind die speziellen Zeiten pro Tag, welche für die Anrufzeit Definition gesetzt werden können. Es gelten die selben Regeln wie für Start und Stop Zeiten.

<BR>
<A NAME="call_times-default_afterhours_filename_override">
<B>After Hours Dateiname Override -</B> In diesen Feldern können Sie die After Hours Nachricht für eingehende Gruppen außer Kraft setzen, wenn es um etwas eingestellt wird. Standard ist leer.

<BR>
<A NAME="call_times-ct_state_call_times">
<B>Land Anrufzeit Definition -</B> Dies ist die Liste der landspezifischen Anrufzeit Definitionen, die von dieser Anrufzeit Definition beachtet werden.

<BR>
<A NAME="call_times-state_call_time_state">
<B>Landesspezifische Anrufzeit Definition -</B> Dies ist der zwei Buchstaben Code für das Land, für das die Anrufzeit Definition gilt. Damit diese ausgeführt wird muss die in der Kampagne gesetzte lokale Anrufzeit diese landesspezifische Anrufzeit Aufzeichnung enthalten, genauso wie alle anzurufenden Anschlüsse den landesspezifischen zwei Buchstaben Code enthalten müssen .

<A NAME="call_times-holiday_id">
<BR>
<B>Ferien ID -</B> Dies ist der Kurzname einer Ferienwohnung Definition. Dies muss eine eindeutige Kennung sein. Verwenden Sie keine Leerzeichen oder Satzzeichen für dieses Feld. max 30 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="call_times-holiday_name">
<B>Feiertags-Name -</B> Dies ist ein beschreibender Name der Ferienwohnung Definition. Dies ist eine kurze Zusammenfassung der Ferien Definition. max 100 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="call_times-holiday_comments">
<B>Ferien Kommentare -</B> This is where you can place comments for a Holiday Definition such as -10am to 4pm boxing day restrictions-.  max 255 characters.

<BR>
<A NAME="call_times-holiday_date">
<B>Feiertag Datum -</B> Dies ist das Datum des Feiertags.

<BR>
<A NAME="call_times-holiday_status">
<B>Ferien-Status -</B> Dies ist der Status des Urlaubs Eintrag. ACTIVE-Status bedeutet, dass der Urlaub auf der Ferieninsel Datum wird aktiviert. INACTIVE Status bedeutet, dass der Urlaub auch am Feiertag Datum wird ignoriert. EXPIRED bedeutet, dass der Urlaub hat es Feiertagsdatum weitergegeben. Standard ist inaktiv.




<BR><BR><BR><BR>

<B><FONT SIZE=3>SHIFTSTABELLE</FONT></B><BR><BR>
<A NAME="shifts-shift_id">
<BR>
<B>Shift ID -</B> Dies ist der Kurzname eines Systems Shift-Definition. Dies muss eine eindeutige Kennung darstellen. Verwenden Sie keine Leerzeichen oder Satzzeichen für dieses Feld. max 20 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="shifts-shift_name">
<B>Shift Name - </B> Dies ist eine beschreibende Bezeichnung der Shift Definition. Dies ist eine kurze Zusammenfassung der Shift Definition. max 50 Zeichen, mindestens 2-Zeichen.

<BR>
<A NAME="shifts-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="shifts-shift_start_time">
<B>Start Time Shift - </B> Es ist an der Zeit, dass die Kampagne Schicht beginnt. Darf nur Zahlen, 9:30 AM wäre 0930 und 5:00 PM wäre 1700.

<BR>
<A NAME="shifts-shift_length">
<B>Shift Länge - </B> Das ist die Zeit in Stunden und Minuten, dass die Kampagne Schicht dauert. 8 Stunden wäre 08:00 Uhr und 7 Stunden und 30 Minuten würde 07:30.

<BR>
<A NAME="shifts-shift_weekdays">
<B>Shift Wochentage - </B> In diesem Abschnitt sollten Sie die Tage der Woche, dass diese Verschiebung ist aktiv.

<BR>
<A NAME="shifts-report_option">
<B>Bericht Option -</B> Diese Option ermöglicht diese spezielle Schicht zu zeigen, bis in ausgewählte Berichte, die diese Option unterstützen.

<BR>
<A NAME="shifts-report_rank">
<B>Melde Rang -</B> Diese Option ermöglicht es Ihnen, Verschiebungen in ausgewählte Berichte, die diese Option unterstützen Rang.




<BR><BR><BR><BR>
<A NAME="audio_store">
<B>Audio-Store -</B> Dieses Programm erlaubt Ihnen, Audio-Dateien auf den Webserver hochladen, so dass sie auf alle Systemserver in einer Multi-Server-Cluster verteilt werden. Ein wichtiger Hinweis, wird nur zwei Audio-Dateitypen. Wav-Dateien, die PCM-Mono-und 16-Bit-8k. Gsm-Dateien, die 8-Bit-8k sind. Bitte überprüfen Sie, ob Ihre Dateien sind, bevor hier Hochladen formatiert.



<BR><BR><BR><BR>

<B><FONT SIZE=3>MUSIC_ON_HOLDTABELLE</FONT></B><BR><BR>
<A NAME="music_on_hold-moh_id">
<BR>
<B>Music On Hold ID -</B> Dies ist der Kurzname eines Music On Hold Eintrag. Dies muss eine eindeutige Kennung. Verwenden Sie keine Leerzeichen oder sonstige Zeichen für dieses Feld. max 100 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="music_on_hold-moh_name">
<B>Music On Hold Name -</B> Dies ist eine mehr beschreibende Name des Music On Hold-Eintrag. Dies ist eine kurze Zusammenfassung des Music On Hold-Kontext und als Kommentar in der Datei conf musiconhold zeigen. max 255 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="music_on_hold-active">
<B>Aktive -</B> Diese Option ermöglicht es Ihnen, die Wartemusik Eintrag aktiv oder inaktiv gesetzt. Inaktive wird entfernen Sie den Eintrag aus der conf-Dateien.

<BR>
<A NAME="music_on_hold-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="music_on_hold-random">
<B>Zufälliger Reihenfolge -</B> Diese Option ermöglicht es Ihnen, die Wiedergabe der Audio-Dateien in zufälliger Reihenfolge zu definieren. Wenn auf N dann die festgelegten Reihenfolge verwendet werden.

<BR>
<A NAME="music_on_hold-filename">
<B>Filename -</B> Um eine neue Audiodatei auf eine Music On Hold Eintrag hinzufügen, muss die Datei zuerst in der Audio zu speichern, dann können Sie die Datei auszuwählen und klicken Sie legt ihn in die Datei-Liste aufzunehmen. Music on Hold ist einmal pro Minute aktualisiert, wenn es zu Veränderungen vorgenommen. Alle Dateien, die nicht in der Wartemusik-Eintrag, der in der Musik präsentieren zu halten Ordner aufgeführt sind, werden gelöscht.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TTS_PROMPTSTABELLE</FONT></B><BR><BR>
<A NAME="tts_prompts-tts_id">
<BR>
<B>TTS-ID -</B> Dies ist der Kurzname eines TTS-Eintrag. Dies muss eine eindeutige Kennung. Verwenden Sie keine Leerzeichen oder sonstige Zeichen für dieses Feld. max 50 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="tts_prompts-tts_name">
<B>TTS Name -</B> Dies ist um einen beschreibenden Namen des TTS-Eintrag. Dies ist eine kurze Zusammenfassung der TTS-Definition. max 100 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="tts_prompts-active">
<B>Aktive -</B> Diese Option ermöglicht es Ihnen, die TTS-Eintrag aktiv oder inaktiv gesetzt.

<BR>
<A NAME="tts_prompts-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="tts_prompts-tts_voice">
<B>TTS-Stimme -</B> Hier können Sie die Stimme definieren, die in der TTS-Erzeugung verwendet werden. Die Standardeinstellung ist Allison-8kHz.

<BR>
<A NAME="tts_prompts-tts_text">
<B>TTS Text -</B> Dies ist der eigentliche Text To Speech Datenfeld Cepstral, die für die Erstellung der Audio-Datei gesendet wird, um dem Kunden gespielt werden. Sie können Speech Synthesis Markup Language-SSML-Einsatz in diesem Bereich, zum Beispiel, &lt;break time='1000ms'/&gt; for a 1 second break. You can also use several variables such as first name, last name and title as system variables just like you do in a Index: --A--first_name--B--. Wenn Sie statische Audiodateien, die Sie verwenden möchten, auf den Wert eines der Felder, die Sie für diese ebenfalls verwenden können mit C-und D-Tags basieren. Die Dateinamen müssen kleingeschrieben sein und sie müssen 8k 16bit PCM WAV-Dateien sein. Der Name des Feldes muss die gleiche, aber ohne die. Wav im Dateinamen sein. Zum Beispiel - C ---- A - Address3 - B ---- D - zuerst finden würde, den Wert für Address3, dann würde es versuchen, eine Audio-Datei passenden Wert, dass es in den Prompt legte finden. Hier ist eine Liste der verfügbaren Variablen: lead_id, entry_date, modify_date, status, user, vendor_lead_code, source_id, list_id, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_count, last_local_call_time, rank, owner




<BR><BR><BR><BR>

<B><FONT SIZE=3>VOICEMAILTABELLE</FONT></B><BR><BR>
<A NAME="voicemail-voicemail_id">
<BR>
<B>Voicemail-ID -</B> Dies ist die Kennung der alle Zahlen dieses Postfach. Das darf nicht sein, ein Duplikat eines vorhandenen Voicemail-ID oder die Voicemail-ID eines Telefons auf dem System, mindestens 2 Zeichen.

<BR>
<A NAME="voicemail-fullname">
<B>Name -</B> Dies ist der Name im Zusammenhang mit dieser Voicemail-Box. max 100 Zeichen, mindestens 2 Zeichen.

<BR>
<A NAME="voicemail-pass">
<B>Password -</B> Dies ist das Kennwort, das verwendet wird, um Zugriff auf die Voicemail-Box beim Wählen Nachrichten zu maximal 10 Zeichen, mindestens 2 Zeichen überprüfen zu gewinnen.

<BR>
<A NAME="voicemail-active">
<B>Aktive -</B> Diese Option ermöglicht es Ihnen, die Voicemail-Box aktiv oder inaktiv gesetzt. Ist das Kontrollkästchen aktiviert kann man nicht die Nachrichten auf sie und man kann nicht kontrollieren Einträge in ihr.

<BR>
<A NAME="voicemail-email">
<BEmail-</B> Mit dieser Einstellung können Sie die Voicemail-Nachrichten geschickt haben eine E-Mail-Konto, wenn Ihr System so eingerichtet ist, senden E-Mail. Wenn dieses Feld leer ist, dann wird keine E-Mails verschickt werden.

<BR>
<A NAME="voicemail-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="voicemail-delete_vm_after_email">
<B>Löschen Voicemail Nach E-Mail -</B> Mit dieser Einstellung können Sie die Voicemail-Nachrichten aus dem System gelöscht haben, nachdem sie per E-Mail haben gewesen. Der Standardwert ist N.

<BR>
<A NAME="voicemail-voicemail_greeting">
<B>Voicemail-Gruß -</B> Diese optionale Einstellung ermöglicht es Ihnen, eine Voicemail-Ansage Audiodatei aus dem Audio-Speicher definieren. Standardeinstellung ist leer.

<BR>
<A NAME="voicemail-voicemail_timezone">
<B>Voicemail-Zone -</B> Diese Einstellung können Sie die Zone, dass diese Voicemail-Box wird an eingestellt werden, wenn die Zeit für eine Meldung protokolliert wird eingestellt. Standard wird in den Systemeinstellungen festgelegt.

<BR>
<A NAME="voicemail-voicemail_options">
<B>Voicemail-Optionen -</B> Diese optionale Einstellung ermöglicht es Ihnen, zusätzliche Voicemail-Einstellungen zu definieren. Es wird empfohlen, dass Sie dieses Feld leer lassen, wenn Sie wissen was Sie tun.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>

	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LIST LOADER FUNCTIONALITY</FONT></B><BR><BR>
	<A NAME="list_loader">
	<BR>
	Die grundlegende Web-basierte Führung loader ist einfach gestaltet, um eine Führung Datei zu nehmen - in der Größe bis zu 8 MB - das ist entweder Tab oder Rohr begrenzt und laden Sie sie in der Liste Tabelle. Die Bleilader ermöglicht Feld wählen und TXT-Nur-Text-, CSV-Comma Separated Values ​​und XLS-Excel-Datei-Formate. Das Blei loader nicht Datenvalidierung zu tun, aber es können Sie nach Duplikaten in sich selbst zu überprüfen, innerhalb der Kampagne oder innerhalb des gesamten Systems. Also, stellen Sie sicher, dass Sie die Liste, dass diese Leitungen sind unter, so dass Sie sie verwenden können, geschaffen haben. Hier ist eine Liste der Felder in der richtigen Reihenfolge für die Blei-Dateien:
		<OL>
		<LI>Verkäufer-Leitung Code - zeigt oben im Verkäufer, den Identifikationvom GUI auffangen
		<LI>Quellenprogramm - interner Gebrauch nur für admins und DBAs
		<LI>Liste Identifikation - die Liste Zahl, die diese Leitungen obendarunter zeigen
		<LI>Phone Code - the prefix for the phone number - 1 for US, 44 for UK, 61 for AUS, etc
		<LI>Telefonnummer - muß mindestens 8 Stellen lang sein
		<LI>Titel - Titel dem Kunden - Herr Ms Mrs, usw....
		<LI>Vorname
		<LI>Mittlere Initiale
		<LI>Letzter Name
		<LI>Adresse Linie 1
		<LI>Adresse Linie 2
		<LI>Adresse Linie 3
		<LI>Stadt
		<LI>Zustand - begrenzt auf 2 Buchstaben
		<LI>Provinz
		<LI>Postcode
		<LI>Land
		<LI>Geschlecht
		<LI>Geburtsdatum
		<LI>Wechselnde Telefonnummer
		<LIEmailaddress
		<LI>Sicherheit Phrase
		<LI>Anmerkungen
		<LI>Rank
		<LI>Owner
		</OL>

	<BR>ANMERKUNGEN: Die übertreffenleitung Ladevorrichtung Funktionalitätwird durch eine Reihe Perl Indexe ermöglicht und muß eine richtigzusammengebaute /etc/astguiclient.conf Akte im Platz auf dem webserver haben. Auch ein Paarperl Module müssen für sie, um außerdemzu arbeiten - OLE-Storage_Lite-Storage_Lite undVerteilungsbogen-ParseExcel geladen werden. Sie können aufLaufzeitfehler in diesen überprüfen, indem Sie Ihre Apache error_logAkte betrachten. Auch für Doppelprüfungen gegen campaignverzeichnet, die Liste, die neue Leitungen hat, in es einzusteigenmuß im System verursacht werden, bevor Sie beginnen, die Leitungen zuladen.

	<BR>
	<A NAME="list_loader-duplicate_check">
	<BR>
	<B>Dublettenprüfung - </B>Die doppelten Optionen können Sie für doppelte Einträge überprüfen, wie Sie die Leitungen in das System zu laden. Sie können auswählen, für Duplikate nur innerhalb der gleichen Liste, nur den gleichen Kampagnenlisten oder innerhalb des gesamten Systems zu überprüfen. Wenn Sie eine Dublettenprüfung gewählt haben, können Sie auch optional die nur bestimmte Zustände, die Sie Prüfung gegen duplizieren möchten.

	<BR>
	<A NAME="list_loader-file_layout">
	<BR>
	<B>File layout - </B>Das Layout der Datei geladen werden. "Standardformat" verwendet die vordefinierten Standard-Dateiformat. "Kundenspezifische Layout" ermöglicht es dem Benutzer, um das Layout der Datei selbst zu definieren. "Benutzerdefinierte Vorlage" ist ein Hybrid aus den beiden vorherigen Optionen, die es dem Benutzer, eine benutzerdefinierte Format, das sie vorher definiert haben und mit Hilfe der benutzerdefinierten Vorlage gespeichert Maker verwenden können.

	<BR>
	<A NAME="list_loader-template_id">
	<BR>
	<B>Vorlagen-ID -</B> If the user has selected "Kundenspezifischer Plan" from the "File layout" options, then this the the template the lead loader will use.  It will also override the selected list ID with the list ID that was assigned to the selected template when it was created.



	<BR><BR><BR><BR>
	<?php
	}
?>





<B><FONT SIZE=3>TELEFON-TABELLE</FONT></B><BR><BR>
<A NAME="phones-extension">
<BR>
<B>Durchwahl -</B> Dieses Feld ist, wo Sie die Telefone Namen setzen, wie es zu Asterisk nicht einschließlich des Protokolls oder Schrägstrich am Anfang erscheint. Zum Beispiel: für das SIP-Telefon SIP/test101 die Telefon-Durchwahl würde test101 sein. Auch für IAX2 phones: IAX2/IAXphone1 @ IAXphone1 würde IAXphone1 sein. Für Zap und Dahdi angehängten channelbank oder FXS-Telefone stellen Sie sicher stellen das gesamte Kanal-Nummer ohne das Präfix: Zap/25-1 würde 25-1 sein. Noch ein Hinweis, sicherstellen, dass Sie das Protokoll mit folgendem richtig für Ihre Art des Telefons.

<BR>
<A NAME="phones-dialplan_number">
<BR>
<B>Dial Plan Zahl -</B> This field is for the number you dial to have the phone ring. This number is defined in the extensions.conf file of your Asterisk server

<BR>
<A NAME="phones-voicemail_id">
<BR>
<B>Voicemail Kasten -</B> Dieses fangen ist für den voicemailKasten auf, daß die Anzeigen für zum Benutzer dieses Telefons gehen.Wir verwenden dieses, um auf voicemail Anzeigen und auf den Benutzerzu überprüfen, zum in der Lage zuSEIN, die VOICEMAIL Taste aufastGUIclient APP zu benutzen.

<BR>
<A NAME="phones-voicemail_timezone">
<B>Voicemail-Zone -</B> Diese Einstellung können Sie die Zone, dass diese Voicemail-Box wird an eingestellt werden, wenn die Zeit für eine Meldung protokolliert wird eingestellt. Standard wird in den Systemeinstellungen festgelegt.

<BR>
<A NAME="phones-voicemail_options">
<B>Voicemail-Optionen -</B> Diese optionale Einstellung ermöglicht es Ihnen, zusätzliche Voicemail-Einstellungen zu definieren. Es wird empfohlen, dass Sie dieses Feld leer lassen, wenn Sie wissen was Sie tun.

<BR>
<A NAME="phones-outbound_cid">
<BR>
<B>Outbound CallerID -</B> Dieses fangen ist auf, wo Sie diecallerID Zahl eintragen würden, die Sie möchten, daß auf outboundAnrufe gesetzter Form der astguiclient Netz-Klient aussieht. Diesesarbeitet nicht auf RBS, non-PRI, T1/Eß.

<BR>
<A NAME="phones-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="phones-phone_ip">
<BR>
<B>Telefon-IP address -</B> Dieses fangen ist für IP addressdes Telefons auf, wenn es ein VOIP Telefon ist. Dieses istauffangen ein wahlweise freigestelltes

<BR>
<A NAME="phones-computer_ip">
<BR>
<B>Computer-IP address -</B> Dieses fangen ist für IP addressComputer des Benutzers auf. Dieses ist auffangen einwahlweise freigestelltes

<BR>
<A NAME="phones-server_ip">
<BR>
<B>Bediener IP -</B> Dieses Menü ist, wo Sie vorwählen, dem Bedienerdas Telefon auf aktiv ist.

<BR>
<A NAME="phones-login">
<BR>
<B>Agent-Bildschirm Anmelden -</B> Die Anmeldung für die Telefon-Benutzeroberfläche verwendet, um die Client-Anwendungen anmelden, wie der Agent-Bildschirm.

<BR>
<A NAME="phones-pass">
<BR>
<B>Login Passwort -</B>  Das Passwort für den Benutzer verwendet werden, um telefonisch zu den Web-basierten Client-Anwendungen anmelden. Wichtig ist, ist dies das Passwort nur für den Agenten, Web-Interface Telefon anmelden, um die sip.conf oder iax.conf Kennwort ein, oder geheim zu ändern, für dieses Telefon Gerät, das Sie, um die Registrierung Passwort in das nächste Feld zu ändern müssen.

<BR>
<A NAME="phones-conf_secret">
<BR>
<B>Anmeldung Passwort -</B> Das ist das Geheimnis, oder das Passwort für das Telefon in der IAX oder SIP-auto-generated conf-Datei für dieses Telefon. Der Grenzwert beträgt 20 alphanumerischen Zeichen Bindestrich und Unterstrich akzeptiert. Default is test. Früher nannte conf-Datei Geheimnis. Eine starke Registrierung Passwort sollte mindestens 8 Zeichen lang sein und müssen in Kleinbuchstaben und Großbuchstaben sowie mindestens eine Zahl.

<BR>
<A NAME="phones-is_webphone">
<BR>
<B>Set As Webphone -</B>  Wenn diese Option auf Y wird versuchen, ein Web-basiertes Handy zu laden, wenn der Agent loggt sich in ihren Agenten-Bildschirm. Die Standardeinstellung ist N. Die Y_API_LAUNCH Option kann mit dem Agenten-API verwendet werden, um die webphone in einem separaten Fenster zu öffnen oder Iframe.

<BR>
<A NAME="phones-webphone_dialpad">
<BR>
<B>Webphone Dialpad -</B>  Diese Einstellung können Sie aktivieren oder deaktivieren Sie das Tastenfeld für diese webphone. Standard ist für Y aktiviert. ROCKER kann der Benutzer zum Anzeigen und Ausblenden der Wähltasten durch Anklicken eines Links. Diese Funktion ist nicht bei allen Versionen zur Verfügung webphone. TOGGLE_OFF ausfällt, um nicht das Tastenfeld beim ersten Laden, sondern ermöglicht es dem Benutzer, die Wähltasten, indem Sie zeigen, auf dem Tastenfeld Link.

<BR>
<A NAME="phones-webphone_auto_answer">
<BR>
<B>Webphone Auto-Antwort -</B>  Mit dieser Einstellung kann die Web-Telefon eingerichtet werden, dass Anrufe automatisch, die kommen, indem es auf Y, oder um Anrufe Ring indem Sie sie auf N. Standard haben, ist Y.

<BR>
<A NAME="phones-use_external_server_ip">
<BR>
<B>Mit externen Bediener IP -</B>  Bei Verwendung als Web-Telefon, können Sie dies auf Y gesetzt, um die Server zu verwenden, um externe IP statt des Server-IP registrieren. Standard ist leer.

<BR>
<A NAME="phones-status">
<BR>
<B>Status -</B> Der Status des Telefons im System, die AKTIVEN und ADMINdürfen, damit GUI Klienten arbeiten. Admin erlaubt Zugang zu dieseradministrativen Web site. Alle weiteren Status erlauben nicht GUI oderAdmin Netzzugang.

<BR>
<A NAME="phones-active">
<BR>
<B>Aktives Konto -</B> Ob das Telefon aktiv ist, es in die Liste im GUIKlienten einzusetzen.

<BR>
<A NAME="phones-phone_type">
<BR>
<B>Telefon-Art -</B> Lediglich für administrative Anmerkungen.

<BR>
<A NAME="phones-fullname">
<BR>
<B>Voller Name -</B> Verwendet durch das GUIclient in der Liste deraktiven Telefone.

<BR>
<A NAME="phones-company">
<BR>
<B>Firma -</B> Lediglich für administrative Anmerkungen.

<BR>
<A NAME="phones-email">
<BR>
<B>Telefon E-Mail - </B> Die E-Mail-Adresse im Zusammenhang mit diesem Telefon-Eintrag. Dies ist für die Mailbox-Einstellungen.

<BR>
<A NAME="phones-delete_vm_after_email">
<B>Löschen Voicemail Nach E-Mail -</B> Mit dieser Einstellung können Sie die Voicemail-Nachrichten aus dem System gelöscht haben, nachdem sie per E-Mail haben gewesen. Der Standardwert ist N.

<BR>
<A NAME="phones-voicemail_greeting">
<B>Voicemail-Gruß -</B> Diese optionale Einstellung ermöglicht es Ihnen, eine Voicemail-Ansage Audiodatei aus dem Audio-Speicher definieren. Standardeinstellung ist leer.

<BR>
<A NAME="phones-voicemail_instructions">
<B>Voicemail-Anleitung -</B> Diese Einstellung können Sie festlegen, ob die Voicemail-Anweisungen werden nach der Voicemail-Ansage spielen, wenn ein Anruf auf dem Agent-Erweiterung und die Zeiten aus, um Voicemail. Standardwert ist Y.

<BR>
<A NAME="phones-picture">
<BR>
<B>Abbildung -</B> Nicht schon eingeführt.

<BR>
<A NAME="phones-messages">
<BR>
<B>Neue Anzeigen -</B> Zahl der neuen voicemail Anzeigen für diesesTelefon auf dem Sternchenbediener.

<BR>
<A NAME="phones-old_messages">
<BR>
<B>Alte Anzeigen -</B> Zahl der alten voicemail Anzeigen für diesesTelefon auf dem Sternchenbediener.

<BR>
<A NAME="phones-protocol">
<BR>
<B>Klient Protokoll -</B> Das Protokoll, das das Telefon verwendet, anden Sternchenbediener anzuschließen: Sip, IAX2, Zap. Auch, es gibtexternal für Remotevorwahlknopfzahlen oder GeschwindigkeitVorwahlknopfzahlen, die Sie als Telefone verzeichnen möchten.

<BR>
<A NAME="phones-local_gmt">
<BR>
<B>Lokales GMT -</B> Der Unterschied zu Greenwich Mean Time oder Zulu-Zeit, wo das Telefon befindet. NICHT ANPASSEN auf die Sommerzeit. Dies wird durch die Kampagne verwendet werden, um die Systemzeit und Kunden Zeit genau anzuzeigen, wie genau protokollieren, wenn Ereignisse eintreten.

<BR>
<A NAME="phones-phone_ring_timeout">
<BR>
<B>Telefon Ring Timeout -</B> Dies ist die Zeit, in Sekunden, die das Telefon in der dialplan, bevor der Anruf an die Voicemail-Ring wird. Der Standardwert ist 60 Sekunden.

<BR>
<A NAME="phones-on_hook_agent">
<BR>
<B>On-Haken Agent Login -</B> Diese Option ist nur für eingehende Anrufe gehen an einen Agenten angemeldet mit diesem Telefon verwendet. Diese Funktion ruft den Agenten und wird nicht sendet dem Kunden an den Agenten Sitzung, bis die Leitung beantwortet wird. Standard ist für behinderte N.

<BR>
<A NAME="phones-ASTmgrUSERNAME">
<BR>
<B>Manager-LOGON -</B> Dieses ist der LOGON, dem die GUI Klienten fürdieses Telefon pflegen, die Datenbank zugänglich zu machen, in derdie Bedienerdaten liegen.

<BR>
<A NAME="phones-ASTmgrSECRET">
<BR>
<B>Manager-Geheimnis -</B> Dieses ist das Kennwort, dem die GUI Klientenfür dieses Telefon pflegen, die Datenbank zugänglich zu machen, inder die Bedienerdaten liegen.

<BR>
<A NAME="phones-login_user">
<BR>
<B>Default User-Agent -</B> Dies ist auf einen Standardwert im Benutzerfeld Mittel legen, wenn dieses Telefon Benutzer die Client-Anwendung wird geöffnet. Leer lassen, ohne Nutzer.

<BR>
<A NAME="phones-login_pass">
<BR>
<B>Agent-Standard-Pass -</B> Dies ist auf einen Standardwert in das Passwort-Feld Mittel legen, wenn dieses Telefon Benutzer die Client-Anwendung wird geöffnet. Leer lassen für keine Pass.

<BR>
<A NAME="phones-login_campaign">
<BR>
<B>Agent-Standard-Kampagne -</B> Dies ist auf einen Standardwert in der Kampagne Agentin Bildschirm zu platzieren, wenn dieses Telefon Benutzer die Client-Anwendung wird geöffnet. Leer lassen für keine Kampagne.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Park Exten -</B> Dieses ist die Rückstellung Parkenverlängerung fürdie Klient apps. Überprüfen Sie, daß ein unterschiedliches manarbeitet, bevor Sie dieses ändern.

<BR>
<A NAME="phones-conf_on_extension">
<BR>
<B>Conf Exten -</B> This is the default Conference park extension for the client apps. Verify that a different one works before you change this.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Agent-Park Exten -</B> Dies ist der Standard Parkplatz-Erweiterung für Client-App. Überprüfen Sie, ob ein anderer funktioniert, bevor Sie dies ändern.

<BR>
<A NAME="phones-park_on_filename">
<BR>
<B>Agent-Park Datei -</B> Dies ist das Standardmittel Bildschirm Parkerweiterung Dateinamen für die Client-Anwendungen. Überprüfen Sie, ob ein anderer funktioniert, bevor Sie dies ändern. auf 10 Zeichen begrenzt.

<BR>
<A NAME="phones-monitor_prefix">
<BR>
<B>Überwachen Sie Präfix -</B> This is the dial plan prefix for monitoring of Zap channels automatically within the astGUIclient app. Only change according to the extensions.conf ZapBarge extensions records.

<BR>
<A NAME="phones-recording_exten">
<BR>
<B>Aufnahme Exten -</B> Dieses ist die dial plan Verlängerung für dieAufnahmeverlängerung, die verwendet wird, um in meetme Konferenzen zufallen, um sie zu notieren. Es dauert normalerweise bis zu einerStunde, wenn es nicht gestoppt wird, überprüft mit extensions.confAkte, bevor es ändert.

<BR>
<A NAME="phones-voicemail_exten">
<BR>
<B>VMAIL Hauptexten -</B> Dieses ist die dial plan Verlängerung, diegeht, Ihr voicemail. zu überprüfen, überprüfen mit extensions.confAkte, bevor es ändert.

<BR>
<A NAME="phones-voicemail_dump_exten">
<BR>
<B>VMAIL Dump Exten -</B> Dieses ist das dial plan Präfix, das verwendetwird, um Anrufe direkt zum voicemail eines Benutzers von einemPhasenanruf in der astGUIclient APP zu schicken, überprüfen mitextensions.conf Akte, bevor es ändert.

<BR>
<A NAME="phones-voicemail_dump_exten_no_inst">
<BR>
<B>VMAIL Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="phones-ext_context">
<BR>
<B>Exten Kontext -</B> Dies ist der Wählplan Zusammenhang, dass die Agenten-Bildschirm, nutzt in erster Linie. Es wird angenommen, dass alle Zahlen von den Client-Apps gewählt sind mit diesem Kontext, so ist es eine gute Idee, um sicherzustellen, dass dies die breiten Kontext möglich ist. Verifizierung mit extensions.conf Datei vor dem Ändern. Standardmäßig ist.

<BR>
<A NAME="phones-phone_context">
<BR>
<B>Telefon Kontext -</B> Dies ist der Kontext, der Wahlplan dieses Telefon zu verwenden, um Dial-Out. Wenn Sie mit einem Call-Center und Sie nicht möchten, dass Ihre Mitarbeiter in der Lage, außerhalb des Mittels Bildschirm applicaiton beispiels hinauszuwählen, dann würden Sie dieses Feld, um einen Dialplan Zusammenhang, der nicht existiert gesetzt, so etwas wie Agenten-nodial. Standardmäßig ist.

<BR>
<A NAME="phones-codecs_list">
<BR>
<B>Erlaubt Codecs -</B> Sie können eine durch Kommas getrennte Liste von Codecs als die Standard-Codecs für dieses Telefon eingestellt werden. Optionen für Codecs beinhalten ulaw, alaw, GSM, G729, Speex, G722, G723, G726, ilbc, ... Einige dieser Codecs ist möglicherweise nicht auf Ihrem System zur Verfügung, wie G729 oder G726. Wenn das Feld leer ist, dann werden die System-Standard-Codecs oder das Telefon über diesem Eintrag wird man für den zulässigen Codecs verwendet werden. Standard ist leer.

<BR>
<A NAME="phones-codecs_with_template">
<BR>
<B>Erlaubt Codecs mit Schablone-</B> Wenn diese Option auf 1 umfassen auch die Codecs oben definiert, wenn eine conf-Datei Vorlage verwendet wird. Standard ist 0.

<BR>
<A NAME="phones-dtmf_send_extension">
<BR>
<B>DTMF senden Führung -</B> Dieses ist die Führung Zeichenkette, diebenutzt wird, um DTMF Töne in meetme Konferenzen von den Klient appszu senden. Überprüfen Sie, daß und Kontext mit der extensions.confAkte exten.

<BR>
<A NAME="phones-call_out_number_group">
<BR>
<B>Outbound Anruf-Gruppe -</B> Dieses ist die Kanalgruppe, daß outboundAnrufe von diesem Telefon aus gesetzt werden. Es gibt Programme einesPaares in den Klient apps, die dieses verwenden. Für Zap Führungen,die Sie etwas wie Zap/g2 verwenden möchten, denn Stämme IAX2 Siedas volle IAX Präfix wie IAX2/VICItest1:secret@10.10.10.15:4569würden verwenden wollen. Überprüfen Sie die Stämme mit derextensions.conf Akte, es ist normalerweise, was Sie als die globaleVariable des STAMMES an der Oberseite der Akte definiert haben.

<BR>
<A NAME="phones-client_browser">
<BR>
<B>Datenbanksuchroutine-Position -</B> Dieses ist auf nur UNIX/LINUXKlienten, der absolute Weg zu Mozilla anwendbar, oder FirefoxDatenbanksuchroutine auf der Maschine überprüfen dieses, indem siemanuell es ausstößt.

<BR>
<A NAME="phones-install_directory">
<BR>
<B>Bringen Sie Verzeichnis An -</B> Nicht mehr verwendet.

<BR>
<A NAME="phones-local_web_callerID_URL">
<BR>
<B>CallerID URL -</B> Dieses ist die Netzadresse der Seite, die benutztwird, um kundenspezifische callerID Nachschlagen zu tun, Rückstellungprüfen, dieadresse ist:http://astguiclient.sf.net/test_callerid_output.php

<BR>
<A NAME="phones-agent_web_URL">
<BR>
<B>Agent-Standard-URL -</B> Dies ist die Web-Adresse des Agenten verwendet, um benutzerdefinierte Netz-Form-Abfragen Seite. Standardtests Adresse wird in der Datenbank-Schema definiert.

<BR>
<A NAME="phones-AGI_call_logging_enabled">
<BR>
<B>Anruf-Protokollierung -</B> This is set to true if the call_log step is in place in the extensions.conf file for all outbound and hang up 'h' extensions to log all calls. This should always be 1 because it is manditory for many of the system features to work properly.

<BR>
<A NAME="phones-user_switching_enabled">
<BR>
<B>Benutzer-Schaltung -</B> Set to true to allow user to switch to another user account. NOTE: If user switches they can initiate recording on the new user's phone conversation

<BR>
<A NAME="phones-conferencing_enabled">
<BR>
<B>Conferencing -</B> Stellen Sie ein, um auszurichten, um Benutzer zuerlauben, Konferenzanrufe mit bis zu sechs externen Linien zubeginnen.

<BR>
<A NAME="phones-admin_hangup_enabled">
<BR>
<B>Admin Hängezustand -</B> um Benutzer zu erlauben auszurichten Satz,zum Hängezustand irgendeine Linie am Willen durch astGUIclient in derLage zuSEIN. Gute Idee, diesem für Admin Benutzer nur zuermöglichen.

<BR>
<A NAME="phones-admin_hijack_enabled">
<BR>
<B>Admin Straßenräuber -</B> Stellen Sie ein, um auszurichten, umBenutzer zu erlauben, in der Lage zuSEIN, zu ihrer Verlängerung jedemögliche Linie zu ergreifen und umzuadressieren am Willen durchastGUIclient. Gute Idee, diesem für Admin Benutzer nur zuermöglichen. Aber ist für Manager sehr nützlich.

<BR>
<A NAME="phones-admin_monitor_enabled">
<BR>
<B>Admin Monitor -</B> Stellen Sie ein, um auszurichten, um Benutzer zuerlauben, in der Lage zuSEIN, zu ihrer Verlängerung jede möglicheLinie zu ergreifen und umzuadressieren am Willen durch astGUIclient.Gute Idee, diesem für Admin Benutzer nur zu ermöglichen. Aber istfür Manager und als Training Werkzeug sehr nützlich.

<BR>
<A NAME="phones-call_parking_enabled">
<BR>
<B>Parkschaltung -</B> um Benutzer zu erlauben auszurichten der Satz, inder Lage zuSEIN zu parken ersucht um astGUIclient Einfluß, von jedemanderen astGUIclient Benutzer auf dem System aufgehoben zu werden.Anrufe bleiben auf Einfluß für bis zu einen halbe Stunde dannHängezustand. Normalerweise ermöglicht für alle.

<BR>
<A NAME="phones-updater_check_enabled">
<BR>
<B>Updater Überprüfung -</B> Stellen Sie ein, um auszurichten, um einepopup Warnung anzuzeigen, die die updater Zeit nicht in 20 Sekundengeändert hat. Nützlich für Admin Benutzer.

<BR>
<A NAME="phones-AFLogging_enabled">
<BR>
<B>Af Protokollierung -</B> Stellen Sie ein, um auszurichten, um vieleTätigkeiten des astGUIclient Verbrauches in einer Textakte auf demComputer des Benutzers zu protokollieren.

<BR>
<A NAME="phones-QUEUE_ACTION_enabled">
<BR>
<B>Warteschlange Ermöglicht -</B> Auf true gesetzt zu haben, Client-Anwendungen verwenden Sie den Asterisk Zentral Queue-System. Für das System zu arbeiten erforderlich, und für alle Handys empfohlen.

<BR>
<A NAME="phones-CallerID_popup_enabled">
<BR>
<B>CallerID Popup -</B> Stellen Sie ein, um auszurichten, um die Zahlenzuzulassen, die in der extensions.conf Akte definiert werden, umCallerID popup Schirme zu schicken den astGUIclient Benutzern.

<BR>
<A NAME="phones-voicemail_button_enabled">
<BR>
<B>VMail Taste -</B> Stellen Sie ein, um auszurichten, um die VOICEMAILTaste anzuzeigen und die Anzeigen zählen Anzeige auf astGUIclient.

<BR>
<A NAME="phones-enable_fast_refresh">
<BR>
<B>Schnell erneuern Sie -</B> Stellen Sie ein, um auszurichten, um einerneuen Rate von zu ermöglichen erneuern von den Anrufinformationenfür das astGUIclient. Rückstellung arbeitsunfähige Rate ist 1000zweite des Ms.1. Kann Anlagen-Belastung erhöhen, wenn Sie diese Zahlsenken.

<BR>
<A NAME="phones-fast_refresh_rate">
<BR>
<B>Schnell erneuern Sie Rate -</B> in den Millisekunden. Nur verwendet,wenn schnell, erneuern Sie wird ermöglicht. Rückstellungarbeitsunfähige Rate ist 1000 zweite des Ms.1. Kann Anlagen-Belastungerhöhen, wenn Sie diese Zahl senken.

<BR>
<A NAME="phones-enable_persistant_mysql">
<BR>
<B>Persistant MySQL -</B> Wenn er ermöglicht wird, bleibt derastGUIclient Anschluß angeschlossen, anstatt, jede Sekundeanzuschließen. Nützlich, wenn Sie ein schnelles die eingestellteRate erneuern lassen. Sie erhöht die Zahl Anschlüssen auf IhrerMySQL Maschine.

<BR>
<A NAME="phones-auto_dial_next_number">
<BR>
<B>Selbstvorwahlknopf-Folgende Zahl -</B> Wenn aktiviert der Agent-Bildschirm wird die nächste Nummer auf der Liste automatisch nach Anordnung einer Rufnummer zu wählen, wenn sie nicht MANAGER Wahl auf dem Bildschirm Disposition PAUSE ausgewählt.

<BR>
<A NAME="phones-VDstop_rec_after_each_call">
<BR>
<B>Stoppen Sie Rec nach jedem Anruf -</B> Wenn aktiviert der Agent-Bildschirm wird die Aufnahme stoppen, was los ist, nach jedem Anruf wurde dispositioned. Nützlich, wenn Sie tun eine Menge der Aufzeichnung oder Sie verwenden ein Web-Formular, um die Aufnahme auslösen. 

<BR>
<A NAME="phones-enable_sipsak_messages">
<BR>
<B>Enable SIPSAK Messages -</B> Wenn aktiviert, wird der Server-Nachrichten an den SIP-Telefon senden, um auf dem Handy Anzeige angezeigt, wenn in den Agenten-Weboberfläche angemeldet. Feature funktioniert nur mit SIP-Telefonen und erfordert sipsak Anwendung auf dem Web-Server installiert werden. Standard ist 0.

<BR>
<A NAME="phones-DBX_server">
<BR>
<B>DBX Bediener -</B> Der MySQL Datenbankbediener, den dieser Benutzeranschließen sollte an.

<BR>
<A NAME="phones-DBX_database">
<BR>
<B>DBX Datenbank -</B> Die MySQL Datenbank, die dieser Benutzeranschließen sollte an. Rückstellung ist Sternchen.

<BR>
<A NAME="phones-DBX_user">
<BR>
<B>DBX Benutzer -</B> Der MySQL Benutzer-LOGON, den dieser Benutzer beimAnschließen verwenden sollte. Rückstellung ist cron.

<BR>
<A NAME="phones-DBX_pass">
<BR>
<B>DBX Durchlauf -</B> Das MySQL Benutzerkennwort, das dieser Benutzerbeim Anschließen verwenden sollte. Rückstellung ist 1234.

<BR>
<A NAME="phones-DBX_port">
<BR>
<B>DBX Tor -</B> Das MySQL TCP Tor, das dieser Benutzer beim Anschließenbenutzen sollte. Rückstellung ist 3306.

<BR>
<A NAME="phones-DBY_server">
<BR>
<B>DBY Bediener -</B> Der MySQL Datenbankbediener, den dieser Benutzeranschließen sollte an. Zweitensserver, nicht z.Z. verwendet.

<BR>
<A NAME="phones-DBY_database">
<BR>
<B>DBY Datenbank -</B> Die MySQL Datenbank, die dieser Benutzeranschließen sollte an. Rückstellung ist Sternchen. Zweitensserver, nicht z.Z. verwendet.

<BR>
<A NAME="phones-DBY_user">
<BR>
<B>DBY Benutzer -</B> Der MySQL Benutzer-LOGON, den dieser Benutzer beimAnschließen verwenden sollte. Rückstellung ist cron. Zweitensserver, nicht z.Z. verwendet.

<BR>
<A NAME="phones-DBY_pass">
<BR>
<B>DBY Durchlauf -</B> Das MySQL Benutzerkennwort, das dieser Benutzerbeim Anschließen verwenden sollte. Rückstellung ist 1234. Zweitensserver, nicht z.Z. verwendet.

<BR>
<A NAME="phones-DBY_port">
<BR>
<B>DBY Tor -</B> Das MySQL TCP Tor, das dieser Benutzer beim Anschließenbenutzen sollte. Rückstellung ist 3306. Zweitensserver, nicht z.Z. verwendet.

<BR>
<A NAME="phones-alias_id">
<BR>
<B>Alias-ID - </B> Die ID des Alias verwendet, um für Telefon-Lastenausgleich Logins. keine Leerzeichen oder andere Sonderzeichen erlaubt. Muss zwischen 2 und 20 Zeichen lang sein.

<BR>
<A NAME="phones-alias_name">
<BR>
<B>Alias-Name - </B> Der Name für die Bezeichnung eines Handys Alias, muss zwischen 2 und 50 Zeichen lang sein.

<BR>
<A NAME="phones-logins_list">
<BR>
<B>Telefone Logins List - </B> Das Komma getrennt Liste der Telefonnummern Logins verwendet, wenn ein Agent meldet sich mit Telefon Lastenausgleich Logins. Der Agent-Anwendung finden Sie die AktivServer mit den wenigsten Makler eingeloggt ist und ein Anruf aus, dass der Server-Agent bei der Anmeldung.

<BR>
<A NAME="phones-template_id">
<BR>
<B>Vorlagen-ID - </B> Das ist die conf Datei template-ID, die dieses Telefon-Eintrag wird für die Asterisk-Einstellungen. Standard ist - KEINE --.

<BR>
<A NAME="phones-conf_override">
<BR>
<B>Conf Override Settings -</B> If populated, and the Vorlagen-ID is set to --NONE-- then the contents of this field are used as the conf file entries for this phone. generate conf files for this phones server must be set to Y for this to work. This field should NOT contain the [extension] line, that will be automatically generated.

<BR>
<A NAME="phones-group_alias_id">
<BR>
<B>Alias-ID-Gruppe -</B> Die ID der Gruppe, alias der Agenten auf gewählte Anrufe vom Agenten-Schnittstelle mit unterschiedlichen Anrufer-IDs. keine Leerzeichen oder Sonderzeichen erlaubt. Muss zwischen 2 und 20 Zeichen lang sein.

<BR>
<A NAME="phones-group_alias_name">
<BR>
<B>Alias-Name-Fraktion - </B> Der Name für die Bezeichnung einer Gruppe Alias, muss zwischen 2 und 50 Zeichen lang sein.

<BR>
<A NAME="phones-caller_id_number">
<BR>
<B>Anrufer-ID-Nummer - </B> Die Caller-ID-Nummer, die in dieser Gruppe Alias. Muss nur Ziffern.

<BR>
<A NAME="phones-caller_id_name">
<BR>
<B>Anrufer-ID Name - </B> Die Anrufer-ID-Namen ein, können mit dieser Gruppe Alias. Soweit wir wissen, wird dies nur funktionieren, in Kanada über den PRI-Leitungen und mit einer Schleife IAX Stamm durch Asterisk.




<BR><BR><BR><BR>

<B><FONT SIZE=3>BEDIENER-TABELLE</FONT></B><BR><BR>
<A NAME="servers-server_id">
<BR>
<B>Bediener Identifikation -</B> Dieses fangen ist, wohin Sieden Sternchenbedienernamen setzen, doesnt müssen ein amtliches GebietUnterseeboot, gerade ein Spitzname sein auf, um den Bediener zu denAdmin Benutzern zu kennzeichnen.

<BR>
<A NAME="servers-server_description">
<BR>
<B>Bediener-Beschreibung -</B> auffangen, wo Sie eine kleine Phraseverwenden, um den Sternchenbediener zu beschreiben.

<BR>
<A NAME="servers-server_ip">
<BR>
<B>Bediener-IP address -</B> auffangen, wo Sie das Netz-IP addressdes Sternchenbedieners setzten.

<BR>
<A NAME="servers-active">
<BR>
<B>Aktiv -</B> Stellen Sie ein, ob der Sternchenbediener aktiv oderunaktiviert ist.

<BR>
<A NAME="servers-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="servers-sysload">
<BR>
<B>System Load -</B> Diese beiden Statistiken zeigen, die loadavg ein System mal 100 und die CPU-Auslastung Anteil des Bediener und wird jede Minute aktualisiert. Die loadavg sollte im Durchschnitt unter 100 multipliziert mit der Anzahl der CPU-Kernen Ihr System hat, für eine optimale Leistung zu erzielen. Die CPU-Auslastung Prozentsatz sollte Aufenthalt unter 50 für eine optimale Leistung zu erzielen.

<BR>
<A NAME="servers-channels_total">
<BR>
<B>Live-Channels -</B> Dieses Feld zeigt die aktuelle Anzahl der Asterisk-Kanäle, die sich live über das System jetzt. Es ist wichtig zu beachten, dass die Anzahl der Kanäle Asterisk ist in der Regel viel höher als die tatsächliche Zahl der Anrufe auf einem System. Dieses Feld wird einmal pro Minute aktualisiert.

<BR>
<A NAME="servers-disk_usage">
<BR>
<B>Disk Usage -</B> Dieses Feld wird die Nutzung der Festplatte für jede Partition auf diesem Server. Dieses Feld wird einmal pro Minute aktualisiert.

<BR>
<A NAME="servers-system_uptime">
<BR>
<B>System-Uptime -</B> Dieses Feld wird die Systemverfügbarkeit von diesem Server zu zeigen. Dieses Feld wird nur aktualisiert, wenn konfiguriert, so zu tun von Ihrem Administrator.

<BR>
<A NAME="servers-asterisk_version">
<BR>
<B>Sternchen-Version -</B> Stellen Sie die Version des Sternchens ein,die Sie auf diesen Bediener angebracht haben. Beispiele: ' 1.2 ', '1.0.8 ', ' 1.0.7 ', ' CVS_HEAD ', ' WIRKLICH ALTES ', usw.... Dieseswird verwendet, weil Versionen 1.0.8 und 1.0.9 eine andere Methode desBeschäftigens Local/ Führungen, eine Wanze, die in CVS v1.0geregelt worden ist, und Notwendigkeit, anders als behandelt zu werdenhaben, wenn die Behandlung ihres Local/ lenkt. Auch gegenwärtigesCVS_HEAD und der 1.2 Freigabebaumgebrauch unterschiedlicher Managerund Befehl gaben aus, also muß es anders als außerdem behandeltwerden.

<BR>
<A NAME="servers-max_trunks">
<BR>
<B>Max Trunks -</B> Dieses Feld wird die maximale Anzahl der Zeilen, die die Auto-Dialer wird versuchen, auf diesem Server aufrufen zu bestimmen. Wenn Sie zwei volle PRI T1s widmen, um Outbound-Dialing auf einem Server wollen, dann würde dies auf 46 gesetzt. Alle eingehenden Anrufe oder manuelle Wahl werden gegen diese insgesamt als gut gewertet. Standard ist 96.

<BR>
<A NAME="servers-outbound_calls_per_second">
<BR>
<B>Max Calls pro Sekunde -</B> Diese Einstellung bestimmt die maximale Anzahl von Anrufen, die sich durch den Ausgang Auto-Einwahl-Script auf diesem Server pro Sekunde. Muss zwischen 1 und 100. Der Standardwert ist 20.

<BR>
<A NAME="servers-telnet_host">
<BR>
<B>Telnet Wirt -</B> Dieses ist die Adresse oder der Name desSternchenbedieners und ist, wie die Manageranwendungen an ihnanschließen von, wo sie laufen. Wenn sie auf den Sternchenbedienerlaufen, dann ist die Rückstellung von ' localhost ' fein.

<BR>
<A NAME="servers-telnet_port">
<BR>
<B>Telnet Tor -</B> Dieses ist das Tor des SternchenbedienerManageranschlußes und ist, wie die Manageranwendungen an ihnanschließen von, wo sie laufen. Die Rückstellung von ' 5038 ' istfein für einen Standard anbringen.

<BR>
<A NAME="servers-ASTmgrUSERNAME">
<BR>
<B>Manager-Benutzer -</B> Das username oder der LOGON verwendeten, an denSternchenbedienermanager genericly anzuschließen. Rückstellung ist 'cron '

<BR>
<A NAME="servers-ASTmgrSECRET">
<BR>
<B>Manager-Geheimnis -</B> Das Geheimnis oder das Kennwort verwendeten,an den Sternchenbedienermanager genericly anzuschließen.Rückstellung ist ' 1234 '

<BR>
<A NAME="servers-ASTmgrUSERNAMEupdate">
<BR>
<B>Manager-Update-Benutzer -</B> Das username oder der LOGON verwendeten,an den Sternchenbedienermanager anzuschließen, der für dieUpdateindexe optimiert wurde. Fallen Sie ist ' updatecron ' undannimmt das gleiche Geheimnis wie der generische Benutzer zurück.

<BR>
<A NAME="servers-ASTmgrUSERNAMElisten">
<BR>
<B>Manager hören Benutzer -</B> Das username oder der LOGON verwendeten,an den Sternchenbedienermanager anzuschließen, der für Indexeoptimiert wurde, die nur auf Ausgang hören. Fallen Sie ist 'listencron ' und annimmt das gleiche Geheimnis wie der generischeBenutzer zurück.

<BR>
<A NAME="servers-ASTmgrUSERNAMEsend">
<BR>
<B>Manager senden Benutzer -</B> Das username oder der LOGON verwendeten,an den Sternchenbedienermanager anzuschließen, der für Indexeoptimiert wurde, die dem Manager nur Tätigkeiten schicken. Fallen Sieist ' sendcron ' und annimmt das gleiche Geheimnis wie der generischeBenutzer zurück.

<BR>
<A NAME="servers-conf_secret">
<BR>
<B>Conf-Datei Secret -</B> Das ist das Geheimnis, oder das Passwort für den Server in die IAX automatisch generierte conf für diesen Server auf anderen Servern benötigt. Der Grenzwert beträgt 20 alphanumerischen Zeichen Bindestrich und Unterstrich akzeptiert. Default is test. Eine starke conf-Datei Geheimnis sollte mindestens 8 Zeichen lang sein und müssen in Kleinbuchstaben und Großbuchstaben sowie mindestens eine Zahl.

<BR>
<A NAME="servers-local_gmt">
<BR>
<B>Bediener GMT versetzt -</B> Der Unterschied bezüglich der Stunden vonder GMT Zeit eingestellt nicht auf Tageslicht-Sparung-Zeit desBedieners. Rückstellung ist ' - 5 '

<BR>
<A NAME="servers-voicemail_dump_exten">
<BR>
<B>VMail Dump Exten -</B> Das Verlängerung Präfix verwendet auf diesemBediener, um Anrufe direkt durch agc zu einem spezifischen voicemailKasten zu schicken. Rückstellung ist ' 85026666666666 '

<BR>
<A NAME="servers-voicemail_dump_exten_no_inst">
<BR>
<B>VMAIL Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="servers-answer_transfer_agent">
<BR>
<B>Kurzwahl Erweiterung -</B> Die Standarderweiterung, wenn keiner in der Kampagne, um Anrufe an für die Autowahl senden vorhanden ist. Standard ist '8365'

<BR>
<A NAME="servers-ext_context">
<BR>
<B>Rückstellung Kontext -</B> Der Rückstellung dial plan Kontextverwendet für Indexe, die für diesen Bediener funktionieren.Rückstellung ist ' Rückstellung '

<BR>
<A NAME="servers-sys_perf_log">
<BR>
<B>System Leistung -</B> die Einstellung dieser Wahl auf Y ermöglicht derProtokollierung von System Leistung Notfall für die Bedienermaschineeinschließlich Anlagen-Belastung, System Prozesse undSternchenführungen im Gebrauch. Rückstellung ist N.

<BR>
<A NAME="servers-vd_server_logs">
<BR>
<B>Bediener-Maschinenbordbücher -</B> Wenn diese Option auf Y wird die Protokollierung aller Systembezogenen Skripten, ihre Text-Log-Dateien zu ermöglichen. Wird dies auf N wird aufhören zu schreiben Protokolle, um Dateien für diese Prozesse, auch der Bildschirm Protokollierung der Stern wird deaktiviert, wenn dieser auf N gesetzt, wenn Asterisk gestartet werden. Standardwert ist Y.

<BR>
<A NAME="servers-agi_output">
<BR>
<B>AGI Ausgang -</B> Wenn diese Option auf NONE ausgegeben von allen systembezogenen AGI-Skripten zu deaktivieren. Wird dies auf STDERR die AGI Ausgabe auf dem Asterisk-CLI senden. Wird dies auf Datei wird die Ausgabe in eine Datei im Verzeichnis logs senden. Wird dies auf beide Ausgabe sowohl in die Asterisk CLI und eine Log-Datei zu senden. Standard ist FILE.

<BR>
<A NAME="servers-balance_active">
<BR>
<B>Balancieren Dialing -</B> Setzen Sie dieses Feld auf Y wird damit der Server Balance Anrufe für Kampagnen im System zu platzieren, so dass die definierte Wahl Ebene erfüllt werden können, auch wenn es keine angemeldeten Agenten in dieser Kampagne auf diesem Server. Standardwert ist N.

<BR>
<A NAME="servers-balance_rank">
<BR>
<B>Bilanz Rang -</B> In diesem Feld können Sie die Reihenfolge, in der dieser Server ist für das Gleichgewicht Einwahl verwendet werden, wenn Gleichgewicht Einwahl aktivieren gesetzt ist. Der Server mit dem höchsten Rang wird zuerst zu platzieren Balance füllen Anrufe verwendet. Der Standardwert ist 0.

<BR>
<A NAME="servers-balance_trunks_offlimits">
<BR>
<B>Bilanz offlimits -</B> Diese Einstellung definiert die Anzahl der Stämme, nicht zulassen, die Balance Wahlprozesse zu bedienen. Zum Beispiel, wenn Sie 40 max Stämme und Balance offlimits auf 10 gesetzt haben, können Sie nur zu 30 Amtsleitungen für die Balance Wahl nutzen. Standard ist 0 .

<BR>
<A NAME="servers-recording_web_link">
<BR>
<B>Aufnahme Web-Link - </B> Diese Einstellung erlaubt es Ihnen, sich über die Standard in der Anzeige der Aufnahme-Link im Admin-Web-Seiten. Der Standardwert ist SERVER_IP.

<BR>
<A NAME="servers-alt_server_ip">
<BR>
<B>Alternate Recording Bediener IP - </B> Diese Einstellung können Sie eine Server-IP oder andere Rechner-Namen ein, kann anstelle der server_ip in die Links zu den Aufnahmen in der Admin-Web-Seiten. Der Standardwert ist leer.

<BR>
<A NAME="servers-external_server_ip">
<BR>
<B>Externe Bediener IP -</B> Diese Einstellung ist, wo Sie eine Server-IP oder andere Maschine Namen, der an Stelle des server_ip verwendet werden können, wenn ein webphone im Agent-Interface stellen können. Damit dies funktioniert, müssen Sie auch die Telefone Eintrag auf den externen Bediener IP zu verwenden. Standard ist leer.

<BR>
<A NAME="servers-active_twin_server_ip">
<BR>
<B>Aktive Twin-Server-IP -</B> Einige Systeme erfordern die Einrichtung Telefonie-Server in Paaren. Diese Einstellung ist, wo Sie die Server-IP von einem anderen Server, dieser Server ist mit Partner setzen können. Standard ist leer für Behinderte.

<BR>
<A NAME="servers-active_asterisk_server">
<BR>
<B>Aktive Asterisk Server -</B> Wenn Asterisk ist auf diesem Server ausgeführt wird, oder wenn die Wahlprozesse sollten nicht über diesen Server, oder wenn nur mit diesem Server für andere Schriften wie dem Trichter Lade Skript würden Sie wollen, dies zu N. Standard eingestellt ist Y.

<BR>
<A NAME="servers-auto_restart_asterisk">
<BR>
<B>Auto-Restart Asterisk -</B> Wenn Asterisk auf diesem Server ausgeführt wird, und Sie wollen das System, um sicherzustellen, dass es in dem Fall, dass es abstürzt neu gestartet werden, möchten Sie vielleicht zu prüfen, diese Einstellung aktivieren. Wenn aktiviert, wird das System jede Minute überprüfen, um zu sehen, ob Asterisk läuft, und wenn es nicht wird es versuchen, es neu zu starten. Dieser Prozess wird nicht in den ersten 5 Minuten laufen, nachdem ein System bereits läuft. Standardwert ist N.

<BR>
<A NAME="servers-asterisk_temp_no_restart">
<BR>
<B>Temp No-Restart Asterisk -</B> Wenn Auto-Restart Asterisk ist auf diesem Server aktiviert ist, Einschalten dieser Einstellung wird der Auto-Neustart-Prozess ausgeführt wird, bis nach der Server neu gestartet zu verhindern. Standardwert ist N.

<BR>
<A NAME="servers-active_agent_login_server">
<BR>
<B>Aktive Agent Server -</B> Wenn diese Option auf N-Agenten wird von der Möglichkeit, auf diesen Server über den Agent-Bildschirm einloggen verhindern. Dies ist sehr nützlich, wenn Sie eine Telefon Login Lastenausgleich-Setup. Standardwert ist Y.

<BR>
<A NAME="servers-generate_conf">
<BR>
<B>Generate conf files -</B> Wenn Sie möchten, dass das System automatisch generieren Sternchen conf-Dateien, basierend auf den Telefonen Einträge, Einträge Träger und Lastverteilung im System-Setup dann setzen Sie diese auf Y. Standardwert ist Y.

<BR>
<A NAME="servers-rebuild_conf_files">
<BR>
<B>Rebuild conf Dateien - </B> Wenn Sie wollen, um einen Wiederaufbau der Asterisk conf-Dateien, oder wenn eines der Handys oder Carrier-Einträge geändert haben, dann sollte so eingestellt werden, dass Y. Nach der conf-Dateien wurden erzeugt und Asterisk hat wurden neu geladen, so wird diese Veränderung zu N. Voreingestellt ist Y.

<BR>
<A NAME="servers-rebuild_music_on_hold">
<BR>
<B>Rebuild Music On Hold -</B> Wenn Sie ein Wiederaufbau der Wartemusik-Dateien Gewalt oder wenn die Wartemusik Einträge oder Server-Einträge geändert haben, dann sollte dies auf Y., nachdem die Wartemusik-Dateien wurden synchronisiert und geladen gesetzt dann geändert werden, um N. Standard ist Y.

<BR>
<A NAME="servers-sounds_update">
<BR>
<B>Klingt Update -</B> Wenn Sie einen Scheck von Sound-Dateien auf diesem Server erzwingen wollen, und die zentrale Audio-Shop ist als ein System Einstellung aktiviert ist, wird dieses Feld ermöglicht die Töne Updater auf der nächsten Spitze der Minute ausführen. Jedes Mal, wenn eine Audio-Datei wird von der Web-Oberfläche wird automatisch auf Y für alle Server, Asterisk aktive haben hochgeladen. Der Standardwert ist N.

<BR>
<A NAME="servers-recording_limit">
<BR>
<B>Aufnahmebeschränkung -</B> Dieses Feld ist, wo Sie die maximale Anzahl von Minuten, dass eine Gesprächsaufzeichnung durch das System initiiert werden kann gesetzt. Standard ist 60 Minuten.

<BR>
<A NAME="servers-carrier_logging_active">
<BR>
<B>Carrier AktivLogging -</B> Diese Einstellung erlaubt Ihnen, alle hangup Return-Codes für jede ausgehende Liste Wählen log Anrufe, die Sie platzieren. Der Standardwert ist N.

<BR>
<A NAME="servers-custom_dialplan_entry">
<BR>
<B>Benutzerdefinierte Dialplan Eintrag -</B> Dieses Feld ermöglicht es Ihnen, in allen Dialplan Elemente, die Sie für den Server eingeben, werden die Zeilen in die Standard-Kontext hinzugefügt werden.






<BR><BR><BR><BR>

<B><FONT SIZE=3>conf_templatesTABELLE</FONT></B><BR><BR>
<A NAME="conf_templates-template_id">
<BR>
<B>Vorlagen-ID - </B> Dieses Feld muss mindestens 2 Zeichen lang sein und nicht mehr als 15 Zeichen lang sein, kein Leerzeichen. Dies ist die ID, die verwendet werden, um die conf Vorlage des gesamten Systems.

<BR>
<A NAME="conf_templates-template_name">
<BR>
<B>Template Name - </B> Dies ist der beschreibende Name der Conf-Datei-Vorlage Eintrag.

<BR>
<A NAME="conf_templates-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="conf_templates-template_contents">
<BR>
<B>Template Inhalt - </B> Dieses Feld ist, wo können Sie in den Einstellungen, die von allen Telefonen und-oder Luftfahrtunternehmen, die für die Verwendung dieser Vorlage conf. Bereiche, die nicht in diesem Feld sind: geheim, accountcode, Konto, Benutzernamen und Mailbox.





<BR><BR><BR><BR>

<B><FONT SIZE=3>server_carriersTABELLE</FONT></B><BR><BR>
<A NAME="server_carriers-carrier_id">
<BR>
<B>Carrier-ID - </B> Dieses Feld muss mindestens 2 Zeichen lang sein und nicht mehr als 15 Zeichen lang sein, kein Leerzeichen. Dies ist die ID, die verwendet werden, um die Träger für diesen speziellen Eintrag im gesamten System.

<BR>
<A NAME="server_carriers-carrier_name">
<BR>
<B>Carrier Name - </B> Dies ist der beschreibende Name des Beförderers Eintrag.

<BR>
<A NAME="server_carriers-carrier_description">
<BR>
<B>Carrier Beschreibung -</B> Dies ist in den Kommentaren des Sternchens conf-Dateien über den dialplan und Account-Einträge setzen. Maximal 255 Zeichen.

<BR>
<A NAME="server_carriers-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="server_carriers-registration_string">
<BR>
<B>Anmeldung String - </B> Dieses Feld ist, wo können Sie in die genaue String benötigt, der SIP-oder IAX-Konfigurations-Datei zu registrieren, um den Anbieter. Optional, aber dringend empfohlen, wenn Ihr durch die Registrierung.

<BR>
<A NAME="server_carriers-template_id">
<BR>
<B>Vorlagen-ID - </B> Dieses optionale Feld ermöglicht es Ihnen, wählen Sie eine conf-Datei-Vorlage für dieses Luftfahrtunternehmen Eintrag.

<BR>
<A NAME="server_carriers-account_entry">
<BR>
<B>Account Entry -</B> Dieses Feld wird verwendet, wenn Sie eine Vorlage verwenden nicht ausgewählt haben, und es ist, wo Sie in den spezifischen Kontoeinstellungen eingeben können, um für diesen Träger verwendet werden. Wenn Sie einnehmen wird, in eingehenden Anrufe von dieser Trägerstamm möchten Sie vielleicht, um das Kontext = trunkinbound in diesem Bereich gesetzt, so dass Sie die DID-Handling-Prozess, im System.

<BR>
<A NAME="server_carriers-protocol">
<BR>
<B>Protokoll - </B> Mit diesem Feld können Sie festlegen, das Protokoll zu verwenden für die Carrier-Eintrag. Derzeit werden nur IAX-und SIP unterstützt werden.

<BR>
<A NAME="server_carriers-globals_string">
<BR>
<B>Global String - </B> Dieses optionale Feld können Sie die Definition einer globalen Variablen, die für die Träger in der dialplan.

<BR>
<A NAME="server_carriers-dialplan_entry">
<BR>
<B>Dialplan Eintrag - </B> Dieses optionale Feld können Sie festlegen, eine Reihe von dialplan Einträge, die für diesen Träger.

<BR>
<A NAME="server_carriers-server_ip">
<BR>
<B>Bediener IP - </B> Dies ist der Server, dass dieser besondere Träger wird im Zusammenhang mit.

<BR>
<A NAME="server_carriers-active">
<BR>
<B>Aktive - </B> Diese legt fest, ob der Träger wird in den automatisch generierten conf-Dateien oder nicht.





<BR><BR><BR><BR>

<B><FONT SIZE=3>KONFERENZTISCH</FONT></B><BR><BR>
<A NAME="conferences-conf_exten">
<BR>
<B>Konferenz-Zahl -</B> Dieses Feld ist, wo Sie die MeetMe-Konferenznummer Dialplan setzen. Es wird auch empfohlen, dass die Zahl in meetme meetme.conf Spiele in dieser Zahl für jeden Eintrag. Dies ist für die Konferenzen in den astGUIclient Benutzer-Bildschirm und ist für Urlaubs-3-Wege-Call-Funktionalität im System verwendet.

<BR>
<A NAME="conferences-server_ip">
<BR>
<B>Bediener IP -</B> Das Menü, wo Sie den Sternchenbediener vorwählen,daß diese Konferenz eingeschaltet ist.




<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>SERVER_TrunksTABELLE</FONT></B><BR><BR>
	<A NAME="server_trunks">
	<BR>
	<B>Server Trunks können Sie die abgehenden Leitungen, die auf diesem Server für die Wahlkampagne auf einer Pro-Kampagne Basis verwendet werden, zu beschränken. Sie haben die Möglichkeit, eine bestimmte Anzahl der Zeilen, die nur von einer Kampagne verwendet werden, sowie die Möglichkeit, dass über seine Kampagne zur vorbehalten Linien laufen in was auch immer behalten Leitungen offen bleiben, so lange mit der Summe der durch das System auf diesem Server verwendet Linien weniger als der Max-Trunks-Einstellung. Nicht mit jeder dieser Datensätze werden die Kampagne, die die Linie wählt zuerst, so viele Linien zu ermöglichen, wie es unter der Max-Trunks Einstellung bekommen können.</B>
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SYSTEM_SETTINGSTABELLE</FONT></B><BR><BR>
<A NAME="settings-use_non_latin">
<BR>
<B>Gebrauch Nicht-Lateinisch -</B> diese Wahl erlaubt Ihnen, den NetzanzeigeIndex auf Buchstaben des Gebrauches UTF8 zurückzufallen und keinenfilternden Lateinisch-Buchstabefamilie regelmäßigen Ausdruck zu tunoder Formatierung anzuzeigen. Rückstellung ist 0.

<BR>
<A NAME="settings-webroot_writable">
<BR>
<B>Webroot schreibbar -</B> diese Einstellung erlaubt Ihnen zu definieren, obTemperatur Akten und Authentisierung Akten in das webroot auf Ihr webserver gelegt werden sollten. Rückstellung ist 1.

<BR>
<A NAME="settings-agent_disable">
<BR>
<B>Agent-Disable Anzeige -</B> Dieses Feld wird verwendet, um auszuwählen, an einen Agenten Hinweise zeigen, wenn die Sitzung wurde von dem System, ein Manager Aktion oder durch eine externe Maßnahme deaktiviert. Die NOT_ACTIVE Einstellung wird die Meldung auf dem Bildschirm Agenten deaktivieren. Die Einstellung wird erst LIVE_MITTELBehinderte Meldung angezeigt, wenn die Agenten auto_calls Rekord entfernt worden ist, z. B. während eines Kraft-Logout oder Notfall-Logout. Standardmäßig werden alle.

<BR>
<A NAME="settings-frozen_server_call_clear">
<BR>
<B>Klar Gefrorene Anrufe -</B> Diese Option kann die Fähigkeit für die Seite allgemeine Berichte und die optionale AST_timecheck.pl Skript zu räumen auto_calls die Einträge für eine gefrorene Server, so dass sie keinen Einfluss auf Call-Routing aktivieren. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-allow_sipsak_messages">
<BR>
<B>Allow SIPSAK Messages -</B> Wenn auf 1 gesetzt, wird diese ermöglichen die sipsak Telefone Tisch Einstellung zu arbeiten, wenn das Telefon mit dem SIP-Protokoll festgelegt ist. Der Server Nachrichten an den SIP-Telefon senden, um auf dem Handy-Anzeige angezeigt, wenn im System angemeldet. Dieses Feature funktioniert nur mit SIP-Telefonen und erfordert sipsak Anwendung auf dem Web-Server, die der Agent angemeldet ist installiert. Standard ist 0. 

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agent API Aktiv- </B> Wenn auf 1 gesetzt, wird die Agent-API-Schnittstelle zu funktionieren. Standard ist 0. 

<BR>
<A NAME="settings-admin_home_url">
<BR>
<B>Admin Haupt-URL -</B> dieses ist die URL oder Web site Adresse, daß Siegehen, wenn Sie an die HAUPTVERBINDUNG an der Oberseite der admin.phpSeite klicken.

<BR>
<A NAME="settings-admin_modify_refresh">
<BR>
<B>Admin Ändern Auto-Refresh -</B> Dies ist das Aktualisierungsintervall in Sekunden der modify-Bildschirme in diesem Admin-Interface. Sie diesen Wert auf 0 deaktiviert es, indem ich sie unter 5 wird meistens machen die Steuerwerte Bildschirme unbrauchbar, weil sie zu schnell wird aktualisiert, um Felder zu ändern. Diese Option ist nützlich in Situationen, in denen mehr als ein Manager Controlling Einstellungen basiert auf einer aktiven Kampagne oder in-group, damit die Einstellungen häufig aktualisiert werden. Standard ist 0.

<BR>
<A NAME="settings-nocache_admin">
<BR>
<B>Admin No-Cache -</B> Wenn Sie diesen Wert auf 1 wird alle Admin-Seiten auf Web-Browser no-cache gesetzt, so dass jeder Bildschirm muss jedes Mal neu geladen wird es angesehen werden, auch wenn Sie wieder auf den Browser. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-enable_agc_xfer_log">
<BR>
<B>Ermöglichen Sie Vertreter-Übergangsprotokolldatei - diese Wahlprotokolliert in einer Textprotokolldatei auf dem webserver, jedesmalwenn ein Anruf auf ein Mittel gebracht wird. Rückstellung ist 0,gesperrt.

<BR>
<A NAME="settings-enable_agc_dispo_log">
<BR>
<B>Aktivieren Agenten Disposition Logfile -</B> Diese Option wird in einer Textdatei auf dem Webserver Logfiles protokollieren jedes Mal, wenn ein Anruf von einem Agenten dispositioned wird. Standard ist 0, deaktiviert.

<BR>
<A NAME="settings-timeclock_end_of_day">
<BR>
<B>Timeclock End of Day - </B> Diese Einstellung legt fest, wenn alle Benutzer werden automatisch abgemeldet der timeclock System. Läuft nur einmal pro Tag. darf nur 4 Ziffern 2 Digit Stunden und 2 Minuten in Ziffer 24 Stunden Zeit. Standard ist 0000.

<BR>
<A NAME="settings-default_local_gmt">
<BR>
<B>Standard Lokales GMT -</B> Diese Einstellung legt fest, was standardmäßig verwendet werden, wenn neue Telefone und Server hinzugefügt werden, mit dieser Admin Web-Interface. Die Standardeinstellung ist -5.

<BR>
<A NAME="settings-default_voicemail_timezone">
<BR>
<B>Standard-Voicemail-Zone -</B> Diese Einstellung definiert, in welcher Zone wird standardmäßig verwendet werden, wenn neue Telefone und Voicemail-Boxen erstellt werden. Die Liste der verfügbaren Zonen wird direkt aus dem voicemail.conf Datei entnommen. Die Standardeinstellung ist Ost.

<BR>
<A NAME="settings-agents_calls_reset">
<BR>
<B>Bevollmächtigte Calls zurücksetzen -</B> Diese Einstellung legt fest, ob die angemeldeten Agenten und aktive Anrufe Aufzeichnungen zurückgesetzt werden an der Zeitschaltuhr am Ende des Tages sind. Standard ist 1 für aktivierte.

<BR>
<A NAME="settings-timeclock_last_reset_date">
<BR>
<B>LetzteTimeclock Auto Logout - </B> Dieses Feld zeigt das Datum der letzten Auto-logout.

<BR>
<A NAME="settings-vdc_header_date_format">
<BR>
<B>Agent-Header Datum Screen Format -</B> In diesem Menü können Sie das Format des Datums und der Zeit, die an der Spitze des Agenten-Bildschirm zeigt, zu wählen. Die Optionen für diese Einstellung sind: Standard ist MS_DASH_24HR<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Standard-Datum-Format Jahr Monat Tag gefolgt von 24-Stunden-Zeit<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - USA Datum-Format mit Monat Tag Jahr gefolgt von 24-Stunden-Zeit<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Europäische Datumsformat mit Tag Monat Jahr gefolgt von 24-Stunden-Zeit<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Text-Format mit Datum abgekürzt Monat Tag gefolgt von 24-Stunden-Zeit<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Standard-Datum-Format Jahr Monat Tag gefolgt von 12-Stunden-Zeit<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - USA Datum-Format mit Monat Tag Jahr gefolgt von 12-Stunden-Zeit<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Europäische Datumsformat mit Tag Monat Jahr gefolgt von 12-Stunden-Zeit<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Text-Format mit Datum abgekürzt Monat Tag gefolgt von 12-Stunden-Zeit<BR>

<BR>
<A NAME="settings-vdc_customer_date_format">
<BR>
<B>Agent Screen Kunden Datum Format -</B> In diesem Menü können Sie das Format des Kunden Datum und Uhrzeit, die an der Spitze der Kundeninformationsteil des Bildschirms zeigt Mittel zu wählen. Die Optionen für diese Einstellung sind: Standard ist AL_TEXT_AMPM<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Standard-Datum-Format Jahr Monat Tag gefolgt von 24-Stunden-Zeit<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - USA Datum-Format mit Monat Tag Jahr gefolgt von 24-Stunden-Zeit<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Europäische Datumsformat mit Tag Monat Jahr gefolgt von 24-Stunden-Zeit<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Text-Format mit Datum abgekürzt Monat Tag gefolgt von 24-Stunden-Zeit<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Standard-Datum-Format Jahr Monat Tag gefolgt von 12-Stunden-Zeit<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - USA Datum-Format mit Monat Tag Jahr gefolgt von 12-Stunden-Zeit<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Europäische Datumsformat mit Tag Monat Jahr gefolgt von 12-Stunden-Zeit<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Text-Format mit Datum abgekürzt Monat Tag gefolgt von 12-Stunden-Zeit<BR>

<BR>
<A NAME="settings-vdc_header_phone_format">
<BR>
<B>Agent Screen Kunden Telefon Format -</B> In diesem Menü können Sie das Format des Kunden Telefonnummer, die in der Statusabschnitt des Bildschirms zeigt Mittel zu wählen. Die Optionen für diese Einstellung sind: Standard ist US_PARN<BR>
US_DASH 000-000-0000 - USA Bindestrich getrennt Telefonnummer<BR>
US_PARN (000)000-0000 - USA Bindestrich getrennt Nummer mit Vorwahl in Klammern<BR>
MS_NODS 0000000000 - Keine Formatierung<BR>
UK_DASH 00 0000-0000 - UK Bindestrich getrennt Telefonnummer mit Leerzeichen nach Stadt-Code<BR>
AU_SPAC 000 000 000 - Australia Leerzeichen getrennt Telefonnummer<BR>
IT_DASH 0000-000-000 - Italy Bindestrich getrennt Telefonnummer<BR>
FR_SPAC 00 00 00 00 00 - France Leerzeichen getrennt Telefonnummer<BR>

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agent interface API Access Aktiv-</B> This option allows you to enable or disable the agent interface API. Default is 0.

<BR>
<A NAME="settings-agentonly_callback_campaign_lock">
<BR>
<B>Agent Only Rückruf-Lock-Kampagne -</B> Diese Option legt fest, ob AGENTONLY Rückrufe an der Kampagne, dass der Agent ursprünglich erstellt wurde ihnen im Rahmen gesperrt sind. Sie diesen Wert auf 1 bedeutet, dass der Agent kann nur wählen sie aus der Kampagne wurden sie unter festgelegten, 0 bedeutet, dass der Agent auf sie zugreifen, egal was sie in Aktion protokolliert werden können. Der Standardwert ist 1.

<BR>
<A NAME="settings-sounds_central_control_active">
<BR>
<B>Central Sound Control Aktiv-</B> Diese Option legt fest, ob der Ton-Synchronisierung System aktiv ist, auf allen Servern. Der Standardwert ist 0 für inaktive.

<BR>
<A NAME="settings-sounds_web_server">
<BR>
<B>Sounds Web Server -</B> Dies ist die Server-Namen oder die IP-Adresse des Web-Server, der die Sound-Dateien auf diesem System Handling wird, muss dieses Spiel den Server-Namen oder die IP des Rechners, den Sie versuchen, die audio_store.php Webseite über den Zugang oder es wird nicht funktionieren . Standard ist 127.0.0.1.

<BR>
<A NAME="settings-sounds_web_directory">
<BR>
<B>Sounds Web-Verzeichnis -</B> Diese automatisch generierten Verzeichnis-Name wird zufällig vom System als den Ort, dass die Audio-Lager gehalten werden geschaffen. Alle Audio-Dateien befinden sich in diesem Verzeichnis.

<BR>
<A NAME="settings-admin_web_directory">
<BR>
<B>Admin Web Directory -</B> Dies ist die Web-Verzeichnis, das die administa Web-Inhalte, wie admin.php sind in. Um herauszufinden, Ihr Admin-Web-Verzeichnis, es ist alles, was zwischen dem Domainnamen und der admin.php in der URL auf dieser Seite ist, ohne die Anfang und Ende Schrägstriche.

<BR>
<A NAME="settings-active_voicemail_server">
<BR>
<B>Aktive Voicemail-Server -</B> Im Multi-Server-Systemen, ist dies der Server, der alle Voicemailboxen behandelt. Dieser Server ist auch, wenn die Einwahl-Anweisungen generiert werden aus hochgeladen werden, die 8168 Aufnahmen.

<BR>
<A NAME="settings-allow_voicemail_greeting">
<BR>
<B>Erlauben Voicemail Gruß Chooser -</B> Wenn diese Einstellung aktiviert ist es erlaubt Ihnen, eine Audio-Datei aus dem Audio-Speicher wählen, die als Voicemail-Ansage zu einem bestimmten Voicemail-Box gespielt werden. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-outbound_autodial_active">
<BR>
<B>Outbound Auto-Dial Aktiv-</B> Diese Option ermöglicht es Ihnen, ausgehende automatische Einwahl in das System aktivieren oder deaktivieren, die Einstellung dieses Feld auf 0 werden die Listen zu entfernen und Filter Abschnitte und viele Felder aus den Kampagne Änderung Bildschirme. Manuelle Eingabe Wahl wird immer noch innerhalb der zulässigen Mittel Bildschirm, aber keine Liste Wahl möglich ist. Der Standardwert ist 1 für aktive.

<BR>
<A NAME="settings-disable_auto_dial">
<BR>
<B>Deaktivieren Sie Auto-Dial -</B> Diese Option ist nur von einem Systemadministrator bearbeitet werden. Es wird nicht alle Optionen von der Management-Web-Interface zu entfernen, aber es wird keine automatische Einwahl von Leads aus geschieht auf dem System zu verhindern. Nur Manuelle Wahl ausgehende Anrufe direkt von Agenten ausgelöst wird funktionieren, wenn diese Option aktiviert ist. Standard ist 0 für inaktiv.

<BR>
<A NAME="settings-auto_dial_limit">
<BR>
<B>Ratio Dial Limit -</B> Dies ist die maximale Grenze der automatische Einwahl-Ebene in die Kampagne Bildschirm.

<BR>
<A NAME="settings-outbound_calls_per_second">
<BR>
<B>Max-FILL fordert pro Sekunde -</B> Diese Einstellung bestimmt die maximale Anzahl von Anrufen, die sich durch den Automobil-füllen Sie Outbound-Dialing-Skript automatisch für alle Server, pro Sekunde. Muss zwischen 1 und 200. Der Standardwert ist 40.

<BR>
<A NAME="settings-allow_custom_dialplan">
<BR>
<B>Lassen Sie Custom Dialplan Einträge -</B> Diese Option ermöglicht es Ihnen, benutzerdefinierte Dialplan Linien in Anruf-Menüs,-Server und System-Einstellungen eingeben. Standard ist 0 für inaktive.

<BR>
<A NAME="settings-pllb_grouping_limit">
<BR>
<B>PLLB Limit-Gruppierung -</B> Telefon Einloggen Load Balancing Verbund Limit. Wenn PLLB Gruppierung in Cascading auf der Kampagnen-Ebene eingestellt ist, dann wird diese Einstellung bestimmt die Anzahl der Agenten akzeptabel auf jedem Server in allen Kampagnen. Der Standardwert ist 100.

<BR>
<A NAME="settings-generate_cross_server_exten">
<BR>
<B>Generieren Sie Cross-Server-Durchwahls -</B> Diese Option, wenn auf 1 gesetzt wird Dialplan Einträge für jedes Telefon auf eine Multi-Server-System zu generieren. Standard ist 0 für inaktive.

<BR>
<A NAME="settings-user_territories_active">
<BR>
<B>User Gebiete Aktiv-</B> Diese Einstellung können Sie die Benutzereinstellungen Gebiete von der Benutzer Modifikation Bildschirm ermöglichen. Diese Funktion wurde hinzugefügt, um für mehr Integration mit einer angepassten Vtiger Installation zu ermöglichen, kann aber Anwendungen im System selbst als gut. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-enable_second_webform">
<BR>
<B>Aktivieren Zweiten Webform -</B> This setting allows you to have a second web form for campaigns and in-groups in the agent interface. Default is 0 for disabled.

<BR>
<A NAME="settings-enable_tts_integration">
<BR>
<B>Aktivieren TTS Integration -</B> Diese Einstellung ermöglicht es Ihnen zu ermöglichen, Text-to-Speech Integration mit Cepstral. Dies ist derzeit nur für Outbound-Kampagnen Art Umfrage. Der Standardwert ist 0 für Behinderte.

<BR>
<A NAME="settings-callcard_enabled">
<BR>
<B>Aktivieren CallCard -</B> Diese Einstellung ermöglicht es den Callcard-Funktionen, damit für den Anrufer an Pin-Nummern und card_ids, die ein Gleichgewicht von Minuten, und diese Bilanzen aufweisen können Mittel Sprechzeit auf Kundenanrufe in Gruppen abgezogen haben. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-custom_fields_enabled">
<BR>
<B>Aktivieren Sie Benutzerdefinierte Liste Felder -</B> Diese Einstellung ermöglicht die benutzerdefinierte Liste Felder Feature, das für benutzerdefinierte Datenfelder in der Webschnittstelle auf einer Pro-Liste auf Basis definiert werden und dann müssen die Felder in einer Form vor Registerkarte, um den Agenten in der Agenten-Web-Interface ermöglicht. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-test_campaign_calls">
<BR>
<B>Aktivieren Kampagne Test-Anrufe -</B> Diese Einstellung ermöglicht die Fähigkeit, ein Telefon und die Telefonnummer in die Felder eingeben am unteren Ende der Kampagne Detailbild und legen Sie einen Anruf an diese Nummer als wäre es eine Führung in das System auto-gewählt wurden. Die Rufnummer wird als eine neue Führung in der Liste manuelle Einwahl-ID-Liste gespeichert werden. Die Kampagne muss aktiv sein, damit diese Funktion aktiviert werden, und es wird empfohlen, dass die Listen zugeordnet der Kampagne alle auf inaktiv gesetzt werden. Die Vorwahl, Dial-Timeout und alle anderen Wahlverfahren verwandten Funktionen, mit Ausnahme von DNC und Call-Optionen eingeben, wird Auswirkungen auf die Wahl der Prüfnummer. Der Anruf wird auf dem Server als dem Voicemail-Server in den Systemeinstellungen ausgewählt platziert werden. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-expanded_list_stats">
<BR>
<B>Aktivieren Sie Erweiterte Liste Stats -</B> Diese Einstellung ermöglicht es zwei weitere Säulen, auf die meisten der Liste Status Zusammenbruch Tabellen auf der Liste Modifizierung und Änderung Kampagne Seiten angezeigt werden. Penetration ist als der Prozentsatz von Leitungen, die bei oder oberhalb der Kampagne Anruf Count Limit sind und oder der Status wird als abgeschlossen gekennzeichnet definiert. Standard ist 1 für aktivierte.

<BR>
<A NAME="settings-country_code_list_stats">
<BR>
<B>Ländercode-Liste Statistik -</B> Diese Einstellung wird aktiviert, wenn die Landes-Zusammenbruch Zusammenfassung auf der Liste ändern Bildschirm zu zeigen. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-enhanced_disconnect_logging">
<BR>
<B>Verbesserte Disconnect Logging -</B> Diese Einstellung aktiviert die Protokollierung der Anrufe, die ein CONGESTION Signal mit einer Ursache-Code von 1, 19, 21, 34 oder 38 zu bekommen. Wir empfehlen in der Regel nicht mit dieser Freigabe in den USA. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-campaign_cid_areacodes_enabled">
<BR>
<B>Aktivieren Kampagne Vorwahl CID -</B> Diese Einstellung ermöglicht es die Möglichkeit, bestimmte abgehende CallerID Zahlen, die pro Kampagne verwendet werden soll. Standard ist 1 für aktivierte.

<BR>
<A NAME="settings-did_ra_extensions_enabled">
<BR>
<B>Aktivieren Sie Remote Agent Erweiterung Overrides -</B> Diese Einstellung ermöglicht DIDs auf Verlängerung Überschreibungen für Remote-Agenten geroutet haben ruft durch in-Gruppen. Standard ist 0 für Behinderte.

<BR>
<A NAME="contact_information">
<A NAME="settings-contacts_enabled">
<BR>
<B>Kontakte aktiviert -</B> Diese Einstellung aktiviert die Kontakte Abs. Admin in die ein Manager zu ändern oder zu löschen Kontakte im System, die als Teil einer benutzerdefinierten Transfer in einer Kampagne, wo ein Agent für Kontakte nach Vorname Nachname oder Büro suchen können verwendet werden kann hinzufügen können Nummer und wählen Sie dann eine der vielen Zahlen mit diesem Kontakt zugeordnet. Diese Funktion wird oft von den Betreibern oder im Schaltschrank, wo Funktionen muss der Benutzer einen Anruf an einen Nicht-Agent Telefon übertragen würden verwendet. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-call_menu_qualify_enabled">
<BR>
<B>Aufruf Menü Qualifizieren Aktiviert -</B> Mit dieser Einstellung können Sie die Option Anruf in Menüs, um eine SQL Qualifikation auf die Leute, die diesen Ruf hören Menü setzen. Für weitere Informationen darüber, wie dieses Feature funktioniert, zeigen Sie die Hilfe für Call Menüs. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-level_8_disable_add">
<BR>
<B>Level 8 Deaktivieren hinzufügen -</B> Diese Einstellung wird aktiviert, wenn jede Stufe 8 Benutzer hinzufügen oder Kopieren auf einen Eintrag im System, egal, was ihre Benutzer-Einstellungen sind zu vermeiden. Ausgenommen von diesen Beschränkungen sind die Fähigkeit, DNC und Filter Phone Gruppen Zahlen zu addieren und das Hinzufügen eines neuen Lead-Seite. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-admin_list_counts">
<BR>
<B>Admin Liste Grafen -</B> Diese Einstellung können Sie die Liste zählt, die auf den Listen Notierung und die Kampagne modify Bildschirme erscheinen deaktivieren. Der Standardwert ist 1 für aktivierte.

<BR>
<A NAME="settings-allow_emails">
<BR>
<B>Erlauben Sie E-Mails -</B> Dies ist, wo Sie festlegen können, ob dieses System in der Lage, eingehende E-Mails zusätzlich zu Telefon Anrufe empfangen.

<BR>
<A NAME="settings-first_login_trigger">
<BR>
<B>Erster Login-Trigger -</B> Mit dieser Einstellung kann für die Erstkonfiguration des Bediener Bildschirm, um dem Administrator angezeigt werden, wenn sie in das System zuerst anmelden.

<BR>
<A NAME="settings-default_phone_registration_password">
<BR>
<B>Standard-Telefon-Anmeldung Passwort -</B> Dies ist die Standard-Registrierung Passwort verwendet werden, wenn neue Handys auf dem System hinzugefügt werden. Die Standardeinstellung ist Test.

<BR>
<A NAME="settings-default_phone_login_password">
<BR>
<B>Standard-Telefon Login Passwort -</B> Dies ist die Standard Web-Telefon Login Passwort verwendet werden, wenn neue Handys auf dem System hinzugefügt werden. Die Standardeinstellung ist Test.

<BR>
<A NAME="settings-default_server_password">
<BR>
<B>Standard-Server-Kennwort -</B> Dies ist die Standard-Server-Passwort verwendet werden, wenn neue Server mit dem System hinzugefügt werden. Die Standardeinstellung ist Test.

<BR>
<A NAME="settings-slave_db_server">
<BR>
<B>Slave Database Server -</B> Wenn Sie eine MySQL-Slave-Datenbank-Server haben, dann geben Sie die lokale IP-Adresse für diesen Server hier. Diese Option ist derzeit nur durch die ausgewählten Berichte in der nächsten Option genutzt und hat nichts mit der automatischen Konfiguration von MySQL Master-Slave-Replikation zu tun. Standard ist leer für Behinderte.

<BR>
<A NAME="settings-reports_use_slave_db">
<BR>
<B>Berichte auf Slave DB verwenden -</B> Diese Option ermöglicht es Ihnen, die Berichte, die Sie verwenden, die MySQL-Datenbank als Slave in der oben genannten Option statt der Master-Datenbank, die Ihr Live-System läuft definiert haben soll. Sie müssen zu den MySQL-Slave-Replikation, bevor Sie diese Option aktivieren. Standard ist leer für Behinderte.

<BR>
<A NAME="settings-default_field_labels">
<BR>
<B>Standard Feldbezeichner -</B> Diese 19 Feldern können Sie den Namen gesetzt, wie es in der Agenten-Oberfläche angezeigt als auch die Verwaltungs modify Blei-Seite. Der Standardwert ist leer, was die hartcodierte Standardwerte in das Agenten-Interface nutzen werden. Sie können auch ein Label --- Verberge --- sowohl das Etikett und das Feld zu verstecken eingestellt.

<BR>
<A NAME="settings-label_hide_field_logs">
<BR>
<B>Beschriftung ausblenden in Anrufprotokolle -</B> Wird ein Etikett zu setzen --- Verberge --- dann sind die Agenten Anruflisten, wenn über die Kampagne aktiviert ist, werden immer noch das Feld und Daten, wenn diese Option gesetzt, um Standard-Y ist N.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>QC Funktionen Aktiv- </B> Mit dieser Option können Sie aktivieren oder deaktivieren Sie die QC oder Qualitätskontrolle-Funktionen. Der Standardwert ist 0 für inaktiv.

<BR>
<A NAME="settings-default_webphone">
<BR>
<B>Standard Webphone -</B> Wenn auf 1 gesetzt, diese Option wird alle neuen Handys erstellt wurden als Webphone Satz zu Y. Standardwert ist 0.

<BR>
<A NAME="settings-webphone_systemkey">
<BR>
<B>Webphone System Key -</B> Wenn Ihr System oder Anbieter erfordert es, das ist, wo das System für den Key eingegeben webphone sollte in. Standard ist leer werden.

<BR>
<A NAME="settings-default_codecs">
<BR>
<B>Standard-Codecs -</B> Sie können eine durch Kommas getrennte Liste von Codecs als die Standard-Codecs für alle Systeme festgelegt werden. Optionen für Codecs beinhalten ulaw, alaw, GSM, G729, Speex, G722, G723, G726, ilbc, ... Standard ist leer.

<BR>
<A NAME="settings-custom_dialplan_entry">
<BR>
<B>Benutzerdefinierte Dialplan Eintrag -</B> Dieses Feld ermöglicht es Ihnen, in allen Dialplan Elemente, die Sie für all die Asterisk-Servern eingeben, werden die Zeilen in die Standard-Kontext hinzugefügt werden.

<BR>
<A NAME="settings-reload_dialplan_on_servers">
<BR>
<B>Auf Servern neu laden Dialplan -</B> Diese Option ermöglicht es Ihnen, ein erneutes Laden der Dialplan auf allen Asterisk-Server im Cluster zu zwingen. Wenn Sie Änderungen in den Custom Dialplan Eintrag oben gemachten Sie dies auf 1 gesetzt und reichen sollte, um diese Änderungen in Kraft treten müssen auf den Servern.

<BR>
<A NAME="settings-noanswer_log">
<BR>
<B>Keine Antwort Anmelden -</B> Diese Option wird protokolliert die Wählautomatik Anrufe, die nicht auf einer separaten Tabelle beantwortet werden. Standard ist N.

<BR>
<A NAME="settings-did_agent_log">
<BR>
<B>DID-Agent-Protokoll -</B> Diese Option wird protokolliert die eingehenden Anrufe DID zusammen mit einer Gruppe in-und Benutzer-ID, falls zutreffend, in eine separate Tabelle. Standard ist N.

<BR>
<A NAME="settings-alt_log_server_ip">
<BR>
<B>Alt-Log DB Server -</B> Dies ist der alternative Log-Datenbank-Server. Dies ist optional und ermöglicht einige Protokolle auf einer separaten Datenbank geschrieben werden. Standard ist leer.

<BR>
<A NAME="settings-tables_use_alt_log_db">
<BR>
<B>Alt-Log-Tabellen -</B> Dies sind die Tabellen, die für die Protokollierung auf dem alternativen Protokoll-Datenbank-Server sind. Standardeinstellung ist leer.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>Externe Server-IP-Standard -</B> Wenn auf 1 gesetzt, diese Option wird alle neuen Telefone erstellt haben Mit externen Bediener IP auf Y gesetzt Standard ist 0.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>Webphone URL -</B> Dies ist die URL des webphone, die mit diesem System verwendet werden soll, wenn es in der Rekord-Telefone aktiviert ist, dass ein Agent verwendet. Standard ist leer.

<BR>
<A NAME="settings-enable_queuemetrics_logging">
<BR>
<B>Enable QueueMetrics Logging -</B> Diese Einstellung können Sie festlegen, ob das System-Log-Einträge in die Datenbank queue_log Tabelle einfügen als Asterisk Warteschlangen Aktivität tut. QueueMetrics ist eine eigenständige, Closed-Source-statistische Analyse-Programm. Sie müssen QueueMetrics bereits installiert und vor der Aktivierung dieser Funktion konfiguriert haben. Standard ist 0.

<BR>
<A NAME="settings-queuemetrics_server_ip">
<BR>
<B>QueueMetrics Bediener IP -</B> dieses ist das IP address der Datenbankfür Ihre QueueMetrics Installation.

<BR>
<A NAME="settings-queuemetrics_dbname">
<BR>
<B>QueueMetrics Datenbank-Name -</B> dieses ist der Datenbankname für IhreQueueMetrics Datenbank.

<BR>
<A NAME="settings-queuemetrics_login">
<BR>
<B>QueueMetrics Datenbank-LOGON -</B> dieses ist der Benutzername, derverwendet wird, um in Ihrer QueueMetrics Datenbank innen zuprotokollieren.

<BR>
<A NAME="settings-queuemetrics_pass">
<BR>
<B>QueueMetrics Datenbank-Kennwort -</B> dieses ist das Kennwort, dasverwendet wird, um in Ihrer QueueMetrics Datenbank innen zuprotokollieren.

<BR>
<A NAME="settings-queuemetrics_url">
<BR>
<B>QueueMetrics URL -</B> Dieses ist die URL oder Web site Adresse, dieverwendet wird, um zu Ihrer QueueMetrics Installation zu kommen.

<BR>
<A NAME="settings-queuemetrics_log_id">
<BR>
<B>QueueMetrics Log ID -</B> Dies ist der Server-ID, die alle Contact Center-Protokolle gehen in die QueueMetrics Datenbank wird als Kennung für jeden Datensatz verwenden.

<BR>
<A NAME="settings-queuemetrics_eq_prepend">
<BR>
<B>QueueMetrics EnterQueue Prepend -</B> Dieses Feld wird verwendet, um das Voranstellen von einem der Listendatenfelder vor der Telefonnummer des Kunden für maßgeschneiderte QueueMetrics Berichte ermöglichen. Der Standardwert ist NONE, um nichts zu bevöl.

<BR>
<A NAME="settings-queuemetrics_loginout">
<BR>
<B>QueueMetrics Login-Out -</B> Diese Option beeinflusst, wie das System die An-und Abmeldungen von einem Agenten in der queue_log anmelden. Standard ist STANDARD Standard-Agentlogin AGENTLOGOFF verwenden, wird CALLAgentCallBackLogin und AGENTCALLBACKLOGOFF verwenden, die QM wird unterschiedlich analysieren, wird NONE keine An-und Abmeldungen innerhalb queue_log nicht anmelden.

<BR>
<A NAME="settings-queuemetrics_callstatus">
<BR>
<B>QueueMetrics CallStatus -</B> Diese Option, wenn der Wert auf 0 wird nicht in der CALLSTATUS die Einreise in queue_log, wenn ein Agent einen Anruf Dispositionen. Standard ist 1 für aktivierte.

<BR>
<A NAME="settings-queuemetrics_addmember_enabled">
<BR>
<B>QueueMetrics AddMember Aktiviert -</B> Diese Option, wenn auf 1 gesetzt wird und ADDMEMBER2 removeMember Einträge in queue_log generieren. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-queuemetrics_dispo_pause">
<BR>
<B>QueueMetrics Dispo Pausecode -</B> Diese Option, wenn besiedelt, können Sie definieren, ob ein Dispo Pause Code in queue_log, wenn ein Agent ist in Dispo-Status eingetragen wird. Standard ist leer für Behinderte.

<BR>
<A NAME="settings-queuemetrics_pause_type">
<BR>
<B>QueueMetrics Pause Typ Logging -</B> Wenn aktiviert, wird diese Option die Art der Pause in der Tabelle Daten5 Feld queue_log anmelden. Sie müssen sicherstellen, dass Sie ein Daten5 Bereich haben oder die Aktivierung dieser Funktion wird QM-Kompatibilität zu brechen. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-queuemetrics_pe_phone_append">
<BR>
<B>QueueMetrics Telefon Umwelt Telefon anhängen -</B> Diese Option, falls aktiviert, wird der Agent anhängen Telefon anmelden, um die data4 Rekord in der Warteschlange Log-Tabelle, wenn die Kampagne Telefon Umwelt-Feld gefüllt wird. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-queuemetrics_record_hold">
<BR>
<B>QueueMetrics Halten Anrufprotokoll -</B> Diese Option, wenn aktiviert, melden Sie sich, wenn ein Kunde wird gehalten und aus Halt in der neueren record_tags QM-Tabelle entnommen. Standard ist 0 für Behinderte.

<BR>
<A NAME="settings-queuemetrics_socket">
<BR>
<B>QueueMetrics Sockel senden -</B> Diese Option, falls aktiviert, werden QM-Daten zu einer Web-Seite, die es über eine Socket sendet für die Anmeldung zu senden. Die CONNECT_COMPLETE Option CONNECT, COMPLETEMITTELund COMPLETECALLER Ereignisse an den url nachstehend definiert senden. Der Standardwert ist NONE für Behinderte.

<BR>
<A NAME="settings-queuemetrics_socket_url">
<BR>
<B>QueueMetrics Sockel senden URL -</B> Wenn Sockel Send oben aktiviert ist, ist dies die URL, die verwendet werden, um die Daten zu senden. Standardwert ist Empty für Behinderte.

<BR>
<A NAME="settings-enable_vtiger_integration">
<BR>
<B>Enable Vtiger Integration -</B> Diese Einstellung können Sie Vtiger Integration mit dem System zu ermöglichen. Aktuelle Links zu Vtiger admin und suchen sowie Benutzer Replikation sind die einzigen Integrationsfunktionen zur Verfügung. Standard ist 0.

<BR>
<A NAME="settings-vtiger_server_ip">
<BR>
<B>Vtiger HE Bediener IP - </B> Das ist die IP-Adresse der Datenbank für Ihre vtiger-Installation.

<BR>
<A NAME="settings-vtiger_dbname">
<BR>
<B>Vtiger Datenbankname - </B> Dies ist die Datenbank-Namen für Ihre Datenbank vtiger.

<BR>
<A NAME="settings-vtiger_login">
<BR>
<B>Vtiger Datenbank Login - </B> Das ist die Benutzernamen verwendet, um sich in Ihrem vtiger-Datenbank.

<BR>
<A NAME="settings-vtiger_pass">
<BR>
<B>Vtiger Passwort für die Datenbank - </B> Dies ist das Passwort für die Anmeldung in Ihrem vtiger-Datenbank.

<BR>
<A NAME="settings-vtiger_url">
<BR>
<B>Vtiger URL - </B> Das ist die URL oder Website-Adresse, um an Ihr vtiger-Installation.


<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUSESTABELLE</FONT></B><BR><BR>
<A NAME="system_statuses">
<BR>
<B>Durch die Verwendung von Systemzuständen, können Sie Zustände, die für alle Kampagnen und-Gruppen vorhanden sind. Die Status muss 1-6 Zeichen lang sein, muss die Beschreibung 2-30 Zeichen lang sein und Wählbare definiert, ob es zeigt sich in dem System als Agent Disposition. Die human_answered Feld wird bei der Berechnung die Drop Prozentsatz oder Abbruchrate. Die Einstellung auf Y human_answered diesen Status zu verwenden, wenn das Zählen der menschlichen angenommene Anrufe. Die Option Kategorie können Sie mehrere Status-Gruppe in eine Kategorie, die für die statistische Analyse verwendet werden kann. Es gibt auch 5 zusätzliche Einstellungen, die die Art von Status definieren wird: Verkauf, dnc, Kundenkontakt, kein Interesse, nicht praktikabel, geplante Rückruf.</B>



<BR><BR><BR><BR>

<B><FONT SIZE=3>SCREEN_LABELSTABELLE</FONT></B><BR><BR>
<A NAME="screen_labels">
<BR>
<B>Screen labels give you the option of setting different labels for the default agent screen fields on a pro Kampagne basis.</B>

<A NAME="screen_labels-label_id">
<BR>
<B>Screen-Label-ID -</B> Dieses Feld muss mindestens 2 Zeichen lang sein und nicht länger als 20 Zeichen lang sein, keine Leerzeichen oder Sonderzeichen enthalten. Dies ist die ID, die verwendet werden, um den Bildschirm Label im gesamten System identifiziert wird.

<BR>
<A NAME="screen_labels-label_name">
<BR>
<B>Screen-Label Name -</B> Dies ist der beschreibende Name des Bildschirms Etikett Eintrag.

<BR>
<A NAME="screen_labels-user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Rekord, ermöglicht diese Admin Betrachten dieser recoird durch Benutzergruppe zur Verfügung. Standard ist - ALLE -, die jeder Benutzer admin, um diesen Datensatz anzeigen können.

<BR>
<A NAME="screen_labels-label_hide_field_logs">
<BR>
<B>Beschriftung ausblenden in Anrufprotokolle -</B> Wird ein Etikett zu setzen --- Verberge --- dann sind die Agenten Anruflisten, wenn über die Kampagne aktiviert ist, werden immer noch das Feld und Daten, wenn diese Option gesetzt, um Standard-Y ist N.

<BR>
<A NAME="screen_labels-default_field_labels">
<BR>
<B>Standard Feldbezeichner -</B> Diese 19 Feldern können Sie den Namen gesetzt, wie es in der Agenten-Oberfläche angezeigt als auch die Verwaltungs modify Blei-Seite. Der Standardwert ist leer, was die hartcodierte Standardwerte in das Agenten-Interface nutzen werden. Sie können auch ein Label --- Verberge --- sowohl das Etikett und das Feld zu verstecken eingestellt.



<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUS_CATEGORIESTABELLE</FONT></B><BR><BR>
<A NAME="status_categories">
<BR>
<B>Durch den Gebrauch von Systemstatuskategorien, können Sie diezusammen gruppieren Status, um statistische Analyse auf einer GruppeStatus zuzulassen. Die Kategorie Identifikation muß 2-20 Buchstabenlang ohne Räume sein, muß der Name 2-50 Buchstaben lang sein, istdie Beschreibung wahlweise freigestellt und TimeonVDAD Anzeigedefiniert, ob dieser Status einer von bis zu 4 Status ist, die auf derZeit auf VDAD Realzeitreport errechnet werden und angezeigt werdenkönnen.</B> Der Verkauf Kategorie und Dead Lead Kategorie sind auch die von den US-Vorschlag, wenn die Analyse Liste Statistiken.



<BR><BR><BR><BR>
<?php
if ($SSallow_emails>0)
	{
?>
<B><FONT SIZE=3>EMAIL ACCOUNTS</FONT></B><BR><BR>
<A NAME="email_accounts">
<BR>
<B>Die E-Mail-Konten Management Abschnitt können Sie die E-Mail-Konto-Einstellungen, mit denen Sie haben E-Mail-Nachrichten in Ihr System kommen und behandelt werden, als ob sie Anrufe an Agenten waren, werden zu erstellen, kopieren und löschen. E-Mail Konten müssen von Ihnen und einem E-MAIL SERVICE Anbieter festgelegt werden -, die nicht von diesem Modul IS.</B>

<BR>
<A NAME="email_accounts-email_account_id">
<BR>
<B>E-Mail-Konto-ID -</B> Dies ist der kurze Name des E-Mail-Konto, es ist nicht editierbar nach anfänglichen Vorlage darf keine Leerzeichen enthalten und muss zwischen 2 und 20 Zeichen lang sein.

<BR>
<A NAME="email_accounts-email_account_name">
<BR>
<B>E-Mail Account Name -</B> Dies ist der vollständige Name des E-Mail-Konto, muss er zwischen 2 und 30 Zeichen lang sein. 

<BR>
<A NAME="email_accounts-email_account_active">
<BR>
<B>Aktive -</B> Diese legt fest, ob dieses Konto für neue E-Mails überprüft werden, um in den Dialer geladen werden. 

<BR>
<A NAME="email_accounts-email_account_description">
<BR>
<BEmailKonto Beschreibung -</B> This allows for a lengthy description, if needed, of the email account.  255 characters max. 

<BR>
<A NAME="email_accounts-email_account_type">
<BR>
<B>E-Mail Account -</B> Specifies whether the account is used for inbound or outbound email messages.  Should be set to INBOUND. 

<BR>
<A NAME="email_accounts-admin_user_group">
<BR>
<B>Admin Benutzer-Gruppe -</B> Dies ist die administrative Gruppe für das Inbound-Gruppe, ermöglicht diese Admin Betrachten dieser in-group von Benutzergruppe beschränkt. Standard ist - ALLE -, die jeder Benutzer admin, dies in-group sehen können.  

<BR>
<A NAME="email_accounts-protocol">
<BR>
<B>E-Mail-Konto-Protokoll -</B> This is the email protocol used by the account you are setting up access to.  Currently only IMAP and POP3 accounts are supported.  

<BR>
<A NAME="email_accounts-email_replyto_address">
<BR>
<BEmailReply-to Adresse -</B> The email address of the account you are setting up access to.  Replies to email messages from the agent interface will read as coming from this address.  

<BR>
<A NAME="email_accounts-email_account_server">
<BR>
<B>E-Mail-Konto Server -</B> Die E-Mail-Server, dass das Konto auf untergebracht.  

<BR>
<A NAME="email_accounts-email_account_user">
<BR>
<BEmailKonto Benutzername -</B> The login used to access this account.  Usually it's the portion of the reply-to address before the -at- symbol.  

<BR>
<A NAME="email_accounts-email_account_pass">
<BR>
<B>E-Mail Konto Passwort -</B> The password used to access this account.  This is usually set at the time the email account is created.  

<BR>
<A NAME="email_accounts-email_frequency_check_mins">
<BR>
<BEmailFrequency Check Rate (mins) -</B> How often this email account should be checked.  The highest rate of frequency at the moment is five minutes; some email providers will not allow more than three login attempts in fifteen minutes before locking the account for an indeterminate amount of time.  

<BR>
<A NAME="email_accounts-in_group">
<BR>
<B>In-Gruppen-ID -</B> Die In-Fraktion, dass E-Mails an gesendet werden.   

<BR>
<A NAME="email_accounts-default_list_id">
<BR>
<B>Standard Liste ID -</B> Die Liste führt die ID wird in gegebenenfalls eingefügt werden.  

<BR>
<A NAME="email_accounts-call_handle_method">
<BR>
<B>In-Group Call Handle-Methodee -</B> Dies ist die Aktion, die ergriffen werden, wenn eine neue E-Mail wird auf dem Konto gefunden werden. E-MAIL bedeutet, dass alle E-Mail-Nachrichten werden in der Liste Tabelle als neue Führung eingesetzt werden. EMAILLOOKUP wird die gesamte Liste Tabelle für die E-Mail-Adresse in das E-Mail-Spalte zu suchen - wenn die Leitung gefunden wird, dass Blei-ID-Liste in der Aufzeichnung, die in die Tabelle email_liste geht verwendet werden. EMAILLOOKUPRC macht das gleiche, aber es wird nur nach Listen an die in der Kampagne Feld ID In-Gruppe unter ausgewählte Kampagne gehören. EMAILLOOKUPRL sucht nur eine bestimmte Liste, die die einen in die In-Group Liste Identifikation Feld unten eingetragen ist.  

<BR>
<A NAME="email_accounts-agent_search_method">
<BR>
<B>In-Gruppe-Agent Suchmethode -</B> Der Agent Suchverfahren von der Inbound-Gruppe verwendet werden, LO Lastverteilte-Overflow ist und werden versuchen, den Anruf an einen Agenten auf dem lokalen Server zu senden, bevor Sie sie an einen Agenten auf einem anderen Server zu senden, ist LB Lastverteilte und wird versuchen, den Anruf an den nächsten Agenten, egal auf welchem ​​Server sie sind zu senden, SO ist Server-Only und nur versuchen, die Anrufe auf die Agenten auf dem Server zu senden, dass der Anruf einging. Standard ist LB. <B>IS THIS NECESSARY?</B>  

<BR>
<A NAME="email_accounts-ingroup_list_id">
<BR>
<B>In-Group Liste Identifikation -</B> Dies ist die Liste ID, die verwendet werden, um für ein Spiel in dem gesucht werden soll.  

<BR>
<A NAME="email_accounts-ingroup_campaign_id">
<BR>
<B>In-Gruppe Kampagne Identifikation -</B> Dies ist die Kampagnen-ID, die verwendet werden, um für ein Spiel in dem gesucht werden soll.  




<BR><BR><BR><BR>
<?php } ?>
<B><FONT SIZE=3>CUSTOM TEMPLATE MAKER</FONT></B><BR><BR>
<A NAME="template_maker">
<BR>
Die benutzerdefinierte Vorlage Maker können Sie Ihre eigene Datei-Layout für die Verwendung mit der Liste loader zu definieren und auch zu löschen, falls erforderlich. Wenn Sie häufig Dateien, die in einem einheitlichen Layout andere als die Standard-Layout sind hochladen, können Sie finden dieses Tool hilfreich. Die gespeicherte Layout wird auf jedem hochgeladenen Datei es passt zu arbeiten, unabhängig von Dateityp oder Trennzeichen.

<BR>
<A NAME="template_maker-create_template">
<BR>
<B>Erstellen Sie eine neue Vorlage - </B>In order to begin creating your new listloader template, you must first load a lead file that has the layout you wish to create the template for.  Click "Choose file", and open the file on your computer you wish to use.  This will upload a copy to your server and process it to determine the file type and delimiter (for TXT files).

<BR>
<A NAME="template_maker-delete_template">
<BR>
<B>Löschentemplate -</B> If you have a template you no longer use or you mis-entered information on it and would like to re-enter it, select the template from the drop-down menu and click "Vorlage löschen".

<BR>
<A NAME="template_maker-template_id">
<BR>
<B>Vorlagen-ID -</B> This field is where you enter an arbitrary ID for your new custom template.  It must be between 2 and 20 characters and consist of alphanumeric characters and underscores.

<BR>
<A NAME="template_maker-template_name">
<BR>
<B>Template Name -</B> This field is where you enter the name for your new custom template.  Can be up to 30 characters long.

<BR>
<A NAME="template_maker-template_description">
<BR>
<B>Template Description -</B> This field is where you enter the description for your new custom template.  It can be up to 255 characters long.

<BR>
<A NAME="template_maker-list_id">
<BR>
<B>Liste Identifikation -</B> All templates must load their records into a list.  Select a list ID to load leads into from this drop-down list, which will display any lists available to you given your user settings.

<BR>
<A NAME="template_maker-assign_columns">
<BR>
<B>Assigning columns -</B> Once you have loaded a sample lead file matching the layout you want to make into a template and select a list ID to load leads into, all of the available columns from the list table and the custom table for the list you selected (if any) will be displayed here.  Spaltes highlighted in blue are standard columns from the list table.  Spaltes highlighted in pink belong to the custom table for the selected list.  Each column listed has a drop-down menu, which should be populated with the fields from the first row of the sample lead file you uploaded.  Assign the appropriate fields to the appropriate columns and press SUBMIT TEMPLATE to create your template.  You do not need to assign every field to a column, and you do not need to assign every column a field.  For details on the standard list columns, click <a href="#list_loader">HERE</a>.


<BR><BR><BR><BR>

<A NAME="max_stats">
<B><FONT SIZE=3>Maximale Systemspannung Statistik Berichte</FONT></B><BR><BR>
<BR>
<B>Diese Statistiken werden zwischengespeichert Summen, die jeden Tag überall in Echtzeit durch Back-End-Prozesse gespeichert werden. Bei eingehenden Anrufen werden die gesamten Anrufe pro in-group für jeden Anruf, den Prozess, der berechnet betritt berechnet. Für das gesamte System zählt, werden die Summen von Log-Einträgen sowie andere in-group-und Kampagnen-Summen erzeugt. Diese Summen können nicht addieren sich aufgrund der Einstellungen, die Sie in Ihrem System haben, sowie, wenn der Anruf aufgelegt.</B>


<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>QC STATUS CODES</FONT></B><BR><BR>
	<A NAME="qc_status_codes">
	<BR>
	<B>Die Qualitätskontrolle - QC-Funktion hat ihren eigenen Satz von Status-Codes von denen innerhalb der Anrufbearbeitungsfunktionen des Systems zu trennen. QC-Codes sind zwischen 2 und 8 Zeichen lang sein und darf keine Sonderzeichen wie Leerzeichen oder Doppelpunkt. Der QC-Statuscode Beschreibung muss zwischen 2 und 30 Zeichen lang sein. Für diese Funktionen zu arbeiten, müssen Sie haben QC in den Systemeinstellungen aktiviert.</B>
	<?php
	}
?>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Berichte</FONT></B><BR><BR>

<A NAME="agent_time_detail">
<BR>
<B>Agent Time Detail -</B> In this report you can view how much time agents spent on what.<BR>
<U>TIME CLOCK</U> = Time the agent been logged in to the time clock.<BR>
<U>MITTELTIME</U> = Gesamttime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
<U>WAIT</U> = Time the agent waits for a call.<BR>
<U>TALK</U> = Time the agent talks to a customer or is in dead state (<U>DEAD</U> + <U>CUSTOMER</U>).<BR>
<U>DISPO</U> = Time the agent uses at the disposition screen (where the agent picks NI, SALE etc).<BR>
<U>PAUSE</U> = Time the agent is in pause mode (<U>LOGIN</U> + <U>LAGGED</U> + ...).<BR>
<U>DEAD</U> = Time the agent is in a call where the customer has hung up.<BR>
<U>CUSTOMER</U> = Time the agent is in a live call with a customer.<BR>
- The next table is pause codes and their time.<BR>
<U>LOGIN</U> = The pause code when going from login directly to pause.<BR>
<U>LAGGED</U> = The time the agent had some network problem or similar.<BR>
<U>ANDIAL</U> = This pause code triggers if the agent been on dispo screen for longer than 1000 seconds.<BR>
and empty is undefined pause code. <BR>

<A NAME="agent_status_detail">
<BR>
<B>Agent Status Detail -</B> In this report you can view what and how many statuses has been selected by the agents.<BR>
<U>CALLS</U> = Gesamtnumber of calls sent to the user.<BR>
<U>CIcalls</U> = Gesamtnumber of call where there was a Human Antwort which is set under "Admin" -> "System Status".<BR>
<U>DNC/CI%</U> = How much in percent DNC (Do Not Call) per Human Antworts.<BR>
And the rest is justSystemstatusthat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Status".<BR>

<A NAME="agent_performance_detail">
<BR>
<B>Agent Performance Detail -</B> This is a combination of Agent Time Detail and Agent Status Detail.<BR>
(Statistics related to handling of calls only)<BR>
<U>CALLS</U> = Gesamtnumber of calls sent to the user.<BR>
<U>TIME</U> = Gesamttime of these (<U>PAUSE</U> + <U>WAIT</U> + <U>TALK</U> + <U>DISPO</U>).<BR>
<U>PAUSE</U> = Amount of time being paused in related to call handling.<BR>
<U>AVG</U> means Average so everything -AVG is for example amount of PAUSE-time divided by total number of calls: (<U>PAUSE</U> / <U>CALLS</U> = <U>PAUSAVG</U>)<BR>
<U>WAIT</U> = Time the agent waits for a call.<BR>
<U>TALK</U> = Time the agent talks to a customer or is in dead state (<U>DEAD</U> + <U>CUSTOMER</U>).<BR>
<U>DISPO</U> = Time the agent uses at the disposition screen (where the agent picks NI, SALE etc).<BR>
<U>DEAD</U> = Time the agent is in a call where the customer has hung up.<BR>
<U>CUSTOMER</U> = Time the agent is in a live call with a customer.<BR>
And the rest is justSystemstatusthat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Status".<BR>
- Next table is Pause Codes.<BR>
<U>GESAMTMENGE</U> = Gesamttime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
<U>NONPAUSE</U> = Everything except pause (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U>).<BR>
<U>PAUSE</U> = Only Pause.<BR>
- The last table is pause codes and their time (like "Agent Time Detail").<BR>
<U>LOGIN</U> = The pause code when going from login directly to pause.<BR>
<U>LAGGED</U> = The time the agent had some network problem or similar.<BR>
<U>ANDIAL</U> = This pause code triggers if the agent been on dispo screen for longer than 1000 seconds.<BR>
and empty is undefined pause code. <BR>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Nanpa cellphone filtering</FONT></B><BR><BR>


<A NAME="nanpa-running">
<BR>
<B>Currently running NANPA scrubs -</B> Anzeiges a log of the currently running scrubs, including: Start Time, Leads Count, Filter Count, Status Line, Time to Complete, Field Updated, and Field excluded
<BR><BR>
<A NAME="nanpa-settings">
<BR>
<B>Inactive Listen -</B> Contains all the unaktivierte Listen, that are eligible to be scrubbed.  A list can only be scrubbed if Aktivis set to N on the list.
<BR><BR>
<B>Field to Update -</B> Indicates which lead field will contain the scrub result -Cellphone, Landline, or Invalid-.  If NONE is selected it will use the default field Land_Code.
<BR><BR>
<B>Exclusion Field -</B> In conjunction with Exclusion Value allows administrators to indicate leads that are not to be scanned. This field indicates which list field contains the scrub result.
<BR><BR>
<B>Exclusion Value -</B> In conjunction with Exclusion Field allows administrators to indicate leads that are not to be scanned. This field indicates which scrub result -either Cellphone, Landline, or Invalid- should be ignored.
<BR><BR>
<B>List Conversions -</B> Administrators can indicate a list ID in any of the fields -Cellphone, Landline, or Invalid- and the scrub will move all the corresponding stamped leads to that list.
<BR><BR>
<B>Time until Activation -</B> allows The administrator to set how long, in the future, the scrub will occur.
<BR><BR>
<A NAME="nanpa-log">
<BR>
<B>View Past Scrubs -</B> -link at the bottom- Click to see a log of all previous scrubs, including: Start Time, Leads Count, Filter Count, Status Line, Time to Complete, Field updated, Field Excluded, and total leads in each category.

 

<BR><BR><BR><BR><BR><BR><BR><BR>
<BR><BR><BR><BR><BR><BR><BR><BR>
DAS ENDE
</TD></TR></TABLE></BODY></HTML>
<?php
exit;

#### END HELP SCREENS
