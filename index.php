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
/*
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
*/
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
    echo "<tr>";
        echo "<td>";
            if ($item->track->explicit) { echo "&#x26A0; ";};
            echo($item->track->name);
        echo "</td>";
        echo "<td>";
            echo($item->track->artists[0]->name);
        echo "</td>";
        echo "<td><img src='";
            echo($item->track->album->images[2]->url);
        echo "'></td>";
        echo "<td>";
            echo($item->track->album->name);
        echo "</td>";
        foreach (array_slice($item->track->artists, 1) as $artist) {
            echo("<td>$artist->name</td>");
        }
    echo "</tr>";
}
echo "</table>";

// Genres should be fetched later with an approach safer to Too Many Requests
/*
$artistids = [];
$counter = 0;
$batch = -1;
define("BATCHSIZE", 10);

foreach($trackitems as $plitem) {
    if ($counter % BATCHSIZE == 0) {
        $batch++;
        $artistids[$batch] = [];
    }
    if (!in_array( $plitem->track->artists[0]->id, $artistids[$batch])){
        $artistids[$batch][] = $plitem->track->artists[0]->id;
    }

    $counter++; 
}



echo "<table style=\"width:100%; border-width=1px\">";
for ($i = 0; $i <= $batch; $i++ ) {

    $uri = "https://api.spotify.com/v1/artists?ids=".implode(',', $artistids[$batch]);

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
}
echo "</table>";    

*/
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