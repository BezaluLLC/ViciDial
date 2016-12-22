UPDATE system_settings SET db_schema_version='1479',version='2.13rc1tk',db_schema_update_date=NOW() where db_schema_version < 1479;

UPDATE system_settings SET db_schema_version='1480',version='2.14b0.5',db_schema_update_date=NOW() where db_schema_version < 1480;

ALTER TABLE vicidial_settings_containers MODIFY container_type ENUM('OTHER','PERL_CLI','EMAIL_TEMPLATE','AGI') default 'OTHER';

UPDATE system_settings SET db_schema_version='1481',db_schema_update_date=NOW() where db_schema_version < 1481;

CREATE TABLE parked_channels_recent (
channel VARCHAR(100) NOT NULL,
server_ip VARCHAR(15) NOT NULL,
channel_group VARCHAR(30),
park_end_time DATETIME,
index (channel_group),
index (park_end_time)
) ENGINE=MyISAM;

ALTER TABLE vicidial_manager_chats ADD column internal_chat_type ENUM('AGENT', 'MANAGER') default 'MANAGER' after manager_chat_id;
ALTER TABLE vicidial_manager_chats_archive ADD column internal_chat_type ENUM('AGENT', 'MANAGER') default 'MANAGER' after manager_chat_id;

ALTER TABLE vicidial_manager_chat_log ADD column message_id VARCHAR(20) after message;
ALTER TABLE vicidial_manager_chat_log_archive ADD column message_id VARCHAR(20) after message;

UPDATE system_settings SET db_schema_version='1482',db_schema_update_date=NOW() where db_schema_version < 1482;

ALTER TABLE system_settings ADD agent_chat_screen_colors VARCHAR(20) default 'default';

UPDATE system_settings SET db_schema_version='1483',db_schema_update_date=NOW() where db_schema_version < 1483;
