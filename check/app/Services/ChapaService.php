<?php

namespace App\Services;

use GuzzleHttp\Client;

use GuzzleHttp\Exception\RequestException;

class ChapaService 

{
    protected $client;
    protected $baseUri;
    protected $secretKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUri = config('chapa.base_uri');
        $this->secretKey = config('chapa.secret_key');
    }


    public function initializePayment($data)
    {
        try {
            $response = $this->client->post($this->baseUri . 'transaction/initialize', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody()->getContents(), true);
            }

            return ['error' => 'Something went wrooooooooong'];
        }
    }

    public function verifyPayment($transactionId)
    {
        try {
            $response = $this->client->get($this->baseUri . 'transaction/verify/' . $transactionId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody()->getContents(), true);
            }

            return ['error' => 'Something went wrong'];
        }
    }
}
