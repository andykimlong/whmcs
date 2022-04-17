<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

function getOrderStatusColour($status)
{
    $statuscolors = ["Active" => "779500", "Pending" => "CC0000", "Fraud" => "000000", "Cancelled" => "888"];
    return "<span style=\"color:#" . $statuscolors[$status] . "\">" . $status . "</span>";
}
function getProductInfo($pid)
{
    $result = select_query("tblproducts", "tblproducts.id,tblproducts.name,tblproducts.description,tblproducts.gid,tblproducts.type,tblproductgroups.id AS group_id,tblproductgroups.name as group_name, tblproducts.freedomain,tblproducts.freedomainpaymentterms,tblproducts.freedomaintlds,tblproducts.stockcontrol,tblproducts.qty", ["tblproducts.id" => $pid], "", "", "", "tblproductgroups ON tblproductgroups.id=tblproducts.gid");
    $data = mysql_fetch_array($result);
    $productinfo = [];
    $productinfo["pid"] = $data["id"];
    $productinfo["gid"] = $data["gid"];
    $productinfo["type"] = $data["type"];
    $productinfo["groupname"] = WHMCS\Product\Group::getGroupName($data["group_id"], $data["group_name"]);
    $productinfo["name"] = WHMCS\Product\Product::getProductName($data["id"], $data["name"]);
    $productinfo["description"] = nl2br(WHMCS\Product\Product::getProductDescription($data["id"]), $data["description"]);
    $productinfo["freedomain"] = $data["freedomain"];
    $productinfo["freedomainpaymentterms"] = explode(",", $data["freedomainpaymentterms"]);
    $productinfo["freedomaintlds"] = explode(",", $data["freedomaintlds"]);
    $productinfo["qty"] = $data["stockcontrol"] ? $data["qty"] : "";
    return $productinfo;
}
function getPricingInfo($pid, $inclconfigops = false, $upgrade = false, WHMCS\Billing\Currency $currencyObj = NULL)
{
    global $_LANG;
    global $currency;
    $currency = $currencyObj ? $currencyObj : $currency;
    $result = select_query("tblproducts", "", ["id" => $pid]);
    $data = mysql_fetch_array($result);
    $paytype = $data["paytype"];
    $freedomain = $data["freedomain"];
    $freedomainpaymentterms = $data["freedomainpaymentterms"];
    if (!isset($currency["id"])) {
        $currency = getCurrency();
    }
    $result = select_query("tblpricing", "", ["type" => "product", "currency" => $currency["id"], "relid" => $pid]);
    $data = mysql_fetch_array($result);
    $msetupfee = $data["msetupfee"];
    $qsetupfee = $data["qsetupfee"];
    $ssetupfee = $data["ssetupfee"];
    $asetupfee = $data["asetupfee"];
    $bsetupfee = $data["bsetupfee"];
    $tsetupfee = $data["tsetupfee"];
    $monthly = $data["monthly"];
    $quarterly = $data["quarterly"];
    $semiannually = $data["semiannually"];
    $annually = $data["annually"];
    $biennially = $data["biennially"];
    $triennially = $data["triennially"];
    $configoptions = new WHMCS\Product\ConfigOptions();
    $freedomainpaymentterms = explode(",", $freedomainpaymentterms);
    $monthlypricingbreakdown = WHMCS\Config\Setting::getValue("ProductMonthlyPricingBreakdown");
    $minprice = 0;
    $setupFee = 0;
    $mincycle = "";
    $hasconfigoptions = false;
    if ($paytype == "free") {
        $pricing["type"] = $mincycle = "free";
    } else {
        if ($paytype == "onetime") {
            if ($inclconfigops) {
                $msetupfee += $configoptions->getBasePrice($pid, "msetupfee");
                $monthly += $configoptions->getBasePrice($pid, "monthly");
            }
            $minprice = $monthly;
            $setupFee = $msetupfee;
            $pricing["type"] = $mincycle = "onetime";
            $pricing["onetime"] = new WHMCS\View\Formatter\Price($monthly, $currency);
            if ($msetupfee != "0.00") {
                $pricing["onetime"] .= " + " . new WHMCS\View\Formatter\Price($msetupfee, $currency) . " " . $_LANG["ordersetupfee"];
            }
            if (in_array("onetime", $freedomainpaymentterms) && $freedomain && !$upgrade) {
                $pricing["onetime"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
            }
        } else {
            if ($paytype == "recurring") {
                $pricing["type"] = "recurring";
                if (0 <= $monthly) {
                    if ($inclconfigops) {
                        $msetupfee += $configoptions->getBasePrice($pid, "msetupfee");
                        $monthly += $configoptions->getBasePrice($pid, "monthly");
                    }
                    if (!$mincycle) {
                        $minprice = $monthly;
                        $setupFee = $msetupfee;
                        $mincycle = "monthly";
                        $minMonths = 1;
                    }
                    if ($monthlypricingbreakdown) {
                        $pricing["monthly"] = $_LANG["orderpaymentterm1month"] . " - " . new WHMCS\View\Formatter\Price($monthly, $currency);
                    } else {
                        $pricing["monthly"] = new WHMCS\View\Formatter\Price($monthly, $currency) . " " . $_LANG["orderpaymenttermmonthly"];
                    }
                    if ($msetupfee != "0.00") {
                        $pricing["monthly"] .= " + " . new WHMCS\View\Formatter\Price($msetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                    }
                    if (in_array("monthly", $freedomainpaymentterms) && $freedomain && !$upgrade) {
                        $pricing["monthly"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                    }
                }
                if (0 <= $quarterly) {
                    if ($inclconfigops) {
                        $qsetupfee += $configoptions->getBasePrice($pid, "qsetupfee");
                        $quarterly += $configoptions->getBasePrice($pid, "quarterly");
                    }
                    if (!$mincycle) {
                        $minprice = $monthlypricingbreakdown ? $quarterly / 3 : $quarterly;
                        $setupFee = $qsetupfee;
                        $mincycle = "quarterly";
                        $minMonths = 3;
                    }
                    if ($monthlypricingbreakdown) {
                        $pricing["quarterly"] = $_LANG["orderpaymentterm3month"] . " - " . new WHMCS\View\Formatter\Price($quarterly / 3, $currency);
                    } else {
                        $pricing["quarterly"] = new WHMCS\View\Formatter\Price($quarterly, $currency) . " " . $_LANG["orderpaymenttermquarterly"];
                    }
                    if ($qsetupfee != "0.00") {
                        $pricing["quarterly"] .= " + " . new WHMCS\View\Formatter\Price($qsetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                    }
                    if (in_array("quarterly", $freedomainpaymentterms) && $freedomain && !$upgrade) {
                        $pricing["quarterly"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                    }
                }
                if (0 <= $semiannually) {
                    if ($inclconfigops) {
                        $ssetupfee += $configoptions->getBasePrice($pid, "ssetupfee");
                        $semiannually += $configoptions->getBasePrice($pid, "semiannually");
                    }
                    if (!$mincycle) {
                        $minprice = $monthlypricingbreakdown ? $semiannually / 6 : $semiannually;
                        $setupFee = $ssetupfee;
                        $mincycle = "semiannually";
                        $minMonths = 6;
                    }
                    if ($monthlypricingbreakdown) {
                        $pricing["semiannually"] = $_LANG["orderpaymentterm6month"] . " - " . new WHMCS\View\Formatter\Price($semiannually / 6, $currency);
                    } else {
                        $pricing["semiannually"] = new WHMCS\View\Formatter\Price($semiannually, $currency) . " " . $_LANG["orderpaymenttermsemiannually"];
                    }
                    if ($ssetupfee != "0.00") {
                        $pricing["semiannually"] .= " + " . new WHMCS\View\Formatter\Price($ssetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                    }
                    if (in_array("semiannually", $freedomainpaymentterms) && $freedomain && !$upgrade) {
                        $pricing["semiannually"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                    }
                }
                if (0 <= $annually) {
                    if ($inclconfigops) {
                        $asetupfee += $configoptions->getBasePrice($pid, "asetupfee");
                        $annually += $configoptions->getBasePrice($pid, "annually");
                    }
                    if (!$mincycle) {
                        $minprice = $monthlypricingbreakdown ? $annually / 12 : $annually;
                        $setupFee = $asetupfee;
                        $mincycle = "annually";
                        $minMonths = 12;
                    }
                    if ($monthlypricingbreakdown) {
                        $pricing["annually"] = $_LANG["orderpaymentterm12month"] . " - " . new WHMCS\View\Formatter\Price($annually / 12, $currency);
                    } else {
                        $pricing["annually"] = new WHMCS\View\Formatter\Price($annually, $currency) . " " . $_LANG["orderpaymenttermannually"];
                    }
                    if ($asetupfee != "0.00") {
                        $pricing["annually"] .= " + " . new WHMCS\View\Formatter\Price($asetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                    }
                    if (in_array("annually", $freedomainpaymentterms) && $freedomain && !$upgrade) {
                        $pricing["annually"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                    }
                }
                if (0 <= $biennially) {
                    if ($inclconfigops) {
                        $bsetupfee += $configoptions->getBasePrice($pid, "bsetupfee");
                        $biennially += $configoptions->getBasePrice($pid, "biennially");
                    }
                    if (!$mincycle) {
                        $minprice = $monthlypricingbreakdown ? $biennially / 24 : $biennially;
                        $setupFee = $bsetupfee;
                        $mincycle = "biennially";
                        $minMonths = 24;
                    }
                    if ($monthlypricingbreakdown) {
                        $pricing["biennially"] = $_LANG["orderpaymentterm24month"] . " - " . new WHMCS\View\Formatter\Price($biennially / 24, $currency);
                    } else {
                        $pricing["biennially"] = new WHMCS\View\Formatter\Price($biennially, $currency) . " " . $_LANG["orderpaymenttermbiennially"];
                    }
                    if ($bsetupfee != "0.00") {
                        $pricing["biennially"] .= " + " . new WHMCS\View\Formatter\Price($bsetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                    }
                    if (in_array("biennially", $freedomainpaymentterms) && $freedomain && !$upgrade) {
                        $pricing["biennially"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                    }
                }
                if (0 <= $triennially) {
                    if ($inclconfigops) {
                        $tsetupfee += $configoptions->getBasePrice($pid, "tsetupfee");
                        $triennially += $configoptions->getBasePrice($pid, "triennially");
                    }
                    if (!$mincycle) {
                        $minprice = $monthlypricingbreakdown ? $triennially / 36 : $triennially;
                        $setupFee = $tsetupfee;
                        $mincycle = "triennially";
                        $minMonths = 36;
                    }
                    if ($monthlypricingbreakdown) {
                        $pricing["triennially"] = $_LANG["orderpaymentterm36month"] . " - " . new WHMCS\View\Formatter\Price($triennially / 36, $currency);
                    } else {
                        $pricing["triennially"] = new WHMCS\View\Formatter\Price($triennially, $currency) . " " . $_LANG["orderpaymenttermtriennially"];
                    }
                    if ($tsetupfee != "0.00") {
                        $pricing["triennially"] .= " + " . new WHMCS\View\Formatter\Price($tsetupfee, $currency) . " " . $_LANG["ordersetupfee"];
                    }
                    if (in_array("triennially", $freedomainpaymentterms) && $freedomain && !$upgrade) {
                        $pricing["triennially"] .= " (" . $_LANG["orderfreedomainonly"] . ")";
                    }
                }
            }
        }
    }
    $pricing["hasconfigoptions"] = $configoptions->hasConfigOptions($pid);
    if (isset($pricing["onetime"])) {
        $pricing["cycles"]["onetime"] = $pricing["onetime"];
    }
    if (isset($pricing["monthly"])) {
        $pricing["cycles"]["monthly"] = $pricing["monthly"];
    }
    if (isset($pricing["quarterly"])) {
        $pricing["cycles"]["quarterly"] = $pricing["quarterly"];
    }
    if (isset($pricing["semiannually"])) {
        $pricing["cycles"]["semiannually"] = $pricing["semiannually"];
    }
    if (isset($pricing["annually"])) {
        $pricing["cycles"]["annually"] = $pricing["annually"];
    }
    if (isset($pricing["biennially"])) {
        $pricing["cycles"]["biennially"] = $pricing["biennially"];
    }
    if (isset($pricing["triennially"])) {
        $pricing["cycles"]["triennially"] = $pricing["triennially"];
    }
    $pricing["rawpricing"] = ["msetupfee" => format_as_currency($msetupfee), "qsetupfee" => format_as_currency($qsetupfee), "ssetupfee" => format_as_currency($ssetupfee), "asetupfee" => format_as_currency($asetupfee), "bsetupfee" => format_as_currency($bsetupfee), "tsetupfee" => format_as_currency($tsetupfee), "monthly" => format_as_currency($monthly), "quarterly" => format_as_currency($quarterly), "semiannually" => format_as_currency($semiannually), "annually" => format_as_currency($annually), "biennially" => format_as_currency($biennially), "triennially" => format_as_currency($triennially)];
    $pricing["minprice"] = ["price" => new WHMCS\View\Formatter\Price($minprice, $currency), "setupFee" => 0 < $setupFee ? new WHMCS\View\Formatter\Price($setupFee, $currency) : 0, "cycle" => $monthlypricingbreakdown && $paytype == "recurring" ? "monthly" : $mincycle, "simple" => (new WHMCS\View\Formatter\Price($minprice, $currency))->toPrefixed()];
    if (isset($minMonths)) {
        switch ($minMonths) {
            case 3:
                $langVar = "shoppingCartProductPerMonth";
                $count = "3 ";
                break;
            case 6:
                $langVar = "shoppingCartProductPerMonth";
                $count = "6 ";
                break;
            case 12:
                $langVar = $monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear";
                $count = "";
                break;
            case 24:
                $langVar = $monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear";
                $count = "2 ";
                break;
            case 36:
                $langVar = $monthlypricingbreakdown ? "shoppingCartProductPerMonth" : "shoppingCartProductPerYear";
                $count = "3 ";
                break;
            default:
                $langVar = "shoppingCartProductPerMonth";
                $count = "";
                $pricing["minprice"]["cycleText"] = Lang::trans($langVar, [":count" => $count, ":price" => $pricing["minprice"]["simple"]]);
                $pricing["minprice"]["cycleTextWithCurrency"] = Lang::trans($langVar, [":count" => $count, ":price" => $pricing["minprice"]["price"]]);
        }
    }
    return $pricing;
}
function calcCartTotals(WHMCS\User\Client $client = NULL, $checkout = false, $ignorenoconfig = false)
{
    global $_LANG;
    global $promo_data;
    $whmcs = WHMCS\Application::getInstance();
    $order = NULL;
    $orderid = 0;
    if (!function_exists("bundlesGetProductPriceOverride")) {
        require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "cartfunctions.php";
    }
    if (!function_exists("getClientsDetails")) {
        require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "clientfunctions.php";
    }
    if (!function_exists("getCartConfigOptions")) {
        require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "configoptionsfunctions.php";
    }
    if (!function_exists("getTLDPriceList")) {
        require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "domainfunctions.php";
    }
    if (!function_exists("getTaxRate")) {
        require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "invoicefunctions.php";
    }
    $isAdmin = false;
    if ($client) {
        $uninvoicedItemsCount = WHMCS\Billing\Invoice\Item::clientId($client->id)->notInvoiced()->count();
        if (0 < $uninvoicedItemsCount) {
            createInvoices($client->id);
        }
    }
    if (defined("ADMINAREA") || defined("APICALL") || DI::make("runtimeStorage")->runningViaLocalApi === true) {
        $isAdmin = true;
    }
    if ($client) {
        $currency = $client->currencyrel;
    } else {
        $currency = WHMCS\Billing\Currency::factoryForClientArea();
    }
    $orderForm = new WHMCS\OrderForm();
    $cart_total = $cart_discount = 0;
    $cart_tax = [];
    $recurring_tax = [];
    run_hook("PreCalculateCartTotals", $orderForm->getCartData());
    if (!$ignorenoconfig) {
        if ($orderForm->getCartDataByKey("products")) {
            foreach ($orderForm->getCartDataByKey("products") as $key => $productdata) {
                if (isset($productdata["noconfig"]) && $productdata["noconfig"]) {
                    unset($_SESSION["cart"]["products"][$key]);
                }
            }
        }
        $bundlewarnings = bundlesValidateCheckout();
        if ($orderForm->getCartDataByKey("products")) {
            $_SESSION["cart"]["products"] = array_values($_SESSION["cart"]["products"]);
        }
    }
    if ($checkout) {
        if (!$_SESSION["cart"]) {
            return false;
        }
        run_hook("PreShoppingCartCheckout", $_SESSION["cart"]);
        $ordernumhooks = run_hook("OverrideOrderNumberGeneration", $_SESSION["cart"]);
        $order_number = "";
        if (count($ordernumhooks)) {
            foreach ($ordernumhooks as $ordernumhookval) {
                if (is_numeric($ordernumhookval)) {
                    $order_number = $ordernumhookval;
                }
            }
        }
        if (!$order_number) {
            $order_number = generateUniqueID();
        }
        $_SESSION["cart"]["paymentmethod"] ? exit : NULL;
    }
    $promotioncode = $orderForm->getCartDataByKey("promo");
    if ($promotioncode) {
        $result = select_query("tblpromotions", "", ["code" => $promotioncode]);
        $promo_data = mysql_fetch_array($result);
    }
    if (!$client) {
        if (!$_SESSION["cart"]["user"]["country"]) {
            $_SESSION["cart"]["user"]["country"] = WHMCS\Config\Setting::getValue("DefaultCountry");
        }
        $_SESSION["cart"]["user"]["state"] ? exit : NULL;
    } else {
        $legacyClient = new WHMCS\Client($client);
        $clientsdetails = $legacyClient->getDetails();
        $state = $clientsdetails["state"];
        $country = $clientsdetails["country"];
    }
    $taxCalculator = new WHMCS\Billing\Tax();
    $taxCalculator->setIsInclusive(WHMCS\Config\Setting::getValue("TaxType") == "Inclusive")->setIsCompound(WHMCS\Config\Setting::getValue("TaxL2Compound"));
    $taxname = $taxname2 = "";
    $rawtaxrate = $rawtaxrate2 = 0;
    $taxrate = $taxrate2 = 0;
    if (WHMCS\Config\Setting::getValue("TaxEnabled")) {
        $taxdata = getTaxRate(1, $state, $country);
        $taxname = $taxdata["name"];
        $taxrate = $taxdata["rate"];
        $rawtaxrate = $taxrate;
        $inctaxrate = $taxrate / 100 + 1;
        $taxrate /= 100;
        $taxCalculator->setLevel1Percentage($taxdata["rate"]);
        $taxdata = getTaxRate(2, $state, $country);
        $taxname2 = $taxdata["name"];
        $taxrate2 = $taxdata["rate"];
        $rawtaxrate2 = $taxrate2;
        $inctaxrate2 = $taxrate2 / 100 + 1;
        $taxrate2 /= 100;
        $taxCalculator->setLevel2Percentage($taxdata["rate"]);
    }
    if (WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxInclusiveDeduct") && WHMCS\Config\Setting::getValue("TaxType") == "Inclusive" && (!$taxrate && !$taxrate2 || $clientsdetails["taxexempt"])) {
        $systemFirstTaxRate = WHMCS\Database\Capsule::table("tbltax")->value("taxrate");
        if ($systemFirstTaxRate) {
            $excltaxrate = 1 + $systemFirstTaxRate / 100;
        } else {
            $excltaxrate = 1;
        }
    } else {
        $excltaxrate = 1;
    }
    $cartdata = $productsarray = $tempdomains = $orderproductids = $orderdomainids = $orderaddonids = $orderrenewalids = $freedomains = [];
    $recurring_cycles_total = ["monthly" => 0, "quarterly" => 0, "semiannually" => 0, "annually" => 0, "biennially" => 0, "triennially" => 0];
    $cartProducts = $orderForm->getCartDataByKey("products");
    if (is_array($cartProducts)) {
        $productRemovedFromCart = false;
        $one_time_discount_applied = false;
        $orderEmailItems = "";
        $adminEmailItems = [];
        foreach ($cartProducts as $key => $productdata) {
            $data = get_query_vals("tblproducts", "tblproducts.*, tblproductgroups.name AS groupname", ["tblproducts.id" => $productdata["pid"]], "", "", "", "tblproductgroups ON tblproductgroups.id=tblproducts.gid");
            $pid = $data["id"];
            if (!$pid) {
                unset($_SESSION["cart"]["products"][$key]);
            } else {
                $gid = $data["gid"];
                $groupname = $isAdmin && !$checkout ? $data["groupname"] : WHMCS\Product\Group::getGroupName($gid, $data["groupname"]);
                $adminGroupName = $data["groupname"];
                $productname = $isAdmin && !$checkout ? $data["name"] : WHMCS\Product\Product::getProductName($pid, $data["name"]);
                $adminProductName = $data["name"];
                $paytype = $data["paytype"];
                $allowqty = (int) $data["allowqty"];
                $proratabilling = in_array($paytype, [WHMCS\Product\Product::PAYMENT_ONETIME, WHMCS\Product\Product::PAYMENT_FREE]) ? "" : $data["proratabilling"];
                $proratadate = $data["proratadate"];
                $proratachargenextmonth = $data["proratachargenextmonth"];
                $tax = $data["tax"];
                $servertype = $data["servertype"];
                $servergroup = $data["servergroup"];
                $stockcontrol = $data["stockcontrol"];
                $qty = isset($productdata["qty"]) ? $productdata["qty"] : 1;
                if (!$allowqty || !$qty) {
                    $qty = 1;
                }
                $productdata["allowqty"] = $allowqty;
                if ($stockcontrol) {
                    $quantityAvailable = (int) $data["qty"];
                    if (!defined("ADMINAREA")) {
                        if ($quantityAvailable <= 0) {
                            unset($_SESSION["cart"]["products"][$key]);
                            $productRemovedFromCart = true;
                        } else {
                            if ($allowqty === WHMCS\Cart\Cart::QUANTITY_MULTIPLE && $quantityAvailable < $qty) {
                                $qty = $quantityAvailable;
                            }
                        }
                    }
                }
                $productdata["qty"] = $qty;
                $freedomain = $data["freedomain"];
                if ($freedomain) {
                    $freedomainpaymentterms = $data["freedomainpaymentterms"];
                    $freedomaintlds = $data["freedomaintlds"];
                    $freedomainpaymentterms = explode(",", $freedomainpaymentterms);
                    $freedomaintlds = explode(",", $freedomaintlds);
                } else {
                    $freedomainpaymentterms = $freedomaintlds = [];
                }
                $productinfo = getproductinfo($pid);
                if (array_key_exists("sslCompetitiveUpgrade", $productdata) && $productdata["sslCompetitiveUpgrade"]) {
                    $productinfo["name"] .= "<br><small>" . Lang::trans("store.ssl.competitiveUpgradeQualified") . "</small>";
                }
                $productdata["productinfo"] = $productinfo;
                if (!function_exists("getCustomFields")) {
                    require ROOTDIR . "/includes/customfieldfunctions.php";
                }
                $customfields = getCustomFields("product", $pid, "", $isAdmin, "", $productdata["customfields"]);
                $productdata["customfields"] = $customfields;
                $pricing = getpricinginfo($pid, false, false, $currency);
                if ($paytype != "free") {
                    $prod = new WHMCS\Pricing();
                    $prod->loadPricing("product", $pid);
                    if (!$prod->hasBillingCyclesAvailable()) {
                        unset($_SESSION["cart"]["products"][$key]);
                    }
                }
                if ($pricing["type"] == "recurring") {
                    $billingcycle = strtolower($productdata["billingcycle"]);
                    if (!in_array($billingcycle, ["monthly", "quarterly", "semiannually", "annually", "biennially", "triennially"])) {
                        $billingcycle = "";
                    }
                    if ($billingcycle && $pricing["rawpricing"][$billingcycle] < 0) {
                        $billingcycle = "";
                    }
                    if (!$billingcycle) {
                        if (0 <= $pricing["rawpricing"]["monthly"]) {
                            $billingcycle = "monthly";
                        } else {
                            if (0 <= $pricing["rawpricing"]["quarterly"]) {
                                $billingcycle = "quarterly";
                            } else {
                                if (0 <= $pricing["rawpricing"]["semiannually"]) {
                                    $billingcycle = "semiannually";
                                } else {
                                    if (0 <= $pricing["rawpricing"]["annually"]) {
                                        $billingcycle = "annually";
                                    } else {
                                        if (0 <= $pricing["rawpricing"]["biennially"]) {
                                            $billingcycle = "biennially";
                                        } else {
                                            if (0 <= $pricing["rawpricing"]["triennially"]) {
                                                $billingcycle = "triennially";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($pricing["type"] == "onetime") {
                        $billingcycle = "onetime";
                    } else {
                        $billingcycle = "free";
                    }
                }
                $productdata["billingcycle"] = $billingcycle;
                $productdata["billingcyclefriendly"] = Lang::trans("orderpaymentterm" . $billingcycle);
                if ($billingcycle == "free") {
                    $product_setup = $product_onetime = $product_recurring = "0";
                    $databasecycle = "Free Account";
                } else {
                    if ($billingcycle == "onetime") {
                        $product_setup = $pricing["rawpricing"]["msetupfee"];
                        $product_onetime = $pricing["rawpricing"]["monthly"];
                        $product_recurring = 0;
                        $databasecycle = "One Time";
                    } else {
                        $product_setup = $pricing["rawpricing"][substr($billingcycle, 0, 1) . "setupfee"];
                        $product_onetime = $product_recurring = $pricing["rawpricing"][$billingcycle];
                        $databasecycle = ucfirst($billingcycle);
                        if ($databasecycle == "Semiannually") {
                            $databasecycle = "Semi-Annually";
                        }
                    }
                }
                if ($product_setup < 0) {
                    $product_setup = 0;
                }
                $before_priceoverride_value = "";
                if ($bundleoverride = bundlesGetProductPriceOverride("product", $key)) {
                    $before_priceoverride_value = $product_setup + $product_onetime;
                    $product_setup = 0;
                    $product_onetime = $product_recurring = $bundleoverride;
                }
                $hookret = run_hook("OrderProductPricingOverride", ["key" => $key, "pid" => $pid, "proddata" => $productdata]);
                foreach ($hookret as $hookret2) {
                    if (is_array($hookret2)) {
                        if ($hookret2["setup"]) {
                            $product_setup = $hookret2["setup"];
                        }
                        if ($hookret2["recurring"]) {
                            $product_onetime = $product_recurring = $hookret2["recurring"];
                        }
                    }
                }
                $productdata["pricing"]["baseprice"] = new WHMCS\View\Formatter\Price($product_onetime, $currency);
                $configoptionsdb = [];
                $configurableoptions = getCartConfigOptions($pid, $productdata["configoptions"], $billingcycle, "", "", true);
                $configoptions = [];
                if ($configurableoptions) {
                    foreach ($configurableoptions as $confkey => $value) {
                        if (!$value["hidden"] || defined("ADMINAREA") || defined("APICALL")) {
                            $configoptions[] = ["name" => $value["optionname"], "type" => $value["optiontype"], "option" => $value["selectedoption"], "optionname" => $value["selectedname"], "setup" => 0 < $value["selectedsetup"] ? new WHMCS\View\Formatter\Price($value["selectedsetup"], $currency) : "", "recurring" => new WHMCS\View\Formatter\Price($value["selectedrecurring"], $currency), "qty" => $value["selectedqty"]];
                            $product_setup += $value["selectedsetup"];
                            $product_onetime += $value["selectedrecurring"];
                            if (strlen($before_priceoverride_value)) {
                                $before_priceoverride_value += $value["selectedrecurring"];
                            }
                            if ($billingcycle != "onetime") {
                                $product_recurring += $value["selectedrecurring"];
                            }
                        }
                        $configoptionsdb[$value["id"]] = ["value" => $value["selectedvalue"], "qty" => $value["selectedqty"]];
                    }
                }
                $productdata["configoptions"] = $configoptions;
                if (in_array($billingcycle, $freedomainpaymentterms)) {
                    $domain = $productdata["domain"];
                    $domainparts = explode(".", $domain, 2);
                    $tld = "." . $domainparts[1];
                    if (in_array($tld, $freedomaintlds)) {
                        $freedomains[$domain] = $freedomain;
                    }
                }
                if ($proratabilling) {
                    $proratavalues = getProrataValues($billingcycle, $product_onetime, $proratadate, $proratachargenextmonth, date("d"), date("m"), date("Y"), $client->id);
                    $product_onetime = $proratavalues["amount"];
                    $productdata["proratadate"] = fromMySQLDate($proratavalues["date"]);
                }
                if (WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxInclusiveDeduct")) {
                    $product_setup = format_as_currency($product_setup / $excltaxrate);
                    $product_onetime = format_as_currency($product_onetime / $excltaxrate);
                    $product_recurring = format_as_currency($product_recurring / $excltaxrate);
                }
                $singleProductSetup = $product_setup;
                $singleProductOnetime = $product_onetime;
                $singleProductRecurring = $product_recurring;
                if ($allowqty !== WHMCS\Cart\Cart::QUANTITY_SCALING) {
                    $product_setup *= $qty;
                }
                if ($allowqty === WHMCS\Cart\Cart::QUANTITY_SCALING) {
                    $singleProductOnetime *= $qty;
                    $singleProductRecurring *= $qty;
                }
                $productTotalEach = $product_onetime;
                $product_onetime *= $qty;
                $product_total_today_db = $product_setup + $product_onetime;
                $product_recurring_db = $product_recurring * $qty;
                $singleProductTotalToday = $singleProductSetup + $singleProductOnetime;
                $productdata["pricing"]["setup"] = $product_setup;
                $productdata["pricing"]["recurring"][$billingcycle] = $product_recurring_db;
                $productdata["pricing"]["totaltoday"] = $product_total_today_db;
                $productdata["pricing"]["productonlysetup"] = $productdata["pricing"]["setup"];
                $productdata["pricing"]["totaltodayexcltax"] = $productdata["pricing"]["totaltoday"];
                $productdata["pricing"]["totalTodayExcludingTaxSetup"] = $product_onetime;
                if ($product_onetime == 0 && $product_recurring == 0) {
                    $pricing_text = $_LANG["orderfree"];
                } else {
                    $pricing_text = "";
                    if (strlen($before_priceoverride_value)) {
                        $pricing_text .= "<strike>" . new WHMCS\View\Formatter\Price($before_priceoverride_value, $currency) . "</strike> ";
                    }
                    $pricing_text .= new WHMCS\View\Formatter\Price($productTotalEach, $currency);
                    if (0 < $product_setup) {
                        $pricing_text .= " + " . new WHMCS\View\Formatter\Price($product_setup, $currency) . " " . $_LANG["ordersetupfee"];
                    }
                    if ($allowqty && 1 < $qty) {
                        $pricing_text .= $_LANG["invoiceqtyeach"] . "<br />" . $_LANG["invoicestotal"] . ": " . new WHMCS\View\Formatter\Price($productdata["pricing"]["totaltoday"], $currency);
                    }
                }
                $productdata["pricingtext"] = $pricing_text;
                if (isset($productdata["priceoverride"])) {
                    $product_total_today_db = $product_recurring_db = $product_onetime = $productdata["priceoverride"];
                    $singleProductTotalToday = $singleProductOnetime = $singleProductRecurring = $productdata["priceoverride"];
                    $product_setup = 0;
                    if ($billingcycle === WHMCS\Billing\Cycles::CYCLE_ONETIME) {
                        $product_recurring_db = $singleProductRecurring = 0;
                    }
                }
                $applyTaxToCart = WHMCS\Config\Setting::getValue("TaxEnabled") && $tax && !$clientsdetails["taxexempt"];
                if ($applyTaxToCart) {
                    $taxLineItemsQty = $allowqty === WHMCS\Cart\Cart::QUANTITY_SCALING ? 1 : $qty;
                    $cart_tax = array_merge($cart_tax, array_fill(0, $taxLineItemsQty, $singleProductTotalToday));
                    if (!isset($recurring_tax[$billingcycle])) {
                        $recurring_tax[$billingcycle] = [];
                    }
                    $recurring_tax[$billingcycle] = array_merge($recurring_tax[$billingcycle], array_fill(0, $taxLineItemsQty, $singleProductRecurring));
                }
                $firstqtydiscountonly = false;
                if ($promotioncode) {
                    $onetimediscount = $recurringdiscount = $promoid = $firstqtydiscountedamtonetime = $firstqtydiscountedamtrecurring = 0;
                    if ($promocalc = CalcPromoDiscount($pid, $databasecycle, $product_total_today_db, $product_recurring_db, $product_setup, $allowqty)) {
                        $applyonce = $promocalc["applyonce"];
                        $onetimediscount = $promocalc["onetimediscount"];
                        if ($applyonce && $promo_data["type"] === WHMCS\Product\Promotion::TYPE_FREE_SETUP && $allowqty && $allowqty !== WHMCS\Cart\Cart::QUANTITY_SCALING) {
                            $onetimediscount /= $qty;
                        }
                        if (!$applyonce && $promo_data["type"] === WHMCS\Product\Promotion::TYPE_FIXED_AMOUNT && $allowqty && $allowqty !== WHMCS\Cart\Cart::QUANTITY_SCALING) {
                            $onetimediscount *= $qty;
                            if ($product_total_today_db <= $onetimediscount) {
                                $onetimediscount = $product_total_today_db;
                            }
                        } else {
                            if ($applyonce && $promo_data["type"] === WHMCS\Product\Promotion::TYPE_FIXED_AMOUNT && $allowqty && $allowqty !== WHMCS\Cart\Cart::QUANTITY_SCALING) {
                                $onetimediscount *= $qty;
                                if ($product_total_today_db / $qty <= $onetimediscount) {
                                    $onetimediscount = $product_total_today_db / $qty;
                                }
                            }
                        }
                        $recurringdiscount = $promocalc["recurringdiscount"];
                        $product_total_today_db -= $onetimediscount;
                        if ($allowqty && $allowqty === WHMCS\Cart\Cart::QUANTITY_MULTIPLE && 1 < $qty) {
                            if (!$applyonce) {
                                $onetimediscount /= $qty;
                                $singleProductRecurring -= $recurringdiscount / $qty;
                            }
                            if ($applyonce) {
                                $recurringdiscount /= $qty;
                                $singleProductRecurring -= $recurringdiscount;
                            }
                            $singleProductTotalToday -= $onetimediscount;
                            $product_recurring_db -= $recurringdiscount;
                            if ($applyonce) {
                                $cart_discount += $onetimediscount;
                                $firstqtydiscountonly = true;
                                $firstqtydiscountedamtonetime = $singleProductTotalToday;
                                $firstqtydiscountedamtrecurring = $singleProductRecurring;
                                $product_total_today_db += $onetimediscount;
                                $singleProductTotalToday += $onetimediscount;
                                $product_recurring_db += $recurringdiscount;
                                $singleProductRecurring += $recurringdiscount;
                            } else {
                                $cart_discount += $onetimediscount * $qty;
                            }
                            if ($applyTaxToCart) {
                                $discount_quantity = $firstqtydiscountonly ? 1 : $qty;
                            }
                        } else {
                            if ($allowqty && $allowqty === WHMCS\Cart\Cart::QUANTITY_SCALING && 1 < $qty) {
                                $singleProductTotalToday -= $onetimediscount;
                                $product_recurring_db -= $recurringdiscount;
                                $singleProductRecurring -= $recurringdiscount;
                                if ($applyonce) {
                                    $cart_discount += $onetimediscount;
                                    $firstqtydiscountonly = true;
                                    $firstqtydiscountedamtonetime = $singleProductTotalToday;
                                    $firstqtydiscountedamtrecurring = $singleProductRecurring;
                                    $product_total_today_db += $onetimediscount;
                                    $singleProductTotalToday += $onetimediscount;
                                    $product_recurring_db += $recurringdiscount;
                                    $singleProductRecurring += $recurringdiscount;
                                } else {
                                    $cart_discount += $onetimediscount;
                                }
                                if ($applyTaxToCart) {
                                    $discount_quantity = $qty;
                                }
                            } else {
                                $singleProductTotalToday -= $onetimediscount;
                                $product_recurring_db -= $recurringdiscount;
                                $singleProductRecurring -= $recurringdiscount;
                                $cart_discount += $onetimediscount;
                                if ($applyTaxToCart) {
                                    $discount_quantity = $firstqtydiscountonly ? 1 : $qty;
                                }
                            }
                        }
                        if ($applyTaxToCart) {
                            if ($onetimediscount != 0) {
                                $cart_tax = array_merge($cart_tax, array_fill(0, $discount_quantity, -1 * $onetimediscount));
                            }
                            if ($recurringdiscount != 0) {
                                $recurring_tax[$billingcycle] = array_merge($recurring_tax[$billingcycle], array_fill(0, $discount_quantity, -1 * $recurringdiscount));
                            }
                        }
                        $promoid = $promo_data["id"];
                    }
                }
                $cart_total += $product_total_today_db;
                $product_total_qty_recurring = $product_recurring_db;
                if ($firstqtydiscountonly) {
                    $cart_total = $cart_total - $cart_discount;
                    $product_total_qty_recurring = $product_total_qty_recurring - $singleProductRecurring + $firstqtydiscountedamtrecurring;
                }
                if (!isset($recurring_cycles_total[$billingcycle])) {
                    $recurring_cycles_total[$billingcycle] = 0;
                }
                $recurring_cycles_total[$billingcycle] += $product_total_qty_recurring;
                $domain = "";
                if (!empty($productdata["domain"])) {
                    if (isset($productdata["strictDomain"]) && $productdata["strictDomain"] === false) {
                        $domain = $productdata["domain"];
                    } else {
                        (new WHMCS\Domains())->splitAndCleanDomainInput($productdata["domain"]);
                        $domain = (new WHMCS\Domains\Domain($productdata["domain"]))->toUnicode();
                    }
                }
                $serverhostname = isset($productdata["server"]["hostname"]) ? $productdata["server"]["hostname"] : "";
                $serverns1prefix = isset($productdata["server"]["ns1prefix"]) ? $productdata["server"]["ns1prefix"] : "";
                $serverns2prefix = isset($productdata["server"]["ns2prefix"]) ? $productdata["server"]["ns2prefix"] : "";
                $serverrootpw = isset($productdata["server"]["rootpw"]) ? encrypt($productdata["server"]["rootpw"]) : "";
                if ($serverns1prefix && $domain) {
                    $serverns1prefix = $serverns1prefix . "." . $domain;
                }
                if ($serverns2prefix && $domain) {
                    $serverns2prefix = $serverns2prefix . "." . $domain;
                }
                if ($serverhostname) {
                    $serverhostname = trim($serverhostname, " .");
                    if (1 < substr_count($serverhostname, ".") || !$domain) {
                        $domain = $serverhostname;
                    } else {
                        $domain = $serverhostname . "." . $domain;
                    }
                }
                $productdata["domain"] = $domain;
                if ($checkout) {
                    $multiqtyids = [];
                    $qtycount = 1;
                    while ($qtycount <= $qty) {
                        $quantityId = $qtycount;
                        if ($firstqtydiscountonly) {
                            if ($one_time_discount_applied) {
                                $promoid = 0;
                            } else {
                                $one_time_discount_applied = true;
                            }
                        }
                        $serverid = $servertype ? getServerID($servertype, $servergroup) : "0";
                        $hostingquerydates = $databasecycle == "Free Account" ? "0000-00-00" : date("Y-m-d");
                        $firstpaymentamount = $firstqtydiscountonly && $qtycount == 1 ? $firstqtydiscountedamtonetime : $singleProductTotalToday;
                        $recurringamount = $firstqtydiscountonly && $qtycount == 1 ? $firstqtydiscountedamtrecurring : $singleProductRecurring;
                        $quantity = 1;
                        if ($allowqty === WHMCS\Cart\Cart::QUANTITY_SCALING) {
                            $quantity = $qty;
                            $qtycount = $qty + 1;
                        }
                        $promoid ? exit : NULL;
                    }
                }
                $addonsarray = [];
                $addons = $productdata["addons"];
                $addonProvisioningType = WHMCS\Product\Addon::PROVISIONING_TYPE_STANDARD;
                if ($addons) {
                    foreach ($addons as $addonData) {
                        $addonid = $addonData["addonid"];
                        $addonQuantity = $addonData["qty"];
                        $data = WHMCS\Product\Addon::find($addonid);
                        if ($data) {
                            $addon_name = $data["name"];
                            $addon_description = $data["description"];
                            $addon_billingcycle = $data["billingcycle"];
                            $addon_tax = $data["tax"];
                            $serverType = $data["module"];
                            $serverGroupId = $data["server_group_id"];
                            $addonAllowQuantity = $data->allowMultipleQuantities;
                            $addonProvisioningType = $data->provisioningType;
                            if ($addonAllowQuantity === WHMCS\Cart\Cart::QUANTITY_MULTIPLE) {
                                $addonAllowQuantity = 0;
                                $addonQuantity = 1;
                            }
                            if (!WHMCS\Config\Setting::getValue("TaxEnabled")) {
                                $addon_tax = "";
                            }
                            $addonIsProrated = $data->prorate;
                            switch ($addon_billingcycle) {
                                case "recurring":
                                    $availableAddonCycles = [];
                                    $data = WHMCS\Database\Capsule::table("tblpricing")->where("type", "=", "addon")->where("currency", "=", $currency["id"])->where("relid", "=", $addonid)->first();
                                    $databaseCycles = (new WHMCS\Billing\Cycles())->getSystemBillingCycles(true);
                                    foreach ($databaseCycles as $dbCyclesKey => $value) {
                                        if (0 <= $data->{$value}) {
                                            $objectKey = substr($value, 0, 1) . "setupfee";
                                            $availableAddonCycles[$value] = ["price" => $data->{$value}, "setup" => $data->{$objectKey}];
                                        }
                                    }
                                    $addon_setupfee = 0;
                                    $addon_recurring = 0;
                                    $addon_billingcycle = "Free Account";
                                    if ($availableAddonCycles) {
                                        if (array_key_exists($billingcycle, $availableAddonCycles)) {
                                            $addon_setupfee = $availableAddonCycles[$billingcycle]["setup"];
                                            $addon_recurring = $availableAddonCycles[$billingcycle]["price"];
                                            $addon_billingcycle = $billingcycle;
                                        } else {
                                            foreach ($availableAddonCycles as $cycle => $data) {
                                                $addon_setupfee = $data["setup"];
                                                $addon_recurring = $data["price"];
                                                $addon_billingcycle = $cycle;
                                            }
                                        }
                                    }
                                    $addon_billingcycle = ucfirst($addon_billingcycle);
                                    if ($addon_billingcycle == "Semiannually") {
                                        $addon_billingcycle = "Semi-Annually";
                                    }
                                    break;
                                case "free":
                                case "Free":
                                case "Free Account":
                                    $addon_setupfee = 0;
                                    $addon_recurring = 0;
                                    $addon_billingcycle = "Free";
                                    break;
                                case "onetime":
                                    $addon_billingcycle = "One Time";
                                    break;
                                case "One Time":
                                default:
                                    $result = select_query("tblpricing", "msetupfee,monthly", ["type" => "addon", "currency" => $currency["id"], "relid" => $addonid]);
                                    $data = mysql_fetch_array($result);
                                    $addon_setupfee = $data["msetupfee"];
                                    $addon_recurring = $data["monthly"];
                                    $hookret = run_hook("OrderAddonPricingOverride", ["key" => $key, "pid" => $pid, "addonid" => $addonid, "proddata" => $productdata]);
                                    foreach ($hookret as $hookret2) {
                                        if (is_array($hookret2)) {
                                            if ($hookret2["setup"]) {
                                                $addon_setupfee = $hookret2["setup"];
                                            }
                                            if ($hookret2["recurring"]) {
                                                $addon_recurring = $hookret2["recurring"];
                                            }
                                        }
                                    }
                                    if (!($addon_billingcycle == "recurring" || (new WHMCS\Billing\Cycles())->isRecurring($addon_billingcycle)) || !$proratabilling) {
                                        $addonIsProrated = false;
                                    }
                                    $addonNextDueDate = $carbonNow = WHMCS\Carbon::now();
                                    $addonChargeNextMonthDay = $proratabilling ? $proratachargenextmonth : 32;
                                    if ($addonIsProrated) {
                                        $addonProrataValues = getProrataValues($addon_billingcycle, $addon_recurring, $proratadate, $addonChargeNextMonthDay, $carbonNow->day, $carbonNow->month, $carbonNow->year, $client->id);
                                        $addonProratedDate = $addonProrataValues["date"];
                                        $addon_recurring_prorata = $addonProrataValues["amount"];
                                    }
                                    $addon_total_today = ($addonIsProrated ? $addon_recurring_prorata : $addon_recurring) * $addonQuantity;
                                    $addon_recurring *= $addonQuantity;
                                    $addon_total_today_db = $addon_setupfee + $addon_total_today;
                                    $addon_recurring_db = $addon_recurring;
                                    $addon_setupfee_db = $addon_setupfee;
                                    if ($allowqty === WHMCS\Cart\Cart::QUANTITY_MULTIPLE) {
                                        $addon_total_today *= $qty;
                                        $addon_setupfee *= $qty;
                                        $addon_recurring *= $qty;
                                    }
                                    if (WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxInclusiveDeduct")) {
                                        $addon_setupfee_db = round($addon_setupfee_db / $excltaxrate, 2);
                                        $addon_total_today_db = round($addon_total_today_db / $excltaxrate, 2);
                                        $addon_recurring_db = round($addon_recurring_db / $excltaxrate, 2);
                                    }
                                    if ($promotioncode) {
                                        $onetimediscount = $recurringdiscount = $promoid = 0;
                                        if ($promocalc = CalcPromoDiscount("A" . $addonid, $addon_billingcycle, $addon_total_today_db, $addon_recurring_db, $addon_setupfee)) {
                                            $onetimediscount = $promocalc["onetimediscount"];
                                            $recurringdiscount = $promocalc["recurringdiscount"];
                                            $setupDiscount = $onetimediscount - ($addon_total_today_db - $addon_setupfee_db);
                                            $addon_setupfee_db -= $setupDiscount;
                                            $addon_total_today_db -= $onetimediscount;
                                            $addon_recurring_db -= $recurringdiscount;
                                            $cart_discount += $onetimediscount * $addonQuantity;
                                        }
                                    }
                                    if ($checkout) {
                                        if ($addon_billingcycle == "Free") {
                                            $addon_billingcycle = "Free Account";
                                        }
                                        $qtycount = 1;
                                        while ($qtycount <= $qty) {
                                            $serviceid = $multiqtyids[$qtycount];
                                            $serverId = 0;
                                            if ($addonProvisioningType !== WHMCS\Product\Addon::PROVISIONING_TYPE_FEATURE) {
                                                $serverId = $serverType ? WHMCS\Module\Server::getServerId($serverType, $serverGroupId) : "0";
                                            }
                                            $quantity = 1;
                                            if ($addonAllowQuantity === WHMCS\Cart\Cart::QUANTITY_SCALING) {
                                                $quantity = $addonQuantity;
                                            }
                                            $aid = insert_query("tblhostingaddons", ["hostingid" => $serviceid, "addonid" => $addonid, "userid" => $client->id, "orderid" => $orderid, "server" => $serverId, "regdate" => "now()", "name" => "", "qty" => $quantity, "firstpaymentamount" => $addon_total_today_db, "setupfee" => $addon_setupfee_db, "recurring" => $addon_recurring_db, "billingcycle" => $addon_billingcycle, "status" => "Pending", "nextduedate" => $addonNextDueDate->toDateString(), "nextinvoicedate" => "now()", "paymentmethod" => $paymentmethod, "tax" => $addon_tax]);
                                            $serviceAddonModel = WHMCS\Service\Addon::find($aid);
                                            if ($addonIsProrated && $addonProratedDate) {
                                                $serviceAddonModel->prorataDate = $addonProratedDate;
                                                $serviceAddonModel->save();
                                            }
                                            if (!$_SESSION["cart"]["geninvoicedisabled"] && $addon_billingcycle != "free" && 0 <= $addon_total_today_db) {
                                                $invoiceAddonDetails = getInvoiceAddonDetails($serviceAddonModel);
                                                WHMCS\Billing\Invoice\Item::create(["type" => "Addon", "relid" => $aid, "description" => $invoiceAddonDetails["description"], "amount" => $addon_total_today_db, "userid" => $client->id, "taxed" => $invoiceAddonDetails["tax"], "duedate" => $addonNextDueDate->toDateString(), "paymentmethod" => $paymentmethod]);
                                            }
                                            $orderaddonids[] = $aid;
                                            $emailItem = ["service" => "", "domain" => ""];
                                            $emailItem["qty"] = 0;
                                            if (1 < $quantity) {
                                                $orderEmailItems .= $quantity . " x ";
                                                $emailItem["qty"] = $quantity;
                                            }
                                            $orderEmailItems .= $_LANG["clientareaaddon"] . ": " . $addon_name . "<br>\n" . $_LANG["ordersetupfee"] . ": " . new WHMCS\View\Formatter\Price($addonsetupfee, $currency) . "<br>\n";
                                            $emailItem["addon"] = $addon_name;
                                            $emailItem["setupFee"] = new WHMCS\View\Formatter\Price($addonsetupfee, $currency);
                                            if ($addon_recurring_db) {
                                                $orderEmailItems .= $_LANG["recurringamount"] . ": " . new WHMCS\View\Formatter\Price($addon_recurring_db, $currency) . "<br>\n";
                                                $emailItem["recurringPayment"] = new WHMCS\View\Formatter\Price($addon_recurring_db, $currency);
                                            }
                                            $emailItem["cycle"] = $addon_billingcycle;
                                            $orderEmailItems .= $_LANG["orderbillingcycle"] . ": " . $_LANG["orderpaymentterm" . str_replace(["-", " "], "", strtolower($addon_billingcycle))] . "<br>\n<br>\n";
                                            $adminEmailItems[] = $emailItem;
                                            if ($allowqty !== WHMCS\Cart\Cart::QUANTITY_SCALING) {
                                                $qtycount++;
                                            }
                                        }
                                    }
                                    $cartQuantity = $allowqty === WHMCS\Cart\Cart::QUANTITY_MULTIPLE ? $qty : 1;
                                    $cart_total += $addon_total_today_db * $cartQuantity;
                                    $addon_billingcycle = str_replace(["-", " "], "", strtolower($addon_billingcycle));
                                    if ($addon_tax && !$clientsdetails["taxexempt"]) {
                                        $cart_tax[] = $addon_total_today_db * $cartQuantity;
                                        if ($addon_billingcycle != "onetime") {
                                            if (!isset($recurring_tax[$addon_billingcycle])) {
                                                $recurring_tax[$addon_billingcycle] = [];
                                            }
                                            $recurring_tax[$addon_billingcycle][] = $addon_recurring_db * $cartQuantity;
                                        }
                                    }
                                    if ($addon_billingcycle != "onetime") {
                                        $recurring_cycles_total[$addon_billingcycle] += $addon_recurring_db * $cartQuantity;
                                    }
                                    $addon_isRecurring = false;
                                    if ($addon_setupfee == "0" && $addon_recurring == "0") {
                                        $pricing_text = $_LANG["orderfree"];
                                    } else {
                                        $pricing_text = new WHMCS\View\Formatter\Price($addon_total_today, $currency);
                                        if ($addon_setupfee && $addon_setupfee != "0.00") {
                                            $pricing_text .= " + " . new WHMCS\View\Formatter\Price($addon_setupfee, $currency) . " " . $_LANG["ordersetupfee"];
                                        }
                                        if ($allowqty && 1 < $qty) {
                                            $pricing_text .= $_LANG["invoiceqtyeach"] . "<br />" . $_LANG["invoicestotal"] . ": " . new WHMCS\View\Formatter\Price($addon_total_today, $currency);
                                        }
                                        if ($addon_billingcycle != "onetime") {
                                            $addon_isRecurring = true;
                                        }
                                    }
                                    $addonsarray[] = ["addonid" => $addonid, "name" => $addon_name, "pricingtext" => $pricing_text, "setup" => 0 < $addon_setupfee ? new WHMCS\View\Formatter\Price($addon_setupfee, $currency) : "", "recurring" => new WHMCS\View\Formatter\Price($addon_recurring, $currency), "isRecurring" => $addon_isRecurring, "billingcycle" => $addon_billingcycle, "billingcyclefriendly" => Lang::trans("orderpaymentterm" . $addon_billingcycle), "totaltoday" => new WHMCS\View\Formatter\Price($addon_total_today, $currency), "taxed" => $addon_tax, "allowqty" => $addonAllowQuantity, "qty" => $addonQuantity, "isProrated" => $addonIsProrated, "prorataDate" => fromMySQLDate($addonProratedDate)];
                                    $productdata["pricing"]["setup"] += $addon_setupfee;
                                    $productdata["pricing"]["addons"] += $addon_recurring;
                                    if ($addon_isRecurring) {
                                        $productdata["pricing"]["recurring"][$addon_billingcycle] += $addon_recurring;
                                    }
                                    $productdata["pricing"]["totaltoday"] += $addon_total_today + $addon_setupfee;
                            }
                        }
                    }
                }
                $productdata["addons"] = $addonsarray;
                if (WHMCS\Config\Setting::getValue("TaxEnabled") && $tax && !$clientsdetails["taxexempt"]) {
                    $taxCalculator->setTaxBase($productdata["pricing"]["totaltoday"]);
                    $total_tax_1 = $taxCalculator->getLevel1TaxTotal();
                    $total_tax_2 = $taxCalculator->getLevel2TaxTotal();
                    $productdata["pricing"]["totaltoday"] = $taxCalculator->getTotalAfterTaxes();
                    if (0 < $total_tax_1) {
                        $productdata["pricing"]["tax1"] = new WHMCS\View\Formatter\Price($total_tax_1, $currency);
                    }
                    if (0 < $total_tax_2) {
                        $productdata["pricing"]["tax2"] = new WHMCS\View\Formatter\Price($total_tax_2, $currency);
                    }
                }
                $productdata["pricing"]["productonlysetup"] = 0 < $productdata["pricing"]["productonlysetup"] ? new WHMCS\View\Formatter\Price($productdata["pricing"]["productonlysetup"], $currency) : "";
                $productdata["pricing"]["setup"] = new WHMCS\View\Formatter\Price($productdata["pricing"]["setup"], $currency);
                foreach ($productdata["pricing"]["recurring"] as $cycle => $recurring) {
                    unset($productdata["pricing"]["recurring"][$cycle]);
                    if (0 < $recurring) {
                        $recurringwithtax = $recurring;
                        $recurringbeforetax = $recurringwithtax;
                        if (WHMCS\Config\Setting::getValue("TaxEnabled") && $tax && !$clientsdetails["taxexempt"]) {
                            $taxCalculator->setTaxBase($recurring);
                            $recurringwithtax = $taxCalculator->getTotalAfterTaxes();
                            $recurringbeforetax = $taxCalculator->getTotalBeforeTaxes();
                        }
                        $productdata["pricing"]["recurring"][$_LANG["orderpaymentterm" . $cycle]] = new WHMCS\View\Formatter\Price($recurringwithtax, $currency);
                        $productdata["pricing"]["recurringexcltax"][$_LANG["orderpaymentterm" . $cycle]] = new WHMCS\View\Formatter\Price($recurringbeforetax, $currency);
                    }
                }
                if (isset($productdata["pricing"]["addons"]) && 0 < $productdata["pricing"]["addons"]) {
                    $productdata["pricing"]["addons"] = new WHMCS\View\Formatter\Price($productdata["pricing"]["addons"], $currency);
                }
                $productdata["pricing"]["totaltoday"] = new WHMCS\View\Formatter\Price($productdata["pricing"]["totaltoday"], $currency);
                $productdata["pricing"]["totaltodayexcltax"] = new WHMCS\View\Formatter\Price($productdata["pricing"]["totaltodayexcltax"], $currency);
                $productdata["pricing"]["totalTodayExcludingTaxSetup"] = new WHMCS\View\Formatter\Price($productdata["pricing"]["totalTodayExcludingTaxSetup"], $currency);
                $productdata["taxed"] = $tax;
                $productsarray[$key] = $productdata;
            }
        }
        if ($productRemovedFromCart) {
            $_SESSION["cart"]["products"] = array_values($_SESSION["cart"]["products"]);
            $cartdata["productRemovedFromCart"] = true;
        }
    }
    $cartdata["products"] = $productsarray;
    $addonsarray = [];
    $cartAddons = $orderForm->getCartDataByKey("addons");
    if (is_array($cartAddons)) {
        foreach ($cartAddons as $key => $addon) {
            $addonid = $addon["id"];
            $serviceid = $addon["productid"];
            $addonQuantity = $addon["qty"];
            $service = WHMCS\Service\Service::find($serviceid);
            if ($service->clientId == $client->id) {
                $requested_billingcycle = isset($addon["billingcycle"]) ? $addon["billingcycle"] : "";
                if (!$requested_billingcycle) {
                    $requested_billingcycle = strtolower(str_replace("-", "", $service->billingCycle));
                }
                $data = WHMCS\Product\Addon::find($addonid);
                if ($data) {
                    $addon_name = $data["name"];
                    if (array_key_exists("sslCompetitiveUpgrade", $addon) && $addon["sslCompetitiveUpgrade"]) {
                        $addon_name .= "<br><small>" . Lang::trans("store.ssl.competitiveUpgradeQualified") . "</small>";
                    }
                    $addon_description = $data["description"];
                    $addon_billingcycle = $data["billingcycle"];
                    $addon_tax = $data["tax"];
                    $serverType = $data["module"];
                    $serverGroupId = $data["server_group_id"];
                    $addonAllowQuantity = $data->allowMultipleQuantities;
                    $addonProvisioningType = $data->provisioningType;
                    if ($addonAllowQuantity === WHMCS\Cart\Cart::QUANTITY_MULTIPLE) {
                        $addonAllowQuantity = 0;
                        $addonQuantity = 1;
                    }
                    if (!WHMCS\Config\Setting::getValue("TaxEnabled")) {
                        $addon_tax = "";
                    }
                    $addonIsProrated = $data->prorate;
                    switch ($addon_billingcycle) {
                        case "recurring":
                            $availableAddonCycles = [];
                            $data = WHMCS\Database\Capsule::table("tblpricing")->where("type", "=", "addon")->where("currency", "=", $currency["id"])->where("relid", "=", $addonid)->first();
                            $databaseCycles = (new WHMCS\Billing\Cycles())->getSystemBillingCycles(true);
                            foreach ($databaseCycles as $dbCyclesKey => $value) {
                                if (0 <= $data->{$value}) {
                                    $objectKey = substr($value, 0, 1) . "setupfee";
                                    $availableAddonCycles[$value] = ["price" => $data->{$value}, "setup" => $data->{$objectKey}];
                                }
                            }
                            $addon_setupfee = 0;
                            $addon_recurring = 0;
                            $addon_billingcycle = "Free";
                            if ($availableAddonCycles) {
                                if (array_key_exists($requested_billingcycle, $availableAddonCycles)) {
                                    $addon_setupfee = $availableAddonCycles[$requested_billingcycle]["setup"];
                                    $addon_recurring = $availableAddonCycles[$requested_billingcycle]["price"];
                                    $addon_billingcycle = $requested_billingcycle;
                                } else {
                                    foreach ($availableAddonCycles as $cycle => $data) {
                                        $addon_setupfee = $data["setup"];
                                        $addon_recurring = $data["price"];
                                        $addon_billingcycle = $cycle;
                                    }
                                }
                            }
                            $addon_billingcycle = ucfirst($addon_billingcycle);
                            if ($addon_billingcycle == "Semiannually") {
                                $addon_billingcycle = "Semi-Annually";
                            }
                            break;
                        case "free":
                        case "Free":
                        case "Free Account":
                            $addon_setupfee = 0;
                            $addon_recurring = 0;
                            $addon_billingcycle = "Free";
                            break;
                        case "onetime":
                        case "One Time":
                        default:
                            $result = select_query("tblpricing", "msetupfee,monthly", ["type" => "addon", "currency" => $currency["id"], "relid" => $addonid]);
                            $data = mysql_fetch_array($result);
                            $addon_setupfee = $data["msetupfee"];
                            $addon_recurring = $data["monthly"];
                            $hookret = run_hook("OrderAddonPricingOverride", ["key" => $key, "addonid" => $addonid, "serviceid" => $serviceid]);
                            foreach ($hookret as $hookret2) {
                                if (is_array($hookret2)) {
                                    if ($hookret2["setup"]) {
                                        $addon_setupfee = $hookret2["setup"];
                                    }
                                    if ($hookret2["recurring"]) {
                                        $addon_recurring = $hookret2["recurring"];
                                    }
                                }
                            }
                            if (!($addon_billingcycle == "recurring" || (new WHMCS\Billing\Cycles())->isRecurring($addon_billingcycle))) {
                                $addonIsProrated = false;
                            }
                            $addonNextDueDate = $carbonNow = WHMCS\Carbon::now();
                            $addonChargeNextMonthDay = $service->product->proRataBilling ? $service->product->proRataChargeNextMonthAfterDay : 32;
                            $serviceNextDueDate = WHMCS\Carbon::safeCreateFromMySqlDate($service->nextDueDate);
                            $prorataUntilDate = $service->billingCycle == ucfirst($requested_billingcycle) ? $serviceNextDueDate : NULL;
                            if ($addonIsProrated) {
                                $addonProrataValues = getProrataValues($requested_billingcycle, $addon_recurring, $serviceNextDueDate->day, $addonChargeNextMonthDay, $carbonNow->day, $carbonNow->month, $carbonNow->year, $client->id, $prorataUntilDate);
                                $addonProratedDate = $addonProrataValues["date"];
                                $addon_recurring_prorata = $addonProrataValues["amount"];
                            }
                            $addon_total_today = ($addonIsProrated ? $addon_recurring_prorata : $addon_recurring) * $addonQuantity;
                            $addon_recurring *= $addonQuantity;
                            $addon_setupfee_db = $addon_setupfee;
                            $addon_total_today_db = $addon_setupfee + $addon_total_today;
                            $addon_recurring_db = $addon_recurring;
                            if (WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxInclusiveDeduct")) {
                                $addon_setupfee_db = round($addon_setupfee_db / $excltaxrate, 2);
                                $addon_total_today_db = round($addon_total_today_db / $excltaxrate, 2);
                                $addon_recurring_db = round($addon_recurring_db / $excltaxrate, 2);
                            }
                            if ($promotioncode) {
                                $onetimediscount = $recurringdiscount = $promoid = 0;
                                if ($promocalc = CalcPromoDiscount("A" . $addonid, $addon_billingcycle, $addon_total_today_db, $addon_recurring_db, $addon_setupfee)) {
                                    $onetimediscount = $promocalc["onetimediscount"];
                                    $recurringdiscount = $promocalc["recurringdiscount"];
                                    $setupDiscount = $onetimediscount - ($addon_total_today_db - $addon_setupfee_db);
                                    $addon_setupfee_db -= $setupDiscount;
                                    $addon_total_today_db -= $onetimediscount;
                                    $addon_recurring_db -= $recurringdiscount;
                                    $cart_discount += $onetimediscount;
                                }
                            }
                            if ($checkout) {
                                if ($addon_billingcycle == "Free") {
                                    $addon_billingcycle = "Free Account";
                                }
                                $serverId = 0;
                                if ($addonProvisioningType !== WHMCS\Product\Addon::PROVISIONING_TYPE_FEATURE) {
                                    $serverId = $serverType ? WHMCS\Module\Server::getServerId($serverType, $serverGroupId) : "0";
                                }
                                $aid = insert_query("tblhostingaddons", ["hostingid" => $serviceid, "addonid" => $addonid, "userid" => $client->id, "orderid" => $orderid, "server" => $serverId, "regdate" => "now()", "name" => "", "setupfee" => $addon_setupfee_db, "recurring" => $addon_recurring_db, "billingcycle" => $addon_billingcycle, "status" => "Pending", "nextduedate" => $addonNextDueDate->toDateString(), "nextinvoicedate" => "now()", "paymentmethod" => $paymentmethod, "tax" => $addon_tax, "qty" => $addonQuantity, "firstpaymentamount" => $addon_total_today_db]);
                                $serviceAddonModel = WHMCS\Service\Addon::find($aid);
                                if ($addonIsProrated && $addonProratedDate) {
                                    $serviceAddonModel->prorataDate = $addonProratedDate;
                                    $serviceAddonModel->save();
                                }
                                if (array_key_exists("sslCompetitiveUpgrade", $addon) && $addon["sslCompetitiveUpgrade"]) {
                                    $sslCompetitiveUpgradeAddons = WHMCS\Session::get("SslCompetitiveUpgradeAddons");
                                    if (!is_array($sslCompetitiveUpgradeAddons)) {
                                        $sslCompetitiveUpgradeAddons = [];
                                    }
                                    $sslCompetitiveUpgradeAddons[] = $aid;
                                    WHMCS\Session::set("SslCompetitiveUpgradeAddons", $sslCompetitiveUpgradeAddons);
                                }
                                if (!$_SESSION["cart"]["geninvoicedisabled"] && $addon_billingcycle != "free" && 0 <= $addon_total_today_db) {
                                    $invoiceAddonDetails = getInvoiceAddonDetails($serviceAddonModel, true);
                                    WHMCS\Billing\Invoice\Item::create(["type" => "Addon", "relid" => $aid, "description" => $invoiceAddonDetails["description"], "amount" => $addon_total_today_db, "userid" => $client->id, "taxed" => $invoiceAddonDetails["tax"], "duedate" => $addonNextDueDate->toDateString(), "paymentmethod" => $paymentmethod]);
                                }
                                $orderaddonids[] = $aid;
                                $orderEmailItems .= $_LANG["clientareaaddon"] . ": " . $addon_name . "<br>\n" . $_LANG["ordersetupfee"] . ": " . new WHMCS\View\Formatter\Price($addonsetupfee * $addonQuantity, $currency) . "<br>\n";
                                $emailItem = ["service" => "", "domain" => "", "addon" => $addon_name, "setupFee" => new WHMCS\View\Formatter\Price($addonsetupfee, $currency)];
                                if ($addon_recurring_db) {
                                    $orderEmailItems .= $_LANG["recurringamount"] . ": " . new WHMCS\View\Formatter\Price($addon_recurring_db * $addonQuantity, $currency) . "<br>\n";
                                    $emailItem["recurringPayment"] = new WHMCS\View\Formatter\Price($addon_recurring_db, $currency);
                                }
                                $orderEmailItems .= $_LANG["orderbillingcycle"] . ": " . $_LANG["orderpaymentterm" . str_replace(["-", " "], "", strtolower($addon_billingcycle))] . "<br>\n<br>\n";
                                $emailItem["cycle"] = $addon_billingcycle;
                                $adminEmailItems[] = $emailItem;
                            }
                            $cart_total += $addon_total_today_db;
                            $addon_billingcycle = str_replace(["-", " "], "", strtolower($addon_billingcycle));
                            if ($addon_tax && !$clientsdetails["taxexempt"]) {
                                $cart_tax[] = $addon_total_today_db;
                                if ($addon_billingcycle != "onetime") {
                                    if (!isset($recurring_tax[$addon_billingcycle])) {
                                        $recurring_tax[$addon_billingcycle] = [];
                                    }
                                    $recurring_tax[$addon_billingcycle][] = $addon_recurring_db;
                                }
                            }
                            if ($addon_billingcycle != "onetime") {
                                $recurring_cycles_total[$addon_billingcycle] += $addon_recurring_db;
                            }
                            $addon_isRecurring = false;
                            if ($addon_setupfee == "0" && $addon_recurring == "0") {
                                $pricing_text = $_LANG["orderfree"];
                            } else {
                                $pricing_text = new WHMCS\View\Formatter\Price($addon_total_today, $currency);
                                if ($addon_setupfee && $addon_setupfee != "0.00") {
                                    $pricing_text .= " + " . new WHMCS\View\Formatter\Price($addon_setupfee, $currency) . " " . $_LANG["ordersetupfee"];
                                }
                                if ($addon_billingcycle != "onetime") {
                                    $addon_isRecurring = true;
                                }
                            }
                            $result = select_query("tblhosting", "tblproducts.name,tblhosting.packageid,tblhosting.domain", ["tblhosting.id" => $serviceid], "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
                            $data = mysql_fetch_array($result);
                            $productname = $isAdmin ? $data["name"] : WHMCS\Product\Product::getProductName($data["packageid"]);
                            $domainname = $data["domain"];
                            $addonsarray[] = ["addonid" => $addonid, "name" => $addon_name, "productname" => $productname, "domainname" => $domainname, "pricingtext" => $pricing_text, "setup" => 0 < $addon_setupfee ? new WHMCS\View\Formatter\Price($addon_setupfee, $currency) : "", "totaltoday" => new WHMCS\View\Formatter\Price($addon_total_today, $currency), "recurring" => new WHMCS\View\Formatter\Price($addon_recurring, $currency), "isRecurring" => $addon_isRecurring, "billingcycle" => $addon_billingcycle, "billingcyclefriendly" => Lang::trans("orderpaymentterm" . $addon_billingcycle), "taxed" => $addon_tax, "allowqty" => $addonAllowQuantity, "qty" => $addonQuantity, "isProrated" => $addonIsProrated, "prorataDate" => fromMySQLDate($addonProratedDate)];
                    }
                }
            }
        }
    }
    $cartdata["addons"] = $addonsarray;
    $totaldomainprice = 0;
    $cartDomains = $orderForm->getCartDataByKey("domains");
    if (is_array($cartDomains)) {
        $result = select_query("tblpricing", "", ["type" => "domainaddons", "currency" => $currency["id"], "relid" => 0]);
        $data = mysql_fetch_array($result);
        $domaindnsmanagementprice = $data["msetupfee"];
        $domainemailforwardingprice = $data["qsetupfee"];
        $domainidprotectionprice = $data["ssetupfee"];
        foreach ($cartDomains as $key => $domain) {
            $domaintype = $domain["type"];
            $domainname = $domain["domain"];
            $regperiod = $domain["regperiod"];
            $domainPriceOverride = array_key_exists("domainpriceoverride", $domain) ? $domain["domainpriceoverride"] : NULL;
            $domainRenewOverride = array_key_exists("domainrenewoverride", $domain) ? $domain["domainrenewoverride"] : NULL;
            $domainparts = explode(".", $domainname, 2);
            $idnLanguage = $domain["idnLanguage"];
            list($sld, $tld) = $domainparts;
            $temppricelist = getTLDPriceList("." . $tld, false, "", $client ? $client->id : 0);
            if (!isset($temppricelist[$regperiod][$domaintype])) {
                $tldyears = array_keys($temppricelist);
                $regperiod = $tldyears[0];
            }
            if (!isset($temppricelist[$regperiod][$domaintype])) {
                $errMsg = "Invalid TLD/Registration Period Supplied for Domain Registration";
                if ($whmcs->isApiRequest()) {
                    $apiresults = ["result" => "error", "message" => $errMsg];
                    return $apiresults;
                }
                throw new WHMCS\Exception\Fatal($errMsg);
            }
            if (array_key_exists($domainname, $freedomains)) {
                $tldyears = array_keys($temppricelist);
                $regperiod = $tldyears[0];
                $domainprice = "0.00";
                $renewprice = $freedomains[$domainname] == "once" ? $temppricelist[$regperiod]["renew"] : ($renewprice = "0.00");
            } else {
                $domainprice = $temppricelist[$regperiod][$domaintype];
                $renewprice = $temppricelist[$regperiod]["renew"];
            }
            $renewalPeriod = $regperiod;
            if (!$renewprice && $renewalPeriod == 10) {
                do {
                    $renewalPeriod -= 1;
                    $renewprice = $temppricelist[$renewalPeriod]["renew"];
                } while ($renewprice || 0 >= $renewalPeriod);
            }
            $before_priceoverride_value = "";
            if ($bundleoverride = bundlesGetProductPriceOverride("domain", $key)) {
                $before_priceoverride_value = $domainprice;
                $domainprice = $renewprice = $bundleoverride;
            }
            if (!is_null($domainPriceOverride)) {
                $domainprice = $domainPriceOverride;
            }
            if (!is_null($domainRenewOverride)) {
                $renewprice = $domainRenewOverride;
            }
            $hookret = run_hook("OrderDomainPricingOverride", ["type" => $domaintype, "domain" => $domainname, "regperiod" => $regperiod, "renewalperiod" => $renewalPeriod, "dnsmanagement" => $domain["dnsmanagement"], "emailforwarding" => $domain["emailforwarding"], "idprotection" => $domain["idprotection"], "eppcode" => WHMCS\Input\Sanitize::decode($domain["eppcode"]), "premium" => $domain["isPremium"]]);
            foreach ($hookret as $hookret2) {
                if (is_array($hookret2)) {
                    if (isset($hookret2["firstPaymentAmount"])) {
                        $before_priceoverride_value = $domainprice;
                        $domainprice = $hookret2["firstPaymentAmount"];
                    }
                    if (isset($hookret2["recurringAmount"])) {
                        $renewprice = $hookret2["recurringAmount"];
                    }
                } else {
                    if (strlen($hookret2)) {
                        $before_priceoverride_value = $domainprice;
                        $domainprice = $hookret2;
                    }
                }
            }
            if ($domain["dnsmanagement"]) {
                $dnsmanagement = true;
                $domainprice += $domaindnsmanagementprice * $regperiod;
                $renewprice += $domaindnsmanagementprice * $regperiod;
                if (strlen($before_priceoverride_value)) {
                    $before_priceoverride_value += $domaindnsmanagementprice * $regperiod;
                }
            } else {
                $dnsmanagement = false;
            }
            if ($domain["emailforwarding"]) {
                $emailforwarding = true;
                $domainprice += $domainemailforwardingprice * $regperiod;
                $renewprice += $domainemailforwardingprice * $regperiod;
                if (strlen($before_priceoverride_value)) {
                    $before_priceoverride_value += $domainemailforwardingprice * $regperiod;
                }
            } else {
                $emailforwarding = false;
            }
            if ($domain["idprotection"]) {
                $idprotection = true;
                $domainprice += $domainidprotectionprice * $regperiod;
                $renewprice += $domainidprotectionprice * $regperiod;
                if (strlen($before_priceoverride_value)) {
                    $before_priceoverride_value += $domainidprotectionprice * $regperiod;
                }
            } else {
                $idprotection = false;
            }
            if (WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxInclusiveDeduct")) {
                $domainprice = round($domainprice / $excltaxrate, 2);
                $renewprice = round($renewprice / $excltaxrate, 2);
            }
            $domain_price_db = $domainprice;
            $domain_renew_price_db = $renewprice;
            if ($promotioncode) {
                $onetimediscount = $recurringdiscount = $promoid = 0;
                if ($promocalc = CalcPromoDiscount("D." . $tld, $regperiod . "Years", $domain_price_db, $domain_renew_price_db)) {
                    $onetimediscount = $promocalc["onetimediscount"];
                    $recurringdiscount = $promocalc["recurringdiscount"];
                    $domain_price_db -= $onetimediscount;
                    $domain_renew_price_db -= $recurringdiscount;
                    $cart_discount += $onetimediscount;
                    $promoid = $promo_data["id"];
                }
            }
            if ($regperiod == "1") {
                $domain_billing_cycle = "annually";
            } else {
                if ($regperiod == "2") {
                    $domain_billing_cycle = "biennially";
                } else {
                    if ($regperiod == "3") {
                        $domain_billing_cycle = "triennially";
                    }
                }
            }
            if (!is_null($domain_renew_price_db)) {
                if (WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxDomains") && !$clientsdetails["taxexempt"]) {
                    if (!isset($recurring_tax[$domain_billing_cycle])) {
                        $recurring_tax[$domain_billing_cycle] = [];
                    }
                    $recurring_tax[$domain_billing_cycle][] = $domain_renew_price_db;
                }
                $recurring_cycles_total[$domain_billing_cycle] += $domain_renew_price_db;
            }
            if ($checkout) {
                $donotrenew = 1;
                if (App::get_config("DomainAutoRenewDefault")) {
                    $donotrenew = 0;
                }
                $domainid = insert_query("tbldomains", ["userid" => $client->id, "orderid" => $orderid, "type" => $domaintype, "registrationdate" => "now()", "domain" => $domainname, "firstpaymentamount" => $domain_price_db, "recurringamount" => $domain_renew_price_db, "registrationperiod" => $regperiod, "status" => "Pending", "paymentmethod" => $paymentmethod, "expirydate" => "00000000", "nextduedate" => "now()", "nextinvoicedate" => "now()", "dnsmanagement" => (int) $dnsmanagement, "emailforwarding" => (int) $emailforwarding, "idprotection" => (int) $idprotection, "donotrenew" => (int) $donotrenew, "promoid" => $promoid, "is_premium" => (int) $domain["isPremium"]]);
                if ($idnLanguage) {
                    $extraDetails = WHMCS\Domain\Extra::firstOrNew(["domain_id" => $domainid, "name" => "idnLanguage"]);
                    $extraDetails->value = $idnLanguage;
                    $extraDetails->save();
                }
                if (array_key_exists("registrarCostPrice", $domain)) {
                    $extraDetails = WHMCS\Domain\Extra::firstOrNew(["domain_id" => $domainid, "name" => "registrarCostPrice"]);
                    $extraDetails->value = $domain["registrarCostPrice"];
                    $extraDetails->save();
                    $extraDetails = WHMCS\Domain\Extra::firstOrNew(["domain_id" => $domainid, "name" => "registrarCurrency"]);
                    $extraDetails->value = (int) $domain["registrarCurrency"];
                    $extraDetails->save();
                }
                if ($domain["isPremium"] && array_key_exists("registrarRenewalCostPrice", $domain)) {
                    $extraDetails = WHMCS\Domain\Extra::firstOrNew(["domain_id" => $domainid, "name" => "registrarRenewalCostPrice"]);
                    $extraDetails->value = $domain["registrarRenewalCostPrice"];
                    $extraDetails->save();
                    $extraDetails = WHMCS\Domain\Extra::firstOrNew(["domain_id" => $domainid, "name" => "registrarCurrency"]);
                    if ((int) $extraDetails->value != (int) $domain["registrarCurrency"]) {
                        $extraDetails->value = $domain["registrarCurrency"];
                        $extraDetails->save();
                    }
                }
                $orderdomainids[] = $domainid;
                $orderEmailItems .= $_LANG["orderdomainregistration"] . ": " . ucfirst($domaintype) . "<br>\n" . $_LANG["orderdomain"] . ": " . $domainname . "<br>\n" . $_LANG["firstpaymentamount"] . ": " . new WHMCS\View\Formatter\Price($domain_price_db, $currency) . "<br>\n" . $_LANG["recurringamount"] . ": " . new WHMCS\View\Formatter\Price($domain_renew_price_db, $currency) . "<br>\n" . $_LANG["orderregperiod"] . ": " . $regperiod . " " . $_LANG["orderyears"] . "<br>\n";
                if ($dnsmanagement) {
                    $orderEmailItems .= " + " . $_LANG["domaindnsmanagement"] . "<br>\n";
                }
                if ($emailforwarding) {
                    $orderEmailItems .= " + " . $_LANG["domainemailforwarding"] . "<br>\n";
                }
                if ($idprotection) {
                    $orderEmailItems .= " + " . $_LANG["domainidprotection"] . "<br>\n";
                }
                $orderEmailItems .= "<br>\n";
                $emailItem = ["service" => "", "domain" => $domainname, "type" => ucfirst($domaintype), "firstPayment" => new WHMCS\View\Formatter\Price($domain_price_db, $currency), "recurringPayment" => new WHMCS\View\Formatter\Price($domain_renew_price_db, $currency), "registrationPeriod" => $regperiod, "dnsManagement" => (int) $dnsmanagement, "emailForwarding" => (int) $emailforwarding, "idProtection" => (int) $idprotection];
                $adminEmailItems[] = $emailItem;
                if (in_array($domaintype, ["register", "transfer"])) {
                    $additflds = new WHMCS\Domains\AdditionalFields();
                    $additflds->setTLD($tld)->setDomainType($domaintype)->setFieldValues($domain["fields"])->saveToDatabase($domainid);
                }
                if ($domaintype == "transfer" && $domain["eppcode"]) {
                    $domaineppcodes[$domainname] = $domain["eppcode"];
                }
            }
            $pricing_text = "";
            if (strlen($before_priceoverride_value)) {
                $pricing_text .= "<strike>" . new WHMCS\View\Formatter\Price($before_priceoverride_value, $currency) . "</strike> ";
            }
            $pricing_text .= new WHMCS\View\Formatter\Price($domainprice, $currency);
            $pricing = getTLDPriceList("." . $tld, true, $domaintype == "transfer" ? "transfer" : "");
            if (array_key_exists($domainname, $freedomains)) {
                $pricing = [key($pricing) => current($pricing)];
            }
            $renewPrice = new WHMCS\View\Formatter\Price($renewprice, $currency);
            $tempdomains[$key] = ["type" => $domaintype, "domain" => $domainname, "regperiod" => $regperiod, "yearsLanguage" => $regperiod == 1 ? Lang::trans("orderForm.year") : Lang::trans("orderForm.years"), "shortYearsLanguage" => $regperiod == 1 ? Lang::trans("orderForm.shortPerYear", [":years" => $regperiod]) : Lang::trans("orderForm.shortPerYears", [":years" => $regperiod]), "price" => $pricing_text, "totaltoday" => new WHMCS\View\Formatter\Price($domainprice, $currency), "renewprice" => $renewPrice, "prefixedRenewPrice" => $renewPrice->toPrefixed(), "renewalPeriod" => $renewalPeriod, "renewalPeriodYearsLang" => $renewalPeriod == 1 ? Lang::trans("orderForm.year") : Lang::trans("orderForm.years"), "shortRenewalYearsLanguage" => $renewalPeriod == 1 ? Lang::trans("orderForm.shortPerYear", [":years" => $renewalPeriod]) : Lang::trans("orderForm.shortPerYears", [":years" => $renewalPeriod]), "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "eppvalue" => $domain["eppcode"], "premium" => $domain["isPremium"], "pricing" => !is_null($domainPriceOverride) ? [1 => $pricing_text] : $pricing, "taxed" => (int) WHMCS\Config\Setting::getValue("TaxDomains")];
            if (!$domain_renew_price_db) {
                unset($tempdomains[$key]["renewprice"]);
            }
            $totaldomainprice += $domain_price_db;
        }
    }
    $cartdata["domains"] = $tempdomains;
    $cart_total += $totaldomainprice;
    if (WHMCS\Config\Setting::getValue("TaxDomains")) {
        $cart_tax[] = $totaldomainprice;
    }
    $orderUpgradeIds = [];
    $cartdata["upgrades"] = [];
    $showUpgradeQtyOptions = false;
    $cartUpgrades = $orderForm->getCartDataByKey("upgrades");
    if (is_array($cartUpgrades)) {
        foreach ($cartUpgrades as $key => $cartUpgrade) {
            $entityType = $cartUpgrade["upgrade_entity_type"];
            $entityId = $cartUpgrade["upgrade_entity_id"];
            $targetEntityId = $cartUpgrade["target_entity_id"];
            $upgradeCycle = $cartUpgrade["billing_cycle"];
            $quantity = $cartUpgrade["quantity"];
            $minimumQuantity = $cartUpgrade["minimumQuantity"];
            try {
                if ($entityType == "service") {
                    $upgradeEntity = WHMCS\Service\Service::findOrFail($entityId);
                    $upgradeTarget = WHMCS\Product\Product::findOrFail($targetEntityId);
                } else {
                    if ($entityType == "addon") {
                        $upgradeEntity = WHMCS\Service\Addon::findOrFail($entityId);
                        $upgradeTarget = WHMCS\Product\Addon::findOrFail($targetEntityId);
                    }
                }
                if ($upgradeEntity->clientId == $client->id) {
                    if ($upgradeTarget->allowMultipleQuantities === WHMCS\Cart\Cart::QUANTITY_SCALING) {
                        $showUpgradeQtyOptions = true;
                        if (App::isInRequest("upgradeqty")) {
                            $quantity = (int) App::getFromRequest("upgradeqty", $key);
                            $_SESSION["cart"]["upgrades"][$key]["quantity"] = $quantity;
                        }
                    }
                    $upgrade = (new WHMCS\Service\Upgrade\Calculator())->setUpgradeTargets($upgradeEntity, $upgradeTarget, $upgradeCycle, $quantity, $minimumQuantity)->calculate();
                    $cartdata["upgrades"][] = $upgrade;
                    $cart_total += $upgrade->upgradeAmount->toNumeric();
                    if ($upgrade->applyTax) {
                        $cart_tax[] = $upgrade->upgradeAmount->toNumeric();
                    }
                    if ($checkout) {
                        $upgrade->userId = $client->id;
                        $upgrade->orderId = $orderid;
                        $upgrade->upgradeAmount = $upgrade->upgradeAmount->toNumeric();
                        $upgrade->creditAmount = $upgrade->creditAmount->toNumeric();
                        $upgrade->newRecurringAmount = $upgrade->newRecurringAmount->toNumeric();
                        $upgrade->save();
                        $invoiceDescription = Lang::trans("upgrade") . ": ";
                        if ($upgrade->type == "service") {
                            $originalQty = "";
                            $newQty = "";
                            if ($upgrade->allowMultipleQuantities) {
                                $originalQty = $upgrade->service->qty;
                                $newQty = $upgrade->qty;
                                if (1 < $originalQty) {
                                    $originalQty .= " x ";
                                }
                                if (1 < $newQty) {
                                    $newQty .= " x ";
                                }
                            }
                            $invoiceDescription .= $upgrade->originalProduct->productGroup->name . " - " . $originalQty . $upgrade->originalProduct->name . " => " . $newQty . $upgrade->newProduct->name;
                            if ($upgrade->service->domain) {
                                $invoiceDescription .= "\n" . $upgrade->service->domain;
                            }
                        } else {
                            if ($upgrade->type == "addon") {
                                $originalQty = "";
                                $newQty = "";
                                if ($upgrade->allowMultipleQuantities) {
                                    $originalQty = $upgrade->addon->qty;
                                    $newQty = $upgrade->qty;
                                    if (1 < $originalQty) {
                                        $originalQty .= " x ";
                                    }
                                    if (1 < $newQty) {
                                        $newQty .= " x ";
                                    }
                                }
                                $invoiceDescription .= $originalQty . $upgrade->originalAddon->name . " => " . $newQty . $upgrade->newAddon->name;
                            }
                        }
                        $invoiceDescription .= "\nNew Recurring Amount: " . formatCurrency($upgrade->newRecurringAmount);
                        if (0 < $upgrade->totalDaysInCycle) {
                            $invoiceDescription .= "\nCredit Amount: " . formatCurrency($upgrade->creditAmount) . "\n" . Lang::trans("upgradeCreditDescription", [":daysRemaining" => $upgrade->daysRemaining, ":totalDays" => $upgrade->totalDaysInCycle]);
                        }
                        insert_query("tblinvoiceitems", ["userid" => $client->id, "type" => "Upgrade", "relid" => $upgrade->id, "description" => $invoiceDescription, "amount" => $upgrade->upgradeAmount, "taxed" => $upgrade->applyTax, "duedate" => "now()", "paymentmethod" => $paymentmethod]);
                        $orderUpgradeIds[] = $upgrade->id;
                    }
                }
            } catch (Exception $e) {
            }
        }
    }
    $orderrenewals = [];
    $cartdata["renewals"] = [];
    $cartRenewals = $orderForm->getCartDataByKey("renewals");
    if (is_array($cartRenewals)) {
        $result = select_query("tblpricing", "", ["type" => "domainaddons", "currency" => $currency["id"], "relid" => 0]);
        $data = mysql_fetch_array($result);
        $domaindnsmanagementprice = $data["msetupfee"];
        $domainemailforwardingprice = $data["qsetupfee"];
        $domainidprotectionprice = $data["ssetupfee"];
        foreach ($cartRenewals as $domainid => $regperiod) {
            try {
                $domain = WHMCS\Domain\Domain::findOrFail($domainid);
                $domainid = $domain->id;
                if ($client->id == $domain->clientId) {
                    $domainname = $domain->domain;
                    $expirydate = $domain->expiryDate;
                    if ($domain->getRawAttribute("expirydate") == "0000-00-00") {
                        $expirydate = $domain->nextDueDate;
                    }
                    $dnsmanagement = $domain->hasDnsManagement;
                    $emailforwarding = $domain->hasEmailForwarding;
                    $idprotection = $domain->hasIdProtection;
                    $tld = "." . $domain->tld;
                    $isPremium = $domain->isPremium;
                    $temppricelist = getTLDPriceList($tld, "", true);
                    if (!isset($temppricelist[$regperiod]["renew"])) {
                        $errMsg = "Invalid TLD/Registration Period Supplied for Domain Renewal";
                        if ($whmcs->isApiRequest()) {
                            $apiresults = ["result" => "error", "message" => $errMsg];
                            return $apiresults;
                        }
                        throw new WHMCS\Exception\Fatal($errMsg);
                    }
                    $renewprice = $temppricelist[$regperiod]["renew"];
                    if ($isPremium) {
                        $extraDetails = WHMCS\Domain\Extra::whereDomainId($domainid)->whereName("registrarRenewalCostPrice")->first();
                        if ($extraDetails) {
                            $regperiod = 1;
                            $markupRenewalPrice = $extraDetails->value;
                            $domainRecurringPrice = (int) format_as_currency($domain->recurringAmount);
                            $markupPercentage = WHMCS\Domains\Pricing\Premium::markupForCost($markupRenewalPrice);
                            $markupRenewalPrice = (int) format_as_currency($markupRenewalPrice * (1 + $markupPercentage / 100));
                            if ($domainRecurringPrice == $markupRenewalPrice) {
                                $renewprice = $domainRecurringPrice;
                            } else {
                                if ($markupRenewalPrice <= $domainRecurringPrice) {
                                    $renewprice = $domainRecurringPrice;
                                } else {
                                    if ($domainRecurringPrice <= $markupRenewalPrice) {
                                        $renewprice = $markupRenewalPrice;
                                    } else {
                                        $renewprice = $markupRenewalPrice;
                                    }
                                }
                            }
                        }
                    }
                    $renewalGracePeriod = $domain->gracePeriod;
                    $gracePeriodFee = $domain->gracePeriodFee;
                    $redemptionGracePeriod = $domain->redemptionGracePeriod;
                    $redemptionGracePeriodFee = $domain->redemptionGracePeriodFee;
                    if (0 < $gracePeriodFee) {
                        $gracePeriodFee = convertCurrency($gracePeriodFee, 1, $currency["id"]);
                    }
                    if (0 < $redemptionGracePeriodFee) {
                        $redemptionGracePeriodFee = convertCurrency($redemptionGracePeriodFee, 1, $currency["id"]);
                    }
                    if (!$renewalGracePeriod || $renewalGracePeriod < 0 || $gracePeriodFee < 0) {
                        $renewalGracePeriod = 0;
                        $gracePeriodFee = 0;
                    }
                    if (!$redemptionGracePeriod || $redemptionGracePeriod < 0 || $redemptionGracePeriodFee < 0) {
                        $redemptionGracePeriod = 0;
                        $redemptionGracePeriodFee = 0;
                    }
                    $today = WHMCS\Carbon::today();
                    $todayExpiryDifference = $today->diff($expirydate);
                    $daysUntilExpiry = ($todayExpiryDifference->invert == 1 ? -1 : 1) * $todayExpiryDifference->days;
                    $inGracePeriod = $inRedemptionGracePeriod = false;
                    if ($daysUntilExpiry < 0) {
                        if ($renewalGracePeriod && 0 - $renewalGracePeriod <= $daysUntilExpiry) {
                            $inGracePeriod = true;
                        } else {
                            if ($redemptionGracePeriod && 0 - ($renewalGracePeriod + $redemptionGracePeriod) <= $daysUntilExpiry) {
                                $inRedemptionGracePeriod = true;
                            }
                        }
                        if (($inGracePeriod || $inRedemptionGracePeriod) && !$isPremium) {
                            $renewalOptions = reset($temppricelist);
                            $regperiod = reset(array_keys($temppricelist));
                            $renewprice = $renewalOptions["renew"];
                        }
                    }
                    if ($dnsmanagement) {
                        $renewprice += $domaindnsmanagementprice * $regperiod;
                    }
                    if ($emailforwarding) {
                        $renewprice += $domainemailforwardingprice * $regperiod;
                    }
                    if ($idprotection) {
                        $renewprice += $domainidprotectionprice * $regperiod;
                    }
                    if (WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxInclusiveDeduct")) {
                        $renewprice = round($renewprice / $excltaxrate, 2);
                    }
                    $domain_renew_price_db = $renewprice;
                    $adjustRecurringAmount = true;
                    if ($promotioncode) {
                        $onetimediscount = $recurringdiscount = $promoid = 0;
                        $promocalc = CalcPromoDiscount("D" . $tld, $regperiod . "Years", $domain_renew_price_db, $domain_renew_price_db);
                        if ($promocalc) {
                            $onetimediscount = $promocalc["onetimediscount"];
                            $recurringdiscount = $promocalc["recurringdiscount"];
                            if (!empty($recurringdiscount)) {
                                $domain_renew_price_db -= $recurringdiscount;
                                $cart_discount += $recurringdiscount;
                            } else {
                                $domain_renew_price_db -= $onetimediscount;
                                $cart_discount += $onetimediscount;
                                $adjustRecurringAmount = false;
                            }
                        }
                    }
                    $cart_total += $domain_renew_price_db;
                    if (WHMCS\Config\Setting::getValue("TaxDomains")) {
                        $cart_tax[] = $domain_renew_price_db;
                    }
                    if ($checkout) {
                        $domain_renew_price_db = format_as_currency($domain_renew_price_db);
                        $orderrenewalids[] = $domainid;
                        $orderrenewals[] = $domainid . "=" . $regperiod;
                        $orderEmailItems .= $_LANG["domainrenewal"] . ": " . $domainname . " - " . $regperiod . " " . $_LANG["orderyears"] . "<br>\n";
                        $domaindesc = $_LANG["domainrenewal"] . " - " . $domainname . " - " . $regperiod . " " . $_LANG["orderyears"] . " (" . fromMySQLDate($expirydate) . " - " . fromMySQLDate(getInvoicePayUntilDate($expirydate, $regperiod)) . ")";
                        if ($dnsmanagement) {
                            $orderEmailItems .= " + " . $_LANG["domaindnsmanagement"] . "<br>\n";
                            $domaindesc .= "\n + " . $_LANG["domaindnsmanagement"];
                        }
                        if ($emailforwarding) {
                            $orderEmailItems .= " + " . $_LANG["domainemailforwarding"] . "<br>\n";
                            $domaindesc .= "\n + " . $_LANG["domainemailforwarding"];
                        }
                        if ($idprotection) {
                            $orderEmailItems .= " + " . $_LANG["domainidprotection"] . "<br>\n";
                            $domaindesc .= "\n + " . $_LANG["domainidprotection"];
                        }
                        $orderEmailItems .= "<br>\n";
                        $emailItem = ["service" => "", "domain" => $domainname, "type" => "Renewal", "registrationPeriod" => $regperiod, "dnsManagement" => (int) $dnsmanagement, "emailForwarding" => (int) $emailforwarding, "idProtection" => (int) $idprotection];
                        $adminEmailItems[] = $emailItem;
                        $tax = WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxDomains") ? "1" : "0";
                        $domain->registrationPeriod = $regperiod;
                        if ($adjustRecurringAmount === true) {
                            $domain->recurringAmount = $domain_renew_price_db;
                        }
                        $domain->promotionId = $promo_data["id"];
                        insert_query("tblinvoiceitems", ["userid" => $client->id, "type" => "Domain", "relid" => $domainid, "description" => $domaindesc, "amount" => $domain_renew_price_db, "taxed" => $tax, "duedate" => "now()", "paymentmethod" => $paymentmethod]);
                        if ($inGracePeriod || $inRedemptionGracePeriod) {
                            if (0 < $gracePeriodFee) {
                                WHMCS\Database\Capsule::table("tblinvoiceitems")->insert(["userid" => $client->id, "type" => "DomainGraceFee", "relid" => $domainid, "description" => Lang::trans("domainGracePeriodFeeInvoiceItem", [":domainName" => $domainname]), "amount" => $gracePeriodFee, "taxed" => $tax, "duedate" => $today->toDateString(), "paymentmethod" => $paymentmethod]);
                            }
                            if ($domain->status == "Active") {
                                $domain->status = "Grace";
                            }
                        }
                        if ($inRedemptionGracePeriod) {
                            if (0 < $redemptionGracePeriodFee) {
                                WHMCS\Database\Capsule::table("tblinvoiceitems")->insert(["userid" => $client->id, "type" => "DomainRedemptionFee", "relid" => $domainid, "description" => Lang::trans("domainRedemptionPeriodFeeInvoiceItem", [":domainName" => $domainname]), "amount" => $redemptionGracePeriodFee, "taxed" => $tax, "duedate" => $today->toDateString(), "paymentmethod" => $paymentmethod]);
                            }
                            if (in_array($domain->status, ["Active", "Grace"])) {
                                $domain->status = "Redemption";
                            }
                        }
                        $domain->save();
                        $result = select_query("tblinvoiceitems", "tblinvoiceitems.id,tblinvoiceitems.invoiceid", ["type" => "Domain", "relid" => $domainid, "status" => "Unpaid", "tblinvoices.userid" => $client->id], "", "", "", "tblinvoices ON tblinvoices.id=tblinvoiceitems.invoiceid");
                        while ($data = mysql_fetch_array($result)) {
                            $itemid = $data["id"];
                            $invoiceid = $data["invoiceid"];
                            $otherItems = WHMCS\Billing\Invoice\Item::where("invoiceid", $invoiceid)->where("id", "!=", $itemid);
                            $itemCount = $otherItems->count();
                            foreach ($otherItems->get() as $otherItem) {
                                switch ($otherItem->type) {
                                    case "DomainGraceFee":
                                    case "DomainRedemptionFee":
                                    case "PromoDomain":
                                        if ($otherItem->relatedEntityId == $domainid) {
                                            $itemCount--;
                                        }
                                        break;
                                    case "GroupDiscount":
                                    case "LateFee":
                                        $itemCount--;
                                        break;
                                }
                            }
                            if ($itemCount === 0) {
                                WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->update(["status" => WHMCS\Billing\Invoice::STATUS_CANCELLED, "date_cancelled" => WHMCS\Carbon::now()->toDateTimeString()]);
                                logActivity("Cancelled Previous Domain Renewal Invoice - " . "Invoice ID: " . $invoiceid . " - Domain: " . $domainname, $client->id);
                                run_hook("InvoiceCancelled", ["invoiceid" => $invoiceid]);
                            } else {
                                WHMCS\Billing\Invoice\Item::where(function (Illuminate\Database\Eloquent\Builder $query) {
                                    $query->where("invoiceid", $invoiceid)->where("relid", $domainid)->whereIn("type", ["Domain", "DomainGraceFee", "DomainRedemptionFee", "PromoDomain"]);
                                })->orWhere(function (Illuminate\Database\Eloquent\Builder $query) {
                                    $query->where("invoiceid", $invoiceid)->whereIn("type", ["GroupDiscount", "LateFee"]);
                                })->delete();
                                updateInvoiceTotal($invoiceid);
                                logActivity("Removed Previous Domain Renewal Line Item" . " - Invoice ID: " . $invoiceid . " - Domain: " . $domainname, $client->id);
                            }
                        }
                    }
                    $renewalPrice = $renewprice;
                    $hasGracePeriodFee = $hasRedemptionGracePeriodFee = false;
                    if (($inGracePeriod || $inRedemptionGracePeriod) && $gracePeriodFee != "0.00") {
                        $cart_total += $gracePeriodFee;
                        $renewalPrice += $gracePeriodFee;
                        if (WHMCS\Config\Setting::getValue("TaxDomains")) {
                            $cart_tax[] = $gracePeriodFee;
                        }
                        $hasGracePeriodFee = true;
                    }
                    if ($inRedemptionGracePeriod && $redemptionGracePeriodFee != "0.00") {
                        $cart_total += $redemptionGracePeriodFee;
                        $renewalPrice += $redemptionGracePeriodFee;
                        if (WHMCS\Config\Setting::getValue("TaxDomains")) {
                            $cart_tax[] = $redemptionGracePeriodFee;
                        }
                        $hasRedemptionGracePeriodFee = true;
                    }
                    $renewalTax = [];
                    $renewalPriceBeforeTax = $renewalPrice;
                    if (WHMCS\Config\Setting::getValue("TaxEnabled") && WHMCS\Config\Setting::getValue("TaxDomains") && !$clientsdetails["taxexempt"]) {
                        $taxCalculator->setTaxBase($renewalPrice);
                        $total_tax_1 = $taxCalculator->getLevel1TaxTotal();
                        $total_tax_2 = $taxCalculator->getLevel2TaxTotal();
                        if (0 < $total_tax_1) {
                            $renewalTax["tax1"] = new WHMCS\View\Formatter\Price($total_tax_1, $currency);
                        }
                        if (0 < $total_tax_2) {
                            $renewalTax["tax2"] = new WHMCS\View\Formatter\Price($total_tax_2, $currency);
                        }
                        if (WHMCS\Config\Setting::getValue("TaxType") == "Inclusive") {
                            $renewalPriceBeforeTax = $taxCalculator->getTotalBeforeTaxes();
                        }
                    }
                    $cartdata["renewals"][$domainid] = ["domain" => $domainname, "regperiod" => $regperiod, "price" => new WHMCS\View\Formatter\Price($renewalPrice, $currency), "priceBeforeTax" => new WHMCS\View\Formatter\Price($renewalPriceBeforeTax, $currency), "priceWithoutGraceAndRedemption" => new WHMCS\View\Formatter\Price($domain_renew_price_db, $currency), "taxes" => $renewalTax, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "hasGracePeriodFee" => $hasGracePeriodFee, "hasRedemptionGracePeriodFee" => $hasRedemptionGracePeriodFee, "taxed" => 0 < count($renewalTax)];
                }
            } catch (Exception $e) {
            }
        }
    }
    $cart_adjustments = 0;
    $adjustments = run_hook("CartTotalAdjustment", $_SESSION["cart"]);
    foreach ($adjustments as $k => $adjvals) {
        if ($checkout) {
            insert_query("tblinvoiceitems", ["userid" => $client->id, "type" => "", "relid" => "", "description" => $adjvals["description"], "amount" => $adjvals["amount"], "taxed" => $adjvals["taxed"], "duedate" => "now()", "paymentmethod" => $paymentmethod]);
        }
        $adjustments[$k]["amount"] = new WHMCS\View\Formatter\Price($adjvals["amount"], $currency);
        $cart_adjustments += $adjvals["amount"];
        if ($adjvals["taxed"]) {
            $cart_tax[] = $adjvals["amount"];
        }
    }
    $total_tax_1 = $total_tax_2 = 0;
    $cart_subtotal = $cart_total + $cart_discount;
    if (WHMCS\Config\Setting::getValue("TaxEnabled") && !$clientsdetails["taxexempt"]) {
        $originalCartItems = collect(WHMCS\Cart\Cart::getItemsFromCartData($cartdata));
        $hookCartData = [];
        if (HookMgr::getRegistered("CartItemsTax")) {
            foreach ($originalCartItems as $hookItem) {
                $hookCartData[] = clone $hookItem;
            }
        }
        $taxOverride = run_hook("CartItemsTax", ["clientData" => $clientsdetails, "cartData" => $hookCartData]);
        if (isset($taxOverride[0]["cartData"])) {
            foreach ($taxOverride[0]["cartData"] as $item) {
                $originalItem = $originalCartItems->where("uuid", "=", $item->getUuid())->first();
                if ($originalItem) {
                    $itemTotal = $item->getAmount()->toNumeric();
                    $originalTotal = $originalItem->getAmount()->toNumeric();
                    $totalDifference = $itemTotal - $originalTotal;
                    $total_tax_1 += $totalDifference;
                    if ($item->isRecurring()) {
                        $itemTotal = $item->getRecurringAmount()->toNumeric();
                        $originalTotal = $originalItem->getRecurringAmount()->toNumeric();
                        $recurring_cycles_total[$item->getBillingCycle()] += $itemTotal - $originalTotal;
                    }
                }
            }
        } else {
            if (WHMCS\Config\Setting::getValue("TaxPerLineItem")) {
                foreach ($cart_tax as $taxBase) {
                    $taxCalculator->setTaxBase($taxBase);
                    $total_tax_1 += $taxCalculator->getLevel1TaxTotal();
                    $total_tax_2 += $taxCalculator->getLevel2TaxTotal();
                }
            } else {
                $taxCalculator->setTaxBase(array_sum($cart_tax));
                $total_tax_1 = $taxCalculator->getLevel1TaxTotal();
                $total_tax_2 = $taxCalculator->getLevel2TaxTotal();
            }
            if (WHMCS\Config\Setting::getValue("TaxType") == "Inclusive") {
                $cart_total -= $total_tax_1 + $total_tax_2;
            } else {
                foreach ($recurring_tax as $cycle => $taxBases) {
                    if (WHMCS\Config\Setting::getValue("TaxPerLineItem")) {
                        foreach ($taxBases as $taxBase) {
                            $taxCalculator->setTaxBase($taxBase);
                            $recurring_cycles_total[$cycle] += $taxCalculator->getLevel1TaxTotal() + $taxCalculator->getLevel2TaxTotal();
                        }
                    } else {
                        $taxCalculator->setTaxBase(array_sum($taxBases));
                        $recurring_cycles_total[$cycle] += $taxCalculator->getLevel1TaxTotal() + $taxCalculator->getLevel2TaxTotal();
                    }
                }
            }
        }
    }
    $cart_total += $total_tax_1 + $total_tax_2 + $cart_adjustments;
    $cart_subtotal = format_as_currency($cart_subtotal);
    $cart_discount = format_as_currency($cart_discount);
    $cart_adjustments = format_as_currency($cart_adjustments);
    $total_tax_1 = format_as_currency($total_tax_1);
    $total_tax_2 = format_as_currency($total_tax_2);
    $cart_total = format_as_currency($cart_total);
    if ($checkout) {
        $ordernameservers = "";
        $orderEmailItems .= $_LANG["ordertotalduetoday"] . ": " . new WHMCS\View\Formatter\Price($cart_total, $currency);
        $totalDueToday = new WHMCS\View\Formatter\Price($cart_total, $currency);
        if ($promotioncode && $promo_data["promoapplied"]) {
            update_query("tblpromotions", ["uses" => "+1"], ["code" => $promotioncode]);
            $promo_recurring = $promo_data["recurring"] ? "Recurring" : "One Time";
            update_query("tblorders", ["promocode" => $promo_data["code"], "promotype" => $promo_recurring . " " . $promo_data["type"], "promovalue" => $promo_data["value"]], ["id" => $orderid]);
        }
        if (!empty($_SESSION["cart"]["ns1"]) && !empty($_SESSION["cart"]["ns2"])) {
            $ordernameservers = $_SESSION["cart"]["ns1"] . "," . $_SESSION["cart"]["ns2"];
            if (!empty($_SESSION["cart"]["ns3"])) {
                $ordernameservers .= "," . $_SESSION["cart"]["ns3"];
            }
            if (!empty($_SESSION["cart"]["ns4"])) {
                $ordernameservers .= "," . $_SESSION["cart"]["ns4"];
            }
            if (!empty($_SESSION["cart"]["ns5"])) {
                $ordernameservers .= "," . $_SESSION["cart"]["ns5"];
            }
        }
        $domaineppcodes = count($domaineppcodes) ? safe_serialize($domaineppcodes) : "";
        $orderdata = [];
        if (isset($_SESSION["cart"]["bundle"]) && is_array($_SESSION["cart"]["bundle"])) {
            foreach ($_SESSION["cart"]["bundle"] as $bvals) {
                $orderdata["bundleids"][] = $bvals["bid"];
            }
        }
        if (!empty($cartdata["upgrades"]) && is_array($cartdata["upgrades"])) {
            foreach ($cartdata["upgrades"] as $orderUpgrade) {
                $orderdata["upgrades"][$orderUpgrade->id] = $orderUpgrade->qty;
            }
        }
        $order->amount = $cart_total;
        $order->nameservers = $ordernameservers;
        $order->transferSecret = $domaineppcodes;
        $order->renewals = implode(",", $orderrenewals);
        $order->orderData = json_encode($orderdata);
        $order->save();
        $invoiceid = 0;
        if (empty($_SESSION["cart"]["geninvoicedisabled"])) {
            if (!$client->id) {
                $errMsg = "An error occurred";
                if ($whmcs->isApiRequest()) {
                    $apiresults = ["result" => "error", "message" => $errMsg];
                    return $apiresults;
                }
                throw new WHMCS\Exception\Fatal($errMsg);
            }
            $invoiceid = createInvoices($client->id, true, "", ["products" => $orderproductids, "addons" => $orderaddonids, "domains" => $orderdomainids]);
            if (WHMCS\Config\Setting::getValue("OrderDaysGrace")) {
                $new_time = mktime(0, 0, 0, date("m"), date("d") + WHMCS\Config\Setting::getValue("OrderDaysGrace"), date("Y"));
                $duedate = date("Y-m-d", $new_time);
                update_query("tblinvoices", ["duedate" => $duedate], ["id" => $invoiceid]);
            }
            if (!WHMCS\Config\Setting::getValue("NoInvoiceEmailOnOrder") && $invoiceid) {
                $invoiceArr = ["source" => "autogen", "user" => WHMCS\Session::get("adminid") ? WHMCS\Session::get("adminid") : "system", "invoiceid" => $invoiceid];
                run_hook("InvoiceCreationPreEmail", $invoiceArr);
                sendMessage("Invoice Created", $invoiceid);
            }
        }
        if ($invoiceid) {
            $order->invoiceId = $invoiceid;
            $order->save();
            $result = select_query("tblinvoices", "status", ["id" => $invoiceid]);
            $data = mysql_fetch_array($result);
            $status = $data["status"];
            if ($status == "Paid" && $orderid) {
                run_hook("OrderPaid", ["orderId" => $orderid, "userId" => $client->id, "invoiceId" => $invoiceid]);
            }
        }
        if (empty($_SESSION["adminid"])) {
            if (isset($_COOKIE["WHMCSAffiliateID"])) {
                $result = select_query("tblaffiliates", "clientid", ["id" => (int) $_COOKIE["WHMCSAffiliateID"]]);
                $data = mysql_fetch_array($result);
                $clientid = $data["clientid"];
                if ($clientid && $client->id != $clientid) {
                    foreach ($orderproductids as $orderproductid) {
                        insert_query("tblaffiliatesaccounts", ["affiliateid" => (int) $_COOKIE["WHMCSAffiliateID"], "relid" => $orderproductid]);
                    }
                }
            }
            if (isset($_COOKIE["WHMCSLinkID"])) {
                update_query("tbllinks", ["conversions" => "+1"], ["id" => $_COOKIE["WHMCSLinkID"]]);
            }
        }
        $result = select_query("tblclients", "firstname, lastname, companyname, email, address1, address2, city, state, postcode, country, phonenumber, ip, host", ["id" => $client->id]);
        $data = mysql_fetch_array($result);
        list($firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $ip, $host) = $data;
        $customfields = getCustomFields("client", "", $client->id, "", true);
        $clientcustomfields = "";
        foreach ($customfields as $customfield) {
            $clientcustomfields .= $customfield["name"] . ": " . $customfield["value"] . "<br />\n";
        }
        $nicegatewayname = WHMCS\Module\GatewaySetting::getFriendlyNameFor($paymentmethod);
        $invoiceModel = WHMCS\Billing\Invoice::find($invoiceid);
        $customInvoiceNumber = $invoiceModel ? $invoiceModel->invoiceNumber : NULL;
        sendAdminMessage("New Order Notification", ["order_id" => $orderid, "order_number" => $order_number, "order_date" => fromMySQLDate(date("Y-m-d H:i:s"), true), "invoice_id" => $invoiceid, "custom_invoice_number" => $customInvoiceNumber, "order_payment_method" => $nicegatewayname, "order_total" => new WHMCS\View\Formatter\Price($cart_total, $currency), "client_id" => $client->id, "client_first_name" => $firstname, "client_last_name" => $lastname, "client_email" => $email, "client_company_name" => $companyname, "client_address1" => $address1, "client_address2" => $address2, "client_city" => $city, "client_state" => $state, "client_postcode" => $postcode, "client_country" => $country, "client_phonenumber" => $phonenumber, "client_customfields" => $clientcustomfields, "order_items" => $orderEmailItems, "order_items_array" => $adminEmailItems, "order_notes" => nl2br($ordernotes), "client_ip" => $ip, "client_hostname" => $host, "total_due_today" => $totalDueToday], "account");
        if (empty($_SESSION["cart"]["orderconfdisabled"])) {
            sendMessage("Order Confirmation", $client->id, ["order_id" => $orderid, "order_number" => $order_number, "order_details" => $orderEmailItems]);
        }
        $_SESSION["cart"] = [];
        $_SESSION["orderdetails"] = ["OrderID" => $orderid, "OrderNumber" => $order_number, "ServiceIDs" => $orderproductids, "DomainIDs" => $orderdomainids, "AddonIDs" => $orderaddonids, "UpgradeIDs" => $orderUpgradeIds, "RenewalIDs" => $orderrenewalids, "PaymentMethod" => $paymentmethod, "InvoiceID" => $invoiceid, "TotalDue" => $cart_total, "Products" => $orderproductids, "Domains" => $orderdomainids, "Addons" => $orderaddonids, "Renewals" => $orderrenewalids];
        run_hook("AfterShoppingCartCheckout", $_SESSION["orderdetails"]);
    }
    $total_recurringmonthly = $recurring_cycles_total["monthly"] <= 0 ? "" : new WHMCS\View\Formatter\Price($recurring_cycles_total["monthly"], $currency);
    $total_recurringquarterly = $recurring_cycles_total["quarterly"] <= 0 ? "" : new WHMCS\View\Formatter\Price($recurring_cycles_total["quarterly"], $currency);
    $total_recurringsemiannually = $recurring_cycles_total["semiannually"] <= 0 ? "" : new WHMCS\View\Formatter\Price($recurring_cycles_total["semiannually"], $currency);
    $total_recurringannually = $recurring_cycles_total["annually"] <= 0 ? "" : new WHMCS\View\Formatter\Price($recurring_cycles_total["annually"], $currency);
    $total_recurringbiennially = $recurring_cycles_total["biennially"] <= 0 ? "" : new WHMCS\View\Formatter\Price($recurring_cycles_total["biennially"], $currency);
    $total_recurringtriennially = $recurring_cycles_total["triennially"] <= 0 ? "" : new WHMCS\View\Formatter\Price($recurring_cycles_total["triennially"], $currency);
    $cartdata["bundlewarnings"] = $bundlewarnings;
    $cartdata["rawdiscount"] = $cart_discount;
    $cartdata["subtotal"] = new WHMCS\View\Formatter\Price($cart_subtotal, $currency);
    $cartdata["discount"] = new WHMCS\View\Formatter\Price($cart_discount, $currency);
    if ($promo_data && is_array($promo_data)) {
        $promo_data["type"] ? exit : NULL;
    } else {
        $promoType = NULL;
        $promoValue = 0;
        $promoRecurring = false;
    }
    $cartdata["promotype"] = $promoType;
    $cartdata["promovalue"] = $promoValue;
    $cartdata["promorecurring"] = $promoRecurring ? $_LANG["recurring"] : $_LANG["orderpaymenttermonetime"];
    $cartdata["taxrate"] = $rawtaxrate;
    $cartdata["taxrate2"] = $rawtaxrate2;
    $cartdata["taxname"] = $taxname;
    $cartdata["taxname2"] = $taxname2;
    $cartdata["taxtotal"] = new WHMCS\View\Formatter\Price($total_tax_1, $currency);
    $cartdata["taxtotal2"] = new WHMCS\View\Formatter\Price($total_tax_2, $currency);
    $cartdata["adjustments"] = $adjustments;
    $cartdata["adjustmentstotal"] = new WHMCS\View\Formatter\Price($cart_adjustments, $currency);
    $cartdata["rawtotal"] = $cart_total;
    $cartdata["total"] = new WHMCS\View\Formatter\Price($cart_total, $currency);
    $cartdata["totalrecurringmonthly"] = $total_recurringmonthly;
    $cartdata["totalrecurringquarterly"] = $total_recurringquarterly;
    $cartdata["totalrecurringsemiannually"] = $total_recurringsemiannually;
    $cartdata["totalrecurringannually"] = $total_recurringannually;
    $cartdata["totalrecurringbiennially"] = $total_recurringbiennially;
    $cartdata["totalrecurringtriennially"] = $total_recurringtriennially;
    $cartdata["showUpgradeQtyOptions"] = $showUpgradeQtyOptions;
    run_hook("AfterCalculateCartTotals", $cartdata);
    return $cartdata;
}
function SetPromoCode($promotioncode)
{
    global $_LANG;
    $_SESSION["cart"]["promo"] = "";
    $result = select_query("tblpromotions", "", ["code" => $promotioncode]);
    $data = mysql_fetch_array($result);
    $id = $data["id"];
    $maxuses = $data["maxuses"];
    $uses = $data["uses"];
    $startdate = $data["startdate"];
    $expiredate = $data["expirationdate"];
    $newsignups = $data["newsignups"];
    $existingclient = $data["existingclient"];
    $onceperclient = $data["onceperclient"];
    if (!$id) {
        $promoerrormessage = $_LANG["ordercodenotfound"];
        return $promoerrormessage;
    }
    if ($startdate != "0000-00-00") {
        $startdate = str_replace("-", "", $startdate);
        if (date("Ymd") < $startdate) {
            $promoerrormessage = $_LANG["orderpromoprestart"];
            return $promoerrormessage;
        }
    }
    if ($expiredate != "0000-00-00") {
        $expiredate = str_replace("-", "", $expiredate);
        if ($expiredate < date("Ymd")) {
            $promoerrormessage = $_LANG["orderpromoexpired"];
            return $promoerrormessage;
        }
    }
    if (0 < $maxuses && $maxuses <= $uses) {
        $promoerrormessage = $_LANG["orderpromomaxusesreached"];
        return $promoerrormessage;
    }
    if ($newsignups && Auth::client()) {
        $result = select_query("tblorders", "COUNT(*)", ["userid" => Auth::client()->id]);
        $data = mysql_fetch_array($result);
        $previousorders = $data[0];
        if (0 < $previousorders) {
            $promoerrormessage = $_LANG["promonewsignupsonly"];
            return $promoerrormessage;
        }
    }
    if ($existingclient) {
        if (Auth::client()) {
            $result = select_query("tblorders", "count(*)", ["status" => "Active", "userid" => Auth::client()->id]);
            $orderCount = mysql_fetch_array($result);
            if ($orderCount[0] == 0) {
                $promoerrormessage = $_LANG["promoexistingclient"];
                return $promoerrormessage;
            }
        } else {
            $promoerrormessage = $_LANG["promoexistingclient"];
            return $promoerrormessage;
        }
    }
    if ($onceperclient && Auth::client()) {
        $result = select_query("tblorders", "count(*)", "promocode='" . db_escape_string($promotioncode) . "' AND userid=" . (int) Auth::client()->id . " AND status IN ('Pending','Active')");
        $orderCount = mysql_fetch_array($result);
        if (0 < $orderCount[0]) {
            $promoerrormessage = $_LANG["promoonceperclient"];
            return $promoerrormessage;
        }
    }
    $_SESSION["cart"]["promo"] = $promotioncode;
}
function CalcPromoDiscount($pid, $cycle, $fpamount, $recamount, $setupfee = 0, array $qtyType = WHMCS\Cart\Cart::QUANTITY_NONE)
{
    global $promo_data;
    global $currency;
    $id = $promo_data["id"];
    $promotionCode = $promo_data["code"];
    if (!$id) {
        return false;
    }
    $anyPromotionPermission = false;
    if (WHMCS\Session::get("adminid") && !defined("CLIENTAREA")) {
        $anyPromotionPermission = checkPermission("Use Any Promotion Code on Order", true);
    }
    if (!$anyPromotionPermission) {
        $newSignups = $promo_data["newsignups"];
        if ($newSignups && Auth::client()) {
            $previousOrders = get_query_val("tblorders", "COUNT(*)", ["userid" => Auth::client()->id]);
            if (2 <= $previousOrders) {
                return false;
            }
        }
        $existingClient = $promo_data["existingclient"];
        $oncePerClient = $promo_data["onceperclient"];
        if ($existingClient && Auth::client()) {
            $orderCount = get_query_val("tblorders", "count(*)", ["status" => "Active", "userid" => Auth::client()->id]);
            if ($orderCount < 1) {
                return false;
            }
        }
        if ($oncePerClient && Auth::client()) {
            $orderCount = get_query_val("tblorders", "count(*)", ["promocode" => $promotionCode, "userid" => Auth::client()->id, "status" => ["sqltype" => "IN", "values" => ["Pending", "Active"]]]);
            if (0 < $orderCount) {
                return false;
            }
        }
        $promo_data["applyonce"] ? exit : NULL;
    }
    $type = $promo_data["type"];
    $value = $promo_data["value"];
    $onetimediscount = 0;
    if ($type == "Percentage") {
        $onetimediscount = $fpamount * $value / 100;
    } else {
        if ($type == "Fixed Amount") {
            if ($currency["id"] != 1) {
                $promo_data["value"] = $value = convertCurrency($value, 1, $currency["id"]);
            }
            if ($fpamount < $value) {
                $onetimediscount = $fpamount;
            } else {
                $onetimediscount = $value;
            }
        } else {
            if ($type == "Price Override") {
                if ($currency["id"] != 1) {
                    $promo_data["value"] = convertCurrency($promo_data["value"], 1, $currency["id"]);
                }
                if (!isset($promo_data["priceoverride"])) {
                    $promo_data["priceoverride"] = $promo_data["value"];
                }
                $onetimediscount = $fpamount - $promo_data["priceoverride"];
            } else {
                if ($type == "Free Setup") {
                    $onetimediscount = $setupfee;
                    $promo_data["value"] += $setupfee;
                }
            }
        }
    }
    $recurringdiscount = 0;
    $recurring = $promo_data["recurring"];
    if ($recurring) {
        if ($type == "Percentage") {
            $recurringdiscount = $recamount * $value / 100;
        } else {
            if ($type == "Fixed Amount") {
                if ($recamount < $value) {
                    $recurringdiscount = $recamount;
                } else {
                    $recurringdiscount = $value;
                }
            } else {
                if ($type == "Price Override") {
                    $recurringdiscount = $recamount - $promo_data["priceoverride"];
                }
            }
        }
    }
    $onetimediscount = round($onetimediscount, 2);
    $recurringdiscount = round($recurringdiscount, 2);
    $promo_data["promoapplied"] = true;
    return ["onetimediscount" => $onetimediscount, "recurringdiscount" => $recurringdiscount, "applyonce" => $applyOnce];
}
function acceptOrder($orderid, $vars = [])
{
    $whmcs = WHMCS\Application::getInstance();
    if (!$orderid) {
        return false;
    }
    if (!is_array($vars)) {
        $vars = [];
    }
    $errors = [];
    run_hook("AcceptOrder", ["orderid" => $orderid]);
    $services = WHMCS\Service\Service::with("product")->where("orderid", $orderid)->where("domainstatus", WHMCS\Utility\Status::PENDING);
    foreach ($services->get() as $service) {
        $serviceId = $service->id;
        $userId = $service->userId;
        if ($vars["products"][$serviceId]["server"]) {
            $service->serverId = $vars["products"][$serviceId]["server"];
        }
        if ($vars["products"][$serviceId]["username"]) {
            $service->username = $vars["products"][$serviceId]["username"];
        }
        if ($vars["products"][$serviceId]["password"]) {
            $service->password = encrypt($vars["products"][$serviceId]["password"]);
        }
        if ($vars["api"]["serverid"]) {
            $service->serverId = $vars["api"]["serverid"];
        }
        if ($vars["api"]["username"]) {
            $service->username = $vars["api"]["username"];
        }
        if ($vars["api"]["password"]) {
            $service->password = encrypt($vars["api"]["password"]);
        }
        if ($service->isDirty()) {
            $service->save();
        }
        $module = $service->product->module;
        $autosetup = $service->product->autoSetup;
        $autosetup = $autosetup ? true : false;
        $sendwelcome = $autosetup ? true : false;
        if (count($vars)) {
            $autosetup = $vars["products"][$serviceId]["runcreate"];
            $sendwelcome = $vars["products"][$serviceId]["sendwelcome"];
            if (isset($vars["api"]["autosetup"])) {
                $autosetup = $vars["api"]["autosetup"];
            }
            if (isset($vars["api"]["sendemail"])) {
                $sendwelcome = $vars["api"]["sendemail"];
            }
        }
        if ($autosetup && $module) {
            logActivity("Running Module Create on Accept Pending Order", $userId);
            $moduleresult = $service->legacyProvision();
            if ($moduleresult == "success") {
                if ($sendwelcome && $module != "marketconnect") {
                    sendMessage("defaultnewacc", $service->id);
                }
            } else {
                $errors[] = $moduleresult;
            }
        } else {
            $service->domainStatus = WHMCS\Utility\Status::ACTIVE;
            $service->save();
            if ($sendwelcome) {
                sendMessage("defaultnewacc", $service->id);
            }
        }
    }
    $addons = WHMCS\Service\Addon::with("productAddon")->where("orderid", "=", $orderid)->where("status", "=", "Pending")->get();
    foreach ($addons as $addon) {
        $addonUniqueId = $addon->id;
        $serviceId = $addon->serviceId;
        $addonId = $addon->addonId;
        $addonBillingCycle = $addon->billingCycle;
        $addonStatus = $addon->status;
        $addonNextDueDate = $addon->nextDueDate;
        $addonName = $addon->name ?: $addon->productAddon->name;
        $autoSetup = $addonId && $addon->productAddon->autoActivate;
        $sendWelcomeEmail = $autoSetup && $addon->productAddon->welcomeEmailTemplateId;
        if (count($vars)) {
            $autoSetup = $vars["addons"][$addonUniqueId]["runcreate"];
            $sendWelcomeEmail = $vars["addons"][$addonUniqueId]["sendwelcome"];
            if (isset($vars["api"]["autosetup"])) {
                $autoSetup = $vars["api"]["autosetup"];
            }
            if (isset($vars["api"]["sendemail"])) {
                $sendWelcomeEmail = $vars["api"]["sendemail"];
            }
        }
        if ($sendWelcomeEmail && !$addon->productAddon->welcomeEmailTemplateId) {
            $sendWelcomeEmail = false;
        }
        if ($autoSetup) {
            $automationResult = "";
            $noModule = true;
            if ($addon->productAddon->module) {
                $automation = WHMCS\Service\Automation\AddonAutomation::factory($addon);
                $action = $addon->provisioningType === WHMCS\Product\Addon::PROVISIONING_TYPE_FEATURE ? "ProvisionAddOnFeature" : "CreateAccount";
                $automationResult = $automation->runAction($action);
                $noModule = false;
                if ($addon->productAddon->module == "marketconnect") {
                    $sendWelcomeEmail = false;
                }
            }
            if ($noModule || $automationResult) {
                if ($sendWelcomeEmail) {
                    sendMessage($addon->productAddon->welcomeEmailTemplate, $serviceId, ["addon_order_id" => $orderid, "addon_id" => $addonUniqueId, "addon_service_id" => $serviceId, "addon_addonid" => $addonId, "addon_billing_cycle" => $addonBillingCycle, "addon_status" => $addonStatus, "addon_nextduedate" => $addonNextDueDate, "addon_name" => $addonName]);
                }
                $addon->status = "Active";
                $addon->save();
                if ($noModule) {
                    run_hook("AddonActivation", ["id" => $addonUniqueId, "userid" => $addon->clientId, "serviceid" => $serviceId, "addonid" => $addonId]);
                }
            }
        } else {
            if ($sendWelcomeEmail) {
                sendMessage($addon->productAddon->welcomeEmailTemplate, $serviceId, ["addon_order_id" => $orderid, "addon_id" => $addonUniqueId, "addon_service_id" => $serviceId, "addon_addonid" => $addonId, "addon_billing_cycle" => $addonBillingCycle, "addon_status" => $addonStatus, "addon_nextduedate" => $addonNextDueDate, "addon_name" => $addonName]);
            }
            $addon->status = "Active";
            $addon->save();
            run_hook("AddonActivated", ["id" => $addonUniqueId, "userid" => $addon->clientId, "serviceid" => $serviceId, "addonid" => $addonId]);
        }
    }
    $result = select_query("tbldomains", "", ["orderid" => $orderid, "status" => "Pending"]);
    while ($data = mysql_fetch_array($result)) {
        $domainid = $data["id"];
        $regtype = $data["type"];
        $domain = $data["domain"];
        $registrar = $data["registrar"];
        $emailmessage = $regtype == "Transfer" ? "Domain Transfer Initiated" : "Domain Registration Confirmation";
        if ($vars["domains"][$domainid]["registrar"]) {
            $registrar = $vars["domains"][$domainid]["registrar"];
        }
        if ($vars["api"]["registrar"]) {
            $registrar = $vars["api"]["registrar"];
        }
        if ($registrar) {
            update_query("tbldomains", ["registrar" => $registrar], ["id" => $domainid]);
        }
        if ($vars["domains"][$domainid]["sendregistrar"]) {
            $sendregistrar = "on";
        }
        if ($vars["domains"][$domainid]["sendemail"]) {
            $sendemail = "on";
        }
        if (isset($vars["api"]["sendregistrar"])) {
            $sendregistrar = $vars["api"]["sendregistrar"];
        }
        if (isset($vars["api"]["sendemail"])) {
            $sendemail = $vars["api"]["sendemail"];
        }
        if ($sendregistrar && $registrar) {
            $params = [];
            $params["domainid"] = $domainid;
            $moduleresult = $regtype == "Transfer" ? RegTransferDomain($params) : RegRegisterDomain($params);
            if (!$moduleresult["error"]) {
                if ($sendemail) {
                    sendMessage($emailmessage, $domainid);
                }
            } else {
                $errors[] = $moduleresult["error"];
            }
        } else {
            update_query("tbldomains", ["status" => "Active"], ["id" => $domainid, "status" => "Pending"]);
            if ($sendemail) {
                sendMessage($emailmessage, $domainid);
            }
        }
    }
    if (is_array($vars["renewals"])) {
        foreach ($vars["renewals"] as $domainid => $options) {
            if ($vars["renewals"][$domainid]["sendregistrar"]) {
                $sendregistrar = "on";
            }
            if ($vars["renewals"][$domainid]["sendemail"]) {
                $sendemail = "on";
            }
            if ($sendregistrar) {
                $params = [];
                $params["domainid"] = $domainid;
                $moduleresult = RegRenewDomain($params);
                if ($moduleresult["error"]) {
                    $errors[] = $moduleresult["error"];
                } else {
                    if ($sendemail) {
                        sendMessage("Domain Renewal Confirmation", $domainid);
                    }
                }
            } else {
                if ($sendemail) {
                    sendMessage("Domain Renewal Confirmation", $domainid);
                }
            }
        }
    }
    $result = select_query("tblorders", "userid,promovalue", ["id" => $orderid]);
    $data = mysql_fetch_array($result);
    $userid = $data["userid"];
    $promovalue = $data["promovalue"];
    if (substr($promovalue, 0, 2) == "DR") {
        if ($vars["domains"][$domainid]["sendregistrar"]) {
            $sendregistrar = "on";
        }
        if (isset($vars["api"]["autosetup"])) {
            $sendregistrar = $vars["api"]["autosetup"];
        }
        if ($sendregistrar) {
            $params = [];
            $params["domainid"] = $domainid;
            $moduleresult = RegRenewDomain($params);
            if ($moduleresult["error"]) {
                $errors[] = $moduleresult["error"];
            } else {
                if ($sendemail) {
                    sendMessage("Domain Renewal Confirmation", $domainid);
                }
            }
        } else {
            if ($sendemail) {
                sendMessage("Domain Renewal Confirmation", $domainid);
            }
        }
    }
    update_query("tblupgrades", ["status" => "Completed"], ["orderid" => $orderid]);
    if (!count($errors)) {
        update_query("tblorders", ["status" => "Active"], ["id" => $orderid]);
        logActivity("Order Accepted - Order ID: " . $orderid, $userid);
    }
    return $errors;
}
function changeOrderStatus($orderid, $status, $cancelSubscription = false)
{
    $whmcs = WHMCS\Application::getInstance();
    if (!$orderid) {
        return false;
    }
    $orderid = (int) $orderid;
    if ($status == "Cancelled") {
        run_hook("CancelOrder", ["orderid" => $orderid]);
    } else {
        if ($status == "Refunded") {
            run_hook("CancelAndRefundOrder", ["orderid" => $orderid]);
            $status = "Cancelled";
        } else {
            if ($status == "Fraud") {
                run_hook("FraudOrder", ["orderid" => $orderid]);
            } else {
                if ($status == "Pending") {
                    run_hook("PendingOrder", ["orderid" => $orderid]);
                }
            }
        }
    }
    $orderStatus = WHMCS\Database\Capsule::table("tblorders")->where("id", $orderid)->value("status");
    update_query("tblorders", ["status" => $status], ["id" => $orderid]);
    if ($status == "Cancelled" || $status == "Fraud") {
        $result = select_query("tblhosting", "tblhosting.id,tblhosting.userid,tblhosting.domainstatus,tblproducts.servertype,tblhosting.packageid,tblhosting.paymentmethod,tblproducts.stockcontrol,tblproducts.qty", ["orderid" => $orderid], "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid");
        while ($data = mysql_fetch_array($result)) {
            $userId = $data["userid"];
            if ($cancelSubscription) {
                try {
                    cancelSubscriptionForService($data["id"], $userId);
                } catch (Exception $e) {
                    WHMCS\Database\Capsule::table("tblorders")->where("id", $orderid)->update(["status" => $orderStatus]);
                    $errMessage = "subcancelfailed";
                    return $errMessage;
                }
            }
            $productid = $data["id"];
            $addons = WHMCS\Service\Addon::where("hostingid", $productid)->where("status", "!=", $status)->with("productAddon")->get();
            $cancelResult = processAddonsCancelOrFraud($addons, $status);
            if (App::isApiRequest() && is_array($cancelResult)) {
                return $cancelResult;
            }
            $prodstatus = $data["domainstatus"];
            $module = $data["servertype"];
            $packageid = $data["packageid"];
            $stockcontrol = $data["stockcontrol"];
            $qty = $data["qty"];
            if ($module && ($prodstatus == "Active" || $prodstatus == "Suspended")) {
                logActivity("Running Module Terminate on Order Cancel", $userId);
                if (!isValidforPath($module)) {
                    $errMsg = "Invalid Server Module Name";
                    if ($whmcs->isApiRequest()) {
                        $apiresults = ["result" => "error", "message" => $errMsg];
                        return $apiresults;
                    }
                    throw new WHMCS\Exception\Fatal($errMsg);
                }
                require_once ROOTDIR . "/modules/servers/" . $module . "/" . $module . ".php";
                $moduleresult = ServerTerminateAccount($productid);
                if ($moduleresult == "success") {
                    update_query("tblhosting", ["domainstatus" => $status], ["id" => $productid]);
                    if ($stockcontrol) {
                        update_query("tblproducts", ["qty" => "+1"], ["id" => $packageid]);
                    }
                }
            } else {
                update_query("tblhosting", ["domainstatus" => $status], ["id" => $productid]);
                if ($stockcontrol) {
                    update_query("tblproducts", ["qty" => "+1"], ["id" => $packageid]);
                }
            }
        }
        $addons = WHMCS\Service\Addon::where("orderid", $orderid)->where("status", "!=", $status)->with("productAddon")->get();
        $cancelResult = processAddonsCancelOrFraud($addons, $status);
        if (App::isApiRequest() && is_array($cancelResult)) {
            return $cancelResult;
        }
    } else {
        update_query("tblhosting", ["domainstatus" => $status], ["orderid" => $orderid]);
        update_query("tblhostingaddons", ["status" => $status], ["orderid" => $orderid]);
    }
    if ($status == "Pending") {
        $result = select_query("tbldomains", "id,type", ["orderid" => $orderid]);
        while ($data = mysql_fetch_assoc($result)) {
            if ($data["type"] == "Transfer") {
                $status = "Pending Transfer";
            } else {
                $status = "Pending";
            }
            update_query("tbldomains", ["status" => $status], ["id" => $data["id"]]);
        }
    } else {
        update_query("tbldomains", ["status" => $status], ["orderid" => $orderid]);
    }
    $result = select_query("tblorders", "userid,invoiceid", ["id" => $orderid]);
    $data = mysql_fetch_array($result);
    $userid = $data["userid"];
    $invoiceid = $data["invoiceid"];
    if ($invoiceid) {
        if ($status == "Pending") {
            WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->where("status", WHMCS\Billing\Invoice::STATUS_CANCELLED)->update(["status" => WHMCS\Billing\Invoice::STATUS_UNPAID, "date_cancelled" => "0000-00-00 00:00:00"]);
        } else {
            $invoice = WHMCS\Billing\Invoice::find($invoiceid);
            if ($invoice) {
                if (!function_exists("refundCreditOnStatusChange")) {
                    require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "invoicefunctions.php";
                }
                if (refundCreditOnStatusChange($invoiceid, $status)) {
                    $invoice->status = WHMCS\Billing\Invoice::STATUS_REFUNDED;
                    $invoice->dateRefunded = WHMCS\Carbon::now();
                } else {
                    if ($invoice->status === WHMCS\Billing\Invoice::STATUS_UNPAID) {
                        $invoice->status = WHMCS\Billing\Invoice::STATUS_CANCELLED;
                        $invoice->dateCancelled = WHMCS\Carbon::now();
                    }
                }
                $invoice->save();
                run_hook("InvoiceCancelled", ["invoiceid" => $invoiceid]);
            }
        }
    }
    logActivity("Order Status set to " . $status . " - Order ID: " . $orderid, $userid);
}
function cancelRefundOrder($orderid)
{
    $orderid = (int) $orderid;
    $result = select_query("tblorders", "invoiceid", ["id" => $orderid]);
    $data = mysql_fetch_array($result);
    $invoiceid = $data["invoiceid"];
    if ($invoiceid) {
        $result = select_query("tblinvoices", "status", ["id" => $invoiceid]);
        $data = mysql_fetch_array($result);
        $invoicestatus = $data["status"];
        if ($invoicestatus == "Paid") {
            $result = select_query("tblaccounts", "id", ["invoiceid" => $invoiceid]);
            $data = mysql_fetch_array($result);
            $transid = $data["id"];
            $gatewayresult = refundInvoicePayment($transid, "", true);
            if ($gatewayresult == "manual") {
                return "manual";
            }
            if ($gatewayresult != "success") {
                return "refundfailed";
            }
            changeorderstatus($orderid, "Refunded");
        } else {
            if ($invoicestatus == "Refunded") {
                return "alreadyrefunded";
            }
            return "notpaid";
        }
    } else {
        return "noinvoice";
    }
}
function deleteOrder($orderid)
{
    if (!$orderid) {
        return false;
    }
    $orderid = (int) $orderid;
    run_hook("DeleteOrder", ["orderid" => $orderid]);
    $result = select_query("tblorders", "userid,invoiceid", ["id" => $orderid]);
    $data = mysql_fetch_array($result);
    if (!canOrderBeDeleted($orderid)) {
        return false;
    }
    $userid = $data["userid"];
    $invoiceid = $data["invoiceid"];
    delete_query("tblhostingconfigoptions", "relid IN (SELECT id FROM tblhosting WHERE orderid=" . $orderid . ")");
    delete_query("tblaffiliatesaccounts", "relid IN (SELECT id FROM tblhosting WHERE orderid=" . $orderid . ")");
    $select = "tblhosting.id AS relid, tblcustomfields.id AS fieldid";
    $where = ["tblhosting.orderid" => $orderid, "tblcustomfields.type" => "product"];
    $join = "tblcustomfields ON tblcustomfields.relid=tblhosting.packageid";
    $result = select_query("tblhosting", $select, $where, "", "", "", $join);
    while ($data = mysql_fetch_array($result)) {
        $hostingid = $data["relid"];
        $customfieldid = $data["fieldid"];
        $deleteWhere = ["relid" => $hostingid, "fieldid" => $customfieldid];
        delete_query("tblcustomfieldsvalues", $deleteWhere);
    }
    delete_query("tblhosting", ["orderid" => $orderid]);
    foreach (WHMCS\Service\Addon::where("orderid", $orderid)->get() as $serviceAddon) {
        $serviceAddon->delete();
    }
    delete_query("tbldomains", ["orderid" => $orderid]);
    delete_query("tblupgrades", ["orderid" => $orderid]);
    delete_query("tblorders", ["id" => $orderid]);
    delete_query("tblinvoices", ["id" => $invoiceid]);
    delete_query("tblinvoiceitems", ["invoiceid" => $invoiceid]);
    logActivity("Deleted Order - Order ID: " . $orderid, $userid);
}
function getAddons($pid, $addons = [])
{
    global $currency;
    $addonsArray = [];
    $billingCycles = ["monthly" => Lang::trans("orderpaymenttermmonthly"), "quarterly" => Lang::trans("orderpaymenttermquarterly"), "semiannually" => Lang::trans("orderpaymenttermsemiannually"), "annually" => Lang::trans("orderpaymenttermannually"), "biennially" => Lang::trans("orderpaymenttermbiennially"), "triennially" => Lang::trans("orderpaymenttermtriennially")];
    $addonIds = array_map(function ($item) {
        if (is_array($item)) {
            return $item["addonid"];
        }
        return $item;
    }, $addons);
    $orderAddons = WHMCS\Product\Addon::availableOnOrderForm($addonIds)->get();
    foreach ($orderAddons as $addon) {
        if (in_array($pid, $addon->packages)) {
            $pricing = WHMCS\Database\Capsule::table("tblpricing")->where("type", "=", "addon")->where("currency", "=", $currency["id"])->where("relid", "=", $addon->id)->first();
            if ($pricing || (new WHMCS\Billing\Cycles())->isFree($addon->billingCycle)) {
                $addonPricingString = "";
                $addonBillingCycles = [];
                switch ($addon->billingCycle) {
                    case "recurring":
                        foreach ($billingCycles as $system => $translated) {
                            $setupFeeField = substr($system, 0, 1) . "setupfee";
                            if ($pricing->{$system} >= 0) {
                                $addonPrice = new WHMCS\View\Formatter\Price($pricing->{$system}, $currency) . " " . $translated;
                                if (0 < $pricing->{$setupFeeField}) {
                                    $addonPrice .= " + " . new WHMCS\View\Formatter\Price($pricing->{$setupFeeField}, $currency) . " " . Lang::trans("ordersetupfee");
                                }
                                if (empty($addonPricingString)) {
                                    $addonPricingString = $addonPrice;
                                }
                                $addonBillingCycles[$system] = ["setup" => 0 < $pricing->{$setupFeeField} ? new WHMCS\View\Formatter\Price($pricing->{$setupFeeField}, $currency) : NULL, "price" => new WHMCS\View\Formatter\Price($pricing->{$system}, $currency)];
                            }
                        }
                        break;
                    case "free":
                    case "Free":
                    case "Free Account":
                        $addonPricingString = Lang::trans("orderfree");
                        $addonBillingCycles["free"] = ["setup" => NULL, "price" => NULL];
                        break;
                    case "onetime":
                    case "One Time":
                    default:
                        $system = str_replace([" ", "-"], "", strtolower($addon->billingCycle));
                        $translated = Lang::trans("orderpaymentterm" . $system);
                        $addonPrice = new WHMCS\View\Formatter\Price($pricing->monthly, $currency) . " " . $translated;
                        if (0 < $pricing->msetupfee) {
                            $addonPrice .= " + " . formatCurrency($pricing->msetupfee) . " " . Lang::trans("ordersetupfee");
                        }
                        if (empty($addonPricingString)) {
                            $addonPricingString = $addonPrice;
                        }
                        $addonBillingCycles[$system] = ["setup" => new WHMCS\View\Formatter\Price($pricing->msetupfee, $currency), "price" => new WHMCS\View\Formatter\Price($pricing->monthly, $currency)];
                        $checkbox = "<input type=\"checkbox\" name=\"addons[" . $addon->id . "]\" id=\"a" . $addon->id . "\"";
                        $status = false;
                        if (in_array($addon->id, $addonIds)) {
                            $checkbox .= " checked=\"checked\"";
                            $status = true;
                        }
                        $checkbox .= " />";
                        $minPrice = 0;
                        $minCycle = "onetime";
                        foreach ($addonBillingCycles as $cycle => $price) {
                            $minPrice = $price;
                            $minCycle = $cycle;
                            if (!(isset($minPrice["price"]) && $minPrice["price"]->toNumeric() < 0)) {
                                $addonsArray[] = ["id" => $addon->id, "checkbox" => $checkbox, "name" => $addon->name, "description" => $addon->description, "pricing" => $addonPricingString, "billingCycles" => $addonBillingCycles, "minPrice" => $minPrice, "minCycle" => $minCycle, "status" => $status, "allowsQuantity" => $addon->allowMultipleQuantities];
                            }
                        }
                }
            }
        }
    }
    return $addonsArray;
}
function getAvailableOrderPaymentGateways($forceAll = false)
{
    $whmcs = App::self();
    $disabledGateways = [];
    $cartSession = WHMCS\Session::get("cart");
    if (isset($cartSession["products"])) {
        foreach ($cartSession["products"] as $values) {
            $groupDisabled = WHMCS\Database\Capsule::table("tblproductgroups")->join("tblproducts", "tblproducts.gid", "=", "tblproductgroups.id")->where("tblproducts.id", "=", $values["pid"])->first(["disabledgateways"]);
            $disabledGateways = array_merge(explode(",", $groupDisabled->disabledgateways), $disabledGateways);
        }
    }
    if (!function_exists("showPaymentGatewaysList")) {
        require ROOTDIR . "/includes/gatewayfunctions.php";
    }
    $_SESSION["uid"] ? exit : 0;
}
function canOrderBeDeleted($orderID, $orderStatus = "")
{
    while (!$orderID) {
        if (!is_array($cancelledStatuses)) {
            $cancelledStatuses = WHMCS\Database\Capsule::table("tblorderstatuses")->where("showcancelled", 1)->pluck("title")->all();
        }
        $orderID = (int) $orderID;
        if (!$orderStatus) {
            try {
                $orderDetails = WHMCS\Database\Capsule::table("tblorders")->find($orderID, ["tblorders.status as orderStatus"]);
                if (!$orderDetails) {
                    throw new WHMCS\Exception\Api\InvalidAction("Order Not Found");
                }
                $orderStatus = $orderDetails->orderStatus;
            } catch (Exception $e) {
                return false;
            }
        }
        if (in_array($orderStatus, $cancelledStatuses) || $orderStatus == "Fraud") {
            return true;
        }
        return false;
    }
    return false;
}
function processAddonsCancelOrFraud(Illuminate\Support\Collection $addonCollection, $status)
{
    foreach ($addonCollection as $addon) {
        $addonId = $addon->id;
        $module = $addon->productAddon ? $addon->productAddon->module : "";
        $addonStatus = $addon->status;
        if ($module && in_array($addonStatus, ["Active", "Suspended"])) {
            logActivity("Running Module Terminate on Order Cancel - Addon ID: " . $addonId, $addon->clientId);
            $server = new WHMCS\Module\Server();
            if (!$server->loadByAddonId($addonId)) {
                $errMsg = "Invalid Server Module Name";
                if (App::isApiRequest()) {
                    $apiresults = ["result" => "error", "message" => $errMsg];
                    return $apiresults;
                }
                throw new WHMCS\Exception\Fatal($errMsg);
            }
            $action = $addon->provisioningType === WHMCS\Product\Addon::PROVISIONING_TYPE_FEATURE ? "DeprovisionAddOnFeature" : "TerminateAccount";
            $moduleResult = $server->call($action);
            if ($moduleResult == "success") {
                $addon->status = $status;
                $addon->save();
            }
        } else {
            $addon->status = $status;
            $addon->save();
        }
    }
    return "";
}

?>