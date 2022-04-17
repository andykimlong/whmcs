<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Manager_V1632 extends Plesk_Manager_V1630
{
    protected function _processAddons($_processAddons, $params)
    {
        self::_processAddons($params);
    }
    protected function _addWebspace($params)
    {
        self::_addWebspace($params);
    }
    protected function _getSharedIpv4($params)
    {
        return $this->_getIp($params);
    }
    protected function _getSharedIpv6($params)
    {
        return $this->_getIp($params, Plesk_Object_Ip::IPV6);
    }
    protected function _getFreeDedicatedIpv4()
    {
        return $this->_getFreeDedicatedIp();
    }
    protected function _getFreeDedicatedIpv6()
    {
        return $this->_getFreeDedicatedIp(Plesk_Object_Ip::IPV6);
    }
    protected function _getIpList($_getIpList = Plesk_Object_Ip::SHARED, $type = NULL, $version)
    {
        $ipList = [];
        if (is_null($result)) {
            $result = Plesk_Registry::getInstance()->api->ip_get();
        }
        foreach ($result->ip->get->result->addresses->ip_info as $item) {
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
    protected function _getFreeDedicatedIp($version = Plesk_Object_Ip::IPV4)
    {
        $ipListUse = [];
        $ipListFree = [];
        $ipList = $this->_getIpList(Plesk_Object_Ip::DEDICATED, $version);
        if (is_null($domains)) {
            $domains = Plesk_Registry::getInstance()->api->webspaces_get();
        }
        foreach ($domains->xpath("//webspace/get/result") as $item) {
            try {
                $this->_checkErrors($item);
                foreach ($item->data->hosting->vrt_hst->ip_address as $ip) {
                    $ipListUse[(int) $ip] = (int) $ip;
                }
            } catch (Exception $e) {
                if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                    throw $e;
                }
            }
        }
        foreach ($ipList as $ip) {
            if (!in_array($ip, $ipListUse)) {
                $ipListFree[] = $ip;
            }
        }
        $freeIp = reset($ipListFree);
        if (empty($freeIp)) {
            throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_NO_FREE_DEDICATED_IPTYPE", ["TYPE" => Plesk_Object_Ip::IPV6 == $version ? "IPv6" : "IPv4"]));
        }
        return $freeIp;
    }
    protected function _getWebspacesUsage($params)
    {
        return self::_getWebspacesUsage($params);
    }
    protected function _changeSubscriptionIp($params)
    {
        $webspace = Plesk_Registry::getInstance()->api->webspace_get_by_name(["domain" => $params["domain"]]);
        $ipDedicatedList = $this->_getIpList(Plesk_Object_Ip::DEDICATED);
        foreach ($webspace->webspace->get->result->data->hosting->vrt_hst->ip_address as $ip) {
            $ip = (int) $ip;
            $oldIp[$this->_isIpv6($ip) ? Plesk_Object_Ip::IPV6 : Plesk_Object_Ip::IPV4] = $ip;
        }
        $oldIp[Plesk_Object_Ip::IPV4] ? exit : "";
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
                $webSpaceDataArray["owner-id"] ? exit : NULL;
            }
        }
        return $response;
    }
}

?>