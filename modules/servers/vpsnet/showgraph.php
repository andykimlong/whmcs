<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require "../../../init.php";
require "vpsnet.php";
if (!$_SESSION["uid"]) {
    exit("Access Denied");
}
$serviceid = (int) App::getFromRequest("serviceid");
$addonid = (int) App::getFromRequest("addonid");
$count = 0;
if ($addonid) {
    $count = get_query_val("tblhostingaddons", "count(*)", ["id" => $addonid, "userid" => $_SESSION["uid"]]);
} else {
    $count = get_query_val("tblhosting", "count(*)", ["id" => $serviceid, "userid" => $_SESSION["uid"]]);
}
if (!$count) {
    exit("Access Denied");
}
$creds = vpsnet_GetCredentials();
$api = VPSNET::getInstance($creds["username"], $creds["accesshash"]);
$result = select_query("mod_vpsnet", "", ["relid" => $serviceid, "addon_id" => $addonid]);
while ($data = mysql_fetch_array($result)) {
    ${$data}["setting"] = $data["value"];
}
if (!in_array($period, ["hourly", "daily", "weekly", "monthly"])) {
    $period = "hourly";
}
$postfields = new VirtualMachine();
$postfields->id = $netid;
try {
    if ($graph == "cpu") {
        $result = $postfields->showCPUGraph($period);
    } else {
        $result = $postfields->showNetworkGraph($period);
    }
    $output = $result["response_body"];
    echo $output;
} catch (Exception $e) {
    return "Caught exception: " . $e->getMessage();
}

?>