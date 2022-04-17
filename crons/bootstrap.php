<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . "functions.php";
if (!defined("PROXY_FILE")) {
    try {
        $path = getWhmcsInitPath();
        require_once $path;
    } catch (Exception $e) {
        echo cronsFormatOutput(getInitPathErrorMessage());
        exit(1);
    }
}

?>