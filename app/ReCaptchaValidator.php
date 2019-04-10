<?php


namespace App;

use GuzzleHttp\Client;

class ReCaptchaValidator
{
    /**
     * Check the response with Google Recaptcha
     * to return whether the recaptcha was completed
     * successfully or not.
     *
     * @param $response
     * @return boolean
     */
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
