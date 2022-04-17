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
if (!function_exists("ServerChangePassword")) {
    require ROOTDIR . "/includes/modulefunctions.php";
}
$serviceId = (int) App::getFromRequest("serviceid");
if (!$serviceId && App::isInRequest("accountid")) {
    $serviceId = (int) App::getFromRequest("accountid");
}
if (!$serviceId) {
    $apiresults = ["result" => "error", "message" => "Service ID is required"];
} else {
    $data = WHMCS\Database\Capsule::table("tblhosting")->leftJoin("tblproducts", "tblhosting.packageid", "=", "tblproducts.id")->where("tblhosting.id", $serviceId)->first(["tblhosting.id as service_id", "tblproducts.servertype as module"]);
    if (!$data) {
        $apiresults = ["result" => "error", "message" => "Service ID not found"];
    } else {
        if (!$data->module) {
            $apiresults = ["result" => "error", "message" => "Service not assigned to a module"];
        } else {
            $serviceId = $data->service_id;
            $servicepassword = App::getFromRequest("servicepassword");
            if ($servicepassword) {
                update_query("tblhosting", ["password" => encrypt($servicepassword)], ["id" => $serviceId]);
            }
            $result = ServerChangePassword($serviceId);
            if ($result == "success") {
                $apiresults = ["result" => "success"];
            } else {
                $apiresults = ["result" => "error", "message" => $result];
            }
        }
    }
}

?>