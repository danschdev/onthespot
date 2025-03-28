<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../Database.php';

require_once __DIR__.'/../spotifyApi.php';

final class connectionTest extends TestCase
{
    public function testDatabaseConnection(): void
    {
        $database = new Database();
        $pdo = $database->getPdo();
        self::assertInstanceOf(PDO::class, $pdo);
        self::assertTrue(false !== $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS));
    }

    public function testSpotifyApiToken(): void
    {
        $client = new Client();
        $spotifyApi = new SpotifyApi($client);
        $token = $spotifyApi->createAccessToken();
        self::assertNotEmpty($token);
    }
}
