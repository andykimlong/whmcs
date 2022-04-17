<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

if ($errorMessage) {
    echo "    <div class=\"alert alert-danger\">";
    echo $errorMessage;
    echo "</div>\n";
} else {
    echo "    <div class=\"row\">\n        <div class=\"col-sm-4 text-right bottom-margin-5\">\n            <strong>";
    echo AdminLang::trans("transactions.information.gateway");
    echo "</strong>\n        </div>\n        <div class=\"col-sm-8 bottom-margin-5\">\n            ";
    echo $gatewayInterface->getDisplayName();
    echo "        </div>\n        <div class=\"col-sm-4 text-right bottom-margin-5\">\n            <strong>";
    echo AdminLang::trans("fields.clientname");
    echo "</strong>\n        </div>\n        <div class=\"col-sm-8 bottom-margin-5\">\n            <a href=\"";
    echo $transaction->client->getLink();
    echo "\">";
    echo $transaction->client->fullName;
    echo "</a>\n        </div>\n        <div class=\"col-sm-4 text-right bottom-margin-5\">\n            <strong>";
    echo AdminLang::trans("fields.invoicenum");
    echo "</strong>\n        </div>\n        <div class=\"col-sm-8 bottom-margin-5\">\n            <a href=\"invoices.php?action=edit&id=";
    echo $transaction->invoiceId;
    echo "\">\n                ";
    echo $transaction->invoiceId;
    echo "            </a>\n        </div>\n        ";
    foreach ($transactionInformation->toArray() as $key => $value) {
        echo "            ";
        if ($value) {
            echo "            <div class=\"col-sm-4 text-right bottom-margin-5\">\n                <strong>";
            echo AdminLang::trans("transactions.information." . $key);
            echo "</strong>\n            </div>\n            <div class=\"col-sm-8 bottom-margin-5\">\n                ";
            echo $value;
            echo "            </div>\n        ";
        }
    }
    echo "    </div>\n";
}

?>