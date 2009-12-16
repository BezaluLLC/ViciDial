#!/usr/bin/perl
#
# AST_cleanup_agent_log.pl version 2.2.0
#
# DESCRIPTION:
# to be run frequently to clean up the vicidial_agent_log to fix erroneous time 
# calculations due to out-of-order vicidial_agent_log updates. This happens 0.5%
# of the time in our test setups, but that leads to inaccurate time logs so we
# wrote this script to fix the miscalculations
#
# This program only needs to be run by one server
#
# Copyright (C) 2009  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGES
# 60711-0945 - Changed to DBI by Marin Blu
# 60715-2301 - Changed to use /etc/astguiclient.conf for configs
# 81029-0124 - Added portion to clean up queue_log entries if QM enabled
# 81114-0155 - Added portion to remove queue_log COMPLETE duplicates
# 81208-0133 - Added portion to check for more missing queue_log entries
# 90330-2128 - Minor code fixes and restricted queue_log actions to VICIDIAL-defined serverid records
# 91112-1100 - Added fixing for more QM issues, added CALLOUTBOUND checking with ENTERQUEUE
# 91209-0956 - Added PAUSEREASON-LAGGED queue_log correction during live call
# 91210-0609 - Added LOGOFF queue_log correction during live call
# 91214-0933 - Added queue_position to queue_log COMPLETE... and ABANDON records
#

# constants
$US='__';
$MT[0]='';

