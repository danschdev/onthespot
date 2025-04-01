<?php

require_once 'DatabaseConnection.php';

class SpotifyRepository {

    private PDO $pdo;

    public function __construct(DatabaseConnection $databaseConnection) {
        $this->pdo = $databaseConnection->getPdo();
    }

    public function saveAccessToken(string $accessToken, int $expiresAt): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO spotify_tokens (token, expires_at) VALUES (:token, :expiresAt)");
        $stmt->execute([
            'token' => $accessToken,
            'expiresAt' => $expiresAt,
        ]);
    }

    public function getLatestAccessToken(): ?string
    {
        $stmt = $this->pdo->query('SELECT token FROM spotify_tokens ORDER BY expires_at DESC LIMIT 1');
        return $stmt->fetchColumn() ?: null;
    }
}