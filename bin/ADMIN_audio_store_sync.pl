#!/usr/bin/perl
#
# ADMIN_audio_store_sync.pl      version 2.2.0
#
# DESCRIPTION:
# syncronizes audio between audio store and this server
#
# 
# Copyright (C) 2009  Matt Florell <vicidial@gmail.com>    LICENSE: AGPLv2
#
# CHANGELOG
# 90513-0458 - First Build
# 90518-2107 - Added force-upload option
#

# constants
$DB=0;
$US='__';
$MT[0]='';
$uploaded=0;
$downloaded=0;

$secT = time();
$now_date_epoch = $secT;
($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
$year = ($year + 1900);
$mon++;
if ($mon < 10) {$mon = "0$mon";}
if ($mday < 10) {$mday = "0$mday";}
if ($hour < 10) {$Fhour = "0$hour";}
if ($min < 10) {$min = "0$min";}
if ($sec < 10) {$sec = "0$sec";}
$file_date = "$year-$mon-$mday";
$now_date = "$year-$mon-$mday $hour:$min:$sec";
$VDL_date = "$year-$mon-$mday 00:00:01";
$inactive_epoch = ($secT - 60);
$HHMM = "$hour$min";

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
		print "allowed run time options(must stay in this order):\n";
		print "  [--debug] = debug\n";
		print "  [--debugX] = super debug\n";
		print "  [-t] = test\n";
		print "  [--upload] = upload audio not found on audio store\n";
		print "  [--force-download] = force download of everything from audio store\n";
		print "  [--force-upload] = force upload of all local audio files to the audio store\n";
		print "  [--settings-override] = ignore database settings and run sync anyway\n";
		print "\n";
		exit;
		}
	else
		{
		if ($args =~ /--debug/i)
			{
			$DB=1;
			print "\n----- DEBUG -----\n\n";
			}
		if ($args =~ /-upload/i)
			{
			$upload=1;
			if ($DB) {print "\n----- UPLOAD -----\n\n";}
			}
		if ($args =~ /--force-download/i)
			{
			$force_download=1;
			if ($DB) {print "\n----- FORCE DOWNLOAD -----\n\n";}
			}
		if ($args =~ /--force-upload/i)
			{
			$force_upload=1;
			if ($DB) {print "\n----- FORCE UPLOAD -----\n\n";}
			}
		if ($args =~ /--settings-override/i)
			{
			$settings_override=1;
			if ($DB) {print "\n----- SETTINGS OVERRIDE -----\n\n";}
			}
		if ($args =~ /--debugX/i)
			{
			$DBX=1;
			if ($DB) {print "\n----- SUPER DEBUG -----\n\n";}
			}
		if ($args =~ /-t/i)
			{
			$T=1;   $TEST=1;
			if ($DB) {print "\n-----TESTING -----\n\n";}
			}
		}
	}
else
	{
#	print "no command line options set\n";
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

if (!$VASLOGfile) {$VASLOGfile = "$PATHlogs/audiostore.$year-$mon-$mday";}
if (!$VARDB_port) {$VARDB_port='3306';}

use DBI;	  

$dbhA = DBI->connect("DBI:mysql:$VARDB_database:$VARDB_server:$VARDB_port", "$VARDB_user", "$VARDB_pass")
 or die "Couldn't connect to database: " . DBI->errstr;


### Grab Server values from the database
	$stmtA = "SELECT active_asterisk_server FROM servers where server_ip = '$VARserver_ip';";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$active_asterisk_server = "$aryA[0]";
		}
	$sthA->finish();

### Grab system_settings values from the database
	$stmtA = "SELECT sounds_central_control_active,sounds_web_server,sounds_web_directory FROM system_settings;";
	$sthA = $dbhA->prepare($stmtA) or die "preparing: ",$dbhA->errstr;
	$sthA->execute or die "executing: $stmtA ", $dbhA->errstr;
	$sthArows=$sthA->rows;
	if ($sthArows > 0)
		{
		@aryA = $sthA->fetchrow_array;
		$sounds_central_control_active =	"$aryA[0]";
		$sounds_web_server =				"$aryA[1]";
		$sounds_web_directory =				"$aryA[2]";
		}
	$sthA->finish();

if ( ( ($sounds_central_control_active < 1) || ($active_asterisk_server !~ /Y/) ) && ($settings_override < 1) )
	{
	print "Audio Sync Settings not active, Exiting\n";
	exit;
	}

$stmtA="UPDATE servers SET sounds_update='N' where server_ip='$VARserver_ip';";
$affected_rows = $dbhA->do($stmtA);




### find wget binary
$wgetbin = '';
if ( -e ('/bin/wget')) {$wgetbin = '/bin/wget';}
else 
	{
	if ( -e ('/usr/bin/wget')) {$wgetbin = '/usr/bin/wget';}
	else 
		{
		if ( -e ('/usr/local/bin/wget')) {$wgetbin = '/usr/local/bin/wget';}
		else
			{
			print "Can't find wget binary! Exiting...\n";
			exit;
			}
		}
	}

### find curl binary
$curlbin = '';
if ( -e ('/bin/curl')) {$curlbin = '/bin/curl';}
else 
	{
	if ( -e ('/usr/bin/curl')) {$curlbin = '/usr/bin/curl';}
	else 
		{
		if ( -e ('/usr/local/bin/curl')) {$curlbin = '/usr/local/bin/curl';}
		else
			{
			print "Can't find curl binary! Exiting...\n";
			exit;
			}
		}
	}


