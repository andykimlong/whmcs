<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Module\Gateway\GoCardless\Resources;

class Mandates extends AbstractResource
{
    const STATUSES = ["pending_customer_approval" => "Pending Customer Approval", "pending_submission" => "Pending Submission", "submitted" => "Submitted", "active" => "Active", "failed" => "Failed", "cancelled" => "Cancelled", "expired" => "Expired"];
    public function cancelled($event)
    {
        $mandateId = $event["links"]["mandate"];
        $client = $this->getClientFromMandate($mandateId);
        if ($client) {
            $payMethod = $client->payMethods()->where("gateway_name", "gocardless")->first();
            if ($payMethod && $payMethod->payment->isRemoteBankAccount() && $payMethod->payment->getRemoteToken() == $mandateId) {
                $payMethod->delete();
            }
        }
    }
    public function created($event)
    {
        $mandateId = $event["links"]["mandate"];
        $client = $this->getClientFromMandate($mandateId);
        if (!$client) {
            logTransaction($this->params["paymentmethod"], $event, "No Client Found for Mandate", $this->params);
        } else {
            $payMethod = $client->payMethods()->where("gateway_name", "gocardless")->first();
            if (!$payMethod) {
                $payMethod = \WHMCS\Payment\PayMethod\Adapter\RemoteBankAccount::factoryPayMethod($client, $client);
            }
            $payMethod->payment->setRemoteToken($mandateId);
            $payMethod->payment->save();
            try {
                $this->client->put("mandates/" . $mandateId, ["json" => ["mandates" => ["metadata" => ["client_id" => (int) (int) $client->id]]]]);
            } catch (\Exception $e) {
            }
        }
    }
    public function failed($event)
    {
        $this->cancelled($event);
    }
    public function reinstated($event)
    {
        $mandateId = $event["links"]["mandate"];
        $client = $this->getClientFromMandate($mandateId);
        try {
            $payMethod = $client->payMethods()->where("gateway_name", "gocardless")->first();
            if (!$payMethod) {
                $payMethod = \WHMCS\Payment\PayMethod\Adapter\RemoteBankAccount::factoryPayMethod($client, $client);
            }
            $payMethod->payment->setRemoteToken($mandateId);
            $payMethod->payment->save();
        } catch (\Exception $e) {
        }
    }
    public function replaced($event)
    {
        $mandateId = $event["links"]["mandate"];
        $client = $this->getClientFromMandate($mandateId);
        if ($client) {
            $newMandateId = $event["links"]["new_mandate"];
            $payMethod = $client->payMethods()->where("gateway_name", "gocardless")->first();
            if (!$payMethod) {
                $payMethod = \WHMCS\Payment\PayMethod\Adapter\RemoteBankAccount::factoryPayMethod($client, $client);
            }
            $payMethod->payment->setRemoteToken($newMandateId);
            $payMethod->payment->save();
        }
    }
    public function defaultAction($event)
    {
        logTransaction($this->params["paymentmethod"], $event, "Mandate Notification", $this->params);
    }
    protected function getClientFromMandate($mandateId)
    {
        $client = NULL;
        try {
            $response = json_decode($this->client->get("mandates/" . $mandateId), true);
            if (array_key_exists("metadata", $response) && array_key_exists("client_id", $response["metadata"]) && $response["metadata"]["client_id"]) {
                $clientId = $response["mandates"]["metadata"]["client_id"];
                $client = \WHMCS\User\Client::find($clientId);
            }
            if (!$client) {
                $customerId = $response["links"]["customer"];
                $response = json_decode($this->client->get("customers/" . $customerId), true);
                $email = $response["customers"]["email"];
                $client = \WHMCS\User\Client::where("email", $email)->first();
            }
            return $client;
        } catch (\Exception $e) {
            return $client;
        }
    }
}

?>