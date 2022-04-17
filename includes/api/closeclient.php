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
if (!function_exists("closeClient")) {
    require ROOTDIR . "/includes/clientfunctions.php";
}
$result = select_query("tblclients", "id", ["id" => $clientid]);
$data = mysql_fetch_array($result);
if (!$data["id"]) {
    $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
} else {
    closeClient($_REQUEST["clientid"]);
    $apiresults = ["result" => "success", "clientid" => $_REQUEST["clientid"]];
}

?>