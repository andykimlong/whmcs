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
if (!function_exists("SumUpPackageUpgradeOrder")) {
    require ROOTDIR . "/includes/upgradefunctions.php";
}
if (!function_exists("addTransaction")) {
    require ROOTDIR . "/includes/invoicefunctions.php";
}
if (!function_exists("getCartConfigOptions")) {
    require ROOTDIR . "/includes/configoptionsfunctions.php";
}
$result = select_query("tblhosting", "id,userid", ["id" => $serviceid]);
$data = mysql_fetch_array($result);
$serviceid = $data["id"];
$clientid = $data["userid"];
if (!$serviceid) {
    $apiresults = ["result" => "error", "message" => "Service ID Not Found"];
} else {
    $_SESSION["uid"] = $clientid;
    global $currency;
    $currency = getCurrency($clientid);
    $checkout = $calconly ? false : true;
    $upgradeAlreadyInProgress = upgradeAlreadyInProgress($serviceid);
    if ($checkout) {
        if ($upgradeAlreadyInProgress) {
            $apiresults = ["result" => "error", "message" => "Unable to accept upgrade order. Previous upgrade invoice for service is still unpaid."];
            return NULL;
        }
        $gatewaysarray = WHMCS\Module\GatewaySetting::getActiveGatewayModules();
        if (!in_array($paymentmethod, $gatewaysarray)) {
            $apiresults = ["result" => "error", "message" => "Invalid Payment Method. Valid options include " . implode(",", $gatewaysarray)];
            return NULL;
        }
    }
    $apiresults["result"] = "success";
    if ($type == "product") {
        $upgrades = SumUpPackageUpgradeOrder($serviceid, $newproductid, $newproductbillingcycle, $promocode, $paymentmethod, $checkout);
        $apiresults = array_merge($apiresults, $upgrades[0]);
    } else {
        if ($type == "configoptions") {
            $subtotal = 0;
            $result = select_query("tblhosting", "packageid,billingcycle", ["id" => $serviceid]);
            $data = mysql_fetch_array($result);
            list($pid, $billingcycle) = $data;
            $configoption = getCartConfigOptions($pid, "", $billingcycle, $serviceid);
            $configoptions = $_REQUEST["configoptions"];
            if (!is_array($configoptions)) {
                $configoptions = [];
            }
            foreach ($configoption as $option) {
                $id = $option["id"];
                $optiontype = $option["optiontype"];
                $selectedvalue = $option["selectedvalue"];
                $selectedqty = $option["selectedqty"];
                if (!isset($configoptions[$id])) {
                    if ($optiontype == "3" || $optiontype == "4") {
                        $selectedvalue = $selectedqty;
                    }
                    $configoptions[$id] = $selectedvalue;
                }
            }
            $upgrades = SumUpConfigOptionsOrder($serviceid, $configoptions, $promocode, $paymentmethod, $checkout);
            foreach ($upgrades as $i => $vals) {
                foreach ($vals as $k => $v) {
                    $apiresults[$k . ($i + 1)] = $v;
                }
            }
            $subtotal = $GLOBALS["subtotal"];
            $discount = $GLOBALS["discount"];
            $apiresults["subtotal"] = formatCurrency($subtotal);
            $apiresults["discount"] = formatCurrency($discount);
            $apiresults["total"] = formatCurrency($subtotal - $discount);
        } else {
            $apiresults = ["result" => "error", "message" => "Invalid Upgrade Type"];
            return NULL;
        }
    }
    if (!$checkout) {
        $apiresults["upgradeinprogress"] = (int) $upgradeAlreadyInProgress;
    } else {
        $upgradedata = createUpgradeOrder($serviceid, $ordernotes, $promocode, $paymentmethod);
        $apiresults = array_merge($apiresults, $upgradedata);
    }
}

?>