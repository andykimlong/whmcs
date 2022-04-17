<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

if ($errorMsg) {
    echo "    <div class=\"alert alert-danger\">\n        <strong>";
    echo AdminLang::trans("subscription.unableToRetrieve");
    echo ":</strong>\n        <br>\n        ";
    echo $errorMsg;
    echo "    </div>\n";
} else {
    echo "\n    ";
    if ($isActive) {
        echo "        <div class=\"alert alert-success\">\n            <i class=\"fas fa-check fa-fw\"></i>\n            ";
        echo AdminLang::trans("subscription.active");
        echo "        </div>\n    ";
    }
    echo "\n    ";
    echo $subscriptionDetails;
    echo "\n";
}

?>