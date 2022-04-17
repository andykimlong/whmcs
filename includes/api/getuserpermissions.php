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
            $clientRelation = $user->clients()->find($client->id);
            if (!$clientRelation) {
                $apiresults = ["result" => "error", "message" => "User is not associated with client"];
                return NULL;
            }
            $permissions = $clientRelation->pivot->getPermissions();
            $isOwner = $client->isOwnedBy($user);
            if ($isOwner) {
                $permissions = WHMCS\User\Permissions::all();
            }
            $apiresults = ["result" => "success", "user_id" => $user->id, "client_id" => $client->id, "is_owner" => $isOwner, "permissions" => $permissions->get()];
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