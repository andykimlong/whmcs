<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("View Activity Log");
$aInt->title = $aInt->lang("system", "activitylog");
$aInt->sidebar = "logs";
$aInt->icon = "logs";
ob_start();
echo $aInt->beginAdminTabs([$aInt->lang("global", "searchfilter")]);
echo "\n<form method=\"post\" action=\"systemactivitylog.php\">\n\n<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n    <tr>\n        <td width=\"15%\" class=\"fieldlabel\">";
echo $aInt->lang("fields", "date");
echo "</td>\n        <td class=\"fieldarea\">\n            <div class=\"form-group date-picker-prepend-icon\">\n                <label for=\"inputDate\" class=\"field-icon\">\n                    <i class=\"fal fa-calendar-alt\"></i>\n                </label>\n                <input id=\"inputDate\"\n                       type=\"text\"\n                       name=\"date\"\n                       value=\"";
echo App::getFromRequest("date");
echo "\"\n                       class=\"form-control date-picker-single\"\n                />\n            </div>\n        </td>\n        <td width=\"15%\" class=\"fieldlabel\">\n            ";
echo $aInt->lang("fields", "username");
echo "        </td>\n        <td class=\"fieldarea\">\n            <select name=\"username\" class=\"form-control select-inline\">\n                <option value=\"\">";
echo $aInt->lang("global", "any");
echo "</option>";
$query = "SELECT DISTINCT user FROM tblactivitylog ORDER BY user ASC";
$result = full_query($query);
while ($data = mysql_fetch_array($result)) {
    $user = $data["user"];
    echo "<option";
    if ($user == $whmcs->get_req_var("username")) {
        echo " selected";
    }
    echo ">" . $user . "</option>";
}
echo "            </select>\n        </td>\n    </tr>\n    <tr>\n        <td class=\"fieldlabel\">";
echo $aInt->lang("fields", "description");
echo "</td>\n        <td class=\"fieldarea\">\n            <input type=\"text\" name=\"description\" value=\"";
echo $whmcs->get_req_var("description");
echo "\" class=\"form-control\">\n        </td>\n        <td class=\"fieldlabel\">\n            ";
echo $aInt->lang("fields", "ipaddress");
echo "        </td>\n        <td class=\"fieldarea\">\n            <input type=\"text\" name=\"ipaddress\" value=\"";
echo $whmcs->get_req_var("ipaddress");
echo "\" class=\"form-control input-150\">\n        </td>\n    </tr>\n</table>\n\n<div class=\"btn-container\">\n    <input type=\"submit\" value=\"";
echo $aInt->lang("system", "filterlog");
echo "\" class=\"btn btn-default\" />\n</div>\n\n</form>\n\n";
echo $aInt->endAdminTabs();
echo "\n<br />\n\n";
$aInt->sortableTableInit("date");
$log = new WHMCS\Log\Activity();
$log->prune();
$log->setCriteria(["date" => $whmcs->get_req_var("date"), "username" => $whmcs->get_req_var("username"), "description" => $whmcs->get_req_var("description"), "ipaddress" => $whmcs->get_req_var("ipaddress")]);
$numrows = $log->getTotalCount();
$tabledata = [];
$logs = collect($log->getLogEntries($whmcs->get_req_var("page")));
$clientsMap = WHMCS\User\Client::whereIn("id", $logs->pluck("clientId"))->pluck("email", "id");
$usersMap = WHMCS\User\User::whereIn("id", $logs->pluck("userId"))->pluck("email", "id");
$adminsMap = WHMCS\User\Admin::whereIn("id", $logs->pluck("adminId"))->pluck("email", "id");
foreach ($logs as $entry) {
    if (0 < $entry["adminId"]) {
        $userId = $entry["adminId"];
        $userType = AdminLang::trans("fields.adminId");
        $userLabel = getfrommap($adminsMap, $entry["adminId"], "Missing Admin");
    } else {
        if (0 < $entry["userId"]) {
            $userId = $entry["userId"];
            $userType = AdminLang::trans("fields.userId");
            $userLabel = getfrommap($usersMap, $entry["userId"], "Missing User");
        } else {
            $userId = "";
            $userType = AdminLang::trans("global.userSystem");
            $userLabel = "-";
        }
    }
    $affectedClient = "";
    if (0 < $entry["clientId"]) {
        $email = getfrommap($clientsMap, $entry["clientId"], "Missing Client");
        $affectedClient = "<a href=\"clientssummary.php?userid=" . $entry["clientId"] . "\" title=\"" . $email . "\">" . AdminLang::trans("fields.clientid") . " " . $entry["clientId"] . "</a>" . "<div class=\"truncate\" style=\"max-width:200px;color:#bbb;\">" . $email . "</div>";
    } else {
        $affectedClient = "<em>" . AdminLang::trans("global.none") . "</em>";
    }
    $tabledata[] = [$entry["date"], "<div align=\"left\">" . $entry["description"] . "</div>", "<small>" . $affectedClient . "</small>", "<small>" . $userType . " " . $userId . "<br>" . "<div class=\"truncate\" style=\"max-width:200px;color:#bbb;\">" . $userLabel . "</div></small>", "<small>" . $entry["ipaddress"] . "</small>"];
}
echo $aInt->sortableTable([["", $aInt->lang("fields", "date"), 150], $aInt->lang("fields", "logEntry"), ["", $aInt->lang("fields", "client"), 220], ["", $aInt->lang("fields", "user"), 220], ["", $aInt->lang("fields", "ipaddress"), 120]], $tabledata);
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();
function getFromMap($map, $id, $fallbackLabel)
{
    if ($id === 0) {
        return "-";
    }
    if ($map->has($id)) {
        return $map[$id];
    }
    return $fallback . " " . $id;
}

?>