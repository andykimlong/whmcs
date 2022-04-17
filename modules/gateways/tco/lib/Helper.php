<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Module\Gateway\TCO;

class Helper
{
    protected static $languages = ["chinese" => "zh", "danish" => "da", "dutch" => "nl", "french" => "fr", "german" => "gr", "greek" => "el", "italian" => "it", "japanese" => "jp", "norwegian" => "no", "portuguese" => "pt", "slovenian" => "sl", "spanish" => "es_la", "swedish" => "sv", "english" => "en"];
    public static function convertCurrency($amount, \WHMCS\Billing\Currency $currency, \WHMCS\Billing\Invoice $invoice)
    {
        return \WHMCS\Billing\Invoice\Helper::convertCurrency($amount, $currency, $invoice);
    }
    public static function language($language)
    {
        $language = strtolower($language);
        $tcoLanguage = "";
        if (array_key_exists($language, self::$languages)) {
            $tcoLanguage = self::$languages[$language];
        }
        return $tcoLanguage;
    }
    public static function languageInput($language)
    {
        $tcoLanguage = self::language($language);
        if ($tcoLanguage) {
            $tcoLanguage = "<input type=\"hidden\" name=\"lang\" value=\"" . $tcoLanguage . "\">";
        }
        return $tcoLanguage;
    }
}

?>