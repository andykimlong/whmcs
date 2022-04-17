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
$result = select_query("tblclients", "id", ["id" => $clientid]);
$data = mysql_fetch_array($result);
$clientid = $data["id"];
if (!$clientid) {
    $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
} else {
    $credits = [];
    $result = select_query("tblcredit", "id,date,description,amount,relid", ["clientid" => $clientid], "date", "ASC");
    while ($data = mysql_fetch_assoc($result)) {
        $credits[] = $data;
    }
    $apiresults = ["result" => "success", "totalresults" => count($credits), "clientid" => $clientid, "credits" => ["credit" => $credits]];
    $responsetype = "xml";
}

?>