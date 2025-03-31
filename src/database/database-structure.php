<?php

declare(strict_types=1);

require '../DatabaseConnection.php';

require '../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'\..\..');
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

$sql = 'CREATE TABLE IF NOT EXISTS `artists` (
  `ID` varchar(22) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_german2_ci;
';

$stmt = $pdo->prepare($sql);
$stmt->execute([]);

$sql = 'ALTER TABLE `artists`
  ADD PRIMARY KEY (`ID`)
  WHERE NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_name = `artists`
    AND constraint_type = `PRIMARY KEY`
  );
COMMIT;';

$stmt = $pdo->prepare($sql);
$stmt->execute([]);
