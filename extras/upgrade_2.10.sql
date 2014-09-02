UPDATE system_settings SET db_schema_version='1379',version='2.9rc1tk',db_schema_update_date=NOW() where db_schema_version < 1379;

UPDATE system_settings SET db_schema_version='1380',version='2.10b0.5',db_schema_update_date=NOW() where db_schema_version < 1380;

ALTER TABLE vicidial_users ADD wrapup_seconds_override SMALLINT(4) default '-1';

UPDATE system_settings SET db_schema_version='1381',db_schema_update_date=NOW() where db_schema_version < 1381;

ALTER TABLE vicidial_inbound_dids ADD no_agent_ingroup_redirect ENUM('DISABLED','Y','NO_PAUSED','READY_ONLY') default 'DISABLED';
ALTER TABLE vicidial_inbound_dids ADD no_agent_ingroup_id VARCHAR(20) default '';
ALTER TABLE vicidial_inbound_dids ADD no_agent_ingroup_extension VARCHAR(50) default '9998811112';
ALTER TABLE vicidial_inbound_dids ADD pre_filter_phone_group_id VARCHAR(20) default '';
ALTER TABLE vicidial_inbound_dids ADD pre_filter_extension VARCHAR(50) default '';

UPDATE system_settings SET db_schema_version='1382',db_schema_update_date=NOW() where db_schema_version < 1382;

ALTER TABLE vicidial_campaigns ADD wrapup_bypass ENUM('DISABLED','ENABLED') default 'ENABLED';

UPDATE system_settings SET db_schema_version='1383',db_schema_update_date=NOW() where db_schema_version < 1383;

ALTER TABLE vicidial_campaigns ADD wrapup_after_hotkey ENUM('DISABLED','ENABLED') default 'DISABLED';

UPDATE system_settings SET db_schema_version='1384',db_schema_update_date=NOW() where db_schema_version < 1384;

ALTER TABLE system_settings ADD callback_time_24hour ENUM('0','1') default '0';

UPDATE system_settings SET db_schema_version='1385',db_schema_update_date=NOW() where db_schema_version < 1385;

ALTER TABLE vicidial_campaigns ADD callback_active_limit SMALLINT(5) UNSIGNED default '0';
ALTER TABLE vicidial_campaigns ADD callback_active_limit_override ENUM('N','Y') default 'N';

UPDATE system_settings SET db_schema_version='1386',db_schema_update_date=NOW() where db_schema_version < 1386;

