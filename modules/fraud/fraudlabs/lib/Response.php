<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Module\Fraud\FraudLabs;

class Response extends \WHMCS\Module\Fraud\AbstractResponse implements \WHMCS\Module\Fraud\ResponseInterface
{
    protected $failureErrorCodes = [101, 102, 103, 104, 203, 204, 210, 211];
    public function isSuccessful()
    {
        $errorCode = $this->get("fraudlabspro_error_code");
        return $this->httpCode == 200 && (!$errorCode || !in_array($errorCode, $this->failureErrorCodes));
    }
}

?>