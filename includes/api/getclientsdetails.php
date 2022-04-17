<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    if (!function_exists("getClientsDetails")) {
        require ROOTDIR . "/includes/clientfunctions.php";
    }
    if (!$clientid && !$email) {
        $apiresults = ["result" => "error", "message" => "Either clientid Or email Is Required"];
        return NULL;
    }
    if ($clientid) {
        try {
            $client = WHMCS\User\Client::with("currencyrel")->findOrFail($clientid);
        } catch (Exception $e) {
            $apiresults = ["result" => "error", "message" => "Client Not Found"];
            return NULL;
        }
    }
    $client = WHMCS\User\Client::with("currencyrel")->where("email", $email)->firstOrFail();
    $clientid = $client->id;
    $clientsdetails = getClientsDetails($client);
    unset($clientsdetails["model"]);
    $clientsdetails["currency_code"] = $client->currencyrel->code;
    $users = [];
    foreach ($client->users()->get() as $user) {
        $users["user"][] = ["id" => $user->id, "name" => $user->fullName, "email" => $user->email, "is_owner" => $user->id == $client->owner()->id];
    }
    $clientsdetails["users"] = $users;
    $apiresults = array_merge(["result" => "success"], $clientsdetails);
    if ($clientsdetails["cctype"]) {
        $apiresults["warning"] = "Credit Card related parameters are now deprecated and have been removed. Use GetPayMethods instead.";
    }
    unset($clientsdetails["cctype"]);
    unset($clientsdetails["cclastfour"]);
    unset($clientsdetails["gatewayid"]);
    $userRequestedResponseType = is_object($request) ? $request->getResponseFormat() : NULL;
    if (is_null($userRequestedResponseType) || WHMCS\Api\ApplicationSupport\Http\ResponseFactory::isTypeHighlyStructured($userRequestedResponseType)) {
        $apiresults["client"] = $clientsdetails;
        if ($stats || $userRequestedResponseType == WHMCS\Api\ApplicationSupport\Http\ResponseFactory::RESPONSE_FORMAT_XML) {
            $apiresults["stats"] = getClientsStats($clientid);
        }
    }
}
exit("This file cannot be accessed directly");

?>