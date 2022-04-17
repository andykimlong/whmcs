<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $firstname = App::getFromRequest("firstname");
    $lastname = App::getFromRequest("lastname");
    $email = App::getFromRequest("email");
    $password2 = App::getFromRequest("password2");
    $language = App::getFromRequest("language");
    if (!$firstname) {
        $apiresults = ["result" => "error", "message" => "You did not enter a first name"];
        return NULL;
    }
    if (!$lastname) {
        $apiresults = ["result" => "error", "message" => "You did not enter a last name"];
        return NULL;
    }
    if (!$email) {
        $apiresults = ["result" => "error", "message" => "You did not enter an email address"];
        return NULL;
    }
    if (!$password2) {
        $apiresults = ["result" => "error", "message" => "You did not enter a password"];
        return NULL;
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $apiresults = ["result" => "error", "message" => "The email address entered is not valid"];
        return NULL;
    }
    try {
        $user = WHMCS\User\User::createUser($firstname, $lastname, $email, WHMCS\Input\Sanitize::decode($password2), $language);
        $apiresults = ["result" => "success", "user_id" => $user->id];
    } catch (WHMCS\Exception\User\EmailAlreadyExists $e) {
        $apiresults = ["result" => "error", "message" => "A user already exists with that email address"];
        return NULL;
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => $e->getMessage()];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>