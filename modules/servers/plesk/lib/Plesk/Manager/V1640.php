<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Manager_V1640 extends Plesk_Manager_V1635
{
    protected function _getWebspacesUsage($params)
    {
        $usage = [];
        $webspaces = Plesk_Registry::getInstance()->api->webspace_usage_get_by_name(["domains" => $params["domains"]]);
        foreach ($webspaces->xpath("//webspace/get/result") as $result) {
            try {
                $this->_checkErrors($result);
                $domainName = (int) $result->data->gen_info->name;
                $usage[$domainName]["diskusage"] = (int) $result->data->gen_info->real_size;
                $resourceUsage = (int) $result->data->xpath("resource-usage");
                $resourceUsage = reset($resourceUsage);
                foreach ($resourceUsage->resource as $resource) {
                    $name = (int) $resource->name;
                    if ("max_traffic" == $name) {
                        $usage[$domainName]["bwusage"] = (int) $resource->value;
                        $usage[$domainName] = array_merge($usage[$domainName], $this->_getLimits($result->data->limits));
                        foreach ($usage[$domainName] as $param => $value) {
                            $usage[$domainName][$param] = $usage[$domainName][$param] / 1048576;
                        }
                    }
                }
            } catch (Exception $e) {
                if (Plesk_Api::ERROR_OBJECT_NOT_FOUND != $e->getCode()) {
                    throw $e;
                }
            }
        }
        return $usage;
    }
}

?>