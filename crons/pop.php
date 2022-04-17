<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";
require ROOTDIR . "/includes/adminfunctions.php";
require ROOTDIR . "/includes/ticketfunctions.php";
define("IN_CRON", true);
$transientData = WHMCS\TransientData::getInstance();
$transientData->delete("popCronComplete");
$whmcs = App::self();
$whmcsAppConfig = $whmcs->getApplicationConfig();
$cronOutput = [];
if (defined("PROXY_FILE")) {
    $cronOutput[] = WHMCS\Cron::getLegacyCronMessage();
}
$cronOutput[] = "<b>POP Import Log</b><br>Date: " . date("d/m/Y H:i:s") . "<hr>";
$ticketDepartments = WHMCS\Support\Department::where("host", "!=", "")->where("port", "!=", "")->where("login", "!=", "")->orderBy("order")->get();
$connectionErrors = [];
foreach ($ticketDepartments as $ticketDepartment) {
    ob_start();
    $cronOutput[] = "Host: " . $ticketDepartment->host . "<br>Email: " . $ticketDepartment->login . "<br>";
    try {
        $mailbox = WHMCS\Mail\Incoming\Mailbox::createForDepartment($ticketDepartment);
        $mailCount = $mailbox->getMessageCount();
        if (!$mailCount) {
            $cronOutput[] = "Mailbox is empty<br>";
        } else {
            $cronOutput[] = "Email Count: " . $mailCount . "<br>";
        }
        $mailParser = new ZBateson\MailMimeParser\MailMimeParser();
        foreach ($mailbox->getAllMessages() as $messageIndex => $storageMessage) {
            $toEmails = [];
            $processedCcEmails = [];
            $fromName = $fromEmail = "";
            $subject = "";
            $messageBody = "";
            $attachmentList = "";
            try {
                $message = $mailParser->parse($storageMessage->getHeaders()->toString() . Laminas\Mail\Headers::EOL . $storageMessage->getContent());
                $message->getHeader("reply-to") ? exit : $message->getHeader("from");
            } catch (Throwable $e) {
                WHMCS\Database\Capsule::table("tblticketmaillog")->insert(["date" => WHMCS\Carbon::now()->toDateTimeString(), "to" => implode(",", $toEmails), "cc" => implode(",", $processedCcEmails), "name" => $fromName, "email" => $fromEmail, "subject" => $subject, "message" => $messageBody, "status" => $e->getMessage(), "attachment" => $attachmentList]);
            }
        }
        $cronOutput[] = "<hr>";
    } catch (Exception $e) {
        $connectionErrors[] = ["department" => $ticketDepartment, "error" => $e->getMessage()];
        $cronOutput[] = $e->getMessage() . "<hr>";
        $content = ob_get_clean();
        $cronOutput[] = $content;
    }
}
if (0 < count($connectionErrors)) {
    $connectionErrorsString = "";
    foreach ($connectionErrors as $connectionError) {
        $connectionErrorsString .= "<br>" . $connectionError["department"]->name;
        $connectionErrorsString .= " &lt;" . $connectionError["department"]->email . "&gt;<br>";
        $connectionErrorsString .= "Error: " . $connectionError["error"] . "<br>";
        $connectionErrorsString .= "-----";
    }
    $failureMessage = "<p>One or more POP3 connections failed:<br><br>-----" . $connectionErrorsString . "<br></p>";
    try {
        sendAdminNotification("system", "POP3 Connection Error", $failureMessage);
    } catch (Exception $e) {
    }
}
if (WHMCS\Environment\Php::isCli() || DI::make("config")->pop_cron_debug) {
    $output = implode("", $cronOutput);
    if (WHMCS\Environment\Php::isCli()) {
        $output = strip_tags(str_replace(["<br>", "<hr>"], ["\n", "\n---\n"], $output));
    }
    echo $output;
}
$transientData->store("popCronComplete", "true", 3600);
run_hook("PopEmailCollectionCronCompleted", ["connectionErrors" => $connectionErrors]);

?>