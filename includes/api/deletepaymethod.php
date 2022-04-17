<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $payMethodId = (int) App::getFromRequest("paymethodid");
    $clientId = (int) App::getFromRequest("clientid");
    $failOnRemoteFailure = (int) App::getFromRequest("failonremotefailure");
    if (!$clientId) {
        $apiresults = ["result" => "error", "message" => "Client ID is Required"];
        return NULL;
    }
    if (!$payMethodId) {
        $apiresults = ["result" => "error", "message" => "Pay Method ID is Required"];
        return NULL;
    }
    try {
        $payMethod = WHMCS\Payment\PayMethod\Model::findOrFail($payMethodId);
        if ($payMethod->userid != $clientId) {
            $apiresults = ["result" => "error", "message" => "Pay Method does not belong to passed Client ID"];
            return NULL;
        }
        $payment = $payMethod->payment;
        try {
            try {
                if ($payment instanceof WHMCS\Payment\Contracts\RemoteTokenDetailsInterface) {
                    $payment->deleteRemote();
                }
            } catch (Exception $e) {
                logActivity("Remote deletion failed for pay method " . $payMethod->id . ", User ID: " . $payMethod->client->id);
                if ($failOnRemoteFailure) {
                    throw $e;
                }
                $payMethod->delete();
                $apiresults = ["result" => "success", "paymethodid" => $payMethodId];
            }
        } catch (Exception $e) {
            $apiresults = ["result" => "error", "message" => "Error Deleting Remote Pay Method: " . $e->getMessage()];
            return NULL;
        }
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Invalid Pay Method ID"];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>