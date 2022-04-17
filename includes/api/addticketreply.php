<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    if (!function_exists("saveCustomFields")) {
        require ROOTDIR . "/includes/customfieldfunctions.php";
    }
    if (!function_exists("AddReply")) {
        require ROOTDIR . "/includes/ticketfunctions.php";
    }
    $useMarkdown = stringLiteralToBool(App::get_req_var("markdown"));
    $from = "";
    $ticketData = WHMCS\Support\Ticket::find($ticketid);
    if (!$ticketData) {
        $apiresults = ["result" => "error", "message" => "Ticket ID Not Found"];
        return NULL;
    }
    if ($clientid) {
        $result = select_query("tblclients", "id", ["id" => $clientid]);
        $data = mysql_fetch_array($result);
        if (!$data["id"]) {
            $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
            return NULL;
        }
        if ($contactid) {
            $result = select_query("tblcontacts", "id", ["id" => $contactid, "userid" => $clientid]);
            $data = mysql_fetch_array($result);
            if (!$data["id"]) {
                $apiresults = ["result" => "error", "message" => "Contact ID Not Found"];
                return NULL;
            }
        }
    } else {
        if ((!$name || !$email) && !$adminusername) {
            $apiresults = ["result" => "error", "message" => "Name and email address are required if not a client"];
            return NULL;
        }
        $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$validEmail && !$adminusername) {
            $apiresults = ["result" => "error", "message" => "Email Address Invalid"];
            return NULL;
        }
        $from = ["name" => $name, "email" => $email];
    }
    if (!$message) {
        $apiresults = ["result" => "error", "message" => "Message is required"];
        return NULL;
    }
    if ($status && $status !== $ticketData->status) {
        $validStatus = false;
        $ticketStatuses = WHMCS\Database\Capsule::table("tblticketstatuses")->select(["title"])->get();
        foreach ($ticketStatuses as $ticketStatus) {
            if (strtolower($ticketStatus->title) === strtolower($status)) {
                $status = $ticketStatus->title;
                $validStatus = true;
                if (!$validStatus) {
                    $apiresults = ["result" => "error", "message" => "Invalid Ticket Status"];
                    return NULL;
                }
            }
        }
    }
    $adminusername = App::getFromRequest("adminusername");
    if ($attachment = App::getFromRequest("attachments")) {
        if (!is_array($attachment)) {
            $attachment = json_decode(base64_decode($attachment), true);
        }
        if (is_array($attachment)) {
            $attachments = saveTicketAttachmentsFromApiCall($attachment);
        }
    } else {
        $attachments = uploadTicketAttachments();
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
        $apiresults = ["result" => "error", "message" => "Reply creation date cannot be in the future"];
        return NULL;
    }
    AddReply($ticketData->id, $clientid, $contactid, $message, $adminusername, $attachments, $from, $status, $noemail, true, $useMarkdown, [], $created);
    if ($customfields) {
        $customfields = base64_decode($customfields);
        $customfields = safe_unserialize($customfields);
        saveCustomFields($ticketid, $customfields, "support", true);
    }
    $apiresults = ["result" => "success"];
}
exit("This file cannot be accessed directly");

?>