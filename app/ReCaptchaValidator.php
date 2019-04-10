<?php


namespace App;

use GuzzleHttp\Client;

class ReCaptchaValidator
{
    public static function isValid($response)
    {
        // Validate ReCaptcha
        $client = new Client([
            'base_uri' => 'https://google.com/recaptcha/api/'
        ]);

        $response = $client->post('siteverify', [
            'query' => [
                'secret' => config('formgate.recaptcha.secret_key'),
                'response' => $response
            ]
        ]);

        return json_decode($response->getBody())->success;
    }
}
