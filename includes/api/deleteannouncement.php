<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$result = select_query("tblannouncements", "id", ["id" => $announcementid]);
$data = mysql_fetch_array($result);
if (!$data["id"]) {
    $apiresults = ["result" => "error", "message" => "Announcement ID Not Found"];
    return false;
}
delete_query("tblannouncements", ["id" => $announcementid]);
delete_query("tblannouncements", ["parentid" => $announcementid]);
$apiresults = ["result" => "success", "announcementid" => $announcementid];

?>