<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require_once "init.php";
require_once ROOTDIR . "/includes" . DIRECTORY_SEPARATOR . "clientareafunctions.php";
$controller = new WHMCS\ClientArea\PasswordResetController();
$request = WHMCS\Http\Message\ServerRequest::fromGlobals();
$response = NULL;
if ($_SERVER["REQUEST_METHOD"] === "POST" && $request->has("email")) {
    $response = $controller->validateEmail($request);
}
if (!$response) {
    $response = $controller->emailPrompt($request);
}
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);

?>