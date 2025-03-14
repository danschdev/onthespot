<?php
require 'vendor/autoload.php';
require 'database.php';
use GuzzleHttp\Client;

$client = new Client();
$accesstoken = accesstoken($client);

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
        foreach($item->track->artists as $artist) {
            if (key_exists($artist->id, $artists) && key_exists("count", $artists[$artist->id])) {
                $artists[$artist->id]["count"] += 1;
                $artists[$artist->id]["name"] = $artist->name;
            } else {
                $artists[$artist->id] = [];
                $artists[$artist->id]["name"] = $artist->name;
                $artists[$artist->id]["count"] = 1;
            }
        }
    }
} while ( 0 == sizeof($songs->items) || 99 <= sizeof($songs->items)) ; // Delete first condition? Loop will be run >= 1 time anyway. Test with exactly 100 tracks!

uasort($artists, function($a, $b) {
    return $a["count"] < $b["count"] ? 1 : -1;
});

foreach($artists as $artist) {
    echo $artist["name"].": ".$artist["count"];
    echo "<br/>";
}

echo "<table style='borderwidth: 2px borderstyle: solid'>\n";
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
    echo "</tr>\n";
}
echo "</table>";

function accesstoken(Client $client): string {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $clientId = $_ENV['SPOTIFY_CLIENT_ID'];
    $clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
    
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