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
$status = App::getFromRequest("status");
$where = [];
if ($status == "Incomplete") {
    $where["status"] = ["sqltype" => "NEQ", "value" => "Completed"];
} else {
    if ($status) {
        $where["status"] = $status;
    }
}
$result = select_query("tbltodolist", "COUNT(id)", $where);
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$result = select_query("tbltodolist", "", $where, "duedate", "DESC", $limitstart . "," . $limitnum);
$apiresults = ["result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => mysql_num_rows($result)];
while ($data = mysql_fetch_assoc($result)) {
    $data["title"] = $data["title"];
    $data["description"] = strip_tags($data["description"]);
    $apiresults["items"]["item"][] = $data;
}
$responsetype = "xml";

?>