<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\GoogleApiIdentityClient;
use App\Verification;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;

class PhoneController extends Controller
{
    protected $client;

    public function __construct(GoogleApiIdentityClient $client)
    {
        $this->client = $client;
    }

    public function sendVerificationCode(Request $request) {
        try {
            $data = $request->all();
            $response = $this->client->sendVerificationCode($data["phoneNumber"], $data["recapchaToken"]);
            $body = json_decode($response->getBody(),true);
            $verification = Verification::where([
                'phone' => $data["phoneNumber"],
            ])->first();
            if ($verification) {
                $verification->session_info = $body["sessionInfo"];
                $verification->save();
            } else {
                $verification = Verification::create([
                    'phone' => $data["phoneNumber"],
                    'session_info' => $body["sessionInfo"],
                ]);
            }
            return $verification;
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 400 && array_key_exists("error", json_decode($e->getResponse()->getBody(),true))) {
                switch (json_decode($e->getResponse()->getBody(),true)["error"]["message"]) {
                    case "INVALID_PHONE_NUMBER : Invalid format.":
                        return "INVALID_PHONE_NUMBER : Invalid format.";
                        break;  
                    default:
                        return json_decode($e->getResponse()->getBody(),true);
                  }
            }
        }
        catch (\Exception $e) {
            return "error: " .$e->getMessage();
        }
    }

    public function verifyPhoneNumber(Request $request) {
        try {
            $data = $request->all();

            $verification = Verification::where('phone', $data["phoneNumber"])->first();
            if ($verification) {
                $response = $this->client->verifyPhoneNumber($data["code"], $verification["session_info"]);
                $body = json_decode($response->getBody(),true);
                $verification->verify = true;
                $verification->save();
                return $body;
            }
            return "Not Found";
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 400 && array_key_exists("error", json_decode($e->getResponse()->getBody(),true))) {
                switch (json_decode($e->getResponse()->getBody(),true)["error"]["message"]) {
                    case "SESSION_EXPIRED":
                        return "expired";
                        break;  
                    case "INVALID_CODE":
                        return "INVALID_CODE";
                        break;  
                    default:
                        return json_decode($e->getResponse()->getBody(),true);
                  }
            }
        }
        catch (\Exception $e) {
            return "error: " .$e->getMessage();
        }
    }
}
