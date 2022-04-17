<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";
$server = DI::make("oauth2_server");
if (is_null($scope)) {
    $scope = $server->getScopeUtil()->getScopeFromRequest($request);
}
if ($server->verifyResourceRequest($request, $response)) {
    $token = $server->getAccessToken($request);
    $response->setData(["success" => true, "message" => sprintf("Token '%s' is valid for scope(s) '%s'", $token->accessToken, $token->scope)]);
}
Log::debug("oauth/resource", ["request" => ["headers" => $request->server->getHeaders(), "request" => $request->request->all(), "query" => $request->query->all()], "response" => ["body" => $response->getContent()]]);
$response->send();

?>