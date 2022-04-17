<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $acceptedValues = ["type" => [WHMCS\Product\Product::TYPE_SHARED, WHMCS\Product\Product::TYPE_RESELLER, WHMCS\Product\Product::TYPE_SERVERS, WHMCS\Product\Product::TYPE_OTHER], "payType" => [WHMCS\Product\Product::PAYMENT_FREE, WHMCS\Product\Product::PAYMENT_ONETIME, WHMCS\Product\Product::PAYMENT_RECURRING], "autoSetup" => [WHMCS\Product\Product::AUTO_SETUP_ACCEPT, WHMCS\Product\Product::AUTO_SETUP_DISABLED, WHMCS\Product\Product::AUTO_SETUP_ORDER, WHMCS\Product\Product::AUTO_SETUP_PAYMENT]];
    if (!$name) {
        $apiresults = ["result" => "error", "message" => "You must supply a name for the product"];
        return false;
    }
    if (!$type) {
        $type = "other";
    } else {
        if (!in_array($type, $acceptedValues["type"])) {
            $apiresults = ["result" => "error", "message" => "Invalid product type. Must be one of \"hostingaccount\", \"reselleraccount\", \"server\" or \"other\""];
            return false;
        }
    }
    if ($gid) {
        try {
            $group = WHMCS\Product\Group::findOrFail($gid);
            if ($stockcontrol || $qty) {
                $stockcontrol = "1";
            } else {
                $stockcontrol = "0";
            }
            if (!$paytype) {
                $paytype = WHMCS\Product\Product::PAYMENT_FREE;
            } else {
                if (!in_array($paytype, $acceptedValues["payType"])) {
                    $apiresults = ["result" => "error", "message" => "Invalid pay type. Must be one of \"free\", \"onetime\" or \"recurring\""];
                    return false;
                }
            }
            if (!$welcomeemail) {
                $welcomeemail = 0;
            } else {
                try {
                    $template = WHMCS\Mail\Template::findOrFail($welcomeemail);
                } catch (Exception $e) {
                    $apiresults = ["result" => "error", "message" => "You must supply a valid welcome email ID"];
                    return false;
                }
            }
            if (!$autosetup) {
                $autosetup = "";
            } else {
                if (!in_array($autosetup, $acceptedValues["autoSetup"])) {
                    $apiresults = ["result" => "error", "message" => "Invalid autosetup value. Must be one of \"\", \"on\", \"order\" or \"payment\""];
                    return false;
                }
            }
            if (!$servergroupid) {
                $servergroupid = 0;
            } else {
                $serverIdCheck = WHMCS\Database\Capsule::table("tblservergroups")->where("id", $servergroupid)->count();
                if ($servergroupid < 0 || $serverIdCheck === 0) {
                    $apiresults = ["result" => "error", "message" => "Invalid server group ID"];
                    return false;
                }
            }
            $hidden = (int) (int) $hidden;
            $showdomainoptions = (int) (int) $showdomainoptions;
            $tax = (int) (int) $tax;
            $isFeatured = (int) (int) $isFeatured;
            $proratabilling = (int) (int) $proratabilling;
            $product = new WHMCS\Product\Product();
            $product->type = $type;
            $product->productGroupId = $gid;
            $product->name = $name;
            $product->description = WHMCS\Input\Sanitize::decode($description);
            $product->isHidden = $hidden;
            $product->showDomainOptions = $showdomainoptions;
            $product->welcomeEmailTemplateId = $welcomeemail;
            $product->stockControlEnabled = $stockcontrol;
            $product->quantityInStock = $qty;
            $product->proRataBilling = $proratabilling;
            $product->proRataChargeDayOfCurrentMonth = $proratadate;
            $product->proRataChargeNextMonthAfterDay = $proratachargenextmonth;
            $product->paymentType = $paytype;
            $product->freeSubDomains = explode(",", $subdomain);
            $product->autoSetup = $autosetup;
            $product->module = $module;
            $product->serverGroupId = $servergroupid;
            $product->moduleConfigOption1 = $configoption1;
            $product->moduleConfigOption2 = $configoption2;
            $product->moduleConfigOption3 = $configoption3;
            $product->moduleConfigOption4 = $configoption4;
            $product->moduleConfigOption5 = $configoption5;
            $product->moduleConfigOption6 = $configoption6;
            $product->applyTax = $tax;
            $product->displayOrder = $order;
            $product->isFeatured = $isFeatured;
            $product->save();
            $pid = $product->id;
            if (isset($pricing) && is_array($pricing)) {
                $validCurrencies = WHMCS\Database\Capsule::table("tblcurrencies")->pluck("id")->all();
                foreach ($pricing as $currency => $values) {
                    if (in_array($currency, $validCurrencies)) {
                        $cycleValues = $feeValues = [];
                        foreach ((new WHMCS\Billing\Cycles())->getSystemBillingCycles(true) as $cycle) {
                            if (key_exists($cycle, $values)) {
                                $cycleValues[$cycle] = (int) $values[$cycle];
                            } else {
                                $cycleValues[$cycle] = 0;
                            }
                        }
                        foreach ((new WHMCS\Billing\Pricing())->setupFields() as $fee) {
                            if (key_exists($fee, $values)) {
                                $feeValues[$fee] = (int) $values[$fee];
                            } else {
                                $feeValues[$fee] = 0;
                            }
                        }
                        $data = array_merge(["type" => "product", "currency" => $currency, "relid" => $pid], $feeValues, $cycleValues);
                        WHMCS\Database\Capsule::table("tblpricing")->insert($data);
                    }
                }
            }
            $apiresults = ["result" => "success", "pid" => $pid];
        } catch (Exception $e) {
            $apiresults = ["result" => "error", "message" => "You must supply a valid Product Group ID"];
            return false;
        }
    }
    $apiresults = ["result" => "error", "message" => "You must supply a valid Product Group ID"];
    return false;
}
exit("This file cannot be accessed directly");

?>