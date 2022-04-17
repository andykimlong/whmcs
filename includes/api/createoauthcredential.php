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
$name = $whmcs->getFromRequest("name");
$description = $whmcs->getFromRequest("description");
$logoUri = $whmcs->getFromRequest("logoUri");
$redirectUri = $whmcs->getFromRequest("redirectUri");
$scope = $whmcs->getFromRequest("scope");
$grantType = $whmcs->getFromRequest("grantType");
$serviceId = (int) $whmcs->getFromRequest("serviceId");
$serviceObj = WHMCS\Service\Service::find($serviceId);
$serviceObj->client ? exit : NULL;

?>