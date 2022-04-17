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
if (isset($_REQUEST["userid"])) {
    $result_userid = select_query("tblclients", "id", ["id" => $_REQUEST["userid"]]);
    $data_userid = mysql_fetch_array($result_userid);
    if (!$data_userid["id"]) {
        $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
        return NULL;
    }
}
if (!isset($_REQUEST["adminid"])) {
    $apiresults = ["result" => "error", "message" => "Admin ID not Set"];
} else {
    if (isset($_REQUEST["adminid"])) {
        $result_adminid = select_query("tbladmins", "id", ["id" => $_REQUEST["adminid"]]);
        $data_adminid = mysql_fetch_array($result_adminid);
        if (!$data_adminid["id"]) {
            $apiresults = ["result" => "error", "message" => "Admin ID Not Found"];
            return NULL;
        }
    }
    $version = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "=", "project_management")->where("setting", "=", "version")->first();
    if (!$version instanceof stdClass) {
        $apiresults = ["result" => "error", "message" => "Project Management is not active."];
    } else {
        if (!trim($_REQUEST["title"])) {
            $apiresults = ["result" => "error", "message" => "Project Title is Required."];
        } else {
            $status = get_query_val("tbladdonmodules", "value", ["module" => "project_management", "setting" => "statusvalues"]);
            $validStatus = explode(",", $status);
            $projectStatus = $validStatus[0];
            if (isset($_REQUEST["status"]) && in_array($_REQUEST["status"], $validStatus)) {
                $projectStatus = $_REQUEST["status"];
            }
            $created = !isset($_REQUEST["created"]) ? date("Y-m-d") : $_REQUEST["created"];
            $duedate = !isset($_REQUEST["duedate"]) ? date("Y-m-d") : $_REQUEST["duedate"];
            $completed = isset($_REQUEST["completed"]) ? 1 : 0;
            $projectid = insert_query("mod_project", ["userid" => $_REQUEST["userid"], "title" => $_REQUEST["title"], "ticketids" => $_REQUEST["ticketids"], "invoiceids" => $_REQUEST["invoiceids"], "notes" => $_REQUEST["notes"], "adminid" => $_REQUEST["adminid"], "status" => $projectStatus, "created" => $created, "duedate" => $duedate, "completed" => $completed, "lastmodified" => "now()"]);
            $apiresults = ["result" => "success", "message" => "Project has been created", "projectid" => $projectid];
        }
    }
}

?>