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
if (!function_exists("RegSaveNameservers")) {
    require ROOTDIR . "/includes/registrarfunctions.php";
}
if ($domainid) {
    $where = ["id" => $domainid];
} else {
    $where = ["domain" => $domain];
}
$result = select_query("tbldomains", "id,domain,registrar,registrationperiod", $where);
$data = mysql_fetch_array($result);
$domainid = $data[0];
if (!$domainid) {
    $apiresults = ["result" => "error", "message" => "Domain ID Not Found"];
    return false;
}
if (!($ns1 && $ns2)) {
    $apiresults = ["result" => "error", "message" => "ns1 and ns2 required"];
    return false;
}
$domain = $data["domain"];
$registrar = $data["registrar"];
$regperiod = $data["registrationperiod"];
$domainparts = explode(".", $domain, 2);
$params = [];
$params["domainid"] = $domainid;
list($params["sld"], $params["tld"]) = $domainparts;
$params["regperiod"] = $regperiod;
$params["registrar"] = $registrar;
$params["ns1"] = $ns1;
$params["ns2"] = $ns2;
$params["ns3"] = $ns3;
$params["ns4"] = $ns4;
$params["ns5"] = $ns5;
$values = RegSaveNameservers($params);
if ($values["error"]) {
    $apiresults = ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
    return false;
}
if (!$values) {
    $values = [];
}
$apiresults = array_merge(["result" => "success"], $values);

?>