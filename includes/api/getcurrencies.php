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
$result = select_query("tblcurrencies", "", "", "id", "ASC");
$apiresults = ["result" => "success", "totalresults" => mysql_num_rows($result)];
while ($data = mysql_fetch_array($result)) {
    $id = $data["id"];
    $code = $data["code"];
    $prefix = $data["prefix"];
    $suffix = $data["suffix"];
    $format = $data["format"];
    $rate = $data["rate"];
    $apiresults["currencies"]["currency"][] = ["id" => $id, "code" => $code, "prefix" => $prefix, "suffix" => $suffix, "format" => $format, "rate" => $rate];
}
$responsetype = "xml";

?>