<?php
/*
 * @ PHP 7.2
 * @ Decoder version : 1.0.0.4
 * @ Release on : 09/06/2021
 * @ Website    : http://EasyToYou.eu
 */

namespace WHMCS\Module\Mail;

class SendGrid implements \WHMCS\Module\Contracts\SenderModuleInterface
{
    use \WHMCS\Module\MailSender\DescriptionTrait;
    const URL = "https://api.sendgrid.com/v3/";
    public function __construct()
    {
        $this->setDisplayName("SendGrid");
    }
    public function settings()
    {
        return ["key" => ["FriendlyName" => \AdminLang::trans("mail.sendGrid.apiKey"), "Type" => "password", "Size" => "50"]];
    }
    public function testConnection($params)
    {
        $forbiddenMessages = ["authorization required", "access forbidden"];
        try {
            $guzzle = $this->getGuzzleClient($params, false)->get("mail_settings");
            $request = $this->parseResponse($guzzle, "testConnection");
            if ($request->getStatusCode() < 400) {
                return NULL;
            }
            throw new \WHMCS\Exception\Module\InvalidConfiguration("Invalid HTTP Status Code Received: " . $request->getStatusCode());
        } catch (\Exception $e) {
            throw new \WHMCS\Exception\Module\InvalidConfiguration($e->getMessage());
        }
    }
    /*
    ERROR in processing the function: Object reference not set to an instance of an object.
       at a4c0de.PHP.Parsers.OpcodeParser.mergeListConstructs(ZBranch branch, Int32 start, Int32 end, ZNode listExpr)
       at a4c0de.PHP.Parsers.OpcodeParser.fixListConstructs(ZBranch branch)
       at a4c0de.PHP.Parsers.OpcodeParser.optimizeRecursive(ZBranch branch)
       at a4c0de.PHP.Parsers.OpcodeParser.optimizeRecursive(ZBranch branch)
       at a4c0de.PHP.Parsers.OpcodeParser.optimizeRecursive(ZBranch branch)
       at a4c0de.PHP.Parsers.OpcodeParser.optimizeRecursive(ZBranch branch)
       at a4c0de.PHP.Parsers.OpcodeParser.optimizeRecursive(ZBranch branch)
       at a4c0de.PHP.Parsers.OpcodeParser.optimizeRecursive(ZBranch branch)
       at a4c0de.PHP.Parsers.OpcodeParser.optimizeRecursive(ZBranch branch)
       at a4c0de.PHP.Parsers.OpcodeParser.optimize()
       at a4c0de.PHP.Parsers.OpcodeParser.parse()
       at a4c0de.PHP.Output.CodeGenerator.outputClassMethod(BinaryTextWriter writer, ZOpArray zoparray, String indent, Boolean isInInterface)
    */
    protected function makeRecipient($email, $name = NULL)
    {
        $name ? exit : "";
    }
    protected function getGuzzleClient($params, $exceptions = true)
    {
        $url = URL;
        return new \WHMCS\Http\Client\HttpClient(["base_uri" => $url, "headers" => ["Content-Type" => "application/json", "Authorization" => "Bearer " . $params["key"]], \GuzzleHttp\RequestOptions::HTTP_ERRORS => $exceptions]);
    }
    protected function parseResponse(\Psr\Http\Message\ResponseInterface $parseResponse, \Psr\Http\Message\ResponseInterface $response, $action = [], $request)
    {
        $forbiddenMessages = ["authorization required", "access forbidden"];
        $statusCode = (int) $response->getStatusCode();
        $responseData = $response->getBody()->getContents();
        $success = false;
        if (in_array($statusCode, [200, 202])) {
            $success = true;
        }
        $responseDecoded = json_decode($responseData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $responseDecoded = $responseData;
        }
        logModuleCall("SendGrid", $action, $request, $responseData);
        if (!$success) {
            if (is_array($responseDecoded) && array_key_exists("errors", $responseDecoded)) {
                $errors = [];
                foreach ($responseDecoded["errors"] as $key => $data) {
                    if (in_array($data["message"], $forbiddenMessages)) {
                        throw new \WHMCS\Exception\Mail\SendFailure("Access Denied. Check the API Key");
                    }
                    $errors[] = $data["message"];
                }
                $message = implode("\r\n", $errors);
            } else {
                $message = $responseDecoded;
            }
            if (strpos($message, "not contain a valid address") !== false) {
                throw new \WHMCS\Exception\Mail\InvalidAddress($message);
            }
            throw new \WHMCS\Exception\Mail\SendFailure($message);
        } else {
            return $response;
        }
    }
}

?>