<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Manager_V1000 extends Plesk_Manager_Base
{
    protected function _getSupportedApiVersions()
    {
        $result = Plesk_Registry::getInstance()->api->server_getProtos();
        $versions = [];
        foreach ($result->server->get_protos->result->protos->proto as $proto) {
            $versions[] = (int) $proto;
        }
        rsort($versions);
        return $versions;
    }
    protected function _getSharedIpv4($params)
    {
        return $this->_getIp($params);
    }
    protected function _getSharedIpv6($params)
    {
        throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_IPV6_DOES_NOT_SUPPORTED"));
    }
    protected function _getFreeDedicatedIpv4()
    {
        return $this->_getFreeDedicatedIp();
    }
    protected function _getFreeDedicatedIpv6()
    {
        throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_IPV6_DOES_NOT_SUPPORTED"));
    }
    protected function _setAccountPassword($params)
    {
        $requestParams = ["login" => $params["username"], "accountPassword" => $params["password"]];
        switch ($params["type"]) {
            case Plesk_Object_Customer::TYPE_CLIENT:
                Plesk_Registry::getInstance()->api->customer_set_password($requestParams);
                break;
            case Plesk_Object_Customer::TYPE_RESELLER:
                Plesk_Registry::getInstance()->api->reseller_set_password($requestParams);
                break;
        }
    }
    protected function _getIp($params, $version = Plesk_Object_Ip::IPV4)
    {
        $ipList = $this->_getIpList(Plesk_Object_Ip::SHARED, $version);
        $ipAddress = reset($ipList);
        if (!$ipAddress) {
            if (Plesk_Object_Ip::IPV6 == $version && !$this->_isIpv6($params["serverip"])) {
                throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_NO_SHARED_IPV6"));
            }
            if (Plesk_Object_Ip::IPV4 == $version && $this->_isIpv6($params["serverip"])) {
                throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_NO_SHARED_IPV4"));
            }
            $ipAddress = $params["serverip"];
        }
        return $ipAddress;
    }
    protected function _setWebspaceStatus($params)
    {
        Plesk_Registry::getInstance()->api->webspace_set_status(["status" => $params["status"], "domain" => $params["domain"]]);
    }
    protected function _deleteWebspace($params)
    {
        Plesk_Registry::getInstance()->api->webspace_del(["domain" => $params["domain"]]);
        $manager = new Plesk_Manager_V1000();
        $ownerInfo = $manager->getAccountInfo($params);
        $webspaces = $this->_getWebspacesByOwnerId($ownerInfo["id"]);
        if (!isset($webspaces->id)) {
            Plesk_Registry::getInstance()->api->customer_del(["id" => $ownerInfo["id"]]);
        }
    }
    protected function _setWebspacePassword($params)
    {
        Plesk_Registry::getInstance()->api->webspace_set_password(["domain" => $params["domain"], "password" => $params["password"]]);
    }
    protected function _getClientAreaForm($params)
    {
        $domain = $params["serverhostname"] ? $params["serverhostname"] : $params["serverip"];
        $port = $params["serveraccesshash"] ? $params["serveraccesshash"] : "8443";
        $secure = $params["serversecure"] ? "https" : "http";
        $hosting = Illuminate\Database\Capsule\Manager::table("tblhosting")->where("server", $params["serverid"])->where("userid", $params["clientsdetails"]["userid"])->where("domainstatus", "Active")->first();
        $code = "";
        if ($hosting->username && $hosting->password) {
            $manager = new Plesk_Manager_V1000();
            $ownerInfo = $manager->getAccountInfo($params);
            if (!isset($ownerInfo["login"])) {
                return "";
            }
            $code = sprintf("<form action=\"%s://%s:%s/login_up.php3\" method=\"get\" target=\"_blank\"><input type=\"hidden\" name=\"login_name\" value=\"%s\" /><input type=\"hidden\" name=\"passwd\" value=\"%s\" /><input type=\"submit\" class=\"button\" value=\"%s\" /></form>", $secure, WHMCS\Input\Sanitize::encode($domain), WHMCS\Input\Sanitize::encode($port), WHMCS\Input\Sanitize::encode($ownerInfo["login"]), WHMCS\Input\Sanitize::encode(decrypt($hosting->password)), Lang::trans("plesklogin"));
        }
        return $code;
    }
    protected function _getFreeDedicatedIp($version = Plesk_Object_Ip::IPV4)
    {
        $ipListUse = [];
        $ipListFree = [];
        $ipList = $this->_getIpList(Plesk_Object_Ip::DEDICATED, $version);
        if (is_null($domains)) {
            $domains = Plesk_Registry::getInstance()->api->webspaces_get();
        }
        foreach ($domains->xpath("//domain/get/result") as $item) {
            try {
                $this->_checkErrors($item);
                if (!empty($item->data->hosting->vrt_hst->ip_address)) {
                    $ipListUse[] = (int) $item->data->hosting->vrt_hst->ip_address;
                }
            } catch (Exception $e) {
                if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                    throw $e;
                }
            }
        }
        foreach ($ipList as $ip) {
            if (!in_array($ip, $ipListUse)) {
                $ipListFree[$ip] = $ip;
            }
        }
        $freeIp = reset($ipListFree);
        if (empty($freeIp)) {
            throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_NO_FREE_DEDICATED_IPTYPE", ["TYPE" => Plesk_Object_Ip::IPV6 == $version ? "IPv6" : "IPv4"]));
        }
        return $freeIp;
    }
    protected function _getIpList($_getIpList = Plesk_Object_Ip::SHARED, $type = NULL, $version)
    {
        $ipList = [];
        if (is_null($result)) {
            $result = Plesk_Registry::getInstance()->api->ip_get();
        }
        foreach ($result->ip->get->result->addresses->ip as $item) {
            if ($type === (int) $item->type) {
                $ip = (int) $item->ip_address;
                if (Plesk_Object_Ip::IPV6 !== $version || $this->_isIpv6($ip)) {
                    if (!(Plesk_Object_Ip::IPV4 === $version && $this->_isIpv6($ip))) {
                        $ipList[] = $ip;
                    }
                }
            }
        }
        return $ipList;
    }
    protected function _isIpv6($ip)
    {
        return false === strpos($ip, ".");
    }
    protected function _getCustomerExternalId($params)
    {
        return "";
    }
    protected function _getAccountInfo($params, $panelExternalId = NULL)
    {
        $accountInfo = [];
        $hosting = Illuminate\Database\Capsule\Manager::table("tblhosting")->where("server", $params["serverid"])->where("userid", $params["clientsdetails"]["userid"])->first();
        $login = is_null($hosting) ? "" : $hosting->username;
        try {
            $result = Plesk_Registry::getInstance()->api->customer_get_by_login(["login" => $login]);
            if (isset($result->client->get->result->id)) {
                $accountInfo["id"] = (int) $result->client->get->result->id;
            }
            if (isset($result->client->get->result->data->gen_info->login)) {
                $accountInfo["login"] = (int) $result->client->get->result->data->gen_info->login;
            }
        } catch (Exception $e) {
            if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                throw $e;
            }
            if (empty($accountInfo)) {
                throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_CUSTOMER_WITH_EMAIL_NOT_FOUND_IN_PANEL", ["EMAIL" => $params["clientsdetails"]["email"]]), Plesk_Api::ERROR_OBJECT_NOT_FOUND);
            }
            return $accountInfo;
        }
    }
    protected function _addAccount($params)
    {
        $accountId = NULL;
        $result = Plesk_Registry::getInstance()->api->customer_add($this->_getAddAccountParams($params));
        $accountId = (int) $result->client->add->result->id;
        return $accountId;
    }
    protected function _getAddAccountParams($params)
    {
        $result = array_merge($params["clientsdetails"], ["username" => $params["username"], "accountPassword" => $params["password"], "status" => Plesk_Object_Customer::STATUS_ACTIVE]);
        return $result;
    }
    protected function _addIpToIpPool($accountId, $params)
    {
        Plesk_Registry::getInstance()->api->customer_ippool_add_ip(["clientId" => $accountId, "ipAddress" => $params["ipv4Address"]]);
    }
    protected function _addWebspace($params)
    {
        $this->_checkRestrictions($params);
        $requestParams = ["domain" => $params["domain"], "ownerId" => $params["ownerId"], "username" => $params["username"], "password" => $params["password"], "status" => Plesk_Object_Webspace::STATUS_ACTIVE, "htype" => Plesk_Object_Webspace::TYPE_VRT_HST, "planName" => $params["configoption1"], "ipv4Address" => $params["ipv4Address"], "ipv6Address" => $params["ipv6Address"]];
        Plesk_Registry::getInstance()->api->webspace_add($requestParams);
    }
    protected function _getWebspacesUsage($params)
    {
        $usage = [];
        $webspaces = Plesk_Registry::getInstance()->api->domain_usage_get_by_name(["domains" => $params["domains"]]);
        foreach ($webspaces->xpath("//domain/get/result") as $result) {
            try {
                $this->_checkErrors($result);
                $domainName = (int) $result->data->gen_info->name;
                $usage[$domainName]["diskusage"] = (int) $result->data->gen_info->real_size;
                $usage[$domainName]["bwusage"] = (int) $result->data->stat->traffic;
                foreach ($result->data->limits->children() as $limit) {
                    $name = (int) $limit->getName();
                    switch ($name) {
                        case "disk_space":
                            $usage[$domainName]["disklimit"] = (int) $limit;
                            break;
                        case "max_traffic":
                            $usage[$domainName]["bwlimit"] = (int) $limit;
                            break;
                    }
                }
                foreach ($usage[$domainName] as $param => $value) {
                    $usage[$domainName][$param] = $usage[$domainName][$param] / 1048576;
                }
            } catch (Exception $e) {
                if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                    throw $e;
                }
            }
        }
        return $usage;
    }
    protected function _getWebspacesByOwnerId($ownerId)
    {
        $result = Plesk_Registry::getInstance()->api->webspaces_get_by_owner_id(["ownerId" => $ownerId]);
        return $result->domain->get->result;
    }
    protected function _getIps($params)
    {
        $params["addAddonDedicatedIPv4"] = false;
        $params["addAddonDedicatedIPv6"] = false;
        $ip = ["ipv4Address" => "", "ipv6Address" => ""];
        if (!empty($params["configoptions"])) {
            foreach ($params["configoptions"] as $addonTitle => $value) {
                if ("0" != $value) {
                    if (Plesk_Object_Ip::ADDON_NAME_IPV6 == $addonTitle) {
                        $params["addAddonDedicatedIPv6"] = true;
                    } else {
                        if (Plesk_Object_Ip::ADDON_NAME_IPV4 == $addonTitle) {
                            $params["addAddonDedicatedIPv4"] = true;
                        }
                    }
                }
            }
        }
        if (Plesk_Registry::getInstance()->api->isAdmin()) {
            switch ($params["configoption3"]) {
                case "IPv4 shared; IPv6 none":
                    $ip["ipv4Address"] = $params["addAddonDedicatedIPv4"] ? Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv4() : Plesk_Registry::getInstance()->manager->getSharedIpv4($params);
                    break;
                case "IPv4 none; IPv6 shared":
                    $ip["ipv6Address"] = $params["addAddonDedicatedIPv6"] ? Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv6() : Plesk_Registry::getInstance()->manager->getSharedIpv6($params);
                    break;
                case "IPv4 shared; IPv6 shared":
                    $ip["ipv4Address"] = $params["addAddonDedicatedIPv4"] ? Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv4() : Plesk_Registry::getInstance()->manager->getSharedIpv4($params);
                    $ip["ipv6Address"] = $params["addAddonDedicatedIPv6"] ? Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv6() : Plesk_Registry::getInstance()->manager->getSharedIpv6($params);
                    break;
                case "IPv4 dedicated; IPv6 none":
                    $ip["ipv4Address"] = Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv4();
                    break;
                case "IPv4 none; IPv6 dedicated":
                    $ip["ipv6Address"] = Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv6();
                    break;
                case "IPv4 shared; IPv6 dedicated":
                    $ip["ipv4Address"] = $params["addAddonDedicatedIPv4"] ? Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv4() : Plesk_Registry::getInstance()->manager->getSharedIpv4($params);
                    $ip["ipv6Address"] = Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv6();
                    break;
                case "IPv4 dedicated; IPv6 shared":
                    $ip["ipv4Address"] = Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv4();
                    $ip["ipv6Address"] = $params["addAddonDedicatedIPv6"] ? Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv6() : Plesk_Registry::getInstance()->manager->getSharedIpv6($params);
                    break;
                case "IPv4 dedicated; IPv6 dedicated":
                    $ip["ipv4Address"] = Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv4();
                    $ip["ipv6Address"] = Plesk_Registry::getInstance()->manager->getFreeDedicatedIpv6();
                    break;
            }
        } else {
            $ip["ipv4Address"] = $params["serverip"];
        }
        return $ip;
    }
    protected function _changeSubscriptionIp($params)
    {
        $webspace = Plesk_Registry::getInstance()->api->webspace_get_by_name(["domain" => $params["domain"]]);
        $ipDedicatedList = $this->_getIpList(Plesk_Object_Ip::DEDICATED);
        if (empty($ipDedicatedList)) {
            return NULL;
        }
        $oldIp[Plesk_Object_Ip::IPV4] = (int) $webspace->data->hosting->vrt_hst->ip_address;
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
    protected function _checkRestrictions($params)
    {
        $accountLimit = (int) Plesk_Config::get()->account_limit;
        if ($accountLimit <= 0) {
            return NULL;
        }
        $accountCount = Plesk_Utils::getAccountsCount($params["userid"]);
        if ($accountLimit < $accountCount) {
            throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_RESTRICTIONS_ACCOUNT_COUNT", ["ACCOUNT_LIMIT" => $accountLimit]));
        }
    }
    protected function _getServicePlans()
    {
        return [];
    }
    protected function _listAccounts($_listAccounts, $params)
    {
        return [];
    }
    protected function _getCustomers($params)
    {
        return [];
    }
    protected function _getCustomersByOwner($params)
    {
        return [];
    }
    protected function _getResellers($params)
    {
        return [];
    }
    protected function _getResellerByLogin($params)
    {
        return [];
    }
    protected function _getServerData($params)
    {
        throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_NO_METHOD_TO_API_VERSION", ["METHOD" => "getServerData", "API_VERSION" => Plesk_Registry::getInstance()->version]));
    }
}

?>