<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("ADMINAREA", true);
require_once dirname(__DIR__) . "/init.php";
App::redirectToRoutePath("admin-setup-payments-tax-index");

?>