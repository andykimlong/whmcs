<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo "<form action=\"";
echo routePath(($isAdmin ? "admin-" : "") . "account-security-two-factor-enable-verify");
echo "\" onsubmit=\"dialogSubmit();return false\">\n    ";
echo generate_token("form");
echo "    <input type=\"hidden\" name=\"step\" value=\"verify\" />\n    <input type=\"hidden\" name=\"module\" value=\"";
echo $module;
echo "\" />\n    ";
echo $twoFactorConfigurationOutput;
echo "</form>\n";

?>