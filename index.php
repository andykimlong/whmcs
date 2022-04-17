<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("CLIENTAREA", true);
require_once __DIR__ . "/init.php";
$request = WHMCS\Http\Message\ServerRequest::fromGlobals();
$response = DI::make("Frontend\\Dispatcher")->dispatch($request);
$statusCode = $response->getStatusCode();
$statusFamily = substr($statusCode, 0, 1);
if (!in_array($statusFamily, [2, 3]) && !($response instanceof WHMCS\Http\Message\JsonResponse || $response instanceof Laminas\Diactoros\Response\JsonResponse || $response instanceof WHMCS\Admin\ApplicationSupport\View\Html\Smarty\ErrorPage || $response instanceof WHMCS\ClientArea) && $statusCode === 404) {
    gracefulCoreRequiredFileInclude("/includes/clientareafunctions.php");
    $response = new WHMCS\ClientArea();
    $response->setPageTitle("404 - Page Not Found");
    $response->setTemplate("error/page-not-found");
    $response->skipMainBodyContainer();
    $response = $response->withStatus(404);
}
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
exit;

?>