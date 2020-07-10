<?php

namespace App\Helper;

use App\Helper\HttpClient;

class GoogleApiIdentityClient
{
    const API_URL = "https://www.googleapis.com/identitytoolkit/v3";

    const ENDPOINT_SEND_CODE = "/relyingparty/sendVerificationCode";
    const ENDPOINT_VERIFY_CODE = "/relyingparty/verifyPhoneNumber";

    protected $client;

    protected $apiKey = "AIzaSyBNoomcdNC3N5WPGK1ISskHR_nAdUg4URk";

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
        $this->client->setBaseUrl(self::API_URL);
    }

    private function usesJSON()
    {
        $this->client->setHeader('headers', ['Content-Type' => 'application/json']);
    }

    public function sendVerificationCode($phoneNumber, $recaptchaToken)
    {
        $params = array(
            'phoneNumber' => $phoneNumber,
            'recaptchaToken' => $recaptchaToken
        );

        return $this->sendVerificationCodeCustom($params);
    }

    public function sendVerificationCodeCustom($parameters = [])
    {
        $this->client->addHeaders([]);
        $this->usesJSON();

        $this->client->setHeader('body', json_encode($parameters));
        $this->client->setHeader('verify', false);

        $endpoint = self::ENDPOINT_SEND_CODE . '?key='.$this->apiKey;

        return $this->client->post($endpoint);
    }

    public function verifyPhoneNumber($code, $sessionInfo)
    {
        $params = array(
            'code' => $code,
            'sessionInfo' => $sessionInfo
        );

        return $this->verifyPhoneNumberCustom($params);
    }

    public function verifyPhoneNumberCustom($parameters = [])
    {
        $this->client->addHeaders([]);
        $this->usesJSON();

        $this->client->setHeader('body', json_encode($parameters));
        $this->client->setHeader('verify', false);

        $endpoint = self::ENDPOINT_VERIFY_CODE . '?key='.$this->apiKey;

        return $this->client->post($endpoint);
    }
}
