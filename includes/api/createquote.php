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
if (!function_exists("addClient")) {
    require ROOTDIR . "/includes/clientfunctions.php";
}
if (!function_exists("updateInvoiceTotal")) {
    require ROOTDIR . "/includes/invoicefunctions.php";
}
if (!function_exists("saveQuote")) {
    require ROOTDIR . "/includes/quotefunctions.php";
}
if (!$subject) {
    $apiresults = ["result" => "error", "message" => "Subject is required"];
} else {
    $stagearray = ["Draft", "Delivered", "On Hold", "Accepted", "Lost", "Dead"];
    if (!in_array($stage, $stagearray)) {
        $apiresults = ["result" => "error", "message" => "Invalid Stage"];
    } else {
        if (!$validuntil) {
            $apiresults = ["result" => "error", "message" => "Valid Until is required"];
        } else {
            if (!$datecreated) {
                $datecreated = date("Y-m-d");
            }
            if ($lineitems) {
                $lineitems = base64_decode($lineitems);
                $lineitemsarray = safe_unserialize($lineitems);
            }
            if (!$userid) {
                $clienttype = "new";
            }
            $newquoteid = saveQuote("", $subject, $stage, $datecreated, $validuntil, $clienttype, $userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $currency, $lineitemsarray, $proposal, $customernotes, $adminnotes, false, App::getFromRequest("tax_id"));
            $apiresults = ["result" => "success", "quoteid" => $newquoteid];
        }
    }
}

?>