<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

function getInvoiceStatusColour($status, $clientarea = true)
{
    if (!$clientarea) {
        global $aInt;
        if ($status == "Draft") {
            $status = "<span class=\"textgrey\">" . $aInt->lang("status", "draft") . "</span>";
        } else {
            if ($status == "Unpaid") {
                $status = "<span class=\"textred\">" . $aInt->lang("status", "unpaid") . "</span>";
            } else {
                if ($status == "Paid") {
                    $status = "<span class=\"textgreen\">" . $aInt->lang("status", "paid") . "</span>";
                } else {
                    if ($status == "Cancelled") {
                        $status = "<span class=\"textgrey\">" . $aInt->lang("status", "cancelled") . "</span>";
                    } else {
                        if ($status == "Refunded") {
                            $status = "<span class=\"textblack\">" . $aInt->lang("status", "refunded") . "</span>";
                        } else {
                            if ($status == "Collections") {
                                $status = "<span class=\"textgold\">" . $aInt->lang("status", "collections") . "</span>";
                            } else {
                                if ($status == "Payment Pending") {
                                    $status = "<span class=\"textgreen\">" . AdminLang::trans("status.paymentpending") . "</span>";
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        global $_LANG;
        if ($status == "Unpaid") {
            $status = "<span class=\"textred\">" . $_LANG["invoicesunpaid"] . "</span>";
        } else {
            if ($status == "Paid") {
                $status = "<span class=\"textgreen\">" . $_LANG["invoicespaid"] . "</span>";
            } else {
                if ($status == "Cancelled") {
                    $status = "<span class=\"textgrey\">" . $_LANG["invoicescancelled"] . "</span>";
                } else {
                    if ($status == "Refunded") {
                        $status = "<span class=\"textblack\">" . $_LANG["invoicesrefunded"] . "</span>";
                    } else {
                        if ($status == "Collections") {
                            $status = "<span class=\"textgold\">" . $_LANG["invoicescollections"] . "</span>";
                        } else {
                            if ($status == "Payment Pending") {
                                $status = "<span class=\"textgreen\">" . $_LANG["invoicesPaymentPending"] . "</span>";
                            }
                        }
                    }
                }
            }
        }
    }
    return $status;
}
function getInvoicePayUntilDate($nextduedate, $billingcycle, $fulldate = "")
{
    $year = substr($nextduedate, 0, 4);
    $month = substr($nextduedate, 5, 2);
    $day = substr($nextduedate, 8, 2);
    $daysadjust = $months = 0;
    $months = is_numeric($billingcycle) ? $billingcycle * 12 : getBillingCycleMonths($billingcycle);
    if (!$fulldate) {
        $daysadjust = 1;
    }
    $new_time = mktime(0, 0, 0, $month + $months, $day - $daysadjust, $year);
    $invoicepayuntildate = $billingcycle != "One Time" ? date("Y-m-d", $new_time) : "";
    return $invoicepayuntildate;
}
function addTransaction($userid, $currencyid, $description, $amountin, $fees, $amountout, $gateway = "", $transid = "", $invoiceid = "", $date = "", $refundid = "", $rate = "")
{
    if ($date) {
        if ($date instanceof WHMCS\Carbon) {
            $date = $date->toDateTimeString();
        } else {
            $date = toMySQLDate($date) . date(" H:i:s");
        }
    } else {
        $date = "now()";
    }
    if ($userid) {
        $currency = getCurrency($userid);
        $currencyid = $currency["id"];
    }
    if (!is_numeric($rate)) {
        if (empty($currencyid)) {
            $currency = getCurrency();
            $currencyid = $currency["id"];
        }
        $result = select_query("tblcurrencies", "rate", ["id" => $currencyid]);
        $data = mysql_fetch_array($result);
        $rate = $data["rate"];
    }
    if ($userid) {
        $currencyid = 0;
    }
    $array = ["userid" => $userid, "currency" => $currencyid, "gateway" => $gateway, "date" => $date, "description" => $description, "amountin" => $amountin, "fees" => $fees, "amountout" => $amountout, "rate" => $rate, "transid" => $transid, "invoiceid" => $invoiceid, "refundid" => $refundid];
    $saveid = insert_query("tblaccounts", $array);
    logActivity("Added Transaction - Transaction ID: " . $saveid, $userid);
    $array["id"] = $saveid;
    run_hook("AddTransaction", $array);
}
function updateInvoiceTotal($id)
{
    global $CONFIG;
    $taxsubtotal = 0;
    $nontaxsubtotal = 0;
    $result = select_query("tblinvoices", "userid,credit,taxrate,taxrate2", ["id" => $id]);
    $data = mysql_fetch_array($result);
    $userid = $data["userid"];
    $credit = $data["credit"];
    $taxrate = $data["taxrate"];
    $taxrate2 = $data["taxrate2"];
    if (round($taxrate, 2) == $taxrate) {
        $taxrate = format_as_currency($taxrate);
    }
    if (round($taxrate2, 2) == $taxrate2) {
        $taxrate2 = format_as_currency($taxrate2);
    }
    if (!function_exists("getClientsDetails")) {
        require_once dirname(__FILE__) . "/clientfunctions.php";
    }
    $clientsdetails = getClientsDetails($userid);
    $taxCalculator = new WHMCS\Billing\Tax();
    $taxCalculator->setIsInclusive($CONFIG["TaxType"] == "Inclusive")->setIsCompound($CONFIG["TaxL2Compound"]);
    if (is_numeric($taxrate)) {
        $taxCalculator->setLevel1Percentage($taxrate);
    }
    if (is_numeric($taxrate2)) {
        $taxCalculator->setLevel2Percentage($taxrate2);
    }
    $tax = $tax2 = 0;
    $result = select_query("tblinvoiceitems", "", ["invoiceid" => $id]);
    while ($data = mysql_fetch_array($result)) {
        if ($data["taxed"] == "1" && $CONFIG["TaxEnabled"] == "on" && !$clientsdetails["taxexempt"]) {
            if (WHMCS\Config\Setting::getValue("TaxPerLineItem")) {
                $taxCalculator->setTaxBase($data["amount"]);
                $tax += $taxCalculator->getLevel1TaxTotal();
                $tax2 += $taxCalculator->getLevel2TaxTotal();
                $taxsubtotal += $taxCalculator->getTotalBeforeTaxes();
            } else {
                $taxsubtotal += $data["amount"];
            }
        } else {
            $nontaxsubtotal += $data["amount"];
        }
    }
    if (!WHMCS\Config\Setting::getValue("TaxPerLineItem")) {
        $taxCalculator->setTaxBase($taxsubtotal);
        $tax = $taxCalculator->getLevel1TaxTotal();
        $tax2 = $taxCalculator->getLevel2TaxTotal();
        $taxsubtotal = $taxCalculator->getTotalBeforeTaxes();
    }
    $subtotal = $nontaxsubtotal + $taxsubtotal;
    $total = $subtotal + $tax + $tax2;
    if (0 < $credit) {
        if ($total < $credit) {
            $total = 0;
            $remainingcredit = $total - $credit;
        } else {
            $total -= $credit;
        }
    }
    update_query("tblinvoices", ["subtotal" => $subtotal, "tax" => $tax, "tax2" => $tax2, "total" => $total], ["id" => $id]);
    run_hook("UpdateInvoiceTotal", ["invoiceid" => $id]);
}
function addInvoicePayment($invoiceId, $transactionId, $amount, $fees, $gateway, $noEmail = false, $date = NULL)
{
    try {
        $invoice = WHMCS\Billing\Invoice::findOrFail($invoiceId);
        if (!$amount) {
            $amount = $invoice->balance;
            if ($amount <= 0) {
                throw new WHMCS\Exception\Module\NotServicable("Invoice Amount Invalid");
            }
        }
        if ($date && !$date instanceof WHMCS\Carbon) {
            $date = WHMCS\Carbon::createFromFormat("Y-m-d", toMySQLDate($date));
        }
        if (!$date instanceof WHMCS\Carbon) {
            $date = NULL;
        }
        return $invoice->addPayment($amount, $transactionId, $fees, $gateway, (int) $noEmail, $date);
    } catch (Exception $e) {
        return false;
    }
}
function removeOverpaymentCredit($userid, $transid, $amount)
{
    $clientData = WHMCS\Database\Capsule::table("tblclients")->find($userid, ["credit"]);
    $clientCredit = !is_null($clientData) ? $clientData->credit : 0;
    $creditBalance = $clientCredit - $amount;
    if ($creditBalance < 0) {
        $creditBalance = 0;
    }
    WHMCS\Database\Capsule::table("tblclients")->where("id", $userid)->update(["credit" => $creditBalance]);
    $invoiceData = WHMCS\Database\Capsule::table("tblaccounts")->where("userid", $userid)->find($transid, ["invoiceid"]);
    $invoiceid = !is_null($invoiceData) ? $invoiceData->invoiceid : 0;
    if (0 < $invoiceid) {
        WHMCS\Database\Capsule::table("tblcredit")->insert(["clientid" => $userid, "date" => WHMCS\Carbon::now()->toDateTimeString(), "description" => "Removal of Credit from Invoice #" . $invoiceid, "amount" => "-" . $amount, "relid" => $invoiceid]);
        logActivity("Removal of Credit from Invoice #" . $invoiceid, $userid);
    }
}
function refundInvoicePayment($transid, $amount, $sendtogateway, $addascredit = "", $sendemail = true, $refundtransid = "", $reverse = false)
{
    try {
        $transaction = WHMCS\Billing\Payment\Transaction::findOrFail($transid);
        $transid = $transaction->id;
        $invoiceid = $transaction->invoiceId;
        $gateway = $transaction->paymentGateway;
        $fullamount = $transaction->amountIn;
        $fees = $transaction->fees;
        $gatewaytransid = $transaction->transactionId;
        $rate = $transaction->exchangeRate;
        $userid = $transaction->clientId;
        if (!$userid && $transaction->invoiceId) {
            $userid = $transaction->invoice->clientId;
        }
        $gateway = WHMCS\Gateways::makeSafeName($gateway);
        $result = select_query("tblaccounts", "SUM(amountout),SUM(fees)", ["refundid" => $transid]);
        $data = mysql_fetch_array($result);
        list($alreadyrefunded, $alreadyrefundedfees) = $data;
        $fullamount -= $alreadyrefunded;
        $fees -= $alreadyrefundedfees * -1;
        if ($fees <= 0) {
            $fees = 0;
        }
        $result = select_query("tblaccounts", "SUM(amountin),SUM(amountout)", ["invoiceid" => $invoiceid]);
        $data = mysql_fetch_array($result);
        list($invoicetotalpaid, $invoicetotalrefunded) = $data;
        if (!$amount) {
            $amount = $fullamount;
        }
        if (!$amount || $fullamount < $amount) {
            return "amounterror";
        }
        $amount = format_as_currency($amount);
        if ($addascredit) {
            addtransaction($userid, 0, "Refund of Transaction ID " . $gatewaytransid . " to Credit Balance", 0, $fees * -1, $amount, "", "", $invoiceid, "", $transid, $rate);
            addtransaction($userid, 0, "Credit from Refund of Invoice ID " . $invoiceid, $amount, $fees, 0, "", "", "", "", "", "");
            logActivity("Refunded Invoice Payment to Credit Balance - Invoice ID: " . $invoiceid, $userid);
            insert_query("tblcredit", ["clientid" => $userid, "date" => "now()", "description" => "Credit from Refund of Invoice ID " . $invoiceid, "amount" => $amount]);
            update_query("tblclients", ["credit" => "+=" . $amount], ["id" => (int) $userid]);
            if ($invoicetotalpaid - $invoicetotalrefunded - $amount <= 0) {
                WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->update(["status" => WHMCS\Billing\Invoice::STATUS_REFUNDED, "date_refunded" => WHMCS\Carbon::now()->toDateTimeString()]);
                run_hook("InvoiceRefunded", ["invoiceid" => $invoiceid]);
            }
            if ($sendemail) {
                sendMessage("Invoice Refund Confirmation", $invoiceid, ["invoice_refund_type" => "credit"]);
            }
            return "creditsuccess";
        }
        $convertto = WHMCS\Module\GatewaySetting::getConvertToFor($gateway);
        $client = WHMCS\User\Client::findOrFail($userid);
        if ($convertto) {
            $convertedamount = convertCurrency($amount, $client->currencyId, $convertto, $rate);
            $refundCurrencyId = $convertto;
        } else {
            $convertedamount = NULL;
            $refundCurrencyId = $client->currencyId;
        }
        $params = [];
        if ($gateway) {
            $params = getCCVariables($invoiceid, $gateway);
        }
        if ($sendtogateway) {
            $gatewayModule = new WHMCS\Module\Gateway();
            $gatewayModule->load($gateway);
            if ($gatewayModule->functionExists("refund")) {
                $params["amount"] = $convertedamount ? $convertedamount : $amount;
                $params["transid"] = $gatewaytransid;
                $params["paymentmethod"] = $gateway;
                if ($refundCurrencyId) {
                    $refundCurrency = WHMCS\Billing\Currency::find($refundCurrencyId);
                    if ($refundCurrency) {
                        $params["currency"] = $refundCurrency->code;
                    }
                }
                if (!isset($params["currency"])) {
                    $params["currency"] = "";
                }
                $gatewayresult = $gatewayModule->call("refund", $params);
                if (is_array($gatewayresult)) {
                    $refundtransid = $gatewayresult["transid"];
                    $rawdata = $gatewayresult["rawdata"];
                    if (isset($gatewayresult["fees"])) {
                        $fees = $gatewayresult["fees"];
                    }
                    $gatewayresult = $gatewayresult["status"];
                } else {
                    $gatewayresult = "error";
                    $rawdata = "Returned false";
                }
                logTransaction($gateway, $rawdata, "Refund " . ucfirst($gatewayresult));
            } else {
                $gatewayresult = "manual";
                run_hook("ManualRefund", ["transid" => $transid, "amount" => $amount]);
            }
        } else {
            $gatewayresult = "manual";
            run_hook("ManualRefund", ["transid" => $transid, "amount" => $amount]);
        }
        if ($gatewayresult == "success" || $gatewayresult == "manual") {
            addtransaction($userid, 0, "Refund of Transaction ID " . $gatewaytransid, 0, $fees * -1, $amount, $gateway, $refundtransid, $invoiceid, "", $transid, $rate);
            logActivity("Refunded Invoice Payment - Invoice ID: " . $invoiceid . " - Transaction ID: " . $transid, $userid);
            $result = select_query("tblinvoices", "total", ["id" => $invoiceid]);
            $data = mysql_fetch_array($result);
            $invoicetotal = $data[0];
            if ($invoicetotalpaid - $invoicetotalrefunded - $amount <= 0) {
                WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->update(["status" => WHMCS\Billing\Invoice::STATUS_REFUNDED, "date_refunded" => WHMCS\Carbon::now()->toDateTimeString()]);
                run_hook("InvoiceRefunded", ["invoiceid" => $invoiceid]);
            }
            if ($sendemail) {
                sendMessage("Invoice Refund Confirmation", $invoiceid, ["invoice_refund_type" => "gateway"]);
            }
            if ($reverse) {
                reversePaymentActions($transaction, $refundtransid, $transaction->transactionId);
            }
        }
        return $gatewayresult;
    } catch (Exception $e) {
        return "amounterror";
    }
}
function processPaidInvoice($invoiceid, $noemail = "", $date = "")
{
    try {
        $invoice = WHMCS\Billing\Invoice::findOrFail($invoiceid);
        $invoiceid = $invoice->id;
        $userid = $invoice->clientId;
        $invoicestatus = $invoice->status;
        $invoicenum = $invoice->invoiceNumber;
        if (!in_array($invoicestatus, ["Unpaid", "Payment Pending"])) {
            return false;
        }
        if ($date && !$date instanceof WHMCS\Carbon) {
            $date = toMySQLDate($date) . date(" H:i:s");
        } else {
            if (!$date) {
                $date = WHMCS\Carbon::now();
            }
        }
        $invoice->status = "Paid";
        $invoice->datePaid = $date;
        $invoice->save();
        logActivity("Invoice Marked Paid - Invoice ID: " . $invoiceid, $userid);
        if (WHMCS\Invoices::isSequentialPaidInvoiceNumberingEnabled()) {
            $euVATAddonCustomInvoiceNumbersEnabled = WHMCS\Config\Setting::getValue("TaxNextCustomInvoiceNumber");
            if (!$invoicenum || $euVATAddonCustomInvoiceNumbersEnabled) {
                update_query("tblinvoices", ["invoicenum" => WHMCS\Invoices::getNextSequentialPaidInvoiceNumber()], ["id" => $invoiceid]);
            }
        }
        run_hook("InvoicePaidPreEmail", ["invoiceid" => $invoiceid]);
        if (!$noemail) {
            sendMessage(WHMCS\Billing\Invoice::PAYMENT_CONFIRMATION_EMAIL, $invoiceid, ["gatewayInterface" => $invoice->getGatewayInterface()]);
        }
        $orderId = get_query_val("tblorders", "id", ["invoiceid" => $invoiceid]);
        if ($orderId) {
            run_hook("OrderPaid", ["orderId" => $orderId, "userId" => $userid, "invoiceId" => $invoiceid]);
        }
        $items = $invoice->items()->where("type", "!=", "")->orderBy("id", "asc")->get();
        $massPay = $items->where("type", WHMCS\Billing\Invoice\Item::TYPE_MASS_PAY_INVOICE)->all();
        if (!$massPay) {
            $massPayInvoices = WHMCS\Billing\Invoice\Item::with("Invoice")->where("type", WHMCS\Billing\Invoice\Item::TYPE_MASS_PAY_INVOICE)->where("relid", $invoiceid)->get();
            foreach ($massPayInvoices as $massPayInvoice) {
                if ($massPayInvoice->invoice->status === WHMCS\Billing\Invoice::STATUS_UNPAID) {
                    $massPayInvoice->invoice->setStatusCancelled()->save();
                }
            }
        }
        foreach ($items as $item) {
            $userid = $item->userId;
            $type = $item->type;
            $relid = $item->relatedEntityId;
            $amount = $item->amount;
            if ($type == "Hosting") {
                makeHostingPayment($relid, $invoice);
            } else {
                if ($type == "DomainRegister" || $type == "DomainTransfer" || $type == "Domain") {
                    makeDomainPayment($relid, $type);
                } else {
                    if ($type == "DomainAddonDNS") {
                        $enabledcheck = get_query_val("tbldomains", "dnsmanagement", ["id" => $relid]);
                        if (!$enabledcheck) {
                            $currency = getCurrency($userid);
                            $dnscost = get_query_val("tblpricing", "msetupfee", ["type" => "domainaddons", "currency" => $currency["id"], "relid" => 0]);
                            update_query("tbldomains", ["dnsmanagement" => "1", "recurringamount" => "+=" . $dnscost], ["id" => $relid]);
                        }
                    } else {
                        if ($type == "DomainAddonEMF") {
                            $enabledcheck = get_query_val("tbldomains", "emailforwarding", ["id" => $relid]);
                            if (!$enabledcheck) {
                                $currency = getCurrency($userid);
                                $emfcost = get_query_val("tblpricing", "qsetupfee", ["type" => "domainaddons", "currency" => $currency["id"], "relid" => 0]);
                                update_query("tbldomains", ["emailforwarding" => "1", "recurringamount" => "+=" . $emfcost], ["id" => $relid]);
                            }
                        } else {
                            if ($type == "DomainAddonIDP") {
                                $enabledcheck = get_query_val("tbldomains", "idprotection", ["id" => $relid]);
                                if (!$enabledcheck) {
                                    $currency = getCurrency($userid);
                                    $idpcost = get_query_val("tblpricing", "ssetupfee", ["type" => "domainaddons", "currency" => $currency["id"], "relid" => 0]);
                                    update_query("tbldomains", ["idprotection" => "1", "recurringamount" => "+=" . $idpcost], ["id" => $relid]);
                                    $data = get_query_vals("tbldomains", "type,domain,registrar,registrationperiod", ["id" => $relid]);
                                    $domainparts = explode(".", $data["domain"], 2);
                                    $params = [];
                                    $params["domainid"] = $relid;
                                    list($params["sld"], $params["tld"]) = $domainparts;
                                    $params["regperiod"] = $data["registrationperiod"];
                                    $params["registrar"] = $data["registrar"];
                                    $params["regtype"] = $data["type"];
                                    if (!function_exists("RegIDProtectToggle")) {
                                        require ROOTDIR . "/includes/registrarfunctions.php";
                                    }
                                    $values = RegIDProtectToggle($params);
                                    if ($values["error"]) {
                                        logActivity("ID Protection Enabling Failed - Error: " . $values["error"] . " - Domain ID: " . $relid, $userid);
                                    } else {
                                        logActivity("ID Protection Enabled Successfully - Domain ID: " . $relid, $userid);
                                    }
                                }
                            } else {
                                if ($type == "Addon") {
                                    makeAddonPayment($relid, $invoice);
                                } else {
                                    if ($type == "Upgrade") {
                                        if (!function_exists("processUpgradePayment")) {
                                            require dirname(__FILE__) . "/upgradefunctions.php";
                                        }
                                        processUpgradePayment($relid, "", "", "true");
                                    } else {
                                        if ($type == "AddFunds") {
                                            insert_query("tblcredit", ["clientid" => $userid, "date" => "now()", "description" => "Add Funds Invoice #" . $invoiceid, "amount" => $amount]);
                                            update_query("tblclients", ["credit" => "+=" . $amount], ["id" => (int) $userid]);
                                        } else {
                                            if ($type == "Invoice") {
                                                insert_query("tblcredit", ["clientid" => $userid, "date" => "now()", "description" => "Mass Invoice Payment Credit for Invoice #" . $relid, "amount" => $amount]);
                                                update_query("tblclients", ["credit" => "+=" . $amount], ["id" => (int) $userid]);
                                                applyCredit($relid, $userid, $amount);
                                            } else {
                                                if (substr($type, 0, 14) == "ProrataProduct") {
                                                    $newduedate = substr($type, 14);
                                                    update_query("tblhosting", ["nextduedate" => $newduedate, "nextinvoicedate" => $newduedate], ["id" => $relid]);
                                                } else {
                                                    if (substr($type, 0, 12) == "ProrataAddon") {
                                                        $newduedate = substr($type, 12);
                                                        update_query("tblhostingaddons", ["nextduedate" => $newduedate, "nextinvoicedate" => $newduedate], ["id" => $relid]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        run_hook("InvoicePaid", ["invoiceid" => $invoiceid]);
    } catch (Exception $e) {
        return false;
    }
}
function getTaxRate($level, $state, $country)
{
    $result = select_query("tbltax", "", ["level" => $level, "state" => $state, "country" => $country]);
    $data = mysql_fetch_array($result);
    $taxname = $data["name"];
    $taxrate = $data["taxrate"];
    if (is_null($taxrate)) {
        $result = select_query("tbltax", "", ["level" => $level, "state" => "", "country" => $country]);
        $data = mysql_fetch_array($result);
        $taxname = $data["name"];
        $taxrate = $data["taxrate"];
    }
    if (is_null($taxrate)) {
        $result = select_query("tbltax", "", ["level" => $level, "state" => "", "country" => ""]);
        $data = mysql_fetch_array($result);
        $taxname = $data["name"];
        $taxrate = $data["taxrate"];
    }
    if (is_null($taxrate)) {
        $taxname = "";
        $taxrate = 0;
    } else {
        if (!$taxname) {
            $taxname = Lang::trans("invoicestax");
        }
    }
    if ($taxrate && round($taxrate, 2) == $taxrate) {
        $taxrate = format_as_currency($taxrate);
    }
    return ["name" => $taxname, "rate" => $taxrate];
}
function pdfInvoice($invoiceid, $language = "")
{
    global $whmcs;
    global $CONFIG;
    global $_LANG;
    global $currency;
    $invoice = new WHMCS\Invoice();
    $invoice->pdfCreate();
    if ($language) {
        $invoice->setOutputLanguage($language);
    }
    $invoice->pdfInvoicePage($invoiceid);
    $pdfdata = $invoice->pdfOutput();
    return $pdfdata;
}
function makeHostingPayment($func_domainid, WHMCS\Billing\Invoice $invoice)
{
    global $CONFIG;
    global $disable_to_do_list_entries;
    $result = select_query("tblhosting", "", ["id" => $func_domainid]);
    $data = mysql_fetch_array($result);
    $userid = $data["userid"];
    $orderId = $data["orderid"];
    $billingcycle = $data["billingcycle"];
    $domain = $data["domain"];
    $packageid = $data["packageid"];
    $regdate = $data["regdate"];
    $nextduedate = $data["nextduedate"];
    $status = $data["domainstatus"];
    $server = $data["server"];
    $paymentmethod = $data["paymentmethod"];
    $suspendreason = $data["suspendreason"];
    $result = select_query("tblproducts", "", ["id" => $packageid]);
    $data = mysql_fetch_array($result);
    $producttype = $data["type"];
    $productname = $data["name"];
    $module = $data["servertype"];
    $proratabilling = $data["proratabilling"];
    $proratadate = $data["proratadate"];
    $proratachargenextmonth = $data["proratachargenextmonth"];
    $autosetup = $data["autosetup"];
    if ($regdate == $nextduedate && $proratabilling) {
        $orderyear = substr($regdate, 0, 4);
        $ordermonth = substr($regdate, 5, 2);
        $orderday = substr($regdate, 8, 2);
        $proratavalues = getProrataValues($billingcycle, $product_onetime, $proratadate, $proratachargenextmonth, $orderday, $ordermonth, $orderyear, $userid);
        $nextduedate = $proratavalues["date"];
    } else {
        $nextduedate = getinvoicepayuntildate($nextduedate, $billingcycle, true);
    }
    update_query("tblhosting", ["nextduedate" => $nextduedate, "nextinvoicedate" => $nextduedate], ["id" => $func_domainid]);
    if (!function_exists("getModuleType")) {
        require_once ROOTDIR . "/includes/modulefunctions.php";
    }
    if ($status == "Pending" && $autosetup == "payment" && $module) {
        if (getNewClientAutoProvisionStatus($userid)) {
            logActivity("Running Module Create on Payment", $userid);
            try {
                $result = WHMCS\Service\Service::findOrFail($func_domainid)->legacyProvision();
            } catch (Exception $e) {
                $result = $e->getMessage();
                if ($result == "success") {
                    if ($module != "marketconnect") {
                        sendMessage("defaultnewacc", $func_domainid);
                    }
                    sendAdminMessage("Automatic Setup Successful", ["client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => ""], "account");
                } else {
                    sendAdminMessage("Automatic Setup Failed", ["client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => $result], "account");
                }
            }
        } else {
            logActivity("Module Create on Payment Suppressed for New Client", $userid);
        }
    }
    $suspenddate = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $CONFIG["AutoSuspensionDays"], date("Y")));
    if ($status == "Suspended" && $CONFIG["AutoUnsuspend"] == "on" && $module && !$suspendreason && $suspenddate <= str_replace("-", "", $nextduedate)) {
        logActivity("Running Auto Unsuspend on Payment", $userid);
        $moduleresult = ServerUnsuspendAccount($func_domainid);
        if ($moduleresult == "success") {
            sendMessage("Service Unsuspension Notification", $func_domainid);
            sendAdminMessage("Service Unsuspension Successful", ["client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => ""], "account");
        } else {
            sendAdminMessage("Service Unsuspension Failed", ["client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => $moduleresult], "account");
            if (!$disable_to_do_list_entries) {
                insert_query("tbltodolist", ["date" => "now()", "title" => "Manual Unsuspend Required", "description" => "The order placed for " . $domain . " has received its next payment and the automatic unsuspend has failed<br />Client ID: " . $userid . "<br>Product/Service: " . $productname . "<br>Domain: " . $domain, "admin" => "", "status" => "Pending", "duedate" => date("Y-m-d")]);
            }
        }
    }
    if ($status != "Pending" && $module) {
        $runRenew = $invoice->shouldRenewRun($func_domainid, $regdate);
        if ($runRenew) {
            $moduleResult = ServerRenew($func_domainid);
            if ($moduleResult != "success" && $moduleResult != "notsupported") {
                sendAdminMessage("Service Renewal Failed", ["client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "addon_id" => 0, "addon_name" => "", "error_msg" => $moduleResult], "account");
                if (!$disable_to_do_list_entries) {
                    $description = "The order placed for " . $domain . " has received its next payment and the" . " automatic renewal has failed<br>Client ID: " . $userid . "<br>" . "Product/Service: " . $productname . "<br>Domain: " . $domain;
                    $date = WHMCS\Carbon::now();
                    WHMCS\Database\Capsule::table("tbltodolist")->insert(["date" => $date->toDateString(), "title" => "Manual Renewal Required", "description" => $description, "admin" => "", "status" => "Pending", "duedate" => $date->toDateTimeString()]);
                }
            }
        }
    }
    AffiliatePayment("", $func_domainid);
    $freeAddons = WHMCS\Service\Addon::with("productAddon", "productAddon.welcomeEmailTemplate")->whereIn("billingcycle", ["Free", "Free Account"])->where("addonid", ">", 0)->where("status", "Pending")->where("hostingid", $func_domainid)->get();
    foreach ($freeAddons as $freeAddon) {
        $aId = $freeAddon->id;
        $addonId = $freeAddon->addonId;
        $autoActivate = $freeAddon->productAddon->autoActivate;
        $welcomeEmail = $freeAddon->productAddon->welcomeEmailTemplateId;
        if ($autoActivate && $autoActivate == "payment") {
            switch ($freeAddon->productAddon->module) {
                case "":
                    $freeAddon->status = "Active";
                    $freeAddon->save();
                    $automationResult = "";
                    $noModule = true;
                    break;
                default:
                    $automation = WHMCS\Service\Automation\AddonAutomation::factory($freeAddon);
                    $automationResult = $automation->runAction("CreateAccount");
                    $noModule = false;
                    if ($noModule || $automationResult) {
                        if ($welcomeEmail) {
                            sendMessage($welcomeEmail, $func_domainid, ["addon_id" => $aId, "addon_service_id" => $func_domainid, "addon_addonid" => $addonId, "addon_billing_cycle" => $freeAddon->billingCycle, "addon_status" => "Active", "addon_nextduedate" => "0000-00-00", "addon_name" => $name = $freeAddon->name ?: $freeAddon->productAddon->name]);
                        }
                        if ($noModule) {
                            run_hook("AddonActivation", ["id" => $freeAddon->id, "userid" => $freeAddon->clientId, "serviceid" => $func_domainid, "addonid" => $freeAddon->addonId]);
                        }
                    }
            }
        }
    }
}
function makeDomainPayment($func_domainid, $type = "")
{
    global $whmcs;
    $result = select_query("tbldomains", "", ["id" => $func_domainid]);
    $data = mysql_fetch_array($result);
    $userid = $data["userid"];
    $orderid = $data["orderid"];
    $registrationperiod = $data["registrationperiod"];
    $registrationdate = $data["registrationdate"];
    $nextduedate = $data["nextduedate"];
    $recurringamount = $data["recurringamount"];
    $domain = $data["domain"];
    $paymentmethod = $data["paymentmethod"];
    $registrar = $data["registrar"];
    $status = $data["status"];
    $year = substr($nextduedate, 0, 4);
    $month = substr($nextduedate, 5, 2);
    $day = substr($nextduedate, 8, 2);
    $newnextduedate = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year + $registrationperiod));
    update_query("tbldomains", ["nextduedate" => $newnextduedate], ["id" => $func_domainid]);
    $domaintype = substr($type, 6);
    $domainparts = explode(".", $domain, 2);
    list($sld, $tld) = $domainparts;
    $params = [];
    $params["domainid"] = $func_domainid;
    $params["sld"] = $sld;
    $params["tld"] = $tld;
    if (!function_exists("getRegistrarConfigOptions")) {
        require ROOTDIR . "/includes/registrarfunctions.php";
    }
    if ($domaintype == "Register" || $domaintype == "Transfer") {
        $result = select_query("tbldomainpricing", "autoreg", ["extension" => "." . $tld]);
        $data = mysql_fetch_array($result);
        $autoreg = $data[0];
        if ($status == "Pending") {
            if (getNewClientAutoProvisionStatus($userid)) {
                if ($autoreg) {
                    update_query("tbldomains", ["registrar" => $autoreg], ["id" => $func_domainid]);
                    $params["registrar"] = $autoreg;
                    if ($domaintype == "Register") {
                        logActivity("Running Automatic Domain Registration on Payment", $userid);
                        $result = RegRegisterDomain($params);
                        $emailmessage = "Domain Registration Confirmation";
                    } else {
                        if ($domaintype == "Transfer") {
                            logActivity("Running Automatic Domain Transfer on Payment", $userid);
                            $result = RegTransferDomain($params);
                            $emailmessage = "Domain Transfer Initiated";
                        }
                    }
                    $result = $result["error"];
                    if ($result) {
                        sendAdminMessage("Automatic Setup Failed", ["client_id" => $userid, "domain_id" => $func_domainid, "domain_type" => $domaintype, "domain_name" => $domain, "error_msg" => $result], "account");
                        if ($whmcs->get_config("DomainToDoListEntries")) {
                            if ($domaintype == "Register") {
                                addToDoItem("Manual Domain Registration", "Client ID " . $userid . " has paid for the registration of domain " . $domain . " and the automated registration attempt has failed with the following error: " . $result);
                            } else {
                                if ($domaintype == "Transfer") {
                                    addToDoItem("Manual Domain Transfer", "Client ID " . $userid . " has paid for the transfer of domain " . $domain . " and the automated transfer attempt has failed with the following error: " . $result);
                                }
                            }
                        }
                    } else {
                        sendMessage($emailmessage, $func_domainid);
                        sendAdminMessage("Automatic Setup Successful", ["client_id" => $userid, "domain_id" => $func_domainid, "domain_type" => $domaintype, "domain_name" => $domain, "error_msg" => ""], "account");
                    }
                } else {
                    if ($whmcs->get_config("DomainToDoListEntries")) {
                        if ($domaintype == "Register") {
                            addToDoItem("Manual Domain Registration", "Client ID " . $userid . " has paid for the registration of domain " . $domain);
                        } else {
                            if ($domaintype == "Transfer") {
                                addToDoItem("Manual Domain Transfer", "Client ID " . $userid . " has paid for the transfer of domain " . $domain);
                            }
                        }
                    }
                }
            } else {
                logActivity("Automatic Domain Registration on Payment Suppressed for New Client", $userid);
            }
        } else {
            if ($autoreg) {
                logActivity("Automatic Domain Registration Suppressed as Domain Is Already Active", $userid);
            }
        }
    } else {
        if ($status != "Pending" && $status != "Cancelled" && $status != "Fraud") {
            if ($whmcs->get_config("AutoRenewDomainsonPayment") && $registrar) {
                if ($whmcs->get_config("FreeDomainAutoRenewRequiresProduct") && $recurringamount <= 0 && !get_query_val("tblhosting", "COUNT(*)", ["userid" => $userid, "domain" => $domain, "domainstatus" => "Active"])) {
                    logActivity("Suppressed Automatic Domain Renewal on Payment Due to Domain Being Free and having No Active Associated Product", $userid);
                    sendAdminNotification("account", "Free Domain Renewal Manual Action Required", "The domain " . $domain . " (ID: " . $func_domainid . ") was just invoiced for renewal and automatically marked paid due to it being free, but because no active Product/Service matching the domain was found in order to qualify for the free domain offer, the renewal has not been automatically submitted to the registrar.  You must login to review & process this renewal manually should it be desired.");
                } else {
                    logActivity("Running Automatic Domain Renewal on Payment", $userid);
                    $params["registrar"] = $registrar;
                    $result = RegRenewDomain($params);
                    $result = $result["error"];
                    if ($result) {
                        sendAdminMessage("Domain Renewal Failed", ["client_id" => $userid, "domain_id" => $func_domainid, "domain_name" => $domain, "error_msg" => $result], "account");
                        if ($whmcs->get_config("DomainToDoListEntries")) {
                            addToDoItem("Manual Domain Renewal", "Client ID " . $userid . " has paid for the renewal of domain " . $domain . " and the automated renewal attempt has failed with the following error: " . $result);
                        }
                    } else {
                        sendMessage("Domain Renewal Confirmation", $func_domainid);
                        sendAdminMessage("Domain Renewal Successful", ["client_id" => $userid, "domain_id" => $func_domainid, "domain_name" => $domain, "error_msg" => ""], "account");
                    }
                }
            } else {
                if ($whmcs->get_config("DomainToDoListEntries")) {
                    addToDoItem("Manual Domain Renewal", "Client ID " . $userid . " has paid for the renewal of domain " . $domain);
                }
            }
        }
    }
}
function makeAddonPayment($func_addonid, $invoice)
{
    try {
        $configuration = App::getApplicationConfig()->getData();
        $disable_to_do_list_entries = false;
        if (array_key_exists("disable_to_do_list_entries", $configuration)) {
            $disable_to_do_list_entries = (int) $configuration["disable_to_do_list_entries"];
        }
        $addon = WHMCS\Service\Addon::with("productAddon", "productAddon.welcomeEmailTemplate", "service", "service.product")->findOrFail($func_addonid);
        $id = $addon->id;
        $serviceId = $addon->serviceId;
        $addonId = $addon->addonId;
        $billingCycle = $addon->billingCycle;
        $status = $addon->status;
        $regDate = $addon->registrationDate;
        $nextDueDate = $addon->nextDueDate;
        $userId = $addon->clientId;
        $isProrated = $addon->productAddon->prorate;
        $name = $addon->name ?: $addon->productAddon->name;
        if ($regDate->isSameDay($nextDueDate) && $isProrated && $addon->proratadate != "0000-00-00") {
            $addonChargeNextMonthDay = $addon->service->product->proRataBilling ? $addon->service->product->proRataChargeNextMonthAfterDay : 32;
            $serviceNextDueDate = WHMCS\Carbon::safeCreateFromMySqlDate($addon->service->nextDueDate);
            $prorataUntilDate = $addon->service->billingCycle == $billingCycle ? $serviceNextDueDate : NULL;
            $prorataValues = getProrataValues($billingCycle, 0, $addon->prorataDate->day, $addonChargeNextMonthDay, $regDate->day, $regDate->month, $regDate->year, $userId, $prorataUntilDate);
            $nextDueDate = $prorataValues["date"];
        } else {
            $nextDueDate = getinvoicepayuntildate($nextDueDate, $billingCycle, true);
        }
        $addon->nextInvoiceDate = $nextDueDate;
        $addon->nextDueDate = $nextDueDate;
        $addon->save();
        if ($status == "Pending") {
            $autoActivate = "";
            $welcomeEmail = 0;
            if ($addonId) {
                $autoActivate = $addon->productAddon->autoActivate;
                $welcomeEmail = $addon->productAddon->welcomeEmailTemplate;
            }
            if ($autoActivate && $autoActivate == "payment") {
                switch ($addon->productAddon->module) {
                    case "":
                        $addon->status = "Active";
                        $addon->save();
                        $automationResult = "";
                        $noModule = true;
                        break;
                    default:
                        $automation = WHMCS\Service\Automation\AddonAutomation::factory($addon);
                        if ($addon->provisioningType === WHMCS\Product\Addon::PROVISIONING_TYPE_STANDARD) {
                            $automationResult = $automation->runAction("CreateAccount");
                        } else {
                            $automationResult = $automation->provisionAddOnFeature();
                        }
                        $noModule = false;
                        if ($noModule || $automationResult) {
                            if ($welcomeEmail) {
                                sendMessage($welcomeEmail, $serviceId, ["addon_id" => $id, "addon_service_id" => $serviceId, "addon_addonid" => $addonId, "addon_billing_cycle" => $billingCycle, "addon_status" => $status, "addon_nextduedate" => $nextDueDate, "addon_name" => $name]);
                            }
                            if ($noModule) {
                                run_hook("AddonActivation", ["id" => $addon->id, "userid" => $userId, "serviceid" => $addon->serviceId, "addonid" => $addon->addonId]);
                            }
                        }
                }
            }
        } else {
            if ($status == "Suspended") {
                if ($addonId && $addon->productAddon->module) {
                    $automation = WHMCS\Service\Automation\AddonAutomation::factory($addon);
                    if ($addon->provisioningType === WHMCS\Product\Addon::PROVISIONING_TYPE_STANDARD) {
                        $automationResult = $automation->runAction("UnsuspendAccount");
                    } else {
                        $automationResult = $automation->unsuspendAddOnFeature();
                    }
                    $noModule = false;
                } else {
                    $automationResult = "";
                    $addon->status = "Active";
                    $addon->save();
                    $noModule = true;
                    run_hook("AddonUnsuspended", ["id" => $addon->id, "userid" => $userId, "serviceid" => $serviceId, "addonid" => $addonId]);
                }
                if (($automationResult || $noModule) && $addon->productAddon->suspendProduct && $addon->service->domainStatus == "Suspended" && $addon->service->product->module) {
                    logActivity("Unsuspending Parent Service for Addon Payment - Service ID: " . $serviceId, $userId);
                    if (!function_exists("getModuleType")) {
                        include dirname(__FILE__) . "/modulefunctions.php";
                    }
                    ServerUnsuspendAccount($serviceId);
                }
            } else {
                if ($status == "Active") {
                    $noModule = true;
                    if ($addonId) {
                        switch ($addon->productAddon->module) {
                            case "":
                            default:
                                $registrationDate = $addon->registrationDate;
                                if ($registrationDate instanceof WHMCS\Carbon) {
                                    $registrationDate = $registrationDate->toDateString();
                                }
                                $runRenew = $invoice->shouldRenewRun($func_addonid, $registrationDate, "Addon");
                                if ($runRenew) {
                                    $automation = WHMCS\Service\Automation\AddonAutomation::factory($addon);
                                    $success = $automation->runAction("Renew");
                                    if (!$success && $automation->getError() != "notsupported") {
                                        $addonName = $addon->name;
                                        if (!$addonName && $addon->addonId) {
                                            $addonName = $addon->productAddon->name;
                                        }
                                        sendAdminMessage("Service Renewal Failed", ["client_id" => $userId, "service_id" => $addon->serviceId, "service_product" => $addon->service->product->name, "service_domain" => $addon->service->domain, "addon_id" => $addon->id, "addon_name" => $addonName, "error_msg" => $automation->getError()], "account");
                                        if (!$disable_to_do_list_entries) {
                                            $domain = $addon->serviceProperties->get("Domain Name");
                                            if (!$domain) {
                                                $domain = $addon->service->product->name;
                                            }
                                            $productName = $addon->service->product->name;
                                            $description = "The order placed for " . $domain . " has received its" . " next payment and the automatic renewal has failed<br>" . "Client ID: " . $userId . "<br>Product/Service: " . $productName . "<br>" . "Domain: " . $domain . "<br>Addon: " . $addonName;
                                            $date = WHMCS\Carbon::now();
                                            WHMCS\Database\Capsule::table("tbltodolist")->insert(["date" => $date->toDateString(), "title" => "Manual Renewal Required", "description" => $description, "admin" => "", "status" => "Pending", "duedate" => $date->toDateTimeString()]);
                                        }
                                    }
                                    $noModule = false;
                                }
                        }
                    }
                    if ($noModule) {
                        run_hook("AddonRenewal", ["id" => $addon->id, "userid" => $userId, "serviceid" => $addon->serviceId, "addonid" => $addon->addonId]);
                    }
                }
            }
        }
    } catch (Exception $e) {
    }
}
function getProrataValues($billingcycle, $amount, $proratadate, $proratachargenextmonth, $day, $month, $year, $userid, WHMCS\Carbon $prorataUntil = NULL)
{
    global $CONFIG;
    if (is_array($CONFIG) && !empty($CONFIG["ProrataClientsAnniversaryDate"])) {
        $result = select_query("tblclients", "datecreated", ["id" => $userid]);
        $data = mysql_fetch_array($result);
        $clientregdate = $data[0];
        $clientregdate = explode("-", $clientregdate);
        $proratadate = $clientregdate[2];
        if ($proratadate <= 0) {
            $proratadate = date("d");
        }
    }
    $billingcycle = str_replace("-", "", strtolower($billingcycle));
    $proratamonths = getBillingCycleMonths($billingcycle);
    if ($billingcycle != "monthly") {
        $proratachargenextmonth = 0;
    }
    if ($billingcycle == "monthly") {
        if ($day < $proratadate) {
            $proratamonth = $month;
        } else {
            $proratamonth = $month + 1;
        }
    } else {
        $prorataForMonths = $prorataUntil ? WHMCS\Carbon::now()->startOfMonth()->diffInMonths($prorataUntil->endOfMonth()) : $proratamonths;
        $proratamonth = $month + $prorataForMonths;
    }
    $proratadateuntil = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $year));
    $proratainvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate - 1, $year));
    $monthnumdays = ["31", "28", "31", "30", "31", "30", "31", "31", "30", "31", "30", "31"];
    if ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0) {
        $monthnumdays[1] = 29;
    }
    $totaldays = $extraamount = 0;
    if ($billingcycle == "monthly") {
        if ($proratachargenextmonth < $proratadate && $day < $proratadate && $proratachargenextmonth <= $day || $proratadate <= $proratachargenextmonth && $proratadate <= $day && $proratachargenextmonth <= $day || !$proratachargenextmonth) {
            $proratamonth++;
            $extraamount = $amount;
        }
        $totaldays += $monthnumdays[$month - 1];
        $days = ceil((strtotime($proratadateuntil) - strtotime($year . "-" . $month . "-" . $day)) / 86400);
        $proratadateuntil = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $year));
        $proratainvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate - 1, $year));
    } else {
        for ($counter = $month; $counter <= $month + $proratamonths - 1; $counter++) {
            $month2 = round($counter);
            if (12 < $month2) {
                $month2 = $month2 - 12;
            }
            if (12 < $month2) {
                $month2 = $month2 - 12;
            }
            if (12 < $month2) {
                $month2 = $month2 - 12;
            }
            $totaldays += $monthnumdays[$month2 - 1];
        }
        $days = ceil((strtotime($proratadateuntil) - strtotime($year . "-" . $month . "-" . $day)) / 86400);
    }
    $prorataamount = round($amount * $days / $totaldays, 2) + $extraamount;
    $days = ceil((strtotime($proratadateuntil) - strtotime($year . "-" . $month . "-" . $day)) / 86400);
    return ["amount" => $prorataamount, "date" => $proratadateuntil, "invoicedate" => $proratainvoicedate, "days" => $days];
}
function getNewClientAutoProvisionStatus($userid)
{
    if (WHMCS\Config\Setting::getValue("AutoProvisionExistingOnly")) {
        $activeServiceCount = WHMCS\Service\Service::where("userid", $userid)->where("domainstatus", WHMCS\Service\Status::ACTIVE)->count();
        $activeDomainCount = WHMCS\Domain\Domain::where("userid", $userid)->where("status", WHMCS\Domain\Status::ACTIVE)->count();
        if (0 < $activeServiceCount || 0 < $activeDomainCount) {
            return true;
        }
        return false;
    }
    return true;
}
function applyCredit($invoiceid, $userid, $amount, $noemail = "")
{
    $amount = round($amount, 2);
    update_query("tblinvoices", ["credit" => "+=" . $amount], ["id" => (int) $invoiceid]);
    update_query("tblclients", ["credit" => "-=" . $amount], ["id" => (int) $userid]);
    insert_query("tblcredit", ["clientid" => $userid, "date" => "now()", "description" => "Credit Applied to Invoice #" . $invoiceid, "amount" => $amount * -1]);
    logActivity("Credit Applied - Amount: " . $amount . " - Invoice ID: " . $invoiceid, $userid);
    updateinvoicetotal($invoiceid);
    $result = select_query("tblinvoices", "total", ["id" => $invoiceid]);
    $data = mysql_fetch_array($result);
    $total = $data["total"];
    $result = select_query("tblaccounts", "SUM(amountin)-SUM(amountout)", ["invoiceid" => $invoiceid]);
    $data = mysql_fetch_array($result);
    $amountpaid = $data[0];
    $balance = $total - $amountpaid;
    if ($balance <= 0) {
        processpaidinvoice($invoiceid, $noemail);
    }
}
function getBillingCycleDays($billingcycle)
{
    $totaldays = 0;
    if ($billingcycle == "Monthly") {
        $totaldays = 30;
    } else {
        if ($billingcycle == "Quarterly") {
            $totaldays = 90;
        } else {
            if ($billingcycle == "Semi-Annually") {
                $totaldays = 180;
            } else {
                if ($billingcycle == "Annually") {
                    $totaldays = 365;
                } else {
                    if ($billingcycle == "Biennially") {
                        $totaldays = 730;
                    } else {
                        if ($billingcycle == "Triennially") {
                            $totaldays = 1095;
                        }
                    }
                }
            }
        }
    }
    return $totaldays;
}
function getBillingCycleMonths($billingcycle)
{
    try {
        $months = (new WHMCS\Billing\Cycles())->getNumberOfMonths($billingcycle);
    } catch (Exception $e) {
        $months = 1;
        return $months;
    }
}
function isUniqueTransactionID($transactionID, $gateway)
{
    $transactionID = get_query_val("tblaccounts", "id", ["transid" => $transactionID, "gateway" => $gateway]);
    if ($transactionID) {
        return false;
    }
    return true;
}
function removeCreditOnInvoiceDelete($invoiceID)
{
    $invoiceData = WHMCS\Database\Capsule::table("tblinvoices")->find($invoiceID, ["userid", "credit"]);
    $creditAmount = $invoiceData->credit;
    $userID = $invoiceData->userid;
    if (0 < $creditAmount) {
        WHMCS\Database\Capsule::table("tblinvoices")->where("id", "=", $invoiceID)->update(["credit" => 0]);
        updateinvoicetotal($invoiceID);
        $client = WHMCS\User\Client::find($userID);
        $client->credit += $creditAmount;
        $client->save();
        WHMCS\Database\Capsule::table("tblcredit")->insert(["clientid" => $userID, "date" => date("Y-m-d"), "description" => "Credit Removed on deletion of Invoice #" . $invoiceID, "amount" => $creditAmount]);
        logActivity("Credit Removed on Invoice Deletion - Amount: " . $creditAmount . " - Invoice ID: " . $invoiceID, $userID);
    }
}
function refundCreditOnStatusChange($invoiceId, $status = "Fraud")
{
    $invoiceData = WHMCS\Database\Capsule::table("tblinvoices")->find($invoiceId, ["userid", "credit"]);
    $creditAmount = $invoiceData->credit;
    $userId = $invoiceData->userid;
    if (0 < $creditAmount) {
        WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceId)->update(["credit" => 0]);
        updateinvoicetotal($invoiceId);
        $client = WHMCS\User\Client::find($userId);
        $client->credit += $creditAmount;
        $client->save();
        WHMCS\Database\Capsule::table("tblcredit")->insert(["clientid" => $userId, "date" => WHMCS\Carbon::now()->format("Y-m-d"), "description" => "Credit Removed - Reason: Order status changed to " . $status . " - Invoice #" . $invoiceId, "amount" => $creditAmount]);
        logActivity("Credit Removed - Reason: Order status changed to " . $status . " - Amount: " . $creditAmount . " - Invoice ID: " . $invoiceId, $userId);
        return true;
    }
    return false;
}
function paymentReversed($reverseTransactionId, $originalTransactionId, $invoiceId = 0, $gateway = NULL)
{
    $transaction = WHMCS\Billing\Payment\Transaction::with("client")->where("transid", "=", $originalTransactionId);
    if ($invoiceId) {
        $transaction = $transaction->where("invoiceid", "=", $invoiceId);
    }
    if ($gateway) {
        $transaction = $transaction->where("gateway", "=", $gateway);
    }
    if (1 < $transaction->count()) {
        throw new WHMCS\Exception("Multiple Original Transaction matches - Reversal not Available");
    }
    $transaction = $transaction->first();
    if (!$transaction) {
        throw new WHMCS\Exception("Original Transaction Not Found");
    }
    $existingRefundTransaction = WHMCS\Billing\Payment\Transaction::where("refundid", "=", $transaction->id)->first();
    $reverseTransactionWithSameId = WHMCS\Billing\Payment\Transaction::where("transid", "=", $reverseTransactionId)->first();
    if ($existingRefundTransaction || $reverseTransactionWithSameId) {
        throw new WHMCS\Exception("Transaction Already Reversed");
    }
    $invoice = $transaction->invoice;
    $reversedTransaction = new WHMCS\Billing\Payment\Transaction();
    $reversedTransaction->amountOut = $transaction->amountIn;
    $reversedTransaction->refundId = $transaction->id;
    $reversedTransaction->transactionId = $reverseTransactionId;
    $reversedTransaction->invoiceId = $transaction->invoiceId;
    $reversedTransaction->exchangeRate = $transaction->exchangeRate;
    $reversedTransaction->fees = $transaction->fees * -1;
    $reversedTransaction->clientId = $transaction->clientId;
    $reversedTransaction->description = "Reversed Transaction ID: " . $transaction->transactionId;
    $reversedTransaction->paymentGateway = $transaction->paymentGateway;
    $reversedTransaction->date = WHMCS\Carbon::now();
    $reversedTransaction->save();
    if ($invoice) {
        reversePaymentActions($transaction, $reverseTransactionId, $originalTransactionId);
    }
    $gateway = $transaction->paymentGateway;
    $paymentGateway = "No Gateway";
    if ($gateway) {
        try {
            $paymentGateway = WHMCS\Module\Gateway::factory($gateway)->getDisplayName();
        } catch (Exception $e) {
            $paymentGateway = $gateway;
        }
    }
    sendAdminMessage("Payment Reversed Notification", ["invoice_id" => $invoice->id, "transaction_id" => $originalTransactionId, "transaction_date" => fromMySQLDate($transaction->date), "transaction_amount" => new WHMCS\View\Formatter\Price($transaction->amountIn, getCurrency($transaction->clientId)), "payment_method" => $paymentGateway], "account");
}
function reversePaymentActions(WHMCS\Billing\Payment\Transaction $transaction, $reverseTransactionId, $originalTransactionId)
{
    $invoice = $transaction->invoice;
    $doChangeInvoiceStatus = (int) WHMCS\Config\Setting::getValue("ReversalChangeInvoiceStatus");
    $doChangeDueDates = (int) WHMCS\Config\Setting::getValue("ReversalChangeDueDates");
    if ($doChangeInvoiceStatus) {
        $invoice->status = "Collections";
        $invoice->save();
        logActivity("Payment Reversal - Invoice Status set to Collections - Invoice ID: " . $invoice->id, $invoice->clientId);
    }
    foreach ($invoice->items as $item) {
        switch ($item->type) {
            case "Addon":
            case "Hosting":
                if ($doChangeDueDates) {
                    if ($item->type == "Addon") {
                        $model = WHMCS\Service\Addon::find($item->relatedEntityId);
                        $activityLogEntry = "Payment Reversal - Modified Service Addon - Next Due Date changed from ";
                        $activityLogSuffix = " - Service ID: " . $model->serviceId . " - Addon ID: " . $model->id;
                    } else {
                        $model = WHMCS\Service\Service::find($item->relatedEntityId);
                        $activityLogEntry = "Payment Reversal - Modified Product/Service - Next Due Date changed from ";
                        $activityLogSuffix = " - Service ID: " . $model->id;
                    }
                    $defaultNextDueDate = $model->registrationDate;
                    $nextDueDate = $model->nextDueDate;
                    if (!$nextDueDate instanceof WHMCS\Carbon && $nextDueDate != "0000-00-00" && $nextDueDate != "1970-01-01") {
                        $nextDueDate = WHMCS\Carbon::createFromFormat("Y-m-d", $nextDueDate);
                    }
                    if ($nextDueDate instanceof WHMCS\Carbon) {
                        $activityLogEntry .= $nextDueDate->toDateString() . " to";
                        $nextDueDate = $nextDueDate->subMonths(getbillingcyclemonths($model->billingCycle));
                        $activityLogEntry .= " " . $nextDueDate->toDateString();
                    } else {
                        $activityLogEntry .= $nextDueDate . " to " . $defaultNextDueDate;
                    }
                    $activityLogEntry .= " - User ID: " . $model->clientId;
                    $model->nextDueDate = $nextDueDate;
                    $model->save();
                    logActivity($activityLogEntry . $activityLogSuffix, $model->clientId);
                }
                break;
            case "Upgrade":
                $upgrade = WHMCS\Database\Capsule::table("tblupgrades")->find($item->relatedEntityId);
                $service = WHMCS\Service\Service::find($upgrade->relid);
                if ($service->serverId) {
                    $server = new WHMCS\Module\Server();
                    $server->loadByServiceID($service->id);
                    if ($server->functionExists("SuspendAccount")) {
                        $server->call("SuspendAccount");
                    }
                }
                break;
            case "AddFunds":
                WHMCS\Database\Capsule::table("tblcredit")->insert(["clientid" => $item->userId, "date" => WHMCS\Carbon::now()->toDateString(), "description" => "Reversed Transaction ID: " . $originalTransactionId, "amount" => $transaction->amountIn * -1]);
                $transaction->client->credit -= $transaction->amountIn;
                $transaction->client->save();
                logActivity("Payment Reversal - Removed Credit - User ID: " . $item->userId . " - Amount: " . formatCurrency($transaction->amountIn), $item->userId);
                break;
            case "Invoice":
                $reversedTransaction = new WHMCS\Billing\Payment\Transaction();
                $reversedTransaction->amountOut = $item->amount;
                $reversedTransaction->refundId = $transaction->id;
                $reversedTransaction->transactionId = $reverseTransactionId;
                $reversedTransaction->invoiceId = $item->relatedEntityId;
                $reversedTransaction->exchangeRate = $transaction->exchangeRate;
                $reversedTransaction->fees = 0;
                $reversedTransaction->clientId = $item->userId;
                $reversedTransaction->description = "Invoice Payment Reversal: Invoice ID: #" . $item->invoiceId;
                $reversedTransaction->paymentGateway = $transaction->paymentGateway;
                $reversedTransaction->date = WHMCS\Carbon::now();
                $reversedTransaction->save();
                if ($doChangeInvoiceStatus) {
                    $reversedTransaction->invoice->status = "Collections";
                    $reversedTransaction->invoice->save();
                    logActivity("Payment Reversal - Invoice Status set to Collections - Invoice ID: " . $reversedTransaction->invoice->id, $item->userId);
                }
                break;
            case "DomainRegister":
            case "DomainRenew":
            case "DomainTransfer":
            case "DomainAddonDNS":
            case "DomainAddonEMF":
            case "DomainAddonIDP":
            default:
                if ($doChangeDueDates) {
                    $model = NULL;
                    $previousInvoiceItem = NULL;
                    $activityLogEntry = "";
                    $activityLogSuffix = "";
                    if (substr($item->type, 0, 14) == "ProrataProduct") {
                        $model = WHMCS\Service\Service::find($item->relatedEntityId);
                        $previousInvoiceItem = WHMCS\Billing\Invoice\Item::where("relid", "=", $item->relatedEntityId)->where("type", "=", "Service")->orderBy("id", "DESC")->first();
                        $activityLogEntry = "Payment Reversal - Modified Product/Service - Next Due Date changed from ";
                        $activityLogSuffix = " - Service ID: " . $model->id;
                    } else {
                        if (substr($item->type, 0, 12) == "ProrataAddon") {
                            $model = WHMCS\Service\Addon::find($item->relatedEntityId);
                            $previousInvoiceItem = WHMCS\Billing\Invoice\Item::where("relid", "=", $item->relatedEntityId)->where("type", "=", "Addon")->orderBy("id", "DESC")->first();
                            $activityLogEntry = "Payment Reversal - Modified Service Addon - Next Due Date changed from ";
                            $activityLogSuffix = " - Service ID: " . $model->serviceId . " - Addon ID: " . $model->id;
                        }
                    }
                    if ($model && $previousInvoiceItem) {
                        $activityLogEntry .= $model->nextDueDate . " to " . $previousInvoiceItem->dueDate . " - User ID: " . $model->clientId;
                        $model->nextDueDate = $previousInvoiceItem->dueDate;
                        $model->save();
                        logActivity($activityLogEntry . $activityLogSuffix, $model->clientId);
                    }
                }
        }
    }
}

?>