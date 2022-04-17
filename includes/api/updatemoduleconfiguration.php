<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $moduleType = App::getFromRequest("moduleType");
    $moduleName = App::getFromRequest("moduleName");
    $parameters = App::getFromRequest("parameters");
    $supportedModuleTypes = ["gateway", "registrar", "addon", "fraud"];
    if (!in_array($moduleType, $supportedModuleTypes)) {
        $apiresults = ["result" => "error", "message" => "Invalid module type provided. Supported module types include: " . implode(", ", $supportedModuleTypes)];
        return NULL;
    }
    $moduleClassName = "\\WHMCS\\Module\\" . ucfirst($moduleType);
    $moduleInterface = new $moduleClassName();
    if (!in_array($moduleName, $moduleInterface->getList())) {
        $apiresults = ["result" => "error", "message" => "Invalid module name provided."];
        return NULL;
    }
    $moduleInterface->load($moduleName);
    try {
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $moduleInterface->updateConfiguration($parameters);
        $apiresults = ["result" => "success"];
    } catch (WHMCS\Exception\Module\NotImplemented $e) {
        $apiresults = ["result" => "error", "message" => "Module configuration update not supported by module type."];
        return NULL;
    } catch (WHMCS\Exception\Module\NotActivated $e) {
        $apiresults = ["result" => "error", "message" => "Module Configuration Update Failed: " . $e->getMessage()];
        return NULL;
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "An unexpected error occurred: " . $e->getMessage()];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>