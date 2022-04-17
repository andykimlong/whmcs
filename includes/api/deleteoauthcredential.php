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
$credentialId = (int) $whmcs->getFromRequest("credentialId");
$client = WHMCS\ApplicationLink\Client::find($credentialId);
if (is_null($client)) {
    $apiresults = ["result" => "error", "message" => "Invalid Credential ID provided."];
} else {
    $client->delete();
    $apiresults = ["result" => "success", "credentialId" => $credentialId];
}

?>