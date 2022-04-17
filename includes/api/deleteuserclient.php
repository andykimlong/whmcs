<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $userId = (int) App::getFromRequest("user_id");
    $clientId = (int) App::getFromRequest("client_id");
    try {
        $user = WHMCS\User\User::findOrFail($userId);
        try {
            $client = WHMCS\User\Client::findOrFail($clientId);
            if (!$client->users()->find($user->id)) {
                $apiresults = ["result" => "error", "message" => "User is not associated with client"];
                return NULL;
            }
            if ($client->isOwnedBy($user)) {
                $apiresults = ["result" => "error", "message" => "You cannot remove the account owner"];
                return NULL;
            }
            $user->clients()->detach($client->id);
            $apiresults = ["result" => "success"];
        } catch (Exception $e) {
            $apiresults = ["result" => "error", "message" => "Invalid Client ID requested"];
            return NULL;
        }
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Invalid User ID requested"];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>