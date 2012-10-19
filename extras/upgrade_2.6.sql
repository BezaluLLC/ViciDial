UPDATE system_settings SET db_schema_version='1318',version='2.6b0.5',db_schema_update_date=NOW() where db_schema_version < 1318;

ALTER TABLE vicidial_phone_codes MODIFY geographic_description VARCHAR(100);

ALTER TABLE vicidial_campaigns ADD in_group_dial ENUM('DISABLED','MANUAL_DIAL','NO_DIAL','BOTH') default 'DISABLED';
ALTER TABLE vicidial_campaigns ADD in_group_dial_select ENUM('AGENT_SELECTED','CAMPAIGN_SELECTED','ALL_USER_GROUP') default 'CAMPAIGN_SELECTED';

UPDATE system_settings SET db_schema_version='1319',db_schema_update_date=NOW() where db_schema_version < 1319;

ALTER TABLE vicidial_inbound_groups ADD dial_ingroup_cid VARCHAR(20) default '';

UPDATE system_settings SET db_schema_version='1320',db_schema_update_date=NOW() where db_schema_version < 1320;

ALTER TABLE vicidial_campaigns ADD safe_harbor_audio_field VARCHAR(30) default 'DISABLED';

UPDATE system_settings SET db_schema_version='1321',db_schema_update_date=NOW() where db_schema_version < 1321;

ALTER TABLE system_settings ADD call_menu_qualify_enabled ENUM('0','1') default '0';

ALTER TABLE vicidial_call_menu ADD qualify_sql TEXT;

UPDATE system_settings SET db_schema_version='1322',db_schema_update_date=NOW() where db_schema_version < 1322;

ALTER TABLE recording_log MODIFY filename VARCHAR(100);

ALTER TABLE vicidial_live_agents ADD external_recording VARCHAR(20) default '';

UPDATE system_settings SET db_schema_version='1323',db_schema_update_date=NOW() where db_schema_version < 1323;

ALTER TABLE system_settings ADD admin_list_counts ENUM('0','1') default '1';

UPDATE system_settings SET db_schema_version='1324',db_schema_update_date=NOW() where db_schema_version < 1324;

ALTER TABLE phones MODIFY is_webphone ENUM('Y','N','Y_API_LAUNCH') default 'N';

CREATE TABLE vicidial_session_data (
session_name VARCHAR(40) UNIQUE NOT NULL,
user VARCHAR(20),
campaign_id VARCHAR(8),
server_ip VARCHAR(15) NOT NULL,
conf_exten VARCHAR(20),
extension VARCHAR(100) NOT NULL,
login_time DATETIME NOT NULL,
webphone_url TEXT,
agent_login_call TEXT
);

UPDATE system_settings SET db_schema_version='1325',db_schema_update_date=NOW() where db_schema_version < 1325;

CREATE TABLE vicidial_dial_log (
caller_code VARCHAR(30) NOT NULL,
lead_id INT(9) UNSIGNED default '0',
server_ip VARCHAR(15),
call_date DATETIME,
extension VARCHAR(100) default '',
channel VARCHAR(100) default '',
context VARCHAR(100) default '',
timeout MEDIUMINT(7) UNSIGNED default '0',
outbound_cid VARCHAR(100) default '',
index (caller_code),
index (call_date)
);

CREATE TABLE vicidial_dial_log_archive LIKE vicidial_dial_log;
CREATE UNIQUE INDEX vddla on vicidial_dial_log_archive (caller_code,call_date);

UPDATE system_settings SET db_schema_version='1326',db_schema_update_date=NOW() where db_schema_version < 1326;

ALTER TABLE vicidial_campaigns MODIFY agent_dial_owner_only ENUM('NONE','USER','TERRITORY','USER_GROUP','USER_BLANK','TERRITORY_BLANK','USER_GROUP_BLANK') default 'NONE';

UPDATE system_settings SET db_schema_version='1327',db_schema_update_date=NOW() where db_schema_version < 1327;

