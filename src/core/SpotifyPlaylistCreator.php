<?php

declare(strict_types=1);

use GuzzleHttp\Client;

class SpotifyPlaylistCreator
{
    private Client $client;
    private string $accessToken;

    public function __construct(Client $client, string $accessToken)
    {
        $this->client = $client;
        $this->accessToken = $accessToken;
    }
}
