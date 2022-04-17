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
if (!$days) {
    $days = 7;
}
if (!$expires) {
    $expires = date("YmdHis", mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $days, date("Y")));
}
$banid = insert_query("tblbannedips", ["ip" => $ip, "reason" => $reason, "expires" => $expires]);
$apiresults = ["result" => "success", "banid" => $banid];

?>