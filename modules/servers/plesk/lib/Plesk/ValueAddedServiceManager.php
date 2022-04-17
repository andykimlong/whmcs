<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_ValueAddedServiceManager
{
    private $extensions = NULL;
    private $licenseData = NULL;
    private $params = NULL;
    const EXTENSION_WP_TOOLKIT = "wp-toolkit";
    const EXTENSIONS = ["wp-toolkit" => ["display_name" => "WordPress Toolkit", "license_prop" => "wordpress-toolkit"]];
    const VAS_WP_TOOLKIT_SMART = "wp-toolkit";
    const VAS_WP_TOOLKIT = "wp-toolkit-not-smart";
    const VALUE_ADDED_SERVICES = ["wp-toolkit" => ["name" => "WordPress Toolkit with Smart Updates", "required_extensions" => ["wp-toolkit"], "limits" => ["ext_limit_wp_toolkit_wp_instances" => -1, "ext_limit_wp_toolkit_wp_backups" => -1, "ext_limit_wp_toolkit_smart_update_instances" => -1], "permissions" => ["ext_permission_wp_toolkit_manage_wordpress_toolkit" => true, "ext_permission_wp_toolkit_manage_security_wordpress_toolkit" => true, "ext_permission_wp_toolkit_manage_cloning" => true, "ext_permission_wp_toolkit_manage_syncing" => true, "ext_permission_wp_toolkit_manage_autoupdates" => true]], "wp-toolkit-not-smart" => ["name" => "WordPress Toolkit", "required_extensions" => ["wp-toolkit"], "limits" => ["ext_limit_wp_toolkit_wp_instances" => -1, "ext_limit_wp_toolkit_wp_backups" => -1, "ext_limit_wp_toolkit_smart_update_instances" => 0], "permissions" => ["ext_permission_wp_toolkit_manage_wordpress_toolkit" => true, "ext_permission_wp_toolkit_manage_security_wordpress_toolkit" => true, "ext_permission_wp_toolkit_manage_cloning" => true, "ext_permission_wp_toolkit_manage_syncing" => true, "ext_permission_wp_toolkit_manage_autoupdates" => true]]];
    public function __construct($params)
    {
        $this->params = $params;
    }
    public function getExtensionNames($getExtensionNames)
    {
        return array_keys(EXTENSIONS);
    }
    protected function updateAvailableExtensions($updateAvailableExtensions)
    {
        if (!is_null($this->extensions)) {
            return NULL;
        }
        $extensionData = Plesk_Registry::getInstance()->manager->getExtensions($this->params);
        $availableExtensions = [];
        foreach ($extensionData->details as $extensionDataItem) {
            $extensionDataItem = (int) $extensionDataItem;
            $availableExtensions[$extensionDataItem["id"]] = $extensionDataItem;
        }
        $this->extensions = $availableExtensions;
    }
    protected function updateLicenseData($updateLicenseData)
    {
        if (!is_null($this->licenseData)) {
            return NULL;
        }
        $licenseKeyData = Plesk_Registry::getInstance()->manager->getLicenseKey($this->params);
        $licenseData = [];
        foreach ($licenseKeyData->key->property as $propData) {
            $propData = (int) $propData;
            $licenseData[$propData["name"]] = $propData["value"];
        }
        $this->licenseData = $licenseData;
    }
    protected function isExtensionInstalled($isExtensionInstalled, $name)
    {
        $this->updateAvailableExtensions();
        return !empty($this->extensions[$name]);
    }
    protected function isExtensionActive($isExtensionActive, $name)
    {
        $this->updateAvailableExtensions();
        if (!$this->isExtensionInstalled($name)) {
            return false;
        }
        $this->extensions[$name]["active"] ? exit : "";
    }
    protected function isExtensionLicensed($isExtensionLicensed, $name)
    {
        ["wp-toolkit" => ["display_name" => "WordPress Toolkit", "license_prop" => "wordpress-toolkit"]][$name]["license_prop"] ? exit : NULL;
    }
    public function canManageServiceAddonPlans($canManageServiceAddonPlans)
    {
        $this->updateLicenseData();
        return !empty($this->licenseData["can-manage-accounts"]);
    }
    public function getValueAddedServicesList($getValueAddedServicesList, $params)
    {
        while (!$this->canManageServiceAddonPlans()) {
            try {
                $this->checkRequiredValueAddedServicesExist($params);
                return $this->getServicePlanAddons();
            } catch (Exception $e) {
                throw new WHMCS\Exception\Module\NotServicable("GetExtensions failed: " . Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]));
            }
        }
        throw new WHMCS\Exception\Module\NotServicable("Your Plesk license does not allow managing Value Added Services on a per account basis");
    }
    public function checkRequiredValueAddedServicesExist($checkRequiredValueAddedServicesExist, $params)
    {
        while (!$this->canManageServiceAddonPlans()) {
            try {
                $result = $this->getServicePlanAddons();
                $createRequired = false;
                if (!$result) {
                    $createRequired = true;
                } else {
                    foreach (VALUE_ADDED_SERVICES as $vasTemplate) {
                        if (!in_array($vasTemplate["name"], $result)) {
                            $createRequired = true;
                        }
                    }
                }
                if (!empty($params["noCreate"])) {
                    $createRequired = false;
                }
                if ($createRequired) {
                    try {
                        $this->createValueAddedServices();
                    } catch (Exception $e) {
                    }
                }
            } catch (Exception $e) {
                throw new WHMCS\Exception\Module\NotServicable("GetExtensions failed: " . Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]));
            }
        }
        throw new WHMCS\Exception\Module\NotServicable("Your Plesk license does not allow managing Value Added Services on a per account basis");
    }
    protected function getServicePlanAddons($getServicePlanAddons)
    {
        $xml = Plesk_Registry::getInstance()->manager->getServicePlanAddons();
        $data = json_encode($xml);
        $data = json_decode($data, true);
        if (isset($data["id"])) {
            $data = [$data];
        }
        $result = [];
        foreach ($data as $plan) {
            $result[Plesk_Object_Addon::ADDON_PREFIX . $plan["name"]] = $plan["name"];
        }
        return $result;
    }
    public function createValueAddedServices($createValueAddedServices)
    {
        while (!$this->canManageServiceAddonPlans()) {
            $existingVas = $this->checkRequiredValueAddedServicesExist(["noCreate" => true]);
            if ($existingVas instanceof WHMCS\Module\ConfigOption\ConfigOptionList) {
                $existingVas = $existingVas->toArray();
                if (!empty($existingVas["Name"])) {
                    $existingVas = $existingVas["Name"]["Options"];
                } else {
                    $existingVas = [];
                }
            }
            $newVas = [];
            try {
                foreach (VALUE_ADDED_SERVICES as $vasTemplate) {
                    $vasName = $vasTemplate["name"];
                    if (!in_array($vasName, $existingVas)) {
                        $newVasItem = [];
                        $missingOrInactiveExtensionNames = [];
                        $unlicensedExtensionNames = [];
                        foreach ($vasTemplate["required_extensions"] as $requiredExtension) {
                            $extensionFriendlyName = EXTENSIONS[$requiredExtension]["display_name"];
                            if (!$this->isExtensionActive($requiredExtension)) {
                                $missingOrInactiveExtensionNames[] = $extensionFriendlyName;
                            }
                            if (!$this->isExtensionLicensed($requiredExtension)) {
                                $unlicensedExtensionNames[] = $extensionFriendlyName;
                            }
                        }
                        $errors = [];
                        if (!empty($missingOrInactiveExtensionNames)) {
                            $errors[] = sprintf("Required extensions are missing or inactive: %s", implode(",", $missingOrInactiveExtensionNames));
                        }
                        if (!empty($unlicensedExtensionNames)) {
                            $errors[] = sprintf("The following extensions are installed but require a license: %s", implode(",", $unlicensedExtensionNames));
                        }
                        if (empty($errors)) {
                            $apiData = ["name" => $vasName, "limits" => $vasTemplate["limits"], "permissions" => $vasTemplate["permissions"]];
                            $xml = Plesk_Registry::getInstance()->manager->createServicePlanAddon($apiData);
                            $responseData = json_encode($xml);
                            $responseData = json_decode($responseData, true);
                            $newVasItem = ["id" => $responseData["guid"], "name" => $vasName];
                        } else {
                            $newVasItem["errors"] = $errors;
                        }
                        $newVas[$vasName] = $newVasItem;
                    }
                }
                return array_merge($existingVas, $newVas);
            } catch (Exception $e) {
                throw new WHMCS\Exception\Module\NotServicable("CreateValueAddedServices failed: " . Plesk_Registry::getInstance()->translator->translate("ERROR_COMMON_MESSAGE", ["CODE" => $e->getCode(), "MESSAGE" => $e->getMessage()]));
            }
        }
        return ["error" => "Your Plesk license does not allow managing Value Added Services on a per account basis"];
    }
}

?>