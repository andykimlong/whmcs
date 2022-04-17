<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$installedVersion = App::getVersion();
$versionOutput = ["version" => $installedVersion->getCasual(), "canonicalversion" => $installedVersion->getCanonical()];
$apiresults = ["result" => "success", "whmcs" => $versionOutput];

?>