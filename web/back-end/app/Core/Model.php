<?php

abstract class Model
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connect();
    }
}