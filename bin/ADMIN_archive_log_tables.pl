#!/usr/bin/perl
#
# ADMIN_archive_log_tables.pl
#
# This script is designed to put all  records from call_log, vicidial_log and 
# vicidial_agent_log in relevant _archive tables and delete records in original
# tables older than X months from current date. Also, deletes old
# server_performance table records without archiving them as well as optimizing
# all involved tables.
#
# Place in the crontab and run every month after one in the morning, or whenever
# your server is not busy with other tasks
# 30 1 1 * * /usr/share/astguiclient/ADMIN_archive_log_tables.pl
#
# NOTE: On a high-load outbound dialing system, this script can take hours to 
# run. While the script is running the system is unusable. Please schedule to 
# run this script at a time when the system will not be used for several hours.
#
# original author: I. Taushanov(okli)
# Based on perl scripts in ViciDial from Matt Florell and post: 
# http://www.vicidial.org/VICIDIALforum/viewtopic.php?p=22506&sid=ca5347cffa6f6382f56ce3db9fb3d068#22506
#
# Copyright (C) 2010  I. Taushanov, Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGES
# 90615-1701 - First version
# 100101-1722 - Added error safety checks
# 100103-2052 - Formatting fixes, name change, added initial table counts and added archive tables to official SQL files
# 100109-1018 - Added vicidial_carrier_log archiving
# 100328-1008 - Added --months CLI option
#

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
		print "  [--months=XX] = number of months to archive past, must be 12 or less, default is 2\n";
		print "  [-q] = quiet\n";
		print "  [-t] = test\n\n";
		exit;
		}
	else
		{
		if ($args =~ /-q/i)
			{
			$q=1;   $Q=1;
			}
		if ($args =~ /-t/i)
			{
			$T=1;   $TEST=1;
			print "\n-----TESTING-----\n\n";
			}
		if ($args =~ /--months=/i)
			{
			@data_in = split(/--months=/,$args);
			$CLImonths = $data_in[1];
			$CLImonths =~ s/ .*$//gi;
			$CLImonths =~ s/\D//gi;
			if ($CLImonths > 12)
				{$CLImonths=12;}
			if ($Q < 1) 
				{print "\n----- MONTHS OVERRIDE: $CLImonths -----\n\n";}
			}
		}
	}
else
	{
	print "no command line options set\n";
	}
### end parsing run-time options ###
if ( ($CLImonths > 12) || ($CLImonths < 1) )
	{$CLImonths=2;}

