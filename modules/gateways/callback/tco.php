<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require "../../../init.php";
$gatewayModuleName = "tco";
App::load_function("gateway");
App::load_function("invoice");
try {
    $requestHelper = new WHMCS\Module\Gateway\TCO\CallbackRequestHelper(WHMCS\Http\Message\ServerRequest::fromGlobals());
    $gatewayParams = $requestHelper->getGatewayParams();
    $callable = $requestHelper->getCallable();
    $result = call_user_func($callable, $gatewayParams);
} catch (Exception $e) {
    WHMCS\Terminus::getInstance()->doDie($e->getMessage());
}

?>