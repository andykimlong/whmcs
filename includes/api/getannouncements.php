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
$result = select_query("tblannouncements", "COUNT(*)", "");
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$result = select_query("tblannouncements", "", "", "date", "DESC", $limitstart . "," . $limitnum);
$apiresults = ["result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => mysql_num_rows($result)];
while ($data = mysql_fetch_assoc($result)) {
    $apiresults["announcements"]["announcement"][] = $data;
}
$responsetype = "xml";

?>