### begin parsing run-time options ###
if (length($ARGV[0])>1)
	{
	$i=0;
	while ($#ARGV >= $i)
		{
		$args = "$args $ARGV[$i]";
		$i++;
		}

	if ($args =~ /--help/i)
		{
		print "allowed run time options:\n";
		print "  [-no-time-restriction] = will clean up all logs, without flag will only do last hour\n";
		print "   (without time flag will do logs from 150-10 minutes ago)\n";
		print "  [-last-24hours] = will clean up logs for the last 24 hours only\n";
		print "  [-more-than-24hours] = will clean up logs older than 24 hours only\n";
		print "  [-skip-queue-log-inserts] = will skip only the queue_log missing record checks\n";
		print "  [-only-check-agent-login-lags] = will only fix queue_log missing PAUSEREASON records\n";
		print "  [-only-qm-live-call-check] = will only check the queue_log calls that report as live, in ViciDial\n";
		print "  [-q] = quiet, no output\n";
		print "  [-test] = test\n";
		print "  [-debug] = verbose debug messages\n";
		print "  [-debugX] = Extra-verbose debug messages\n\n";
		exit;
		}
	else
		{
		if ($args =~ /-q/i)
			{
			$Q=1; # quiet
			}
		if ($args =~ /-debug/i)
			{
			$DB=1; # Debug flag
			if ($Q < 1) {print "\n----- DEBUGGING -----\n\n";}
			}
		if ($args =~ /--debugX/i)
			{
			$DBX=1;
			if ($Q < 1) {print "\n----- SUPER-DUPER DEBUGGING -----\n\n";}
			}
		if ($args =~ /-test/i)
			{
			$TEST=1;
			$T=1;
			if ($Q < 1) {print "\n----- TEST RUN, NO UPDATES -----\n\n";}
			}
		if ($args =~ /-no-time-restriction/i)
			{
			$ALL_TIME=1;
			if ($Q < 1) {print "\n----- NO TIME RESTRICTIONS -----\n\n";}
			}
		if ($args =~ /-only-check-agent-login-lags/i)
			{
			$login_lagged_check=1;
			if ($Q < 1) {print "\n----- ONLY LOGIN LAGGED CHECK -----\n\n";}
			}
		if ($args =~ /-only-qm-live-call-check/i)
			{
			$qm_live_call_check=1;
			if ($Q < 1) {print "\n----- QM LIVE CALL CHECK -----\n\n";}
			}
		if ($args =~ /-last-24hours/i)
			{
			$TWENTYFOUR_HOURS=1;
			if ($Q < 1) {print "\n----- LAST 24 HOURS ONLY -----\n\n";}
			}
		if ($args =~ /-more-than-24hours/i)
			{
			$TWENTYFOUR_OLDER=1;
			if ($Q < 1) {print "\n----- MORE THAN 24 HOURS OLD ONLY -----\n\n";}
			}
		if ($args =~ /-skip-queue-log-inserts/i)
			{
			$skip_queue_log_inserts=1;
			if ($Q < 1) {print "\n----- SKIPPING QUEUE_LOG INSERTS -----\n\n";}
			}
		}
	}
else
	{
	#	print "no command line options set\n";
	}
### end parsing run-time options ###

# define time restrictions for queries in script
$secX = time();
$HDtarget = ($secX - 150); # 2.5 minutes in the past
($Hsec,$Hmin,$Hhour,$Hmday,$Hmon,$Hyear,$Hwday,$Hyday,$Hisdst) = localtime($HDtarget);
$Hyear = ($Hyear + 1900);
$Hmon++;
if ($Hmon < 10) {$Hmon = "0$Hmon";}
if ($Hmday < 10) {$Hmday = "0$Hmday";}
if ($Hhour < 10) {$Hhour = "0$Hhour";}
if ($Hmin < 10) {$Hmin = "0$Hmin";}
if ($Hsec < 10) {$Hsec = "0$Hsec";}
	$HDSQLdate = "$Hyear-$Hmon-$Hmday $Hhour:$Hmin:$Hsec";

$FDtarget = ($secX - 600); # 10 minutes in the past
($Fsec,$Fmin,$Fhour,$Fmday,$Fmon,$Fyear,$Fwday,$Fyday,$Fisdst) = localtime($FDtarget);
$Fyear = ($Fyear + 1900);
$Fmon++;
if ($Fmon < 10) {$Fmon = "0$Fmon";}
if ($Fmday < 10) {$Fmday = "0$Fmday";}
if ($Fhour < 10) {$Fhour = "0$Fhour";}
if ($Fmin < 10) {$Fmin = "0$Fmin";}
if ($Fsec < 10) {$Fsec = "0$Fsec";}
	$FDSQLdate = "$Fyear-$Fmon-$Fmday $Fhour:$Fmin:$Fsec";

$TDtarget = ($secX - 9000); # 150 minutes in the past
($Tsec,$Tmin,$Thour,$Tmday,$Tmon,$Tyear,$Twday,$Tyday,$Tisdst) = localtime($TDtarget);
$Tyear = ($Tyear + 1900);
$Tmon++;
if ($Tmon < 10) {$Tmon = "0$Tmon";}
if ($Tmday < 10) {$Tmday = "0$Tmday";}
if ($Thour < 10) {$Thour = "0$Thour";}
if ($Tmin < 10) {$Tmin = "0$Tmin";}
if ($Tsec < 10) {$Tsec = "0$Tsec";}
	$TDSQLdate = "$Tyear-$Tmon-$Tmday $Thour:$Tmin:$Tsec";

$VDAD_SQL_time = "and event_time > \"$TDSQLdate\" and event_time < \"$FDSQLdate\"";
$QM_SQL_time = "and time_id > $TDtarget and time_id < $FDtarget";
$QM_SQL_time_H = "and time_id > $TDtarget and time_id < $HDtarget";

if ($ALL_TIME > 0)
	{
	$VDAD_SQL_time = "";
	$QM_SQL_time = "";
	}
if ($TWENTYFOUR_HOURS > 0)
	{
	$TDtarget = ($secX - 86400); # 24 hours in the past
	($Tsec,$Tmin,$Thour,$Tmday,$Tmon,$Tyear,$Twday,$Tyday,$Tisdst) = localtime($TDtarget);
	$Tyear = ($Tyear + 1900);
	$Tmon++;
	if ($Tmon < 10) {$Tmon = "0$Tmon";}
	if ($Tmday < 10) {$Tmday = "0$Tmday";}
	if ($Thour < 10) {$Thour = "0$Thour";}
	if ($Tmin < 10) {$Tmin = "0$Tmin";}
	if ($Tsec < 10) {$Tsec = "0$Tsec";}
		$TDSQLdate = "$Tyear-$Tmon-$Tmday $Thour:$Tmin:$Tsec";

	$VDAD_SQL_time = "and event_time > \"$TDSQLdate\" and event_time < \"$FDSQLdate\"";
	$QM_SQL_time = "and time_id > $TDtarget and time_id < $FDtarget";
	$QM_SQL_time_H = "and time_id > $TDtarget and time_id < $HDtarget";
	}
if ($TWENTYFOUR_OLDER > 0)
	{
	$TDtarget = ($secX - 86400); # 24 hours in the past
	($Tsec,$Tmin,$Thour,$Tmday,$Tmon,$Tyear,$Twday,$Tyday,$Tisdst) = localtime($TDtarget);
	$Tyear = ($Tyear + 1900);
	$Tmon++;
	if ($Tmon < 10) {$Tmon = "0$Tmon";}
	if ($Tmday < 10) {$Tmday = "0$Tmday";}
	if ($Thour < 10) {$Thour = "0$Thour";}
	if ($Tmin < 10) {$Tmin = "0$Tmin";}
	if ($Tsec < 10) {$Tsec = "0$Tsec";}
		$TDSQLdate = "$Tyear-$Tmon-$Tmday $Thour:$Tmin:$Tsec";

	$VDAD_SQL_time = "and event_time < \"$TDSQLdate\"";
	$QM_SQL_time = "and time_id < $TDtarget";
	$QM_SQL_time_H = "and time_id < $TDtarget";
	}

# default path to astguiclient configuration file:
$PATHconf =		'/etc/astguiclient.conf';

open(conf, "$PATHconf") || die "can't open $PATHconf: $!\n";
@conf = <conf>;
close(conf);
$i=0;
foreach(@conf)
	{
	$line = $conf[$i];
	$line =~ s/ |>|\n|\r|\t|\#.*|;.*//gi;
	if ( ($line =~ /^PATHhome/) && ($CLIhome < 1) )
		{$PATHhome = $line;   $PATHhome =~ s/.*=//gi;}
	if ( ($line =~ /^PATHlogs/) && ($CLIlogs < 1) )
		{$PATHlogs = $line;   $PATHlogs =~ s/.*=//gi;}
	if ( ($line =~ /^PATHagi/) && ($CLIagi < 1) )
		{$PATHagi = $line;   $PATHagi =~ s/.*=//gi;}
	if ( ($line =~ /^PATHweb/) && ($CLIweb < 1) )
		{$PATHweb = $line;   $PATHweb =~ s/.*=//gi;}
	if ( ($line =~ /^PATHsounds/) && ($CLIsounds < 1) )
		{$PATHsounds = $line;   $PATHsounds =~ s/.*=//gi;}
	if ( ($line =~ /^PATHmonitor/) && ($CLImonitor < 1) )
		{$PATHmonitor = $line;   $PATHmonitor =~ s/.*=//gi;}
	if ( ($line =~ /^VARserver_ip/) && ($CLIserver_ip < 1) )
		{$VARserver_ip = $line;   $VARserver_ip =~ s/.*=//gi;}
	if ( ($line =~ /^VARDB_server/) && ($CLIDB_server < 1) )
		{$VARDB_server = $line;   $VARDB_server =~ s/.*=//gi;}
	if ( ($line =~ /^VARDB_database/) && ($CLIDB_database < 1) )
		{$VARDB_database = $line;   $VARDB_database =~ s/.*=//gi;}
	if ( ($line =~ /^VARDB_user/) && ($CLIDB_user < 1) )
		{$VARDB_user = $line;   $VARDB_user =~ s/.*=//gi;}
	if ( ($line =~ /^VARDB_pass/) && ($CLIDB_pass < 1) )
		{$VARDB_pass = $line;   $VARDB_pass =~ s/.*=//gi;}
	if ( ($line =~ /^VARDB_port/) && ($CLIDB_port < 1) )
		{$VARDB_port = $line;   $VARDB_port =~ s/.*=//gi;}
	$i++;
	}

# Customized Variables
$server_ip = $VARserver_ip;		# Asterisk server IP

if (!$CLEANLOGfile) {$CLEANLOGfile = "$PATHlogs/clean.$Hyear-$Hmon-$Hmday";}

if (!$VARDB_port) {$VARDB_port='3306';}

use DBI;	  

$dbhA = DBI->connect("DBI:mysql:$VARDB_database:$VARDB_server:$VARDB_port", "$VARDB_user", "$VARDB_pass")
or die "Couldn't connect to database: " . DBI->errstr;

#############################################
##### START QUEUEMETRICS LOGGING LOOKUP #####
$stmtA = "SELECT enable_queuemetrics_logging,queuemetrics_server_ip,queuemetrics_dbname,queuemetrics_login,queuemetrics_pass,queuemetrics_log_id,queuemetrics_eq_prepend FROM system_settings;";
$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
$sthArows=$sthA->rows;
if ($sthArows > 0)
	{
	@aryA = $sthA->fetchrow_array;
	$enable_queuemetrics_logging =	$aryA[0];
	$queuemetrics_server_ip	=	$aryA[1];
	$queuemetrics_dbname =		$aryA[2];
	$queuemetrics_login=		$aryA[3];
	$queuemetrics_pass =		$aryA[4];
	$queuemetrics_log_id =		$aryA[5];
	$queuemetrics_eq_prepend =	$aryA[6];
	}
$sthA->finish();
##### END QUEUEMETRICS LOGGING LOOKUP #####
###########################################




### BEGIN CHECKING ENTERQUEUE/CALLOUTBOUND ENTRIES FOR LIVE CALLS
if ($enable_queuemetrics_logging > 0)
	{
	if ($DB) {print " - Checking queue_log in-queue calls in ViciDial\n";}

	$dbhB = DBI->connect("DBI:mysql:$queuemetrics_dbname:$queuemetrics_server_ip:3306", "$queuemetrics_login", "$queuemetrics_pass")
	 or die "Couldn't connect to database: " . DBI->errstr;

	if ($DBX) {print "CONNECTED TO DATABASE:  $queuemetrics_server_ip|$queuemetrics_dbname\n";}

	##############################################################
	##### grab all queue_log entries for ENTERQUEUE verb to validate
	$stmtB = "SELECT time_id,call_id,queue,verb,serverid FROM queue_log where verb IN('ENTERQUEUE','CALLOUTBOUND') and serverid='$queuemetrics_log_id' $QM_SQL_time_H order by time_id;";
	$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
	$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
	$EQenter_records=$sthB->rows;
	if ($DB) {print "ENTERQUEUE Records: $EQenter_records|$stmtB|\n\n";}
	$h=0;
	while ($EQenter_records > $h)
		{
		@aryB = $sthB->fetchrow_array;
		$time_id[$h] =	$aryB[0];
		$call_id[$h] =	$aryB[1];
		$queue[$h] =	$aryB[2];
		$verb[$h] =		$aryB[3];
		$serverid[$h] =	$aryB[4];
		$lead_id[$h] = substr($call_id[$h], 11, 9);
		$lead_id[$h] = ($lead_id[$h] + 0);
		$h++;
		}
	$sthB->finish();

	$h=0;
	while ($EQenter_records > $h)
		{
		$EQend_count=0;
		##### find the CONNECT/ABANDON/COMPLETEAGENT/COMPLETECALLER/CALLSTATUS/EXITWITHKEY/EXITWITHTIMEOUT count for each record
		$stmtB = "SELECT count(*) FROM queue_log where verb IN('CONNECT','ABANDON','COMPLETEAGENT','COMPLETECALLER','CALLSTATUS','EXITWITHKEY','EXITWITHTIMEOUT') and call_id='$call_id[$h]';";
		$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
		$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
		$CQ_records=$sthB->rows;
		if ($CQ_records > 0)
			{
			@aryB = $sthB->fetchrow_array;
			$EQend_count =		$aryB[0];
			}
		$sthB->finish();

		if ($EQend_count < 1)
			{
			if ($DB) {print "IN-QUEUE CALL: $h|$time_id[$h]|$call_id[$h]|$verb[$h]|$serverid[$h]\n";}

			$VAClive_count=0;
			$VLAlive_count=0;

			$stmtA = "SELECT count(*) FROM vicidial_auto_calls where callerid='$call_id[$h]';";
			$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
			$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
			$sthArows=$sthA->rows;
			if ($sthArows > 0)
				{
				@aryA = $sthA->fetchrow_array;
				$VAClive_count =	$aryA[0];
				}
			$sthA->finish();

			$stmtA = "SELECT count(*) FROM vicidial_live_agents where callerid='$call_id[$h]';";
			$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
			$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
			$sthArows=$sthA->rows;
			if ($sthArows > 0)
				{
				@aryA = $sthA->fetchrow_array;
				$VLAlive_count =	$aryA[0];
				}
			$sthA->finish();

			if ( ($VLAlive_count < 1) && ($VAClive_count < 1) )
				{
				$EQdead++;
				if ($DB) {print "     DEAD IN-QUEUE queue_log CALL: $EQdead|$call_id[$h]|$VLAlive_count|$VAClive_count\n";}

				$secX = time();
				$Rtarget = ($secX - 21600);	# look for VDCL entry within last 6 hours
				($Rsec,$Rmin,$Rhour,$Rmday,$Rmon,$Ryear,$Rwday,$Ryday,$Risdst) = localtime($Rtarget);
				$Ryear = ($Ryear + 1900);
				$Rmon++;
				if ($Rmon < 10) {$Rmon = "0$Rmon";}
				if ($Rmday < 10) {$Rmday = "0$Rmday";}
				if ($Rhour < 10) {$Rhour = "0$Rhour";}
				if ($Rmin < 10) {$Rmin = "0$Rmin";}
				if ($Rsec < 10) {$Rsec = "0$Rsec";}
					$RSQLdate = "$Ryear-$Rmon-$Rmday $Rhour:$Rmin:$Rsec";

				### find original queue position of the call
				$queue_position=1;
				$queue_seconds=0;
				$stmtA = "SELECT queue_position,queue_seconds FROM vicidial_closer_log where lead_id='$lead_id[$h]' and campaign_id='$queue[$h]' and call_date > \"$RSQLdate\" order by closecallid desc limit 1;";
				$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
				$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
				$sthArows=$sthA->rows;
				if ($sthArows > 0)
					{
					@aryA = $sthA->fetchrow_array;
					$queue_position =	$aryA[0];
					$queue_seconds =	int($aryA[1] + .5);
					}
				$sthA->finish();

				$newtimeABANDON = ($time_id[$h] + 1);
				##### insert an ABANDON record for this call into the queue_log
				$stmtB = "INSERT INTO queue_log SET partition='P01',time_id='$newtimeABANDON',call_id='$call_id[$h]',queue='$queue[$h]',agent='NONE',verb='ABANDON',data1='1',data2='$queue_position',data3='$queue_seconds',serverid='$serverid[$h]';";
				if ($TEST < 1)
					{
					$Baffected_rows = $dbhB->do($stmtB);
					}
				if ($DB) {print "     ABANDON record inserted: $Baffected_rows|$stmtB|\n";}

				$event_string = "DEAD IN-QUEUE CALL: $h|$EQdead|$time_id[$h]|$call_id[$h]|$queue[$h]|$verb[$h]|$serverid[$h]|$VLAlive_count|$VAClive_count|$Baffected_rows|$stmtB";
				&event_logger;
				}
			}

		$h++;
		}

	@time_id=@MT;
	@agent=@MT;

	##############################################################
	##### grab all queue_log entries with a PAUSEREASON of LAGGED to validate
	$stmtB = "SELECT time_id,agent FROM queue_log where verb='PAUSEREASON' and data1='LAGGED' $QM_SQL_time_H order by time_id;";
	$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
	$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
	$P_lagged_records=$sthB->rows;
	if ($DB) {print "LAGGED Records: $P_lagged_records|$stmtB|\n\n";}
	$h=0;
	while ($P_lagged_records > $h)
		{
		@aryB = $sthB->fetchrow_array;
		$time_id[$h] =	$aryB[0];
		$agent[$h] =	$aryB[1];
		$h++;
		}
	$sthB->finish();

	$h=0;
	while ($P_lagged_records > $h)
		{
		$NEXTtime=0;
		$NEXTverb='';
		$NEXTqueue='';
		$NEXTcall_id='';
		##### find the next queue_log record after the PAUSEREASON record
		$stmtB = "SELECT time_id,verb,queue,call_id FROM queue_log where agent='$agent[$h]' and time_id > '$time_id[$h]' order by time_id limit 1;";
		$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
		$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
		$PL_records=$sthB->rows;
		if ($PL_records > 0)
			{
			@aryB = $sthB->fetchrow_array;
			$NEXTtime =		$aryB[0];
			$NEXTverb =		$aryB[1];
			$NEXTqueue =	$aryB[2];
			$NEXTcall_id =	$aryB[3];
			}
		$sthB->finish();

		if ( ($PL_records > 0) && ($NEXTverb =~ /CALLSTATUS|COMPLETECALLER|COMPLETEAGENT/) )
			{
			$NEXTtimePAUSE = ($NEXTtime + 1);
			if ($DB) {print "LAGGED PAUSE DURING CALL: $h|$time_id[$h]|$agent[$h]|$NEXTtime|$NEXTverb|$NEXTqueue|$NEXTcall_id\n";}

			##### update the PAUSEREASON LAGGED record in the queue_log to one second after the end of the call
			$stmtB = "UPDATE queue_log SET time_id='$NEXTtimePAUSE' where agent='$agent[$h]' and time_id='$time_id[$h]' and verb='PAUSEREASON' and data1='LAGGED' limit 1;";
			if ($TEST < 1)
				{
				$Baffected_rows = $dbhB->do($stmtB);
				}
			if ($DB) {print "     PAUSEREASON record updated: $Baffected_rows|$stmtB|\n";}

			$event_string = "LAGGED DURING CALL: $h|$PL_records|$time_id[$h]|$agent[$h]|$NEXTtimePAUSE|$NEXTverb|$NEXTqueue|$NEXTcall_id|$Baffected_rows|$stmtB";
			&event_logger;
			}

		$h++;
		}


	@time_id=@MT;
	@agent=@MT;

	##############################################################
	##### grab all queue_log entries with a verb of AGENTLOGOFF to validate
	$stmtB = "SELECT time_id,agent FROM queue_log where verb='AGENTLOGOFF' $QM_SQL_time_H order by time_id;";
	$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
	$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
	$A_logoff_records=$sthB->rows;
	if ($DB) {print "AGENTLOGOFF Records: $A_logoff_records|$stmtB|\n\n";}
	$h=0;
	while ($A_logoff_records > $h)
		{
		@aryB = $sthB->fetchrow_array;
		$time_id[$h] =	$aryB[0];
		$agent[$h] =	$aryB[1];
		$h++;
		}
	$sthB->finish();

	$h=0;
	while ($A_logoff_records > $h)
		{
		$NEXTtime=0;
		$NEXTverb='';
		$NEXTqueue='';
		$NEXTcall_id='';
		##### find the next queue_log record after the PAUSEREASON record
		$stmtB = "SELECT time_id,verb,queue,call_id FROM queue_log where agent='$agent[$h]' and time_id > '$time_id[$h]' order by time_id limit 1;";
		$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
		$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
		$AL_records=$sthB->rows;
		if ($AL_records > 0)
			{
			@aryB = $sthB->fetchrow_array;
			$NEXTtime =		$aryB[0];
			$NEXTverb =		$aryB[1];
			$NEXTqueue =	$aryB[2];
			$NEXTcall_id =	$aryB[3];
			}
		$sthB->finish();

		if ( ($AL_records > 0) && ($NEXTverb =~ /CALLSTATUS|COMPLETECALLER|COMPLETEAGENT/) )
			{
			$NEXTtimeLOGOFF = ($NEXTtime + 1);
			if ($DB) {print "LOGOFF DURING CALL: $h|$time_id[$h]|$agent[$h]|$NEXTtime|$NEXTverb|$NEXTqueue|$NEXTcall_id\n";}

			##### update the AGENTLOGOFF record in the queue_log to one second after the end of the call
			$stmtB = "UPDATE queue_log SET time_id='$NEXTtimeLOGOFF' where agent='$agent[$h]' and time_id='$time_id[$h]' and verb='AGENTLOGOFF' limit 1;";
			if ($TEST < 1)
				{
				$Baffected_rows = $dbhB->do($stmtB);
				}
			if ($DB) {print "     AGENTLOGOFF record updated: $Baffected_rows|$stmtB|\n";}

			$event_string = "AGENTLOGOFF DURING CALL: $h|$AL_records|$time_id[$h]|$agent[$h]|$NEXTtimeLOGOFF|$NEXTverb|$NEXTqueue|$NEXTcall_id|$Baffected_rows|$stmtB";
			&event_logger;
			}

		$h++;
		}

	if ($qm_live_call_check > 0)
		{
		exit;
		}
	}
### END CHECKING ENTERQUEUE/CALLOUTBOUND ENTRIES FOR LIVE CALLS AND PAUSEREASON-LAGGED/LOGOFF ENTRIES FOR LIVE AGENTS



### BEGIN FIX LOGIN/LAGGED PAUSEREASON ENTRIES (not a recurring process that needs to be run)
if ( ($enable_queuemetrics_logging > 0) && ($login_lagged_check > 0) )
	{
	@time_id=@MT;
	@agent=@MT;
	@verb=@MT;
	@serverid=@MT;
	@lead_id=@MT;

	if ($DB) {print " - Checking for LOGIN and LAGGED pausereason records in queue_log\n";}

	$PAUSEREASONinsert=0;
	##############################################################
	##### grab all queue_log entries for AGENTLOGIN verb to validate
	$stmtB = "SELECT time_id,agent,verb,serverid FROM queue_log where verb='AGENTLOGIN' and serverid='$queuemetrics_log_id' $QM_SQL_time order by time_id;";
	$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
	$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
	$EQ_records=$sthB->rows;
	if ($DB) {print "AGENTLOGIN Records: $EQ_records|$stmtB|\n\n";}
	$h=0;
	while ($EQ_records > $h)
		{
		@aryB = $sthB->fetchrow_array;
		$time_id[$h] =	"$aryB[0]";
		$agent[$h] =	"$aryB[1]";
		$verb[$h] =		"$aryB[2]";
		$serverid[$h] =	"$aryB[3]";
		$h++;
		}
	$sthB->finish();

	$h=0;
	while ($EQ_records > $h)
		{
		$PRtimecheck = ($time_id[$h] + 1);
		$PRtimecheckCOUNT=0;
		##### find the CONNECT details for calls that were sent to agents
		$stmtB = "SELECT count(*) FROM queue_log where verb='PAUSEREASON' and time_id='$PRtimecheck' and agent='$agent[$h]';";
		$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
		$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
		$CQ_records=$sthB->rows;
		if ($CQ_records > 0)
			{
			@aryB = $sthB->fetchrow_array;
			$PRtimecheckCOUNT =		"$aryB[0]";
			}
		$sthB->finish();

		if ($PRtimecheckCOUNT < 1)
			{
			##### insert a PAUSEREASON record for this call into the queue_log
			$stmtB = "INSERT INTO queue_log SET partition='P01',time_id='$PRtimecheck',call_id='NONE',queue='NONE',agent='$agent[$h]',verb='PAUSEREASON',data1='LOGIN',serverid='$serverid[$h]';";
			if ($TEST < 1)
				{
				$Baffected_rows = $dbhB->do($stmtB);
				}
			if ($DB) {print "PRI: $Baffected_rows|$stmtB|\n";}
			$PAUSEREASONinsert++;
			}
		$h++;
		}

	if ($DB) {print " - DONE Checking for LOGIN and LAGGED pausereason records in queue_log\n";}

	exit;
	}
### END FIX LOGIN/LAGGED PAUSEREASON ENTRIES





if ($DB) {print " - cleaning up pause time\n";}
### Grab any pause time record greater than 43999
$stmtA = "SELECT agent_log_id,pause_epoch,wait_epoch from vicidial_agent_log where pause_sec>43999 $VDAD_SQL_time;";
if ($DBX) {print "$stmtA\n";}
#$dbhA->query("$stmtA");
$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
$sthArows=$sthA->rows;

$i=0;
while ($sthArows > $i)
	{
	@aryA = $sthA->fetchrow_array;	
	$DBout = '';
	$agent_log_id[$i]	=		"$aryA[0]";
	$pause_epoch[$i]	=		"$aryA[1]";
	$wait_epoch[$i]	=			"$aryA[2]";
	$pause_sec[$i] = int($wait_epoch[$i] - $pause_epoch[$i]);
	if ( ($pause_sec[$i] < 0) || ($pause_sec[$i] > 43999) ) 
		{
		$DBout = "Override output: $pause_sec[$i]"; 
		$pause_sec[$i] = 0;
		}
	if ($DBX) {print "$i - $agent_log_id[$i]     |$wait_epoch[$i]|$pause_epoch[$i]|$pause_sec[$i]|$DBout|\n";}
	$i++;
	} 

$sthA->finish();
		   
$h=0;
while ($h < $i)
	{
	$stmtA = "UPDATE vicidial_agent_log set pause_sec='$pause_sec[$h]' where agent_log_id='$agent_log_id[$h]';";
		if($DBX){print STDERR "\n|$stmtA|\n";}
	if ($TEST < 1)	{$affected_rows = $dbhA->do($stmtA); }
	$h++;
	}
if ($DB) {print STDERR "     Pause times fixed: $h\n";}


@agent_log_id=@MT;
@wait_epoch=@MT;

if ($DBX) {print "\n\n";}
if ($DB) {print " - cleaning up wait time\n";}
### Grab any pause time record greater than 43999
$stmtA = "SELECT agent_log_id,wait_epoch,talk_epoch from vicidial_agent_log where wait_sec>43999 $VDAD_SQL_time;";
	if ($DBX) {print "$stmtA\n";}

$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
$sthArows=$sthA->rows;
		
$i=0;
while ( $sthArows > $i)
	{
	@aryA = $sthA->fetchrow_array;		
	$DBout = '';
	$agent_log_id[$i]	=		"$aryA[0]";
	$wait_epoch[$i]	=		    "$aryA[1]";
	$talk_epoch[$i]	=			"$aryA[2]";
	$wait_sec[$i] = int($talk_epoch[$i] - $wait_epoch[$i]);
	if ( ($wait_sec[$i] < 0) || ($wait_sec[$i] > 43999) ) 
		{
		$DBout = "Override output: $wait_sec[$i]"; 
		$wait_sec[$i] = 0;
		}
	if ($DBX) {print "$i - $agent_log_id[$i]     |$talk_epoch[$i]|$wait_epoch[$i]|$wait_sec[$i]|$DBout|\n";}
	$i++;
	} 
$sthA->finish();

$h=0;
while ($h < $i)
	{
	$stmtA = "UPDATE vicidial_agent_log set wait_sec='$wait_sec[$h]' where agent_log_id='$agent_log_id[$h]';";
		if($DBX){print STDERR "\n|$stmtA|\n";}
	if ($TEST < 1)	{$affected_rows = $dbhA->do($stmtA); }
	$h++;
	}
if ($DB) {print STDERR "     Wait times fixed: $h\n";}


@agent_log_id=@MT;
@talk_epoch=@MT;

if ($DBX) {print "\n\n";}
if ($DB) {print " - cleaning up talk time\n";}
### Grab any pause time record greater than 43999
$stmtA = "SELECT agent_log_id,talk_epoch,dispo_epoch from vicidial_agent_log where talk_sec>43999 $VDAD_SQL_time;";
	if ($DBX) {print "$stmtA\n";}

$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
$sthArows=$sthA->rows;
	
$i=0;
while ( $sthArows > $i)
	{
	@aryA = $sthA->fetchrow_array;		
	$DBout = '';
	$agent_log_id[$i]	=	"$aryA[0]";
	$talk_epoch[$i]	=		"$aryA[1]";
	$dispo_epoch[$i]	=	"$aryA[2]";
	$talk_sec[$i] = int($dispo_epoch[$i] - $talk_epoch[$i]);
	if ( ($talk_sec[$i] < 0) || ($talk_sec[$i] > 43999) ) 
		{
		$DBout = "Override output: $talk_sec[$i]"; 
		$talk_sec[$i] = 0;
		}
	if ($DBX) {print "$i - $agent_log_id[$i]     |$dispo_epoch[$i]|$talk_epoch[$i]|$talk_sec[$i]|$DBout|\n";}
	$i++;
	} 
$sthA->finish();
 
$h=0;
while ($h < $i)
	{
	$stmtA = "UPDATE vicidial_agent_log set talk_sec='$talk_sec[$h]' where agent_log_id='$agent_log_id[$h]';";
		if($DBX){print STDERR "|$stmtA|\n";}
	if ($TEST < 1)	{$affected_rows = $dbhA->do($stmtA);  }
	$h++;
	}
if ($DB) {print STDERR "     Talk times fixed: $h\n";}



@agent_log_id=@MT;
@dispo_epoch=@MT;

if ($DBX) {print "\n\n";}
if ($DB) {print " - cleaning up dispo time\n";}
	$stmtA = "UPDATE vicidial_agent_log set dispo_sec='0' where dispo_sec>43999 $VDAD_SQL_time;";
		if($DBX){print STDERR "|$stmtA|\n";}
if ($TEST < 1)
	{
	$affected_rows = $dbhA->do($stmtA); 	
	}
if ($DB) {print STDERR "     Bad Dispo times zeroed out: $affected_rows\n";}





if ($enable_queuemetrics_logging > 0)
	{
	if ($skip_queue_log_inserts < 1)
		{
		$COMPLETEinsert=0;
		$COMPLETEupdate=0;
		$COMPLETEqueue=0;
		$CONNECTinsert=0;
		$noCONNECT=0;
		$noCALLSTATUS=0;
		$noCOMPLETEinsert=0;

		##############################################################
		##### grab all queue_log entries for ENTERQUEUE verb to validate
		$stmtB = "SELECT time_id,call_id,queue,agent,verb,serverid FROM queue_log where verb IN('ENTERQUEUE','CALLOUTBOUND') and serverid='$queuemetrics_log_id' $QM_SQL_time order by time_id;";
		$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
		$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
		$EQ_records=$sthB->rows;
		if ($DB) {print "ENTERQUEUE Records: $EQ_records|$stmtB|\n\n";}
		$h=0;
		while ($EQ_records > $h)
			{
			@aryB = $sthB->fetchrow_array;
			$time_id[$h] =	"$aryB[0]";
			$call_id[$h] =	"$aryB[1]";
			$queue[$h] =	"$aryB[2]";
			$agent[$h] =	"$aryB[3]";
			$verb[$h] =		"$aryB[4]";
			$serverid[$h] =	"$aryB[5]";
			$h++;
			}
		$sthB->finish();

		$h=0;
		while ($EQ_records > $h)
			{
			##### find the CONNECT details for calls that were sent to agents
			$stmtB = "SELECT time_id,call_id,queue,agent,verb,serverid,data1 FROM queue_log where verb='CONNECT' and call_id='$call_id[$h]' $QM_SQL_time;";
			$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
			$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
			$CQ_records=$sthB->rows;
			if ($CQ_records > 0)
				{
				@aryB = $sthB->fetchrow_array;
				$Ctime_id[$h] =		"$aryB[0]";
				$Ccall_id[$h] =		"$aryB[1]";
				$Cqueue[$h] =		"$aryB[2]";
				$Cagent[$h] =		"$aryB[3]";
				$Cverb[$h] =		"$aryB[4]";
				$Cserverid[$h] =	"$aryB[5]";
				$Cdata1[$h] =		"$aryB[6]";
				}
			$sthB->finish();

			if ( ($CQ_records > 0) && ($Ctime_id[$h] > 1000) )
				{
				##### find the CALLSTATUS details for calls that were dispositioned by an agent
				$stmtB = "SELECT time_id,call_id,queue,agent,verb,serverid FROM queue_log where verb='CALLSTATUS' and call_id='$call_id[$h]' and agent='$Cagent[$h]';";
				$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
				$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
				$SQ_records=$sthB->rows;
				if ($SQ_records > 0)
					{
					@aryB = $sthB->fetchrow_array;
					$Stime_id[$h] =		"$aryB[0]";
					$Scall_id[$h] =		"$aryB[1]";
					$Squeue[$h] =		"$aryB[2]";
					$Sagent[$h] =		"$aryB[3]";
					$Sverb[$h] =		"$aryB[4]";
					$Sserverid[$h] =	"$aryB[5]";
					$Slead_id[$h] = substr($Scall_id[$h], 11, 9);
					$Slead_id[$h] = ($Slead_id[$h] + 0);
					}
				$sthB->finish();

				if ( ($SQ_records > 0) && ($Stime_id[$h] > 1000) )
					{
					##### check if there is a COMPLETEAGENT or COMPLETECALLER record for this call_id
					$stmtB = "SELECT count(*) FROM queue_log where verb IN('COMPLETEAGENT','COMPLETECALLER') and call_id='$call_id[$h]' and agent='$Cagent[$h]';";
					$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
					$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
					$MQ_records=$sthB->rows;
					if ($MQ_records > 0)
						{
						@aryB = $sthB->fetchrow_array;
						$COMPLETEcount[$h] =		"$aryB[0]";
						}
					$sthB->finish();
					if ($COMPLETEcount[$h] > 0)
						{
						##### check that the queue is set properly
						$stmtB = "SELECT count(*) FROM queue_log where verb IN('COMPLETEAGENT','COMPLETECALLER') and call_id='$call_id[$h]' and agent='$Cagent[$h]' and queue='$queue[$h]';";
						$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
						$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
						$QQ_records=$sthB->rows;
						if ($QQ_records > 0)
							{
							@aryB = $sthB->fetchrow_array;
							$COMPLETEqueue[$h] =		"$aryB[0]";
							}
						$sthB->finish();
						if ($COMPLETEqueue[$h] < 1)
							{
							$stmtB = "UPDATE queue_log SET queue='$queue[$h]' where verb IN('COMPLETEAGENT','COMPLETECALLER') and call_id='$call_id[$h]' and agent='$Cagent[$h]';";
							if ($TEST < 1)
								{
								$Baffected_rows = $dbhB->do($stmtB);
								}
							if ($DB) {print "MCRI: $Baffected_rows|$stmtB|\n";}
							$COMPLETEupdate++;
							}
						}
					else
						{
						$secX = time();
						$Rtarget = ($secX - 21600);	# look for VDCL entry within last 6 hours
						($Rsec,$Rmin,$Rhour,$Rmday,$Rmon,$Ryear,$Rwday,$Ryday,$Risdst) = localtime($Rtarget);
						$Ryear = ($Ryear + 1900);
						$Rmon++;
						if ($Rmon < 10) {$Rmon = "0$Rmon";}
						if ($Rmday < 10) {$Rmday = "0$Rmday";}
						if ($Rhour < 10) {$Rhour = "0$Rhour";}
						if ($Rmin < 10) {$Rmin = "0$Rmin";}
						if ($Rsec < 10) {$Rsec = "0$Rsec";}
							$RSQLdate = "$Ryear-$Rmon-$Rmday $Rhour:$Rmin:$Rsec";

						### find original queue position of the call
						$queue_position=1;
						$queue_seconds=0;
						$stmtA = "SELECT queue_position,queue_seconds FROM vicidial_closer_log where lead_id='$Slead_id[$h]' and campaign_id='$Squeue[$h]' and call_date > \"$RSQLdate\" order by closecallid desc limit 1;";
						$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
						$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
						$sthArows=$sthA->rows;
						if ($sthArows > 0)
							{
							@aryA = $sthA->fetchrow_array;
							$queue_position =	$aryA[0];
							$queue_seconds =	int($aryA[1] + .5);
							}
						$sthA->finish();

						##### insert a COMPLETEAGENT record for this call into the queue_log
						$CALLtime[$h] = ($Stime_id[$h] - $time_id[$h]);
						$stmtB = "INSERT INTO queue_log SET partition='P01',time_id='$Stime_id[$h]',call_id='$Scall_id[$h]',queue='$Squeue[$h]',agent='$Sagent[$h]',verb='COMPLETEAGENT',data1='$Cdata1[$h]',data2='$CALLtime[$h]',data3='$queue_position',serverid='$Sserverid[$h]';";
						if ($TEST < 1)
							{
							$Baffected_rows = $dbhB->do($stmtB);
							}
						if ($DB) {print "MCRI: $Baffected_rows|$stmtB|\n";}
						$COMPLETEinsert++;
						}
					}
				else
					{
					if ($DB) {print "NO CALLSTATUS: $Ctime_id[$h]|$Ccall_id[$h]|$Cagent[$h]   \n";}
					$noCALLSTATUS++;
					##### find the COMPLETE details for calls that were connected to an agent
					$stmtB = "SELECT time_id,call_id,queue,agent,verb,serverid FROM queue_log where verb IN('COMPLETEAGENT','COMPLETECALLER') and call_id='$call_id[$h]' and agent='$Cagent[$h]';";
					$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
					$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
					$SQ_records=$sthB->rows;
					if ($SQ_records > 0)
						{
						@aryB = $sthB->fetchrow_array;
						$Stime_id[$h] =		"$aryB[0]";
						$Scall_id[$h] =		"$aryB[1]";
						$Squeue[$h] =		"$aryB[2]";
						$Sagent[$h] =		"$aryB[3]";
						$Sverb[$h] =		"$aryB[4]";
						$Sserverid[$h] =	"$aryB[5]";
						}
					$sthB->finish();

					if ( ($SQ_records > 0) && ($Stime_id[$h] > 1000) )
						{
						##### insert a CALLSTATUS record for this call into the queue_log
						$CALLtime[$h] = ($Stime_id[$h] - $time_id[$h]);
						$stmtB = "INSERT INTO queue_log SET partition='P01',time_id='$Stime_id[$h]',call_id='$Scall_id[$h]',queue='$Cqueue[$h]',agent='$Sagent[$h]',verb='CALLSTATUS',data1='PU',serverid='$Sserverid[$h]';";
						if ($TEST < 1)
							{
							$Baffected_rows = $dbhB->do($stmtB);
							}
						if ($DB) {print "MCSI: $Baffected_rows|$stmtB|\n";}
						$CONNECTinsert++;
						}
					else
						{
						$old_call_sec = ($secX - 10800);
						if ($Ctime_id[$h] < $old_call_sec) 
							{
							$search_sec_BEGIN = ($Ctime_id[$h] - 3600);
							$search_sec_END = ($Ctime_id[$h] + 3600);
							$search_lead_id = substr($call_id[$h], 11, 9);
							$search_lead_id = ($search_lead_id + 0);
							$VALuser = $Cagent[$h];
							$VALuser =~ s/Agent\///gi;

							##### insert a COMPLETEAGENT record for this call into the queue_log
							$stmtA = "SELECT pause_epoch,wait_epoch,talk_epoch,dispo_epoch,status FROM vicidial_agent_log where lead_id='$search_lead_id' and user='$VALuser' and pause_epoch > \"$search_sec_BEGIN\" and pause_epoch < \"$search_sec_END\" order by pause_epoch desc;";
							$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
							$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
							$sthArows=$sthA->rows;
							$rec_count=0;
							while ($sthArows > $rec_count)
								{
								 @aryA = $sthA->fetchrow_array;
									$VALpause =	"$aryA[0]";
									$VALwait =	"$aryA[1]";
									$VALtalk =	"$aryA[2]";
									$VALdispo =	"$aryA[3]";
									$VALstatus ="$aryA[4]";
								 $rec_count++;
								}
							$sthA->finish();
							
							if ($rec_count > 0)
								{
								$Stime_id[$h]=0;
								if ($VALwait >= $Ctime_id[$h]) {$Stime_id[$h] = $VALwait;}
								if ($VALtalk >= $Ctime_id[$h]) {$Stime_id[$h] = $VALtalk;}
								if ($VALdispo >= $Ctime_id[$h]) {$Stime_id[$h] = $VALdispo;}
								if ($Stime_id[$h] < 1) {$Stime_id[$h] = ($time_id[$h] + 1);}
								$VALstatus =~ s/ //gi;
								if ( ($VALstatus =~ /NULL/i) || (length($VALstatus<1)) ) {$VALstatus='ERI';}

								$Clead_id[$h] = substr($Ccall_id[$h], 11, 9);
								$Clead_id[$h] = ($Clead_id[$h] + 0);

								$secX = time();
								$Rtarget = ($secX - 21600);	# look for VDCL entry within last 6 hours
								($Rsec,$Rmin,$Rhour,$Rmday,$Rmon,$Ryear,$Rwday,$Ryday,$Risdst) = localtime($Rtarget);
								$Ryear = ($Ryear + 1900);
								$Rmon++;
								if ($Rmon < 10) {$Rmon = "0$Rmon";}
								if ($Rmday < 10) {$Rmday = "0$Rmday";}
								if ($Rhour < 10) {$Rhour = "0$Rhour";}
								if ($Rmin < 10) {$Rmin = "0$Rmin";}
								if ($Rsec < 10) {$Rsec = "0$Rsec";}
									$RSQLdate = "$Ryear-$Rmon-$Rmday $Rhour:$Rmin:$Rsec";

								### find original queue position of the call
								$queue_position=1;
								$queue_seconds=0;
								$stmtA = "SELECT queue_position,queue_seconds FROM vicidial_closer_log where lead_id='$Clead_id[$h]' and campaign_id='$Cqueue[$h]' and call_date > \"$RSQLdate\" order by closecallid desc limit 1;";
								$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
								$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
								$sthArows=$sthA->rows;
								if ($sthArows > 0)
									{
									@aryA = $sthA->fetchrow_array;
									$queue_position =	$aryA[0];
									$queue_seconds =	int($aryA[1] + .5);
									}
								$sthA->finish();

								##### insert a COMPLETEAGENT record for this call into the queue_log
								$CALLtime[$h] = ($Stime_id[$h] - $time_id[$h]);
								$stmtB = "INSERT INTO queue_log SET partition='P01',time_id='$Stime_id[$h]',call_id='$Ccall_id[$h]',queue='$Cqueue[$h]',agent='$Cagent[$h]',verb='COMPLETEAGENT',data1='$Cdata1[$h]',data2='$CALLtime[$h]',data3='$queue_position',serverid='$Cserverid[$h]';";
								if ($TEST < 1)
									{
									$Baffected_rows = $dbhB->do($stmtB) or die "ERROR: $stmtB" . DBI->errstr;
									}
								if ($DB) {print "MNCI: $Baffected_rows|$stmtB|$TEST\n";}

								##### insert a CALLSTATUS record for this call into the queue_log
								$CALLtime[$h] = ($Stime_id[$h] - $time_id[$h]);
								$stmtB = "INSERT INTO queue_log SET partition='P01',time_id='$Stime_id[$h]',call_id='$Ccall_id[$h]',queue='$Cqueue[$h]',agent='$Cagent[$h]',verb='CALLSTATUS',data1='$VALstatus',serverid='$Cserverid[$h]';";
								if ($TEST < 1)
									{
									$Baffected_rows = $dbhB->do($stmtB) or die "ERROR: $stmtB" . DBI->errstr;
									}
								if ($DB) {print "MNCI: $Baffected_rows|$stmtB|$TEST\n";}
								$noCOMPLETEinsert++;

								}
							}
						}
					}
				}
			else
				{
				if ($DBX) {print "NO CONNECT: $time_id[$h]|$call_id[$h]|$queue[$h]   \n";}
				$noCONNECT++;
				}
			if ($DB) 
				{
				($Dsec,$Dmin,$Dhour,$Dmday,$Dmon,$Dyear,$Dwday,$Dyday,$Disdst) = localtime($time_id[$h]);
				$Dyear = ($Dyear + 1900);
				$Dmon++;
				if ($Dmon < 10) {$Dmon = "0$Dmon";}
				if ($Dmday < 10) {$Dmday = "0$Dmday";}
				if ($Dhour < 10) {$Dhour = "0$Dhour";}
				if ($Dmin < 10) {$Dmin = "0$Dmin";}
				if ($Dsec < 10) {$Dsec = "0$Dsec";}
					$DBSQLdate = "$Dyear-$Dmon-$Dmday $Dhour:$Dmin:$Dsec";

				if ($h =~ /0$/) {$k='+';}
				if ($h =~ /1$/) {$k='|';}
				if ($h =~ /2$/) {$k='/';}
				if ($h =~ /3$/) {$k='-';}
				if ($h =~ /4$/) {$k="\\";}
				if ($h =~ /5$/) {$k='|';}
				if ($h =~ /6$/) {$k='/';}
				if ($h =~ /7$/) {$k='-';}
				if ($h =~ /8$/) {$k="\\";}
				if ($h =~ /9$/) {$k='0';}
				print STDERR "$k  $noCONNECT $noCALLSTATUS $COMPLETEinsert|$COMPLETEupdate $CONNECTinsert $noCOMPLETEinsert $h/$EQ_records  $DBSQLdate|$time_id[$h]   $Ctime_id[$h]|$CQ_records   $Stime_id[$h]|$SQ_records   $call_id[$h]|$COMPLETEcount[$h]\r";
				}
			$h++;
			}
		}
	


	##############################################################
	##### grab all queue_log entries for COMPLETEAGENT verb to validate queue
	$stmtB = "SELECT time_id,call_id,queue,agent,serverid FROM queue_log where verb='COMPLETEAGENT' and serverid='$queuemetrics_log_id' $QM_SQL_time order by time_id;";
	$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
	$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
	$EQ_records=$sthB->rows;
	if ($DB) {print "COMPLETEAGENT Records: $EQ_records|$stmtB|\n\n";}
	$h=0;
	while ($EQ_records > $h)
		{
		@aryB = $sthB->fetchrow_array;
		$time_id[$h] =	"$aryB[0]";
		$call_id[$h] =	"$aryB[1]";
		$queue[$h] =	"$aryB[2]";
		$agent[$h] =	"$aryB[3]";
		$serverid[$h] =	"$aryB[4]";
		$h++;
		}
	$sthB->finish();

	$h=0;
	while ($EQ_records > $h)
		{
		if (length($queue[$h])<1)
			{
			$CAQqueue[$h]='';
			##### find queue ID for this call
			$stmtB = "SELECT queue FROM queue_log WHERE verb='CONNECT' and serverid='$queuemetrics_log_id' and call_id='$call_id[$h]' and agent='$agent[$h]';";
			$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
			$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
			$MXC_records=$sthB->rows;
			if ($MXC_records > 0)
				{
				@aryB = $sthB->fetchrow_array;
				$CAQqueue[$h] =	$aryB[0];
				}
			$sthB->finish();

			##### update queue ID in this COMPLETEAGENT record
			$stmtB = "UPDATE queue_log SET queue='$CAQqueue[$h]' WHERE verb='COMPLETEAGENT' and serverid='$queuemetrics_log_id' and time_id='$time_id[$h]' and call_id='$call_id[$h]';";
			if ($TEST < 1)	
				{
				$Baffected_rows = $dbhB->do($stmtB);
				$COMPLETEqueue = ($COMPLETEqueue + $Baffected_rows);
				}
			if ($DB) {print "COMPLETEAGENT Record Updated: $Baffected_rows|$stmtB|\n\n";}
			}
		$h++;
		}



	#######################################################################
	##### grab all queue_log entries with more than one COMPLETE verb to clean up
	$stmtB = "SELECT call_id, count(*) FROM queue_log WHERE verb IN('COMPLETEAGENT','COMPLETECALLER','TRANSFER') and serverid='$queuemetrics_log_id' GROUP BY call_id HAVING count(*)>1 ORDER BY count(*) DESC;";
	$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
	$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
	$XC_records=$sthB->rows;
	if ($DB) {print "Extra COMPLETE Records: $XC_records|$stmtB|\n\n";}
	$h=0;
	while ($XC_records > $h)
		{
		@aryB = $sthB->fetchrow_array;
		$CDcall_id[$h] =	"$aryB[0]";
		$h++;
		}
	$sthB->finish();

	$h=0;
	while ($XC_records > $h)
		{
		##### grab oldest COMPLETE record to delete
		$stmtB = "DELETE FROM queue_log WHERE call_id='$CDcall_id[$h]' and verb IN('COMPLETEAGENT','COMPLETECALLER','TRANSFER') ORDER BY unique_row_count DESC LIMIT 1;";
		if ($TEST < 1)	{$Baffected_rows = $dbhB->do($stmtB);  }
		if ($DB) {print "Extra COMPLETE Record Deleted: $Baffected_rows|$stmtB|\n\n";}

		$h++;
		}


	##########################################################################
	##### grab all queue_log COMPLETEAGENT entries with negative call time to clean up
	$stmtB = "SELECT call_id, time_id FROM queue_log WHERE verb IN('COMPLETEAGENT') and data2 < '0' and serverid='$queuemetrics_log_id';";
	$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
	$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
	$XN_records=$sthB->rows;
	if ($DB) {print "Negative COMPLETEAGENT Records: $XN_records|$stmtB|\n\n";}
	$h=0;
	while ($XN_records > $h)
		{
		@aryB = $sthB->fetchrow_array;
		$CNcall_id[$h] =	"$aryB[0]";
		$CNtime_id[$h] =	"$aryB[1]";
		$h++;
		}
	$sthB->finish();

	$h=0;
	while ($XN_records > $h)
		{
		### Get time of CONNECT
		$stmtB = "SELECT time_id FROM queue_log WHERE verb IN('CONNECT') and call_id='$CNcall_id[$h]';";
		$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
		$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
		$XNC_records=$sthB->rows;
		if ($XNC_records < 1)
			{print "ERROR! No CONNECT record for $CNcall_id[$h] $CNtime_id[$h]";}
		else
			{
			@aryB = $sthB->fetchrow_array;
			$CCNtime_id[$h] =	"$aryB[0]";
			$sthB->finish();

			### Get time of CALLSTATUS
			$stmtB = "SELECT time_id FROM queue_log WHERE verb IN('CALLSTATUS') and call_id='$CNcall_id[$h]';";
			$sthB = $dbhB->prepare($stmtB) or die "preparing: ",$dbhB->errstr;
			$sthB->execute or die "executing: $stmtB ", $dbhB->errstr;
			$XNS_records=$sthB->rows;
			if ($XNS_records < 1)
				{print "ERROR! No CALLSTATUS record for $CNcall_id[$h] $CNtime_id[$h]";}
			else
				{
				@aryB = $sthB->fetchrow_array;
				$CSNtime_id[$h] =	"$aryB[0]";
				$sthB->finish();

				if ($CSNtime_id[$h] < $CCNtime_id[$h])
					{
					##### update CALLSTATUS record to CONNECT time_id
					$stmtB = "UPDATE queue_log SET time_id='$CCNtime_id[$h]' WHERE call_id='$CNcall_id[$h]' and verb IN('CALLSTAUTS') LIMIT 1;";
					if ($TEST < 1)	{$Baffected_rows = $dbhB->do($stmtB);  }
					if ($DB) {print "CALLSTATUS time_id Record Updated: $Baffected_rows|$stmtB|\n\n";}
					}
				}
			if ($CNtime_id[$h] < $CCNtime_id[$h])
				{
				##### update COMPLETEAGENT record to CONNECT time_id and 0 data2
				$stmtB = "UPDATE queue_log SET time_id='$CCNtime_id[$h]',data2='0' WHERE call_id='$CNcall_id[$h]' and verb IN('COMPLETEAGENT') LIMIT 1;";
				if ($TEST < 1)	{$Baffected_rows = $dbhB->do($stmtB);  }
				if ($DB) {print "COMPLETEAGENT time_id Record Updated: $Baffected_rows|$stmtB|\n";}
				if ($DB) {print "Debug: $CCNtime_id[$h]|$CSNtime_id[$h]|$CNtime_id[$h]|$CNcall_id[$h]|\n\n";}
				}
			}
		$h++;
		}


	$dbhB->disconnect();
	}







	if ($DB) {print STDERR "\nDONE\n";}



#	$dbhA->close;


exit;






sub event_logger
	{
	### open the log file for writing ###
	open(Lout, ">>$CLEANLOGfile")
			|| die "Can't open $CLEANLOGfile: $!\n";
	print Lout "$HDSQLdate|$event_string|\n";
	close(Lout);
	$event_string='';
	}
