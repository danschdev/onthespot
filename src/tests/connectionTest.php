<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../database.php';

require_once __DIR__.'/../spotifyApi.php';

/**
 * @internal
 *
 * @coversNothing
 */
final class connectionTest extends TestCase
{
    public function testDatabaseConnection(): void
    {
        $pdo = getDatabaseConnection();
        self::assertInstanceOf(PDO::class, $pdo);
        self::assertTrue(false !== $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS));
    }

    public function testSpotifyApiToken(): void
    {
        $client = new Client();
        $token = accesstoken($client);
        self::assertNotEmpty($token);
    }
}
