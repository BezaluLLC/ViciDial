ALTER TABLE vicidial_nanpa_prefix_codes ADD city VARCHAR(50) default '';
ALTER TABLE vicidial_nanpa_prefix_codes ADD state VARCHAR(2) default '';
ALTER TABLE vicidial_nanpa_prefix_codes ADD postal_code VARCHAR(10) default '';
ALTER TABLE vicidial_nanpa_prefix_codes ADD country VARCHAR(2) default '';

UPDATE system_settings SET db_schema_version='1136', version='2.2.0b0.5';

ALTER TABLE vicidial_users ADD delete_from_dnc ENUM('0','1') default '0';

ALTER TABLE vicidial_campaigns ADD vtiger_search_dead ENUM('DISABLED','ASK','RESURRECT') default 'ASK';
ALTER TABLE vicidial_campaigns ADD vtiger_status_call ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaigns MODIFY vtiger_screen_login ENUM('Y','N','NEW_WINDOW') default 'Y';
ALTER TABLE vicidial_campaigns MODIFY vtiger_create_call_record ENUM('Y','N','DISPO') default 'Y';

ALTER TABLE vicidial_statuses ADD sale ENUM('Y','N') default 'N';
ALTER TABLE vicidial_statuses ADD dnc ENUM('Y','N') default 'N';
ALTER TABLE vicidial_statuses ADD customer_contact ENUM('Y','N') default 'N';
ALTER TABLE vicidial_statuses ADD not_interested ENUM('Y','N') default 'N';
ALTER TABLE vicidial_statuses ADD unworkable ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaign_statuses ADD sale ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaign_statuses ADD dnc ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaign_statuses ADD customer_contact ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaign_statuses ADD not_interested ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaign_statuses ADD unworkable ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1137';

ALTER TABLE vicidial_users ADD email VARCHAR(100) default '';
ALTER TABLE vicidial_users ADD user_code VARCHAR(100) default '';
ALTER TABLE vicidial_users ADD territory VARCHAR(100) default '';

UPDATE system_settings SET db_schema_version='1138';

ALTER TABLE vicidial_campaigns ADD survey_third_digit VARCHAR(1) default '';
ALTER TABLE vicidial_campaigns ADD survey_third_audio_file VARCHAR(50) default 'US_thanks_no_contact';
ALTER TABLE vicidial_campaigns ADD survey_third_status VARCHAR(6) default 'NI';
ALTER TABLE vicidial_campaigns ADD survey_third_exten VARCHAR(20) default '8300';
ALTER TABLE vicidial_campaigns ADD survey_fourth_digit VARCHAR(1) default '';
ALTER TABLE vicidial_campaigns ADD survey_fourth_audio_file VARCHAR(50) default 'US_thanks_no_contact';
ALTER TABLE vicidial_campaigns ADD survey_fourth_status VARCHAR(6) default 'NI';
ALTER TABLE vicidial_campaigns ADD survey_fourth_exten VARCHAR(20) default '8300';

ALTER TABLE system_settings ADD enable_tts_integration ENUM('0','1') default '0';

CREATE TABLE vicidial_tts_prompts (
tts_id VARCHAR(50) PRIMARY KEY NOT NULL,
tts_name VARCHAR(100),
active ENUM('Y','N'),
tts_text TEXT
);

UPDATE system_settings SET db_schema_version='1139';

CREATE TABLE vicidial_call_menu (
menu_id VARCHAR(50) PRIMARY KEY NOT NULL,
menu_name VARCHAR(100),
menu_prompt VARCHAR(100),
menu_timeout SMALLINT(2) UNSIGNED default '10',
menu_timeout_prompt VARCHAR(100) default 'NONE',
menu_invalid_prompt VARCHAR(100) default 'NONE',
menu_repeat TINYINT(1) UNSIGNED default '0',
menu_time_check ENUM('0','1') default '0',
call_time_id VARCHAR(20) default '',
track_in_vdac ENUM('0','1') default '1'
);

CREATE TABLE vicidial_call_menu_options (
menu_id VARCHAR(50) NOT NULL,
option_value VARCHAR(20) NOT NULL default '',
option_description VARCHAR(255) default '',
option_route VARCHAR(20),
option_route_value VARCHAR(100),
option_route_value_context VARCHAR(100),
index (menu_id),
unique index menuoption (menu_id, option_value)
);

