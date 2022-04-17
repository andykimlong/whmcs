<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Manager_V1691 extends Plesk_Manager_V1680
{
    protected function _getExtensions($params)
    {
        $data = Plesk_Registry::getInstance()->api->get_extensions();
        $data = $data->xpath("//extension/get/result");
        return $data[0];
    }
}

?>