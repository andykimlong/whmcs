<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo "<div class=\"alert alert-danger admin-modal-error\" style=\"display: none\"></div>\n<form class=\"form-horizontal\" id=\"frmUserSearch\" method=\"post\" action=\"";
echo routePath("admin-client-user-associate", $clientId);
echo "\">\n    ";
echo generate_token();
echo "    <div class=\"admin-tabs-v2\">\n        <div class=\"tab-content\">\n            <div class=\"tab-pane active\">\n                <div class=\"form-group user-search\">\n                    <label for=\"selectUser\" class=\"col-md-2 col-sm-4 control-label\">\n                        ";
echo AdminLang::trans("user.selectUser");
echo "                    </label>\n                    <div class=\"col-md-10 col-sm-8\">\n                        <select id=\"selectUser\"\n                                name=\"user\"\n                                class=\"form-control selectize selectize-user-search\"\n                                data-value-field=\"id\"\n                                data-allow-empty-option=\"0\"\n                                placeholder=\"";
echo AdminLang::trans("user.typeToSearch");
echo "\"\n                                data-user-label=\"";
echo AdminLang::trans("fields.user");
echo "\"\n                                data-search-url=\"";
echo routePath("admin-client-user-associate-search", $clientId);
echo "\"\n                        >\n                        </select>\n                        <div class=\"field-error-msg\">\n                            ";
echo AdminLang::trans("global.required");
echo "                        </div>\n                    </div>\n                    <div class=\"col-md-offset-2 col-sm-offset-4 col-md-10 col-sm-8 help-block\">\n                        ";
echo AdminLang::trans("user.chooseUser");
echo "                    </div>\n                </div>\n                <div id=\"divInviteUser\" class=\"alert alert-info notice-invite-user hidden\">\n                    ";
echo AdminLang::trans("user.anInviteWillBeSent");
echo "                </div>\n                <div class=\"form-group user-search\">\n                    <label for=\"permissions\" class=\"col-md-2 col-sm-4 control-label\">\n                        ";
echo AdminLang::trans("fields.permissions");
echo "                    </label>\n                    <div class=\"col-md-10 col-sm-8\">\n                        ";
$this->insert("client/users/partials/permission-list");
echo "                    </div>\n                </div>\n                <div class=\"form-group\">\n                    <label for=\"checkSendInvite\" class=\"col-md-2 col-sm-4 control-label\">\n                        ";
echo AdminLang::trans("user.sendInvite");
echo "                    </label>\n                    <div class=\"col-md-10 col-sm-8\">\n                        <input id=\"checkSendInvite\"\n                               type=\"checkbox\"\n                               name=\"invite\"\n                               checked=\"checked\"\n                               data-on-text=\"";
echo AdminLang::trans("global.yes");
echo "\"\n                               data-off-text=\"";
echo AdminLang::trans("global.no");
echo "\"\n                               value=\"yes\"\n                        >\n                    </div>\n                </div>\n            </div>\n        </div>\n    </div>\n</form>\n<script>\n    jQuery(document).ready(function() {\n        var modalSubmitButton = jQuery('#btnAssociateUser');\n        WHMCS.selectize.userSearch();\n        jQuery('#checkSendInvite').bootstrapSwitch()\n            .on('switchChange.bootstrapSwitch', function(event, state) {\n                var inputText;\n                if (state) {\n                    inputText = '";
echo WHMCS\Input\Sanitize::escapeSingleQuotedString(AdminLang::trans("user.inviteUser"));
echo "';\n                } else {\n                    inputText = '";
echo WHMCS\Input\Sanitize::escapeSingleQuotedString(AdminLang::trans("user.associateUser"));
echo "&nbsp;<i class=\"fas fa-plus-circle\"></i>';\n                }\n                modalSubmitButton.html(inputText)\n            });\n        jQuery('#selectUser').on('change', function() {\n            var value = jQuery(this).val(),\n                isNumeric = !isNaN(value),\n                inviteAlert = jQuery('.notice-invite-user');\n            if (!isNumeric && inviteAlert.length) {\n                inviteAlert.removeClass('hidden');\n            } else if (inviteAlert.length) {\n                inviteAlert.addClass('hidden');\n            }\n        });\n    });\n</script>\n";

?>