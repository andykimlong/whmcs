<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

$location = pathinfo($_SERVER["PHP_SELF"], PATHINFO_DIRNAME) . "/install.php";
header("Location: " . $location);
exit;

?>