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
$GATEWAY = getGatewayVariables("payson");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
$strYourSecretKey = $GATEWAY["key"];
$strOkURL = $_GET["OkURL"];
$strRefNr = $_GET["RefNr"];
$strPaysonRef = $_GET["Paysonref"];
$strTestMD5String = $strOkURL . $strPaysonRef . $strYourSecretKey;
$strMD5Hash = md5($strTestMD5String);
$transactionStatus = "Unsuccessful";
$redirectFile = "clientarea.php";
$redirectUrl = "action=invoices";
if ($strMD5Hash == $_GET["MD5"]) {
    $invoiceid = checkCbInvoiceID($_REQUEST["RefNr"], $GATEWAY["paymentmethod"]);
    addInvoicePayment($invoiceid, $strPaysonRef, "", "", "payson");
    $transactionStatus = "Successful";
    $redirectFile = "viewinvoice.php";
    $redirectUrl = "id=" . $invoiceid . "&paymentsuccess=true";
}
logTransaction($GATEWAY["paymentmethod"], $_REQUEST, $transactionStatus);
redirSystemURL($redirectUrl, $redirectFile);

?>