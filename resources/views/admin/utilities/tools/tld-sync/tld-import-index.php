<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo WHMCS\View\Asset::cssInclude("tabdrop.css");
echo WHMCS\View\Asset::jsInclude("bootstrap-tabdrop.js");
if (0 < count($supportedRegistrars)) {
    echo "    <p>";
    echo AdminLang::trans("domains.tldImport.description");
    echo "</p>\n    <div id=\"tldImportRegistrarContainer\" class=\"tld-import-step row\">\n        ";
    foreach ($supportedRegistrars as $registrar => $setting) {
        echo "            <div class=\"col-sm-4\">\n                <a href=\"#\" class=\"module-selector btn-tld-registrar\" data-registrar=\"";
        echo $registrar;
        echo "\" data-registrar-name=\"";
        echo $setting["name"];
        echo "\">\n                    <img id=\"imgTldImportRegistrar";
        echo $setting["name"];
        echo "\" src=\"";
        echo $setting["logo"];
        echo "\" alt=\"";
        echo $setting["name"];
        echo "\">\n                </a>\n            </div>\n        ";
    }
    echo "    </div>\n    <hr>\n    <div id=\"alertGeneralError\" class=\"alert alert-danger hidden\"></div>\n    <div id=\"importTldStepTwo\" class=\"tld-import-step top-margin-10\"></div>\n    <div id=\"importTldStepThree\" class=\"tld-import-step top-margin-10 text-center hidden\">\n        <h2>";
    echo AdminLang::trans("domains.tldImport.importComplete");
    echo "</h2>\n        <div id=\"panelTldImportSuccesses\" class=\"panel panel-default\">\n            <div class=\"panel-heading\">\n                ";
    echo AdminLang::trans("domains.tldImport.successes");
    echo "            </div>\n            <table class=\"table\"></table>\n        </div>\n        <div id=\"panelTldImportFailures\" class=\"panel panel-danger\">\n            <div class=\"panel-heading\">\n                ";
    echo AdminLang::trans("domains.tldImport.failures");
    echo "            </div>\n            <ul class=\"list-group\"></ul>\n        </div>\n    </div>\n";
} else {
    echo "    <div class=\"alert alert-info\">\n        ";
    echo AdminLang::trans("domains.tldImport.noRegistrars");
    echo "    </div>\n";
}
echo "<script>\n    var tldsToImport = new Set(),\n        tldsOnAnotherRegistrar = new Set(),\n        selectedRegistrar = '',\n        importSuccesses = jQuery('#panelTldImportSuccesses'),\n        importFailures = jQuery('#panelTldImportFailures'),\n        langMustSelectTld = \"";
echo addslashes(AdminLang::trans("domains.tldImport.noTldsSelected"));
echo "\",\n        otherRegistrarWarning = \"";
echo addslashes(AdminLang::trans("domains.tldImport.autoRegisterOtherRegistrarWarning"));
echo "\",\n        langOtherRegistrarWarning = '';\n    jQuery(document).ready(function() {\n        jQuery('.btn-tld-registrar').on('click', function() {\n            if (jQuery(this).prop('disabled')) {\n                return false;\n            }\n            var thisButton = jQuery(this),\n                allRegistrars = jQuery('.btn-tld-registrar'),\n                generalError = jQuery('#alertGeneralError'),\n                stepTwo = jQuery('#importTldStepTwo'),\n                stepThree = jQuery('#importTldStepThree');\n            allRegistrars.removeClass('active').prop('disabled', true).addClass('disabled');\n            thisButton.addClass('active');\n            if (generalError.is(':visible')) {\n                generalError.hide();\n            }\n            if (stepThree.is(':visible')) {\n                stepThree.slideUp().addClass('hidden');\n            }\n            if (stepTwo.not(':visible')) {\n                stepTwo.html('').show();\n            }\n            importSuccesses.find('table').html('');\n            if (!importSuccesses.hasClass('hidden')) {\n                importSuccesses.addClass('hidden');\n            }\n            importFailures.find('ul').html('');\n            if (!importFailures.hasClass('hidden')) {\n                importFailures.addClass('hidden');\n            }\n            showLoading(\n                stepTwo,\n                '";
echo AdminLang::trans("domains.tldImport.loading");
echo "'\n                    .replace(':registrar', thisButton.data('registrar-name'))\n            );\n            selectedRegistrar = jQuery(this).data('registrar');\n            WHMCS.http.jqClient.jsonPost(\n                {\n                    url: '";
echo routePath("admin-utilities-tools-tld-import-step-two");
echo "'\n                        + '/' + selectedRegistrar,\n                    data: {\n                        token: csrfToken,\n                    },\n                    success: function(data) {\n                        if (data.success) {\n                            stepTwo.html(data.body).promise().done(function () {\n                                jQuery(\".tld-tabs\").tabdrop();\n                                jQuery(window).resize();\n                                jQuery('#inputSyncRedemption,#inputSetAutoRegistrar').bootstrapSwitch();\n                                jQuery('i[data-toggle=\"tooltip\"]').tooltip();\n                            });\n                        }\n                    },\n                    error: function (data) {\n                        generalError.html(data).hide().removeClass('hidden').slideDown();\n                    },\n                    always: function () {\n                        jQuery('#loadingTlds').slideUp().remove();\n                        allRegistrars.prop('disabled', false).removeClass('disabled');\n                    }\n                }\n            );\n            return false;\n        });\n        jQuery(document).on('click', '.check-all-tlds', function (event) {\n            var checked = this.checked;\n\n            jQuery('#tab' + jQuery(this).data('category')).find('input.tld-checkbox').each(function () {\n                jQuery(this).prop('checked', checked);\n                jQuery(this).trigger('change');\n            });\n        });\n        jQuery(document).on('change', '.tld-checkbox', function(e) {\n            var tld = jQuery(this).data('tld'),\n                checked = this.checked,\n                autoRegistrar = jQuery(this).data('auto-registrar');\n            jQuery('input.tld-checkbox[value=\"' + tld + '\"]').not(jQuery(this)).prop('checked', checked);\n\n            if (checked) {\n                if (!tldsToImport.has(tld)) {\n                    tldsToImport.add(tld);\n                }\n                if (\n                    autoRegistrar\n                    && autoRegistrar !== selectedRegistrar\n                    && !tldsOnAnotherRegistrar.has(tld)\n                ) {\n                    tldsOnAnotherRegistrar.add(tld);\n                }\n            } else {\n                if (tldsToImport.has(tld)) {\n                    tldsToImport.delete(tld);\n                }\n                if (tldsOnAnotherRegistrar.has(tld)) {\n                    tldsOnAnotherRegistrar.delete(tld);\n                }\n            }\n            jQuery('#tldImportCount,#tldImportCount2').text(tldsToImport.size);\n        });\n        jQuery(document).on('click', '#doTldImport,#doTldImport2', function(e, options) {\n            options = options || {};\n            var tldCount = parseInt(jQuery('#tldImportCount').html()),\n                roundingValue = jQuery('#inputRoundingValue').val(),\n                setAutoRegistrar = jQuery('#inputSetAutoRegistrar').is(\":checked\"),\n                self = jQuery(this);\n            if (tldCount === 0) {\n                jQuery.growl.warning({title: '', message: langMustSelectTld});\n                return false;\n            }\n            if (parseFloat(roundingValue) > 1) {\n                return false;\n            }\n            if (setAutoRegistrar && tldsOnAnotherRegistrar.size > 0 && !options.doImport) {\n                langOtherRegistrarWarning = otherRegistrarWarning.replace(\n                    ':registrar',\n                    selectedRegistrar\n                );\n                var tldOutput = '<div style=\"max-height: 75px;overflow-y: auto;overflow-x: hidden;\">';\n                    tldsOnAnotherRegistrar.forEach(function (item) {\n                        tldOutput += item + ', ';\n                    });\n                    tldOutput = tldOutput.substr(0, (tldOutput.length - 2));\n                    tldOutput += '</div>';\n                swal(\n                    {\n                        title: '";
echo stripslashes(AdminLang::trans("global.areYouSure"));
echo "',\n                        text: langOtherRegistrarWarning + \"<br><br>\" + tldOutput,\n                        type: 'info',\n                        showCancelButton: true,\n                        html: true,\n                        //confirmButtonColor: \"#DD6B55\",\n                        confirmButtonText: '";
echo stripslashes(AdminLang::trans("global.yes"));
echo "'\n                    },\n                    function(){\n                        self.trigger('click', { 'doImport': true });\n                    }\n                );\n                return;\n            }\n            jQuery('#tldImportCount,#tldImportCount2').text(tldsToImport.size);\n            jQuery('#importTldStepTwo').slideUp().promise().done(function () {\n                var importStepThree = jQuery('#importTldStepThree');\n                window.scrollTo(0, jQuery('#tldImportRegistrarContainer').position().top);\n                showLoading(importStepThree, '";
echo AdminLang::trans("global.loading");
echo "');\n                importStepThree.hide().removeClass('hidden').promise().done(function () {\n                    var tldCount = parseInt(jQuery('#tldImportCount').html()),\n                        markupType = jQuery('#inputMarginType').val(),\n                        markupAmountVar = 'inputMarginPercent',\n                        tldList = [];\n                    if (markupType !== 'percentage') {\n                        markupAmountVar = 'inputMarginFixed';\n                    }\n\n                    tldsToImport.forEach(function (value) {\n                        tldList.push(value);\n                    });\n\n                    if (tldCount === 0) {\n                        jQuery.growl.warning({title: '', message: langMustSelectTld});\n                        return false;\n                    }\n                    WHMCS.http.jqClient.jsonPost(\n                        {\n                            url: '";
echo routePath("admin-utilities-tools-tld-import-do");
echo "',\n                            data: {\n                                token: csrfToken,\n                                tld: tldList.join(','),\n                                margin_type: markupType,\n                                margin: jQuery('#' + markupAmountVar).val(),\n                                rounding_value: jQuery('#inputRoundingValue').val(),\n                                registrar: selectedRegistrar,\n                                sync_redemption: jQuery('#inputSyncRedemption').is(\":checked\") ? 1 : 0,\n                                set_auto_register: setAutoRegistrar ? 1 : 0\n                            },\n                            success: function(data) {\n                                if (data.failed.length) {\n                                    jQuery.each(data.failed, function(index, item) {\n                                        importFailures.find('ul').append(\n                                            '<li class=\"list-group-item\">'\n                                                + item.tld + ': ' + item.error\n                                                + '</li>'\n                                        );\n                                        tldsToImport.delete(item.tld);\n                                    });\n\n                                    if (importFailures.hasClass('hidden')) {\n                                        importFailures.removeClass('hidden');\n                                    }\n                                } else {\n                                    if (!importFailures.hasClass('hidden')) {\n                                        importFailures.removeClass('hidden');\n                                    }\n                                }\n                                if (tldsToImport.size === 0) {\n                                    if (!importSuccesses.hasClass('hidden')) {\n                                        importSuccesses.addClass('hidden');\n                                    }\n                                } else {\n                                    var i = 1,\n                                        tableOutput = '<tr>';\n                                    tldsToImport.forEach(function(item) {\n                                        tableOutput += '<td>' + item + '</td>';\n                                        i++;\n                                        if (i === 11) {\n                                            tableOutput += '</tr><tr>';\n                                            i = 1;\n                                        }\n                                    });\n                                    if (i !== 1 &&  i !== 11 && tldsToImport.size > 10) {\n                                        do {\n                                            tableOutput += '<td></td>';\n                                            i++;\n                                        }\n                                        while (i < 11);\n                                    }\n                                    tableOutput += '</tr>';\n                                    importSuccesses.find('table').html(tableOutput);\n                                    if (importSuccesses.hasClass('hidden')) {\n                                        importSuccesses.removeClass('hidden');\n                                    }\n                                    tldsToImport.clear();\n                                }\n                            },\n                            error: function (data) {\n\n                            },\n                            always: function () {\n                                if (tldsToImport.size === 0) {\n                                    jQuery('#loadingTlds').slideUp().remove();\n                                    importStepThree.slideDown();\n                                }\n                            }\n                        }\n                    );\n                });\n            });\n        });\n        jQuery(document).on('click', 'td', function (e) {\n            if (e.toElement.nodeName !== 'TD') {\n                return;\n            }\n            jQuery(this).closest('tr').find('.tld-checkbox').click();\n        });\n        jQuery(document).on('change', '#inputMarginType', function () {\n            jQuery('.tld-import-percentage-margin,.tld-import-fixed-margin').toggleClass('hidden');\n            if (jQuery(this).val() === 'percentage') {\n                jQuery('.percentage-display').show();\n                jQuery('.absolute-display').hide();\n            } else {\n                jQuery('.percentage-display').hide();\n                jQuery('.absolute-display').show();\n            }\n        });\n        jQuery(document).on('click', '#btnSelectRegistrar', function () {\n            var check = true;\n            if (jQuery(this).hasClass('uncheck')) {\n                var items = jQuery('input.tld-checkbox[data-auto-registrar=\"' + selectedRegistrar + '\"]')\n                    .is(':checked');\n                jQuery(this).text(\n                    '";
echo AdminLang::trans("domains.tldImport.selectRegistrar");
echo "'\n                );\n                check = false;\n            } else {\n                items = jQuery('input.tld-checkbox[data-auto-registrar=\"' + selectedRegistrar + '\"]')\n                    .not(':checked');\n                jQuery(this).text(\n                    '";
echo AdminLang::trans("domains.tldImport.deselectRegistrar");
echo "'\n                );\n            }\n            jQuery(items).each(function () {\n                jQuery(this).prop('checked', check);\n                jQuery(this).trigger('change');\n            });\n            jQuery(this).toggleClass('uncheck');\n        });\n        jQuery(window).resize(function() {\n            jQuery(\".tld-tabs\").tabdrop();\n        })\n    });\n    function showLoading(beforeElement, message) {\n        beforeElement.before(\n            '<div id=\"loadingTlds\" class=\"text-center\">' +\n            '<i class=\"fad fa-cog fa-spin\"></i><br>' +\n            ' ' + message + '</div>'\n        );\n    }\n\n    function openPricingPopup(id)\n    {\n        var winLeft = ((screen.width - 560) / 2),\n            winTop = ((screen.height - 600) / 2),\n            winProperties = 'height=600,width=560,top=' + winTop + ',left=' + winLeft + ',scrollbars=yes',\n            win = window.open(\n                '";
echo $basePath . "/" . App::get_admin_folder_name();
echo "/configdomains.php?action=editpricing&id=' + id,\n                'domainpricing',\n                winProperties\n            );\n        if (parseInt(navigator.appVersion) >= 4) {\n            win.window.focus();\n        }\n    }\n</script>\n";

?>