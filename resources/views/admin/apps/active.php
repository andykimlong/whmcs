<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo "<div class=\"apps active\">\n    ";
$hasActiveApps = false;
foreach ($apps->active() as $app) {
    $this->insert("apps/shared/app", ["app" => $app]);
    $hasActiveApps = true;
}
echo "    ";
if (!$hasActiveApps) {
    echo "        <div class=\"no-active-apps\">\n            <span>";
    echo AdminLang::trans("apps.noActiveApps");
    echo "</span>\n            <br><br>\n            ";
    echo AdminLang::trans("apps.description");
    echo "            <br>\n            ";
    echo AdminLang::trans("apps.activateToGetStarted");
    echo "            <br>\n            <a href=\"#\" class=\"btn btn-default btn-lg\" onclick=\"\$('#tabBrowse').click();\">Browse Apps</a>\n        </div>\n    ";
}
echo "</div>\n";

?>