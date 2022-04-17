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
$clientId = (int) App::getFromRequest("clientid");
if (!$clientId) {
    $clientId = (int) App::getFromRequest("userid");
}
logActivity($description, $clientId);
$apiresults = ["result" => "success"];

?>