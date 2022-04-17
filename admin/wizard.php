<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("");
$response = [];
$wizard = NULL;
try {
    $requestedWizard = App::getFromRequest("wizard");
    $wizard = WHMCS\Admin\Wizard\Wizard::factory($requestedWizard);
} catch (WHMCS\Exception\AccessDenied $e) {
    $response = ["body" => "<div class=\"container\"><h2>" . $e->getMessage() . "</h2></div>"];
} catch (Exception $e) {
    $response = ["body" => $e->getMessage()];
    $dismiss = App::getFromRequest("dismiss");
    if ($dismiss == "true") {
        WHMCS\Config\Setting::setValue("DisableSetupWizard", 1);
        $response = ["disabled" => true];
    }
    if (!is_null($wizard)) {
        if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST" && 0 < count($_POST)) {
            check_token("WHMCS.admin.default");
            try {
                $step = App::getFromRequest("step");
                $action = App::getFromRequest("action");
                if (!$action) {
                    $action = "save";
                }
                $returnData = $wizard->handleSubmit($step, $action, $_POST);
                $response = ["success" => true];
                if (is_array($returnData)) {
                    $response = array_merge($response, $returnData);
                }
            } catch (Exception $e) {
                $response = ["success" => false, "error" => $e->getMessage()];
            }
        } else {
            $output = $wizard->render(new WHMCS\Smarty(true, "mail"));
            $response = ["body" => $output];
        }
    }
    $aInt->setBodyContent($response);
    $aInt->output();
}

?>