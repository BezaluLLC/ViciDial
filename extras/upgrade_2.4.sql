UPDATE system_settings SET version='2.4b0.5',db_schema_update_date=NOW();

INSERT INTO vicidial_statuses values('TIMEOT','Inbound Queue Timeout Drop','N','Y','UNDEFINED','N','N','N','N','N');
INSERT INTO vicidial_statuses values('AFTHRS','Inbound After Hours Drop','N','Y','UNDEFINED','N','N','N','N','N');
INSERT INTO vicidial_statuses values('NANQUE','Inbound No Agent No Queue Drop','N','Y','UNDEFINED','N','N','N','N','N');

UPDATE system_settings SET db_schema_version='1194',db_schema_update_date=NOW();

ALTER TABLE vicidial_users ADD voicemail_id VARCHAR(10) default '';

ALTER TABLE vicidial_inbound_dids ADD record_call ENUM('Y','N','Y_QUEUESTOP') default 'N';

UPDATE system_settings SET db_schema_version='1195',db_schema_update_date=NOW();

CREATE TABLE vtiger_vicidial_roles (
user_level TINYINT(2),
vtiger_role VARCHAR(5)
);

UPDATE system_settings SET db_schema_version='1196',db_schema_update_date=NOW();

ALTER TABLE vicidial_inbound_groups ADD ignore_list_script_override ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1197',db_schema_update_date=NOW();

ALTER TABLE servers ADD external_server_ip VARCHAR(100) default '';
ALTER TABLE servers MODIFY recording_web_link ENUM('SERVER_IP','ALT_IP','EXTERNAL_IP') default 'SERVER_IP';

ALTER TABLE phones ADD is_webphone ENUM('Y','N') default 'N';
ALTER TABLE phones ADD use_external_server_ip ENUM('Y','N') default 'N';

ALTER TABLE system_settings ADD default_webphone ENUM('1','0') default '0';
ALTER TABLE system_settings ADD default_external_server_ip ENUM('1','0') default '0';
ALTER TABLE system_settings ADD webphone_url VARCHAR(255) default '';

UPDATE system_settings SET db_schema_version='1198',db_schema_update_date=NOW();

CREATE TABLE vicidial_call_notes (
notesid INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
lead_id INT(9) UNSIGNED NOT NULL,
vicidial_id VARCHAR(20),
call_date DATETIME,
order_id VARCHAR(20),
appointment_date DATE,
appointment_time TIME,
call_notes TEXT
);

ALTER TABLE vicidial_call_notes AUTO_INCREMENT = 100;

UPDATE system_settings SET db_schema_version='1199',db_schema_update_date=NOW();

CREATE INDEX lead_id on vicidial_call_notes (lead_id);

ALTER TABLE system_settings ADD static_agent_url VARCHAR(255) default '';
ALTER TABLE system_settings ADD default_phone_code VARCHAR(8) default '1';

UPDATE system_settings SET db_schema_version='1200',db_schema_update_date=NOW();

