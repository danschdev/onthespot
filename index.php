<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

$accesstoken = accesstoken();

$client = new Client();
$headers = [
    'Authorization' => 'Bearer ' . $accesstoken
];
$offset = 0;

$response =  null;
$artists = [];
$trackitems = [];
do {

    $response = $client->request('GET',
    'https://api.spotify.com/v1/playlists/'
        .$_ENV['PLAYLIST_ID']
        ."/tracks?offset=$offset&limit=100",
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $accesstoken
            ]
        ]
    );

    $songs = json_decode($response->getBody());

    $offset += 100;

    foreach($songs->items as $item) {
        $trackitems[] = $item;
    }
    

} while ( 0 == sizeof($songs->items) || 99 <= sizeof($songs->items)) ;

echo "<table style='borderwidth: 2px borderstyle: solid'>";
foreach($trackitems as $item) {
    echo "<tr><td>";
    echo($item->track->name);
    echo "</td>";
    foreach($item->track->artists as $artist){
        echo("<td>$artist->name</td>");
    }
    echo "</tr>";
}
echo "</table>";

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