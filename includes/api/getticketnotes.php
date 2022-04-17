<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$notes = [];
$result = select_query("tblticketnotes", "id,admin,date,message,attachments,attachments_removed", ["ticketid" => $ticketid], "date", "ASC");
while ($data = mysql_fetch_assoc($result)) {
    $data["attachments_removed"] = stringLiteralToBool($data["attachments_removed"]);
    $notes[] = $data;
}
$apiresults = ["result" => "success", "totalresults" => count($notes), "notes" => ["note" => $notes]];
$responsetype = "xml";

?>