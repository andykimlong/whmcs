<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

add_hook("ClientAreaPrimarySidebar", -1, "cpanel_defineSsoSidebarLinks");
add_hook("AdminPredefinedAddons", -100, "cpanel_adminPredefinedAddons");
function cpanel_defineSsoSidebarLinks($sidebar)
{
    if (!$sidebar->getChild("Service Details Actions")) {
        return NULL;
    }
    $service = Menu::context("service");
    if ($service instanceof WHMCS\Service\Service && $service->product->module != "cpanel") {
        return NULL;
    }
    $ssoPermission = checkContactPermission("productsso", true);
    $sidebar->getChild("Service Details Actions")->addChild("Login to cPanel", ["uri" => "clientarea.php?action=productdetails&id=" . $service->id . "&dosinglesignon=1" . ($service->product->type == "reselleraccount" ? "&app=Home" : ""), "label" => Lang::trans("cpanellogin"), "attributes" => $ssoPermission ? ["target" => "_blank"] : [], "disabled" => $service->status != "Active", "order" => 1]);
    if ($service->product->type == "reselleraccount") {
        $sidebar->getChild("Service Details Actions")->addChild("Login to WHM", ["uri" => "clientarea.php?action=productdetails&id=" . $service->id . "&dosinglesignon=1", "label" => Lang::trans("cpanelwhmlogin"), "attributes" => $ssoPermission ? ["target" => "_blank"] : [], "disabled" => $service->status != "Active", "order" => 2]);
    }
    $moduleInterface = new WHMCS\Module\Server();
    $moduleInterface->loadByServiceID($service->id);
    $serverParams = $moduleInterface->getServerParams($service->server);
    $domain = $serverParams["serverhostname"] ?: $serverParams["serverip"];
    $port = $serverParams["serversecure"] ? "2096" : "2095";
    $webmailUrl = $serverParams["serverhttpprefix"] . "://" . $domain . ":" . $port;
    $sidebar->getChild("Service Details Actions")->addChild("Login to Webmail", ["uri" => $webmailUrl, "label" => Lang::trans("cpanelwebmaillogin"), "attributes" => ["target" => "_blank"], "disabled" => $service->status != "Active", "order" => 3]);
}
function cpanel_adminPredefinedAddons()
{
    return [["module" => "cpanel", "icontype" => "fa", "iconvalue" => "fad fa-cube", "labeltype" => "success", "labelvalue" => "New!", "paneltitle" => "WordPress Toolkit Deluxe (cPanel)", "paneldescription" => "Automate provisioning of WPTK Deluxe for cPanel Hosting Accounts", "addonname" => "WordPress Toolkit Deluxe", "addondescription" => "WP Toolkit Deluxe gives you advanced features like plugin and theme management, staging, cloning, and Smart Updates!", "welcomeemail" => "WP Toolkit Welcome Email", "featureaddon" => "wp-toolkit-deluxe"]];
}

?>