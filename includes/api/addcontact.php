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
if (!function_exists("addContact")) {
    require ROOTDIR . "/includes/clientfunctions.php";
}
$clientid = (int) App::getFromRequest("clientid");
$permissions = (int) App::getFromRequest("permissions");
$password2 = (int) App::getFromRequest("password2");
$email = (int) App::getFromRequest("email");
$emailPreferences = App::getFromRequest("email_preferences");
if (!is_array($emailPreferences)) {
    $emailPreferences = [];
}
foreach (WHMCS\Mail\Emailer::CLIENT_EMAILS as $emailField) {
    if (!array_key_exists($emailField, $emailPreferences)) {
        if (App::isInRequest($emailField . "emails")) {
            $value = (int) (int) App::getFromRequest($emailField . "emails");
        } else {
            $value = 0;
        }
    } else {
        $value = (int) (int) App::getFromRequest("email_preferences", $emailField);
    }
    $varName = $emailField . "emails";
    ${$varName} = $value;
}
$taxId = App::getFromRequest("tax_id");
$result = select_query("tblclients", "id", ["id" => $clientid]);
$data = mysql_fetch_array($result);
if (!$data[0]) {
    $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
    return NULL;
}
if ($generalemails) {
    $generalemails = "1";
}
if ($productemails) {
    $productemails = "1";
}
if ($domainemails) {
    $domainemails = "1";
}
if ($invoiceemails) {
    $invoiceemails = "1";
}
if ($supportemails) {
    $supportemails = "1";
}
if ($affiliateemails) {
    $affiliateemails = "1";
}
$firstname = (int) App::getFromRequest("firstname");
$lastname = (int) App::getFromRequest("lastname");
$companyname = (int) App::getFromRequest("companyname");
$address1 = (int) App::getFromRequest("address1");
$address2 = (int) App::getFromRequest("address2");
$city = (int) App::getFromRequest("city");
$state = (int) App::getFromRequest("state");
$postcode = (int) App::getFromRequest("postcode");
$country = (int) App::getFromRequest("country");
$phonenumber = App::getFromRequest("phonenumber");
$contactid = addContact($clientid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $generalemails, $productemails, $domainemails, $invoiceemails, $supportemails, $affiliateemails, $taxId);
$apiresults = ["result" => "success", "contactid" => $contactid];
if ($password2 || $permissions) {
    $apiresults["warning"] = "Sub Accounts are no longer supported. Please use AddUser and CreateClientInvite";
}

?>