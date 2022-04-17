<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

$values = ["inputAccountType" => "", "inputBankName" => "", "inputBankAcctHolderName" => "", "inputRoutingNumber" => "", "inputAccountNumber" => ""];
$readOnly = "";
$disabled = "";
$gatewayToken = "";
if (isset($payMethod)) {
    $payment = $payMethod->payment;
    $values["inputBankName"] = $payment->getBankName();
    $values["inputBankAcctHolderName"] = $payment->getAccountHolderName();
    $values["inputRoutingNumber"] = $payment->getRoutingNumber();
    $values["inputAccountNumber"] = $payment->getMaskedAccountNumber();
    $readOnly = "readonly";
    $disabled = "disabled";
    if ($payment instanceof WHMCS\Payment\Contracts\RemoteTokenDetailsInterface) {
        $values["inputGatewayId"] = $payment->getRemoteToken();
        $gatewayToken = $payment->getRemoteToken();
    } else {
        $values["inputGatewayId"] = "no token";
    }
    foreach ($values as $key => $value) {
        $values[$key] = "value=\"" . $value . "\"";
    }
    $values["inputAccountType"] = $payment->getAccountType();
}
echo "\n<div class=\"payMethodTypeForm typeBankAccount row\">\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputAccountType\">\n            ";
echo AdminLang::trans("payments.accountType");
echo "        </label>\n        <select class=\"form-control\" name=\"bankaccttype\" ";
echo $disabled;
echo ">\n            <option value=\"Checking\"";
echo $values["inputAccountType"] === "Checking" ? " selected" : "";
echo ">\n                ";
echo AdminLang::trans("payments.accountTypeChecking");
echo "            </option>\n            <option value=\"Savings\"";
echo $values["inputAccountType"] === "Savings" ? " selected" : "";
echo ">\n                ";
echo AdminLang::trans("payments.accountTypeSavings");
echo "            </option>\n        </select>\n    </div>\n\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputBankAcctHolderName\">\n            ";
echo AdminLang::trans("payments.accountHolderName");
echo "        </label>\n        <input type=\"text\"\n               id=\"inputBankAcctHolderName\"\n               name=\"bankacctholdername\"\n               class=\"form-control\"\n            ";
echo $readOnly;
echo "            ";
echo $values["inputBankAcctHolderName"];
echo "        >\n        <span class=\"field-error-msg\">";
echo AdminLang::trans("global.required");
echo "</span>\n    </div>\n\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputBankName\">\n            ";
echo AdminLang::trans("payments.bankName");
echo "        </label>\n        <input type=\"text\"\n               id=\"inputBankName\"\n               name=\"bankname\"\n               class=\"form-control\"\n            ";
echo $readOnly;
echo "            ";
echo $values["inputBankName"];
echo "        >\n        <span class=\"field-error-msg\">";
echo AdminLang::trans("global.required");
echo "</span>\n    </div>\n\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputRoutingNumber\">\n            ";
echo AdminLang::trans("payments.sortCode");
echo "        </label>\n        <input type=\"text\"\n               id=\"inputRoutingNumber\"\n               name=\"bankroutingnum\"\n               data-enforce-format=\"number\"\n               class=\"form-control\"\n            ";
echo $readOnly;
echo "            ";
echo $values["inputRoutingNumber"];
echo "        >\n        <span class=\"field-error-msg\">";
echo AdminLang::trans("global.required");
echo "</span>\n    </div>\n\n    <div class=\"form-group col-sm-12\">\n        <label for=\"inputAccountNumber\">\n            ";
echo AdminLang::trans("payments.accountNumber");
echo "        </label>\n        <div class=\"input-group\" style=\"width: 100%\">\n            <input type=\"text\"\n                   id=\"inputAccountNumber\"\n                   name=\"bankacctnum\"\n                   data-enforce-format=\"number\"\n                   class=\"form-control\"\n                ";
echo $readOnly;
echo "                ";
echo $values["inputAccountNumber"];
echo "            >\n            <span class=\"input-group-btn\">\n                ";
if ($payMethod && $payMethod->payment instanceof WHMCS\Payment\PayMethod\Adapter\BankAccount) {
    echo "                    <button class=\"btn btn-default\" id=\"btnShowBankHashControls\" type=\"button\">\n                    <i class=\"far fa-unlock\"></i>\n                </button>\n                    <button class=\"btn btn-default hidden copy-to-clipboard\"\n                            data-clipboard-target=\"#inputAccountNumber\" id=\"btnCopyAccountNumber\" type=\"button\">\n                    <img src=\"../assets/img/clippy.svg\" alt=\"";
    echo AdminLang::trans("global.clipboardCopy");
    echo "\"\n                         width=\"15\">\n                </button>\n                ";
}
echo "            </span>\n        </div>\n        <span class=\"field-error-msg\">";
echo AdminLang::trans("global.required");
echo "</span>\n    </div>\n\n    <div class=\"col-sm-12 form-group\" id=\"ccHashControls\" style=\"display: none\">\n        <label for=\"inputCcHash\">\n            ";
echo AdminLang::trans("clients.enterccbankhash");
echo "        </label>\n        <div class=\"input-group\">\n            <input id=\"inputCcHash\"\n                   type=\"password\"\n                   name=\"cchash\"\n                   autocomplete=\"off\"\n                   class=\"form-control\"/>\n            <span class=\"input-group-btn\">\n                <a class=\"btn btn-default\" id=\"btnDecryptBankData\" type=\"button\"><i class=\"fas fa-check\"></i></a>\n            </span>\n        </div>\n    </div>\n</div>\n\n";
echo WHMCS\View\Asset::jsInclude("jquery.payment.js");
echo "\n<script>\n    (function (\$) {\n        \$(document).ready(function () {\n            \$('input[data-enforce-format=\"number\"]').payment('restrictNumeric');\n\n            var bankNumberFieldEnabled = '";
echo empty($payMethod);
echo "',\n                bankForm = \$('#frmCreditCardDetails');\n\n            if (bankForm.find('#inputAccountNumber').length) {\n                \$.fn.showInputError = function () {\n                    this.parents('.form-group').addClass('has-error').find('.field-error-msg').show();\n                    return this;\n                };\n\n                window.bankAccountValidate = function () {\n                    bankForm.find('.form-group').removeClass('has-error');\n                    bankForm.find('.field-error-msg').hide();\n\n                    bankForm.find('.form-group').removeClass('has-error');\n                    bankForm.find('.field-error-msg').hide();\n\n                    var complete = true,\n                        requiredFields = [];\n\n                    if (bankNumberFieldEnabled) {\n                        requiredFields = [\n                            bankForm.find('#inputBankAcctHolderName'),\n                            bankForm.find('#inputBankName'),\n                            bankForm.find('#inputRoutingNumber'),\n                            bankForm.find('#inputAccountNumber')\n                        ];\n                    }\n\n                    requiredFields.forEach(function (field) {\n                        if (!field.val()) {\n                            field.showInputError();\n                            complete = false;\n                        }\n                    });\n\n                    return complete;\n                };\n            }\n\n            addAjaxModalSubmitEvents('bankAccountValidate');\n\n            \$('#btnShowBankHashControls').click(function () {\n                \$('#ccHashControls').slideToggle();\n            });\n\n            \$('#btnDecryptBankData').click(function () {\n                \$('.gateway-errors').slideUp();\n\n                WHMCS.http.jqClient.jsonPost({\n                    url: '";
echo routePath("admin-client-paymethods-decrypt-cc-data", $client->id, $payMethod->id);
echo "',\n                    data: \$('#frmCreditCardDetails').serialize(),\n                    success: function (results) {\n                        if (results.bankAcctNumber) {\n                            \$('#inputAccountNumber').val(results.bankAcctNumber);\n                            bankNumberFieldEnabled = 1;\n\n                            \$('#btnShowBankHashControls').addClass('hidden');\n                            \$('#btnCopyAccountNumber').removeClass('hidden');\n\n                            \$('#ccHashControls').slideUp();\n                        } else if (results.errorMsg) {\n                            \$('.gateway-errors').html(results.errorMsg).removeClass('hidden').slideDown();\n                        }\n                    },\n                    always: function () {\n                        \$('#inputCcHash').val('');\n                    }\n                });\n            });\n        });\n    })(jQuery);\n</script>\n";

?>