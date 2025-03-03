<?php
require 'vendor/autoload.php';
require 'logindata.php';
use GuzzleHttp\Client;

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

var_dump($token);
?>