$secX = time();
($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
$year = ($year + 1900);
$mon = ($mon - $CLImonths);
if ($mon < 0) 		
	{		
	$mon = ($mon + 12);
	$year = ($year - 1);		
	}	
$mon++;	
if ($mon < 10) {$mon = "0$mon";}	
if ($mday < 10) {$mday = "0$mday";}	

$del_time = "$year-$mon-$mday 01:00:00";

if (!$Q) {print "\n\n-- ADMIN_archive_log_tables.pl --\n\n";}
if (!$Q) {print "This program is designed to put all records from  call_log, vicidial_log,\n";}
if (!$Q) {print "server_performance, vicidial_agent_log and vicidial_carrier_log in relevant\n";}
if (!$Q) {print "_archive tables and delete records in original tables older than\n";}
if (!$Q) {print "$CLImonths months ( $del_time ) from current date \n\n";}


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

use DBI;
$dbhA = DBI->connect("DBI:mysql:$VARDB_database:$VARDB_server:$VARDB_port", "$VARDB_user", "$VARDB_pass")
 or die "Couldn't connect to database: " . DBI->errstr;

if (!$T) 
	{
	##### call_log
	$stmtA = "SELECT count(*) from call_log;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$call_log_count =	$aryA[0];
		}
	$sthA->finish();

	$stmtA = "SELECT count(*) from call_log_archive;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$call_log_archive_count =	$aryA[0];
		}
	$sthA->finish();

	if (!$Q) {print "\nProcessing call_log table...  ($call_log_count|$call_log_archive_count)\n";}
	$stmtA = "INSERT IGNORE INTO call_log_archive SELECT * from call_log;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows = $sthA->rows;
	if (!$Q) {print "$sthArows rows inserted into call_log_archive table\n";}
	
	$rv = $sthA->err();
	if (!$rv)
		{
		$stmtA = "DELETE FROM call_log WHERE start_time < '$del_time';";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		$sthArows = $sthA->rows;
		if (!$Q) {print "$sthArows rows deleted from call_log table \n";}

		$stmtA = "optimize table call_log;";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;

		$stmtA = "DELETE from call_log_archive where channel LIKE 'Local/9%' and extension not IN('8365','8366','8367','8368','8369','8370','8371','8372','8373','8374') and caller_code LIKE 'V%' and length_in_sec < 75 and start_time < '$del_time';";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		$sthArows = $sthA->rows;
		if (!$Q) {print "$sthArows rows deleted from call_log_archive table \n";}

		$stmtA = "optimize table call_log_archive;";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		}



	##### vicidial_log
	$stmtA = "SELECT count(*) from vicidial_log;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$vicidial_log_count =	$aryA[0];
		}
	$sthA->finish();

	$stmtA = "SELECT count(*) from vicidial_log_archive;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$vicidial_log_archive_count =	$aryA[0];
		}
	$sthA->finish();

	if (!$Q) {print "\nProcessing vicidial_log table...  ($vicidial_log_count|$vicidial_log_archive_count)\n";}
	$stmtA = "INSERT IGNORE INTO vicidial_log_archive SELECT * from vicidial_log;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	
	$sthArows = $sthA->rows;
	if (!$Q) {print "$sthArows rows inserted into vicidial_log_archive table \n";}
	
	$rv = $sthA->err();
	if (!$rv) 
		{	
		$stmtA = "DELETE FROM vicidial_log WHERE call_date < '$del_time';";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		$sthArows = $sthA->rows;
		if (!$Q) {print "$sthArows rows deleted from vicidial_log table \n";}

		$stmtA = "optimize table vicidial_log;";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;

		$stmtA = "optimize table vicidial_log_archive;";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		}



	##### server_performance
	$stmtA = "SELECT count(*) from server_performance;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$server_performance_count =	$aryA[0];
		}
	$sthA->finish();

	if (!$Q) {print "\nProcessing server_performance table...  ($server_performance_count)\n";}
	$stmtA = "DELETE FROM server_performance WHERE start_time < '$del_time';";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows = $sthA->rows;
	if (!$Q) {print "$sthArows rows deleted from server_performance table \n";}

	$stmtA = "optimize table server_performance;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;



	##### vicidial_agent_log
	$stmtA = "SELECT count(*) from vicidial_agent_log;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$vicidial_agent_log_count =	$aryA[0];
		}
	$sthA->finish();

	$stmtA = "SELECT count(*) from vicidial_agent_log_archive;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$vicidial_agent_log_archive_count =	$aryA[0];
		}
	$sthA->finish();

	if (!$Q) {print "\nProcessing vicidial_agent table...  ($vicidial_agent_log_count|$vicidial_agent_log_archive_count)\n";}
	$stmtA = "INSERT IGNORE INTO vicidial_agent_log_archive SELECT * from vicidial_agent_log;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows = $sthA->rows;
	if (!$Q) {print "$sthArows rows inserted into vicidial_agent_log_archive table \n";}
	
	$rv = $sthA->err();
	if (!$rv) 
		{
		$stmtA = "DELETE FROM vicidial_agent_log WHERE event_time < '$del_time';";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		$sthArows = $sthA->rows;
		if (!$Q) {print "$sthArows rows deleted from vicidial_agent_log table \n";}

		$stmtA = "optimize table vicidial_agent_log;";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;

		$stmtA = "optimize table vicidial_agent_log_archive;";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		}
	}



	##### vicidial_carrier_log
	$stmtA = "SELECT count(*) from vicidial_carrier_log;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$vicidial_carrier_log_count =	$aryA[0];
		}
	$sthA->finish();

	$stmtA = "SELECT count(*) from vicidial_carrier_log_archive;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$vicidial_carrier_log_archive_count =	$aryA[0];
		}
	$sthA->finish();

	if (!$Q) {print "\nProcessing vicidial_carrier_log table...  ($vicidial_carrier_log_count|$vicidial_carrier_log_archive_count)\n";}
	$stmtA = "INSERT IGNORE INTO vicidial_carrier_log_archive SELECT * from vicidial_carrier_log;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	
	$sthArows = $sthA->rows;
	if (!$Q) {print "$sthArows rows inserted into vicidial_carrier_log_archive table \n";}
	
	$rv = $sthA->err();
	if (!$rv) 
		{	
		$stmtA = "DELETE FROM vicidial_carrier_log WHERE call_date < '$del_time';";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		$sthArows = $sthA->rows;
		if (!$Q) {print "$sthArows rows deleted from vicidial_carrier_log table \n";}

		$stmtA = "optimize table vicidial_carrier_log;";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;

		$stmtA = "optimize table vicidial_carrier_log_archive;";
		$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
		$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
		}


#$dbhA->disconnect();
#print "$del_time\n\n";


### calculate time to run script ###
$secY = time();
$secZ = ($secY - $secX);
$secZm = ($secZ /60);
if (!$Q) {print "\nscript execution time in seconds: $secZ     minutes: $secZm\n";}

exit;
