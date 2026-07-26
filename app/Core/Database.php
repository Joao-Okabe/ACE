<?php

class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function connect(): PDO
    {
        if (self::$connection === null) {

            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                $_ENV['DB_HOST'],
                $_ENV['DB_PORT'],
                $_ENV['DB_DATABASE']
            );

            self::$connection = new PDO(
                $dsn,
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD']
            );

            self::$connection->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            self::$connection->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );
        }

        return self::$connection;
    }
}