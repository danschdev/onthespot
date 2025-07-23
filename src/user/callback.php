<?php

declare(strict_types=1);
use GuzzleHttp\Client;

require __DIR__.'/../../vendor/autoload.php';

require __DIR__.'/../config/ConfigLoader.php';

require __DIR__.'/../database/DatabaseConnection.php';

require __DIR__.'/../domain/PdoSpotifyRepository.php';

require __DIR__.'/../core/SpotifyAuthenticator.php';

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

$code = $_GET['code'];
$clientId = $_ENV['SPOTIFY_CLIENT_ID'];
$clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
$redirectUri = 'http://192.168.160.128/onthespot/src/user/callback.php';

$response = file_get_contents('https://accounts.spotify.com/api/token', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Authorization: Basic '.base64_encode("{$clientId}:{$clientSecret}")."\r\n"
                    ."Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]),
    ],
]));

$data = json_decode($response, true);
$accessToken = $data['access_token'];
$refreshToken = $data['refresh_token'];

$response = file_get_contents('https://api.spotify.com/v1/me', false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer {$accessToken}",
    ],
]));

print_r($response);
