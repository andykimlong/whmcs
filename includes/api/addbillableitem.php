<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $clientId = (int) App::getFromRequest("clientid");
    $description = App::getFromRequest("description");
    $invoiceAction = App::getFromRequest("invoiceaction");
    $recur = (int) App::getFromRequest("recur");
    $recurCycle = App::getFromRequest("recurcycle");
    $recurfor = (int) App::getFromRequest("recurfor");
    $dueDate = App::getFromRequest("duedate");
    $quantity = (int) App::getFromRequest("quantity");
    $unit = App::getFromRequest("unit");
    $hours = (int) App::getFromRequest("hours");
    $amount = (int) App::getFromRequest("amount");
    $unit = strtolower($unit);
    $invoiceAction = strtolower($invoiceAction);
    if (!$quantity && $hours) {
        $quantity = $hours;
    }
    $clientId = WHMCS\Database\Capsule::table("tblclients")->where("id", $clientId)->value("id");
    if (!$clientId) {
        $apiresults = ["result" => "error", "message" => "Client ID not Found"];
        return NULL;
    }
    if (!$description) {
        $apiresults = ["result" => "error", "message" => "You must provide a description"];
        return NULL;
    }
    $allowedTypes = ["noinvoice", "nextcron", "nextinvoice", "duedate", "recur"];
    if ($invoiceAction && !in_array($invoiceAction, $allowedTypes)) {
        $apiresults = ["result" => "error", "message" => "Invalid Invoice Action"];
        return NULL;
    }
    if ($invoiceAction == "recur" && (!$recur && !$recurCycle || !$recurfor)) {
        $apiresults = ["result" => "error", "message" => "Recurring must have a unit, cycle and limit"];
        return NULL;
    }
    if ($invoiceAction == "duedate" && !$dueDate) {
        $apiresults = ["result" => "error", "message" => "Due date is required"];
        return NULL;
    }
    $dueDate = $dueDate ? exit : "";
    $allowedUnits = ["hours", "quantity"];
    if (!in_array($unit, $allowedUnits)) {
        $apiresults = ["result" => "error", "message" => "Invalid Unit, please specify either 'hours' or 'quantity'"];
        return NULL;
    }
    $unit = $unit === "hours" ? 0 : 1;
    if ($invoiceAction == "noinvoice") {
        $invoiceAction = "0";
    } else {
        if ($invoiceAction == "nextcron") {
            $invoiceAction = "1";
            if (!$dueDate) {
                $dueDate = WHMCS\Carbon::now();
            }
        } else {
            if ($invoiceAction == "nextinvoice") {
                $invoiceAction = "2";
            } else {
                if ($invoiceAction == "duedate") {
                    $invoiceAction = "3";
                } else {
                    if ($invoiceAction == "recur") {
                        $invoiceAction = "4";
                    }
                }
            }
        }
    }
    $id = WHMCS\Database\Capsule::table("tblbillableitems")->insertGetId(["userid" => $clientId, "description" => $description, "hours" => $quantity, "unit" => $unit, "amount" => $amount, "recur" => $recur, "recurcycle" => $recurCycle, "recurfor" => $recurfor, "invoiceaction" => $invoiceAction, "duedate" => $dueDate]);
    $apiresults = ["result" => "success", "billableid" => $id];
    $responsetype = "json";
}
exit("This file cannot be accessed directly");

?>