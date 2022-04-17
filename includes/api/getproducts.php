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
global $currency;
$currency = getCurrency();
$pid = $whmcs->get_req_var("pid");
$gid = $whmcs->get_req_var("gid");
$module = $whmcs->get_req_var("module");
$where = [];
if ($pid) {
    if (is_numeric($pid)) {
        $where[] = "tblproducts.id=" . (int) $pid;
    } else {
        $pids = [];
        foreach (explode(",", $pid) as $p) {
            $p = (int) trim($p);
            if ($p) {
                $pids[] = $p;
            }
        }
        if ($pids) {
            $where[] = "tblproducts.id IN (" . implode(",", $pids) . ")";
        }
    }
}
if ($gid) {
    $where[] = "gid=" . (int) $gid;
}
if ($module && preg_match("/^[a-zA-Z0-9_\\.\\-]*\$/", $module)) {
    $where[] = "servertype='" . db_escape_string($module) . "'";
}
$result = select_query("tblproducts", "tblproducts.*", implode(" AND ", $where), "tblproductgroups`.`order` ASC, `tblproductgroups`.`id` ASC, `tblproducts`.`order` ASC, `tblproducts`.`id", "ASC", "", "tblproductgroups ON tblproducts.gid = tblproductgroups.id");
$apiresults = ["result" => "success", "totalresults" => mysql_num_rows($result)];
while ($data = mysql_fetch_array($result)) {
    $pid = $data["id"];
    $productarray = ["pid" => $data["id"], "gid" => $data["gid"], "type" => $data["type"], "name" => $data["name"], "description" => $data["description"], "module" => $data["servertype"], "paytype" => $data["paytype"]];
    if ($language = $whmcs->get_req_var("language")) {
        $productarray["translated_name"] = WHMCS\Product\Product::getProductName($data["id"], $data["name"], $language);
        $productarray["translated_description"] = WHMCS\Product\Product::getProductDescription($data["id"], $data["description"], $language);
    }
    if ($data["stockcontrol"]) {
        $productarray["stockcontrol"] = "true";
        $productarray["stocklevel"] = $data["qty"];
    }
    $result2 = select_query("tblpricing", "tblcurrencies.code,tblcurrencies.prefix,tblcurrencies.suffix,tblpricing.msetupfee,tblpricing.qsetupfee,tblpricing.ssetupfee,tblpricing.asetupfee,tblpricing.bsetupfee,tblpricing.tsetupfee,tblpricing.monthly,tblpricing.quarterly,tblpricing.semiannually,tblpricing.annually,tblpricing.biennially,tblpricing.triennially", ["type" => "product", "relid" => $pid], "code", "ASC", "", "tblcurrencies ON tblcurrencies.id=tblpricing.currency");
    while ($data = mysql_fetch_assoc($result2)) {
        $code = $data["code"];
        unset($data["code"]);
        $productarray["pricing"][$code] = $data;
    }
    $customfieldsdata = [];
    $customfields = getCustomFields("product", $pid, "", "", "on");
    foreach ($customfields as $field) {
        $customfieldsdata[] = ["id" => $field["id"], "name" => $field["name"], "description" => $field["description"], "required" => $field["required"]];
    }
    $productarray["customfields"]["customfield"] = $customfieldsdata;
    $configoptiondata = [];
    $configurableoptions = getCartConfigOptions($pid, [], "", "", "", true);
    foreach ($configurableoptions as $option) {
        $options = [];
        foreach ($option["options"] as $op) {
            $pricing = [];
            $result4 = select_query("tblpricing", "code,msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,tsetupfee,monthly,quarterly,semiannually,annually,biennially,triennially", ["type" => "configoptions", "relid" => $op["id"]], "", "", "", "tblcurrencies ON tblcurrencies.id=tblpricing.currency");
            while ($oppricing = mysql_fetch_assoc($result4)) {
                $currcode = $oppricing["code"];
                unset($oppricing["code"]);
                $pricing[$currcode] = $oppricing;
            }
            $options["option"][] = ["id" => $op["id"], "name" => $op["name"], "rawName" => $op["rawName"], "recurring" => $op["recurring"], "required" => $op["required"], "pricing" => $pricing];
        }
        $configoptiondata[] = ["id" => $option["id"], "name" => $option["optionname"], "type" => $option["optiontype"], "options" => $options];
    }
    $productarray["configoptions"]["configoption"] = $configoptiondata;
    $apiresults["products"]["product"][] = $productarray;
}
$responsetype = "xml";

?>