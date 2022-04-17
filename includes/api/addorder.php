<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    if (!function_exists("addClient")) {
        require ROOTDIR . "/includes/clientfunctions.php";
    }
    if (!function_exists("getCartConfigOptions")) {
        require ROOTDIR . "/includes/configoptionsfunctions.php";
    }
    if (!function_exists("getTLDPriceList")) {
        require ROOTDIR . "/includes/domainfunctions.php";
    }
    if (!function_exists("updateInvoiceTotal")) {
        require ROOTDIR . "/includes/invoicefunctions.php";
    }
    if (!function_exists("createInvoices")) {
        require ROOTDIR . "/includes/processinvoices.php";
    }
    if (!function_exists("calcCartTotals")) {
        require ROOTDIR . "/includes/orderfunctions.php";
    }
    if (!function_exists("ModuleBuildParams")) {
        require ROOTDIR . "/includes/modulefunctions.php";
    }
    if (!function_exists("cartPreventDuplicateProduct")) {
        require ROOTDIR . "/includes/cartfunctions.php";
    }
    if ($promocode && !$promooverride) {
        define("CLIENTAREA", true);
    }
    $whmcs = WHMCS\Application::getInstance();
    try {
        $client = WHMCS\User\Client::findOrFail($whmcs->get_req_var("clientid"));
        $blockedStatus = ["Closed"];
        if (in_array($client->status, $blockedStatus)) {
            $apiresults = ["result" => "error", "message" => "Unable to add order when client status is " . $client->status];
            return NULL;
        }
        $userid = (int) $client->id;
        $gatewayModules = WHMCS\Module\GatewaySetting::getActiveGatewayModules();
        if (!in_array($paymentmethod, $gatewayModules)) {
            $apiresults = ["result" => "error", "message" => "Invalid Payment Method. Valid options include " . implode(",", $gatewayModules)];
            return NULL;
        }
        if (!empty($clientip)) {
            if (filter_var($clientip, FILTER_VALIDATE_IP) === false) {
                $apiresults = ["result" => "error", "message" => "Invalid IP address provided for 'clientip'"];
                return NULL;
            }
            global $remote_ip;
            $remote_ip = $clientip;
            WHMCS\Order\Order::creating(function ($model) {
                $model->setAttribute("ipAddress", $clientip);
            });
        }
        unset($clientip);
        global $currency;
        $currency = getCurrency($userid);
        $_SESSION["cart"] = [];
        if (is_array($pid)) {
            foreach ($pid as $i => $prodid) {
                if ($prodid) {
                    $proddomain = $domain[$i];
                    $prodbillingcycle = $billingcycle[$i];
                    $configoptionsarray = [];
                    $customfieldsarray = [];
                    $domainfieldsarray = [];
                    $addonsarray = [];
                    if ($addons[$i]) {
                        foreach (explode(",", $addons[$i]) as $addonForPid) {
                            $addonsarray[] = ["addonid" => $addonForPid, "qty" => 1];
                        }
                    }
                    if ($configoptions[$i]) {
                        $configoptionsarray = safe_unserialize(base64_decode($configoptions[$i]));
                    }
                    if ($customfields[$i]) {
                        $customfieldsarray = safe_unserialize(base64_decode($customfields[$i]));
                    }
                    $productarray = ["pid" => $prodid, "domain" => $proddomain, "billingcycle" => $prodbillingcycle, "server" => $hostname[$i] || $ns1prefix[$i] || $ns2prefix[$i] || $rootpw[$i] ? ["hostname" => $hostname[$i], "ns1prefix" => $ns1prefix[$i], "ns2prefix" => $ns2prefix[$i], "rootpw" => $rootpw[$i]] : "", "configoptions" => $configoptionsarray, "customfields" => $customfieldsarray, "addons" => $addonsarray];
                    if (strlen($priceoverride[$i])) {
                        $productarray["priceoverride"] = $priceoverride[$i];
                    }
                    $_SESSION["cart"]["products"][] = $productarray;
                }
            }
        } else {
            if ($pid) {
                $configoptionsarray = [];
                $customfieldsarray = [];
                $domainfieldsarray = [];
                $addonsarray = [];
                if ($addons) {
                    foreach (explode(",", $addons) as $addonForPid) {
                        $addonsarray[] = ["addonid" => $addonForPid, "qty" => 1];
                    }
                }
                if ($configoptions) {
                    $configoptions = base64_decode($configoptions);
                    $configoptionsarray = safe_unserialize($configoptions);
                }
                if ($customfields) {
                    $customfields = base64_decode($customfields);
                    $customfieldsarray = safe_unserialize($customfields);
                }
                $productarray = ["pid" => $pid, "domain" => $domain, "billingcycle" => $billingcycle, "server" => $hostname || $ns1prefix || $ns2prefix || $rootpw ? ["hostname" => $hostname, "ns1prefix" => $ns1prefix, "ns2prefix" => $ns2prefix, "rootpw" => $rootpw] : "", "configoptions" => $configoptionsarray, "customfields" => $customfieldsarray, "addons" => $addonsarray];
                if (strlen($priceoverride)) {
                    $productarray["priceoverride"] = $priceoverride;
                }
                $_SESSION["cart"]["products"][] = $productarray;
            }
        }
        $domaintype = App::getFromRequest("domaintype");
        $domainfields = App::getFromRequest("domainfields");
        $domain = App::getFromRequest("domain");
        $regperiod = App::getFromRequest("regperiod");
        $idnLanguage = App::getFromRequest("idnlanguage");
        $dnsmanagement = App::getFromRequest("dnsmanagement");
        $emailforwarding = App::getFromRequest("emailforwarding");
        $idprotection = App::getFromRequest("idprotection");
        $eppcode = App::getFromRequest("eppcode");
        $domainpriceoverride = App::getFromRequest("domainpriceoverride");
        $domainrenewoverride = App::getFromRequest("domainrenewoverride");
        if (is_array($domaintype)) {
            foreach ($domaintype as $i => $type) {
                if ($type) {
                    if ($domainfields[$i]) {
                        $domainfields[$i] = base64_decode($domainfields[$i]);
                        $domainfieldsarray[$i] = safe_unserialize($domainfields[$i]);
                    }
                    if (empty($idnLanguage[$i])) {
                        $idnLanguage[$i] = "";
                    }
                    $domainArray = ["type" => $type, "domain" => $domain[$i], "regperiod" => $regperiod[$i], "idnLanguage" => $idnLanguage[$i], "dnsmanagement" => $dnsmanagement[$i], "emailforwarding" => $emailforwarding[$i], "idprotection" => $idprotection[$i], "eppcode" => $eppcode[$i], "fields" => $domainfieldsarray[$i]];
                    if (isset($domainpriceoverride[$i]) && 0 < strlen($domainpriceoverride[$i])) {
                        $domainArray["domainpriceoverride"] = $domainpriceoverride[$i];
                    }
                    if (isset($domainrenewoverride[$i]) && 0 < strlen($domainrenewoverride[$i])) {
                        $domainArray["domainrenewoverride"] = $domainrenewoverride[$i];
                    }
                    $_SESSION["cart"]["domains"][] = $domainArray;
                }
            }
        } else {
            if ($domaintype) {
                if ($domainfields) {
                    $domainfields = base64_decode($domainfields);
                    $domainfieldsarray = safe_unserialize($domainfields);
                }
                if (empty($idnLanguage)) {
                    $idnLanguage = "";
                }
                $domainArray = ["type" => $domaintype, "domain" => $domain, "regperiod" => $regperiod, "idnLanguage" => $idnLanguage, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "eppcode" => $eppcode, "fields" => $domainfieldsarray];
                if (isset($domainpriceoverride) && 0 < strlen($domainpriceoverride)) {
                    $domainArray["domainpriceoverride"] = $domainpriceoverride;
                }
                if (isset($domainrenewoverride) && 0 < strlen($domainrenewoverride)) {
                    $domainArray["domainrenewoverride"] = $domainrenewoverride;
                }
                $_SESSION["cart"]["domains"][] = $domainArray;
            }
        }
        if ($addonid) {
            $addonData = WHMCS\Database\Capsule::table("tbladdons")->find($addonid);
            if (!$addonData) {
                $apiresults = ["result" => "error", "message" => "Addon ID invalid"];
                return NULL;
            }
            $addonid = $addonData->id;
            $allowMultipleQuantities = (int) $addonData->allowqty;
            if ($allowMultipleQuantities === 1) {
                $allowMultipleQuantities = 0;
            }
            $serviceid = get_query_val("tblhosting", "id", ["userid" => $userid, "id" => $serviceid]);
            if (!$serviceid) {
                $apiresults = ["result" => "error", "message" => "Service ID not owned by Client ID provided"];
                return NULL;
            }
            $_SESSION["cart"]["addons"][] = ["id" => $addonid, "productid" => $serviceid, "qty" => 1, "allowsQuantity" => $allowsQuantity];
        }
        if ($addonids) {
            foreach ($addonids as $i => $addonid) {
                $addonData = WHMCS\Database\Capsule::table("tbladdons")->find($addonid);
                if (!$addonData) {
                    $apiresults = ["result" => "error", "message" => "Addon ID invalid"];
                    return NULL;
                }
                $addonid = $addonData->id;
                $allowsQuantity = (int) $addonData->allowqty;
                if ($allowsQuantity === 1) {
                    $allowsQuantity = 0;
                }
                $serviceid = get_query_val("tblhosting", "id", ["userid" => $userid, "id" => $serviceids[$i]]);
                if (!$serviceid) {
                    $apiresults = ["result" => "error", "message" => sprintf("Service ID %s not owned by Client ID provided", (int) $serviceids[$i])];
                    return NULL;
                }
                $_SESSION["cart"]["addons"][] = ["id" => $addonid, "productid" => $serviceid, "qty" => 1, "allowsQuantity" => $allowMultipleQuantities];
            }
        }
        $domainrenewals = $whmcs->get_req_var("domainrenewals");
        if ($domainrenewals) {
            foreach ($domainrenewals as $domain => $regperiod) {
                $domain = mysql_real_escape_string($domain);
                $sql = "SELECT `id`\n                FROM `tbldomains`\n                WHERE userid=" . $userid . " AND domain='" . $domain . "' AND status IN ('Active', 'Expired', 'Grace', 'Redemption')";
                $domainResult = full_query($sql);
                $domainData = mysql_fetch_array($domainResult);
                if (isset($domainData["id"])) {
                    $domainid = $domainData["id"];
                }
                if (!$domainid) {
                    $sql = "SELECT `status`\n                    FROM `tbldomains`\n                    WHERE userid=" . $userid . " AND domain='" . $domain . "'";
                    $domainResult = full_query($sql);
                    $domainData = mysql_fetch_array($domainResult);
                    $apiresults = ["result" => "error", "message" => ""];
                    if (isset($domainData["status"])) {
                        $apiresults["message"] = "Domain status is set to '" . $domainData["status"] . "' and cannot be renewed";
                    } else {
                        $apiresults["message"] = "Domain not owned by Client ID provided";
                    }
                    return NULL;
                }
                $_SESSION["cart"]["renewals"][$domainid] = $regperiod;
            }
        }
        $_SESSION["cart"]["products"] ? exit : [];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>