<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

while (!defined("WHMCS")) {
    $payMethodId = (int) App::getFromRequest("paymethodid");
    $clientId = (int) App::getFromRequest("clientid");
    $default = (int) App::getFromRequest("set_as_default");
    if (!$clientId) {
        $apiresults = ["result" => "error", "message" => "Client ID is Required"];
        return NULL;
    }
    if (!$payMethodId) {
        $apiresults = ["result" => "error", "message" => "Pay Method ID is Required"];
        return NULL;
    }
    try {
        $payMethod = WHMCS\Payment\PayMethod\Model::findOrFail($payMethodId);
        if ($payMethod->userid != $clientId) {
            $apiresults = ["result" => "error", "message" => "Pay Method does not belong to passed Client ID"];
            return NULL;
        }
        $payment = $payMethod->payment;
        if ($payment->isBankAccount() && $payment->isRemoteBankAccount() || $payment->isCreditCard() && !$payment->isManageable()) {
            $apiresults = ["result" => "error", "message" => "Unsupported Gateway Type for Update"];
            return NULL;
        }
        if ($payment->isCreditCard()) {
            if ($payment->isRemoteCreditCard()) {
                $workFlowType = $payMethod->getGateway()->getWorkflowType();
                switch ($workFlowType) {
                    case WHMCS\Module\Gateway::WORKFLOW_MERCHANT:
                        if (App::isInRequest("card_number")) {
                            $payment->setCardNumber(App::getFromRequest("card_number"));
                        }
                        if (App::isInRequest("card_expiry")) {
                            $expiryDate = App::getFromRequest("card_expiry");
                            try {
                                $expiryDate = WHMCS\Carbon::createFromCcInput($expiryDate);
                                $payment->setExpiryDate($expiryDate);
                            } catch (Exception $e) {
                                $apiresults = ["result" => "error", "message" => "Expiry Date is invalid"];
                                return NULL;
                            }
                        }
                        if (App::isInRequest("card_start")) {
                            $startDate = App::getFromRequest("card_start");
                            try {
                                $startDate = WHMCS\Carbon::createFromCcInput($startDate);
                                $payment->setStartDate($startDate);
                            } catch (Exception $e) {
                                $apiresults = ["result" => "error", "message" => "Start Date is invalid"];
                                return NULL;
                            }
                        }
                        if (App::isInRequest("card_issue_number")) {
                            $issueNumber = App::getFromRequest("card_issue_number");
                            if ($issueNumber && !is_numeric($issueNumber)) {
                                $apiresults = ["result" => "error", "message" => "Issue Number is invalid"];
                                return NULL;
                            }
                            $payment->setIssueNumber($issueNumber);
                        }
                        $payment->save();
                        break;
                    case WHMCS\Module\Gateway::WORKFLOW_ASSISTED:
                    case WHMCS\Module\Gateway::WORKFLOW_TOKEN:
                        if ($workFlowType == WHMCS\Module\Gateway::WORKFLOW_ASSISTED && App::isInRequest("card_number")) {
                            $apiresults = ["result" => "error", "message" => "Unable to Update Card Number for Assisted Gateway"];
                            return NULL;
                        }
                        if (App::isInRequest("card_number")) {
                            $payment->setCardNumber(App::getFromRequest("card_number"));
                        }
                        if (App::isInRequest("card_expiry")) {
                            $expiryDate = App::getFromRequest("card_expiry");
                            try {
                                $expiryDate = WHMCS\Carbon::createFromCcInput($expiryDate);
                                $payment->setExpiryDate($expiryDate);
                            } catch (Exception $e) {
                                $apiresults = ["result" => "error", "message" => "Expiry Date is invalid"];
                                return NULL;
                            }
                        }
                        if (App::isInRequest("card_start")) {
                            $startDate = App::getFromRequest("card_start");
                            try {
                                $startDate = WHMCS\Carbon::createFromCcInput($startDate);
                                $payment->setStartDate($startDate);
                            } catch (Exception $e) {
                                $apiresults = ["result" => "error", "message" => "Start Date is invalid"];
                                return NULL;
                            }
                        }
                        if (App::isInRequest("card_issue_number")) {
                            $issueNumber = App::getFromRequest("card_issue_number");
                            if ($issueNumber && !is_numeric($issueNumber)) {
                                $apiresults = ["result" => "error", "message" => "Issue Number is invalid"];
                                return NULL;
                            }
                            $payment->setIssueNumber($issueNumber);
                        }
                        try {
                            $payment->updateRemote();
                        } catch (Exception $e) {
                            $apiresults = ["result" => "error", "message" => "Error Updating Remote Pay Method: " . $e->getMessage()];
                            return NULL;
                        }
                        break;
                    case WHMCS\Module\Gateway::WORKFLOW_NOLOCALCARDINPUT:
                    case WHMCS\Module\Gateway::WORKFLOW_REMOTE:
                    default:
                        $apiresults = ["result" => "error", "message" => "Unsupported Gateway Type for Update"];
                        return NULL;
                }
            } else {
                if ($payment->isLocalCreditCard()) {
                    if (App::isInRequest("card_number")) {
                        $payment->setCardNumber(App::getFromRequest("card_number"));
                    }
                    if (App::isInRequest("card_expiry")) {
                        $expiryDate = App::getFromRequest("card_expiry");
                        try {
                            $expiryDate = WHMCS\Carbon::createFromCcInput($expiryDate);
                            $payment->setExpiryDate($expiryDate);
                        } catch (Exception $e) {
                            $apiresults = ["result" => "error", "message" => "Expiry Date is invalid"];
                            return NULL;
                        }
                    }
                    if (App::isInRequest("card_start")) {
                        $startDate = App::getFromRequest("card_start");
                        try {
                            $startDate = WHMCS\Carbon::createFromCcInput($startDate);
                            $payment->setStartDate($startDate);
                        } catch (Exception $e) {
                            $apiresults = ["result" => "error", "message" => "Start Date is invalid"];
                            return NULL;
                        }
                    }
                    if (App::isInRequest("card_issue_number")) {
                        $issueNumber = App::getFromRequest("card_issue_number");
                        if ($issueNumber && !is_numeric($issueNumber)) {
                            $apiresults = ["result" => "error", "message" => "Issue Number is invalid"];
                            return NULL;
                        }
                        $payment->setIssueNumber($issueNumber);
                    }
                    $payment->save();
                }
            }
        } else {
            $bankName = App::getFromRequest("bank_name");
            $accountType = App::getFromRequest("bank_account_type");
            $bankCode = App::getFromRequest("bank_code");
            $bankAccount = App::getFromRequest("bank_account");
            if ($bankName) {
                $payment->setBankName($bankName);
            }
            if ($accountType) {
                $payment->setAccountType($accountType);
            }
            if ($bankCode) {
                $payment->setRoutingNumber($bankCode);
            }
            if ($bankAccount) {
                $payment->setAccountNumber($bankAccount);
            }
            $payment->save();
        }
        if ($default) {
            $payMethod->setAsDefaultPayMethod()->save();
        }
        $apiresults = ["result" => "success", "paymethodid" => $payMethod->id];
    } catch (Exception $e) {
        $apiresults = ["result" => "error", "message" => ""];
        return NULL;
    }
}
exit("This file cannot be accessed directly");

?>