<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Module\Addon\ProjectManagement\Models;

class WHMCSInvoice extends \WHMCS\Billing\Invoice
{
    protected $appends = ["balance"];
}

?>