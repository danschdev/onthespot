<?php

declare(strict_types=1);

require '../vendor/autoload.php';
require 'ConfigLoader.php';
require 'DatabaseConnection.php';
require 'SpotifyApi.php';
require 'SpotifyPlaylistFetcher.php';

use GuzzleHttp\Client;

$configLoader = new ConfigLoader();
$configLoader->load();

$dsn = $_ENV['DATABASE_DSN'];
$databaseUser = $_ENV['DATABASE_USER'];
$databasePassword = $_ENV['DATABASE_PASSWORD'];

try {
    $database = new DatabaseConnection($dsn, $databaseUser, $databasePassword);
    $pdo = $database->getPdo();
} catch (RuntimeException $e) {
    echo 'Fehler: '.$e->getMessage();
    $pdo = null;
}

$spotifyRepository = new SpotifyRepository($database);
$client = new Client();
$spotifyApi = new SpotifyApi($client, $spotifyRepository);

$accessToken = $spotifyRepository->getLatestAccessToken() ?? $spotifyApi->createAccesstoken();

$headers = [
    'Authorization' => 'Bearer '.$accessToken,
];
$offset = 0;

$response = null;
$artists = [];
$trackitems = [];

$playlistFetcher = new SpotifyPlaylistFetcher($client, $accessToken);

$data = $playlistFetcher->fetchTracks($_ENV['PLAYLIST_ID']);

$artists = $data['artists'];
$trackitems = $data['tracks'];

uasort($artists, static fn ($a, $b) => $a['count'] < $b['count'] ? 1 : -1);

foreach ($artists as $key => $artist) {
    echo $artist['name'].': '.$artist['count'];
    echo '<br/>';
    $sql = 'INSERT INTO artists
        (ID, name)
        VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name);';
    $stmt = $pdo->prepare($sql);
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