ALTER TABLE vicidial_inbound_dids MODIFY did_route ENUM('EXTEN','VOICEMAIL','AGENT','PHONE','IN_GROUP','CALLMENU') default 'EXTEN';
ALTER TABLE vicidial_inbound_dids ADD menu_id VARCHAR(50) default '';

UPDATE system_settings SET db_schema_version='1140';

ALTER TABLE system_settings ADD agentonly_callback_campaign_lock ENUM('0','1') default '1';

UPDATE system_settings SET db_schema_version='1141';

ALTER TABLE system_settings ADD sounds_central_control_active ENUM('0','1') default '0';
ALTER TABLE system_settings ADD sounds_web_server VARCHAR(15) default '127.0.0.1';
ALTER TABLE system_settings ADD sounds_web_directory VARCHAR(255) default '';

ALTER TABLE servers ADD sounds_update ENUM('Y','N') default 'N';

CREATE TABLE vicidial_user_territories (
user VARCHAR(20) NOT NULL,
territory VARCHAR(100) default '',
index (user),
unique index userterritory (user, territory)
);

UPDATE system_settings SET db_schema_version='1142';

ALTER TABLE system_settings ADD active_voicemail_server VARCHAR(15) default '';
ALTER TABLE system_settings ADD auto_dial_limit VARCHAR(5) default '4';

UPDATE system_settings SET db_schema_version='1143';

CREATE TABLE vicidial_territories (
territory_id MEDIUMINT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
territory VARCHAR(100) default '',
territory_description VARCHAR(255) default '',
unique index uniqueterritory (territory)
);

ALTER TABLE vicidial_user_territories ADD level ENUM('TOP_AGENT','STANDARD_AGENT','BOTTOM_AGENT') default 'STANDARD_AGENT';

ALTER TABLE system_settings ADD user_territories_active ENUM('0','1') default '0';

UPDATE system_settings SET db_schema_version='1144';

ALTER TABLE servers ADD vicidial_recording_limit MEDIUMINT(8) default '60';

ALTER TABLE phones ADD phone_context VARCHAR(20) default 'default';

UPDATE system_settings SET db_schema_version='1145';

CREATE UNIQUE INDEX extenserver ON phones (extension, server_ip);

UPDATE system_settings SET db_schema_version='1146';

CREATE TABLE vicidial_override_ids (
id_table VARCHAR(50) PRIMARY KEY NOT NULL,
active ENUM('0','1') default '0',
value INT(9) default '0'
);

INSERT INTO vicidial_override_ids(id_table,active,value) values('vicidial_users','0','1000');
INSERT INTO vicidial_override_ids(id_table,active,value) values('vicidial_campaigns','0','20000');
INSERT INTO vicidial_override_ids(id_table,active,value) values('vicidial_inbound_groups','0','30000');
INSERT INTO vicidial_override_ids(id_table,active,value) values('vicidial_lists','0','40000');
INSERT INTO vicidial_override_ids(id_table,active,value) values('vicidial_call_menu','0','50000');
INSERT INTO vicidial_override_ids(id_table,active,value) values('vicidial_user_groups','0','60000');
INSERT INTO vicidial_override_ids(id_table,active,value) values('vicidial_lead_filters','0','70000');
INSERT INTO vicidial_override_ids(id_table,active,value) values('vicidial_scripts','0','80000');
INSERT INTO vicidial_override_ids(id_table,active,value) values('phones','0','100');

ALTER TABLE vicidial_campaigns MODIFY disable_alter_custphone ENUM('Y','N','HIDE') default 'Y';

UPDATE system_settings SET db_schema_version='1147';

CREATE TABLE vicidial_carrier_log (
uniqueid VARCHAR(20) PRIMARY KEY NOT NULL,
call_date DATETIME,
server_ip VARCHAR(15) NOT NULL,
lead_id INT(9) UNSIGNED,
hangup_cause TINYINT(1) UNSIGNED default '0',
dialstatus VARCHAR(16),
channel VARCHAR(100),
dial_time SMALLINT(2) UNSIGNED default '0',
index (call_date)
);

