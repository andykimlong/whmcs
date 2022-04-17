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
$query = WHMCS\Mail\Template::query();
if ($type) {
    $query->where("type", "=", $type);
}
if ($language) {
    $query->where("language", "=", $language);
} else {
    $query->where("language", "=", "");
}
$templates = $query->orderBy("name")->get();
$apiresults = ["result" => "success", "totalresults" => $templates->count(), "emailtemplates" => ["emailtemplate" => []]];
foreach ($templates as $template) {
    $apiresults["emailtemplates"]["emailtemplate"][] = ["id" => $template->id, "name" => $template->name, "subject" => $template->subject, "custom" => (int) $template->custom];
}
$responsetype = "xml";

?>