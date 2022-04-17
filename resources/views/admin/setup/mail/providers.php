<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo "<div class=\"alert alert-success\" id=\"mailProviderSuccess\" style=\"display: none;\" role=\"alert\">\n    ";
echo AdminLang::trans("mail.testSuccess");
echo "</div>\n<div class=\"alert alert-danger admin-modal-error\" id=\"mailProviderError\" style=\"display: none;\" role=\"alert\"></div>\n<form class=\"form-horizontal\" id=\"frmMailProviderConfiguration\" name=\"frmMailProviderConfiguration\" method=\"post\" action=\"";
echo routePath("admin-setup-mail-provider-save");
echo "\">\n    ";
echo generate_token();
echo "    <div class=\"admin-tabs-v2\">\n        <div class=\"tab-content\">\n            <div class=\"tab-pane active\">\n                <div id=\"mailProvider\" class=\"form-group\">\n                    <label for=\"selectMailProvider\" class=\"col-md-4 col-sm-6 control-label\">\n                        ";
echo AdminLang::trans("mail.provider");
echo "                    </label>\n                    <div class=\"col-md-8 col-sm-6\">\n                        <select id=\"selectMailProvider\" name=\"module\" class=\"form-control\">\n                            ";
foreach ($allModules as $moduleName => $displayName) {
    $selected = "";
    if ($moduleName == $mailInterface->getLoadedModule()) {
        $selected = " selected=\"selected\"";
    }
    echo "<option value=\"" . $moduleName . "\"" . $selected . ">" . $displayName . "</option>";
}
echo "                        </select>\n                    </div>\n                </div>\n                <div id=\"mailProviderSettings\" style=\"overflow-x: hidden; overflow-y: auto; max-height: 33em;\">\n                    ";
$this->insert("setup/mail/config");
echo "                </div>\n            </div>\n        </div>\n    </div>\n</form>\n<script>\n    jQuery(document).ready(function() {\n        \$('#divTestConfigButton').remove();\n        \$('#modalAjaxLoader').before('<div id=\"divTestConfigButton\" class=\"pull-left\">'+\n            '<button id=\"btnTestConfiguration\" class=\"btn btn-default\" type=\"button\">'+\n            '<i class=\"fas fa-spinner fa-spin\" style=\"display: none\"></i>'+\n            '";
echo AdminLang::trans("mail.test");
echo "'+\n            '</button></div>');\n\n        jQuery(document).off('change', '#selectMailProvider');\n        jQuery(document).on('change', '#selectMailProvider', function() {\n            var mailProviderDiv = jQuery('#mailProvider'),\n                modalLoader = jQuery('#modalAjax .loader'),\n                successDiv = jQuery('#mailProviderSuccess'),\n                errorDiv = jQuery('#mailProviderError'),\n                submitButton = jQuery('#btnSaveMailConfiguration'),\n                testButton = jQuery('#btnTestConfiguration');\n            if (successDiv.is(':visible')) {\n                successDiv.hide();\n            }\n            if (errorDiv.is(':visible')) {\n                errorDiv.hide();\n            }\n            submitButton.addClass('disabled').prop('disabled', true);\n            testButton.addClass('disabled').prop('disabled', true);\n            modalLoader.show();\n            jQuery('.mail-provider-configuration').hide().remove();\n            WHMCS.http.jqClient.jsonPost(\n                {\n                    url: '";
echo routePath("admin-setup-mail-provider-configuration");
echo "',\n                    data: {\n                        token: csrfToken,\n                        module: jQuery(this).val(),\n                    },\n                    success: function(data) {\n                        mailProviderDiv.after(data.body);\n                    },\n                    error: function(error) {\n                        errorDiv.text(error).show();\n                    },\n                    fail: function(error, xhr) {\n                        errorDiv.text(xhr.responseJSON.errorMessage).show();\n                    },\n                    always: function() {\n                        modalLoader.fadeOut();\n                        submitButton.removeClass('disabled').prop('disabled', false);\n                        testButton.removeClass('disabled').prop('disabled', false);\n                    }\n                }\n            );\n        });\n\n        jQuery(document).off('click', '#btnTestConfiguration');\n        jQuery(document).on('click', '#btnTestConfiguration', function() {\n            var successDiv = jQuery('#mailProviderSuccess'),\n                submitButton = jQuery('#btnSaveMailConfiguration'),\n                errorDiv = jQuery('#mailProviderError'),\n                self = jQuery(this);\n            if (successDiv.is(':visible')) {\n                successDiv.hide();\n            }\n            if (errorDiv.is(':visible')) {\n                errorDiv.hide();\n            }\n            self.find('i').show();\n            self.addClass('disabled').prop('disabled', true);\n            submitButton.addClass('disabled').prop('disabled', true);\n            WHMCS.http.jqClient.jsonPost(\n                {\n                    url: '";
echo routePath("admin-setup-mail-provider-configuration-test");
echo "',\n                    data: jQuery('#frmMailProviderConfiguration').serialize(),\n                    success: function(data) {\n                        if (data.success) {\n                            successDiv.show();\n                        }\n                    },\n                    error: function(error) {\n                        errorDiv.text(error).show();\n                    },\n                    fail: function(error, xhr) {\n                        errorDiv.text(xhr.responseJSON.errorMessage).show();\n                    },\n                    always: function() {\n                        self.find('i').hide();\n                        self.removeClass('disabled').prop('disabled', false);\n                        submitButton.removeClass('disabled').prop('disabled', false);\n                    }\n                }\n            );\n        });\n    });\n</script>\n";

?>