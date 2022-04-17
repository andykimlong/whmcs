<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    if (!function_exists("checkPermission")) {
        require_once ROOTDIR . "/includes/adminfunctions.php";
    }
    $clientId = App::getFromRequest("clientid");
    $deleteUsers = stringLiteralToBool(App::getFromRequest("deleteusers"));
    $deleteTransactions = stringLiteralToBool(App::getFromRequest("deletetransactions"));
    $deleteUsersCheck = $deleteUsers && checkPermission("Delete Users", true);
    $deleteTransactionsCheck = $deleteTransactions && checkPermission("Delete Transaction", true);
    try {
        $client = WHMCS\User\Client::findOrFail($clientId);
        if ($deleteUsersCheck) {
            $client->deleteUsersWithNoOtherClientAccounts();
        }
        if ($deleteTransactionsCheck) {
            $client->deleteTransactions();
        } else {
            $client->disassociateTransactions();
        }
        $client->deleteEntireClient();
        $apiresults = ["result" => "success", "clientid" => $clientId];
    } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
        return NULL;
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Client Delete Failed: " . $e->getMessage()];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>