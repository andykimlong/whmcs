<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require "init.php";
define("ROUTE_CONVERTED_LEGACY_ENDPOINT", true);
$_GET["rp"] = "/login";
$_SERVER["REQUEST_METHOD"] = "POST";
require_once __DIR__ . "/index.php";

?>