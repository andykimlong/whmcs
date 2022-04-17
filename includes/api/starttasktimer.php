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
if (!isset($_REQUEST["projectid"])) {
    $apiresults = ["result" => "error", "message" => "Project ID is Required"];
} else {
    if (isset($_REQUEST["projectid"])) {
        $result = select_query("mod_project", "", ["id" => (int) $projectid]);
        $data = mysql_fetch_assoc($result);
        $projectid = $data["id"];
        if (!$projectid) {
            $apiresults = ["result" => "error", "message" => "Project ID Not Found"];
            return NULL;
        }
    }
    if (!isset($_REQUEST["adminid"])) {
        $_REQUEST["adminid"] = $_SESSION["adminid"];
    }
    if (isset($_REQUEST["adminid"])) {
        $result_adminid = select_query("tbladmins", "id", ["id" => $_REQUEST["adminid"]]);
        $data_adminid = mysql_fetch_array($result_adminid);
        if (!$data_adminid["id"]) {
            $apiresults = ["result" => "error", "message" => "Admin ID Not Found"];
            return NULL;
        }
    }
    if (!isset($_REQUEST["taskid"])) {
        $apiresults = ["result" => "error", "message" => "Task ID Not Set"];
    } else {
        if (isset($_REQUEST["taskid"])) {
            $result_taskid = select_query("mod_projecttasks", "id", ["id" => $_REQUEST["taskid"]]);
            $data_taskid = mysql_fetch_array($result_taskid);
            if (!$data_taskid["id"]) {
                $apiresults = ["result" => "error", "message" => "Task ID Not Found"];
                return NULL;
            }
        }
        $projectid = $_REQUEST["projectid"];
        $adminid = (int) $_REQUEST["adminid"];
        $taskid = (int) $_REQUEST["taskid"];
        $start_time = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : time();
        $endtime = $_REQUEST["end_time"];
        $apply = insert_query("mod_projecttimes", ["projectid" => $projectid, "adminid" => $adminid, "taskid" => $taskid, "start" => $start_time, "end" => $endtime]);
        $apiresults = ["result" => "success", "message" => "Start Timer Has Been Set"];
    }
}

?>