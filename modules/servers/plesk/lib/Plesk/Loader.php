<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Loader
{
    private static $initComplete = false;
    public static function init($params)
    {
        if (self::$initComplete) {
            return NULL;
        }
        spl_autoload_register(["Plesk_Loader", "autoload"]);
        $port = $params["serveraccesshash"] ? $params["serveraccesshash"] : ($params["serversecure"] ? 8443 : 8880);
        list($caller) = debug_backtrace(false);
        Plesk_Registry::getInstance()->actionName = $caller["function"];
        Plesk_Registry::getInstance()->translator = new Plesk_Translate();
        Plesk_Registry::getInstance()->api = new Plesk_Api($params["serverusername"], $params["serverpassword"], $params["serverhostname"], $port, $params["serversecure"]);
        $manager = new Plesk_Manager_V1000();
        foreach ($manager->getSupportedApiVersions() as $version) {
            $managerClassName = "Plesk_Manager_V" . str_replace(".", "", $version);
            if (class_exists($managerClassName)) {
                Plesk_Registry::getInstance()->manager = new $managerClassName();
                if (!isset(Plesk_Registry::getInstance()->manager)) {
                    throw new Exception(Plesk_Registry::getInstance()->translator->translate("ERROR_NO_APPROPRIATE_MANAGER"));
                }
                self::$initComplete = true;
            }
        }
    }
    public static function autoload($className)
    {
        $filePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . str_replace("_", DIRECTORY_SEPARATOR, $className) . ".php";
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
}

?>