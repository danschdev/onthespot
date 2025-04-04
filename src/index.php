<?php

declare(strict_types=1);

require '../vendor/autoload.php';

require 'database.php';

require 'spotifyApi.php';

use GuzzleHttp\Client;

$client = new Client();
$accesstoken = accesstoken($client);

try {
    $db = getDatabaseConnection();
} catch (RuntimeException $e) {
    echo 'Fehler: '.$e->getMessage();
    $db = null;
}

$headers = [
    'Authorization' => 'Bearer '.$accesstoken,
];
$offset = 0;

$response = null;
$artists = [];
$trackitems = [];
do {
    $response = $client->request(
        'GET',
        'https://api.spotify.com/v1/playlists/'
        .$_ENV['PLAYLIST_ID']
        ."/tracks?offset={$offset}&limit=100",
        [
            'headers' => [
                'Authorization' => 'Bearer '.$accesstoken,
            ],
        ]
    );

    $songs = json_decode($response->getBody()->__toString());

    $offset += 100;

    foreach ($songs->items as $item) {
        $trackitems[] = $item;
        foreach ($item->track->artists as $artist) {
            if (array_key_exists($artist->id, $artists) && array_key_exists('count', $artists[$artist->id])) {
                ++$artists[$artist->id]['count'];
                $artists[$artist->id]['name'] = $artist->name;
            } else {
                $artists[$artist->id] = [];
                $artists[$artist->id]['name'] = $artist->name;
                $artists[$artist->id]['count'] = 1;
            }
        }
    }
} while (0 === count($songs->items) || 99 <= count($songs->items)); // Delete first condition? Loop will be run >= 1 time anyway. Test with exactly 100 tracks!

uasort($artists, static fn ($a, $b) => $a['count'] < $b['count'] ? 1 : -1);

foreach ($artists as $key => $artist) {
    echo $artist['name'].': '.$artist['count'];
    echo '<br/>';
    $sql = 'INSERT INTO artists
        (ID, name)
        VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name);';
    $stmt = $db->prepare($sql);
    $stmt->execute([$key, $artist['name']]);
}

echo "<table style='borderwidth: 2px borderstyle: solid'>\n";
foreach ($trackitems as $item) {
    echo '<tr>';
    echo '<td>';
    if ($item->track->explicit) {
        echo '&#x26A0; ';
    }
    echo $item->track->name;
    echo '</td>';
    echo '<td>';
    echo $item->track->artists[0]->name;
    echo '</td>';
    echo "<td><img src='";
    echo $item->track->album->images[2]->url;
    echo "'></td>";
    echo '<td>';
    echo $item->track->album->name;
    echo '</td>';
    foreach (array_slice($item->track->artists, 1) as $artist) {
        echo "<td>{$artist->name}</td>";
    }
    echo "</tr>\n";
}
echo '</table>';
