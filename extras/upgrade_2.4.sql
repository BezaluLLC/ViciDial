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
