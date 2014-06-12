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
echo "<TABLE WIDTH=98% BGCOLOR=#E6E6E6 cellpadding=2 cellspacing=0><TR><TD ALIGN=LEFT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=4><B>ADMINISTRATION: AJUDA<BR></B></FONT><FONT FACE=\"ARIAL,HELVETICA\" COLOR=BLACK SIZE=2><BR><BR>\n";

?>
<B><FONT SIZE=3>Tabela de usuários</FONT></B><BR><BR>
<A NAME="users-user">
<BR>
<B>Usuário ID -</B> Este campo é onde você coloca o número usuários de identificação, pode ser de até 8 dígitos de comprimento, deve ser pelo menos 2 caracteres.

<BR>
<A NAME="users-pass">
<BR>
<B>Password -</B> Este campo é onde você coloca a senha de usuários. Deve ter pelo menos dois caracteres. A senha do usuário forte deve ter pelo menos 8 caracteres de comprimento e têm minúsculas e maiúsculas, bem como pelo menos um número.

<BR>
<A NAME="users-force_change_password">
<BR>
<B>Forçar alteração de senha -</B> Se esta opção estiver definida para Y, então o usuário será solicitado a alterar sua senha na próxima vez que efetuar login na página de administração. O padrão é N.

<BR>
<A NAME="users-last_login_date">
<BR>
<B>Último Acesso Informações -</B> Isso mostra a última data e hora tentativa de login, e se houve uma recente tentativa de login falhou. Se este modificar formulário do usuário é submetido, então o contador de login tentativa fracassada serão repostas eo agente pode imediatamente tentar logar novamente. Se um agente tem 10 tentativas de login em uma linha, em seguida, eles não podem tentar se conectar novamente por pelo menos 15 minutos a menos que sua conta é redefinir manualmente.

<BR>
<A NAME="users-full_name">
<BR>
<B>Nome Completo -</B> Este campo é onde você coloca o nome completo usuários. Deve ter pelo menos dois caracteres de comprimento.

<BR>
<A NAME="users-user_level">
<BR>
<B>Nível do Usuário -</B> Este menu é onde você seleciona o nível de usuário usuários. Deve haver um nível de 1 para entrar no ecrã do agente, deve ser de nível superior a 2 para iniciar a sessão como mais próximo, deve estar no nível do usuário 8 ou superior para entrar na seção de administração web.

<BR>
<A NAME="users-user_group">
<BR>
<B>Grupo do Usuário -</B> Este menu é onde você seleciona o grupo de usuários que este usuário irá pertencer. Existem vários recursos de tela agente que podem ser controlados por meio de configurações de grupos de usuários. Se este campo for deixado em branco, em seguida, o usuário não pode efetuar login em tela o agente.

<BR>
<A NAME="users-phone_login">
<BR>
<B>Login do Ramal -</B> Aqui é onde você pode definir um valor de login telefone padrão para quando o usuário se conecta a tela do agente. Este valor irá preencher o phone_login automaticamente quando o usuário faz o login com seu user-pass-campanha na tela de login de agente.

<BR>
<A NAME="users-phone_pass">
<BR>
<B>Senha do Ramal -</B> Aqui é onde você pode definir um valor padrão para a passagem de telefone quando o usuário se conecta a tela do agente. Este valor irá preencher o phone_pass automaticamente quando o usuário faz o login com seu user-pass-campanha na tela de login de agente.

<BR>
<A NAME="users-active">
<BR>
<B>Ativo -</B> Este campo define se o usuário está ativo no sistema e pode logar como um agente ou gerente. O padrão é Y.

<BR>
<A NAME="users-voicemail_id">
<BR>
<B>ID de correo de voz -</B> Esta é a caixa de correio de voz que as chamadas serão direcionadas para em um AGENTDIRECT em grupo no momento, se a queda no grupo tem o método da gota definida para a caixa postal eo campo Correio de Voz definido para AGENTVMAIL.

<BR>
<A NAME="users-optional">
<BR>
<B>Email, Código do Usuário e Território -</B> Estes são campos opcionais.

<BR>
<A NAME="users-hotkeys_active">
<BR>
<B>Atalhos Ativos -</B> Esta opção quando configurada, permite ao usuário usar atalhos de teclado para classificação da chamada the agent screen.

<BR>
<A NAME="users-agent_choose_ingroups">
<BR>
<B>Agente Escolhe Grupos de Entrada -</B> Esta opção com valor 1, permite ao usuário escolher os grupos de entrada que quer receber chamadas quando fazem sogon em uma campanha CLOSER ou INBOUND. Caso contrário, o Gerente vai precisar configurar isto na tela de detalhes do cadastro de usuários no site admin.

<BR>
<A NAME="users-agent_choose_blended">
<BR>
<B>Agente Escolha Blended -</B> Esta opção se definido como 1 permite ao usuário escolher se o agente tem o seu conjunto campanha para misto ou não, e se não, então a configuração padrão misturado será usado. O padrão é 1 para habilitado.

<BR>
<A NAME="users-agent_choose_territories">
<BR>
<B>Agente Elija territorios -</B> Esta opción si se establece en 1 permite al usuario elegir los territorios que van a recibir llamadas de los usuarios cuando ingresan a un manual o campaña INBOUND_MAN. De lo contrario el usuario será configurado para utilizar todos los territorios que se establecen a pertenecer a los territorios de usuario administrativos sección.

<BR>
<A NAME="users-scheduled_callbacks">
<BR>
<B>Scheduled Callbacks -</B> Esta opción permite que un agente a disposición de una llamada como CALLBK y elegir la fecha y hora en que el plomo se vuelva a activar.

<BR>
<A NAME="users-agentonly_callbacks">
<BR>
<B>Chamada Agendada por Agente -</B> Permite que um agente configure uma chamada agendada com fidelização, assim ela retorna somente para ele. Isso também permite ao agente ver a sua lista de agendamentos e os chamar qualquer hora que desejem.

<BR>
<A NAME="users-agentcall_manual">
<BR>
<B>Agente Manual -</B> Esta opção permite que um agente para inserir manualmente uma nova pista para o sistema e chamá-los. Isso também permite a convocação de qualquer número de telefone a partir de sua tela de agente e coloca que põem em sua sessão. Use esta opção com cuidado.

<BR>
<A NAME="users-agentcall_email">
<BR>
<B>Agent Call Email -</B> This option is disabled.

<BR>
<A NAME="users-agent_recording">
<BR>
<B>Gravação Agent -</B> Esta opção pode evitar que um agente de fazer qualquer gravação depois de entrar para a tela do agente. Esta opção deve estar ligado para que a tela do agente para seguir as configurações de gravação de campanha.

<BR>
<A NAME="users-agent_transfers">
<BR>
<B>Transferências de agente -</B> Esta opção pode evitar que um agente de abrir a transferência - sessão de conferência na tela do agente. Se este é desativado, o agente não pode terceira chamada festa ou transferência sem quaisquer chamadas.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-closer_default_blended">
	<BR>
	<B>Padrão Mesclado para Finalizador -</B> Esta opção simplesmente marca ou nao o checkbox de Mesclado(blended) no login da campanha Finalizador (closer).
	<?php
	}
?>

<BR>
<A NAME="users-agent_recording_override">
<BR>
<B>Gravação Agente Override -</B> Esta opção irá substituir qualquer que seja a opção está na campanha para a gravação. Com deficiência não vai substituir a configuração de gravação de campanha. NUNCA irá desabilitar a gravação no cliente. ONDEMAND é o padrão e permite que o agente para iniciar e parar a gravação, conforme necessário. ALLCALLS irá iniciar a gravação no cliente sempre que uma chamada é enviada para um agente. ALLFORCE irá iniciar a gravação no cliente sempre que uma chamada é enviada para um agente dando o agente nenhuma opção para parar a gravação. Para ALLCALLS e ALLFORCE há uma opção para usar a gravação Delay para reduzir gravações muito curtas e reduzir a carga do sistema.

<BR>
<A NAME="users-agent_shift_enforcement_override">
<BR>
<B>Sobrepor Controle de Turno do Agente -</B> Esta configuração irá sobrepor qualquer configuração feita no grupo do agente para controle de turno. DISABLED irá usár a configuração do grupo. OFF não irá forçar turnos. START irá somente forçar o horário de login do turno mas não terá efeito sobre horário acima do final do turno se o usuário ainda estiver logado. ALL irá forçar o inicio do turno e irá fazer logout do agente caso passe do final do horário do turno. O padrão é DISABLED.

<BR>
<A NAME="users-agent_call_log_view_override">
<BR>
<B>Call Agent View Log Override -</B> Esta definição irá substituir o que o grupo de usuários usuários estabeleceu para Agente Ver registro de chamadas. DEFICIENTES usará a configuração grupo de usuários. N não vai permitir mostrar o registro de chamadas de usuários. Y vai permitir mostrar o registro de chamadas do usuário. O padrão é DISABLED.

<BR>
<A NAME="users-agent_lead_search_override">
<BR>
<B>Agente Override Pesquisa chumbo -</B> Esta definição irá substituir o que a campanha criou para pesquisa de chumbo agente. NOT_ACTIVE usará o cenário de campanha. ENABLED permitirá a pesquisa de chumbo e deficientes não permitirá a pesquisa de chumbo. O padrão é NOT_ACTIVE. LIVE_CALL_INBOUND permitirá busca por um tempo de chumbo em apenas uma chamada de entrada. LIVE_CALL_INBOUND_AND_MANUAL permitirá busca por um tempo de chumbo em uma chamada de entrada ou durante a pausa. Quando Pesquisa chumbo é utilizado em uma chamada de entrada ao vivo, a liderança da chamada, quando ele foi para o agente será alterado para um status de LSMERG, e os logs para a chamada será modificado para ligar para o agente de ligação selecionado em vez.

<BR>
<A NAME="users-alert_enabled">
<BR>
<B>Alerta Habilitado -</B> Este campo mostra se o agente tem alertas navegador da Web ativado para quando as chamadas vêm em sua sessão screen agente. O padrão é 0 para NÃO.

<BR>
<A NAME="users-allow_alerts">
<BR>
<B>Permitir Alertas -</B> Este campo dá-lhe a capacidade de permitir que os alertas do navegador agente a ser habilitado pelo agente para quando as chamadas vêm em sua sessão screen agente. O padrão é 0 para NÃO.

<BR>
<A NAME="users-preset_contact_search">
<BR>
<B>Contato Busca predefinido -</B> Se o usuário estiver conectado em uma campanha que tem Presets de transferência definida como CONTACTOS, essa configuração pode desativar contato procurando por este único usuário. O padrão é NOT_ACTIVE que vão usar o que o cenário de campanha é.

<BR>
<A NAME="users-max_inbound_calls">
<BR>
<B>Chamadas Max entrada -</B> Se essa configuração for definido para um número maior que 0, então será o número máximo de chamadas de entrada que um agente pode lidar em todos os grupos de entrada em um dia. Se o agente atinge o número máximo de chamadas de entrada, então eles não serão capazes de selecionar grupos de entrada para atender chamadas de até o dia seguinte. Esta configuração irá substituir a configuração de campanha com o mesmo nome. O padrão é 0 para deficientes.

<BR>
<A NAME="users-campaign_ranks">
<BR>
<B>Rankings da Campanha -</B> Nessa sessão você define o ranking que cada agente terá em cada campanha. Esses rankings podem ser usados para permitir roteamento preferencial de chamadas quando o campo Próximo Agente está como campaign_rank. Também nesta seção estão as variáveis WEB para caba campanha. Elas permitem cada agente ter variáveis diferentes que podem ser adicionadas a URL do WEB FORM ou do SCRIPT, simplestemnte colocando --A--web_vars--B-- da mesma forma que faria com qualquer outro campo Outro campo nesta secção é grau, o que permite a campaign_grade_random próximo agente Chamada definindo a ser usado. Esta configuração grau irá aumentar ou diminuir a probabilidade de que o agente vai receber a chamada.

<BR>
<A NAME="users-closer_campaigns">
<BR>
<B>Grupos de entrada -</B> Aqui é selecionado o grupo de entrada do qual você quer receber chamadas se você selecionou uma campanha CLOSER(finalização). Você também poderá configurar o ranking, ou nível de conhecimento, nessa sessão para cada grupo de entrada, assim como será capaz de ver o número de chamadas recebidas por cada grupo de entrada por agente específico. Também nessa sessão está a possibilidade de atribuir um ranking do agente para cada Grupo de Entrada. Esses rankings podem ser usados para roteamento preferencial quando essa opção está selecionada na tela de Grupos de Entrada. Também nesta seção estão as variáveis WEB para caba campanha. Elas permitem cada agente ter variáveis diferentes que podem ser adicionadas a URL do WEB FORM ou do SCRIPT, simplestemnte colocando --A--web_vars--B-- da mesma forma que faria com qualquer outro campo

<BR>
<A NAME="users-alter_custdata_override">
<BR>
<B>Sobrepor Permissão de Alteração-<\/B> Esta opção irá sobrepor qualquer opção na campanha para alterar dados do cliente. NOT_ACTIVE irá usar qualquer configuração presente na campanha. ALLOW_ALTER irá sempre permitir alteração no registro do cliente, não importando a configuração da campanha. O padrão é NOT_ACTIVE.

<BR>
<A NAME="users-alter_custphone_override">
<BR>
<B>Sobrepor Permissão de Alter. de Telefone- </B> Esta opção irá sobrepor qualquer configuração na campanha sobre alterar o telefone do cliente. NOT_ACTIVE irá usar a configuração presente na campanha. ALLOW_ALTER irá sempre permitir que o agente altere o telefone do cliente, não importando a configuração da campanha. O padrão é NOT_ACTIVE.

<BR>
<A NAME="users-custom_one">
<BR>
<B>Los campos de usuario personalizada -</B> Estos cinco campos se puede utilizar para diversos fines, y que se puede rellenar en el formulario web y direcciones de secuencias de comandos como user_custom_one y así sucesivamente. O campo 5 personalizado pode ser usado como um campo para definir um número dialplan para enviar uma chamada para a partir de um AGENTDIRECT em grupo, se o usuário não estiver disponível, você só precisa colocar AGENTEXT no campo da mensagem ou extensão no AGENTDIRECT em grupo eo sistema irá procurar o campo de cinco personalizada e enviar a chamada para esse número dialplan.

<BR>
<A NAME="users-alter_agent_interface_options">
<BR>
<B>Alterar Opções da Interface do Agente -</B> Esta opção quando configurada como 1 permite ao usuário administrativo modificar a interface do agente no admin.php.

<BR>
<A NAME="users-delete_users">
<BR>
<B>Apagar Usuários -</B> Esta opção quando habilitada permite que o usuário apaga outros usuários de igual ou menor nível do sistema.

<BR>
<A NAME="users-delete_user_groups">
<BR>
<B>Apagar Grupos de Usuários -</B> Esta opção, quando habilitada, permite que este usuário apague grupos de usuário do sistema.

<BR>
<A NAME="users-delete_lists">
<BR>
<B>Apagar Listas -</B> Esta opção se definido como 1 permite que o usuário excluir listas do sistema.

<BR>
<A NAME="users-delete_campaigns">
<BR>
<B>Apagar Campanhas -</B> Esta opção se definido como 1 permite que o usuário exclua campanhas a partir do sistema.

<BR>
<A NAME="users-delete_ingroups">
<BR>
<B>Apagar Grupos de Entrada -</B> Esta opção se definido como 1 permite que o usuário excluir Grupos de entrada do sistema.

<BR>
<A NAME="users-modify_custom_dialplans">
<BR>
<B>Modificar DialPlans personalizados -</B> Esta opção se definido como 1 permite ao usuário visualizar e modificar as entradas de costume dialplan que estão disponíveis no menu de chamadas, Configurações e servidores telas de modificação do sistema.

<BR>
<A NAME="users-delete_remote_agents">
<BR>
<B>Apagar Usuários Remotos -</B> Esta opção se definido como 1 permite que o usuário excluir agentes remotos do sistema.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-load_leads">
	<BR>
	<B>Carregar Registros -</B> Esta opção se definido como 1 permite que o usuário carregue listas de chumbo na tabela de lista por meio do carregador de liderança baseado na web.
	<?php
	}
if ($SScustom_fields_enabled > 0)
	{
	?>
	<BR>
	<A NAME="users-custom_fields_modify">
	<BR>
	<B>Campos Personalizados Modificar -</B> Esta opção se definido como 1 permite ao usuário modificar campos personalizados da lista.
	<?php
	}
?>

<BR>
<A NAME="users-campaign_detail">
<BR>
<B>Detalhes da Campanha-</B> Esta opção, quando configurada como 1, permite ao usuário ver e modificar os detalhes da campanha .

<BR>
<A NAME="users-ast_admin_access">
<BR>
<B>Acesso AGC Admin-</B> Esta opção, quando configurada como 1, permite o usuário efetuar logon nas páginas de admin do astGUIclient.

<BR>
<A NAME="users-ast_delete_phones">
<BR>
<B>Apagar Fones no AGC-</B> Esta opção, quando configurada como 1, permite ao usuário apagar registros de fone nas paginas de admin do astGUIclient.

<BR>
<A NAME="users-delete_scripts">
<BR>
<B>Apagar Scripts -</B> Esta opção, quando configurada como 1, permite o usuário apagar scripts de campanha na tela de configuração do script.

<BR>
<A NAME="users-modify_leads">
<BR>
<B>Modificar Registros -</B> Esta opção quando configurada permite ao usuário modificar registros na página de pesquisa do site admin.

<?php
if ($SSallow_emails>0)
	{
?>
<BR>
<A NAME="users-modify_email_accounts">
<BR>
<B>Modificar Contas de Email -</B> Esta opção se definido como 1 permite ao usuário modificar contas de e-mail na página de gerenciamento de contas de e-mail.
<?php
	}
?>

<BR>
<A NAME="users-change_agent_campaign">
<BR>
<B>Trocar Campanha do Agente -</B> Esta opção quando configurada, permite ao usuário trocar a campanha em que um agente está logado no sistema sem que haja necessidade de sair do sistema.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="users-delete_filters">
	<BR>
	<B>Apagar Filtros -</B> Esta opção permite que o usuário seja capaz de excluir filtros de chumbo a partir do sistema.
	<?php
	}
?>

<BR>
<A NAME="users-delete_call_times">
<BR>
<B>RemoverHorários de Cham. -</B> Esta opção permite que o usuário seja capaz de excluir vezes chamada de registros e estaduais vezes chamada de registros do sistema.

<BR>
<A NAME="users-modify_call_times">
<BR>
<B>Alterar Horários de Chamada -</B> Esta opção permite ao usuário visualizar e alterar os registros de horários e estados de ligação. O usuário não precisa desta opção habilitada se for somente alterar as opções de horário na tela de configuração de campanha.

<BR>
<A NAME="users-modify_sections">
<BR>
<B>Alterar Sessões -</B> Estas opções permitem ao usuário visualizar e alterar registros de cada sessão. Se configurado como 0, o usuário será capaz de ver a lista de sessões, mas não poderá ver os detalhes nem alterar os registros da sessão.

<BR>
<A NAME="users-view_reports">
<BR>
<B>View Relatórios -</B> Esta opção permite ao usuário visualizar os relatórios do sistema na Web.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="users-qc_enabled">
	<BR>
	<B>CQ Habilitado - </B> Esta opção permite ao usuário logar na tela de agente de Controle de Qualidade.

	<BR>
	<A NAME="users-qc_user_level">
	<BR>
	<B>Nível de usuário CQ - </B> Esta configuração define qual é o nível do usuário do Controle de Qualidade. Isso irá ditar o nível de funcionalidade desse agente na seção de CQ:<BR>
	1 - Não Modificar Nada<BR>
	2 - Não Modificar Nada Exceto Status<BR>
	3 - Modificar Todos Campos<BR>
	4 - Verificar primeiro nível de CQ<BR>
	5 - Vizualizar Estatísticas de CQ<BR>
	6 - Capacidade de Alterar Registros Finalizados<BR>
	7 - Nível Gerente<BR>

	<BR>
	<A NAME="users-qc_pass">
	<BR>
	<B>Registro Passou no CQ - </B> Esta opção permite ao agente especificar que um registro passou no primeiro nível de Controle de Qualidade após revisar o registro.

	<BR>
	<A NAME="users-qc_finish">
	<BR>
	<B>Finalizado no CQ - </B> Esta opção permite ao agente especificar que um registro finalizou o segundo nível de Controle de Qualidade após revisar o registro.

	<BR>
	<A NAME="users-qc_commit">
	<BR>
	<B>Confirmado no CQ - </B> Esta opção permite ao agente especificar que um registro foi Confirmado no Controle de Qualidade. E não poderá ser alterado por ninguém.
	<?php
	}
?>

<BR>
<A NAME="users-add_timeclock_log">
<BR>
<B>Incluir Registro de Ponto - </B> Esta opção permite ao usuário adicionar registros de log ao Relógio de Ponto.

<BR>
<A NAME="users-modify_timeclock_log">
<BR>
<B>Alterar Registro de Log do Ponto - </B> Esta opção permite ao usuário alterar os registros do log do Relógio de ponto.

<BR>
<A NAME="users-delete_timeclock_log">
<BR>
<B>Remover Registro de Log do Ponto - </B> Esta opção permite ao usuário remover registros do log do relógio de ponto.

<BR>
<A NAME="users-vdc_agent_api_access">
<BR>
<B>Agent API Access -</B> Esta opção permite a conta a ser usado com o agente e não agente comandos API.

<BR>
<A NAME="users-manager_shift_enforcement_override">
<BR>
<B>Sobrepor Controle de Turno do Gerente -</B> Esta configuração quando 1 irá permitir ao gerente entrar com suas credenciais na tela do agente para sobrepor o controle do turno caso o agente esteja tentando logar fora do horário determinado para seu turno. O padrão é 0.

<BR>
<A NAME="users-download_lists">
<BR>
<B>Download Listas -</B> Esta configuração quando 1 permite aoa gerente clicar no download da lista na parte inferior da tela de alteração de listas para exportar o conteúdo da lista para um arquivo em formato de texto. O padrão é 0.

<BR>
<A NAME="users-export_reports">
<BR>
<B>Exportar Relatórios -</B> Esta configuração se definido como 1 irá permitir que um gerente para acessar a chamada exportação e relatórios de chumbo na tela RELATÓRIOS. O padrão é 0. Para a exportação Chama e Leads relatórios, a ordem dos campos a seguir é usado para as exportações: <BR>call_date, phone_number_dialed, status, user, full_name, campaign_id/in-group, vendor_lead_code, source_id, list_id, gmt_offset_now, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, length_in_sec, user_group, alt_dial/queue_seconds, rank, owner

<BR>
<A NAME="users-delete_from_dnc">
<BR>
<B>Remover da Lista de Bloqueio -</B> Esta configuração se definido como 1 permitirá um gerente para remover os números de telefone das listas de DNC no sistema.

<BR>
<A NAME="users-realtime_block_user_info">
<BR>
<B>Informações do usuário em tempo real Bloco -</B> Esta configuração se definido como 1 irá bloquear as informações do usuário e da estação que está sendo exibido no relatório em tempo real. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="users-modify_same_user_level">
<BR>
<B>Modificar o nível mesmo usuário -</B> Esta definição aplica-se apenas ao nível 9 usuários. Se ativado ele permite que o usuário 9 nível para modificar as suas próprias definições, bem como outras de nível 9 usuários. O padrão é 1 para habilitado.

<BR>
<A NAME="users-admin_hide_lead_data">
<BR>
<B>Administrador Ocultar dados levam -</B> Esta definição aplica-se apenas ao nível 7, 8 e 9 usuários. Se ativado ele substitui os dados dos clientes de chumbo nos muitos relatórios e telas do sistema com Xs. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="users-admin_hide_phone_data">
<BR>
<B>Administrador Ocultar dados do telefone -</B> Esta definição aplica-se apenas ao nível 7, 8 e 9 usuários. Se ativado ele substitui os números de telefone dos clientes nos diversos relatórios e telas do sistema com Xs. As configurações dígitos irão mostrar apenas os dígitos últimos X do número de telefone. O padrão é 0 para deficientes físicos.






<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPANHAS TABELA</FONT></B><BR><BR>
<A NAME="campaigns-campaign_id">
<BR>
<B>ID da Campanha -</B> Este é o nome curto para a campanha, não é editável após cadastrado, não pode conter espaços e deve ter entre 2 e 8 caracteres de comprimentoh.

<BR>
<A NAME="campaigns-campaign_name">
<BR>
<B>Nome da Campanha-</B> Esta é a descrição da campanha, deve ter entre 6 e 40 caracteres de comprimento.

<BR>
<A NAME="campaigns-campaign_description">
<BR>
<B>Descrição da Campanha -</B> Este é um campo de texto para a campanha, é opicional e pode ter no máximo 255 caracteres de comprimento.

<BR>
<A NAME="campaigns-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esta campanha, o que permite a visualização de administrador esta campanha, bem como as listas atribuídos a essa campanha para ser restringido por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário administrador com permissões de usuário de campanha do grupo para ver esta campanha.

<BR>
<A NAME="campaigns-campaign_changedate">
<BR>
<B>Data de Alteração da Campanha -</B> Esta é a última vez que a configuração desta campanha foi alterada.

<BR>
<A NAME="campaigns-campaign_logindate">
<BR>
<B>Data do Último Login na Campanha -</B> É a data da última vez que um agente logou nesta campanha.

<BR>
<A NAME="campaigns-campaign_calldate">
<BR>
<B>Última fecha de convocatoria de la Campaña -</B> Esta es la última vez que una llamada fue manejado por un agente registrado en esta campaña.

<BR>
<A NAME="campaigns-max_stats">
<BR>
<B>Máxima diária Stats -</B> Estas são as estatísticas que são geradas pelo sistema a cada dia durante todo o dia até que o relógio de ponto final do dia, como é definido nas configurações do sistema. Estes números são gerados a partir dos registos dentro do sistema para permitir a exibição muito mais rápido. As estatísticas estão incluídos são - Totalde chamadas, Agentes máximo, máximo de chamadas de entrada, Máximo chamadas de saída.

<BR>
<A NAME="campaigns-campaign_stats_refresh">
<BR>
<B>Campanha Stats Refresh -</B> Esta opção permite que você force uma campanha chamando estatísticas atualizar, mesmo que a campanha não está ativo.

<BR>
<A NAME="campaigns-realtime_agent_time_stats">
<BR>
<B>Tempo Real Tempo Estatísticas do Agente -</B> Configurando isso para nada, mas desativado permitirá a recolha de estatísticas em tempo agente para hoje para esta campanha, que são visíveis através do relatório em tempo real. As chamadas são a média chama manipulados por agente, ESPERA é o tempo médio de espera, agente CUST é o tempo médio de conversação do cliente, ACW é a média do tempo de trabalho Depois de chamada, PAUSE é o tempo de pausa média. O padrão é DISABLED.

<BR>
<A NAME="campaigns-active">
<BR>
<B>Ativo -</B> Aqui é onde você configura a campanha coma Ativa ou Inativa. Se Inativa, ninguem pode entrar nela.

<BR>
<A NAME="campaigns-park_ext">
<BR>
<B>Park Music-on-Hold -</B> Isto é onde você pode definir o contexto de música em espera para esta campanha. Certifique-se de selecionar uma música válido no contexto espera a partir da lista de seleção e que o contexto que você selecionou tem arquivos válidos na mesma. O padrão é padrão.

<BR>
<A NAME="campaigns-park_file_name">
<BR>
<B>Park File Name -</B> NOT USED.

<BR>
<A NAME="campaigns-web_form_address">
<BR>
<B>Formulário Web -</B> Aqui é onde você configura a página web customizada que é aberta quando o usuário clica no botão WEB FORM. Para personalizar a string de pesquisa do formulário web, simplesmente inicie o formulário web com VAR e então a URL que você quer usar, substituindo as variáveis com os nomes de variável que você quer usar  --A--phone_number--B-- da mesma forma que na seção SCRIPTS. Se você quiser usar campos personalizados em um endereço de formulário web, você precisa adicionar &CF_uses_custom_fields=Y como parte de sua URL.

<BR>
<A NAME="campaigns-web_form_target">
<BR>
<B>Target do Form. Web-</B> Esta configuração é onde você coloca o frame da página que o formulário web irá ser aberto quando o usuário clica no botão WEB FORM. O padrão é _blank.

<BR>
<A NAME="campaigns-allow_closers">
<BR>
<B>Permitir Finalizadores -</B> Aqui é onde você pode configurar se os usuários dessa campanha poderão ter a opção de enviar a chamada para um finalizador.

<?php
if ($SSallow_emails > 0) 
		{
?>
	<BR>
	<A NAME="campaigns-allow_emails">
	<BR>
	<B>Permitir e-mails -</B> Este é o lugar onde você pode definir se os usuários desta campanha será capaz de receber e-mails de entrada, além de telefonemas.
<?php
		}
?>
<BR>
<A NAME="campaigns-default_xfer_group">
<BR>
<B>Grupo de Transf. Padrão -</B> Este campo é o Grupo de Entrada padrão que será automaticamente selecionado quando um agente seleciona transferência-conferência na tela do agente.

<BR>
<A NAME="campaigns-xfer_groups">
<BR>
<B>Grupos de Transferência Permitidos -</B> Nessa lista você pode selecionar grupos para os quais os agentes nessa campanha podem transferir chamadas. Permitir Finalizadores (closers) deve estar habilitado para que esta opção seja mostrada.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-campaign_allow_inbound">
	<BR>
	<B>Permitir Entrantes e Blended -</B> Aqui é onde se configura se o usuário desta campanha irá ter a opção de receber chamadas entrantes nesta campanha. Se você quiser mesclar entrantes e saintes, deve estar configurado como Y. Se você quer apenas fazer chamadas saintes nesta campnaha, configure como N. O padrão é N.

	<BR>
	<A NAME="campaigns-dial_status">
	<BR>
	<B>Status da Discagem -</B> Aqui é onde você configura os status que você deseja discar nas listas ativas para a campanha abaixo. Para adicionar outro status de discagem, selecione-o na lista e clique ADD. Para remover um dos status, clique no link REMOVE ao lado do status que você quer remover.

	<BR>
	<A NAME="campaigns-lead_order">
	<BR>
	<B>Ordem da Lista -</B> Este menu é onde você seleciona como os registros nos status selecionados acima serão colocados no hopper:
	 <BR> &nbsp; - DOWN: selecionar os primeiros fios carregados na tabela de lista
	 <BR> &nbsp; - UP: Selecione a última leva carregados na tabela da lista de
	 <BR> &nbsp; - UP PHONE: seleciona o número de telefone mais alto e desce até o fim
	 <BR> &nbsp; - DOWN PHONE: seleciona o telefone mais baixo e sube até o topo
	 <BR> &nbsp; - UP LAST NAME: inicia com sobrenomes iniciando em Z e desce até o fim
	 <BR> &nbsp; - DOWN LAST NAME: inicia com sobrenomes iniciando em A e sobe até o inicio
	 <BR> &nbsp; - UP COUNT: inicia com registros mais chamados e vai até o fim
	 <BR> &nbsp; - DOWN COUNT: inicia com registros menos chamados e vai até o começo
	 <BR> &nbsp; - DOWN COUNT 2nd NEW: inicia com registros menos chamados e vai até o começo inserindo registros NOVOS intercalados - Não deve ter selecionado NOVOS nos status de discagem
	 <BR> &nbsp; - DOWN COUNT 3nd NEW: inicia com registros menos chamados e vai até o começo inserindo um registro NOVO a cada três registros - Não deve ter NOVOS selecionado nos status de discagem
	 <BR> &nbsp; - DOWN COUNT 4th NEW: inicia com registros menos chamados e vai até o começo inserindo um registro novo a cada quatro registros - Não deve ter NOVOS selecionado nos status de discagem
	 <BR> &nbsp; - RANDOM: Aleatoriamente pega um registro entre os status e listas definidas
	 <BR> &nbsp; - HORA DA ÚLTIMA CHAMADA PARA CIMA: Ordena os registros pela chamada mais recente 
	 <BR> &nbsp; - HORA DA ÚLTIMA CHAMADA PARA BAIXO: Ordena os registros pela chamada mais antiga
	 <BR> &nbsp; - UP RANK: Comienza con la más alta categoría y se abre camino hacia abajo
	 <BR> &nbsp; - DOWN RANK: Comienza con el rango más bajo y se abre camino hasta
	 <BR> &nbsp; - UP OWNER: Comienza con los propietarios de inicio con Z y obras de su camino hacia abajo
	 <BR> &nbsp; - DOWN OWNER: Comienza con los propietarios empiezan con la letra y se abre camino hasta
	 <BR> &nbsp; - UP TIMEZONE: Comienza con las zonas horarias del Este y del Oeste de obras
	 <BR> &nbsp; - DOWN TIMEZONE: Comienza con las zonas horarias occidental y oriental de obras

	<BR>
	<A NAME="campaigns-lead_order_randomize">
	<BR>
	<B>Ordem Randomize Lista -</B> Esta opção permite que você embaralhar a ordem dos leads no funil de carga dentro dos resultados definidos pelos critérios estabelecidos acima. Por exemplo, a ordem de lista será preservada, mas os resultados serão randomizados dentro que a triagem. Definir esta opção como Y vai ativar esse recurso. Padrão é n para deficientes físicos. Note que, se você tem um grande número de leva esta opção pode diminuir a velocidade do script de carregamento funil.

	<BR>
	<A NAME="campaigns-lead_order_secondary">
	<BR>
	<B>Ordem lista secundária -</B> Esta opção permite que você selecione a ordem de classificação segundo os leads no funil de carga após a Ordem de lista e dentro dos resultados definidos pelos critérios estabelecidos acima. Por exemplo, a ordem de lista irá ser utilizado para o primeiro tipo de os fios, mas os resultados serão classificados uma segunda vez no que ordenação através do mesmo conjunto da Ordem primeira lista. Por exemplo, se você tiver Ordem Lista definido para contagem decrescente e você só tem ligações que foram tentadas 1 e 2 vezes, então se você tem o conjunto Ordem lista secundária para LEAD_ASCEND todas as tentativas 1 ligações serão classificados por mais antigas pistas primeira e será colocada no funil de que maneira. O padrão é LEAD_ASCEND. Note que, se você tem um grande número de ligações usando uma das opções CALLTIME pode diminuir a velocidade do script de carregamento funil. Se Ordem Lista Randomize é ativada, esta opção será ignorada.

	<BR>
	<A NAME="campaigns-hopper_level">
	<BR>
	<B>Nível Hopper mínimo -</B> Este é o número mínimo de ligações o script funil de carga tenta manter na tabela de funil para esta campanha. Se a execução de script VDhopper cada minuto, fazem deste um pouco maior que o número de ligações que você passar em um minuto.

	<BR>
	<A NAME="campaigns-use_auto_hopper">
	<BR>
	<B>Nível Hopper automática -</B> Configurando isso para Y permitirá que o sistema para ajustar automaticamente o funil baseado fora as configurações que você tem em sua campanha. A fórmula que utiliza para fazer isso está:<BR>Number of AtivoAgentes * Nível de Discagem Automática * ( 60 seconds / Tempo de Espera da Discagem ) * Auto Hopper Multiplier<BR>Default is Y.

	<BR>
	<A NAME="campaigns-auto_hopper_multi">
	<BR>
	<B>Multiplicador Hopper automática -</B> Esta é multiplicador para o Auto Hopper. Definir este menos de 1 fará com que o algoritmo de Hopper Auto para carregar leva menos do que normalmente. Definir este maior do que 1 fará com que o algoritmo de Hopper Auto para carregar mais ligações do que normalmente. O padrão é 1.

	<BR>
	<A NAME="campaigns-auto_trim_hopper">
	<BR>
	<B>Auto Trim Hopper -</B> Configurando isso para Y permitirá que o sistema para remover automaticamente leva o excesso de funil. O padrão é Y.

	<BR>
	<A NAME="campaigns-hopper_vlc_dup_check">
	<BR>
	<B>Hopper VLC Dup Confira -</B> Definindo-a para Y irá resultar em todas as pistas que está sendo inserido dentro do depósito que está sendo verificado por vendor_lead_code para se certificar de que não há duplicar leva inseridos com o vendor_lead_code mesmo. Isto é muito útil quando o Auto-Alt-Discagem com MULTI_LEAD. O padrão é N.

	<BR>
	<A NAME="campaigns-lead_filter_id">
	<BR>
	<B>Filtro de Registros -</B> Este é um método de filtrar os registros usando um fragmento de uma query SQL. Use este recurso com cuidado, é fácil parar de discar acidentalmente com uma pequena alteração no comando SQL. O padrão é NONE.

	<BR>
	<A NAME="campaigns-call_count_limit">
	<BR>
	<B>Contagem contas Limite -</B> Isso impõe um limite no número de tentativas de chamadas para os terminais marcados nesta campanha. A liderança pode ultrapassar esse limite se ligeiramente reciclagem de chumbo ou Auto-Alt-discagem está ativado. O padrão é 0 para sem limite.

	<BR>
	<A NAME="campaigns-call_count_target">
	<BR>
	<B>Contagem contas Alvo -</B> Esta opção só é usada para fins de relatório e não tem efeito em ligações discadas. O padrão é 3.

	<BR>
	<A NAME="campaigns-drop_lockout_time">
	<BR>
	<B>Tiempo de caída de bloqueo -</B> Este es un número de horas que DROP abandonar pide se le impida que se está marcando, para establecer inhabilitar a 0. Esta opción es muy útil en países como el Reino Unido, donde existen normas que impiden la tentativa de llamar a los clientes dentro de las 72 horas de abandono, o DROP. Por defecto es 0.

	<BR>
	<A NAME="campaigns-force_reset_hopper">
	<BR>
	<B>Força Limpeza do Hopper -</B> Isto permite que você limpe o conteúdo do hopper a partir do comando enviar da página. O hopper deve ser preenchido assim que o script do VDhopper for executado.

	<BR>
	<A NAME="campaigns-dial_method">
	<BR>
	<B>Método de discagem -</B> Esta é a definição de como a discagem deve ser feita. Se MANUAL então o níver de discagem automática ficará travado em 0 a não ser que o método seja alterado. Se RATIO então a discagem será feita baseada na quantidade de agentes livres. ADAPT_HARD_LIMIT irá discar preditivamente até o nível de chamadas derrubadas e não discará agressivamente após o nível ter sido alcançado até que abaixe novamente. ADAPT_TAPERED permite se passar do nível de derrubadas na primeira metade do turno, conforme definido no horário de campanha, e fica menos agressivo conforme o turno avança. ADAPT_AVERAGE tenta manter uma média no percentual de derrubadas não impondo limites e não sendo tão agressivo como os outros dois métodos. Você não pode mudar o nível de discagem automática se você usar alguns dos métodos ADAPT. Somente o Discador pode mudar o nível no modo preditivo. INBOUND_MAN permite ao agente fazer chamadas manuais a partir de uma campanha enquanto está disponível para receber chamadas entrantes, entre as chamadas manuais.

	<BR>
	<A NAME="campaigns-auto_dial_level">
	<BR>
	<B>Nível de Discagem Automática -</B> Este é onde você define quantas linhas o sistema deve usar por agente ativo. zero, 0 significa discagem automática está desligado e os agentes irão clicar para marcar cada número. Caso contrário, o sistema irá manter a marcação linhas iguais para agentes ativos multiplicado pelo nível de marcação para chegar a quantas linhas esta campanha em cada servidor deve permitir. A caixa de seleção SOBREPOR ADAPT permite forçar um novo nível de ligação, mesmo que o método de discagem está em um modo de se adaptar. Isto é útil se houver uma mudança drástica na qualidade das ligações e você quer mudar drasticamente a dial_level manualmente.

	<BR>
	<A NAME="campaigns-dial_level_threshold">
	<BR>
	<B>Auto Threshold Nível Dial -</B> Essa configuração só funciona com um ADAPT ou RATIO Método de marcação, e deve ser definido para algo diferente de Desativado eo número de agentes de configuração deve estar acima de 0. Este recurso permite que você defina um número mínimo de agentes que o algoritmo preditivo vai trabalhar. Se o número de agentes é inferior ao número que você definiu, então o nível de ligação vai a 1,0 até mais agentes login ou ir para o estado selecionado. LOGGED de IN_AGENTS contará todos os agentes registrados na campanha, NÃO PAUSED_AGENTS só vai contar com agentes que estão à espera ou falar, e WAITING_AGENTS só contam os agentes que estão à espera de uma chamada. O padrão é DISABLED.

	<BR>
	<A NAME="campaigns-available_only_ratio_tally">
	<BR>
	<B>Somente Lista Autom. -</B> Este campo quando configurado como Y não considera a quantidade de chamadas entrantes nem em fila de espera para calcular a quantidade de chamadas para discagem automática. O padrão é N.

	<BR>
	<A NAME="campaigns-available_only_tally_threshold">
	<BR>
	<B>Threshold Tally disponível somente -</B> Essa configuração só funciona com um ADAPT ou RATIO Método de marcação, Tally disponível apenas deve ser definido como N, essa configuração deve ser definido para algo diferente de Desativado eo número de agentes de configuração deve estar acima de 0. Este recurso permite que você defina o número de agentes abaixo do qual Tally disponível apenas será habilitado. Se o número de agentes é inferior ao número que você definiu, então a configuração Tally disponível apenas com ir para Y temporariamente até que os agentes mais fazer login ou ir para o estado selecionado. LOGGED de IN_AGENTS contará todos os agentes registrados na campanha, NÃO PAUSED_AGENTS só vai contar com agentes que estão à espera ou falar, e WAITING_AGENTS só contam os agentes que estão à espera de uma chamada. O padrão é DISABLED.

	<BR>
	<A NAME="campaigns-adaptive_dropped_percentage">
	<BR>
	<B>Percentual Limite de Derrubadas (drop) -</B> Este campo é onde você configura o limite em percentual das chamadas derrubadas quando usamos um dos métodos preditivos(ADAPT), não válido para métodos MANUAL ou RATIO.

	<BR>
	<A NAME="campaigns-adaptive_maximum_level">
	<BR>
	<B>Nível Máximo de Adapt -</B> Este campo é onde você configura o limite máximo da quantidade de linhas a usar por usuário quando usando um método preditivo (ADAPT), não válido para MANUAL ou RATIO. Este número pode ser maior que o nível de discagem automática se o seu hardware permitir. O valor deve ser um número positivo maior que 1 e pode ter casas decimáis. O padrão é 3.0.

	<BR>
	<A NAME="campaigns-adaptive_latest_server_time">
	<BR>
	<B>Horário Final do Servidor -</B> Este campo só é usado pelo método ADAPT_TAPERED. Você deve entrar com a hora e o minuto em que vai parar de fazer chamadas para esta campanha, 2100 significa que você vai parar de discar esta campanha as 21 horas. Isto permite ao algoritmo do método Tapered decidir quanto agressivo deve ser e quanto tempo tem até o horário de parar.

	<BR>
	<A NAME="campaigns-adaptive_intensity">
	<BR>
	<B>Modificador de Intensidade -</B> Este campo é usado para ajustar a intensidade preditiva para cima ou para baixo. Quanto mais alto(positivo) o valor selecionado, mais o discador vai aumentar o ritmo de chamadas quando aumentar a velocidade e mais lento será para o discador diminuir a velocidade. Quanto mais negativo for o número que você selecionou, mais devagar o discador irá aumentar o ritmo quando precisar, e mais rápido diminuirá a velocidade quando precisar diminuir o ritmo. O padrão é 0. Este campo não é usado pelos métodos MANUAL ou RATIO. .

	<BR>
	<A NAME="campaigns-adaptive_dl_diff_target">
	<BR>
	<B>Alvo de Diferença de Nível -</B> Este campo é utilizado para definir se você deseja atingir com um número específico de agentes à espera de chamadas ou chamadas de espera para os agentes. Por exemplo, se você sempre gostaria de ter em média um agente livre para receber chamadas imediatamente você deve definir como -1, se você gostaria de ter sempre como alvo uma chamada em espera à espera de um agente, você deve definir esta a 1. O padrão é 0. Este campo não é usado pelo MANUAL ou métodos de marcação INBOUND_MAN.

	<BR>
	<A NAME="campaigns-dl_diff_target_method">
	<BR>
	<B>Disque nível do método alvo Diferença -</B> Esta opção permite definir se o disco de nível fixação de metas diferença é aplicada apenas para o cálculo do nível de discagem ou também para a marcação real em cada servidor de discagem. Se você estiver executando uma campanha pequena, com agentes logados em vários servidores que você pode querer usar a opção ADAPT_CALC_ONLY, porque a opção CALLS_PLACED pode resultar em menos chamadas que estão sendo colocadas de deisred. Esta opção só está activo se Dial-alvo diferença de nível é definido como algo diferente de 0. O padrão é ADAPT_CALC_ONLY.

	<BR>
	<A NAME="campaigns-concurrent_transfers">
	<BR>
	<B>Transferências Simultâneas -</B> Esta configuração é usada para definir o número de chamadas que podem ser enviadas para agentes ao mesmo tempo. É recomendado que esta configuração seja deixada em AUTO. Este campo não é usado pelo método MANUAL de discagem.

	<BR>
	<A NAME="campaigns-queue_priority">
	<BR>
	<B>Prioridade da Fila - </B> Valor utilizado para definir a ordem na qual as chamadas dessa campanha de saída devem ser respondidas em relação as chamadas entrantes desta campanha em modo blended. Nós não recomendamos a configuração chamadas de entrada para uma prioridade maior do que as chamadas de saída de campanha porque as pessoas que fazem escala em que esperar e pessoas a serem chamadas de esperar de alguém para estar lá no telefone.

	<BR>
	<A NAME="campaigns-drop_rate_group">
	<BR>
	<B>Varias campañas Drop Rate Grupo -</B> Esta característica le permite establecer una campaña como miembro de una campaña de la velocidad de descenso del Grupo, o un grupo de campañas cuyas llamadas contestadas y llamadas Humanos Drop para todas las campañas en el grupo se combinarán en un porcentaje de caída compartida, o tasa de abandono. Esto le permite ejecutar varias campañas a la vez y más fácil de controlar su tasa de caída. Esto es particularmente útil en el Reino Unido, donde las regulaciones lo permiten esta disminución método del tipo de cálculo con la campaña de la agrupación por la misma empresa, incluso si hay varias campañas que la empresa está ejecutando en el mismo día. Para habilitar esta para una campaña, sólo tienes que seleccionar un grupo de la lista. Hay 10 grupos definidos en el sistema por defecto, puede ponerse en contacto con el administrador del sistema para añadir más. Desactivado por defecto.

	<BR>
	<A NAME="campaigns-inbound_queue_no_dial">
	<BR>
	<B>Fila de entrada no Disque -</B> Este recurso se definido como ENABLED permite evitar saída discagem automática desta campanha se existem chamadas de entrada na fila de espera que fazem parte dos grupos permitidos entrada definidos nesta campanha. Definindo-a para ALL_SERVERS vai mudar o algoritmo para calcular todas as chamadas de entrada como as chamadas ativas neste servidor, mesmo se eles estiverem em outro servidor que irá reduzir a chance de colocar desnecessários chamadas de saída se você tiver chamadas que entram em outro servidor. O padrão é DISABLED.

	<BR>
	<A NAME="campaigns-auto_alt_dial">
	<BR>
	<B>Auto Discar Alternativo -</B> Esta configuração é usada para automaticamente discar números alternativos enquanto os métodos RATIO e ADAPT são usados e não há contato no telefone principal para um registro com status NA, B, DC e N. Esta configuração não é usada pelo método MANUAL de discagem. EXTENDIDOS são números alternativos que são carregados no sistema fora da tela padrão de informações sobre registros. Usando EXTENDED você pode ter centenas de números de telefone para um único registro de cliente.  Usando MULTI_LEAD permite que você use um cabo separado para cada número de uma conta de cliente que compartilham vendor_lead_code comum, eo tipo de número de telefone para cada um é definido com uma etiqueta no campo Proprietário. Esta opção irá desativar algumas opções na tela Modificar campanha e mostrar um link para o Multi-Alt página de configurações que permite que você defina os tipos de telefone cujo número, definidas pelo rótulo de cada ligação, será marcado e em que ordem. Isto irá criar um filtro especial de chumbo e vai alterar o campo de classificação de todas as ligações no interior das listas definidas para esta campanha, a fim de chamá-los na ordem que você especificou.

	<BR>
	<A NAME="campaigns-dial_timeout">
	<BR>
	<B>Tempo excedido de discagem -</B> Se configurado, as chamadas que normalmente seriam desligadas após o tempo definido no extensions.conf serão desligadas no tempo configurado aqui, caso este valor seja menor do que o do extensions.conf. Isso permite que rapidamente sejam alterados os timeouts por campanha e que a configuração seja igual para todos os servidores. Se você está recebendo muitas chamadas de secretária eletrônica ou correio de voz, pode tentar mudar esse valor para valores entre 21 e 26 e verifique se melhora.

	<BR>
	<A NAME="campaigns-campaign_vdad_exten">
	<BR>
	<B>Roteamento de Extensão -</B> Este campo permite uma extensão de encaminhamento personalizado de saída. Isto permite-lhe usar métodos de manipulação de chamada diferentes, dependendo de como você quer rotear chamadas através de sua campanha de saída. Extensão VDAD Anteriormente chamado de Campanha. 
	<BR>- 8364 - mesma 8368
	<BR>- 8365 - Vou enviar o convite apenas a um agente no mesmo servidor como a chamada é colocada em
	<BR>- 8366 - Usado para campanhas de imprensa-1 de radiodifusão e vistoria
	<BR>- 8367 - Vai tentar primeiro enviar a chamada para um agente no servidor local, então ele vai procurar em outros servidores
	<BR>- 8368 - PADRÃO - Vou enviar a chamada para o próximo agente disponível não importa o que eles estão no servidor
	<BR>- 8369 - Utilizada para Detecção de Máquina Respondendo depois disso, mesmo comportamento 8368
	<BR>- 8373 - Utilizada para Detecção de Máquina Respondendo depois que o mesmo comportamento como 8366
	<BR>- 8374 - Usado para campanhas de imprensa-1 de radiodifusão, e pesquisa com Cepstral Text-to-speech
	<BR>- 8375 - Utilizada para Detecção de Máquina Respondendo em seguida, pressione-1 de radiodifusão e vistoria campanhas com Cepstral Text-to-speech

	<BR>
	<A NAME="campaigns-am_message_exten">
	<BR>
	<B>Mensagem na Secretária Eletrônica -</B> Este campo es para entrar en el sistema para jugar cuando el agente recibe un contestador automático y hace clic en el botón Responder a máquina contestadora en el marco de conferencias de transferencia. Debe establecer que esto sea un archivo de audio en la tienda de audio o un sistema TTS TTS si está habilitada en su sistema de.

	<BR>
	<A NAME="campaigns-waitforsilence_options">
	<BR>
	<B>Opciones WaitForSilence -</B> Si Wait For silencio es deseada en las llamadas que se detectan como Contestadoras entonces este campo tiene esas opciones. Hay dos opciones separadas por una coma, la primera opción es el tiempo para detectar silencio en milisegundos y la segunda opción es para cuántas veces se detecta que antes de reproducir el mensaje. Por defecto está vacío para discapacitados. Un valor estándar para este sería esperar 2 segundos de silencio en dos ocasiones: 2000,2

	<BR>
	<A NAME="campaigns-amd_send_to_vmx">
	<BR>
	<B>AMD enviar à Ação -</B> Esta opção permite definir se uma chamada é enviada para a Ação AMD quando uma secretária eletrônica é detectado. Se estiver definido para N, então a chamada será preso assim que está determinado a ser uma secretária eletrônica. O padrão é N.

	<BR>
	<A NAME="campaigns-cpd_amd_action">
	<BR>
	<B>Ação CPD AMD - </B> Se você estiver usando o software de Detecção Sangoma ParaXip Call Progress então você deve habilitar esta configuração para DISPO que irá finalizar a chamada como AA e desligar se a chamada estiver sendo processada e ainda não foi enviada para um agente ou MESSAGE que irá enviar a chamada para o campo definido como Mensagem de Secretária Eletrônica para esta Campanha. O padrão é DISABLED. Ajustando para ingroup irá enviar um atendedor de chamadas a um grupo de entrada. Ajustando para CALLMENU enviará uma secretária eletrônica com um menu de chamadas no sistema.

	<BR>
	<A NAME="campaigns-amd_inbound_group">
	<BR>
	<B>AMD Entrada Grupo -</B> Se CPD AMD Ação está definido para ingroup, então este é o grupo de entrada que a chamada será enviada para Se uma secretária eletrônica é detectado.

	<BR>
	<A NAME="campaigns-amd_callmenu">
	<BR>
	<B>Menu de Chamada AMD -</B> Se CPD AMD Ação está definido para CALLMENU, então este é o menu de chamadas que a chamada será enviada para Se uma secretária eletrônica é detectado.

	<BR>
	<A NAME="campaigns-alt_number_dialing">
	<BR>
	<B>Manual de Alt Num Discagem -</B> Esta opção permite que um agente para discar manualmente o número de telefone alternativo ou address3 campo após o número principal foi chamado. Se a opção for selecionado na então a caixa de seleção Dial Alt será verificado automaticamente para cada chamada. Se a opção tem TIMER nele então o telefone Alt ou campo Address3 serão automaticamente ser discado após Segundos Temporizador Alt. O padrão é N para deficientes.

	<BR>
	<A NAME="campaigns-timer_alt_seconds">
	<BR>
	<B>Segundos Alt Temporizador -</B> Se o manual de configuração Alt Num discagem tem TIMER nele então o telefone Alt ou campo Address3 serão automaticamente ser discado após este número de segundos. O padrão é 0 para deficientes .

	<BR>
	<A NAME="campaigns-drop_call_seconds">
	<BR>
	<B>Segundos para derrubar chamada -</B> Número de segundos entre o atendimento pelo cliente até a chamada ser considerada um DROP (derrubada), só se aplica a chamadas saintes.

	<BR>
	<A NAME="campaigns-drop_action">
	<BR>
	<B>Ação de Drop - </B> Este menu permite que você escolha o que acontece com uma chamada quando esta ficou esperando mais que o tempo configurado no campo Tempo Limite de Drop. HANGUP irá simplesmente desligar a chamada, MESSAGE irá enviar a chamada para Extensão de Drop que você definiu abaixo, VOICEMAIL irá enviar a chamada para a caixa de voicemail que você definiu abaixo e IN_GROUP irá enviar a chamada para o grupo de entrada que você define abaixo. VMAIL_NO_INST enviará a chamada para a caixa de correio de voz que você tenha definido a seguir e não vai jogar instruções após a mensagem de correio de voz.

	<BR>
	<A NAME="campaigns-safe_harbor_exten">
	<BR>
	<B>Exten do Porto Seguro -</B> Esta é a extensão do plano de discagem designada para o porto seguro, deve tocar o arquivo de mensagem localizado no seu servidor.

	<BR>
	<A NAME="campaigns-safe_harbor_audio">
	<BR>
	<B>Áudio Safe Harbor -</B> Este é o arquivo de áudio prompt que é jogado, se a ação de soltar for definido como AUDIO. O padrão é o zumbido.

	<BR>
	<A NAME="campaigns-safe_harbor_audio_field">
	<BR>
	<B>Safe Harbor Áudio Campo -</B> Esta configuração opcional permite que você defina um campo na lista que o sistema usará como o nome do arquivo de áudio para cada liderança no lugar do arquivo de áudio de Porto Seguro. Se isso for definido como desativado o arquivo de áudio de Porto Seguro será sempre utilizado. O sistema irá fazer nenhuma validação para certificar-se de que o arquivo de áudio existe a não ser para garantir que o valor do campo é de pelo menos um personagem, então se você quiser uma vantagem de usar o padrão de áudio porto seguro, então você apenas definir o valor do campo na liderança para esvaziar. Você pode usar o caractere pipe para conectar vários arquivos de áudio em conjunto no valor de campo para cada ligação. O padrão é desabilitado. Aqui é a lista de campos que podem ser utilizados para esta configuração: vendor_lead_code, source_id, list_id, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, alt_phone, email, security_phrase, comments, rank, owner, entry_list_id

	<BR>
	<A NAME="campaigns-safe_harbor_menu_id">
	<BR>
	<B>Menu de chamada Safe Harbor -</B> Este é o menu de chamada que uma chamada é enviada para se a ação de soltar for definido como CALLMENU.

	<BR>
	<A NAME="campaigns-voicemail_ext">
	<BR>
	<B>Correio de Voz -</B> Se configurado, as chamadas que normalmente seriam derrubadas por falta de agente, serão transferidas para esse Correio de Voz, irão ouvir uma mensagem e poderão gravar um recado.

	<BR>
	<A NAME="campaigns-drop_inbound_group">
	<BR>
	<B>Grupo de Transferência de Drop - </B> Caso a Ação de Drop esteja configurada como IN_GROUP, a chamada será enviada para esse Grupo de Entrada após o tempo limite.

	<BR>
	<A NAME="campaigns-no_hopper_leads_logins">
	<BR>
	<B>Permitir Logar sem Registro no Hopper -</B> Quando Y, permite agentes logarem na campanha mesmo que não existam registros carregados no hopper para esta campanha. Esta funcionalidade não é necessária em campanha de Finalização(CLOSER). O padrão é N.

	<BR>
	<A NAME="campaigns-no_hopper_dialing">
	<BR>
	<B>N marcado Hopper -</B> Si esta opción está activada, la tolva no se ejecutará para esta campaña. Esta opción sólo está disponible cuando el método de línea se establece en Manual o INBOUND_MAN. Se recomienda que no habilite esta opción si usted tiene una base de datos de plomo muy grande, más de 100.000 clientes potenciales. Con n marcado Hopper, las características siguientes no funcionan: reciclaje de plomo, auto-alt-marcación, la mezcla lista, lista de pedido con X NEW. Si desea utilizar único dueño de marcado no debe tener marcado Hopper habilitado. Por defecto es n para discapacitados.

	<BR>
	<A NAME="campaigns-agent_dial_owner_only">
	<BR>
	<B>Único propietario de marcado -</B> Si esta opción está activada, el agente sólo recibirá las pistas que están dentro de los parámetros de la propiedad. Si esto se establece a usuario entonces el agente debe ser definida por el usuario en la base de datos como el propietario de este lugar. Si esto se establece en territorio que entonces el dueño de la iniciativa debe coincidir con el territorio que figuran en la pantalla de modificación del usuario de este agente. Si esto se establece a USER_GROUP entonces el dueño de la iniciativa debe coincidir con el grupo de usuarios que el agente es un miembro de. Para que esta característica funcione el método de línea se debe establecer en Manual o INBOUND_MAN marcado Hopper y no debe estar activado. El valor predeterminado es NINGUNO para discapacitados. Se a opção tem em branco no final, então os usuários podem discar ligações sem proprietário definido, além de proprietário definido leva.

	<BR>
	<A NAME="campaigns-owner_populate">
	<BR>
	<B>Proprietário Preencher -</B> Se estiver habilitado eo campo proprietário do chumbo estiver em branco, o campo proprietário para a liderança vai preencher com o ID do usuário do agente que lida com a chamada pela primeira vez. O padrão é desabilitado.

	<?php
	if ($SSuser_territories_active > 0)
		{
		?>
		<BR>
		<A NAME="campaigns-agent_select_territories">
		<BR>
		<B>Agente Seleccione territorios -</B> Si esta opción está activada y el agente pertenece a al menos un territorio, el agente tendrá la opción de selección de territorios a los cables de línea. El agente podrá ver una lista de los territorios disponibles a inicio de sesión y tendrán la capacidad de volver a la lista territorio cuando se detuvo a cambiar sus territorios. Para esta función a la labor del único dueño de marcado opción debe configurarse con el territorio y territorios de usuario debe estar habilitado en la configuración del sistema.
		<?php
		}
	?>

	<BR>
	<A NAME="campaigns-list_order_mix">
	<BR>
	<B>Ordem de Mesclagem -</B> Sobrepõe a ordem nos campos Ordem de Registros e Status de Discagem. .

	<BR>
	<A NAME="campaigns-vcl_id">
	<BR>
	<B>ID da Mescl. de Lista -</B> ID da mesclagem de listas. Deve ter entre 2 e 20 caracteres de comprimento sem espaços ou pontos.

	<BR>
	<A NAME="campaigns-vcl_name">
	<BR>
	<B>Nome da Mescl. de Lista -</B> nombre descriptivo de la mezcla dela lista. Debe ser a partir de 2-50 caracteres en longitud.

	<BR>
	<A NAME="campaigns-list_mix_container">
	<BR>
	<B>Detalhes da Mescl. de Lista -</B> Composição da Mesclagem de listas. Contém o ID, ordem, percentuais, e status que montam essa Mesclagem. Os percentuais devem sempre somar 100, e as listas devem estar ativas e configuradas na campanha em que a mesclagem vai ser configurada.

	<BR>
	<A NAME="campaigns-mix_method">
	<BR>
	<B>Método de Mesclagem -</B> O método de Mesclagem de todos os ítens da lista. EVEN_MIX mistura os registros de cada parte intercalados com as outras partes, por exemplo: 1,2,3,1,2,3,1,2,3. IN_ORDER irá colocar os registros na ordem que estão listados na tela de detalhes 1,1,1,2,2,2,3,3,3. RANDOM irá colocar em ordem randômica: 1,3,2,1,1,3,2,1,3. O padrão é IN_ORDER.

	<BR>
	<A NAME="campaigns-agent_extended_alt_dial">
	<BR>
	<B>Agent Screen Extended Alt Dial -</B> Este recurso permite aos agentes para acessar os números de telefone alternativo para ligações prolongados além do telefone Alt padrão e campos Address3 que pode ser usado na tela de agente para os números de telefone para além do número de telefone principal. Os números de telefone estendidas podem ser discado automaticamente usando o recurso Auto-Alt-Disque nas configurações da campanha, mas a habilitação deste recurso Tela agente também vai permitir que o agente de chamar esses números a partir de sua tela de agente, bem como editar suas informações. Este recurso está em desenvolvimento e não está disponível.

	<BR>
	<A NAME="campaigns-survey_first_audio_file">
	<BR>
	<B>Primeiro Audio da Pesquisa - </B> Este é o nome do arquivo de audio que é tocado assim que o cliente atende o telefone em uma campanha de pesquisa.

	<BR>
	<A NAME="campaigns-survey_dtmf_digits">
	<BR>
	<B>Digitos DTMF da Pesquisa - </B> Este campo é onde você define os digitos que um cliente pode digitar como opção para campanha de pesquisa. Digitos válidos são 0123456789*#. Todas as opções, com exceção de Not Interested, Terceiro e Quarto dígito, irão mover a chamada para o método de Pesquisa(Pesquisa).

	<BR>
	<A NAME="campaigns-survey_wait_sec">
	<BR>
	<B>Inquérito Espere Segundos -</B> Este é o número de segundos no modo de pesquisa o sistema irá aguardar pela entrada da pessoa chamada até que o inquérito ou a ação queda é acionado. Não é aplicada se o método de pesquisa é HANGUP. Padrão é de 10 segundos.

	<BR>
	<A NAME="campaigns-survey_ni_digit">
	<BR>
	<B>Digito de Não Interessado - </B> Este campo é onde você define qual digito o cliente aperta para dizer que não está interessado.

	<BR>
	<A NAME="campaigns-survey_ni_status">
	<BR>
	<B>Status p/ Não Interessado -</B> Este campo é onde você seleciona o estado a ser utilizado para Sem interesse. Se DNC é usado ea campanha está definida para utilizar DNC, em seguida, o número de telefone será automaticamente adicionado à lista de DNC interno e, possivelmente, a lista DNC específicos de campanha, se isso está habilitado na campanha.

	<BR>
	<A NAME="campaigns-survey_opt_in_audio_file">
	<BR>
	<B>Arquivo de Aceito - </B> Este é o arquivo de audio que é tocado quando o cliente aceita participar da pesquisa, não respondeu "não interessado" e não respondeu, se a opção de ação sem resposta for OPTOUT. Após esse arquivo de audio ser tocado, a ação do método de pesquisa é realizada 

	<BR>
	<A NAME="campaigns-survey_ni_audio_file">
	<BR>
	<B>Arquivo de Não Interessado - </B> Este é o nome do arquivo de audio que é tocado quando o cliente desistiu da pesquisa, não aceitou a pesquisa ou não respondeu, caso a ação para sem resposta seja OPTIN. Após este arquivo de audio ser tocado, a chamada será desligada.

	<BR>
	<A NAME="campaigns-survey_method">
	<BR>
	<B>Método de Pesquisa - </B> Esta opção define o que acontece com uma chamada após o cliente ter aceitado a pesquisa. AGENT_XFER irá enviar a chamada para o próximo agente disponível. VOICEMAIL irá enviar a chamada para o voicemail especificado no campo Correio de Voz. EXTENSION irá enviar o cliente para uma extensão definida no campo Extensão de Transf. HANGUP irá desligar o cliente. CAMPREC_60_WAV irá enviar o cliente para que grave uma resposta, esta gravação ficará na pasta com o nome da campanha, dentro do diretório de gravações da Campanha de Pesquisa CALLMENU irá enviar o cliente para o menu de chamada definido na lista de seleção abaixo. VMAIL_NO_INST enviará a chamada para a caixa de correio de voz que você tenha definido a seguir e não vai jogar instruções após a mensagem de correio de voz.

	<BR>
	<A NAME="campaigns-survey_no_response_action">
	<BR>
	<B>Ação p/ Sem Resposta - </B> Aqui é definido o que acontece se não há resposta para a pergunta da pesquisa. OPTIN só irá enviar a chamada para o método de pesquisa se o cliente apertar algum digito DTMF. OPTOUT irá enviar o cliente para o método mesmo que ele não pressione um dígito. GOTA vai cair a chamada usando o método da gota campanha, mas ainda registrar a chamada como a PM jogou status da mensagem.

	<BR>
	<A NAME="campaigns-survey_response_digit_map">
	<BR>
	<B> Mapa de Digitos de Resposta - </B> Aqui você pode definir uma descrição para representar cada dígito que o cliente pode selecionar

	<BR>
	<A NAME="campaigns-survey_xfer_exten">
	<BR>
	<B>Extensão de Transfer. - </B> Se o método de pesquisa selecionado for EXTENSION o cliente será transferido para esta extensão do plano de discagem.

	<BR>
	<A NAME="campaigns-survey_camp_record_dir">
	<BR>
	<B>Diretório de Gravação  - </B> Se o método de pesquisa selecionado for CAMPREC_60_WAV então o cliente poderá gravar uma mensagem que será colocada no diretório com o nome da campanha, dentro deste diretório

	<BR>
	<A NAME="campaigns-survey_third_digit">
	<BR>
	<B>Terceiro Dígito -</B> Isto permite que exista um terceiro caminho se o terceiro dígito conforme definido por este campo for pressionado pelo cliente.

	<BR>
	<A NAME="campaigns-survey_fourth_digit">
	<BR>
	<B>Quarto Dígito -</B> Isto permite que exista um quarto caminho se o quarto digito conforme digitado neste campo for digitado pelo cliente.

	<BR>
	<A NAME="campaigns-survey_third_audio_file">
	<BR>
	<B>Terceiro Arquivo de Audio -</B> Este é o terceiro arquivo de audio a ser tocado assim que seja selecionado pelo cliente a terceira opção.

	<BR>
	<A NAME="campaigns-survey_third_status">
	<BR>
	<B>Terceiro Status -</B> Este é o terceiro status usado para a chamada assim que o cliente escolhe a terceira opção.

	<BR>
	<A NAME="campaigns-survey_third_exten">
	<BR>
	<B>Terceira Extensão -</B> Esta é a terceira extensão usada pela chamada assim que é selecionada pelo cliente a terceira opção. O padrão é 8300 que imediatamente desliga a chamada após a mensagem de audio é reproduzida.

	<BR>
	<A NAME="campaigns-agent_display_dialable_leads">
	<BR>
	<B>Agente de pantalla Dialable Leads -</B> Esta opción se mostrará si está habilitado el número de derivaciones dialable disponibles en la campaña en la pantalla del agente. Este segundo número se actualiza en el sistema una vez por minuto y se actualiza en la pantalla del agente por cada par de.

	<BR>
	<A NAME="campaigns-survey_menu_id">
	<BR>
	<B>Menu de chamada pesquisa -</B> Se o método é definida como CALLMENU, este é o menu de chamada que o cliente é enviado para se optar por.

	<BR>
	<A NAME="campaigns-survey_recording">
	<BR>
	<B>Gravação de pesquisa -</B> Se ativado, isto irá iniciar a gravação quando a chamada é atendida. Apenas recomendado se o método não está definido para transferir a um agente. Padrão é n para deficientes físicos. Se definido como Y_WITH_AMD mesmo atendedor chamadas mensagens detectados serão registrados.

	<?php
	}
?>

<BR>
<A NAME="campaigns-next_agent_call">
<BR>
<B>Próximo Agente -</B> Isto determina qual agente recebe a próxima chamada disponível:
 <BR> &nbsp; - random: ordens do valor de atualização aleatória na tabela live_agents
 <BR> &nbsp; - oldest_call_start: ordena pela última vez que um agente recebeu uma chamada. Resulta nos agentes recebendo uma média igual de chamadas.
 <BR> &nbsp; - oldest_call_finish: ordena pela última vez que um agente finalizou uma chamada. Também conhecido como agente que está mais tempo esperando cliente, recebe a primeira chamada.
 <BR> &nbsp; - overall_user_level: ordens do user_level do agente, tal como definido na tabela de usuários uma user_level maior receberá mais chamadas.
 <BR> &nbsp; - campaign_rank: ordena pelo ranking dado para o agente na campanha. Mais alto para Mais baixo.
 <BR> &nbsp; - campaign_grade_random: dá uma maior probabilidade de obter uma chamada para os agentes de maior classificados.
 <BR> &nbsp; - fewest_calls: ordena pelo número de chamadas recebidas por um agente para o grupo de entrada específico. Menor qtd de chamadas primeiro.
 <BR> &nbsp; - longest_wait_time: pedidos por la cantidad de tiempo que el agente ha estado activamente la espera de una llamada de.

<BR>
<A NAME="campaigns-local_call_time">
<BR>
<B>Horário de chamada local -</B> Aqui é onde se configura o horário que se deseja discar, conforme determinação do horário no local para o qual está se discando. Isto é controlado por código de área e é ajustado para o horário de verão caso esteja em vigor. A recomendação para os EUA é de empresa para empresa, das 9:00 as 17:00 e da empresa para consumidor, das 9:00 as 21:00.

<BR>
<A NAME="campaigns-dial_prefix">
<BR>
<B>Prefixo de Discagem -</B> Este campo permite que facilmente configuremos o caminho de discagem sem precisar alteração de configurações de servidor e sem precisar recarregar o Asterisk. O padrão é 9 baseado em 91NXXNXXXXXX no plano de discagem - extensions.conf.

<BR>
<A NAME="campaigns-manual_dial_prefix">
<BR>
<B>Prefixo de discagem manual -</B> Este campo opcional permite que você defina o prefixo de discagem para ser usado somente quando fazer chamadas de discagem manual do agente de interface, como usar o recurso de discagem manual, ou Disque o próximo número, quando no método de discagem manual, manual ou marcação de números alt, ou programado pelo usuário apenas callbacks. O padrão é vazio para deficientes físicos, que utilizam o prefixo de discagem definido no campo acima. Esta opção não interfere com a opção 3way Prefixo de Discagem.

<BR>
<A NAME="campaigns-omit_phone_code">
<BR>
<B>Omitir Código de Telefone -</B> Este campo permite que você deixe o campo phone_code durante a discagem dentro do sistema. Por exemplo, se você está discando no Reino Unido a partir do Reino Unido, você teria 44 como seu campo phone_code para todas as ligações, mas você apenas deseja discar 10 dígitos em sua extensions.conf plano de discagem para fazer chamadas em vez de 44, em seguida, 10 dígitos. O padrão é N.

<BR>
<A NAME="campaigns-campaign_cid">
<BR>
<B>CallerID da Campanha -</B> Este campo permite o envio de um número callerid costume nas chamadas de saída. Este é o número que aparecer no callerid da pessoa que você está chamando. O padrão é desconhecida. Se você estiver usando T1 ou E1s para marcar esta opção só está disponível se você estiver usando PRIs - RDIS ou T1s E1s - que têm a função de callerid personalizado ativado, isso não vai funcionar com Roubado bits service-RBS-circuitos. Isso também irá trabalhar com a maioria dos VoIP-SIP ou IAX troncos provedores que permitem callerID saída dinâmica. O costume callerID só se aplica às chamadas feitas para a campanha diretamente, todas as chamadas terceiro partido ou transferências não enviará o costume callerID. NOTA: Às vezes, colocando desconhecidos ou privado no campo vai render o envio de seu padrão número callerID pela sua operadora com as chamadas. Você pode querer testar isso e colocar 0000000000 no campo callerid em vez se você não quiser enviar CallerID.

<BR>
<A NAME="campaigns-use_custom_cid">
<BR>
<B>Personalizado CallerID -</B> Quando definido para Y, esta opção permite que você use o campo security_phrase na tabela de lista como o CallerID para enviar ao colocar para cada ligação específica. Se este campo não tem CID nele então o CallerID Campanha definido acima será utilizado em seu lugar. Esta opção irá desativar a lista Override CallerID se há um CID presente no campo security_phrase. O padrão é N. Quando ajustado para CódigoDeÁrea você tem a possibilidade de ir para o submenu AC-CID e definir vários callerids para ser usado por areacode.

<BR>
<A NAME="campaigns-campaign_rec_exten">
<BR>
<B>Extensão de Gravação da Campanhasion -</B> Este campo permite uma extensão de gravação personalizado a ser usado com o sistema. Isso permite que você use extensões diferentes, dependendo de quanto tempo você quer para permitir que um máximo de gravação e que tipo de codec que você deseja gravar dentro A extensão padrão é 8309, que se você seguir os exemplos SCRATCH_INSTALL irá gravar no formato WAV para até uma hora. Outra opção incluídos nos exemplos é 8310 que irá gravar em formato GSM para até uma hora. O tempo de gravação pode ser alongado, aumentando a configuração na tela de modificação Servidor na seção de administração.

<BR>
<A NAME="campaigns-campaign_recording">
<BR>
<B>Gravação da Campanha -</B> Este menu permite selecionar qual o nível de gravação é permitido nesta campanha. NEVER irá desabilitar a gravação pele client. ONDEMAND é o padrão e permite que o agente inicie e pare a gravação conforme for preciso. ALLCALLS inicia a gravação pelo client quando uma chamada é enviada para o agente. ALLFORCE will start recording on the client whenever a call is sent to an agent giving the agent no option to stop recording. For ALLCALLS and ALLFORCE there is an option to use the Atraso de Gravação to cut down on very short recordings and reduce system load.

<BR>
<A NAME="campaigns-campaign_rec_filename">
<BR>
<B>Nome do Arquivo de Gravação da Campanha -</B> Este campo permite que você personalize o nome da gravação quando a gravação da campanha é OnDemand ou ALLCALLS. As variáveis ​​são permitidos CAMPANHA INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH AGENTE VENDORLEADCODE LEADID CALLID. O padrão é FULLDATE_AGENTE e ficaria assim 20051020-103108_6666. Outro exemplo é CAMPANHA_TINYDATE_CUSTPHONE que ficaria assim TESTCAMP_51020103108_3125551212. Te resultando nome do arquivo deve ser inferior a 90 caracteres.

<BR>
<A NAME="campaigns-allcalls_delay">
<BR>
<B>Atraso de Gravação -</B> Somente para gravações ALLCALLS e ALLFORCE. Esta configuração irá atrasar a gravação da chamada o tempo em segundos especificado no campo. O padrão é 0.

<BR>
<A NAME="campaigns-per_call_notes">
<BR>
<B>Chame Notes por chamada -</B> Definir esta opção como ENABLED vai permitir que agentes para entrar em notas para cada chamada lidar com eles na interface do agente. O campo de entrada de notas irá aparecer abaixo do campo Observações na interface do agente. Além disso, se o Grupo Usuário Agent tem permissão para ver registros de chamadas, em seguida, o agente será capaz de ver as notas de chamada passado para a liderança a qualquer momento. O padrão é DISABLED.

<BR>
<A NAME="campaigns-hide_call_log_info">
<BR>
<B>Esconder Call Log Informações -</B> Ativando esta opção irá esconder qualquer registo de chamadas ou ligue para informações de contagem quando a informação principal é exibido na tela do agente. O padrão é N.

<BR>
<A NAME="campaigns-agent_lead_search">
<BR>
<B>Pesquisa de chumbo agente -</B> Definir esta opção como ENABLED permitirá que agentes para procurar pistas e visualizar informações de chumbo durante uma pausa na interface do agente. Além disso, se o Grupo Usuário Agent tem permissão para ver registros de chamadas, em seguida, o agente será capaz de ver as notas de chamada passado por qualquer dica que eles estão vendo informações sobre. O padrão é DISABLED. LIVE_CALL_INBOUND permitirá busca por um tempo de chumbo em apenas uma chamada de entrada. LIVE_CALL_INBOUND_AND_MANUAL permitirá busca por um tempo de chumbo em uma chamada de entrada ou durante a pausa. Quando Pesquisa chumbo é utilizado em uma chamada de entrada ao vivo, a liderança da chamada, quando ele foi para o agente será alterado para um status de LSMERG, e os logs para a chamada será modificado para ligar para o agente de ligação selecionado em vez.

<BR>
<A NAME="campaigns-agent_lead_search_method">
<BR>
<B>Agente Método de Pesquisa de chumbo -</B> Se a pesquisa de chumbo Agente está habilitado, essa configuração define onde o agente será permitido a procurar por pistas. O sistema irá procurar todo o sistema, CAMPANHALISTS irá procurar dentro de todas as listas de ativos dentro da campanha, CAMPLISTS_ALL irá procurar dentro de todas as listas de ativos e inativos dentro da campanha, LISTA irá procurar somente dentro do ID manual Dial lista, conforme definida na campanha . O padrão é CAMPLISTS_ALL. Uma dessas opções com USER_ na frente só vai procurar dentro de leads que têm o campo proprietário correspondente ao ID do usuário do agente, as opções com GROUP_ na frente só vai procurar dentro de leads que têm o campo proprietário correspondente ao grupo de usuários que o usuário é um membro, as opções com TERRITORY_ na frente só vai procurar dentro de leads que têm o campo proprietário combinando os territórios que o agente tem selecionados.

<BR>
<A NAME="campaigns-campaign_script">
<BR>
<B>Script da Campanha -</B> Este menu permite que seja escolhido um script que irá aparecer na tela do agente para essa campanha. Selecione NONE para não mostrar scripts para esta campanha.

<BR>
<A NAME="campaigns-get_call_launch">
<BR>
<B>Abrir com Chamada -</B> Este menu permite que seja escolhido se você deseja abrir automaticamente a pagina web em uma janela separada, abrir a aba SCRIPT ou nao fazer nada quando uma chamada é enviada ao agente para esta campanha. Se os campos personalizados da lista estão habilitados em seu sistema, formulário será aberto no separador FORMULÁRIO na conexão de uma chamada para um agente.

<BR>
<A NAME="campaigns-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Estes campos permitem que você tenha dois conjuntos de Transferência e Conferência presets DTMF. Quando a chamada ou campanha é carregado, a tela do agente irá mostrar dois botões no quadro de transferência de conferência e preencher automaticamente o número para discar e os campos de envio-DTMF quando pressionado. Se você quiser permitir transferências consultivos, um fronter a um mais perto, ter o agente usar a caixa de seleção CONSULTIVO, que não trabalha para terceiros não agentes chamadas consultivos. Para aqueles que só tem o agente, clique no botão de discagem com Cliente. Em seguida, o agente pode apenas deixá-3WAY-CALL e passar para a próxima chamada. Se você quiser permitir transferências de Cegos de clientes para um script AGI para logging ou um IVR, em seguida, coloque AXFER no campo número para discar. Você também pode especificar uma extensão personalizada após o AXFER, por exemplo, se você quiser fazer uma chamada para um IVR especial que você definiu para o ramal 83900 você colocaria AXFER83900 no campo número para discar.

<BR>
<A NAME="campaigns-quick_transfer_button">
<BR>
<B>Rápida transferencia Button -</B> Esta opción agrega un botón de la transferencia rápida a la pantalla por debajo de la agente de transferencia de botón Conf. que permitirá la transferencia de un solo clic ciego de llamadas a los seleccionados en Grupo o un número. IN_GROUP enviará pide al Grupo de Xfer predeterminado para esta campaña, o en grupo-si hay una llamada entrante. Las opciones predeterminadas enviará las llamadas a los preset seleccionado. Por defecto es n para discapacitados. As opções bloqueadas são usados ​​para bloquear o valor para o botão Quick Transfer mesmo que o agente usa os recursos de conferência de transferência durante uma chamada antes de usar o botão Quick Transfer.

<BR>
<A NAME="campaigns-custom_3way_button_transfer">
<BR>
<B>Personalizado Transferência Botão 3-Way -</B> Esta opção irá adicionar um botão personalizado Transferência para a tela do agente abaixo do botão de transferência de Conf, que permitirá um clique de três vias chamadas usando o pré-selecionada ou campo. As opções PRESET_ irá efetuar chamadas usando o valor definido predefinido. As opções field_ irá efetuar chamadas usando o número no campo selecionado a partir do chumbo. DEFICIENTES não vai mostrar o botão na tela do agente. As opções PARK_ irá estacionar o cliente antes de discar. O padrão é DISABLED. A opção VIEW_PRESET simplesmente abrir o quadro de transferência e do quadro pré-. A opção VIEW_CONTACTS irá abrir uma janela de busca de contatos, isto só irá funcionar se Presets Enable está configurada para CONTACTOS.

<BR>
<A NAME="campaigns-prepopulate_transfer_preset">
<BR>
<B>Transferencia de rellenar previamente Preset -</B> Esta opción le proporcione el número telefónico al campo en el marco de la Conferencia de transferencia de la pantalla si el agente definido. Por defecto es n para discapacitados.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Habilitar Presets de transferência -</B> Esta opção permitirá que os Presets Sub menu a aparecer no topo da página Modificação da campanha, e você também terá a capacidade para especificar números de discagem pré-selecionadas para Agentes de utilizar no quadro de transferência de Conferência de o agente de interface. O padrão é DISABLED. CONTACTOS é uma opção apenas se contact_information está habilitado em seu sistema, que é um recurso personalizado.

<BR>
<A NAME="campaigns-enable_xfer_presets">
<BR>
<B>Habilitar Presets de transferência -</B> Esta opção permitirá que os Presets Sub menu a aparecer no topo da página Modificação da campanha, e você também terá a capacidade para especificar números de discagem pré-selecionadas para Agentes de utilizar no quadro de transferência de Conferência de o agente de interface. O padrão é DISABLED. CONTACTOS é uma opção apenas se contact_information está habilitado em seu sistema, que é um recurso personalizado.

<BR>
<A NAME="campaigns-hide_xfer_number_to_dial">
<BR>
<B>Esconder número de transferência para discar -</B> Esta opção irá esconder o número a ser discado campo no quadro de transferência de Conferência de o agente de interface. O padrão é DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call">
<BR>
<B>Estacionamento de chamadas IVR -</B> Esta opção vai permitir que um agente para estacionar uma chamada com um botão separado CHAMADA IVR PARK em sua interface agente se este está habilitado ou ENABLED_PARK_ONLY. A opção ENABLED_PARK_ONLY vai permitir ao agente para enviar a chamada para estacionar, mas não clique para recuperar a chamada como a opção Enabled. A opção ENABLED_BUTTON_HIDDEN permite que a função através da API só. O padrão é DISABLED.

<BR>
<A NAME="campaigns-ivr_park_call_agi">
<BR>
<B>Estacionamento de chamadas IVR AGI -</B> Se o Parque campo IVR chamada não está desativado, então este campo é usado como a seqüência de aplicação AGI que o cliente seja enviado. Esta é uma configuração que deve ser definido pelo administrador, se possível.

<BR>
<A NAME="campaigns-timer_action">
<BR>
<B>Temporizador de Acción de -</B> Esta característica le permite activar acciones después de una cierta cantidad de tiempo. D1 y D2 opciones de marcación lanzará un llamamiento a la transferencia de presets Conferencia Número y enviarlos a la sesión del agente, éste se utiliza generalmente para aplicaciones sencillas de validación IVR AGI o simplemente para jugar un mensaje pregrabado. WebForm se abrirá la dirección de formulario web. MESSAGE_ONLY simplemente mostrará el mensaje de que está en el campo de abajo. Ninguna de ellas podrá deshabilitar esta característica y es el predeterminado. HANGUP irá desligar a chamada quando o cronômetro é disparado, CALLMENU enviará a chamada para o menu de chamada especificado no campo Destino Temporizador Ação, EXTENSÃO enviará a chamada para o ramal que está especificado no campo Destino Temporizador Ação, IN_GROUP enviará a chamada para o No Grupo-especificado no campo Destino Temporizador Ação.

<BR>
<A NAME="campaigns-timer_action_message">
<BR>
<B>Temporizador de mensaje de acción -</B> Este es el mensaje que aparece en la pantalla del agente en el momento de la acción del temporizador se dispara.

<BR>
<A NAME="campaigns-timer_action_seconds">
<BR>
<B>Temporizador Segundos Acción -</B> Esta es la cantidad de tiempo después de la llamada se conecta a los clientes que se desencadena la acción del temporizador. El valor predeterminado es -1, que también está inactivo.

<BR>
<A NAME="campaigns-timer_action_destination">
<BR>
<B>Destino Ação temporizador -</B> Este campo é onde você especificar a chamada do menu de extensão, ou em grupo que você deseja a chamada enviada se o tempo de ação é definido como EXTENSÃO CALLMENU, ou IN_GROUP. O padrão é vazio.

<BR>
<A NAME="campaigns-scheduled_callbacks">
<BR>
<B>Chamadas Agendadas -</B> Permite um agente classificar uma chamada como CALLBK e escolher uma data e hora na qual deseja que o registro seja discado.

<BR>
<A NAME="campaigns-scheduled_callbacks_alert">
<BR>
<B>Callbacks alerta agendadas -</B> Esta opção permite que a linha de status callbacks na interface do agente a ser vermelho, piscar ou piscar em vermelho quando há AGENTONLY agendada callbacks que atingiram o seu tempo de disparo e data. O padrão é NONE para a linha de status padrão. As opções de adiar irá parar de piscar e ou exibição no vermelho quando você verificar os retornos de chamada, até que o número de retornos de chamada mudanças.

<BR>
<A NAME="campaigns-scheduled_callbacks_count">
<BR>
<B>Callbacks agendadas Contagem -</B> These options allows you to limit the viewable callbacks in the agent callback alert section on the agent screen, to only LIVE callbacks.  LIVE callbacks are user-only scheduled callbacks that have hit their trigger date and time. ACTIVE call backs are user-only callbacks that are active in the system but have not yet triggered.  You can view both ACTIVE and LIVE callbacks by selecting ALL_ACTIVE.  Default is ALL_ACTIVE.

<BR>
<A NAME="campaigns-callback_days_limit">
<BR>
<B>Programado Callbacks Limite Dias -</B> Esta opção permite que você reduza o agente callbacks programadas do calendário para um número selecionável de dias a partir de hoje, o calendário do mês completa 12 ainda será exibido, mas apenas o número de dias será selecionável. O padrão é 0 para ilimitado.

<BR>
<A NAME="campaigns-callback_hours_block">
<BR>
<B>Programado Callbacks Bloco Horas -</B> Esta opção permite que você restringir um callback USERONLY agendado seja exibido na lista de chamada de retorno agente até X horas depois de ter sido definido. O padrão é 0 para nenhum bloco.

<BR>
<A NAME="campaigns-callback_list_calltime">
<BR>
<B>Callbacks agendadas Bloco Calltime -</B> Esta opção, se habilitado irá impedir o retorno agendado na lista de chamada de retorno do agente de que está sendo chamado se estiver fora do calltime prevista para a campanha. O padrão é DISABLED.

<BR>
<A NAME="campaigns-my_callback_option">
<BR>
<B>Meu padrão Checkbox Callbacks -</B> Esta opção permite-lhe pré-definir a opção de chamada de retorno na tela My callback agente programado. CHECKED irá verificar a caixa de seleção automaticamente para cada chamada. O padrão é UNCHECKED.

<BR>
<A NAME="campaigns-wrapup_seconds">
<BR>
<B>Segundos de Pós Atendimento -</B> Quantidade de segundos para forçar o agente esperar antes de permitir que ele receba ou faça outra chamada. O timer inicia assim que o agente desliga a chamada com o cliente, ou em caso de chamada a número alternativo, quando termina o trabalho com um registro. Valor padrão é 0 segundos. Se o timer acabar antes que o agente tenha finalizado a chamada, o agente não receberá novo registro até que finalize o atual.

<BR>
<A NAME="campaigns-wrapup_message">
<BR>
<B>Mensagem de Pós Atendimento -</B> É uma mensagem específica da campanha a ser mostrada na tela de pós atendimento se o tempo de pós atendimento for configurado.

<BR>
<A NAME="campaigns-disable_dispo_screen">
<BR>
<B>Desativar Tela Dispo -</B> Esta opção permite que você desabilite a tela disposição na interface do agente. O Desabilitar campo Status Dispo abaixo devem ser preenchidos para que essa opção funcione. O padrão é DISPO_ENABLED. A opção DISPO_SELECT_DISABLED não irá desativar a tela dispo completamente, mas vai exibir a tela dispo sem quaisquer disposições, esta opção só deve ser usado se você quiser forçar os agentes a usar o software de CRM que vai enviar o status para o sistema através da API.

<BR>
<A NAME="campaigns-disable_dispo_status">
<BR>
<B>Desativar Status Dispo -</B> Se a opção Desativar Tela Dispo está definido para DISPO_DISABLED, então este campo deve ser preenchido polegadas Você pode usar qualquer disposição que você deseja para este campo, desde que ele é de 1 a 6 caracteres de comprimento com apenas letras e números.

<BR>
<A NAME="campaigns-dead_max">
<BR>
<B>Morto Chamada Máx. Segundos -</B> Se isso for definido como maior que 0, depois de um cliente desliga e o agente não tenha clicado no botão Desligar Cliente neste número de segundos, a chamada será automaticamente desligou, o status abaixo será definida eo agente será pausada. O padrão é 0 para deficientes.

<BR>
<A NAME="campaigns-dead_max_dispo">
<BR>
<B>Morto Status da chamada Max -</B> Se Morto Chamada Máx. Segundos está habilitado, este é o status definido para a chamada quando o agente de call mortos não é desligou passado o número de segundos colocados acima. O padrão é DCMX.

<BR>
<A NAME="campaigns-dispo_max">
<BR>
<B>Dispo Chamada Máx. Segundos -</B> Se isso for definido como maior que 0, eo agente não selecionou um status disposição neste número de segundos, a chamada será automaticamente ajustado para o estado abaixo e o agente será pausada. O padrão é 0 para deficientes.

<BR>
<A NAME="campaigns-dispo_max_dispo">
<BR>
<B>Dispo Chamada Max Estado -</B> Se Dispo Chamada Máx. Segundos está habilitado, este é o status definido para a chamada quando o agente não selecionou um status passado o número de segundos colocados acima. O padrão é DISMX.

<BR>
<A NAME="campaigns-pause_max">
<BR>
<B>Agente Pausa Máx. Segundos -</B> Se isso for definido como maior que 0, eo agente não saiu do estado de pausa neste número de segundos, o agente será automaticamente desconectado da tela do agente. O padrão é 0 para deficientes.

<BR>
<A NAME="campaigns-screen_labels">
<BR>
<B>Tela Etiquetas Agente -</B> Você pode selecionar um conjunto de etiquetas de agente de tela para usar com esta opção. O padrão é - configurações de sistema - para os rótulos padrão.

<BR>
<A NAME="campaigns-status_display_fields">
<BR>
<B>Mostrar campos de status -</B> Você pode selecionar quais variáveis ​​para as chamadas serão exibidos na linha de status da tela do agente. CALLID irá exibir a 20 caracteres ID chamada única, LEADID irá exibir a ID principal do sistema, ListId irá exibir o ID lista. O padrão é CALLID.

<BR>
<A NAME="campaigns-use_internal_dnc">
<BR>
<B>Usar Lista de Bloqueio Interna -</B> Define se esta campanha deve filtrar registros levando em conta a lista de bloqueio interna. Se estiver configurada como Y, o hopper irá procurar cada número na lista de bloqueio antes de carregar. Se o número estiver na lista de bloqueio, o registro de chamada será alterado para o status DNCL assim não será chamado. Padrão é N. La opción codigoDeArea es como la opción-y, excepto que se utiliza para filtrar también un código de área en toda América del Norte desde que se está marcando, en este caso mediante la entrada en la lista 201XXXXXXX DNC podría bloquear todas las llamadas a los 201 codigoDeArea si está habilitada.

<BR>
<A NAME="campaigns-use_campaign_dnc">
<BR>
<B>Usar Lista de Bloqueio - </B> Este recurso define se a campanha deve filtrar registros usando a lista de Bloqueio que é específica para esta campanha. Se configurado como Y, o hopper irá procurar cada número na lista específica de Bloqueio antes de colocar o número na lista do hopper. Se estiver em uma lista específica de Bloqueio, então ele irá alterar o status do registro para DNCC para que este registro não seja discado. O padrão é N La opción codigoDeArea es como la opción-y, excepto que se utiliza para filtrar también un código de área en toda América del Norte desde que se está marcando, en este caso mediante la entrada en la lista 201XXXXXXX DNC podría bloquear todas las llamadas a los 201 codigoDeArea si está habilitada.

<BR>
<A NAME="campaigns-use_other_campaign_dnc">
<BR>
<B>Outros DNC Campanha -</B> Se a opção de utilizar a lista DNC campanha está ativada, esta opção pode permitir que você use uma lista DNC campanha diferente, basta colocar o ID da campanha da outra campanha neste campo. Se você usar esta opção, a lista original DNC campanha não será mais marcada, apenas a outra lista DNC campanha será usado. Isso não afeta o uso da lista DNC sistema interno. Padrão é vazio.

<BR>
<A NAME="campaigns-closer_campaigns">
<BR>
<B>Grupos De entrada Permitidos -</B> For CLOSER campaigns only. Here is where you select the inbound groups you want agents in this CLOSER campaign to be able to take calls from. It is important for BLENDED inbound-outbound campaigns only to select the inbound groups that are used for agents in this campaign. The calls coming into the inbound groups selected here will be counted as active calls for a blended campaign even if all agents in the campaign are not logged in to receive calls from all of those selected inbound groups.

<BR>
<A NAME="campaigns-agent_pause_codes_active">
<BR>
<B>AgentCódigos de PausaAtivo -</B> Permite que os agentes para seleccionar um código de pausa quando clicam no botão de pausa na tela do agente. Pausa códigos são definíveis por campanha na parte inferior da tela detalhe o ponto de vista da campanha e eles são armazenados na tabela agent_log. O padrão é N. FORCE irá forçar os agentes a escolher um código PAUSA se clicar no botão de pausa.

<BR>
<A NAME="campaigns-auto_pause_precall">
<BR>
<B>Auto Pausa trabalho de pré-Call -</B> Em auto dial-modo, esta definição se habilitado irá definir o agente para uma pausa automaticamente quando os cliques do agente em qualquer das seguintes funções que os obriga a ser pausado-Manual Dial, Dial pesquisa rápida e chumbo, Call Log View, Callbacks Verifique, Digite o Código Pause. Padrão é n para inativos.

<BR>
<A NAME="campaigns-auto_resume_precall">
<BR>
<B>Auto Resume Pré-Call Work -</B> Em auto dial-modo, esta definição se habilitado irá definir o agente ativo automaticamente quando o agente clica para fora, ou cancela, em qualquer das seguintes funções que os obriga a ser interrompida Dial-Manual, Discagem rápida, Pesquisa chumbo, Call Entrar View, Callbacks Verifique, insira o código de pausa. Padrão é n para inativos.

<BR>
<A NAME="campaigns-auto_pause_precall_code">
<BR>
<B>Auto Pausa Código Pré-Call -</B> Se o Auto Pause função trabalho de pré-Call acima é ativo, e Códigos Pause agente está ativo, essa configuração será o código de pausa que é usado quando o agente está em pausa para essas atividades. O padrão é PRECAL.

<BR>
<A NAME="campaigns-disable_alter_custdata">
<BR>
<B>Desabilitar Alteração nos Dados do Cliente -</B> Quando configurada como Y, não permite ao usuário alterar registros de cliente quando o Agente finaliza a chamada. O padrão é N.

<BR>
<A NAME="campaigns-disable_alter_custphone">
<BR>
<B>Desab. Alterar Telefone do Cliente - </B> Se configurado como Y, não altera o telefone do cliente quando um agente finaliza a chamada. O padrão é Y. Utilice la opción Ocultar para eliminar completamente el número de teléfono del cliente de la pantalla del agente de.

<BR>
<A NAME="campaigns-display_queue_count">
<BR>
<B>Mostrar Contagem da Fila ao Agente - </B> Quando configurada como Y, quando um cliente está esperando um agente, será demonstrado na tela do agente a quantidade de clientes em espera. O padrão é Y.

<BR>
<A NAME="campaigns-manual_dial_override">
<BR>
<B>Manual de Dial Override -</B> A configuração pode substituir os Usuários definição para a capacidade de discagem manual para agentes quando eles são registrados nesta campanha. NONE seguirá a definição de Usuários, ALLOW_ALL permitirá que qualquer agente registrados nesta campanha para fazer chamadas de discagem manual, DISABLE_ALL não permitirá que ninguém registrados nesta campanha para fazer chamadas de discagem manual. O padrão é NONE.

<BR>
<A NAME="campaigns-manual_dial_list_id">
<BR>
<B>Manual Dial ID da Lista -</B> O padrão list_id para ser usado quando um agente faz uma chamada manual e um novo recorde de chumbo é criada na tabela da lista. O padrão é 999. Este campo pode conter apenas dígitos.

<BR>
<A NAME="campaigns-manual_dial_filter">
<BR>
<B>Filtro de Discagem Manual - </B> Permite que você filtre as chamadas que os agentes fazem no modo manual de discagem para esta campanha por uma combinação dos seguintes: DNC - para tirar, CAMPANHALISTS - o número deve estar nas lístas para a campanha, NONE - sem filtros na discagem manual ou lista de discagem rápida . CAMPLISTS_ALL - incluem listas de inativos na pesquisa para o número.

<BR>
<A NAME="campaigns-manual_dial_search_checkbox">
<BR>
<B>Manual Dial Pesquisa Caixa de seleção -</B> Isso permite que você defina se você quer a caixa de seleção busca manual de marcação a ser selecionada por padrão ou não. Se uma opção com RESET é escolhido, em seguida, a caixa de seleção será reiniciado após cada chamada. Padrão é selecionado.

<BR>
<A NAME="campaigns-manual_preview_dial">
<BR>
<B>Dial visualização manual -</B> Isso permite que o agente em modo de discagem manual para ver as informações de chumbo, quando clicar em Discar Número seguida antes que eles ativamente discar o telefonema. Há um link opcional para Ignorar a ligação e passar para a próxima, se selecionado. O padrão é PREVIEW_AND_SKIP.

<BR>
<A NAME="campaigns-manual_dial_lead_id">
<BR>
<B>Discagem manual por chumbo ID -</B> Isso permite que o agente em modo de discagem manual para fazer uma chamada por lead_id em vez de um número de telefone. O padrão é N para deficientes.

<BR>
<A NAME="campaigns-api_manual_dial">
<BR>
<B>Manual de Dial API -</B> Esta opção permite que você defina a API do agente para fazer qualquer chamada um a um, hora, ou a capacidade de fila chamadas de discagem manual e tê-los discar automaticamente uma vez que o agente entra em pausa ou está disponível para atender a sua chamada próxima com o opção de desativar a discagem automática dessas chamadas, QUEUE, ou QUEUE_AND_AUTOCALL que é a mesma fila, mas sem a opção de desativar a discagem automática das chamadas. Se um agente tem mais do que uma chamada na fila para eles vai ver a conta de quantas chamadas de discagem manual estão na fila logo abaixo do botão de pausa, ou Disque botão próximo número. Sugerimos que se QUEUE é usado que você envie ações API usando o preview = YES opção para que você não está repetidamente discagem de chamadas para o agente sem aviso prévio. Além disso, se estiver usando QUEUE e fortemente usando chamadas de discagem manual em um método não MANUAL marcação, recomendamos definir o Pause Agente Após Cada opção chamada para Y. O padrão é PADRÃO.

<BR>
<A NAME="campaigns-manual_dial_call_time_check">
<BR>
<B>Call Time manual Confira -</B> Se esta opção estiver ativada, ele irá verificar todas as chamadas de discagem manual para se certificar que estão dentro das configurações de chamada de tempo definido para a campanha. O padrão é DISABLED.

<BR>
<A NAME="campaigns-manual_dial_cid">
<BR>
<B>Marcação Manual CID -</B> Este define se um agente fazer chamadas discagem manual terá as configurações de campanha callerID usado, ou seus agentes as configurações do telefone callerID usado. O padrão é CAMPANHA. Se a opção Usar campanha personalizado CID está ativado ou a lista Campanha configuração Override CID é usada, esta configuração será ignorada.

<BR>
<A NAME="campaigns-post_phone_time_diff_alert">
<BR>
<B>Telefone Mensagem de Alerta Diferença de tempo -</B> Este recurso manual-dial-somente, se ativada, irá mostrar um alerta se o fuso horário para o código postal de chumbo, ou código postal, é diferente do fuso horário do código de área do número de telefone para a liderança. A opção OUTSIDE_CALLTIME_ONLY só irá mostrar a indicação se as duas zonas de tempo são diferentes e uma das zonas de tempo está fora do tempo de permanência seleccionado para a campanha. OUTSIDE_CALLTIME_PHONE só verificar o fuso horário do número de telefone do chumbo e alertar se estiver fora do tempo de chamada local. OUTSIDE_CALLTIME_POSTAL só verificar o fuso horário do código postal do chumbo e alertar se estiver fora do tempo de chamada local. OUTSIDE_CALLTIME_BOTH irá verificar o código postal e número de telefone para estar dentro do tempo de chamada local, mesmo se eles estão no mesmo fuso horário. Esses alertas irá mostrar na informação do registo de chamadas, informações callbacks lista, informações de resultados de pesquisa, quando uma ligação é marcado e quando uma ligação é visualizado. O padrão é DISABLED.

<BR>
<A NAME="campaigns-in_group_dial">
<BR>
<B>Manual de In-Grupo Dial -</B> Este recurso permite que você ative a capacidade de agentes para colocar marcação chamadas de saída manuais que são registrados como em grupo chama atribuído a um específico grupo. A opção MANUAL_DIAL permite a colocação de chamadas telefônicas através de uma In-Group com o agente efetuar uma chamada. A opção NO_DIAL permite ao agente registar tempo numa chamada que não existe, como se fosse uma chamada real, isto é frequentemente usado para registo de e-mail ou fax tempo. A opção Ambos irão permitir que tanto apelo e sem chamada de marcação em grupo. O padrão é desabilitado.

<BR>
<A NAME="campaigns-in_group_dial_select">
<BR>
<B>No Grupo Discagem Manual Select -</B> Esta opção só está activo se o exposto no Grupo característica Dial manual não está desativado. Esta opção restringe o selecionável In-Grupos que o agente pode colocar no Grupo manual Dial chama completamente. CAMPANHA_SELECTED só aparecem as em grupos que a campanha estabeleceu como permitido em grupos. ALL_USER_GROUP vai mostrar todos os grupos em que são visíveis para os membros do grupo de usuário que o agente pertence.

<BR>
<A NAME="campaigns-agent_clipboard_copy">
<BR>
<B>Cópia p/ Área de Transf. do Agente - </B> ESTE RECURSO SÓ FUNCIONA NO INTERNET EXPLORER. Este recurso permite que você escolha um campo que será copiado para a área de transferência do agente quando uma chamada é enviada ao agente. Um uso comum para este recurso é permitir fácil uso do comando "colar" para números do cliente ou números de telefone em aplicativos cliente no computador do agente.

<BR>
<A NAME="campaigns-three_way_call_cid">
<BR>
<B>3-Way Call CallerID de Saída -</B> Isso define o que é enviado para fora como o número de chamadas de saída callerID 3 vias colocados pelo agente, CAMPANHA usa a campanha costume callerID, CLIENTE usa o número do cliente que está ativo na tela e os agentes AGENT_PHONE usa o callerID para o telefone que o agente está conectado. AGENT_CHOOSE permite que o agente de escolher qual callerID usar para chamadas de 3 vias de uma lista de opções. CUSTOM_CID usará o CID personalizado que é definido no campo security_phrase da tabela de lista para a liderança.

<BR>
<A NAME="campaigns-three_way_dial_prefix">
<BR>
<B>Prefixo para Chamadas a 3 - </B> Isso define o que será usado como prefixo de discagem nas chamadas a 3, o padrão é vazio, então o prefixo da campanha é usado, uma alternativa para se ouvir tocar é 88.

<BR>
<A NAME="campaigns-customer_3way_hangup_logging">
<BR>
<B>Cliente 3-Way Logging Hangup -</B> Se esta opção estiver habilitada a user_call_log irá registrar quando um cliente hangup até se desligar durante uma chamada 3-way. Além disso, este pode permitir para o cliente 3-forma acção hangup se um é defineed abaixo. O padrão é ativado.

<BR>
<A NAME="campaigns-customer_3way_hangup_seconds">
<BR>
<B>Cliente 3-Way Segundos hangup -</B> Se o registro do cliente 3-way é ativada, esta opção permite que você defina o número de segundos após o desligamento do cliente é detectado antes que ele está realmente conectado eo cliente opcional ação hangup de 3 vias é executado. O padrão é 5 segundos.

<BR>
<A NAME="campaigns-customer_3way_hangup_action">
<BR>
<B>Cliente 3-Way Ação Hangup -</B> Se o registro do cliente 3-way é ativada, esta opção permite-lhe ter a tela de agente automaticamente pendurar-se sobre a chamada e ir para a tela DISPO se esta opção estiver definida para DISPO. O padrão é NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="campaigns-qc_enabled">
	<BR>
	<B>CQ Ativado - </B> Configurando este campo como Y permite que os recursos de Controle de Qualidade do agente funcionem. O padrão é N.

	<BR>
	<A NAME="campaigns-qc_statuses">
	<BR>
	<B>Status de CQ - </B> Esta área é onde você escolhe quais status de registro devem passar pelo Controle de Qualidade. Selecione o status que você quer que o CQ revise. 

	<BR>
	<A NAME="campaigns-qc_shift_id">
	<BR>
	<B>Turno de CQ- </B> Este é o controle de tempo usado para trazer registros de uma campanha para o CQ. Os dias da semana são ignorados para esta função.

	<BR>
	<A NAME="campaigns-qc_get_record_launch">
	<BR>
	<B>Entrada do Registro de CQ -</B> Isso permite uma das seguintes ações a ser ativada na entrada de um registro de CQ para um agente.

	<BR>
	<A NAME="campaigns-qc_show_recording">
	<BR>
	<B>Mostrar Gravação do CQ - </B> Permite que uma gravação que está ligada com o registro do CQ seja mostrada na tela do agente de CQ.

	<BR>
	<A NAME="campaigns-qc_web_form_address">
	<BR>
	<B>Endereço WebForm de CQ - </B> Este é o endereço do websitem que o agente de CQ pode entrar quando clicar no botão WEBFORM na tela de CQ.

	<BR>
	<A NAME="campaigns-qc_script">
	<BR>
	<B>Script CQ - </B> Este é o Script que pode ser usado pelos agentes de CQ na aba SCRIPT na tela de CQ.
	<?php
	}
?>

<BR>
<A NAME="campaigns-vtiger_search_category">
<BR>
<B>Vtiger Search Category -</B> Se a integração Vtiger está habilitado nas configurações do sistema, em seguida, essa configuração vai definir onde a página vtiger_search.php vai procurar o número de telefone que foi digitado. Existem 4 opções que podem ser utilizados neste campo: LEAD-Esta opção irá procurar através do Vtiger leva apenas, CONTA-Esta opção irá procurar através de contas vtiger e todos os contatos e sub-contactos para o número de telefone, o vendedor-Esta opção só irá procurar através dos fornecedores vtiger, ACCTID-Esta opção funciona somente para contas e vai entrar em campo vendor_lead_code lista e tentar procurar a conta ID Vtiger. Se não tiver êxito ele vai tentar todos os outros métodos listados que você selecionou. Várias opções pode ser utilizada para cada pesquisa, mas em grandes bases de dados não seja recomendado. O padrão é LEAD. UNIFIED_CONTACT-Esta opção irá utilizar a versão beta 5.1.0 Vtiger característica de busca por número de telefone e abrir uma página de pesquisa em Vtiger.

<BR>
<A NAME="campaigns-vtiger_search_dead">
<BR>
<B>Pesquisar contas Mortas no Vtiger -</B> Se a integração com o Vtiger estiver habilitada nas confifurações do sistema então esta configuração define se contas apagadas serão pesquisadas quando o agente clica em WEB FORM para procurar no sistema Vtiger. DISABLED- registros apagados não serão pesquisados, ASK- registros apagados serão pesquisados e a página de pesquisa do vtiger irá perguntar ao agente se ele quer ativar a conta no Vtiger, RESURRECT- irá automaticamente ativar a conta e levar o agente para a tela da conta sem atrasos após clicar em WEB FORM. O padrão é DISABLED.

<BR>
<A NAME="campaigns-vtiger_create_call_record">
<BR>
<B>Vtiger Cria Registro de Chamada - </B> Se a integração com Vtiger estiver habilitada, esta configuração define se um registro de atividade do Vtiger é gravado quando o agente entra na página de pesquisa do vtiger (vtiger_search). O padrão é Y. A opção DISPO irá criar um registro de chamada para a conta Vtiger sem que o agente precise ir para a página da conta no Vtiger através do botão WEB FORM.

<BR>
<A NAME="campaigns-vtiger_create_lead_record">
<BR>
<B>Criar Registro de Tel. no Vtiger-</B> Se a integração com o Vtiger estiver habilitada no sistema e a Categoria de Pesquisa no Vtiger incluir LEAD, esta configuração define se um novo registro da chamada do Vtiger é criado quando o agente entra na página de pesquisa do vtiger e o registro não é encontrado para que se tenha um número de telefone. O padrão é Y.

<BR>
<A NAME="campaigns-vtiger_screen_login">
<BR>
<B>Vtiger Screen Login -</B> Se a integração Vtiger está habilitado nas configurações do sistema, em seguida, essa configuração vai definir se o usuário estiver conectado na interface Vtiger automaticamente quando entrar para a tela do agente. O padrão é Y. A opção NEW_WINDOW vai abrir uma nova janela no login para a tela do agente.

<BR>
<A NAME="campaigns-vtiger_status_call">
<BR>
<B>Status da Chamada no Vtiger -</B> Se a integração Vtiger está habilitado nas configurações do sistema, em seguida, essa configuração vai definir se o estado da Conta Vtiger será atualizado com o estado da chamada depois de ter sido dispositioned. O padrão é N.

<BR>
<A NAME="campaigns-queuemetrics_callstatus">
<BR>
<B>QM CallStatus Override -</B> Se a integração QueueMetrics está ativado nas configurações do sistema, em seguida, essa configuração permite que o principal das configurações do sistema de definição para as entradas CallStatus queue_log. O padrão é DISABLED que irá utilizar a configuração do sistema.

<BR>
<A NAME="campaigns-queuemetrics_phone_environment">
<BR>
<B>QM Ambiente Telefone -</B> Se a integração QueueMetrics está ativado nas configurações do sistema, em seguida, essa configuração permite a inserção de dados este valor no campo dados4 do queue_log para registros de atividade do agente. O padrão é vazio para deficientes físicos.

<BR>
<A NAME="campaigns-extension_appended_cidname">
<BR>
<B>Extensão Anexar CID -</B> Se activado, as chamadas feitas a partir deste campanha terá um espaço e da extensão de telefone do agente acrescentado ao final do nome CallerID para a chamada antes de ser enviado para o agente. Padrão é n para deficientes físicos.

<BR>
<A NAME="campaigns-pllb_grouping">
<BR>
<B>Agrupamento PLLB -</B> Telefone Entrar Agrupamento de balanceamento de carga, só é permitido se houver vários agentes e servidores de aliases de telefone estão presentes no sistema. Se definido como ONE_SERVER_ONLY vai obrigar todos os agentes para essa campanha para entrar no mesmo servidor. Se definido como CASCATA ele vai agrupar logado agentes no mesmo servidor até PLLB Limitar número Agrupamento de agentes são atingidos, então o próximo agente entrará para o próximo servidor com o menor número de agentes. Se definido como Desativado o padrão de comportamento Telefone Aliases de cada agente encontrar o servidor com o menor número de não-remotos agentes conectados em que será utilizado. O padrão é DISABLED.

<BR>
<A NAME="campaigns-pllb_grouping_limit">
<BR>
<B>PLLB Limite Agrupamento -</B> Telefone Entrar Carga Limite Agrupamento de balanceamento. Se Agrupamento PLLB está definido para CASCATA essa configuração irá determinar o número de agentes aceitável em cada servidor para esta campanha. O padrão é 50.

<BR>
<A NAME="campaigns-crm_popup_login">
<BR>
<B>CRM Popup Login -</B> Si se establece en Y, la Dirección de Popup CRM se utiliza para abrir una nueva ventana de inicio de sesión agente a esta campaña. Por defecto es n.

<BR>
<A NAME="campaigns-crm_login_address">
<BR>
<B>CRM Dirección Popup -</B> La dirección en Internet de una página de acceso de CRM, puede tener las variables de población como la dirección de formulario web, con el VAR en el frente y con la opción - A - user_custom_one - B - para definir variables.

<BR>
<A NAME="campaigns-start_call_url">
<BR>
<B>Iniciar llamada URL -</B> Esta web dirección URL no es visto por el agente, sino que es llamado cada vez que una llamada se envía a un agente si se poblaron. Utiliza las mismas variables que los campos de formulario web y scripts. Esta URL no puede ser una ruta relativa. La URL de inicio no funciona para las llamadas de marcación manual. Por defecto está en blanco.

<BR>
<A NAME="campaigns-dispo_call_url">
<BR>
<B>Dispo Call URL -</B> Esta web dirección URL no es visto por el agente, sino que es llamado cada vez que una llamada es dispositioned por un agente, si se poblaron. Utiliza las mismas variables que los campos de formulario web y scripts. dispo y talk_time son las variables que puede utilizar para recuperar el agente definido por la disposición de la llamada y el tiempo de conversación real en segundos de la llamada. Esta URL no puede ser una ruta relativa. Por defecto está en blanco.

<BR>
<A NAME="campaigns-na_call_url">
<BR>
<B>No URL chamada Agent -</B> Este endereço de URL da web não é visto pelo agente, mas se ela é preenchida é chamada cada vez que uma chamada que não é tratado por um agente é desligou ou transferidos. Usa as mesmas variáveis ​​que os campos de formulários da Web e scripts. dispo pode ser usado para recuperar o definido pelo sistema de disposição para a chamada. Esta URL não pode ser um caminho relativo. O padrão é em branco.

<BR>
<A NAME="campaigns-agent_allow_group_alias">
<BR>
<B>Alias de Grupo Permitidas - </B> Se você quer permitir seus agentes a usarem alias de grupo então você deve configurar como Y. Alias de grupo são explicados melhor na seção Admin, eles permitem que os agentes usem CallerIDs diferentes para chamadas manuais que realizam. O padrão é N.

<BR>
<A NAME="campaigns-default_group_alias">
<BR>
<B>Alias de Grupo Padrão - </B> Se você permitiu Alias de Grupo então este é o alias de grupo que será selecionado como padrão quando o agente escolhe usar um alias de grupo em uma chamada manual. O padrão é NONE ou vazio.

<BR>
<A NAME="campaigns-view_calls_in_queue">
<BR>
<B>Agente Pide Ver en cola -</B> Si se pone a nada, pero NINGUNO, los agentes podrán consultar los detalles de las llamadas que están esperando en la cola en la pantalla de su agente. Si se establece en un valor de número, las llamadas que aparecerá será limitada al número seleccionado. El valor predeterminado es NINGUNO.

<BR>
<A NAME="campaigns-view_calls_in_queue_launch">
<BR>
<B>Ver las llamadas en cola de lanzamiento -</B> Este ajuste, si en AUTO tendrá las convocatorias en el marco de la cola aparecen en inicio de sesión por el agente en la pantalla del agente. El valor predeterminado es MANUAL.

<BR>
<A NAME="campaigns-grab_calls_in_queue">
<BR>
<B>Agente Pide Agarre en cola -</B> Esta opción si se define Y permitirá que el agente para seleccionar la llamada que desea tomar de las llamadas en la pantalla de la cola haciendo clic en él durante la pausa. Los agentes sólo podrán coger las llamadas entrantes o las llamadas transferidas, las llamadas no de salida. Por defecto es n.

<BR>
<A NAME="campaigns-call_requeue_button">
<BR>
<B>Agente de Call Re-Cola Button -</B> Esta opción si se define Y agregará un botón Re-cliente de la cola a la pantalla del agente, permite a las agencias para enviar la llamada en una cola de AGENTDIRECT que está reservado para el único agente. Por defecto es n.

<BR>
<A NAME="campaigns-pause_after_each_call">
<BR>
<B>Agente de pausa después de cada llamada -</B> Esta opción si se define como Y hará una pausa en el agente después de cada llamada automáticamente. Por defecto es n.

<BR>
<A NAME="campaigns-pause_after_next_call">
<BR>
<B>Pausa agente After Next chamada de vínculo -</B> Esta opção se estiver ativado irá exibir um link na tela do agente que vai deixar o agente entrar em pausa automaticamente depois que pendurar sua próxima chamada. O padrão é desabilitado.

<BR>
<A NAME="campaigns-blind_monitor_warning">
<BR>
<B>Aviso Monitor de Cegos -</B> Esta opção, se habilitado deixará o agente sabe de várias formas opcionais, se eles estão sendo monitorados por alguém cego. DESATIVADO significa que este recurso não está ativo, o alerta só aparecerá um alerta na tela do agente, AVISO irá publicar uma nota que permanece na tela do agente, enquanto theyâ re sendo monitorado, AUDIO vai jogar o nome definido abaixo quando um agente está começando a ser monitorados e as outras opções são combibnations das opções acima. O padrão é DISABLED.

<BR>
<A NAME="campaigns-blind_monitor_message">
<BR>
<B>Aviso Monitor de Cegos -</B> Esta é a mensagem que irá mostrar na tela do agente, enquanto eles estão sendo monitorados se a opção AVISO é selecionado. O padrão é -Someone is blind monitoring your session-.

<BR>
<A NAME="campaigns-blind_monitor_filename">
<BR>
<B>Nome do Monitor de Cegos -</B> Este é o arquivo de áudio que será reproduzido na sessão de agentes no início do monitoramento alguém cegá-los. Este aviso vai ser jogado para todos na sessão, incluindo o cliente se for o caso está presente. O padrão é vazio.

<BR>
<A NAME="campaigns-max_inbound_calls">
<BR>
<B>Chamadas Max entrada -</B> Se essa configuração for definido para um número maior que 0, então será o número máximo de chamadas de entrada que um agente pode lidar em todos os grupos de entrada em um dia. Se o agente atinge o número máximo de chamadas de entrada, então eles não serão capazes de selecionar grupos de entrada para atender chamadas de até o dia seguinte. Esta configuração pode ser substituída pela configuração do usuário com o mesmo nome. O padrão é 0 para deficientes.






<BR><BR><BR><BR>

<B><FONT SIZE=3>LISTAS DE TABELA</FONT></B><BR><BR>
<A NAME="lists-list_id">
<BR>
<B>ID da Lista -</B> Este é o nome numérico da lista, não é editável após a confirmação do cadastro, deve conter somente numeros e deve ter entre 2 e 8 caracteres de comprimento. Must be a number greater than 100.

<BR>
<A NAME="lists-list_name">
<BR>
<B>Nome da Lista -</B> Esta é a descrição da lista, deve ter entre 2 e 20 caracteres de comprimento.

<BR>
<A NAME="lists-list_description">
<BR>
<B>Descrição da List -</B> É um campo texto para descrião da lista, é opicional.

<BR>
<A NAME="lists-list_changedate">
<BR>
<B>Data de Alteração da Lista -</B> Esta é a última vez que as configurações da lista foram modificadas.

<BR>
<A NAME="lists-list_lastcalldate">
<BR>
<B>Data da Última Chamada da Lista -</B> É a data em que o último registro da lista foi discado.

<BR>
<A NAME="lists-campaign_id">
<BR>
<B>Campanha -</B> Este é a campanha que esta lista pertence. Uma lista só pode ser discada por uma campanha de cada vez.

<BR>
<A NAME="lists-active">
<BR>
<B>Ativo -</B> Indica se a lista é para ser discada ou não.

<BR>
<A NAME="lists-reset_list">
<BR>
<B>Zerar Status de chamada dos registros desta lista -</B> Zera todos os registros desta lista colocando N para \"not called since last reset"\ e significa que qualquer registro pode ser discado agora se for do status conforme configurado na tela da campanha.

<BR>
<A NAME="lists-reset_time">
<BR>
<B>Perdí Times -</B> Este campo le permite poner veces, separados por un guión, que esta lista se restablece automáticamente por el sistema. Los tiempos deben estar en formato de 24 horas sin puntuacion, por ejemplo 0800-1700 se restablecer la lista de las 8 AM y las 5 PM todos los días. Por defecto está vacío.

<BR>
<A NAME="lists-expiration_date">
<BR>
<B>Data de Vencimento -</B> Esta opção permite que você defina a data após a qual lidera esta lista não será permitida a ser auto-discado ou manual-list-discado pelo sistema. O padrão é 2099/12/31.

<BR>
<A NAME="lists-audit_comments">
<BR>
<B>Auditoria Comentários -</B> Esta opção permite que comentários sejam transferidos para uma tabela de auditoria. Não é mais editável, mas visível, juntamente com a data e hora-criador de cada comentário. O padrão é N. Esta é uma parte do Controle de Qualidade Add-On.

<BR>
<A NAME="lists-agent_script_override">
<BR>
<B>Agente de secuencias de comandos Reemplazar -</B> Si se establece este campo, esta será la secuencia de comandos que el agente ve en su pantalla en lugar de la secuencia de comandos de campaña cuando la ventaja es de esta lista. Por defecto no se establece.

<BR>
<A NAME="lists-campaign_cid_override">
<BR>
<B>Campaña CID Override -</B> Si se establece este campo, que reemplazarán la campaña CallerID que se establece para las llamadas que se colocan a la cabeza en esta lista. Por defecto no se establece.

<BR>
<A NAME="lists-am_message_exten_override">
<BR>
<B>Contestador automático de mensajes Override -</B> Si se establece este campo, esto anulará el mensaje de contestador automático situado en la campaña para los clientes en esta lista. Por defecto no se establece. 
<BR>
<A NAME="lists-drop_inbound_group_override">
<BR>
<B>Drop de entrada Grupo de Override -</B> Si se establece este campo, en este grupo se utilizará para las llamadas salientes dentro de esta lista que la caída de la campaña de salida en lugar de la caída en grupo situado en la pantalla de detalle de la campaña. Por defecto no se establece.

<BR>
<A NAME="lists-web_form_address">
<BR>
<B>Formulário Web Override -</B> Este é o endereço personalizado que clicar no botão de formulário da Web na tela do agente irá levá-lo para as chamadas que chegam nesta lista. Se você quiser usar os campos personalizados em um endereço de formulário web, você precisa adicionar & CF_uses_custom_fields = Y como parte de sua URL.

<BR>
<A NAME="lists-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf Número Override -</B> Estos cinco campos permiten reemplazar la transferencia de presets Conferencia número cuando la iniciativa es de esta lista. Por defecto está en blanco.

<BR>
<A NAME="lists-inventory_report">
<BR>
<B>Relatório de Inventário -</B> Se o relatório de inventário está habilitado em seu sistema, esta opção irá determinar se esta lista é incluída no relatório ou não. O padrão é Y para sim.

<BR>
<A NAME="lists-time_zone_setting">
<BR>
<B>Definição de Fuso Horário -</B> Esta opção permite que você defina o método de manter a pesquisa de fuso horário atual para as ligações dentro desta lista. Este processo só é feito à noite para que todas as alterações feitas não serão imediatos. COUNTRY_AND_AREA_CODE é o padrão, e vai usar o código do país eo código de área do número de telefone para determinar o fuso horário do chumbo. Postal_code irá utilizar o código postal se disponível para determinar o fuso horário do chumbo. NANPA_PREFIX funciona apenas nos EUA e vai usar o código de área eo prefixo do número de telefone para determinar o fuso horário do chumbo, mas isso não é ativado por padrão no sistema, por isso, tenha certeza de ter os dados prefixo NANPA carregados seu sistema antes de selecionar esta opção. OWNER_TIME_ZONE_CODE usará o abbraviation zona padrão de tempo carregado no campo do proprietário do chumbo para determinar o fuso horário, nos EUA são exemplos AST, EST, CST, MST, PDT, AKST, HST. Este recurso deve ser ativado pelo administrador do sistema para entrar em vigor.


<BR>
<A NAME="internal_list-dnc">
<BR>
<B>Lista DNC Interno -</B> This Do Not Call list contains every lead that has been set to a status of DNC in the system. Through the LISTS - INCLUIR NÚMERO AO BLOQUEIO page you are able to manually add numbers to this list so that they will not be called by campaigns that use the internal DNC list. Existe também a opção de incluir registros em listas de bloqueio específicas de campanhas. Si usted tiene la opción de DNC activas de codigoDeArea a continuación, usted también puede usar el código de entradas de la zona este 201XXXXXXX como comodín para bloquear todas las llamadas a los 201 codigoDeArea cuando está activado.



<BR><BR><BR><BR>

<B><FONT SIZE=3>TABELA INBOUND_GROUPS</FONT></B><BR><BR>
<A NAME="inbound_groups-group_id">
<BR>
<B>ID do Grupo -</B> Nome curto do grupo de entrada, não é editável após confirmação, não deve conter qualquer espaços e deve ter entre 2 e 20 caracteres de comprimento.

<BR>
<A NAME="inbound_groups-group_name">
<BR>
<B>Nome do Grupo -</B> Descrição do grupo, deve ter entre 2 e 30 caracteres de comprimento. Não pode incluir os caracteres - + ou espaço .

<BR>
<A NAME="inbound_groups-group_color">
<BR>
<B>Cor do Grupo -</B> Esta é a cor que aparece no aplicativo cliente agente quando chega uma chamada sobre este grupo. Ele deve estar entre 2 e 7 caracteres. Se esta é uma definição de cor hexadecimal que você deve se lembrar de colocar um # no início da string ou tela o agente não irá funcionar corretamente.

<BR>
<A NAME="inbound_groups-active">
<BR>
<B>Ativo -</B> Isso determina se este grupo de entrada está disponível para receber chamadas. Se estiver definido para inativo então o After Hours Ação será usado em todas as chamadas que entram em que.

<BR>
<A NAME="inbound_groups-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse grupo de entrada, o que permite a visualização de administrador isso no grupo restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário administrador para visualizar esta em grupo.

<BR>
<A NAME="inbound_groups-group_calldate">
<BR>
<B>In-Grupo Calldate -</B> Esta é a última data e hora em que uma chamada foi direcionada para esse grupo de entrada.

<BR>
<A NAME="inbound_groups-web_form_address">
<BR>
<B>Formulário Web -</B> Este é o endereço personalizado que clicar no botão de formulário da Web na tela do agente irá levá-lo para as chamadas que chegam a este grupo. Se você quiser usar os campos personalizados em um endereço de formulário web, você precisa adicionar & CF_uses_custom_fields = Y como parte de sua URL.

<BR>
<A NAME="inbound_groups-next_agent_call">
<BR>
<B>Próximo Agente -</B> Isto determina qual agente recebe a próxima chamada disponível:
 <BR> &nbsp; - random: ordens do valor de atualização aleatória na tabela live_agents
 <BR> &nbsp; - oldest_call_start: ordena pela última vez que um agente recebeu uma chamada. Resulta nos agentes recebendo uma média igual de chamadas.
 <BR> &nbsp; - oldest_call_finish: ordena pela última vez que um agente finalizou uma chamada. Também conhecido como agente que está mais tempo esperando cliente, recebe a primeira chamada.
 <BR> &nbsp; - oldest_inbound_call_start: encomendas por a última vez que um agente foi enviado uma chamada de entrada. Resultados em agentes que recebem aproximadamente o mesmo número de chamadas global.
 <BR> &nbsp; - oldest_inbound_call_finish: encomendas por a última vez que um agente terminar uma chamada de entrada. AKA agente espera mais longa recebe primeira chamada.
 <BR> &nbsp; - overall_user_level: ordens do user_level do agente, tal como definido na tabela de usuários uma user_level maior receberá mais chamadas.
 <BR> &nbsp; - inbound_group_rank: ordena pelo ranking dado para um agente para um grupo de entrada. Do mais alto para o mais baixo.
 <BR> &nbsp; - campaign_rank: ordena pelo ranking dado para o agente na campanha. Mais alto para Mais baixo.
 <BR> &nbsp; - ingroup_grade_random: dá uma maior probabilidade de obter uma chamada para os agentes de maior classificados por em grupo.
 <BR> &nbsp; - campaign_grade_random: dá uma maior probabilidade de obter uma chamada para os agentes de maior classificados por campanha.
 <BR> &nbsp; - fewest_calls: ordena pelo número de chamadas recebidas por um agente para o grupo de entrada específico. Menor qtd de chamadas primeiro.
 <BR> &nbsp; - fewest_calls_campaign: orders by the number of calls received by an agent for the campaign. Least calls first.
 <BR> &nbsp; - longest_wait_time: pedidos por la cantidad de tiempo que el agente ha estado activamente la espera de una llamada de.
 <BR> &nbsp; - ring_all: rings all available agents until one picks up the phone.
 <BR> NOTAS: Para ring_all, os agentes que estão usando telefones que têm Em habilitado agente Gancho terá seu anel de telefones e o primeiro a responder vai receber a chamada e as informações na tela do agente. Desde ring_all ignora tempo agente espera e classificação e vai chamar todos os agentes que está disponível para a fila, nós não recomendamos usar este método para as filas grandes. Ao utilizar ring_all, agentes conectados com telefones que têm o Agente Gancho Em desativado terá que usar as chamadas em painel de fila e clique no link CHAMADA TOMAR PARA receber chamadas em fila. A quantidade de tempo que o telefone vai tocar para os agentes ring_all está definido para a configuração de tempo no gancho do anel ou o menor tempo de anel dos telefones que serão chamados. Nós não recomendamos o uso ring_all em filas de chamadas em grande volume, ou filas com muitos agentes. O método ring_all se destina a ser usado com apenas alguns agentes e sobre o volume de chamadas em baixo-grupos.
<BR>

<BR>
<A NAME="inbound_groups-on_hook_ring_time">
<BR>
<B>No gancho-Time Anel -</B> Esta opção só é usada para os agentes que são registrados com telefones que têm a função de agente no gancho habilitado. Este é o número de segundos que cada tentativa de chamada para o agente tocará para até que o sistema irá aguardar um segundo e começar a tocar os telefones agente disponível novamente. Este campo pode ser substituído se os telefones de agentes são definidos para um menor tempo de anel que pode ser necessário para evitar chamadas sejam enviados um correio de voz telefones. O padrão é 15.

<BR>
<A NAME="inbound_groups-on_hook_cid">
<BR>
<B>Em Hook-CID -</B> Esta opção só é usada para os agentes que são registrados com telefones que têm a função de agente no gancho habilitado. Este é o identificador de chamadas que aparecerá em seus telefones agente quando as chamadas são de toque. GENERIC é um tipo genérico RINGAGENT00000000001 da notificação. Ingroup vai mostrar apenas o grupo na chamada veio. CUSTOMER_PHONE irá mostrar apenas o número de telefone do cliente. CUSTOMER_PHONE_RINGAGENTE irá mostrar RINGAGENT_3125551212 com o RINGAGENTE como parte do CID com o número de telefone do cliente. CUSTOMER_PHONE_INGROUP irá mostrar os primeiros 10 caracteres do grupo em seguida o número do telefone do cliente. Default is GENERIC.

<BR>
<A NAME="inbound_groups-queue_priority">
<BR>
<B>Queue Priority -</B> This setting is used to define the order in which the calls from this inbound group should be answered in relation to calls from other inbound groups.

<A NAME="inbound_groups-fronter_display">
<BR>
<B>Mostrar Fronter -</B> Este campo determina se o agente de entrada teria o nome fronter - se houver - exibido no campo de status quando a chamada é para o agente.

<BR>
<A NAME="inbound_groups-ingroup_script">
<BR>
<B>Script da Campanha -</B> Este menu permite que seja escolhido um script que irá aparecer na tela do agente para essa campanha. Selecione NONE para não mostrar scripts para esta campanha.

<BR>
<A NAME="inbound_groups-ignore_list_script_override">
<BR>
<B>Ignorar Override Script Lista-</B> Esta opção permite que você ignore a lista de ID opção Override Script para chamadas que entram esta no Grupo. Definindo-a para Y irá ignorar qualquer lista as configurações de script de identificação. O padrão é N.

<BR>
<A NAME="inbound_groups-get_call_launch">
<BR>
<B>Abrir com Chamada -</B> Este menu permite que seja escolhido se você deseja abrir automaticamente a pagina web em uma janela separada, abrir a aba SCRIPT ou nao fazer nada quando uma chamada é enviada ao agente para esta campanha. Se os campos personalizados da lista estão habilitados em seu sistema, formulário será aberto no separador FORMULÁRIO na conexão de uma chamada para um agente.

<BR>
<A NAME="inbound_groups-xferconf_a_dtmf">
<BR>
<B>Xfer-Conf DTMF -</B> Estes quatro campos permitem que você tenha dois conjuntos de Transferência e Conferência presets DTMF. Quando a chamada ou campanha é carregado, a tela do agente irá mostrar dois botões no quadro de transferência de conferência e preencher automaticamente o número para discar e os campos de envio-DTMF quando pressionado. Se você quiser permitir transferências consultivos, um fronter a um mais perto, ter o agente usar a caixa de seleção CONSULTIVO, que não funciona para tela agente de terceiros chamadas consultivos. Para aqueles que só tem o agente, clique no botão de discagem com Cliente. Em seguida, o agente pode apenas deixá-3WAY-CALL e passar para a próxima chamada. Se você quiser permitir transferências de Cegos de clientes para um script AGI para logging ou um IVR, em seguida, coloque AXFER no campo número para discar. Você também pode especificar uma extensão personalizada após o AXFER, por exemplo, se você quiser fazer uma chamada para um IVR especial que você definiu para o ramal 83900 você colocaria AXFER83900 no campo número para discar.

<BR>
<A NAME="inbound_groups-timer_action">
<BR>
<B>Temporizador de Acción de -</B> Esta característica le permite activar acciones después de una cierta cantidad de tiempo. D1 y D2 opciones de marcación lanzará un llamamiento a la transferencia de presets Conferencia Número y enviarlos a la sesión del agente, éste se utiliza generalmente para aplicaciones sencillas de validación IVR AGI o simplemente para jugar un mensaje pregrabado. WebForm se abrirá la dirección de formulario web. MESSAGE_ONLY simplemente mostrará el mensaje de que está en el campo de abajo. Ninguna de ellas podrá deshabilitar esta característica y es el predeterminado. Esta configuración anula la configuración de la campaña. HANGUP irá desligar a chamada quando o cronômetro é disparado, CALLMENU enviará a chamada para o menu de chamada especificado no campo Destino Temporizador Ação, EXTENSÃO enviará a chamada para o ramal que está especificado no campo Destino Temporizador Ação, IN_GROUP enviará a chamada para o No Grupo-especificado no campo Destino Temporizador Ação.

<BR>
<A NAME="inbound_groups-timer_action_message">
<BR>
<B>Temporizador de mensaje de acción -</B> Este es el mensaje que aparece en la pantalla del agente en el momento de la acción del temporizador se dispara.

<BR>
<A NAME="inbound_groups-timer_action_seconds">
<BR>
<B>Temporizador Segundos Acción -</B> Esta es la cantidad de tiempo después de la llamada se conecta a los clientes que se desencadena la acción del temporizador. El valor predeterminado es -1, que también está inactivo.

<BR>
<A NAME="inbound_groups-timer_action_destination">
<BR>
<B>Destino Ação temporizador -</B> Este campo é onde você especificar a chamada do menu de extensão, ou em grupo que você deseja a chamada enviada se o tempo de ação é definido como EXTENSÃO CALLMENU, ou IN_GROUP. O padrão é vazio.

<BR>
<A NAME="inbound_groups-drop_call_seconds">
<BR>
<B>Tempo Limite de Drop- </B> O número em segundos que uma chamada deve ficar na fila de espera até ser considerada um DROP.

<BR>
<A NAME="inbound_groups-drop_action">
<BR>
<B>Ação de Drop - </B> Este menu permite que você escolha o que acontece com uma chamada quando esta ficou esperando mais que o tempo configurado no campo Tempo Limite de Drop. HANGUP irá simplesmente desligar a chamada, MESSAGE irá enviar a chamada para Extensão de Drop que você definiu abaixo, VOICEMAIL irá enviar a chamada para a caixa de voicemail que você definiu abaixo e IN_GROUP irá enviar a chamada para o grupo de entrada que você define abaixo. VMAIL_NO_INST enviará a chamada para a caixa de correio de voz que você tenha definido a seguir e não vai jogar instruções após a mensagem de correio de voz.

<BR>
<A NAME="inbound_groups-drop_exten">
<BR>
<B>Extensão de Drop - </B> Se a Ação de Drop estiver configurada como MESSAGE, este é o número do plano de discagem a ser discado para transferir a chamada após o tempo limite de drop. Para AGENTDIRECT em grupos de, se o usuário não estiver disponível, você pode colocar AGENTEXT neste campo eo sistema irá procurar o usuário cinco campo personalizado e enviar a chamada para esse número dialplan.

<BR>
<A NAME="inbound_groups-voicemail_ext">
<BR>
<B>Correio de Voz -</B> If Drop Action is set to VOICEMAIL, the call DROP would instead be directed to this voicemail box to hear and leave a message. Em um AGENTDIRECT em grupo, definindo-o para AGENTVMAIL irá selecionar o ID do usuário para usar correio de voz.

<BR>
<A NAME="inbound_groups-drop_inbound_group">
<BR>
<B>Grupo de Transferência de Drop - </B> Caso a Ação de Drop esteja configurada como IN_GROUP, a chamada será enviada para esse Grupo de Entrada após o tempo limite.

<BR>
<A NAME="inbound_groups-drop_callmenu">
<BR>
<B>Largar menu de chamada -</B> Se a Ação Gota é definida como CALLMENU, a chamada será enviada para este menu chamada se atingir Segundos chamada gota.

<BR>
<A NAME="inbound_groups-action_xfer_cid">
<BR>
<B>Transferência de Ação CID -</B> Usado para Gota, Aberto até tarde e ações não-agente-no-fila. Este é o número de identificação de chamadas que usa a chamada antes de ser transferido para menus extensões, mensagens de correio de voz ou chamadas. Você pode usar CLIENTE neste campo para usar o número de telefone do cliente, ou uma campanha para usar o número de ID de chamador primeira campanha permitido. O padrão é CLIENTE. Se esta é uma chamada que irá para um menu Chamadas e depois voltar para uma in-grupo, sugerimos que você use CUSTOMERCLOSER neste campo, e você também precisa definir o método Handle Em Grupo no menu Chamada de CLOSER.

<BR>
<A NAME="inbound_groups-call_time_id">
<BR>
<B>Horário de Chamada </B> É a configuração do horário de chamada para ser usado neste grupo de entrada. Lembre-se que o horário é baseado no horário do servidor. Padrão é 24Horas.

<BR>
<A NAME="inbound_groups-after_hours_action">
<BR>
<B>Ação Fora do Expediente - </B> A ação a ser realizada se o horário for fora do definido no horário de chamada para este grupo de entrada. HANGUP irá imediatamente desligar a chamada, MESSAGE irá tocar o arquivo no campo Arquivo de Mensagem Fora de Expediente, EXTENSION irá enviar a chamada para o extensão do campo Extensão Fora do Expediente no plano de discagem e VOICEMAIL irá enviar a chamada para a caixa de voicemail listada no campo de Correio de Voz Fora do Expediente, IN_GROUP irá enviar a chamada para o grupo de entrada selecionado na lista Grupo de Entrada Fora do Expediente. Padrão é MESSAGE. VMAIL_NO_INST enviará a chamada para a caixa de correio de voz que você tenha definido a seguir e não vai jogar instruções após a mensagem de correio de voz.

<BR>
<A NAME="inbound_groups-after_hours_message_filename">
<BR>
<B>Arquivo Fora do Expediente -</B> O arquivo de audio localizado no servidor, que deve ser tocado quando a ação estiver configurada como MESSAGE. Padrão é vm-goodbye

<BR>
<A NAME="inbound_groups-after_hours_exten">
<BR>
<B>Extensão Fora do Expediente -</B> Extensão a ser enviada a chamada que entrar se o campo Ação estiver como EXTENSION. O padrão é 8300. Para AGENTDIRECT em-grupos, você pode colocar AGENTEXT neste campo eo sistema irá procurar o usuário cinco campo personalizado e enviar a chamada para esse número dialplan.

<BR>
<A NAME="inbound_groups-after_hours_voicemail">
<BR>
<B>Correio de Voz Fora do Expediente-</B> A caixa de VoiceMail a ser enviada a chamada, quando o campo Ação for VOICEMAIL. Em um AGENTDIRECT em grupo, definindo-o para AGENTVMAIL irá selecionar o ID do usuário para usar correio de voz.

<BR>
<A NAME="inbound_groups-afterhours_xfer_group">
<BR>
<B>Grupo de Transferência Fora do Expediente- </B> Se a Ação de Fora do Expediente estiver configurada para IN_GROUP, a chamada irá ser enviada para esse grupo de entrada se ela entrar neste grupo de entrada fora do horário configurado no Horário de Chamadas para este grupo de entrada.

<BR>
<A NAME="inbound_groups-after_hours_callmenu">
<BR>
<B>After Hours chamada do menu -</B> Se After Hours Ação é definida como CALLMENU, a chamada será enviada para este menu de chamada se ele entra no exterior no grupo do regime de tempo de chamada definido para o grupo em-.

<BR>
<A NAME="inbound_groups-no_agent_no_queue">
<BR>
<B>N de colas n Agentes -</B> Si este campo está establecido en Y o NO_PAUSED entonces no se pide se pondrá en la cola de este grupo en si no hay agentes conectado y las llamadas se vaya a la agente n Ninguna acción de la cola. La opción NO_PAUSED no se envían también las personas que llaman a la cola si sólo hay una pausa en los agentes en el grupo. Por defecto es n. Em um AGENTDIRECT em grupo, definindo-o para AGENTVMAIL irá selecionar o ID do usuário para usar correio de voz. Você também pode colocar AGENTEXT neste campo, se ele é definido como extensão eo sistema irá procurar o usuário cinco campo personalizado e enviar a chamada para esse número dialplan. Se estiver definido para N, as chamadas serão fila, mesmo se não há agentes conectados e definido para receber chamadas a partir deste em grupo.

<BR>
<A NAME="inbound_groups-no_agent_action">
<BR>
<B>No No Acción cola de agente -</B> Si está activada ninguna cola Ningún Agente, entonces este campo se define en la llamada pasará si no hay agentes en la A-Group. Por defecto es el mensaje, este reproduce los archivos de sonido en el campo el valor de acción y luego cuelga el.

<BR>
<A NAME="inbound_groups-no_agent_action_value">
<BR>
<B>No hay ninguna cola Acción Agente Valor -</B> Este es el valor de la acción anterior. El valor predeterminado es nbdy-avail-to-take-call|vm-goodbye.

<BR>
<A NAME="inbound_groups-max_calls_method">
<BR>
<B>Max chama o método -</B> Esta opção pode permitir que o máximo recurso de chamadas simultâneas para esta no grupo. Se definido como TOTAL, então o número total de chamadas que estão sendo manipulados pelos agentes e em fila neste em grupo não será permitido exceder a Max Chama Contar o número de linhas como definido abaixo. Se definido como IN_QUEUE, então se o número de chamadas em fila de espera para os agentes não será permitido exceder a Max Chama Conte, não importa quantas chamadas são com agentes para isso no grupo. O padrão é DISABLED.

<BR>
<A NAME="inbound_groups-max_calls_count">
<BR>
<B>Max chama Conte -</B> Esta opção deve ser definida maior que 0 se você quiser usar o recurso do método Max Chama. O padrão é 0.

<BR>
<A NAME="inbound_groups-max_calls_action">
<BR>
<B>Max chama Ação -</B> Esta é a ação a ser tomada se o Max chama o método é habilitado eo número de chamadas exceder o que está definido acima, na Max Chama Conte configuração. As chamadas superiores a esse montante será enviado para tanto o GOTA ação, a ação ou a ação AfterHours NO_AGENT_NO_QUEUE e será registrado como um estado MAXCAL com uma razão hangup MAXCALLS. O padrão é NO_AGENT_NO_QUEUE.

<BR>
<A NAME="inbound_groups-welcome_message_filename">
<BR>
<B>Arquivo de Mensagem Boasvindas -</B> Arquivo de audio localizado no servidor a ser tocado quando uma chamada entra. Se estiver configurado como ---NONE--- então nenhuma mensagem irá ser tocada. O padrão é ---NONE---. Este campo como con los otros campos de audio en In-grupos, con la excepción del nombre de archivo de alerta de agente, puede tener múltiples archivos de audio reproducido si se pone un tubo de lista separada de los archivos de audio en el campo.

<BR>
<A NAME="inbound_groups-play_welcome_message">
<BR>
<B>Tocar Mensagem de Boas Vindas - </B> Esta configuração seleciona quando tocar mensagens de boas vindas, ALWAYS irá tocar toda vez, NEVER nunca irá tocar, IF_WAIT_ONLY irá somente tocar a mensagem se a chamada não for diretamente para o agente, e YES_UNLESS_NODELAY irá sempre tocar a mensagem a não ser que a configuração de Sem Atraso estiver habilitada. O padrão é ALWAYS.

<BR>
<A NAME="inbound_groups-moh_context">
<BR>
<B>Contexto da Música de Espera-</B> O contexto de Música de Espera a ser usado quando o cliente é colocado em espera. O padrão é default.

<BR>
<A NAME="inbound_groups-onhold_prompt_filename">
<BR>
<B>Arquivo de Mensagem de Espera -</B> Arquivo de audio localizado no servidor a ser tocado em um intervalo regular quando o cliente está em espera. O padrão é generic_hold. Este arquivo de audio deve ter até 9 segundos

<BR>
<A NAME="inbound_groups-prompt_interval">
<BR>
<B>Intervalo de Anúncio -</B> O tempo em segundos para esperar até tocar a mensagem de Espera. O padrão é 60. Para desabilitar a Mensagem em Espera, configuro o intervalo como 0.

<BR>
<A NAME="inbound_groups-onhold_prompt_no_block">
<BR>
<B>On Hold Prompt No Bloco -</B> Definir esta opção como Y permitirá chamadas na fila atrás de uma chamada em espera, onde o aviso é jogar para ir a um agente se torna disponível, enquanto a mensagem está jogando. Enquanto o On Hold mensagem Filename Prompt está jogando a um cliente que não pode ser enviada a um agente. O padrão é N.

<BR>
<A NAME="inbound_groups-onhold_prompt_seconds">
<BR>
<B>On Hold Segundos Prompt -</B> Este campo precisa ser definido como o número de segundos que o Filename On Hold Prompt joga. O padrão é 10.

<BR>
<A NAME="inbound_groups-play_place_in_line">
<BR>
<B>Tocar Posição na Fila - </B> Isto define se o cliente irá ouvir seu lugar na fila de atendimento quando entram na fila e também periodicamente. O padrão é N.

<BR>
<A NAME="inbound_groups-play_estimate_hold_time">
<BR>
<B>Tocar Tempo Estimado - </B> Isto define se o cliente irá ouvir o tempo estimado de espera antes de ser transferido para um agente. O padrão é N. Se o cliente está em espera e ouve o tempo estimado de atendimento, o tempo mínimo que será tocado é de 15 segundos.

<BR>
<A NAME="inbound_groups-calculate_estimated_hold_seconds">
<BR>
<B>Calcule Segundos Retenção estimado -</B> Isso define o número de segundos na fila que o cliente irá esperar antes de o tempo de espera estimado será calculado e, opcionalmente, jogado. Mínima é de 3 segundos, mesmo se definida inferior a 3. O padrão é 0.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_filename">
<BR>
<B>Estima Segure Filename tempo mínimo -</B> Se o tempo de retenção estimado é activo e é calculado para ser igual ou inferior ao mínimo de 15 segundos, então este ficheiro irá ser jogado em vez de o anúncio padrão. O padrão é vazio para inativos.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_no_block">
<BR>
<B>Mínimo Tempo estimado de espera Não Avisar Bloco -</B> Se Tempo de espera estimado é ativo eo estimado de espera Tempo campo Nome do arquivo mínimo acima é preenchido, então esta opção para permitir que as chamadas em fila atrás de uma chamada em que o prompt está jogando para ir a um agente se torna disponível quando a mensagem é jogar . Enquanto o tempo está a desempenhar a um cliente que não podem ser enviados para um agente. O padrão é N.

<BR>
<A NAME="inbound_groups-eht_minimum_prompt_seconds">
<BR>
<B>Estimado Hold Time Segundos mínimos Prompt -</B> Este campo precisa ser definido como o número de segundos que o estimado de espera Filename tempo mínimo prompt de joga. O padrão é 10.

<BR>
<A NAME="inbound_groups-wait_time_option">
<BR>
<B>Aguarde Opção Tempo -</B> Isto permite-lhe oferecer aos clientes opções para deixar a fila se o tempo de espera é sobre a quantidade de segundos especificados abaixo. O padrão é NONE. Se uma das opções de PRESS_ é selecionado, ele vai jogar o Filename Imprensa definido abaixo e dar ao cliente a opção de pressionar 1 em seu telefone para sair da fila e executar a opção selecionada. A opção PRESS_STAY enviará o cliente de volta para a fila sem perder seu lugar na fila.

<BR>
<A NAME="inbound_groups-wait_time_second_option">
<BR>
<B>Aguarde Opção Segundo Tempo -</B> Mesmo que o primeiro campo Opção Wait Time acima, exceto este irá verificar para o cliente pressionando a tecla 2. O padrão é NONE. Se nenhuma opção de tempo de espera é primeiro selecionado, então esta opção não serão oferecidos.

<BR>
<A NAME="inbound_groups-wait_time_third_option">
<BR>
<B>Aguarde Opção terceira vez -</B> Mesmo que o primeiro campo Opção Wait Time acima, exceto este irá verificar para o cliente pressionando a tecla 3. O padrão é NONE. Se nenhuma opção Second Time Wait for selecionado, então esta opção não serão oferecidos.

<BR>
<A NAME="inbound_groups-wait_time_option_seconds">
<BR>
<B>Aguarde alguns segundos opção de tempo -</B> Se a opção Tempo de espera é definida como qualquer coisa, mas NENHUM, este é o número de segundos que o cliente está esperando na fila que irá acionar as opções de tempo de espera. O padrão é 120 segundos.

<BR>
<A NAME="inbound_groups-wait_time_option_exten">
<BR>
<B>Aguarde Opção Extensão Tempo -</B> Se a opção Tempo de espera é definido como PRESS_EXTEN, esta é a extensão dialplan que a chamada será enviado para o cliente, se pressiona a tecla opção quando apresentados com a opção. Para AGENTDIRECT em-grupos, você pode colocar AGENTEXT neste campo eo sistema irá procurar o usuário cinco campo personalizado e enviar a chamada para esse número dialplan.

<BR>
<A NAME="inbound_groups-wait_time_option_callmenu">
<BR>
<B>Espere Callmenu opção de tempo -</B> Se a opção Tempo de espera é definido como PRESS_CALLMENU, este é o menu de chamada que a chamada será enviado para o cliente, se pressiona a tecla opção quando apresentados com a opção.

<BR>
<A NAME="inbound_groups-wait_time_option_voicemail">
<BR>
<B>Espere Correio de Voz opção de tempo -</B> Se a opção Tempo de espera é definido como PRESS_VMAIL, esta é a caixa de correio de voz que a chamada será enviado para o cliente, se pressiona a tecla opção quando apresentados com a opção. Em um AGENTDIRECT em grupo, definindo-o para AGENTVMAIL irá selecionar o ID do usuário para usar correio de voz.

<BR>
<A NAME="inbound_groups-wait_time_option_xfer_group">
<BR>
<B>Espere Transferência Tempo Opção em grupo -</B> Se a opção Tempo de espera é definido como PRESS_INGROUP, este é o grupo de entrada que a chamada será enviado para o cliente, se pressiona a tecla opção quando apresentados com a opção.

<BR>
<A NAME="inbound_groups-wait_time_option_press_filename">
<BR>
<B>Tempo de espera Filename Pressione Opção -</B> Se a opção Tempo de espera é definida como uma das opções PRESS_, este é o prompt de nome de arquivo que é jogado, se o tempo de espera do cliente exceder os Aguarde alguns segundos opção de tempo para dar ao cliente a opção de pressionar 1, 2 ou 3 em seu telefone para executar os selecionados Wait Imprensa opções de tempo. É muito importante que você inclua opções no arquivo de áudio para todas as suas opções selecionadas Tempo de espera, e que o tamanho do arquivo de áudio em segundos está devidamente definido no campo Nome do arquivo Segundos abaixo ou haverá problemas. O padrão é a-ser-chamado-back.

<BR>
<A NAME="inbound_groups-wait_time_option_no_block">
<BR>
<B>Espere Pressione Opção No Time Bloco -</B> Definir esta opção como Y permitirá chamadas na fila atrás de uma chamada onde o Tempo de Espera Pressione Opção Filename prompt é jogar para ir a um agente se torna disponível, enquanto a mensagem está jogando. Enquanto o Tempo de Espera Opção mensagem Filename Imprensa está jogando a um cliente que não pode ser enviada a um agente. O padrão é N.

<BR>
<A NAME="inbound_groups-wait_time_option_prompt_seconds">
<BR>
<B>Aguarde tempo de opção Segundos Nome do ficheiro Imprensa -</B> Este campo precisa ser definido como o número de segundos que o Wait Time Filename Pressione Opção joga. O padrão é 10.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_filename">
<BR>
<B>Aguarde Opção Time After Filename Imprensa -</B> Se a opção Tempo de espera é definida como uma das opções PRESS_, este é o prompt de nome de arquivo que é jogado depois que o cliente tem pressionado 1, 2 ou 3.

<BR>
<A NAME="inbound_groups-wait_time_option_callback_list_id">
<BR>
<B>Tempo de espera Opção ID Lista de chamada de retorno -</B> Se a opção Tempo de espera é definido como PRESS_CID_CALLBACK, este é o ID da chamada Lista é adicionado como uma nova pista, se o cliente pressiona a tecla opção quando apresentados com a opção.

<BR>
<A NAME="inbound_groups-wait_hold_option_priority">
<BR>
<B>Espere Segure Prioridade Opção -</B> Se ambas as opções Retenção estimado vez e aguarde opções de tempo são ativos, esta configuração irá definir se um, outro ou ambos os recursos são ativos. Por exemplo, se a opção de tempo estimado de espera é definida como 360, a opção Tempo de espera é definida para 120 eo cliente tem estado à espera de 120 segundos e ainda há 400 segundos estimados tempo de espera, então eles são ambos ativos ao mesmo tempo e esta definição será verificada para ver quais opções serão oferecidas. O padrão é esperar apenas.

<BR>
<A NAME="inbound_groups-hold_time_option">
<BR>
<B>Estimated Opção de Tempo de Espera - </B> Permite que você especifique uma rota para a chamada caso o tempo de espera esteja superior a quantidade de segundos configurada abaixo. O padrão é NONE. Se uma das opções de PRESS_ é selecionado, ele vai jogar o Filename Imprensa definido abaixo e dar ao cliente a opção de pressionar 1 no seu telemóvel para deixar a fila e executar a opção selecionada.

<BR>
<A NAME="inbound_groups-hold_time_second_option">
<BR>
<B>Segure Opção Segundo Tempo -</B> Mesmo que o primeiro campo Opção Tempo de espera acima, exceto este irá verificar para o cliente pressionando a tecla 2. O padrão é NONE. Se nenhuma opção de tempo de espera primeiro é selecionada a opção não será oferecido.

<BR>
<A NAME="inbound_groups-hold_time_third_option">
<BR>
<B>Segure Opção terceira vez -</B> Mesmo que o primeiro campo Opção Tempo de espera acima, exceto este irá verificar para o cliente pressionando a tecla 3. O padrão é NONE. Se nenhuma opção Second Time espera for selecionado, então esta opção não serão oferecidos.

<BR>
<A NAME="inbound_groups-hold_time_option_seconds">
<BR>
<B>Tempo de Opção de Espera - </B> Se a opção de tempo de espera estiver configurado para um valor diferente de NONE, este número em segundos de tempo estimado de atendimento irá acionar esta opção de espera. O padrão é 360 segundos.

<BR>
<A NAME="inbound_groups-hold_time_option_minimum">
<BR>
<B>Segure mínimo opção de tempo -</B> Se a opção Tempo de espera ativado, este é o número mínimo de segundos que a chamada deve ser de espera antes de ser apresentado com a opção de tempo de espera. A opção de tempo de retenção será imediatamente apresentado neste momento, se o tempo de espera estimado é maior do que o valor de tempo de espera Opção Segundos. O padrão é 0 segundos.

<BR>
<A NAME="inbound_groups-hold_time_option_exten">
<BR>
<B>Extensão de Opção de Tempo - </B> Se a Opção de Tempo de Espera estiver como EXTENSION, esta extensão do plano de discagem será usada para enviar a chamada quando o tempo de espera superar a configuração de tempo de espera. Para AGENTDIRECT em-grupos, você pode colocar AGENTEXT neste campo eo sistema irá procurar o usuário cinco campo personalizado e enviar a chamada para esse número dialplan.

<BR>
<A NAME="inbound_groups-hold_time_option_callmenu">
<BR>
<B>Segure Callmenu opção de tempo -</B> Se a opção Hold Time está definido para CALL_MENU, este é o menu de chamada que a chamada será enviado para que o tempo de espera estimado ultrapassa os Segundos opção Tempo de Espera.

<BR>
<A NAME="inbound_groups-hold_time_option_voicemail">
<BR>
<B>VoiceMail da Opção de Espera - </B> Se a Opção de Espera estiver como VOICEMAIL, esta é a caixa de voicemail que a chamada será direcionada, caso o tempo de espera exceda o tempo configurado na opção de tempo. Em um AGENTDIRECT em grupo, definindo-o para AGENTVMAIL irá selecionar o ID do usuário para usar correio de voz.

<BR>
<A NAME="inbound_groups-hold_time_option_xfer_group">
<BR>
<B>Gr. de Entr. da Opção de Espera - </B> Se a Opção de Espera for IN_GROUP, este grupo de entrada será usado para transferir a chamada que entrar e o tempo de espera estimado for maior que o tempo da opção..

<BR>
<A NAME="inbound_groups-hold_time_option_press_filename">
<BR>
<B>Hold Time Filename Pressione Opção -</B> Se a opção Tempo de espera é definida como uma das opções PRESS_, este é o prompt de nome de arquivo que é jogado, se o tempo de espera estimado ultrapassa os Segundos Opção Hold Time para dar ao cliente a opção de pressionar 1 em seu telefone para executar o Hold Time selecionado Pressione Opção. É muito importante que este ficheiro de áudio é de 10 segundos ou menos, ou não haverá problemas. O padrão é a-ser-chamado-back.

<BR>
<A NAME="inbound_groups-hold_time_option_no_block">
<BR>
<B>Segure Pressione Opção No Time Bloco -</B> Definir esta opção como Y permitirá chamadas na fila atrás de uma chamada onde o espera Tempo Pressione Opção Filename prompt é jogar para ir a um agente se torna disponível, enquanto a mensagem está jogando. Enquanto o Tempo de espera Opção mensagem Filename Imprensa está jogando a um cliente que não pode ser enviada a um agente. O padrão é N.

<BR>
<A NAME="inbound_groups-hold_time_option_prompt_seconds">
<BR>
<B>Hold Time Opção Segundos Nome do ficheiro Imprensa -</B> Este campo precisa ser definido como o número de segundos que o tempo de espera Filename Pressione Opção joga. O padrão é 10.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_filename">
<BR>
<B>Segure Opção Time After Filename Imprensa -</B> Se a opção Tempo de espera é definida como uma das opções PRESS_ ou CALLERID_CALLBACK, este é o prompt de nome de arquivo que é jogado depois que o cliente tem pressionado 1 ou a chamada foi adicionado à lista de chamada de retorno.

<BR>
<A NAME="inbound_groups-hold_time_option_callback_list_id">
<BR>
<B>ID da Lista de Agendamento de Espera - Se a Opção de Tempo de Espera estiver como CALLERID_CALLBACK, esta é a ID da lista na qual a chamada é adicionada como novo registro caso o tempo de chamada exceda o tempo definido na configuração</B> .

<BR>
<A NAME="inbound_groups-agent_alert_exten">
<BR>
<B>Agente de Alerta Nombre de archivo -</B> El archivo de audio a jugar a un agente de anunciar que una llamada está llegando a su agente. Para no utilizar esta función establezca este valor por defecto es a X. Ding.

<BR>
<A NAME="inbound_groups-agent_alert_delay">
<BR>
<B>Atraso de Alerta ao Agente -</B> O tempo em milisegundos para esperar antes de enviar a chamada para o agente após tocar na extensão do agente. O padrão é 1000.

<BR>
<A NAME="inbound_groups-default_xfer_group">
<BR>
<B>Grupo de Transf. Padrão -</B> Este campo é o Grupo de Entrada padrão que será automaticamente selecionado quando um agente seleciona transferência-conferência na tela do agente.

<BR>
<A NAME="inbound_groups-ingroup_recording_override">
<BR>
<B>In-Group Recording Override -</B> Este campo permite o primordial do cenário de gravação de chamada campanha. Esta configuração pode ser substituído pela configuração de substituição de gravação usuário. Com deficiência não vai substituir a configuração de gravação de campanha. NUNCA irá desabilitar a gravação no cliente. ONDEMAND é o padrão e permite que o agente para iniciar e parar a gravação, conforme necessário. ALLCALLS irá iniciar a gravação no cliente sempre que uma chamada é enviada para um agente. ALLFORCE irá iniciar a gravação no cliente sempre que uma chamada é enviada para um agente dando o agente nenhuma opção para parar a gravação.

<BR>
<A NAME="inbound_groups-ingroup_rec_filename">
<BR>
<B>In-Group Recording Filename -</B> Este campo irá substituir o regime de nomes de arquivos de gravação de campanha, a menos que ela é definida como NONE. As variáveis ​​são permitidas CAMPANHA INGROUP CUSTPHONE FULLDATE TINYDATE EPOCH AGENTE VENDORLEADCODE LEADID CALLID. O padrão é FULLDATE_AGENTE e ficaria assim 20051020-103108_6666. Outro exemplo é CAMPANHA_TINYDATE_CUSTPHONE que ficaria assim TESTCAMP_51020103108_3125551212. Te resultando nome do arquivo deve ser inferior a 90 caracteres. Default is NONE.

<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="inbound_groups-qc_enabled">
	<BR>
	<B>CQ Ativado - </B> Configurando este campo como Y permite que os recursos de Controle de Qualidade do agente funcionem. O padrão é N.

	<BR>
	<A NAME="inbound_groups-qc_statuses">
	<BR>
	<B>Status de CQ - </B> Esta área é onde você escolhe quais status de registro devem passar pelo Controle de Qualidade. Selecione o status que você quer que o CQ revise. 

	<BR>
	<A NAME="inbound_groups-qc_shift_id">
	<BR>
	<B>Turno de CQ - </B> Este é o turno usado pelo CQ para trazer registros de CQ para um grupo de entrada. Os dias da semana são ignorados para estas funções.

	<BR>
	<A NAME="inbound_groups-qc_get_record_launch">
	<BR>
	<B>Entrada do Registro de CQ -</B> Isso permite uma das seguintes ações a ser ativada na entrada de um registro de CQ para um agente.

	<BR>
	<A NAME="inbound_groups-qc_show_recording">
	<BR>
	<B>Mostrar Gravação do CQ - </B> Permite que uma gravação que está ligada com o registro do CQ seja mostrada na tela do agente de CQ.

	<BR>
	<A NAME="inbound_groups-qc_web_form_address">
	<BR>
	<B>Endereço WebForm de CQ - </B> Este é o endereço do websitem que o agente de CQ pode entrar quando clicar no botão WEBFORM na tela de CQ.

	<BR>
	<A NAME="inbound_groups-qc_script">
	<BR>
	<B>Script CQ - </B> Este é o Script que pode ser usado pelos agentes de CQ na aba SCRIPT na tela de CQ.
	<?php
	}
?>

<BR>
<A NAME="inbound_groups-hold_recall_xfer_group">
<BR>
<B>Grupo de Rechamada de Espera- </B> Se um cliente liga de volta para este grupo de entrada mais de uma vez e esta config. estiver como NONE, então a chamada é automaticamente desviada para o Grupo de Entrada selecionado neste campo. O padrão é NONE.

<BR>
<A NAME="inbound_groups-no_delay_call_route">
<BR>
<B>Rotear Sem Atraso - </B> Configurando como Y irá remover qualquer tempo de espera ou anuncios de audio e irá tentar enviar a chamada direto para o agente. Não sobrepõe mensagem de boas vindas e anuncios da fila de espera. O padrão é N.

<BR>
<A NAME="inbound_groups-answer_sec_pct_rt_stat_one">
<BR>
<B>Percentual Estatístico de Chamadas Atendidas em X seg - </B> Este campo permite você configurar o número de segundos que a tela de estatísticas em tempo real demorará para calcular o percentual de chamadas atendidas que foram atendidas antes de X segundos em espera.

<BR>
<A NAME="inbound_groups-start_call_url">
<BR>
<B>Iniciar llamada URL -</B> This web URL address is not seen by the agent, but it is called every time a call is sent to an agent if it is populated. Uses the same variables as the web form fields and scripts. Default is blank.

<BR>
<A NAME="inbound_groups-dispo_call_url">
<BR>
<B>Dispo Call URL -</B> This web URL address is not seen by the agent, but it is called every time a call is dispositioned by an agent if it is populated. Uses the same variables as the web form fields and scripts. dispo and talk_time are the variables you can use to retrieve the agent-defined disposition for the call and the actual talk time em segundo of the call. Default is blank.

<BR>
<A NAME="inbound_groups-add_lead_url">
<BR>
<B>Adicionar Chumbo URL -</B> Este endereço de URL teia não é visto pelo agente, mas é chamado cada vez que um chumbo é adicionado ao sistema através do processo de entrada. O padrão é em branco. Você deve começar esta URL com VAR se você quiser usar variáveis ​​e, é claro - A - e - B - em torno da variável real na URL onde você quer usá-lo. Aqui está a lista de variáveis ​​que estão disponíveis para esta função. lead_id, vendor_lead_code, list_id, phone_number, phone_code, did_id, did_extension, did_pattern, did_description, uniqueid

<BR>
<A NAME="inbound_groups-na_call_url">
<BR>
<B>No URL chamada Agent -</B> Este endereço de URL da web não é visto pelo agente, mas se ela é preenchida é chamada cada vez que uma chamada que não é tratado por um agente é desligou ou transferidos. Usa as mesmas variáveis ​​que os campos de formulários da Web e scripts. dispo pode ser usado para recuperar o definido pelo sistema de disposição para a chamada. Esta URL não pode ser um caminho relativo. O padrão é em branco.

<BR>
<A NAME="inbound_groups-default_group_alias">
<BR>
<B>Alias de Grupo Padrão - </B> Se você permitium Alias de Grupo para a campanha que o agente está logado, então este é o alias de grupo que é selecionado como padrão em uma chamada vindo deste grupo de entrada quando o agente escolhe um alias de grupo para uma chamada manual. O padrão é NONE ou vazio.

<BR>
<A NAME="inbound_groups-dial_ingroup_cid">
<BR>
<B>Disque No Grupo CID -</B> Se a campanha do agente permite o manual no Grupo de discagem, este número de identificação de chamada será enviada como o CID de saída da chamada de telefone se ele está preenchido, substituindo as configurações de campanha e lista de configuração de substituição CID. O padrão é vazio.

<BR>
<A NAME="inbound_groups-extension_appended_cidname">
<BR>
<B>Extensão Anexar CID -</B> Se activado, as chamadas entrada, desde no grupo-terá um espaço ea extensão de telefone do agente acrescentado ao final do nome CallerID para a chamada antes de ser enviado para o agente. Padrão é n para deficientes físicos.

<BR>
<A NAME="inbound_groups-uniqueid_status_display">
<BR>
<B>Mostrar de Status uniqueid -</B> Se ativado, quando um agente recebe uma chamada através deste em grupo eles vão ver o uniqueid da chamada adicionada à linha de status em sua interface agente. O prefixo opção vai acrescentar o prefixo, definido abaixo, para o início do uniqueid no visor. O padrão é desabilitado. Se já havia uma Uniqueid definido em uma chamada em entrar neste grupo, então o uniqueid original será exibida. Se a opção PRESERVE é usado ea chamada é enviada para um segundo agente, o uniqueid e prefixo exibido para o primeiro agente também será exibido para o segundo agente.

<BR>
<A NAME="inbound_groups-uniqueid_status_prefix">
<BR>
<B>Prefixo Estado uniqueid -</B> Se a opção for selecionada PREFIX acima, então este é o valor do prefixo. O padrão é vazio.




<BR><BR><BR><BR>

<B><FONT SIZE=3>INBOUND_DIDS TABELA</FONT></B><BR><BR>
<BR>
<A NAME="inbound_dids-did_pattern">
<BR>
<B>Extensão DDR- </B> Este é o número, extensão ou DDR que irá acionar esta entrada e que você irá rotear no sistema usando este recurso. Existe um DDR padrão reservado que você pode usar que é a palavra -default- sem os traços, que irá permitir que você envie uma chamada que não pertenca a nenhum padrão existente para o DDR padrão.

<BR>
<A NAME="inbound_dids-did_description">
<BR>
<B>Descrição do DDR - </B> Esta é a descrição do roteamento ao DDR.

<BR>
<A NAME="inbound_dids-did_active">
<BR>
<B>DDR Ativo - </B> Este é o campo que você configura se o DDR está ativo ou não. O padrão é Y.

<BR>
<A NAME="inbound_dids-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para este fez, isso permite a visualização de administração deste ter restringido por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário administrador para visualizar este fez.

<BR>
<A NAME="inbound_dids-did_route">
<BR>
<B>DID Route -</B> Este é o tipo de percurso que você definir o DID para usar. EXTEN vai enviar chamadas para o ramal entrou abaixo, correio de voz irá enviar chamadas diretamente para a caixa de correio de voz entrou a seguir, agente vai enviar chamadas para um agente se está logado, telefone irá enviar a chamada para uma entrada de telefones selecionado abaixo, IN_GROUP enviará chamadas diretamente para o grupo de entrada especificado. O padrão é EXTEN. CALLMENU irá enviar a chamada para o menu Chamada definida. VMAIL_NO_INST enviará a chamada para a caixa de correio de voz que você tenha definido a seguir e não vai jogar instruções após a mensagem de correio de voz.

<BR>
<A NAME="inbound_dids-record_call">
<BR>
<B>Registro de chamada -</B> Esta opção permite que você defina as chamadas que vem a este fez para ser gravado. Y vai gravar a chamada inteira, Y_QUEUESTOP irá gravar a chamada até que a chamada é hungup ou entra em uma fila em grupo, N não vai gravar a chamada. O padrão é N.

<BR>
<A NAME="inbound_dids-extension">
<BR>
<B>Extensão -</B> Se EXTEN for selecionado como rota, então este é a extensão do plano de discagem que as chamadas serão enviadas. O padrão é 999811112, sem-serviço.

<BR>
<A NAME="inbound_dids-exten_context">
<BR>
<B>Contexto da Extensão -</B> Se EXTEN estiver selecionado como Rota do DDR, então este é o contexto do plano de discagem para o qual as chamadas serão enviadas. O padrão é default.

<BR>
<A NAME="inbound_dids-voicemail_ext">
<BR>
<B>Caixa de VoiceMail - </B> Se VOICEMAIL estiver selecionado como rota de DDR, então esta é a caixa de voicemail que a chamada deve ser enviada. O padrão é vazio.

<BR>
<A NAME="inbound_dids-phone">
<BR>
<B>Extensão do Ramal - </B> Se PHONE estiver selecionado na rota DDR, então esta é a extensão do ramal que as chamadas serão enviadas.

<BR>
<A NAME="inbound_dids-server_ip">
<BR>
<B>IP do Servidor do Ramal - </B> Se PHONE estiver selecionado como rota DDR, então este é o IP do servidor do ramal que as chamadas devem ser enviadas.

<BR>
<A NAME="inbound_dids-menu_id">
<BR>
<B>Menu -</B> Se CALLMENU estiver selecionado como Rota de DDR, então este é o Menu para o qual a chamada será transferida.

<BR>
<A NAME="inbound_dids-user">
<BR>
<B>Usuário Agent -</B> Se agente é selecionado como o Route DID, então este é o agente que chama será enviado para.

<BR>
<A NAME="inbound_dids-user_unavailable_action">
<BR>
<B>Ação p/ Usuário não Dispon. - </B> Se AGENTE estiver selecionado como rota de DDR, e o usuário não estiver logado ou disponível, então esta é a rota que as chamadas serão direcionadas.

<BR>
<A NAME="inbound_dids-user_route_settings_ingroup">
<BR>
<B>Config. de Grupo de Entr. do Agente - </B> Se AGENTE estiver selecionado como roda DDR, então este é o grupo de entrada que irá ser usado para configurações de fila na qual o cliente ficará esperando para ser direcionado ao agente. O padrão é AGENTDIRECT.

<BR>
<A NAME="inbound_dids-group_id">
<BR>
<B>ID do Grupo de Entr. - </B> Se IN_GROUP estiver selecionado como rota de DDR, então este é o grupo de entrada a ser enviadas as chamadas.

<BR>
<A NAME="inbound_dids-call_handle_method">
<BR>
<B>No Grupo Método identificador de chamada -</B> Se IN_GROUP é selecionado como o Route DID, então este é o método de processamento de chamadas utilizado para estas chamadas. CID vai adicionar um novo registro liderança com cada chamada usando o CallerID como o número de telefone, CIDLOOKUP tentará procurar o número de telefone pelo CallerID em todo o sistema, CIDLOOKUPRL tentará procurar o número de telefone pelo CallerID em apenas uma lista específica , CIDLOOKUPRC tentará procurar o número de telefone pelo CallerID em todas as listas que pertencem à campanha especificada, mais perto está especificado para chamadas mais estreita, ANI irá adicionar um novo registro liderança com cada chamada usando o ANI como o número de telefone, ANILOOKUP tentará procurar o número de telefone pela ANI em todo o sistema, ANILOOKUPRL tentará procurar o número de telefone pela ANI em apenas uma lista específica, XDIGITID pedirá ao chamador para um código de X dígitos antes da chamada será colocado na fila, VIDPROMPT pedirá ao chamador para o seu número de ID e criará um novo recorde de liderança com o CallerID como o número de telefone eo ID como a identificação do fornecedor, VIDPROMPTLOOKUP tentará procurar o ID em todo o sistema, VIDPROMPTLOOKUPRL tentará lookup o ID do fornecedor pela ID em apenas uma lista especificada, VIDPROMPTLOOKUPRC tentará procurar o ID do fornecedor pela ID em todas as listas que pertencem à campanha especificada. O padrão é CID. Se um método CIDLOOKUP é usado com ALT, ele irá procurar o campo alt_phone para o número de telefone, se nenhuma correspondência for encontrada para o número de telefone principal. Se um método é usado com CIDLOOKUP ADDR3, ele irá procurar o campo address3 para o número de telefone, se nenhuma correspondência for encontrada para o número de telefone principal e, opcionalmente, o campo alt_phone.

<BR>
<A NAME="inbound_dids-agent_search_method">
<BR>
<B>Método de Busca do Grupo de Entrada - </B> Se IN_GROUP estiver selecionado na rota de DDR, então este é o método de pesquisa do agente a ser usado pelo grupo de entrada, LO é Balanceamento de Carga com Transbordo e irá tentar enviar a chamada para um agente no servidor local antes de tentar enviar para outro servidor, LB é balanceamento de carga e tentará enviar a chamada para o próximo agente não importando o servidor que estiver logado, SO é Somente Servidor e tentará enviar a chamada somente para agentes no mesmo servidor da chamada. O padrão é LB.

<BR>
<A NAME="inbound_dids-list_id">
<BR>
<B>ID da Lista do Grupo de Entrada- </B> Se IN_GROUP estiver selecionado como rota de DDR, então este é o ID da lista que os registros devem ser pesquisados e onde o novo registro será incluído se necessário.

<BR>
<A NAME="inbound_dids-campaign_id">
<BR>
<B>ID da Camp. do Grp. de Entrada- </B> Se IN_GROUP estiver selecionado como rota de DDR, então este é o ID da campanha que os registros devem ser procurados quando o método de pesquisa for CIDLOOKUPRC.

<BR>
<A NAME="inbound_dids-phone_code">
<BR>
<B>Código do Tel. de Entrada -</B> Se IN_GROUP estiver selecionado como rota de DDR, então este é o código de telefone usado para um novo registro.

<BR>
<A NAME="inbound_dids-filter_clean_cid_number">
<BR>
<B>Limpe Número CID -</B> Este campo permite que você especifique um número de dígitos para restringir o número de identificação de entrada chamador, colocando um R na frente do número de dígitos, por exemplo, para restringir o direito de 10 dígitos que gostariam de entrar no R10. Você também pode usar esse recurso para remover apenas um dígito ou dígitos colocando um L na frente dos dígitos específicos que você deseja remover, por exemplo, para remover a 1 como o primeiro dígito que você entraria em L1. O padrão é vazio. Se mais de uma regra é especificada certifique-se de separá-los com um espaço e do R será executado antes do L.

<BR>
<A NAME="inbound_dids-filter_inbound_number">
<BR>
<B>Número de entrada do filtro -</B> Esta opção, se habilitada permite filtrar as chamadas que entram neste DID e enviá-los para uma ação alternativa se corresponderem a um número de telefone que está no grupo de telefone filtro ou uma resposta URL se você tiver configurado um. O padrão é DISABLED. GRUPO irá pesquisar em um grupo de Telefone Filter. URL irá enviar uma URL e irá corresponder a 1, se é enviado de volta. DNC_INTERNAL irá procurar pela lista interna DNC. DNC_CAMPANHA irá procurar por uma lista específica DNC campanha.

<BR>
<A NAME="inbound_dids-filter_phone_group_id">
<BR>
<B>ID Group Filter Telefone -</B> Se o campo Número de filtro de entrada é definida como grupo, então este é o ID do Grupo Telefone filtro que terão seus números pesquisados ​​à procura de um jogo para o número de identificação do autor da chamada.

<BR>
<A NAME="inbound_dids-filter_url">
<BR>
<B>Filtro de URL -</B> Se o campo Número de filtro de entrada é definida como URL, então este é o endereço web de um script que irá procurar um sistema remoto e retornar um 1 por um jogo e um 0 para nenhuma correspondência. Apenas duas variáveis ​​estão disponíveis no endereço se você usar o prefixo VAR como com endereços webform em campanhas, - A - phone_number - B - e - A - did_pattern - B - pode ser usado na URL para indicar o identificador de chamadas do chamador e do DID que o cliente chamado em.

<BR>
<A NAME="inbound_dids-filter_url_did_redirect">
<BR>
<B>URL Filter DID Redirect -</B> Se o campo Número de entrada do filtro é definido como URL, em seguida, essa configuração permite que a resposta URL para especificar um sistema de DID para redirecionar a chamada para INSEAD de usar a ação padrão. Se um 0 é retornado, em seguida, é utilizada a ação padrão. Se há alguma coisa que não seja um 0 é retornado então a chamada será redirecionada para o valor de resposta URL resultante.

<BR>
<A NAME="inbound_dids-filter_dnc_campaign">
<BR>
<B>Filtre Campanha DNC -</B> Se o campo Número de entrada do filtro é definida como DNC_CAMPANHA então este é o ID campanha específica que a lista campanha DNC pertence.



<BR>
<A NAME="inbound_dids-filter_action">
<BR>
<B>Ação do Filtro -</B> Se Número de entrada do filtro é ativado e uma correspondência for encontrada, então esta é a ação que deve ser tomado. Este é o mesmo que a rota que você selecionar para um DID, e as configurações abaixo funcionar exatamente como eles fazem para um encaminhamento padrão.

<BR>
<A NAME="inbound_dids-custom_one">
<BR>
<B>Personalizado DID Campos - </ B> Estes cinco campos podem ser usados ​​para diversos fins, principalmente relativas à programação personalizada e relatórios.




<BR>
<A NAME="did_ra_extensions">
<BR>
<B>DID substituições extensão remota do Agente -</B><BR>Esta seção permite que você ative DIDs ter substituições de extensão para agente remoto encaminhado chama através de in-grupos. O Início do usuário deve ser um usuário válido Iniciar Remote Agent ou se você quer a extensão Substituir entrada de trabalhar para todas as chamadas, então você pode usar --- todos --- no campo Iniciar do usuário. Se houver várias entradas para o mesmo usuário e DID Iniciar, em seguida, as entradas de ativos será usado em um método round robin.




<BR><BR><BR><BR>

<B><FONT SIZE=3>CHAMADA MENU TABELA</FONT></B><BR><BR>
<A NAME="call_menu-menu_id">
<BR>
<B>ID do Menu -</B> Este é o ID para este passo do menu de chamadas. Ele irá aparecer como o contexto que é usado no plano de discagem para este menu de chamadas. Aqui é uma lista de frases reservados que não pode ser usado como um menu de IDs: vicidial, vicidial-auto, general, globals, default, trunkinbound, loopback-no-log, monitor_exit, monitor.

<BR>
<A NAME="call_menu-menu_name">
<BR>
<B>Nome do Menu -</B> Este campo é o nome descritivo do menu de chamadas.

<BR>
<A NAME="call_menu-menu_prompt">
<BR>
<B>Audio do Menu -</B> Este campo contém o nome do arquivo para o audio que será tocado no inicio do menu. Puede introducir propmts múltiples en este campo y los demás ámbitos del sistema mediante la separación de ellos con un carácter de canalización. Você pode adicionar NOINT diretamente na frente de um nome de arquivo de áudio para fazê-lo assim que a reprodução não pode ser interrompido com uma tecla pressionada pelo chamador, o NOINT não deve ser uma parte do nome do arquivo, é uma bandeira especial para o sistema. Você também pode usar propósito especial. Agi scripts neste campo, bem como o roteiro cm_date.agi, discutir com o administrador para obter mais detalhes.

<BR>
<A NAME="call_menu-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="call_menu-menu_timeout">
<BR>
<B>Tempo max do menu -</B> Este campo é onde você configura o tempo máximo em segundos que um menu irá aguardar o cliente digitar uma opção em DTMF. Establecer este campo a cero 0 significa que no habrá tiempo de espera después de que el sistema se juega.

<BR>
<A NAME="call_menu-menu_timeout_prompt">
<BR>
<B>Audio de tempo excedido do menu -</B> Este campo contém o arquivo de audio a ser tocado caso o tempo máximo de espera tenha sido alcançado. O padrão é NONE para nao tocar audio.

<BR>
<A NAME="call_menu-menu_invalid_prompt">
<BR>
<B>Audio de Opção Inválida do menu -</B> Este campo contém o nome do arquivo de audio que será tocado quando o cliente selecionar uma opção inválida. O padrão é NONE que não toca audio quando a opção é inválida.

<BR>
<A NAME="call_menu-menu_repeat">
<BR>
<B>Repetir Menu -</B> Este campo é onde você define o número de vezes que o menu irá tocar depois da primeira vez, se nenhuma opção for escolhida pelo cliente. O padrão é 1 para repetir o menu uma vez.

<BR>
<A NAME="call_menu-menu_time_check">
<BR>
<B>Verif. Horário de Menu -</B> Este campo é onde você seleciona se as chamadas á URA devem ser restritas a horários específicos cadastrados no Cadastro de Horários de Chamada. Se o Horário de chamada estiver vazio, esta configuração será ignorada. O padrão é 0 para desabilitado.

<BR>
<A NAME="call_menu-call_time_id">
<BR>
<B>ID do Horário de Cham. -</B> Este é o ID do Horário de chamadas que será usado para restringir horários de chamada para este menu, quando a opção estiver habilitada.

<BR>
<A NAME="call_menu-track_in_vdac">
<BR>
<B>Rastrear Chamadas no Relat. Tempo-Real -</B> Este campo é onde você seleciona se deseja que as chamadas devem ser rastreadas no relatório de Tempo-Real como uma chamada de URA. O padrão é 1 para ativo.

<BR>
<A NAME="call_menu-tracking_group">
<BR>
<B>Grupo de seguimiento de -</B> Esta es la identificación que se puede utilizar para rastrear las llamadas a este menú de llamada cuando se mira al Informe de IVR. La lista incluye CALLMENU como predeterminado, así como todos los Grupos de In -.

<BR>
<A NAME="call_menu-dtmf_log">
<BR>
<B>Pressione a tecla Entrar -</B> Esta opção, se habilitado irá registrar a imprensa DTMF chave pelo chamador neste menu Chamadas. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="call_menu-dtmf_field">
<BR>
<B>Entrar Campo -</B> Se a opção Registrar Pressione a tecla estiver ativada, essa configuração opcional pode permitir que a resposta a ser armazenado neste campo lista. vendor_lead_code, source_id, phone_code, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, alt_phone, email, security_phrase, comments, rank, owner, status, user. O padrão é NONE para deficientes físicos.

<BR>
<A NAME="call_menu-option_value">
<BR>
<B>Valor da Opção -</B> Este campo é onde você define a opção de menu, as opções são: 0,1,2,3,4,5,6,7,8,9,*,#,A,B,C,D,TIMECHECK. A opção especial TIMECHECK pode ser usada somente se você habilitou a verificação de horário e existe um horário de chamadas definido para o menu. Para remover uma opção, apenas selecione a rota como REMOVE e a opção será removida quando você clicar no botão enviar. TIMEOUT irá permitir que você defina o que acontece com a chamada quando ele expira sem entrada a partir da chamada. INVALID irá permitir que você defina o que acontece quando o chamador entra uma opção inválida. INVALID_2ND e 3 º só pode estar ativo se inválido não for usado, ele vai esperar até a entrada segundo ou terceiro inválido pelo chamador antes de executar a opção.

<BR>
<A NAME="call_menu-option_description">
<BR>
<B>Descrição da Opção -</B> Este campo é onde você descreve a opção, esta descrição será colocada no plano de discagem como um comentário acima da opção.

<BR>
<A NAME="call_menu-option_route">
<BR>
<B>Rota da Opção -</B> 
 VMAIL_NO_INST enviará a chamada para a caixa de correio de voz que você tenha definido a seguir e não vai jogar instruções após a mensagem de correio de voz.

<BR>
<A NAME="call_menu-option_route_value">
<BR>
<B>Valor da rota da opção -</B> Este campo é onde você coloca o valor que define para onde, na Opção de Rota selecionada, a chamada deve ser direcionada .

<BR>
<A NAME="call_menu-option_route_value_context">
<BR>
<B>Contexto do valor da rota da opção -</B> Este campo é opcional e somente é usado para Rotas do tipo EXTENSION.

<BR>
<A NAME="call_menu-ingroup_settings">
<BR>
<B>Chamada do menu Configurações em grupo -</B> Se a rota é definida como ingroup então há muitas opções que você pode definir para definir como a chamada é enviada para a fila. Em-Group é o grupo de entrada que você deseja que o convite para ir. Método Handle é o jeito que você quer que a chamada seja tratada, <a href="#inbound_dids-call_handle_method">Clique aqui para ver uma lista dos métodos disponíveis punho</a>. Método de pesquisa define como a fila vai encontrar o próximo agente, recomendo deixar em LB. ID lista é a lista que o novo eletrodo é inserido, também se o método não é um método de pesquisa eo chumbo não é encontrado. ID da campanha é a campanha para procurar listas através se um dos métodos de RC é usado. Código de telefone é a entrada do campo phone_code para o chumbo que é inserido com. VID Enter Filename é usado se o método é definido como um dos métodos VIDPROMPT, é o prompt de áudio tocado de pedir ao cliente a entrar no seu ID. VID Filename número de identificação é usado se o método é definido como um dos métodos VIDPROMPT, é o prompt de áudio tocado após cliente entra em seu ID, algo como você digitou. VID Filename Confirmar é usado se o método é definido como um dos métodos VIDPROMPT, é o prompt de áudio tocado para confirmar sua identidade, algo como PRESS 1 para confirmar e entrar novamente 2. Dígitos VID é usado se o método é definido como um dos métodos VIDPROMPT, se ele é definido como um número é o número de dígitos que devem ser inseridos pelo cliente, quando solicitado pelo seu ID, se definido como vazio ou X, em seguida, o cliente terá que pressionar a libra ou de hash para terminar a sua entrada de sua ID.

<BR>
<A NAME="call_menu-custom_dialplan_entry">
<BR>
<B>Entrada Dialplan personalizado -</B> Este campo le permite entrar en los elementos dialplan que desea para el menú de llamada.

<BR>
<A NAME="call_menu-qualify_sql">
<BR>
<B>Qualificar SQL -</B> Este campo permite a entrada de SQL - Structured Query Language - fragmentos do banco de dados, como com filtros, para determinar se este menu chamada deve jogar para o chamador ou não. Esta característica só funciona se a chamada tem o conjunto callerIDname antes de ser enviado para este menu de chamada, quer como uma transferência de levantamento de saída, ou através da utilização de um menu de chamada para uma chamada de gota no grupo. Se houver uma coincidência, a chamada será prosseguir normalmente. Se não houver correspondência, a chamada vai para a opção D ou a opção inválida se nenhuma opção D está definido. Você não pode usar aspas simples neste campo, apenas aspas, se eles são necessários. O padrão é vazio para deficientes.






<BR><BR><BR><BR>

<BR>
<A NAME="filter_phone_groups-filter_phone_group_id">
<BR>
<B>ID Group Filter Telefone -</B> Este é o ID do Grupo Telefone filtro que é o recipiente para um grupo de números de telefone que você pode ter procurado automaticamente através quando receber uma chamada em um arquivo. DID e enviar para uma rota alternativa, se houver uma correspondência Este campo deve ser entre 2 e 20 caracteres e não têm pontuação, exceto para sublinhado.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_name">
<BR>
<B>Nome do grupo Filtro Telefone -</B> This is the name of the Filter Phone Group and is displayed with the ID in select lists where this feature is used. This field should be between 2 and 40 characters.

<BR>
<A NAME="filter_phone_groups-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="filter_phone_groups-filter_phone_group_description">
<BR>
<B>Descrição Group Filter Telefone -</B> Esta é a descrição do Grupo de telefone de filtro, é puramente para fins de notação apenas e não é um campo necessário.




<BR><BR><BR><BR>

<B><FONT SIZE=3>REMOTE_AGENTS TABELA</FONT></B><BR><BR>
<A NAME="remote_agents-user_start">
<BR>
<B>Início do ID do Usuário -</B> Este é o ID do utilizador a partir de que é utilizada quando as entradas de agente remotos são inseridos no sistema. Se o número de linhas for maior que 1, este número é incrementado por um, até que cada linha tem uma entrada. Certifique-se de criar uma nova conta de usuário com um nível de usuário de 4 ou grandes, se você quer que eles sejam capazes de usar a página vdremote.php para acesso web remoto desta conta.

<BR>
<A NAME="remote_agents-number_of_lines">
<BR>
<B>Número de Linhas -</B> Define quantas entradas de usuários remotos o sistema deve criar, e determina quantas linhas podem ser enviadas com segurança para o número abaixo.

<BR>
<A NAME="remote_agents-server_ip">
<BR>
<B>IP do Servidor -</B> Um cadastro de agente remoto só funciona em um servidor, aqui é onde você seleciona qual servidor.

<BR>
<A NAME="remote_agents-conf_exten">
<BR>
<B>Extensão Externa -</B> Este é o número para o qual você quer que as chamadas sejam direcionadas. Tenha certeza que é um número completo do plano de discagem e que se você precisa de um 9 no começo, você o coloca aqui. Teste discando o número de um fone no sistema.

<BR>
<A NAME="remote_agents-extension_group">
<BR>
<B>Grupo de Extensão -</B> Se definido para algo diferente de NONE ou vazio este irá substituir o campo Extensão Externa e usar as entradas de extensão do Grupo que têm o mesmo ID de grupo de extensão. O padrão é NONE para desativado.

<BR>
<A NAME="remote_agents-status">
<BR>
<B>Status -</B>  Aqui é onde você desliga ou liga o agente remoto. Assim que o agente está ativo, o sistema entende que ele pode receber chamadas. Pode demorar até 30 segundos após a mudança do status para Inativopara parar de receber chamadas.

<BR>
<A NAME="remote_agents-campaign_id">
<BR>
<B>Campanha -</B>  Aqui é onde você escolhe a campanha que este agente remoto deve ser logado. Chamadas entrantes precisam usar uma campanha CLOSER e selecionar uma campanha entrante abaixo para que você receba chamadas.

<BR>
<A NAME="remote_agents-on_hook_agent">
<BR>
<B>Em Hook-agente -</B> Esta opção só é usada para chamadas de entrada que vão a este agente remoto. Este recurso irá chamar o agente remoto e não enviar o cliente para o agente remoto até que a linha é atendida. Padrão é n para deficientes físicos.

<BR>
<A NAME="remote_agents-on_hook_ring_time">
<BR>
<B>No gancho-Time Anel -</B> Esta opção só é usada quando o campo agente no gancho acima é definido como Y e, em seguida, apenas para chamadas de entrada próximos a este agente remoto. Este é o número de segundos que cada tentativa de chamada irá tocar para tentar obter uma resposta. É recomendável que você defina isso para alguns segundos menos do que leva para uma chamada para ser enviado para a caixa postal. O padrão é 15.

<BR>
<A NAME="remote_agents-closer_campaigns">
<BR>
<B>Grupos de entrada -</B> Aqui é selecionado o grupo de entrada do qual você quer receber chamadas se você selecionou uma campanha CLOSER(finalização).


<BR><BR><BR><BR>

<B><FONT SIZE=3>EXTENSION_GROUPS TABELA</FONT></B><BR><BR>
<A NAME="extension_groups-extension_group_id">
<BR>
<B>Grupo de Extensão -</B> Este campo obrigatório é onde você entra o ID do grupo que você quer que essa extensão a ser colocada. Sem espaços ou caracteres especiais, exceto para destacar letras e números.

<BR>
<A NAME="extension_groups-extension">
<BR>
<B>Extension -</B> Este campo obrigatório é o lugar onde você colocar a extensão dialplan que você deseja que o agente remoto chama para ser enviado para para esta entrada do grupo de extensão.

<BR>
<A NAME="extension_groups-rank">
<BR>
<B>Rank -</B> Este campo permite-lhe classificar as entradas de extensão do grupo que compartilham o grupo de mesma extensão. O padrão é 0.

<BR>
<A NAME="extension_groups-campaign_groups">
<BR>
<B>Grupos de Campanhas -</B> Neste campo você pode colocar uma lista de IDs de campanha e IDs de grupo ou de entrada que você deseja restringir o uso do grupo de extensão para. Lista deve ser separada por tubos e têm um tubo no início e no fim da cadeia.


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPANHA_LISTS</FONT></B><BR><BR>
<A NAME="campaign_lists">
<BR>
<B>As listas dentro desta campanha são listadas aqui, estando ativas ou desativadas será demonstrado por Y ou N e você pode ir para a tela de cadastro de listas clicando no Id na primeira coluna.</B>


<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPANHA_STATUS TABELA</FONT></B><BR><BR>
<A NAME="campaign_statuses">
<BR>
<B>Através do uso de estados de campanha personalizada, você pode ter os status que só existem para uma campanha específica. O Estado deve ser 1-8 caracteres, a descrição deve ser 2-30 caracteres de comprimento e selecionável define se ele aparece no sistema como uma disposição. O campo human_answered é usado no cálculo do percentual de queda, ou a taxa de abandono. Definir human_answered a Y vai usar esse status quando a contagem das chamadas humanos respondidas. A opção Categoria permite agrupar vários estados em um catogy que pode ser utilizado para a análise estatística.</B>



<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>CAMPANHA_HOTKEYS TABELA</FONT></B><BR><BR>
	<A NAME="campaign_hotkeys">
	<BR>
	<B>Através do uso de teclas de atalho de campanha personalizada, agentes que usam o agente de web-cliente pode desligar e disposição exige apenas pressionando uma única tecla no teclado.</B> Existem duas opções de atalhos especiais que você pode usar em conjunto com discagem a Telefones Alternativos, ALTPH2 - Discar para o Tel. Alt. e  ADDR3----Discagem rápida do endereço 3, permitem ao agente usar o atalho para desligar a chamada, permanecer no mesmo registro e discar outro telefone do mesmo registro. Você também pode usar LTMG ou XFTAMM como status para desencadear uma transferência automática para a opção Leave-Correio de Voz.





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_RECYCLE TABELA</FONT></B><BR><BR>
	<A NAME="lead_recycle">
	<BR>
	<B>Through the use of lead recycling, you can call specific statuses of leads again at a specified interval without resetting the entire list. Lead recycling is campaign-specific and does not have to be a selected dialable status in your campaign. The attempt delay field is the number of seconds until the lead can be placed back in the hopper, this number must be at least 120 seconds. The attempt maximum field is the maximum number of times that a lead of this status can be attempted before the list needs to be reset, this number can be from 1 to 10. You can activate and deactivate a lead recycle entry with the provided links.</B>





	<BR><BR><BR><BR>

	<B><FONT SIZE=3>AUTO ALT STATUS DE DISC.</FONT></B><BR><BR>
	<A NAME="auto_alt_dial_statuses">
	<BR>
	<B>Se o campo Auto Discar Alternativo estiver marcado, os registros que são finalizados sob estes status terão os números alternativos discados após qualquer um desses status de não atendidos.</B>

	<?php
	}
?>



<BR><BR><BR><BR>

<B><FONT SIZE=3>CÓDIGOS DE PAUSA DO AGENTE</FONT></B><BR><BR>
<A NAME="pause_codes">
<BR>
<B>Se o Agente Pausa Códigos campo Ativo está definido para activo em seguida, os agentes serão capazes de escolher entre esses códigos de pausa quando clicam no botão de pausa em suas telas. Esses dados são então armazenados no log do agente. O código de pausa deve conter apenas letras e números e ter menos de 7 caracteres. O nome de código pausa pode não ser mais do que 30 caracteres.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPANHA PRESETS</FONT></B><BR><BR>
<A NAME="xfer_presets">
<BR>
<B>Se o cenário de campanha para pré-está definido como Habilitado, então você tem a capacidade de definir Transferência-Conferência presets que estarão disponíveis para o agente que lhes permite 3-way chamar esses presets ou transferir chamadas cegos a estes números predefinidos. Essas predefinições também tem a opção de ocultar o número associado a cada preset do agente.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>CAMPANHA CID CódigoDeÁreaS</FONT></B><BR><BR>
<A NAME="campaign_cid_areacodes">
<BR>
<B>Se a configuração do sistema para CódigoDeÁrea CIDs é habilitado eo cenário de campanha para uso personalizado CallerID é definido como código de área, então você tem a capacidade de definir CIDs CódigoDeÁrea que serão usados ​​quando chamado de saída para ligações nesta campanha específica. Você pode adicionar múltiplas callerIDs por areacode e você pode ativar e desativar cada um deles de tempo real. Se mais do que um callerID é activo para um código de área específica, então o sistema irá usar o callerid que tem sido utilizado o menor número de vezes que hoje em dia. Se não callerIDs são ativos para a areacode então o CallerID campanha ou uma lista de substituição CallerID será usado.</B>





<BR><BR><BR><BR>

<B><FONT SIZE=3>USER_GROUPS TABELA</FONT></B><BR><BR>
<A NAME="user_groups-user_group">
<BR>
<B>Grupo do Usuário -</B> Este é o nome abreviado de um grupo de usuários, tente não usar quaisquer espaços ou pontuação para este campo. máximo 20 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="user_groups-group_name">
<BR>
<B>Nome do Grupo -</B> Esta é a descrição do grupo de utilizadores no máximo de 40 caracteres.

<BR>
<A NAME="user_groups-forced_timeclock_login">
<BR>
<B>Forçar Login no Ponto -</B> Esta opção permite que você não deixar um agente efetuar login na interface do agente, caso não tenham registrado no relógio de tempo. O padrão é N. Há uma opção para isentar os usuários de administração, os níveis 8 e 9.

<BR>
<A NAME="user_groups-shift_enforcement">
<BR>
<B>Controle de Turno -</B> Esta configuração permite você restringir logins do agente baseado nos turnos selecionados abaixo. OFF não irá forçar controle de turno. START irá forçar o horário de login mas não terá efeito sobre um agente que está fazendo horário além do limite final e não saiu do sistema. ALL irá forçar o horário de início e fim, retirando o agente do sistema quando o horário do turno estiver terminado. O padrão é OFF

<BR>
<A NAME="user_groups-group_shifts">
<BR>
<B>Turnos do Grupo - </B> Esta é uma lista selecionável de turnos que podem restringir o login do agente no sistema.

<BR>
<A NAME="user_groups-allowed_campaigns">
<BR>
<B>Campanhas Permitidas -</B> Esta é uma lista selecionável de campanhas nas quais os membros deste grupo podem se logar. A opção ALL-CAMPANHAS permite aos usuários neste grupo ver e logar em qualquer campanha no sistema.

<BR>
<A NAME="user_groups-agent_status_viewable_groups">
<BR>
<B>Estado del agente visible Grupos -</B> Esta es una lista seleccionable de grupos de usuarios y las funciones de usuario al que los miembros de este grupo de usuarios pueden ver el estado de las llamadas, así como la transferencia al interior de la pantalla del agente. La opción de grupo permite a los usuarios de este grupo para ver y transferir llamadas a cualquier usuario en el sistema. La campaña-AGENTES opción permite a los usuarios en este grupo para ver y transferir llamadas a cualquier usuario en la campaña que se registran en la. A opção NÃO-logged-in-Agentes permite que todos os usuários do sistema a ser exibido, mesmo que eles não são registrados em curently.

<BR>
<A NAME="user_groups-agent_status_view_time">
<BR>
<B>Ver estado del agente de turno -</B> Esta opción define si el agente va a ver la cantidad de tiempo que los usuarios en su barra lateral agente han sido en su estado actual. Por defecto es n sin o con discapacidad.

<BR>
<A NAME="user_groups-agent_call_log_view">
<BR>
<B>Agente Ver registro de chamadas -</B> Esta opção define se o agente será capaz de ver o seu registo de chamadas para chamadas atendidas através da tela do agente. O padrão é N para não ou desativado.

<BR>
<A NAME="user_groups-agent_xfer_options">
<BR>
<B>Transferência Opções do Agente -</B> Estas opções de permitir a desactivação de botões específicos na secção Conferência de transferência da interface do agente. O padrão é Y para sim ou habilitado.

<BR>
<A NAME="user_groups-agent_fullscreen">
<BR>
<B>Agente Fullscreen -</B> Esta opção se definido como Y irá definir a altura ea largura da tela do agente para o tamanho da janela do navegador, sem qualquer subsídio para os agentes View, chamadas em fila Ver ou Chamadas em vista Session. O padrão é N para não ou desativado.

<BR>
<A NAME="user_groups-allowed_reports">
<BR>
<B>Relatórios admitidos -</B> Se um usuário neste grupo é definido como nível de usuário 7 ou superior, então este recurso pode ser usado para restringir os relatórios que os usuários possam visualizar. O padrão é ALL. Se você quiser selecionar mais de um relatório, em seguida, pressione a tecla Ctrl no teclado enquanto seleciona os relatórios.

<BR>
<A NAME="user_groups-admin_viewable_groups">
<BR>
<B>Admitidos Grupos de Usuários -</B> Esta é uma lista selecionável de Grupos de Usuários em que os membros deste grupo de usuário pode visualizar e possivelmente editar. Grupos de usuários podem restringir o acesso a quase todos os aspectos do sistema, a partir de um DID de entrada para caixas de correio de voz para telefones. A - ALL - opção permite que os usuários neste grupo para ver e efetuar login em qualquer registro no sistema se suas permissões de usuário permitem que.

<BR>
<A NAME="user_groups-admin_viewable_call_times">
<BR>
<B>Admitidos Tempos de Ligação -</B> Esta é uma lista selecionável de Times de chamada para que os membros deste grupo de usuários pode usar em campanhas, em grupos de chamada e menus. A - ALL - opção permite que os usuários neste grupo a usar todos os tempos de chamada no sistema.


<?php
if (strlen($SSwebphone_url) > 5)
	{
	?>
	<BR>
	<A NAME="user_groups-webphone_url_override">
	<BR>
	<B>Substituir URL Webphone -</B> Esta configuração permite que você defina uma URL webphone alternativa apenas para os membros de um grupo de usuários. O padrão é vazio.

	<BR>
	<A NAME="user_groups-webphone_systemkey_override">
	<BR>
	<B>Sistema Webphone Tecla Anulação -</B> Esta configuração permite que você defina uma chave do sistema alternativo webphone apenas para os membros de um grupo de usuários. O padrão é vazio.

	<BR>
	<A NAME="user_groups-webphone_dialpad_override">
	<BR>
	<B>Webphone Dialpad Override -</B> Esta configuração permite ativar ou desativar o teclado na webphone apenas para os membros de um grupo de usuários. O padrão é DISABLED. TOGGLE permitirá ao usuário visualizar e ocultar o teclado, clicando em um link. TOGGLE_OFF será o padrão para não mostrar o teclado na primeira carga, mas permitirá que o usuário para mostrar o teclado, clicando no link dialpad.

	<?php
	}

if ($SSqc_features_active > 0)
	{
	?>
	<BR>
	<A NAME="user_groups-qc_allowed_campaigns">
	<BR>
	<B>Campanha com CQ - </B> Esta lista selecionável de campanhas tem os membros para esse grupo de usuários que terão habilitado o controle de qualidade. A opcão ALL-CAMPANHAS permite ao usuário neste grupo fazer controle de qualidade em qualquer campanha do sistema.

	<BR>
	<A NAME="user_groups-qc_allowed_inbound_groups">
	<BR>
	<B>Permitir CQ de Entrada- </B> Esta é uma lista selecionável de Grupos de Entrada aos quais os membros deste grupo de usuários podem realizar Controle de Qualidade. A opção ALL-GROUPS permite que usuários deste grupo fazer Controle de Qualidade em qualquer grupo do sistema.
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SCRIPTS TABELA</FONT></B><BR><BR>
<A NAME="scripts-script_id">
<BR>
<B>ID do Script -</B> Este é o nome abreviado de um Script. Isso precisa ser um identificador exclusivo. Tente não usar quaisquer espaços ou pontuação para este campo. máximo 10 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="scripts-script_name">
<B>Nome do Script -</B> Este é o título de um Script. Este é um breve resumo do roteiro. máximo 50 caracteres, mínimo de 2 caracteres. Não deve haver espaços ou pontuação de qualquer tipo no campo theis.

<BR>
<A NAME="scripts-script_comments">
<B>Comentários do Script -</B> Isto é onde você pode colocar comentários de um Script tela agente como-mudado para atualização gratuita em 23 de setembro -. máximo 255 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="scripts-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="scripts-script_text">
<B>Texto do Script -</B> Este é onde você coloca o conteúdo de uma tela Script agente. Mínimo de 2 caracteres. Você pode ter a informação do cliente ser preenchido automaticamente no script usando "--A--field--B--" em que o campo é um dos seguintes fieldnames: vendor_lead_code, source_id, list_id, gmt_offset_now, called_since_last_reset, phone_code, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, lead_id, campaign, phone_login, group, channel_group, SQLdate, epoch, uniqueid, customer_zap_channel, server_ip, SIPexten, session_id, dialed_number, dialed_label, rank, owner, camp_script, in_script, script_width, script_height, recording_filename, recording_id, user_custom_one, user_custom_two, user_custom_three, user_custom_four, user_custom_five, preset_number_a, preset_number_b, preset_number_c, preset_number_d, preset_number_e, preset_number_f, preset_dtmf_a, preset_dtmf_b, did_id, did_extension, did_pattern, did_description, closecallid, xfercallid, agent_log_id, entry_list_id, call_id, user_group, called_count, TABELAper_call_notes. For example, this sentence would print the persons name in it----<BR><BR>  Hello, can I speak with --A--first_name--B-- --A--last_name--B-- please? Well hello --A--title--B-- --A--last_name--B-- how are you today?<BR><BR> This would read----<BR><BR>Hello, can I speak with John Doe please? Well hello Mr. Doe how are you today?<BR><BR> Você também pode usar um iframe para carregar uma janela separada dentro da aba SCRIPT, aqui está um exemplo com variáveis ​​previamente preenchida:

<DIV style="height:200px;width:400px;background:white;overflow:scroll;font-size:12px;font-family:sans-serif;" id=iframe_example>
&#60;iframe src="http://www.sample.net/test_output.php?lead_id=--A--lead_id--B--&#38;vendor_id=--A--vendor_lead_code--B--&#38;list_id=--A--list_id--B--&#38;gmt_offset_now=--A--gmt_offset_now--B--&#38;phone_code=--A--phone_code--B--&#38;phone_number=--A--phone_number--B--&#38;title=--A--title--B--&#38;first_name=--A--first_name--B--&#38;middle_initial=--A--middle_initial--B--&#38;last_name=--A--last_name--B--&#38;address1=--A--address1--B--&#38;address2=--A--address2--B--&#38;address3=--A--address3--B--&#38;city=--A--city--B--&#38;state=--A--state--B--&#38;province=--A--province--B--&#38;postal_code=--A--postal_code--B--&#38;country_code=--A--country_code--B--&#38;gender=--A--gender--B--&#38;date_of_birth=--A--date_of_birth--B--&#38;alt_phone=--A--alt_phone--B--&#38;email=--A--email--B--&#38;security_phrase=--A--security_phrase--B--&#38;comments=--A--comments--B--&#38;user=--A--user--B--&#38;campaign=--A--campaign--B--&#38;phone_login=--A--phone_login--B--&#38;fronter=--A--fronter--B--&#38;closer=--A--user--B--&#38;group=--A--group--B--&#38;channel_group=--A--group--B--&#38;SQLdate=--A--SQLdate--B--&#38;epoch=--A--epoch--B--&#38;uniqueid=--A--uniqueid--B--&#38;customer_zap_channel=--A--customer_zap_channel--B--&#38;server_ip=--A--server_ip--B--&#38;SIPexten=--A--SIPexten--B--&#38;session_id=--A--session_id--B--&#38;dialed_number=--A--dialed_number--B--&#38;dialed_label=--A--dialed_label--B--&#38;rank=--A--rank--B--&#38;owner=--A--owner--B--&#38;phone=--A--phone--B--&#38;camp_script=--A--camp_script--B--&#38;in_script=--A--in_script--B--&#38;script_width=--A--script_width--B--&#38;script_height=--A--script_height--B--&#38;recording_filename=--A--recording_filename--B--&#38;recording_id=--A--recording_id--B--&#38;user_custom_one=--A--user_custom_one--B--&#38;user_custom_two=--A--user_custom_two--B--&#38;user_custom_three=--A--user_custom_three--B--&#38;user_custom_four=--A--user_custom_four--B--&#38;user_custom_five=--A--user_custom_five--B--&#38;preset_number_a=--A--preset_number_a--B--&#38;preset_number_b=--A--preset_number_b--B--&#38;preset_number_c=--A--preset_number_c--B--&#38;preset_number_d=--A--preset_number_d--B--&#38;preset_number_e=--A--preset_number_e--B--&#38;preset_number_f=--A--preset_number_f--B--&#38;preset_dtmf_a=--A--preset_dtmf_a--B--&#38;preset_dtmf_b=--A--preset_dtmf_b--B--&#38;did_id=--A--did_id--B--&#38;did_extension=--A--did_extension--B--&#38;did_pattern=--A--did_pattern--B--&#38;did_description=--A--did_description--B--&#38;closecallid=--A--closecallid--B--&#38;xfercallid=--A--xfercallid--B--&#38;agent_log_id=--A--agent_log_id--B--&#38;entry_list_id=--A--entry_list_id--B--&#38;call_id=--A--call_id--B--&&#38;user_group=--A--user_group--B--&&#38;" style="width:580;height:290;background-color:transparent;" scrolling="auto" frameborder="0" allowtransparency="true" id="popupFrame" name="popupFrame" width="460" height="290" STYLE="z-index:17"&#62;
&#60;/iframe&#62;
</DIV>
<BR>
Você também pode usar um IGNORENOSCROLL variável especial para forçar as barras de rolagem na guia script mesmo se você estiver usando um iframe dentro dela.

<BR>
<A NAME="scripts-active">
<BR>
<B>Ativo -</B> Isto determina se o script pode ser selecionado para ser usado na campanha.





<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LEAD_FILTROS TABELA</FONT></B><BR><BR>
	<A NAME="lead_filters-lead_filter_id">
	<BR>
	<B>Filter ID -</B> Este é o nome abreviado de um filtro de chumbo. Isso precisa ser um identificador exclusivo. Não utilize espaços ou pontuação para este campo. máximo 10 caracteres, mínimo de 2 caracteres.

	<BR>
	<A NAME="lead_filters-lead_filter_name">
	<B>Nome do Filtro -</B> É um nome mais descritivo para o filtro. É um resumo da função do filtro. Máximo de 30 caracteres, mínimo de 2 caracteres.

	<BR>
	<A NAME="lead_filters-lead_filter_comments">
	<B>Filter Comentários -</B> Isto é onde você pode colocar comentários em um filtro, como chamadas de tudo-da Califórnia leva-. máximo 255 caracteres, mínimo de 2 caracteres.

	<BR>
	<A NAME="lead_filters-user_group">
	<BR>
	<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

	<BR>
	<A NAME="lead_filters-lead_filter_sql">
	<B>Filtro SQL -</B> É onde colocamos o fragmento da consulta SQL que você deseja usar para filtrar. Não inicie nem termine com AND, isso será adicionado pelo script do hopper automaticamente. Um exemplo de consulta SQL que funcionaría é: called_count \> 4 and called_count \<8 .
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>HORÁRIOS DE CHAMADA TABELA</FONT></B><BR><BR>
<A NAME="call_times-call_time_id">
<BR>
<B>ID do Horário de Cham. -</B> Este é o nome abreviado de uma definição de Call Time. Isso precisa ser um identificador exclusivo. Não utilize espaços ou pontuação para este campo. máximo 10 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="call_times-call_time_name">
<B>Nome do Horário de Chamada -</B> Nome mais descritivo da definição de Horário de Chamada. É um resumo do Horário de Chamada. Máximo de 30 caracteres e mínimo de 2 caracteres.

<BR>
<A NAME="call_times-call_time_comments">
<B>Comentários do Horário de cham. -</B> Isto é onde você pode colocar comentários para uma definição de Call Time como-dez horas - quatro horas com estado chamada extra-restrições. máximo 255 caracteres.

<BR>
<A NAME="call_times-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="call_times-ct_default_start">
<B>Horário de Início e Fim Padrão -</B> Este é o padrão de horário no qual chamadas são permitidas se o dia de início na semana não estiver definido. 0 é meia noite. Para evitar chamadas a qualquer horário, coloque 2400 no início e 2400 no fim. Para permitir chamadas nas 24 horas do dia, coloque 0 e 2400 nestes campos. Para somente de entrada, você também pode definir o tempo de chamada de parada maior do que 2400 se quiser que o tempo de chamada para ir além da meia-noite. Então, se você quiser que o seu tempo de chamada para executar a partir de 6 da manhã até as 2 da manhã do dia seguinte, você colocaria 0600 como o horário de início e 2600 como o tempo de paragem.

<BR>
<A NAME="call_times-ct_sunday_start">
<B>Horários de Início e Fim em dias Úteis -</B> Estes são os horários por dia que podem ser configurados para chamada. As mesmas regras se aplicam conforme os horários de início e fim.

<BR>
<A NAME="call_times-default_afterhours_filename_override">
<B>After Hours Override Filename -</B> Estes campos permitem substituir a mensagem Horas depois para os grupos de entrada, se for configurado para algo. O padrão é vazio.

<BR>
<A NAME="call_times-ct_state_call_times">
<B>Definição de Horários por Estado -</B> Esta é a lista de horários específica por Estado que deve ser seguida por esta definição de horário.

<BR>
<A NAME="call_times-state_call_time_state">
<B>Estado da definição de horário -</B> Código de duas letras para o estado ao qual esta definição foi configurada. Para que funcione corretamente o horário local da campanha deve ter selecionado o horário local deste estado e os registros de clientes devem ter as mesmas letras de estado.

<A NAME="call_times-holiday_id">
<BR>
<B>Férias ID -</B> Este é o nome abreviado de uma definição de férias. Isso precisa ser um identificador exclusivo. Não use espaços ou pontuação para este campo. máximo de 30 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="call_times-holiday_name">
<B>Nome do feriado -</B> Este é um nome mais descritivo da definição de férias. Este é um breve resumo da definição de férias. máximo de 100 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="call_times-holiday_comments">
<B>Férias Comentários -</B> This is where you can place comments for a Holiday Definition such as -10am to 4pm boxing day restrictions-.  max 255 characters.

<BR>
<A NAME="call_times-holiday_date">
<B>Feriado Data -</B> Esta é a data do feriado.

<BR>
<A NAME="call_times-holiday_status">
<B>Férias Estado -</B> Este é o estado da entrada de férias. Estado ACTIVE significa que o feriado será ativado na data do feriado. Estado Inativo significa que o feriado será ignorada até mesmo na data do feriado. Significa que o feriado passou sua data do feriado expirado. O padrão é INATIVOS.




<BR><BR><BR><BR>

<B><FONT SIZE=3>SHIFTS TABELA</FONT></B><BR><BR>
<A NAME="shifts-shift_id">
<BR>
<B>Shift ID -</B> Este é o nome abreviado de uma definição de deslocamento do sistema. Isso precisa ser um identificador exclusivo. Não utilize espaços ou pontuação para este campo. máximo 20 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="shifts-shift_name">
<B>Nome do Turno - </B> Este é um nome mais descritivo da definição do turno. É um resumo da definição do turno. Máximo de 50 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="shifts-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="shifts-shift_start_time">
<B>Início do Turno- </B> É o horário que o turno da campanha começa. Deve ter apenas números, 9:30 seria apenas 0930 e 17:00 seria 1700.

<BR>
<A NAME="shifts-shift_length">
<B>Duração do Turno -</B> Este é o tempo em horas e minutos que dura o turno. 8 horas seria 08:00 e 7 horas e 30 minutos seria 7:30.

<BR>
<A NAME="shifts-shift_weekdays">
<B>Dias do Turno -</B> Nesta sessão você deve escolher os dias da semana em que este turno funciona.

<BR>
<A NAME="shifts-report_option">
<B>Opção Relatório -</B> Esta opção permite que essa mudança específica para aparecer nos relatórios selecionados que suportam esta opção.

<BR>
<A NAME="shifts-report_rank">
<B>Relatório Ranking -</B> Esta opção permite que você classifique as mudanças nos relatórios selecionados que suportam esta opção.




<BR><BR><BR><BR>
<A NAME="audio_store">
<B>Audio de la tienda -</B> Este utilitário permite que você faça o upload de arquivos de áudio para o servidor web, para que possam ser distribuídos para todos os servidores em um cluster de sistema multi-servidor. Uma nota importante, apenas dois tipos de arquivos de áudio irá funcionar,. Wav que são PCM Mono 16bit e 8k. Arquivos GSM que são 8k 8bit. Por favor verifique se os arquivos estão devidamente formatado antes de enviá-los aqui.



<BR><BR><BR><BR>

<B><FONT SIZE=3>MUSIC_ON_HOLD TABELA</FONT></B><BR><BR>
<A NAME="music_on_hold-moh_id">
<BR>
<B>Música en espera de identificación -</B> Este es el nombre corto de una música en espera de entrada. Esto debe ser un identificador único. No utilice espacios ni puntuacion para este campo. 100 caracteres como máximo, mínimo de 2 caracteres de.

<BR>
<A NAME="music_on_hold-moh_name">
<B>Música en espera Nombre -</B> Este é um nome mais descritivo do Music On entrada Hold. Este é um breve resumo da música em contexto Espera e vai mostrar como um comentário no arquivo conf musiconhold. máximo 255 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="music_on_hold-active">
<B>Ativo -</B> Esta opción le permite definir la música en espera la entrada en activo o inactivo. Inactivo eliminará la entrada de los archivos de conf.

<BR>
<A NAME="music_on_hold-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="music_on_hold-random">
<B>Orden aleatorio -</B> Esta opción le permite definir la reproducción de los archivos de audio en un orden aleatorio. Si se establece a N, entonces el orden definido se utilizará.

<BR>
<A NAME="music_on_hold-filename">
<B>Filename -</B> Para añadir un nuevo archivo de audio a una música en espera de entrada el primer archivo debe estar en la tienda de audio, puede seleccionar el archivo y haga clic en Enviar para añadirlo a la lista de archivos. Música en espera se actualiza una vez por minuto si se han producido cambios realizados. Cualquier archivo que no figuran en la música en la entrada de reserva que están presentes en la música en la carpeta celebrar serán eliminados.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TTS_PROMPTS TABELA</FONT></B><BR><BR>
<A NAME="tts_prompts-tts_id">
<BR>
<B>TTS ID -</B> Este es el nombre corto de una entrada de TTS. Esto debe ser un identificador único. No utilice espacios ni puntuacion para este campo. caracteres como máximo 50, mínimo de 2 caracteres.

<BR>
<A NAME="tts_prompts-tts_name">
<B>Nombre TTS -</B> Este es un nombre más descriptivo de la entrada TTS. Este es un breve resumen de la definición de TTS. 100 caracteres como máximo, mínimo de 2 caracteres de.

<BR>
<A NAME="tts_prompts-active">
<B>Ativo -</B> Esta opción le permite establecer la entrada de TTS a activos o inactivos.

<BR>
<A NAME="tts_prompts-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="tts_prompts-tts_voice">
<B>Voz -</B> Isto é, onde poderá definir a voz a ser usado na geração de TTS. O padrão é Allison-8kHz.

<BR>
<A NAME="tts_prompts-tts_text">
<B>TTS Texto -</B> Este es el texto real de campo de datos de voz que se envía a Cepstral para la creación del archivo de audio que deben desempeñar para el cliente. Usted puede utilizar Speech Syntesis Markup Language-SSML-en este campo, por ejemplo,, &lt;break time='1000ms'/&gt; for a 1 second break. You can also use several variables such as first name, last name and title as system variables just like you do in a Script: --A--first_name--B--. Se você tiver estáticos arquivos de áudio que você deseja usar com base no valor de um dos campos que você pode usar as também com C e D etiquetas. Os nomes dos arquivos devem estar em letras minúsculas e devem ser 8k 16bit arquivos PCM WAV. O nome do campo deve ser o mesmo, mas sem o wav. No nome do arquivo. Por exemplo - C ---- A - Endereço3 - B ---- D - em primeiro lugar encontrar o valor para Endereço3, então seria tentar encontrar um arquivo de áudio que correspondem ao valor de colocá-lo no prompt. Aqui está uma lista das variáveis ​​disponíveis: lead_id, entry_date, modify_date, status, user, vendor_lead_code, source_id, list_id, phone_number, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_count, last_local_call_time, rank, owner




<BR><BR><BR><BR>

<B><FONT SIZE=3>VOICEMAIL TABELA</FONT></B><BR><BR>
<A NAME="voicemail-voicemail_id">
<BR>
<B>ID de correo de voz -</B> Este es el identificador de todos los números de este buzón. Esto no debe ser un duplicado de un mensaje de voz existentes de identificación o la identificación del correo de voz de un teléfono en el sistema, mínimo de 2 caracteres.

<BR>
<A NAME="voicemail-fullname">
<B>Name -</B> Este es el nombre asociado a este buzón de voz. 100 caracteres como máximo, mínimo de 2 caracteres de.

<BR>
<A NAME="voicemail-pass">
<B>Password -</B> Esta es la contraseña que se utiliza para acceder al buzón de voz cuando se marca para revisar los mensajes máximo 10 caracteres, mínimo de 2 caracteres.

<BR>
<A NAME="voicemail-active">
<B>Ativo -</B> Esta opción le permite configurar la casilla de correo de voz activo o inactivo. Si el cuadro está inactivo no se puede dejar mensajes en él y no se puede revisar los mensajes en él.

<BR>
<A NAME="voicemail-email">
<B>Email -</B> Este ajuste opcional le permite tener los mensajes de correo de voz enviado a una cuenta de correo electrónico, si su sistema está configurado para enviar correo electrónico. Si este campo está vacío, entonces no los correos electrónicos se envían.

<BR>
<A NAME="voicemail-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="voicemail-delete_vm_after_email">
<B>Eliminar correo de voz después del email -</B> Este ajuste opcional le permite tener los mensajes de correo de voz se elimina del sistema después de haber sido enviado por correo electrónico a cabo. Por defecto es n.

<BR>
<A NAME="voicemail-voicemail_greeting">
<B>Saudação de correio de voz -</B> Esta configuração opcional permite que você defina um arquivo de áudio de saudação de correio de voz a partir da loja de áudio. O padrão é em branco.

<BR>
<A NAME="voicemail-voicemail_timezone">
<B>Zona Correio de Voz -</B> Esta configuração permite que você defina a zona que esta caixa de correio de voz será definido para quando o tempo é registrado para uma mensagem. O padrão é definido nas configurações do sistema.

<BR>
<A NAME="voicemail-voicemail_options">
<B>Opções de correio de voz -</B> Esta configuração opcional lhe permite definir as configurações de correio de voz adicionais. É recomendável que você deixe este campo em branco se você não sabe o que está fazendo.

<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>

	<BR><BR><BR><BR>

	<B><FONT SIZE=3>LIST LOADER FUNCTIONALITY</FONT></B><BR><BR>
	<A NAME="list_loader">
	<BR>
	O carregador de chumbo básico baseado na web é projetado simplesmente para tomar um arquivo de liderança - até 8MB de tamanho - que seja guia ou tubo delimitado e carregá-lo na tabela de lista. O carregador de chumbo permite a escolha de campo e TXT-texto simples, CSV-Comma Separated Values ​​e formatos de arquivo XLS-Excel. O carregador de chumbo não faz a validação de dados, mas não permite que você verifique se há duplicatas em si, dentro da campanha ou dentro de todo o sistema. Além disso, certifique-se de que você criou na lista que estas ligações devem estar sob a fim de que você possa usá-los. Aqui está uma lista dos campos em sua devida ordem para os arquivos de chumbo:
		<OL>
		<LI>Código do Vendedor no Registro - é mostrado no campo Vendor ID na tela do agente
		<LI>Código Fonte - uso interno, somente para admins e DBAs
		<LI>ID da Lista - número da lista em que esses registros vão ser carregados
		<LI>Phone Code - the prefix for the phone number - 1 for US, 44 for UK, 61 for AUS, etc
		<LI>Número do telefone - deve ter pelo menos 8 digitos
		<LI>Título - título do cliente(Sr. Sra. Srta., etc...)
		<LI>Nome
		<LI>Inicial do Meio
		<LI>Sobrenome
		<LI>Endereço, linha 1
		<LI>Endereço, linha 2
		<LI>Endereço, linha 3
		<LI>Cidade
		<LI>Estado - limitado a 2 caracteres
		<LI>Província
		<LI>CEP
		<LI>País
		<LI>Sexo
		<LI>Data de Nascimento
		<LI>Número Alternativo
		<LI>Endereço de Email
		<LI>Frase de Segurança
		<LI>Comentários
		<LI>Rank
		<LI>Owner
		</OL>

	<BR>ATENÇÃO: A funcionalidade de carregar registros usando arquivos EXCEL é permitida por uma série de scripts e precisa estar devidamente configurada no arquivo /etc/astguiclient.conf no servidor web. Também, alguns módulos perl devem ser carregados para que funcione, OLE-Storage_Lite e Spreadsheet-ParseExcel. Você pode verificar erros de tempo de execução neles olhando o arquivo de log do apache. Também, para verificação de duplicados na lista da campanha, a lista com os novos registros precisa existir no sistema antes de iniciar a carga.

	<BR>
	<A NAME="list_loader-duplicate_check">
	<BR>
	<B>Duplicar Verificação - </B>As opções duplicadas permitem verificar se há entradas duplicadas como você carregar os cabos para o sistema. Você pode selecionar para verificar se há duplicatas dentro só a mesma lista, apenas as mesmas listas de campanha ou dentro de todo o sistema. Se você tiver escolhido um método de verificação de duplicata, você também pode selecionar opcionalmente os únicos estados específicos que você deseja duplicar cheque contra.

	<BR>
	<A NAME="list_loader-file_layout">
	<BR>
	<B>File layout - </B>O layout do arquivo que você está carregando. "Formato padrão" usa o formato de arquivo padrão pré-definido. "Layout Personalizado" permite que o usuário defina o layout do arquivo de si mesmos. "Template personalizado" é um híbrido das duas opções anteriores, o que permite ao usuário utilizar um formato personalizado que tenham definido previamente e salvou usando o modelo personalizado Criador.

	<BR>
	<A NAME="list_loader-template_id">
	<BR>
	<B>ID do Template -</B> If the user has selected "Layout Customizado" from the "File layout" options, then this the the template the lead loader will use.  It will also override the selected list ID with the list ID that was assigned to the selected template when it was created.



	<BR><BR><BR><BR>
	<?php
	}
?>





<B><FONT SIZE=3>TABELA DE RAMAIS</FONT></B><BR><BR>
<A NAME="phones-extension">
<BR>
<B>Extensão de telefone -</B> Este campo é onde você coloca o nome de telefones como ele aparece para o Asterisk não incluindo o protocolo ou barra no início. Por exemplo: para o telefone SIP SIP/test101 a extensão do telefone seria test101. Além disso, para IAX2 telefones: IAX2/IAXphone1 @ IAXphone1 seria IAXphone1. Para Zap e Dahdi channelbank anexado ou FXS telefones certifique-se de colocar o número do canal completo sem o prefixo: Zap/25-1 seria 25-1. Outra nota, certifique-se de definir o campo de protocolo abaixo corretamente para o seu tipo de telefone.

<BR>
<A NAME="phones-dialplan_number">
<BR>
<B>Numero do plano de discagem -</B> Este campo é para o número que você disca para tocar o ramal. Esse número é definido no arquivo extensions.conf do seu servidor Asterisk

<BR>
<A NAME="phones-voicemail_id">
<BR>
<B>Caixa Postal -</B> Este campo é para a caixa postal de voz para onde vao as menssagens para o usuário deste ramal. Utilizamos isto para verificar as mensagens e para o usuário utilizar o botão VOICEMAIL no aplicativo astGUIclient.

<BR>
<A NAME="phones-voicemail_timezone">
<B>Zona Correio de Voz -</B> Esta configuração permite que você defina a zona que esta caixa de correio de voz será definido para quando o tempo é registrado para uma mensagem. O padrão é definido nas configurações do sistema.

<BR>
<A NAME="phones-voicemail_options">
<B>Opções de correio de voz -</B> Esta configuração opcional lhe permite definir as configurações de correio de voz adicionais. É recomendável que você deixe este campo em branco se você não sabe o que está fazendo.

<BR>
<A NAME="phones-outbound_cid">
<BR>
<B>Identificador de Saída -</B> Este campo é onde deve ser colocado o número que você quer que saia no identificador para as ligações de saída feitas pelo astguiclient. Isto não funciona em linhas RTB(non-PRI) T1/E1.

<BR>
<A NAME="phones-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="phones-phone_ip">
<BR>
<B>Endereço IP do ramal -</B> Este campo é para o endereço IP do ramal se for um ramal VOIP. Este campo é opcional

<BR>
<A NAME="phones-computer_ip">
<BR>
<B>Endereço IP do computador -</B> Este campo é para o endereço IP do computador do usuário. Este campo é opcional

<BR>
<A NAME="phones-server_ip">
<BR>
<B>IP do Servidor -</B> Este menu é onde você escolhe em qual servidor está o ramal ativo.

<BR>
<A NAME="phones-login">
<BR>
<B>Entrar Tela agente -</B> O login usado para o usuário do telefone para acessar os aplicativos do cliente, como a tela do agente.

<BR>
<A NAME="phones-pass">
<BR>
<B>Login Senha -</B>  A senha usada para o usuário do telefone para acessar as aplicações baseadas na web do cliente. IMPORTANTE, esta é a senha apenas para o agente de login do telefone interface web, para mudar o sip.conf ou senha iax.conf, ou secreta, para este dispositivo de telefone que você precisa para modificar a senha de inscrição no próximo campo.

<BR>
<A NAME="phones-conf_secret">
<BR>
<B>Registo Senha -</B> Este es el secreto o la contraseña, por el teléfono de la SIP IAX o auto-generados conf para este teléfono. Límite es el tablero de 20 caracteres alfanuméricos y de subrayado aceptado. El valor predeterminado es la prueba. Anteriormente chamado Segredo arquivo conf. Uma palavra-passe registo forte deve ser de pelo menos 8 caracteres de comprimento e têm minúsculas e letras maiúsculas, bem como, pelo menos, um número.

<BR>
<A NAME="phones-is_webphone">
<BR>
<B>Definir como Webphone -</B>  Definir esta opção como Y tentará carregar um telefone baseado na web quando o agente registra em sua tela de agente. O padrão é N. A opção Y_API_LAUNCH pode ser usado com a API para lançar o agente webphone numa janela separada ou Iframe.

<BR>
<A NAME="phones-webphone_dialpad">
<BR>
<B>Webphone Dialpad -</B>  Esta configuração permite ativar ou desativar o teclado para este webphone. O padrão é Y para habilitado. TOGGLE permitirá ao usuário visualizar e ocultar o teclado, clicando em um link. Este recurso não está disponível em todas as versões webphone. TOGGLE_OFF será o padrão para não mostrar o teclado na primeira carga, mas permitirá que o usuário para mostrar o teclado, clicando no link dialpad.

<BR>
<A NAME="phones-webphone_auto_answer">
<BR>
<B>Webphone Auto-Resposta -</B>  Esta configuração permite que o telefone web para ser configurado para atender automaticamente as chamadas que chegam por defini-la como Y, ou ter o toque de chamadas por defini-la como padrão é N. Y.

<BR>
<A NAME="phones-use_external_server_ip">
<BR>
<B>Use IP do servidor externo -</B>  Se usar como um telefone web, você pode definir isso para Y para utilizar os servidores de IP externo para se registar em vez do IP do servidor. O padrão é vazio.

<BR>
<A NAME="phones-status">
<BR>
<B>Status -</B> O status do ramal no sistema, ATIVO e ADMIN permiten que os clientes GUI trabalhem. ADMIN permite o acceso ao Website de administração. Os outros status não permitem o acceso ao GUI ou ao Website de Admin.

<BR>
<A NAME="phones-active">
<BR>
<B>Conta ativa -</B> Se o ramal está ativado será incluído na lista do cliente GUI.

<BR>
<A NAME="phones-phone_type">
<BR>
<B>Tipo de Ramal -</B> Somente para informação administrativa.

<BR>
<A NAME="phones-fullname">
<BR>
<B>Nome Completo -</B> Usado pelo GUIclient na lista de ramais ativos.

<BR>
<A NAME="phones-company">
<BR>
<B>Empresa -</B> Somente para informação administrativa.

<BR>
<A NAME="phones-email">
<BR>
<B>Email do Ramal - </B> Endereço de email associado ao ramal. Isto pode ser usado para configurações de VoiceMail.

<BR>
<A NAME="phones-delete_vm_after_email">
<B>Eliminar correo de voz después del email -</B> Este ajuste opcional le permite tener los mensajes de correo de voz se elimina del sistema después de haber sido enviado por correo electrónico a cabo. Por defecto es n.

<BR>
<A NAME="phones-voicemail_greeting">
<B>Saudação de correio de voz -</B> Esta configuração opcional permite que você defina um arquivo de áudio de saudação de correio de voz a partir da loja de áudio. O padrão é em branco.

<BR>
<A NAME="phones-voicemail_instructions">
<B>Instruções de correio de voz -</B> Esta configuração permite que você defina se as instruções de voz vai jogar após a saudação do correio de voz quando uma chamada toca no ramal do agente e os tempos para o correio de voz. O padrão é Y.

<BR>
<A NAME="phones-picture">
<BR>
<B>Foto -</B> Ainda não implementado.

<BR>
<A NAME="phones-messages">
<BR>
<B>Novas Mensagens -</B> Número de mensagens novas na conta de correio de voz para esse ramal no servidor Asterisk.

<BR>
<A NAME="phones-old_messages">
<BR>
<B>Mensagens Antigas -</B> .

<BR>
<A NAME="phones-protocol">
<BR>
<B>Protocolo Client -</B> O protocolo que o ramal utiliza para se conectar com o servidor Asterisk: SIP, IAX2, Zap. Também, para os números Externos ou número de discagem rápida que você deseja listar como ramais.

<BR>
<A NAME="phones-local_gmt">
<BR>
<B>GMT Local -</B> A diferença de Greenwich, ou o tempo de ZULU onde o telefone está localizado. NÃO AJUSTE PARA horário de verão. Isto é usado pela campanha para exibir com precisão o tempo de sistema e tempo do cliente, bem como registrar com precisão quando os eventos acontecem.

<BR>
<A NAME="phones-phone_ring_timeout">
<BR>
<B>Teléfono Anillo de tiempo de espera -</B> Esta es la cantidad de tiempo, en segundos, que el teléfono sonará en el dialplan antes de enviar la llamada al buzón de voz. El valor predeterminado es 60 segundos.

<BR>
<A NAME="phones-on_hook_agent">
<BR>
<B>Em Hook-Agent Login -</B> Esta opção só é usada para chamadas de entrada que vão a um agente conectado com este telefone. Este recurso irá chamar o agente e não vai mandar o cliente para a sessão de agentes até a linha é atendida. Padrão é n para deficientes físicos.

<BR>
<A NAME="phones-ASTmgrUSERNAME">
<BR>
<B>Login de Gerenciamento-</B> Este é o login que o client GUI para esse ramal usará para acessar o Banco de Dados onde os dados do servidor residem .

<BR>
<A NAME="phones-ASTmgrSECRET">
<BR>
<B>Senha de Gerenciamento -</B> Esta é a senha que o client GUI para esse ramal usará para acessar o Banco de Dados onde os dados do servidor residem.

<BR>
<A NAME="phones-login_user">
<BR>
<B>Default Usuário Agent -</B> Isso é para colocar um valor padrão no campo de usuário do agente sempre que este usuário do telefone abre o aplicativo cliente. Deixe em branco para nenhum usuário.

<BR>
<A NAME="phones-login_pass">
<BR>
<B>Agente Padrão passagem -</B> Este é colocar um valor padrão no campo de senha sempre que este agente do usuário do telefone abre o aplicativo cliente. Deixe em branco para não passar.

<BR>
<A NAME="phones-login_campaign">
<BR>
<B>Campanha Agente Padrão -</B> Isso é para colocar um valor padrão no campo campanha tela agente sempre que o usuário do telefone abre o aplicativo cliente. Deixe em branco para nenhuma campanha.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Exten de Estacionamento -</B> Esta é a extensão de estacionamento padrao para o aplicativo client. Verifique se a extensão funciona antes de alterar..

<BR>
<A NAME="phones-conf_on_extension">
<BR>
<B>Exten de Conferência -</B> Esta é a extensão padrao para estacionamento da conferência para o aplicativo client. Verifique se a extensão funciona antes de alterar.

<BR>
<A NAME="phones-park_on_extension">
<BR>
<B>Agente Parque Exten -</B> Esta é a extensão de estacionamento padrão para aplicativos cliente. Verifique se um diferente funciona antes de alterar este.

<BR>
<A NAME="phones-park_on_filename">
<BR>
<B>Arquivo Agente Parque -</B> Este é o nome do arquivo de extensão parque tela agente padrão para os aplicativos cliente. Verifique se um diferente funciona antes de mudar isso. limitada a 10 caracteres.

<BR>
<A NAME="phones-monitor_prefix">
<BR>
<B>Prefixo do monitor -</B> Este é o prefixo do plano de discagem para monitorar canais automaticamente dentro do aplicativo astGUIclient. Só altere de acordo com os registros de ZapBarge do arquivo extensions.conf.

<BR>
<A NAME="phones-recording_exten">
<BR>
<B>Exten de Gravação -</B> Esta é a extensão do plano de discagem que irá entrar na sala de conferências e gravar a conversa. Normalmente dura por volta de uma hora se nao for cancelada. Verifique no arquivo extensions.conf antes de alterar.

<BR>
<A NAME="phones-voicemail_exten">
<BR>
<B>Exten principal de VMAIL-</B> Esta é a extensão do plano de discagem que acessa o voicemail. Verifique o arquivo extensions.conf antes de alterar.

<BR>
<A NAME="phones-voicemail_dump_exten">
<BR>
<B>Descarga Exten de VMAIL -</B> Esta é a extensão do plano de discagem usada para deixar mensagens no correio de voz.

<BR>
<A NAME="phones-voicemail_dump_exten_no_inst">
<BR>
<B>Vmail Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="phones-ext_context">
<BR>
<B>Contexto do Exten -</B> Este é o contexto do plano de discagem que a tela do agente, principalmente usa. Supõe-se que todos os números discados pelos aplicativos clientes estão usando este contexto, por isso é uma boa idéia para garantir que este é o contexto mais amplo possível. verificar com o arquivo extensions.conf antes de mudar. padrão é padrão.

<BR>
<A NAME="phones-phone_context">
<BR>
<B>Teléfono Contexto -</B> Este é o contexto do plano de discagem que este telefone vai usar para discar. Se você estiver executando um call center e você não quer que seus agentes para ser capaz de discar de fora da tela applicaiton agente por exemplo, então você deve definir esse campo para um contexto dialplan que não existe, algo como agente nodial. padrão é padrão.

<BR>
<A NAME="phones-codecs_list">
<BR>
<B>Codecs admitidos -</B> Você pode definir uma lista delimitada por vírgula de codecs para ser definido como os codecs padrão para este telefone. Opções para incluir codecs ulaw, alaw, gsm, g729, Speex, G722, G723, G726, iLBC, ... Alguns desses codecs podem não estar disponíveis em seu sistema, como o G729 ou G726. Se o campo estiver vazio, então os codecs padrão do sistema ou a entrada de telefone acima este será utilizado para os codecs permitidas. O padrão é vazio.

<BR>
<A NAME="phones-codecs_with_template">
<BR>
<B>Codecs permitidos com Template-</B> Definir essa opção como 1 irá incluir os codecs definidos acima, mesmo se um modelo de arquivo conf é usado. O padrão é 0.

<BR>
<A NAME="phones-dtmf_send_extension">
<BR>
<B>Canal de envio de DTMF -</B> Este é canal usado para enviar tons DTMF nas conferencias meetme do aplicativo client. Verifique a exten e o contexto no arquivo extensions.conf.

<BR>
<A NAME="phones-call_out_number_group">
<BR>
<B>Grupo de Saída -</B> Este é o grupo de canais em que as chamadas saintes são feitas. Existem algumas rotinas no aplicativo cliente que usam isso. Para canais Zap você deve fazer algo como Zap/g2, para trunks IAX2 voce deve usar o prefixo completo como: IAX2/VICItest1:secret@10.10.10.15:4569. Verifique os trunks no arquivo extensions.conf, normalmente é o que voce definiu na variavel global TRUNK no inicio do arquivo.

<BR>
<A NAME="phones-client_browser">
<BR>
<B>Local do Browser -</B> Isto se aplica apenas a clientes UNIX/LINUX, é o caminho absoluto para os browsers Mozilla ou Firefox na maquina. Verifique isso executando-o manualmente.

<BR>
<A NAME="phones-install_directory">
<BR>
<B>Caminho de Instalação -</B> Não é mais usado.

<BR>
<A NAME="phones-local_web_callerID_URL">
<BR>
<B>URL do Identificador -</B> 

<BR>
<A NAME="phones-agent_web_URL">
<BR>
<B>URL Agente Padrão -</B> Este é o endereço da Web da página usada para fazer consultas de formulário Web agente personalizado. endereço teste padrão é definido no esquema de banco de dados.

<BR>
<A NAME="phones-AGI_call_logging_enabled">
<BR>
<B>Call Logging -</B> This is set to true if the call_log step is in place in the extensions.conf file for all outbound and hang up 'h' extensions to log all calls. This should always be 1 because it is manditory for many of the system features to work properly.

<BR>
<A NAME="phones-user_switching_enabled">
<BR>
<B>Conmutación del usuario -</B> fije para verdad para permitir que el usuario cambie a otra cuenta del usuario. NOTA: Si los interruptores del usuario ellos pueden iniciar la grabación en la conversación de teléfono del nuevo usuario

<BR>
<A NAME="phones-conferencing_enabled">
<BR>
<B>Conferencias -</B> Configurado como true, permite o usuário iniciar conferências de até seis telefones externos.

<BR>
<A NAME="phones-admin_hangup_enabled">
<BR>
<B>Hangup do Admin -</B> Configurado como true permite que o usuário desligue qualquer linha através do astGUIclient. Uma boa ideia é somente permitir isso para usuários Admin.

<BR>
<A NAME="phones-admin_hijack_enabled">
<BR>
<B>Sequestro do Admin -</B> Configure como true para permitir que o usuário consiga pegar qualquer linha e transferir para seu ramal através do astGUIclient. Uma boa ideia é permitir somente para o usuário Admin. Mas é muito útil para gerentes.

<BR>
<A NAME="phones-admin_monitor_enabled">
<BR>
<B>Monitoramento Admin -</B> Configure como true para permitir que o usuário consiga pegar qualquer linha e transferir para seu ramal através do astGUIclient. Uma boa ideia é permitir somente para o usuário Admin. Mas é muito útil para gerentes.

<BR>
<A NAME="phones-call_parking_enabled">
<BR>
<B>Estacionamento de chamadas -</B> Se configurado como true permite o usuário estacionar as chamadas pelo astGUIclient para que sejam capturadas por outros usuários do sistema. Chamadas ficam estacionadas por até meia hora e depois são desligadas. Normalmente é habilitado para todos.

<BR>
<A NAME="phones-updater_check_enabled">
<BR>
<B>Verif. de Updater -</B> Se configurado como true, mostra um aviso em popup que o tempo do atualizador nao mudou em 20 segundos. Útil para usuários Admin.

<BR>
<A NAME="phones-AFLogging_enabled">
<BR>
<B>Registro AF -</B> Se configurado como true, grava várias ações do uso do astGUIclient para um arquivo texto no computador do usuário.

<BR>
<A NAME="phones-QUEUE_ACTION_enabled">
<BR>
<B>Permitir Filas -</B> Defina para true para que aplicativos cliente usam o sistema Asterisk Central Fila. Necessário para que o sistema funcione e recomendado para todos os telefones.

<BR>
<A NAME="phones-CallerID_popup_enabled">
<BR>
<B>Popup do CallerID -</B> Configurado como true permite que números configurados no arquivo extensions.conf enviem telas popup de Identificação (CallerID) para os usuários.

<BR>
<A NAME="phones-voicemail_button_enabled">
<BR>
<B>Botão do VMail -</B> Configurado como true irá mostrar o botao do VOICEMAIL (correio de voz) e o contador de mensagens no astGUIclient.

<BR>
<A NAME="phones-enable_fast_refresh">
<BR>
<B>Atualização Rápida -</B> Configure como true para habilitar uma nova velocidade de atualização das informações sobre a chamada no astGUIclient. A velocidade padrão quando desabilitado é de 1000ms ou 1 segundo. Pode aumentar a carga no sistema se esse tempo for reduzido.

<BR>
<A NAME="phones-fast_refresh_rate">
<BR>
<B>Tempo de Atualização Rápida -</B> em milisegundos. Só é usado se a opção "Atualização Rápida" estiver habilitada. O valor padrão desabilitado é 1000ms, 1 segundo. Pode aumentar a carga do sistema se esse valor for reduzido.

<BR>
<A NAME="phones-enable_persistant_mysql">
<BR>
<B>MySQL Permanente-</B> Se estiver habilitada, a conexão do astGUIclient permanecerá ativa ao invés de conectar a cada segundo. Útil se você tiver um tempo de atualização curto. Pode aumentar a quantidade de conexões no servidor MySQL.

<BR>
<A NAME="phones-auto_dial_next_number">
<BR>
<B>Auto Discar próximo número -</B> Se ativado tela o agente irá discar o próximo número da lista automaticamente após a alienação de uma chamada a menos que eles selecionaram para PAUSA AGENTE discagem na tela a disposição.

<BR>
<A NAME="phones-VDstop_rec_after_each_call">
<BR>
<B>Parar de gravar após cada chamada -</B> Se ativado a tela do agente vai parar o que está acontecendo a gravação após cada chamada foi dispositioned. Útil se você está fazendo um monte de gravação ou você está usando um formulário web para acionar a gravação. 

<BR>
<A NAME="phones-enable_sipsak_messages">
<BR>
<B>Enable SIPSAK Messages -</B> Se ativado, o servidor irá enviar mensagens para o telefone SIP para exibir na tela do visor do telefone quando conectado a interface web do agente. Recurso só funciona com telefones SIP e requer aplicação sipsak a ser instalado no servidor web. O padrão é 0.

<BR>
<A NAME="phones-DBX_server">
<BR>
<B>Servidor DBX -</B> O servidor de banco de dados que este usuário deve se conectar.

<BR>
<A NAME="phones-DBX_database">
<BR>
<B>Base de dados DBX -</B> A base de dados MySQL que o usuário deve se conectar. Padrão é asterisk.

<BR>
<A NAME="phones-DBX_user">
<BR>
<B>Usuário do DBX -</B> O login de usuário do MySQL que esse usuário deve usar para conectar. Default é cron.

<BR>
<A NAME="phones-DBX_pass">
<BR>
<B>Senha do DBX -</B> Senha do usuário de conexão do MySQL. Padrão é 1234.

<BR>
<A NAME="phones-DBX_port">
<BR>
<B>Porta DBX -</B> A porta TCP de conexão com o MySQL que o usuário deve usar para conectar. Padrão é 3306.

<BR>
<A NAME="phones-DBY_server">
<BR>
<B>Servidor DBY -</B> O servidor de banco de dados que este usuário deve se conectar. Secundário server, não utilizado atualmente.

<BR>
<A NAME="phones-DBY_database">
<BR>
<B>Base de dados DBY -</B> A base de dados MySQL que o usuário deve se conectar. Padrão é asterisk. Secundário server, não utilizado atualmente.

<BR>
<A NAME="phones-DBY_user">
<BR>
<B>Usuário do DBY -</B> O login de usuário do MySQL que esse usuário deve usar para conectar. Default é cron. Secundário server, não utilizado atualmente.

<BR>
<A NAME="phones-DBY_pass">
<BR>
<B>Senha do DBY -</B> Senha do usuário de conexão do MySQL. Padrao é 1234. Secundário server, não utilizado atualmente.

<BR>
<A NAME="phones-DBY_port">
<BR>
<B>Porta DBY -</B> A porta TCP de conexão com o MySQL que o usuário deve usar para conectar. Padrao é 3306. Secundário server, não utilizado atualmente.

<BR>
<A NAME="phones-alias_id">
<BR>
<B>ID do Alias - </B> O ID do alias usado para permitir balanceamento de carga entre ramais. Não é permitido espacos ou caracteres especiais. Deve ter entre 2 e 20 caracteres.

<BR>
<A NAME="phones-alias_name">
<BR>
<B>Nome do Alias - </B> O nome usado para descrever o alias do ramal, deve ter entre 2 e 50 caracteres.

<BR>
<A NAME="phones-logins_list">
<BR>
<B>Lista de Logins de Ramais - </B> Uma lista, separada por vírgulas, logins de ramais usados quando o agente está usando ramais do balanceamento de carga. O aplicativo agente irá encontrar o servidor ativo com a menor quantidade de agentes logados e fazer a chamada a partir daquele servidor para o agente no login.

<BR>
<A NAME="phones-template_id">
<BR>
<B>ID do Template - </B> Este é o ID do Template de arquivo conf que este ramal irá usar para as configurações do Asterisk. O padrão é --NONE--.

<BR>
<A NAME="phones-conf_override">
<BR>
<B>Conf Override Settings -</B> If populated, and the ID do Template is set to --NONE-- then the contents of this field are used as the conf file entries for this phone. generate conf files for this phones server must be set to Y for this to work. This field should NOT contain the [extension] line, that will be automatically generated.

<BR>
<A NAME="phones-group_alias_id">
<BR>
<B>ID do Alias -</B> O ID do alias de grupo utilizadas pelos agentes para discar chamadas a partir do interface de agente com diferentes IDs de chamadas. sem espaços ou outros caracteres especiais permitidos. Deve ser entre 2 e 20 caracteres de comprimento.

<BR>
<A NAME="phones-group_alias_name">
<BR>
<B>Nome do Alias de Grupo - </B> O nome usado para descrever um alias de grupo, Deve ter entre 2 e 20 caracteres de comprimento.

<BR>
<A NAME="phones-caller_id_number">
<BR>
<B>Número do CallerID - </B> O Número do Caller ID usado para este alias de grupo. Deve ter apenas dígitos.

<BR>
<A NAME="phones-caller_id_name">
<BR>
<B>Nome do CallerID - </B> O Nome do Caller ID que é enviado com esse Alias de Grupo. Até onde sabemos isso só funciona nos circuitos PRI do Canadá e usando trunk IAX com Asterisk.




<BR><BR><BR><BR>

<B><FONT SIZE=3>TABELA DOS SERVIDORES</FONT></B><BR><BR>
<A NAME="servers-server_id">
<BR>
<B>ID do Servidor -</B> Este campo é onde você configura o nome dos servidores Asterisk, nao precisa ser o domínio oficial, apenas um apelido para identificar o servidor para usuários Admin.

<BR>
<A NAME="servers-server_description">
<BR>
<B>Descrição do Servidor -</B> O campo onde você usa uma pequena descrição para o servidor Asterisk.

<BR>
<A NAME="servers-server_ip">
<BR>
<B>Endereço IP do Servidor -</B> O campo onde você configura o endereço IP do servidor Asterisk.

<BR>
<A NAME="servers-active">
<BR>
<B>Ativo -</B> Configure se o servidor está ativo ou não.

<BR>
<A NAME="servers-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="servers-sysload">
<BR>
<B>Carga do Sistema -</B> Estas duas estatísticas mostram a média de carga de um sistema vezes 100 e o percentual de uso de CPU do servidor, atualizados a cada minuto. A média de carga deve ser em média menor que 100 multiplicado pelo número de núcleos de CPU que seu sistema tem, para melhor performance. O percentual de uso do CPU deve ficar abaixo de 50 para melhor performance.

<BR>
<A NAME="servers-channels_total">
<BR>
<B>Canais Falando -</B> Este campo mostra o número atual de canais ativos no Asterisk que estão falando neste momento. É importante perceber que o número de canais do Asterisk é normalmente muito mais alto do que as chamadas atuais do sistema. Este campo é atualizado a cada minuto.

<BR>
<A NAME="servers-disk_usage">
<BR>
<B>Uso do HD -</B> Este campo irá mostrar o uso de disco para cada partição neste servidor. Este campo é atualizado a cada minuto.

<BR>
<A NAME="servers-system_uptime">
<BR>
<B>Sistema Uptime -</B> Este campo mostrará o funcionamento do sistema de servidor. Este campo só atualiza se configurado para fazê-lo pelo seu administrador.

<BR>
<A NAME="servers-asterisk_version">
<BR>
<B>Versão do Asterisk -</B> Configure a versão do Asterisk que está instalada no servidor. Exemplos: '1.2', '1.0.8', '1.0.7', 'CVS_HEAD', 'REALLY OLD', etc... Isto é necessário pois as versões 1.0.8 e 1.0.9 tem metodos diferentes de controlar canais Local, um erro foi arrumado na CVS v1.0, e precisa ser tratado de forma diferente quando usamos os canais "Local". Também, a versão atual CVS_HEAD e a 1.2 usam managers diferentes e os resultados dos comandos devem ser tratados de forma diferente.

<BR>
<A NAME="servers-max_trunks">
<BR>
<B>Max Trunks -</B> Este campo irá determinar o número máximo de linhas que a auto-dialer tentará chamar neste servidor. Se você quer dedicar dois completos PRI T1s até Outbound discar em um servidor, em seguida, você deve definir esta a 46. Todas as chamadas de discagem de entrada ou manuais serão imputadas Desse total também. O padrão é 96.

<BR>
<A NAME="servers-outbound_calls_per_second">
<BR>
<B>Máx. de Chamadas por Segundo -</B> Esta configuração determina o número máximo de chamadas que podem ser feitas pelo script de discagem automática neste servidor por segundo. Deve ser de 1 a 100. O padrão é 20

<BR>
<A NAME="servers-telnet_host">
<BR>
<B>Host Telnet -</B>  Este é o endereço ou nome do servidor Asterisk e é como os aplicativos de gerenciamento se conectam a ele. Se os aplicativos de gerenciamento estiverem sendo executados no próprio servidor Asterisk, então 'localhost' está correto.

<BR>
<A NAME="servers-telnet_port">
<BR>
<B>Porta Telnet -</B> Esta é a porta para conexão com o Manager do servidor Asterisk e é como os aplicativos de gerenciamento se conectam a ele. A porta padrão é '5038' e funciona normalmente com a instalação padrão.

<BR>
<A NAME="servers-ASTmgrUSERNAME">
<BR>
<B>Usuário do Manager -</B> O nome do usuário (login) usado para conectar genericamente ao serviço do Manager do Asterisk. O padrão é 'cron'

<BR>
<A NAME="servers-ASTmgrSECRET">
<BR>
<B>Senha do Manager -</B> A senha usada para a conexão com o serviço do Manager do Asterisk. O padrão é '1234'

<BR>
<A NAME="servers-ASTmgrUSERNAMEupdate">
<BR>
<B>Usuário de atualização do Manager -</B>O nome do usuário (login) otimizado para atualizações, para se conectar ao serviço do Manager do Asterisk. O padrão é 'updatecron' e asume que a senha é a mesma do usuário genérico.

<BR>
<A NAME="servers-ASTmgrUSERNAMElisten">
<BR>
<B>Usuário de Escuta do Manager -</B> O nome de usuário (login) otimizado para escutar eventos de saída, usado para conectar ao serviço de Manager do Asterisk. Padrão é 'listencron' e assume que a senha é a mesma do usuário genérico.

<BR>
<A NAME="servers-ASTmgrUSERNAMEsend">
<BR>
<B>Usuário de envio do Manager -</B> O nome de usuário (login) otimizado para enviar Ações para o manager, para se conectar ao serviço do Manager do Asterisk. O Valor padrão é 'sendcron' e assume que a senha é a mesma do usuário genérico.

<BR>
<A NAME="servers-conf_secret">
<BR>
<B>Conf. Archivo Secreto -</B> Este es el secreto, o la contraseña, para que el servidor en el auto iax generado conf para este servidor en otros servidores. Límite es el tablero de 20 caracteres alfanuméricos y de subrayado aceptado. El valor predeterminado es la prueba. Um segredo arquivo forte conf deve ser de pelo menos 8 caracteres de comprimento e têm minúsculas e letras maiúsculas, bem como, pelo menos, um número.

<BR>
<A NAME="servers-local_gmt">
<BR>
<B>Diferença GMT do Servidor-</B> A diferença em horas a partir do horário GMT de onde se encontra o servidor, nao ajustada para o horário de verão. O padrão é '-5'

<BR>
<A NAME="servers-voicemail_dump_exten">
<BR>
<B>Exten de descarga de Correio de Voz -</B> É o prefixo de exten usado neste servidor para enviar chamadas diretamente pelo agc para uma caixa postal específica. O padrão é '85026666666666'

<BR>
<A NAME="servers-voicemail_dump_exten_no_inst">
<BR>
<B>Vmail Dump Exten NI -</B> This is the dial plan prefix used to send calls directly to a user's voicemail from a live call in the astGUIclient app. This is the No Instructions setting.

<BR>
<A NAME="servers-answer_transfer_agent">
<BR>
<B>extensão de discagem automática -</B> A extensão padrão se nenhum estiver presente na campanha para enviar chamadas para para discagem automática. O padrão é '8365'

<BR>
<A NAME="servers-ext_context">
<BR>
<B>Contexto Padrão -</B> O contexto padrão usado para scripts que operam nesse servidor. O valor padrão é 'default'

<BR>
<A NAME="servers-sys_perf_log">
<BR>
<B>Performance do Sistema -</B> Configurar esta opção como Y irá habilitar a gravação de log de estatísticas de performance do sistema para o servidor, processos do sistema e canais em uso no Asterisk. O padrão é N.

<BR>
<A NAME="servers-vd_server_logs">
<BR>
<B>Logs do Servidor -</B> Definir esta opção como Y permitirá o registro de todos os scripts relacionados ao sistema para seus arquivos de log de texto. Ajustando para N vai parar de escrever logs para arquivos para estes processos, também o registro de tela de asterisco será desativado se isso for definido como N quando Asterisk é iniciado. O padrão é Y.

<BR>
<A NAME="servers-agi_output">
<BR>
<B>Saída do AGI -</B> Definir esta opção como NONE irá desativar a saída de todos os scripts AGI relacionados ao sistema. Ajustando para STDERR vai enviar a saída AGI ao Asterisk CLI. Ajustando para Matrícula vai enviar a saída para um arquivo no diretório de logs. Configurando-o para ambos irão enviar a saída para tanto o CLI Asterisk e um arquivo de log. O padrão é IMAGEM.

<BR>
<A NAME="servers-balance_active">
<BR>
<B>Saldo Discagem -</B> Definir este campo para Y vai permitir que o servidor para fazer chamadas de equilíbrio para as campanhas no sistema para que o nível de discagem definido pode ser satisfeita mesmo se não há agentes conectados em que a campanha no servidor. O padrão é N.

<BR>
<A NAME="servers-balance_rank">
<BR>
<B>Balance Ranking -</B> Este campo le permite establecer el orden en que este servidor se va a utilizar para la marcación de equilibrio, si está activada la marcación de equilibrio. El servidor con el rango más alto será usado primero en la colocación de Balance llenar las llamadas. Por defecto es 0.

<BR>
<A NAME="servers-balance_trunks_offlimits">
<BR>
<B>Balanço offlimits -</B> Esta configuração define o número de troncos para não permitir que os processos de equilíbrio de marcação de usar. Por exemplo, se você tem 40 troncos max e equilíbrio offlimits é definido como 10, você só vai ser capaz de usar 30 linhas tronco para o equilíbrio de marcação. O padrão é 0 .

<BR>
<A NAME="servers-recording_web_link">
<BR>
<B>Link Web de Gravação - </B> Esta configuração permite que você altere o padrão do link apresentado na tela de gravação no site admin. O padrão é SERVER_IP.

<BR>
<A NAME="servers-alt_server_ip">
<BR>
<B>IP do Serv. Altern. de Gravação- </B> Esta configuração é onde você pode colocar um IP do servidor ou outro nome de máquina que pode ser usado para colocar no lugar do server_ip nos links de gravação nas páginas do site admin. O padrão é vazio.

<BR>
<A NAME="servers-external_server_ip">
<BR>
<B>IP do servidor externo -</B> Esta configuração é onde você pode colocar um IP ou o nome outra máquina que pode ser usado no lugar do server_ip quando se utiliza um webphone na interface do agente. Para que isso funcione você também deve ter a entrada de telefones configurado para usar o IP do servidor externo. O padrão é vazio.

<BR>
<A NAME="servers-active_twin_server_ip">
<BR>
<B>IP AtivoServer Gêmea -</B> Alguns sistemas exigem a criação de servidores de telefonia em pares. Esta configuração é onde você pode colocar o IP do servidor de outro servidor que este servidor está geminada com. O padrão é vazio para deficientes.

<BR>
<A NAME="servers-active_asterisk_server">
<BR>
<B>Ativo Asterisk Server -</B> Se o Asterisk não está em execução neste servidor, ou se os processos de marcação não deve estar usando este servidor, ou se estiver usando apenas este servidor para outros scripts, como o script funil de carga que você gostaria de definir isso para N. O padrão é Y.

<BR>
<A NAME="servers-auto_restart_asterisk">
<BR>
<B>Auto-Restart Asterisk -</B> Se Asterisk está em execução neste servidor e você deseja que o sistema para certificar-se que ele será reiniciado no caso em que ele trava, você pode querer considerar como habilitar essa configuração. Se ativado, o sistema irá verificar a cada minuto para ver se o Asterisk está em execução e se não for ele tentará reiniciá-lo. Este processo não será executado nos primeiros 5 minutos depois que um sistema tenha sido até. O padrão é N.

<BR>
<A NAME="servers-asterisk_temp_no_restart">
<BR>
<B>Temp No-Restart Asterisk -</B> Se Auto-Restart Asterisk está habilitado neste servidor, ligar esta definição vai impedir o processo de reinicialização automática de correr até que o servidor seja reiniciado. O padrão é N.

<BR>
<A NAME="servers-active_agent_login_server">
<BR>
<B>Ativo Server Agent -</B> Definir esta opção como N impedirá agentes de ser capaz de fazer login para este servidor através da tela do agente. Isso é muito útil quando se usa uma configuração de carga de login telefone equilibrada. O padrão é Y.

<BR>
<A NAME="servers-generate_conf">
<BR>
<B>Generate conf files -</B> Se você gostaria que o sistema de auto-gerar conf asterisco com base nas entradas de telefones, entradas de transporte e balanceamento de carga de configuração dentro do sistema, em seguida, defina-o para Y. O padrão é Y.

<BR>
<A NAME="servers-rebuild_conf_files">
<BR>
<B>Reconstruir Arquivos Conf - </B> Se você quer forçar a reconstrução dos arquivos conf do Asterisk ou se qualquer um dos ramais ou operadoras foi alterado então isso deve ser Y. Após os aquivos conf terem sido criados e o Asterisk ter sido recarregado, então isso será alterado para N. O padrão é Y.

<BR>
<A NAME="servers-rebuild_music_on_hold">
<BR>
<B>Volver a generar música en espera -</B> Si desea forzar una reconstrucción de la música en los archivos de la bodega o si la música en las entradas de espera ni entradas de servidor han cambiado entonces esto debe establecerse en Y. Después de la música en espera los archivos se han sincronizado y volver a cargar entonces esto va a ser cambiado a N. defecto es Y.

<BR>
<A NAME="servers-sounds_update">
<BR>
<B>Actualización de los sonidos -</B> Si desea forzar una comprobación de los archivos de sonido en este servidor, y el almacén central de audio está activado como una configuración del sistema, este campo permitirán a los sonidos de actualización para ejecutar en la parte superior de la siguiente minuto. Toda vez que un archivo de audio se carga desde la interfaz web de este se ajusta automáticamente a Y para todos los servidores que tienen activos de Asterisk. Por defecto es n.

<BR>
<A NAME="servers-recording_limit">
<BR>
<B>Limite de gravação -</B> Este campo é onde você define o número máximo de minutos que uma chamada a gravação iniciada pelo sistema pode ser. Padrão é de 60 minutos.

<BR>
<A NAME="servers-carrier_logging_active">
<BR>
<B>Portador de registro activo -</B> Esta configuración le permite registrar todos los códigos de retorno para colgar cualquier lista de marcación saliente llamadas que están haciendo. Por defecto es N.

<BR>
<A NAME="servers-custom_dialplan_entry">
<BR>
<B>Entrada Dialplan personalizado -</B> Este campo permite-lhe entrar em quaisquer elementos dialplan que você deseja para o servidor, as linhas serão adicionadas para o contexto padrão.






<BR><BR><BR><BR>

<B><FONT SIZE=3>conf_templates TABELA</FONT></B><BR><BR>
<A NAME="conf_templates-template_id">
<BR>
<B>ID da Template - </B> Este campo precisa ter pelo menos 2 caracteres de comprimento e não mais que 15 caracteres, sem espaços. Este é o ID que irá ser usado para identificar o template de conf no sistema.

<BR>
<A NAME="conf_templates-template_name">
<BR>
<B>Nome da Template - </B> Este é o nome descritivo da configuração da template.

<BR>
<A NAME="conf_templates-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="conf_templates-template_contents">
<BR>
<B>Conteúdo da Template - </B> Este campo é onde você pode configurar itens específicos para serem usados por todos os ramais e/ou todas as operadoras que estão configuradas para usar este template. Campos que NÃO devem ser incluídos nesse ítem são: secret, accountcode, account, username e mailbox.





<BR><BR><BR><BR>

<B><FONT SIZE=3>server_carriers TABELA</FONT></B><BR><BR>
<A NAME="server_carriers-carrier_id">
<BR>
<B>ID da Operadora - </B> Este campo precisa ter entre 2 e 15 caracteres de comprimento, sem espaços. Este é o ID que irá ser usado para identificar a operadora no sistema.

<BR>
<A NAME="server_carriers-carrier_name">
<BR>
<B>Nome da Operadora - </B> Este é o nome descritivo da operadora no sistema.

<BR>
<A NAME="server_carriers-carrier_description">
<BR>
<B>Descripción Carrier -</B> Éste se coloca en los comentarios de los archivos de conf asterisco encima del dialplan y anotaciones en cuenta. Máximo 255 caracteres.

<BR>
<A NAME="server_carriers-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="server_carriers-registration_string">
<BR>
<B>String de Registro - </B> Este campo é onde você coloca a string necessária na configuração do IAX ou SIP para registrar a operadora. Opcional mas altamente recomendado se sua operadora permite registro .

<BR>
<A NAME="server_carriers-template_id">
<BR>
<B>ID do Template - </B> Este campo opcional permite você escolha um template para esta configuração de operadora.

<BR>
<A NAME="server_carriers-account_entry">
<BR>
<B>Account Entry -</B> Este campo é usado se você não tiver selecionado um modelo a ser usado, e é onde você pode entrar nas configurações da conta específica a ser utilizada para esta transportadora. Se você vai tomar em chamadas de entrada a partir deste tronco transportadora você pode querer definir o contexto = trunkinbound dentro deste campo para que você possa usar o processo de DID manipulação dentro do sistema.

<BR>
<A NAME="server_carriers-protocol">
<BR>
<B>Protocolo - </B> Este campo permite que você defina o protocolo a ser usado para esta configuração de operadora. Atualmente somente IAX e SIP são suportados.

<BR>
<A NAME="server_carriers-globals_string">
<BR>
<B>String Global- </B> Este campo opcional permite definir uma variável global para uso desta operadora no plano de discagem.

<BR>
<A NAME="server_carriers-dialplan_entry">
<BR>
<B>Plano de Discagem- </B> Este campo opcional permite definir um conjunto de entradas no plano de discagem para serem usados com esta operadora.

<BR>
<A NAME="server_carriers-server_ip">
<BR>
<B>IP do Servidor - </B> Este é o servidor que este registro de operadora está configurado.

<BR>
<A NAME="server_carriers-active">
<BR>
<B>Ativo - </B> Isto define se a operadora irá ser incluída nos arquivos conf gerados automáticamente ou não.





<BR><BR><BR><BR>

<B><FONT SIZE=3>TABELA DE CONFERÊNCIAS</FONT></B><BR><BR>
<A NAME="conferences-conf_exten">
<BR>
<B>Número da Conferência -</B> Este campo é onde você coloca o número dialplan conferência meetme. Recomenda-se também que o número meetme em meetme.conf corresponde a este número para cada entrada. Isto é para as conferências na tela do usuário astGUIclient e é usado para a funcionalidade leave-3way-call no sistema.

<BR>
<A NAME="conferences-server_ip">
<BR>
<B>IP do Servidor -</B> Deve ser selecionado qual servidor Asterisk esta conferência está configurada.




<?php
if ($SSoutbound_autodial_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>SERVER_TRUNKS TABELA</FONT></B><BR><BR>
	<A NAME="server_trunks">
	<BR>
	<B>Trunks do Servidor permite restringir as linhas de saída que são usados ​​neste servidor para discagem campanha em uma base per-campanha. Você tem a opção de reservar um número específico de linhas a ser utilizado por uma única campanha, bem como permitindo que a campanha para executar mais de suas linhas reservadas em qualquer linhas permanecem abertas, desde as linhas totais utilizadas pelo sistema neste servidor é menos do que a configuração Max Trunks. Não tendo qualquer desses registros permitirá a campanha que marca a linha primeiro a ter o maior número de linhas, pois ele pode ficar sob a configuração Max Trunks.</B>
	<?php
	}
?>




<BR><BR><BR><BR>

<B><FONT SIZE=3>SYSTEM_SETTINGS TABELA</FONT></B><BR><BR>
<A NAME="settings-use_non_latin">
<BR>
<B>Uso Não-Latino -</B> Esta opção permite configurar o padrão de visualização para usar caracteres UTF8 e não filtrar com expressões regulares os caracteres da família dos latinos ou mostrar formatação. Padrão é 0.

<BR>
<A NAME="settings-webroot_writable">
<BR>
<B>Raiz Web Gravável -</B> Esta configuração permite que você define se os arquivos temporários e de autenticação devem ser gravados na raiz do website ou do servidor web. Padrão é 1.

<BR>
<A NAME="settings-agent_disable">
<BR>
<B>Agente de desativar a exibição -</B> Este campo é utilizado para selecionar quando mostrar um agente avisos quando a sessão foi desativada pelo sistema, uma ação de gestor ou por uma medida externa. A configuração NOT_ACTIVE irá desativar a mensagem na tela agentes. A configuração LIVE_AGENTE só vai exibir a mensagem desativado quando registro dos agentes auto_calls foi removido, como durante um logout força ou sair de emergência. O padrão é ALL.

<BR>
<A NAME="settings-frozen_server_call_clear">
<BR>
<B>Limpar chamadas congelados -</B> Esta opção pode permitir que a capacidade para a página Relatórios gerais eo script AST_timecheck.pl opcional para limpar as entradas auto_calls para um servidor congelado para que eles não afetam o roteamento de chamadas. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-allow_sipsak_messages">
<BR>
<B>Allow SIPSAK Messages -</B> Se definido como 1, isso vai permitir que o ajuste da tabela telefones sipsak para trabalhar se o telefone está configurado para o protocolo SIP. O servidor irá enviar mensagens para o telefone SIP para exibir no visor do telefone quando conectado ao sistema. Este recurso só funciona com telefones SIP e requer aplicação sipsak a ser instalado no servidor web que o agente está conectado. O padrão é 0. 

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>API do Agente Ativa - </B> Se configurado como 1, irá permitir que a interface API do agente Funcione. O padrão é 0. 

<BR>
<A NAME="settings-admin_home_url">
<BR>
<B>URL Home do Admin -</B> É a URL do site que você irá se clicar no link HOME no topo da página admin.php.

<BR>
<A NAME="settings-admin_modify_refresh">
<BR>
<B>Administrador Modificar Auto-Refresh -</B> Este é o intervalo de atualização em segundos das telas de modificação neste interface de administração. Configurando isso para 0 irá desativá-lo, configurá-lo abaixo de 5, na sua maioria fazem as telas modificam inutilizável porque irá atualizar rápido demais para mudar de campo. Esta opção é útil em situações em que mais de um gerente é controlar configurações em uma campanha ativa ou em grupo, para que as configurações são atualizadas com freqüência. O padrão é 0.

<BR>
<A NAME="settings-nocache_admin">
<BR>
<B>Administrador No-Cache -</B> Definindo-a para um irá definir todas as páginas de administração para o navegador web no-cache, por isso cada tela tem que ser recarregado cada vez que é visto, mesmo se clicar de volta no navegador. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="settings-enable_agc_xfer_log">
<BR>
<B>Habilitar Log de Transf.-</B> Esta opção irá gravar um regsitro de ocorrências em um arquivo no servidor web cada vez que uma chamada for transferida para um agente. O padrão é 0, desabilitado.

<BR>
<A NAME="settings-enable_agc_dispo_log">
<BR>
<B>Habilitar Logfile Disposição agente -</B> Esta opção irá registrar em um arquivo de log de texto no servidor cada vez que uma chamada é dispositioned por um agente. O padrão é 0, desativado.

<BR>
<A NAME="settings-timeclock_end_of_day">
<BR>
<B>Fim do dia - </B> Esta configuração define quando os usuários devem ser retirados do sistema de ponto automaticamente. Só roda uma vez por dia. Deve ter somente 4 caracteres, 2 para hora e 2 para os minutos no formato 24 horas. O padrão é 0000.

<BR>
<A NAME="settings-default_local_gmt">
<BR>
<B>Local padrão GMT -</B> Esta configuração define o que será usado por padrão quando novos telefones e servidores são adicionadas usando essa interface web de administração. Padrão é de -5.

<BR>
<A NAME="settings-default_voicemail_timezone">
<BR>
<B>Zona Correio de Voz padrão -</B> Esta configuração define o fuso será usado por padrão quando novos telefones e caixas de correio de voz são criados. A lista de zonas disponíveis é diretamente retirado do arquivo voicemail.conf. O padrão é oriental.

<BR>
<A NAME="settings-agents_calls_reset">
<BR>
<B>Agentes Chama Redefinir -</B> Esta configuração define se a agentes com sessão iniciada e registros telefonemas ativos devem ser zerados no final timeclock do dia. O padrão é 1 para habilitado.

<BR>
<A NAME="settings-timeclock_last_reset_date">
<BR>
<B>Último Sair Automático do Ponto - </B> Este campo mostra a data do último logout automático -.

<BR>
<A NAME="settings-vdc_header_date_format">
<BR>
<B>Formato da Data no Cabeçalho da tela do Agente -</B> Este menu permite-lhe escolher o formato da data e hora que aparece no topo da tela do agente. As opções para essa configuração são: o padrão é MS_DASH_24HR<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Formato de data Padrão com ano mês dia seguido por horário 24 horas<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - Formato de data dos EUA com mês dia ano seguido por horário 24 horas<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Formato de data Europeu com dia mês ano seguido por horário 24 horas<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Formato de data abreviada em texto com mes e dia seguido por horário 24 horas<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Formato de data Padrão com ano mês dia seguido por horário 12 horas<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - Formato de data dos EUA com mês dia ano seguido por horário 12 horas<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Formato de data Europeu com dia mês ano seguido por horário 12 horas<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Formato de data abreviada em texto com mes e dia seguido por horário 12 horas<BR>

<BR>
<A NAME="settings-vdc_customer_date_format">
<BR>
<B>Formato da Data do Cliente na tela do Agente -</B> Este menu permite-lhe escolher o formato da data e hora do cliente que aparece no topo da seção de informações do cliente da tela do agente. As opções para essa configuração são: o padrão é AL_TEXT_AMPM<BR>
MS_DASH_24HR  2008-06-24 23:59:59 - Formato de data Padrão com ano mês dia seguido por horário 24 horas<BR>
US_SLASH_24HR 06/24/2008 23:59:59 - Formato de data dos EUA com mês dia ano seguido por horário 24 horas<BR>
EU_SLASH_24HR 24/06/2008 23:59:59 - Formato de data Europeu com dia mês ano seguido por horário 24 horas<BR>
AL_TEXT_24HR  JUN 24 23:59:59 - Formato de data abreviada em texto com mes e dia seguido por horário 24 horas<BR>
MS_DASH_AMPM  2008-06-24 11:59:59 PM - Formato de data Padrão com ano mês dia seguido por horário 12 horas<BR>
US_SLASH_AMPM 06/24/2008 11:59:59 PM - Formato de data dos EUA com mês dia ano seguido por horário 12 horas<BR>
EU_SLASH_AMPM 24/06/2008 11:59:59 PM - Formato de data Europeu com dia mês ano seguido por horário 12 horas<BR>
AL_TEXT_AMPM  JUN 24 11:59:59 PM - Formato de data abreviada em texto com mes e dia seguido por horário 12 horas<BR>

<BR>
<A NAME="settings-vdc_header_phone_format">
<BR>
<B>Formato do Número do Telefone na tela do Agente -</B> Este menu permite-lhe escolher o formato do número de telefone do cliente que aparece na seção de status da tela do agente. As opções para essa configuração são: o padrão é US_PARN<BR>
US_DASH 000-000-0000 - USA número separado por traço<BR>
US_PARN (000)000-0000 - USA número separado por traço, ddd entre parentesis<BR>
MS_NODS 0000000000 - Sem Formatação<BR>
UK_DASH 00 0000-0000 - UK número separado por traço com espaço após o código da cidade<BR>
AU_SPAC 000 000 000 - Australia número separado por espaços<BR>
IT_DASH 0000-000-000 - Italy número separado por traço<BR>
FR_SPAC 00 00 00 00 00 - France número separado por espaços<BR>

<BR>
<A NAME="settings-vdc_agent_api_active">
<BR>
<B>Agent interface API Access Ativo-</B> This option allows you to enable or disable the agent interface API. Default is 0.

<BR>
<A NAME="settings-agentonly_callback_campaign_lock">
<BR>
<B>Trava para Agendamento Vinculado -</B> Esta opção define se Agendamento Vinculado(AGENTONLY) serão travados para a campanha que o a gente originalmente agendou. Configurando como 1 significa que o agente somente pode discar para eles a partir da campanha que os configurou, 0 significa que o agente pode acessar os agendamentos não importando a campanha que eles foram criados. O padrão é 1.

<BR>
<A NAME="settings-sounds_central_control_active">
<BR>
<B>Controle Central de Audio Ativado -</B> Esta opção define se o sistema de sincronia de som está ativado entre todos os servidores. O padrão é 0 para inativo.

<BR>
<A NAME="settings-sounds_web_server">
<BR>
<B>Servidor Web de Sons -</B> Este é o nome do servidor ou endereço IP para o servidor web que estará controlando os arquivos de audio no sistema, isto deve combinar com o nome do servidor ou endereço IP no qual você está tentando acessar a página audio_store.php. O padrão é 127.0.0.1.

<BR>
<A NAME="settings-sounds_web_directory">
<BR>
<B>Diretório Web de Sons -</B> Esta auto-generado nombre del directorio es creado al azar por el sistema como el lugar que la tienda de audio se mantendrá. Todos los archivos de audio residir en este directorio.

<BR>
<A NAME="settings-admin_web_directory">
<BR>
<B>Diretório do Web Admin -</B> Este é o diretório web que seu conteúdo web administation, como admin.php, está dentro Para descobrir o seu diretório web de administração, é tudo que está entre o nome de domínio eo admin.php na URL desta página, sem a começando e terminando barras.

<BR>
<A NAME="settings-active_voicemail_server">
<BR>
<B>Servidor de correo de voz activo -</B> En sistemas multi-servidor, este es el servidor que se encargará de todas las casillas de correo de voz. Este servidor es también donde la línea telefónica en mensajes generados se cargan desde el 8168 grabaciones.

<BR>
<A NAME="settings-allow_voicemail_greeting">
<BR>
<B>Permitir Correio de Voz saudação Chooser -</B> Se essa configuração for habilitada permitirá que você escolha um arquivo de áudio a partir da loja de áudio para ser jogado como a saudação de correio de voz a uma caixa de correio de voz específico. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-outbound_autodial_active">
<BR>
<B>Outbound Auto-Dial Ativo-</B> Esta opção permite que você ative ou desative saída discagem automática dentro do sistema, definir este campo para 0 irá remover as listas e filtros seções e muitos campos das telas Campanha modificação. Marcação por entrada manual ainda será permitida a partir de dentro da tela do agente, mas nenhuma lista de discagem será possível. O padrão é 1 para o ativo.

<BR>
<A NAME="settings-disable_auto_dial">
<BR>
<B>Desativar Auto-Dial -</B> Esta opção só é editável pelo administrador do sistema. Ele não irá remover quaisquer opções a partir da interface web de gestão, mas vai impedir que qualquer discagem automática de ligações de acontecer no sistema. Apenas chamadas manuais Dial saída acionados diretamente por agentes irão funcionar se esta opção estiver habilitada. O padrão é 0 para inativo.

<BR>
<A NAME="settings-auto_dial_limit">
<BR>
<B>Ratio Dial Limit -</B> Este es el límite máximo de la escala de marcación automática en la pantalla de la campaña.

<BR>
<A NAME="settings-outbound_calls_per_second">
<BR>
<B>Máx. de Cham. Trasnf. por Segundo -</B> Esta configuração determina o número máximo de chamadas que podem ser feitas pelo discador automático de saída para todos os servidores, por segundo. Deve ter entre 1 e 200 caracteres. O padrão é 40.

<BR>
<A NAME="settings-allow_custom_dialplan">
<BR>
<B>Permitir Custom Dialplan entradas -</B> Esta opção permite que você insira personalizados linhas dialplan em Menus de chamadas, servidores e configurações do sistema. O padrão é 0 para inativo.

<BR>
<A NAME="settings-pllb_grouping_limit">
<BR>
<B>PLLB Limite Agrupamento -</B> Telefone Entrar Carga Limite Agrupamento de balanceamento. Se Agrupamento PLLB está definido para CASCATA no nível da campanha, em seguida, essa configuração vai determinar o número de agentes aceitável em cada servidor em todas as campanhas. O padrão é 100.

<BR>
<A NAME="settings-generate_cross_server_exten">
<BR>
<B>Gerar extensões entre servidores telefone -</B> Esta opção se definido como 1 irá gerar entradas dialplan para qualquer telefone em um sistema multi-servidor. O padrão é 0 para inativo.

<BR>
<A NAME="settings-user_territories_active">
<BR>
<B>Territorios de usuario de Ativo-</B> Esta configuração permite que você ative as configurações Territórios do usuário na tela modificação do usuário. Esta funcionalidade foi adicionada para permitir maior integração com uma instalação personalizada Vtiger, mas pode ter aplicações no sistema, por si só também. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-enable_second_webform">
<BR>
<B>Formulario de Asistencia Segunda Habilitar -</B> This setting allows you to have a second web form for campaigns and in-groups in the agent interface. Default is 0 for disabled.

<BR>
<A NAME="settings-enable_tts_integration">
<BR>
<B>Habilitar TTS Integración -</B> Esta configuración le permite habilitar Texto a la integración de voz con Cepstral. Esto sólo está disponible actualmente para las campañas de tipo de encuesta de salida. Por defecto es 0 para discapacitados.

<BR>
<A NAME="settings-callcard_enabled">
<BR>
<B>Habilitar CallCard -</B> Essa configuração permite que os recursos CallCard para permitir que os chamadores usem números de pinos e card_ids que têm um saldo de minutos, e esses saldos pode ter agente tempo de conversação em chamadas de clientes a in-grupos deduzidos. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-custom_fields_enabled">
<BR>
<B>Habilitar campos personalizados da lista -</B> Esta configuração permite que o costume lista de recursos que permite que os campos para os campos de dados personalizados a ser definido na interface web de administração em uma base per-lista e, então, os campos disponíveis em uma guia FORM para o agente na interface web do agente. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="settings-test_campaign_calls">
<BR>
<B>Habilitar chamadas de teste da campanha -</B> Esta configuração permite que a capacidade de introduzir um código de telefone e número de telefone em campos na parte inferior da tela Detalhes da campanha e colocar uma chamada telefónica para o número como se fosse uma vantagem de ser auto-marcado no sistema. O número de telefone será armazenado como uma nova pista no manual lista de identificação de discagem lista. A campanha deve estar ativo para que este recurso seja ativado, e recomenda-se que as listas atribuídas à campanha todos ser definido como inativo. O prefixo de discagem, tempo limite de ligação e todos os recursos de discagem outros produtos, exceto para DNC e opções de compra de tempo, vai afetar a discagem do número de teste. O telefonema vai ser colocado no servidor selecionado como o servidor de correio de voz nas configurações do sistema. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="settings-expanded_list_stats">
<BR>
<B>Habilitar Estatísticas lista extensa -</B> Esta configuração permite que duas colunas adicionais a serem exibidos em mais da lista de tabelas de estado de degradação sobre a modificação lista de páginas e de modificação de campanha. A penetração é definido como a percentagem de ligações que são iguais ou superiores ao limite de contagem campanha de chamadas e ou o estado é marcado como concluído. O padrão é 1 para habilitado.

<BR>
<A NAME="settings-country_code_list_stats">
<BR>
<B>Códigos de Países Estatísticas Lista -</B> Esta configuração se habilitado mostrará um resumo quebra o código de país na lista tela modificar. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-enhanced_disconnect_logging">
<BR>
<B>Maior Disconnect Logging -</B> Essa configuração permite o registro de chamadas que recebem um sinal CONGESTIONAMENTO com um código de causa de 1, 19, 21, 34 ou 38. Nós geralmente não é recomendável habilitar esta nos EUA. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-campaign_cid_areacodes_enabled">
<BR>
<B>Habilitar CódigoDeÁrea Campanha CID -</B> Esta configuração permite que a capacidade de definir números específicos de saída callerid para ser usado por campanha. O padrão é 1 para habilitado.

<BR>
<A NAME="settings-did_ra_extensions_enabled">
<BR>
<B>Ativar substituições extensão remota do Agente -</B> Esta configuração permite DIDs ter substituições de extensão para agente remoto encaminhado chama através de in-grupos. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="contact_information">
<A NAME="settings-contacts_enabled">
<BR>
<B>Contactos Habilitado -</B> Esta configuração permite que os contatos sub-seção de administração que permite que um gerente para adicionar modificar ou excluir contatos no sistema que podem ser usados ​​como parte de uma transferência personalizado em uma campanha onde um agente pode procurar contactos por Nome Sobrenome ou escritório número e, em seguida, selecione um dos muitos números associados a esse contato. Este recurso é freqüentemente utilizado pelos operadores ou em funções de telefonistas, onde o usuário teria de transferir uma chamada para um telefone não-agente. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="settings-call_menu_qualify_enabled">
<BR>
<B>Menu de chamada Qualificar Ativado -</B> Esta configuração permite a opção nos menus de chamadas para colocar uma qualificação SQL sobre as pessoas que ouvem esse menu chamada. Para mais informações sobre como esse recurso funciona, consulte a ajuda para menus de atendimento. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-level_8_disable_add">
<BR>
<B>Nível 8 Desativar Adicionar -</B> Esta configuração se habilitado irá impedir qualquer nível 8 usuário adicione ou copiar qualquer registro no sistema, não importa o que suas configurações de usuário são. Excluem-se destas restrições são a capacidade de adicionar grupos de números de telefone do DNC e do filtro e uma página nova pista em Adicionar. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-admin_list_counts">
<BR>
<B>Administração das listas Counts -</B> Esta configuração permite que você desabilite a contagem da lista que aparecem na lista Listas e as telas de modificação da campanha. O padrão é 1 para enabled.

<BR>
<A NAME="settings-allow_emails">
<BR>
<B>Permitir e-mails -</B> Este é o lugar onde você pode definir se o sistema será capaz de receber e-mails de entrada, além de telefonemas.

<BR>
<A NAME="settings-first_login_trigger">
<BR>
<B>Gatilho primeiro Login -</B> Esta configuração permite a configuração inicial da tela do servidor para ser mostrado para o administrador quando pela primeira vez entrar no sistema.

<BR>
<A NAME="settings-default_phone_registration_password">
<BR>
<B>Senha padrão de Registro Telefone -</B> Esta é a senha registo padrão usado quando novos celulares são adicionados ao sistema. O padrão é teste.

<BR>
<A NAME="settings-default_phone_login_password">
<BR>
<B>Padrão Login Senha Telefone -</B> Este é o padrão de telefone senha de acesso web utilizado quando os telefones novos são adicionados ao sistema. O padrão é teste.

<BR>
<A NAME="settings-default_server_password">
<BR>
<B>Senha do Servidor Padrão -</B> Esta é a senha do servidor padrão usado quando novos servidores são adicionados ao sistema. O padrão é teste.

<BR>
<A NAME="settings-slave_db_server">
<BR>
<B>Database Server Escravo -</B> Se você tem um banco de dados MySQL escravo em seguida, digite o endereço IP local para o servidor aqui. Esta opção é atualmente usada apenas nos relatórios selecionados na próxima opção e não tem nada a ver com a configuração automática MySQL replicação mestre-escravo. O padrão é vazio para deficientes físicos.

<BR>
<A NAME="settings-reports_use_slave_db">
<BR>
<B>Relatórios para usar DB Escravo -</B> Esta opção permite que você selecione os relatórios que você quer ter usar o banco de dados MySQL escravo, conforme definido na opção acima, em vez de banco de dados mestre que o sistema vivo está sendo executado. Você deve configurar a replicação escravo MySQL antes de habilitar esta opção. O padrão é vazio para deficientes.

<BR>
<A NAME="settings-default_field_labels">
<BR>
<B>Os rótulos de campos padrão -</B> Esses 19 campos permitem que você defina o nome como ele aparecerá na interface do agente, bem como a página principal de modificação administrativa. O padrão é vazio que irá usar os padrões codificados na interface do agente. Você também pode definir um rótulo para --- --- ESCONDER para ocultar o rótulo eo campo.

<BR>
<A NAME="settings-label_hide_field_logs">
<BR>
<B>Ocultar Label em registros de chamadas -</B> Se um rótulo é definida como --- OCULTAR --- então os registros de chamadas de agentes, se habilitado na campanha, ainda vai mostrar o campo e os dados a menos que esta opção é definida para Y. O padrão é N.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>Recursos de CQ Ativos - </B> Esta opção permite habilitar ou desabilitar os recursos de CQ ou Controle de Qualidade. O padrão é 0 para Inativo.

<BR>
<A NAME="settings-default_webphone">
<BR>
<B>Webphone padrão -</B> Se definido para 1, esta opção fará com que todos os novos telefones criados tiver definido como conjunto de Webphone padrão é 0 Y..

<BR>
<A NAME="settings-webphone_systemkey">
<BR>
<B>Webphone chave do sistema -</B> Se o seu sistema ou o fornecedor de exigir, este é o lugar onde a chave do sistema para o webphone deve ser inserido dentro padrão é vazia.

<BR>
<A NAME="settings-default_codecs">
<BR>
<B>Codecs padrão -</B> Você pode definir uma lista delimitada por vírgula de codecs para ser definido como os codecs padrão para todos os sistemas. Opções para incluir codecs ulaw, alaw, gsm, g729, Speex, G722, G723, G726, iLBC, ... O padrão é vazio.

<BR>
<A NAME="settings-custom_dialplan_entry">
<BR>
<B>Entrada Dialplan personalizado -</B> Este campo permite-lhe entrar em quaisquer elementos dialplan que você deseja para todos os servidores Asterisk, as linhas serão adicionadas para o contexto padrão.

<BR>
<A NAME="settings-reload_dialplan_on_servers">
<BR>
<B>Atualizar Dialplan Em Servidores -</B> Esta opção permite que você force uma recarga do dialplan em todos os servidores Asterisk no cluster. Se você fez alterações na entrada personalizada Dialplan acima, você deve definir esta a 1 e se submeter a ter essas alterações entrem em vigor nos servidores.

<BR>
<A NAME="settings-noanswer_log">
<BR>
<B>Sem resposta-Log -</B> Esta opção irá registrar as chamadas auto-discagem que não foram respondidas para uma tabela separada. O padrão é N.

<BR>
<A NAME="settings-did_agent_log">
<BR>
<B>DID Log agente -</B> Esta opção irá registrar a entrada DID chamadas junto com um no grupo e de usuário de identificação, se aplicável, uma tabela separada. O padrão é N.

<BR>
<A NAME="settings-alt_log_server_ip">
<BR>
<B>Alt Log-DB Server -</B> Este é o servidor de banco de dados alternativo log. Isto é opcional, e permite que alguns logs para serem gravados em um banco de dados separado. O padrão é vazio.

<BR>
<A NAME="settings-tables_use_alt_log_db">
<BR>
<B>Alt-Log Tabelas -</B> Estas são as tabelas que estão disponíveis para fazer logon no servidor alternativo log de banco de dados. O padrão é em branco.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>Padrão IP do servidor externo -</B> Se definido para 1, esta opção fará com que todos os novos telefones criados têm Use IP do servidor externo definido como Y. O padrão é 0.

<BR>
<A NAME="settings-qc_features_active">
<BR>
<B>URL Webphone -</B> Esta é a URL do webphone que será usado com este sistema se ele estiver habilitado no registro de telefones que um agente está usando. O padrão é vazio.

<BR>
<A NAME="settings-enable_queuemetrics_logging">
<BR>
<B>Enable QueueMetrics Logging -</B> Esta configuração permite que você defina se o sistema irá inserir entradas de registro na tabela de banco de dados queue_log como atividade Asterisk Filas faz. QueueMetrics é um autônomo, de código fechado programa de análise estatística. Você deve ter QueueMetrics já instalado e configurado antes de habilitar este recurso. O padrão é 0.

<BR>
<A NAME="settings-queuemetrics_server_ip">
<BR>
<B>IP do servidor do QueueMetrics -</B> É o IP do servidor do QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_dbname">
<BR>
<B>Nome do BD do QueueMetrics -</B> É o nome do banco de Dados do QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_login">
<BR>
<B>Login do BD do QueueMetrics -</B> éste es el nombre delusuario usado para abrirse una sesión a su base de datos deQueueMetrics.

<BR>
<A NAME="settings-queuemetrics_pass">
<BR>
<B>Senha do BD do QueueMetrics -</B> É a senha a ser usada para logar no DB do QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_url">
<BR>
<B>URL do QueueMetrics -</B> É a URL ou endereço do site usado para entrar na sua instalação do QueueMetrics.

<BR>
<A NAME="settings-queuemetrics_log_id">
<BR>
<B>QueueMetrics Log ID -</B> Este é o ID do servidor que todos contact center registra indo para o banco de dados QueueMetrics usará como um identificador para cada registro.

<BR>
<A NAME="settings-queuemetrics_eq_prepend">
<BR>
<B>QueueMetrics EnterQueue Prepend -</B> Este campo é utilizado para permitir a prepending de um dos campos de dados de lista na frente do número de telefone do cliente para relatórios QueueMetrics personalizados. O padrão é NONE para não preencher nada.

<BR>
<A NAME="settings-queuemetrics_loginout">
<BR>
<B>QueueMetrics Login-Out -</B> Esta opção afeta a forma como o sistema irá registrar os logins e logouts de um agente na queue_log. O padrão é o padrão de uso padrão AGENTLOGIN AGENTLOGOFF, callback usará AGENTCALLBACKLOGIN e AGENTCALLBACKLOGOFF que QM irá analisar de forma diferente, NONE não registrará qualquer login e logout dentro queue_log.

<BR>
<A NAME="settings-queuemetrics_callstatus">
<BR>
<B>QueueMetrics CallStatus -</B> Esta opção se definido a 0 não vai colocar na entrada CALLSTATUS em queue_log quando um agente disposições chamadas. O padrão é 1 para habilitado.

<BR>
<A NAME="settings-queuemetrics_addmember_enabled">
<BR>
<B>QueueMetrics AddMember Habilitado -</B> Esta opção se definido como 1 irá gerar ADDMEMBER2 e entradas REMOVEMEMBER em queue_log. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="settings-queuemetrics_dispo_pause">
<BR>
<B>QueueMetrics Código Pause Dispo -</B> Esta opção, se preenchida, permite que você defina se um código de pausa dispo é celebrado queue_log quando um agente está em estado dispo. O padrão é vazio para deficientes físicos.

<BR>
<A NAME="settings-queuemetrics_pause_type">
<BR>
<B>QueueMetrics Tipo Pausa Logging -</B> Se ativado, esta opção irá registrar o tipo de pausa no campo data5 mesa queue_log. Você deve se certificar de que você tem um campo data5 ou habilitar esse recurso vai quebrar compatibilidade QM. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-queuemetrics_pe_phone_append">
<BR>
<B>QueueMetrics Telefone Ambiente Telefone Anexar -</B> Esta opção, se ativada, irá acrescentar o login telefone agente para o registro dados4 na tabela de log fila se a campanha de campo Ambiente Telefone é preenchida. O padrão é 0 para deficientes físicos.

<BR>
<A NAME="settings-queuemetrics_record_hold">
<BR>
<B>QueueMetrics Mantenha registro de chamadas -</B> Esta opção, se estiver ativado, irá registrar quando um cliente é colocada em espera e retirada da retenção na tabela record_tags QM mais recente. O padrão é 0 para deficientes.

<BR>
<A NAME="settings-queuemetrics_socket">
<BR>
<B>QueueMetrics Soquete Enviar -</B> Esta opção, se estiver ativado, enviará dados QM para uma página web que irá enviá-lo através de um soquete para a exploração madeireira. A opção CONNECT_COMPLETE enviará eventos CONNECT, COMPLETEAGENTE e COMPLETECALLER a url abaixo definido. O padrão é NONE para deficientes.

<BR>
<A NAME="settings-queuemetrics_socket_url">
<BR>
<B>QueueMetrics Soquete Enviar URL -</B> Se enviar socket está habilitado acima, este é o URL que é usado para enviar os dados. Padrão é vazio para deficientes.

<BR>
<A NAME="settings-enable_vtiger_integration">
<BR>
<B>Enable Vtiger Integration -</B> Esta configuração permite que você ative a integração Vtiger com o sistema. Atualmente links para Vtiger administração e pesquisa, bem como a replicação do usuário são os únicos recursos de integração disponíveis. O padrão é 0.

<BR>
<A NAME="settings-vtiger_server_ip">
<BR>
<B>IP do Serv. BD Vtiger - </B> É o endereço IP do servidor de banco de dados da instalação do Vtiger.

<BR>
<A NAME="settings-vtiger_dbname">
<BR>
<B>Base de Dados Vtiger - </B> Este é o nome da base de dados para o Vtiger.

<BR>
<A NAME="settings-vtiger_login">
<BR>
<B>Login do BD do Vtiger - </B> É o nome do usuário usado para acessar o BD do Vtiger.

<BR>
<A NAME="settings-vtiger_pass">
<BR>
<B>Senha do BD Vtiger - </B> É a senha usada para acessar o BD do Vtiger.

<BR>
<A NAME="settings-vtiger_url">
<BR>
<B>URL do Vtiger - </B> Esta é a URL ou endereço do site usado para acessar o Vtiger.


<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUS TABELA</FONT></B><BR><BR>
<A NAME="system_statuses">
<BR>
<B>Através do uso de status do sistema, você pode ter os status que existem para todas as campanhas e em grupos. O Estado deve ser 1-6 caracteres, a descrição deve ser 2-30 caracteres de comprimento e selecionável define se ele aparece no sistema como uma disposição do agente. O campo human_answered é usado no cálculo do percentual de queda, ou a taxa de abandono. Definir human_answered a Y vai usar esse status quando a contagem das chamadas humanos respondidas. A opção Categoria permite agrupar vários estados em uma categoria que pode ser usado para análise estatística. Existem também 5 configurações adicionais que irão definir o tipo de status: venda, dnc, contato com o cliente, não está interessado impraticável, callback, marcada.</B>



<BR><BR><BR><BR>

<B><FONT SIZE=3>SCREEN_LABELS TABELA</FONT></B><BR><BR>
<A NAME="screen_labels">
<BR>
<B>Screen labels give you the option of setting different labels for the default agent screen fields on a por campanha basis.</B>

<A NAME="screen_labels-label_id">
<BR>
<B>Label ID tela -</B> Este campo precisa ser pelo menos 2 caracteres de comprimento e um máximo de 20 caracteres de comprimento, sem espaços ou caracteres especiais. Esta é a identificação que irá ser utilizado para identificar o rótulo tela ao longo do sistema.

<BR>
<A NAME="screen_labels-label_name">
<BR>
<B>Nome do Rótulo tela -</B> Este é o nome descritivo da entrada rótulo tela.

<BR>
<A NAME="screen_labels-user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse registro, o que permite a visualização de administrador deste recoird restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário admin para ver este registro.

<BR>
<A NAME="screen_labels-label_hide_field_logs">
<BR>
<B>Ocultar Label em registros de chamadas -</B> Se um rótulo é definida como --- OCULTAR --- então os registros de chamadas de agentes, se habilitado na campanha, ainda vai mostrar o campo e os dados a menos que esta opção é definida para Y. O padrão é N.

<BR>
<A NAME="screen_labels-default_field_labels">
<BR>
<B>Os rótulos de campos padrão -</B> Esses 19 campos permitem que você defina o nome como ele aparecerá na interface do agente, bem como a página principal de modificação administrativa. O padrão é vazio que irá usar os padrões codificados na interface do agente. Você também pode definir um rótulo para --- --- ESCONDER para ocultar o rótulo eo campo.



<BR><BR><BR><BR>

<B><FONT SIZE=3>STATUS_CATEGORIES TABELA</FONT></B><BR><BR>
<A NAME="status_categories">
<BR>
<B>Através do uso de categoria de status de sistema, você pode agrupar status para permitir análise estatística em um grupo de status. O ID da categoria deve ter entre 2 e 20 caracteres sem espaços, o nome deve ter entre 2 e 50 caracteres, a descrição é opicional e o Mostrar TimeonVDAD define se o status deve ser um dos 4 status que podem ser calculados e mostrados no relatório em tempo real Time On VDAD .</B> A Categoria de Venda e a Categoria Registro Ruim são ambas usadas pelo sistema de Sugestão de Listas quando analisando as estatisticas da lista.



<BR><BR><BR><BR>
<?php
if ($SSallow_emails>0)
	{
?>
<B><FONT SIZE=3>EMAIL ACCOUNTS</FONT></B><BR><BR>
<A NAME="email_accounts">
<BR>
<B>A seção de gerenciamento de contas de e-mail permite que você criar, copiar e excluir as configurações da conta de e-mail que lhe permitirá ter mensagens de e-mail entram em seu sistema e ser tratados como se fossem telefonemas para agentes. Contas de email deve ser configurado por você e provedor de email - que não é coberta por este módulo.</B>

<BR>
<A NAME="email_accounts-email_account_id">
<BR>
<B>Email Conta ID -</B> Este é o nome curto da conta de e-mail, não é editável após a apresentação inicial, não deve conter espaços e deve ter entre 2 e 20 caracteres.

<BR>
<A NAME="email_accounts-email_account_name">
<BR>
<B>Nome da conta de e-mail -</B> Este é o nome completo da conta de e-mail, deve ser entre 2 e 30 caracteres. 

<BR>
<A NAME="email_accounts-email_account_active">
<BR>
<B>Ativo -</B> Isso determina se esta conta será verificado se há novas mensagens de e-mail para ser carregado no discador. 

<BR>
<A NAME="email_accounts-email_account_description">
<BR>
<B>Email Descrição da Conta -</B> This allows for a lengthy description, if needed, of the email account.  255 characters max. 

<BR>
<A NAME="email_accounts-email_account_type">
<BR>
<B>E-mail Tipo de Conta -</B> Specifies whether the account is used for inbound or outbound email messages.  Should be set to INBOUND. 

<BR>
<A NAME="email_accounts-admin_user_group">
<BR>
<B>Grupo de Usuários do Administrador -</B> Este é o grupo de usuário administrativo para esse grupo de entrada, o que permite a visualização de administrador isso no grupo restrito por grupo de usuários. O padrão é - ALL - que permite a qualquer usuário administrador para visualizar esta em grupo.  

<BR>
<A NAME="email_accounts-protocol">
<BR>
<B>Email Conta Protocolo -</B> This is the email protocol used by the account you are setting up access to.  Currently only IMAP and POP3 accounts are supported.  

<BR>
<A NAME="email_accounts-email_replyto_address">
<BR>
<B>E-mail Endereço de Resposta -</B> The email address of the account you are setting up access to.  Replies to email messages from the agent interface will read as coming from this address.  

<BR>
<A NAME="email_accounts-email_account_server">
<BR>
<B>Email conta do servidor -</B> O servidor de e-mail que a conta está alojado em.  

<BR>
<A NAME="email_accounts-email_account_user">
<BR>
<B>E-mail de Conta de Usuário -</B> The login used to access this account.  Usually it's the portion of the reply-to address before the -at- symbol.  

<BR>
<A NAME="email_accounts-email_account_pass">
<BR>
<B>E-mail a senha da conta -</B> The password used to access this account.  This is usually set at the time the email account is created.  

<BR>
<A NAME="email_accounts-email_frequency_check_mins">
<BR>
<B>Email Frequency Check Rate (mins) -</B> How often this email account should be checked.  The highest rate of frequency at the moment is five minutes; some email providers will not allow more than three login attempts in fifteen minutes before locking the account for an indeterminate amount of time.  

<BR>
<A NAME="email_accounts-in_group">
<BR>
<B>No Grupo ID -</B> O In-Group de que as mensagens de e-mail será enviado para.   

<BR>
<A NAME="email_accounts-default_list_id">
<BR>
<B>Padrão Lista de ID -</B> A lista de ID que leva será inserido, se necessário.  

<BR>
<A NAME="email_accounts-call_handle_method">
<BR>
<B>No Grupo Método identificador de chamada -</B> Esta é a ação que será tomada quando um novo e-mail encontra-se na conta. EMAIL significa que todas as mensagens de e-mail serão inseridos na tabela da lista como uma nova liderança. EMAILLOOKUP vai procurar em toda a tabela de lista para o endereço de e-mail na coluna e-mail - se a liderança for encontrado, que lista de chumbo ID será utilizado no registro que vai para a mesa de email_list. EMAILLOOKUPRC faz o mesmo, mas ela só vai procurar listas pertencentes à campanha selecionada na caixa ID da campanha do Grupo In-abaixo. EMAILLOOKUPRL só irá procurar uma lista específica, que é a que entrou em caixa Lista In-Grupo ID abaixo.  

<BR>
<A NAME="email_accounts-agent_search_method">
<BR>
<B>Agent No Grupo Método de Pesquisa -</B> O método de pesquisa agente a ser utilizado pelo grupo de entrada, LO é com Balanceamento de Carga-Transbordo e vai tentar enviar a chamada para um agente no servidor local antes de tentar enviá-lo a um agente em outro servidor, LB é com Balanceamento de Carga e vai tentar enviar a chamada para o próximo agente não importa o servidor no qual estão, por isso é servidor apenas e só vai tentar enviar as chamadas para os agentes no servidor que o convite veio em diante. O padrão é LB. <B>IS THIS NECESSARY?</B>  

<BR>
<A NAME="email_accounts-ingroup_list_id">
<BR>
<B>No Grupo Lista de ID -</B> Este é o ID da lista que irá ser utilizado para pesquisar uma correspondência dentro.  

<BR>
<A NAME="email_accounts-ingroup_campaign_id">
<BR>
<B>No Grupo ID Campanha -</B> Este é o ID da campanha, que serão utilizados para pesquisar para uma correspondência dentro.  




<BR><BR><BR><BR>
<?php } ?>
<B><FONT SIZE=3>CUSTOM TEMPLATE MAKER</FONT></B><BR><BR>
<A NAME="template_maker">
<BR>
A fabricante de modelo personalizado permite definir seus próprios layouts de arquivos para uso com o carregador de lista e também excluí-los, se necessário. Se você costuma fazer upload de arquivos que estão em um layout consistente que não seja o layout padrão, você pode encontrar esta ferramenta útil. O layout salvo irá funcionar em qualquer arquivo carregado ele corresponde, independentemente do tipo de arquivo ou delimitador.

<BR>
<A NAME="template_maker-create_template">
<BR>
<B>Criar um novo modelo - </B>In order to begin creating your new listloader template, you must first load a lead file that has the layout you wish to create the template for.  Click "Choose file", and open the file on your computer you wish to use.  This will upload a copy to your server and process it to determine the file type and delimiter (for TXT files).

<BR>
<A NAME="template_maker-delete_template">
<BR>
<B>Removertemplate -</B> If you have a template you no longer use or you mis-entered information on it and would like to re-enter it, select the template from the drop-down menu and click "Apagar Modelo".

<BR>
<A NAME="template_maker-template_id">
<BR>
<B>ID do Template -</B> This field is where you enter an arbitrary ID for your new custom template.  It must be between 2 and 20 characters and consist of alphanumeric characters and underscores.

<BR>
<A NAME="template_maker-template_name">
<BR>
<B>Nome do Template -</B> This field is where you enter the name for your new custom template.  Can be up to 30 characters long.

<BR>
<A NAME="template_maker-template_description">
<BR>
<B>Descrição template -</B> This field is where you enter the description for your new custom template.  It can be up to 255 characters long.

<BR>
<A NAME="template_maker-list_id">
<BR>
<B>ID da Lista -</B> All templates must load their records into a list.  Select a list ID to load leads into from this drop-down list, which will display any lists available to you given your user settings.

<BR>
<A NAME="template_maker-assign_columns">
<BR>
<B>Assigning columns -</B> Once you have loaded a sample lead file matching the layout you want to make into a template and select a list ID to load leads into, all of the available columns from the list table and the custom table for the list you selected (if any) will be displayed here.  Colunas highlighted in blue are standard columns from the list table.  Colunas highlighted in pink belong to the custom table for the selected list.  Each column listed has a drop-down menu, which should be populated with the fields from the first row of the sample lead file you uploaded.  Assign the appropriate fields to the appropriate columns and press Enviar modelo to create your template.  You do not need to assign every field to a column, and you do not need to assign every column a field.  For details on the standard list columns, click <a href="#list_loader">HERE</a>.


<BR><BR><BR><BR>

<A NAME="max_stats">
<B><FONT SIZE=3>Máxima do sistema Relatórios Estatísticas</FONT></B><BR><BR>
<BR>
<B>Estas estatísticas são totais que são armazenados em cache ao longo de cada dia em tempo real através de processos de back-end. Para chamadas de entrada, as chamadas totais por in-grupo são calculadas para cada chamada que entra no processo que calcula. Para a contagem de todo o sistema, os totais são gerados a partir de entradas de registro, bem como outros totais em grupo e da campanha. Esses totais podem não somar devido às configurações que você tem em seu sistema, bem como quando a chamada é desligada.</B>


<?php
if ($SSqc_features_active > 0)
	{
	?>
	<BR><BR><BR><BR>

	<B><FONT SIZE=3>QC STATUS CODES</FONT></B><BR><BR>
	<A NAME="qc_status_codes">
	<BR>
	<B>O Controle de Qualidade - função QC tem seu próprio conjunto de códigos de status de separar aqueles dentro da chamada funções do sistema de manuseio. Códigos de status de CQ deve estar entre 2 e 8 caracteres de comprimento e não contêm caracteres especiais, como um espaço ou dois pontos. O QC descrição código de status deve estar entre 2 e 30 caracteres de comprimento. Para estas funções funcionem, você deve ter QC ativado nas configurações do sistema.</B>
	<?php
	}
?>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Relatórios</FONT></B><BR><BR>

<A NAME="agent_time_detail">
<BR>
<B>Agente Tiempo Detalle -</B> In this report you can view how much time agents spent on what.<BR>
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
<B>Agent Status Detalhes -</B> In this report you can view what and how many statuses has been selected by the agents.<BR>
<U>CALLS</U> = Totalnumber of calls sent to the user.<BR>
<U>CIcalls</U> = Totalnumber of call where there was a Resposta Humana which is set under "Admin" -> "System Status".<BR>
<U>DNC/CI%</U> = How much in percent DNC (Do Not Call) per Resposta Humanas.<BR>
And the rest is justStatus do Sistemathat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Status".<BR>

<A NAME="agent_performance_detail">
<BR>
<B>Agent Performance Detalhes -</B> This is a combination of Agente Tiempo Detalle and Agent Status Detalhes.<BR>
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
And the rest is justStatus do Sistemathat the agent picked and how many, to find out what they means then head over to "Admin" -> "System Status".<BR>
- Next table is Códigos de Pausa.<BR>
<U>TOTAL</U> = Totaltime on the system (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U> + <U>PAUSE</U>).<BR>
<U>NONPAUSE</U> = Everything except pause (<U>WAIT</U> + <U>TALK</U> + <U>DISPO</U>).<BR>
<U>PAUSE</U> = Only Pause.<BR>
- The last table is pause codes and their time (like "Agente Tiempo Detalle").<BR>
<U>LOGIN</U> = The pause code when going from login directly to pause.<BR>
<U>LAGGED</U> = The time the agent had some network problem or similar.<BR>
<U>ANDIAL</U> = This pause code triggers if the agent been on dispo screen for longer than 1000 seconds.<BR>
and empty is undefined pause code. <BR>


<BR><BR><BR><BR>
<B><FONT SIZE=3>Nanpa cellphone filtering</FONT></B><BR><BR>


<A NAME="nanpa-running">
<BR>
<B>Currently running NANPA scrubs -</B> Mostrars a log of the currently running scrubs, including: Start Time, Leads Count, Filter Count, Status Line, Time to Complete, Field Updated, and Field excluded
<BR><BR>
<A NAME="nanpa-settings">
<BR>
<B>Inactive Listas -</B> Contains all the listas inativas, that are eligible to be scrubbed.  A list can only be scrubbed if Ativois set to N on the list.
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
FIM
</TD></TR></TABLE></BODY></HTML>
<?php
exit;

#### END HELP SCREENS
