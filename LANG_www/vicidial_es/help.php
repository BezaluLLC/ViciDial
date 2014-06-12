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
echo "<TABLE WIDTH=98% BGCOLOR=#E6E6E6 cellpadding=2 cellspacing=0><TR><TD ALIGN=LEFT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=4><B>ADMINISTRATION: AYUDA<BR></B></FONT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2><BR><BR>\n";

?>
<B><FONT SIZE=3>USUARIOS tabular</FONT></B><BR><BR>
<A NAME="users-user">
<BR>
<B>Usuario ID -</B> Este campo es donde se pone el número de usuarios de identificación, puede ser de hasta 8 dígitos de longitud, debe tener al menos 2 caracteres de longitud.

<BR>
<A NAME="users-pass">
<BR>
<B>Password -</B> Este campo es donde usted pone la contraseña a los usuarios. Debe tener al menos 2 caracteres de longitud. La contraseña de usuario segura debe tener al menos 8 caracteres de longitud y tienen minúsculas y mayúsculas, así como al menos un número.

<BR>
<A NAME="users-force_change_password">
<BR>
<B>Forzar Cambio de Contraseña -</B> Si esta opción está establecida en Y entonces el usuario se le pedirá que cambie su contraseña la próxima vez que inicie sesión en la página web de administración. El valor predeterminado es N.

<BR>
<A NAME="users-last_login_date">
<BR>
<B>Última Entrar Info -</B> Esto muestra la última fecha y hora de inicio de sesión intento, y si se ha producido un reciente intento fallido de login. Si esto se presenta modificar formulario de usuario, el contador de inicio de sesión fallido intento se restablecerá y el agente puede intentar inmediatamente volver a iniciar sesión. Si un agente tiene 10 intentos fallidos de acceso en una fila, entonces no puede intentar iniciar sesión de nuevo durante al menos 15 minutos a menos que su cuenta se pone a cero de forma manual.

<BR>
<A NAME="users-full_name">
<BR>
<B>Nombre Completo -</B> Este campo es donde se pone el nombre completo a los usuarios. Debe tener al menos 2 caracteres de longitud.

<BR>
<A NAME="users-user_level">
<BR>
<B>Nivel Del Usuario -</B> Este menú es donde se selecciona el nivel de usuario los usuarios. Debe haber un nivel de 1 para acceder a la pantalla del agente, debe estar a nivel superior a 2 para acceder al sistema como más cerca, debe estar a nivel de usuario 8 o superior para entrar en la sección web de administración.

<BR>
<A NAME="users-user_group">
<BR>
<B>Grupo Del Usuario -</B> Este menú es donde se selecciona el grupo de usuarios que este usuario pertenecerá. Hay varias características de la pantalla del agente que se pueden controlar a través de la configuración de grupos de usuarios. Si este campo se deja en blanco a continuación, el usuario no puede iniciar sesión en la pantalla del agente.

<BR>
<A NAME="users-phone_login">
<BR>
<B>Acceso Teléfono -</B> Aquí es donde se puede establecer un valor de teléfono de acceso por defecto para cuando el usuario inicia sesión en la pantalla del agente. Este valor se rellenará el phone_login automáticamente cuando el usuario inicia sesión con su campaña de usuario se pasa en la pantalla de inicio de sesión del agente.

<BR>
<A NAME="users-phone_pass">
<BR>
<B>Contraseña Teléfono -</B> Aquí es donde se puede establecer el valor de aprobación de teléfono predeterminado para cuando el usuario inicia sesión en la pantalla del agente. Este valor se rellenará el phone_pass automáticamente cuando el usuario inicia sesión con su campaña de usuario se pasa en la pantalla de inicio de sesión del agente.

<BR>
<A NAME="users-active">
<BR>
<B>Activo -</B> Este campo define si el usuario está activo en el sistema y se puede iniciar la sesión como un agente o manager. El valor predeterminado es Y.

<BR>
<A NAME="users-voicemail_id">
<BR>
<B>ID Correo de Voz -</B> Este es el buzón de voz al que las llamadas serán dirigidas en un grupo de entrada AGENTDIRECT en el momento de caída, si el grupo de entrada tiene en el método de caída establecido en VOICEMAIL y el campo de correo de voz está configurado como AGENTVMAIL.

<BR>
<A NAME="users-optional">
<BR>
<B>Correo Electrónico, Código de Usuario y Territorio -</B> Estos son campos opcionales.

<BR>
<A NAME="users-hotkeys_active">
<BR>
<B>Teclas Rápidas Activas -</B> Si la opción se establece a 1 permite que el usuario utilice la función de disposición rápida de atajos de teclado the agent screen.

<BR>
<A NAME="users-agent_choose_ingroups">
<BR>
<B>Agente elige Grupos de entrada -</B> Si se establece esta opción a 1 permite que el usuario elija los grupos de entrada de los cuales recibirán llamadas cuando ellos accedan a una campaña CLOSER ó DE ENTRADA. De otro modo, se requiere del supervisór que defina esto en su pantalla del detalle de usuario de la página de admin.

<BR>
<A NAME="users-agent_choose_blended">
<BR>
<B>Agente Elija Mezclado -</B> Esta opción, si se establece en 1 permite al usuario elegir si el agente tiene o no, su configuración de campaña como mezclado, y si no entonces el valor predeterminado de mezcla se utilizará. El valor predeterminado es 1 para habilitado.

<BR>
<A NAME="users-agent_choose_territories">
<BR>
<B>Agente Elige territorios -</B> Esta opción si se establece en 1 permite al usuario elegir los territorios que van a recibir llamadas de los usuarios cuando ingresan a una campaña  MANUAL o INBOUND_MAN. De lo contrario el usuario será configurado para utilizar todos los territorios a los que ha establecido que pertenece en la sección administrativa de Territorios de Usuario.

<BR>
<A NAME="users-scheduled_callbacks">
<BR>
<B>Scheduled Callbacks -</B> Esta opción permite que un agente disponga de una llamada como CALLBK y elija la fecha y hora en la cual el prospecto se vuelve a activar.

<BR>
<A NAME="users-agentonly_callbacks">
<BR>
<B>Remarcación de Único Agente -</B> Esta opción permite que un agente active una remarcación de tal forma que unicamente el agente puede realizar la remarcación. Esto también permite que el agente vea sus listados de remarcaciones y ejecute la marcación en el momento que desee.

<BR>
<A NAME="users-agentcall_manual">
<BR>
<B>Marcación Manual de Agente -</B> Esta opción permite que un agente para introducir manualmente una nueva pista en el sistema y llamarlos. Esto también permite que la convocatoria de cualquier número de teléfono desde la pantalla de su agente y puts que ponen en su sesión. Utilice esta opción con precaución.

<BR>
<A NAME="users-agentcall_email">
<BR>
<B>Agent Call Email -</B> This option is disabled.

<BR>
<A NAME="users-agent_recording">
<BR>
<B>Grabación Agente -</B> Esta opción puede evitar que un agente de hacer cualquier grabación después de haberse iniciado en la pantalla del agente. Esta opción debe estar encendido para que la pantalla del agente para seguir la configuración de grabación de campaña.

<BR>
<A NAME="users-agent_transfers">
<BR>
<B>Transferencias Agente -</B> Esta opción puede evitar que un agente de la apertura de la transferencia - sesión de la conferencia, en la pantalla del agente. Si está desactivada, el agente puede no llamadas de terceros o de transferencia ciega ninguna llamada.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-closer_default_blended">
	<BR>
	<B>Un defecto más cercano mezclado -</B> Esta opción simplemente activa por defecto la caja de verificación Mezclado en una pantalla de acceso .
	<?php
	}
?>

<BR>
<A NAME="users-agent_recording_override">
<BR>
<B>Grabación Agente Anulación -</B> Esta opción anulará cualquiera que sea la opción es en la campaña para la grabación. DISABLED no anulará el ajuste de grabación campaña. NUNCA será desactivar la grabación en el cliente. ONDEMAND es el valor predeterminado y permite que el agente para iniciar y detener la grabación, según sea necesario. ALLCALLS comenzará a grabar en el cliente cada vez que se envía una llamada a un agente. ALLFORCE empezará a grabar en el cliente cada vez que se envía una llamada a un agente sin dar ninguna opción para detener la grabación del agente. Para ALLCALLS y ALLFORCE hay una opción para usar el retardo de grabación para reducir en grabaciones muy cortas y reducir la carga del sistema.

<BR>
<A NAME="users-agent_shift_enforcement_override">
<BR>
<B>Invalidar Forzar Turno de Agente -</B> Esta configuración anula cualquier configuracion establecida para el grupo de usuarios del usuario con relación a Fozado de Turno. DISABLED permitirá el uso de la configuracion de grupo de usuarios. OFF no forzará turnos de ninguna manera. START sólo forzará la hora de registro pero no afectará un agente que esté ejecutando su turno si ya se encuentra registrado. ALL forzará la hora de inicio de turno y expulsará despues de sobrepasar el tiempo del turno. El valor por defecto es DISABLED.

<BR>
<A NAME="users-agent_call_log_view_override">
<BR>
<B>Invalidar Ver Registro de Marcaciones de Agente -</B> Este configuración invalidará cualquier otra opcion definida para la Ver el Registro de MArcaciones por Agente. DISABLED utilizará la configuración de grupo de usuarios. N no permitirá que muestre el registro de marcación de usuarios. Y permitirá mostrar el registro de llamadas del usuario. El valor predeterminado es DISABLED.

<BR>
<A NAME="users-agent_lead_search_override">
<BR>
<B>Agente Invalidar Búsqueda de Contacto -</B> Este valor invalidará cualquier configuración de campaña relacionada con la búsqueda de Contactos de Agente. NOT_ACTIVE utilizará el ajuste de la campaña. ENABLED permitirá la búsqueda de prospectos y DISABLED no permitirá la búsqueda de prospectos. El valor predeterminado es NOT_ACTIVE. LIVE_CALL_INBOUND permitirá busqueda de contactos mientras mientras se está ejecutando unicamente una llamada entrante. LIVE_CALL_INBOUND_AND_MANUAL permitirá busqueda de contactos en una llamada entrante o mientras está en pausa. Cuando la busqueda de contacto se realiza durante una llamada entrante activa, el contacto de la llamada cuando abandona al agente cambiará al estado de LSMERG, y los registros de la llamada serán modificados para enlazar en su lugar al contacto elegido por el agente.

<BR>
<A NAME="users-alert_enabled">
<BR>
<B>Alerta Activada -</B> Este campo muestra si el agente tiene alertas del navegador web habilitado para cuando entra una llamada en su sesión de screen agente. Por defecto es 0 para No.

<BR>
<A NAME="users-allow_alerts">
<BR>
<B>Permitir Alertas -</B> Este campo le da la capacidad para permitir que las alertas del navegador agente esté habilitado por el agente para cuando entra una llamada en su sesión de screen agente. Por defecto es 0 para No.

<BR>
<A NAME="users-preset_contact_search">
<BR>
<B>Preestablecido Búsqueda de Contactos -</B> Si el usuario está conectado a una campaña que establecido como CONTACTS la caracteristica de Preconfiguración de Transferencia, entonces esta opción puede deshabilitar la búsqueda de contactos unicamente a este usuario. El valor predeterminado es NOT_ACTIVE el cuál utilizará cualquier configuración que la campaña tenga.

<BR>
<A NAME="users-max_inbound_calls">
<BR>
<B>Las llamadas entrantes Max -</B> Si este parámetro se establece en un número mayor que 0, entonces será el número máximo de llamadas entrantes que un agente puede manejar todos los grupos entrantes en un día. Si el agente alcanza el número máximo de llamadas entrantes, entonces no van a ser capaces de seleccionar los grupos entrantes para atender las llamadas de hasta el día siguiente. Esta configuración anula la configuración de la campaña del mismo nombre. Por defecto es 0 para discapacitados.

<BR>
<A NAME="users-campaign_ranks">
<BR>
<B>Calificaciones de campaña -</B> Define la calificación que un agente tendrá en cada campaña. Estas calificaciones pueden ser consideradas para permitir el enutamiento preferido para las llamadas siguientes cuando se establece el valor como campaign_rank. También en esta sección están las WEB VARs para cada campaña. Estas permiten a cada agente tener una cadena de variable distinta que puede ser añadida al formulario WEB, pestaña de Script, URLs o simplemente poniendo --A-- web_vars--B-- como se usaría en cualquier otro campo. Otro campo en esta sección es de GRADE, lo que permite al campo campaign_grade_random  que utilice la configuración Siguiente Marcación de Agente. Esta configuración de categoría aumentará o disminuirá la probabilidad de que el agente recibirá la llamada.

<BR>
<A NAME="users-closer_campaigns">
<BR>
<B>Grupos de entrada -</B> Seleccionar los grupos de entrada de los cuales usted desea recibir llamadas, si ha seleccionado la campaña CLOSER. También podrá fijar el nivel de destreza, en esta sección para cada uno de los grupos de entrada así como poder ver elnúmero de las llamadas recibidas de cada grupo de entrada para este agente específico. También en esta sección se establece la capacidad de dar al agente una calificación para cada grupo de entrada. Estas calificaciones pueden ser utilizadas para el enrutamiento de llamadas preferido cunado esta opción se selecciona en el tablero de Grupo-Entrada. También en esta sección están las WEB VARs para cada campaña. Estas permiten a cada agente tener una cadena de variable distinta que puede ser añadida al formulario WEB, pestaña de Script, URLs o simplemente poniendo --A-- web_vars--B-- como se usaría en cualquier otro campo.

<BR>
<A NAME="users-alter_custdata_override">
<BR>
<B>Invalidar Agente Modifica Datos de Cliente-</B> Esta opción invalidará cualquier opción que esté en la campaña para alterar los datos del cliente. NOT_ACTIVE utilizará lo los ajustes presentes para la campaña. ALLOW_ALTER permitirá siempre al agente modificar los datos del cliente, no importa qué ajuste tenga la campaña. El valor por defecto es NOT_ACTIVE.

<BR>
<A NAME="users-alter_custphone_override">
<BR>
<B>Agente Inhabilitar Teléfono Alterno de Cliente -</B> Esta opción inhabilitará cualquier configuración en la campaña relacionada con la modificación del numero telefónico del cliente. NOT_ACTIVE utilizará cualquier ajuste es para la presente campaña. ALLOW_ALTER siempre permitirá que el agente modifique el número de teléfono del cliente, no importa la configuración de la campaña. El valor predeterminado es NOT_ACTIVE.

<BR>
<A NAME="users-custom_one">
<BR>
<B>Campos de Usuario Personalizados -</B> Estos cinco campos se puede utilizar para diversos fines, y pueden ser rellenados en direcciones del formulario web, secuencias de comandos como user_custom_one, y así sucesivamente. El campo personalizado 5 puede ser utilizado como un campo para definir un número de plan de marcación para enviar una llamada a partir de un AGENTDIRECT de grupo de entrada, si el usuario no está disponible, simplemente necesita poner AGENTEXT en los campos MESSAGE o EXTENSION, en el grupo de entrada AGENTDIRECT y el sistema buscará el campo 5 personalizado y enviará la llamada a ese número del plan de marcación.

<BR>
<A NAME="users-alter_agent_interface_options">
<BR>
<B>Opciones interfaz de Agente Alternativas -</B> Si se establece a 1 esta opción permite que el usuario administrativo modifique las opciones de interfaz de los agentes en admin.php.

<BR>
<A NAME="users-delete_users">
<BR>
<B>Borrar Usuarios -</B> Esta opción si se establece como 1 permite que el usuario suprima a otros usuarios del igual o nivel inferior del usuario del sistema.

<BR>
<A NAME="users-delete_user_groups">
<BR>
<B>Borrar Grupos de Usuario -</B> Esta opción si se establece como 1 permite que el usuario suprima grupos de usuario del sistema.

<BR>
<A NAME="users-delete_lists">
<BR>
<B>Suorimir Listas -</B> Esta opción, si el valor 1 permite al usuario borrar las listas del sistema.

<BR>
<A NAME="users-delete_campaigns">
<BR>
<B>Suprimir Campañas -</B> Esta opción, si el valor 1 permite al usuario borrar las campañas del sistema.

<BR>
<A NAME="users-delete_ingroups">
<BR>
<B>Suprimir Grupos-Entrada -</B> Esta opción, si el valor 1 permite al usuario borrar Grupos de entrada del sistema.

<BR>
<A NAME="users-modify_custom_dialplans">
<BR>
<B>Modificar DialPlans personalizados -</B> Esta opción, si el valor 1 permite al usuario ver y modificar las entradas personalizadas dialplan que están disponibles en el menú de llamada, ajustes del sistema y servidores pantallas de modificación.

<BR>
<A NAME="users-delete_remote_agents">
<BR>
<B>Suprimir agentes remotos -</B> Esta opción, si el valor 1 permite al usuario eliminar agentes remotos del sistema.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-load_leads">
	<BR>
	<B>Cargar Contactos -</B> Esta opción, si el valor 1 permite al usuario cargar listas de plomo en la tabla de la lista por medio del cargador de plomo basado en la web.
	<?php
	}
if ($SScustom_fields_enabled > 0)
	{
	?>
	<BR>
	<A NAME="users-custom_fields_modify">
	<BR>
	<B>Modificar campos personalizados -</B> Si esta opciónse establece en 1 permite al usuario modificar los campos personalizados listados.
	<?php
	}
?>

<BR>
<A NAME="users-campaign_detail">
<BR>
<B>Detalles de Campaña -</B> Si se establece esta opción como 1 permite que el usuario vea y modifique los elementos en pantalla de detalles de la campaña.

<BR>
<A NAME="users-ast_admin_access">
<BR>
<B>Acceso de AGC Admin -</B> Si se establece esta opción como 1 permite a usuario ingresar a las páginas astGUIclient de admin.

<BR>
<A NAME="users-ast_delete_phones">
<BR>
<B>Borrar teléfonos AGC -</B> Si se establece esta opción como 1 permite que el usuario suprima entradas del teléfono en las páginas astGUIclient de admin.

<BR>
<A NAME="users-delete_scripts">
<BR>
<B>Borrar Scripts -</B> Si se establece esta opción como 1 permite que el usuario suprima Scripts de la campaña en la pantalla de la modificación de scripts.

<BR>
<A NAME="users-modify_leads">
<BR>
<B>Modificar Contactos -</B> Si esta opción se establece en 1 permite que el usuario modifique los contactos en la página de resultados de búsqueda de la sección admin.

<?php
if ($SSallow_emails>0)
	{
?>
<BR>
<A NAME="users-modify_email_accounts">
<BR>
<B>Modificar Cuentas de Correo Electrónico -</B> Esta opción establecida como 1 permitirá al usuario modificar cuentas de correo electrónico en la página de gestión de cuentas de correo electrónico.
<?php
	}
?>

<BR>
<A NAME="users-change_agent_campaign">
<BR>
<B>Cambio de campaña del agente -</B> si se establece esta opción en 1 permite que el usuario altere la campaña que un agente está registrado en mientras que se encuentre registrado en ella.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-delete_filters">
	<BR>
	<B>Borrar Filtros -</B> Esta opción permite que el usuario sea capaz de eliminar los filtros de plomo del sistema.
	<?php
	}
?>

<BR>
<A NAME="users-delete_call_times">
<BR>
<B>BorrarHorarios de Marcación -</B> Esta opción permite que el usuario sea capaz de eliminar los tiempos de llamada y los registros estatales veces llamadas los registros del sistema.

<BR>
<A NAME="users-modify_call_times">
<BR>
<B>Modificar Horarios d Marcación -</B> esta opción permite que el usuario consulte y modifique los los registros de momentos de marcación y estados de marcación. Un usuario no necesita esta opción activa si solo necesita modificar la opción de horarios de marcación en el tablero de campañas.

<BR>
<A NAME="users-modify_sections">
<BR>
<B>Modificar Secciones -</B> Estas opciones permiten que el usuario vea y modifique cada uno los registros de sección. Si es establecido a 0, el usuario puede ver la lista de sección, pero no el detalle o la pantalla de modificación de un registro en esa sección.

<BR>
<A NAME="users-view_reports">
<BR>
<B>View Informes -</B> Esta opción permite al usuario ver los informes web del sistema.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="users-qc_enabled">
	<BR>
	<B>CC Activado - </B> Esta opción permite al usuario acceder a la pantalla de control de calidad de agente.

	<BR>
	<A NAME="users-qc_user_level">
	<BR>
	<B>Nivel de Usuario de CC - </B> Este ajuste define lo que es el nivel de usuario de agente de control de calidad. Este dictará el nivel de funcionalidad para el agente en la sección de CC:<BR>
	1 - Modificar Nada<BR>
	2 - Modificar Nada Salvo Estado<BR>
	3 - Modificar Todos los Campos<BR>
	4 - Verificar primera ronda de CC<BR>
	5 - Ver Estadísticas de CC<BR>
	6 - Capacidad para modificar Registros Terminados<BR>
	7 - Nivel de Manager<BR>

	<BR>
	<A NAME="users-qc_pass">
	<BR>
	<B>CC Aprobar Registro - </B> Esta opción permite al agente especificar que un registro ha pasado la primera ronda de control de calidad después de ser examinado.

	<BR>
	<A NAME="users-qc_finish">
	<BR>
	<B>CC Finalizar Registro - </B> Esta opción permite al agente especificar que un registro ha terminado la segunda ronda de control de calidad después de revisar el registro aprobado.

	<BR>
	<A NAME="users-qc_commit">
	<BR>
	<B>Registro de control de calidad Compro - </B> Esta opción permite al agente para especificar que un registro se ha cometido en el control de la calidad. Ya no puede ser modificado por cualquiera.
	<?php
	}
?>

<BR>
<A NAME="users-add_timeclock_log">
<BR>
<B>Añadir Registro Grabación a Reloj Asistencia - </B> Esta opción permite al usuario añadir registros de grabación al reloj de asistencia.

<BR>
<A NAME="users-modify_timeclock_log">
<BR>
<B>Modificar Registro GRabación en Reloj Asistencia - </B> Esta opción permite al usuario modificar los registros de grabación en el Reloj de Asistencia.

<BR>
<A NAME="users-delete_timeclock_log">
<BR>
<B>Eliminar Registros de Grabación Reloj Asistencia - </B> Esta opción permite al usuario borrar los registros re grabación en el Reloj de Asistencia.

<BR>
<A NAME="users-vdc_agent_api_access">
<BR>
<B>Agent API Access -</B> Esta opción permite que la cuenta que se utilizará con el agente y comandos de la API no agentes.

<BR>
<A NAME="users-manager_shift_enforcement_override">
<BR>
<B>Invalidar Fozar Turno de Manager -</B> Este ajuste, si se pone a 1 permitirá que un administrador de entrar en su usuario y contraseña en una pantalla de agente para anular las restricciones sobre el turno de una sesión de agente si el agente está intentando iniciar sesión fuera de su turno. El valor por defecto es 0.

<BR>
<A NAME="users-download_lists">
<BR>
<B>Download Listas -</B> Este ajuste, si se establece en 1 permitirá que un usuario manager hacer click en el enlace de lista de descargas, en la parte inferior de la pantalla de una lista de modificacion, para exportar todo el contenido de una lista a un archivo de datos plano. El valor por defecto es 0.

<BR>
<A NAME="users-export_reports">
<BR>
<B>Exportar Informes -</B> Este ajuste, si se pone a 1 permitirá a un manager el acceso a exportar reportes de marcación y contactos en la pantalla Informes. El valor predeterminado es 0. Para los reportes de Exportacion de Marcaciones y Contactos, el siguiente ordenamiento de campos es utilizado en la exportacion: <BR>call_date, phone_number_dialed, status, user, full_name, campaign_id/in-group, vendor_lead_code, source_id, list_id, gmt_offset_now, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, length_in_sec, user_group, alt_dial/queue_seconds, rank, owner

<BR>
<A NAME="users-delete_from_dnc">
<BR>
<B>Eliminar de listas DNC -</B> Este ajuste si el valor 1 permite a un administrador para eliminar los números de teléfono de las listas de DNC en el sistema.

<BR>
<A NAME="users-realtime_block_user_info">
<BR>
<B>Bloqueo Tiempo-real de Información Usuario -</B> Este ajuste, si se establece en 1 se bloqueará la información del usuario y la información de estado para evitar mostrarla en el informe sw tiempo real. El valor predeterminado es 0 para desactivado.

<BR>
<A NAME="users-modify_same_user_level">
<BR>
<B>Modificar Usuario del mismo Nivel -</B> Este ajuste sólo se aplica a usuarios de  nivel 9. Si se activa permite que el usuario nivel 9 pueda modificar su propia configuración, así como la de otros usuarios de nivel 9 . El valor predeterminado es 1 para habilitado.

<BR>
<A NAME="users-admin_hide_lead_data">
<BR>
<B>Admin Datos de Prospecto Ocultos -</B> Este ajuste sólo se aplica a usuarios de nivel 7, 8 y 9. Si se activa reemplaza los datos principales de los contactos en multiples informes y pantallas en el sistema con Xs. El valor predeterminado es 0 para deshabilitado.

<BR>
<A NAME="users-admin_hide_phone_data">
<BR>
<B>Admin Ocultar datos de teléfono -</B> Este ajuste sólo se aplica a usuarios de nivel 7, 8 y 9. Si está activado, reemplaza los números de teléfono de los contactos en los múltiples informes y pantallas en el sistema con Xs. El ajuste DÍGITOS sólo mostrará los dígitos de los últimos X del número de teléfono. El valor predeterminado es 0 para deshabilitado.






<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAÑAS DE MESA</FONT></B><BR><BR>
<A NAME="campaigns-campaign_id">
<BR>
<B>ID de Campaña -</B> Éste es el identificador de la campaña, el cual no es editable después de la remisión inicial, no puede contener espacios y debe tener entre 2 y 8 caracteres de longitudh.

<BR>
<A NAME="campaigns-campaign_name">
<BR>
<B>Nombre de campaña -</B> Ésta es la descripción de la campaña, debe tener entre 6 y 40 caracteres de longitud..

<BR>
<A NAME="campaigns-campaign_description">
<BR>
<B>Descripción de la campaña -</B> Este es un campo de notas para la campaña, es opcional y puede tener máximo 255 caracteres.

<BR>
<A NAME="campaigns-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos de esta campaña, esto permite visualización administrativa de esta campaña, así como las listas asignadas a esta campaña para ser restringidas por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario Admin, con permisos de grupo de usuario administrador de la campaña, la consulta de esta campaña.

<BR>
<A NAME="campaigns-campaign_changedate">
<BR>
<B>Fecha de Cambios en Campaña -</B> Ésta es la última vez que los ajustes para esta campaña fueron modificados de alguna forma.

<BR>
<A NAME="campaigns-campaign_logindate">
<BR>
<B>Última fecha de ingreso a la campaña -</B> Ésta es la última vez que un agente fue registrado en esta campaña.

<BR>
<A NAME="campaigns-campaign_calldate">
<BR>
<B>Última Fecha de Marcación de Campaña -</B> Esta es la última vez que una llamada fue manejado por un agente registrado en esta campaña.

<BR>
<A NAME="campaigns-max_stats">
<BR>
<B>Estadísticas Máximos Diarios -</B> Estas son estadísticas generadas por el sistema cada día durante todo el día hasta alcanzar la hora de fin de jornada definido en la Configuración del Sistema. Estos números son generados a partir de los registros en el sistema para permitir la visualización más rápida. Las estadísticas que se incluyen son - Totalde llamadas, Máximo de Agentes, Máximo llamadas entrantes, Máximo de llamadas salientes.

<BR>
<A NAME="campaigns-campaign_stats_refresh">
<BR>
<B>Campaña Stats Refresh -</B> Esta opción le permitirá forzar una campaña llamando a las estadísticas de actualización, incluso si la campaña no está activo.

<BR>
<A NAME="campaigns-realtime_agent_time_stats">
<BR>
<B>Estadísticas Temporales de Agente en Tiempo Real -</B> Al establecer este a cualquier cosa distinta de DISABLED, permitirá la recolección de estadísticas de tiempo del agente para el día de hoy para esta campaña, que se pueden ver a través del reporte de Tiempo Real. CALLS son el promedio de llamadas atendidas por agente, WAIT es el tiempo promedio de espera por agente, CUST es el tiempo medio de conversación con el cliente, ACW es el promedio de tiempo de TRabajo Post Marcación, PAUSA es el tiempo promedio de pausas. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-active">
<BR>
<B>Activo -</B> Aquí se establece la condición de la Campaña como Activa o Inactiva. Si es Inactiva, nadie se puede registrar en ella..

<BR>
<A NAME="campaigns-park_ext">
<BR>
<B>Aparcar Música de Espera -</B> Aquí es donde usted puede definir el contexto de música en espera para esta campaña. Asegúrese de seleccionar un contexto válido de música en espera, y que el contexto que usted selecciona tenga archivos válidos en él. El valor por defecto es default.

<BR>
<A NAME="campaigns-park_file_name">
<BR>
<B>Park File Name -</B> NOT USED.

<BR>
<A NAME="campaigns-web_form_address">
<BR>
<B>Formulario Web -</B> Aquí se establece la pagina web que será desplegada cuando el usuario haga click en el botón FORMULARIO WEB. Para personalizar la cadena de consulta después del formulario web, basta con iniciar el formulario web con VAR y, a continuación la URL que desee utilizar, remplazando las variables con el nombre de variables que desee utilizar --A--phone_number--B-- al igual que en la pestaña de la sección SCRIPTS. Si desea utilizar campos personalizados en una direccion de formulario web, es necesario agregarlo &CF_uses_custom_fields=Y como parte de su URL.

<BR>
<A NAME="campaigns-web_form_target">
<BR>
<B>Formulario Web Destino -</B> Aquí es donde usted puede establecer el marco de página web personalizada del formulario web que se abrirá cuando el usuario hace click en el botón formulario Web. El valor predeterminado es _blank.

<BR>
<A NAME="campaigns-allow_closers">
<BR>
<B>Permitir Agentes de Cierre -</B> Aquí se establece si los usuarios de esta campaña tendrán la opción de enviar la llamada a un Agente de Cierre.

<?php
if ($SSallow_emails > 0) 
		{
?>
	<BR>
	<A NAME="campaigns-allow_emails">
	<BR>
	<B>Permitir mensajes de correo electrónico -</B> Aquí es donde se puede establecer que los usuarios de esta campaña serán capaces de recibir mensajes de correo electrónico entrantes, además de llamadas telefónicas.
<?php
		}
?>
<BR>
<A NAME="campaigns-default_xfer_group">
<BR>
<B>Grupo de transferencia por defecto -</B> Este campo define el grupo de entrada por defecto que será seleccionado automáticamente cuando el agente se dirige al entorno de Trasferencia-conferencia en su interfas de agente.

<BR>
<A NAME="campaigns-xfer_groups">
<BR>
<B>Grupos de transferencia Permitidos-</B> Con estas listas verificables, usted puede seleccionar los grupos a los cuales los agentes pueden transferir llamadas. Permitir Agentes de Cierre debe ser permitido para que esta opción se despliegue.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-campaign_allow_inbound">
	<BR>
	<B>Permitir entrada y combinación -</B> Establecer  si los usuarios de esta campaña tendrán la opción para tomar llamadas de entrada en esta campaña. Si usted desea combinar la opcion de tomar llamadas de entrada y de salida combinada entonces se debe establecer el valor verdadero (Y). Si desea solamente hacer marcación de salida en esta campaña establecer el valor a  N. El valor por defecto es N.

	<BR>
	<A NAME="campaigns-dial_status">
	<BR>
	<B>Estado de Marcación-</B> Aquí se fijan los estados o condiciones que usted requiere para permitir la marcación dentro de las listas que son activas para la campaña definida posteriormente.. Para agregar otro estado de marcación, selecciónelo de la lista desplegable y haga click en AGREGAR. Para quitar uno de los estados de marcación, click en el enlace REMOVER junto al estado que quiere quitar..

	<BR>
	<A NAME="campaigns-lead_order">
	<BR>
	<B>Orden de la lista -</B> Este menú es donde usted selecciona cómo los contactos que cumplen con los estados selecionados arriba serán puestos en la lista de contactos para marcación:
	 <BR> &nbsp; - DOWN: seleccione el primero conduce cargadas en la tabla lista
	 <BR> &nbsp; - UP: seleccione los últimos conductores cargados en la tabla de la lista
	 <BR> &nbsp; - UP PHONE: seleccione el primer número y continue en forma descendente.
	 <BR> &nbsp; - DOWN PHONE: seleccione el último número y continue en forma ascendente.
	 <BR> &nbsp; - UP LAST NAME: Iniciar con apellidos comenzando con la Z y continuar en forma desendente
	 <BR> &nbsp; - DOWN LAST NAME: Iniciar con apellidos a partir de la A y continuar en forma ascendente
	 <BR> &nbsp; - UP COUNT: Iniciar con los contactos mas marcados y continuar en forma descendente
	 <BR> &nbsp; - DOWN COUNT: Iniciar con prospectos menos marcados y trabajar en forma ascendente.
	 <BR> &nbsp; - DOWN COUNT 2nd NEW: Iniciar con los contactos menos marcados y trabajar en forma ascendente insertando un NUEVO prospecto por cada contacto - NO debe tener la opción NUEVO activa en los estados de marcación.
	 <BR> &nbsp; - DOWN COUNT 3nd NEW: Iniciar con prospectos menos marcados y trabajar en forma ascendente e insertar NUEVOS prospectos por cada tercer contacto - NO debe tener la opción NUEVO activa en los estados de marcación.
	 <BR> &nbsp; - DOWN COUNT 4th NEW: Iniciar con prospectos menos marcados y trabajar en forma ascendente e insertar NUEVOS prospectos por cada cuarto contacto - NO debe tener la opción NUEVO activa en los estados de marcación.
	 <BR> &nbsp; - RANDOM:Selecciona contactos aleatoriamente en los estados y listas definidas
	 <BR> &nbsp; - ARRIBA ULTIMA HORA DE MARCACION: Ordena por el momento más reciente del horario de marcación local para los contactos
	 <BR> &nbsp; - ABAJO ULTIMA HORA DE MARCACION: Ordena por el momento más antiguo del horario de marcación local para los contactos
	 <BR> &nbsp; - UP RANK: Comienza con la más alta categoría y trabaja en forma descendente
	 <BR> &nbsp; - DOWN RANK: Comienza con el rango más bajo y trabaja en forma ascendente
	 <BR> &nbsp; - UP OWNER: Comienza con los propietarios, empezando con Z y trabajando de forma descendente
	 <BR> &nbsp; - DOWN OWNER: Comienza con propietarios empiezando con la A y trabajando en forma ascendente
	 <BR> &nbsp; - UP TIMEZONE: Comienza con las zonas horarias del Este y trabaja en el Oeste
	 <BR> &nbsp; - DOWN TIMEZONE: Comienza con las zonas horarias Occidentales y trabaja en el Oriente

	<BR>
	<A NAME="campaigns-lead_order_randomize">
	<BR>
	<B>Orden de lista Aleatoria -</B> Esta opción permite ordenar aleatoriamente los contactos en la lista de marcación dentro de los lineamientos definidos por los criterios establecidos anteriormente. Por ejemplo, el orden del listado se mantendrá, pero los resultados serán aleatorios dentro de esa clasificación. Al establecer esta opción a Y se activa esta función. El valor por defecto es N para deshabilitado. NOTA, si usted tiene un gran número de contactos esta opción puede ralentizar la velocidad del script de carga de la Lista de Marcación.

	<BR>
	<A NAME="campaigns-lead_order_secondary">
	<BR>
	<B>Lista Orden Secundario -</B> Esta opción le permite seleccionar un segundo criterio de ordenamiento orden de clasificación para los contactos en la Lista de Marcación  dentro de los resultados definidos por los criterios establecidos anteriormente. Esto significa que el orden de lista se utilizará para la primera clasificación de los contactos, pero los resultados se ordenarán una segunda vez dentro del ordenamiento de la primera clasificación. Por ejemplo, si usted tiene un criterio de ordenamiento de conteo descendente DOWN COUNT y solo tiene contactos que han sido contactados 1 y 2 veces, entonces al definir un criterio secundario de Contactos ascendente LEAD_ASCEND, entonces todos los contactos de un intento serán organizados por el mas antiguo primero y serán puestos en ese orden en la lista de marcación. El valor por defecto es LEAD_ASCEND. NOTE, Si tiene una gran cantidad de contactos utilizando una de las opciones CALLTIME podría ralentizar la velocidad del script de carga de Lista de Marcación. Si el ordenamiento de lista aleatorio se activa, esta opción será ignorada.

	<BR>
	<A NAME="campaigns-hopper_level">
	<BR>
	<B>Mínimo Nivel de Lista de Marcación -</B> Este es el número mínimo de clientes potenciales el guión tolva de carga trata de mantener en la tabla de la tolva para esta campaña. Si se ejecuta la escritura VDhopper cada minuto, hacen de este un poco mayor que el número de cables que pasan por en un minuto.

	<BR>
	<A NAME="campaigns-use_auto_hopper">
	<BR>
	<B>Nivel Automático de Lista de Marcación -</B> Al establecer esta a Y permitirá que el sistema para ajustar automáticamente la tolva con sede fuera de los ajustes que tiene en su campaña. La fórmula que utiliza para hacerlo es:<BR>Number of ActivoAgentes * Nivel del Automarcación * ( 60 seconds / Tiempo de espera de Marcación ) * Auto Hopper Multiplier<BR>Default is Y.

	<BR>
	<A NAME="campaigns-auto_hopper_multi">
	<BR>
	<B>Multiplicador Lista Atomatica de Marcación -</B> Esto es multiplicador de Lista Automática de Marcación. Al establecer este con un valor inferiór a 1 hará que el algoritmo de la Lista automática de Marcación para cargue menos contactos que los que haría normalmente. Al establecer esta mayor que 1, hará que el algoritmo de la tolva automática cargue más contactos que los que haría normalmente. El valor predeterminado es 1.

	<BR>
	<A NAME="campaigns-auto_trim_hopper">
	<BR>
	<B>AutoRecorte Lista de Marcación -</B> Al establecer esta a Y permitirá que el sistema para eliminar automáticamente el exceso de cable de la tolva. El valor predeterminado es Y.

	<BR>
	<A NAME="campaigns-hopper_vlc_dup_check">
	<BR>
	<B>Verificación Dup VLC en Lista de Marcación -</B> Si se establece en Y se traducirá en que cada prospecto será insertado el la lista de marcación siendo validado por el campo vendor_lead_code para asegurarse de que no hay prospectos duplicados insertados con la misma vendor_lead_code. Esto es muy útil cuando se establece Auto-Alt-Dialing con MULTI_LEAD. El valor predeterminado es N.

	<BR>
	<A NAME="campaigns-lead_filter_id">
	<BR>
	<B>Filtro de Contactos -</B> Método para filtrar los contactos usando un fragmento de consulta SQL. Utilice esta característica con precaución, es fácil detener la marcación accidentalmente con la mas sencilla declaración de SQL. El valor por defecto es ninguno-NONE.

	<BR>
	<A NAME="campaigns-call_count_limit">
	<BR>
	<B>Límite de Conteo de Llamadas -</B> Esto impone un límite en el número de intentos de marcación a los contactos marcados en esta campaña. Un contacto puede ir por encima de este límite ligeramente si el Reciclaje de Contactos o la AutomArcación Alterna están habilitadas. El valor predeterminado es 0 para ningún límite.

	<BR>
	<A NAME="campaigns-call_count_target">
	<BR>
	<B>Objetivo de Conteo de Llamadas -</B> Esta opción sólo se utiliza con fines de información y no tiene ningún efecto en los contactos marcados. El valor predeterminado es 3.

	<BR>
	<A NAME="campaigns-drop_lockout_time">
	<BR>
	<B>Horario Desbloqueo de Caída  -</B> Este es el número de horas de abandono de llamadas caidas impedirá su marcando, para establecer inhabilitar a 0. Esta opción es muy útil en países como el Reino Unido, donde existen normas que impiden la tentativa de llamar a los clientes dentro de las 72 horas de abandono, o Caida. Por defecto es 0.

	<BR>
	<A NAME="campaigns-force_reset_hopper">
	<BR>
	<B>Forzar reinicio de Lista de Marcación-</B> Permite que evacue el contenido de la lista de marcación en el formulario de remisión. Debe ser llenada muevamente cuando el script VDhopper se ejecute.

	<BR>
	<A NAME="campaigns-dial_method">
	<BR>
	<B>Método del Marcación -</B> Este establece el modo de definir cómo se realiza la marcación. Si es MANUAL entonces el auto_dial_level es asegurado en 0 a menos que se cambie el método marcación. Si es RATIO entonces permite marcación normal de un número de líneas según los agentes activos. ADAPT_HARD_LIMIT marcará predictivamente hasta un porcentaje de llamadas caidas y después no permitirá marcación agresiva una vez alcance el límite de caidas, hasta que reduzca este porcentaje nuevamente. ADAPT_TAPERED permite el desbordamiento del porcentaje de caidas en la mitad del primer cambio - según lo definido por el tiempo de marcación (call_time) para la campaña y se hace mas estricto en la medida en que va cambiando. ADAPT_AVERAGE intenta mantener un promedio del porcentaje de caidas al no imponer límites tan agresivos como en los otros dos métodos. No puede cambiar el nivel de automarcación si activa alguno de los métodos ADAPT de marcación. Solamente quien marca puede cambiar el nivel marcación cuando se encuentra en modo predictivo. INBOUND_MAN permite que el agente ejecute llamadas mediante marcación manual a partir de una lista de campaña, mientras es capaz de tomar las llamadas entrantes entre llamadas de marcación manual.

	<BR>
	<A NAME="campaigns-auto_dial_level">
	<BR>
	<B>Nivel del Automarcación -</B> Aquí es donde se establece el número de líneas del sistema debe utilizar por agente activo. cero 0 significa marcación automática está apagado y los agentes hará clic para marcar cada número. De lo contrario, el sistema mantendrá marcando líneas iguales a los agentes activos, multiplicado por el nivel de línea para llegar a la cantidad de líneas de esta campaña en cada servidor debe permitir. La casilla de verificación INVALIDAR ADAPTAR permite forzar un nuevo nivel de línea a pesar de que el método de línea está en un modo de ADAPT. Esto es útil si hay un cambio dramático en la calidad de los cables y desea cambiar drásticamente el dial_level manualmente.

	<BR>
	<A NAME="campaigns-dial_level_threshold">
	<BR>
	<B>Umbrañ de Automarcación -</B> Esta opción sólo funciona con un Método de marcación ADAPT o RATIO, y se debe establecer en algo distinto de DISABLED, y el número de configuraciones de agente debe ser superior a 0. Esta característica le permite establecer un número mínimo de agentes con los cuales trabajará el algoritmo predictivo. Si el número de agentes es inferior al número que usted ha fijado, entonces el nivel de marcación irá hasta 1.0 hasta que haya más agentes registrados o en marcha dentro del estado seleccionado. LOGGED-IN_AGENTS contará los agentes registrados en la campaña, NO PAUSED_AGENTS sólo contará los agentes que están esperando o hablando, y WAITING_AGENTS sólo contará agentes que están esperando una llamada. El valor predeterminado es DISABLED.

	<BR>
	<A NAME="campaigns-available_only_ratio_tally">
	<BR>
	<B>Registro de Únicos Disponibles -</B> Si este campo se establece como verdadero (Y), omitirá los agentes en estado INCALL y QUEUE en el cálculo del número de llamadas a marcar cuando no estan en modo de marcación manual. El valor por defecto es N.

	<BR>
	<A NAME="campaigns-available_only_tally_threshold">
	<BR>
	<B>Umbral de Conteo de Disponibles Únicamente -</B> Esta opción sólo funciona con método de marcación ADAPT o RATIO, y  Conteo de Disponibles Únicamente se debe establecer en N, asi, esta configuración se debe establecer en algo distinto a DISABLED y el número de ajustes de agentes debe ser superior a 0. Esta característica le permite establecer el número de agentes por debajo de los cuales se habilitará Conteo de Disponibles Únicamente. Si el número de agentes es inferior al número que usted ha fijado entonces, la configuración de Conteo de Disponibles Únicamente  ira en Y temporalmente hasta que más agentes re registren o entren en el estado seleccionado. LOGGED-IN_AGENTS contará todos los agentes registrados en la campaña, NO PAUSED_AGENTS sólo contará los agentes que están esperando o hablando, y WAITING_AGENTS solo contará agentes que están esperando llamada. El valor predeterminado es DISABLED.

	<BR>
	<A NAME="campaigns-adaptive_dropped_percentage">
	<BR>
	<B>Porcentaje Límite de Caidas -</B> En este campo se define el porcentaje límite de llamadas caídas que usted quisiera mientras se utilice un método de marcación predictiva adaptativa, no manual (MANUAL) o proporcional (RADIO).

	<BR>
	<A NAME="campaigns-adaptive_maximum_level">
	<BR>
	<B>Máximo nivel de Adaptación de Marcación -</B> Este campo es donde usted define el límite del numero de líneas de líneas que usted quisiera marcadas por agente mientras utiliza un método de marcación predictivo, no manual (MANUAL) o proporcional (RATIO). El valor debe ser un número positivo mayor que uno y puede tener lugares decimales. el valor por defecto es 3.0.

	<BR>
	<A NAME="campaigns-adaptive_latest_server_time">
	<BR>
	<B>HOra de cierre de Servidor -</B> Este campo es utilizado solamente por el método de marcación ADAPT_TAPERED. Debe digitar la hora y el minuto en que usted detendrá la marcación para la campaña; p.e. 2100 significa que usted detendrá la marcación de la campaña a las 9PM en la zona horaria de servidor. Esto permite que el algoritmo agudo decida la agresividad de la marcación mediante el tiempo disponible antes de terminar la jornada.

	<BR>
	<A NAME="campaigns-adaptive_intensity">
	<BR>
	<B>Mofificar Intensidad de Adaptación -</B> Este campo se utiliza para ajustar la intensidad predictiva a un nivel más alto o más bajo. El mas alto numero positivo elegido, sera mayor el incremento en  El mayor número positivo elegido incrementará el ritmo de marcación, así mismo se reducirá cuando se seleccione un valor menor . El menor número negativo incrementará la lentitud en que se realiza el ritmo de marcación siendo mas rápido en valores superiores. El valor por defecto es 0. Este campo no es utilizado por los métodos del marcacion manual (MANUAL) o proporcional (COCIENTE).

	<BR>
	<A NAME="campaigns-adaptive_dl_diff_target">
	<BR>
	<B>Objetivo de nivel de marcacion diferencial -</B> Este campo se utiliza para definir si desea tener como objetivo un determinado número de agentes de espera para las llamadas o las llamadas en espera de los agentes. Por ejemplo, si siempre quisiera tener en promedio un agente libre para recibir llamadas de inmediato entonces pondrá esto en -1, si usted quisiera tener siempre como objetivo una llamada en espera, esperando por un agente entonces se pone en 1. El valor predeterminado es 0. Este campo no es utilizado por los métodos de marcación MANUAL o INBOUND_MAN.

	<BR>
	<A NAME="campaigns-dl_diff_target_method">
	<BR>
	<B>Método de Diferencia Objetivo de nivel de Marcación -</B> Esta opción le permite definir si la configuración del objetivo de diferencia de nivel de marcación es aplicado solo al cálculo del nivel de marcación o también a la marcación actual en cada servidor de marcación. Si está ejecutando una pequeña campaña con agentes conectados en muchos servidores es posible que desee utilizar la opción ADAPT_CALC_ONLY, porque la opción CALLS_PLACED puede resultar en un número de llamadas inferior al deseado. Esta opción sólo está activa si el Objetivo Diferencial del Nivel de Marcación se establece en algo distinto de 0. El valor predeterminado es ADAPT_CALC_ONLY.

	<BR>
	<A NAME="campaigns-concurrent_transfers">
	<BR>
	<B>Transferencias Concurrentes -</B> Este ajuste se utiliza para definir el número de llamadas que se pueden enviar a los agentes al mismo tiempo. Se recomienda que este ajuste permanezca como AUTO. Esta definición no es utilizado por el método de marcacion manual (MANUAL).

	<BR>
	<A NAME="campaigns-queue_priority">
	<BR>
	<B>Prioridad de Cola - </B> Este valor se utiliza para definir el orden en que las llamadas salientes de esta campaña debe responderse en relación con las llamadas entrantes si esta campaña está en el modo mixto. No se recomienda establecer las llamadas entrantes a una prioridad más alta que las llamadas salientes de la campaña porque la gente al comunicarse espera que alguien este allí para contestarle y la gente que  llama espera que alguien este allí en el teléfono.

	<BR>
	<A NAME="campaigns-drop_rate_group">
	<BR>
	<B>Tasa Caida Grupal x Multiples Campañas -</B> Esta característica le permite establecer una campaña como miembro de una campaña de Grupo de Tasa de Caidas, o un grupo de campañas cuyas llamadas contestadas por humanos y llamadas caidas  para todas las campañas en el grupo serán combinadas dentro de un porcentaje de caída compartida, o tasa de abandono. Esto le permite ejecutar varias campañas a la vez y más fácil de controlar su tasa de caída. Esto es particularmente útil en el Reino Unido, donde las regulaciones permiten este método de cálculo de tasa caidas con agrupacion de campañas para la misma compañía, aún si existen multiples campañas que la  compañía este ejecutando el mismo día. Para habilitar esto para una campaña, sólo tienes que seleccionar un grupo de la lista. Eisten 10 grupos definidos en el sistema por defecto, puede ponerse en contacto con el administrador del sistema para añadir más. El valor por defecto es DISABLED.

	<BR>
	<A NAME="campaigns-inbound_queue_no_dial">
	<BR>
	<B>No Marcación cola de Entrada -</B> Si esta función se establece como ENABLED permite evitar el automracado de salida de esta campaña si hay llamadas entrantes esperando en la cola que forman parte de la configuración de grupos entrantes establecidos en la presente campaña. Si se establece en ALL_SERVERS cambiará el algoritmo para cálcular todas las llamadas entrantes como llamadas activas en este servidor, incluso si están en otro servidor lo cuál reducirá la posibilidad de colocar llamadas salientes innecesarias si tiene llamadas entrando en otro servidor. El valor predeterminado es DISABLED.

	<BR>
	<A NAME="campaigns-auto_alt_dial">
	<BR>
	<B>Automarcacion de número Alterno -</B> Este ajuste se utiliza para marcar automáticamente los numeros alternos cuando los métodos de marcación activos son proporcional(RATIO) y adaptativo (ADAPT), y no es posible contactar mediante el numero principal, ó en estados NA, B, DC y N. Este ajuste no es utilizado por el método de marcación manual (MANUAL). EXTENDED números alternativos extendidos son números cargados en el sistema fuera de la pantalla de información estándar del contacto. Utilizando EXTENDED usted puede tener cientos de números de teléfono para un solo registro de cliente. Usando MULTI_LEAD le permite utilizar un contacto separado para cada número en una cuenta de cliente en la que todos comparten un vendor_lead_code común, y el tipo de número de teléfono de cada uno se define con una etiqueta en el campo Propietario. Esta opción deshabilitará algunas opciones en la pantalla Modificar Campaña y mostrará un enlace a la página Configuración de Multi-Alt que le permitirá establecer los tipos de número de teléfono, definidos por la etiqueta de cada prospecto, podrá ser marcado y en qué orden. Esto creará un filtro especial de prospecto y alterará el campo categoría Rank de todos los prospectos dentro de las listas que figuran en esta campaña, con el fin de marcar en el orden que se haya especificado.

	<BR>
	<A NAME="campaigns-dial_timeout">
	<BR>
	<B>Vencimiento de Marcación -</B> Si está definido, llamadas que normalmente se cuelgan una vez alcanzado el tiempo de vencimiento definido en extensions.conf podrían a cambio terminar en este lapso (en segundos) si este es inferior al definido en extensions.conf. Esto se permite para modificar rápidamente los tiempos de cancelación de una llamada de servidor a servidor, y limitando los efectos campañas sencillas. Si usted tiene demasiadas llamadas de Maquinas Contestadoras o Correos de Voz, usted podría cambiar este valor en un rango entre 21-26 y verificar mejores resultados.

	<BR>
	<A NAME="campaigns-campaign_vdad_exten">
	<BR>
	<B>Extensión de Enrutamiento -</B> Este campo permite definir una extensión de enrutamiento de salida personalizado. Esto le permite utilizar diferentes métodos de manejo de llamadas, dependiendo de cómo desea enrutar las llamadas a través de su campaña de salida. Anteriormente conocido como extensión de Campaña VDAD. 
	<BR>- 8364 -  Igual que 8368
	<BR>- 8365 - Enviará la llamada unicamente a un agente en el mismo servidor cuando es puesta la llamada 
	<BR>- 8366 - Se utiliza para campañas de presionar-1, de difusión y de encuesta
	<BR>- 8367 - Tratará de enviar la primera llamada a un agente en el servidor local, y luego se verá en otros servidores
	<BR>- 8368 - DEFAULT - Envíará la llamada al siguiente agente disponible independientemente del servidor en que se encuentren
	<BR>- 8369 - Se utiliza para Detección de Máquina Contestadora y después de eso, el mismo comportamiento que 8368
	<BR>- 8373 - Se utiliza para Detección de Máquina Contestadora y después de eso, el mismo comportamiento que 8366
	<BR>- 8374 - Se utiliza para las campañas con Texto a voz de Cepstral,  Presionar-1, de difusión y estadística
	<BR>- 8375 - Se utiliza para campañas con  Texto a voz de Cepstral, de Detección de la Máquina Contestadora luego pulse-1, de difusión y estadísticas

	<BR>
	<A NAME="campaigns-am_message_exten">
	<BR>
	<B>Mensaje del contestador automático -</B> Este campo es para entrar la ruta de reproducción cuando el agente recibe un contestador automático y hace click en el botón Responder a Máquina Contestadora en el marco de conferencias de transferencia. Debe establecer que esto sea un archivo de audio en el almacen de audio, o un sistema TTS si está habilitado TSS en su sistema.

	<BR>
	<A NAME="campaigns-waitforsilence_options">
	<BR>
	<B>Opciones WaitForSilence -</B> Si Wait For silencio ese busca para las llamadas que se detectan como Máquinas Contestadoras, entonces este campo tiene esas opciones. Hay dos opciones separadas por coma, la primera opción es cuanto tiempo tomar para la detección del silencio en milisegundos, y la segunda opción es cuantas veces detectar esto antes de reproducir el mensaje. El valor por defecto es EMPTY para deshabilitado. Un valor estándar para este podría ser esperar 2 segundos de silencio en dos ocasiones: 2000,2

	<BR>
	<A NAME="campaigns-amd_send_to_vmx">
	<BR>
	<B>AMD envía a Acción -</B> Esta opción le permite definir si una llamada se envía a la Acción de AMD cuando se detecta un contestador automático. Si se establece en N, entonces la llamada se colgará en cuanto se determina que es un contestador automático. El valor predeterminado es N.

	<BR>
	<A NAME="campaigns-cpd_amd_action">
	<BR>
	<B>Acción CPD AMD - </B> Si está utilizando el software de detección de llamadas en progreso Sangoma ParaXip entonces deberá habilitar esta configuración en el valor DISPO el cuál dispondra de la llamada como AA y colgarla si se está procesando y no se ha enviado aún a un agente, o la opción MESSAGE la cuál enviará la llamada al contestador automático de mensajes definidos para esta campaña. El valor por defecto es DISABLED. Establecer esto como un grupo de entrada (INGOROUP)enviará un contestador automático a un grupo de entrada. Si se establece en CALLMENU enviará un contestador automático a un Menú de Marcación en el sistema.

	<BR>
	<A NAME="campaigns-amd_inbound_group">
	<BR>
	<B>AMD Grupo Entrante -</B> Si la Accióm CPD AMD se establece como INGROUP, entonces este es el grupo de entrada al que la llamada será enviada si se detecta un contestador automático.

	<BR>
	<A NAME="campaigns-amd_callmenu">
	<BR>
	<B>AMD Menu de Marcación -</B> Si una acción CPD AMD se establece en el CALLMENU, entonces este es el menú de MArcación al que la llamada se dirige si se detecta un contestador automático.

	<BR>
	<A NAME="campaigns-alt_number_dialing">
	<BR>
	<B>Manual Alt Num Marcación -</B> Esta opción permite que un agente para marcar manualmente el número de teléfono alternativo o address3 campo después del número principal ha sido llamado. Si la opción ha elegido en él, entonces la casilla Alt Dial se comprobará de forma automática para cada llamada. Si la opción tiene TEMPORIZADOR en él, entonces el Teléfono alternativo o campo Dirección3 serán automáticamente marcado después de segundos Temporizador Alt. El valor predeterminado es N para discapacitados.

	<BR>
	<A NAME="campaigns-timer_alt_seconds">
	<BR>
	<B>Segundos Alt Timer -</B> Si el ajuste Alt Num marcación manual tiene TEMPORIZADOR en él, entonces el Teléfono alternativo o campo Dirección3 serán automáticamente marcado después de esta cantidad de segundos. Por defecto es 0 para discapacitados.

	<BR>
	<A NAME="campaigns-drop_call_seconds">
	<BR>
	<B>Segundos de llamada Caida -</B> tiempo en segundos desde el momento en que la línea de cliente es tomada hasta que se requiere dar caida a la llamada, sólo se aplica a las llamadas desalida.

	<BR>
	<A NAME="campaigns-drop_action">
	<BR>
	<B>Acción post Caidas - </B> Este menú le permite elegir lo que le sucede a una llamada cuando se ha estado esperando durante más de lo establecido en el campo Segundos Caida de Llamada. HANGUP simplemente cuelga la llamada, MESSAGE envia el mensaje de llamar a la Extensión de Caída que se define a continuación, VOICEMAIL envía la llamada al buzón de voz se define a continuación, IN_GROUP enviará la llamada a la Grupo de Entrada que se define a continuación. VMAIL_NO_INST enviará la llamada al buzón de voz que ha definido a continuación y no jugará instrucciones después del mensaje de correo de voz.

	<BR>
	<A NAME="campaigns-safe_harbor_exten">
	<BR>
	<B>Exten de puerto Seguro -</B> Esta es la extensión en el plan de marcación que determina la localización en el servidor del archivo de audio deseado para  reproducir como mensaje de puerto seguro.

	<BR>
	<A NAME="campaigns-safe_harbor_audio">
	<BR>
	<B>Puerto Seguro-Audio -</B> Esta es la entrada del archivo de audio que se reproduce, si se establece como AUDIO la Acción de Caída. El valor predeterminado es buzz.

	<BR>
	<A NAME="campaigns-safe_harbor_audio_field">
	<BR>
	<B>PuertoSeguro -Campo de Audio -</B> Esta configuración opcional le permite definir un campo en la lista que el sistema utilizará como nombre de archivo de audio para cada contacto en lugar del archivo de audio de Puerto Seguro. Si se establece en DISABLED el archivo de audio de puerto seguro siempre será utilizado. El sistema hará ninguna validación para asegurarse de que el archivo de audio existe distinta de asegurarse de que el valor del campo es al menos un carácter, así que si se busca que un contacto utilize la opción predeterminada de Archivo de audio de Puerto Seguro, solo tienes que configurar el valor del campo en el contacto como Vacio. Usted puede utilizar el carácter de barra vertical para enlazar varios archivos de audio juntos en el valor del campo para cada contacto. Por defecto está desactivado. Aquí está la lista de los campos que se puede utilizar para este ajuste: vendor_lead_code, source_id, list_id, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, alt_phone, email, security_phrase, comments, rank, owner, entry_list_id

	<BR>
	<A NAME="campaigns-safe_harbor_menu_id">
	<BR>
	<B>Puerto Seguro Menú de Marcación -</B> Este es el menú de marcación al que una llamada se envía si la Acción de Caídad se establece como CALLMENU.

	<BR>
	<A NAME="campaigns-voicemail_ext">
	<BR>
	<B>Correo de Voz -</B> Si están definidas, llamadas que normalmente serían CAIDAS podrían redirigisrse a este buzon de voz para escuchar y dejar un mensaje..

	<BR>
	<A NAME="campaigns-drop_inbound_group">
	<BR>
	<B>Grupo de Transferencia de Caidas - </B> En caso de acción de caída se establece el Grupo de entrada al cuál la llamada será enviada si cumple el tiempo establecido para la Caida de la llamada.

	<BR>
	<A NAME="campaigns-no_hopper_leads_logins">
	<BR>
	<B>Permitir Acceso sin contactos activos en lista de Marcación  -</B> Si se establece el valor a Verdadero (Y), permite a los agentes registrar en la campaña incluso si no hay prospectos cargados en la lista de marcación para esa campaña. Esta función no se necesita en campañas de tipo CIERRE. El valor por defecto es N.

	<BR>
	<A NAME="campaigns-no_hopper_dialing">
	<BR>
	<B>Marcación Sin Lista de Marcación -</B> Si esta opción está activada, la lista de marcación (Hoper) no se ejecutará para esta campaña. Esta opción sólo está disponible cuando el método de marcación se establece como MANUAL ó INBOUND_MAN. Se recomienda que no habilite esta opción si usted tiene una base de datos de contactos muy grande, más de 100.000 prospectos. Con la opción Sin Lista de Marcación, las siguientes características no funcionan: reciclaje de prospecto, automarcación alterna, combinación de listas, ordenamiento de lista con X NEW. Si desea utilizar Marcación Exclusiva de Propietario debe tener activa la opción Sin Lista de Marcación. El valor por defecto es N para desactivado.

	<BR>
	<A NAME="campaigns-agent_dial_owner_only">
	<BR>
	<B>Marcación Exclusiva de Propietario -</B> Si esta opción está activada, el agente sólo recibirá contactos que están dentro de los parámetros de propietario. Si esto se establece como USER entonces el agente debe ser el usuario definido en la base de datos como el propietario de este Contacto. Si esto se establece en TERRITORY entonces el propietario del prospecto debe coincidir con el territorio listado en las opciones de modificación de la Pantalla de Usuario de este agente. Si se establece a USER_GROUP entonces el dueño del contacto debe coincidir con el grupo de usuarios al cuál el agente pertence. Para que esta característica funcione el método marcación se debe establecer como MANUAL o INBOUND_MAN y la marcación Sin Lista de Marcación debe estar activado. El valor predeterminado es NONE para desactivado. Si la opción tiene está en blanco al final, entonces a los usuarios se les se les permite a marcar Contactos sin propietario definido, además de los contactos con propietario definido.

	<BR>
	<A NAME="campaigns-owner_populate">
	<BR>
	<B>Propietario Rellenar -</B> Si esta opción está activada y el campo de propietario del contacto está en blanco, el campo propietario Contacto se rellenará con el ID de usuario del agente que se encarga de la primera llamada. El valor por defecto es DISABLED.

	<?php
	if ($SSuser_territories_active > 0)
		{
		?>
		<BR>
		<A NAME="campaigns-agent_select_territories">
		<BR>
		<B>Agente Selecciona territorios -</B> Si esta opción se activa y el agente pertenece a al menos un territorio, el agente tendrá la opción de selección de territorios desde la cual marcará contactos. El agente podrá ver una lista de los territorios disponibles a inicio de sesión y tendrán la capacidad de volver a la lista territorio cuando efecute una pausa para cambiar su territorio. Para que esta función trabaje, la opción de Marcación Exclusiva de Propietario debe configurarse con TERRYTORY y los territorios de usuario debe estar habilitados en la configuración del sistema.
		<?php
		}
	?>

	<BR>
	<A NAME="campaigns-list_order_mix">
	<BR>
	<B>Mezcla Orden de Lista-</B> Invalida el orden de los campos de Contacto y de Estados de Marcación. A cambio, utilizará los parámetros de la lista y del estado para la entrada de Lista Mixta en la subsección de Lista Mixta. El valor por defecto es DISABLED.

	<BR>
	<A NAME="campaigns-vcl_id">
	<BR>
	<B>ID de lista Mixta -</B> Identificador de la Lista Mixta. Debe tener entre 2 y 20 caracteres sin los espacios o signos de puntuación especiales.

	<BR>
	<A NAME="campaigns-vcl_name">
	<BR>
	<B>Nombre de Lista Mixta -</B> Nombre descriptivo de la Lista Mixta. Debe tener entre 2 y 50 caracteres.

	<BR>
	<A NAME="campaigns-list_mix_container">
	<BR>
	<B>Detalle de Lista Mixta -</B> Composición de la entrada de la Lista Mixta. Contiene el Id de la lista, orden de combinación, porcentajes y estados que constituen esta lista Mixta. Los porcentajes siempre deben sumar hasta 100, y las listas deben ser todas activas y configuradas para la campaña para uq la entrada del orden de combinación sea activado.

	<BR>
	<A NAME="campaigns-mix_method">
	<BR>
	<B>Método de Lista Mixta -</B> El método de combinación aplicado a todas las partes detalladas en la Lista Mixta juntas. EVEN_MIX combinará los prospectos de cada parte intercalada con las otras partes, como esta 1,2,3,1,2,3,1,2,3. IN_ORDER pondrá los prospectos en el orden en el cual se enumeran en pantalla de detallas de Lista Mixta 1,1,1,2,2,2,3,3,3.RANDOM los dispodrá de un ordenamiento aleatorio 1,3,2,1,1,3,2,1,3. El valor por defecto es IN_ORDER.

	<BR>
	<A NAME="campaigns-agent_extended_alt_dial">
	<BR>
	<B>Agent Screen Extended Alt Dial -</B> Esta característica permite a los agentes acceder a los números de teléfono alternativo extendidas de clientes potenciales más allá del Teléfono Alt estándar y campos Dirección3 que se puede utilizar en la pantalla de agente para los números de teléfono más allá del número de teléfono principal. Los números de teléfono Extended se pueden marcar de forma automática con la función Auto-Alt-Dial en la configuración de la campaña, pero la habilitación de esta función de pantalla Agent también permitirán al agente a llamar a estos números desde la pantalla de su agente así como editar su información. Esta característica está en desarrollo y no está disponible actualmente.

	<BR>
	<A NAME="campaigns-survey_first_audio_file">
	<BR>
	<B>Encuesta primer archivo de audio - </B> Este es el nombre de archivo de audio que se reproduce en cuanto el cliente coge el teléfono cuando se ejecuta una campaña de encuesta.

	<BR>
	<A NAME="campaigns-survey_dtmf_digits">
	<BR>
	<B>Encuesta Dígitos DTMF - </B> Este campo es donde se definen los dígitos que un cliente puede pulsar como una opción en una campaña de encuesta. Válidos unicamente los dígitos DTMF0123456789*#. Todas las opciones excepto para las de  No Interesados, opciondes de Tercer y Cuarto digito se moverán a la ruta de marcación de Metodo Encuesta.

	<BR>
	<A NAME="campaigns-survey_wait_sec">
	<BR>
	<B>Encuesta Segundos de Espera -</B> Este es el número de segundos en el modo de encuesta, en que el sistema esperará una entrada de la persona contactada hasta que se activa acción de Encuesta o de Caída. No se aplica si el método de encuesta es HANGUP. El tiempo predeterminado es 10 segundos.

	<BR>
	<A NAME="campaigns-survey_ni_digit">
	<BR>
	<B>Encuesta dígito de No Interesado - </B> Este campo es donde se define el digito a presionar por el cliente que indica que no está interesado.

	<BR>
	<A NAME="campaigns-survey_ni_status">
	<BR>
	<B>Encuesta  Estado de No Interesado -</B> Este campo es donde se selecciona el estado que se utilizará para No hay interés. Si DNC se utiliza y la campaña está configurada para utilizar DNC entonces el número de teléfono se añade automáticamente a la lista interna DNC y, posiblemente, la lista de DNC-campaña específica si que está habilitado en la campaña.

	<BR>
	<A NAME="campaigns-survey_opt_in_audio_file">
	<BR>
	<B>Encuesta Archivo audio de Opcoines-ent - </B> Este es el nombre de archivo de audio que se reproduce cuando el cliente ha decidido participar en la encuesta, no seleccionó opción de salida, ó no respondió si la accion de no respuesta se establece a OPTOUT. Después de reproducir este archivo de audio, se ejecuta acciónes Método de Encuesta.

	<BR>
	<A NAME="campaigns-survey_ni_audio_file">
	<BR>
	<B>Encuesta Archivo de Audio No Interesado- </B> Este es el nombre de archivo de audio que se reproduce cuando el cliente ha optapdo por salir de la encuesta, no seleccionar opción de entrada o no responder si la respuesta de no actividad se establece como OPTIN. Después de reproducir este archivo de audio, la llamada será colgada.

	<BR>
	<A NAME="campaigns-survey_method">
	<BR>
	<B>Método de encuesta - </B> Esta opción se define lo que sucede a una llamada después de que el cliente ha optado por entrar a la encuesta. AGENT_XFER enviará la convocatoria al siguiente agente disponible. VOICEMAIL se envía la llamada al buzón de voz de especificado en el campo Buzon de Voz. EXTENSION enviará al cliente a la extensión definida en el campo Extension Xfer Encuesta. HANGUP colgará al cliente. CAMPREC_60_WAV enviará al cliente a realizar una grabación con su respuesta, esta grabación se colocará en una carpeta con el nombre de la campaña dentro del Directorio de Grabaciones de Campaña de Encuesta. CALLMENU enviará al cliente al Menú de Marcación definido en la lista de selección de se encuentra a continuación.. VMAIL_NO_INST enviará la llamada al buzón de voz que ha definido a continuación y no jugará instrucciones después del mensaje de correo de voz.

	<BR>
	<A NAME="campaigns-survey_no_response_action">
	<BR>
	<B>Encuesta Acción No-Responde- </B> Define lo que ocurrirá si no hay respuesta a la pregunta de la encuesta. OPTIN sólo enviará la llamada al Método de Encuesta si el cliente pulsa un dígito DTMF. OPTOUT enviará al cliente en el Método de Encuesta, aun cuando no presiona un dígito DTMF. DROP cancelará la llamada utilizando el método de Caída de la Campaña, pero todavía puede iniciar la llamada como un estado PM de Mensaje Reproducido.

	<BR>
	<A NAME="campaigns-survey_response_digit_map">
	<BR>
	<B>Encuesta Enumerar Dígitos de Resuesta - </B> En esta sección define una descripción que vá junto a cada opción de dígito DTMF que el cliente puede seleccionar.

	<BR>
	<A NAME="campaigns-survey_xfer_exten">
	<BR>
	<B>Encuesta Xfer Extensión - </B> Si el método de encuesta de EXTENSIÓN se selecciona entonces la llamada de cliente se dirige a esta extensión del plan de marcación.

	<BR>
	<A NAME="campaigns-survey_camp_record_dir">
	<BR>
	<B>ENcuesta Directorio de Grabación de Campaña -</B> Si el método de encuesta CAMPREC_60_WAV se selecciona, entonces la respuesta de los clientes serán grabadas y se colocadas en un directorio llamado después de la campaña dentro de este directorio.

	<BR>
	<A NAME="campaigns-survey_third_digit">
	<BR>
	<B>Encuesta Tercer Dígito -</B> Esta permitido para una tercera ruta de marcacion si el tercer dígito, como está definido en este campo, es presionado por el cliente.

	<BR>
	<A NAME="campaigns-survey_fourth_digit">
	<BR>
	<B>Encuesta Cuarto Digit -</B> Esta permitido para una cuarta ruta de marcacion si el cuarto dígito, como está definido en este campo, es presionado por el cliente.

	<BR>
	<A NAME="campaigns-survey_third_audio_file">
	<BR>
	<B>Encuesta Tercer Archivo de Audio -</B> Este es el tercer archivo de audio a ser reproducido tras la selección por parte del cliente de la opción del tercer dígito.

	<BR>
	<A NAME="campaigns-survey_third_status">
	<BR>
	<B>Encuesta Estado Tercera Opción -</B> Este es el tercer estado utilizado para la llamada tras la selección por parte del cliente de la opción del tercer dígito.

	<BR>
	<A NAME="campaigns-survey_third_exten">
	<BR>
	<B>Encuesta Extensión Tercera Opción -</B> Esta es la tercera extensión utilizada para las llamadas tras la selección por parte del cliente de la opción del tercer dígito. El valor por defecto es 8300 el cual inmediatamente cuelga la llamada después de que se reproduce el archivo de audio de mensaje.

	<BR>
	<A NAME="campaigns-agent_display_dialable_leads">
	<BR>
	<B>Agente Despliega Contactos Marcables -</B> Si se activa esta opción se mostrará el número de contactos a los que es posible marcar disponibles en la campaña, en la pantalla del agente. Este número se actualiza en el sistema una vez por minuto y se actualiza en la pantalla del agente cada pocos segundos.

	<BR>
	<A NAME="campaigns-survey_menu_id">
	<BR>
	<B>Encuesta Menú de Marcación -</B> Si el método se ajusta en CALLMENU, este es el Menú de Marcación al que se envía el cliente si selecciona opción de ingreso opt-in.

	<BR>
	<A NAME="campaigns-survey_recording">
	<BR>
	<B>Encuesta Grabación -</B> Si está activa, este empezará a grabar cuando la llamada es contestada. Sólo recomendado si el método no está configurado para transferir a un agente. Defecto es n para deshabilitado. Si se establece en Y_WITH_AMD incluso los mensajes de Máquina Contestadora detectados serán grabados.

	<?php
	}
?>

<BR>
<A NAME="campaigns-next_agent_call">
<BR>
<B>Llamar siguiente agente -</B> Esto determina qué agente recibe la siguiente llamada que está disponible:
 <BR> &nbsp; - random: pedidos por el valor actualizado al azar en la tabla live_agents
 <BR> &nbsp; - oldest_call_start: Ordenar por la última vez que un agente envió una llamada. El resultado es que los agentes recibirán aproximadamente el mismo número de llamadas globales.
 <BR> &nbsp; - oldest_call_finish: Ordenar por la última vez que un agente terminó una llamada. El agente AKA que ha esperado mas tiempo recibe la primera llamada.
 <BR> &nbsp; - overall_user_level: órdenes del USER_NIVEL del agente tal como se define en la tabla a los usuarios una mayor USER_NIVEL recibirá más llamadas.
 <BR> &nbsp; - campaign_rank: Ordenamiento por la puntuación dada al agente para la campaña. De Mayor a menor..
 <BR> &nbsp; - campaign_grade_random: da una mayor probabilidad de recibir una llamada a los agentes de mayor categoría.
 <BR> &nbsp; - fewest_calls: Ordenamiento por el número de las llamadas recibidas por un agente para ese grupo de entrada específico. De menor a mayor.
 <BR> &nbsp; - longest_wait_time: ordena considerando la cantidad de tiempo que el agente ha estado esperando activamente por una llamada.

<BR>
<A NAME="campaigns-local_call_time">
<BR>
<B>Horario de Marcación Local -</B> Aqi se define el horario de marcación, determinado por la hora de la zona donde usted está marcando. Esto es controlado por código de Área y es ajustado para ajustes estacionales si es aplicable. Los lineamientos generales para EEUU en contactos comerciales estan dado así: marcación  Empresa a Empresa de 9AM a 5PM, y marcación Empresa a Consumidor de 9AM a 9PM. .

<BR>
<A NAME="campaigns-dial_prefix">
<BR>
<B>Prefijo de Marcación-</B> Este campo permite fácilmente cambiar una trayectoria de marción de salida mediante un método distinto sin hacer recargas en Asterisk. El valor por defecto es 9 basados en la plantilla 91NXXNXXXXXX definida en el plan de marcación - extensions.conf.

<BR>
<A NAME="campaigns-manual_dial_prefix">
<BR>
<B>Prefijo Marcación Manual -</B> Este campo opcional le permite configurar el prefijo de marcación para ser utilizado solamente cuando la colocación de las llamadas se hace manual desde la interfaz de agente, como cuando se utiliza la caracteristica MANUAL DIAL, o Marcación de Siguiente Número cuando está dentro del método de marcación MANUAL, o marcación manual de número alterno, o remarcaciones programadas de único usuario. El valor predeterminado es vacío para deshabilitado, el cuál utilizará el prefijo de marcación definido en el campo anterior. Esta opción no interfiere con la opción de prefijo de marcación Tripartito.

<BR>
<A NAME="campaigns-omit_phone_code">
<BR>
<B>Omitir Código de Teléfono -</B> Este campo le permite dejar el campo phone_code al marcar dentro del sistema. Por ejemplo, si usted está marcando en el Reino Unido desde el Reino Unido tendría 44 en que su campo phone_code para todas las pistas, pero lo que desea es marcar 10 dígitos en tu extensions.conf plan de marcado para realizar llamadas en lugar de 44 y luego 10 dígitos. El valor predeterminado es N.

<BR>
<A NAME="campaigns-campaign_cid">
<BR>
<B>Campaña ID de Llamada -</B> Este campo permite el envío de un número identificador de llamadas personalizado en las llamadas salientes. Este es el número que aparecerá en el identificador de llamadas de la persona que está llamando. El valor predeterminado es NO CONOCIDO. Si utiliza T1 o E1 para marcar esta opción sólo está disponible si está utilizando PRI - RDSI T1 o E1s - que tienen la función de identificador de llamadas personalizadas activada, esto no funcionará con-RBS-circuitos de servicio por bit robado. Esto también funciona a través de la mayoría de VOIP SIP o IAX troncos de proveedores que permiten identificador de llamadas salientes dinámico. La costumbre identificador de llamadas sólo se aplica a las llamadas realizadas para la campaña directamente, todas las llamadas de 3 ª parte o transferencias no enviarán el identificador de llamadas personalizado. NOTA: A veces poner DESCONOCIDO o PRIVADO en el campo rendirá el envío de su número de identificador de llamadas por defecto por su portador con las llamadas. Es posible que desee probar esto y poner 0000000000 en el campo identificador de llamadas en su lugar si usted no quiere que le enviemos CallerID.

<BR>
<A NAME="campaigns-use_custom_cid">
<BR>
<B>ID de Llamadas Personalizado -</B> Cuando se establece en Y, esta opción le permite usar el campo security_phrase en la tabla de lista como el identificador de llamadas para enviar al hacer de cada derivación específica. Si este campo no tiene CID en él, entonces el CallerID Campaña definido anteriormente será utilizado en su lugar. Esta opción desactiva el Override lista CallerID si hay un CID presente en el campo security_phrase. El valor predeterminado es N. Si se ajusta en Areacode usted tiene la capacidad de entrar en el submenú AC-CID y definir múltiples callerids para ser utilizado por areacode.

<BR>
<A NAME="campaigns-campaign_rec_exten">
<BR>
<B>Campaña Extensión de Grabaciónsion -</B> Este campo permite una extensión de grabación personalizada para ser utilizado con el sistema. Esto le permite utilizar diferentes extensiones dependiendo de cuánto tiempo usted desea permitir que un máximo de grabación y el tipo de códec que desea grabar pulg La extensión por defecto es 8309 que si se siguen los ejemplos SCRATCH_INSTALL registrarán en el formato WAV para un máximo de una hora. Otra opción incluida en los ejemplos es 8310 que registrará en formato GSM para un máximo de una hora. El tiempo de grabación se puede alargar al elevar el ajuste en la pantalla Modificación del servidor en la sección Admin.

<BR>
<A NAME="campaigns-campaign_recording">
<BR>
<B>Grabación de la campaña -</B> Esste menú permite elegir qué nivel de grabación es permitido en esta campaña. NEVER (Nunca) inhabilitará la grabación en el cliente. ONDEMAND (A solicitud) es el valor por defecto y permite que el agente comience y pare el registro según lo requiera. ALLCALLS (Todas las llamadas)comenzará la grabación en el cliente siempre que una llamada se envíe a un agente. ALLFORCE will start recording on the client whenever a call is sent to an agent giving the agent no option to stop recording. For ALLCALLS and ALLFORCE there is an option to use the Retardo en Grabación to cut down on very short recordings and reduce system load.

<BR>
<A NAME="campaigns-campaign_rec_filename">
<BR>
<B>Campaña Nombre de archivo de Grabación -</B> Este campo le permite personalizar el nombre de la grabación cuando la grabación de campaña es ONDEMAND o ALLCALLS. Las variables permitidas son CAMPAIGN INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH AGENTE VENDORLEADCODE LEADID CALLID. El valor predeterminado es FULLDATE_AGENTE y se vería así 20051020-103108_6666. Otro ejemplo es CAMPAIGN_TINYDATE_CUSTPHONE que se vería así TESTCAMP_51020103108_3125551212. el nombre  resultante del archivo debe ser inferior a 90 caracteres de longitud.

<BR>
<A NAME="campaigns-allcalls_delay">
<BR>
<B>Retardo en Grabación -</B> Para la grabación ALLCALLS y ALLFORCE solamente. Esta definición retardará el inicio de la grabación en todas las llamadas por tiempo en segundos especificado en este campo. el valor por defecto es 0.

<BR>
<A NAME="campaigns-per_call_notes">
<BR>
<B>Marcar por Notas de llamada -</B> Al establecer esta opción en ENABLED permitirá a los agentes digitar notas para cada llamada que realicen mediante la interfaz de agente. El campo de entrada de notas aparecerá abajo en el campo de Comentarios en la interfaz de agente. Además, si al Grupo de Usuario de Agente tiene permitido ver los registros de marcación entonces, el agente podrá ver notas de marcaciones pasadas realizadas a un contacto, en cualquier momento. El valor por defecto es DISABLED.

<BR>
<A NAME="campaigns-hide_call_log_info">
<BR>
<B>Ocultar Call Log Info -</B> Al habilitar esta opción, se oculta cualquier registro de llamadas o call información conteo cuando se muestra la información principal en la pantalla del agente. El valor predeterminado es N.

<BR>
<A NAME="campaigns-agent_lead_search">
<BR>
<B>Agente Búsqueda de Contacto -</B> Al establecer esta opción en ENABLED se permitirá a los agentes a buscar prospectos y ver la información del prospecto durante la pausa en la interfaz de agente. Además, si al Grupo de Usuarios de Agente se le permite ver los registros de marcación entonces, el agente será capaz de ver notas a marcaciones anteriores realizadas a cualquier prospecto que se esté consultando. El valor predeterminado es DISABLED. LIVE_CALL_INBOUND permitirá busqueda de contactos mientras mientras se está ejecutando unicamente una llamada entrante. LIVE_CALL_INBOUND_AND_MANUAL permitirá busqueda de contactos en una llamada entrante o mientras está en pausa. Cuando la busqueda de contacto se realiza durante una llamada entrante activa, el contacto de la llamada cuando abandona al agente cambiará al estado de LSMERG, y los registros de la llamada serán modificados para enlazar en su lugar al contacto elegido por el agente.

<BR>
<A NAME="campaigns-agent_lead_search_method">
<BR>
<B>Agente Método de Búsqueda de Contactos -</B> Si la búsqueda de contactos del agente está habilitada, este parámetro define el lugar donde el agente se le permitirá la búsqueda de prospectos. SYSTEM buscará en todo el sistema, CAMPAIGNLISTS buscará dentro de todas las listas activas dentro de la campaña, CAMPLISTS_ALL buscará dentro de todas las listas activas e inactivas dentro de la campaña, LISTA sólo buscará dentro de la lista de ID de Marcacion Manual como esté definda dentro de la Campaña. El valor predeterminado es CAMPLISTS_ALL. Una de estas opciones con USER_ en frontal, sólo buscará dentro de los contactos que tienen campo propietario coincidente con el ID de usuario del agente, las opciones con GROUP_ en frontal sólo buscarán dentro de los contactos que tienen el campo propietario coincidente con el grupo de usuarios del cual el usuario es miembro, las opciones con TERRITORY_ en frontal sólo buscará dentro de los contactos que tienen el campo propietario coincidente con el territorio que el agente ha seleccionado.

<BR>
<A NAME="campaigns-campaign_script">
<BR>
<B>Script de campaña -</B> Este menú permite que usted elija el script que aparecerá en la pantalla de los agentes para esta campaña. Seleccione NONE para no presentar ningún Script para la campaña.

<BR>
<A NAME="campaigns-get_call_launch">
<BR>
<B>Desplegar al Llamar -</B> Este menú permite seleccionar si usted desee autodesplegar la página del formulario web en una ventana separada, autoconmutar con la pestaña de script o no haga nada cuando se envía una llamada al agente para esta campaña. Si los campos de lista personalizados están habilitados en su sistema, FORM abrirá la pestaña FORM despues producirse la conexión de una llamada a un agente.

<BR>
<A NAME="campaigns-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Estos campos permiten que usted tenga dos conjuntos de Transferencia Conferencia y presets DTMF. Cuando se carga la llamada o la campaña, la pantalla del agente mostrará dos botones del marco de transferencia de conferencias y rellenar automáticamente el número de línea y los campos Enviar DTMF cuando se pulsa. Si desea permitir transferencias de consulta, un fronter a una cerca, tiene el agente utiliza la casilla CONSULTIVO, que no funciona por falta de agentes de las llamadas de consulta de terceros. Para aquellos que sólo tienen el agente haga clic en el botón de Marcación con el Cliente. A continuación, el agente sólo puede dejar-3WAY-CALL y pasar a la siguiente llamada. Si desea permitir las transferencias ciegas de los clientes a un script AGI para el registro o un IVR, a continuación, coloque AXFER en el campo de número-to-dial. También puede especificar una extensión personalizada después de la AXFER, por ejemplo, si usted quiere hacer una llamada a un IVR especial que usted ha ajustado a 83.900 extensión que pondría AXFER83900 en el campo de número-to-dial.

<BR>
<A NAME="campaigns-quick_transfer_button">
<BR>
<B>botón Transferencia Rápida -</B> Esta opción agrega un botón de Transferencia Rápida a la pantalla de agente, por debajo del botón Transfer-Conf. que permitirá la transferencia llamadas ciegas mediante un solo click al número ó Grupo de Entrada seleccionado. IN_GROUP enviará las llamadas al Grupo de Xfer predeterminado para esta campaña, o Grupo-Entrada si hay una llamada entrante. Las opciones PRESET enviarán las llamadas a preselección  realizada. Por defecto es N para desactivado. Las opciones de bloqueo LOCKED se utilizan para bloquear el valor en el botón de transferencia rápida, incluso si el agente utiliza las características de transferencia de la conferencia durante una llamada antes de utilizar el botón de transferencia rápida.

<BR>
<A NAME="campaigns-custom_3way_button_transfer">
<BR>
<B>Boton Tranferencia Tripartita -</B> Esta opción, agregará un botón de transferencia personalizada a la pantalla del agente por debajo de la tecla de Transferencia-Conf que permitirá marcaciones tripartitas con un click utilizando el campo o ajuste seleccionado. Las opciones de PRESET_ realizaran llamadas utilizando el valor de configuración preestablecido. Las opciones FIELD_ realizarán llamadas utilizando el número en el campo seleccionado en el registro del prospecto. DISABLED no mostrará el botón en la pantalla del agente. Las opciones PARK_ aparcarán el cliente antes de marcar. El valor predeterminado es DISABLED. La opción VIEW_PRESET simplemente abrirá el marco de la transferencia y el marco preestablecido. La opción VIEW_CONTACTS abrirá una ventana de búsqueda de contactos. Esto sólo funcionará si Habilitar Precofiguraciones se se establece en CONTACTS.

<BR>
<A NAME="campaigns-prepopulate_transfer_preset">
<BR>
<B>Transferencia Preseleccionada -</B> Esta opción llenará el número telefónico en campo de marcación en el marco de Conferencia de Transferencia de la pantalla de agente si es definido. El valor por defecto es N para desactivado.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Activar Preconfiguración de transferencia -</B> Esta opción permitirá que un menú de preconfiguraciones aparezca en la parte superior de la página de Modificación de Campaña, y también tendrá la posibilidad de especificar los números predeterminados de marcación para los agentes a utilizar en el marco de Transferencia-Conferencia de la interfaz de agente. El valor predeterminado es DISABLED. CONTACTS es una opción sólo si contact_information está habilitado en su sistema, esto es una característica personalizada.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Activar Preconfiguración de transferencia -</B> Esta opción permitirá que un menú de preconfiguraciones aparezca en la parte superior de la página de Modificación de Campaña, y también tendrá la posibilidad de especificar los números predeterminados de marcación para los agentes a utilizar en el marco de Transferencia-Conferencia de la interfaz de agente. El valor predeterminado es DISABLED. CONTACTS es una opción sólo si contact_information está habilitado en su sistema, esto es una característica personalizada.

<BR>
<A NAME="campaigns-hide_xfer_number_to_dial">
<BR>
<B>Ocultar número de transferencia a Marcar -</B> Esta opción ocultará el campo número a marcar en el marco de Transferencia-Conferencia de la interfaz de agente. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call">
<BR>
<B>Aparcar Marcaciones IVR -</B> Esta opción permite a un agente aparcar una llamada con un botón independiente IVR PARK CALL en su interfaz de agente, si esta opción está activada o ENABLED_PARK_ONLY. La opción ENABLED_PARK_ONLY permitirá que el agente pueda aparcar la llamada pero no le permite hacer click para recuperarla, como ocurre con la opción ENABLED. La opción ENABLED_BUTTON_HIDDEN permite que la función opere a través de la API unicamente. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call_agi">
<BR>
<B>Aparcar llamadas IVR AGI -</B> Si el campo de Aparcamiento de Llamadas IVR no se establece DISABLED, entonces este campo se utiliza como la cadena de aplicación AGI que se envía al cliente. Este es un ajuste que debe ser establecido por el administrador, si es posible.

<BR>
<A NAME="campaigns-timer_action">
<BR>
<B>Temporizador de Acción -</B> Esta característica le permite activar acciones después de una cierta cantidad de tiempo. Las opciones de marcación D1 y D2 lanzarán una llamada a los Números de Transferencia de Conferencia predefinidos y los enviará a la sesión de agente; Este se utilizado generalmente para aplicaciones sencillas de validación IVR, aplicaciones AGI o simplemente para reproducir un mensaje pregrabado. WEBFORM abrirá la dirección de formulario web. MESSAGE_ONLY simplemente mostrará el mensaje que está en el campo de abajo. Ninguna de ellas podrá deshabilitar esta característica y es el predeterminado. HANGUP colgará la llamada cuando el temporizador se activa, CALLMENU enviará la llamada al Menú de Marcación especificado en el campo de acción del temporizador de destino, EXTENSIÓN enviará la llamada a la extensión que se especifica en el campo de acción del temporizador de destino, IN_GROUP enviará la llamada al Grupo-Entrada especificado en el campo de acción del temporizador de destino.

<BR>
<A NAME="campaigns-timer_action_message">
<BR>
<B>Mensaje Acción de Temporizador -</B> Este es el mensaje que aparece en la pantalla del agente en el momento de ejecutar la acción del temporizador .

<BR>
<A NAME="campaigns-timer_action_seconds">
<BR>
<B>Segundos Accion de Temporizador -</B> Esta es la cantidad de tiempo después de que la llamada se conecta al clientes que se desencadena la acción del temporizador. El valor predeterminado es -1, el cuál es también inactivo.

<BR>
<A NAME="campaigns-timer_action_destination">
<BR>
<B>Destino Acción Temporizador -</B> Este campo es donde se especifica el menú de Marcación, Extensión o el grupo de entrada al que desea enviar la llamada está definida como  CALLMENU, EXTENSION o IN_GROUP. El valor por defecto es vacío.

<BR>
<A NAME="campaigns-scheduled_callbacks">
<BR>
<B>Agenda de Remarcación -</B> Eesta opción permite a un agente disponer de una llamada como CALLBK (Remarcación) y elige la fecha y hora a la cual el prospecto será reactivado.

<BR>
<A NAME="campaigns-scheduled_callbacks_alert">
<BR>
<B>Alerta de Remarcación Programada -</B> Esta opción permite que la línea de estado de remarcación en la interfaz de agente sea de color rojo, parpadear o parpadear en rojo cuando está programada la remarcación AGENTONLY, que han alcanzado su fecha y hora de activación. El valor predeterminado es NONE para la línea de estado estándar. Las opciones de aplazamiento DEFER, detendrán el parpadear y-o  el despliege en rojo, cuando se activa la remarcación, hasta que el número de remarcaciones cambie.

<BR>
<A NAME="campaigns-scheduled_callbacks_count">
<BR>
<B>Conteo de Remarcaciones Programadas -</B> These options allows you to limit the viewable callbacks in the agent callback alert section on the agent screen, to only LIVE callbacks.  LIVE callbacks are user-only scheduled callbacks that have hit their trigger date and time. ACTIVE call backs are user-only callbacks that are active in the system but have not yet triggered.  You can view both ACTIVE and LIVE callbacks by selecting ALL_ACTIVE.  Default is ALL_ACTIVE.

<BR>
<A NAME="campaigns-callback_days_limit">
<BR>
<B>Días Límite de Remarcaciones Programadas -</B> Esta opción le permite reducir el el calendario de remarcaciones programadas por agente a un número de días seleccionable a partir de hoy, sin embargo el calendario de 12 meses completo se seguirá desplegando, pero sólo la configuración del número de días será seleccionable. El valor predeterminado es 0 para ilimitado.

<BR>
<A NAME="campaigns-callback_hours_block">
<BR>
<B>Bloque Horas Remarcación Programados -</B> Esta opción le permite restringir una remarcación USERONLY prevista, de ser mostrada en la lista de remarcaciones del agente hasta que pasen X horas después haberse establecido. El valor predeterminado es 0 para no bloquear.

<BR>
<A NAME="campaigns-callback_list_calltime">
<BR>
<B>Bloque Horarios Remarcación Programados -</B> Esta opción si está habilitada impedirá la remarcación programada de la lista remarcación del agente si está fuera del horario previsto para la campaña. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-my_callback_option">
<BR>
<B>Mi Casilla de Verificación de  Remarcacion Predeterminada -</B> Esta opción le permite pre-configurar la casilla de verificación Mi Remarcaciónde en la pantalla de agente para programación de remarcación. El valor por defecto es UNCHEKED.

<BR>
<A NAME="campaigns-wrapup_seconds">
<BR>
<B>Segundos de Cierre -</B> Segusndos que se debe forzar al agente a esperar antes de marcar o recibir la siguiente llamada. El contador de tiempo inicia tan pronto como un agente cuelgue a su cliente, ó en el caso del marcación de numeros alternativos cuando el agente termina con el contacto.- el valor por defecto es 0 seg. Si el contador de tiempo se desborda antes de que el agente halla dispuesto la llamada, el agente todavía no podrá avanzar a la llamada siguiente hasta que seleccionan una disposición final.

<BR>
<A NAME="campaigns-wrapup_message">
<BR>
<B>Mensaje de Cierre -</B> Mensaje especifico a cada campaña que se mostrará en el tablero de cierre si se establece este tiempo.

<BR>
<A NAME="campaigns-disable_dispo_screen">
<BR>
<B>Desactivar Pantalla de Disposición -</B> Esta opción le permite desactivar la pantalla de Disposición en la interfaz de agente. El campo Estado de Disposición Desactivado presentado a continuación, deben ser llenados para que funcione esta opción. El valor predeterminado es DISPO_ENABLED. La opción DISPO_SELECT_DISABLED no desactivará la pantalla de disposición por completo, pero se mostrará la pantalla de disposición sin ningún tipo de disposiciones, esta opción sólo debe utilizarse si usted quiere forzar a los agentes a utilizar su software de CRM, el cuál enviará el estado del sistema a través de la API.

<BR>
<A NAME="campaigns-disable_dispo_status">
<BR>
<B>Desactivar Estado de Disposición -</B> Si la opción Desactivar Pantalla de Disposición se ajusta a DISPO_DISABLED, entonces este campo debe ser llenado. Puede usar cualquier disposición que desee para este campo, siempre y cuando tenga de 1 a 6 caracteres, sólo con letras y números.

<BR>
<A NAME="campaigns-dead_max">
<BR>
<B>Dead segundos Call Max -</B> Si se establece en mayor que 0, después de que un cliente cuelga y el agente no ha hecho clic en el botón al Cliente Colgar en este número de segundos, la llamada será automáticamente colgó, el estado a continuación se establece y el agente será pausa. Por defecto es 0 para discapacitados.

<BR>
<A NAME="campaigns-dead_max_dispo">
<BR>
<B>Dead estado de las llamadas Max -</B> Si Dead Call Max segundos se habilita, este es el estado establecido para la llamada cuando el agente de llamada muertos no se cuelga para arriba más allá de la cantidad de segundos indicados anteriormente. El valor predeterminado es DCMX.

<BR>
<A NAME="campaigns-dispo_max">
<BR>
<B>Dispo Call Max segundos -</B> Si se establece en mayor que 0, y el agente no ha seleccionado un estado de disposición en este número de segundos, la llamada se ajustará automáticamente a la condición de abajo y el agente se pondrá en pausa. Por defecto es 0 para discapacitados.

<BR>
<A NAME="campaigns-dispo_max_dispo">
<BR>
<B>Dispo Call Max Estado -</B> Si Dispo Call Max segundos se habilita, este es el estado establecido para la llamada cuando el agente no ha seleccionado un estado más allá de la cantidad de segundos indicados anteriormente. El valor predeterminado es DISMX.

<BR>
<A NAME="campaigns-pause_max">
<BR>
<B>Agente Pausa Max segundos -</B> Si se establece en mayor que 0, y el agente no ha salido del estado de en pausa en este número de segundos, el agente automáticamente se cerrará la sesión de la pantalla del agente. Por defecto es 0 para discapacitados.

<BR>
<A NAME="campaigns-screen_labels">
<BR>
<B>Agente Etiquetas de Pantalla -</B> Con esta opción puede seleccionar un conjunto de etiquetas de pantalla de agente para utilizar. El valor predeterminado es --SYSTEM-SETTINGS-- para las etiquetas predeterminadas.

<BR>
<A NAME="campaigns-status_display_fields">
<BR>
<B>Campos Pantalla de Estados -</B> Usted puede seleccionar que variables de marcación serán desplegadas en la linea de estados de la pantalla de agente. CallID mostrará el ID de Llamada único de 20 caracteres, LEADID mostrará el ID del contacto en el sistema, LISTID mostrará el ID de la lista. El valor predeterminado es CALLID.

<BR>
<A NAME="campaigns-use_internal_dnc">
<BR>
<B>Utilizar Lista interna DNC -</B> Esto define si la campaña es para filtrar contactos frente a la lista DNC. Si se establece el valor a verdadero (Y), la lista de marcación buscará cada número de teléfono en la lista DNC antes de trasladarla a la lista de marcación. Si está en la lista DNC entonces cambiará el estado de este Contacto a DNCL de tal forma que no pueda ser marcado. El valor por defecto es N. La opción CÓDIGOÁREA es como la opción Y, excepto que si es utilizada también filtrará un código de área completo en NorteAmérica para ser marcado, en este caso utilizando la entrada 201XXXXXXX en la lista DNC podría bloquear todas las llamadas a los códigos de área 201 si está habilitada.

<BR>
<A NAME="campaigns-use_campaign_dnc">
<BR>
<B>Utilizar Lista DNC de Campaña - </B> Este define si esta campaña es para filtrar contactos comparado con una lista DNC que es especifica para esa campaña solamente. Si se establece el valor a verdadero (Y), la lista de marcación se verá para cada número de teléfono en la lista DNC específica de la campaña antes de colocarla en la lista de marcación. Si está en la lista DNC especifica de la campaña entonces cambiara el estado del contacto a DNC y por lo tanto no podrá ser marcado. El valor por defecto es N. La opción CÓDIGOÁREA es como la opción Y, excepto que si es utilizada también filtrará un código de área completo en NorteAmérica para ser marcado, en este caso utilizando la entrada 201XXXXXXX en la lista DNC podría bloquear todas las llamadas a los códigos de área 201 si está habilitada.

<BR>
<A NAME="campaigns-use_other_campaign_dnc">
<BR>
<B>DNC Otra Campaña -</B> Si la Opción Usar Lista DNC de Campaña está activa, esta opción permitirá el uso de una lista de DNC de campaña diferente, sólo hay que poner el ID de Campaña de la otra campaña en este campo. Si utiliza esta opción, se verificará con la Lista DNC original de la campaña, sólo se utilizará la otra lista DNC de campaña. Esto no afecta el uso de la lista de DNC Interna del Sistema. El valor por defecto es EMPTY.

<BR>
<A NAME="campaigns-closer_campaigns">
<BR>
<B>Grupos De entrada Permitidos -</B> For CLOSER campaigns only. Here is where you select the inbound groups you want agents in this CLOSER campaign to be able to take calls from. It is important for BLENDED inbound-outbound campaigns only to select the inbound groups that are used for agents in this campaign. The calls coming into the inbound groups selected here will be counted as active calls for a blended campaign even if all agents in the campaign are not logged in to receive calls from all of those selected inbound groups.

<BR>
<A NAME="campaigns-agent_pause_codes_active">
<BR>
<B>Agent Códigos de PausaActivo -</B> Permite a los agentes para seleccionar un código de pausa al hacer clic en el botón de pausa en la pantalla del agente. Códigos de pausa son definibles por campaña en la parte inferior de la pantalla de la vista de detalle de la campaña y que se almacenan en la tabla agent_log. El valor predeterminado es N. FUERZA obligará a los agentes para elegir un código PAUSA si hacen clic en el botón PAUSE.

<BR>
<A NAME="campaigns-auto_pause_precall">
<BR>
<B>Auto-Pausa Trabajo de Premarcación -</B> En el modo de marcación automática, si esta opción está habilitada se establece que el agente sea pausado automáticamente cuando el agente hace click en cualquiera de las siguientes funciones que les obliga a estar en pausa-  Marcación Manual , Marcación Abreviada, Búsqueda de Prospecto, Ver Registro de Marcación, Verificar Remarcación, Ingresar Código de Pausa. El valor por Defecto es n para inactivas.

<BR>
<A NAME="campaigns-auto_resume_precall">
<BR>
<B>Auto Reanudar Trabajo de Premarcación -</B> En el modo de marcación automática, si esta opción está habilitada se establece que el agente como activo de forma automática cuando el agente hace click en salir de, o cancela,  en cualquiera de las siguientes funciones que les obliga a estar en pausa - Marcación Manual, Marcación Abreviada, Búsqueda de Prospecto, Ver Registro de Marcación, Verificar Remarcaciones, Ingresar Código de Pausa. El valor por defecto es N para inactivo.

<BR>
<A NAME="campaigns-auto_pause_precall_code">
<BR>
<B>Código Auto-Pausa de Premarcación -</B> Si la función de Trabajo de Autopausa de Premarcación está activa, este ajuste será el código de pausa que se utiliza cuando el agente se detiene para estas actividades. El valor predeterminado es PRECAL.

<BR>
<A NAME="campaigns-disable_alter_custdata">
<BR>
<B>Desactivar Modificar Datos de Cliente -</B> Si se establece el valor verdadero Y, no permite modificar ningun registro de datos del cliente cuando un agente dispone de la llamada. El valor por defecto es N.

<BR>
<A NAME="campaigns-disable_alter_custphone">
<BR>
<B>Deshabilitar Teléfono personalizado Alterno -</B> Si se establece el valor como verdadero (Y), no cambia el número de teléfono del cliente, cuando un agente dispone de la llamada. El valor por defecto es Y. Utilice la opción OCULTAR para eliminar completamente el número de teléfono del cliente de la pantalla del agente.

<BR>
<A NAME="campaigns-display_queue_count">
<BR>
<B>Agente Despliegue de Contador de Cola - </B> Se se establece el valor verdadero (Y), cuando un cliente está a la espera de un agente, la cola de llamadas en la pantalla superior de la pantalla agente se iluminará en rojo y mostrará el número de llamadas en espera. El valor por defecto es Y.

<BR>
<A NAME="campaigns-manual_dial_override">
<BR>
<B>Invalidación de Marcación Manual -</B> El ajuste puede Invalidar la configuración de Usuarios en la capacidad de marcación manual para los agentes, cuando éstos son registrados en esta campaña. NINGUNO mantiene la configuración de los usuarios, ALLOW_ALL permitirá que cualquier agente registrado en esta campaña pueda  hacer llamadas mediante marcación manual, DISABLE_ALL no permitirá que nadie que haya iniciado sesión en esta campaña pueda hacer llamadas con marcación manual. El valor predeterminado es NINGUNO.

<BR>
<A NAME="campaigns-manual_dial_list_id">
<BR>
<B>Manual Dial ID de Lista -</B> El valor predeterminado list_id para ser utilizado cuando un agente realiza una llamada manual y se crea un nuevo registro de plomo en la mesa lista. El valor predeterminado es 999 Este campo puede contener sólo dígitos..

<BR>
<A NAME="campaigns-manual_dial_filter">
<BR>
<B>Filtro Marcación Manual - </B> Esto permite filtrar las llamadas que hacen los agentes en el modo de marcación manual para esta campaña por cualquier combinación de los siguientes: DNC - a expulsar, CAMPAIGNLISTS - el número debe estar dentro de las listas para la campaña, NONE - sin filtro en marcación manual, o marcación rápida de listas. CAMPLISTS_ALL - incluirá las listas inactivas en la búsqueda de del número.

<BR>
<A NAME="campaigns-manual_dial_search_checkbox">
<BR>
<B>Manual Dial Buscar Checkbox -</B> Esto le permite definir si desea que la casilla de verificación manual del dial de búsqueda para seleccionar por defecto o no. Si se elige una opción con RESET, entonces la casilla de verificación se restablecerá después de cada llamada. Por defecto se selecciona.

<BR>
<A NAME="campaigns-manual_preview_dial">
<BR>
<B>Manual Previsualizar Marcación -</B> Esto permite al agente en el modo de marcación manual ver la información de prospecto cuando se hace click en Marcar Siguiente Número, antes de marcar activamente la llamada telefónica. Hay un enlace opcional SKIP que permite abandonar el prospecto y saltar al siguiente si se ha seleccionado. El valor predeterminado es PREVIEW_AND_SKIP.

<BR>
<A NAME="campaigns-manual_dial_lead_id">
<BR>
<B>Marcación manual por ID plomo -</B> Esto permite al agente en el modo de marcado manual para hacer una llamada por lead_id en lugar de un número de teléfono. El valor predeterminado es N para discapacitados.

<BR>
<A NAME="campaigns-api_manual_dial">
<BR>
<B>API Marcación Manual -</B> Esta opción le permite configurar la API de Agente para hacer ya sea una llamada a la vez, STANDARD, ó la capacidad de poner llamadas en cola de forma manual y tenerlas para marcación automática una vez que el agente entra en pausa o está disponible para tomar la siguiente llamada, con la opción de desactivar la marcación automática de estas llamadas, QUEUE ó QUEUE_AND_AUTOCALL la cuál es lo mismo que cola, pero sin la opción para desactivar la marcación automática de estas llamadas. Si un agente tiene más de una llamada en cola entonces verá el contador de cuántas llamadas de marcación manual se encuentran justo en la cola debajo del botón de Pausa, o del botón de Marcar Siguiente Número. Se sugiere que si se utiliza la configuración QUEUE de tal forma que usted pueda envíar Acciones de API utilizando la opción de vista previa preview=YES, entonces no habrá marción repetida del agente sin notificación. Asimismo, si está utilizando QUEUE y en gran medida realiza llamadas de marcación manual en un método de marcación no manual, es recomendable ajustar la opción de Agente Pausa Después de Cada Llamada al valor Y. el valor predeterminado es STANDARD.

<BR>
<A NAME="campaigns-manual_dial_call_time_check">
<BR>
<B>Verificación Horario de Marcación Manual -</B> Si esta opción está activada, se comprobarán todas las llamadas de marcado manual para asegurarse de que están dentro de los ajustes de horarios de Marcación establecidos para la campaña. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-manual_dial_cid">
<BR>
<B>CID de Marcación Manual -</B> Esto define si un agente que realiza Marcación Manual tendrá la configuración de ID de Llamadas que la campaña utiliza, o utiliza su configuración de agentes de ID de Llamada. El valor predeterminado es CAMPAIGN. Si la opción de Utilizar CID Personalizado está activa o es utilizada la lista de  invalidación de CID de Campaña, este ajuste será ignorado.

<BR>
<A NAME="campaigns-post_phone_time_diff_alert">
<BR>
<B>Alerta de Diferencia Horaria -</B> Esta característica de solo marcación manual, si está activa, mostrará una alerta si la zona horaria para el código postal de prospecto, o el código postal, es diferente de la zona horaria del código de área del número de teléfono para el prospecto. La opción OUTSIDE_CALLTIME_ONLY sólo mostrará la alerta si las dos zonas de tiempo son diferentes y una de las zonas horarias se encuentra fuera del tiempo de marcación seleccionado para la campaña. OUTSIDE_CALLTIME_PHONE sólo comprueba si la zona horaria del número de teléfono del prospecto y alerta si está fuera del horario de marcación local. OUTSIDE_CALLTIME_POSTAL sólo comprueba la zona horaria del código postal del prospecto y alerta si está fuera del horario de marcación local. OUTSIDE_CALLTIME_BOTH comprobará que el código postal y número de teléfono estén dentro del horario de marcación local, incluso si se encuentran en la misma zona horaria. Estas alertas se muestran en la información del registro de llamadas, información de listas de remarcación, información de resultados de búsqueda, cuando un prospecto se contacta y cuando un prostpecto es consultado. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-in_group_dial">
<BR>
<B>Grupo-Entrada Marcación Manual -</B> Esta característica le permite habilitar la capacidad de los agentes para realizar llamadas salientes que se registran como en marcaciones de grupo-entrada asignadas a un determinado grupo de entrada. La opción MANUAL_DIAL permite la colocación de las llamadas telefónicas a través del grupo de entrada del agente que realiza la llamada. La opción NO_DIAL permite que el agente registre la hora de una llamada que no existe, como si se tratara de una llamada real, función que a menudo se utiliza para el registro de correo electrónico o la hora de envío de faxes. La opción BOTH permitirá la marcación  del grupo de entrada tanto  con-llamada como sin-llamada. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-in_group_dial_select">
<BR>
<B>Grupo-Entrada Selecionar Marcación Manual -</B> Esta opción sólo está activa si la característica de Marcacion Manual de Grupo de entrada definida anteriomente no se establece como DISABLED. Esta opción restringe Los grupos de entrada seleccionables que el agente puede colocar dentro del Grupo de Entrada de marcacion manual. CAMPAIGN_SELECTED mostrará sólo los los grupos de entrada que la campaña se ha establecido como grupos de entrada permitidos. ALL_USER_GROUP mostrará todos los grupos de entrada que son visibles para a los miembros del grupo de usuarios al que el agente pertenece.

<BR>
<A NAME="campaigns-agent_clipboard_copy">
<BR>
<B>Copia a Portapapeles de pantalla de Agente  - </B> ESTA FUNCIÓN POR EL MOMENTO SÓLO SE HAN PERMITIDO PARA INTERNET EXPLORER. Esta característica le permite seleccionar un campo que será copiado al portapapeles el equipo del ordenador del agente en base a una llamada que se envíe a un agente. Usos comunes para ello son permitir pegado fácil de números de cuenta o números de teléfono en aplicaciones de cliente complementarias en el equipo agente.

<BR>
<A NAME="campaigns-three_way_call_cid">
<BR>
<B>3-Way Call CallerID De salida -</B> Esto define lo que se envía como el número identificador de llamadas salientes de las llamadas de 3 vías colocados por el agente, campaña utiliza la campaña personalizada identificador de llamadas, clientes utilizan el número de cliente que está activa en los agentes de la pantalla y utiliza el identificador de llamadas AGENT_PHONE para el teléfono que el agente se registra en. AGENT_CHOOSE permite al agente para elegir qué identificador de llamadas para las llamadas de 3 vías de una lista de opciones. CUSTOM_CID utilizará el CID personalizado que se define en el campo security_phrase de la tabla de lista por el liderato.

<BR>
<A NAME="campaigns-three_way_dial_prefix">
<BR>
<B>Marcación Tripartita  Prefijo Marcación - </B> Esto define lo que se utiliza como prefijo de marcación para las llamadas tripartitas; por defecto está vacío por lo que se utiliza el prefijo de marcación de la campaña, atravesandolo de tal forma que puede escuchar un timbre que corresponde a 88.

<BR>
<A NAME="campaigns-customer_3way_hangup_logging">
<BR>
<B>Colgar Registro de Cliente Tripartito -</B> Si esta opción se establece como activa, el user_call_log permitirá el registro cuando un cliente cuelgue, si ha colgado durante una llamada tripartita. Además, esto puede permitir al cliente de llamada tripartita una acción de colgar si se define a continuación. El valor por defecto es Activado.

<BR>
<A NAME="campaigns-customer_3way_hangup_seconds">
<BR>
<B>Cliente Colgar Tripartito (seg) -</B> Si el acceso de Cliente tripartito es activado, esta opción le permite definir el número de segundos que en que será detectado que el cliente ha colgado, antes de que realmente sea registrado y la acción opcional de colgado de llamada tripartita sea ejecutada. El valor predeterminado es 5 segundos.

<BR>
<A NAME="campaigns-customer_3way_hangup_action">
<BR>
<B>Acción Colgar Cliente Tripartito -</B> Si el registro de cliente tripartito está habilitado, esta opción le permite tener la pantalla del agente de forma automática para colgar la llamada e ir a la pantalla DISPO, si esta opción está establecida en DISPO. El valor predeterminado es NINGUNO.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-qc_enabled">
	<BR>
	<B>CC Activado - </B> Establecer este campo como verdadero (Y) permite activar la configuración al agente de control de calidad. El valor predeterminado es N.

	<BR>
	<A NAME="campaigns-qc_statuses">
	<BR>
	<B>CC Estados - </B> Esta zona es donde se selecciona el estados en que los contactos deben estar para ser enviador a revisión por control de calidad. Coloque una marca de verificación junto al estado que desea que CC revise.. 

	<BR>
	<A NAME="campaigns-qc_shift_id">
	<BR>
	<B>CC Horario - </B> Este es el marco de tiempo utilizado para obtener registros de control de calidad para una campaña. Los días de la semana no se tienen en cuenta para estas funciones.

	<BR>
	<A NAME="campaigns-qc_get_record_launch">
	<BR>
	<B>CC Obtener Reproducción de Grabación-</B> Esto permite que una de las siguientes acciones sea activada por un agente de control de calidad recibiendo una nueva grabación.

	<BR>
	<A NAME="campaigns-qc_show_recording">
	<BR>
	<B>CC Mostrar grabación - </B>, Permite que una grabación pueda vincularse con el registro de control de calidad que se mostrará en pantalla el agente de control de calidad.

	<BR>
	<A NAME="campaigns-qc_web_form_address">
	<BR>
	<B>CC Dirección Formulario Web- </B> Esta es la dirección del sitio web en la cual un agente de control de calidad puede alcanzar tras hacer click en el enlace WEBFORM en la pantalla de control de calidad.

	<BR>
	<A NAME="campaigns-qc_script">
	<BR>
	<B>CC Escritura - </B> Este es el script que puede ser utilizado por agentes de control de calidad en pestaña SCRIPT en el tablero de control de calidad.
	<?php
	}
?>

<BR>
<A NAME="campaigns-vtiger_search_category">
<BR>
<B>Vtiger Search Category -</B> Si la integración Vtiger está habilitado en la configuración del sistema, entonces este parámetro definirá donde la página vtiger_search.php buscará el número de teléfono introducido. Hay 4 opciones que se pueden utilizar en este campo: LEAD-Esta opción buscará a través del Vtiger sólo conduce, CUENTA-Esta opción buscará a través de las cuentas de Vtiger y todos los contactos y sub-contactos para el número de teléfono, VENDEDOR: esta opción solamente buscará a través de los vendedores de Vtiger, ACCTID-Esta opción sólo funciona para las cuentas y que le llevará al campo vendor_lead_code lista y tratar de buscar el ID de cuenta Vtiger. Si no tiene éxito, intentará cualquier otro método de las mencionadas que ha seleccionado. Múltiples opciones pueden utilizarse para cada búsqueda, pero en las grandes bases de datos no se recomienda. El valor predeterminado es LEAD. UNIFIED_CONTACT-Esta opción será utilizar la beta Vtiger 5.1.0 característica de búsqueda por número de teléfono y abrir una página de búsqueda en Vtiger.

<BR>
<A NAME="campaigns-vtiger_search_dead">
<BR>
<B>Vtiger Buscar Cuentas Muertas -</B> Si la integración Vtiger está activa en la configuración del sistema, entonces esta configuración definirá si las cuentas eliminadas se buscarán cuando el agente haga click en WEB FORM para realizar una busqueda en el sistema Vtiger. DISABLED no buscará en contactos eliminados, ASK- los contactos eliminados serán buscados y la pagina web de búsqueda vtiger preguntará al agente si quiere hacer la cuenta vtier activa, RESURRECT- hará la cuenta eliminada activa de nuevo y llevará al agente a la pantalla de cuenta sin demora al hacer clic en WEB FORM. El valor por defecto es DISABLED.

<BR>
<A NAME="campaigns-vtiger_create_call_record">
<BR>
<B>Vtiger Crear Registro Marcación - </B> Si está habilitada la integración Vtiger en la configuración del sistema, esta configuración definirá si un nuevo registro de actividad Vtiger se crea para la llamada, cuando el agente va a la página vtiger_search. El valor por defecto es Y. La opción DISPO creará un registro de llamadas para la cuenta de Vtiger sin que el agente requiera ir a la página de búsqueda vtiger a través del formulario Web.

<BR>
<A NAME="campaigns-vtiger_create_lead_record">
<BR>
<B>Vtiger Crear Registro de Contacto - </B> Si está habilitado la integración con Vtiger en la configuración del sistema y la Categoría de Búsqueda Vtiger incluye LEAD entonces esta configuración definirá si un nuevo registro de Contacto Vtiger es creado cuando el agente va a la página vtiger_search y no se encunetra ningun registro para tener la llamada al número de teléfono. El valor por defecto es Y.

<BR>
<A NAME="campaigns-vtiger_screen_login">
<BR>
<B>Vtiger Screen Login -</B> Si la integración Vtiger está habilitado en la configuración del sistema, entonces este parámetro definirá si el usuario ha iniciado sesión en la interfaz de Vtiger automáticamente al iniciar la sesión a la pantalla del agente. El valor predeterminado es Y. La opción new_window abrirá una nueva ventana sobre la conexión a la pantalla del agente.

<BR>
<A NAME="campaigns-vtiger_status_call">
<BR>
<B>Vtiger Estado de Llamada -</B> Si la integración Vtiger está habilitado en la configuración del sistema, entonces este parámetro definirá si el estado de la Cuenta Vtiger se actualizará con el estado de la llamada después de haber sido dispositioned. El valor predeterminado es N.

<BR>
<A NAME="campaigns-queuemetrics_callstatus">
<BR>
<B>QM Invalidar Estado de Marcación -</B> Si la integración QueueMetrics está activada en la configuración del sistema, entonces esta configuración permite invalidación de la configuración del sistema de las entradas de Estados de Marcación a queue_log. El valor predeterminado es DISABLED el cuál utilizará la configuración del sistema.

<BR>
<A NAME="campaigns-queuemetrics_phone_environment">
<BR>
<B>QM Entorno Telefónico -</B> Si la integración QueueMetrics está activada en la configuración del sistema, esta configuración permite la inserción de este valor en el campo data4 de queue_log para los registros de actividad del agente. El valor predeterminado es vacío para deshabilitado.

<BR>
<A NAME="campaigns-extension_appended_cidname">
<BR>
<B>CID Anexar Extensión -</B> Si está activado, las llamadas realizadas a partir de esta campaña tendrá un espacio y la extensión telefónica del agente anexa al final del nombre de ID de Llamada antes de ser enviada al agente. El valor por defecto es n para desactivado.

<BR>
<A NAME="campaigns-pllb_grouping">
<BR>
<B>PLLB Agrupación  -</B> PLLB (Acceso Teléfono Load Balancing) - Agrupación de Balanceo de Carga por Acceso Telefonico, sólo se permite si hay varios servidores de agentes y los alias de teléfono están presentes en el sistema. Si se establece en ONE_SERVER_ONLY obligará a todos los agentes de esta campaña a iniciar sesión en el mismo servidor. Si se establece a CASCADING agrupará los agentes registrados en el mismo servidor hasta que se alcance el numero límite de agrupacióm PLLB de agentes, entonces el siguiente agente se conectará al servidor siguiente con el menor número de agentes. Si se establece en DISABLED el comportamiento estándar de los Alias Telefonicos de cada agente será encontrar el servidor con el menor número de agentes no remotos registrados, para ser utilizados. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-pllb_grouping_limit">
<BR>
<B>PLLB Límite Agrupación -</B> PLLB (Acceso Teléfono Load Balancing) Límite de Agrupacion de Balanceo de Carga por Acceso Telefónico. Si la Agrupación PLLB se encuentra establecida como CASCADING, entonces este ajuste determinará el número de agentes aceptable en cada servidor para esta campaña. Por defecto es 50.

<BR>
<A NAME="campaigns-crm_popup_login">
<BR>
<B>CRM Popup de Acceso -</B> Si se establece en Y, la Dirección de Popup CRM se utiliza para abrir una nueva ventana de inicio de sesión de agente para esta campaña. El valor por defecto es n.

<BR>
<A NAME="campaigns-crm_login_address">
<BR>
<B>CRM Dirección Popup -</B> La dirección web de una página de acceso de CRM, puede tener las variables para diligenciar como la dirección de formulario web, con VAR en el frente y utilizando --A--user_custom_one--B-- para definir variables.

<BR>
<A NAME="campaigns-start_call_url">
<BR>
<B>URL Inicio Llamada -</B> Esta URL de dirección web no es vista por el agente, sino que es llamada cada vez que una llamada se envía a un agente si se diligencia este campo. Utiliza las mismas variables que los campos de formulario web y scripts. Esta URL no puede ser una ruta relativa. La URL de inicio no funciona para las llamadas de marcación manual. Por defecto está en blanco.

<BR>
<A NAME="campaigns-dispo_call_url">
<BR>
<B>URL disposicion de Llamada -</B> Esta URL de dirección web no es vista por el agente, sino que es llamada cada vez que una llamada es dispuesta por un agente, si está diligenciada. Utiliza las mismas variables que los campos de formulario web y scripts. dispo y talk_time son las variables que puede utilizar para recuperar la disposición establecida por el agente para la llamada y el tiempo de conversación real en segundos de la llamada. Esta URL no puede ser una ruta relativa. Por defecto está en blanco.

<BR>
<A NAME="campaigns-na_call_url">
<BR>
<B>URL Contacto Sin Agente  -</B> Esta dirección web URL no es vista por el agente, pero si está rellena se llama cada vez que se una llamada no es atendida por un agente, colgada o transferida. Utiliza las mismas variables que los campos de formularios web y scripts. Disposición puede utilizarse para recuperar la disposición definida por el sistema para la llamada. Esta URL no puede ser una ruta relativa. El valor predeterminado es en blanco.

<BR>
<A NAME="campaigns-agent_allow_group_alias">
<BR>
<B>Alias de Grupo Permitido - </B> Si desea permitir que sus agentes utilicen alias de grupo, entonces debe establecer este valor como Verdadero (Y). Los Alias de Grupo se explican más en la sección de administración. Estos permiten a los agentes seleccionar diferentes ID de llamada para las llamadas manuales salientes que ellos puedan colocar. El valor predeterminado es N.

<BR>
<A NAME="campaigns-default_group_alias">
<BR>
<B>Alias de Grupo Predeterminado - </B> Si ha permitido el uso de Alias de Grupo este es el Alias de grupo que se ha seleccionado como opción primera o por defecto, cuando el agente decide usar un alias para un grupo de salida manual de llamada. El valor por defecto es vacío o NINGUNA.

<BR>
<A NAME="campaigns-view_calls_in_queue">
<BR>
<B>Agente ve Llamadas en cola -</B> Si no se pone a nada  en lugar de NONE, los agentes podrán consultar los detalles de las llamadas que están en cola de espera, en su pantalla de agente. Si se establece un valor númerico, las llamadas desplegadas se limitarán al número seleccionado. El valor predeterminado es NONE.

<BR>
<A NAME="campaigns-view_calls_in_queue_launch">
<BR>
<B>Ver llamadas en Cola de lanzamiento -</B> Este ajuste, si se establece en AUTO tendrá lasllamads en el marco de cola visibles tras el inicio de sesión de agente, dentro de la pantalla de agente. El valor predeterminado es MANUAL.

<BR>
<A NAME="campaigns-grab_calls_in_queue">
<BR>
<B>Agente Toma Llamadas en Cola -</B> Esta opción si se establece como Y, permitirá que el agente seleccione la llamada que quiere tomar de las llamadas en el despliegue de cola haciendo click en ella durante la pausa. Los agentes sólo podrán coger las llamadas entrantes o las llamadas transferidas, no llamadas de salida. El valor por defecto es N.

<BR>
<A NAME="campaigns-call_requeue_button">
<BR>
<B>Boton Re-encolar llamada Agente -</B> Esta opción si se establece como Y, agregará un botón Re-Encolar cliente (Re-Queue Customer )a la pantalla del agente, permitiendo al gente enviar las llamadas a una cola AGENDIRECT está reservada únicamente para agentes. El valor por defecto es N.

<BR>
<A NAME="campaigns-pause_after_each_call">
<BR>
<B>Agente Pausa después de Cada Llamada -</B> Esta opción si se establece como Y, hará una pausa en el agente después de cada llamada automáticamente. El valor por defecto es N.

<BR>
<A NAME="campaigns-pause_after_next_call">
<BR>
<B>Agente Pausa posterir al Enlace del Siguiente Agente -</B> Esta al activar esta opción se mostrará un enlace en la pantalla de agente que le permitirá entrar pausa automáticamente después de colgar su próxima llamada. Predeterminado es Desactivado.

<BR>
<A NAME="campaigns-blind_monitor_warning">
<BR>
<B>Advertencia Monitor Oculto -</B> Esta opción está activa, permitirá al agente conocer de varias formas opcionales si están siendo controlados de manera oculta. DISABLED significa que esta función no está activa, ALERT sólo aparecerá una alerta en la pantalla el agente, NOTICE pegará una nota que permanece en la pantalla el agente, durante el tiempo que está siendo monitoreado, AUDIO reproducirá el nombre define a continuación cuando un agente está empezando a ser monitoreados y las otras opciones son combibnations de las opciones anteriores. El valor predeterminado es DISABLED.

<BR>
<A NAME="campaigns-blind_monitor_message">
<BR>
<B>Notificación de Monitor Oculto -</B> Este es el mensaje que aparecerá en la pantalla del agente, mientras que están siendo monitoreados, si la opción NOTICE se ha seleccionado. El valor predeterminado es -Someone is blind monitoring your session-.

<BR>
<A NAME="campaigns-blind_monitor_filename">
<BR>
<B>Archivo Monitor Oculto -</B> Este es el archivo de audio que se reproducirá en la sesión de los agentes al inicio del monitoreo oculto. Este mensaje se reproducirá para todo el mundo en la sesión, incluyendo al cliente si está presente. Por defecto está vacío.

<BR>
<A NAME="campaigns-max_inbound_calls">
<BR>
<B>Las llamadas entrantes Max -</B> Si este parámetro se establece en un número mayor que 0, entonces será el número máximo de llamadas entrantes que un agente puede manejar todos los grupos entrantes en un día. Si el agente alcanza el número máximo de llamadas entrantes, entonces no van a ser capaces de seleccionar los grupos entrantes para atender las llamadas de hasta el día siguiente. Esta configuración se puede anular con la configuración Usuario del mismo nombre. Por defecto es 0 para discapacitados.






<BR><BR><BR><BR>

<B><FONT SIZE=3>LISTAS DE MESA</FONT></B><BR><BR>
<A NAME="lists-list_id">
<BR>
<B>ID de Lista -</B> Este es el identificador numérico de la lista, el cuál no es editable después de remisión inicial, debe contener solamente números y debe tener entre 2 y 8 caracteres de longitud. Must be a number greater than 100.

<BR>
<A NAME="lists-list_name">
<BR>
<B>Nombre de Lista -</B> Es la descripción de la lista, debe estar entre 2 y 20 caracteres de longitud.

<BR>
<A NAME="lists-list_description">
<BR>
<B>Descripción de lista -</B> éste es el campo notas para la lista, es opcional.

<BR>
<A NAME="lists-list_changedate">
<BR>
<B>Fecha de cambio de lista -</B> Ésta es la vez última que los ajustes para esta lista fueron modificados de alguna forma.

<BR>
<A NAME="lists-list_lastcalldate">
<BR>
<B>Última Fecha de Marcacion de Lista -</B> Ésta es la última vez que un un prospecto de esta lista fué contactado .

<BR>
<A NAME="lists-campaign_id">
<BR>
<B>Campaña -</B> Ésta es la campaña a la cual pertenece esta lista. Una lista se puede marcar solamente en una única campaña..

<BR>
<A NAME="lists-active">
<BR>
<B>Activa -</B> Esto establece sí se permite o nó, realizar marcaciones a partir de esta lista..

<BR>
<A NAME="lists-reset_list">
<BR>
<B>Reinicio de Estado de Contacto para esta Lista -</B> Esto reinicia todos los prospectos en esta lista al valor N para "no marcado desde el último reinicio" y significa que cualquier prospecto puede ahora ser llamado si este es el estado correcto según lo definido en la pantalla de la campaña.

<BR>
<A NAME="lists-reset_time">
<BR>
<B>Horas de Reinicio -</B> Este campo le permite ingresar horas, separadas por un guión, en que esta lista se restablece automáticamente por el sistema. Las horas deben estar en formato de 24 horas sin puntuacion, por ejemplo 0800-1700 indica restablecer la lista de las 8 AM y las 5 PM todos los días. Por defecto está vacío.

<BR>
<A NAME="lists-expiration_date">
<BR>
<B>Fecha de Vencimiento -</B> Esta opción le permite configurar la fecha después de la cual los contactos en esta lista no se les  permitirá contacto mediante auto-marcación o marcación por el sistema. El valor predeterminado es 2099-12-31.

<BR>
<A NAME="lists-audit_comments">
<BR>
<B>Auditoría Comentarios -</B> Esta opción permite que los comentarios se mueven a una tabla de auditoría. Ya no es editable, pero puede ser consultada  junto con la fecha de creación y autor de cada comentario. El valor predeterminado es N. Esta es una parte del Complemento (Add-on) de Control de Calidad.

<BR>
<A NAME="lists-agent_script_override">
<BR>
<B>Invalidar Script de Agente -</B> Si se establece este campo como verdadero, esta será la secuencia de comandos que el agente ve en su pantalla en lugar de la secuencia de comandos de campaña cuando el contacto se encuentra en esta lista. Por defecto no está activo.

<BR>
<A NAME="lists-campaign_cid_override">
<BR>
<B>Invalidar CID de Campaña -</B> Si se activa este campo, invalidará el ID de llamadas CID que se estableció para las llamadas que se realicen a contactos de esta lista. Por defecto esta inactivo.

<BR>
<A NAME="lists-am_message_exten_override">
<BR>
<B>Invalidar Mensaje Maquina Contestadora -</B> Si se establece este campo, esto anulará el mensaje de contestador automático definido en la campaña para los clientes en esta lista. El valor por defecto es desactivado. 
<BR>
<A NAME="lists-drop_inbound_group_override">
<BR>
<B>Invalidar Grupo Entrada de Caidas -</B> Si se establece este campo, este grupo de entrada se utilizará para las llamadas salientes dentro de esta lista que caigan en la campaña de salida, a cambio del grupo de entrada para caidas establecido en la pantalla de detalle de la campaña. el valor por defecto es inactivo.

<BR>
<A NAME="lists-web_form_address">
<BR>
<B>Formulario Web Override -</B> Esta es la dirección personalizada que hacer clic en el botón de formulario web en la pantalla del agente le llevará a las llamadas que entran en esta lista. Si desea utilizar campos personalizados en una dirección de formulario web, es necesario agregar y CF_uses_custom_fields = Y como parte de su URL.

<BR>
<A NAME="lists-xferconf_a_dtmf">
<BR>
<B>Invalidar Número Xfer-Conf -</B> Estos cinco campos permiten invalidar la configuración de numeros de Transferencia de Conferencia cuando el contacto pertenece a esta lista. Por defecto está en blanco.

<BR>
<A NAME="lists-inventory_report">
<BR>
<B>Informe de Inventario -</B> Si el Informe de Inventario está habilitado en su sistema, esta opción determinará si esta lista está incluida en el informe o no. El valor por defecto es Y para Sí.

<BR>
<A NAME="lists-time_zone_setting">
<BR>
<B>Ajuste de Zona Horaria -</B> Esta opción le permite definir el método de mantenimiento de la búsqueda de zona horaria actual para de los contactos dentro de esta lista. Este proceso sólo se realiza por la noche de tal forma que los cambios que realice no serán inmediatos. COUNTRY_AND_AREA_CODE es el valor predeterminado, y utilizará el código de país y código de área del número de teléfono para determinar la zona horaria del contacto. Postal_code utilizará el código postal si está disponible, para determinar la zona horaria del contacto. NANPA_PREFIX sólo funciona en los EE.UU. y utilizará el código de área y el prefijo del número de teléfono para determinar la zona horaria del contacto, pero esto no está habilitado por defecto en el sistema, así que por favor asegúrese de tener los datos cargados en su sistema con el prefijo NANPA antes de seleccionar esta opción. OWNER_TIME_ZONE_CODE utilizará la abreviatura estandar de la zona horaria cargada en el campo propietario del contacto para determinar la zona horaria, casos en Estados Unidos son AST, EST, CST, MST, PST, el CCTA, HST. Esta característica debe ser habilitada por el administrador del sistema para que tenga efecto.


<BR>
<A NAME="internal_list-dnc">
<BR>
<B>Lista DNC Interna -</B> This Do Not Call list contains every lead that has been set to a status of DNC in the system. Through the LISTS - AGREGAR NÚMERO A LISTA DNC page you are able to manually add numbers to this list so that they will not be called by campaigns that use the internal DNC list. También existe la opción de añadir contactos a la lista DNC de campaña específica para esas campañas que les han. Si tiene la opción DNC activa en CÓDIGOÁREA entonces, usted también puede usar el entradas comodín de código de area como esta 201XXXXXXX para bloquear todas las llamadas a los códigos de área 201 cuando está activado.



<BR><BR><BR><BR>

<B><FONT SIZE=3>TABLA INBOUND_GROUPS</FONT></B><BR><BR>
<A NAME="inbound_groups-group_id">
<BR>
<B>ID de Grupo -</B> Define el nombre corto del grupo de entrada, el cuál no es editable después de la remisión inicial. No debe contener espacios y debe tener entre 2 y 20 caracteres de longitud..

<BR>
<A NAME="inbound_groups-group_name">
<BR>
<B>Nombre de Grupo -</B> Define la descripción del grupo, debe estar entre 2 y 30 caracteres de longitud. No puede incluir signos menos, signos más o espacios .

<BR>
<A NAME="inbound_groups-group_color">
<BR>
<B>Color de Grupo -</B> Este es el color que se muestra en la aplicación cliente agente cuando se recibe una llamada en este grupo. Debe estar entre 2 y 7 caracteres. Si se trata de una definición de color hexadecimal hay que acordarse de poner un # al principio de la cadena o de la pantalla del agente no funciona correctamente.

<BR>
<A NAME="inbound_groups-active">
<BR>
<B>Activo -</B> Esto determina si este grupo de entrada se encuentra disponible para recibir llamadas. Si esto se define como inactivo entonces el After Hours Acción se utilizará en todas las llamadas que entran en ella.

<BR>
<A NAME="inbound_groups-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativo para este grupo entrante, esto permite visualización administrativa de este grupo de entrada restringido por grupo de usuarios. El valor predeterminado es --ALL-- la cual permite a cualquier usuario de administración, la consulta de este grupo de entrada.

<BR>
<A NAME="inbound_groups-group_calldate">
<BR>
<B>Grupo-Entrada Fecha de Marcación -</B> Esta es la última fecha y hora en que una llamada fue dirigida a este grupo de entrada.

<BR>
<A NAME="inbound_groups-web_form_address">
<BR>
<B>Formulario Web -</B> Esta es la dirección personalizada que hacer clic en el botón de formulario web en la pantalla del agente le llevará a las llamadas que entran en este grupo. Si desea utilizar campos personalizados en una dirección de formulario web, es necesario agregar y CF_uses_custom_fields = Y como parte de su URL.

<BR>
<A NAME="inbound_groups-next_agent_call">
<BR>
<B>Llamar siguiente agente -</B> Esto determina qué agente recibe la siguiente llamada que está disponible:
 <BR> &nbsp; - random: pedidos por el valor actualizado al azar en la tabla live_agents
 <BR> &nbsp; - oldest_call_start: Ordenar por la última vez que un agente envió una llamada. El resultado es que los agentes recibirán aproximadamente el mismo número de llamadas globales.
 <BR> &nbsp; - oldest_call_finish: Ordenar por la última vez que un agente terminó una llamada. El agente AKA que ha esperado mas tiempo recibe la primera llamada.
 <BR> &nbsp; - oldest_inbound_call_start: ordenados por la última hora en que se envió a un agenta una llamada de entrada. El resultado es ordenamiento por agentes que han recibido el mismo numero de llamadas en general.
 <BR> &nbsp; - oldest_inbound_call_finish: oerdena por la última hora en que una agente de entrada terminó la llamada. También conocido como el agente de espera más larga recibe la primera llamada.
 <BR> &nbsp; - overall_user_level: órdenes del USER_NIVEL del agente tal como se define en la tabla a los usuarios una mayor USER_NIVEL recibirá más llamadas.
 <BR> &nbsp; - inbound_group_rank: Órdenamiento de fila dado al agente para el grupo de entrada específico. De mayor a menor.
 <BR> &nbsp; - campaign_rank: Ordenamiento por la puntuación dada al agente para la campaña. De Mayor a menor..
 <BR> &nbsp; - ingroup_grade_random: da una mayor probabilidad de recibir una llamada a los agentes de mayor grado por grupo de entrada.
 <BR> &nbsp; - campaign_grade_random: da una mayor probabilidad de recibir una llamada a los agentes de mayor categoría por campaña.
 <BR> &nbsp; - fewest_calls: Ordenamiento por el número de las llamadas recibidas por un agente para ese grupo de entrada específico. De menor a mayor.
 <BR> &nbsp; - fewest_calls_campaign: orders by the number of calls received by an agent for the campaign. Least calls first.
 <BR> &nbsp; - longest_wait_time: ordena considerando la cantidad de tiempo que el agente ha estado esperando activamente por una llamada.
 <BR> &nbsp; - ring_all: rings all available agents until one picks up the phone.
 <BR> NOTAS: Para ring_all, los agentes que utilizan los teléfonos que tengan habilitada la opción Agente en Reposo, tendrán sus teléfonos timbrando y el primero en responder recibirá la llamada y la información en la pantalla de agente. Dado que ring_all ignora el tiempo de espera y la categoría, y marcará a todos los agentes que se encuentra disponible en la cola, no se recomienda utilizar este método para colas largas. Cuando se utiliza ring_all, los agentes registrados con teléfonos qu tengan la opción de Agente en Reposo deshabilitada, utilizarán el panel de Llamadas en Cola y harán click en el enlace TAKE CALL para tomar las llamads en cola. El tiempo que el teléfono sonará para los agentes ring_all se ajustará a la configuración de Tiempo de Timbrado en Reposo, ó el tiempo de timbrado más corto de los teléfonos que serán marcados. Nosotros no recomendamos el uso de ring_all en colas de de alto volumen de marcación, o colas con muchos agentes. El método ring_all se destina a ser utilizado con sólo unos pocos agentes y en grupos de entrada de bajo volumen.
<BR>

<BR>
<A NAME="inbound_groups-on_hook_ring_time">
<BR>
<B>Tiempo de Timbrado en Reposo -</B> Esta opción sólo se utiliza para los agentes que se conectan con  teléfonos y que tienen la función de agente en reposo habilitada (agent-on-hook). Este es el número de segundos que cada intento de llamada al agente sonará hasta que el sistema tome una espera de un segundo antes de comienzar a sonar de nuevo. Este campo puede ser invalidado si los teléfonos del agente se establecen para poca duración de timbrado caso en el cual puede ser necesario evitar el envio de llamadas a un teléfono de correo de voz. El valor predeterminado es 15.

<BR>
<A NAME="inbound_groups-on_hook_cid">
<BR>
<B>CID en Reposo -</B> Esta opción sólo se utiliza para los agentes que se conectan con los teléfonos que tienen la caracteristica de agente-en-reposo habilitada. Este es el identificador de llamadas que se mostrará en sus teléfonos de agentes cuando las llamadas estan timbrando. GENERIC es un tipo de notificación genérica RINGAGENT00000000001. INGROUP mostrará sólo el grupo de entrada del cual viene la llamada. CUSTOMER_PHONE sólo mostrará el número de teléfono del cliente. CUSTOMER_PHONE_RINGAGENTE mostrará RINGAGENT_3125551212 con RINGAGENTE como parte de la CID con el número de teléfono del cliente. CUSTOMER_PHONE_INGROUP mostrará los primeros 10 caracteres del grupo de entrada seguido por el número de teléfono del cliente. Default is GENERIC.

<BR>
<A NAME="inbound_groups-queue_priority">
<BR>
<B>Queue Priority -</B> This setting is used to define the order in which the calls from this inbound group should be answered in relation to calls from other inbound groups.

<A NAME="inbound_groups-fronter_display">
<BR>
<B>Mostrar Nombre Público(Fronter) -</B> Este campo determina si el agente entrante tendría el nombre fronter - si lo hay - que se muestra en el campo Estado cuando la llamada viene al agente.

<BR>
<A NAME="inbound_groups-ingroup_script">
<BR>
<B>Script de campaña -</B> Este menú permite que usted elija el script que aparecerá en la pantalla de los agentes para esta campaña. Seleccione NONE para no presentar ningún Script para la campaña.

<BR>
<A NAME="inbound_groups-ignore_list_script_override">
<BR>
<B>Ignorar Invalidar Lista de Scripts-</B> Esta opción le permite hacer caso omiso de la opción de Invalidación de la lista de ID de Scripts para las llamadas que entren en este grupo de entrada. Si se establece en Y ignorará cualquier configuración de lista de ID de Scripts. El valor por defecto es N.

<BR>
<A NAME="inbound_groups-get_call_launch">
<BR>
<B>Desplegar al Llamar -</B> Este menú permite seleccionar si usted desee autodesplegar la página del formulario web en una ventana separada, autoconmutar con la pestaña de script o no haga nada cuando se envía una llamada al agente para esta campaña. Si los campos de lista personalizados están habilitados en su sistema, FORM abrirá la pestaña FORM despues producirse la conexión de una llamada a un agente.

<BR>
<A NAME="inbound_groups-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Estos cuatro campos de permitir que usted tenga dos conjuntos de transferencia de conferencias y presets DTMF. Cuando se carga la llamada o la campaña, la pantalla del agente mostrará dos botones del marco de transferencia de conferencias y rellenar automáticamente el número de línea y los campos Enviar DTMF cuando se pulsa. Si desea permitir transferencias de consulta, un fronter a una cerca, tiene el agente utiliza la casilla CONSULTIVO, que no funciona para las llamadas de consulta pantalla tercer agente fiesta. Para aquellos que sólo tienen el agente haga clic en el botón de Marcación con el Cliente. A continuación, el agente sólo puede dejar-3WAY-CALL y pasar a la siguiente llamada. Si desea permitir las transferencias ciegas de los clientes a un script AGI para el registro o un IVR, a continuación, coloque AXFER en el campo de número-to-dial. También puede especificar una extensión personalizada después de la AXFER, por ejemplo, si usted quiere hacer una llamada a un IVR especial que usted ha ajustado a 83.900 extensión que pondría AXFER83900 en el campo de número-to-dial.

<BR>
<A NAME="inbound_groups-timer_action">
<BR>
<B>Temporizador de Acción -</B> Esta característica le permite activar acciones después de una cierta cantidad de tiempo. Las opciones de marcación D1 y D2 lanzarán una llamada a los Números de Transferencia de Conferencia predefinidos y los enviará a la sesión de agente; Este se utilizado generalmente para aplicaciones sencillas de validación IVR, aplicaciones AGI o simplemente para reproducir un mensaje pregrabado. WEBFORM abrirá la dirección de formulario web. MESSAGE_ONLY simplemente mostrará el mensaje que está en el campo de abajo. Ninguna de ellas podrá deshabilitar esta característica y es el predeterminado. Esta configuración anula la Configuración de la Campaña. HANGUP colgará la llamada cuando el temporizador se activa, CALLMENU enviará la llamada al Menú de Marcación especificado en el campo de acción del temporizador de destino, EXTENSIÓN enviará la llamada a la extensión que se especifica en el campo de acción del temporizador de destino, IN_GROUP enviará la llamada al Grupo-Entrada especificado en el campo de acción del temporizador de destino.

<BR>
<A NAME="inbound_groups-timer_action_message">
<BR>
<B>Mensaje Acción de Temporizador -</B> Este es el mensaje que aparece en la pantalla del agente en el momento de ejecutar la acción del temporizador .

<BR>
<A NAME="inbound_groups-timer_action_seconds">
<BR>
<B>Segundos Accion de Temporizador -</B> Esta es la cantidad de tiempo después de que la llamada se conecta al clientes que se desencadena la acción del temporizador. El valor predeterminado es -1, el cuál es también inactivo.

<BR>
<A NAME="inbound_groups-timer_action_destination">
<BR>
<B>Destino Acción Temporizador -</B> Este campo es donde se especifica el menú de Marcación, Extensión o el grupo de entrada al que desea enviar la llamada está definida como  CALLMENU, EXTENSION o IN_GROUP. El valor por defecto es vacío.

<BR>
<A NAME="inbound_groups-drop_call_seconds">
<BR>
<B>Tiempo Caida de Llamada - </B> Tiempo en segundos que una llamada se quedará en la cola antes de ser considerada una CAIDA.

<BR>
<A NAME="inbound_groups-drop_action">
<BR>
<B>Acción post Caidas - </B> Este menú le permite elegir lo que le sucede a una llamada cuando se ha estado esperando durante más de lo establecido en el campo Segundos Caida de Llamada. HANGUP simplemente cuelga la llamada, MESSAGE envia el mensaje de llamar a la Extensión de Caída que se define a continuación, VOICEMAIL envía la llamada al buzón de voz se define a continuación, IN_GROUP enviará la llamada a la Grupo de Entrada que se define a continuación. VMAIL_NO_INST enviará la llamada al buzón de voz que ha definido a continuación y no jugará instrucciones después del mensaje de correo de voz.

<BR>
<A NAME="inbound_groups-drop_exten">
<BR>
<B>Extensión de Caída- </B> Si se establece la acción post caída a MESSAGE, esta es la extensión del plan de marcación que debe ser alcanzada si cumple el tiempo establecido para la Caida de la llamada.. Para los grupos de entrada AGENTDIRECT, si el usuario no está disponible, usted puede poner AGENTEXT en este campo y el sistema buscará el campo 5 personalizada y enviará la llamada a ese número del plan de marcación.

<BR>
<A NAME="inbound_groups-voicemail_ext">
<BR>
<B>Buzón de Voz -</B> If Drop Action is set to VOICEMAIL, the call DROP would instead be directed to this voicemail box to hear and leave a message. En un grupo de entrada AGENTDIRECT, establecer esto como AGENTVMAIL seleccionará el ID de buzón de voz del usuario a utilizar.

<BR>
<A NAME="inbound_groups-drop_inbound_group">
<BR>
<B>Grupo de Transferencia de Caidas - </B> En caso de acción de caída se establece el Grupo de entrada al cuál la llamada será enviada si cumple el tiempo establecido para la Caida de la llamada.

<BR>
<A NAME="inbound_groups-drop_callmenu">
<BR>
<B>Caidas Menú de Marcación -</B> Si la Acción de Caida se establece como CALLMENU, la llamada será enviada a este menú de marcación tras alcanzar los segundos de caída de llamadas.

<BR>
<A NAME="inbound_groups-action_xfer_cid">
<BR>
<B>Acción Transferencia CID -</B> Se utiliza para la gota, Abierto por la noche y acciones No-agente-no-cola. Este es el número de identificación de llamadas que la llamada utiliza antes de ser transferida a los menús de las extensiones, mensajes, correo de voz o llamadas. Usted puede utilizar CLIENTE en este campo para utilizar el número de teléfono del cliente, o la campaña de utilizar el número de identificación de llamadas de campaña primero permitida. El valor predeterminado es CLIENTE. Si se trata de una llamada que se van a un menú de llamada y luego de vuelta a un en-grupo, le sugerimos que utilice CUSTOMERCLOSER en este campo, y también tenemos que establecer el método de Mango En-Group en el menú de Llamada a CLOSER.

<BR>
<A NAME="inbound_groups-call_time_id">
<BR>
<B>Horario de Marcación -</B> Éste es el esquema del horarios de marcación a utilizar para este grupo de entrada. Tenga presente que el horario está basado en la hora del servidor. El valor por defecto es 24 horas.

<BR>
<A NAME="inbound_groups-after_hours_action">
<BR>
<B>Acción post Horario- </B> Acción a ejecutar si sobrepasa el horario definido en el horario de marcación para este grupo de entrada. HANGUP cuelga inmediatamente la llamada, MESSASGE  reproduce el archivo fefinido en el campo Archivo Mensaje Post Horario, EXTENSIÓN enviará la llamada a la extensión de Post horario definida en el plan de marcación y VOICEMAIL envía la llamada al buzón de voz  definido en el campo Buzon de Voz Post Horario; IN_GROUP enviará la llamada al grupo de entrada seleccionado en la lista de Grupo de Transferencia post horario. El valor predeterminado es MESSAGE. VMAIL_NO_INST enviará la llamada al buzón de voz que ha definido a continuación y no jugará instrucciones después del mensaje de correo de voz.

<BR>
<A NAME="inbound_groups-after_hours_message_filename">
<BR>
<B>Archivo Mensaje Post-Horario -</B> Establece el archivo de audio localizado en el servidor que se reproducirá si la actividad se establece en MESSAGE. El valor por defecto es vm-goodbye

<BR>
<A NAME="inbound_groups-after_hours_exten">
<BR>
<B>Extensión Post-Horario -</B> Define la extensión del plan de marcación que a la cual se enviará la llamada si la Actividad se establece como EXTENSION. El valor por defecto es 8300. Para los grupos de entrada AGENTDIRECT, se puede poner AGENTEXT en este campo y el sistema buscará el campo 5 personalizado y enviará la llamada a ese número del plan de marcación.

<BR>
<A NAME="inbound_groups-after_hours_voicemail">
<BR>
<B>Correo de Voz Post-Horario -</B> Define el Buzón de correo de vos para enviar la llamada si la actividad se establece como VOICEMAIL. En un grupo de entrada AGENTDIRECT, establecer esto como AGENTVMAIL seleccionará el ID de buzón de voz del usuario a utilizar.

<BR>
<A NAME="inbound_groups-afterhours_xfer_group">
<BR>
<B>Grupo Tranferencia Post Horario- </B> Si se define la Acción post-horario en IN_GROUP, la llamada será enviada a este grupo de entrada si esta entra en el grupo fuera del esquema de horarios de marcación definido para este grupo.

<BR>
<A NAME="inbound_groups-after_hours_callmenu">
<BR>
<B>Menu Marcación Post-Horario -</B> Si se establece la Acción Post-Horario como CALLMENU, la llamada será enviada a este Menú de Marcación si entra en el grupo de entrada cuando se encuentra fuera de su esquema horario.

<BR>
<A NAME="inbound_groups-no_agent_no_queue">
<BR>
<B>Sin Agentes No Llenar Cola -</B> Si este campo está establecido en Y o NO_PAUSED entonces ninguna lamada será puesta en cola para este grupo de entrada si no hay agentes registrados y las llamadas iran a la Acción  Sin Agentes Sin Cola. La opción NO_PAUSED tampoco enviará las llamadas entrantes a la cola si hay solo agentes en pausa en el grupo de entrada. El valor por defecto es n. En un grupo de entrada AGENTDIRECT, establecer esto como AGENTVMAIL seleccionará el ID de buzón de voz del usuario a utilizar. También puede poner AGENTEXT en este campo si se ha establecido en EXTENSION, y el sistema buscará el campo 5 personalizado y enviará la llamada a ese número del plan de marcación. Si se establece en N, las llamadas serán cola, incluso si no hay agentes conectados y ajustados a recibir llamadas de este in-group.

<BR>
<A NAME="inbound_groups-no_agent_action">
<BR>
<B>Acción sin Agentes Sin Cola -</B> Si la acción Sin Agnte Sin Cola está activada,  entonces este campo define el destino de la llamada si no hay agentes en el grupo de entrada. El valor por defecto es MESSAGE, el cual reproduce el archivo de audio definido en el campo valor de la acción y luego cuelga..

<BR>
<A NAME="inbound_groups-no_agent_action_value">
<BR>
<B>Valor Sin Agentes Sin Cola -</B> Este es el valor para la acción anterior. El valor predeterminado es nbdy-avail-to-take-call|vm-goodbye.

<BR>
<A NAME="inbound_groups-max_calls_method">
<BR>
<B>Método de Max Marcaciones -</B> Esta opción puede activar la característica de máximas llamadas simultáneas para esta grupo de entrada. Si se establece a TOTAL, entonces el número total de llamadas que son manejados por los agentes y en la cola en este grupo de entrada no podrá exceder el máximo número de lineas del Contador de Marcaciones definidas a continuación. Si se establece en IN_QUEUE, entonces, el número de llamadas en cola de espera para los agentes no podrán exceder el Contador de Llamadas Máximo sin importar cuántas llamadas están con agentes para este grupo de entrada. El valor predeterminado es DISABLED.

<BR>
<A NAME="inbound_groups-max_calls_count">
<BR>
<B>Contador Marcaciones Maximas -</B> Esta opción debe ser mayor que 0 si desea utilizar la característica Método de Marcaciones Máxima. El valor predeterminado es 0.

<BR>
<A NAME="inbound_groups-max_calls_action">
<BR>
<B>Acción Marcaciones Máximas -</B> Esta es la acción a tomar si el Método de Marcaciones Máxima es activado y el número de llamadas excede lo establecido anteriormente en la configuración de Contador de Llamadas Maximo. Las llamadas por encima de esa cantidad serán enviado a la Acción de Caida DROP, la acción post horario AFTERHOURS, o la acción SinAgente-SinCola  NO_AGENT_NO_QUEUE y se registrará como un estado MAXCAL con una motivo para colgar MAXCALLS. El valor predeterminado es NO_AGENT_NO_QUEUE.

<BR>
<A NAME="inbound_groups-welcome_message_filename">
<BR>
<B>Archivo Mensaje Bienvenida -</B> Define el archivo de audio situado en el servidor que se reproducirá cuando entra una llamada. Si se establece  ---NONE --- entonces no se reproduce ningún mensaje. El valor por defecto es---NONE ---. Este campo como con los otros campos de audio en Grupos-Entrada, con la excepción del nombre de archivo de alerta de agente, puede tener múltiples archivos de audio para reproducir si usted coloca en el campo, como separador de listas de archivos de audio, la barra vertical (pipe).

<BR>
<A NAME="inbound_groups-play_welcome_message">
<BR>
<B> Reproducir Mensaje de Bienvenida - </B> Esta configuración selecciona cuando ejecutar el mensaje de bienvenida. ALWAYS reproducirá todas la veces, NUNCA nunca lo ejecutará, IF_WAIT_ONLY sólo reproducirá el mensaje de bienvenida si la llamada no va inmediatamente a un agente , y YES_UNLESS_NODELAY siempre reproducira el mensaje de bienvenida a menos que se active la opción NO_DELAY. El valor por defecto es SIEMPRE.

<BR>
<A NAME="inbound_groups-moh_context">
<BR>
<B>Contexto Música de Espera -</B> Define el contexto de música en espera a utilizar cuando se pone en espera la llamada. El valor por defecto es default.

<BR>
<A NAME="inbound_groups-onhold_prompt_filename">
<BR>
<B>Nombre Archivo Aviso de Espera -</B> Define el archivo de audio situado en el servidor que se reproducirá periodicamente cuando el cliente está en espera. El valor por defecto es generic_hold. Este archivo de audio DEBE tener 9 segundos o menos.

<BR>
<A NAME="inbound_groups-prompt_interval">
<BR>
<B>Intervalo Aviso de Espera -</B> Tiempo en segundos de espera antes de reproducir el aviso de espera. El valor por defecto es 60. Para desactivar la espera, definir el intervalo a 0.

<BR>
<A NAME="inbound_groups-onhold_prompt_no_block">
<BR>
<B>Espera Entrada Sin Bloque  -</B> Al establecer esta opción a Y permitirá que las llamadas en línea detrás de una llamada en espera, en el momento en que el sistema está reproduciendo el mensaje de espera, vayan a un agente si alguno está disponible durante la reproducción del mensaje. Mientras el mensaje del Archivo de Espera es reproducido a un cliente, este no puede ser enviado a un agente. El valor por defecto es N.

<BR>
<A NAME="inbound_groups-onhold_prompt_seconds">
<BR>
<B>Segundos Entrar en Espera -</B> Este campo debe ser establecido al número de segundos en que el Archivo de Espera será reproducido. El valor predeterminado es 10.

<BR>
<A NAME="inbound_groups-play_place_in_line">
<BR>
<B>Reproducir Posición en línea - </B> Este define si la persona que llama escuchará su posición en la línea cuando entran en la cola, así como cuando escuchan el anuncio. El valor predeterminado es N.

<BR>
<A NAME="inbound_groups-play_estimate_hold_time">
<BR>
<B>Reproducir Tiempo de espera Estimado - </B> Este define si la persona que llama escuchará el tiempo estimado de espera antes de ser transferido a un agente. El valor predeterminado es N. Si el cliente está en espera y escucha este mensaje de tiempo de espera estimado, el tiempo mínimo que se reproducirá es de 15 segundos.

<BR>
<A NAME="inbound_groups-calculate_estimated_hold_seconds">
<BR>
<B>Calcular Espera Estimada (seg) -</B> Esto define el número de segundos que el cliente va a esperar en la cola, antes de que el tiempo de espera estimado sea calculado y opcionalmente reproducido. Mínima 3 segundos, incluso si se ajusta por debajo de 3. El valor predeterminado es 0.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_filename">
<BR>
<B>Archivo de Tiempo Mínimo Estimado -</B> Si el tiempo de espera mínimo estimado se establece como activo y es calculado igual o inferior a 15 segundos, entonces este abrirá el archivo que será reproducido en lugar del anuncio por defecto. El valor predeterminado es Empty para inactivo.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_no_block">
<BR>
<B>Tiempo Espera Mínnimo Estimado Selección Sin Bloque -</B> Si Tiempo estimado de espera está activo y el campo de Archivo de Tiempo de Espera Mínimo Estimado es rellenado, entonces esta opción permitirá que las llamadas en fila detrás de la llamada en la cual se está reproduciendo irán a un agente, si alguno está disponible, mientras el mensaje se está reproduciendo. Mientras el sistema esté reproduciendo el mensaje a un cliente, este no puede ser enviado a un agente. El valor predeterminado es N.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_seconds">
<BR>
<B>Ejecución de Tiempo Mínimo de Espera Estimado (seg) -</B> Este campo debe tener registrado el número de segundos en que El Archivo de Tiempo Mínimo de Espera Estimado será ejecutado. El valor predeterminado es 10.

<BR>
<A NAME="inbound_groups-wait_time_option">
<BR>
<B>Tiempo Espera Opcional -</B> Esto le permite ofrecer a los clientes opciones para salir de la cola si su tiempo de espera supera la cantidad de segundos especificada a continuación. El valor predeterminado es NONE. Si una de las opciones PRESS_ está seleccionada, se reproducirá el nombre de archivo definido a continuación y dará al cliente la opción de pulsar 1 en su teléfono para salir de la cola y ejecutar la opción seleccionada. La opción PRESS_STAY enviará al cliente al final de la cola sin perder su lugar en la fila.

<BR>
<A NAME="inbound_groups-wait_time_second_option">
<BR>
<B>Segundo Tiempo Espera Opcional -</B> Igual que la primera espera opcional definida anteriormente, excepto que éste comprobará si el cliente pulsa el botón 2. El valor predeterminado es NONE. Si no se selecciona la primera opción de tiempo opcional de espera entonces esta opción no se presentará.

<BR>
<A NAME="inbound_groups-wait_time_third_option">
<BR>
<B>Tercer Tiempo Espera Opcional -</B> Igual que el primero, pero éste comprobará si el cliente pulsa la tecla 3. El valor predeterminado es NONE. Si no se selecciona la segunda opción de tiempo opcional de espera entonces esta opción no se presentará.

<BR>
<A NAME="inbound_groups-wait_time_option_seconds">
<BR>
<B>tiempo Espera Opcional (seg) -</B> Si la opción Tiempo de espera Opcional tiene establecido algún valor distinto a NONE, este es el número de segundos que el cliente ha estado esperando en la cola que activará las opciones de tiempo de espera. Por defecto es 120 segundos.

<BR>
<A NAME="inbound_groups-wait_time_option_exten">
<BR>
<B>Extensión de Tiempo Espera Opcional -</B> Si la opción Tiempo de espera se establece en PRESS_EXTEN, esta es la extensión de plan de marcación al que la llamada se enviará si el cliente pulsa la tecla de opción cuando se le presenta la opción. Para los grupos de entrada AGENTDIRECT, se puede poner AGENTEXT en este campo y el sistema buscará el campo 5 personalizado y enviará la llamada a ese número del plan de marcación.

<BR>
<A NAME="inbound_groups-wait_time_option_callmenu">
<BR>
<B>Menú de Marcación Tiempo Espera Opcional -</B> Si la opción Tiempo de espera opcional se establece en PRESS_CALLMENU, este es el menú de marcación al que la llamada será enviada si el cliente pulsa la tecla de opción cuando se le presenta la opción.

<BR>
<A NAME="inbound_groups-wait_time_option_voicemail">
<BR>
<B>Buzon de Boz Tiempo Opcional de Espera -</B> Si la opción Tiempo de espera Opcional se establece en PRESS_VMAIL, este es el buzón de voz al que la llamada se enviará si el cliente pulsa la tecla de opción cuando se le presenta la opción. En un grupo de entrada AGENTDIRECT, configurado este como AGENTVMAIL se seleccionará el ID de buzón de voz del usuario para el reenvío.

<BR>
<A NAME="inbound_groups-wait_time_option_xfer_group">
<BR>
<B>Grupo-Entrada de Transfer Tiempo Espera Opcional -</B> Si la opción Tiempo de espera Opcional se establece en PRESS_INGROUP, este es el grupo de entrada al que la llamada se enviará si el cliente selecciona esta opción cuando se le presenta.

<BR>
<A NAME="inbound_groups-wait_time_option_press_filename">
<BR>
<B>Archivo Selección Tiempo Espera Opcional -</B> Si la opción Tiempo Espera Opcional se establece en una de los modos PRESS_, este es el símbolo de nombre de archivo que se reproduce, si el tiempo de espera de clientes que supera el tiempo de espera para dar al cliente la opción de pulsar 1, 2 o 3 en su teléfono para ejecutar las opciones de espera. Es muy importante que incluya las opciones en el archivo de audio para todas las opciones de espera, y que la longitud del archivo de audio en segundos esté bien definida en el siguente campo Segundos de Archivo o habrá problemas. El valor predeterminado es to-be-called-back.

<BR>
<A NAME="inbound_groups-wait_time_option_no_block">
<BR>
<B>Tiempo Espera Opciónal Selección Sin Bloque -</B> Al establecer esta opción a Y permitirá que las llamadas en fila detrás de una llamada cuando se está ejecutando el Archivo de Tiempo de Espera Opcional, podran ser enviadas a un agente si está disponible, mientras que el mensaje es reproducido. Mientras el mensaje se este reproducientdo a un cliente, este no puede ser enviado a un agente. El valor predeterminado es N.

<BR>
<A NAME="inbound_groups-wait_time_option_prompt_seconds">
<BR>
<B>Tiempo Espera Opcional Tiempo Reproducción Archivo -</B> Este campo establece el número de segundos que el mensaje de tiempo de espera opcional será ejecutado. El valor predeterminado es 10.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_filename">
<BR>
<B>Tiempo Espera Opcional Archivo post Selección -</B> Si la opción Tiempo de Espera Opcional se establece en una de las opciones PRESS_, esta es la entrada de archivo que ejecuta después de que el cliente ha presionado 1, 2 o 3.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_list_id">
<BR>
<B>ID Lista Remarcación Tiempo Espera Opcional -</B> Si la opción Tiempo de Espera Opcional se establece en PRESS_CID_CALLBACK, este es el ID de la lista de la llamada que se añade como nuevo prospecto, si el cliente selecciona esta opción cuando se le presenta.

<BR>
<A NAME="inbound_groups-wait_hold_option_priority">
<BR>
<B>Prioridad Espera Opcional -</B> Si las opciones Tiempo de Espera Estimado y  Tiempo Opcional de Espera están activas, esta configuración definirá si una, otra o las dos funciones están activas. Por ejemplo, si la opción de Tiempo De Espera Estimado se establece en 360, la opción Tiempo Opcional está establecida en 120, y el cliente ha estado esperando durante 120 segundos existiendo aún 400 segundos en el Tiempo de Espera Estimado, entonces las dos opciones están activos simultaneamente, y por tanto este valor será verificado para ver qué opciones se ofrecen. El valor predeterminado es unicamente WAIT.

<BR>
<A NAME="inbound_groups-hold_time_option">
<BR>
<B>Estimated Opción tiempo de Espera  -</B> Esto le permite especificar la ruta de la llamada si el tiempo de espera estimado es superior a la cantidad de segundos que se especifican a continuación. El valor predeterminado es NINGUNA. Si una de las opciones PRESS_ está seleccionada, se reproducirá el nombre Archivo de Selección definido más adelante y dara al cliente la opción de pulsar 1 en su teléfono para salir de la cola y ejecutar la opción seleccionada.

<BR>
<A NAME="inbound_groups-hold_time_second_option">
<BR>
<B>Segunda Opción de Espera -</B> Igual que el primer campo Opción Tiempo de Espera, excepto que este comprobará si el cliente pulsa el botón 2. El valor predeterminado es NONE. Si no se selecciona la primera Opción Tiempo de Espera entonces esta opción no sera presentada.

<BR>
<A NAME="inbound_groups-hold_time_third_option">
<BR>
<B>Tercera Opción de Espera -</B> Igual que la primera Opción de Espera, pero éste comprobará si el cliente pulsa la tecla 3. El valor predeterminado es NONE. Si no se selecciona la segunda Opción Tiempo de Espera entonces esta opción no sera presentada.

<BR>
<A NAME="inbound_groups-hold_time_option_seconds">
<BR>
<B>Opción Tiempo de Espera (seg) - </B> Si en la opción de tiempo de se establece una opción distinta de NONE, este es el tiempo en segundos del tiempo de espera estimado que activará la opción de tiempo de espera. El valor por defecto es 360 segundos.

<BR>
<A NAME="inbound_groups-hold_time_option_minimum">
<BR>
<B>Mínimo para Opción Tiempo Espera -</B> Si la opción Opción Tiempo Espera se habilita, este es el número mínimo de segundos que la llamada debe esperar antes de que sea presentada la Opción Tiempo de Espera. La opción de tiempo de espera será presentada de forma inmediata sí el tiempo de espera estimado es mayor que el valor en segundos  que la Opción Tiempo de Espera. El valor predeterminado es 0 segundos.

<BR>
<A NAME="inbound_groups-hold_time_option_exten">
<BR>
<B>Extensión de Opción Tiempo de Espera - </B> Si la Opción de tiempo de Espera se establece como EXTENSION, esta es la extensión del plan de marcación a la que se enviará la llamada si el tiempo de espera estimado excede el tiempo definido en la Opcion de Tiempo de Espera. Para los grupos de entrada AGENTDIRECT, se puede poner AGENTEXT en este campo y el sistema buscará el campo 5 personalizado y enviará la llamada a ese número del plan de marcación.

<BR>
<A NAME="inbound_groups-hold_time_option_callmenu">
<BR>
<B>Menu Marcación Opción Tiempo Espera -</B> Si Opción Tiempo de Espera está establecida como CALL_MENU, este es el menú de marcación al que la llamada se enviará si el tiempo de espera estimada supera los segundos de Opción Tiempo de Espera.

<BR>
<A NAME="inbound_groups-hold_time_option_voicemail">
<BR>
<B>Buzon de Voz Opción Tiempo de Espera - </B> Si la Opción de Tiempo de espera se establece como  VOICEMAIL, este sará el buzón de voz que se enviará si el tiempo de espera estimado excede el tiempo definido en la Opcion de Tiempo de Espera. En un grupo de entrada AGENTDIRECT, establecer esto como AGENTVMAIL seleccionará el ID de buzón de voz del usuario a utilizar.

<BR>
<A NAME="inbound_groups-hold_time_option_xfer_group">
<BR>
<B>Transferir Grupo-Entrada de Opción Tiempo de Espera  - </B> Si la Opción tiempo de Espera se establece como IN_GROUP, este será el grupo de entrada al que la llamada se enviará si el tiempo de espera estimado excede el tiempo definido en la Opcion de Tiempo de Espera.

<BR>
<A NAME="inbound_groups-hold_time_option_press_filename">
<BR>
<B>Archivo presionar Opción Tiempo Espera -</B> Si Opción Tiempo de Espera está establecido en una de las opciones PRESS_, esta es la estrada al archivo que se reproduce, si el tiempo de espera estimado excede el tiempo definido en Opción de Tiempo de Espera para dar al cliente la opción de pulsar 1 en su teléfono para ejecutar la Opción de Tiempo de Espera Pulsada. Es muy importante que este archivo de audio sea de 10 segundos o menos, o habrá problemas. El valor predeterminado es to-be-called-back.

<BR>
<A NAME="inbound_groups-hold_time_option_no_block">
<BR>
<B>Espera Opción Selección sin Bloque -</B> Al establecer esta opción a Y permitirá que las llamadas en fila detrás de una llamada donde el Archivo de espera se está reproduciendo vayan a un agente si alguno está disponible, mientras que el mensaje es reproducido. Mientras el mensaje de Opción de presionar es reproducido a un cliente, este no puede ser enviado a un agente. Por defecto es N.

<BR>
<A NAME="inbound_groups-hold_time_option_prompt_seconds">
<BR>
<B>Espera Opción Archivo de Selección (seg) -</B> En este campo se establece en el número de segundos que El Archivo de Opciones de Espera será reproducido. El valor predeterminado es 10.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_filename">
<BR>
<B>Archivo post Selección de Opción Tiempo de Espera -</B> Si Opción Tiempo de Espera está establecido en una de las opciones PRESS_ o CALLERID_CALLBACK, este es el archivo que se reproduce después de que el cliente haya presionado 1 o la llamada ha sido añadida a la lista de remarcación.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_list_id">
<BR>
<B>ID Lista Remarcación de Opción Tiempo de Espera - </B> Si el tiempo de la opción está establecida en CALLERID_CALLBACK, esta es el ID de lista con el que la llamada será añadida como nuevo, si el tiempo de espera estimado es superior a la Opción Tiempo de Espera.

<BR>
<A NAME="inbound_groups-agent_alert_exten">
<BR>
<B>Agente Archivo de Alerta -</B> Archivo de audio para reproducir a un agente para anunciar que una llamada está entrando al agente. Para no utilizar esta función establezca este valor a X. El valor por defecto es ding.

<BR>
<A NAME="inbound_groups-agent_alert_delay">
<BR>
<B>Retardo Alerta de Agente -</B> Retardo en milisegundos antes de enviar la llamada al agente después de reproducir la Extensión Alerta del agente. El valor por defecto es 1000.

<BR>
<A NAME="inbound_groups-default_xfer_group">
<BR>
<B>Grupo de transferencia por defecto -</B> Este campo define el grupo de entrada por defecto que será seleccionado automáticamente cuando el agente se dirige al entorno de Trasferencia-conferencia en su interfas de agente.

<BR>
<A NAME="inbound_groups-ingroup_recording_override">
<BR>
<B>In-Group Recording Override -</B> Este campo permite la primordial de la configuración de grabación de llamadas de campaña. Esta configuración se puede anular con la configuración de invalidación de grabación de usuario. DISABLED no anulará el ajuste de grabación campaña. NUNCA será desactivar la grabación en el cliente. ONDEMAND es el valor predeterminado y permite que el agente para iniciar y detener la grabación, según sea necesario. ALLCALLS comenzará a grabar en el cliente cada vez que se envía una llamada a un agente. ALLFORCE empezará a grabar en el cliente cada vez que se envía una llamada a un agente sin dar ninguna opción para detener la grabación del agente.

<BR>
<A NAME="inbound_groups-ingroup_rec_filename">
<BR>
<B>In-Group Recording Filename -</B> Este campo anulará el esquema de nombres de archivo de grabación de campaña a menos que se  ajuste a NONE. Las variables permitidas son CAMPAIGN INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH AGENTE VENDORLEADCODE LEADID CALLID. El valor predeterminado es FULLDATE_AGENTE y se vería así 20051020-103108_6666. Otro ejemplo es CAMPAIGN_TINYDATE_CUSTPHONE que se vería así TESTCAMP_51020103108_3125551212. el nombre  resultante del archivo debe ser inferior a 90 caracteres de longitud. Default is NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="inbound_groups-qc_enabled">
	<BR>
	<B>CC Activado - </B> Establecer este campo como verdadero (Y) permite activar la configuración al agente de control de calidad. El valor predeterminado es N.

	<BR>
	<A NAME="inbound_groups-qc_statuses">
	<BR>
	<B>CC Estados - </B> Esta zona es donde se selecciona el estados en que los contactos deben estar para ser enviador a revisión por control de calidad. Coloque una marca de verificación junto al estado que desea que CC revise.. 

	<BR>
	<A NAME="inbound_groups-qc_shift_id">
	<BR>
	<B>CC Turno - </B> Este es el marco de tiempo utilizado para obtener registros de CC para un grupo de entrada. Los días de la semana no se tienen en cuenta para estas funciones.

	<BR>
	<A NAME="inbound_groups-qc_get_record_launch">
	<BR>
	<B>CC Obtener Reproducción de Grabación-</B> Esto permite que una de las siguientes acciones sea activada por un agente de control de calidad recibiendo una nueva grabación.

	<BR>
	<A NAME="inbound_groups-qc_show_recording">
	<BR>
	<B>CC Mostrar grabación - </B>, Permite que una grabación pueda vincularse con el registro de control de calidad que se mostrará en pantalla el agente de control de calidad.

	<BR>
	<A NAME="inbound_groups-qc_web_form_address">
	<BR>
	<B>CC Dirección Formulario Web- </B> Esta es la dirección del sitio web en la cual un agente de control de calidad puede alcanzar tras hacer click en el enlace WEBFORM en la pantalla de control de calidad.

	<BR>
	<A NAME="inbound_groups-qc_script">
	<BR>
	<B>CC Escritura - </B> Este es el script que puede ser utilizado por agentes de control de calidad en pestaña SCRIPT en el tablero de control de calidad.
	<?php
	}
?>

<BR>
<A NAME="inbound_groups-hold_recall_xfer_group">
<BR>
<B>Grupo de Transferencia Remarcación Tiempo de Espera -</B> Si un cliente marca a este grupo de entrada mas de una vez, y esta opción no está definida como NONE, entonces la llamada automaticamente será enviada al Grupo de Entrada al grupo de entrada establecido en este campo. El valor por defecto es NONE.

<BR>
<A NAME="inbound_groups-no_delay_call_route">
<BR>
<B>N Retraso de llamada Ruta - </B> Establecer este valor como verdadero (Y) eliminará todos los tiempos de espera y anuncios de audio y tratara de enviar la llamada directo a un agente. No anula el mensaje de bienvenida o la configuración de anuncios de espera. El valor por defecto es N.

<BR>
<A NAME="inbound_groups-answer_sec_pct_rt_stat_one">
<BR>
<B>Porcentaje Estadístico de llamadas respondidas dentro de X segundos - </B> Este campo le permite ajustar el número de segundos en que las estadísticas de tiempo real utilizarán para calcular el porcentaje de llamadas que contestadas dentro de X número de segundos en espera.

<BR>
<A NAME="inbound_groups-start_call_url">
<BR>
<B>URL Inicio Llamada -</B> This web URL address is not seen by the agent, but it is called every time a call is sent to an agent if it is populated. Uses the same variables as the web form fields and scripts. Default is blank.

<BR>
<A NAME="inbound_groups-dispo_call_url">
<BR>
<B>URL disposicion de Llamada -</B> This web URL address is not seen by the agent, but it is called every time a call is dispositioned by an agent if it is populated. Uses the same variables as the web form fields and scripts. dispo and talk_time are the variables you can use to retrieve the agent-defined disposition for the call and the actual talk time en segundos of the call. Default is blank.

<BR>
<A NAME="inbound_groups-add_lead_url">
<BR>
<B>Agregar URL de Contacto -</B> Esta dirección URL no es vista por el agente, sino que es llamado cada vez que un contacto se agrega al sistema a través del proceso de entrada. El valor predeterminado es en blanco. Usted debe comenzar esta URL con VAR si desea utilizar las variables, y por supuesto --A-- y --B-- en torno a la variable actual en la dirección URL que desea utilizar. Aquí está la lista de variables que están disponibles para esta función. lead_id, vendor_lead_code, list_id, phone_number, phone_code, did_id, did_extension, did_pattern, did_description, uniqueid

<BR>
<A NAME="inbound_groups-na_call_url">
<BR>
<B>URL Contacto Sin Agente  -</B> Esta dirección web URL no es vista por el agente, pero si está rellena se llama cada vez que se una llamada no es atendida por un agente, colgada o transferida. Utiliza las mismas variables que los campos de formularios web y scripts. Disposición puede utilizarse para recuperar la disposición definida por el sistema para la llamada. Esta URL no puede ser una ruta relativa. El valor predeterminado es en blanco.

<BR>
<A NAME="inbound_groups-default_group_alias">
<BR>
<B>Alias de Grupo Predeterminado  -</B> If you have allowed Group Aliases for the campaign that the agent is logged into then this is the group alias that is selected first by default on a call coming in from this inbound group when the agent chooses to use a Group Alias for an outbound manual call. Default is NONE or empty.

<BR>
<A NAME="inbound_groups-dial_ingroup_cid">
<BR>
<B>Dial CID Grupo-Entrada -</B> Si la campaña del agente permite Marcacion de GRupo de Entrada Manual , este ID de Llamadas se enviará cmo el CID de llamada saliente si se rellena, invalidando la configuración de la campaña y la configuración de la lista de invalidación de CID. Por defecto está vacía.

<BR>
<A NAME="inbound_groups-extension_appended_cidname">
<BR>
<B>CID Anexar Extensión -</B> Si está activado, entonces las llamadas recibidas por este grupo de entrada tendrán un espacio y la extensión telefónica del agente anexa al final del nombre de ID de Llamadas para la llamada antes de ser enviada al agente. el valor por defecto es n para desactivado.

<BR>
<A NAME="inbound_groups-uniqueid_status_display">
<BR>
<B>UniqueId Pantalla de estado  -</B> Si está habilitado, cuando un agente recibe una llamada a través de este in-group verán al uniqueid de la llamada añade a la línea de estado en su interfaz del agente. La opción de prefijo se añada el prefijo, se define a continuación, para el comienzo de la UniqueID en la pantalla. Por defecto está desactivada. Si ya había una UniqueID definido en una llamada que entra en este grupo, a continuación, se mostrará la uniqueid originales. Si la opción PRESERVAR se utiliza y la llamada se envía a un segundo agente, el uniqueid y el prefijo que se muestra al primer agente también se mostrará al segundo agente.

<BR>
<A NAME="inbound_groups-uniqueid_status_prefix">
<BR>
<B>UniqueId Prefijo de Estado -</B> Si PREFIX es la opción seleccionada anterirmente entonces, este es el valor de ese prefijo. El valor por defecto es vacío.




<BR><BR><BR><BR>

<B><FONT SIZE=3>INBOUND_DIDS TABLA</FONT></B><BR><BR>
<BR>
<A NAME="inbound_dids-did_pattern">
<BR>
<B>Extensión DID - </B> Este es el número, extensión o DID que activará esta entrada y que usted enrutará en el sistema utilizando esta función. Hay un DID reservado por defecto que puede utilizar el cual es simplemente la palabra -default-, sin los guiones, que le permitirá el envío de cualquier llamada que no coincida con cualquier otro de los modelos existentes por defecto DID.

<BR>
<A NAME="inbound_dids-did_description">
<BR>
<B>Descripción DID - </B> Esta es la descripción de la entrada de entrutamiento DID.

<BR>
<A NAME="inbound_dids-did_active">
<BR>
<B>Activar DID - </B> En este campo se establece si la entrada DID está activa o no. El valor por defecto es Y.

<BR>
<A NAME="inbound_dids-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos de este DID, permitiendo la visualización administrativa de este DID restringido por grupo de usuarios. El valor predeterminado es --ALL-- el cuál permite a cualquier usuario de administración, la consulta de este DID.

<BR>
<A NAME="inbound_dids-did_route">
<BR>
<B>DID Route -</B> Este el tipo de ruta que establezca el DID de usar. EXTEN enviará las llamadas a la extensión entró a continuación, VOICEMAIL enviará las llamadas directamente al buzón de voz introducida a continuación, AGENTE enviará las llamadas a un agente si has iniciado sesión, teléfono enviará la llamada a una entrada de teléfonos seleccionado a continuación, IN_GROUP enviará llamadas directamente al grupo de entrada especificado. El valor predeterminado es EXTEN. CALLMENU enviará la llamada al menú de llamada definida. VMAIL_NO_INST enviará la llamada al buzón de voz que ha definido a continuación y no jugará instrucciones después del mensaje de correo de voz.

<BR>
<A NAME="inbound_dids-record_call">
<BR>
<B>Grabar Llamadas -</B> Esta opción le permite definir que las llamadas que entren a este DID sean grabadas. Y_QUEUESTOP grabará la llamada hasta que la llamad sea colgada o entre a una cola de grupo de entrada, N no grabará la llamada. El valor por defecto es N.

<BR>
<A NAME="inbound_dids-extension">
<BR>
<B>Extensión - </B> Si se ha seleccionado EXTEN como la ruta DID, entonces esta es la extensión del plan de marcación a la cual se enviará. El valor por defecto es 9998811112, no-service.

<BR>
<A NAME="inbound_dids-exten_context">
<BR>
<B>Contexto de Extensión - </B> Si se ha seleccionado EXTEN como la ruta DID, este es el contexto del plan de marcación al cual se enviará. El valor por defecto es por default
.

<BR>
<A NAME="inbound_dids-voicemail_ext">
<BR>
<B>Buzón de voz - </B> Si se ha seleccionado VOICEMAIL como la ruta DID, entonces este es el buzón de voz al que las llamadas se enviarán. Por defecto está vacío.

<BR>
<A NAME="inbound_dids-phone">
<BR>
<B>Extensión Telefónica- </B> Si está seleccionado PHONE como la ruta DID, entonces esta es la extensión de teléfono a la que se enviarán las llamadas.

<BR>
<A NAME="inbound_dids-server_ip">
<BR>
<B>Teléfono Servidor IP- </B> Si está seleccionado PHONE como la ruta de DID, entonces esta es la IP del servidor de la extensión teléfonica a la cuál las llamadas se enviarán.

<BR>
<A NAME="inbound_dids-menu_id">
<BR>
<B>Menú de Marcación -</B> Sí CALLMENU se selecciona como la Ruta de DID, entonces este es el menú de llamada al que las llamadas serán enviadas.

<BR>
<A NAME="inbound_dids-user">
<BR>
<B>Usuario Agent -</B> Si se selecciona como agente DID ruta, entonces este es el agente que pide será enviado a.

<BR>
<A NAME="inbound_dids-user_unavailable_action">
<BR>
<B>Acción Usuario no Disponible - </B> Si se ha seleccionado AGENTE como la ruta DID, y el usuario no está conectado o disponible, entonces este es el camino que las llamadas tomarán.

<BR>
<A NAME="inbound_dids-user_route_settings_ingroup">
<BR>
<B>En Configuración de usuario Ruta-Grupo - </B> Si se ha seleccionado AGENTE como ruta DID, este es el grupo de entrada que se utilizará para la configuración de la cola en la cuqal quien llama está a la espera de ser atendido por un agente. El valor por defecto es AGENTDIRECT.

<BR>
<A NAME="inbound_dids-group_id">
<BR>
<B>ID Grupo-Entrada- </B> Si se ha seleccionado IN_GROUP como ruta DID, este es el grupo al cual las llamadas se enviarán.

<BR>
<A NAME="inbound_dids-call_handle_method">
<BR>
<B>Grupo de Entrada Método de Manejo de Marcación -</B> Si se selecciona IN_GROUP como la ruta DID, entonces este es el método de manejo de llamadas se utiliza para estas llamadas. CID añadirá un nuevo registro de cliente potencial con cada llamada con el identificador de llamadas como el número de teléfono, CIDLOOKUP intentará buscar el número de teléfono por el identificador de llamadas en todo el sistema, CIDLOOKUPRL intentará buscar el número de teléfono por el identificador de llamadas en una sola lista especificada , CIDLOOKUPRC intentará buscar el número de teléfono por el identificador de llamadas en todas las listas que pertenecen a la campaña especificada, CLOSER se especifica para las llamadas más fuerte con ANI añadirá un nuevo registro de cliente potencial con cada llamada con la ANI como el número de teléfono, ANILOOKUP tratará de buscar el número de teléfono de la ANI en todo el sistema, ANILOOKUPRL intentará buscar el número de teléfono de la ANI en una sola lista especificada, XDIGITID pedirá al llamante para un código X dígitos antes de la llamada se puso en la cola, VIDPROMPT pedirá al llamante por su número de identificación y se creará un nuevo registro de cliente potencial con el identificador de llamadas como el número de teléfono y la identificación que el Vendor ID, VIDPROMPTLOOKUP intentará buscar el ID en todo el sistema, VIDPROMPTLOOKUPRL intentará operaciones de búsqueda la identificación del vendedor por el ID en una sola lista especificada, VIDPROMPTLOOKUPRC intentará buscar el ID de proveedor por el ID en todas las listas que pertenecen a la campaña especificada. El valor predeterminado es CID. Si un método CIDLOOKUP se usa con ALT, buscará el campo alt_phone para el número de teléfono si no se encuentran coincidencias para el número de teléfono principal. Si un método CIDLOOKUP se utiliza con ADDR3, buscará el campo address3 para el número de teléfono si no se encuentran coincidencias para el número de teléfono principal y opcionalmente el campo alt_phone.

<BR>
<A NAME="inbound_dids-agent_search_method">
<BR>
<B>Método Búsqueda Agente en Grupo-Entrada- </B> Si se ha seleccionado IN_GROUP como ruta DID, entonces este es el método de busqueda de agente que será utilizado en el gruupo de entrada.; LO es Load-Balanced-Overflow (Carga-Balance-Desbordamiento) y tratará de enviar la llamada a un agente en el servidor local antes de tratar de enviarla a un agente en otro servidor; LB es Load-Balance (Carga-Balance)y tratará de enviar la llamada al siguiente agente sin importar en que servidor esté; SO es el Server-Only (Servidor-Unicamente) y sólo intentará enviar las llamadas a los agentes en el servidor que está recibiendo la llamada. El valor por defecto es LB.

<BR>
<A NAME="inbound_dids-list_id">
<BR>
<B>-ID Lista Grupo-Entrada - </B> Si se ha seleccionado IN_GROUP como ruta DID, entonces esta es el ID de lista a través de la cual los contactos pueden ser buscados y en la que los contactos serán incluidos si es necesario.

<BR>
<A NAME="inbound_dids-campaign_id">
<BR>
<B>ID CAmpaña Grupo-Entrada - </B> Si se ha seleccionado como IN_GROUP la ruta DID, este es el ID de campaña en que los contactos pueden ser buscados si el método de manejo de la llamada es CIDLOOKUPRC.

<BR>
<A NAME="inbound_dids-phone_code">
<BR>
<B>Codigo Telefono de Grupo-Entrada - </B> Si se ha seleccionado IN_GROUP como ruta DID, este es el código de Teléfono Código a utilizar si se crea un nuevo contacto.

<BR>
<A NAME="inbound_dids-filter_clean_cid_number">
<BR>
<B>Limpiar Número CID -</B> Este campo le permite especificar un número de dígitos para restringir el número de ID de Llamada entrante, colocando una R delante del número de dígitos, por ejemplo, para restringir con 10 dígitos a la derecha se deben introducir R10. También puede utilizar esta función para eliminar sólo el primer dígito o los dígitos sucesivos al poner una L en frente de los dígitos específicos que desea eliminar, por ejemplo para eliminar un 1 como el primer dígito se debe introducir L1. El valor predeterminado es vacío. Si más de una regla se especifica asegúrese de separarlas con un espacio y la R se ejecutará antes de la L.

<BR>
<A NAME="inbound_dids-filter_inbound_number">
<BR>
<B>Filtro Número Entrante -</B> Esta opción si está activada permite filtrar las llamadas que entran en este DID y enviarlos a una acción alternativa si coinciden con un número de teléfono existente en el grupo filtro de teléfonos, o una URL de respuesta si se ha configurado una. El valor predeterminado es DISABLED. GRUPO buscará en un Grupo de Filtro de Teléfono. URL enviará a una dirección URL y se ajustará si uno es enviado de vuelta. DNC_INTERNAL buscará por la lista interna DNC. DNC_CAMPAIGN buscará por una lista específica DNC campaña.

<BR>
<A NAME="inbound_dids-filter_phone_group_id">
<BR>
<B>Filtrar ID Grupo de Teléfono -</B> Si el campo Filtro Número de entrada se establece como GROUP, entonces este es el ID del Grupo Filtro de Teléfono que tiene la lista de números en los cuales buscará identificar coincidencias con el número ID de Llamadas de la marcación entrante.

<BR>
<A NAME="inbound_dids-filter_url">
<BR>
<B>Filtro URL -</B> Si el campo Filtro Número de Entrada se establece como URL, entonces esta es la dirección web de un script que buscará un sistema remoto y devolverá un 1 si existe coincidencia y un 0 para ninguna coincidencia. Sólo dos variables están disponibles en la dirección si se utiliza el prefijo VAR similar a las direcciones de formulario web en campañas, --A--PHONE_NUMBER--B-- y --A--did_pattern--B-- se puede utilizar en la URL para indicar el ID de Llamadas de la persona que llama y el DID por el cual el cliente llama.

<BR>
<A NAME="inbound_dids-filter_url_did_redirect">
<BR>
<B>Filtro de URL de redireccionamiento DID -</B> Si el filtro de campo Número de entrada está establecido en el URL a continuación, este ajuste permite que la respuesta de URL para especificar un sistema de DID para redirigir la llamada a la INSEAD de utilizar la acción predeterminada. Si se devuelve un 0 se utiliza la acción predeterminada. Si no es un 0 nada se devuelve la llamada se redirige al valor de respuesta URL resultante.

<BR>
<A NAME="inbound_dids-filter_dnc_campaign">
<BR>
<B>Filtrar Campaña DNC -</B> Si el filtro de campo Número de entrada está establecido en DNC_CAMPAIGN entonces este es el ID específico campaña que la lista de DNC campaña pertenece a.



<BR>
<A NAME="inbound_dids-filter_action">
<BR>
<B>Acción de filtrado -</B> Si el número de entrada del filtro se activa y se encuentra una coincidencia, entonces este es la acción que debe ser tomada. Esta es la misma que la ruta seleccionada para un DID, y los ajustes siguientes operan de la misma forma que se hace en enrutamiento estandar.

<BR>
<A NAME="inbound_dids-custom_one">
<BR>
<B>Campos DID Personalizados- </ B> Estos cinco campos se pueden utilizar para diversos fines, principalmente en relación a la programación personalizada y los informes.




<BR>
<A NAME="did_ra_extensions">
<BR>
<B>DID Invalidaciones Extensión Agente Remoto -</B><BR>Esta sección le permite activar DID para tener invalñidaciónes de extensión para llamadas enrutadas por agentes remotos través de los grupos de entrada. El inicio del usuario debe ser un Inicio de Usuario Remoto Válido, o si lo desea una entrada de Invalidar Extensión para que trabaje con todas las llamadas entonces debe utilizar ---ALL--- en el campo de Inicio de Usuario. Si hay múltiples entradas para el mismo DID e Inicio de Usuario entonces las entradas activas serán utilizados en un método round robin.




<BR><BR><BR><BR>

<B><FONT SIZE=3>CONVOCATORIA MESA MENÚ</FONT></B><BR><BR>
<A NAME="call_menu-menu_id">
<BR>
<B>ID Menú -</B> Este es el ID para esta etapa del menú de marcación. Esto también se mostrará como el contexto que se utiliza en el plan de marcación para esta menú de marcación. Aquí hay una lista de frases reservadas que no se puede utilizar como ID de Menú: vicidial, vicidial-auto, general, globals, default, trunkinbound, loopback-no-log, monitor_exit, monitor.

<BR>
<A NAME="call_menu-menu_name">
<BR>
<B>Nombre de menú -</B> Este campo es el nombre descriptivo para el menú de marcación.

<BR>
<A NAME="call_menu-menu_prompt">
<BR>
<B>Menú del sistema -</B> Este campo contiene el nombre de archivo del sistema de audio para reproducir al comienzo de este menú. Puede introducir múltiples rutas en este campo y los demás ámbitos del sistema mediante  separación de ellos con el carácter barra vertical. Puede agregar NOINT directamente delante de un nombre de archivo de audio para hacer que la reproducción no se pueda interrumpir presionando una tecla por parte de la persona que llama,NOINT no debe formar parte del nombre de archivo, se trata de una marca especial para el sistema. También se puede utilizar para scripts .agi  con fines especiales en este campo, así como el script cm_date.agi, hable con su administrador para obtener más detalles.

<BR>
<A NAME="call_menu-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="call_menu-menu_timeout">
<BR>
<B>Menú de Tiempo de Espera -</B> Este campo es donde se define el tiempo de espera en segundos que el menú tomará para permitir a quien llama, digitar una opción DTMF. Establecer este campo a cero 0 significa que no habrá tiempo de espera después de que el sistema se ejecuta.

<BR>
<A NAME="call_menu-menu_timeout_prompt">
<BR>
<B>Menú Tiempo de Espera del Sistema -</B> Este campo contiene el nombre de archivo del sistema de audio a reproducir cuando el tiempo de espera ha sido alcanzado. El valor predeterminado es NINGUNO para no reproducir ningun audio en tiempo de espera.

<BR>
<A NAME="call_menu-menu_invalid_prompt">
<BR>
<B>Menú de Sistema no Válido -</B> Este campo contiene el nombre de archivo de audio del sistema que se reproduce cuando quien llama ha seleccionado una opción inválida. El valor predeterminado es NINGUNO para no reproducir audio en caso inválido.

<BR>
<A NAME="call_menu-menu_repeat">
<BR>
<B>Repetir Menú -</B> Este campo es donde se define el número de veces que el menú se reproducirá después de la primera vez, si quien marca no elige una opción válida. El valor por defecto es 1 para repetir el menú una vez.

<BR>
<A NAME="call_menu-menu_time_check">
<BR>
<B>Verificar Horario Menú -</B> Este campo es donde se puede seleccionar si desea restringir el acceso de llamadas del menú a la hora específica creada en el horario de marcación seleccionado. Si la hora de atención está en blanco, este ajuste será ignorado. Por defecto es 0 para desactivado.

<BR>
<A NAME="call_menu-call_time_id">
<BR>
<B>ID de Horario de Marcación -</B> Este es el ID Horario de Marcación que se utilizará para restringir los momentos de marcación si se activa la opción Verificar Horario Menú.

<BR>
<A NAME="call_menu-track_in_vdac">
<BR>
<B>Informe Segimiento de Marcación en Tiempo Real -</B> Este campo es donde se puede seleccionar si desea que la marcación sea seguida en la pantalla de tiempo real como una marcacion entrante de tipo IVR. El valor por defecto es1 para activo.

<BR>
<A NAME="call_menu-tracking_group">
<BR>
<B>ID de Seguimiento Grupo -</B> Este es el ID que puede utilizar para rastrear las llamadas en este Menú de Marcación cuando revisa el Informe IVR. La lista incluye CALLMENU como valor predeterminado, así como todos los Grupos-Entrada.

<BR>
<A NAME="call_menu-dtmf_log">
<BR>
<B>Oprima la tecla Entrar -</B> Esta opción si está habilitado registrará el DTMF de tecla pesionada por la persona que llama en este Menú de Marcación. El valor predeterminado es 0 para deshabilitado.

<BR>
<A NAME="call_menu-dtmf_field">
<BR>
<B>Campo de Registro -</B> Si la opción Presionar Clave de Registro se establece como activa, esta configuración opcional puede permitir que la respuesta también sea almacenada en este campo de lista. vendor_lead_code, source_id, phone_code, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, alt_phone, email, security_phrase, comments, rank, owner, status, user. El valor predeterminado es NONE para deshabilitado.

<BR>
<A NAME="call_menu-option_value">
<BR>
<B>Valor Opción -</B> Este campo es donde se define la opción de menú, las opciones posibles son: 0,1,2,3,4,5,6,7,8,9,*,#,A,B,C,D,TIMECHECK. La opción especial TIMECHECK sólo puede utilizarse si tiene la opción Verificación de Horario de Menú activa y existe un horario de maración definido para el menú. Para eliminar una opción, establezca la ruta al valor REMOVE y la opción se borrará cuando usted haga click en el botón REMITIR. TIMEOUT permitirá establecer lo que ocurre con la llamada cuando termina el tiempo de espera sin información de parte de la persona que llama. INVALID le permiten establecer lo que sucede cuando la persona que llama entra en una opción no válida. INVALID_2ND y 3RD sólo puede ser activo si INVALID no se utiliza, esperará hasta que la segunda o tercera entrada inválida la realice la persona que llama antes de ejecutar la opción.

<BR>
<A NAME="call_menu-option_description">
<BR>
<B>Opción Descripción -</B> Este campo es para describir la opción, esta descripción se pondrá en el plan de marcación como un comentario por encima de la opción.

<BR>
<A NAME="call_menu-option_route">
<BR>
<B>Opción Ruta -</B> Este menú contiene las opciones de a dónde enviar la llamada si se selecciona esta opción: CALLMENU, INGROUP, DID, HANGUP, EXTENSION, PHONE. Para CALLMENU, el valor de ruta debe ser el ID del menú del menú de marcación al que se desea enviar la llamada. Para INGROUP, el grupo de entrada al que desea que la llamada sea enviada para que el requerimiento sea seleccionado así como las otras 5 opciones que requieren ser configuradas adecuadamente para enrutar la llamada hacia un grupo de entrada. Para DID, el valor de ruta debe ser el camino de DID al que desea enviar la llamada. Para HANGUP, el valor de ruta pueden ser el nombre de un archivo de audio a reproducir antes de colgar la llamada. Para EXTENSION, el valor de ruta debe ser la extensión del plan de marcación a la que desea enviar la llamada, y el valor de contexto de de ruta es el contexto que se encuentra en la extensión. Si se deja en blanco el contexto se establecerá al contexto por defecto. Para PHONE, el valor de ruta debe ser el valor de inicio de sesión de teléfono de la entrada del teléfono al cual desea enviar la llamada. Para VOICEMAIL, el valor de ruta debe ser el número del buzón de voz, y el mensage de no disponible será reproducido. Para AGI, el valor de ruta debe ser el script de AGI y los valores que deben pasar a ella. VMAIL_NO_INST enviará la llamada al buzón de voz que ha definido a continuación y no jugará instrucciones después del mensaje de correo de voz.

<BR>
<A NAME="call_menu-option_route_value">
<BR>
<B>Opción Valor de Ruta -</B> Este campo es donde se introduce el valor que define el lugar al que la llamada será dirigida, en la opción de Ruta..

<BR>
<A NAME="call_menu-option_route_value_context">
<BR>
<B>Opción Contexto Valor de Ruta -</B> Este campo es opcional y sólo se utiliza para la extension de opciones de ruta.

<BR>
<A NAME="call_menu-ingroup_settings">
<BR>
<B>Menú Marcación Configuración Grupo-Entrada -</B> Si la ruta se establece como INGROUP entonces hay múltiples opciones que se pueden establecer para definir cómo se envía la llamada a la cola. El grupo de entrada es el al que ustede desea que se dirija la llamada. Método de Manejo es la forma en que desea que la llamada sea tratada, <a href="#inbound_dids-call_handle_method">Click aquí para ver una lista de los métodos de manejo disponibles</a>. Método de búsqueda define la forma en la cola encuentra el siguiente agente, recomendamos dejar este como LB. ID de Lista es la lista en que el nuevo Contacto es insertado, también si el método no es un método de búsqueda (LOOKUP)y el prospecto no se encuentra. ID Campaña es la campaña donde se encuentran las listas  a través de las cuales se realizará la búsqueda si uno de los métodos de RC se utiliza. Código de teléfono es la entrada del campo phone_code con el cual sera insertado el prospecto. VID Ingrese Nombre del Archivo es usado si el método se ajusta a uno de los métodos VIDPROMPT, esto es la entrada de audio reproducida para solicitar al cliente introducir su ID.VID número ID de Archivo se usa si el método se ajusta a uno de los métodos VIDPROMPT, es la entrada de audio reproducida después de que el cliente ingresa su ID, algo así como HA SIDO INGRESADO. VID Confirmar Nombre de Archivo se utiliza si el método se ajusta a uno de los métodos VIDPROMPT, y corresponde a la entrada de audio reproducida para confirmar su ID, algo así como TECLA 1 PARA CONFIRMAR Y 2 PARA VOLVER A ENTRAR. VID Dígitos se utiliza si el método se ajusta a uno de los métodos VIDPROMPT, si se establece en un número se entendería como el número de dígitos que deben ser introducidos por el cliente cuando se le solicite para su ID, si se establece en vacío o X, entonces la cliente tendrá que presione la tecla hash para terminar la entrada de su ID.

<BR>
<A NAME="call_menu-custom_dialplan_entry">
<BR>
<B>Entrada Plan de MArcación Personalizado -</B> Este campo le permite incorporar cualquier elemento en el plan de marcación que usted quiera en el Menú de Marcacion.

<BR>
<A NAME="call_menu-qualify_sql">
<BR>
<B>Encabezado SQL -</B> Este campo le permite introducir fragmentos de bases de datos SQL - Structured Query Language - , al igual que con los filtros, para determinar si este Menú de Marcación debe o no reproducirse a la persona que llama. Esta característica sólo funciona si la llamada tiene definido el parámetros Nombre de Id de Llamada antes de ser enviado a este Menú de Marcación, ya sea como una transferencia de encuesta de salida, o a través del uso de un menú de marcación desplegable para una llamada en grupo de entrada. Si hay una coincidencia, la llamada procederá de forma normal. Si no hay coincidencias, la llamada va a la opción D o la opción no válida si no se configura la opción D. No se puede utilizar comillas simples en este campo, sólo comillas dobles si se requiere. Por defecto está vacía para deshabilitado.






<BR><BR><BR><BR>

<BR>
<A NAME="filter_phone_groups-filter_phone_group_id">
<BR>
<B>Filtrar ID Grupo de Teléfono -</B> Este es el ID del Grupo Filtro de Teléfono que almacena un grupo de números de teléfonos que usted puede tener de forma automática a través de búsquedas en cuando entra una llamada en DID y envia a una ruta alternativa si hay una coincidencia. Este campo debe tener entre 2 y 20 caracteres y no tienen puntuacion exceptuando la raya al piso.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_name">
<BR>
<B>Filtrar Nombre Grupo de Telefono -</B> This is the name of the Filter Phone Group and is displayed with the ID in select lists where this feature is used. This field should be between 2 and 40 characters.

<BR>
<A NAME="filter_phone_groups-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_description">
<BR>
<B>Teléfono Filtro Descripción del Grupo -</B> Esta es la descripción del Grupo filtro de Teléfono, la cual es exclusivamente para propósitos de notación y no es un campo obligatorio.




<BR><BR><BR><BR>

<B><FONT SIZE=3>REMOTE_AGENTS tabular</FONT></B><BR><BR>
<A NAME="remote_agents-user_start">
<BR>
<B>Comienzo Del ID del usuario -</B> Este es el ID de usuario de partida que se utiliza cuando las entradas de los agentes remotos se insertan en el sistema. Si el número de líneas de ajuste sea mayor que 1, este número se incrementa en uno hasta que cada línea tiene una entrada. Asegúrese de crear una nueva cuenta de usuario con un nivel de usuario de 4 o grande si usted quiere que ellos sean capaces de utilizar la página vdremote.php para el acceso web remoto de esta cuenta.

<BR>
<A NAME="remote_agents-number_of_lines">
<BR>
<B>Número de Líneas -</B> Define cuántas entradas de agentes remotos crea el sistema, y se determina cuántas líneas considera que puede enviar con seguridad a la IP definida a continuación..

<BR>
<A NAME="remote_agents-server_ip">
<BR>
<B>IP de servidor -</B> Una entrada agente remoto es solamente apropiada para un servidor específico, por tanto aquí se selecciona el servidor requerido..

<BR>
<A NAME="remote_agents-conf_exten">
<BR>
<B>Extensión Externa -</B> Este es el número de llamadas que usted busca redireccionar. Asegurese de que sea un número completo del plande marcación y que si se requiere un 9 al comienzo este allí ubicado. Pruebe mediante marcación de este numero desde un teléfono en el sistema.

<BR>
<A NAME="remote_agents-extension_group">
<BR>
<B>Grupo de Extension -</B> Si se establece en algo distinto de NONE o vacío este anulará el campo de Extensión Externa y utilizará las entradas de Grupo de Extension que tienen el mismo ID de Extensión de Grupo. El valor predeterminado es NONE para desactivado.

<BR>
<A NAME="remote_agents-status">
<BR>
<B>Estado -</B> Aqui se activa o desactiva el Agente Remoto. Tan pronto como el agente esté activo el sistema asume que puede enviarle llamadas. Puede tomar hasta 30 segundos una vez que usted cambie el estado a inactivo para detener la recepción de llamadas.

<BR>
<A NAME="remote_agents-campaign_id">
<BR>
<B>Campaña -</B> Selecciona la campaña en la cual los agentes remotos serán registrados. Las llamadas entrantes requieren utilizar la campaña CLOSER y seleccionar en las campañas de entrada siguentes, de cuales se requiere recibir llamadas.

<BR>
<A NAME="remote_agents-on_hook_agent">
<BR>
<B>Agente en Reposo -</B> Esta opción sólo se utiliza para las llamadas entrantes van a este agente remoto. Esta función  llamará al agente remoto y no enviará al cliente al agente remoto hasta que la línea sea respondida. El valor por defecto es N para deshabilitado.

<BR>
<A NAME="remote_agents-on_hook_ring_time">
<BR>
<B>Tiempo de Timbrado en Reposo -</B> Esta opción sólo se utiliza cuando el campo Agente en Reposo descrito anteriormente se establece en Y,  entonces sólo aplicará a las llamadas entrantes que estén llegando a este agente remoto. Este es el número de segundos que cada intento de llamada timbrará tratando de obtener una respuesta. Se recomienda que se establezca esto en unos pocos segundos a menos de que requiera enviar la llamada a a un correo de voz. El valor predeterminado es 15.

<BR>
<A NAME="remote_agents-closer_campaigns">
<BR>
<B>Grupos de entrada -</B> Seleccionar los grupos de entrada de los cuales usted desea recibir llamadas, si ha seleccionado la campaña CLOSER.


<BR><BR><BR><BR>

<B><FONT SIZE=3>EXTENSION_GROUPS TABLA</FONT></B><BR><BR>
<A NAME="extension_groups-extension_group_id">
<BR>
<B>Grupo de Extension -</B> Este campo obligatorio es donde se introduce la ID de grupo que desea para que sea puesta esta extensión. No hay espacios ni caracteres especiales a excepción de la raya al piso,letras y números.

<BR>
<A NAME="extension_groups-extension">
<BR>
<B>Extension -</B> Este campo requerido es donde se coloca la extensión de plan de marcación que desea que el agente remoto marque para ser enviadas como entrada para este Grupo de Extension.

<BR>
<A NAME="extension_groups-rank">
<BR>
<B>Rank -</B> Este campo le permite clasificar las entradas de Grupo de Extensión que comparten el mismo grupo de extension. El valor predeterminado es 0.

<BR>
<A NAME="extension_groups-campaign_groups">
<BR>
<B>Grupos de Campañas -</B> En este campo se puede poner una lista de ID de campaña y de entrada y/o ID de Grupo de entrada en las que desee restringir el uso del grupo de extensión.La lista debe ser separado por barras veritcales y tiene barra al principio y al final de la cadena.


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN_LISTS</FONT></B><BR><BR>
<A NAME="campaign_lists">
<BR>
<B>Las listas dentro de esta campaña se enumeran aquí, si son activas son denotadas con Y o N y puede explorar por el listad en pantalla haciendo click en el ID de lista en la primera columna.</B>


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN_STATUSES TABLA</FONT></B><BR><BR>
<A NAME="campaign_statuses">
<BR>
<B>Mediante el uso de estados de campañas personalizadas, puede hacer que los estados que sólo existen para una campaña específica. El Estado debe ser 1-8 caracteres de longitud, la descripción debe ser 2-30 caracteres de longitud y seleccionable define si se muestra en el sistema en su disposición. El campo human_answered se utiliza en el cálculo del porcentaje de descenso, o tasa de abandono. Ajuste human_answered a Y utilizará este estado cuando se cuentan las llamadas humanos-contestó. La opción Categoría le permite agrupar varios estados en un catogy que se puede utilizar para el análisis estadístico.</B>



<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>CAMPAIGN_HOTKEYS TABLA</FONT></B><BR><BR>
	<A NAME="campaign_hotkeys">
	<BR>
	<B>Mediante el uso de teclas de acceso rápido de campaña personalizada, agentes que utilizan la web-cliente agente puede colgar y disposición llamadas con sólo pulsar una tecla en su teclado.</B> Hay dos opciones especiales que usted puede utilizar conjuntamente con la marcación del número de teléfono alterno, ALTPH2 - Marcación en caliente de numero alterno, y ADDR3----- Marcación en caliente de Address3, los cuales permiten que un agente utilice un atajo de teclado para colgar la llamada, permanecer con el mismo contacto, y marcar otro numero de contacto para ese prospecto.. También puede utilizar LTMG o XFTAMM como estados para desencadenar una transferencia automática a la opción de dejar correo de voz Leave-Buzón de Voz.





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_RECYCLE TABLA</FONT></B><BR><BR>
	<A NAME="lead_recycle">
	<BR>
	<B>Through the use of lead recycling, you can call specific statuses of leads again at a specified interval without resetting the entire list. Lead recycling is campaign-specific and does not have to be a selected dialable status in your campaign. The attempt delay field is the number of seconds until the lead can be placed back in the hopper, this number must be at least 120 seconds. The attempt maximum field is the maximum number of times that a lead of this status can be attempted before the list needs to be reset, this number can be from 1 to 10. You can activate and deactivate a lead recycle entry with the provided links.</B>





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>AUTO ALT ESTADOS DE MARCACION</FONT></B><BR><BR>
	<A NAME="auto_alt_dial_statuses">
	<BR>
	<B> Si se activa el campo de Automarcación Alterna, entonses los contactos que son dispuestos bajo este estado de automarcacion alterna recibiran marcación de los numeros registrados en los campos alt_phone y-o address3 despues de que se alcanza alguno de los estados de no contestar.</B>

	<?php
	}
?>



<BR><BR><BR><BR>

<B><FONT SIZE=3>CÓDIGOS DE PAUSA DE AGENTE</FONT></B><BR><BR>
<A NAME="pause_codes">
<BR>
<B>Si el Agente Pausa Códigos de campo activo se establece en activo, entonces los agentes podrán seleccionar de estos códigos de pausa al hacer clic en el botón PAUSA en sus pantallas. Estos datos se almacena en el registro del agente. El código de pausa debe contener sólo letras y números y ser menos de 7 caracteres. El nombre en clave de pausa puede tener más de 30 caracteres.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN PRESETS</FONT></B><BR><BR>
<A NAME="xfer_presets">
<BR>
<B>Si la configuración de campaña para preestablecidos se define como ENABLED, entonces usted tiene la capacidad de definir la configuración preestablecida de Transferencia-Conferencia que estará disponible para el agente que permitiendoles llamadas tripartitas en esta configuración o transferencias de llamadas ciegas a estos números predefinidos. Estas configuraciones también tienen una opción para ocultar el número asociado a cada preconfiguración de agente.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPAIGN CID CÓDIGOÁREAS</FONT></B><BR><BR>
<A NAME="campaign_cid_areacodes">
<BR>
<B>Si la configuración del sistema para CID de CódigoÁrea está habilitada y la configuración de la campaña para el ID de Llamada personalizado se establece CÓDIGOÁREA entonces usted tiene la capacidad de definir CID para los códigos de Area que se utilizarán cuando realice llamadas salientes a los contactos en esta campaña específica. Usted puede agregar múltiples id de llamada por código de área y se puede activar y desactivar cada uno de ellos en tiempo real. Si más de un identificador de llamadas está activo para un código de área específico, entonces el sistema utilizará el identificador de llamadas que se ha usad el menor número de veces en la actualidad. Si no estan activados los id de llamadas para el código de área entonces el ID de Llamadas de la campaña o de lista de invalidación de id de llamadas serán utilizados.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>USER_GROUPS TABLA</FONT></B><BR><BR>
<A NAME="user_groups-user_group">
<BR>
<B>Grupo Del Usuario -</B> Este es el nombre corto de un grupo de usuarios, trate de no usar espacios ni puntuacion para este campo. max 20 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="user_groups-group_name">
<BR>
<B>Nombre de Grupo -</B> Esta es la descripción del grupo de usuarios máximo de 40 caracteres.

<BR>
<A NAME="user_groups-forced_timeclock_login">
<BR>
<B>Forzar Registro Reloj Asistencia  -</B> Esta opción le permite no dejar registro de un agente para la interfaz del agente si no han iniciado sesión en el reloj temporizador. El valor predeterminado es N. Hay una opción de eximir a usuarios administradores, los niveles 8 y 9.

<BR>
<A NAME="user_groups-shift_enforcement">
<BR>
<B>Forzado de Turno -</B> Esta opción le permite restringir el acceso de usuarios basado en los turnos seleccionados a continuación. OFF no forzará ningún turno. START sólo fuerza la hora de inicio de sesión, pero no afectará a un agente que se está desarrollando su turno su ya está registrado. ALL forzará la hora de inicio de  turnos y desconectará  los agentes después de completar la duración del turno. El valor por defecto es OFF.

<BR>
<A NAME="user_groups-group_shifts">
<BR>
<B>Grupo de Turnos - </B> Esta es una lista de seleccioón de turnos que pueden restringir los horarios de acceso de los agentes en el sistema.

<BR>
<A NAME="user_groups-allowed_campaigns">
<BR>
<B>Campañas Permitidas -</B> Ésta es una lista de selección de campañas a las cuales los miembros de este grupo de usuario pueden abrirse una sesión. La opción de ALL-CAMPAIGNS permite que los usuarios en este grupo puedan ver e ingresar una sesión en cualquier campaña en el sistema.

<BR>
<A NAME="user_groups-agent_status_viewable_groups">
<BR>
<B>Grupos Estado de Agente Visible -</B> Esta es una lista seleccionable de grupos de usuarios y las funciones de usuario al que los miembros de este grupo de usuarios pueden ver el estado de las llamadas, así como la transferencia de lamadas al interior de la pantalla del agente. La opción ALL-GROUPS permite a los usuarios de este grupo ver y transferir llamadas a cualquier usuario en el sistema. La opción CAMPAIGN-AGENTS permite a los usuarios en este grupo ver y transferir llamadas a cualquier usuario en la campaña en la cual el usuario se encuentra registrado. La opción NOT-LOGGED-IN-AGENTS permite a todos los usuarios del sistema que se muestren, incluso si no están registrados actualmente.

<BR>
<A NAME="user_groups-agent_status_view_time">
<BR>
<B>Estado Agente Ver Hora -</B> Esta opción define si el agente va a ver la cantidad de tiempo que los usuarios en su barra lateral de agente han estado en su Estado ctual. Por defecto es N para  no ó deshabilitado.

<BR>
<A NAME="user_groups-agent_call_log_view">
<BR>
<B>Ver registro de Marcaciones de Agente -</B> Esta opción define si el agente será capaz de ver su registro de llamadas para las llamadas manejadas a través de la pantalla del agente. El valor predeterminado es N para no o discapacitados.

<BR>
<A NAME="user_groups-agent_xfer_options">
<BR>
<B>Opciones Transferencia de Agente -</B> Estas opciones permiten la desactivación de botones específicos en la sección Transferencia de Conferencia de la interfaz del agente. el valor por defecto es Y para Sí o habilitado.

<BR>
<A NAME="user_groups-agent_fullscreen">
<BR>
<B>Agente Pantalla Completa -</B> Esta opción, si establece en Y será establecer la altura y la anchura de la pantalla del agente con el tamaño de la ventana del navegador web sin ningún tipo de subsidio para los agentes View, llamadas en cola Ver o llamadas en la vista Sesión. El valor predeterminado es N para no o discapacitados.

<BR>
<A NAME="user_groups-allowed_reports">
<BR>
<B>Informes Permitidos -</B> Si un usuario en este grupo se establece en el nivel de usuario 7 o superior, entonces esta característica se utiliza para restringir los informes que los usuarios pueden ver. El valor predeterminado es ALL. Si desea seleccionar más de un informe entonces, pulse la tecla Ctrl en su teclado mientras selecciona los informes.

<BR>
<A NAME="user_groups-admin_viewable_groups">
<BR>
<B>PErmitido Grupos de Usuario -</B> Esta es una lista seleccionable de Grupos de Usuario a los que los miembros de este grupo de usuarios pueden ver y, posiblemente, editar. Grupos de usuarios puede restringir el acceso a casi todos los aspectos del sistema, desde DIDs entrantes hasta teléfonos y buzones de voz. La opción --ALL-- permite a los usuarios de este grupo para ver y acceder a cualquier registro en el sistema si sus permisos de usuario lo permiten.

<BR>
<A NAME="user_groups-admin_viewable_call_times">
<BR>
<B>Horarios de Marcación Permitidos -</B> Esta es una lista seleccionable de los horarios de marcación para que los miembros de este grupo de usuarios puedan utilizar en las campañas, en los grupos y menús de llamadas. La opción --ALL-- permite a los usuarios en este grupo usar todos los horarios de las llamadas en el sistema.


<?php
if (strlen($SSwebphone_url) > 5)
	{
	?>
	<BR>
	<A NAME="user_groups-webphone_url_override">
	<BR>
	<B>Invalidar URL de Webphone -</B> Esta configuración le permite configurar una URL de webphone alternativa sólo para los miembros de un grupo de usuarios. Por defecto está vacío.

	<BR>
	<A NAME="user_groups-webphone_systemkey_override">
	<BR>
	<B>Invalidar Systema de contraseña Webphone -</B> Este ajuste le permite configurar un Sistema de Clave  alternativo de webphone sólo para los miembros de un grupo de usuarios. El valor por defecto está vacío.

	<BR>
	<A NAME="user_groups-webphone_dialpad_override">
	<BR>
	<B>Invalidar Teclado de marcación de Webphone  -</B> Esta opción le permite activar o desactivar el teclado de marcación en el webphone sólo para los miembros de un grupo de usuarios. El valor predeterminado es DISABLED. TOGGLE permitirá al usuario ver y ocultar el teclado haciendo click en un enlace. TOGGLE_OFF por defecto para no mostrar el teclado de marcación en la primera carga, pero permitirá al usuario mostrar el teclado haciendo click en el enlace de teclado.

	<?php
	}

if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="user_groups-qc_allowed_campaigns">
	<BR>
	<B>CC Campañas Permitidas -</B> Esta es una lista seleccionable de Campañas en la que los miembros de este grupo de usuarios podrá realizar Control de Calidad (CC). En la opción ALL-CAMPAIGNS permite a los usuarios en este grupo realizar control de calidad para cualquier campaña en el sistema.

	<BR>
	<A NAME="user_groups-qc_allowed_inbound_groups">
	<BR>
	<B>CC Grupos Entrantes Permitidos - </B> Esta es una lista de seleccion de grupos de entrada en la que los miembros podran realizar Control de Calidad. LA opción ALL_GROUPS permite a los usuarios de este grupo realizar control de calidad a cualquier grupo en el sistema.
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SCRIPTS TABLA</FONT></B><BR><BR>
<A NAME="scripts-script_id">
<BR>
<B>ID de Script: -</B> Este es el nombre corto de un script. Esto tiene que ser un identificador único. Trate de no usar espacios ni puntuacion para este campo. max 10 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="scripts-script_name">
<B>Nombre de Script -</B> Este es el título de un script. Este es un breve resumen de la secuencia de comandos. max 50 caracteres, mínimo de 2 caracteres. No debe haber espacios o puntuacion de ningún tipo en el campo theis.

<BR>
<A NAME="scripts-script_comments">
<B>Comentarios de Script -</B> Aquí es donde usted puede colocar comentarios para una secuencia de comandos de pantalla agente como-cambió a actualización gratuita el 23 de septiembre -. max 255 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="scripts-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="scripts-script_text">
<B>Texto de Script -</B> Aquí es donde se coloca el contenido de una secuencia de comandos de pantalla del agente. Mínimo de 2 caracteres. Usted puede tener la información del cliente se llenará-auto en esta secuencia de comandos con "--A--field--B--" donde el campo es uno de los siguientes nombres de campos: vendor_lead_code, source_id, list_id, gmt_offset_now, called_since_last_reset, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, lead_id, campaign, phone_login, group, channel_group, SQLdate, epoch, uniqueid, customer_zap_channel, server_ip, SIPexten, session_id, dialed_number, dialed_label, rank, owner, camp_script, in_script, script_width, script_height, recording_filename, recording_id, user_custom_one, user_custom_two, user_custom_three, user_custom_four, user_custom_five, preset_number_a, preset_number_b, preset_number_c, preset_number_d, preset_number_e, preset_number_f, preset_dtmf_a, preset_dtmf_b, did_id, did_extension, did_pattern, did_description, closecallid, xfercallid, agent_log_id, entry_list_id, call_id, user_group, called_count, TABLAper_call_notes. For example, this sentence would print the persons name in it----<BR><BR>  Hello, can I speak with --A--first_name--B-- --A--last_name--B-- please? Well hello --A--title--B-- --A--last_name--B-- how are you today?<BR><BR> This would read----<BR><BR>Hello, can I speak with John Doe please? Well hello Mr. Doe how are you today?<BR><BR> También puede utilizar un iframe para cargar una ventana separada dentro de la pestaña Script, aquí hay un ejemplo con variables prepopulated:

<DIV style="height:200px;width:400px;background:white;overflow:scroll;font-size:12px;font-family:sans-serif;" id=iframe_example>
&#60;iframe src="http://www.sample.net/test_output.php?lead_id=--A--lead_id--B--&#38;vendor_id=--A--vendor_lead_code--B--&#38;list_id=--A--list_id--B--&#38;gmt_offset_now=--A--gmt_offset_now--B--&#38;phone_code=--A--phone_code--B--&#38;phone_number=--A--phone_number--B--&#38;title=--A--title--B--&#38;first_name=--A--first_name--B--&#38;middle_initial=--A--middle_initial--B--&#38;last_name=--A--last_name--B--&#38;address1=--A--address1--B--&#38;address2=--A--address2--B--&#38;address3=--A--address3--B--&#38;city=--A--city--B--&#38;state=--A--state--B--&#38;province=--A--province--B--&#38;postal_code=--A--postal_code--B--&#38;country_code=--A--country_code--B--&#38;gender=--A--gender--B--&#38;date_of_birth=--A--date_of_birth--B--&#38;alt_phone=--A--alt_phone--B--&#38;email=--A--email--B--&#38;security_phrase=--A--security_phrase--B--&#38;comments=--A--comments--B--&#38;user=--A--user--B--&#38;campaign=--A--campaign--B--&#38;phone_login=--A--phone_login--B--&#38;fronter=--A--fronter--B--&#38;closer=--A--user--B--&#38;group=--A--group--B--&#38;channel_group=--A--group--B--&#38;SQLdate=--A--SQLdate--B--&#38;epoch=--A--epoch--B--&#38;uniqueid=--A--uniqueid--B--&#38;customer_zap_channel=--A--customer_zap_channel--B--&#38;server_ip=--A--server_ip--B--&#38;SIPexten=--A--SIPexten--B--&#38;session_id=--A--session_id--B--&#38;dialed_number=--A--dialed_number--B--&#38;dialed_label=--A--dialed_label--B--&#38;rank=--A--rank--B--&#38;owner=--A--owner--B--&#38;phone=--A--phone--B--&#38;camp_script=--A--camp_script--B--&#38;in_script=--A--in_script--B--&#38;script_width=--A--script_width--B--&#38;script_height=--A--script_height--B--&#38;recording_filename=--A--recording_filename--B--&#38;recording_id=--A--recording_id--B--&#38;user_custom_one=--A--user_custom_one--B--&#38;user_custom_two=--A--user_custom_two--B--&#38;user_custom_three=--A--user_custom_three--B--&#38;user_custom_four=--A--user_custom_four--B--&#38;user_custom_five=--A--user_custom_five--B--&#38;preset_number_a=--A--preset_number_a--B--&#38;preset_number_b=--A--preset_number_b--B--&#38;preset_number_c=--A--preset_number_c--B--&#38;preset_number_d=--A--preset_number_d--B--&#38;preset_number_e=--A--preset_number_e--B--&#38;preset_number_f=--A--preset_number_f--B--&#38;preset_dtmf_a=--A--preset_dtmf_a--B--&#38;preset_dtmf_b=--A--preset_dtmf_b--B--&#38;did_id=--A--did_id--B--&#38;did_extension=--A--did_extension--B--&#38;did_pattern=--A--did_pattern--B--&#38;did_description=--A--did_description--B--&#38;closecallid=--A--closecallid--B--&#38;xfercallid=--A--xfercallid--B--&#38;agent_log_id=--A--agent_log_id--B--&#38;entry_list_id=--A--entry_list_id--B--&#38;call_id=--A--call_id--B--&&#38;user_group=--A--user_group--B--&&#38;" style="width:580;height:290;background-color:transparent;" scrolling="auto" frameborder="0" allowtransparency="true" id="popupFrame" name="popupFrame" width="460" height="290" STYLE="z-index:17"&#62;
&#60;/iframe&#62;
</DIV>
<BR>
También puede utilizar la variable especial IGNORENOSCROLL para forzar las barras de desplazamiento en la pestaña de script, incluso si usted está usando un iframe dentro de ella.

<BR>
<A NAME="scripts-active">
<BR>
<B>Activo -</B> Esto determina si este Script se puede seleccionar para ser utilizado por una campaña.





<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_FILTROS TABLA</FONT></B><BR><BR>
	<A NAME="lead_filters-lead_filter_id">
	<BR>
	<B>Filter ID -</B> Este es el nombre abreviado de un Filtro de plomo. Esto tiene que ser un identificador único. No utilice espacios ni puntuacion para este campo. max 10 caracteres, mínimo de 2 caracteres.

	<BR>
	<A NAME="lead_filters-lead_filter_name">
	<B>Nombre de Filtro -</B> Define un nombre más descriptivo para el filtro. Éste es un resumen del filtro. Máximo 30 caracteres, mínimo 2 caracteres.

	<BR>
	<A NAME="lead_filters-lead_filter_comments">
	<B>Filter Comentarios -</B> Aquí es donde usted puede colocar comentarios para un filtro, como-llamadas todo California lidera-. max 255 caracteres, mínimo de 2 caracteres.

	<BR>
	<A NAME="lead_filters-user_group">
	<BR>
	<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

	<BR>
	<A NAME="lead_filters-lead_filter_sql">
	<B>Filtro SQL -</B> Aquí se define el fragmento de consulta SQL por el cual desea filtrar. No inicie ni termine con expresiones AND, por que será agregado en el script de descarga a la lista de contactos para marcar, de forma automática. Un ejemplo de consulta SQL que podría funcionar acá es - called_count \> 4 and called_count \< 8 -.
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>HORARIOS DE MARCACION TABLA</FONT></B><BR><BR>
<A NAME="call_times-call_time_id">
<BR>
<B>ID de Horario de Marcación -</B> Este es el nombre corto de una definición de tiempo de llamada. Esto tiene que ser un identificador único. No utilice espacios ni puntuacion para este campo. max 10 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="call_times-call_time_name">
<B>Nombre de Horario de Marcación -</B> Nombre descriptivo del horario de marcación definido. Resumen de la definición del horario de marcación. Máximo 30 caracteres, mínimo 2 caracteres.

<BR>
<A NAME="call_times-call_time_comments">
<B>Comentarios de Horario de Marcación -</B> Aquí es donde usted puede colocar comentarios para una definición de tiempo de llamada, como-10 a.m.-4 p.m. con estado de llamada extra de restricciones-. max 255 caracteres.

<BR>
<A NAME="call_times-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="call_times-ct_default_start">
<B>Horas de inicio y de parada por defecto -</B> Este es el horario de inicio y parada definido por defecto en que se permitirá la llamada si la hora de inicio del dia de la semana no se ha definido. 0 es media noche. Para restringir cualquier marcación establecer este campo en 2400 y establecer el momento de parada en 2400. Para permitir marcación durante 24 horas se establece inicio en 0 y parada en 2400.. Para entrantes solamente, también puede ajustar la duración de la marcación superior a 2400 si desea que el horario de marcación vaya más allá de la medianoche. Así que si requiere que su horario de marcación funcione desde las 6 am hasta las 2 am del día siguiente, se pondría a 0600 como hora de inicio y 2600 como hora de parada.

<BR>
<A NAME="call_times-ct_sunday_start">
<B>Horarios de inicio y parada entre semana -</B> Establece horarios personalizados diarios que pueden ser establecidos para la deficición de horarios. Aplica las mismas reglas de definición de horarios de inicio y parada por defecto..

<BR>
<A NAME="call_times-default_afterhours_filename_override">
<B>Invalidar Archivo post horario -</B> Estos campos le permiten invalidar el mensaje post horario para los grupos de entrada si se ha establecido algun valor. el valor por defecto es vacío.

<BR>
<A NAME="call_times-ct_state_call_times">
<B>Definicion de estados de horarios de marcación -</B> Definición especifica de estados para horarios de marcación .

<BR>
<A NAME="call_times-state_call_time_state">
<B>Condición Estados de Horarios de Marcación -</B> Código de 2 letras de el estado para el cual está definido este horario. Para que tenga efecto en el horario de marcación local definido en la campaña debe tener este registro de condiciones de horario de marcación también como todos los contactos tienen codigos de estado de dos letras en ellos.

<A NAME="call_times-holiday_id">
<BR>
<B>DiaFestivo ID -</B> Este es el nombre corto de una Definición de Dia Festivo. Esto tiene que ser un identificador único. No utilice espacios ni signos de puntuación para este campo. Máximo 30 caracteres, mínimo 2 caracteres.

<BR>
<A NAME="call_times-holiday_name">
<B>DiaFestivo Nombre  -</B> Este es un nombre más descriptivo de la definición del Dia Festivo. Este es un resumen de la definición de Dia Festivo. Máximo 100 caracteres, mínimo 2 caracteres.

<BR>
<A NAME="call_times-holiday_comments">
<B>DiaFestivo Comentarios -</B> This is where you can place comments for a Holiday Definition such as -10am to 4pm boxing day restrictions-.  max 255 characters.

<BR>
<A NAME="call_times-holiday_date">
<B>DiaFestivo Fecha -</B> Esta es la fecha del dia festivo.

<BR>
<A NAME="call_times-holiday_status">
<B>Estado DiaFestivo -</B> Este es el estado de la entrada de DiaFestivo. Estado ACTIVE significa que el festivo será activado en la fecha de DiaFestivo. Estado inactivo significa que las vacaciones serán ignoradas incluso en la fecha de DiaFestivo. EXPIRED significa que la fiesta ha pasado su fecha de vacaciones. El valor predeterminado es INACTIVE.




<BR><BR><BR><BR>

<B><FONT SIZE=3>SHIFTS TABLA</FONT></B><BR><BR>
<A NAME="shifts-shift_id">
<BR>
<B>Shift ID -</B> Este es el nombre corto de una definición Shift sistema. Esto tiene que ser un identificador único. No utilice espacios ni puntuacion para este campo. max 20 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="shifts-shift_name">
<B>Nombre de Turno - </B> Nombre descriptivo de la Definición de Turno. Este resume la definición de turno. Máximo 50 caracteres, mínimo 2 caracteres.

<BR>
<A NAME="shifts-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="shifts-shift_start_time">
<B>Hora inicio Turno - </B> Este es el momento en que comienza el turnos en la campaña. Sólo deben ser números, por ejemplo las 9:30 AM y 5:00 AM sería 0930 - 1700.

<BR>
<A NAME="shifts-shift_length">
<B>Duración Turno - </B> Este es el tiempo en horas y minutos que dura la campaña por turnos. 8 horas sería 08:00 y 7 horas y 30 minutos sería 07:30.

<BR>
<A NAME="shifts-shift_weekdays">
<B>Dias Semana Turno - </B> En esta sección usted debe elegir los días de la semana que este turno está activo.

<BR>
<A NAME="shifts-report_option">
<B>Opción de Informe -</B> Esta opción permite que este turno específico sea mostrado en los informes seleccionados que soportan esta opción.

<BR>
<A NAME="shifts-report_rank">
<B>Informe de Categorías -</B> Esta opción le permite clasificar los turnos en los informes seleccionados que apoyan esta opción.




<BR><BR><BR><BR>
<A NAME="audio_store">
<B>Almacén de Audio -</B> Esta utilidad te permite subir archivos de audio en el servidor web para que puedan distribuirse a todos los servidores del sistema de un clúster de servidores múltiples. Una nota importante, sólo dos tipos de archivos de audio va a funcionar,. Wav que son PCM Mono 16bit y 8k. Archivos gsm que son 8k de 8 bits. Compruebe que los archivos tienen el formato correcto antes de subirlos aquí.



<BR><BR><BR><BR>

<B><FONT SIZE=3>MUSIC_ON_HOLD TABLA</FONT></B><BR><BR>
<A NAME="music_on_hold-moh_id">
<BR>
<B>ID Música de Espera -</B> Este es el nombre corto de una entrada de Música de Espera. Este debe ser un identificador único. No utilice espacios ni puntuacion para este campo. Máximo 100 caracteres, mínimo 2 caracteres.

<BR>
<A NAME="music_on_hold-moh_name">
<B>Nombre Música de Espera -</B> Este es un nombre más descriptivo de la música en la entrada Hold. Este es un breve resumen de la música en el contexto de Espera y se mostrará como un comentario en el archivo conf musiconhold. max 255 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="music_on_hold-active">
<B>Activo -</B> Esta opción le permite definir la entrada de Música de Espera como activa o inactiva. Inactivo eliminará la entrada de los archivos conf.

<BR>
<A NAME="music_on_hold-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="music_on_hold-random">
<B>Orden Aleatorio -</B> Esta opción le permite definir la reproducción de los archivos de audio en un orden aleatorio. Si se establece a N, entonces el orden definido será utilizado.

<BR>
<A NAME="music_on_hold-filename">
<B>Filename -</B> Para añadir un nuevo archivo de audio a una entrada de Música de Espera, primero debe almacenarse el archivo en al almacén de audio, luego puede seleccionar el archivo y haga clic en Remitir para añadirlo a la lista de archivos. Música de Espera se actualiza una vez por minuto si se han producido cambios. Cualquier archivo que no figure en entrada de Música de Espera que esté presente en el folder de espera será eliminado.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TTS_PROMPTS TABLA</FONT></B><BR><BR>
<A NAME="tts_prompts-tts_id">
<BR>
<B>TTS ID -</B> Este es el nombre corto de una entrada de TTS. Este debe ser un identificador único. No utilice espacios ni puntuacion para este campo. Máximo 50 caracteres, mínimo 2 caracteres.

<BR>
<A NAME="tts_prompts-tts_name">
<B>TTS Nombre -</B> Este es un nombre más descriptivo de la entrada TTS. Este es un breve resumen de la definición de TTS. Máximo 100 caracteres, mínimo 2 caracteres.

<BR>
<A NAME="tts_prompts-active">
<B>Activo -</B> Esta opción le permite establecer la entrada de TTS como activa o inactiva.

<BR>
<A NAME="tts_prompts-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="tts_prompts-tts_voice">
<B>TTS Voz -</B> Aquí es donde se define la voz a ser utilizada en la generación de TTS. El valor predeterminado es Allison-8kHz.

<BR>
<A NAME="tts_prompts-tts_text">
<B>TTS Texto -</B> Este es el campo de datos actual de TTS que se envía a Cepstral para la creación del archivo de audio que debe reproducir el cliente. Puede utilizar Speech Syntesis Markup Language -SSML- en este campo, por ejemplo,, &lt;break time='1000ms'/&gt; for a 1 second break. You can also use several variables such as first name, last name and title as system variables just like you do in a Script: --A--first_name--B--. Si usted tiene archivos de audio estáticos que desee utilizar como valor base de uno de los campos, puede utilizar estos así como las etiquetas C y D. Los nombres de archivo deben estar todos en minúscula y deben ser archivo .wav de 16 bits PCM 8k. El nombre del campo debe ser el mismo pero sin el .wav en el nombre de archivo. Por ejemplo --C----A--address3--B----D-- podría primero encontrar el valor para address3, luego trataría de encontrar un archivo de audio que corresponda a ese valor para abrirlo. Aquí está una lista de las variables disponibles: lead_id, entry_date, modify_date, status, user, vendor_lead_code, source_id, list_id, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_count, last_local_call_time, rank, owner




<BR><BR><BR><BR>

<B><FONT SIZE=3>VOICEMAIL TABLA</FONT></B><BR><BR>
<A NAME="voicemail-voicemail_id">
<BR>
<B>ID Correo de Voz -</B> Este es el ID de todos los números de este buzón. Esto no debe ser duplicado de un ID de mensaje de voz existentes o el ID del correo de voz de un teléfono en el sistema, mínimo 2 caracteres.

<BR>
<A NAME="voicemail-fullname">
<B>Name -</B> Este es el nombre asociado a este buzón de voz. Máximo 100 caracteres , mínimo de 2 caracteres.

<BR>
<A NAME="voicemail-pass">
<B>Password -</B> Esta es la contraseña que se utiliza para acceder al buzón de voz cuando se marca para revisar los mensajes máximo 10 caracteres, mínimo 2 caracteres.

<BR>
<A NAME="voicemail-active">
<B>Activo -</B> Esta opción le permite configurar el buzón de correo de voz como activo o inactivo. Si el buzón está inactivo no se puede dejar mensajes en él y no se puede revisar los mensajes en él.

<BR>
<A NAME="voicemail-email">
<B>Correo Electronico -</B> Este ajuste opcional le permite tener los mensajes de correo de voz enviados a una cuenta de correo electrónico, si su sistema está configurado para enviar correo electrónico. Si este campo está vacío, entonces no se envían correos electrónicos.

<BR>
<A NAME="voicemail-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="voicemail-delete_vm_after_email">
<B>Eliminar Correo de Voz después de Email -</B> Este ajuste opcional le permite tener los mensajes de correo de voz eliminados del sistema después de haber sido enviado por correo electrónico. El valor por defecto es N.

<BR>
<A NAME="voicemail-voicemail_greeting">
<B>Saludo de Correo de Voz -</B> Esta configuración opcional le permite definir un archivo de audio de saludo de correo de voz desde el almacén de audio. Por defecto está en blanco.

<BR>
<A NAME="voicemail-voicemail_timezone">
<B>Zona Correo de Voz -</B> Esta configuración permite configurar la zona en que este buzón de voz se establecerá en el momento en que se registre un mensaje. Por defecto está activo en la Configuración del Sistema.

<BR>
<A NAME="voicemail-voicemail_options">
<B>Opciones Correo de Voz -</B> Esta configuración opcional le permite definir configuraciónes de correo de voz adicionales. Se recomienda que deje este en blanco a menos que sepa lo que está haciendo.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>

	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LIST LOADER FUNCTIONALITY</FONT></B><BR><BR>
	<A NAME="list_loader">
	<BR>
	El cargador de plomo básico basado en la web está diseñada simplemente para tener un archivo de plomo - hasta 8 MB de tamaño - es decir cualquiera de las fichas o una pipa delimitada y cargarlo en la tabla de la lista. El cargador de plomo permite elegir el terreno y TXT-Plain Text, CSV, Comma Separated Values ​​y formatos de archivo XLS-Excel. El cargador de plomo no hace la validación de datos, pero permite comprobar si hay duplicados en sí mismo, dentro de la campaña o dentro de todo el sistema. Además, asegúrese de que se haya creado la lista que estos conductores van a ser bajo para que pueda utilizarlos. Aquí está una lista de los campos en el orden correcto para los archivos de plomo:
		<OL>
		<LI>Vendor Lead Code - shows up in the Vendor ID field of the GUI
		<LI>Código de fuente - uso interno solamente para los admins y DBAs
		<LI>ID de lista - Número de lista en la que están contenidos que los contactos mostrarán
		<LI>Phone Code - the prefix for the phone number - 1 for US, 44 for UK, 61 for AUS, etc
		<LI>Número de teléfono - Debe ser al menos 8 dígitos de largo
		<LI>Título - título del contacto(Sr. Sra., etc...)
		<LI>Nombre
		<LI>Inicial Media
		<LI>Apellidos
		<LI>Dirección, línea 1
		<LI>Dirección, línea 2
		<LI>Dirección, línea 3
		<LI>Ciudad
		<LI>Estado - limitado a 2 caracteres
		<LI>Provincia
		<LI>Código Postal
		<LI>País
		<LI>Género
		<LI>Fecha de Nacimiento
		<LI>Número De Teléfono Alternativo
		<LI>Dirección Email
		<LI>Frase de Seguridad
		<LI>Comentarios
		<LI>Rank
		<LI>Owner
		</OL>

	<BR>NOTAS: La funcionalidad para cargar archivos excel es permitida a través de una serie de scripts perl y requiere tener configurado de forma adecuada el archivo /etc/astguiclient.conf en la ubicación del servidor web. También, es necesario cargar los siguientes  modulos perl para que funcione adecuadamente - oLE-Storage_Lite-Storage_Lite y Spreadsheet-ParseExcel. Puede verificar la existencia de errores en ejecución de estos scripts revisando el contenido de su archivo error_log de apache. También, para verificación de duplicados contra listas de campañas, la lista que tiene nuevos contactos por ingresar necesita ser creada en el sistema antes de poder cargar los contactos.

	<BR>
	<A NAME="list_loader-duplicate_check">
	<BR>
	<B>Verificación por duplicado - </B>Las opciones duplicadas permiten comprobar las entradas duplicadas como cargar los cables en el sistema. Usted puede seleccionar para comprobar si hay duplicados en tan sólo la misma lista, sólo las mismas listas de campaña o dentro de todo el sistema. Si usted ha elegido un método de comprobación de duplicados, también se puede seleccionar opcionalmente los únicos estados específicos que desea duplicar cheque contra.

	<BR>
	<A NAME="list_loader-file_layout">
	<BR>
	<B>File layout - </B>La disposición del archivo que va a cargar. "Formato estándar" utiliza el formato de archivo estándar predefinido. "Diseño personalizado" permite al usuario definir la disposición del archivo sí mismos. "Plantilla personalizada" es un híbrido de las dos opciones anteriores, lo que permite al usuario utilizar un formato personalizado que ha definido previamente y guardado utilizando el creador de plantillas Custom.

	<BR>
	<A NAME="list_loader-template_id">
	<BR>
	<B>ID Plantilla -</B> If the user has selected "Plantilla Personalizada" from the "File layout" options, then this the the template the lead loader will use.  It will also override the selected list ID with the list ID that was assigned to the selected template when it was created.



	<BR><BR><BR><BR>
	<?php
	}
?>





<B><FONT SIZE=3>TABLA DE LOS TELÉFONOS</FONT></B><BR><BR>
<A NAME="phones-extension">
<BR>
<B>Extensión de Teléfono -</B> Este campo es donde se pone el nombre de los teléfonos, como aparecen en Asterisk sin incluir el protocolo o slash al principio. Por ejemplo: para el teléfono SIP: SIP/test101 la extensión del teléfono sería test101. Además, para teléfonos IAX2: IAX2/IAXphone1@IAXphone1 sería IAXphone1. Para Zap y Dahdi adjunta a un banco de teléfonos o un teléfono FXS, asegúrese de poner el número de canal completo, sin prefijo: Zap/25-1 sería 25-1. Otra nota, asegúrese de que establece el campo del Protocolo correctamente para su tipo de teléfono, en los campos sucesivos.

<BR>
<A NAME="phones-dialplan_number">
<BR>
<B>Número del plan de Marcación-</B> Este campo es para el número que usted marca para alcanzar el anillo del teléfono. Este número se define en el archivo extensions.conf de su servidor asterisk

<BR>
<A NAME="phones-voicemail_id">
<BR>
<B>Casillero de Buzón de Voz -</B> Este campo es para el casillero del buzón de voz donde se almacenan los mensajes para el usuario de este teléfono. Se utiliza esto para comprobar los mensajes del buzón de voz y para que el usuario pueda acceder al Buzón de Voz desde astGUIclient.

<BR>
<A NAME="phones-voicemail_timezone">
<B>Zona Correo de Voz -</B> Esta configuración permite configurar la zona en que este buzón de voz se establecerá en el momento en que se registre un mensaje. Por defecto está activo en la Configuración del Sistema.

<BR>
<A NAME="phones-voicemail_options">
<B>Opciones Correo de Voz -</B> Esta configuración opcional le permite definir configuraciónes de correo de voz adicionales. Se recomienda que deje este en blanco a menos que sepa lo que está haciendo.

<BR>
<A NAME="phones-outbound_cid">
<BR>
<B>ID de quien llama de salida -</B> Este campo es donde usted incorporara el número del ID de quien llama que usted quiera que aparezca en las llamadas de salida. Esto no funciona en las líneas RTB(non-PRI) T1/E1.

<BR>
<A NAME="phones-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="phones-phone_ip">
<BR>
<B>Dirección IP del teléfono -</B> Este campo es para la dirección IP del teléfono si es un teléfono de VoIP. Este es un campo opcional

<BR>
<A NAME="phones-computer_ip">
<BR>
<B>Dirección IP del ordenador -</B> Este campo es para la dirección IP del ordenador del usuario. Es un campo opcional

<BR>
<A NAME="phones-server_ip">
<BR>
<B>IP del servidor -</B> Este menú es donde usted selecciona en que servidor está el teléfono activo.

<BR>
<A NAME="phones-login">
<BR>
<B>Agente Pantalla de Acceso -</B> El nombre de usuario utilizado para el usuario del teléfono para acceder a las aplicaciones cliente, como la pantalla de agente.

<BR>
<A NAME="phones-pass">
<BR>
<B>Inicio de sesión Contraseña -</B>  Es la contraseña usada por el usuario del teléfono para acceder a las aplicaciones cliente basadas en Web. IMPORTANTE, esta es la contraseña exclusiva para el inicio de agente en interfaz teléfonica Web. Para cambiar la contraseña de sip.conf o iax.conf, o el secreto, para este dispositivo telefónico tiene que modificar la Contraseña de Registro en el siguiente campo.

<BR>
<A NAME="phones-conf_secret">
<BR>
<B>Registro Contraseña -</B> Este es el secreto o la contraseña, para el teléfono en el archivo autogenerado conf de SIP ó IAX para este teléfono. El límite es 20 caracteres alfanuméricos y se acepta guión y raya al piso. El valor predeterminado es test. Secreto Archivo Conf. marcado anteriormente . Una contraseña de registro segura debe tener al menos 8 caracteres y contiene minúsculas, mayúsculas y al menos un número.

<BR>
<A NAME="phones-is_webphone">
<BR>
<B>Establecer como Webphone -</B>  Establecer esta opción como Y intentará cargar un teléfono basado en web cuando el agente se registra en la pantalla de su agente. Por defecto es N. La opción Y_API_LAUNCH se puede utilizar con la API de agente para poner en marcha el webphone en un Iframe o ventana separada .

<BR>
<A NAME="phones-webphone_dialpad">
<BR>
<B>Teclado de Marcación Webphone -</B>  Esta opción le permite activar o desactivar el teclado de marcación para este webphone. Por defecto es Y para activado. TOGGLE permitirá al usuario ver y ocultar el teclado haciendo clic en un enlace. Esta característica no está disponible en todas las versiones Webphone. TOGGLE_OFF por defecto para no mostrar el teclado de marcación en la primera carga, pero permitirá al usuario mostrar el teclado haciendo click en el enlace de teclado.

<BR>
<A NAME="phones-webphone_auto_answer">
<BR>
<B>Webphone Respuesta automática -</B>  Esta configuración permite al webphone ser configurado para responder automáticamente a las llamadas que entran, estableciéndo este campo en Y, o tener llamadas timbrando estableciendolo este campo en N. El valor por defecto es Y.

<BR>
<A NAME="phones-use_external_server_ip">
<BR>
<B>Use IP de Servidor Externo -</B>  Si utiliza algo como un web phone, usted puede definir a Y para utilizar IP de Servidores Externa para registrarse en lugar de la IP del servidor. Por defecto está vacío.

<BR>
<A NAME="phones-status">
<BR>
<B>Estado -</B> El estado del teléfono en el sistema, puede ser ACTIVO y ADMIN permitiendo que los clientes del GUI trabajen. ADMIN permite el acceso a este Web site de administración. El resto de los estados no permiten el acceso al GUI o al Web de Admin.

<BR>
<A NAME="phones-active">
<BR>
<B>Cuenta activa -</B> Si el teléfono está activo se registra en la lista en el GUI de cliente.

<BR>
<A NAME="phones-phone_type">
<BR>
<B>Tipo de teléfono -</B> Solamente para información administrativa.

<BR>
<A NAME="phones-fullname">
<BR>
<B>Nombre completo -</B> Usado por el GUIclient en la lista de teléfonos activos.

<BR>
<A NAME="phones-company">
<BR>
<B>Compañía -</B> Solamente para las notas administrativas.

<BR>
<A NAME="phones-email">
<BR>
<B>Teléfonos E-mail - </B> La dirección de correo electrónico asociada con esta entrada de teléfono. Esto se utiliza configuraciones de buzón de voz.

<BR>
<A NAME="phones-delete_vm_after_email">
<B>Eliminar Correo de Voz después de Email -</B> Este ajuste opcional le permite tener los mensajes de correo de voz eliminados del sistema después de haber sido enviado por correo electrónico. El valor por defecto es N.

<BR>
<A NAME="phones-voicemail_greeting">
<B>Saludo de Correo de Voz -</B> Esta configuración opcional le permite definir un archivo de audio de saludo de correo de voz desde el almacén de audio. Por defecto está en blanco.

<BR>
<A NAME="phones-voicemail_instructions">
<B>Instrucciones del correo de voz -</B> Esta configuración le permite definir si las instrucciones del correo de voz jugarán después del saludo de correo de voz cuando una llamada suena en la extensión del agente y el tiempo de espera al correo de voz. El valor predeterminado es Y.

<BR>
<A NAME="phones-picture">
<BR>
<B>Cuadro -</B> No implementado aún.

<BR>
<A NAME="phones-messages">
<BR>
<B>Nuevos mensajes -</B> Número de los nuevos mensajes del Buzón de Voz para este teléfono en el servidor asterisk.

<BR>
<A NAME="phones-old_messages">
<BR>
<B>Mensajes Antiguos-</B> Cantidad de mensajes antiguos en el Buzón de Voz para este teléfono en el servidor asterisk.

<BR>
<A NAME="phones-protocol">
<BR>
<B>Protocolo del cliente -</B> El protocolo que el teléfono utiliza para conectar con el servidor asterisk: Sip, IAX2, Zap. También, existe EXTERNO para los números remotos o los números de marcación rápida que usted desea enumerar como teléfonos.

<BR>
<A NAME="phones-local_gmt">
<BR>
<B>GMT Local -</B> La diferencia con la hora de Greenwich, o la hora ZULU donde se encuentra el teléfono. NO AJUSTE PARA HORARIO DE VERANO. Esto es utilizado por la campaña para mostrar con precisión la hora del sistema y el tiempo del cliente, así como iniciar con precisión cuando los eventos ocurren.

<BR>
<A NAME="phones-phone_ring_timeout">
<BR>
<B>Tiempo Espera Timbre Teléfono -</B> Esta es la cantidad de tiempo, en segundos, que el teléfono sonará en el plan de marcación antes de enviar la llamada al buzón de voz. El valor por defecto es 60 segundos.

<BR>
<A NAME="phones-on_hook_agent">
<BR>
<B>Acceso de Agente en Reposo -</B> Esta opción sólo se utiliza para las llamadas entrantes van a un agente registrado con este teléfono. Esta característica llamará al agente y no enviará al cliente a la sesión de agente hasta que la línea sea contestada. El valor por defecto es N para deshabilitado.

<BR>
<A NAME="phones-ASTmgrUSERNAME">
<BR>
<B>Acceso de Supervisor -</B> Éste es el acceso que los clientes del GUI de este teléfono utilizarán para tener acceso a la base de datos donde residen los datos del servidor..

<BR>
<A NAME="phones-ASTmgrSECRET">
<BR>
<B>Contraseña de Supervisor -</B> Ésta es la contraseña que los clientes del GUI de este teléfono utilizarán para tener acceso a la base de datos donde residen los datos del servidor..

<BR>
<A NAME="phones-login_user">
<BR>
<B>Agente de usuario predeterminado -</B> Se trata de poner un valor predeterminado en el campo de usuario del agente cada vez que este usuario teléfono abre la aplicación cliente. Deja en blanco para ningún usuario.

<BR>
<A NAME="phones-login_pass">
<BR>
<B>Agente defecto Pass -</B> Esta es colocar un valor predeterminado en el campo de la contraseña cada vez que el agente este usuario de teléfono abre la aplicación cliente. Deje en blanco para no pasar.

<BR>
<A NAME="phones-login_campaign">
<BR>
<B>Campaña por defecto Agente -</B> Se trata de poner un valor predeterminado en el campo campaña pantalla agente siempre que el usuario de teléfono abre la aplicación cliente. Deja en blanco para ninguna campaña.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Exten Aparcado -</B> Ésta es la extensión de aparcado por defecto para las aplicaciones de cliente. Verifique que una distinta funciona antes cambiar esta..

<BR>
<A NAME="phones-conf_on_extension">
<BR>
<B>Extensión de conferencia -</B> Ésta es la extensión de aparcado de conferencias por defecto para las aplicacines de cliente. Verifique que una distinta funciona antes de cambiar esta..

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Agente Parque Exten -</B> Se trata de la extensión de estacionamiento por defecto para la aplicación cliente. Compruebe que una diferente funciona antes de cambiar este.

<BR>
<A NAME="phones-park_on_filename">
<BR>
<B>Parque Agente Archivo -</B> Este es el nombre del archivo de extensión parque pantalla agente predeterminado para las aplicaciones cliente. Compruebe que una diferente funciona antes de cambiar esto. limitado a 10 caracteres.

<BR>
<A NAME="phones-monitor_prefix">
<BR>
<B>Prefijo del monitor -</B> Éste es el prefijo del plan de marcación para supervisar los canales zap automáticamente dentro de la aplicación astGUIclient. Cambie solamente según los registros de extensiones ZapBarge del archivo  extensions.conf.

<BR>
<A NAME="phones-recording_exten">
<BR>
<B>Extension de Grabación -</B> Ésta es la extensión del plan de marcación para la extensión de grabación que se utiliza para ingresar en las conferencias para grabarlas. Dura generalmente hasta una hora si no se detiene. Verificar con el archivo extensions.conf antes de cambiarla..

<BR>
<A NAME="phones-voicemail_exten">
<BR>
<B>Extension principal VMAIL -</B> Ésta es la extensión del plan de marcación que comprueba su correo de voz. Verificar en el archivo extensions.conf antes de cambiarla..

<BR>
<A NAME="phones-voicemail_dump_exten">
<BR>
<B>Descarga Exten de Descarga de VMAIL -</B> Éste es el prefijo del plan de marcación usado para enviar llamadas directamente al buzón de voz de un usuario a partir de una llamada activa en la aplicación astGUIclient. Verificar en el archivo extensions.conf antes de cambiarla..

<BR>
<A NAME="phones-voicemail_dump_exten_no_inst">
<BR>
<B>VMAIL Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="phones-ext_context">
<BR>
<B>Contexto De Exten -</B> Este es el contexto del plan de marcado de que la pantalla del agente, utiliza principalmente. Se supone que todos los números marcados por las aplicaciones cliente utilizan este contexto por lo que es una buena idea para asegurarse de que este es el más amplio contexto posible. verificar con el archivo extensions.conf antes de cambiar. por defecto es default.

<BR>
<A NAME="phones-phone_context">
<BR>
<B>Contexto de Telefonos -</B> Este es el contexto del plan de marcado que este teléfono va a utilizar para realizar llamadas. Si está ejecutando un centro de llamadas y usted no quiere que sus agentes sean capaces de marcar fuera de la pantalla applicaiton agente por ejemplo, entonces se debe establecer este campo en un contexto dialplan que no existe, algo así como-agente nodial. por defecto es default.

<BR>
<A NAME="phones-codecs_list">
<BR>
<B>Codecs Permitidos -</B> Se puede definir una lista delimitada por comas de los codecs que se establecerá como los codecs predeterminados para este teléfono. Opciones para los codecs son ulaw, alaw, gsm, G729, Speex, G722, G723, G726, iLBC, ... Algunos de estos codecs pueden no estar disponibles en su sistema, como el G729 o G726. Si el campo está vacío, entonces los codecs por defecto del sistema o la entrada de teléfono por encima de éste se utilizará para los codecs permitidos. Por defecto está vacío.

<BR>
<A NAME="phones-codecs_with_template">
<BR>
<B>Códecs Permitidos con Plantilla-</B> Al establecer esta opción en 1, se incluyen los códecs definidos anteriormente, incluso si un archivo de plantilla conf se utiliza. El valor predeterminado es 0.

<BR>
<A NAME="phones-dtmf_send_extension">
<BR>
<B>Canal de envío DTMF -</B> Ésta es la secuencia de canal usada para enviar sonidos DTMF en conferencias desde las aplicaciones de cliente. Verifique la extensión y el contexto en el archivo extensions.conf.

<BR>
<A NAME="phones-call_out_number_group">
<BR>
<B>Grupo de llamadas salientes -</B> Éste es el canal del grupo que realiza llamadas de salida a partir de este telefono, ubicandolas fuera. Hay varias rutinas en las aplicaciones de cliente que utilizan esto. Para los canales zap se utiliza algo como Zap/g2, para troncales IAX2 debe utilizar el prefijo completo de IAX como IAX2/VICItest1:secret@10.10.10.15:4569. Verifique las troncaless en el archivo extensions.conf, que es lo que generalmente se ha definido como la variable global TRUNK en la cabecera del archivo..

<BR>
<A NAME="phones-client_browser">
<BR>
<B>Localización del Navegador -</B> esto es aplicable solamente a los clientes UNIX/LINUX, el camino absoluto a Mozilla o el navegador de Firefox en el ordenador. Verificar esto lanzándolo manualmente.

<BR>
<A NAME="phones-install_directory">
<BR>
<B>Directorio de Instalación -</B> No se utiliza más.

<BR>
<A NAME="phones-local_web_callerID_URL">
<BR>
<B>URL de CallerID -</B> Ésta es la dirección web de la página usada para hacer operaciones de búsqueda particulares de callerID. La dirección de prueba por defecto : http://astguiclient.sf.net/test_callerid_output.php

<BR>
<A NAME="phones-agent_web_URL">
<BR>
<B>URL Agent por defecto -</B> Esta es la dirección web de la página que se utiliza para realizar consultas de formularios Web de los agentes personalizado. dirección de prueba predeterminado se define en el esquema de base de datos.

<BR>
<A NAME="phones-AGI_call_logging_enabled">
<BR>
<B>Call Logging -</B> This is set to true if the call_log step is in place in the extensions.conf file for all outbound and hang up 'h' extensions to log all calls. This should always be 1 because it is manditory for many of the system features to work properly.

<BR>
<A NAME="phones-user_switching_enabled">
<BR>
<B>Conmutación de usuario -</B> Se establece como Verdadero para permitir que el usuario cambie a otra cuenta de usuario. NOTA: Si los usuarios conmutan pueden inicial la grabación de la conversación de nuevo usuario

<BR>
<A NAME="phones-conferencing_enabled">
<BR>
<B>Conferencia -</B> Se fija el valor Verdadero para permitir que el usuario comience llamadas de conferencia con hasta que seis líneas externas.

<BR>
<A NAME="phones-admin_hangup_enabled">
<BR>
<B>Admin Cuelga-</B> Se fija el valor Verdadero para permitir que el usuario pueda al colgar cualquier línea arbitrariamente en astGUIclient. Es conveniente permitir unicamente a los usuarios Admin.

<BR>
<A NAME="phones-admin_hijack_enabled">
<BR>
<B>Apoderamiento de Admin -</B> Se establece el valor Verdadero para permitir que el usuario pueda capturar y redirigir a su extensión una línea arbitraria a través de astGUIclient. Buena idea de permitir solamente esto unicamente a los usuarios Admin. Pero es muy útil para los Supervisores.

<BR>
<A NAME="phones-admin_monitor_enabled">
<BR>
<B>Monitor del Admin -</B> Se establece el valor Verdadero para permitir que el usuario pueda capturar y redirigir a su extensión cualquier línea arbitrariamente a través de astGUIclient. Es conveniente permitir solamente esto para los usuarios Admin. Pero es muy útil para los Supervisores y como herramienta de entrenamiento.

<BR>
<A NAME="phones-call_parking_enabled">
<BR>
<B>Aparcado de llamada -</B> Se establece el valor Verdadero para permitir que el usuario pueda poner llamadas en espera en astGUIclient para ser tomado por cualquier otro usuario de astGUIclient en el sistema. Las llamadas permanecen en espera hasta por media hora luego se cuelgan. Usualmente activo para todos.

<BR>
<A NAME="phones-updater_check_enabled">
<BR>
<B>Verificar Updater -</B> Se establece el valor Verdadero para desplegar un  popup de advertencia informado que el registro de tiempo de Updater no ha cambiado en 20 segundos. Útil para los usuarios Admin.

<BR>
<A NAME="phones-AFLogging_enabled">
<BR>
<B>Registro de Af -</B> Se establece el valor verdadero para registrar varias acciones del uso de astGUIclient en un archivo de texto en el ordenador del usuario.

<BR>
<A NAME="phones-QUEUE_ACTION_enabled">
<BR>
<B>Colas Habilitadas -</B> Se establece en true para que las aplicaciones cliente utilizan el sistema Asterisk central Queue. Necesario para que el sistema funcione y se recomienda para todos los teléfonos.

<BR>
<A NAME="phones-CallerID_popup_enabled">
<BR>
<B>Popup de CallerID -</B> Establecer el valor a Verdadero para permitir a todos los numeros definidos en el archivo extensions.conf, el envío de las mensajes popup de CallerID a los usuarios de astGUIclient.

<BR>
<A NAME="phones-voicemail_button_enabled">
<BR>
<B>Botón de VMail -</B> Se establece el valor Verdadero para desplegar el botón de VOICEMAIL y el despliege del conteo de mensajes en astGUIclient.

<BR>
<A NAME="phones-enable_fast_refresh">
<BR>
<B>Actualización Rápida -</B> Se establece el valor a Verdadero para permitir una nueva tasa de actualización de la información de la llamada para el astGUIclient. La tasa de actualización por defecto al estar inactivo es de 1000ms (1 segundo). Si se reduce este numero se aumenta la carga del sistema.

<BR>
<A NAME="phones-fast_refresh_rate">
<BR>
<B>Tasa de Actualización Rápida -</B> en milisegundos. Utilizado solamente si la tasa de activación rápida se activa. La de actualización por defecto, al estar inactiva es de 1000ms (1 segundo). Si se reduce este numero se aumenta la carga del sistema.

<BR>
<A NAME="phones-enable_persistant_mysql">
<BR>
<B>MySQL Persistente-</B> si está activa la conexión del astGUIclient será permanente en lugar de ser conexión iterativa por segundo. Útil si tiene definida una tasa de actualización rápida. Esto aumentará el número de conexiones a su máquina MySQL.

<BR>
<A NAME="phones-auto_dial_next_number">
<BR>
<B>Auto Marcar el siguiente Número -</B> Si se habilita la pantalla de agente marcará el siguiente número de la lista de forma automática a disposición de una llamada a menos que se seleccionan en PAUSA AGENTE DE MARCADO en la pantalla de la disposición.

<BR>
<A NAME="phones-VDstop_rec_after_each_call">
<BR>
<B>Detener Grabación después de cada llamada -</B> Si se activa la pantalla de agente dejará de cualquier grabación que está pasando después de cada llamada se ha dispositioned. Es útil si usted está haciendo un montón de grabación o si está utilizando un formulario web para activar la grabación. 

<BR>
<A NAME="phones-enable_sipsak_messages">
<BR>
<B>Enable SIPSAK Messages -</B> Si está habilitado, el servidor enviará mensajes al teléfono SIP para mostrar en la pantalla del teléfono cuando está conectado a la interfaz web del agente. Característica sólo funciona con teléfonos SIP y requiere la aplicación SIPSAK ser instalado en el servidor web. Por defecto es 0.

<BR>
<A NAME="phones-DBX_server">
<BR>
<B>Servidor DBX -</B> El servidor de la base de datos MySQL con el cual este usuario debe conectar.

<BR>
<A NAME="phones-DBX_database">
<BR>
<B>Base de datos DBX -</B> La base de datos de MySQL con la cual este usuario debe conectar. Por defecto es asterisk.

<BR>
<A NAME="phones-DBX_user">
<BR>
<B>Usuario DBX -</B> La conexión de usuario MySQL que este usuario debe utilizar al conectar. Por defecto "cron".

<BR>
<A NAME="phones-DBX_pass">
<BR>
<B>Contraseña DBX -</B> La contraseña de usuario de MySQL que este usuario debe utilizar al conectar. Por defecto "1234".

<BR>
<A NAME="phones-DBX_port">
<BR>
<B>Puerto DBX -</B> El puerto TCP de MySQL que este usuario debe utilizar al conectar. Por defecto es 3306.

<BR>
<A NAME="phones-DBY_server">
<BR>
<B>Servidor DBY -</B> El servidor de base de datos MySQL con el cual este usuario debe conectar. Secundario server, no utilizado actualmente.

<BR>
<A NAME="phones-DBY_database">
<BR>
<B>Base de datos DBY -</B> La base de datos de MySQL con la cual este usuario debe conectar. Por defecto es asterisk. Secundario server, no utilizado actualmente.

<BR>
<A NAME="phones-DBY_user">
<BR>
<B>Usuario DBY -</B> Identificador de usuario MySQL que este usuario debe utilizar al conectar. Por defecto "cron". Secundario server, no utilizado actualmente.

<BR>
<A NAME="phones-DBY_pass">
<BR>
<B>Contraseña DBY -</B> Contraseña de usuario MySQL que este usuario debe utilizar al conectar. Por defecto "1234". Secundario server, no utilizado actualmente.

<BR>
<A NAME="phones-DBY_port">
<BR>
<B>Puerto DBY -</B> Puerto TCP MySQL que este usuario debe utilizar al conectar. Por defecto "3306". Secundario server, no utilizado actualmente.

<BR>
<A NAME="phones-alias_id">
<BR>
<B>ID Alias - </B> El ID de los alias utilizados para permitir accesos de distribución de carga teléfonica. Sin espacios ni otros caracteres especiales permitidos. Debe tener entre 2 y 20 caracteres.

<BR>
<A NAME="phones-alias_name">
<BR>
<B>Nombre Alias - </B> El nombre utilizado para describir un alias de teléfonos, debe tener entre 2 y 50 caracteres.

<BR>
<A NAME="phones-logins_list">
<BR>
<B>Lista de accesos a teléfonos- </B> La lista separada por comas de accesos telefonicos utilizados cuando un agente se registra para el uso de accesos de balanceo de carga telefónica. La aplicación de agente encontrará el servidor activo con el menor número de agentes registrados en él y ubicará una llamada desde ese servidor al agente tras su inicio de sesión..

<BR>
<A NAME="phones-template_id">
<BR>
<B>ID Plantilla - </B> Este es el ID de la plantilla del archivo conf que este teléfono va a utilizar para la entrada de su configuración en Asterisk. El valor por defecto es --NONE--.

<BR>
<A NAME="phones-conf_override">
<BR>
<B>Conf Override Settings -</B> If populated, and the ID Plantilla is set to --NONE-- then the contents of this field are used as the conf file entries for this phone. generate conf files for this phones server must be set to Y for this to work. This field should NOT contain the [extension] line, that will be automatically generated.

<BR>
<A NAME="phones-group_alias_id">
<BR>
<B>ID Alias de Grupo -</B> El ID del alias de grupo utilizados por los agentes para marcar llamadas desde la interfaz del agente con diferentes ID de llamada. sin espacios ni otros caracteres especiales permitidos. Debe estar entre 2 y 20 caracteres de longitud.

<BR>
<A NAME="phones-group_alias_name">
<BR>
<B>Nombre Alias de Grupo - </B> El nombre utilizado para describir un alias de grupo, debe tener entre 2 y 50 caracteres.

<BR>
<A NAME="phones-caller_id_number">
<BR>
<B>Número ID de llamadas - </B> El número de identificador de llamadas utilizado en este Alias de Grupo de Alias. Debe ser sólo cifras.

<BR>
<A NAME="phones-caller_id_name">
<BR>
<B>Nombre ID de Llamadas - </B> El nombre de ID de llamadas que puede ser enviado con este alias de grupo. Por lo que sabemos esto sólo funcionará en Canadá en los circuitos PRI y la utilizando un bucle troncal IAX a través de Asterisk.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TABLA DE SERVIDORES</FONT></B><BR><BR>
<A NAME="servers-server_id">
<BR>
<B>ID del Servidor -</B>  Este campo es donde usted registra el nombre de los servidores asterisk, no tiene que ser un subdominio oficial, simplemente un nickname para identificar el servidor a los usuarios Admin.

<BR>
<A NAME="servers-server_description">
<BR>
<B>Descripción del servidor -</B> Campo donde usted describe con una frase corta el servidor asterisk.

<BR>
<A NAME="servers-server_ip">
<BR>
<B>IP address del servidor -</B> El campo donde registró la dirección IP de red del servidor asterisk.

<BR>
<A NAME="servers-active">
<BR>
<B>Activo -</B> Activosí el servidor asterisk esta activo o inactivo.

<BR>
<A NAME="servers-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="servers-sysload">
<BR>
<B>Sistema de carga -</B> Estas dos estadísticas muestran el promedio de carga del sistema multiplicado por 100 y el porcentaje de uso de la CPU del servidor, datos que se actualizan cada minuto. El promedio de carga (loadavg) debe estar en promedio, por debajo de los 100 multiplicado por el número de núcleos de CPU que su sistema tiene, para un rendimiento óptimo. El porcentaje de uso de la CPU debería permanecer por debajo de 50 para un rendimiento óptimo.

<BR>
<A NAME="servers-channels_total">
<BR>
<B>Canales Activos -</B> Este campo muestra el número actual de canales Asterisk activos en el sistema en el momento actual. Es importante señalar que el número de canales de Asterisk es generalmente mucho mayor que el número de llamadas en un sistema. Este campo se actualiza una vez cada minuto.

<BR>
<A NAME="servers-disk_usage">
<BR>
<B>Uso del disco -</B> Este campo mostrará el uso del disco para cada partición en este servidor. Este campo se actualiza una vez cada minuto.

<BR>
<A NAME="servers-system_uptime">
<BR>
<B>Sistema Uptime -</B> Este campo mostrará la disponibilidad del sistema de este servidor. Este campo sólo se actualiza si está configurado para hacerlo por su administrador.

<BR>
<A NAME="servers-asterisk_version">
<BR>
<B>Versión asterisk -</B> Define la versión de asterisk que ha instalado en este servidor. Ejemplos: ' 1.2 ', ' 1.0.8 ', ' 1.0.7 ', ' CVS_HEAD ', ' REALMENTE ANTIGUO ', etc... Se utiliza esto porque las versiones 1.0.8 y 1.0.9 tienen un método distinto para tratar con el entorno local y los canales, un error que ha sido corregido en CVS v1.0, por tanto requiere ser tratado de forma distinta cuando se define el modelo de manejo del entorno Local y los canales. También, el CVS_HEAD actual y las jerarquias en la versión 1.2 utilizan diferentes modelos de gestión e instrucciones por tanto también deben ser tratadas de forma distinta.

<BR>
<A NAME="servers-max_trunks">
<BR>
<B>Max Trunks -</B> Este campo determinará el número máximo de líneas que el auto-dialer intentará llamar en este servidor. Si quieres dedicar dos T1 completos del PRI hasta la salida de la marcación en el servidor a continuación, establecer esta a 46. Todas las llamadas entrantes de marcación o manuales se imputarán a este total también. El valor predeterminado es 96.

<BR>
<A NAME="servers-outbound_calls_per_second">
<BR>
<B>Máximas llamadas por segundo -</B> Esta configuración determina el número máximo de llamadas que pueden ser colocados mediante el script de automarcación de salida en este servidor por cada segundo. Debe ser entre 1 y 100. El valor por defecto es 20.

<BR>
<A NAME="servers-telnet_host">
<BR>
<B>Host telnet -</B> Ésta es la dirección o el nombre del servidor asterisk y es cómo las aplicaciones de supervisor conectan con él desde donde se estén ejecutando. Si están funcionando en el servidor asterisk, entonces el valor por defecto 'localhost' es correcto.

<BR>
<A NAME="servers-telnet_port">
<BR>
<B>Puerto Telnet -</B> Éste es el puerto de la conexión del supervisor del servidor asterisk y es cómo las aplicaciones de supervisión son conectadas desde donde se estén ejecutando. El valor por defecto es '5038' es correcto para una instalación estándar.

<BR>
<A NAME="servers-ASTmgrUSERNAME">
<BR>
<B>Usuario Supervisor -</B> el nombre de usuario o login utilizado para conectar de forma genérica con el gestor del servidor asterisk. El valor por defecto es 'cron'

<BR>
<A NAME="servers-ASTmgrSECRET">
<BR>
<B>Clave Supervisor -</B> La Clave o contraseña utilizada para conectar de forma genérica con el gestor del servidor asterisk. El valor por defecto es '1234'

<BR>
<A NAME="servers-ASTmgrUSERNAMEupdate">
<BR>
<B>Usuario de actualización del gestor -</B> El nombre de usuario o login utilizado para conectar con el gestor de asterisk  en modo de actualización de scripts. Por defecto se utiliza 'updatecron' y toma la misma clave del usuario genérico.

<BR>
<A NAME="servers-ASTmgrUSERNAMElisten">
<BR>
<B>Usuario de Escucha Supervisor -</B> El nombre de usuario o login utilizado para conectar con el gestor del servidor asterisk optimizado para scripts que solo quedan en espera para enviar señales de salida. Por defecto es 'listencron' y toma la misma clave del usuario genérico.

<BR>
<A NAME="servers-ASTmgrUSERNAMEsend">
<BR>
<B>Usuario de envíos Supervisor -</B> El usuario o login utilizado para conectar con el gestor del servidor asterisk optimizado para scripts que envían solamente solicitudes al supervisor. El valor por defecto es 'sendcron' y toma la misma clave que el usuario genérico.

<BR>
<A NAME="servers-conf_secret">
<BR>
<B>Secreto Archivo Conf -</B> Este es el secreto, o la contraseña, para que el servidor en el archivo autogenerado de conf iax para este servidor ó en otros servidores. El límite es de 20 caracteres alfanuméricos, el guión y la raya al piso son aceptadas. El valor predeterminado es test. Un secreto fuerte para un archivo conf debe tener al menos 8 caracteres y contener minúsculas, mayúsculas y al menos un número.

<BR>
<A NAME="servers-local_gmt">
<BR>
<B>Compensación GMT del servidor -</B> La diferencia en horas relativa al horario GMT sin ajuste de horarios estacionales. El valor por defecto es '-5'

<BR>
<A NAME="servers-voicemail_dump_exten">
<BR>
<B>Extensión de Descarga Vmail -</B> El prefijo de extensión utilizado para VMail (correo de voz) usado en este servidor para enviar llamadas directamente a través de agc a un compartimiento específco de correo de voz. El valor por defecto es '85026666666666'

<BR>
<A NAME="servers-voicemail_dump_exten_no_inst">
<BR>
<B>VMAIL Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="servers-answer_transfer_agent">
<BR>
<B>extensión de marcado automático -</B> La extensión por defecto si ninguno está presente en la campaña para enviar llamadas a la marcación automática. El valor predeterminado es '8365'

<BR>
<A NAME="servers-ext_context">
<BR>
<B>Contexto por defecto -</B> El contexto del plan de marcación por defecto usado para scripts que funcionan en este servidor. El valor por defecto es 'default'

<BR>
<A NAME="servers-sys_perf_log">
<BR>
<B>Rendimiento del Sistema -</B> Establecer esta opción como Verdadero (Y) activa el registro de las estadisticas de rendimiento del sistema para la máquina servidor, incluyendo el sistema de carga, procesos del sistema y canales Asterisk en uso. El valor por defecto es N.

<BR>
<A NAME="servers-vd_server_logs">
<BR>
<B>Registros del Servidor -</B> Establecer esta opción a Y será habilitar el registro de todos los scripts relacionados con el sistema a sus archivos de registro de texto. Fijar esto a N dejará escribir registros en archivos para estos procesos, también el registro de la pantalla de asterisco se desactivará si se establece en N cuando se inicia Asterisk. El valor predeterminado es Y.

<BR>
<A NAME="servers-agi_output">
<BR>
<B>Salida de AGI -</B> Al establecer esta opción en NONE desactiva la salida de todos los scripts AGI relacionados con el sistema. Fijar esto a STDERR enviará la salida de AGI para el Asterisk CLI. Fijar esto a FILE enviar la salida a un archivo en el directorio de registros. Fijar esto a ambos envían la salida tanto a la CLI Asterisk y un archivo de registro. El valor predeterminado es Archivo.

<BR>
<A NAME="servers-balance_active">
<BR>
<B>Equilibre Marcación -</B> Si establece este campo a Y permitirá que el servidor para realizar llamadas de saldo durante las campañas en el sistema de modo que el nivel de línea definida puede cumplirse incluso si no hay agentes conectados en esa campaña en este servidor. El valor predeterminado es N.

<BR>
<A NAME="servers-balance_rank">
<BR>
<B>Saldo Posición -</B> Este campo le permite establecer el orden en que este servidor se va a utilizar para compensar la marcación, si está activada la compensación de marcación. El servidor con el puntaje más alto será usado primero para la colocación de compensación de llamadas. el valor por defecto es 0.

<BR>
<A NAME="servers-balance_trunks_offlimits">
<BR>
<B>Equilibre Offlimits -</B> Este ajuste define el número de troncales para no permitir que los procesos de marcado equilibrio para su uso. Por ejemplo, si tiene 40 troncos max y equilibrio Offlimits se establece en 10 sólo se podrá utilizar 30 líneas troncales de marcación equilibrio. Por defecto es 0.

<BR>
<A NAME="servers-recording_web_link">
<BR>
<B>Grabación Enlace Web - </B> Esta opción le permite ignorar el valor por defecto de la pantalla del enlace de grabación en la página web de administración. El valor por defecto es server_ip.

<BR>
<A NAME="servers-alt_server_ip">
<BR>
<B>IP Servidor de Grabación Alternativo - </B> Este valor es donde usted puede poner una IP de servidor o el nombre de otra máquina que se puede utilizar en lugar de server_ip en los enlaces para grabaciones dentro del administrador de páginas web. Por defecto está vacío.

<BR>
<A NAME="servers-external_server_ip">
<BR>
<B>IP Servidor Externo -</B> Esta configuración define una dirección IP de servidor o el nombre de otra máquina que se puede utilizar en lugar de la server_ip cuando se utiliza un webphone en la interfaz de agente. Para que esto funcione debe tener también la entrada de teléfonos configurada para utilizar la dirección IP del servidor externo. Por defecto está vacío.

<BR>
<A NAME="servers-active_twin_server_ip">
<BR>
<B>Activar IP Servidor Pareja -</B> Algunos sistemas requieren la configuración de servidores de telefonía en parejas. Este ajuste es el lugar donde usted puede poner el IP del servidor de otro servidor que este servidor está hermanada con. Por defecto está vacía para discapacitados .

<BR>
<A NAME="servers-active_asterisk_server">
<BR>
<B>Activo Asterisk Server -</B> Si Asterisk no está en ejecución en este servidor, o si los procesos de marcado no deberían usar este servidor, o si sólo están utilizando este servidor para otros guiones como el guión de la tolva de carga que usted desearía establecer esto en N. El valor predeterminado es Y.

<BR>
<A NAME="servers-auto_restart_asterisk">
<BR>
<B>Auto-Restart Asterisk -</B> Si Asterisk está en ejecución en este servidor y desea que el sistema para asegurarse de que se reiniciará en el caso de que se bloquea, es posible que desee considerar la habilitación de esta configuración. Si está habilitado, el sistema comprobará cada minuto para ver si Asterisk está funcionando, y si no lo es intentará reiniciarlo. Este proceso no se ejecutará en los primeros 5 minutos después de que el sistema ha estado funcionando. El valor predeterminado es N.

<BR>
<A NAME="servers-asterisk_temp_no_restart">
<BR>
<B>Temperatura de No-Restart Asterisk -</B> Si Auto-Restart Asterisk está habilitado en este servidor, activar esta opción impedirá que el proceso de rearranque automático se ejecute hasta que se reinicia el servidor. El valor predeterminado es N.

<BR>
<A NAME="servers-active_agent_login_server">
<BR>
<B>Servidor de Agente Activo -</B> Al establecer esta opción de N será evitar que los agentes de la posibilidad de conectarse a este servidor a través de la pantalla del agente. Esto es muy útil cuando se utiliza una configuración de equilibrado de carga de teléfono de acceso. El valor predeterminado es Y.

<BR>
<A NAME="servers-generate_conf">
<BR>
<B>Generate conf files -</B> Si desea que el sistema genere automáticamente conf asterisco en base a las entradas de teléfonos, entradas de soporte y equilibrio de carga de configuración del sistema y establezca dicho a Y. El valor predeterminado es Y.

<BR>
<A NAME="servers-rebuild_conf_files">
<BR>
<B>Reconstruir Archivos de configuración - </B> Si desea forzar una reconstrucción de los archivos de configuración de Asterisk o si alguno de los teléfonos o entradas del proveedor de telefonía han cambiado esto debe establecerse en verdadero (Y). Después de la generación de archivos de configuración de Asterisk, y que este se ha cargado nuevamente  cambia este valor a N. El valor por defecto es Y.

<BR>
<A NAME="servers-rebuild_music_on_hold">
<BR>
<B>Reconstruír Música de Espera -</B> Si desea forzar una reconstrucción de archivos de música de espera, ó si las entradas de música de espera o las entradas de servidor han cambiado, entonces debe establecerse en Y. Después de que los archivos de Música de Espera han sido sincronizados y recargados entonces este valor será cambiado a N. el valor por defecto es Y.

<BR>
<A NAME="servers-sounds_update">
<BR>
<B>Actualización de Sonidos -</B> Si desea forzar una comprobación de los archivos de sonido en este servidor, y el almacén central de audio está activado como una configuración del sistema, este campo permitirán ejecutar el actualizador de sonidos en el siguiente minuto. Toda vez que un archivo de audio se carga desde la interfaz web, este se ajusta automáticamente a Y para todos los servidores que tienen Asterisk activo. el valor por defecto es es N.

<BR>
<A NAME="servers-recording_limit">
<BR>
<B>Límite de grabación -</B> Este campo es donde se establece el número máximo de minutos que una llamada grabación iniciada por el sistema puede ser. Predeterminado es de 60 minutos.

<BR>
<A NAME="servers-carrier_logging_active">
<BR>
<B>Registro Carrier Activo -</B> Esta configuración le permite registrar todos los códigos de retorno para colgar en cualquier lista de marcación saliente, que usted esté haciendo. El valor por defecto es N.

<BR>
<A NAME="servers-custom_dialplan_entry">
<BR>
<B>Entrada Plan de MArcación Personalizado -</B> Este campo permite introducir en todos los elementos del plan de marcación que desee para el servidor, las líneas que se añadirán al contexto por defecto.






<BR><BR><BR><BR>

<B><FONT SIZE=3>conf_templates TABLA</FONT></B><BR><BR>
<A NAME="conf_templates-template_id">
<BR>
<B>ID de plantilla - </B> Este campo necesita tener al menos 2 caracteres y no más de 15 caracteres, sin espacios. Esta es la ID que se utilizará para identificar la plantilla de conf en todo el sistema.

<BR>
<A NAME="conf_templates-template_name">
<BR>
<B>Nombre de plantilla - </B> Este es el nombre descriptivo del archivo de configuración de la plantilla definida.

<BR>
<A NAME="conf_templates-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="conf_templates-template_contents">
<BR>
<B>Plantilla de Contenido - </B> Este campo es donde usted puede entrar en la configuración específica a ser utilizado por todos los teléfonos o los carriers y que están configurados para utilizar esta plantilla de conf. Campos que NO deben incluirse en este cuadro son: secret, accountcode, account, username y mailbox.





<BR><BR><BR><BR>

<B><FONT SIZE=3>server_carriers TABLA</FONT></B><BR><BR>
<A NAME="server_carriers-carrier_id">
<BR>
<B>ID Carrier - </B> Este campo tiene que tener 2 caracteres de longitud y no más de 15 caracteres, sin espacios. Este es el ID que se utilizará para identificar al carrier de esta entrada a través de todo el sistema.

<BR>
<A NAME="server_carriers-carrier_name">
<BR>
<B>Nombre Carrier - </B> Este es el nombre descriptivo de la compañía incorporada.

<BR>
<A NAME="server_carriers-carrier_description">
<BR>
<B>Descripción Carrier -</B> Aquí se colocan comentarios en los archivos de conf asterisk encima del plan de marcación y entradas de cuenta. Máximo 255 caracteres.

<BR>
<A NAME="server_carriers-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="server_carriers-registration_string">
<BR>
<B>Cadena de Registro  - </B> Este campo es donde usted puede incorporar la cadena exacta necesaria en el archivo de configuración  SIP o IAX para registrar al proveedor. Opcional, pero altamente recomendable si tu proveedor permite registro.

<BR>
<A NAME="server_carriers-template_id">
<BR>
<B>ID Plantilla - </B> Este campo opcional permite elegir un archivo de plantilla de configuración para este registro de carrier.

<BR>
<A NAME="server_carriers-account_entry">
<BR>
<B>Account Entry -</B> Este campo se utiliza si no se ha seleccionado una plantilla de usar, y es donde se puede introducir en la configuración de la cuenta específica que se utilizará para este soporte. Si usted va a tomar en las llamadas entrantes de este tronco portadora es posible que desee establecer el contexto = trunkinbound dentro de este campo para que pueda utilizar el proceso DID manejo dentro del sistema.

<BR>
<A NAME="server_carriers-protocol">
<BR>
<B>Protocolo - </B> Este campo permite definir el protocolo a utilizar con el carrier incorporado. Actualmente, sólo IAX y SIP están soportados.

<BR>
<A NAME="server_carriers-globals_string">
<BR>
<B>Cadena Globals - </B> Este campo opcional le permite definir una variable global a utilizar dentro del carrier en el plan de marcación.

<BR>
<A NAME="server_carriers-dialplan_entry">
<BR>
<B>Plan de Marcación - Entrada- </B> Este campo opcional le permite definir un conjunto de entradas de plan de marcación para utilizar con este carrier.

<BR>
<A NAME="server_carriers-server_ip">
<BR>
<B>IP del servidor - </B> Este es el servidor al cuál está asociado especificamente este carrier registrado.

<BR>
<A NAME="server_carriers-active">
<BR>
<B>Activo - </B> Esto define si el carrier será incluido en los archivos de conf auto-generados o no.





<BR><BR><BR><BR>

<B><FONT SIZE=3>TABLA DE CONFERENCIAS</FONT></B><BR><BR>
<A NAME="conferences-conf_exten">
<BR>
<B>Número de Conferencia -</B> Este campo es donde se pone el número dialplan conferencia meetme. También se recomienda que el número meetme en meetme.conf coincide con este número para cada entrada. Esto es para las conferencias en la pantalla del usuario ASTGUICLIENT y se utiliza para la funcionalidad de la licencia-3way-llamada en el sistema.

<BR>
<A NAME="conferences-server_ip">
<BR>
<B>IP del servidor -</B> Menú donde usted selecciona el servidor asterisk que esta conferencia tendrá activo.




<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>SERVER_TRONCALES TABLA</FONT></B><BR><BR>
	<A NAME="server_trunks">
	<BR>
	<B>Trunks Server le permite restringir las líneas de salida que se utilizan en este servidor para la marcación campaña en función de cada campaña. Usted tiene la opción de reservar un número determinado de líneas para ser utilizado por una sola campaña, así como permitir que la campaña entre un poco en sus líneas reservadas en cualquier líneas permanecen abiertas, en tanto en las líneas totales utilizados por el sistema en este servidor es menor que el valor máximo de Trunks. No tener ninguno de estos registros permitirá la campaña que marca la línea de primera para tener tantas líneas como se puede conseguir en el marco del ajuste Max Trunks.</B>
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SYSTEM_SETTINGS TABLA</FONT></B><BR><BR>
<A NAME="settings-use_non_latin">
<BR>
<B>Usar No-Latino -</B> Esta opción permite que usted por defecto que el script de despliege web utilice caracteres UTF8 y que no una familia de caracteres latinos en expresiones regulares, filtrado y presentacion en pantalla. El valor por defecto es 0.

<BR>
<A NAME="settings-webroot_writable">
<BR>
<B>Webroot editable -</B> Este ajuste permite que usted defina si los archivos temporales y los archivos de autentificación deben ser ubicados en el webroot en su web server. El valor por defecto es 1.

<BR>
<A NAME="settings-agent_disable">
<BR>
<B>Agente Disable Desplegar -</B> Este campo se utiliza para seleccionar cuándo mostrar un agente avisos cuando su sesión ha sido desactivado por el sistema, una acción gerente o por una medida externa. El ajuste NOT_ACTIVE deshabilitará el mensaje en la pantalla de los agentes. El ajuste LIVE_AGENTE sólo mostrará el mensaje desactivan cuando se ha eliminado el récord de los agentes auto_calls, como por ejemplo durante la sesión con fuerza o cierre de sesión de emergencia. El valor predeterminado es TODO.

<BR>
<A NAME="settings-frozen_server_call_clear">
<BR>
<B>Exhorta congeladas claras -</B> Esta opción se puede habilitar la capacidad de la página Informes generales y el guión AST_timecheck.pl opcional para limpiar las entradas auto_calls para un servidor de congelados, por lo que no afectan el enrutamiento de llamadas. Por defecto es 0 para discapacitados.

<BR>
<A NAME="settings-allow_sipsak_messages">
<BR>
<B>Allow SIPSAK Messages -</B> Si se establece en 1, esto permitirá que el ajuste de la tabla teléfonos SIPSAK funcione si el teléfono está configurado para el protocolo SIP. El servidor enviará mensajes al teléfono SIP que se mostrará en la pantalla del teléfono cuando está conectado al sistema. Esta característica sólo funciona con teléfonos SIP y requiere la aplicación SIPSAK ser instalado en el servidor web de que el agente se registra en. Por defecto es 0. 

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agente Activar API-</B> Si establece el valor en  1, permitirá el funcionamiento de la interfaz de agente. Por defecto es 0. 

<BR>
<A NAME="settings-admin_home_url">
<BR>
<B>URL Local de Admin -</B> Ésta es la dirección del URL o del sito Web al que se dirigirá tras hacer click en el enlace INICIO en la parte superior de la página admin.php.

<BR>
<A NAME="settings-admin_modify_refresh">
<BR>
<B>Admin Modificar Auto-Refrescar -</B> Este es el intervalo de refresco en segundos de las pantallas modificadas en esta interfaz de administración. Si se establece en 0 se desactivará, establecerlo por debajo de 5 hará la modificación de las pantallas inútiles debido a se actualizarán con demasiada rapidez para cambiar los campos. Esta opción es útil en situaciones en las que más de un manager está controlando ajustes una campaña activa o un grupo de entrada, de modo que los ajustes se actualicen con frecuencia. El valor predeterminado es 0.

<BR>
<A NAME="settings-nocache_admin">
<BR>
<B>Admin No-Cache -</B> Si se establece en 1 pondrá todas las páginas de administración del navegador sin cache, por lo que cada pantalla tiene que ser recargada cada vez que se ve, incluso si se hace click de regreso en el navegador. El valor predeterminado es 0 para deshabilitado.

<BR>
<A NAME="settings-enable_agc_xfer_log">
<BR>
<B>Activar Logfile de Transfer de Agente -</B> Esta opción registrará en un archivo de texto en el servidor web cada llamada transferida a un agente. El valor por defecto es 0, inhabilitado.

<BR>
<A NAME="settings-enable_agc_dispo_log">
<BR>
<B>Habilitar el archivo de registro del Disposición de Agente -</B> Esta opción se conectará a un archivo de registro de texto en el servidor web cada vez que un agente dispone de una llamada. El valor predeterminado es 0, desactivado.

<BR>
<A NAME="settings-timeclock_end_of_day">
<BR>
<B>Reloj Asistencia Fin de Día -</B> Este ajuste define el momento en que todos los usuarios se van a auto-desconexión de la sesión en el Reloj de Asistencia del sistema. Sólo se ejecuta una vez al día. debe ser sólo de 4 dígitos 2 horas y 2 minutos en cifras de 24 horas - Formato hhmm. Por defecto es 0000.

<BR>
<A NAME="settings-default_local_gmt">
<BR>
<B>GMT Local por Defecto -</B> Este ajuste define lo que se utilizará por defecto cuando los nuevos teléfonos y servidores se agregan utilizando esta interfaz web de administración. El valor predeterminado es -5.

<BR>
<A NAME="settings-default_voicemail_timezone">
<BR>
<B>Zona Correo de Voz por defecto -</B> Esta configuración define qué zona se utilizará por defecto cuando los nuevos teléfonos y buzones de voz se crean. La lista de zonas disponibles se toma directamente desde el archivo voicemail.conf. El valor por defecto es Este.

<BR>
<A NAME="settings-agents_calls_reset">
<BR>
<B>Agentes Restablecer Marcaciones -</B> Esta configuración define si los agentes registrados y con registros de llamadas ativas van a ser reiniciados al terminar la jornada. El valor predeterminado es 1 para habilitado.

<BR>
<A NAME="settings-timeclock_last_reset_date">
<BR>
<B>Reloj Asistencia Última Auto-Desconexión - </B> Este campo muestra la fecha de la última auto-desconexión.

<BR>
<A NAME="settings-vdc_header_date_format">
<BR>
<B>Agente Formato Fecha Encabezado en pantalla -</B> Este menú le permite seleccionar el formato de la fecha y la hora que aparece en la parte superior de la pantalla del agente. Las opciones de esta configuración son: por defecto es MS_DASH_24HR<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Por defecto el formato de fecha con el año mes día seguido por 24 horas<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - Formato fecha EE.UU.  mes día año seguido por 24 horas<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Formato fecha Europea  día mes año seguido por 24 horas<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Texto con formato de fecha abreviado mes día seguido por 24 horas<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Por defecto el formato de fecha con el año mes día seguido por 12 horas<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - Formato fecha EE.UU.  mes día año seguido por 12 horas<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Formato fecha Europea  día mes año seguido por 12 horas<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Texto con formato de fecha abreviado mes día seguido por 12 horas<BR>

<BR>
<A NAME="settings-vdc_customer_date_format">
<BR>
<B>Agente Formato Fecha Personalizado en pantalla
 -</B> Este menú le permite seleccionar el formato de la fecha y la hora del cliente que aparece en la parte superior de la sección de información del cliente de la pantalla del agente. Las opciones de esta configuración son: por defecto es AL_TEXT_AMPM<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Por defecto el formato de fecha con el año mes día seguido por 24 horas<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - Formato fecha EE.UU.  mes día año seguido por 24 horas<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Formato fecha Europea  día mes año seguido por 24 horas<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Texto con formato de fecha abreviado mes día seguido por 24 horas<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Por defecto el formato de fecha con el año mes día seguido por 12 horas<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - Formato fecha EE.UU.  mes día año seguido por 12 horas<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Formato fecha Europea  día mes año seguido por 12 horas<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Texto con formato de fecha abreviado mes día seguido por 12 horas<BR>

<BR>
<A NAME="settings-vdc_header_phone_format">
<BR>
<B>Agente Formato Teléfono de Cliente en pantalla -</B> Este menú le permite seleccionar el formato del número de teléfono del cliente que aparece en la sección de estado de la pantalla del agente. Las opciones de esta configuración son: por defecto es US_PARN<BR>
US_DASH 000-000-0000 - USA Numero telefónico separado por guión<BR>
US_PARN (000)000-0000 - USA Numero separado por guión y código de area en paréntesis<BR>
MS_NODS 0000000000 - Sin formato<BR>
UK_DASH 00 0000-0000 - UK Numero telefónico separado por guión con espacio después del código de ciudad<BR>
AU_SPAC 000 000 000 - Australia número telefónico separado con espacios<BR>
IT_DASH 0000-000-000 - Italy Numero telefónico separado por guión<BR>
FR_SPAC 00 00 00 00 00 - France número telefónico separado con espacios<BR>

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agent interface API Access Activo-</B> This option allows you to enable or disable the agent interface API. Default is 0.

<BR>
<A NAME="settings-agentonly_callback_campaign_lock">
<BR>
<B>Bloquear Remarcación en Campaña unicamente a los agentes -</B> Esta opción define si las remarcación AGENTONLY está limitada a la campaña bajo la cual el agente originalmente la creó. Establecer este valor a 1 significa que el agente sólo puede remarcar desde la campaña en que la creó, 0 significa que el agente puede acceder a ellos sin importar en que campaña inició la sesión. Por defecto es 1.

<BR>
<A NAME="settings-sounds_central_control_active">
<BR>
<B>Central de Control de Sonido Activa -</B> Esta opción define si el sistema de sincronización de sonido está activo en todos los servidores. El valor predeterminado es 0 para Inactivo.

<BR>
<A NAME="settings-sounds_web_server">
<BR>
<B>Servidor de Sonidos Web -</B> Este es el nombre del servidor o la dirección IP del servidor web que manejará los archivos de sonido en este sistema, este debe coincidir con el nombre de servidor o dirección IP del equipo que está intentando acceder mediante página audio_store.php o no funcionará . Por defecto es 127.0.0.1.

<BR>
<A NAME="settings-sounds_web_directory">
<BR>
<B>Directorio Sonidos Web -</B> Este nombre de directorio auto-generado es creado aleatoriamente por el sistema como el lugar en que se mantendrá el archivo de audio. Todos los archivos de audio residirán en este directorio.

<BR>
<A NAME="settings-admin_web_directory">
<BR>
<B>Admin Directorio Web -</B> Este es el directorio web que su contenido web administation, como admin.php, son pulg Para averiguar su directorio web de administración, que es todo lo que hay entre el nombre de dominio y el admin.php en la URL de esta página, sin la comenzando y terminando barras.

<BR>
<A NAME="settings-active_voicemail_server">
<BR>
<B>Servidor de Correo de Voz Activo -</B> En sistemas multi-servidor, este es el servidor que se encargará de todas los buzones de correo de voz. Este servidor es también donde la línea telefónica en mensajes generados se cargan desde el 8168 grabaciones.

<BR>
<A NAME="settings-allow_voicemail_greeting">
<BR>
<B>Permitir Seleción de Saludo Correo de Voz -</B> Si se habilita esta opción le permite elegir un archivo de audio del almacen de audio que se reproducirá como el saludo de correo de voz establecido para un buzón de voz específico. Por defecto es 0 para deshabilitado.

<BR>
<A NAME="settings-outbound_autodial_active">
<BR>
<B>Outbound Auto-Dial Activo-</B> Esta opción le permite activar o desactivar la salida de marcación automática en el sistema, el establecimiento de este campo a 0 eliminará las listas y filtros secciones y muchos campos de las pantallas de modificación de la Campaña. Marcación de entrada manual seguirá estando permitida desde la pantalla del agente, pero no hay marcación lista será posible. El valor predeterminado es 1 para activo.

<BR>
<A NAME="settings-disable_auto_dial">
<BR>
<B>Desactivar Auto-Dial -</B> Esta opción sólo es editable por el administrador del sistema. No va a eliminar cualquier opción desde la interfaz web de gestión, pero evitará cualquier marcación automática de clientes potenciales a ocurrir en el sistema. Sólo llamadas salientes Marcación manual provocados directamente por agentes funcionarán si esta opción está activada. Por defecto es 0 para inactivas.

<BR>
<A NAME="settings-auto_dial_limit">
<BR>
<B>Ratio Dial Limit -</B> Este es el límite máximo del nivel de automarcación en la pantalla de la campaña.

<BR>
<A NAME="settings-outbound_calls_per_second">
<BR>
<B>Max llamadas Completas por segundo -</B> Esta configuración determina el número máximo de llamadas que pueden ser colocados por el script de auto-marcación de salida  auto-LLENADA para todos los servidores, por segundo. Debe ser de 1 a 200. El valor por defecto es 40.

<BR>
<A NAME="settings-allow_custom_dialplan">
<BR>
<B>Permitir Entradas Personalizadas al Plan de Marcación -</B> Esta opción le permite introducir líneas personalizadas de plan de marcación en los Menús de Marcación, Servidores y Configuración del Sistema. El valor predeterminado es 0 para inactivos.

<BR>
<A NAME="settings-pllb_grouping_limit">
<BR>
<B>PLLB Límite Agrupación -</B> PLLB (Acceso Teléfono Load Balancing) Límite de Agrupacion de Balanceo de Carga por Acceso Telefónico. Si Agrupación PLLB se encuentra establecido en CASCADING en el nivel de la campaña, este ajuste determinará el número de agentes aceptados en cada servidor a lo largo de todas las campañas. El valor predeterminado es 100.

<BR>
<A NAME="settings-generate_cross_server_exten">
<BR>
<B>Generar Extensiones Telefonicas Entre Servidores  -</B> Esta opción si se establece en 1 generará entradas al plan de marcación para todos los teléfonos en un sistema multi-servidor. El valor predeterminado es 0 para inactivo.

<BR>
<A NAME="settings-user_territories_active">
<BR>
<B>Territorios de Usuario de Activo-</B> Esta configuración le permite habilitar los Territorios Configuración de usuario de la pantalla de modificación de usuario. Esta característica se agregó para permitir una mayor integración con una instalación personalizada Vtiger pero puede tener aplicaciones en el sistema por sí mismo también. Por defecto es 0 para discapacitados.

<BR>
<A NAME="settings-enable_second_webform">
<BR>
<B>Habilitar Segundo Formulario Web -</B> This setting allows you to have a second web form for campaigns and in-groups in the agent interface. Default is 0 for disabled.

<BR>
<A NAME="settings-enable_tts_integration">
<BR>
<B>Activar Integración TTS  -</B> Esta configuración le permite habilitar la integración Texto a Voz (TextToSpeach) con Cepstral. Esto sólo está disponible actualmente para las campañas de tipo de encuesta de salida. Por defecto es 0 para desactivado.

<BR>
<A NAME="settings-callcard_enabled">
<BR>
<B>Habilitar CallCard -</B> Esta configuración permite que las características CallCard para permitir llamadas de usar números de pin y card_ids que tienen un saldo de minutos y dichos saldos pueden tener agente de tiempo de conversación en llamadas de clientes a en grupos deducidos. Por defecto es 0 para discapacitados.

<BR>
<A NAME="settings-custom_fields_enabled">
<BR>
<B>Habilitar Lista de Campos Personalizados -</B> Esta configuración permite que la característica de campos de lista personalizados que está permitida para los campos de datos personalizados sea definida en la interfaz web de administración en función de cada lista y luego tener aquellos campos disponibles en una ficha de formulario para el agente en su interfaz web. El valor predeterminado es 0 para desactivado.

<BR>
<A NAME="settings-test_campaign_calls">
<BR>
<B>Habilitar Marcaciones de Prueba en Campaña -</B> Esta configuración permite la posibilidad de ingresar un código de teléfono y número de teléfono a los campos en la parte inferior de la pantalla Detalle de Campaña y hacer una llamada a ese número como si fuera un contacto que está siendo auto-marcado en el sistema. El número de teléfono será almacenado como un nuevo contacto en la lista de Marcación Manual. La campaña debe estar activa para que esta función esté habilitada, y se recomienda que las listas asignadas a la campaña esten todas establecidas como inactivas. El prefijo de marcación, el retardo de marcación y todas las demás funciones de marcación relacionadas, a excepción de DNC y opciones de tiempo de marcación, afectarán la marcación del número de prueba. La llamada se coloca en el servidor seleccionado como el servidor de correo de voz en la configuración del sistema. El valor predeterminado es 0 para deshabilitado.

<BR>
<A NAME="settings-expanded_list_stats">
<BR>
<B>Habilitar Estadisticas de Lista Expandida -</B> Esta configuración permite que dos columnas adicionales sean desplegadas en la mayoría de los cuadros de detalle de lista de estado, en las páginas de modificación de campaña y modificacion de lista. La penetración se define como el porcentaje de contactos que están en o por encima del límite de Conteo de Marcaciones de Campaña de llamadas y-ó el estado está marcado como Completado. El valor predeterminado es 1 para habilitado.

<BR>
<A NAME="settings-country_code_list_stats">
<BR>
<B>Con Código de País Lista Estadísticas -</B> Este ajuste si está habilitado mostrará un resumen desglose código de país en la lista de modificar la pantalla. Por defecto es 0 para discapacitados.

<BR>
<A NAME="settings-enhanced_disconnect_logging">
<BR>
<B>Desconectar Mejorado -</B> Esta opción habilita el registro de llamadas que reciben una señal de CONGESTION con código de causa 1, 19, 21, 34 o 38. Por lo general, no se recomienda habilitar esto en los EE.UU.. Por defecto es 0 para deshabilitado.

<BR>
<A NAME="settings-campaign_cid_areacodes_enabled">
<BR>
<B>Habilitar CID CódigoÁrea Campaña  -</B> Esta configuración permite la posibilidad de configurar los números específicos de salida de ID de llamadas para ser utilizados por campaña. El valor predeterminado es 1 para habilitado.

<BR>
<A NAME="settings-did_ra_extensions_enabled">
<BR>
<B>Habilitar Invalidaciones de Extensión Agente Remoto -</B> Esta configuración permite DID tener invalidaciones de extensiones para llamadas de agentes remotos enrutadas a grupos de entrada. El valor predeterminado es 0 para deshabilitado.

<BR>
<A NAME="contact_information">
<A NAME="settings-contacts_enabled">
<BR>
<B>Contactos Activado -</B> Esta configuración activa la sub-sección Contactos en Admin lo cual permite a un manager añadir modificar o eliminar contactos en el sistema que pueden ser utilizados como parte de una Transferencia Personalizada en una campaña, donde un agente puede buscar contactos por nombre, primer apellido o número de oficina, y luego seleccionar uno de los muchos números asociados con ese contacto. Esta característica se utiliza a menudo por los operadores o en las funciones de central telefónica donde el usuario necesita transferir una llamada a un teléfono que no sea de agente. El valor predeterminado es 0 para deshabilitado.

<BR>
<A NAME="settings-call_menu_qualify_enabled">
<BR>
<B>Encabezado Menu de Marcación Activado -</B> Esta configuración permite la opción en los Menús de Marcación, la posibilidad de agregar un Encabezado SQL a las personas que escuchan ese Menú de Marcación. Para obtener más información sobre cómo funciona esta característica, consulte la ayuda de los Menús de Marcación. Por defecto es 0 para desactivados.

<BR>
<A NAME="settings-level_8_disable_add">
<BR>
<B>Añadir Restricción de Nivel 8 -</B> Si se activa esta configuración evitará que cualquier usuario de nivel 8 añada o copie cualquier archivo en el sistema, sin importar cuáles son sus opciones de usuario. Excluidas de estas restricciones estan la capacidad de agregar DNC, numeros de Grupos de Telefonos Filtrados, y la página agregar nuevo contacto. El valor por defecto es 0 para desactivado.

<BR>
<A NAME="settings-admin_list_counts">
<BR>
<B>Admin Lista de Contadores -</B> Esta opción le permite desactivar la lista de contadores que aparecen en el listado de Listas y las pantallas de Modificación de Campaña. El valor predeterminado es 1 para activado.

<BR>
<A NAME="settings-allow_emails">
<BR>
<B>Permitir mensajes de correo electrónico -</B> Aquí es donde usted puede establecer si este sistema será capaz de recibir mensajes de correo electrónico entrantes, además de llamadas telefónicas.

<BR>
<A NAME="settings-first_login_trigger">
<BR>
<B>Lanzamiento de Primera Sesión -</B> Esta configuración permite la configuración inicial de la pantalla del servidor para ser mostrado al administrador cuando se registre por primera vez en el sistema.

<BR>
<A NAME="settings-default_phone_registration_password">
<BR>
<B>Contraseña predeterminada de Registro de Teléfono -</B> Esta es la contraseña de registro predeterminado que se utiliza cuando los teléfonos nuevos se agregan al sistema. El valor predeterminado es test.

<BR>
<A NAME="settings-default_phone_login_password">
<BR>
<B>Clave Acceso PRedeterminada de Teléfono -</B> Este es la contraseña de acceso predeterminada para el teléfono web, utilizada cuando teléfonos nuevos se agregan al sistema. El valor predeterminado es test.

<BR>
<A NAME="settings-default_server_password">
<BR>
<B>Contraseña predeterminada del servidor -</B> Esta es la contraseña del servidor predeterminado que se utiliza cuando los servidores se añaden al sistema. El valor predeterminado es test.

<BR>
<A NAME="settings-slave_db_server">
<BR>
<B>Servidor de Base de Datos Esclavo -</B> Si usted tiene un servidor esclavo MySQL entonces, introduzca aquí la dirección IP local para este servidor. Esta opción actualmente se utiliza para los informes seleccionados en la siguiente opción y no tiene nada que ver con la réplica de configuración automática maestro-esclavo de MySQL. El valor predeterminado es vacío para deshabilitado.

<BR>
<A NAME="settings-reports_use_slave_db">
<BR>
<B>Informes de Uso de DB Esclavo -</B> Esta opción le permite seleccionar los informes que desea tener utilice la base de datos MySQL de esclavos como se define en la opción anterior en lugar de la base de datos principal de que el sistema en vivo se está ejecutando. Debe configurar el esclavo de replicación de MySQL antes de habilitar esta opción. Por defecto está vacía para discapacitados.

<BR>
<A NAME="settings-default_field_labels">
<BR>
<B>Etiquetas de Campo Predeterminadas -</B> Estos 19 campos le permiten definir el nombre que aparecerá en la interfaz del agente, así como la página principal de modificación administrativa. Por defecto está vacío, que utilizará los valores predeterminados codificados en la interfaz del agente. También puede establecer una etiqueta para --- OCULTAR --- para ocultar la etiqueta y el campo.

<BR>
<A NAME="settings-label_hide_field_logs">
<BR>
<B>Ocultar etiqueta en los registros de llamadas -</B> Si una etiqueta se establece en ---OCULTAR--- entonces los registros de marcación de agentes, si está activado en la campaña, aún mostrarán el campo y los datos a menos que esta opción esté establecida en Y. El valor predeterminado es N.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>CC Características Activas - </B> Esta opción le permite activar o desactivar el control de calidad o las características de Control de Calidad. El valor por defecto es 0 para inactivo.

<BR>
<A NAME="settings-default_webphone">
<BR>
<B>Webphone Predeterminado  -</B> Si se establece en 1, esta opción hará que todos los nuevos teléfonos creados sean establecidos con Activo como Webphone. El valor por defecto es 0.

<BR>
<A NAME="settings-webphone_systemkey">
<BR>
<B>Webphone Clave del sistema -</B> Si su sistema o proveedor lo requiere, aquí es donde la clave del sistema para el webphone deben introducirse. el valor por defecto es vacío.

<BR>
<A NAME="settings-default_codecs">
<BR>
<B>Códecs Predeterminados -</B> Se puede definir una lista de codecs separados por coma, que se establecerán como los codecs por defecto para todos los sistemas. Opciones para los codecs son ulaw, alaw, gsm, G729, Speex, G722, G723, G726, iLBC, ... Por defecto está vacío.

<BR>
<A NAME="settings-custom_dialplan_entry">
<BR>
<B>Entrada Plan de MArcación Personalizado -</B> Este campo le permite introducirce en cualquier elemento del plan de marcación que desee, para todos los servidores Asterisk, las líneas se añadirán al contexto por defecto.

<BR>
<A NAME="settings-reload_dialplan_on_servers">
<BR>
<B>Recargar Plan de Marcación en Servidores -</B> Esta opción le permite forzar una recarga del plan de marcación en todos los servidores de Asterisk en el clúster. Si ha realizado cambios en la Entrada de Plan de Marcación personalizado debe establecer este valor a 1 y remitir para que los cambios entren en vigor en los servidores.

<BR>
<A NAME="settings-noanswer_log">
<BR>
<B>Registro de No Respuesta -</B> Esta opción registrará las llamadas de marcado automático que no se responden en una tabla separada. El valor predeterminado es N.

<BR>
<A NAME="settings-did_agent_log">
<BR>
<B>DID Registro de Agentes -</B> Esta opción registrará las llamadas entrantes DID con un grupo de entrada y un ID de usuario, si es aplicable, a una tabla separada. El valor predeterminado es N.

<BR>
<A NAME="settings-alt_log_server_ip">
<BR>
<B>Servidor DB Registro-Alterno -</B> Este es el servidor de registro de base de datos alternativa. Esto es opcional, y permite que algunos registros que se escriben en una base de datos separada. Por defecto está vacío.

<BR>
<A NAME="settings-tables_use_alt_log_db">
<BR>
<B>Tablas Registro-Alterno -</B> Estas son las tablas que están disponibles para el registro en el servidor de registro de base de datos alterno. El valor predeterminado es en blanco.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>IP Servidor Externa Predeterminado -</B> Si se establece en 1, esta opción hará que todos los nuevos teléfonos creados utilicen la configuracion de IP de Servidor Externo establecida en Y. El valor predeterminado es 0.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>URL Webphone -</B> Esta es la URL del Webphone que se utilizará con este sistema, si está habilitado en el registro de los teléfonos que un agente este utilizando. Por defecto está vacío.

<BR>
<A NAME="settings-enable_queuemetrics_logging">
<BR>
<B>Enable QueueMetrics Logging -</B> Esta configuración le permite definir si el sistema insertará entradas del registro en la tabla de base de datos como la actividad queue_log Asterisk Colas hace. QueueMetrics es un independiente, de código cerrado el programa de análisis estadístico. Usted debe haber QueueMetrics instalado y configurado antes de habilitar esta característica. Por defecto es 0.

<BR>
<A NAME="settings-queuemetrics_server_ip">
<BR>
<B>QueueMetrics IP Servidor  -</B> Esta es el la dirección IP de la base de datos para su instalación de QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_dbname">
<BR>
<B>Nombre DB QueueMetrics -</B> Este es el nombre de la base de datos para su base de análisis con QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_login">
<BR>
<B>QueueMetrics Acceso DB  -</B> Este es el nombre del usuario usado para registrar una sesión en la base de datos QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_pass">
<BR>
<B>QueueMetrics Contraseña DB -</B> Ésta es la contraseña usada registrar una sesión en la base de datos QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_url">
<BR>
<B>QueueMetrics  URL -</B> Ésta es la dirección del URL o sitio Web usado para enlazar la instalación de QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_log_id">
<BR>
<B>QueueMetrics Log ID -</B> Este es el ID de servidor que todos los centros de contacto Troncos de entrar en la base de datos QueueMetrics utilizará como identificador para cada registro.

<BR>
<A NAME="settings-queuemetrics_eq_prepend">
<BR>
<B>QueueMetrics EnterQueue Prepend -</B> Este campo se utiliza para permitir prepending de uno de los campos de datos de lista en frente del número de teléfono del cliente para los informes QueueMetrics personalizados. El valor predeterminado es NONE para no rellenar nada.

<BR>
<A NAME="settings-queuemetrics_loginout">
<BR>
<B>QueueMetrics Sesión Entrada-Salida -</B> Esta opción afecta a cómo el sistema registrará los ingresos y salidas de un agente en el queue_log. Predeterminado es NORMAL para utilizar estándar AgentLogin AGENTLOGOFF, CALLBACK utilizará AgentCallbackLogin y AGENTCALLBACKLOGOFF que QM analizará de manera diferente, NINGUNO no registrará ningún ingresos y salidas dentro queue_log.

<BR>
<A NAME="settings-queuemetrics_callstatus">
<BR>
<B>QueueMetrics Estados de Marcación -</B> Esta opción, si se pone a 0 no pondrá la entrada CALLSTATUS en queue_log cuando un agente dispone de una llamada. El valor predeterminado es 1 para habilitado.

<BR>
<A NAME="settings-queuemetrics_addmember_enabled">
<BR>
<B>QueueMetrics Activado Añadir Miembro -</B> Esta opción, si se establece a 1 generará las entradas ADDMEMBER2 y  REMOVEMEMBER en queue_log. El valor predeterminado es 0 para deshabilitado.

<BR>
<A NAME="settings-queuemetrics_dispo_pause">
<BR>
<B>QueueMetrics Código Pausa de Disposición -</B> Esta opción, si se rellena, le permite definir si un código de pausa de disposición ha entrado en queue_log cuando un agente se encuentra en estado de disposición . El valor predeterminado es vacío para deshabilitado.

<BR>
<A NAME="settings-queuemetrics_pause_type">
<BR>
<B>QueueMetrics Tipo Pausa Logging -</B> Si se activa esta opción, se registrará el tipo de pausa en el campo Data5 mesa queue_log. Usted debe asegurarse de que usted tiene un campo Data5 o de activar esta característica se romperá la compatibilidad QM. Por defecto es 0 para discapacitados.

<BR>
<A NAME="settings-queuemetrics_pe_phone_append">
<BR>
<B>QueueMetrics Entorno Telefono AnexarTeléfono -</B> Esta opción, si está habilitada, anexará el registro de acceso telefonico del agente al campo data4 en la tabla de registro de cola si el campo Entorno Telefonico de Campaña está relleno. El valor predeterminado es 0 para deshabilitado.

<BR>
<A NAME="settings-queuemetrics_record_hold">
<BR>
<B>QueueMetrics Mantenga registro de llamadas -</B> Esta opción, si está activado, se registrará cuando un cliente se pone en espera y saldrá de espera en la tabla QM record_tags más reciente. Por defecto es 0 para discapacitados.

<BR>
<A NAME="settings-queuemetrics_socket">
<BR>
<B>QueueMetrics Enviar a Socket -</B> Esta opción, si está activada, enviará los datos de QM a una página web que va a enviar a través de un socket para su registro. La opción CONNECT_COMPLETE enviará los eventos CONNECT, COMPLETEAGENTE y COMPLETECALLER a la url definida a continuación. El valor predeterminado es NONE para deshabilitado.

<BR>
<A NAME="settings-queuemetrics_socket_url">
<BR>
<B>QueueMetrics URL de Envío a Socket -</B> Si Enviar a Socket está habilitado anteriormente, esta es la URL que se utiliza para enviar los datos. El valor por defecto es EMPTY para deshabilitado.

<BR>
<A NAME="settings-enable_vtiger_integration">
<BR>
<B>Enable Vtiger Integration -</B> Este ajuste le permite activar la integración Vtiger con el sistema. Actualmente enlaza con Vtiger admin y búsqueda, así como la replicación de usuarios son las únicas características de integración disponibles. Por defecto es 0.

<BR>
<A NAME="settings-vtiger_server_ip">
<BR>
<B>Vtiger IP Servidor de DB- </B> Este es la dirección IP de la base de datos para su instalación de Vtiger.

<BR>
<A NAME="settings-vtiger_dbname">
<BR>
<B>Vtiger Nombre base de datos - </B> Este es el nombre de base de datos de su instalación Vtiger.

<BR>
<A NAME="settings-vtiger_login">
<BR>
<B>Vtiger REgistro en DB - </B> Este es el nombre de usuario utilizado para acceder a su base de datos Vtiger.

<BR>
<A NAME="settings-vtiger_pass">
<BR>
<B>Vtiger Contraseña DB- </B> Esta es la contraseña utilizada para acceder a su base de datos Vtiger.

<BR>
<A NAME="settings-vtiger_url">
<BR>
<B>Vtiger URL - </B> Este es el URL o dirección del sitio web utilizado para acceder a su instalación Vtiger.


<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUSES TABLA</FONT></B><BR><BR>
<A NAME="system_statuses">
<BR>
<B>Mediante el uso de estados del sistema, usted puede tener estados que existen para todas las campañas y en grupos. El Estado debe ser 1-6 caracteres de longitud, la descripción debe ser 2-30 caracteres de longitud y seleccionable define si aparece en el sistema como una disposición del agente. El campo human_answered se utiliza en el cálculo del porcentaje de descenso, o tasa de abandono. Ajuste human_answered a Y utilizará este estado cuando se cuentan las llamadas humanos-contestó. La opción Categoría le permite agrupar varios estados en una categoría que se puede utilizar para el análisis estadístico. También hay 5 opciones adicionales que van a definir el tipo de estado: la venta, dnc, contacto con el cliente, no le interesa, inviable, devolución de llamada programada.</B>



<BR><BR><BR><BR>

<B><FONT SIZE=3>SCREEN_LABELS TABLA</FONT></B><BR><BR>
<A NAME="screen_labels">
<BR>
<B>Screen labels give you the option of setting different labels for the default agent screen fields on a por campaña basis.</B>

<A NAME="screen_labels-label_id">
<BR>
<B>ID Etiqueta de Pantalla -</B> Este campo necesita tener al menos 2 caracteres y no más de 20 caracteres, sin espacios ni caracteres especiales. Este es el ID que se utilizará para identificar la etiqueta de pantalla en todo el sistema.

<BR>
<A NAME="screen_labels-label_name">
<BR>
<B>Nombre Etiqueta de Pantalla -</B> Este es el nombre descriptivo de la entrada de etiqueta de pantalla.

<BR>
<A NAME="screen_labels-user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativos para este registro, lo cual permite la visualización administrativa de este registro, restringido por grupo de usuarios. El valor predeterminado es --ALL-- que permite a cualquier usuario de administración, la consulta de este registro.

<BR>
<A NAME="screen_labels-label_hide_field_logs">
<BR>
<B>Ocultar etiqueta en los registros de llamadas -</B> Si una etiqueta se establece en ---OCULTAR--- entonces los registros de marcación de agentes, si está activado en la campaña, aún mostrarán el campo y los datos a menos que esta opción esté establecida en Y. El valor predeterminado es N.

<BR>
<A NAME="screen_labels-default_field_labels">
<BR>
<B>Etiquetas de Campo Predeterminadas -</B> Estos 19 campos le permiten definir el nombre que aparecerá en la interfaz del agente, así como la página principal de modificación administrativa. Por defecto está vacío, que utilizará los valores predeterminados codificados en la interfaz del agente. También puede establecer una etiqueta para --- OCULTAR --- para ocultar la etiqueta y el campo.



<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUS_CATEGORIES TABLA</FONT></B><BR><BR>
<A NAME="status_categories">
<BR>
<B>Con el uso de las categorías del estado de sistema, puede agrupar todos los estados para considerarlos en análisis estadístico en un grupo de estados. La CAtegoría ID debe tener de 2 a 20 caracteres, sin espacios, el nombre debe ser de 2 a 50 caracteres, la descripción es opcional y Despliege de TimeonVDAD define si ese estado será uno de hasta 4 estados que pueden ser calculados y desplegados en el reporte Horarios en VDAD en Tiempo Real.</B> Las Categorías Venta y Contacto Muerto son utilizadas tanto por el sistema de lista de sugerencias en el análisis de estadísticas de la lista.



<BR><BR><BR><BR>
<?php
if ($SSallow_emails>0)
	{
?>
<B><FONT SIZE=3>EMAIL ACCOUNTS</FONT></B><BR><BR>
<A NAME="email_accounts">
<BR>
<B>La sección de gestión de cuentas de correo electrónico le permite crear, copiar y eliminar cuenta de correo electrónico que le permitirán tiene mensajes de correo electrónico entran en su sistema y ser tratados como si fueran llamadas telefónicas a los agentes. CUENTAS DE CORREO ELECTRÓNICO debe configurarse POR USTED Y UN PROVEEDOR DE SERVICIO DE CORREO ELECTRÓNICO - QUE NO ESTÁ CUBIERTO POR ESTE MODULO.</B>

<BR>
<A NAME="email_accounts-email_account_id">
<BR>
<B>Cuenta Correo Electrónico ID -</B> Este es el nombre corto de la cuenta de correo electrónico, no se puede editar después de la remisión inicial, no debe contener espacios y debe tener entre 2 y 20 caracteres de longitud.

<BR>
<A NAME="email_accounts-email_account_name">
<BR>
<B>Cuenta Correo Electrónico Nombre -</B> Este es el nombre completo de la cuenta de correo electrónico, debe tener entre 2 y 30 caracteres de longitud. 

<BR>
<A NAME="email_accounts-email_account_active">
<BR>
<B>Activo -</B> Esto determina si esta cuenta será verificada para nuevos mensajes de correo electrónico que se cargarán en el sistema de marcación. 

<BR>
<A NAME="email_accounts-email_account_description">
<BR>
<B>Cuenta Correo Electronico Descripción -</B> This allows for a lengthy description, if needed, of the email account.  255 characters max. 

<BR>
<A NAME="email_accounts-email_account_type">
<BR>
<B>cuenta Correo Electrónico Tipo -</B> Specifies whether the account is used for inbound or outbound email messages.  Should be set to INBOUND. 

<BR>
<A NAME="email_accounts-admin_user_group">
<BR>
<B>Admin Grupo de Usuarios -</B> Este es el grupo de usuarios administrativo para este grupo entrante, esto permite visualización administrativa de este grupo de entrada restringido por grupo de usuarios. El valor predeterminado es --ALL-- la cual permite a cualquier usuario de administración, la consulta de este grupo de entrada.  

<BR>
<A NAME="email_accounts-protocol">
<BR>
<B>Cuenta Correo Electrónico Protocolo -</B> This is the email protocol used by the account you are setting up access to.  Currently only IMAP and POP3 accounts are supported.  

<BR>
<A NAME="email_accounts-email_replyto_address">
<BR>
<B>Correo Electrónico Dirección de Respuesta -</B> The email address of the account you are setting up access to.  Replies to email messages from the agent interface will read as coming from this address.  

<BR>
<A NAME="email_accounts-email_account_server">
<BR>
<B>Cuenta Correo Electrónico Servidor -</B> El servidor en que la cuenta de correo electrónico está localizado.  

<BR>
<A NAME="email_accounts-email_account_user">
<BR>
<B>Cuenta Correo Electrónico Usuario -</B> The login used to access this account.  Usually it's the portion of the reply-to address before the -at- symbol.  

<BR>
<A NAME="email_accounts-email_account_pass">
<BR>
<B>Cuenta Correo Electrónico Contraseña -</B> The password used to access this account.  This is usually set at the time the email account is created.  

<BR>
<A NAME="email_accounts-email_frequency_check_mins">
<BR>
<B>Correo Electronico Frequency Check Rate (mins) -</B> How often this email account should be checked.  The highest rate of frequency at the moment is five minutes; some email providers will not allow more than three login attempts in fifteen minutes before locking the account for an indeterminate amount of time.  

<BR>
<A NAME="email_accounts-in_group">
<BR>
<B>Grupo-Entrada ID -</B> El grupo de entrada  al que que los mensajes de correo electrónico se enviarán.   

<BR>
<A NAME="email_accounts-default_list_id">
<BR>
<B>Lista de ID Predeterminada -</B> Lista de ID Predeterminada en que se insertarán los prospectos en caso de ser necesario.  

<BR>
<A NAME="email_accounts-call_handle_method">
<BR>
<B>Grupo de Entrada Método de Manejo de Marcación -</B> Esta es la acción que se tomará cuando un nuevo correo electrónico se encuentra en el relato. EMAIL significa todos los mensajes de correo electrónico se pueden insertar en la tabla de la lista como una nueva pista. EMAILLOOKUP buscará en toda la tabla de lista de la dirección de correo electrónico en la columna de la dirección de correo electrónico - si se encuentra a la cabeza, esa lista plomo ID se utilizará en el expediente que se dedica a la mesa email_list. EMAILLOOKUPRC hace lo mismo, pero sólo buscará listas pertenecientes a la campaña seleccionada en el cuadro ID Campaña In-Grupo de abajo. EMAILLOOKUPRL sólo buscará una lista en particular, que es el que entró en el cuadro A-Group ID de Lista abajo.  

<BR>
<A NAME="email_accounts-agent_search_method">
<BR>
<B>Grupo-Entrada Método Búsqueda de Agentes -</B> El método de búsqueda de agentes para ser utilizado por el grupo entrante, LO se balancea la carga-Desbordamiento y tratará de enviar la llamada a un agente en el servidor local antes de intentar enviar a un agente en otro servidor, LB se balancea la carga y tratará de enviar la llamada a otro agente no importa qué servidor que se encuentran, por lo que es servidor de sólo y únicamente tratará de enviar las llamadas a los agentes en el servidor que recibió la llamada en adelante. El valor predeterminado es LB. <B>IS THIS NECESSARY?</B>  

<BR>
<A NAME="email_accounts-ingroup_list_id">
<BR>
<B>Grupo-Entrada ID de Lista -</B> Este el ID de Lista que se utilizará para buscar coincidencias de contenido.  

<BR>
<A NAME="email_accounts-ingroup_campaign_id">
<BR>
<B>Grupo-entrada ID de Campaña -</B> Este es el ID de Campaña que se utilizará para buscar coincidencias de contenido.  




<BR><BR><BR><BR>
<?php } ?>
<B><FONT SIZE=3>CUSTOM TEMPLATE MAKER</FONT></B><BR><BR>
<A NAME="template_maker">
<BR>
El fabricante de plantilla personalizada le permite definir sus propios diseños de archivo para su uso con la lista de cargadores y eliminarlos, si es necesario. Si suele subir archivos que se encuentran en un diseño coherente que no sea el diseño estándar, puede encontrar útil esta herramienta. El diseño guardado funcionará en cualquier archivo cargado coincide, independientemente del tipo de archivo o delimitador.

<BR>
<A NAME="template_maker-create_template">
<BR>
<B>Crear nueva plantilla - </B>In order to begin creating your new listloader template, you must first load a lead file that has the layout you wish to create the template for.  Click "Choose file", and open the file on your computer you wish to use.  This will upload a copy to your server and process it to determine the file type and delimiter (for TXT files).

<BR>
<A NAME="template_maker-delete_template">
<BR>
<B>Borrartemplate -</B> If you have a template you no longer use or you mis-entered information on it and would like to re-enter it, select the template from the drop-down menu and click "BORRAR PLANTILLA".

<BR>
<A NAME="template_maker-template_id">
<BR>
<B>ID Plantilla -</B> This field is where you enter an arbitrary ID for your new custom template.  It must be between 2 and 20 characters and consist of alphanumeric characters and underscores.

<BR>
<A NAME="template_maker-template_name">
<BR>
<B>Nombre Plantilla -</B> This field is where you enter the name for your new custom template.  Can be up to 30 characters long.

<BR>
<A NAME="template_maker-template_description">
<BR>
<B>descripción de plantillas -</B> This field is where you enter the description for your new custom template.  It can be up to 255 characters long.

<BR>
<A NAME="template_maker-list_id">
<BR>
<B>ID de Lista -</B> All templates must load their records into a list.  Select a list ID to load leads into from this drop-down list, which will display any lists available to you given your user settings.

<BR>
<A NAME="template_maker-assign_columns">
<BR>
<B>Assigning columns -</B> Once you have loaded a sample lead file matching the layout you want to make into a template and select a list ID to load leads into, all of the available columns from the list table and the custom table for the list you selected (if any) will be displayed here.  Columnas highlighted in blue are standard columns from the list table.  Columnas highlighted in pink belong to the custom table for the selected list.  Each column listed has a drop-down menu, which should be populated with the fields from the first row of the sample lead file you uploaded.  Assign the appropriate fields to the appropriate columns and press REMITIR PLANTILLA to create your template.  You do not need to assign every field to a column, and you do not need to assign every column a field.  For details on the standard list columns, click <a href="#list_loader">HERE</a>.


<BR><BR><BR><BR>

<A NAME="max_stats">
<B><FONT SIZE=3>Reportes Estadísticas de Sistema Máximas</FONT></B><BR><BR>
<BR>
<B>Estas estadísticas son totales reservados que se almacenan a lo largo de cada día en tiempo real a través de procesos secundarios. Para las llamadas entrantes, las llamadas totales por grupo de entrada se calculan para cada llamada que entra en proceso. Para el recuento general del sistema, los totales son generados a partir de entradas de registro, así como de totales adicionales de grupo de entrada y de campaña. Estos totales pueden no sumar debido a la configuración que tienes en tu sistema, así como cuando la llamada se cuelga.</B>


<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>QC STATUS CODES</FONT></B><BR><BR>
	<A NAME="qc_status_codes">
	<BR>
	<B>El Control de Calidad - Función de control de calidad tiene su propio conjunto de códigos de estado separados de los de la gestión de llamadas funciones del sistema. Códigos de estado de control de calidad deben estar entre 2 y 8 caracteres de longitud y no contienen caracteres especiales como un espacio o colon. La descripción del código de estado de control de calidad debe estar entre 2 y 30 caracteres de longitud. Para utilizar estas funciones, debe tener activado QC en la configuración del sistema.</B>
	<?php
	}
?>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Informes</FONT></B><BR><BR>

<A NAME="agent_time_detail">
<BR>
<B>Detalle Horario Agente -</B> In this report you can view how much time agents spent on what.<BR>
<U>TIME CLOCK</U> = Time the agent been logged in to the time clock.<BR>
<U>AGENTE TIME</U> = Totaltime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
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
<B>Agent Status Detalle -</B> In this report you can view what and how many statuses has been selected by the agents.<BR>
<U>CALLS</U> = Totalnumber of calls sent to the user.<BR>
<U>CIcalls</U> = Totalnumber of call where there was a Respuesta Humana which is set under "Admin" -> "System Estados".<BR>
<U>DNC/CI%</U> = How much in percent DNC (Do Not Call) per Respuesta Humanas.<BR>
And the rest is just Estados de Sistemathat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Estados".<BR>

<A NAME="agent_performance_detail">
<BR>
<B>Agent Performance Detalle -</B> This is a combination of Detalle Horario Agente and Agent Status Detalle.<BR>
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
And the rest is just Estados de Sistemathat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Estados".<BR>
- Next table is Códigos de Pausa.<BR>
<U>TOTAL</U> = Totaltime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
<U>NONPAUSE</U> = Everything except pause (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U>).<BR>
<U>PAUSE</U> = Only Pause.<BR>
- The last table is pause codes and their time (like "Detalle Horario Agente").<BR>
<U>LOGIN</U> = The pause code when going from login directly to pause.<BR>
<U>LAGGED</U> = The time the agent had some network problem or similar.<BR>
<U>ANDIAL</U> = This pause code triggers if the agent been on dispo screen for longer than 1000 seconds.<BR>
and empty is undefined pause code. <BR>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Nanpa cellphone filtering</FONT></B><BR><BR>


<A NAME="nanpa-running">
<BR>
<B>Currently running NANPA scrubs -</B> Desplegars a log of the currently running scrubs, including: Start Time, Leads Count, Filter Count, Status Line, Time to Complete, Field Updated, and Field excluded
<BR><BR>
<A NAME="nanpa-settings">
<BR>
<B>Inactive Listas -</B> Contains all the listas inactivas, that are eligible to be scrubbed.  A list can only be scrubbed if Activois set to N on the list.
<BR><BR>
<B>Field to Update -</B> Indicates which lead field will contain the scrub result -Cellphone, Landline, or Invalid-.  If NONE is selected it will use the default field País_Code.
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
FIN
</TD></TR></TABLE></BODY></HTML>
<?php
exit;

#### END HELP SCREENS
