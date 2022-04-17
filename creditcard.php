<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

require "init.php";
$invoiceId = App::getFromRequest("invoiceid");
if (!$invoiceId) {
    App::redirect("clientarea.php");
}
App::redirectToRoutePath("invoice-pay", $invoiceId);

?>