<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $moduleType = $whmcs->getFromRequest("moduleType");
    $moduleName = $whmcs->getFromRequest("moduleName");
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
        $configurationParams = $moduleInterface->getConfiguration();
        $paramsToReturn = [];
        if (is_array($configurationParams)) {
            foreach ($configurationParams as $key => $values) {
                if ($values["Type"] == "System") {
                    if ($key == "FriendlyName") {
                        $values["Type"] = "text";
                    }
                }
                $paramsToReturn[] = ["name" => $key, "displayName" => $values["FriendlyName"] ?: $key, "fieldType" => $values["Type"]];
            }
        }
        $apiresults = ["result" => "success", "parameters" => $paramsToReturn];
    } catch (WHMCS\Exception\Module\NotImplemented $e) {
        $apiresults = ["result" => "error", "message" => "Get module configuration parameters not supported by module type."];
        return NULL;
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "An unexpected error occurred: " . $e->getMessage()];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>