<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

$validationCom = new WHMCS\User\Validation\ValidationCom\ValidationCom();
$apiSettings = $validationCom->getClientAuth();
$isEnabled = $validationCom->isEnabled();
$isAutoEnabled = $validationCom->isAutoEnabled();
$uploadTypes = $validationCom->getUploadTypes();
echo "<form method=\"post\" action=\"";
echo routePath("admin-validation_com-configure-save");
echo "\">\n    ";
echo generate_token();
echo "    <div class=\"container-fluid row\">\n        <div>\n            <div class=\"bottom-margin-10\">\n                <label for=\"validationComApiKey\">";
echo AdminLang::trans("validationCom.apiKey");
echo "</label>\n                <input type=\"text\" class=\"form-control input-700\" id=\"validationComApiKey\" value=\"";
echo $apiSettings["clientId"];
echo "\" readonly=\"readonly\" aria-describedby=\"validationComApiKey\">\n            </div>\n            <div class=\"bottom-margin-20\">\n                <label for=\"validationComApiSecret\">";
echo AdminLang::trans("validationCom.apiSecret");
echo "</label>\n                <input type=\"password\" autocomplete=\"off\" class=\"form-control input-700\" id=\"validationComApiSecret\" value=\"";
echo $apiSettings["clientSecret"];
echo "\" readonly=\"readonly\" aria-describedby=\"validationComApiSecret\">\n            </div>\n            <div class=\"text-center\">\n                <a href=\"#\" onclick=\"deactivateValidation();return false;\" id=\"btnDeactivateValidation\" class=\"btn btn-danger\">";
echo AdminLang::trans("global.deactivate");
echo " Validation.com</a>\n            </div>\n        </div>\n        <hr/>\n        <div class=\"row bottom-margin-10\">\n            <div class=\"col-xs-3 validation-switch-div\">\n                <input type=\"checkbox\"\n                       class=\"validation-setting-switch\"\n                       name=\"coreEnabled\"\n                       id=\"coreEnabledConfig\"\n                       aria-labelledby=\"validationCoreToggle\"\n                       aria-describedby=\"validationCoreDescSpan\"\n                    ";
echo $isEnabled ? "value=\"1\" checked=\"checked\"" : "";
echo "                >\n            </div>\n            <div class=\"col-xs-9 row\">\n                <div><label for=\"validationCoreToggle\">";
echo AdminLang::trans("validationCom.identityVerification");
echo "</label></div>\n                <span id=\"validationCoreDescSpan\">";
echo AdminLang::trans("validationCom.identityVerificationDesc");
echo "</span>\n            </div>\n        </div>\n        <div class=\"row bottom-margin-10\">\n            <div class=\"col-xs-3 validation-switch-div\">\n                <input type=\"checkbox\"\n                       class=\"validation-setting-switch\"\n                       name=\"autoEnabled\"\n                       id=\"autoEnabledConfig\"\n                       aria-labelledby=\"validationAutoSwitch\"\n                       aria-describedby=\"validationAutoDescSpan\"\n                    ";
echo $isAutoEnabled ? "value=\"1\" checked=\"checked\"" : "";
echo "                >\n            </div>\n            <div class=\"col-xs-9 row\">\n                <div><label for=\"validationAutoSwitch\">";
echo AdminLang::trans("validationCom.autoRequests");
echo "</label></div>\n                <span id=\"validationAutoDescSpan\">";
echo AdminLang::trans("validationCom.autoRequestsDesc");
echo "</span>\n            </div>\n        </div>\n        <hr/>\n        <div>\n            <div class=\"bottom-margin-10\">\n                <label for=\"validationComApiKey\">Required Documents</label>\n            </div>\n            <div class=\"validation-required-docs\">\n                ";
foreach ($validationCom::UPLOAD_TYPES_MAP as $typeId => $typeString) {
    echo "                    <div class=\"upload-type\">\n                        <div class=\"upload-type-toggle\">\n                            <input type=\"checkbox\"\n                                   class=\"validation-setting-switch\"\n                                   name=\"uploadTypes[]\"\n                                   value=\"";
    echo $typeId;
    echo "\"\n                                ";
    echo in_array($typeId, $uploadTypes) ? "checked" : "";
    echo "                            >\n                        </div>\n                        <div class=\"upload-type-label\">\n                            <label>";
    echo AdminLang::trans("validationCom." . $typeString);
    echo "</label>\n                        </div>\n                    </div>\n                ";
}
echo "            </div>\n        </div>\n    </div>\n</form>\n<script>\n    (function() {\n        var coreConfig = jQuery('#coreEnabledConfig'),\n            autoConfig = jQuery('#autoEnabledConfig');\n        jQuery('.validation-setting-switch').bootstrapSwitch({\n            size: 'mini'\n        });\n        if (autoConfig.bootstrapSwitch('state') === false && coreConfig.bootstrapSwitch('state') === false) {\n            autoConfig.bootstrapSwitch('disabled', true);\n        }\n        jQuery('#coreEnabledConfig').on('switchChange.bootstrapSwitch', function() {\n            if (coreConfig.bootstrapSwitch('state') === false) {\n                autoConfig.bootstrapSwitch('state', false).bootstrapSwitch('disabled', true);\n            } else {\n                autoConfig.bootstrapSwitch('disabled', false).bootstrapSwitch('state', true);\n            }\n        });\n    })();\n\n    addAjaxModalPostSubmitEvents('afterSaveValidationConfig');\n    function afterSaveValidationConfig(data, modalForm)\n    {\n        var coreEnabledState = jQuery('#coreEnabledConfig').bootstrapSwitch('state'),\n            validationConfigBtn = jQuery('#btnConfigureValidation');\n        if (!coreEnabledState && validationConfigBtn.hasClass('btn-default')) {\n            validationConfigBtn.removeClass('btn-default')\n                .addClass('btn-success')\n                .text('";
echo AdminLang::trans("global.enable");
echo "');\n        } else if (coreEnabledState && validationConfigBtn.hasClass('btn-success')) {\n            validationConfigBtn.removeClass('btn-success')\n                .addClass('btn-default')\n                .text('";
echo AdminLang::trans("global.configure");
echo "');\n        }\n    }\n\n    function deactivateValidation()\n    {\n        var deactivateValidationBtn = jQuery('#btnDeactivateValidation');\n        if (deactivateValidationBtn.attr('disabled')) {\n            return false;\n        }\n        deactivateValidationBtn.attr('disabled','disabled');\n\n        WHMCS.http.jqClient.post(\n            '";
echo routePath("admin-validation_com-deactivate");
echo "',\n            {\n                token: '";
echo generate_token("plain");
echo "',\n            }\n        ).done(function(response) {\n            if (response.success) {\n                jQuery('div.validationConfigModal').modal('hide');\n                jQuery('#btnConfigureValidation').hide();\n                jQuery('#btnSignupValidation').show();\n            }\n        });\n    }\n</script>\n";

?>