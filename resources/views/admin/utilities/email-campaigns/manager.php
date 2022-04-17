<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo "<div class=\"bottom-margin-10\">";
echo AdminLang::trans("utilities.emailCampaigns.description");
echo "</div>\n<div class=\"bottom-margin-10\">\n    <a id=\"btnAddCampaign\" class=\"btn btn-default\" href=\"";
echo $WEB_ROOT . "/" . App::get_admin_folder_name();
echo "/massmail.php\">\n        <i class=\"fas fa-plus\"></i> ";
echo AdminLang::trans("utilities.emailCampaigns.createNew");
echo "    </a>\n</div>\n";
if ($updated) {
    echo "    ";
    echo WHMCS\View\Helper::alert("<strong>" . AdminLang::trans("global.success") . "</strong><br />" . AdminLang::trans("utilities.emailCampaigns.updated"), "success");
} else {
    if ($draft) {
        echo "    ";
        echo WHMCS\View\Helper::alert("<strong>" . AdminLang::trans("global.success") . "</strong><br />" . AdminLang::trans("utilities.emailCampaigns.draftCreated"), "success");
    } else {
        if ($newCampaignAdded) {
            echo "    ";
            echo WHMCS\View\Helper::alert("<strong>" . AdminLang::trans("global.success") . "</strong><br />" . AdminLang::trans("utilities.emailCampaigns.created"), "success");
        }
    }
}
if ($uneditable) {
    echo "    ";
    echo WHMCS\View\Helper::alert("<strong>" . AdminLang::trans("global.erroroccurred") . "</strong><br />" . AdminLang::trans("utilities.emailCampaigns.uneditable"), "danger");
}
echo $campaignTableOutput;
echo "<script>\n    var deleteId = 0;\n    function campaignSuccessMessage(text)\n    {\n        jQuery.growl.notice(\n            {\n                title: '',\n                message: text\n            }\n        );\n    }\n    function campaignErrorMessage(text)\n    {\n        jQuery.growl.warning(\n            {\n                title: '',\n                message: text\n            }\n        );\n    }\n    jQuery(document).ready(function() {\n        jQuery('button.manage').width(function() {\n            return jQuery(this).outerWidth(true);\n        });\n        jQuery('body').on('click', '.pause', function(e) {\n            e.preventDefault();\n            var self = jQuery(this),\n                id = self.data('campaign-id');\n            self.prop('disabled', true).addClass('disabled').end();\n            self.closest('.btn-group').find('button span').toggleClass('hidden').end();\n            WHMCS.http.jqClient.jsonPost({\n                url: '";
echo routePath("admin-utilities-tools-email-campaigns-pause");
echo "',\n                data: {\n                    token: csrfToken,\n                    id: id\n                },\n                success: function(response) {\n                    self.closest('tr').find('span.status').text(response.status);\n                    self.closest('td').find('a.resume, a.pause').toggleClass('hidden').end();\n                    campaignSuccessMessage(response.successMessage);\n                },\n                warning: campaignErrorMessage,\n                error: campaignErrorMessage,\n                fail: campaignErrorMessage,\n                always: function() {\n                    self.prop('disabled', false).removeClass('disabled');\n                    self.closest('.btn-group').find('button span').toggleClass('hidden').end();\n                }\n            });\n        }).on('click', '.resume', function(e) {\n            e.preventDefault();\n            var self = jQuery(this),\n                id = self.data('campaign-id');\n            self.prop('disabled', true).addClass('disabled').end();\n            self.closest('.btn-group').find('button span').toggleClass('hidden').end();\n            WHMCS.http.jqClient.jsonPost({\n                url: '";
echo routePath("admin-utilities-tools-email-campaigns-resume");
echo "',\n                data: {\n                    token: csrfToken,\n                    id: id\n                },\n                success: function(response) {\n                    self.closest('tr').find('span.status').text(response.status);\n                    self.closest('td').find('a.resume, a.pause').toggleClass('hidden').end();\n                    campaignSuccessMessage(response.successMessage);\n                },\n                warning: campaignErrorMessage,\n                error: campaignErrorMessage,\n                fail: campaignErrorMessage,\n                always: function() {\n                    self.prop('disabled', false).removeClass('disabled');\n                    self.closest('.btn-group').find('button span').toggleClass('hidden').end();\n                }\n            });\n        }).on('click', '.edit', function(e) {\n            e.preventDefault();\n            var self = jQuery(this),\n                id = self.data('campaign-id'),\n                uri = '";
echo App::getSystemURL() . App::get_admin_folder_name() . "/sendmessage.php?type=massmail";
echo "';\n            window.location.href = uri + '&campaign=' + id;\n        }).on('click', '.delete', function(e) {\n            e.preventDefault();\n            deleteId = jQuery(this).data('campaign-id');\n            jQuery('#doDelete').modal('show');\n        }).on('click', '#doDelete-ok', function(e) {\n            e.preventDefault();\n            var btn = jQuery('a.delete[data-campaign-id=\"' + deleteId + '\"]');\n            btn.prop('disabled', true).addClass('disabled').end();\n            btn.closest('.btn-group').find('button span').toggleClass('hidden').end();\n            WHMCS.http.jqClient.jsonPost({\n                url: '";
echo routePath("admin-utilities-tools-email-campaigns-delete");
echo "',\n                data: {\n                    token: csrfToken,\n                    id: deleteId\n                },\n                success: function (response) {\n                    var table = btn.closest('table');\n                    btn.closest('tr').remove();\n                    if (table.find('tr').length === 1) {\n                        table.append(\n                            '<tr><td colspan=\"8\" class=\"text-center\">";
echo escape(AdminLang::trans("global.norecordsfound"));
echo "</td></tr>'\n                        );\n                    }\n                    campaignSuccessMessage(response.successMessage);\n                },\n                warning: campaignErrorMessage,\n                error: campaignErrorMessage,\n                fail: campaignErrorMessage,\n                always: function() {\n                    if (btn.length) {\n                        btn.prop('disabled', false).removeClass('disabled');\n                        btn.closest('.btn-group').find('button span').toggleClass('hidden').end();\n                    }\n                    jQuery('#doDelete').modal('hide');\n                }\n            });\n        }).on('click', 'button.btn-retry', function() {\n            var loader = jQuery('#modalAjax .loader'),\n                self = jQuery(this),\n                errorContainer = jQuery('#retryError');\n            jQuery('#modalAjax').find('button.btn-retry')\n                .addClass('disabled')\n                .prop('disabled', true);\n\n            loader.show();\n            errorContainer.slideUp();\n\n            WHMCS.http.jqClient.jsonPost({\n                url: '";
echo routePath("admin-utilities-tools-email-campaigns-retry-single-email");
echo "',\n                data: {\n                    token: csrfToken,\n                    id: self.data('email-id')\n                },\n                success: function(data) {\n                    if (data.success === true) {\n                        var sent = jQuery('#divSentEmails'),\n                            failed = jQuery('#divFailedEmails'),\n                            remaining = jQuery('#divRemainingEmails');\n                        self.closest('tr').remove();\n                        sent.text(data.sentEmailsText).attr('aria-valuenow', data.sentCount);\n                        failed.text(data.failedEmailsText).attr('aria-valuenow', data.failedCount);\n                        remaining.text(data.remainingEmailsText).attr('aria-valuenow', data.remainingCount);\n                        updateSendingProgress();\n                        campaignSuccessMessage('";
echo AdminLang::trans("utilities.emailCampaigns.sendRetried");
echo "');\n\n                        if (data.failedCount === 0) {\n                            jQuery('#rowNoResults').removeClass('hidden');\n                        }\n                    } else {\n                        self.closest('tr').find('failure-reason').text(data.failureReason);\n                        errorContainer.html(data.failureReason)\n                            .slideDown();\n                    }\n                },\n                always: function() {\n                    loader.hide();\n                    jQuery('#modalAjax').find('button.btn-retry')\n                        .removeClass('disabled')\n                        .prop('disabled', false);\n                }\n            });\n\n        });\n\n        window.updateSendingProgress = function () {\n            jQuery('#progressSending').find('.progress-bar').each(function(index) {\n                var currentValue = jQuery(this).attr('aria-valuenow'),\n                    maxValue = jQuery(this).attr('aria-valuemax');\n                jQuery(this).css(\n                    'width',\n                    ((currentValue / maxValue) * 100) + '%'\n                );\n            });\n        }\n    });\n</script>\n<style>\n    div.tablebg {\n        overflow: visible;\n    }\n    a.disabled {\n        pointer-events: none;\n        cursor: not-allowed;\n        opacity: .65;\n    }\n</style>\n";
echo WHMCS\View\Helper::confirmationModal("doDelete", AdminLang::trans("utilities.emailCampaigns.deleteInfo"));

?>