INSERT INTO vicidial_scripts (script_id,script_name,script_comments,active,script_text) values('CALLNOTES','Call Notes and Appointment Setting','','Y','<iframe src=\"../agc/vdc_script_notes.php?lead_id=--A--lead_id--B--&vendor_id=--A--vendor_lead_code--B--&list_id=--A--list_id--B--&gmt_offset_now=--A--gmt_offset_now--B--&phone_code=--A--phone_code--B--&phone_number=--A--phone_number--B--&title=--A--title--B--&first_name=--A--first_name--B--&middle_initial=--A--middle_initial--B--&last_name=--A--last_name--B--&address1=--A--address1--B--&address2=--A--address2--B--&address3=--A--address3--B--&city=--A--city--B--&state=--A--state--B--&province=--A--province--B--&postal_code=--A--postal_code--B--&country_code=--A--country_code--B--&gender=--A--gender--B--&date_of_birth=--A--date_of_birth--B--&alt_phone=--A--alt_phone--B--&email=--A--email--B--&security_phrase=--A--security_phrase--B--&comments=--A--comments--B--&user=--A--user--B--&pass=--A--pass--B--&campaign=--A--campaign--B--&phone_login=--A--phone_login--B--&fronter=--A--fronter--B--&closer=--A--user--B--&group=--A--group--B--&channel_group=--A--group--B--&SQLdate=--A--SQLdate--B--&epoch=--A--epoch--B--&uniqueid=--A--uniqueid--B--&rank=--A--rank--B--&owner=--A--owner--B--&customer_zap_channel=--A--customer_zap_channel--B--&server_ip=--A--server_ip--B--&SIPexten=--A--SIPexten--B--&session_id=--A--session_id--B--\" style=\"background-color:transparent;\" scrolling=\"auto\" frameborder=\"0\" allowtransparency=\"true\" id=\"popupFrame\" name=\"popupFrame\"  width=\"--A--script_width--B--\" height=\"--A--script_height--B--\" STYLE=\"z-index:17\"> </iframe>');

ALTER TABLE system_settings ADD enable_agc_dispo_log ENUM('0','1') default '0';

ALTER TABLE vicidial_user_groups ADD agent_call_log_view ENUM('Y','N') default 'N';

ALTER TABLE vicidial_users ADD agent_call_log_view_override ENUM('DISABLED','Y','N') default 'DISABLED';

UPDATE system_settings SET db_schema_version='1201',db_schema_update_date=NOW();

ALTER TABLE system_settings ADD custom_dialplan_entry TEXT;

ALTER TABLE servers ADD custom_dialplan_entry TEXT;

UPDATE system_settings SET db_schema_version='1202',db_schema_update_date=NOW();

ALTER TABLE vicidial_campaigns ADD use_custom_cid ENUM('Y','N') default 'N';
ALTER TABLE vicidial_campaigns MODIFY three_way_call_cid ENUM('CAMPAIGN','CUSTOMER','AGENT_PHONE','AGENT_CHOOSE','CUSTOM_CID') default 'CAMPAIGN';

CREATE TABLE vicidial_custom_cid (
cid VARCHAR(18) NOT NULL,
state VARCHAR(20),
areacode VARCHAR(6),
country_code SMALLINT(5) UNSIGNED,
campaign_id VARCHAR(8) default '--ALL--',
index (state),
index (areacode)
);

ALTER TABLE vicidial_agent_log ADD processed ENUM('Y','N') default 'N';
ALTER TABLE vicidial_agent_log_archive ADD processed ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1203',db_schema_update_date=NOW();

ALTER TABLE vicidial_campaigns ADD scheduled_callbacks_alert ENUM('NONE','BLINK','RED','BLINK_RED') default 'NONE';

UPDATE system_settings SET db_schema_version='1204',db_schema_update_date=NOW();

ALTER TABLE system_settings ADD queuemetrics_loginout ENUM('STANDARD','CALLBACK') default 'STANDARD';

UPDATE system_settings SET db_schema_version='1205',db_schema_update_date=NOW();

CREATE TABLE callcard_accounts (
card_id VARCHAR(20) PRIMARY KEY NOT NULL,
pin VARCHAR(10) NOT NULL,
status ENUM('GENERATE','PRINT','SHIP','HOLD','ACTIVE','USED','EMPTY','CANCEL','VOID') default 'GENERATE',
balance_minutes SMALLINT(5) default '3',
inbound_group_id VARCHAR(20) default '',
index (pin)
);

