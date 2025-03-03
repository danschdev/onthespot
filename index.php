<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;

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
$token = $body['access_token'];

?>