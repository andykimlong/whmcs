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
$admin = WHMCS\User\Admin::find((int) WHMCS\Session::get("adminid"));
if (is_null($admin)) {
    $apiresults = ["result" => "error", "message" => "You must be authenticated as an admin user to perform this action"];
} else {
    $admin->notes = $notes;
    $admin->save();
    $apiresults = ["result" => "success"];
}

?>