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
$statuses = [];
$result = select_query("tblticketstatuses", "", "", "sortorder", "ASC");
while ($data = mysql_fetch_array($result)) {
    $statuses[$data["title"]]["count"] = 0;
    $statuses[$data["title"]]["color"] = $data["color"];
}
$apiresults = ["result" => "success", "totalresults" => count($statuses), "statuses" => ["status" => []]];
$where = "";
$deptid = (int) App::get_req_var("deptid");
$statusesCountQuery = WHMCS\Database\Capsule::table("tbltickets");
if ($deptid) {
    $statusesCountQuery = $statusesCountQuery->where("did", "=", $deptid);
}
$statusesCountResults = $statusesCountQuery->where("merged_ticket_id", "=", 0)->groupBy("status")->pluck(WHMCS\Database\Capsule::raw("count(id)"), "status")->all();
foreach ($statuses as $status => $dataArray) {
    $apiresults["statuses"]["status"][] = ["title" => $status, "count" => $statusesCountResults[$status] ? $statusesCountResults[$status] : 0, "color" => $dataArray["color"]];
}
$responsetype = "xml";

?>