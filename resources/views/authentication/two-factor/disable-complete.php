<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

echo "<p>";
echo Lang::trans("twofadisableconfirmation");
echo "</p>\n\n<script>\n\$('.twofa-toggle-switch').bootstrapSwitch('state', false, true);\n\$('.twofa-config-link.disable').hide();\n\$('.twofa-config-link.enable').removeClass('hidden').show();\n</script>\n";

?>