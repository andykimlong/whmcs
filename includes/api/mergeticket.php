<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $masterTicketId = (int) App::getFromRequest("ticketid");
    $mergeTicketIds = array_filter(explode(",", App::getFromRequest("mergeticketids")));
    $newSubject = App::getFromRequest("newsubject");
    if (!$masterTicketId) {
        $apiresults = ["result" => "error", "message" => "Ticket ID Required"];
        return NULL;
    }
    try {
        $masterTicket = WHMCS\Support\Ticket::where("merged_ticket_id", 0)->findOrFail($masterTicketId);
        if (count($mergeTicketIds) === 0) {
            $apiresults = ["result" => "error", "message" => "Merge Ticket IDs Required"];
            return NULL;
        }
        $invalidMergeTicketIds = [];
        foreach ($mergeTicketIds as $mergeTicketId) {
            try {
                $mergeTicket = WHMCS\Support\Ticket::findOrFail($mergeTicketId);
            } catch (Exception $e) {
                $invalidMergeTicketIds[] = $mergeTicketId;
            }
        }
        if (0 < count($invalidMergeTicketIds)) {
            $apiresults = ["result" => "error", "message" => "Invalid Merge Ticket IDs: " . implode(", ", $invalidMergeTicketIds)];
            return NULL;
        }
        if ($newSubject) {
            $masterTicket->title = $newSubject;
            $masterTicket->save();
        }
        $masterTicket->mergeOtherTicketsInToThis($mergeTicketIds);
        $apiresults = ["result" => "success", "ticketid" => $masterTicketId];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Ticket ID Invalid"];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>