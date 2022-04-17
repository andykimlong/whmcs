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
    $publish = App::get_req_var("publish");
    $publishAndSendEmail = App::get_req_var("publishandsendemail");
    $invoiceId = (int) App::getFromRequest("invoiceid");
    $itemDescription = App::getFromRequest("itemdescription");
    $itemAmount = App::getFromRequest("itemamount");
    $itemTaxed = App::getFromRequest("itemtaxed");
    $newItemDescription = App::getFromRequest("newitemdescription");
    $newItemAmount = App::getFromRequest("newitemamount");
    $newItemTaxed = App::getFromRequest("newitemtaxed");
    $deleteLineIds = App::getFromRequest("deletelineids");
    $status = App::getFromRequest("status");
    try {
        $invoice = WHMCS\Billing\Invoice::findOrFail($invoiceId);
        $userId = $invoice->clientId;
        if (($publish || $publishAndSendEmail) && $invoice->status != "Draft") {
            $apiresults = ["result" => "error", "message" => "Invoice must be in Draft status to be published"];
            return NULL;
        }
        if ($status && !in_array($status, WHMCS\Invoices::getInvoiceStatusValues())) {
            $apiresults = ["result" => "error", "message" => "Invalid status " . $status];
            return NULL;
        }
        if ($itemDescription) {
            foreach ($itemDescription as $lineid => $description) {
                if (!array_key_exists($lineid, $itemAmount) || !array_key_exists($lineid, $itemTaxed)) {
                    $apiresults = ["result" => "error", "message" => "Missing Variables: itemdescription, itemamount and itemtaxed are required for each item being changed"];
                    return NULL;
                }
                $amount = $itemAmount[$lineid];
                $taxed = $itemTaxed[$lineid];
                $update = ["userid" => $userId, "description" => $description, "amount" => $amount, "taxed" => $taxed, "invoiceid" => $invoiceId];
                WHMCS\Database\Capsule::table("tblinvoiceitems")->where("id", "=", $lineid)->update($update);
            }
        }
        if ($newItemDescription) {
            $inserts = [];
            foreach ($newItemDescription as $k => $v) {
                $description = $v;
                $amount = $newItemAmount[$k];
                $taxed = $newItemTaxed[$k];
                $insert = ["invoiceid" => $invoiceId, "userid" => $userId, "description" => $description, "amount" => $amount, "taxed" => $taxed];
                $inserts[] = $insert;
            }
            if (0 < count($inserts)) {
                WHMCS\Database\Capsule::table("tblinvoiceitems")->insert($inserts);
            }
        }
        if ($deleteLineIds) {
            WHMCS\Database\Capsule::table("tblinvoiceitems")->where("invoiceid", "=", $invoiceId)->whereIn("id", $deleteLineIds)->delete();
        }
        $invoiceNum = App::getFromRequest("invoicenum");
        $date = App::getFromRequest("date");
        $dueDate = App::getFromRequest("duedate");
        $datePaid = App::getFromRequest("datepaid");
        $credit = App::getFromRequest("credit");
        $taxRate = App::getFromRequest("taxrate");
        $taxRate2 = App::getFromRequest("taxrate2");
        $paymentMethod = App::getFromRequest("paymentmethod");
        $notes = App::getFromRequest("notes");
        $changes = false;
        if ($invoiceNum) {
            $changes = true;
            $invoice->invoiceNumber = $invoiceNum;
        }
        if ($date) {
            $changes = true;
            $invoice->dateCreated = $date;
        }
        if ($dueDate) {
            $changes = true;
            $invoice->dateDue = $dueDate;
        }
        if ($datePaid) {
            $changes = true;
            $invoice->datePaid = $datePaid;
        }
        if ($credit) {
            $changes = true;
            $invoice->credit = $credit;
        }
        if ($taxRate) {
            $changes = true;
            $invoice->taxRate1 = $taxRate;
        }
        if ($taxRate2) {
            $changes = true;
            $invoice->taxRate2 = $taxRate2;
        }
        if ($status) {
            $changes = true;
            switch ($status) {
                case WHMCS\Billing\Invoice::STATUS_REFUNDED:
                    $invoice->setStatusRefunded();
                    break;
                case WHMCS\Billing\Invoice::STATUS_UNPAID:
                    $invoice->setStatusUnpaid();
                    break;
                case WHMCS\Billing\Invoice::STATUS_CANCELLED:
                    $invoice->setStatusCancelled();
                    break;
                case WHMCS\Billing\Invoice::STATUS_PAYMENT_PENDING:
                    $invoice->setStatusPending();
                    break;
                case WHMCS\Billing\Invoice::STATUS_PAID:
                case WHMCS\Billing\Invoice::STATUS_DRAFT:
                case WHMCS\Billing\Invoice::STATUS_COLLECTIONS:
                default:
                    $invoice->status = $status;
            }
        }
        if ($paymentMethod) {
            $changes = true;
            $invoice->paymentGateway = $paymentMethod;
        }
        if ($notes) {
            $changes = true;
            $invoice->adminNotes = $notes;
        }
        if ($changes) {
            $invoice->save();
        }
        updateInvoiceTotal($invoiceId);
        if ($publish || $publishAndSendEmail) {
            $invoiceArr = ["source" => "api", "user" => WHMCS\Session::get("adminid") ?: "system", "invoiceid" => $invoiceId, "status" => "Unpaid"];
            $invoice = WHMCS\Billing\Invoice::find($invoiceId);
            $invoice->status = "Unpaid";
            $invoice->dateCreated = WHMCS\Carbon::now();
            $invoice->save();
            $invoice->runCreationHooks("api");
            if (!$paymentMethod) {
                $paymentMethod = getClientsPaymentMethod($userId);
            }
            $paymentType = WHMCS\Module\GatewaySetting::getTypeFor($paymentMethod);
            updateInvoiceTotal($invoiceId);
            logActivity("Modified Invoice Options - Invoice ID: " . $invoiceId, $userId);
            if ($publishAndSendEmail) {
                run_hook("InvoiceCreationPreEmail", $invoiceArr);
                $emailName = "Invoice Created";
                if ($paymentType == WHMCS\Module\Gateway::GATEWAY_CREDIT_CARD) {
                    $emailName = "Credit Card " . $emailName;
                }
                sendMessage($emailName, $invoiceId);
                run_hook("InvoiceCreated", $invoiceArr);
            }
        }
        $apiresults = ["result" => "success", "invoiceid" => $invoiceId];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Invoice ID Not Found"];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>