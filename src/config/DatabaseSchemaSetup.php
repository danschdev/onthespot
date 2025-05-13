<?php

declare(strict_types=1);

require __DIR__.'/../database/DatabaseConnection.php';

require __DIR__.'/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(realpath(__DIR__.'/../../'));
$dotenv->load();

$dsn = $_ENV['DATABASE_DSN'];
$databaseUser = $_ENV['DATABASE_USER'];
$databasePassword = $_ENV['DATABASE_PASSWORD'];

try {
    $database = new DatabaseConnection($dsn, $databaseUser, $databasePassword);
    $pdo = $database->getPdo();
} catch (RuntimeException $e) {
    echo 'Fehler: '.$e->getMessage();
    $pdo = null;
}

$databaseName = 'onthespot';

$createDatabaseSql = "CREATE DATABASE IF NOT EXISTS `$databaseName` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_german2_ci;";

$pdo->exec($createDatabaseSql);

// Danach sicherstellen, dass du die neue Datenbank benutzt:
$pdo->exec("USE `$databaseName`;");

$sql = 'CREATE TABLE IF NOT EXISTS `artists` (
  `id` varchar(22) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_german2_ci;
';

$stmt = $pdo->prepare($sql);
$stmt->execute([]);

$sql = 'CREATE TABLE IF NOT EXISTS `spotify_tokens` (
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  UNIQUE (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_german2_ci;
';

$stmt = $pdo->prepare($sql);
$stmt->execute([]);

$sql = 'CREATE TABLE IF NOT EXISTS `genres` (
    `id` int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_german2_ci;
';

$stmt = $pdo->prepare($sql);
$stmt->execute([]);

$sql = 'CREATE TABLE IF NOT EXISTS `artist_genre` (
  `artist_id` varchar(22) NOT NULL,
  `genre_id` int NOT NULL,
  PRIMARY KEY (`artist_id`, `genre_id`),
  FOREIGN KEY (`artist_id`) REFERENCES `artists`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`genre_id`) REFERENCES `genres`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_german2_ci;';

$stmt = $pdo->prepare($sql);
$stmt->execute([]);
