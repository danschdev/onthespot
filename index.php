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
$trackitems = [];
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

    foreach($songs->items as $item) {
        $trackitems[] = $item;
    }
    

} while ( 0 == sizeof($songs->items) || 99 <= sizeof($songs->items)) ;

$artistids = [];
$counter = 0;
$batch = -1;

foreach($trackitems as $plitem) {
    /*
    echo $plitem->track->name;
    echo " - ";
    echo $plitem->track->artists[0]->name;
    echo " - ";
    echo $plitem->track->artists[0]->id;
    echo "<br/>";
    */

    if ($counter % 10 == 0) {
        $batch++;
        $artistids[$batch] = [];
    }
    if (!in_array( $plitem->track->artists[0]->id, $artistids[$batch])){
        $artistids[$batch][] = $plitem->track->artists[0]->id;
    }

    $counter++; 
}

for ($i = 0; $i <= $batch; $i++ ) {

$uri = "https://api.spotify.com/v1/artists?ids=".implode(',', $artistids[$batch]);

echo "<table style=\"width:100%; border-width=1px\">";
for ($i = 0; $i <= $batch; $i++) {

    $response = $client->request('GET', 
    'https://api.spotify.com/v1/artists?ids='.implode(',', $artistids[$i]),
    [
        'headers' => [
            'Authorization' => 'Bearer ' . $accesstoken,
        ]
        ]);

    $body = json_decode($response->getBody());
    foreach($body->artists as $artist) {
        echo "<tr><td>".$artist->name."</td>";
        foreach($artist->genres as $genre) {
            echo "<td>$genre</td>";
        }
        echo "</tr>";
    }
}
echo "</table>";    
}
/*
$genres = implode(', ', $artist->genres);
$artists[$plitem->track->artists[0]->id] = [$plitem->track->artists[0]->name, $genres];
/*
    echo($artists[$plitem->track->artists[0]->id][1]);

echo "<br/>";

*/





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