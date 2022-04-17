<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("View Clients Products/Services");
if ($userid && $hostingid) {
    redir("userid=" . $userid . "&id=" . $hostingid, "clientsservices.php");
}
if ($userid && $id) {
    redir("userid=" . $userid . "&id=" . $id, "clientsservices.php");
}
if ($id) {
    redir("id=" . $id, "clientsservices.php");
}
if ($userid) {
    redir("userid=" . $userid, "clientsservices.php");
}
redir("", "clientsservices.php");

?>