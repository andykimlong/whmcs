<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

class Plesk_Utils
{
    public static function getAccountsCount($userId)
    {
        $hostingAccounts = WHMCS\Database\Capsule::table("tblhosting")->join("tblservers", "tblservers.id", "=", "tblhosting.server")->where("tblhosting.userid", $userId)->where("tblservers.type", "plesk")->whereIn("tblhosting.domainstatus", ["Active", "Suspended", "Pending"])->count();
        $hostingAddonAccounts = WHMCS\Database\Capsule::table("tblhostingaddons")->join("tblservers", "tblhostingaddons.server", "=", "tblservers.id")->where("tblhostingaddons.userid", $userId)->where("tblservers.type", "plesk")->whereIn("status", ["Active", "Suspended", "Pending"])->count();
        return $hostingAccounts + $hostingAddonAccounts;
    }
}

?>