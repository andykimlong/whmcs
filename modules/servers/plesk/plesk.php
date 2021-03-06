<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require_once "lib/Plesk/Loader.php";
function plesk_MetaData()
{
    return ["DisplayName" => "Plesk", "APIVersion" => "1.1", "ListAccountsUniqueIdentifierDisplayName" => "Domain", "ListAccountsUniqueIdentifierField" => "domain", "ListAccountsProductField" => "configoption1", "PasswordGenerationSpecialCharacters" => "!@#\$%^&*?_~"];
}
function plesk_ConfigOptions($params)
{
    require_once "lib/Plesk/Translate.php";
    $translator = new Plesk_Translate();
    $resellerSimpleMode = $params["producttype"] == "reselleraccount";
    $configarray = ["servicePlanName" => ["FriendlyName" => $translator->translate("CONFIG_SERVICE_PLAN_NAME"), "Type" => "text", "Size" => "25", "Loader" => function ($params) {
        $return = [];
        Plesk_Loader::init($params);
        $packages = Plesk_Registry::getInstance()->manager->getServicePlans();
        $return[""] = "None";
        foreach ($packages as $package) {
            $return[$package] = $package;
        }
        return $return;
    }, "SimpleMode" => true], "resellerPlanName" => ["FriendlyName" => $translator->translate("CONFIG_RESELLER_PLAN_NAME"), "Type" => "text", "Size" => "25", "Loader" => function ($params) {
        $return = [];
        Plesk_Loader::init($params);
        $packages = Plesk_Registry::getInstance()->manager->getResellerPlans();
        $return[""] = "None";
        foreach ($packages as $package) {
            $return[$package->name] = $package->name;
        }
        return $return;
    }, "SimpleMode" => $resellerSimpleMode], "ipAdresses" => ["FriendlyName" => $translator->translate("CONFIG_WHICH_IP_ADDRESSES"), "Type" => "dropdown", "Options" => "IPv4 shared; IPv6 none,IPv4 dedicated; IPv6 none,IPv4 none; IPv6 shared,IPv4 none; IPv6 dedicated,IPv4 shared; IPv6 shared,IPv4 shared; IPv6 dedicated,IPv4 dedicated; IPv6 shared,IPv4 dedicated; IPv6 dedicated", "Default" => "IPv4 shared; IPv6 none", "Description" => "", "SimpleMode" => true], "powerUser" => ["FriendlyName" => $translator->translate("CONFIG_POWER_USER_MODE"), "Type" => "yesno", "Description" => $translator->translate("CONFIG_POWER_USER_MODE_DESCRIPTION")]];
    return $configarray;
}
function plesk_AdminLink($params)
{
    $address = $params["serverhostname"] ? $params["serverhostname"] : $params["serverip"];
    $port = $params["serveraccesshash"] ? $params["serveraccesshash"] : "8443";
    $secure = $params["serversecure"] ? "https" : "http";
    if (empty($address)) {
        return "";
    }
    $form = sprintf("<form action=\"%s://%s:%s/login_up.php3\" method=\"post\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"%s\" /><input type=\"hidden\" name=\"passwd\" value=\"%s\" /><input type=\"submit\" value=\"%s\"></form>", $secure, WHMCS\Input\Sanitize::encode($address), WHMCS\Input\Sanitize::encode($port), WHMCS\Input\Sanitize::encode($params["serverusername"]), WHMCS\Input\Sanitize::encode($params["serverpassword"]), "Login to panel");
    return $form;
}
function plesk_ClientArea($params)
{
    try {
        Plesk_Loader::init($params);
        return Plesk_Registry::getInstance()->manager->getClientAreaForm($params);
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_CreateAccount($params)
{
    try {
        Plesk_Loader::init($params);
        $translator = Plesk_Registry::getInstance()->translator;
        if ("" == $params["clientsdetails"]["firstname"] && "" == $params["clientsdetails"]["lastname"]) {
            return $translator->translate("ERROR_ACCOUNT_VALIDATION_EMPTY_FIRST_OR_LASTNAME");
        }
        if ("" == $params["username"]) {
            return $translator->translate("ERROR_ACCOUNT_VALIDATION_EMPTY_USERNAME");
        }
        Plesk_Registry::getInstance()->manager->createTableForAccountStorage();
        $account = WHMCS\Database\Capsule::table("mod_pleskaccounts")->where("userid", $params["clientsdetails"]["userid"])->where("usertype", $params["type"])->first();
        $panelExternalId = is_null($account) ? "" : $account->panelexternalid;
        $params["clientsdetails"]["panelExternalId"] = $panelExternalId;
        $accountId = NULL;
        try {
            $accountInfo = Plesk_Registry::getInstance()->manager->getAccountInfo($params, $panelExternalId);
            if (isset($accountInfo["id"])) {
                $accountId = $accountInfo["id"];
            }
        } catch (Exception $e) {
            if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                throw $e;
            }
            if (!is_null($accountId) && Plesk_Object_Customer::TYPE_RESELLER == $params["type"]) {
                return $translator->translate("ERROR_RESELLER_ACCOUNT_IS_ALREADY_EXISTS", ["EMAIL" => $params["clientsdetails"]["email"]]);
            }
            $params = array_merge($params, Plesk_Registry::getInstance()->manager->getIps($params));
            if (is_null($accountId)) {
                try {
                    $accountId = Plesk_Registry::getInstance()->manager->addAccount($params);
                } catch (Exception $e) {
                    if (Plesk_Api::ERROR_OPERATION_FAILED == $e->getCode()) {
                        return $translator->translate("ERROR_ACCOUNT_CREATE_COMMON_MESSAGE");
                    }
                    throw $e;
                }
            }
            Plesk_Registry::getInstance()->manager->addIpToIpPool($accountId, $params);
            if ("" == $panelExternalId && "" != ($possibleExternalId = Plesk_Registry::getInstance()->manager->getCustomerExternalId($params))) {
                WHMCS\Database\Capsule::table("mod_pleskaccounts")->insert(["userid" => $params["clientsdetails"]["userid"], "usertype" => $params["type"], "panelexternalid" => $possibleExternalId]);
            }
            if (!is_null($accountId) && Plesk_Object_Customer::TYPE_RESELLER == $params["type"]) {
                return "success";
            }
            $params["ownerId"] = $accountId;
            Plesk_Registry::getInstance()->manager->addWebspace($params);
            if (!empty($params["configoptions"])) {
                Plesk_Registry::getInstance()->manager->processAddons($params);
            }
            return "success";
        }
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_SuspendAccount($params)
{
    try {
        Plesk_Loader::init($params);
        $params["status"] = "root" != $params["serverusername"] && "admin" != $params["serverusername"] ? Plesk_Object_Customer::STATUS_SUSPENDED_BY_RESELLER : Plesk_Object_Customer::STATUS_SUSPENDED_BY_ADMIN;
        switch ($params["type"]) {
            case Plesk_Object_Customer::TYPE_CLIENT:
                Plesk_Registry::getInstance()->manager->setWebspaceStatus($params);
                break;
            case Plesk_Object_Customer::TYPE_RESELLER:
                Plesk_Registry::getInstance()->manager->setResellerStatus($params);
                break;
            default:
                return "success";
        }
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_UnsuspendAccount($params)
{
    try {
        Plesk_Loader::init($params);
        switch ($params["type"]) {
            case Plesk_Object_Customer::TYPE_CLIENT:
                $params["status"] = Plesk_Object_Webspace::STATUS_ACTIVE;
                Plesk_Registry::getInstance()->manager->setWebspaceStatus($params);
                break;
            case Plesk_Object_Customer::TYPE_RESELLER:
                $params["status"] = Plesk_Object_Customer::STATUS_ACTIVE;
                Plesk_Registry::getInstance()->manager->setResellerStatus($params);
                break;
            default:
                return "success";
        }
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_TerminateAccount($params)
{
    try {
        Plesk_Loader::init($params);
        switch ($params["type"]) {
            case Plesk_Object_Customer::TYPE_CLIENT:
                Plesk_Registry::getInstance()->manager->deleteWebspace($params);
                break;
            case Plesk_Object_Customer::TYPE_RESELLER:
                Plesk_Registry::getInstance()->manager->deleteReseller($params);
                break;
            default:
                return "success";
        }
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_ChangePassword($params)
{
    try {
        Plesk_Loader::init($params);
        Plesk_Registry::getInstance()->manager->setAccountPassword($params);
        if (Plesk_Object_Customer::TYPE_RESELLER == $params["type"]) {
            return "success";
        }
        Plesk_Registry::getInstance()->manager->setWebspacePassword($params);
        return "success";
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_AdminServicesTabFields($params)
{
    $data = http_build_query(["userid" => $params["userid"], "id" => $params["serviceid"], "aid" => $params["addonid"], "modop" => "custom", "ac" => "DetermineUserState", "token" => generate_token("plain")]);
    $javascript = "<div id=\"pleskUserState\"></div>\n<script>\n    const targetElement = jQuery(\"div#pleskUserState\");\n    jQuery(document).ready(function() {\n        WHMCS.http.jqClient.jsonPost({\n            url: \"clientsservices.php\",\n            data: \"" . $data . "\",\n            success: function(data) {\n                targetElement.text(data.string);\n            }\n        });\n    });\n</script>";
    return ["" => $javascript];
}
function plesk_DetermineUserState($params)
{
    try {
        Plesk_Loader::init($params);
        $translator = Plesk_Registry::getInstance()->translator;
        $accountInfo = Plesk_Registry::getInstance()->manager->getAccountInfo($params);
        if (!isset($accountInfo["login"])) {
            $responseString = "";
        } else {
            if ($accountInfo["login"] == $params["username"]) {
                $responseString = $translator->translate("FIELD_CHANGE_PASSWORD_MAIN_PACKAGE_DESCR");
            } else {
                $responseString = $translator->translate("FIELD_CHANGE_PASSWORD_ADDITIONAL_PACKAGE_DESCR", ["PACKAGE" => $params["domain"]]);
            }
        }
    } catch (Exception $e) {
        $responseString = Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
        $response = new WHMCS\Admin("View Clients Products/Services");
        $response->jsonResponse(["string" => $responseString]);
    }
}
function plesk_ChangePackage($params)
{
    try {
        Plesk_Loader::init($params);
        $params = array_merge($params, Plesk_Registry::getInstance()->manager->getIps($params));
        Plesk_Registry::getInstance()->manager->switchSubscription($params);
        if (Plesk_Object_Customer::TYPE_RESELLER == $params["type"]) {
            return "success";
        }
        Plesk_Registry::getInstance()->manager->processAddons($params);
        Plesk_Registry::getInstance()->manager->changeSubscriptionIp($params);
        return "success";
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_UsageUpdate($params)
{
    $services = WHMCS\Service\Service::where("server", "=", $params["serverid"])->whereIn("domainstatus", ["Active", "Suspended"])->get();
    $addons = WHMCS\Service\Addon::whereHas("customFieldValues.customField", function ($query) {
        $query->where("fieldname", "Domain");
    })->with("customFieldValues", "customFieldValues.customField")->where("server", "=", $params["serverid"])->whereIn("status", ["Active", "Suspended"])->get();
    $domains = [];
    $resellerUsernames = [];
    $resellerAccountsUsage = [];
    $domainToModel = [];
    foreach ($services as $service) {
        if ($service->product->type == "reselleraccount") {
            $resellerUsernames["service"][] = $service->username;
            $resellerToModel[$service->username] = $service;
        } else {
            if ($service->domain) {
                $domains[] = $service->domain;
                $domainToModel[$service->domain] = $service;
            }
        }
    }
    foreach ($addons as $addon) {
        if ($addon->productAddon->type == "reselleraccount") {
            $resellerUsernames["addon"][] = $addon->username;
            $resellerToModel[$addon->username] = $addon;
        } else {
            foreach ($addon->customFieldValues as $customFieldValue) {
                if ($customFieldValue->customField) {
                    if ($customFieldValue->value) {
                        $domains[] = $customFieldValue->value;
                        $domainToModel[$customFieldValue->value] = $addon;
                    }
                }
            }
        }
    }
    if (!empty($resellerUsernames) && !empty($resellerUsernames["service"])) {
        $params["usernames"] = $resellerUsernames["service"];
        try {
            Plesk_Loader::init($params);
            $resellerServiceUsage = Plesk_Registry::getInstance()->manager->getResellersUsage($params);
            $resellerAccountsUsage = $resellerServiceUsage;
        } catch (Exception $e) {
            return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
        }
    }
    if (!empty($resellerUsernames) && !empty($resellerUsernames["addon"])) {
        $params["usernames"] = $resellerUsernames["addon"];
        try {
            Plesk_Loader::init($params);
            $resellerAddonUsage = Plesk_Registry::getInstance()->manager->getResellersUsage($params);
            $resellerAccountsUsage = array_merge($resellerAccountsUsage, $resellerAddonUsage);
        } catch (Exception $e) {
            return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
        }
    }
    if (!empty($resellerAccountsUsage)) {
        foreach ($resellerAccountsUsage as $username => $usage) {
            $domainModel = $resellerToModel[$username];
            if ($domainModel) {
                $domainModel->serviceProperties->save(["diskusage" => $usage["diskusage"], "disklimit" => $usage["disklimit"], "bwusage" => $usage["bwusage"], "bwlimit" => $usage["bwlimit"], "lastupdate" => WHMCS\Carbon::now()->toDateTimeString()]);
            }
        }
    }
    if (!empty($domains)) {
        $params["domains"] = $domains;
        try {
            Plesk_Loader::init($params);
            $domainsUsage = Plesk_Registry::getInstance()->manager->getWebspacesUsage($params);
            foreach ($domainsUsage as $domainName => $usage) {
                $domainModel = $domainToModel[$domainName];
                if ($domainModel) {
                    $domainModel->serviceProperties->save(["diskusage" => $usage["diskusage"], "disklimit" => $usage["disklimit"], "bwusage" => $usage["bwusage"], "bwlimit" => $usage["bwlimit"], "lastupdate" => WHMCS\Carbon::now()->toDateTimeString()]);
                }
            }
        } catch (Exception $e) {
            return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
        }
    }
    return "success";
}
function plesk_TestConnection($params)
{
    try {
        Plesk_Loader::init($params);
        $translator = Plesk_Registry::getInstance()->translator;
        return ["success" => true];
    } catch (Exception $e) {
        return ["error" => Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()])];
    }
}
function plesk_GenerateCertificateSigningRequest($params)
{
    try {
        Plesk_Loader::init($params);
        $result = Plesk_Registry::getInstance()->manager->generateCSR($params);
        if (!$result) {
            throw new WHMCS\Exception\Module\NotServicable("Unable to automatically retrieve Certificate Signing Request from Plesk");
        }
        return ["csr" => $result->certificate->generate->result->csr->__toString(), "key" => $result->certificate->generate->result->pvt->__toString(), "saveData" => true];
    } catch (WHMCS\Exception\Module\NotServicable $e) {
        throw $e;
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_InstallSsl($params)
{
    try {
        Plesk_Loader::init($params);
        Plesk_Registry::getInstance()->manager->installSsl($params);
        return "success";
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_GetMxRecords($params)
{
    try {
        Plesk_Loader::init($params);
        return Plesk_Registry::getInstance()->manager->getMxRecords($params);
    } catch (Exception $e) {
        throw new Exception("MX Retrieval Failed: " . Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]));
    }
}
function plesk_DeleteMxRecords($params)
{
    try {
        Plesk_Loader::init($params);
        Plesk_Registry::getInstance()->manager->deleteMxRecords($params);
    } catch (Exception $e) {
        throw new Exception("Unable to Delete MX Record: " . Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]));
    }
}
function plesk_AddMxRecords($params)
{
    try {
        Plesk_Loader::init($params);
        Plesk_Registry::getInstance()->manager->addMxRecords($params);
    } catch (Exception $e) {
        throw new Exception("MX Creation Failed: " . Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]));
    }
}
function plesk_CreateFileWithinDocRoot($params)
{
    $logParams = ["serverhostname" => $params["serverhostname"], "username" => $params["username"], "filename" => $params["filename"], "fileContent" => $params["fileContent"]];
    $ftpConnection = false;
    try {
        if (function_exists("ftp_ssl_connect")) {
            $ftpConnection = @ftp_ssl_connect($params["serverhostname"]);
        }
        if (!$ftpConnection) {
            $ftpConnection = @ftp_connect($params["serverhostname"]);
        }
        if (!$ftpConnection) {
            throw new Exception("Plesk: Unable to create DV Auth File: FTP Connection Failed");
        }
        $ftpLogin = @ftp_login($ftpConnection, $params["username"], $params["password"]);
        if (!$ftpLogin) {
            throw new Exception("Plesk: Unable to create DV Auth File: FTP Login Failed");
        }
        $tempFile = tempnam(sys_get_temp_dir(), "plesk");
        if (!$tempFile) {
            throw new Exception("Plesk: Unable to create DV Auth File: Unable to Create Temp File");
        }
        $file = fopen($tempFile, "w+");
        if (!fwrite($file, $params["fileContent"])) {
            throw new Exception("Plesk: Unable to create DV Auth File: Unable to Write to Temp File");
        }
        fclose($file);
        ftp_chdir($ftpConnection, "httpdocs");
        $dir = array_key_exists("dir", $params) ? $params["dir"] : "";
        if ($dir) {
            $dirParts = explode("/", $dir);
            foreach ($dirParts as $dirPart) {
                if (!@ftp_chdir($ftpConnection, $dirPart)) {
                    ftp_mkdir($ftpConnection, $dirPart);
                    ftp_chdir($ftpConnection, $dirPart);
                }
            }
        }
        $upload = ftp_put($ftpConnection, $params["filename"], $tempFile, FTP_ASCII);
        if (!$upload) {
            ftp_pasv($ftpConnection, true);
            $upload = ftp_put($ftpConnection, $params["filename"], $tempFile, FTP_ASCII);
        }
        ftp_close($ftpConnection);
        if (!$upload) {
            throw new Exception("Plesk: Unable to create DV Auth File: Unable to Upload File: " . json_encode(error_get_last()));
        }
        logModuleCall("plesk", "plesk_CreateFileWithinDocRoot", $logParams, "success", "success");
    } catch (Exception $e) {
        logModuleCall("plesk", "plesk_CreateFileWithinDocRoot", $logParams, $e->getMessage(), $e->getMessage());
        throw $e;
    }
}
function plesk_ListAccounts($params)
{
    try {
        Plesk_Loader::init($params);
        return ["success" => true, "accounts" => Plesk_Registry::getInstance()->manager->listAccounts($params)];
    } catch (Exception $e) {
        return ["error" => Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()])];
    }
}
function plesk_GetUserCount($params)
{
    try {
        $totalCount = $ownedAccounts = 0;
        Plesk_Loader::init($params);
        $mainAccountId = 0;
        try {
            $mainAccount = Plesk_Registry::getInstance()->manager->getResellerByLogin(["username" => $params["serverusername"]]);
            $mainAccountId = $mainAccount["id"];
        } catch (Exception $e) {
            $customers = Plesk_Registry::getInstance()->manager->getCustomers([]);
            foreach ($customers as $customer) {
                $customerData = (int) $customer->data->gen_info;
                if (array_key_exists("owner-login", $customerData) && $customerData["owner-login"] == $params["serverusername"]) {
                    $totalCount += 1;
                    $ownedAccounts += 1;
                } else {
                    if (array_key_exists("owner-id", $customerData) && $customerData["owner-id"] == $mainAccountId) {
                        $totalCount += 1;
                        $ownedAccounts += 1;
                    }
                }
            }
            try {
                $resellers = Plesk_Registry::getInstance()->manager->getResellers([]);
                foreach ($resellers as $reseller) {
                    $reseller = (int) $reseller;
                    $resellerId = $reseller["id"];
                    if ($resellerId != $mainAccountId) {
                        $totalCount += count($resellers);
                        $ownedAccounts += count($resellers);
                        $resellerCustomers = Plesk_Registry::getInstance()->manager->getCustomersByOwner(["ownerId" => $resellerId]);
                        $totalCount += count($resellerCustomers);
                    }
                }
                return ["success" => true, "totalAccounts" => $totalCount, "ownedAccounts" => $ownedAccounts];
            } catch (Exception $e) {
                throw $e;
            }
        }
    } catch (Exception $e) {
        return ["error" => Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()])];
    }
}
function plesk_GetRemoteMetaData($params)
{
    $version = "-";
    try {
        $loads = ["fifteen" => "0", "five" => "0", "one" => "0"];
        $maxUsers = "0";
        Plesk_Loader::init($params);
        $serverInformation = Plesk_Registry::getInstance()->manager->getServerData([]);
        if (isset($serverInformation->stat->version)) {
            $version = (int) $serverInformation->stat->version->plesk_version;
        }
        if (isset($serverInformation->stat->load_avg)) {
            $loads = ["fifteen" => (int) $serverInformation->stat->load_avg->l15 / 100, "five" => (int) $serverInformation->stat->load_avg->l5 / 100, "one" => (int) $serverInformation->stat->load_avg->l1 / 100];
        }
        if (isset($serverInformation->key)) {
            $licenseInfo = [];
            foreach ($serverInformation->key->property as $data) {
                $data = (int) $data;
                $licenseInfo[$data["name"]] = $data["value"];
            }
            if (array_key_exists("lim_cl", $licenseInfo)) {
                $maxUsers = $licenseInfo["lim_cl"];
            }
        }
        return ["version" => $version, "load" => $loads, "max_accounts" => $maxUsers];
    } catch (Exception $e) {
        return ["error" => Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()])];
    }
}
function plesk_RenderRemoteMetaData($params)
{
    $remoteData = $params["remoteData"];
    if ($remoteData) {
        $metaData = $remoteData->metaData;
        $version = "Unknown";
        $loadOne = $loadFive = $loadFifteen = 0;
        $maxAccounts = "Unlimited";
        if (array_key_exists("version", $metaData)) {
            $version = $metaData["version"];
        }
        if (array_key_exists("load", $metaData)) {
            $loadOne = $metaData["load"]["one"];
            $loadFive = $metaData["load"]["five"];
            $loadFifteen = $metaData["load"]["fifteen"];
        }
        if (array_key_exists("max_accounts", $metaData) && 0 < $metaData["max_accounts"]) {
            $maxAccounts = $metaData["max_accounts"];
        }
        return "Plesk Version: " . $version . "<br>\nLoad Averages: " . $loadOne . " " . $loadFive . " " . $loadFifteen . "<br>\nLicense Max # of Accounts: " . $maxAccounts;
    }
    return "";
}
function plesk_GetSPFRecord($params)
{
    try {
        Plesk_Loader::init($params);
        return Plesk_Registry::getInstance()->manager->getSPFRecord($params);
    } catch (Exception $e) {
        throw new Exception("SPF Retrieval Failed: " . Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]));
    }
}
function plesk_SetSPFRecord($params)
{
    try {
        Plesk_Loader::init($params);
        Plesk_Registry::getInstance()->manager->setSPFRecord($params);
    } catch (Exception $e) {
        throw new Exception("SPF Set Failed: " . Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]));
    }
}
function plesk_ListAddOnFeatures($plesk_ListAddOnFeatures, $params)
{
    Plesk_Loader::init($params);
    $vasManager = new Plesk_ValueAddedServiceManager($params);
    try {
        return $vasManager->getValueAddedServicesList($params);
    } catch (Exception $e) {
        return ["error" => $e->getMessage()];
    }
}
function plesk_ProvisionAddOnFeature($plesk_ProvisionAddOnFeature, $params)
{
    try {
        Plesk_Loader::init($params);
        (new Plesk_ValueAddedServiceManager($params))->checkRequiredValueAddedServicesExist($params);
        $params["configoptions"] = [$params["configoption1"] => 1];
        Plesk_Registry::getInstance()->manager->processAddons($params);
        return "success";
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_DeprovisionAddOnFeature($plesk_DeprovisionAddOnFeature, $params)
{
    try {
        Plesk_Loader::init($params);
        (new Plesk_ValueAddedServiceManager($params))->checkRequiredValueAddedServicesExist($params);
        $params["configoptions"] = [$params["configoption1"] => 0];
        Plesk_Registry::getInstance()->manager->processAddons($params);
        return "success";
    } catch (Exception $e) {
        return Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]);
    }
}
function plesk_SuspendAddOnFeature($plesk_SuspendAddOnFeature, $params)
{
    return plesk_deprovisionaddonfeature($params);
}
function plesk_UnsuspendAddOnFeature($plesk_UnsuspendAddOnFeature, $params)
{
    return plesk_provisionaddonfeature($params);
}
function plesk_getProductTypesForAddOn($plesk_getProductTypesForAddOn, $params)
{
    switch ($params["Feature Name"]) {
        case "Plesk WordPress Toolkit with Smart Updates":
            return ["hostingaccount"];
            break;
        default:
            return ["hostingsaccount", "reselleraccount", "server", "other"];
    }
}

?>