UPDATE system_settings SET db_schema_version='1349',version='2.8b0.5',db_schema_update_date=NOW() where db_schema_version < 1349;

ALTER TABLE vicidial_state_call_times ADD ct_holidays TEXT default '';

UPDATE system_settings SET db_schema_version='1350',db_schema_update_date=NOW() where db_schema_version < 1350;

ALTER TABLE vicidial_users ADD failed_login_count TINYINT(3) UNSIGNED default '0';
ALTER TABLE vicidial_users ADD last_login_date DATETIME default '2001-01-01 00:00:01';
ALTER TABLE vicidial_users ADD last_ip VARCHAR(15) default '';
ALTER TABLE vicidial_users ADD pass_hash VARCHAR(100) default '';

ALTER TABLE system_settings ADD pass_hash_enabled ENUM('0','1') default '0';

UPDATE system_settings SET db_schema_version='1351',db_schema_update_date=NOW() where db_schema_version < 1351;
