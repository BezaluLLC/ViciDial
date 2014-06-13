UPDATE system_settings SET db_schema_version='1379',version='2.9rc1tk',db_schema_update_date=NOW() where db_schema_version < 1379;

UPDATE system_settings SET db_schema_version='1380',version='2.10b0.5',db_schema_update_date=NOW() where db_schema_version < 1380;
