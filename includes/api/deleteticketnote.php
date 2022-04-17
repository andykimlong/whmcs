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
$result = select_query("tblticketnotes", "id", ["id" => $noteid]);
$data = mysql_fetch_array($result);
if (!$data["id"]) {
    $apiresults = ["result" => "error", "message" => "Note ID Not Found"];
} else {
    delete_query("tblticketnotes", ["id" => $noteid]);
    $apiresults = ["result" => "success", "noteid" => $noteid];
}

?>