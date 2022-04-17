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
$setting = App::getFromRequest("setting");
$isValueInRequest = App::isInRequest("value");
$value = App::getFromRequest("value");
if (!$setting) {
    $apiresults = ["result" => "error", "message" => "Parameter setting is required"];
} else {
    $currentValue = WHMCS\Config\Setting::find($setting);
    if (is_null($currentValue)) {
        $apiresults = ["result" => "error", "message" => "Invalid name for parameter setting"];
    } else {
        if (!$isValueInRequest) {
            $apiresults = ["result" => "error", "message" => "Parameter value is required"];
        } else {
            $apiresults = [];
            $apiresults["result"] = "success";
            if ($value != $currentValue->value) {
                if (!function_exists("logAdminActivity")) {
                    require_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "adminfunctions.php";
                }
                WHMCS\Config\Setting::setValue($setting, $value);
                logAdminActivity("Settings Changed. " . $setting . " Updated: '" . $value . "'");
                $apiresults["value_changed"] = true;
            }
        }
    }
}

?>