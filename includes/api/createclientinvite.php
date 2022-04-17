<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

$email = App::getFromRequest("email");
$clientId = App::getFromRequest("client_id");
$permissions = App::getFromRequest("permissions") ?: [];
try {
    $client = WHMCS\User\Client::findOrFail($clientId);
    if (!$email) {
        $apiresults = ["result" => "error", "message" => "Email is required"];
        return NULL;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $apiresults = ["result" => "error", "message" => "The email address entered is not valid"];
        return NULL;
    }
    if (!$permissions) {
        $apiresults = ["result" => "error", "message" => "User permissions are required"];
        return NULL;
    }
    $permissions = new WHMCS\User\Permissions($permissions);
    if (0 < $client->users()->where("email", $email)->count()) {
        $apiresults = ["result" => "error", "message" => "User already associated with client"];
        return NULL;
    }
    WHMCS\User\User\UserInvite::new($email, $permissions, $client->id);
    $apiresults = ["result" => "success"];
} catch (Exception $e) {
    $apiresults = ["result" => "error", "message" => "Invalid client id"];
}

?>