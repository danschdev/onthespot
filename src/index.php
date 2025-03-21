<?php

require '../vendor/autoload.php';
require 'connections.php';
use GuzzleHttp\Client;

$client = new Client();
$accesstoken = accesstoken($client);

try {
    $db = getDatabaseConnection();
} catch (RuntimeException $e) {
    echo 'Fehler: ' . $e->getMessage();
    $db = null;
}

$headers = [
    'Authorization' => 'Bearer ' . $accesstoken
];
$offset = 0;

$response =  null;
$artists = [];
$trackitems = [];
do {

    $response = $client->request(
        'GET',
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

    foreach ($songs->items as $item) {
        $trackitems[] = $item;
        foreach ($item->track->artists as $artist) {
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
} while (0 == sizeof($songs->items) || 99 <= sizeof($songs->items)) ; // Delete first condition? Loop will be run >= 1 time anyway. Test with exactly 100 tracks!

uasort($artists, function ($a, $b) {
    return $a["count"] < $b["count"] ? 1 : -1;
});

foreach ($artists as $key => $artist) {
    echo $artist["name"].": ".$artist["count"];
    echo "<br/>";
    $sql = "INSERT INTO artists
        (ID, name)
        VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name);";
    $stmt = $db->prepare($sql);
    $stmt->execute([$key, $artist["name"]]);
}
