<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    if (!function_exists("getAdminName")) {
        require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "adminfunctions.php";
    }
    if (!function_exists("AddNote")) {
        require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "ticketfunctions.php";
    }
    $ticketnum = App::get_req_var("ticketnum");
    $ticketid = (int) App::get_req_var("ticketid");
    $message = App::getFromRequest("message");
    $useMarkdown = stringLiteralToBool(App::get_req_var("markdown"));
    $created = App::getFromRequest("created");
    $ticketData = WHMCS\Database\Capsule::table("tbltickets");
    if ($ticketnum) {
        $ticketData->where("tid", $ticketnum);
    } else {
        $ticketData->where("id", $ticketid);
    }
    $data = $ticketData->first(["id", "tid", "title"]);
    if (!$data) {
        $apiresults = ["result" => "error", "message" => "Ticket ID not found"];
        return NULL;
    }
    $ticketid = $data->id;
    if (!$message) {
        $apiresults = ["result" => "error", "message" => "Message is required"];
        return NULL;
    }
    $timeDateNow = false;
    if (!$created) {
        $created = WHMCS\Carbon::now();
    } else {
        try {
            $created = WHMCS\Carbon::parse($created);
            $timeDateNow = WHMCS\Carbon::now();
        } catch (Exception $e) {
            $apiresults = ["result" => "error", "message" => "Invalid Date Format"];
            return NULL;
        }
    }
    if ($timeDateNow && !$created->lte($timeDateNow)) {
        $apiresults = ["result" => "error", "message" => "Note creation date cannot be in the future"];
        return NULL;
    }
    AddNote($ticketid, $message, $useMarkdown, $created);
    $mentionedAdminIds = WHMCS\Mentions\Mentions::getIdsForMentions($message);
    $changes["Note"] = ["new" => $message, "editor" => "markdown"];
    $changes["Who"] = getAdminName(WHMCS\Session::get("adminid"));
    WHMCS\Tickets::notifyTicketChanges($ticketid, $changes, [], $mentionedAdminIds);
    if ($mentionedAdminIds) {
        $ticketTid = $ticket->tid;
        $ticketTitle = $ticket->title;
        WHMCS\Mentions\Mentions::sendNotification("ticket", $ticketid, $message, $mentionedAdminIds, AdminLang::trans("mention.ticket") . " #" . $ticketTid . " - " . $ticketTitle);
    }
    $apiresults = ["result" => "success"];
}
exit("This file cannot be accessed directly");

?>