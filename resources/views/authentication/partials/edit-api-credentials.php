<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo "<form name=\"frmApiCredentialManage\" action=\"";
echo routePath("admin-setup-authz-api-devices-update");
echo "\">\n    <input type=\"hidden\" name=\"token\" value=\"";
echo $csrfToken;
echo "\">\n    <input type=\"hidden\" name=\"id\" value=\"";
echo $device->id;
echo "\">\n    ";
echo $this->insert("partials/attributes-api-credentials");
echo "</form>\n";

?>