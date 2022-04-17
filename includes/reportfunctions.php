<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

function addPrintInputToForm($formContainer)
{
    return preg_replace("/(<form\\W[^>]*\\bmethod=('|\"|)POST('|\"|)\\b[^>]*>)/i", "\\1\n<input type=\"hidden\" name=\"print\" value=\"true\" />", $formContainer);
}
function getReportsList()
{
    if (!$textReports) {
        $textReports = [];
        $reportDir = ROOTDIR . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . "reports" . DIRECTORY_SEPARATOR;
        $dh = opendir($reportDir);
        while (false !== ($file = readdir($dh))) {
            if ($file != "index.php" && is_file($reportDir . $file)) {
                $file = str_replace(".php", "", $file);
                if (substr($file, 0, 5) != "graph") {
                    $niceName = str_replace("_", " ", $file);
                    $niceName = titleCase($niceName);
                    $textReports[$file] = $niceName;
                }
            }
        }
        closedir($dh);
        asort($textReports);
    }
    return $textReports;
}

?>