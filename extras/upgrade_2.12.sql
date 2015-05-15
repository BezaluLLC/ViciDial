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
