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
    if (!function_exists("openNewTicket")) {
        require ROOTDIR . "/includes/ticketfunctions.php";
    }
    $useMarkdown = stringLiteralToBool(App::getFromRequest("markdown"));
    $from = [];
    $clientid = (int) App::getFromRequest("clientid");
    $contactid = (int) App::getFromRequest("contactid");
    $name = (int) App::getFromRequest("name");
    $email = (int) App::getFromRequest("email");
    $deptid = (int) App::getFromRequest("deptid");
    $subject = (int) App::getFromRequest("subject");
    $message = (int) App::getFromRequest("message");
    $priority = (int) App::getFromRequest("priority");
    $created = (int) App::getFromRequest("created");
    $serviceid = (int) App::getFromRequest("serviceid");
    $domainid = (int) App::getFromRequest("domainid");
    $customfields = (int) App::getFromRequest("customfields");
    if ($customfields) {
        $customfields = base64_decode($customfields);
        $customfields = safe_unserialize($customfields);
    }
    if (!is_array($customfields)) {
        $customfields = [];
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
        $from = ["name" => "", "email" => ""];
    } else {
        if (!$name || !$email) {
            $apiresults = ["result" => "error", "message" => "Name and email address are required if not a client"];
            return NULL;
        }
        $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$validEmail) {
            $apiresults = ["result" => "error", "message" => "Email Address Invalid"];
            return NULL;
        }
        $from = ["name" => $name, "email" => $email];
    }
    $result = select_query("tblticketdepartments", "", ["id" => $deptid]);
    $data = mysql_fetch_array($result);
    $deptid = $data["id"];
    if (!$deptid) {
        $apiresults = ["result" => "error", "message" => "Department ID not found"];
        return NULL;
    }
    if (!$subject) {
        $apiresults = ["result" => "error", "message" => "Subject is required"];
        return NULL;
    }
    if (!$message) {
        $apiresults = ["result" => "error", "message" => "Message is required"];
        return NULL;
    }
    if (!$priority || !in_array($priority, ["Low", "Medium", "High"])) {
        $priority = "Low";
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
        $apiresults = ["result" => "error", "message" => "Ticket creation date cannot be in the future"];
        return NULL;
    }
    if ($serviceid) {
        if (is_numeric($serviceid) || substr($serviceid, 0, 1) == "S") {
            $result = select_query("tblhosting", "id", ["id" => $serviceid, "userid" => $clientid]);
            $data = mysql_fetch_array($result);
            if (!$data["id"]) {
                $apiresults = ["result" => "error", "message" => "Service ID Not Found"];
                return NULL;
            }
            $serviceid = "S" . $data["id"];
        } else {
            $serviceid = substr($serviceid, 1);
            $result = select_query("tbldomains", "id", ["id" => $serviceid, "userid" => $clientid]);
            $data = mysql_fetch_array($result);
            if (!$data["id"]) {
                $apiresults = ["result" => "error", "message" => "Service ID Not Found"];
                return NULL;
            }
            $serviceid = "D" . $data["id"];
        }
    }
    if ($domainid) {
        $result = select_query("tbldomains", "id", ["id" => $domainid, "userid" => $clientid]);
        $data = mysql_fetch_array($result);
        if (!$data["id"]) {
            $apiresults = ["result" => "error", "message" => "Domain ID Not Found"];
            return NULL;
        }
        $serviceid = "D" . $data["id"];
    }
    $treatAsAdmin = $whmcs->getFromRequest("admin") ? true : false;
    $validationData = ["clientId" => $clientid, "contactId" => $contactid, "name" => $name, "email" => $email, "isAdmin" => $treatAsAdmin, "departmentId" => $deptid, "subject" => $subject, "message" => $message, "priority" => $priority, "relatedService" => $serviceid, "customfields" => $customfields];
    $ticketOpenValidateResults = run_hook("TicketOpenValidation", $validationData);
    if (is_array($ticketOpenValidateResults)) {
        $hookErrors = [];
        foreach ($ticketOpenValidateResults as $hookReturn) {
            if (is_string($hookReturn) && ($hookReturn = trim($hookReturn))) {
                $hookErrors[] = $hookReturn;
            }
        }
        if ($hookErrors) {
            $apiresults = ["result" => "error", "message" => implode(". ", $hookErrors)];
            return NULL;
        }
    }
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
    $noemail = "";
    $ticketdata = openNewTicket($clientid, $contactid, $deptid, $subject, $message, $priority, $attachments, $from, $serviceid, $cc, $noemail, $treatAsAdmin, $useMarkdown, $created);
    if ($customfields) {
        saveCustomFields($ticketdata["ID"], $customfields, "support", true);
    }
    $apiresults = ["result" => "success", "id" => $ticketdata["ID"], "tid" => $ticketdata["TID"], "c" => $ticketdata["C"]];
}
exit("This file cannot be accessed directly");

?>