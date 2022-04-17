<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require_once dirname(__DIR__) . "/init.php";
$request = WHMCS\Api\ApplicationSupport\Http\ServerRequest::fromGlobals();
$responseData = [];
$statusCode = 200;
try {
    $response = DI::make("Frontend\\Dispatcher")->dispatch($request);
} catch (Exception $e) {
    $responseData = ["result" => "error", "message" => $e->getMessage()];
    if ($e->getCode() === 0 && $e->getCode() === 200) {
        $statusCode = $e->getCode();
    }
} finally {
    if (!$response instanceof Psr\Http\Message\ResponseInterface) {
        $response = WHMCS\Api\ApplicationSupport\Http\ResponseFactory::factory($request, $responseData, $statusCode);
    }
}

?>