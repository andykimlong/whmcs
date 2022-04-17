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
$where = [];
if ($code) {
    $where["code"] = (int) $code;
} else {
    if ($id) {
        $where["id"] = (int) $id;
    }
}
$result = select_query("tblpromotions", "", $where, "code", "ASC");
$apiresults = ["result" => "success", "totalresults" => mysql_num_rows($result)];
while ($data = mysql_fetch_assoc($result)) {
    $apiresults["promotions"]["promotion"][] = $data;
}
$responsetype = "xml";

?>