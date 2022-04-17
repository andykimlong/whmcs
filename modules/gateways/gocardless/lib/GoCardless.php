<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Module\Gateway\GoCardless;

class GoCardless
{
    const SUPPORTED_CURRENCIES = ["AUD", "CAD", "DKK", "EUR", "GBP", "NZD", "SEK", "USD"];
    const SCHEMES = ["AUD" => "becs", "CAD" => "pad", "DKK" => "betalingsservice", "EUR" => "sepa", "GBP" => "bacs", "NZD" => "becs_nz", "SEK" => "autogiro", "USD" => "ach"];
    const SCHEME_NAMES = ["becs" => "BECS", "pad" => "PAD", "betalingsservice" => "Betalingsservice", "sepa" => "SEPA", "bacs" => "BACS", "becs_nz" => "BECS NZ", "autogiro" => "Autogiro", "ach" => "ACH"];
}

?>