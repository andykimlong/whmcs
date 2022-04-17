<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $registrars = [];
    try {
        $activeRegistrars = new WHMCS\Module\Registrar();
        foreach ($activeRegistrars->getActiveModules() as $registrar) {
            if ($activeRegistrars->load($registrar)) {
                $registrars[] = ["module" => $activeRegistrars->getLoadedModule(), "display_name" => $activeRegistrars->getDisplayName()];
            }
        }
        if (empty($registrars)) {
            $apiresults = ["status" => "error", "message" => "No Active Registrars Found"];
            return NULL;
        }
        $apiresults = ["status" => "success", "registrars" => $registrars];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => $e->getMessage()];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>