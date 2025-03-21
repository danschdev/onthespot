<?php

use GuzzleHttp\Client;

function getDatabaseConnection(): ?PDO
{
    try {
        $db = new PDO(
            'mysql:host=localhost;dbname=onthespot;charset=utf8mb4',
            'root',
            ''
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $exception) {
        echo 'Konnte Verbindung nicht erstellen: '.$exception->getMessage();
        return null;
    }
}

function accesstoken(Client $client): string
{
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__."\..");
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

