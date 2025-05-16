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
foreach ($trackitems as $item) {
    $songGenres = [];

    foreach ($item->track->artists as $trackArtist) {
        $artist = $artists[$trackArtist->id];
        foreach ($artist['genres'] as $genre) {
            if (!array_key_exists($songGenre, $genres)) {
                $songGenres[$genre] = $genre;
            }
        }
    }

    foreach($songGenres as $genre) {
        if (!array_key_exists($genre, $playlistGenres)) {
            $playlistGenres[$genre] = 0;
        } 
        $playlistGenres[$genre] += 1;
    }
}

foreach ($artists as $key => $artist) {
    foreach ($artist['genres'] as $genre) {
        if (array_key_exists($genre, $genres)) {
            $genres[$genre][] = $artist;
        } else {
            $genres[$genre] = [$artist];
        }
        $spotifyRepository->saveGenre($genre);
        $spotifyRepository->saveArtistGenre($key, $genre);
    }
    $spotifyRepository->saveArtist($key, $artist);
}

uasort($playlistGenres, static fn ($a, $b) => $a > $b ? 1 : -1);

foreach($playlistGenres as $genre => $genreSongCount) {
      $paragraph = '<b>'.$genre.'</b>: '.$genreSongCount.' Songs<br/>'
    .$paragraph.'<br/>';
}
echo $paragraph;
  
