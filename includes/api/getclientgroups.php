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
$result = select_query("tblclientgroups", "COUNT(id)", "");
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$apiresults = ["result" => "success", "totalresults" => $totalresults];
$result = select_query("tblclientgroups", "", "", "id", "ASC");
while ($data = mysql_fetch_assoc($result)) {
    $apiresults["groups"]["group"][] = $data;
}
$responsetype = "xml";

?>