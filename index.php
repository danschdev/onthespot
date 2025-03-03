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

var_dump($song->name);
var_dump($song->explicit);
echo "<br/>";

$response = $client->request('GET', 
    'https://api.spotify.com/v1/artists/'.$song->album->artists[0]->id,
    [
        'headers' => [
            'Authorization' => 'Bearer ' . $accesstoken
        ]
        ]);

$artist = json_decode($response->getBody());

foreach($artist->genres as $genre) {
    var_dump($genre);
}  

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