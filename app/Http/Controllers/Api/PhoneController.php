<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\GoogleApiIdentityClient;
use App\Verification;

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
            $verification = Verification::create([
                'phone' => $data["phoneNumber"],
                'session_info' => $body["sessionInfo"],
            ]);
            return $verification;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function verifyPhoneNumber(Request $request) {
        try {
            $data = $request->all();

            $verification = Verification::where('phone', $data["phoneNumber"])->first();
            if ($verification) {
                $response = $this->client->verifyPhoneNumber($data["code"], $verification["session_info"]);
                $body = $response->getBody();
                $verification->verify = true;
                $verification->save();
                return $body;
            }

            return "Not Found";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
