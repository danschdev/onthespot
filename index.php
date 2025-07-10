<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

require __DIR__.'/src/config/ConfigLoader.php';

require __DIR__.'/src/database/DatabaseConnection.php';

require __DIR__.'/src/core/SpotifyAuthenticator.php';

require __DIR__.'/src/core/SpotifyPlaylistFetcher.php';

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

    exit;
}

$spotifyRepository = new PdoSpotifyRepository($database);
$client = new Client();
$spotifyApi = new SpotifyAuthenticator($client, $spotifyRepository);

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
$genres = [];

$playlistGenres = [];
$structuredTracks = [];
$artistCount = [];

foreach ($trackitems as $item) {
    $songGenres = [];
    foreach ($item->track->artists as $trackArtist) {
        $artistId = $trackArtist->id;
        $artistName = $trackArtist->name;

        $structuredTracks[$item->track->id]['artists'][$artistId]= $artistName;
        
        if (!isset($artistCount[$artistId])) {
            $artistCount[$artistId] = [
                'name' => $artistName,
                'count' => 1
            ];
        } else {
            $artistCount[$artistId]['count']++;
        }
    }
}

foreach ($artists as $key => $artist) {
    foreach ($artist['genres'] as $genre) {
        if (array_key_exists($genre, $genres)) {
            $genres[$genre]['artists'][] = $artist;
        } else {
            $genres[$genre]['artists'] = [$artist];
        }
        $artistTrackItems = array_filter($trackitems, function($item) use ($key) {
            foreach($item->track->artists as $trackArtist) {
                if ($trackArtist->id === $key) {
                    return true;
                }
            }
            return false;
        });

        foreach($artistTrackItems as $ati) {
            $genres[$genre]["tracks"][$ati->track->id] = $ati->track->name;
        }

        $spotifyRepository->saveGenre($genre);
        $spotifyRepository->saveArtistGenre($key, $genre);
    }
    $spotifyRepository->saveArtist($key, $artist);
}

uasort($genres, static fn ($a, $b) => sizeof($a["tracks"]) > sizeof($b["tracks"]) ? -1 : 1);

foreach($genres as $name => $genre) {
    echo "<br/>";
    echo "<h2>".$name.": ".sizeof($genre["tracks"])." Tracks, ".sizeof($genre["artists"])." Artists</h2>";
    echo "<ul>";
    foreach ($genre["artists"] as $artist) {
        echo "<br/>";
        echo "<li>".$artist["name"].": ".$artist["count"]."</li>";
    }
    foreach ($genre["tracks"] as $trackItem) {
        var_dump($trackItem);
    }
    echo "</ul>";
}
