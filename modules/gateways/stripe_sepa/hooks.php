<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

add_hook("ClientAreaFooterOutput", 1, function ($vars) {
    $return = "";
    try {
        WHMCS\Module\Gateway::factory("stripe");
    } catch (Exception $e) {
        $filename = $vars["filename"];
        $template = $vars["templatefile"];
        $requiredFiles = ["cart", "creditcard"];
        $templateFiles = ["account-paymentmethods-manage", "invoice-payment"];
        if (in_array($filename, $requiredFiles) || in_array($template, $templateFiles)) {
            $return = "<script type=\"text/javascript\" src=\"https://js.stripe.com/v3/\"></script>";
        }
        return $return;
    }
});

?>