<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    if (!function_exists("getClientsDetails")) {
        require ROOTDIR . "/includes/clientfunctions.php";
    }
    if (!function_exists("updateInvoiceTotal")) {
        require ROOTDIR . "/includes/invoicefunctions.php";
    }
    $sendInvoice = App::get_req_var("sendinvoice");
    $paymentMethod = App::get_req_var("paymentmethod");
    if (!$paymentMethod) {
        $paymentMethod = NULL;
    }
    $status = App::get_req_var("status");
    $createAsDraft = (int) App::get_req_var("draft");
    $invoiceStatuses = WHMCS\Invoices::getInvoiceStatusValues();
    $defaultStatus = "Unpaid";
    $doprocesspaid = false;
    $result = select_query("tblclients", "id", ["id" => $_POST["userid"]]);
    $data = mysql_fetch_array($result);
    if (!$data["id"]) {
        $apiresults = ["result" => "error", "message" => "Client ID Not Found"];
        return NULL;
    }
    if ($createAsDraft && $sendInvoice) {
        $apiresults = ["result" => "error", "message" => "Cannot create and send a draft invoice in a single API request. Please create and send separately."];
        return NULL;
    }
    $taxrate = $taxrate2 = NULL;
    if (App::isInRequest("taxrate")) {
        $taxrate2 = 0;
        $taxrate = App::getFromRequest("taxrate");
        if (App::isInRequest("taxrate2")) {
            $taxrate2 = App::getFromRequest("taxrate2");
        }
    }
    if ($createAsDraft) {
        $status = "Draft";
    } else {
        if (!in_array($status, $invoiceStatuses)) {
            $status = $defaultStatus;
        }
    }
    $dateCreated = App::getFromRequest("date");
    if ($dateCreated) {
        $format = "Y-m-d";
        try {
            if (!stristr($dateCreated, "-")) {
                $format = "Ymd";
            }
            $dateCreated = WHMCS\Carbon::createFromFormat($format, $dateCreated);
        } catch (Exception $e) {
            $dateCreated = NULL;
        }
    }
    $dueDate = App::getFromRequest("duedate");
    if ($dueDate) {
        $format = "Y-m-d";
        try {
            if (!stristr($dueDate, "-")) {
                $format = "Ymd";
            }
            $dueDate = WHMCS\Carbon::createFromFormat($format, $dueDate);
        } catch (Exception $e) {
            $dueDate = NULL;
        }
    }
    $invoice = WHMCS\Billing\Invoice::newInvoice(App::getFromRequest("userid"), $paymentMethod, $taxrate, $taxrate2);
    if ($dateCreated) {
        $invoice->dateCreated = $dateCreated;
    }
    if ($dueDate) {
        $invoice->dateDue = $dueDate;
    }
    if ($status != $invoice->status) {
        $invoice->status = $status;
    }
    $invoice->adminNotes = App::getFromRequest("notes");
    $invoice->save();
    $invoiceid = $invoice->id;
    logActivity("Created Invoice - Invoice ID: " . $invoiceid, $userid);
    $invoiceArr = ["source" => "api", "user" => WHMCS\Session::get("adminid"), "invoiceid" => $invoiceid, "status" => $status];
    foreach ($_POST as $k => $v) {
        if (substr($k, 0, 10) == "itemamount") {
            $counter = substr($k, 10);
            $description = $_POST["itemdescription" . $counter];
            $amount = $_POST["itemamount" . $counter];
            $taxed = $_POST["itemtaxed" . $counter];
            if ($description) {
                insert_query("tblinvoiceitems", ["invoiceid" => $invoiceid, "userid" => $userid, "description" => $description, "amount" => $amount, "taxed" => $taxed]);
            }
        }
    }
    updateInvoiceTotal($invoiceid);
    $invoice->runCreationHooks("api");
    if (isset($autoapplycredit) && $autoapplycredit) {
        $result = select_query("tblclients", "credit", ["id" => $userid]);
        $data = mysql_fetch_array($result);
        $credit = $data["credit"];
        $result = select_query("tblinvoices", "total", ["id" => $invoiceid]);
        $data = mysql_fetch_array($result);
        $total = $data["total"];
        if (0 < $credit) {
            if ($total <= $credit) {
                $creditleft = $credit - $total;
                $credit = $total;
                $doprocesspaid = true;
            } else {
                $creditleft = 0;
            }
            logActivity("Credit Automatically Applied at Invoice Creation - Invoice ID: " . $invoiceid . " - Amount: " . $credit, $userid);
            update_query("tblclients", ["credit" => $creditleft], ["id" => $userid]);
            update_query("tblinvoices", ["credit" => $credit], ["id" => $invoiceid]);
            insert_query("tblcredit", ["clientid" => $userid, "date" => "now()", "description" => "Credit Applied to Invoice #" . $invoiceid, "amount" => $credit * -1]);
            updateInvoiceTotal($invoiceid);
        }
    }
    if ($sendInvoice) {
        run_hook("InvoiceCreationPreEmail", $invoiceArr);
        $paymentType = $invoice->paymentGateway ? WHMCS\Module\GatewaySetting::getTypeFor((int) $invoice->paymentGateway) : NULL;
        $emailTemplate = "Invoice Created";
        if ($paymentType === WHMCS\Module\Gateway::GATEWAY_CREDIT_CARD) {
            $emailTemplate = "Credit Card Invoice Created";
        }
        $template = WHMCS\Mail\Template::where("name", $emailTemplate)->get()->first();
        sendMessage($template, $invoiceid);
    }
    if ($status != "Draft") {
        run_hook("InvoiceCreated", $invoiceArr);
    }
    if ($doprocesspaid) {
        processPaidInvoice($invoiceid);
    }
    $apiresults = ["result" => "success", "invoiceid" => $invoiceid, "status" => $status];
}
exit("This file cannot be accessed directly");

?>