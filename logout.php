<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("CLIENTAREA", true);
require "init.php";
$userId = NULL;
if (Auth::user()) {
    $client = Auth::client();
    if ($client) {
        $userId = $client->id;
    }
    Auth::logout();
}
if (App::getFromRequest("returntoadmin") && WHMCS\User\Admin::getAuthenticatedUser()) {
    if ($userId) {
        App::redirect(App::get_admin_folder_name() . "/clientssummary.php", ["userid" => $userId]);
    }
    App::redirect(App::get_admin_folder_name());
}
App::redirect("index.php");

?>