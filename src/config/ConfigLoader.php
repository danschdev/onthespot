<?php

declare(strict_types=1);
class ConfigLoader
{
    public static function load(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(realpath(__DIR__.'/../../'));
        $dotenv->load();
    }
}
