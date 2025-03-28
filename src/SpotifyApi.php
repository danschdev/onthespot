<?php

declare(strict_types=1);
use GuzzleHttp\Client;

class SpotifyApi
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function createAccesstoken(): string
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'\..');
        $dotenv->load();

        $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
        $clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];

        $response = $this->client->post('https://accounts.spotify.com/api/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
            'headers' => [
                'Authorization' => 'Basic '.base64_encode($clientId.':'.$clientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $body = json_decode($response->getBody()->__toString(), true);

        return $body['access_token'];
    }
}
