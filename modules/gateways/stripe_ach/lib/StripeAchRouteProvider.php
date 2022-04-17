<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Module\Gateway\StripeAch;

class StripeAchRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    protected function getRoutes()
    {
        return ["/stripe_ach" => [["name" => $this->getDeferredRoutePathNameAttribute() . "exchange", "method" => ["POST"], "path" => "/token/exchange", "authorization" => function () {
            return (new \WHMCS\ClientArea\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken();
        }, "handle" => ["WHMCS\\Module\\Gateway\\StripeAch\\StripeAchController", "exchange"]]]];
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "stripe-ach-";
    }
}

?>