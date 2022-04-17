<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
try {
    $GATEWAY = getGatewayVariables("paymateau");
} catch (WHMCS\Exception $e) {
    $GATEWAY = getGatewayVariables("paymatenz");
    $invoiceid = checkCbInvoiceID($_POST["ref"], $GATEWAY["paymentmethod"]);
    $redirectUrl = "id=" . $invoiceid . "&paymentfailed=true";
    $transactionStatus = "Error";
    if ($_POST["responseCode"] == "PA" && $invoiceid) {
        addInvoicePayment($invoiceid, $_POST["transactionID"], "", "", "paymate");
        $redirectUrl = "id=" . $invoiceid . "&paymentsuccess=true";
        $transactionStatus = "Successful";
    }
    logTransaction($GATEWAY["paymentmethod"], $_REQUEST, $transactionStatus);
    redirSystemURL($redirectUrl, "viewinvoice.php");
}

?>