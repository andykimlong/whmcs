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
if (!$limitstart) {
    $limitstart = 0;
}
if (!$limitnum) {
    $limitnum = 25;
}
$where = [];
if ($userid) {
    $where["userid"] = $userid;
}
if ($firstname) {
    $where["firstname"] = $firstname;
}
if ($lastname) {
    $where["lastname"] = $lastname;
}
if ($companyname) {
    $where["companyname"] = $companyname;
}
if ($email) {
    $where["email"] = $email;
}
if ($address1) {
    $where["address1"] = $address1;
}
if ($address2) {
    $where["address2"] = $address2;
}
if ($city) {
    $where["city"] = $city;
}
if ($state) {
    $where["state"] = $state;
}
if ($postcode) {
    $where["postcode"] = $postcode;
}
if ($country) {
    $where["country"] = $country;
}
if ($phonenumber) {
    $where["phonenumber"] = $phonenumber;
}
$result = select_query("tblcontacts", "COUNT(*)", $where);
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$result = select_query("tblcontacts", "", $where, "id", "ASC", $limitstart . "," . $limitnum);
$apiresults = ["result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => mysql_num_rows($result)];
while ($data = mysql_fetch_assoc($result)) {
    $apiresults["contacts"]["contact"][] = $data;
}
$responsetype = "xml";

?>