ALTER TABLE servers ADD carrier_logging_active ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1148';

ALTER TABLE vicidial_campaigns MODIFY adaptive_dropped_percentage VARCHAR(4) default '3';

INSERT INTO vicidial_statuses values('AB','Busy Auto','N','N','UNDEFINED','N','N','N','N','N');
INSERT INTO vicidial_statuses values('ADC','Disconnected Number Auto','N','N','UNDEFINED','N','N','N','N','Y');

UPDATE system_settings SET db_schema_version='1149';

ALTER TABLE vicidial_campaigns ADD drop_lockout_time VARCHAR(6) default '0';

UPDATE system_settings SET db_schema_version='1150';

ALTER TABLE vicidial_live_agents ADD agent_log_id INT(9) UNSIGNED default '0';

UPDATE system_settings SET db_schema_version='1151';

ALTER TABLE system_settings ADD allow_custom_dialplan ENUM('0','1') default '0';

ALTER TABLE vicidial_call_menu ADD custom_dialplan_entry TEXT;

ALTER TABLE phones ADD phone_ring_timeout SMALLINT(3) default '60';

UPDATE system_settings SET db_schema_version='1152';

ALTER TABLE vicidial_call_menu MODIFY menu_prompt VARCHAR(255);
ALTER TABLE vicidial_call_menu MODIFY menu_timeout_prompt VARCHAR(255) default 'NONE';
ALTER TABLE vicidial_call_menu MODIFY menu_invalid_prompt VARCHAR(255) default 'NONE';

ALTER TABLE vicidial_call_menu_options MODIFY option_route_value VARCHAR(255);

UPDATE system_settings SET db_schema_version='1153';

ALTER TABLE phones ADD conf_secret VARCHAR(20) default 'test';
UPDATE phones set conf_secret=pass;

UPDATE system_settings SET db_schema_version='1154';

ALTER TABLE vicidial_call_menu ADD tracking_group VARCHAR(20) default 'CALLMENU';

UPDATE system_settings SET db_schema_version='1155';

ALTER TABLE vicidial_inbound_groups MODIFY after_hours_message_filename VARCHAR(255) default 'vm-goodbye';
ALTER TABLE vicidial_inbound_groups MODIFY welcome_message_filename VARCHAR(255) default '---NONE---';
ALTER TABLE vicidial_inbound_groups MODIFY onhold_prompt_filename VARCHAR(255) default 'generic_hold';
ALTER TABLE vicidial_inbound_groups MODIFY hold_time_option_callback_filename VARCHAR(255) default 'vm-hangup';
ALTER TABLE vicidial_inbound_groups MODIFY agent_alert_exten VARCHAR(100) default 'ding';

UPDATE system_settings SET db_schema_version='1156';

ALTER TABLE vicidial_inbound_groups ADD no_agent_no_queue ENUM('N','Y','NO_PAUSED') default 'N';
ALTER TABLE vicidial_inbound_groups ADD no_agent_action ENUM('CALLMENU','INGROUP','DID','MESSAGE','EXTENSION','VOICEMAIL') default 'MESSAGE';
ALTER TABLE vicidial_inbound_groups ADD no_agent_action_value VARCHAR(255) default 'nbdy-avail-to-take-call|vm-goodbye';

ALTER TABLE vicidial_closer_log MODIFY term_reason  ENUM('CALLER','AGENT','QUEUETIMEOUT','ABANDON','AFTERHOURS','HOLDRECALLXFER','HOLDTIME','NOAGENT','NONE') default 'NONE';

UPDATE system_settings SET db_schema_version='1157';

CREATE TABLE vicidial_list_update_log (
event_date DATETIME,
lead_id INT(9) UNSIGNED,
vendor_id VARCHAR(20),
phone_number VARCHAR(20),
status VARCHAR(6),
old_status VARCHAR(6),
filename VARCHAR(255) default '',
result VARCHAR(20),
result_rows SMALLINT(3) UNSIGNED default '0',
index (event_date)
);

