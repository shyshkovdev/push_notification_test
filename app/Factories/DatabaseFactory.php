<?php


namespace App\Factories;


use PDO;

class DatabaseFactory
{
    protected $connection = null;

    public function init()
    {
        if ( $this->connection === null )
        {
            $dns = 'mysql:host='.config('DB_HOST').';port='.config('DB_PORT').';dbname='.config('DB_NAME').';charset=utf8';
            $this->connection = new PDO($dns, config('DB_USER_NAME'), config('DB_PASSWORD'));
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return $this->connection;
    }


}