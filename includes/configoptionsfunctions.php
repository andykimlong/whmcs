<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

function getCartConfigOptions($pid, $values, $cycle, $accountid = "", $orderform = "", $showHiddenOverride = false)
{
    global $CONFIG;
    global $_LANG;
    global $currency;
    if (!(is_array($currency) || $currency instanceof WHMCS\Billing\Currency) && !(isset($currency["id"]) && is_numeric($currency["id"]))) {
        if (!function_exists("getCurrency")) {
            require_once ROOTDIR . "/includes/functions.php";
        }
        $currency = getCurrency();
    }
    $configoptions = [];
    $cycle = strtolower(str_replace(["-", " "], "", $cycle));
    if ($cycle == "onetime") {
        $cycle = "monthly";
    }
    $showhidden = $showHiddenOverride || WHMCS\Session::get("adminid") && (defined("ADMINAREA") || defined("APICALL")) ? true : false;
    if (!function_exists("getBillingCycleMonths")) {
        require ROOTDIR . "/includes/invoicefunctions.php";
    }
    $cyclemonths = getBillingCycleMonths($cycle);
    if ($accountid) {
        $values = $options = [];
        $accountid = (int) $accountid;
        $query = "SELECT tblproductconfigoptionssub.id, tblproductconfigoptionssub.configid\nFROM tblproductconfigoptionssub\nINNER JOIN tblproductconfigoptions ON tblproductconfigoptionssub.configid = tblproductconfigoptions.id\nINNER JOIN tblproductconfiglinks ON tblproductconfigoptions.gid = tblproductconfiglinks.gid\nINNER JOIN tblhosting on tblproductconfiglinks.pid = tblhosting.packageid\nWHERE tblhosting.id = " . $accountid . "\nAND tblproductconfigoptions.optiontype IN (3, 4)\nGROUP BY tblproductconfigoptionssub.configid\nORDER BY tblproductconfigoptionssub.sortorder ASC, id ASC;";
        $configOptionsResult = full_query($query);
        while ($configOptionsData = mysql_fetch_assoc($configOptionsResult)) {
            $options[$configOptionsData["id"]] = $configOptionsData["configid"];
        }
        if (count($options)) {
            foreach ($options as $subID => $configOptionID) {
                $isOptionSaved = (int) get_query_val("tblhostingconfigoptions", "configid", ["configid" => $configOptionID, "relid" => $accountid]);
                if (!$isOptionSaved) {
                    insert_query("tblhostingconfigoptions", ["relid" => $accountid, "configid" => $configOptionID, "optionid" => $subID, "qty" => 0]);
                }
            }
        }
        $result = select_query("tblhostingconfigoptions", "", ["relid" => $accountid]);
        while ($data = mysql_fetch_array($result)) {
            $configid = $data["configid"];
            $result2 = select_query("tblproductconfigoptions", "", ["id" => $configid]);
            $data2 = mysql_fetch_array($result2);
            $optiontype = $data2["optiontype"];
            if ($optiontype == 3 || $optiontype == 4) {
                $configoptionvalue = $data["qty"];
            } else {
                $configoptionvalue = $data["optionid"];
            }
            $values[$configid] = $configoptionvalue;
        }
    }
    $where = ["pid" => $pid];
    if (!$showhidden) {
        $where["hidden"] = 0;
    }
    $result2 = select_query("tblproductconfigoptions", "tblproductconfigoptions.*", $where, "tblproductconfigoptions`.`order` ASC,`tblproductconfigoptions`.`id", "ASC", "", "tblproductconfiglinks ON tblproductconfiglinks.gid=tblproductconfigoptions.gid");
    while ($data2 = mysql_fetch_array($result2)) {
        $optionid = $data2["id"];
        $optionname = $data2["optionname"];
        $optiontype = $data2["optiontype"];
        $optionhidden = $data2["hidden"];
        $qtyminimum = $data2["qtyminimum"];
        $qtymaximum = $data2["qtymaximum"];
        if (strpos($optionname, "|")) {
            $optionname = explode("|", $optionname);
            $optionname = trim($optionname[1]);
        }
        $options = [];
        $selname = $selectedoption = "";
        $selsetup = $selrecurring = 0;
        $selectedqty = 0;
        $foundPreselectedValue = false;
        $selvalue = isset($values[$optionid]) ? $values[$optionid] : "";
        if ($optiontype == "3") {
            $result3 = select_query("tblproductconfigoptionssub", "", ["configid" => $optionid]);
            $data3 = mysql_fetch_array($result3);
            $opid = $data3["id"];
            $ophidden = $data3["hidden"];
            $opname = $data3["optionname"];
            if (strpos($opname, "|")) {
                $opname = explode("|", $opname);
                $opname = trim($opname[1]);
            }
            $opnameonly = $opname;
            $result4 = select_query("tblpricing", "", ["type" => "configoptions", "currency" => $currency["id"], "relid" => $opid]);
            $data = mysql_fetch_array($result4);
            $setup = isset($data[$cycle]) ? $data[substr($cycle, 0, 1) . "setupfee"] : 0;
            $price = $fullprice = isset($data[$cycle]) ? $data[$cycle] : 0;
            if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                $price = $price / $cyclemonths;
            }
            if (0 < $price) {
                $opname .= " " . formatCurrency($price);
            }
            $setupvalue = 0 < $setup ? " + " . formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
            $options[] = ["id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price];
            if (!$selvalue) {
                $selvalue = 0;
            }
            $selectedqty = $selvalue;
            $selvalue = $opid;
            $selname = $_LANG["no"];
            if ($selectedqty) {
                $selname = $_LANG["yes"];
                $selectedoption = $opname;
                $selsetup = $setup;
                $selrecurring = $fullprice;
            }
        } else {
            if ($optiontype == "4") {
                $result3 = select_query("tblproductconfigoptionssub", "", ["configid" => $optionid]);
                $data3 = mysql_fetch_array($result3);
                $opid = $data3["id"];
                $ophidden = $data3["hidden"];
                $opname = $data3["optionname"];
                if (strpos($opname, "|")) {
                    $opname = explode("|", $opname);
                    $opname = trim($opname[1]);
                }
                $opnameonly = $opname;
                $result4 = select_query("tblpricing", "", ["type" => "configoptions", "currency" => $currency["id"], "relid" => $opid]);
                $data = mysql_fetch_array($result4);
                $setup = $data[substr($cycle, 0, 1) . "setupfee"];
                $price = $fullprice = $data[$cycle];
                if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                    $price = $price / $cyclemonths;
                }
                if (0 < $price) {
                    $opname .= " " . formatCurrency($price);
                }
                $setupvalue = 0 < $setup ? " + " . formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
                $options[] = ["id" => $opid, "hidden" => $ophidden, "name" => $opname . $setupvalue, "nameonly" => $opnameonly, "recurring" => $price];
                if (!is_numeric($selvalue) || $selvalue < 0) {
                    $selvalue = $qtyminimum;
                }
                if (0 < $qtyminimum && $selvalue < $qtyminimum) {
                    $selvalue = $qtyminimum;
                }
                if (0 < $qtymaximum && $qtymaximum < $selvalue) {
                    $selvalue = $qtymaximum;
                }
                $selectedqty = $selvalue;
                $selvalue = $opid;
                $selname = $selectedqty;
                $selectedoption = $opname;
                $selsetup = $setup * $selectedqty;
                $selrecurring = $fullprice * $selectedqty;
            } else {
                $result3 = select_query("tblproductconfigoptionssub", "tblpricing.*,tblproductconfigoptionssub.*", ["tblproductconfigoptionssub.configid" => $optionid, "tblpricing.type" => "configoptions", "tblpricing.currency" => $currency["id"]], "tblproductconfigoptionssub`.`sortorder` ASC,`tblproductconfigoptionssub`.`id", "ASC", "", "tblpricing ON tblpricing.relid=tblproductconfigoptionssub.id");
                while ($data3 = mysql_fetch_array($result3)) {
                    $opid = $data3["id"];
                    $ophidden = $data3["hidden"];
                    $setup = $data3[substr($cycle, 0, 1) . "setupfee"];
                    $price = $fullprice = $data3[$cycle];
                    if ($orderform && $CONFIG["ProductMonthlyPricingBreakdown"]) {
                        $price = $price / $cyclemonths;
                    }
                    $setupvalue = 0 < $setup ? " + " . formatCurrency($setup) . " " . $_LANG["ordersetupfee"] : "";
                    $rawName = $required = $opname = $data3["optionname"];
                    if (strpos($opname, "|")) {
                        $opnameArr = explode("|", $opname);
                        $opname = trim($opnameArr[1]);
                        $required = trim($opnameArr[0]);
                        if (defined("APICALL")) {
                            $setupvalue = "";
                        }
                    }
                    $opnameonly = $opname;
                    if (0 < $price && !defined("APICALL")) {
                        $opname .= " " . formatCurrency($price);
                    }
                    if ($showhidden || !$ophidden || $opid == $selvalue) {
                        $options[] = ["id" => $opid, "name" => $opname . $setupvalue, "rawName" => $rawName, "required" => $required, "nameonly" => $opnameonly, "nameandprice" => $opname, "setup" => $setup, "fullprice" => $fullprice, "recurring" => $price, "hidden" => $ophidden];
                    }
                    if ($opid == $selvalue || !$selvalue && !$ophidden) {
                        $selname = $opnameonly;
                        $selectedoption = $opname;
                        $selsetup = $setup;
                        $selrecurring = $fullprice;
                        $selvalue = $opid;
                        $foundPreselectedValue = true;
                    }
                }
                if (!$foundPreselectedValue && 0 < count($options)) {
                    $selname = $options[0]["nameonly"];
                    $selectedoption = $options[0]["nameandprice"];
                    $selsetup = $options[0]["setup"];
                    $selrecurring = $options[0]["fullprice"];
                    $selvalue = $options[0]["id"];
                }
            }
        }
        $configoptions[] = ["id" => $optionid, "hidden" => $optionhidden, "optionname" => $optionname, "optiontype" => $optiontype, "selectedvalue" => $selvalue, "selectedqty" => $selectedqty, "selectedname" => $selname, "selectedoption" => $selectedoption, "selectedsetup" => $selsetup, "selectedrecurring" => $selrecurring, "qtyminimum" => $qtyminimum, "qtymaximum" => $qtymaximum, "options" => $options];
    }
    return $configoptions;
}
function validateAndSanitizeQuantityConfigOptions($configoption)
{
    $whmcs = WHMCS\Application::getInstance();
    $validConfigOptions = $errorConfigIDs = [];
    $errorMessage = "";
    foreach ($configoption as $configid => $optionvalue) {
        $data = get_query_vals("tblproductconfigoptions", "", ["id" => $configid]);
        $optionname = $data["optionname"];
        $optiontype = $data["optiontype"];
        $qtyminimum = $data["qtyminimum"];
        $qtymaximum = $data["qtymaximum"];
        if (strpos($optionname, "|")) {
            $optionname = explode("|", $optionname);
            $optionname = trim($optionname[1]);
        }
        if ($optiontype == "3") {
            $optionvalue = $optionvalue ? "1" : "0";
        } else {
            if ($optiontype == "4") {
                $optionvalue = (int) $optionvalue;
                if ($qtyminimum < 0) {
                    $qtyminimum = 0;
                }
                if ($optionvalue < 0 || $optionvalue < $qtyminimum && 0 < $qtyminimum || 0 < $qtymaximum && $qtymaximum < $optionvalue) {
                    if ($qtymaximum <= 0) {
                        $qtymaximum = $whmcs->get_lang("clientareaunlimited");
                    }
                    $errorMessage .= "<li>" . sprintf($whmcs->get_lang("configoptionqtyminmax"), $optionname, $qtyminimum, $qtymaximum);
                    $errorConfigIDs[] = $configid;
                    $optionvalue = 0 < $qtyminimum ? $qtyminimum : 0;
                }
            } else {
                $optionvalue = get_query_val("tblproductconfigoptionssub", "id", ["configid" => $configid, "id" => $optionvalue]);
                if (!$optionvalue) {
                    $errorMessage .= "<li>The option selected for " . $optionname . " is not valid";
                    $errorConfigIDs[] = $configid;
                }
            }
        }
        $validConfigOptions[$configid] = $optionvalue;
    }
    return ["validOptions" => $validConfigOptions, "errorConfigIDs" => $errorConfigIDs, "errorMessage" => $errorMessage];
}

?>