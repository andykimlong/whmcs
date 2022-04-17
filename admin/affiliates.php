<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("Manage Affiliates");
$aInt->title = $aInt->lang("affiliates", "title");
$aInt->sidebar = "clients";
$aInt->icon = "affiliates";
$aInt->helplink = "Affiliates";
$aInt->requiredFiles(["invoicefunctions", "gatewayfunctions"]);
if ($action == "save") {
    check_token("WHMCS.admin.default");
    update_query("tblaffiliates", ["paytype" => $paymenttype, "payamount" => $payamount, "onetime" => $onetime, "visitors" => $visitors, "balance" => $balance, "withdrawn" => $withdrawn], ["id" => $id]);
    logActivity("Affiliate ID " . $id . " Details Updated");
    redir("action=edit&id=" . $id);
}
if ($action == "deletecommission") {
    check_token("WHMCS.admin.default");
    delete_query("tblaffiliatespending", ["id" => $cid]);
    redir("action=edit&id=" . $id);
}
if ($action == "deletehistory") {
    check_token("WHMCS.admin.default");
    delete_query("tblaffiliateshistory", ["id" => $hid]);
    redir("action=edit&id=" . $id);
}
if ($action == "deletereferral") {
    check_token("WHMCS.admin.default");
    delete_query("tblaffiliatesaccounts", ["id" => $affaccid]);
    redir("action=edit&id=" . $id);
}
if ($action == "deletewithdrawal") {
    check_token("WHMCS.admin.default");
    delete_query("tblaffiliateswithdrawals", ["id" => $wid]);
    redir("action=edit&id=" . $id);
}
if ($action == "addcomm") {
    check_token("WHMCS.admin.default");
    $amount = format_as_currency($amount);
    insert_query("tblaffiliateshistory", ["affiliateid" => $id, "date" => toMySQLDate($date), "affaccid" => $refid, "description" => $description, "amount" => $amount]);
    update_query("tblaffiliates", ["balance" => "+=" . $amount], ["id" => (int) $id]);
    logActivity("Manual Commission Added to Affiliate - Affiliate ID: " . $id);
    redir("action=edit&id=" . $id);
}
if ($action == "withdraw") {
    check_token("WHMCS.admin.default");
    insert_query("tblaffiliateswithdrawals", ["affiliateid" => $id, "date" => "now()", "amount" => $amount]);
    update_query("tblaffiliates", ["balance" => "-=" . $amount, "withdrawn" => "+=" . $amount], ["id" => (int) $id]);
    if ($payouttype == "1") {
        $result = select_query("tblaffiliates", "", ["id" => (int) $id]);
        $data = mysql_fetch_array($result);
        $id = (int) $data["id"];
        $clientid = (int) $data["clientid"];
        addTransaction($clientid, "", "Affiliate Commissions Withdrawal Payout", "0", "0", $amount, $paymentmethod, $transid);
    } else {
        if ($payouttype == "2") {
            $result = select_query("tblaffiliates", "", ["id" => (int) $id]);
            $data = mysql_fetch_array($result);
            $id = (int) $data["id"];
            $clientid = (int) $data["clientid"];
            insert_query("tblcredit", ["clientid" => $clientid, "date" => "now()", "description" => "Affiliate Commissions Withdrawal", "amount" => $amount]);
            update_query("tblclients", ["credit" => "+=" . $amount], ["id" => $clientid]);
            logActivity("Processed Affiliate Commissions Withdrawal to Credit Balance - User ID: " . $clientid . " - Amount: " . $amount);
        }
    }
    redir("action=edit&id=" . $id);
}
if ($sub == "delete") {
    check_token("WHMCS.admin.default");
    delete_query("tblaffiliates", ["id" => $ide]);
    logActivity("Affiliate " . $ide . " Deleted");
    redir();
}
ob_start();
if ($action == "") {
    $aInt->sortableTableInit("clientname", "ASC");
    $query = "FROM `tblaffiliates` INNER JOIN tblclients ON tblclients.id=tblaffiliates.clientid WHERE tblaffiliates.id!=''";
    if ($client) {
        $query .= " AND concat(firstname,' ',lastname) LIKE '%" . db_escape_string($client) . "%'";
    }
    if ($visitors) {
        $visitorstype = $visitorstype == "greater" ? ">" : "<";
        $query .= " AND visitors " . $visitorstype . " '" . db_escape_string($visitors) . "'";
    }
    if ($balance) {
        $balancetype = $balancetype == "greater" ? ">" : "<";
        $query .= " AND balance " . $balancetype . " '" . db_escape_string($balance) . "'";
    }
    if ($withdrawn) {
        $withdrawntype = $withdrawntype == "greater" ? ">" : "<";
        $query .= " AND withdrawn " . $withdrawntype . " '" . db_escape_string($withdrawn) . "'";
    }
    $result = full_query("SELECT COUNT(tblaffiliates.id) " . $query);
    $data = mysql_fetch_array($result);
    $numrows = $data[0];
    $aInt->deleteJSConfirm("doDelete", "affiliates", "deletesure", "affiliates.php?sub=delete&ide=");
    echo $aInt->beginAdminTabs([$aInt->lang("global", "searchfilter")]);
    echo "\n<form action=\"";
    echo $whmcs->getPhpSelf();
    echo "\" method=\"get\">\n\n<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n<tr><td width=\"15%\" class=\"fieldlabel\">";
    echo $aInt->lang("fields", "clientname");
    echo "</td><td class=\"fieldarea\"><input type=\"text\" name=\"client\" class=\"form-control input-250\" value=\"";
    echo $client;
    echo "\"></td><td width=\"10%\" class=\"fieldlabel\">";
    echo $aInt->lang("fields", "balance");
    echo "</td><td class=\"fieldarea\"><select name=\"balancetype\" class=\"form-control select-inline\"><option value=\"greater\">";
    echo $aInt->lang("affiliates", "greaterthan");
    echo "<option>";
    echo $aInt->lang("affiliates", "lessthan");
    echo "</select> <input type=\"text\" name=\"balance\" class=\"form-control input-100 input-inline\" value=\"";
    echo $balance;
    echo "\"></td></tr>\n<tr><td class=\"fieldlabel\">";
    echo $aInt->lang("affiliates", "visitorsref");
    echo "</td><td class=\"fieldarea\"><select name=\"visitorstype\" class=\"form-control select-inline\"><option value=\"greater\">";
    echo $aInt->lang("affiliates", "greaterthan");
    echo "<option>";
    echo $aInt->lang("affiliates", "lessthan");
    echo "</select> <input type=\"text\" name=\"visitors\" class=\"form-control input-100 input-inline\" value=\"";
    echo $visitors;
    echo "\"></td><td class=\"fieldlabel\">";
    echo $aInt->lang("affiliates", "withdrawn");
    echo "</td><td class=\"fieldarea\"><select name=\"withdrawntype\" class=\"form-control select-inline\"><option value=\"greater\">";
    echo $aInt->lang("affiliates", "greaterthan");
    echo "<option>";
    echo $aInt->lang("affiliates", "lessthan");
    echo "</select> <input type=\"text\" name=\"withdrawn\" class=\"form-control input-100 input-inline\" value=\"";
    echo $withdrawn;
    echo "\"></td></tr>\n</table>\n\n<div class=\"btn-container\">\n    <input type=\"submit\" value=\"";
    echo $aInt->lang("global", "search");
    echo "\" class=\"btn btn-default\">\n</div>\n\n</form>\n\n";
    echo $aInt->endAdminTabs();
    echo "\n<br>\n\n";
    if (!($orderby == "id" || $orderby == "date" || $orderby == "clientname" || $orderby == "visitors" || $orderby == "balance" || $orderby == "withdrawn")) {
        $orderby = "clientname";
    }
    $query .= " ORDER BY ";
    $query .= $orderby == "clientname" ? "tblclients.firstname " . $order . ",tblclients.lastname" : $orderby;
    $query .= " " . $order;
    $query = "SELECT tblaffiliates.*,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid,tblclients.currency,(SELECT COUNT(*) FROM tblaffiliatesaccounts WHERE tblaffiliatesaccounts.affiliateid=tblaffiliates.id) AS signups " . $query . " LIMIT " . (int) ($page * $limit) . "," . (int) $limit;
    $result = full_query($query);
    while ($data = mysql_fetch_array($result)) {
        $id = $data["id"];
        $date = $data["date"];
        $userid = $data["clientid"];
        $visitors = $data["visitors"];
        $balance = $data["balance"];
        $withdrawn = $data["withdrawn"];
        $firstname = $data["firstname"];
        $lastname = $data["lastname"];
        $companyname = $data["companyname"];
        $groupid = $data["groupid"];
        $currency = $data["currency"];
        $signups = $data["signups"];
        $currency = getCurrency(NULL, $currency);
        $balance = formatCurrency($balance);
        $withdrawn = formatCurrency($withdrawn);
        $date = fromMySQLDate($date);
        $tabledata[] = ["<input type=\"checkbox\" name=\"selectedclients[]\" value=\"" . $id . "\" class=\"checkall\" />", "<a href=\"affiliates.php?action=edit&id=" . $id . "\">" . $id . "</a>", $date, $aInt->outputClientLink($userid, $firstname, $lastname, $companyname, $groupid), $visitors, $signups, $balance, $withdrawn, "<a href=\"?action=edit&id=" . $id . "\"><img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $id . "');return false\"><img src=\"images/delete.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("global", "delete") . "\"></a>"];
    }
    $tableformurl = "sendmessage.php?type=affiliate&multiple=true";
    $tableformbuttons = "<input type=\"submit\" value=\"" . $aInt->lang("global", "sendmessage") . "\" class=\"button btn btn-default\">";
    echo $aInt->sortableTable(["checkall", ["id", $aInt->lang("fields", "id")], ["date", $aInt->lang("affiliates", "signupdate")], ["clientname", $aInt->lang("fields", "clientname")], ["visitors", $aInt->lang("affiliates", "visitorsref")], $aInt->lang("affiliates", "signups"), ["balance", $aInt->lang("fields", "balance")], ["withdrawn", $aInt->lang("affiliates", "withdrawn")], "", ""], $tabledata, $tableformurl, $tableformbuttons);
} else {
    if ($action == "edit") {
        if ($pay == "true") {
            $error = AffiliatePayment($affaccid, "");
            if ($error) {
                infoBox($aInt->lang("affiliates", "paymentfailed"), $error);
            } else {
                infoBox($aInt->lang("affiliates", "paymentsuccess"), $aInt->lang("affiliates", "paymentsuccessdetail"));
                logActivity("Manual Payout to Affiliate - Affiliate ID: " . $id . " - Service ID: " . $serviceid, $userid);
            }
        }
        echo $infobox;
        $result = select_query("tblaffiliates", "", ["id" => $id]);
        $data = mysql_fetch_array($result);
        $id = $data["id"];
        if (!$id) {
            $aInt->gracefulExit("Invalid Affiliate ID. Please Try Again...");
        }
        $date = $data["date"];
        $affiliateClientID = $data["clientid"];
        $visitors = $data["visitors"];
        $balance = $data["balance"];
        $withdrawn = $data["withdrawn"];
        $paymenttype = $data["paytype"];
        $payamount = $data["payamount"];
        $onetime = $data["onetime"];
        $result = select_query("tblclients", "", ["id" => $affiliateClientID]);
        $data = mysql_fetch_array($result);
        $firstname = $data["firstname"];
        $lastname = $data["lastname"];
        $result = select_query("tblaffiliatesaccounts", "COUNT(id)", ["affiliateid" => $id]);
        $data = mysql_fetch_array($result);
        $signups = $data[0];
        $result = select_query("tblaffiliatespending", "COUNT(*),SUM(tblaffiliatespending.amount)", ["affiliateid" => $id], "clearingdate", "DESC", "", "tblaffiliatesaccounts ON tblaffiliatesaccounts.id=tblaffiliatespending.affaccid INNER JOIN tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid");
        $data = mysql_fetch_array($result);
        list($pendingcommissions, $pendingcommissionsamount) = $data;
        $currency = getCurrency($affiliateClientID);
        $date = fromMySQLDate($date);
        $pendingcommissionsamount = formatCurrency($pendingcommissionsamount);
        $conversionrate = round($signups / $visitors * 100, 2);
        $aInt->deleteJSConfirm("doAccDelete", "affiliates", "refdeletesure", "affiliates.php?action=deletereferral&id=" . $id . "&affaccid=");
        $aInt->deleteJSConfirm("doPendingCommissionDelete", "affiliates", "pendeletesure", "affiliates.php?action=deletecommission&id=" . $id . "&cid=");
        $aInt->deleteJSConfirm("doAffHistoryDelete", "affiliates", "pytdeletesure", "affiliates.php?action=deletehistory&id=" . $id . "&hid=");
        $aInt->deleteJSConfirm("doWithdrawHistoryDelete", "affiliates", "witdeletesure", "affiliates.php?action=deletewithdrawal&id=" . $id . "&wid=");
        echo "\n<form method=\"post\" action=\"";
        echo $whmcs->getPhpSelf();
        echo "?action=save&id=";
        echo $id;
        echo "\">\n\n    <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "id");
        echo "            </td>\n            <td class=\"fieldarea\">\n                ";
        echo $id;
        echo "            </td>\n            <td width=\"20%\"\n                class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "signupdate");
        echo "            </td>\n            <td class=\"fieldarea\">\n                ";
        echo $date;
        echo "            </td>\n        </tr>\n        <tr>\n            <td width=\"15%\" class=\"fieldlabel\">\n                ";
        echo $aInt->lang("fields", "clientname");
        echo "            </td>\n            <td class=\"fieldarea\">\n                <a href=\"clientssummary.php?userid=";
        echo $affiliateClientID;
        echo "\">\n                    ";
        echo $firstname . " " . $lastname;
        echo "                </a>\n            </td>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "pendingcommissions");
        echo "            </td>\n            <td class=\"fieldarea\">\n                ";
        echo $pendingcommissionsamount;
        echo "            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "commissiontype");
        echo "</td>\n            <td class=\"fieldarea\">\n                <label class=\"radio-inline\">\n                    <input type=\"radio\" name=\"paymenttype\" value=\"\"";
        echo !$paymenttype ? " checked=\"checked\"" : "";
        echo ">\n                    ";
        echo $aInt->lang("affiliates", "usedefault");
        echo "                </label>\n                <label class=\"radio-inline\">\n                    <input type=\"radio\" name=\"paymenttype\" value=\"percentage\"";
        echo $paymenttype == "percentage" ? " checked=\"checked\"" : "";
        echo ">\n                    ";
        echo $aInt->lang("affiliates", "percentage");
        echo "                </label>\n                <label class=\"radio-inline\">\n                    <input type=\"radio\" name=\"paymenttype\" value=\"fixed\"";
        echo $paymenttype == "fixed" ? " checked=\"checked\"" : "";
        echo ">\n                    ";
        echo $aInt->lang("affiliates", "fixedamount");
        echo "                </label>\n            </td>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "availablebalance");
        echo "            </td>\n            <td class=\"fieldarea\">\n                <input type=\"number\" name=\"balance\" class=\"form-control input-100\" value=\"";
        echo $balance;
        echo "\" step=\"0.01\">\n            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">";
        echo $aInt->lang("affiliates", "commissionamount");
        echo "</td>\n            <td class=\"fieldarea\">\n                <input type=\"number\" name=\"payamount\" class=\"form-control input-inline input-100 \" value=\"";
        echo $payamount;
        echo "\" step=\"0.01\">\n                <label class=\"checkbox-inline\">\n                    <input type=\"checkbox\" name=\"onetime\" id=\"onetime\" value=\"1\"";
        echo $onetime ? " checked=\"checked\"" : "";
        echo " />\n                    Pay One Time Only\n                </label>\n            </td>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "withdrawnamount");
        echo "            </td>\n            <td class=\"fieldarea\">\n                <input type=\"number\" name=\"withdrawn\" class=\"form-control input-100\" value=\"";
        echo $withdrawn;
        echo "\" step=\"0.01\">\n            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "visitorsref");
        echo "            </td>\n            <td class=\"fieldarea\">\n                <input type=\"number\" name=\"visitors\" class=\"form-control input-75\" value=\"";
        echo $visitors;
        echo "\">\n            </td>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "conversionrate");
        echo "            </td>\n            <td class=\"fieldarea\">\n                ";
        echo $conversionrate;
        echo "%\n            </td>\n        </tr>\n    </table>\n\n<div class=\"btn-container\">\n    <input type=\"submit\" value=\"";
        echo $aInt->lang("global", "savechanges");
        echo "\" class=\"btn btn-primary\">\n    <input type=\"reset\" value=\"";
        echo $aInt->lang("global", "cancelchanges");
        echo "\" class=\"btn btn-default\" />\n</div>\n\n</form>\n\n";
        echo $aInt->beginAdminTabs([$aInt->lang("affiliates", "referrals"), $aInt->lang("affiliates", "referredsignups"), $aInt->lang("affiliates", "pendingcommissions") . " (" . $pendingcommissions . ")", $aInt->lang("affiliates", "commissionshistory"), $aInt->lang("affiliates", "withdrawalshistory")], true);
        $referralTimePeriods = ["30" => "30 Days", "60" => "60 Days", "90" => "90 Days", "180" => "180 Days"];
        $days = (int) App::getFromRequest("days");
        if (!$days) {
            $days = key($referralTimePeriods);
        }
        echo "<div class=\"text-right\"><strong>Time Period</strong> <div class=\"btn-group\" role=\"group\">";
        foreach ($referralTimePeriods as $referralDays => $referralLabel) {
            echo "<a href=\"affiliates.php?action=edit&id=" . $id . "&days=" . $referralDays . "\" class=\"btn btn-default" . ($days == $referralDays ? " active" : "") . "\">" . $referralLabel . "</a>";
        }
        echo "</div></div>";
        $chartData = [];
        $hitData = [];
        $referrers = WHMCS\Database\Capsule::table("tblaffiliates_hits")->join("tblaffiliates_referrers", "tblaffiliates_referrers.id", "=", "tblaffiliates_hits.referrer_id")->where("tblaffiliates_hits.affiliate_id", "=", $id)->where("tblaffiliates_hits.created_at", ">", WHMCS\Carbon::now()->subDays($days)->toDateTimeString())->groupBy(WHMCS\Database\Capsule::raw("date_format(tblaffiliates_hits.created_at, '%D %M %Y')"))->orderBy("tblaffiliates_hits.created_at", "DESC")->selectRaw("tblaffiliates_hits.created_at,COUNT(tblaffiliates_hits.id) as hits")->pluck("hits", "created_at")->all();
        foreach ($referrers as $created => $referrer) {
            $hitData[substr($created, 0, 10)] = $referrer;
        }
        for ($chartDay = 1; $chartDay <= $days; $chartDay++) {
            $chartData["rows"][] = ["c" => [["v" => WHMCS\Carbon::now()->subDays($days - $chartDay)->format("jS F Y")], ["v" => isset($hitData[WHMCS\Carbon::now()->subDays($days - $chartDay)->toDateString()]) ? $hitData[WHMCS\Carbon::now()->subDays($days - $chartDay)->toDateString()] : 0]]];
        }
        $chartData["cols"][] = ["label" => AdminLang::trans("fields.date"), "type" => "string"];
        $chartData["cols"][] = ["label" => AdminLang::trans("affiliates.numberOfHits"), "type" => "number"];
        echo (new WHMCS\Chart())->drawChart("Area", $chartData, [], "400px") . "<br>";
        $referrers = WHMCS\Database\Capsule::table("tblaffiliates_hits")->join("tblaffiliates_referrers", "tblaffiliates_referrers.id", "=", "tblaffiliates_hits.referrer_id")->where("tblaffiliates_hits.affiliate_id", "=", $id)->where("tblaffiliates_hits.created_at", ">", WHMCS\Carbon::now()->subDays($days)->toDateTimeString())->groupBy("tblaffiliates_hits.referrer_id")->orderBy("hits", "DESC")->selectRaw("referrer,COUNT(tblaffiliates_hits.id) as hits")->pluck("hits", "referrer")->all();
        $aInt->sortableTableInit("nopagination");
        $tabledata = [];
        foreach ($referrers as $referrer => $hits) {
            if (!trim($referrer)) {
                $referrer = AdminLang::trans("affiliates.noReferrer");
            } else {
                if (120 < strlen($referrer)) {
                    $referrer = substr($referrer, 0, 120) . "... <a href=\"#\">Reveal</a>";
                }
            }
            $tabledata[] = [$referrer, $hits];
        }
        echo $aInt->sortableTable([AdminLang::trans("affiliates.referrerUrl"), AdminLang::trans("affiliates.numberOfHits")], $tabledata);
        echo $aInt->nextAdminTab();
        $aInt->sortableTableInit("regdate", "DESC");
        $tabledata = [];
        $mysql_errors = true;
        $numrows = get_query_val("tblaffiliatesaccounts", "COUNT(*)", ["tblaffiliatesaccounts.affiliateid" => $id], "", "", "", "tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid");
        if (!($orderby == "id" || $orderby == "regdate" || $orderby == "clientname" || $orderby == "name" || $orderby == "lastpaid" || $orderby == "domainstatus")) {
            $orderby = "regdate";
        }
        $result = select_query("tblaffiliatesaccounts", "tblaffiliatesaccounts.id,tblaffiliatesaccounts.lastpaid,tblaffiliatesaccounts.relid, concat(tblclients.firstname,' ',tblclients.lastname,'|||',tblclients.currency) as clientname,tblproducts.name,tblhosting.userid,tblhosting.domainstatus,tblhosting.domain,tblhosting.amount,tblhosting.firstpaymentamount,tblhosting.regdate,tblhosting.billingcycle", ["tblaffiliatesaccounts.affiliateid" => $id], (int) $orderby, (int) $order, $page * $limit . "," . $limit, "tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid");
        while ($data = mysql_fetch_array($result)) {
            $affaccid = $data["id"];
            $lastpaid = $data["lastpaid"];
            $relid = $data["relid"];
            $clientname = $data["clientname"];
            $clientname = explode("|||", $clientname, 2);
            list($clientname, $referralCurrency) = $clientname;
            $userid = $data["userid"];
            $firstpaymentamount = $data["firstpaymentamount"];
            $amount = $data["amount"];
            $domain = $data["domain"];
            $date = $data["regdate"];
            $product = $data["name"];
            $billingcycle = $data["billingcycle"];
            $status = $data["domainstatus"];
            $currency = getCurrency($affiliateClientID);
            $commission = calculateAffiliateCommission($id, $relid, $lastpaid);
            $commission = formatCurrency($commission);
            $currency = getCurrency(NULL, $referralCurrency);
            if ($billingcycle == "Free" || $billingcycle == "Free Account") {
                $amountdesc = "Free";
            } else {
                if ($billingcycle == "One Time") {
                    $amountdesc = formatCurrency($firstpaymentamount) . " " . $billingcycle;
                } else {
                    $amountdesc = $firstpaymentamount != $amount ? formatCurrency($firstpaymentamount) . " " . $aInt->lang("affiliates", "initiallythen") . " " : "";
                    $amountdesc .= formatCurrency($amount) . " " . $billingcycle;
                }
            }
            $date = fromMySQLDate($date);
            if (!$domain) {
                $domain = "";
            }
            if ($lastpaid == "0000-00-00") {
                $lastpaid = $aInt->lang("affiliates", "never");
            } else {
                $lastpaid = fromMySQLDate($lastpaid);
            }
            $tabledata[] = [$affaccid, $date, "<a href=\"clientssummary.php?userid=" . $userid . "\">" . $clientname . "</a>", "<a href=\"clientshosting.php?userid=" . $userid . "&id=" . $relid . "\">" . $product . "</a><br>" . $amountdesc, $commission, $lastpaid, $status, "<a href=\"affiliates.php?action=edit&id=" . $id . "&pay=true&affaccid=" . $affaccid . "&serviceid=" . $relid . "&userid=" . $userid . "\">" . $aInt->lang("affiliates", "manual") . "<br>" . $aInt->lang("affiliates", "payout") . "</a>", "<a href=\"#\" onClick=\"doAccDelete('" . $affaccid . "');return false\"><img src=\"images/delete.gif\" border=\"0\"></a>"];
        }
        echo $aInt->sortableTable([["id", $aInt->lang("fields", "id")], ["regdate", $aInt->lang("affiliates", "signupdate")], ["clientname", $aInt->lang("fields", "clientname")], ["name", $aInt->lang("fields", "product")], $aInt->lang("affiliates", "commission"), ["lastpaid", $aInt->lang("affiliates", "lastpaid")], ["domainstatus", $aInt->lang("affiliates", "productstatus")], " ", ""], $tabledata, $tableformurl, $tableformbuttons);
        echo $aInt->nextAdminTab();
        $currency = getCurrency($affiliateClientID);
        $aInt->sortableTableInit("nopagination");
        $tabledata = [];
        $result = select_query("tblaffiliatespending", "tblaffiliatespending.id,tblaffiliatespending.affaccid,tblaffiliatespending.amount,tblaffiliatespending.clearingdate,tblaffiliatesaccounts.relid,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblproducts.name,tblhosting.userid,tblhosting.domainstatus,tblhosting.billingcycle", ["affiliateid" => $id], "clearingdate", "ASC", "", "tblaffiliatesaccounts ON tblaffiliatesaccounts.id=tblaffiliatespending.affaccid INNER JOIN tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid");
        while ($data = mysql_fetch_array($result)) {
            $pendingid = $data["id"];
            $affaccid = $data["affaccid"];
            $amount = $data["amount"];
            $clearingdate = $data["clearingdate"];
            $relid = $data["relid"];
            $firstname = $data["firstname"];
            $lastname = $data["lastname"];
            $companyname = $data["companyname"];
            $userid = $data["userid"];
            $product = $data["name"];
            $billingcycle = $data["billingcycle"];
            $status = $data["domainstatus"];
            $clearingdate = fromMySQLDate($clearingdate);
            $amount = formatCurrency($amount);
            $tabledata[] = [$affaccid, $aInt->outputClientLink($userid, $firstname, $lastname, $companyname), "<a href=\"clientshosting.php?userid=" . $userid . "&id=" . $relid . "\">" . $product . "</a>", $status, $amount, $clearingdate, "<a href=\"#\" onClick=\"doPendingCommissionDelete('" . $pendingid . "');return false\"><img src=\"images/delete.gif\" border=\"0\"></a>"];
        }
        echo $aInt->sortableTable([$aInt->lang("affiliates", "refid"), $aInt->lang("fields", "clientname"), $aInt->lang("fields", "product"), $aInt->lang("affiliates", "productstatus"), $aInt->lang("fields", "amount"), $aInt->lang("affiliates", "clearingdate"), ""], $tabledata);
        echo $aInt->nextAdminTab();
        $aInt->sortableTableInit("nopagination");
        $tabledata = [];
        $result = select_query("tblaffiliateshistory", "tblaffiliateshistory.*,(SELECT CONCAT(tblclients.id,'|||',tblclients.firstname,'|||',tblclients.lastname,'|||',tblclients.companyname,'|||',tblproducts.name,'|||',tblhosting.id,'|||',tblhosting.billingcycle,'|||',tblhosting.domainstatus) FROM tblaffiliatesaccounts INNER JOIN tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid WHERE tblaffiliatesaccounts.id=tblaffiliateshistory.affaccid) AS referraldata", ["affiliateid" => $id], "date", "DESC");
        while ($data = mysql_fetch_array($result)) {
            $historyid = $data["id"];
            $date = $data["date"];
            $affaccid = $data["affaccid"];
            $description = $data["description"];
            $amount = $data["amount"];
            $referraldata = $data["referraldata"];
            $referraldata = explode("|||", $referraldata);
            $userid = $firstname = $lastname = $companyname = $product = $relid = $billingcycle = $status = "";
            if ($affaccid) {
                list($userid, $firstname, $lastname, $companyname, $product, $relid, $billingcycle, $status) = $referraldata;
            }
            $date = fromMySQLDate($date);
            $amount = formatCurrency($amount);
            if (!$description) {
                $description = "&nbsp;";
            }
            $tabledata[] = [$date, $affaccid, $aInt->outputClientLink($userid, $firstname, $lastname, $companyname), "<a href=\"clientshosting.php?userid=" . $userid . "&id=" . $relid . "\">" . $product . "</a>", $status, $description, $amount, "<a href=\"#\" onClick=\"doAffHistoryDelete('" . $historyid . "');return false\"><img src=\"images/delete.gif\" border=\"0\"></a>"];
        }
        echo $aInt->sortableTable([$aInt->lang("fields", "date"), $aInt->lang("affiliates", "refid"), $aInt->lang("fields", "clientname"), $aInt->lang("fields", "product"), $aInt->lang("affiliates", "productstatus"), "Description", $aInt->lang("fields", "amount"), ""], $tabledata);
        echo "\n<br />\n\n<form method=\"post\" action=\"";
        echo $whmcs->getPhpSelf();
        echo "?action=addcomm&id=";
        echo $id;
        echo "\">\n<p align=\"left\"><b>Add Manual Commission Entry</b></p>\n<table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n    <tr>\n        <td class=\"fieldlabel\">";
        echo $aInt->lang("fields", "date");
        echo ":</td>\n        <td class=\"fieldarea\">\n            <div class=\"form-group date-picker-prepend-icon\">\n                <label for=\"inputDate\" class=\"field-icon\">\n                    <i class=\"fal fa-calendar-alt\"></i>\n                </label>\n                <input id=\"inputDate\"\n                       type=\"text\"\n                       name=\"date\"\n                       value=\"";
        echo getTodaysDate();
        echo "\"\n                       class=\"form-control input-inline date-picker-single\"\n                />\n            </div>\n        </td>\n    </tr>\n    <tr>\n        <td class=\"fieldlabel\">Related Referral:</td>\n        <td class=\"fieldarea\">\n            <select name=\"refid\" class=\"form-control select-inline\">\n                <option value=\"\">None</option>";
        $result = select_query("tblaffiliatesaccounts", "tblaffiliatesaccounts.*,(SELECT CONCAT(tblclients.firstname,'|||',tblclients.lastname,'|||',tblhosting.userid,'|||',tblproducts.name,'|||',tblhosting.domainstatus,'|||',tblhosting.domain,'|||',tblhosting.amount,'|||',tblhosting.regdate,'|||',tblhosting.billingcycle) FROM tblhosting INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid WHERE tblhosting.id=tblaffiliatesaccounts.relid) AS referraldata", ["affiliateid" => $id]);
        while ($data = mysql_fetch_array($result)) {
            $affaccid = $data["id"];
            $lastpaid = $data["lastpaid"];
            $relid = $data["relid"];
            $referraldata = $data["referraldata"];
            $referraldata = explode("|||", $referraldata);
            list($firstname, $lastname, $userid, $product, $status, $domain, $amount, $date, $billingcycle) = $referraldata;
            if (!$domain) {
                $domain = "";
            }
            if ($lastpaid == "0000-00-00") {
                $lastpaid = $aInt->lang("affiliates", "never");
            } else {
                $lastpaid = fromMySQLDate($lastpaid);
            }
            echo "<option value=\"" . $affaccid . "\">ID " . $affaccid . " - " . $firstname . " " . $lastname . " - " . $product . "</option>";
        }
        echo "            </select>\n        </td>\n    </tr>\n    <tr>\n        <td class=\"fieldlabel\">\n            ";
        echo $aInt->lang("fields", "description");
        echo ":\n        </td>\n        <td class=\"fieldarea\">\n            <input type=\"text\" name=\"description\" class=\"form-control input-inline input-400\" /> (Optional)\n        </td>\n    </tr>\n    <tr>\n        <td class=\"fieldlabel\">\n            ";
        echo $aInt->lang("fields", "amount");
        echo ":\n        </td>\n        <td class=\"fieldarea\">\n            <input type=\"number\" name=\"amount\" class=\"form-control input-100\" value=\"0.00\" step=\"0.01\" />\n        </td>\n    </tr>\n</table>\n<div class=\"btn-container\">\n    <input type=\"submit\" value=\"";
        echo $aInt->lang("global", "submit");
        echo "\" class=\"btn btn-primary\" />\n</div>\n</form>\n\n";
        echo $aInt->nextAdminTab();
        $aInt->sortableTableInit("nopagination");
        $tabledata = [];
        $result = select_query("tblaffiliateswithdrawals", "", ["affiliateid" => $id], "id", "DESC");
        while ($data = mysql_fetch_array($result)) {
            $historyid = $data["id"];
            $date = $data["date"];
            $amount = $data["amount"];
            $date = fromMySQLDate($date);
            $amount = formatCurrency($amount);
            $tabledata[] = [$date, $amount, "<a href=\"#\" onClick=\"doWithdrawHistoryDelete('" . $historyid . "');return false\"><img src=\"images/delete.gif\" border=\"0\"></a>"];
        }
        echo $aInt->sortableTable([$aInt->lang("fields", "date"), $aInt->lang("fields", "amount"), ""], $tabledata);
        echo "\n<br />\n\n<form method=\"post\" action=\"";
        echo $whmcs->getPhpSelf();
        echo "?action=withdraw&id=";
        echo $id;
        echo "\">\n<p align=\"left\"><b>";
        echo $aInt->lang("affiliates", "makepayout");
        echo "</b></p>\n    <table class=\"form\" width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"3\">\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("fields", "amount");
        echo ":\n            </td>\n            <td class=\"fieldarea\">\n                <input type=\"number\" name=\"amount\" class=\"form-control input-100\" value=\"";
        echo $balance;
        echo "\" step=\"0.01\"/>\n            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("affiliates", "payouttype");
        echo ":</td>\n            <td class=\"fieldarea\">\n                <select name=\"payouttype\" class=\"form-control select-inline\">\n                    <option value=\"1\">";
        echo $aInt->lang("affiliates", "transactiontoclient");
        echo "</option>\n                    <option value=\"2\">";
        echo $aInt->lang("affiliates", "addtocredit");
        echo "</option>\n                    <option>";
        echo $aInt->lang("affiliates", "withdrawalsonly");
        echo "</option>\n                </select>\n            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("fields", "transid");
        echo ":</td>\n            <td class=\"fieldarea\">\n                <input type=\"text\" name=\"transid\" class=\"form-control input-inline input-200\"/>\n                (";
        echo $aInt->lang("affiliates", "transactiontoclientinfo");
        echo ")\n            </td>\n        </tr>\n        <tr>\n            <td class=\"fieldlabel\">\n                ";
        echo $aInt->lang("fields", "paymentmethod");
        echo ":</td>\n            <td class=\"fieldarea\">\n                ";
        echo paymentMethodsSelection($aInt->lang("global", "na"));
        echo "            </td>\n        </tr>\n    </table>\n<div class=\"btn-container\">\n    <input type=\"submit\" value=\"";
        echo $aInt->lang("global", "submit");
        echo "\" class=\"btn btn-primary\" />\n</div>\n</form>\n\n";
        echo $aInt->endAdminTabs();
    }
}
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->jquerycode = $jquerycode;
$aInt->jscode = $jscode;
$aInt->display();

?>