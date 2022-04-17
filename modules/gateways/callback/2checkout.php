<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require "../../../init.php";
App::load_function("clientarea");
App::load_function("gateway");
App::load_function("invoice");
$forceInline = false;
$forceStandard = false;
if (App::isInRequest("x_invoice_num")) {
    $invoiceId = App::getFromRequest("x_invoice_num");
    $forceStandard = true;
} else {
    if (App::isInRequest("product_description")) {
        $invoiceId = App::getFromRequest("product_description");
        $forceInline = true;
    } else {
        $invoiceId = App::getFromRequest("merchant_order_id");
    }
}
try {
    $gatewayParams = getGatewayVariables("tco", $invoiceId);
    if (!$gatewayParams["type"]) {
        WHMCS\Terminus::getInstance()->doDie("Module Not Activated");
    }
    $class = "\\WHMCS\\Module\\Gateway\\TCO\\Standard";
    if (!$forceStandard && ($forceInline || $gatewayParams["integrationMethod"] == "inline")) {
        $class = "\\WHMCS\\Module\\Gateway\\TCO\\Inline";
    }
    $callback = new $class();
    $callback->clientCallback($gatewayParams);
} catch (Exception $e) {
    WHMCS\Terminus::getInstance()->doDie($e->getMessage());
}

?>