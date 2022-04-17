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
$setting = App::get_req_var("setting");
if (!$setting) {
    $apiresults = ["result" => "error", "message" => "Parameter setting is required"];
} else {
    $currentValue = WHMCS\Config\Setting::find($setting);
    if (is_null($currentValue)) {
        $apiresults = ["result" => "error", "message" => "Invalid name for parameter setting"];
    } else {
        $apiresults = ["result" => "success", "setting" => $setting, "value" => $currentValue->value];
    }
}

?>