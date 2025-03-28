<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../database.php';

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
        $spotifyApi = new SpotifyApi();
        $token = $spotifyApi->createAccessToken();
        self::assertNotEmpty($token);
    }
}
