<?php
# audit_comments.php
# 
# Copyright (C) 2012  poundteam.com    LICENSE: AGPLv2
#
# This script is designed to be used by the QC application, contributed by poundteam.com
#
# changes:
# 121116-1335 - First build, added to vicidial codebase
# 130902-0910 - Changed to mysqli PHP functions
#

function audit_comments($lead_id,$list_id,$format,$user,$mel,$NOW_TIME,$link,$server_ip,$session_name,$one_mysql_log,$campaign) {
    $audit_comments_active=audit_comments_active($list_id,$format,$user,$mel,$NOW_TIME,$link,$server_ip,$session_name,$one_mysql_log);
    if ($audit_comments_active) {
        //Get comment from list
        $stmt="select comments from vicidial_list where lead_id='$lead_id' limit 1;";
        if ($format=='debug') {
            echo "\n<!-- $stmt -->";
        }
        $rslt=mysql_to_mysqli($stmt, $link);
        if ($mel > 0) {
            mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-AuditCommentaires2',$user,$server_ip,$session_name,$one_mysql_log);
        }
        $row=mysqli_fetch_row($rslt);
        if (strlen($row[0]) > 0) {
            $comment=$row[0];
            //Put comment in comment table
            $stmt="INSERT INTO vicidial_comments (lead_id,user_id,list_id,campaign_id,comment) VALUES ('$lead_id','$user','$list_id','$campaign','$comment');";
            if ($format=='debug') {
                echo "\n<!-- $stmt -->";
            }
            $rslt=mysql_to_mysqli($stmt, $link);
            if ($mel > 0) {
                mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-AuditCommentaires3',$user,$server_ip,$session_name,$one_mysql_log);
            }
            $affected=mysqli_affected_rows($link);
            if($affected>0) {
                $stmt="UPDATE vicidial_list set comments='' where lead_id='$lead_id';";
                if ($format=='debug') {
                    echo "\n<!-- $stmt -->";
                }
                $rslt=mysql_to_mysqli($stmt, $link);
                if ($mel > 0) {
                    mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-AuditCommentaires4',$user,$server_ip,$session_name,$one_mysql_log);
                }
            } else {
                mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-AuditCommentairesERROR-Comment not moved',$user,$server_ip,$session_name,$one_mysql_log);
                echo "\n<!-- 00142-AuditCommentairesERROR-Comment not moved -->";
            }
        }
    }
}
function audit_comments_active($list_id,$format,$user,$mel,$NOW_TIME,$link,$server_ip,$session_name,$one_mysql_log){
    $stmt="select count(audit_comments) from vicidial_lists_custom where list_id='$list_id' and audit_comments='1' limit 1;";
    if ($format=='debug') {
        echo "\n<!-- $stmt -->";
    }
    $rslt=mysql_to_mysqli($stmt, $link);
    if ($mel > 0) {
        mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-AuditCommentaires5',$user,$server_ip,$session_name,$one_mysql_log);
    }
    $row=mysqli_fetch_row($rslt);
    if ($row[0] == '1') {
        return true;
    } else {
        return false;
    }
}
function get_audited_comments($lead_id,$format,$user,$mel,$NOW_TIME,$link,$server_ip,$session_name,$one_mysql_log) {
    global $ACcount;
    global $ACcomments;
    $stmt="select user_id,comment from vicidial_comments where lead_id='$lead_id';";
    if ($mel > 0) {
        mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,"00142-65-AuditCommentaires:$stmt LeadID: $lead_id,$format,$user,$mel,$NOW_TIME,$link,$server_ip,$session_name,$one_mysql_log",$user,$server_ip,$session_name,$one_mysql_log);
    }
    $rslt=mysql_to_mysqli($stmt, $link);
    if ($mel > 0) {
        mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-69-AuditCommentaires',$user,$server_ip,$session_name,$one_mysql_log);
    }
    $ACcount=mysqli_num_rows($rslt);
    mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-72-AuditCommentaires $ACcount='.$ACcount,$user,$server_ip,$session_name,$one_mysql_log);
    if($ACcount>0) {
        $i=0;
        while ($i < $ACcount) {
            $row=mysqli_fetch_row($rslt);
            mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-77-AuditCommentaires UtilisateurID='.$row[0],$user,$server_ip,$session_name,$one_mysql_log);
            $ACcomments .=	"UtilisateurID: $row[0]\n";
            mysqli_error_logging($NOW_TIME,$link,$mel,$stmt,'00142-79-AuditCommentaires Comment='.$row[1],$user,$server_ip,$session_name,$one_mysql_log);
            $ACcomments .=	$row[1];
            $ACcomments .=	"\n----------------------------------\n";
            $i++;
        }
        return true;
    } else {
        return false;
    }
}
?>
