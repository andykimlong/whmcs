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
    $permissions = App::getFromRequest("permissions");
    try {
        $user = WHMCS\User\User::findOrFail($userId);
        try {
            $client = WHMCS\User\Client::findOrFail($clientId);
            if (!$permissions) {
                $apiresults = ["result" => "error", "message" => "Missing permissions definition"];
                return NULL;
            }
            if ($client->isOwnedBy($user)) {
                $apiresults = ["result" => "error", "message" => "Permissions cannot be set on a client owner"];
                return NULL;
            }
            $clientRelation = $user->clients()->find($client->id);
            if (!$clientRelation) {
                $apiresults = ["result" => "error", "message" => "User is not associated with client"];
                return NULL;
            }
            $permissions = new WHMCS\User\Permissions($permissions);
            try {
                $clientRelation->pivot->setPermissions($permissions)->save();
                $apiresults = ["result" => "success", "user_id" => $user->id, "client_id" => $client->id, "permissions" => $clientRelation->pivot->getPermissions()->get()];
            } catch (Exception $e) {
                $apiresults = ["result" => "error", "message" => $e->getMessage()];
                return NULL;
            }
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