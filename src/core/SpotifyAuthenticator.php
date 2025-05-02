<?php

declare(strict_types=1);
use GuzzleHttp\Client;

require_once __DIR__.'/../database/DatabaseConnection.php';

require_once __DIR__.'/../domain/PdoSpotifyRepository.php';

class SpotifyAuthenticator
{
    private Client $client;
    private ?PdoSpotifyRepository $spotifyRepository;

    public function __construct(Client $client, ?PdoSpotifyRepository $spotifyRepository = null)
    {
        $this->client = $client;
        $this->spotifyRepository = $spotifyRepository;
    }

    public function createAccesstoken(): string
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
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
        $token = $body['access_token'];

        if ($this->spotifyRepository) {
            $this->spotifyRepository->saveAccessToken($token, new DateTime('1 hour'));
        }

        return $body['access_token'];
    }
}
