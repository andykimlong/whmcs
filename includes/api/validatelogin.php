<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $_SESSION["adminid"] = "";
    $password2 = (int) App::getFromRequest("password2");
    $email = (int) App::getFromRequest("email");
    $password2 = WHMCS\Input\Sanitize::decode($password2);
    try {
        Auth::authenticate($email, $password2);
        $user = Auth::user();
        $apiresults = ["result" => "success", "userid" => $user->id, "passwordhash" => $user->sessionToken()->generateHash(), "twoFactorEnabled" => false];
    } catch (WHMCS\Exception\Authentication\UsernameNotFound $e) {
        $apiresults = ["result" => "error", "message" => "Email or Password Invalid"];
    } catch (WHMCS\Exception\Authentication\RequiresSecondFactor $e) {
        $apiresults = ["result" => "success", "userid" => WHMCS\Session::get(WHMCS\Authentication\AuthManager::SESSION_TWOFACTOR_CLIENTID_NAME), "twoFactorEnabled" => true];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Email or Password Invalid"];
    }
}
exit("This file cannot be accessed directly");

?>