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
echo "<TABLE WIDTH=98% BGCOLOR=#E6E6E6 cellpadding=2 cellspacing=0><TR><TD ALIGN=LEFT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=4><B>ADMINISTRATION: AIDE<BR></B></FONT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2><BR><BR>\n";

?>
<B><FONT SIZE=3>Tableau des utilisateurs</FONT></B><BR><BR>
<A NAME="users-user">
<BR>
<B>Utilisateur ID -</B> Ce champ est où vous mettez le nombre des utilisateurs d identité, peut être jusqu à 8 chiffres, doit être au moins 2 caractères.

<BR>
<A NAME="users-pass">
<BR>
<B>Password -</B> Ce champ est où vous mettez le mot de passe de l utilisateur. Doit être au moins 2 caractères. Un mot de passe de l utilisateur doit comprendre au moins 8 caractères et avoir minuscules et majuscules, ainsi que d au moins un nombre.

<BR>
<A NAME="users-force_change_password">
<BR>
<B>Forcer Changer mot de passe -</B> Si cette option est réglée sur Y, alors l'utilisateur sera invité à modifier leur mot de passe la prochaine fois qu'ils se connectent à la page Web d'administration. Par défaut est N.

<BR>
<A NAME="users-last_login_date">
<BR>
<B>Dernière connexion Infos -</B> Cela montre la dernière date et l heure de tentative de connexion, et si il ya eu une récente tentative de connexion a échoué. Si cela modifier la forme de l utilisateur est soumis, le compteur de tentative de connexion a échoué sera réinitialisé et l agent peut immédiatement tenter de vous reconnecter. Si un agent a 10 tentatives de connexion infructueuses dans une rangée, ils ne peuvent pas tenter de se connecter à nouveau pendant au moins 15 minutes, à moins de leur compte est remis à zéro manuellement.

<BR>
<A NAME="users-full_name">
<BR>
<B>Nom complet -</B> Ce champ est où vous mettez le nom complet des utilisateurs. Au moins doit être de 2 caractères.

<BR>
<A NAME="users-user_level">
<BR>
<B>Niveau de l'utilisateur -</B> Ce menu vous permet de sélectionner le niveau de l utilisateur de l utilisateur. Doit être un niveau de 1 pour vous connecter à l écran de l agent, doit être de niveau supérieur à 2 pour connecter en tant que plus proche, doit être au niveau de l utilisateur 8 ou plus pour entrer dans la section Web admin.

<BR>
<A NAME="users-user_group">
<BR>
<B>Groupe utilisateur -</B> Ce menu vous permet de sélectionner le groupe d utilisateurs que cet utilisateur appartient. Il existe plusieurs fonctions de l écran de l agent qui peuvent être contrôlés par les paramètres du groupe d utilisateurs. Si ce champ est laissé vide, l utilisateur ne peut pas se connecter à l écran de l agent.

<BR>
<A NAME="users-phone_login">
<BR>
<B>Login du téléphone -</B> C est ici que vous pouvez configurer un téléphone valeur de connexion par défaut lorsque l utilisateur se connecte l écran de l agent. Cette valeur va peupler la phone_login lorsque l utilisateur se connecte avec son utilisateur passe-campagne dans l écran l agent de connexion automatique.

<BR>
<A NAME="users-phone_pass">
<BR>
<B>Mot de passe du téléphone -</B> C est ici que vous pouvez configurer un téléphone valeur passe par défaut lorsque l utilisateur se connecte l écran de l agent. Cette valeur va peupler la phone_pass lorsque l utilisateur se connecte avec son utilisateur passe-campagne dans l écran l agent de connexion automatique.

<BR>
<A NAME="users-active">
<BR>
<B>Active -</B> Ce champ détermine si l utilisateur est actif dans le système et peut se connecter comme un agent ou manager. Par défaut est Y.

<BR>
<A NAME="users-voicemail_id">
<BR>
<B>ID vocale -</B> Il s'agit de la boîte vocale que les appels seront dirigés vers dans un AGENTDIRECT en groupe à la fois si la baisse en groupe a la méthode de la goutte réglé sur VOCAL et le champ de messagerie vocale mis au AGENTVMAIL.

<BR>
<A NAME="users-optional">
<BR>
<B>Courrier électronique, code utilisateur et territoire -</B> Ce sont des champs optionnels.

<BR>
<A NAME="users-hotkeys_active">
<BR>
<B>Raccourcis clavier actifs -</B> Si cette option est à 1 elle autorise l'utilisateur à utiliser les raccourcis clavier the agent screen.

<BR>
<A NAME="users-agent_choose_ingroups">
<BR>
<B>Choix des Ingroups -<\/B> Cette option, si elle est mise à 1 autorise l'utilisateur à choisir les ingroups depuis lesquels il recevra des appels quand il est connecté dans une campagne de type CLOSER ou INBOUND sinon le manager devra le définir dans l'écran de détails de l'utiliseur sur la page d'amiministration.

<BR>
<A NAME="users-agent_choose_blended">
<BR>
<B>Agent Choisissez Blended -</B> Cette option si elle est définie à 1 permet à l'utilisateur de choisir si l'agent a son ensemble campagne pour mélangés ou non, et si non, le réglage par défaut sera utilisé mélangé. Par défaut est 1 pour activé.

<BR>
<A NAME="users-agent_choose_territories">
<BR>
<B>Agent Choisir Territoires -</B> Cette option si la valeur 1 permet à l-utilisateur de choisir les territoires qu-ils vont recevoir des appels à partir de leur connexion à un manuel ou INBOUND_MAN campagne. Sinon, l-utilisateur sera configuré pour utiliser l-ensemble des territoires qu-ils sont définis comme appartenant à l-utilisateur dans les Territoires Section administrative.

<BR>
<A NAME="users-scheduled_callbacks">
<BR>
<B>Scheduled Callbacks -</B> Cette option permet à un agent à disposition un appel comme CALLBK et choisir la date et l-heure à laquelle le plomb sera ré-activé.

<BR>
<A NAME="users-agentonly_callbacks">
<BR>
<B>Rappel Agent-Seulement -</B> Cette option autorise un agent à assigner un rappel pour lequel il sera le seul a pouvoir rappeler le client. Cela autorise aussi l'agent à voir sa liste de rappel quand il le désire.

<BR>
<A NAME="users-agentcall_manual">
<BR>
<B>Appel manuel de l'agent -</B> Cette option permet à un agent de saisir manuellement un nouveau chef de file dans le système et de les appeler. Cela permet également la vocation de tout numéro de téléphone à partir de l écran de leur agent et met qui appellent dans leur session. Utilisez cette option avec prudence.

<BR>
<A NAME="users-agentcall_email">
<BR>
<B>Agent Call Email -</B> This option is disabled.

<BR>
<A NAME="users-agent_recording">
<BR>
<B>Agent d enregistrement -</B> Cette option peut empêcher un agent de faire des enregistrements après qu ils se connectent à l écran de l agent. Cette option doit être activée pour que l écran de l agent de suivre les paramètres d enregistrement de la campagne.

<BR>
<A NAME="users-agent_transfers">
<BR>
<B>Transferts de l agent -</B> Cette option peut empêcher un agent de l ouverture du transfert - session de conférence dans l écran de l agent. Si cette option est désactivée, l agent ne peut pas appeler de tiers ou transfert aveugle des appels.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-closer_default_blended">
	<BR>
	<B>Closer Default Blended -<\/B> Cette option coche simplement par défaut la checbox Blended sur un écran de connection d'une campagne de type CLOSER.
	<?php
	}
?>

<BR>
<A NAME="users-agent_recording_override">
<BR>
<B>Remplacer l agent d enregistrement -</B> Cette option supplante tout ce l option est dans la campagne pour l enregistrement. HANDICAPÉS ne sera pas remplacer le paramètre d enregistrement de la campagne. Ne sera jamais désactiver l enregistrement sur le client. ONDEMAND est la valeur par défaut et permet à l agent pour démarrer et arrêter l enregistrement en tant que de besoin. ALLCALLS va commencer l enregistrement sur le client chaque fois qu un appel est envoyé à un agent. ALLFORCE démarre l enregistrement sur le client chaque fois qu un appel est envoyé à un agent donnant aucune option pour arrêter l enregistrement de l agent. Pour ALLCALLS et ALLFORCE il ya une option pour utiliser le délai d enregistrement de réduire enregistrements très courts et de réduire la charge du système.

<BR>
<A NAME="users-agent_shift_enforcement_override">
<BR>
<B>Agent de l'exécution Shift Override -</B> Ce paramètre remplace les utilisateurs quel que soit le groupe d'utilisateurs a mis en application de la loi pour Maj. HANDICAPES utilise l'utilisateur groupe. OFF ne fera pas appliquer à tous les changements. START ne faire respecter les temps de connexion, mais n'aura pas d'incidence sur un agent qui est en cours d'exécution sur leurs heures de travail si elles sont déjà logged in ALL appliquent passer l'heure de début et se connecter à un agent, après, ils courent sur la fin de leur quart de temps. La valeur par défaut est désactivé.

<BR>
<A NAME="users-agent_call_log_view_override">
<BR>
<B>Agent Call Log Voir Override -</B> Ce paramètre remplace quel que soit le groupe d'utilisateurs utilisateurs s'est fixé pour View Log Agent d'appel. HANDICAPÉS utilisera le réglage du groupe utilisateur. N ne permettra pas montrer le journal des appels utilisateurs. Y permettra montrant le journal des appels de l'utilisateur. Par défaut est DISABLED.

<BR>
<A NAME="users-agent_lead_search_override">
<BR>
<B>Override Recherche Agent principal -</B> Ce paramètre remplace quelle que soit la campagne s'est fixé pour principal agent de recherche. NOT_ACTIVE utilisera le paramètre de campagne. ENABLED permettra recherche de plomb et de DISABLED ne permettra pas la recherche de plomb. Par défaut est NOT_ACTIVE. LIVE_CALL_INBOUND permettra recherche pendant un certain temps d'avance sur un appel entrant uniquement. LIVE_CALL_INBOUND_AND_MANUAL permettra recherche pendant un certain temps d'avance sur un appel entrant ou pendant la pause. Quand Lead Recherche est utilisé sur un appel entrant direct, le chef de file de l'appel quand il est allé à l'agent sera changé pour un statut de LSMERG, et les journaux de l'appel sera modifié pour créer un lien vers l'agent principal choisi à la place.

<BR>
<A NAME="users-alert_enabled">
<BR>
<B>Alerte Enabled -</B> Ce champ indique si l agent a des alertes de navigateur Web compatibles pour quand les appels entrent dans leur séance de l écran de l agent. Défaut est 0 pour NON.

<BR>
<A NAME="users-allow_alerts">
<BR>
<B>Permettez Alertes -</B> Ce champ vous donne la possibilité de permettre à des alertes du navigateur de l agent à être activés par l agent lorsque les appels viennent dans leur séance de l écran de l agent. Défaut est 0 pour NON.

<BR>
<A NAME="users-preset_contact_search">
<BR>
<B>Contact Recherche Preset -</B> Si l'utilisateur est connecté à une campagne qui a Presets de transfert prévues à l'CONTACTS, alors ce paramètre peut désactiver la recherche de contact pour cet utilisateur uniquement. Par défaut est NOT_ACTIVE qui utilisera quelle que soit la campagne est mise en.

<BR>
<A NAME="users-max_inbound_calls">
<BR>
<B>Appels entrants Max -</B> Si ce paramètre est réglé sur un nombre supérieur à 0, alors il sera le nombre maximum d appels entrants que l agent peut traiter tous les groupes entrants en une seule journée. Si l agent atteint le nombre maximum d appels entrants, alors ils ne seront pas en mesure de sélectionner des groupes entrants pour prendre les appels de jusqu au lendemain. Ce paramètre remplacera le paramètre de la campagne du même nom. Défaut est 0 pour les handicapés.

<BR>
<A NAME="users-campaign_ranks">
<BR>
<B>La campagne se range - dans cette section vous pouvez définir le rangque un agent aura pour chaque campagne. Ces rangs peuvent êtreemployés pour tenir compte du cheminement préféré d'appel quand leprochain appel d'agent est placé au campaign_rank. Également dans cette section sont le WEB VAR pour chaque campagne. Il s'agit de permettre à chaque agent d'avoir une autre variable de chaîne qui peuvent être ajoutés à la fiche de Web ou SCRIPT onglet URL, simplement en mettant - A - web_vars - B - comme vous le mettre dans tout autre domaine. Un autre domaine dans cette section est de qualité, qui permet pour la campaign_grade_random agent Suivant Appelez la mise à être utilisé. Ce paramètre de qualité va augmenter ou diminuer la probabilité que l'agent recevra l'appel.

<BR>
<A NAME="users-closer_campaigns">
<BR>
<B>Groupes entrants -</B> C'est ici que vous selectionnez les groupes entrants depuis lesquels vous voulez recevoir des appels si vous avez selectionné une campagne de type CLOSER. Vous pourrez également placer le rang, ou le niveau de compétence,dans cette section pour chacun des groupes d'arrivée aussi bien quepouvoir voir le nombre d'appels reçus de chaque groupe d'arrivéepour cet agent spécifique. En outre dans cette section est la capacité de donner à l'agent unrang pour chaque groupe d'arrivée. Ces rangs peuvent être employéspour le cheminement préféré d'appel quand cette option est choisiedans l'écran de dans-groupe. Également dans cette section sont le WEB VAR pour chaque campagne. Il s'agit de permettre à chaque agent d'avoir une autre variable de chaîne qui peuvent être ajoutés à la fiche de Web ou SCRIPT onglet URL, simplement en mettant - A - web_vars - B - comme vous le mettre dans tout autre domaine.

<BR>
<A NAME="users-alter_custdata_override">
<BR>
<B>Modifications des données des clients pour l'agent -</B> Cette option permet de surcharger, pour un agent précis, l'option de la campagne concernant la modification des données du clients. NOT_ACTIVE utilisera la valeur présente dans l'option de la campagne. ALLOW_ALTER autorisera toujours à cet agent de modifier les données du client quelque soit la valeur de l'option dans la campagne. La valeur par défaut est NOT_ACTIVE.

<BR>
<A NAME="users-alter_custphone_override">
<BR>
<B>Alter Agent Client Téléphone Override - </B> Cette option va remplacer ce que l'option est dans la campagne pour modifier le numéro de téléphone du client. NOT_ACTIVE va utiliser quel que soit l'établissement est présent pour la campagne. ALLOW_ALTER toujours de permettre à l'agent de modifier le numéro de téléphone des clients, quel que soit le réglage de la campagne. La valeur par défaut est NOT_ACTIVE.

<BR>
<A NAME="users-custom_one">
<BR>
<B>Custom Utilisateur Fields -</B> Ces cinq champs peuvent être utilisés à des fins diverses, et ils peuvent être archivés dans les adresses formulaire web et des scripts comme user_custom_one et ainsi de suite. Le Custom 5 champ peut être utilisé comme un champ de définir un certain nombre dialplan d'envoyer un appel à partir d'un AGENTDIRECT en groupe si l'utilisateur n'est pas disponible, vous avez juste besoin de mettre AGENTEXT dans le champ Message ou EXTENSION dans le AGENTDIRECT en groupe et le système va chercher la coutume cinq sur le terrain et envoyer l'appel vers ce numéro dialplan.

<BR>
<A NAME="users-alter_agent_interface_options">
<BR>
<B>Modifier les options de l'interface agent -</B> Cette option, si elle est à 1, autorise l'utilisateur administrateur à modifier les otpions de l'interface Agent dans admin.php.

<BR>
<A NAME="users-delete_users">
<BR>
<B>Effacer des utilisateurs -</B> Cette option et mise à 1 pour autoriser l'utilisateur à effacer d'autres utilisateurs qui ont un niveau inférieur ou égal depuis le système.

<BR>
<A NAME="users-delete_user_groups">
<BR>
<B>Supprimer des groupes d'utilisateurs -</B> Cette option et mise à 1 pour autoriser l'utilisateur à effacer les groupes d'utilisateurs depuis le système.

<BR>
<A NAME="users-delete_lists">
<BR>
<B>Supprimer les listes -</B> Cette option, si mis à 1 permet à l utilisateur de supprimer des listes du système.

<BR>
<A NAME="users-delete_campaigns">
<BR>
<B>Supprimer les campagnes -</B> Cette option, si mis à 1 permet à l utilisateur de supprimer les campagnes du système.

<BR>
<A NAME="users-delete_ingroups">
<BR>
<B>Supprimer les in-groups -</B> Cette option, si mis à 1 permet à l utilisateur de supprimer des groupes entrants du système.

<BR>
<A NAME="users-modify_custom_dialplans">
<BR>
<B>Modifier DialPlans mesure -</B> Cette option, si mis à 1 permet à l utilisateur de visualiser et de modifier les entrées DialPlan personnalisés qui sont disponibles dans le menu d appel, les paramètres système et les serveurs écrans de modification.

<BR>
<A NAME="users-delete_remote_agents">
<BR>
<B>Supprimer les agents distants -</B> Cette option, si mis à 1 permet à l utilisateur de supprimer des agents distants du système.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-load_leads">
	<BR>
	<B>Charger des fiches -</B> Cette option, si mis à 1 permet à l utilisateur de charger des listes de plomb dans le tableau de la liste par le biais du Web basé chargeur plomb.
	<?php
	}
if ($SScustom_fields_enabled > 0)
	{
	?>
	<BR>
	<A NAME="users-custom_fields_modify">
	<BR>
	<B>Modifier les champs personnalisés -</B> Cette option, si mis à 1 permet à l'utilisateur de modifier les champs de listes personnalisées.
	<?php
	}
?>

<BR>
<A NAME="users-campaign_detail">
<BR>
<B>Détails de la campagne-</B> Cette option et mise à 1 pour autoriser l'utilisateur à voir et modifier les éléments de l'écran de details de la campagne.

<BR>
<A NAME="users-ast_admin_access">
<BR>
<B>Accès administrateur AGC -</B> Cette option et mise à 1 pour autoriser l'utilisateur à se connecter aux pages d'administration de astGUIclient.

<BR>
<A NAME="users-ast_delete_phones">
<BR>
<B>Supprimer des téléphones AGC -</B> Cette option est mise à 1 pour autoriser l'utilisateur à effacer des téléphones dans les pages d'aministration d'astGUIclient.

<BR>
<A NAME="users-delete_scripts">
<BR>
<B>Supprimer des scripts -</B> Cette option si elle est mise à 1 autorise l'utilisateur à supprimer des scripts de campagne dans l'écran de modification du script.

<BR>
<A NAME="users-modify_leads">
<BR>
<B>Modifier les fiches -</B> Si cette otpion est à 1 elle autorise l'utilisateur à modifier les fiches dans la page des résultats de la recherche de fiches dans la section d'administration.

<?php
if ($SSallow_emails>0)
	{
?>
<BR>
<A NAME="users-modify_email_accounts">
<BR>
<B>Modifier Comptes de messagerie -</B> Cette option, si mis à 1 permet à l'utilisateur de modifier les comptes de messagerie dans la page de gestion de compte de messagerie.
<?php
	}
?>

<BR>
<A NAME="users-change_agent_campaign">
<BR>
<B>Changer l'agent de campagne -</B> Cette option, si elle est mise à 1, permet à l'utilisateur de changer la campagne dans laquelle est connecté un agent alors qu'il y est connecté.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-delete_filters">
	<BR>
	<B>Supprimer le filtres -</B> Cette option permet à l utilisateur d être en mesure de supprimer des filtres de plomb du système.
	<?php
	}
?>

<BR>
<A NAME="users-delete_call_times">
<BR>
<B>SupprimerPériode d'appels -</B> Cette option permet à l utilisateur d être en mesure de supprimer les temps d enregistrements d appels et l Etat fois de dossiers d appel du système.

<BR>
<A NAME="users-modify_call_times">
<BR>
<B>Modification des périodes d'appels -<\/B> Cette option autorise l'utilisateur à voir et modifier les périodes d'appels et les périodes d'appels d'état. Un utilisateur n'a pas besoin de cette option si il a seulement besoin de changer les options des périodes d'appels sur l'écran de campagne.

<BR>
<A NAME="users-modify_sections">
<BR>
<B>Modifications des sections -</B> Cette option autorise l'utilisateur à voir et modifier chaque enregistrement de section. Si cette option a pour valeur 0, l'utilisateur pourra voir la liste des sections, mais pas le détail et ne pourra pas modifier les enregistrements dans cette section.

<BR>
<A NAME="users-view_reports">
<BR>
<B>View Rapports -</B> Cette option permet à l utilisateur de visualiser les rapports Web du système.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="users-qc_enabled">
	<BR>
	<B>QC Activé - </B> Cette option permet à l'utilisateur de se connecter à l'agent de contrôle de la qualité d'écran.

	<BR>
	<A NAME="users-qc_user_level">
	<BR>
	<B>QC Niveau de l'utilisateur - </B> Ce paramètre définit ce que l'agent de contrôle de la qualité est au niveau utilisateur. Ce qui déterminera le niveau de fonctionnalité de l'agent dans la section QC:<BR>
	1 - Rien Modifier<BR>
	2 - Modifier Rien Sauf Etat<BR>
	3 - Modifier tous les Domaines<BR>
	4 - Vérifiez Premier Cycle de QC<BR>
	5 - Voir QC Statistiques<BR>
	6 - Possibilité de modifier fini records<BR>
	7 - Level Manager<BR>

	<BR>
	<A NAME="users-qc_pass">
	<BR>
	<B>Record QC Pass - </B> Cette option permet à l'agent de préciser que le dossier a franchi le premier tour de Québec, après avoir examiné le dossier.

	<BR>
	<A NAME="users-qc_finish">
	<BR>
	<B>Record QC Finish - </B> Cette option permet à l'agent de préciser que le dossier a terminé la deuxième ronde de Québec après avoir examiné le dossier transmis.

	<BR>
	<A NAME="users-qc_commit">
	<BR>
	<B>Record QC Commit - </B> Cette option permet à l'agent de préciser que le record a été commise dans QC. Il ne peut plus être modifié par n'importe qui.
	<?php
	}
?>

<BR>
<A NAME="users-add_timeclock_log">
<BR>
<B>Ajouter Log timeclock Record - </B> Cette option permet à l'utilisateur d'ajouter des enregistrements à l'timeclock journal.

<BR>
<A NAME="users-modify_timeclock_log">
<BR>
<B>Modifier timeclock Connexion Record - </B> Cette option permet à l'utilisateur de modifier des enregistrements dans le journal timeclock.

<BR>
<A NAME="users-delete_timeclock_log">
<BR>
<B>Supprimer timeclock Connexion Record - </B> Cette option permet à l'utilisateur de supprimer des enregistrements dans les journaux timeclock.

<BR>
<A NAME="users-vdc_agent_api_access">
<BR>
<B>Agent API Access -</B> Cette option permet au compte d être utilisé avec l agent et non-agent commandes API.

<BR>
<A NAME="users-manager_shift_enforcement_override">
<BR>
<B>Shift Manager exécution Override -</B> Ce paramètre, s'il est à 1 permettra d'entrer dans un gestionnaire d'utilisateur et leur mot de passe sur l'écran d'un agent pour l'emporter sur l'évolution des restrictions sur un agent de la session si l'agent essaie de se connecter à l'extérieur de leur quart de travail. La valeur par défaut est 0.

<BR>
<A NAME="users-download_lists">
<BR>
<B>Download Listes -</B> Ce paramètre, s'il est à 1 permet à un gestionnaire de téléchargement, cliquez sur le lien de la liste au bas de l'écran de modification d'une liste d'exporter la totalité du contenu d'une liste de données à un fichier plat. La valeur par défaut est 0.

<BR>
<A NAME="users-export_reports">
<BR>
<B>Rapports d'exportation -</B> Ce paramètre si elle est définie à 1, permettre à un gestionnaire d'accéder à l'appel d'exportation et les rapports de plomb sur l'écran RAPPORTS. Par défaut est 0. Pour l'exportation Appels et mène des rapports, l'ordre des champs ci-dessous est utilisé pour les exportations: <BR>call_date, phone_number_dialed, status, user, full_name, campaign_id/in-group, vendor_lead_code, source_id, list_id, gmt_offset_now, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, length_in_sec, user_group, alt_dial/queue_seconds, rank, owner

<BR>
<A NAME="users-delete_from_dnc">
<BR>
<B>Supprimer From Listes DNC -</B> Ce paramètre si mis à 1 permettra un gestionnaire pour supprimer les numéros de téléphone des listes DNC dans le système.

<BR>
<A NAME="users-realtime_block_user_info">
<BR>
<B>En temps réel Infos Message Bloquer -</B> Ce paramètre si elle est définie à 1 permet de bloquer les informations utilisateur et de la station ne sera pas affichée dans le rapport en temps réel. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="users-modify_same_user_level">
<BR>
<B>Modifier niveau de l'utilisateur même -</B> Ce paramètre s'applique uniquement au niveau 9 utilisateurs. Si elle est activée elle permet à l'utilisateur de niveau 9 à modifier leurs paramètres propres ainsi que d'autres au niveau 9 utilisateurs. Par défaut est 1 pour activé.

<BR>
<A NAME="users-admin_hide_lead_data">
<BR>
<B>Administrateur de données masquer plomb -</B> Ce paramètre s'applique uniquement au niveau 7, 8 et 9 utilisateurs. Si elle est activée elle remplace les données de plomb clients dans les nombreux rapports et des écrans dans le système avec Xs. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="users-admin_hide_phone_data">
<BR>
<B>Administrateur masquer les données du téléphone -</B> Ce paramètre s'applique uniquement au niveau 7, 8 et 9 utilisateurs. Si elle est activée elle remplace les numéros de téléphone des clients dans les nombreux rapports et des écrans dans le système avec Xs. Les paramètres chiffres seront montrent que les chiffres X derniers du numéro de téléphone. Par défaut est 0 pour les handicapés.






<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAGNES TABLE</FONT></B><BR><BR>
<A NAME="campaigns-campaign_id">
<BR>
<B>ID de la campagne -</B> C'est un nom court pour la campagne, il n'est pas modifiable après validation, il ne peut contenir d'espaces et doit contenir entre 2 et 8 caractèresh.

<BR>
<A NAME="campaigns-campaign_name">
<BR>
<B>Nom de la campagne -</B> C'est la description de la campagne, elle doit contenir entre 6 et 40 caractères.

<BR>
<A NAME="campaigns-campaign_description">
<BR>
<B>Description de la campagne -</B> C'est un champ de note pour la campagne, il est optionel et ne peut contenir plus de 255 caractères.

<BR>
<A NAME="campaigns-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cette campagne, ce qui permet d'administration visualisation de cette campagne ainsi que les listes assignés à cette campagne pour être limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin avec les autorisations des utilisateurs du groupe de campagne pour voir cette campagne.

<BR>
<A NAME="campaigns-campaign_changedate">
<BR>
<B>Date de changement de la campagne -</B> C'est la dernière fois que les réglages de la campagne ont été modifié.

<BR>
<A NAME="campaigns-campaign_logindate">
<BR>
<B>Date de la dernière connection à la campagne -</B> C'est la dernière fois qu'un agent s'est connecté à la campagne.

<BR>
<A NAME="campaigns-campaign_calldate">
<BR>
<B>Dernière campagne téléphonique Date -</B> Il s-agit de la dernière fois qu-un appel a été traitée par un mandataire inscrit dans cette campagne.

<BR>
<A NAME="campaigns-max_stats">
<BR>
<B>Maximum quotidien Statistiques -</B> Ce sont des statistiques qui sont générées par le système chaque jour pendant toute la journée jusqu'à ce que la minuterie de fin de journée, car il est réglé dans les paramètres système. Ces nombres sont générés à partir des grumes dans le système pour permettre l'affichage beaucoup plus rapide. Les stats sont inclus sont - Nombre total d'appels, les agents maximum, maximum des appels entrants, appels sortants maximum.

<BR>
<A NAME="campaigns-campaign_stats_refresh">
<BR>
<B>Campagne Stats Refresh -</B> Cette option permet de forcer une campagne appelant les statistiques rafraîchir, même si la campagne n est pas active.

<BR>
<A NAME="campaigns-realtime_agent_time_stats">
<BR>
<B>Real-Time Temps Stats Agent -</B> Mettre cette option à quoi que ce soit, mais HANDICAPÉS permettra la collecte de statistiques en temps des agents pour aujourd'hui pour cette campagne, qui sont visibles au travers du rapport en temps réel. Les appels sont la moyenne appels traités par agent, attente est le temps moyen d'attente d'agent, le CUST est le temps moyen de clients discours, ACW est la moyenne Après le temps de travail Appel, PAUSE est le temps de pause en moyenne. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-active">
<BR>
<B>Active -</B> C'est ici que vous indiquez si la campagne est active ou non. Si elle est inactive personne ne peut se connecter à cette campagne.

<BR>
<A NAME="campaigns-park_ext">
<BR>
<B>Parc de la Musique-sur-Hold -</B> C'est là que vous pouvez définir le contexte de la musique en attente pour cette campagne. Assurez-vous que vous sélectionnez une musique valable sur le contexte de maintien de la liste de sélection et que le contexte que vous avez sélectionné les fichiers valides a en elle. Par défaut est par défaut.

<BR>
<A NAME="campaigns-park_file_name">
<BR>
<B>Park File Name -</B> NOT USED.

<BR>
<A NAME="campaigns-web_form_address">
<BR>
<B>Formulaire Web -</B> C'est ici que que vous pouvez mettre une page web personnalisée qui sera ouverte quand l'utilisateur cliquera sur le bouton FORMULAIRE WEB. Pour personnaliser la chaîne de requête, après le formulaire Web, il suffit de commencer avec le formulaire web, VAR et puis l'URL que vous voulez utiliser, en remplaçant les variables avec les noms des variables que vous voulez utiliser - A - phone_number - B -- comme dans l'onglet SCRIPTS. Si vous voulez utiliser des champs personnalisés dans un formulaire Web l'adresse, vous devez ajouter &CF_uses_custom_fields=Y dans le cadre de votre URL.

<BR>
<A NAME="campaigns-web_form_target">
<BR>
<B>Formulaire Web Public-</B> C'est là que vous pouvez définir la page Web personnalisée cadre que le formulaire web sera ouverte lorsque l'utilisateur clique sur le bouton de formulaire Web. La valeur par défaut est _blank.

<BR>
<A NAME="campaigns-allow_closers">
<BR>
<B>Autoriser les Closers -</B> C'est ici que vous pouvez définir si les utilisateurs de cette campagne pouront envoyer l'appel vers une campagne de type closer.

<?php
if ($SSallow_emails > 0) 
		{
?>
	<BR>
	<A NAME="campaigns-allow_emails">
	<BR>
	<B>Laisser Emails -</B> C'est là que vous pouvez définir si les utilisateurs de cette campagne seront en mesure de recevoir des e-mails entrants, en plus des appels téléphoniques.
<?php
		}
?>
<BR>
<A NAME="campaigns-default_xfer_group">
<BR>
<B>Groupe de transfert de défaut - ce champ est le Dans-Groupe dedéfaut qui sera automatiquement choisi quand l'agent va à l'armaturede transférer-conférence dans leur interface d'agent.

<BR>
<A NAME="campaigns-xfer_groups">
<BR>
<B>Groupes laissés de transfert - avec ces listes de checkbox vouspouvez choisir les groupes aux lesquels les agents dans cette campagnepeuvent transférer des appels. Permettez Closers doit être permispour que cette option révèle.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-campaign_allow_inbound">
	<BR>
	<B>Permettez d'arrivée et mélangé - c'est où vous pouvez placer siles utilisateurs de cette campagne auront l'option pour prendre desappels d'arrivée avec cette campagne. Si vous voulez faire d'arrivéeet en partance mélangés puis ceci doit être placé à Y. Si vousvoulez seulement faire composer en partance sur cette campagne placiezceci à N. Default est N.

	<BR>
	<A NAME="campaigns-dial_status">
	<BR>
	<B>Statut de l'appel -<\/B> C'est ici que vous indiquez les statuts que vous voulez appeler dans les listes actives pour la campagne suivante. Pour ajouter un autre statut d'appel, selectionnez le dans la liste déroulante et cliquez sur AJOUTER. Pour supprimer un de ces statuts, cliquez sur le lien SUPPRIMER à côté du statut que vous voulez supprimer.

	<BR>
	<A NAME="campaigns-lead_order">
	<BR>
	<B>Ordre de la liste -</B> C'est dans ce menu que vous selectionnez comment les fiches qui correspondent aux statuts selectionnés ci-dessus seront mises dans le distributeur de fiches:
	 <BR> &nbsp; - DOWN: sélectionnez les premiers conducteurs chargés dans le tableau de la liste
	 <BR> &nbsp; - UP: sélectionnez les dernières pistes chargées dans le tableau de la liste
	 <BR> &nbsp; - UP PHONE: selectionne le plus grand numéro de téléphone et continue vers le bas
	 <BR> &nbsp; - DOWN PHONE: selectionne le plus petit numéro de téléphone et continue vers le haut
	 <BR> &nbsp; - UP LAST NAME: commence avec les noms commençant pas Z et continue vers le bas
	 <BR> &nbsp; - DOWN LAST NAME: commence avec les noms commençant par A et continue vers le haut
	 <BR> &nbsp; - UP COUNT: commence avec les fiches les plus appelées et continue en descendant
	 <BR> &nbsp; - DOWN COUNT: commence avec les fiches les moins appelées et continue en augmentant
	 <BR> &nbsp; - DOWN COUNT 2nd NEW: commence avec les fiches les moins appelées et continue en montant en insérant une fiche NEW à chaque autre fiche - Ne doit PAS avoir NEW selectionné dans les statuts d'appel
	 <BR> &nbsp; - DOWN COUNT 3nd NEW: commence avec les fiches les moins appelées et continue en augmentant en insérant une fiche NEW toutes les 3 fiches - Ne doit PAS avoir NEW selectionné dans les statuts d'appel
	 <BR> &nbsp; - DOWN COUNT 4th NEW: commence avec les fiches les moins appelées et continue en augmentant en insérant une fiche NEW toutes les 4 fiches - Ne doit PAS avoir NEW selectionné dans les statuts d'appel
	 <BR> &nbsp; - RANDOM: Hasard s-empare de plomb dans les statuts et listes définies
	 <BR> &nbsp; - UP LAST CALL TIME: Trie par la nouvelle durée d-un appel local pour le conduit
	 <BR> &nbsp; - DOWN LAST CALL TIME: Trie par le doyen des temps d-appel local pour le conduit
	 <BR> &nbsp; - UP RANK: Commence par le plus haut rang et fait son chemin vers le bas
	 <BR> &nbsp; - DOWN RANK: Commence avec le plus bas grade et fait son chemin jusqu-à
	 <BR> &nbsp; - UP OWNER: Commence avec les propriétaires commençant par Z et fait son chemin vers le bas
	 <BR> &nbsp; - DOWN OWNER: Commence avec les propriétaires commençant par A et fait son chemin jusqu-à
	 <BR> &nbsp; - UP TIMEZONE: Commence avec les fuseaux horaires de l-Est et l-Ouest de travaux
	 <BR> &nbsp; - DOWN TIMEZONE: Commence avec les fuseaux horaires de l-Ouest et travaille Est

	<BR>
	<A NAME="campaigns-lead_order_randomize">
	<BR>
	<B>Commandez Liste Randomize -</B> Cette option vous permet de randomiser l'ordre des pistes dans la charge trémie dans les résultats définis par les critères énoncés ci-dessus. Par exemple, la liste de commande seront préservés, mais les résultats seront randomisés dans ce tri. Mettre cette option à Y activer cette fonctionnalité. Par défaut est N pour les handicapés. REMARQUE, si vous avez un grand nombre de conducteurs de cette option peut ralentir la vitesse du script trémie de chargement.

	<BR>
	<A NAME="campaigns-lead_order_secondary">
	<BR>
	<B>Commandez Liste secondaire -</B> Cette option vous permet de sélectionner l'ordre de tri second des fils de la charge trémie après l'Ordre et au sein de la liste des résultats définis par les critères énoncés ci-dessus. Par exemple, la liste de commande sera utilisé pour la première sorte de les fils, mais les résultats seront triés une seconde fois au sein de ce tri dans le même ensemble de l'Ordre de la première liste. Par exemple, si vous avez la liste de commandes mis à décomptage et vous avez seulement des pistes qui ont été tentées 1 et 2 fois, puis si vous avez l'ensemble de commande Liste secondaire à LEAD_ASCEND tous les fils 1 tentative sera triée par les plus anciens conducteurs premier et sera placée dans la trémie de cette façon. Par défaut est LEAD_ASCEND. REMARQUE, si vous avez un grand nombre de prospects en utilisant l'une des options CALLTIME peut ralentir la vitesse du script trémie de chargement. Si la liste de commandes Aléatoire est activée, cette option sera ignorée.

	<BR>
	<A NAME="campaigns-hopper_level">
	<BR>
	<B>Niveau minimum Hopper -</B> C est le nombre minimum de fils le script trémie de chargement essaie de garder dans le tableau de la trémie pour cette campagne. Si vous exécutez le script VDhopper chaque minute, faire ce légèrement supérieur au nombre de pistes que vous passez par en une minute.

	<BR>
	<A NAME="campaigns-use_auto_hopper">
	<BR>
	<B>Niveau Hopper automatique -</B> Mettre ce paramètre à Y permet au système d ajuster automatiquement la trémie en fonction de paramètres que vous avez dans votre campagne. La formule qu il utilise pour ce faire est:<BR>Number of ActifAgents * Niveau de l'appel automatique * ( 60 seconds / Délai de tentative d'appel ) * Auto Hopper Multiplier<BR>Default is Y.

	<BR>
	<A NAME="campaigns-auto_hopper_multi">
	<BR>
	<B>Multiplicateur Hopper automatique -</B> C'est multiplicateur pour l'Hopper Auto. La définition de cette moins de 1 fera l'algorithme Hopper Auto à charger moins que fils il le ferait normalement. La définition de cette supérieur à 1 fera l'algorithme Hopper Auto pour charger plus de prospects que normalement. Par défaut est 1.

	<BR>
	<A NAME="campaigns-auto_trim_hopper">
	<BR>
	<B>Auto Trim Hopper -</B> Mettre ce paramètre à Y permettra au système de supprimer automatiquement le surplus de câble de la trémie. Par défaut est Y.

	<BR>
	<A NAME="campaigns-hopper_vlc_dup_check">
	<BR>
	<B>Hopper VLC Dup Vérifiez -</B> Un réglage à Y entraîne chaque piste étant insérée dans la trémie étant vérifiée par vendor_lead_code pour s'assurer qu'il n'y sont pas dupliquer conducteurs insérés avec le même vendor_lead_code. Cela est particulièrement utile lorsque la fonction Auto-Alt-numérotation avec MULTI_LEAD. Par défaut est N.

	<BR>
	<A NAME="campaigns-lead_filter_id">
	<BR>
	<B>Filtre de fiches -</B> C'est une méthode de filtrage de vos fiches utilisant un fragment de requête SQL. Utilisez cette fonction avec précaution, il est facile de stoper une comunication accidentellement avec le plus petit changement du statement SQL. La valeur par défaut est NONE.

	<BR>
	<A NAME="campaigns-call_count_limit">
	<BR>
	<B>Appelez le comte Limite -</B> Ceci impose une limite sur le nombre de tentatives d'appel pour les conducteurs composés dans cette campagne. Un plomb peut dépasser cette limite légèrement si le recyclage de plomb ou Auto-Alt-numérotation est activée. Par défaut est 0 pour aucune limite.

	<BR>
	<A NAME="campaigns-call_count_target">
	<BR>
	<B>Appelez le comte cible -</B> Cette option est uniquement utilisée pour des fins de rapport et n'a aucun effet sur les pistes composés. Par défaut est 3.

	<BR>
	<A NAME="campaigns-drop_lockout_time">
	<BR>
	<B>Drop Lockout Time -</B> Il s-agit d-un nombre d-heures que DROP abandonner les appels seront empêchés d-être composés, pour définir désactiver à 0. Ce paramètre est très utile dans des pays comme le Royaume-Uni où il existe une réglementation empêchant l-appelant a tenté de clients dans les 72 heures d-un abandon, ou DROP. Par défaut est 0.

	<BR>
	<A NAME="campaigns-force_reset_hopper">
	<BR>
	<B>Forcer la réinitialisation du distributeur -</B> Cela vous autorise à éliminer le contenu du distributeur depuis un formulaire de validation. Il devrait être rempli une nouvelle fois quand le quand VDhopper se lance.

	<BR>
	<A NAME="campaigns-dial_method">
	<BR>
	<B>Méthode d'appel -</B> Ce champ permet de définir la façon dont sont passés les appels. Si c'est MANUAL alors le auto_dial_level sera bloqué à 0 à moins que le méthode d'appel ne change. If RATIO then the normal dialing a number of lines for Actifagents. ADAPT_HARD_LIMIT will dial predictively up to the dropped percentage and then not allow aggressive dialing once the drop limit is reached until the percentage goes down again. ADAPT_TAPERED allows for running over the dropped percentage in the first half of the shift -as defined by call_time selected for campaign- and gets more strict as the shift goes on. ADAPT_AVERAGE tries to maintain an average or the dropped percentage not imposing hard limits as aggressively as the other two methods. You cannot change the Niveau de l'appel automatique if you are in any of the ADAPT dial methods. Only the Dialer can change the dial level when in predictive dialing mode. INBOUND_MAN permet à l'agent de mise manuel composer des appels de la liste d'une campagne tout en étant en mesure de prendre des appels entrants entre le manuel de composer des appels.

	<BR>
	<A NAME="campaigns-auto_dial_level">
	<BR>
	<B>Niveau de l'appel automatique -</B> C est là que vous définissez le nombre de lignes le système doit utiliser par agent actif. 0 signifie zéro la numérotation automatique est désactivé et les agents va cliquer pour composer chaque numéro. Sinon, le système permet de garder la numérotation des lignes correspondant à des actifs multiplié par le niveau de numérotation pour arriver à combien de lignes de cette campagne sur chaque serveur devrait permettre. La case à cocher ADAPT PRIORITAIRE vous permet de forcer un niveau de ligne, même si la méthode de numérotation est dans un mode ADAPT. Ceci est utile si il ya un changement radical dans la qualité des pistes et que vous voulez changer radicalement la dial_level manuellement.

	<BR>
	<A NAME="campaigns-dial_level_threshold">
	<BR>
	<B>Seuil Auto Level Dial -</B> Ce paramètre ne fonctionne qu'avec un ADAPT ou la méthode de rapport Dial, et il doit être réglé sur autre chose que HANDICAPÉS et le nombre de paramètre agents doit être supérieure à 0. Cette fonctionnalité vous permet de définir un nombre minimum d'agents que l'algorithme prédictif travailler. Si le nombre d'agents en deçà du nombre que vous avez défini, alors le niveau de numérotation ira à 1,0 jusqu'à plus d'agents connecter ou entrer dans l'état sélectionné. Logged-IN_AGENTS comptera tous les agents connectés à la campagne, NON-PAUSED_AGENTS comptera seulement les agents qui sont en attente ou en parlant, et WAITING_AGENTS comptera seulement des agents qui sont en attente d'un appel. Par défaut est DISABLED.

	<BR>
	<A NAME="campaigns-available_only_ratio_tally">
	<BR>
	<B>Valable seulement pour les contrôles -</B> Si ce champ est à Y les agents ayant pour statut INCALL ou QUEUE seront omis pour le calcul des appels à passer quand le mode d'appel n'est pas MANUAL. La valeur par défaut est N.

	<BR>
	<A NAME="campaigns-available_only_tally_threshold">
	<BR>
	<B>Disponible Seuil Tally seulement -</B> Ce paramètre ne fonctionne qu'avec un ADAPT ou la méthode de rapport Dial, disponible seulement Tally doit être réglé sur N, ce paramètre doit être réglé sur autre chose que HANDICAPÉS et le nombre de paramètre agents doit être supérieure à 0. Cette fonctionnalité vous permet de définir le nombre d'agents en dessous duquel Disponible Tally Seuls seront permis. Si le nombre d'agents en deçà du nombre que vous avez défini, puis la mise en Tally Disponible seulement au rendez-vous à Y temporairement jusqu'à ce que plus d'agents connecter ou entrer dans l'état sélectionné. Logged-IN_AGENTS comptera tous les agents connectés à la campagne, NON-PAUSED_AGENTS comptera seulement les agents qui sont en attente ou en parlant, et WAITING_AGENTS comptera seulement des agents qui sont en attente d'un appel. Par défaut est DISABLED.

	<BR>
	<A NAME="campaigns-adaptive_dropped_percentage">
	<BR>
	<B>Pourcentage limite d'abandon -</B> Ce champ permet de définir le pourcentage limite d'appels abandonnés voulu lors de l'utilisation de méthode d'appel adaptive-predictive, pas MANUAL ni RATIO.

	<BR>
	<A NAME="campaigns-adaptive_maximum_level">
	<BR>
	<B>Niveau maximum d'adaptation d'appel -</B> Ce champ permet de définir la limite du nombre de lignes que vous voulez composer par agent lors de l'utilisation d'une méthode d'appel adaptive-predictive, pas MANUAL ni RATIO. Ce nombre peut être plus grand que le Niveau de l'appel automatique de votre matériel ci celui-ci le supporte. La valeur doit être un nombre positif plus grand que 1 qui peut être décimal. La valeur par défaut est 3.0.

	<BR>
	<A NAME="campaigns-adaptive_latest_server_time">
	<BR>
	<B>Heure maximum du serveur -</B> Ce champ est seulement utilisé par la méthode d'appel ADAPT_TAPERED. Vous devez y saisir l'heure à laquelle vous allez arrêter d'appeler pour cette campagne, 2100 veut dire que vous arrêterez d'appeler pour cette campagne à 21h00. Cela autorise l'algorithme Tapered de décider comment appeler agressivement en fonction du temps restant avant d'arrêter d'appeler.

	<BR>
	<A NAME="campaigns-adaptive_intensity">
	<BR>
	<B>Adapter le modifieur d'intensité -<\/B> Ce champ est utilisé pour ajuster l'intensité prédictive à la fois plus haut ou plus bas. Plus vous selectionnez un nombre positif grand, plus l'appeleur augmentera considérablement l'appel quand il monte et plus l'appeleur dimunera lentement l'appel quand il descend. Plus le chiffre négatif que vous selectionnez est petit, plus l'appeleur augmentera lentement l'appel et plus l'appeleur diminuera rapidement l'appel quand il descend. La valeur par défaut est 0. Ce champ n'est pas utilisé par les méthodes d'appels MANUAL et RATIO.

	<BR>
	<A NAME="campaigns-adaptive_dl_diff_target">
	<BR>
	<B>Différence cible du niveau d'appel -</B> Ce champ est utilisé pour définir si vous souhaitez cibler ayant un nombre précis d'agents d'attente pour les appels ou les appels en attente pour les agents. Par exemple, si vous voulez toujours avoir en moyenne un agent libre de prendre les appels immédiatement, vous devez définir cette valeur à -1, si vous souhaitez cibler avoir toujours un appel en attente en attente d'un agent, vous devez définir cette valeur à 1. Par défaut est 0. Ce champ n'est pas utilisé dans le manuel ou les méthodes de numérotation INBOUND_MAN.

	<BR>
	<A NAME="campaigns-dl_diff_target_method">
	<BR>
	<B>Composez méthode cible de différence de niveau -</B> Cette option vous permet de définir si le niveau cible cadran différence paramètre est appliqué uniquement sur le calcul du niveau de cadran ou également à la composition réelle sur chaque serveur de numérotation. Si vous exécutez une petite campagne avec des agents connectés sur de nombreux serveurs, vous voudrez peut-être utiliser l'option ADAPT_CALC_ONLY, parce que l'option CALLS_PLACED peut entraîner une diminution des appels en cours placés que deisred. Cette option n'est active que si la cible Différence Dial Level est réglé sur autre chose que 0. Par défaut est ADAPT_CALC_ONLY.

	<BR>
	<A NAME="campaigns-concurrent_transfers">
	<BR>
	<B>Transferts concurents -</B> Ce champ permet de définir le nombre d'appels qui peuvent être envoyés à un agent simultanément. Il est recommandé de laisser AUTO. Le champ n'est pas utilisé si la méthode est MANUAL.

	<BR>
	<A NAME="campaigns-queue_priority">
	<BR>
	<B>File d'attente de priorité - </B> Ce paramètre est utilisé pour définir l'ordre dans lequel les appels sortants à partir de cette campagne devrait être répondu en ce qui concerne les appels entrants si cette campagne est en mode mixte. Nous ne recommandons pas la mise en appels entrants vers une plus grande priorité que les appels sortants de la campagne parce que les gens font escale dans s'attendre à attendre et les gens sont appelés attendre pour que quelqu'un soit là sur le téléphone.

	<BR>
	<A NAME="campaigns-drop_rate_group">
	<BR>
	<B>Multiples campagne Drop Rate Group -</B> Cette fonctionnalité vous permet de définir une campagne comme un membre d-un groupe de la Campagne taux d-abandon, ou un groupe de campagnes dont l-homme appels répondus et les appels de chute pour toutes les campagnes dans le groupe seront combinés en un pourcentage partagée déposer, ou les taux d-abandon. Ceci vous permet d-exécuter plusieurs campagnes à la fois et plus facile à contrôler votre taux de chute. Ceci est particulièrement utile dans le Royaume-Uni où la réglementation permet cette méthode de calcul des taux d-abandon avec une campagne de groupement pour la même compagnie, même s-il ya plusieurs campagnes cette société est en marche pendant le jour même. Pour activer ce pour une campagne, il suffit de sélectionner un groupe de la liste. Il existe 10 groupes définis dans le système par défaut, vous pouvez contacter votre administrateur système d-ajouter d-autres. Par défaut est Disabled.

	<BR>
	<A NAME="campaigns-inbound_queue_no_dial">
	<BR>
	<B>Pas de file d'attente entrante Composez -</B> Cette fonctionnalité, si la valeur ENABLED vous permet d'empêcher sortant numérotation automatique de cette campagne s'il ya des appels entrants en attente dans la file d'attente qui font partie des groupes entrantes autorisées fixés dans cette campagne. Mettre cette option à ALL_SERVERS va changer l'algorithme pour calculer tous les appels entrants que les appels actifs sur ce serveur, même si elles sont sur un autre serveur qui permettra de réduire le risque de placer des appels sortants inutiles si vous avez des appels arrivant sur un autre serveur. Par défaut est DISABLED.

	<BR>
	<A NAME="campaigns-auto_alt_dial">
	<BR>
	<B>Appeler automatiquement le numéro alternatif -</B> Ce champ est utilisé pour appeler automatiquement le numéro alternatif si les méthodes d'appels RATIO ou ADAPT sont utilisées quand il n'y pas de réponse au numéro de téléphone principale d'une fiche, les statuts NA, B, DC et N. Cette option n'est pas utilisée pour la méthode d'appel MANUAL. EXTENDED suppléant nombres sont des nombres dans le système en dehors de la norme de l'écran principal de l'information. L'aide de vous pouvez avoir des centaines de numéros de téléphone pour un seul client record. Utilisation MULTI_LEAD vous permet d'utiliser un câble séparé pour chaque numéro sur un compte client qui partagent tous une vendor_lead_code commune, et le type de numéro de téléphone pour chacun d'eux est défini avec une étiquette dans le domaine du propriétaire. Cette option permet de désactiver certaines options sur l'écran de la campagne Modifier et afficher un lien vers la page Multi-Alt Paramètres qui vous permet de définir quels types de numéros de téléphone, définis par l'étiquette de chaque plomb, sera composé et dans quel ordre. Cela va créer un filtre spécial de plomb et de modifier le champ Rang de tous les fils à l'intérieur des listes établies à cette campagne afin de les appeler dans l'ordre que vous avez spécifié.

	<BR>
	<A NAME="campaigns-dial_timeout">
	<BR>
	<B>Délai d'appel -<\/B> Si défini, les appels qui seraient normalement raccrochés après le délai défini dans extensions.conf le seront après ce nombre de secondes si c'est moins que le délai dans extensions.conf. Cela autorise des délais de changement d'appel rapide de serveur à serveur et limite les effets à une simple campagne. Si vous avez beaucoup de répondeur et de boîte vocales, vous devriez essayer de mettre une valeur entre 21 et 26 et voir si les résultats s'améliorent.

	<BR>
	<A NAME="campaigns-campaign_vdad_exten">
	<BR>
	<B>Routage Extension -</B> Ce champ permet à une extension de routage sortant personnalisé. Cela vous permet d'utiliser différentes méthodes de traitement des appels en fonction de la façon dont vous voulez acheminer les appels sortants grâce à votre campagne. Anciennement appelée l'extension VDAD Campagne. 
	<BR>- 8364 - même 8368
	<BR>- 8365 - Enverra l'appel uniquement à un agent sur le même serveur que l'appel est placé sur
	<BR>- 8366 - Utilisé pour des campagnes de presse-1, de diffusion et d'enquête
	<BR>- 8367 - Je vais essayer d'envoyer d'abord l'appel à un agent sur le serveur local, il se penchera sur d'autres serveurs
	<BR>- 8368 - DEFAULT - Permet d'envoyer l'appel vers le prochain agent disponible, peu importe quel serveur ils sont sur
	<BR>- 8369 - Utilisé pour Détection des répondeurs après que, même comportement que 8368
	<BR>- 8373 - Utilisé pour Détection des répondeurs après cette même comportement que 8366
	<BR>- 8374 - Utilisé pour des campagnes de presse-1, de diffusion et d'enquête avec Cepstral Text-to-speech
	<BR>- 8375 - Utilisé pour Détection des répondeurs puis appuyez-1, de diffusion et d'enquête des campagnes avec Cepstral Text-to-speech

	<BR>
	<A NAME="campaigns-am_message_exten">
	<BR>
	<B>Message de répondeur -</B> Ce champ sert à saisir l-invite à jouer lorsque l-agent est un répondeur et clique sur le bouton Répondre Message Machine dans le cadre de conférences de transfert. Vous devez définir ce soit à un fichier audio dans le magasin audio ou une invite si TTS TTS est activé sur votre système.

	<BR>
	<A NAME="campaigns-waitforsilence_options">
	<BR>
	<B>WaitForSilence Options -</B> Si Wait For Silence est demandée sur les appels qui sont détectés comme répondeur alors ce champ possède ces options. Il ya deux paramètres séparés par une virgule, la première option est combien de temps pour détecter le silence en millisecondes et la seconde option est pour combien de fois de détecter que avant la lecture du message. Par défaut est vide pour les handicapés. Une valeur standard pour ce serait attendre 2 secondes de silence à deux reprises: 2000,2

	<BR>
	<A NAME="campaigns-amd_send_to_vmx">
	<BR>
	<B>AMD envoyer à l'action -</B> Cette option vous permet de définir si un appel est envoyé à l'action AMD lorsqu'un répondeur est détecté. Si ce paramètre est réglé à N, l'appel sera raccroché dès qu'il est déterminé à être un répondeur. Défaut est N.

	<BR>
	<A NAME="campaigns-cpd_amd_action">
	<BR>
	<B>CPD AMD Action - </B> Si vous utilisez le Sangoma ParaXip Call Progress logiciel de détection, vous souhaitez activer ce paramètre, soit la mise à disposition DISPO qui l'appel comme AA et l'accrocher si l'appel est en cours de traitement et n'a pas été envoyée à un agent ou encore MESSAGE qui enverra l'appel à la définition Message de répondeur pour cette campagne. La valeur par défaut est DISABLED. Un réglage à ingroup enverra un répondeur à un groupe entrant. Un réglage à CALLMENU enverra un répondeur à un menu d'appel dans le système.

	<BR>
	<A NAME="campaigns-amd_inbound_group">
	<BR>
	<B>AMD Entrant Groupe -</B> Si CPD AMD Action est réglé sur ingroup, alors c'est le groupe entrant que l'appel sera envoyé si un répondeur est détecté.

	<BR>
	<A NAME="campaigns-amd_callmenu">
	<BR>
	<B>AMD Appel Menu -</B> Si CPD AMD Action est réglé sur CALLMENU, alors c'est le menu d'appel que l'appel sera envoyé si un répondeur est détecté.

	<BR>
	<A NAME="campaigns-alt_number_dialing">
	<BR>
	<B>Manuel Alt Num numérotation -</B> Cette option permet à un agent de composer manuellement le numéro de téléphone ou autre address3 terrain après le numéro principal a été appelé. Si l option a choisi en elle alors la case Alt Dial sera automatiquement vérifié pour chaque appel. Si l option a TIMER en elle alors le téléphone ou Alt champ Address3 seront automatiquement composé après secondes minuterie Alt. Défaut est N pour les handicapés.

	<BR>
	<A NAME="campaigns-timer_alt_seconds">
	<BR>
	<B>secondes Alt Timer -</B> Si le réglage Alt Num composition manuelle a TIMER en elle alors le téléphone ou Alt champ Address3 seront automatiquement composé après le nombre de secondes. Défaut est 0 pour les handicapés .

	<BR>
	<A NAME="campaigns-drop_call_seconds">
	<BR>
	<B>Secondes avant abandon de l'appel -<\/B> C'est le nombre de secondes depuis le moment où la ligne du client est prise jusqu'à ce que l'appel soit considéré comme abandonné, s'applique seulement aux appels sortants.

	<BR>
	<A NAME="campaigns-drop_action">
	<BR>
	<B>Drop Action - </B> Ce menu vous permet de choisir ce qui se passe à un appel quand il a attendu plus longtemps que ce qui est prévu dans le Temps avant abandon de l'appel domaine. Hangup simplement raccrocher l'appel, envoyer le message de l'appel Drop EXTEN que vous avez définies ci-après, à la messagerie vocale vous envoyer l'appel à la boîte vocale que vous avez définis ci-dessous et IN_GROUP envoie l'appel à la Entrant groupe qui est défini ci-dessous. VMAIL_NO_INST enverra l appel à la boîte vocale que vous avez défini ci-dessous et ne jouera pas les instructions après le message vocal.

	<BR>
	<A NAME="campaigns-safe_harbor_exten">
	<BR>
	<B>Extension du port sûr -<\/B> C'est l'extension du dial plan où le fichier audio désiré est localisé sur le serveur.

	<BR>
	<A NAME="campaigns-safe_harbor_audio">
	<BR>
	<B>Safe Harbour Audio -</B> Il s'agit du fichier audio rapide qui se joue, si l'action Drop est réglé sur AUDIO. Par défaut est buzz.

	<BR>
	<A NAME="campaigns-safe_harbor_audio_field">
	<BR>
	<B>Safe Harbour Audio terrain -</B> Ce paramètre optionnel vous permet de définir un champ dans la liste que le système va utiliser comme nom de fichier audio pour chaque conducteur à la place du fichier audio Safe Harbor. Si cela est défini sur Désactivé Le fichier audio Safe Harbor sera toujours utilisé. Le système ne fera pas de validation pour s'assurer que le fichier audio existant autre que de s'assurer que la valeur du champ est au moins un caractère, donc si vous voulez une avance d'utiliser le Safe Audio de port par défaut alors que vous venez de définir la valeur du champ en tête se vider. Vous pouvez utiliser le caractère pipe de relier plusieurs fichiers audio en même temps à la valeur du champ pour chaque fil. Est désactivée par défaut. Voici la liste des champs qui peuvent être utilisés pour ce paramètre: vendor_lead_code, source_id, list_id, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, alt_phone, email, security_phrase, comments, rank, owner, entry_list_id

	<BR>
	<A NAME="campaigns-safe_harbor_menu_id">
	<BR>
	<B>Coffre-fort Menu Appel port -</B> C'est le menu d'appel que l'appel est envoyé à si l'action Drop est réglé sur CALLMENU.

	<BR>
	<A NAME="campaigns-voicemail_ext">
	<BR>
	<B>Boîte vocale -</B> Si elle est définie, les appels qui sont normalement abandonné seront redirigés vers une boîte vocale pour écouter et laisser un message.

	<BR>
	<A NAME="campaigns-drop_inbound_group">
	<BR>
	<B>Drop de transfert Group - </B> Si Drop action est fixée à IN_GROUP, l'appel sera envoyé à ce groupe d'arrivée si elle parvient Temps avant abandon de l'appel.

	<BR>
	<A NAME="campaigns-no_hopper_leads_logins">
	<BR>
	<B>Autoriser la connection aux distributeurs de fiches vides -</B> Si défini à Y, autorise les agents à se connecter à la campagne même si il n'y a pas de fiches chargées dans le distributeur pour cette campagne. Cette fonction est nécessaire pour les campagne de type CLOSER. La valeur par défaut est N.

	<BR>
	<A NAME="campaigns-no_hopper_dialing">
	<BR>
	<B>N Hopper Dialing -</B> Si elle est activée, la trémie ne fonctionnera pas pour cette campagne. Cette option n-est disponible que lorsque la méthode du cadran est mis en mode manuel ou INBOUND_MAN. Il est recommandé de ne pas activer cette option si vous avez une base très large de plomb, plus de 100.000 prospects. Avec pas d-appel Hopper, les caractéristiques suivantes ne fonctionnent pas: recyclage du plomb, auto-alt-numérotation, mélanger la liste, mise en ordre avec Xème NEW. Si vous souhaitez utiliser Propriétaire composer seulement vous devez avoir pas d-appel Hopper activé. Par défaut est N pour les handicapés.

	<BR>
	<A NAME="campaigns-agent_dial_owner_only">
	<BR>
	<B>Propriétaire composer seulement -</B> Si elle est activée, l-agent ne recevra pistes qu-ils sont dans les paramètres de propriété pour. Si cela est réglé sur Utilisateur, puis l-agent doit être définie par l-utilisateur dans la base de données en tant que propriétaire de ce plomb. Si cela est défini au territoire, le propriétaire de la direction doit correspondre au territoire énumérées dans l-écran de Modification de l-utilisateur pour cet agent. Si ce réglage est à GROUPE_UTILISATEUR le propriétaire du nom de la sonde doit correspondre au groupe d-utilisateurs que l-agent est un membre de. Pour que cette fonction soit la méthode cadran doit être réglé en mode manuel ou INBOUND_MAN Hopper et pas d-appel doit être activé. Par défaut est AUCUN pour les handicapés. Si l'option est vierge à la fin, les utilisateurs sont autorisés à composer pistes sans propriétaire défini en plus de propriétaire défini conduit.

	<BR>
	<A NAME="campaigns-owner_populate">
	<BR>
	<B>Propriétaire Remplir -</B> Si cette option est activée et le champ propriétaire de la mine est vide, le champ de propriétaire pour le plomb va peupler avec l'ID utilisateur de l'agent qui traite l'appel en premier. Est désactivée par défaut.

	<?php
	if ($SSuser_territories_active > 0)
		{
		?>
		<BR>
		<A NAME="campaigns-agent_select_territories">
		<BR>
		<B>Agent de sélectionner des territoires -</B> Si cette option est activée et que l-agent appartient à au moins un territoire, l-agent aura la possibilité de choisir les territoires à composer mène de. L-agent pourrez voir une liste des territoires disponibles sur login et ils auront la possibilité de retourner à cette liste le territoire pendant la pause pour changer leurs territoires. Pour que cette fonction de travail de la propriétaire composer seulement option doit être définie sur le territoire et les territoires d-utilisateur doit être activé dans les paramètres système.
		<?php
		}
	?>

	<BR>
	<A NAME="campaigns-list_order_mix">
	<BR>
	<B>Mélange d'ordre de liste - dépasse les champs d'ordre de fil et destatut de cadran. Emploiera les paramètres de liste et de statut pourl'entrée choisie de mélange de liste dans la section de sous-marinde mélange de liste à la place. Le défaut est HANDICAPÉ.

	<BR>
	<A NAME="campaigns-vcl_id">
	<BR>
	<B>Identification de mélange de liste - identification du mélange deliste. Doit être de 2-20 caractères de longueur sans les espaces outoute autre ponctuation spéciale.

	<BR>
	<A NAME="campaigns-vcl_name">
	<BR>
	<B>Nom de mélange de liste - nom descriptif du mélange de liste. Doitêtre de 2-50 caractères de longueur.

	<BR>
	<A NAME="campaigns-list_mix_container">
	<BR>
	<B>Détail de mélange de liste - la composition de l'entrée de mélangede liste. Contient l'identification de liste, l'ordre de mélange, lespourcentages et les statuts qui composent ce mélange de liste. Lespourcentages toujours doivent additionner jusqu'à 100, et toutes leslistes doivent être en activité et réglées à la campagne pour quel'entrée de mélange d'ordre soit activée.

	<BR>
	<A NAME="campaigns-mix_method">
	<BR>
	<B>Méthode de mélange de liste - la méthode de mélanger toutes lesparties du détail de mélange de liste ensemble. EVEN_MIX mélangerades fils de chaque partie intercalée aux autres parties, comme ces1.2.3.1.2.3.1.2.3. IN_ORDER mettra les fils dans l'ordre dans lequelils sont énumérés dans l'écran 1.1.1.2.2.2.3.3.3 de détail demélange de liste. La volonté ALÉATOIRE les a mis dans l'ordreALÉATOIRE 1.3.2.1.1.3.2.1.3. Le défaut est IN_ORDER.

	<BR>
	<A NAME="campaigns-agent_extended_alt_dial">
	<BR>
	<B>Agent Screen Extended Alt Dial -</B> Cette fonction permet aux agents d accéder à d autres numéros de téléphone prolongées pour les pistes au-delà du téléphone Alt standard et champs Address3 qui peut être utilisé dans l écran de l agent des numéros de téléphone au-delà du numéro de téléphone principal. Les numéros de téléphone étendues peuvent être composés automatiquement en utilisant la fonction Auto-Alt-Dial dans les paramètres de la campagne, mais l activation de cette fonction de l écran de l agent permettront également l agent d appeler ces numéros depuis leur écran de l agent ainsi que modifier leurs informations. Cette catégorie est en développement et n est pas disponible actuellement.

	<BR>
	<A NAME="campaigns-survey_first_audio_file">
	<BR>
	<B>Première Enquête fichier audio - </B> Il s'agit du nom de fichier audio qui se joue dès que le client choisit le téléphone lors de l'exécution d'une étude campagne.

	<BR>
	<A NAME="campaigns-survey_dtmf_digits">
	<BR>
	<B>Enquête DTMF Digits - </B> Ce domaine est l'endroit où vous définissez les chiffres que le client peut presse comme une option sur une campagne de sondage. valable chiffres DTMF sont0123456789*#. Toutes les options à l-exception des pas intéressé, troisième et quatrième options chiffre passera à la voie des appels de méthode Enquête.

	<BR>
	<A NAME="campaigns-survey_wait_sec">
	<BR>
	<B>Enquête Attendez secondes -</B> C'est le nombre de secondes en mode d'enquête le système attend l'entrée de la personne appelée jusqu'à ce que l'enquête ou de l'action de chute est déclenchée. Ne s'applique pas si la méthode d'enquête est HANGUP. Défaut est de 10 secondes.

	<BR>
	<A NAME="campaigns-survey_ni_digit">
	<BR>
	<B>Enquête Non Intéressé Digit - </B> Ce domaine est l'endroit où vous définissez le client pressé chiffres qui montrent qu'ils ne sont pas intéressés.

	<BR>
	<A NAME="campaigns-survey_ni_status">
	<BR>
	<B>Enquête Non Intéressé Etat -</B> Ce champ vous permet de sélectionner l état à utiliser pour ne m intéresse pas. Si DNC est utilisé et la campagne est configuré pour utiliser DNC puis le numéro de téléphone sera automatiquement ajouté à la liste DNC interne et, éventuellement, la liste DNC spécifiques campagne si cela est permis dans la campagne.

	<BR>
	<A NAME="campaigns-survey_opt_in_audio_file">
	<BR>
	<B>Enquête sur Opt-in fichier audio - </B> Il s'agit du nom de fichier audio qui se joue lorsque le client a opté dans l'enquête, a choisi de ne pas-ou pas répondu si la non-réponse-action est fixé à optout. Après ce fichier audio est joué, le sondage de l'action ne soit prise.

	<BR>
	<A NAME="campaigns-survey_ni_audio_file">
	<BR>
	<B>Enquête Non Intéressé fichier audio - </B> Il s'agit du nom de fichier audio qui se joue lorsque le client a choisi de l'enquête, a choisi de ne pas-ou pas répondu si la non-réponse-action est fixé à OptIn. Après ce fichier audio est joué, l'appel sera suspendu jusqu'à.

	<BR>
	<A NAME="campaigns-survey_method">
	<BR>
	<B>Enquête sur la méthode - </B> Cette option permet de définir ce qui se passe à un appel après que le client a opté en. AGENT_XFER enverra l'appel à la prochaine agent. MESSAGERIE VOCALE envoie l'appel à la boîte vocale qui est spécifié dans la boîte vocale domaine. EXTENSION envoie le client à l'extension définie dans le Enquête Xfer Extension domaine. Hangup se raccrocher le client. CAMPREC_60_WAV enverra au client d'avoir un enregistrement réalisé avec leur réponse, cet enregistrement sera placé dans un dossier nommé comme la campagne de l'intérieur de l'Enquête sur la campagne d'enregistrement Directory. CALLMENU enverra au client dans le menu Appel défini dans la liste de sélection ci-dessous. VMAIL_NO_INST enverra l appel à la boîte vocale que vous avez défini ci-dessous et ne jouera pas les instructions après le message vocal.

	<BR>
	<A NAME="campaigns-survey_no_response_action">
	<BR>
	<B>Etude de non-réponse de l'action - </B> C'est là que vous définissez ce qui se passera s'il n'y a pas de réponse à la question de sondage. OptIn ne envoyer l'appel à la méthode d'enquête, si le client en pressant une chiffres DTMF. OptOut envoie le client sur la méthode d'enquête, même si elles ne sont pas sur un chiffre DTMF. DROP abandonner l'appel en utilisant la méthode de la goutte de campagne, mais toujours se connecter à l'appel en tant que PM a joué état du message.

	<BR>
	<A NAME="campaigns-survey_response_digit_map">
	<BR>
	<B>Enquête Réponse Digit Map - </B> Il s'agit de la section où vous pouvez définir une description pour aller avec chaque option DTMF chiffres que le client mai sélectionner.

	<BR>
	<A NAME="campaigns-survey_xfer_exten">
	<BR>
	<B>Enquête Xfer Extension - </B> Si la méthode d'enquête de l'extension est sélectionné, le client appel sera dirigé vers cette extension dialplan.

	<BR>
	<A NAME="campaigns-survey_camp_record_dir">
	<BR>
	<B>Enquête sur la campagne d'enregistrement - Annuaire </B> Si la méthode d'enquête de CAMPREC_60_WAV est sélectionné, le service à la clientèle sera enregistré et placé dans un répertoire nommé d'après la campagne de l'intérieur de ce répertoire.

	<BR>
	<A NAME="campaigns-survey_third_digit">
	<BR>
	<B>Enquête sur le troisième chiffre -</B> Cela permet un chemin d-appel tiers si le troisième chiffre tel que défini dans ce domaine est pressé par le client.

	<BR>
	<A NAME="campaigns-survey_fourth_digit">
	<BR>
	<B>Enquête quatrième chiffre -</B> Cela permet pour un chemin quatrième appel si la quatrième chiffres comme définis dans ce domaine est pressé par le client.

	<BR>
	<A NAME="campaigns-survey_third_audio_file">
	<BR>
	<B>Troisième enquête sur Fichier Audio -</B> Ceci est le fichier audio troisième à être joué sur le choix par le client de l-option de troisième chiffre.

	<BR>
	<A NAME="campaigns-survey_third_status">
	<BR>
	<B>Troisième enquête Etat -</B> C-est le statut de troisième utilisés pour l-appel lancé à la sélection par le client de l-option de troisième chiffre.

	<BR>
	<A NAME="campaigns-survey_third_exten">
	<BR>
	<B>Enquête Troisième prorogation -</B> Il s-agit de la troisième extension utilisé pour l-appel sur le choix par le client de l-option de troisième chiffre. Par défaut est 8300, qui se bloque immédiatement à l-appel après que le message audio du fichier est joué.

	<BR>
	<A NAME="campaigns-agent_display_dialable_leads">
	<BR>
	<B>Agent d-affichage composable Leads -</B> Cette option si elle est activée affiche le nombre de pistes d-accès automatique disponibles dans la campagne dans l-écran de l-agent. Ce numéro est mis à jour dans le système une fois par minute et sera rafraîchie à l-écran tous les agents de quelques secondes.

	<BR>
	<A NAME="campaigns-survey_menu_id">
	<BR>
	<B>Menu Appel Enquête -</B> Si la méthode est réglé sur CALLMENU, c'est le menu Appel que le client est envoyé à si elles optent en.

	<BR>
	<A NAME="campaigns-survey_recording">
	<BR>
	<B>Enregistrement Enquête -</B> Si elle est activée, cela va démarrer l'enregistrement lorsque l'appel est répondu. Seulement recommandé si la méthode n'est pas réglé pour le transfert à un agent. Par défaut est N pour les handicapés. S'il est défini à Y_WITH_AMD même machine à répondre aux appels de messages détectés seront enregistrées.

	<?php
	}
?>

<BR>
<A NAME="campaigns-next_agent_call">
<BR>
<B>Appel de l'agent suivant -</B> Cela determine quel agent recevra l'appel suivant quand c'est possible:
 <BR> &nbsp; - random: commandes de la valeur de mise à jour aléatoire dans la table live_agents
 <BR> &nbsp; - oldest_call_start: ordonne par la dernière date à laquelle un agent a passé un appel. Résultats des agents recevant globalement un nombre à peu près identique d'appels .
 <BR> &nbsp; - oldest_call_finish: ordonne suivant la dernière date à laquelle un agent a terminé un appel. L'agent AKA attendant le plus longtemps reçoit le premier appel.
 <BR> &nbsp; - overall_user_level: commandes par le user_level de l agent tel que défini dans la table des utilisateurs un user_level supérieur recevront plus d appels.
 <BR> &nbsp; - campaign_rank : ordres par le rang donné à l'agent pour la campagne.Le plus haut à le plus bas.
 <BR> &nbsp; - campaign_grade_random: donne une plus grande probabilité de recevoir un appel aux agents de rang supérieur.
 <BR> &nbsp; - fewest_calls : ordres par le nombre d'appels reçus par un agent pource groupe d'arrivée spécifique. Moindres appels d'abord.
 <BR> &nbsp; - longest_wait_time: commandes par la quantité de temps d-attente agent a été activement pour un appel.

<BR>
<A NAME="campaigns-local_call_time">
<BR>
<B>Temps d'appel local -<\/B> C'est ici que vous indiquez pendant quelles heures vous voulez appeler, comme déterminé par l'heure locale dans la région dans laquelle vous voulez appeler. C'est controlé par le code de la région et est ajusté pendant le temps des Daylight Savings si c'est approprié. Les habitudes générales aux Etat-Unis, de Buisness à Buisness, sont de 9h00 à 17h00 et de Buisness à particulier de 9h00 à 21h00.

<BR>
<A NAME="campaigns-dial_prefix">
<BR>
<B>Préfixe d'appel -</B> Ce champ autorise plus facilement à changer un chemin d'appel vers l'exterieur à travers une méthode differente sans devoir recharger Asterisk. La valeur par défaut 9 est basée sur 91NXXNXXXXXX dans le dial plan du fichier extensions.conf .

<BR>
<A NAME="campaigns-manual_dial_prefix">
<BR>
<B>Préfixe Manuel -</B> Ce champ optionnel vous permet de définir le préfixe doit être utilisé que lors d'un appel numérotation manuelle à partir de l'interface agent, comme l'utilisation de la fonction numérotation manuelle, ou Composez le numéro suivant dans la méthode lorsque la numérotation manuelle, ou numérotation manuelle nombre alt, ou prévue par l'utilisateur seulement rappels. Par défaut est vide pour les handicapés, qui utilisent le préfixe défini dans le champ ci-dessus. Cette option ne pas interférer avec l'option Préfixe 3way.

<BR>
<A NAME="campaigns-omit_phone_code">
<BR>
<B>Omettre le phone code -</B> Ce champ vous permet de laisser le champ phone_code lors de la composition au sein du système. Par exemple, si vous appelez au Royaume-Uni de Grande-Bretagne, vous auriez 44 en tant que votre champ de phone_code pour toutes les pistes, mais vous voulez juste composer 10 chiffres dans votre plan de numérotation extensions.conf pour passer des appels au lieu de 44 puis 10 chiffres. Défaut est N.

<BR>
<A NAME="campaigns-campaign_cid">
<BR>
<B>CallerID de la campagne -</B> Ce champ permet l envoi d un certain nombre de callerid personnalisée sur les appels sortants. C est le nombre qui apparaîtra sur la callerid de la personne que vous appelez. La valeur par défaut est INCONNU. Si vous utilisez T1 ou E1 pour composer cette option n est disponible que si vous utilisez PRI - RNIS T1 ou E1 - qui ont la fonction de callerid personnalisé allumé, cela ne fonctionnera pas avec réassignation de bit services-RBS-circuits. Cela fonctionnera également dans la plupart VOIP SIP ou IAX-malles-fournisseurs qui permettent callerID sortant dynamique. La coutume callerID s applique uniquement aux appels placés pour la campagne directement, tous les appels 3ème partie ou transferts ne seront pas envoyer le callerID personnalisé. REMARQUE: Parfois, la mise inconnu ou privé dans le domaine donnera l envoi de votre numéro callerID défaut par votre support avec les appels. Vous pouvez tester cela et mettre 0000000000 dans le domaine de callerid place si vous ne voulez pas vous envoyer CallerID.

<BR>
<A NAME="campaigns-use_custom_cid">
<BR>
<B>Personnalisé CallerID -</B> Lorsqu il est réglé sur Y, cette option vous permet d utiliser le domaine security_phrase dans le tableau de la liste comme le CallerID à envoyer lors de la passation de chaque fil spécifique. Si ce champ n a aucun CID en elle alors la campagne CallerID défini ci-dessus sera utilisé à la place. Cette option désactive l option Remplacer la liste de CallerID si il ya un CID présente dans le domaine security_phrase. Par défaut est N. Lorsqu il est réglé sur Areacode vous avez la possibilité d aller dans le sous-menu AC-CID et définir plusieurs callerids à être utilisé par indicatif régional.

<BR>
<A NAME="campaigns-campaign_rec_exten">
<BR>
<B>Extension d'enregistrement de la campagnesion -</B> Ce champ permet une extension d enregistrement personnalisé pour être utilisé avec le système. Cela vous permet d utiliser des extensions différentes en fonction de combien de temps vous voulez permettre à un enregistrement maximale et le type de codec que vous souhaitez enregistrer po Le extension par défaut est 8309 qui, si vous suivez les exemples de SCRATCH_INSTALL seront enregistrer au format WAV pour un maximum de une heure. Une autre option inclus dans les exemples est 8310 qui enregistre au format GSM pendant une heure. La durée d enregistrement peut être allongée en augmentant le réglage de l écran de modification du serveur dans la section Admin.

<BR>
<A NAME="campaigns-campaign_recording">
<BR>
<B>Enregistrement de campagne -</B> Ce menu vous autorise à choisir quel niveau d'enregistrement est autorisé pour cette campagne. NEVER désactivera l'enregistrement sur le client. ONDEMAND est la valeur par défaut et autorise l'agent à démarrer et arrêter l'enregistrement au besoin. ALLCALLS démarera l'enregistrement sur le client quand un appel est lancé par un agent. ALLFORCE will start recording on the client whenever a call is sent to an agent giving the agent no option to stop recording. For ALLCALLS and ALLFORCE there is an option to use the Délai d'enregistrement to cut down on very short recordings and reduce system load.

<BR>
<A NAME="campaigns-campaign_rec_filename">
<BR>
<B>Nom du fichier d'enregistrement de la campagne -</B> Ce champ vous permet de personnaliser le nom de l'enregistrement quand la campagne est ONDEMAND ou ALLCALLS. Les variables sont permis CAMPAIGN INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH AGENT VENDORLEADCODE LEADID CALLID. La valeur par défaut est FULLDATE_AGENT et devrait ressembler à ceci 20051020-103108_6666. Un autre exemple est CAMPAIGN_TINYDATE_CUSTPHONE qui ressemblerait à ceci TESTCAMP_51020103108_3125551212. Te résultant nom de fichier doit être inférieure à 90 caractères.

<BR>
<A NAME="campaigns-allcalls_delay">
<BR>
<B>Délai d'enregistrement -</B> Uniquement pour les enregistrements ALLCALLS et ALLFORCE. Cette valeur est le nombre de secondes après lesquelles commence l'enregistrement des appels. La valeur par défaut est 0.

<BR>
<A NAME="campaigns-per_call_notes">
<BR>
<B>Appelez-notes par Appel -</B> La définition de cette option pour activer permettra aux agents d'entrer dans les notes pour chaque appel ils ont accès dans l'interface agent. Le champ de saisie des notes apparaît en dessous du champ de commentaires dans l'interface agent. En outre, si le Groupe de l'agent utilisateur est autorisé à afficher des journaux d'appels, puis l'agent sera en mesure d'afficher des notes d'appel passées pour une avance à tout moment. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-hide_call_log_info">
<BR>
<B>Masquer Info appels Connexion -</B> Activation de cette option permet de masquer tout journal d appels ou appeler l information de comptage lorsque l information plomb est affiché sur l écran de l agent. Défaut est N.

<BR>
<A NAME="campaigns-agent_lead_search">
<BR>
<B>Recherche de plomb Agent -</B> La définition de cette option pour activer permettra aux agents de rechercher des pistes et afficher des informations de plomb pendant la pause dans l'interface agent. En outre, si le Groupe de l'agent utilisateur est autorisé à afficher des journaux d'appels, puis l'agent sera en mesure d'afficher des notes d'appel passées pour toute avance qu'ils sont affichés sur les informations. Par défaut est DISABLED. LIVE_CALL_INBOUND permettra recherche pendant un certain temps d'avance sur un appel entrant uniquement. LIVE_CALL_INBOUND_AND_MANUAL permettra recherche pendant un certain temps d'avance sur un appel entrant ou pendant la pause. Quand Lead Recherche est utilisé sur un appel entrant direct, le chef de file de l'appel quand il est allé à l'agent sera changé pour un statut de LSMERG, et les journaux de l'appel sera modifié pour créer un lien vers l'agent principal choisi à la place.

<BR>
<A NAME="campaigns-agent_lead_search_method">
<BR>
<B>Méthode de recherche d'agent principal -</B> Si la recherche de plomb Agent est activé, ce paramètre définit l'endroit où l'agent ne sera autorisé à rechercher des pistes. Système va rechercher l'ensemble du système, sera CAMPAIGNLISTS chercher au cœur de toutes les listes actives au sein de la campagne, sera CAMPLISTS_ALL chercher au cœur de toutes les listes actives et inactives au sein de la campagne, LISTE va rechercher uniquement dans le ID de liste numérotation manuelle tel que défini dans la campagne . Par défaut est CAMPLISTS_ALL. Une de ces options avec USER_ en face ne recherche que dans les pistes qui ont le champ propriétaire correspondant de l'ID utilisateur de l'agent, les options avec GROUP_ en face ne recherche que dans les pistes qui ont le champ propriétaire correspondant au groupe d'utilisateurs que l'utilisateur est un membre de la, les options avec TERRITORY_ en face ne recherche que dans les pistes qui ont le champ propriétaire adéquation des territoires que l'agent a choisi.

<BR>
<A NAME="campaigns-campaign_script">
<BR>
<B>Script campagne ->/B> Ce menu vous permet de choisir le script qui apparaîtra sur l'écran des agents pour cette campagne. Mettez NONE pour ne pas montrer de script pour cette campagne.

<BR>
<A NAME="campaigns-get_call_launch">
<BR>
<B>Consigne de lancement d'appel -</B> Ce menu vous permet de choisir si vous voulez lancer automatiquement la page de web-form dans une fenêtre séparée, passer automatiquement sur l'onglet de script ou ne rien faire quand un appel est envoyé par un agent dans cette campagne. Si les champs de liste personnalisées sont activées sur votre système, formulaire s'ouvrira sur l'onglet FORMULAIRE lors de la connexion d'un appel à un agent.

<BR>
<A NAME="campaigns-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Ces champs permettent pour vous d avoir deux ensembles de Conférence de transfert et les présélections du DTMF. Lorsque l appel ou de la campagne est chargé, l écran de l agent affiche deux boutons sur le cadre de transfert de conférence et auto-remplir la fois pressée nombre à cadran et les champs d envoi DTMF. Si vous souhaitez autoriser les transferts de consultation, un fronter à un proche, ont l agent utiliser la case CONSULTATIF, qui ne fonctionne pas pour les non-agent des appels de consultation de tiers. Pour ceux qui ont juste l agent, cliquez sur le bouton Dial Avec la clientèle. Ensuite, l agent peut simplement laisser-3P-CALL et de passer à leur prochain appel. Si vous souhaitez autoriser les transferts aveugles de clients à un script AGI pour l exploitation forestière ou un IVR, puis placez AXFER dans le domaine de nombre de ligne. Vous pouvez également spécifier une extension personnalisée après la AXFER, par exemple si vous voulez faire un appel à un IVR spécial que vous avez défini à l extension 83900 vous mettriez AXFER83900 dans le domaine de nombre à cadran.

<BR>
<A NAME="campaigns-quick_transfer_button">
<BR>
<B>Quick Transfer Button -</B> Cette option permet d-ajouter un bouton de transfert rapide à l-écran de l-agent en dessous du bouton Transfer-Conf qui permettra un clic aveugles transfert d-appels à la sélectionnés en-groupe ou un numéro. IN_GROUP va envoyer des appels au groupe par défaut Xfer pour cette campagne, ou en-groupe s-il y avait un appel entrant. Les options prédéfinies vous enverrons les appels à la preset sélectionnée. Par défaut est N pour les handicapés. Les options verrouillés sont utilisés pour verrouiller la valeur de la touche de transfert rapide, même si l'agent utilise les fonctionnalités de conférence de transfert lors d'un appel avant d'utiliser le bouton de transfert rapide.

<BR>
<A NAME="campaigns-custom_3way_button_transfer">
<BR>
<B>Transfert personnalisé Bouton 3-voies -</B> Cette option permet d'ajouter un bouton de transfert personnalisé à l'écran ci-dessous sur le bouton d'agent de transfert-Conf qui permettra d'un clic à trois voies des appels en utilisant le préréglage sélectionné ou sur le terrain. Les options seront PRESET_ passer des appels en utilisant la valeur définie prédéfinie. Les options seront field_ passer des appels en utilisant le numéro dans le champ sélectionné à partir du plomb. HANDICAPÉS ne sera pas afficher le bouton sur l'écran l'agent. Les options seront PARK_ garer le client avant de composer. Par défaut est DISABLED. L'option sera VIEW_PRESET il suffit d'ouvrir le cadre de transfert et le cadre prédéfini. L'option VIEW_CONTACTS va ouvrir une fenêtre de recherche de contacts, cela ne fonctionnera que si Presets Activer est réglé sur CONTACTS.

<BR>
<A NAME="campaigns-prepopulate_transfer_preset">
<BR>
<B>Préremplir Transfert Preset -</B> Cette option permet de remplir le champ Numéro de réseau commuté dans le cadre de transfert de conférence de l-écran de l-agent s-il est défini. Par défaut est N pour les handicapés.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Activer Presets de transfert -</B> Cette option permettra aux présélections de menu sous de comparaître à la partie supérieure de la page de modification de la campagne, et aussi vous aurez la possibilité de spécifier prédéfinis numéros de numérotation pour les agents à utiliser dans le cadre de transfert de la conférence de l'interface de l'agent. Par défaut est DISABLED. CONTACTS est une option seulement si Information_sur_le_contact est activé sur votre système, qui est une fonction personnalisée.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Activer Presets de transfert -</B> Cette option permettra aux présélections de menu sous de comparaître à la partie supérieure de la page de modification de la campagne, et aussi vous aurez la possibilité de spécifier prédéfinis numéros de numérotation pour les agents à utiliser dans le cadre de transfert de la conférence de l'interface de l'agent. Par défaut est DISABLED. CONTACTS est une option seulement si Information_sur_le_contact est activé sur votre système, qui est une fonction personnalisée.

<BR>
<A NAME="campaigns-hide_xfer_number_to_dial">
<BR>
<B>Masquer le numéro de transfert à composer -</B> Cette option permet de cacher le numéro à composer le terrain dans le cadre de transfert de la conférence de l'interface de l'agent. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call">
<BR>
<B>Parc Appel IVR -</B> Cette option permettra à un agent de parquer un appel avec un bouton séparé IVR APPEL DU PARC sur leur interface de l'agent, si cette option est activée ou ENABLED_PARK_ONLY. L'option ENABLED_PARK_ONLY permettra à l'agent d'envoyer l'appel à parc, mais ne cliquez pas sur pour récupérer l'appel, comme l'option Activé. L'option ENABLED_BUTTON_HIDDEN permet à la fonction via l'API uniquement. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call_agi">
<BR>
<B>Parc Appel IVR AGI -</B> Si le champ Parc IVR Appel n'est pas désactivé, alors ce champ est utilisé comme la chaîne de l'application AGI que le client est envoyé. Il s'agit d'un réglage qui doit être définie par votre administrateur si possible.

<BR>
<A NAME="campaigns-timer_action">
<BR>
<B>Minuterie d-action -</B> Cette fonctionnalité vous permet de déclencher des actions après un certain laps de temps. D1 et D2 options DIAL va lancer un appel à la presets Transfer Conference Nombre et les envoyer à la session d-agent, ce n-est généralement utilisé pour les applications IVR AGI simple validation ou tout simplement pour jouer un message pré-enregistré. WebForm ouvrira l-adresse formulaire web. MESSAGE_ONLY va simplement afficher le message qui est dans le champ ci-dessous. AUCUNE désactive cette fonctionnalité et la valeur par défaut. HANGUP raccroche l'appel lorsque la minuterie est déclenchée, CALLMENU enverra l'appel au menu Appel spécifié dans le champ Destination minuterie d'action, EXTENSION enverra l'appel à l'extension qui est spécifié dans le champ Destination minuterie d'action, IN_GROUP enverra l'appel à l'en-groupe spécifié dans le champ Destination minuterie d'action.

<BR>
<A NAME="campaigns-timer_action_message">
<BR>
<B>Minuterie d-action Message -</B> Tel est le message qui s-affiche sur l-écran de l-agent au moment de l-action Timer est déclenché.

<BR>
<A NAME="campaigns-timer_action_seconds">
<BR>
<B>Minuterie d-action Seconds -</B> Il s-agit de la quantité de temps après l-appel est connecté au client que l-action Timer est déclenchée. Par défaut est -1, qui est aussi inactive.

<BR>
<A NAME="campaigns-timer_action_destination">
<BR>
<B>Destination action minuterie -</B> Ce champ est l'endroit où vous spécifiez le menu Appel, Extension ou en groupe que vous voulez que l'appel envoyé à l'action si l'heure est défini CALLMENU, EXTENSION ou IN_GROUP. Par défaut est vide.

<BR>
<A NAME="campaigns-scheduled_callbacks">
<BR>
<B>Rappels programmés -</B> Cette option autorise un agent à statuer un appel comme CALLBK et à choisir une date et une heure à laquelle la fiche sera réactivée.

<BR>
<A NAME="campaigns-scheduled_callbacks_alert">
<BR>
<B>Rappels réguliers d'alerte -</B> Cette option permet de la ligne d'état rappels dans l'interface agent d'être rouge, clignote ou clignote en rouge quand il ya AGENTONLY prévue rappels qui ont frappé leur temps de déclenchement et la date. Par défaut est NONE pour la ligne d'état standard. Les options reporter s'arrête de clignoter et-ou l'affichage en rouge lorsque vous vérifiez les rappels, jusqu'à ce que le nombre de rappels des changements.

<BR>
<A NAME="campaigns-scheduled_callbacks_count">
<BR>
<B>Rappels réguliers comte -</B> These options allows you to limit the viewable callbacks in the agent callback alert section on the agent screen, to only LIVE callbacks.  LIVE callbacks are user-only scheduled callbacks that have hit their trigger date and time. ACTIVE call backs are user-only callbacks that are active in the system but have not yet triggered.  You can view both ACTIVE and LIVE callbacks by selecting ALL_ACTIVE.  Default is ALL_ACTIVE.

<BR>
<A NAME="campaigns-callback_days_limit">
<BR>
<B>Prévu rappels Jours Limite -</B> Cette option vous permet de réduire l'agent rappels réguliers de calendrier pour un nombre sélectionnable de jours à partir d'aujourd'hui, la totalité des 12 mois civils seront toujours affichées, mais seulement le nombre de jours sera sélectionnable. Par défaut est 0 pour un nombre illimité.

<BR>
<A NAME="campaigns-callback_hours_block">
<BR>
<B>Prévu rappels Heures Bloquer -</B> Cette option vous permet de restreindre un rappel useronly prévu d'être affiché sur la liste de rappel jusqu'à ce que l'agent X heures après qu'elle a été définie. Par défaut est 0 pour pas de bloc.

<BR>
<A NAME="campaigns-callback_list_calltime">
<BR>
<B>Rappels réguliers bloc Calltime -</B> Cette option, si activée, permettra d'éviter le rappel prévu dans la liste de rappel d'agent d'être composé si elle est en dehors de la calltime prévue pour la campagne. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-my_callback_option">
<BR>
<B>Mon défaut Checkbox rappels -</B> Cette option vous permet de prédéfinir la case à cocher Mon rappel sur l'écran de rappel agent programmé. Enregistrés seront cochez la case automatiquement pour chaque appel. Par défaut est désactivé.

<BR>
<A NAME="campaigns-wrapup_seconds">
<BR>
<B>Temps de conclusion -<\/B> Nombre de secondes pendant lesquelles l'agent est forcé à attendre avant de pouvoir recevoir ou passer un nouvel appel. Le timer commence dès que l'agent à raccrocher - ou dans le cas de l'appel du numéro alternatif quand l'agent à terminer la fiche - La valeur par défaut est 0. Si le timer se termine avant que l'agent n'ait statué l'appel, l'agent ne passera pas à l'appel suivant tant qu'il n'a pas sélectionné un statut.

<BR>
<A NAME="campaigns-wrapup_message">
<BR>
<B>Message de conclusion -<\/B> C'est un message spécifique à la campagne à afficher si le temps de conclusion est défini.

<BR>
<A NAME="campaigns-disable_dispo_screen">
<BR>
<B>Désactiver l'écran Dispo -</B> Cette option vous permet de désactiver l'écran de la disposition dans l'interface agent. Le champ Désactiver Statut Dispo ci-dessous doit être rempli pour que cette option fonctionne. Par défaut est DISPO_ENABLED. L'option ne sera pas DISPO_SELECT_DISABLED désactiver l'écran de dispo complètement, mais afficher l'écran de dispo sans dispositions, cette option ne doit être utilisé si vous voulez forcer vos agents à utiliser votre logiciel de CRM qui enverra le statut du système via l'API.

<BR>
<A NAME="campaigns-disable_dispo_status">
<BR>
<B>Désactiver Statut Dispo -</B> Si l'option Désactiver l'écran est réglé sur Dispo DISPO_DISABLED, ce champ doit être rempli en jeu. Vous pouvez utiliser n'importe quel caractère que vous voulez pour ce domaine tant qu'il est de 1 à 6 caractères de longueur avec des lettres et des chiffres.

<BR>
<A NAME="campaigns-dead_max">
<BR>
<B>Décédé secondes Appel Max -</B> Si ce paramètre est réglé à plus de 0, après un client raccroche et l agent n a pas cliqué sur le bouton de la clientèle Raccrocher dans ce nombre de secondes, l appel sera automatiquement suspendu, le statut ci-dessous sera mis et l agent sera pause. Défaut est 0 pour les handicapés.

<BR>
<A NAME="campaigns-dead_max_dispo">
<BR>
<B>Morte Statut Appel Max -</B> Si Morte Appel Max secondes est activée, c est le statut fixée pour l appel lorsque l appel morts de l agent n est pas raccroché passé le nombre de secondes défini ci-dessus. Défaut est DCMX.

<BR>
<A NAME="campaigns-dispo_max">
<BR>
<B>Dispo Appel Max secondes -</B> Si ce paramètre est réglé à plus de 0, et l agent n a pas choisi un statut de disposition dans ce nombre de secondes, l appel sera automatiquement mis à l état ci-dessous et l agent sera suspendue. Défaut est 0 pour les handicapés.

<BR>
<A NAME="campaigns-dispo_max_dispo">
<BR>
<B>Dispo Appel Max État -</B> Si Dispo Appel Max secondes est activée, c est le statut fixée pour l appel lorsque l agent n a pas choisi un statut passé le nombre de secondes défini ci-dessus. Défaut est DISMX.

<BR>
<A NAME="campaigns-pause_max">
<BR>
<B>Agent Pause Max secondes -</B> Si ce paramètre est réglé à plus de 0, et l agent n est pas sorti de l état PAUSE dans ce nombre de secondes, l agent sera automatiquement déconnecté de l écran de l agent. Défaut est 0 pour les handicapés.

<BR>
<A NAME="campaigns-screen_labels">
<BR>
<B>Les étiquettes de l'écran de l'agent -</B> Vous pouvez sélectionner un ensemble d'étiquettes d'écran agents à utiliser avec cette option. Par défaut est - SYSTEM-SETTINGS - pour les étiquettes par défaut.

<BR>
<A NAME="campaigns-status_display_fields">
<BR>
<B>Les champs d'affichage d'état -</B> Vous pouvez sélectionner les variables pour les appels seront affichés dans la ligne d'état de l'écran l'agent. CALLID affiche l'ID 20 caractères appel unique, LEADID affiche l'ID de plomb du système, ListId affiche l'ID de liste. Par défaut est CALLID.

<BR>
<A NAME="campaigns-use_internal_dnc">
<BR>
<B>Utiliser la liste DNC interne -</B> Cela défini si cette campagne doit filter les fiches par rapport à la liste DNC. Si ce champ est à Y alors le distributeur de fiches recherchera chaque numéro de téléphone dans la liste DNC avant de la placer dans le distributeur de fiches. Si il est dans la liste DNC alors le statut de la fiche sera changer en DNCL, il ne pour donc pas être appelé. La valeur par défaut est N. L-option AreaCode est comme l-option Y, sauf qu-il est utilisé pour filtrer également un code région entière en Amérique du Nord d-être appelé, dans ce cas en utilisant l-entrée 201XXXXXXX dans la liste DNC serait de bloquer tous les appels à l-indicatif régional 201 si elle est activée.

<BR>
<A NAME="campaigns-use_campaign_dnc">
<BR>
<B>Utilisez Campagne DNC List - </B> Cela définit si cette campagne est de filtrer les mène à une liste DNC qui est spécifique à cette campagne seulement. Si elle est définie à Y, la trémie sera, pour chaque numéro de téléphone dans la campagne des DNC avant de le placer dans la trémie. Si elle est dans la campagne des DNC liste, il va changer le statut qui conduisent à DNCC il ne peut donc pas être composé. La valeur par défaut est N. L-option AreaCode est comme l-option Y, sauf qu-il est utilisé pour filtrer également un code région entière en Amérique du Nord d-être appelé, dans ce cas en utilisant l-entrée 201XXXXXXX dans la liste DNC serait de bloquer tous les appels à l-indicatif régional 201 si elle est activée.

<BR>
<A NAME="campaigns-use_other_campaign_dnc">
<BR>
<B>Autre Campagne DNC -</B> Si l'option Utiliser la liste des DNC de campagne est activée, cette option peut vous permettre d'utiliser une liste de DNC de campagne différente, il suffit de mettre l'ID de campagne de l'Autre Campagne dans ce domaine. Si vous utilisez cette option, la liste DNC de campagne originale ne sera plus cochée, seul l'autre liste de DNC de la campagne sera utilisé. Cela n'affecte pas l'utilisation de la liste DNC système interne. Par défaut est vide.

<BR>
<A NAME="campaigns-closer_campaigns">
<BR>
<B>Groupes entrants autorisés -</B> For CLOSER campaigns only. Here is where you select the inbound groups you want agents in this CLOSER campaign to be able to take calls from. It is important for BLENDED inbound-outbound campaigns only to select the inbound groups that are used for agents in this campaign. The calls coming into the inbound groups selected here will be counted as active calls for a blended campaign even if all agents in the campaign are not logged in to receive calls from all of those selected inbound groups.

<BR>
<A NAME="campaigns-agent_pause_codes_active">
<BR>
<B>AgentCodes De PauseActive -</B> Permet aux agents de choisir un code de pause quand ils cliquent sur le bouton PAUSE sur l écran de l agent. codes de pause sont définissables par campagne au bas de l écran de détail de la vue de la campagne et ils sont stockés dans la table agent_log. Par défaut est N. FORCE va forcer les agents à choisir un code PAUSE s ils cliquent sur le bouton PAUSE.

<BR>
<A NAME="campaigns-auto_pause_precall">
<BR>
<B>Auto Pause pré-Appel de travail -</B> Dans l'auto-sélecteur de mode, ce paramètre si elle est activée seront mis à l'agent de pause automatiquement lorsque l'agent clique sur l'une des fonctions suivantes, qui les oblige à être en pause-Manuel Dial, Fast Dial, Recherche de plomb, Appelez-View Log, rappels Vérifiez, Entrez le code de pause. Par défaut est N pour inactive.

<BR>
<A NAME="campaigns-auto_resume_precall">
<BR>
<B>Auto Reprendre pré-Appel de travail -</B> Dans l'auto-sélecteur de mode, ce paramètre si elle est activée fixera l'agent à l'état actif automatiquement lorsque l'agent clique sur, ou annule, sur l'une des fonctions suivantes, qui les oblige à être en pause Dial-Manuel, Fast Dial, Recherche de plomb, Appelez Connectez-vous Voir, rappels Vérifiez, Entrez le code de pause. Par défaut est N pour inactive.

<BR>
<A NAME="campaigns-auto_pause_precall_code">
<BR>
<B>Auto Pause pré-Appel de code -</B> Si la fonction Auto Pause de travail fonction de pré-appel ci-dessus est active, et les codes de pause Agent est actif, ce paramètre sera le code de pause qui est utilisé lorsque l'agent est en pause pour ces activités. Par défaut est PRECAL.

<BR>
<A NAME="campaigns-disable_alter_custdata">
<BR>
<B>Désactiver la modification des données du client -</B> Si défini à Y alors les données de la fiche d'un client ne sont pas modifiées quand un agent passe l'appel. La valeur pas défaut est N.

<BR>
<A NAME="campaigns-disable_alter_custphone">
<BR>
<B>Désactiver Alter clientèle Téléphone - </B> Si la valeur de Y, ne modifie pas le client numéro de téléphone lors d'un agent dispositions de l'appel. La valeur par défaut est Y. Utilisez l-option Masquer pour supprimer complètement le numéro de téléphone du client depuis l-écran de l-agent.

<BR>
<A NAME="campaigns-display_queue_count">
<BR>
<B>Agent Nombre d'écrans de file d'attente - </B> Si la valeur de Y, lorsque le client est en attente d'un agent, la file d'attente de demande d'affichage en haut de l'agent de l'écran devient rouge et indique le nombre d'appels en attente. Le défaut est Y.

<BR>
<A NAME="campaigns-manual_dial_override">
<BR>
<B>Composition manuelle Override -</B> Le réglage peut l'emporter sur les utilisateurs de réglage de la capacité numérotation manuelle pour les agents quand ils sont enregistrés dans cette campagne. NONE suivra la mise en utilisateurs, ALLOW_ALL permettra à tout agent connecté dans cette campagne pour passer des appels numérotation manuelle, DISABLE_ALL ne permettra à personne connecté à cette campagne pour passer des appels numérotation manuelle. Par défaut est NONE.

<BR>
<A NAME="campaigns-manual_dial_list_id">
<BR>
<B>Manual Dial ID de la liste -</B> La valeur par défaut list_id être utilisé lorsqu un agent met un appel manuel et un nouveau record de plomb est créé dans le tableau de la liste. Par défaut est 999. Ce champ peut contenir que des chiffres.

<BR>
<A NAME="campaigns-manual_dial_filter">
<BR>
<B>Manuelle Filter - </B> Cela vous permet de filtrer les appels que les agents font en mode manuel composer pour cette campagne par une combinaison des éléments suivants: DNC - à coup sur, CAMPAIGNLISTS - le nombre doit être dans les listes pour la campagne, AUCUN - pas de filtre sur le cadran ou manuel rapide composer des listes. CAMPLISTS_ALL - comportera des listes inactives dans la recherche pour le nombre.

<BR>
<A NAME="campaigns-manual_dial_search_checkbox">
<BR>
<B>Composition manuelle Recherche cocher -</B> Cela vous permet de définir si vous souhaitez que la numérotation manuelle recherche case pour être sélectionné par défaut ou non. Si une option de RESET est choisi, alors la case sera remis à zéro après chaque appel. Par défaut est sélectionné.

<BR>
<A NAME="campaigns-manual_preview_dial">
<BR>
<B>Dial Aperçu Manuel -</B> Cela permet à l'agent en mode numérotation manuelle pour voir le principal de l'information quand ils cliquez Composez le numéro suivant avant de composer activement l'appel téléphonique. Il ya un lien en option pour sauter la tête et de passer à la suivante si elle est sélectionnée. Par défaut est PREVIEW_AND_SKIP.

<BR>
<A NAME="campaigns-manual_dial_lead_id">
<BR>
<B>Composition manuelle par Lead ID -</B> Cela permet à l agent dans le mode de numérotation manuelle pour passer un appel par lead_id au lieu d un numéro de téléphone. Défaut est N pour les handicapés.

<BR>
<A NAME="campaigns-api_manual_dial">
<BR>
<B>Composition manuelle API -</B> Cette option vous permet de définir l'API agent de faire soit un appel à la fois, STANDARD, ou la capacité de la file d'attente des appels numérotation manuelle et de les faire composer automatiquement une fois que l'agent va le mettre en pause ou est disponible pour prendre leur prochain appel à la option pour désactiver la numérotation automatique de ces appels, file d'attente ou QUEUE_AND_AUTOCALL qui est le même que file d'attente mais sans l'option pour désactiver la numérotation automatique de ces appels. Si un agent a plus d'un appel en file d'attente pour eux, ils verront le nombre de combien d'appels numérotation manuelle sont en file d'attente juste au-dessous sur le bouton Pause, ou Composez le bouton numéro suivant. Nous suggérons que si QUEUE est utilisé que vous envoyez en utilisant l'API actions de la preview = OUI option de sorte que vous ne sont pas reprises pour composer les appels de l'agent sans préavis. En outre, si vous utilisez file d'attente et fortement en utilisant les appels de numérotation manuelle à une méthode cadran MANUEL non, nous recommandons de régler le Pause agent après chaque option Appel à Y. par défaut est Standard.

<BR>
<A NAME="campaigns-manual_dial_call_time_check">
<BR>
<B>Durée de l'appel Vérification manuelle -</B> Si cette option est activée, il va vérifier tous les appels numérotation manuelle pour s'assurer qu'ils sont dans les paramètres de temps d'appel fixés pour la campagne. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-manual_dial_cid">
<BR>
<B>Composition manuelle CID -</B> Ceci définit si un agent des appels numérotation manuelle aura les paramètres de campagne CallerID utilisé, ou de leurs agents téléphoniques paramètres CallerID utilisé. Par défaut est CAMPAGNE. Si l'option Utiliser personnalisé campagne CID est activée ou de la Campagne liste Override réglage CID est utilisé, ce paramètre sera ignoré.

<BR>
<A NAME="campaigns-post_phone_time_diff_alert">
<BR>
<B>Message Téléphone Décalage horaire alerte -</B> Cette fonction manuelle cadran seule, si elle est activée, va afficher une alerte si le fuseau horaire pour le code postal de plomb, ou le code postal, est différente de la zone de temps de l'indicatif régional du numéro de téléphone pour le plomb. L'option OUTSIDE_CALLTIME_ONLY ne montrent l'alerte si les deux fuseaux horaires sont différents et l'un des fuseaux horaires est en dehors de la durée d'appel choisi pour la campagne. OUTSIDE_CALLTIME_PHONE ne fera que vérifier le fuseau horaire du numéro de téléphone de la tête et d'alerter si elle est en dehors du temps d'un appel local. OUTSIDE_CALLTIME_POSTAL ne fera que vérifier le fuseau horaire du code postal du plomb et d'alerter si elle est en dehors du temps d'un appel local. OUTSIDE_CALLTIME_BOTH va vérifier le code postal et le numéro de téléphone pour être dans le délai d'un appel local, même si elles sont dans le même fuseau horaire. Ces alertes seront afficher dans l'info journal des appels, Informations sur la liste des rappels, des résultats de recherche d'info, quand un chef de file est composé et quand un chef de file est visualisé. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-in_group_dial">
<BR>
<B>In-Group Composition manuelle -</B> Cette fonction vous permet d'activer la possibilité pour les agents de placer numérotation des appels sortants manuelles qui sont enregistrés comme en appels de groupe affecté à un particulier en groupe. L'option MANUAL_DIAL permet la mise en place des appels téléphoniques à travers un In-groupe à l'agent effectuant l'appel. L'option NO_DIAL permet à l'agent de se connecter de temps sur un appel qui n'existe pas, comme s'il s'agissait d'un véritable appel, ce qui est souvent utilisé pour la connexion e-mail ou télécopie temps. Le BOTH option permettra à la fois appel et sans appel de composition en groupe. Désactivé par défaut.

<BR>
<A NAME="campaigns-in_group_dial_select">
<BR>
<B>In-Group Manuel molette SELECT -</B> Cette option n'est active que si le dessus en groupe fonction de composition manuelle n'est pas désactivé. Cette option limite le choix In-groupes que l'agent peut placer In-Group Manuel composer des appels à travers. CAMPAIGN_SELECTED n'indiquera que les en-groupes que la campagne s'est fixé comme admissible au-groupes. ALL_USER_GROUP montrera tous les en-groupes qui sont visibles pour les membres du groupe d'utilisateurs qui appartient l'agent.

<BR>
<A NAME="campaigns-agent_clipboard_copy">
<BR>
<B>Agent de presse-papiers de copie d'écran - </B> Cette fonctionnalité est actuellement disponible que pour Internet Explorer. Cette fonctionnalité vous permet de sélectionner un domaine qui sera copiée dans le presse-papiers de l'ordinateur de l'agent sur l'ordinateur d'un appel d'être envoyées à un agent. Commune utilise pour cela sont pour faciliter le collage de numéros de compte ou des numéros de téléphone en héritage les applications client à l'agent d'ordinateur.

<BR>
<A NAME="campaigns-three_way_call_cid">
<BR>
<B>3-Way Call CallerID sortant -</B> Ceci définit ce qui est envoyé sur le nombre de callerID sortant des appels à 3 voies placés par l agent, CAMPAGNE utilise la campagne personnalisée callerID, CLIENT utilise le numéro de client qui est actif sur les agents écran et AGENT_PHONE utilise le callerID pour le téléphone que l agent est connecté. AGENT_CHOOSE permet à l agent de choisir qui callerID à utiliser pour 3 voies appels dans une liste de choix. CUSTOM_CID utilisera le CID personnalisé qui est défini dans le champ security_phrase de la table de liste pour le plomb.

<BR>
<A NAME="campaigns-three_way_dial_prefix">
<BR>
<B>3-Way Call Préfixe de numérotation - </B> Cela définit ce qui est utilisé comme préfixe pour les 3-way appels, par défaut est vide de sorte que la campagne de préfixe est utilisé, passthru afin que vous puissiez entendre sonner est de 88.

<BR>
<A NAME="campaigns-customer_3way_hangup_logging">
<BR>
<B>Logging Raccrocher à la clientèle 3-voies -</B> Si cette option est activée, le user_call_log se connectera quand un raccrochage à la clientèle jusqu'à s'ils raccrocher pendant un appel 3-way. En outre, cela peut permettre au Client d'action de raccrochage 3-way si l'on est defineed ci-dessous. Par défaut est Enabled.

<BR>
<A NAME="campaigns-customer_3way_hangup_seconds">
<BR>
<B>Secondes hangup clientèle 3-voies -</B> Si le Client 3-way journalisation est activée, cette option vous permet de définir le nombre de secondes après le raccrochage client est détecté avant qu'il ne soit connecté et l'action à la clientèle en option raccrochage 3-way est exécuté. Par défaut est de 5 secondes.

<BR>
<A NAME="campaigns-customer_3way_hangup_action">
<BR>
<B>Raccrocher à la clientèle d'action 3-voies -</B> Si le Client 3-way journalisation est activée, cette option vous permet d'avoir l'écran l'agent raccroche automatiquement sur l'appel et passer à l'écran DISPO si cette option est réglée sur DISPO. Par défaut est NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-qc_enabled">
	<BR>
	<B>QC Activé - </B> Mise en ce domaine permet à Y pour l'agent de contrôle de la qualité caractéristiques au travail. La valeur par défaut est N.

	<BR>
	<A NAME="campaigns-qc_statuses">
	<BR>
	<B>Statuts QC - </B> Cette zone est l'endroit où vous choisissez ce qui entraîne des statuts doit être dépassé par le système de QC. Placez un contrôle à côté de l'état que vous voulez examiner QC. 

	<BR>
	<A NAME="campaigns-qc_shift_id">
	<BR>
	<B>QC Shift - </B> Il s'agit du changement de calendrier utilisé pour tirer QC pour une campagne. Les jours de la semaine sont ignorées pour ces fonctions.

	<BR>
	<A NAME="campaigns-qc_get_record_launch">
	<BR>
	<B>Record QC Get lancement </B> Cela permet à l'une des actions suivantes peut être déclenché sur un agent QC recevoir un nouveau record.

	<BR>
	<A NAME="campaigns-qc_show_recording">
	<BR>
	<B>QC Voir Recording - </B> qui permet un enregistrement qui mai être liée à la QC record à afficher dans le QC agent écran.

	<BR>
	<A NAME="campaigns-qc_web_form_address">
	<BR>
	<B>QC WebForm Adresse - </B> Il s'agit de l'adresse du site web d'un agent QC pouvez aller à en cliquant sur le lien dans la WebForm QC écran.

	<BR>
	<A NAME="campaigns-qc_script">
	<BR>
	<B>Script QC - </B> C'est le script qui peut être utilisé par les agents QC SCRIPT dans l'onglet de la QC écran.
	<?php
	}
?>

<BR>
<A NAME="campaigns-vtiger_search_category">
<BR>
<B>Vtiger Search Category -</B> Si l intégration Vtiger est activée dans les paramètres du système, alors ce paramètre définit où la page de vtiger_search.php recherche le numéro de téléphone qui a été entré. Il ya 4 options qui peuvent être utilisés dans ce domaine: LEAD-Cette option recherche à travers le Vtiger conduit uniquement, COMPTE-Cette option recherche à travers les comptes vtiger et tous les contacts et les sous-contacts pour le numéro de téléphone, VENDEUR-Cette option recherchera seulement par les fournisseurs vtiger, ACCTID-Cette option ne fonctionne que pour les comptes et il faudra le domaine de vendor_lead_code de liste et essayer de rechercher l ID de compte Vtiger. En cas d échec, il va essayer d autres méthodes énumérées que vous avez sélectionné. Plusieurs options peuvent être utilisées pour chaque recherche, mais sur de grandes bases de données ce n est pas recommandé. Défaut est LEAD. UNIFIED_CONTACT-Cette option utilise la bêta Vtiger 5.1.0 fonction de recherche par numéro de téléphone et mettre en place une page de recherche dans Vtiger.

<BR>
<A NAME="campaigns-vtiger_search_dead">
<BR>
<B>Vtiger Recherche Dead Comptes -</B> Si Vtiger intégration est activée dans les paramètres du système, puis, cette option détermine si les comptes supprimés sera recherché lorsque l-agent clics WEB forme pour chercher dans le système de Vtiger. Handicapés conduit supprimés ne seront pas cherché, ASK-deleted conduit seront recherchés et la page de recherche vtiger web demandera à l-agent si ils veulent faire le compte Vtiger active, ressusciter-rend automatiquement le compte supprimé de nouveau active et elle prendra les agent à l-écran de compte sans tarder et à cliquer sur un formulaire Web. Par défaut est Disabled.

<BR>
<A NAME="campaigns-vtiger_create_call_record">
<BR>
<B>Vtiger Créer Call Record - </B> Si Vtiger intégration est activé dans les paramètres du système puis de définir ce paramètre est de savoir si un nouveau record d'activité Vtiger est créé pour l'appel lorsque l'agent est à la vtiger_search page. La valeur par défaut est Y. L-option DISPO créera un enregistrement d-appel pour le compte Vtiger sans que l-agent ayant besoin d-aller à la page de recherche vtiger Via le formulaire web.

<BR>
<A NAME="campaigns-vtiger_create_lead_record">
<BR>
<B>Vtiger Créer Lead Record - </B> Si Vtiger intégration est activé dans les paramètres système et Vtiger LEAD Recherche Catégorie comprend alors ce paramètre est de déterminer si un nouveau record Vtiger plomb est créé lorsque l'agent est à la page vtiger_search et il n'est pas ont trouvé à l'appel le numéro de téléphone. La valeur par défaut est Y.

<BR>
<A NAME="campaigns-vtiger_screen_login">
<BR>
<B>Vtiger Screen Login -</B> Si l intégration Vtiger est activée dans les paramètres du système, alors ce paramètre définit si l utilisateur est connecté à l interface Vtiger automatiquement lorsqu ils se connectent à l écran de l agent. Défaut est Y. L option NEW_WINDOW ouvrira une nouvelle fenêtre lors de la connexion à l écran de l agent.

<BR>
<A NAME="campaigns-vtiger_status_call">
<BR>
<B>Vtiger état des appels -</B> Si l intégration Vtiger est activée dans les paramètres du système, alors ce paramètre définit si le statut du compte Vtiger sera mise à jour avec l état de l appel après avoir été dispositioned. Défaut est N.

<BR>
<A NAME="campaigns-queuemetrics_callstatus">
<BR>
<B>QM CallStatus Override -</B> Si l'intégration QueueMetrics est activée dans les paramètres du système, alors ce paramètre permet l'impérieuse des paramètres du système de réglage pour les entrées queue_log CallStatus. Par défaut est DISABLED qui utilisera le réglage du système.

<BR>
<A NAME="campaigns-queuemetrics_phone_environment">
<BR>
<B>Environnement Téléphone QM -</B> Si l'intégration QueueMetrics est activée dans les paramètres du système, alors ce paramètre permet l'insertion de cette valeur de données dans le domaine de l'données4 queue_log pour les enregistrements d'activité des agents. Par défaut est vide pour les handicapés.

<BR>
<A NAME="campaigns-extension_appended_cidname">
<BR>
<B>Extension Ajoutez CID -</B> Si elle est activée, les appels effectués à partir de cette campagne aura un espace et l'extension de téléphone de l'agent ajouté à la fin du nom de CallerID de l'appel avant qu'il ne soit envoyé à l'agent. Par défaut est N pour les handicapés.

<BR>
<A NAME="campaigns-pllb_grouping">
<BR>
<B>Groupement PLLB -</B> Connexion Téléphone Load Balancing Groupement, n'est autorisée que si il ya des serveurs d'agents multiples et les alias de téléphone sont présents sur le système. S'il est défini à ONE_SERVER_ONLY il va forcer tous les agents de cette campagne pour se connecter au même serveur. S'il est défini à CASCADE il regroupera connecté agents sur le même serveur jusqu'à ce que le numéro PLLB Limite Regroupement des agents sont atteints, alors le prochain agent va se connecter au serveur suivant avec le plus petit nombre d'agents. Si elle est désactivée le téléphone comportement standard alias de chaque agent de trouver le serveur avec le moins grand nombre de non-distance des agents connectés à elle sera utilisée. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-pllb_grouping_limit">
<BR>
<B>Limite Groupement PLLB -</B> Connexion Charge Téléphone Groupement Limite d'équilibrage. Si Groupement PLLB est réglé sur CASCADE, alors ce paramètre détermine le nombre d'agents acceptable dans chaque serveur pour cette campagne. Par défaut est 50.

<BR>
<A NAME="campaigns-crm_popup_login">
<BR>
<B>CRM Popup Login -</B> Si elle est égale à Y, le CRM Popup adresse est utilisée pour ouvrir une nouvelle fenêtre sur login agent pour cette campagne. Par défaut est n.

<BR>
<A NAME="campaigns-crm_login_address">
<BR>
<B>CRM Popup Adresse -</B> L-adresse Web d-une page de connexion de CRM, il peut avoir des variables de population tout comme l-adresse sous forme d-enchaînement, avec la VaR à l-avant et à l-aide - Un user_custom_one - - B - de définir des variables.

<BR>
<A NAME="campaigns-start_call_url">
<BR>
<B>Début de l-appel Web -</B> Cette web adresse URL n-est pas perçu par l-agent, mais elle est appelée chaque fois qu-un appel est adressé à un agent si elle est peuplée. Utilise les mêmes variables que les champs de formulaire web et des scripts. Cette URL ne peut être un chemin relatif. L-URL de début ne fonctionne pas pour les appels cadran manuel. Par défaut est vide.

<BR>
<A NAME="campaigns-dispo_call_url">
<BR>
<B>Dispo Call Web -</B> Cette web adresse URL n-est pas perçu par l-agent, mais elle est appelée chaque fois qu-un appel est dispositioned par un mandataire si elle est peuplée. Utilise les mêmes variables que les champs de formulaire web et des scripts. DISPO et talk_time sont les variables que vous pouvez utiliser pour récupérer l-agent défini par la disposition de l-appel et le temps de parole effective en quelques secondes de l-appel. Cette URL ne peut être un chemin relatif. Par défaut est vide.

<BR>
<A NAME="campaigns-na_call_url">
<BR>
<B>Aucune URL Appel Agent -</B> Cette adresse URL Web n'est pas vu par l'agent, mais si elle est peuplée, il est appelé à chaque fois un appel qui n'est pas gérée par un agent est suspendu ou transféré. Utilise les mêmes variables que les champs du formulaire Web et les scripts. dispo peut être utilisée pour récupérer le système défini par la disposition de l'appel. Cette URL ne peut pas être un chemin relatif. Par défaut est vide.

<BR>
<A NAME="campaigns-agent_allow_group_alias">
<BR>
<B>Groupe Alias Admis - </B> Si vous souhaitez permettre à vos agents d'utiliser les alias de groupe, vous devez mettre à Y. alias Groupe sont expliqués plus en section administrative, ils permettent de sélectionner différents agents callerIDs manuel pour les appels sortants qu'ils mai place. La valeur par défaut est N.

<BR>
<A NAME="campaigns-default_group_alias">
<BR>
<B>Groupe par défaut Alias - </B> Si vous avez permis à ce groupe Alias est l'alias du groupe qui est sélectionné en premier par défaut lorsque l'agent choisit d'utiliser un alias pour un groupe émetteur manuel appel. La valeur par défaut est None ou vide.

<BR>
<A NAME="campaigns-view_calls_in_queue">
<BR>
<B>Agent Voir appels en attente -</B> Si défini à autre chose mais aucun, les agents seront en mesure de voir des détails sur les appels qui sont en attente dans la file d-attente dans leur écran de l-agent. S-il est défini à une valeur numérique, les appels affiché sera limité au nombre sélectionné. Par défaut est AUCUN.

<BR>
<A NAME="campaigns-view_calls_in_queue_launch">
<BR>
<B>Voir les appels en attente de lancement -</B> Ce paramètre s-il est réglé sur AUTO auront la demande, dans la file d-attente apparaissent cadre moment de la connexion par l-agent dans l-écran de l-agent. Par défaut est Manuel.

<BR>
<A NAME="campaigns-grab_calls_in_queue">
<BR>
<B>Agent Grab appels en attente -</B> Cette option s-il est réglé sur Y permet à l-agent pour sélectionner l-appel qui veulent prendre de la demande, dans la file d-attente d-affichage en cliquant sur elle pendant la pause. Agents ne seront en mesure de saisir les appels entrants ou les appels transférés, les appels sortants pas. Par défaut est n.

<BR>
<A NAME="campaigns-call_requeue_button">
<BR>
<B>Agent d-appel Re-Queue Button -</B> Cette option s-il est réglé sur Y va ajouter un bouton Re-file d-attente des clients à l-écran de l-agent, permettant à l-agent d-envoyer l-appel dans une file d-attente AGENTDIRECT qui est réservé à l-agent seulement. Par défaut est n.

<BR>
<A NAME="campaigns-pause_after_each_call">
<BR>
<B>Agent Pause après chaque appel -</B> Cette option s-il est réglé sur Y se mettra en pause l-agent après chaque appel automatiquement. Par défaut est n.

<BR>
<A NAME="campaigns-pause_after_next_call">
<BR>
<B>Agent Pause après appel Lien suivant -</B> Cette option si elle est activée affichera un lien sur l'écran de l'agent qui vous permettra de l'agent aller en pause automatiquement après avoir raccroché leur prochain appel. Est désactivée par défaut.

<BR>
<A NAME="campaigns-blind_monitor_warning">
<BR>
<B>Attention Moniteur aveugles -</B> Cette option si elle est activée permettra à l'agent sais de diverses manières optionnelles si elles sont d'être aveugle contrôlée par quelqu'un. DISABLED signifie que cette fonction n'est pas active, alerte ne s'affiche une alerte à l'écran l'agent, AVIS publiera une note qui reste sur l'écran aussi longtemps que l'agent theyâ re objet d'un suivi, AUDIO jouera le nom de fichier défini ci-dessous lors d'un agent commence à être surveillés et les autres options sont combibnations des options susmentionnées. Par défaut est DISABLED.

<BR>
<A NAME="campaigns-blind_monitor_message">
<BR>
<B>Avis aveugles Moniteur -</B> Tel est le message qui apparaîtra sur l'écran tandis que l'agent qu'ils sont surveillés si l'option est sélectionnée AVIS. Par défaut est -Someone is blind monitoring your session-.

<BR>
<A NAME="campaigns-blind_monitor_filename">
<BR>
<B>Nom du moniteur aveugles -</B> Il s'agit du fichier audio qui jouera lors de la session des agents au début de la surveillance quelqu'un aveugler. Cette invite sera joué pour tout le monde lors de la session, y compris le client le cas échéant est présent. Par défaut est vide.

<BR>
<A NAME="campaigns-max_inbound_calls">
<BR>
<B>Appels entrants Max -</B> Si ce paramètre est réglé sur un nombre supérieur à 0, alors il sera le nombre maximum d appels entrants que l agent peut traiter tous les groupes entrants en une seule journée. Si l agent atteint le nombre maximum d appels entrants, alors ils ne seront pas en mesure de sélectionner des groupes entrants pour prendre les appels de jusqu au lendemain. Ce réglage peut être modifié par le réglage de l utilisateur du même nom. Défaut est 0 pour les handicapés.






<BR><BR><BR><BR>

<B><FONT SIZE=3>LISTE TABLE</FONT></B><BR><BR>
<A NAME="lists-list_id">
<BR>
<B>ID de la liste -</B> C'est le nom numérique de la liste, il n'est pas éditable après la première validation, il ne doit contenir que des nombres et contenir entre 2 et 8 caractères. Must be a number greater than 100.

<BR>
<A NAME="lists-list_name">
<BR>
<B>Nom de la liste -</B> C'est la description de la liste, elle doit contenir entre 2 et 20 caractères.

<BR>
<A NAME="lists-list_description">
<BR>
<B>Description de la liste -</B> C'est un champ de note pour la liste, il est optionnel.

<BR>
<A NAME="lists-list_changedate">
<BR>
<B>Dernier changement de la liste -</B> C'est la dernière fois que les réglages de la liste ont été modifié.

<BR>
<A NAME="lists-list_lastcalldate">
<BR>
<B>Date du dernier appel de la liste -</B> C'est la dernière fois qu'une fiche de cette liste a été appelée.

<BR>
<A NAME="lists-campaign_id">
<BR>
<B>Campagne -</B> C'est la campagne à laquelle cette liste appartient. Une liste ne peut etre appelée que depuis une seule campagne à la fois.

<BR>
<A NAME="lists-active">
<BR>
<B>Active -</B> Cela définie si la liste peut etre appelée ou non.

<BR>
<A NAME="lists-reset_list">
<BR>
<B>Réinisialiser le Lead-Called-Status pour cette liste -</B> Cela réinitialise les fiches dans cette liste à N pour "not called since last reset" et signifie que n'importe quelle fiche peut maintenant être appelée si ce statut est défini dans l'écran de campagne.

<BR>
<A NAME="lists-reset_time">
<BR>
<B>Reset Times -</B> Ce champ vous permet de mettre fois, séparées par un tiret, que cette liste sera automatiquement remis à zéro par le système. Les heures doivent être au format 24 heures sans ponctuation, par exemple 0800-1700 serait rétablir la liste à 8 h et 5pm tous les jours. Par défaut est vide.

<BR>
<A NAME="lists-expiration_date">
<BR>
<B>Date d'expiration -</B> Cette option vous permet de définir la date après laquelle conduit dans cette liste ne seront pas autorisés à être composés automatiquement ou manuel-list-composé par le système. Par défaut 2099-12-31.

<BR>
<A NAME="lists-audit_comments">
<BR>
<B>Commentaires de vérification -</B> Cette option permet de commentaires pour être déplacés vers une table d'audit. Ce n'est plus modifiable, mais visible avec la date-time-créateur de chaque commentaire. Défaut est N. Il s'agit d'une partie du contrôle de la qualité Add-On.

<BR>
<A NAME="lists-agent_script_override">
<BR>
<B>Agent Script Override -</B> Si ce champ est défini, ce sera le script que l-agent voit sur son écran au lieu du script campagne lorsque le plomb est de cette liste. Défaut n-est pas réglé.

<BR>
<A NAME="lists-campaign_cid_override">
<BR>
<B>Campagne CID Override -</B> Si ce champ est définie, elle aura préséance sur la campagne CallerID qui est défini pour les appels qui sont placés devant une piste dans cette liste. Défaut n-est pas réglé.

<BR>
<A NAME="lists-am_message_exten_override">
<BR>
<B>Message de répondeur Override -</B> Si ce champ est définie, elle aura préséance sur le message répondeur mis dans la campagne pour les clients de cette liste. Défaut n-est pas réglé. 
<BR>
<A NAME="lists-drop_inbound_group_override">
<BR>
<B>Drop Entrant Groupe Override -</B> Si ce champ est défini, dans ce groupe seront utilisées pour les appels sortants de cette liste de cette goutte de la campagne à l-étranger au lieu de la baisse du groupe fixé dans l-écran de détail de la campagne. Défaut n-est pas réglé.

<BR>
<A NAME="lists-web_form_address">
<BR>
<B>Formulaire Web Override -</B> C est l adresse personnalisée que cliquant sur le bouton de formulaire Web sur l écran de l agent vous amène à des appels qui arrivent sur cette liste. Si vous voulez utiliser des champs personnalisés dans une adresse de formulaire Web, vous devez ajouter et CF_uses_custom_fields = Y dans le cadre de votre URL.

<BR>
<A NAME="lists-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf Nombre Override -</B> Ces cinq domaines pour vous permettre d-emporter les préréglages numéro de la conférence de transfert lorsque le plomb est de cette liste. Par défaut est vide.

<BR>
<A NAME="lists-inventory_report">
<BR>
<B>Rapport d inventaire -</B> Si le Rapport d'inventaire est activé sur votre système, cette option permettra de déterminer si cette liste est inclus dans le rapport ou non. Par défaut est O pour oui.

<BR>
<A NAME="lists-time_zone_setting">
<BR>
<B>Réglage du fuseau horaire -</B> Cette option vous permet de définir la méthode de maintien de la recherche fuseau horaire actuel pour les fils de cette liste. Ce processus se fait uniquement la nuit de sorte que toute modification apportée ne sera pas immédiate. COUNTRY_AND_AREA_CODE est la valeur par défaut, et utiliser le code pays et l'indicatif régional du numéro de téléphone afin de déterminer le fuseau horaire de la tête. Postal_code utilisera le code postal si disponible afin de déterminer le fuseau horaire de la tête. NANPA_PREFIX ne fonctionne que dans les Etats-Unis et utiliser le code régional et le préfixe du numéro de téléphone afin de déterminer le fuseau horaire de la tête, mais ce n'est pas activée par défaut dans le système, donc s'il vous plaît assurez-vous que vous avez les données chargées sur le préfixe NANPA votre système avant de sélectionner cette option. OWNER_TIME_ZONE_CODE utilisera le abbraviation fuseau horaire standard chargé dans le domaine propriétaire du plomb afin de déterminer le fuseau horaire, dans les exemples USA sont AST, HNE, CST, MST, PST, AKST, TVH. Cette fonction doit être activée par votre administrateur système pour entrer en vigueur.


<BR>
<A NAME="internal_list-dnc">
<BR>
<B>Interne liste DNC -</B> This Do Not Call list contains every lead that has been set to a status of DNC in the system. Through the LISTS - AJOUTER LE NUMERO A LA LISTE DNC page you are able to manually add numbers to this list so that they will not be called by campaigns that use the internal DNC list. Il ya aussi la possibilité d'ajouter conduit à la campagne des listes DNC et pour les campagnes qui ont les. Si vous avez l-option active DNC réglé sur AreaCode alors vous pouvez également utiliser les entrées génériques indicatif régional comme celui-ci 201XXXXXXX de bloquer tous les appels au 201 lorsque AreaCode permis.



<BR><BR><BR><BR>

<B><FONT SIZE=3>TABLE INBOUND_GROUPS</FONT></B><BR><BR>
<A NAME="inbound_groups-group_id">
<BR>
<B>ID du groupe -</B> C'est le nom court du group entrant, il n'est pas éditable après la première initialisation, il doit contenir entre 2 est 20 caractères.

<BR>
<A NAME="inbound_groups-group_name">
<BR>
<B>Nom du groupe -</B> C'est la déscription du groupe, il doit contenir entre 2 et 30 caractères et ne doit pas contenir de tirets, de plus ou d'espaces .

<BR>
<A NAME="inbound_groups-group_color">
<BR>
<B>Couleur du groupe -</B> C est la couleur qui s affiche dans l application cliente de l agent quand un appel arrive sur ce groupe. Il doit être compris entre 2 et 7 caractères. S il s agit d une définition de la couleur de sortilège que vous devez vous rappeler de mettre un # au début de la chaîne ou l écran de l agent ne fonctionne pas correctement.

<BR>
<A NAME="inbound_groups-active">
<BR>
<B>Active -</B> Cela détermine si ce groupe entrant est disponible pour prendre des appels. Si ce paramètre est réglé à l état inactif alors le After Hours action sera utilisé sur tous les appels à venir en elle.

<BR>
<A NAME="inbound_groups-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour ce groupe entrant, ce qui permet la visualisation de cette administration en groupe restreint par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce groupe en.

<BR>
<A NAME="inbound_groups-group_calldate">
<BR>
<B>En-Groupe Calldate -</B> Il s'agit de la dernière date et l'heure qu'un appel a été orientée vers ce groupe entrant.

<BR>
<A NAME="inbound_groups-web_form_address">
<BR>
<B>Formulaire Web -</B> C est l adresse personnalisée que cliquant sur le bouton de formulaire Web sur l écran de l agent vous amène à des appels qui arrivent sur ce groupe. Si vous voulez utiliser des champs personnalisés dans une adresse de formulaire Web, vous devez ajouter et CF_uses_custom_fields = Y dans le cadre de votre URL.

<BR>
<A NAME="inbound_groups-next_agent_call">
<BR>
<B>Appel de l'agent suivant -</B> Cela determine quel agent recevra l'appel suivant quand c'est possible:
 <BR> &nbsp; - random: commandes de la valeur de mise à jour aléatoire dans la table live_agents
 <BR> &nbsp; - oldest_call_start: ordonne par la dernière date à laquelle un agent a passé un appel. Résultats des agents recevant globalement un nombre à peu près identique d'appels .
 <BR> &nbsp; - oldest_call_finish: ordonne suivant la dernière date à laquelle un agent a terminé un appel. L'agent AKA attendant le plus longtemps reçoit le premier appel.
 <BR> &nbsp; - oldest_inbound_call_start: les commandes par la dernière fois qu'un agent a été envoyé un appel entrant. Résultats dans les agents recevant environ le même nombre d'appels globale.
 <BR> &nbsp; - oldest_inbound_call_finish: les commandes par la dernière fois un agent de terminer un appel entrant. Agent de AKA attente plus longue reçoit d'abord appeler.
 <BR> &nbsp; - overall_user_level: commandes par le user_level de l agent tel que défini dans la table des utilisateurs un user_level supérieur recevront plus d appels.
 <BR> &nbsp; - inbound_group_rank : ordres par le rang donné à l'agent pour legroupe d'arrivée spécifique. Le plus haut à le plus bas.
 <BR> &nbsp; - campaign_rank : ordres par le rang donné à l'agent pour la campagne.Le plus haut à le plus bas.
 <BR> &nbsp; - ingroup_grade_random: donne une plus grande probabilité de recevoir un appel aux agents de rang supérieur par l'intérieur du groupe.
 <BR> &nbsp; - campaign_grade_random: donne une plus grande probabilité de recevoir un appel aux agents de rang supérieur par la campagne.
 <BR> &nbsp; - fewest_calls : ordres par le nombre d'appels reçus par un agent pource groupe d'arrivée spécifique. Moindres appels d'abord.
 <BR> &nbsp; - fewest_calls_campaign: orders by the number of calls received by an agent for the campaign. Least calls first.
 <BR> &nbsp; - longest_wait_time: commandes par la quantité de temps d-attente agent a été activement pour un appel.
 <BR> &nbsp; - ring_all: rings all available agents until one picks up the phone.
 <BR> NOTES: Pour ring_all, les agents qui utilisent les téléphones qui ont permis d'agent Le crochet auront leur téléphone sonne et le premier à répondre recevront l'appel et les informations sur l'écran l'agent. Depuis ring_all ignore le temps d'attente d'agent et le classement et fera appel à tout agent qui est disponible pour la file d'attente, nous ne recommandons pas d'utiliser cette méthode pour grandes files d'attente. Lorsque vous utilisez ring_all, agents connectés avec les téléphones qui ont de l'agent Crochet Sur désactivé devrez utiliser les appels en file d'attente et panneau de cliquer sur le lien APPEL TAKE pour prendre des appels en file d'attente. La quantité de temps au téléphone sonnera pour les agents ring_all est réglé sur le réglage On-Hook durée de sonnerie ou le temps le plus court anneau des téléphones qui seront appelés. Nous ne recommandons pas d'utiliser ring_all sur les files d'attente de haute volume d'appels, ou les files d'attente avec de nombreux agents. Procédé ring_all est destiné à être utilisé avec seulement quelques agents et le volume d'appels à faible dans les groupes.
<BR>

<BR>
<A NAME="inbound_groups-on_hook_ring_time">
<BR>
<B>Temps Ring On-Hook -</B> Cette option n'est utilisée que pour les agents qui sont connectés avec des téléphones qui ont la fonction d'agent-sur-crochet activé. C'est le nombre de secondes que chaque tentative d'appel à l'agent se fera entendre pendant que le système va attendre une seconde et se mettent à sonner les téléphones des agents disponibles à nouveau. Ce champ peut être modifié si les téléphones des agents sont fixés à un moment anneau inférieur qui peut être nécessaire pour empêcher les appels d'être envoyé un message vocal téléphones. Par défaut est 15.

<BR>
<A NAME="inbound_groups-on_hook_cid">
<BR>
<B>On-Hook CID -</B> Cette option n'est utilisée que pour les agents qui sont connectés avec des téléphones qui ont la fonction d'agent-sur-crochet activé. Il s'agit de la identification de l'appelant qui s'affichera sur leurs téléphones agent lorsque les appels sonnent. GENERIC est un générique RINGAGENT00000000001 type de notification. Endogroupe montrera que l'en-groupe de l'appel est venu. CUSTOMER_PHONE n'affiche que le numéro de téléphone du client. CUSTOMER_PHONE_RINGAGENT montrera RINGAGENT_3125551212 avec le RINGAGENT dans le cadre du CID avec le numéro de téléphone du client. CUSTOMER_PHONE_INGROUP montrera les 10 premiers caractères de l'en-groupe suivi par le numéro de téléphone à la clientèle. Default is GENERIC.

<BR>
<A NAME="inbound_groups-queue_priority">
<BR>
<B>Queue Priority -</B> This setting is used to define the order in which the calls from this inbound group should be answered in relation to calls from other inbound groups.

<A NAME="inbound_groups-fronter_display">
<BR>
<B>Affichage de l'enseigne -</B> Ce champ détermine si l agent entrant aurait le nom de fronter - s il en existe un - s affiche dans le champ d état lorsque l appel arrive à l agent.

<BR>
<A NAME="inbound_groups-ingroup_script">
<BR>
<B>Script campagne ->/B> Ce menu vous permet de choisir le script qui apparaîtra sur l'écran des agents pour cette campagne. Mettez NONE pour ne pas montrer de script pour cette campagne.

<BR>
<A NAME="inbound_groups-ignore_list_script_override">
<BR>
<B>Ignorer Override Script Liste-</B> Cette option vous permet d'ignorer l'ID de liste Override Script l'option pour les appels à venir dans ce groupe-En. Mettre ce paramètre à Y ignorera tous les paramètres de la liste ID du script. Par défaut est N.

<BR>
<A NAME="inbound_groups-get_call_launch">
<BR>
<B>Consigne de lancement d'appel -</B> Ce menu vous permet de choisir si vous voulez lancer automatiquement la page de web-form dans une fenêtre séparée, passer automatiquement sur l'onglet de script ou ne rien faire quand un appel est envoyé par un agent dans cette campagne. Si les champs de liste personnalisées sont activées sur votre système, formulaire s'ouvrira sur l'onglet FORMULAIRE lors de la connexion d'un appel à un agent.

<BR>
<A NAME="inbound_groups-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Ces quatre domaines permettra de vous d avoir deux ensembles de Conférence de transfert et les présélections du DTMF. Lorsque l appel ou de la campagne est chargé, l écran de l agent affiche deux boutons sur le cadre de transfert de conférence et auto-remplir la fois pressée nombre à cadran et les champs d envoi DTMF. Si vous souhaitez autoriser les transferts de consultation, un fronter à un proche, ont l agent utiliser la case CONSULTATIF, qui ne fonctionne pas pour les appels de consultation troisième écran de l agent du parti. Pour ceux qui ont juste l agent, cliquez sur le bouton Dial Avec la clientèle. Ensuite, l agent peut simplement laisser-3P-CALL et de passer à leur prochain appel. Si vous souhaitez autoriser les transferts aveugles de clients à un script AGI pour l exploitation forestière ou un IVR, puis placez AXFER dans le domaine de nombre de ligne. Vous pouvez également spécifier une extension personnalisée après la AXFER, par exemple si vous voulez faire un appel à un IVR spécial que vous avez défini à l extension 83900 vous mettriez AXFER83900 dans le domaine de nombre à cadran.

<BR>
<A NAME="inbound_groups-timer_action">
<BR>
<B>Minuterie d-action -</B> Cette fonctionnalité vous permet de déclencher des actions après un certain laps de temps. D1 et D2 options DIAL va lancer un appel à la presets Transfer Conference Nombre et les envoyer à la session d-agent, ce n-est généralement utilisé pour les applications IVR AGI simple validation ou tout simplement pour jouer un message pré-enregistré. WebForm ouvrira l-adresse formulaire web. MESSAGE_ONLY va simplement afficher le message qui est dans le champ ci-dessous. AUCUNE désactive cette fonctionnalité et la valeur par défaut. Ce paramètre remplace les paramètres de campagne. HANGUP raccroche l'appel lorsque la minuterie est déclenchée, CALLMENU enverra l'appel au menu Appel spécifié dans le champ Destination minuterie d'action, EXTENSION enverra l'appel à l'extension qui est spécifié dans le champ Destination minuterie d'action, IN_GROUP enverra l'appel à l'en-groupe spécifié dans le champ Destination minuterie d'action.

<BR>
<A NAME="inbound_groups-timer_action_message">
<BR>
<B>Minuterie d-action Message -</B> Tel est le message qui s-affiche sur l-écran de l-agent au moment de l-action Timer est déclenché.

<BR>
<A NAME="inbound_groups-timer_action_seconds">
<BR>
<B>Minuterie d-action Seconds -</B> Il s-agit de la quantité de temps après l-appel est connecté au client que l-action Timer est déclenchée. Par défaut est -1, qui est aussi inactive.

<BR>
<A NAME="inbound_groups-timer_action_destination">
<BR>
<B>Destination action minuterie -</B> Ce champ est l'endroit où vous spécifiez le menu Appel, Extension ou en groupe que vous voulez que l'appel envoyé à l'action si l'heure est défini CALLMENU, EXTENSION ou IN_GROUP. Par défaut est vide.

<BR>
<A NAME="inbound_groups-drop_call_seconds">
<BR>
<B>Temps avant abandon de l'appel - </B> Le nombre de secondes d'un appel restera dans la file d'attente avant d'être considéré comme un DROP.

<BR>
<A NAME="inbound_groups-drop_action">
<BR>
<B>Drop Action - </B> Ce menu vous permet de choisir ce qui se passe à un appel quand il a attendu plus longtemps que ce qui est prévu dans le Temps avant abandon de l'appel domaine. Hangup simplement raccrocher l'appel, envoyer le message de l'appel Drop EXTEN que vous avez définies ci-après, à la messagerie vocale vous envoyer l'appel à la boîte vocale que vous avez définis ci-dessous et IN_GROUP envoie l'appel à la Entrant groupe qui est défini ci-dessous. VMAIL_NO_INST enverra l appel à la boîte vocale que vous avez défini ci-dessous et ne jouera pas les instructions après le message vocal.

<BR>
<A NAME="inbound_groups-drop_exten">
<BR>
<B>Drop EXTEN - </B> Si l'action est goutte d'MESSAGE, c'est le cadran plan d'extension que l'appel sera envoyée si elle parvient à Temps avant abandon de l'appel. Pour AGENTDIRECT dans les groupes, si l'utilisateur n'est pas disponible, vous pouvez mettre AGENTEXT dans ce domaine et le système va chercher l'utilisateur personnalisée cinq sur le terrain et envoyer l'appel vers ce numéro dialplan.

<BR>
<A NAME="inbound_groups-voicemail_ext">
<BR>
<B>Boîte vocale -</B> If Drop Action is set to VOICEMAIL, the call DROP would instead be directed to this voicemail box to hear and leave a message. Dans un AGENTDIRECT en groupe, le réglage à AGENTVMAIL sera sélectionnez l'ID utilisateur à utiliser la messagerie vocale.

<BR>
<A NAME="inbound_groups-drop_inbound_group">
<BR>
<B>Drop de transfert Group - </B> Si Drop action est fixée à IN_GROUP, l'appel sera envoyé à ce groupe d'arrivée si elle parvient Temps avant abandon de l'appel.

<BR>
<A NAME="inbound_groups-drop_callmenu">
<BR>
<B>Menu déroulant Appel -</B> Si l'action chute est fixé à CALLMENU, l'appel sera envoyé à ce menu d'appel si elle atteint secondes de chute d'appels.

<BR>
<A NAME="inbound_groups-action_xfer_cid">
<BR>
<B>Transfert d'action CID -</B> Utilisé pour la baisse, bout de la nuit et des actions non-agent sans file d attente. C est le numéro d identification de l appelant que l appel utilise avant qu il ne soit transféré à des extensions, des messages, vocaux ou appels menus. Vous pouvez utiliser CLIENT dans ce domaine à utiliser le numéro de téléphone du client, ou CAMPAGNE à utiliser le numéro d identification de visiteur campagne abord permis. Défaut est CLIENT. Si c est un appel qui va aller à un menu d appel, puis de nouveau à une, nous vous proposons en groupe vous utilisez CUSTOMERCLOSER dans ce domaine, et vous devez définir la méthode de poignée-groupe dans le menu d appel à CLOSER.

<BR>
<A NAME="inbound_groups-call_time_id">
<BR>
<B>Temps d'appel - c'est l'arrangement de temps d'appel à employer pource groupe d'arrivée. Maintenez dans l'esprit que le temps est basésur le temps de serveur. Le défaut est 24hours.

<BR>
<A NAME="inbound_groups-after_hours_action">
<BR>
<B>Après les heures d'ouverture d'action - </B> L'action à effectuer si c'est après les heures de travail tels que définis dans le temps de l'arrivée pour ce groupe. Hangup hangup immédiatement à l'appel, MESSASGE va jouer le fichier dans le message après les heures de Filenam domaine, EXTENSION envoie l'appel à la fois la durée d'extension de la dialplan et de la messagerie vocale vous envoyer l'appel à la boîte vocale figurant dans la boîte vocale après les heures de domaine , IN_GROUP enverra l'appel à l'arrivée du groupe sélectionné dans le groupe après les heures de sélectionner la liste de transfert. La valeur par défaut est MESSAGE. VMAIL_NO_INST enverra l appel à la boîte vocale que vous avez défini ci-dessous et ne jouera pas les instructions après le message vocal.

<BR>
<A NAME="inbound_groups-after_hours_message_filename">
<BR>
<B>Après nom de fichier de message d'heures - le dossier audio localisésur le serveur à jouer si l'action est placée au MESSAGE. Le défautest VM-AU revoir

<BR>
<A NAME="inbound_groups-after_hours_exten">
<BR>
<B>Après prolongation d'heures - la prolongation dialplan pour envoyerl'appel si l'action est placée à la PROLONGATION. Le défaut est8300. Pour AGENTDIRECT dans les groupes, vous pouvez mettre AGENTEXT dans ce domaine et le système va chercher l'utilisateur personnalisée cinq sur le terrain et envoyer l'appel vers ce numéro dialplan.

<BR>
<A NAME="inbound_groups-after_hours_voicemail">
<BR>
<B>Après des heures Boîte vocale - la boîte de voicemail pour envoyerl'appel si l'action est placée à VOICEMAIL. Dans un AGENTDIRECT en groupe, le réglage à AGENTVMAIL sera sélectionnez l'ID utilisateur à utiliser la messagerie vocale.

<BR>
<A NAME="inbound_groups-afterhours_xfer_group">
<BR>
<B>Après les heures d'ouverture de transfert Group - </B> Si l'action Après les heures d'ouverture est fixée à IN_GROUP, l'appel sera envoyé à cette arrivée du groupe si celui-ci entre dans le groupe en dehors de l'appel de temps défini pour le régime de groupe.

<BR>
<A NAME="inbound_groups-after_hours_callmenu">
<BR>
<B>After Hours Appel du menu -</B> Si après action Heures est réglé sur CALLMENU, l'appel sera envoyé à cette Menu Appel si elle pénètre à l'extérieur en groupe du régime du temps d'appel définie pour l'en-groupe.

<BR>
<A NAME="inbound_groups-no_agent_no_queue">
<BR>
<B>Agents n Queueing -</B> Si ce champ est réglé sur Y ou NO_PAUSED alors aucun appel ne sera mis dans la file d-attente pour cet en-groupe, si il n-ya pas d-agents connectés et les appels ira à l-agent Non Non action Queue. L-option NO_PAUSED enverra également pas les appelants en file d-attente s-il ya seulement une pause des agents dans l-en-groupe. Par défaut est n. Dans un AGENTDIRECT en groupe, le réglage à AGENTVMAIL sera sélectionnez l'ID utilisateur à utiliser la messagerie vocale. Vous pouvez aussi mettre AGENTEXT dans ce domaine si elle est définie à l'extension et le système va chercher l'utilisateur personnalisée cinq sur le terrain et envoyer l'appel vers ce numéro dialplan. Si la valeur de N, les appels en file d attente, même s il n y a pas d agents connectés et mis à prendre des appels de ce groupe en.

<BR>
<A NAME="inbound_groups-no_agent_action">
<BR>
<B>Non Non Queue action de l-agent -</B> Si aucun agent Aucune file d-attente est activé, ce champ définit l-endroit où l-appel sera acheminé en l-absence des agents dans l-en-groupe. Par défaut est le message, celui-ci joue les fichiers audio dans le champ Valeur d-action, puis raccroche.

<BR>
<A NAME="inbound_groups-no_agent_action_value">
<BR>
<B>Non Non Queue action de l-agent Valeur -</B> C-est la valeur pour l-action ci-dessus. Par défaut est nbdy-avail-to-take-call|vm-goodbye.

<BR>
<A NAME="inbound_groups-max_calls_method">
<BR>
<B>Max Appelle la méthode -</B> Cette option peut permettre le maximum fonction simultanée des appels pour ce en groupe. S'il est défini à TOTAL, alors le nombre total d'appels étant traités par des agents et en file d'attente dans cet en-groupe ne sera pas autorisé à dépasser le nombre maximum Appels comte de lignes tel que défini ci-dessous. S'il est défini à IN_QUEUE, alors si le nombre d'appels en file d'attente pour les agents ne seront pas autorisés à dépasser le maximum Appels comte, peu importe combien d'appels sont des agents à cet effet dans-groupe. Par défaut est DISABLED.

<BR>
<A NAME="inbound_groups-max_calls_count">
<BR>
<B>Max appelle le comte -</B> Cette option doit être supérieure à 0 si vous souhaitez utiliser le Max Appels fonction Méthode. Par défaut est 0.

<BR>
<A NAME="inbound_groups-max_calls_action">
<BR>
<B>Max Appels d'action -</B> C'est l'action à prendre si le Max Appelle la méthode est activée et que le nombre d'appels dépasse ce qui est prévu ci-dessus dans le Max Appels Comptez réglage. Les appels au-dessus de ce montant sera envoyé à l'action, soit DROP, l'action ou l'action AFTERHOURS NO_AGENT_NO_QUEUE et sera enregistré comme un statut MAXCAL une raison raccrochage MaxCalls. Par défaut est NO_AGENT_NO_QUEUE.

<BR>
<A NAME="inbound_groups-welcome_message_filename">
<BR>
<B>Nom de fichier bienvenu de message - le dossier audio localisé sur leserveur à jouer quand l'appel entre. Si ensemble à ---NONE --- alorsaucun message ne sera joué. Le défaut est ---NONE ---. Ce domaine, comme avec les autres champs audio en groupes, à l-exception du nom de fichier d-alerte agent, peut avoir plusieurs fichiers audio joué seulement si vous mettez un tuyau d-une liste séparée par des fichiers audio dans le domaine.

<BR>
<A NAME="inbound_groups-play_welcome_message">
<BR>
<B>Message de bienvenue Play - </B> Ces réglages choisir le moment de jouer le message de bienvenue défini, TOUJOURS jouera à chaque fois, JAMAIS il ne pourra jamais jouer, IF_WAIT_ONLY seulement jouer le message de bienvenue si l'appel n'est pas immédiatement à un agent , et YES_UNLESS_NODELAY toujours jouer le message de bienvenue NO_DELAY à moins que le paramètre est activé. La valeur par défaut est TOUJOURS.

<BR>
<A NAME="inbound_groups-moh_context">
<BR>
<B>Musique sur le contexte de prise - la musique sur le contexte de prisepour employer quand le client est placé sur la prise. Le défaut estdéfaut.

<BR>
<A NAME="inbound_groups-onhold_prompt_filename">
<BR>
<B>Sur le nom de fichier de message de sollicitation de prise - ledossier audio localisé sur le serveur à jouer à un intervallerégulier quand le client est sur la prise. Le défaut estgeneric_hold. Ce dossier audio DOIT être de 9 secondes ou moins dansla longueur.

<BR>
<A NAME="inbound_groups-prompt_interval">
<BR>
<B>Sur l'intervalle de message de sollicitation de prise - la durée ensecondes d'attendre avant de jouer dessus le message de sollicitationde tenir. Le défaut est 60. Pour désactiver l'invite en attente, réglez l'intervalle de 0.

<BR>
<A NAME="inbound_groups-onhold_prompt_no_block">
<BR>
<B>En attente Demander Non Bloquer -</B> La définition de cette option à Y permettra appels en ligne derrière un appel en attente, où l'invite à jouer est d'aller à un agent si l'on devient disponible pendant la lecture du message. Alors que le message d'attente Nom Prompt joue à un client, ils ne peuvent pas être envoyé à un agent. Par défaut est N.

<BR>
<A NAME="inbound_groups-onhold_prompt_seconds">
<BR>
<B>En attente secondes Prompt -</B> Ce champ doit être réglé sur le nombre de secondes que le nom de fichier en attente d'invite joue pour. Par défaut est 10.

<BR>
<A NAME="inbound_groups-play_place_in_line">
<BR>
<B>Jouer en ligne Place - </B> Cela définit si l'appelant entendra leur place dans la ligne quand ils entrent dans la file d'attente, ainsi que quand ils entendent le announcemend. La valeur par défaut est N.

<BR>
<A NAME="inbound_groups-play_estimate_hold_time">
<BR>
<B>Hold estimée Play Time - </B> Cela définit si l'appelant entend tenir les prévisions de temps avant qu'ils ne soient transférés à un agent. La valeur par défaut est N. Si le client est en attente et entend ce message d-estime détenir le temps, le temps minimum qui sera jouée est de 15 secondes.

<BR>
<A NAME="inbound_groups-calculate_estimated_hold_seconds">
<BR>
<B>Calculer secondes Attente Estimation -</B> Ceci définit le nombre de secondes dans la file d'attente que le client doit attendre avant de le temps de maintien estimée sera calculée et éventuellement joué. Minimum est de 3 secondes, même s'il est réglé inférieur à 3. Par défaut est 0.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_filename">
<BR>
<B>Estimation Tenez Nom minimum de temps -</B> Si le temps de maintien estimée est actif et il est calculé pour être égal ou inférieur au minimum de 15 secondes, alors ce fichier sera joué invite au lieu de l'annonce par défaut. Par défaut est vide pour inactive.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_no_block">
<BR>
<B>Estimation minimale Hold Time Demander Non Bloquer -</B> Si Hold Time estimée est actif et l'heure estimée de maintien Nom minimum champ ci-dessus est remplie, alors cette option pour autoriser les appels en file derrière un appel où l'invite à jouer est d'aller à un agent si l'on devient disponible pendant la lecture du message . Alors que l'invite est de jouer pour un client, ils ne peuvent pas être envoyé à un agent. Par défaut est N.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_seconds">
<BR>
<B>Estimation secondes Hold Time minimum Prompt -</B> Ce champ doit être réglé sur le nombre de secondes que le nom de fichier en attente estimée Temps minimum invite joue pour. Par défaut est 10.

<BR>
<A NAME="inbound_groups-wait_time_option">
<BR>
<B>Attendre Option Temps -</B> Cela vous permet de donner aux clients des options pour sortir de la file d'attente si leur temps d'attente est plus le nombre de secondes indiquées ci-dessous. Par défaut est NONE. Si l'une des options PRESS_ est sélectionné, il jouera le nom de fichier de presse défini ci-dessous et donner au client l'option d'appuyer sur 1 sur leur téléphone à quitter la file d'attente et d'exécuter l'option sélectionnée. L'option PRESS_STAY enverra le client vers la file d'attente sans pour autant perdre leur place dans la ligne.

<BR>
<A NAME="inbound_groups-wait_time_second_option">
<BR>
<B>Attendre Option Second Time -</B> Même que le champ Option temps d'attente ci-dessus, sauf celle-ci va vérifier pour le client en appuyant sur la touche 2. Par défaut est NONE. Si aucune option d'attente première fois est sélectionné, cette option ne sera pas offert.

<BR>
<A NAME="inbound_groups-wait_time_third_option">
<BR>
<B>Attendre Option troisième fois -</B> Même que le champ Option temps d'attente ci-dessus, sauf celle-ci va vérifier pour le client en appuyant sur la touche 3. Par défaut est NONE. Si aucune option d'attente Deuxième temps est sélectionné, cette option ne sera pas offert.

<BR>
<A NAME="inbound_groups-wait_time_option_seconds">
<BR>
<B>Attendez quelques secondes Option Temps -</B> Si l'option temps d'attente est fixé à quelque chose, mais AUCUN, c'est le nombre de secondes que le client a été en attente dans la file d'attente qui va déclencher les options de temps d'attente. Par défaut est 120 secondes.

<BR>
<A NAME="inbound_groups-wait_time_option_exten">
<BR>
<B>Attendre Extension Option Temps -</B> Si l'option temps d'attente est fixé à PRESS_EXTEN, c'est l'extension que le plan de numérotation téléphonique sera envoyé à si le client appuie sur la touche option lorsque présentés avec l'option. Pour AGENTDIRECT dans les groupes, vous pouvez mettre AGENTEXT dans ce domaine et le système va chercher l'utilisateur personnalisée cinq sur le terrain et envoyer l'appel vers ce numéro dialplan.

<BR>
<A NAME="inbound_groups-wait_time_option_callmenu">
<BR>
<B>Attendre Callmenu Option Temps -</B> Si l'option temps d'attente est fixé à PRESS_CALLMENU, c'est le menu Appel que l'appel sera envoyé à si le client appuie sur la touche option lorsque présentés avec l'option.

<BR>
<A NAME="inbound_groups-wait_time_option_voicemail">
<BR>
<B>Attendre la messagerie vocale Option Temps -</B> Si l'option temps d'attente est fixé à PRESS_VMAIL, c'est la boîte vocale que l'appel sera envoyé à si le client appuie sur la touche option lorsque présentés avec l'option. Dans un AGENTDIRECT en groupe, le réglage à AGENTVMAIL sera sélectionnez l'ID utilisateur à utiliser la messagerie vocale.

<BR>
<A NAME="inbound_groups-wait_time_option_xfer_group">
<BR>
<B>Attendre transfert Option Temps En-Groupe -</B> Si l'option temps d'attente est fixé à PRESS_INGROUP, c'est le groupe entrant que l'appel sera envoyé à si le client appuie sur la touche option lorsque présentés avec l'option.

<BR>
<A NAME="inbound_groups-wait_time_option_press_filename">
<BR>
<B>Attendre Nom de presse Heure Option -</B> Si l'option temps d'attente est fixé à l'une des options PRESS_, c'est le nom de fichier rapide qui se joue, si le temps d'attente des clients dépasse les secondes des temps d'attente Option de donner au client l'option d'appuyer sur 1, 2 ou 3 sur leur téléphone afin de fonctionner les options choisies temps d'attente de presse. Il est très important que vous inclure des options dans le fichier audio pour l'ensemble de vos options sélectionnées les temps d'attente, et que la longueur du fichier audio en quelques secondes est bien définie dans le domaine du fichier secondes ci-dessous ou il y aura des problèmes. Par défaut est à-être-appelé-back.

<BR>
<A NAME="inbound_groups-wait_time_option_no_block">
<BR>
<B>Attendre Appuyez sur Option No Time Bloquer -</B> La définition de cette option à Y permettra appels en ligne derrière un appel où l'attente Option Temps de presse Nom invite joue pour aller à un agent si l'on devient disponible pendant la lecture du message. Alors que le temps d'attente Option Nom message Appuyez sur la lecture à un client, ils ne peuvent pas être envoyé à un agent. Par défaut est N.

<BR>
<A NAME="inbound_groups-wait_time_option_prompt_seconds">
<BR>
<B>Les temps d'attente secondes Appuyez sur Option Nom de fichier -</B> Ce champ doit être réglé sur le nombre de secondes que le temps d'attente Appuyez sur Option Nom joue pour. Par défaut est 10.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_filename">
<BR>
<B>Attendre Option Time After Nom de presse -</B> Si l'option temps d'attente est fixé à l'une des options PRESS_, c'est le nom de fichier rapide qui se joue après que le client a appuyé sur 1, 2 ou 3.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_list_id">
<BR>
<B>Attendre Option Temps liste de rappel ID -</B> Si l'option temps d'attente est fixé à PRESS_CID_CALLBACK, c'est l'ID de la liste de l'appel est ajouté à un nouveau chef de file si le client appuie sur la touche option lorsque présentés avec l'option.

<BR>
<A NAME="inbound_groups-wait_hold_option_priority">
<BR>
<B>Attendre Tenez priorité Option -</B> Si les deux options de temps estimé le protéger et attendre les options de temps sont actifs, ce paramètre sera de définir si un, l'autre ou les deux de ces fonctions sont actives. Par exemple, si l'option de maintien Durée estimée est fixé à 360, l'option temps d'attente est fixé à 120 et le client a été en attente pendant 120 secondes et il ya encore 400 secondes estimé la durée de rétention, puis ils sont tous deux actifs en même temps et ce paramètre sera vérifié pour voir quelles options seront offertes. Par défaut, c'est d'attendre que.

<BR>
<A NAME="inbound_groups-hold_time_option">
<BR>
<B>Estimation Option Temps de maintien - </B> Cela vous permet de spécifier le routage de l'appel, si les prévisions de tenir le temps est sur le montant de la seconde précisés ci-dessous. La valeur par défaut est NON. Si l'une des options PRESS_ est sélectionné, il jouera le nom de fichier de presse défini ci-dessous et donner au client l'option d'appuyer sur 1 sur leur téléphone à quitter la file d'attente et d'exécuter l'option sélectionnée.

<BR>
<A NAME="inbound_groups-hold_time_second_option">
<BR>
<B>Tenez Option Second Time -</B> Même que le champ Option temps de maintien ci-dessus, sauf celle-ci va vérifier pour le client en appuyant sur la touche 2. Par défaut est NONE. Si aucune option de maintien première fois est sélectionné, cette option ne sera pas offert.

<BR>
<A NAME="inbound_groups-hold_time_third_option">
<BR>
<B>Tenez Option troisième fois -</B> Même que le champ Option temps de maintien ci-dessus, sauf celle-ci va vérifier pour le client en appuyant sur la touche 3. Par défaut est NONE. Si aucune option Mise en attente Deuxième temps est sélectionné, cette option ne sera pas offert.

<BR>
<A NAME="inbound_groups-hold_time_option_seconds">
<BR>
<B>Hold Time Option secondes - </B> Si Hold Time Option est fixé à rien, mais AUCUNE, c'est le nombre de secondes de l'estimation du temps que vous maintenez le déclencheur tenir option. La valeur par défaut est 360 secondes.

<BR>
<A NAME="inbound_groups-hold_time_option_minimum">
<BR>
<B>Tenez minimum Option Temps -</B> Si l'option Hold Time est activé, c'est le nombre minimum de secondes de l'appel doit être en attente avant d'être présenté avec l'option temps de maintien. L'option temps de maintien sera immédiatement présenté à ce moment si le temps de maintien estimée est supérieure à la touche Option secondes la valeur temps. Par défaut est 0 secondes.

<BR>
<A NAME="inbound_groups-hold_time_option_exten">
<BR>
<B>Hold Time Option Extension - </B> Si Hold Time Option est fixé à l'extension, c'est l'extension dialplan que l'appel sera envoyé à tenir si l'estimation dépasse le temps Hold Time Option Seconds. Pour AGENTDIRECT dans les groupes, vous pouvez mettre AGENTEXT dans ce domaine et le système va chercher l'utilisateur personnalisée cinq sur le terrain et envoyer l'appel vers ce numéro dialplan.

<BR>
<A NAME="inbound_groups-hold_time_option_callmenu">
<BR>
<B>Tenez Callmenu Option Temps -</B> Si l'option Hold Time est réglé sur CALL_MENU, c'est le menu Appel que l'appel sera envoyé à si le temps de maintien estimée dépasse les secondes touche Option Temps.

<BR>
<A NAME="inbound_groups-hold_time_option_voicemail">
<BR>
<B>Hold Time Option Boîte vocale - </B> Si Hold Time Option est fixé à la boîte vocale, il s'agit de la boîte vocale que l'appel sera envoyé à tenir si l'estimation dépasse le temps Hold Time Option Seconds. Dans un AGENTDIRECT en groupe, le réglage à AGENTVMAIL sera sélectionnez l'ID utilisateur à utiliser la messagerie vocale.

<BR>
<A NAME="inbound_groups-hold_time_option_xfer_group">
<BR>
<B>Hold Time Option de transfert In-Group - </B> Si Hold Time Option est fixé à IN_GROUP, c'est l'arrivée du groupe que l'appel sera envoyé à tenir si l'estimation dépasse le temps Hold Time Option Seconds.

<BR>
<A NAME="inbound_groups-hold_time_option_press_filename">
<BR>
<B>Tenez Nom de presse Heure Option -</B> Si l'option Hold Time est réglé sur l'une des options PRESS_, c'est le nom de fichier rapide qui se joue, si le temps de maintien estimée dépasse les secondes touche Option Temps de donner au client l'option d'appuyer sur 1 sur leur téléphone afin de fonctionner le temps de maintien sélectionné Appuyez sur Option. Il est très important que ce fichier audio est de 10 secondes ou moins, ou il y aura des problèmes. Par défaut est à-être-appelé-back.

<BR>
<A NAME="inbound_groups-hold_time_option_no_block">
<BR>
<B>Maintenez Appuyez sur Option No Time Bloquer -</B> La définition de cette option à Y permettra appels en ligne derrière un appel où la touche Option Temps de presse Nom invite joue pour aller à un agent si l'on devient disponible pendant la lecture du message. Alors que le temps de maintien Option Nom message Appuyez sur la lecture à un client, ils ne peuvent pas être envoyé à un agent. Par défaut est N.

<BR>
<A NAME="inbound_groups-hold_time_option_prompt_seconds">
<BR>
<B>Hold Time Seconds Appuyez sur Option Nom de fichier -</B> Ce champ doit être réglé sur le nombre de secondes que le temps de maintien de presse Option Nom joue pour. Par défaut est 10.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_filename">
<BR>
<B>Tenez Option Time After Nom de presse -</B> Si l'option Hold Time est réglé sur l'une des options PRESS_ ou CALLERID_CALLBACK, c'est le nom de fichier rapide qui se joue après que le client a appuyé sur 1 ou l'appel a été ajouté à la liste de rappel.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_list_id">
<BR>
<B>Hold Time Option Rappel Liste ID - </B> Si Hold Time Option est fixé à CALLERID_CALLBACK, il s'agit de la liste de ID de l'appel est ajouté à un nouveau chef de file, si les prévisions de tenir le temps dépasse le Hold Time Option Seconds.

<BR>
<A NAME="inbound_groups-agent_alert_exten">
<BR>
<B>Agent d-alerte Filename -</B> Le fichier audio à lire à un agent d-annoncer qu-un appel arrive à l-agent. Pour ne pas utiliser cette fonction pour définir cette valeur par défaut est X. Ding.

<BR>
<A NAME="inbound_groups-agent_alert_delay">
<BR>
<B>L'alerte d'agent Retardent - la durée en millisecondesd'attendre avant d'envoyer l'appel à l'agent après avoir jouédessus la prolongation alerte d'agent. Le défaut est 1000.

<BR>
<A NAME="inbound_groups-default_xfer_group">
<BR>
<B>Groupe de transfert de défaut - ce champ est le Dans-Groupe dedéfaut qui sera automatiquement choisi quand l'agent va à l'armaturede transférer-conférence dans leur interface d'agent.

<BR>
<A NAME="inbound_groups-ingroup_recording_override">
<BR>
<B>In-Group Recording Override -</B> Ce champ permet d primordial de la création de l enregistrement de l appel de la campagne. Ce réglage peut être modifié par le réglage d enregistrement prioritaire de l utilisateur. HANDICAPÉS ne sera pas remplacer le paramètre d enregistrement de la campagne. Ne sera jamais désactiver l enregistrement sur le client. ONDEMAND est la valeur par défaut et permet à l agent pour démarrer et arrêter l enregistrement en tant que de besoin. ALLCALLS va commencer l enregistrement sur le client chaque fois qu un appel est envoyé à un agent. ALLFORCE va commencer l enregistrement sur le client chaque fois qu un appel est envoyé à un agent donnant aucune option pour arrêter l enregistrement de l agent.

<BR>
<A NAME="inbound_groups-ingroup_rec_filename">
<BR>
<B>In-Group Recording Filename -</B> Ce champ remplace le régime de Dénomination de fichiers d'enregistrement de la campagne si elle est réglée sur NONE. Les variables autorisées sont CAMPAIGN INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH AGENT VENDORLEADCODE LEADID CALLID. La valeur par défaut est FULLDATE_AGENT et devrait ressembler à ceci 20051020-103108_6666. Un autre exemple est CAMPAIGN_TINYDATE_CUSTPHONE qui ressemblerait à ceci TESTCAMP_51020103108_3125551212. Te résultant nom de fichier doit être inférieure à 90 caractères. Default is NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="inbound_groups-qc_enabled">
	<BR>
	<B>QC Activé - </B> Mise en ce domaine permet à Y pour l'agent de contrôle de la qualité caractéristiques au travail. La valeur par défaut est N.

	<BR>
	<A NAME="inbound_groups-qc_statuses">
	<BR>
	<B>Statuts QC - </B> Cette zone est l'endroit où vous choisissez ce qui entraîne des statuts doit être dépassé par le système de QC. Placez un contrôle à côté de l'état que vous voulez examiner QC. 

	<BR>
	<A NAME="inbound_groups-qc_shift_id">
	<BR>
	<B>QC Shift - </B> Il s'agit du changement de calendrier servant à tirer d'un QC inbound_group. Les jours de la semaine sont ignorées pour ces fonctions.

	<BR>
	<A NAME="inbound_groups-qc_get_record_launch">
	<BR>
	<B>Record QC Get lancement </B> Cela permet à l'une des actions suivantes peut être déclenché sur un agent QC recevoir un nouveau record.

	<BR>
	<A NAME="inbound_groups-qc_show_recording">
	<BR>
	<B>QC Voir Recording - </B> qui permet un enregistrement qui mai être liée à la QC record à afficher dans le QC agent écran.

	<BR>
	<A NAME="inbound_groups-qc_web_form_address">
	<BR>
	<B>QC WebForm Adresse - </B> Il s'agit de l'adresse du site web d'un agent QC pouvez aller à en cliquant sur le lien dans la WebForm QC écran.

	<BR>
	<A NAME="inbound_groups-qc_script">
	<BR>
	<B>Script QC - </B> C'est le script qui peut être utilisé par les agents QC SCRIPT dans l'onglet de la QC écran.
	<?php
	}
?>

<BR>
<A NAME="inbound_groups-hold_recall_xfer_group">
<BR>
<B>Hold Recall transfert In-Group - </B> Si un client demande à ce groupe en plus d'une fois et cela n'est pas mis à AUCUNE, puis l'appel sera automatiquement envoyé à l'En-groupe sélectionné dans ce domaine . La valeur par défaut est NON.

<BR>
<A NAME="inbound_groups-no_delay_call_route">
<BR>
<B>Appel Route n ° Delay - </B> Mettre ce paramètre à Y va effacer tous les temps d'attente et audio et de tenter d'envoyer l'appel droit à un agent. T-elle pas sur le message d'accueil ou en attente rapide des paramètres. La valeur par défaut est N.

<BR>
<A NAME="inbound_groups-answer_sec_pct_rt_stat_one">
<BR>
<B>Stats Pourcentage des appels dans les X secondes - </B> Ce domaine vous permet de définir le nombre de secondes que le tenir en temps réel des statistiques d'affichage à utiliser pour calculer le pourcentage d'appels qui ont répondu ont répondu dans X le nombre de secondes en attente.

<BR>
<A NAME="inbound_groups-start_call_url">
<BR>
<B>Début de l-appel Web -</B> This web URL address is not seen by the agent, but it is called every time a call is sent to an agent if it is populated. Uses the same variables as the web form fields and scripts. Default is blank.

<BR>
<A NAME="inbound_groups-dispo_call_url">
<BR>
<B>Dispo Call Web -</B> This web URL address is not seen by the agent, but it is called every time a call is dispositioned by an agent if it is populated. Uses the same variables as the web form fields and scripts. dispo and talk_time are the variables you can use to retrieve the agent-defined disposition for the call and the actual talk time en secondes of the call. Default is blank.

<BR>
<A NAME="inbound_groups-add_lead_url">
<BR>
<B>Ajouter plomb URL -</B> Cette adresse URL Web n'est pas vu par l'agent, mais il est appelé chaque fois qu'un chef de file est ajouté au système par le biais du processus de rapprochement. Par défaut est vide. Vous devez commencer cette URL avec VAR si vous voulez utiliser des variables, et bien sûr - A - et - B - autour de la variable réelle dans l'URL où vous souhaitez l'utiliser. Voici la liste des variables qui sont disponibles pour cette fonction. lead_id, vendor_lead_code, list_id, phone_number, phone_code, did_id, did_extension, did_pattern, did_description, uniqueid

<BR>
<A NAME="inbound_groups-na_call_url">
<BR>
<B>Aucune URL Appel Agent -</B> Cette adresse URL Web n'est pas vu par l'agent, mais si elle est peuplée, il est appelé à chaque fois un appel qui n'est pas gérée par un agent est suspendu ou transféré. Utilise les mêmes variables que les champs du formulaire Web et les scripts. dispo peut être utilisée pour récupérer le système défini par la disposition de l'appel. Cette URL ne peut pas être un chemin relatif. Par défaut est vide.

<BR>
<A NAME="inbound_groups-default_group_alias">
<BR>
<B>Groupe par défaut Alias - </B> Si vous avez permis à Groupe Alias pour la campagne que l'agent est entré dans ce groupe est l'alias qui est sélectionnée par défaut sur la première d'un appel provenant de l'arrivée de ce groupe quand l'agent choisit de utiliser un alias pour un groupe émetteur manuel appel. La valeur par défaut est None ou vide.

<BR>
<A NAME="inbound_groups-dial_ingroup_cid">
<BR>
<B>Composez In-groupe CID -</B> Si la campagne de l'agent permet de Manuel Dans la composition de groupe, ce numéro d'identification de l'appelant sera envoyée comme le CID sortant de l'appel téléphonique s'il est rempli, remplaçant les paramètres de la campagne et la liste Paramètre de remplacement CID. Default est vide.

<BR>
<A NAME="inbound_groups-extension_appended_cidname">
<BR>
<B>Extension Ajoutez CID -</B> Si elle est activée, les appels à venir à partir de cet en-groupe disposera d'un espace et l'extension de téléphone de l'agent ajouté à la fin du nom de CallerID de l'appel avant qu'il ne soit envoyé à l'agent. Par défaut est N pour les handicapés.

<BR>
<A NAME="inbound_groups-uniqueid_status_display">
<BR>
<B>Affichage de l'état uniqueid -</B> Si elle est activée, lorsqu un agent reçoit un appel à travers ce en groupe, ils verront la uniqueid de l appel ajouté à la ligne d état dans leur interface de l agent. L option PREFIX va ajouter le préfixe, définis ci-dessous, pour le début de la uniqueid sur l afficheur. Est désactivé par défaut. Si il y avait déjà un UniqueId défini sur un appel entrant ce en groupe, puis le uniqueid original sera affiché. Si l option de conservation est utilisé et l appel est envoyé à un deuxième agent, le uniqueid et le préfixe affiché au premier agent sera également affiché au second agent.

<BR>
<A NAME="inbound_groups-uniqueid_status_prefix">
<BR>
<B>Préfixe Statut uniqueid -</B> Si l'option PREFIX est sélectionné ci-dessus, alors c'est la valeur de ce préfixe. Par défaut est vide.




<BR><BR><BR><BR>

<B><FONT SIZE=3>INBOUND_DIDS TABLE</FONT></B><BR><BR>
<BR>
<A NAME="inbound_dids-did_pattern">
<BR>
<B>DID Extension - </B> C'est le nombre, d'extension ou de DID qui déclenchent cette entrée et que vous la route à l'intérieur du système en utilisant cette fonction. Il est réservé par défaut DID que vous pouvez utiliser ce qui est juste le mot-par défaut, sans les tirets, qui allos d'envoyer tout appel qui ne correspond pas à tout les autres modèles de la valeur par défaut de DID.

<BR>
<A NAME="inbound_dids-did_description">
<BR>
<B>Description DID - </B> Il s'agit de la description de l'entrée de routage DID.

<BR>
<A NAME="inbound_dids-did_active">
<BR>
<B>DID Actif- </B> C'est le domaine où vous définissez la DID à l'entrée active ou non. La valeur par défaut est Y.

<BR>
<A NAME="inbound_dids-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour ce n'a, ce qui permet d'administration de visualisation de ce ne restreint par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce fait.

<BR>
<A NAME="inbound_dids-did_route">
<BR>
<B>DID Route -</B> Ce type de parcours que vous définissez la DID à utiliser. EXTEN enverra des appels à l extension entré ci-après, la messagerie vocale envoyer des appels directement à la boîte vocale indiquée ci-dessous, AGENT enverra les appels à un agent si ils sont connectés, téléphone envoie l appel à une entrée de téléphones sélectionnés ci-dessous, IN_GROUP enverra demande directement au groupe entrant spécifié. Défaut est EXTEN. CALLMENU enverra l appel au menu d appel défini. VMAIL_NO_INST enverra l appel à la boîte vocale que vous avez défini ci-dessous et ne jouera pas les instructions après le message vocal.

<BR>
<A NAME="inbound_dids-record_call">
<BR>
<B>Enregistrement de l'appel -</B> Cette option vous permet de définir les appels à venir dans ce fait pour être enregistrées. Y va enregistrer tout l'appel, Y_QUEUESTOP enregistrera l'appel jusqu'à ce que l'appel est hungup ou pénètre dans une file d'attente en groupe N ne sera pas enregistrer l'appel. Par défaut est N.

<BR>
<A NAME="inbound_dids-extension">
<BR>
<B>Extension - </B> Si EXTEN est sélectionné en tant que DID route, alors c'est le dialplan extension qui appelle sera envoyée. La valeur par défaut est 9998811112, pas de service.

<BR>
<A NAME="inbound_dids-exten_context">
<BR>
<B>Extension Contexte - </B> Si EXTEN est sélectionné en tant que DID route, alors c'est le contexte qui dialplan demande sera envoyée. Par défaut est la valeur par défaut.

<BR>
<A NAME="inbound_dids-voicemail_ext">
<BR>
<B>Boite vocale - </B> Si messagerie vocale est sélectionné en tant que DID Route, c'est la boîte vocale qui demande sera envoyée. La valeur par défaut est vide.

<BR>
<A NAME="inbound_dids-phone">
<BR>
<B>Téléphone Extension - </B> si le téléphone est sélectionné comme DID Route, cette extension est le téléphone que les appels seront envoyées.

<BR>
<A NAME="inbound_dids-server_ip">
<BR>
<B>Téléphone IP du serveur - </B> si le téléphone est sélectionné comme DID route, alors c'est l'IP du serveur pour le téléphone d'extension que les appels seront envoyés à.

<BR>
<A NAME="inbound_dids-menu_id">
<BR>
<B>Appel du menu -</B> Si CALLMENU est sélectionné comme la Route de DID, alors c-est le menu d-appel que les appels seront envoyés aux.

<BR>
<A NAME="inbound_dids-user">
<BR>
<B>Utilisateur Agent -</B> Si l agent est choisi comme la Route DID, alors c est l agent qui demande seront envoyés à.

<BR>
<A NAME="inbound_dids-user_unavailable_action">
<BR>
<B>Utilisateur Unavailable Action - </B> si l'agent est sélectionné en tant que DID route, et l'utilisateur n'est pas connecté ou disponible, c'est la route que les appels se.

<BR>
<A NAME="inbound_dids-user_route_settings_ingroup">
<BR>
<B>Utilisateur Settings En Route-Group - </B> si l'agent est sélectionné en tant que DID route, alors c'est le groupe In-qui sera utilisé pour la file d'attente de paramètres que l'appelant est en attente d'être envoyé à l'agent. La valeur par défaut est AGENTDIRECT.

<BR>
<A NAME="inbound_dids-group_id">
<BR>
<B>In-Groupe ID - </B> Si IN_GROUP est sélectionné en tant que DID route, alors c'est le groupe qui en demande sera envoyée à.

<BR>
<A NAME="inbound_dids-call_handle_method">
<BR>
<B>En appel de groupe Méthode de poignée -</B> Si IN_GROUP est sélectionné comme la Route DID, alors c est la méthode de traitement des appels utilisé pour ces appels. CID va ajouter un nouveau record de plomb à chaque appel en utilisant le CallerID que le numéro de téléphone, CIDLOOKUP tentera de rechercher le numéro de téléphone par le CallerID dans l ensemble du système, CIDLOOKUPRL tentera de rechercher le numéro de téléphone par le CallerID dans une seule liste spécifiée , CIDLOOKUPRC tentera de rechercher le numéro de téléphone par le CallerID dans toutes les listes qui appartiennent à la campagne spécifiée, CLOSER est spécifié pour les appels Closer, ANI ajouter un nouveau record de plomb à chaque appel en utilisant l ANI que le numéro de téléphone, ANILOOKUP va tenter de rechercher le numéro de téléphone par l ANI dans l ensemble du système, ANILOOKUPRL tentera de rechercher le numéro de téléphone par l ANI dans une seule liste spécifiée, XDIGITID demandera à l appelant pour un code X de chiffres avant l appel sera mis dans le file d attente, VIDPROMPT demandera à l appelant de leur numéro d identification et créera un nouveau record de plomb avec le CallerID que le numéro de téléphone et l ID que l ID du vendeur, VIDPROMPTLOOKUP tentera de rechercher l ID dans l ensemble du système, VIDPROMPTLOOKUPRL tentera de rechercher l ID de fournisseur par l ID dans une seule liste spécifiée, VIDPROMPTLOOKUPRC tentera de rechercher l ID de fournisseur par l ID dans toutes les listes qui appartiennent à la campagne spécifiée. Défaut est CID. Si une méthode de CIDLOOKUP est utilisé avec ALT, il va rechercher le domaine de alt_phone pour le numéro de téléphone si aucune correspondance n est trouvée pour le numéro de téléphone principal. Si une méthode de CIDLOOKUP est utilisé avec ADDR3, il va rechercher le domaine address3 pour le numéro de téléphone si aucune correspondance n est trouvée pour le numéro de téléphone principal et éventuellement le domaine de alt_phone.

<BR>
<A NAME="inbound_dids-agent_search_method">
<BR>
<B>In-Groupe Agent Méthode de recherche - </B> Si IN_GROUP est sélectionné en tant que DID route, alors c'est l'agent de recherche à la méthode de l'arrivée du groupe, LO est à équilibrage de charge-débordement et d'essayer d'envoyer l'appel à un agent sur le serveur local avant d'essayer de l'envoyer à un agent sur un autre serveur, LB est à équilibrage de charge et de tenter d'envoyer l'appel à l'agent, quel que soit elles sont sur le serveur, le serveur SO est seulement et que d'essayer d'envoyer des appels vers les agents sur le serveur que l'appel est venu dans le. La valeur par défaut est LB.

<BR>
<A NAME="inbound_dids-list_id">
<BR>
<B>Dans la liste Groupe ID - </B> Si IN_GROUP est sélectionné en tant que DID Route, c'est la liste qui mène mai ID être recherchées à travers et que le conduit sera inséré dans le cas échéant.

<BR>
<A NAME="inbound_dids-campaign_id">
<BR>
<B>En-groupe ID de la campagne - </B> Si IN_GROUP est sélectionné en tant que DID route, alors c'est le ID de la campagne que mène mai être recherchée dans le cas de la méthode est de faire appel CIDLOOKUPRC.

<BR>
<A NAME="inbound_dids-phone_code">
<BR>
<B>En-groupe Téléphone Code - </B> Si IN_GROUP est sélectionné en tant que DID route, alors ce téléphone est le code utilisé si un nouveau chef de file est créé.

<BR>
<A NAME="inbound_dids-filter_clean_cid_number">
<BR>
<B>Nettoyez Nombre CID -</B> Ce champ vous permet de spécifier un certain nombre de chiffres à limiter le nombre d'appel entrant ID en mettant un R en face de le nombre de chiffres, par exemple pour limiter les 10 chiffres de droite, vous devez entrer dans R10. Vous pouvez également utiliser cette fonctionnalité pour supprimer seulement un chiffre de premier plan ou de chiffres en mettant un L devant les chiffres spécifiques que vous souhaitez supprimer, par exemple pour enlever un 1 comme le premier chiffre que vous entrez en L1. Par défaut est vide. Si plus d'une règle est prévue assurez-vous de les séparer par un espace et du R sera exécuté avant la L.

<BR>
<A NAME="inbound_dids-filter_inbound_number">
<BR>
<B>Filtre Nombre entrants -</B> Cette option, si activée, vous permet de filtrer les appels à venir dans ce DID et les envoyer à une autre action si elles correspondent à un numéro de téléphone qui se trouve dans le groupe téléphone filtre ou une réponse URL si vous avez configuré un. Défaut est Disabled. GROUPE va chercher dans un groupe Téléphone filtre. URL enverra une URL et correspondra si un 1 est renvoyé. DNC_INTERNAL va rechercher par la liste interne DNC. DNC_CAMPAIGN recherchera par un liste spécifique de DNC de campagne.

<BR>
<A NAME="inbound_dids-filter_phone_group_id">
<BR>
<B>Filtre ID Groupe Téléphone -</B> Si le champ Numéro de filtre entrant est réglé sur GROUPE, alors c'est l'ID du groupe Téléphone filtre qui aura ses numéros de recherche à la recherche d'un match à l'identification de l'appelant le numéro de l'appel entrant.

<BR>
<A NAME="inbound_dids-filter_url">
<BR>
<B>URL Filter -</B> Si le champ Numéro de filtre entrant est réglé à l'URL, alors c'est l'adresse Web d'un script qui va chercher un système distant et retourner un 1 pour un match et un 0 pour aucun match. Seuls les deux variables sont disponibles à l'adresse si vous utilisez le préfixe VAR comme avec des adresses formulaire en ligne dans les campagnes, - A - phone_number - B - et - A - did_pattern - B - peut être utilisé dans l'URL pour indiquer l'ID de l'appelant de l'appelant et l'a fait que le client a appelé dans le.

<BR>
<A NAME="inbound_dids-filter_url_did_redirect">
<BR>
<B>Filtre URL de redirection DID -</B> Si le champ Numéro entrant filtre est fixé à l URL, alors ce paramètre permet à l intervention d URL permet de spécifier un système DID pour rediriger l appel vers l INSEAD de l utilisation de l action par défaut. Si un 0 est retourné alors l action par défaut est utilisé. Si un autre que 0 tout est ensuite retourné l appel sera redirigé vers la valeur de réponse d URL résultant.

<BR>
<A NAME="inbound_dids-filter_dnc_campaign">
<BR>
<B>Filtrer campagne DNC -</B> Si le champ Numéro entrant filtre est réglé sur DNC_CAMPAIGN alors c est l ID de campagne spécifique que la liste campagne de DNC appartient à.



<BR>
<A NAME="inbound_dids-filter_action">
<BR>
<B>Action de filtrage -</B> Si le filtre est activé Nombre entrants et une correspondance est trouvée, alors c'est l'action qui doit être prise. C'est la même chose que la route que vous sélectionnez pour un SDA, et les réglages ci-dessous fonctionnent comme ils le font pour une norme de routage.

<BR>
<A NAME="inbound_dids-custom_one">
<BR>
<B>La coutume voulait Champs - </ B> Ces cinq champs peuvent être utilisés à des fins diverses, la plupart ayant trait à la programmation personnalisée et des rapports.




<BR>
<A NAME="did_ra_extensions">
<BR>
<B>DID télécommande remplace extension de l'agent -</B><BR>Cette section vous permet d'activer DID d'avoir des remplacements de vulgarisation pour l'agent à distance acheminés par le biais de demande-groupes. Le début d'utilisateur doit être valide démarrage à distance de l'agent utilisateur ou si vous voulez l'extension Remplacer l'entrée de travailler pour tous les appels, vous pouvez utiliser --- Tous --- dans le champ Début de l'utilisateur. S'il ya des entrées multiples pour le même DID et l'utilisateur sur Démarrer, puis les entrées actives sera utilisé dans un procédé à la ronde.




<BR><BR><BR><BR>

<B><FONT SIZE=3>CALL MENU TABLE</FONT></B><BR><BR>
<A NAME="call_menu-menu_id">
<BR>
<B>Menu ID -</B> Ceci est l-ID pour cette étape du menu d-appel. Ce sera également visible selon le contexte qui est utilisée dans le plan de numérotation pour cet appel menu. Voici une liste de phrases réservés qui ne peuvent pas être utilisés en tant que menu ID: vicidial, vicidial-auto, general, globals, default, trunkinbound, loopback-no-log, monitor_exit, monitor.

<BR>
<A NAME="call_menu-menu_name">
<BR>
<B>Nom du menu -</B> Ce champ est le nom descriptif pour le menu d-appel.

<BR>
<A NAME="call_menu-menu_prompt">
<BR>
<B>Menu rapide -</B> Ce champ contient le nom du fichier audio de l-invite à jouer au début de ce menu. Vous pouvez entrer propmts multiples dans ce domaine et invite les autres champs en les séparant par un caractère pipe. Vous pouvez ajouter NOINT directement en face du nom d'un fichier audio pour en faire de sorte que la lecture ne peut pas être interrompue par une pression sur la touche par l'appelant, le NOINT ne devrait pas être une partie du nom de fichier, c'est un drapeau spécial pour le système. Vous pouvez également utiliser spécial. Scripts agi dans ce domaine ainsi que le script cm_date.agi, discutez avec votre administrateur pour plus de détails.

<BR>
<A NAME="call_menu-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="call_menu-menu_timeout">
<BR>
<B>Menu Timeout -</B> Ce champ est l-endroit où vous définissez le délai en secondes que le menu va attendre que l-appelant à entrer dans un choix DTMF. Fixation de ce champ à zéro 0 signifie qu-il n-y aura pas de temps d-attente après le prompt est joué.

<BR>
<A NAME="call_menu-menu_timeout_prompt">
<BR>
<B>Menu prompt timeout -</B> Ce champ contient le nom du fichier audio de l-invite à jouer lorsque le délai d-attente a été atteint. AUCUNE par défaut est de ne jouer aucune audio à temporisation.

<BR>
<A NAME="call_menu-menu_invalid_prompt">
<BR>
<B>Menu Blancs Prompt -</B> Ce champ contient le nom du fichier audio de l-invite à jouer lorsque l-appelant a choisi une option non valide. AUCUNE par défaut est de ne jouer aucune audio à invalide.

<BR>
<A NAME="call_menu-menu_repeat">
<BR>
<B>Menu Répétez -</B> Ce champ est l-endroit où vous définissez le nombre de fois que le menu sera joué après la première fois si aucun choix valide est faite par l-appelant. Par défaut est de 1 à répéter le menu une fois.

<BR>
<A NAME="call_menu-menu_time_check">
<BR>
<B>Menu Heure Départ -</B> Ce champ est l-endroit où vous pouvez choisir de restreindre l-accès au menu d-appel des heures spécifiques mis en place à l-heure choisie Call. Si le temps d-appel est vide, ce paramètre sera ignoré. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="call_menu-call_time_id">
<BR>
<B>Identifiant de la période d'appels -</B> C-est l-appel Heure ID qui sera utilisé pour restreindre fois appelant si le menu Heure option Vérifier est activée.

<BR>
<A NAME="call_menu-track_in_vdac">
<BR>
<B>Le suivi des appels dans Real-Time Report -</B> Ce champ est l-endroit où vous pouvez choisir si vous voulez l-appel à être suivis dans l-écran en temps réel comme un appel entrant RVI type. Par défaut est 1 pour actifs.

<BR>
<A NAME="call_menu-tracking_group">
<BR>
<B>Groupe de suivi -</B> Ceci est l-ID que vous pouvez utiliser pour suivre les appels vers ce menu d-appel lorsque l-on examine le Rapport IVR. La liste comprend CALLMENU par défaut ainsi que l-ensemble de l-En-Groupes.

<BR>
<A NAME="call_menu-dtmf_log">
<BR>
<B>Connectez-vous Appuyer sur la touche -</B> Cette option si elle est activée vous connecterez la presse DTMF clé par l'appelant dans ce menu Appel. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="call_menu-dtmf_field">
<BR>
<B>Connectez-vous Champ -</B> Si l'option de presse Key Log est activé, ce paramètre optionnel peut permettre à la réponse aussi être stocké dans ce champ la liste. vendor_lead_code, source_id, phone_code, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, alt_phone, email, security_phrase, comments, rank, owner, status, user. Par défaut est NONE pour personnes à mobilité réduite.

<BR>
<A NAME="call_menu-option_value">
<BR>
<B>Valeur d-option -</B> Ce champ est l-endroit où vous définissez l-option de menu, les choix possibles sont: 0,1,2,3,4,5,6,7,8,9,*,#,A,B,C,D,TIMECHECK. Le timecheck option spéciale peut être utilisée que si vous avez menu Heure Départ activé et il ya un temps d-appel définis pour le menu. Pour supprimer une option, vient de mettre la route pour supprimer et l-option sera supprimée lorsque vous cliquez sur le bouton ENVOYER. TIMEOUT vous permettra de mettre ce qui se passe à l'appel quand il arrive à expiration sans la participation de l'appelant. INVALID vous permettra de définir ce qui se passe lorsque l'appelant entre une option non valide. INVALID_2ND et 3e ne peut être active que si INVALID n'est pas utilisé, il faudra patienter jusqu'à l'entrée deuxième ou troisième invalide par l'appelant avant d'exécuter l'option.

<BR>
<A NAME="call_menu-option_description">
<BR>
<B>Option Description -</B> Ce champ est l-endroit où vous pouvez décrire l-option, cette description sera mis dans le plan de numérotation en tant que commentaire dessus de l-option.

<BR>
<A NAME="call_menu-option_route">
<BR>
<B>Option Itinéraire -</B> Ce menu contient les options pour savoir où envoyer l-appel si cette option est sélectionnée: CALLMENU, endogroupe, DID, HANGUP, EXTENSION, téléphone. Pour CALLMENU, la valeur de marche doit être l-ID du menu d-appel que vous voulez que l-appel adressé à. Pour endogroupe, l-en-groupe que vous voulez que l-appel doit être envoyé aux besoins d-être sélectionnée ainsi que les 5 autres options qui restent à être mis à bien acheminer un appel à un groupe d-entrants. Pour DID, la valeur de route doit être le modèle de DID que vous voulez envoyer l-appel. Pour HANGUP, la valeur de la Route peut être le nom d-un fichier audio à jouer avant de raccrocher l-appel. Par extension, la qualité route doit être l-extension plan de numérotation que vous souhaitez envoyer l-appel à, et la route de valeur de contexte est le contexte que l-extension est situé dans, s-il est laissé en blanc le contexte par défaut par défaut. Pour le téléphone, la valeur de route doit être la valeur de connexion de téléphone pour les téléphones d-entrée que vous voulez envoyer l-appel. Pour la messagerie vocale, la Value itinéraire doit être le numéro de boîte vocale, le mesage non disponible sera joué. Pour AGI, la valeur de route doit être le script AGI et toutes les valeurs qui doivent être transmis à elle. VMAIL_NO_INST enverra l appel à la boîte vocale que vous avez défini ci-dessous et ne jouera pas les instructions après le message vocal.

<BR>
<A NAME="call_menu-option_route_value">
<BR>
<B>Valeur option Itinéraire -</B> Ce champ est l-endroit où vous entrez la valeur qui désigne l-endroit du choisis option de route que l-appel doit être dirigé vers.

<BR>
<A NAME="call_menu-option_route_value_context">
<BR>
<B>Option Itinéraire valeur de contexte -</B> Ce champ est optionnel et utilisé uniquement pour l-option Itinéraires EXTENSION.

<BR>
<A NAME="call_menu-ingroup_settings">
<BR>
<B>Appelez le menu Réglages En-Groupe -</B> Si l'itinéraire est défini à l'endogroupe puis, il ya de nombreuses options que vous pouvez utiliser pour définir la manière dont l'appel est envoyé à la file d'attente en. En-Group est le groupe entrant que vous voulez l'appel à aller. Méthode La poignée est la façon dont vous voulez que l'appel à être manipulés, <a href="#inbound_dids-call_handle_method">Cliquez ici pour voir une liste des méthodes disponibles poignée</a>. Méthode de recherche définit la manière dont la file d'attente se trouve le prochain agent, de recommander le laisser sur LB. ID de liste est la liste que le nouveau chef de file est inséré, même si la méthode n'est pas une méthode LOOKUP et le plomb ne se trouve pas. ID de la campagne est la campagne pour chercher des listes par le biais si l'un des méthodes RC est utilisé. Code de téléphone est l'entrée du champ phone_code pour le plomb qui est insérée avec. VID Entrez nom de fichier est utilisé si la méthode est définie à l'une des méthodes VIDPROMPT, il est l'invite audio joué de demander au client d'entrer son identifiant. Nom du fichier VID Numéro d'identification est utilisée si la méthode est définie à l'une des méthodes VIDPROMPT, il est l'invite audio jouée après la clientèle entre leur carte d'identité, quelque chose comme vous avez saisies. VID Confirmer nom de fichier est utilisé si la méthode est définie à l'une des méthodes VIDPROMPT, il est l'invite audio joué afin de confirmer leur identité, quelque chose comme 1 COMMUNIQUÉ DE PRESSE POUR CONFIRMER ET 2 DE RETOURNER. Chiffres VID est utilisée si la méthode est définie à l'une des méthodes VIDPROMPT, si elle est définie à un certain nombre, c'est le nombre de chiffres qui doivent être saisies par le client lorsque vous êtes invité pour leur identité, s'il est réglé à vide ou X puis le client devra appuyer sur la livre ou le hachage pour terminer leur entrée sur leur carte d'identité.

<BR>
<A NAME="call_menu-custom_dialplan_entry">
<BR>
<B>Entrée personnalisée Dialplan -</B> Ce champ vous permet d-entrer dans tous les éléments de plan de numérotation que vous souhaitez pour le menu d-appel.

<BR>
<A NAME="call_menu-qualify_sql">
<BR>
<B>Qualifiez-SQL -</B> Ce champ vous permet d'entrer SQL - Structured Query Language - fragments de bases de données, comme des filtres, afin de déterminer si ce menu d'appel doit jouer pour l'appelant ou non. Cette fonctionnalité ne fonctionne que si l'appel a l'ensemble de callerIDname avant d'être envoyé à ce menu d'appel, soit comme un transfert d'enquête à l'étranger, ou par le biais d'un menu d'appel de baisse pour un appel en groupe. S'il ya un match, l'appel se déroulera comme normal. Si aucune correspondance n'est trouvée, l'appel sera acheminé à l'option D ou l'option non valide si aucune option D est réglé. Vous ne pouvez pas utiliser les guillemets simples dans ce domaine, seuls les guillemets si elles sont nécessaires. Default est vide pour handicapés.






<BR><BR><BR><BR>

<BR>
<A NAME="filter_phone_groups-filter_phone_group_id">
<BR>
<B>Filtre ID Groupe Téléphone -</B> C'est l'ID du groupe Téléphone filtre qui est le conteneur pour un groupe de numéros de téléphone que vous pouvez avoir automatiquement fouillé quand un appel arrive dans un fichier. DID et l'envoyer à un autre itinéraire si il ya un match Ce champ doit être comprise entre 2 et 20 caractères et n'ont pas de ponctuation à l'exception de soulignement.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_name">
<BR>
<B>Filtre Nom du groupe Téléphone -</B> This is the name of the Filter Phone Group and is displayed with the ID in select lists where this feature is used. This field should be between 2 and 40 characters.

<BR>
<A NAME="filter_phone_groups-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_description">
<BR>
<B>Filtre Description du groupe Téléphone -</B> Ceci est la description du groupe Téléphone filtre, il est purement à des fins de notation et n'est pas un champ obligatoire.




<BR><BR><BR><BR>

<B><FONT SIZE=3>REMOTE_AGENTS TABLE</FONT></B><BR><BR>
<A NAME="remote_agents-user_start">
<BR>
<B>Début des ID utilisateurs -</B> Ceci correspond à l ID d utilisateur en commençant qui est utilisé lorsque les entrées d agent à distance sont insérés dans le système. Si le nombre de lignes est supérieur à 1, ce nombre est incrémenté par un jusqu à ce que chaque ligne dispose d une entrée. Assurez-vous que vous créez un nouveau compte utilisateur avec un niveau utilisateur de 4 ou bien si vous voulez qu ils soient en mesure d utiliser la page de vdremote.php pour l accès de ce compte web à distance.

<BR>
<A NAME="remote_agents-number_of_lines">
<BR>
<B>Numéro de lignes -</B> Cela définie combien d'entrées d'agent distants le système crée, et détermine combien de lignes peuvent être envoyées sans soucis au numéro ci-dessus.

<BR>
<A NAME="remote_agents-server_ip">
<BR>
<B>IP du serveur -</B> Une entrée d'agent distant est seulement valide pour un serveur spécifique, c'est ici que vous sélectionnez quel serveur vous voulez.

<BR>
<A NAME="remote_agents-conf_exten">
<BR>
<B>Extension externe -</B> C'est le numéro vers lequel vous voulez faire suivre l'appel. Assurez vous que c'est un numéro du dial plan et si vous avez besoin d'un 9 au début mettez le ici. Testez en appelant ce numéro depuis un téléphone du système.

<BR>
<A NAME="remote_agents-extension_group">
<BR>
<B>Groupe d'extension -</B> Si réglé sur autre chose que NONE ou vide cela surcharger le champ d'extension externe et d'utiliser les entrées de groupe de vulgarisation qui ont la même extension ID groupe. Par défaut est NONE pour désactiver.

<BR>
<A NAME="remote_agents-status">
<BR>
<B>Etat -</B> C'est ici que vous activez ou desacivez l'agent distant. Dès que l'agent est actif le systeme permet que des appels puissent lui être envoyés. Cela peu prendre plus de 30 secondes à partir du moment où vous avez desactivé l'agent jusqu'à l'arrêt de la réception des appels.

<BR>
<A NAME="remote_agents-campaign_id">
<BR>
<B>Campagne -</B> C'est ici que vous choisissez la campagne dans laquelle ces agents distants ce connecteront. Les appels entrants ont besoin d'une campagne de type CLOSER et de selectionner les campagnes entrantes ci-dessus dans laquelle vous voulez recevoir des appels.

<BR>
<A NAME="remote_agents-on_hook_agent">
<BR>
<B>On-Hook Agent -</B> Cette option est utilisée uniquement pour les appels entrants vont à cet agent à distance. Cette fonctionnalité sera appeler l'agent à distance et ne pas envoyer le client à l'agent à distance jusqu'à ce que la ligne est répondu. Par défaut est N pour les handicapés.

<BR>
<A NAME="remote_agents-on_hook_ring_time">
<BR>
<B>Temps Ring On-Hook -</B> Cette option n'est utilisée que lorsque le champ Agent Sur-crochet ci-dessus est fixé à Y et alors seulement pour les appels entrants à venir à cet agent à distance. C'est le nombre de secondes que chaque tentative d'appel sonne pour essayer d'obtenir une réponse. Il est recommandé que vous définissez cette option sur quelques secondes inférieur à ce qu'il prend pour un appel à être envoyé vers la messagerie vocale. Par défaut est 15.

<BR>
<A NAME="remote_agents-closer_campaigns">
<BR>
<B>Groupes entrants -</B> C'est ici que vous selectionnez les groupes entrants depuis lesquels vous voulez recevoir des appels si vous avez selectionné une campagne de type CLOSER.


<BR><BR><BR><BR>

<B><FONT SIZE=3>EXTENSION_GROUPS TABLE</FONT></B><BR><BR>
<A NAME="extension_groups-extension_group_id">
<BR>
<B>Groupe d'extension -</B> Ce champ est requis lorsque vous entrez l'ID du groupe que vous voulez cette extension pour être mis en. Pas d'espaces ni de caractères spéciaux à l'exception des lettres et des chiffres soulignent.

<BR>
<A NAME="extension_groups-extension">
<BR>
<B>Extension -</B> Ce champ doit là où vous mettez l'extension que vous souhaitez plan de numérotation de l'agent à distance appelle à être envoyé à cette entrée du groupe d'extension.

<BR>
<A NAME="extension_groups-rank">
<BR>
<B>Rank -</B> Ce champ vous permet de classer les entrées de groupe de vulgarisation qui partagent le groupe même extension. Par défaut est 0.

<BR>
<A NAME="extension_groups-campaign_groups">
<BR>
<B>Groupes Campagnes -</B> Dans ce domaine, vous pouvez mettre une liste d'ID de campagne et ou ID de groupe entrants que vous souhaitez restreindre l'utilisation du groupe d'extension pour. Liste doivent être séparés par des tuyaux et un tuyau au début et à la fin de la chaîne.


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN_LISTS</FONT></B><BR><BR>
<A NAME="campaign_lists">
<BR>
<B>Les listes de cette campagne sont énumérées ici, leur activité est dénotée par le Y ou N et vous pouvez aller à l'écran de la liste en cliquant sur l'ID de la liste dans la première colonne.</B>


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN_STATUSES TABLE</FONT></B><BR><BR>
<A NAME="campaign_statuses">
<BR>
<B>Grâce à l utilisation des statuts de la campagne personnalisée, vous pouvez avoir des statuts qui existent seulement pour une campagne spécifique. Le statut doit être 1-8 caractères, la description doit être 2-30 caractères et sélectionnable définit si elle apparaît dans le système comme une disposition. Le champ human_answered est utilisé pour le calcul du pourcentage de baisse, ou le taux d abandon. Réglage human_answered à Y va utiliser ce statut en comptant les appels de l homme répondu. L option Catégorie vous permet de regrouper plusieurs états dans un catogy qui peut être utilisé pour l analyse statistique.</B>



<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>CAMPAIGN_HOTKEYS TABLE</FONT></B><BR><BR>
	<A NAME="campaign_hotkeys">
	<BR>
	<B>Grâce à l utilisation de campagne personnalisé touches de raccourci, les agents qui utilisent le web-client d un agent peut raccrocher et de disposition des appels en appuyant simplement sur une seule touche sur le clavier.</B> Il y a deux raccourcis claviers que vous pouvez utiliser en même temps que l'appel du numéro alternatif, ALTPH2 - Alternate Phone Hot Dial et ADDR3 - Adresse3 qui permet à un agent d'utiliser un raccourci pour raccocher l'appel, rester sur la fiche et appeler un autre numéro de la fiche. Vous pouvez également utiliser LTMG ou XFTAMM que les statuts de déclencher un transfert automatique de l'option Leave-Boîte vocale.





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_RECYCLE TABLE</FONT></B><BR><BR>
	<A NAME="lead_recycle">
	<BR>
	<B>Through the use of lead recycling, you can call specific statuses of leads again at a specified interval without resetting the entire list. Lead recycling is campaign-specific and does not have to be a selected dialable status in your campaign. The attempt delay field is the number of seconds until the lead can be placed back in the hopper, this number must be at least 120 seconds. The attempt maximum field is the maximum number of times that a lead of this status can be attempted before the list needs to be reset, this number can be from 1 to 10. You can activate and deactivate a lead recycle entry with the provided links.</B>





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>AUTO ALT DIAL STATUTS</FONT></B><BR><BR>
	<A NAME="auto_alt_dial_statuses">
	<BR>
	<B>Si le champ Appel automatique du numéro alternatif est défini, alors les fiches diposées sous ces statuts d'appels automatiques du numéro alternatif auront leurs champs numéro alternatif et/ou adresse3 appelés après n'importe quel statut de non-réponse.</B>

	<?php
	}
?>



<BR><BR><BR><BR>

<B><FONT SIZE=3>CODES PAUSE DES AGENTS</FONT></B><BR><BR>
<A NAME="pause_codes">
<BR>
<B>Si l agent Mettre en pause codes champ actif est réglé active, alors les agents seront en mesure de choisir parmi ces codes de pause quand ils cliquent sur le bouton PAUSE sur leurs écrans. Ces données sont ensuite stockées dans le journal de l agent. Le code de pause doit contenir que des lettres et des chiffres et être inférieur à 7 caractères. Le nom de code de pause peut être pas plus de 30 caractères.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN PRESETS</FONT></B><BR><BR>
<A NAME="xfer_presets">
<BR>
<B>Si le réglage de la campagne pour les presets est ENABLED alors vous avez la possibilité de définir le transfert à la Conférence des préréglages qui seront disponibles à l'agent qui leur permet de 3-way appeler ces préréglages ou transférer des appels aveugles à ces numéros de présélection. Ces presets ont également une option pour cacher le numéro associé à chaque préréglage de l'agent.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN CID Indicatif régionalS</FONT></B><BR><BR>
<A NAME="campaign_cid_areacodes">
<BR>
<B>Si le réglage du système pour le code zonal CID est activée et la mise en campagne pour une utilisation personnalisée CallerID est réglé sur le code zonal alors vous avez la possibilité de définir CID indicatif régional qui seront utilisés lors d'appels sortants à des fils dans cette campagne spécifique. Vous pouvez ajouter plusieurs callerIDs par indicatif régional et vous pouvez activer et désactiver à chacun en temps réel. Si plus d'un callerID est actif pour un indicatif régional spécifique, le système utilisera la callerid qui a été utilisé le moins de fois aujourd'hui. Si aucun callerIDs sont actifs pour le code zonal alors le CallerID campagne ou la liste prioritaire CallerID sera utilisé.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>USER_GROUPS TABLE</FONT></B><BR><BR>
<A NAME="user_groups-user_group">
<BR>
<B>Groupe utilisateur -</B> C est le nom court d un groupe d utilisateur, essayez de ne pas utiliser des espaces ou des signes de ponctuation pour ce domaine. max 20 caractères, au moins 2 caractères.

<BR>
<A NAME="user_groups-group_name">
<BR>
<B>Nom du groupe -</B> Ceci est la description du groupe d utilisateurs maximum de 40 caractères.

<BR>
<A NAME="user_groups-forced_timeclock_login">
<BR>
<B>Force timeclock Login -</B> Cette option vous permet de ne pas laisser un agent connecter à l interface de l agent si elles n ont pas enregistré dans la minuterie. Par défaut est N. Il ya une option d exempter les utilisateurs admin, les niveaux 8 et 9.

<BR>
<A NAME="user_groups-shift_enforcement">
<BR>
<B>Shift exécution -</B> Ce paramètre vous permet de restreindre l'agent de connexion sur la base des changements qui ont été sélectionnés ci-dessous. OFF ne fera pas appliquer à tous les changements. START ne faire respecter les temps de connexion, mais n'aura pas d'incidence sur un agent qui est en cours d'exécution sur leurs heures de travail si elles sont déjà logged in ALL appliquent passer l'heure de début et se connecter à un agent, après, ils courent sur la fin de leur quart de temps. La valeur par défaut est OFF.

<BR>
<A NAME="user_groups-group_shifts">
<BR>
<B>Groupe changements - </B> Il s'agit d'un choix de la liste des changements qui peuvent restreindre l'accès des agents à temps sur le système.

<BR>
<A NAME="user_groups-allowed_campaigns">
<BR>
<B>Campagnes autorisés -</B> C'est une liste de campagnes dans lesquelles les utilisateurs de ce groupes pourront se connecter. L'option TOUTES LES CAMPAGNES autorise les utilisateurs de ce groupe à voir et se connecter à toutes les campagnes sur le système.

<BR>
<A NAME="user_groups-agent_status_viewable_groups">
<BR>
<B>Le statut de mandataire Viewable Groupes -</B> Ceci est une liste sélectionnable de groupes d-utilisateurs et les fonctions utilisateur pour lequel les membres de ce groupe d-utilisateurs peuvent visualiser l-état des appels ainsi que le transfert vers l-intérieur de l-écran de l-agent. The All-GROUPES option permet aux utilisateurs de ce groupe à voir et à transférer des appels vers n-importe quel utilisateur sur le système. La CAMPAGNE AGENTS option permet aux utilisateurs de ce groupe à voir et à transférer des appels vers n-importe quel utilisateur dans la campagne à laquelle ils sont connectés à. L'option non-logé-IN-AGENTS permet à tous les utilisateurs dans le système pour être affiché, même si elles ne sont pas enregistrés curently.

<BR>
<A NAME="user_groups-agent_status_view_time">
<BR>
<B>Le statut de mandataire Afficher Time -</B> Cette option définit si l-agent va voir la quantité de temps que les utilisateurs dans leur encadré agent ont été dans leur état actuel. Par défaut est N pour non ou handicapés.

<BR>
<A NAME="user_groups-agent_call_log_view">
<BR>
<B>Voir le journal de l'Agent d'appel -</B> Cette option définit si l agent sera en mesure de voir leur journal d appel pour les appels traités à travers l écran de l agent. Défaut est N pour non ou handicapés.

<BR>
<A NAME="user_groups-agent_xfer_options">
<BR>
<B>Options de transfert de l'agent -</B> Ces options permettent la désactivation des boutons spécifiques dans la section Transfert Conférence de l'interface de l'agent. Par défaut est O pour oui ou permis.

<BR>
<A NAME="user_groups-agent_fullscreen">
<BR>
<B>Agent plein écran -</B> Cette option si la valeur Y régler la hauteur et la largeur de l écran de l agent de la taille de la fenêtre du navigateur Web sans aucune indemnité pour les agents View, appels en attente Afficher ou appels en vue de la session. Défaut est N pour non ou handicapés.

<BR>
<A NAME="user_groups-allowed_reports">
<BR>
<B>Rapports admis -</B> Si un utilisateur dans ce groupe est fixé à 7 niveau de l'utilisateur ou plus, cette fonction peut être utilisée pour restreindre les rapports que les utilisateurs peuvent visualiser. Par défaut est ALL. Si vous voulez sélectionner plus d'un rapport, puis appuyez sur la touche Ctrl de votre clavier pendant que vous sélectionnez les rapports.

<BR>
<A NAME="user_groups-admin_viewable_groups">
<BR>
<B>Groupes d'utilisateurs autorisés -</B> Ceci est une liste sélectionnable de groupes d'utilisateurs à laquelle les membres de ce groupe d'utilisateurs peuvent visualiser et éventuellement modifier. Groupes d'utilisateurs peuvent restreindre l'accès à presque tous les aspects du système, à partir de DID entrants vers des téléphones à des boîtes vocales. Le - ALL - option permet aux utilisateurs de ce groupe à voir et à connecter à n'importe quel dossier sur le système si leurs autorisations d'utilisateur le permettent.

<BR>
<A NAME="user_groups-admin_viewable_call_times">
<BR>
<B>Durée des appels autorisés -</B> Ceci est une liste sélectionnable Durée des appels à laquelle les membres de ce groupe d'utilisateurs peuvent utiliser dans les campagnes, dans les groupes et les menus d'appel. Le - ALL - option permet aux utilisateurs de ce groupe à utiliser tous les temps d'appel dans le système.


<?php
if (strlen($SSwebphone_url) > 5)
	{
	?>
	<BR>
	<A NAME="user_groups-webphone_url_override">
	<BR>
	<B>Remplacer URL Webphone -</B> Ce paramètre vous permet de définir une URL webphone autre seulement pour les membres d'un groupe d'utilisateurs. Par défaut est vide.

	<BR>
	<A NAME="user_groups-webphone_systemkey_override">
	<BR>
	<B>Webphone Key System Override -</B> Ce paramètre vous permet de définir une clé de remplacement webphone système juste pour les membres d'un groupe d'utilisateurs. Par défaut est vide.

	<BR>
	<A NAME="user_groups-webphone_dialpad_override">
	<BR>
	<B>Webphone Dialpad Override -</B> Ce paramètre vous permet d'activer ou de désactiver le clavier sur le webphone seulement pour les membres d'un groupe d'utilisateurs. Défaut est Disabled. Toggle va permettre à l'utilisateur de visualiser et de cacher le clavier en cliquant sur un lien. TOGGLE_OFF par défaut pour ne pas montrer le clavier sur la première charge, mais permettra à l'utilisateur d'afficher le clavier en cliquant sur le lien clavier.

	<?php
	}

if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="user_groups-qc_allowed_campaigns">
	<BR>
	<B>QC permis Campagnes - </B> Il s'agit d'un choix de la liste des campagnes où les membres de ce groupe d'utilisateurs seront en mesure de QC. Le ALL-CAMPAGNES option permet aux utilisateurs de ce groupe de Québec une campagne sur le système.

	<BR>
	<A NAME="user_groups-qc_allowed_inbound_groups">
	<BR>
	<B>QC permis l'arrivée des groupes - </B> Il s'agit d'un choix de la liste des groupes dont l'arrivée des membres de ce groupe d'utilisateurs seront en mesure de QC. Le ALL-GROUPES option permet aux utilisateurs dans ce groupe d'utilisateurs QC arrivée du groupe sur tout le système.
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SCRIPTS TABLE</FONT></B><BR><BR>
<A NAME="scripts-script_id">
<BR>
<B>ID du script -</B> C est le nom court d un script. Il doit s agir d un identificateur unique. Essayez de ne pas utiliser des espaces ou des signes de ponctuation pour ce domaine. max 10 caractères, au moins 2 caractères.

<BR>
<A NAME="scripts-script_name">
<B>Nom du script -</B> C est le titre d un script. Il s agit d un bref résumé du script. max 50 caractères, au moins 2 caractères. Il devrait y avoir aucun espace ni ponctuation de toute nature dans le domaine de Theis.

<BR>
<A NAME="scripts-script_comments">
<B>Commentaires du script -</B> C est là que vous pouvez placer des commentaires pour un script d écran de l agent tel que modifié à-mise à jour gratuite sur septembre 23 -. max 255 caractères, au moins 2 caractères.

<BR>
<A NAME="scripts-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="scripts-script_text">
<B>Texte du script -</B> C est là que vous placez le contenu d un script à l écran de l agent. Minimum de 2 caractères. Vous pouvez avoir des informations à la clientèle est auto-alimenté dans ce script à l aide "--A--field--B--" où le champ est l un des noms de champs suivants: vendor_lead_code, source_id, list_id, gmt_offset_now, called_since_last_reset, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, lead_id, campaign, phone_login, group, channel_group, SQLdate, epoch, uniqueid, customer_zap_channel, server_ip, SIPexten, session_id, dialed_number, dialed_label, rank, owner, camp_script, in_script, script_width, script_height, recording_filename, recording_id, user_custom_one, user_custom_two, user_custom_three, user_custom_four, user_custom_five, preset_number_a, preset_number_b, preset_number_c, preset_number_d, preset_number_e, preset_number_f, preset_dtmf_a, preset_dtmf_b, did_id, did_extension, did_pattern, did_description, closecallid, xfercallid, agent_log_id, entry_list_id, call_id, user_group, called_count, TABLEper_call_notes. For example, this sentence would print the persons name in it----<BR><BR>  Hello, can I speak with --A--first_name--B-- --A--last_name--B-- please? Well hello --A--title--B-- --A--last_name--B-- how are you today?<BR><BR> This would read----<BR><BR>Hello, can I speak with John Doe please? Well hello Mr. Doe how are you today?<BR><BR> Vous pouvez également utiliser une iframe pour charger une fenêtre séparée dans l onglet Script, voici un exemple avec des variables prérempli:

<DIV style="height:200px;width:400px;background:white;overflow:scroll;font-size:12px;font-family:sans-serif;" id=iframe_example>
&#60;iframe src="http://www.sample.net/test_output.php?lead_id=--A--lead_id--B--&#38;vendor_id=--A--vendor_lead_code--B--&#38;list_id=--A--list_id--B--&#38;gmt_offset_now=--A--gmt_offset_now--B--&#38;phone_code=--A--phone_code--B--&#38;phone_number=--A--phone_number--B--&#38;title=--A--title--B--&#38;first_name=--A--first_name--B--&#38;middle_initial=--A--middle_initial--B--&#38;last_name=--A--last_name--B--&#38;address1=--A--address1--B--&#38;address2=--A--address2--B--&#38;address3=--A--address3--B--&#38;city=--A--city--B--&#38;state=--A--state--B--&#38;province=--A--province--B--&#38;postal_code=--A--postal_code--B--&#38;country_code=--A--country_code--B--&#38;gender=--A--gender--B--&#38;date_of_birth=--A--date_of_birth--B--&#38;alt_phone=--A--alt_phone--B--&#38;email=--A--email--B--&#38;security_phrase=--A--security_phrase--B--&#38;comments=--A--comments--B--&#38;user=--A--user--B--&#38;campaign=--A--campaign--B--&#38;phone_login=--A--phone_login--B--&#38;fronter=--A--fronter--B--&#38;closer=--A--user--B--&#38;group=--A--group--B--&#38;channel_group=--A--group--B--&#38;SQLdate=--A--SQLdate--B--&#38;epoch=--A--epoch--B--&#38;uniqueid=--A--uniqueid--B--&#38;customer_zap_channel=--A--customer_zap_channel--B--&#38;server_ip=--A--server_ip--B--&#38;SIPexten=--A--SIPexten--B--&#38;session_id=--A--session_id--B--&#38;dialed_number=--A--dialed_number--B--&#38;dialed_label=--A--dialed_label--B--&#38;rank=--A--rank--B--&#38;owner=--A--owner--B--&#38;phone=--A--phone--B--&#38;camp_script=--A--camp_script--B--&#38;in_script=--A--in_script--B--&#38;script_width=--A--script_width--B--&#38;script_height=--A--script_height--B--&#38;recording_filename=--A--recording_filename--B--&#38;recording_id=--A--recording_id--B--&#38;user_custom_one=--A--user_custom_one--B--&#38;user_custom_two=--A--user_custom_two--B--&#38;user_custom_three=--A--user_custom_three--B--&#38;user_custom_four=--A--user_custom_four--B--&#38;user_custom_five=--A--user_custom_five--B--&#38;preset_number_a=--A--preset_number_a--B--&#38;preset_number_b=--A--preset_number_b--B--&#38;preset_number_c=--A--preset_number_c--B--&#38;preset_number_d=--A--preset_number_d--B--&#38;preset_number_e=--A--preset_number_e--B--&#38;preset_number_f=--A--preset_number_f--B--&#38;preset_dtmf_a=--A--preset_dtmf_a--B--&#38;preset_dtmf_b=--A--preset_dtmf_b--B--&#38;did_id=--A--did_id--B--&#38;did_extension=--A--did_extension--B--&#38;did_pattern=--A--did_pattern--B--&#38;did_description=--A--did_description--B--&#38;closecallid=--A--closecallid--B--&#38;xfercallid=--A--xfercallid--B--&#38;agent_log_id=--A--agent_log_id--B--&#38;entry_list_id=--A--entry_list_id--B--&#38;call_id=--A--call_id--B--&&#38;user_group=--A--user_group--B--&&#38;" style="width:580;height:290;background-color:transparent;" scrolling="auto" frameborder="0" allowtransparency="true" id="popupFrame" name="popupFrame" width="460" height="290" STYLE="z-index:17"&#62;
&#60;/iframe&#62;
</DIV>
<BR>
Vous pouvez également utiliser un IGNORENOSCROLL variable spéciale pour forcer les barres de défilement sur l'onglet script même si vous utilisez une iframe en son sein.

<BR>
<A NAME="scripts-active">
<BR>
<B>Activé -</B> Cela determine si ce script peut être selectionné pour être utilisé dans une campagne.





<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_FILTRES TABLE</FONT></B><BR><BR>
	<A NAME="lead_filters-lead_filter_id">
	<BR>
	<B>Filter ID -</B> C est le nom court d un filtre principal. Il doit s agir d un identificateur unique. Ne pas utiliser des espaces ou des signes de ponctuation pour ce domaine. max 10 caractères, au moins 2 caractères.

	<BR>
	<A NAME="lead_filters-lead_filter_name">
	<B>Nom du Filtre -</B> C'est un nom plus descrtiptif du filtre. C'est un court resumé du filtre. Max 30 caractères Min 2.

	<BR>
	<A NAME="lead_filters-lead_filter_comments">
	<B>Filter Commentaires -</B> C est là que vous pouvez placer des commentaires pour un filtre comme-tous les appels Californie mène. max 255 caractères, au moins 2 caractères.

	<BR>
	<A NAME="lead_filters-user_group">
	<BR>
	<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

	<BR>
	<A NAME="lead_filters-lead_filter_sql">
	<B>Filtre SQL -</B> C'est ici que vous mettez le fragment de requête SQL que vous voulez utiliser pour filtrer. Ne commencez pas ni ne finissez pas par AND, cela est automatiquement ajouté par le script cron. Exemple : called_count > 4 and called_count < 8 .
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>PERIODES D'APPELS TABLE</FONT></B><BR><BR>
<A NAME="call_times-call_time_id">
<BR>
<B>Identifiant de la période d'appels -</B> C est le nom court d une définition de durée d appel. Il doit s agir d un identificateur unique. Ne pas utiliser des espaces ou des signes de ponctuation pour ce domaine. max 10 caractères, au moins 2 caractères.

<BR>
<A NAME="call_times-call_time_name">
<B>Nom de la période d'appel -</B> C'est un nom plus descriptif de la période d'appel. Une courte description. 30 caractères maximum et 2 minimum.

<BR>
<A NAME="call_times-call_time_comments">
<B>Commentaires de la période d'appels -</B> C est là que vous pouvez placer des commentaires pour une définition de durée d appel tels que-10 heures-16 heures avec l État d appel supplémentaire restrictions. max 255 caractères.

<BR>
<A NAME="call_times-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="call_times-ct_default_start">
<B>Heures de début et de fin par défaut -</B> Ce sont les heures par défaut pendant lesquelles un appel est autorisé si l'heure de début de day-of-the-week n'est pas définie. 0 est minuit. Pour interdir complétement les appels mettez 2400 et mettez 2400 à l'heure de fin par défaut. Pour autoriser les appels 24h/24, remplissez les champs avec 0 pour le début et 2400 pour la fin. Pour entrant seulement, vous pouvez également définir la durée d'appel d'arrêt supérieur à 2400 si vous voulez que le temps appel à aller au-delà de minuit. Donc, si vous voulez que votre temps d'appel à courir à partir de 6 h jusqu'à 2 h du matin le lendemain, vous mettriez 0600 que l'heure de début et 2600 que le temps d'arrêt.

<BR>
<A NAME="call_times-ct_sunday_start">
<B>Heures de début et de fin pour un jour -</B> Ce sont les heures de début et de fin personnalisés pour un jour. Les mêmes règles s'appliquent qu'aux heures de début et de fin par défaut.

<BR>
<A NAME="call_times-default_afterhours_filename_override">
<B>Après Nom Heures Override -</B> Ces champs vous permettent de remplacer le message après les heures pour les groupes entrants si elle est définie à quelque chose. Par défaut est vide.

<BR>
<A NAME="call_times-ct_state_call_times">
<B>Définitions des états des périodes d'appel -<\/B> C'est la liste des définitions spécifiques des périodes d'appels des états qui sont suivies dans cette définition de période d'appels.

<BR>
<A NAME="call_times-state_call_time_state">
<B>State Call Time State -<\/B> Ce sont les deux lettres code pour l'état auquel cette définition de période d'appels correspond. Pour que cela prenne effet, la période d'appel locale qui est définie dans la campagne doit avoir cette enregistrement d'état de période d'appel aussi bien que toutes les fiches ayant ces deux lettre codes d'état.

<A NAME="call_times-holiday_id">
<BR>
<B>ID vacances -</B> C'est le nom court d'une définition de vacances. Cela doit être un identifiant unique. Ne pas utiliser d'espaces ou de la ponctuation pour ce champ. maximum 30 caractères, un minimum de 2 caractères.

<BR>
<A NAME="call_times-holiday_name">
<B>Nom de vacances -</B> C'est un nom plus descriptif de la Définition de vacances. Ceci est un bref résumé de la définition de vacances. max 100 caractères, un minimum de 2 caractères.

<BR>
<A NAME="call_times-holiday_comments">
<B>Vacances Commentaires -</B> This is where you can place comments for a Holiday Definition such as -10am to 4pm boxing day restrictions-.  max 255 characters.

<BR>
<A NAME="call_times-holiday_date">
<B>Date de vacances -</B> Il s'agit de la date de la fête.

<BR>
<A NAME="call_times-holiday_status">
<B>Statut de vacances -</B> C'est l'état de l'entrée de vacances. Signifie le statut actif que la fête sera activé à la date de vacances. Signifie inactif que la fête sera ignorée, même sur la date de vacances. Signifie que le jour férié a passé sa date de vacances EXPIRATION. Défaut est inactif.




<BR><BR><BR><BR>

<B><FONT SIZE=3>SHIFTS TABLE</FONT></B><BR><BR>
<A NAME="shifts-shift_id">
<BR>
<B>Shift ID -</B> C est le nom court d une définition de décalage de système. Il doit s agir d un identificateur unique. Ne pas utiliser des espaces ou des signes de ponctuation pour ce domaine. max 20 caractères, au moins 2 caractères.

<BR>
<A NAME="shifts-shift_name">
<B>Nom Shift - </B> Il s'agit d'un nom descriptif de la Maj Définition. Ceci est un bref résumé de la définition Maj. max 50 caractères, un minimum de 2 caractères.

<BR>
<A NAME="shifts-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="shifts-shift_start_time">
<B>Start Time Shift - </B> Il est temps que la campagne commence à passer. Ne doivent être des chiffres, 9:30 AM serait 0930 et 5:00 PM serait 1700.

<BR>
<A NAME="shifts-shift_length">
<B>Longueur Shift - </B> Il est temps en heures et minutes que dure la campagne de changement. 8 heures seraient 08h00 et 7 heures et 30 minutes serait 07:30.

<BR>
<A NAME="shifts-shift_weekdays">
<B>Shift Weekdays - </B> Dans cette section, vous devriez choisir le jour de la semaine que ce changement est active.

<BR>
<A NAME="shifts-report_option">
<B>Option Report -</B> Cette option permet ce changement spécifique à apparaître dans les rapports sélectionnés qui soutiennent cette option.

<BR>
<A NAME="shifts-report_rank">
<B>Rapport Rang -</B> Cette option vous permet de classer les changements dans les rapports sélectionnés qui soutiennent cette option.




<BR><BR><BR><BR>
<A NAME="audio_store">
<B>Audio Store -</B> Cet utilitaire vous permet de télécharger des fichiers audio sur le serveur Web de sorte qu ils peuvent être distribués à tous les serveurs du système dans un cluster multi-serveur. Une remarque importante, seuls deux types de fichiers audio fonctionne. Wav qui sont PCM Mono 16bit 8k et fichiers. Gsm qui sont 8k 8bit. S il vous plaît vérifier que vos fichiers sont correctement formatés avant de les télécharger ici.



<BR><BR><BR><BR>

<B><FONT SIZE=3>MUSIC_ON_HOLD TABLE</FONT></B><BR><BR>
<A NAME="music_on_hold-moh_id">
<BR>
<B>Musique en attente d-identification -</B> Ceci est le nom court d-une musique d-attente d-entrée. Cela doit être un identifiant unique. N-utilisez pas d-espaces ou de ponctuation dans ce champ. max 100 caractères, minimum de 2 caractères.

<BR>
<A NAME="music_on_hold-moh_name">
<B>Musique d-attente Nom -</B> C est un nom plus descriptif de la musique sur l entrée de maintien. Il s agit d un bref résumé de la musique sur le contexte de maintien et s affiche comme un commentaire dans le fichier musiconhold conf. max 255 caractères, au moins 2 caractères.

<BR>
<A NAME="music_on_hold-active">
<B>Active -</B> Cette option vous permet de définir la musique d-attente à l-entrée active ou inactive. Inactifs va supprimer l-entrée à partir des fichiers conf.

<BR>
<A NAME="music_on_hold-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="music_on_hold-random">
<B>Random Order -</B> Cette option vous permet de définir la lecture des fichiers audio dans un ordre aléatoire. Si elle est égale à N, alors l-ordre défini sera utilisé.

<BR>
<A NAME="music_on_hold-filename">
<B>Filename -</B> Pour ajouter un nouveau fichier audio à une musique d-attente d-entrée, le fichier doit d-abord être dans le magasin de l-audio, alors vous pouvez sélectionner le fichier et cliquez sur Soumettre pour l-ajouter à la liste des fichiers. Musique d-attente est mis à jour une fois par minute s-il ya eu des modifications apportées. Tous les fichiers qui ne figurent pas dans une musique à l-entrée détiennent qui sont présents dans la musique sur le dossier de détention seront supprimés.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TTS_PROMPTS TABLE</FONT></B><BR><BR>
<A NAME="tts_prompts-tts_id">
<BR>
<B>TTS ID -</B> Ceci est le nom court d-une entrée de TTS. Cela doit être un identifiant unique. N-utilisez pas d-espaces ou de ponctuation dans ce champ. max 50 caractères, minimum de 2 caractères.

<BR>
<A NAME="tts_prompts-tts_name">
<B>TTS Nom -</B> Il s-agit d-un nom plus descriptif de l-entrée TTS. Ceci est un bref résumé de la définition de TTS. max 100 caractères, minimum de 2 caractères.

<BR>
<A NAME="tts_prompts-active">
<B>Active -</B> Cette option vous permet de définir l-entrée de TTS à l-état actif ou inactif.

<BR>
<A NAME="tts_prompts-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="tts_prompts-tts_voice">
<B>TTS Voix -</B> C'est là que vous définissez la voix pour être utilisé dans la génération TTS. Par défaut est Allison-8kHz.

<BR>
<A NAME="tts_prompts-tts_text">
<B>TTS Text -</B> Il s-agit du texte à la parole champ de données qui est envoyé à Cepstral pour la création du fichier audio à lire pour le client. vous pouvez utiliser Speech Synthesis Markup Language-SSML, dans ce domaine, par exemple, &lt;break time='1000ms'/&gt; for a 1 second break. You can also use several variables such as first name, last name and title as system variables just like you do in a Script: --A--first_name--B--. Si vous avez statiques fichiers audio que vous souhaitez utiliser en fonction de la valeur de l'un des domaines que vous pouvez utiliser aussi bien avec ceux C et D balises. Les noms de fichiers doivent être en minuscules et ils doivent être 8k 16bit pcm wav. Le nom de domaine doit être le même, mais sans le wav. Dans le nom du fichier. Par exemple - C ---- A - address3 - B ---- D - tout d'abord trouver la valeur pour address3, alors il serait tenter de trouver un fichier audio correspondant à celle de la valeur à mettre dans l'invite. Voici une liste des variables disponibles: lead_id, entry_date, modify_date, status, user, vendor_lead_code, source_id, list_id, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_count, last_local_call_time, rank, owner




<BR><BR><BR><BR>

<B><FONT SIZE=3>VOICEMAIL TABLE</FONT></B><BR><BR>
<A NAME="voicemail-voicemail_id">
<BR>
<B>ID vocale -</B> Il s-agit de la identifiant nombres de cette boîte aux lettres. Cela ne doit pas être un doublon d-un répondeur d-identification existant ou la boîte vocale d-ID d-un téléphone sur le système, au moins 2 caractères.

<BR>
<A NAME="voicemail-fullname">
<B>Name -</B> Ce nom est associé à cette boîte vocale. max 100 caractères, minimum de 2 caractères.

<BR>
<A NAME="voicemail-pass">
<B>Password -</B> C-est le mot de passe utilisés pour accéder à la boîte vocale lors de la numérotation pour vérifier ses messages max 10 caractères, minimum de 2 caractères.

<BR>
<A NAME="voicemail-active">
<B>Active -</B> Cette option vous permet de configurer la boîte vocale à l-état actif ou inactif. Si la case est inactive vous ne pouvez pas laisser de messages sur elle et vous ne pouvez pas vérifier les messages en elle.

<BR>
<A NAME="voicemail-email">
<B>Email -</B> Ce paramètre facultatif vous permet d-avoir les messages vocaux envoyés à un compte de messagerie, si votre système est configuré pour envoyer des email. Si ce champ est vide, alors pas de courriels seront envoyés.

<BR>
<A NAME="voicemail-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="voicemail-delete_vm_after_email">
<B>Supprimer messagerie vocale après le courrier électronique -</B> Ce paramètre facultatif vous permet d-avoir les messages vocaux supprimés du système après avoir été envoyé par courriel Out. Par défaut est n.

<BR>
<A NAME="voicemail-voicemail_greeting">
<B>Message d'accueil -</B> Ce paramètre optionnel vous permet de définir un fichier audio message d'accueil du magasin audio. Défaut est vide.

<BR>
<A NAME="voicemail-voicemail_timezone">
<B>Zone de messagerie vocale -</B> Ce paramètre vous permet de définir la zone que cette boîte vocale sera réglé lorsque le temps est enregistré pour un message. Par défaut est définie dans les paramètres système.

<BR>
<A NAME="voicemail-voicemail_options">
<B>Options de la messagerie vocale -</B> Ce paramètre optionnel vous permet de définir les paramètres de messagerie vocale supplémentaires. Il est recommandé que vous laissez ce champ vide si vous ne savez ce que vous faites.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>

	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LIST LOADER FUNCTIONALITY</FONT></B><BR><BR>
	<A NAME="list_loader">
	<BR>
	Le Web de base de plomb chargeur vise simplement à prendre un fichier de plomb - jusqu à 8MB - c est l un des onglets ou tuyau délimité et le charger dans le tableau de la liste. Le chargeur de plomb permet de choix de terrain et TXT-Plain Text, CSV Comma Separated Values ​​et les formats de fichiers XLS Excel. Le chargeur plomb ne fait pas la validation des données, mais elle vous permet de vérifier la présence de doublons en elle-même, dans la campagne ou dans l ensemble du système. Aussi, assurez-vous que vous avez créé la liste que ces pistes doivent être sous afin que vous puissiez les utiliser. Voici une liste des champs dans le bon ordre pour les fichiers de plomb:
		<OL>
		<LI>Code vendeur de la fiche - montre dans le champ dID du vendeur de l'interface graphique
		<LI>Code source - usage interne uniquement pour les administrateurs et les administrateurs de la base de données
		<LI>Identifiant de la liste - le numéro de la liste dans laquelle seront ces fiches
		<LI>Phone Code - the prefix for the phone number - 1 for US, 44 for UK, 61 for AUS, etc
		<LI>Numéro de téléphone - doit contenir au moins 8 chiffres
		<LI>Titre - titre du client - Mr. Mme. Mlle., etc...
		<LI>Nom
		<LI>Initiales
		<LI>Prénom
		<LI>Adresse Ligne 1
		<LI>Adresse Ligne 2
		<LI>Adresse Ligne 3
		<LI>Ville
		<LI>Etat - Limité à 2 caractères
		<LI>Province
		<LI>Code Postal
		<LI>Pays
		<LI>Sexe
		<LI>Date de naissance
		<LI>Numero de téléphone alternatif
		<LI>Adresse mail
		<LI>Phrase de sécurité
		<LI>Commentaires
		<LI>Rank
		<LI>Owner
		</OL>

	<BR>NOTES : La fonctionnalité de chargeur de fil d'exceler est permisepar une série de manuscrits de Perl et doit avoir un dossier en placecorrectement configuré de /etc/astguiclient.conf sur le web server.En outre, des modules d'un Perl de couples doivent être chargés pourelle pour travailler aussi bien - oLE-Storage_Lite-Storage_Lite et leBilan-ParseExcel. Vous pouvez vérifier les erreurs d'exécution dansces derniers en regardant votre dossier d'error_log d'apache. Enoutre, pour des contrôles de duplication contre le campaignénumère, la liste qui a de nouveaux fils entrer dans lui doit êtrecréée dans le système avant que vous commenciez à charger lesfils.

	<BR>
	<A NAME="list_loader-duplicate_check">
	<BR>
	<B>Déduplication - </B>Les options en double vous permettent de vérifier les entrées dupliquées que vous chargez les pistes dans le système. Vous pouvez choisir de vérifier la présence de doublons dans seulement la même liste, seulement les mêmes listes de campagne ou dans l ensemble du système. Si vous avez choisi une méthode de contrôle des doublons, vous pouvez également sélectionner éventuellement les seuls statuts spécifiques que vous souhaitez dupliquer chèque contre.

	<BR>
	<A NAME="list_loader-file_layout">
	<BR>
	<B>File layout - </B>La mise en page du fichier que vous chargez. "Format standard" utilise le format de fichier standard prédéfini. "Agencement personnalisé" permet à l utilisateur de définir la mise en page du fichier eux-mêmes. "Modèle personnalisé» est un hybride des deux options précédentes, qui permet à l utilisateur d utiliser un format personnalisé, ils ont défini précédemment et enregistré à l aide du Créateur modèle personnalisé.

	<BR>
	<A NAME="list_loader-template_id">
	<BR>
	<B>ID modèle -</B> If the user has selected "Disposition personnalisée" from the "File layout" options, then this the the template the lead loader will use.  It will also override the selected list ID with the list ID that was assigned to the selected template when it was created.



	<BR><BR><BR><BR>
	<?php
	}
?>





<B><FONT SIZE=3>TABLE DES TELEPHONES</FONT></B><BR><BR>
<A NAME="phones-extension">
<BR>
<B>Extension Téléphone -</B> Ce champ est l'endroit où vous mettez le nom téléphones tel qu'il apparaît à Asterisk pas y compris le protocole ou une barre oblique au début. Par exemple: pour le téléphone SIP SIP/test101 l'extension Téléphone serait test101. En outre, pour IAX2 téléphones: IAX2/IAXphone1 @ IAXphone1 serait IAXphone1. Pour Zap et dahdi joint channelbank ou FXS téléphones assurez-vous que vous mettez le numéro de canal complète sans le préfixe: Zap/25-1 serait 25-1. Une autre note, assurez-vous que vous définissez le champ ci-dessous Protocole correctement pour votre type de téléphone.

<BR>
<A NAME="phones-dialplan_number">
<BR>
<B>Numéro dans le Dial Plan -</B> Ce champ est pour le numéro utilisé pour appeler ce téléphone. Ce numéro est défini dans le fichier extensions.conf du serveur Asterisk

<BR>
<A NAME="phones-voicemail_id">
<BR>
<B>Boîte vocale -</B> Ce champ est pour la boîte vocale vers laquelle iront les messages pour l'utilisateur de ce téléphone. Il est utilisé pour vérifier la présence de messages et pour permettre à l'utilisateur d'utiliser le bouton VOICEMAIL dans l'application astGUIclient.

<BR>
<A NAME="phones-voicemail_timezone">
<B>Zone de messagerie vocale -</B> Ce paramètre vous permet de définir la zone que cette boîte vocale sera réglé lorsque le temps est enregistré pour un message. Par défaut est définie dans les paramètres système.

<BR>
<A NAME="phones-voicemail_options">
<B>Options de la messagerie vocale -</B> Ce paramètre optionnel vous permet de définir les paramètres de messagerie vocale supplémentaires. Il est recommandé que vous laissez ce champ vide si vous ne savez ce que vous faites.

<BR>
<A NAME="phones-outbound_cid">
<BR>
<B>CallerID sortant -</B> Ce champ est celui dans lequel vous entrez le numéro callerID que vous souhaitez voir apparaître lors d'appels externes passés depuis le client web astguiclient. Cela ne fonctionne pas avec RBS, non-PRI, T1/E1s.

<BR>
<A NAME="phones-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="phones-phone_ip">
<BR>
<B>Adresse IP du téléphone -</B> Ce champ est pour l'adresse IP du téléphone si c'est un téléphone VOIP. Ce champ est optionnel

<BR>
<A NAME="phones-computer_ip">
<BR>
<B>Adresse IP de l'ordinateur -</B> Ce champ est pour l'adresse IP de l'ordinateur. C'est un champ optionel

<BR>
<A NAME="phones-server_ip">
<BR>
<B>IP du serveur -</B> Ce champ est celui où vous choisissez le serveur sur lequel est activé le téléphone.

<BR>
<A NAME="phones-login">
<BR>
<B>Écran de connexion Agent -</B> La connexion utilisé pour l utilisateur du téléphone pour accéder à des applications clientes, comme l écran de l agent.

<BR>
<A NAME="phones-pass">
<BR>
<B>Login Mot de passe -</B>  Le mot de passe utilisé pour l'utilisateur du téléphone pour se connecter à des applications clientes basées sur le Web. IMPORTANT, c'est le seul mot de passe pour la connexion agent web interface téléphonique, de changer le sip.conf ou mot de passe iax.conf, ou secret, pour cet appareil de téléphone dont vous avez besoin de modifier le mot de passe d'inscription dans le champ suivant.

<BR>
<A NAME="phones-conf_secret">
<BR>
<B>Inscription Mot de passe -</B> C-est le secret ou mot de passe, pour le téléphone dans le IAX ou SIP auto-généré fichier de conf pour ce téléphone. La limite est de 20 caractères alphanumériques tiret de soulignement et acceptée. Par défaut est test. Anciennement appelé secret fichier de conf. Un mot de passe d'enregistrement forte doit être au moins 8 caractères de longueur et ont minuscules et majuscules, ainsi que au moins un numéro.

<BR>
<A NAME="phones-is_webphone">
<BR>
<B>Définir en tant que Webphone -</B>  La définition de cette option à Y va tenter de charger un téléphone basé sur le Web lorsque l'agent se connecte à l'écran de leur agent. Par défaut est N. L'option Y_API_LAUNCH peut être utilisé avec l'API de l'agent pour lancer le webphone dans une Iframe ou fenêtre séparée.

<BR>
<A NAME="phones-webphone_dialpad">
<BR>
<B>Webphone Dialpad -</B>  Ce paramètre vous permet d'activer ou de désactiver le clavier pour cette webphone. Par défaut est Y pour activé. Toggle va permettre à l'utilisateur de visualiser et de cacher le clavier en cliquant sur un lien. Cette fonctionnalité n'est pas disponible sur toutes les versions webphone. TOGGLE_OFF par défaut pour ne pas montrer le clavier sur la première charge, mais permettra à l'utilisateur d'afficher le clavier en cliquant sur le lien clavier.

<BR>
<A NAME="phones-webphone_auto_answer">
<BR>
<B>Webphone Réponse automatique -</B>  Ce paramètre permet au téléphone Web pour être automatiquement configuré pour répondre aux appels qui entrent en en le mettant à Y, ou d'avoir anneau appels en le mettant à défaut N. Y est.

<BR>
<A NAME="phones-use_external_server_ip">
<BR>
<B>Utilisez IP du serveur externe -</B>  Si vous utilisez un téléphone Web, vous pouvez définir cette sur Y pour utiliser les serveurs IP externe pour vous inscrire à la place de l'adresse IP du serveur. Par défaut est vide.

<BR>
<A NAME="phones-status">
<BR>
<B>Statut -</B> C'est le statut du téléphone dans le système, ACTIVE et ADMIN autorisent les clients graphiques à fonctionner. ADMIN autorise l'acces à l'administration de ce site. Tous les autres statuts n'autorisent pas l'accès aux clients graphiques ou à l'administration.

<BR>
<A NAME="phones-active">
<BR>
<B>Compte actif -</B> Indique si le téléphone est actif pour le mettre dans la liste dans le client GUI.

<BR>
<A NAME="phones-phone_type">
<BR>
<B>Type de téléphone -</B> Seuelement pour les notes administratives.

<BR>
<A NAME="phones-fullname">
<BR>
<B>Nom complet -</B> Utilisé par le client GUI dans la liste des téléphones actifs.

<BR>
<A NAME="phones-company">
<BR>
<B>Compagnie -</B> Seulement pour les notes administratives.

<BR>
<A NAME="phones-email">
<BR>
<B>Téléphones Email - </B> L'adresse e-mail associée à ce téléphone d'entrée. Il est utilisé pour les paramètres de messagerie vocale.

<BR>
<A NAME="phones-delete_vm_after_email">
<B>Supprimer messagerie vocale après le courrier électronique -</B> Ce paramètre facultatif vous permet d-avoir les messages vocaux supprimés du système après avoir été envoyé par courriel Out. Par défaut est n.

<BR>
<A NAME="phones-voicemail_greeting">
<B>Message d'accueil -</B> Ce paramètre optionnel vous permet de définir un fichier audio message d'accueil du magasin audio. Défaut est vide.

<BR>
<A NAME="phones-voicemail_instructions">
<B>Boîte vocale Instructions -</B> Ce paramètre vous permet de définir si les instructions vocales vont jouer après le message d accueil lorsqu un appel sonne sur le poste de l agent et fois sur la messagerie vocale. Par défaut est Y.

<BR>
<A NAME="phones-picture">
<BR>
<B>Image -</B> Pas encore implémenté.

<BR>
<A NAME="phones-messages">
<BR>
<B>Nouveaux messages -</B> Nombre de nouveaux messages vocaux pour ce téléphone sur le serveur Asterisk.

<BR>
<A NAME="phones-old_messages">
<BR>
<B>Anciens messages -</B> Nombre d'anciens messages vocaux pour ce téléphone sur le serveur Asterisk.

<BR>
<A NAME="phones-protocol">
<BR>
<B>Protocole client -</B> Le protocole que le téléphone utilise pour se connecter au serveur Asterisk: SIP, IAX2, Zap. Il y a également EXTERNAL pour les numéros distants et les numérotation rapide que vous voulez lister comme téléphone.

<BR>
<A NAME="phones-local_gmt">
<BR>
<B>GMT local -</B> La différence de temps moyen de Greenwich, ou temps ZULU où se trouve le téléphone. NE PAS REGLER POUR HEURE AVANCÉE. Il est utilisé par la campagne pour afficher avec précision le temps et l heure système de la clientèle, ainsi que vous connecter avec précision quand les événements se produisent.

<BR>
<A NAME="phones-phone_ring_timeout">
<BR>
<B>Phone Ring Timeout -</B> Il s-agit de la quantité de temps, en secondes, que le téléphone sonne dans le plan de numérotation avant d-envoyer l-appel vers la messagerie vocale. Par défaut est de 60 secondes.

<BR>
<A NAME="phones-on_hook_agent">
<BR>
<B>Connexion Agent Sur-Crochet -</B> Cette option est utilisée uniquement pour les appels entrants vont à un agent connecté avec ce téléphone. Cette fonctionnalité sera appeler l'agent et ne pas envoyer le client à la session des agents jusqu'à ce que la ligne est répondu. Par défaut est N pour les handicapés.

<BR>
<A NAME="phones-ASTmgrUSERNAME">
<BR>
<B>Login du manager -</B> C'est le login que les clients GUI de ce téléphones vont utiliser pour accéder à la base de données dans laquelle sont stockées les données du serveur.

<BR>
<A NAME="phones-ASTmgrSECRET">
<BR>
<B>Mot de passe du manager -</B> C'est le mot de passe que les clients GUI de ce téléphone vont utiliser pour accéder à la base de données dans laquelle sont stockées les données du serveur.

<BR>
<A NAME="phones-login_user">
<BR>
<B>Agent utilisateur par défaut -</B> C est pour placer une valeur par défaut dans le champ utilisateur de l agent lorsque cet utilisateur de téléphone ouvre l application cliente. Laissez ce champ vide pour aucun utilisateur.

<BR>
<A NAME="phones-login_pass">
<BR>
<B>Agent de passe par défaut -</B> C est pour placer une valeur par défaut dans le champ de mot de passe de l agent lorsque cet utilisateur de téléphone ouvre l application cliente. Laissez vide pour ne pas passer.

<BR>
<A NAME="phones-login_campaign">
<BR>
<B>Campagne agent par défaut -</B> C est pour placer une valeur par défaut dans le champ de la campagne de l écran de l agent lorsque cet utilisateur de téléphone ouvre l application cliente. Laissez ce champ vide pour aucune campagne.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Exten de mise en attente -</B> C'est l'exten par défaut utilisé pour la mise en attente dans l'application client. Verifiez qu'il fonctionne avant de le changer.

<BR>
<A NAME="phones-conf_on_extension">
<BR>
<B>Exten pour mise en attente de la conférence -</B> C'est l'exten par défaut utilisé pour la mise en attente de la conférence dans l'appilcation client. Verifiez qu'il autre fonctionne avant de le changer.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Agent Parc Exten -</B> C est l extension du parking par défaut de l application client. Vérifiez que un autre fonctionne avant de modifier ce.

<BR>
<A NAME="phones-park_on_filename">
<BR>
<B>Agent Parc fichier -</B> C est le nom de fichier d extension du parc de défaut de l écran de l agent pour les applications client. Vérifiez que un autre fonctionne avant de changer cela. limité à 10 caractères.

<BR>
<A NAME="phones-monitor_prefix">
<BR>
<B>Prefix de Moniteur -</B> C'est le prefix du monitoring des Channels Zap automatiquement contenu dans l'appication astguiclient. Ne changez qu'en accord avec les enregistrement des extensions ZapBarge.

<BR>
<A NAME="phones-recording_exten">
<BR>
<B>Exten d'enregistrement -</B> C'est l'extension dans le dial plan pour l'enregistrement utilisé pour enregistrer les conférences. C'est habituellement stoppé au bout d'une heure. Verifiez le fichier extentions.conf avant tout changement.

<BR>
<A NAME="phones-voicemail_exten">
<BR>
<B>Exten principal pour VMAIL -</B> C'est l'extension du dial plan utilisé pour verifier la boîte vocale. Verifiez le fichier extensions.conf avant tout changement.

<BR>
<A NAME="phones-voicemail_dump_exten">
<BR>
<B>VMAIL Dump Exten -</B> C'est le préfix du dial plan utilisé pour lancer des appels directement vers la boite vocale de l'utilisateur depuis un un appelen cours dans l'application astGUIclient app. Avant tout changement verifiez le fichier extensions.conf.

<BR>
<A NAME="phones-voicemail_dump_exten_no_inst">
<BR>
<B>Vmail Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="phones-ext_context">
<BR>
<B>Extension du context -</B> C est dans ce contexte de plan de numérotation que l écran de l agent, utilise principalement. On suppose que tous les numéros composés par les applications clientes utilisent ce contexte il est donc une bonne idée de s assurer que cela est le contexte le plus large possible. vérifier avec le fichier extensions.conf avant de changer. défaut est par défaut.

<BR>
<A NAME="phones-phone_context">
<BR>
<B>Téléphone Contexte -</B> C est dans ce contexte de plan de numérotation que ce téléphone sera utiliser pour composer. Si vous utilisez un centre d appel et que vous ne voulez pas que vos agents d être en mesure de composer à l extérieur de l applicaiton de l écran de l agent par exemple, alors vous définissez ce champ sur un contexte de plan de numérotation qui n existe pas, quelque chose comme agent nodial. défaut est par défaut.

<BR>
<A NAME="phones-codecs_list">
<BR>
<B>Codecs admis -</B> Vous pouvez définir une liste délimitée par des virgules des codecs d'être défini comme les codecs par défaut pour ce téléphone. Options pour les codecs comprennent ulaw, alaw, gsm, g729, speex, G722, G723, G726, ILBC, ... Certains de ces codecs ne sont pas disponibles sur votre système, comme g729 ou G726. Si le champ est vide, alors les codecs par défaut du système ou de l'entrée de téléphone ci-dessus celle-ci sera utilisée pour les codecs admissibles. Par défaut est vide.

<BR>
<A NAME="phones-codecs_with_template">
<BR>
<B>Codecs admis avec la matrice-</B> Mettre cette option à 1 comprendra les codecs définis ci-dessus, même si un modèle de fichier de conf est utilisé. Par défaut est 0.

<BR>
<A NAME="phones-dtmf_send_extension">
<BR>
<B>Cannal d'envoi de DTMF -</B> C'est le channel utilisé pour envoyer les sons DTMF dans les conférences meetme depuis l'application cliente. Verifiez le fichier extentions.conf avant tout changement.

<BR>
<A NAME="phones-call_out_number_group">
<BR>
<B>Groupe d'appel sortant -<\/B> C'est le groupe de canal hors duquel seront placés les appels passés depuis ce téléphone. Il y a des routines dans l'application cliente qui l'utilisent. Pour les cannal Zap vous devriez utiliser quelque chose comme Zap/g2, pour les trunks IAX vous devriez utiliser un préfixe entier IAX comme IAX2/VICItet1:secret@10.10.10.15:4569. Vérifiez les trunks dans le fichier extensions.conf, il est courant que vous avez défini comme variable globale TRUNK au début du fichier.

<BR>
<A NAME="phones-client_browser">
<BR>
<B>Emplacement du Navigateur -</B> C'est le chemin absolu pour le navigateur Mozilla ou firefox, ceci n'est applicable que pour les clients UNIX/LINUX. Vérifiez en le lançant manuellement.

<BR>
<A NAME="phones-install_directory">
<BR>
<B>Répertoire d'installation -</B> Pas plus utilisé.

<BR>
<A NAME="phones-local_web_callerID_URL">
<BR>
<B>URL du CallerID -</B> C'est l'adresse web de la page utilisée pour faire une verification personnalisée du CallerId. L'adresse des test par defaut est : http://astguiclient.sf.net/test_callerid_output.php

<BR>
<A NAME="phones-agent_web_URL">
<BR>
<B>Agent URL par défaut -</B> Ceci est l adresse web de la page utilisée pour faire des requêtes Formulaire Webulaire d agent personnalisé. adresse de test par défaut est défini dans le schéma de base de données.

<BR>
<A NAME="phones-AGI_call_logging_enabled">
<BR>
<B>Log des appels -</B> This is set to true if the call_log step is in place in the extensions.conf file for all outbound and hang up 'h' extensions to log all calls. This should always be 1 because it is manditory for many of the system features to work properly.

<BR>
<A NAME="phones-user_switching_enabled">
<BR>
<B>Changement d'utilisateur -</B> Mettez à true pour autoriser un utilisateur à changer pour un autre compte utilisateur. NOTE: Si l'utilisateur commute, il peut initier l'enregistrement sur la nouvelle conversation téléphonique du nouvel utilisateur

<BR>
<A NAME="phones-conferencing_enabled">
<BR>
<B>En conférence -</B> Mettez à true pour autoriser un utilisateur a démarrer une conférence avec plus de 6 lignes externs.

<BR>
<A NAME="phones-admin_hangup_enabled">
<BR>
<B>Racrochage administateur-</B> Mettre à true pour autoriser l'utilisateur à racrocher n'importe quelle ligne à travers astGUIclient. C'est une bonne idée de l'activer pour les administarteurs.

<BR>
<A NAME="phones-admin_hijack_enabled">
<BR>
<B>Détournement adminisrateur -<\/B> Mettre à true pour autoriser l'utilisateur à pouvoir intercepter et rediriger vers son extention n'importe quelle ligne à travers Astguiclient. C'est une bonne idée de l'activer pour les administrateurs. Mais c'est aussi très utile pour les managers.

<BR>
<A NAME="phones-admin_monitor_enabled">
<BR>
<B>Moniteur adminisrateur -</B> Mettre à true pour autoriser l'utilisateur à intercepter et rediriger vers son extension certaines lignes à partir de astGUIclient. C'est une bonne idée de l'activer pour les adiministrateurs.  Mais c'est aussi très utile pour les managers et comme outil d'entrainement.

<BR>
<A NAME="phones-call_parking_enabled">
<BR>
<B>Parc d'appels -<\/B> Mettez à true pour autoriser l'utilisateur à parquer des appels dans astGUIclient pour être pris par n'importe quel utilisateur astGUIcient sur le système. Les appels sont mis en attente jusqu'à 30 minutes puis sont raccrochés. Habituellement activé pour tous.

<BR>
<A NAME="phones-updater_check_enabled">
<BR>
<B>Vérification de mise à jour -</B> Mettre à true pour afficher une popup avertissant que le temps de mise à jour n'a pas changé en 20 secondes. Utile pour les administrateurs.

<BR>
<A NAME="phones-AFLogging_enabled">
<BR>
<B>Log des AF -</B> Mettre à true pour logguer les actions de astGUIclient dans un fichier texte sur l'ordinateur de l'utilisateur.

<BR>
<A NAME="phones-QUEUE_ACTION_enabled">
<BR>
<B>Queue activée -</B> Affectez la valeur true à avoir des applications clientes utilisent le système Asterisk centrale file d attente. Nécessaire pour que le système fonctionne et recommandé pour tous les téléphones.

<BR>
<A NAME="phones-CallerID_popup_enabled">
<BR>
<B>Popup sur le CallerID -</B> Mettre à true pour autoriser, pour les numéros définis dans le fichier extensions.conf, à afficher des popup concernat le CallerID aux utilisateurs d'astGUIclient.

<BR>
<A NAME="phones-voicemail_button_enabled">
<BR>
<B>Bouton de boîte vocale -</B> Mettre à true pour afficher le bouton VOICEMAIL et le nombre de messages dans astGUIclient.

<BR>
<A NAME="phones-enable_fast_refresh">
<BR>
<B>Rafraichissement rapide -</B> Mettre à true pour activer un nouveau taux de rafraichissement de l'information dans astGUIclient. Par défaut ce taux est de 1000ms, 1 seconde. Si vous diminuez ce taux cela peu augmenter la charge système.

<BR>
<A NAME="phones-fast_refresh_rate">
<BR>
<B>Taux de rafraichissement rapide -</B> en millisecondes. Utiliser seulement si le rafraichissement rapide est activé. Par défaut le taux est de 1000ms , 1 seconde. La diminution de ce taux peux augmenter la charge système.

<BR>
<A NAME="phones-enable_persistant_mysql">
<BR>
<B>MySQL persistant -<\/B> Si activé, alors la connection à MYSQL sera maintenue au lieu d'être reconnectée chaque seconde. Utile si vous avez un taux de rafraichissement rapide. Cela va augmenter le nombre de connections au seveur MYSQL.

<BR>
<A NAME="phones-auto_dial_next_number">
<BR>
<B>Appel automatique du numéro suivant -</B> Si activé l écran de l agent compose le numéro suivant sur la liste automatiquement lors de la disposition d un appel à moins qu ils sélectionnés pour PAUSE AGENT COMPOSITION sur l écran de mise à disposition.

<BR>
<A NAME="phones-VDstop_rec_after_each_call">
<BR>
<B>Arrêter l'enregistrement après chaque appel -</B> Si activé l écran de l agent s arrête quelle que soit l enregistrement se passe après chaque appel a été dispositioned. Utile si vous faites beaucoup de l enregistrement ou si vous utilisez un formulaire Web pour déclencher l enregistrement. 

<BR>
<A NAME="phones-enable_sipsak_messages">
<BR>
<B>Enable SIPSAK Messages -</B> Si elle est activée, le serveur enverra des messages sur le téléphone SIP pour afficher sur l écran d affichage du téléphone lorsque vous êtes connecté à l interface Web de l agent. Fonctionnalité fonctionne uniquement avec les téléphones SIP et exige l application sipsak être installé sur le serveur Web. Défaut est 0.

<BR>
<A NAME="phones-DBX_server">
<BR>
<B>Serveur DBX -</B> Le serveur de la base de données MYSQL auquel cette utilisateur se connecte.

<BR>
<A NAME="phones-DBX_database">
<BR>
<B>Base de données DBX -</B> Base de données à laquelle cet utilisateur ce connecte. La valeur par défault est asterisk.

<BR>
<A NAME="phones-DBX_user">
<BR>
<B>Utilisateur DBX -</B> Login utilisateur de la base de données utilisé pour la connection. La valeur par défaut est cron.

<BR>
<A NAME="phones-DBX_pass">
<BR>
<B>Mot de passe DBX -</B> Mot de pass de la connetion MYSLQ. La valeur par défaut est 1234.

<BR>
<A NAME="phones-DBX_port">
<BR>
<B>Port DBX -</B> Port de la connection à la base de données. La valeur par défaut est 3306.

<BR>
<A NAME="phones-DBY_server">
<BR>
<B>Serveur DBY -</B> Le serveur de base de données auquel cet utilisateur ce connecte. Secondaireserver, pas utilisé actuellement.

<BR>
<A NAME="phones-DBY_database">
<BR>
<B>Base de données DBY -</B> La base de données MYSQL à laquelle cette utilisateur se connecte. La valeur par défaut est asterisk. Secondaireserver, pas utilisé actuellement.

<BR>
<A NAME="phones-DBY_user">
<BR>
<B>Utilisateur DBY -</B> Login utilisateur avec lequel cet utilisateur ce connecte à la base de données. La valeur par défaut est cron. Secondaireserver, pas utilisé actuellement.

<BR>
<A NAME="phones-DBY_pass">
<BR>
<B>Mot de passe DBY -</B> Mot de passe avec lequel cet utilisateur ce connecte à MYSQL. La valeur par defaut est 1234. Secondaireserver, pas utilisé actuellement.

<BR>
<A NAME="phones-DBY_port">
<BR>
<B>DBY port -</B> Port TCP de la connection MYSQL utilisée par cet utilisateur. La valeur par défaut est 3306. Secondaireserver, pas utilisé actuellement.

<BR>
<A NAME="phones-alias_id">
<BR>
<B>Alias ID - </B> L'ID de l'alias utilisé pour permettre les connexions de téléphone à équilibrage de charge. pas d'espaces ou autres caractères spéciaux autorisés. Doit être compris entre 2 et 20 caractères de longueur.

<BR>
<A NAME="phones-alias_name">
<BR>
<B>Alias Nom - </B> Le nom utilisé pour désigner un téléphone alias, doit être compris entre 2 et 50 caractères de longueur.

<BR>
<A NAME="phones-logins_list">
<BR>
<B>Téléphones Connexions List - </B> La virgule sépare la liste de téléphone de connexion utilisé lors d'un agent se connecte par téléphone pour équilibrer la charge de connexion. L'agent se trouve l'application serveur actif avec le moins d'agents et connecté à un appel à ce serveur à l'agent au moment de la connexion.

<BR>
<A NAME="phones-template_id">
<BR>
<B>ID modèle - </B> C'est le fichier de conf ID que ce modèle de téléphone utilisé pour la saisie des paramètres de son Asterisk. La valeur par défaut est - ABSENCE --.

<BR>
<A NAME="phones-conf_override">
<BR>
<B>Conf Override Settings -</B> If populated, and the ID modèle is set to --NONE-- then the contents of this field are used as the conf file entries for this phone. generate conf files for this phones server must be set to Y for this to work. This field should NOT contain the [extension] line, that will be automatically generated.

<BR>
<A NAME="phones-group_alias_id">
<BR>
<B>Alias Groupe ID -</B> L ID de l alias de groupe utilisés par les agents pour composer des appels de l interface de l agent avec différents ID Caller. pas d espaces ou d autres caractères spéciaux autorisés. Doit être compris entre 2 et 20 caractères.

<BR>
<A NAME="phones-group_alias_name">
<BR>
<B>Nom du groupe Alias - </B> Le nom utilisé pour décrire un groupe d'emprunt, doit être compris entre 2 et 50 caractères de longueur.

<BR>
<A NAME="phones-caller_id_number">
<BR>
<B>Numéro d'identification de l'appelant - </B> Le numéro d'identification de l'appelant utilisée dans ce groupe Alias. Chiffres doivent être.

<BR>
<A NAME="phones-caller_id_name">
<BR>
<B>Nom d'identification de l'appelant - </B> Le nom d'identification de l'appelant qui peut être envoyé à ce groupe Alias. Pour autant que nous sachions ce qui ne fera que travailler au Canada sur les circuits PRI et l'utilisation d'un tronc IAX boucle par Asterisk.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TABLE DES SERVEURS</FONT></B><BR><BR>
<A NAME="servers-server_id">
<BR>
<B>ID du Sevreur -</B> C'est ici que vous mettez le nom du serveur Asterisk, il n'est pas obligatoire de mettre le sous dommaine, juste un nom pour identifier le seveur pour les administrateurs.

<BR>
<A NAME="servers-server_description">
<BR>
<B>Descritpion du serveur -</B> Petite description du serveur asterisk.

<BR>
<A NAME="servers-server_ip">
<BR>
<B>Adresse IP du serveur -</B> Le champs dans lequel vous mettez l'adresse IP du serveur Asterisk.

<BR>
<A NAME="servers-active">
<BR>
<B>Actif -</B> Mettre si le serveur Asterisk est actif ou non.

<BR>
<A NAME="servers-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="servers-sysload">
<BR>
<B>Système de charge -</B> Ces deux statistiques montrent loadavg la fois d'un système 100 et le pourcentage d'utilisation CPU du serveur et est mis à jour chaque minute. Le loadavg devrait être en moyenne inférieure à 100 multiplié par le nombre de noyaux de CPU de votre système a, pour des performances optimales. Le pourcentage d'utilisation CPU devrait rester en dessous de 50 pour une performance optimale.

<BR>
<A NAME="servers-channels_total">
<BR>
<B>Live Chaînes -</B> Ce champ indique le nombre actuel d'Asterisk canaux qui sont en direct sur le système dès maintenant. Il est important de noter que le nombre de canaux Asterisk est habituellement beaucoup plus élevé que le nombre d'appels sur un système. Ce champ est mis à jour une fois chaque minute.

<BR>
<A NAME="servers-disk_usage">
<BR>
<B>Disk Usage -</B> Ce champ montre l'utilisation du disque pour chaque partition sur ce serveur. Ce champ est mis à jour une fois chaque minute.

<BR>
<A NAME="servers-system_uptime">
<BR>
<B>Système Uptime -</B> Ce champ affiche la disponibilité du système de ce serveur. Ce champ ne met à jour s il est configuré pour le faire par votre administrateur.

<BR>
<A NAME="servers-asterisk_version">
<BR>
<B>Version d'Asterisk -</B> Mettre la version d'asterisk que vous avez installé sur ce serveur. Examples: ' 1.2 ', ' 1.0.8 ', ' 1.0.7 ', 'CVS_HEAD', 'REALLY OLD', etc... Ceci est utilisé car les versions 1.0.8 et 1.0.9 ont une methode differente pour gérer les canaux Local/, un bug qui a été fixé dans le CVS v1.0, et qui nescessite d'être traité differement quand les canaux Local/ sont manipulés. Aussi, la version courante CVS_HEAD et l'arbre 1.2 utilisent des managers et des commandes différentes, qui doivent donc être traités différemment..

<BR>
<A NAME="servers-max_trunks">
<BR>
<B>Max Trunks -</B> Ce champ détermine le nombre maximum de lignes que le composeur automatique tentera d appeler sur ce serveur. Si vous voulez consacrer deux T1 PRI complets de numérotation sortante sur un serveur alors que vous réglez sur 46. Toutes ligne les appels entrants ou manuelles seront imputées sur ce total ainsi. Valeur par défaut est 96.

<BR>
<A NAME="servers-outbound_calls_per_second">
<BR>
<B>Max demande par seconde -</B> Ce paramètre détermine le nombre maximum d'appels qui peuvent être mis par la sortie d'auto-script d'appel sur ce serveur par seconde. Doit être de 1 à 100. La valeur par défaut est 20.

<BR>
<A NAME="servers-telnet_host">
<BR>
<B>Hôte telnet -</B> C'est l'adresse ou le nom du serveur Asterisk et c'est comme cela que les applications managers se connectent au sevreur quand elle sont lancées. Si les applications managers sont lancées sur le serveur Asterisk, alors la valeur par defaut 'localhost' fonctionne bien .

<BR>
<A NAME="servers-telnet_port">
<BR>
<B>Port telnet -</B> C'est le port de connection au manager du serveur Asterisk c'est par ceului-ci que les applications managers se connectent. La valeur par defaut '5038' fonctionne bien avec une installation standard.

<BR>
<A NAME="servers-ASTmgrUSERNAME">
<BR>
<B>Utilisateur du manager -</B> C'est l'utilisateur utilisé pour la connection au manager Asterisk. La valeur par défaut est 'cron'

<BR>
<A NAME="servers-ASTmgrSECRET">
<BR>
<B>Mot de passe du manager -</B> Le secret ou mot de passe utilisé pour ce connecter au manager Asterisk. La valeur par defaut est '1234'

<BR>
<A NAME="servers-ASTmgrUSERNAMEupdate">
<BR>
<B>Utilisateur du manager pour les mises à jour -</B> Le nom d'utilisateur ou le login utilisé pour se connecter au manager d'Asterisk, optimisé pour les scripts de mise à jour. La valeur par defaut est 'updatecron' et a le même secret que l'utilisateur générique.

<BR>
<A NAME="servers-ASTmgrUSERNAMElisten">
<BR>
<B>Utilisateur du manager pour l'écoute -</B> Le nom d'utilisateur ou le login utilisé pour se connecter au manager d'Asterisk,  optimisé pour les scripts qui écoutent la sortie seulement. La valeur par défaut est 'listencron' et a le même secret que l'utilisateur générique.

<BR>
<A NAME="servers-ASTmgrUSERNAMEsend">
<BR>
<B>Utilisateur du manager pour l'envoi -</B> Le nom d'utilisateur ou le login utilisé pour se connecter au manager d'Asterisk, optimisé pour les scripts qui envoient des actions au manager.  La valeur par défaut est 'sendcron' et a le même secret que l'utilisateur générique.

<BR>
<A NAME="servers-conf_secret">
<BR>
<B>Conf dossier secret -</B> C-est le secret ou mot de passe, pour le serveur dans l-auto-généré IAX fichier de conf pour ce serveur sur d-autres serveurs. La limite est de 20 caractères alphanumériques tiret de soulignement et acceptée. Par défaut est test. Un secret conf forte du fichier doit être au moins 8 caractères de longueur et ont minuscules et majuscules, ainsi que au moins un numéro.

<BR>
<A NAME="servers-local_gmt">
<BR>
<B>Décalage GMT du serveur -</B>La difference en heure par rapport à l'heure GMT non ajustée pour Les Daylight-Savings-Time du serveur. Par defaut '-5'

<BR>
<A NAME="servers-voicemail_dump_exten">
<BR>
<B>Exten VMail Dump -</B> Le prefix de l'extention utilisé sur ce serveur pour lancer des appels directement depuis agc vers une boite vocale spécifique. La valeur par defaut est '85026666666666'

<BR>
<A NAME="servers-voicemail_dump_exten_no_inst">
<BR>
<B>Vmail Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="servers-answer_transfer_agent">
<BR>
<B>numérotation automatique prolongation -</B> L extension par défaut si aucun n est présent dans la campagne pour envoyer des appels à la numérotation automatique. Défaut est '8365'

<BR>
<A NAME="servers-ext_context">
<BR>
<B>Contexte par défaut -</B> Le contexte par defaut utilisé par les scripts qui opèrent sur ce serveur. La valeur par défaut est 'default'

<BR>
<A NAME="servers-sys_perf_log">
<BR>
<B>Performances du système -</B> Mettre cette option à Y permet de loger les statistiques des performances du système, incluant la charge du système, les processus et les canaux Asterisk utilisés. La valeur par défaut est N.

<BR>
<A NAME="servers-vd_server_logs">
<BR>
<B>Logs du serveur -</B> Mettre cette option à Y activer la journalisation de tous les scripts du système liés à leurs fichiers journaux de texte. Mettre cette option à N sera arrêter d écrire des journaux de fichiers pour ces processus, aussi l enregistrement de l écran de astérisque sera désactivée si ce paramètre est réglé à N quand Asterisk est démarré. Par défaut est Y.

<BR>
<A NAME="servers-agi_output">
<BR>
<B>Sortie des AGI -</B> Mettre cette option à NONE désactive sortie de tous les systèmes liés AGI scripts. Mettre ce paramètre à STDERR enverra la sortie de l AGI à la CLI Asterisk. La définition de cette FILE enverra la sortie vers un fichier dans le répertoire des journaux. Mettre ce paramètre à la fois une volonté d envoyer la sortie à la fois de la CLI Asterisk et un fichier journal. Défaut est FILE.

<BR>
<A NAME="servers-balance_active">
<BR>
<B>Solde de numérotation -</B> Définir ce champ à Y permettra au serveur de passer des appels d équilibre pour les campagnes dans le système afin que le niveau de numérotation défini peut être respectée, même si il n y a pas les agents affectés à cette campagne sur ce serveur. Défaut est N.

<BR>
<A NAME="servers-balance_rank">
<BR>
<B>Solde Rang -</B> Ce champ vous permet de définir l-ordre dans lequel ce serveur doit être utilisé pour la composition à l-équilibre, si vous appelez l-équilibre est activé. Le serveur avec le plus haut rang sera utilisé en premier dans le placement de la balance remplir appels. Par défaut est 0.

<BR>
<A NAME="servers-balance_trunks_offlimits">
<BR>
<B>Solde Offlimits -</B> Ce paramètre définit le nombre de lignes à ne pas autoriser les processus solde de numérotation à utiliser. Par exemple, si vous avez 40 troncs max et équilibre offlimits est réglé sur 10, vous ne serez en mesure d utiliser les 30 lignes principales pour l équilibre numérotation. Défaut est 0 .

<BR>
<A NAME="servers-recording_web_link">
<BR>
<B>Enregistrement Web Link - </B> Ce paramètre permet d'outrepasser les paramètres par défaut de l'affichage de l'enregistrement de lien dans l'admin des pages web. La valeur par défaut est SERVER_IP.

<BR>
<A NAME="servers-alt_server_ip">
<BR>
<B>Autre enregistrement IP du serveur - </B> Ce paramètre est l'endroit où vous pouvez mettre une IP de serveur de nom de machine ou d'autres qui peuvent être utilisés à la place de la server_ip dans les liens d'enregistrements au sein de l'administration des pages Web. La valeur par défaut est vide.

<BR>
<A NAME="servers-external_server_ip">
<BR>
<B>IP du serveur externe -</B> Ce paramètre est l'endroit où vous pouvez mettre une adresse IP du serveur ou nom de la machine qui peut être utilisé à la place de l'server_ip lorsque vous utilisez un webphone dans l'interface agent. Pour que cela fonctionne vous devez également avoir l'entrée téléphones configuré pour utiliser l'adresse IP du serveur externe. Par défaut est vide.

<BR>
<A NAME="servers-active_twin_server_ip">
<BR>
<B>Actif IP du serveur double -</B> Certains systèmes nécessitent la mise en place des serveurs de téléphonie par paires. Ce paramètre est l endroit où vous pouvez mettre l adresse IP du serveur d un autre serveur que le serveur est jumelée avec. Défaut est vide pour les handicapés.

<BR>
<A NAME="servers-active_asterisk_server">
<BR>
<B>Active Asterisk Server -</B> Si Asterisk ne fonctionne pas sur ce serveur, ou si les processus de composition ne devraient pas utiliser ce serveur, ou si seulement utilisent ce serveur pour d autres scripts comme le script trémie de chargement que vous voudriez mettre ce à N. défaut est Y.

<BR>
<A NAME="servers-auto_restart_asterisk">
<BR>
<B>Redémarrage automatique Asterisk -</B> Si Asterisk est en cours d exécution sur ce serveur et que vous voulez le système pour s assurer qu il sera redémarré dans le cas où il se bloque, vous pourriez envisager d activer ce paramètre. S il est activé, le système vérifie toutes les minutes pour voir si Asterisk est en marche, et si elle n est pas elle va tenter de le redémarrer. Ce processus ne sera pas exécutée dans les 5 premières minutes après un système a été en. Défaut est N.

<BR>
<A NAME="servers-asterisk_temp_no_restart">
<BR>
<B>Temp Pas-Redémarrer Asterisk -</B> Si redémarrage automatique Asterisk est activée sur ce serveur, tournant sur ce paramètre permet d éviter le processus de redémarrage automatique de s exécuter jusqu à ce que le serveur est redémarré. Défaut est N.

<BR>
<A NAME="servers-active_agent_login_server">
<BR>
<B>Active Server Agent -</B> Mettre cette option à N empêche les agents d être en mesure de se connecter à ce serveur à travers l écran de l agent. Ceci est très utile lorsque vous utilisez une configuration équilibrée connexion téléphone charge. Par défaut est Y.

<BR>
<A NAME="servers-generate_conf">
<BR>
<B>Generate conf files -</B> Si vous souhaitez que le système d auto-générer des fichiers de conf astérisque sur la base des entrées de téléphones, les entrées de support et l équilibrage de charge la configuration du système, puis réglez-le sur Y. défaut est Y.

<BR>
<A NAME="servers-rebuild_conf_files">
<BR>
<B>Reconstruire les fichiers conf - </B> Si vous voulez forcer une reconstruction de la conf Asterisk fichiers ou si l'une des entrées de porte ou les téléphones ont changé, alors ceci doit être réglé sur Y. Après la conf des fichiers ont été générés et Asterisk a été rechargée alors ce sera changée pour N. La valeur par défaut est Y.

<BR>
<A NAME="servers-rebuild_music_on_hold">
<BR>
<B>Reconstruire la musique d-attente -</B> Si vous voulez forcer une reconstruction de la musique sur fichiers contiennent ou si la musique sur les entrées de détenir ou des entrées de serveur ont changé, alors ceci doit être réglé sur Y. Après la musique d-attente les fichiers ont été synchronisés et rechargés alors ce sera changée à N. défaut est Y..

<BR>
<A NAME="servers-sounds_update">
<BR>
<B>Mise à jour de sons -</B> Si vous voulez forcer une vérification des fichiers sonores sur ce serveur, et la banque centrale audio est activée d-un paramètre système, ce champ permettra aux sons de mise à jour à courir au début de la prochaine minute. Chaque fois qu-un fichier audio est téléchargé à partir de l-interface web de ce contrat est automatiquement réglé sur Y pour tous les serveurs qui ont activement Asterisk. Par défaut est n.

<BR>
<A NAME="servers-recording_limit">
<BR>
<B>Limite d enregistrement -</B> Ce champ est où vous définissez le nombre maximal de minutes que l enregistrement d un appel lancé par le système peut être. Défaut est de 60 minutes.

<BR>
<A NAME="servers-carrier_logging_active">
<BR>
<B>Carrier Logging Actif-</B> Ce paramètre vous permet d-enregistrer l-ensemble des codes de retour de raccrochage pour tout composant la liste des appels sortants que vous passez. Par défaut est N.

<BR>
<A NAME="servers-custom_dialplan_entry">
<BR>
<B>Entrée personnalisée Dialplan -</B> Ce champ vous permet d'entrer dans tous les éléments plan de numérotation que vous souhaitez pour le serveur, les lignes seront ajoutées au contexte par défaut.






<BR><BR><BR><BR>

<B><FONT SIZE=3>conf_templates TABLE</FONT></B><BR><BR>
<A NAME="conf_templates-template_id">
<BR>
<B>ID modèle - </B> Ce champ doit être d'au moins 2 caractères et pas plus de 15 caractères, sans espaces. C'est l'identifiant qui sera utilisé pour identifier le modèle de configuration à travers le système.

<BR>
<A NAME="conf_templates-template_name">
<BR>
<B>Nom du modèle - </B> C'est le nom descriptif du modèle de fichier de configuration d'entrée.

<BR>
<A NAME="conf_templates-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="conf_templates-template_contents">
<BR>
<B>Template Sommaire - </B> Ce domaine est l'endroit où vous pouvez entrer dans les paramètres spécifiques pour être utilisés par tous les téléphones et / ou les transporteurs qui sont mis à utiliser ce modèle de conf. Les champs qui ne doivent PAS être inclus dans cette zone sont: le secret, accountcode, compte, nom d'utilisateur et boîte aux lettres.





<BR><BR><BR><BR>

<B><FONT SIZE=3>server_carriers TABLE</FONT></B><BR><BR>
<A NAME="server_carriers-carrier_id">
<BR>
<B>Carrier ID - </B> Ce champ doit être d'au moins 2 caractères et pas plus de 15 caractères, sans espaces. C'est l'identifiant qui sera utilisé pour identifier le transporteur pour cette entrée spécifique dans l'ensemble du système.

<BR>
<A NAME="server_carriers-carrier_name">
<BR>
<B>Nom du transporteur - </B> C'est le nom descriptif de la porte d'entrée.

<BR>
<A NAME="server_carriers-carrier_description">
<BR>
<B>Carrier Description -</B> Ceci est mis dans les commentaires des fichiers de conf asterisk-dessus du plan de numérotation et des entrées de compte. Maximum 255 caractères.

<BR>
<A NAME="server_carriers-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="server_carriers-registration_string">
<BR>
<B>Inscription String - </B> Ce domaine est l'endroit où vous pouvez entrer dans la chaîne exacte nécessaire dans le IAX ou SIP pour enregistrer le fichier de configuration pour le fournisseur. Facultatif mais fortement recommandé si votre opérateur permet l'enregistrement.

<BR>
<A NAME="server_carriers-template_id">
<BR>
<B>ID modèle - </B> Ce champ facultatif permet de choisir un modèle de fichier de conf de ce transporteur entrée.

<BR>
<A NAME="server_carriers-account_entry">
<BR>
<B>Account Entry -</B> Ce champ est utilisé si vous n avez pas sélectionné un modèle à utiliser, et c est là que vous pouvez entrer dans les paramètres spécifiques de compte à utiliser pour ce transporteur. Si vous allez prendre en appels entrants à partir de ce support tronc, vous pouvez définir le contexte = trunkinbound dans ce domaine de sorte que vous pouvez utiliser le processus DID manipulation au sein du système.

<BR>
<A NAME="server_carriers-protocol">
<BR>
<B>Protocole - </B> Ce domaine vous permet de définir le protocole à utiliser pour le transporteur d'entrée. Actuellement, seuls SIP et IAX sont soutenus.

<BR>
<A NAME="server_carriers-globals_string">
<BR>
<B>Globals String - </B> Ce champ facultatif permet de définir une variable globale à utiliser pour le transporteur de la dialplan.

<BR>
<A NAME="server_carriers-dialplan_entry">
<BR>
<B>Entrée Dialplan - </B> Ce champ facultatif permet de définir un ensemble de dialplan entrées à utiliser pour ce transporteur.

<BR>
<A NAME="server_carriers-server_ip">
<BR>
<B>IP du serveur - </B> Il s'agit du serveur que ce support est associé à enregistrer.

<BR>
<A NAME="server_carriers-active">
<BR>
<B>Active - </B> Cela définit si le transporteur sera inclus dans l'auto-générés ou non les fichiers conf.





<BR><BR><BR><BR>

<B><FONT SIZE=3>TABLE DES CONFERENCES</FONT></B><BR><BR>
<A NAME="conferences-conf_exten">
<BR>
<B>Numéro de la conférence -</B> Ce champ est où vous mettez la conférence Numéro de plan de numérotation de meetme. Il est également recommandé que le nombre de meetme dans meetme.conf correspond à ce nombre pour chaque entrée. C est pour les conférences sur l écran de l utilisateur astGUIclient et est utilisé pour la fonctionnalité congé-3 voies d appel dans le système.

<BR>
<A NAME="conferences-server_ip">
<BR>
<B>IP du serveur -</B> C'est le menu dans lequel vous choisissez le serveur Asterisk sur lequel sera cette conférence.




<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>SERVER_MallesTABLE</FONT></B><BR><BR>
	<A NAME="server_trunks">
	<BR>
	<B>Trunks Server vous permet de restreindre les lignes sortantes qui sont utilisés sur ce serveur pour la campagne numérotation sur une base par campagne. Vous avez la possibilité de réserver un certain nombre de lignes pour être utilisé par une seule campagne ainsi que de permettre cette campagne afin de fonctionner sur ses lignes réservées en quelque lignes restent ouvertes, tant au nombre total de lignes utilisées par le système sur ce serveur est moins que le réglage Trunks Max. Ne pas avoir l un de ces dossiers permettra à la campagne qui compose la première ligne d avoir autant de lignes qu il peut obtenir sous le réglage Trunks Max.</B>
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SYSTEM_SETTINGS TABLE</FONT></B><BR><BR>
<A NAME="settings-use_non_latin">
<BR>
<B>Utiliser Non-Latin -</B> Cette option vous permet d'utiliser les caractères UTF8 pour l'affichage web des scripts, et de ne pas utiliser des filtres d'expressions régulières latin-character-family ou de formater l'affichage. La valeur par défaut est 0.

<BR>
<A NAME="settings-webroot_writable">
<BR>
<B>Webroot éditable -</B> Cette option vous permet de définir si les fichiers temporaires et les fichiers d'authentification doivent être placés dans le webroot sur le serveur web. La valeur par défaut est 1.

<BR>
<A NAME="settings-agent_disable">
<BR>
<B>Agent désactiver l affichage -</B> Ce champ est utilisé pour sélectionner le moment de montrer un avis de l agent lorsque leur session a été désactivée par le système, une action de gestion ou par une mesure externe. Le réglage de NOT_ACTIVE va désactiver le message sur l écran des agents. Le réglage de LIVE_AGENT n affiche que le message désactivé lorsque le dossier des agents a été supprimé, comme lors d une déconnexion de la force ou de déconnexion d urgence. Par défaut est ALL.

<BR>
<A NAME="settings-frozen_server_call_clear">
<BR>
<B>Appels congelés claires -</B> Cette option peut permettre à la capacité de la page Rapports généraux et le script de AST_timecheck.pl option pour effacer les entrées de auto_calls pour un serveur congelés afin qu ils n affectent pas le routage d appel. Défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-allow_sipsak_messages">
<BR>
<B>Allow SIPSAK Messages -</B> Si mis à 1, ce qui permettra la création de la table des téléphones sipsak de travailler si le téléphone est réglé sur le protocole SIP. Le serveur enverra des messages sur le téléphone SIP pour afficher sur l écran du téléphone lorsque vous êtes connecté au système. Cette fonctionnalité fonctionne uniquement avec les téléphones SIP et exige l application sipsak être installé sur le serveur Web que l agent est connecté. Défaut est 0. 

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agent API Actif- </B> Si définie à 1, cela permet à l'agent d'interface API pour fonctionner. La valeur par défaut est 0. 

<BR>
<A NAME="settings-admin_home_url">
<BR>
<B>URL de l'acceuil de l'administration -</B> C'est l'URL ou l'adresse du site web ou vous voulez aller quand vous cliquez sur le lien ACCUEIL en haut de la page admin.php.

<BR>
<A NAME="settings-admin_modify_refresh">
<BR>
<B>Administrateur Modifier Auto-Refresh -</B> C'est l'intervalle de rafraîchissement en secondes des écrans modifier dans ce interface d'administration. Un réglage à 0 sera le désactiver, il la mise en-dessous de 5 sera la plupart du temps faire des écrans de forçage inutilisables car ils se rafraîchira trop vite pour changer les champs. Cette option est utile dans les situations où plus d'un gestionnaire est le contrôle des paramètres sur une campagne active ou en groupe afin que les paramètres sont rafraîchies fréquemment. Par défaut est 0.

<BR>
<A NAME="settings-nocache_admin">
<BR>
<B>Admin No-Cache -</B> Si définie à 1 permet de régler toutes les pages d'administration du navigateur internet de no-cache, de sorte que chaque écran doit être rechargé à chaque fois qu'il est considéré, même si le clic de retour sur le navigateur. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-enable_agc_xfer_log">
<BR>
<B>Permettez le fichier journal de transfert d'agent - cette optionnotera à un fichier journal des textes sur le webserver chaque foisqu'un appel est transféré à un agent. Le défaut est 0,neutralisé.

<BR>
<A NAME="settings-enable_agc_dispo_log">
<BR>
<B>Activer Logfile Disposition Agent -</B> Cette option se connecter à un fichier journal texte sur le serveur web à chaque fois qu'un appel est rejetées par un agent. Par défaut est 0, désactivé.

<BR>
<A NAME="settings-timeclock_end_of_day">
<BR>
<B>Horloge en temps Fin de la journée - </B> Ce paramètre définit lorsque tous les utilisateurs sont automatiquement déconnecté du système timeclock. Fonctionne uniquement une fois par jour. ne doit être que 4 chiffres et 2 chiffres heures à 2 chiffres dans les minutes de 24 heures. La valeur par défaut est 0000.

<BR>
<A NAME="settings-default_local_gmt">
<BR>
<B>Par défaut locale GMT -</B> Ce paramètre définit ce qui sera utilisé par défaut lors de nouveaux téléphones et les serveurs sont ajoutés à l'aide de cette interface web d'administration. Par défaut est de -5.

<BR>
<A NAME="settings-default_voicemail_timezone">
<BR>
<B>Zone messagerie vocale par défaut -</B> Ce paramètre définit quelle zone sera utilisé par défaut lors de nouveaux téléphones et boîtes vocales sont créées. La liste des zones disponibles est directement pris à partir du fichier voicemail.conf. Par défaut est l'Est.

<BR>
<A NAME="settings-agents_calls_reset">
<BR>
<B>Agents Appels Réinitialiser -</B> Ce paramètre définit si l'utilisateur connecté et les agents actifs des appels téléphoniques des dossiers doivent être remis à zéro à la fin de la journée minuterie. Par défaut est 1 pour activé.

<BR>
<A NAME="settings-timeclock_last_reset_date">
<BR>
<B>Dernière timeclock Auto Déconnection - </B> Ce champ affiche la date de la dernière auto-logout.

<BR>
<A NAME="settings-vdc_header_date_format">
<BR>
<B>Agent Header Screen Format Date -</B> Ce menu vous permet de choisir le format de la date et l heure qui apparaît en haut de l écran de l agent. Les options de ce paramètre sont: défaut est MS_DASH_24HR<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Par défaut avec le format de la date année mois jour suivie par 24 heures,<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - Etats-Unis avec le format de la date mois jour année suivie par 24 heures,<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Européen de format de date avec jour mois année suivie par 24 heures,<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Texte de format de date avec abrégée mois jour suivie par 24 heures,<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Par défaut avec le format de la date année mois jour , suivies de 12 heures<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - Etats-Unis avec le format de la date mois jour année , suivies de 12 heures<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Européen de format de date avec jour mois année , suivies de 12 heures<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Texte de format de date avec abrégée mois jour , suivies de 12 heures<BR>

<BR>
<A NAME="settings-vdc_customer_date_format">
<BR>
<B>Agent client Screen Format Date -</B> Ce menu vous permet de choisir le format de la date et de l heure à la clientèle qui se présente à la tête de la section de l information à la clientèle de l écran de l agent. Les options de ce paramètre sont: défaut est AL_TEXT_AMPM<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Par défaut avec le format de la date année mois jour suivie par 24 heures,<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - Etats-Unis avec le format de la date mois jour année suivie par 24 heures,<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Européen de format de date avec jour mois année suivie par 24 heures,<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Texte de format de date avec abrégée mois jour suivie par 24 heures,<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Par défaut avec le format de la date année mois jour , suivies de 12 heures<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - Etats-Unis avec le format de la date mois jour année , suivies de 12 heures<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Européen de format de date avec jour mois année , suivies de 12 heures<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Texte de format de date avec abrégée mois jour , suivies de 12 heures<BR>

<BR>
<A NAME="settings-vdc_header_phone_format">
<BR>
<B>Agent Client Téléphone Screen Format -</B> Ce menu vous permet de choisir le format du numéro de téléphone du client qui apparaît dans la section d état de l écran de l agent. Les options de ce paramètre sont: défaut est US_PARN<BR>
US_DASH 000-000-0000 - USA tableau de bord séparés numéro de téléphone<BR>
US_PARN (000)000-0000 - USA Numéro de tableau de bord séparés avec le code régional entre parenthèses<BR>
MS_NODS 0000000000 - Pas de formatage<BR>
UK_DASH 00 0000-0000 - UK tableau de bord séparés numéro de téléphone avec l'espace après le code de ville<BR>
AU_SPAC 000 000 000 - Australia espace séparé numéro de téléphone<BR>
IT_DASH 0000-000-000 - Italy tableau de bord séparés numéro de téléphone<BR>
FR_SPAC 00 00 00 00 00 - France espace séparé numéro de téléphone<BR>

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agent interface API Access Actif-</B> This option allows you to enable or disable the agent interface API. Default is 0.

<BR>
<A NAME="settings-agentonly_callback_campaign_lock">
<BR>
<B>Agent Only Lock Campagne de rappel -</B> Cette option définit si rappels AGENTONLY sont enfermés à la campagne que l-agent a initialement créé sous. Mettre ce paramètre à 1 signifie que l-agent ne peut les composer dans la campagne, ils ont été définies sous, 0 signifie que l-agent peut y accéder quel que campagne, ils sont connecté. Par défaut est 1.

<BR>
<A NAME="settings-sounds_central_control_active">
<BR>
<B>Central Sound Control Actif-</B> Cette option définit si le système de synchronisation sonore est actif sur tous les serveurs. Par défaut est 0 pour inactif.

<BR>
<A NAME="settings-sounds_web_server">
<BR>
<B>Sounds Web Server -</B> C-est le nom du serveur ou l-adresse IP du serveur Web qui sera la manipulation des fichiers sonores sur ce système, cela doit correspondre le nom du serveur ou adresse IP de la machine que vous essayez d-accéder à la page Web audio_store.php sur ou ça ne marchera pas . Par défaut est 127.0.0.1.

<BR>
<A NAME="settings-sounds_web_directory">
<BR>
<B>Sounds Web Directory -</B> Cette auto-générés nom du répertoire est créé au hasard par le système comme étant le lieu que le magasin audio seront conservés. Tous les fichiers audio se trouver dans ce répertoire.

<BR>
<A NAME="settings-admin_web_directory">
<BR>
<B>Annuaire Web Admin -</B> Il s agit du répertoire Web que votre contenu web administation, comme admin.php, sont po Pour déterminer votre répertoire web d administration, il est tout ce qui est entre le nom de domaine et le admin.php dans l URL de cette page sans le début et de fin des barres obliques.

<BR>
<A NAME="settings-active_voicemail_server">
<BR>
<B>Active Server Boîte vocale -</B> Dans les systèmes multi-serveur, c-est le serveur qui va gérer toutes les boîtes de messagerie vocale. Ce serveur est également lorsque le dial-in générée invites seront téléchargées à partir, le 8168 enregistrements.

<BR>
<A NAME="settings-allow_voicemail_greeting">
<BR>
<B>La messagerie vocale Sélecteur de voeux -</B> Si ce paramètre est activé, il vous permettra de choisir un fichier audio à partir du magasin audio pour être joué que le message d'accueil d'une boîte vocale spécifique. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-outbound_autodial_active">
<BR>
<B>Outbound Auto-Dial Actif-</B> Cette option vous permet d activer ou de désactiver sortant numérotation automatique dans le système, la mise en ce domaine à 0 va supprimer les listes et les filtres sections et de nombreux champs des écrans campagne de modification. Manuel entrée numérotation sera toujours autorisée à partir de l écran de l agent, mais aucune liste de numérotation sera possible. Défaut est 1 pour actif.

<BR>
<A NAME="settings-disable_auto_dial">
<BR>
<B>Désactiver Auto-Dial -</B> Cette option n est modifiable que par un administrateur système. Il ne supprime pas toutes les options de l interface Web de gestion, mais il permettra d éviter toute numérotation automatique des pistes de passe sur le système. Seulement Manuel appels Dial sortants déclenchés directement par les agents vont fonctionner si cette option est activée. Défaut est 0 pour inactif.

<BR>
<A NAME="settings-auto_dial_limit">
<BR>
<B>Ratio Dial Limit -</B> C-est la limite maximale du niveau automatique ligne dans l-écran de campagne.

<BR>
<A NAME="settings-outbound_calls_per_second">
<BR>
<B>Max REMPLISSAGE demande par seconde -</B> Ce paramètre détermine le nombre maximum d'appels qui peuvent être placés par les auto-FILL sortie d'auto-script de numérotation pour tous les serveurs, par seconde. Doit être de 1 à 200. La valeur par défaut est 40.

<BR>
<A NAME="settings-allow_custom_dialplan">
<BR>
<B>Permettant de personnaliser le plan d-appel Entrées -</B> Cette option vous permet d'entrer des lignes personnalisées plan de numérotation dans les menus d'appels, serveurs et les paramètres système. Par défaut est 0 pour inactif.

<BR>
<A NAME="settings-pllb_grouping_limit">
<BR>
<B>Limite Groupement PLLB -</B> Connexion Charge Téléphone Groupement Limite d'équilibrage. Si Groupement PLLB est réglé sur CASCADE au niveau de la campagne, alors ce paramètre détermine le nombre d'agents acceptable sur chaque serveur dans toutes les campagnes. Par défaut est 100.

<BR>
<A NAME="settings-generate_cross_server_exten">
<BR>
<B>Générer les extensions de téléphone de la Croix-Server -</B> Cette option si elle est définie à 1, générer des entrées pour chaque plan de numérotation de téléphone sur un système multi-serveur. Par défaut est 0 pour inactif.

<BR>
<A NAME="settings-user_territories_active">
<BR>
<B>Territoires utilisateur Actif-</B> Ce paramètre vous permet d activer les utilisateurs Territoires paramètres de l écran de modification de l utilisateur. Cette fonctionnalité a été ajoutée afin de permettre une plus grande intégration avec une installation personnalisée Vtiger mais peut avoir des applications dans le système par lui-même ainsi. Défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-enable_second_webform">
<BR>
<B>Activer Webform Deuxième -</B> This setting allows you to have a second web form for campaigns and in-groups in the agent interface. Default is 0 for disabled.

<BR>
<A NAME="settings-enable_tts_integration">
<BR>
<B>Activer TTS Intégration -</B> Ce paramètre vous permet d-activer l-intégration Text To Speech avec Cepstral. Ce n-est actuellement disponible pour les campagnes Enquête sortants type. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-callcard_enabled">
<BR>
<B>Activer CallCard -</B> Ce paramètre permet les fonctions de CallCard pour permettre aux appelants d utiliser les numéros de broches et card_ids qui ont un solde de minutes et les soldes peuvent avoir un agent temps de conversation sur les appels des clients au-groupes déduits. Défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-custom_fields_enabled">
<BR>
<B>Activer les champs de liste personnalisés -</B> Ce paramètre permet la liste personnalisée fonction des champs qui permet de champs de données personnalisés à définir dans l'interface Web d'administration sur une base par liste et alors ces champs disponibles dans un onglet formulaire à l'agent dans l'interface web agent. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-test_campaign_calls">
<BR>
<B>Permettre des appels de test de la campagne -</B> Ce paramètre permet la possibilité d'entrer un code de téléphone et numéro de téléphone dans les champs au bas de l'écran Détail de la campagne et placer un appel téléphonique à ce numéro comme s'il s'agissait d'une avance en cours d'auto-composé dans le système. Le numéro de téléphone sera stocké en tant que nouveau chef de file dans la liste ID cadran liste manuelle. La campagne doit être activé pour que cette fonctionnalité soit activée, et il est recommandé que les listes affectés à la campagne tous être mis à l'état inactif. Le préfixe de numérotation, délai d'attente de numérotation et toutes les fonctions de numérotation autres connexes, à l'exception de la DNC et des options d'achat de temps, aura une incidence sur la numérotation du nombre d'essai. L'appel téléphonique sera placé sur le serveur sélectionné comme serveur de messagerie vocale dans les paramètres du système. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-expanded_list_stats">
<BR>
<B>Activer Statistiques liste élargie -</B> Ce réglage permet à deux colonnes supplémentaires pour être affichés sur la plupart des tables de la Liste des dégradation de situation sur la liste et la modification des pages de modification de la campagne. La pénétration est définie comme étant la pour cent des conducteurs qui sont au niveau ou au-dessus de la limite de comptage des appels et la campagne ou le statut est marqué comme complété. Par défaut est 1 pour activé.

<BR>
<A NAME="settings-country_code_list_stats">
<BR>
<B>Liste des codes de pays Statistiques -</B> Ce paramètre si activé affichera un code de pays résumé de panne sur la liste modifier l écran. Défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-enhanced_disconnect_logging">
<BR>
<B>Enhanced Disconnect Logging -</B> Ce paramètre active la journalisation des appels qui obtiennent un signal d'encombrement avec un code de cause de 1, 19, 21, 34 ou 38. Habituellement, nous ne recommandons pas l'activation de cette aux Etats-Unis. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-campaign_cid_areacodes_enabled">
<BR>
<B>Activer le code zonal de la campagne CID -</B> Ce paramètre permet la possibilité de définir spécifiques sortants numéros callerid à être utilisés par campagne. Par défaut est 1 pour activé.

<BR>
<A NAME="settings-did_ra_extensions_enabled">
<BR>
<B>Activer télécommande remplace extension de l'agent -</B> Ce réglage permet d'avoir DID remplacements de vulgarisation pour l'agent à distance acheminés par le biais de demande-groupes. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="contact_information">
<A NAME="settings-contacts_enabled">
<BR>
<B>Contacts Enabled -</B> Ce paramètre permet les contacts dans la sous-section Admin qui permet à un gestionnaire d'ajouter, modifier ou supprimer des contacts dans le système qui peuvent être utilisés dans le cadre d'un transfert personnalisé dans une campagne où un agent peut rechercher des contacts par Prénom Nom ou au bureau numéro, puis sélectionnez l'une des nombreux numéros associés à ce contact. Cette fonction est souvent utilisée par les opérateurs ou dans les fonctions de standard où l'utilisateur aurait besoin de transférer un appel vers un téléphone non-agent. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-call_menu_qualify_enabled">
<BR>
<B>Menu Appel qualifier Activé -</B> Ce paramètre active l'option dans les menus d'appel pour mettre une qualification SQL sur les personnes qui entendent que le menu d'appel. Pour plus d'informations sur la façon dont cette fonctionnalité fonctionne, consultez l'aide pour les menus d'appel. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-level_8_disable_add">
<BR>
<B>Niveau 8 désactiver des -</B> Ce paramètre si elle est activée empêchera tout utilisateur de niveau 8 de l'ajout ou la copie de tout document dans le système, quelles que soient leurs réglages de l'utilisateur sont. Sont exclus de ces restrictions sont la possibilité d'ajouter des numéros de téléphone Groupes DNC et le filtre et la page Ajouter un nouveau chef de file. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-admin_list_counts">
<BR>
<B>Liste des comtes d'administration -</B> Ce réglage permet de désactiver la liste des chefs d'accusation qui figurent sur la liste des listes et des écrans de forçage de la campagne. Défaut est 1 pour activé.

<BR>
<A NAME="settings-allow_emails">
<BR>
<B>Laisser Emails -</B> C'est là que vous pouvez définir si ce système sera en mesure de recevoir des e-mails entrants, en plus des appels téléphoniques.

<BR>
<A NAME="settings-first_login_trigger">
<BR>
<B>Trigger connecter d'abord -</B> Ce paramètre permet la configuration initiale de l'écran du serveur pour être montré à l'administrateur lors de leur première session dans le système.

<BR>
<A NAME="settings-default_phone_registration_password">
<BR>
<B>Mot de passe par défaut d'inscription Téléphone -</B> C'est le mot de passe d'enregistrement par défaut utilisée lors de nouveaux téléphones sont ajoutés au système. Par défaut est test.

<BR>
<A NAME="settings-default_phone_login_password">
<BR>
<B>Login Mot de passe par défaut Téléphone -</B> C'est le téléphone de connexion par défaut mot de passe utilisé lors web nouveaux téléphones sont ajoutés au système. Par défaut est test.

<BR>
<A NAME="settings-default_server_password">
<BR>
<B>Mot de passe par défaut du serveur -</B> C'est le mot de passe par défaut du serveur utilisé lorsque de nouveaux serveurs sont ajoutés au système. Par défaut est test.

<BR>
<A NAME="settings-slave_db_server">
<BR>
<B>Database Server esclave -</B> Si vous avez un serveur base de données MySQL esclave puis entrez l'adresse IP locale de ce serveur ici. Cette option n'est actuellement utilisé que par les rapports sélectionnés dans l'option suivante et n'a rien à voir avec la configuration automatique de MySQL réplication maître-esclave. Par défaut est vide pour les handicapés.

<BR>
<A NAME="settings-reports_use_slave_db">
<BR>
<B>Rapports d'utiliser DB Slave -</B> Cette option vous permet de sélectionner les rapports que vous voulez avoir utiliser la base de données MySQL esclave tel que défini dans l option ci-dessus à la place de la base de données master que votre système live est exécuté. Vous devez configurer la réplication esclave MySQL avant d activer cette option. Défaut est vide pour les handicapés.

<BR>
<A NAME="settings-default_field_labels">
<BR>
<B>Les étiquettes de champs par défaut -</B> Ces 19 champs vous permettent de définir le nom tel qu il apparaît dans l interface de l agent ainsi que la page de plomb Modifier administrative. Par défaut est vide, ce qui va utiliser les valeurs par défaut codées en dur dans l interface de l agent. Vous pouvez également définir une étiquette pour --- --- CACHER pour cacher l étiquette et le champ.

<BR>
<A NAME="settings-label_hide_field_logs">
<BR>
<B>Masquer Label dans des journaux d'appels -</B> Si une étiquette est fixée à --- CACHER --- puis les journaux d'appels d'agents, si elle est activée sur la campagne, seront toujours visibles sur le terrain et des données à moins que cette option est activée par défaut est à Y. N.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>Caractéristiques QC Actif- </B> Cette option vous permet d'activer ou désactiver le contrôle de la qualité ou QC fonctionnalités. La valeur par défaut est 0 pour les inactifs.

<BR>
<A NAME="settings-default_webphone">
<BR>
<B>Webphone par défaut -</B> Si définie à 1, cette option fera tous les nouveaux téléphones ont créés Définir en tant que jeu Webphone à défaut Y. est égal à 0.

<BR>
<A NAME="settings-webphone_systemkey">
<BR>
<B>Key System Webphone -</B> Si votre système ou votre fournisseur l'exige, c'est là la clé du système pour le webphone doit être inscrit po par défaut est vide.

<BR>
<A NAME="settings-default_codecs">
<BR>
<B>Codecs par défaut -</B> Vous pouvez définir une liste délimitée par des virgules des codecs d'être défini comme les codecs par défaut pour tous les systèmes. Options pour les codecs comprennent ulaw, alaw, gsm, g729, speex, G722, G723, G726, ILBC, ... Par défaut est vide.

<BR>
<A NAME="settings-custom_dialplan_entry">
<BR>
<B>Entrée personnalisée Dialplan -</B> Ce champ vous permet d'entrer dans tous les éléments plan de numérotation que vous souhaitez pour l'ensemble des serveurs Asterisk, les lignes seront ajoutées au contexte par défaut.

<BR>
<A NAME="settings-reload_dialplan_on_servers">
<BR>
<B>Recharger Dialplan Sur les serveurs -</B> Cette option vous permet de forcer un rechargement de la dialplan sur tous les serveurs Asterisk dans le cluster. Si vous avez fait des changements dans l'entrée personnalisé Dialplan ci-dessus vous devez définir cette valeur à 1 et se soumettre à ces changements ont entrera en vigueur sur les serveurs.

<BR>
<A NAME="settings-noanswer_log">
<BR>
<B>Non-Réponse Connexion -</B> Cette option vous connecter les appels à composition automatique qui ne sont pas répondu à une table séparée. Par défaut est N.

<BR>
<A NAME="settings-did_agent_log">
<BR>
<B>DID journal de l'Agent -</B> Cette option enregistre l'arrivée des appels DID long avec un en-groupe et l'ID utilisateur, le cas échéant, à une table séparée. Par défaut est N.

<BR>
<A NAME="settings-alt_log_server_ip">
<BR>
<B>Alt-Server Log DB -</B> C'est le serveur de log alternatif base de données. Cette option est facultative, et permet à certains journaux soient écrits à une base de données distincte. Par défaut est vide.

<BR>
<A NAME="settings-tables_use_alt_log_db">
<BR>
<B>Alt-Log tableaux -</B> Ce sont les tableaux qui sont disponibles pour la connexion sur le serveur de base de données journal alternatif. Par défaut est vide.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>IP par défaut du serveur externe -</B> Si définie à 1, cette option fera tous les nouveaux téléphones ont créés Utilisez IP du serveur externe mis à Y. par défaut est 0.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>URL Webphone -</B> C'est l'URL de la webphone qui sera utilisé avec ce système si elle est activée dans le dossier des téléphones qui est un agent à l'aide. Par défaut est vide.

<BR>
<A NAME="settings-enable_queuemetrics_logging">
<BR>
<B>Enable QueueMetrics Logging -</B> Ce paramètre vous permet de définir si le système insère les entrées du journal dans la table de base de données queue_log que l activité Asterisk files d attente fait. QueueMetrics est un autonome, closed-source programme d analyse statistique. Vous devez avoir QueueMetrics déjà installé et configuré avant d activer cette fonction. Défaut est 0.

<BR>
<A NAME="settings-queuemetrics_server_ip">
<BR>
<B>IP du serveur QueueMetrics -</B> C'est l'adresse IP de la base de données pour votre installation de QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_dbname">
<BR>
<B>Nom de la base de données de QueueMetrics -</B> C'est le nom de la base de données de QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_login">
<BR>
<B>Login de la base de données de QueueMetrics -</B> C'est le login utilisé pour se connecter à la base de données QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_pass">
<BR>
<B>Mot de passe de la base de données de QueueMetrics -</B> C'est le mot de passe utilisé pour se connecter à la base de données QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_url">
<BR>
<B>URL de QueueMetrics -</B> C'est l'URL ou l'adresse du site web utilisé pour atteindre QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_log_id">
<BR>
<B>QueueMetrics Log ID -</B> C est l ID du serveur que tous les journaux de centre de contact d entrer dans la base de données QueueMetrics va utiliser comme identifiant pour chaque enregistrement.

<BR>
<A NAME="settings-queuemetrics_eq_prepend">
<BR>
<B>QueueMetrics EnterQueue Prepend -</B> Ce champ est utilisé pour permettre l ajout initial de l un des champs de données de liste devant le numéro de téléphone du client pour des rapports de QueueMetrics personnalisés. Défaut est NONE pour ne pas remplir tout.

<BR>
<A NAME="settings-queuemetrics_loginout">
<BR>
<B>QueueMetrics connecter-Out -</B> Cette option affecte la façon dont le système enregistre les connexions et déconnexions d un agent dans le queue_log. Défaut est STANDARD pour utiliser norme AGENTLOGIN AGENTLOGOFF, RAPPEL utilisera AGENTCALLBACKLOGIN et AGENTCALLBACKLOGOFF que QM va analyser différemment, NONE n enregistre aucune connexions et déconnexions dans queue_log.

<BR>
<A NAME="settings-queuemetrics_callstatus">
<BR>
<B>QueueMetrics CallStatus -</B> Cette option si elle est définie à 0 ne sera pas mis dans l'entrée en CALLSTATUS queue_log quand un agent de dispositions d'un appel. Par défaut est 1 pour activé.

<BR>
<A NAME="settings-queuemetrics_addmember_enabled">
<BR>
<B>QueueMetrics addMember Enabled -</B> Cette option si elle est définie à 1 générera ADDMEMBER2 et les entrées dans removeMember queue_log. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-queuemetrics_dispo_pause">
<BR>
<B>Code de QueueMetrics Pause Dispo -</B> Cette option, si peuplée, vous permet de définir si un code de pause dispo est entré en queue_log quand un agent est en état de dispo. Par défaut est vide pour les handicapés.

<BR>
<A NAME="settings-queuemetrics_pause_type">
<BR>
<B>QueueMetrics Type Pause Logging -</B> Si elle est activée, cette option vous identifier le type de pause dans le domaine de données5 de table queue_log. Vous devez vous assurer que vous avez un champ de données5 ou d activer cette fonctionnalité allez casser la compatibilité de QM. Défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-queuemetrics_pe_phone_append">
<BR>
<B>Téléphone QueueMetrics Environnement Téléphone Append -</B> Cette option, si elle est activée, va ajouter le login de téléphone d'agent à l'enregistrement données4 dans la table du journal file d'attente si le domaine de l'environnement de la campagne est peuplée Téléphone. Par défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-queuemetrics_record_hold">
<BR>
<B>QueueMetrics Tenir le journal des appels -</B> Cette option, si elle est activée, enregistre quand un client est mis en attente et prendre fin à l attente dans le tableau record_tags QM plus récente. Défaut est 0 pour les handicapés.

<BR>
<A NAME="settings-queuemetrics_socket">
<BR>
<B>QueueMetrics Socket Envoyer -</B> Cette option, si elle est activée, va envoyer des données QM vers une page web qui va l'envoyer à travers une prise pour la connexion. L'option CONNECT_COMPLETE enverra événements CONNECT, COMPLETEAGENT et COMPLETECALLER à l'URL définie ci-dessous. Défaut est NONE pour handicapés.

<BR>
<A NAME="settings-queuemetrics_socket_url">
<BR>
<B>QueueMetrics Socket Envoyer URL -</B> Si Socket Send est activé au-dessus, c'est l'URL qui est utilisé pour envoyer les données. Par défaut est vide pour handicapés.

<BR>
<A NAME="settings-enable_vtiger_integration">
<BR>
<B>Enable Vtiger Integration -</B> Ce paramètre vous permet d activer l intégration Vtiger avec le système. Liens actuellement Vtiger admin et la recherche ainsi que la réplication de l utilisateur sont les seules fonctions d intégration disponibles. Défaut est 0.

<BR>
<A NAME="settings-vtiger_server_ip">
<BR>
<B>Vtiger DB IP du serveur - </B> Il s'agit de l'adresse IP de la base de données de votre installation Vtiger.

<BR>
<A NAME="settings-vtiger_dbname">
<BR>
<B>Vtiger Database Name - </B> C'est le nom de base de données de votre base de données Vtiger.

<BR>
<A NAME="settings-vtiger_login">
<BR>
<B>Vtiger Database Login - </B> C'est le nom d'utilisateur utilisé pour vous connecter à votre base de données Vtiger.

<BR>
<A NAME="settings-vtiger_pass">
<BR>
<B>Vtiger Database Password - </B> Il s'agit du mot de passe utilisé pour vous connecter à votre base de données Vtiger.

<BR>
<A NAME="settings-vtiger_url">
<BR>
<B>Vtiger Web - </B> Il s'agit de l'URL ou adresse du site Web utilisé pour accéder à votre installation Vtiger.


<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUSES TABLE</FONT></B><BR><BR>
<A NAME="system_statuses">
<BR>
<B>Grâce à l utilisation des états du système, vous pouvez avoir des statuts qui existent pour toutes les campagnes et dans les groupes. Le statut doit être 1-6 caractères, la description doit être 2-30 caractères et sélectionnable définit si elle apparaît dans le système comme une disposition de l agent. Le champ human_answered est utilisé pour le calcul du pourcentage de baisse, ou le taux d abandon. Réglage human_answered à Y va utiliser ce statut en comptant les appels de l homme répondu. L option Catégorie vous permet de regrouper plusieurs états dans une catégorie qui peut être utilisé pour l analyse statistique. Il ya aussi 5 autres paramètres qui définiront le type de statut: vente, dnc, contact avec le client, ne s intéresse pas irréalisable, rappel, prévue.</B>



<BR><BR><BR><BR>

<B><FONT SIZE=3>SCREEN_LABELS TABLE</FONT></B><BR><BR>
<A NAME="screen_labels">
<BR>
<B>Screen labels give you the option of setting different labels for the default agent screen fields on a par campagne basis.</B>

<A NAME="screen_labels-label_id">
<BR>
<B>Label ID écran -</B> Ce champ doit être d'au moins 2 caractères et pas plus de 20 caractères de long, sans espaces ni de caractères spéciaux. Il s'agit du N qui sera utilisé pour identifier l'étiquette écran à travers le système.

<BR>
<A NAME="screen_labels-label_name">
<BR>
<B>Nom du Label écran -</B> Il s'agit du nom descriptif de l'entrée étiquette écran.

<BR>
<A NAME="screen_labels-user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour cet enregistrement, ce qui permet la visualisation de cette administration recoird limité par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce record.

<BR>
<A NAME="screen_labels-label_hide_field_logs">
<BR>
<B>Masquer Label dans des journaux d'appels -</B> Si une étiquette est fixée à --- CACHER --- puis les journaux d'appels d'agents, si elle est activée sur la campagne, seront toujours visibles sur le terrain et des données à moins que cette option est activée par défaut est à Y. N.

<BR>
<A NAME="screen_labels-default_field_labels">
<BR>
<B>Les étiquettes de champs par défaut -</B> Ces 19 champs vous permettent de définir le nom tel qu il apparaît dans l interface de l agent ainsi que la page de plomb Modifier administrative. Par défaut est vide, ce qui va utiliser les valeurs par défaut codées en dur dans l interface de l agent. Vous pouvez également définir une étiquette pour --- --- CACHER pour cacher l étiquette et le champ.



<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUS_CATEGORIES TABLE</FONT></B><BR><BR>
<A NAME="status_categories">
<BR>
<B>Par l'utilisation des catégories de statut de système, vous pouvezgrouper ensemble des statuts pour tenir compte de l'analysestatistique sur un groupe de statuts. L'identification de catégoriedoit être 2-20 caractères de longueur sans les espaces, le nom doitêtre 2-50 caractères de longueur, la description est facultative etl'affichage de TimeonVDAD définit si ce statut sera un de jusqu'à 4statuts qui peuvent être calculés et montrés le temps sur lerapport de temps réel de VDAD.</B> La vente et la catégorie Dead Lead Catégorie sont tous deux utilisés par le système de liste de suggestions lors de l'analyse de la liste des statistiques.



<BR><BR><BR><BR>
<?php
if ($SSallow_emails>0)
	{
?>
<B><FONT SIZE=3>EMAIL ACCOUNTS</FONT></B><BR><BR>
<A NAME="email_accounts">
<BR>
<B>La section de la gestion des comptes de messagerie vous permet de créer, copier et supprimer les paramètres du compte e-mail qui vous permettront d avoir des messages électroniques entrent dans votre système et être traités comme s ils étaient des appels téléphoniques à des agents. comptes de messagerie doit être configuré PAR VOUS ET UN FOURNISSEUR DE SERVICES EMAIL - CE QUI N EST PAS COUVERT PAR CE MODULE.</B>

<BR>
<A NAME="email_accounts-email_account_id">
<BR>
<B>Email Account ID -</B> C'est le nom abrégé du compte e-mail, il n'est pas modifiable après la présentation initiale, ne doivent pas contenir d'espaces et doit être comprise entre 2 et 20 caractères de long.

<BR>
<A NAME="email_accounts-email_account_name">
<BR>
<B>Email Nom du compte -</B> Il s'agit du nom complet du compte de messagerie, il doit être compris entre 2 et 30 caractères de long. 

<BR>
<A NAME="email_accounts-email_account_active">
<BR>
<B>Active -</B> Cela détermine si ce compte sera vérifié pour les nouveaux messages e-mail pour être chargé dans le composeur. 

<BR>
<A NAME="email_accounts-email_account_description">
<BR>
<B>Email Description de compte -</B> This allows for a lengthy description, if needed, of the email account.  255 characters max. 

<BR>
<A NAME="email_accounts-email_account_type">
<BR>
<B>Email Type de compte -</B> Specifies whether the account is used for inbound or outbound email messages.  Should be set to INBOUND. 

<BR>
<A NAME="email_accounts-admin_user_group">
<BR>
<B>Groupe d'utilisateurs d'administration -</B> C'est le groupe d'utilisateur d'administration pour ce groupe entrant, ce qui permet la visualisation de cette administration en groupe restreint par groupe d'utilisateurs. Par défaut est - ALL - qui permet à tout utilisateur admin pour voir ce groupe en.  

<BR>
<A NAME="email_accounts-protocol">
<BR>
<B>Email Protocole de compte -</B> This is the email protocol used by the account you are setting up access to.  Currently only IMAP and POP3 accounts are supported.  

<BR>
<A NAME="email_accounts-email_replyto_address">
<BR>
<B>Email Adresse de réponse -</B> The email address of the account you are setting up access to.  Replies to email messages from the agent interface will read as coming from this address.  

<BR>
<A NAME="email_accounts-email_account_server">
<BR>
<B>Email Account Serveur -</B> Le serveur de messagerie que le compte est logé sur.  

<BR>
<A NAME="email_accounts-email_account_user">
<BR>
<B>Email Utilisateur Account -</B> The login used to access this account.  Usually it's the portion of the reply-to address before the -at- symbol.  

<BR>
<A NAME="email_accounts-email_account_pass">
<BR>
<B>Email Account Mot de passe -</B> The password used to access this account.  This is usually set at the time the email account is created.  

<BR>
<A NAME="email_accounts-email_frequency_check_mins">
<BR>
<B>Email Frequency Check Rate (mins) -</B> How often this email account should be checked.  The highest rate of frequency at the moment is five minutes; some email providers will not allow more than three login attempts in fifteen minutes before locking the account for an indeterminate amount of time.  

<BR>
<A NAME="email_accounts-in_group">
<BR>
<B>ID en groupe -</B> L'In-Group que les e-mails seront envoyés aux.   

<BR>
<A NAME="email_accounts-default_list_id">
<BR>
<B>ID par défaut de la liste -</B> L'ID de la liste qui mène sera insérée dans le cas échéant.  

<BR>
<A NAME="email_accounts-call_handle_method">
<BR>
<B>En appel de groupe Méthode de poignée -</B> C est l action qui sera prise quand un nouvel e-mail se trouve dans le compte. Signifie EMAIL tous les messages électroniques seront insérées dans le tableau de la liste en tant que nouveau chef de file. EMAILLOOKUP va rechercher l ensemble du tableau de la liste pour l adresse de courriel dans la colonne e-mail - si le plomb est trouvée, cette liste de plomb ID sera utilisé dans le dossier qui va dans le tableau de email_list. EMAILLOOKUPRC fait la même chose, mais il ne recherche que des listes appartenant à la campagne sélectionnée dans la zone ID de la campagne En-groupe ci-dessous. EMAILLOOKUPRL recherche uniquement une liste particulière, qui est celle entrée dans la zone Liste En-ID de groupe ci-dessous.  

<BR>
<A NAME="email_accounts-agent_search_method">
<BR>
<B>In-Group Agent Méthode de recherche -</B> La méthode de recherche de l'agent pour être utilisé par le groupe entrant, LO est Load-Balanced-débordement et va essayer d'envoyer l'appel à un agent sur le serveur local avant d'essayer de parvenir à un agent sur un autre serveur, LB est à charge équilibrée et va essayer d'envoyer l'appel vers l'agent suivant, peu importe quel serveur ils sont allumées, est serveur uniquement et ne tentera d'envoyer les appels aux agents sur le serveur que l'appel est arrivé sur. Par défaut LB. <B>IS THIS NECESSARY?</B>  

<BR>
<A NAME="email_accounts-ingroup_list_id">
<BR>
<B>In-Group Liste ID -</B> C'est l'ID de la liste qui sera utilisé pour rechercher un donneur compatible dans.  

<BR>
<A NAME="email_accounts-ingroup_campaign_id">
<BR>
<B>Dans le groupe de campagne ID -</B> C'est l'ID de campagne qui sera utilisé pour rechercher un donneur compatible dans.  




<BR><BR><BR><BR>
<?php } ?>
<B><FONT SIZE=3>CUSTOM TEMPLATE MAKER</FONT></B><BR><BR>
<A NAME="template_maker">
<BR>
Le fabricant de modèle personnalisé vous permet de définir vos propres dispositions de fichiers pour une utilisation avec la liste chargeur et aussi les supprimer si nécessaire. Si vous téléchargez fréquemment des fichiers qui sont dans une disposition cohérente autre que la norme, vous pouvez trouver cet outil utile. La disposition enregistrée fonctionne sur n importe quel fichier téléchargé il correspond, indépendamment du type de fichier ou séparateur.

<BR>
<A NAME="template_maker-create_template">
<BR>
<B>Créer un nouveau modèle - </B>In order to begin creating your new listloader template, you must first load a lead file that has the layout you wish to create the template for.  Click "Choose file", and open the file on your computer you wish to use.  This will upload a copy to your server and process it to determine the file type and delimiter (for TXT files).

<BR>
<A NAME="template_maker-delete_template">
<BR>
<B>Supprimertemplate -</B> If you have a template you no longer use or you mis-entered information on it and would like to re-enter it, select the template from the drop-down menu and click "Supprimer le modèle".

<BR>
<A NAME="template_maker-template_id">
<BR>
<B>ID modèle -</B> This field is where you enter an arbitrary ID for your new custom template.  It must be between 2 and 20 characters and consist of alphanumeric characters and underscores.

<BR>
<A NAME="template_maker-template_name">
<BR>
<B>Nom du modèle -</B> This field is where you enter the name for your new custom template.  Can be up to 30 characters long.

<BR>
<A NAME="template_maker-template_description">
<BR>
<B>description du modèle -</B> This field is where you enter the description for your new custom template.  It can be up to 255 characters long.

<BR>
<A NAME="template_maker-list_id">
<BR>
<B>ID de la liste -</B> All templates must load their records into a list.  Select a list ID to load leads into from this drop-down list, which will display any lists available to you given your user settings.

<BR>
<A NAME="template_maker-assign_columns">
<BR>
<B>Assigning columns -</B> Once you have loaded a sample lead file matching the layout you want to make into a template and select a list ID to load leads into, all of the available columns from the list table and the custom table for the list you selected (if any) will be displayed here.  Colonnes highlighted in blue are standard columns from the list table.  Colonnes highlighted in pink belong to the custom table for the selected list.  Each column listed has a drop-down menu, which should be populated with the fields from the first row of the sample lead file you uploaded.  Assign the appropriate fields to the appropriate columns and press SOUMETTRE TEMPLATE to create your template.  You do not need to assign every field to a column, and you do not need to assign every column a field.  For details on the standard list columns, click <a href="#list_loader">HERE</a>.


<BR><BR><BR><BR>

<A NAME="max_stats">
<B><FONT SIZE=3>Maximum du système des rapports statistiques</FONT></B><BR><BR>
<BR>
<B>Ces statistiques sont mises en cache totaux qui sont stockés sur chaque journée de en temps réel par le biais des processus back-end. Pour les appels entrants, les appels totaux par en-groupe sont calculés pour chaque appel qui pénètre dans le processus qui calcule. Pour les chefs d'accusation ensemble du système, les totaux sont générés à partir des entrées de journal ainsi que d'autres totaux en groupe et de la campagne. Ces totaux peuvent ne pas s'additionner à cause des paramètres que vous avez dans votre système ainsi que lorsque l'appel est raccroché.</B>


<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>QC STATUS CODES</FONT></B><BR><BR>
	<A NAME="qc_status_codes">
	<BR>
	<B>Le contrôle de la qualité - Fonction QC a son propre ensemble de l état des codes distincts de ceux dans l appel des fonctions du système de traitement. QC codes d état doit être comprise entre 2 et 8 caractères et ne contiennent pas de caractères spéciaux comme un espace ou du côlon. Le code d état QC description doit être comprise entre 2 et 30 caractères. Pour utiliser ces fonctions, vous devez avoir QC activé dans les paramètres du système.</B>
	<?php
	}
?>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Rapports</FONT></B><BR><BR>

<A NAME="agent_time_detail">
<BR>
<B>Agent Time Détails -</B> In this report you can view how much time agents spent on what.<BR>
<U>TIME CLOCK</U> = Time the agent been logged in to the time clock.<BR>
<U>AGENT TIME</U> = Totaltime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
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
<B>Agent Status Détail -</B> In this report you can view what and how many statuses has been selected by the agents.<BR>
<U>CALLS</U> = Totalnumber of calls sent to the user.<BR>
<U>CIcalls</U> = Totalnumber of call where there was a Réponse de l'homme which is set under "Admin" -> "System Statuts".<BR>
<U>DNC/CI%</U> = How much in percent DNC (Do Not Call) per Réponse de l'hommes.<BR>
And the rest is justStatuts De Systèmethat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Statuts".<BR>

<A NAME="agent_performance_detail">
<BR>
<B>Agent Performance Détail -</B> This is a combination of Agent Time Détails and Agent Status Détail.<BR>
(Statistics related to handling of calls only)<BR>
<U>CALLS</U> = Totalnumber of calls sent to the user.<BR>
<U>TIME</U> = Totaltime of these (<U>PAUSE</U> + <U>WAIT</U> + <U>TALK</U> + <U>DISPO</U>).<BR>
<U>PAUSE</U> = Amount of time being paused in related to call handling.<BR>
<U>AVG</U> means Average so everything -AVG is for example amount of PAUSE-time divided by total number of calls: (<U>PAUSE</U> / <U>CALLS</U> = <U>PAUSAVG</U>)<BR>
<U>WAIT</U> = Time the agent waits for a call.<BR>
<U>TALK</U> = Time the agent talks to a customer or is in dead state (<U>DEAD</U> + <U>CUSTOMER</U>).<BR>
<U>DISPO</U> = Time the agent uses at the disposition screen (where the agent picks NI, SALE etc).<BR>
<U>DEAD</U> = Time the agent is in a call where the customer has hung up.<BR>
<U>CUSTOMER</U> = Time the agent is in a live call with a customer.<BR>
And the rest is justStatuts De Systèmethat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Statuts".<BR>
- Next table is Pause Codes.<BR>
<U>TOTAL</U> = Totaltime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
<U>NONPAUSE</U> = Everything except pause (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U>).<BR>
<U>PAUSE</U> = Only Pause.<BR>
- The last table is pause codes and their time (like "Agent Time Détails").<BR>
<U>LOGIN</U> = The pause code when going from login directly to pause.<BR>
<U>LAGGED</U> = The time the agent had some network problem or similar.<BR>
<U>ANDIAL</U> = This pause code triggers if the agent been on dispo screen for longer than 1000 seconds.<BR>
and empty is undefined pause code. <BR>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Nanpa cellphone filtering</FONT></B><BR><BR>


<A NAME="nanpa-running">
<BR>
<B>Currently running NANPA scrubs -</B> Affichers a log of the currently running scrubs, including: Start Time, Leads Count, Filter Count, Status Line, Time to Complete, Field Updated, and Field excluded
<BR><BR>
<A NAME="nanpa-settings">
<BR>
<B>Inactive Listes -</B> Contains all the listes inactives, that are eligible to be scrubbed.  A list can only be scrubbed if Actifis set to N on the list.
<BR><BR>
<B>Field to Update -</B> Indicates which lead field will contain the scrub result -Cellphone, Landline, or Invalid-.  If NONE is selected it will use the default field Pays_Code.
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
LA FIN
</TD></TR></TABLE></BODY></HTML>
<?php
exit;

#### END HELP SCREENS
