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
if (!function_exists("getClientsDetails")) {
    require ROOTDIR . "/includes/clientfunctions.php";
}
if (!function_exists("saveCustomFields")) {
    require ROOTDIR . "/includes/customfieldfunctions.php";
}
if (isset($_REQUEST["projectid"])) {
    $result = select_query("mod_project", "", ["id" => (int) $projectid]);
    $data = mysql_fetch_assoc($result);
    $projectid = $data["id"];
    if (!$projectid) {
        $apiresults = ["result" => "error", "message" => "Project ID Not Found"];
        return NULL;
    }
}
if (!isset($_REQUEST["timerid"])) {
    $apiresults = ["result" => "error", "message" => "Timer ID Not Set"];
} else {
    if (isset($_REQUEST["timerid"])) {
        $result = select_query("mod_projecttimes", "", ["id" => $_REQUEST["timerid"]]);
        $data_timerid = mysql_fetch_assoc($result);
        $timerid = $data_timerid["id"];
        if (!$timerid) {
            $apiresults = ["result" => "error", "message" => "Timer ID Not Found"];
            return NULL;
        }
    }
    $timerid = $data_timerid["id"];
    if (isset($_REQUEST["adminid"])) {
        $result_adminid = select_query("tbladmins", "id", ["id" => $_REQUEST["adminid"]]);
        $data_adminid = mysql_fetch_array($result_adminid);
        if (!$data_adminid["id"]) {
            $apiresults = ["result" => "error", "message" => "Admin ID Not Found"];
            return NULL;
        }
    }
    $projectid = $_REQUEST["projectid"];
    $adminid = $_REQUEST["adminid"];
    $endtime = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : time();
    $updateqry = [];
    if ($projectid) {
        $updateqry["projectid"] = $projectid;
    }
    if ($adminid) {
        $updateqry["adminid"] = $adminid;
    }
    if ($timerid) {
        $updateqry["end"] = $endtime;
    }
    update_query("mod_projecttimes", $updateqry, ["id" => $timerid]);
    $apiresults = ["result" => "success", "message" => "Timer Has Ended"];
}

?>