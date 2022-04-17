<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require_once "init.php";
$rss = new WHMCS\Announcement\Rss();
$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals();
$response = $rss->toXml($request);
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);

?>