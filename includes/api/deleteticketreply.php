<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    if (!function_exists("deleteTicket")) {
        require ROOTDIR . "/includes/ticketfunctions.php";
    }
    $ticketId = App::getFromRequest("ticketid");
    $replyId = App::getFromRequest("replyid");
    if (!$ticketId) {
        $apiresults = ["result" => "error", "message" => "Ticket ID Required"];
        return NULL;
    }
    if (!$replyId) {
        $apiresults = ["result" => "error", "message" => "Reply ID Required"];
        return NULL;
    }
    $ticket = WHMCS\Database\Capsule::table("tbltickets")->find($ticketId);
    if (!$ticket) {
        $apiresults = ["result" => "error", "message" => "Ticket ID Not Found"];
        return NULL;
    }
    $reply = WHMCS\Database\Capsule::table("tblticketreplies")->where("tid", $ticketId)->find($replyId);
    if (!$reply) {
        $apiresults = ["result" => "error", "message" => "Reply ID Not Found"];
        return NULL;
    }
    try {
        deleteTicket($ticketId, $replyId);
        $apiresults = ["result" => "success"];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => $e->getMessage()];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>