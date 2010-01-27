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
