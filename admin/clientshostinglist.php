<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("List Services");
$listType = App::getFromRequest("listtype");
switch ($listType) {
    case "hostingaccount":
        $path = "shared";
        break;
    case "reselleraccount":
        $path = "reseller";
        break;
    case "server":
    case "other":
        $path = $listType;
        break;
    default:
        $path = "index";
        App::redirectToRoutePath("admin-services-" . $path);
}

?>