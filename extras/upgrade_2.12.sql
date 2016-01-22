UPDATE system_settings SET db_schema_version='1404',version='2.11rc1tk',db_schema_update_date=NOW() where db_schema_version < 1404;

UPDATE system_settings SET db_schema_version='1405',version='2.12b0.5',db_schema_update_date=NOW() where db_schema_version < 1405;

ALTER TABLE system_settings ADD meetme_enter_login_filename VARCHAR(255) default '';
ALTER TABLE system_settings ADD meetme_enter_leave3way_filename VARCHAR(255) default '';

UPDATE system_settings SET db_schema_version='1406',db_schema_update_date=NOW() where db_schema_version < 1406;

ALTER TABLE system_settings ADD enable_did_entry_list_id ENUM('0','1') default '0';

ALTER TABLE vicidial_inbound_dids ADD entry_list_id BIGINT(14) UNSIGNED default '0';
ALTER TABLE vicidial_inbound_dids ADD filter_entry_list_id BIGINT(14) UNSIGNED default '0';

UPDATE system_settings SET db_schema_version='1407',db_schema_update_date=NOW() where db_schema_version < 1407;

ALTER TABLE system_settings ADD enable_third_webform ENUM('0','1') default '0';

ALTER TABLE vicidial_campaigns ADD web_form_address_three TEXT;
ALTER TABLE vicidial_lists ADD web_form_address_three TEXT;
ALTER TABLE vicidial_inbound_groups ADD web_form_address_three TEXT;

ALTER TABLE vicidial_campaigns MODIFY get_call_launch ENUM('NONE','SCRIPT','WEBFORM','WEBFORMTWO','WEBFORMTHREE','FORM') default 'NONE';
ALTER TABLE vicidial_inbound_groups MODIFY get_call_launch ENUM('NONE','SCRIPT','WEBFORM','WEBFORMTWO','WEBFORMTHREE','FORM') default 'NONE';

UPDATE system_settings SET db_schema_version='1408',db_schema_update_date=NOW() where db_schema_version < 1408;

ALTER TABLE vicidial_users ADD api_list_restrict ENUM('1','0') default '0';

UPDATE system_settings SET db_schema_version='1409',db_schema_update_date=NOW() where db_schema_version < 1409;

ALTER TABLE vicidial_users ADD api_allowed_functions VARCHAR(1000) default ' ALL_FUNCTIONS ';

UPDATE system_settings SET db_schema_version='1410',db_schema_update_date=NOW() where db_schema_version < 1410;

ALTER TABLE vicidial_email_accounts ADD pop3_auth_mode ENUM('BEST','PASS','APOP','CRAM-MD5') default 'BEST' AFTER email_account_pass;

UPDATE system_settings SET db_schema_version='1411',db_schema_update_date=NOW() where db_schema_version < 1411;

ALTER TABLE system_settings ADD chat_url VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE system_settings ADD chat_timeout INT(3) UNSIGNED DEFAULT NULL;

ALTER TABLE vicidial_inbound_groups MODIFY group_handling ENUM('PHONE','EMAIL','CHAT') default 'PHONE';

