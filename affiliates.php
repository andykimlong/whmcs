<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("CLIENTAREA", true);
require "init.php";
include "includes/affiliatefunctions.php";
include "includes/ticketfunctions.php";
$pagetitle = $_LANG["affiliatestitle"];
$breadcrumbnav = "<a href=\"index.php\">" . $_LANG["globalsystemname"] . "</a> > <a href=\"affiliates.php\">" . $_LANG["affiliatestitle"] . "</a>";
$pageicon = "images/affiliate_big.gif";
if (Auth::client()) {
    $result = select_query("tblaffiliates", "", ["clientid" => Auth::client()->id]);
    $data = mysql_fetch_array($result);
    $affiliateId = $data["id"];
} else {
    $affiliateId = 0;
}
if (WHMCS\Config\Setting::getValue("AffiliateEnabled")) {
    $displayTitle = $affiliateId ? Lang::trans("affiliatestitle") : Lang::trans("affiliatesactivate");
} else {
    $displayTitle = Lang::trans("affiliatestitle");
}
$tagline = $affiliateId ? Lang::trans("affiliatesrealtime") : "";
initialiseClientArea($pagetitle, $displayTitle, $tagline, $pageicon, $breadcrumbnav);
if (Auth::client()) {
    checkContactPermission("affiliates");
    if (!$affiliateId) {
        if (WHMCS\Config\Setting::getValue("AffiliateEnabled") && isset($_REQUEST["activate"])) {
            check_token();
            affiliateActivate(Auth::client()->id);
            redir();
        }
        $clientcurrency = Auth::client()->currency;
        $bonusdeposit = convertCurrency($CONFIG["AffiliateBonusDeposit"], 1, $clientcurrency);
        $templatefile = "affiliatessignup";
        $smarty->assign("affiliatesystemenabled", $CONFIG["AffiliateEnabled"]);
        $smarty->assign("bonusdeposit", formatCurrency($bonusdeposit));
        $smarty->assign("payoutpercentage", $CONFIG["AffiliateEarningPercent"] . "%");
    } else {
        $templatefile = "affiliates";
        $currency = WHMCS\Billing\Currency::factoryForClientArea();
        $date = $data["date"];
        $date = fromMySQLDate($date);
        $visitors = $data["visitors"];
        $balance = $data["balance"];
        $withdrawn = $data["withdrawn"];
        $result = select_query("tblaffiliatesaccounts", "COUNT(id)", ["affiliateid" => $affiliateId]);
        $data = mysql_fetch_array($result);
        $signups = $data[0];
        $result = select_query("tblaffiliatespending", "SUM(tblaffiliatespending.amount)", ["affiliateid" => $affiliateId], "clearingdate", "DESC", "", "tblaffiliatesaccounts ON tblaffiliatesaccounts.id=tblaffiliatespending.affaccid INNER JOIN tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid");
        $data = mysql_fetch_array($result);
        $pendingcommissions = $data[0];
        $conversionrate = 0 < $visitors ? round($signups / $visitors * 100, 2) : 0;
        $smarty->assign("affiliateid", $affiliateId);
        $smarty->assign("referrallink", $CONFIG["SystemURL"] . "/aff.php?aff=" . $affiliateId);
        $smarty->assign("date", $date);
        $smarty->assign("visitors", $visitors);
        $smarty->assign("signups", $signups);
        $smarty->assign("conversionrate", $conversionrate);
        $smarty->assign("pendingcommissions", formatCurrency($pendingcommissions));
        $smarty->assign("balance", formatCurrency($balance));
        $smarty->assign("withdrawn", formatCurrency($withdrawn));
        $affpayoutmin = $CONFIG["AffiliatePayout"];
        $affpayoutmin = convertCurrency($affpayoutmin, 1, $currency["id"]);
        $smarty->assign("withdrawlevel", false);
        if ($affpayoutmin <= $balance) {
            $smarty->assign("withdrawlevel", true);
            if ($action == "withdrawrequest") {
                $deptid = "";
                if ($CONFIG["AffiliateDepartment"]) {
                    $deptid = get_query_val("tblticketdepartments", "id", ["id" => $CONFIG["AffiliateDepartment"]]);
                }
                if (!$deptid) {
                    $deptid = get_query_val("tblticketdepartments", "id", ["hidden" => ""], "order", "ASC");
                }
                $message = "Affiliate Account Withdrawal Request.  Details below:\n\nClient ID: " . Auth::client()->id . "\nAffiliate ID: " . $affiliateId . "\nBalance: " . $balance;
                $responses = run_hook("AffiliateWithdrawalRequest", ["affiliateId" => $affiliateId, "userId" => Auth::user()->id, "clientId" => Auth::client()->id, "balance" => $balance]);
                $skipTicket = false;
                foreach ($responses as $response) {
                    if (array_key_exists("skipTicket", $response) && $response["skipTicket"]) {
                        $skipTicket = true;
                    }
                }
                if (!$skipTicket) {
                    $ticketdetails = openNewTicket(Auth::client()->id, 0, $deptid, "Affiliate Withdrawal Request", $message, "Medium", "", [], "", "", "", false);
                    redir("withdraw=1");
                }
            }
        }
        $smarty->assign("withdrawrequestsent", $whmcs->get_req_var("withdraw") ? true : false);
        $smarty->assign("affiliatePayoutMinimum", formatCurrency($affpayoutmin));
        $content = "\n<p><b>" . $_LANG["affiliatesreferals"] . "</b></p>\n<table align=\"center\" id=\"affiliates\" cellspacing=\"1\">\n<tr><td id=\"affiliatesheading\">" . $_LANG["affiliatessignupdate"] . "</td><td id=\"affiliatesheading\">" . $_LANG["affiliateshostingpackage"] . "</td><td id=\"affiliatesheading\">" . $_LANG["affiliatesamount"] . "</td><td id=\"affiliatesheading\">" . $_LANG["affiliatescommission"] . "</td><td id=\"affiliatesheading\">" . $_LANG["affiliatesstatus"] . "</td></tr>\n";
        $numitems = get_query_val("tblaffiliatesaccounts", "COUNT(*)", ["affiliateid" => $affiliateId], "", "", "", "tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid");
        list($orderby, $sort, $limit) = clientAreaTableInit("affiliates", "regdate", "DESC", $numitems);
        $smartyvalues["orderby"] = $orderby;
        $smartyvalues["sort"] = strtolower($sort);
        if ($orderby == "product") {
            $orderby = "tblproducts`.`name";
        } else {
            if ($orderby == "amount") {
                $orderby = "tblhosting`.`amount";
            } else {
                if ($orderby == "billingcycle") {
                    $orderby = "tblhosting`.`billingcycle";
                } else {
                    if ($orderby == "status") {
                        $orderby = "tblhosting`.`domainstatus";
                    } else {
                        $orderby = "tblhosting`.`regdate";
                    }
                }
            }
        }
        $referrals = [];
        $result = select_query("tblaffiliatesaccounts", "tblaffiliatesaccounts.*,tblhosting.userid,tblhosting.domainstatus,tblhosting.amount,tblhosting.firstpaymentamount,tblhosting.regdate,unix_timestamp(tblhosting.regdate) as regdate_ts,unix_timestamp(tblaffiliatesaccounts.lastpaid) as lastpaid_ts,tblhosting.billingcycle,tblhosting.packageid", ["affiliateid" => $affiliateId], $orderby, $sort, $limit, "tblhosting ON tblhosting.id=tblaffiliatesaccounts.relid INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblclients ON tblclients.id=tblhosting.userid");
        while ($data = mysql_fetch_array($result)) {
            $affaccid = $data["id"];
            $lastpaid = $data["lastpaid"];
            $lastpaidTs = $data["lastpaid_ts"];
            $relid = $data["relid"];
            $referralClientID = $data["userid"];
            $firstpaymentamount = $data["firstpaymentamount"];
            $amount = $data["amount"];
            $date = $data["regdate"];
            $dateTs = $data["regdate_ts"];
            $service = WHMCS\Product\Product::getProductName($data["packageid"]);
            $billingcycle = $data["billingcycle"];
            $rawstatus = $data["domainstatus"];
            $date = fromMySQLDate($date);
            $commission = calculateAffiliateCommission($affiliateId, $relid, $lastpaid);
            if (!$domain) {
                $domain = "";
            }
            $lastpaid = $lastpaid == "0000-00-00" ? "Never" : fromMySQLDate($lastpaid);
            $status = $_LANG["clientarea" . strtolower($rawstatus)];
            $billingcyclelang = strtolower($billingcycle);
            $billingcyclelang = str_replace(" ", "", $billingcyclelang);
            $billingcyclelang = str_replace("-", "", $billingcyclelang);
            $billingcyclelang = $_LANG["orderpaymentterm" . $billingcyclelang];
            $currency = getCurrency($referralClientID);
            $amountnum = 0;
            if ($billingcycle == "Free" || $billingcycle == "Free Account") {
                $amountdesc = $billingcyclelang;
            } else {
                if ($billingcycle == "One Time") {
                    $amountdesc = formatCurrency($firstpaymentamount) . " " . $billingcyclelang;
                    $amountnum = $firstpaymentamount;
                } else {
                    $amountdesc = $firstpaymentamount != $amount ? formatCurrency($firstpaymentamount) . " " . $_LANG["affiliatesinitialthen"] . " " : "";
                    $amountdesc .= formatCurrency($amount) . " " . $billingcyclelang;
                    $amountnum = $firstpaymentamount != $amount ? $firstpaymentamount : $amount;
                }
            }
            $currency = WHMCS\Billing\Currency::factoryForClientArea();
            $referrals[] = ["id" => $affaccid, "date" => $date, "datets" => $dateTs, "service" => $service, "package" => $service, "userid" => $referralClientID, "amount" => $amount, "billingcycle" => $billingcyclelang, "amountnum" => $amountnum, "amountdesc" => $amountdesc, "commissionnum" => $commission, "commission" => formatCurrency($commission), "lastpaid" => $lastpaid, "lastpaidts" => $lastpaidTs, "status" => $status, "rawstatus" => $rawstatus];
        }
        $smarty->assign("referrals", $referrals);
        $smartyvalues = array_merge($smartyvalues, clientAreaTablePageNav($numitems));
        $commissionhistory = [];
        $result = select_query("tblaffiliateshistory", "", ["affiliateid" => $affiliateId], "id", "DESC", "0,10");
        while ($data = mysql_fetch_array($result)) {
            $historyid = $data["id"];
            $date = $data["date"];
            $affaccid = $data["affaccid"];
            $amount = $data["amount"];
            $date = fromMySQLDate($date);
            $commissionhistory[] = ["date" => $date, "referralid" => $affaccid, "amount" => formatCurrency($amount)];
        }
        $smarty->assign("commissionhistory", $commissionhistory);
        $withdrawalshistory = [];
        $result = select_query("tblaffiliateswithdrawals", "", ["affiliateid" => $affiliateId], "id", "DESC");
        while ($data = mysql_fetch_array($result)) {
            $historyid = $data["id"];
            $date = $data["date"];
            $amount = $data["amount"];
            $date = fromMySQLDate($date);
            $withdrawalshistory[] = ["date" => $date, "amount" => formatCurrency($amount)];
        }
        $smarty->assign("withdrawalshistory", $withdrawalshistory);
        $affiliatelinkscode = WHMCS\Input\Sanitize::decode($CONFIG["AffiliateLinks"]);
        $affiliatelinkscode = str_replace("[AffiliateLinkCode]", $CONFIG["SystemURL"] . "/aff.php?aff=" . $affiliateId, $affiliatelinkscode);
        $affiliatelinkscode = str_replace("<(", "&lt;", $affiliatelinkscode);
        $affiliatelinkscode = str_replace(")>", "&gt;", $affiliatelinkscode);
        $smarty->assign("affiliatelinkscode", $affiliatelinkscode);
    }
} else {
    $goto = "affiliates";
    include "login.php";
}
$primarySidebar = Menu::primarySidebar("affiliateView");
$secondarySidebar = Menu::secondarySidebar("affiliateView");
$smarty->assign("inactive", is_null(WHMCS\Config\Setting::getValue("AffiliateEnabled")));
outputClientArea($templatefile, false, ["ClientAreaPageAffiliates"]);

?>