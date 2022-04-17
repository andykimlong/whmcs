<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("ADMINAREA", true);
require "../init.php";
$auth = new WHMCS\Auth();
if ($auth->logout()) {
    redir("logout=1", "login.php");
}
redir("", "login.php");

?>