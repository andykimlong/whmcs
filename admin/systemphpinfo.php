<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("View PHP Info");
$aInt->title = $aInt->lang("system", "phpinfo");
$aInt->sidebar = "utilities";
$aInt->icon = "phpinfo";
ob_start();
phpinfo();
$info = ob_get_contents();
ob_end_clean();
$info = preg_replace("%^.*<body>(.*)</body>.*\$%ms", "\$1", $info);
ob_start();
echo "<div class=\"whmcs-phpinfo\">" . $info . "</div>";
$content = ob_get_contents();
ob_end_clean();
$aInt->content = $content;
$aInt->display();

?>