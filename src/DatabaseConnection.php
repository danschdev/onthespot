<?php

declare(strict_types=1);

class DatabaseConnection
{
    private PDO $pdo;

    public function __construct(string $dsn, string $user, string $password)
    {
        try {
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Konnte Datenbankverbindung nicht erstellen: '.$exception->getMessage());
        }
    }

    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }
}
