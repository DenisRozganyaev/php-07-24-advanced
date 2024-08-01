<?php

namespace Core;

use PDO;

class DB
{
    static protected PDO|null $instance = null;

    static public function connect(): PDO
    {
        if (is_null(static::$instance)) {
            $dsn = 'mysql:host='. getenv('DB_HOST') .';dbname=' . getenv('DB_NAME');
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];

            static::$instance = new PDO(
                $dsn,
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                $options
            );
        }

        return static::$instance;
    }
}