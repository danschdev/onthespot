<?php

declare(strict_types=1);

use GuzzleHttp\Client;

class SpotifyPlaylistFetcher
{
    private Client $client;
    private string $accessToken;

    public function __construct(Client $client, string $accessToken)
    {
        $this->client = $client;
        $this->accessToken = $accessToken;
    }

    public function fetchTracks(string $playlistId)
    {
        $offset = 0;
        $data = ['artists' => [], 'tracks' => []];
        do {
            $response = $this->client->request(
                'GET',
                'https://api.spotify.com/v1/playlists/'
                .$playlistId
                ."/tracks?offset={$offset}&limit=100",
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->accessToken,
                    ],
                ]
            );

            $songs = json_decode($response->getBody()->__toString());

            $offset += 100;

            foreach ($songs->items as $item) {
                $data['tracks'][] = $item;
                foreach ($item->track->artists as $artist) {
                    if (array_key_exists($artist->id, $data['artists']) && array_key_exists('count', $data['artists'][$artist->id])) {
                        ++$data['artists'][$artist->id]['count'];
                        $data['artists'][$artist->id]['name'] = $artist->name;
                    } else {
                        $data['artists'][$artist->id] = [];
                        $data['artists'][$artist->id]['name'] = $artist->name;
                        $data['artists'][$artist->id]['count'] = 1;
                    }
                }
            }
        } while (0 === count($songs->items) || 99 <= count($songs->items)); // Delete first condition? Loop will be run >= 1 time anyway. Test with exactly 100 tracks!

        return $data;
    }
}
