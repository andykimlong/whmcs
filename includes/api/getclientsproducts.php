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
if (!function_exists("getCustomFields")) {
    require ROOTDIR . "/includes/customfieldfunctions.php";
}
if (!function_exists("getCartConfigOptions")) {
    require ROOTDIR . "/includes/configoptionsfunctions.php";
}
$where = [];
if ($clientid) {
    $where["tblhosting.userid"] = $clientid;
}
if ($serviceid) {
    $where["tblhosting.id"] = $serviceid;
}
if ($pid) {
    $where["tblhosting.packageid"] = $pid;
}
if ($domain) {
    $where["tblhosting.domain"] = $domain;
}
if ($username2) {
    $where["tblhosting.username"] = $username2;
}
$result = select_query("tblhosting", "COUNT(*)", $where, "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblproductgroups ON tblproductgroups.id=tblproducts.gid");
$data = mysql_fetch_array($result);
$totalresults = $data[0];
$limitstart = (int) $limitstart;
$limitnum = (int) $limitnum;
if (!$limitnum) {
    $limitnum = 999999;
}
$result = select_query("tblhosting", "tblhosting.*,tblproductgroups.name as group_name,tblproductgroups.id AS group_id,tblproducts.name, tblorders.ordernum,(SELECT CONCAT(name,'|',ipaddress,'|',hostname) FROM tblservers WHERE tblservers.id=tblhosting.server) AS serverdetails,(SELECT tblpaymentgateways.value FROM tblpaymentgateways WHERE tblpaymentgateways.gateway=tblhosting.paymentmethod AND tblpaymentgateways.setting='name' LIMIT 1) AS paymentmethodname", $where, "tblhosting`.`id", "ASC", $limitstart . "," . $limitnum, "tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblproductgroups ON tblproductgroups.id=tblproducts.gid LEFT JOIN tblorders ON tblhosting.orderid = tblorders.id");
$apiresults = ["result" => "success", "clientid" => $clientid, "serviceid" => $serviceid, "pid" => $pid, "domain" => $domain, "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => mysql_num_rows($result)];
if (!$totalresults) {
    $apiresults["products"] = "";
    while ($data = mysql_fetch_array($result)) {
    }
    $responsetype = "xml";
}
$id = $data["id"];
$userid = $data["userid"];
$orderid = $data["orderid"];
$pid = $data["packageid"];
$name = $data["name"];
$suspensionReason = $data["suspendreason"];
if (empty($name)) {
    $name = WHMCS\Product\Product::find($pid, ["name"])->name;
}
$language = WHMCS\Config\Setting::getValue("Language");
if ($userid) {
    $language = WHMCS\User\Client::find($userid, ["language"])->language ?: $language;
}
$translatedName = WHMCS\Product\Product::getProductName($data["packageid"], $name);
$groupname = $data["group_name"];
$translatedGroupName = WHMCS\Product\Group::getGroupName($data["group_id"], $groupname);
$server = $data["server"];
$regdate = $data["regdate"];
$domain = $data["domain"];
$paymentmethod = $data["paymentmethod"];
$paymentmethodname = $data["paymentmethodname"];
$firstpaymentamount = $data["firstpaymentamount"];
$recurringamount = $data["amount"];
$billingcycle = $data["billingcycle"];
$nextduedate = $data["nextduedate"];
$domainstatus = $data["domainstatus"];
$username = $data["username"];
$password = decrypt($data["password"]);
$notes = $data["notes"];
$subscriptionid = $data["subscriptionid"];
$promoid = $data["promoid"];
$ipaddress = $data["ipaddress"];
$overideautosuspend = $data["overideautosuspend"];
$overidesuspenduntil = $data["overidesuspenduntil"];
$ns1 = $data["ns1"];
$ns2 = $data["ns2"];
$dedicatedip = $data["dedicatedip"];
$assignedips = $data["assignedips"];
$diskusage = $data["diskusage"];
$disklimit = $data["disklimit"];
$bwusage = $data["bwusage"];
$bwlimit = $data["bwlimit"];
$lastupdate = $data["lastupdate"];
$serverdetails = $data["serverdetails"];
$serverdetails = explode("|", $serverdetails);
$customfieldsdata = [];
$customfields = getCustomFields("product", $pid, $id, "on", "");
foreach ($customfields as $customfield) {
    $customfieldsdata[] = ["id" => $customfield["id"], "name" => $customfield["name"], "translated_name" => WHMCS\CustomField::getFieldName($customfield["id"], $customfield["name"], $language), "value" => $customfield["value"]];
}
$configoptionsdata = [];
$currency = getCurrency($userid);
$configoptions = getCartConfigOptions($pid, "", $billingcycle, $id, "", true);
foreach ($configoptions as $configoption) {
    switch ($configoption["optiontype"]) {
        case 1:
            $type = "dropdown";
            break;
        case 2:
            $type = "radio";
            break;
        case 3:
            $type = "yesno";
            break;
        case 4:
            $type = "quantity";
            break;
        default:
            if ($configoption["optiontype"] == "3" || $configoption["optiontype"] == "4") {
                $configoptionsdata[] = ["id" => $configoption["id"], "option" => $configoption["optionname"], "type" => $type, "value" => $configoption["selectedqty"]];
            } else {
                $configoptionsdata[] = ["id" => $configoption["id"], "option" => $configoption["optionname"], "type" => $type, "value" => $configoption["selectedoption"]];
            }
    }
}
$data["ordernum"] ? exit : "";

?>