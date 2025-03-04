<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

$accesstoken = accesstoken();

$client = new Client();
$headers = [
    'Authorization' => 'Bearer ' . $accesstoken
];

$response = $client->request('GET', 
    'https://api.spotify.com/v1/tracks/69VKmxxZrMxIkccXvMNMhT',
    [
        'headers' => [
            'Authorization' => 'Bearer ' . $accesstoken
        ]
        ]);

$song = json_decode($response->getBody());
/*
var_dump($song->name);
var_dump($song->explicit);
echo "<br/>";
*/
$response = $client->request('GET', 
    'https://api.spotify.com/v1/artists/'.$song->album->artists[0]->id,
    [
        'headers' => [
            'Authorization' => 'Bearer ' . $accesstoken
        ]
        ]);

$artist = json_decode($response->getBody());

$response = $client->request('GET',
    'https://api.spotify.com/v1/playlists/'
        .'5b6HY4TAenULF8SHFdw2nn'
        .'/tracks?offset=0&limit=100',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $accesstoken
            ]
        ]
    );

    $songs = json_decode($response->getBody());
    $artists = [];
    foreach($songs->items as $plitem) {
        echo $plitem->track->name;
        echo " - ";
        echo $plitem->track->artists[0]->name;
        echo " - ";
        echo $plitem->track->artists[0]->id;
        echo "<br/>";

        if (!array_key_exists($plitem->track->artists[0]->id, $artists)){

            $response = $client->request('GET', 
            'https://api.spotify.com/v1/artists/'.$plitem->track->artists[0]->id,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accesstoken
                ]
                ]);
        
            $artist = json_decode($response->getBody());
        

            $genres = implode(', ', $artist->genres);
            $artists[$plitem->track->artists[0]->id] = [$plitem->track->artists[0]->name, $genres];
        }
            // var_dump($plitem->track->artists[0]);

            echo($artists[$plitem->track->artists[0]->id][1]);

        echo "<br/>";
    }
    var_dump($artists);

function accesstoken(): string {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
    $clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
    
    $client = new Client();
    $response = $client->post('https://accounts.spotify.com/api/token', [
        'form_params' => [
            'grant_type' => 'client_credentials'
        ],
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($clientId.":".$clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]
    ]);
    
    $body = json_decode($response->getBody(), true);
    return $body['access_token'];
}


?>