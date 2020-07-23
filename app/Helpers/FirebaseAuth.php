<?php

namespace App\Helper;

use Exception;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Firebase\Auth\Token\Exception\InvalidToken;

class FirebaseAuth
{

    /**
     * @var Firebase
     */
    protected $firebase;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('/app/key/serviceAccountKey.json'));

        $this->firebase = $factory ->createAuth();
    }

    /**
     * Verify idToken
     * @param $idToken
     * @return bool|string
     */
    public function verifyIdToken($idToken)
    {
        try {
            $verifiedIdToken  = $this->firebase->verifyIdToken($idToken);

            $phoneNumber = $verifiedIdToken->getClaim('phone_number');

            return $phoneNumber;

        } catch (\InvalidArgumentException $e) {
            echo 'The token could not be parsed: '.$e->getMessage();
            return null;
        } catch (InvalidToken $e) {
            echo 'The token is invalid: '.$e->getMessage();
            return null;
        }
    }
}