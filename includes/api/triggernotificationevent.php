<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $identifier = App::getFromRequest("notification_identifier");
    $title = App::getFromRequest("title");
    $message = App::getFromRequest("message");
    $url = WHMCS\Input\Sanitize::decode(App::getFromRequest("url"));
    $status = App::getFromRequest("status");
    $statusStyle = App::getFromRequest("statusStyle");
    $notificationAttributes = App::getFromRequest("attributes");
    if (!is_array($notificationAttributes)) {
        $notificationAttributes = [];
    }
    if (!$identifier) {
        $apiresults = ["result" => "error", "message" => "API Notification Events require a identifier string to be passed."];
        return NULL;
    }
    if (!$title) {
        $apiresults = ["result" => "error", "message" => "API Notification Events require a title to be provided."];
        return NULL;
    }
    if (!$message) {
        $apiresults = ["result" => "error", "message" => "API Notification Events require a message to be provided."];
        return NULL;
    }
    $parameters = ["identifier" => $identifier, "title" => $title, "message" => $message, "url" => $url, "status" => $status, "statusStyle" => $statusStyle, "attributes" => $notificationAttributes];
    try {
        WHMCS\Notification\Events::trigger(WHMCS\Notification\Events::API, "api_call", $parameters);
        $apiresults = ["result" => "success", "message" => "Notification Event Triggered"];
        return NULL;
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Notification failed to send: " . $e->getMessage()];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>