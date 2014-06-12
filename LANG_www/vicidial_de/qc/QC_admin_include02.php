<?php
# QC_admin_include02.php
# 
# Copyright (C) 2012  poundteam.com    LICENSE: AGPLv2
#
# This script is designed to be used by admin.php with QC enabled, contributed by poundteam.com
#
# changes:
# 121116-1333 - First build, added to vicidial codebase
# 130902-0902 - Changed to mysqli PHP functions
#
//Get QC User permissions
$stmt="SELECT qc_enabled,qc_user_level,qc_pass,qc_finish,qc_commit from vicidial_users where user='$PHP_AUTH_USER' and user_level > 1 and active='Y' and qc_enabled='1';";
if ($DB) {echo "|$stmt|\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$qc_auth=$row[0];
//Not "qc_" as it will interfere with ADD=4A storage of modified user.
if ($qc_auth=='1') {
    $qcuser_level=$row[1];
    $qcpass=$row[2];
    $qcfinish=$row[3];
    $qccommit=$row[4];
}
//Ändernmenuing to allow qc users into the system (if they have no permission otherwise)
//Copied Berichte-Only user setup for QC-Only user (Poundteam QC setup)
$qc_only_user=0;
if ( ($qc_auth > 0) and ($auth < 1) )
        {
        if ($ADD != '881'){
                $ADD=100000000000000;
        }
        $qc_only_user=1;
        }
?>
