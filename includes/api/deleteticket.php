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
$result = select_query("tbltickets", "", ["id" => $ticketid]);
$data = mysql_fetch_array($result);
$ticketid = $data["id"];
if (!$ticketid) {
    $apiresults = ["result" => "error", "message" => "Ticket ID not found"];
} else {
    if (!function_exists("deleteTicket")) {
        require ROOTDIR . "/includes/ticketfunctions.php";
    }
    deleteTicket($ticketid);
    $apiresults = ["result" => "success"];
}

?>