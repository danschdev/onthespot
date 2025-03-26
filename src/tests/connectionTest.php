<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../spotifyApi.php';

class ConnectionTest extends TestCase
{
    public function testDatabaseConnection()
    {
        $pdo = getDatabaseConnection();
        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertTrue($pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) !== false);
    }

    public function testSpotifyApiToken()
    {
        $client = new Client();
        $token = accesstoken($client);
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }
}