<?php

namespace App\Helper;

use App\Helper\HttpClient;

class GoogleApiIdentityClientCurl
{
    const API_URL = "https://www.googleapis.com/identitytoolkit/v3";

    const ENDPOINT_SEND_CODE = "/relyingparty/sendVerificationCode";
    const ENDPOINT_VERIFY_CODE = "/relyingparty/verifyPhoneNumber";

    protected $apiKey = "AIzaSyBNoomcdNC3N5WPGK1ISskHR_nAdUg4URk";



    public function sendVerificationCode($phoneNumber, $recaptchaToken)
    {
        
        $url = self::API_URL . self::ENDPOINT_SEND_CODE . '?key='.$this->apiKey;

        $headers = array('Accept: application/json', 'Content-type: application/json');

        $json = json_encode(array('phoneNumber' => $phoneNumber, 'recaptchaToken' => $recaptchaToken));
        
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);
        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
        }
        return json_decode($result, true);
    }

    public function verifyPhoneNumber($code, $sessionInfo)
    {
        
        $url = self::API_URL . self::ENDPOINT_VERIFY_CODE . '?key='.$this->apiKey;

        $headers = array('Accept: application/json', 'Content-type: application/json');

        $json = json_encode(array('code' => $code, 'sessionInfo' => $sessionInfo));
        
        $http = curl_init($url);
        curl_setopt($http, CURLOPT_HEADER, false);
        curl_setopt($http, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($http, CURLOPT_POSTFIELDS, $json);
        curl_setopt($http, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($http, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($http, CURLOPT_VERBOSE, 0);
        curl_setopt($http, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($http);
        if (curl_errno($http)) {
            return null;
        } else {
            curl_close($http);
        }
        return json_decode($result, true);
    }
}
