<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $ticketId = App::getFromRequest("ticketid");
    $delete = (int) App::getFromRequest("delete");
    if (!$ticketId) {
        $apiresults = ["result" => "error", "message" => "Ticket ID Required"];
        return NULL;
    }
    $ticket = WHMCS\Database\Capsule::table("tbltickets")->find($ticketId);
    if (!$ticket) {
        $apiresults = ["result" => "error", "message" => "Ticket ID Not Found"];
        return NULL;
    }
    if ($ticket->userid) {
        $apiresults = ["result" => "error", "message" => "A Client Cannot Be Blocked"];
        return NULL;
    }
    $email = $ticket->email;
    if (!$email) {
        $apiresults = ["result" => "error", "message" => "Missing Email Address"];
        return NULL;
    }
    $blockedAlready = WHMCS\Database\Capsule::table("tblticketspamfilters")->where("type", "sender")->where("content", $email)->count();
    if ($blockedAlready === 0) {
        WHMCS\Database\Capsule::table("tblticketspamfilters")->insert(["type" => "sender", "content" => $email]);
    }
    $apiresults = ["result" => "success", "deleted" => false];
    if ($delete) {
        if (!function_exists("deleteTicket")) {
            require ROOTDIR . "/includes/ticketfunctions.php";
        }
        try {
            deleteTicket($ticketId);
            $apiresults["deleted"] = true;
        } catch (Exception $e) {
            $apiresults = ["result" => "error", "message" => $e->getMessage()];
            return NULL;
        }
    }
}
exit("This file cannot be accessed directly");

?>