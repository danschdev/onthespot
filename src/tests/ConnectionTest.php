<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../database/DatabaseConnection.php';

require_once __DIR__.'/../core/SpotifyAuthenticator.php';

require_once __DIR__.'/../core/SpotifyPlaylistFetcher.php';

final class ConnectionTest extends TestCase
{
    public function testDatabaseConnection(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'\..\..');
        $dotenv->load();

        $dsn = $_ENV['DATABASE_DSN'];
        $databaseUser = $_ENV['DATABASE_USER'];
        $databasePassword = $_ENV['DATABASE_PASSWORD'];

        $database = new DatabaseConnection($dsn, $databaseUser, $databasePassword);
        $pdo = $database->getPdo();
        self::assertInstanceOf(PDO::class, $pdo);
        self::assertTrue(false !== $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS));
    }

    public function testSpotifyApiToken(): void
    {
        $client = new Client();
        $spotifyApi = new SpotifyAuthenticator($client);
        $token = $spotifyApi->createAccessToken();
        self::assertNotEmpty($token);
    }

    public function testSpotifyPlaylistFetcher(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../..');
        $dotenv->load();

        $client = new Client();
        $spotifyApi = new SpotifyAuthenticator($client);
        $accessToken = $spotifyApi->createAccessToken();
        $playlistFetcher = new SpotifyPlaylistFetcher($client, $accessToken);

        $data = $playlistFetcher->fetchTracks($_ENV['PLAYLIST_ID']);

        self::assertNotEmpty($data['artists']);
        self::assertNotEmpty($data['tracks']);
    }
}
