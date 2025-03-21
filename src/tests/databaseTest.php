<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../database.php';

class DatabaseTest extends TestCase
{
    public function testDatabaseConnection()
    {
        $pdo = getDatabaseConnection();
        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertTrue($pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) !== false);
    }
}