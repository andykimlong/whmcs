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
$where = [];
if ($clientid) {
    $where["userid"] = $clientid;
}
if ($invoiceid) {
    $where["invoiceid"] = $invoiceid;
}
if ($transid) {
    $where["transid"] = $transid;
}
$result = select_query("tblaccounts", "", $where);
$apiresults = ["result" => "success", "totalresults" => mysql_num_rows($result), "startnumber" => 0, "numreturned" => mysql_num_rows($result)];
while ($data = mysql_fetch_assoc($result)) {
    $apiresults["transactions"]["transaction"][] = $data;
}
$responsetype = "xml";

?>