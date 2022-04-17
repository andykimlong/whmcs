<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

add_hook("AdminAreaFooterOutput", 1, function ($vars) {
    $scriptUrl = "https://secure.ewaypayments.com/scripts/eWAY.min.js";
    $filename = $vars["filename"];
    $return = "";
    if ($filename == "clientssummary") {
        $return = "<script src=\"" . $scriptUrl . "\" data-init=\"false\"></script>";
    }
    return $return;
});
add_hook("ClientAreaFooterOutput", 1, function ($vars) {
    $scriptUrl = "https://secure.ewaypayments.com/scripts/eWAY.min.js";
    $filename = $vars["filename"];
    $template = $vars["templatefile"];
    $return = "";
    $requiredFiles = ["cart"];
    $requiredTemplates = ["account-paymentmethods-manage", "invoice-payment"];
    if (in_array($filename, $requiredFiles) || in_array($template, $requiredTemplates)) {
        $return = "<script src=\"" . $scriptUrl . "\" data-init=\"false\"></script>";
    }
    return $return;
});

?>