CREATE TABLE vicidial_chat_archive (
chat_id INT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
chat_start_time DATETIME DEFAULT NULL,
status VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
chat_creator VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
group_id VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
lead_id INT(9) UNSIGNED DEFAULT NULL,
PRIMARY KEY (chat_id),
KEY vicidial_chat_archive_lead_id_key (lead_id),
KEY vicidial_chat_archive_start_time_key (chat_start_time)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE vicidial_chat_log (
message_row_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
chat_id VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
message MEDIUMTEXT COLLATE utf8_unicode_ci,
message_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
poster VARCHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
chat_member_name VARCHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
chat_level ENUM('0','1') COLLATE utf8_unicode_ci DEFAULT '0',
PRIMARY KEY (message_row_id),
KEY vicidial_chat_log_user_key (poster)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE vicidial_chat_log_archive (
message_row_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
chat_id VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
message MEDIUMTEXT COLLATE utf8_unicode_ci,
message_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
poster VARCHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
chat_member_name VARCHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
chat_level ENUM('0','1') COLLATE utf8_unicode_ci DEFAULT '0',
PRIMARY KEY (message_row_id),
KEY vicidial_chat_log_archive_user_key (poster)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE vicidial_chat_participants (
chat_participant_id INT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
chat_id INT(9) UNSIGNED DEFAULT NULL,
chat_member VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
chat_member_name VARCHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
ping_date DATETIME DEFAULT NULL,
vd_agent ENUM('Y','N') COLLATE utf8_unicode_ci DEFAULT 'N',
PRIMARY KEY (chat_participant_id),
UNIQUE KEY vicidial_chat_participants_key (chat_id,chat_member)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE vicidial_live_chats (
chat_id INT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
chat_start_time DATETIME DEFAULT NULL,
status VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
chat_creator VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
group_id VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
lead_id INT(9) UNSIGNED DEFAULT NULL,
PRIMARY KEY (chat_id),
KEY vicidial_live_chats_lead_id_key (lead_id),
KEY vicidial_live_chats_start_time_key (chat_start_time)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE vicidial_manager_chat_log (
manager_chat_message_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
manager_chat_id INT(10) UNSIGNED DEFAULT NULL,
manager_chat_subid TINYINT(3) UNSIGNED DEFAULT NULL,
manager VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
user VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
message MEDIUMTEXT COLLATE utf8_unicode_ci,
message_date DATETIME DEFAULT NULL,
message_viewed_date DATETIME DEFAULT NULL,
message_posted_by VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
audio_alerted ENUM('Y','N') COLLATE utf8_unicode_ci DEFAULT 'N',
PRIMARY KEY (manager_chat_message_id)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE vicidial_manager_chat_log_archive (
manager_chat_message_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
manager_chat_id INT(10) UNSIGNED DEFAULT NULL,
manager_chat_subid TINYINT(3) UNSIGNED DEFAULT NULL,
manager VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
user VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
message MEDIUMTEXT COLLATE utf8_unicode_ci,
message_date DATETIME DEFAULT NULL,
message_viewed_date DATETIME DEFAULT NULL,
message_posted_by VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
audio_alerted ENUM('Y','N') COLLATE utf8_unicode_ci DEFAULT 'N',
PRIMARY KEY (manager_chat_message_id)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE vicidial_manager_chats (
manager_chat_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
chat_start_date DATETIME DEFAULT NULL,
manager VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
selected_agents MEDIUMTEXT COLLATE utf8_unicode_ci,
selected_user_groups MEDIUMTEXT COLLATE utf8_unicode_ci,
selected_campaigns MEDIUMTEXT COLLATE utf8_unicode_ci,
allow_replies ENUM('Y','N') COLLATE utf8_unicode_ci DEFAULT 'N',
PRIMARY KEY (manager_chat_id)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE vicidial_manager_chats_archive (
manager_chat_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
chat_start_date DATETIME DEFAULT NULL,
manager VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
selected_agents MEDIUMTEXT COLLATE utf8_unicode_ci,
selected_user_groups MEDIUMTEXT COLLATE utf8_unicode_ci,
selected_campaigns MEDIUMTEXT COLLATE utf8_unicode_ci,
allow_replies ENUM('Y','N') COLLATE utf8_unicode_ci DEFAULT 'N',
PRIMARY KEY (manager_chat_id)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1412',db_schema_update_date=NOW() where db_schema_version < 1412;

ALTER TABLE vicidial_campaigns ADD manual_dial_override_field ENUM('ENABLED','DISABLED') default 'ENABLED';

UPDATE system_settings SET db_schema_version='1413',db_schema_update_date=NOW() where db_schema_version < 1413;

ALTER TABLE vicidial_campaigns ADD status_display_ingroup ENUM('ENABLED','DISABLED') default 'ENABLED';

ALTER TABLE vicidial_inbound_groups ADD populate_lead_ingroup ENUM('ENABLED','DISABLED') default 'ENABLED';

ALTER TABLE vicidial_scripts ADD script_color VARCHAR(7) default 'white';

UPDATE system_settings SET db_schema_version='1414',db_schema_update_date=NOW() where db_schema_version < 1414;

ALTER TABLE vicidial_campaigns ADD customer_gone_seconds SMALLINT(5) UNSIGNED default '30';

UPDATE system_settings SET db_schema_version='1415',db_schema_update_date=NOW() where db_schema_version < 1415;

UPDATE vicidial_inbound_groups set group_handling='PHONE' where group_handling='';

UPDATE system_settings SET db_schema_version='1416',db_schema_update_date=NOW() where db_schema_version < 1416;

ALTER TABLE vicidial_campaigns MODIFY lead_filter_id VARCHAR(20) default 'NONE';

ALTER TABLE vicidial_lead_filters MODIFY lead_filter_id VARCHAR(20) NOT NULL;

ALTER TABLE vicidial_users ADD lead_filter_id VARCHAR(20) default 'NONE';

UPDATE system_settings SET db_schema_version='1417',db_schema_update_date=NOW() where db_schema_version < 1417;

ALTER TABLE vicidial_inbound_dids ADD max_queue_ingroup_calls SMALLINT(5) default '0';
ALTER TABLE vicidial_inbound_dids ADD max_queue_ingroup_id VARCHAR(20) default '';
ALTER TABLE vicidial_inbound_dids ADD max_queue_ingroup_extension VARCHAR(50) default '9998811112';

UPDATE system_settings SET db_schema_version='1418',db_schema_update_date=NOW() where db_schema_version < 1418;

CREATE TABLE vicidial_url_multi (
url_id INT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
campaign_id VARCHAR(20) NOT NULL,
entry_type ENUM('campaign','ingroup','list','') default '',
active ENUM('Y','N') default 'N',
url_type ENUM('dispo','start','addlead','noagent','') default '',
url_rank SMALLINT(5) default '1',
url_statuses VARCHAR(1000) default '',
url_description VARCHAR(255) default '',
url_address TEXT,
PRIMARY KEY (url_id),
KEY vicidial_url_multi_campaign_id_key (campaign_id)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1419',db_schema_update_date=NOW() where db_schema_version < 1419;

CREATE TABLE vicidial_dtmf_log (
dtmf_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
dtmf_time DATETIME,
channel VARCHAR(100) NOT NULL,
server_ip VARCHAR(15) NOT NULL,
uniqueid VARCHAR(20) default '',
digit VARCHAR(1) default '',
direction ENUM('Received','Sent') default 'Received',
state ENUM('BEGIN','END') default 'BEGIN',
PRIMARY KEY (dtmf_id),
KEY vicidial_dtmf_uniqueid_key (uniqueid)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1420',db_schema_update_date=NOW() where db_schema_version < 1420;

CREATE TABLE vicidial_ajax_log (
user VARCHAR(20) default '',
start_time DATETIME NOT NULL,
db_time DATETIME NOT NULL,
run_time VARCHAR(20) default '0',
php_script VARCHAR(40) NOT NULL,
action VARCHAR(100) default '',
lead_id INT(10) UNSIGNED default '0',
stage VARCHAR(100) default '',
session_name VARCHAR(40) default '',
last_sql TEXT,
KEY ajax_dbtime_key (db_time)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE system_settings ADD agent_debug_logging VARCHAR(20) default '0';

UPDATE system_settings SET db_schema_version='1421',db_schema_update_date=NOW() where db_schema_version < 1421;

ALTER TABLE vicidial_campaigns ADD agent_display_fields VARCHAR(50) default '';

UPDATE system_settings SET db_schema_version='1422',db_schema_update_date=NOW() where db_schema_version < 1422;

ALTER TABLE system_settings ADD default_language VARCHAR(100) default 'default English';

UPDATE system_settings SET db_schema_version='1423',db_schema_update_date=NOW() where db_schema_version < 1423;

ALTER TABLE vicidial_campaigns MODIFY lead_order_secondary ENUM('LEAD_ASCEND','LEAD_DESCEND','CALLTIME_ASCEND','CALLTIME_DESCEND','VENDOR_ASCEND','VENDOR_DESCEND') default 'LEAD_ASCEND';

UPDATE system_settings SET db_schema_version='1424',db_schema_update_date=NOW() where db_schema_version < 1424;

ALTER TABLE system_settings ADD agent_whisper_enabled ENUM('0','1') default '0';

UPDATE servers SET rebuild_conf_files='Y' where active_asterisk_server='Y';

UPDATE system_settings SET db_schema_version='1425',db_schema_update_date=NOW() where db_schema_version < 1425;

ALTER TABLE vicidial_inbound_groups ADD drop_lead_reset ENUM('Y','N') default 'N';
ALTER TABLE vicidial_inbound_groups ADD after_hours_lead_reset ENUM('Y','N') default 'N';
ALTER TABLE vicidial_inbound_groups ADD nanq_lead_reset ENUM('Y','N') default 'N';
ALTER TABLE vicidial_inbound_groups ADD wait_time_lead_reset ENUM('Y','N') default 'N';
ALTER TABLE vicidial_inbound_groups ADD hold_time_lead_reset ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1426',db_schema_update_date=NOW() where db_schema_version < 1426;

CREATE TABLE vicidial_settings_containers (
container_id VARCHAR(40) PRIMARY KEY NOT NULL,
container_notes VARCHAR(255) default '',
container_type ENUM('OTHER','PERL_CLI','EMAIL_TEMPLATE') default 'OTHER',
user_group VARCHAR(20) default '---ALL---',
container_entry MEDIUMTEXT
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1427',db_schema_update_date=NOW() where db_schema_version < 1427;

ALTER TABLE vicidial_lists_fields ADD field_encrypt ENUM('Y','N') default 'N';
ALTER TABLE vicidial_lists_fields ADD field_show_hide ENUM('DISABLED','X_OUT_ALL','LAST_1','LAST_2','LAST_3','LAST_4','FIRST_1_LAST_4') default 'DISABLED';

ALTER TABLE vicidial_users ADD admin_cf_show_hidden ENUM('1','0') default '0';

UPDATE system_settings SET db_schema_version='1428',db_schema_update_date=NOW() where db_schema_version < 1428;

ALTER TABLE vicidial_users ADD agentcall_chat ENUM('1','0') default '0';

UPDATE system_settings SET db_schema_version='1429',db_schema_update_date=NOW() where db_schema_version < 1429;

ALTER TABLE system_settings ADD user_hide_realtime_enabled ENUM('0','1') default '0';

ALTER TABLE vicidial_users ADD user_hide_realtime ENUM('1','0') default '0';

UPDATE system_settings SET db_schema_version='1430',db_schema_update_date=NOW() where db_schema_version < 1430;

ALTER TABLE vicidial_inbound_dids ADD did_carrier_description VARCHAR(255) default '';

UPDATE system_settings SET db_schema_version='1431',db_schema_update_date=NOW() where db_schema_version < 1431;

CREATE TABLE vicidial_dnc_log (
phone_number VARCHAR(18) NOT NULL,
campaign_id VARCHAR(8) NOT NULL,
action ENUM('add','delete') default 'add',
action_date DATETIME NOT NULL,
user VARCHAR(20) default '',
index (phone_number)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1432',db_schema_update_date=NOW() where db_schema_version < 1432;

CREATE TABLE vicidial_status_groups (
status_group_id VARCHAR(20) PRIMARY KEY NOT NULL,
status_group_notes VARCHAR(255) default '',
user_group VARCHAR(20) default '---ALL---'
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE vicidial_campaign_statuses MODIFY campaign_id VARCHAR(20);

ALTER TABLE vicidial_lists ADD status_group_id VARCHAR(20) default '';

ALTER TABLE vicidial_inbound_groups ADD status_group_id VARCHAR(20) default '';

ALTER TABLE vicidial_campaign_statuses ADD min_sec INT(5) UNSIGNED default '0';
ALTER TABLE vicidial_campaign_statuses ADD max_sec INT(5) UNSIGNED default '0';

ALTER TABLE vicidial_statuses ADD min_sec INT(5) UNSIGNED default '0';
ALTER TABLE vicidial_statuses ADD max_sec INT(5) UNSIGNED default '0';

ALTER TABLE system_settings ADD custom_reports_use_slave_db VARCHAR(2000) default '';

ALTER TABLE vicidial_user_groups ADD allowed_custom_reports VARCHAR(2000) default '';

CREATE TABLE vicidial_custom_reports (
custom_report_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
report_name VARCHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
date_added DATETIME DEFAULT NULL,
user VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
domain VARCHAR(70) COLLATE utf8_unicode_ci DEFAULT NULL,
path_name VARCHAR(100) COLLATE utf8_unicode_ci DEFAULT NULL,
date_modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
user_modify VARCHAR(20) COLLATE utf8_unicode_ci DEFAULT NULL,
PRIMARY KEY (custom_report_id),
UNIQUE KEY custom_report_name_key (report_name)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1433',db_schema_update_date=NOW() where db_schema_version < 1433;

ALTER TABLE system_settings ADD usacan_phone_dialcode_fix ENUM('0','1') default '0';

UPDATE system_settings SET db_schema_version='1434',db_schema_update_date=NOW() where db_schema_version < 1434;

CREATE TABLE vicidial_amm_multi (
amm_id INT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
campaign_id VARCHAR(20) NOT NULL,
entry_type ENUM('campaign','ingroup','list','') default '',
active ENUM('Y','N') default 'N',
amm_field VARCHAR(30) default 'vendor_lead_code',
amm_rank SMALLINT(5) default '1',
amm_wildcard VARCHAR(100) default '',
amm_filename VARCHAR(255) default '',
amm_description VARCHAR(255) default '',
PRIMARY KEY (amm_id),
KEY vicidial_AMM_multi_campaign_id_key (campaign_id)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE vicidial_campaigns ADD am_message_wildcards ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1435',db_schema_update_date=NOW() where db_schema_version < 1435;

CREATE TABLE vicidial_html_cache_stats (
stats_type VARCHAR(20) NOT NULL,
stats_id VARCHAR(20) NOT NULL,
stats_date DATETIME NOT NULL,
stats_count INT(9) UNSIGNED default '0',
stats_html MEDIUMTEXT,
UNIQUE KEY vicidial_html_cache_stats_key (stats_type,stats_id)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE system_settings ADD cache_carrier_stats_realtime ENUM('0','1') default '0';

UPDATE system_settings SET db_schema_version='1436',db_schema_update_date=NOW() where db_schema_version < 1436;

ALTER TABLE system_settings ADD oldest_logs_date DATETIME;

UPDATE system_settings SET db_schema_version='1437',db_schema_update_date=NOW() where db_schema_version < 1437;

CREATE TABLE vicidial_dnccom_filter_log (
filter_log_id INT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
lead_id INT(9) UNSIGNED NOT NULL,
list_id BIGINT(14) UNSIGNED DEFAULT NULL,
filter_date DATETIME DEFAULT NULL,
new_status VARCHAR(6) COLLATE utf8_unicode_ci DEFAULT NULL,
old_status VARCHAR(6) COLLATE utf8_unicode_ci DEFAULT NULL,
phone_number VARCHAR(18) COLLATE utf8_unicode_ci DEFAULT NULL,
dnccom_data VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
PRIMARY KEY (filter_log_id),
KEY lead_id (lead_id),
KEY filter_date (filter_date)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1438',db_schema_update_date=NOW() where db_schema_version < 1438;

ALTER TABLE phones ADD unavail_dialplan_fwd_exten VARCHAR(40) default '';
ALTER TABLE phones ADD unavail_dialplan_fwd_context VARCHAR(100) default '';

UPDATE system_settings SET db_schema_version='1439',db_schema_update_date=NOW() where db_schema_version < 1439;

ALTER TABLE phones ADD nva_call_url TEXT;
ALTER TABLE phones ADD nva_search_method VARCHAR(40) default 'NONE';
ALTER TABLE phones ADD nva_error_filename VARCHAR(255) default '';

UPDATE system_settings SET db_schema_version='1440',db_schema_update_date=NOW() where db_schema_version < 1440;

ALTER TABLE phones ADD nva_new_list_id BIGINT(14) UNSIGNED default '995';
ALTER TABLE phones ADD nva_new_phone_code VARCHAR(10) default '1';
ALTER TABLE phones ADD nva_new_status VARCHAR(6) default 'NVAINS';

UPDATE system_settings SET db_schema_version='1441',db_schema_update_date=NOW() where db_schema_version < 1441;

CREATE TABLE vicidial_country_iso_tld (
country_name VARCHAR(200) default '',
iso2 VARCHAR(2) default '',
iso3 VARCHAR(3) default '',
num3 VARCHAR(4) default '',
tld VARCHAR(20) default '',
index(iso3)
)ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1442',db_schema_update_date=NOW() where db_schema_version < 1442;

CREATE TABLE vicidial_asterisk_output (
server_ip VARCHAR(15) NOT NULL,
sip_peers MEDIUMTEXT,
iax_peers MEDIUMTEXT,
asterisk MEDIUMTEXT,
update_date DATETIME,
unique index(server_ip)
)ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE servers ADD gather_asterisk_output ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1443',db_schema_update_date=NOW() where db_schema_version < 1443;

ALTER TABLE vicidial_campaigns ADD manual_dial_timeout VARCHAR(3) default '';

UPDATE system_settings SET db_schema_version='1444',db_schema_update_date=NOW() where db_schema_version < 1444;

ALTER TABLE vicidial_user_groups ADD agent_allowed_chat_groups TEXT;

UPDATE vicidial_user_groups INNER JOIN system_settings ON system_settings.db_schema_version = 1444 SET agent_allowed_chat_groups=agent_status_viewable_groups;

UPDATE system_settings SET db_schema_version='1445',db_schema_update_date=NOW() where db_schema_version < 1445;

ALTER TABLE vicidial_campaigns ADD routing_initiated_recordings ENUM('Y','N') default 'N';

ALTER TABLE vicidial_inbound_groups ADD routing_initiated_recordings ENUM('Y','N') default 'N';

CREATE TABLE routing_initiated_recordings (
recording_id INT(10) UNSIGNED PRIMARY KEY NOT NULL,
filename VARCHAR(100),
launch_time DATETIME,
lead_id INT(9) UNSIGNED,
vicidial_id VARCHAR(20),
user VARCHAR(20) DEFAULT NULL,
processed TINYINT(1) default '0',
index(lead_id),
index(user),
index(processed)
) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE system_settings SET db_schema_version='1446',db_schema_update_date=NOW() where db_schema_version < 1446;

CREATE INDEX live_chat_id on vicidial_chat_log (chat_id);

UPDATE system_settings SET db_schema_version='1447',db_schema_update_date=NOW() where db_schema_version < 1447;

ALTER TABLE vicidial_inbound_dids MODIFY filter_inbound_number ENUM('DISABLED','GROUP','URL','DNC_INTERNAL','DNC_CAMPAIGN','GROUP_AREACODE') default 'DISABLED';

UPDATE system_settings SET db_schema_version='1448',db_schema_update_date=NOW() where db_schema_version < 1448;

ALTER TABLE vicidial_inbound_groups ADD on_hook_cid_number VARCHAR(18) default '';

UPDATE system_settings SET db_schema_version='1449',db_schema_update_date=NOW() where db_schema_version < 1449;

ALTER TABLE vicidial_campaigns ADD manual_dial_hopper_check ENUM('Y','N') default 'N';

CREATE INDEX manager_chat_id_key on vicidial_manager_chat_log (manager_chat_id);
CREATE INDEX manager_chat_subid_key on vicidial_manager_chat_log (manager_chat_subid);
CREATE INDEX manager_chat_manager_key on vicidial_manager_chat_log (manager);
CREATE INDEX manager_chat_user_key on vicidial_manager_chat_log (user);

CREATE INDEX manager_chat_id_archive_key on vicidial_manager_chat_log_archive (manager_chat_id);
CREATE INDEX manager_chat_subid_archive_key on vicidial_manager_chat_log_archive (manager_chat_subid);

UPDATE system_settings SET db_schema_version='1450',db_schema_update_date=NOW() where db_schema_version < 1450;

CREATE TABLE vicidial_recording_access_log (
recording_access_log_id INT(10) unsigned NOT NULL AUTO_INCREMENT,
recording_id INT(10) unsigned DEFAULT NULL,
lead_id INT(10) unsigned DEFAULT NULL,
user VARCHAR(20) DEFAULT NULL,
access_datetime DATETIME DEFAULT NULL,
access_result ENUM('ACCESSED','INVALID USER','INVALID PERMISSIONS','NO RECORDING','RECORDING UNAVAILABLE') DEFAULT NULL,
ip VARCHAR(15) default '',
PRIMARY KEY (recording_access_log_id),
index(recording_id),
index(lead_id)
) ENGINE=MyISAM AUTO_INCREMENT=1599 DEFAULT CHARSET=utf8;

ALTER TABLE vicidial_users ADD access_recordings ENUM('0', '1') default '0';

ALTER TABLE system_settings ADD log_recording_access ENUM('0', '1') default '0';

UPDATE system_settings SET db_schema_version='1451',db_schema_update_date=NOW() where db_schema_version < 1451;

ALTER TABLE system_settings ADD report_default_format ENUM('TEXT', 'HTML') default 'TEXT';

UPDATE system_settings SET db_schema_version='1452',db_schema_update_date=NOW() where db_schema_version < 1452;
