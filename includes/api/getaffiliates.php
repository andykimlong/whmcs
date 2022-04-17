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
if (!$limitstart) {
    $limitstart = 0;
}
if (!$limitnum) {
    $limitnum = 25;
}
$where = [];
if ($userid) {
    $where["clientid"] = (int) $userid;
}
if ($visitors) {
    $where["visitors"] = (int) $visitors;
}
if ($paytype) {
    $where["paytype"] = ["sqltype" => "LIKE", "value" => $paytype];
}
if ($payamount) {
    $where["payamount"] = ["sqltype" => "LIKE", "value" => $payamount];
}
if ($onetime) {
    $where["onetime"] = (int) $onetime;
}
if ($balance) {
    $where["balance"] = ["sqltype" => "LIKE", "value" => $balance];
}
if ($withdrawn) {
    $where["withdrawn"] = ["sqltype" => "LIKE", "value" => $withdrawn];
}
if ($userid) {
    $result_user = select_query("tblaffiliates", "clientid", ["clientid" => $userid]);
    $data_user = mysql_fetch_array($result_user);
    $userid = $data_user["clientid"];
    if (!$userid) {
        $apiresults = ["result" => "error", "message" => "Client ID not found"];
        return NULL;
    }
}
$result = select_query("tblaffiliates", "COUNT(*)", $where);
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$result2 = select_query("tblaffiliates", "", $where, "id", "ASC", (int) $limitstart . "," . (int) $limitnum);
$apiresults = ["result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => mysql_num_rows($result2), "affiliates" => []];
while ($data3 = mysql_fetch_assoc($result2)) {
    $apiresults["affiliates"]["affiliate"][] = $data3;
}
$responsetype = "xml";

?>