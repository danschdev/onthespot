<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

require __DIR__.'/config/ConfigLoader.php';

require __DIR__.'/database/DatabaseConnection.php';

require __DIR__.'/core/SpotifyAuthenticator.php';

require __DIR__.'/core/SpotifyPlaylistFetcher.php';

use GuzzleHttp\Client;

// phpinfo();

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

uasort($artists, static fn ($a, $b) => $a['count'] < $b['count'] ? 1 : -1);

foreach ($artists as $key => $artist) {
    echo $artist['name'].': '.$artist['count'];
    echo '<br/>';
    echo 'Genres: <br/>';
    foreach ($artist['genres'] as $genre) {
        echo $genre.'</br>';
        $spotifyRepository->saveGenre($genre);
        $spotifyRepository->saveArtistGenre($key, $genre);
    }
    echo '<br/>';
    $spotifyRepository->saveArtist($key, $artist);
}