CREATE TABLE callcard_accounts_details (
card_id VARCHAR(20) PRIMARY KEY NOT NULL,
run VARCHAR(4) default '',
batch VARCHAR(5) default '',
pack VARCHAR(5) default '',
sequence VARCHAR(5) default '',
status ENUM('GENERATE','PRINT','SHIP','HOLD','ACTIVE','USED','EMPTY','CANCEL','VOID') default 'GENERATE',
balance_minutes SMALLINT(5) default '3',
initial_value VARCHAR(6) default '0.00',
initial_minutes SMALLINT(5) default '3',
note_purchase_order VARCHAR(20) default '',
note_printer VARCHAR(20) default '',
note_did VARCHAR(18) default '',
inbound_group_id VARCHAR(20) default '',
note_language VARCHAR(10) default 'English',
note_name VARCHAR(20) default '',
note_comments VARCHAR(255) default '',
create_user VARCHAR(20) default '',
activate_user VARCHAR(20) default '',
used_user VARCHAR(20) default '',
void_user VARCHAR(20) default '',
create_time DATETIME,
activate_time DATETIME,
used_time DATETIME,
void_time DATETIME
);

CREATE TABLE callcard_log (
uniqueid VARCHAR(20) PRIMARY KEY NOT NULL,
card_id VARCHAR(20),
balance_minutes_start SMALLINT(5) default '3',
call_time DATETIME,
agent_time DATETIME,
dispo_time DATETIME,
agent VARCHAR(20) default '',
agent_dispo VARCHAR(6) default '',
agent_talk_sec MEDIUMINT(8) default '0',
agent_talk_min MEDIUMINT(8) default '0',
phone_number VARCHAR(18),
inbound_did VARCHAR(18),
index (card_id),
index (call_time)
);

ALTER TABLE system_settings ADD callcard_enabled ENUM('1','0') default '0';

ALTER TABLE vicidial_users ADD callcard_admin ENUM('1','0') default '0';

UPDATE system_settings SET db_schema_version='1206',db_schema_update_date=NOW();

ALTER TABLE vicidial_user_groups ADD agent_xfer_consultative ENUM('Y','N') default 'Y';
ALTER TABLE vicidial_user_groups ADD agent_xfer_dial_override ENUM('Y','N') default 'Y';
ALTER TABLE vicidial_user_groups ADD agent_xfer_vm_transfer ENUM('Y','N') default 'Y';
ALTER TABLE vicidial_user_groups ADD agent_xfer_blind_transfer ENUM('Y','N') default 'Y';
ALTER TABLE vicidial_user_groups ADD agent_xfer_dial_with_customer ENUM('Y','N') default 'Y';
ALTER TABLE vicidial_user_groups ADD agent_xfer_park_customer_dial ENUM('Y','N') default 'Y';

ALTER TABLE vicidial_agent_log ADD uniqueid VARCHAR(20) default '';
ALTER TABLE vicidial_agent_log_archive ADD uniqueid VARCHAR(20) default '';

UPDATE system_settings SET db_schema_version='1207',db_schema_update_date=NOW();

ALTER TABLE vicidial_user_groups ADD agent_fullscreen ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1208',db_schema_update_date=NOW();

ALTER TABLE vicidial_auto_calls ADD extension VARCHAR(100) default '';

ALTER TABLE vicidial_live_agents ADD ra_user VARCHAR(20) default '';
ALTER TABLE vicidial_live_agents ADD ra_extension VARCHAR(100) default '';

ALTER TABLE vicidial_remote_agents ADD extension_group VARCHAR(20) default 'NONE';
ALTER TABLE vicidial_remote_agents ADD extension_group_order VARCHAR(20) default 'NONE';

CREATE TABLE vicidial_extension_groups (
extension_id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
extension_group_id VARCHAR(20) NOT NULL,
extension VARCHAR(100) default '8300',
rank MEDIUMINT(7) default '0',
campaign_groups TEXT,
call_count_today MEDIUMINT(7) default '0',
last_call_time DATETIME,
last_callerid VARCHAR(20) default '',
index (extension_group_id)
);

CREATE TABLE vicidial_remote_agent_log (
uniqueid VARCHAR(20) default '',
callerid VARCHAR(20) default '',
ra_user VARCHAR(20),
user VARCHAR(20),
call_time DATETIME,
extension VARCHAR(100) default '',
lead_id INT(9) UNSIGNED default '0',
phone_number VARCHAR(18) default '',
campaign_id VARCHAR(20) default '',
processed ENUM('Y','N') default 'N',
comment VARCHAR(255) default '',
index (call_time),
index (ra_user),
index (extension),
index (phone_number)
);

