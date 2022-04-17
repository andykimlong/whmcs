<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Registry
{
    private $_instances = [];
    private static $_instance = NULL;
    public static function getInstance(Plesk_Registry $getInstance)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new $this();
        }
        return self::$_instance;
    }
    public function __get($name)
    {
        if (isset($this->_instances[$name])) {
            return $this->_instances[$name];
        }
        throw new Exception("There is no object \"" . $name . "\" in the registry.");
    }
    public function __set($name, $value)
    {
        $this->_instances[$name] = $value;
    }
    public function __isset($name)
    {
        return isset($this->_instances[$name]);
    }
}

?>