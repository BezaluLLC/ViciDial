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

