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
if (!function_exists("getAdminName")) {
    require ROOTDIR . "/includes/adminfunctions.php";
}
if (!function_exists("affiliateActivate")) {
    require ROOTDIR . "/includes/affiliatefunctions.php";
}
$result = select_query("tblclients", "id", ["id" => $userid]);
$data = mysql_fetch_array($result);
$userid = $data["id"];
if (!$userid) {
    $apiresults = ["result" => "error", "message" => "Client ID not found"];
} else {
    affiliateActivate($userid);
    $apiresults = ["result" => "success"];
}

?>