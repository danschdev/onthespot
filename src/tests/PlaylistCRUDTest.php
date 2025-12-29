<?php

declare(strict_types=1);
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../core/SpotifyAuthenticator.php';

require_once __DIR__.'/../core/SpotifyPlaylistCreator.php';

final class PlaylistCRUDTest extends TestCase
{
    public function testCreatePlaylist(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../..');
        $dotenv->load();

        $client = new Client();
        $spotifyApi = new SpotifyAuthenticator($client);
        $token = $spotifyApi->createAccessToken();
        $userId = $_ENV['SPOTIFY_USER_ID'];
        $spotifyPlaylistCreator = new SpotifyPlaylistCreator($client, $token);

        $playlistName = 'Test Playlist';
        $description = 'This is a test playlist created for unit testing.';
        $public = false;

        $response = $spotifyPlaylistCreator->createPlaylist($playlistName, $description, $public, $userId);

        self::assertArrayHasKey('id', $response);
        self::assertSame($playlistName, $response['name']);
    }
}
