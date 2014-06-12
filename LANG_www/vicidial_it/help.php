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
echo "<TABLE WIDTH=98% BGCOLOR=#E6E6E6 cellpadding=2 cellspacing=0><TR><TD ALIGN=LEFT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=4><B>ADMINISTRATION: AIUTO<BR></B></FONT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2><BR><BR>\n";

?>
<B><FONT SIZE=3>UTENTI TABELLA</FONT></B><BR><BR>
<A NAME="users-user">
<BR>
<B>Utente ID -</B> Questo campo è dove mettere il numero di utenti di ID, può essere fino a 8 cifre di lunghezza, deve essere di almeno 2 caratteri.

<BR>
<A NAME="users-pass">
<BR>
<B>Password -</B> Questo campo è dove mettere le password degli utenti. Deve essere almeno 2 caratteri di lunghezza. Una password utente deve essere di almeno 8 caratteri e avere lettere minuscole e maiuscole, così come almeno un numero.

<BR>
<A NAME="users-force_change_password">
<BR>
<B>Forza Cambia password -</B> Se questa opzione è impostata su Y, allora l'utente verrà richiesto di cambiare la propria password la prossima volta che accedi alla pagina web di amministrazione. Il valore predefinito è N.

<BR>
<A NAME="users-last_login_date">
<BR>
<B>Ultimo Login Info -</B> Questo mostra la data e l ora di accesso tentativo, e se vi è stato un recente tentativo di login fallito. Se questa modifica modulo utente è presentato, il contatore di login fallito tentativo verrà resettato e l agente può immediatamente tentare di accedere nuovamente. Se un agente ha 10 tentativi di accesso non riusciti di fila, allora non si può tentare di accedere nuovamente per almeno 15 minuti se il proprio account è manuale.

<BR>
<A NAME="users-full_name">
<BR>
<B>Nome Completo -</B> Questo campo è dove mettere il nome completo utenti. Deve essere almeno 2 caratteri.

<BR>
<A NAME="users-user_level">
<BR>
<B>Livello Utente -</B> Questo menu è possibile selezionare il livello utente utenti. Deve essere un livello di 1 per accedere alla schermata agente, deve essere di livello maggiore di 2 per accedere come un vicino, deve essere a livello utente 8 o superiore per entrare in sezione web di amministrazione.

<BR>
<A NAME="users-user_group">
<BR>
<B>Gruppo Utente -</B> Questo menu è possibile selezionare il gruppo di utenti che l utente farà parte. Ci sono diverse caratteristiche dello schermo agente che possono essere controllati tramite le impostazioni del gruppo di utenti. Se questo campo viene lasciato vuoto quindi l utente non può accedere alla schermata agente.

<BR>
<A NAME="users-phone_login">
<BR>
<B>Login Telefono -</B> Qui è dove si può impostare un valore login telefono predefinito quando l utente accede alla schermata agente. Questo valore popolerà automaticamente il phone_login quando l utente accede con il proprio user-pass-campagna nella schermata di login dell agente.

<BR>
<A NAME="users-phone_pass">
<BR>
<B>Password Telefono -</B> Qui è dove è possibile impostare un valore di passaggio telefono predefinito quando l utente accede alla schermata agente. Questo valore popolerà automaticamente il phone_pass quando l utente accede con il proprio user-pass-campagna nella schermata di login dell agente.

<BR>
<A NAME="users-active">
<BR>
<B>Attivo -</B> Questo campo definisce se l utente è attivo nel sistema e può accedere come un agente o manager. Il valore predefinito è Y.

<BR>
<A NAME="users-voicemail_id">
<BR>
<B>Casella Vocale ID -</B> Questa è la casella vocale che le chiamate verranno indirizzati a in un AGENTDIRECT in gruppo al momento se la caduta in gruppo ha impostato il metodo della goccia alla casella vocale e il campo di Casella Vocale impostata AGENTVMAIL.

<BR>
<A NAME="users-optional">
<BR>
<B>E-mail, codice utente e Territorio -</B> Questi sono i campi facoltativi.

<BR>
<A NAME="users-hotkeys_active">
<BR>
<B>Hot Keys activo -</B> esta opcio`n si el sistema a 1 permite que el usuario utilice la funcio`n ra`pida-dispositioning de Hot Keys adentro the agent screen.

<BR>
<A NAME="users-agent_choose_ingroups">
<BR>
<B>El agente elige Ingroups -</B> esta opcio`n si el sistema a 1 permite que el usuario elija los ingroups de los cuales recibirán llamadas cuando ellos conexion a una campaña MÁS CERCANA o DE ENTRADA. Si no el encargado necesitará fijar esto en su pantalla del detalle del usuario de la página del admin.

<BR>
<A NAME="users-agent_choose_blended">
<BR>
<B>Agente Scegli Blended -</B> Questa opzione se impostato a 1 consente all'utente di scegliere se l'agente ha il proprio set campagna per miscelati o meno, e se poi non l'impostazione predefinita mescolato verrà utilizzato. Il valore predefinito è 1 per abilitato.

<BR>
<A NAME="users-agent_choose_territories">
<BR>
<B>Agente Scegli Territori -</B> Questa opzione se impostata a 1 permette all-utente di scegliere i territori che essi ricevere chiamate da quando il login per un manuale o una campagna INBOUND_MAN. In caso contrario, l-utente sarà impostato per utilizzare tutti i territori che sono fissati a far parte dei territori amministrativi Utente sezione.

<BR>
<A NAME="users-scheduled_callbacks">
<BR>
<B>Scheduled Callbacks -</B> Questa opzione consente un agente a disposizione una chiamata come CALLBK e scegliere la data e l-ora in cui il piombo sarà riattivato.

<BR>
<A NAME="users-agentonly_callbacks">
<BR>
<B>Servicios repetidos del Agente-Solamente -</B> esta opcio`n permite que un agente fije un servicio repetido de modo que sean el único agente que puede llamar la parte posteriora del cliente. Esto Tambien permite que el agente vea sus listados del servicio repetido y los llame detrás cualquier momento desean a.

<BR>
<A NAME="users-agentcall_manual">
<BR>
<B>Chiamata Manuale Operatore -</B> Questa opzione consente a un agente di inserire manualmente un nuovo elettrocatetere nel sistema e li chiamano. Questo permette anche la chiamata di qualsiasi numero di telefono dal proprio schermo agente e mette che chiamano nella loro sessione. Utilizzare questa opzione con cautela.

<BR>
<A NAME="users-agentcall_email">
<BR>
<B>Agent Call Email -</B> This option is disabled.

<BR>
<A NAME="users-agent_recording">
<BR>
<B>Agente di registrazione -</B> Questa opzione può impedire un agente di fare le registrazioni dopo il log-in per la schermata agente. Questa opzione deve essere per lo schermo agente di seguire le impostazioni di registrazione della campagna.

<BR>
<A NAME="users-agent_transfers">
<BR>
<B>Agente Trasferimenti -</B> Questa opzione può impedire un agente di aprire il trasferimento - sessione della conferenza nella schermata agente. Se questo è disattivato, l agente non può chiamare terze parti o trasferimento cieco chiamate.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-closer_default_blended">
	<BR>
	<B>Un defecto más cercano mezclado -</B> esta opcio`n omite simplemente el checkbox mezclado en una pantalla MÁS CERCANA de la conexion.
	<?php
	}
?>

<BR>
<A NAME="users-agent_recording_override">
<BR>
<B>Override Agente di registrazione -</B> Questa opzione esclude qualunque sia l opzione è nella campagna per la registrazione. DISABILI non esclude l impostazione di registrazione campagna. MAI consente di disattivare la registrazione sul client. Ondemand è l impostazione predefinita e consente all agente di avviare e interrompere la registrazione, se necessario. ALLCALLS inizieranno la registrazione sul client ogni volta che viene inviata una chiamata a un agente. ALLFORCE inizierà a registrare sul client ogni volta che viene inviata una chiamata a un agente che dà l agente alcuna opzione per fermare la registrazione. Per ALLCALLS e ALLFORCE vi è la possibilità di utilizzare il Delay registrazione di ridurre molto brevi registrazioni e ridurre il carico del sistema.

<BR>
<A NAME="users-agent_shift_enforcement_override">
<BR>
<B>Agente Maiusc esecuzione Sovrascrive -</B> Questa impostazione si sovrascriverà qualsiasi cosa gli utenti del gruppo di utenti ha fissato per lesecuzione Maiusc. ANDICAPPATI utilizzerà lutente gruppo. OFF non applicare a tutti i turni. INIZIO solo applicare il login, ma non riguarda un agente che è in esecuzione sul loro passaggio, se essi sono già eseguito laccesso TUTTE imporrà a turni orario di inizio e di un agente di log dopo che corrono oltre la fine del loro turno tempo. Il valore di default è disattivato.

<BR>
<A NAME="users-agent_call_log_view_override">
<BR>
<B>Call Agent View Log Override -</B> Questa impostazione avrà la precedenza su ciò che l'utente gli utenti gruppo ha fissato per View Call Log Agent. DISABLED utilizza l'impostazione gruppo di utenti. N non permetterà che mostra la chiamata degli utenti log. Y permetterà mostrando il registro delle chiamate dell'utente. Predefinito è DISABLED.

<BR>
<A NAME="users-agent_lead_search_override">
<BR>
<B>Agente di piombo Override Search -</B> Questa impostazione avrà la precedenza su ciò che la campagna ha fissato per la Ricerca Agente di piombo. NOT_ACTIVE utilizza l'impostazione della campagna. ENABLED permette la ricerca di piombo ei disabili non permette la ricerca di piombo. Il valore predefinito è NOT_ACTIVE. LIVE_CALL_INBOUND permetterà ricerca di un po 'di vantaggio su solo una chiamata in entrata. LIVE_CALL_INBOUND_AND_MANUAL permetterà ricerca di un po 'di piombo su una chiamata in entrata o la pausa. Quando piombo di ricerca viene utilizzato in una chiamata in entrata dal vivo, la testa della chiamata quando è andato per l'agente verrà modificato in uno stato di LSMERG, ed i registri per la chiamata viene modificato per creare un collegamento con l'agente di piombo scelto invece.

<BR>
<A NAME="users-alert_enabled">
<BR>
<B>Alert Attivato -</B> Questo campo indica se l agente ha abilitati per quando le chiamate entrano in loro sessione di screen agent del browser avvisi web. Il default è 0 per NO.

<BR>
<A NAME="users-allow_alerts">
<BR>
<B>Consentire Alerts -</B> Questo campo ti dà la possibilità di consentire gli avvisi del browser all agente di essere abilitati dall agente per quando le chiamate entrano in loro sessione di screen agente. Il default è 0 per NO.

<BR>
<A NAME="users-preset_contact_search">
<BR>
<B>Ricerca Contatto Preset -</B> Se l'utente è connesso in una campagna che ha Presets di trasferimento impostate CONTATTI, questa impostazione è possibile disattivare la ricerca di contatto solo per questo utente. Il valore predefinito è NOT_ACTIVE che userà qualunque sia l'impostazione della campagna è.

<BR>
<A NAME="users-max_inbound_calls">
<BR>
<B>Chiamate Max entrata -</B> Se questa impostazione è impostata su un numero maggiore di 0, allora sarà il numero massimo di chiamate entranti un agente in grado di gestire tutti i gruppi in entrata in un giorno. Se l agente raggiunge il numero massimo di chiamate in entrata, allora non saranno in grado di selezionare i gruppi in entrata per prendere le chiamate dal fino al giorno successivo. Questa impostazione avrà la precedenza l impostazione della campagna con lo stesso nome. Il default è 0 per disabili.

<BR>
<A NAME="users-campaign_ranks">
<BR>
<B>La campaña alinea -</B> en esta seccio`n usted puede definir a la filaque un agente tendrá para cada campaña. Estas filas pueden serutilizadas para tener en cuenta la encaminamiento preferida de lallamada cuando la llamada siguiente del agente se fija alcampaign_rank. Anche in questa sezione sono WEB VAR per ciascuna campagna. Questi permettono di ogni agente di avere una diversa variabile stringa che può essere aggiunto al Web Form o SCRIPT scheda URL semplicemente messa - A - web_vars - B - come si farebbe con qualsiasi altro posto campo. Un altro campo in questa sezione è GRADE, che consente la campaign_grade_random agente successiva chiamata l'impostazione da utilizzare. Questa impostazione grado aumenta o diminuisce la probabilità che l'agente riceve la chiamata.

<BR>
<A NAME="users-closer_campaigns">
<BR>
<B>Grupos de entrada -</B> aquí es donde usted selecciona a grupos de entrada que usted desea recibir llamadas de si usted ha seleccionado la campaña MÁS CERCANA. Usted Tambien podrá fijar la fila, o el nivel de habilidad, en estaseccio`n para cada uno de los grupos de entrada así como poder ver elnúmero de las llamadas recibidas de cada grupo de entrada para esteagente específico. Tambien en esta seccio`n está la capacidad de dar al agente una filapara cada grupo de entrada. Estas filas pueden ser utilizadas para laencaminamiento preferida de la llamada cuando esa opcio`n seselecciona en la pantalla del en-grupo. Anche in questa sezione sono WEB VAR per ciascuna campagna. Questi permettono di ogni agente di avere una diversa variabile stringa che può essere aggiunto al Web Form o SCRIPT scheda URL semplicemente messa - A - web_vars - B - come si farebbe con qualsiasi altro posto campo.

<BR>
<A NAME="users-alter_custdata_override">
<BR>
<B>El agente altera la invalidacio`n de los datos del cliente -</B> estaopcio`n se eliminará lo que es la opcio`n en la campaña paraalterarse de los datos del cliente. NOT_ACTIVE utilizará lo que estápresente el ajuste para la campaña. ALLOW_ALTER permitirá siemprepara que el agente altere los datos del cliente, no importa que` es elajuste de la campaña. El defecto es NOT_ACTIVE.

<BR>
<A NAME="users-alter_custphone_override">
<BR>
<B>Agente Alter clienti Telefono Sovrascrive - </B> Questa opzione sovrascrive lopzione è ciò che nella campagna per modificare il numero di telefono del cliente. NOT_ACTIVE userà qualsiasi impostazione è presente per la campagna. ALLOW_ALTER sempre per consentire lagente di modificare il numero di telefono del cliente, non importa ciò che la campagna è limpostazione. Il valore di default è NOT_ACTIVE.

<BR>
<A NAME="users-custom_one">
<BR>
<B>Utente campi personalizzati -</B> Questi cinque campi possono essere utilizzati per vari scopi, e possono essere organizzati in forma gli indirizzi web e script come user_custom_one e così via. La personalizzato 5 campo può essere utilizzato come campo per definire un numero dialplan di inviare una chiamata a da un AGENTDIRECT in gruppo se l'utente non è disponibile, basta mettere AGENTEXT nel campo del messaggio o gli ampliamenti di AGENTDIRECT l'in-group e il sistema cercherà il costume cinque campo e inviare la chiamata a quel numero dialplan.

<BR>
<A NAME="users-alter_agent_interface_options">
<BR>
<B>Altere las opciones de interfaz del agente -</B> esta opcio`n si el sistema a 1 permite que el usuario administrativo modifique las opciones de interfaz de los agentes en admin.php.

<BR>
<A NAME="users-delete_users">
<BR>
<B>Usuarios de la cancelacio`n -</B> esta opcio`n si el sistema a 1 permite que el usuario suprima a otros usuarios del igual o de poco nivel de usuario del sistema.

<BR>
<A NAME="users-delete_user_groups">
<BR>
<B>Suprima a grupos de usuario -</B> esta opcio`n si el sistema a 1 permite que el usuario suprima a grupos de usuario del sistema.

<BR>
<A NAME="users-delete_lists">
<BR>
<B>Cancella Liste -</B> Questa opzione se impostato a 1 consente all utente di eliminare liste dal sistema.

<BR>
<A NAME="users-delete_campaigns">
<BR>
<B>Cancella Campagne -</B> Questa opzione se impostato a 1 consente all utente di eliminare campagne dal sistema.

<BR>
<A NAME="users-delete_ingroups">
<BR>
<B>En-Grupos de la cancelacio`n -</B> Questa opzione se impostata a 1 consente all utente di eliminare Inbound gruppi dal sistema.

<BR>
<A NAME="users-modify_custom_dialplans">
<BR>
<B>Modifica DialPlans personalizzate -</B> Questa opzione, se impostato a 1 consente all utente di visualizzare e modificare le voci dialplan personalizzato che sono disponibili nel menu chiamate, impostazioni di sistema e server schermi di modifica.

<BR>
<A NAME="users-delete_remote_agents">
<BR>
<B>Suprima los agentes alejados -</B> Questa opzione se impostata a 1 consente all utente di eliminare agenti remoti dal sistema.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-load_leads">
	<BR>
	<B>Carica Contatti -</B> Questa opzione se impostato a 1 consente all utente di caricare liste di piombo nella tabella dell elenco attraverso il caricatore piombo web basato.
	<?php
	}
if ($SScustom_fields_enabled > 0)
	{
	?>
	<BR>
	<A NAME="users-custom_fields_modify">
	<BR>
	<B>Modifica campi personalizzati -</B> Questa opzione, se impostato su 1 consente all'utente di modificare i campi elenco personalizzati.
	<?php
	}
?>

<BR>
<A NAME="users-campaign_detail">
<BR>
<B>Detalle de la campaña -</B> esta opcio`n si el sistema a 1 permite que el usuario visio`n y modifique los elementos de la pantalla del detalle de la campaña.

<BR>
<A NAME="users-ast_admin_access">
<BR>
<B>Acceso de AGC Admin -</B> esta opcio`n si el sistema a 1 permite a usuario a la conexion a las páginas astGUIclient del admin.

<BR>
<A NAME="users-ast_delete_phones">
<BR>
<B>La cancelacio`n de AGC telefona -</B> esta opcio`n si el sistema a 1 permite que el usuario suprima entradas del telefono en las páginas astGUIclient del admin.

<BR>
<A NAME="users-delete_scripts">
<BR>
<B>Escrituras de la cancelacio`n -</B> esta opcio`n si el sistema a 1 permite que el usuario suprima las escrituras de la campaña en la pantalla de la modificacio`n de la escritura.

<BR>
<A NAME="users-modify_leads">
<BR>
<B>Modifique los plomos -</B> esta opcio`n si el sistema a 1 permite que el usuario modifique los plomos en la página de los resultados de la búsqueda del plomo de la seccio`n del admin.

<?php
if ($SSallow_emails>0)
	{
?>
<BR>
<A NAME="users-modify_email_accounts">
<BR>
<B>Modifica Account di posta elettronica -</B> Questa opzione, se impostato a 1 consente all'utente di modificare gli account di posta elettronica nella pagina di gestione account e-mail.
<?php
	}
?>

<BR>
<A NAME="users-change_agent_campaign">
<BR>
<B>Cambie la campaña del agente -</B> esta opcio`n si el sistema a 1 permite que el usuario altere la campaña que un agente está registrado en mientras que se registran en e`l.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-delete_filters">
	<BR>
	<B>Cancella Filtros -</B> Questa opzione consente all utente di essere in grado di eliminare i filtri di piombo dal sistema.
	<?php
	}
?>

<BR>
<A NAME="users-delete_call_times">
<BR>
<B>EliminareOrari Chiamata -</B> Questa opzione consente all utente di essere in grado di eliminare i tempi di chiamata record e statali volte chiamata record dal sistema.

<BR>
<A NAME="users-modify_call_times">
<BR>
<B>Modifique El Tiempo De la Llamadas -</B> This option allows the user to view and modify the call times and state call times records. A user doesn't need this option enabled if they only need to change the call times option on the campaigns screen.

<BR>
<A NAME="users-modify_sections">
<BR>
<B>Modifique las secciones -</B> estas opciones permiten que el usuariovisio`n y modifique cada uno los expedientes de las secciones. Si elsistema a 0, el usuario puede considerar la lista de la seccio`n, perono el detalle o la pantalla de la modificacio`n de un expediente enesa seccio`n.

<BR>
<A NAME="users-view_reports">
<BR>
<B>View Report -</B> Questa opzione consente all utente di visualizzare i report web di sistema.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="users-qc_enabled">
	<BR>
	<B>QC Attivato - </B> Questa opzione permette allutente di accedere alla schermata di controllo della qualità agente.

	<BR>
	<A NAME="users-qc_user_level">
	<BR>
	<B>QC Utente Livello - </B> Questa impostazione consente di definire ciò che lagente di controllo della qualità è il livello utente. Ciò dettare il livello di funzionalità per lagente nel QC sezione:<BR>
	1 - Modificare Nulla<BR>
	2 - Modificare Nulla Salvo Stato<BR>
	3 - Modificare tutti i campi<BR>
	4 - Verificare prima tornata di CQ<BR>
	5 - Vedi QC Statistiche<BR>
	6 - Capacità di modificare i record finito<BR>
	7 - Livello Manager<BR>

	<BR>
	<A NAME="users-qc_pass">
	<BR>
	<B>QC record Pass - </B> Questa opzione permette di specificare lagente di un record che ha superato il primo turno delle QC dopo aver esaminato il record.

	<BR>
	<A NAME="users-qc_finish">
	<BR>
	<B>QC record Fine - </B> Questa opzione permette di specificare lagente che ha finito di registrare il secondo turno delle QC dopo aver esaminato il passato record.

	<BR>
	<A NAME="users-qc_commit">
	<BR>
	<B>QC record Impegni - </B> Questa opzione permette di specificare lagente che un record è stato commesso in QC. Essa non può più essere modificato da chiunque.
	<?php
	}
?>

<BR>
<A NAME="users-add_timeclock_log">
<BR>
<B>Aggiungi orologio Entra record - </B> Questa opzione permette allutente di aggiungere record alla orologio Accedi.

<BR>
<A NAME="users-modify_timeclock_log">
<BR>
<B>Modificare orologio Entra record - </B> Questa opzione permette allutente di modificare i record nella orologio Accedi.

<BR>
<A NAME="users-delete_timeclock_log">
<BR>
<B>Elimina orologio Entra record - </B> Questa opzione consente allutente di eliminare i record nel registro orologio.

<BR>
<A NAME="users-vdc_agent_api_access">
<BR>
<B>Agent API Access -</B> Questa opzione consente l account da utilizzare con l agente e non-agente comandi API.

<BR>
<A NAME="users-manager_shift_enforcement_override">
<BR>
<B>Shift Manager esecuzione Sovrascrive -</B> Questa impostazione, se impostato a 1 consentirà un manager per inserire il proprio utente e la password su un agente schermo per scavalcare le restrizioni al passaggio di un agente di sessione, se lagente sta tentando di accedere al di fuori del loro passaggio. Il valore di default è 0.

<BR>
<A NAME="users-download_lists">
<BR>
<B>Download Liste -</B> Questa impostazione, se impostato a 1 consentirà un manager per il download cliccare sul link elenco nella parte inferiore della schermata di modifica un elenco di esportare lintero contenuto di un elenco di file di dati di un appartamento. Il valore di default è 0.

<BR>
<A NAME="users-export_reports">
<BR>
<B>Rapporti Export -</B> Questa impostazione, se impostato a 1 consentirà un manager di accedere alla chiamata di esportazione e le relazioni di piombo sullo schermo Report. Di default è 0. Per l'esportazione chiamate e conduce Rapporti, l'ordine seguente campo viene utilizzato per le esportazioni: <BR>call_date, phone_number_dialed, status, user, full_name, campaign_id/in-group, vendor_lead_code, source_id, list_id, gmt_offset_now, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, length_in_sec, user_group, alt_dial/queue_seconds, rank, owner

<BR>
<A NAME="users-delete_from_dnc">
<BR>
<B>Cancellare dalle liste DNC -</B> Questa impostazione, se impostato a 1 consentirà un gestore di rimuovere i numeri di telefono dagli elenchi DNC nel sistema.

<BR>
<A NAME="users-realtime_block_user_info">
<BR>
<B>Realtime Block Info Utente -</B> Questa impostazione, se impostato su 1 si bloccherà le informazioni utente e la stazione venga visualizzato in tempo reale report. Il default è 0 per disabili.

<BR>
<A NAME="users-modify_same_user_level">
<BR>
<B>Modificare stesso livello utente -</B> Questa impostazione si applica solo al livello 9 utenti. Se abilitato permette al livello 9 all'utente di modificare le proprie impostazioni così come altri a livello di 9 utenti. Il valore predefinito è 1 per abilitato.

<BR>
<A NAME="users-admin_hide_lead_data">
<BR>
<B>Admin nascondere i dati di piombo -</B> Questa impostazione si applica solo a livello 7, 8 e 9 utenti. Se abilitato sostituisce i dati del cliente di piombo nei molti rapporti e le schermate del sistema con Xs. Il default è 0 per disabili.

<BR>
<A NAME="users-admin_hide_phone_data">
<BR>
<B>Admin nascondere i dati del telefono -</B> Questa impostazione si applica solo a livello 7, 8 e 9 utenti. Se abilitato sostituisce i numeri telefonici dei clienti in numerosi rapporti e le schermate del sistema con Xs. Le impostazioni CIFRE mostra solo le ultime X cifre del numero di telefono. Il default è 0 per disabili.






<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAGNE TABELLA</FONT></B><BR><BR>
<A NAME="campaigns-campaign_id">
<BR>
<B>Identificacio`n de la campaña -</B> Úste es el nombre corto de la campaña, e`l no es editable despue`s de la sumisio`n inicial, no puede contener espacios y debe estar entre 2 y 8 caracteres en longitudh.

<BR>
<A NAME="campaigns-campaign_name">
<BR>
<B>Nombre de la campaña -</B> esta es la descripcio`n de la campaña, debe estar entre 6 y 40 caracteres en longitud.

<BR>
<A NAME="campaigns-campaign_description">
<BR>
<B>Descripcio`n de la campaña -</B> esto es un campo de la nota para lacampaña, es opcional y puede ser un máximo de 255 caracteres enlongitud.

<BR>
<A NAME="campaigns-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questa campagna, questo permette la visualizzazione amministratore di questa campagna così come le liste assegnati a questa campagna per essere limitato dal gruppo di utenti. Il default è - ALL - che consente a qualsiasi utente amministratore con le autorizzazioni di gruppi di utenti delle campagne per visualizzare questa campagna.

<BR>
<A NAME="campaigns-campaign_changedate">
<BR>
<B>Fecha del cambio de la campaña -</B> esta es la vez última que losajustes para esta campaña fueron modificados de cualquier manera.

<BR>
<A NAME="campaigns-campaign_logindate">
<BR>
<B>La fecha pasada de la conexion de la campaña -</B> esta es la vezúltima que un agente fue registrado en esta campaña.

<BR>
<A NAME="campaigns-campaign_calldate">
<BR>
<B>Last Campagna Call Data -</B> Questa è l-ultima volta che una chiamata è stata gestita da un agente registrato in questa campagna.

<BR>
<A NAME="campaigns-max_stats">
<BR>
<B>Massimo giornaliero Stats -</B> Sono dati che vengono generati dal sistema ogni giorno per tutta la giornata fino allaOrologio fine della giornata in quanto si trova in Impostazioni di sistema. Questi numeri sono generati dai registri all'interno del sistema per consentire visualizzazione molto più veloce. Le statistiche sono inclusi sono: - Chiamate totali, Agenti Massimo, Massimo chiamate in entrata, Massimo chiamate outbound.

<BR>
<A NAME="campaigns-campaign_stats_refresh">
<BR>
<B>Campagna Stats Refresh -</B> Questa opzione permette di forzare una campagna chiamata statistiche aggiornare, anche se la campagna non è attivo.

<BR>
<A NAME="campaigns-realtime_agent_time_stats">
<BR>
<B>Real-Time Agente Statistiche temporali -</B> L'impostazione di questo a nulla, ma DISABLED consentirà la raccolta di statistiche temporali agente per oggi per questa campagna, che sono visualizzabili attraverso il rapporto in tempo reale. Le chiamate sono in media le chiamate gestite per agente, WAIT è il tempo medio di attesa agente, CUST è il tempo medio di conversazione del cliente, ACW è la media Dopo il tempo di lavoro a chiamata, PAUSE è il tempo di pausa media. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-active">
<BR>
<B>Activo -</B> aquí es adonde usted fija la campaña a activo o a inactivo. Si es inactivo, noone puede registrar en e`l.

<BR>
<A NAME="campaigns-park_ext">
<BR>
<B>Parco della Musica-on-Hold -</B> Qui è dove è possibile definire on-hold contesto musicale per questa campagna. Assicurarsi di selezionare una musica valida sul contesto attesa dalla lista di selezione e che il contesto che hai selezionato ha file validi in esso. Il valore predefinito è di default.

<BR>
<A NAME="campaigns-park_file_name">
<BR>
<B>Park File Name -</B> NOT USED.

<BR>
<A NAME="campaigns-web_form_address">
<BR>
<B>Forma del Web -</B> aquí es donde usted puede fijar el Web page de encargo que será abierto cuando el usuario chasca encendido el boton de la FORMA del WEB. Per personalizzare la stringa di ricerca dopo il modulo web, è sufficiente avviare il modulo web con VAR e quindi lURL che si desidera utilizzare, che sostituisce le variabili con i nomi di variabile che si desidera utilizzare - A - phone_number - B -- proprio come nella sezione SCRIPT scheda. Se si desidera utilizzare i campi personalizzati in un indirizzo web form, è necessario aggiungere &CF_uses_custom_fields=Y come parte del tuo URL.

<BR>
<A NAME="campaigns-web_form_target">
<BR>
<B>Web Form-Target </B> Qui è possibile impostare la pagina web personalizzata cornice del modulo web che sarà aperto a quando lutente fa clic sul pulsante Web Form. Il valore di default è _blank.

<BR>
<A NAME="campaigns-allow_closers">
<BR>
<B>Permita Closers -</B> aquí es donde usted puede fijar si los usuarios de esta campaña tendrán la opcio`n para enviar la llamada a un más cercano.

<?php
if ($SSallow_emails > 0) 
		{
?>
	<BR>
	<A NAME="campaigns-allow_emails">
	<BR>
	<B>Consenti email -</B> Questo è dove si può impostare se gli utilizzatori di questa campagna saranno in grado di ricevere e-mail in entrata, oltre alle telefonate.
<?php
		}
?>
<BR>
<A NAME="campaigns-default_xfer_group">
<BR>
<B>Grupo de la transferencia del defecto -</B> este campo es el En-Grupo deldefecto que será seleccionado automáticamente cuando el agente va almarco de la transferir-conferencia en su interfaz del agente.

<BR>
<A NAME="campaigns-xfer_groups">
<BR>
<B>Grupos permitidos de la transferencia -</B> con estos listados delcheckbox usted puede seleccionar a los grupos a quienes los agentes enesta campaña pueden transferir llamadas. Permita Closers debe serpermitido para que esta opcio`n demuestre para arriba.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-campaign_allow_inbound">
	<BR>
	<B>Permita de entrada y mezclado -</B> aquí es donde usted puede fijar silos usuarios de esta campaña tendrán la opcio`n para tomar llamadasde entrada con esta campaña. Si usted desea hacer de entrada y desalida mezclada entonces esto se debe fijar a Y. Si usted deseasolamente hacer marcar de salida en esta campaña fijo` esto a N.Default es N.

	<BR>
	<A NAME="campaigns-dial_status">
	<BR>
	<B>Estado del dial -</B> aquí es adonde usted fija los estados que usted está deseando marcar encendido dentro de las listas que son activas para la campaña abajo. Para agregar otro estado al dial, seleccio`nelo de la lista drop-downy el tecleo AGREGA. Para quitar uno de los estados del dial, chasqueencendido el acoplamiento del QUITAR al lado del estado que usteddesea quitar.

	<BR>
	<A NAME="campaigns-lead_order">
	<BR>
	<B>Orden de la lista -</B> Este menú es donde usted selecciona co`mo los plomos que emparejan los estados seleccionados arriba serán puestos en la tolva del plomo:
	 <BR> &nbsp; - DOWN: selezionare i primi contatti caricati nella tabella lista
	 <BR> &nbsp; - UP: selezionare gli ultimi conduttori caricati nella tabella lista
	 <BR> &nbsp; - UP PHONE: seleziona il numero di telefono piu` grande e poi scorre verso il basso
	 <BR> &nbsp; - DOWN PHONE: seleziona il numero di telefono piu` piccolo e poi scorre verso l`alto
	 <BR> &nbsp; - UP LAST NAME: comienzo con los nombres pasados comenzando con Z y trabajos su manera abajo
	 <BR> &nbsp; - DOWN LAST NAME: comienzo con los nombres pasados comenzando con A y trabajos su manera para arriba
	 <BR> &nbsp; - UP COUNT: comienzo con los plomos y los trabajos llamados su manera abajo
	 <BR> &nbsp; - DOWN COUNT: comienzo con menos plomos y trabajos llamados su manera para arriba
	 <BR> &nbsp; - DOWN COUNT 2nd NEW: comienzo con menos plomos y trabajos llamados su manera para arriba que inserta un NEW plomo en cada otro lead(Must para no tener NEW seleccionado en los estados del dial)
	 <BR> &nbsp; - DOWN COUNT 3nd NEW: comienzo con menos plomos y trabajos llamados su manera para arriba que inserta un NEW plomo en cada tercer lead(Must para no tener NEW seleccionado en los estados del dial)
	 <BR> &nbsp; - DOWN COUNT 4th NEW: comienzo con menos plomos y trabajos llamados que su manera para arriba que inserta un NEW plomo en cada adelante conduzca (no debe tener NEW seleccionado en los estados del dial)
	 <BR> &nbsp; - RANDOM: Randomly afferra prima all-interno della statuti e gli elenchi di cui
	 <BR> &nbsp; - Rialzi ultimi guardia: Ordina per il tempo più recente chiamata locale per la porta
	 <BR> &nbsp; - Ribassi ultimi guardia: Ordina per il tempo più antiche chiamata locale per la porta
	 <BR> &nbsp; - UP RANK: Inizia con il rango più elevato e si sta facendo strada verso il basso
	 <BR> &nbsp; - DOWN RANK: Inizia con il rango più basso e si sta facendo strada fino
	 <BR> &nbsp; - UP OWNER: Inizia con i proprietari che iniziano con Z e lavora la sua strada verso il basso
	 <BR> &nbsp; - DOWN OWNER: Inizia con i proprietari che inizia con A e si sta facendo strada fino
	 <BR> &nbsp; - UP TIMEZONE: Inizia con fuso orario orientale e opere West
	 <BR> &nbsp; - DOWN TIMEZONE: Inizia con fuso orario occidentale e orientale opere

	<BR>
	<A NAME="campaigns-lead_order_randomize">
	<BR>
	<B>Order List Randomize -</B> Questa opzione consente di casuale l'ordine dei cavi nella tramoggia di carico all'interno dei risultati definiti dai criteri di cui sopra. Per esempio, l'ordine di graduatoria saranno conservati, ma i risultati saranno randomizzati all'interno di tale ordinamento. Impostando questa opzione su Y abilitare questa funzione. Il default è n per disabili. NOTE, se si dispone di un gran numero di porta questa opzione può rallentare la velocità dello script tramoggia di carico.

	<BR>
	<A NAME="campaigns-lead_order_secondary">
	<BR>
	<B>Elenco Ordine Secondario -</B> Questa opzione consente di selezionare secondo ordine smistamento dei cavi in ​​carico tramoggia dopo l'ordine di graduatoria e nei risultati definiti dai criteri di cui sopra. Ad esempio, l'Ordine elenco verrà utilizzato per il primo tipo dei fili conduttori, ma i risultati saranno ordinati una seconda volta all'interno di tale ordinamento attraverso lo stesso insieme dell'Ordine primo elenco. Ad esempio, se avete Order List impostato conto alla rovescia e si dispone solo di cavi che sono state tentate 1 e 2 volte, quindi se avete il set dell'Ordine secondaria List per LEAD_ASCEND tutti i 1 conduce tentativo verranno ordinati dai più antichi conduttori prima e sarà messa nella tramoggia in quel modo. Predefinito è LEAD_ASCEND. NOTE, se si dispone di un gran numero di cavi utilizzando una delle opzioni CALLTIME può rallentare la velocità dello script tramoggia di carico. Se Order List Randomize è attivata, questa opzione verrà ignorata.

	<BR>
	<A NAME="campaigns-hopper_level">
	<BR>
	<B>Hopper Livello Minimo -</B> Questo è il numero minimo di cavi script tramoggia di carico cerca di mantenere nella tabella tramoggia per questa campagna. Se l esecuzione di script VDhopper ogni minuto, fanno di questo un po  maggiore del numero di porta si passa attraverso in un minuto.

	<BR>
	<A NAME="campaigns-use_auto_hopper">
	<BR>
	<B>Hopper Automatic Level -</B> L impostazione di questo a Y permette al sistema di regolare automaticamente la tramoggia in base al largo le impostazioni che avete nella vostra campagna. La formula si usa per fare questo è:<BR>Number of AttivoAgenti * Livello Auto Dial * ( 60 seconds / Timeout Chiamata ) * Auto Hopper Multiplier<BR>Default is Y.

	<BR>
	<A NAME="campaigns-auto_hopper_multi">
	<BR>
	<B>Hopper Moltiplicatore automatico -</B> Questo è il moltiplicatore per Hopper Auto. L'impostazione di questo a meno di 1 farà sì che l'algoritmo di Hopper Auto per caricare meno contatti rispetto al normale. L'impostazione di questa maggiore di 1 farà sì che l'algoritmo di Hopper Auto per caricare più contatti di quanto non farebbe normalmente. Default è 1.

	<BR>
	<A NAME="campaigns-auto_trim_hopper">
	<BR>
	<B>Auto Trim Hopper -</B> L impostazione di questo a Y permetterà al sistema di rimuovere automaticamente i cavi in ​​eccesso dalla tramoggia. Il valore predefinito è Y.

	<BR>
	<A NAME="campaigns-hopper_vlc_dup_check">
	<BR>
	<B>Hopper VLC Dup Verifica -</B> Impostare questo a Y si tradurrà in ogni lead essere inserito nella tramoggia di essere controllato da vendor_lead_code per assicurarsi che non ci sono duplicati conduttori inseriti con la vendor_lead_code stessa. Questo è molto utile quando Auto-Alt-Dialing con MULTI_LEAD. Il valore predefinito è N.

	<BR>
	<A NAME="campaigns-lead_filter_id">
	<BR>
	<B>Filtro del plomo -</B> este es un me`todo de filtrar sus plomos usando un fragmento de una pregunta del SQL. Utilice esta característica con la precaucio`n, e`l es fácil de parar el marcar accidentalmente con la alteracio`n más leve a la declaracio`n del SQL. El defecto no es NINGUNO.

	<BR>
	<A NAME="campaigns-call_count_limit">
	<BR>
	<B>Conteggio chiamate Limit -</B> Questo impone un limite al numero di tentativi di chiamata per i cavi selezionati in questa campagna. Un cavo può andare oltre questo limite un po 'Se il riciclaggio di piombo o Auto-Alt-Dialing è abilitata. Di default è 0 per nessun limite.

	<BR>
	<A NAME="campaigns-call_count_target">
	<BR>
	<B>Conteggio chiamate di destinazione -</B> Questa opzione viene utilizzata solo per fini di comunicazione e non ha alcun effetto sulla porta composti. Di default è 3.

	<BR>
	<A NAME="campaigns-drop_lockout_time">
	<BR>
	<B>Drop Lockout Time -</B> Questo è un numero di ore che DROP abbandonare le chiamate sarà impedito di essere composto, per impostare disabilitare a 0. Questa impostazione è molto utile in paesi come il Regno Unito, dove vi sono norme che impediscono la convocazione tentativo di clienti entro le 72 ore di un abbandono, o DROP. Di default è 0.

	<BR>
	<A NAME="campaigns-force_reset_hopper">
	<BR>
	<B>Reajuste de la fuerza de la tolva -</B> esto permite que usted limpie fuera del contenido de la tolva sobre la sumisio`n de la forma. Debe ser llenada otra vez cuando la escritura de VDhopper funciona.

	<BR>
	<A NAME="campaigns-dial_method">
	<BR>
	<B>Me`todo del dial -</B> Este campo es la manera de definir co`mo el marcar debe ocurrir. Si el MANUAL entonces el auto_dial_level es bloqueado en 0 a menos que se cambie el me`todo del dial. Si COCIENTE entonces el normal marcando un número de líneas para los agentes activos. ADAPT_HARD_LIMIT marcará predictively hasta el porcentaje caído y despue`s no permitirá marcar agresivo una vez que se alcance el límite de la gota hasta que va el porcentaje abajo otra vez. ADAPT_TAPERED permite para el excedente de funcionamiento el porcentaje caído por la mitad primer de la cambio - según lo definido por el call_time seleccionado para la campaña y consigue más terminante mientras que se enciende la cambio. ADAPT_AVERAGE intenta mantener un promedio o el porcentaje caído que no impone límites duros tan agresivamente como los otros dos me`todos. Usted no puede cambiar el nivel auto del dial si usted está en cualesquiera de los me`todos del dial del ADAPTAR. Solamente el sintonizador puede cambiar el nivel del dial cuando en modo que marca profe`tico. INBOUND_MAN consente di collocare lagente manuale comporre le chiamate provenienti da una campagna elenco pur essendo in grado di prendere le chiamate in entrata tra manuale quadrante chiamate.

	<BR>
	<A NAME="campaigns-auto_dial_level">
	<BR>
	<B>Livello Auto Dial -</B> Questo è possibile impostare quante righe il sistema dovrebbe utilizzare per agente attivo. lo zero 0 significa composizione automatica è disattivata e gli agenti si clicca per comporre ciascun numero. In caso contrario, il sistema manterrà la composizione linee pari ad agenti attivi moltiplicato per il livello di manopola per arrivare a quante linee questa campagna su ogni server dovrebbe consentire. La casella di controllo FORZA LIVELLO ANCHE CON MODALITA` ADAPT consente di forzare un nuovo livello di linea, anche se il metodo di selezione è in una modalità ADAPT. Questo è utile se c è un cambiamento drammatico nella qualità dei cavi e si desidera cambiare drasticamente il dial_level manualmente.

	<BR>
	<A NAME="campaigns-dial_level_threshold">
	<BR>
	<B>Auto Dial Threshold Level -</B> Questa impostazione funziona solo con una ADAPT o RATIO Metodo di selezione, e deve essere impostato a qualcosa di diverso da Disabilitato e il numero di agenti impostazione deve essere superiore a 0. Questa funzione consente di impostare un numero minimo di agenti che l'algoritmo predittivo lavorare. Se il numero di agenti scende al di sotto del numero che avete impostato, allora il livello dial andrà a 1.0 fino a più agenti il ​​login o andare nello stato selezionato. LOGGED di IN_AGENTS conterà tutti gli agenti registrati nella campagna, NON PAUSED_AGENTS conterà solo gli agenti che sono in attesa o di parlare, e WAITING_AGENTS conterà solo agenti che sono in attesa di una chiamata. Predefinito è DISABLED.

	<BR>
	<A NAME="campaigns-available_only_ratio_tally">
	<BR>
	<B>Solamente cuenta disponible -</B> este campo si el sistema a Y deja haciafuera los agentes del estado de INCALL y de la COLETA al calcular elnúmero de llamadas al dial cuando no en modo de dial MANUAL. Eldefecto es N.

	<BR>
	<A NAME="campaigns-available_only_tally_threshold">
	<BR>
	<B>Disponibile Soglia Tally Solo -</B> Questa impostazione funziona solo con una ADAPT o RATIO Metodo di selezione, disponibile solo Tally deve essere impostato su N, questa impostazione deve essere impostato a qualcosa di diverso da Disabilitato e il numero di agenti impostazione deve essere superiore a 0. Questa funzione consente di impostare il numero di agenti di sotto del quale Tally Disponibile solo verrà attivato. Se il numero di agenti scende al di sotto del numero che avete impostato, l'impostazione Disponibile solo con Tally andare a Y temporaneamente fino a quando altri agenti il ​​login o andare nello stato selezionato. LOGGED di IN_AGENTS conterà tutti gli agenti registrati nella campagna, NON PAUSED_AGENTS conterà solo gli agenti che sono in attesa o di parlare, e WAITING_AGENTS conterà solo agenti che sono in attesa di una chiamata. Predefinito è DISABLED.

	<BR>
	<A NAME="campaigns-adaptive_dropped_percentage">
	<BR>
	<B>Límite del porcentaje de la gota -</B> este campo es donde usted fijo` ellímite del porcentaje de llamadas caídas que usted quisiera mientrasque usaba un me`todo adaptante-profe`tico del dial, NO MANUAL o delCOCIENTE.

	<BR>
	<A NAME="campaigns-adaptive_maximum_level">
	<BR>
	<B>El máximo adapta el dial llano -</B> este campo es donde usted fijo` ellímite del límite al numbr de líneas que usted quisiera marcado poragente mientras que usaba un me`todo adaptante-profe`tico del dial, NOMANUAL o del COCIENTE. Este número puede ser más alto que el nivelauto del dial si su hardware lo apoya. El valor debe ser un númeropositivo mayor de uno y puede tener defecto 3.0 de los lugaresdecimales.

	<BR>
	<A NAME="campaigns-adaptive_latest_server_time">
	<BR>
	<B>El tiempo más último del servidor -</B> este campo es utilizadosolamente por el me`todo del dial de ADAPT_TAPERED. Usted debe entraren la hora y el minuto que usted parará el invitar de esta campaña,2100 significaría que usted parará el marcar de esta campaña en eltiempo del servidor de los 9PM. Esto permite que el algoritmo afiladodecida a co`mo agresivamente al dial cerca cuánto tiempo usted tienehasta que usted será el llamar acabado.

	<BR>
	<A NAME="campaigns-adaptive_intensity">
	<BR>
	<B>Adapte el modificante de la intensidad -</B> este campo se utiliza paraajustar la intensidad profe`tica más alta o más baja. El más altoun número positivo que usted selecciona, mayor va el sintonizadoraumentará la llamada que establece el paso cuando para arriba y máslentamente va el sintonizador disminuirá la llamada que establece elpaso cuando abajo. Cuanto más bajo es el número negativo que ustedseleccionan aquí, más lentamente el sintonizador aumentará lallamada que establece el paso y más rápidamente el sintonizadorbajará la llamada que establece el paso cuando va abajo. El defectoes 0. Este campo no es utilizado por los me`todos del dial del MANUALo del COCIENTE.

	<BR>
	<A NAME="campaigns-adaptive_dl_diff_target">
	<BR>
	<B>Blanco Llana De la Diferencia Del Dial -</B> Questo campo è utilizzato per definire se si vuole indirizzare avere un determinato numero di agenti di attesa per le chiamate o le chiamate in attesa per gli agenti. Ad esempio se si vorrebbe sempre avere in media un agente libero di prendere immediatamente le chiamate è necessario impostare questo a -1, se si desidera avere sempre di mira una chiamata in attesa in attesa di un agente è necessario impostare questo valore a 1. Di default è 0. Questo campo non viene utilizzato dal manuale o metodi di selezione INBOUND_MAN.

	<BR>
	<A NAME="campaigns-dl_diff_target_method">
	<BR>
	<B>Comporre Differenza Metodo Target Level -</B> Questa opzione consente di definire se il livello di rotella di regolazione differenza di destinazione viene applicato solo al calcolo del livello di quadrante o anche per la composizione reale su ogni server di composizione. Se si esegue una piccola campagna con agenti collegati in su molti server si potrebbe desiderare di utilizzare l'opzione ADAPT_CALC_ONLY, perché l'opzione CALLS_PLACED può comportare un minor numero di chiamate di essere immessi che deisred. Questa opzione è attiva solo se il livello Dial Difference è impostato su qualcosa di diverso da 0. Il valore predefinito è ADAPT_CALC_ONLY.

	<BR>
	<A NAME="campaigns-concurrent_transfers">
	<BR>
	<B>Transferencias concurrentes -</B> este ajuste se utiliza para definir elnúmero de las llamadas que se pueden enviar a los agentes en el mismotiempo. Se recomienda que este ajuste está dejado en el AUTOMo`VIL. Este campo no es utilizado por el me`todo MANUAL del dial.

	<BR>
	<A NAME="campaigns-queue_priority">
	<BR>
	<B>Coda di priorità - </B> Questa impostazione è utilizzata per definire lordine in cui le chiamate in uscita da questa campagna dovrebbe essere risolta in relazione alle chiamate in entrata, se questa è una campagna in modalità blended. Si consiglia di non impostare le chiamate in entrata a una priorità maggiore rispetto alle campagne outbound chiamate perché le persone si aspettano di chiamata in attesa e le persone si aspettano di essere chiamati per qualcuno di essere lì al telefono.

	<BR>
	<A NAME="campaigns-drop_rate_group">
	<BR>
	<B>Multiple campagna Drop Rate Group -</B> Questa funzione consente di impostare una campagna come membro di una campagna Drop Rate gruppo, o un gruppo di campagne cui dell-uomo chiede risposta e le chiamate Drop per tutte le campagne del gruppo verranno combinati in una percentuale condivisa goccia, o tasso di abbandono. Questo vi permette di eseguire più campagne contemporaneamente e controllare più facilmente il tasso di drop. Ciò è particolarmente utile nel Regno Unito, dove ai permessi di questo metodo di calcolo con il tasso di drop campagna di raggruppamento per la stessa società, anche se vi sono numerose campagne di tale società è in esecuzione durante il giorno stesso. Per attivare questa per una campagna, basta selezionare un gruppo dall-elenco. Ci sono 10 gruppi definiti nel sistema di default, è possibile contattare l-amministratore di sistema per aggiungere di più. Di default è disabilitato.

	<BR>
	<A NAME="campaigns-inbound_queue_no_dial">
	<BR>
	<B>Coda in ingresso No Dial -</B> Questa funzione, se impostata su ENABLED permette di evitare che in uscita auto-selezione di questa campagna se ci sono chiamate in entrata in attesa in coda che fanno parte dei gruppi ammessi in entrata di cui in questa campagna. Impostare questo a ALL_SERVERS cambierà l'algoritmo per calcolare tutte le chiamate in entrata, come le chiamate attive su questo server, anche se sono su un altro server che si riducono le possibilità di porre inutili chiamate in uscita se si dispone di chiamate in arrivo su un altro server. Predefinito è DISABLED.

	<BR>
	<A NAME="campaigns-auto_alt_dial">
	<BR>
	<B>Alt-Nu`mero auto que marca -</B> este ajuste se utiliza para marcarautomáticamente campos alternos del número mientras que marca en elCOCIENTE y ADAPTA me`todos del dial cuando no hay contacto en elnúmero de telefono principal para un plomo, los estados del NA, deB, de la C.C. y de N. Este ajuste no es utilizado por el me`todoMANUAL del dial. PROROGATO alternano i numeri sono numeri caricato nel sistema al di fuori della norma portare informazioni schermo. Uso PROROGATO si possono avere centinaia di numeri di telefono per un singolo cliente record. Utilizzando MULTI_LEAD consente di utilizzare un cavo separato per ogni numero su un conto cliente che tutti condividono un vendor_lead_code comune, e il tipo di numero di telefono per ciascuno è definito con una etichetta nel campo Proprietario. Questa opzione consente di disattivare alcune opzioni sulla schermata Campagna Modifica e mostrare un link al Multi-Alt pagina Impostazioni che permette di impostare quali tipi numero di telefono, definiti dalla etichetta di ogni cavo, viene selezionato e in quale ordine. Questo creerà un filtro speciale di piombo e alterare il campo Rango di tutti i cavi all'interno degli elenchi di cui a questa campagna per chiamare nell'ordine specificato.

	<BR>
	<A NAME="campaigns-dial_timeout">
	<BR>
	<B>Descanso del dial -</B> si está definido, llamadas que normalmente retraso despue`s de que el descanso definido en extensions.conf en lugar de otro descanso en esta cantidad de segundos si es menos que el descanso de extensions.conf. Esto permite descansos del dial rápidamente que cambian del servidor al servidor y a limitar los efectos a una sola campaña. Si usted está teniendo muchos de llamadas del contestador automático o de Casella Vocale usted puede desear intentar cambiar este valor entre a 21-26 y ver si los resultados mejoran.

	<BR>
	<A NAME="campaigns-campaign_vdad_exten">
	<BR>
	<B>Routing Extension -</B> Questo campo permette di un'estensione personalizzata di routing in uscita. Questo consente di utilizzare diversi metodi di gestione delle chiamate a seconda di come si desidera instradare le chiamate in uscita attraverso la vostra campagna. Precedentemente chiamato Campagna estensione VDAD. 
	<BR>- 8364 - stesso 8368
	<BR>- 8365 - Invierà la chiamata solo per un agente sullo stesso server come la chiamata viene messa in
	<BR>- 8366 - Utilizzato per la stampa-1, radiofonici e televisivi e campagne di indagine
	<BR>- 8367 - Will tenta di inviare prima la chiamata a un agente sul server locale, quindi cercherà su altri server
	<BR>- 8368 - DEFAULT - Può inviare la chiamata al prossimo agente disponibile non importa a quale server sono in
	<BR>- 8369 - Utilizzati esclusivamente per rispondere Detection macchina dopo che, lo stesso comportamento 8368
	<BR>- 8373 - Utilizzati esclusivamente per rispondere Detection macchina dopo che il comportamento stesso 8366
	<BR>- 8374 - Utilizzato per la stampa-1, trasmissione e campagne di indagine con Cepstral Text-to-speech
	<BR>- 8375 - Utilizzati esclusivamente per rispondere Detection macchina, quindi premere-1, trasmissione e con campagne di indagine Cepstral Text-to-speech

	<BR>
	<A NAME="campaigns-am_message_exten">
	<BR>
	<B>Messaggio Segreteria Telefonica -</B> Questo campo è per entrare nel prompt di giocare quando l-agente riceve una segreteria telefonica e fa clic sul pulsante Messaggio Segreteria Telefonica nella cornice conferenza di trasferimento. È necessario impostare questo sia un file audio nel negozio di audio o un programma di TTS prompt se TTS è attivato sul tuo sistema.

	<BR>
	<A NAME="campaigns-waitforsilence_options">
	<BR>
	<B>WaitForSilence Opzioni -</B> Se Wait For Silence è desiderato per le chiamate che vengono rilevati come Segreterie allora questo campo ha queste opzioni. Ci sono due impostazioni separate da una virgola, la prima opzione è il tempo per individuare il silenzio in millisecondi e la seconda opzione è per quante volte a rilevare che prima di giocare il messaggio. Di default è vuota per disabili. Un valore standard per questo sarebbe attendere 2 secondi di silenzio per due volte: 2000,2

	<BR>
	<A NAME="campaigns-amd_send_to_vmx">
	<BR>
	<B>AMD invia ad azione -</B> Questa opzione consente di definire se una chiamata viene inviata al Azione AMD quando viene rilevata una segreteria telefonica. Se questo è impostato a N, allora la chiamata sarà appeso appena viene determinata essere una segreteria telefonica. Il default è N.

	<BR>
	<A NAME="campaigns-cpd_amd_action">
	<BR>
	<B>CPD AMD Azione - </B> Se si utilizza il Sangoma ParaXip chiamate di avanzamento di rilevamento di software allora si desidera attivare questa impostazione o limpostazione di DISPO disposizione che la chiamata come AA e appende se la chiamata è in fase di elaborazione e non è stato inviato a un agente o un messaggio che ancora invierà la richiesta alla Segreteria definito Messaggio per questa campagna. Il valore di default è ANDICAPPATI. Impostare questo a ingroup invierà una segreteria telefonica a un gruppo in entrata. Impostare questo a CALLMENU invierà una segreteria telefonica a una chiamata del menu nel sistema.

	<BR>
	<A NAME="campaigns-amd_inbound_group">
	<BR>
	<B>AMD Inbound Gruppo -</B> Se CPD AMD azione è impostata su ingroup, allora questo è il gruppo in ingresso che la chiamata verrà inviata se viene rilevata una segreteria telefonica.

	<BR>
	<A NAME="campaigns-amd_callmenu">
	<BR>
	<B>AMD chiamata Menu -</B> Se CPD AMD azione è impostata su CALLMENU, allora questo è il menu del registro chiamate che la chiamata verrà inviata se viene rilevata una segreteria telefonica.

	<BR>
	<A NAME="campaigns-alt_number_dialing">
	<BR>
	<B>Manuale Alt Num composizione -</B> Questa opzione consente a un agente di comporre manualmente il numero di telefono alternativo o address3 campo dopo il numero principale è stato chiamato. Se l opzione ha selezionato in poi l opzione Alt Dial verrà controllato automaticamente per ogni chiamata. Se l opzione è TIMER in poi Alt telefono o il campo Address3 verrà automaticamente selezionato dopo Timer Alt secondi. Di default è N per disabili.

	<BR>
	<A NAME="campaigns-timer_alt_seconds">
	<BR>
	<B>Secondi Timer Alt -</B> Se l impostazione Alt Num selezione manuale ha TIMER in poi Alt telefono o il campo Address3 verrà automaticamente selezionato dopo questo numero di secondi. Il default è 0 per disabili .

	<BR>
	<A NAME="campaigns-drop_call_seconds">
	<BR>
	<B>Segundos de la llamada de la gota -</B> el número de segundos a partirdel tiempo que la línea de cliente se escoge encima de hasta que lallamada se considera una GOTA, so`lo se aplica a las llamadas desalida.

	<BR>
	<A NAME="campaigns-drop_action">
	<BR>
	<B>Goccia dazione - </B> Questo menu consente di scegliere che cosa succede a una chiamata quando si è in attesa di più di quello che si trova nel campo Secondi Call Drop. Hangup semplicemente riagganciare la chiamata, inviare il messaggio di chiamare il Gota Extensions che lei ha definito di seguito, segreteria invierà la chiamata alla casella vocale che è stato definito sotto IN_GROUP e invia la chiamata in ingresso al gruppo che è definito qui di seguito. VMAIL_NO_INST invierà la chiamata alla casella di posta vocale che si è definito di seguito e non giocherà le istruzioni dopo il messaggio vocale.

	<BR>
	<A NAME="campaigns-safe_harbor_exten">
	<BR>
	<B>seguro puerto Exten -</B> este ser dial plan extension que desear seguropuerto audio archivo ser localizar en en su servidor.

	<BR>
	<A NAME="campaigns-safe_harbor_audio">
	<BR>
	<B>Safe Harbor Audio -</B> Questo è il prompt dei file audio che viene riprodotto se l'azione è impostata su Goccia AUDIO. Il valore predefinito è Buzz.

	<BR>
	<A NAME="campaigns-safe_harbor_audio_field">
	<BR>
	<B>Safe Harbor Audio Campo -</B> Questa impostazione opzionale consente di definire un campo nell'elenco che il sistema utilizzerà come il nome del file audio per ogni piombo al posto del file audio Safe Harbor. Se questo è impostato su Disabilitato sarà sempre utilizzato il file audio Safe Harbor. Il sistema farà alcuna convalida per assicurarsi che il file audio non esiste se non per assicurarsi che il valore del campo è presente almeno un carattere, quindi se volete una guida per utilizzare il valore predefinito sicuro porto Audio poi basta impostare il valore del campo in testa a vuoto. È possibile utilizzare il carattere pipe per collegare più file audio insieme nel valore di campo per ogni cavo. Default è disabilitata. Ecco l'elenco dei campi che possono essere utilizzati per questa impostazione: vendor_lead_code, source_id, list_id, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, alt_phone, email, security_phrase, comments, rank, owner, entry_list_id

	<BR>
	<A NAME="campaigns-safe_harbor_menu_id">
	<BR>
	<B>Safe Harbor Menu chiamata -</B> Questo è il menu chiamata che una chiamata viene inviato se l'azione è impostata su Goccia CALLMENU.

	<BR>
	<A NAME="campaigns-voicemail_ext">
	<BR>
	<B>Casella Vocale -</B> si estuvieron definidas, las llamadas que CAERÍAN normalmente en lugar de otro serían ordenadas a esta caja del voicemail para oír y para dejar un mensaje.

	<BR>
	<A NAME="campaigns-drop_inbound_group">
	<BR>
	<B>Trasferimento Drop Group - </B> In caso di caduta di azione è fissato a IN_GROUP, la chiamata verrà inviata a questo gruppo in entrata se si raggiunge il Call Drop Secondi.

	<BR>
	<A NAME="campaigns-no_hopper_leads_logins">
	<BR>
	<B>Permita Ninguno-Tolva-Conduce conexiones -</B> si el sistema a Y, permiteagentes a la conexion a la campaña incluso si no hay plomos cargadosen la tolva para esa campaña. Esta funcio`n no se necesita encampañas de CLOSER-type. El defecto es N.

	<BR>
	<A NAME="campaigns-no_hopper_dialing">
	<BR>
	<B>N. Dialing Hopper -</B> Se questo è abilitato, la tramoggia non avrà una durata di questa campagna. Questa opzione è disponibile solo se il metodo di selezione è impostato su Manuale o INBOUND_MAN. Si raccomanda di non attivare questa opzione se si dispone di un database molto grande di piombo, oltre 100.000 contatti. Con il n. telefonico Hopper, le seguenti caratteristiche non funzionano: il riciclaggio di piombo, di auto-alt-dialing, mix di lista, la lista degli ordini con X. NUOVO. Se si desidera utilizzare solo il proprietario di composizione è necessario disporre di n. telefonico Hopper abilitato. Di default è N per disabili.

	<BR>
	<A NAME="campaigns-agent_dial_owner_only">
	<BR>
	<B>Unico proprietario Dialing -</B> Se questo è abilitato, l-agente riceverà solo porta che sono entro i parametri per la proprietà. Se questo è impostato su USER allora l-agente deve essere definita dall-utente nel database come proprietario di questo piombo. Se questo è impostato su un territorio il proprietario del piombo deve coincidere con il territorio di cui la schermata Utente Modifica per questo agente. Se questo è impostato su USER_GROUP poi il proprietario del piombo deve corrispondere al gruppo di utenti che l-agente è un membro di. Per questa funzione il metodo di selezione deve essere impostato su Manuale o INBOUND_MAN e n. telefonico Hopper deve essere attivato. Di default è NESSUNO per disabili. Se l'opzione è vuota alla fine, quindi gli utenti possono comporre i cavi senza proprietario definito oltre al proprietario definito conduce.

	<BR>
	<A NAME="campaigns-owner_populate">
	<BR>
	<B>Proprietario Popola -</B> Se questa opzione è attivata e il proprietario campo del piombo è vuoto, il campo proprietario per il piombo popolerà con l'ID utente di un agente che gestisce la chiamata prima. Disabilitata di default.

	<?php
	if ($SSuser_territories_active > 0)
		{
		?>
		<BR>
		<A NAME="campaigns-agent_select_territories">
		<BR>
		<B>Agente Seleziona Territori -</B> Se questa opzione è attivata e l-agente appartiene ad almeno un territorio, l-agente avrà la possibilità di selezionare i territori per comporre conduce da. L-agente viene visualizzato un elenco dei territori disponibili su login e avranno la possibilità di tornare indietro a quella lista territorio, quando si fermò a cambiare i loro territori. Per questa funzione l-unico proprietario di composizione opzione deve essere impostata al territorio e territori utente deve essere abilitato nelle impostazioni di sistema.
		<?php
		}
	?>

	<BR>
	<A NAME="campaigns-list_order_mix">
	<BR>
	<B>Mezcla de la orden de la lista -</B> elimina los campos de la orden delplomo y del estado del dial. Utilizará los parámetros de la lista ydel estado para la entrada seleccionada de la mezcla de la lista en laseccio`n del submarino de la mezcla de la lista en lugar de otro. Eldefecto es LISIADO.

	<BR>
	<A NAME="campaigns-vcl_id">
	<BR>
	<B>Identificacio`n de la mezcla de la lista -</B> identificacio`n de lamezcla de la lista. Debe ser a partir de 2-20 caracteres en longitudsin los espacios o la otra puntuacio`n especial.

	<BR>
	<A NAME="campaigns-vcl_name">
	<BR>
	<B>Nombre de la mezcla de la lista -</B> nombre descriptivo de la mezcla dela lista. Debe ser a partir de 2-50 caracteres en longitud.

	<BR>
	<A NAME="campaigns-list_mix_container">
	<BR>
	<B>Detalle de la mezcla de la lista -</B> la composicio`n de la entrada de lamezcla de la lista. Contiene la identificacio`n de la lista, la ordende la mezcla, los porcentajes y los estados que hacen para arriba estamezcla de la lista. Los porcentajes tienen que agregar siempre hasta100, y las listas todas tienen que ser activas y fijadas a la campañapara que la entrada de la mezcla de la orden sea activada.

	<BR>
	<A NAME="campaigns-mix_method">
	<BR>
	<B>Me`todo de la mezcla de la lista -</B> el me`todo de mezclar a todas laspartes del detalle de la mezcla de la lista juntas. EVEN_MIX mezclarálos plomos de cada parte interpolada con las otras partes, como estos1.2.3.1.2.3.1.2.3. IN_ORDER pondrá los plomos en la orden en la cualse enumeran en la pantalla 1.1.1.2.2.2.3.3.3 del detalle de la mezclade la lista. La voluntad AL AZAR los puso en la orden AL AZAR1.3.2.1.1.3.2.1.3. El defecto es IN_ORDER.

	<BR>
	<A NAME="campaigns-agent_extended_alt_dial">
	<BR>
	<B>Agent Screen Extended Alt Dial -</B> Questa caratteristica permette agli agenti di accedere numeri di telefono alternativo estesi per i cavi oltre il Alt telefoniche standard e campi Address3 che possono essere utilizzati nella schermata di agente per i numeri di telefono oltre il numero di telefono principale. I numeri di telefono estese possono essere selezionati automaticamente utilizzando la funzione Auto-Alt-Dial nelle impostazioni della campagna, ma l attivazione di questa funzione di agente dello schermo consentiranno anche per l agente a chiamare questi numeri da loro schermo agente così come modificare le loro informazioni. Questa funzionalità è in sviluppo e non è al momento disponibile.

	<BR>
	<A NAME="campaigns-survey_first_audio_file">
	<BR>
	<B>Indagine primo file audio - </B> Questo è il nome del file audio che viene riprodotto non appena il cliente sceglie il telefono cellulare durante lesecuzione di un sondaggio campagna.

	<BR>
	<A NAME="campaigns-survey_dtmf_digits">
	<BR>
	<B>Indagine sulle cifre DTMF - </B> Questo campo è il punto in cui definire le cifre che un cliente può premere come opzione su una campagna di indagine. cifre DTMF sono validi0123456789*#. Tutte le opzioni eccetto per i non interessati, terzo e quarto opzioni cifra di passare al percorso di indagine chiamata al metodo.

	<BR>
	<A NAME="campaigns-survey_wait_sec">
	<BR>
	<B>Indagine Attendere Secondi -</B> Questo è il numero di secondi in modalità Indagine attesa del sistema per l'ingresso da parte della persona chiamata fino a quando scatta l'indagine o azione goccia. Non si applica se il metodo di indagine è CHIUDI. Predefinito è di 10 secondi.

	<BR>
	<A NAME="campaigns-survey_ni_digit">
	<BR>
	<B>Indagine non interessa Digit - </B> Questo campo è dove si definisce il cliente premuto cifre che dimostrano che non sono interessati.

	<BR>
	<A NAME="campaigns-survey_ni_status">
	<BR>
	<B>Indagine Stato non è interessato -</B> Questo campo è possibile selezionare lo stato da utilizzare per non interessa. Se DNC viene utilizzato e la campagna è impostato per utilizzare DNC poi il numero di telefono verrà automaticamente aggiunto alla lista DNC interno e, eventualmente, l elenco DNC-specific campagna se questo è attivata in campagna.

	<BR>
	<A NAME="campaigns-survey_opt_in_audio_file">
	<BR>
	<B>Indagine Opt-in file Audio - </B> Questo è il nome del file audio che viene riprodotto quando il cliente ha optato-in per lindagine, non hanno scelto lopt-out non ha risposto o se la non-azione-reazione è impostato su OptOut. Dopo questo file audio viene riprodotto, il metodo di indagine di azione.

	<BR>
	<A NAME="campaigns-survey_ni_audio_file">
	<BR>
	<B>Indagine non interessa File audio - </B> Questo è il nome del file audio che viene riprodotto quando il cliente ha scelto lopt-out di indagine, non hanno scelto lopt-in o non ha risposto se il non-azione-reazione è impostato su OptIn. Dopo questo file audio viene riprodotto, linvito sarà appeso.

	<BR>
	<A NAME="campaigns-survey_method">
	<BR>
	<B>Indagine Metodo - </B> Questa opzione definisce che cosa succede a una chiamata dopo che il cliente ha scelto lopt-in. AGENT_XFER invierà linvito al prossimo disponibili agente. Segreteria invierà la chiamata alla casella vocale che è specificato nel campo Casella Vocale. ESTENSIONE invierà al cliente di definire lestensione della Indagine Xfer Estensione campo. Hangup si bloccherà il cliente. CAMPREC_60_WAV invierà al cliente di avere una registrazione fatta con la loro risposta, questa registrazione sarà collocato in una cartella denominata come la campagna allinterno della campagna di registrazione Indagine Directory. CALLMENU invierà al cliente al menu chiamate definito nell'elenco di selezione di seguito. VMAIL_NO_INST invierà la chiamata alla casella di posta vocale che si è definito di seguito e non giocherà le istruzioni dopo il messaggio vocale.

	<BR>
	<A NAME="campaigns-survey_no_response_action">
	<BR>
	<B>Indagine n. risposta Azione - </B> Questo è dove si definisce che cosa accadrà se non vi è alcuna risposta alla domanda del sondaggio. OptIn solo inviare la chiamata al metodo di indagine, se il cliente preme un cifre DTMF. OptOut invierà al cliente al metodo di indagine, anche se non si preme uno cifre DTMF. GOCCIA lascerà cadere la chiamata utilizzando il metodo della caduta di campagna ma comunque accedere la chiamata come un PM giocato stato del messaggio.

	<BR>
	<A NAME="campaigns-survey_response_digit_map">
	<BR>
	<B>Indagine risposta Digit Mappa - </B> Questa è la sezione in cui è possibile definire una descrizione per andare a ciascuna opzione cifre DTMF che il cliente può scegliere.

	<BR>
	<A NAME="campaigns-survey_xfer_exten">
	<BR>
	<B>Indagine Xfer Estensione - </B> Se il metodo di indagine è stato selezionato ESTENSIONE quindi chiamare il cliente sarebbe stata destinata a questo dialplan estensione.

	<BR>
	<A NAME="campaigns-survey_camp_record_dir">
	<BR>
	<B>Indagine campagna di registrazione Repertorio - </B> Se il metodo di indagine è stato selezionato CAMPREC_60_WAV allora la risposta dei clienti vengono registrati e messi in una directory di nome, dopo la campagna allinterno di questa directory.

	<BR>
	<A NAME="campaigns-survey_third_digit">
	<BR>
	<B>Terza indagine Digit -</B> Questo permette di avere un percorso di terza convocazione, se la terza cifra come definito in questo campo è premuto da parte del cliente.

	<BR>
	<A NAME="campaigns-survey_fourth_digit">
	<BR>
	<B>Digit Indagine Quarta -</B> Questo permette di avere un percorso di quarta chiamata se la quarta cifra come definito in questo campo è premuto da parte del cliente.

	<BR>
	<A NAME="campaigns-survey_third_audio_file">
	<BR>
	<B>Terza indagine di file audio -</B> Questo è il terzo file audio da riprodurre alla selezione da parte del cliente della facoltà di terza cifra.

	<BR>
	<A NAME="campaigns-survey_third_status">
	<BR>
	<B>Terza indagine Status -</B> Questo è il terzo stato utilizzato per la chiamata alla selezione da parte del cliente della facoltà di terza cifra.

	<BR>
	<A NAME="campaigns-survey_third_exten">
	<BR>
	<B>Terza indagine Extension -</B> Questa è la terza proroga utilizzato per la chiamata alla selezione da parte del cliente della facoltà di terza cifra. Il valore predefinito è 8.300, che si blocca immediatamente la chiamata dopo il messaggio di file audio viene riprodotto.

	<BR>
	<A NAME="campaigns-agent_display_dialable_leads">
	<BR>
	<B>Agente di visualizzazione Leads componibile -</B> Questa opzione, se attivata mostrerà il numero di porta dialable disponibili nella campagna nella schermata agente. Questo numero è aggiornato nel sistema di una volta al minuto e sarà aggiornata sullo schermo agente ogni pochi secondi.

	<BR>
	<A NAME="campaigns-survey_menu_id">
	<BR>
	<B>Indagine chiamate Menu -</B> Se il metodo è impostato CALLMENU, questo è il Menu di chiamata che il cliente viene inviato a se opt-in.

	<BR>
	<A NAME="campaigns-survey_recording">
	<BR>
	<B>Indagine di registrazione -</B> Se attivata, questa inizierà la registrazione quando si risponde alla chiamata. Consigliato solo se il metodo non è impostato per trasferire ad un agente. Il default è n per disabili. Se è impostato su Y_WITH_AMD anche segreteria telefonica le chiamate dei messaggi rilevati saranno registrati.

	<?php
	}
?>

<BR>
<A NAME="campaigns-next_agent_call">
<BR>
<B>Llamada siguiente del agente -</B> esto se determina que` agente recibe la llamada siguiente que está disponible:
 <BR> &nbsp; - random: ordini del valore di aggiornamento casuale nella tabella live_agents
 <BR> &nbsp; - oldest_call_start: las o`rdenes por la vez última un agente fueron enviadas una llamada. Resultados en los agentes que reciben el número casi igual de llamadas cabalmente.
 <BR> &nbsp; - oldest_call_finish: las o`rdenes por la vez última un agente acabaron una llamada. El agente de AKA que espera lo más de largo posible recibe la primera llamada.
 <BR> &nbsp; - overall_user_level: ordinanze del user_level dell agente come definito nella tabella agli utenti una maggiore user_level riceveranno più chiamate.
 <BR> &nbsp; - campaign_rank: o`rdenes de la fila dada al agente para la campaña. Lomás arriba posible a lo más bajo posible.
 <BR> &nbsp; - campaign_grade_random: fornisce una maggiore probabilità di ottenere una chiamata agli agenti superiori graduate.
 <BR> &nbsp; - fewest_calls: o`rdenes por el número de las llamadas recibidas por unagente para ese grupo de entrada específico. Menos llamadas primero.
 <BR> &nbsp; - longest_wait_time: ordini per la quantità di tempo agente è stato attivamente in attesa di una chiamata.

<BR>
<A NAME="campaigns-local_call_time">
<BR>
<B>Tiempo de la llamada local -</B> aquí es adonde usted fijo` durante que` horas usted quisiera marcar, según lo determinado por el tiempo local en está en cuál usted está llamando. Esto es controlada por co`digo de área y ajustada por tiempo de los ahorros de la luz del día si es aplicable. Las pautas generales en los E.E.U.U. para el negocio al negocio son los 9am a los 5pm y el negocio a las llamadas del consumidor es los 9am a los 9pm.

<BR>
<A NAME="campaigns-dial_prefix">
<BR>
<B>Prefijo del dial -</B> este campo permite más fácilmente cambiar una trayectoria de marcar a salir con un diverso me`todo sin hacer una recarga en asterisco. El defecto es 9 basados sobre un 91NXXNXXXXXX en el dial plan - extensions.conf.

<BR>
<A NAME="campaigns-manual_dial_prefix">
<BR>
<B>Selezione prefisso manuale -</B> Questo campo opzionale consente di impostare il prefisso di selezione da utilizzare solo quando effettuano le chiamate dial manuali dall'interfaccia agente, come ad esempio utilizzando la funzione MANUAL DIAL, o Dial Number successiva quando il metodo di selezione MANUAL, o la composizione manuale alt numero, oppure programmato dall'utente solo richiamate. Il valore predefinito è vuoto per disabili, che utilizzano il prefisso di selezione definita nel campo precedente. Questa opzione non interferisce con l'opzione Prefisso Chiamata 3way.

<BR>
<A NAME="campaigns-omit_phone_code">
<BR>
<B>Ometti Prefisso Telefono -</B> Questo campo consente di lasciare il campo phone_code durante la selezione all interno del sistema. Ad esempio se si sta componendo nel Regno Unito dal Regno Unito si avrebbe 44 come vostro campo phone_code per tutte le derivazioni, ma si vuole solo comporre 10 cifre nel dial plan extensions.conf per effettuare le chiamate, invece di 44, poi 10 cifre. L impostazione predefinita è N.

<BR>
<A NAME="campaigns-campaign_cid">
<BR>
<B>CallerID della Campagna -</B> Questo campo consente l invio di un numero callerid personalizzato sulle chiamate in uscita. Questo è il numero che avrebbe mostrato sulla callerid della persona che si sta chiamando. Il valore predefinito è UNKNOWN. Se si utilizza T1 o E1 per comporre questa opzione è disponibile solo se si utilizza PRI - ISDN T1 o E1 - che hanno la funzione di callerid personalizzata attivata, questo non funziona con prestatori di servizi di RBS circuiti Derubato bit. Questo funziona anche attraverso la maggior parte VOIP-SIP o IAX bauli-provider che consentono l identificazione del chiamante in uscita dinamica. L usanza callerID si applica solo alle chiamate dirette per la campagna direttamente, tutte le chiamate terze parti o trasferimenti non invierà il callerID personalizzato. NOTA: A volte mettere UNKNOWN o privati ​​nel campo produrrà l invio del numero di default callerID dal gestore con le chiamate. Si consiglia di verificare questo e mettere 0000000000 nel campo callerid invece se non si desidera inviare CallerID.

<BR>
<A NAME="campaigns-use_custom_cid">
<BR>
<B>Personalizzato CallerID -</B> Quando è impostato su Y, questa opzione consente di utilizzare il campo security_phrase nella tabella lista come il CallerID di inviare quando si posiziona per ogni cavo specifico. Se questo campo non ha CID in poi il CallerID campagna sopra definita verrà utilizzato. Questa opzione disabilita l override lista CallerID se c è un CID presente nel campo security_phrase. Predefinito è N. Se impostato su AreaCode si avrà la possibilità di entrare nel sottomenu AC-CID e definire più callerids da utilizzare per AreaCode.

<BR>
<A NAME="campaigns-campaign_rec_exten">
<BR>
<B>Interno Registrazioni Per Questa Campagnasion -</B> Questo campo permette di un estensione registrazione personalizzata per essere utilizzato con il sistema. Ciò consente di utilizzare diverse estensioni a seconda di quanto tempo si desidera consentire a un massimo di registrazione e che tipo di codec che si desidera registrare in La prolunga di default è 8309 che se si seguono gli esempi SCRATCH_INSTALL registreranno nel formato WAV per un massimo di un ora. Un altra opzione inclusa negli esempi è 8310 che registri in formato GSM per un massimo di un ora. Il tempo di registrazione può essere prolungata aumentando l impostazione nella schermata di modifica del server nella sezione Amministrazione.

<BR>
<A NAME="campaigns-campaign_recording">
<BR>
<B>grabacion de la campaña -</B> este menú permite que usted elija que` nivel de la grabacion se permite en esta campaña. NEVER inhabilitará la grabacion en el cliente. ONDEMAND es el defecto y permite que el agente comience y pare a registrar según lo necesitado. ALLCALLS comenzará la grabacion en el cliente siempre que una llamada se envíe a un agente. ALLFORCE will start recording on the client whenever a call is sent to an agent giving the agent no option to stop recording. For ALLCALLS and ALLFORCE there is an option to use the Ritardo Registrazione to cut down on very short recordings and reduce system load.

<BR>
<A NAME="campaigns-campaign_rec_filename">
<BR>
<B>Nome Registrazioni Per Questa Campagna -</B> Questo campo vi permette di personalizzare il nome della registrazione quando la campagna è OnDemand o ALLCALLS. Le variabili sono consentiti CAMPAIGN INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH AGENT VENDORLEADCODE LEADID CALLID. L'impostazione predefinita è FULLDATE_AGENT e sarebbe simile a questa 20051020-103108_6666. Un altro esempio è CAMPAIGN_TINYDATE_CUSTPHONE che sarebbe simile a questa TESTCAMP_51020103108_3125551212. Te conseguente nome del file deve essere inferiore a 90 caratteri di lunghezza.

<BR>
<A NAME="campaigns-allcalls_delay">
<BR>
<B>La registracion retrasa -</B> para la grabacion de ALLCALLS y deALLFORCE solamente. Esta voluntad del ajuste retrasa comenzar de lagrabacion en todas las llamadas para el número de los segundosespecificados en este campo. El defecto es 0.

<BR>
<A NAME="campaigns-per_call_notes">
<BR>
<B>Chiama Note per chiamata -</B> Impostando questa opzione a ENABLED consentirà agli agenti di entrare in note per ogni chiamata che gestiscono l'interfaccia agente. La voce le note campo verrà visualizzato sotto il campo Commenti nell'interfaccia dell'agente. Inoltre, se il gruppo Utente Agent è autorizzato a visualizzare dei registri delle chiamate quindi l'agente sarà in grado di visualizzare le note di chiamata passate per un lead in qualsiasi momento. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-hide_call_log_info">
<BR>
<B>Nascondi Chiama Info Login -</B> Attivando questa opzione si nasconderà qualsiasi registro delle chiamate o chiamare il numero di informazioni quando le informazioni di piombo viene visualizzato sullo schermo dell agente. L impostazione predefinita è N.

<BR>
<A NAME="campaigns-agent_lead_search">
<BR>
<B>Agente di piombo Search -</B> Impostando questa opzione su ENABLED consentirà agli agenti di cercare contatti e visualizzare le informazioni di piombo durante la pausa nell'interfaccia dell'agente. Inoltre, se il gruppo Utente Agent è autorizzato a visualizzare dei registri delle chiamate quindi l'agente sarà in grado di visualizzare le note di chiamata per ogni ultimi piombo che si sta visualizzando informazioni su. Predefinito è DISABLED. LIVE_CALL_INBOUND permetterà ricerca di un po 'di vantaggio su solo una chiamata in entrata. LIVE_CALL_INBOUND_AND_MANUAL permetterà ricerca di un po 'di piombo su una chiamata in entrata o la pausa. Quando piombo di ricerca viene utilizzato in una chiamata in entrata dal vivo, la testa della chiamata quando è andato per l'agente verrà modificato in uno stato di LSMERG, ed i registri per la chiamata viene modificato per creare un collegamento con l'agente di piombo scelto invece.

<BR>
<A NAME="campaigns-agent_lead_search_method">
<BR>
<B>Agente di piombo Metodo di ricerca -</B> Se il piombo Agente di ricerca è attivata, questa impostazione definisce dove l'agente sarà consentito per la ricerca di conduttori. Sistema cercherà l'intero sistema, CAMPAIGNLISTS cercherà all'interno di tutte le liste attive all'interno della campagna, CAMPLISTS_ALL cercherà all'interno di tutte le liste attivi e inattivi all'interno della campagna, LIST cercherà solo all'interno della ID Manuale delle liste Dial come definito nella campagna . Il valore predefinito è CAMPLISTS_ALL. Una di queste opzioni con Utente_ di fronte solo cercare all'interno di cavi che hanno il campo corrispondente al proprietario ID utente dell'agente, le opzioni con GROUP_ di fronte solo cercare all'interno di cavi che hanno il campo proprietario che corrisponde al gruppo di utenti che l'utente è un membro, le opzioni con TERRITORY_ di fronte solo cercare all'interno di cavi che hanno il campo proprietario corrispondenti ai territori che l'agente ha selezionato.

<BR>
<A NAME="campaigns-campaign_script">
<BR>
<B>Escritura de la campaña -</B> este menú permite que usted elija la escritura que aparecerá en la pantalla de los agentes para esta campaña. No seleccione NONE no demostrar ninguna escritura para esta campaña.

<BR>
<A NAME="campaigns-get_call_launch">
<BR>
<B>Consiga el lanzamiento de la llamada -</B> este menú permite que usted elija si usted desee automo`vil-lance la página en una ventana separada, auto-switch de la tela-forma a la lengüeta de la ESCRITURA o no haga nada cuando una llamada se envía al agente para esta campaña. Se i campi elenco personalizzati sono attivati ​​sul vostro sistema, FORM si aprirà la scheda FORM al momento della connessione di una chiamata a un agente.

<BR>
<A NAME="campaigns-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Questi campi permettere di avere due insiemi di Transfer Conference e preset DTMF. Quando viene caricato la chiamata o campagna, lo schermo agente mostrerà due pulsanti sulla cornice trasferimento conferenze e auto-popolano il numero-to-dial e campi invio-DTMF quando viene premuto. Se si desidera consentire trasferimenti consultive, un fronter a una più stretta, hanno l agente usa la casella di controllo CONSULTIVO, che non funziona per terze parti non-agente chiamate consultivi. Per coloro che hanno solo l agente clicca il quadrante con pulsante di clienti. Poi l agente può semplicemente lasciare-3 VIE-CALL e passare alla loro chiamata successiva. Se si desidera consentire i trasferimenti ciechi dei clienti di uno script AGI per la registrazione o un IVR, quindi inserire AXFER nel campo numero-to-dial. È inoltre possibile specificare un estensione personalizzata dopo la AXFER, per esempio se si vuole fare una chiamata a un IVR speciale è stato impostato per estensione 83900 si dovrebbe mettere AXFER83900 nel campo numero-to-dial.

<BR>
<A NAME="campaigns-quick_transfer_button">
<BR>
<B>Trasferimento rapido Button -</B> Questa opzione aggiunge un pulsante di trasferimento rapido alla schermata agente sotto il pulsante Transfer-Conf che permetterà un clic cieco trasferimento di chiamate al selezionato In-gruppo o numero. IN_GROUP invierà le chiamate al Default Xfer gruppo per questa campagna, o In-gruppo se ci fosse una chiamata in entrata. Le opzioni preimpostate invierà le chiamate al preset selezionato. Di default è N per disabili. Le opzioni BLOCCATE vengono utilizzate per bloccare il valore per il pulsante di trasferimento rapido anche se l'agente utilizza le funzionalità di trasferimento conferenza durante una chiamata prima di utilizzare il pulsante Quick Transfer.

<BR>
<A NAME="campaigns-custom_3way_button_transfer">
<BR>
<B>Custom 3-Way Button Transfer -</B> Questa opzione aggiungere un pulsante personalizzato Transfer alla schermata agente sotto il Transfer-Conf pulsante che permette uno scatto a tre vie chiamate utilizzando il preset selezionato o sul campo. Le opzioni saranno PRESET_ effettuare chiamate utilizzando il valore predefinito definito. Le opzioni saranno FIELD_ effettuare chiamate utilizzando il numero nel campo selezionato dal piombo. DISABILI non verrà visualizzato il pulsante sullo schermo dell'agente. Le opzioni saranno PARK_ parcheggiare al cliente prima della composizione. Predefinito è DISABLED. L'opzione sarà VIEW_PRESET aprire semplicemente la cornice di trasferimento e il telaio preset. L'opzione VIEW_CONTACTS aprirà una finestra di ricerca di contatti, questo funziona solo se Presets Enable è impostato su CONTATTI.

<BR>
<A NAME="campaigns-prepopulate_transfer_preset">
<BR>
<B>Trasferimento PrePopulate Preset -</B> Questa opzione di inserire il numero di campo Dial nella cornice Conferenza di trasferimento dello schermo agente se definito. Di default è N per disabili.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Abilita Presets di trasferimento -</B> Questa opzione permetterà ai Presets menu secondario ad apparire nella parte superiore della pagina Modifica campagna, e inoltre si avrà la possibilità di specificare i numeri preimpostati di composizione per agenti da utilizzare nel Transfer-Conference struttura dell'interfaccia dell'agente. Predefinito è DISABLED. CONTATTI è un'opzione solo se contact_information è abilitata sul sistema, che è una funzione personalizzata.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Abilita Presets di trasferimento -</B> Questa opzione permetterà ai Presets menu secondario ad apparire nella parte superiore della pagina Modifica campagna, e inoltre si avrà la possibilità di specificare i numeri preimpostati di composizione per agenti da utilizzare nel Transfer-Conference struttura dell'interfaccia dell'agente. Predefinito è DISABLED. CONTATTI è un'opzione solo se contact_information è abilitata sul sistema, che è una funzione personalizzata.

<BR>
<A NAME="campaigns-hide_xfer_number_to_dial">
<BR>
<B>Nascondi numero di trasferimento da chiamare -</B> Questa opzione consente di nascondere il numero da comporre campo nella Transfer-Conference struttura dell'interfaccia dell'agente. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call">
<BR>
<B>Parcheggia chiamata IVR -</B> Questa opzione permetterà un agente di parcheggiare una chiamata con un apposito tasto CALL IVR PARK sulla loro interfaccia agente se questo è abilitato o ENABLED_PARK_ONLY. L'opzione ENABLED_PARK_ONLY permetterà all'agente di inviare la chiamata a parco, ma non fare clic per recuperare la chiamata come l'opzione abilitata. L'opzione ENABLED_BUTTON_HIDDEN consente alla funzione tramite l'API solo. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call_agi">
<BR>
<B>Parcheggia chiamata IVR AGI -</B> Se il campo Chiamata Parco IVR non è disattivato, allora questo campo viene utilizzato come stringa di applicazione AGI che il cliente viene inviato. Si tratta di una impostazione che deve essere impostata dall'amministratore, se possibile,.

<BR>
<A NAME="campaigns-timer_action">
<BR>
<B>Timer azione -</B> Questa funzione consente di attivare azioni dopo un certo periodo di tempo. D1 e D2 le opzioni di chiamata lancerà un appello per il trasferimento Conferenza preset Numero e li trasmette alla sessione di agente, questo è di solito utilizzato per semplici applicazioni IVR AGI convalida o semplicemente a svolgere un messaggio pre-registrato. FORM aprirà l-indirizzo web form. MESSAGE_ONLY semplicemente visualizzare il messaggio che è nel campo sottostante. NESSUNO sarà disabilitare questa funzione ed è il default. CHIUDI si bloccherà la chiamata quando il timer è attivato, CALLMENU invierà la chiamata al Menu chiamata specificato nel campo Destinazione Timer azione, ESTENSIONE invierà la chiamata per l'estensione che è specificato nel campo Destinazione Timer azione, IN_GROUP invierà la chiamata alla In-gruppo specificato nel campo Destinazione Timer azione.

<BR>
<A NAME="campaigns-timer_action_message">
<BR>
<B>Timer azione Message -</B> Questo è il messaggio che appare sullo schermo agente al momento l-azione Timer è attivato.

<BR>
<A NAME="campaigns-timer_action_seconds">
<BR>
<B>Timer azione Secondi -</B> Questa è la quantità di tempo dopo la chiamata è collegata al cliente che l-azione del timer viene attivato. Predefinito è -1, che è anche attivo.

<BR>
<A NAME="campaigns-timer_action_destination">
<BR>
<B>Timer Azione Destination -</B> Questo campo è dove si specifica il Menu di chiamata, l'estensione o In-gruppo che si desidera la chiamata inviata se l'azione ora è impostato su CALLMENU, l'estensione o IN_GROUP. Il valore predefinito è vuoto.

<BR>
<A NAME="campaigns-scheduled_callbacks">
<BR>
<B>Servicios repetidos programar -</B> esta opcio`n no prohibe a agente a la disposicion una llamada como CALLBK y elige los datos y el tiempo en los cuales el plomo será reactivado.

<BR>
<A NAME="campaigns-scheduled_callbacks_alert">
<BR>
<B>Richiamate pianificate Alert -</B> Questa opzione consente la riga di stato callback nell'interfaccia agente essere rosso, lampeggia o lampeggiare in rosso quando ci sono in programma AGENTONLY callback che hanno colpito il loro tempo e la data di attivazione. Il valore predefinito è NONE per la linea di stato standard. Le opzioni rinviano smette di lampeggiare e-o la visualizzazione in rosso quando si controlla i callback, finché il numero di callback modifiche.

<BR>
<A NAME="campaigns-scheduled_callbacks_count">
<BR>
<B>Richiamate pianificate Conte -</B> These options allows you to limit the viewable callbacks in the agent callback alert section on the agent screen, to only LIVE callbacks.  LIVE callbacks are user-only scheduled callbacks that have hit their trigger date and time. ACTIVE call backs are user-only callbacks that are active in the system but have not yet triggered.  You can view both ACTIVE and LIVE callbacks by selecting ALL_ACTIVE.  Default is ALL_ACTIVE.

<BR>
<A NAME="campaigns-callback_days_limit">
<BR>
<B>Programmato richiamate Giorni Limit -</B> Questa opzione consente di ridurre l'agente richiamate in programma calendario per un numero selezionabile di giorni da oggi, 12 l'intero mese di calendario verrà comunque visualizzato, ma solo il numero di giorni sarà selezionabile. Il default è 0 per un numero illimitato.

<BR>
<A NAME="campaigns-callback_hours_block">
<BR>
<B>Programmato richiamate Ore Block -</B> Questa opzione consente di limitare un callback USERONLY prevista la visualizzazione della lista agente di callback fino a X ore dopo che è stato impostato. Il valore predefinito è 0 per nessun blocco.

<BR>
<A NAME="campaigns-callback_list_calltime">
<BR>
<B>Richiamate pianificate blocco Calltime -</B> Questa opzione, se abilitata impedirà la richiamata classificata nella lista callback agente di essere composto se è al di fuori del calltime previsto per la campagna. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-my_callback_option">
<BR>
<B>Il mio richiamate Casella di default -</B> Questa opzione consente di pre-impostare la mia casella di controllo richiamata sullo schermo agente pianificato callback. CHECKED controllerà la casella di controllo automaticamente per ogni chiamata. Il valore predefinito è UNCHECKED.

<BR>
<A NAME="campaigns-wrapup_seconds">
<BR>
<B>Segundos de Wrap Up -</B> el número de los segundos para forzar un agenteesperar antes de permitir que reciban o que marquen otra llamada. Elcontador de tiempo comienza tan pronto como un agente cuelgue paraarriba en su cliente - o en el caso del número alterno que marcacuando el agente acaba el plomo - defecto sea los segundos 0. Si elcontador de tiempo funciona hacia fuera antes de que el agente tengadispositioned la llamada, el agente todavía no se mueve encendido ala llamada siguiente hasta que seleccionan una disposicion.

<BR>
<A NAME="campaigns-wrapup_message">
<BR>
<B>Mensaje de Wrap up -</B> esto es un mensaje campaña-especi`fico que seexhibirá en la pantalla del wrapup si se fijan los segundos delwrapup.

<BR>
<A NAME="campaigns-disable_dispo_screen">
<BR>
<B>Disable Screen Dispo -</B> Questa opzione consente di disattivare lo schermo disposizione nell'interfaccia dell'agente. Il campo Stato Disattiva Dispo di seguito devono essere compilati per far funzionare questa opzione. Il valore predefinito è DISPO_ENABLED. L'opzione DISPO_SELECT_DISABLED non disattivare la schermata dispo completamente, ma viene visualizzata la schermata dispo senza disposizioni, questa opzione deve essere utilizzata solo se si desidera forzare i vostri agenti di utilizzare il software CRM, che invierà lo stato del sistema tramite l'API.

<BR>
<A NAME="campaigns-disable_dispo_status">
<BR>
<B>Disabilita Stato Dispo -</B> Se l'opzione Dispo schermo è impostata su Disabilita DISPO_DISABLED, allora questo campo deve essere compilati Puoi usare qualsiasi disposizione che si desidera per il campo finché è di 1 a 6 caratteri di lunghezza, con solo lettere e numeri.

<BR>
<A NAME="campaigns-dead_max">
<BR>
<B>Morto chiamata Max secondi -</B> Se questo è impostato su un valore superiore a 0, dopo che un cliente riaggancia e l agente non ha fatto clic sul pulsante CHIUDI clienti in questo numero di secondi, la chiamata verrà automaticamente appeso, lo stato seguito verrà impostato e l agente sarà pausa. Il default è 0 per disabili.

<BR>
<A NAME="campaigns-dead_max_dispo">
<BR>
<B>Morto stato di chiamata Max -</B> Se Morto chiamata Max secondi è abilitata, questo è lo stato impostato per la chiamata quando la chiamata morti agente non è appeso fino a superare il numero di secondi impostato in precedenza. L impostazione predefinita è DCMX.

<BR>
<A NAME="campaigns-dispo_max">
<BR>
<B>Dispo chiamata Max secondi -</B> Se questo è impostato maggiore di 0, e l agente non ha selezionato uno status disposizione in questo numero di secondi, la chiamata verrà automaticamente impostato lo stato di sotto e l agente sarà in pausa. Il default è 0 per disabili.

<BR>
<A NAME="campaigns-dispo_max_dispo">
<BR>
<B>Dispo chiamata Max Stato -</B> Se Dispo chiamata Max secondi è abilitata, questo è lo stato impostato per la chiamata quando l agente non ha selezionato uno stato passato il numero di secondi impostato in precedenza. L impostazione predefinita è DISMX.

<BR>
<A NAME="campaigns-pause_max">
<BR>
<B>Agente Pausa Max secondi -</B> Se questo è impostato maggiore di 0, e l agente non va di stato di pausa in questo numero di secondi, l agente verrà automaticamente disconnesso dello schermo dell agente. Il default è 0 per disabili.

<BR>
<A NAME="campaigns-screen_labels">
<BR>
<B>Agente schermo etichette -</B> È possibile selezionare un set di etichette schermo agenti da utilizzare con questa opzione. Il valore predefinito è - System-Settings - per le etichette predefinite.

<BR>
<A NAME="campaigns-status_display_fields">
<BR>
<B>Visualizzazione dello stato Fields -</B> È possibile selezionare quali variabili per le chiamate verrà visualizzato nella riga di stato della schermata agente. CallID visualizzerà il 20 ID chiamata il suo carattere unico, LEADID visualizzerà l'ID di piombo del sistema, LISTID visualizzerà la lista di ID. Il valore predefinito è CallID.

<BR>
<A NAME="campaigns-use_internal_dnc">
<BR>
<B>Lista interna del uso DNC -</B> esto define si esta campaña es filtrarlos plomos contra la lista interna de DNC. Si se fija a Y, la tolvabuscará cada número de telefono en la lista de DNC antes de ponerlaen la tolva. Si está en la lista de DNC entonces que cambiará eseconduce estado a DNCL así que no puede ser marcada. El defecto es N. L-opzione AreaCode è proprio come l-opzione Y, tranne che viene utilizzato per filtrare inoltre un codice di tutta l-area del Nord America ad essere composto, in questo caso utilizzando la voce 201XXXXXXX nella lista DNC potrebbe bloccare tutte le chiamate al 201 AreaCode se abilitato.

<BR>
<A NAME="campaigns-use_campaign_dnc">
<BR>
<B>Usa campagna DNC List - </B> Questo definisce se questa è una campagna per filtrare DNC conduce contro una lista che è specifico per tale campagna solo. Se è impostato su Y, la tramoggia sarà per ciascuno il numero di telefono nel campo specifico DNC prima immissione nella tramoggia. Se si è in campagna specifici DNC elenco allora cambierà status che portano a DNCC e non può quindi essere selezionato. Il valore predefinito è N. L-opzione AreaCode è proprio come l-opzione Y, tranne che viene utilizzato per filtrare inoltre un codice di tutta l-area del Nord America ad essere composto, in questo caso utilizzando la voce 201XXXXXXX nella lista DNC potrebbe bloccare tutte le chiamate al 201 AreaCode se abilitato.

<BR>
<A NAME="campaigns-use_other_campaign_dnc">
<BR>
<B>Altra Campagna DNC -</B> Se l'opzione Usa campagna Lista DNC è abilitata, questa opzione può consentire di utilizzare un diverso elenco DNC campagna, appena messo l'ID campagna dell'Altra Campagna in questo campo. Se si utilizza questa opzione, la lista originale DNC campagna non sarà più selezionata, sarà usato solo l'altra lista DNC campagna. Ciò non pregiudica l'uso della lista di DNC sistema interno. Predefinito è vuoto.

<BR>
<A NAME="campaigns-closer_campaigns">
<BR>
<B>Grupos De entrada Permitidos -</B> For CLOSER campaigns only. Here is where you select the inbound groups you want agents in this CLOSER campaign to be able to take calls from. It is important for BLENDED inbound-outbound campaigns only to select the inbound groups that are used for agents in this campaign. The calls coming into the inbound groups selected here will be counted as active calls for a blended campaign even if all agents in the campaign are not logged in to receive calls from all of those selected inbound groups.

<BR>
<A NAME="campaigns-agent_pause_codes_active">
<BR>
<B>AgentCodici PausaAttivo -</B> Consente agli agenti di selezionare un codice di pausa quando si clicca sul tasto PAUSE nella schermata agente. Codici di pausa sono definibili per campagna nella parte inferiore della schermata di dettaglio vista della campagna e sono memorizzati nella tabella agent_log. L impostazione predefinita è N. FORCE costringerà gli agenti per scegliere un codice PAUSE se si clicca sul tasto PAUSE.

<BR>
<A NAME="campaigns-auto_pause_precall">
<BR>
<B>Auto Pause Pre-Call Work -</B> In auto-selettore di modalità, questa impostazione se abilitata imposterà l'agente automaticamente messa in pausa quando si fa clic su qualsiasi agente delle seguenti funzioni che richiede loro di essere in pausa-Manual Dial, chiamata rapida, Ricerca Piombo, Call View Log, richiamate Check, Inserisci il codice Pausa. Il default è n per inattivo.

<BR>
<A NAME="campaigns-auto_resume_precall">
<BR>
<B>Auto Resume Pre-Call Work -</B> In auto-selettore di modalità, questa impostazione se abilitata imposterà l'agente attiva automaticamente quando l'agente fa clic fuori, o annulla, su qualsiasi delle seguenti funzioni che richiede loro di essere in pausa Dial-Manual, chiamata rapida, Ricerca Piombo, Call Log View, richiamate di controllo, immettere codice di pausa. Il default è n per inattivo.

<BR>
<A NAME="campaigns-auto_pause_precall_code">
<BR>
<B>Auto Pause Pre-Call Codice -</B> Se l'Auto Pause Pre-Call funzione di lavoro di cui sopra è attiva e codici Pause agente è attivo, questa impostazione sarà il codice pausa che viene utilizzato quando l'agente è in pausa per queste attività. Il valore predefinito è PRECAL.

<BR>
<A NAME="campaigns-disable_alter_custdata">
<BR>
<B>Inhabilite alteran datos del cliente -</B> si el sistema a Y, no cambiacualquiera del expediente de datos del cliente cuando lasdisposiciones de un agente la llamada. El defecto es N.

<BR>
<A NAME="campaigns-disable_alter_custphone">
<BR>
<B>Disattiva Alter clienti Telefono - </B> Se è impostata a Y, non cambia il numero di telefono del cliente, quando un agente disposizioni la chiamata. Il valore di default è Y. Utilizzare l-opzione Nascondi per rimuovere completamente il numero di telefono del cliente dal display agente.

<BR>
<A NAME="campaigns-display_queue_count">
<BR>
<B>Agente Mostra Coda Conte - </B> Se è impostata a Y, ogni volta che un cliente è in attesa di un agente, la coda invita visualizzazione nella parte superiore dello schermo agente diventa rosso e mostra il numero di chiamate in attesa. Il valore di default è Y.

<BR>
<A NAME="campaigns-manual_dial_override">
<BR>
<B>Selezione manuale Override -</B> L'impostazione può ignorare le possibilità di impostazione per utenti dial manuale per gli agenti quando sono connessi in questa campagna. NONE seguirà l'impostazione di utenti, ALLOW_ALL permetterà a qualsiasi agente collegato in questa campagna di effettuare chiamate di selezione manuale, DISABLE_ALL non permetteremo a nessuno l'accesso a questa campagna per effettuare chiamate di selezione manuale. Il valore predefinito è NONE.

<BR>
<A NAME="campaigns-manual_dial_list_id">
<BR>
<B>Manual Dial ID Lista -</B> L impostazione predefinita list_id da utilizzare quando un agente effettua una chiamata manuale e un nuovo record di piombo viene creato nella tabella lista. Il valore predefinito è 999. Questo campo può contenere solo cifre.

<BR>
<A NAME="campaigns-manual_dial_filter">
<BR>
<B>Manuale Dial Filtro - </B> Questo ti permette di filtrare le chiamate che gli agenti fanno chiamare in modalità manuale per questa campagna da qualsiasi combinazione delle seguenti operazioni: DNC - a kick out, CAMPAIGNLISTS - il numero deve essere allinterno di elenchi per la campagna, NESSUNO - nessun filtro sul manuale o remoto veloce dial elenchi. CAMPLISTS_ALL - si includono gli elenchi inattivi nella ricerca per il numero.

<BR>
<A NAME="campaigns-manual_dial_search_checkbox">
<BR>
<B>Manual Dial Ricerca Casella -</B> Ciò consente di definire se si desidera che la casella di ricerca selettore manuale deve essere selezionata per impostazione predefinita o meno. Se si sceglie un opzione con RESET, quindi la casella di controllo verrà azzerato dopo ogni chiamata. Predefinita viene selezionata.

<BR>
<A NAME="campaigns-manual_preview_dial">
<BR>
<B>Anteprima composizione manuale -</B> In questo modo l'agente in modalità di selezione manuale per visualizzare le informazioni di piombo quando si fa clic su Componi Numero successiva prima che attivamente comporre la telefonata. C'è un collegamento opzionale per SALTA il cavo e passare a quella successiva se selezionata. Il valore predefinito è PREVIEW_AND_SKIP.

<BR>
<A NAME="campaigns-manual_dial_lead_id">
<BR>
<B>Selezione manuale da ID piombo -</B> In questo modo l agente in modalità di selezione manuale per effettuare una chiamata da lead_id invece di un numero di telefono. Di default è N per disabili.

<BR>
<A NAME="campaigns-api_manual_dial">
<BR>
<B>Selezione manuale API -</B> Questa opzione consente di impostare l'API agente per fare uno dei due chiamate contemporaneamente, STANDARD, o la capacità di mettere in coda le chiamate di selezione manuale e li hanno comporre automaticamente una volta che l'agente va in pausa o è disponibile a prendere il loro chiamata successiva con il opzione per disattivare la composizione automatica di queste chiamate, una coda o QUEUE_AND_AUTOCALL che è lo stesso di coda, ma senza l'opzione per disattivare la composizione automatica di queste chiamate. Se un agente ha più di una chiamata in coda per loro vedranno il conteggio di quante chiamate dial manuali sono in coda proprio sotto il pulsante Pausa, o Dial Number pulsante Avanti. Suggeriamo che se coda viene utilizzato di inviare azioni API usando l'anteprima = YES opzione in modo non si è più volte chiamata chiamate per l'agente senza preavviso. Inoltre, se usate QUEUE e pesantemente utilizzando le chiamate con linea manuali in un metodo non composizione manuale, si consiglia l'impostazione della pausa agente dopo ogni opzione Call to Y. Il valore predefinito è STANDARD.

<BR>
<A NAME="campaigns-manual_dial_call_time_check">
<BR>
<B>Tempo di chiamata Controllo manuale -</B> Se questa opzione è attivata, controlla tutte le chiamate di selezione manuale per assicurarsi che essi si trovano le impostazioni dell'ora delle chiamate previste per la campagna. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-manual_dial_cid">
<BR>
<B>Selezione manuale CID -</B> Questo definisce se un agente di effettuare le chiamate di selezione manuale avrà le impostazioni della campagna identificativi dei chiamanti utilizzati, o dei loro agenti impostazioni del telefono utilizzato identificativi dei chiamanti. Il valore predefinito è CAMPAGNA. Se l'opzione Custom campagna Usa CID è abilitata o la campagna elenco CID impostazione Override viene utilizzato, questa impostazione verrà ignorata.

<BR>
<A NAME="campaigns-post_phone_time_diff_alert">
<BR>
<B>Phone messaggio Ora Alert Difference -</B> Questo manuale-dial-unica caratteristica, se attivata, verrà visualizzato un avviso se il fuso orario per il codice postale di piombo, o il codice postale, è diverso dal fuso orario del prefisso del numero di telefono per il piombo. L'opzione OUTSIDE_CALLTIME_ONLY mostrerà solo l'avviso se i due fusi orari sono diversi e uno dei fusi orari è al di fuori del tempo di chiamata selezionata per la campagna. OUTSIDE_CALLTIME_PHONE solo controllare il fuso orario del numero di telefono del piombo e avvisa se è al di fuori del tempo di chiamata locale. OUTSIDE_CALLTIME_POSTAL solo controllare il fuso orario del codice postale del piombo e avvisa se è al di fuori del tempo di chiamata locale. OUTSIDE_CALLTIME_BOTH controllerà il codice postale e numero di telefono per essere dentro il tempo di chiamata locale, anche se sono nello stesso fuso orario. Questi avvisi mostrerà le informazioni nel registro delle chiamate, elenco callback informazioni, risultati di ricerca di informazioni, quando un cavo viene composto e quando è previsto un cavo. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-in_group_dial">
<BR>
<B>In-Group Selezione manuale -</B> Questa funzione permette di abilitare la possibilità per gli agenti di inserire linea chiamate in uscita manuali che vengono registrati come in-group chiamate assegnato a una specifica in-group. L'opzione MANUAL_DIAL consente l'immissione di telefonate attraverso un In-Gruppo per l'agente di effettuare la chiamata. L'opzione NO_DIAL consente all'agente di accedere contemporaneamente su chiamata che non esiste, come se fosse una vera e propria chiamata, questa viene spesso utilizzato per la registrazione e-mail o fax di tempo. Il SIA opzione consentirà sia chiamata e non-chiamata a riconoscimento in-group. Disabilitato di default.

<BR>
<A NAME="campaigns-in_group_dial_select">
<BR>
<B>In-Group Selezione manuale Select -</B> Questa opzione è attiva solo se il suddetto in-Group funzione Selezione manuale non è disattivato. Questa opzione limita la selezionabili A-Groups che l'agente può inserire in gruppo Selezione manuale chiama attraverso. CAMPAIGN_SELECTED mostra solo le in-gruppi che la campagna è posta come ammissibile a-gruppi. ALL_USER_GROUP mostrerà tutte le in-gruppi che sono visualizzabili ai membri del gruppo di utenti che l'agente appartiene al.

<BR>
<A NAME="campaigns-agent_clipboard_copy">
<BR>
<B>Appunti di agente Schermata Copia - </B> Questa funzione è al momento attivo solo per Internet Explorer. Questa funzione consente di selezionare un campo che verrà copiato negli appunti il computer dellagente computer su una chiamata in corso linvio di un agente. Comune utilizza per questo sono per facilitare lincollatura di numeri di conto corrente o numeri di telefono in eredità applicazioni client sul computer agente.

<BR>
<A NAME="campaigns-three_way_call_cid">
<BR>
<B>3-Way Call CallerID De salida -</B> Questo definisce ciò che viene inviato il numero callerID uscita dal 3-way chiamate effettuate da un agente, CAMPAGNA utilizza la campagna personalizzata callerID, cliente utilizza il numero del cliente che è attivo dagli agenti schermo e AGENT_PHONE utilizza il callerID per il telefono che l agente è collegato. AGENT_CHOOSE consente all agente di scegliere quale callerID da utilizzare per le chiamate a 3 vie da un elenco di scelte. CUSTOM_CID utilizzerà il CID personalizzata definita nel campo security_phrase della tabella lista per il piombo.

<BR>
<A NAME="campaigns-three_way_dial_prefix">
<BR>
<B>3-Way Call Prefisso Chiamata - </B> Questo definisce quello che è usato come prefisso per chiamare il 3-way chiamate, di default è vuoto in modo che il prefisso campagna quadrante è usato, passthru in modo che tu possa sentire suonare è 88.

<BR>
<A NAME="campaigns-customer_3way_hangup_logging">
<BR>
<B>Clienti 3-Way Hangup Logging -</B> Se questa opzione è abilitata, la user_call_log registrerà quando un cliente fino hangup se riagganciare nel corso di un 3-way chiamata. Inoltre, questo può consentire al Cliente 3-way azione hangup se è defineed seguito. Predefinito è ABILITATO.

<BR>
<A NAME="campaigns-customer_3way_hangup_seconds">
<BR>
<B>Cliente 3-Way Hangup Secondi -</B> Se il Cliente 3-way la registrazione è attivata, questa opzione consente di definire il numero di secondi dopo l'hangup cliente viene rilevato prima che sia effettivamente registrato e opzionale cliente 3-way hangup azione viene eseguita. Il valore predefinito è 5 secondi.

<BR>
<A NAME="campaigns-customer_3way_hangup_action">
<BR>
<B>Clienti 3-Way Hangup Action -</B> Se il Cliente 3-way la registrazione è attivata, questa opzione permette di avere lo schermo automaticamente agente di riagganciare la chiamata e passare alla schermata DISPO se questa opzione è impostata su DISPO. Il valore predefinito è NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-qc_enabled">
	<BR>
	<B>QC Attivato - </B> Limpostazione di questo campo a Y consente, per lagente di Controllo della Qualità funzioni. Il valore di default è N.

	<BR>
	<A NAME="campaigns-qc_statuses">
	<BR>
	<B>Status QC - </B> La zona dove si è stati di selezionare quale porta deve essere andato oltre, QC, dal sistema. Mettere un segno di spunta accanto allo status che si desidera, QC, di rivedere. 

	<BR>
	<A NAME="campaigns-qc_shift_id">
	<BR>
	<B>QC Maiusc - </B> Questo è il passaggio di tempo utilizzato per estrarre QC record per una campagna. I giorni della settimana sono ignorati per queste funzioni.

	<BR>
	<A NAME="campaigns-qc_get_record_launch">
	<BR>
	<B>QC ottenere il record di lancio-</B> Ciò consente una delle seguenti azioni per essere attivato su un QC agente riceve un nuovo record.

	<BR>
	<A NAME="campaigns-qc_show_recording">
	<BR>
	<B>QC Visualizza Registrazione - </B> Ciò consente una registrazione che può essere collegato con la QC record di essere esposto nel QC agente schermo.

	<BR>
	<A NAME="campaigns-qc_web_form_address">
	<BR>
	<B>QC Indirizzo Web Form - </B> Questo è lindirizzo web che un agente, QC può andare quando si fa clic sul link contenuto nel Web Form, QC schermo.

	<BR>
	<A NAME="campaigns-qc_script">
	<BR>
	<B>QC Script - </B> Questo è lo script che possono essere utilizzati da agenti QC nel SCRIPT scheda nella schermata QC.
	<?php
	}
?>

<BR>
<A NAME="campaigns-vtiger_search_category">
<BR>
<B>Vtiger Search Category -</B> Se l integrazione Vtiger è abilitata nelle impostazioni di sistema, allora questa impostazione definirà in cui la pagina vtiger_search.php ricercherà il numero di telefono che è stato immesso. Ci sono 4 opzioni che possono essere utilizzati in questo campo: LEAD-Questa opzione di ricerca attraverso il Vtiger porta solo, ACCOUNT-Questa opzione consente di cercare attraverso i conti vtiger e tutti i contatti e sub-contatti per il numero di telefono, VENDOR: questa opzione sarà solo cercare attraverso i fornitori di Vtiger, ACCTID-Questa opzione funziona solo per gli account e prenderà il campo vendor_lead_code lista e provare a cercare l ID account Vtiger. In caso di insuccesso si cercherà eventuali altri metodi elencati che hai selezionato. Opzioni multiple possono essere usate per ogni ricerca, ma su grandi banche dati non è consigliabile. L impostazione predefinita è LEAD. UNIFIED_CONTACT-Questa opzione utilizza la versione beta Vtiger 5.1.0 Funzione di ricerca per numero di telefono e aprire la pagina di ricerca in Vtiger.

<BR>
<A NAME="campaigns-vtiger_search_dead">
<BR>
<B>Vtiger Cerca Dead Accounts -</B> Se l-integrazione Vtiger è attivata nelle impostazioni di sistema, quindi, questa impostazione definire se i conti saranno eliminati cercato quando l-agente clic WEB form per la ricerca nel sistema Vtiger. Disabili conduce da non essere cercati, ASK-soppresso porta sarà ricerche e la pagina di ricerca vtiger web chiederà l-agente se vogliono fare il conto Vtiger attivo, risorgere-automaticamente rendere l-account cancellato di nuovo attivo e adotterà le agente per lo schermo conto immediatamente dopo aver fatto clic sul WEB FORM. Di default è disabilitato.

<BR>
<A NAME="campaigns-vtiger_create_call_record">
<BR>
<B>Vtiger Crea chiamate record - </B> Se Vtiger integrazione è attivata in poi le impostazioni del sistema questa impostazione definisce se una nuova attività Vtiger è stato creato per registrare la chiamata quando lagente va al vtiger_search pagina. Il valore di default è Y. L-opzione DISPO creerà un record di chiamata per l-account Vtiger senza l-agente di dover andare alla pagina di ricerca vtiger attraverso il Web Form.

<BR>
<A NAME="campaigns-vtiger_create_lead_record">
<BR>
<B>Vtiger Crea record Piombo - </B> Se è attivata Vtiger integrazione nel sistema e le impostazioni Vtiger Ricerca Categoria PIOMBO comprende quindi questa impostazione definisce se un nuovo record Vtiger piombo viene creato quando lagente va a vtiger_search la pagina di registrazione e non è di avere trovato la chiamata numero di telefono. Il valore di default è Y.

<BR>
<A NAME="campaigns-vtiger_screen_login">
<BR>
<B>Vtiger Screen Login -</B> Se l integrazione Vtiger è abilitata nelle impostazioni di sistema, allora questa impostazione definisce se l utente è connesso automaticamente nell interfaccia Vtiger quando accedi alla schermata agente. L impostazione predefinita è Y. L opzione NEW_WINDOW aprirà una nuova finestra al momento del login alla schermata agente.

<BR>
<A NAME="campaigns-vtiger_status_call">
<BR>
<B>Vtiger stato della chiamata -</B> Se l integrazione Vtiger è abilitata nelle impostazioni di sistema, allora questa impostazione definisce se lo stato del conto Vtiger verrà aggiornato con lo stato della chiamata dopo che è stato disposto. L impostazione predefinita è N.

<BR>
<A NAME="campaigns-queuemetrics_callstatus">
<BR>
<B>QM CallStatus Override -</B> Se l'integrazione QueueMetrics è abilitato nelle impostazioni di sistema questa impostazione consentono prevalente delle impostazioni di sistema di impostazione per CallStatus voci queue_log. Predefinito è DISABLED che utilizzerà l'impostazione del sistema.

<BR>
<A NAME="campaigns-queuemetrics_phone_environment">
<BR>
<B>QM Phone Ambiente -</B> Se l'integrazione QueueMetrics è abilitato nelle impostazioni di sistema questa impostazione consente l'inserimento di questo valore dei dati nel campo data4 del queue_log per le registrazioni delle attività dell'agente. Il valore predefinito è vuota per disabili.

<BR>
<A NAME="campaigns-extension_appended_cidname">
<BR>
<B>Extension Aggiunge CID -</B> Se attivata, le chiamate effettuate da questa campagna avrà uno spazio e l'estensione telefono dell'agente aggiunto alla fine del nome CallerID per la chiamata prima che venga inviato all'agente. Il default è n per disabili.

<BR>
<A NAME="campaigns-pllb_grouping">
<BR>
<B>PLLB Raggruppamento -</B> Accedi Phone Raggruppamento Load Balancing, consentito solo se ci sono server multipli e alias agente del telefono sono presenti sul sistema. Se è impostato su ONE_SERVER_ONLY che costringerà tutti gli agenti di questa campagna per accedere allo stesso server. Se è impostato su CASCATA si raggruppare i registrato nel agenti sullo stesso server fino al numero Raggruppamento PLLB Limite di agenti sono raggiunti, allora l'agente prossimo per accedere al server successivo con il minor numero di agenti. Se impostato su Disabilitato il comportamento standard Phone alias di ciascun agente trovare il server con il minor numero di agenti non-remoti collegati in esso sarà utilizzato. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-pllb_grouping_limit">
<BR>
<B>PLLB Raggruppamento Limit -</B> Telefono Accedi Load Balancing Limit Raggruppamento. Se Raggruppamento PLLB è impostato su CASCATA questa impostazione determinerà il numero di agenti accettabile in ogni server per questa campagna. Il valore predefinito è 50.

<BR>
<A NAME="campaigns-crm_popup_login">
<BR>
<B>CRM Popup Login -</B> Se impostato a Y, il CRM Popup indirizzo viene utilizzato per aprire una nuova finestra il login agente per questa campagna. Di default è N.

<BR>
<A NAME="campaigns-crm_login_address">
<BR>
<B>CRM Popup Indirizzo -</B> L-indirizzo web di una pagina di login CRM, può avere variabili popolato come l-indirizzo web form, con i VAR nella parte anteriore e l-utilizzo - A user_custom_one - - B - per definire le variabili.

<BR>
<A NAME="campaigns-start_call_url">
<BR>
<B>Start Call URL -</B> Questo web indirizzo URL non è visto che l-agente, ma è chiamato ogni volta che una chiamata viene inviata ad un agente, se viene popolato. Utilizza le stesse variabili, come i campi del modulo web e script. Questo URL non può essere un percorso relativo. L-URL Start non funziona per le chiamate in linea manuale. Predefinita è vuota.

<BR>
<A NAME="campaigns-dispo_call_url">
<BR>
<B>Dispo Call URL -</B> Questo web indirizzo URL non è visto che l-agente, ma è chiamato ogni volta che una chiamata è dispositioned da un agente, se viene popolato. Utilizza le stesse variabili, come i campi del modulo web e script. dispo e talk_time sono le variabili che si possono utilizzare per recuperare l-agente definito disposizione per la chiamata e il tempo effettivo di conversazione in secondi della telefonata. Questo URL non può essere un percorso relativo. Predefinita è vuota.

<BR>
<A NAME="campaigns-na_call_url">
<BR>
<B>Nessun agente URL chiamata -</B> Questo indirizzo URL web non si vede l'agente, ma se è popolato è chiamato ogni volta che viene appeso a una chiamata che non è gestito da un agente verso l'alto o trasferiti. Utilizza le stesse variabili i campi del modulo web e script. dispo può essere utilizzato per recuperare il sistema definito disposizione per la chiamata. Questo URL non può essere un percorso relativo. Il valore predefinito è vuoto.

<BR>
<A NAME="campaigns-agent_allow_group_alias">
<BR>
<B>Gruppo Alias ammessi - </B> Se si desidera consentire agli agenti di utilizzare il vostro gruppo alias quindi è necessario impostare questo gruppo Y. Alias sono spiegate più in Admin sezione, che consentono di selezionare diversi agenti callerIDs manuale per le chiamate in uscita che essi possono posto. Il valore di default è N.

<BR>
<A NAME="campaigns-default_group_alias">
<BR>
<B>Predefinito Gruppo Alias - </B> Se si hanno consentito Gruppo Alias allora questo è il gruppo alias che viene selezionato per impostazione predefinita quando prima lagente sceglie di utilizzare un gruppo di Alias per un manuale di chiamata in uscita. Il valore predefinito è vuoto o NESSUNO.

<BR>
<A NAME="campaigns-view_calls_in_queue">
<BR>
<B>Agente chiede View in coda -</B> Se impostato a nulla, ma NESSUNO, gli agenti saranno in grado di visualizzare i dettagli sulle chiamate che sono in attesa in coda nelle loro schermo agente. Se impostato su un valore numerico, le chiamate visualizzato sarà limitata al numero selezionato. Di default è NESSUNO.

<BR>
<A NAME="campaigns-view_calls_in_queue_launch">
<BR>
<B>Visualizzazione delle chiamate in coda Launch -</B> Questa impostazione, se impostato su AUTO avranno la chiede in cornice della coda mostra fino al momento del login da parte dell-agente nella schermata di agente. Di default è MANUALE.

<BR>
<A NAME="campaigns-grab_calls_in_queue">
<BR>
<B>Agente chiede Grab in coda -</B> Questa opzione se impostata a Y permetterà all-agente di selezionare la chiamata che vogliono prendere dalle chiamate in coda visualizzazione facendo clic su di esso, mentre in pausa. Gli agenti saranno in grado di afferrare le chiamate in entrata o chiamate il trasferimento, non le chiamate in uscita. Di default è N.

<BR>
<A NAME="campaigns-call_requeue_button">
<BR>
<B>Call Agent Re-Queue Button -</B> Questa opzione se impostata a Y aggiungerà il bottone ri-cliente Coda alla schermata di agente, l-agente che consente di inviare la chiamata in una coda AGENTDIRECT che è riservata per il solo agente. Di default è N.

<BR>
<A NAME="campaigns-pause_after_each_call">
<BR>
<B>Agente di pausa dopo ogni chiamata -</B> Questa opzione se impostata a Y si fermerà l-agente, dopo ogni chiamata automaticamente. Di default è N.

<BR>
<A NAME="campaigns-pause_after_next_call">
<BR>
<B>Agente Pausa Dopo Pross.selezione link -</B> Questa opzione, se attivata mostrerà un link sullo schermo dell'agente che vi permetterà l'agente va in pausa automaticamente dopo che riagganciare la chiamata successiva. Disabilitata di default.

<BR>
<A NAME="campaigns-blind_monitor_warning">
<BR>
<B>Monitor Warning cieca -</B> Questa opzione, se attivata farà l'agente di sapere in vari modi opzionali se vengono cieco controllati da qualcuno. DISABILI significa che questa funzione non è attiva, ALERT solo un avviso pop-up sullo schermo agente, AVVISO pubblicherà una nota che rimane sullo schermo agente finché theya re monitorato, AUDIO giocherà il nome del file definito di seguito quando un agente sta cominciando a essere monitorati e le altre opzioni sono combibnations delle suddette opzioni. Predefinito è DISABLED.

<BR>
<A NAME="campaigns-blind_monitor_message">
<BR>
<B>Cieco Monitor Notice -</B> Questo è il messaggio che verrà visualizzato sullo schermo l'agente mentre vengono monitorati se l'opzione AVVISO è selezionata. Il valore predefinito è -Someone is blind monitoring your session-.

<BR>
<A NAME="campaigns-blind_monitor_filename">
<BR>
<B>Cieco Monitor Filename -</B> Questo è il file audio che verrà riprodotto nella sessione agenti all'inizio del monitoraggio che qualcuno li accecano. Questo messaggio sarà giocato per tutta la sessione anche se il cliente è presente. Il valore predefinito è vuoto.

<BR>
<A NAME="campaigns-max_inbound_calls">
<BR>
<B>Chiamate Max entrata -</B> Se questa impostazione è impostata su un numero maggiore di 0, allora sarà il numero massimo di chiamate entranti un agente in grado di gestire tutti i gruppi in entrata in un giorno. Se l agente raggiunge il numero massimo di chiamate in entrata, allora non saranno in grado di selezionare i gruppi in entrata per prendere le chiamate dal fino al giorno successivo. Questa impostazione può essere sovrascritta dall impostazione utente con lo stesso nome. Il default è 0 per disabili.






<BR><BR><BR><BR>

<B><FONT SIZE=3>LISTE TAVOLO</FONT></B><BR><BR>
<A NAME="lists-list_id">
<BR>
<B>Identificacio`n de la lista -</B> Úste es el nombre nume`rico de la lista, e`l no es editable despue`s de la sumisio`n inicial, debe contener solamente números y debe estar entre 2 y 8 caracteres en longitud. Must be a number greater than 100.

<BR>
<A NAME="lists-list_name">
<BR>
<B>Nombre de la lista -</B> esta es la descripcio`n de la lista, debe estar entre 2 y 20 caracteres en longitud.

<BR>
<A NAME="lists-list_description">
<BR>
<B>Descripcio`n de la lista -</B> este es el campo de la nota para la lista,e`l es opcional.

<BR>
<A NAME="lists-list_changedate">
<BR>
<B>Fecha del cambio de la lista -</B> esta es la vez última que los ajustespara esta lista fueron modificados de cualquier manera.

<BR>
<A NAME="lists-list_lastcalldate">
<BR>
<B>Fecha de la llamada del último de la lista -</B> esta es la vez últimaque conduce fue marcado de esta lista.

<BR>
<A NAME="lists-campaign_id">
<BR>
<B>Campaña -</B> esta es la campaña que esta lista pertenece a. Una lista se puede marcar solamente en una sola campaña contemporáneamente.

<BR>
<A NAME="lists-active">
<BR>
<B>Activo -</B> esto define si la lista debe ser marcada encendido o no.

<BR>
<A NAME="lists-reset_list">
<BR>
<B>Reajuste el Conducir-Llamar-Estado para esta lista -</B> esto reajusta todos los plomos en esta lista a N para "no llamado puesto que reajuste pasado" y significa que cualquier plomo puede ahora ser llamado si es el estado derecho según lo definido en la pantalla de la campaña.

<BR>
<A NAME="lists-reset_time">
<BR>
<B>Reset Times -</B> Questo campo vi permette di mettere a volte, separati da un trattino-, che questa lista sarà automaticamente reimpostato dal sistema. I tempi devono essere in formato 24 ore senza punteggiatura, ad esempio 0.800-1.700 sarebbe reimpostare l-elenco di 8 e 5 tutti i giorni. Di default è vuota.

<BR>
<A NAME="lists-expiration_date">
<BR>
<B>Data di Scadenza -</B> Questa opzione consente di impostare la data dopo la quale conduce in questa lista non sarà consentito di essere auto-composto o manuale-list-composto dal sistema. Il default è 2099-12-31.

<BR>
<A NAME="lists-audit_comments">
<BR>
<B>Audit Commenti -</B> Questa opzione consente commenti di essere spostati in una tabella di controllo. Non è più modificabile, ma visualizzabili con la data-ora-creatore di ogni commento. Default e N. Questa è una parte del Controllo Qualità Add-On.

<BR>
<A NAME="lists-agent_script_override">
<BR>
<B>Agent Script Override -</B> Se questo campo è impostato, questo sarà lo script che l-agente vede sullo schermo del proprio posto di script campagna quando il piombo è da questo elenco. Di default non è impostato.

<BR>
<A NAME="lists-campaign_cid_override">
<BR>
<B>Campagna CID Override -</B> Se questo campo è impostato, andranno a sostituire la campagna CallerID che è impostato per le chiamate che vengono posti a porta in questo elenco. Di default non è impostato.

<BR>
<A NAME="lists-am_message_exten_override">
<BR>
<B>Messaggio Segreteria Telefonica Override -</B> Se questo campo è impostato, questo ignorare il messaggio di Segreteria insieme nella campagna per i clienti in questo elenco. Di default non è impostato. 
<BR>
<A NAME="lists-drop_inbound_group_override">
<BR>
<B>Drop Inbound Group Override -</B> Se questo campo è impostato, in questo gruppo verranno utilizzati per le chiamate in uscita all-interno di questa lista che scendono da campagna in uscita, invece di cadere in gruppo insieme nella schermata di dettaglio della campagna. Di default non è impostato.

<BR>
<A NAME="lists-web_form_address">
<BR>
<B>Web Form Override -</B> Questo è l indirizzo personalizzato che cliccando sul pulsante Web Form nella schermata agente vi porterà per le chiamate che arrivano su questa lista. Se si desidera utilizzare i campi personalizzati in un indirizzo web form, è necessario aggiungere & CF_uses_custom_fields = Y come parte del tuo URL.

<BR>
<A NAME="lists-xferconf_a_dtmf">
<BR>
<B>XFER-Conf Numero Override -</B> Questi cinque campi consentono di ignorare le impostazioni predefinite Conferenza di trasferimento del numero, quando la guida è da questo elenco. Predefinita è vuota.

<BR>
<A NAME="lists-inventory_report">
<BR>
<B>Inventory report -</B> Se nel verbale di inventario è abilitata sul sistema, questa opzione di verificare se questo elenco è incluso nella relazione oppure no. Il valore predefinito è Y per yes.

<BR>
<A NAME="lists-time_zone_setting">
<BR>
<B>Impostazione del fuso orario -</B> Questa opzione consente di impostare il metodo di mantenere l'attuale ricerca fuso orario per i cavi all'interno di questa lista. Questo processo viene fatto solo di notte quindi tutte le modifiche apportate non saranno immediati. COUNTRY_AND_AREA_CODE è l'impostazione predefinita, e utilizzerà il prefisso del paese e il prefisso interurbano del numero di telefono per determinare il fuso orario del piombo. Postal_Code utilizzerà il codice postale se disponibile, per determinare il fuso orario del piombo. NANPA_PREFIX funziona solo negli Stati Uniti e utilizzerà il prefisso e il prefisso del numero di telefono per determinare il fuso orario del piombo, ma questo non è abilitato di default nel sistema, in modo da assicurarsi di disporre dei dati prefisso NANPA caricati su il sistema prima di selezionare questa opzione. OWNER_TIME_ZONE_CODE utilizzerà lo standard abbraviation fuso orario caricato nel campo proprietario del piombo per determinare il fuso orario, negli esempi USA sono AST, EST, CST, MST, PST, AKST, HST. Questa funzione deve essere abilitata dall'amministratore di sistema potrà entrare in vigore.


<BR>
<A NAME="internal_list-dnc">
<BR>
<B>Lista DNC interno -</B> This Do Not Call list contains every lead that has been set to a status of DNC in the system. Through the LISTS - AGREGUE EL NÚMERO A DNC page you are able to manually add numbers to this list so that they will not be called by campaigns that use the internal DNC list. Vi è inoltre la possibilità di aggiungere porta alla campagna specifici DNC liste per le campagne che hanno loro. Se hai attiva l-opzione DNC insieme a AreaCode allora è anche possibile utilizzare il codice voci area jolly come questo 201XXXXXXX per bloccare tutte le chiamate al 201, quando attivata AreaCode.



<BR><BR><BR><BR>

<B><FONT SIZE=3>TABELLA INBOUND_GROUPS</FONT></B><BR><BR>
<A NAME="inbound_groups-group_id">
<BR>
<B>Identificacio`n de grupo -</B> Úste es el nombre corto del grupo de entrada, e`l no es editable despue`s de la sumisio`n inicial, no debe contener cualquier espacio y debe estar entre 2 y 20 caracteres en longitud.

<BR>
<A NAME="inbound_groups-group_name">
<BR>
<B>Nombre de grupo -</B> esta es la descripcio`n del grupo, debe estar entre 2 y 30 caracteres en longitud. No puede incluir rociadas -, plusses + o espacios .

<BR>
<A NAME="inbound_groups-group_color">
<BR>
<B>Colore Gruppo -</B> Questo è il colore che visualizza in app client agent quando arriva una chiamata su questo gruppo. Essa deve essere compresa tra 2 e 7 caratteri. Se questa è una definizione colore esadecimale è necessario ricordarsi di mettere un # all inizio della stringa o lo schermo dell agente non funziona correttamente.

<BR>
<A NAME="inbound_groups-active">
<BR>
<B>Attivo -</B> Questo determina se questo gruppo in ingresso è disponibile a prendere le chiamate. Se questo è impostato su inattivo poi After Hours azione sarà utilizzato su tutte le chiamate che vengono in esso.

<BR>
<A NAME="inbound_groups-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo gruppo in entrata, questo permette la visualizzazione di questa amministrazione in-gruppo limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo gruppo.

<BR>
<A NAME="inbound_groups-group_calldate">
<BR>
<B>In-Group Calldate -</B> Questa è l'ultima data e l'ora che una chiamata è stata rivolta a questo gruppo in entrata.

<BR>
<A NAME="inbound_groups-web_form_address">
<BR>
<B>Web Form -</B> Questo è l indirizzo personalizzato che cliccando sul pulsante Web Form nella schermata agente vi porterà per le chiamate che arrivano su questo gruppo. Se si desidera utilizzare i campi personalizzati in un indirizzo web form, è necessario aggiungere & CF_uses_custom_fields = Y come parte del tuo URL.

<BR>
<A NAME="inbound_groups-next_agent_call">
<BR>
<B>Llamada siguiente del agente -</B> esto se determina que` agente recibe la llamada siguiente que está disponible:
 <BR> &nbsp; - random: ordini del valore di aggiornamento casuale nella tabella live_agents
 <BR> &nbsp; - oldest_call_start: las o`rdenes por la vez última un agente fueron enviadas una llamada. Resultados en los agentes que reciben el número casi igual de llamadas cabalmente.
 <BR> &nbsp; - oldest_call_finish: las o`rdenes por la vez última un agente acabaron una llamada. El agente de AKA que espera lo más de largo posible recibe la primera llamada.
 <BR> &nbsp; - oldest_inbound_call_start: ordini per l'ultima volta che un agente è stato inviato una chiamata in ingresso. Risultati in agenti che ricevono lo stesso numero complessivo di chiamate.
 <BR> &nbsp; - oldest_inbound_call_finish: ordini per l'ultima volta che un agente finito una chiamata in ingresso. Agente AKA attesa più lunga riceve per la prima chiamata.
 <BR> &nbsp; - overall_user_level: ordinanze del user_level dell agente come definito nella tabella agli utenti una maggiore user_level riceveranno più chiamate.
 <BR> &nbsp; - inbound_group_rank: o`rdenes de la fila dada al agente para el grupode entrada específico. Lo más arriba posible a lo más bajo posible.
 <BR> &nbsp; - campaign_rank: o`rdenes de la fila dada al agente para la campaña. Lomás arriba posible a lo más bajo posible.
 <BR> &nbsp; - ingroup_grade_random: dà una probabilità maggiore di ottenere una chiamata agli agenti più alti graduati di in-group.
 <BR> &nbsp; - campaign_grade_random: dà una probabilità maggiore di ottenere una chiamata agli agenti più alti graduati per campagna.
 <BR> &nbsp; - fewest_calls: o`rdenes por el número de las llamadas recibidas por unagente para ese grupo de entrada específico. Menos llamadas primero.
 <BR> &nbsp; - fewest_calls_campaign: orders by the number of calls received by an agent for the campaign. Least calls first.
 <BR> &nbsp; - longest_wait_time: ordini per la quantità di tempo agente è stato attivamente in attesa di una chiamata.
 <BR> &nbsp; - ring_all: rings all available agents until one picks up the phone.
 <BR> NOTE: Per ring_all, gli agenti che utilizzano i telefoni che hanno su Enabled Hook agente avranno il loro anello di telefoni e il primo a rispondere riceverà la chiamata e le informazioni sullo schermo dell'agente. Dal momento che ring_all ignora i tempi di attesa agente e ranking e chiamerà tutti gli agenti che è disponibile per la coda, si consiglia di non utilizzare questo metodo per le code di grandi dimensioni. Quando si utilizza ring_all, gli agenti registrati con i telefoni che hanno l'Agente On Hook disabili dovranno utilizzare le chiamate nel pannello Coda e cliccare sul link CALL PRESA a prendere le chiamate in coda. La quantità di tempo gli agenti del telefono squillerà per ring_all è impostato su On-Hook impostazione squilli o il tempo più breve anello dei telefoni che saranno chiamati. Si consiglia di non utilizzare ring_all su code ad alto volume di chiamata, o le code con molti agenti. Il metodo ring_all è destinato ad essere utilizzato con solo pochi agenti e il volume chiamata basso in gruppi.
<BR>

<BR>
<A NAME="inbound_groups-on_hook_ring_time">
<BR>
<B>On-Hook squilli -</B> Questa opzione viene utilizzata solo per gli agenti che vengono registrati con i telefoni che hanno l'agente-on-hook funzione attivata. Questo è il numero di secondi che ogni tentativo di chiamata all'agente suonerà per fino a quando il sistema attende un secondo e iniziano a suonare i telefoni degli agenti disponibili di nuovo. Questo campo può essere ignorato se i telefoni degli agenti sono impostate per un tempo di anello inferiore che può essere necessario per evitare che le chiamate vengano inviate uno segreteria telefoni. Il valore predefinito è 15.

<BR>
<A NAME="inbound_groups-on_hook_cid">
<BR>
<B>On-Hook CID -</B> Questa opzione viene utilizzata solo per gli agenti che vengono registrati con i telefoni che hanno l'agente-on-hook funzione attivata. Questo è l'ID chiamante che apparirà sui loro telefoni agenti quando le chiamate vengono chiamata. GENERIC è un generico RINGAGENT00000000001 tipo di notifica. Ingroup mostrerà solo l'in-group la chiamata proviene. CUSTOMER_PHONE mostrerà solo il numero telefonico del cliente. CUSTOMER_PHONE_RINGAGENT mostrerà RINGAGENT_3125551212 con il RINGAGENT come parte del CID con il numero di telefono del cliente. CUSTOMER_PHONE_INGROUP mostrerà i primi 10 caratteri del gruppo in-seguito dal numero di telefono del cliente. Default is GENERIC.

<BR>
<A NAME="inbound_groups-queue_priority">
<BR>
<B>Queue Priority -</B> This setting is used to define the order in which the calls from this inbound group should be answered in relation to calls from other inbound groups.

<A NAME="inbound_groups-fronter_display">
<BR>
<B>Mostrar Fronter -</B> Questo campo determina se l agente in entrata dovrebbe avere il nome fronter - se c è - visualizzato nel campo Stato quando arriva la chiamata all agente.

<BR>
<A NAME="inbound_groups-ingroup_script">
<BR>
<B>Escritura de la campaña -</B> este menú permite que usted elija la escritura que aparecerá en la pantalla de los agentes para esta campaña. No seleccione NONE no demostrar ninguna escritura para esta campaña.

<BR>
<A NAME="inbound_groups-ignore_list_script_override">
<BR>
<B>Ignora Script Override List-</B> Questa opzione consente di ignorare l'ID elenco delle opzioni Script Override per le chiamate che entrano in questo In-Group. Impostare questo a Y ignorerà le impostazioni di script ID List. Il valore predefinito è N.

<BR>
<A NAME="inbound_groups-get_call_launch">
<BR>
<B>Consiga el lanzamiento de la llamada -</B> este menú permite que usted elija si usted desee automo`vil-lance la página en una ventana separada, auto-switch de la tela-forma a la lengüeta de la ESCRITURA o no haga nada cuando una llamada se envía al agente para esta campaña. Se i campi elenco personalizzati sono attivati ​​sul vostro sistema, FORM si aprirà la scheda FORM al momento della connessione di una chiamata a un agente.

<BR>
<A NAME="inbound_groups-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Questi quattro campi permettere di avere due insiemi di Transfer Conference e preset DTMF. Quando viene caricato la chiamata o campagna, lo schermo agente mostrerà due pulsanti sulla cornice trasferimento conferenze e auto-popolano il numero-to-dial e campi invio-DTMF quando viene premuto. Se si desidera consentire trasferimenti consultive, un fronter a una più stretta, hanno l agente usa la casella di controllo CONSULTIVO, che non funziona per il terzo schermo agente parti chiamate consultivi. Per coloro che hanno solo l agente clicca il quadrante con pulsante di clienti. Poi l agente può semplicemente lasciare-3 VIE-CALL e passare alla loro chiamata successiva. Se si desidera consentire i trasferimenti ciechi dei clienti di uno script AGI per la registrazione o un IVR, quindi inserire AXFER nel campo numero-to-dial. È inoltre possibile specificare un estensione personalizzata dopo la AXFER, per esempio se si vuole fare una chiamata a un IVR speciale è stato impostato per estensione 83900 si dovrebbe mettere AXFER83900 nel campo numero-to-dial.

<BR>
<A NAME="inbound_groups-timer_action">
<BR>
<B>Timer azione -</B> Questa funzione consente di attivare azioni dopo un certo periodo di tempo. D1 e D2 le opzioni di chiamata lancerà un appello per il trasferimento Conferenza preset Numero e li trasmette alla sessione di agente, questo è di solito utilizzato per semplici applicazioni IVR AGI convalida o semplicemente a svolgere un messaggio pre-registrato. FORM aprirà l-indirizzo web form. MESSAGE_ONLY semplicemente visualizzare il messaggio che è nel campo sottostante. NESSUNO sarà disabilitare questa funzione ed è il default. Questa impostazione sovrascrive le impostazioni della campagna. CHIUDI si bloccherà la chiamata quando il timer è attivato, CALLMENU invierà la chiamata al Menu chiamata specificato nel campo Destinazione Timer azione, ESTENSIONE invierà la chiamata per l'estensione che è specificato nel campo Destinazione Timer azione, IN_GROUP invierà la chiamata alla In-gruppo specificato nel campo Destinazione Timer azione.

<BR>
<A NAME="inbound_groups-timer_action_message">
<BR>
<B>Timer azione Message -</B> Questo è il messaggio che appare sullo schermo agente al momento l-azione Timer è attivato.

<BR>
<A NAME="inbound_groups-timer_action_seconds">
<BR>
<B>Timer azione Secondi -</B> Questa è la quantità di tempo dopo la chiamata è collegata al cliente che l-azione del timer viene attivato. Predefinito è -1, che è anche attivo.

<BR>
<A NAME="inbound_groups-timer_action_destination">
<BR>
<B>Timer Azione Destination -</B> Questo campo è dove si specifica il Menu di chiamata, l'estensione o In-gruppo che si desidera la chiamata inviata se l'azione ora è impostato su CALLMENU, l'estensione o IN_GROUP. Il valore predefinito è vuoto.

<BR>
<A NAME="inbound_groups-drop_call_seconds">
<BR>
<B>Call Drop Secondi - </B> Il numero di secondi di una chiamata rimane in coda, prima di essere considerato un DROP.

<BR>
<A NAME="inbound_groups-drop_action">
<BR>
<B>Goccia dazione - </B> Questo menu consente di scegliere che cosa succede a una chiamata quando si è in attesa di più di quello che si trova nel campo Secondi Call Drop. Hangup semplicemente riagganciare la chiamata, inviare il messaggio di chiamare il Gota Extensions che lei ha definito di seguito, segreteria invierà la chiamata alla casella vocale che è stato definito sotto IN_GROUP e invia la chiamata in ingresso al gruppo che è definito qui di seguito. VMAIL_NO_INST invierà la chiamata alla casella di posta vocale che si è definito di seguito e non giocherà le istruzioni dopo il messaggio vocale.

<BR>
<A NAME="inbound_groups-drop_exten">
<BR>
<B>Gota Extensions - </B> In caso di caduta di azione è fissato al messaggio, questa è la linea che il piano di estensione invito sarà inviato a se raggiunge Call Drop Secondi. Per AGENTDIRECT in gruppi, se l'utente non è disponibile, si può mettere AGENTEXT in questo campo e il sistema cercherà l'utente personalizzata cinque campo e inviare la chiamata a quel numero dialplan.

<BR>
<A NAME="inbound_groups-voicemail_ext">
<BR>
<B>Casella Vocale -</B> If Drop Action is set to VOICEMAIL, the call DROP would instead be directed to this voicemail box to hear and leave a message. In un AGENTDIRECT in gruppo, l'impostazione a AGENTVMAIL selezionerà il voicemail ID utente da utilizzare.

<BR>
<A NAME="inbound_groups-drop_inbound_group">
<BR>
<B>Trasferimento Drop Group - </B> In caso di caduta di azione è fissato a IN_GROUP, la chiamata verrà inviata a questo gruppo in entrata se si raggiunge il Call Drop Secondi.

<BR>
<A NAME="inbound_groups-drop_callmenu">
<BR>
<B>Menu a discesa chiamata -</B> Se l'azione è impostata su Goccia CALLMENU, la chiamata verrà inviato a questo menu chiamata, se arriva secondi di chiamata Goccia.

<BR>
<A NAME="inbound_groups-action_xfer_cid">
<BR>
<B>Azione Transfer CID -</B> Usato per Goccia, Dopo mezzanotte e le azioni non-agent-no-code. Questo è il numero di ID chiamante che la chiamata utilizza prima di essere trasferito al menu estensioni, sms, segreteria telefonica e chiamate. È possibile utilizzare CLIENTE in questo campo per utilizzare il numero di telefono del cliente, o CAMPAGNA utilizzare il numero di campagna id chiamante primo permesso. L impostazione predefinita è CLIENTE. Se questo è un invito che andrà a un menu chiamata e poi di nuovo in un gruppo, si consiglia di utilizzare CUSTOMERCLOSER in questo campo, ed inoltre è necessario impostare il metodo Handle In-Group nel menu chiamata a CLOSER.

<BR>
<A NAME="inbound_groups-call_time_id">
<BR>
<B>Tiempo de la llamada -</B> este es el esquema del tiempo de la llamada autilizar para este grupo de entrada. Tenga presente que el tiempoestá basado en el tiempo del servidor. El defecto es 24hours.

<BR>
<A NAME="inbound_groups-after_hours_action">
<BR>
<B>After Hours Azione - </B> Loperazione da eseguire se si tratta di ore dopo, come definito nel tempo per la chiamata in entrata questo gruppo. Hangup hangup immediatamente la chiamata, si MESSASGE riprodurre il file in After Hours Messaggio Filenam campo, ESTENSIONE invierà la chiamata al After Hours Estensione nel dialplan Casella Vocale e invia la chiamata alla casella vocale di cui il settore After Hours Casella Vocale , IN_GROUP invierà la chiamata in ingresso al gruppo selezionato nel gruppo After Hours Trasferimento selezionare lista. Il valore di default è MESSAGGIO. VMAIL_NO_INST invierà la chiamata alla casella di posta vocale che si è definito di seguito e non giocherà le istruzioni dopo il messaggio vocale.

<BR>
<A NAME="inbound_groups-after_hours_message_filename">
<BR>
<B>Despue`s del nombre de fichero del mensaje de las horas -</B> el archivoaudio situado en el servidor que se jugará si la accio`n se fija alMENSAJE. El defecto es VM-ADIO`S

<BR>
<A NAME="inbound_groups-after_hours_exten">
<BR>
<B>Despue`s de la extension de las horas -</B> la extension dialplan paraenviar la llamada si la accio`n se fija a la Extension. El defecto es8300. Per AGENTDIRECT in gruppi, si può mettere AGENTEXT in questo campo e il sistema cercherà l'utente personalizzata cinque campo e inviare la chiamata a quel numero dialplan.

<BR>
<A NAME="inbound_groups-after_hours_voicemail">
<BR>
<B>Despue`s de las horas Casella Vocale -</B> la caja del voicemail para enviar lallamada si la accio`n se fija a VOICEMAIL. In un AGENTDIRECT in gruppo, l'impostazione a AGENTVMAIL selezionerà il voicemail ID utente da utilizzare.

<BR>
<A NAME="inbound_groups-afterhours_xfer_group">
<BR>
<B>After Hours Trasferimento Group - </B> Se After Hours azione è destinata a IN_GROUP, la chiamata verrà inviata a questo gruppo in entrata se si entra nel gruppo in al di fuori del regime di chiamata tempo definito per il gruppo in.

<BR>
<A NAME="inbound_groups-after_hours_callmenu">
<BR>
<B>After Hours chiamate Menu -</B> Se After Hours azione è impostata su CALLMENU, la chiamata verrà inviata a questo Menu chiamata se si entra in gruppo al di fuori del regime di tempo definito per la chiamata in gruppo.

<BR>
<A NAME="inbound_groups-no_agent_no_queue">
<BR>
<B>N. agenti n. Queueing -</B> Se questo campo è impostato su Y o NO_PAUSED allora non le chiamate saranno messi in coda per questo gruppo se non ci sono agenti di login e le chiamate andrà l-agente No No Action coda. L-opzione NO_PAUSED non anche inviare i chiamanti in coda se non ci sono solo agenti in pausa in gruppo. Di default è N. In un AGENTDIRECT in gruppo, l'impostazione a AGENTVMAIL selezionerà il voicemail ID utente da utilizzare. Si può anche mettere AGENTEXT in questo campo se è impostato per estensione e il sistema cercherà l'utente personalizzata cinque campo e inviare la chiamata a quel numero dialplan. Se impostato su N, le chiamate a mettersi in fila, anche se non ci sono agenti connessi e in modo da tener chiamate da questo gruppo.

<BR>
<A NAME="inbound_groups-no_agent_action">
<BR>
<B>Nessun agente Nessuna azione di coda -</B> Se Nessun agente n. coda è attivato, allora questo campo definisce dove la chiamata andrà se non ci sono agenti in In-Group. Di default è il messaggio, questo gioca il file audio nel campo di azione di valore e poi riattacca.

<BR>
<A NAME="inbound_groups-no_agent_action_value">
<BR>
<B>Nessun agente n. coda azione Value -</B> Questo è il valore per l-azione di cui sopra. Predefinito è nbdy-avail-to-take-call|vm-goodbye.

<BR>
<A NAME="inbound_groups-max_calls_method">
<BR>
<B>Max Chiama il metodo -</B> Questa opzione può consentire la massima funzionalità concorrente chiede che tale in-gruppo. Se impostato a TOTAL, quindi il numero totale di chiamate gestite dagli agenti e in coda in questo gruppo non potrà superare il Conte Max chiama il numero di linee come di seguito definiti. Se impostato su IN_QUEUE, quindi se il numero di chiamate in coda in attesa per gli agenti non potranno superare il Conte Max chiamate non importa quante chiamate sono con gli agenti per questo in-gruppo. Predefinito è DISABLED.

<BR>
<A NAME="inbound_groups-max_calls_count">
<BR>
<B>Chiamate Conte Max -</B> Questa opzione deve essere superiore a 0 se si desidera utilizzare la funzione di chiamate Max metodo. Il default è 0.

<BR>
<A NAME="inbound_groups-max_calls_action">
<BR>
<B>Max chiamate Azione -</B> Questa è l'azione da intraprendere se il Max Chiama il metodo è abilitata e il numero di chiamate superiore a quello che si trova al di sopra del Conte Max Chiamate impostazione. Le chiamate oltre tale importo verrà inviato sia l'azione DROP, l'azione AFTERHOURS o l'azione NO_AGENT_NO_QUEUE e verrà registrato come uno status MAXCAL con una ragione hangup MaxCalls. Il valore predefinito è NO_AGENT_NO_QUEUE.

<BR>
<A NAME="inbound_groups-welcome_message_filename">
<BR>
<B>Nombre de fichero agradable del mensaje -</B> el archivo audio situado enel servidor que se jugará cuando viene la llamada adentro. Si sistemaa ---NONE --- entonces no se jugará ningún mensaje. El defecto es---NONE ---. Questo campo, come con gli altri campi audio in In-gruppi, con l-eccezione della Agente Alert Filename, può avere più file audio giocato se si mette un tubo di elenco separato di file audio nel campo.

<BR>
<A NAME="inbound_groups-play_welcome_message">
<BR>
<B>Lettura del messaggio di benvenuto - </B> Queste impostazioni selezionate, quando per la riproduzione del messaggio di benvenuto definito, SEMPRE svolgerà ogni volta, non potrà mai mai giocare, IF_WAIT_ONLY solo riprodurre il messaggio di benvenuto, se la chiamata non vada immediatamente ad un agente , e YES_UNLESS_NODELAY avranno sempre il messaggio di benvenuto NO_DELAY a meno che limpostazione è attivata. Il valore di default è SEMPRE.

<BR>
<A NAME="inbound_groups-moh_context">
<BR>
<B>Música en contexto del asimiento -</B> la música en el contexto delasimiento para utilizar cuando colocan al cliente en asimiento. Eldefecto es defecto.

<BR>
<A NAME="inbound_groups-onhold_prompt_filename">
<BR>
<B>En el nombre de fichero del aviso del asimiento -</B> el archivo audiosituado en el servidor que se jugará en un intervalo regular cuandoel cliente está en asimiento. El defecto es generic_hold. Estearchivo audio DEBE estar 9 segundos o menos en longitud.

<BR>
<A NAME="inbound_groups-prompt_interval">
<BR>
<B>En el intervalo del aviso del asimiento -</B> la longitud de la hora ensegundos de esperar antes de jugar encendido el aviso del sostener. Eldefecto es 60. Per disattivare il prompt Hold On, impostare lintervallo a 0.

<BR>
<A NAME="inbound_groups-onhold_prompt_no_block">
<BR>
<B>In attesa Prompt No Block -</B> Impostando questa opzione su Y consentire le chiamate in fila dietro una chiamata in attesa in cui la richiesta sta giocando per andare ad un agente, se uno diventa disponibile durante la riproduzione del messaggio. Mentre l'On Hold messaggio Nome Prompt sta giocando a un cliente che non possono essere inviati a un agente. Il valore predefinito è N.

<BR>
<A NAME="inbound_groups-onhold_prompt_seconds">
<BR>
<B>In attesa Secondi Prompt -</B> Questo campo deve essere impostato il numero di secondi che l'On Hold Filename Prompt gioca. Il valore predefinito è 10.

<BR>
<A NAME="inbound_groups-play_place_in_line">
<BR>
<B>Gioca Luogo in Linea - </B> Questo definisce se il chiamante ascolterà il loro posto in linea momento in cui entra nella coda così come quando sentono il announcemend. Il valore di default è N.

<BR>
<A NAME="inbound_groups-play_estimate_hold_time">
<BR>
<B>Gioca stimato Hold Time - </B> Questo definisce se il chiamante ascolterà il tempo stimato tenere prima di essere trasferiti a un agente. Il valore di default è N. Se il cliente è in attesa e sente questo messaggio stimato tempo attesa, il tempo minimo che verrà riprodotto è di 15 secondi.

<BR>
<A NAME="inbound_groups-calculate_estimated_hold_seconds">
<BR>
<B>Calcolare Secondi Attesa stimato -</B> Definisce il numero di secondi nella coda, che il cliente dovrà attendere prima il tempo di attesa stimato sarà calcolato ed eventualmente riprodotti. Minima è di 3 secondi, anche se impostato inferiore a 3. Il default è 0.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_filename">
<BR>
<B>Tenere minimo stimato Filename Tempo -</B> Se il tempo di attesa stimato è attivo e si calcola sia pari o inferiore al minimo di 15 secondi, allora questo file sarà tempestivamente svolto invece l'annuncio di default. Il valore predefinito è vuoto per inattiva.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_no_block">
<BR>
<B>Tempo di attesa stimato minimo Prompt No Block -</B> Se il tempo di attesa stimato è attiva e la stima del tempo minimo di attesa campo Nome del file è al di sopra compilata, quindi questa opzione per consentire le chiamate in fila dietro a una chiamata in cui la richiesta sta giocando per andare ad un agente, se uno diventa disponibile durante la riproduzione del messaggio . Mentre la richiesta sta giocando a un cliente che non possono essere inviati ad un agente. Il valore predefinito è N.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_seconds">
<BR>
<B>Tempo di attesa stimato secondi minimo Prompt -</B> Questo campo deve essere impostato il numero di secondi che il tempo stimato di attesa minima richiesta Nome gioca. Il valore predefinito è 10.

<BR>
<A NAME="inbound_groups-wait_time_option">
<BR>
<B>Attendere Opzione Tempo -</B> Ciò consente di offrire ai clienti opzioni per lasciare la coda se il loro tempo di attesa è finita la quantità di secondi specificato qui di seguito. Il valore predefinito è NONE. Se una delle opzioni PRESS_ è selezionata, si svolgerà il Filename Press definito di seguito e dare al cliente la possibilità di premere 1 sul telefono a lasciare la coda ed eseguire l'opzione selezionata. L'opzione PRESS_STAY invierà al cliente alla coda senza perdere il loro posto in linea.

<BR>
<A NAME="inbound_groups-wait_time_second_option">
<BR>
<B>Attendere Opzione Second Time -</B> Stesso come il primo campo Opzione Tempo di attesa al di sopra, tranne questo controllerà per il cliente premendo il tasto 2. Il valore predefinito è NONE. Se nessun tempo di attesa prima opzione è selezionata questa opzione non sarà disponibile.

<BR>
<A NAME="inbound_groups-wait_time_third_option">
<BR>
<B>Attendere Opzione terza volta -</B> Stesso come il primo campo Opzione Tempo di attesa al di sopra, tranne questo controllerà per il cliente preme il tasto 3. Il valore predefinito è NONE. Se non seconda opzione tempo di attesa è selezionata questa opzione non sarà disponibile.

<BR>
<A NAME="inbound_groups-wait_time_option_seconds">
<BR>
<B>Attendere seconds Tempo -</B> Se il tempo di attesa opzione è impostata a nulla, ma NONE, questo è il numero di secondi che il cliente ha atteso in coda che attiveranno le opzioni di tempo di attesa. Il valore predefinito è 120 secondi.

<BR>
<A NAME="inbound_groups-wait_time_option_exten">
<BR>
<B>Attendere Opzione proroga -</B> Se il tempo di attesa opzione è impostato PRESS_EXTEN, questa è l'estensione dialplan che la chiamata sarà inviata se il cliente preme il tasto opzione presentati con l'opzione. Per AGENTDIRECT in gruppi, si può mettere AGENTEXT in questo campo e il sistema cercherà l'utente personalizzata cinque campo e inviare la chiamata a quel numero dialplan.

<BR>
<A NAME="inbound_groups-wait_time_option_callmenu">
<BR>
<B>Attendere Opzione Callmenu Tempo -</B> Se il tempo di attesa opzione è impostata su PRESS_CALLMENU, questo è il Menu di chiamata che la chiamata verrà inviato se il cliente preme il tasto Opzione quando presentati con l'opzione.

<BR>
<A NAME="inbound_groups-wait_time_option_voicemail">
<BR>
<B>Attendere Opzione Casella Vocale Tempo -</B> Se il tempo di attesa opzione è impostata su PRESS_VMAIL, questa è la casella vocale che la chiamata verrà inviato se il cliente preme il tasto Opzione quando presentati con l'opzione. In un AGENTDIRECT in gruppo, l'impostazione a AGENTVMAIL selezionerà il voicemail ID utente da utilizzare.

<BR>
<A NAME="inbound_groups-wait_time_option_xfer_group">
<BR>
<B>Attendere Opzione Transfer Time In-Group -</B> Se il tempo di attesa opzione è impostata su PRESS_INGROUP, questo è il gruppo in entrata che la chiamata verrà inviato se il cliente preme il tasto Opzione quando presentati con l'opzione.

<BR>
<A NAME="inbound_groups-wait_time_option_press_filename">
<BR>
<B>Attendere opzione filename Tempo Press -</B> Se il tempo di attesa opzione è impostata su una delle opzioni PRESS_, questo è il prompt nome del file che viene riprodotto se il tempo di attesa dei clienti supera i Secondi opzione di Wait dare al cliente la possibilità di premere 1, 2 o 3 sul loro telefono cellulare per l'esecuzione i selezionati tempo di attesa premere Opzioni. E 'molto importante includere le opzioni nel file audio per tutte le opzioni selezionate tempo di attesa, e che la lunghezza del file audio in secondi è propriamente definito nel campo Nome file Seconds sotto o ci saranno problemi. L'impostazione predefinita è di-essere-chiamato-back.

<BR>
<A NAME="inbound_groups-wait_time_option_no_block">
<BR>
<B>Attendere Opzione Press Tempo No Block -</B> Impostando questa opzione su Y consentire le chiamate in fila dietro una chiamata in cui il tempo di attesa Opzione Press Nome richiesta sta giocando per andare ad un agente, se uno diventa disponibile durante la riproduzione del messaggio. Mentre il tempo di attesa Opzione messaggio Press Filename sta giocando a un cliente che non possono essere inviati a un agente. Il valore predefinito è N.

<BR>
<A NAME="inbound_groups-wait_time_option_prompt_seconds">
<BR>
<B>Attendere opzione di secondi Nome del file Stampa -</B> Questo campo deve essere impostato il numero di secondi che il tempo di attesa Nome Opzione Press gioca. Il valore predefinito è 10.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_filename">
<BR>
<B>Attendere opzione Time After Nome Press -</B> Se il tempo di attesa opzione è impostata su una delle opzioni PRESS_, questo è il prompt nome del file che viene riprodotto dopo che il cliente ha premuto 1, 2 o 3.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_list_id">
<BR>
<B>Attendere Opzione Tempo ID Callback List -</B> Se il tempo di attesa opzione è impostata su PRESS_CID_CALLBACK, questo è l'ID List la chiamata viene aggiunto come un nuovo capo se il cliente preme il tasto Opzione quando presentati con l'opzione.

<BR>
<A NAME="inbound_groups-wait_hold_option_priority">
<BR>
<B>Attendere Tenere Priority Opzione -</B> Se entrambe le opzioni Stima tempo di mantenimento e attendere opzioni di tempo sono attive, questa impostazione definisce se uno, l'altra o entrambe queste funzioni sono attive. Ad esempio, se il tempo di attesa stimato opzione è impostata su 360, l'opzione di tempo di attesa è impostato a 120 e il cliente è in attesa di 120 secondi e ci sono ancora 400 secondi il tempo di attesa stimato, allora sono entrambe attive nello stesso momento e questa impostazione verrà controllato per vedere quali opzioni verranno offerti. Il valore predefinito è aspettare solo.

<BR>
<A NAME="inbound_groups-hold_time_option">
<BR>
<B>Estimated Tenere Opzione Tempo - </B> Questo ti permette di specificare linstradamento della chiamata, se la stima del tempo di attesa è di oltre limporto di secondi specificato in appresso. Il valore di default è NESSUNO. Se una delle opzioni PRESS_ è selezionata, si svolgerà il Filename Press definito di seguito e dare al cliente la possibilità di premere 1 sul telefono a lasciare la coda ed eseguire l'opzione selezionata.

<BR>
<A NAME="inbound_groups-hold_time_second_option">
<BR>
<B>Tenere Opzione Second Time -</B> Stesso come il primo campo Opzione Tempo di attesa al di sopra, tranne questo si verifica per il cliente premendo il tasto 2. Il valore predefinito è NONE. Se non prima opzione Hold Time è selezionata questa opzione non sarà disponibile.

<BR>
<A NAME="inbound_groups-hold_time_third_option">
<BR>
<B>Tenere Opzione terza volta -</B> Stesso come il primo campo Opzione Tempo di attesa al di sopra, tranne questo si verifica per il cliente preme il tasto 3. Il valore predefinito è NONE. Se non seconda opzione Hold Time è selezionata questa opzione non sarà disponibile.

<BR>
<A NAME="inbound_groups-hold_time_option_seconds">
<BR>
<B>Tenere Opzione Tempo Secondi - </B> Se Hold Time opzione è impostata a nulla, ma NESSUNO, questo è il numero di secondi di attesa tempo stimato che il tempo di attivare lopzione. Il valore predefinito è 360 secondi.

<BR>
<A NAME="inbound_groups-hold_time_option_minimum">
<BR>
<B>Tenere Opzione Tempo minimo -</B> Se il tempo di attesa opzione abilitata, questo è il numero minimo di secondi la chiamata deve essere in attesa prima di essere presentato con l'opzione tempo di attesa. L'opzione tempo di attesa sarà immediatamente presentata in questo momento se il tempo di attesa stimato è superiore al Hold Time Value Opzione secondi. Il valore predefinito è 0 secondi.

<BR>
<A NAME="inbound_groups-hold_time_option_exten">
<BR>
<B>Tenere Opzione Tempo Estensione - </B> Se lopzione Hold Time è impostato per estensione, questo è il dialplan proroga che la richiesta sarà inviata a se la stima del tempo di attesa supera il tempo di Opzione Secondi. Per AGENTDIRECT in gruppi, si può mettere AGENTEXT in questo campo e il sistema cercherà l'utente personalizzata cinque campo e inviare la chiamata a quel numero dialplan.

<BR>
<A NAME="inbound_groups-hold_time_option_callmenu">
<BR>
<B>Tenere Opzione Callmenu Tempo -</B> Se il tempo di attesa opzione è impostata su CALL_MENU, questo è il Menu di chiamata che la chiamata verrà inviato se il tempo di attesa stimato supera i Attesa seconds Tempo.

<BR>
<A NAME="inbound_groups-hold_time_option_voicemail">
<BR>
<B>Tenere Tempo Opzione Segreteria - </B> Se Hold Time opzione è impostata a segreteria telefonica, questa è la casella di segreteria telefonica che la richiesta sarà inviata a se la stima del tempo di attesa supera il tempo di Opzione Secondi. In un AGENTDIRECT in gruppo, l'impostazione a AGENTVMAIL selezionerà il voicemail ID utente da utilizzare.

<BR>
<A NAME="inbound_groups-hold_time_option_xfer_group">
<BR>
<B>Tenere Opzione Tempo trasferimento in gruppo - </B> Se Hold Time opzione è impostata a IN_GROUP, questo è il gruppo in entrata che linvito sarà inviato a se la stima del tempo di attesa supera il tempo di Opzione Secondi.

<BR>
<A NAME="inbound_groups-hold_time_option_press_filename">
<BR>
<B>Tenere opzione filename Tempo Press -</B> Se il tempo di attesa opzione è impostata su una delle opzioni PRESS_, questo è il prompt nome del file che viene riprodotto se il tempo di attesa stimato supera i Attesa seconds tempo per dare al cliente la possibilità di premere 1 sul loro telefono cellulare per eseguire il tempo selezionato attesa Premere Opzione. E 'molto importante che questo file audio è di 10 secondi o meno o ci saranno problemi. L'impostazione predefinita è di-essere-chiamato-back.

<BR>
<A NAME="inbound_groups-hold_time_option_no_block">
<BR>
<B>Tenere Opzione Press Tempo No Block -</B> Impostando questa opzione su Y consentire le chiamate in fila dietro una chiamata in cui l'attesa opzione di stampa Nome richiesta sta giocando per andare ad un agente, se uno diventa disponibile durante la riproduzione del messaggio. Mentre il tempo di attesa Opzione messaggio Press Filename sta giocando a un cliente che non possono essere inviati a un agente. Il valore predefinito è N.

<BR>
<A NAME="inbound_groups-hold_time_option_prompt_seconds">
<BR>
<B>Tenere opzione di secondi Nome del file Stampa -</B> Questo campo deve essere impostato il numero di secondi che il tempo di attesa Nome Opzione Press gioca. Il valore predefinito è 10.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_filename">
<BR>
<B>Tenere Opzione Time After Nome Press -</B> Se il tempo di attesa opzione è impostata su una delle opzioni PRESS_ o CALLERID_CALLBACK, questo è il prompt nome del file che viene riprodotto dopo che il cliente ha premuto 1 o la chiamata è stato aggiunto alla lista callback.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_list_id">
<BR>
<B>Tenere Opzione Tempo richiamata Elenco ID - </B> Se lopzione Hold Time è impostato su CALLERID_CALLBACK, questo è lelenco ID la chiamata viene aggiunto come un nuovo piombo se la stima del tempo di attesa supera il tempo di Opzione Secondi.

<BR>
<A NAME="inbound_groups-agent_alert_exten">
<BR>
<B>Agente Alert Filename -</B> Il file audio si gioca a un agente di annunciare che una chiamata è venuta per l-agente. A non utilizzare questa funzione per impostare il valore predefinito è X. Ding.

<BR>
<A NAME="inbound_groups-agent_alert_delay">
<BR>
<B>La alarma del agente retrasa -</B> la longitud de la hora en milisegundosde esperar antes de enviar la llamada al agente despue`s de jugarencendido la extension alerta del agente. El defecto es 1000.

<BR>
<A NAME="inbound_groups-default_xfer_group">
<BR>
<B>Grupo de la transferencia del defecto -</B> este campo es el En-Grupo deldefecto que será seleccionado automáticamente cuando el agente va almarco de la transferir-conferencia en su interfaz del agente.

<BR>
<A NAME="inbound_groups-ingroup_recording_override">
<BR>
<B>In-Group Recording Override -</B> Questo campo consente l imperativo della impostazione di registrazione delle chiamate campagna. Questa impostazione può essere sovrascritta dall utente impostazione di sostituzione di registrazione. DISABILI non esclude l impostazione di registrazione campagna. MAI consente di disattivare la registrazione sul client. Ondemand è l impostazione predefinita e consente all agente di avviare e interrompere la registrazione, se necessario. ALLCALLS inizieranno la registrazione sul client ogni volta che viene inviata una chiamata a un agente. ALLFORCE avvierà la registrazione sul client ogni volta che viene inviata una chiamata a un agente che dà l agente alcuna opzione per fermare la registrazione.

<BR>
<A NAME="inbound_groups-ingroup_rec_filename">
<BR>
<B>In-Group Recording Filename -</B> Questo campo avrà la precedenza il regime filenaming Recording campagna a meno che non sia impostato su NONE. Le variabili sono consentiti CAMPAIGN INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH AGENT VENDORLEADCODE LEADID CALLID. L'impostazione predefinita è FULLDATE_AGENT e sarebbe simile a questa 20051020-103108_6666. Un altro esempio è CAMPAIGN_TINYDATE_CUSTPHONE che sarebbe simile a questa TESTCAMP_51020103108_3125551212. Te conseguente nome del file deve essere inferiore a 90 caratteri di lunghezza. Default is NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="inbound_groups-qc_enabled">
	<BR>
	<B>QC Attivato - </B> Limpostazione di questo campo a Y consente, per lagente di Controllo della Qualità funzioni. Il valore di default è N.

	<BR>
	<A NAME="inbound_groups-qc_statuses">
	<BR>
	<B>Status QC - </B> La zona dove si è stati di selezionare quale porta deve essere andato oltre, QC, dal sistema. Mettere un segno di spunta accanto allo status che si desidera, QC, di rivedere. 

	<BR>
	<A NAME="inbound_groups-qc_shift_id">
	<BR>
	<B>QC Maiusc - </B> Questo è il passaggio di tempo utilizzato per estrarre QC record per un inbound_group. I giorni della settimana sono ignorati per queste funzioni.

	<BR>
	<A NAME="inbound_groups-qc_get_record_launch">
	<BR>
	<B>QC ottenere il record di lancio-</B> Ciò consente una delle seguenti azioni per essere attivato su un QC agente riceve un nuovo record.

	<BR>
	<A NAME="inbound_groups-qc_show_recording">
	<BR>
	<B>QC Visualizza Registrazione - </B> Ciò consente una registrazione che può essere collegato con la QC record di essere esposto nel QC agente schermo.

	<BR>
	<A NAME="inbound_groups-qc_web_form_address">
	<BR>
	<B>QC Indirizzo Web Form - </B> Questo è lindirizzo web che un agente, QC può andare quando si fa clic sul link contenuto nel Web Form, QC schermo.

	<BR>
	<A NAME="inbound_groups-qc_script">
	<BR>
	<B>QC Script - </B> Questo è lo script che possono essere utilizzati da agenti QC nel SCRIPT scheda nella schermata QC.
	<?php
	}
?>

<BR>
<A NAME="inbound_groups-hold_recall_xfer_group">
<BR>
<B>Tenere Ricorda Trasferimento In-Group - </B> Se un cliente chiede di tornare in questo gruppo di più di una volta e questo non è impostato su Nessuno, quindi la chiamata verrà automaticamente inviata al gruppo selezionato In-in questo campo . Il valore di default è NESSUNO.

<BR>
<A NAME="inbound_groups-no_delay_call_route">
<BR>
<B>Scadenza Bando n. rotta - </B> Impostando questo parametro a Y rimuoverà tutti i tempi di attesa e audio richiede e si tenta di inviare il diritto di chiamata di un agente. Non ignorare il messaggio di benvenuto o tenere prompt impostazioni. Il valore di default è N.

<BR>
<A NAME="inbound_groups-answer_sec_pct_rt_stat_one">
<BR>
<B>Statistiche per cento delle chiamate risposta entro X secondi - </B> Questo campo consente di impostare il numero di tenere secondo le statistiche in tempo reale che il display utilizza per calcolare la percentuale di risposta che sono state chiamate risposta entro X numero di secondi in attesa.

<BR>
<A NAME="inbound_groups-start_call_url">
<BR>
<B>Start Call URL -</B> This web URL address is not seen by the agent, but it is called every time a call is sent to an agent if it is populated. Uses the same variables as the web form fields and scripts. Default is blank.

<BR>
<A NAME="inbound_groups-dispo_call_url">
<BR>
<B>Dispo Call URL -</B> This web URL address is not seen by the agent, but it is called every time a call is dispositioned by an agent if it is populated. Uses the same variables as the web form fields and scripts. dispo and talk_time are the variables you can use to retrieve the agent-defined disposition for the call and the actual talk time in secondi of the call. Default is blank.

<BR>
<A NAME="inbound_groups-add_lead_url">
<BR>
<B>Aggiungi URL piombo -</B> Questo indirizzo URL web non si vede l'agente, ma è chiamato ogni volta che viene aggiunto un vantaggio al sistema attraverso il processo di entrata. Predefinita è vuota. Si deve iniziare con questo URL VAR, se si desidera utilizzare le variabili, e naturalmente - A - e - B - attorno al variabile effettiva nell'URL in cui si desidera utilizzarlo. Ecco l'elenco delle variabili che sono disponibili per questa funzione. lead_id, vendor_lead_code, list_id, phone_number, phone_code, did_id, did_extension, did_pattern, did_description, uniqueid

<BR>
<A NAME="inbound_groups-na_call_url">
<BR>
<B>Nessun agente URL chiamata -</B> Questo indirizzo URL web non si vede l'agente, ma se è popolato è chiamato ogni volta che viene appeso a una chiamata che non è gestito da un agente verso l'alto o trasferiti. Utilizza le stesse variabili i campi del modulo web e script. dispo può essere utilizzato per recuperare il sistema definito disposizione per la chiamata. Questo URL non può essere un percorso relativo. Il valore predefinito è vuoto.

<BR>
<A NAME="inbound_groups-default_group_alias">
<BR>
<B>Predefinito Gruppo Alias - </B> Se si hanno consentito Alias gruppo per la campagna che lagente è registrato in poi questo è il gruppo alias che è stato selezionato prima di default su un invito da parte di questo gruppo in entrata, quando lagente sceglie di utilizzare un gruppo di Alias per un manuale di chiamata in uscita. Il valore predefinito è vuoto o NESSUNO.

<BR>
<A NAME="inbound_groups-dial_ingroup_cid">
<BR>
<B>Dial-In Group CID -</B> Se la campagna agente consente manuale In-Selezione di gruppo, questo numero ID del chiamante verrà inviato come l'uscita CID della telefonata se è popolata, sovrascrivendo le impostazioni della campagna e la lista di impostazione di override CID. L'impostazione predefinita è vuota.

<BR>
<A NAME="inbound_groups-extension_appended_cidname">
<BR>
<B>Extension Aggiunge CID -</B> Se attivata, le chiamate in arrivo da questo gruppo avrà uno spazio e l'estensione telefono dell'agente aggiunto alla fine del nome CallerID per la chiamata prima che venga inviato all'agente. Il default è n per disabili.

<BR>
<A NAME="inbound_groups-uniqueid_status_display">
<BR>
<B>Visualizzare di stato UniqueID -</B> Se abilitata, quando un agente riceve una chiamata tramite questo gruppo vedranno il uniqueid della chiamata aggiunto alla riga di stato nella loro interfaccia agente. L opzione prefix aggiungerà il prefisso, definito di seguito, all inizio della uniqueid nel display. Disabilitato di default. Se ci fosse già un uniqueID definito su una chiamata entrando in questo gruppo, verrà visualizzato il UniqueID originale. Se l opzione CONSERVARE viene utilizzato e la chiamata viene inviato a un secondo agente, il uniqueid e il prefisso visualizzata al primo agente verranno visualizzate anche al secondo agente.

<BR>
<A NAME="inbound_groups-uniqueid_status_prefix">
<BR>
<B>Stato Prefisso UniqueID -</B> Se l'opzione è selezionata PREFISSO sopra allora questo è il valore di quel prefisso. Il valore predefinito è vuoto.




<BR><BR><BR><BR>

<B><FONT SIZE=3>INBOUND_DIDS TABELLA</FONT></B><BR><BR>
<BR>
<A NAME="inbound_dids-did_pattern">
<BR>
<B>Estensione DID - </B> Questo è il numero, lestensione o la DID che attiveranno questa voce e che si percorso allinterno del sistema di utilizzare questa funzione. Vi è una riservati DID predefinito che è possibile utilizzare, che è solo la parola-default-senza trattini, che Allos di inviare qualsiasi richiesta che non corrisponde a tutte le altre esistenti modelli di default DID.

<BR>
<A NAME="inbound_dids-did_description">
<BR>
<B>Descrizione DID - </B> Questa è la descrizione di DID routing.

<BR>
<A NAME="inbound_dids-did_active">
<BR>
<B>HA attiva - </B> Questo è il campo in cui si è impostato il DID ingresso attivo o meno. Il valore di default è Y.

<BR>
<A NAME="inbound_dids-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo si, questo permette la visualizzazione di questa amministrazione ha limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore, per visualizzare questo ha fatto.

<BR>
<A NAME="inbound_dids-did_route">
<BR>
<B>DID Route -</B> Questo è il tipo di percorso che si imposta la DID da utilizzare. EXTEN invierà le chiamate verso l interno inserito sotto, segreteria invierà le chiamate direttamente alla casella di posta vocale inserito sotto, AGENTE invierà le chiamate a un agente se sono connessi, telefono invia la chiamata a una voce i telefoni selezionata di seguito, IN_GROUP invierà chiede direttamente al gruppo in ingresso specificato. L impostazione predefinita è EXTEN. CALLMENU invierà la chiamata al Call menu definito. VMAIL_NO_INST invierà la chiamata alla casella di posta vocale che si è definito di seguito e non giocherà le istruzioni dopo il messaggio vocale.

<BR>
<A NAME="inbound_dids-record_call">
<BR>
<B>Record di chiamata -</B> Questa opzione permette di impostare le chiamate che viene in questo DID da registrare. Y registrerà l'intera chiamata, Y_QUEUESTOP registrerà la chiamata fino a quando la chiamata è hungup o entra in un gruppo di in-coda, N non registrare la chiamata. Il valore predefinito è N.

<BR>
<A NAME="inbound_dids-extension">
<BR>
<B>Estensione - </B> Se è selezionata come estensione DID rotta, allora questa è lestensione che dialplan inviti saranno inviati. Il valore di default è 9998811112, nessun servizio.

<BR>
<A NAME="inbound_dids-exten_context">
<BR>
<B>Estensione Contesto - </B> Se è selezionata come estensione DID rotta, allora questo è il contesto che dialplan inviti saranno inviati. Il valore di default è di default.

<BR>
<A NAME="inbound_dids-voicemail_ext">
<BR>
<B>Buzon de voz - </B> Se è selezionata come Casella Vocale DID rotta, allora questa è la casella vocale che inviti saranno inviati. Il valore di default è vuota.

<BR>
<A NAME="inbound_dids-phone">
<BR>
<B>Telefono Estensione - </B> Se è selezionata come TELEFONO DID percorso, allora questo è il telefono cellulare in estensione che inviti saranno inviati.

<BR>
<A NAME="inbound_dids-server_ip">
<BR>
<B>Telefono IP Server - </B> Se è selezionata come TELEFONO DID percorso, allora questo è lIP del server per il telefono che chiede lestensione sarà trasmessa al.

<BR>
<A NAME="inbound_dids-menu_id">
<BR>
<B>Call Menu -</B> Se CALLMENU è selezionato come la Route DID, allora questo è il menu di chiamata che gli inviti saranno inviati a.

<BR>
<A NAME="inbound_dids-user">
<BR>
<B>Utente Agent -</B> Se l agente è selezionato come percorso DID, allora questo è l agente che chiede verrà inviato a.

<BR>
<A NAME="inbound_dids-user_unavailable_action">
<BR>
<B>Lutente non disponibile Azione - </B> Se è selezionata come AGENTE DID rotta, e lutente non è connesso o disponibili, allora questo è il percorso che le chiamate si.

<BR>
<A NAME="inbound_dids-user_route_settings_ingroup">
<BR>
<B>Utente percorso Impostazioni In-Group - </B> Se è selezionata come AGENTE DID rotta, allora questo è il gruppo In-che saranno utilizzati per la coda di impostazioni come il chiamante è in attesa di essere inviati al agente. Il valore di default è AGENTDIRECT.

<BR>
<A NAME="inbound_dids-group_id">
<BR>
<B>In-ID gruppo - </B> Se è selezionata come IN_GROUP DID rotta, allora questo è il gruppo In-chiede che saranno inviati alla.

<BR>
<A NAME="inbound_dids-call_handle_method">
<BR>
<B>In-chiamata di gruppo maniglia Metodo -</B> Se IN_GROUP è selezionato come percorso DID, allora questo è il metodo di gestione delle chiamate utilizzato per queste chiamate. CID aggiungerà un nuovo record vantaggio con ogni chiamata utilizzando il CallerID come numero di telefono, CIDLOOKUP tenterà di ricercare il numero di telefono dal CallerID in tutto il sistema, CIDLOOKUPRL tenterà di ricercare il numero di telefono dal CallerID in una sola lista specificata , CIDLOOKUPRC tenterà di ricercare il numero di telefono dal CallerID in tutte le liste che appartengono alla campagna specificato, CLOSER è specificato per le chiamate Closer, ANI aggiungerà un nuovo record vantaggio con ogni chiamata usando l ANI come numero di telefono, ANILOOKUP tenterà di ricercare il numero di telefono dal ANI in tutto il sistema, ANILOOKUPRL tenterà di ricercare il numero di telefono dal ANI in una sola lista specificata, XDIGITID chiederà al chiamante per un codice X cifre prima della chiamata verrà messa in coda, VIDPROMPT chiederà al chiamante per il loro numero ID e creerà un nuovo record vantaggio con il CallerID come il numero di telefono e l ID come ID del fornitore, VIDPROMPTLOOKUP tenterà di ricercare l ID in tutto il sistema, VIDPROMPTLOOKUPRL tenterà di ricercare l ID commerciante per l ID in una sola lista specificato, VIDPROMPTLOOKUPRC tenterà di ricercare l ID commerciante per l ID in tutte le liste che appartengono alla campagna specificato. L impostazione predefinita è CID. Se un metodo CIDLOOKUP viene utilizzato con ALT, cercherà il campo alt_phone per il numero di telefono se non vengono trovate corrispondenze per il numero di telefono principale. Se un metodo CIDLOOKUP viene utilizzato con ADDR3, cercherà il campo address3 per il numero di telefono se non vengono trovate corrispondenze per il numero di telefono principale e facoltativamente il campo alt_phone.

<BR>
<A NAME="inbound_dids-agent_search_method">
<BR>
<B>Nel gruppo di agente Metodo di ricerca - </B> Se è selezionata come IN_GROUP DID rotta, allora questo è il metodo di ricerca agente debba essere utilizzato da un gruppo in entrata, LO è Load-Balanced-Overflow e cercherà di inviare la chiamata di un agente sul server locale prima di tentare di inviarlo a un agente su un altro server, LB è bilanciamento del carico e si tenta di inviare la chiamata al prossimo agente non importa ciò che essi sono il server, server-SO è solo e solo tenta di inviare gli inviti agli agenti presenti sul server che linvito è venuto in su. Il valore di default è LB.

<BR>
<A NAME="inbound_dids-list_id">
<BR>
<B>Nel gruppo Lista ID - </B> Se è selezionata come IN_GROUP DID rotta, allora questo è lelenco ID che porta può essere cercato attraverso la porta e che sarà inserito in se necessario.

<BR>
<A NAME="inbound_dids-campaign_id">
<BR>
<B>Nel gruppo-ID campagna - </B> Se è selezionata come IN_GROUP DID rotta, allora questo è lID campagna che porta può essere cercato per se il metodo di gestire la chiamata è CIDLOOKUPRC.

<BR>
<A NAME="inbound_dids-phone_code">
<BR>
<B>Nel gruppo Codice Telefono - </B> Se è selezionata come IN_GROUP DID rotta, allora questo è il codice utilizzato Telefono se un nuovo piombo è creato.

<BR>
<A NAME="inbound_dids-filter_clean_cid_number">
<BR>
<B>Pulire Numero CID -</B> Questo campo consente di specificare un numero di cifre per limitare il numero ID del chiamante in ingresso al ponendo R davanti al numero di cifre, ad esempio per limitare verso destra di 10 cifre è necessario immettere in R10. È inoltre possibile utilizzare questa funzione per rimuovere solo una cifra iniziale oi numeri ponendo L di fronte alle cifre specifiche che si desidera rimuovere, ad esempio per rimuovere un 1 come prima cifra è necessario immettere in L1. Il valore predefinito è vuoto. Se più di una regola viene specificato assicurarsi di separarle con uno spazio e la R verrà eseguito prima della L.

<BR>
<A NAME="inbound_dids-filter_inbound_number">
<BR>
<B>Filter Number Inbound -</B> Questa opzione, se attivata permette di filtrare le chiamate che entrano in questo DID e inviarli ad una azione alternativa se corrispondono a un numero di telefono che è nel gruppo telefonico filtro o una risposta URL se si è configurato uno. Predefinito è DISABLED. GRUPPO cercherà in un gruppo di Phone Filter. URL invierà un URL e corrisponde se un 1 viene inviato. DNC_INTERNAL ricerca dalla lista interna DNC. DNC_CAMPAIGN cercherà di uno specifico elenco DNC campagna.

<BR>
<A NAME="inbound_dids-filter_phone_group_id">
<BR>
<B>Filter Phone ID Group -</B> Se il campo Filter Number ingresso è impostato su GROUP allora questo è l'ID del gruppo Phone Filter che avrà i suoi numeri di ricerca per trovare una corrispondenza per il numero del chiamante ID della chiamata in entrata.

<BR>
<A NAME="inbound_dids-filter_url">
<BR>
<B>URL Filter -</B> Se il campo Filter Number ingresso è impostato su URL, allora questo è l'indirizzo web di un script che cercherà un sistema remoto e restituire un 1 per un incontro e uno 0 per nessuna partita. Solo due variabili sono disponibili l'indirizzo se si utilizza il prefisso VAR come con indirizzi webform nelle campagne, - A - phone_number - B - e - A - did_pattern - B - può essere utilizzato nella URL per indicare l'ID chiamante del chiamante e il DID che il cliente chiamato in su.

<BR>
<A NAME="inbound_dids-filter_url_did_redirect">
<BR>
<B>Filtro URL DID Redirect -</B> Se il campo Numero Inbound Filter è impostato URL allora questa impostazione consente la risposta URL per specificare un sistema DID per reindirizzare la chiamata alla INSEAD di utilizzare l azione predefinita. Se viene restituito 0, allora viene utilizzata l azione predefinita. Se viene restituito qualcosa di diverso da uno 0 allora la chiamata verrà deviata al valore di risposta URL risultante.

<BR>
<A NAME="inbound_dids-filter_dnc_campaign">
<BR>
<B>Filtro Campagna DNC -</B> Se il campo Numero Inbound Filter è impostato DNC_CAMPAIGN allora questo è l ID specifica campagna che l elenco campagna DNC appartiene.



<BR>
<A NAME="inbound_dids-filter_action">
<BR>
<B>Action Filter -</B> Se il filtro numero in ingresso viene attivato e viene rilevata una corrispondenza, allora questo è l'azione che deve essere presa. Questo è lo stesso del percorso che si seleziona per un fatto, e le seguenti impostazioni funzionano proprio come fanno di uno standard di routing.

<BR>
<A NAME="inbound_dids-custom_one">
<BR>
<B>I campi personalizzati DID - </ B> Questi cinque campi possono essere utilizzati per vari scopi, per lo più relative alla programmazione personalizzata e relazioni.




<BR>
<A NAME="did_ra_extensions">
<BR>
<B>DID distanza sostituisce interno di un agente -</B><BR>Questa sezione permette di abilitare DID di avere sostituzioni di estensione per agente remoto le chiamate instradate attraverso in-gruppi. The Start utente deve essere un valido Remote Start Utente Agent o se si desidera l'estensione Override voce a lavorare per tutte le chiamate è possibile utilizzare --- Tutte --- nel campo Inizio Utente. Se ci sono più voci per la stessa DID utente e Start, quindi le voci attive saranno utilizzati in un metodo round robin.




<BR><BR><BR><BR>

<B><FONT SIZE=3>CALL TAVOLA MENU</FONT></B><BR><BR>
<A NAME="call_menu-menu_id">
<BR>
<B>Menu ID -</B> Questo è l-ID di questo passo del menù chiamata. Questo mostrerà anche come il contesto che viene utilizzata nel dialplan per il presente invito menu. Ecco un elenco di frasi riservate che non possono essere utilizzati come menù ID: vicidial, vicidial-auto, general, globals, default, trunkinbound, loopback-no-log, monitor_exit, monitor.

<BR>
<A NAME="call_menu-menu_name">
<BR>
<B>Menu Nome -</B> Questo campo è il nome descrittivo per il menu di chiamata.

<BR>
<A NAME="call_menu-menu_prompt">
<BR>
<B>Menu Prompt -</B> Questo campo contiene il nome del file del prompt audio da riprodurre all-inizio di questo menu. È possibile immettere propmts più in questo campo e gli altri settori prompt separandole con un carattere pipe. Puoi aggiungere NOINT direttamente di fronte a un nome di file audio per fare in modo che la riproduzione non può essere interrotta premendo un tasto dal chiamante, il NOINT non dovrebbe essere una parte del nome del file, è una bandiera speciale per il sistema. Si può anche utilizzare per usi speciali. Agi script in questo settore, così come la sceneggiatura cm_date.agi, discutere con l'amministratore per maggiori dettagli.

<BR>
<A NAME="call_menu-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="call_menu-menu_timeout">
<BR>
<B>Timeout menu -</B> Questo campo è dove si imposta il timeout in secondi che il menu si aspetta per il chiamante per entrare in una scelta DTMF. L-impostazione di questo campo a zero 0 significa che non vi sarà alcun tempo di attesa dopo il prompt si gioca.

<BR>
<A NAME="call_menu-menu_timeout_prompt">
<BR>
<B>Menu prompt timeout -</B> Questo campo contiene il nome del file del prompt audio da riprodurre quando il timeout è stato raggiunto. Di default è NESSUNO per giocare senza audio a timeout.

<BR>
<A NAME="call_menu-menu_invalid_prompt">
<BR>
<B>Menu valide Prompt -</B> Questo campo contiene il nome del file del prompt audio da riprodurre quando il chiamante ha scelto un-opzione valida. Di default è NESSUNO per giocare senza audio a non validi.

<BR>
<A NAME="call_menu-menu_repeat">
<BR>
<B>Menu Repeat -</B> Questo campo è dove si definisce il numero di volte che il menu svolgeranno dopo la prima volta se non valida scelta è fatta dal chiamante. Di default è di 1 a ripetere il menu di una volta.

<BR>
<A NAME="call_menu-menu_time_check">
<BR>
<B>Menu Check Time -</B> Questo campo è dove si può scegliere se limitare l-accesso al menù di chiamata ora specifici stabiliti nella selezionato di guardia. Se la durata delle chiamate è vuota, questa impostazione verrà ignorata. Di default è 0 per disabili.

<BR>
<A NAME="call_menu-call_time_id">
<BR>
<B>ID Orario Chiamate -</B> Questa è la chiamata Time ID che verrà utilizzato per limitare i tempi chiamando se il menu Time opzione di controllo è attivata.

<BR>
<A NAME="call_menu-track_in_vdac">
<BR>
<B>Chiede Track in Real-Time Report -</B> Questo campo è dove è possibile selezionare se si desidera che la chiamata ad essere monitorati in tempo reale sullo schermo come una chiamata in entrata di tipo IVR. Predefinito è 1 per attivo.

<BR>
<A NAME="call_menu-tracking_group">
<BR>
<B>Gruppo di monitoraggio -</B> Questo è l-ID che è possibile utilizzare per monitorare le chiamate a questo menu di chiamata se si guarda alla relazione IVR. L-elenco comprende CALLMENU come predefinito così come tutte le In-Groups.

<BR>
<A NAME="call_menu-dtmf_log">
<BR>
<B>Log pressione di un tasto -</B> Questa opzione, se attivata registrerà la stampa DTMF da parte del chiamante in questo menu Chiama. Il default è 0 per disabili.

<BR>
<A NAME="call_menu-dtmf_field">
<BR>
<B>Log campo -</B> Se il tasto opzione Log Press è attivata, questa impostazione opzionale può permettere la risposta anche essere memorizzati in questo campo elenco. vendor_lead_code, source_id, phone_code, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, alt_phone, email, security_phrase, comments, rank, owner, status, user. Il valore predefinito è NONE per disabili.

<BR>
<A NAME="call_menu-option_value">
<BR>
<B>Value Opzione -</B> Questo campo è dove si definisce l-opzione di menu, le scelte possibili sono: 0,1,2,3,4,5,6,7,8,9,*,#,A,B,C,D,TIMECHECK. Il TIMECHECK speciale opzione può essere utilizzata solo se si dispone di menu Check Time abilitato e c-è un tempo chiamata definito per il Menu. Per eliminare un opzione, è sufficiente impostare il percorso per rimuovere e l-opzione verranno eliminati quando si fa clic sul pulsante INVIA. TIMEOUT vi permetterà di impostare cosa succede alla chiamata quando si verifica un timeout senza alcun input dal chiamante. INVALID vi permetterà di impostare ciò che accade quando il chiamante entra un'opzione non valida. INVALID_2ND e 3 può essere attivo solo se INVALID non viene utilizzato, si attenderà fino all'entrata seconda o terza non valida da parte del chiamante prima di eseguire l'opzione.

<BR>
<A NAME="call_menu-option_description">
<BR>
<B>Opzione Descrizione -</B> Questo campo è dove si può descrivere l-opzione, questa descrizione sarà messo in dialplan come un commento in precedenza l-opzione.

<BR>
<A NAME="call_menu-option_route">
<BR>
<B>Opzione Route -</B> Questo menu contiene le opzioni per sapere dove inviare la chiamata, se questa opzione è selezionata: CALLMENU, ingroup, DID, Hangup, ESTENSIONE, TEL. Per CALLMENU, il Rapporto percorso dovrebbe essere l-ID del menu chiamata che si desidera la chiamata inviata. Per ingroup, l-In-gruppo che si desidera che la chiamata ad essere inviato al bisogno di essere scelto come pure le altre 5 opzioni che hanno bisogno di essere impostato correttamente il percorso di una chiamata a un gruppo di ingresso. Per DID, il Rapporto percorso deve essere il modello DID che si desidera inviare la chiamata al. Per Hangup, il Rapporto percorso può essere il nome di un file audio di giocare prima di appendere la chiamata. Per estensione, il valore Route deve essere l-estensione dialplan che si desidera inviare la chiamata a, e la Strada del valore di contesto è il contesto che l-estensione in cui si trova, se lasciato in bianco il contesto sarà di default per difetto. Per telefono, il Rapporto percorso deve essere il valore login telefono cellulare per l-entrata telefoni che si desidera inviare la chiamata al. Per VoiceMail, il Rapporto percorso deve essere il numero di casella vocale, il mesage disponibile verrà riprodotto. Per AGI, il Rapporto percorso deve essere lo script AGI e tutti i valori che devono essere passati a esso. VMAIL_NO_INST invierà la chiamata alla casella di posta vocale che si è definito di seguito e non giocherà le istruzioni dopo il messaggio vocale.

<BR>
<A NAME="call_menu-option_route_value">
<BR>
<B>Route Value Opzione -</B> Questo campo è dove si immette il valore che definisce il punto della opzione selezionata percorso che la chiamata è di essere diretto a.

<BR>
<A NAME="call_menu-option_route_value_context">
<BR>
<B>Opzione Route valore di contesto -</B> Questo campo è opzionale e solo utilizzati come percorsi di opzione di proroga.

<BR>
<A NAME="call_menu-ingroup_settings">
<BR>
<B>Chiama Menu In-Impostazioni del gruppo -</B> Se il percorso è impostato su ingroup poi ci sono molte opzioni che si possono impostare per definire la modalità di chiamata viene inviato nella coda. In-Group è il gruppo in entrata a cui si desidera la chiamata per andare a. Metodo Handle è il modo in cui si desidera che la chiamata a essere gestito, <a href="#inbound_dids-call_handle_method">Clicca qui per vedere un elenco dei metodi di maniglie disponibili</a>. Metodo di ricerca definisce come la coda si trova l'agente successivo, consigliamo lasciarlo su LB. ID List è l'elenco che il piombo viene inserito nel nuovo, anche se il metodo non è un metodo di ricerca e il piombo non viene trovato. ID campagna è la campagna per la ricerca attraverso liste se uno dei metodi RC viene utilizzato. Codice Phone è la voce del campo phone_code per il piombo che viene inserito con. VID Inserisci Nome viene utilizzato se il metodo è impostato su uno dei metodi VIDPROMPT, è il prompt audio giocato a chiedere al cliente di inserire il proprio ID. VID Nome Numero ID viene utilizzato se il metodo è impostato su uno dei metodi VIDPROMPT, è il prompt audio giocato dopo che il cliente entra nel loro ID, qualcosa di simile che hai inserito. Nome del file VID Conferma viene utilizzato se il metodo è impostato su uno dei metodi VIDPROMPT, è il prompt audio giocato a confermare il loro ID, qualcosa di simile PRESS 1 per confermare e 2 per rientrare. Cifre VID viene utilizzato se il metodo è impostato su uno dei metodi VIDPROMPT, se è impostato su un numero è il numero di cifre che devono essere inseriti dal Cliente quando richiesto per il loro ID, se impostato a vuoto o poi la X cliente dovrà premere sterlina o hash per terminare il loro ingresso del loro ID.

<BR>
<A NAME="call_menu-custom_dialplan_entry">
<BR>
<B>Personalizzato Entry Dialplan -</B> Questo campo consente di inserire in ogni dialplan elementi che si desidera per il menu di chiamata.

<BR>
<A NAME="call_menu-qualify_sql">
<BR>
<B>Qualificare SQL -</B> Questo campo consente di immettere la SQL - Structured Query Language - frammenti di database, come con i filtri, per determinare se questo menu chiamata deve giocare per il chiamante o meno. Questa caratteristica funziona solo se la chiamata è impostata l'callerIDname prima di essere inviato a questo menu chiamata, come un trasferimento sondaggio in uscita, o attraverso l'uso di un menu drop call per una chiamata In-Group. Se vi è una corrispondenza, la chiamata procede come normale. Se non vi è alcuna corrispondenza, la chiamata viene inoltrata a l'opzione D o l'opzione non valida se nessuna opzione D è impostata. Non è possibile utilizzare apici in questo campo, solo i doppi apici se sono necessari. L'impostazione predefinita è vuota per disabili.






<BR><BR><BR><BR>

<BR>
<A NAME="filter_phone_groups-filter_phone_group_id">
<BR>
<B>Filter Phone ID Group -</B> Questo è l'ID del gruppo Phone Filter, che è il contenitore per un gruppo di numeri di telefono che si può avere automaticamente cercato attraverso quando arriva una chiamata in un file. DID e inviare a un percorso alternativo se esiste una corrispondenza Il campo deve essere tra i 2 ei 20 caratteri e non hanno punteggiatura ad eccezione di sottolineatura.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_name">
<BR>
<B>Filter Nome Telefono Group -</B> This is the name of the Filter Phone Group and is displayed with the ID in select lists where this feature is used. This field should be between 2 and 40 characters.

<BR>
<A NAME="filter_phone_groups-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_description">
<BR>
<B>Filter Phone Descrizione Gruppo -</B> Questa è la descrizione del gruppo Phone Filter, è puramente notazione e non è un campo obbligatorio.




<BR><BR><BR><BR>

<B><FONT SIZE=3>REMOTE_AGENTS TABELLA</FONT></B><BR><BR>
<A NAME="remote_agents-user_start">
<BR>
<B>Inizio ID Utente -</B> Questo è l ID utente di partenza che viene utilizzata quando le voci agente telecomando siano inserite nel sistema. Se il numero di righe impostato superiore a 1, questo numero è incrementato di uno fino a quando ogni linea ha una voce. Assicurarsi di creare un nuovo account utente con un livello utente di 4 o grande se si desidera che siano in grado di utilizzare la pagina vdremote.php per l accesso web remoto di questo account.

<BR>
<A NAME="remote_agents-number_of_lines">
<BR>
<B>Número de líneas -</B> el define cuánto crea el agente alejado las entradas el sistema, y se determina cuántas líneas piensa que puede enviar con seguridad al número debajo.

<BR>
<A NAME="remote_agents-server_ip">
<BR>
<B>IP del servidor -</B> Una entrada alejada del agente es solamente buena para un servidor específico, aquí es donde usted selecciona a que el servidor usted desea.

<BR>
<A NAME="remote_agents-conf_exten">
<BR>
<B>Extension externa -</B> este es el número que usted desea las llamadas remitidas a. Cercio`rese de que sea un número dial plan completo y que si usted necesita 9 al principio usted lo pone adentro aquí. Pruebe marcando este número de un telefono en el sistema.

<BR>
<A NAME="remote_agents-extension_group">
<BR>
<B>Extension Group -</B> Se è impostato su qualcosa di diverso da NONE o vuoto questo ridefinire il campo esterno Estensione e utilizzare le voci di estensione del Gruppo che hanno lo stesso ID del gruppo estensione. Il valore predefinito è NONE per disattivato.

<BR>
<A NAME="remote_agents-status">
<BR>
<B>Estado -</B> aquí es donde usted da vuelta al agente alejado por intervalos. Tan pronto como el agente sea activo el sistema asume que puede enviarle llamadas. Puede tomar hasta 30 segundos una vez que usted cambie el estado a inactivo para parar el recibir de llamadas.

<BR>
<A NAME="remote_agents-campaign_id">
<BR>
<B>Campaña -</B> aquí es donde usted selecciona la campaña que estos agentes alejados serán registrados en. Necesidades de entrada de utilizar la campaña MÁS CERCANA y de seleccionar las campañas de entrada debajo de e`sa que usted desea recibir llamadas de.

<BR>
<A NAME="remote_agents-on_hook_agent">
<BR>
<B>On-Hook agente -</B> Questa opzione viene utilizzata solo per le chiamate in entrata che vanno a questo agente remoto. Questa funzione consente di chiamare l'agente remoto e non inviare al cliente all'agente remoto fino a quando la linea viene risposto. Il default è n per disabili.

<BR>
<A NAME="remote_agents-on_hook_ring_time">
<BR>
<B>On-Hook squilli -</B> Questa opzione viene utilizzata solo quando l'On-Hook campo agente sopra è impostato a Y e quindi solo per le chiamate in entrata che arrivano a questo agente remoto. Questo è il numero di secondi che ogni tentativo di chiamata squillerà per cercare di ottenere una risposta. Si consiglia di impostare questo a pochi secondi in meno di quanto necessario per una chiamata da inviare alla segreteria telefonica. Il valore predefinito è 15.

<BR>
<A NAME="remote_agents-closer_campaigns">
<BR>
<B>Grupos de entrada -</B> aquí es donde usted selecciona a grupos de entrada que usted desea recibir llamadas de si usted ha seleccionado la campaña MÁS CERCANA.


<BR><BR><BR><BR>

<B><FONT SIZE=3>EXTENSION_GROUPS TABELLA</FONT></B><BR><BR>
<A NAME="extension_groups-extension_group_id">
<BR>
<B>Extension Group -</B> Questo campo obbligatorio è dove inserire l'ID gruppo che si desidera questa estensione da mettere in. Non ci sono spazi o caratteri speciali ad eccezione di sottolineatura lettere e numeri.

<BR>
<A NAME="extension_groups-extension">
<BR>
<B>Extension -</B> Questo campo è richiesto in cui si inserisce l'estensione dialplan che si desidera che l'agente remoto le chiamate da inviare per questa voce gruppo di interni.

<BR>
<A NAME="extension_groups-rank">
<BR>
<B>Rank -</B> Questo campo consente di classificare le voci di estensione dei gruppi che condividono lo stesso gruppo estensione. Il default è 0.

<BR>
<A NAME="extension_groups-campaign_groups">
<BR>
<B>Campagne Gruppi -</B> In questo campo si può mettere un elenco di ID o ID campagna e di gruppo in ingresso che si desidera limitare l'uso del gruppo per estensione. Elenco devono essere separati da tubi ed un condotto all'inizio e alla fine della stringa.


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN_LISTS</FONT></B><BR><BR>
<A NAME="campaign_lists">
<BR>
<B>Las listas dentro de esta campaña se enumeran aquí, si son activas son denotadas por la Y o N y usted pueden ir a la pantalla de la lista chascando en la identificacio`n de la lista en la primera columna.</B>


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN_STATUSES TABELLA</FONT></B><BR><BR>
<A NAME="campaign_statuses">
<BR>
<B>Attraverso l uso di stati delle campagne personalizzate, è possibile avere gli stati che esistono solo per una specifica campagna. Lo stato deve essere 1-8 caratteri, la descrizione deve essere 2-30 caratteri e selezionabile definisce se si presenta nel sistema come disposizione. Il campo human_answered viene utilizzato per il calcolo della percentuale di goccia, o abbandonare rate. Impostazione human_answered Y userà questo stato quando il conteggio delle chiamate umani-risposta. L opzione Categoria consente di raggruppare più status in un catogy che può essere utilizzato per l analisi statistica.</B>



<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>CAMPAIGN_HOTKEYS TABELLA</FONT></B><BR><BR>
	<A NAME="campaign_hotkeys">
	<BR>
	<B>Attraverso l uso di tasti di scelta rapida personalizzate della campagna, gli agenti che utilizzano il web-client agent può riagganciare e disposizione chiamate semplicemente premendo un singolo tasto sulla loro tastiera.</B> Hay dos opciones especiales que usted puede utilizar conjuntamente conel número de telefono alterno que marca, ALTPH2 de HotKey - el dialcaliente del telefono alterno y el dial caliente ADDR3-----Address3permiten que un agente utilice un hotkey al retraso su llamada,permanecen en el mismo plomo, y marcan otro número del contacto deese conducen. È inoltre possibile utilizzare LTMG o XFTAMM come status per attivare un trasferimento automatico per l'opzione Leave-Casella Vocale.





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_RECYCLE TABELLA</FONT></B><BR><BR>
	<A NAME="lead_recycle">
	<BR>
	<B>Through the use of lead recycling, you can call specific statuses of leads again at a specified interval without resetting the entire list. Lead recycling is campaign-specific and does not have to be a selected dialable status in your campaign. The attempt delay field is the number of seconds until the lead can be placed back in the hopper, this number must be at least 120 seconds. The attempt maximum field is the maximum number of times that a lead of this status can be attempted before the list needs to be reset, this number can be from 1 to 10. You can activate and deactivate a lead recycle entry with the provided links.</B>





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>AUTO ALT DIAL status</FONT></B><BR><BR>
	<A NAME="auto_alt_dial_statuses">
	<BR>
	<B>Si se fija el campo que marca del Alt-Nu`mero auto, despue`s losplomos que son dispositioned bajo estos estados del dial del alt delautomo`vil tendrán su alt_phone y-o los campos address3 marcadosdespue`s de que cualesquiera de estos ninguno-contesten a estados sefijan.</B>

	<?php
	}
?>



<BR><BR><BR><BR>

<B><FONT SIZE=3>CODICI PAUSA OPERATORE</FONT></B><BR><BR>
<A NAME="pause_codes">
<BR>
<B>Se l agente Pausa Codici di campo attivo è impostato su attivo poi gli agenti saranno in grado di scegliere tra questi codici di pausa quando si clicca sul tasto PAUSE sui loro schermi. Questi dati vengono poi memorizzati nel registro dell agente. Il codice Pause deve contenere solo lettere e numeri ed essere meno di 7 caratteri. Il nome in codice pausa può essere più lungo di 30 caratteri.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN PRESETS</FONT></B><BR><BR>
<A NAME="xfer_presets">
<BR>
<B>Se l'impostazione della campagna per i preset è impostato su ENABLED allora avete la possibilità di definire Transfer-conferenza preset che saranno disponibili per l'agente permettendo loro di 3-way chiamare questi preset o trasferire chiamate cieche a questi numeri preimpostati. Questi preset hanno anche un opzione per nascondere il numero associato ad ogni preset dall'agente.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN CID PrefissoS</FONT></B><BR><BR>
<A NAME="campaign_cid_areacodes">
<BR>
<B>Se l'impostazione di sistema per IndicativoLocalità CID è attivata e l'impostazione della campagna per l'uso personalizzato CallerID è impostato su IndicativoLocalità allora avete la possibilità di definire CID prefisso locale che verranno utilizzati quando si chiama in uscita per cavi in ​​questa campagna specifica. È possibile aggiungere callerIDs multipli per ogni prefisso locale e si può attivare e disattivare ogni loro in tempo reale. Se più di un callerID è attiva per un prefisso locale specifica allora il sistema userà il CallerID che è stato utilizzato il minor numero di volte oggi. Se non callerIDs sono attivi per il prefisso locale poi il CallerID campagna o di un elenco di override CallerID verrà utilizzato.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>USER_GROUPS TABELLA</FONT></B><BR><BR>
<A NAME="user_groups-user_group">
<BR>
<B>Gruppo Utente -</B> Questo è il nome breve di un gruppo di utenti, cercare di non utilizzare spazi o segni di punteggiatura per questo campo. max 20 caratteri, almeno 2 caratteri.

<BR>
<A NAME="user_groups-group_name">
<BR>
<B>Nome Gruppo -</B> Questa è la descrizione del gruppo di utenti massimo di 40 caratteri.

<BR>
<A NAME="user_groups-forced_timeclock_login">
<BR>
<B>Forza orologio Login -</B> Questa opzione consente di non lasciare che un agente login all interfaccia agente se non hanno effettuato l accesso al orologio. L impostazione predefinita è N. C è un opzione di esentare utenti admin, livelli 8 e 9.

<BR>
<A NAME="user_groups-shift_enforcement">
<BR>
<B>Maiusc esecuzione -</B> Questa impostazione consente di limitare agente login basata su turni che sono selezionati in appresso. OFF non applicare a tutti i turni. INIZIO solo applicare il login, ma non riguarda un agente che è in esecuzione sul loro passaggio, se essi sono già eseguito laccesso TUTTE imporrà a turni orario di inizio e di un agente di log dopo che corrono oltre la fine del loro turno tempo. Il valore di default è OFF.

<BR>
<A NAME="user_groups-group_shifts">
<BR>
<B>Gruppo Turni - </B> Si tratta di un elenco dei turni selezionabili che possono limitare il tempo di accesso agenti sul sistema.

<BR>
<A NAME="user_groups-allowed_campaigns">
<BR>
<B>Campañas permitidas -</B> esta es una lista seleccionable de lascampañas a las cuales los miembros de este grupo de usuario puedenabrirse una sesio`n. La opcio`n de ALL-CAMPAIGNS permite que losusuarios en este grupo consideren y se abran una sesio`n a cualquiercampaña en el sistema.

<BR>
<A NAME="user_groups-agent_status_viewable_groups">
<BR>
<B>Stato agente Viewable Gruppi -</B> Questa è una lista selezionabile di gruppi di utenti e funzioni di utente a cui i membri di questo gruppo utente può visualizzare lo stato delle chiamate, nonché il trasferimento verso l-interno dello schermo agente. The All-GRUPPI opzione consente agli utenti in questo gruppo da vedere e trasferire le chiamate a qualsiasi utente del sistema. LA CAMPAGNA AGENTI opzione consente agli utenti in questo gruppo da vedere e trasferire le chiamate a qualsiasi utente nella campagna che si è registrato in. Il non-logged-IN-AGENTI opzione consente a tutti gli utenti del sistema da visualizzare, anche se non sono registrati curently.

<BR>
<A NAME="user_groups-agent_status_view_time">
<BR>
<B>Visualizza stato agente Time -</B> Questa opzione definisce se l-agente vedrà la quantità di tempo che gli utenti nella loro sidebar agenti sono stati nel loro stato attuale. Di default è N per nulla o portatori di handicap.

<BR>
<A NAME="user_groups-agent_call_log_view">
<BR>
<B>Call Agent View Log -</B> Questa opzione definisce se l agente sarà in grado di vedere il loro registro chiamate per le chiamate gestite attraverso lo schermo dell agente. Di default è N per no o disabili.

<BR>
<A NAME="user_groups-agent_xfer_options">
<BR>
<B>Agente di trasferimento Opzioni -</B> Queste opzioni consentono la disattivazione di pulsanti specifici nella sezione di trasferimento Conferenza dell'interfaccia dell'agente. Il valore predefinito è Y per sì o abilitati.

<BR>
<A NAME="user_groups-agent_fullscreen">
<BR>
<B>Agente Fullscreen -</B> Questa opzione, se impostato su Y imposterà l altezza e la larghezza dello schermo dell agente alle dimensioni della finestra del browser web senza alcuna indennità per il Agenti View, chiamate in coda View o chiede in vista della sessione. Di default è N per no o disabili.

<BR>
<A NAME="user_groups-allowed_reports">
<BR>
<B>Rapporti ammessi -</B> Se un utente in questo gruppo è impostata su livello di utente 7 o superiore, questa funzione può essere utilizzata per limitare i rapporti che gli utenti possono visualizzare. Il valore predefinito è ALL. Se si desidera selezionare più di una relazione, quindi premere il tasto Ctrl della tastiera mentre si selezionano i rapporti.

<BR>
<A NAME="user_groups-admin_viewable_groups">
<BR>
<B>Gruppi di utenti ammessi -</B> Questa è una lista selezionabile di gruppi di utenti a cui i membri di questo gruppo di utenti può visualizzare ed eventualmente modificare. Gruppi di utenti possibile limitare l'accesso a quasi tutti gli aspetti del sistema, da DID in entrata ai telefoni a caselle vocali. The - ALL - opzione consente agli utenti in questo gruppo da vedere e accedere a qualsiasi record sul sistema se i loro permessi dell'utente lo consentono.

<BR>
<A NAME="user_groups-admin_viewable_call_times">
<BR>
<B>Tempi di chiamata ammessi -</B> Questa è una lista selezionabile di durata delle chiamate a cui i membri di questo gruppo utente può utilizzare nelle campagne, nei gruppi e menu di chiamata. The - ALL - opzione consente agli utenti di questo gruppo di utilizzare tutti i tempi delle chiamate al sistema.


<?php
if (strlen($SSwebphone_url) > 5)
	{
	?>
	<BR>
	<A NAME="user_groups-webphone_url_override">
	<BR>
	<B>URL Override webphone -</B> Questa impostazione consente di impostare un URL alternativo webphone solo per i membri di un gruppo di utenti. Il valore predefinito è vuoto.

	<BR>
	<A NAME="user_groups-webphone_systemkey_override">
	<BR>
	<B>Webphone Key System Override -</B> Questa impostazione consente di impostare un supplente Key System webphone solo per i membri di un gruppo di utenti. Il valore predefinito è vuoto.

	<BR>
	<A NAME="user_groups-webphone_dialpad_override">
	<BR>
	<B>Webphone Dialpad Override -</B> Questa impostazione consente di attivare o disattivare la tastiera sul webphone solo per i membri di un gruppo di utenti. Predefinito è DISABLED. TOGGLE permetterà all'utente di visualizzare e nascondere la tastiera cliccando su un link. TOGGLE_OFF per impostazione predefinita per non mostrare la tastiera il primo carico, ma permette all'utente di visualizzare la tastiera facendo clic sul link tastiera.

	<?php
	}

if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="user_groups-qc_allowed_campaigns">
	<BR>
	<B>QC ammessi Campagne - </B> Si tratta di un elenco di campagne selezionabili che membri di questo gruppo di utenti sarà in grado di QC. The All-CAMPAGNE opzione consente agli utenti di questo gruppo di QC una campagna sul sistema.

	<BR>
	<A NAME="user_groups-qc_allowed_inbound_groups">
	<BR>
	<B>QC ammessi Inbound Gruppi - </B> Si tratta di un elenco di ingresso selezionabile gruppi che membri di questo gruppo di utenti sarà in grado di QC. TUTTI I GRUPPI opzione consente agli utenti di questo gruppo di utenti di qualsiasi entrata CQ gruppo sul sistema.
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SCRIPT TABELLA</FONT></B><BR><BR>
<A NAME="scripts-script_id">
<BR>
<B>ID Script: -</B> Questo è il nome breve di uno script. Questo deve essere un identificatore univoco. Cercate di non utilizzare spazi o segni di punteggiatura per questo campo. max 10 caratteri, almeno 2 caratteri.

<BR>
<A NAME="scripts-script_name">
<B>Nome Script -</B> Questo è il titolo di uno script. Questo è un breve sunto dello script. max 50 caratteri, minimo di 2 caratteri. Non ci dovrebbero essere spazi o segni di punteggiatura di qualsiasi tipo in campo Theis.

<BR>
<A NAME="scripts-script_comments">
<B>Note Script -</B> Questo è dove è possibile inserire commenti per uno script schermo agente chimico come-cambiato in aggiornamento gratuito on 23 settembre -. max 255 caratteri, almeno 2 caratteri.

<BR>
<A NAME="scripts-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="scripts-script_text">
<B>Testo Script -</B> Questo è dove si colloca il contenuto di uno script schermo agente. Minimo di 2 caratteri. È possibile avere informazioni sui clienti essere auto-popolato in questo script utilizzando "--A--field--B--" dove il campo è uno dei seguenti fieldnames: vendor_lead_code, source_id, list_id, gmt_offset_now, called_since_last_reset, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, lead_id, campaign, phone_login, group, channel_group, SQLdate, epoch, uniqueid, customer_zap_channel, server_ip, SIPexten, session_id, dialed_number, dialed_label, rank, owner, camp_script, in_script, script_width, script_height, recording_filename, recording_id, user_custom_one, user_custom_two, user_custom_three, user_custom_four, user_custom_five, preset_number_a, preset_number_b, preset_number_c, preset_number_d, preset_number_e, preset_number_f, preset_dtmf_a, preset_dtmf_b, did_id, did_extension, did_pattern, did_description, closecallid, xfercallid, agent_log_id, entry_list_id, call_id, user_group, called_count, TABELLAper_call_notes. For example, this sentence would print the persons name in it----<BR><BR>  Hello, can I speak with --A--first_name--B-- --A--last_name--B-- please? Well hello --A--title--B-- --A--last_name--B-- how are you today?<BR><BR> This would read----<BR><BR>Hello, can I speak with John Doe please? Well hello Mr. Doe how are you today?<BR><BR> È anche possibile utilizzare un iframe per caricare una finestra separata all interno della scheda SCRIPT, ecco un esempio con le variabili precompilato:

<DIV style="height:200px;width:400px;background:white;overflow:scroll;font-size:12px;font-family:sans-serif;" id=iframe_example>
&#60;iframe src="http://www.sample.net/test_output.php?lead_id=--A--lead_id--B--&#38;vendor_id=--A--vendor_lead_code--B--&#38;list_id=--A--list_id--B--&#38;gmt_offset_now=--A--gmt_offset_now--B--&#38;phone_code=--A--phone_code--B--&#38;phone_number=--A--phone_number--B--&#38;title=--A--title--B--&#38;first_name=--A--first_name--B--&#38;middle_initial=--A--middle_initial--B--&#38;last_name=--A--last_name--B--&#38;address1=--A--address1--B--&#38;address2=--A--address2--B--&#38;address3=--A--address3--B--&#38;city=--A--city--B--&#38;state=--A--state--B--&#38;province=--A--province--B--&#38;postal_code=--A--postal_code--B--&#38;country_code=--A--country_code--B--&#38;gender=--A--gender--B--&#38;date_of_birth=--A--date_of_birth--B--&#38;alt_phone=--A--alt_phone--B--&#38;email=--A--email--B--&#38;security_phrase=--A--security_phrase--B--&#38;comments=--A--comments--B--&#38;user=--A--user--B--&#38;campaign=--A--campaign--B--&#38;phone_login=--A--phone_login--B--&#38;fronter=--A--fronter--B--&#38;closer=--A--user--B--&#38;group=--A--group--B--&#38;channel_group=--A--group--B--&#38;SQLdate=--A--SQLdate--B--&#38;epoch=--A--epoch--B--&#38;uniqueid=--A--uniqueid--B--&#38;customer_zap_channel=--A--customer_zap_channel--B--&#38;server_ip=--A--server_ip--B--&#38;SIPexten=--A--SIPexten--B--&#38;session_id=--A--session_id--B--&#38;dialed_number=--A--dialed_number--B--&#38;dialed_label=--A--dialed_label--B--&#38;rank=--A--rank--B--&#38;owner=--A--owner--B--&#38;phone=--A--phone--B--&#38;camp_script=--A--camp_script--B--&#38;in_script=--A--in_script--B--&#38;script_width=--A--script_width--B--&#38;script_height=--A--script_height--B--&#38;recording_filename=--A--recording_filename--B--&#38;recording_id=--A--recording_id--B--&#38;user_custom_one=--A--user_custom_one--B--&#38;user_custom_two=--A--user_custom_two--B--&#38;user_custom_three=--A--user_custom_three--B--&#38;user_custom_four=--A--user_custom_four--B--&#38;user_custom_five=--A--user_custom_five--B--&#38;preset_number_a=--A--preset_number_a--B--&#38;preset_number_b=--A--preset_number_b--B--&#38;preset_number_c=--A--preset_number_c--B--&#38;preset_number_d=--A--preset_number_d--B--&#38;preset_number_e=--A--preset_number_e--B--&#38;preset_number_f=--A--preset_number_f--B--&#38;preset_dtmf_a=--A--preset_dtmf_a--B--&#38;preset_dtmf_b=--A--preset_dtmf_b--B--&#38;did_id=--A--did_id--B--&#38;did_extension=--A--did_extension--B--&#38;did_pattern=--A--did_pattern--B--&#38;did_description=--A--did_description--B--&#38;closecallid=--A--closecallid--B--&#38;xfercallid=--A--xfercallid--B--&#38;agent_log_id=--A--agent_log_id--B--&#38;entry_list_id=--A--entry_list_id--B--&#38;call_id=--A--call_id--B--&&#38;user_group=--A--user_group--B--&&#38;" style="width:580;height:290;background-color:transparent;" scrolling="auto" frameborder="0" allowtransparency="true" id="popupFrame" name="popupFrame" width="460" height="290" STYLE="z-index:17"&#62;
&#60;/iframe&#62;
</DIV>
<BR>
È inoltre possibile utilizzare uno IGNORENOSCROLL variabile speciale per forzare le barre di scorrimento nella scheda script anche se si utilizza un iframe all'interno di esso.

<BR>
<A NAME="scripts-active">
<BR>
<B>Activo -</B> esto se determina si esta escritura se puede seleccionar para ser utilizado por una campaña.





<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_FILTRI TABELLA</FONT></B><BR><BR>
	<A NAME="lead_filters-lead_filter_id">
	<BR>
	<B>Filter ID -</B> Questo è il nome breve di un filtro di piombo. Questo deve essere un identificatore univoco. Non utilizzare spazi o segni di punteggiatura per questo campo. max 10 caratteri, almeno 2 caratteri.

	<BR>
	<A NAME="lead_filters-lead_filter_name">
	<B>Nombre del filtro -</B> este es un nombre más descriptivo del filtro. este es un resumen corto de los caracteres del máximo 30 del filtro, mínimo de 2 caracteres.

	<BR>
	<A NAME="lead_filters-lead_filter_comments">
	<B>Filter Note -</B> Questo è dove è possibile inserire commenti relativi a un filtro come-tutte le chiamate in California conduce. max 255 caratteri, almeno 2 caratteri.

	<BR>
	<A NAME="lead_filters-user_group">
	<BR>
	<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

	<BR>
	<A NAME="lead_filters-lead_filter_sql">
	<B>Filtro SQL -</B> Aquí es adonde usted pone el fragmento de la pregunta del SQL que usted desea filtrar cerca no comienza o el extremo con Y, eso será agregado por la escritura del cron de la tolva automáticamente. una pregunta del SQL del ejemplo que trabajaría aquí es el called_count 4 y called_count.
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>ORARI CHIAMATA TABELLA</FONT></B><BR><BR>
<A NAME="call_times-call_time_id">
<BR>
<B>ID Orario Chiamate -</B> Questo è il nome breve di una chiamata Tempo definizione. Questo deve essere un identificatore univoco. Non utilizzare spazi o segni di punteggiatura per questo campo. max 10 caratteri, almeno 2 caratteri.

<BR>
<A NAME="call_times-call_time_name">
<B>Nombre del tiempo de la llamada -</B> este es un nombre más descriptivode la definicio`n del tiempo de la llamada. este es un resumen cortode los caracteres del máximo 30 de la definicio`n del tiempo de lallamada, mínimo de 2 caracteres.

<BR>
<A NAME="call_times-call_time_comments">
<B>Note Orario Chiamate -</B> Questo è dove è possibile inserire commenti per una definizione di tempo di chiamata, come-10:00-16:00 con lo stato della chiamata supplementare restrizioni-. max 255 caratteri.

<BR>
<A NAME="call_times-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="call_times-ct_default_start">
<B>Tiempos del comienzo y de parada del defecto -</B> este es el tiempo dedefecto paran que el llamar será permitido que sea comenzado o dentrode esta definicio`n del tiempo de la llamada si la hora de salida dela di`a-de-$$$-SEMANA no se define. 0 es medianoche. Evitar el llamarfijo` totalmente este campo a 2400 y fijo` el tiempo de parada deldefecto a 2400. Permitir el llamar 24 horas al día fijo` la hora desalida a 0 y el tiempo de parada a 2400. Per solo in ingresso, è possibile anche impostare la durata delle chiamate di arresto superiore a 2400 se si desidera che la durata delle chiamate di andare oltre la mezzanotte. Quindi, se volete che il vostro tempo di chiamata per eseguire dalle 6 del mattino fino alle 2 del mattino del giorno successivo, si metterebbe 0600 come l'ora di inizio e 2600 come il tempo di arresto.

<BR>
<A NAME="call_times-ct_sunday_start">
<B>Tiempos del comienzo y de parada del día laborable -</B> estos son lostiempos de encargo por el día que se puede fijar para la definicio`ndel tiempo de la llamada que las mismas reglas se aplican como con lostiempos del comienzo y de parada del defecto.

<BR>
<A NAME="call_times-default_afterhours_filename_override">
<B>Dopo Nome file Override Hours -</B> Questi campi consentono di ignorare il messaggio After Hours per i gruppi in arrivo se è impostato a qualcosa. Il valore predefinito è vuoto.

<BR>
<A NAME="call_times-ct_state_call_times">
<B>Definiciones del tiempo de la llamada del estado -</B> esta es la listade las definiciones específicas del tiempo de la llamada del estadoque se siguen en esta definicio`n del tiempo de la llamada.

<BR>
<A NAME="call_times-state_call_time_state">
<B>Estado del tiempo de la llamada del estado -</B> este es el co`digo dedos letras para el estado que esta definicio`n del tiempo que llamaestá para. Para que esto sea en efecto la llamada local mida eltiempo que se fija en la campaña debe tener este expediente deltiempo de la llamada del estado en e`l así como todos los plomos quetienen dos co`digos del estado de la letra en ellos.

<A NAME="call_times-holiday_id">
<BR>
<B>Vacanze ID -</B> Questo è il nome breve di un Holiday Definizione. Questo deve essere un identificatore univoco. Non utilizzare spazi o segni di punteggiatura per questo campo. max 30 caratteri, minimo di 2 caratteri.

<BR>
<A NAME="call_times-holiday_name">
<B>Nome di festa -</B> Questo è un nome più descrittivo della vacanza Definizione. Questo è un breve riepilogo della definizione di vacanza. max 100 caratteri, minimo di 2 caratteri.

<BR>
<A NAME="call_times-holiday_comments">
<B>Vacanze Commenti -</B> This is where you can place comments for a Holiday Definition such as -10am to 4pm boxing day restrictions-.  max 255 characters.

<BR>
<A NAME="call_times-holiday_date">
<B>Vacanze Data -</B> Questa è la data della vacanza.

<BR>
<A NAME="call_times-holiday_status">
<B>Vacanze Stato -</B> Questo è lo stato della voce vacanza. Stato attivo significa che la vacanza sarà abilitata alla data di vacanza. Stato inattivo significa che la vacanza sarà ignorato, anche alla data di vacanza. Significa che la vacanza ha superato la sua data di vacanza SCADUTO. Default è inattivo.




<BR><BR><BR><BR>

<B><FONT SIZE=3>SHIFTS TABELLA</FONT></B><BR><BR>
<A NAME="shifts-shift_id">
<BR>
<B>Shift ID -</B> Questo è il nome breve di una definizione Maiusc sistema. Questo deve essere un identificatore univoco. Non utilizzare spazi o segni di punteggiatura per questo campo. max 20 caratteri, almeno 2 caratteri.

<BR>
<A NAME="shifts-shift_name">
<B>Nome Shift - </B> Questo è un nome più descrittivo della Maiuscole Definizione. Questa è una breve sintesi della Maiuscole definizione. max 50 caratteri, minimo di 2 caratteri.

<BR>
<A NAME="shifts-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="shifts-shift_start_time">
<B>Start Time Shift - </B> Questo è il tempo che la campagna ha inizio turno. Deve essere solo numeri, 9:30 AM sarebbe 5:00 PM 0930 e 1700 sarebbe.

<BR>
<A NAME="shifts-shift_length">
<B>Maiusc Lunghezza - </B> Questo è il tempo in ore e minuti che la campagna a turni dura. 8 ore sarebbe 08:00 e 7 ore e 30 minuti sarebbero 07:30.

<BR>
<A NAME="shifts-shift_weekdays">
<B>Maiusc Weekdays - </B> In questa sezione si deve scegliere il giorno della settimana che questo spostamento è attivo.

<BR>
<A NAME="shifts-report_option">
<B>Segnala Opzione -</B> Questa opzione consente questo cambiamento specifico di presentarsi nei rapporti selezionati che supportano questa opzione.

<BR>
<A NAME="shifts-report_rank">
<B>Segnala Rango -</B> Questa opzione consente di classificare cambiamenti nei rapporti selezionati che supportano questa opzione.




<BR><BR><BR><BR>
<A NAME="audio_store">
<B>Audio Store -</B> Questa utility consente di caricare file audio al server web in modo che possano essere distribuiti a tutti i server del sistema in un cluster multi-server. Una nota importante, solo due tipi di file audio funzionerà. File wav che sono PCM Mono 16bit 8k e file. Gsm che sono 8k 8bit. Si prega di verificare che i file siano correttamente formattati prima di caricare qui.



<BR><BR><BR><BR>

<B><FONT SIZE=3>MUSIC_ON_HOLD TABELLA</FONT></B><BR><BR>
<A NAME="music_on_hold-moh_id">
<BR>
<B>Music On Hold ID -</B> Questo è il nome breve di un Music On Hold entrata. Questo deve essere un identificatore univoco. Non usare spazi o punteggiatura per questo campo. max 100 caratteri, minimo di 2 caratteri.

<BR>
<A NAME="music_on_hold-moh_name">
<B>Music On Hold Nome -</B> Questo è un nome più descrittivo della musica su attesa dell entrata. Questo è un breve sunto del Music On contesto attesa e mostrerà come commento nel file musiconhold conf. max 255 caratteri, almeno 2 caratteri.

<BR>
<A NAME="music_on_hold-active">
<B>Attivo -</B> Questa opzione consente di impostare la musica su attesa ingresso attivo o inattivo. Inattivi rimuovere la voce dal file di conf.

<BR>
<A NAME="music_on_hold-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="music_on_hold-random">
<B>Ordine casuale -</B> Questa opzione permette di definire la riproduzione dei file audio in ordine casuale. Se impostato su N quindi l-ordine definito saranno utilizzati.

<BR>
<A NAME="music_on_hold-filename">
<B>Filename -</B> Per aggiungere un nuovo file audio ad un Music On Hold ingresso il file deve essere prima nel negozio di audio, quindi è possibile selezionare il file e fare clic su per aggiungerlo alla lista dei file. Musica in attesa viene aggiornato una volta al minuto, se ci sono state le modifiche apportate. Tutti i file che non figurano in una musica d-ingresso sostengono che sono presenti nella cartella di musica su attesa saranno eliminati.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TTS_PROMPTS TABELLA</FONT></B><BR><BR>
<A NAME="tts_prompts-tts_id">
<BR>
<B>TTS ID -</B> Questo è il nome breve di una voce di TTS. Questo deve essere un identificatore univoco. Non usare spazi o punteggiatura per questo campo. caratteri max 50, minimo di 2 caratteri.

<BR>
<A NAME="tts_prompts-tts_name">
<B>TTS Nome -</B> Questo è un nome più descrittivo della voce di TTS. Questo è un breve sunto della definizione TTS. max 100 caratteri, minimo di 2 caratteri.

<BR>
<A NAME="tts_prompts-active">
<B>Attivo -</B> Questa opzione consente di impostare la voce TTS di attivi o inattivi.

<BR>
<A NAME="tts_prompts-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="tts_prompts-tts_voice">
<B>TTS Voice -</B> Questo è dove si definisce la voce da utilizzare nella generazione TTS. Il valore predefinito è Allison-8kHz.

<BR>
<A NAME="tts_prompts-tts_text">
<B>TTS Text -</B> Questo è il testo effettivo ai dati Discorso campo che viene inviato al Cepstral per la creazione del file audio da riprodurre al cliente. è possibile utilizzare Speech Synthesis Markup Language-SSML-in questo campo, per esempio, &lt;break time='1000ms'/&gt; for a 1 second break. You can also use several variables such as first name, last name and title as system variables just like you do in a Script: --A--first_name--B--. Se avete statici file audio che si desidera utilizzare in base al valore di uno dei campi che è possibile utilizzare anche quelli con la C e D tag. I nomi dei file devono essere tutti in lettere minuscole e devono essere 8k 16bit i file WAV PCM. Il nome del campo deve essere lo stesso, ma senza l'estensione. Wav nel nome del file. Per esempio - C ---- A - Indirizzo3 - B ---- D - per prima cosa trovare il valore per Indirizzo3, poi avrebbe cercato di trovare un file audio corrispondente a quel valore per metterlo nel prompt. Ecco un elenco delle variabili disponibili: lead_id, entry_date, modify_date, status, user, vendor_lead_code, source_id, list_id, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_count, last_local_call_time, rank, owner




<BR><BR><BR><BR>

<B><FONT SIZE=3>VOICEMAIL TABELLA</FONT></B><BR><BR>
<A NAME="voicemail-voicemail_id">
<BR>
<B>Casella Vocale ID -</B> Questo è l-identificativo di tutti i numeri di questa cassetta postale. Questo non deve essere un duplicato di una segreteria telefonica esistente ID o la segreteria telefonica ID di un telefono cellulare sul sistema, minimo di 2 caratteri.

<BR>
<A NAME="voicemail-fullname">
<B>Name -</B> Questo nome è associato a questa casella vocale. max 100 caratteri, minimo di 2 caratteri.

<BR>
<A NAME="voicemail-pass">
<B>Password -</B> Questa è la password che viene utilizzata per ottenere l-accesso alla casella vocale, quando chiamate in ingresso per controllare i messaggi max 10 caratteri, minimo di 2 caratteri.

<BR>
<A NAME="voicemail-active">
<B>Attivo -</B> Questa opzione consente di impostare la casella vocale di attivo o inattivo. Se la casella è attiva non è possibile lasciare dei messaggi su di essa e non è possibile controllare i messaggi in esso.

<BR>
<A NAME="voicemail-email">
<B>Email -</B> Questa impostazione consente di avere i messaggi vocali inviati a un account di posta elettronica, se il vostro sistema è configurato per inviare e-mail. Se questo campo è vuoto quindi non e-mail verrà inviato.

<BR>
<A NAME="voicemail-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="voicemail-delete_vm_after_email">
<B>Eliminare Casella Vocale Dopo Email -</B> Questa impostazione consente di avere i messaggi vocali eliminato dal sistema dopo che sono state inviate fuori. Di default è N.

<BR>
<A NAME="voicemail-voicemail_greeting">
<B>Messaggio di benvenuto -</B> Questa impostazione opzionale consente di definire un file audio messaggio di benvenuto dal negozio audio. Predefinito è vuoto.

<BR>
<A NAME="voicemail-voicemail_timezone">
<B>Casella Vocale Zone -</B> Questa impostazione consente di impostare la zona che questa casella voicemail sarà impostato quando il tempo viene registrato un messaggio. Di default è impostato in Impostazioni di sistema.

<BR>
<A NAME="voicemail-voicemail_options">
<B>Casella Vocale Opzioni -</B> Questa impostazione opzionale consente di definire le impostazioni vocali aggiuntive. Si consiglia di lasciare vuoto questo se non sai cosa stai facendo.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>

	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LIST LOADER FUNCTIONALITY</FONT></B><BR><BR>
	<A NAME="list_loader">
	<BR>
	Il web a base di piombo loader di base mira semplicemente a prendere un file vantaggio - fino a 8 MB di dimensione - che è o tab o un tubo delimitato e caricarlo nella tabella lista. Il caricatore cavo permette di campo scelta e TXT-Plain Text, CSV, Comma Separated Values ​​e formati di file XLS-Excel. Il caricatore di piombo non fa convalida dei dati, ma permette di controllare i duplicati in sé, all interno della campagna o all interno dell intero sistema. Inoltre, assicurarsi di aver creato la lista che questi cavi devono essere in modo da poterli utilizzare. Ecco un elenco dei campi nel loro giusto ordine per i file di piombo:
		<OL>
		<LI>Co`digo del plomo del vendedor - demuestra para arriba en el campo de la identificacio`n del vendedor del GUI
		<LI>Co`digo de fuente - uso interno solamente para los admins y DBAs
		<LI>Identificacio`n de la lista - el número de la lista que estos plomos demostrarán para arriba debajo
		<LI>Phone Code - the prefix for the phone number - 1 for US, 44 for UK, 61 for AUS, etc
		<LI>Número de telefono - debe ser por lo menos 8 dígitos de largo
		<LI>Título - título del customer(Mr. Ms señ., etc...)
		<LI>Nome
		<LI>Secondo Nome
		<LI>Cognome
		<LI>Indirizzo, linea 1
		<LI>Indirizzo, linea 2
		<LI>Indirizzo, linea 3
		<LI>Citta`
		<LI>Stato - limitato a 2 caratteri
		<LI>Provincia
		<LI>CAP
		<LI>Stato
		<LI>Sesso
		<LI>Data di Nascita
		<LI>Numero di Telefono Alternativo
		<LI>Email
		<LI>Frase De Seguridad
		<LI>Note
		<LI>Rank
		<LI>Owner
		</OL>

	<BR>NOTAS: La funcionalidad del cargador del plomo del sobresalir espermitida por una serie de escrituras del Perl y necesita tener unarchivo correctamente configurado de /etc/astguiclient.conf en lugaren el web server. Tambien, los mo`dulos de un Perl de los pares sedeben cargar para ella para trabajar Tambien -oLE-Storage_Lite-Storage_Lite y la Hoja de balance-ParseExcel. Ustedpuede comprobar para saber si hay errores runtime en estos mirando suarchivo del error_log de apache. Tambien, para los cheques deduplicacio`n contra campaign enumera, la lista que tiene nuevos plomosel entrar e`l necesita ser creada en el sistema antes de que ustedcomience a cargar los plomos.

	<BR>
	<A NAME="list_loader-duplicate_check">
	<BR>
	<B>Duplicare Controllo - </B>Le opzioni duplicati consentono di controllare per le voci duplicate, come si carica i cavi nel sistema. È possibile scegliere di controllare i duplicati all interno solo lo stesso elenco, solo le stesse liste di campagna o all interno dell intero sistema. Se avete scelto un metodo di controllo duplicato, è possibile anche selezionare facoltativamente i soli stati specifici che si desidera duplicare controllo contro.

	<BR>
	<A NAME="list_loader-file_layout">
	<BR>
	<B>File layout - </B>Il layout del file che si sta caricando. "Formato standard" utilizza il formato di file standard pre-definiti. "Layout Custom" consente all utente di definire il layout del file stessi. "Modello Custom" è un ibrido delle due opzioni precedenti, che permette all utente di utilizzare un formato personalizzato che hanno definito in precedenza e salvato utilizzando il modello personalizzato Maker.

	<BR>
	<A NAME="list_loader-template_id">
	<BR>
	<B>Template ID -</B> If the user has selected "Struttura personalizzata" from the "File layout" options, then this the the template the lead loader will use.  It will also override the selected list ID with the list ID that was assigned to the selected template when it was created.



	<BR><BR><BR><BR>
	<?php
	}
?>





<B><FONT SIZE=3>TABELLA TELEFONI</FONT></B><BR><BR>
<A NAME="phones-extension">
<BR>
<B>Phone Extension -</B> Questo campo è dove si inserisce il nome di telefoni come appare ad Asterisk non è compreso il protocollo o barra all'inizio. Ad esempio: per il telefono SIP SIP/test101 l'estensione Phone sarebbe test101. Inoltre, per i telefoni IAX2: IAX2/IAXphone1 @ IAXphone1 sarebbe IAXphone1. Per Zap e dahdi allegato channelbank FXS o telefoni assicuratevi di mettere l'intero numero dei canali senza il prefisso: Zap/25-1 sarebbe 25-1. Un'altra nota, assicuratevi di impostare il campo protocollo di seguito correttamente per il tipo di telefono.

<BR>
<A NAME="phones-dialplan_number">
<BR>
<B>Número del Dial plan -</B> este campo está para el número que usted marca para tener el anillo del telefono. Este número se define en el archivo de extensions.conf de su servidor asterisco

<BR>
<A NAME="phones-voicemail_id">
<BR>
<B>Caja de Buzon de Voz -</B> este campo es para la caja del Buzon de voz donde van los mensajes para al usuario de este telefono. Utilizamos esto para comprobar los mensajes del Buzon de voz y para que el usuario pueda acceder al Buzon de Voz desde astGUIclient.

<BR>
<A NAME="phones-voicemail_timezone">
<B>Casella Vocale Zone -</B> Questa impostazione consente di impostare la zona che questa casella voicemail sarà impostato quando il tempo viene registrato un messaggio. Di default è impostato in Impostazioni di sistema.

<BR>
<A NAME="phones-voicemail_options">
<B>Casella Vocale Opzioni -</B> Questa impostazione opzionale consente di definire le impostazioni vocali aggiuntive. Si consiglia di lasciare vuoto questo se non sai cosa stai facendo.

<BR>
<A NAME="phones-outbound_cid">
<BR>
<B>CallerID de salida -</B> este campo es donde usted incorporara el número del callerID que usted quiera que aparezca en las llamadas de salida. Esto no funciona en las líneas RTB(non-PRI) T1/E1.

<BR>
<A NAME="phones-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="phones-phone_ip">
<BR>
<B>Direccion IP del telefono -</B> This field is for the phone's IP address if it is a VOIP phone. This is an optional field

<BR>
<A NAME="phones-computer_ip">
<BR>
<B>Direccion IP del ordenador -</B> This field is for the user's computer IP address. This is an optional field

<BR>
<A NAME="phones-server_ip">
<BR>
<B>IP del servidor -</B> Este menú es donde usted selecciona en que servidor está el telefono activo.

<BR>
<A NAME="phones-login">
<BR>
<B>Agente schermata di login -</B> Il login utilizzato per l utente del telefono per accedere alle applicazioni client, come lo schermo dell agente.

<BR>
<A NAME="phones-pass">
<BR>
<B>Login Password -</B>  La password utilizzata per l'utente del telefono per accedere alle applicazioni web-based client. IMPORTANTE, questa è la password solo per il login Phone Agent interfaccia web, per cambiare il sip.conf o la password iax.conf, o il segreto, per questo dispositivo cellulare è necessario modificare la password di registrazione nel campo successivo.

<BR>
<A NAME="phones-conf_secret">
<BR>
<B>Registrazione password -</B> Questo è il segreto, o la password, per il telefono cellulare in auto o IAX SIP-generated conf file per questo telefono. Limite è dash 20 caratteri alfanumerici e underscore accettato. Di default è di prova. Precedentemente chiamato Secret File Conf. Una password di registrazione forte deve essere di almeno 8 caratteri di lunghezza e di minuscole e maiuscole, nonché almeno un numero.

<BR>
<A NAME="phones-is_webphone">
<BR>
<B>Imposta come Webphone -</B>  Impostando questa opzione su Y tenterà di caricare un web-based telefono quando l'agente accede a loro schermo agente. Il valore predefinito è N. L'opzione Y_API_LAUNCH può essere utilizzato con l'API agente di lanciare il webphone in una finestra separata o Iframe.

<BR>
<A NAME="phones-webphone_dialpad">
<BR>
<B>Webphone Dialpad -</B>  Questa impostazione consente di attivare o disattivare la tastiera di questo webphone. Y è predefinita per abilitato. TOGGLE permetterà all'utente di visualizzare e nascondere la tastiera facendo clic su un link. Questa funzione non è disponibile su tutte le versioni webphone. TOGGLE_OFF per impostazione predefinita per non mostrare la tastiera il primo carico, ma permette all'utente di visualizzare la tastiera facendo clic sul link tastiera.

<BR>
<A NAME="phones-webphone_auto_answer">
<BR>
<B>Webphone Risposta automatica -</B>  Questa impostazione permette al telefono web per essere impostato per rispondere automaticamente alle chiamate che arrivano impostando a Y, o avere anello chiamate impostando per default è N. Y.

<BR>
<A NAME="phones-use_external_server_ip">
<BR>
<B>Utilizzare IP esterno Server -</B>  Se si utilizza come un telefono web, è possibile impostare questo valore a Y per utilizzare i server IP esterno per la registrazione al posto del IP del server. Il valore predefinito è vuoto.

<BR>
<A NAME="phones-status">
<BR>
<B>Estado -</B> el estado del telefono en el sistema, puede ser ACTIVO y ADMIN permiten que los clientes del GUI trabajen. ADMIN permite el acceso a este Web site de administracion. El resto de los estados no permiten el acceso al GUI o al Web de Admin.

<BR>
<A NAME="phones-active">
<BR>
<B>Cuenta activa -</B> si el telefono está activado ponerla en la lista del cliente del GUI.

<BR>
<A NAME="phones-phone_type">
<BR>
<B>Tipo de telefono -</B> solamente para informacion administrativas.

<BR>
<A NAME="phones-fullname">
<BR>
<B>Nombre completo -</B> usado por el GUIclient en la lista de telefonos activos.

<BR>
<A NAME="phones-company">
<BR>
<B>Compañía -</B> solamente para las notas administrativas.

<BR>
<A NAME="phones-email">
<BR>
<B>Cellulari e-mail - </B> Lindirizzo email associato a questo telefono cellulare entrata. Questo è utilizzato per le impostazioni della segreteria.

<BR>
<A NAME="phones-delete_vm_after_email">
<B>Eliminare Casella Vocale Dopo Email -</B> Questa impostazione consente di avere i messaggi vocali eliminato dal sistema dopo che sono state inviate fuori. Di default è N.

<BR>
<A NAME="phones-voicemail_greeting">
<B>Messaggio di benvenuto -</B> Questa impostazione opzionale consente di definire un file audio messaggio di benvenuto dal negozio audio. Predefinito è vuoto.

<BR>
<A NAME="phones-voicemail_instructions">
<B>Istruzioni Casella Vocale -</B> Questa impostazione consente di definire se le istruzioni Casella Vocale svolgeranno dopo il saluto vocale quando una chiamata squilla sul all estensione agente e timeout alla voicemail. Il valore predefinito è Y.

<BR>
<A NAME="phones-picture">
<BR>
<B>Cuadro -</B> todavía no puesto en ejecucio`n.

<BR>
<A NAME="phones-messages">
<BR>
<B>Nuevos mensajes -</B> número de los nuevos mensajes del Buzon de Voz para este telefono en el servidor del asterisco.

<BR>
<A NAME="phones-old_messages">
<BR>
<B>Viejos mensajes -</B> número de los viejos mensajes del Buzon de Voz para este telefono en el servidor del asterisco.

<BR>
<A NAME="phones-protocol">
<BR>
<B>Protocolo del cliente -</B> el protocolo que el telefono utiliza para conectar con el servidor del asterisco: El Sip, IAX2, Zap. Tambien, para los números External remotos o los números SpeedDial que usted desea enumerar como telefonos.

<BR>
<A NAME="phones-local_gmt">
<BR>
<B>Fuso Orario Locale -</B> La differenza di tempo medio di Greenwich, o il tempo ZULU in cui si trova il telefono. NON REGOLARE per l ora legale. Questo è utilizzato dalla campagna per visualizzare accuratamente il tempo di sistema e tempo cliente, nonché accedere precisione quando accadono eventi.

<BR>
<A NAME="phones-phone_ring_timeout">
<BR>
<B>Phone Ring Timeout -</B> Questa è la quantità di tempo, in secondi, che il telefono squilla nel dialplan prima di inviare la chiamata alla segreteria telefonica. Predefinito è 60 secondi.

<BR>
<A NAME="phones-on_hook_agent">
<BR>
<B>On-Hook Login Agente -</B> Questa opzione viene utilizzata solo per le chiamate in entrata che vanno ad un agente effettuato l'accesso con questo telefono. Questa funzione consente di chiamare l'agente e non inviare al cliente alla sessione agenti fino a quando la linea si risponde. Il default è n per disabili.

<BR>
<A NAME="phones-ASTmgrUSERNAME">
<BR>
<B>conexion del encargado -</B> esta es la conexion que los clientes del GUI para este telefono utilizarán tener acceso a la base de datos donde residen los datos del servidor.

<BR>
<A NAME="phones-ASTmgrSECRET">
<BR>
<B>Secreto del encargado -</B> esta es la contraseña que los clientes del GUI para este telefono utilizarán tener acceso a la base de datos donde residen los datos del servidor.

<BR>
<A NAME="phones-login_user">
<BR>
<B>Agente Default Utente -</B> Questo è quello di mettere un valore predefinito nel campo utente dell agente ogni volta che l utente del telefono si apre l applicazione client. Lasciare vuoto per nessun utente.

<BR>
<A NAME="phones-login_pass">
<BR>
<B>Agente di default Passo -</B> Questo è quello di mettere un valore predefinito nel campo password dell agente ogni volta che l utente del telefono si apre l applicazione client. Lasciare vuoto per non passa.

<BR>
<A NAME="phones-login_campaign">
<BR>
<B>Campagna agente di default -</B> Questo è quello di mettere un valore predefinito nel campo campagna schermo agent ogni volta che l utente del telefono si apre l applicazione client. Lasciare vuoto per nessuna campagna.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Parque Exten -</B> esta es la extension del estacionamiento del defecto para los apps del cliente. Verifique que diverso trabaje antes de que usted cambie esto.

<BR>
<A NAME="phones-conf_on_extension">
<BR>
<B>Extension de la conferencia -</B> esta es la extension del parque de la conferencia del defecto para los apps del cliente. Verifique que diverso trabaje antes de que usted cambie esto.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Agente Parco Exten -</B> Questa è l estensione Parcheggio predefinita per un applicazione client. Verificare che uno diverso funziona prima di modificare questo.

<BR>
<A NAME="phones-park_on_filename">
<BR>
<B>Agente Parco File -</B> Questo è il nome della schermata agente di estensione file parco di default per le applicazioni client. Verificare che uno diverso funziona prima di modificare questo. limitata a 10 caratteri.

<BR>
<A NAME="phones-monitor_prefix">
<BR>
<B>Prefijo del monitor -</B> este es el prefijo dial plan para supervisar de zap los canales automáticamente dentro del app astGUIclient. Cambie solamente según los expedientes de extensiones de extensions.conf ZapBarge.

<BR>
<A NAME="phones-recording_exten">
<BR>
<B>grabacion Exten -</B> esta es la extension dial plan para la extension de la grabacion que se utiliza para caer en conferencias del meetme para registrarlas. Dura generalmente hasta que una hora si no parada verifica con el archivo de extensions.conf antes de cambiar.

<BR>
<A NAME="phones-voicemail_exten">
<BR>
<B>VMAIL Exten principal -</B> esta es la extension dial plan que va a comprobar su voicemail. verifica con el archivo de extensions.conf antes de cambiar.

<BR>
<A NAME="phones-voicemail_dump_exten">
<BR>
<B>VMAIL Dump Exten -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. verify with extensions.conf file before changing.

<BR>
<A NAME="phones-voicemail_dump_exten_no_inst">
<BR>
<B>Vmail Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="phones-ext_context">
<BR>
<B>Contexto De Exten -</B> Questo è il contesto dial plan che lo schermo agente, utilizza principalmente. Si presume che tutti i numeri chiamati dalle applicazioni client utilizzano questo contesto, quindi è una buona idea per assicurarsi che questo sia il più ampio possibile contesto. verificare con il file extensions.conf prima di cambiare. predefinito è default.

<BR>
<A NAME="phones-phone_context">
<BR>
<B>Telefono Contesto -</B> Questo è il contesto dial plan che questo telefono sarà utilizzato per effettuare chiamate in uscita. Se si esegue un call center e non volete i vostri agenti di essere in grado di comporre al di fuori dello schermo applicaiton agente per esempio, allora si dovrebbe impostare questo campo a un contesto dialplan che non esiste, qualcosa come agente nodial. predefinito è default.

<BR>
<A NAME="phones-codecs_list">
<BR>
<B>Codec ammessi -</B> È possibile definire un elenco delimitato da virgole di codec da impostare come i codec di default per questo telefono. Le opzioni per i codec sono ulaw, alaw, GSM, G729, Speex, G722, G723, G726, iLBC, ... Alcuni di questi codec potrebbero non essere disponibili sul vostro sistema, come il G729 o G726. Se il campo è vuoto, allora i codec di default del sistema o la rubrica telefonica di sopra di questo uno sarà utilizzato per i codec ammissibili. Il valore predefinito è vuoto.

<BR>
<A NAME="phones-codecs_with_template">
<BR>
<B>Codec ammessi Con Template-</B> Impostando questa opzione a 1 includerà i codec di cui sopra, anche se un modello di file di configurazione viene utilizzato. Il default è 0.

<BR>
<A NAME="phones-dtmf_send_extension">
<BR>
<B>DTMF envían el canal -</B> esta es la secuencia del canal usada para enviar sonidos de DTMF en conferencias del meetme de los apps del cliente. Verifique que exten y contexto con el archivo de extensions.conf.

<BR>
<A NAME="phones-call_out_number_group">
<BR>
<B>Grupo de salida de la llamada -</B> este es el grupo de canal que las llamadas de salida de este telefono están puestas de. Hay rutinas de un par en los apps del cliente que utilizan esto. Para zap los canales que usted desea utilizar algo como Zap/g2, porque los troncos IAX2 usted desearía utilizar el prefijo completo de IAX como IAX2/VICItest1:secret@10.10.10.15:4569. Verifique que los troncos con el file(it de extensions.conf sean generalmente lo que usted ha definido como la variable global del TRUNK en la tapa del archivo).

<BR>
<A NAME="phones-client_browser">
<BR>
<B>Localizacio`n del browser -</B> esto es aplicable solamente a los clientes de UNIX/LINUX, el camino absoluto a Mozilla o el browser de Firefox en la máquina verifica esto lanzándolo manualmente.

<BR>
<A NAME="phones-install_directory">
<BR>
<B>Directorio de instalacio`n -</B> Non più utilizzati.

<BR>
<A NAME="phones-local_web_callerID_URL">
<BR>
<B>URL de CallerID -</B> Ústa es la Direccion de la tela de la página usada para hacer operaciones de búsqueda de encargo del callerID que es la Direccion de prueba del defecto: http://astguiclient.sf.net/test_callerid_output.php

<BR>
<A NAME="phones-agent_web_URL">
<BR>
<B>URL Agente di default -</B> Questo è l indirizzo web della pagina utilizzata per fare agente Web personalizzata query modulo. Indirizzo test di default è definito nello schema del database.

<BR>
<A NAME="phones-AGI_call_logging_enabled">
<BR>
<B>Call Logging -</B> This is set to true if the call_log step is in place in the extensions.conf file for all outbound and hang up 'h' extensions to log all calls. This should always be 1 because it is manditory for many of the system features to work properly.

<BR>
<A NAME="phones-user_switching_enabled">
<BR>
<B>Cambio de Usuario -</B> Set to true to allow user to switch to another user account. NOTE: If user switches they can initiate recording on the new user's phone conversation

<BR>
<A NAME="phones-conferencing_enabled">
<BR>
<B>Comunicacio`n -</B> fije para verdad para permitir que el usuario comience llamadas de conferencia con hasta que seis líneas externas.

<BR>
<A NAME="phones-admin_hangup_enabled">
<BR>
<B>Retraso del Admin -</B> sistema a verdad para permitir que el usuario pueda al retraso cualquier línea en la voluntad con astGUIclient. Buena idea de permitir solamente esto para los usuarios del Admin.

<BR>
<A NAME="phones-admin_hijack_enabled">
<BR>
<B>Secuestro del Admin -</B> fije para verdad para permitir que el usuario pueda asir y volver a dirigir a su extension cualquier línea en la voluntad con astGUIclient. Buena idea de permitir solamente esto para los usuarios del Admin. Pero es muy útil para los encargados.

<BR>
<A NAME="phones-admin_monitor_enabled">
<BR>
<B>Monitor del Admin -</B> fije para verdad para permitir que el usuario pueda asir y volver a dirigir a su extension cualquier línea en la voluntad con astGUIclient. Buena idea de permitir solamente esto para los usuarios del Admin. Pero es muy útil para los encargados y como herramienta del entrenamiento.

<BR>
<A NAME="phones-call_parking_enabled">
<BR>
<B>Parque de llamada -</B> el sistema a verdad para permitir que el usuario pueda parquear invita el asimiento astGUIclient para ser tomado por cualquier otro usuario astGUIclient en el sistema. Las llamadas permanecen en el asimiento para hasta que un retraso de la media-hora entonces. Permitido generalmente para todos.

<BR>
<A NAME="phones-updater_check_enabled">
<BR>
<B>Cheque de Updater -</B> fije para verdad para exhibir una advertencia del popup que el tiempo del updater no ha cambiado en 20 segundos. Útil para los usuarios del Admin.

<BR>
<A NAME="phones-AFLogging_enabled">
<BR>
<B>AF Logging -</B> Set to true to log many actions of astGUIclient usage to a text file on the user's computer.

<BR>
<A NAME="phones-QUEUE_ACTION_enabled">
<BR>
<B>Colas permitidas -</B> Impostare a true per avere applicazioni client utilizzano il sistema Asterisk centrale Coda. Necessario per il sistema di lavorare e raccomandato per tutti i telefoni.

<BR>
<A NAME="phones-CallerID_popup_enabled">
<BR>
<B>Ventana emergente del CallerID -</B> sistema a verdad para tener en cuenta los números definidos en el archivo de extensions.conf para enviar las pantallas del popup de CallerID a los usuarios astGUIclient.

<BR>
<A NAME="phones-voicemail_button_enabled">
<BR>
<B>Boto`n de VMail -</B> fije para verdad para exhibir el boton de VOICEMAIL y los mensajes cuentan la exhibicio`n en astGUIclient.

<BR>
<A NAME="phones-enable_fast_refresh">
<BR>
<B>Rápido restaure -</B> fije para verdad para permitir un nuevo índice de restauran de la informacion de la llamada para el astGUIclient. La tarifa inhabilitada defecto es el ms 1000 (1 segundo). Puede aumentar la carga de sistema si usted baja este número.

<BR>
<A NAME="phones-fast_refresh_rate">
<BR>
<B>Rápido restaure la tarifa -</B> en milisegundos. Utilizado solamente si es rápido restaure se permite. La tarifa inhabilitada defecto es el ms 1000 (1 segundo). Puede aumentar la carga de sistema si usted baja este número.

<BR>
<A NAME="phones-enable_persistant_mysql">
<BR>
<B>Persistant MySQL -</B> si está permitida la conexion astGUIclient seguirá conectada en vez de conectar cada segundo. Útil si usted hace que un rápido restaure la tarifa fijada. Aumentará el número de conexiones en su máquina de MySQL.

<BR>
<A NAME="phones-auto_dial_next_number">
<BR>
<B>Auto Marcar el Siguiente Número -</B> Se attivata la schermata agente comporre il numero successivo nella lista automaticamente alla disposizione di una telefonata a meno che non selezionati per PAUSE AGENTE DI COMPOSIZIONE sullo schermo disposizione.

<BR>
<A NAME="phones-VDstop_rec_after_each_call">
<BR>
<B>Parar de Grabar despue`s de cada llamada -</B> Se abilitata la schermata agente interromperà qualsiasi registrazione che sta accadendo dopo ogni chiamata è stato disposto. Utile se si sta facendo un sacco di registrazione o si sta utilizzando un modulo web per attivare la registrazione. 

<BR>
<A NAME="phones-enable_sipsak_messages">
<BR>
<B>Enable SIPSAK Messages -</B> Se attivato, il server invierà messaggi al telefono SIP da visualizzare sul display del telefono quando si accede all interfaccia web agente. Caratteristica funziona solo con i telefoni SIP e richiede l applicazione sipsak per essere installato sul server web. Il default è 0.

<BR>
<A NAME="phones-DBX_server">
<BR>
<B>Servidor de DBX -</B> el servidor de la base de datos de MySQL con el cual este usuario debe conectar.

<BR>
<A NAME="phones-DBX_database">
<BR>
<B>Base de datos de DBX -</B> la base de datos de MySQL con la cual este usuario debe conectar. El defecto es asterisco.

<BR>
<A NAME="phones-DBX_user">
<BR>
<B>Usuario de DBX -</B> la conexion del usuario de MySQL que este usuario debe utilizar al conectar. El defecto es cron.

<BR>
<A NAME="phones-DBX_pass">
<BR>
<B>Paso de DBX -</B> la contraseña del usuario de MySQL que este usuario debe utilizar al conectar. El defecto es 1234.

<BR>
<A NAME="phones-DBX_port">
<BR>
<B>DBX viran -</B> el puerto de MySQL hacia el lado de babor TCP que este usuario debe utilizar al conectar. El defecto es 3306.

<BR>
<A NAME="phones-DBY_server">
<BR>
<B>Servidor de DBY -</B> el servidor de la base de datos de MySQL con el cual este usuario debe conectar. Secondario server, non utilizzato attualmente.

<BR>
<A NAME="phones-DBY_database">
<BR>
<B>Base de datos de DBY -</B> la base de datos de MySQL con la cual este usuario debe conectar. El defecto es asterisco. Secondario server, non utilizzato attualmente.

<BR>
<A NAME="phones-DBY_user">
<BR>
<B>Usuario de DBY -</B> la conexion del usuario de MySQL que este usuario debe utilizar al conectar. El defecto es cron. Secondario server, non utilizzato attualmente.

<BR>
<A NAME="phones-DBY_pass">
<BR>
<B>Paso de DBY -</B> la contraseña del usuario de MySQL que este usuario debe utilizar al conectar. El defecto es 1234. Secondario server, non utilizzato attualmente.

<BR>
<A NAME="phones-DBY_port">
<BR>
<B>DBY viran -</B> el puerto de MySQL hacia el lado de babor TCP que este usuario debe utilizar al conectar. El defecto es 3306. Secondario server, non utilizzato attualmente.

<BR>
<A NAME="phones-alias_id">
<BR>
<B>ID Alias - </B> LID del alias utilizzati per consentire di telefono carico equilibrato login. senza spazi o altri caratteri speciali ammessi. Deve essere compreso tra 2 e 20 caratteri di lunghezza.

<BR>
<A NAME="phones-alias_name">
<BR>
<B>Alias Nome - </B> Il nome utilizzato per descrivere un alias cellulari, deve essere compresa tra 2 e 50 caratteri di lunghezza.

<BR>
<A NAME="phones-logins_list">
<BR>
<B>Cellulari Login Lista - </B> Lelenco separato da virgole di telefono cellulare utilizzato dati di accesso quando un agente accede tramite telefono cellulare carico equilibrato login. Lagente si trova lapplicazione server attivo con il minor numero di agenti e registrati in una chiamata da tale server di agenti su login.

<BR>
<A NAME="phones-template_id">
<BR>
<B>Template ID - </B> Questo è il file di conf che questo modello di telefono cellulare ID entrata utilizzerà per i suoi Asterisk impostazioni. Il valore di default è - INSUSSISTENZA --.

<BR>
<A NAME="phones-conf_override">
<BR>
<B>Conf Override Settings -</B> If populated, and the Template ID is set to --NONE-- then the contents of this field are used as the conf file entries for this phone. generate conf files for this phones server must be set to Y for this to work. This field should NOT contain the [extension] line, that will be automatically generated.

<BR>
<A NAME="phones-group_alias_id">
<BR>
<B>Gruppo Alias ID -</B> L ID del gruppo alias utilizzati dagli agenti per le chiamate in uscita chiamate da interfaccia agente con diversi ID chiamante. senza spazi o altri caratteri speciali ammessi. Deve essere compreso tra 2 e 20 caratteri di lunghezza.

<BR>
<A NAME="phones-group_alias_name">
<BR>
<B>Gruppo Alias Nome - </B> Il nome utilizzato per designare un gruppo di alias, deve essere compresa tra 2 e 50 caratteri di lunghezza.

<BR>
<A NAME="phones-caller_id_number">
<BR>
<B>Numero ID chiamante - </B> Il numero ID del chiamante utilizzato in questo gruppo di Alias. Deve essere solo cifre.

<BR>
<A NAME="phones-caller_id_name">
<BR>
<B>Caller ID Nome - </B> Il Caller ID nome che possono essere inviati con questo gruppo di Alias. Per quanto ne sappiamo questo funziona solo in Canada e il PRI circuiti IAX loop utilizzando un tronco attraverso Asterisk.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TABELLA SERVER</FONT></B><BR><BR>
<A NAME="servers-server_id">
<BR>
<B>ID Del Servidor -</B>  Este campo es donde usted pone el nombre del servidor del asterisco, no tiene que ser un submarino oficial del dominio, apenas un apodo para identificar el servidor a los usuarios del Admin.

<BR>
<A NAME="servers-server_description">
<BR>
<B>Descripcio`n del servidor -</B> el campo donde usted utiliza una frase pequeña para describir el servidor del asterisco.

<BR>
<A NAME="servers-server_ip">
<BR>
<B>IP address del servidor -</B> el campo donde usted puso el IP address de la red del servidor del asterisco.

<BR>
<A NAME="servers-active">
<BR>
<B>Activo -</B> fije si el servidor del asterisco es activo o inactivo.

<BR>
<A NAME="servers-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="servers-sysload">
<BR>
<B>Sistema di carico -</B> Queste due statistiche mostrano il loadavg di un sistema di 100 volte e la percentuale di utilizzo della CPU del server e viene aggiornato ogni minuto. Il loadavg dovrebbe essere in media al di sotto di 100, moltiplicato per il numero di CPU core il sistema è, per ottenere prestazioni ottimali. La percentuale di utilizzo della CPU dovrebbe rimanere al di sotto del 50 per ottenere prestazioni ottimali.

<BR>
<A NAME="servers-channels_total">
<BR>
<B>Live Canali -</B> Questo campo indica il numero attuale di Asterisk canali che sono in diretta su il sistema adesso. È importante notare che il numero di canali Asterisk è di solito molto più elevato rispetto al numero effettivo di chiamate su un sistema. Questo campo è aggiornato una volta ogni minuto.

<BR>
<A NAME="servers-disk_usage">
<BR>
<B>Utilizzo del disco -</B> Questo campo mostra lutilizzo del disco per ogni partizione su questo server. Questo campo è aggiornato una volta ogni minuto.

<BR>
<A NAME="servers-system_uptime">
<BR>
<B>Sistema Uptime -</B> Questo campo mostrerà l uptime del sistema di questo server. Questo campo aggiorna solo se configurato in tal senso dall amministratore.

<BR>
<A NAME="servers-asterisk_version">
<BR>
<B>Versione Di Asterisk -</B> Set the version of Asterisk that you have installed on this server. Examples: '1.2', '1.0.8', '1.0.7', 'CVS_HEAD', 'REALLY OLD', etc... This is used because versions 1.0.8 and 1.0.9 have a different method of dealing with Local/ channels, a bug that has been fixed in CVS v1.0, and need to be treated differently when handling their Local/ channels. Also, current CVS_HEAD and the 1.2 release tree uses different manager and command output so it must be treated differently as well.

<BR>
<A NAME="servers-max_trunks">
<BR>
<B>Max Trunks -</B> Questo campo determina il numero massimo di righe che l auto-dialer tenterà di chiamare su questo server. Se si vuole dedicare due completi PRI T1 a outbound composizione su un server, allora si dovrebbe impostare questo valore a 46. Tutte le chiamate in entrata o in linea manuali saranno imputate al totale pure. Il valore predefinito è 96.

<BR>
<A NAME="servers-outbound_calls_per_second">
<BR>
<B>Max chiede al secondo -</B> Questa impostazione determina il numero massimo di chiamate che possono essere immessi in uscita dalla selezione automatica di script sul server al secondo. Deve essere da 1 a 100. Il valore predefinito è 20.

<BR>
<A NAME="servers-telnet_host">
<BR>
<B>Host Telnet -</B> This is the address or name of the Asterisk server and is how the manager applications connect to it from where they are running. If they are running on the Asterisk server, then the default of 'localhost' is fine.

<BR>
<A NAME="servers-telnet_port">
<BR>
<B>Porta Telnet -</B> This is the port of the Asterisk server Manager connection and is how the manager applications connect to it from where they are running. The default of '5038' is fine for a standard install.

<BR>
<A NAME="servers-ASTmgrUSERNAME">
<BR>
<B>Usuario Del Manager -</B> The username or login used to connect genericly to the Asterisk server manager. Default is 'cron'

<BR>
<A NAME="servers-ASTmgrSECRET">
<BR>
<B>Contraseña del Manager -</B> The secret or password used to connect genericly to the Asterisk server manager. Default is '1234'

<BR>
<A NAME="servers-ASTmgrUSERNAMEupdate">
<BR>
<B>Usuario del Updater del Manager -</B> The username or login used to connect to the Asterisk server manager optimized for the Update scripts. Default is 'updatecron' and assumes the same secret as the generic user.

<BR>
<A NAME="servers-ASTmgrUSERNAMElisten">
<BR>
<B>Usuario Listen del Manager -</B> The username or login used to connect to the Asterisk server manager optimized for scripts that only listen for output. Default is 'listencron' and assumes the same secret as the generic user.

<BR>
<A NAME="servers-ASTmgrUSERNAMEsend">
<BR>
<B>Usuario Send del Manager -</B> The username or login used to connect to the Asterisk server manager optimized for scripts that only send Actions to the manager. Default is 'sendcron' and assumes the same secret as the generic user.

<BR>
<A NAME="servers-conf_secret">
<BR>
<B>Conf file segreto -</B> Questo è il segreto, o la password, per il server in automatico IAX-generated conf file per questo server su altri server. Limite è dash 20 caratteri alfanumerici e underscore accettato. Di default è di prova. Un forte secret file di configurazione deve essere di almeno 8 caratteri di lunghezza e di minuscole e maiuscole, nonché almeno un numero.

<BR>
<A NAME="servers-local_gmt">
<BR>
<B>Server GMT offset -</B> The difference in hours from GMT time not adjusted for Daylight-Savings-Time of the server. Default is '-5'

<BR>
<A NAME="servers-voicemail_dump_exten">
<BR>
<B>Extension de Vmail Dump  -</B> The extension prefix used on this server to send calls directly through agc to a specific voicemail box. Default is '85026666666666'

<BR>
<A NAME="servers-voicemail_dump_exten_no_inst">
<BR>
<B>Vmail Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="servers-answer_transfer_agent">
<BR>
<B>estensione di composizione automatica -</B> L estensione di default se non è presente nella campagna di inviare chiamate verso per la selezione automatica. L impostazione predefinita è '8365'

<BR>
<A NAME="servers-ext_context">
<BR>
<B>Contexto por Defecto -</B> The default dial plan context used for scripts that operate for this server. Default is 'default'

<BR>
<A NAME="servers-sys_perf_log">
<BR>
<B>Funcionamiento del sistema -</B> fijar esta opcio`n a Y permitirá laregistracion del stats del funcionamiento del sistema para lamáquina del servidor incluyendo carga de sistema, procesos delsistema y canales del asterisco en uso. El defecto es N.

<BR>
<A NAME="servers-vd_server_logs">
<BR>
<B>Log Server -</B> L impostazione di questa opzione per Y permetterà la registrazione di tutti gli script di sistema relativi alle loro file di log di testo. Impostando a N smetterà di scrivere i log su file per questi processi, anche la registrazione dello schermo di asterisco verrà disattivato se è impostato su N quando viene avviato Asterisk. Il valore predefinito è Y.

<BR>
<A NAME="servers-agi_output">
<BR>
<B>Salida de AGI -</B> L impostazione di questa opzione NONE disabilita l uscita da tutti gli script AGI relativi al sistema. L impostazione di questo per STDERR invierà l output AGI alla Asterisk CLI. L impostazione di questo a FILE invierà l output a un file nella directory logs. L impostazione di questo BOTH invierà in uscita sia per la CLI di Asterisk e un file di log. L impostazione predefinita è FILE.

<BR>
<A NAME="servers-balance_active">
<BR>
<B>Saldo Dialing -</B> L impostazione di questo campo per Y permetterà al server di effettuare chiamate di bilancio per le campagne nel sistema in modo che il livello del quadrante definito può essere soddisfatto, anche se non ci sono agenti collegati a quella campagna su questo server. L impostazione predefinita è N.

<BR>
<A NAME="servers-balance_rank">
<BR>
<B>Equilibrio Classifica -</B> Questo campo consente di impostare l-ordine in cui il server deve essere utilizzato per chiamate di equilibrio, se l-equilibrio di composizione è abilitato. Il server con il rango più alto sarà utilizzato prima immissione Balance riempire le chiamate. Di default è 0.

<BR>
<A NAME="servers-balance_trunks_offlimits">
<BR>
<B>Equilibrio Offlimits -</B> Questa impostazione definisce il numero di linee per non permettere i processi di selezione equilibrio da usare. Ad esempio, se si dispone di 40 tronchi max e l equilibrio offlimits è impostato su 10 vi sarà solo in grado di utilizzare 30 linee urbane per la selezione equilibrio. Il default è 0 .

<BR>
<A NAME="servers-recording_web_link">
<BR>
<B>Registrazione Web Link - </B> Questa impostazione consente di ignorare limpostazione predefinita della visualizzazione della registrazione in collegamento lamministratore pagine web. Il valore di default è SERVER_IP.

<BR>
<A NAME="servers-alt_server_ip">
<BR>
<B>Supplente di registrazione IP Server - </B> Questa impostazione è dove si può mettere un server IP o nome di altre macchine che possono essere utilizzati al posto del server_ip tra i link alle registrazioni ai admin pagine web. Il valore di default è vuota.

<BR>
<A NAME="servers-external_server_ip">
<BR>
<B>IP Server esterno -</B> Questa impostazione è dove si può mettere un indirizzo IP o il nome del server di altra macchina che può essere utilizzato al posto del IP_server quando si utilizza un webphone nell'interfaccia dell'agente. Per farlo funzionare si deve avere anche la voce telefoni impostato per utilizzare l'IP esterno del server. Il valore predefinito è vuoto.

<BR>
<A NAME="servers-active_twin_server_ip">
<BR>
<B>Attivo IP server Twin -</B> Alcuni sistemi richiedono la creazione di server di telefonia a coppie. Questa impostazione è dove si può mettere l IP del server di un altro server che questo server è gemellata con. Il valore predefinito è vuota per disabili.

<BR>
<A NAME="servers-active_asterisk_server">
<BR>
<B>Attivo Asterisk Server -</B> Se Asterisk non è in esecuzione su questo server, o se i processi di composizione non dovrebbero utilizzare questo server, o se stanno solo usando questo server per altri script, come lo script tramoggia di carico si vorrebbe impostare questo N. predefinito è Y.

<BR>
<A NAME="servers-auto_restart_asterisk">
<BR>
<B>Auto-Restart Asterisk -</B> Se Asterisk è in esecuzione su questo server e si desidera che il sistema per assicurarsi che sarà riavviato nel caso in cui si blocca, si potrebbe prendere in considerazione l attivazione di questa impostazione. Se attivato, il sistema controlla ogni minuto per vedere se Asterisk è in esecuzione, e se non è tenterà di riavviarlo. Questo processo non verrà eseguito nei primi 5 minuti dopo che un sistema è stato up. L impostazione predefinita è N.

<BR>
<A NAME="servers-asterisk_temp_no_restart">
<BR>
<B>Temp No-Restart Asterisk -</B> Se Auto-Restart Asterisk è abilitato su questo server, attivando questa impostazione impedisce il processo di riavvio automatico l esecuzione fino a quando il server viene riavviato. L impostazione predefinita è N.

<BR>
<A NAME="servers-active_agent_login_server">
<BR>
<B>Attivo Server Agent -</B> L impostazione di questa opzione per N impedirà agenti di essere in grado di accedere al server attraverso lo schermo dell agente. Questo è molto utile quando si utilizza una configurazione equilibrata telefono carico login. Il valore predefinito è Y.

<BR>
<A NAME="servers-generate_conf">
<BR>
<B>Generate conf files -</B> Se si desidera che il sistema di auto-generare conf asterisco sulla base delle voci telefoni, le voci portanti e bilanciamento del carico di configurazione all interno del sistema, quindi impostare questo a Y. Il valore predefinito è Y.

<BR>
<A NAME="servers-rebuild_conf_files">
<BR>
<B>Ricostruire conf - </B> Se si desidera forzare una ricostruzione del Asterisk conf file o se uno dei telefoni cellulari o vettore voci hanno cambiato allora questo deve essere impostato a Y. Dopo il file di configurazione sono stati generati e Asterisk ha stati ricaricati quindi questo sarà cambiato in N. Il valore di default è Y.

<BR>
<A NAME="servers-rebuild_music_on_hold">
<BR>
<B>Ricostruire Music On Hold -</B> Se si vuole imporre una ricostruzione della musica su file di attesa o se la musica su voci di attesa o voci di server hanno cambiato allora questo dovrebbe essere impostato su Y. Dopo la musica in attesa dei file sono state sincronizzate e ricaricate allora questo sarà cambiato N. Il valore predefinito è Y.

<BR>
<A NAME="servers-sounds_update">
<BR>
<B>Suoni Update -</B> Se si vuole forzare un controllo dei file audio su questo server, e l-archivio centrale audio è abilitato come impostazione di sistema, quindi questo settore permetterà i suoni di aggiornamento per eseguire in cima successiva del minuto. Ogni volta che un file audio viene caricato tramite l-interfaccia web questa viene automaticamente impostato su Y per tutti i server che sono attivi Asterisk. Di default è N.

<BR>
<A NAME="servers-recording_limit">
<BR>
<B>Limite di registrazione -</B> Questo campo è possibile impostare il numero massimo di minuti che una chiamata di registrazione avviata dal sistema può essere. Il valore predefinito è 60 minuti.

<BR>
<A NAME="servers-carrier_logging_active">
<BR>
<B>Carrier Logging Attivo-</B> Questa impostazione consente di registrare tutti i codici hangup ritorno per qualsiasi composizione lista delle chiamate in uscita che si sta ponendo. Predefinito è N.

<BR>
<A NAME="servers-custom_dialplan_entry">
<BR>
<B>Personalizzato Entry Dialplan -</B> Questo campo consente di inserire in tutti gli elementi dialplan che si desidera per il server, le linee saranno aggiunti al contesto di default.






<BR><BR><BR><BR>

<B><FONT SIZE=3>conf_templates TABELLA</FONT></B><BR><BR>
<A NAME="conf_templates-template_id">
<BR>
<B>Template ID - </B> Questo campo deve essere di almeno 2 caratteri e non più di 15 caratteri, senza spazi. Questo è lID che sarà utilizzato per identificare il modello di conf in tutto il sistema.

<BR>
<A NAME="conf_templates-template_name">
<BR>
<B>Nome modello - </B> Questo è il nome descrittivo del file di conf modello entrata.

<BR>
<A NAME="conf_templates-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="conf_templates-template_contents">
<BR>
<B>Template Sommario - </B> Questo campo è possibile entrare nello specifico delle impostazioni per essere utilizzati da tutti i telefoni o e-vettori che sono impostate per lutilizzo di questo modello conf. I campi che non dovrebbero essere inclusi in questa casella sono i seguenti: segreto, accountcode, account, nome utente e la casella e-mail.





<BR><BR><BR><BR>

<B><FONT SIZE=3>server_carriers TABELLA</FONT></B><BR><BR>
<A NAME="server_carriers-carrier_id">
<BR>
<B>ID Vettore - </B> Questo campo deve essere di almeno 2 caratteri e non più di 15 caratteri, senza spazi. Questo è lID che sarà utilizzato per individuare il vettore per questo specifico entrata in tutto il sistema.

<BR>
<A NAME="server_carriers-carrier_name">
<BR>
<B>Nome Vettore - </B> Questo è il nome descrittivo del vettore entrata.

<BR>
<A NAME="server_carriers-carrier_description">
<BR>
<B>Carrier Descrizione -</B> Questo è messo nei commenti dei file asterisco conf sopra il dialplan e le voci del conto. Massimo 255 caratteri.

<BR>
<A NAME="server_carriers-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="server_carriers-registration_string">
<BR>
<B>Registrazione Stringa - </B> Questo campo è possibile immettere nella stringa esatta necessaria la SIP IAX o file di configurazione per la registrazione al provider. Opzionale, ma altamente raccomandato se il vettore consente di registrazione.

<BR>
<A NAME="server_carriers-template_id">
<BR>
<B>Template ID - </B> Questo campo opzionale vi permette di scegliere un file di configurazione per questo modello vettore entrata.

<BR>
<A NAME="server_carriers-account_entry">
<BR>
<B>Account Entry -</B> Questo campo viene utilizzato se non è stato selezionato un modello da utilizzare, ed è dove è possibile inserire nelle impostazioni dell account specifiche da utilizzare per questo vettore. Se si intende prendere in chiamate in entrata da questo tronco vettore si potrebbe desiderare di impostare il contesto = trunkinbound in questo campo in modo che è possibile utilizzare il processo di DID di gestione all interno del sistema.

<BR>
<A NAME="server_carriers-protocol">
<BR>
<B>Protocollo - </B> Questo campo permette di definire il protocollo da utilizzare per lingresso del vettore. Attualmente solo SIP e IAX sono supportati.

<BR>
<A NAME="server_carriers-globals_string">
<BR>
<B>Globals Stringa - </B> Questo campo opzionale permette di definire una variabile globale da utilizzare per il vettore in dialplan.

<BR>
<A NAME="server_carriers-dialplan_entry">
<BR>
<B>Entrata Dialplan - </B> Questo campo opzionale permette di definire una serie di dialplan voci da utilizzare per questo vettore.

<BR>
<A NAME="server_carriers-server_ip">
<BR>
<B>IP Server - </B> Questo è il server che questo specifico vettore record è associato con.

<BR>
<A NAME="server_carriers-active">
<BR>
<B>Attiva - </B> Questo definisce se il vettore sarà incluso nel generata automaticamente conf file o non.





<BR><BR><BR><BR>

<B><FONT SIZE=3>TABLA DE CONFERENCIAS</FONT></B><BR><BR>
<A NAME="conferences-conf_exten">
<BR>
<B>Número de la Conferencia -</B> Questo campo è dove mettere la conferenza numero dialplan ConfAut. Si raccomanda inoltre che il numero ConfAut in meetme.conf corrisponde questo numero per ogni voce. Questo è per le conferenze nella schermata utente astGUIclient e viene utilizzato per la funzionalità leave-3way-call nel sistema.

<BR>
<A NAME="conferences-server_ip">
<BR>
<B>IP del servidor -</B> El menú donde usted selecciona el servidor del asterisco que esta conferencia estará encendido.




<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>SERVER_Bauli TABELLA</FONT></B><BR><BR>
	<A NAME="server_trunks">
	<BR>
	<B>Trunks Server consente di limitare le linee in uscita che vengono utilizzati su questo server per la composizione campagna su una base per-campagna. Hai la possibilità di riservare un certo numero di linee per essere utilizzati da una sola campagna, oltre a permettere che la campagna di correre sulle sue linee riservati in qualunque linee rimangono aperti, purché le linee totali utilizzati dal sistema su questo server è inferiore all impostazione Max Trunks. Non avendo tutti questi dati consentirà la campagna che compone la linea prima di avere come molte linee come si può ottenere con l impostazione Max Trunks.</B>
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SYSTEM_SETTINGS TABELLA</FONT></B><BR><BR>
<A NAME="settings-use_non_latin">
<BR>
<B>Uso No-Latino -</B> esta opcio`n permite que usted omita la escritura dela exhibicio`n de la tela los caracteres del uso UTF8 y que no haganinguna expresio`n regular de la latino-cara`cter-familia que sefiltra ni que no exhiba el formato. El defecto es 0.

<BR>
<A NAME="settings-webroot_writable">
<BR>
<B>Webroot escribible -</B> este ajuste permite que usted defina si losarchivos de la temperatura y los archivos de la autentificacio`n seancolocados en el webroot en su web server. El defecto es 1.

<BR>
<A NAME="settings-agent_disable">
<BR>
<B>Agente disattivare la visualizzazione -</B> Questo campo viene utilizzato per selezionare quando visualizzare un agente avvisi quando la sessione è stata disabilitata dal sistema, un azione manager o da una misura esterna. L impostazione NOT_ACTIVE disabiliterà il messaggio sullo schermo agenti. L impostazione LIVE_AGENT visualizza solo il messaggio disabilitata quando registrare gli agenti auto_calls è stato rimosso, come ad esempio durante una disconnessione forza o la disconnessione di emergenza. Il valore predefinito è ALL.

<BR>
<A NAME="settings-frozen_server_call_clear">
<BR>
<B>Cancella chiamate congelati -</B> Questa opzione può consentire la possibilità per la pagina Rapporti generali e lo script AST_timecheck.pl opzionale per cancellare le auto_calls voci relative a un server congelato in modo da non incidere instradamento delle chiamate. Il default è 0 per disabili.

<BR>
<A NAME="settings-allow_sipsak_messages">
<BR>
<B>Allow SIPSAK Messages -</B> Se impostato a 1, questo permetterà l impostazione della tabella telefoni sipsak a lavorare se il telefono è impostato il protocollo SIP. Il server invia messaggi al telefono SIP da visualizzare sul display del telefono quando si accede al sistema. Questa caratteristica funziona solo con i telefoni SIP e richiede l applicazione sipsak per essere installato sul server web che l agente è collegato. Il default è 0. 

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agente attivo API - </B> Se è impostato a 1, questo permetterà lagente interfaccia API di funzionare. Il valore di default è 0. 

<BR>
<A NAME="settings-admin_home_url">
<BR>
<B>URL casero del Admin -</B> esta es la Direccion del URL o del Web siteque usted irá si usted chasca encendido el acoplamiento CASERO en latapa de la página de admin.php.

<BR>
<A NAME="settings-admin_modify_refresh">
<BR>
<B>Admin Modificare Auto-Refresh -</B> Questo è l'intervallo di aggiornamento in secondi degli schermi modificano in questa interfaccia di amministrazione. L'impostazione di questo a 0 lo disabilita, impostandolo di sotto del 5 saranno principalmente rendere gli schermi modificano inutilizzabili perché si aggiorna troppo in fretta di modificare i campi. Questa opzione è utile in situazioni in cui più di un manager è il controllo delle impostazioni su una campagna attiva o in gruppo in modo che le impostazioni vengono aggiornate frequentemente. Il default è 0.

<BR>
<A NAME="settings-nocache_admin">
<BR>
<B>Admin No-Cache -</B> L'impostazione di questo ad 1 impostare tutte le pagine di amministrazione al browser web no-cache, in modo che ogni schermo deve essere ricaricata ogni volta che viene visualizzato, anche se cliccando di nuovo sul browser. Il default è 0 per disabili.

<BR>
<A NAME="settings-enable_agc_xfer_log">
<BR>
<B>Permita el logfile de la transferencia del agente -</B> esta opcio`nregistrará a un logfile del texto en el webserver cada vez que unallamada se transfiere a un agente. El defecto es 0, inhabilitado.

<BR>
<A NAME="settings-enable_agc_dispo_log">
<BR>
<B>Abilita Disposition Logfile agente -</B> Questa opzione si accede ad un file di log di testo sul server web ogni volta che viene dispositioned una chiamata da un agente. Il default è 0, disattivato.

<BR>
<A NAME="settings-timeclock_end_of_day">
<BR>
<B>Orologio End Of Day - </B> Questa impostazione definisce il momento in cui tutti gli utenti devono essere registrati automaticamente dal sistema orologio. Funziona solo una volta al giorno. deve essere solo il 4 cifre 2 cifre 2 cifre ora e 24 minuti in ora di tempo. Il valore di default è 0000.

<BR>
<A NAME="settings-default_local_gmt">
<BR>
<B>Predefinito locale GMT -</B> Questa impostazione definisce ciò che verrà utilizzato per impostazione predefinita quando nuovi telefoni e server vengono aggiunti utilizzando questa interfaccia di amministrazione web. Il valore predefinito è -5.

<BR>
<A NAME="settings-default_voicemail_timezone">
<BR>
<B>Predefinito Casella Vocale Zone -</B> Questa impostazione definisce quale zona verrà utilizzato per impostazione predefinita quando nuovi telefoni e caselle vocali vengono creati. L'elenco delle zone disponibili è direttamente presa dal file voicemail.conf. Il valore predefinito è orientale.

<BR>
<A NAME="settings-agents_calls_reset">
<BR>
<B>Agenti chiamate Ripristina -</B> Questa impostazione definisce se connesso in agenti attivi telefonate e le registrazioni devono essere resettato alla fineOrologio del giorno. Il valore predefinito è 1 per abilitato.

<BR>
<A NAME="settings-timeclock_last_reset_date">
<BR>
<B>Ultimo orologio automatico Logout - </B> Questo campo visualizza la data degli ultimi auto-logout.

<BR>
<A NAME="settings-vdc_header_date_format">
<BR>
<B>Agente Screen Formato Data Header -</B> Questo menu consente di scegliere il formato della data e dell ora che appare nella parte superiore dello schermo dell agente. Le opzioni per questa impostazione sono: di default è MS_DASH_24HR<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Default data con formato giorno mese anno seguita da 24 ore di tempo<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - Stati Uniti dAmerica con il formato della data giorno mese anno seguita da 24 ore di tempo<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Europeo con il formato della data giorno mese anno seguita da 24 ore di tempo<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Testo abbreviato con il formato della data giorno mese seguita da 24 ore di tempo<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Default data con formato giorno mese anno seguita da 12 ore di tempo<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - Stati Uniti dAmerica con il formato della data giorno mese anno seguita da 12 ore di tempo<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Europeo con il formato della data giorno mese anno seguita da 12 ore di tempo<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Testo abbreviato con il formato della data giorno mese seguita da 12 ore di tempo<BR>

<BR>
<A NAME="settings-vdc_customer_date_format">
<BR>
<B>Cliente di agente Screen Formato Data -</B> Questo menu consente di scegliere il formato della data e il tempo che mostra nella parte superiore della sezione Informazioni utente della schermata agente. Le opzioni per questa impostazione sono: di default è AL_TEXT_AMPM<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Default data con formato giorno mese anno seguita da 24 ore di tempo<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - Stati Uniti dAmerica con il formato della data giorno mese anno seguita da 24 ore di tempo<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Europeo con il formato della data giorno mese anno seguita da 24 ore di tempo<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Testo abbreviato con il formato della data giorno mese seguita da 24 ore di tempo<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Default data con formato giorno mese anno seguita da 12 ore di tempo<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - Stati Uniti dAmerica con il formato della data giorno mese anno seguita da 12 ore di tempo<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Europeo con il formato della data giorno mese anno seguita da 12 ore di tempo<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Testo abbreviato con il formato della data giorno mese seguita da 12 ore di tempo<BR>

<BR>
<A NAME="settings-vdc_header_phone_format">
<BR>
<B>Agente Cliente Telefono Formato schermo -</B> Questo menu consente di scegliere il formato del numero di telefono del cliente che si presenta nella sezione di stato dello schermo dell agente. Le opzioni per questa impostazione sono: di default è US_PARN<BR>
US_DASH 000-000-0000 - USA dash separati numero di telefono<BR>
US_PARN (000)000-0000 - USA dash separati numero con prefisso parentesi<BR>
MS_NODS 0000000000 - N. formattazione<BR>
UK_DASH 00 0000-0000 - UK dash separati numero di telefono con lo spazio dopo il codice di città<BR>
AU_SPAC 000 000 000 - Australia spazio separato numero telefonico<BR>
IT_DASH 0000-000-000 - Italy dash separati numero di telefono<BR>
FR_SPAC 00 00 00 00 00 - France spazio separato numero telefonico<BR>

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agent interface API Access Attivo-</B> This option allows you to enable or disable the agent interface API. Default is 0.

<BR>
<A NAME="settings-agentonly_callback_campaign_lock">
<BR>
<B>Solo agente di richiamata Campagna Lock -</B> Questa opzione definisce se richiamate AGENTONLY sono bloccati per la campagna che l-agente originariamente creato sotto. Impostare questo a 1 significa che l-agente non può che chiamarli dalla campagna sono stati fissati nel quadro, 0 significa che l-agente può accedervi non importa quale campagna sono collegato. Di default è 1.

<BR>
<A NAME="settings-sounds_central_control_active">
<BR>
<B>Centrale Sound Control Attivo-</B> Questa opzione definisce se il sistema audio di sincronizzazione è attivo su tutti i server. Di default è 0 per inattivo.

<BR>
<A NAME="settings-sounds_web_server">
<BR>
<B>Suoni Web Server -</B> Questo è il nome del server o l-indirizzo IP del server Web che sarà la gestione dei file audio su questo sistema, questo deve corrispondere il nome del server o IP della macchina che si sta tentando di accedere alla pagina web audio_store.php o non funzionerà . Di default è 127.0.0.1.

<BR>
<A NAME="settings-sounds_web_directory">
<BR>
<B>Suoni Web Directory -</B> Questa auto-generata nome della directory è creata in modo casuale dal sistema come il luogo che l-archivio audio saranno mantenute. Tutti i file audio risiede in questa directory.

<BR>
<A NAME="settings-admin_web_directory">
<BR>
<B>Web Directory Admin -</B> Questa è la directory web che il contenuto web administation, come admin.php, trovi Per capire la vostra directory web Admin, è tutto ciò che si trova tra il nome di dominio e il admin.php l URL su questa pagina senza l  iniziando e terminando barre.

<BR>
<A NAME="settings-active_voicemail_server">
<BR>
<B>Attivo Server Casella Vocale -</B> Nei sistemi multi-server, questo è il server che consente di gestire tutte le caselle di posta vocale. Questo server è anche il luogo dove il dial-in generati prompt saranno caricati dai, il 8.168 registrazioni.

<BR>
<A NAME="settings-allow_voicemail_greeting">
<BR>
<B>Consentire Casella Vocale Saluto Chooser -</B> Se questa impostazione è abilitata vi permetterà di scegliere un file audio dal negozio audio da riprodurre come messaggio di benvenuto a una casella vocale specifica. Il default è 0 per disabili.

<BR>
<A NAME="settings-outbound_autodial_active">
<BR>
<B>Outbound Auto-Dial Attivo-</B> Questa opzione permette di abilitare o disabilitare in uscita auto-selezione all interno del sistema, impostando questo campo a 0 rimuoverà le liste e filtri sezioni e molti campi dalle schermate di modifica Campagna. Manuale di composizione iscrizione sarà ancora consentita all interno della schermata di agente, ma nessuna selezione elenco sarà possibile. Default è 1 per attivo.

<BR>
<A NAME="settings-disable_auto_dial">
<BR>
<B>Disattivare Auto-Dial -</B> Questa opzione è modificabile solo da un amministratore di sistema. Esso non rimuoverà tutte le opzioni dall interfaccia web di gestione, ma impedirà qualsiasi auto-composizione di porta accada sul sistema. Solo chiamate Manuale Quadrante in uscita attivate direttamente dagli agenti funzioneranno se questa opzione è abilitata. Di default è 0 per inattiva.

<BR>
<A NAME="settings-auto_dial_limit">
<BR>
<B>Ratio Dial Limit -</B> Questo è il limite massimo del livello di linea automatica nella schermata di campagna.

<BR>
<A NAME="settings-outbound_calls_per_second">
<BR>
<B>Max COMPILA invita al secondo -</B> Questa impostazione determina il numero massimo di chiamate che possono essere messi da parte lauto-fill-in uscita di selezione automatica di script per tutti i server, al secondo. Deve essere da 1 a 200. Il valore predefinito è 40.

<BR>
<A NAME="settings-allow_custom_dialplan">
<BR>
<B>Consentire Custom Dialplan Entries -</B> Questa opzione consente di inserire linee di dialplan personalizzati in menu chiamate, server e impostazioni di sistema. Il default è 0 per inattiva.

<BR>
<A NAME="settings-pllb_grouping_limit">
<BR>
<B>PLLB Raggruppamento Limit -</B> Telefono Accedi Load Balancing Limit Raggruppamento. Se Raggruppamento PLLB è impostato su CASCATA a livello di campagna questa impostazione determinerà il numero di agenti accettabile su ogni server in tutte le campagne. Il valore predefinito è 100.

<BR>
<A NAME="settings-generate_cross_server_exten">
<BR>
<B>Genera Cross-Server Extensions Phone -</B> Questa opzione, se impostato a 1 genererà voci dialplan per ogni telefono su un sistema multi-server. Il default è 0 per inattiva.

<BR>
<A NAME="settings-user_territories_active">
<BR>
<B>Territori Utente Attivo -</B> Questa impostazione consente di abilitare i Territori impostazioni dell utente nella schermata di modifica dell utente. Questa funzionalità è stata aggiunta per consentire una maggiore integrazione con un installazione personalizzata Vtiger ma può avere applicazioni nel sistema da solo così. Il default è 0 per disabili.

<BR>
<A NAME="settings-enable_second_webform">
<BR>
<B>Consentire Webform Seconda -</B> This setting allows you to have a second web form for campaigns and in-groups in the agent interface. Default is 0 for disabled.

<BR>
<A NAME="settings-enable_tts_integration">
<BR>
<B>Consentire l-integrazione TTS -</B> Questa impostazione consente di attivare Text To Speech integrazione con Cepstral. Questo è attualmente disponibile solo per le campagne di tipo uscita Indagine. Di default è 0 per disabili.

<BR>
<A NAME="settings-callcard_enabled">
<BR>
<B>Abilita CallCard -</B> Questa impostazione consente le funzioni CallCard per consentire ai chiamanti di utilizzare i numeri di pin e card_ids che hanno un equilibrio di minuti e gli equilibri possono avere agente di conversazione per le chiamate dei clienti al-gruppi dedotti. Il default è 0 per disabili.

<BR>
<A NAME="settings-custom_fields_enabled">
<BR>
<B>Abilita campi elenco personalizzati -</B> Questa impostazione consente l'usanza caratteristica dei campi che consente di personalizzare i campi dati da definire l'interfaccia di amministrazione web per ogni base-list e quindi avere a disposizione quei campi in una scheda FORM all'agente nell'interfaccia web agent. Il default è 0 per disabili.

<BR>
<A NAME="settings-test_campaign_calls">
<BR>
<B>Abilita chiamate di prova della campagna -</B> Questa impostazione consente la possibilità di inserire un codice telefono e numero di telefono nei campi nella parte inferiore dello schermo Dettagliata campagna ed effettuare una telefonata a quel numero, come se si trattasse di un vantaggio di essere auto-selezionato nel sistema. Il numero di telefono viene memorizzato come una nuova guida nel Manuale delle liste ID lista di selezione. La campagna deve essere attivo per questa funzione deve essere abilitata, e si raccomanda che le liste assegnati alla campagna tutti essere impostato su inattivo. Il prefisso di selezione, timeout di composizione e di tutte le funzioni di selezione altre collegate, fatta eccezione per la DNC opzioni call e di tempo, influenzerà la composizione del numero di test. La telefonata sarà posizionato sul server selezionato come server voicemail nelle impostazioni di sistema. Il default è 0 per disabili.

<BR>
<A NAME="settings-expanded_list_stats">
<BR>
<B>Abilita Statistiche elenco esteso -</B> Questa impostazione consente a due colonne aggiuntive per essere visualizzati sulla maggior parte delle tabelle nell'elenco di disaggregazione di stato la modifica dell'elenco e le pagine di modifica della campagna. Penetrazione è definita come la percentuale di cavi che sono pari o superiore al limite di conteggio di campagna chiamata e, oppure lo stato è contrassegnato come completato. Il valore predefinito è 1 per abilitato.

<BR>
<A NAME="settings-country_code_list_stats">
<BR>
<B>Elenco Codici Paese Stats -</B> Questa impostazione, se attivata mostrerà un codice di paese riepilogo ripartizione sulla lista modificare schermo. Il default è 0 per disabili.

<BR>
<A NAME="settings-enhanced_disconnect_logging">
<BR>
<B>Maggiore Disconnect Logging -</B> Questa impostazione consente la registrazione delle chiamate che ricevono un segnale CONGESTIONE con un codice a causa di 1, 19, 21, 34 o 38. Noi di solito non consigliamo di abilitare questa negli Stati Uniti. Il default è 0 per disabili.

<BR>
<A NAME="settings-campaign_cid_areacodes_enabled">
<BR>
<B>Abilita IndicativoLocalità Campagna CID -</B> Questa impostazione consente la possibilità di impostare specifici numeri identificativi dei chiamanti in uscita da utilizzare per campagna. Il valore predefinito è 1 per abilitato.

<BR>
<A NAME="settings-did_ra_extensions_enabled">
<BR>
<B>Abilita distanza sostituisce interno di un agente -</B> Questa impostazione consente di avere DID sostituzioni di estensione per agente remoto le chiamate instradate attraverso in-gruppi. Il default è 0 per disabili.

<BR>
<A NAME="contact_information">
<A NAME="settings-contacts_enabled">
<BR>
<B>Contatti Enabled -</B> Questa impostazione consente ai Contatti sotto-sezione in Admin che consente al gestore di aggiungere modificare o cancellare i contatti nel sistema che possono essere utilizzati come parte di un trasferimento personalizzato in una campagna in cui un agente può cercare i contatti in base al nome Cognome o in ufficio numero e quindi selezionare uno dei tanti numeri associati a tale contatto. Questa funzione viene spesso utilizzata dagli operatori o funzioni del quadro in cui l'utente avrebbe bisogno di trasferire una chiamata a un non-agent telefono. Il default è 0 per disabili.

<BR>
<A NAME="settings-call_menu_qualify_enabled">
<BR>
<B>Chiamata Menu Qualificati Enabled -</B> Questa impostazione abilita l'opzione nel menu di chiamata per mettere una qualifica SQL sulle persone che ascoltano quel menu chiamata. Per ulteriori informazioni sul funzionamento di tale funzione, vedere la Guida per la chiamata dei menu. Il default è 0 per disabili.

<BR>
<A NAME="settings-level_8_disable_add">
<BR>
<B>Livello 8 Disattiva Aggiungi -</B> Questa impostazione, se attivata impedisce qualsiasi livello 8 all'utente di aggiungere o copiare qualsiasi record nel sistema, non importa ciò che i loro impostazioni utente sono. Sono escluse da queste restrizioni sono la possibilità di aggiungere i numeri di telefono DNC e filtro Gruppi e la pagina Aggiungi un nuovo piombo. Il default è 0 per disabili.

<BR>
<A NAME="settings-admin_list_counts">
<BR>
<B>Lista Conti Admin -</B> Questa impostazione consente di disabilitare la lista conta che compaiono negli elenchi elenco e gli schermi modify Campagna. Il default è 1 per abilitato.

<BR>
<A NAME="settings-allow_emails">
<BR>
<B>Consenti email -</B> Questo è dove si può impostare se questo sistema sarà in grado di ricevere e-mail in entrata, oltre alle telefonate.

<BR>
<A NAME="settings-first_login_trigger">
<BR>
<B>Accedi primo trigger -</B> Questa impostazione consente la configurazione iniziale dello schermo del server da mostrare all'amministratore al primo accesso al sistema.

<BR>
<A NAME="settings-default_phone_registration_password">
<BR>
<B>Predefinito Password Telefonoword Registrazione -</B> Questa è la password di registrazione predefinita utilizzata quando nuovi telefoni vengono aggiunte al sistema. Il valore predefinito è di prova.

<BR>
<A NAME="settings-default_phone_login_password">
<BR>
<B>Predefinito Password Telefonoword Login -</B> Questo è il telefono predefinito password di accesso utilizzata per la web nuovi telefoni vengono aggiunti al sistema. Il valore predefinito è di prova.

<BR>
<A NAME="settings-default_server_password">
<BR>
<B>Default Server password -</B> Questa è la password del server predefinito utilizzato quando i server vengono aggiunti nuovi al sistema. Il valore predefinito è di prova.

<BR>
<A NAME="settings-slave_db_server">
<BR>
<B>Slave Database Server -</B> Se avete un server slave database MySQL, quindi immettere l'indirizzo IP locale per il server qui. Questa opzione è attualmente utilizzato solo dai rapporti selezionati l'opzione successiva e non ha nulla a che fare con la configurazione automatica di MySQL master-slave di replica. Il valore predefinito è vuota per disabili.

<BR>
<A NAME="settings-reports_use_slave_db">
<BR>
<B>Rapporti da utilizzare DB Slave -</B> Questa opzione consente di selezionare i report che si desidera avere utilizzare il database MySQL schiavo come definito nell opzione sopra invece del database master che il sistema live è in esecuzione. È necessario impostare la replica schiavo MySQL prima di poter attivare questa opzione. Il valore predefinito è vuota per disabili.

<BR>
<A NAME="settings-default_field_labels">
<BR>
<B>Di campo predefiniti di etichette -</B> Questi 19 campi consentono di impostare il nome come apparirà nell interfaccia agente così come la pagina di piombo di modifica amministrativa. Il valore predefinito è vuoto, che utilizzerà i valori predefiniti hard-coded nell interfaccia agente. È inoltre possibile impostare una etichetta per --- NASCONDI --- per nascondere sia l etichetta e il campo.

<BR>
<A NAME="settings-label_hide_field_logs">
<BR>
<B>Nascondi etichette in registri delle chiamate -</B> Se l'etichetta è impostato su NASCONDI --- --- poi i registri delle chiamate degli agenti, se abilitata sulla campagna, si mostrano ancora sul campo e dati a meno che questa opzione è impostata su Y. Il valore predefinito è N.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>Caratteristiche di AttivoQC - </B> Questa opzione consente di attivare o disattivare la qualità o le caratteristiche di controllo della qualità. Il valore di default è 0 per inattive.

<BR>
<A NAME="settings-default_webphone">
<BR>
<B>Predefinito Webphone -</B> Se impostato a 1, questa opzione farà tutti i nuovi telefoni sono creati Imposta come predefinito set Webphone a Y. è 0.

<BR>
<A NAME="settings-webphone_systemkey">
<BR>
<B>Webphone Key System -</B> Se il vostro sistema o il provider lo richiede, è qui la chiave di sistema per la webphone deve essere inserito dentro di default è vuota.

<BR>
<A NAME="settings-default_codecs">
<BR>
<B>Predefinite Codecs -</B> È possibile definire un elenco delimitato da virgole di codec da impostare come i codec di default per tutti i sistemi. Le opzioni per i codec sono ulaw, alaw, GSM, G729, Speex, G722, G723, G726, iLBC, ... Il valore predefinito è vuoto.

<BR>
<A NAME="settings-custom_dialplan_entry">
<BR>
<B>Personalizzato Entry Dialplan -</B> Questo campo consente di inserire in tutti gli elementi dialplan che si desidera per tutti i server Asterisk, le linee saranno aggiunti al contesto di default.

<BR>
<A NAME="settings-reload_dialplan_on_servers">
<BR>
<B>Ricarica Dialplan sui server -</B> Questa opzione permette di forzare un reload del dialplan Asterisk su tutti i server nel cluster. Se sono state apportate modifiche nella voce personalizzata Dialplan sopra si consiglia di impostare questo valore a 1 e presentare ad avere tali modifiche entreranno in vigore sui server.

<BR>
<A NAME="settings-noanswer_log">
<BR>
<B>Risposta No-Log -</B> Questa opzione consente di registrare le chiamate di auto-selezione che non trovano risposta in una tabella separata. Il valore predefinito è N.

<BR>
<A NAME="settings-did_agent_log">
<BR>
<B>DID Log in agenzia -</B> Questa opzione registrerà l'ingresso DID chiamate insieme ad un in-group e l'ID utente, se del caso, in una tabella separata. Il valore predefinito è N.

<BR>
<A NAME="settings-alt_log_server_ip">
<BR>
<B>Alt-Log DB Server -</B> Questo è il server di database alternativo log. Questo è opzionale, e permette di alcuni registri di essere scritta in un database separato. Il valore predefinito è vuoto.

<BR>
<A NAME="settings-tables_use_alt_log_db">
<BR>
<B>Alt-Log tabelle -</B> Queste sono le tabelle che sono disponibili per la registrazione sul server di database alternativo log. Il valore predefinito è vuoto.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>IP di default del server esterno -</B> Se impostato a 1, questa opzione farà tutti i nuovi telefoni sono creati Usa IP esterno Server impostato su Y. Il valore predefinito è 0.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>URL webphone -</B> Questo è l'URL del webphone che verrà utilizzato con questo sistema se è abilitato nel record telefoni che un agente sta utilizzando. Il valore predefinito è vuoto.

<BR>
<A NAME="settings-enable_queuemetrics_logging">
<BR>
<B>Enable QueueMetrics Logging -</B> Questa impostazione consente di definire se il sistema inserirà voci di registro nella tabella di database queue_log come attività di Asterisk code fa. QueueMetrics è un autonomo, programma di analisi statistica closed-source. È necessario avere QueueMetrics già installato e configurato prima di abilitare questa funzione. Il default è 0.

<BR>
<A NAME="settings-queuemetrics_server_ip">
<BR>
<B>IP del servidor de QueueMetrics -</B> este es el IP address de la base dedatos para su instalacio`n de QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_dbname">
<BR>
<B>Nombre de la base de datos de QueueMetrics -</B> este es el nombre de labase de datos para su base de datos de QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_login">
<BR>
<B>conexion de la base de datos de QueueMetrics -</B> este es el nombre delusuario usado para abrirse una sesio`n a su base de datos deQueueMetrics.

<BR>
<A NAME="settings-queuemetrics_pass">
<BR>
<B>Contraseña de la base de datos de QueueMetrics -</B> esta es lacontraseña usada para abrirse una sesio`n a su base de datos deQueueMetrics.

<BR>
<A NAME="settings-queuemetrics_url">
<BR>
<B>URL de QueueMetrics -</B> esta es la Direccion del URL o del Web siteusada para conseguir a su instalacio`n de QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_log_id">
<BR>
<B>QueueMetrics Log ID -</B> Questo è l ID del server che tutti i contact center tronchi di entrare nel database di QueueMetrics userà come un identificatore per ogni record.

<BR>
<A NAME="settings-queuemetrics_eq_prepend">
<BR>
<B>QueueMetrics EnterQueue Prepend -</B> Questo campo è utilizzato per consentire prepending di uno dei campi dati dell elenco di fronte al numero di telefono del cliente per rapporti QueueMetrics personalizzati. Il valore predefinito è NONE per non compilare nulla.

<BR>
<A NAME="settings-queuemetrics_loginout">
<BR>
<B>QueueMetrics Login-Out -</B> Questa opzione ha effetto come il sistema registrerà i login e logout di un agente nel queue_log. Il valore predefinito è STANDARD utilizzare standard di AGENTLOGIN AGENTLOGOFF, RICHIAMATA userà AGENTCALLBACKLOGIN e AGENTCALLBACKLOGOFF che QM analizzerà in modo diverso, NONE non registrerà alcun login e logout all interno queue_log.

<BR>
<A NAME="settings-queuemetrics_callstatus">
<BR>
<B>QueueMetrics CallStatus -</B> Questa opzione se impostato a 0 non metterà nella voce CALLSTATUS in queue_log quando un agente disposizioni una chiamata. Il valore predefinito è 1 per abilitato.

<BR>
<A NAME="settings-queuemetrics_addmember_enabled">
<BR>
<B>QueueMetrics Addmember Enabled -</B> Questa opzione, se impostato a 1 genererà ADDMEMBER2 e le voci REMOVEMEMBER in queue_log. Il default è 0 per disabili.

<BR>
<A NAME="settings-queuemetrics_dispo_pause">
<BR>
<B>QueueMetrics Dispo Pause Codice -</B> Questa opzione, se popolato, permette di definire se un codice di pausa dispo viene inserito in queue_log quando un agente è in stato di dispo. Il valore predefinito è vuota per disabili.

<BR>
<A NAME="settings-queuemetrics_pause_type">
<BR>
<B>QueueMetrics Pausa tipo di registrazione -</B> Se attivata, questa opzione registrerà il tipo di pausa nella tabella campo dati5 queue_log. È necessario assicurarsi di avere un campo dati5 o abilitazione di questa funzione si romperà la compatibilità QM. Il default è 0 per disabili.

<BR>
<A NAME="settings-queuemetrics_pe_phone_append">
<BR>
<B>Telefono Telefono QueueMetrics Ambiente Append -</B> Questa opzione, se attivata, aggiungere il login telefono dell'agente al record data4 nella tabella log coda se il telefono campo Campagna ambiente è popolato. Il default è 0 per disabili.

<BR>
<A NAME="settings-queuemetrics_record_hold">
<BR>
<B>QueueMetrics Tenere Registro chiamate -</B> Questa opzione, se attivata, registrerà quando un cliente viene messo in attesa e tolto in attesa della tabella record_tags QM più recente. Il default è 0 per disabili.

<BR>
<A NAME="settings-queuemetrics_socket">
<BR>
<B>QueueMetrics Socket Invia -</B> Questa opzione, se attivata, invierà i dati QM ad una pagina web che lo invierà attraverso una presa per il logging. L'opzione CONNECT_COMPLETE invierà CONNECT, COMPLETEAGENT e COMPLETECALLER eventi all'URL definito di seguito. Il valore predefinito è NONE per disabili.

<BR>
<A NAME="settings-queuemetrics_socket_url">
<BR>
<B>QueueMetrics Socket Invia URL -</B> Se invio socket è abilitato in precedenza, questo è l'URL che viene utilizzato per inviare i dati. Predefinito è vuoto per disabili.

<BR>
<A NAME="settings-enable_vtiger_integration">
<BR>
<B>Enable Vtiger Integration -</B> Questa impostazione consente di abilitare l integrazione Vtiger con il sistema. Collega attualmente Vtiger admin e di ricerca, nonché la replica utente sono le uniche funzioni di integrazione disponibili. Il default è 0.

<BR>
<A NAME="settings-vtiger_server_ip">
<BR>
<B>Vtiger DB IP Server - </B> Questo è lindirizzo IP della banca dati per la vostra installazione Vtiger.

<BR>
<A NAME="settings-vtiger_dbname">
<BR>
<B>Vtiger Nome Database - </B> Questo è il nome del database per il vostro database Vtiger.

<BR>
<A NAME="settings-vtiger_login">
<BR>
<B>Vtiger Database Login - </B> Questo è il nome utente utilizzato per accedere al vostro database Vtiger.

<BR>
<A NAME="settings-vtiger_pass">
<BR>
<B>Vtiger Database Password - </B> Questa è la password utilizzata per accedere al tuo database Vtiger.

<BR>
<A NAME="settings-vtiger_url">
<BR>
<B>Vtiger URL - </B> Questo è lURL o lindirizzo del sito web utilizzato per accedere al tuo Vtiger installazione.


<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUSES TABELLA</FONT></B><BR><BR>
<A NAME="system_statuses">
<BR>
<B>Attraverso l uso di stati del sistema, si può avere stati che esistono per tutte le campagne e in gruppi. Lo stato deve essere 1-6 caratteri, la descrizione deve essere 2-30 caratteri e selezionabile definisce se si presenta nel sistema come una disposizione agente. Il campo human_answered viene utilizzato per il calcolo della percentuale di goccia, o abbandonare rate. Impostazione human_answered Y userà questo stato quando il conteggio delle chiamate umani-risposta. L opzione Categoria consente di raggruppare più status in una categoria che può essere utilizzato per l analisi statistica. Ci sono anche 5 ulteriori impostazioni che definiscono il tipo di esito: vendita, DNC, contatto con il cliente, non è interessato, impraticabile, richiamata in programma.</B>



<BR><BR><BR><BR>

<B><FONT SIZE=3>SCREEN_LABELS TABELLA</FONT></B><BR><BR>
<A NAME="screen_labels">
<BR>
<B>Screen labels give you the option of setting different labels for the default agent screen fields on a per campagna basis.</B>

<A NAME="screen_labels-label_id">
<BR>
<B>Schermo Label ID -</B> Questo campo deve essere di almeno 2 caratteri di lunghezza e non più di 20 caratteri, senza spazi o caratteri speciali. Questo è l'ID che verrà utilizzato per identificare l'etichetta schermo tutto il sistema.

<BR>
<A NAME="screen_labels-label_name">
<BR>
<B>Nome schermata Etichetta -</B> Questo è il nome descrittivo della voce label schermo.

<BR>
<A NAME="screen_labels-user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo disco, questo permette la visualizzazione di questa amministrazione recoird limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo record.

<BR>
<A NAME="screen_labels-label_hide_field_logs">
<BR>
<B>Nascondi etichette in registri delle chiamate -</B> Se l'etichetta è impostato su NASCONDI --- --- poi i registri delle chiamate degli agenti, se abilitata sulla campagna, si mostrano ancora sul campo e dati a meno che questa opzione è impostata su Y. Il valore predefinito è N.

<BR>
<A NAME="screen_labels-default_field_labels">
<BR>
<B>Di campo predefiniti di etichette -</B> Questi 19 campi consentono di impostare il nome come apparirà nell interfaccia agente così come la pagina di piombo di modifica amministrativa. Il valore predefinito è vuoto, che utilizzerà i valori predefiniti hard-coded nell interfaccia agente. È inoltre possibile impostare una etichetta per --- NASCONDI --- per nascondere sia l etichetta e il campo.



<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUS_CATEGORIES TABELLA</FONT></B><BR><BR>
<A NAME="status_categories">
<BR>
<B>Con el uso de las categorías del estado de sistema, usted puedeagrupar juntos estados para tener en cuenta análisis estadístico enun grupo de estados. La identificacio`n de la categoría debe ser 2-20caracteres en longitud sin espacios, el nombre debe ser 2-50caracteres en longitud, la descripcio`n es opcional y la exhibicio`nde TimeonVDAD define si ese estado será uno de hasta que 4 estadosque se puedan calcular y exhibir en el tiempo en informe del tiemporeal de VDAD.</B> La vendita e Dead Categoria Categoria di piombo sono entrambi utilizzati dal sistema quando Elenco Suggerimento analisi statistiche elenco.



<BR><BR><BR><BR>
<?php
if ($SSallow_emails>0)
	{
?>
<B><FONT SIZE=3>EMAIL ACCOUNTS</FONT></B><BR><BR>
<A NAME="email_accounts">
<BR>
<B>La sezione Gestione Account di posta elettronica consente di creare, copiare e cancellare le impostazioni dell account e-mail che vi permetterà di avere messaggi di posta elettronica arrivano nel vostro sistema ed essere trattati come se fossero telefonate agli agenti. Account di posta elettronica deve essere impostato dall Utente e da un provider di posta elettronica - che non è coperto da questo modulo.</B>

<BR>
<A NAME="email_accounts-email_account_id">
<BR>
<B>Email ID account -</B> Questo è il nome breve dell'account di posta elettronica, non è modificabile dopo la presentazione iniziale, non deve contenere spazi e deve essere compresa tra 2 e 20 caratteri di lunghezza.

<BR>
<A NAME="email_accounts-email_account_name">
<BR>
<B>Email Nome account -</B> Questo è il nome completo dell'account di posta elettronica, deve essere compresa tra 2 e 30 caratteri di lunghezza. 

<BR>
<A NAME="email_accounts-email_account_active">
<BR>
<B>Attivo -</B> Questo determina se questo account sarà controllata per i nuovi messaggi e-mail per essere caricato nel combinatore. 

<BR>
<A NAME="email_accounts-email_account_description">
<BR>
<B>Email Account Descrizione -</B> This allows for a lengthy description, if needed, of the email account.  255 characters max. 

<BR>
<A NAME="email_accounts-email_account_type">
<BR>
<B>Email Tipo di account -</B> Specifies whether the account is used for inbound or outbound email messages.  Should be set to INBOUND. 

<BR>
<A NAME="email_accounts-admin_user_group">
<BR>
<B>Admin Gruppo Utente -</B> Questo è il gruppo utente amministrativo per questo gruppo in entrata, questo permette la visualizzazione di questa amministrazione in-gruppo limitato dal gruppo di utenti. Il default è - ALL - che permette a qualsiasi utente amministratore per visualizzare questo gruppo.  

<BR>
<A NAME="email_accounts-protocol">
<BR>
<B>Email Account protocollo -</B> This is the email protocol used by the account you are setting up access to.  Currently only IMAP and POP3 accounts are supported.  

<BR>
<A NAME="email_accounts-email_replyto_address">
<BR>
<B>Email Indirizzo di risposta -</B> The email address of the account you are setting up access to.  Replies to email messages from the agent interface will read as coming from this address.  

<BR>
<A NAME="email_accounts-email_account_server">
<BR>
<B>Email Account Server -</B> Il server di posta elettronica che l'account è ospitato su.  

<BR>
<A NAME="email_accounts-email_account_user">
<BR>
<B>Email Account Utente -</B> The login used to access this account.  Usually it's the portion of the reply-to address before the -at- symbol.  

<BR>
<A NAME="email_accounts-email_account_pass">
<BR>
<B>Email Password account -</B> The password used to access this account.  This is usually set at the time the email account is created.  

<BR>
<A NAME="email_accounts-email_frequency_check_mins">
<BR>
<B>Email Frequency Check Rate (mins) -</B> How often this email account should be checked.  The highest rate of frequency at the moment is five minutes; some email providers will not allow more than three login attempts in fifteen minutes before locking the account for an indeterminate amount of time.  

<BR>
<A NAME="email_accounts-in_group">
<BR>
<B>ID In-Group -</B> L'In-Group che i messaggi di posta elettronica verranno inviati al.   

<BR>
<A NAME="email_accounts-default_list_id">
<BR>
<B>Predefinito ID List -</B> L'ID lista che porta sarà inserito se necessario.  

<BR>
<A NAME="email_accounts-call_handle_method">
<BR>
<B>In-chiamata di gruppo maniglia Metodo -</B> Questa è l azione che verrà presa quando una nuova email si trova nel conto. EMAIL: tutti i messaggi di posta elettronica vengono inseriti nella tabella lista come nuovo lead. EMAILLOOKUP cercherà l intera tabella lista per l indirizzo email nella colonna email - se si trova il piombo, che ID elenco di piombo sarà utilizzato nel record che va nella tabella email_list. EMAILLOOKUPRC fa lo stesso, ma sarà solo liste appartenenti alla campagna selezionata nella casella ID campagna in gruppo sotto la ricerca. EMAILLOOKUPRL solo cercare un particolare elenco, che è quello immesso nella casella di In-ID Lista Gruppo qui sotto.  

<BR>
<A NAME="email_accounts-agent_search_method">
<BR>
<B>Agente in-Gruppo Ricerca Metodo -</B> Il metodo di ricerca agente per essere utilizzato dal gruppo in entrata, LO è a carico bilanciato-Overflow e cercherà di inviare la chiamata di un agente sul server locale prima di provare a inviarlo a un agente su un altro server, LB è a carico bilanciato e cercherà di inviare la chiamata all'agente successivo non importa quale server sono connessi, così è solo server e sarà solo provare a inviare le chiamate agli agenti sul server che la chiamata è venuto in su. Il default è LB. <B>IS THIS NECESSARY?</B>  

<BR>
<A NAME="email_accounts-ingroup_list_id">
<BR>
<B>ID In-Group List -</B> Questa è l'ID elenco che verrà utilizzato per la ricerca di una corrispondenza all'interno.  

<BR>
<A NAME="email_accounts-ingroup_campaign_id">
<BR>
<B>ID In-Group campagna -</B> Questa è l'ID campagna che verrà utilizzato per la ricerca di una corrispondenza all'interno.  




<BR><BR><BR><BR>
<?php } ?>
<B><FONT SIZE=3>CUSTOM TEMPLATE MAKER</FONT></B><BR><BR>
<A NAME="template_maker">
<BR>
Il creatore modello personalizzato consente di definire il proprio layout di file per l utilizzo con la lista caricatore e anche cancellarli, se necessario. Se avete spesso caricare file che si trovano in un layout coerente diverso dal layout standard, potete trovare questo utile strumento. Il layout salvato funziona su qualsiasi file caricato corrisponde, indipendentemente dal tipo di file o un delimitatore.

<BR>
<A NAME="template_maker-create_template">
<BR>
<B>Creare un nuovo modello - </B>In order to begin creating your new listloader template, you must first load a lead file that has the layout you wish to create the template for.  Click "Choose file", and open the file on your computer you wish to use.  This will upload a copy to your server and process it to determine the file type and delimiter (for TXT files).

<BR>
<A NAME="template_maker-delete_template">
<BR>
<B>Eliminaretemplate -</B> If you have a template you no longer use or you mis-entered information on it and would like to re-enter it, select the template from the drop-down menu and click "Elimina modello".

<BR>
<A NAME="template_maker-template_id">
<BR>
<B>Template ID -</B> This field is where you enter an arbitrary ID for your new custom template.  It must be between 2 and 20 characters and consist of alphanumeric characters and underscores.

<BR>
<A NAME="template_maker-template_name">
<BR>
<B>Nome modello -</B> This field is where you enter the name for your new custom template.  Can be up to 30 characters long.

<BR>
<A NAME="template_maker-template_description">
<BR>
<B>Template Description -</B> This field is where you enter the description for your new custom template.  It can be up to 255 characters long.

<BR>
<A NAME="template_maker-list_id">
<BR>
<B>ID Lista -</B> All templates must load their records into a list.  Select a list ID to load leads into from this drop-down list, which will display any lists available to you given your user settings.

<BR>
<A NAME="template_maker-assign_columns">
<BR>
<B>Assigning columns -</B> Once you have loaded a sample lead file matching the layout you want to make into a template and select a list ID to load leads into, all of the available columns from the list table and the custom table for the list you selected (if any) will be displayed here.  Colonnas highlighted in blue are standard columns from the list table.  Colonnas highlighted in pink belong to the custom table for the selected list.  Each column listed has a drop-down menu, which should be populated with the fields from the first row of the sample lead file you uploaded.  Assign the appropriate fields to the appropriate columns and press Carica modello to create your template.  You do not need to assign every field to a column, and you do not need to assign every column a field.  For details on the standard list columns, click <a href="#list_loader">HERE</a>.


<BR><BR><BR><BR>

<A NAME="max_stats">
<B><FONT SIZE=3>Massimo sistema Statistiche Rapporti</FONT></B><BR><BR>
<BR>
<B>Queste statistiche sono memorizzate nella cache totali che vengono memorizzati nel corso di ogni giorno in tempo reale attraverso processi back-end. Per le chiamate in entrata, le chiamate totali per in-gruppo sono calcolati per ciascuna chiamata che entra nel processo che calcola. Per i conti tutto il sistema, i totali sono generati da voci di registro così come altre totali in gruppo e campagna. I totali potrebbero non corrispondere a causa delle impostazioni che avete nel vostro sistema, nonché quando la chiamata viene riagganciato.</B>


<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>QC STATUS CODES</FONT></B><BR><BR>
	<A NAME="qc_status_codes">
	<BR>
	<B>Il controllo di qualità - Funzione di controllo di qualità ha una propria serie di codici di stato separati da quelli all interno della gestione delle chiamate funzioni del sistema. Codici di stato QC deve essere compresa tra 2 e 8 caratteri e contenere caratteri speciali, come uno spazio o due punti. La descrizione del codice di stato QC deve essere compresa tra 2 e 30 caratteri di lunghezza. Per queste funzioni, è necessario avere QC abilitata nelle impostazioni di sistema.</B>
	<?php
	}
?>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Rapporti</FONT></B><BR><BR>

<A NAME="agent_time_detail">
<BR>
<B>Agente Time Dettagliata -</B> In this report you can view how much time agents spent on what.<BR>
<U>TIME CLOCK</U> = Time the agent been logged in to the time clock.<BR>
<U>AGENT TIME</U> = Totaletime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
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
<B>Agent Status Dettagliata -</B> In this report you can view what and how many statuses has been selected by the agents.<BR>
<U>CALLS</U> = Totalenumber of calls sent to the user.<BR>
<U>CIcalls</U> = Totalenumber of call where there was a Umani Risposta which is set under "Admin" -> "System Esiti".<BR>
<U>DNC/CI%</U> = How much in percent DNC (Do Not Call) per Umani Rispostas.<BR>
And the rest is just Esiti di Defaultthat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Esiti".<BR>

<A NAME="agent_performance_detail">
<BR>
<B>Agent Performance Dettagliata -</B> This is a combination of Agente Time Dettagliata and Agent Status Dettagliata.<BR>
(Statistics related to handling of calls only)<BR>
<U>CALLS</U> = Totalenumber of calls sent to the user.<BR>
<U>TIME</U> = Totaletime of these (<U>PAUSE</U> + <U>WAIT</U> + <U>TALK</U> + <U>DISPO</U>).<BR>
<U>PAUSE</U> = Amount of time being paused in related to call handling.<BR>
<U>AVG</U> means Average so everything -AVG is for example amount of PAUSE-time divided by total number of calls: (<U>PAUSE</U> / <U>CALLS</U> = <U>PAUSAVG</U>)<BR>
<U>WAIT</U> = Time the agent waits for a call.<BR>
<U>TALK</U> = Time the agent talks to a customer or is in dead state (<U>DEAD</U> + <U>CUSTOMER</U>).<BR>
<U>DISPO</U> = Time the agent uses at the disposition screen (where the agent picks NI, SALE etc).<BR>
<U>DEAD</U> = Time the agent is in a call where the customer has hung up.<BR>
<U>CUSTOMER</U> = Time the agent is in a live call with a customer.<BR>
And the rest is just Esiti di Defaultthat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Esiti".<BR>
- Next table is Codici Pausa.<BR>
<U>TOTALE</U> = Totaletime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
<U>NONPAUSE</U> = Everything except pause (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U>).<BR>
<U>PAUSE</U> = Only Pause.<BR>
- The last table is pause codes and their time (like "Agente Time Dettagliata").<BR>
<U>LOGIN</U> = The pause code when going from login directly to pause.<BR>
<U>LAGGED</U> = The time the agent had some network problem or similar.<BR>
<U>ANDIAL</U> = This pause code triggers if the agent been on dispo screen for longer than 1000 seconds.<BR>
and empty is undefined pause code. <BR>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Nanpa cellphone filtering</FONT></B><BR><BR>


<A NAME="nanpa-running">
<BR>
<B>Currently running NANPA scrubs -</B> Visualizzares a log of the currently running scrubs, including: Start Time, Leads Count, Filter Count, Status Line, Time to Complete, Field Updated, and Field excluded
<BR><BR>
<A NAME="nanpa-settings">
<BR>
<B>Inactive Liste -</B> Contains all the lista inattive, that are eligible to be scrubbed.  A list can only be scrubbed if Attivois set to N on the list.
<BR><BR>
<B>Field to Update -</B> Indicates which lead field will contain the scrub result -Cellphone, Landline, or Invalid-.  If NONE is selected it will use the default field Stato_Code.
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
FINE
</TD></TR></TABLE></BODY></HTML>
<?php
exit;

#### END HELP SCREENS
