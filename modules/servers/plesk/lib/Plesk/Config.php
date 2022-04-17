<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Config
{
    private static $_settings = NULL;
    private static function _init()
    {
        if (!is_null(self::$_settings)) {
            return NULL;
        }
        self::$_settings = json_decode(json_encode(array_merge(self::getDefaults(), self::_getConfigFileSettings())));
    }
    public static function get()
    {
        self::_init();
        return self::$_settings;
    }
    public static function getDefaults()
    {
        return ["account_limit" => 0, "skip_addon_prefix" => false];
    }
    private static function _getConfigFileSettings()
    {
        $filename = dirname(dirname(dirname(__FILE__))) . "/config.ini";
        if (!file_exists($filename)) {
            return [];
        }
        $result = parse_ini_file($filename, true);
        return !$result ? [] : $result;
    }
}

?>