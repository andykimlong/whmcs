<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $userId = (int) App::getFromRequest("user_id");
    $firstname = trim(App::getFromRequest("firstname"));
    $lastname = trim(App::getFromRequest("lastname"));
    $email = trim(App::getFromRequest("email"));
    $language = trim(App::getFromRequest("language"));
    try {
        $user = WHMCS\User\User::findOrFail($userId);
        if (!$email && !$firstname && !$lastname && !$language) {
            $apiresults = ["result" => "error", "message" => "One of `email`, `firstname`, `lastname`, or `language` is required"];
            return NULL;
        }
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $apiresults = ["result" => "error", "message" => "The email address entered is not valid"];
            return NULL;
        }
        if ($email && 0 < WHMCS\User\User::where("email", $email)->where("id", "!=", $userId)->count()) {
            $apiresults = ["result" => "error", "message" => "A user already exists with that email address"];
            return NULL;
        }
        $oldUserDetails = $user->getDetails();
        if ($firstname) {
            $user->first_name = $firstname;
        }
        if ($lastname) {
            $user->last_name = $lastname;
        }
        if ($language) {
            $user->language = $language;
        }
        if ($email) {
            $user->email = $email;
        }
        if ($user->isDirty()) {
            $user->save();
        }
        if ($email && $user->isEmailVerificationEnabled()) {
            $user->invalidateEmailVerification();
            $user->sendEmailVerification();
        }
        run_hook("UserEdit", array_merge($user->getDetails(), ["olddata" => $oldUserDetails]));
        $apiresults = ["result" => "success", "user_id" => $user->id];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => "Invalid User ID requested"];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>