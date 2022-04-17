<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $replyId = (int) App::getFromRequest("replyid");
    $message = App::getFromRequest("message");
    $created = App::getFromRequest("created");
    if (!$replyId) {
        $apiresults = ["result" => "error", "message" => "Reply ID Required"];
        return NULL;
    }
    if (!$message) {
        $apiresults = ["result" => "error", "message" => "Message is Required"];
        return NULL;
    }
    if ($created) {
        try {
            $created = WHMCS\Carbon::parse($created);
            $timeDateNow = WHMCS\Carbon::now();
            if (!$created->lte($timeDateNow)) {
                $apiresults = ["result" => "error", "message" => "Reply creation date cannot be in the future"];
                return NULL;
            }
        } catch (Exception $e) {
            $apiresults = ["result" => "error", "message" => "Invalid Date Format"];
            return NULL;
        }
    }
    if ($replyId) {
        try {
            $reply = WHMCS\Support\Ticket\Reply::findOrFail($replyId);
        } catch (Exception $e) {
            $apiresults = ["result" => "error", "message" => "Reply ID Not Found"];
            return NULL;
        }
    }
    $reply->message = $message;
    if (App::isInRequest("markdown")) {
        $useMarkdown = (int) App::getFromRequest("markdown");
        $editor = "plain";
        if ($useMarkdown) {
            $editor = "markdown";
        }
        $reply->editor = $editor;
    }
    if ($created && $created instanceof WHMCS\Carbon) {
        $reply->date = $created;
    }
    $reply->save();
    $apiresults = ["result" => "success", "replyid" => $replyId];
}
exit("This file cannot be accessed directly");

?>