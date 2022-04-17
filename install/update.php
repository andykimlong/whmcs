<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_REQUEST["token"])) {
    define("ROOTDIR", dirname(__DIR__));
    define("INSTALLER_DIR", __DIR__);
    if (file_exists(ROOTDIR . DIRECTORY_SEPARATOR . "c3.php")) {
        include ROOTDIR . DIRECTORY_SEPARATOR . "c3.php";
    }
    ini_set("eaccelerator.enable", 0);
    ini_set("eaccelerator.optimizer", 0);
    require_once ROOTDIR . "/vendor/autoload.php";
    require_once ROOTDIR . "/includes/functions.php";
    require_once ROOTDIR . "/includes/dbfunctions.php";
    $debugErrorLevel = 22519;
    $errorLevel = basename(INSTALLER_DIR) == "install2" ? $debugErrorLevel : 0;
    $errMgmt = WHMCS\Utility\ErrorManagement::boot();
    if (empty($errorLevel)) {
        $errMgmt::disableIniDisplayErrors();
    } else {
        $errMgmt::enableIniDisplayErrors();
    }
    $errMgmt::setErrorReportingLevel($errorLevel);
    set_time_limit(0);
    $runtimeStorage = new WHMCS\Config\RuntimeStorage();
    $runtimeStorage->errorManagement = $errMgmt;
    WHMCS\Utility\Bootstrap\Installer::boot($runtimeStorage);
    $errMgmt->loadApplicationHandlers()->loadDeferredHandlers();
    try {
        DI::make("db")->getSqlVersion();
    } catch (Exception $e) {
        Log::pushHandler(WHMCS\Installer\LogServiceProvider::getUpdateLogHandler());
        Log::debug("Updater bootstrapped");
        $whmcsInstaller = new WHMCS\Installer\Installer(new WHMCS\Version\SemanticVersion(WHMCS\Installer\Installer::DEFAULT_VERSION), new WHMCS\Version\SemanticVersion(WHMCS\Application::FILES_VERSION));
        $updaterUpdateToken = WHMCS\Config\Setting::getValue("UpdaterUpdateToken");
        $currentVersion = $whmcsInstaller->getVersion()->getCanonical();
        $updateVersion = $whmcsInstaller->getLatestVersion()->getCanonical();
        logActivity(sprintf("An upgrade from %s to %s will be attempted.", $currentVersion, $updateVersion));
        try {
            if ($whmcsInstaller->isUpToDate()) {
                throw new Exception("Files and database are already up to date");
            }
            if (!(0 < strlen($updaterUpdateToken) && $updaterUpdateToken == $_REQUEST["token"])) {
                throw new Exception("Invalid token");
            }
            $whmcsInstaller->runUpgrades();
            if (basename(INSTALLER_DIR) == "install") {
                try {
                    $file = new WHMCS\Utility\File();
                    $file->recursiveDelete(INSTALLER_DIR, [], true);
                } catch (Exception $e) {
                    throw new Exception("Database update completed successfully but was unable to remove the install directory post completion");
                }
            }
            $updater = new WHMCS\Installer\Update\Updater();
            $updater->disableAutoUpdateMaintenanceMsg();
            $updateCount = (int) WHMCS\Config\Setting::getValue("AutoUpdateCountSuccess");
            $updateCount += 1;
            WHMCS\Config\Setting::setValue("AutoUpdateCountSuccess", $updateCount);
            logActivity(sprintf("Update from %s to %s completed successfully.", $currentVersion, $updateVersion));
            $response = ["success" => true];
        } catch (Exception $e) {
            $response = ["success" => false, "errorMessage" => $e->getMessage()];
            logActivity("Update Failed: " . $e->getMessage());
            WHMCS\Config\Setting::setValue("UpdaterUpdateToken", "");
            echo json_encode($response);
        }
    }
}
header("Location: install.php");
exit;

?>