UPDATE system_settings SET db_schema_version='1209',db_schema_update_date=NOW();

ALTER TABLE vicidial_users ADD agent_choose_blended ENUM('0','1') default '1';

UPDATE system_settings SET db_schema_version='1210',db_schema_update_date=NOW();

ALTER TABLE system_settings ADD queuemetrics_callstatus ENUM('0','1') default '1';

UPDATE system_settings SET db_schema_version='1211',db_schema_update_date=NOW();

ALTER TABLE vicidial_campaigns ADD queuemetrics_callstatus_override ENUM('DISABLED','NO','YES') default 'DISABLED';

UPDATE system_settings SET db_schema_version='1212',db_schema_update_date=NOW();

ALTER TABLE vicidial_campaigns ADD extension_appended_cidname ENUM('Y','N') default 'N';

ALTER TABLE vicidial_inbound_groups ADD extension_appended_cidname ENUM('Y','N') default 'N';

UPDATE system_settings SET db_schema_version='1213',db_schema_update_date=NOW();

ALTER TABLE vicidial_campaigns ADD scheduled_callbacks_count ENUM('LIVE','ALL_ACTIVE') default 'ALL_ACTIVE';

UPDATE system_settings SET db_schema_version='1214',db_schema_update_date=NOW();

ALTER TABLE vicidial_user_log ADD session_id VARCHAR(20);
ALTER TABLE vicidial_user_log ADD server_ip VARCHAR(15);
ALTER TABLE vicidial_user_log ADD extension VARCHAR(50);
ALTER TABLE vicidial_user_log ADD computer_ip VARCHAR(15);
ALTER TABLE vicidial_user_log ADD browser VARCHAR(255);
ALTER TABLE vicidial_user_log ADD data VARCHAR(255);

ALTER TABLE vicidial_campaigns ADD manual_dial_override ENUM('NONE','ALLOW_ALL','DISABLE_ALL') default 'NONE';
ALTER TABLE vicidial_campaigns ADD blind_monitor_warning ENUM('DISABLED','ALERT','NOTICE','AUDIO','ALERT_NOTICE','ALERT_AUDIO','NOTICE_AUDIO','ALL') default 'DISABLED';
ALTER TABLE vicidial_campaigns ADD blind_monitor_message VARCHAR(255) default 'Someone is blind monitoring your session';
ALTER TABLE vicidial_campaigns ADD blind_monitor_filename VARCHAR(100) default '';

ALTER TABLE vicidial_users ADD realtime_block_user_info ENUM('0','1') default '0';

ALTER TABLE vicidial_inbound_groups ADD uniqueid_status_display ENUM('DISABLED','ENABLED','ENABLED_PREFIX') default 'DISABLED';
ALTER TABLE vicidial_inbound_groups ADD uniqueid_status_prefix VARCHAR(50) default '';

CREATE TABLE vicidial_log_extended (
uniqueid VARCHAR(50) PRIMARY KEY NOT NULL,
server_ip VARCHAR(15),
call_date DATETIME,
lead_id INT(9) UNSIGNED,
caller_code VARCHAR(30) NOT NULL,
custom_call_id VARCHAR(100)
);

ALTER TABLE system_settings ADD default_codecs VARCHAR(100) default '';

ALTER TABLE phones ADD codecs_list VARCHAR(100) default '';
ALTER TABLE phones ADD codecs_with_template ENUM('0','1') default '0';

UPDATE system_settings SET db_schema_version='1215',db_schema_update_date=NOW();

ALTER TABLE vicidial_inbound_groups MODIFY uniqueid_status_display ENUM('DISABLED','ENABLED','ENABLED_PREFIX','ENABLED_PRESERVE') default 'DISABLED';

UPDATE system_settings SET db_schema_version='1216',db_schema_update_date=NOW();
