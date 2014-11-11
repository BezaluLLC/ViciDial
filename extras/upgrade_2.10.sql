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

ALTER TABLE system_settings ADD active_modules TEXT;
ALTER TABLE system_settings ADD allow_chats ENUM('0','1') default '0';
ALTER TABLE vicidial_campaigns ADD allow_chats ENUM('Y','N') default 'N';
ALTER TABLE vicidial_user_groups MODIFY shift_enforcement ENUM('OFF','START','ALL','ADMIN_EXEMPT') default 'OFF';

CREATE TABLE vicidial_avatars (
avatar_id VARCHAR(100) PRIMARY KEY NOT NULL,
avatar_name VARCHAR(100),
avatar_notes TEXT,
avatar_api_user VARCHAR(20) default '',
avatar_api_pass VARCHAR(20) default '',
active ENUM('Y','N') default 'Y',
audio_functions VARCHAR(100) default 'PLAY-STOP-RESTART',
audio_display VARCHAR(100) default 'FILE-NAME',
user_group VARCHAR(20) default '---ALL---'
) ENGINE=MyISAM;

CREATE TABLE vicidial_avatar_audio (
avatar_id VARCHAR(100) NOT NULL,
audio_filename VARCHAR(255) NOT NULL,
audio_name TEXT,
rank SMALLINT(5) default '0',
h_ord SMALLINT(5) default '1',
level SMALLINT(5) default '1',
parent_audio_filename VARCHAR(255) default '',
parent_rank VARCHAR(2) default '',
index (avatar_id)
) ENGINE=MyISAM;

UPDATE system_settings SET db_schema_version='1387',db_schema_update_date=NOW() where db_schema_version < 1387;