ALTER TABLE vicidial_campaigns ADD quick_transfer_button ENUM('N','IN_GROUP','PRESET_1','PRESET_2') default 'N';
ALTER TABLE vicidial_campaigns ADD prepopulate_transfer_preset ENUM('N','PRESET_1','PRESET_2') default 'N';

UPDATE system_settings SET db_schema_version='1158';

CREATE TABLE vicidial_drop_rate_groups (
group_id VARCHAR(20) PRIMARY KEY NOT NULL,
update_time TIMESTAMP,
calls_today INT(9) UNSIGNED default '0',
answers_today INT(9) UNSIGNED default '0',
drops_today INT(9) UNSIGNED default '0',
drops_today_pct VARCHAR(6) default '0',
drops_answers_today_pct VARCHAR(6) default '0'
);

INSERT INTO vicidial_drop_rate_groups SET group_id='101';
INSERT INTO vicidial_drop_rate_groups SET group_id='102';
INSERT INTO vicidial_drop_rate_groups SET group_id='103';
INSERT INTO vicidial_drop_rate_groups SET group_id='104';
INSERT INTO vicidial_drop_rate_groups SET group_id='105';
INSERT INTO vicidial_drop_rate_groups SET group_id='106';
INSERT INTO vicidial_drop_rate_groups SET group_id='107';
INSERT INTO vicidial_drop_rate_groups SET group_id='108';
INSERT INTO vicidial_drop_rate_groups SET group_id='109';
INSERT INTO vicidial_drop_rate_groups SET group_id='110';

ALTER TABLE vicidial_campaigns ADD drop_rate_group VARCHAR(20) default 'DISABLED';

UPDATE system_settings SET db_schema_version='1159';

CREATE TABLE vicidial_process_triggers (
trigger_id VARCHAR(20) PRIMARY KEY NOT NULL,
trigger_name VARCHAR(100),
server_ip VARCHAR(15) NOT NULL,
trigger_time DATETIME,
trigger_run ENUM('0','1') default '0',
user VARCHAR(20),
trigger_lines TEXT
);

CREATE TABLE vicidial_process_trigger_log (
trigger_id VARCHAR(20) NOT NULL,
server_ip VARCHAR(15) NOT NULL,
trigger_time DATETIME,
user VARCHAR(20),
trigger_lines TEXT,
trigger_results TEXT,
index (trigger_id),
index (trigger_time)
);

INSERT INTO vicidial_process_triggers SET trigger_id='LOAD_LEADS',server_ip='10.10.10.15',trigger_name='Load Leads',trigger_time='2009-01-01 00:00:00',trigger_run='0',trigger_lines='/usr/share/astguiclient/VICIDIAL_IN_new_leads_file.pl';

UPDATE system_settings SET db_schema_version='1160';

ALTER TABLE vicidial_user_groups ADD agent_status_viewable_groups TEXT;
ALTER TABLE vicidial_user_groups ADD agent_status_view_time ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1161';

ALTER TABLE vicidial_campaigns ADD view_calls_in_queue ENUM('NONE','ALL','1','2','3','4','5') default 'NONE';
ALTER TABLE vicidial_campaigns ADD view_calls_in_queue_launch ENUM('AUTO','MANUAL') default 'MANUAL';
ALTER TABLE vicidial_campaigns ADD grab_calls_in_queue ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaigns ADD call_requeue_button ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaigns ADD pause_after_each_call ENUM('Y','N') default 'N';

ALTER TABLE vicidial_auto_calls ADD agent_grab VARCHAR(20) default '';

UPDATE system_settings SET db_schema_version='1162';

ALTER TABLE vicidial_list MODIFY list_id BIGINT(14) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE vicidial_list ADD rank SMALLINT(5) NOT NULL default '0';
ALTER TABLE vicidial_list ADD owner VARCHAR(20) default '';
CREATE INDEX rank ON vicidial_list (rank);

ALTER TABLE vicidial_campaigns ADD no_hopper_dialing ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaigns ADD agent_dial_owner_only ENUM('NONE','USER','TERRITORY','USER_GROUP') default 'NONE';

ALTER TABLE vicidial_lists ADD reset_time VARCHAR(100) default '';

UPDATE system_settings SET db_schema_version='1163';
