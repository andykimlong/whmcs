<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

add_hook("ShoppingCartValidateCheckout", 1, function ($vars) {
    require_once "lib/Plesk/Translate.php";
    require_once "lib/Plesk/Config.php";
    require_once "lib/Plesk/Utils.php";
    $translator = new Plesk_Translate();
    $accountLimit = (int) Plesk_Config::get()->account_limit;
    if ($accountLimit <= 0) {
        return [];
    }
    $accountCount = "new" == $vars["custtype"] ? 0 : Plesk_Utils::getAccountsCount($vars["userid"]);
    $pleskAccountsInCart = 0;
    foreach ($_SESSION["cart"]["products"] as $product) {
        $currentProduct = Illuminate\Database\Capsule\Manager::table("tblproducts")->where("id", $product["pid"])->first();
        if ("plesk" == $currentProduct->servertype) {
            $pleskAccountsInCart++;
        }
    }
    if (!$pleskAccountsInCart) {
        return [];
    }
    $summaryAccounts = $accountCount + $pleskAccountsInCart;
    $errors = [];
    if (0 < $accountLimit && $accountLimit < $summaryAccounts) {
        $errors[] = $translator->translate("ERROR_RESTRICTIONS_ACCOUNT_COUNT", ["ACCOUNT_LIMIT" => $accountLimit]);
    }
    return $errors;
});
add_hook("AdminPredefinedAddons", 0, function () {
    return [["module" => "plesk", "icontype" => "fa", "iconvalue" => "fad fa-cube", "labeltype" => "success", "labelvalue" => "New!", "paneltitle" => "WordPress Toolkit (Plesk)", "paneldescription" => "Automate provisioning of WPTK for Plesk Hosting Accounts", "addonname" => "WordPress Toolkit with Smart Updates", "addondescription" => "Smart Updates automatically tests updates for themes, plugins, languages, and WordPress!", "welcomeemail" => "WP Toolkit Welcome Email", "featureaddon" => "Plesk WordPress Toolkit with Smart Updates"]];
});

?>