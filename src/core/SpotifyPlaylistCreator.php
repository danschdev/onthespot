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

    public function createPlaylist(string $name, string $description, bool $public = false, string $userId = ''): array
    {
        if (empty($userId)) {
            throw new InvalidArgumentException('User ID cannot be empty.');
        }

        $response = $this->client->request(
            'POST',
            "https://api.spotify.com/v1/users/{$userId}/playlists",
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $name,
                    'description' => $description,
                    'public' => $public,
                ],
            ]
        );

        return json_decode($response->getBody()->__toString(), true);
    }
}