$URL = "http://$sounds_web_server/vicidial/audio_store.php?action=LIST";

$URL =~ s/&/\\&/gi;
if ($DB) 
	{print "\n$URL\n";}

$audio_list_file = '/tmp/audio_store_list.txt';
`rm -f $audio_list_file`;
`$wgetbin -q --output-document=$audio_list_file $URL `;

open(list, "$audio_list_file") || die "can't open $audio_list_file: $!\n";
@list = <list>;
close(list);

opendir(sounds, "$PATHsounds");
@sounds= readdir(sounds); 
closedir(sounds);



####### BEGIN download of audio files
if ($DB > 0) {print "REMOTE AUDIO FILES:\n";}

$i=0;
while ($i <= $#list)
	{
	chomp($list[$i]);
	@file_data = split(/\t/, $list[$i]);
	$filename =		$file_data[1];
	$filedate =		$file_data[2];
	$filesize =		$file_data[3];
	$fileepoch =	$file_data[4];
	if ($DB > 0) {print "$i   $filename     $filedate     $filesize     $fileepoch\n";}

	$k=0;
	$found_file=0;
	while ($k <= $#sounds)
		{
		chomp($sounds[$k]);
		$soundname =	$sounds[$k];
		$soundsize =	(-s "$PATHsounds/$sounds[$k]");

		if ( ($filename eq "$soundname") && ($filesize eq "$soundsize") )
			{
			$found_file++;
			}
		$k++;
		}

	if ( ($found_file < 1) || ($force_download > 0) )
		{
		`$wgetbin -q --output-document=$PATHsounds/$filename http://$sounds_web_server/$sounds_web_directory/$filename`;
		$event_string = "DOWNLOADING: $filename     $filesize";
		if ($DB > 0) {print "          $event_string\n";}
		&event_logger;

		$downloaded++;
		}
	else
		{
		if ($DB > 0) {print "     FILE FOUND: $filename\n";}
		}
	$i++;
	}
####### END download of audio files

$total_files = $i;



`rm -f $audio_list_file`;
`$wgetbin -q --output-document=$audio_list_file $URL `;

open(list, "$audio_list_file") || die "can't open $audio_list_file: $!\n";
@list = <list>;
close(list);

opendir(sounds, "$PATHsounds");
@sounds= readdir(sounds); 
closedir(sounds);





####### BEGIN upload of audio files
if ($upload > 0)
	{
	if ($DB > 0) {print "LOCAL AUDIO FILES:\n";}

	$k=0;
	while ($k <= $#sounds)
		{
		chomp($sounds[$k]);
		if ($sounds[$k] =~ /\.wav$|\.gsm$/)
			{
			$soundname =	$sounds[$k];
			$sounddate =	(-M "$PATHsounds/$sounds[$k]");
			$soundsize =	(-s "$PATHsounds/$sounds[$k]");
			$soundsec =	($sounddate * 86400);
			$soundepoch =	($secT - $soundsec);

			if ($DB > 0) {print "$k   $soundname     $sounddate     $soundsize     $soundepoch\n";}

			$i=0;
			$found_file=0;
			while ($i <= $#list)
				{
				chomp($list[$i]);
				@file_data = split(/\t/, $list[$i]);
				$filename =		$file_data[1];
				$filedate =		$file_data[2];
				$filesize =		$file_data[3];
				$fileepoch =	$file_data[4];

				if ( ($filename eq "$soundname") && ($filesize eq "$soundsize") )
					{
					$found_file++;
					}
				$i++;
				}

			if ( ($found_file < 1) || ($force_upload > 0) )
				{
				$curloptions = "-s 'http://$sounds_web_server/vicidial/audio_store.php?action=AUTOUPLOAD' -F \"audiofile=\@$PATHsounds/$soundname\"";
				`$curlbin $curloptions`;
				$event_string = "UPLOADING: $soundname     $soundsize";
				if ($DB > 0) {print "          $event_string\n|$curlbin $curloptions|\n";}
				&event_logger;

				$uploaded++;
				}
			else
				{
				if ($DB > 0) {print "     FILE FOUND: $soundname\n";}
				}
			}
		$k++;
		}
	}
####### END upload of audio files




###### If audio was uploaded from this server, set all other servers to update sounds next minute
if ($uploaded > 0)
	{
	$stmtA="UPDATE servers SET sounds_update='Y' where server_ip NOT IN('$VARserver_ip');";
	$affected_rows = $dbhA->do($stmtA);
	}


if($DB)
	{
	print "AUDIO FILES ON SERVER:  $total_files\n";
	print "NEW DOWNLOADED:         $downloaded\n";
	print "NEW UPLOADED:           $uploaded\n\n";

	### calculate time to run script ###
	$secY = time();
	$secZ = ($secY - $secT);

	if (!$q) {print "DONE. Script execution time in seconds: $secZ\n";}
	}

$dbhA->disconnect();

exit;



sub event_logger
	{
	if ($SYSLOG)
		{
		### open the log file for writing ###
		open(Lout, ">>$VASLOGfile")
				|| die "Can't open $VASLOGfile: $!\n";
		print Lout "$now_date|$event_string|\n";
		close(Lout);
		}
	$event_string='';
	}

