<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Manager_V1680 extends Plesk_Manager_V1670
{
    protected function _getServicePlanAddons()
    {
        $data = Plesk_Registry::getInstance()->api->service_plan_addon_get();
        $data = $data->xpath("//service-plan-addon/get/result");
        return $data;
    }
    protected function _createServicePlanAddon($data)
    {
        $data = Plesk_Registry::getInstance()->api->service_plan_addon_create($data);
        $data = $data->xpath("//service-plan-addon/add/result");
        return $data[0];
    }
}

?>