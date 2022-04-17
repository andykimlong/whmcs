<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    if (!function_exists("RegSaveRegistrarLock")) {
        require ROOTDIR . "/includes/registrarfunctions.php";
    }
    $domain = WHMCS\Domain\Domain::find($domainid);
    if (!$domain) {
        $apiresults = ["result" => "error", "message" => "Domain ID Not Found"];
        return false;
    }
    try {
        $registrar = $domain->getRegistrarInterface();
        $values = $registrar->call("SaveRegistrarLock", ["lockenabled" => (int) $lockstatus ? "locked" : ""]);
    } catch (WHMCS\Exception\Module\InvalidConfiguration $e) {
        $values = ["error" => "An invalid configuration was detected with the registrar module"];
    } catch (Throwable $e) {
        $values = ["error" => $e->getMessage()];
        if (!is_array($values)) {
            $values = [];
        }
        if (empty($values["success"]) || !($values["success"] === "success" || $values["success"] === true)) {
            $apiresults = ["result" => "error", "message" => "Registrar Error Message", "error" => !empty($values["error"]) ? $values["error"] : "An unknown error occurred"];
            return false;
        }
        unset($values["success"]);
        $apiresults = array_merge(["result" => "success"], $values);
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>