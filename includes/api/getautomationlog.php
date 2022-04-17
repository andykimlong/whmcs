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
$startdate = trim(App::getFromRequest("startdate"));
if (!$startdate) {
    $startdate = date("Y-m-d");
}
$enddate = trim(App::getFromRequest("enddate"));
if (!$enddate) {
    $enddate = date("Y-m-d");
}
$namespace = trim(App::getFromRequest("namespace"));
$query = WHMCS\Log\Register::select(WHMCS\Database\Capsule::raw("date_format(created_at, '%Y-%m-%d') AS date, name, namespace, IF((namespace_value REGEXP '^[[:digit:]]+\$'), SUM(namespace_value), namespace_value) AS total_count"));
if ($namespace) {
    $query->where("namespace", "LIKE", $namespace . "%");
} else {
    $query->where("namespace", "!=", "cron.dailyreport");
}
$tempStats = [];
$entries = $query->where("created_at", ">=", $startdate . " 00:00:00")->where("created_at", "<=", $enddate . " 23:59:59")->groupBy("name", "namespace", WHMCS\Database\Capsule::raw("date_format(created_at, '%Y-%m-%d')"))->get();
foreach ($entries as $data) {
    $tempStats[$data->namespace][$data->date] = $data->toArray();
}
$statistics = [];
foreach ($tempStats as $namespace => $stats) {
    $i = 0;
    while ($i <= 90) {
        $date = date("Y-m-d", strtotime($startdate) + $i * 24 * 60 * 60);
        $namespaceParts = explode(".", $namespace, 2);
        $statistics[$date][$namespaceParts[0]][$namespaceParts[1]] = isset($stats[$date]["total_count"]) ? $stats[$date]["total_count"] : 0;
        if ($date != $enddate) {
            $i++;
        }
    }
}
$apiresults = ["result" => "success", "currentDatetime" => date("Y-m-d H:i:s"), "lastDailyCronInvocationTime" => WHMCS\Config\Setting::getValue("lastDailyCronInvocationTime"), "startdate" => $startdate . " 00:00:00", "enddate" => $enddate . " 23:59:59", "statistics" => $statistics];

?>