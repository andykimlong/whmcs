<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require "init.php";
$redirectUrl = routePath("subscription-manage");
if (strpos($redirectUrl, "?") === false) {
    $redirectUrl .= "?";
} else {
    $redirectUrl .= "&";
}
$redirectUrl .= "action=optout&email=" . App::getFromRequest("email") . "&key=" . App::getFromRequest("key");
header("Location: " . $redirectUrl);

?>