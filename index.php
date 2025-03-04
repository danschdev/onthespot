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

$offset = 0;

$response =  null;
$artists = [];

do {

    $response = $client->request('GET',
    'https://api.spotify.com/v1/playlists/'
        .'5b6HY4TAenULF8SHFdw2nn'
        ."/tracks?offset=$offset&limit=100",
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $accesstoken
            ]
        ]
    );

    $songs = json_decode($response->getBody());

    $offset += 100;

} while ( 0 == sizeof($songs->items) || 99 <= sizeof($songs->items)) ;

$artistids = [];
foreach($songs->items as $plitem) {
    /*
    echo $plitem->track->name;
    echo " - ";
    echo $plitem->track->artists[0]->name;
    echo " - ";
    echo $plitem->track->artists[0]->id;
    echo "<br/>";
    */
    if (!in_array( $plitem->track->artists[0]->id, $artistids)){
        $artistids[] = $plitem->track->artists[0]->id;
    }

}

$uri = "https://api.spotify.com/v1/artists?ids=".implode(',', $artistids);


// echo $uri;

$response = $client->request('GET', 
'https://api.spotify.com/v1/artists?ids='.implode(',', $artistids),
[
    'headers' => [
        'Authorization' => 'Bearer ' . $accesstoken,
    ]
    ]);

$body = json_decode($response->getBody());

/*
$genres = implode(', ', $artist->genres);
$artists[$plitem->track->artists[0]->id] = [$plitem->track->artists[0]->name, $genres];
/*
    echo($artists[$plitem->track->artists[0]->id][1]);

echo "<br/>";

*/


echo "<table style=\"width:100%\">";
foreach($body->artists as $artist) {
    echo "<tr><td>".$artist->name."</td><td>";
    foreach($artist->genres as $genre) {
        echo "<td>$genre</td>";
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