<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Manager_V1670 extends Plesk_Manager_V1660
{
    protected function _generateCSR($params)
    {
        return self::_generateCSR($params);
    }
    protected function _installSsl($params)
    {
        return self::_installSsl($params);
    }
    protected function _getLicenseKey($params)
    {
        $data = Plesk_Registry::getInstance()->api->get_license_key();
        $data = $data->xpath("//server/get/result");
        return $data[0];
    }
}

?>