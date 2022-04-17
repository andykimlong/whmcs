<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Module\Gateway\Stripe\Widget;

class Stripe extends \WHMCS\Module\AbstractWidget
{
    protected $title = "Stripe Balance";
    protected $description = "An overview of connected Stripe account balance information.";
    protected $weight = 15;
    protected $cache = true;
    protected $cacheExpiry = 60;
    protected $requiredPermission = "View Gateway Balances";
    public function getId()
    {
        return "Stripe";
    }
    public function getData()
    {
        try {
            $gatewayInterface = \WHMCS\Module\Gateway::factory("stripe");
            $balanceCollection = $gatewayInterface->call("account_balance");
            if ($balanceCollection instanceof \WHMCS\Module\Gateway\BalanceCollection) {
                return $balanceCollection->all();
            }
        } catch (\Throwable $t) {
            return [];
        }
    }
    public function generateOutput($generateOutput, $data)
    {
        $output = [];
        foreach ($data as $index => $balanceObject) {
            if (is_array($balanceObject)) {
                $balanceObject = \WHMCS\Module\Gateway\Balance::factoryFromArray($balanceObject);
            }
            $currencyObject = $balanceObject->getCurrencyObject();
            if ($currencyObject) {
                $textColor = $balanceObject->colorCodeAsString();
                $additionalStyle = "";
                if (is_null($textColor)) {
                    $additionalStyle = " style=\"color: " . $balanceObject->getColor() . ";\"";
                }
                $additionalClasses = [];
                if (!in_array($index, [0, 2])) {
                    $additionalClasses[] = "bordered-top";
                }
                if ($balanceObject->getRawLabel() !== "status.pending") {
                    $additionalClasses[] = "bordered-right";
                }
                $additionalClass = implode(" ", $additionalClasses);
                $output[$currencyObject->code] .= "<div class=\"col-sm-6 " . $additionalClass . "\">\n    <div class=\"item\">\n        <div class=\"data " . $textColor . "\"" . $additionalStyle . ">" . $balanceObject->getAmount()->toPrefixed() . "</div>\n        <div class=\"note\">" . $balanceObject->getLabel() . "</div>\n    </div>\n</div>";
            }
        }
        $output = implode("", $output);
        return "<div class=\"row\">" . $output . "</div>";
    }
}

?>