<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Manager_V1660 extends Plesk_Manager_V1640
{
    protected function _getAddAccountParams($params)
    {
        $result = self::_getAddAccountParams($params);
        $result["powerUser"] = "on" === $params["configoption4"] ? "true" : "false";
        return $result;
    }
    protected function _addAccount($params)
    {
        return self::_addAccount($params);
    }
}

?>