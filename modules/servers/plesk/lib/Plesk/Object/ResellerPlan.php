<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Object_ResellerPlan
{
    public $id = NULL;
    public $name = NULL;
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

?>