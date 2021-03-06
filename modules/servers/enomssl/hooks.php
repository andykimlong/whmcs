<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

add_hook("ClientAreaPrimarySidebar", -1, function ($sidebar) {
    if (!$sidebar->getChild("Service Details Actions")) {
        return NULL;
    }
    $service = Menu::context("service");
    if ($service instanceof WHMCS\Service\Service && $service->product->module != "enomssl") {
        return NULL;
    }
    $sslCertificate = Illuminate\Database\Capsule\Manager::table("tblsslorders")->where("serviceid", "=", $service->id)->first();
    $sidebar->getChild("Service Details Actions")->addChild(Lang::trans("sslconfigurenow"), ["uri" => "configuressl.php?cert=" . md5($sslCertificate->id), "order" => 1, "disabled" => is_null($sslCertificate) || $sslCertificate->status != "Awaiting Configuration"]);
});

?>