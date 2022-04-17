<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

function affiliateActivate($userid)
{
    global $CONFIG;
    $result = select_query("tblclients", "currency", ["id" => $userid]);
    $data = mysql_fetch_array($result);
    $clientcurrency = $data["currency"];
    $bonusdeposit = convertCurrency($CONFIG["AffiliateBonusDeposit"], 1, $clientcurrency);
    $result = select_query("tblaffiliates", "id", ["clientid" => $userid]);
    $data = mysql_fetch_array($result);
    $affiliateid = $data["id"];
    if (!$affiliateid) {
        $affiliateid = insert_query("tblaffiliates", ["date" => "now()", "clientid" => $userid, "balance" => $bonusdeposit]);
    }
    logActivity("Activated Affiliate Account - Affiliate ID: " . $affiliateid . " - User ID: " . $userid, $userid);
    run_hook("AffiliateActivation", ["affid" => $affiliateid, "userid" => $userid]);
}

?>