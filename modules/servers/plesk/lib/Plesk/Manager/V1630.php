<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Manager_V1630 extends Plesk_Manager_V1000
{
    protected function _getResellerPlans()
    {
        $result = Plesk_Registry::getInstance()->api->resellerPlan_get();
        $resellerPlans = [];
        foreach ($result->xpath("//reseller-plan/get/result") as $result) {
            $resellerPlans[] = new Plesk_Object_ResellerPlan((int) $result->id, (int) $result->name);
        }
        return $resellerPlans;
    }
    protected function _getAccountInfo($params, $panelExternalId = NULL)
    {
        $accountInfo = [];
        if (is_null($panelExternalId)) {
            $this->createTableForAccountStorage();
            $account = WHMCS\Database\Capsule::table("mod_pleskaccounts")->where("userid", $params["clientsdetails"]["userid"])->where("usertype", $params["type"])->first();
            $panelExternalId = is_null($account) ? "" : $account->panelexternalid;
        }
        if ("" != $panelExternalId) {
            $requestParams = ["externalId" => $panelExternalId];
            switch ($params["type"]) {
                case Plesk_Object_Customer::TYPE_CLIENT:
                    try {
                        $result = Plesk_Registry::getInstance()->api->customer_get_by_external_id($requestParams);
                        if (isset($result->customer->get->result->id)) {
                            $accountInfo["id"] = (int) $result->customer->get->result->id;
                        }
                        if (isset($result->customer->get->result->data->gen_info->login)) {
                            $accountInfo["login"] = (int) $result->customer->get->result->data->gen_info->login;
                        }
                    } catch (Exception $e) {
                        if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                            throw $e;
                        }
                        throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_CUSTOMER_WITH_EXTERNAL_ID_NOT_FOUND_IN_PANEL", ["EXTERNAL_ID" => $panelExternalId]), Plesk_Api::ERROR_OBJECT_NOT_FOUND);
                    }
                    break;
                case Plesk_Object_Customer::TYPE_RESELLER:
                    try {
                        $result = Plesk_Registry::getInstance()->api->reseller_get_by_external_id($requestParams);
                        if (isset($result->reseller->get->result->id)) {
                            $accountInfo["id"] = (int) $result->reseller->get->result->id;
                        }
                        if (isset($result->reseller->get->result->data->_obfuscated_67656E2D696E666F_->login)) {
                            $accountInfo["login"] = (int) $result->reseller->get->result->data->_obfuscated_67656E2D696E666F_->login;
                        }
                    } catch (Exception $e) {
                        if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                            throw $e;
                        }
                        throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_RESELLER_WITH_EXTERNAL_ID_NOT_FOUND_IN_PANEL", ["EXTERNAL_ID" => $panelExternalId]), Plesk_Api::ERROR_OBJECT_NOT_FOUND);
                    }
                    break;
                default:
                    return $accountInfo;
            }
        } else {
            $accountsArray = [];
            $productsOnServer = WHMCS\Database\Capsule::table("tblhosting")->where("server", $params["serverid"])->where("userid", $params["clientsdetails"]["userid"])->get()->all();
            if ($productsOnServer) {
                foreach ($productsOnServer as $product) {
                    $accountsArray[] = $product->username;
                }
            }
            $addonsOnServer = $addons = WHMCS\Service\Addon::with(["customFieldValues", "customFieldValues.customField" => function ($query) {
                $query->where("fieldname", "=", "username");
            }])->where("server", $params["serverid"])->where("userid", $params["clientsdetails"]["userid"])->get();
            if ($addonsOnServer) {
                foreach ($addonsOnServer as $addon) {
                    $pleskUsername = $addon->customFieldValue["username"];
                    if ($pleskUsername) {
                        $accountsArray[] = $pleskUsername;
                    }
                }
            }
            foreach ($accountsArray as $username) {
                $requestParams = ["login" => $username];
                switch ($params["type"]) {
                    case Plesk_Object_Customer::TYPE_CLIENT:
                        try {
                            $result = Plesk_Registry::getInstance()->api->customer_get_by_login($requestParams);
                            if (isset($result->customer->get->result->id)) {
                                $accountInfo["id"] = (int) $result->customer->get->result->id;
                            }
                            if (isset($result->customer->get->result->data->gen_info->login)) {
                                $accountInfo["login"] = (int) $result->customer->get->result->data->gen_info->login;
                            }
                        } catch (Exception $e) {
                            if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                                throw $e;
                            }
                        }
                        break;
                    case Plesk_Object_Customer::TYPE_RESELLER:
                        try {
                            $result = Plesk_Registry::getInstance()->api->reseller_get_by_login($requestParams);
                            if (isset($result->reseller->get->result->id)) {
                                $accountInfo["id"] = (int) $result->reseller->get->result->id;
                            }
                            if (isset($result->reseller->get->result->data->_obfuscated_67656E2D696E666F_->login)) {
                                $accountInfo["login"] = (int) $result->reseller->get->result->data->_obfuscated_67656E2D696E666F_->login;
                            }
                        } catch (Exception $e) {
                            if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                                throw $e;
                            }
                        }
                        break;
                    default:
                        if (!empty($accountInfo)) {
                            if (empty($accountInfo)) {
                                throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_CUSTOMER_WITH_EMAIL_NOT_FOUND_IN_PANEL", ["EMAIL" => $params["clientsdetails"]["email"]]), Plesk_Api::ERROR_OBJECT_NOT_FOUND);
                            }
                            return $accountInfo;
                        }
                }
            }
        }
    }
    protected function _getAddAccountParams($params)
    {
        $result = self::_getAddAccountParams($params);
        $result["externalId"] = $this->_getCustomerExternalId($params);
        return $result;
    }
    protected function _addAccount($params)
    {
        $accountId = NULL;
        $requestParams = $this->_getAddAccountParams($params);
        switch ($params["type"]) {
            case Plesk_Object_Customer::TYPE_RESELLER:
                $requestParams = array_merge($requestParams, ["planName" => $params["configoption2"]]);
                $result = Plesk_Registry::getInstance()->api->reseller_add($requestParams);
                $accountId = (int) $result->reseller->add->result->id;
                break;
            case Plesk_Object_Customer::TYPE_CLIENT:
            default:
                $result = Plesk_Registry::getInstance()->api->customer_add($requestParams);
                $accountId = (int) $result->customer->add->result->id;
                return $accountId;
        }
    }
    protected function _addWebspace($params)
    {
        $this->_checkRestrictions($params);
        $requestParams = ["domain" => $params["domain"], "ownerId" => $params["ownerId"], "username" => $params["username"], "password" => $params["password"], "status" => Plesk_Object_Webspace::STATUS_ACTIVE, "htype" => Plesk_Object_Webspace::TYPE_VRT_HST, "planName" => $params["configoption1"], "ipv4Address" => $params["ipv4Address"], "ipv6Address" => $params["ipv6Address"]];
        Plesk_Registry::getInstance()->api->webspace_add($requestParams);
    }
    protected function _setResellerStatus($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return NULL;
        }
        Plesk_Registry::getInstance()->api->reseller_set_status(["status" => $params["status"], "id" => $accountInfo["id"]]);
    }
    protected function _deleteReseller($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return NULL;
        }
        Plesk_Registry::getInstance()->api->reseller_del(["id" => $accountInfo["id"]]);
    }
    protected function _setAccountPassword($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return NULL;
        }
        if (isset($accountInfo["login"]) && $accountInfo["login"] != $params["username"]) {
            return NULL;
        }
        $requestParams = ["id" => $accountInfo["id"], "accountPassword" => $params["password"]];
        switch ($params["type"]) {
            case Plesk_Object_Customer::TYPE_CLIENT:
                Plesk_Registry::getInstance()->api->customer_set_password($requestParams);
                break;
            case Plesk_Object_Customer::TYPE_RESELLER:
                Plesk_Registry::getInstance()->api->reseller_set_password($requestParams);
                break;
        }
    }
    protected function _deleteWebspace($params)
    {
        Plesk_Registry::getInstance()->api->webspace_del(["domain" => $params["domain"]]);
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return NULL;
        }
        $webspaces = $this->_getWebspacesByOwnerId($accountInfo["id"]);
        if (!isset($webspaces->id)) {
            Plesk_Registry::getInstance()->api->customer_del(["id" => $accountInfo["id"]]);
        }
    }
    protected function _switchSubscription($params)
    {
        switch ($params["type"]) {
            case Plesk_Object_Customer::TYPE_CLIENT:
                $result = Plesk_Registry::getInstance()->api->service_plan_get_by_name(["name" => $params["configoption1"]]);
                $servicePlanResult = reset($result->xpath("//service-plan/get/result"));
                Plesk_Registry::getInstance()->api->switch_subscription(["domain" => $params["domain"], "planGuid" => (int) $servicePlanResult->guid]);
                break;
            case Plesk_Object_Customer::TYPE_RESELLER:
                $result = Plesk_Registry::getInstance()->api->reseller_plan_get_by_name(["name" => $params["configoption2"]]);
                $resellerPlanResult = reset($result->xpath("//reseller-plan/get/result"));
                $accountInfo = $this->_getAccountInfo($params);
                if (!isset($accountInfo["id"])) {
                } else {
                    Plesk_Registry::getInstance()->api->switch_reseller_plan(["id" => $accountInfo["id"], "planGuid" => (int) $resellerPlanResult->guid]);
                }
                break;
        }
    }
    protected function _processAddons($_processAddons, $params)
    {
        $result = Plesk_Registry::getInstance()->api->webspace_subscriptions_get_by_name(["domain" => $params["domain"]]);
        $planGuids = [];
        foreach ($result->xpath("//webspace/get/result/data/subscriptions/subscription/plan/plan-guid") as $guid) {
            $planGuids[] = (int) $guid;
        }
        $webspaceId = (int) $result->webspace->get->result->id;
        $excludedPlanGuids = [];
        $servicePlan = Plesk_Registry::getInstance()->api->service_plan_get_by_guid(["planGuids" => $planGuids]);
        foreach ($servicePlan->xpath("//service-plan/get/result") as $result) {
            try {
                $this->_checkErrors($result);
                $excludedPlanGuids[] = (int) $result->guid;
            } catch (Exception $e) {
                if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                    throw $e;
                }
            }
        }
        $addons = [];
        $addonGuids = array_diff($planGuids, $excludedPlanGuids);
        if (!empty($addonGuids)) {
            $addon = Plesk_Registry::getInstance()->api->service_plan_addon_get_by_guid(["addonGuids" => $addonGuids]);
            foreach ($addon->xpath("//service-plan-addon/get/result") as $result) {
                try {
                    $this->_checkErrors($result);
                    $addons[(int) $result->guid] = (int) $result->name;
                } catch (Exception $e) {
                    if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                        throw $e;
                    }
                }
            }
        }
        $addonsToRemove = [];
        $addonsFromRequest = [];
        $skipAddonPrefix = (int) Plesk_Config::get()->skip_addon_prefix;
        foreach ($params["configoptions"] as $addonTitle => $value) {
            if ("0" != $value) {
                $addonTitleHasPrefix = 0 === strpos($addonTitle, Plesk_Object_Addon::ADDON_PREFIX);
                if ($skipAddonPrefix) {
                    $pleskAddonTitle = $addonTitleHasPrefix ? $this->sanitizeAddonName($addonTitle) : $addonTitle;
                } else {
                    if ($addonTitleHasPrefix) {
                        $pleskAddonTitle = $this->sanitizeAddonName($addonTitle);
                    }
                }
                $addonsFromRequest[] = "1" == $value ? $pleskAddonTitle : $value;
            }
        }
        foreach ($addons as $guid => $addonName) {
            if (!in_array($addonName, $addonsFromRequest)) {
                $addonsToRemove[$guid] = $addonName;
            }
        }
        $addonsToAdd = array_diff($addonsFromRequest, array_values($addons));
        foreach ($addonsToRemove as $guid => $addon) {
            Plesk_Registry::getInstance()->api->webspace_remove_subscription(["planGuid" => $guid, "id" => $webspaceId]);
        }
        foreach ($addonsToAdd as $addonName) {
            $addon = Plesk_Registry::getInstance()->api->service_plan_addon_get_by_name(["name" => $addonName]);
            foreach ($addon->xpath("//service-plan-addon/get/result/guid") as $guid) {
                Plesk_Registry::getInstance()->api->webspace_add_subscription(["planGuid" => (int) $guid, "id" => $webspaceId]);
            }
        }
    }
    protected function sanitizeAddonName($addonTitle)
    {
        return substr_replace($addonTitle, "", 0, strlen(Plesk_Object_Addon::ADDON_PREFIX));
    }
    protected function _getWebspacesUsage($params)
    {
        $usage = [];
        $data = Plesk_Registry::getInstance()->api->webspace_usage_get_by_name(["domains" => $params["domains"]]);
        foreach ($data->xpath("//webspace/get/result") as $result) {
            try {
                $this->_checkErrors($result);
                $domainName = (int) $result->data->gen_info->name;
                $usage[$domainName]["diskusage"] = (int) $result->data->gen_info->real_size;
                $usage[$domainName]["bwusage"] = (int) $result->data->stat->traffic;
                $usage[$domainName] = array_merge($usage[$domainName], $this->_getLimits($result->data->limits));
            } catch (Exception $e) {
                if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                    throw $e;
                }
            }
        }
        foreach ($data->xpath("//site/get/result") as $result) {
            try {
                $parentDomainName = (int) reset($result->xpath("filter-id"));
                $usage[$parentDomainName]["bwusage"] += (int) $result->data->stat->traffic;
            } catch (Exception $e) {
                if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                    throw $e;
                }
            }
        }
        foreach ($usage as $domainName => $domainUsage) {
            foreach ($domainUsage as $param => $value) {
                $usage[$domainName][$param] = $usage[$domainName][$param] / 1048576;
            }
        }
        return $usage;
    }
    protected function _getResellersUsage($params)
    {
        $usage = [];
        $data = Plesk_Registry::getInstance()->api->reseller_get_usage_by_login(["logins" => $params["usernames"]]);
        foreach ($data->xpath("//reseller/get/result") as $result) {
            try {
                $this->_checkErrors($result);
                $login = (int) $result->data->_obfuscated_67656E2D696E666F_->login;
                $usage[$login]["diskusage"] = (int) $result->data->stat->_obfuscated_6469736B2D7370616365_;
                $usage[$login]["bwusage"] = (int) $result->data->stat->traffic;
                $usage[$login] = array_merge($usage[$login], $this->_getLimits($result->data->limits));
            } catch (Exception $e) {
                if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                    throw $e;
                }
            }
        }
        foreach ($usage as $login => $loginUsage) {
            foreach ($loginUsage as $param => $value) {
                $usage[$login][$param] = $usage[$login][$param] / 1048576;
            }
        }
        return $usage;
    }
    protected function _addIpToIpPool($accountId, $params)
    {
    }
    protected function _getWebspacesByOwnerId($ownerId)
    {
        $result = Plesk_Registry::getInstance()->api->webspaces_get_by_owner_id(["ownerId" => $ownerId]);
        return $result->webspace->get->result;
    }
    protected function _getCustomerExternalId($params)
    {
        return Plesk_Object_Customer::getCustomerExternalId($params);
    }
    protected function _changeSubscriptionIp($params)
    {
        $webspace = Plesk_Registry::getInstance()->api->webspace_get_by_name(["domain" => $params["domain"]]);
        $ipDedicatedList = $this->_getIpList(Plesk_Object_Ip::DEDICATED);
        $oldIp[Plesk_Object_Ip::IPV4] = (int) $webspace->webspace->get->result->data->hosting->vrt_hst->ip_address;
        $ipv4Address = isset($oldIp[Plesk_Object_Ip::IPV4]) ? $oldIp[Plesk_Object_Ip::IPV4] : "";
        if ($params["configoption3"] == "IPv4 none; IPv6 shared" || $params["configoption3"] == "IPv4 none; IPv6 dedicated") {
            $ipv4Address = "";
        }
        if (!empty($params["ipv4Address"])) {
            if (isset($oldIp[Plesk_Object_Ip::IPV4]) && $oldIp[Plesk_Object_Ip::IPV4] != $params["ipv4Address"] && (!in_array($oldIp[Plesk_Object_Ip::IPV4], $ipDedicatedList) || !in_array($params["ipv4Address"], $ipDedicatedList))) {
                $ipv4Address = $params["ipv4Address"];
            } else {
                if (!isset($oldIp[Plesk_Object_Ip::IPV4])) {
                    $ipv4Address = $params["ipv4Address"];
                }
            }
        }
        if (!empty($ipv4Address)) {
            Plesk_Registry::getInstance()->api->webspace_set_ip(["domain" => $params["domain"], "ipv4Address" => $ipv4Address]);
        }
    }
    protected function _getLimits(SimpleXMLElement $limits)
    {
        $result = [];
        foreach ($limits->limit as $limit) {
            $name = (int) $limit->name;
            switch ($name) {
                case "disk_space":
                    $result["disklimit"] = (int) $limit->value;
                    break;
                case "max_traffic":
                    $result["bwlimit"] = (int) $limit->value;
                    break;
            }
        }
        return $result;
    }
    protected function _getServicePlans()
    {
        $result = Plesk_Registry::getInstance()->api->service_plan_get();
        $plans = [];
        foreach ($result->xpath("//service-plan/get/result") as $plan) {
            $plans[] = (int) $plan->name;
        }
        return $plans;
    }
    protected function _generateCSR($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return "";
        }
        if (isset($accountInfo["login"]) && $accountInfo["login"] != $params["username"]) {
            return "";
        }
        return Plesk_Registry::getInstance()->api->certificate_generate($params["certificateInfo"]);
    }
    protected function _installSsl($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return "";
        }
        if (isset($accountInfo["login"]) && $accountInfo["login"] != $params["username"]) {
            return "";
        }
        return Plesk_Registry::getInstance()->api->certificate_install($params);
    }
    protected function _getMxRecords($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return "";
        }
        if (isset($accountInfo["login"]) && $accountInfo["login"] != $params["username"]) {
            return "";
        }
        $webSpace = Plesk_Registry::getInstance()->api->webspace_get_by_name(["domain" => $params["domain"]]);
        $siteId = (int) $webSpace->webspace->get->result->id;
        $records = Plesk_Registry::getInstance()->api->dns_record_retrieve(["siteId" => (int) $siteId]);
        $mxRecords = [];
        foreach ($records->dns->get_rec->result as $dnsRecord) {
            if (strtolower($dnsRecord->data->type->__toString()) === "mx") {
                $mxData = (int) $dnsRecord->data;
                $mxRecords[] = ["id" => (int) $dnsRecord->id, "mx" => $mxData["value"], "priority" => $mxData["opt"]];
            }
        }
        return ["mxRecords" => $mxRecords];
    }
    protected function _deleteMxRecords($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return NULL;
        }
        if (isset($accountInfo["login"]) && $accountInfo["login"] != $params["username"]) {
            return NULL;
        }
        $dnsToRemove = [];
        foreach ($params["mxRecords"] as $record) {
            $dnsToRemove[] = $record["id"];
        }
        Plesk_Registry::getInstance()->api->dns_record_delete(["dnsRecords" => $dnsToRemove]);
    }
    protected function _addMxRecords($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return NULL;
        }
        if (isset($accountInfo["login"]) && $accountInfo["login"] != $params["username"]) {
            return NULL;
        }
        $webSpace = Plesk_Registry::getInstance()->api->webspace_get_by_name(["domain" => $params["domain"]]);
        $siteId = (int) $webSpace->webspace->get->result->id;
        $params["pleskSiteId"] = $siteId;
        Plesk_Registry::getInstance()->api->mx_record_create($params);
    }
    protected function _getSPFRecord($params)
    {
        $accountInfo = $this->_getAccountInfo($params);
        if (!isset($accountInfo["id"])) {
            return "";
        }
        if (isset($accountInfo["login"]) && $accountInfo["login"] != $params["username"]) {
            return "";
        }
        $webSpace = Plesk_Registry::getInstance()->api->webspace_get_by_name(["domain" => $params["domain"]]);
        $siteId = (int) (int) $webSpace->webspace->get->result->id;
        $records = Plesk_Registry::getInstance()->api->dns_record_retrieve(["siteId" => $siteId]);
        foreach ($records->dns->get_rec->result as $dnsRecord) {
            if (strtolower($dnsRecord->data->type->__toString()) === "txt") {
                $spfRecord = (int) $dnsRecord->data;
                return ["siteId" => $siteId, "recordId" => $dnsRecord->id->__toString(), "spfRecord" => $spfRecord["value"]];
            }
        }
        return "";
    }
    protected function _setSPFRecord($params)
    {
        $spfRecord = $this->_getSPFRecord($params);
        if (is_array($spfRecord)) {
            $siteId = $spfRecord["siteId"];
            $spfRecordId = $spfRecord["recordId"];
            Plesk_Registry::getInstance()->api->dns_record_delete(["dnsRecords" => $spfRecordId]);
            Plesk_Registry::getInstance()->api->dns_record_create(["pleskSiteId" => $siteId, "dnsRecords" => [["type" => "TXT", "value" => $params["spfRecord"]]]]);
        }
    }
    protected function _listAccounts($_listAccounts, $params)
    {
        $data = Plesk_Registry::getInstance()->api->webspace_get_all([]);
        $response = [];
        foreach ($data->xpath("//webspace/get/result") as $webSpace) {
            $webSpaceData = $webSpace->data->gen_info;
            if ($webSpaceData) {
                $planData = $webSpace->data->subscriptions;
                $planData = (int) $planData->subscription->plan;
                $planGuid = $planData["plan-guid"];
                $webSpaceDataArray = (int) $webSpaceData;
                $ownerId = $webSpaceDataArray["owner-id"];
                try {
                    $ownerData = Plesk_Registry::getInstance()->api->customer_get_by_id(["id" => $ownerId]);
                    list($ownerData) = $ownerData->xpath("//customer/get/result");
                } catch (Exception $e) {
                    if ($e->getMessage() === "Client does not exist") {
                        $ownerData = Plesk_Registry::getInstance()->api->reseller_get_by_id(["id" => $ownerId]);
                        list($ownerData) = $ownerData->xpath("//reseller/get/result");
                        $username = $ownerData->xpath("//login")[0]->__toString();
                        try {
                            $servicePlan = Plesk_Registry::getInstance()->api->service_plan_get_by_guid(["planGuids" => [$planGuid]]);
                            $planName = $servicePlan->xpath("//service-plan/get/result/name")[0]->__toString();
                            $status = WHMCS\Service\Status::ACTIVE;
                            if ((int) $webSpaceDataArray["status"]) {
                                $status = WHMCS\Service\Status::SUSPENDED;
                            }
                            $response[] = ["name" => $username, "email" => $ownerData->xpath("//email")[0]->__toString(), "username" => $username, "domain" => $webSpaceDataArray["name"], "uniqueIdentifier" => $webSpaceDataArray["name"], "product" => $planName, "primaryip" => $webSpaceDataArray["dns_ip_address"], "created" => $webSpaceDataArray["cr_date"] . " 00:00:00", "status" => $status];
                        } catch (Exception $e) {
                        }
                    } else {
                        throw $e;
                    }
                }
            }
        }
        return $response;
    }
    protected function _getCustomers($params)
    {
        $data = Plesk_Registry::getInstance()->api->customer_get();
        $data = $data->xpath("//result");
        return $data;
    }
    protected function _getCustomersByOwner($params)
    {
        $data = Plesk_Registry::getInstance()->api->customer_get_by_owner(["ownerId" => $params["ownerId"]]);
        $data = $data->xpath("//result");
        return $data;
    }
    protected function _getResellers($params)
    {
        $data = Plesk_Registry::getInstance()->api->reseller_get();
        $data = $data->xpath("//result");
        return $data;
    }
    protected function _getResellerByLogin($params)
    {
        $data = Plesk_Registry::getInstance()->api->reseller_get_by_login(["login" => $params["username"]]);
        return (int) $data->reseller->get->result;
    }
    protected function _getServerData($params)
    {
        $data = Plesk_Registry::getInstance()->api->get_server_info();
        return $data->server->get->result;
    }
}

?>