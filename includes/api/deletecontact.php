<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $contactid = App::getFromRequest("contactid");
    try {
        $contact = WHMCS\User\Client\Contact::findOrFail($contactid);
        $client = $contact->client;
        $legacyClient = new WHMCS\Client($client);
        $legacyClient->deleteContact($contactid);
        $apiresults = ["result" => "success", "message" => $contactid];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Contact ID Not Found"];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>