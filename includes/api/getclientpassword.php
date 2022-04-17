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
if ($_POST["userid"]) {
    $result = select_query("tblclients", "", ["id" => $_POST["userid"]]);
} else {
    $result = select_query("tblclients", "", ["email" => $_POST["email"]]);
}
$data = mysql_fetch_array($result);
if ($data["id"]) {
    $password = $data["password"];
    $apiresults = ["result" => "success", "password" => $password];
} else {
    $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
}

?>