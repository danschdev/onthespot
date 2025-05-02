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

    /**
     * @return array{
     *     tracks: list<mixed>,
     *     artists: array<string, array{name: string, count: int, genres: array<string>}>
     * }
     */
    public function fetchTracks(string $playlistId): array
    {
        $offset = 0;
        $data = ['artists' => [], 'tracks' => [], 'genres' => []];
        $artistIds = [];
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
                        $artistIds[] = $artist->id;
                        $data['artists'][$artist->id] = [];
                        $data['artists'][$artist->id]['name'] = $artist->name;
                        $data['artists'][$artist->id]['count'] = 1;
                    }
                    if (20 === count($artistIds) || 99 <= count($songs->items)) {
                        $data['genres'][] = $this->fetchGenres($artistIds, $data);
                        $artistIds = [];
                    }
                }
            }
        } while (0 === count($songs->items) || 99 <= count($songs->items)); // Delete first condition? Loop will be run >= 1 time anyway. Test with exactly 100 tracks!
        print_r($data['artists']);
        echo '<br/>';

        return $data;
    }

    // TODO: Call this after whole playlist is there
    /**
     * @param array<string> $artistIds
     * @param mixed         &$data
     *
     * @return array<string>
     */
    public function fetchGenres(array $artistIds, &$data): array
    {
        $url = 'https://api.spotify.com/v1/artists?ids='
        .implode(',', $artistIds);
        $response = $this->client->request(
            'GET',
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->accessToken,
                ],
            ]
        );

        $artistData = json_decode($response->getBody()->__toString());
        $genres = [];
        foreach ($artistData->artists as $artist) {
            $data['artists'][$artist->id]['genres'] = $artist->genres;
            foreach ($artist->genres as $genre) {
                $genres[$genre] = $genre;
            }
        }

        return $genres;
    }
}
