UPDATE system_settings SET db_schema_version='1349',version='2.8b0.5',db_schema_update_date=NOW() where db_schema_version < 1349;

ALTER TABLE vicidial_state_call_times ADD ct_holidays TEXT default '';

UPDATE system_settings SET db_schema_version='1350',db_schema_update_date=NOW() where db_schema_version < 1350;
