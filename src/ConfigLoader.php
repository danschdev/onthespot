<?php
class ConfigLoader
{
    public static function load(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '\..');
        $dotenv->load();
    }
}
