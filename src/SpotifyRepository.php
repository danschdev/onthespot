<?php

declare(strict_types=1);

require_once 'DatabaseConnection.php';

class SpotifyRepository
{
    private PDO $pdo;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->pdo = $databaseConnection->getPdo();
    }

    public function saveAccessToken(string $accessToken, DateTime $expiresAt): void
    {
        $formattedDate = $expiresAt->format('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('INSERT INTO spotify_tokens (token, expires_at) VALUES (:token, :expiresAt)');
        $stmt->execute([
            'token' => $accessToken,
            'expiresAt' => $formattedDate,
        ]);
    }

    public function getLatestAccessToken(): ?string
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('SELECT token FROM spotify_tokens WHERE expires_at > :now ORDER BY expires_at DESC LIMIT 1');
        $stmt->execute(['now' => $now]);

        return $stmt->fetchColumn() ?: null;
    }

    /**
     * @param array<mixed> $artist
     */
    public function saveArtist(string $key, array $artist): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO artists (ID, name)
        VALUES (:id, :name) ON DUPLICATE KEY UPDATE name = VALUES(name);');
        $stmt->execute(['id' => $key, 'name' => $artist['name']]);
    }
}
