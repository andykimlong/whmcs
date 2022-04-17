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
if (!function_exists("RegRenewDomain")) {
    require ROOTDIR . "/includes/registrarfunctions.php";
}
if ($domainid) {
    $result = select_query("tbldomains", "id", ["id" => $domainid]);
} else {
    $result = select_query("tbldomains", "id", ["domain" => $domain]);
}
$data = mysql_fetch_array($result);
$domainid = $data[0];
if (!$domainid) {
    $apiresults = ["result" => "error", "message" => "Domain Not Found"];
    return false;
}
if ($regperiod) {
    update_query("tbldomains", ["registrationperiod" => $regperiod], ["id" => $domainid]);
}
$params = ["domainid" => $domainid];
$values = RegRenewDomain($params);
if ($values["error"]) {
    $apiresults = ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
    return false;
}
$apiresults = array_merge(["result" => "success"], $values);

?>