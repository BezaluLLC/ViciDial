UPDATE system_settings SET db_schema_version='1404',version='2.11rc1tk',db_schema_update_date=NOW() where db_schema_version < 1404;

UPDATE system_settings SET db_schema_version='1405',version='2.12b0.5',db_schema_update_date=NOW() where db_schema_version < 1405;

ALTER TABLE system_settings ADD meetme_enter_login_filename VARCHAR(255) default '';
ALTER TABLE system_settings ADD meetme_enter_leave3way_filename VARCHAR(255) default '';

UPDATE system_settings SET db_schema_version='1406',db_schema_update_date=NOW() where db_schema_version < 1406;
