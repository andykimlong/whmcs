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
if (!function_exists("getGatewaysArray")) {
    require ROOTDIR . "/includes/gatewayfunctions.php";
}
$paymentmethods = getGatewaysArray();
$apiresults = ["result" => "success", "totalresults" => count($paymentmethods)];
foreach ($paymentmethods as $module => $name) {
    $apiresults["paymentmethods"]["paymentmethod"][] = ["module" => $module, "displayname" => $name];
}
$responsetype = "xml";

?>